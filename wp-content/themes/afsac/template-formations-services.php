<?php
/**
 * Template Name: AFSAC — Formations & Services
 *
 * HUB « Vue d'ensemble » de la rubrique Formations & Services (CDC §5.2).
 * SCAFFOLD : assemble 5 sections (stubs) — contenu/style ajoutés ensuite,
 * section par section. Données via les helpers afsac_get_fs_field() /
 * afsac_get_domains_count() (afsac-core). Aucune logique métier ici.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Fil d'Ariane Rank Math (si la fonctionnalité « Breadcrumbs » est activée dans
// RM). RM émet déjà son propre <nav aria-label="breadcrumbs"> : on l'enveloppe
// d'un <div> (pas d'un second landmark <nav>). Si RM ne renvoie rien (fonction
// désactivée), on n'émet aucun conteneur vide.
if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
	ob_start();
	rank_math_the_breadcrumbs();
	$afsac_crumbs = trim( ob_get_clean() );
	if ( '' !== $afsac_crumbs ) {
		echo '<div class="afsac-breadcrumb"><div class="afsac-container">' . $afsac_crumbs . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sortie Rank Math déjà assainie (wp_kses_post).
	}
}

$afsac_hero_eyebrow = function_exists( 'get_field' ) ? (string) get_field( 'afsac_page_eyebrow' ) : '';
if ( '' === $afsac_hero_eyebrow ) {
	$afsac_hero_eyebrow = __( 'Notre offre', 'afsac' );
}
$afsac_hero_chapo = function_exists( 'get_field' ) ? (string) get_field( 'afsac_page_chapo' ) : '';
if ( '' === $afsac_hero_chapo ) {
	$afsac_hero_chapo = __( 'Formations OACI certifiées et services d’accompagnement réglementaire.', 'afsac' );
}
$afsac_courses     = function_exists( 'afsac_get_fs_field' ) ? (int) afsac_get_fs_field( 'afsac_course_count' ) : 0;
$afsac_domains     = function_exists( 'afsac_get_domains_count' ) ? afsac_get_domains_count() : 0;
$afsac_cat_url     = function_exists( 'afsac_get_catalogue_url' ) ? afsac_get_catalogue_url() : '';
$afsac_contact_url = function_exists( 'afsac_get_contact_url' ) ? afsac_get_contact_url() : home_url( '/' );

/*
 * Hero VIDÉO (film client « Formations et services », 17/08/2026) : plein cadre
 * derrière le texte (layout overlay), contrairement à l'accueil qui montre sa
 * vidéo entière dans un panneau. Ici c'est une prise de vue réelle : le
 * recadrage ne détruit aucune composition, elle joue le rôle d'ambiance.
 * Remplace le hero clair guide-hero, dont le contenu est repris tel quel.
 */
get_template_part(
	'template-parts/shared/video-hero',
	null,
	array(
		'layout'        => 'overlay',
		'align'         => 'start',
		// Sens D'ORIGINE du plan (demande client 17/08) : le technicien reste à
		// gauche. Passer `mirror` à true le renvoie à droite, hors du texte.
		'mirror'        => false,
		'video_src'     => get_theme_file_uri( 'assets/video/formations-services.mp4' ),
		'poster'        => get_theme_file_uri( 'assets/images/fs-hero-poster.jpg' ),
		'eyebrow'       => $afsac_hero_eyebrow,
		'title'         => get_the_title( get_queried_object_id() ),
		'lead'          => $afsac_hero_chapo,
		'stats'         => array(
			array(
				'value' => number_format_i18n( $afsac_courses ),
				'label' => __( 'Cours standardisés', 'afsac' ),
			),
			array(
				'value' => number_format_i18n( $afsac_domains ),
				'label' => __( 'Domaines OACI', 'afsac' ),
			),
			array(
				'value' => number_format_i18n( 2 ),
				'label' => __( 'Langues · FR EN', 'afsac' ),
			),
		),
		'cta_primary'   => $afsac_cat_url ? array(
			'label' => __( 'Explorer le catalogue', 'afsac' ),
			'url'   => $afsac_cat_url,
		) : array(),
		'cta_secondary' => array(
			'label' => __( 'Demander une formation', 'afsac' ),
			'url'   => $afsac_contact_url,
		),
	)
);
/*
 * Retour client : « trop de répétition, trop de scroll ».
 * Deux sections ont été SUPPRIMÉES pour cette raison :
 *  - « intro » (carte « Notre approche ») : son chapô et ses 3 atouts répétaient
 *    mot pour mot le chapô et les statistiques du hero ;
 *  - « parcours » (« Trouvez votre formation en 3 étapes ») : retirée le
 *    05/08/2026, le parcours étant déjà porté par les CTA du hero et les deux
 *    piliers. La page enchaîne donc directement hero → piliers → services.
 */
get_template_part( 'template-parts/page-formations/pillars' );
get_template_part( 'template-parts/page-formations/services' );

get_footer();
