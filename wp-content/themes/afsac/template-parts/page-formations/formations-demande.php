<?php
/**
 * Calendrier — section « Formations spécifiques, à la demande ».
 *
 * Demande client (rapport du 19/06/2026) : « Prévoir une section dédiée aux
 * formations spécifiques, accompagnée d'un bouton “Autres”. »
 *
 * Contenu issu de la brochure officielle « Programme des Formations AVSEC/OACI
 * 2026 » (§2 « Formation à la demande »). Liste ÉDITORIALE pour l'instant : ces
 * ateliers ne sont pas des sessions programmées (pas de date), donc pas de CPT
 * afsac_session. Ils pourront devenir des CPT afsac_formation (modalité
 * « Sur-mesure ») si le client veut les éditer depuis l'admin.
 *
 * Identité visuelle bleu + ORANGE, comme le bloc AVSEC du catalogue.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_contact_url   = function_exists( 'afsac_get_contact_url' ) ? afsac_get_contact_url() : home_url( '/' );
$afsac_catalogue_url = function_exists( 'afsac_get_catalogue_url' ) ? afsac_get_catalogue_url() : '';

$afsac_ateliers = array(
	array(
		'title' => __( 'Gestion de crise en sûreté de l’aviation civile', 'afsac' ),
		'days'  => 5,
	),
	array(
		'title' => __( 'Culture de la sûreté (classe virtuelle)', 'afsac' ),
		'days'  => 5,
	),
	array(
		'title' => __( 'Programme National de Formation en Sûreté (PNFSAC)', 'afsac' ),
		'days'  => 5,
	),
	array(
		'title' => __( 'Programme de Contrôle Qualité en Sûreté (PCQSAC)', 'afsac' ),
		'days'  => 5,
	),
	array(
		'title' => __( 'Programme de Sûreté de l’Aéroport (PSA)', 'afsac' ),
		'days'  => 5,
	),
	array(
		'title' => __( 'Systèmes de Certification en Sûreté de l’Aviation', 'afsac' ),
		'days'  => 5,
	),
	array(
		'title' => __( 'Gestion des risques', 'afsac' ),
		'days'  => 5,
	),
	array(
		'title' => __( 'Recyclage des inspecteurs nationaux', 'afsac' ),
		'days'  => 3,
	),
	array(
		'title' => __( 'Recyclage des instructeurs nationaux', 'afsac' ),
		'days'  => 3,
	),
);
?>
<section class="afsac-ondemand">
	<svg class="afsac-ondemand__deco" viewBox="0 0 400 400" aria-hidden="true" focusable="false">
		<g fill="none" stroke="currentColor" stroke-width="1.4" opacity="0.5">
			<circle cx="200" cy="200" r="70"/>
			<circle cx="200" cy="200" r="120"/>
			<circle cx="200" cy="200" r="175"/>
		</g>
		<circle class="afsac-ondemand__pulse" cx="200" cy="200" r="62"/>
		<circle class="afsac-ondemand__pulse afsac-ondemand__pulse--2" cx="200" cy="200" r="62"/>
	</svg>

	<div class="afsac-container">
		<div class="afsac-ondemand__head afsac-reveal">
			<span class="afsac-eyebrow afsac-ondemand__eyebrow"><?php esc_html_e( 'Hors calendrier', 'afsac' ); ?></span>
			<h2 class="afsac-ondemand__title"><?php esc_html_e( 'Formations spécifiques, à la demande', 'afsac' ); ?></h2>
			<p class="afsac-ondemand__lead"><?php esc_html_e( 'Au-delà des sessions programmées, l’AFSAC organise des formations et ateliers sur mesure selon les besoins de votre État ou de votre organisation — dans nos locaux à Tunis ou sur votre site.', 'afsac' ); ?></p>
		</div>

		<div class="afsac-ondemand__grid afsac-stagger">
			<?php foreach ( $afsac_ateliers as $afsac_a ) : ?>
				<div class="afsac-ondemand__item afsac-reveal">
					<span class="afsac-ondemand__item-title"><?php echo esc_html( $afsac_a['title'] ); ?></span>
					<span class="afsac-ondemand__item-days">
						<?php
						printf(
							/* translators: %s: number of days. */
							esc_html( _n( '%s j', '%s j', $afsac_a['days'], 'afsac' ) ),
							esc_html( number_format_i18n( $afsac_a['days'] ) )
						);
						?>
					</span>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="afsac-ondemand__actions afsac-reveal">
			<?php if ( '' !== $afsac_catalogue_url ) : ?>
				<a class="afsac-button afsac-ondemand__cta" href="<?php echo esc_url( $afsac_catalogue_url ); ?>">
					<?php esc_html_e( 'Autres formations', 'afsac' ); ?>
					<span class="afsac-arrow" aria-hidden="true">&rarr;</span>
				</a>
			<?php endif; ?>
			<a class="afsac-button afsac-button--ghost" href="<?php echo esc_url( $afsac_contact_url ); ?>">
				<?php esc_html_e( 'Demander une session sur mesure', 'afsac' ); ?>
			</a>
		</div>
	</div>
</section>
