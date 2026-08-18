<?php
/**
 * Archive du CPT formation : catalogue.
 *
 * Deux rendus :
 *   1. ?famille=avsec[&format=cours|atelier] — PAGE DE LISTE AVSEC dédiée
 *      (template-parts/page-formations/avsec-liste.php), pendant AVSEC de
 *      l'archive de domaine TRAINAIR PLUS. C'est la cible des deux « Voir plus »
 *      du catalogue depuis la demande client du 07/08/2026.
 *   2. tout le reste — même page que l'archive d'un domaine OACI : bandeau
 *      clair, barre de recherche seule, lignes riches, tri et pagination client.
 *
 * ⚠️ Le rendu générique était resté le GABARIT D'ORIGINE (titre nu +
 * .afsac-grid + template-parts/card-formation.php), jamais repris par les
 * refontes : il ne ressemblait à aucune autre page du site. Réaligné le
 * 18/08/2026 sur taxonomy-afsac_area.php, dont il partage le shell `.afsac-area`,
 * les lignes `course-row` et le script `area-filters.js` — donc AUCUN CSS ni JS
 * spécifique. Le partiel card-formation.php, dont c'était l'unique appelant, a
 * été supprimé.
 *
 * Le filtre de famille / langue est posé en amont sur la requête principale
 * (afsac_formation_archive_filter, functions.php) ; le volet AVSEC, lui, passe par
 * les helpers afsac_avsec_*() du plugin, seuls capables de trier par typologie —
 * elle est dérivée de `_afsac_import_key`, et n'existe pas en taxonomie.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Filtres de lecture, pas d'écriture.
$afsac_famille = isset( $_GET['famille'] ) ? sanitize_key( wp_unslash( $_GET['famille'] ) ) : '';
$afsac_format  = isset( $_GET['format'] ) ? sanitize_key( wp_unslash( $_GET['format'] ) ) : '';
$afsac_langue  = isset( $_GET['langue'] ) ? sanitize_key( wp_unslash( $_GET['langue'] ) ) : '';
// phpcs:enable WordPress.Security.NonceVerification.Recommended

if ( 'avsec' === $afsac_famille && function_exists( 'afsac_avsec_get_courses' ) ) {

	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
		ob_start();
		rank_math_the_breadcrumbs();
		$afsac_crumbs = trim( ob_get_clean() );
		if ( '' !== $afsac_crumbs ) {
			echo '<div class="afsac-breadcrumb"><div class="afsac-container">' . $afsac_crumbs . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sortie Rank Math déjà assainie.
		}
	}

	get_template_part(
		'template-parts/page-formations/avsec-liste',
		null,
		array( 'format' => $afsac_format )
	);

	get_footer();
	return;
}

if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
	ob_start();
	rank_math_the_breadcrumbs();
	$afsac_crumbs = trim( ob_get_clean() );
	if ( '' !== $afsac_crumbs ) {
		echo '<div class="afsac-breadcrumb"><div class="afsac-container">' . $afsac_crumbs . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sortie Rank Math déjà assainie.
	}
}

/*
 * Titre : le NOM DU TERME filtré plutôt que « Formations ». Les tokens d'URL
 * (trainair / francais…) sont stables, mais les slugs de termes changent avec la
 * langue — on repasse donc par la même table de correspondance que le filtre de
 * requête, sans la dupliquer : on lit le terme effectivement appliqué.
 */
$afsac_lang_slug = function_exists( 'pll_current_language' ) ? (string) pll_current_language() : 'fr';
if ( ! in_array( $afsac_lang_slug, array( 'fr', 'en' ), true ) ) {
	$afsac_lang_slug = 'fr';
}
$afsac_fam_map = array(
	'trainair' => array( 'fr' => 'trainair-plus-fr', 'en' => 'trainair-plus' ),
	'avsec'    => array( 'fr' => 'avsec-fr', 'en' => 'avsec' ),
);
$afsac_lng_map = array(
	'fr' => array( 'fr' => 'francais', 'en' => 'french' ),
	'en' => array( 'fr' => 'anglais', 'en' => 'english' ),
	'ar' => array( 'fr' => 'arabe', 'en' => 'arabic' ),
);

$afsac_title  = post_type_archive_title( '', false );
$afsac_eyebrow = __( 'Catalogue OACI', 'afsac' );
$afsac_chips  = array();

if ( isset( $afsac_fam_map[ $afsac_famille ][ $afsac_lang_slug ] ) ) {
	$afsac_fam_term = get_term_by( 'slug', $afsac_fam_map[ $afsac_famille ][ $afsac_lang_slug ], 'afsac_famille' );
	if ( $afsac_fam_term && ! is_wp_error( $afsac_fam_term ) ) {
		$afsac_title   = $afsac_fam_term->name;
		$afsac_eyebrow = __( 'Programme', 'afsac' );
	}
}
if ( isset( $afsac_lng_map[ $afsac_langue ][ $afsac_lang_slug ] ) ) {
	$afsac_lng_term = get_term_by( 'slug', $afsac_lng_map[ $afsac_langue ][ $afsac_lang_slug ], 'afsac_langue' );
	if ( $afsac_lng_term && ! is_wp_error( $afsac_lng_term ) ) {
		$afsac_chips[] = $afsac_lng_term->name;
	}
}

// Description du terme filtré, sinon celle de l'archive. Rien n'est inventé ici :
// s'il n'y a pas de texte en base, le bandeau n'a pas de chapô.
$afsac_intro = '';
if ( ! empty( $afsac_fam_term ) && ! is_wp_error( $afsac_fam_term ) ) {
	$afsac_intro = (string) $afsac_fam_term->description;
}
if ( '' === trim( $afsac_intro ) ) {
	$afsac_intro = (string) get_the_archive_description();
}
$afsac_intro = trim( wp_strip_all_tags( $afsac_intro ) );

$afsac_back = function_exists( 'afsac_get_catalogue_url' ) ? afsac_get_catalogue_url() : '';

// Prochaines sessions batchées, comme sur l'archive d'un domaine (1 requête).
global $wp_query;
$afsac_post_ids = ( $wp_query instanceof WP_Query ) ? wp_list_pluck( $wp_query->posts, 'ID' ) : array();
$afsac_next_map = function_exists( 'afsac_get_next_sessions_map' ) ? afsac_get_next_sessions_map( $afsac_post_ids ) : array();
?>

<main id="primary" class="afsac-area">

	<header class="afsac-area-hero">
		<div class="afsac-container">
			<?php if ( $afsac_back ) : ?>
				<a class="afsac-area-hero__back" href="<?php echo esc_url( $afsac_back ); ?>"><span aria-hidden="true">‹</span> <?php esc_html_e( 'Retour au catalogue', 'afsac' ); ?></a>
			<?php endif; ?>
			<span class="afsac-area-hero__eyebrow"><?php echo esc_html( $afsac_eyebrow ); ?></span>
			<h1 class="afsac-area-hero__title"><?php echo esc_html( $afsac_title ); ?></h1>
			<?php if ( '' !== $afsac_intro ) : ?>
				<p class="afsac-area-hero__intro"><?php echo esc_html( $afsac_intro ); ?></p>
			<?php endif; ?>
			<?php if ( $afsac_chips ) : ?>
				<p class="afsac-area-hero__intro">
					<?php
					/* translators: %s: filtre actif (ex. « Français »). */
					printf( esc_html__( 'Filtre actif : %s', 'afsac' ), esc_html( implode( ' · ', $afsac_chips ) ) );
					?>
				</p>
			<?php endif; ?>
		</div>
	</header>

	<section class="afsac-area__list">
		<div class="afsac-container">
			<?php if ( have_posts() ) : ?>

				<?php
				/*
				 * Les options du filtre « Domaine » sont dérivées du jeu RÉELLEMENT
				 * affiché, pas de la taxonomie entière : pas de domaine proposé qui
				 * ne renverrait aucun résultat.
				 */
				$afsac_domains = array();

				ob_start();
				while ( have_posts() ) :
					the_post();
					$afsac_fid  = get_the_ID();
					$afsac_next = isset( $afsac_next_map[ $afsac_fid ] ) ? $afsac_next_map[ $afsac_fid ] : null;
					$afsac_args = afsac_build_course_row_args( $afsac_fid, $afsac_next );

					if ( '' !== $afsac_args['area_slug'] ) {
						$afsac_domains[ $afsac_args['area_slug'] ] = $afsac_args['area_name'];
					}

					get_template_part( 'template-parts/course-row', null, $afsac_args );
				endwhile;
				$afsac_rows_html = ob_get_clean();

				// Tri alphabétique sur le libellé traduit, pas sur le slug.
				natcasesort( $afsac_domains );
				?>

				<div class="afsac-area-filters afsac-reveal">
					<?php if ( count( $afsac_domains ) > 1 ) : ?>
						<label class="afsac-area-filters__field">
							<span class="screen-reader-text"><?php esc_html_e( 'Domaine OACI', 'afsac' ); ?></span>
							<select class="afsac-area-filters__select" data-area-filter="area">
								<option value=""><?php esc_html_e( 'Tous les domaines', 'afsac' ); ?></option>
								<?php foreach ( $afsac_domains as $afsac_dslug => $afsac_dname ) : ?>
									<option value="<?php echo esc_attr( $afsac_dslug ); ?>"><?php echo esc_html( $afsac_dname ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					<?php endif; ?>

					<div class="afsac-area-filters__search">
						<svg class="afsac-area-filters__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4.3-4.3"></path></svg>
						<input type="search" class="afsac-area-filters__input" data-area-search placeholder="<?php esc_attr_e( 'Rechercher un cours…', 'afsac' ); ?>" aria-label="<?php esc_attr_e( 'Rechercher un cours', 'afsac' ); ?>">
					</div>
				</div>

				<div class="afsac-area-resulthead afsac-reveal">
					<div class="afsac-area-resulthead__left">
						<span class="afsac-area-resultcount" data-area-count data-singular="<?php esc_attr_e( 'cours', 'afsac' ); ?>" data-plural="<?php esc_attr_e( 'cours', 'afsac' ); ?>"></span>
						<label class="afsac-area-sort">
							<span class="afsac-area-sort__label"><?php esc_html_e( 'trier par :', 'afsac' ); ?></span>
							<select class="afsac-area-sort__select" data-area-sort>
								<option value="type"><?php esc_html_e( 'Type', 'afsac' ); ?></option>
								<option value="title"><?php esc_html_e( 'Titre (A→Z)', 'afsac' ); ?></option>
							</select>
						</label>
					</div>
				</div>

				<div class="afsac-area__rows" data-area-rows data-page-size="10">
					<?php echo $afsac_rows_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Lignes échappées dans course-row.php. ?>
				</div>

				<p class="afsac-area__noresult" data-area-noresult hidden><?php esc_html_e( 'Aucun cours ne correspond à vos critères.', 'afsac' ); ?></p>

				<nav class="afsac-area-pager" data-area-pager aria-label="<?php esc_attr_e( 'Pagination', 'afsac' ); ?>"></nav>

			<?php else : ?>

				<div class="afsac-area__empty">
					<h2 class="afsac-area__empty-title"><?php esc_html_e( 'Aucun cours pour cette sélection', 'afsac' ); ?></h2>
					<p class="afsac-area__empty-text"><?php esc_html_e( 'Contactez-nous pour organiser une session sur mesure ou recevoir le programme détaillé.', 'afsac' ); ?></p>
					<a class="afsac-button" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Demander une session', 'afsac' ); ?></a>
				</div>

			<?php endif; ?>
		</div>
	</section>

</main>

<?php
get_footer();
