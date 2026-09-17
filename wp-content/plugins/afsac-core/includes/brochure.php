<?php
/**
 * Brochure sous formulaire : CPT « afsac_telechargement » + livraison par jeton.
 *
 * Demande client (14/08/2026) : la brochure ne doit plus se télécharger d'un
 * clic. Le visiteur laisse son e-mail, AFSAC sait donc QUI la demande et COMBIEN
 * de personnes l'ont réellement téléchargée.
 *
 * Demande client (08/09/2026) : TÉLÉCHARGEMENT DIRECT, EN UN CLIC, sans e-mail
 * ni formulaire. Le thème ne pose plus qu'un lien vers le point de
 * téléchargement direct (afsac_brochure_direct_url(), section 5b), qui sert le
 * PDF de l'ÉDITION DE LA LANGUE DE NAVIGATION et incrémente un compteur ANONYME
 * par édition (option afsac_brochure_direct_counts), affiché dans l'écran
 * Téléchargements. Tout le circuit « e-mail → fiche → jeton » ci-dessous est
 * CONSERVÉ (les fiches existantes restent consultables, et il suffit de
 * rebrancher le formulaire côté thème pour le rouvrir), mais plus rien ne
 * l'appelle depuis le site.
 *
 * Deux compteurs DISTINCTS, c'est le cœur de la traçabilité :
 *   - `demandes`         : nombre de fois où la personne a rempli le formulaire ;
 *   - `telechargements`  : nombre de fois où le PDF a réellement été servi.
 * Un formulaire rempli n'est pas un téléchargement (le visiteur peut fermer la
 * fenêtre) : les confondre gonflerait artificiellement le chiffre annoncé au
 * client.
 *
 * Une personne = UNE fiche : la deuxième demande du même e-mail incrémente les
 * compteurs de la fiche existante au lieu d'en créer une seconde. Le nombre de
 * fiches répond donc directement à « combien de personnes ? ».
 *
 * Le PDF n'est JAMAIS exposé en lien direct : le formulaire délivre un jeton
 * (transient, 7 jours) et le fichier est servi par admin-post.php. Sans cela, la
 * première URL récupérée circulerait et toute la traçabilité tomberait.
 * Le jeton ne porte que la CLÉ de langue (« fr »), jamais un chemin : le fichier
 * est re-résolu côté serveur depuis les champs de la home, donc aucun jeton
 * forgé ne peut faire lire un fichier arbitraire.
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Préfixe des métadonnées d'une fiche de téléchargement.
 *
 * @var string
 */
const AFSAC_DL_META = 'afsac_dl_';

/**
 * Durée de validité d'un jeton de téléchargement (aussi celle du lien e-mail).
 *
 * @var int
 */
const AFSAC_DL_TOKEN_TTL = 7 * DAY_IN_SECONDS;

/* -------------------------------------------------------------------------
 * 1. Type de contenu
 * ---------------------------------------------------------------------- */

/**
 * Enregistre le CPT « afsac_telechargement » (leads brochure, privé/admin).
 *
 * @return void
 */
function afsac_register_cpt_telechargement() {
	$labels = array(
		'name'               => _x( 'Téléchargements', 'Nom général du CPT', 'afsac' ),
		'singular_name'      => _x( 'Téléchargement', 'Nom singulier du CPT', 'afsac' ),
		'menu_name'          => __( 'Téléchargements', 'afsac' ),
		'all_items'          => __( 'Demandes de brochure', 'afsac' ),
		'edit_item'          => __( 'Voir la demande', 'afsac' ),
		'view_item'          => __( 'Voir la demande', 'afsac' ),
		'search_items'       => __( 'Rechercher une demande', 'afsac' ),
		'not_found'          => __( 'Aucune demande de brochure.', 'afsac' ),
		'not_found_in_trash' => __( 'Aucune demande dans la corbeille.', 'afsac' ),
	);

	register_post_type(
		'afsac_telechargement',
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
			'menu_icon'          => 'dashicons-download',
			'menu_position'      => 28,
			'hierarchical'       => false,
			'supports'           => array( 'title', 'custom-fields' ),
			'capability_type'    => 'post',
			// Une fiche est un journal : on ne l'écrit pas à la main en admin.
			'capabilities'       => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'       => true,
		)
	);
}
add_action( 'init', 'afsac_register_cpt_telechargement' );

/* -------------------------------------------------------------------------
 * 2. Fichiers disponibles
 * ---------------------------------------------------------------------- */

/**
 * Autonymes des langues de brochure (libellés de CONTENU, jamais traduits).
 *
 * L'ÉDITION ARABE A ÉTÉ RETIRÉE (demande client, 17/08/2026 : « enlever
 * l'arabe, on ne met que FR/EN »), comme elle l'a déjà été de la vitrine
 * d'accueil et du sélecteur du calendrier. Cette liste pilote à la fois le
 * sélecteur du formulaire, les pastilles du pied de page ET la résolution des
 * fichiers : une clé absente ici n'est plus proposée nulle part, même si un PDF
 * arabe reste téléversé en base. Le drapeau `rtl` est conservé pour que le jour
 * où une édition droite-à-gauche revient, il suffise d'une ligne.
 *
 * @return array<string,array{code:string,name:string,rtl:bool}>
 */
function afsac_brochure_langs() {
	return array(
		'fr' => array( 'code' => 'FR', 'name' => 'Français', 'rtl' => false ),
		'en' => array( 'code' => 'EN', 'name' => 'English', 'rtl' => false ),
	);
}

/**
 * ID de la home CANONIQUE (langue par défaut), où les PDF sont réglés une fois.
 *
 * @return int
 */
function afsac_brochure_home_id() {
	$home_id = (int) get_option( 'page_on_front' );
	if ( $home_id && function_exists( 'pll_get_post' ) && function_exists( 'pll_default_language' ) ) {
		$canonical = pll_get_post( $home_id, pll_default_language() );
		if ( $canonical ) {
			$home_id = (int) $canonical;
		}
	}
	return $home_id;
}

/**
 * Brochures réellement téléversées, indexées par clé (« all », « fr », « en »…).
 *
 * Lit la MÉTA BRUTE plutôt que get_field() : un champ « file » ACF stocke l'ID
 * de la pièce jointe en base, la résolution ne dépend donc pas d'ACF (le point
 * de téléchargement doit fonctionner même si ACF est désactivé) et nous donne le
 * CHEMIN serveur, indispensable pour servir le fichier sans le publier.
 *
 * @return array<string,array<string,mixed>> Clé => fichier (id, url, path, filename, filesize, code, name, rtl).
 */
function afsac_brochure_files() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$home_id = afsac_brochure_home_id();
	$cache   = array();
	if ( ! $home_id ) {
		return $cache;
	}

	$sources = array( 'all' => 'afsac_brochure' );
	foreach ( array_keys( afsac_brochure_langs() ) as $lang ) {
		$sources[ $lang ] = 'afsac_brochure_' . $lang;
	}

	$autonyms = afsac_brochure_langs();
	foreach ( $sources as $key => $meta_key ) {
		$att_id = (int) get_post_meta( $home_id, $meta_key, true );
		if ( ! $att_id || 'attachment' !== get_post_type( $att_id ) ) {
			continue;
		}
		$path = get_attached_file( $att_id );
		$url  = wp_get_attachment_url( $att_id );
		if ( ! $url || ! $path || ! file_exists( $path ) ) {
			continue;
		}

		$cache[ $key ] = array(
			'key'      => $key,
			'id'       => $att_id,
			'url'      => $url,
			'path'     => $path,
			'filename' => basename( $path ),
			'filesize' => (int) filesize( $path ),
			'code'     => isset( $autonyms[ $key ] ) ? $autonyms[ $key ]['code'] : 'FR · EN',
			'name'     => isset( $autonyms[ $key ] ) ? $autonyms[ $key ]['name'] : __( 'Édition multilingue', 'afsac' ),
			'rtl'      => isset( $autonyms[ $key ] ) ? $autonyms[ $key ]['rtl'] : false,
		);
	}

	return $cache;
}

/**
 * Clé de brochure proposée par défaut : la LANGUE DE NAVIGATION.
 *
 * Ordre inchangé par rapport à l'ancienne bande de pied de page : langue
 * courante → PDF combiné → première brochure disponible.
 *
 * @return string Clé, ou '' si aucun fichier n'est téléversé.
 */
function afsac_brochure_default_key() {
	$files = afsac_brochure_files();
	if ( ! $files ) {
		return '';
	}

	$lang = function_exists( 'pll_current_language' )
		? (string) pll_current_language()
		: substr( (string) get_locale(), 0, 2 );
	$lang = strtolower( $lang );

	foreach ( array( $lang, 'all' ) as $candidate ) {
		if ( isset( $files[ $candidate ] ) ) {
			return $candidate;
		}
	}

	return (string) array_key_first( $files );
}

/**
 * Fichier correspondant à une clé, ou null.
 *
 * @param string $key Clé de brochure.
 * @return array<string,mixed>|null
 */
function afsac_brochure_file( $key ) {
	$files = afsac_brochure_files();
	return isset( $files[ $key ] ) ? $files[ $key ] : null;
}

/* -------------------------------------------------------------------------
 * 3. Jetons de téléchargement
 * ---------------------------------------------------------------------- */

/**
 * Nettoie un jeton reçu en URL sans en altérer la casse.
 *
 * NE PAS remplacer par sanitize_key() : celui-ci passe la chaîne en minuscules,
 * alors que wp_generate_password() produit des jetons À CASSE MIXTE. Le jeton
 * transformé ne retrouve alors sa ligne que parce que MySQL compare les
 * option_name sans tenir compte de la casse — un cache objet (Redis, Memcached)
 * casserait tous les téléchargements en production.
 *
 * @param string $token Jeton brut (déjà unslashé).
 * @return string Jeton alphanumérique, 64 caractères au plus.
 */
function afsac_brochure_clean_token( $token ) {
	return substr( (string) preg_replace( '/[^A-Za-z0-9]/', '', (string) $token ), 0, 64 );
}

/**
 * Émet un jeton de téléchargement lié à une fiche.
 *
 * @param int    $lead_id ID de la fiche.
 * @param string $key     Clé de brochure.
 * @return string Jeton.
 */
function afsac_brochure_issue_token( $lead_id, $key ) {
	$token = wp_generate_password( 24, false );
	set_transient(
		'afsac_dl_' . $token,
		array(
			'lead' => (int) $lead_id,
			'key'  => (string) $key,
		),
		AFSAC_DL_TOKEN_TTL
	);
	update_post_meta( $lead_id, AFSAC_DL_META . 'token', $token );
	return $token;
}

/**
 * Charge la charge utile d'un jeton (ou null s'il a expiré / n'existe pas).
 *
 * @param string $token Jeton.
 * @return array{lead:int,key:string}|null
 */
function afsac_brochure_read_token( $token ) {
	$token = afsac_brochure_clean_token( $token );
	if ( '' === $token ) {
		return null;
	}
	$data = get_transient( 'afsac_dl_' . $token );
	if ( ! is_array( $data ) || empty( $data['key'] ) ) {
		return null;
	}
	return array(
		'lead' => isset( $data['lead'] ) ? (int) $data['lead'] : 0,
		'key'  => (string) $data['key'],
	);
}

/**
 * URL de téléchargement pour un jeton.
 *
 * @param string $token Jeton.
 * @return string
 */
function afsac_brochure_download_url( $token ) {
	return add_query_arg(
		array(
			'action' => 'afsac_brochure_download',
			'token'  => rawurlencode( $token ),
		),
		admin_url( 'admin-post.php' )
	);
}

/* -------------------------------------------------------------------------
 * 4. Soumission du formulaire
 * ---------------------------------------------------------------------- */

/**
 * URL de retour vers la page d'origine avec un état (PRG).
 *
 * L'ancre vise la bande de pied de page : sans JavaScript (donc sans fenêtre
 * modale) le visiteur atterrit directement sur le panneau.
 *
 * @param string $url   URL de base.
 * @param string $state 'ok' | 'error' | 'expired'.
 * @param array  $args  Arguments additionnels.
 * @return string
 */
function afsac_brochure_redirect_url( $url, $state, $args = array() ) {
	$url = $url ? $url : home_url( '/' );
	$url = remove_query_arg( array( 'brochure', 'token', 'dl' ), $url );
	return add_query_arg( array_merge( array( 'brochure' => $state ), $args ), $url ) . '#afsac-brochure';
}

/**
 * Le formulaire a-t-il été envoyé en arrière-plan par le script du thème ?
 *
 * Dans ce cas le handler répond en JSON (wp_send_json_*) au lieu de rediriger :
 * la page n'est pas rechargée et le thème affiche lui-même le résultat.
 *
 * @return bool
 */
function afsac_brochure_is_ajax() {
	return ! empty( $_POST['afsac_ajax'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- simple choix du format de réponse, nonce vérifié par l'appelant.
}

/**
 * Stocke erreurs + valeurs saisies en transient et redirige (PRG), ou répond
 * en JSON si le formulaire a été envoyé en arrière-plan.
 *
 * @param string $url    Référent.
 * @param array  $errors Messages d'erreur.
 * @param array  $old    Anciennes valeurs.
 * @return void
 */
function afsac_brochure_fail( $url, $errors, $old ) {
	if ( afsac_brochure_is_ajax() ) {
		wp_send_json_error( array( 'errors' => array_values( (array) $errors ) ) );
	}
	$token = wp_generate_password( 16, false );
	set_transient(
		'afsac_dlform_' . $token,
		array(
			'errors' => $errors,
			'old'    => $old,
		),
		10 * MINUTE_IN_SECONDS
	);
	wp_safe_redirect( afsac_brochure_redirect_url( $url, 'error', array( 'token' => $token ) ) );
	exit;
}

/**
 * Limite de débit par IP : 8 demandes par heure.
 *
 * Le formulaire est public et sans captcha ; sans plafond, un script pourrait
 * remplir la liste de fausses fiches et rendre le comptage inexploitable.
 *
 * @return bool True si la limite est atteinte.
 */
function afsac_brochure_rate_limited() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( '' === $ip ) {
		return false;
	}
	$key   = 'afsac_dlrl_' . md5( $ip );
	$count = (int) get_transient( $key );
	if ( $count >= 8 ) {
		return true;
	}
	set_transient( $key, $count + 1, HOUR_IN_SECONDS );
	return false;
}

/**
 * Retrouve la fiche existante d'un e-mail (une personne = une fiche).
 *
 * Volontairement limité aux fiches PUBLIÉES : si une fiche a été mise à la
 * corbeille, une nouvelle demande doit réapparaître dans la liste plutôt que
 * d'incrémenter en silence des compteurs que plus personne ne voit.
 *
 * @param string $email E-mail assaini.
 * @return int ID de la fiche, 0 si aucune.
 */
function afsac_brochure_find_lead( $email ) {
	$found = get_posts(
		array(
			'post_type'              => 'afsac_telechargement',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'meta_key'               => AFSAC_DL_META . 'email', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- table de leads, volume faible.
			'meta_value'             => $email, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	return $found ? (int) $found[0] : 0;
}

/**
 * Traite la soumission du formulaire de brochure.
 *
 * @return void
 */
function afsac_brochure_handle_submit() {
	$referer = wp_get_referer();
	if ( ! $referer ) {
		$referer = home_url( '/' );
	}

	// 1) Nonce.
	if ( ! isset( $_POST['afsac_brochure_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afsac_brochure_nonce'] ) ), 'afsac_brochure_submit' ) ) {
		afsac_brochure_fail( $referer, array( __( 'Votre session a expiré. Merci de renvoyer le formulaire.', 'afsac' ) ), array() );
	}

	// 2) Honeypot + 3) time-trap : on simule un succès pour ne pas renseigner les robots.
	$ts = isset( $_POST['afsac_ts'] ) ? absint( $_POST['afsac_ts'] ) : 0;
	if ( ! empty( $_POST['afsac_website'] ) || ( $ts > 0 && ( time() - $ts ) < 3 ) ) {
		if ( afsac_brochure_is_ajax() ) {
			wp_send_json_success( array() );
		}
		wp_safe_redirect( afsac_brochure_redirect_url( $referer, 'ok' ) );
		exit;
	}

	// 4) Récupération + assainissement.
	$f = array(
		'nom'          => isset( $_POST['afsac_dl_nom'] ) ? sanitize_text_field( wp_unslash( $_POST['afsac_dl_nom'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce vérifié.
		'email'        => isset( $_POST['afsac_dl_email'] ) ? sanitize_email( wp_unslash( $_POST['afsac_dl_email'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'organisation' => isset( $_POST['afsac_dl_organisation'] ) ? sanitize_text_field( wp_unslash( $_POST['afsac_dl_organisation'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'pays'         => isset( $_POST['afsac_dl_pays'] ) ? sanitize_text_field( wp_unslash( $_POST['afsac_dl_pays'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
	);
	$key     = isset( $_POST['afsac_dl_edition'] ) ? sanitize_key( wp_unslash( $_POST['afsac_dl_edition'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$consent = ! empty( $_POST['afsac_consent_rgpd'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	// L'édition demandée doit exister : sinon on retombe sur la langue de navigation.
	if ( ! afsac_brochure_file( $key ) ) {
		$key = afsac_brochure_default_key();
	}

	/*
	 * 5) Validation serveur. Depuis le 08/09/2026, SEUL L'E-MAIL est requis :
	 * nom, organisation et pays sont enregistrés s'ils sont fournis (formulaires
	 * tiers), la case RGPD n'est plus exigée — le consentement est donné par la
	 * mention affichée sous le bouton, et horodaté ci-dessous comme avant.
	 */
	$errors = array();
	if ( '' === $f['email'] || ! is_email( $f['email'] ) ) {
		$errors[] = __( 'Une adresse e-mail valide est requise : c’est elle qui reçoit le lien de téléchargement.', 'afsac' );
	}
	if ( '' === $key ) {
		$errors[] = __( 'La brochure n’est pas disponible pour le moment. Merci de nous contacter.', 'afsac' );
	}

	if ( $errors ) {
		afsac_brochure_fail(
			$referer,
			$errors,
			array(
				'afsac_dl_nom'          => $f['nom'],
				'afsac_dl_email'        => $f['email'],
				'afsac_dl_organisation' => $f['organisation'],
				'afsac_dl_pays'         => $f['pays'],
				'afsac_dl_edition'      => $key,
				'afsac_consent_rgpd'    => $consent ? '1' : '',
			)
		);
	}

	if ( afsac_brochure_rate_limited() ) {
		afsac_brochure_fail( $referer, array( __( 'Trop de demandes envoyées depuis cette connexion. Merci de réessayer dans une heure.', 'afsac' ) ), array() );
	}

	// 6) Création OU mise à jour de la fiche (une personne = une fiche).
	$now      = current_time( 'mysql' );
	$title    = '' !== $f['nom'] ? $f['nom'] . ' — ' . $f['email'] : $f['email'];
	$lead_id  = afsac_brochure_find_lead( $f['email'] );
	$is_new   = ( 0 === $lead_id );

	if ( $is_new ) {
		$lead_id = wp_insert_post(
			array(
				'post_type'   => 'afsac_telechargement',
				'post_status' => 'publish',
				'post_title'  => $title,
			),
			true
		);
		if ( is_wp_error( $lead_id ) || ! $lead_id ) {
			afsac_brochure_fail( $referer, array( __( 'Une erreur technique est survenue. Merci de réessayer.', 'afsac' ) ), array() );
		}
		update_post_meta( $lead_id, AFSAC_DL_META . 'first_at', $now );
		update_post_meta( $lead_id, AFSAC_DL_META . 'downloads', 0 );
		update_post_meta( $lead_id, AFSAC_DL_META . 'demandes', 0 );
	} else {
		// Le nom/l'organisation peuvent avoir changé : on garde la saisie la plus récente.
		wp_update_post(
			array(
				'ID'         => $lead_id,
				'post_title' => $title,
			)
		);
	}

	foreach ( $f as $k => $v ) {
		update_post_meta( $lead_id, AFSAC_DL_META . $k, $v );
	}
	$file = afsac_brochure_file( $key );
	update_post_meta( $lead_id, AFSAC_DL_META . 'edition', $key );
	update_post_meta( $lead_id, AFSAC_DL_META . 'last_at', $now );
	update_post_meta( $lead_id, AFSAC_DL_META . 'demandes', (int) get_post_meta( $lead_id, AFSAC_DL_META . 'demandes', true ) + 1 );
	update_post_meta( $lead_id, AFSAC_DL_META . 'consent_rgpd_at', $now );
	update_post_meta( $lead_id, AFSAC_DL_META . 'ip', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	update_post_meta( $lead_id, AFSAC_DL_META . 'source', esc_url_raw( $referer ) );
	/*
	 * La langue vient du FORMULAIRE, pas de pll_current_language() : le handler
	 * tourne sur admin-post.php, où Polylang n'a pas de page à interroger et
	 * renverrait systématiquement une valeur vide.
	 */
	$lang = isset( $_POST['afsac_dl_lang'] ) ? sanitize_key( wp_unslash( $_POST['afsac_dl_lang'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce vérifié.
	if ( function_exists( 'pll_languages_list' ) && ! in_array( $lang, (array) pll_languages_list(), true ) ) {
		$lang = '';
	}
	update_post_meta( $lead_id, AFSAC_DL_META . 'lang', $lang );

	$token = afsac_brochure_issue_token( $lead_id, $key );

	$data = array_merge(
		$f,
		array(
			'edition'      => $key,
			'edition_name' => $file ? $file['name'] : $key,
			'consent_at'   => $now,
			'is_new'       => $is_new,
			'download_url' => afsac_brochure_download_url( $token ),
		)
	);

	/**
	 * Point d'extension : une demande de brochure vient d'être enregistrée.
	 *
	 * @param int   $lead_id ID du CPT afsac_telechargement.
	 * @param array $data    Données assainies + lien de téléchargement.
	 */
	do_action( 'afsac_brochure_created', $lead_id, $data );

	if ( afsac_brochure_is_ajax() ) {
		wp_send_json_success( array( 'download_url' => $data['download_url'] ) );
	}

	wp_safe_redirect( afsac_brochure_redirect_url( $referer, 'ok', array( 'dl' => $token ) ) );
	exit;
}
add_action( 'admin_post_afsac_brochure_submit', 'afsac_brochure_handle_submit' );
add_action( 'admin_post_nopriv_afsac_brochure_submit', 'afsac_brochure_handle_submit' );

/* -------------------------------------------------------------------------
 * 5. Livraison du fichier
 * ---------------------------------------------------------------------- */

/**
 * Sert le PDF après vérification du jeton, et compte le téléchargement.
 *
 * Le fichier est lu par tranches : un readfile() sur une brochure de plusieurs
 * mégaoctets ferait grimper la mémoire PHP inutilement.
 *
 * @return void
 */
function afsac_brochure_handle_download() {
	$token   = isset( $_GET['token'] ) ? afsac_brochure_clean_token( wp_unslash( $_GET['token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- le jeton EST le secret.
	$payload = afsac_brochure_read_token( $token );
	$file    = $payload ? afsac_brochure_file( $payload['key'] ) : null;

	if ( ! $file ) {
		wp_safe_redirect( afsac_brochure_redirect_url( home_url( '/' ), 'expired' ) );
		exit;
	}

	// Comptage : c'est CE compteur qui répond à « combien de téléchargements ? ».
	if ( ! empty( $payload['lead'] ) && 'afsac_telechargement' === get_post_type( $payload['lead'] ) ) {
		$lead = (int) $payload['lead'];
		update_post_meta( $lead, AFSAC_DL_META . 'downloads', (int) get_post_meta( $lead, AFSAC_DL_META . 'downloads', true ) + 1 );
		update_post_meta( $lead, AFSAC_DL_META . 'downloaded_at', current_time( 'mysql' ) );
	}

	afsac_brochure_send_file( $file );
}
add_action( 'admin_post_afsac_brochure_download', 'afsac_brochure_handle_download' );
add_action( 'admin_post_nopriv_afsac_brochure_download', 'afsac_brochure_handle_download' );

/**
 * Envoie un PDF de brochure en pièce jointe, par tranches, puis termine.
 *
 * Commun au lien à jeton et au téléchargement direct.
 *
 * @param array<string,mixed> $file Fichier (cf. afsac_brochure_files()).
 * @return void
 */
function afsac_brochure_send_file( $file ) {
	nocache_headers();
	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: attachment; filename="' . rawurlencode( $file['filename'] ) . '"' );
	header( 'Content-Length: ' . (int) $file['filesize'] );
	header( 'X-Content-Type-Options: nosniff' );

	if ( function_exists( 'ob_get_level' ) ) {
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}
	}

	$handle = fopen( $file['path'], 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- flux binaire, WP_Filesystem chargerait tout en mémoire.
	if ( $handle ) {
		while ( ! feof( $handle ) ) {
			echo fread( $handle, 262144 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- flux binaire.
			flush();
		}
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	}
	exit;
}

/* -------------------------------------------------------------------------
 * 5b. Téléchargement DIRECT (sans e-mail) — demande client du 08/09/2026
 * ---------------------------------------------------------------------- */

/**
 * URL de téléchargement direct d'une édition (lien de la bande du pied de page).
 *
 * On ne pointe pas sur le fichier lui-même : passer par admin-post.php permet
 * de servir le PDF en pièce jointe (jamais ouvert dans l'onglet) et de COMPTER
 * chaque téléchargement, sans aucune donnée personnelle.
 *
 * @param string $key Clé d'édition (« fr », « en », « all »).
 * @return string
 */
function afsac_brochure_direct_url( $key ) {
	return add_query_arg(
		array(
			'action'  => 'afsac_brochure_direct',
			'edition' => sanitize_key( $key ),
		),
		admin_url( 'admin-post.php' )
	);
}

/**
 * Compteurs anonymes de téléchargements directs, par édition.
 *
 * @return array<string,int> Clé d'édition => total.
 */
function afsac_brochure_direct_counts() {
	$counts = get_option( 'afsac_brochure_direct_counts', array() );
	return is_array( $counts ) ? array_map( 'intval', $counts ) : array();
}

/**
 * Sert le PDF de l'édition demandée (ou de la langue de navigation) et compte.
 *
 * @return void
 */
function afsac_brochure_handle_direct() {
	$key  = isset( $_GET['edition'] ) ? sanitize_key( wp_unslash( $_GET['edition'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lien public, aucune donnée modifiée hors un compteur.
	$file = afsac_brochure_file( $key );
	if ( ! $file ) {
		$key  = afsac_brochure_default_key();
		$file = afsac_brochure_file( $key );
	}
	if ( ! $file ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	$counts         = afsac_brochure_direct_counts();
	$counts[ $key ] = ( isset( $counts[ $key ] ) ? $counts[ $key ] : 0 ) + 1;
	update_option( 'afsac_brochure_direct_counts', $counts, false );

	afsac_brochure_send_file( $file );
}
add_action( 'admin_post_afsac_brochure_direct', 'afsac_brochure_handle_direct' );
add_action( 'admin_post_nopriv_afsac_brochure_direct', 'afsac_brochure_handle_direct' );

/* -------------------------------------------------------------------------
 * 6. Statistiques
 * ---------------------------------------------------------------------- */

/**
 * Chiffres de suivi de la brochure (personnes, demandes, téléchargements).
 *
 * Une seule requête agrégée : la liste peut atteindre plusieurs milliers de
 * fiches, les additionner en PHP après un get_posts() serait coûteux.
 *
 * @return array{personnes:int,demandes:int,telechargements:int,mois:int,convertis:int,editions:array<string,int>}
 */
function afsac_brochure_stats() {
	global $wpdb;

	$sql = $wpdb->prepare(
		"SELECT COUNT(*) AS personnes,
			COALESCE( SUM( CAST( dem.meta_value AS UNSIGNED ) ), 0 ) AS demandes,
			COALESCE( SUM( CAST( dl.meta_value AS UNSIGNED ) ), 0 ) AS telechargements,
			SUM( CASE WHEN CAST( dl.meta_value AS UNSIGNED ) > 0 THEN 1 ELSE 0 END ) AS convertis,
			SUM( CASE WHEN p.post_date >= %s THEN 1 ELSE 0 END ) AS mois
		FROM {$wpdb->posts} p
		LEFT JOIN {$wpdb->postmeta} dem ON dem.post_id = p.ID AND dem.meta_key = %s
		LEFT JOIN {$wpdb->postmeta} dl  ON dl.post_id  = p.ID AND dl.meta_key  = %s
		WHERE p.post_type = 'afsac_telechargement' AND p.post_status = 'publish'",
		wp_date( 'Y-m-01 00:00:00' ),
		AFSAC_DL_META . 'demandes',
		AFSAC_DL_META . 'downloads'
	);

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- agrégat préparé ci-dessus.
	$row = $wpdb->get_row( $sql, ARRAY_A );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$editions = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT m.meta_value AS edition, COUNT(*) AS total
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = %s
			WHERE p.post_type = 'afsac_telechargement' AND p.post_status = 'publish'
			GROUP BY m.meta_value",
			AFSAC_DL_META . 'edition'
		),
		ARRAY_A
	);

	$by_edition = array();
	foreach ( (array) $editions as $edition ) {
		$by_edition[ (string) $edition['edition'] ] = (int) $edition['total'];
	}

	return array(
		'personnes'       => isset( $row['personnes'] ) ? (int) $row['personnes'] : 0,
		'demandes'        => isset( $row['demandes'] ) ? (int) $row['demandes'] : 0,
		'telechargements' => isset( $row['telechargements'] ) ? (int) $row['telechargements'] : 0,
		'convertis'       => isset( $row['convertis'] ) ? (int) $row['convertis'] : 0,
		'mois'            => isset( $row['mois'] ) ? (int) $row['mois'] : 0,
		'editions'        => $by_edition,
	);
}

/* -------------------------------------------------------------------------
 * 7. Écran d'administration
 * ---------------------------------------------------------------------- */

/**
 * Colonnes de la liste des demandes.
 *
 * @param array $cols Colonnes.
 * @return array
 */
function afsac_telechargement_columns( $cols ) {
	return array(
		'cb'                => isset( $cols['cb'] ) ? $cols['cb'] : '',
		'title'             => __( 'Personne', 'afsac' ),
		'afsac_dl_email'    => __( 'E-mail', 'afsac' ),
		'afsac_dl_org'      => __( 'Organisation', 'afsac' ),
		'afsac_dl_edition'  => __( 'Édition', 'afsac' ),
		'afsac_dl_counts'   => __( 'Demandes / Téléch.', 'afsac' ),
		'date'              => isset( $cols['date'] ) ? $cols['date'] : __( 'Date', 'afsac' ),
	);
}
add_filter( 'manage_afsac_telechargement_posts_columns', 'afsac_telechargement_columns' );

/**
 * Contenu des colonnes personnalisées.
 *
 * @param string $col     Colonne.
 * @param int    $post_id ID de la fiche.
 * @return void
 */
function afsac_telechargement_column_content( $col, $post_id ) {
	switch ( $col ) {
		case 'afsac_dl_email':
			$email = (string) get_post_meta( $post_id, AFSAC_DL_META . 'email', true );
			if ( $email ) {
				printf( '<a href="%s">%s</a>', esc_url( 'mailto:' . $email ), esc_html( $email ) );
			}
			break;

		case 'afsac_dl_org':
			echo esc_html( (string) get_post_meta( $post_id, AFSAC_DL_META . 'organisation', true ) );
			$pays = (string) get_post_meta( $post_id, AFSAC_DL_META . 'pays', true );
			if ( $pays ) {
				echo '<br><span style="color:#8c8f94;">' . esc_html( $pays ) . '</span>';
			}
			break;

		case 'afsac_dl_edition':
			$file = afsac_brochure_file( (string) get_post_meta( $post_id, AFSAC_DL_META . 'edition', true ) );
			echo esc_html( $file ? $file['code'] : '—' );
			break;

		case 'afsac_dl_counts':
			$downloads = (int) get_post_meta( $post_id, AFSAC_DL_META . 'downloads', true );
			printf(
				'<strong>%s</strong> / <strong style="color:%s;">%s</strong>',
				esc_html( number_format_i18n( (int) get_post_meta( $post_id, AFSAC_DL_META . 'demandes', true ) ) ),
				$downloads > 0 ? '#0054a4' : '#b32d2e',
				esc_html( number_format_i18n( $downloads ) )
			);
			if ( 0 === $downloads ) {
				echo '<br><span style="color:#8c8f94;">' . esc_html__( 'Jamais téléchargé', 'afsac' ) . '</span>';
			}
			break;
	}
}
add_action( 'manage_afsac_telechargement_posts_custom_column', 'afsac_telechargement_column_content', 10, 2 );

/**
 * Tri par nombre de téléchargements.
 *
 * @param array $cols Colonnes triables.
 * @return array
 */
function afsac_telechargement_sortable( $cols ) {
	$cols['afsac_dl_counts'] = 'afsac_dl_counts';
	return $cols;
}
add_filter( 'manage_edit-afsac_telechargement_sortable_columns', 'afsac_telechargement_sortable' );

/**
 * Applique le tri numérique sur le compteur de téléchargements.
 *
 * @param WP_Query $query Requête d'admin.
 * @return void
 */
function afsac_telechargement_orderby( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'afsac_dl_counts' !== $query->get( 'orderby' ) ) {
		return;
	}
	$query->set( 'meta_key', AFSAC_DL_META . 'downloads' );
	$query->set( 'orderby', 'meta_value_num' );
}
add_action( 'pre_get_posts', 'afsac_telechargement_orderby' );

/**
 * Tableau de bord affiché AU-DESSUS de la liste des demandes.
 *
 * C'est la réponse directe à la demande du client (« savoir qui et combien de
 * personnes ont téléchargé ») : sans ce résumé, il faudrait compter les lignes.
 *
 * @return void
 */
function afsac_telechargement_dashboard() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-afsac_telechargement' !== $screen->id ) {
		return;
	}

	$stats = afsac_brochure_stats();
	$taux  = $stats['personnes'] > 0 ? round( $stats['convertis'] / $stats['personnes'] * 100 ) : 0;

	$direct       = afsac_brochure_direct_counts();
	$direct_total = array_sum( $direct );

	$tiles = array(
		array( __( 'Téléchargements directs', 'afsac' ), number_format_i18n( $direct_total ), __( 'depuis le pied de page, sans e-mail (depuis le 08/09/2026)', 'afsac' ) ),
		array( __( 'Personnes', 'afsac' ), number_format_i18n( $stats['personnes'] ), __( 'fiches uniques (1 e-mail = 1 fiche)', 'afsac' ) ),
		array( __( 'Téléchargements', 'afsac' ), number_format_i18n( $stats['telechargements'] ), __( 'PDF réellement servis', 'afsac' ) ),
		array( __( 'Formulaires envoyés', 'afsac' ), number_format_i18n( $stats['demandes'] ), __( 'demandes, ré-envois compris', 'afsac' ) ),
		/* translators: %s : pourcentage. */
		array( __( 'Ce mois-ci', 'afsac' ), number_format_i18n( $stats['mois'] ), sprintf( __( '%s%% des fiches ont téléchargé', 'afsac' ), number_format_i18n( $taux ) ) ),
	);

	echo '<div class="afsac-dl-dash" style="display:flex;flex-wrap:wrap;gap:12px;margin:16px 0 4px;">';
	foreach ( $tiles as $tile ) {
		printf(
			'<div style="flex:1 1 180px;background:#fff;border:1px solid #dcdcde;border-left:4px solid #0054a4;border-radius:3px;padding:12px 16px;">
				<div style="font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:#546670;">%s</div>
				<div style="font-size:26px;font-weight:600;line-height:1.2;color:#0054a4;">%s</div>
				<div style="font-size:12px;color:#8c8f94;">%s</div>
			</div>',
			esc_html( $tile[0] ),
			esc_html( $tile[1] ),
			esc_html( $tile[2] )
		);
	}
	echo '</div>';

	// Répartition par édition + export.
	$parts = array();
	foreach ( $stats['editions'] as $key => $total ) {
		$file    = afsac_brochure_file( $key );
		$parts[] = sprintf( '%s : %s', $file ? $file['name'] : $key, number_format_i18n( $total ) );
	}

	$direct_parts = array();
	foreach ( $direct as $key => $total ) {
		$file           = afsac_brochure_file( $key );
		$direct_parts[] = sprintf( '%s : %s', $file ? $file['name'] : $key, number_format_i18n( $total ) );
	}

	echo '<p style="margin:4px 0 12px;">';
	if ( $direct_parts ) {
		echo '<span style="color:#546670;">' . esc_html__( 'Téléchargements directs par édition', 'afsac' ) . ' — ' . esc_html( implode( ' · ', $direct_parts ) ) . '</span><br>';
	}
	if ( $parts ) {
		echo '<span style="color:#546670;">' . esc_html__( 'Éditions demandées', 'afsac' ) . ' — ' . esc_html( implode( ' · ', $parts ) ) . '</span> &nbsp; ';
	}
	printf(
		'<a class="button" href="%s">%s</a>',
		esc_url(
			wp_nonce_url(
				admin_url( 'admin-post.php?action=afsac_brochure_export' ),
				'afsac_brochure_export'
			)
		),
		esc_html__( 'Exporter en CSV', 'afsac' )
	);
	echo '</p>';
}
add_action( 'all_admin_notices', 'afsac_telechargement_dashboard' );

/**
 * Export CSV de toutes les demandes (traçabilité hors WordPress).
 *
 * @return void
 */
function afsac_brochure_handle_export() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Action non autorisée.', 'afsac' ) );
	}
	check_admin_referer( 'afsac_brochure_export' );

	$leads = get_posts(
		array(
			'post_type'      => 'afsac_telechargement',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	$rows = array(
		array(
			__( 'Nom', 'afsac' ),
			__( 'E-mail', 'afsac' ),
			__( 'Organisation', 'afsac' ),
			__( 'Pays', 'afsac' ),
			__( 'Édition', 'afsac' ),
			__( 'Demandes', 'afsac' ),
			__( 'Téléchargements', 'afsac' ),
			__( 'Première demande', 'afsac' ),
			__( 'Dernière demande', 'afsac' ),
			__( 'Dernier téléchargement', 'afsac' ),
			__( 'Langue du site', 'afsac' ),
			__( 'Page d’origine', 'afsac' ),
			__( 'Consentement RGPD', 'afsac' ),
			__( 'Adresse IP', 'afsac' ),
		),
	);

	foreach ( $leads as $lead ) {
		$m    = function ( $key ) use ( $lead ) {
			return (string) get_post_meta( $lead->ID, AFSAC_DL_META . $key, true );
		};
		$file   = afsac_brochure_file( $m( 'edition' ) );
		$rows[] = array(
			$m( 'nom' ),
			$m( 'email' ),
			$m( 'organisation' ),
			$m( 'pays' ),
			$file ? $file['name'] : $m( 'edition' ),
			$m( 'demandes' ),
			$m( 'downloads' ),
			$m( 'first_at' ),
			$m( 'last_at' ),
			$m( 'downloaded_at' ),
			$m( 'lang' ),
			$m( 'source' ),
			$m( 'consent_rgpd_at' ),
			$m( 'ip' ),
		);
	}

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="afsac-brochure-' . wp_date( 'Y-m-d' ) . '.csv"' );

	$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- BOM pour Excel.
	foreach ( $rows as $row ) {
		fputcsv( $out, $row, ';' );
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	exit;
}
add_action( 'admin_post_afsac_brochure_export', 'afsac_brochure_handle_export' );

/**
 * Avis admin : aucune brochure téléversée → le formulaire ne peut rien livrer.
 *
 * @return void
 */
function afsac_brochure_missing_file_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-afsac_telechargement' !== $screen->id || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( afsac_brochure_files() ) {
		return;
	}
	$home_id = afsac_brochure_home_id();
	printf(
		'<div class="notice notice-error"><p>%s %s</p></div>',
		esc_html__( 'Aucun PDF de brochure n’est téléversé : la bande « Téléchargez la brochure » n’apparaît pas sur le site.', 'afsac' ),
		$home_id
			? sprintf(
				'<a href="%s">%s</a>',
				esc_url( (string) get_edit_post_link( $home_id ) ),
				esc_html__( 'Téléverser la brochure sur la page d’accueil', 'afsac' )
			)
			: ''
	);
}
add_action( 'admin_notices', 'afsac_brochure_missing_file_notice' );
