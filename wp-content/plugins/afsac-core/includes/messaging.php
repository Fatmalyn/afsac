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
				array(
					'key'   => 'field_afsac_contact_address',
					'label' => __( 'Adresse', 'afsac' ),
					'name'  => 'afsac_contact_address',
					'type'  => 'textarea',
					'rows'  => 3,
				),
				array(
					'key'   => 'field_afsac_contact_phone',
					'label' => __( 'Téléphone', 'afsac' ),
					'name'  => 'afsac_contact_phone',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_afsac_contact_email',
					'label' => __( 'E-mail public', 'afsac' ),
					'name'  => 'afsac_contact_email',
					'type'  => 'email',
				),
				array(
					'key'   => 'field_afsac_contact_hours',
					'label' => __( 'Horaires', 'afsac' ),
					'name'  => 'afsac_contact_hours',
					'type'  => 'textarea',
					'rows'  => 3,
				),
				array(
					'key'     => 'field_afsac_contact_lat',
					'label'   => __( 'Latitude (carte)', 'afsac' ),
					'name'    => 'afsac_contact_lat',
					'type'    => 'number',
					'step'    => 'any',
					'min'     => -90,
					'max'     => 90,
					'wrapper' => array( 'width' => '50' ),
				),
				array(
					'key'     => 'field_afsac_contact_lng',
					'label'   => __( 'Longitude (carte)', 'afsac' ),
					'name'    => 'afsac_contact_lng',
					'type'    => 'number',
					'step'    => 'any',
					'min'     => -180,
					'max'     => 180,
					'wrapper' => array( 'width' => '50' ),
				),
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
 * Coordonnées publiques (page contact + en-tête éventuellement).
 *
 * @return array<string,string>
 */
function afsac_contact_coords() {
	return array(
		'address'  => afsac_opt( 'afsac_contact_address' ),
		'phone'    => afsac_opt( 'afsac_contact_phone' ),
		'email'    => afsac_opt( 'afsac_contact_email' ),
		'hours'    => afsac_opt( 'afsac_contact_hours' ),
		'lat'      => afsac_opt( 'afsac_contact_lat' ),
		'lng'      => afsac_opt( 'afsac_contact_lng' ),
		'linkedin' => afsac_opt( 'afsac_social_linkedin' ),
		'facebook' => afsac_opt( 'afsac_social_facebook' ),
		'x'        => afsac_opt( 'afsac_social_x' ),
		'youtube'  => afsac_opt( 'afsac_social_youtube' ),
	);
}

/**
 * Dispatch partagé d'un lead : notif interne + confirmation + webhook CRM.
 *
 * @param array $ctx {
 *     @type string $subject_internal Sujet de la notif interne.
 *     @type array  $admin_lines      Lignes du corps interne.
 *     @type string $reply_to         E-mail de l'expéditeur (Reply-To).
 *     @type string $reply_name       Nom de l'expéditeur.
 *     @type string $confirm_to       E-mail de confirmation (ou '').
 *     @type string $confirm_subject  Sujet de la confirmation.
 *     @type string $confirm_body     Corps de la confirmation.
 *     @type string $edit_link        Lien admin du lead.
 *     @type array  $payload          Données JSON pour le webhook.
 * }
 * @return void
 */
function afsac_dispatch_lead( $ctx ) {
	$s          = afsac_messaging_settings();
	$from       = sprintf( 'From: %s <%s>', $s['from_name'], $s['from_email'] );
	$base_head  = array( 'Content-Type: text/plain; charset=UTF-8', $from );

	// 1) Notification interne.
	if ( ! empty( $s['notif_emails'] ) && ! empty( $ctx['admin_lines'] ) ) {
		$headers = $base_head;
		if ( ! empty( $ctx['reply_to'] ) && is_email( $ctx['reply_to'] ) ) {
			$headers[] = sprintf( 'Reply-To: %s <%s>', isset( $ctx['reply_name'] ) ? $ctx['reply_name'] : '', $ctx['reply_to'] );
		}
		$body = implode( "\n", $ctx['admin_lines'] );
		if ( ! empty( $ctx['edit_link'] ) ) {
			$body .= "\n\n" . __( 'Voir dans l’administration :', 'afsac' ) . ' ' . $ctx['edit_link'];
		}
		wp_mail( $s['notif_emails'], $ctx['subject_internal'], $body, $headers );
	}

	// 2) Confirmation à l'expéditeur.
	if ( ! empty( $ctx['confirm_to'] ) && is_email( $ctx['confirm_to'] ) && ! empty( $ctx['confirm_subject'] ) ) {
		wp_mail( $ctx['confirm_to'], $ctx['confirm_subject'], $ctx['confirm_body'], $base_head );
	}

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
	$site = get_bloginfo( 'name' );
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
			'edit_link'        => get_edit_post_link( $id, '' ),
			'payload'          => array_merge( array( 'type' => 'contact', 'id' => (int) $id ), $data ),
		)
	);
}
add_action( 'afsac_contact_created', 'afsac_on_contact_created', 10, 2 );

/**
 * Listener : inscription créée → notif + confirmation + webhook (mutualisé).
 *
 * @param int   $id   ID du CPT afsac_inscription.
 * @param array $data Données du lead.
 * @return void
 */
function afsac_on_inscription_created( $id, $data ) {
	$site = get_bloginfo( 'name' );
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
		__( 'Mode de règlement', 'afsac' ) . ' : ' . $g( 'paiement' ),
	);
	afsac_dispatch_lead(
		array(
			'subject_internal' => sprintf( '[%s] %s — %s', $site, __( 'Nouvelle inscription', 'afsac' ), '' !== $name ? $name : __( 'participant', 'afsac' ) ),
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
			'edit_link'        => get_edit_post_link( $id, '' ),
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
	$site = get_bloginfo( 'name' );
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
			'edit_link'        => get_edit_post_link( $id, '' ),
			'payload'          => array_merge( array( 'type' => 'brochure', 'id' => (int) $id ), $data ),
		)
	);
}
add_action( 'afsac_brochure_created', 'afsac_on_brochure_created', 10, 2 );
