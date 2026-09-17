<?php
/**
 * Catalogue — volet AVSEC : les deux formats (cours / ateliers), puis sessions.
 *
 * Retour client (06/08/2026) : le volet ouvrait sur DEUX sections d'entrée — les
 * 6 thématiques de sûreté en aplats, puis le catalogue complet filtrable. Trop de
 * portes pour une seule question : « cours ou atelier ? ». Les deux sections sont
 * remplacées par UNE section coupée en deux, un format par moitié (illustration +
 * texte qui explique ce que le format apporte), chacune ouvrant SA liste via
 * « Voir plus ».
 *
 * Retour client (07/08/2026) : « Voir plus » DÉPLIAIT la liste sous la section ;
 * le client la veut sur une PAGE À PART, comme un domaine TRAINAIR PLUS. Les deux
 * boutons sont donc des liens vers ?famille=avsec&format=… — page servie par
 * archive-afsac_formation.php. Les listes repliées et avsec-catalogue.js ont
 * disparu avec ce changement. Depuis le 04/09/2026, `format` ne choisit plus un
 * gabarit : il pré-sélectionne le filtre « Type » de la liste.
 *
 * Retour client (07/08/2026, plus tard) : le texte de référence de l'OACI (ASTP,
 * ateliers) avait d'abord été posé dans une SECTION à part — « ça devient trop de
 * texte ». Il est désormais REPLIÉ dans les deux cartes, sous l'image, derrière un
 * « Lire la suite » : la page reste courte, et chaque texte est là où on le
 * cherche — dans la carte du format qu'il décrit. Repli en <details> natif : rien
 * à charger, rien à gater, et le contenu reste indexable.
 *
 * Trois sections :
 *   1. le cadre international (Annexe 17 / Annexe 9, réseau ASTC) — avsec-cadre ;
 *   2. les deux formats, côte à côte, avec leur texte de référence replié ;
 *   3. les prochaines sessions programmées (dates réelles du CPT session).
 *
 * La typologie (cours / atelier) et la thématique restent DÉRIVÉES de la clé
 * d'import par les helpers afsac_avsec_*() du plugin : aucune logique métier ici.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Garde : sans le plugin, la page reste valide (le volet disparaît simplement).
if ( ! function_exists( 'afsac_avsec_get_courses' ) ) {
	return;
}

$afsac_courses = afsac_avsec_get_courses();
$afsac_stats   = afsac_avsec_stats( $afsac_courses );

$afsac_archive = get_post_type_archive_link( 'afsac_formation' );
if ( ! $afsac_archive ) {
	$afsac_archive = home_url( '/' );
}
$afsac_avsec_all = add_query_arg( array( 'famille' => 'avsec' ), $afsac_archive );

/*
 * Les deux formats. `kind` reprend À L'IDENTIQUE la valeur rendue par
 * afsac_avsec_kind_of() : c'est la valeur attendue par la page de liste dans
 * ?format=, une clé d'affichage distincte n'y filtrerait rien.
 */
$afsac_formats = array(
	array(
		'kind'  => 'cours',
		'label' => __( 'Certificat OACI', 'afsac' ),
		'title' => __( 'Cours certifiants', 'afsac' ),
		'count' => (int) $afsac_stats['cours'],
		'text'  => __( 'Un cours qualifie une FONCTION : agent d’inspection filtrage, superviseur, inspecteur national, instructeur. Il se termine par une évaluation et délivre un certificat de l’OACI, reconnu à l’international.', 'afsac' ),
		'items' => array(
			__( 'Évaluation finale et certificat nominatif de l’OACI', 'afsac' ),
			__( 'De 5 à 10 jours, en français ou en anglais', 'afsac' ),
			__( 'Animé par des instructeurs agréés OACI', 'afsac' ),
		),
		/*
		 * Texte de référence de l'OACI, replié : il n'apparaît qu'au clic sur
		 * « Lire la suite ». C'est le contenu fourni par le client, mot pour mot.
		 */
		'more'  => array(
			'title' => __( 'Les trousses de formation à la sûreté de l’aviation civile de l’OACI (ASTP)', 'afsac' ),
			'paras' => array(
				__( 'Afin de soutenir les États dans le développement des compétences nécessaires à l’application efficace des exigences internationales, l’OACI élabore et actualise des trousses de formation spécialisées en sûreté de l’aviation civile (Aviation Security Training Packages – ASTPs).', 'afsac' ),
				__( 'Ces programmes de formation sont conçus selon une approche basée sur les compétences et répondent aux différents niveaux de responsabilité impliqués dans la gestion et la mise en œuvre de la sûreté aérienne.', 'afsac' ),
			),
			'label' => __( 'Ils couvrent notamment :', 'afsac' ),
			'items' => array(
				__( 'La formation de base du personnel de sûreté de l’aviation civile', 'afsac' ),
				__( 'La formation des instructeurs nationaux en sûreté de l’aviation civile', 'afsac' ),
				__( 'La formation des superviseurs de sûreté aéroportuaire', 'afsac' ),
				__( 'La sûreté du fret et de la poste aérienne', 'afsac' ),
				__( 'La formation des inspecteurs nationaux de sûreté de l’aviation civile', 'afsac' ),
				__( 'La formation des gestionnaires de sûreté de l’aviation civile', 'afsac' ),
				__( 'La détection des comportements suspects', 'afsac' ),
			),
			'outro' => __( 'Ces formations permettent aux participants d’acquérir les connaissances, compétences et aptitudes nécessaires pour assurer la mise en œuvre des mesures préventives, réaliser les activités de contrôle qualité, conduire les inspections, gérer les programmes de sûreté et développer les capacités nationales de formation, conformément aux exigences de l’Annexe 17 et aux orientations établies par l’OACI.', 'afsac' ),
		),
	),
	array(
		'kind'  => 'atelier',
		'label' => __( 'Livrable pour l’État', 'afsac' ),
		'title' => __( 'Ateliers OACI', 'afsac' ),
		'count' => (int) $afsac_stats['ateliers'],
		'text'  => __( 'Un atelier réunit les acteurs d’un même dispositif pour produire un LIVRABLE : programme national de sûreté, programme de contrôle qualité, plan de gestion de crise, système de certification. Le travail est collectif et directement applicable.', 'afsac' ),
		'items' => array(
			__( 'Un livrable rédigé pendant l’atelier', 'afsac' ),
			__( 'Autorités, exploitants d’aéroport et compagnies', 'afsac' ),
			__( 'Animé par des experts OACI', 'afsac' ),
		),
		'more'  => array(
			'title' => __( 'Ateliers de l’OACI sur la sûreté de l’aviation civile', 'afsac' ),
			'paras' => array(
				__( 'En complément des programmes de formation, l’OACI développe des ateliers spécialisés en sûreté de l’aviation civile destinés à accompagner les États et les organisations dans l’amélioration de leurs dispositifs réglementaires, opérationnels et institutionnels.', 'afsac' ),
				__( 'Ces ateliers adoptent une approche pratique et interactive permettant aux participants d’analyser leurs systèmes existants, d’identifier les écarts éventuels, de partager les meilleures pratiques internationales et de développer des solutions adaptées à leurs environnements nationaux et organisationnels.', 'afsac' ),
			),
			'label' => __( 'Ils portent notamment sur :', 'afsac' ),
			'items' => array(
				__( 'La culture de sûreté de l’aviation civile', 'afsac' ),
				__( 'Le Programme national de sûreté de l’aviation civile (PNSAC)', 'afsac' ),
				__( 'Le Programme de sûreté d’aéroport', 'afsac' ),
				__( 'Le Programme national de contrôle qualité de la sûreté de l’aviation civile', 'afsac' ),
				__( 'Le Programme national de formation à la sûreté de l’aviation civile', 'afsac' ),
				__( 'La certification des personnels et des intervenants en sûreté', 'afsac' ),
				__( 'La gestion des risques liés à la sûreté', 'afsac' ),
				__( 'La gestion des crises et des situations d’urgence', 'afsac' ),
				__( 'La prévention et la gestion du risque interne', 'afsac' ),
			),
			// Ce paragraphe compare les deux formats : il ferme la carte « atelier ».
			'outro' => __( 'Contrairement aux formations classiques, principalement orientées vers l’acquisition de connaissances et de compétences individuelles, les ateliers de l’OACI privilégient une approche collaborative fondée sur l’analyse de cas pratiques, l’évaluation des dispositifs existants, l’utilisation d’outils méthodologiques et l’élaboration de plans d’action applicables au niveau national ou organisationnel.', 'afsac' ),
		),
	),
);
?>

<?php /* ============ 1. LE CADRE INTERNATIONAL (ÉDITORIAL) ============ */ ?>
<?php get_template_part( 'template-parts/page-formations/avsec-cadre' ); ?>

<?php /* ============ 2. DEUX FORMATS : COURS / ATELIERS ============ */ ?>
<section class="afsac-section afsac-avsec-duo">
	<div class="afsac-container">
		<?php
		get_template_part(
			'template-parts/shared/section-head',
			null,
			array(
				'eyebrow' => __( 'Le catalogue', 'afsac' ),
				'title'   => __( 'Cours certifiants & ateliers OACI', 'afsac' ),
				'intro'   => __( 'Deux formats, deux finalités : le cours qualifie une personne, l’atelier produit un document de référence. Choisissez le vôtre, puis ouvrez la liste des formations.', 'afsac' ),
				'center'  => true,
			)
		);
		?>

		<div class="afsac-avsec-duo__grid afsac-stagger">
			<?php foreach ( $afsac_formats as $afsac_fmt ) : ?>
				<article class="afsac-avsec-duo__item afsac-reveal">

					<div class="afsac-avsec-duo__media afsac-avsec-duo__media--<?php echo esc_attr( $afsac_fmt['kind'] ); ?>">
						<span class="afsac-eyebrow afsac-avsec-duo__media-label"><?php echo esc_html( $afsac_fmt['label'] ); ?></span>
						<span class="afsac-avsec-duo__media-count">
							<?php
							printf(
								/* translators: %s: number of courses. */
								esc_html( _n( '%s formation', '%s formations', $afsac_fmt['count'], 'afsac' ) ),
								esc_html( number_format_i18n( $afsac_fmt['count'] ) )
							);
							?>
						</span>
					</div>

					<h3 class="afsac-avsec-duo__title"><?php echo esc_html( $afsac_fmt['title'] ); ?></h3>
					<p class="afsac-avsec-duo__text"><?php echo esc_html( $afsac_fmt['text'] ); ?></p>

					<ul class="afsac-avsec-duo__list">
						<?php foreach ( $afsac_fmt['items'] as $afsac_line ) : ?>
							<li><?php echo esc_html( $afsac_line ); ?></li>
						<?php endforeach; ?>
					</ul>

					<?php
					/*
					 * REPLI — le texte de référence de l'OACI. Fermé par défaut : la
					 * carte reste courte pour qui vient juste choisir un format. Le
					 * complément lecteur d'écran distingue les deux « Lire la suite »
					 * de la section, qui portent autrement le même libellé.
					 */
					?>
					<details class="afsac-more afsac-avsec-duo__more">
						<summary class="afsac-more__toggle">
							<span class="afsac-more__label afsac-more__label--closed"><?php esc_html_e( 'Lire la suite', 'afsac' ); ?></span>
							<span class="afsac-more__label afsac-more__label--open"><?php esc_html_e( 'Réduire', 'afsac' ); ?></span>
							<span class="screen-reader-text">&nbsp;&mdash;&nbsp;<?php echo esc_html( $afsac_fmt['title'] ); ?></span>
							<svg class="afsac-more__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6 9l6 6 6-6"/></svg>
						</summary>

						<div class="afsac-more__body">
							<h4 class="afsac-avsec-duo__more-title"><?php echo esc_html( $afsac_fmt['more']['title'] ); ?></h4>

							<?php foreach ( $afsac_fmt['more']['paras'] as $afsac_para ) : ?>
								<p class="afsac-avsec-duo__more-text"><?php echo esc_html( $afsac_para ); ?></p>
							<?php endforeach; ?>

							<p class="afsac-avsec-duo__more-label"><?php echo esc_html( $afsac_fmt['more']['label'] ); ?></p>
							<ul class="afsac-avsec-duo__list afsac-avsec-duo__list--more">
								<?php foreach ( $afsac_fmt['more']['items'] as $afsac_topic ) : ?>
									<li><?php echo esc_html( $afsac_topic ); ?></li>
								<?php endforeach; ?>
							</ul>

							<p class="afsac-avsec-duo__more-text"><?php echo esc_html( $afsac_fmt['more']['outro'] ); ?></p>
						</div>
					</details>

					<?php
					/*
					 * LIEN vers la page de liste du format (aucun JS) : le complément
					 * lecteur d'écran distingue les deux « Voir plus », qui portent
					 * autrement le même libellé.
					 */
					?>
					<a
						class="afsac-btn afsac-btn--accent afsac-avsec-duo__cta"
						href="<?php echo esc_url( add_query_arg( array( 'format' => $afsac_fmt['kind'] ), $afsac_avsec_all ) ); ?>"
					>
						<?php esc_html_e( 'Voir plus', 'afsac' ); ?>
						<span class="screen-reader-text">&nbsp;&mdash;&nbsp;<?php echo esc_html( $afsac_fmt['title'] ); ?></span>
						<span class="afsac-avsec-duo__cta-arrow" aria-hidden="true">&rarr;</span>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php /* Mot de fin du volet : une phrase, la mission de l'AFSAC dans le dispositif OACI. */ ?>
<section class="afsac-section afsac-avsec-mission">
	<div class="afsac-container">
		<p>
			<?php
			echo wp_kses(
				__( 'À travers son statut de Centre régional de formation à la sûreté de l’aviation civile de l’OACI, l’AFSAC – ICAO ASTC Tunis contribue au développement durable des compétences des professionnels de l’aviation civile, au partage de l’expertise internationale et au renforcement des capacités des États, conformément aux objectifs stratégiques de l’OACI et à l’initiative mondiale <strong>« Aucun État laissé de côté » (« No Country Left Behind »)</strong>.', 'afsac' ),
				array( 'strong' => array() )
			);
			?>
		</p>
	</div>
</section>

<?php
/* ============ 3. PROCHAINES SESSIONS ============ */
/*
 * Même carte que l'accueil (« Consulter nos formations à venir ») : vignette du cours,
 * éditeur, langues / durée / modalité, domaine — via afsac_build_course_card()
 * et le partiel home/course-card-icao. La seule addition est le bandeau de
 * SESSION (dates, lieu, statut) que ce partiel accepte en option.
 * 4 sessions = une grille 2×2, comme l'accueil.
 */
$afsac_sessions = function_exists( 'afsac_avsec_next_sessions' ) ? afsac_avsec_next_sessions( 4 ) : array();
if ( ! empty( $afsac_sessions ) && function_exists( 'afsac_build_course_card' ) ) :
	$afsac_cal = function_exists( 'afsac_get_calendar_url' ) ? afsac_get_calendar_url() : '';
	$afsac_ins = function_exists( 'afsac_get_inscription_url' ) ? afsac_get_inscription_url() : '';
	?>
	<section class="afsac-section afsac-avsec-next">
		<div class="afsac-container">

			<div class="afsac-formations-carousel__head afsac-reveal">
				<div class="afsac-formations-carousel__intro">
					<span class="afsac-eyebrow"><?php esc_html_e( 'Formations à venir', 'afsac' ); ?></span>
					<h2 class="afsac-formations-carousel__title"><?php esc_html_e( 'Prochaines sessions AVSEC', 'afsac' ); ?></h2>
				</div>
				<?php if ( '' !== $afsac_cal ) : ?>
					<a class="afsac-link" href="<?php echo esc_url( $afsac_cal ); ?>"><?php esc_html_e( 'Voir tout le calendrier', 'afsac' ); ?></a>
				<?php endif; ?>
			</div>

			<?php /* .afsac-reveal sur le CONTENEUR, comme l'accueil : ce volet démarre masqué (onglet inactif), une carte individuelle risquerait de rester à opacity 0. */ ?>
			<div class="afsac-fgrid__grid afsac-avsec-next__grid afsac-reveal afsac-reveal--fade">
				<?php
				foreach ( $afsac_sessions as $afsac_s ) :
					if ( ! $afsac_s['formation'] ) {
						continue; // Session orpheline : sans cours lié, la carte n'a rien à montrer.
					}

					// Minuit dans le fuseau du site, sinon la date bascule au lendemain.
					$afsac_d1 = afsac_date_from_ymd( (string) $afsac_s['debut'] );
					$afsac_d2 = afsac_date_from_ymd( (string) $afsac_s['fin'] );
					if ( ! $afsac_d1 ) {
						continue;
					}

					/*
					 * Plage compacte « 21 sept. – 2 oct. 2026 » : l'année n'est portée
					 * que par la borne finale, elle est toujours la même sur une session.
					 */
					$afsac_range = $afsac_d2
						? sprintf( '%s – %s', wp_date( 'j M', $afsac_d1->getTimestamp() ), wp_date( 'j M Y', $afsac_d2->getTimestamp() ) )
						: wp_date( 'j M Y', $afsac_d1->getTimestamp() );

					$afsac_card            = afsac_build_course_card( $afsac_s['formation']->ID );
					$afsac_card['session'] = array(
						'date'         => $afsac_range,
						'lieu'         => $afsac_s['lieu'],
						'statut'       => $afsac_s['statut'],
						'statut_label' => ( '' !== $afsac_s['statut'] ) ? afsac_session_statut_label( $afsac_s['statut'] ) : '',
					);

					get_template_part( 'template-parts/home/course-card-icao', null, $afsac_card );
				endforeach;
				?>
			</div>

			<div class="afsac-avsec-next__actions">
				<?php if ( '' !== $afsac_ins ) : ?>
					<a class="afsac-btn afsac-btn--accent" href="<?php echo esc_url( $afsac_ins ); ?>"><?php esc_html_e( 'Demander une inscription', 'afsac' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
endif;
