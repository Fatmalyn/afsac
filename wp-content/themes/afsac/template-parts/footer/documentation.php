<?php
/**
 * Footer — bande « Documentation » (téléchargement de la brochure).
 *
 * Présente sur TOUTES les pages : incluse depuis footer.php, en tête du pied de
 * page.
 *
 * DEPUIS LE 08/09/2026 (demande client) : TÉLÉCHARGEMENT DIRECT, EN UN CLIC.
 * Plus de fenêtre modale, plus de champ e-mail, plus de formulaire : la carte
 * est un simple lien qui sert le PDF. Le panneau « Recevoir la brochure » du
 * 14/08/2026 et le formulaire d'une ligne qui l'a brièvement remplacé ont été
 * retirés ; ne pas les remettre.
 *
 * L'ÉDITION SUIT LA LANGUE DE NAVIGATION, sans sélecteur : page FR → brochure
 * française, page EN → brochure anglaise (afsac_brochure_default_key(), qui
 * retombe sur le PDF combiné puis sur la première édition disponible).
 *
 * Le lien ne pointe pas sur le fichier lui-même mais sur le point de
 * téléchargement du plugin (afsac_brochure_direct_url()) : le PDF est servi en
 * pièce jointe (jamais ouvert dans l'onglet) et chaque envoi est COMPTÉ, sans
 * aucune donnée personnelle (compteur anonyme par édition, visible dans
 * Téléchargements). Champ vide (aucun PDF téléversé) → la bande n'est pas rendue
 * (jamais de bouton qui ne mène à rien). Les fichiers restent réglés une seule
 * fois sur la home CANONIQUE (langue par défaut) : ce sont des pièces jointes,
 * pas du contenu traduit.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'afsac_brochure_files' ) || ! function_exists( 'afsac_brochure_direct_url' ) ) {
	return; // Plugin afsac-core désactivé ou trop ancien.
}

$afsac_files = afsac_brochure_files();
if ( ! $afsac_files ) {
	return; // Aucun PDF téléversé : pas de bande.
}

// --- Édition servie : celle de la LANGUE COURANTE ---------------------------
$afsac_key  = afsac_brochure_default_key();
$afsac_file = isset( $afsac_files[ $afsac_key ] ) ? $afsac_files[ $afsac_key ] : reset( $afsac_files );
$afsac_size = ! empty( $afsac_file['filesize'] ) ? size_format( (int) $afsac_file['filesize'] ) : '';

if ( $afsac_size ) {
	/* translators: %s: taille du fichier (ex. « 4 Mo »). */
	$afsac_meta = sprintf( __( 'PDF · %s', 'afsac' ), $afsac_size );
	/* translators: 1: langue de l'édition (ex. « Français »), 2: taille du fichier (ex. « 4 Mo »). */
	$afsac_a11y = sprintf( __( 'Télécharger la brochure AFSAC en %1$s (PDF, %2$s)', 'afsac' ), $afsac_file['name'], $afsac_size );
} else {
	$afsac_meta = 'PDF';
	/* translators: %s: langue de l'édition (ex. « Français »). */
	$afsac_a11y = sprintf( __( 'Télécharger la brochure AFSAC en %s (PDF)', 'afsac' ), $afsac_file['name'] );
}
?>
<section class="afsac-docband" id="afsac-brochure" aria-labelledby="afsac-docband-title">
	<div class="afsac-container afsac-docband__inner">

		<div class="afsac-docband__intro afsac-reveal">
			<p class="afsac-eyebrow afsac-docband__eyebrow"><?php esc_html_e( 'Documentation', 'afsac' ); ?></p>
			<h2 id="afsac-docband-title" class="afsac-docband__title"><?php esc_html_e( 'Téléchargez la brochure', 'afsac' ); ?></h2>
			<p class="afsac-docband__text"><?php esc_html_e( 'Le catalogue complet des formations AFSAC en un seul PDF : programmes TRAINAIR PLUS sur les 11 domaines de l’OACI et cursus AVSEC.', 'afsac' ); ?></p>
		</div>

		<?php /* La carte entière est le lien de téléchargement (édition de la langue courante). */ ?>
		<a class="afsac-docband__card afsac-reveal" href="<?php echo esc_url( afsac_brochure_direct_url( $afsac_key ) ); ?>" download aria-label="<?php echo esc_attr( $afsac_a11y ); ?>">
			<span class="afsac-docband__stack" aria-hidden="true">
				<span class="afsac-docband__sheet afsac-docband__sheet--3"></span>
				<span class="afsac-docband__sheet afsac-docband__sheet--2"></span>
				<span class="afsac-docband__sheet afsac-docband__sheet--1">
					<span class="afsac-docband__sheet-tag">PDF</span>
				</span>
			</span>

			<span class="afsac-docband__body">
				<span class="afsac-docband__card-eyebrow"><?php esc_html_e( 'Programme de formation', 'afsac' ); ?></span>
				<span class="afsac-docband__card-title"><?php esc_html_e( 'Brochure AFSAC', 'afsac' ); ?></span>
				<span class="afsac-docband__card-meta"><?php echo esc_html( $afsac_meta ); ?> · <?php echo esc_html( $afsac_file['name'] ); ?></span>
			</span>

			<span class="afsac-docband__cta">
				<span class="afsac-docband__cta-label"><?php esc_html_e( 'Télécharger', 'afsac' ); ?></span>
				<span class="afsac-docband__cta-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/></svg>
				</span>
			</span>
		</a>

	</div>
</section>
