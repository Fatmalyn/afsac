<?php
/**
 * Enregistrement des types de contenu personnalisés (CPT).
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre tous les CPT du site AFSAC.
 *
 * @return void
 */
function afsac_register_post_types() {
	afsac_register_cpt_formation();
	afsac_register_cpt_session();
	afsac_register_cpt_temoignage();
	afsac_register_cpt_reference();
	afsac_register_cpt_service();
	afsac_register_cpt_inscription();
}
add_action( 'init', 'afsac_register_post_types' );

/**
 * CPT « Formation ».
 *
 * Produit principal : un cours / programme de formation. Archive activée
 * (catalogue), exposé à l'API REST pour Gutenberg, traduisible via Polylang.
 *
 * @return void
 */
function afsac_register_cpt_formation() {
	$labels = array(
		'name'                  => _x( 'Formations', 'Nom général du CPT', 'afsac' ),
		'singular_name'         => _x( 'Formation', 'Nom singulier du CPT', 'afsac' ),
		'menu_name'             => __( 'Formations', 'afsac' ),
		'name_admin_bar'        => __( 'Formation', 'afsac' ),
		'add_new'               => __( 'Ajouter', 'afsac' ),
		'add_new_item'          => __( 'Ajouter une formation', 'afsac' ),
		'new_item'              => __( 'Nouvelle formation', 'afsac' ),
		'edit_item'             => __( 'Modifier la formation', 'afsac' ),
		'view_item'             => __( 'Voir la formation', 'afsac' ),
		'all_items'             => __( 'Toutes les formations', 'afsac' ),
		'search_items'          => __( 'Rechercher une formation', 'afsac' ),
		'not_found'             => __( 'Aucune formation trouvée.', 'afsac' ),
		'not_found_in_trash'    => __( 'Aucune formation dans la corbeille.', 'afsac' ),
		'featured_image'        => __( 'Visuel de la formation', 'afsac' ),
		'set_featured_image'    => __( 'Définir le visuel', 'afsac' ),
		'remove_featured_image' => __( 'Retirer le visuel', 'afsac' ),
		'archives'              => __( 'Catalogue des formations', 'afsac' ),
		'item_published'        => __( 'Formation publiée.', 'afsac' ),
		'item_updated'          => __( 'Formation mise à jour.', 'afsac' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'has_archive'        => true,
		'rewrite'            => array(
			'slug'       => 'formations',
			'with_front' => false,
		),
		'menu_icon'          => 'dashicons-welcome-learn-more',
		'menu_position'      => 20,
		'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes', 'custom-fields' ),
		'show_in_rest'       => true,
		'rest_base'          => 'formations',
		'hierarchical'       => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'publicly_queryable' => true,
	);

	register_post_type( 'afsac_formation', $args );
}

/**
 * CPT « Session ».
 *
 * Occurrence planifiée d'une formation (date / lieu / langue). Reliée à une
 * formation via un champ méta (voir includes/relations.php). Archive activée
 * pour lister le calendrier global.
 *
 * @return void
 */
function afsac_register_cpt_session() {
	$labels = array(
		'name'               => _x( 'Sessions', 'Nom général du CPT', 'afsac' ),
		'singular_name'      => _x( 'Session', 'Nom singulier du CPT', 'afsac' ),
		'menu_name'          => __( 'Sessions', 'afsac' ),
		'name_admin_bar'     => __( 'Session', 'afsac' ),
		'add_new'            => __( 'Ajouter', 'afsac' ),
		'add_new_item'       => __( 'Ajouter une session', 'afsac' ),
		'new_item'           => __( 'Nouvelle session', 'afsac' ),
		'edit_item'          => __( 'Modifier la session', 'afsac' ),
		'view_item'          => __( 'Voir la session', 'afsac' ),
		'all_items'          => __( 'Toutes les sessions', 'afsac' ),
		'search_items'       => __( 'Rechercher une session', 'afsac' ),
		'not_found'          => __( 'Aucune session trouvée.', 'afsac' ),
		'not_found_in_trash' => __( 'Aucune session dans la corbeille.', 'afsac' ),
		'archives'           => __( 'Calendrier des sessions', 'afsac' ),
		'item_published'     => __( 'Session publiée.', 'afsac' ),
		'item_updated'       => __( 'Session mise à jour.', 'afsac' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'has_archive'        => true,
		'rewrite'            => array(
			'slug'       => 'sessions',
			'with_front' => false,
		),
		'menu_icon'          => 'dashicons-calendar-alt',
		'menu_position'      => 21,
		'supports'           => array( 'title', 'editor', 'custom-fields' ),
		'show_in_rest'       => true,
		'rest_base'          => 'sessions',
		'hierarchical'       => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => false,
		'publicly_queryable' => true,
	);

	register_post_type( 'afsac_session', $args );
}

/**
 * CPT « Témoignage ».
 *
 * Avis client pour la réassurance. Pas d'archive publique (affiché via blocs /
 * requêtes dans les pages éditoriales).
 *
 * @return void
 */
function afsac_register_cpt_temoignage() {
	$labels = array(
		'name'               => _x( 'Témoignages', 'Nom général du CPT', 'afsac' ),
		'singular_name'      => _x( 'Témoignage', 'Nom singulier du CPT', 'afsac' ),
		'menu_name'          => __( 'Témoignages', 'afsac' ),
		'name_admin_bar'     => __( 'Témoignage', 'afsac' ),
		'add_new'            => __( 'Ajouter', 'afsac' ),
		'add_new_item'       => __( 'Ajouter un témoignage', 'afsac' ),
		'new_item'           => __( 'Nouveau témoignage', 'afsac' ),
		'edit_item'          => __( 'Modifier le témoignage', 'afsac' ),
		'view_item'          => __( 'Voir le témoignage', 'afsac' ),
		'all_items'          => __( 'Tous les témoignages', 'afsac' ),
		'search_items'       => __( 'Rechercher un témoignage', 'afsac' ),
		'not_found'          => __( 'Aucun témoignage trouvé.', 'afsac' ),
		'not_found_in_trash' => __( 'Aucun témoignage dans la corbeille.', 'afsac' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'has_archive'        => false,
		'rewrite'            => array(
			'slug'       => 'temoignages',
			'with_front' => false,
		),
		'menu_icon'          => 'dashicons-format-quote',
		'menu_position'      => 22,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		'show_in_rest'       => true,
		'rest_base'          => 'temoignages',
		'hierarchical'       => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => false,
		'publicly_queryable' => true,
	);

	register_post_type( 'afsac_temoignage', $args );
}

/**
 * CPT « Référence ».
 *
 * Client / partenaire institutionnel (logos, études de cas). Pas d'archive
 * publique par défaut.
 *
 * @return void
 */
function afsac_register_cpt_reference() {
	$labels = array(
		'name'               => _x( 'Références', 'Nom général du CPT', 'afsac' ),
		'singular_name'      => _x( 'Référence', 'Nom singulier du CPT', 'afsac' ),
		'menu_name'          => __( 'Références', 'afsac' ),
		'name_admin_bar'     => __( 'Référence', 'afsac' ),
		'add_new'            => __( 'Ajouter', 'afsac' ),
		'add_new_item'       => __( 'Ajouter une référence', 'afsac' ),
		'new_item'           => __( 'Nouvelle référence', 'afsac' ),
		'edit_item'          => __( 'Modifier la référence', 'afsac' ),
		'view_item'          => __( 'Voir la référence', 'afsac' ),
		'all_items'          => __( 'Toutes les références', 'afsac' ),
		'search_items'       => __( 'Rechercher une référence', 'afsac' ),
		'not_found'          => __( 'Aucune référence trouvée.', 'afsac' ),
		'not_found_in_trash' => __( 'Aucune référence dans la corbeille.', 'afsac' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'has_archive'        => false,
		'rewrite'            => array(
			'slug'       => 'references',
			'with_front' => false,
		),
		'menu_icon'          => 'dashicons-awards',
		'menu_position'      => 23,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		'show_in_rest'       => true,
		'rest_base'          => 'references',
		'hierarchical'       => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => false,
		'publicly_queryable' => true,
	);

	register_post_type( 'afsac_reference', $args );
}

/**
 * CPT « Service ».
 *
 * Prestation de conseil/accompagnement (hub Formations & Services). Single-capable
 * (futures pages « En savoir plus » ; fallback single.php pour l'instant), pas
 * d'archive dédiée. Traduisible Polylang (cf. includes/polylang.php).
 *
 * @return void
 */
function afsac_register_cpt_service() {
	$labels = array(
		'name'                  => _x( 'Services', 'Nom général du CPT', 'afsac' ),
		'singular_name'         => _x( 'Service', 'Nom singulier du CPT', 'afsac' ),
		'menu_name'             => __( 'Services', 'afsac' ),
		'name_admin_bar'        => __( 'Service', 'afsac' ),
		'add_new'               => __( 'Ajouter', 'afsac' ),
		'add_new_item'          => __( 'Ajouter un service', 'afsac' ),
		'new_item'              => __( 'Nouveau service', 'afsac' ),
		'edit_item'             => __( 'Modifier le service', 'afsac' ),
		'view_item'             => __( 'Voir le service', 'afsac' ),
		'all_items'             => __( 'Tous les services', 'afsac' ),
		'search_items'          => __( 'Rechercher un service', 'afsac' ),
		'not_found'             => __( 'Aucun service trouvé.', 'afsac' ),
		'not_found_in_trash'    => __( 'Aucun service dans la corbeille.', 'afsac' ),
		'featured_image'        => __( 'Visuel du service', 'afsac' ),
		'set_featured_image'    => __( 'Définir le visuel', 'afsac' ),
		'remove_featured_image' => __( 'Retirer le visuel', 'afsac' ),
		'item_published'        => __( 'Service publié.', 'afsac' ),
		'item_updated'          => __( 'Service mis à jour.', 'afsac' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'has_archive'        => false,
		'rewrite'            => array(
			'slug'       => 'service',
			'with_front' => false,
		),
		'menu_icon'          => 'dashicons-admin-tools',
		'menu_position'      => 24,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'revisions', 'page-attributes', 'custom-fields' ),
		'show_in_rest'       => true,
		'rest_base'          => 'services',
		'hierarchical'       => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'publicly_queryable' => true,
	);

	register_post_type( 'afsac_service', $args );
}

/**
 * CPT « Inscription » — leads de capture (PROSPECTS). Privé : aucune vue publique,
 * pas d'archive ni de slug (données personnelles, admin uniquement). C'est la
 * « base de données leads » du cahier des charges.
 *
 * @return void
 */
function afsac_register_cpt_inscription() {
	$labels = array(
		'name'               => _x( 'Inscriptions', 'Nom général du CPT', 'afsac' ),
		'singular_name'      => _x( 'Inscription', 'Nom singulier du CPT', 'afsac' ),
		'menu_name'          => __( 'Inscriptions', 'afsac' ),
		'name_admin_bar'     => __( 'Inscription', 'afsac' ),
		'all_items'          => __( 'Toutes les inscriptions', 'afsac' ),
		'edit_item'          => __( 'Voir / éditer l’inscription', 'afsac' ),
		'view_item'          => __( 'Voir l’inscription', 'afsac' ),
		'search_items'       => __( 'Rechercher une inscription', 'afsac' ),
		'not_found'          => __( 'Aucune inscription.', 'afsac' ),
		'not_found_in_trash' => __( 'Aucune inscription dans la corbeille.', 'afsac' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false,
		'publicly_queryable' => false,
		'has_archive'        => false,
		'rewrite'            => false,
		'query_var'          => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => false,
		'show_in_rest'       => false,
		'menu_icon'          => 'dashicons-id-alt',
		'menu_position'      => 26,
		'hierarchical'       => false,
		'supports'           => array( 'title', 'custom-fields' ),
		'capability_type'    => 'post',
	);

	register_post_type( 'afsac_inscription', $args );
}

/**
 * Vocabulaire des types d'institution du CPT afsac_reference.
 *
 * Source UNIQUE : les clés sont des slugs STABLES (stockés en base par le champ
 * ACF afsac_reference_type), les valeurs des libellés traduits résolus à
 * l'affichage. Ne jamais stocker le libellé traduit : il figerait la langue de
 * saisie sur toutes les versions linguistiques de la page.
 *
 * @return array<string,string> Slug => libellé traduit.
 */
function afsac_get_reference_types() {
	return array(
		'autorite'     => __( 'Autorité · DGAC', 'afsac' ),
		'aeroport'     => __( 'Aéroport & ANSP', 'afsac' ),
		'academie'     => __( 'Académie & école', 'afsac' ),
		'compagnie'    => __( 'Compagnie & opérateur', 'afsac' ),
		'organisation' => __( 'Organisation internationale', 'afsac' ),
	);
}

/**
 * Niveaux de coopération du CPT afsac_reference.
 *
 * Reprend le découpage de la brochure « Nos Références » du client : national
 * (Tunisie), régional (Afrique) et international. Clés = slugs STABLES.
 *
 * @return array<string,string> Slug => libellé traduit.
 */
function afsac_get_reference_levels() {
	return array(
		'national'      => __( 'Au niveau national', 'afsac' ),
		'regional'      => __( 'Au niveau régional', 'afsac' ),
		'international' => __( 'Au niveau international', 'afsac' ),
	);
}

/**
 * Libellé traduit d'un type d'institution.
 *
 * @param string $slug Slug stocké (cf. afsac_get_reference_types()).
 * @return string Libellé traduit, ou chaîne vide si le slug est inconnu/vide.
 */
function afsac_get_reference_type_label( $slug ) {
	$types = afsac_get_reference_types();
	return isset( $types[ $slug ] ) ? $types[ $slug ] : '';
}
