<?php
/**
 * Composant : carte / ligne de session (listes liées à une formation).
 *
 * Attend une variable $args['session_id'] OU utilise le post courant.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_session_id = isset( $args['session_id'] ) ? (int) $args['session_id'] : get_the_ID();

$afsac_debut  = function_exists( 'get_field' ) ? get_field( 'afsac_date_debut', $afsac_session_id ) : '';
$afsac_fin    = function_exists( 'get_field' ) ? get_field( 'afsac_date_fin', $afsac_session_id ) : '';
$afsac_lieu   = function_exists( 'get_field' ) ? get_field( 'afsac_lieu', $afsac_session_id ) : '';
$afsac_statut = function_exists( 'get_field' ) ? get_field( 'afsac_statut', $afsac_session_id ) : '';
?>
<article class="afsac-card afsac-card--session">
	<div class="afsac-card__body">
		<h3 class="afsac-card__title">
			<a href="<?php echo esc_url( get_permalink( $afsac_session_id ) ); ?>">
				<?php echo esc_html( get_the_title( $afsac_session_id ) ); ?>
			</a>
		</h3>

		<ul class="afsac-meta-list">
			<?php if ( $afsac_debut ) : ?>
				<li class="afsac-meta-list__item">
					<span class="afsac-meta-list__label"><?php esc_html_e( 'Du', 'afsac' ); ?></span>
					<span class="afsac-meta-list__value"><?php echo esc_html( afsac_format_date( $afsac_debut ) ); ?></span>
				</li>
			<?php endif; ?>

			<?php if ( $afsac_fin ) : ?>
				<li class="afsac-meta-list__item">
					<span class="afsac-meta-list__label"><?php esc_html_e( 'Au', 'afsac' ); ?></span>
					<span class="afsac-meta-list__value"><?php echo esc_html( afsac_format_date( $afsac_fin ) ); ?></span>
				</li>
			<?php endif; ?>

			<?php if ( $afsac_lieu ) : ?>
				<li class="afsac-meta-list__item">
					<span class="afsac-meta-list__label"><?php esc_html_e( 'Lieu', 'afsac' ); ?></span>
					<span class="afsac-meta-list__value"><?php echo esc_html( $afsac_lieu ); ?></span>
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
	</div>
</article>
