<?php
/**
 * Template Name: Espace participant
 *
 * Page d'ATTERRISSAGE (pas le système de comptes — module futur, cahier §10).
 * Hero éditorial partagé + toolkit de sections (feature-cards) annonçant les
 * fonctions à venir, et CTA utiles en attendant. noindex (contenu mince, géré
 * dans functions.php). Liens header/footer pointent déjà ici (Polylang).
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Hero overlay éditable (groupe ACF « En-tête de page »), avec replis.
$afsac_eyebrow = function_exists( 'get_field' ) ? (string) get_field( 'afsac_page_eyebrow' ) : '';
if ( '' === $afsac_eyebrow ) {
	$afsac_eyebrow = __( 'Espace participant', 'afsac' );
}
$afsac_chapo = function_exists( 'get_field' ) ? (string) get_field( 'afsac_page_chapo' ) : '';
if ( '' === $afsac_chapo ) {
	$afsac_chapo = __( 'Votre espace personnel arrive bientôt : suivez vos inscriptions, accédez à vos supports de cours et téléchargez vos attestations.', 'afsac' );
}
// Hero clair partagé (charte OACI blanc + bleu), identique aux autres pages internes.
get_template_part(
	'template-parts/shared/guide-hero',
	null,
	array(
		'eyebrow' => $afsac_eyebrow,
		'title'   => get_the_title( get_queried_object_id() ),
		'chapo'   => $afsac_chapo,
	)
);

// En-tête de la section « fonctions à venir » (éditable).
$afsac_intro_eyebrow = function_exists( 'get_field' ) ? (string) get_field( 'afsac_intro_eyebrow' ) : '';
if ( '' === $afsac_intro_eyebrow ) {
	$afsac_intro_eyebrow = __( 'Bientôt disponible', 'afsac' );
}
$afsac_intro_title = function_exists( 'get_field' ) ? (string) get_field( 'afsac_intro_title' ) : '';
if ( '' === $afsac_intro_title ) {
	$afsac_intro_title = __( 'Ce que votre espace vous offrira', 'afsac' );
}

// Fonctions à venir (icône SVG + titre + texte).
$afsac_features = array(
	array(
		'svg'   => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="M9 16l2 2 4-4"/>',
		'title' => __( 'Suivre mes inscriptions', 'afsac' ),
		'text'  => __( 'Retrouvez l’état de vos demandes d’inscription et vos sessions confirmées.', 'afsac' ),
	),
	array(
		'svg'   => '<path d="M4 5h13v14H4z"/><path d="M17 9h3v8a2 2 0 0 1-2 2h-1z"/><path d="M7 8h7M7 11h7M7 14h4"/>',
		'title' => __( 'Accéder aux supports de cours', 'afsac' ),
		'text'  => __( 'Téléchargez les supports pédagogiques de vos formations en cours.', 'afsac' ),
	),
	array(
		'svg'   => '<path d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/><circle cx="12" cy="8" r="0.5" fill="currentColor"/>',
		'title' => __( 'Télécharger mes attestations', 'afsac' ),
		'text'  => __( 'Obtenez vos certificats et attestations de fin de formation.', 'afsac' ),
	),
	array(
		'svg'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/>',
		'title' => __( 'Gérer mon profil', 'afsac' ),
		'text'  => __( 'Mettez à jour vos informations et vos préférences pédagogiques.', 'afsac' ),
	),
);

// CTA utiles en attendant (helpers Polylang).
$afsac_actions = array();
if ( function_exists( 'afsac_get_catalogue_url' ) && '' !== afsac_get_catalogue_url() ) {
	$afsac_actions[] = array( __( 'Voir le catalogue', 'afsac' ), afsac_get_catalogue_url() );
}
if ( function_exists( 'afsac_get_calendar_url' ) ) {
	$afsac_actions[] = array( __( 'Voir le calendrier', 'afsac' ), afsac_get_calendar_url() );
}
if ( function_exists( 'afsac_get_inscription_url' ) && '' !== afsac_get_inscription_url() ) {
	$afsac_actions[] = array( __( 'S’inscrire à une session', 'afsac' ), afsac_get_inscription_url() );
}
if ( function_exists( 'afsac_get_contact_url' ) ) {
	$afsac_actions[] = array( __( 'Nous contacter', 'afsac' ), afsac_get_contact_url() );
}
?>

<main id="primary" class="afsac-participant">

	<section class="afsac-section">
		<div class="afsac-container">
			<?php
			get_template_part(
				'template-parts/shared/section-head',
				null,
				array(
					'eyebrow' => $afsac_intro_eyebrow,
					'title'   => $afsac_intro_title,
					'center'  => true,
				)
			);
			?>
			<div class="afsac-feature-grid">
				<?php foreach ( $afsac_features as $afsac_f ) : ?>
					<article class="afsac-feature-card">
						<span class="afsac-feature-card__icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><?php echo $afsac_f['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Tracé SVG statique défini ci-dessus. ?></svg>
						</span>
						<h2 class="afsac-feature-card__title"><?php echo esc_html( $afsac_f['title'] ); ?></h2>
						<p class="afsac-feature-card__text"><?php echo esc_html( $afsac_f['text'] ); ?></p>
						<span class="afsac-feature-card__soon"><?php esc_html_e( 'Disponible prochainement', 'afsac' ); ?></span>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php if ( ! empty( $afsac_actions ) ) : ?>
		<section class="afsac-section afsac-section--light">
			<div class="afsac-container">
				<?php
				get_template_part(
					'template-parts/shared/section-head',
					null,
					array(
						'eyebrow' => __( 'En attendant', 'afsac' ),
						'title'   => __( 'Continuez votre parcours', 'afsac' ),
						'center'  => true,
					)
				);
				?>
				<div class="afsac-participant__actions">
					<?php foreach ( $afsac_actions as $afsac_i => $afsac_action ) : ?>
						<a class="afsac-button<?php echo 0 === $afsac_i ? '' : ' afsac-button--outline'; ?>" href="<?php echo esc_url( $afsac_action[1] ); ?>"><?php echo esc_html( $afsac_action[0] ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

</main>

<?php
get_footer();
