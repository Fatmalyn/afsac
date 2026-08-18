<?php
/**
 * Seed idempotent des termes de taxonomies, avec gestion Polylang (langue +
 * traductions FR↔EN liées).
 *
 * Lancement : WP-CLI « wp afsac seed-terms ». Aucune auto-exécution à
 * l'activation (les langues Polylang fr/en doivent préexister).
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Données du seed : pour chaque taxonomie, la liste des concepts (paire fr/en).
 *
 * Les noms EN des areas sont la terminologie OACI officielle ; les FR ont été
 * validés. famille = noms propres (fr = en).
 *
 * @return array<string,array<int,array{en:string,fr:string}>>
 */
function afsac_seed_data() {
	return array(
		'afsac_area'     => array(
			array( 'en' => 'Aerodromes', 'fr' => 'Aérodromes' ),
			array( 'en' => 'Air Transport', 'fr' => 'Transport aérien' ),
			array( 'en' => 'Training & Competency Development', 'fr' => 'Formation et développement des compétences' ),
			array( 'en' => 'Environment', 'fr' => 'Environnement' ),
			array( 'en' => 'Aviation Management', 'fr' => 'Gestion de l’aviation' ),
			array( 'en' => 'Air Navigation Services', 'fr' => 'Services de navigation aérienne' ),
			array( 'en' => 'Flight Safety & Safety Management', 'fr' => 'Sécurité des vols et gestion de la sécurité' ),
			array( 'en' => 'Aviation Security', 'fr' => 'Sûreté de l’aviation' ),
			array( 'en' => 'Facilitation', 'fr' => 'Facilitation' ),
			array( 'en' => 'Aviation Law', 'fr' => 'Droit aérien' ),
			array( 'en' => 'Aviation New Entrants', 'fr' => 'Nouveaux entrants dans l’aviation' ),
		),
		'afsac_famille'  => array(
			array( 'en' => 'TRAINAIR PLUS', 'fr' => 'TRAINAIR PLUS' ),
			array( 'en' => 'AVSEC', 'fr' => 'AVSEC' ),
		),
		'afsac_langue'   => array(
			array( 'en' => 'French', 'fr' => 'Français' ),
			array( 'en' => 'English', 'fr' => 'Anglais' ),
			array( 'en' => 'Arabic', 'fr' => 'Arabe' ),
		),
		'afsac_modalite' => array(
			array( 'en' => 'On-site', 'fr' => 'Présentiel' ),
			array( 'en' => 'Online', 'fr' => 'Distanciel' ),
			array( 'en' => 'Hybrid', 'fr' => 'Hybride' ),
			array( 'en' => 'In-house', 'fr' => 'Intra-entreprise' ),
			array( 'en' => 'Bespoke', 'fr' => 'Sur-mesure' ),
		),
	);
}

/**
 * Crée (ou retrouve) un terme dans une langue Polylang donnée, de façon idempotente.
 *
 * IMPORTANT : Polylang filtre get_terms()/get_term_by() par langue courante.
 *   - Idempotence (même nom + même langue) : get_terms() avec l'argument Polylang
 *     « lang » => $lang, qui cible explicitement la bonne langue.
 *   - Détection de collision de slug entre langues : term_exists() (requête SQL
 *     directe, NON filtrée par Polylang) → si le slug existe déjà, on suffixe
 *     « -{lang} » pour permettre des noms identiques en fr et en (ex. AVSEC).
 *
 * @param string $name     Nom du terme.
 * @param string $taxonomy Taxonomie cible.
 * @param string $lang     Slug de langue Polylang (ex. « fr », « en »).
 * @param array  $report   Compteur passé par référence (created / reused).
 * @return int ID du terme, ou 0 en cas d'échec.
 */
function afsac_seed_term( $name, $taxonomy, $lang, &$report ) {
	// 1) Déjà présent dans CETTE langue (lookup Polylang-aware par nom) ?
	$existing = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'lang'       => $lang,
			'name'       => $name,
			'number'     => 1,
		)
	);
	if ( ! is_wp_error( $existing ) && ! empty( $existing ) ) {
		$report['reused']++;
		return (int) $existing[0]->term_id;
	}

	// 2) Slug déterministe ; suffixé par langue si déjà pris (toutes langues).
	$slug = sanitize_title( $name );
	if ( term_exists( $slug, $taxonomy ) ) {
		$slug .= '-' . $lang;
	}

	// 3) Création + affectation de langue.
	$result = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );

	if ( is_wp_error( $result ) ) {
		$existing_id = $result->get_error_data( 'term_exists' );
		if ( $existing_id ) {
			if ( ! pll_get_term_language( (int) $existing_id ) ) {
				pll_set_term_language( (int) $existing_id, $lang );
			}
			$report['reused']++;
			return (int) $existing_id;
		}
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::warning( sprintf( '“%s” (%s/%s) : %s', $name, $taxonomy, $lang, $result->get_error_message() ) );
		}
		return 0;
	}

	$term_id = (int) $result['term_id'];
	pll_set_term_language( $term_id, $lang );
	$report['created']++;

	return $term_id;
}

/**
 * Exécute le seed complet : crée les termes fr + en et lie les traductions.
 *
 * @return array{ok:bool,message:string,created:int,reused:int,linked:int,pairs:array,preexisting:array}
 */
function afsac_seed_terms() {
	$report = array(
		'ok'          => false,
		'message'     => '',
		'created'     => 0,
		'reused'      => 0,
		'linked'      => 0,
		'pairs'       => array(),
		'preexisting' => array(),
	);

	// Garde : Polylang et ses langues fr/en doivent être disponibles.
	if ( ! function_exists( 'pll_set_term_language' ) || ! function_exists( 'pll_save_term_translations' ) || ! function_exists( 'pll_languages_list' ) ) {
		$report['message'] = 'Polylang est requis (fonctions pll_* introuvables).';
		return $report;
	}

	$langs = pll_languages_list();
	if ( ! in_array( 'fr', $langs, true ) || ! in_array( 'en', $langs, true ) ) {
		$report['message'] = 'Les langues Polylang « fr » et « en » doivent être configurées. Trouvées : ' . implode( ', ', $langs );
		return $report;
	}

	$data = afsac_seed_data();

	// Inventaire des termes PRÉEXISTANTS (avant toute création), pour décision.
	foreach ( array_keys( $data ) as $taxonomy ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $terms ) ) {
			continue;
		}
		foreach ( $terms as $t ) {
			$report['preexisting'][] = array(
				'taxonomy' => $taxonomy,
				'name'     => $t->name,
				'slug'     => $t->slug,
				'term_id'  => (int) $t->term_id,
				'lang'     => pll_get_term_language( $t->term_id ) ? pll_get_term_language( $t->term_id ) : '(aucune)',
			);
		}
	}

	// Création + liaison fr↔en, taxonomie par taxonomie.
	foreach ( $data as $taxonomy => $concepts ) {
		foreach ( $concepts as $concept ) {
			$en_id = afsac_seed_term( $concept['en'], $taxonomy, 'en', $report );
			$fr_id = afsac_seed_term( $concept['fr'], $taxonomy, 'fr', $report );

			if ( $en_id && $fr_id ) {
				pll_save_term_translations(
					array(
						'en' => $en_id,
						'fr' => $fr_id,
					)
				);
				$report['linked']++;
				$report['pairs'][] = array(
					'taxonomy' => $taxonomy,
					'fr'       => $concept['fr'],
					'fr_id'    => $fr_id,
					'en'       => $concept['en'],
					'en_id'    => $en_id,
				);
			}
		}
	}

	$report['ok']      = true;
	$report['message'] = sprintf( '%d terme(s) créé(s), %d réutilisé(s), %d paire(s) fr↔en liée(s).', $report['created'], $report['reused'], $report['linked'] );

	return $report;
}

/**
 * Commande WP-CLI : wp afsac seed-terms
 *
 * Crée/relie les termes des taxonomies AFSAC (idempotent). Affiche un résumé,
 * la liste des paires liées et les termes préexistants.
 *
 * @return void
 */
function afsac_cli_seed_terms() {
	$r = afsac_seed_terms();

	if ( ! $r['ok'] ) {
		WP_CLI::error( $r['message'] );
		return;
	}

	WP_CLI::log( '— Paires fr↔en liées —' );
	if ( $r['pairs'] ) {
		$rows = array();
		foreach ( $r['pairs'] as $p ) {
			$rows[] = array(
				'taxonomy' => $p['taxonomy'],
				'fr'       => $p['fr'] . ' (#' . $p['fr_id'] . ')',
				'en'       => $p['en'] . ' (#' . $p['en_id'] . ')',
			);
		}
		WP_CLI\Utils\format_items( 'table', $rows, array( 'taxonomy', 'fr', 'en' ) );
	}

	WP_CLI::log( '' );
	WP_CLI::log( '— Termes préexistants (avant seed) —' );
	if ( $r['preexisting'] ) {
		WP_CLI\Utils\format_items( 'table', $r['preexisting'], array( 'taxonomy', 'name', 'slug', 'term_id', 'lang' ) );
	} else {
		WP_CLI::log( '(aucun)' );
	}

	WP_CLI::success( $r['message'] );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'afsac seed-terms', 'afsac_cli_seed_terms' );
}
