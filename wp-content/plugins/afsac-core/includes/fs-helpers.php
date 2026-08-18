<?php
/**
 * Helpers de présentation pour le hub « Formations & Services ».
 *
 * Lecture seule (pas d'enregistrement de logique métier ici, juste l'accès aux
 * données déjà définies : champs ACF du hub + taxonomie des domaines OACI).
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lit un champ ACF du hub Formations & Services depuis la page CANONIQUE
 * (langue par défaut). Permet de régler les valeurs une seule fois (sur la page
 * FR) et de les afficher en FR comme en EN.
 *
 * @param string $name Nom du champ (ex. « afsac_course_count »).
 * @return mixed Valeur du champ, ou null si indisponible.
 */
function afsac_get_fs_field( $name ) {
	$page_id = (int) get_queried_object_id();
	if ( ! $page_id ) {
		return null;
	}
	if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_default_language' ) ) {
		$canonical = pll_get_post( $page_id, pll_default_language() );
		if ( $canonical ) {
			$page_id = (int) $canonical;
		}
	}
	return function_exists( 'get_field' ) ? get_field( $name, $page_id ) : null;
}

/**
 * Résout l'ID de la traduction en langue PAR DÉFAUT d'un contenu.
 *
 * Factorise le motif pll_get_post( $id, pll_default_language() ). Permet aux
 * vues d'une langue secondaire (ex. fiche cours EN) de retomber sur l'ID
 * canonique (FR) qui porte les relations/réglages.
 *
 * @param int $post_id ID du contenu courant.
 * @return int ID canonique (langue par défaut), ou $post_id si indisponible.
 */
function afsac_get_default_lang_id( $post_id ) {
	$post_id = (int) $post_id;
	if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_default_language' ) ) {
		$default = pll_get_post( $post_id, pll_default_language() );
		if ( $default ) {
			return (int) $default;
		}
	}
	return $post_id;
}

/**
 * Nombre de domaines OACI = termes de PREMIER NIVEAU de la taxonomie « afsac_area »
 * DANS LA LANGUE PAR DÉFAUT. Polylang stocke les termes par langue (11 FR +
 * 11 EN) ; on compte une seule langue pour obtenir le périmètre réel (11),
 * et non le total cumulé (22).
 *
 * Le `parent => 0` est INDISPENSABLE : sans lui, les sous-domaines étaient
 * comptés et la page « Formations & Services » annonçait 14 domaines, alors que
 * le catalogue en affichait 11 (il utilise afsac_get_area_domains(), déjà filtré).
 *
 * @return int Nombre de domaines.
 */
function afsac_get_domains_count() {
	$args = array(
		'taxonomy'   => 'afsac_area',
		'parent'     => 0,
		'hide_empty' => false,
		'fields'     => 'count',
	);
	if ( function_exists( 'pll_default_language' ) ) {
		$args['lang'] = pll_default_language();
	}
	$count = get_terms( $args );
	return is_wp_error( $count ) ? 0 : (int) $count;
}

/**
 * URL du hub « Formations & Services » (gabarit template-formations-services.php)
 * dans la LANGUE COURANTE.
 *
 * @return string Permalien du hub, ou '' si aucune page ne porte le gabarit.
 */
function afsac_get_hub_url() {
	$pages = get_posts(
		array(
			'post_type'        => 'page',
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'meta_key'         => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'       => 'template-formations-services.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'suppress_filters' => false,
		)
	);

	return ! empty( $pages ) ? (string) get_permalink( (int) $pages[0] ) : '';
}

/**
 * Lit un champ ACF du hub Formations & Services depuis N'IMPORTE QUELLE page.
 *
 * Variante de afsac_get_fs_field() qui localise d'abord la page portant le
 * template du hub (langue courante via suppress_filters=false), puis lit le champ
 * sur sa page CANONIQUE (langue par défaut). Permet aux autres gabarits (ex.
 * Catalogue) de réutiliser les valeurs éditoriales du hub. Sur le hub lui-même,
 * le résultat est identique à afsac_get_fs_field().
 *
 * @param string $name Nom du champ.
 * @return mixed Valeur du champ, ou null si indisponible.
 */
function afsac_get_hub_field( $name ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}

	$hub = get_posts(
		array(
			'post_type'        => 'page',
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'meta_key'         => '_wp_page_template',
			'meta_value'       => 'template-formations-services.php',
			'suppress_filters' => false,
		)
	);

	if ( empty( $hub ) ) {
		return null;
	}

	$page_id = afsac_get_default_lang_id( (int) $hub[0] );

	return get_field( $name, $page_id );
}

/**
 * URL de la page « Catalogue » dans la LANGUE COURANTE.
 *
 * Localise la page portant le template Catalogue (suppress_filters=false laisse
 * Polylang cibler la traduction de la langue courante : FR sur /fr, EN sur /en).
 *
 * @return string Permalien de la page Catalogue, ou '' si introuvable.
 */
function afsac_get_catalogue_url() {
	$pages = get_posts(
		array(
			'post_type'        => 'page',
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'meta_key'         => '_wp_page_template',
			'meta_value'       => 'template-catalogue.php',
			'suppress_filters' => false,
		)
	);

	if ( empty( $pages ) ) {
		return '';
	}

	return (string) get_permalink( (int) $pages[0] );
}

/**
 * URL de la page « Inscription participant » (gabarit template-inscription.php),
 * dans la langue courante. '' si aucune page ne l'utilise.
 *
 * @return string
 */
function afsac_get_inscription_url() {
	$pages = get_posts(
		array(
			'post_type'        => 'page',
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'meta_key'         => '_wp_page_template',
			'meta_value'       => 'template-inscription.php',
			'suppress_filters' => false,
		)
	);

	return ! empty( $pages ) ? (string) get_permalink( (int) $pages[0] ) : '';
}

/**
 * URL de la page « Contact » (gabarit template-contact.php), langue courante.
 *
 * @return string '' si aucune page ne l'utilise.
 */
function afsac_get_contact_url() {
	$pages = get_posts(
		array(
			'post_type'        => 'page',
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'meta_key'         => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'       => 'template-contact.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'suppress_filters' => false,
		)
	);

	return ! empty( $pages ) ? (string) get_permalink( (int) $pages[0] ) : home_url( '/' );
}

/**
 * URL de la page « Calendrier des sessions » (gabarit template-calendrier.php).
 *
 * @return string '' si aucune page ne l'utilise.
 */
function afsac_get_calendar_url() {
	$pages = get_posts(
		array(
			'post_type'        => 'page',
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'meta_key'         => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'       => 'template-calendrier.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'suppress_filters' => false,
		)
	);

	return ! empty( $pages ) ? (string) get_permalink( (int) $pages[0] ) : home_url( '/' );
}

/**
 * URL de la FICHE COURS (formation) d'une session, dans la langue courante.
 *
 * @param int $sid ID de la session.
 * @return string '' si aucune formation liée.
 */
function afsac_get_session_formation_url( $sid ) {
	$fid = function_exists( 'afsac_get_session_formation_id' ) ? afsac_get_session_formation_id( (int) $sid ) : 0;
	if ( ! $fid ) {
		return '';
	}
	if ( function_exists( 'pll_get_post' ) ) {
		$tr = pll_get_post( (int) $fid );
		if ( $tr ) {
			$fid = (int) $tr;
		}
	}
	return (string) get_permalink( (int) $fid );
}

/**
 * ID de la formation liée à une session, résolu dans la LANGUE COURANTE.
 *
 * La relation (`_afsac_formation_id`) stocke l'ID CANONIQUE (langue par défaut,
 * FR) : pour l'AFFICHAGE (titre, champs, terms) il faut basculer sur la
 * traduction de langue courante, sinon une fiche EN montre le titre FR. À
 * défaut de traduction, on retombe sur l'ID canonique (dégradation propre).
 *
 * @param int $sid ID de la session.
 * @return int ID de la formation en langue courante, ou 0 si aucune.
 */
function afsac_get_session_formation_id_localized( $sid ) {
	$fid = function_exists( 'afsac_get_session_formation_id' ) ? afsac_get_session_formation_id( (int) $sid ) : 0;
	if ( ! $fid ) {
		return 0;
	}
	if ( function_exists( 'pll_get_post' ) ) {
		$tr = pll_get_post( (int) $fid );
		if ( $tr ) {
			$fid = (int) $tr;
		}
	}
	return (int) $fid;
}

/**
 * Objet formation lié à une session, résolu dans la LANGUE COURANTE (Polylang).
 *
 * Pendant « affichage » de afsac_get_session_formation() (qui renvoie l'ID
 * canonique brut) : à utiliser dans les templates pour titre/lien/champs.
 *
 * @param int $sid ID de la session.
 * @return WP_Post|null Formation en langue courante, ou null si aucune.
 */
function afsac_get_session_formation_localized( $sid ) {
	$fid = afsac_get_session_formation_id_localized( (int) $sid );
	if ( ! $fid ) {
		return null;
	}
	$formation = get_post( $fid );
	return ( $formation instanceof WP_Post && 'afsac_formation' === $formation->post_type ) ? $formation : null;
}

/**
 * URL d'une page par slug, résolue dans la LANGUE COURANTE (Polylang).
 *
 * Trouve la page par son slug (n'importe quelle langue) puis bascule sur sa
 * traduction de langue courante. Évite tout slug en dur cassant l'EN.
 *
 * @param string $slug Slug de la page (ex. « espace-participant »).
 * @return string URL localisée, ou '' si introuvable.
 */
function afsac_localized_page_url( $slug ) {
	$page = get_page_by_path( sanitize_title( $slug ) );
	if ( ! $page instanceof WP_Post ) {
		return '';
	}
	$id = (int) $page->ID;
	if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_current_language' ) && pll_current_language() ) {
		$tr = pll_get_post( $id, pll_current_language() );
		if ( $tr ) {
			$id = (int) $tr;
		}
	}
	return (string) get_permalink( $id );
}
