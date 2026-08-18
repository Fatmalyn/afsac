<?php
/**
 * Géolocalisation des lieux de session (vue carte du calendrier).
 *
 * Les sessions n'ont pas toujours de coordonnées saisies (afsac_lat/afsac_lng).
 * Ce helper fournit un repli : une table de villes connues → coordonnées, que
 * la vue carte interroge sur le texte du lieu (ex. « Tunis, Tunisie » → Tunis).
 * La table est extensible via le filtre `afsac_lieu_coords_table`.
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Table de référence des villes → [latitude, longitude].
 *
 * Clés = slugs (sanitize_title) recherchés par sous-chaîne dans le lieu.
 * Des alias pointent vers la même ville (ex. caire / cairo / le-caire).
 * Étendre via le filtre `afsac_lieu_coords_table`.
 *
 * @return array<string,array{0:float,1:float}>
 */
function afsac_lieu_coords_table() {
	$table = array(
		'tunis'      => array( 36.8065, 10.1815 ),
		'nairobi'    => array( -1.2921, 36.8219 ),
		'dakar'      => array( 14.6928, -17.4467 ),
		'casablanca' => array( 33.5731, -7.5898 ),
		'le-caire'   => array( 30.0444, 31.2357 ),
		'caire'      => array( 30.0444, 31.2357 ),
		'cairo'      => array( 30.0444, 31.2357 ),
		'abidjan'    => array( 5.3599, -4.0083 ),
	);

	/**
	 * Filtre la table villes → coordonnées.
	 *
	 * @param array $table Map slug => [lat, lng].
	 */
	return (array) apply_filters( 'afsac_lieu_coords_table', $table );
}

/**
 * Résout les coordonnées d'un lieu textuel via la table de villes.
 *
 * Compare le slug du lieu à chaque clé de la table (sous-chaîne) ; les clés les
 * plus longues sont testées d'abord pour éviter les faux positifs.
 *
 * @param string $lieu Texte libre du lieu (ex. « Tunis, Tunisie »).
 * @return array{0:float,1:float}|null [lat, lng] ou null si non résolu.
 */
function afsac_lieu_coords( $lieu ) {
	$lieu = trim( (string) $lieu );
	if ( '' === $lieu ) {
		return null;
	}

	$slug  = sanitize_title( $lieu );
	$table = afsac_lieu_coords_table();

	// Clés longues d'abord (« le-caire » avant « caire »).
	$keys = array_keys( $table );
	usort(
		$keys,
		function ( $a, $b ) {
			return strlen( $b ) - strlen( $a );
		}
	);

	foreach ( $keys as $key ) {
		if ( '' !== $key && false !== strpos( $slug, $key ) ) {
			$coords = $table[ $key ];
			if ( is_array( $coords ) && isset( $coords[0], $coords[1] ) ) {
				return array( (float) $coords[0], (float) $coords[1] );
			}
		}
	}

	return null;
}
