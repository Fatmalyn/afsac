<?php
/**
 * Intégration Rank Math : libellé du fil d'Ariane.
 *
 * Présentation côté thème (CSS). Ici, uniquement la logique métier i18n :
 * traduire le libellé du crumb d'accueil ("Home" → "Accueil"/"Home"), sans
 * toucher aux autres crumbs ni à l'URL du lien d'accueil (qui doit rester
 * l'accueil de la langue courante, géré par home_url() + Polylang).
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Localise le libellé du premier crumb (accueil) du fil d'Ariane Rank Math.
 *
 * Rank Math ajoute le crumb d'accueil EN PREMIER (maybe_add_home_crumb), et
 * uniquement si l'option « general.breadcrumbs_home » est active. On réétiquette
 * donc $crumbs[0] précisément sous cette condition : c'est le seul cas où le
 * premier item est l'accueil. On ne touche ni aux autres crumbs ni à l'URL du
 * lien d'accueil (laissée telle quelle par Rank Math / Polylang). Le garde ne
 * dépend pas de home_url() — fiable indépendamment de la langue courante.
 *
 * @param array $crumbs Items du fil d'Ariane ([0]=>libellé, [1]=>lien).
 * @return array Items éventuellement modifiés.
 */
function afsac_breadcrumb_home_label( $crumbs ) {
	if ( empty( $crumbs ) || ! class_exists( '\RankMath\Helper' ) ) {
		return $crumbs;
	}

	if ( \RankMath\Helper::get_settings( 'general.breadcrumbs_home' ) ) {
		$crumbs[0][0] = __( 'Accueil', 'afsac' );
	}

	return $crumbs;
}
add_filter( 'rank_math/frontend/breadcrumb/items', 'afsac_breadcrumb_home_label' );

/**
 * Fil d'Ariane des archives de domaine OACI (taxonomy afsac_area) :
 * Accueil › Formations & Services › Catalogue OACI › [Domaine].
 *
 * Remplace le crumb intermédiaire par défaut (« Areas OACI ») par les deux pages
 * de la rubrique. N'affecte que is_tax('afsac_area') ; les autres types restent
 * inchangés. S'exécute après afsac_breadcrumb_home_label (accueil déjà localisé).
 *
 * @param array $crumbs Items ([0]=>libellé, [1]=>lien).
 * @return array
 */
function afsac_breadcrumb_area_trail( $crumbs ) {
	if ( empty( $crumbs ) || ! is_tax( 'afsac_area' ) ) {
		return $crumbs;
	}

	$home = $crumbs[0];
	$term = end( $crumbs );
	reset( $crumbs );

	$middle = array();

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
	if ( ! empty( $hub ) ) {
		$middle[] = array( __( 'Formations & Services', 'afsac' ), get_permalink( (int) $hub[0] ) );
	}

	$cat_url = function_exists( 'afsac_get_catalogue_url' ) ? afsac_get_catalogue_url() : '';
	if ( $cat_url ) {
		$middle[] = array( __( 'Catalogue OACI', 'afsac' ), $cat_url );
	}

	return array_merge( array( $home ), $middle, array( $term ) );
}
add_filter( 'rank_math/frontend/breadcrumb/items', 'afsac_breadcrumb_area_trail' );

/**
 * Fil d'Ariane du détail d'un cours : Accueil › Formations & Services ›
 * Catalogue OACI › [Domaine du cours] › [Cours]. N'affecte que
 * is_singular('afsac_formation') ; les autres types restent inchangés.
 *
 * @param array $crumbs Items ([0]=>libellé, [1]=>lien).
 * @return array
 */
function afsac_breadcrumb_course_trail( $crumbs ) {
	if ( empty( $crumbs ) || ! is_singular( 'afsac_formation' ) ) {
		return $crumbs;
	}

	$id   = get_queried_object_id();
	$home = $crumbs[0];
	$last = end( $crumbs );
	reset( $crumbs );

	$trail = array( $home );

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
	if ( ! empty( $hub ) ) {
		$trail[] = array( __( 'Formations & Services', 'afsac' ), get_permalink( (int) $hub[0] ) );
	}

	$cat_url = function_exists( 'afsac_get_catalogue_url' ) ? afsac_get_catalogue_url() : '';
	if ( $cat_url ) {
		$trail[] = array( __( 'Catalogue OACI', 'afsac' ), $cat_url );
	}

	// Domaine (terme area parent) du cours.
	$areas  = get_the_terms( $id, 'afsac_area' );
	$domain = null;
	if ( $areas && ! is_wp_error( $areas ) ) {
		foreach ( $areas as $t ) {
			if ( 0 === (int) $t->parent ) {
				$domain = $t;
				break;
			}
			if ( ! $domain && (int) $t->parent > 0 ) {
				$p = get_term( $t->parent, 'afsac_area' );
				if ( $p && ! is_wp_error( $p ) ) {
					$domain = $p;
				}
			}
		}
	}
	if ( $domain ) {
		$link    = get_term_link( $domain );
		$trail[] = array( $domain->name, is_wp_error( $link ) ? '' : $link );
	}

	$trail[] = $last; // le cours (courant).

	return $trail;
}
add_filter( 'rank_math/frontend/breadcrumb/items', 'afsac_breadcrumb_course_trail' );
