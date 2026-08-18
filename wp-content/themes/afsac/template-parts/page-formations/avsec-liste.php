<?php
/**
 * Page de LISTE des formations AVSEC — cours certifiants / ateliers OACI.
 *
 * Pendant AVSEC de l'archive de domaine TRAINAIR PLUS (taxonomy-afsac_area.php) :
 * demande client du 07/08/2026 — sur le catalogue, « Voir plus » dépliait la liste
 * SOUS la section ; le client la veut sur une page à part, comme un domaine
 * TRAINAIR PLUS. Les deux « Voir plus » pointent donc désormais ici.
 *
 * Servi par archive-afsac_formation.php sur ?famille=avsec[&format=cours|atelier] :
 * la typologie n'est PAS une taxonomie (elle est DÉRIVÉE de `_afsac_import_key`
 * par les helpers afsac_avsec_*() du plugin), elle ne peut donc pas avoir
 * d'archive de terme. Les fiches viennent du helper, pas de la boucle principale.
 *
 * Réemploie les composants déjà en place : le bandeau .afsac-area-hero et ses
 * onglets (ici de VRAIS liens, une page par format), et les cartes
 * .afsac-avsec-list/.afsac-avsec-item du catalogue.
 *
 * @package AFSAC\Theme
 *
 * @var array $args {
 *     @type string $format « cours », « atelier » ou '' pour les deux.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'afsac_avsec_get_courses' ) ) {
	return;
}

$afsac_format = ( isset( $args['format'] ) && in_array( $args['format'], array( 'cours', 'atelier' ), true ) )
	? $args['format']
	: '';

$afsac_courses = afsac_avsec_get_courses();
$afsac_themes  = afsac_avsec_themes();
$afsac_stats   = afsac_avsec_stats( $afsac_courses );

// Sous-ensemble affiché : l'ordre du helper (thématique, puis cours avant ateliers).
$afsac_subset = ( '' === $afsac_format )
	? $afsac_courses
	: array_values(
		array_filter(
			$afsac_courses,
			static function ( $c ) use ( $afsac_format ) {
				return $afsac_format === $c['kind'];
			}
		)
	);

// En-tête : un titre et un chapô par format. Les chapôs reprennent MOT POUR MOT
// ceux des deux volets du catalogue : le visiteur arrive ici depuis eux.
$afsac_titles = array(
	''        => __( 'Cours & ateliers AVSEC', 'afsac' ),
	'cours'   => __( 'Cours certifiants', 'afsac' ),
	'atelier' => __( 'Ateliers OACI', 'afsac' ),
);
$afsac_intros = array(
	''        => __( 'Deux formats, deux finalités : le cours qualifie une personne, l’atelier produit un document de référence.', 'afsac' ),
	'cours'   => __( 'Un cours qualifie une FONCTION : agent d’inspection filtrage, superviseur, inspecteur national, instructeur. Il se termine par une évaluation et délivre un certificat de l’OACI, reconnu à l’international.', 'afsac' ),
	'atelier' => __( 'Un atelier réunit les acteurs d’un même dispositif pour produire un LIVRABLE : programme national de sûreté, programme de contrôle qualité, plan de gestion de crise, système de certification. Le travail est collectif et directement applicable.', 'afsac' ),
);

// Les trois vues de la page (onglets = liens, une URL par format).
$afsac_archive = get_post_type_archive_link( 'afsac_formation' );
if ( ! $afsac_archive ) {
	$afsac_archive = home_url( '/' );
}
$afsac_base = add_query_arg( array( 'famille' => 'avsec' ), $afsac_archive );
$afsac_tabs = array(
	array(
		'format' => '',
		'label'  => __( 'Tout le programme AVSEC', 'afsac' ),
		'count'  => (int) $afsac_stats['total'],
		'url'    => $afsac_base,
	),
	array(
		'format' => 'cours',
		'label'  => __( 'Cours certifiants', 'afsac' ),
		'count'  => (int) $afsac_stats['cours'],
		'url'    => add_query_arg( array( 'format' => 'cours' ), $afsac_base ),
	),
	array(
		'format' => 'atelier',
		'label'  => __( 'Ateliers OACI', 'afsac' ),
		'count'  => (int) $afsac_stats['ateliers'],
		'url'    => add_query_arg( array( 'format' => 'atelier' ), $afsac_base ),
	),
);

/*
 * Retour au catalogue AVEC l'ancre #avsec : sans elle la page se rouvre sur
 * l'onglet TRAINAIR PLUS (programme-tabs.js n'ouvre l'onglet que sur ce hash),
 * et le visiteur perd le fil d'où il venait.
 */
$afsac_back = function_exists( 'afsac_get_catalogue_url' ) ? afsac_get_catalogue_url() : '';
if ( '' !== $afsac_back ) {
	$afsac_back .= '#avsec';
}
$afsac_inscription = function_exists( 'afsac_get_inscription_url' ) ? afsac_get_inscription_url() : '';
?>

<main id="primary" class="afsac-area afsac-avsec-page" style="--afsac-area-accent: #0054a4;">

	<header class="afsac-area-hero">
		<div class="afsac-container">
			<?php if ( '' !== $afsac_back ) : ?>
				<a class="afsac-area-hero__back" href="<?php echo esc_url( $afsac_back ); ?>"><span aria-hidden="true">‹</span> <?php esc_html_e( 'Retour au catalogue', 'afsac' ); ?></a>
			<?php endif; ?>

			<span class="afsac-area-hero__eyebrow"><?php esc_html_e( 'Programme AVSEC', 'afsac' ); ?></span>
			<h1 class="afsac-area-hero__title"><?php echo esc_html( $afsac_titles[ $afsac_format ] ); ?></h1>
			<p class="afsac-area-hero__intro"><?php echo esc_html( $afsac_intros[ $afsac_format ] ); ?></p>

			<nav class="afsac-area-tabs afsac-area-hero__tabs" aria-label="<?php esc_attr_e( 'Format de formation', 'afsac' ); ?>">
				<?php foreach ( $afsac_tabs as $afsac_tab ) : ?>
					<a class="afsac-area-tab<?php echo $afsac_tab['format'] === $afsac_format ? ' is-active' : ''; ?>"
						href="<?php echo esc_url( $afsac_tab['url'] ); ?>"
						<?php echo $afsac_tab['format'] === $afsac_format ? ' aria-current="page"' : ''; ?>>
						<?php echo esc_html( $afsac_tab['label'] ); ?>
						<span class="afsac-area-tab__count">(<?php echo esc_html( number_format_i18n( $afsac_tab['count'] ) ); ?>)</span>
					</a>
				<?php endforeach; ?>
			</nav>
		</div>
	</header>

	<section class="afsac-area__list">
		<div class="afsac-container">
			<?php if ( ! empty( $afsac_subset ) ) : ?>

				<ul class="afsac-avsec-list afsac-avsec-page__list">
					<?php
					foreach ( $afsac_subset as $afsac_c ) :
						$afsac_t_label = ( '' !== $afsac_c['theme'] && isset( $afsac_themes[ $afsac_c['theme'] ] ) )
							? $afsac_themes[ $afsac_c['theme'] ]['label']
							: '';
						$afsac_t_color = ( '' !== $afsac_c['theme'] && isset( $afsac_themes[ $afsac_c['theme'] ] ) )
							? $afsac_themes[ $afsac_c['theme'] ]['color']
							: 'var(--afsac-border)';
						?>
						<li class="afsac-avsec-item" style="--afsac-avsec-accent: <?php echo esc_attr( $afsac_t_color ); ?>;">
							<a class="afsac-avsec-item__link" href="<?php echo esc_url( $afsac_c['url'] ); ?>">
								<h2 class="afsac-avsec-item__title"><?php echo esc_html( $afsac_c['title'] ); ?></h2>
								<span class="afsac-avsec-item__meta">
									<?php if ( '' !== $afsac_c['duree'] ) : ?>
										<span class="afsac-avsec-item__duree">
											<?php echo function_exists( 'afsac_meta_icon' ) ? afsac_meta_icon( 'duration' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper renvoie du SVG statique. ?>
											<?php echo esc_html( $afsac_c['duree'] ); ?>
										</span>
									<?php endif; ?>
									<?php if ( '' === $afsac_format ) : ?>
										<span class="afsac-avsec-item__kind">
											<?php echo esc_html( 'atelier' === $afsac_c['kind'] ? __( 'Atelier', 'afsac' ) : __( 'Cours certifiant', 'afsac' ) ); ?>
										</span>
									<?php endif; ?>
									<?php if ( '' !== $afsac_t_label ) : ?>
										<span class="afsac-avsec-item__theme"><?php echo esc_html( $afsac_t_label ); ?></span>
									<?php endif; ?>
								</span>
								<span class="afsac-avsec-item__go" aria-hidden="true">&rarr;</span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>

				<div class="afsac-avsec-page__actions">
					<?php if ( '' !== $afsac_inscription ) : ?>
						<a class="afsac-btn afsac-btn--accent" href="<?php echo esc_url( $afsac_inscription ); ?>"><?php esc_html_e( 'Demander une inscription', 'afsac' ); ?></a>
					<?php endif; ?>
					<?php if ( '' !== $afsac_back ) : ?>
						<a class="afsac-btn afsac-btn--outline" href="<?php echo esc_url( $afsac_back ); ?>"><?php esc_html_e( 'Retour au catalogue', 'afsac' ); ?></a>
					<?php endif; ?>
				</div>

			<?php else : ?>

				<div class="afsac-area__empty">
					<h2 class="afsac-area__empty-title"><?php esc_html_e( 'Aucune formation dans ce format pour le moment', 'afsac' ); ?></h2>
					<p class="afsac-area__empty-text"><?php esc_html_e( 'Contactez-nous pour organiser une session sur mesure ou recevoir le programme détaillé.', 'afsac' ); ?></p>
					<?php if ( '' !== $afsac_back ) : ?>
						<a class="afsac-btn afsac-btn--accent" href="<?php echo esc_url( $afsac_back ); ?>"><?php esc_html_e( 'Retour au catalogue', 'afsac' ); ?></a>
					<?php endif; ?>
				</div>

			<?php endif; ?>
		</div>
	</section>

</main>
