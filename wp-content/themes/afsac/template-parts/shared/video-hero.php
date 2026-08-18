<?php
/**
 * Hero vidéo PARTAGÉ — fond vidéo plein cadre + overlay dégradé navy, contenu
 * superposé (eyebrow, titre, lead, note, 2 CTA). Paramétrable, même esprit que
 * guide-hero.php (une seule source de markup, DRY).
 *
 * Deux mises en page via `layout` :
 *  - `overlay` : vidéo plein cadre EN FOND, texte par-dessus (scrim navy) ;
 *  - `panel`   : deux panneaux — texte d'un côté, vidéo ENTIÈRE de l'autre
 *                (object-fit: contain, aucun recadrage). À préférer quand la
 *                vidéo est une composition qui se suffit (accueil).
 *
 * `align` ne règle que l'alignement du texte : `center` ou `start`.
 *
 * La source vidéo n'est PAS posée en dur : elle est injectée en JS
 * (assets/js/hero-video.js) afin de NE PAS charger la vidéo sur mobile ni en
 * reduced-motion. Le poster (+ fallback CSS mobile) couvre ces cas.
 *
 * @param array $args {
 *     @type string $layout        'overlay' (défaut) ou 'panel'.
 *     @type string $align         'center' (défaut) ou 'start'.
 *     @type string $eyebrow       Intitulé superposé (optionnel).
 *     @type string $title         Titre H1 (Source Serif 4).
 *     @type string $lead          Paragraphe d'accroche, <strong>/<br> admis (optionnel).
 *     @type string $note          Paragraphe de clôture, <strong>/<br> admis (optionnel).
 *     @type array  $stats         Liste de ['value'=>string,'label'=>string] (optionnel).
 *     @type bool   $mirror        Retourne le plan horizontalement (défaut false).
 *     @type bool   $dim           Voile renforcé sur la vidéo (défaut false).
 *     @type string $poster        URL de l'image poster.
 *     @type string $video_src     URL de la vidéo (injectée en JS via data-src).
 *     @type bool   $scroll        Affiche l'indicateur de défilement (défaut false).
 *     @type array  $cta_primary   { @type string label, @type string url }.
 *     @type array  $cta_secondary { @type string label, @type string url }.
 * }
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_layout    = ( isset( $args['layout'] ) && 'panel' === $args['layout'] ) ? 'panel' : 'overlay';
$afsac_align     = ( isset( $args['align'] ) && 'start' === $args['align'] ) ? 'start' : 'center';
$afsac_eyebrow   = isset( $args['eyebrow'] ) ? (string) $args['eyebrow'] : '';
$afsac_title     = isset( $args['title'] ) ? (string) $args['title'] : '';
$afsac_lead      = isset( $args['lead'] ) ? (string) $args['lead'] : '';
$afsac_note      = isset( $args['note'] ) ? (string) $args['note'] : '';
$afsac_stats     = ( isset( $args['stats'] ) && is_array( $args['stats'] ) ) ? $args['stats'] : array();
$afsac_poster    = isset( $args['poster'] ) ? (string) $args['poster'] : get_theme_file_uri( 'assets/images/hero-poster.jpg' );
$afsac_video_src = isset( $args['video_src'] ) ? (string) $args['video_src'] : get_theme_file_uri( 'assets/video/hero.mp4' );
$afsac_scroll    = ! empty( $args['scroll'] );
// Miroir horizontal : utile quand le sujet filmé tombe du côté du texte.
$afsac_mirror    = ! empty( $args['mirror'] ) ? ' afsac-video-hero--mirror' : '';
// Voile renforcé : pour les bandeaux chargés ou clairs, qui doivent passer en
// ambiance derrière le texte. (Ne touche PAS à la hauteur : elle est commune à
// toutes les pages, cf. --afsac-hero-h.)
$afsac_dim       = ! empty( $args['dim'] ) ? ' afsac-video-hero--dim' : '';
$afsac_cta_1     = isset( $args['cta_primary'] ) && is_array( $args['cta_primary'] ) ? $args['cta_primary'] : array();
$afsac_cta_2     = isset( $args['cta_secondary'] ) && is_array( $args['cta_secondary'] ) ? $args['cta_secondary'] : array();

// Balises admises dans les paragraphes riches (lead / note).
$afsac_rich = array(
	'strong' => array(),
	'em'     => array(),
	'br'     => array(),
);
?>
<section class="afsac-video-hero afsac-video-hero--<?php echo esc_attr( $afsac_layout ); ?> afsac-video-hero--<?php echo esc_attr( $afsac_align ); ?><?php echo esc_attr( $afsac_mirror ); ?><?php echo esc_attr( $afsac_dim ); ?>">

	<div class="afsac-video-hero__stage">
		<video class="afsac-video-hero__media" muted loop playsinline preload="none"
			data-src="<?php echo esc_url( $afsac_video_src ); ?>"
			poster="<?php echo esc_url( $afsac_poster ); ?>"></video>
		<span class="afsac-video-hero__overlay" aria-hidden="true"></span>
	</div>

	<div class="afsac-container afsac-video-hero__content">
		<div class="afsac-video-hero__col afsac-stagger">
			<?php if ( '' !== $afsac_eyebrow ) : ?>
				<span class="afsac-eyebrow afsac-eyebrow--light afsac-reveal"><?php echo esc_html( $afsac_eyebrow ); ?></span>
			<?php endif; ?>

			<?php if ( '' !== $afsac_title ) : ?>
				<h1 class="afsac-video-hero__title afsac-reveal"><?php echo esc_html( $afsac_title ); ?></h1>
			<?php endif; ?>

			<?php if ( '' !== $afsac_lead ) : ?>
				<p class="afsac-video-hero__lead afsac-reveal"><?php echo wp_kses( $afsac_lead, $afsac_rich ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $afsac_note ) : ?>
				<p class="afsac-video-hero__note afsac-reveal"><?php echo wp_kses( $afsac_note, $afsac_rich ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $afsac_stats ) ) : ?>
				<div class="afsac-video-hero__stats afsac-reveal">
					<?php foreach ( $afsac_stats as $afsac_stat ) : ?>
						<div>
							<b><?php echo esc_html( (string) $afsac_stat['value'] ); ?></b>
							<span><?php echo esc_html( (string) $afsac_stat['label'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $afsac_cta_1 ) || ! empty( $afsac_cta_2 ) ) : ?>
				<div class="afsac-video-hero__actions afsac-reveal">
					<?php if ( ! empty( $afsac_cta_1['label'] ) ) : ?>
						<a class="afsac-button afsac-button--accent" href="<?php echo esc_url( $afsac_cta_1['url'] ); ?>">
							<?php echo esc_html( $afsac_cta_1['label'] ); ?>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $afsac_cta_2['label'] ) ) : ?>
						<a class="afsac-button afsac-button--ghost" href="<?php echo esc_url( $afsac_cta_2['url'] ); ?>">
							<?php echo esc_html( $afsac_cta_2['label'] ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $afsac_scroll ) : ?>
		<span class="afsac-video-hero__scroll" aria-hidden="true">
			<span class="afsac-video-hero__scroll-mouse"><span class="afsac-video-hero__scroll-wheel"></span></span>
		</span>
	<?php endif; ?>

</section>
