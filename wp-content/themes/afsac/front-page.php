<?php
/**
 * Page d'accueil — assemblage par blocs.
 *
 * Ordre : hero (+ barre de stats) → intro → carrousel formations → pourquoi
 * nous choisir → partenaires.
 *
 * La section « Shorts » (vidéos verticales de la chaîne YouTube) vit sur la
 * page CONTACT, pas ici — demande client du 12/08/2026.
 *
 * La section « Témoignages » a été retirée de l'accueil à la demande du client
 * (réunion 04/08/2026). Les témoignages restent publiés sur la page
 * « Références & Témoignages » (template-references-temoignages.php).
 *
 * Le « Mot du Directeur Général » a été déplacé vers la page « Qui sommes-nous »
 * (template-qui-sommes-nous.php) à la demande du client.
 *
 * La section « Documentation » (téléchargement du programme) vit désormais dans
 * le pied de page (template-parts/footer/documentation.php) : elle est ainsi
 * présente sur toutes les pages, pas seulement l'accueil.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/home/hero' );
get_template_part( 'template-parts/home/intro' );
get_template_part( 'template-parts/home/formations-carousel' );
get_template_part( 'template-parts/home/pourquoi' );
get_template_part( 'template-parts/home/partenaires' );

get_footer();
