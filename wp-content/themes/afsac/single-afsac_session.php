<?php
/**
 * Fiche session : détails planifiés + renvoi vers la formation liée.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$afsac_id = get_the_ID();

	$afsac_has_acf = function_exists( 'get_field' );
	$afsac_debut   = $afsac_has_acf ? get_field( 'afsac_date_debut', $afsac_id ) : '';
	$afsac_fin     = $afsac_has_acf ? get_field( 'afsac_date_fin', $afsac_id ) : '';
	$afsac_lieu    = $afsac_has_acf ? get_field( 'afsac_lieu', $afsac_id ) : '';
	$afsac_places  = $afsac_has_acf ? get_field( 'afsac_places', $afsac_id ) : '';
	$afsac_statut  = $afsac_has_acf ? get_field( 'afsac_statut', $afsac_id ) : '';

	// Formation liée, résolue dans la langue courante (le méta stocke l'ID
	// canonique FR ; sans résolution, une fiche EN afficherait le titre FR).
	$afsac_formation = function_exists( 'afsac_get_session_formation_localized' )
		? afsac_get_session_formation_localized( $afsac_id )
		: ( function_exists( 'afsac_get_session_formation' ) ? afsac_get_session_formation( $afsac_id ) : null );
	?>

	<article <?php post_class( 'afsac-container afsac-session' ); ?>>

		<header class="afsac-page-header">
			<h1 class="afsac-entry-title"><?php the_title(); ?></h1>

			<?php if ( $afsac_formation instanceof WP_Post ) : ?>
				<p class="afsac-session__formation">
					<?php esc_html_e( 'Formation :', 'afsac' ); ?>
					<a href="<?php echo esc_url( get_permalink( $afsac_formation ) ); ?>">
						<?php echo esc_html( get_the_title( $afsac_formation ) ); ?>
					</a>
				</p>
			<?php endif; ?>
		</header>

		<ul class="afsac-meta-list">
			<?php if ( $afsac_debut ) : ?>
				<li class="afsac-meta-list__item">
					<span class="afsac-meta-list__label"><?php esc_html_e( 'Date de début', 'afsac' ); ?></span>
					<span class="afsac-meta-list__value"><?php echo esc_html( afsac_format_date( $afsac_debut ) ); ?></span>
				</li>
			<?php endif; ?>

			<?php if ( $afsac_fin ) : ?>
				<li class="afsac-meta-list__item">
					<span class="afsac-meta-list__label"><?php esc_html_e( 'Date de fin', 'afsac' ); ?></span>
					<span class="afsac-meta-list__value"><?php echo esc_html( afsac_format_date( $afsac_fin ) ); ?></span>
				</li>
			<?php endif; ?>

			<?php if ( $afsac_lieu ) : ?>
				<li class="afsac-meta-list__item">
					<span class="afsac-meta-list__label"><?php esc_html_e( 'Lieu', 'afsac' ); ?></span>
					<span class="afsac-meta-list__value"><?php echo esc_html( $afsac_lieu ); ?></span>
				</li>
			<?php endif; ?>

			<?php
			// Langue de la session : terme de la taxonomie afsac_langue.
			$afsac_langue_html = get_the_term_list( $afsac_id, 'afsac_langue', '', ', ' );
			if ( $afsac_langue_html && ! is_wp_error( $afsac_langue_html ) ) :
				?>
				<li class="afsac-meta-list__item">
					<span class="afsac-meta-list__label"><?php esc_html_e( 'Langue', 'afsac' ); ?></span>
					<span class="afsac-meta-list__value"><?php echo wp_kses_post( $afsac_langue_html ); ?></span>
				</li>
			<?php endif; ?>

			<?php if ( '' !== $afsac_places && null !== $afsac_places ) : ?>
				<li class="afsac-meta-list__item">
					<span class="afsac-meta-list__label"><?php esc_html_e( 'Places disponibles', 'afsac' ); ?></span>
					<span class="afsac-meta-list__value"><?php echo esc_html( (string) (int) $afsac_places ); ?></span>
				</li>
			<?php endif; ?>

			<?php if ( $afsac_statut ) : ?>
				<li class="afsac-meta-list__item">
					<span class="afsac-meta-list__label"><?php esc_html_e( 'Statut', 'afsac' ); ?></span>
					<span class="afsac-meta-list__value">
						<span class="afsac-badge afsac-badge--<?php echo esc_attr( $afsac_statut ); ?>">
							<?php echo esc_html( afsac_session_statut_label( $afsac_statut ) ); ?>
						</span>
					</span>
				</li>
			<?php endif; ?>
		</ul>

		<?php if ( '' !== trim( get_the_content() ) ) : ?>
			<section class="afsac-entry-content"><?php the_content(); ?></section>
		<?php endif; ?>

	</article>

	<?php
endwhile;

get_footer();
