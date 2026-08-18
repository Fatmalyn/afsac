<?php
/**
 * En-tête de section PARTAGÉ — sur-titre (orange) + titre serif + intro optionnelle.
 *
 * Composant unique réutilisé par toutes les pages (aucun en-tête bespoke). Le
 * titre est un <h2> par défaut (le <h1> est dans le hero) ; surcharger via `tag`.
 *
 * @param array $args {
 *     @type string $eyebrow Sur-titre (maj. via CSS).
 *     @type string $title   Titre de section.
 *     @type string $intro   Paragraphe d'intro optionnel.
 *     @type bool   $center   Centrer l'en-tête. Défaut false.
 *     @type string $tag      Balise du titre. Défaut 'h2'.
 * }
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_eyebrow = isset( $args['eyebrow'] ) ? (string) $args['eyebrow'] : '';
$afsac_title   = isset( $args['title'] ) ? (string) $args['title'] : '';
$afsac_intro   = isset( $args['intro'] ) ? (string) $args['intro'] : '';
$afsac_center  = ! empty( $args['center'] );
$afsac_tag     = isset( $args['tag'] ) && in_array( $args['tag'], array( 'h1', 'h2', 'h3' ), true ) ? $args['tag'] : 'h2';

if ( '' === $afsac_eyebrow && '' === $afsac_title ) {
	return;
}
?>
<div class="afsac-section-head<?php echo $afsac_center ? ' afsac-section-head--center' : ''; ?>">
	<?php if ( '' !== $afsac_eyebrow ) : ?>
		<span class="afsac-eyebrow"><?php echo esc_html( $afsac_eyebrow ); ?></span>
	<?php endif; ?>
	<?php if ( '' !== $afsac_title ) : ?>
		<<?php echo esc_html( $afsac_tag ); ?> class="afsac-section-head__title"><?php echo esc_html( $afsac_title ); ?></<?php echo esc_html( $afsac_tag ); ?>>
	<?php endif; ?>
	<?php if ( '' !== $afsac_intro ) : ?>
		<p class="afsac-section-head__intro"><?php echo esc_html( $afsac_intro ); ?></p>
	<?php endif; ?>
</div>
