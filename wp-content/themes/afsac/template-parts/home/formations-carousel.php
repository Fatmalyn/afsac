<?php
/**
 * Section accueil : « Consulter nos formations à venir ».
 *
 * Grille de QUATRE cartes (2×2) au format du catalogue OACI, qui change
 * automatiquement : 12 cours découpés en 3 pages, rotation toutes les 6 s, avec
 * points de pagination et flèches (demande client, réunion 04/08/2026).
 *
 * MÉLANGE GARANTI des deux familles : deux requêtes (AVSEC + « non-AVSEC »
 * = TRAINAIR PLUS), puis entrelacement — on voit toujours les deux types quelle
 * que soit la date.
 *
 * Le rendu d'une carte vit dans template-parts/home/course-card-icao.php.
 * Le masquage des pages inactives est scopé sous `.afsac-js` : sans JavaScript
 * les 12 cartes s'affichent en grille continue, rien n'est jamais inaccessible.
 *
 * Source : CPT afsac_formation.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_cat_link = function_exists( 'afsac_get_catalogue_url' ) ? afsac_get_catalogue_url() : get_post_type_archive_link( 'afsac_formation' );
if ( ! $afsac_cat_link ) {
	$afsac_cat_link = home_url( '/' );
}

// Nombre de cartes par page et total visé (3 pages de 4).
$afsac_per_page = 4;
$afsac_want     = 12;

// Deux requêtes (IDs seulement) pour garantir un mélange AVSEC / TRAINAIR PLUS.
$afsac_q_base = array(
	'post_type'           => 'afsac_formation',
	'post_status'         => 'publish',
	'orderby'             => 'date',
	'order'               => 'DESC',
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
	'fields'              => 'ids',
	'posts_per_page'      => $afsac_want,
);
$afsac_ids_avsec = ( new WP_Query( array_merge( $afsac_q_base, array(
	'tax_query' => array( array( 'taxonomy' => 'afsac_famille', 'field' => 'name', 'terms' => 'AVSEC', 'operator' => 'IN' ) ),
) ) ) )->posts;
$afsac_ids_tp = ( new WP_Query( array_merge( $afsac_q_base, array(
	'tax_query' => array( array( 'taxonomy' => 'afsac_famille', 'field' => 'name', 'terms' => 'AVSEC', 'operator' => 'NOT IN' ) ),
) ) ) )->posts;

if ( ! empty( $afsac_ids_avsec ) || ! empty( $afsac_ids_tp ) ) :

	/*
	 * Le constructeur de carte a été SORTI d'ici : afsac_build_course_card()
	 * (functions.php) sert désormais aussi les « prochaines sessions AVSEC » du
	 * catalogue. Dupliquer cette trentaine de lignes garantissait qu'un jour les
	 * deux copies divergent.
	 */
	$afsac_cards_avsec = array_map( 'afsac_build_course_card', $afsac_ids_avsec );
	$afsac_cards_tp    = array_map( 'afsac_build_course_card', $afsac_ids_tp );

	// Sélection équilibrée (~moitié/moitié, puis on complète avec la famille la plus fournie).
	$afsac_take_a = min( count( $afsac_cards_avsec ), (int) ceil( $afsac_want / 2 ) );
	$afsac_take_t = min( count( $afsac_cards_tp ), $afsac_want - $afsac_take_a );
	$afsac_take_a = min( count( $afsac_cards_avsec ), $afsac_want - $afsac_take_t );
	$afsac_sel_a  = array_slice( $afsac_cards_avsec, 0, $afsac_take_a );
	$afsac_sel_t  = array_slice( $afsac_cards_tp, 0, $afsac_take_t );

	// Entrelacement AVSEC / TRAINAIR PLUS pour un mélange visible sur chaque page.
	$afsac_cards = array();
	$afsac_maxn  = max( count( $afsac_sel_a ), count( $afsac_sel_t ) );
	for ( $afsac_n = 0; $afsac_n < $afsac_maxn; $afsac_n++ ) {
		if ( isset( $afsac_sel_a[ $afsac_n ] ) ) {
			$afsac_cards[] = $afsac_sel_a[ $afsac_n ];
		}
		if ( isset( $afsac_sel_t[ $afsac_n ] ) ) {
			$afsac_cards[] = $afsac_sel_t[ $afsac_n ];
		}
	}

	/*
	 * On ne garde que des pages COMPLÈTES quand c'est possible : une dernière page
	 * à une seule carte déséquilibrerait la grille 2×2 à chaque rotation.
	 */
	if ( count( $afsac_cards ) >= $afsac_per_page ) {
		$afsac_cards = array_slice( $afsac_cards, 0, intdiv( count( $afsac_cards ), $afsac_per_page ) * $afsac_per_page );
	}
	$afsac_pages = array_chunk( $afsac_cards, $afsac_per_page );
	$afsac_total = count( $afsac_pages );
	$afsac_uid   = wp_unique_id( 'afsac-fgrid-' );
	?>
	<section class="afsac-section afsac-formations-carousel">
		<div class="afsac-container">
			<div class="afsac-formations-carousel__head afsac-reveal">
				<div class="afsac-formations-carousel__intro">
					<span class="afsac-eyebrow"><?php esc_html_e( 'Formations à venir', 'afsac' ); ?></span>
					<h2 class="afsac-formations-carousel__title"><?php esc_html_e( 'Consulter nos formations à venir', 'afsac' ); ?></h2>
				</div>
				<a class="afsac-link" href="<?php echo esc_url( $afsac_cat_link ); ?>"><?php esc_html_e( 'Voir tout le catalogue', 'afsac' ); ?></a>
			</div>

			<?php /* .afsac-reveal sur le CONTENEUR uniquement : une carte de page masquée ne serait jamais observée. */ ?>
			<div class="afsac-fgrid afsac-reveal afsac-reveal--fade"
				data-afsac-fgrid
				role="group"
				aria-roledescription="<?php esc_attr_e( 'carrousel', 'afsac' ); ?>"
				aria-label="<?php esc_attr_e( 'Carrousel de formations', 'afsac' ); ?>">

				<div class="afsac-fgrid__viewport">
					<?php foreach ( $afsac_pages as $afsac_p => $afsac_page ) : ?>
						<div class="afsac-fgrid__page<?php echo 0 === $afsac_p ? ' is-active' : ''; ?>"
							id="<?php echo esc_attr( $afsac_uid . '-p' . $afsac_p ); ?>"
							role="tabpanel"
							tabindex="0"
							aria-labelledby="<?php echo esc_attr( $afsac_uid . '-t' . $afsac_p ); ?>">
							<div class="afsac-fgrid__grid">
								<?php
								foreach ( $afsac_page as $afsac_card ) {
									// Pages masquées en `eager` : sinon leur vignette ne se
									// télécharge qu'à la rotation (carré vide au 1er passage).
									$afsac_card['loading'] = ( 0 === $afsac_p ) ? 'lazy' : 'eager';
									get_template_part( 'template-parts/home/course-card-icao', null, $afsac_card );
								}
								?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<?php if ( $afsac_total > 1 ) : ?>
					<div class="afsac-fgrid__controls">
						<button type="button" class="afsac-fgrid__arrow afsac-fgrid__arrow--prev" data-afsac-fgrid-prev aria-label="<?php esc_attr_e( 'Formations précédentes', 'afsac' ); ?>">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7"/></svg>
						</button>

						<div class="afsac-fgrid__dots" role="tablist" aria-label="<?php esc_attr_e( 'Pages du carrousel', 'afsac' ); ?>">
							<?php foreach ( $afsac_pages as $afsac_p => $afsac_unused ) : ?>
								<button type="button"
									role="tab"
									id="<?php echo esc_attr( $afsac_uid . '-t' . $afsac_p ); ?>"
									aria-controls="<?php echo esc_attr( $afsac_uid . '-p' . $afsac_p ); ?>"
									aria-selected="<?php echo 0 === $afsac_p ? 'true' : 'false'; ?>"
									tabindex="<?php echo 0 === $afsac_p ? '0' : '-1'; ?>"
									class="afsac-fgrid__dot<?php echo 0 === $afsac_p ? ' is-active' : ''; ?>"
									data-index="<?php echo (int) $afsac_p; ?>">
									<span class="screen-reader-text">
										<?php
										/* translators: %d: numéro de page du carrousel. */
										echo esc_html( sprintf( __( 'Page %d', 'afsac' ), $afsac_p + 1 ) );
										?>
									</span>
								</button>
							<?php endforeach; ?>
						</div>

						<button type="button" class="afsac-fgrid__arrow afsac-fgrid__arrow--next" data-afsac-fgrid-next aria-label="<?php esc_attr_e( 'Formations suivantes', 'afsac' ); ?>">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 5l7 7-7 7"/></svg>
						</button>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
endif;
