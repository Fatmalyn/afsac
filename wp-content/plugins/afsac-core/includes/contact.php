<?php
/**
 * Page contact : CPT « afsac_message » (leads de contact) + handler POST.
 *
 * Mêmes garde-fous que l'inscription : nonce + honeypot + time-trap + validation
 * 100 % serveur + sanitization + PRG + transient (erreurs/valeurs conservées).
 * Crée un lead (CPT privé, sans réécriture publique → aucun flush requis) puis
 * fire do_action('afsac_contact_created', $id, $data), écouté par le module
 * partagé messaging.php (e-mails + webhook CRM).
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre le CPT « afsac_message » (leads de contact, privé/admin).
 *
 * @return void
 */
function afsac_register_cpt_message() {
	$labels = array(
		'name'               => _x( 'Messages', 'Nom général du CPT', 'afsac' ),
		'singular_name'      => _x( 'Message', 'Nom singulier du CPT', 'afsac' ),
		'menu_name'          => __( 'Messages', 'afsac' ),
		'all_items'          => __( 'Tous les messages', 'afsac' ),
		'edit_item'          => __( 'Voir / éditer le message', 'afsac' ),
		'view_item'          => __( 'Voir le message', 'afsac' ),
		'search_items'       => __( 'Rechercher un message', 'afsac' ),
		'not_found'          => __( 'Aucun message.', 'afsac' ),
		'not_found_in_trash' => __( 'Aucun message dans la corbeille.', 'afsac' ),
	);

	register_post_type(
		'afsac_message',
		array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'has_archive'        => false,
			'rewrite'            => false,
			'query_var'          => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => false,
			'show_in_rest'       => false,
			'menu_icon'          => 'dashicons-email',
			'menu_position'      => 27,
			'hierarchical'       => false,
			'supports'           => array( 'title', 'custom-fields' ),
			'capability_type'    => 'post',
		)
	);
}
add_action( 'init', 'afsac_register_cpt_message' );

/**
 * Sujets qualifiants du formulaire de contact (clé => libellé).
 *
 * Source unique partagée par le template (select) et le handler (whitelist).
 *
 * @return array<string,string>
 */
function afsac_contact_subjects() {
	return array(
		'info'        => __( 'Demande d’information', 'afsac' ),
		'inscription' => __( 'Inscription à une formation', 'afsac' ),
		'devis'       => __( 'Devis sur-mesure', 'afsac' ),
		'sur-mesure'  => __( 'Formation sur-mesure', 'afsac' ),
		'partenariat' => __( 'Partenariat', 'afsac' ),
		'autre'       => __( 'Autre', 'afsac' ),
	);
}

/**
 * URL de redirection vers la page contact avec un état (PRG).
 *
 * @param string $url   URL de base.
 * @param string $state 'ok' | 'error'.
 * @param string $token Jeton d'erreur.
 * @return string
 */
function afsac_contact_redirect_url( $url, $state, $token = '' ) {
	$url  = $url ? $url : home_url( '/' );
	$url  = remove_query_arg( array( 'contact', 'token' ), $url );
	$args = array( 'contact' => $state );
	if ( '' !== $token ) {
		$args['token'] = $token;
	}
	return add_query_arg( $args, $url ) . '#afsac-contact-form';
}

/**
 * Stocke erreurs + anciennes valeurs en transient et redirige (PRG).
 *
 * @param string $url    Référent.
 * @param array  $errors Messages d'erreur.
 * @param array  $old    Anciennes valeurs.
 * @return void
 */
function afsac_contact_fail( $url, $errors, $old ) {
	$token = wp_generate_password( 16, false );
	set_transient( 'afsac_msg_' . $token, array( 'errors' => $errors, 'old' => $old ), 10 * MINUTE_IN_SECONDS );
	wp_safe_redirect( afsac_contact_redirect_url( $url, 'error', $token ) );
	exit;
}

/**
 * Traite la soumission du formulaire de contact.
 *
 * @return void
 */
function afsac_contact_handle_submit() {
	$referer = wp_get_referer();
	if ( ! $referer ) {
		$referer = home_url( '/' );
	}

	// 1) Nonce.
	if ( ! isset( $_POST['afsac_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afsac_contact_nonce'] ) ), 'afsac_contact_submit' ) ) {
		afsac_contact_fail( $referer, array( __( 'Votre session a expiré. Merci de renvoyer le formulaire.', 'afsac' ) ), array() );
	}

	// 2) Honeypot.
	if ( ! empty( $_POST['afsac_website'] ) ) {
		wp_safe_redirect( afsac_contact_redirect_url( $referer, 'ok' ) );
		exit;
	}

	// 3) Time-trap (rejet < 3 s si tampon JS présent).
	$ts = isset( $_POST['afsac_ts'] ) ? absint( $_POST['afsac_ts'] ) : 0;
	if ( $ts > 0 && ( time() - $ts ) < 3 ) {
		wp_safe_redirect( afsac_contact_redirect_url( $referer, 'ok' ) );
		exit;
	}

	// 4) Récupération + sanitization.
	// Le téléphone est conservé AVANT nettoyage : « abc » s'assainit en chaîne
	// vide, ce qui le ferait passer pour « non renseigné » (donc valide, le champ
	// étant facultatif) au lieu d'être signalé à l'expéditeur.
	$telephone_raw = isset( $_POST['afsac_telephone'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['afsac_telephone'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce vérifié.
	$f             = array(
		'prenom'       => isset( $_POST['afsac_prenom'] ) ? sanitize_text_field( wp_unslash( $_POST['afsac_prenom'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce vérifié.
		'nom'          => isset( $_POST['afsac_nom'] ) ? sanitize_text_field( wp_unslash( $_POST['afsac_nom'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'email'        => isset( $_POST['afsac_email'] ) ? sanitize_email( wp_unslash( $_POST['afsac_email'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'telephone'    => afsac_sanitize_phone( $telephone_raw ),
		'fonction'     => isset( $_POST['afsac_fonction'] ) ? sanitize_text_field( wp_unslash( $_POST['afsac_fonction'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'organisation' => isset( $_POST['afsac_organisation'] ) ? sanitize_text_field( wp_unslash( $_POST['afsac_organisation'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'sujet'        => isset( $_POST['afsac_sujet'] ) ? sanitize_text_field( wp_unslash( $_POST['afsac_sujet'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'message'      => isset( $_POST['afsac_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['afsac_message'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
	);
	$consent = ! empty( $_POST['afsac_consent_rgpd'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	// 5) Validation serveur.
	$errors = array();
	if ( '' === $f['prenom'] ) {
		$errors[] = __( 'Votre prénom est requis.', 'afsac' );
	}
	if ( '' === $f['nom'] ) {
		$errors[] = __( 'Votre nom est requis.', 'afsac' );
	}
	if ( '' === $f['email'] || ! is_email( $f['email'] ) ) {
		$errors[] = __( 'Une adresse e-mail valide est requise.', 'afsac' );
	}
	// Téléphone REQUIS : on distingue « non renseigné » de « mal formaté » pour
	// que l'expéditeur sache quoi corriger.
	if ( '' === $telephone_raw ) {
		$errors[] = __( 'Votre numéro de téléphone est requis.', 'afsac' );
	} elseif ( ! afsac_is_valid_phone( $f['telephone'] ) ) {
		$errors[] = __( 'Le numéro de téléphone saisi n’est pas valide.', 'afsac' );
	}
	if ( '' === $f['fonction'] ) {
		$errors[] = __( 'Votre fonction est requise.', 'afsac' );
	}
	if ( '' === $f['organisation'] ) {
		$errors[] = __( 'Votre organisation est requise.', 'afsac' );
	}
	$afsac_subjects = afsac_contact_subjects();
	if ( ! isset( $afsac_subjects[ $f['sujet'] ] ) ) {
		$errors[] = __( 'Merci de choisir un sujet.', 'afsac' );
	}
	if ( mb_strlen( $f['message'] ) < 10 ) {
		$errors[] = __( 'Votre message doit comporter au moins 10 caractères.', 'afsac' );
	}
	if ( ! $consent ) {
		$errors[] = __( 'Vous devez accepter le traitement de vos données (RGPD).', 'afsac' );
	}

	if ( $errors ) {
		$old = array(
			'afsac_prenom'       => $f['prenom'],
			'afsac_nom'          => $f['nom'],
			'afsac_email'        => $f['email'],
			// La saisie D'ORIGINE est réaffichée : renvoyer le numéro nettoyé
			// (parfois vide) laisserait l'expéditeur devant un champ vidé et une
			// erreur incompréhensible.
			'afsac_telephone'    => $telephone_raw,
			'afsac_fonction'     => $f['fonction'],
			'afsac_organisation' => $f['organisation'],
			'afsac_sujet'        => $f['sujet'],
			'afsac_message'      => $f['message'],
			'afsac_consent_rgpd' => $consent ? '1' : '',
		);
		afsac_contact_fail( $referer, $errors, $old );
	}

	// 6) Création du lead.
	$afsac_sujet_label = isset( $afsac_subjects[ $f['sujet'] ] ) ? $afsac_subjects[ $f['sujet'] ] : $f['sujet'];
	$afsac_full_name   = trim( $f['prenom'] . ' ' . $f['nom'] );
	$title             = trim( $afsac_full_name . ' — ' . $afsac_sujet_label ) . ' (' . wp_date( 'Y-m-d H:i' ) . ')';

	$lead_id = wp_insert_post(
		array(
			'post_type'   => 'afsac_message',
			'post_status' => 'publish',
			'post_title'  => $title,
		),
		true
	);
	if ( is_wp_error( $lead_id ) || ! $lead_id ) {
		afsac_contact_fail( $referer, array( __( 'Une erreur technique est survenue. Merci de réessayer.', 'afsac' ) ), array() );
	}

	$now = current_time( 'mysql' );
	foreach ( $f as $k => $v ) {
		update_post_meta( $lead_id, 'afsac_msg_' . $k, $v );
	}
	// Stocke le libellé lisible du sujet (et non la clé) pour l'admin/e-mail.
	update_post_meta( $lead_id, 'afsac_msg_sujet', $afsac_sujet_label );
	update_post_meta( $lead_id, 'afsac_msg_statut', 'nouveau' );
	update_post_meta( $lead_id, 'afsac_msg_consent_rgpd_at', $now );
	update_post_meta( $lead_id, 'afsac_msg_ip', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	update_post_meta( $lead_id, 'afsac_msg_source', esc_url_raw( $referer ) );
	update_post_meta( $lead_id, 'afsac_msg_lang', function_exists( 'pll_current_language' ) ? pll_current_language() : '' );

	$data          = array_merge( $f, array( 'consent_at' => $now ) );
	$data['sujet'] = $afsac_sujet_label;

	/**
	 * Point d'extension : un message de contact vient d'être créé.
	 *
	 * @param int   $lead_id ID du CPT afsac_message.
	 * @param array $data    Données sanitisées.
	 */
	do_action( 'afsac_contact_created', $lead_id, $data );

	wp_safe_redirect( afsac_contact_redirect_url( $referer, 'ok' ) );
	exit;
}
add_action( 'admin_post_afsac_contact_submit', 'afsac_contact_handle_submit' );
add_action( 'admin_post_nopriv_afsac_contact_submit', 'afsac_contact_handle_submit' );

/**
 * Colonnes admin de la liste des messages.
 *
 * @param array $cols Colonnes.
 * @return array
 */
function afsac_message_columns( $cols ) {
	return array(
		'cb'               => isset( $cols['cb'] ) ? $cols['cb'] : '',
		'title'            => __( 'Message', 'afsac' ),
		'afsac_msg_email'  => __( 'E-mail', 'afsac' ),
		'afsac_msg_sujet'  => __( 'Sujet', 'afsac' ),
		'date'             => isset( $cols['date'] ) ? $cols['date'] : __( 'Date', 'afsac' ),
	);
}
add_filter( 'manage_afsac_message_posts_columns', 'afsac_message_columns' );

/**
 * Contenu des colonnes personnalisées.
 *
 * @param string $col     Colonne.
 * @param int    $post_id ID du message.
 * @return void
 */
function afsac_message_column_content( $col, $post_id ) {
	if ( 'afsac_msg_email' === $col ) {
		echo esc_html( (string) get_post_meta( $post_id, 'afsac_msg_email', true ) );
	} elseif ( 'afsac_msg_sujet' === $col ) {
		echo esc_html( (string) get_post_meta( $post_id, 'afsac_msg_sujet', true ) );
	}
}
add_action( 'manage_afsac_message_posts_custom_column', 'afsac_message_column_content', 10, 2 );
