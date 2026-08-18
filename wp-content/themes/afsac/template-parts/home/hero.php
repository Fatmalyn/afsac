<?php
/**
 * Composant : hero de la page d'accueil (fond VIDÉO « globe + trajectoires »
 * fourni par le client) + barre de statistiques.
 *
 * Le markup est mutualisé dans template-parts/shared/video-hero.php, en layout
 * `panel` : colonne éditoriale à gauche, vidéo ENTIÈRE (non recadrée) dans un
 * panneau à droite. La vidéo n'est chargée qu'en desktop et hors
 * reduced-motion (assets/js/hero-video.js) ; ailleurs le poster prend le relais.
 *
 * Historique : ce hero était auparavant une recréation SVG/CSS animée du
 * key-visual (globe filaire + guirlande dorée + ronde de personnes). Le client
 * a fourni sa propre vidéo le 17/08/2026 ; la scène SVG a été déposée. Son CSS
 * (`.afsac-hero-globe*`) reste dans style.css et n'est plus utilisé.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_stats = function_exists( 'afsac_hero_stats' ) ? afsac_hero_stats() : array();

// CTA éditables (options) avec repli Polylang-aware : calendrier des sessions
// puis hub « Formations & services ».
$afsac_cta1_url = function_exists( 'afsac_opt' ) ? afsac_opt( 'afsac_home_cta1_url' ) : '';
if ( '' === $afsac_cta1_url ) {
	$afsac_cta1_url = function_exists( 'afsac_get_calendar_url' ) ? afsac_get_calendar_url() : home_url( '/' );
}
$afsac_cta2_url = function_exists( 'afsac_opt' ) ? afsac_opt( 'afsac_home_cta2_url' ) : '';
if ( '' === $afsac_cta2_url ) {
	$afsac_cta2_url = function_exists( 'afsac_get_hub_url' ) ? afsac_get_hub_url() : '';
}
if ( '' === $afsac_cta2_url ) {
	$afsac_cta2_url = function_exists( 'afsac_get_catalogue_url' ) ? afsac_get_catalogue_url() : home_url( '/' );
}

get_template_part(
	'template-parts/shared/video-hero',
	null,
	array(
		'layout'        => 'panel',
		'align'         => 'start',
		'scroll'        => false,
		'video_src'     => get_theme_file_uri( 'assets/video/hero-accueil.mp4' ),
		'poster'        => get_theme_file_uri( 'assets/images/hero-accueil-poster.jpg' ),
		'eyebrow'       => __( 'Centre Régional de Formation à la Sûreté de l’Aviation · OACI', 'afsac' ),
		'title'         => __( 'L’expertise OACI au service de votre performance aéronautique.', 'afsac' ),
		'lead'          => __( 'Grâce à notre expertise <strong>ICAO ASTC</strong> et <strong>TRAINAIR PLUS</strong>, nous proposons des formations spécialisées, des solutions de renforcement des capacités, de l’ingénierie pédagogique et un accompagnement professionnel adaptés aux besoins de l’aviation civile.', 'afsac' ),
		'note'          => __( '<strong>Aucun État laissé de côté.</strong> Ensemble, développons les compétences et construisons une aviation plus sûre, plus performante et durable.', 'afsac' ),
		'cta_primary'   => array(
			'label' => __( 'Consulter le calendrier', 'afsac' ),
			'url'   => $afsac_cta1_url,
		),
		'cta_secondary' => array(
			'label' => __( 'Découvrir nos formations et services', 'afsac' ),
			'url'   => $afsac_cta2_url,
		),
	)
);
?>
<?php if ( ! empty( $afsac_stats ) ) : ?>
	<div class="afsac-hero__stats">
		<div class="afsac-container afsac-stats afsac-stagger">
			<?php
			foreach ( $afsac_stats as $afsac_stat ) :
				// Sépare le nombre (data-target) de son suffixe éventuel (ex. « 45+ » → 45 / « + »).
				preg_match( '/^(\d+)(.*)$/', (string) $afsac_stat['value'], $afsac_m );
				$afsac_target = isset( $afsac_m[1] ) ? $afsac_m[1] : '0';
				$afsac_suffix = isset( $afsac_m[2] ) ? $afsac_m[2] : '';
				?>
				<div class="afsac-stats__item afsac-reveal">
					<span class="afsac-stats__value">
						<span class="afsac-count" data-target="<?php echo esc_attr( $afsac_target ); ?>" data-suffix="<?php echo esc_attr( $afsac_suffix ); ?>">0</span>
					</span>
					<span class="afsac-stats__label"><?php echo esc_html( $afsac_stat['label'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>
