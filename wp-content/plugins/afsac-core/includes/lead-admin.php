<?php
/**
 * Affichage admin des leads : boîte « Détail de la demande ».
 *
 * Les CPT « afsac_message », « afsac_inscription » et « afsac_telechargement »
 * sont déclarés SANS support « editor » : tout ce que saisit le visiteur vit en
 * métadonnées (afsac_msg_* / afsac_lead_* / afsac_dl_*). Sans cette boîte,
 * l'écran d'édition d'un lead apparaît vide et le contenu n'est lisible qu'en
 * activant « Champs personnalisés » dans les options d'écran — illisible pour un
 * utilisateur non technique.
 *
 * Lecture seule : la source de vérité est le formulaire, un lead ne se réécrit
 * pas depuis l'admin.
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Libellés lisibles des valeurs stockées sous forme de clé.
 *
 * Les formulaires enregistrent des clés (« autorite », « bon_commande », « fr »)
 * pour rester stables ; l'admin doit afficher le libellé correspondant.
 *
 * @param string $group Groupe de valeurs.
 * @param string $key   Clé stockée.
 * @return string Libellé traduit, ou la clé brute si inconnue.
 */
function afsac_lead_label( $group, $key ) {
	$maps = array(
		'type_org'        => array(
			'autorite'  => __( 'Autorité de l’aviation civile', 'afsac' ),
			'aeroport'  => __( 'Aéroport', 'afsac' ),
			'compagnie' => __( 'Compagnie aérienne', 'afsac' ),
			'academie'  => __( 'Académie / centre de formation', 'afsac' ),
			'autre'     => __( 'Autre', 'afsac' ),
		),
		'paiement'        => array(
			'bon_commande' => __( 'Bon de commande', 'afsac' ),
			'virement'     => __( 'Virement bancaire', 'afsac' ),
			'institution'  => __( 'Prise en charge par l’institution', 'afsac' ),
			'autre'        => __( 'Autre', 'afsac' ),
		),
		'langue_supports' => array(
			'fr' => __( 'Français', 'afsac' ),
			'en' => __( 'Anglais', 'afsac' ),
			'ar' => __( 'Arabe', 'afsac' ),
		),
		'statut'          => array(
			'nouveau' => __( 'Nouveau', 'afsac' ),
			'traite'  => __( 'Traité', 'afsac' ),
		),
	);

	if ( '' === $key ) {
		return '';
	}
	return isset( $maps[ $group ][ $key ] ) ? $maps[ $group ][ $key ] : $key;
}

/**
 * Types de leads gérés : CPT => préfixe de métadonnée.
 *
 * Source unique du couple écran / préfixe, partagée par la boîte de détail et
 * son enregistrement. Ajouter un type de lead = ajouter une ligne ici et un cas
 * dans afsac_lead_admin_schema().
 *
 * @return array<string,string>
 */
function afsac_lead_admin_types() {
	return array(
		'afsac_message'        => 'afsac_msg_',
		'afsac_inscription'    => 'afsac_lead_',
		'afsac_telechargement' => AFSAC_DL_META,
	);
}

/**
 * Structure d'affichage d'un lead : sections => champs.
 *
 * La clé de chaque champ est le SUFFIXE de la méta (le préfixe est porté par le
 * type de lead). La valeur est un couple libellé / mode de rendu.
 *
 * @param string $type 'message' | 'inscription' | 'telechargement'.
 * @return array<string,array<string,array{0:string,1:string}>>
 */
function afsac_lead_admin_schema( $type ) {
	if ( 'telechargement' === $type ) {
		return array(
			__( 'Demandeur', 'afsac' )    => array(
				'nom'          => array( __( 'Nom', 'afsac' ), 'text' ),
				'email'        => array( __( 'E-mail', 'afsac' ), 'email' ),
				'organisation' => array( __( 'Organisation', 'afsac' ), 'text' ),
				'pays'         => array( __( 'Pays', 'afsac' ), 'text' ),
			),
			__( 'Brochure', 'afsac' )     => array(
				'edition'       => array( __( 'Édition demandée', 'afsac' ), 'edition' ),
				'demandes'      => array( __( 'Formulaires envoyés', 'afsac' ), 'text' ),
				'downloads'     => array( __( 'Téléchargements effectifs', 'afsac' ), 'text' ),
				'first_at'      => array( __( 'Première demande', 'afsac' ), 'date' ),
				'last_at'       => array( __( 'Dernière demande', 'afsac' ), 'date' ),
				'downloaded_at' => array( __( 'Dernier téléchargement', 'afsac' ), 'date' ),
			),
			__( 'Traçabilité', 'afsac' )  => array(
				'lang'            => array( __( 'Langue du site', 'afsac' ), 'text' ),
				'source'          => array( __( 'Page d’origine', 'afsac' ), 'url' ),
				'consent_rgpd_at' => array( __( 'Consentement RGPD', 'afsac' ), 'date' ),
				'ip'              => array( __( 'Adresse IP', 'afsac' ), 'text' ),
			),
		);
	}

	if ( 'message' === $type ) {
		return array(
			__( 'Expéditeur', 'afsac' )   => array(
				'prenom'       => array( __( 'Prénom', 'afsac' ), 'text' ),
				'nom'          => array( __( 'Nom', 'afsac' ), 'text' ),
				'email'        => array( __( 'E-mail', 'afsac' ), 'email' ),
				'telephone'    => array( __( 'Téléphone', 'afsac' ), 'phone' ),
				'fonction'     => array( __( 'Fonction', 'afsac' ), 'text' ),
				'organisation' => array( __( 'Organisation', 'afsac' ), 'text' ),
			),
			__( 'Demande', 'afsac' )      => array(
				'sujet'   => array( __( 'Sujet', 'afsac' ), 'text' ),
				'message' => array( __( 'Message', 'afsac' ), 'longtext' ),
			),
			__( 'Traçabilité', 'afsac' )  => array(
				'statut'           => array( __( 'Statut', 'afsac' ), 'statut' ),
				'lang'             => array( __( 'Langue du site', 'afsac' ), 'text' ),
				'source'           => array( __( 'Page d’origine', 'afsac' ), 'url' ),
				'consent_rgpd_at'  => array( __( 'Consentement RGPD', 'afsac' ), 'date' ),
				'ip'               => array( __( 'Adresse IP', 'afsac' ), 'text' ),
			),
		);
	}

	return array(
		__( 'Participant', 'afsac' )   => array(
			'civilite'  => array( __( 'Civilité', 'afsac' ), 'text' ),
			'prenom'    => array( __( 'Prénom', 'afsac' ), 'text' ),
			'nom'       => array( __( 'Nom', 'afsac' ), 'text' ),
			'email'     => array( __( 'E-mail', 'afsac' ), 'email' ),
			'telephone' => array( __( 'Téléphone', 'afsac' ), 'phone' ),
			'age'       => array( __( 'Tranche d’âge', 'afsac' ), 'text' ),
			'fonction'  => array( __( 'Fonction', 'afsac' ), 'text' ),
		),
		__( 'Organisation', 'afsac' )  => array(
			'organisation' => array( __( 'Organisation', 'afsac' ), 'text' ),
			'type_org'     => array( __( 'Type d’organisation', 'afsac' ), 'type_org' ),
			'pays'         => array( __( 'Pays', 'afsac' ), 'text' ),
		),
		__( 'Formation', 'afsac' )     => array(
			'formation_id'    => array( __( 'Cours', 'afsac' ), 'post' ),
			'session_id'      => array( __( 'Séance', 'afsac' ), 'post' ),
			'langue_supports' => array( __( 'Langue des supports', 'afsac' ), 'langue_supports' ),
		),
		__( 'Logistique', 'afsac' )    => array(
			'regime'        => array( __( 'Régime alimentaire', 'afsac' ), 'text' ),
			'accessibilite' => array( __( 'Accessibilité', 'afsac' ), 'text' ),
			'paiement'      => array( __( 'Mode de paiement', 'afsac' ), 'paiement' ),
			'bon_commande'  => array( __( 'Référence bon de commande', 'afsac' ), 'text' ),
		),
		__( 'Traçabilité', 'afsac' )   => array(
			'statut'                  => array( __( 'Statut', 'afsac' ), 'statut' ),
			'lang'                    => array( __( 'Langue du site', 'afsac' ), 'text' ),
			'source'                  => array( __( 'Page d’origine', 'afsac' ), 'url' ),
			'consent_rgpd_at'         => array( __( 'Consentement RGPD', 'afsac' ), 'date' ),
			'consent_exactitude_at'   => array( __( 'Attestation d’exactitude', 'afsac' ), 'date' ),
			'ip'                      => array( __( 'Adresse IP', 'afsac' ), 'text' ),
			'ua'                      => array( __( 'Navigateur', 'afsac' ), 'text' ),
		),
	);
}

/**
 * Rend la valeur d'un champ selon son mode d'affichage (sortie échappée).
 *
 * @param string $mode  Mode de rendu.
 * @param string $value Valeur brute de la méta.
 * @return string HTML prêt à l'affichage.
 */
function afsac_lead_render_value( $mode, $value ) {
	$value = (string) $value;

	if ( '' === trim( $value ) || '0' === $value ) {
		return '<span style="color:#8c8f94;">' . esc_html__( 'Non renseigné', 'afsac' ) . '</span>';
	}

	switch ( $mode ) {
		case 'email':
			return '<a href="' . esc_url( 'mailto:' . $value ) . '">' . esc_html( $value ) . '</a>';

		case 'phone':
			return '<a href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $value ) ) . '">' . esc_html( $value ) . '</a>';

		case 'url':
			return '<a href="' . esc_url( $value ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $value ) . '</a>';

		case 'longtext':
			return '<div style="white-space:pre-wrap;background:#fff;border:1px solid #dcdcde;border-left:4px solid #0054a4;padding:12px 14px;border-radius:3px;max-width:46em;">'
				. esc_html( $value ) . '</div>';

		case 'post':
			$post_id = absint( $value );
			$title   = $post_id ? get_the_title( $post_id ) : '';
			if ( ! $post_id || '' === $title ) {
				return '<span style="color:#8c8f94;">' . esc_html__( 'Non renseigné', 'afsac' ) . '</span>';
			}
			return '<a href="' . esc_url( (string) get_edit_post_link( $post_id ) ) . '">' . esc_html( $title ) . '</a>'
				. ' <span style="color:#8c8f94;">(#' . absint( $post_id ) . ')</span>';

		case 'date':
			$ts = strtotime( $value );
			return $ts
				? esc_html( wp_date( 'j F Y à H:i', $ts ) )
				: esc_html( $value );

		case 'edition':
			$file = function_exists( 'afsac_brochure_file' ) ? afsac_brochure_file( $value ) : null;
			return esc_html( $file ? $file['name'] . ' (' . $file['filename'] . ')' : $value );

		case 'statut':
		case 'type_org':
		case 'paiement':
		case 'langue_supports':
			return esc_html( afsac_lead_label( $mode, $value ) );

		default:
			return esc_html( $value );
	}
}

/**
 * Contenu de la boîte « Détail de la demande ».
 *
 * @param WP_Post $post Lead affiché.
 * @return void
 */
function afsac_lead_render_metabox( $post ) {
	$types  = afsac_lead_admin_types();
	$prefix = isset( $types[ $post->post_type ] ) ? $types[ $post->post_type ] : 'afsac_lead_';
	// Le type de schéma est le suffixe du CPT : afsac_message → « message ».
	$schema = afsac_lead_admin_schema( (string) substr( $post->post_type, strlen( 'afsac_' ) ) );

	foreach ( $schema as $section => $fields ) {
		echo '<h2 style="font-size:14px;margin:18px 0 6px;padding:0;">' . esc_html( $section ) . '</h2>';
		echo '<table class="widefat striped" style="margin-bottom:4px;"><tbody>';
		foreach ( $fields as $key => $def ) {
			list( $label, $mode ) = $def;
			$value                = get_post_meta( $post->ID, $prefix . $key, true );
			echo '<tr><th scope="row" style="width:16em;text-align:left;">' . esc_html( $label ) . '</th><td>'
				. afsac_lead_render_value( $mode, is_scalar( $value ) ? (string) $value : '' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappement fait dans afsac_lead_render_value().
				. '</td></tr>';
		}
		echo '</tbody></table>';
	}

	$email = (string) get_post_meta( $post->ID, $prefix . 'email', true );
	if ( $email && is_email( $email ) ) {
		$subject = sprintf(
			/* translators: %s: nom du site. */
			__( 'Votre demande auprès de %s', 'afsac' ),
			get_bloginfo( 'name' )
		);
		echo '<p style="margin-top:16px;"><a class="button button-primary" href="'
			. esc_url( 'mailto:' . $email . '?subject=' . rawurlencode( $subject ) ) . '">'
			. esc_html__( 'Répondre à cette personne', 'afsac' ) . '</a></p>';
	}
}

/**
 * Enregistre la boîte sur les deux écrans de leads.
 *
 * @return void
 */
function afsac_lead_register_metabox() {
	foreach ( array_keys( afsac_lead_admin_types() ) as $screen ) {
		add_meta_box(
			'afsac_lead_details',
			__( 'Détail de la demande', 'afsac' ),
			'afsac_lead_render_metabox',
			$screen,
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'afsac_lead_register_metabox' );
