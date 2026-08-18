<?php
/**
 * Capture de lead : traitement POST de la page « Inscription participant ».
 *
 * Handler admin-post (action afsac_inscription_submit) : nonce + anti-spam
 * (honeypot + time-trap) + validation SERVEUR + sanitization, puis création d'un
 * lead (CPT afsac_inscription) avec horodatage des consentements. PRG. Le point
 * d'extension do_action('afsac_inscription_created', $id, $data) permet de brancher
 * emails / CRM (étape 3) sans toucher au cœur.
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Construit une URL de redirection vers la page du formulaire avec un état.
 *
 * @param string $url   URL de base (référent).
 * @param string $state 'ok' | 'error'.
 * @param string $token Jeton (état d'erreur).
 * @return string
 */
function afsac_inscription_redirect_url( $url, $state, $token = '' ) {
	$url  = $url ? $url : home_url( '/' );
	$url  = remove_query_arg( array( 'inscription', 'token' ), $url );
	$args = array( 'inscription' => $state );
	if ( '' !== $token ) {
		$args['token'] = $token;
	}
	return add_query_arg( $args, $url );
}

/**
 * Stocke erreurs + anciennes valeurs en transient et redirige (PRG).
 *
 * @param string $url    Référent.
 * @param array  $errors Messages d'erreur.
 * @param array  $old    Anciennes valeurs (clés = noms de champs du formulaire).
 * @return void
 */
function afsac_inscription_fail( $url, $errors, $old ) {
	$token = wp_generate_password( 16, false );
	set_transient( 'afsac_ins_' . $token, array( 'errors' => $errors, 'old' => $old ), 10 * MINUTE_IN_SECONDS );
	wp_safe_redirect( afsac_inscription_redirect_url( $url, 'error', $token ) );
	exit;
}

/**
 * Traite la soumission du formulaire d'inscription.
 *
 * @return void
 */
function afsac_inscription_handle_submit() {
	$referer = wp_get_referer();
	if ( ! $referer ) {
		$referer = function_exists( 'afsac_get_inscription_url' ) ? afsac_get_inscription_url() : home_url( '/' );
	}

	// 1) Nonce (CSRF).
	if ( ! isset( $_POST['afsac_ins_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afsac_ins_nonce'] ) ), 'afsac_inscription_submit' ) ) {
		afsac_inscription_fail( $referer, array( __( 'Votre session a expiré. Merci de renvoyer le formulaire.', 'afsac' ) ), array() );
	}

	// 2) Honeypot : champ invisible, doit rester vide. Bot → faux succès, rien enregistré.
	if ( ! empty( $_POST['afsac_website'] ) ) {
		wp_safe_redirect( afsac_inscription_redirect_url( $referer, 'ok' ) );
		exit;
	}

	// 3) Time-trap : rejet si soumission < 3 s, UNIQUEMENT si le tampon JS est présent.
	$ts = isset( $_POST['afsac_ts'] ) ? absint( $_POST['afsac_ts'] ) : 0;
	if ( $ts > 0 && ( time() - $ts ) < 3 ) {
		wp_safe_redirect( afsac_inscription_redirect_url( $referer, 'ok' ) );
		exit;
	}

	// 4) Récupération + sanitization.
	$text = function ( $k ) {
		return isset( $_POST[ $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce vérifié ci-dessus.
	};
	// Téléphone conservé AVANT nettoyage : une saisie non numérique (« abc »)
	// s'assainit en chaîne vide et serait alors signalée comme « champ manquant »
	// plutôt que comme numéro invalide.
	$telephone_raw = $text( 'afsac_telephone' );
	$f             = array(
		'civilite'        => $text( 'afsac_civilite' ),
		'prenom'          => $text( 'afsac_prenom' ),
		'nom'             => $text( 'afsac_nom' ),
		'email'           => isset( $_POST['afsac_email'] ) ? sanitize_email( wp_unslash( $_POST['afsac_email'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'telephone'       => afsac_sanitize_phone( $telephone_raw ),
		'age'             => $text( 'afsac_age' ),
		'fonction'        => $text( 'afsac_fonction' ),
		'organisation'    => $text( 'afsac_organisation' ),
		'type_org'        => $text( 'afsac_type_org' ),
		'pays'            => $text( 'afsac_pays' ),
		'langue_supports' => $text( 'afsac_langue_supports' ),
		'regime'          => $text( 'afsac_regime' ),
		'accessibilite'   => $text( 'afsac_accessibilite' ),
		'paiement'        => $text( 'afsac_paiement' ),
		'bon_commande'    => $text( 'afsac_bon_commande' ),
	);
	$consent_exact = ! empty( $_POST['afsac_consent_exactitude'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$consent_rgpd  = ! empty( $_POST['afsac_consent_rgpd'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$session_id    = isset( $_POST['afsac_session_id'] ) ? absint( $_POST['afsac_session_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$formation_id  = isset( $_POST['afsac_formation_id'] ) ? absint( $_POST['afsac_formation_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

	// 5) Whitelists.
	$wl = array(
		'civilite'        => array( 'M.', 'Mme', 'Autre' ),
		'type_org'        => array( 'autorite', 'aeroport', 'compagnie', 'academie', 'autre' ),
		'langue_supports' => array( 'fr', 'en', 'ar' ),
		'paiement'        => array( 'bon_commande', 'virement', 'institution', 'autre' ),
		'age'             => array( '', '18-25', '26-35', '36-45', '46-55', '56+' ),
	);
	if ( ! in_array( $f['age'], $wl['age'], true ) ) {
		$f['age'] = '';
	}

	// 6) Validation serveur.
	$errors = array();
	if ( ! in_array( $f['civilite'], $wl['civilite'], true ) ) {
		$errors[] = __( 'Civilité requise.', 'afsac' );
	}
	if ( '' === $f['prenom'] ) {
		$errors[] = __( 'Prénom requis.', 'afsac' );
	}
	if ( '' === $f['nom'] ) {
		$errors[] = __( 'Nom requis.', 'afsac' );
	}
	if ( '' === $f['email'] || ! is_email( $f['email'] ) ) {
		$errors[] = __( 'Une adresse e-mail valide est requise.', 'afsac' );
	}
	// Téléphone REQUIS ici (contrairement au contact) : une inscription engage
	// une place en session, l'AFSAC doit pouvoir joindre le participant.
	if ( '' === $telephone_raw ) {
		$errors[] = __( 'Numéro de téléphone requis.', 'afsac' );
	} elseif ( ! afsac_is_valid_phone( $f['telephone'] ) ) {
		$errors[] = __( 'Le numéro de téléphone saisi n’est pas valide.', 'afsac' );
	}
	if ( '' === $f['fonction'] ) {
		$errors[] = __( 'Fonction requise.', 'afsac' );
	}
	if ( '' === $f['organisation'] ) {
		$errors[] = __( 'Organisation requise.', 'afsac' );
	}
	if ( ! in_array( $f['type_org'], $wl['type_org'], true ) ) {
		$errors[] = __( 'Type d’organisation requis.', 'afsac' );
	}
	if ( '' === $f['pays'] ) {
		$errors[] = __( 'Pays requis.', 'afsac' );
	}
	if ( ! in_array( $f['langue_supports'], $wl['langue_supports'], true ) ) {
		$errors[] = __( 'Langue préférée des supports requise.', 'afsac' );
	}
	if ( ! in_array( $f['paiement'], $wl['paiement'], true ) ) {
		$errors[] = __( 'Mode de règlement requis.', 'afsac' );
	}
	if ( ! $consent_exact ) {
		$errors[] = __( 'Vous devez certifier l’exactitude des informations et accepter les conditions de l’AFSAC.', 'afsac' );
	}
	if ( ! $consent_rgpd ) {
		$errors[] = __( 'Vous devez accepter le traitement de vos données (RGPD / loi 63-2004).', 'afsac' );
	}

	if ( $errors ) {
		$old = array(
			'afsac_civilite'           => $f['civilite'],
			'afsac_prenom'             => $f['prenom'],
			'afsac_nom'                => $f['nom'],
			'afsac_email'              => $f['email'],
			'afsac_telephone'          => $telephone_raw, // saisie d'origine (cf. plus haut).
			'afsac_age'                => $f['age'],
			'afsac_fonction'           => $f['fonction'],
			'afsac_organisation'       => $f['organisation'],
			'afsac_type_org'           => $f['type_org'],
			'afsac_pays'               => $f['pays'],
			'afsac_langue_supports'    => $f['langue_supports'],
			'afsac_regime'             => $f['regime'],
			'afsac_accessibilite'      => $f['accessibilite'],
			'afsac_paiement'           => $f['paiement'],
			'afsac_bon_commande'       => $f['bon_commande'],
			'afsac_consent_exactitude' => $consent_exact ? '1' : '',
			'afsac_consent_rgpd'       => $consent_rgpd ? '1' : '',
		);
		afsac_inscription_fail( $referer, $errors, $old );
	}

	// 7) Création du lead.
	$formation_title = $formation_id ? get_the_title( $formation_id ) : '';
	$title           = trim( $f['prenom'] . ' ' . $f['nom'] );
	if ( '' !== $formation_title ) {
		$title .= ' — ' . $formation_title;
	}
	$title .= ' (' . wp_date( 'Y-m-d H:i' ) . ')';

	$lead_id = wp_insert_post(
		array(
			'post_type'   => 'afsac_inscription',
			'post_status' => 'publish',
			'post_title'  => $title,
		),
		true
	);
	if ( is_wp_error( $lead_id ) || ! $lead_id ) {
		afsac_inscription_fail( $referer, array( __( 'Une erreur technique est survenue. Merci de réessayer.', 'afsac' ) ), array() );
	}

	$now = current_time( 'mysql' );
	foreach ( $f as $k => $v ) {
		update_post_meta( $lead_id, 'afsac_lead_' . $k, $v );
	}
	update_post_meta( $lead_id, 'afsac_lead_session_id', $session_id );
	update_post_meta( $lead_id, 'afsac_lead_formation_id', $formation_id );
	update_post_meta( $lead_id, 'afsac_lead_statut', 'nouveau' );
	update_post_meta( $lead_id, 'afsac_lead_consent_exactitude_at', $now );
	update_post_meta( $lead_id, 'afsac_lead_consent_rgpd_at', $now );
	update_post_meta( $lead_id, 'afsac_lead_ip', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	update_post_meta( $lead_id, 'afsac_lead_ua', isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 255 ) : '' );
	update_post_meta( $lead_id, 'afsac_lead_source', esc_url_raw( $referer ) );

	/*
	 * Langue du lead. `pll_current_language()` renvoyait TOUJOURS '' ici : on est
	 * sur admin-post.php, Polylang n'a aucune page de contexte d'où déduire la
	 * langue. On la prend donc sur le contenu soumis — session d'abord (c'est
	 * elle qui porte la langue de la formation, cf. la paire fr#207 / en#208),
	 * puis la formation — et on retombe sur le préfixe du référent en dernier
	 * recours (formulaire ouvert sans session ni formation).
	 */
	$lead_lang = '';
	if ( function_exists( 'pll_get_post_language' ) ) {
		foreach ( array( $session_id, $formation_id ) as $lang_src ) {
			if ( $lang_src ) {
				$lead_lang = (string) pll_get_post_language( $lang_src, 'slug' );
				if ( '' !== $lead_lang ) {
					break;
				}
			}
		}
	}
	if ( '' === $lead_lang && $referer && preg_match( '#/([a-z]{2})(/|$)#', wp_parse_url( $referer, PHP_URL_PATH ), $m ) ) {
		$lead_lang = $m[1];
	}
	if ( '' === $lead_lang && function_exists( 'pll_default_language' ) ) {
		$lead_lang = (string) pll_default_language();
	}
	update_post_meta( $lead_id, 'afsac_lead_lang', $lead_lang );

	// Données complètes pour les intégrations (emails / CRM en étape 3).
	$data = array_merge(
		$f,
		array(
			'session_id'   => $session_id,
			'formation_id' => $formation_id,
			'formation'    => $formation_title,
			'consent_at'   => $now,
		)
	);

	/**
	 * Point d'extension : un lead vient d'être créé.
	 *
	 * @param int   $lead_id ID du CPT afsac_inscription.
	 * @param array $data    Données du lead (sanitisées).
	 */
	do_action( 'afsac_inscription_created', $lead_id, $data );

	wp_safe_redirect( afsac_inscription_redirect_url( $referer, 'ok' ) );
	exit;
}
add_action( 'admin_post_afsac_inscription_submit', 'afsac_inscription_handle_submit' );
add_action( 'admin_post_nopriv_afsac_inscription_submit', 'afsac_inscription_handle_submit' );

/**
 * Colonnes de la liste des leads (wp-admin).
 *
 * @param array $cols Colonnes.
 * @return array
 */
function afsac_inscription_columns( $cols ) {
	return array(
		'cb'                => isset( $cols['cb'] ) ? $cols['cb'] : '',
		'title'             => __( 'Inscription', 'afsac' ),
		'afsac_lead_statut' => __( 'Statut', 'afsac' ),
		'afsac_lead_email'  => __( 'E-mail', 'afsac' ),
		'afsac_lead_cours'  => __( 'Cours', 'afsac' ),
		'date'              => isset( $cols['date'] ) ? $cols['date'] : __( 'Date', 'afsac' ),
	);
}
add_filter( 'manage_afsac_inscription_posts_columns', 'afsac_inscription_columns' );

/**
 * Contenu des colonnes personnalisées.
 *
 * @param string $col     Colonne.
 * @param int    $post_id ID du lead.
 * @return void
 */
function afsac_inscription_column_content( $col, $post_id ) {
	if ( 'afsac_lead_statut' === $col ) {
		$statut = (string) get_post_meta( $post_id, 'afsac_lead_statut', true );
		echo esc_html( '' !== $statut ? ucfirst( $statut ) : '—' );
	} elseif ( 'afsac_lead_email' === $col ) {
		echo esc_html( (string) get_post_meta( $post_id, 'afsac_lead_email', true ) );
	} elseif ( 'afsac_lead_cours' === $col ) {
		$fid = (int) get_post_meta( $post_id, 'afsac_lead_formation_id', true );
		echo esc_html( $fid ? get_the_title( $fid ) : '—' );
	}
}
add_action( 'manage_afsac_inscription_posts_custom_column', 'afsac_inscription_column_content', 10, 2 );
