<?php
/**
 * En-tête du site : barre utilitaire (haut) + barre principale, puis ouverture
 * de la zone <main>. Présent sur toutes les pages.
 *
 * Présentation uniquement : les coordonnées proviennent du Customizer et les
 * liens des menus (cf. template-parts/header/*). Aucun texte/lien en dur ici.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#afsac-main">
	<?php esc_html_e( 'Aller au contenu principal', 'afsac' ); ?>
</a>

<header class="afsac-site-header" role="banner">
	<?php
	get_template_part( 'template-parts/header/topbar' );
	get_template_part( 'template-parts/header/nav' );
	?>
</header>

<main id="afsac-main" class="afsac-site-main">
