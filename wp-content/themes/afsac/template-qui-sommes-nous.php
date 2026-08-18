<?php
/**
 * Template Name: Qui sommes-nous
 *
 * Page « À propos de l'AFSAC ». Refonte (rapport client 19/06/2026 §5) :
 *   - la PHOTO DU DG a été retirée du hero (v0.6.4) ; elle reste dans « Le mot
 *     du DG », son emplacement éditorial naturel ;
 *   - les volets demandés sont des ONGLETS (même composant que le catalogue),
 *     et non des sections empilées : la page tient la hauteur d'un seul volet.
 *     Retour client : « trop de scroll ».
 *
 * Mise à jour 08/2026 — demande client :
 *   1. « Le mot du DG » passe en PREMIER onglet (c'était le 2e) ;
 *   2. le contenu de la brochure « Qui sommes-nous » est intégré à la page :
 *      il alimente deux nouveaux volets, « Notre identité » (double label,
 *      formation à la sûreté, technicité + pédagogie, sur-mesure, publics,
 *      logistique) et « Accréditations » (certificats, réseau ASTC, TRAINAIR
 *      PLUS et les 19 Annexes) ;
 *   3. les deux CERTIFICATS OACI sont présentés en CARTES (scan du certificat +
 *      émetteur + dates + PDF téléchargeable). Sources dans assets/docs/,
 *      rendus en .webp dans assets/images/.
 *
 * La barre d'onglets passe donc de 4 à 5 entrées : modificateur
 * `.afsac-progtabs--penta` (même grammaire visuelle que `--quad`, seule la
 * répartition en colonnes change). Aucune couleur nouvelle : tout vient des
 * jetons de la palette OACI (--afsac-blue-accent, --afsac-sky, --afsac-ink…).
 *
 * Mise à jour 07/08/2026 — TEXTES OFFICIELS livrés par le client. Quatre volets
 * sont réécrits au mot près : « Notre identité », « Accréditations »,
 * « Notre histoire » et « Mission & Vision ».
 *   ⚠️ La CHRONOLOGIE change : le Centre est créé en 2008 (agrément DGAC),
 *      rejoint TRAINAIR PLUS en 2016 et devient ICAO ASTC en 2017. L'ancienne
 *      frise (1981 · 1995 · 2015 · 2020 · 2026) était une reconstitution : elle
 *      est supprimée, et le hero passe de « depuis 1981 » à « depuis 2008 ».
 *   La frise horizontale (.afsac-about__frise) laisse place à un récit jalonné
 *   (.afsac-about__story) qui porte les paragraphes complets du client.
 *   Le volet « Perspectives » est SUPPRIMÉ (demande client du 07/08/2026) : son
 *   contenu était une extrapolation de notre part, jamais validée. La barre
 *   d'onglets repasse à cinq entrées.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Fil d'Ariane Rank Math (même wrapper que les autres templates).
if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
	ob_start();
	rank_math_the_breadcrumbs();
	$afsac_crumbs = trim( ob_get_clean() );
	if ( '' !== $afsac_crumbs ) {
		echo '<div class="afsac-breadcrumb"><div class="afsac-container">' . $afsac_crumbs . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sortie Rank Math déjà assainie (wp_kses_post).
	}
}

/*
 * Photo du DG. Le fichier livré s'appelle « dg-hassen-seddik » (avec un E) ;
 * on garde une résolution tolérante au cas où il serait redéposé avec un A.
 *
 * Elle n'apparaît PAS dans le hero (demande client : « enlever la photo du
 * directeur du header ») — elle reste en revanche dans la section « Lettre du
 * Directeur Général ». Le médaillon du hero reste disponible dans
 * template-parts/shared/guide-hero.php (argument « portrait ») si le client
 * revient sur sa décision.
 */
$afsac_dg_photo = '';
foreach ( array( 'dg-hassen-seddik', 'dg-hassan-seddik' ) as $afsac_dg_slug ) {
	foreach ( array( 'jpg', 'jpeg', 'png', 'webp' ) as $afsac_ext ) {
		$afsac_rel = 'assets/images/' . $afsac_dg_slug . '.' . $afsac_ext;
		if ( file_exists( get_theme_file_path( $afsac_rel ) ) ) {
			$afsac_dg_photo = get_theme_file_uri( $afsac_rel );
			break 2;
		}
	}
}

$afsac_dg_name = __( 'Hassen SEDDIK', 'afsac' );
$afsac_dg_role = __( 'Fondateur & Directeur Général', 'afsac' );

// HERO — sur-titre / titre / chapô + chiffres clés (sans portrait, cf. plus haut).
$afsac_hero_eyebrow = function_exists( 'get_field' ) ? (string) get_field( 'afsac_page_eyebrow' ) : '';
if ( '' === $afsac_hero_eyebrow ) {
	$afsac_hero_eyebrow = __( 'À propos', 'afsac' );
}
$afsac_hero_chapo = function_exists( 'get_field' ) ? (string) get_field( 'afsac_page_chapo' ) : '';
if ( '' === $afsac_hero_chapo ) {
	$afsac_hero_chapo = __( 'Double label de l’Organisation de l’Aviation Civile Internationale : Centre Régional AVSEC et membre du programme mondial TRAINAIR PLUS.', 'afsac' );
}

// Hero VIDÉO (bandeau client « Qui sommes-nous », 17/08/2026) : plan d'atelier
// clair, donc le voile navy du layout overlay est indispensable à la lisibilité.
get_template_part(
	'template-parts/shared/video-hero',
	null,
	array(
		'layout'    => 'overlay',
		'align'     => 'start',
		'video_src' => get_theme_file_uri( 'assets/video/qui-sommes-nous.mp4' ),
		'poster'    => get_theme_file_uri( 'assets/images/qui-sommes-nous-hero-poster.jpg' ),
		'eyebrow'   => $afsac_hero_eyebrow,
		'title'     => __( 'Centre international d’excellence de l’OACI, depuis 2008.', 'afsac' ),
		'lead'      => $afsac_hero_chapo,
		'stats'     => array(
			array(
				'value' => '2008',
				'label' => __( 'Création du Centre', 'afsac' ),
			),
			array(
				'value' => number_format_i18n( 2 ),
				'label' => __( 'Labels OACI', 'afsac' ),
			),
			array(
				'value' => number_format_i18n( 19 ),
				'label' => __( 'Annexes couvertes', 'afsac' ),
			),
		),
	)
);

/*
 * NOTRE HISTOIRE — trajectoire officielle fournie par le client (08/2026).
 * Elle REMPLACE la frise précédente (1981 · 1995 · 2015 · 2020 · 2026), qui
 * était une reconstitution : la création du Centre date de 2008, l'adhésion
 * TRAINAIR PLUS de 2016 et la reconnaissance ASTC de 2017.
 * Chaque jalon porte le paragraphe complet du client (rien n'est résumé).
 */
$afsac_story = array(
	array(
		'year'  => '2008',
		'title' => __( 'Création de l’AFSAC', 'afsac' ),
		'tag'   => __( 'Agrément DGAC', 'afsac' ),
		'text'  => __( 'Créé en 2008, l’AFSAC a été établi en tant que centre spécialisé dans la formation en sûreté et sécurité de l’aviation civile, agréé par la Direction Générale de l’Aviation Civile (DGAC) relevant du Ministère du Transport tunisien.', 'afsac' ),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V10M19 21V10M9 21V10M15 21V10"/><path d="M12 3l9 5H3l9-5z"/></svg>',
	),
	array(
		'year'  => '2016',
		'title' => __( 'Programme mondial ICAO TRAINAIR PLUS', 'afsac' ),
		'tag'   => __( 'GAT / OACI', 'afsac' ),
		'text'  => __( 'En 2016, l’AFSAC a intégré le Programme mondial ICAO TRAINAIR PLUS (GAT/OACI), marquant une étape majeure dans son développement international et confirmant son engagement à dispenser des formations conformes aux méthodologies et aux exigences de l’Organisation de l’Aviation Civile Internationale.', 'afsac' ),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="8"/><path d="M4 12h16M12 4c2.5 2.6 2.5 13.4 0 16M12 4c-2.5 2.6-2.5 13.4 0 16"/></svg>',
	),
	array(
		'year'  => '2017',
		'title' => __( 'Centre Régional de Formation à la Sûreté (ICAO ASTC)', 'afsac' ),
		'tag'   => __( 'ICAO ASTC Tunis', 'afsac' ),
		'text'  => __( 'En 2017, l’AFSAC a été reconnu en tant que Centre Régional de Formation à la Sûreté de l’Aviation Civile de l’OACI (ICAO Aviation Security Training Centre – ASTC), renforçant ainsi son positionnement en tant que centre régional de référence pour le développement des compétences dans le domaine de la sûreté aérienne.', 'afsac' ),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v5c0 4-3 7-7 8-4-1-7-4-7-8V6l7-3z"/><path d="M9.2 11.8l2 2 3.6-4"/></svg>',
	),
);

// Clôture de la frise : le cycle d'audit OACI (2 derniers § du texte client).
$afsac_audit = array(
	__( 'Afin de garantir le maintien de son niveau d’excellence et sa conformité permanente aux exigences de l’OACI, l’AFSAC fait l’objet d’un processus régulier d’audit et d’évaluation par l’OACI tous les deux ans, dans le cadre du renouvellement de sa reconnaissance. Ces évaluations permettent de vérifier le respect des standards internationaux, l’efficacité de son système de formation ainsi que sa capacité à maintenir les meilleures pratiques en matière de développement des compétences aéronautiques.', 'afsac' ),
	__( 'Grâce au renouvellement continu de sa reconnaissance OACI et à son engagement permanent envers la qualité et l’amélioration continue, l’AFSAC consolide la confiance de ses partenaires et poursuit sa mission d’accompagnement des États et des acteurs de l’aviation civile conformément à la vision de l’OACI « No Country Left Behind ».', 'afsac' ),
);

// Signature pédagogique (lead + texte). `text` désormais traduit lui aussi.
$afsac_features = array(
	array(
		'lead' => __( 'Approche par compétences alignée OACI Doc 9941', 'afsac' ),
		'text' => __( 'chaque cours documente objectifs, évaluation et performance attendue.', 'afsac' ),
	),
	array(
		'lead' => __( 'Bilinguisme natif', 'afsac' ),
		'text' => __( 'toutes les ressources AVSEC sont produites en français et en anglais sans perte d’équivalence pédagogique.', 'afsac' ),
	),
	array(
		'lead' => __( 'Études de cas régionales', 'afsac' ),
		'text' => __( 'incidents et bonnes pratiques tirés des États accompagnés (Afrique, Maghreb, Golfe).', 'afsac' ),
	),
	array(
		'lead' => __( 'Évaluation rigoureuse', 'afsac' ),
		'text' => __( 'examens normalisés, certification individuelle, suivi post-formation à 90 jours.', 'afsac' ),
	),
	array(
		'lead' => __( 'Mode mixte', 'afsac' ),
		'text' => __( 'présentiel sur campus, intra-entreprise sur site, virtuel synchrone, e-learning autonome.', 'afsac' ),
	),
	array(
		'lead' => __( 'Mise à jour continue', 'afsac' ),
		'text' => __( 'révision des cours à chaque amendement OACI, veille réglementaire trimestrielle.', 'afsac' ),
	),
);

/*
 * Lettre du DG — texte OFFICIEL du client, conservé au mot près. Les 3 premiers
 * paragraphes sont visibles, le reste derrière un <details> natif (« Lire la
 * suite ») : ~1500 px de scroll en moins, zéro mot perdu, et aucun JS.
 */
$afsac_letter_intro = array(
	__( 'Je vous souhaite la bienvenue sur le site internet de notre Centre, dédié au développement des compétences et à l’assistance technique dans le domaine de l’aviation civile.', 'afsac' ),
	__( 'Fort d’une expérience de plus de 20 ans dans le domaine de la formation aéronautique, notre Centre s’est progressivement imposé comme un acteur de référence dans le renforcement des capacités des États et des professionnels du secteur.', 'afsac' ),
	__( 'Notre positionnement repose sur une double reconnaissance internationale unique. Nous sommes à la fois Centre Régional de Formation en Sûreté de l’Aviation Civile au sein du réseau des Aviation Security Training Centres (ASTC) de l’ICAO, et membre du programme mondial TRAINAIR PLUS, ce qui nous permet de concevoir et de délivrer des formations conformes aux standards internationaux, basées sur une approche par les compétences et une méthodologie harmonisée.', 'afsac' ),
);
$afsac_letter_more = array(
	__( 'Cette double reconnaissance confère à notre Centre un positionnement distinctif dans le paysage international de la formation aéronautique, combinant expertise en sûreté de l’aviation civile et excellence pédagogique à travers le programme TRAINAIR PLUS.', 'afsac' ),
	__( 'Nos activités couvrent l’ensemble des domaines de l’aviation civile, à travers la prise en compte des 19 Annexes de la Convention de Chicago, et se traduisent par des actions de formation, de conseil et d’assistance technique adaptées aux besoins des États et des acteurs du secteur.', 'afsac' ),
	__( 'Notre action s’inscrit pleinement dans la vision de l’ICAO « No Country Left Behind », visant à garantir une mise en œuvre effective, harmonisée et équitable des normes internationales de l’aviation civile.', 'afsac' ),
	__( 'Nos interventions couvrent l’ensemble des parties prenantes du système de l’aviation civile, incluant les autorités de régulation, les exploitants aéroportuaires, les compagnies aériennes, les prestataires de services de navigation aérienne ainsi que les organismes de formation. Cette approche intégrée vise le renforcement des capacités institutionnelles et opérationnelles, ainsi que l’amélioration continue de la conformité aux normes et pratiques recommandées de l’OACI.', 'afsac' ),
	__( 'Nous demeurons mobilisés aux côtés des États et des parties prenantes de l’aviation civile afin de soutenir la mise en œuvre effective des normes internationales, le renforcement des capacités institutionnelles et l’amélioration continue des performances du secteur aéronautique, dans le respect des objectifs d’une aviation sûre, harmonisée et durable.', 'afsac' ),
);

/* =====================================================================
 * MISSION & VISION — texte officiel du client (08/2026), au mot près.
 * ================================================================== */

$afsac_vision = array(
	__( 'Être un centre international de référence pour le développement des capacités de l’aviation civile, reconnu pour son expertise OACI, son excellence en formation et sa capacité à accompagner durablement les États et les organisations aéronautiques dans l’amélioration de leurs performances.', 'afsac' ),
	__( 'À travers son engagement en faveur de l’initiative « No Country Left Behind », l’AFSAC contribue au développement d’une aviation civile plus sûre, plus sécurisée et durable, fondée sur le partage des connaissances, l’innovation pédagogique et l’expertise internationale.', 'afsac' ),
);

/*
 * Les 4 missions. `title` est un intitulé de repérage ajouté pour la lecture en
 * cartes ; `text` reste la formulation exacte du client.
 */
$afsac_missions = array(
	array(
		'title' => __( 'Des programmes de formation OACI certifiants', 'afsac' ),
		'text'  => __( 'Couvrant les différents domaines de l’aviation civile et alignés sur les standards internationaux.', 'afsac' ),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l9 4.5-9 4.5L3 7.5 12 3z"/><path d="M7 10v5c0 1.7 2.2 3 5 3s5-1.3 5-3v-5"/></svg>',
	),
	array(
		'title' => __( 'Le transfert de connaissances et d’expertise', 'afsac' ),
		'text'  => __( 'Grâce à un réseau d’instructeurs et d’experts aéronautiques qualifiés.', 'afsac' ),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3"/><path d="M2.5 20c0-3.3 2.9-6 6.5-6s6.5 2.7 6.5 6"/><path d="M16 5.2a3 3 0 0 1 0 5.6"/><path d="M18 14.4c2.1.8 3.5 2.6 3.5 4.6"/></svg>',
	),
	array(
		'title' => __( 'Des solutions d’accompagnement adaptées', 'afsac' ),
		'text'  => __( 'Aux besoins des États et des organisations, incluant la formation sur mesure, l’assistance technique, le conseil et le développement des compétences.', 'afsac' ),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20l5-1 9.5-9.5a2.1 2.1 0 0 0-3-3L6 16l-2 4z"/><path d="M14 6.5l3.5 3.5"/></svg>',
	),
	array(
		'title' => __( 'Un appui à la mise en œuvre des SARPs', 'afsac' ),
		'text'  => __( 'Standards et Pratiques Recommandées de l’OACI, contribuant au renforcement de la sécurité, de la sûreté et de la performance du transport aérien.', 'afsac' ),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v5c0 4-3 7-7 8-4-1-7-4-7-8V6l7-3z"/><path d="M9.2 11.8l2 2 3.6-4"/></svg>',
	),
);

$afsac_values = array(
	array(
		'title' => __( 'Rigueur normative', 'afsac' ),
		'text'  => __( 'Conformité stricte aux Annexes de la Convention de Chicago', 'afsac' ),
	),
	array(
		'title' => __( 'Équité', 'afsac' ),
		'text'  => __( 'Aucun État laissé de côté, quel que soit son niveau', 'afsac' ),
	),
	array(
		'title' => __( 'Innovation', 'afsac' ),
		'text'  => __( 'Pédagogie par les compétences, formats hybrides', 'afsac' ),
	),
	array(
		'title' => __( 'Durabilité', 'afsac' ),
		'text'  => __( 'Une aviation sûre, harmonisée et durable', 'afsac' ),
	),
);

/* =====================================================================
 * BROCHURE « QUI SOMMES-NOUS » — données du volet « Notre identité »
 * ================================================================== */

/*
 * Chapô d'identité — texte officiel du client (08/2026).
 * §1 = le positionnement, §2 = la capacité de formation (repris sous les
 * mallettes officielles), §3 = l'accompagnement, §4 = « No Country Left
 * Behind » (rendu en bandeau de clôture du volet).
 *
 * MISE EN FORME (demande client 07/08/2026 : « minimaliste, affiche quelques
 * lignes, fais une sorte de voir plus ») : chaque paragraphe est coupé en DEUX
 * à une frontière de phrase — la première partie reste visible, la seconde
 * passe derrière un dépliant `.afsac-more`. Aucun mot n'est perdu ; seules les
 * charnières de phrase sont réécrites (« bénéficiant de » → « Il bénéficie
 * de ») pour que la coupe se lise naturellement.
 */
$afsac_identity_tagline = __( 'Votre partenaire stratégique pour le développement des capacités de l’aviation civile', 'afsac' );

$afsac_identity_lead      = __( 'L’AFSAC – ICAO ASTC Tunis est un Centre international d’excellence de l’Organisation de l’Aviation Civile Internationale (OACI).', 'afsac' );
$afsac_identity_lead_more = __( 'Il bénéficie d’un positionnement unique grâce à sa double reconnaissance officielle en tant que Centre Régional de Formation à la Sûreté de l’Aviation Civile (ICAO Aviation Security Training Centre – ASTC) et membre du Programme mondial ICAO TRAINAIR PLUS (Global Aviation Training – GAT).', 'afsac' );

$afsac_identity_packs_text = __( 'Cette double reconnaissance confère à l’AFSAC la capacité de concevoir, développer et dispenser des programmes de formation certifiants couvrant les différents domaines techniques de l’aviation civile relevant des 19 Annexes de la Convention de Chicago.', 'afsac' );
$afsac_identity_packs_more = __( 'Ces programmes s’appuient sur les mallettes pédagogiques officielles de l’OACI, conformément aux Standards et Pratiques Recommandées (SARPs), aux Procédures pour les services de navigation aérienne (PANS) et aux meilleures pratiques internationales.', 'afsac' );

$afsac_identity_support      = __( 'Au-delà de son rôle de centre de formation, l’AFSAC accompagne les acteurs de l’aviation civile dans la mise en œuvre des normes et exigences de l’OACI, à travers des solutions intégrées.', 'afsac' );
$afsac_identity_support_more = __( 'Sont concernés les États, les Autorités de l’Aviation Civile, les exploitants d’aéroports, les prestataires de services de navigation aérienne, les compagnies aériennes et les organisations internationales.', 'afsac' );

$afsac_identity_ncb      = __( 'En parfaite cohérence avec l’initiative mondiale de l’OACI « No Country Left Behind », l’AFSAC mobilise son réseau d’experts internationaux afin d’accompagner les États dans le développement durable de leurs capacités nationales, le transfert de connaissances et d’expertise ainsi que le renforcement des compétences de leur capital humain.', 'afsac' );
$afsac_identity_ncb_more = __( 'À travers ses programmes de formation, ses missions d’assistance technique et son accompagnement stratégique, l’AFSAC contribue à l’amélioration des systèmes nationaux de supervision, à la préparation aux audits internationaux et à la modernisation des structures de l’aviation civile, en faveur d’un transport aérien plus sûr, plus sécurisé, plus performant et durable.', 'afsac' );

/*
 * Dépliant partagé de la page. Rend le composant `.afsac-more` (<details>
 * natif : accessible, indexable, zéro JavaScript) déjà utilisé par le volet
 * AVSEC du catalogue. `$class` sert à porter une variante contextuelle —
 * `--light` sur le bandeau bleu, où le bleu de l'accent serait illisible.
 */
$afsac_more = static function ( array $paragraphs, $class = '', $p_class = '' ) {
	$paragraphs = array_values( array_filter( $paragraphs ) );
	if ( ! $paragraphs ) {
		return;
	}
	?>
	<details class="afsac-more<?php echo $class ? ' ' . esc_attr( $class ) : ''; ?>">
		<summary class="afsac-more__toggle">
			<span class="afsac-more__label afsac-more__label--closed"><?php esc_html_e( 'Lire la suite', 'afsac' ); ?></span>
			<span class="afsac-more__label afsac-more__label--open"><?php esc_html_e( 'Réduire', 'afsac' ); ?></span>
			<svg class="afsac-more__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6 9l6 6 6-6"/></svg>
		</summary>
		<div class="afsac-more__body">
			<?php foreach ( $paragraphs as $afsac_mp ) : ?>
				<p<?php echo $p_class ? ' class="' . esc_attr( $p_class ) . '"' : ''; ?>><?php echo esc_html( $afsac_mp ); ?></p>
			<?php endforeach; ?>
		</div>
	</details>
	<?php
};

// Les trois familles de mallettes pédagogiques officielles de l'OACI.
$afsac_packs = array(
	array(
		'code' => 'STP',
		'name' => __( 'Standardized Training Packages', 'afsac' ),
	),
	array(
		'code' => 'CBT',
		'name' => __( 'Competency-Based Training Packages', 'afsac' ),
	),
	array(
		'code' => 'TPP',
		'name' => __( 'TRAINAIR PLUS Training Packages', 'afsac' ),
	),
);

// Les solutions intégrées proposées au-delà de la formation (§3 du client).
$afsac_solutions = array(
	__( 'Formation', 'afsac' ),
	__( 'Assistance technique', 'afsac' ),
	__( 'Coaching', 'afsac' ),
	__( 'Conseil', 'afsac' ),
	__( 'Audits', 'afsac' ),
	__( 'Renforcement des capacités', 'afsac' ),
	__( 'Développement des compétences', 'afsac' ),
	__( 'Ingénierie pédagogique', 'afsac' ),
	__( 'Appui institutionnel', 'afsac' ),
);

// Les DEUX labels OACI, présentés côte à côte (argument commercial n°1).
$afsac_labels = array(
	array(
		'tag'   => __( 'Label OACI n° 1', 'afsac' ),
		'title' => __( 'Centre Régional de Formation à la Sûreté de l’Aviation de l’OACI', 'afsac' ),
		'sub'   => 'ICAO ASTC Tunis',
		'text'  => __( 'Accrédité par l’OACI depuis le 1er avril 2017, au sein du réseau mondial des Aviation Security Training Centres — Région Europe et Atlantique Nord (EUR/NAT).', 'afsac' ),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v5c0 4-3 7-7 8-4-1-7-4-7-8V6l7-3z"/><path d="M9.2 11.8l2 2 3.6-4"/></svg>',
	),
	array(
		'tag'   => __( 'Label OACI n° 2', 'afsac' ),
		'title' => __( 'Membre du Réseau Mondial TRAINAIR PLUS', 'afsac' ),
		'sub'   => 'TRAINAIR PLUS GAT/ICAO',
		'text'  => __( 'Membre du Programme mondial ICAO TRAINAIR PLUS (GAT/OACI) depuis 2016 : conception et diffusion de mallettes pédagogiques normalisées (MPN) couvrant les 19 Annexes à la Convention de Chicago, dispensées par des instructeurs certifiés OACI.', 'afsac' ),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="8"/><path d="M4 12h16M12 4c2.5 2.6 2.5 13.4 0 16M12 4c-2.5 2.6-2.5 13.4 0 16"/></svg>',
	),
);

// « L'alliance de la technicité et de la pédagogie » / « Des solutions sur-mesure ».
$afsac_approach = array(
	array(
		'title' => __( 'L’alliance de la technicité et de la pédagogie', 'afsac' ),
		'text'  => __( 'La force du Centre réside dans la synergie entre ses experts techniques — couvrant l’ensemble des domaines opérationnels — et ses experts en ingénierie pédagogique, en charge du développement et de l’accompagnement des formations.', 'afsac' ),
		'more'  => __( 'Les professionnels formés au Centre bénéficient d’une approche fondée sur les compétences, en prise directe avec les objectifs de performance visés par les entreprises.', 'afsac' ),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l9 4.5-9 4.5L3 7.5 12 3z"/><path d="M7 10v5c0 1.7 2.2 3 5 3s5-1.3 5-3v-5"/></svg>',
	),
	array(
		'title' => __( 'Des solutions conçues sur-mesure', 'afsac' ),
		'text'  => __( 'Le Centre propose une offre sur-mesure, toujours mieux adaptée aux attentes des acteurs de l’aviation civile.', 'afsac' ),
		'more'  => __( 'Il accompagne ses interlocuteurs dans l’expression de leurs besoins de formation et leur propose des solutions personnalisées, dispensées dans ses locaux à Tunis ou en intra sur site, partout dans le monde — et toujours conformes aux standards internationaux.', 'afsac' ),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20l5-1 9.5-9.5a2.1 2.1 0 0 0-3-3L6 16l-2 4z"/><path d="M14 6.5l3.5 3.5"/></svg>',
	),
);

// Publics et familles de compétences couvertes (brochure, page « Panel de produits »).
$afsac_publics = array(
	array(
		'title' => __( 'Compagnies aériennes', 'afsac' ),
		'items' => array(
			__( 'Opérations', 'afsac' ),
			__( 'Navigabilité', 'afsac' ),
			__( 'Exploitation', 'afsac' ),
			__( 'Management du transport aérien', 'afsac' ),
			__( 'Certification', 'afsac' ),
			__( 'Réglementation', 'afsac' ),
			__( 'Sûreté — Sécurité', 'afsac' ),
		),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 20l1.5-5L3 12.5v-2l8.5 1.5L13 4.5a1.5 1.5 0 0 1 3 0l1.5 7.5L21 11.5v2L14 15l1 5-2.5-1.5L10 20z"/></svg>',
	),
	array(
		'title' => __( 'Aéroports', 'afsac' ),
		'items' => array(
			__( 'Management', 'afsac' ),
			__( 'Ingénierie et conception', 'afsac' ),
			__( 'Exploitation', 'afsac' ),
			__( 'Sûreté — Sécurité', 'afsac' ),
			__( 'Développement durable', 'afsac' ),
			__( 'Réglementation EASA', 'afsac' ),
		),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 20h18"/><path d="M5 20V9l7-4 7 4v11"/><path d="M9 20v-5h6v5"/></svg>',
	),
	array(
		'title' => __( 'Services de la navigation aérienne', 'afsac' ),
		'items' => array(
			__( 'Formation continue des contrôleurs, superviseurs et instructeurs', 'afsac' ),
			__( 'Service d’information aéronautique', 'afsac' ),
			__( 'Communication, navigation et surveillance', 'afsac' ),
		),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 13v8"/><path d="M8 21h8"/><circle cx="12" cy="10" r="3"/><path d="M5.5 4.5a9 9 0 0 0 0 11M18.5 4.5a9 9 0 0 1 0 11"/></svg>',
	),
	array(
		'title' => __( 'Autorités de l’aviation civile', 'afsac' ),
		'items' => array(
			__( 'Surveillance', 'afsac' ),
			__( 'Audit', 'afsac' ),
			__( 'Réglementation', 'afsac' ),
			__( 'Gestion des risques', 'afsac' ),
			__( 'Développement durable', 'afsac' ),
		),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V10M19 21V10M9 21V10M15 21V10"/><path d="M12 3l9 5H3l9-5z"/></svg>',
	),
	array(
		'title' => __( 'Formations transverses', 'afsac' ),
		'items' => array(
			__( 'Sûreté — Sécurité', 'afsac' ),
			__( 'Safety Management System (SMS)', 'afsac' ),
			__( 'Audit', 'afsac' ),
			__( 'Pédagogie', 'afsac' ),
			__( 'Management de la qualité', 'afsac' ),
			__( 'Facteurs humains', 'afsac' ),
		),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M2.5 20c0-3.3 2.9-6 6.5-6s6.5 2.7 6.5 6"/><path d="M16 5.2a3 3 0 0 1 0 5.6"/><path d="M18 14.4c2.1.8 3.5 2.6 3.5 4.6"/></svg>',
	),
);

// Services logistiques offerts aux participants (brochure, page « Logistique »).
$afsac_logistics = array(
	array(
		'title' => __( 'Facilitation d’obtention de visa', 'afsac' ),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><circle cx="12" cy="10" r="2.6"/><path d="M8.5 17h7"/></svg>',
	),
	array(
		'title' => __( 'Accueil à l’aéroport', 'afsac' ),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 20h18"/><path d="M4 16l16-4.5a2 2 0 0 0-1-3.8L4 11"/><path d="M9.5 12.2L7 7.5l2-.5 3.6 4"/></svg>',
	),
	array(
		'title' => __( 'Transport interne', 'afsac' ),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 16v-4l2-5h11l3 5v4"/><path d="M3 16h18"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg>',
	),
	array(
		'title' => __( 'Visites et découverte de la Tunisie', 'afsac' ),
		'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-6.1 7-11a7 7 0 1 0-14 0c0 4.9 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/></svg>',
	),
);

/* =====================================================================
 * BROCHURE — données du volet « Accréditations »
 * ================================================================== */

/*
 * Les deux certificats OACI, en CARTES (demande client). Le scan .webp et le
 * PDF d'origine vivent dans le thème (assets/images + assets/docs) : ni l'un ni
 * l'autre ne dépend de la médiathèque, donc rien à re-téléverser en production.
 * `img` / `pdf` sont résolus plus bas avec file_exists() → jamais de lien mort.
 */
$afsac_certificates = array(
	array(
		'slug'   => 'certificat-icao-astc-2017',
		'n'      => '01',
		'issuer' => __( 'Organisation de l’Aviation Civile Internationale — Montréal (Canada)', 'afsac' ),
		'title'  => __( 'Centre Régional de Formation à la Sûreté de l’Aviation Civile de l’OACI', 'afsac' ),
		'sub'    => 'ICAO ASTC Tunis',
		'lead'   => __( 'Une expertise OACI dédiée à la sûreté et à la facilitation du transport aérien', 'afsac' ),
		'paras'  => array(
			__( 'La reconnaissance de l’AFSAC en tant que Centre Régional de Formation à la Sûreté de l’Aviation Civile de l’OACI (ICAO ASTC) lui permet de dispenser des formations officielles OACI dans les domaines de la Sûreté de l’Aviation Civile (Annexe 17 – Sûreté) et de la Facilitation du transport aérien (Annexe 9 – Facilitation).', 'afsac' ),
			__( 'À travers les Mallettes Pédagogiques Normalisées (MPN) de l’OACI, l’AFSAC organise des cours certifiants, des ateliers spécialisés et des programmes de renforcement des capacités destinés aux professionnels de l’aviation civile, aux autorités compétentes et aux acteurs du secteur aéronautique.', 'afsac' ),
			__( 'Cette reconnaissance témoigne de la conformité du Centre aux exigences de l’OACI et de sa capacité à accompagner les États dans la mise en œuvre des standards internationaux relatifs à la sûreté et à la facilitation.', 'afsac' ),
		),
		'meta'   => array(
			array(
				'k' => __( 'Délivré le', 'afsac' ),
				'v' => __( '1er avril 2017', 'afsac' ),
			),
			array(
				'k' => __( 'Région OACI', 'afsac' ),
				'v' => __( 'Europe et Atlantique Nord (EUR/NAT)', 'afsac' ),
			),
			array(
				'k' => __( 'Portée', 'afsac' ),
				'v' => __( 'Annexes 17 (Sûreté) et 9 (Facilitation)', 'afsac' ),
			),
		),
		'badge'  => __( 'Accréditation permanente', 'afsac' ),
	),
	array(
		'slug'   => 'certificat-trainair-plus-2026',
		'n'      => '02',
		'issuer' => __( 'International Civil Aviation Organization — Juan Carlos Salazar, Secrétaire général', 'afsac' ),
		'title'  => __( 'Membre du Programme Mondial ICAO TRAINAIR PLUS (GAT/OACI)', 'afsac' ),
		'sub'    => 'TRAINAIR PLUS GAT/ICAO',
		'lead'   => __( 'Une capacité élargie de formation couvrant les différents domaines de l’aviation civile', 'afsac' ),
		'paras'  => array(
			__( 'En tant que membre du Programme mondial ICAO TRAINAIR PLUS, l’AFSAC bénéficie d’un accès au réseau international de formation de l’OACI et aux méthodologies officielles de développement des compétences aéronautiques.', 'afsac' ),
			__( 'Cette appartenance permet au Centre de concevoir et de dispenser des formations basées sur les compétences couvrant les différents domaines techniques de l’aviation civile relevant des 19 Annexes de la Convention de Chicago, en utilisant les Training Packages (TPP), Standardized Training Packages (STP) et Competency-Based Training Packages (CBT) développés selon les standards de l’OACI.', 'afsac' ),
			__( 'Grâce à cette expertise en ingénierie pédagogique, l’AFSAC est également capable de développer des solutions de formation adaptées aux besoins spécifiques des États et des organisations aéronautiques, conformément à la méthodologie TRAINAIR PLUS.', 'afsac' ),
		),
		'meta'   => array(
			array(
				'k' => __( 'Membre depuis', 'afsac' ),
				'v' => __( '2016', 'afsac' ),
			),
			array(
				'k' => __( 'Statut', 'afsac' ),
				'v' => __( 'Bronze Member', 'afsac' ),
			),
			array(
				'k' => __( 'Validité', 'afsac' ),
				'v' => __( 'du 1er janvier au 31 décembre 2026', 'afsac' ),
			),
		),
		'badge'  => __( 'Renouvellement annuel', 'afsac' ),
	),
);

// Résolution des fichiers (image de la carte + PDF téléchargeable).
foreach ( $afsac_certificates as $afsac_ci => $afsac_cert ) {
	$afsac_img_rel = 'assets/images/' . $afsac_cert['slug'] . '.webp';
	$afsac_pdf_rel = 'assets/docs/' . $afsac_cert['slug'] . '.pdf';

	$afsac_certificates[ $afsac_ci ]['img'] = file_exists( get_theme_file_path( $afsac_img_rel ) )
		? get_theme_file_uri( $afsac_img_rel )
		: '';
	$afsac_certificates[ $afsac_ci ]['pdf'] = file_exists( get_theme_file_path( $afsac_pdf_rel ) )
		? get_theme_file_uri( $afsac_pdf_rel )
		: '';
}
unset( $afsac_cert, $afsac_ci );

// Réseau ASTC de l'OACI — 35 centres actifs, par région (brochure, page « Réseau »).
$afsac_network = array(
	array(
		'region' => 'NACC & SAM',
		'places' => array( 'Bogota', 'Buenos Aires', 'Mexico City', 'Port of Spain', 'Quito', 'Santo Domingo' ),
	),
	array(
		'region' => 'EUR/NAT',
		'places' => array( 'Almaty', 'Casablanca', 'Doncaster', 'Kyiv (2)', 'Minsk', 'Moscow', 'Toulouse', 'Tunis' ),
	),
	array(
		'region' => 'ESAF & WACAF',
		'places' => array( 'Dakar', 'Dar es Salaam', 'Douala', 'Johannesburg', 'Lagos', 'Nairobi' ),
	),
	array(
		'region' => 'MID',
		'places' => array( 'Amman', 'Beirut', 'Cairo', 'Dubai', 'Jeddah', 'Manama' ),
	),
	array(
		'region' => 'APAC',
		'places' => array( 'Auckland', 'Beijing', 'Hong Kong', 'Jakarta', 'Kuala Lumpur', 'New Delhi', 'Seoul', 'Singapore' ),
	),
);

// Les 19 Annexes à la Convention de Chicago couvertes par TRAINAIR PLUS.
$afsac_annexes = array(
	__( 'Licences du personnel', 'afsac' ),
	__( 'Règles de l’air', 'afsac' ),
	__( 'Assistance météorologique', 'afsac' ),
	__( 'Cartes aéronautiques', 'afsac' ),
	__( 'Unités de mesure', 'afsac' ),
	__( 'Exploitation technique des aéronefs', 'afsac' ),
	__( 'Marques de nationalité et d’immatriculation', 'afsac' ),
	__( 'Navigabilité des aéronefs', 'afsac' ),
	__( 'Facilitation', 'afsac' ),
	__( 'Télécommunications aéronautiques', 'afsac' ),
	__( 'Services de la circulation aérienne', 'afsac' ),
	__( 'Recherches et sauvetage', 'afsac' ),
	__( 'Enquêtes sur les accidents et incidents d’aviation', 'afsac' ),
	__( 'Aérodromes', 'afsac' ),
	__( 'Services d’information aéronautique', 'afsac' ),
	__( 'Protection de l’environnement', 'afsac' ),
	__( 'Sûreté', 'afsac' ),
	__( 'Sécurité du transport aérien des marchandises dangereuses', 'afsac' ),
	__( 'Gestion de la sécurité', 'afsac' ),
);

$afsac_check_icon = '<svg class="afsac-about__check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/></svg>';
?>

<main id="primary" class="afsac-about-page">

	<?php
	/*
	 * Sélecteur des volets. Réutilise le composant du catalogue
	 * (.afsac-progtabs + assets/js/programme-tabs.js) : mêmes rôles ARIA, mêmes
	 * raccourcis clavier, même lien profond (#dg, #identite, #accreditations,
	 * #histoire, #mission).
	 * Les panneaux sont rendus côté serveur (SEO + dégradation sans JS).
	 *
	 * ORDRE — « Le mot du DG » est le PREMIER volet (demande client 08/2026).
	 */
	$afsac_tabs = array(
		'dg'             => __( 'Le mot du DG', 'afsac' ),
		'identite'       => __( 'Notre identité', 'afsac' ),
		'accreditations' => __( 'Accréditations', 'afsac' ),
		'histoire'       => __( 'Notre histoire', 'afsac' ),
		'mission'        => __( 'Mission & Vision', 'afsac' ),
	);
	?>
	<div class="afsac-progtabs afsac-progtabs--penta" data-prog-tabs>
		<div class="afsac-container">
			<p class="afsac-progtabs__hint">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 8h13M17 5l3 3-3 3"/><path d="M17 16H4M7 13l-3 3 3 3"/></svg>
				<?php esc_html_e( 'Cinq volets — cliquez pour naviguer', 'afsac' ); ?>
			</p>
			<div class="afsac-progtabs__nav" role="tablist" aria-label="<?php esc_attr_e( 'Sections de la page', 'afsac' ); ?>">
				<?php
				$afsac_i = 0;
				foreach ( $afsac_tabs as $afsac_key => $afsac_label ) :
					$afsac_on = ( 0 === $afsac_i );
					?>
					<button type="button" class="afsac-progtabs__tab<?php echo $afsac_on ? ' is-active' : ''; ?>"
						role="tab" id="afsac-tab-<?php echo esc_attr( $afsac_key ); ?>"
						aria-controls="afsac-panel-<?php echo esc_attr( $afsac_key ); ?>"
						aria-selected="<?php echo $afsac_on ? 'true' : 'false'; ?>"
						data-prog-tab="<?php echo esc_attr( $afsac_key ); ?>"<?php echo $afsac_on ? '' : ' tabindex="-1"'; ?>>
						<span class="afsac-progtabs__name"><?php echo esc_html( $afsac_label ); ?></span>
					</button>
					<?php
					++$afsac_i;
				endforeach;
				?>
			</div>
		</div>
	</div>

	<?php /* ============ 1. LE MOT DU DG ============ */ ?>
	<div class="afsac-progtabs__panel" id="afsac-panel-dg" role="tabpanel" aria-labelledby="afsac-tab-dg" data-prog-panel="dg">
		<section class="afsac-section afsac-director">
			<span class="afsac-director__watermark" aria-hidden="true">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/logo-icao.png' ) ); ?>" alt="" loading="lazy" decoding="async">
			</span>

			<div class="afsac-container afsac-director__inner">
				<aside class="afsac-director__aside afsac-reveal">
					<figure class="afsac-director__media">
						<?php if ( $afsac_dg_photo ) : ?>
							<img class="afsac-director__photo" src="<?php echo esc_url( $afsac_dg_photo ); ?>" alt="" loading="lazy" decoding="async">
						<?php else : ?>
							<span class="afsac-director__photo afsac-director__photo--placeholder" role="img" aria-label="<?php esc_attr_e( 'Portrait du Directeur Général (à venir)', 'afsac' ); ?>">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg>
							</span>
						<?php endif; ?>
					</figure>
					<figcaption class="afsac-director__sign">
						<span class="afsac-director__name"><?php echo esc_html( $afsac_dg_name ); ?></span>
						<span class="afsac-director__role"><?php echo esc_html( $afsac_dg_role ); ?></span>
					</figcaption>
				</aside>

				<div class="afsac-director__body afsac-reveal">
					<p class="afsac-eyebrow"><?php esc_html_e( 'Mot du Directeur Général', 'afsac' ); ?></p>
					<h2 class="afsac-director__title"><?php esc_html_e( 'Une vision au service de l’aviation civile', 'afsac' ); ?></h2>

					<div class="afsac-director__letter">
						<p class="afsac-director__greeting"><?php esc_html_e( 'Chers visiteurs,', 'afsac' ); ?></p>
						<?php foreach ( $afsac_letter_intro as $afsac_p ) : ?>
							<p><?php echo esc_html( $afsac_p ); ?></p>
						<?php endforeach; ?>

						<blockquote class="afsac-director__quote">
							<?php esc_html_e( '« Positionner le Centre comme une référence internationale, en alliant rigueur normative, innovation pédagogique et accompagnement stratégique des États. »', 'afsac' ); ?>
						</blockquote>

						<?php /* <details> natif : lecture progressive sans JS, accessible par défaut. */ ?>
						<details class="afsac-director__more">
							<summary class="afsac-button afsac-button--ghost afsac-director__more-btn">
								<span class="afsac-director__more-open"><?php esc_html_e( 'Lire la suite', 'afsac' ); ?></span>
								<span class="afsac-director__more-close"><?php esc_html_e( 'Réduire', 'afsac' ); ?></span>
							</summary>
							<div class="afsac-director__more-body">
								<?php foreach ( $afsac_letter_more as $afsac_p ) : ?>
									<p><?php echo esc_html( $afsac_p ); ?></p>
								<?php endforeach; ?>
							</div>
						</details>
					</div>

					<div class="afsac-director__signoff">
						<span class="afsac-director__name"><?php echo esc_html( $afsac_dg_name ); ?></span>
						<span class="afsac-director__role"><?php echo esc_html( $afsac_dg_role ); ?></span>
					</div>
				</div>
			</div>
		</section>
	</div>

	<?php /* ============ 2. NOTRE IDENTITÉ (brochure) ============ */ ?>
	<div class="afsac-progtabs__panel" id="afsac-panel-identite" role="tabpanel" aria-labelledby="afsac-tab-identite" data-prog-panel="identite">
		<section class="afsac-section afsac-about__section--sky afsac-about__identity">
			<div class="afsac-container">

				<div class="afsac-about__section-head afsac-reveal">
					<span class="afsac-eyebrow"><?php esc_html_e( 'Notre identité', 'afsac' ); ?></span>
					<h2 class="afsac-about__section-title">AFSAC <span class="afsac-about__title-dash" aria-hidden="true">–</span> ICAO ASTC Tunis</h2>
					<p class="afsac-about__tagline"><?php echo esc_html( $afsac_identity_tagline ); ?></p>
				</div>

				<?php
				/*
				 * Maquette du siège de l’AFSAC. Déplacée depuis le diaporama de l’accueil
				 * (08/2026) : c’est un rendu 3D et non une photo de session, et le cadrage
				 * en cover du diaporama le rognait. Ici il est montré ENTIER (pas de
				 * recadrage), à sa place dans la section qui présente le Centre.
				 * Décoratif au sens strict — mais il porte la signalétique trilingue du
				 * bâtiment, d’où un alt descriptif et une légende.
				 */
				$afsac_building = 'assets/images/intro-photo-11.webp';
				if ( file_exists( get_theme_file_path( $afsac_building ) ) ) :
					?>
					<figure class="afsac-about__building afsac-reveal">
						<img src="<?php echo esc_url( get_theme_file_uri( $afsac_building ) ); ?>"
							alt="<?php esc_attr_e( 'Maquette du bâtiment du Centre régional de formation à la sûreté de l’aviation de l’OACI de Tunis (ASTC)', 'afsac' ); ?>"
							width="1120" height="840" loading="lazy" decoding="async">
						<figcaption><?php esc_html_e( 'Le siège de l’AFSAC – ICAO ASTC Tunis', 'afsac' ); ?></figcaption>
					</figure>
					<?php
				endif;
				?>

				<div class="afsac-about__prose afsac-about__prose--folded afsac-reveal">
					<p class="afsac-about__prose-lead"><?php echo esc_html( $afsac_identity_lead ); ?></p>
					<?php $afsac_more( array( $afsac_identity_lead_more ) ); ?>
				</div>

				<div class="afsac-about__labels afsac-stagger">
					<?php foreach ( $afsac_labels as $afsac_lb ) : ?>
						<article class="afsac-about__label afsac-reveal">
							<span class="afsac-about__label-ico" aria-hidden="true"><?php echo $afsac_lb['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?></span>
							<span class="afsac-about__label-tag"><?php echo esc_html( $afsac_lb['tag'] ); ?></span>
							<h3 class="afsac-about__label-title"><?php echo esc_html( $afsac_lb['title'] ); ?></h3>
							<span class="afsac-about__label-sub"><?php echo esc_html( $afsac_lb['sub'] ); ?></span>
							<p class="afsac-about__label-text"><?php echo esc_html( $afsac_lb['text'] ); ?></p>
						</article>
					<?php endforeach; ?>
				</div>

				<?php /* --- Ce que la double reconnaissance permet : les MPN officielles --- */ ?>
				<div class="afsac-about__packs afsac-reveal">
					<div class="afsac-about__packs-body">
						<span class="afsac-eyebrow"><?php esc_html_e( 'Mallettes pédagogiques officielles de l’OACI', 'afsac' ); ?></span>
						<h3 class="afsac-about__sub-title"><?php esc_html_e( 'Concevoir, développer et dispenser des programmes certifiants', 'afsac' ); ?></h3>
						<p><?php echo esc_html( $afsac_identity_packs_text ); ?></p>
						<?php $afsac_more( array( $afsac_identity_packs_more ) ); ?>
					</div>
					<ul class="afsac-about__packs-list afsac-stagger">
						<?php foreach ( $afsac_packs as $afsac_pk ) : ?>
							<li class="afsac-about__pack afsac-reveal">
								<span class="afsac-about__pack-code"><?php echo esc_html( $afsac_pk['code'] ); ?></span>
								<span class="afsac-about__pack-name"><?php echo esc_html( $afsac_pk['name'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<?php /* --- La formation à la sûreté de l'aviation --- */ ?>
				<div class="afsac-about__avsec afsac-reveal">
					<div class="afsac-about__avsec-body">
						<span class="afsac-eyebrow"><?php esc_html_e( 'Notre cœur de métier', 'afsac' ); ?></span>
						<h3 class="afsac-about__sub-title"><?php esc_html_e( 'La formation à la sûreté de l’aviation', 'afsac' ); ?></h3>
						<p><?php esc_html_e( 'Vous vous sentez impliqué dans ce domaine et souhaitez y travailler : le Centre Régional de l’OACI de Tunis vous propose des formations « qualifiantes » et « certifiantes » afin de devenir responsable, instructeur ou concepteur en sûreté de l’aviation civile, conformément aux standards internationaux de l’OACI.', 'afsac' ); ?></p>
						<?php $afsac_more( array( __( 'La sûreté de l’aviation civile est un domaine en constante évolution. Elle doit s’adapter en permanence à la menace que des groupes terroristes continuent de faire peser sur un secteur dont l’importance économique comme la haute valeur symbolique le transforment en cible particulièrement attractive.', 'afsac' ) ) ); ?>
						<ul class="afsac-about__pills">
							<li><?php esc_html_e( 'Responsable sûreté', 'afsac' ); ?></li>
							<li><?php esc_html_e( 'Instructeur', 'afsac' ); ?></li>
							<li><?php esc_html_e( 'Concepteur de formation', 'afsac' ); ?></li>
						</ul>
					</div>
					<p class="afsac-about__avsec-quote"><?php esc_html_e( '« La sûreté : l’affaire de tous »', 'afsac' ); ?></p>
				</div>

				<?php /* --- Technicité + pédagogie / sur-mesure --- */ ?>
				<div class="afsac-about__mv-grid afsac-stagger">
					<?php foreach ( $afsac_approach as $afsac_ap ) : ?>
						<article class="afsac-about__mv-card afsac-about__mv-card--vision afsac-reveal">
							<span class="afsac-about__mv-ico" aria-hidden="true"><?php echo $afsac_ap['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?></span>
							<h3 class="afsac-about__mv-title"><?php echo esc_html( $afsac_ap['title'] ); ?></h3>
							<p class="afsac-about__mv-text"><?php echo esc_html( $afsac_ap['text'] ); ?></p>
							<?php $afsac_more( array( $afsac_ap['more'] ), '', 'afsac-about__mv-text' ); ?>
						</article>
					<?php endforeach; ?>
				</div>

				<?php /* --- Au-delà de la formation : publics accompagnés + solutions intégrées --- */ ?>
				<h3 class="afsac-about__sig-title afsac-reveal"><?php esc_html_e( 'Au-delà de la formation, un accompagnement intégré', 'afsac' ); ?></h3>
				<div class="afsac-about__support afsac-reveal">
					<p class="afsac-about__support-text"><?php echo esc_html( $afsac_identity_support ); ?></p>
					<?php $afsac_more( array( $afsac_identity_support_more ), '', 'afsac-about__support-text' ); ?>
					<ul class="afsac-about__chips afsac-stagger">
						<?php foreach ( $afsac_solutions as $afsac_sol ) : ?>
							<li class="afsac-about__chip afsac-reveal afsac-reveal--fade"><?php echo esc_html( $afsac_sol ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<?php /* --- Publics et compétences couvertes --- */ ?>
				<h3 class="afsac-about__sig-title afsac-reveal"><?php esc_html_e( 'À qui s’adressent nos formations', 'afsac' ); ?></h3>
				<div class="afsac-about__publics afsac-stagger">
					<?php foreach ( $afsac_publics as $afsac_pub ) : ?>
						<article class="afsac-about__public afsac-reveal">
							<span class="afsac-about__public-ico" aria-hidden="true"><?php echo $afsac_pub['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?></span>
							<h4 class="afsac-about__public-title"><?php echo esc_html( $afsac_pub['title'] ); ?></h4>
							<ul class="afsac-about__public-list">
								<?php foreach ( $afsac_pub['items'] as $afsac_it ) : ?>
									<li><?php echo esc_html( $afsac_it ); ?></li>
								<?php endforeach; ?>
							</ul>
						</article>
					<?php endforeach; ?>
				</div>

				<?php /* --- Logistique participants --- */ ?>
				<h3 class="afsac-about__sig-title afsac-reveal"><?php esc_html_e( 'Nous accompagnons vos participants', 'afsac' ); ?></h3>
				<ul class="afsac-about__logi afsac-stagger">
					<?php foreach ( $afsac_logistics as $afsac_lg ) : ?>
						<li class="afsac-about__logi-item afsac-reveal">
							<span class="afsac-about__logi-ico" aria-hidden="true"><?php echo $afsac_lg['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?></span>
							<span class="afsac-about__logi-label"><?php echo esc_html( $afsac_lg['title'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>

				<?php /* --- Clôture du volet : « No Country Left Behind » (§4 du client) --- */ ?>
				<div class="afsac-about__ncl afsac-about__ncl--globe afsac-reveal">
					<?php /* Emblème BLANC détouré : cf. la note du bloc « Notre vision ». */ ?>
					<?php if ( file_exists( get_theme_file_path( 'assets/images/oaci-emblem-white.webp' ) ) ) : ?>
						<span class="afsac-about__ncl-globe" aria-hidden="true">
							<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/oaci-emblem-white.webp' ) ); ?>" alt="" loading="lazy" decoding="async">
						</span>
					<?php endif; ?>
					<div class="afsac-about__ncl-inner">
						<span class="afsac-eyebrow afsac-about__ncl-eyebrow"><?php esc_html_e( 'Initiative mondiale de l’OACI', 'afsac' ); ?></span>
						<h3 class="afsac-about__ncl-title">« No Country Left Behind »</h3>
						<p class="afsac-about__ncl-text"><?php echo esc_html( $afsac_identity_ncb ); ?></p>
						<?php $afsac_more( array( $afsac_identity_ncb_more ), 'afsac-more--light', 'afsac-about__ncl-text' ); ?>
					</div>
				</div>

			</div>
		</section>
	</div>

	<?php /* ============ 3. ACCRÉDITATIONS (brochure + certificats) ============ */ ?>
	<div class="afsac-progtabs__panel" id="afsac-panel-accreditations" role="tabpanel" aria-labelledby="afsac-tab-accreditations" data-prog-panel="accreditations">
		<section class="afsac-section afsac-about__accred">
			<div class="afsac-container">

				<div class="afsac-about__section-head afsac-reveal">
					<span class="afsac-eyebrow"><?php esc_html_e( 'Certificats officiels', 'afsac' ); ?></span>
					<h2 class="afsac-about__section-title"><?php esc_html_e( 'Nos accréditations délivrées par l’OACI', 'afsac' ); ?></h2>
					<p class="afsac-about__tagline"><?php esc_html_e( 'Une double reconnaissance officielle, auditée et renouvelée par l’Organisation.', 'afsac' ); ?></p>
				</div>

				<div class="afsac-about__certs">
					<?php foreach ( $afsac_certificates as $afsac_cert ) : ?>
						<article class="afsac-about__cert afsac-reveal">
							<?php if ( $afsac_cert['img'] ) : ?>
								<figure class="afsac-about__cert-media">
									<img src="<?php echo esc_url( $afsac_cert['img'] ); ?>"
										alt="<?php echo esc_attr( sprintf( /* translators: %s: intitulé du certificat. */ __( 'Certificat OACI — %s', 'afsac' ), $afsac_cert['title'] ) ); ?>"
										loading="lazy" decoding="async">
									<figcaption class="afsac-about__cert-badge"><?php echo esc_html( $afsac_cert['badge'] ); ?></figcaption>
								</figure>
							<?php endif; ?>

							<div class="afsac-about__cert-body">
								<span class="afsac-about__cert-n" aria-hidden="true"><?php echo esc_html( $afsac_cert['n'] ); ?></span>
								<h3 class="afsac-about__cert-title"><?php echo esc_html( $afsac_cert['title'] ); ?></h3>
								<span class="afsac-about__cert-sub"><?php echo esc_html( $afsac_cert['sub'] ); ?></span>
								<p class="afsac-about__cert-lead"><?php echo esc_html( $afsac_cert['lead'] ); ?></p>

								<?php
								/*
								 * Les deux certificats sont CÔTE À CÔTE (demande client
								 * 07/08/2026) : la colonne est étroite, donc seul le
								 * premier paragraphe reste visible — les deux autres
								 * passent derrière le dépliant.
								 */
								$afsac_cert_paras = array_values( $afsac_cert['paras'] );
								?>
								<div class="afsac-about__cert-text">
									<p><?php echo esc_html( array_shift( $afsac_cert_paras ) ); ?></p>
									<?php $afsac_more( $afsac_cert_paras ); ?>
								</div>

								<dl class="afsac-about__cert-meta">
									<?php foreach ( $afsac_cert['meta'] as $afsac_m ) : ?>
										<div class="afsac-about__cert-row">
											<dt><?php echo esc_html( $afsac_m['k'] ); ?></dt>
											<dd><?php echo esc_html( $afsac_m['v'] ); ?></dd>
										</div>
									<?php endforeach; ?>
								</dl>

								<p class="afsac-about__cert-issuer"><?php echo esc_html( $afsac_cert['issuer'] ); ?></p>

								<?php if ( $afsac_cert['pdf'] ) : ?>
									<a class="afsac-button afsac-button--outline afsac-about__cert-link"
										href="<?php echo esc_url( $afsac_cert['pdf'] ); ?>"
										target="_blank" rel="noopener"
										aria-label="<?php echo esc_attr( sprintf( /* translators: %s: intitulé du certificat. */ __( 'Consulter le certificat « %s » au format PDF (nouvel onglet)', 'afsac' ), $afsac_cert['title'] ) ); ?>">
										<?php esc_html_e( 'Consulter le certificat (PDF)', 'afsac' ); ?>
									</a>
								<?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>

				<?php /* --- Le réseau ASTC de l'OACI --- */ ?>
				<div class="afsac-about__net afsac-reveal">
					<div class="afsac-about__net-head">
						<div>
							<span class="afsac-eyebrow"><?php esc_html_e( 'Réseau mondial', 'afsac' ); ?></span>
							<h3 class="afsac-about__sub-title"><?php esc_html_e( 'Le réseau des Centres de Formation à la Sûreté de l’Aviation (ASTC)', 'afsac' ); ?></h3>
							<p><?php esc_html_e( 'Le réseau ASTC de l’OACI comprend 35 centres de formation actifs dans toutes les régions de l’Organisation. Une réunion annuelle des directeurs de centres assure une communication efficace entre ces centres et l’OACI. L’AFSAC — Centre Régional de l’OACI de Tunis — appartient à la Région EUR/NAT, dont le Bureau Régional est situé à Paris.', 'afsac' ); ?></p>
						</div>
						<ul class="afsac-about__net-stats">
							<li><b>35</b><span><?php esc_html_e( 'centres actifs', 'afsac' ); ?></span></li>
							<li><b>5</b><span><?php esc_html_e( 'régions OACI', 'afsac' ); ?></span></li>
							<li><b>EUR/NAT</b><span><?php esc_html_e( 'notre région', 'afsac' ); ?></span></li>
						</ul>
					</div>

					<div class="afsac-about__net-grid">
						<?php foreach ( $afsac_network as $afsac_reg ) : ?>
							<div class="afsac-about__net-col">
								<h4 class="afsac-about__net-region"><?php echo esc_html( $afsac_reg['region'] ); ?></h4>
								<ul>
									<?php foreach ( $afsac_reg['places'] as $afsac_place ) : ?>
										<li<?php echo ( 'Tunis' === $afsac_place ) ? ' class="is-us"' : ''; ?>>
											<?php echo esc_html( $afsac_place ); ?>
											<?php if ( 'Tunis' === $afsac_place ) : ?>
												<span class="screen-reader-text"><?php esc_html_e( '(notre centre)', 'afsac' ); ?></span>
											<?php endif; ?>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<?php /* --- Domaine TRAINAIR PLUS + les 19 Annexes --- */ ?>
				<div class="afsac-about__tp afsac-reveal">
					<span class="afsac-eyebrow"><?php esc_html_e( 'Domaine TRAINAIR PLUS', 'afsac' ); ?></span>
					<h3 class="afsac-about__sub-title"><?php esc_html_e( 'Les 19 Annexes à la Convention de Chicago', 'afsac' ); ?></h3>
					<p class="afsac-about__tp-lead"><?php esc_html_e( 'Nos offres de formation continue répondent aux besoins de l’ensemble des acteurs de l’aéronautique, selon des mallettes pédagogiques normalisées (MPN) et dispensées par des formateurs / instructeurs OACI. Le Programme Mondial TRAINAIR PLUS couvre les 19 Annexes de l’OACI.', 'afsac' ); ?></p>

					<ol class="afsac-about__annexes afsac-stagger">
						<?php
						$afsac_an = 0;
						foreach ( $afsac_annexes as $afsac_annex ) :
							++$afsac_an;
							?>
							<li class="afsac-about__annex afsac-reveal">
								<span class="afsac-about__annex-n" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $afsac_an ) ); ?></span>
								<span class="afsac-about__annex-label">
									<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %d: numéro de l'Annexe OACI. */ __( 'Annexe %d —', 'afsac' ), $afsac_an ) ); ?></span>
									<?php echo esc_html( $afsac_annex ); ?>
								</span>
							</li>
						<?php endforeach; ?>
					</ol>
				</div>

			</div>
		</section>
	</div>

	<?php /* ============ 4. NOTRE HISTOIRE ============ */ ?>
	<div class="afsac-progtabs__panel" id="afsac-panel-histoire" role="tabpanel" aria-labelledby="afsac-tab-histoire" data-prog-panel="histoire">
		<section class="afsac-section afsac-about__section--sky afsac-about__timeline">
			<div class="afsac-container">
				<div class="afsac-about__section-head afsac-reveal">
					<span class="afsac-eyebrow"><?php esc_html_e( 'Notre histoire', 'afsac' ); ?></span>
					<h2 class="afsac-about__section-title"><?php esc_html_e( 'Depuis 2008, une trajectoire jalonnée de reconnaissances OACI', 'afsac' ); ?></h2>
				</div>

				<ol class="afsac-about__story afsac-stagger">
					<?php foreach ( $afsac_story as $afsac_st ) : ?>
						<li class="afsac-about__step afsac-reveal">
							<span class="afsac-about__step-marker" aria-hidden="true">
								<span class="afsac-about__step-dot"><?php echo $afsac_st['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?></span>
							</span>
							<div class="afsac-about__step-body">
								<p class="afsac-about__step-head">
									<span class="afsac-about__step-year"><?php echo esc_html( $afsac_st['year'] ); ?></span>
									<span class="afsac-about__step-tag"><?php echo esc_html( $afsac_st['tag'] ); ?></span>
								</p>
								<h3 class="afsac-about__step-title"><?php echo esc_html( $afsac_st['title'] ); ?></h3>
								<p class="afsac-about__step-text"><?php echo esc_html( $afsac_st['text'] ); ?></p>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>

				<?php /* Cycle d'audit OACI : la reconnaissance se mérite tous les deux ans. */ ?>
				<div class="afsac-about__audit afsac-reveal">
					<div class="afsac-about__audit-badge" aria-hidden="true">
						<span class="afsac-about__audit-ring"></span>
						<b><?php esc_html_e( '2 ans', 'afsac' ); ?></b>
						<span><?php esc_html_e( 'cycle d’audit', 'afsac' ); ?></span>
					</div>
					<div class="afsac-about__audit-body">
						<span class="afsac-eyebrow"><?php esc_html_e( 'Excellence auditée', 'afsac' ); ?></span>
						<h3 class="afsac-about__sub-title"><?php esc_html_e( 'Une reconnaissance renouvelée tous les deux ans', 'afsac' ); ?></h3>
						<?php foreach ( $afsac_audit as $afsac_ap_txt ) : ?>
							<p><?php echo esc_html( $afsac_ap_txt ); ?></p>
						<?php endforeach; ?>
					</div>
				</div>

				<?php /* Ancrage institutionnel repris de la brochure (« panel de produits »). */ ?>
				<div class="afsac-about__prose afsac-about__prose--wide afsac-reveal">
					<p><?php esc_html_e( 'Dans le cadre de sa mission de formation du personnel de l’aviation civile au sens large, le Centre organise des sessions de formation pour les personnes concernées par la sûreté de l’aviation civile. Depuis quelques années, la lutte contre les actes de terrorisme aérien est devenue l’une des priorités majeures des organisations internationales et des autorités nationales de l’aviation civile.', 'afsac' ); ?></p>
					<p><?php esc_html_e( 'Pour répondre à la technicité croissante de la sûreté du transport aérien, le Centre a su s’entourer d’une équipe de professionnels et obtenir, au cours de ces dernières années, une notoriété tant nationale qu’internationale. En sa qualité de Centre Régional de formation de l’OACI, il accueille chaque année des ateliers et des formations en sûreté de l’aviation civile pour le compte de l’Organisation — ainsi l’atelier OACI sur la gestion des risques organisé à Tunis du 1er au 4 octobre 2019, à l’intention des États relevant du Bureau Régional de Paris (EUR/NAT).', 'afsac' ); ?></p>
				</div>

				<h3 class="afsac-about__sig-title afsac-reveal"><?php esc_html_e( 'Notre signature pédagogique', 'afsac' ); ?></h3>
				<ul class="afsac-about__features afsac-stagger">
					<?php foreach ( $afsac_features as $afsac_f ) : ?>
						<li class="afsac-about__feature afsac-reveal">
							<?php echo $afsac_check_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?>
							<span><strong><?php echo esc_html( $afsac_f['lead'] ); ?></strong> — <?php echo esc_html( $afsac_f['text'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	</div>

	<?php /* ============ 5. MISSION & VISION ============ */ ?>
	<div class="afsac-progtabs__panel" id="afsac-panel-mission" role="tabpanel" aria-labelledby="afsac-tab-mission" data-prog-panel="mission">
		<section class="afsac-section afsac-about__section--sky afsac-about__mv">
			<div class="afsac-container">

				<?php /* --- NOTRE VISION : bloc immersif, filigrane du globe OACI --- */ ?>
				<section class="afsac-about__vision afsac-reveal">
					<?php
					/*
					 * Filigrane : l'emblème BLANC détouré (même choix que le bloc
					 * « Pourquoi nous choisir » de l'accueil). logo-icao.png a un
					 * fond blanc opaque — sur un aplat bleu, il dessinerait un
					 * rectangle clair au lieu d'un filigrane.
					 */
					if ( file_exists( get_theme_file_path( 'assets/images/oaci-emblem-white.webp' ) ) ) :
						?>
						<span class="afsac-about__vision-globe" aria-hidden="true">
							<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/oaci-emblem-white.webp' ) ); ?>" alt="" loading="lazy" decoding="async">
						</span>
						<?php
					endif;
					?>
					<div class="afsac-about__vision-inner">
						<span class="afsac-about__vision-ico" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
						</span>
						<span class="afsac-eyebrow afsac-about__vision-eyebrow"><?php esc_html_e( 'Notre vision', 'afsac' ); ?></span>
						<h2 class="afsac-about__vision-title"><?php esc_html_e( 'Un centre international de référence pour le développement des capacités de l’aviation civile', 'afsac' ); ?></h2>
						<?php foreach ( $afsac_vision as $afsac_vp ) : ?>
							<p class="afsac-about__vision-text"><?php echo esc_html( $afsac_vp ); ?></p>
						<?php endforeach; ?>
					</div>
				</section>

				<?php /* --- NOS MISSIONS : chapô + 4 axes numérotés --- */ ?>
				<div class="afsac-about__section-head afsac-about__section-head--tight afsac-reveal">
					<span class="afsac-eyebrow"><?php esc_html_e( 'Nos missions', 'afsac' ); ?></span>
					<h2 class="afsac-about__section-title"><?php esc_html_e( 'Renforcer les compétences et les capacités opérationnelles', 'afsac' ); ?></h2>
					<p class="afsac-about__tagline"><?php esc_html_e( 'L’AFSAC a pour mission d’accompagner les acteurs de l’aviation civile dans le renforcement de leurs compétences et de leurs capacités opérationnelles à travers :', 'afsac' ); ?></p>
				</div>

				<ol class="afsac-about__missions afsac-stagger">
					<?php
					$afsac_mi = 0;
					foreach ( $afsac_missions as $afsac_mis ) :
						++$afsac_mi;
						?>
						<li class="afsac-about__mission afsac-reveal">
							<span class="afsac-about__mission-n" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $afsac_mi ) ); ?></span>
							<span class="afsac-about__mission-ico" aria-hidden="true"><?php echo $afsac_mis['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?></span>
							<h3 class="afsac-about__mission-title"><?php echo esc_html( $afsac_mis['title'] ); ?></h3>
							<p class="afsac-about__mission-text"><?php echo esc_html( $afsac_mis['text'] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ol>

				<div class="afsac-about__values afsac-stagger">
					<?php foreach ( $afsac_values as $afsac_v ) : ?>
						<div class="afsac-about__value afsac-reveal">
							<b><?php echo esc_html( $afsac_v['title'] ); ?></b>
							<span><?php echo esc_html( $afsac_v['text'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	</div>


</main>

<?php
get_footer();
