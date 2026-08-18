<?php
/**
 * Formations & Services — section 2 « Intro » (Notre offre).
 *
 * Bloc texte centré sur fond blanc, sous la bande hero. Le titre est le titre de
 * la page courante (traduit par Polylang) rendu en <h1> — premier vrai titre de
 * la page. Pas de SVG.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_intro_eyebrow = function_exists( 'get_field' ) ? (string) get_field( 'afsac_intro_eyebrow' ) : '';
if ( '' === $afsac_intro_eyebrow ) {
	$afsac_intro_eyebrow = __( 'Notre approche', 'afsac' );
}
$afsac_intro_title = function_exists( 'get_field' ) ? (string) get_field( 'afsac_intro_title' ) : '';
if ( '' === $afsac_intro_title ) {
	$afsac_intro_title = __( 'Former, conseiller, accompagner', 'afsac' );
}
?>
<section class="afsac-section afsac-section--center afsac-fs-intro">
	<div class="afsac-container">
		<div class="afsac-intro-card afsac-reveal">
			<div class="afsac-intro-card__head">
				<span class="afsac-eyebrow"><?php echo esc_html( $afsac_intro_eyebrow ); ?></span>
				<h2 class="afsac-intro-card__title"><?php echo esc_html( $afsac_intro_title ); ?></h2>
			</div>
			<div class="afsac-fs-intro__body">
				<p class="afsac-fs-intro__text"><?php esc_html_e( 'En tant que centre régional de formation OACI, l\'AFSAC déploie une double offre : un catalogue complet de formations TRAINAIR PLUS & AVSEC sur les onze domaines aéronautiques, et un portefeuille de services de conseil et d\'accompagnement réglementaire.', 'afsac' ); ?></p>
			</div>
			<div class="afsac-intro-atouts">
				<span class="afsac-intro-atouts__item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/></svg> <?php esc_html_e( 'Certifié OACI', 'afsac' ); ?></span>
				<span class="afsac-intro-atouts__item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg> <?php esc_html_e( '11 domaines OACI', 'afsac' ); ?></span>
				<span class="afsac-intro-atouts__item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 2.5 13.4 0 18M12 3c-2.5 2.6-2.5 13.4 0 18"/></svg> <?php esc_html_e( 'FR · EN', 'afsac' ); ?></span>
			</div>
		</div>
	</div>
</section>
