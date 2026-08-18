<?php
/**
 * Enregistrement des taxonomies personnalisées.
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre toutes les taxonomies du site AFSAC.
 *
 * @return void
 */
function afsac_register_taxonomies() {
	afsac_register_taxonomy_area();
	afsac_register_taxonomy_famille();
	afsac_register_taxonomy_langue();
	afsac_register_taxonomy_modalite();
	afsac_register_taxonomy_type();
}
add_action( 'init', 'afsac_register_taxonomies' );

/**
 * Taxonomie « Area OACI » (hiérarchique).
 *
 * Les 11 areas du programme TRAINAIR PLUS. Rattachée aux formations.
 *
 * @return void
 */
function afsac_register_taxonomy_area() {
	$labels = array(
		'name'              => _x( 'Areas OACI', 'Nom général de la taxonomie', 'afsac' ),
		'singular_name'     => _x( 'Area OACI', 'Nom singulier de la taxonomie', 'afsac' ),
		'menu_name'         => __( 'Areas OACI', 'afsac' ),
		'all_items'         => __( 'Toutes les areas', 'afsac' ),
		'edit_item'         => __( 'Modifier l\'area', 'afsac' ),
		'view_item'         => __( 'Voir l\'area', 'afsac' ),
		'update_item'       => __( 'Mettre à jour l\'area', 'afsac' ),
		'add_new_item'      => __( 'Ajouter une area', 'afsac' ),
		'new_item_name'     => __( 'Nom de la nouvelle area', 'afsac' ),
		'parent_item'       => __( 'Area parente', 'afsac' ),
		'parent_item_colon' => __( 'Area parente :', 'afsac' ),
		'search_items'      => __( 'Rechercher une area', 'afsac' ),
		'not_found'         => __( 'Aucune area trouvée.', 'afsac' ),
		'back_to_items'     => __( 'Retour aux areas', 'afsac' ),
	);

	$args = array(
		'labels'            => $labels,
		'public'            => true,
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'show_in_nav_menus' => true,
		'query_var'         => true,
		'rewrite'           => array(
			'slug'       => 'area',
			'with_front' => false,
		),
	);

	register_taxonomy( 'afsac_area', array( 'afsac_formation' ), $args );
}

/**
 * Taxonomie « Famille » (hiérarchique).
 *
 * Regroupement commercial des formations (ex. AVSEC, sûreté, navigation…).
 * Rattachée aux formations.
 *
 * @return void
 */
function afsac_register_taxonomy_famille() {
	$labels = array(
		'name'              => _x( 'Familles', 'Nom général de la taxonomie', 'afsac' ),
		'singular_name'     => _x( 'Famille', 'Nom singulier de la taxonomie', 'afsac' ),
		'menu_name'         => __( 'Familles', 'afsac' ),
		'all_items'         => __( 'Toutes les familles', 'afsac' ),
		'edit_item'         => __( 'Modifier la famille', 'afsac' ),
		'view_item'         => __( 'Voir la famille', 'afsac' ),
		'update_item'       => __( 'Mettre à jour la famille', 'afsac' ),
		'add_new_item'      => __( 'Ajouter une famille', 'afsac' ),
		'new_item_name'     => __( 'Nom de la nouvelle famille', 'afsac' ),
		'parent_item'       => __( 'Famille parente', 'afsac' ),
		'parent_item_colon' => __( 'Famille parente :', 'afsac' ),
		'search_items'      => __( 'Rechercher une famille', 'afsac' ),
		'not_found'         => __( 'Aucune famille trouvée.', 'afsac' ),
		'back_to_items'     => __( 'Retour aux familles', 'afsac' ),
	);

	$args = array(
		'labels'            => $labels,
		'public'            => true,
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'show_in_nav_menus' => true,
		'query_var'         => true,
		'rewrite'           => array(
			'slug'       => 'famille',
			'with_front' => false,
		),
	);

	register_taxonomy( 'afsac_famille', array( 'afsac_formation' ), $args );
}

/**
 * Taxonomie « Langue de la formation » (non hiérarchique).
 *
 * Langue d'animation du cours (FR / ENG / ARAB). Distincte de la langue
 * d'interface gérée par Polylang. Rattachée aux formations ET aux sessions.
 *
 * @return void
 */
function afsac_register_taxonomy_langue() {
	$labels = array(
		'name'                       => _x( 'Langues de formation', 'Nom général de la taxonomie', 'afsac' ),
		'singular_name'              => _x( 'Langue de formation', 'Nom singulier de la taxonomie', 'afsac' ),
		'menu_name'                  => __( 'Langues de formation', 'afsac' ),
		'all_items'                  => __( 'Toutes les langues', 'afsac' ),
		'edit_item'                  => __( 'Modifier la langue', 'afsac' ),
		'view_item'                  => __( 'Voir la langue', 'afsac' ),
		'update_item'                => __( 'Mettre à jour la langue', 'afsac' ),
		'add_new_item'               => __( 'Ajouter une langue', 'afsac' ),
		'new_item_name'              => __( 'Nom de la nouvelle langue', 'afsac' ),
		'search_items'               => __( 'Rechercher une langue', 'afsac' ),
		'popular_items'              => __( 'Langues fréquentes', 'afsac' ),
		'separate_items_with_commas' => __( 'Séparer les langues par des virgules', 'afsac' ),
		'add_or_remove_items'        => __( 'Ajouter ou retirer des langues', 'afsac' ),
		'choose_from_most_used'      => __( 'Choisir parmi les langues les plus utilisées', 'afsac' ),
		'not_found'                  => __( 'Aucune langue trouvée.', 'afsac' ),
		'back_to_items'              => __( 'Retour aux langues', 'afsac' ),
	);

	$args = array(
		'labels'            => $labels,
		'public'            => true,
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'show_in_nav_menus' => true,
		'query_var'         => true,
		'rewrite'           => array(
			'slug'       => 'langue',
			'with_front' => false,
		),
	);

	register_taxonomy( 'afsac_langue', array( 'afsac_formation', 'afsac_session' ), $args );
}

/**
 * Taxonomie « Modalité » (non hiérarchique).
 *
 * Mode de dispense (présentiel, distanciel, hybride, sur-mesure…).
 * Rattachée aux formations.
 *
 * @return void
 */
function afsac_register_taxonomy_modalite() {
	$labels = array(
		'name'                       => _x( 'Modalités', 'Nom général de la taxonomie', 'afsac' ),
		'singular_name'              => _x( 'Modalité', 'Nom singulier de la taxonomie', 'afsac' ),
		'menu_name'                  => __( 'Modalités', 'afsac' ),
		'all_items'                  => __( 'Toutes les modalités', 'afsac' ),
		'edit_item'                  => __( 'Modifier la modalité', 'afsac' ),
		'view_item'                  => __( 'Voir la modalité', 'afsac' ),
		'update_item'                => __( 'Mettre à jour la modalité', 'afsac' ),
		'add_new_item'               => __( 'Ajouter une modalité', 'afsac' ),
		'new_item_name'              => __( 'Nom de la nouvelle modalité', 'afsac' ),
		'search_items'               => __( 'Rechercher une modalité', 'afsac' ),
		'separate_items_with_commas' => __( 'Séparer les modalités par des virgules', 'afsac' ),
		'add_or_remove_items'        => __( 'Ajouter ou retirer des modalités', 'afsac' ),
		'choose_from_most_used'      => __( 'Choisir parmi les modalités les plus utilisées', 'afsac' ),
		'not_found'                  => __( 'Aucune modalité trouvée.', 'afsac' ),
		'back_to_items'              => __( 'Retour aux modalités', 'afsac' ),
	);

	$args = array(
		'labels'            => $labels,
		'public'            => true,
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'show_in_nav_menus' => true,
		'query_var'         => true,
		'rewrite'           => array(
			'slug'       => 'modalite',
			'with_front' => false,
		),
	);

	register_taxonomy( 'afsac_modalite', array( 'afsac_formation' ), $args );
}

/**
 * Taxonomie « Type de cours » (non hiérarchique).
 *
 * Cours OACI / STP / Atelier / Programme. Sert le badge, le filtre Type et
 * l'onglet « Programmes » de la page « Liste de cours ». Non publique (pas
 * d'archive propre) ; chaque terme porte une méta `afsac_type_key` (code stable
 * indépendant du slug Polylang) lue par le gabarit.
 *
 * @return void
 */
function afsac_register_taxonomy_type() {
	$labels = array(
		'name'          => _x( 'Types de cours', 'Nom général de la taxonomie', 'afsac' ),
		'singular_name' => _x( 'Type de cours', 'Nom singulier de la taxonomie', 'afsac' ),
		'menu_name'     => __( 'Types de cours', 'afsac' ),
		'all_items'     => __( 'Tous les types', 'afsac' ),
		'edit_item'     => __( 'Modifier le type', 'afsac' ),
		'add_new_item'  => __( 'Ajouter un type', 'afsac' ),
		'search_items'  => __( 'Rechercher un type', 'afsac' ),
		'not_found'     => __( 'Aucun type trouvé.', 'afsac' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false,
		'publicly_queryable' => false,
		'hierarchical'       => false,
		'show_ui'            => true,
		'show_admin_column'  => true,
		'show_in_rest'       => true,
		'show_in_nav_menus'  => false,
		'query_var'          => false,
		'rewrite'            => false,
	);

	register_taxonomy( 'afsac_type', array( 'afsac_formation' ), $args );
}
