<?php
/**
 * Catalogue — section « Par programme » : 2 accès distincts AVSEC / TRAINAIR PLUS.
 *
 * Demande client (rapport du 19/06/2026) : depuis « Voir le catalogue », offrir
 * deux portes d'entrée distinctes vers les formations AVSEC et TRAINAIR PLUS.
 * Réutilise les cartes « département » de l'accueil (mêmes classes .afsac-dept-card,
 * mêmes décors vectoriels, même système d'animation reveal/stagger) pour une
 * cohérence visuelle totale avec la home. Chaque carte pointe vers l'archive des
 * formations pré-filtrée par famille (tokens stables ?famille=, cf.
 * functions.php → afsac_formation_archive_filter()).
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_archive = get_post_type_archive_link( 'afsac_formation' );
if ( ! $afsac_archive ) {
	$afsac_archive = home_url( '/' );
}
$afsac_see = esc_html__( 'Voir les cours', 'afsac' ); // réutilisé ×2, déjà échappé.

// Chapô descriptif (repris de l'ancienne intro « collée » sous le hero, placé ici,
// bien aéré, dans l'en-tête de section).
$afsac_fam_domains = function_exists( 'afsac_get_domains_count' ) ? afsac_get_domains_count() : 0;
$afsac_fam_courses = function_exists( 'afsac_get_hub_field' ) ? (int) afsac_get_hub_field( 'afsac_course_count' ) : 0;
$afsac_fam_intro   = sprintf(
	/* translators: 1: number of ICAO areas, 2: number of courses. */
	__( 'Programmes TRAINAIR PLUS & AVSEC : %1$s domaines aéronautiques OACI, %2$s cours standardisés, en français et en anglais.', 'afsac' ),
	number_format_i18n( $afsac_fam_domains ),
	number_format_i18n( $afsac_fam_courses )
);
?>
<section class="afsac-section afsac-section--center afsac-catalogue-familles">
	<div class="afsac-container">
		<?php
		get_template_part(
			'template-parts/shared/section-head',
			null,
			array(
				'eyebrow' => __( 'Par programme', 'afsac' ),
				'title'   => __( 'Deux programmes, un même standard OACI', 'afsac' ),
				'intro'   => $afsac_fam_intro,
				'center'  => true,
			)
		);
		?>
		<div class="afsac-dept-grid afsac-dept-grid--duo afsac-stagger">

			<?php /* TRAINAIR PLUS : champ d'étoiles + ligne d'orbite (décor de la home). */ ?>
			<a class="afsac-dept-card afsac-dept-card--trainair afsac-reveal" href="<?php echo esc_url( add_query_arg( array( 'famille' => 'trainair' ), $afsac_archive ) ); ?>">
				<span class="afsac-dept-card__badge">OACI</span>
				<svg class="afsac-dept-card__deco" viewBox="0 0 325 423" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
					<g fill="currentColor">
						<circle cx="108.1" cy="77.3" r="2.2" opacity="0.18"/>
						<circle cx="30.4" cy="223.6" r="1.4" opacity="0.5"/>
						<circle cx="25.9" cy="212.8" r="0.9" opacity="0.18"/>
						<circle cx="142" cy="46.5" r="0.9" opacity="0.5"/>
						<circle cx="139.2" cy="334.2" r="0.9" opacity="0.25"/>
						<circle cx="202.9" cy="241.5" r="0.9" opacity="0.5"/>
						<circle cx="188.9" cy="38.8" r="1.1" opacity="0.18"/>
						<circle cx="180" cy="70.6" r="1.4" opacity="0.25"/>
						<circle cx="175.1" cy="236.9" r="1.8" opacity="0.8"/>
						<circle cx="218.7" cy="59.2" r="1.8" opacity="0.65"/>
						<circle cx="66.1" cy="57" r="2.2" opacity="0.18"/>
						<circle cx="182.4" cy="255.2" r="1.4" opacity="0.65"/>
						<circle cx="172.3" cy="315.3" r="1.4" opacity="0.5"/>
						<circle cx="293.3" cy="157.4" r="1.1" opacity="0.8"/>
						<circle cx="63.5" cy="316.3" r="0.9" opacity="0.5"/>
						<circle cx="100.8" cy="208.1" r="1.4" opacity="0.65"/>
						<circle cx="146.7" cy="251.4" r="0.9" opacity="0.18"/>
						<circle cx="166.2" cy="82.7" r="1.4" opacity="0.25"/>
						<circle cx="296.4" cy="180.2" r="2.2" opacity="0.18"/>
						<circle cx="244.3" cy="237.7" r="2.8" opacity="0.3"/>
						<circle cx="113.1" cy="153.1" r="1.4" opacity="0.5"/>
						<circle cx="254.2" cy="46.1" r="0.9" opacity="0.3"/>
						<circle cx="154.5" cy="272.4" r="0.9" opacity="0.65"/>
						<circle cx="224.8" cy="265.9" r="2.2" opacity="0.8"/>
						<circle cx="145.7" cy="292.3" r="2.2" opacity="0.3"/>
						<circle cx="15" cy="195.4" r="1.1" opacity="0.5"/>
						<circle cx="44.2" cy="42.4" r="2.8" opacity="0.3"/>
						<circle cx="48" cy="114.1" r="1.4" opacity="0.8"/>
						<circle cx="161.4" cy="83.2" r="1.4" opacity="0.5"/>
						<circle cx="93.9" cy="72" r="1.4" opacity="0.8"/>
						<circle cx="178" cy="288.4" r="1.4" opacity="0.65"/>
						<circle cx="281.2" cy="383.9" r="1.1" opacity="0.18"/>
						<circle cx="62.5" cy="108.1" r="1.1" opacity="0.18"/>
						<circle cx="157.9" cy="243.9" r="1.4" opacity="0.3"/>
					</g>
					<g fill="none" stroke="currentColor" stroke-linecap="round">
						<path d="M-12 332 Q150 205 338 150" stroke-width="1.4" opacity="0.4"/>
						<path d="M64 304 l18 -7" stroke-width="2.2" opacity="0.55"/>
						<path d="M196 199 l17 -6" stroke-width="2.2" opacity="0.45"/>
					</g>
					<g class="afsac-fam-stars" fill="#cfe0f5">
						<circle cx="175" cy="237" r="1.9"/>
						<circle cx="93" cy="72" r="1.6"/>
						<circle cx="293" cy="157" r="1.5"/>
						<circle cx="48" cy="114" r="1.7"/>
						<circle cx="225" cy="266" r="1.9"/>
						<circle cx="146" cy="251" r="1.4"/>
					</g>
					<path class="afsac-fam-orbit" d="M-12 332 Q150 205 338 150"/>
				</svg>
				<span class="afsac-dept-card__body">
					<span class="afsac-dept-card__subtitle"><?php esc_html_e( '11 domaines OACI · programmes standardisés', 'afsac' ); ?></span>
					<span class="afsac-dept-card__title">TRAINAIR PLUS</span>
					<span class="afsac-dept-card__cta"><?php echo $afsac_see; ?></span>
				</span>
			</a>

			<?php /* AVSEC : anneaux concentriques + bouclier métallisé (décor de la home). */ ?>
			<a class="afsac-dept-card afsac-dept-card--avsec afsac-reveal" href="<?php echo esc_url( add_query_arg( array( 'famille' => 'avsec' ), $afsac_archive ) ); ?>">
				<span class="afsac-dept-card__badge">Annexe 17</span>
				<svg class="afsac-dept-card__deco" viewBox="0 0 325 423" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
					<defs>
						<linearGradient id="afsac-shield-grad-cat" x1="0" y1="0" x2="0" y2="1">
							<stop offset="0" stop-color="var(--afsac-silver-100)"/>
							<stop offset="0.5" stop-color="var(--afsac-silver-300)"/>
							<stop offset="1" stop-color="var(--afsac-silver-500)"/>
						</linearGradient>
					</defs>
					<g fill="none" stroke="currentColor" stroke-width="1.6">
						<circle cx="162" cy="205" r="60" opacity="0.85"/>
						<circle cx="162" cy="205" r="105" opacity="0.68"/>
						<circle cx="162" cy="205" r="150" opacity="0.52"/>
						<circle cx="162" cy="205" r="195" opacity="0.38"/>
						<circle cx="162" cy="205" r="240" opacity="0.26"/>
						<circle cx="162" cy="205" r="285" opacity="0.16"/>
					</g>
					<circle class="afsac-fam-pulse" cx="162" cy="205" r="52"/>
					<circle class="afsac-fam-pulse afsac-fam-pulse--2" cx="162" cy="205" r="52"/>
					<path d="M162 120 L236 150 C236 250 214 318 162 356 C110 318 88 250 88 150 Z" fill="url(#afsac-shield-grad-cat)"/>
					<path d="M131 206 L153 232 L198 178" fill="none" stroke="var(--afsac-navy)" stroke-width="11" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<span class="afsac-dept-card__body">
					<span class="afsac-dept-card__subtitle"><?php esc_html_e( 'Sûreté de l’aviation civile · FR · EN', 'afsac' ); ?></span>
					<span class="afsac-dept-card__title">AVSEC</span>
					<span class="afsac-dept-card__cta"><?php echo $afsac_see; ?></span>
				</span>
			</a>

		</div>
	</div>
</section>
