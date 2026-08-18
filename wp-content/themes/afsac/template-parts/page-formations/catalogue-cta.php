<?php
/**
 * Formations & Services — section « Catalogue CTA » (bandeau de téléchargement).
 *
 * Bandeau pleine largeur (fond accent) : couverture (placeholder), texte avec
 * compteurs dynamiques (cours/domaines via helpers afsac-core) et bouton de
 * téléchargement du PDF (lien réel si le champ ACF afsac_catalogue_pdf est
 * renseigné, sinon placeholder href="#").
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_courses = function_exists( 'afsac_get_hub_field' ) ? (int) afsac_get_hub_field( 'afsac_course_count' ) : 0;
$afsac_areas   = function_exists( 'afsac_get_domains_count' ) ? afsac_get_domains_count() : 0;

$afsac_pdf     = function_exists( 'afsac_get_hub_field' ) ? afsac_get_hub_field( 'afsac_catalogue_pdf' ) : '';
$afsac_pdf_url = $afsac_pdf ? ( is_array( $afsac_pdf ) ? $afsac_pdf['url'] : $afsac_pdf ) : '#';
?>
<section class="afsac-fs-catalogue">
	<div class="afsac-container">
		<div class="afsac-fs-catalogue__cover" aria-hidden="true">
			<?php // TODO : remplacer ce placeholder par la vraie image de couverture du catalogue. ?>
			<span>AFSAC</span>
			<span>CATALOGUE</span>
			<span>FORMATION</span>
			<span>2026</span>
		</div>
		<div class="afsac-fs-catalogue__text">
			<h2 class="afsac-fs-catalogue__title"><?php esc_html_e( 'Téléchargez notre catalogue', 'afsac' ); ?></h2>
			<p class="afsac-fs-catalogue__desc">
				<?php
				printf(
					/* translators: 1: number of courses, 2: number of ICAO areas */
					esc_html__( 'Catalogue digital complet 2026 : %1$s cours TRAINAIR PLUS sur les %2$s domaines OACI, formations AVSEC en français et en anglais, prestations de conseil et d\'audit.', 'afsac' ),
					number_format_i18n( $afsac_courses ),
					number_format_i18n( $afsac_areas )
				);
				?>
			</p>
		</div>
		<?php if ( '#' !== $afsac_pdf_url ) : ?>
			<a class="afsac-button afsac-button--invert" href="<?php echo esc_url( $afsac_pdf_url ); ?>" target="_blank" rel="noopener">
				<?php esc_html_e( 'Télécharger le PDF', 'afsac' ); ?>
			</a>
		<?php else : ?>
			<a class="afsac-button afsac-button--invert" href="<?php echo esc_url( function_exists( 'afsac_get_contact_url' ) ? afsac_get_contact_url() : home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Demander le catalogue', 'afsac' ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>
