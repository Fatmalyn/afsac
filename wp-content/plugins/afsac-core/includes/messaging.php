<?php
/**
 * Module PARTAGÉ : notifications de leads + coordonnées + intégration CRM.
 *
 * Mutualisé par l'inscription ET le contact. Écoute les points d'extension
 * do_action('afsac_inscription_created') et do_action('afsac_contact_created'),
 * et dispatche : e-mail de notification interne (Reply-To = expéditeur), e-mail
 * de confirmation à l'expéditeur, et POST JSON vers un webhook CRM. Tous les
 * réglages (e-mails, expéditeur, webhook) et les coordonnées vivent dans une
 * page d'options ACF — AUCUNE valeur réelle en dur. (Complète l'« étape 3 ».)
 *
 * DEPUIS LE 09/09/2026 (retour client : « le mail arrive brut, et en spam ») :
 * les e-mails partent en HTML MIS EN FORME (gabarit afsac_mail_html(), bandeau
 * bleu, tableau des champs, bouton vers la fiche) avec une version texte en
 * alternative (AltBody), les entités HTML des titres (« &#8211; ») sont
 * décodées, et le préfixe des sujets / le nom d'expéditeur viennent du réglage
 * « Nom de l'expéditeur » (afsac_mail_brand()) plutôt que du titre du site.
 * La MISE EN FORME ne suffit pas contre le spam : l'expéditeur doit être une
 * adresse du domaine du client envoyée via un SMTP authentifié (plugin SMTP,
 * cf. docs/guide-admin.md) — afsactunisie.com est en Microsoft 365 avec un SPF
 * strict (-all), le serveur web ne peut donc PAS émettre en son nom.
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slug de la page d'options ACF partagée.
 *
 * @var string
 */
const AFSAC_SETTINGS_SLUG = 'afsac-settings';

/**
 * Enregistre la page d'options ACF (réglages + coordonnées).
 *
 * @return void
 */
function afsac_register_settings_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}
	acf_add_options_page(
		array(
			'page_title' => __( 'AFSAC — Réglages & coordonnées', 'afsac' ),
			'menu_title' => __( 'AFSAC Réglages', 'afsac' ),
			'menu_slug'  => AFSAC_SETTINGS_SLUG,
			'capability' => 'manage_options',
			'position'   => 59,
			'icon_url'   => 'dashicons-email-alt',
			'redirect'   => false,
		)
	);
}
add_action( 'acf/init', 'afsac_register_settings_page' );

/**
 * Déclare les groupes de champs de la page d'options (notifications + coordonnées).
 *
 * @return void
 */
function afsac_register_settings_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_afsac_notifications',
			'title'    => __( 'Notifications & CRM', 'afsac' ),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => AFSAC_SETTINGS_SLUG,
					),
				),
			),
			'fields'   => array(
				array(
					'key'          => 'field_afsac_notif_emails',
					'label'        => __( 'E-mail(s) de notification', 'afsac' ),
					'name'         => 'afsac_notif_emails',
					'type'         => 'text',
					'instructions' => __( 'Destinataires internes des leads (inscription + contact). Séparer par des virgules. Vide = e-mail admin du site.', 'afsac' ),
				),
				array(
					'key'          => 'field_afsac_from_name',
					'label'        => __( 'Nom de l’expéditeur', 'afsac' ),
					'name'         => 'afsac_from_name',
					'type'         => 'text',
					'instructions' => __( 'Vide = nom du site.', 'afsac' ),
				),
				array(
					'key'          => 'field_afsac_from_email',
					'label'        => __( 'E-mail de l’expéditeur', 'afsac' ),
					'name'         => 'afsac_from_email',
					'type'         => 'email',
					'instructions' => __( 'Adresse « From » des e-mails. Vide = no-reply@domaine.', 'afsac' ),
				),
				array(
					'key'          => 'field_afsac_crm_webhook',
					'label'        => __( 'Webhook CRM (URL)', 'afsac' ),
					'name'         => 'afsac_crm_webhook',
					'type'         => 'url',
					'instructions' => __( 'Optionnel : chaque lead est POSTé en JSON à cette URL.', 'afsac' ),
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_afsac_coordonnees',
			'title'    => __( 'Coordonnées AFSAC', 'afsac' ),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => AFSAC_SETTINGS_SLUG,
					),
				),
			),
			'fields'   => array(
				/*
				 * Adresse, téléphone et e-mail public ONT ÉTÉ RETIRÉS d'ici (02/09/2026) :
				 * ils faisaient double emploi avec les réglages Customizer qui
				 * alimentent le pied de page et la barre du haut, et la page Contact
				 * affichait encore les valeurs de maquette restées dans ces champs
				 * (« contact@afsac.example »). Un seul endroit désormais :
				 * Apparence → Personnaliser → Coordonnées (afsac_get_contact()).
				 */
				array(
					'key'   => 'field_afsac_contact_hours',
					'label' => __( 'Horaires', 'afsac' ),
					'name'  => 'afsac_contact_hours',
					'type'  => 'textarea',
					'rows'  => 3,
				),
				/*
				 * Latitude / longitude RETIRÉES le 02/09/2026 : elles ne pilotaient que
				 * le marqueur Leaflet, remplacé par un iframe Google Maps qui géolocalise
				 * l'adresse lui-même. Pour épingler le lieu au mètre, le réglage est
				 * désormais « Carte — lien d'intégration Google Maps », dans le
				 * Customizer (afsac_map_embed) — c'est-à-dire au même endroit que
				 * l'adresse, et surtout à un endroit ATTEIGNABLE : cette page d'options
				 * ACF ne s'enregistre pas tant qu'ACF Pro n'est pas activé.
				 */
				array(
					'key'   => 'field_afsac_social_linkedin',
					'label' => __( 'LinkedIn (URL)', 'afsac' ),
					'name'  => 'afsac_social_linkedin',
					'type'  => 'url',
				),
				array(
					'key'   => 'field_afsac_social_facebook',
					'label' => __( 'Facebook (URL)', 'afsac' ),
					'name'  => 'afsac_social_facebook',
					'type'  => 'url',
				),
				array(
					'key'   => 'field_afsac_social_x',
					'label' => __( 'X / Twitter (URL)', 'afsac' ),
					'name'  => 'afsac_social_x',
					'type'  => 'url',
				),
				array(
					'key'   => 'field_afsac_social_youtube',
					'label' => __( 'YouTube (URL)', 'afsac' ),
					'name'  => 'afsac_social_youtube',
					'type'  => 'url',
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_afsac_home_cta',
			'title'    => __( 'CTA accueil', 'afsac' ),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => AFSAC_SETTINGS_SLUG,
					),
				),
			),
			'fields'   => array(
				array(
					'key'          => 'field_afsac_home_cta1_url',
					'label'        => __( 'CTA principal — lien', 'afsac' ),
					'name'         => 'afsac_home_cta1_url',
					'type'         => 'url',
					'instructions' => __( 'Bouton « Consulter les formations » du hero. Vide = page Catalogue.', 'afsac' ),
				),
				array(
					'key'          => 'field_afsac_home_cta2_url',
					'label'        => __( 'CTA secondaire — lien', 'afsac' ),
					'name'         => 'afsac_home_cta2_url',
					'type'         => 'url',
					'instructions' => __( 'Bouton « Demander un devis » du hero. Vide = Contact (sujet devis).', 'afsac' ),
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_afsac_keyfigures',
			'title'    => __( 'Chiffres-clés', 'afsac' ),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => AFSAC_SETTINGS_SLUG,
					),
				),
			),
			'fields'   => array(
				array(
					'key'          => 'field_afsac_keyfigures',
					'label'        => __( 'Chiffres-clés', 'afsac' ),
					'name'         => 'afsac_keyfigures',
					'type'         => 'repeater',
					'instructions' => __( 'Bande de chiffres réutilisée sous les heros (ex. « 45+ » / « Années d’expertise »). Vide = valeurs par défaut.', 'afsac' ),
					'button_label' => __( 'Ajouter un chiffre', 'afsac' ),
					'sub_fields'   => array(
						array(
							'key'     => 'field_afsac_keyfigure_number',
							'label'   => __( 'Valeur', 'afsac' ),
							'name'    => 'number',
							'type'    => 'text',
							'wrapper' => array( 'width' => '30' ),
						),
						array(
							'key'     => 'field_afsac_keyfigure_label',
							'label'   => __( 'Libellé', 'afsac' ),
							'name'    => 'label',
							'type'    => 'text',
							'wrapper' => array( 'width' => '70' ),
						),
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'afsac_register_settings_fields' );

/**
 * Chiffres-clés éditables (repeater options) avec repli sur afsac_hero_stats().
 *
 * @return array<int,array{number:string,label:string}>
 */
function afsac_key_figures() {
	$rows = function_exists( 'get_field' ) ? get_field( 'afsac_keyfigures', 'option' ) : array();
	$out  = array();
	if ( is_array( $rows ) ) {
		foreach ( $rows as $row ) {
			$number = isset( $row['number'] ) ? trim( (string) $row['number'] ) : '';
			$label  = isset( $row['label'] ) ? trim( (string) $row['label'] ) : '';
			if ( '' !== $number ) {
				$out[] = array( 'number' => $number, 'label' => $label );
			}
		}
	}
	if ( empty( $out ) && function_exists( 'afsac_hero_stats' ) ) {
		foreach ( afsac_hero_stats() as $stat ) {
			$out[] = array(
				'number' => isset( $stat['value'] ) ? (string) $stat['value'] : '',
				'label'  => isset( $stat['label'] ) ? (string) $stat['label'] : '',
			);
		}
	}
	return $out;
}

/**
 * Lit une valeur d'option ACF avec repli.
 *
 * @param string $key     Nom du champ.
 * @param string $default Valeur de repli.
 * @return string
 */
function afsac_opt( $key, $default = '' ) {
	$v = function_exists( 'get_field' ) ? get_field( $key, 'option' ) : null;
	return ( null === $v || false === $v || '' === $v ) ? $default : (string) $v;
}

/**
 * Lit un réglage de messagerie : option NATIVE (page Settings API) d'abord,
 * puis repli sur l'option ACF (si ACF Pro configuré), enfin la valeur par défaut.
 *
 * Découple la config des leads de la licence ACF Pro : la page de réglages
 * native (cf. plus bas) fonctionne même en ACF gratuit.
 *
 * @param string $key     Clé interne (notif_emails|from_name|from_email|crm_webhook).
 * @param string $default Valeur de repli.
 * @return string
 */
function afsac_msg_setting( $key, $default = '' ) {
	$o = get_option( 'afsac_messaging', array() );
	if ( is_array( $o ) && isset( $o[ $key ] ) && '' !== trim( (string) $o[ $key ] ) ) {
		return (string) $o[ $key ];
	}
	// Repli ACF (page d'options ACF Pro), si une valeur y est renseignée.
	$acf = array(
		'notif_emails' => 'afsac_notif_emails',
		'from_name'    => 'afsac_from_name',
		'from_email'   => 'afsac_from_email',
		'crm_webhook'  => 'afsac_crm_webhook',
	);
	if ( isset( $acf[ $key ] ) && function_exists( 'afsac_opt' ) ) {
		$v = afsac_opt( $acf[ $key ], '' );
		if ( '' !== $v ) {
			return $v;
		}
	}
	return $default;
}

/**
 * Réglages de notification résolus (avec replis sûrs, jamais de valeur en dur).
 *
 * `notif_explicit` indique si un destinataire a RÉELLEMENT été réglé (sinon on
 * retombe sur l'e-mail admin du site — signalé par un avis en admin).
 *
 * @return array{notif_emails:array,notif_explicit:bool,from_name:string,from_email:string,webhook:string}
 */
function afsac_messaging_settings() {
	$explicit  = afsac_msg_setting( 'notif_emails', '' );
	$notif_raw = '' !== $explicit ? $explicit : (string) get_option( 'admin_email' );
	$emails    = array_filter( array_map( 'trim', preg_split( '/[,;\n]+/', $notif_raw ) ) );
	$emails    = array_values( array_filter( $emails, 'is_email' ) );

	$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );

	return array(
		'notif_emails'   => $emails,
		'notif_explicit' => ( '' !== $explicit && ! empty( $emails ) ),
		'from_name'      => afsac_msg_setting( 'from_name', (string) get_bloginfo( 'name' ) ),
		'from_email'     => afsac_msg_setting( 'from_email', 'no-reply@' . ( $host ? $host : 'localhost' ) ),
		'webhook'        => afsac_msg_setting( 'crm_webhook', '' ),
	);
}

/**
 * ACF Pro (pages d'options) est-il disponible ?
 *
 * @return bool
 */
function afsac_acf_options_available() {
	return function_exists( 'acf_add_options_page' );
}

/**
 * Page de réglages NATIVE (Settings API) — REPLI quand ACF Pro est absent.
 *
 * Garantit que la config des leads (destinataires + webhook) reste éditable en
 * admin même sans licence ACF Pro. Ne s'enregistre PAS si ACF Pro gère déjà la
 * page d'options (évite le doublon). Mêmes champs, vides par défaut.
 *
 * @return void
 */
function afsac_register_native_settings_page() {
	if ( afsac_acf_options_available() ) {
		return; // ACF Pro fournit déjà la page d'options (cf. afsac_register_settings_page).
	}
	add_menu_page(
		__( 'AFSAC — Réglages & coordonnées', 'afsac' ),
		__( 'AFSAC Réglages', 'afsac' ),
		'manage_options',
		AFSAC_SETTINGS_SLUG,
		'afsac_render_native_settings_page',
		'dashicons-email-alt',
		59
	);
}
add_action( 'admin_menu', 'afsac_register_native_settings_page' );

/**
 * Enregistre l'option native + son assainissement (uniquement sans ACF Pro).
 *
 * @return void
 */
function afsac_register_native_settings() {
	if ( afsac_acf_options_available() ) {
		return;
	}
	register_setting(
		'afsac_messaging_group',
		'afsac_messaging',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'afsac_sanitize_messaging',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'afsac_register_native_settings' );

/**
 * Assainit les réglages de messagerie soumis.
 *
 * @param mixed $input Valeurs brutes du formulaire.
 * @return array
 */
function afsac_sanitize_messaging( $input ) {
	$input  = is_array( $input ) ? $input : array();
	$raw    = isset( $input['notif_emails'] ) ? (string) $input['notif_emails'] : '';
	$emails = array_filter( array_map( 'trim', preg_split( '/[,;\n]+/', $raw ) ) );
	$emails = array_values( array_filter( $emails, 'is_email' ) );

	$from_email = isset( $input['from_email'] ) ? sanitize_email( $input['from_email'] ) : '';

	return array(
		'notif_emails' => implode( ', ', $emails ),
		'from_name'    => isset( $input['from_name'] ) ? sanitize_text_field( $input['from_name'] ) : '',
		'from_email'   => ( $from_email && is_email( $from_email ) ) ? $from_email : '',
		'crm_webhook'  => isset( $input['crm_webhook'] ) ? esc_url_raw( trim( (string) $input['crm_webhook'] ) ) : '',
	);
}

/**
 * Rendu de la page de réglages native (Settings API).
 *
 * @return void
 */
function afsac_render_native_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o = get_option( 'afsac_messaging', array() );
	$g = function ( $k ) use ( $o ) {
		return isset( $o[ $k ] ) ? esc_attr( (string) $o[ $k ] ) : '';
	};
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'AFSAC — Réglages & coordonnées', 'afsac' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'afsac_messaging_group' ); ?>
			<h2><?php echo esc_html__( 'Notifications & CRM', 'afsac' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="afsac_notif_emails"><?php echo esc_html__( 'E-mail(s) de notification', 'afsac' ); ?></label></th>
					<td>
						<input name="afsac_messaging[notif_emails]" id="afsac_notif_emails" type="text" class="regular-text" value="<?php echo $g( 'notif_emails' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- déjà esc_attr. ?>">
						<p class="description"><?php echo esc_html__( 'Destinataires internes des leads (inscription + contact). Séparer par des virgules. Vide = e-mail admin du site.', 'afsac' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="afsac_from_name"><?php echo esc_html__( 'Nom de l’expéditeur', 'afsac' ); ?></label></th>
					<td>
						<input name="afsac_messaging[from_name]" id="afsac_from_name" type="text" class="regular-text" value="<?php echo $g( 'from_name' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- déjà esc_attr. ?>">
						<p class="description"><?php echo esc_html__( 'Vide = nom du site.', 'afsac' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="afsac_from_email"><?php echo esc_html__( 'E-mail de l’expéditeur', 'afsac' ); ?></label></th>
					<td>
						<input name="afsac_messaging[from_email]" id="afsac_from_email" type="email" class="regular-text" value="<?php echo $g( 'from_email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- déjà esc_attr. ?>">
						<p class="description"><?php echo esc_html__( 'Adresse « From » des e-mails. Vide = no-reply@domaine.', 'afsac' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="afsac_crm_webhook"><?php echo esc_html__( 'Webhook CRM (URL)', 'afsac' ); ?></label></th>
					<td>
						<input name="afsac_messaging[crm_webhook]" id="afsac_crm_webhook" type="url" class="regular-text" value="<?php echo $g( 'crm_webhook' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- déjà esc_attr. ?>">
						<p class="description"><?php echo esc_html__( 'Optionnel : chaque lead est POSTé en JSON à cette URL.', 'afsac' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Avis admin : aucun destinataire de notification réglé → repli sur admin_email.
 *
 * Affiché uniquement sur les écrans pertinents (listes Inscriptions/Messages +
 * page de réglages) pour éviter le bruit. Disparaît dès qu'un destinataire est
 * renseigné.
 *
 * @return void
 */
function afsac_messaging_recipient_notice() {
	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'get_current_screen' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}
	$relevant = array(
		'edit-afsac_inscription',
		'edit-afsac_message',
		'edit-afsac_telechargement',
		'toplevel_page_' . AFSAC_SETTINGS_SLUG,
		'acf_options_page_' . AFSAC_SETTINGS_SLUG,
	);
	if ( ! in_array( $screen->id, $relevant, true ) ) {
		return;
	}
	$s = afsac_messaging_settings();
	if ( ! empty( $s['notif_explicit'] ) ) {
		return;
	}
	$url = admin_url( 'admin.php?page=' . AFSAC_SETTINGS_SLUG );
	printf(
		'<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
		esc_html(
			sprintf(
				/* translators: %s: e-mail d'administration du site. */
				__( 'Aucun destinataire de notification n’est réglé : les leads (inscriptions, messages, demandes de brochure) sont envoyés à l’e-mail d’administration du site (%s). Renseignez un ou plusieurs destinataires pour ne rien manquer.', 'afsac' ),
				(string) get_option( 'admin_email' )
			)
		),
		esc_url( $url ),
		esc_html__( 'Configurer les notifications', 'afsac' )
	);
}
add_action( 'admin_notices', 'afsac_messaging_recipient_notice' );

/**
 * Coordonnées publiques de la page contact : ce qui n'a PAS d'équivalent dans les
 * réglages Customizer du thème.
 *
 * Adresse, téléphone et e-mail n'y figurent plus : ils viennent désormais de
 * afsac_get_contact() (thème), source unique avec le pied de page et la barre du
 * haut. Cf. le commentaire du groupe ACF « Coordonnées AFSAC » plus haut.
 *
 * @return array<string,string>
 */
function afsac_contact_coords() {
	return array(
		'hours'    => afsac_opt( 'afsac_contact_hours' ),
		'linkedin' => afsac_opt( 'afsac_social_linkedin' ),
		'facebook' => afsac_opt( 'afsac_social_facebook' ),
		'x'        => afsac_opt( 'afsac_social_x' ),
		'youtube'  => afsac_opt( 'afsac_social_youtube' ),
	);
}

/**
 * Lien vers la fiche d'un lead dans l'administration.
 *
 * PAS get_edit_post_link() : le handler tourne sur admin-post.php SANS
 * utilisateur connecté (visiteur), la fonction renvoyait donc null et le bouton
 * « Ouvrir la fiche » n'apparaissait jamais dans les notifications.
 *
 * @param int $id ID du lead.
 * @return string
 */
function afsac_mail_edit_link( $id ) {
	return admin_url( 'post.php?post=' . (int) $id . '&action=edit' );
}

/**
 * Marque affichée dans les e-mails (préfixe des sujets, bandeau, expéditeur).
 *
 * Le réglage « Nom de l'expéditeur » s'il est renseigné, sinon le titre du site.
 *
 * @return string Texte brut (entités décodées).
 */
function afsac_mail_brand() {
	$s = afsac_messaging_settings();
	return afsac_mail_text( $s['from_name'] );
}

/**
 * Convertit une chaîne WordPress (titre avec entités, HTML) en texte brut d'e-mail.
 *
 * get_the_title() renvoie « ACI &#8211; OACI » : sans décodage, l'entité
 * s'affiche telle quelle dans un e-mail texte (bug constaté par le client).
 *
 * @param string $value Valeur brute.
 * @return string
 */
function afsac_mail_text( $value ) {
	$value = wp_strip_all_tags( (string) $value );
	return trim( html_entity_decode( $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

/**
 * Regroupe des lignes « Libellé : valeur » en blocs pour le gabarit HTML.
 *
 * - « Libellé : valeur »            → ligne du tableau ;
 * - « Libellé : » (valeur vide)     → tout ce qui suit est un bloc multi-ligne
 *                                     (ex. « Message : » puis le texte) ;
 * - autre ligne non vide            → paragraphe.
 *
 * @param string[] $lines Lignes texte.
 * @return array{rows:array<int,array{0:string,1:string}>,paras:string[],block_label:string,block:string}
 */
function afsac_mail_blocks( $lines ) {
	$rows  = array();
	$paras = array();
	$label = '';
	$block = '';
	$n     = count( $lines );
	for ( $i = 0; $i < $n; $i++ ) {
		$line = afsac_mail_text( $lines[ $i ] );
		if ( '' === $line ) {
			continue;
		}
		if ( preg_match( '/^([^:]{1,40}) :\s*(.*)$/u', $line, $m ) ) {
			if ( '' === trim( $m[2] ) ) {
				$label = $m[1];
				$block = trim( implode( "\n", array_map( 'afsac_mail_text', array_slice( $lines, $i + 1 ) ) ) );
				break;
			}
			$rows[] = array( $m[1], $m[2] );
			continue;
		}
		$paras[] = $line;
	}
	return array(
		'rows'        => $rows,
		'paras'       => $paras,
		'block_label' => $label,
		'block'       => $block,
	);
}

/**
 * Gabarit HTML des e-mails (tableau 600 px, styles en ligne : clients mail).
 *
 * @param array $a {
 *     @type string $title  Titre principal.
 *     @type string $intro  Paragraphe d'introduction (texte brut).
 *     @type array  $rows   Lignes [libellé, valeur].
 *     @type array  $paras  Paragraphes (texte brut ; une URL seule devient un lien).
 *     @type string $block_label Libellé du bloc multi-ligne.
 *     @type string $block  Bloc multi-ligne (message).
 *     @type array  $button [ 'label' => ..., 'url' => ... ] ou vide.
 *     @type string $footer Texte du pied.
 * }
 * @return string HTML complet.
 */
function afsac_mail_html( $a ) {
	$a = wp_parse_args(
		$a,
		array(
			'title'       => '',
			'intro'       => '',
			'rows'        => array(),
			'paras'       => array(),
			'block_label' => '',
			'block'       => '',
			'button'      => array(),
			'footer'      => '',
		)
	);
	$brand = afsac_mail_brand();
	$blue  = '#0054a4';
	$font  = 'font-family:Arial,Helvetica,sans-serif;';

	$h  = '<!DOCTYPE html><html lang="' . esc_attr( str_replace( '_', '-', get_locale() ) ) . '"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width"><title>' . esc_html( $a['title'] ) . '</title></head>';
	$h .= '<body style="margin:0;padding:0;background:#f2f6f9;">';
	$h .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f6f9;padding:24px 12px;"><tr><td align="center">';
	$h .= '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;' . $font . 'color:#1f2a37;">';
	$h .= '<tr><td style="background:' . $blue . ';padding:18px 28px;color:#ffffff;font-size:13px;letter-spacing:1.5px;text-transform:uppercase;font-weight:bold;' . $font . '">' . esc_html( $brand ) . '</td></tr>';
	$h .= '<tr><td style="padding:28px;">';
	$h .= '<h1 style="margin:0 0 10px;font-size:22px;line-height:1.3;color:' . $blue . ';font-weight:bold;' . $font . '">' . esc_html( $a['title'] ) . '</h1>';
	if ( '' !== $a['intro'] ) {
		$h .= '<p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#546670;' . $font . '">' . esc_html( $a['intro'] ) . '</p>';
	}
	if ( $a['rows'] ) {
		$h .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;margin:0 0 18px;' . $font . '">';
		foreach ( $a['rows'] as $row ) {
			$h .= '<tr><td style="padding:9px 12px 9px 0;border-bottom:1px solid #e3ebf1;color:#546670;width:38%;vertical-align:top;">' . esc_html( $row[0] ) . '</td>';
			$h .= '<td style="padding:9px 0 9px 12px;border-bottom:1px solid #e3ebf1;font-weight:bold;vertical-align:top;">' . esc_html( $row[1] ) . '</td></tr>';
		}
		$h .= '</table>';
	}
	if ( '' !== $a['block'] ) {
		if ( '' !== $a['block_label'] ) {
			$h .= '<p style="margin:0 0 6px;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#546670;' . $font . '">' . esc_html( $a['block_label'] ) . '</p>';
		}
		$h .= '<div style="background:#f2f6f9;border-left:4px solid ' . $blue . ';padding:14px 16px;border-radius:4px;font-size:15px;line-height:1.6;margin:0 0 18px;white-space:pre-wrap;' . $font . '">' . esc_html( $a['block'] ) . '</div>';
	}
	foreach ( $a['paras'] as $para ) {
		if ( filter_var( $para, FILTER_VALIDATE_URL ) ) {
			$h .= '<p style="margin:0 0 14px;font-size:15px;line-height:1.6;word-break:break-all;' . $font . '"><a href="' . esc_url( $para ) . '" style="color:' . $blue . ';">' . esc_html( $para ) . '</a></p>';
		} else {
			$h .= '<p style="margin:0 0 14px;font-size:15px;line-height:1.6;' . $font . '">' . nl2br( esc_html( $para ) ) . '</p>';
		}
	}
	if ( ! empty( $a['button']['url'] ) && ! empty( $a['button']['label'] ) ) {
		$h .= '<p style="margin:8px 0 0;"><a href="' . esc_url( $a['button']['url'] ) . '" style="display:inline-block;background:' . $blue . ';color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:6px;font-weight:bold;font-size:14px;' . $font . '">' . esc_html( $a['button']['label'] ) . '</a></p>';
	}
	$h .= '</td></tr>';
	if ( '' !== $a['footer'] ) {
		$h .= '<tr><td style="padding:16px 28px;background:#f7f9fb;font-size:12px;line-height:1.5;color:#8c8f94;' . $font . '">' . esc_html( $a['footer'] ) . '</td></tr>';
	}
	$h .= '</table></td></tr></table></body></html>';
	return $h;
}

/**
 * Pose la version TEXTE (AltBody) sur PHPMailer pendant un envoi HTML.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer Instance.
 * @return void
 */
function afsac_mail_set_alt_body( $phpmailer ) {
	if ( ! empty( $GLOBALS['afsac_mail_alt_body'] ) ) {
		$phpmailer->AltBody = (string) $GLOBALS['afsac_mail_alt_body'];
	}
}

/**
 * Envoie un e-mail HTML + texte (multipart/alternative) via wp_mail().
 *
 * @param string|array $to      Destinataire(s).
 * @param string       $subject Sujet (texte brut).
 * @param string       $html    Corps HTML.
 * @param string       $text    Corps texte (alternative).
 * @param string[]     $headers En-têtes (From, Reply-To…), sans Content-Type.
 * @return bool
 */
function afsac_mail_send( $to, $subject, $html, $text, $headers ) {
	$headers[] = 'Content-Type: text/html; charset=UTF-8';
	$GLOBALS['afsac_mail_alt_body']   = $text;
	$GLOBALS['afsac_mail_last_error'] = '';
	add_action( 'phpmailer_init', 'afsac_mail_set_alt_body' );
	$ok = wp_mail( $to, afsac_mail_text( $subject ), $html, $headers );
	remove_action( 'phpmailer_init', 'afsac_mail_set_alt_body' );
	unset( $GLOBALS['afsac_mail_alt_body'] );
	return $ok;
}

/**
 * Mémorise la cause du dernier échec de wp_mail() (WordPress ET WP Mail SMTP
 * déclenchent tous deux `wp_mail_failed` avec un WP_Error).
 *
 * @param WP_Error|mixed $error Erreur transmise par le hook.
 * @return void
 */
function afsac_mail_note_failure( $error ) {
	$GLOBALS['afsac_mail_last_error'] = is_wp_error( $error ) ? $error->get_error_message() : (string) $error;
}
add_action( 'wp_mail_failed', 'afsac_mail_note_failure' );

/**
 * Nom du canal d'envoi actif, pour le journal : WP Mail SMTP (et son mailer)
 * ou la fonction mail() du serveur.
 *
 * @return string
 */
function afsac_mail_channel() {
	if ( defined( 'WPMS_PLUGIN_VER' ) ) {
		$mailer = '';
		if ( class_exists( '\WPMailSMTP\Options' ) ) {
			$mailer = (string) \WPMailSMTP\Options::init()->get( 'mail', 'mailer' );
		}
		return 'WP Mail SMTP ' . WPMS_PLUGIN_VER . ( '' !== $mailer ? ' (' . $mailer . ')' : '' );
	}
	return __( 'mail() du serveur (aucun plugin SMTP)', 'afsac' );
}

/**
 * Entrée du journal d'envoi d'un lead.
 *
 * @param string       $kind 'notification' | 'confirmation'.
 * @param string|array $to   Destinataire(s).
 * @param string       $from Expéditeur demandé par le site (le plugin SMTP peut le forcer).
 * @param bool         $ok   Retour de wp_mail().
 * @return array<string,mixed>
 */
function afsac_mail_log_entry( $kind, $to, $from, $ok ) {
	return array(
		'kind'  => $kind,
		'to'    => implode( ', ', (array) $to ),
		'from'  => (string) $from,
		'ok'    => (bool) $ok,
		'error' => $ok ? '' : ( isset( $GLOBALS['afsac_mail_last_error'] ) ? (string) $GLOBALS['afsac_mail_last_error'] : '' ),
		'via'   => afsac_mail_channel(),
		'at'    => current_time( 'mysql' ),
	);
}

/**
 * Enregistre le journal d'envoi sur la fiche du lead (méta `<préfixe>mail_log`),
 * affiché dans la boîte « Détail de la demande » (lead-admin.php).
 *
 * Répond à « je ne reçois rien » : la fiche dit si le site a bien confié
 * chaque e-mail au serveur d'envoi, ou la cause exacte de l'échec.
 *
 * @param array $ctx     Contexte du dispatch (payload.type / payload.id).
 * @param array $entries Entrées (afsac_mail_log_entry()).
 * @return void
 */
function afsac_mail_log_store( $ctx, $entries ) {
	if ( empty( $ctx['payload']['id'] ) || empty( $ctx['payload']['type'] ) ) {
		return;
	}
	$prefixes = array(
		'contact'     => 'afsac_msg_',
		'inscription' => 'afsac_lead_',
		'brochure'    => defined( 'AFSAC_DL_META' ) ? AFSAC_DL_META : 'afsac_dl_',
	);
	$type = (string) $ctx['payload']['type'];
	if ( ! isset( $prefixes[ $type ] ) ) {
		return;
	}
	update_post_meta( (int) $ctx['payload']['id'], $prefixes[ $type ] . 'mail_log', $entries );
}

/**
 * Dispatch partagé d'un lead : notif interne + confirmation + webhook CRM.
 *
 * @param array $ctx {
 *     @type string $subject_internal Sujet de la notif interne.
 *     @type string $title_internal   Titre affiché dans la notif interne (défaut : sujet).
 *     @type array  $admin_lines      Lignes du corps interne (« Libellé : valeur »).
 *     @type string $reply_to         E-mail de l'expéditeur (Reply-To).
 *     @type string $reply_name       Nom de l'expéditeur.
 *     @type string $confirm_to       E-mail de confirmation (ou '').
 *     @type string $confirm_subject  Sujet de la confirmation.
 *     @type string $confirm_body     Corps de la confirmation (paragraphes séparés par une ligne vide).
 *     @type string $edit_link        Lien admin du lead.
 *     @type array  $payload          Données JSON pour le webhook.
 * }
 * @return void
 */
function afsac_dispatch_lead( $ctx ) {
	$s     = afsac_messaging_settings();
	$brand = afsac_mail_brand();
	$host  = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	$from  = sprintf( 'From: %s <%s>', str_replace( array( "\r", "\n", '<', '>' ), '', $brand ), $s['from_email'] );
	$log   = array();

	// 1) Notification interne.
	if ( ! empty( $s['notif_emails'] ) && ! empty( $ctx['admin_lines'] ) ) {
		$headers = array( $from );
		if ( ! empty( $ctx['reply_to'] ) && is_email( $ctx['reply_to'] ) ) {
			$reply_name = isset( $ctx['reply_name'] ) ? str_replace( array( "\r", "\n", '<', '>' ), '', afsac_mail_text( $ctx['reply_name'] ) ) : '';
			$headers[]  = sprintf( 'Reply-To: %s <%s>', $reply_name, $ctx['reply_to'] );
		}

		$blocks = afsac_mail_blocks( $ctx['admin_lines'] );
		$title  = ! empty( $ctx['title_internal'] ) ? afsac_mail_text( $ctx['title_internal'] ) : afsac_mail_text( $ctx['subject_internal'] );
		$intro  = sprintf(
			/* translators: 1: date et heure, 2: nom de domaine du site. */
			__( 'Reçue le %1$s depuis le site %2$s.', 'afsac' ),
			wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ),
			$host
		);
		$footer = sprintf(
			/* translators: %s: nom de domaine du site. */
			__( 'E-mail automatique envoyé par le site %s. Pour répondre à cette personne, utilisez simplement « Répondre » : son adresse est déjà renseignée.', 'afsac' ),
			$host
		);
		$button = ! empty( $ctx['edit_link'] )
			? array(
				'label' => __( 'Ouvrir la fiche dans l’administration', 'afsac' ),
				'url'   => $ctx['edit_link'],
			)
			: array();

		$html = afsac_mail_html(
			array(
				'title'       => $title,
				'intro'       => $intro,
				'rows'        => $blocks['rows'],
				'paras'       => $blocks['paras'],
				'block_label' => $blocks['block_label'],
				'block'       => $blocks['block'],
				'button'      => $button,
				'footer'      => $footer,
			)
		);
		$text = $title . "\n" . $intro . "\n\n" . implode( "\n", array_map( 'afsac_mail_text', $ctx['admin_lines'] ) );
		if ( ! empty( $ctx['edit_link'] ) ) {
			$text .= "\n\n" . __( 'Voir dans l’administration :', 'afsac' ) . ' ' . $ctx['edit_link'];
		}
		$ok    = afsac_mail_send( $s['notif_emails'], $ctx['subject_internal'], $html, $text, $headers );
		$log[] = afsac_mail_log_entry( 'notification', $s['notif_emails'], $s['from_email'], $ok );
	}

	// 2) Confirmation à l'expéditeur.
	if ( ! empty( $ctx['confirm_to'] ) && is_email( $ctx['confirm_to'] ) && ! empty( $ctx['confirm_subject'] ) ) {
		$paras = array_values( array_filter( array_map( 'afsac_mail_text', preg_split( "/\n\s*\n/", (string) $ctx['confirm_body'] ) ) ) );
		// Le dernier paragraphe est la signature (marque) : le bandeau la porte déjà.
		if ( $paras && afsac_mail_text( end( $paras ) ) === $brand ) {
			array_pop( $paras );
		}
		$title = afsac_mail_text( $ctx['confirm_subject'] );
		if ( '' !== $brand && str_ends_with( $title, ' — ' . $brand ) ) {
			$title = trim( substr( $title, 0, -strlen( ' — ' . $brand ) ) );
		}
		$html = afsac_mail_html(
			array(
				'title'  => $title,
				'paras'  => $paras,
				'footer' => sprintf(
					/* translators: %s: nom de domaine du site. */
					__( 'Cet e-mail vous a été envoyé automatiquement suite à votre demande sur %s.', 'afsac' ),
					$host
				),
			)
		);
		$ok    = afsac_mail_send( $ctx['confirm_to'], $ctx['confirm_subject'], $html, afsac_mail_text( $ctx['confirm_body'] ), array( $from ) );
		$log[] = afsac_mail_log_entry( 'confirmation', $ctx['confirm_to'], $s['from_email'], $ok );
	}

	afsac_mail_log_store( $ctx, $log );

	// 3) Webhook CRM (non bloquant).
	if ( ! empty( $s['webhook'] ) && filter_var( $s['webhook'], FILTER_VALIDATE_URL ) && ! empty( $ctx['payload'] ) ) {
		wp_remote_post(
			$s['webhook'],
			array(
				'timeout'  => 5,
				'blocking' => false,
				'headers'  => array( 'Content-Type' => 'application/json' ),
				'body'     => wp_json_encode( $ctx['payload'] ),
			)
		);
	}
}

/**
 * Listener : message de contact créé → notif + confirmation + webhook.
 *
 * @param int   $id   ID du CPT afsac_message.
 * @param array $data Données du message.
 * @return void
 */
function afsac_on_contact_created( $id, $data ) {
	$site = afsac_mail_brand();
	$name = trim( ( isset( $data['prenom'] ) ? $data['prenom'] : '' ) . ' ' . ( isset( $data['nom'] ) ? $data['nom'] : '' ) );
	$g    = function ( $k ) use ( $data ) {
		return ( isset( $data[ $k ] ) && '' !== $data[ $k ] ) ? $data[ $k ] : '—';
	};
	$lines = array(
		__( 'Nom', 'afsac' ) . ' : ' . ( '' !== $name ? $name : '—' ),
		__( 'E-mail', 'afsac' ) . ' : ' . $g( 'email' ),
		__( 'Téléphone', 'afsac' ) . ' : ' . $g( 'telephone' ),
		__( 'Fonction', 'afsac' ) . ' : ' . $g( 'fonction' ),
		__( 'Organisation', 'afsac' ) . ' : ' . $g( 'organisation' ),
		__( 'Sujet', 'afsac' ) . ' : ' . $data['sujet'],
		'',
		__( 'Message', 'afsac' ) . ' :',
		$data['message'],
	);
	afsac_dispatch_lead(
		array(
			'subject_internal' => sprintf( '[%s] %s — %s', $site, __( 'Nouveau message de contact', 'afsac' ), $data['sujet'] ),
			'title_internal'   => __( 'Nouveau message de contact', 'afsac' ),
			'admin_lines'      => $lines,
			'reply_to'         => $data['email'],
			'reply_name'       => $name,
			'confirm_to'       => $data['email'],
			'confirm_subject'  => sprintf( '%s — %s', __( 'Votre message a bien été reçu', 'afsac' ), $site ),
			'confirm_body'     => sprintf(
				"%s\n\n%s\n\n%s",
				sprintf( /* translators: %s: nom. */ __( 'Bonjour %s,', 'afsac' ), $name ),
				__( 'Nous avons bien reçu votre message et reviendrons vers vous dans les meilleurs délais.', 'afsac' ),
				$site
			),
			'edit_link'        => afsac_mail_edit_link( $id ),
			'payload'          => array_merge( array( 'type' => 'contact', 'id' => (int) $id ), $data ),
		)
	);
}
add_action( 'afsac_contact_created', 'afsac_on_contact_created', 10, 2 );

/**
 * Libellé lisible d'un mode de règlement (la clé brute « bon_commande » sortait
 * telle quelle dans l'e-mail).
 *
 * @param string $key Clé enregistrée.
 * @return string
 */
function afsac_mail_paiement_label( $key ) {
	$labels = array(
		'bon_commande' => __( 'Bon de commande', 'afsac' ),
		'virement'     => __( 'Virement bancaire', 'afsac' ),
		'institution'  => __( 'Prise en charge par l’institution', 'afsac' ),
		'autre'        => __( 'Autre', 'afsac' ),
	);
	return isset( $labels[ $key ] ) ? $labels[ $key ] : $key;
}

/**
 * Listener : inscription créée → notif + confirmation + webhook (mutualisé).
 *
 * @param int   $id   ID du CPT afsac_inscription.
 * @param array $data Données du lead.
 * @return void
 */
function afsac_on_inscription_created( $id, $data ) {
	$site = afsac_mail_brand();
	$name = trim( ( isset( $data['prenom'] ) ? $data['prenom'] : '' ) . ' ' . ( isset( $data['nom'] ) ? $data['nom'] : '' ) );
	$g    = function ( $k ) use ( $data ) {
		return ( isset( $data[ $k ] ) && '' !== $data[ $k ] ) ? $data[ $k ] : '—';
	};
	$lines = array(
		__( 'Participant', 'afsac' ) . ' : ' . ( '' !== $name ? $name : '—' ),
		__( 'E-mail', 'afsac' ) . ' : ' . $g( 'email' ),
		__( 'Téléphone', 'afsac' ) . ' : ' . $g( 'telephone' ),
		__( 'Organisation', 'afsac' ) . ' : ' . $g( 'organisation' ),
		__( 'Fonction', 'afsac' ) . ' : ' . $g( 'fonction' ),
		__( 'Pays', 'afsac' ) . ' : ' . $g( 'pays' ),
		__( 'Formation', 'afsac' ) . ' : ' . $g( 'formation' ),
		__( 'Mode de règlement', 'afsac' ) . ' : ' . afsac_mail_paiement_label( $g( 'paiement' ) ),
	);
	afsac_dispatch_lead(
		array(
			'subject_internal' => sprintf( '[%s] %s — %s', $site, __( 'Nouvelle inscription', 'afsac' ), '' !== $name ? $name : __( 'participant', 'afsac' ) ),
			'title_internal'   => __( 'Nouvelle inscription', 'afsac' ),
			'admin_lines'      => $lines,
			'reply_to'         => isset( $data['email'] ) ? $data['email'] : '',
			'reply_name'       => $name,
			'confirm_to'       => isset( $data['email'] ) ? $data['email'] : '',
			'confirm_subject'  => sprintf( '%s — %s', __( 'Votre demande d’inscription a bien été reçue', 'afsac' ), $site ),
			'confirm_body'     => sprintf(
				"%s\n\n%s\n\n%s",
				sprintf( /* translators: %s: nom. */ __( 'Bonjour %s,', 'afsac' ), '' !== $name ? $name : '' ),
				__( 'Nous avons bien reçu votre demande d’inscription ; nos équipes vous recontacteront.', 'afsac' ),
				$site
			),
			'edit_link'        => afsac_mail_edit_link( $id ),
			'payload'          => array_merge( array( 'type' => 'inscription', 'id' => (int) $id ), $data ),
		)
	);
}
add_action( 'afsac_inscription_created', 'afsac_on_inscription_created', 10, 2 );

/**
 * Listener : demande de brochure → notif interne + e-mail contenant le LIEN.
 *
 * L'e-mail de confirmation porte ici une vraie utilité : il redonne le lien de
 * téléchargement (valable 7 jours) si le visiteur a fermé la fenêtre avant que
 * le PDF n'arrive, et il vaut preuve que l'adresse saisie est bien la sienne.
 *
 * @param int   $id   ID du CPT afsac_telechargement.
 * @param array $data Données du lead (dont download_url).
 * @return void
 */
function afsac_on_brochure_created( $id, $data ) {
	$site = afsac_mail_brand();
	$name = isset( $data['nom'] ) ? (string) $data['nom'] : '';
	$g    = function ( $k ) use ( $data ) {
		return ( isset( $data[ $k ] ) && '' !== $data[ $k ] ) ? $data[ $k ] : '—';
	};

	$lines = array(
		__( 'Nom', 'afsac' ) . ' : ' . ( '' !== $name ? $name : '—' ),
		__( 'E-mail', 'afsac' ) . ' : ' . $g( 'email' ),
		__( 'Organisation', 'afsac' ) . ' : ' . $g( 'organisation' ),
		__( 'Pays', 'afsac' ) . ' : ' . $g( 'pays' ),
		__( 'Édition demandée', 'afsac' ) . ' : ' . $g( 'edition_name' ),
		'',
		empty( $data['is_new'] )
			? __( 'Cette personne avait DÉJÀ demandé la brochure : ses compteurs ont été mis à jour.', 'afsac' )
			: __( 'Nouveau contact.', 'afsac' ),
	);

	$download = isset( $data['download_url'] ) ? (string) $data['download_url'] : '';

	afsac_dispatch_lead(
		array(
			'subject_internal' => sprintf( '[%s] %s — %s', $site, __( 'Nouvelle demande de brochure', 'afsac' ), '' !== $name ? $name : $g( 'email' ) ),
			'title_internal'   => __( 'Nouvelle demande de brochure', 'afsac' ),
			'admin_lines'      => $lines,
			'reply_to'         => isset( $data['email'] ) ? $data['email'] : '',
			'reply_name'       => $name,
			'confirm_to'       => isset( $data['email'] ) ? $data['email'] : '',
			'confirm_subject'  => sprintf( '%s — %s', __( 'Votre brochure AFSAC', 'afsac' ), $site ),
			'confirm_body'     => sprintf(
				"%s\n\n%s\n\n%s\n\n%s\n\n%s",
				sprintf( /* translators: %s: nom. */ __( 'Bonjour %s,', 'afsac' ), $name ),
				__( 'Merci de votre intérêt. Voici le lien de téléchargement de la brochure AFSAC :', 'afsac' ),
				$download,
				__( 'Ce lien reste valable 7 jours. Notre équipe se tient à votre disposition pour toute question sur nos formations.', 'afsac' ),
				$site
			),
			'edit_link'        => afsac_mail_edit_link( $id ),
			'payload'          => array_merge( array( 'type' => 'brochure', 'id' => (int) $id ), $data ),
		)
	);
}
add_action( 'afsac_brochure_created', 'afsac_on_brochure_created', 10, 2 );
