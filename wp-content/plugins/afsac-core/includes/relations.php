<?php
/**
 * Relations entre types de contenu.
 *
 * Une « session » est une occurrence planifiée d'une « formation ». Le lien
 * est stocké dans une méta-donnée de la session (ID de la formation parente).
 * ACF Pro pourra exposer ce champ via un sélecteur de relation, mais la méta
 * est enregistrée ici pour rester la source de vérité (REST/Gutenberg inclus)
 * indépendamment du plugin de champs.
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clé de méta stockant l'ID de la formation rattachée à une session.
 *
 * @var string
 */
const AFSAC_SESSION_FORMATION_META = '_afsac_formation_id';

/**
 * Enregistre la méta-donnée de relation session → formation.
 *
 * Exposée à l'API REST (donc à Gutenberg) avec contrôle d'autorisation :
 * seuls les utilisateurs pouvant éditer la session peuvent écrire la valeur.
 *
 * @return void
 */
function afsac_register_session_relation_meta() {
	register_post_meta(
		'afsac_session',
		AFSAC_SESSION_FORMATION_META,
		array(
			'type'              => 'integer',
			'description'       => __( 'ID de la formation rattachée à cette session.', 'afsac' ),
			'single'            => true,
			'default'           => 0,
			'show_in_rest'      => true,
			'sanitize_callback' => 'absint',
			'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
				return current_user_can( 'edit_post', $post_id );
			},
		)
	);
}
add_action( 'init', 'afsac_register_session_relation_meta' );

/**
 * Retourne l'ID de la formation rattachée à une session.
 *
 * @param int $session_id ID du post session.
 * @return int ID de la formation, ou 0 si aucune.
 */
function afsac_get_session_formation_id( $session_id ) {
	return (int) get_post_meta( (int) $session_id, AFSAC_SESSION_FORMATION_META, true );
}

/**
 * Retourne l'objet formation rattaché à une session.
 *
 * @param int $session_id ID du post session.
 * @return WP_Post|null L'objet formation, ou null si introuvable.
 */
function afsac_get_session_formation( $session_id ) {
	$formation_id = afsac_get_session_formation_id( $session_id );

	if ( ! $formation_id ) {
		return null;
	}

	$formation = get_post( $formation_id );

	return ( $formation instanceof WP_Post && 'afsac_formation' === $formation->post_type ) ? $formation : null;
}

/**
 * Retourne les sessions rattachées à une formation.
 *
 * @param int   $formation_id ID du post formation.
 * @param array $args         Surcharges optionnelles de WP_Query.
 * @return WP_Post[] Tableau d'objets session (éventuellement vide).
 */
function afsac_get_formation_sessions( $formation_id, $args = array() ) {
	$defaults = array(
		'post_type'      => 'afsac_session',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'meta_value',
		'meta_key'       => 'afsac_date_debut',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'   => AFSAC_SESSION_FORMATION_META,
				'value' => (int) $formation_id,
			),
		),
	);

	$query = new WP_Query( wp_parse_args( $args, $defaults ) );

	return $query->posts;
}

/**
 * Sessions d'une formation pour AFFICHAGE public (fiche cours).
 *
 * Corrige le multilingue : résout d'abord l'ID canonique (langue par défaut)
 * pour retrouver les sessions liées à l'ID FR depuis une fiche EN. Réutilise
 * afsac_get_formation_sessions() puis filtre en PHP (jeu de données réduit) :
 *   - exclut TOUJOURS le statut « archive » (garde « ouvert » et « complet ») ;
 *   - ne garde que les sessions à venir / en cours : ( date_fin sinon
 *     date_debut ) >= aujourd'hui.
 * L'ordre chronologique ASC de la requête sous-jacente est conservé.
 *
 * @param int   $formation_id ID de la formation (n'importe quelle langue).
 * @param array $args         { @type bool $include_past Inclure les sessions passées. Défaut false. }
 * @return WP_Post[] Sessions filtrées (éventuellement vide).
 */
function afsac_get_formation_sessions_for_display( $formation_id, $args = array() ) {
	$args = wp_parse_args( $args, array( 'include_past' => false ) );

	$canonical_id = afsac_get_default_lang_id( (int) $formation_id );
	$sessions     = afsac_get_formation_sessions( $canonical_id );

	if ( empty( $sessions ) ) {
		return array();
	}

	$today    = (int) current_time( 'Ymd' );
	$filtered = array();

	foreach ( $sessions as $session ) {
		// Exclut toujours les sessions archivées.
		if ( 'archive' === get_post_meta( $session->ID, 'afsac_statut', true ) ) {
			continue;
		}

		// À venir / en cours uniquement (sauf override include_past).
		if ( empty( $args['include_past'] ) ) {
			$debut = (int) get_post_meta( $session->ID, 'afsac_date_debut', true );
			$fin   = (int) get_post_meta( $session->ID, 'afsac_date_fin', true );
			$ref   = $fin > 0 ? $fin : $debut;
			if ( $ref > 0 && $ref < $today ) {
				continue;
			}
		}

		$filtered[] = $session;
	}

	return $filtered;
}

/**
 * Prochaine session (à venir) pour un LOT de formations, en une seule requête.
 *
 * Évite le N+1 sur la page « Liste de cours » : résout les IDs canoniques
 * (langue par défaut), interroge toutes les sessions liées en une fois, puis mappe
 * la 1re session à venir (statut ≠ archive ; date de fin sinon début >= aujourd'hui,
 * comparaison Ymd entière comme afsac_get_formation_sessions_for_display) sur chaque
 * ID de formation AFFICHÉ. Le filtrage Polylang par langue des sessions est conservé.
 *
 * @param int[] $formation_ids IDs des formations affichées (toute langue).
 * @return array<int,WP_Post> Map id_affiché => prochaine session.
 */
function afsac_get_next_sessions_map( array $formation_ids ) {
	$canonical = array();
	$canon_ids = array();
	foreach ( $formation_ids as $fid ) {
		$c                       = afsac_get_default_lang_id( (int) $fid );
		$canonical[ (int) $fid ] = $c;
		$canon_ids[ $c ]         = $c;
	}
	if ( empty( $canon_ids ) ) {
		return array();
	}

	$sessions = get_posts(
		array(
			'post_type'      => 'afsac_session',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'meta_value',
			'meta_key'       => 'afsac_date_debut',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => AFSAC_SESSION_FORMATION_META,
					'value'   => array_values( $canon_ids ),
					'compare' => 'IN',
					'type'    => 'NUMERIC',
				),
			),
		)
	);

	$today        = (int) current_time( 'Ymd' );
	$by_formation = array();
	foreach ( $sessions as $session ) {
		if ( 'archive' === get_post_meta( $session->ID, 'afsac_statut', true ) ) {
			continue;
		}
		$debut = (int) get_post_meta( $session->ID, 'afsac_date_debut', true );
		$fin   = (int) get_post_meta( $session->ID, 'afsac_date_fin', true );
		$ref   = $fin > 0 ? $fin : $debut;
		if ( $ref > 0 && $ref < $today ) {
			continue;
		}
		$canon = (int) get_post_meta( $session->ID, AFSAC_SESSION_FORMATION_META, true );
		if ( ! isset( $by_formation[ $canon ] ) ) {
			$by_formation[ $canon ] = $session; // 1re = la plus proche (tri ASC).
		}
	}

	$result = array();
	foreach ( $canonical as $displayed => $canon ) {
		if ( isset( $by_formation[ $canon ] ) ) {
			$result[ $displayed ] = $by_formation[ $canon ];
		}
	}

	return $result;
}

/**
 * Formations qui ont une SESSION À VENIR, dans l'ordre chronologique.
 *
 * Le miroir de afsac_get_next_sessions_map() : celle-ci part d'une liste de
 * formations et cherche leur prochaine session ; celle-ci part des sessions pour
 * répondre à « quels cours sont réellement programmés, et dans quel ordre ? ».
 *
 * C'est ce que réclame la section « Consulter nos formations à venir » de
 * l'accueil : elle listait les fiches les plus RÉCEMMENT CRÉÉES, ce qui n'a rien
 * à voir — le client y voyait des cours sans aucune date (demande du 04/09/2026).
 *
 * La formation est résolue dans la LANGUE COURANTE : la relation stocke l'ID
 * canonique (FR), l'afficher tel quel donnerait des titres français sur le site
 * anglais. Une formation n'apparaît qu'une fois, à la date de sa session la plus
 * proche, même si elle revient plusieurs fois dans l'année.
 *
 * @param int $limit Nombre maximum de formations retournées (0 = pas de limite).
 * @return int[] IDs de formation, de la session la plus proche à la plus lointaine.
 */
function afsac_formations_with_upcoming_session( $limit = 0 ) {
	$sessions = get_posts(
		array(
			'post_type'      => 'afsac_session',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'meta_value',
			'meta_key'       => 'afsac_date_debut', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'order'          => 'ASC',
		)
	);

	$today = (int) current_time( 'Ymd' );
	$out   = array();

	foreach ( $sessions as $session ) {
		if ( 'archive' === get_post_meta( $session->ID, 'afsac_statut', true ) ) {
			continue;
		}

		$debut = (int) get_post_meta( $session->ID, 'afsac_date_debut', true );
		$fin   = (int) get_post_meta( $session->ID, 'afsac_date_fin', true );
		$ref   = $fin > 0 ? $fin : $debut;
		if ( $ref > 0 && $ref < $today ) {
			continue;
		}

		$formation = function_exists( 'afsac_get_session_formation_localized' )
			? afsac_get_session_formation_localized( $session->ID )
			: null;
		if ( ! $formation instanceof WP_Post || 'publish' !== $formation->post_status ) {
			continue;
		}

		$fid = (int) $formation->ID;
		if ( isset( $out[ $fid ] ) ) {
			continue; // Déjà retenue à une date plus proche.
		}
		$out[ $fid ] = $fid;

		if ( $limit > 0 && count( $out ) >= $limit ) {
			break;
		}
	}

	return array_values( $out );
}
