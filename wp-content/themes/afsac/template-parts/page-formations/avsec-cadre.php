<?php
/**
 * Catalogue — volet AVSEC : le cadre international (texte de référence client).
 *
 * Contenu fourni par le client (07/08/2026) pour ancrer le volet AVSEC dans le
 * cadre réglementaire de l'OACI AVANT de parler formats et catalogue : Annexe 17
 * (sûreté), Annexe 9 (facilitation), SARPs, puis le réseau des centres agréés
 * (ASTC) et la place de l'AFSAC dedans.
 *
 * Retour client (07/08/2026, même jour) : « trop de texte, fais minimaliste ».
 * Une SEULE colonne centrée, un seul paragraphe visible ; tout le reste est
 * replié derrière « Lire la suite ». Le repli est un <details> natif : il
 * fonctionne sans JavaScript, reste indexable par les moteurs et n'a besoin
 * d'aucune gate .afsac-js.
 *
 * Purement éditorial : aucune donnée, aucun helper, rien à charger.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Les deux encadrés du repli : le réseau mondial des ASTC, puis l'AFSAC dans ce
 * réseau. Ils sont détachés de la prose parce qu'ils changent de sujet — on passe
 * des exigences de l'OACI à qui les met en œuvre.
 */
$afsac_blocks = array(
	array(
		'label' => __( 'Réseau mondial · ASTC', 'afsac' ),
		'text'  => __( 'Les Centres de formation agréés par l’OACI pour la sûreté de l’aviation civile (ICAO Aviation Security Training Centres – ASTCs) représentent un élément essentiel du dispositif mondial de renforcement des capacités de l’OACI. Ils assurent la diffusion des programmes de formation reconnus par l’Organisation, contribuent au développement des compétences des professionnels de l’aviation civile et participent activement à l’amélioration de la conformité des États aux normes internationales.', 'afsac' ),
	),
	array(
		'label' => __( 'AFSAC – ICAO ASTC Tunis', 'afsac' ),
		'text'  => __( 'L’AFSAC – Centre Régional de Formation à la Sûreté de l’Aviation Civile de l’OACI de Tunis (ICAO ASTC Tunis), membre du réseau mondial OACI GAT TRAINAIR PLUS, s’inscrit pleinement dans cette mission en offrant des formations spécialisées destinées aux autorités de l’aviation civile, aux exploitants d’aéroports, aux compagnies aériennes et aux différents acteurs impliqués dans la chaîne de sûreté aérienne.', 'afsac' ),
	),
);
?>
<section class="afsac-section afsac-avsec-cadre">
	<div class="afsac-container">
		<?php
		get_template_part(
			'template-parts/shared/section-head',
			null,
			array(
				'eyebrow' => __( 'Cadre international', 'afsac' ),
				'title'   => __( 'La formation AVSEC au cœur des exigences internationales de l’OACI', 'afsac' ),
				'center'  => true,
			)
		);
		?>

		<?php /* .afsac-reveal sur le CONTENEUR : ce volet démarre masqué (onglet inactif). */ ?>
		<div class="afsac-avsec-cadre__prose afsac-reveal afsac-reveal--fade">

			<?php /* Seul paragraphe visible : ce que la sûreté de l'aviation civile protège. */ ?>
			<p class="afsac-avsec-cadre__lead"><?php esc_html_e( 'La sûreté de l’aviation civile constitue un pilier essentiel du système international de l’aviation civile et vise à protéger l’aviation contre les actes d’intervention illicite, tout en assurant la fluidité et l’efficacité des opérations aéroportuaires.', 'afsac' ); ?></p>

			<details class="afsac-more afsac-avsec-cadre__more">
				<summary class="afsac-more__toggle">
					<span class="afsac-more__label afsac-more__label--closed"><?php esc_html_e( 'Lire la suite', 'afsac' ); ?></span>
					<span class="afsac-more__label afsac-more__label--open"><?php esc_html_e( 'Réduire', 'afsac' ); ?></span>
					<svg class="afsac-more__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6 9l6 6 6-6"/></svg>
				</summary>

				<div class="afsac-more__body">
					<p class="afsac-avsec-cadre__text"><?php esc_html_e( 'Dans ce cadre, l’OACI accompagne les États dans la mise en œuvre des Normes et Pratiques Recommandées (SARPs) applicables notamment au titre de l’Annexe 17 – Sûreté, consacrée à la protection de l’aviation civile contre les actes d’intervention illicite, ainsi qu’au titre de l’Annexe 9 – Facilitation, qui encadre les mesures visant à faciliter le mouvement des aéronefs, des passagers, des équipages, des bagages, du fret et des documents, tout en intégrant les exigences pertinentes de sûreté.', 'afsac' ); ?></p>
					<p class="afsac-avsec-cadre__text"><?php esc_html_e( 'À travers son programme d’assistance et de renforcement des capacités, l’OACI contribue ainsi à permettre aux États de développer, mettre en œuvre et maintenir des systèmes nationaux efficaces de sûreté et de facilitation, conformément aux exigences internationales.', 'afsac' ); ?></p>

					<div class="afsac-avsec-cadre__blocks">
						<?php foreach ( $afsac_blocks as $afsac_block ) : ?>
							<div class="afsac-avsec-cadre__block">
								<span class="afsac-eyebrow"><?php echo esc_html( $afsac_block['label'] ); ?></span>
								<p class="afsac-avsec-cadre__text"><?php echo esc_html( $afsac_block['text'] ); ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</details>

		</div>
	</div>
</section>
