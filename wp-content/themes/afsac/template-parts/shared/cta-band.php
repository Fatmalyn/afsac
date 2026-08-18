<?php
/**
 * Bande CTA pleine largeur PARTAGÉE — sur-titre + titre + texte + boutons.
 *
 * Composant unique placé avant le footer (ou en fin de page). Variante navy
 * (défaut) ou accent. Réutilisé par toutes les pages.
 *
 * @param array $args {
 *     @type string  $eyebrow Sur-titre optionnel.
 *     @type string  $title   Titre.
 *     @type string  $text    Phrase optionnelle.
 *     @type array[] $buttons Liste de boutons : { @type string label, @type string url, @type bool outline }.
 *     @type string  $variant 'navy' (défaut) | 'accent'.
 *     @type bool    $reveal  Révélation au scroll. Défaut true.
 * }
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_eyebrow = isset( $args['eyebrow'] ) ? (string) $args['eyebrow'] : '';
$afsac_title   = isset( $args['title'] ) ? (string) $args['title'] : '';
$afsac_text    = isset( $args['text'] ) ? (string) $args['text'] : '';
$afsac_buttons = ( isset( $args['buttons'] ) && is_array( $args['buttons'] ) ) ? $args['buttons'] : array();
$afsac_variant = ( isset( $args['variant'] ) && 'accent' === $args['variant'] ) ? 'accent' : 'navy';
$afsac_reveal  = ! isset( $args['reveal'] ) || ! empty( $args['reveal'] );

if ( '' === $afsac_title ) {
	return;
}

$afsac_class = 'afsac-cta-band';
if ( 'accent' === $afsac_variant ) {
	$afsac_class .= ' afsac-cta-band--accent';
}
if ( $afsac_reveal ) {
	$afsac_class .= ' afsac-reveal';
}
?>
<section class="<?php echo esc_attr( $afsac_class ); ?>">
	<div class="afsac-container afsac-cta-band__inner">
		<?php if ( '' !== $afsac_eyebrow ) : ?>
			<span class="afsac-eyebrow"><?php echo esc_html( $afsac_eyebrow ); ?></span>
		<?php endif; ?>
		<h2 class="afsac-cta-band__title"><?php echo esc_html( $afsac_title ); ?></h2>
		<?php if ( '' !== $afsac_text ) : ?>
			<p class="afsac-cta-band__text"><?php echo esc_html( $afsac_text ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $afsac_buttons ) ) : ?>
			<div class="afsac-cta-band__actions">
				<?php foreach ( $afsac_buttons as $afsac_btn ) : ?>
					<?php
					if ( empty( $afsac_btn['label'] ) ) {
						continue;
					}
					$afsac_btn_class = 'afsac-button';
					if ( ! empty( $afsac_btn['outline'] ) ) {
						$afsac_btn_class .= ' afsac-button--outline';
					}
					?>
					<a class="<?php echo esc_attr( $afsac_btn_class ); ?>" href="<?php echo esc_url( isset( $afsac_btn['url'] ) ? $afsac_btn['url'] : '#' ); ?>"><?php echo esc_html( $afsac_btn['label'] ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
