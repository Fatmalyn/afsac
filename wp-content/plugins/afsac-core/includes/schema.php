<?php
/**
 * Données structurées JSON-LD (schema.org « Course ») pour la fiche cours.
 *
 * Émis sur wp_head, uniquement sur le singulier d'un afsac_formation. Rank Math
 * n'émet aucun rich snippet pour ce CPT (pt_afsac_formation_default_rich_snippet
 * = off), donc pas de doublon. Aucune chaîne gettext ici (c'est de la donnée).
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mappe un slug de modalité (FR ou EN) vers une valeur courseMode schema.org.
 *
 * @param string $slug Slug du terme afsac_modalite.
 * @return string 'Onsite' | 'Online' | 'Blended', ou '' si inconnu.
 */
function afsac_schema_course_mode( $slug ) {
	$map = array(
		'presentiel'       => 'Onsite',
		'on-site'          => 'Onsite',
		'intra-entreprise' => 'Onsite',
		'in-house'         => 'Onsite',
		'distanciel'       => 'Online',
		'online'           => 'Online',
		'hybride'          => 'Blended',
		'hybrid'           => 'Blended',
		// sur-mesure / bespoke : non mappés (on ne devine pas).
	);
	return isset( $map[ $slug ] ) ? $map[ $slug ] : '';
}

/**
 * Normalise une valeur de TEXTE HUMAIN pour le JSON-LD : retire le HTML,
 * décode les entités (&#038; → &) et trim. À n'utiliser que sur du texte
 * libre, jamais sur des valeurs contrôlées (prix, dates, slugs, courseMode).
 *
 * @param mixed $value Valeur brute.
 * @return string Texte propre.
 */
function afsac_schema_text( $value ) {
	return trim( html_entity_decode( wp_strip_all_tags( (string) $value ), ENT_QUOTES, 'UTF-8' ) );
}

/**
 * Construit et émet le JSON-LD Course de la fiche formation courante.
 *
 * @return void
 */
function afsac_output_course_schema() {
	if ( ! is_singular( 'afsac_formation' ) ) {
		return;
	}

	$id = (int) get_queried_object_id();
	if ( ! $id ) {
		return;
	}

	$has_acf = function_exists( 'get_field' );

	$data = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Course',
		'name'     => afsac_schema_text( get_the_title( $id ) ),
	);

	// Description : rank_math_description → excerpt → objectifs (strippé/tronqué).
	$description = (string) get_post_meta( $id, 'rank_math_description', true );
	if ( '' === $description ) {
		$post        = get_post( $id );
		$description = $post ? (string) $post->post_excerpt : '';
	}
	if ( '' === $description && $has_acf ) {
		$objectifs = (string) get_field( 'afsac_objectifs', $id );
		if ( '' !== $objectifs ) {
			$description = wp_html_excerpt( wp_strip_all_tags( $objectifs ), 160, '…' );
		}
	}
	if ( '' !== $description ) {
		$data['description'] = afsac_schema_text( $description );
	}

	// courseCode.
	$code = $has_acf ? (string) get_field( 'afsac_code', $id ) : '';
	if ( '' !== $code ) {
		$data['courseCode'] = afsac_schema_text( $code );
	}

	// Provider = l'organisme qui DISPENSE le cours (AFSAC).
	$data['provider'] = array(
		'@type' => 'Organization',
		'name'  => afsac_schema_text( get_bloginfo( 'name' ) ),
		'url'   => home_url( '/' ),
	);

	// Auteur = organisme ayant DÉVELOPPÉ le cours (ex. ICAO/OACI), si renseigné.
	// Crédit uniquement — jamais en provider.
	if ( $has_acf ) {
		$afsac_developer = (string) get_field( 'afsac_developpe_par', $id );
		if ( '' !== $afsac_developer ) {
			$data['author'] = array(
				'@type' => 'Organization',
				'name'  => afsac_schema_text( $afsac_developer ),
			);
		}
	}

	// Langue du cours (slug Polylang : fr / en).
	if ( function_exists( 'pll_get_post_language' ) ) {
		$lang = pll_get_post_language( $id );
		if ( $lang ) {
			$data['inLanguage'] = $lang;
		}
	}

	// Offre (uniquement si un montant est renseigné).
	if ( $has_acf ) {
		$montant = get_field( 'afsac_frais_montant', $id );
		if ( '' !== $montant && null !== $montant ) {
			/*
			 * Le prix balisé doit être CELUI QUE LE VISITEUR LIT sur la page :
			 * afficher 1 840 € et déclarer 2 000 USD ferait diverger le rich
			 * snippet de la page (et Google le sanctionne). On passe donc par le
			 * même helper que les templates (EUR en FR, USD en EN).
			 */
			$devise = (string) get_field( 'afsac_devise', $id );
			$prix   = function_exists( 'afsac_price_display' ) ? afsac_price_display( $montant, $devise ) : array();

			$offer = array(
				'@type'    => 'Offer',
				'price'    => ! empty( $prix ) ? (string) $prix['value'] : (string) $montant,
				'category' => 'Paid',
			);
			if ( ! empty( $prix ) ) {
				$offer['priceCurrency'] = $prix['currency'];
			} elseif ( '' !== $devise ) {
				$offer['priceCurrency'] = $devise;
			}
			$data['offers'] = $offer;
		}
	}

	// courseMode depuis la/les modalité(s) du COURS (calculé une fois, dédupliqué).
	$course_modes = array();
	$modalites    = get_the_terms( $id, 'afsac_modalite' );
	if ( $modalites && ! is_wp_error( $modalites ) ) {
		foreach ( $modalites as $modalite ) {
			$mode = afsac_schema_course_mode( $modalite->slug );
			if ( '' !== $mode ) {
				$course_modes[ $mode ] = $mode;
			}
		}
	}
	$course_modes = array_values( $course_modes );

	// Sessions → hasCourseInstance[].
	$instances = array();
	if ( function_exists( 'afsac_get_formation_sessions_for_display' ) ) {
		foreach ( afsac_get_formation_sessions_for_display( $id ) as $session ) {
			$start = DateTime::createFromFormat( 'Ymd', (string) get_post_meta( $session->ID, 'afsac_date_debut', true ) );
			if ( ! $start ) {
				continue; // startDate indispensable à un CourseInstance.
			}

			$instance = array(
				'@type'     => 'CourseInstance',
				'startDate' => $start->format( 'Y-m-d' ),
			);

			$fin_raw = (string) get_post_meta( $session->ID, 'afsac_date_fin', true );
			if ( '' !== $fin_raw ) {
				$end = DateTime::createFromFormat( 'Ymd', $fin_raw );
				if ( $end ) {
					$instance['endDate'] = $end->format( 'Y-m-d' );
				}
			}

			$lieu = (string) get_post_meta( $session->ID, 'afsac_lieu', true );
			if ( '' !== $lieu ) {
				$instance['location'] = array(
					'@type' => 'Place',
					'name'  => afsac_schema_text( $lieu ),
				);
			}

			if ( $course_modes ) {
				$instance['courseMode'] = ( 1 === count( $course_modes ) ) ? $course_modes[0] : $course_modes;
			}

			$langues = get_the_terms( $session->ID, 'afsac_langue' );
			if ( $langues && ! is_wp_error( $langues ) ) {
				$langue                 = reset( $langues );
				$instance['inLanguage'] = afsac_schema_text( $langue->name );
			}

			$instances[] = $instance;
		}
	}
	if ( $instances ) {
		$data['hasCourseInstance'] = $instances;
	}

	$json = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( false === $json ) {
		return;
	}
	// Durcissement : neutralise toute évasion </script> (slashes laissés intacts).
	$json = str_replace( '<', '\\u003C', $json );

	echo "\n" . '<script type="application/ld+json">' . $json . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON-LD encodé + < échappé.
}
add_action( 'wp_head', 'afsac_output_course_schema' );
