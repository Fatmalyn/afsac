<?php
/**
 * Hero illustré PARTAGÉ — bande pleine largeur de 4 panneaux (campus, salle,
 * piste, réunion). Décor SVG posé en background-image CSS (décoratif, hors arbre
 * d'accessibilité). Réutilisé par le hub « Formations & Services » ET la page
 * « Qui sommes-nous » : une seule source de markup (DRY).
 *
 * Les libellés eyebrow superposés sont OPTIONNELS : la page À propos affiche le
 * hero sans texte overlay.
 *
 * ⚠️ PLUS UTILISÉ depuis le réalignement sur la charte OACI (blanc + bleu) : toutes
 * les pages internes emploient désormais le hero clair shared/guide-hero.php.
 * Conservé tel quel — bande sombre à 4 panneaux — au cas où le client voudrait
 * revenir à un hero imagé sur une page précise.
 *
 * @param array $args {
 *     Arguments passés via get_template_part( $slug, $name, $args ).
 *
 *     @type bool $with_labels Affiche les libellés superposés. Défaut true.
 * }
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Overlay éditorial optionnel (sur-titre + titre + chapô sur la bande). Si présent,
// les libellés de panneau sont masqués. Rétro-compatible : F&S / Qui-sommes-nous
// n'envoient pas ces arguments → bande + libellés inchangés.
$afsac_hero_eyebrow = isset( $args['eyebrow'] ) ? (string) $args['eyebrow'] : '';
$afsac_hero_title   = isset( $args['title'] ) ? (string) $args['title'] : '';
$afsac_hero_chapo   = isset( $args['chapo'] ) ? (string) $args['chapo'] : '';
$afsac_hero_overlay = ( '' !== $afsac_hero_eyebrow || '' !== $afsac_hero_title || '' !== $afsac_hero_chapo );

$afsac_with_labels = $afsac_hero_overlay
	? false
	: ( ! isset( $args['with_labels'] ) || (bool) $args['with_labels'] );

$afsac_hero_panels = array(
	array( 'slug' => 'campus',  'label' => __( 'Campus · Tunis', 'afsac' ) ),
	array( 'slug' => 'salle',   'label' => __( 'Salle de formation', 'afsac' ) ),
	array( 'slug' => 'piste',   'label' => __( 'Piste · TUN', 'afsac' ) ),
	array( 'slug' => 'reunion', 'label' => __( 'Réunion institutionnelle', 'afsac' ) ),
);
?>
<section class="afsac-fs-hero<?php echo $afsac_hero_overlay ? ' afsac-fs-hero--cover' : ''; ?>">
	<div class="afsac-fs-hero__band">
		<?php foreach ( $afsac_hero_panels as $afsac_panel ) : ?>
			<div class="afsac-fs-hero__panel afsac-fs-hero__panel--<?php echo esc_attr( $afsac_panel['slug'] ); ?>">
				<?php if ( $afsac_with_labels ) : ?>
					<span class="afsac-eyebrow afsac-fs-hero__label"><?php echo esc_html( $afsac_panel['label'] ); ?></span>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php if ( $afsac_hero_overlay ) : ?>
		<div class="afsac-fs-hero__overlay">
			<div class="afsac-container afsac-fs-hero__overlay-inner">
				<?php if ( '' !== $afsac_hero_eyebrow ) : ?>
					<span class="afsac-eyebrow afsac-fs-hero__eyebrow"><?php echo esc_html( $afsac_hero_eyebrow ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $afsac_hero_title ) : ?>
					<h1 class="afsac-fs-hero__title"><?php echo esc_html( $afsac_hero_title ); ?></h1>
				<?php endif; ?>
				<?php if ( '' !== $afsac_hero_chapo ) : ?>
					<p class="afsac-fs-hero__chapo"><?php echo esc_html( $afsac_hero_chapo ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</section>
