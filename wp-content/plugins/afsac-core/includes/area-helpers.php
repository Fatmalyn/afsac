<?php
/**
 * Helpers de la taxonomie « afsac_area » (domaines OACI).
 *
 * Lecture seule, Polylang-aware. Alimente l'index Catalogue (template-catalogue)
 * et, à terme, l'archive de domaine (taxonomy-afsac_area). Aucune dépendance à un
 * répéteur ACF ni à une page d'options.
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Domaines OACI de premier niveau (parent = 0), dans la langue COURANTE, ordre
 * stable (nom A→Z). Polylang stocke un jeu de termes par langue : on cible la
 * langue courante pour des libellés et des liens localisés.
 *
 * @return WP_Term[] Liste de termes (vide si erreur).
 */
function afsac_get_area_domains() {
	$args = array(
		'taxonomy'   => 'afsac_area',
		'parent'     => 0,
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	);

	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language();
		if ( $lang ) {
			$args['lang'] = $lang;
		}
	}

	$terms = get_terms( $args );

	return ( is_array( $terms ) && ! is_wp_error( $terms ) ) ? $terms : array();
}

/**
 * Couleur d'accent d'un domaine OACI (méta de terme « afsac_area_color »,
 * alimentée par le champ ACF color_picker du même nom sur l'écran du terme).
 *
 * Source unique de l'accent : méta de terme. Retourne une couleur hexadécimale
 * validée (#RGB ou #RRGGBB) ou '' si absente/invalide — le gabarit applique alors
 * son repli navy. (Champ ACF gratuit ; pas de répéteur ni de page d'options.)
 *
 * @param WP_Term|int $term Terme ou ID de terme.
 * @return string Couleur hex (avec « # »), ou '' .
 */
function afsac_get_area_accent_color( $term ) {
	$term_id = ( $term instanceof WP_Term ) ? (int) $term->term_id : (int) $term;
	if ( ! $term_id ) {
		return '';
	}

	$raw = trim( (string) get_term_meta( $term_id, 'afsac_area_color', true ) );
	if ( '' === $raw ) {
		return '';
	}

	return preg_match( '/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $raw ) ? $raw : '';
}

/**
 * Nombre de cours publiés rattachés à un domaine, sous-domaines INCLUS : ceux de
 * la langue courante PLUS les cours TRAINAIR PLUS de toutes les langues — le même
 * jeu que l'archive du domaine (cf. trainair-bilingue.php).
 *
 * @param WP_Term|int $term Terme (domaine) ou ID.
 * @return int Nombre de cours (0 si aucun / terme invalide).
 */
function afsac_get_area_course_count( $term ) {
	$term_id = ( $term instanceof WP_Term ) ? (int) $term->term_id : (int) $term;
	if ( ! $term_id ) {
		return 0;
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'afsac_formation',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			AFSAC_TRAINAIR_BILINGUE  => true,
			'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Comptage par domaine sur une page d'index (mise en cache objet WP).
				array(
					'taxonomy'         => 'afsac_area',
					'field'            => 'term_id',
					'terms'            => $term_id,
					'include_children' => true,
				),
			),
		)
	);

	return (int) $query->found_posts;
}
