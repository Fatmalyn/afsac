<?php
/**
 * Seed idempotent des cours TRAINAIR PLUS proposés par l'AFSAC (catalogue OACI
 * igat.icao.int) + leurs SESSIONS planifiées (dates / lieu / langue).
 *
 * Différences avec seed-formations.php (AVSEC) :
 *   - Ces cours sont MONO-LANGUE (pas de paire FR↔EN) : TDC/TIC = FR, GSI/OJTI = EN.
 *   - Chaque cours porte des SESSIONS réelles (CPT afsac_session), reliées à la
 *     formation via la méta `_afsac_formation_id` (includes/relations.php).
 *   - On renseigne aussi code, niveau, frais/devise et modalité (absents des
 *     fiches AVSEC).
 *
 * Idempotent : marqueurs `_afsac_import_key` (formation) et
 * `_afsac_import_session_key` (session) → relancer met à jour, ne duplique pas.
 * Réutilise les helpers de seed-formations.php (afsac_import_set_field,
 * afsac_import_term_pair) et de seed-terms.php (afsac_seed_term).
 *
 * Lancement : « wp afsac seed-trainair » OU Outils → « Import TRAINAIR PLUS ».
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Données des 4 cours TRAINAIR PLUS (source : catalogue OACI, 8 juillet 2026).
 *
 * Chaque cours : `lang` (fr|en), champs de la formation, et `sessions` (dates
 * au format Ymd attendu par le date_picker ACF et par les requêtes de
 * includes/relations.php).
 *
 * @return array<int,array<string,mixed>>
 */
function afsac_trainair_courses() {
	return array(
		array(
			'key'                  => 'tp-tdc-fr',
			'lang'                 => 'fr',
			'title'                => 'Formation de concepteur de cours',
			'abbr'                 => 'TDC FR',
			'code'                 => '214/001/TDC FR',
			'area'                 => array(
				'fr' => 'Formation et développement des compétences',
				'en' => 'Training & Competency Development',
			),
			'modalite'             => array(
				'fr' => 'Présentiel',
				'en' => 'On-site',
			),
			'niveau'               => 'technique',
			'duree'                => '10 jours / 57 heures',
			'frais'                => 1500,
			'devise'               => 'USD',
			'developpe_par'        => 'ICAO · OACI',
			'developpe_par_detail' => 'International Civil Aviation Organization · Montréal, Canada',
			'goal'                 => 'Ce cours vise à doter les concepteurs de formation aéronautique des compétences nécessaires pour élaborer des packages de formation standardisés, selon la méthodologie TRAINAIR PLUS de l’OACI.',
			'lieu'                 => 'Tunis, Tunisie',
			'hote'                 => 'AFSAC',
			'sessions'             => array(
				array(
					'key'   => 's1',
					'title' => 'TDC FR — Tunis · 14–25 sept. 2026',
					'debut' => '20260914',
					'fin'   => '20260925',
				),
			),
		),
		array(
			'key'                  => 'tp-tic-fr',
			'lang'                 => 'fr',
			'title'                => 'Cours de formation instructeurs',
			'abbr'                 => 'TIC FR',
			'code'                 => '212/002/TIC FR',
			'area'                 => array(
				'fr' => 'Formation et développement des compétences',
				'en' => 'Training & Competency Development',
			),
			'modalite'             => array(
				'fr' => 'Présentiel',
				'en' => 'On-site',
			),
			'niveau'               => 'technique',
			'duree'                => '5 jours / 27 heures',
			'frais'                => 1500,
			'devise'               => 'USD',
			'developpe_par'        => 'ICAO · OACI',
			'developpe_par_detail' => 'International Civil Aviation Organization · Montréal, Canada',
			'goal'                 => 'Ce cours forme des instructeurs capables de dispenser méthodiquement des sessions de formation, conformément au cadre de compétences des instructeurs de l’OACI, à l’aide d’une mallette pédagogique normalisée.',
			'lieu'                 => 'Tunis, Tunisie',
			'hote'                 => 'AFSAC',
			'sessions'             => array(
				array(
					'key'   => 's1',
					'title' => 'TIC FR — Tunis · 28 sept.–2 oct. 2026',
					'debut' => '20260928',
					'fin'   => '20261002',
				),
				array(
					'key'   => 's2',
					'title' => 'TIC FR — Tunis · 9–13 nov. 2026',
					'debut' => '20261109',
					'fin'   => '20261113',
				),
			),
		),
		array(
			'key'                  => 'tp-gsi-air-en',
			'lang'                 => 'en',
			'title'                => 'Government Safety Inspector — Airworthiness (Air Operator & AMO Certification)',
			'abbr'                 => 'GSI AIR EN',
			'code'                 => 'ITP/FSM/GSI AIR/007EN',
			'area'                 => array(
				'fr' => 'Sécurité des vols et gestion de la sécurité',
				'en' => 'Flight Safety & Safety Management',
			),
			'modalite'             => array(
				'fr' => 'Hybride',
				'en' => 'Hybrid',
			),
			'niveau'               => 'technique',
			'duree'                => '15 days / 112 hours',
			'frais'                => 4200,
			'devise'               => 'USD',
			'developpe_par'        => 'ICAO · FAA',
			'developpe_par_detail' => 'Jointly developed by ICAO and the U.S. Federal Aviation Administration (FAA)',
			'goal'                 => 'Course jointly developed by ICAO and the FAA to equip airworthiness inspectors with the competencies required to certify approved maintenance organizations and air operators, following a five-phase process.',
			'lieu'                 => 'Tunis, Tunisia',
			'hote'                 => 'AFSAC',
			'sessions'             => array(
				array(
					'key'   => 's1',
					'title' => 'GSI AIR EN — Tunis · 5–23 Oct 2026',
					'debut' => '20261005',
					'fin'   => '20261023',
				),
			),
		),
		array(
			'key'                  => 'tp-ojti-en',
			'lang'                 => 'en',
			'title'                => 'On-the-Job Training Instructor (OJTI)',
			'abbr'                 => 'OJTI',
			'code'                 => 'ITP/R/TCD/OJTI/010E',
			'area'                 => array(
				'fr' => 'Formation et développement des compétences',
				'en' => 'Training & Competency Development',
			),
			'modalite'             => array(
				'fr' => 'Hybride',
				'en' => 'Hybrid',
			),
			'niveau'               => 'technique',
			'duree'                => '5 days / 30 hours',
			'frais'                => 1900,
			'devise'               => 'USD',
			'developpe_par'        => 'Etihad Aviation Training',
			'developpe_par_detail' => 'Etihad Aviation Training',
			'goal'                 => 'This course prepares on-the-job training (OJT) instructors to identify needs, plan, deliver and assess practical training sessions, in line with ICAO Doc 9941 and Doc 9868.',
			'lieu'                 => 'Tunis, Tunisia',
			'hote'                 => 'AFSAC',
			'sessions'             => array(
				array(
					'key'   => 's1',
					'title' => 'OJTI — Tunis · 23–27 Nov 2026',
					'debut' => '20261123',
					'fin'   => '20261127',
				),
			),
		),
	);
}

/**
 * Insère ou met à jour une formation TRAINAIR PLUS (mono-langue) + champs + taxos.
 *
 * @param array  $c      Données du cours.
 * @param string $lang   Langue Polylang (« fr » ou « en »).
 * @param array  $terms  IDs de termes pour CETTE langue (famille/area/modalite/langue).
 * @param array  $report Compteur passé par référence.
 * @return int ID du post (0 si échec).
 */
function afsac_seed_trainair_formation( $c, $lang, $terms, &$report ) {
	$existing = get_posts(
		array(
			'post_type'      => 'afsac_formation',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'lang'           => $lang,
			'meta_key'       => '_afsac_import_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $c['key'], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);

	$goal    = isset( $c['goal'] ) ? (string) $c['goal'] : '';
	$content = '' !== $goal ? '<!-- wp:paragraph --><p>' . esc_html( $goal ) . '</p><!-- /wp:paragraph -->' : '';
	$excerpt = '' !== $goal ? wp_trim_words( $goal, 32, '…' ) : '';

	$postarr = array(
		'post_type'    => 'afsac_formation',
		'post_status'  => 'publish',
		'post_title'   => $c['title'],
		'post_excerpt' => $excerpt,
		'post_content' => $content,
	);

	if ( ! empty( $existing ) ) {
		$postarr['ID'] = (int) $existing[0];
		$post_id       = wp_update_post( $postarr, true );
		$report['updated']++;
	} else {
		$post_id = wp_insert_post( $postarr, true );
		$report['created']++;
	}

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return 0;
	}
	$post_id = (int) $post_id;

	// Langue Polylang AVANT l'affectation des termes.
	if ( function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $post_id, $lang );
	}

	// Taxonomies : famille TRAINAIR PLUS, domaine (area), modalité, langue.
	if ( ! empty( $terms['famille'] ) ) {
		wp_set_object_terms( $post_id, array( (int) $terms['famille'] ), 'afsac_famille', false );
	}
	if ( ! empty( $terms['area'] ) ) {
		wp_set_object_terms( $post_id, array( (int) $terms['area'] ), 'afsac_area', false );
	}
	if ( ! empty( $terms['modalite'] ) ) {
		wp_set_object_terms( $post_id, array( (int) $terms['modalite'] ), 'afsac_modalite', false );
	}
	if ( ! empty( $terms['langue'] ) ) {
		wp_set_object_terms( $post_id, array( (int) $terms['langue'] ), 'afsac_langue', false );
	}

	// Champs ACF structurés (repli méta si ACF absent).
	afsac_import_set_field( $post_id, 'afsac_abbreviation', $c['abbr'] );
	afsac_import_set_field( $post_id, 'afsac_code', $c['code'] );
	afsac_import_set_field( $post_id, 'afsac_duree', $c['duree'] );
	afsac_import_set_field( $post_id, 'afsac_niveau', $c['niveau'] );
	afsac_import_set_field( $post_id, 'afsac_frais_montant', (int) $c['frais'] );
	afsac_import_set_field( $post_id, 'afsac_devise', $c['devise'] );
	afsac_import_set_field( $post_id, 'afsac_developpe_par', $c['developpe_par'] );
	afsac_import_set_field( $post_id, 'afsac_developpe_par_detail', $c['developpe_par_detail'] );

	// Marqueurs d'idempotence.
	update_post_meta( $post_id, '_afsac_import', 1 );
	update_post_meta( $post_id, '_afsac_import_key', $c['key'] );
	update_post_meta( $post_id, '_afsac_import_prog', 'trainair' );

	return $post_id;
}

/**
 * Insère ou met à jour une session planifiée, reliée à sa formation.
 *
 * @param array  $s               Données de la session (title/debut/fin).
 * @param array  $c               Données du cours parent (lieu/hote).
 * @param int    $formation_id    ID de la formation liée.
 * @param string $lang            Langue Polylang de la session.
 * @param int    $langue_term_id  ID du terme afsac_langue à affecter.
 * @param array  $report          Compteur passé par référence.
 * @return int ID du post session (0 si échec).
 */
function afsac_seed_trainair_session( $s, $c, $formation_id, $lang, $langue_term_id, &$report ) {
	$skey = $c['key'] . '-' . $s['key'];

	$existing = get_posts(
		array(
			'post_type'      => 'afsac_session',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'lang'           => $lang,
			'meta_key'       => '_afsac_import_session_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $skey, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);

	$postarr = array(
		'post_type'   => 'afsac_session',
		'post_status' => 'publish',
		'post_title'  => $s['title'],
	);

	if ( ! empty( $existing ) ) {
		$postarr['ID'] = (int) $existing[0];
		$session_id    = wp_update_post( $postarr, true );
		$report['sessions_updated']++;
	} else {
		$session_id = wp_insert_post( $postarr, true );
		$report['sessions_created']++;
	}

	if ( is_wp_error( $session_id ) || ! $session_id ) {
		return 0;
	}
	$session_id = (int) $session_id;

	if ( function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $session_id, $lang );
	}

	// Relation session → formation (source de vérité, cf. includes/relations.php).
	update_post_meta( $session_id, '_afsac_formation_id', (int) $formation_id );

	afsac_import_set_field( $session_id, 'afsac_date_debut', $s['debut'] );
	afsac_import_set_field( $session_id, 'afsac_date_fin', $s['fin'] );
	afsac_import_set_field( $session_id, 'afsac_lieu', isset( $c['lieu'] ) ? $c['lieu'] : '' );
	afsac_import_set_field( $session_id, 'afsac_hote', isset( $c['hote'] ) ? $c['hote'] : '' );
	afsac_import_set_field( $session_id, 'afsac_statut', 'ouvert' );

	// Langue d'animation : terme de taxonomie (le thème lit via ACF load_terms).
	if ( $langue_term_id ) {
		wp_set_object_terms( $session_id, array( (int) $langue_term_id ), 'afsac_langue', false );
		afsac_import_set_field( $session_id, 'afsac_langue_session', (int) $langue_term_id );
	}

	update_post_meta( $session_id, '_afsac_import_session', 1 );
	update_post_meta( $session_id, '_afsac_import_session_key', $skey );

	return $session_id;
}

/**
 * Exécute le seed complet des cours TRAINAIR PLUS + sessions.
 *
 * @return array{ok:bool,message:string,created:int,updated:int,sessions_created:int,sessions_updated:int,terms:array,items:array}
 */
function afsac_seed_trainair() {
	$report = array(
		'ok'               => false,
		'message'          => '',
		'created'          => 0,
		'updated'          => 0,
		'sessions_created' => 0,
		'sessions_updated' => 0,
		'terms'            => array(
			'created' => 0,
			'reused'  => 0,
		),
		'items'            => array(),
	);

	// Garde : Polylang et ses langues fr/en doivent être disponibles.
	if ( ! function_exists( 'pll_set_post_language' ) || ! function_exists( 'pll_languages_list' ) ) {
		$report['message'] = 'Polylang est requis (fonctions pll_* introuvables).';
		return $report;
	}
	$langs = pll_languages_list();
	if ( ! in_array( 'fr', $langs, true ) || ! in_array( 'en', $langs, true ) ) {
		$report['message'] = 'Les langues Polylang « fr » et « en » doivent être configurées. Trouvées : ' . implode( ', ', $langs );
		return $report;
	}

	// Helpers partagés avec le seed AVSEC.
	if ( ! function_exists( 'afsac_import_term_pair' ) || ! function_exists( 'afsac_import_set_field' ) ) {
		$report['message'] = 'Helpers d’import introuvables (seed-formations.php doit être chargé).';
		return $report;
	}

	foreach ( afsac_trainair_courses() as $c ) {
		$lang = $c['lang'];

		// Termes appariés (idempotents, liés fr↔en), on garde le côté de la langue.
		$fam    = afsac_import_term_pair( 'TRAINAIR PLUS', 'TRAINAIR PLUS', 'afsac_famille', $report['terms'] );
		$area   = afsac_import_term_pair( $c['area']['fr'], $c['area']['en'], 'afsac_area', $report['terms'] );
		$mod    = afsac_import_term_pair( $c['modalite']['fr'], $c['modalite']['en'], 'afsac_modalite', $report['terms'] );
		$langue = ( 'fr' === $lang )
			? afsac_import_term_pair( 'Français', 'French', 'afsac_langue', $report['terms'] )
			: afsac_import_term_pair( 'Anglais', 'English', 'afsac_langue', $report['terms'] );

		$terms = array(
			'famille'  => $fam[ $lang ],
			'area'     => $area[ $lang ],
			'modalite' => $mod[ $lang ],
			'langue'   => $langue[ $lang ],
		);

		$formation_id = afsac_seed_trainair_formation( $c, $lang, $terms, $report );
		if ( ! $formation_id ) {
			continue;
		}

		foreach ( $c['sessions'] as $s ) {
			afsac_seed_trainair_session( $s, $c, $formation_id, $lang, $langue[ $lang ], $report );
		}

		$report['items'][] = array(
			'title'    => $c['title'] . ' (' . $c['abbr'] . ')',
			'lang'     => strtoupper( $lang ),
			'id'       => $formation_id,
			'sessions' => count( $c['sessions'] ),
		);
	}

	$report['ok']      = true;
	$report['message'] = sprintf(
		'%d formation(s) créée(s), %d mise(s) à jour ; %d session(s) créée(s), %d mise(s) à jour ; termes de rattachement : %d créé(s)/%d réutilisé(s).',
		$report['created'],
		$report['updated'],
		$report['sessions_created'],
		$report['sessions_updated'],
		$report['terms']['created'],
		$report['terms']['reused']
	);

	return $report;
}

/**
 * Commande WP-CLI : wp afsac seed-trainair
 *
 * @return void
 */
function afsac_cli_seed_trainair() {
	$r = afsac_seed_trainair();
	if ( ! $r['ok'] ) {
		WP_CLI::error( $r['message'] );
		return;
	}
	if ( $r['items'] ) {
		WP_CLI\Utils\format_items( 'table', $r['items'], array( 'id', 'lang', 'title', 'sessions' ) );
	}
	WP_CLI::success( $r['message'] );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'afsac seed-trainair', 'afsac_cli_seed_trainair' );
}

/**
 * Repli non-CLI : page d'admin sous « Outils » pour lancer l'import TRAINAIR PLUS.
 *
 * @return void
 */
function afsac_trainair_admin_menu() {
	add_management_page(
		__( 'Import TRAINAIR PLUS', 'afsac' ),
		__( 'Import TRAINAIR PLUS', 'afsac' ),
		'manage_options',
		'afsac-import-trainair',
		'afsac_trainair_admin_page'
	);
}
add_action( 'admin_menu', 'afsac_trainair_admin_menu' );

/**
 * Rendu de la page d'admin d'import TRAINAIR PLUS (et exécution sur soumission).
 *
 * @return void
 */
function afsac_trainair_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$report = null;
	if ( isset( $_POST['afsac_trainair_run'] ) && check_admin_referer( 'afsac_import_trainair' ) ) {
		$report = afsac_seed_trainair();
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Import des cours TRAINAIR PLUS', 'afsac' ) . '</h1>';
	echo '<p>' . esc_html__( 'Crée / met à jour les cours TRAINAIR PLUS proposés par l’AFSAC (catalogue OACI) et leurs sessions planifiées. Idempotent : relancer met à jour sans dupliquer.', 'afsac' ) . '</p>';

	if ( is_array( $report ) ) {
		$class = $report['ok'] ? 'notice-success' : 'notice-error';
		echo '<div class="notice ' . esc_attr( $class ) . '"><p>' . esc_html( $report['message'] ) . '</p></div>';

		if ( ! empty( $report['items'] ) ) {
			echo '<table class="widefat striped"><thead><tr><th>#</th><th>' . esc_html__( 'Langue', 'afsac' ) . '</th><th>' . esc_html__( 'Formation', 'afsac' ) . '</th><th>' . esc_html__( 'Sessions', 'afsac' ) . '</th></tr></thead><tbody>';
			foreach ( $report['items'] as $item ) {
				echo '<tr><td>' . esc_html( $item['id'] ) . '</td><td>' . esc_html( $item['lang'] ) . '</td><td>' . esc_html( $item['title'] ) . '</td><td>' . esc_html( $item['sessions'] ) . '</td></tr>';
			}
			echo '</tbody></table>';
		}
	}

	echo '<form method="post" style="margin-top:1em;">';
	wp_nonce_field( 'afsac_import_trainair' );
	echo '<p><button type="submit" name="afsac_trainair_run" value="1" class="button button-primary">' . esc_html__( 'Lancer l’import', 'afsac' ) . '</button></p>';
	echo '</form>';
	echo '</div>';
}
