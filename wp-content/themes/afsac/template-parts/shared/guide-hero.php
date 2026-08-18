<?php
/**
 * Hero « guidage » navy — champ d'étoiles animé (canvas) + orbite dorée.
 *
 * Repris de la maquette de refonte, dans la charte de la home (navy, sur-titre
 * OR, titre serif, stats, boutons). Utilisé par le hub « Formations & Services »
 * ET la page « Catalogue » (les autres pages gardent le hero illustré partagé).
 * Le titre est rendu en <h1> (un seul par page, cohérent SEO).
 *
 * Le champ d'étoiles est peint par assets/js/afsac-guide-hero.js (respecte
 * prefers-reduced-motion) ; sans JS, le hero reste lisible (dégradé + halo + texte).
 *
 * @param array $args {
 *     @type string $eyebrow  Sur-titre (OR).
 *     @type string $title    Titre (H1).
 *     @type string $chapo    Chapô.
 *     @type array  $stats    Liste de ['value'=>string,'label'=>string].
 *     @type array  $ctas     Liste de ['label'=>string,'url'=>string,'variant'=>'accent'|'ghost'].
 *     @type array  $portrait Portrait en médaillon ['src'=>url,'alt'=>string,'caption'=>string].
 *                            Utilisé par « Qui sommes-nous » : photo du DG mise en
 *                            valeur sur le filigrane du globe OACI (demande client).
 *     @type bool   $compact  Réduit le rembourrage vertical. Pour les pages-OUTILS
 *                            (calendrier) où le hero n'est qu'un en-tête : la barre
 *                            de filtres doit rester visible sans défiler.
 * }
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_eyebrow  = isset( $args['eyebrow'] ) ? (string) $args['eyebrow'] : '';
$afsac_title    = isset( $args['title'] ) ? (string) $args['title'] : '';
$afsac_chapo    = isset( $args['chapo'] ) ? (string) $args['chapo'] : '';
$afsac_stats    = ( isset( $args['stats'] ) && is_array( $args['stats'] ) ) ? $args['stats'] : array();
$afsac_ctas     = ( isset( $args['ctas'] ) && is_array( $args['ctas'] ) ) ? $args['ctas'] : array();
$afsac_portrait = ( isset( $args['portrait'] ) && ! empty( $args['portrait']['src'] ) ) ? $args['portrait'] : array();
$afsac_compact  = ! empty( $args['compact'] );
?>
<section class="afsac-guide-hero<?php echo $afsac_portrait ? ' afsac-guide-hero--portrait' : ''; ?><?php echo $afsac_compact ? ' afsac-guide-hero--compact' : ''; ?>">
	<canvas class="afsac-guide-hero__stars" aria-hidden="true"></canvas>
	<span class="afsac-guide-hero__glow" aria-hidden="true"></span>
	<div class="afsac-container<?php echo $afsac_portrait ? ' afsac-guide-hero__grid' : ''; ?>">
		<div class="afsac-guide-hero__inner">
			<?php if ( '' !== $afsac_eyebrow ) : ?>
				<span class="afsac-eyebrow afsac-guide-hero__eyebrow"><?php echo esc_html( $afsac_eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $afsac_title ) : ?>
				<h1 class="afsac-guide-hero__title"><?php echo esc_html( $afsac_title ); ?></h1>
			<?php endif; ?>
			<?php if ( '' !== $afsac_chapo ) : ?>
				<p class="afsac-guide-hero__chapo"><?php echo esc_html( $afsac_chapo ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $afsac_ctas ) ) : ?>
				<div class="afsac-guide-hero__actions">
					<?php
					foreach ( $afsac_ctas as $afsac_cta ) :
						if ( empty( $afsac_cta['label'] ) || empty( $afsac_cta['url'] ) ) {
							continue;
						}
						$afsac_ghost = ( isset( $afsac_cta['variant'] ) && 'ghost' === $afsac_cta['variant'] );
						$afsac_class = $afsac_ghost ? 'afsac-button--ghost' : 'afsac-button--accent';
						?>
						<a class="afsac-button <?php echo esc_attr( $afsac_class ); ?>" href="<?php echo esc_url( $afsac_cta['url'] ); ?>">
							<?php echo esc_html( $afsac_cta['label'] ); ?>
							<?php if ( ! $afsac_ghost ) : ?><span class="afsac-arrow" aria-hidden="true">&rarr;</span><?php endif; ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $afsac_stats ) ) : ?>
				<div class="afsac-guide-hero__stats">
					<?php
					foreach ( $afsac_stats as $afsac_stat ) :
						if ( ! isset( $afsac_stat['value'], $afsac_stat['label'] ) ) {
							continue;
						}
						?>
						<div>
							<b><?php echo esc_html( $afsac_stat['value'] ); ?></b>
							<span><?php echo esc_html( $afsac_stat['label'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $afsac_portrait ) : ?>
			<?php /* Médaillon : halo doré + filigrane du globe OACI + anneau en rotation. */ ?>
			<figure class="afsac-guide-hero__portrait">
				<span class="afsac-guide-hero__halo" aria-hidden="true"></span>
				<span class="afsac-guide-hero__oaci" aria-hidden="true">
					<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/logo-icao.png' ) ); ?>" alt="" loading="lazy" decoding="async">
				</span>
				<span class="afsac-guide-hero__ring" aria-hidden="true"></span>
				<span class="afsac-guide-hero__photo">
					<img src="<?php echo esc_url( $afsac_portrait['src'] ); ?>" alt="<?php echo esc_attr( isset( $afsac_portrait['alt'] ) ? $afsac_portrait['alt'] : '' ); ?>" loading="eager" decoding="async">
				</span>
				<?php if ( ! empty( $afsac_portrait['caption'] ) ) : ?>
					<figcaption class="afsac-guide-hero__cap"><?php echo esc_html( $afsac_portrait['caption'] ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endif; ?>
	</div>
</section>
