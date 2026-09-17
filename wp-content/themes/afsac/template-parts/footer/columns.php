<?php
/**
 * Footer — grille unique : colonne de marque (logo + réseaux) puis
 * Liens utiles / Contact / Adresse. Fond marine (--afsac-navy), identique aux
 * autres sections sombres du site (hero stats, « pourquoi »).
 *
 * Le logo est la version BLANCHE du verrou OACI (WebP à canal alpha) : posée
 * directement sur le bleu, sans pastille claire (demande client). Depuis le
 * 08/09/2026 (demande client) : PLUS de signature sous le logo, logo agrandi,
 * et la colonne « S'abonner » (texte + bouton « Nous écrire ») est SUPPRIMÉE.
 * Ne pas les remettre.
 *
 * La colonne « Liens utiles » est un MIROIR du menu principal (afsac_primary) :
 * le client veut exactement les mêmes entrées en haut et en bas. Polylang sert
 * automatiquement le menu de la langue courante. Coordonnées et réseaux via le
 * Customizer.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_contact = afsac_get_contact();
$afsac_emails  = $afsac_contact['emails'];
$afsac_email_1 = array_shift( $afsac_emails ); // Email principal (contact).
$afsac_socials = afsac_get_social_links();

// Correspondance clé réseau -> nom d'icône du thème (afsac_icon()).
$afsac_social_icons = array(
	'linkedin' => 'linkedin',
	'x'        => 'twitter',
	'facebook' => 'facebook',
	'youtube'  => 'youtube',
);

?>
<div class="afsac-container afsac-footer__grid">

	<?php /* Colonne marque : logo blanc + réseaux. */ ?>
	<div class="afsac-footer__col afsac-footer__brand">
		<?php
		/*
		 * Verrou BLANC de la langue courante (même helper que l'en-tête). Le lien
		 * porte le ratio du fichier (variables CSS) : c'est LUI qui est dimensionné
		 * (76 px de haut, réduit sur mobile sans déformation), l'image le remplit.
		 * Ainsi la place est réservée avant même le chargement de l'image (pas de
		 * saut de mise en page, logo différent en FR et en EN).
		 */
		$afsac_flogo = afsac_logo_centre( true );
		?>
		<a class="afsac-footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" style="--afsac-logo-w:<?php echo (int) $afsac_flogo['width']; ?>;--afsac-logo-h:<?php echo (int) $afsac_flogo['height']; ?>">
			<img
				src="<?php echo esc_url( $afsac_flogo['url'] ); ?>"
				alt="<?php esc_attr_e( 'Centre Régional de Formation à la Sûreté de l’Aviation de l’OACI — Tunis, Tunisie', 'afsac' ); ?>"
				width="<?php echo esc_attr( (string) $afsac_flogo['width'] ); ?>"
				height="<?php echo esc_attr( (string) $afsac_flogo['height'] ); ?>"
				loading="lazy" decoding="async"
			>
		</a>
		<?php if ( ! empty( $afsac_socials ) ) : ?>
			<ul class="afsac-social__list" aria-label="<?php esc_attr_e( 'Suivez-nous', 'afsac' ); ?>">
				<?php foreach ( $afsac_socials as $afsac_social ) : ?>
					<?php $afsac_icon_name = isset( $afsac_social_icons[ $afsac_social['key'] ] ) ? $afsac_social_icons[ $afsac_social['key'] ] : ''; ?>
					<li>
						<a href="<?php echo esc_url( $afsac_social['url'] ); ?>" aria-label="<?php echo esc_attr( $afsac_social['label'] ); ?>" rel="noopener noreferrer" target="_blank">
							<?php echo afsac_icon( $afsac_icon_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>

	<?php
	/*
	 * Colonne 1 : liens utiles = MIROIR EXACT du menu principal (afsac_primary).
	 * Polylang résout seul l'emplacement vers le menu de la langue courante.
	 * depth=1 : le menu du haut a des sous-entrées, une colonne reste à plat.
	 */
	?>
	<div class="afsac-footer__col">
		<h2 class="afsac-footer__heading" id="afsac-footer-links"><?php esc_html_e( 'Liens utiles', 'afsac' ); ?></h2>
		<nav class="afsac-footer__nav" aria-labelledby="afsac-footer-links">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'afsac_primary',
					'container'      => false,
					'menu_class'     => 'afsac-footer__menu',
					'depth'          => 1,
					'fallback_cb'    => 'afsac_primary_menu_fallback',
				)
			);
			?>
		</nav>
	</div>

	<?php /* Colonne 2 : contact (téléphone + emails du Customizer). */ ?>
	<div class="afsac-footer__col">
		<h2 class="afsac-footer__heading"><?php esc_html_e( 'Contact', 'afsac' ); ?></h2>
		<ul class="afsac-footer__contact">
			<li class="afsac-footer__contact-row">
				<?php if ( $afsac_contact['phone_display'] ) : ?>
					<span class="afsac-footer__contact-item">
						<?php echo afsac_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
						<a class="afsac-footer__contact-phone" href="tel:<?php echo esc_attr( $afsac_contact['phone'] ); ?>"><?php echo esc_html( $afsac_contact['phone_display'] ); ?></a>
					</span>
				<?php endif; ?>
				<?php /* 2ᵉ numéro : footer uniquement (barre du haut n'en montre qu'un). */ ?>
				<?php if ( ! empty( $afsac_contact['phone_2_display'] ) ) : ?>
					<span class="afsac-footer__contact-item">
						<?php echo afsac_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
						<a class="afsac-footer__contact-phone" href="tel:<?php echo esc_attr( $afsac_contact['phone_2'] ); ?>"><?php echo esc_html( $afsac_contact['phone_2_display'] ); ?></a>
					</span>
				<?php endif; ?>
				<?php if ( $afsac_email_1 ) : ?>
					<span class="afsac-footer__contact-item">
						<?php echo afsac_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
						<a href="mailto:<?php echo esc_attr( $afsac_email_1 ); ?>"><?php echo esc_html( $afsac_email_1 ); ?></a>
					</span>
				<?php endif; ?>
			</li>
			<?php foreach ( $afsac_emails as $afsac_email ) : ?>
				<li class="afsac-footer__contact-item">
					<?php echo afsac_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
					<a href="mailto:<?php echo esc_attr( $afsac_email ); ?>"><?php echo esc_html( $afsac_email ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php /* Colonne 3 : adresse (Customizer). */ ?>
	<div class="afsac-footer__col">
		<h2 class="afsac-footer__heading"><?php esc_html_e( 'Adresse', 'afsac' ); ?></h2>
		<address class="afsac-footer__address">
			<?php echo nl2br( esc_html( $afsac_contact['address'] ) ); ?>
		</address>
	</div>

</div>
