<?php
/**
 * Section PARTAGÉE : « Shorts » — vidéos verticales de la chaîne YouTube AFSAC.
 *
 * Rail horizontal de vignettes 9/16 (scroll-snap). AUCUNE iframe n'est chargée
 * au rendu : seule la vignette de YouTube est servie, et le lecteur n'est
 * injecté qu'au clic, dans une lightbox (assets/js/afsac-shorts.js). C'est à la
 * fois une question de poids de page et de vie privée — sans clic, le visiteur
 * ne dialogue jamais avec YouTube.
 *
 * Sans JavaScript, chaque carte reste un LIEN vers le short sur YouTube et le
 * rail se fait défiler au doigt / à la molette : la section n'est jamais un
 * cul-de-sac (mêmes règles que la playlist des témoignages). Les flèches, elles,
 * n'ont de sens qu'avec JS : elles sont donc gatées en CSS sous `.afsac-js`.
 *
 * Source des vidéos : afsac_shorts_items() (Customizer → « Vidéos & Shorts »,
 * avec repli sur les shorts de la chaîne).
 *
 * @param array $args {
 *     @type string $eyebrow Sur-titre (défaut : « L’AFSAC en vidéo »).
 *     @type string $title   Titre de section.
 *     @type string $lead    Chapô.
 *     @type string $id      Ancre du conteneur (défaut : « shorts »).
 * }
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_shorts = function_exists( 'afsac_shorts_items' ) ? afsac_shorts_items() : array();
if ( empty( $afsac_shorts ) ) {
	return;
}

// Le script est enregistré dans functions.php ; on ne l'enfile que si la section
// est réellement rendue (elle sort plus haut quand aucune vidéo n'est réglée).
wp_enqueue_script( 'afsac-shorts' );

$afsac_eyebrow = isset( $args['eyebrow'] ) ? (string) $args['eyebrow'] : __( 'L’AFSAC en vidéo', 'afsac' );
$afsac_title   = isset( $args['title'] ) ? (string) $args['title'] : __( 'Nos shorts', 'afsac' );
$afsac_lead    = isset( $args['lead'] ) ? (string) $args['lead'] : __( 'Formats courts tournés au centre de Tunis : coulisses des sessions, paroles d’instructeurs et repères de sûreté aérienne, en une minute.', 'afsac' );
$afsac_anchor  = isset( $args['id'] ) ? (string) $args['id'] : 'shorts';
$afsac_channel = function_exists( 'afsac_youtube_channel_url' ) ? afsac_youtube_channel_url() : '';
?>
<section class="afsac-shorts" id="<?php echo esc_attr( $afsac_anchor ); ?>" data-afsac-shorts>
	<div class="afsac-container">

		<div class="afsac-shorts__head">
			<div class="afsac-shorts__head-text">
				<?php
				get_template_part(
					'template-parts/shared/section-header',
					null,
					array(
						'eyebrow' => $afsac_eyebrow,
						'title'   => $afsac_title,
						'reveal'  => true,
					)
				);
				?>
				<?php if ( '' !== $afsac_lead ) : ?>
					<p class="afsac-shorts__lead afsac-reveal"><?php echo esc_html( $afsac_lead ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( '' !== $afsac_channel ) : ?>
				<a class="afsac-shorts__channel afsac-reveal" href="<?php echo esc_url( $afsac_channel ); ?>" target="_blank" rel="noopener noreferrer">
					<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M23 12s0-3.2-.4-4.7a2.5 2.5 0 00-1.8-1.8C19.3 5 12 5 12 5s-7.3 0-8.8.5A2.5 2.5 0 001.4 7.3C1 8.8 1 12 1 12s0 3.2.4 4.7a2.5 2.5 0 001.8 1.8C4.7 19 12 19 12 19s7.3 0 8.8-.5a2.5 2.5 0 001.8-1.8C23 15.2 23 12 23 12zM9.8 15.3V8.7l5.7 3.3z"/></svg>
					<?php esc_html_e( 'Voir la chaîne YouTube', 'afsac' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<?php
		/*
		 * La révélation au scroll est posée sur le RAIL, pas sur chaque carte :
		 * les cartes hors du champ du rail n'entrent jamais dans la fenêtre, et
		 * un .afsac-reveal jamais déclenché resterait invisible (cf. la règle du
		 * moteur d'animation : rien de révélé dans un conteneur masqué).
		 */
		?>
		<div class="afsac-shorts__rail afsac-reveal">
			<button type="button" class="afsac-shorts__arrow afsac-shorts__arrow--prev" data-afsac-shorts-prev aria-label="<?php esc_attr_e( 'Vidéos précédentes', 'afsac' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7"/></svg>
			</button>

			<ul class="afsac-shorts__track" data-afsac-shorts-track>
				<?php foreach ( $afsac_shorts as $afsac_i => $afsac_short ) : ?>
					<?php
					$afsac_label = '' !== $afsac_short['title'] ? $afsac_short['title'] : __( 'Vidéo AFSAC', 'afsac' );
					?>
					<li class="afsac-shorts__item">
						<a class="afsac-shorts__card"
							href="<?php echo esc_url( $afsac_short['url'] ); ?>"
							target="_blank"
							rel="noopener noreferrer"
							data-afsac-short
							data-id="<?php echo esc_attr( $afsac_short['id'] ); ?>"
							data-title="<?php echo esc_attr( $afsac_label ); ?>">
							<img class="afsac-shorts__thumb"
								src="<?php echo esc_url( $afsac_short['thumb'] ); ?>"
								alt=""
								width="1080"
								height="1920"
								loading="<?php echo $afsac_i < 3 ? 'eager' : 'lazy'; ?>"
								decoding="async"
								referrerpolicy="no-referrer" />
							<span class="afsac-shorts__shade" aria-hidden="true"></span>
							<span class="afsac-shorts__play" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="currentColor" focusable="false"><path d="M8 5.5v13l11-6.5z"/></svg>
							</span>
							<span class="afsac-shorts__badge" aria-hidden="true"><?php esc_html_e( 'Short', 'afsac' ); ?></span>
							<span class="afsac-shorts__title"><?php echo esc_html( $afsac_label ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<button type="button" class="afsac-shorts__arrow afsac-shorts__arrow--next" data-afsac-shorts-next aria-label="<?php esc_attr_e( 'Vidéos suivantes', 'afsac' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 5l7 7-7 7"/></svg>
			</button>
		</div>

	</div>

	<?php
	/*
	 * Libellés de la lightbox, injectés dans le DOM par le script. Ils vivent ici
	 * (et non dans le JS) pour rester traduisibles et suivre la langue Polylang
	 * de la page.
	 */
	?>
	<template data-afsac-shorts-i18n
		data-close="<?php esc_attr_e( 'Fermer la vidéo', 'afsac' ); ?>"
		data-prev="<?php esc_attr_e( 'Vidéo précédente', 'afsac' ); ?>"
		data-next="<?php esc_attr_e( 'Vidéo suivante', 'afsac' ); ?>"
		data-watch="<?php esc_attr_e( 'Ouvrir sur YouTube', 'afsac' ); ?>"></template>
</section>
