<?php
/**
 * Bannière de PROGRAMME (pleine largeur) — TRAINAIR PLUS ou AVSEC.
 *
 * Donne à chaque programme un poids visuel égal et sa propre identité :
 *   - trainair : navy + OR (champ d'étoiles, comète dorée le long de l'orbite) ;
 *   - avsec    : bleu + ORANGE (bouclier, impulsions radar) — couleurs demandées
 *     par le client pour la sûreté (« ICAO SECURITY AND FACILITATION »).
 *
 * Le CTA est rendu sur SA PROPRE LIGNE, en bas de la bannière (il ne chevauche
 * donc jamais le décor, y compris quand la mise en page se replie).
 * Chiffres animés via afsac_count_markup() (moteur global afsac-animate.js).
 *
 * @param array $args {
 *     @type string $variant « trainair » | « avsec ».
 *     @type string $eyebrow Sur-titre.
 *     @type string $name    Nom du programme (H2).
 *     @type string $desc    Description.
 *     @type array  $figures Liste de ['value'=>string,'label'=>string].
 *     @type array  $cta     ['label'=>string,'url'=>string].
 * }
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_variant = ( isset( $args['variant'] ) && 'avsec' === $args['variant'] ) ? 'avsec' : 'trainair';
$afsac_eyebrow = isset( $args['eyebrow'] ) ? (string) $args['eyebrow'] : '';
$afsac_name    = isset( $args['name'] ) ? (string) $args['name'] : '';
$afsac_desc    = isset( $args['desc'] ) ? (string) $args['desc'] : '';
$afsac_figures = ( isset( $args['figures'] ) && is_array( $args['figures'] ) ) ? $args['figures'] : array();
$afsac_cta     = ( isset( $args['cta'] ) && is_array( $args['cta'] ) ) ? $args['cta'] : array();
?>
<?php /* Pas d'id="trainair|avsec" ici : l'ancre #avsec pilote les ONGLETS (programme-tabs.js). Un id homonyme ferait scroller le navigateur avant la bascule. */ ?>
<section class="afsac-prog afsac-prog--<?php echo esc_attr( $afsac_variant ); ?>">

	<?php if ( 'trainair' === $afsac_variant ) : ?>
		<svg class="afsac-prog__deco" viewBox="0 0 1200 360" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
			<?php /* Orbites concentriques à droite : ancrent le vide et rappellent le globe de l'accueil. */ ?>
			<g class="afsac-prog__rings" fill="none" stroke="currentColor" stroke-width="1.2">
				<circle cx="1010" cy="180" r="88"/>
				<circle cx="1010" cy="180" r="146"/>
				<circle cx="1010" cy="180" r="208"/>
				<circle cx="1010" cy="180" r="274"/>
			</g>
			<circle class="afsac-prog__ring-gold" cx="1010" cy="180" r="146"/>
			<g fill="currentColor">
				<circle cx="180" cy="70" r="2.2" opacity="0.5"/>
				<circle cx="420" cy="40" r="1.5" opacity="0.4"/>
				<circle cx="700" cy="90" r="2.6" opacity="0.55"/>
				<circle cx="960" cy="60" r="1.8" opacity="0.45"/>
				<circle cx="1100" cy="130" r="2" opacity="0.5"/>
				<circle cx="300" cy="180" r="1.6" opacity="0.4"/>
				<circle cx="880" cy="200" r="2.4" opacity="0.5"/>
				<circle cx="560" cy="150" r="1.6" opacity="0.45"/>
				<circle cx="140" cy="290" r="1.8" opacity="0.35"/>
				<circle cx="1160" cy="280" r="1.6" opacity="0.4"/>
			</g>
			<g class="afsac-prog__tw" fill="#cfe0f5">
				<circle cx="640" cy="60" r="2.4"/>
				<circle cx="1040" cy="210" r="2.2"/>
				<circle cx="240" cy="240" r="2"/>
				<circle cx="820" cy="110" r="2.2"/>
			</g>
			<path d="M-20 320 Q600 120 1220 180" fill="none" stroke="currentColor" stroke-width="1.4" opacity="0.35"/>
			<path class="afsac-prog__comet" d="M-20 320 Q600 120 1220 180"/>
		</svg>
	<?php else : ?>
		<svg class="afsac-prog__deco" viewBox="0 0 1200 360" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
			<defs>
				<linearGradient id="afsac-shield-prog" x1="0" y1="0" x2="0" y2="1">
					<stop offset="0" stop-color="var(--afsac-silver-100)"/>
					<stop offset="0.5" stop-color="var(--afsac-silver-300)"/>
					<stop offset="1" stop-color="var(--afsac-silver-500)"/>
				</linearGradient>
			</defs>
			<g fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.55">
				<circle cx="980" cy="180" r="70"/>
				<circle cx="980" cy="180" r="120"/>
				<circle cx="980" cy="180" r="175"/>
				<circle cx="980" cy="180" r="240"/>
			</g>
			<circle class="afsac-prog__pulse" cx="980" cy="180" r="62"/>
			<circle class="afsac-prog__pulse afsac-prog__pulse--2" cx="980" cy="180" r="62"/>
			<path d="M980 92 L1036 115 C1036 190 1019 240 980 268 C941 240 924 190 924 115 Z" fill="url(#afsac-shield-prog)"/>
			<?php /* Coche en bleu OACI (et non plus en navy #072e58) : la bannière est passée en clair, la charte ne garde que le bleu et le blanc. */ ?>
			<path d="M957 180 L974 200 L1008 158" fill="none" stroke="#0054a4" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
	<?php endif; ?>

	<div class="afsac-container afsac-prog__inner">
		<div class="afsac-prog__id afsac-reveal">
			<?php if ( '' !== $afsac_eyebrow ) : ?>
				<span class="afsac-eyebrow afsac-prog__eyebrow"><?php echo esc_html( $afsac_eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $afsac_name ) : ?>
				<h2 class="afsac-prog__name"><?php echo esc_html( $afsac_name ); ?></h2>
			<?php endif; ?>
			<?php if ( '' !== $afsac_desc ) : ?>
				<p class="afsac-prog__desc"><?php echo esc_html( $afsac_desc ); ?></p>
			<?php endif; ?>
		</div>

		<?php
		/*
		 * Chiffres et CTA sur la MÊME ligne (retour client : trop de scroll).
		 * Le pied reste borné à la zone voilée de gauche : le CTA ne chevauche
		 * donc jamais le décor de droite, même quand la ligne se replie.
		 */
		?>
		<?php if ( ! empty( $afsac_figures ) || ( ! empty( $afsac_cta['label'] ) && ! empty( $afsac_cta['url'] ) ) ) : ?>
			<div class="afsac-prog__foot">

				<?php if ( ! empty( $afsac_figures ) ) : ?>
					<div class="afsac-prog__figs afsac-stagger">
						<?php
						foreach ( $afsac_figures as $afsac_fig ) :
							if ( ! isset( $afsac_fig['value'], $afsac_fig['label'] ) ) {
								continue;
							}
							?>
							<div class="afsac-reveal">
								<b><?php echo function_exists( 'afsac_count_markup' ) ? afsac_count_markup( $afsac_fig['value'] ) : esc_html( $afsac_fig['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper renvoie du HTML déjà échappé. ?></b>
								<span><?php echo esc_html( $afsac_fig['label'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $afsac_cta['label'] ) && ! empty( $afsac_cta['url'] ) ) : ?>
					<div class="afsac-prog__actions afsac-reveal">
						<a class="afsac-button afsac-prog__cta" href="<?php echo esc_url( $afsac_cta['url'] ); ?>">
							<?php echo esc_html( $afsac_cta['label'] ); ?>
							<span class="afsac-arrow" aria-hidden="true">&rarr;</span>
						</a>
					</div>
				<?php endif; ?>

			</div>
		<?php endif; ?>
	</div>
</section>
