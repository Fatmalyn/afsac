<?php
/**
 * Footer — bande « Documentation » (téléchargement de la brochure).
 *
 * Présente sur TOUTES les pages : incluse depuis footer.php, en tête du pied de
 * page.
 *
 * DEPUIS 08/2026 (demande client) : la carte ne pointe PLUS sur le PDF. Elle
 * ouvre le panneau « Recevoir la brochure » (template-parts/shared/brochure-gate),
 * qui demande l'e-mail avant de délivrer le fichier — AFSAC sait ainsi qui a
 * demandé la brochure et combien de personnes l'ont réellement téléchargée
 * (CPT afsac_telechargement, cf. plugin includes/brochure.php).
 * Ne pas remettre de `href` vers le fichier : l'URL du PDF circulerait et le
 * comptage perdrait tout son sens.
 *
 * La résolution des fichiers vit maintenant dans le PLUGIN
 * (afsac_brochure_files() / afsac_brochure_default_key()) : le point de
 * téléchargement et l'affichage doivent désigner exactement le même fichier.
 * Champ vide → la bande n'est pas rendue (jamais de porte ouvrant sur rien).
 *
 * Les fichiers restent réglés une seule fois sur la home CANONIQUE (langue par
 * défaut) : ce sont des pièces jointes, pas du contenu traduit. L'édition
 * proposée par défaut suit la langue de navigation ; le visiteur peut en changer
 * dans le panneau quand plusieurs éditions existent.
 *
 * L'autonyme de langue (FR / EN) reste LITTÉRAL : c'est un libellé de contenu,
 * pas une chaîne d'interface à traduire. Depuis le 17/08/2026, l'édition arabe
 * n'est plus proposée (demande client) : la liste vient d'afsac_brochure_langs(),
 * il n'y a donc rien à retirer ici.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'afsac_brochure_files' ) ) {
	return; // Plugin afsac-core désactivé.
}

$afsac_files = afsac_brochure_files();
if ( ! $afsac_files ) {
	return; // Aucun PDF téléversé : pas de bande.
}

$afsac_key  = afsac_brochure_default_key();
$afsac_file = isset( $afsac_files[ $afsac_key ] ) ? $afsac_files[ $afsac_key ] : reset( $afsac_files );
$afsac_size = ! empty( $afsac_file['filesize'] ) ? size_format( (int) $afsac_file['filesize'] ) : '';

if ( $afsac_size ) {
	/* translators: %s: taille du fichier PDF (ex. « 4 Mo »). */
	$afsac_a11y = sprintf( __( 'Recevoir la brochure AFSAC (PDF, %s) — un e-mail vous sera demandé', 'afsac' ), $afsac_size );
} else {
	$afsac_a11y = __( 'Recevoir la brochure AFSAC (PDF) — un e-mail vous sera demandé', 'afsac' );
}

/*
 * Pastilles des éditions RÉELLEMENT disponibles. Autonymes LITTÉRAUX, jamais
 * traduits.
 */
$afsac_langs = array();
foreach ( $afsac_files as $afsac_f ) {
	$afsac_langs[] = $afsac_f;
}
?>
<section class="afsac-docband" aria-labelledby="afsac-docband-title">
	<div class="afsac-container afsac-docband__inner">

		<div class="afsac-docband__intro afsac-reveal">
			<p class="afsac-eyebrow afsac-docband__eyebrow"><?php esc_html_e( 'Documentation', 'afsac' ); ?></p>
			<h2 id="afsac-docband-title" class="afsac-docband__title"><?php esc_html_e( 'Téléchargez la brochure', 'afsac' ); ?></h2>
			<p class="afsac-docband__text"><?php esc_html_e( 'Le catalogue complet des formations AFSAC en un seul PDF : programmes TRAINAIR PLUS sur les 11 domaines de l’OACI et cursus AVSEC.', 'afsac' ); ?></p>
			<?php if ( ! empty( $afsac_langs ) ) : ?>
				<ul class="afsac-docband__langs" aria-label="<?php esc_attr_e( 'Éditions disponibles', 'afsac' ); ?>">
					<?php foreach ( $afsac_langs as $afsac_l ) : ?>
						<li class="afsac-docband__lang"<?php echo $afsac_l['rtl'] ? ' dir="rtl"' : ''; ?>>
							<span class="afsac-docband__lang-code"><?php echo esc_html( $afsac_l['code'] ); ?></span>
							<span class="afsac-docband__lang-name"><?php echo esc_html( $afsac_l['name'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<?php
		/*
		 * Déclencheur du panneau. Sans JavaScript, l'ancre #afsac-brochure mène au
		 * formulaire rendu juste en dessous ; avec JavaScript, le script intercepte
		 * le clic et ouvre la fenêtre modale. C'est un lien (et non un bouton) pour
		 * que ce repli fonctionne.
		 */
		?>
		<a class="afsac-docband__card afsac-reveal" href="#afsac-brochure" data-bgate-trigger aria-label="<?php echo esc_attr( $afsac_a11y ); ?>">
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
				<span class="afsac-docband__card-meta">
					<?php
					if ( $afsac_size ) {
						/* translators: %s: taille du fichier (ex. « 4 Mo »). */
						echo esc_html( sprintf( __( 'PDF · %s · e-mail demandé', 'afsac' ), $afsac_size ) );
					} else {
						esc_html_e( 'PDF · e-mail demandé', 'afsac' );
					}
					?>
				</span>
			</span>

			<span class="afsac-docband__cta">
				<span class="afsac-docband__cta-label"><?php esc_html_e( 'Recevoir', 'afsac' ); ?></span>
				<span class="afsac-docband__cta-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/></svg>
				</span>
			</span>
		</a>

	</div>

	<?php
	/*
	 * Le conteneur ne sert QU'AU repli sans JavaScript (marges du panneau en
	 * pleine page) : avec JavaScript, le script déplace le panneau dans <body>
	 * pour qu'aucun `overflow` d'ancêtre ne rogne la fenêtre modale, et ce
	 * conteneur reste vide (hauteur nulle).
	 */
	?>
	<div class="afsac-container afsac-bgate-holder">
		<?php get_template_part( 'template-parts/shared/brochure-gate' ); ?>
	</div>
</section>
