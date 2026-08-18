<?php
/**
 * Section PARTAGÉE : la VISITE GUIDÉE (onboarding de première visite).
 *
 * Ne rend qu'une COQUILLE : le voile, le halo et la bulle, tous vides. Le
 * contenu (titres, textes, cibles) est passé en JSON juste à côté, et c'est
 * assets/js/afsac-tour.js qui l'injecte étape par étape.
 *
 * Pourquoi du JSON dans la page plutôt qu'un wp_localize_script() : la visite
 * n'est rendue que là où elle a du sens (l'accueil), et ses étapes se lisent
 * ainsi au même endroit que le balisage qu'elles désignent.
 *
 * Sans JavaScript, RIEN ne s'affiche (la coquille est `hidden`) : une visite
 * guidée est un confort, jamais un passage obligé. Aucun contenu du site n'est
 * enfermé ici — chaque étape ne fait que désigner un élément déjà présent.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'afsac_tour_enabled' ) || ! afsac_tour_enabled() ) {
	return;
}

$afsac_tour_steps = afsac_tour_steps();
if ( empty( $afsac_tour_steps ) ) {
	return;
}

// Le script n'est qu'ENREGISTRÉ dans functions.php : il n'est enfilé que si la
// visite est réellement rendue (même logique que la section « Shorts »).
wp_enqueue_script( 'afsac-tour' );

/* translators: %1$s : numéro de l'étape courante, %2$s : nombre total d'étapes. */
$afsac_tour_count = __( 'Étape %1$s sur %2$s', 'afsac' );
?>
<div
	class="afsac-tour"
	data-afsac-tour
	data-tour-key="afsac-tour-v<?php echo esc_attr( AFSAC_TOUR_VERSION ); ?>"
	data-tour-autostart="1"
	data-tour-delay="1400"
	data-count-tpl="<?php echo esc_attr( $afsac_tour_count ); ?>"
	hidden
>
	<?php
	/*
	 * Étapes. JSON_HEX_TAG interdit qu'un « < » traduit ne referme ce <script>.
	 */
	?>
	<script type="application/json" data-afsac-tour-steps>
		<?php echo wp_json_encode( $afsac_tour_steps, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON encodé avec échappement des chevrons. ?>
	</script>

	<div class="afsac-tour__veil" data-tour-veil></div>
	<div class="afsac-tour__spot" data-tour-spot aria-hidden="true"></div>

	<div
		class="afsac-tour__pop"
		data-tour-pop
		role="dialog"
		aria-modal="true"
		aria-labelledby="afsac-tour-title"
		tabindex="-1"
	>
		<span class="afsac-tour__arrow" data-tour-arrow aria-hidden="true"></span>

		<button type="button" class="afsac-tour__close" data-tour-close aria-label="<?php esc_attr_e( 'Fermer la visite guidée', 'afsac' ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
		</button>

		<p class="afsac-tour__count" data-tour-count></p>
		<h2 class="afsac-tour__title" id="afsac-tour-title" data-tour-title></h2>
		<div class="afsac-tour__text" data-tour-text></div>

		<a class="afsac-tour__cta" data-tour-cta hidden></a>

		<div class="afsac-tour__foot">
			<ol class="afsac-tour__dots" data-tour-dots aria-hidden="true"></ol>

			<div class="afsac-tour__actions">
				<button type="button" class="afsac-tour__btn afsac-tour__btn--quiet" data-tour-skip>
					<?php esc_html_e( 'Passer', 'afsac' ); ?>
				</button>
				<button type="button" class="afsac-tour__btn" data-tour-prev>
					<?php esc_html_e( 'Précédent', 'afsac' ); ?>
				</button>
				<?php
				/*
				 * Un seul bouton d'avancement, dont le libellé suit l'étape :
				 * « Démarrer la visite » à l'accueil, « Suivant » en cours de
				 * route, « Terminer » à la dernière. Les trois valeurs sont
				 * traduites ici ; le script ne fait que les recopier.
				 */
				?>
				<button
					type="button"
					class="afsac-tour__btn afsac-tour__btn--primary"
					data-tour-next
					data-label-start="<?php esc_attr_e( 'Démarrer la visite', 'afsac' ); ?>"
					data-label-next="<?php esc_attr_e( 'Suivant', 'afsac' ); ?>"
					data-label-done="<?php esc_attr_e( 'Terminer', 'afsac' ); ?>"
				>
					<?php esc_html_e( 'Suivant', 'afsac' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
