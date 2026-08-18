<?php
/**
 * En-tête de section PARTAGÉ — eyebrow + titre serif (H2) + trait décoratif.
 * Composant générique paramétré, réutilisable par toutes les sections éditoriales.
 *
 * @param array $args {
 *     @type string $eyebrow Intitulé (eyebrow).
 *     @type string $title   Titre de section (rendu en <h2>).
 *     @type string $align   Alignement : 'left' (défaut) | 'center'.
 *     @type string $variant Thème : 'light' (défaut) | 'dark' (fonds sombres).
 *     @type string $id      Ancre optionnelle posée sur le conteneur.
 * }
 *
 * Le titre accepte un accent inline : <span class="afsac-section-header__title-accent">…</span>
 * (seul ce span est autorisé ; un titre en texte simple reste rendu à l'identique).
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_eyebrow = isset( $args['eyebrow'] ) ? (string) $args['eyebrow'] : '';
$afsac_title   = isset( $args['title'] ) ? (string) $args['title'] : '';

// Garde-fou : sans eyebrow ni titre, rien à rendre.
if ( '' === $afsac_eyebrow && '' === $afsac_title ) {
	return;
}

$afsac_classes = array( 'afsac-section-header' );
if ( isset( $args['align'] ) && 'center' === $args['align'] ) {
	$afsac_classes[] = 'afsac-section-header--center';
}
if ( isset( $args['variant'] ) && 'dark' === $args['variant'] ) {
	$afsac_classes[] = 'afsac-section-header--dark';
}
if ( ! empty( $args['reveal'] ) ) {
	$afsac_classes[] = 'afsac-reveal';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $afsac_classes ) ); ?>"<?php if ( ! empty( $args['id'] ) ) : ?> id="<?php echo esc_attr( $args['id'] ); ?>"<?php endif; ?>>
	<?php if ( '' !== $afsac_eyebrow ) : ?>
		<span class="afsac-section-header__eyebrow"><?php echo esc_html( $afsac_eyebrow ); ?></span>
	<?php endif; ?>
	<?php if ( '' !== $afsac_title ) : ?>
		<h2 class="afsac-section-header__title"><?php echo wp_kses( $afsac_title, array( 'span' => array( 'class' => array(), 'data-target' => array(), 'data-suffix' => array() ) ) ); ?></h2>
	<?php endif; ?>
	<span class="afsac-section-header__rule" aria-hidden="true"></span>
</div>
