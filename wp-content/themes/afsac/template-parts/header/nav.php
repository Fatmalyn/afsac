<?php
/**
 * Header — barre principale (fond blanc).
 *
 * Logo AFSAC fourni par le client (assets/images/logo-afsac.png) : avion +
 * « AFSAC » + bandeau « ICAO ASTC Tunisia ». Identique dans toutes les langues.
 * Il remplace depuis le 17/08/2026 le verrou OACI qui portait l'intitulé
 * complet du Centre (celui-ci reste en pied de page, en blanc). Pas de
 * tagline/divider séparés : le logo se suffit.
 * Navigation principale (afsac_primary) ; actions recherche + compte ;
 * panneau de recherche déroulant.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="afsac-mainbar">
	<div class="afsac-container afsac-mainbar__inner">

		<div class="afsac-branding">
			<div class="afsac-branding__lockup">
				<?php /* Logo AFSAC — même fichier dans toutes les langues. */ ?>
				<?php $afsac_logo = afsac_logo_centre(); ?>
				<a class="afsac-logo afsac-logo--img" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					<img
						class="afsac-logo__oaci"
						src="<?php echo esc_url( $afsac_logo['url'] ); ?>"
						width="<?php echo esc_attr( (string) $afsac_logo['width'] ); ?>"
						height="<?php echo esc_attr( (string) $afsac_logo['height'] ); ?>"
						alt="<?php esc_attr_e( 'Centre Régional de Formation à la Sûreté de l’Aviation de l’OACI — Tunis, Tunisie', 'afsac' ); ?>"
					>
				</a>
			</div>
		</div>

		<nav class="afsac-primary-nav" aria-label="<?php esc_attr_e( 'Navigation principale', 'afsac' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'afsac_primary',
					'container'      => false,
					'menu_class'     => 'afsac-menu',
					'depth'          => 2,
					'fallback_cb'    => 'afsac_primary_menu_fallback',
				)
			);
			?>
		</nav>

		<div class="afsac-mainbar__actions">
			<button type="button" class="afsac-action afsac-search-toggle" aria-expanded="false" aria-controls="afsac-search-panel" aria-label="<?php esc_attr_e( 'Rechercher', 'afsac' ); ?>">
				<?php echo afsac_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
			</button>
			<?php
			$afsac_participant_url = function_exists( 'afsac_localized_page_url' ) ? afsac_localized_page_url( 'espace-participant' ) : '';
			if ( '' === $afsac_participant_url ) {
				$afsac_participant_url = wp_login_url();
			}
			?>
			<a class="afsac-action" href="<?php echo esc_url( $afsac_participant_url ); ?>" aria-label="<?php esc_attr_e( 'Espace participant', 'afsac' ); ?>">
				<?php echo afsac_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
			</a>
			<button type="button" class="afsac-action afsac-menu-toggle" aria-expanded="false" aria-controls="afsac-primary-nav" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'afsac' ); ?>">
				<?php echo afsac_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
			</button>
		</div>

	</div>

	<?php /* Panneau de recherche déroulant (piloté par assets/js/main.js). */ ?>
	<div id="afsac-search-panel" class="afsac-search-panel" hidden>
		<div class="afsac-container">
			<?php get_search_form(); ?>
		</div>
	</div>
</div>
