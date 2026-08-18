<?php
/**
 * Bandeau CTA léger (navy) — REMPLACE l'ancienne bande PDF pleine largeur.
 *
 * La zone « Documentation / Télécharger le catalogue » vit désormais uniquement
 * dans le pied de page (présent sur toutes les pages) : ce bandeau se recentre
 * sur l'action (contact) tout en conservant l'accès PDF s'il est renseigné.
 * Réutilisable par F&S et Catalogue via des libellés surchargeables.
 *
 * @param array $args {
 *     @type string $title Titre du bandeau (optionnel).
 *     @type string $text  Sous-texte (optionnel).
 * }
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_title = ( isset( $args['title'] ) && '' !== $args['title'] )
	? (string) $args['title']
	: __( 'Prêt à former vos équipes ?', 'afsac' );
$afsac_text  = ( isset( $args['text'] ) && '' !== $args['text'] )
	? (string) $args['text']
	: __( 'Parlez de votre besoin à un conseiller, ou téléchargez le catalogue complet 2026.', 'afsac' );

$afsac_contact_url = function_exists( 'afsac_get_contact_url' ) ? afsac_get_contact_url() : home_url( '/' );

$afsac_pdf     = function_exists( 'afsac_get_hub_field' ) ? afsac_get_hub_field( 'afsac_catalogue_pdf' ) : '';
$afsac_pdf_url = $afsac_pdf ? ( is_array( $afsac_pdf ) ? $afsac_pdf['url'] : $afsac_pdf ) : '';
?>
<section class="afsac-cta-strip">
	<span class="afsac-cta-strip__glow" aria-hidden="true"></span>
	<div class="afsac-container">
		<div class="afsac-cta-strip__body">
			<h2 class="afsac-cta-strip__title"><?php echo esc_html( $afsac_title ); ?></h2>
			<p class="afsac-cta-strip__text"><?php echo esc_html( $afsac_text ); ?></p>
		</div>
		<div class="afsac-cta-strip__actions">
			<a class="afsac-button afsac-button--accent" href="<?php echo esc_url( $afsac_contact_url ); ?>">
				<?php esc_html_e( 'Nous contacter', 'afsac' ); ?>
				<span class="afsac-arrow" aria-hidden="true">&rarr;</span>
			</a>
			<?php if ( '' !== $afsac_pdf_url ) : ?>
				<a class="afsac-button afsac-button--ghost" href="<?php echo esc_url( $afsac_pdf_url ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'Télécharger le PDF', 'afsac' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
