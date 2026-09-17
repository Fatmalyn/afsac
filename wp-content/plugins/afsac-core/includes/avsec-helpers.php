<?php
/**
 * AVSEC — structure éditoriale du programme de sûreté de l'aviation civile.
 *
 * AVSEC ne se range PAS dans les 11 domaines aéronautiques (ceux-ci appartiennent
 * à TRAINAIR PLUS, cf. area-helpers.php). Ses axes propres sont :
 *   1. la THÉMATIQUE de sûreté (6 axes, ci-dessous) ;
 *   2. la TYPOLOGIE — cours certifiant OACI vs atelier (workshop).
 *
 * Aucun de ces deux axes n'existe en base sous forme de taxonomie : ils sont
 * DÉRIVÉS de la clé d'import stable `_afsac_import_key` posée sur chaque fiche
 * par seed-formations.php. Ce fichier est donc la source unique du classement —
 * métier, donc plugin, jamais le thème (cf. CLAUDE.md).
 *
 * Un cours AVSEC sans clé connue (fiches saisies à la main) reste listé : il
 * tombe simplement dans « aucune thématique » et est typé par son titre.
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Les 6 axes thématiques AVSEC.
 *
 * `color` : STRICTEMENT deux bleus OACI, en damier (0054a4 / 1a6fc0 / 0054a4 sur
 * la première rangée, l'inverse sur la seconde) — jamais deux voisins de la même
 * teinte. La rampe précédente descendait jusqu'au navy #05356a et au noir encre :
 * le client l'a refusée le 06/08/2026 (« enlève ce bleu foncé »), le site devant
 * rester bleu et blanc. Les deux teintes dépassent 4,5:1 avec du blanc, ce que
 * réclame le titre en gras des aplats.
 *
 * @return array<string,array{label:string,desc:string,color:string,keys:array<int,string>}>
 */
function afsac_avsec_themes() {
	return array(
		'aeroport'       => array(
			'label' => __( 'Sûreté aéroportuaire', 'afsac' ),
			'desc'  => __( 'Personnel, superviseurs, côté ville et programme de sûreté d’aéroport.', 'afsac' ),
			'color' => '#0054a4',
			'keys'  => array( 'avsec-formation-base', 'avsec-superviseurs', 'avsec-psa', 'avsec-landside-security' ),
		),
		'cadre-national' => array(
			'label' => __( 'Cadre national & réglementaire', 'afsac' ),
			'desc'  => __( 'Programmes nationaux, contrôle qualité et systèmes de certification.', 'afsac' ),
			'color' => '#1a6fc0',
			'keys'  => array( 'avsec-pnsac', 'avsec-pnfsac', 'avsec-pcqsac', 'avsec-certification' ),
		),
		'inspection'     => array(
			'label' => __( 'Inspection & contrôle', 'afsac' ),
			'desc'  => __( 'Inspecteurs nationaux, imagerie radioscopique et maintenance des équipements.', 'afsac' ),
			'color' => '#0054a4',
			'keys'  => array( 'avsec-inspecteurs', 'avsec-imagerie', 'avsec-maintenance-equipements-surete', 'avsec-recyclage-inspecteurs' ),
		),
		'risques'        => array(
			'label' => __( 'Risques, crises & menace interne', 'afsac' ),
			'desc'  => __( 'Évaluation du risque, gestion de crise et risque d’origine interne.', 'afsac' ),
			'color' => '#1a6fc0',
			'keys'  => array( 'avsec-gestion-risques', 'avsec-gestion-crises', 'avsec-risque-interne' ),
		),
		'fret-fal'       => array(
			'label' => __( 'Fret, poste & facilitation', 'afsac' ),
			'desc'  => __( 'Chaîne d’approvisionnement sécurisée et Annexe 9 (FAL).', 'afsac' ),
			'color' => '#0054a4',
			'keys'  => array( 'avsec-fret-poste', 'avsec-facilitation' ),
		),
		'humain'         => array(
			'label' => __( 'Facteurs humains & encadrement', 'afsac' ),
			'desc'  => __( 'Culture de sûreté, détection comportementale, instructeurs et responsables.', 'afsac' ),
			'color' => '#1a6fc0',
			'keys'  => array( 'avsec-culture-surete', 'avsec-behaviour-detection', 'avsec-instructeurs', 'avsec-recyclage-instructeurs', 'avsec-responsables' ),
		),
	);
}

/**
 * Clés des fiches qui sont des ATELIERS OACI (le reste = cours certifiants).
 *
 * Le titre porte déjà l'information (« Atelier … » / « … Workshop »), mais s'y
 * fier seul rendrait le classement dépendant de la rédaction : la liste de clés
 * fait foi, le titre ne sert que de repli pour les fiches hors import.
 *
 * @return string[]
 */
function afsac_avsec_workshop_keys() {
	return array(
		'avsec-culture-surete',
		'avsec-gestion-crises',
		'avsec-gestion-risques',
		'avsec-pcqsac',
		'avsec-pnsac',
		'avsec-risque-interne',
		'avsec-certification',
		'avsec-psa',
		'avsec-pnfsac',
	);
}

/**
 * Clés des cours qui composent le PROGRAMME du centre (brochures 2026).
 *
 * La famille `afsac_famille = AVSEC` ne suffit pas à répondre à « qu'est-ce que
 * le centre dispense ? » : elle rassemble deux choses que le client distingue.
 *
 *   1. Le PROGRAMME — les cours des brochures « Programme des Formations
 *      AVSEC/OACI 2026 » (FR) et « Aviation Security Annual Training Program
 *      2026 » (EN). C'est l'offre du centre, celle qu'on affiche sur la page
 *      « Cours & ateliers AVSEC » et qu'on peut réserver.
 *   2. Le CATALOGUE de sûreté de l'OACI — d'autres cours AVSEC du catalogue
 *      mondial (Master of Science in Aviation Security, Catering Security…),
 *      présents dans le tableur du client. Ils restent consultables dans le
 *      catalogue et la recherche, mais le centre ne les programme pas.
 *
 * Le dataset de `afsac_import_courses()` (seed-formations.php) EST la liste du
 * programme : on la dérive plutôt que de la recopier, pour qu'ajouter un cours
 * au seed suffise à l'ajouter au programme.
 *
 * @return string[] Clés `_afsac_import_key` du programme.
 */
function afsac_avsec_programme_keys() {
	static $keys = null;
	if ( null !== $keys ) {
		return $keys;
	}
	$keys = function_exists( 'afsac_import_courses' )
		? array_values( array_filter( wp_list_pluck( afsac_import_courses(), 'key' ) ) )
		: array();
	return $keys;
}

/**
 * Slug du terme « famille = AVSEC » dans la langue courante.
 *
 * Les termes sont traduits par Polylang : le slug FR est « avsec-fr », le slug
 * EN « avsec ». Interroger avec le slug de l'autre langue renverrait 0 résultat.
 *
 * @return string
 */
function afsac_avsec_famille_slug() {
	$lang = function_exists( 'pll_current_language' ) ? pll_current_language() : 'fr';
	return ( 'en' === $lang ) ? 'avsec' : 'avsec-fr';
}

/**
 * Programme d'une fiche de formation : « trainair » ou « avsec ».
 *
 * L'offre du centre se lit d'abord par PROGRAMME — c'est la première question du
 * visiteur (« un cours OACI TRAINAIR PLUS ou une formation sûreté AVSEC ? »).
 * Seule la taxonomie `afsac_famille` porte cette information ; ses slugs étant
 * traduits par Polylang (`avsec` / `avsec-fr`, `trainair-plus` / `trainair-plus-fr`),
 * on normalise sur le PRÉFIXE du slug pour obtenir une clé stable dans les deux
 * langues — utilisable telle quelle en attribut `data-*` et en paramètre d'URL.
 *
 * @param int $post_id ID de la formation.
 * @return string « trainair » | « avsec » | '' (fiche sans famille).
 */
function afsac_formation_programme( $post_id ) {
	$post_id = (int) $post_id;
	if ( ! $post_id ) {
		return '';
	}

	$terms = get_the_terms( $post_id, 'afsac_famille' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}

	foreach ( $terms as $term ) {
		if ( 0 === strpos( $term->slug, 'avsec' ) ) {
			return 'avsec';
		}
		if ( 0 === strpos( $term->slug, 'trainair' ) ) {
			return 'trainair';
		}
	}

	return '';
}

/**
 * Libellés des deux programmes. Noms propres : identiques en FR et en EN, mais
 * centralisés ici pour que les templates n'en codent aucun en dur.
 *
 * @return array<string,string> Clé de programme => libellé.
 */
function afsac_programme_labels() {
	return array(
		'trainair' => 'TRAINAIR PLUS',
		'avsec'    => 'AVSEC',
	);
}

/**
 * Thématique d'une clé d'import.
 *
 * @param string $key Clé `_afsac_import_key`.
 * @return string Slug de thématique, ou '' si la clé n'est rattachée à aucune.
 */
function afsac_avsec_theme_of_key( $key ) {
	if ( '' === (string) $key ) {
		return '';
	}
	foreach ( afsac_avsec_themes() as $slug => $theme ) {
		if ( in_array( $key, $theme['keys'], true ) ) {
			return $slug;
		}
	}
	return '';
}

/**
 * Typologie d'une fiche : « atelier » ou « cours ».
 *
 * @param string $key   Clé d'import (peut être vide).
 * @param string $title Titre de la fiche (repli pour les fiches hors import).
 * @return string « atelier » | « cours »
 */
function afsac_avsec_kind_of( $key, $title = '' ) {
	if ( '' !== (string) $key && in_array( $key, afsac_avsec_workshop_keys(), true ) ) {
		return 'atelier';
	}
	if ( '' === (string) $key && preg_match( '/(atelier|workshop)/i', (string) $title ) ) {
		return 'atelier';
	}
	return 'cours';
}

/**
 * Les cours du PROGRAMME AVSEC publiés dans la langue courante, prêts à l'affichage.
 *
 * Le filtre porte sur la FAMILLE (et non sur la langue Polylang) : c'est le seul
 * critère qui sépare proprement AVSEC de TRAINAIR PLUS, et le slug de famille
 * étant lui-même traduit, l'ensemble retourné est déjà cohérent avec la langue.
 *
 * S'y ajoute le filtre du PROGRAMME (cf. afsac_avsec_programme_keys) : les cours
 * de sûreté du catalogue mondial que le centre ne dispense pas sont écartés de
 * cette liste — ils restent visibles dans le catalogue et la recherche.
 *
 * Tri : thématique (ordre de afsac_avsec_themes), puis cours avant ateliers,
 * puis titre — les fiches sans thématique ferment la liste.
 *
 * @return array<int,array{id:int,title:string,url:string,duree:string,key:string,kind:string,theme:string}>
 */
function afsac_avsec_get_courses() {
	$query = new WP_Query(
		array(
			'post_type'              => 'afsac_formation',
			'post_status'            => 'publish',
			'posts_per_page'         => 100,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'afsac_famille',
					'field'    => 'slug',
					'terms'    => array( afsac_avsec_famille_slug() ),
				),
			),
		)
	);

	$order     = array_keys( afsac_avsec_themes() );
	$programme = afsac_avsec_programme_keys();
	$items     = array();

	foreach ( $query->posts as $post ) {
		$key = (string) get_post_meta( $post->ID, '_afsac_import_key', true );

		// Hors programme du centre (cours de sûreté du catalogue mondial) : écarté.
		if ( ! empty( $programme ) && ! in_array( $key, $programme, true ) ) {
			continue;
		}

		$theme = afsac_avsec_theme_of_key( $key );
		$duree = function_exists( 'get_field' )
			? (string) get_field( 'afsac_duree', $post->ID )
			: (string) get_post_meta( $post->ID, 'afsac_duree', true );

		$items[] = array(
			'id'    => (int) $post->ID,
			// Titre DÉCODÉ : get_the_title() renvoie « &#038; », que les templates
			// ré-échapperaient en « &amp;#038; ». On rend une chaîne brute, à
			// échapper une seule fois à l'affichage.
			'title' => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			'url'   => (string) get_permalink( $post ),
			'duree' => $duree,
			'key'   => $key,
			'kind'  => afsac_avsec_kind_of( $key, $post->post_title ),
			'theme' => $theme,
		);
	}

	usort(
		$items,
		static function ( $a, $b ) use ( $order ) {
			// Les fiches sans thématique passent en fin de liste.
			$ia = ( '' === $a['theme'] ) ? count( $order ) : array_search( $a['theme'], $order, true );
			$ib = ( '' === $b['theme'] ) ? count( $order ) : array_search( $b['theme'], $order, true );
			if ( $ia !== $ib ) {
				return $ia < $ib ? -1 : 1;
			}
			if ( $a['kind'] !== $b['kind'] ) {
				return ( 'cours' === $a['kind'] ) ? -1 : 1;
			}
			return strnatcasecmp( $a['title'], $b['title'] );
		}
	);

	return $items;
}

/**
 * Compteurs AVSEC pour la bannière et les aplats : total, cours, ateliers, par thème.
 *
 * @param array|null $courses Liste déjà calculée (évite une 2ᵉ requête). Défaut : la recalcule.
 * @return array{total:int,cours:int,ateliers:int,themes:array<string,int>}
 */
function afsac_avsec_stats( $courses = null ) {
	$courses = is_array( $courses ) ? $courses : afsac_avsec_get_courses();

	$stats = array(
		'total'    => count( $courses ),
		'cours'    => 0,
		'ateliers' => 0,
		'themes'   => array_fill_keys( array_keys( afsac_avsec_themes() ), 0 ),
	);

	foreach ( $courses as $c ) {
		if ( 'atelier' === $c['kind'] ) {
			$stats['ateliers']++;
		} else {
			$stats['cours']++;
		}
		if ( '' !== $c['theme'] && isset( $stats['themes'][ $c['theme'] ] ) ) {
			$stats['themes'][ $c['theme'] ]++;
		}
	}

	return $stats;
}

/**
 * Prochaines sessions programmées sur une formation AVSEC (langue courante).
 *
 * Même règle de filtrage que afsac_get_formation_sessions_for_display() :
 * statut « archive » exclu, et session retenue tant que sa date de FIN (à défaut
 * sa date de début) n'est pas passée. La relation session → formation stocke
 * l'ID CANONIQUE (langue par défaut) : on résout donc les IDs canoniques des
 * fiches AVSEC avant d'interroger, en une seule requête (pas de N+1).
 *
 * @param int $limit Nombre maximum de sessions retournées.
 * @return array<int,array{id:int,debut:int,fin:int,lieu:string,statut:string,formation:WP_Post|null,url:string}>
 */
function afsac_avsec_next_sessions( $limit = 3 ) {
	$courses = afsac_avsec_get_courses();
	if ( empty( $courses ) ) {
		return array();
	}

	$canonical = array();
	foreach ( $courses as $c ) {
		$cid               = function_exists( 'afsac_get_default_lang_id' ) ? (int) afsac_get_default_lang_id( $c['id'] ) : $c['id'];
		$canonical[ $cid ] = $cid;
	}

	$sessions = get_posts(
		array(
			'post_type'      => 'afsac_session',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => 'meta_value',
			'meta_key'       => 'afsac_date_debut', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'order'          => 'ASC',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => defined( 'AFSAC_SESSION_FORMATION_META' ) ? AFSAC_SESSION_FORMATION_META : '_afsac_formation_id',
					'value'   => array_values( $canonical ),
					'compare' => 'IN',
					'type'    => 'NUMERIC',
				),
			),
		)
	);

	$today = (int) current_time( 'Ymd' );
	$out   = array();

	foreach ( $sessions as $session ) {
		if ( 'archive' === get_post_meta( $session->ID, 'afsac_statut', true ) ) {
			continue;
		}

		$debut = (int) get_post_meta( $session->ID, 'afsac_date_debut', true );
		$fin   = (int) get_post_meta( $session->ID, 'afsac_date_fin', true );
		$ref   = $fin > 0 ? $fin : $debut;
		if ( $ref > 0 && $ref < $today ) {
			continue;
		}

		$formation = function_exists( 'afsac_get_session_formation_localized' )
			? afsac_get_session_formation_localized( $session->ID )
			: null;

		$out[] = array(
			'id'        => (int) $session->ID,
			'debut'     => $debut,
			'fin'       => $fin,
			'lieu'      => (string) get_post_meta( $session->ID, 'afsac_lieu', true ),
			'statut'    => (string) get_post_meta( $session->ID, 'afsac_statut', true ),
			'formation' => $formation,
			'url'       => $formation ? (string) get_permalink( $formation ) : '',
		);

		if ( count( $out ) >= (int) $limit ) {
			break;
		}
	}

	return $out;
}
