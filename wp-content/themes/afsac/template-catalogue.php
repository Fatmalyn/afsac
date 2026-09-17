<?php
/**
 * Template Name: Catalogue
 *
 * Index des 11 domaines OACI (taxonomie « afsac_area », termes parent = 0).
 * Chaque domaine renvoie vers son archive (taxonomy-afsac_area.php, à venir).
 * Page bilingue FR/EN dès l'origine : toutes les chaînes via __()/esc_html_e().
 *
 * Réutilise le système de design existant : hero illustré partagé, classes
 * d'intro du hub F&S (.afsac-fs-intro) et bandeau catalogue partagé. Données via
 * les helpers afsac-core (afsac_get_area_domains / afsac_get_area_accent_color /
 * afsac_get_area_course_count / afsac_get_hub_field). Aucune logique métier ici
 * (présentation uniquement).
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Fil d'Ariane Rank Math (même wrapper que le hub F&S / About ; rien si RM ne renvoie rien).
if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
	ob_start();
	rank_math_the_breadcrumbs();
	$afsac_crumbs = trim( ob_get_clean() );
	if ( '' !== $afsac_crumbs ) {
		echo '<div class="afsac-breadcrumb"><div class="afsac-container">' . $afsac_crumbs . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sortie Rank Math déjà assainie (wp_kses_post).
	}
}

// Domaines OACI de premier niveau, langue courante, ordre stable (nom A→Z).
$afsac_areas = function_exists( 'afsac_get_area_domains' ) ? afsac_get_area_domains() : array();

// Compteurs dynamiques pour l'intro : nombre de domaines = taille de la grille.
$afsac_domains = count( $afsac_areas );
$afsac_courses = function_exists( 'afsac_get_hub_field' ) ? (int) afsac_get_hub_field( 'afsac_course_count' ) : 0;

/*
 * 1. HERO — bandeau VIDÉO (demande client 02/09/2026).
 *
 * C'est l'ANCIEN bandeau de la page Contact — la variante nº 1 du bandeau
 * « calendrier » (monde pointé + avion + calendrier flottant) — que le client a
 * voulu conserver ici quand Contact a reçu son propre bandeau. Récupéré tel quel
 * sous `assets/video/catalogue.mp4`.
 *
 * ⚠️ Ses libellés de jours sont fautifs (« Tuoday », « Wesday », « Frisit »,
 * dimanche en double) : c'est précisément pour ça qu'il avait été écarté de la
 * page Calendrier. `dim: true` + `align: start` restent donc OBLIGATOIRES : la
 * colonne de texte couvre la zone fautive et le voile rend le reste illisible.
 */
$afsac_hero_eyebrow = function_exists( 'get_field' ) ? (string) get_field( 'afsac_page_eyebrow' ) : '';
if ( '' === $afsac_hero_eyebrow ) {
	$afsac_hero_eyebrow = __( 'Notre catalogue', 'afsac' );
}
$afsac_hero_chapo = function_exists( 'get_field' ) ? (string) get_field( 'afsac_page_chapo' ) : '';
if ( '' === $afsac_hero_chapo ) {
	$afsac_hero_chapo = __( 'TRAINAIR PLUS & AVSEC, en français et en anglais.', 'afsac' );
}
get_template_part(
	'template-parts/shared/video-hero',
	null,
	array(
		'layout'    => 'overlay',
		'align'     => 'start',
		'dim'       => true,
		'video_src' => get_theme_file_uri( 'assets/video/catalogue.mp4' ),
		'poster'    => get_theme_file_uri( 'assets/images/catalogue-hero-poster.jpg' ),
		'eyebrow'   => $afsac_hero_eyebrow,
		/*
		 * Le H1 ne mentionne PLUS « 11 domaines OACI » (demande client) : les 11
		 * domaines structurent TRAINAIR PLUS uniquement, alors que ce catalogue
		 * couvre TRAINAIR PLUS *et* AVSEC. Le chiffre reste là où il est exact :
		 * dans l'onglet TRAINAIR (ses chiffres + le titre de sa grille).
		 */
		'title'     => __( 'Catalogue des formations OACI', 'afsac' ),
		'lead'      => $afsac_hero_chapo,
		/*
		 * Pas de statistiques ici (retour client : « trop de scroll, trop de
		 * répétition ») : elles faisaient doublon avec les chiffres des deux
		 * bannières de programme, et ces chiffres appartiennent aux programmes,
		 * pas à la page.
		 */
	)
);
?>

<main id="primary" class="afsac-catalogue-page">

	<?php
	/*
	 * SÉLECTEUR DE PROGRAMME (onglets).
	 *
	 * Retour client : empilés verticalement, les visiteurs voyaient TRAINAIR PLUS
	 * et n'arrivaient jamais jusqu'à AVSEC. Le problème est structurel, pas une
	 * question de padding : tant qu'AVSEC est SOUS TRAINAIR, il reste enterré.
	 * Les deux programmes sont donc désormais deux onglets côte à côte, à poids
	 * strictement égal — zéro scroll pour atteindre AVSEC, et la page ne fait plus
	 * que la hauteur d'un seul programme.
	 *
	 * Les DEUX panneaux sont rendus côté serveur (bon pour le SEO, et sans JS les
	 * deux restent visibles : c'est le JS qui masque l'inactif). Lien profond
	 * possible via #trainair / #avsec.
	 */
	$afsac_archive_url = get_post_type_archive_link( 'afsac_formation' );
	if ( ! $afsac_archive_url ) {
		$afsac_archive_url = home_url( '/' );
	}
	?>

	<div class="afsac-progtabs" data-prog-tabs>
		<div class="afsac-container">

			<?php /* Consigne explicite : sans elle, deux onglets se lisent comme « un titre + un titre grisé ». */ ?>
			<p class="afsac-progtabs__hint">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 8h13M17 5l3 3-3 3"/><path d="M17 16H4M7 13l-3 3 3 3"/></svg>
				<?php esc_html_e( 'Deux programmes — cliquez pour basculer', 'afsac' ); ?>
			</p>

			<div class="afsac-progtabs__nav" role="tablist" aria-label="<?php esc_attr_e( 'Choisir un programme', 'afsac' ); ?>">
				<button type="button" class="afsac-progtabs__tab afsac-progtabs__tab--trainair is-active"
					role="tab" id="afsac-tab-trainair" aria-controls="afsac-panel-trainair" aria-selected="true"
					data-prog-tab="trainair">
					<span class="afsac-progtabs__name">TRAINAIR PLUS</span>
					<span class="afsac-progtabs__sub"><?php esc_html_e( 'Les 11 domaines aéronautiques OACI', 'afsac' ); ?></span>
				</button>
				<button type="button" class="afsac-progtabs__tab afsac-progtabs__tab--avsec"
					role="tab" id="afsac-tab-avsec" aria-controls="afsac-panel-avsec" aria-selected="false"
					data-prog-tab="avsec" tabindex="-1">
					<span class="afsac-progtabs__name">AVSEC</span>
					<span class="afsac-progtabs__sub"><?php esc_html_e( 'Sûreté de l’aviation · FR · EN', 'afsac' ); ?></span>
				</button>
			</div>
		</div>
	</div>

	<div class="afsac-progtabs__panel" id="afsac-panel-trainair" role="tabpanel" aria-labelledby="afsac-tab-trainair" data-prog-panel="trainair">

	<?php
	/*
	 * 2. BLOC TRAINAIR PLUS — bannière identitaire (navy + or) puis SES 11 domaines.
	 * Le client a précisé que les 11 domaines OACI relèvent de TRAINAIR PLUS : la
	 * grille est donc explicitement rattachée à ce programme.
	 */
	get_template_part(
		'template-parts/page-formations/programme-banner',
		null,
		array(
			'variant' => 'trainair',
			'eyebrow' => __( 'Programme mondial OACI', 'afsac' ),
			'name'    => 'TRAINAIR PLUS',
			'desc'    => __( 'Cours standardisés (STP) développés selon la méthodologie de l’OACI et structurés en 11 domaines aéronautiques. Instructeurs certifiés, certificats reconnus à l’international.', 'afsac' ),
			'figures' => array(
				array(
					'value' => number_format_i18n( $afsac_domains ),
					'label' => __( 'Domaines OACI', 'afsac' ),
				),
				array(
					'value' => number_format_i18n( $afsac_courses ),
					'label' => __( 'Cours standardisés', 'afsac' ),
				),
				array(
					'value' => 'STP',
					'label' => __( 'Méthodologie', 'afsac' ),
				),
			),
			'cta'     => array(
				'label' => __( 'Tous les cours TRAINAIR PLUS', 'afsac' ),
				'url'   => add_query_arg( array( 'famille' => 'trainair' ), $afsac_archive_url ),
			),
		)
	);
	?>

	<?php /* 3. LES 11 DOMAINES DE TRAINAIR PLUS. */ ?>
	<section class="afsac-catalogue">
		<div class="afsac-container">
			<?php
			get_template_part(
				'template-parts/shared/section-head',
				null,
				array(
					'eyebrow' => __( 'Par domaine', 'afsac' ),
					/* translators: %s: number of ICAO areas. */
					'title'   => sprintf( __( 'Les %s domaines aéronautiques de TRAINAIR PLUS', 'afsac' ), number_format_i18n( $afsac_domains ) ),
					// Chapô retiré : le code couleur se voit, il n'a pas besoin d'être annoncé.
					'center'  => true,
				)
			);
			?>
			<?php if ( ! empty( $afsac_areas ) ) : ?>
				<div class="afsac-catalogue__grid afsac-stagger">
					<?php
					foreach ( $afsac_areas as $afsac_area ) :
						$afsac_link = get_term_link( $afsac_area );
						if ( is_wp_error( $afsac_link ) ) {
							continue;
						}

						// Accent : méta de terme validée, sinon '' → repli navy géré en CSS.
						$afsac_accent = function_exists( 'afsac_get_area_accent_color' )
							? afsac_get_area_accent_color( $afsac_area )
							: '';

						// Compteur de cours (sous-domaines inclus, TRAINAIR PLUS des deux langues).
						$afsac_count = function_exists( 'afsac_get_area_course_count' )
							? afsac_get_area_course_count( $afsac_area )
							: 0;

						/*
						 * Aplat de couleur (demande client : « exactement comme la
						 * capture d'icao.int »). Le fond est la couleur OFFICIELLE du
						 * domaine, le texte est choisi par contraste — la palette OACI
						 * va du navy #39474e au jaune #fab50f, un blanc uniforme serait
						 * illisible sur les teintes claires.
						 */
						$afsac_fg = ( '' !== $afsac_accent ) ? afsac_readable_text_color( $afsac_accent ) : '';

						$afsac_card_style = '';
						if ( '' !== $afsac_accent ) {
							$afsac_card_style = sprintf(
								'--afsac-area-accent: %1$s; --afsac-area-fg: %2$s;',
								$afsac_accent,
								$afsac_fg
							);
						}
						?>
						<a
							class="afsac-catalogue__card afsac-reveal"
							href="<?php echo esc_url( $afsac_link ); ?>"
							<?php if ( '' !== $afsac_card_style ) : ?>style="<?php echo esc_attr( $afsac_card_style ); ?>"<?php endif; ?>
						>
							<h2 class="afsac-catalogue__card-title"><?php echo esc_html( $afsac_area->name ); ?></h2>

							<?php if ( $afsac_count > 0 ) : ?>
								<span class="afsac-catalogue__card-count">
									<?php
									printf(
										/* translators: %s: number of courses. */
										esc_html( _n( '%s cours', '%s cours', $afsac_count, 'afsac' ) ),
										esc_html( number_format_i18n( $afsac_count ) )
									);
									?>
								</span>
							<?php endif; ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="afsac-catalogue__empty"><?php esc_html_e( 'Le catalogue des domaines sera bientôt disponible.', 'afsac' ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	</div><?php /* fin du panneau TRAINAIR PLUS */ ?>

	<div class="afsac-progtabs__panel" id="afsac-panel-avsec" role="tabpanel" aria-labelledby="afsac-tab-avsec" data-prog-panel="avsec">

	<?php
	/*
	 * 4. BLOC AVSEC — bannière identitaire (bouclier + impulsions radar) puis le
	 * volet complet : thématiques de sûreté, catalogue cours/ateliers, sessions.
	 *
	 * Les chiffres sont RÉELS et viennent des helpers afsac_avsec_*() : ils
	 * décrivent désormais la STRUCTURE du programme (volume, typologie, axes)
	 * plutôt que ses langues — le trio « FR · EN » reste annoncé par l'onglet.
	 */
	$afsac_avsec_stats  = function_exists( 'afsac_avsec_stats' ) ? afsac_avsec_stats() : array( 'total' => 0, 'ateliers' => 0 );
	$afsac_avsec_themes = function_exists( 'afsac_avsec_themes' ) ? count( afsac_avsec_themes() ) : 0;

	get_template_part(
		'template-parts/page-formations/programme-banner',
		null,
		array(
			'variant' => 'avsec',
			'eyebrow' => __( 'Centre régional de formation à la sûreté · ASTC', 'afsac' ),
			'name'    => 'AVSEC',
			'desc'    => __( 'Le programme de sûreté de l’aviation civile de l’OACI, aligné sur l’Annexe 17 de la Convention de Chicago. De l’agent d’inspection filtrage au responsable d’un programme national : cours certifiants et ateliers, animés par des instructeurs agréés OACI, en français et en anglais.', 'afsac' ),
			'figures' => array(
				array(
					'value' => number_format_i18n( (int) $afsac_avsec_stats['total'] ),
					'label' => __( 'Cours & ateliers', 'afsac' ),
				),
				array(
					'value' => number_format_i18n( $afsac_avsec_themes ),
					'label' => __( 'Thématiques de sûreté', 'afsac' ),
				),
				array(
					'value' => number_format_i18n( 17 ),
					'label' => __( 'Annexe OACI', 'afsac' ),
				),
			),
			'cta'     => array(
				'label' => __( 'Tous le programme AVSEC', 'afsac' ),
				'url'   => add_query_arg( array( 'famille' => 'avsec' ), $afsac_archive_url ),
			),
		)
	);
	?>

	<?php get_template_part( 'template-parts/page-formations/avsec-showcase' ); ?>

	</div><?php /* fin du panneau AVSEC */ ?>

</main>

<?php
get_footer();
