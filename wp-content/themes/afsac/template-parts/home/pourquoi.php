<?php
/**
 * Accueil — section « Pourquoi nous choisir » (L'avantage AFSAC).
 *
 * Anneau d'atouts tournant autour de l'emblème OACI, et panneau de texte qui
 * suit le nœud actif (refonte demandée par le client sur le modèle d'un site de
 * référence). Chaque atout = un bouton icône sur l'arc + une diapositive
 * « surtitre / titre / paragraphe ».
 *
 * Géométrie : les nœuds sont répartis sur un arc de 200° (pas 360°), et l'anneau
 * pivote pour amener le nœud actif face au texte ; chaque nœud contre-pivote
 * pour rester droit. Le rayon vient d'une variable CSS (--afsac-why-r), donc les
 * points de rupture ne sont écrits qu'une fois, dans la feuille de style.
 *
 * Transition (demande client du 18/08/2026) : au changement, le texte ARRIVE DE
 * LA DROITE et l'ancien part vers la gauche, ligne par ligne. Le sens est porté
 * par la variable CSS --afsac-why-dir, écrite par le JS = sens de lecture ×
 * sens de navigation (elle s'inverse donc sur « précédent » et en arabe).
 *
 * Motif ARIA « onglets » : l'anneau est le tablist, les diapositives sont les
 * panneaux. L'anneau n'est donc PAS aria-hidden (il contient des boutons
 * focusables) ; seuls les éléments purement décoratifs le sont. Sous 1200px
 * l'anneau disparaît : les flèches du panneau prennent le relais, elles sont
 * donc affichées à toutes les largeurs.
 *
 * Chaînes traduisibles (gettext, domaine afsac) ; les SVG sont décoratifs, le
 * sens est porté par le surtitre repris en texte lecteur d'écran sur le bouton.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Les 6 atouts, dans l'ordre validé par le client (07/08/2026). `icon` = clé
 * d'un tracé SVG défini plus bas (mono-trait) ; le client fournit des emoji, on
 * garde les icônes maison pour rester homogène avec le reste du site.
 * Aucun chiffre n'est affiché ici : une diapositive masquée n'étant jamais
 * observée par l'IntersectionObserver, un compteur animé y resterait à zéro.
 */
$afsac_why = array(
	array(
		'icon'    => 'shield',
		'eyebrow' => esc_html__( 'Reconnaissance internationale', 'afsac' ),
		'title'   => esc_html__( 'Une reconnaissance internationale OACI', 'afsac' ),
		'text'    => esc_html__( 'Bénéficiant d’une double reconnaissance en tant que Centre Régional de Formation à la Sûreté de l’Aviation Civile (ICAO ASTC) et membre du Programme ICAO TRAINAIR PLUS, l’AFSAC garantit des formations alignées sur les standards internationaux de l’aviation civile.', 'afsac' ),
	),
	array(
		'icon'    => 'award',
		'eyebrow' => esc_html__( 'Certification', 'afsac' ),
		'title'   => esc_html__( 'Des formations certifiantes reconnues mondialement', 'afsac' ),
		'text'    => esc_html__( 'Nos programmes sont développés selon les méthodologies officielles de l’OACI et permettent aux professionnels d’acquérir des compétences opérationnelles conformes aux exigences internationales.', 'afsac' ),
	),
	array(
		'icon'    => 'users',
		'eyebrow' => esc_html__( 'Experts & instructeurs', 'afsac' ),
		'title'   => esc_html__( 'Un réseau d’experts et d’instructeurs internationaux', 'afsac' ),
		'text'    => esc_html__( 'L’AFSAC s’appuie sur des experts aéronautiques qualifiés et expérimentés, capables de transmettre des connaissances actualisées adaptées aux réalités opérationnelles des États et organisations.', 'afsac' ),
	),
	array(
		'icon'    => 'layers',
		'eyebrow' => esc_html__( 'Domaines couverts', 'afsac' ),
		'title'   => esc_html__( 'Une expertise couvrant les différents domaines de l’aviation civile', 'afsac' ),
		'text'    => esc_html__( 'Grâce à son appartenance aux réseaux OACI, l’AFSAC propose des solutions de formation couvrant un large éventail de domaines techniques de l’aviation civile, au-delà de la sûreté aérienne.', 'afsac' ),
	),
	array(
		'icon'    => 'support',
		'eyebrow' => esc_html__( 'Accompagnement sur mesure', 'afsac' ),
		'title'   => esc_html__( 'Un accompagnement personnalisé des États et organisations', 'afsac' ),
		'text'    => esc_html__( 'Nous développons des solutions adaptées aux besoins spécifiques de nos partenaires : formations dédiées, assistance technique, renforcement des capacités et accompagnement institutionnel.', 'afsac' ),
	),
	array(
		'icon'    => 'globe',
		'eyebrow' => esc_html__( 'Partage des connaissances', 'afsac' ),
		'title'   => esc_html__( 'Une approche internationale basée sur le partage des connaissances', 'afsac' ),
		'text'    => esc_html__( 'Dans le cadre de l’initiative OACI « No Country Left Behind », l’AFSAC contribue au transfert d’expertise et au développement durable des compétences aéronautiques à travers le monde.', 'afsac' ),
	),
);

// Tracés SVG mono-trait (24×24, currentColor). Décoratifs.
$afsac_why_icons = array(
	'shield'  => '<path d="M12 3l7 3v5c0 4.2-3 7.4-7 8.6-4-1.2-7-4.4-7-8.6V6z"/><path d="M9 12l2 2 4-4.2"/>',
	'award'   => '<circle cx="12" cy="9" r="5.5"/><path d="M9 13.5l-1.5 7L12 18l4.5 2.5-1.5-7"/><path d="M9.6 9l1.6 1.6L14.6 7"/>',
	'users'   => '<circle cx="9.5" cy="8" r="3.3"/><path d="M3.5 19.5c0-3.2 2.7-5.4 6-5.4s6 2.2 6 5.4"/><circle cx="17.5" cy="9.5" r="2.3"/><path d="M17 14.2c2.1.3 3.9 2.1 3.9 4.6"/>',
	'layers'  => '<path d="M12 3l9 5-9 5-9-5z"/><path d="M3 12l9 5 9-5"/><path d="M3 16l9 5 9-5"/>',
	'support' => '<circle cx="10.5" cy="8" r="3.4"/><path d="M4 19.8c0-3.4 2.9-5.7 6.5-5.7 .8 0 1.6.1 2.3.35"/><path d="M15 17.6l2 2 4.2-4.4"/>',
	'globe'   => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3c2.7 2.5 4.2 5.7 4.2 9s-1.5 6.5-4.2 9c-2.7-2.5-4.2-5.7-4.2-9s1.5-6.5 4.2-9z"/>',
);

$afsac_uid    = wp_unique_id( 'afsac-why-' );
$afsac_total  = count( $afsac_why );
$afsac_emblem = get_theme_file_path( 'assets/images/oaci-emblem-white.webp' );
?>
<section class="afsac-section afsac-pourquoi" data-afsac-why>
	<span class="afsac-pourquoi__glow" aria-hidden="true"></span>

	<?php
	/*
	 * Filigrane de l'emblème, côté texte — même traitement que le « Mot du DG »
	 * (.afsac-director__watermark), mais en blanc puisque le fond est bleu.
	 */
	if ( file_exists( $afsac_emblem ) ) :
		?>
		<span class="afsac-pourquoi__watermark" aria-hidden="true">
			<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/oaci-emblem-white.webp' ) ); ?>" alt="" loading="lazy" decoding="async">
		</span>
		<?php
	endif;
	?>

	<div class="afsac-container afsac-pourquoi__inner">

		<?php /* Zone anneau — masquée sous 1200px (les flèches prennent le relais). */ ?>
		<div class="afsac-pourquoi__ring-zone">
			<span class="afsac-pourquoi__dashed" aria-hidden="true"></span>

			<?php if ( file_exists( $afsac_emblem ) ) : ?>
				<span class="afsac-pourquoi__emblem" aria-hidden="true">
					<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/oaci-emblem-white.webp' ) ); ?>" alt="" width="630" height="490" loading="lazy" decoding="async">
				</span>
			<?php endif; ?>

			<div class="afsac-pourquoi__ring" data-afsac-why-ring role="tablist" aria-orientation="vertical" aria-label="<?php esc_attr_e( 'Nos atouts', 'afsac' ); ?>">
				<?php foreach ( $afsac_why as $afsac_i => $afsac_item ) : ?>
					<button type="button"
						role="tab"
						class="afsac-pourquoi__item<?php echo 0 === $afsac_i ? ' is-active' : ''; ?>"
						id="<?php echo esc_attr( $afsac_uid . '-t' . $afsac_i ); ?>"
						aria-controls="<?php echo esc_attr( $afsac_uid . '-s' . $afsac_i ); ?>"
						aria-selected="<?php echo 0 === $afsac_i ? 'true' : 'false'; ?>"
						tabindex="<?php echo 0 === $afsac_i ? '0' : '-1'; ?>"
						data-index="<?php echo (int) $afsac_i; ?>">
						<span class="afsac-pourquoi__halo afsac-pourquoi__halo--1" aria-hidden="true"></span>
						<span class="afsac-pourquoi__halo afsac-pourquoi__halo--2" aria-hidden="true"></span>
						<span class="afsac-pourquoi__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" focusable="false"><?php echo $afsac_why_icons[ $afsac_item['icon'] ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?></svg>
						</span>
						<span class="screen-reader-text"><?php echo esc_html( $afsac_item['eyebrow'] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
		</div>

		<?php /* Colonne texte. */ ?>
		<div class="afsac-pourquoi__text">

			<?php /* Chapô retiré à la demande du client (04/08/2026) : le titre suffit. */ ?>
			<div class="afsac-section__head afsac-reveal">
				<span class="afsac-eyebrow afsac-eyebrow--light"><?php esc_html_e( '', 'afsac' ); ?></span>
				<h2 class="afsac-pourquoi__title"><?php esc_html_e( 'Pourquoi choisir l’AFSAC ?', 'afsac' ); ?></h2>
			</div>

			<div class="afsac-pourquoi__slides">
				<?php foreach ( $afsac_why as $afsac_i => $afsac_item ) : ?>
					<article class="afsac-pourquoi__slide<?php echo 0 === $afsac_i ? ' is-active' : ''; ?>"
						id="<?php echo esc_attr( $afsac_uid . '-s' . $afsac_i ); ?>"
						role="tabpanel"
						tabindex="<?php echo 0 === $afsac_i ? '0' : '-1'; ?>"
						aria-labelledby="<?php echo esc_attr( $afsac_uid . '-t' . $afsac_i ); ?>">
						<p class="afsac-pourquoi__slide-eyebrow"><?php echo esc_html( $afsac_item['eyebrow'] ); ?></p>
						<h3 class="afsac-pourquoi__slide-title"><?php echo esc_html( $afsac_item['title'] ); ?></h3>
						<p class="afsac-pourquoi__slide-text"><?php echo esc_html( $afsac_item['text'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>

			<div class="afsac-pourquoi__nav">
				<button type="button" class="afsac-pourquoi__arrow afsac-pourquoi__arrow--prev" data-afsac-why-prev aria-label="<?php esc_attr_e( 'Atout précédent', 'afsac' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7"/></svg>
				</button>
				<span class="afsac-pourquoi__counter" aria-hidden="true">
					<span class="afsac-pourquoi__counter-num" data-afsac-why-current>01</span> / <?php echo esc_html( sprintf( '%02d', $afsac_total ) ); ?>
				</span>
				<?php /* Jauge de cadence — décor pur (le CSS la masque sans animations). */ ?>
				<span class="afsac-pourquoi__progress" aria-hidden="true">
					<span class="afsac-pourquoi__progress-bar" data-afsac-why-bar></span>
				</span>
				<button type="button" class="afsac-pourquoi__arrow afsac-pourquoi__arrow--next" data-afsac-why-next aria-label="<?php esc_attr_e( 'Atout suivant', 'afsac' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 5l7 7-7 7"/></svg>
				</button>
			</div>

		</div>

	</div>
</section>
