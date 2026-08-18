<?php
/**
 * Bandeau de statistiques PARTAGÉ — bande navy pleine largeur, N colonnes
 * (number + label, séparateurs verticaux). Composant générique paramétré :
 * réutilisé par « Qui sommes-nous » et « Références & Témoignages ».
 *
 * @param array $args {
 *     @type array[] $stats Liste d'items. Chaque item :
 *         @type string number Valeur affichée (ex. « 148 », « 96 % »).
 *         @type string label  Libellé (casse normale ; MAJUSCULES via CSS).
 * }
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_stats = ( isset( $args['stats'] ) && is_array( $args['stats'] ) ) ? $args['stats'] : array();

// Garde-fou : aucun item → on ne rend rien.
if ( empty( $afsac_stats ) ) {
	return;
}

// Révélation au scroll optionnelle (opt-in via l'arg reveal).
$afsac_band_class = 'afsac-stats-band';
if ( ! empty( $args['reveal'] ) ) {
	$afsac_band_class .= ' afsac-reveal';
}

// Compteur animé optionnel (opt-in via l'arg count) sur la partie numérique.
$afsac_count = ! empty( $args['count'] );
?>
<section class="<?php echo esc_attr( $afsac_band_class ); ?>">
	<div class="afsac-container">
		<div class="afsac-stats-band__inner" style="--afsac-stats-cols: <?php echo absint( count( $afsac_stats ) ); ?>;">
			<?php foreach ( $afsac_stats as $afsac_stat ) : ?>
				<div class="afsac-stats-band__item">
					<span class="afsac-stats-band__number"><?php echo ( $afsac_count && empty( $afsac_stat['nocount'] ) ) ? afsac_count_markup( $afsac_stat['number'] ) : esc_html( $afsac_stat['number'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- afsac_count_markup() renvoie du HTML déjà échappé. ?></span>
					<span class="afsac-stats-band__label"><?php echo esc_html( $afsac_stat['label'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
