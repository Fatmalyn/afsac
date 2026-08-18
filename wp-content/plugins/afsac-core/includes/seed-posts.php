<?php
/**
 * Seed des actualités de démonstration (articles WP natifs + Polylang).
 *
 * Crée des catégories et des articles fr↔en réalistes (aviation civile /
 * formation), avec image à la une sideloadée depuis le thème (repli motif).
 * Idempotent : marqueurs `_afsac_demo_post` + `_afsac_demo_key` → ré-exécuter
 * met à jour au lieu de dupliquer. Assigne aussi le template « Actualités » aux
 * pages d'index fr/en. Commande : `wp afsac seed-posts`.
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Catégories de démonstration (paires fr/en).
 *
 * @return array<string,array{fr:string,en:string}>
 */
function afsac_seed_post_categories() {
	return array(
		'formation'      => array(
			'fr' => 'Formation',
			'en' => 'Training',
		),
		'reglementation' => array(
			'fr' => 'Réglementation OACI',
			'en' => 'ICAO Regulation',
		),
	);
}

/**
 * Articles de démonstration (paires fr/en).
 *
 * @return array<int,array<string,mixed>>
 */
function afsac_seed_post_articles() {
	return array(
		array(
			'key'   => 'trainair-plus-tunis',
			'cat'   => 'formation',
			'image' => 'afsac-cours-salle-formation.png',
			'fr'    => array(
				'title'   => 'L’AFSAC accueille une nouvelle session TRAINAIR PLUS à Tunis',
				'excerpt' => 'Une nouvelle session du programme TRAINAIR PLUS de l’OACI s’est ouverte au campus de Tunis, réunissant des participants de plusieurs autorités de l’aviation civile.',
				'content' => "<!-- wp:paragraph --><p>L’AFSAC a inauguré cette semaine une nouvelle session du programme <strong>TRAINAIR PLUS</strong> de l’OACI, accueillant des participants venus de plusieurs autorités de l’aviation civile de la région.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Des cours standardisés (STP) reconnus</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Les <em>Standardized Training Packages</em> garantissent une qualité homogène et une reconnaissance internationale des compétences acquises par les stagiaires.</p><!-- /wp:paragraph -->",
			),
			'en'    => array(
				'title'   => 'AFSAC hosts a new TRAINAIR PLUS session in Tunis',
				'excerpt' => 'A new session of ICAO’s TRAINAIR PLUS programme opened at the Tunis campus, bringing together participants from several civil aviation authorities.',
				'content' => "<!-- wp:paragraph --><p>This week AFSAC launched a new session of ICAO’s <strong>TRAINAIR PLUS</strong> programme, welcoming participants from several civil aviation authorities across the region.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Recognized Standardized Training Packages (STP)</h2><!-- /wp:heading --><!-- wp:paragraph --><p><em>Standardized Training Packages</em> ensure consistent quality and international recognition of the skills gained by trainees.</p><!-- /wp:paragraph -->",
			),
		),
		array(
			'key'   => 'avsec-certification',
			'cat'   => 'reglementation',
			'image' => 'afsac-cours-reunion-institutionnelle.png',
			'fr'    => array(
				'title'   => 'Renforcement des compétences AVSEC : une nouvelle certification OACI',
				'excerpt' => 'Le programme de sûreté de l’aviation civile (AVSEC) s’enrichit d’un nouveau parcours certifiant, disponible en français, anglais et arabe.',
				'content' => "<!-- wp:paragraph --><p>Le programme <strong>AVSEC</strong> de sûreté de l’aviation civile s’enrichit d’un nouveau parcours certifiant, aligné sur l’Annexe 17 de la Convention de Chicago.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>La formation est proposée en français, en anglais et en arabe, afin de couvrir l’ensemble des publics de la région.</p><!-- /wp:paragraph -->",
			),
			'en'    => array(
				'title'   => 'Strengthening AVSEC skills: a new ICAO certification',
				'excerpt' => 'The civil aviation security (AVSEC) programme adds a new certified track, available in French, English and Arabic.',
				'content' => "<!-- wp:paragraph --><p>The civil aviation security <strong>AVSEC</strong> programme adds a new certified track, aligned with Annex 17 to the Chicago Convention.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>The course is delivered in French, English and Arabic to serve all audiences across the region.</p><!-- /wp:paragraph -->",
			),
		),
		array(
			'key'   => 'usoap-audit',
			'cat'   => 'reglementation',
			'image' => 'afsac-cours-tour-controle.png',
			'fr'    => array(
				'title'   => 'Préparer les audits USOAP : retour sur notre atelier régional',
				'excerpt' => 'Un atelier dédié à la méthode de surveillance continue USOAP a réuni des experts pour outiller les États dans leurs auto-évaluations.',
				'content' => "<!-- wp:paragraph --><p>Notre atelier régional consacré à l’approche de surveillance continue <strong>USOAP</strong> a réuni des experts venus accompagner les États dans leurs auto-évaluations.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Des plans d’action concrets</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Les participants sont repartis avec des feuilles de route opérationnelles pour combler les écarts identifiés.</p><!-- /wp:paragraph -->",
			),
			'en'    => array(
				'title'   => 'Preparing for USOAP audits: insights from our regional workshop',
				'excerpt' => 'A workshop dedicated to the USOAP continuous monitoring approach gathered experts to support States in their self-assessments.',
				'content' => "<!-- wp:paragraph --><p>Our regional workshop on the <strong>USOAP</strong> continuous monitoring approach gathered experts to support States in their self-assessments.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Concrete action plans</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Participants left with operational roadmaps to close the gaps they had identified.</p><!-- /wp:paragraph -->",
			),
		),
		array(
			'key'   => 'partenariat-afrique',
			'cat'   => 'formation',
			'image' => '',
			'fr'    => array(
				'title'   => 'Nouveau partenariat pour la formation aéronautique en Afrique de l’Ouest',
				'excerpt' => 'L’AFSAC signe un accord pour développer des sessions mutualisées et renforcer les capacités de formation dans la sous-région.',
				'content' => "<!-- wp:paragraph --><p>L’AFSAC a signé un accord de partenariat visant à développer des sessions mutualisées et à renforcer les capacités de formation aéronautique en Afrique de l’Ouest.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Ce rapprochement facilitera l’accès des professionnels aux cours OACI tout en optimisant les coûts pour les États partenaires.</p><!-- /wp:paragraph -->",
			),
			'en'    => array(
				'title'   => 'New partnership for aviation training in West Africa',
				'excerpt' => 'AFSAC signs an agreement to develop shared sessions and strengthen training capacity across the sub-region.',
				'content' => "<!-- wp:paragraph --><p>AFSAC has signed a partnership agreement to develop shared sessions and strengthen aviation training capacity across West Africa.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>The collaboration will improve access to ICAO courses for professionals while optimizing costs for partner States.</p><!-- /wp:paragraph -->",
			),
		),
	);
}

/**
 * Crée (ou réutilise) l'image à la une de démo, sideloadée depuis le thème.
 *
 * Idempotent via la méta `_afsac_demo_img` = clé d'article.
 *
 * @param string $filename Nom du fichier dans assets/demo-cours/.
 * @param string $key      Clé stable de l'article.
 * @return int ID de la pièce jointe, ou 0 (repli motif).
 */
function afsac_seed_demo_image( $filename, $key ) {
	if ( '' === $filename ) {
		return 0;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_afsac_demo_img', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	if ( ! empty( $existing ) ) {
		return (int) $existing[0];
	}

	$src = trailingslashit( get_template_directory() ) . 'assets/demo-cours/' . $filename;
	if ( ! file_exists( $src ) ) {
		return 0;
	}

	$upload = wp_upload_bits( $filename, null, file_get_contents( $src ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- lecture d'un asset local du thème.
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}

	$filetype = wp_check_filetype( $upload['file'] );
	$attach   = array(
		'post_mime_type' => $filetype['type'],
		'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);
	$aid = wp_insert_attachment( $attach, $upload['file'] );
	if ( is_wp_error( $aid ) || ! $aid ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $aid, wp_generate_attachment_metadata( $aid, $upload['file'] ) );
	update_post_meta( $aid, '_afsac_demo_img', $key );
	update_post_meta( $aid, '_afsac_demo_post', 1 );

	return (int) $aid;
}

/**
 * Insère ou met à jour un article de démo dans une langue donnée.
 *
 * @param array  $data      Données de l'article pour la langue (title/excerpt/content).
 * @param string $key       Clé stable de l'article.
 * @param string $lang      Langue Polylang.
 * @param int    $cat_id    ID de catégorie (langue correspondante).
 * @param int    $thumb_id  ID de l'image à la une (0 = aucune).
 * @param array  $report    Compteur passé par référence.
 * @return int ID du post, ou 0.
 */
function afsac_seed_demo_post( $data, $key, $lang, $cat_id, $thumb_id, &$report ) {
	$existing = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'lang'           => $lang,
			'meta_key'       => '_afsac_demo_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);

	$postarr = array(
		'post_type'    => 'post',
		'post_status'  => 'publish',
		'post_title'   => $data['title'],
		'post_excerpt' => $data['excerpt'],
		'post_content' => $data['content'],
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

	if ( function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $post_id, $lang );
	}
	if ( $cat_id ) {
		wp_set_post_categories( $post_id, array( (int) $cat_id ), false );
	}
	if ( $thumb_id ) {
		set_post_thumbnail( $post_id, (int) $thumb_id );
	}
	update_post_meta( $post_id, '_afsac_demo_post', 1 );
	update_post_meta( $post_id, '_afsac_demo_key', $key );

	return $post_id;
}

/**
 * Exécute le seed complet des actualités (catégories + articles fr↔en).
 *
 * @return array{ok:bool,message:string,created:int,updated:int,linked:int,pairs:array}
 */
function afsac_seed_posts() {
	$report = array(
		'ok'      => false,
		'message' => '',
		'created' => 0,
		'updated' => 0,
		'reused'  => 0,
		'linked'  => 0,
		'pairs'   => array(),
	);

	if ( ! function_exists( 'pll_set_post_language' ) || ! function_exists( 'pll_save_post_translations' ) || ! function_exists( 'pll_languages_list' ) ) {
		$report['message'] = 'Polylang est requis (fonctions pll_* introuvables).';
		return $report;
	}
	$langs = pll_languages_list();
	if ( ! in_array( 'fr', $langs, true ) || ! in_array( 'en', $langs, true ) ) {
		$report['message'] = 'Les langues Polylang « fr » et « en » doivent être configurées. Trouvées : ' . implode( ', ', $langs );
		return $report;
	}

	// 1) Catégories appariées (réutilise le helper de seed-terms). Compteur séparé
	// pour ne pas gonfler le total d'articles.
	$cat_report = array(
		'created' => 0,
		'reused'  => 0,
	);
	$cat_ids = array();
	foreach ( afsac_seed_post_categories() as $ckey => $names ) {
		$fr_id = afsac_seed_term( $names['fr'], 'category', 'fr', $cat_report );
		$en_id = afsac_seed_term( $names['en'], 'category', 'en', $cat_report );
		if ( $fr_id && $en_id && function_exists( 'pll_save_term_translations' ) ) {
			pll_save_term_translations(
				array(
					'fr' => $fr_id,
					'en' => $en_id,
				)
			);
		}
		$cat_ids[ $ckey ] = array(
			'fr' => $fr_id,
			'en' => $en_id,
		);
	}

	// 2) Articles fr + en, image à la une, puis liaison de la paire.
	foreach ( afsac_seed_post_articles() as $article ) {
		$key      = $article['key'];
		$thumb_id = afsac_seed_demo_image( $article['image'], $key );
		$cat      = isset( $cat_ids[ $article['cat'] ] ) ? $cat_ids[ $article['cat'] ] : array(
			'fr' => 0,
			'en' => 0,
		);

		$fr_id = afsac_seed_demo_post( $article['fr'], $key, 'fr', $cat['fr'], $thumb_id, $report );
		$en_id = afsac_seed_demo_post( $article['en'], $key, 'en', $cat['en'], $thumb_id, $report );

		if ( $fr_id && $en_id ) {
			pll_save_post_translations(
				array(
					'fr' => $fr_id,
					'en' => $en_id,
				)
			);
			$report['linked']++;
			$report['pairs'][] = array(
				'fr' => $article['fr']['title'] . ' (#' . $fr_id . ')',
				'en' => $article['en']['title'] . ' (#' . $en_id . ')',
			);
		}
	}

	// 3) Assigne le template Actualités aux pages d'index fr/en (idempotent).
	$assigned = array();
	$news_fr  = get_page_by_path( 'actualites' );
	$news_en  = get_page_by_path( 'news' );
	foreach ( array( $news_fr, $news_en ) as $news_page ) {
		if ( $news_page instanceof WP_Post ) {
			update_post_meta( $news_page->ID, '_wp_page_template', 'template-actualites.php' );
			$assigned[] = $news_page->ID;
		}
	}

	$report['ok']      = true;
	$report['message'] = sprintf(
		'%d article(s) créé(s), %d mis à jour, %d paire(s) fr↔en liée(s) ; %d catégorie(s) créée(s)/%d réutilisée(s) ; template assigné aux pages : %s.',
		$report['created'],
		$report['updated'],
		$report['linked'],
		$cat_report['created'],
		$cat_report['reused'],
		$assigned ? implode( ', ', $assigned ) : '(aucune)'
	);

	return $report;
}

/**
 * Commande WP-CLI : wp afsac seed-posts
 *
 * @return void
 */
function afsac_cli_seed_posts() {
	$r = afsac_seed_posts();
	if ( ! $r['ok'] ) {
		WP_CLI::error( $r['message'] );
		return;
	}
	WP_CLI::log( '— Paires fr↔en liées —' );
	if ( $r['pairs'] ) {
		WP_CLI\Utils\format_items( 'table', $r['pairs'], array( 'fr', 'en' ) );
	}
	WP_CLI::success( $r['message'] );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'afsac seed-posts', 'afsac_cli_seed_posts' );
}
