<?php
/**
 * Formations & Services — section 3 « Piliers » (2 colonnes).
 *
 * Deux piliers (Formations OACI / Services & conseil) sur fond blanc. Chaque
 * carte pose un décor SVG en background CSS (décoratif, hors arbre a11y, comme
 * la bande hero) avec deux surimpressions en texte vivant i18n : un libellé en
 * haut-gauche et un badge en bas. Le badge du pilier 1 est dynamique (domaines
 * et cours via les helpers afsac-core) ; celui du pilier 2 est statique.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Badge dynamique du pilier 1 : nombre de domaines OACI + nombre de cours.
$afsac_areas   = function_exists( 'afsac_get_domains_count' ) ? afsac_get_domains_count() : 0;
$afsac_courses = function_exists( 'afsac_get_fs_field' ) ? (int) afsac_get_fs_field( 'afsac_course_count' ) : 0;
$afsac_badge_1 = sprintf(
	/* translators: 1: number of ICAO areas, 2: number of courses */
	esc_html__( '%1$s domaines · %2$s cours', 'afsac' ),
	number_format_i18n( $afsac_areas ),
	number_format_i18n( $afsac_courses )
);

// URL de la page Catalogue (langue courante) pour le CTA du pilier « Formations OACI ».
$afsac_catalogue_url = function_exists( 'afsac_get_catalogue_url' ) ? afsac_get_catalogue_url() : '';

$afsac_pillars = array(
	array(
		'eyebrow'    => __( 'Pilier 01', 'afsac' ),
		'title'      => __( 'Formations OACI', 'afsac' ),
		'svg'        => 'salle',
		'image'      => 'intro-slide-1.webp',
		'card_label' => __( 'Certifié OACI', 'afsac' ),
		'badge'      => $afsac_badge_1,                          // déjà échappé (esc_html__)
		// Texte et puces volontairement NON redondants avec les statistiques du hero
		// (483 cours · 11 domaines · 3 langues) : on n'y met que ce qui différencie.
		'text'       => __( 'Le catalogue complet des cours standardisés OACI, dispensés en présentiel, à distance ou en intra-entreprise.', 'afsac' ),
		'items'      => array(
			__( 'Instructeurs certifiés OACI', 'afsac' ),
			__( 'Certificats reconnus à l\'international', 'afsac' ),
			__( 'Sessions ouvertes & intra sur demande', 'afsac' ),
		),
		'cta'        => __( 'Voir le catalogue', 'afsac' ),
		'cta_url'    => $afsac_catalogue_url ? $afsac_catalogue_url : '#',
	),
	array(
		'eyebrow'    => __( 'Pilier 02', 'afsac' ),
		'title'      => __( 'Services & conseil', 'afsac' ),
		'svg'        => 'reunion',
		'image'      => 'intro-slide-2.webp',
		'card_label' => __( 'Experts ICAO', 'afsac' ),
		'badge'      => esc_html__( 'Audit · Ingénierie · Accompagnement', 'afsac' ),
		'text'       => __( 'L\'accompagnement réglementaire et opérationnel des autorités, aéroports et compagnies aériennes.', 'afsac' ),
		'items'      => array(
			__( 'Audit AVSEC & conformité Annexe 17', 'afsac' ),
			__( 'Ingénierie de formation sur mesure', 'afsac' ),
			__( 'Création & certification de centres OACI', 'afsac' ),
		),
		'cta'        => __( 'Voir nos services', 'afsac' ),
		// Ancre vers la section Services du hub (pas de page dédiée).
		'cta_url'    => '#services',
	),
);
?>
<section class="afsac-section afsac-fs-pillars">
	<div class="afsac-container">
		<?php
		get_template_part(
			'template-parts/shared/section-head',
			null,
			array(
				'eyebrow' => __( 'Nos deux piliers', 'afsac' ),
				'title'   => __( 'Formation et conseil, sous un même toit', 'afsac' ),
				'center'  => true,
			)
		);
		?>
		<div class="afsac-fs-pillars__grid afsac-stagger">
			<?php foreach ( $afsac_pillars as $afsac_pillar ) : ?>
				<article class="afsac-fs-pillars__item afsac-reveal">
					<span class="afsac-eyebrow"><?php echo esc_html( $afsac_pillar['eyebrow'] ); ?></span>
					<h2 class="afsac-fs-pillars__title"><?php echo esc_html( $afsac_pillar['title'] ); ?></h2>

					<div class="afsac-fs-pillars__card afsac-fs-pillars__card--<?php echo esc_attr( $afsac_pillar['svg'] ); ?>">
						<?php if ( ! empty( $afsac_pillar['image'] ) ) : ?>
							<span class="afsac-fs-pillars__card-media" style="background-image:url(<?php echo esc_url( get_theme_file_uri( 'assets/images/' . $afsac_pillar['image'] ) ); ?>);" aria-hidden="true"></span>
						<?php endif; ?>
						<span class="afsac-eyebrow afsac-fs-pillars__card-label"><?php echo esc_html( $afsac_pillar['card_label'] ); ?></span>
						<span class="afsac-fs-pillars__card-badge"><?php echo $afsac_pillar['badge']; // déjà échappé via esc_html__ ?></span>
					</div>

					<p class="afsac-fs-pillars__text"><?php echo esc_html( $afsac_pillar['text'] ); ?></p>

					<ul class="afsac-fs-pillars__list">
						<?php foreach ( $afsac_pillar['items'] as $afsac_item ) : ?>
							<li><?php echo esc_html( $afsac_item ); ?></li>
						<?php endforeach; ?>
					</ul>

					<?php
					/*
					 * Bouton PLEIN (retour client : le lien-flèche seul passait inaperçu).
					 * Son ::after couvre toute la carte → carte entièrement cliquable, sans
					 * envelopper le contenu dans un <a> (le nom accessible du lien reste
					 * « Voir le catalogue » et non tout le texte de la carte).
					 */
					?>
					<a class="afsac-button afsac-button--accent afsac-fs-pillars__cta" href="<?php echo esc_url( $afsac_pillar['cta_url'] ); ?>">
						<?php echo esc_html( $afsac_pillar['cta'] ); ?>
						<span class="afsac-arrow" aria-hidden="true">&rarr;</span>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
