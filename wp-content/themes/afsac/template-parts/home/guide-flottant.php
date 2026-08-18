<?php
/**
 * Accueil — guide flottant (nudge animé) qui invite à découvrir les formations.
 *
 * Épingle en bas de page, apparaît après un défilement (classe .is-visible
 * posée par main.js), oscille doucement et pulse pour attirer l'œil. Fermable
 * (mémorisé le temps de la session). Animations coupées en reduced-motion.
 * Rendu sur l'accueil uniquement (cf. footer.php).
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_guide_url = function_exists( 'afsac_get_catalogue_url' ) ? afsac_get_catalogue_url() : home_url( '/' );
?>
<div class="afsac-guide" data-afsac-guide>
	<a class="afsac-guide__link" href="<?php echo esc_url( $afsac_guide_url ); ?>" aria-label="<?php esc_attr_e( 'Découvrir nos formations', 'afsac' ); ?>">
		<span class="afsac-guide__icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1.2 2.7 2.5 6 2.5s6-1.3 6-2.5v-5"/></svg>
		</span>
		<span class="afsac-guide__text">
			<span class="afsac-guide__label"><?php esc_html_e( 'Trouvez votre formation', 'afsac' ); ?></span>
			<span class="afsac-guide__sub"><?php esc_html_e( 'Explorer le catalogue OACI', 'afsac' ); ?></span>
		</span>
	</a>
	<button type="button" class="afsac-guide__close" aria-label="<?php esc_attr_e( 'Fermer', 'afsac' ); ?>">&times;</button>
</div>
