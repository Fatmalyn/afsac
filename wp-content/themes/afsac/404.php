<?php
/**
 * Template d'erreur 404.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="afsac-container">
	<section class="afsac-error-404">
		<header class="afsac-page-header">
			<h1 class="afsac-entry-title"><?php esc_html_e( 'Page introuvable', 'afsac' ); ?></h1>
		</header>

		<p><?php esc_html_e( 'La page demandée n’existe pas ou a été déplacée.', 'afsac' ); ?></p>

		<p>
			<a class="afsac-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Retour à l’accueil', 'afsac' ); ?>
			</a>
		</p>

		<?php get_search_form(); ?>
	</section>
</div>

<?php
get_footer();
