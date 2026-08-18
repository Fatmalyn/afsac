<?php
/**
 * Gestion RTL pour l'arabe.
 *
 * Quand la langue active est en écriture droite-à-gauche (arabe), on garantit
 * deux choses :
 *   1. l'attribut dir="rtl" sur la balise <html> ;
 *   2. le chargement d'une feuille de style RTL conditionnelle.
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Force dir="rtl" et l'attribut lang sur la balise <html> en contexte RTL.
 *
 * WordPress positionne déjà dir="rtl" via language_attributes() lorsque la
 * locale est RTL (cas de l'arabe). Ce filtre sécurise le comportement même si
 * un thème construit l'attribut autrement, et n'altère rien hors RTL.
 *
 * @param string $output Chaîne d'attributs générée par language_attributes().
 * @return string
 */
function afsac_force_rtl_html_attributes( $output ) {
	if ( ! is_rtl() ) {
		return $output;
	}

	if ( false === strpos( $output, 'dir=' ) ) {
		$output .= ' dir="rtl"';
	}

	return $output;
}
add_filter( 'language_attributes', 'afsac_force_rtl_html_attributes' );

/**
 * Charge la feuille de style RTL uniquement en contexte droite-à-gauche.
 *
 * Chargée après les styles du thème (dépendance non bloquante) pour pouvoir
 * surcharger les marges/flottants. Versionnée sur AFSAC_CORE_VERSION pour le
 * cache-busting.
 *
 * @return void
 */
function afsac_enqueue_rtl_styles() {
	if ( ! is_rtl() ) {
		return;
	}

	wp_enqueue_style(
		'afsac-rtl',
		AFSAC_CORE_URL . 'assets/css/rtl.css',
		array(),
		AFSAC_CORE_VERSION,
		'all'
	);
}
add_action( 'wp_enqueue_scripts', 'afsac_enqueue_rtl_styles', 20 );
