<?php
/**
 * Catalogue TRAINAIR PLUS bilingue.
 *
 * Demande client du 16/09/2026 : « les formations TRAINAIR PLUS s'affichent
 * toutes dans les deux langues — en FR comme en EN, on affiche tout, français et
 * anglais ». Chaque cours TRAINAIR est une fiche Polylang MONO-LANGUE (sa langue
 * est celle dans laquelle il est dispensé) : la page française n'en montrait que
 * 28, la page anglaise 318.
 *
 * Le périmètre est volontairement limité à TRAINAIR PLUS : le programme AVSEC est
 * traduit fiche à fiche (paires FR↔EN), il reste filtré par langue.
 *
 * Mécanique : une requête qui porte la variable AFSAC_TRAINAIR_BILINGUE est
 * réécrite au moment où WordPress construit sa requête de taxonomie :
 *   1. la restriction de langue de Polylang est remplacée par
 *      « langue courante OU famille TRAINAIR PLUS (toutes langues) » ;
 *   2. les termes traduits (domaine, famille, langue) sont étendus à leurs
 *      traductions — un cours anglais porte le terme anglais « Aerodromes », pas
 *      « Aérodromes ».
 *
 * Les paires traduites TRAINAIR (7 au 16/09/2026, ex. TIC FR / TIC EN) ne sont PAS
 * dédoublonnées : ce sont deux offres distinctes, une par langue d'animation.
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Variable de requête qui active la réécriture bilingue.
 *
 * Usage : `'afsac_trainair_bilingue' => true` dans les arguments d'un WP_Query,
 * ou `$query->set( AFSAC_TRAINAIR_BILINGUE, true )` dans un pre_get_posts.
 */
define( 'AFSAC_TRAINAIR_BILINGUE', 'afsac_trainair_bilingue' );

/**
 * IDs des termes « famille = TRAINAIR PLUS », toutes langues confondues.
 *
 * Même règle que afsac_formation_programme() : on reconnaît la famille au PRÉFIXE
 * du slug (`trainair-plus` / `trainair-plus-fr`).
 *
 * @return int[]
 */
function afsac_trainair_famille_term_ids() {
	static $ids = null;
	if ( null !== $ids ) {
		return $ids;
	}

	$ids   = array();
	$terms = get_terms(
		array(
			'taxonomy'   => 'afsac_famille',
			'hide_empty' => false,
			'lang'       => '',
		)
	);
	if ( is_array( $terms ) ) {
		foreach ( $terms as $term ) {
			if ( 0 === strpos( $term->slug, 'trainair' ) ) {
				$ids[] = (int) $term->term_id;
			}
		}
	}

	return $ids;
}

/**
 * term_taxonomy_id des termes donnés, de toutes leurs traductions et, au besoin,
 * de leurs enfants.
 *
 * On résout jusqu'au term_taxonomy_id pour que WP_Tax_Query n'ait plus aucune
 * conversion à faire : ses conversions passent par WP_Term_Query, que Polylang
 * restreint à la langue de la requête (sur /en/, les termes français seraient
 * écartés sans bruit).
 *
 * @param int[]  $term_ids         IDs de termes.
 * @param string $taxonomy         Taxonomie.
 * @param bool   $include_children Inclure les sous-termes.
 * @return int[]
 */
function afsac_term_tt_ids_all_languages( array $term_ids, $taxonomy, $include_children ) {
	$all = array();
	foreach ( $term_ids as $term_id ) {
		$term_id         = (int) $term_id;
		$all[ $term_id ] = $term_id;
		if ( function_exists( 'pll_get_term_translations' ) ) {
			foreach ( pll_get_term_translations( $term_id ) as $translated_id ) {
				$all[ (int) $translated_id ] = (int) $translated_id;
			}
		}
	}

	if ( $include_children && is_taxonomy_hierarchical( $taxonomy ) ) {
		foreach ( array_values( $all ) as $term_id ) {
			$children = get_term_children( $term_id, $taxonomy );
			if ( is_array( $children ) ) {
				foreach ( $children as $child_id ) {
					$all[ (int) $child_id ] = (int) $child_id;
				}
			}
		}
	}

	$tt_ids = array();
	foreach ( $all as $term_id ) {
		$term = get_term( $term_id, $taxonomy );
		if ( $term instanceof WP_Term ) {
			$tt_ids[] = (int) $term->term_taxonomy_id;
		}
	}

	return $tt_ids;
}

/**
 * Étend une clause de taxonomie traduite à toutes les langues.
 *
 * Ne traite que les clauses « IN » par term_id ou slug : c'est ce que produisent
 * les gabarits et WordPress lui-même pour une archive de terme. Les autres
 * clauses sont rendues telles quelles.
 *
 * @param array $clause Clause WP_Tax_Query déjà assainie.
 * @return array
 */
function afsac_tax_clause_all_languages( array $clause ) {
	$taxonomy = isset( $clause['taxonomy'] ) ? (string) $clause['taxonomy'] : '';
	$field    = isset( $clause['field'] ) ? (string) $clause['field'] : 'term_id';
	$operator = isset( $clause['operator'] ) ? strtoupper( (string) $clause['operator'] ) : 'IN';

	if ( ! function_exists( 'afsac_translatable_taxonomies' )
		|| ! in_array( $taxonomy, afsac_translatable_taxonomies(), true )
		|| 'IN' !== $operator
		|| ! in_array( $field, array( 'term_id', 'slug' ), true )
		|| empty( $clause['terms'] )
	) {
		return $clause;
	}

	$term_ids = array_map( 'intval', (array) $clause['terms'] );
	if ( 'slug' === $field ) {
		$term_ids = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'slug'       => (array) $clause['terms'],
				'hide_empty' => false,
				'lang'       => '',
				'fields'     => 'ids',
			)
		);
		if ( ! is_array( $term_ids ) || empty( $term_ids ) ) {
			return $clause;
		}
	}

	$tt_ids = afsac_term_tt_ids_all_languages( $term_ids, $taxonomy, ! empty( $clause['include_children'] ) );
	if ( empty( $tt_ids ) ) {
		return $clause;
	}

	$clause['field']            = 'term_taxonomy_id';
	$clause['terms']            = $tt_ids;
	$clause['include_children'] = false;

	return $clause;
}

/**
 * Réécrit la requête de taxonomie d'une requête marquée AFSAC_TRAINAIR_BILINGUE.
 *
 * Branché sur `parse_tax_query` APRÈS Polylang (priorités 1 et 10), qui modifie
 * lui aussi `tax_query->queries` à cet endroit. Les `queried_terms` ne sont pas
 * touchés : l'objet interrogé d'une archive de domaine reste le terme de la langue
 * courante (titre, fil d'Ariane, sélecteur de langue).
 *
 * @param WP_Query $query Requête en cours.
 * @return void
 */
function afsac_trainair_bilingual_tax_query( $query ) {
	if ( ! $query instanceof WP_Query || ! $query->get( AFSAC_TRAINAIR_BILINGUE ) || ! $query->tax_query instanceof WP_Tax_Query ) {
		return;
	}
	if ( ! function_exists( 'pll_current_language' ) || 'OR' === $query->tax_query->relation ) {
		return;
	}

	$lang      = (string) pll_current_language();
	$lang_term = '' !== $lang ? get_term_by( 'slug', $lang, 'language' ) : false;
	$famille   = afsac_trainair_famille_term_ids();
	if ( ! $lang_term instanceof WP_Term || empty( $famille ) ) {
		return;
	}

	$queries = array();
	foreach ( $query->tax_query->queries as $key => $clause ) {
		if ( 'relation' === $key ) {
			$queries['relation'] = $clause;
			continue;
		}
		if ( is_array( $clause ) && isset( $clause['taxonomy'] ) ) {
			// Restriction de langue de Polylang : remplacée par la clause ci-dessous.
			if ( 'language' === $clause['taxonomy'] ) {
				continue;
			}
			$clause = afsac_tax_clause_all_languages( $clause );
		}
		$queries[] = $clause;
	}

	$queries[] = array(
		'relation' => 'OR',
		array(
			'taxonomy'         => 'language',
			'field'            => 'term_taxonomy_id',
			'terms'            => array( (int) $lang_term->term_taxonomy_id ),
			'include_children' => false,
		),
		array(
			'taxonomy'         => 'afsac_famille',
			'field'            => 'term_taxonomy_id',
			'terms'            => afsac_term_tt_ids_all_languages( $famille, 'afsac_famille', false ),
			'include_children' => false,
		),
	);

	$query->tax_query->queries = $query->tax_query->sanitize_query( $queries );
}
add_action( 'parse_tax_query', 'afsac_trainair_bilingual_tax_query', 20 );

/**
 * Langue(s) d'animation d'un cours, dans la langue de navigation.
 *
 * Maintenant qu'une même liste mêle cours français et anglais, la langue doit se
 * lire sur chaque ligne. Or 270 des 346 cours TRAINAIR n'ont pas de terme
 * `afsac_langue` : on retombe alors sur la langue Polylang de la fiche, qui est
 * celle dans laquelle le cours est dispensé.
 *
 * @param int $post_id ID du cours.
 * @return WP_Term[]
 */
function afsac_formation_langue_terms( $post_id ) {
	$post_id = (int) $post_id;
	$terms   = get_the_terms( $post_id, 'afsac_langue' );
	$terms   = ( $terms && ! is_wp_error( $terms ) ) ? $terms : array();

	if ( empty( $terms ) && function_exists( 'pll_get_post_language' ) ) {
		// Code Polylang → slugs du terme de langue (FR, EN).
		$slugs = array(
			'fr' => array( 'francais', 'french' ),
			'en' => array( 'anglais', 'english' ),
		);
		$code  = (string) pll_get_post_language( $post_id );
		if ( isset( $slugs[ $code ] ) ) {
			$found = get_terms(
				array(
					'taxonomy'   => 'afsac_langue',
					'slug'       => $slugs[ $code ],
					'hide_empty' => false,
					'lang'       => '',
					'number'     => 1,
				)
			);
			$terms = is_array( $found ) ? $found : array();
		}
	}

	if ( ! function_exists( 'pll_get_term' ) || ! function_exists( 'pll_current_language' ) || ! pll_current_language() ) {
		return $terms;
	}

	$localized = array();
	foreach ( $terms as $term ) {
		$translated_id = (int) pll_get_term( $term->term_id, pll_current_language() );
		$translated    = $translated_id ? get_term( $translated_id, 'afsac_langue' ) : null;
		$localized[]   = ( $translated instanceof WP_Term ) ? $translated : $term;
	}

	return $localized;
}
