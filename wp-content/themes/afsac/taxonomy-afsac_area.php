<?php
/**
 * Archive d'un domaine OACI (« afsac_area ») — page « Liste de cours (sous-domaine) ».
 *
 * Bandeau d'en-tête (accent plein), barre de RECHERCHE seule, en-tête de
 * résultats et lignes riches. Tout le set du domaine est chargé en une requête
 * (pre_get_posts) ; prochaines sessions batchées ; recherche / tri / pagination /
 * compteur côté client (area-filters.js).
 *
 * ⚠️ Demande client 18/08/2026 : « enlève tout type de filtre, laisse juste la
 * barre de recherche ». Onglets méthode, chips de sous-domaines, dropdowns
 * (localisation / langue / type) et cases à cocher (sessions à venir / virtuel /
 * tarif réduit) ont été RETIRÉS. Les attributs `data-*` des lignes sont
 * conservés (cf. template-parts/course-row.php) pour pouvoir les rétablir.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
	ob_start();
	rank_math_the_breadcrumbs();
	$afsac_crumbs = trim( ob_get_clean() );
	if ( '' !== $afsac_crumbs ) {
		echo '<div class="afsac-breadcrumb"><div class="afsac-container">' . $afsac_crumbs . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sortie Rank Math déjà assainie.
	}
}

$afsac_term = get_queried_object();

$afsac_accent     = ( $afsac_term instanceof WP_Term && function_exists( 'afsac_get_area_accent_color' ) )
	? afsac_get_area_accent_color( $afsac_term )
	: '';
$afsac_accent_css = ( '' !== $afsac_accent ) ? $afsac_accent : 'var(--afsac-navy-mid)';

$afsac_intro = '';
if ( $afsac_term instanceof WP_Term ) {
	$afsac_intro = function_exists( 'get_field' ) ? (string) get_field( 'afsac_area_intro', $afsac_term ) : '';
	if ( '' === trim( $afsac_intro ) ) {
		$afsac_intro = (string) $afsac_term->description;
	}
	$afsac_intro = trim( wp_strip_all_tags( $afsac_intro ) );
}

$afsac_back = function_exists( 'afsac_get_catalogue_url' ) ? afsac_get_catalogue_url() : '';

// Tout le set du domaine (1 requête) + batch des prochaines sessions.
global $wp_query;
$afsac_post_ids = ( $wp_query instanceof WP_Query ) ? wp_list_pluck( $wp_query->posts, 'ID' ) : array();
$afsac_next_map = function_exists( 'afsac_get_next_sessions_map' ) ? afsac_get_next_sessions_map( $afsac_post_ids ) : array();

?>

<main id="primary" class="afsac-area" style="--afsac-area-accent: <?php echo esc_attr( $afsac_accent_css ); ?>;">

	<?php /* 1. BANDEAU D'EN-TÊTE — fond accent plein, texte blanc, onglets intégrés. */ ?>
	<header class="afsac-area-hero">
		<div class="afsac-container">
			<?php if ( $afsac_back ) : ?>
				<a class="afsac-area-hero__back" href="<?php echo esc_url( $afsac_back ); ?>"><span aria-hidden="true">‹</span> <?php esc_html_e( 'Retour aux domaines', 'afsac' ); ?></a>
			<?php endif; ?>
			<span class="afsac-area-hero__eyebrow"><?php esc_html_e( 'Domaine OACI', 'afsac' ); ?></span>
			<h1 class="afsac-area-hero__title"><?php echo esc_html( $afsac_term instanceof WP_Term ? $afsac_term->name : single_term_title( '', false ) ); ?></h1>
			<?php if ( '' !== $afsac_intro ) : ?>
				<p class="afsac-area-hero__intro"><?php echo esc_html( $afsac_intro ); ?></p>
			<?php endif; ?>
		</div>
	</header>

	<section class="afsac-area__list">
		<div class="afsac-container">
			<?php if ( have_posts() ) : ?>

				<?php
				// Passe d'affichage : buffer des lignes (plus aucune option de filtre à collecter).
				ob_start();
				while ( have_posts() ) :
					the_post();
					$afsac_fid  = get_the_ID();
					$afsac_next = isset( $afsac_next_map[ $afsac_fid ] ) ? $afsac_next_map[ $afsac_fid ] : null;

					get_template_part(
						'template-parts/course-row',
						null,
						afsac_build_course_row_args( $afsac_fid, $afsac_next )
					);
				endwhile;
				$afsac_rows_html = ob_get_clean();
				?>
				<?php /* Barre de RECHERCHE seule (cf. entête du fichier : plus aucun filtre). */ ?>
				<div class="afsac-area-filters afsac-reveal">
					<div class="afsac-area-filters__search">
						<svg class="afsac-area-filters__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4.3-4.3"></path></svg>
						<input type="search" class="afsac-area-filters__input" data-area-search placeholder="<?php esc_attr_e( 'Rechercher un cours…', 'afsac' ); ?>" aria-label="<?php esc_attr_e( 'Rechercher un cours', 'afsac' ); ?>">
					</div>
				</div>

				<?php /* En-tête de résultats : compte + tri (le tri n'est PAS un filtre, conservé). */ ?>
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
					<h2 class="afsac-area__empty-title"><?php esc_html_e( 'Aucun cours programmé pour ce domaine', 'afsac' ); ?></h2>
					<p class="afsac-area__empty-text"><?php esc_html_e( 'Ce domaine sera bientôt enrichi. Contactez-nous pour organiser une session sur mesure ou recevoir le programme détaillé.', 'afsac' ); ?></p>
					<a class="afsac-button" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Demander une session', 'afsac' ); ?></a>
				</div>

			<?php endif; ?>
		</div>
	</section>

</main>

<?php
get_footer();
