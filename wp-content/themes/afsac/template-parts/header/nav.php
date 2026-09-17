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

		<?php
		/*
		 * L'`id` est posé ICI (et non en JS) : le bouton hamburger le référence en
		 * `aria-controls`, la relation doit donc exister dès le HTML servi, avant
		 * que main.js ne s'exécute.
		 *
		 * Sous 1150 px cette <nav> devient un tiroir latéral (cf. style.css). Le
		 * bouton de fermeture n'a de sens que dans ce tiroir — masqué au-dessus —
		 * mais il est rendu systématiquement : le voile qui couvre le hamburger
		 * (z-index 1040 > 1000) laissait sinon le tiroir sans aucune commande de
		 * fermeture visible sur téléphone.
		 */
		?>
		<nav id="afsac-primary-nav" class="afsac-primary-nav" aria-label="<?php esc_attr_e( 'Navigation principale', 'afsac' ); ?>">
			<button type="button" class="afsac-nav-close" data-afsac-nav-close aria-label="<?php esc_attr_e( 'Fermer le menu', 'afsac' ); ?>">
				<?php echo afsac_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
			</button>
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

			<?php
			/*
			 * Pied du tiroir — n'existe QUE sous le point de rupture (masqué en CSS
			 * au-dessus, cf. `.afsac-nav-foot`).
			 *
			 * Deux raisons. D'abord la barre utilitaire se vide sur téléphone : le
			 * libellé du téléphone et de l'e-mail est masqué sous 980 px et les
			 * liens rapides sous 620 px — il ne reste que deux pictogrammes de
			 * 15 px, sous le seuil tactile. Ensuite le tiroir n'occupait que sa
			 * moitié haute : sept entrées de menu puis 400 px de blanc.
			 *
			 * On y remet donc, à taille de doigt, ce que la barre utilitaire ne peut
			 * plus montrer : le choix de la langue et les deux moyens de contact.
			 * Rien de neuf n'est inventé — mêmes sources (Customizer via
			 * afsac_get_contact(), sélecteur Polylang) que la barre du haut.
			 */
			$afsac_nav_contact = afsac_get_contact();
			?>
			<div class="afsac-nav-foot">
				<span class="afsac-nav-foot__label"><?php esc_html_e( 'Langue', 'afsac' ); ?></span>
				<div class="afsac-nav-foot__lang">
					<?php
					afsac_theme_language_switcher(
						array(
							'show_flags'       => false,
							'display_names_as' => 'slug',
							'hide_if_empty'    => false,
						)
					);
					?>
				</div>

				<?php if ( $afsac_nav_contact['phone_display'] || $afsac_nav_contact['email'] ) : ?>
					<span class="afsac-nav-foot__label"><?php esc_html_e( 'Nous joindre', 'afsac' ); ?></span>
					<?php if ( $afsac_nav_contact['phone_display'] ) : ?>
						<a class="afsac-nav-foot__link" href="tel:<?php echo esc_attr( $afsac_nav_contact['phone'] ); ?>">
							<?php echo afsac_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
							<span><?php echo esc_html( $afsac_nav_contact['phone_display'] ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( $afsac_nav_contact['email'] ) : ?>
						<a class="afsac-nav-foot__link" href="mailto:<?php echo esc_attr( $afsac_nav_contact['email'] ); ?>">
							<?php echo afsac_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
							<span><?php echo esc_html( $afsac_nav_contact['email'] ); ?></span>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>
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
			<?php /* Les deux libellés voyagent en data-* : main.js bascule aria-label sans chaîne en dur (traduction assurée). */ ?>
			<button type="button" class="afsac-action afsac-menu-toggle" aria-expanded="false" aria-controls="afsac-primary-nav"
				aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'afsac' ); ?>"
				data-label-open="<?php esc_attr_e( 'Ouvrir le menu', 'afsac' ); ?>"
				data-label-close="<?php esc_attr_e( 'Fermer le menu', 'afsac' ); ?>">
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
