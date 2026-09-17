<?php
/**
 * Accueil — section « Intro / proposition de valeur ».
 *
 * Mise en page 2 colonnes : diaporama de photos de sessions à gauche, texte à droite.
 * Le texte est découpé en 4 BLOCS et le diaporama en 11 photos réparties entre ces
 * blocs : quand la photo change, le bloc correspondant prend la main (fondu). Le
 * pilotage est dans assets/js/intro-slideshow.js — chaque slide porte `data-block`,
 * chaque bloc de texte aussi ; le JS n'a qu'à faire correspondre les deux.
 *
 * Sans JavaScript (gate `.afsac-js`) les 4 blocs restent empilés et lisibles, et la
 * 1re photo reste affichée : rien n'est perdu pour le référencement.
 *
 * Textes traduisibles via gettext (domaine afsac). Le CTA pointe vers la page
 * « Qui sommes-nous » (Customizer), résolue par Polylang.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// CTA : page « Qui sommes-nous » (Customizer), traduite par Polylang.
$afsac_about_id = (int) get_theme_mod( 'afsac_page_about', 0 );
if ( $afsac_about_id && function_exists( 'pll_get_post' ) ) {
	$afsac_translated = pll_get_post( $afsac_about_id );
	if ( $afsac_translated ) {
		$afsac_about_id = (int) $afsac_translated;
	}
}
$afsac_about_url = $afsac_about_id ? get_permalink( $afsac_about_id ) : home_url( '/' );

$afsac_allowed = array( 'strong' => array() );

/*
 * Les 4 blocs de discours, chacun accompagné de ses photos. Le nombre de photos par
 * bloc est proportionnel à la longueur du texte (~6 s par photo) pour laisser le temps
 * de lire : 2 / 4 / 2 / 3. Uniquement des PHOTOS de sessions : la maquette 3D du
 * bâtiment est présentée dans « Qui sommes-nous », où elle a la place d’être vue
 * en entier (ici le cadrage en cover la rognait).
 */
$afsac_blocks = array(
	array(
		'text'   => __( 'L’AFSAC – ICAO ASTC Tunis est un Centre international d’excellence de l’Organisation de l’Aviation Civile Internationale (OACI), bénéficiant d’un positionnement unique grâce à sa double reconnaissance officielle en tant que <strong>Centre Régional de Formation à la Sûreté de l’Aviation Civile (ICAO Aviation Security Training Centre – ASTC)</strong> et <strong>membre du Programme mondial ICAO TRAINAIR PLUS (Global Aviation Training – GAT)</strong>.', 'afsac' ),
		'photos' => array(
			array(
				'file' => 'assets/images/intro-photo-01.webp',
				'alt'  => __( 'Participants et équipe de l’AFSAC réunis dans le hall du Centre régional de formation à la sûreté de l’aviation de l’OACI de Tunis', 'afsac' ),
			),
			
		),
	),
	array(
		'text'   => __( 'Cette double reconnaissance confère à l’AFSAC la capacité de concevoir, développer et dispenser des <strong>programmes de formation certifiants</strong> couvrant les différents domaines techniques de l’aviation civile relevant des <strong>19 Annexes de la Convention de Chicago</strong>, en s’appuyant sur les mallettes pédagogiques officielles de l’OACI (Standardized Training Packages – STPs, Competency-Based Training Packages – CBTs et TRAINAIR PLUS Training Packages – TPPs), conformément aux Standards et Pratiques Recommandées (SARPs), aux Procédures pour les services de navigation aérienne (PANS) et aux meilleures pratiques internationales.', 'afsac' ),
		'photos' => array(
			array(
				'file' => 'assets/images/intro-photo-04.webp',
				'alt'  => __( 'Stagiaires présentant leur certificat à l’issue d’une session de formation à la sûreté de l’aviation', 'afsac' ),
			),
			array(
				'file' => 'assets/images/intro-photo-05.webp',
				'alt'  => __( 'Photo de clôture d’une promotion devant le mur du Centre régional de formation de l’OACI de Tunis', 'afsac' ),
			),
			array(
				'file' => 'assets/images/intro-photo-06.webp',
				'alt'  => __( 'Trois stagiaires présentant leur certificat de formation à la sûreté de l’aviation', 'afsac' ),
			),
			array(
				'file' => 'assets/images/intro-photo-12.webp',
				'alt'  => __( 'Remise du drapeau de l’OACI lors de la réunion Course Developers and Instructors Standardization (CDI/STD) de l’OACI à Tunis, décembre 2017', 'afsac' ),
			),
		),
	),
	array(
		'text'   => __( 'Au-delà de son rôle de centre de formation, l’AFSAC accompagne les <strong>États, les Autorités de l’Aviation Civile, les exploitants d’aéroports, les prestataires de services de navigation aérienne, les compagnies aériennes et les organisations internationales</strong> dans la mise en œuvre des normes et exigences de l’OACI, à travers des solutions intégrées de formation, assistance technique, coaching, conseil, audits, renforcement des capacités, développement des compétences, ingénierie pédagogique et appui institutionnel.', 'afsac' ),
		'photos' => array(
			array(
				'file' => 'assets/images/intro-photo-07.webp',
				'alt'  => __( 'Participants d’un cours pour inspecteurs de la sûreté de l’aviation avec leur attestation', 'afsac' ),
			),
			array(
				'file' => 'assets/images/intro-photo-08.webp',
				'alt'  => __( 'Participants d’un atelier sur le contrôle qualité de la sûreté de l’aviation civile', 'afsac' ),
			),
		),
	),
	array(
		'text'   => __( 'En parfaite cohérence avec l’initiative mondiale de l’OACI <strong>« No Country Left Behind »</strong>, l’AFSAC mobilise son réseau d’experts internationaux afin d’accompagner les États dans le développement durable de leurs capacités nationales, le transfert de connaissances et d’expertise ainsi que le renforcement des compétences de leur capital humain. À travers ses programmes de formation, ses missions d’assistance technique et son accompagnement stratégique, l’AFSAC contribue à l’amélioration des systèmes nationaux de supervision, à la préparation aux audits internationaux et à la modernisation des structures de l’aviation civile, en faveur d’un transport aérien plus sûr, plus sécurisé, plus performant et durable.', 'afsac' ),
		'photos' => array(
			array(
				'file' => 'assets/images/intro-photo-03.webp',
				'alt'  => __( 'Stagiaires venus de la région déployant le drapeau de l’OACI à l’ouverture de deux sessions de formation', 'afsac' ),
			),
			array(
				'file' => 'assets/images/intro-photo-09.webp',
				'alt'  => __( 'Instructeurs certifiés en sûreté de l’aviation civile à l’issue de leur formation de formateurs', 'afsac' ),
			),
			array(
				'file' => 'assets/images/intro-photo-10.webp',
				'alt'  => __( 'Stagiaires déployant le drapeau de l’OACI à l’issue d’un cours pour inspecteurs de la sûreté de l’aviation', 'afsac' ),
			),
		),
	),
);

/*
 * Aplatit les blocs en une liste de slides (une par photo présente sur le disque) et
 * ne garde que les blocs qui conservent au moins une photo, pour que la correspondance
 * photo <-> texte reste vraie même si un visuel manque.
 */
$afsac_slides = array();
$afsac_texts  = array();
foreach ( $afsac_blocks as $afsac_block ) {
	$afsac_kept = array();
	foreach ( $afsac_block['photos'] as $afsac_photo ) {
		if ( file_exists( get_theme_file_path( $afsac_photo['file'] ) ) ) {
			$afsac_kept[] = $afsac_photo;
		}
	}
	if ( ! $afsac_kept ) {
		continue;
	}
	$afsac_index   = count( $afsac_texts );
	$afsac_texts[] = $afsac_block['text'];
	foreach ( $afsac_kept as $afsac_photo ) {
		$afsac_photo['block'] = $afsac_index;
		$afsac_slides[]       = $afsac_photo;
	}
}

/*
 * Repli : aucun visuel disponible -> pas de diaporama, donc pas de rotation. Les 4
 * blocs sont alors simplement empilés (comme sans JavaScript).
 */
$afsac_rotating = ! empty( $afsac_slides );
if ( ! $afsac_texts ) {
	$afsac_texts = wp_list_pluck( $afsac_blocks, 'text' );
}
?>
<section class="afsac-section afsac-intro">
	<div class="afsac-container afsac-intro__grid<?php echo empty( $afsac_slides ) ? ' afsac-intro__grid--solo' : ''; ?>">

		<?php if ( ! empty( $afsac_slides ) ) : ?>
		<div class="afsac-intro__media afsac-reveal">
			<div class="afsac-slideshow" data-afsac-slideshow data-delay="6000" role="group" aria-roledescription="<?php esc_attr_e( 'diaporama', 'afsac' ); ?>" aria-label="<?php esc_attr_e( 'Sessions de formation à l’AFSAC – ICAO ASTC Tunis', 'afsac' ); ?>">
				<div class="afsac-slideshow__track">
					<?php foreach ( $afsac_slides as $afsac_i => $afsac_slide ) : ?>
					<figure class="afsac-slideshow__slide<?php echo 0 === $afsac_i ? ' is-active' : ''; ?>" data-block="<?php echo esc_attr( (string) $afsac_slide['block'] ); ?>">
						<img src="<?php echo esc_url( get_theme_file_uri( $afsac_slide['file'] ) ); ?>" alt="<?php echo esc_attr( $afsac_slide['alt'] ); ?>" width="1120" height="840" loading="<?php echo 0 === $afsac_i ? 'eager' : 'lazy'; ?>" decoding="async"<?php echo 0 === $afsac_i ? '' : ' fetchpriority="low"'; ?>>
					</figure>
					<?php endforeach; ?>
				</div>
				<?php if ( count( $afsac_slides ) > 1 ) : ?>
				<div class="afsac-slideshow__dots" role="tablist" aria-label="<?php esc_attr_e( 'Choisir une photo', 'afsac' ); ?>">
					<?php
					$afsac_current_block = null;
					foreach ( $afsac_slides as $afsac_i => $afsac_slide ) :
						// Un groupe de puces par bloc de texte : les 4 temps du discours restent lisibles.
						if ( $afsac_slide['block'] !== $afsac_current_block ) :
							if ( null !== $afsac_current_block ) :
								?>
								</span>
								<?php
							endif;
							$afsac_current_block = $afsac_slide['block'];
							?>
							<span class="afsac-slideshow__dotgroup">
							<?php
						endif;
						?>
						<button type="button" class="afsac-slideshow__dot<?php echo 0 === $afsac_i ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo 0 === $afsac_i ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d = numéro de la photo. */ __( 'Photo %d', 'afsac' ), $afsac_i + 1 ) ); ?>"></button>
						<?php
					endforeach;
					if ( null !== $afsac_current_block ) :
						?>
						</span>
						<?php
					endif;
					?>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<div class="afsac-intro__content afsac-reveal">
			<span class="afsac-eyebrow"><?php esc_html_e( 'AFSAC – ICAO ASTC Tunis', 'afsac' ); ?></span>
			<h2 class="afsac-intro__title"><?php esc_html_e( 'Votre partenaire stratégique pour le développement des capacités de l’aviation civile', 'afsac' ); ?></h2>

			<div class="afsac-intro__body afsac-intro__blocks<?php echo $afsac_rotating ? ' afsac-intro__blocks--rotating' : ''; ?>">
				<?php foreach ( $afsac_texts as $afsac_i => $afsac_text ) : ?>
				<?php // `aria-hidden` est posé par le JS (syncBlock) : sans JavaScript les 4 blocs restent visibles ET annoncés. ?>
				<p class="afsac-intro__block<?php echo ( $afsac_rotating && 0 === $afsac_i ) ? ' is-active' : ''; ?>" data-block="<?php echo esc_attr( (string) $afsac_i ); ?>"><?php echo wp_kses( $afsac_text, $afsac_allowed ); ?></p>
				<?php endforeach; ?>
			</div>

			<div class="afsac-intro__actions">
				<a class="afsac-link" href="<?php echo esc_url( $afsac_about_url ); ?>">
					<?php esc_html_e( 'Découvrir l’AFSAC', 'afsac' ); ?>
				</a>
			</div>
		</div>

	</div>
</section>
