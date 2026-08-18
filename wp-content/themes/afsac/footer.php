<?php
/**
 * Pied de page : fermeture de la zone <main>, bande « Documentation »
 * (téléchargement du programme complet, sur toutes les pages), colonnes
 * (liens / contact / adresse / réseaux) puis barre basse (badges, copyright,
 * liens légaux).
 *
 * Présentation uniquement : données issues du Customizer, des menus et d'ACF
 * (cf. template-parts/footer/*). Aucun texte/lien en dur ici.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main><!-- #afsac-main -->

<footer class="afsac-site-footer" role="contentinfo">
	<?php
	get_template_part( 'template-parts/footer/documentation' );
	get_template_part( 'template-parts/footer/columns' );
	get_template_part( 'template-parts/footer/bottom' );
	?>
</footer>

<?php
// Accueil : guide flottant animé menant au catalogue des formations, et visite
// guidée (proposée automatiquement au visiteur qui découvre le site). Les deux
// ne se marchent pas dessus : le guide flottant est masqué pendant la visite.
if ( is_front_page() ) {
	get_template_part( 'template-parts/home/guide-flottant' );
	get_template_part( 'template-parts/shared/tour' );
}
?>

<?php wp_footer(); ?>
</body>
</html>
