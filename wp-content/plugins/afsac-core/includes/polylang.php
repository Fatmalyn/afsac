<?php
/**
 * Intégration Polylang : rend traduisibles les CPT et taxonomies AFSAC,
 * et fournit un sélecteur de langue réutilisable par le thème.
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Liste des CPT AFSAC à rendre traduisibles dans Polylang.
 *
 * @return string[]
 */
function afsac_translatable_post_types() {
	return array(
		'afsac_formation',
		'afsac_session',
		'afsac_temoignage',
		'afsac_reference',
		'afsac_service',
	);
}

/**
 * Liste des taxonomies AFSAC à rendre traduisibles dans Polylang.
 *
 * @return string[]
 */
function afsac_translatable_taxonomies() {
	return array(
		'afsac_area',
		'afsac_famille',
		'afsac_langue',
		'afsac_modalite',
		'afsac_type',
	);
}

/**
 * Déclare les CPT AFSAC comme traduisibles.
 *
 * Le second paramètre $is_settings vaut true uniquement sur l'écran de
 * réglages de Polylang : on l'ignore pour forcer la traduction par code et
 * éviter que l'option soit décochable côté admin.
 *
 * @param string[] $post_types  Types de contenu déjà déclarés.
 * @param bool     $is_settings Contexte de l'écran de réglages Polylang.
 * @return string[]
 */
function afsac_pll_post_types( $post_types, $is_settings ) {
	foreach ( afsac_translatable_post_types() as $type ) {
		$post_types[ $type ] = $type;
	}

	return $post_types;
}
add_filter( 'pll_get_post_types', 'afsac_pll_post_types', 10, 2 );

/**
 * Déclare les taxonomies AFSAC comme traduisibles.
 *
 * @param string[] $taxonomies  Taxonomies déjà déclarées.
 * @param bool     $is_settings Contexte de l'écran de réglages Polylang.
 * @return string[]
 */
function afsac_pll_taxonomies( $taxonomies, $is_settings ) {
	foreach ( afsac_translatable_taxonomies() as $taxonomy ) {
		$taxonomies[ $taxonomy ] = $taxonomy;
	}

	return $taxonomies;
}
add_filter( 'pll_get_taxonomies', 'afsac_pll_taxonomies', 10, 2 );

/**
 * Affiche (ou retourne) un sélecteur de langue réutilisable par le thème.
 *
 * Encapsule pll_the_languages() pour fournir un balisage accessible et stable,
 * utilisable partout dans les templates : afsac_language_switcher();
 *
 * Dégrade proprement si Polylang est désactivé (n'affiche rien).
 *
 * @param array $args {
 *     Options d'affichage.
 *
 *     @type bool   $echo            Afficher (true) ou retourner (false). Défaut true.
 *     @type bool   $show_flags      Afficher les drapeaux. Défaut false.
 *     @type bool   $show_names      Afficher les noms de langue. Défaut true.
 *     @type bool   $hide_current    Masquer la langue courante. Défaut false.
 *     @type bool   $hide_if_empty   Masquer les langues sans traduction. Défaut true.
 *     @type string $display_names_as 'name' (nom complet) ou 'slug' (code). Défaut 'name'.
 * }
 * @return string|void Le HTML si $echo vaut false, sinon rien.
 */
function afsac_language_switcher( $args = array() ) {
	$defaults = array(
		'echo'             => true,
		'show_flags'       => false,
		'show_names'       => true,
		'hide_current'     => false,
		'hide_if_empty'    => true,
		'display_names_as' => 'name',
	);

	$args = wp_parse_args( $args, $defaults );

	// Polylang absent : on ne casse pas le template.
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return $args['echo'] ? null : '';
	}

	$items = pll_the_languages(
		array(
			'raw'              => 1,
			'hide_if_empty'    => (int) $args['hide_if_empty'],
			'hide_current'     => (int) $args['hide_current'],
			'display_names_as' => $args['display_names_as'],
		)
	);

	if ( empty( $items ) ) {
		return $args['echo'] ? null : '';
	}

	ob_start();
	?>
	<nav class="afsac-language-switcher" aria-label="<?php esc_attr_e( 'Sélecteur de langue', 'afsac' ); ?>">
		<ul class="afsac-language-switcher__list">
			<?php foreach ( $items as $item ) : ?>
				<li class="afsac-language-switcher__item<?php echo ! empty( $item['current_lang'] ) ? ' is-current' : ''; ?>">
					<a
						href="<?php echo esc_url( $item['url'] ); ?>"
						hreflang="<?php echo esc_attr( $item['locale'] ); ?>"
						lang="<?php echo esc_attr( $item['locale'] ); ?>"
						class="afsac-language-switcher__link"
						<?php echo ! empty( $item['current_lang'] ) ? 'aria-current="true"' : ''; ?>
					>
						<?php if ( $args['show_flags'] && ! empty( $item['flag'] ) ) : ?>
							<span class="afsac-language-switcher__flag"><?php echo $item['flag']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Drapeau HTML fourni par Polylang. ?></span>
						<?php endif; ?>
						<?php if ( $args['show_names'] ) : ?>
							<span class="afsac-language-switcher__name"><?php echo esc_html( $item['name'] ); ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
	$output = ob_get_clean();

	if ( $args['echo'] ) {
		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Balisage déjà échappé ci-dessus.
		return;
	}

	return $output;
}
