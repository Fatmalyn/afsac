<?php
/**
 * Accueil — section « Partenaires » (bandeau logos défilant).
 *
 * Simple appel du part PARTAGÉ template-parts/shared/partners-marquee : le même
 * bandeau est réutilisé tel quel par la page « Références & Témoignages ».
 * Toute la logique (requête CPT afsac_reference, fallback démo, marquee) vit
 * dans le part partagé — ne rien dupliquer ici.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_template_part(
	'template-parts/shared/partners-marquee',
	null,
	array(
		'eyebrow' => __( 'Ils nous font confiance', 'afsac' ),
		'title'   => __( 'Partenaires institutionnels et clients de référence', 'afsac' ),
	)
);
