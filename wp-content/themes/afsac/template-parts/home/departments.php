<?php
/**
 * Accueil — section « Choisissez votre département » (4 cartes sur-mesure).
 *
 * Mise en page (cf. maquette) : format portrait (aspect-ratio 325/423), texte
 * en bas de carte dans l'ordre sous-titre (petites capitales) → titre (serif) →
 * lien « Voir les cours » (flèche →). Chaque carte porte une décoration
 * VECTORIELLE inline (SVG), traits en --afsac-blue, derrière le texte, avec un
 * scrim sombre en bas (géré en CSS) pour la lisibilité.
 *
 * Les 3 cartes AVSEC sont un sélecteur de langue : leurs titres/sous-titres
 * restent dans leur langue (autonymes), donc LITTÉRAUX et identiques en FR/EN
 * (pas de gettext). Seuls l'eyebrow, le titre de section, le sous-titre TRAINAIR
 * et les libellés de liens passent en gettext (domaine afsac).
 *
 * Chiffres (483 / 11 domaines) en dur pour l'instant. Liens en avance vers
 * l'archive des formations filtrée (famille/langue). Carte AR : mots arabes en
 * dir=rtl (shaping), bloc aligné à gauche comme la maquette.
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
$afsac_see = esc_html__( 'Voir les cours', 'afsac' ); // réutilisé ×4, déjà échappé.
?>
<section class="afsac-section afsac-section--light afsac-section--center afsac-departments">
	<div class="afsac-container">

		<div class="afsac-section__head">
			<span class="afsac-eyebrow"><?php esc_html_e( 'Catégories de formation', 'afsac' ); ?></span>
			<h2 class="afsac-departments__title"><?php esc_html_e( 'Choisissez votre département', 'afsac' ); ?></h2>
		</div>

		<?php /* 3 colonnes depuis le retrait de la carte arabe (elles étaient 4). */ ?>
		<div class="afsac-dept-grid afsac-dept-grid--trio afsac-stagger">

			<?php /* 1 — TRAINAIR PLUS : champ d'étoiles + ligne d'orbite. */ ?>
			<a class="afsac-dept-card afsac-dept-card--trainair afsac-reveal" href="<?php echo esc_url( add_query_arg( array( 'famille' => 'trainair' ), $afsac_archive ) ); ?>">
				<span class="afsac-dept-card__badge">483</span>
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
				</svg>
				<span class="afsac-dept-card__body">
					<span class="afsac-dept-card__subtitle"><?php esc_html_e( '11 domaines OACI · 483 cours', 'afsac' ); ?></span>
					<span class="afsac-dept-card__title">TRAINAIR PLUS</span>
					<span class="afsac-dept-card__cta"><?php echo $afsac_see; ?></span>
				</span>
			</a>

			<?php /* 2 — AVSEC · Français (littéral) : anneaux concentriques + bouclier métallisé. */ ?>
			<a class="afsac-dept-card afsac-dept-card--fr afsac-reveal" href="<?php echo esc_url( add_query_arg( array( 'famille' => 'avsec', 'langue' => 'fr' ), $afsac_archive ) ); ?>">
				<span class="afsac-dept-card__badge">FR</span>
				<svg class="afsac-dept-card__deco" viewBox="0 0 325 423" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
					<defs>
						<linearGradient id="afsac-shield-grad" x1="0" y1="0" x2="0" y2="1">
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
					<path d="M162 120 L236 150 C236 250 214 318 162 356 C110 318 88 250 88 150 Z" fill="url(#afsac-shield-grad)"/>
					<path d="M131 206 L153 232 L198 178" fill="none" stroke="var(--afsac-navy)" stroke-width="11" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<span class="afsac-dept-card__body">
					<span class="afsac-dept-card__subtitle">Sûreté de l’aviation en français</span>
					<span class="afsac-dept-card__title">AVSEC · Français</span>
					<span class="afsac-dept-card__cta"><?php echo $afsac_see; ?></span>
				</span>
			</a>

			<?php /* 3 — AVSEC · English (littéral) : diagonales + icône document. */ ?>
			<a class="afsac-dept-card afsac-dept-card--en afsac-reveal" href="<?php echo esc_url( add_query_arg( array( 'famille' => 'avsec', 'langue' => 'en' ), $afsac_archive ) ); ?>">
				<span class="afsac-dept-card__badge">EN</span>
				<svg class="afsac-dept-card__deco" viewBox="0 0 325 423" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
					<defs>
						<pattern id="afsac-diag" width="34" height="34" patternUnits="userSpaceOnUse" patternTransform="rotate(-18)">
							<line x1="0" y1="0" x2="0" y2="34" stroke="currentColor" stroke-width="1" opacity="0.16"/>
						</pattern>
					</defs>
					<rect x="0" y="0" width="325" height="423" fill="url(#afsac-diag)"/>
					<g>
						<rect x="86" y="150" width="153" height="120" rx="12" fill="none" stroke="var(--afsac-silver-300)" stroke-width="2.4"/>
						<line x1="108" y1="192" x2="217" y2="192" stroke="var(--afsac-silver-300)" stroke-width="3" stroke-linecap="round"/>
						<line x1="108" y1="216" x2="217" y2="216" stroke="var(--afsac-silver-300)" stroke-width="3" stroke-linecap="round"/>
						<line x1="108" y1="240" x2="184" y2="240" stroke="var(--afsac-silver-300)" stroke-width="3" stroke-linecap="round"/>
						<circle cx="206" cy="240" r="11" fill="none" stroke="currentColor" stroke-width="3"/>
					</g>
				</svg>
				<span class="afsac-dept-card__body">
					<span class="afsac-dept-card__subtitle">Aviation Security in English</span>
					<span class="afsac-dept-card__title">AVSEC · English</span>
					<span class="afsac-dept-card__cta"><?php echo $afsac_see; ?></span>
				</span>
			</a>

			<?php
			/*
			 * La 4e carte « AVSEC · العربية » a été RETIRÉE (demande client :
			 * « enlever l'arabe »). La vitrine n'annonce plus que FR/EN. Les cours
			 * dispensés en arabe restent en base (taxonomie afsac_langue) : la carte
			 * pointait vers ?famille=avsec&langue=ar, filtre toujours fonctionnel si
			 * le client revient sur sa décision.
			 */
			?>

		</div>

		<div class="afsac-departments__actions">
			<a class="afsac-button afsac-reveal" href="<?php echo esc_url( function_exists( 'afsac_get_catalogue_url' ) ? afsac_get_catalogue_url() : $afsac_archive ); ?>">
				<?php esc_html_e( 'Voir tout le catalogue', 'afsac' ); ?>
			</a>
		</div>

	</div>
</section>
