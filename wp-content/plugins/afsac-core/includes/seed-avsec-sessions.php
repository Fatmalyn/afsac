<?php
/**
 * Seed idempotent des SESSIONS planifiées des cours AVSEC.
 *
 * Les 19 fiches AVSEC importées par seed-formations.php sont des fiches de
 * catalogue : elles ne portaient aucune date. La page Catalogue (volet AVSEC) et
 * le calendrier ont besoin de sessions réelles pour afficher « prochaines
 * sessions ». Ce fichier planifie un calendrier de démonstration, modifiable
 * ensuite dans l'admin comme n'importe quelle session.
 *
 * Règles respectées :
 *   - la session porte la MÊME langue Polylang que la fiche qu'elle vise (une
 *     session FR sous une fiche EN n'apparaîtrait dans aucune des deux listes) ;
 *   - la relation `_afsac_formation_id` stocke l'ID CANONIQUE (langue par
 *     défaut, FR) de la formation — cf. includes/relations.php ;
 *   - la fiche visée est retrouvée par sa clé stable `_afsac_import_key`, jamais
 *     par un ID en dur.
 *
 * Idempotent : marqueur `_afsac_import_session_key` → relancer met à jour, ne
 * duplique pas. Réutilise afsac_import_set_field() (seed-formations.php).
 *
 * Lancement : « wp afsac seed-avsec-sessions » OU Outils → « Sessions AVSEC ».
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calendrier planifié (dates au format Ymd attendu par le date picker ACF).
 *
 * `course` = clé d'import de la fiche visée ; `lang` = langue de la session, qui
 * doit exister pour cette fiche (les cours « landside » et
 * « behaviour-detection » n'existent qu'en anglais).
 *
 * @return array<int,array<string,string>>
 */
function afsac_avsec_planned_sessions() {
	return array(
		// --- Français -------------------------------------------------------
		array(
			'key'    => 'base-tunis-2609',
			'course' => 'avsec-formation-base',
			'lang'   => 'fr',
			'title'  => 'Formation de base — Tunis · 21 sept.–2 oct. 2026',
			'debut'  => '20260921',
			'fin'    => '20261002',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => 'pnsac-tunis-2610',
			'course' => 'avsec-pnsac',
			'lang'   => 'fr',
			'title'  => 'Atelier PNSAC — Tunis · 19–23 oct. 2026',
			'debut'  => '20261019',
			'fin'    => '20261023',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => 'inspecteurs-alger-2611',
			'course' => 'avsec-inspecteurs',
			'lang'   => 'fr',
			'title'  => 'Inspecteurs nationaux — Alger · 16–24 nov. 2026',
			'debut'  => '20261116',
			'fin'    => '20261124',
			'lieu'   => 'Alger, Algérie',
		),
		array(
			'key'    => 'crises-tunis-2612',
			'course' => 'avsec-gestion-crises',
			'lang'   => 'fr',
			'title'  => 'Atelier Gestion de crises — Tunis · 7–11 déc. 2026',
			'debut'  => '20261207',
			'fin'    => '20261211',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => 'fret-casa-2701',
			'course' => 'avsec-fret-poste',
			'lang'   => 'fr',
			'title'  => 'Sûreté du fret et de la poste — Casablanca · 18–22 janv. 2027',
			'debut'  => '20270118',
			'fin'    => '20270122',
			'lieu'   => 'Casablanca, Maroc',
		),
		array(
			'key'    => 'instructeurs-tunis-2702',
			'course' => 'avsec-instructeurs',
			'lang'   => 'fr',
			'title'  => 'Instructeurs nationaux — Tunis · 15–23 févr. 2027',
			'debut'  => '20270215',
			'fin'    => '20270223',
			'lieu'   => 'Tunis, Tunisie',
		),

		// --- Anglais --------------------------------------------------------
		array(
			'key'    => 'basic-tunis-2610-en',
			'course' => 'avsec-formation-base',
			'lang'   => 'en',
			'title'  => 'AVSEC Basic — Tunis · 5–9 Oct 2026',
			'debut'  => '20261005',
			'fin'    => '20261009',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => 'inspectors-tunis-2611-en',
			'course' => 'avsec-inspecteurs',
			'lang'   => 'en',
			'title'  => 'National Inspectors — Tunis · 23 Nov–1 Dec 2026',
			'debut'  => '20261123',
			'fin'    => '20261201',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => 'behaviour-tunis-2701-en',
			'course' => 'avsec-behaviour-detection',
			'lang'   => 'en',
			'title'  => 'Behaviour Detection — Tunis · 11–15 Jan 2027',
			'debut'  => '20270111',
			'fin'    => '20270115',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => 'landside-dakar-2703-en',
			'course' => 'avsec-landside',
			'lang'   => 'en',
			'title'  => 'Airport Landside Security — Dakar · 1–5 Mar 2027',
			'debut'  => '20270301',
			'fin'    => '20270305',
			'lieu'   => 'Dakar, Sénégal',
		),
		array(
			'key'    => 'cargo-tunis-2704-en',
			'course' => 'avsec-fret-poste',
			'lang'   => 'en',
			'title'  => 'Air Cargo and Mail Security — Tunis · 12–16 Apr 2027',
			'debut'  => '20270412',
			'fin'    => '20270416',
			'lieu'   => 'Tunis, Tunisia',
		),
	);
}

/**
 * Retrouve une fiche AVSEC par sa clé d'import, dans une langue donnée.
 *
 * @param string $key  Clé `_afsac_import_key`.
 * @param string $lang Langue Polylang (« fr » | « en »).
 * @return int ID du post, ou 0 si absent dans cette langue.
 */
function afsac_avsec_find_course( $key, $lang ) {
	$found = get_posts(
		array(
			'post_type'      => 'afsac_formation',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'lang'           => $lang,
			'meta_key'       => '_afsac_import_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);

	return ! empty( $found ) ? (int) $found[0] : 0;
}

/**
 * Crée ou met à jour une session AVSEC planifiée.
 *
 * @param array $s      Entrée du calendrier.
 * @param array $report Compteurs passés par référence.
 * @return int ID de la session, ou 0 si la fiche visée est introuvable.
 */
function afsac_seed_avsec_session( $s, &$report ) {
	$formation_id = afsac_avsec_find_course( $s['course'], $s['lang'] );
	if ( ! $formation_id ) {
		$report['skipped'][] = $s['key'] . ' (fiche « ' . $s['course'] . ' » absente en ' . strtoupper( $s['lang'] ) . ')';
		return 0;
	}

	// La relation vise TOUJOURS l'ID canonique (langue par défaut).
	$canonical_id = function_exists( 'afsac_get_default_lang_id' )
		? (int) afsac_get_default_lang_id( $formation_id )
		: $formation_id;

	$skey     = 'avsec-' . $s['key'];
	$existing = get_posts(
		array(
			'post_type'      => 'afsac_session',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'lang'           => $s['lang'],
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
		$report['updated']++;
	} else {
		$session_id = wp_insert_post( $postarr, true );
		$report['created']++;
	}

	if ( is_wp_error( $session_id ) || ! $session_id ) {
		return 0;
	}
	$session_id = (int) $session_id;

	if ( function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $session_id, $s['lang'] );
	}

	update_post_meta( $session_id, '_afsac_formation_id', $canonical_id );

	afsac_import_set_field( $session_id, 'afsac_date_debut', $s['debut'] );
	afsac_import_set_field( $session_id, 'afsac_date_fin', $s['fin'] );
	afsac_import_set_field( $session_id, 'afsac_lieu', $s['lieu'] );
	afsac_import_set_field( $session_id, 'afsac_hote', 'AFSAC' );
	afsac_import_set_field( $session_id, 'afsac_statut', 'ouvert' );

	// Langue d'animation : terme de taxonomie apparié FR↔EN (idempotent).
	$langue = ( 'fr' === $s['lang'] )
		? afsac_import_term_pair( 'Français', 'French', 'afsac_langue', $report['terms'] )
		: afsac_import_term_pair( 'Anglais', 'English', 'afsac_langue', $report['terms'] );

	if ( ! empty( $langue[ $s['lang'] ] ) ) {
		wp_set_object_terms( $session_id, array( (int) $langue[ $s['lang'] ] ), 'afsac_langue', false );
		afsac_import_set_field( $session_id, 'afsac_langue_session', (int) $langue[ $s['lang'] ] );
	}

	update_post_meta( $session_id, '_afsac_import_session', 1 );
	update_post_meta( $session_id, '_afsac_import_session_key', $skey );

	$report['items'][] = array(
		'id'    => $session_id,
		'lang'  => strtoupper( $s['lang'] ),
		'title' => $s['title'],
		'debut' => $s['debut'],
	);

	return $session_id;
}

/**
 * Exécute le seed complet des sessions AVSEC.
 *
 * @return array{ok:bool,message:string,created:int,updated:int,skipped:array,terms:array,items:array}
 */
function afsac_seed_avsec_sessions() {
	$report = array(
		'ok'      => false,
		'message' => '',
		'created' => 0,
		'updated' => 0,
		'skipped' => array(),
		'terms'   => array(
			'created' => 0,
			'reused'  => 0,
		),
		'items'   => array(),
	);

	if ( ! function_exists( 'pll_set_post_language' ) ) {
		$report['message'] = 'Polylang est requis (fonctions pll_* introuvables).';
		return $report;
	}
	if ( ! function_exists( 'afsac_import_set_field' ) || ! function_exists( 'afsac_import_term_pair' ) ) {
		$report['message'] = 'Helpers d’import introuvables (seed-formations.php doit être chargé).';
		return $report;
	}

	foreach ( afsac_avsec_planned_sessions() as $s ) {
		afsac_seed_avsec_session( $s, $report );
	}

	$report['ok']      = true;
	$report['message'] = sprintf(
		'%d session(s) créée(s), %d mise(s) à jour%s.',
		$report['created'],
		$report['updated'],
		$report['skipped'] ? ' ; ' . count( $report['skipped'] ) . ' ignorée(s) : ' . implode( ', ', $report['skipped'] ) : ''
	);

	return $report;
}

/**
 * Commande WP-CLI : wp afsac seed-avsec-sessions
 *
 * @return void
 */
function afsac_cli_seed_avsec_sessions() {
	$r = afsac_seed_avsec_sessions();
	if ( ! $r['ok'] ) {
		WP_CLI::error( $r['message'] );
		return;
	}
	if ( $r['items'] ) {
		WP_CLI\Utils\format_items( 'table', $r['items'], array( 'id', 'lang', 'debut', 'title' ) );
	}
	WP_CLI::success( $r['message'] );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'afsac seed-avsec-sessions', 'afsac_cli_seed_avsec_sessions' );
}

/**
 * Repli non-CLI : page d'admin sous « Outils ».
 *
 * @return void
 */
function afsac_avsec_sessions_admin_menu() {
	add_management_page(
		__( 'Sessions AVSEC', 'afsac' ),
		__( 'Sessions AVSEC', 'afsac' ),
		'manage_options',
		'afsac-seed-avsec-sessions',
		'afsac_avsec_sessions_admin_page'
	);
}
add_action( 'admin_menu', 'afsac_avsec_sessions_admin_menu' );

/**
 * Rendu de la page d'admin (et exécution sur soumission).
 *
 * @return void
 */
function afsac_avsec_sessions_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$report = null;
	if ( isset( $_POST['afsac_avsec_sessions_run'] ) && check_admin_referer( 'afsac_seed_avsec_sessions' ) ) {
		$report = afsac_seed_avsec_sessions();
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Sessions AVSEC planifiées', 'afsac' ) . '</h1>';
	echo '<p>' . esc_html__( 'Crée / met à jour le calendrier des sessions AVSEC (dates, lieu, langue) sur les fiches importées. Idempotent : relancer met à jour sans dupliquer. Les dates restent modifiables ensuite dans Sessions.', 'afsac' ) . '</p>';

	if ( is_array( $report ) ) {
		$class = $report['ok'] ? 'notice-success' : 'notice-error';
		echo '<div class="notice ' . esc_attr( $class ) . '"><p>' . esc_html( $report['message'] ) . '</p></div>';

		if ( ! empty( $report['items'] ) ) {
			echo '<table class="widefat striped"><thead><tr><th>#</th><th>' . esc_html__( 'Langue', 'afsac' ) . '</th><th>' . esc_html__( 'Début', 'afsac' ) . '</th><th>' . esc_html__( 'Session', 'afsac' ) . '</th></tr></thead><tbody>';
			foreach ( $report['items'] as $item ) {
				echo '<tr><td>' . esc_html( $item['id'] ) . '</td><td>' . esc_html( $item['lang'] ) . '</td><td>' . esc_html( $item['debut'] ) . '</td><td>' . esc_html( $item['title'] ) . '</td></tr>';
			}
			echo '</tbody></table>';
		}
	}

	echo '<form method="post" style="margin-top:1em;">';
	wp_nonce_field( 'afsac_seed_avsec_sessions' );
	echo '<p><button type="submit" name="afsac_avsec_sessions_run" value="1" class="button button-primary">' . esc_html__( 'Planifier les sessions', 'afsac' ) . '</button></p>';
	echo '</form>';
	echo '</div>';
}
