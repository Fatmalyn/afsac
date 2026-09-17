<?php
/**
 * Plugin Name:       AFSAC Core
 * Plugin URI:        https://afsac.local
 * Description:        Logique métier du site AFSAC : types de contenu (formation, session, témoignage, référence), taxonomies et relations. Toute la logique métier vit ici, jamais dans le thème.
 * Version:           0.5.3
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Author:            AFSAC
 * Text Domain:       afsac
 * Domain Path:       /languages
 *
 * @package AFSAC\Core
 */

// Sécurité : interdire l'accès direct.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Constantes du plugin.
 */
define( 'AFSAC_CORE_VERSION', '0.5.3' );
define( 'AFSAC_CORE_FILE', __FILE__ );
define( 'AFSAC_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'AFSAC_CORE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Charge les fichiers d'includes.
 *
 * @return void
 */
function afsac_core_load_includes() {
	require_once AFSAC_CORE_PATH . 'includes/post-types.php';
	require_once AFSAC_CORE_PATH . 'includes/taxonomies.php';
	require_once AFSAC_CORE_PATH . 'includes/relations.php';
	require_once AFSAC_CORE_PATH . 'includes/geo.php';
	require_once AFSAC_CORE_PATH . 'includes/form-helpers.php';
	require_once AFSAC_CORE_PATH . 'includes/inscription.php';
	require_once AFSAC_CORE_PATH . 'includes/contact.php';
	require_once AFSAC_CORE_PATH . 'includes/brochure.php';
	require_once AFSAC_CORE_PATH . 'includes/lead-admin.php';
	require_once AFSAC_CORE_PATH . 'includes/messaging.php';
	require_once AFSAC_CORE_PATH . 'includes/polylang.php';
	require_once AFSAC_CORE_PATH . 'includes/rtl.php';
	require_once AFSAC_CORE_PATH . 'includes/breadcrumb.php';
	require_once AFSAC_CORE_PATH . 'includes/acf-fields.php';
	require_once AFSAC_CORE_PATH . 'includes/fs-helpers.php';
	require_once AFSAC_CORE_PATH . 'includes/pricing.php';
	require_once AFSAC_CORE_PATH . 'includes/area-helpers.php';
	require_once AFSAC_CORE_PATH . 'includes/avsec-helpers.php';
	require_once AFSAC_CORE_PATH . 'includes/trainair-bilingue.php';
	require_once AFSAC_CORE_PATH . 'includes/schema.php';
	require_once AFSAC_CORE_PATH . 'includes/seed-terms.php';
	require_once AFSAC_CORE_PATH . 'includes/seed-posts.php';
	require_once AFSAC_CORE_PATH . 'includes/seed-formations.php';
	require_once AFSAC_CORE_PATH . 'includes/seed-trainair.php';
	require_once AFSAC_CORE_PATH . 'includes/seed-avsec-sessions.php';
	require_once AFSAC_CORE_PATH . 'includes/seed-temoignages.php';
	require_once AFSAC_CORE_PATH . 'includes/import-catalogue.php';
}
afsac_core_load_includes();

/**
 * Charge le text domain pour l'internationalisation.
 *
 * @return void
 */
function afsac_core_load_textdomain() {
	load_plugin_textdomain( 'afsac', false, dirname( plugin_basename( AFSAC_CORE_FILE ) ) . '/languages' );
}
add_action( 'init', 'afsac_core_load_textdomain' );

/**
 * Activation : enregistre les CPT/taxonomies puis vide les règles de réécriture.
 *
 * @return void
 */
function afsac_core_activate() {
	afsac_register_post_types();
	afsac_register_taxonomies();
	flush_rewrite_rules();
}
register_activation_hook( AFSAC_CORE_FILE, 'afsac_core_activate' );

/**
 * Désactivation : vide les règles de réécriture.
 *
 * @return void
 */
function afsac_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( AFSAC_CORE_FILE, 'afsac_core_deactivate' );
