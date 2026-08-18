<?php
/**
 * Header — barre utilitaire (fond marine).
 *
 * Gauche : téléphone + email (Customizer via afsac_get_contact()).
 * Droite : menu utilitaire (afsac_topbar) + sélecteur de langue (drapeau + code).
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_contact = afsac_get_contact();
?>
<div class="afsac-topbar">
	<div class="afsac-container afsac-topbar__inner">

		<div class="afsac-topbar__contact">
			<?php if ( $afsac_contact['phone_display'] ) : ?>
				<a class="afsac-topbar__link" href="tel:<?php echo esc_attr( $afsac_contact['phone'] ); ?>">
					<?php echo afsac_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
					<span><?php echo esc_html( $afsac_contact['phone_display'] ); ?></span>
				</a>
			<?php endif; ?>
			<?php if ( $afsac_contact['email'] ) : ?>
				<a class="afsac-topbar__link" href="mailto:<?php echo esc_attr( $afsac_contact['email'] ); ?>">
					<?php echo afsac_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
					<span><?php echo esc_html( $afsac_contact['email'] ); ?></span>
				</a>
			<?php endif; ?>
		</div>

		<div class="afsac-topbar__aside">
			<div class="afsac-topbar__lang">
				<?php
				// Sélecteur Polylang : codes courts uniquement (FR · EN · AR), restylé
				// en CSS (séparateurs « · », langue active en orange OACI). Pas de
				// drapeaux : avec raw=1 Polylang renvoie l'URL brute du drapeau (et
				// non la balise <img>), qui s'affichait alors en texte. On force aussi
				// l'affichage des langues même sans traduction (hide_if_empty=false).
				afsac_theme_language_switcher(
					array(
						'show_flags'       => false,
						'display_names_as' => 'slug',
						'hide_if_empty'    => false,
					)
				);
				?>
			</div>

			<?php if ( has_nav_menu( 'afsac_topbar' ) ) : ?>
				<nav class="afsac-topbar__links" aria-label="<?php esc_attr_e( 'Liens rapides', 'afsac' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'afsac_topbar',
							'container'      => false,
							'menu_class'     => 'afsac-topbar__menu',
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				</nav>
			<?php endif; ?>
		</div>

	</div>
</div>
