<?php
/**
 * Groupes de champs ACF (version GRATUITE uniquement).
 *
 * Enregistrés par code via acf_add_local_field_group() : la définition vit
 * dans le plugin (versionnée), pas en base. Aucun type Pro n'est utilisé
 * (pas de Répéteur, Contenu flexible, Galerie ni Clone).
 *
 * Rappels CLAUDE.md :
 *   - area / famille / langue / modalité sont des TAXONOMIES, pas des champs.
 *   - La description commerciale d'une formation reste dans l'éditeur Gutenberg.
 *   - Le lien session -> formation s'appuie sur la méta existante
 *     `_afsac_formation_id` (voir includes/relations.php).
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre les groupes de champs ACF du site.
 *
 * Accroché à acf/init pour s'exécuter uniquement quand ACF est chargé.
 *
 * @return void
 */
function afsac_register_acf_field_groups() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	afsac_register_acf_group_formation();
	afsac_register_acf_group_session();
	afsac_register_acf_group_brochures();
	afsac_register_acf_group_formations_services();
	afsac_register_acf_group_temoignage();
	afsac_register_acf_group_reference();
	afsac_register_acf_group_area_color();
	afsac_register_acf_group_service();
	afsac_register_acf_group_area_intro();
	afsac_register_acf_group_formation_listing();
	afsac_register_acf_group_page_hero();
}
add_action( 'acf/init', 'afsac_register_acf_field_groups' );

/**
 * Groupe « En-tête de page » — sur les pages aux templates Contact et Actualités.
 *
 * Sur-titre (eyebrow) + chapô éditables, affichés en overlay sur le hero illustré.
 * Le titre du hero reste le titre de la page (H1).
 *
 * @return void
 */
function afsac_register_acf_group_page_hero() {
	acf_add_local_field_group(
		array(
			'key'             => 'group_afsac_page_hero',
			'title'           => __( 'En-tête de page', 'afsac' ),
			'location'        => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-contact.php',
					),
				),
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-actualites.php',
					),
				),
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-formations-services.php',
					),
				),
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-qui-sommes-nous.php',
					),
				),
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-references-temoignages.php',
					),
				),
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-catalogue.php',
					),
				),
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-espace-participant.php',
					),
				),
			),
			'menu_order'      => 0,
			'position'        => 'acf_after_title',
			'label_placement' => 'top',
			'active'          => true,
			'fields'          => array(
				array(
					'key'          => 'field_afsac_page_eyebrow',
					'label'        => __( 'Sur-titre', 'afsac' ),
					'name'         => 'afsac_page_eyebrow',
					'type'         => 'text',
					'instructions' => __( 'Court label affiché au-dessus du titre (ex. « Nous contacter »).', 'afsac' ),
				),
				array(
					'key'          => 'field_afsac_page_chapo',
					'label'        => __( 'Chapô', 'afsac' ),
					'name'         => 'afsac_page_chapo',
					'type'         => 'textarea',
					'rows'         => 2,
					'new_lines'    => '',
					'instructions' => __( 'Une phrase d’introduction affichée dans le hero, sous le titre.', 'afsac' ),
				),
				array(
					'key'          => 'field_afsac_intro_eyebrow',
					'label'        => __( 'Intro — sur-titre', 'afsac' ),
					'name'         => 'afsac_intro_eyebrow',
					'type'         => 'text',
					'instructions' => __( 'Sur-titre (orange) de la carte d’intro sous le hero (ex. « Notre mission »).', 'afsac' ),
				),
				array(
					'key'          => 'field_afsac_intro_title',
					'label'        => __( 'Intro — titre', 'afsac' ),
					'name'         => 'afsac_intro_title',
					'type'         => 'text',
					'instructions' => __( 'Titre court de la carte d’intro sous le hero.', 'afsac' ),
				),
			),
		)
	);
}

/**
 * Groupe « Classement (liste de cours) » — sur le CPT afsac_formation.
 *
 * Méthode de dispensation (onglets) + tarif réduit (checkbox) de la page
 * « Liste de cours ». Le type de cours est une taxonomie (afsac_type).
 *
 * @return void
 */
function afsac_register_acf_group_formation_listing() {
	acf_add_local_field_group(
		array(
			'key'             => 'group_afsac_formation_listing',
			'title'           => __( 'Classement (liste de cours)', 'afsac' ),
			'location'        => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'afsac_formation',
					),
				),
			),
			'menu_order'      => 5,
			'position'        => 'side',
			'style'           => 'default',
			'label_placement' => 'top',
			'active'          => true,
			'show_in_rest'    => true,
			'fields'          => array(
				array(
					'key'           => 'field_afsac_methode',
					'label'         => __( 'Méthode de dispensation', 'afsac' ),
					'name'          => 'afsac_methode',
					'type'          => 'select',
					'instructions'  => __( 'Pilote les onglets « Avec instructeur » / « Auto-rythmé ».', 'afsac' ),
					'choices'       => array(
						'instructeur' => __( 'Avec instructeur', 'afsac' ),
						'autorythme'  => __( 'Auto-rythmé (en ligne)', 'afsac' ),
					),
					'default_value' => 'instructeur',
					'allow_null'    => 0,
					'multiple'      => 0,
					'ui'            => 0,
					'return_format' => 'value',
				),
				array(
					'key'           => 'field_afsac_tarif_reduit',
					'label'         => __( 'Tarif réduit (États ACA)', 'afsac' ),
					'name'          => 'afsac_tarif_reduit',
					'type'          => 'true_false',
					'instructions'  => __( 'Tarif préférentiel pour les États membres ACA.', 'afsac' ),
					'ui'            => 1,
					'default_value' => 0,
				),
			),
		)
	);
}

/**
 * Groupe « Présentation du domaine » — chapô éditorial sur le terme afsac_area.
 *
 * Distinct de la description courte (catalogue) et de la meta SEO (Rank Math,
 * éditable par terme) : texte d'introduction affiché sous le H1 de l'archive.
 *
 * @return void
 */
function afsac_register_acf_group_area_intro() {
	acf_add_local_field_group(
		array(
			'key'             => 'group_afsac_area_content',
			'title'           => __( 'Présentation du domaine', 'afsac' ),
			'location'        => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'afsac_area',
					),
				),
			),
			'menu_order'      => 1,
			'position'        => 'normal',
			'style'           => 'default',
			'label_placement' => 'top',
			'active'          => true,
			'show_in_rest'    => false,
			'description'     => __( 'Chapô affiché sous le titre de la page « Liste de cours » du domaine.', 'afsac' ),
			'fields'          => array(
				array(
					'key'          => 'field_afsac_area_intro',
					'label'        => __( 'Chapô (introduction)', 'afsac' ),
					'name'         => 'afsac_area_intro',
					'type'         => 'textarea',
					'instructions' => __( 'Texte d’introduction du domaine. À défaut, la description courte du terme est utilisée.', 'afsac' ),
					'rows'         => 3,
					'new_lines'    => '',
				),
			),
		)
	);
}

/**
 * Groupe « Détails service » — sur le CPT afsac_service.
 *
 * Le titre du service = titre du post. Les valeurs du select « icône »
 * correspondent aux slugs du registre d'icônes du gabarit (services.php).
 *
 * @return void
 */
function afsac_register_acf_group_service() {
	acf_add_local_field_group(
		array(
			'key'             => 'group_afsac_service',
			'title'           => __( 'Détails service', 'afsac' ),
			'location'        => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'afsac_service',
					),
				),
			),
			'menu_order'      => 0,
			'position'        => 'normal',
			'style'           => 'default',
			'label_placement' => 'top',
			'active'          => true,
			'show_in_rest'    => true,
			'description'     => __( 'Champs de la carte service du hub Formations & Services.', 'afsac' ),
			'fields'          => array(
				array(
					'key'          => 'field_afsac_service_description',
					'label'        => __( 'Description', 'afsac' ),
					'name'         => 'afsac_service_description',
					'type'         => 'textarea',
					'instructions' => __( 'Texte court affiché sur la carte.', 'afsac' ),
					'rows'         => 4,
					'new_lines'    => '',
				),
				array(
					'key'           => 'field_afsac_service_icone',
					'label'         => __( 'Icône', 'afsac' ),
					'name'          => 'afsac_service_icone',
					'type'          => 'select',
					'instructions'  => __( 'Pictogramme de la carte. Vide = aucune icône.', 'afsac' ),
					'choices'       => array(
						'document'   => __( 'Document / audit', 'afsac' ),
						'crayon'     => __( 'Crayon / ingénierie', 'afsac' ),
						'batiment'   => __( 'Bâtiment / centre', 'afsac' ),
						'equipe'     => __( 'Équipe / experts', 'afsac' ),
						'calendrier' => __( 'Calendrier / intra', 'afsac' ),
						'conseil'    => __( 'Bulle / conseil', 'afsac' ),
					),
					'allow_null'    => 1,
					'multiple'      => 0,
					'ui'            => 0,
					'return_format' => 'value',
				),
				array(
					'key'          => 'field_afsac_service_lien',
					'label'        => __( 'Lien « En savoir plus »', 'afsac' ),
					'name'         => 'afsac_service_lien',
					'type'         => 'url',
					'instructions' => __( 'Optionnel. Si vide, la carte pointe vers la page du service.', 'afsac' ),
				),
			),
		)
	);
}

/**
 * Groupe « Détails témoignage » — sur le CPT afsac_temoignage.
 *
 * Les noms de champs correspondent EXACTEMENT à ceux déjà lus par le thème
 * (afsac_temoignage_org / _citation / _auteur / _fonction). Les 3 derniers
 * champs sont prospectifs (section « Voix » de la page Références, câblage à venir).
 *
 * @return void
 */
function afsac_register_acf_group_temoignage() {
	acf_add_local_field_group(
		array(
			'key'             => 'group_afsac_temoignage',
			'title'           => __( 'Détails témoignage', 'afsac' ),
			'location'        => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'afsac_temoignage',
					),
				),
			),
			'menu_order'      => 0,
			'position'        => 'normal',
			'style'           => 'default',
			'label_placement' => 'top',
			'active'          => true,
			'show_in_rest'    => true,
			'description'     => __( 'Champs structurés du témoignage. La citation et l’auteur retombent sur le contenu / le titre s’ils sont vides.', 'afsac' ),
			'fields'          => array(
				array(
					'key'          => 'field_afsac_temoignage_auteur',
					'label'        => __( 'Auteur', 'afsac' ),
					'name'         => 'afsac_temoignage_auteur',
					'type'         => 'text',
					'instructions' => __( 'Nom de la personne citée (ex. Mme. Aïcha Diallo). À défaut, le titre du témoignage est utilisé.', 'afsac' ),
				),
				array(
					'key'          => 'field_afsac_temoignage_fonction',
					'label'        => __( 'Fonction', 'afsac' ),
					'name'         => 'afsac_temoignage_fonction',
					'type'         => 'text',
					'instructions' => __( 'Fonction · entité (ex. Directrice Sûreté · ANAC Sénégal).', 'afsac' ),
				),
				array(
					'key'          => 'field_afsac_temoignage_org',
					'label'        => __( 'Organisation', 'afsac' ),
					'name'         => 'afsac_temoignage_org',
					'type'         => 'text',
					'instructions' => __( 'Institution affichée en étiquette (ex. ANAC Sénégal).', 'afsac' ),
				),
				array(
					'key'          => 'field_afsac_temoignage_citation',
					'label'        => __( 'Citation', 'afsac' ),
					'name'         => 'afsac_temoignage_citation',
					'type'         => 'textarea',
					'instructions' => __( 'Texte du témoignage. À défaut, le contenu de l’éditeur est utilisé.', 'afsac' ),
					'rows'         => 4,
					'new_lines'    => '',
				),
				array(
					'key'           => 'field_afsac_temoignage_langue',
					'label'         => __( 'Langue', 'afsac' ),
					'name'          => 'afsac_temoignage_langue',
					'type'          => 'select',
					'instructions'  => __( 'Langue du témoignage (badge). Optionnel.', 'afsac' ),
					'choices'       => array(
						'FR' => 'FR',
						'EN' => 'EN',
						'AR' => 'AR',
					),
					'allow_null'    => 1,
					'multiple'      => 0,
					'ui'            => 0,
					'return_format' => 'value',
				),
				array(
					'key'          => 'field_afsac_temoignage_duree',
					'label'        => __( 'Durée (vidéo)', 'afsac' ),
					'name'         => 'afsac_temoignage_duree',
					'type'         => 'text',
					'instructions' => __( 'Durée de la vidéo au format mm:ss (ex. 04:12). Optionnel.', 'afsac' ),
				),
				array(
					'key'          => 'field_afsac_temoignage_video_url',
					'label'        => __( 'URL vidéo', 'afsac' ),
					'name'         => 'afsac_temoignage_video_url',
					'type'         => 'url',
					'instructions' => __( 'Lien de la vidéo du témoignage. Optionnel.', 'afsac' ),
				),
			),
		)
	);
}

/**
 * Groupe « Détails référence » — sur le CPT afsac_reference.
 *
 * Alimente les DEUX blocs de la page « Références & Témoignages » :
 *   - « Nos partenaires » : bandeau de logos, alimenté par les fiches cochées
 *     `afsac_reference_partenaire` (le logo est l'image à la une). Si AUCUNE
 *     fiche n'est cochée, le bandeau prend toutes les références publiées.
 *   - « Nos références »   : grille des institutions (pays + type).
 *
 * @return void
 */
function afsac_register_acf_group_reference() {
	acf_add_local_field_group(
		array(
			'key'             => 'group_afsac_reference',
			'title'           => __( 'Détails référence', 'afsac' ),
			'location'        => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'afsac_reference',
					),
				),
			),
			'menu_order'      => 0,
			'position'        => 'normal',
			'style'           => 'default',
			'label_placement' => 'top',
			'active'          => true,
			'show_in_rest'    => true,
			'description'     => __( 'Le nom de l’institution est le titre ; le logo est l’image à la une.', 'afsac' ),
			'fields'          => array(
				array(
					'key'          => 'field_afsac_reference_pays',
					'label'        => __( 'Pays', 'afsac' ),
					'name'         => 'afsac_reference_pays',
					'type'         => 'text',
					'instructions' => __( 'Pays ou zone (ex. Tunisie, Multilatéral). Affiché sous le nom.', 'afsac' ),
				),
				array(
					'key'           => 'field_afsac_reference_type',
					'label'         => __( 'Type d’institution', 'afsac' ),
					'name'          => 'afsac_reference_type',
					'type'          => 'select',
					'instructions'  => __( 'Étiquette affichée en bas de la carte. Optionnel.', 'afsac' ),
					// Slugs stables en clé (cf. afsac_get_reference_types()) : le libellé
					// traduit est résolu à l'affichage, jamais stocké en base.
					'choices'       => afsac_get_reference_types(),
					'allow_null'    => 1,
					'multiple'      => 0,
					'ui'            => 0,
					'return_format' => 'value',
				),
				array(
					'key'           => 'field_afsac_reference_niveau',
					'label'         => __( 'Niveau de coopération', 'afsac' ),
					'name'          => 'afsac_reference_niveau',
					'type'          => 'select',
					'instructions'  => __( 'Groupe d’affichage sur la page « Références & Témoignages » (découpage de la brochure).', 'afsac' ),
					// Slugs stables en clé (cf. afsac_get_reference_levels()).
					'choices'       => afsac_get_reference_levels(),
					'allow_null'    => 1,
					'multiple'      => 0,
					'ui'            => 0,
					'return_format' => 'value',
				),
				array(
					'key'           => 'field_afsac_reference_partenaire',
					'label'         => __( 'Afficher dans le bandeau « Nos partenaires »', 'afsac' ),
					'name'          => 'afsac_reference_partenaire',
					'type'          => 'true_false',
					'instructions'  => __( 'À cocher pour les logos du bandeau défilant (accueil + page Références). Nécessite une image à la une.', 'afsac' ),
					'ui'            => 1,
					'default_value' => 0,
				),
			),
		)
	);
}

/**
 * Groupe « Couleur du domaine » — sur la taxonomie afsac_area (écran du terme).
 *
 * Champ color_picker (gratuit) name = afsac_area_color : source unique de
 * l’accent d’un domaine OACI, lu par afsac_get_area_accent_color(). Vide = repli
 * navy géré côté thème.
 *
 * @return void
 */
function afsac_register_acf_group_area_color() {
	acf_add_local_field_group(
		array(
			'key'             => 'group_afsac_area_color',
			'title'           => __( 'Couleur du domaine', 'afsac' ),
			'location'        => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'afsac_area',
					),
				),
			),
			'menu_order'      => 0,
			'position'        => 'normal',
			'style'           => 'default',
			'label_placement' => 'top',
			'active'          => true,
			'show_in_rest'    => false,
			'description'     => __( 'Couleur d’accent du domaine OACI (filet des cartes du catalogue & en-tête d’archive).', 'afsac' ),
			'fields'          => array(
				array(
					'key'           => 'field_afsac_area_color',
					'label'         => __( 'Couleur d’accent', 'afsac' ),
					'name'          => 'afsac_area_color',
					'type'          => 'color_picker',
					'instructions'  => __( 'Couleur hexadécimale d’accent. Laisser vide pour le repli bleu marine du thème.', 'afsac' ),
					'return_format' => 'string',
				),
			),
		)
	);
}

/**
 * Groupe « Formations & Services (vue d'ensemble) » — sur le page template
 * AFSAC — Formations & Services.
 *
 * Données éditoriales du hub, réglées une seule fois sur la page en langue par
 * défaut (le thème lit la page canonique via afsac_get_fs_field()).
 *
 * @return void
 */
function afsac_register_acf_group_formations_services() {
	acf_add_local_field_group(
		array(
			'key'             => 'group_afsac_formations_services',
			'title'           => __( 'Formations & Services (vue d’ensemble)', 'afsac' ),
			'location'        => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-formations-services.php',
					),
				),
			),
			'menu_order'      => 2,
			'position'        => 'normal',
			'style'           => 'default',
			'label_placement' => 'top',
			'active'          => true,
			'show_in_rest'    => false,
			'description'     => __( 'Données du hub Formations & Services (réglées une fois, sur la page en langue par défaut).', 'afsac' ),
			'fields'          => array(
				array(
					'key'           => 'field_afsac_course_count',
					'label'         => __( 'Nombre de cours (périmètre OACI)', 'afsac' ),
					'name'          => 'afsac_course_count',
					'type'          => 'number',
					'instructions'  => __( 'Valeur éditoriale du périmètre du programme (ex. 483) — PAS un comptage de publications.', 'afsac' ),
					'default_value' => 483,
					'min'           => 0,
					'step'          => 1,
				),
				array(
					'key'           => 'field_afsac_catalogue_pdf',
					'label'         => __( 'Catalogue complet (PDF)', 'afsac' ),
					'name'          => 'afsac_catalogue_pdf',
					'type'          => 'file',
					'return_format' => 'array',
					'library'       => 'all',
					'mime_types'    => 'pdf',
					'instructions'  => __( 'Catalogue PDF complet (peut rester vide pour l’instant).', 'afsac' ),
				),
			),
		)
	);
}

/**
 * Groupe « Brochures » — sur la page d'accueil (Front Page).
 *
 * PDF global de la section Documentation (désormais dans le footer, donc présent
 * sur TOUTES les pages). Un seul téléchargement : le champ `afsac_brochure`
 * regroupe les programmes FR + EN dans un unique PDF. Les deux champs par langue
 * sont conservés en REPLI (le thème les utilise seulement si le PDF complet n'est
 * pas encore renseigné) afin de ne perdre aucun fichier
 * déjà téléversé. Édité une seule fois sur la home canonique, lu par le thème
 * via la home canonique. return_format=array pour exposer
 * url + filesize + filename + mime_type au template.
 *
 * @return void
 */
function afsac_register_acf_group_brochures() {
	$afsac_file_field = static function ( $key, $name, $label, $instructions ) {
		return array(
			'key'           => $key,
			'label'         => $label,
			'name'          => $name,
			'type'          => 'file',
			'return_format' => 'array',
			'library'       => 'all',
			'mime_types'    => 'pdf',
			'instructions'  => $instructions,
		);
	};

	acf_add_local_field_group(
		array(
			'key'             => 'group_afsac_brochures',
			'title'           => __( 'Brochure — programme de formation', 'afsac' ),
			'location'        => array(
				array(
					array(
						'param'    => 'page_type',
						'operator' => '==',
						'value'    => 'front_page',
					),
				),
			),
			'menu_order'      => 1,
			'position'        => 'normal',
			'style'           => 'default',
			'label_placement' => 'top',
			'active'          => true,
			'show_in_rest'    => false,
			'description'     => __( 'PDF téléchargeable de la section Documentation (affichée dans le pied de page, sur toutes les pages).', 'afsac' ),
			'fields'          => array(
				$afsac_file_field(
					'field_afsac_brochure',
					'afsac_brochure',
					__( 'Programme complet — PDF (FR · EN)', 'afsac' ),
					__( 'PDF UNIQUE regroupant les programmes en français et en anglais. C’est ce fichier qui est proposé au téléchargement dans le pied de page, sur tout le site.', 'afsac' )
				),
				$afsac_file_field(
					'field_afsac_brochure_fr',
					'afsac_brochure_fr',
					__( 'Repli — Brochure Français (PDF)', 'afsac' ),
					__( 'Optionnel. Utilisé uniquement si le « Programme complet » ci-dessus n’est pas renseigné.', 'afsac' )
				),
				$afsac_file_field(
					'field_afsac_brochure_en',
					'afsac_brochure_en',
					__( 'Repli — Brochure English (PDF)', 'afsac' ),
					__( 'Optionnel (legacy). Conservé pour ne pas perdre un fichier déjà téléversé.', 'afsac' )
				),
				/*
				 * Le champ « Repli — Brochure العربية » a été RETIRÉ (demande client,
				 * 17/08/2026 : la brochure n'est plus proposée qu'en FR/EN). La méta
				 * `afsac_brochure_ar` reste en base si un fichier y avait été téléversé,
				 * mais afsac_brochure_langs() ne la lit plus : laisser la boîte de
				 * téléversement ouvrirait sur une édition que le site ne propose plus.
				 */
			),
		)
	);
}

/**
 * Groupe « Détails formation » — sur le CPT afsac_formation.
 *
 * @return void
 */
function afsac_register_acf_group_formation() {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_afsac_formation_details',
			'title'                 => __( 'Détails formation', 'afsac' ),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'afsac_formation',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'active'                => true,
			'show_in_rest'          => true,
			'description'           => __( 'Champs structurés de la formation. La description commerciale principale se rédige dans l’éditeur de blocs.', 'afsac' ),
			'fields'                => array(
				array(
					'key'          => 'field_afsac_formation_abbreviation',
					'label'        => __( 'Abréviation', 'afsac' ),
					'name'         => 'afsac_abbreviation',
					'type'         => 'text',
					'instructions' => __( 'Acronyme court affiché à côté du titre (ex. TDC EN).', 'afsac' ),
				),
				array(
					'key'          => 'field_afsac_formation_code',
					'label'        => __( 'Référence (complète)', 'afsac' ),
					'name'         => 'afsac_code',
					'type'         => 'text',
					'instructions' => __( 'ex. 214001/TDCEN', 'afsac' ),
					'required'     => 0,
				),
				array(
					'key'          => 'field_afsac_formation_objectifs',
					'label'        => __( 'Objectifs', 'afsac' ),
					'name'         => 'afsac_objectifs',
					'type'         => 'wysiwyg',
					'instructions' => __( 'Objectifs pédagogiques de la formation.', 'afsac' ),
					'tabs'         => 'all',
					'toolbar'      => 'full',
					'media_upload' => 1,
				),
				array(
					'key'          => 'field_afsac_formation_public_cible',
					'label'        => __( 'Public cible', 'afsac' ),
					'name'         => 'afsac_public_cible',
					'type'         => 'wysiwyg',
					'instructions' => __( 'À qui s’adresse cette formation.', 'afsac' ),
					'tabs'         => 'all',
					'toolbar'      => 'full',
					'media_upload' => 1,
				),
				array(
					'key'          => 'field_afsac_formation_prerequis',
					'label'        => __( 'Prérequis', 'afsac' ),
					'name'         => 'afsac_prerequis',
					'type'         => 'textarea',
					'instructions' => __( 'Connaissances ou conditions requises avant l’inscription.', 'afsac' ),
					'rows'         => 4,
					'new_lines'    => 'wpautop',
				),
				array(
					'key'          => 'field_afsac_formation_resultats',
					'label'        => __( 'Résultats attendus', 'afsac' ),
					'name'         => 'afsac_resultats',
					'type'         => 'wysiwyg',
					'instructions' => __( 'Compétences acquises à l’issue de la formation.', 'afsac' ),
					'tabs'         => 'all',
					'toolbar'      => 'full',
					'media_upload' => 1,
				),
				array(
					'key'          => 'field_afsac_formation_structure',
					'label'        => __( 'Structure (modules)', 'afsac' ),
					'name'         => 'afsac_structure',
					'type'         => 'textarea',
					'instructions' => __( 'Un module par ligne. Le gabarit numérote automatiquement (Module 0, 1, 2…) et met le préfixe en gras.', 'afsac' ),
					'rows'         => 8,
					'new_lines'    => '',
				),
				array(
					'key'           => 'field_afsac_formation_developpe_par',
					'label'         => __( 'Développé par', 'afsac' ),
					'name'          => 'afsac_developpe_par',
					'type'          => 'text',
					'default_value' => 'ICAO · OACI',
				),
				array(
					'key'           => 'field_afsac_formation_developpe_par_detail',
					'label'         => __( 'Développé par (détail)', 'afsac' ),
					'name'          => 'afsac_developpe_par_detail',
					'type'          => 'text',
					'default_value' => 'International Civil Aviation Organization · Montréal, Canada',
				),
				array(
					'key'          => 'field_afsac_formation_autres_langues',
					'label'        => __( 'Autres langues', 'afsac' ),
					'name'         => 'afsac_autres_langues',
					'type'         => 'text',
					'instructions' => __( 'Langues additionnelles, séparées par des virgules (ex. Français, Espagnol, Russe).', 'afsac' ),
				),
				array(
					'key'           => 'field_afsac_formation_niveau',
					'label'         => __( 'Niveau', 'afsac' ),
					'name'          => 'afsac_niveau',
					'type'          => 'select',
					'instructions'  => __( 'Valeur = slug (le libellé est traduit côté gabarit). Liste éditable ici.', 'afsac' ),
					'choices'       => array(
						'initiation'    => __( 'Initiation', 'afsac' ),
						'fondamental'   => __( 'Fondamental', 'afsac' ),
						'intermediaire' => __( 'Intermédiaire', 'afsac' ),
						'avance'        => __( 'Avancé', 'afsac' ),
						'technique'     => __( 'Technique', 'afsac' ),
						'management'    => __( 'Management', 'afsac' ),
					),
					'allow_null'    => 1,
					'multiple'      => 0,
					'ui'            => 0,
					'return_format' => 'value',
				),
				array(
					'key'          => 'field_afsac_formation_public_resume',
					'label'        => __( 'Public (résumé)', 'afsac' ),
					'name'         => 'afsac_public_resume',
					'type'         => 'text',
					'instructions' => __( 'Version courte pour la sidebar (ex. Cadres & experts). Le détaillé reste dans Public cible.', 'afsac' ),
				),
				array(
					'key'          => 'field_afsac_formation_duree',
					'label'        => __( 'Durée', 'afsac' ),
					'name'         => 'afsac_duree',
					'type'         => 'text',
					'instructions' => __( 'Format libre, ex. « 5 jours ».', 'afsac' ),
					'placeholder'  => __( '5 jours', 'afsac' ),
				),
				array(
					'key'          => 'field_afsac_formation_frais_montant',
					'label'        => __( 'Frais (montant)', 'afsac' ),
					'name'         => 'afsac_frais_montant',
					'type'         => 'number',
					'instructions' => __( 'Montant seul, sans devise (ex. 1800).', 'afsac' ),
					'min'          => 0,
				),
				array(
					'key'           => 'field_afsac_formation_devise',
					'label'         => __( 'Devise', 'afsac' ),
					'name'          => 'afsac_devise',
					'type'          => 'select',
					'choices'       => array(
						'USD' => 'USD',
						'EUR' => 'EUR',
						'TND' => 'TND',
					),
					'default_value' => 'USD',
					'allow_null'    => 0,
					'return_format' => 'value',
				),
				array(
					'key'           => 'field_afsac_formation_certificat',
					'label'         => __( 'Certificat délivré', 'afsac' ),
					'name'          => 'afsac_certificat',
					'type'          => 'true_false',
					'instructions'  => __( 'Cocher si la formation donne lieu à un certificat.', 'afsac' ),
					'ui'            => 1,
					'ui_on_text'    => __( 'Oui', 'afsac' ),
					'ui_off_text'   => __( 'Non', 'afsac' ),
					'default_value' => 0,
				),
				array(
					'key'               => 'field_afsac_formation_certificat_intitule',
					'label'             => __( 'Intitulé du certificat', 'afsac' ),
					'name'              => 'afsac_certificat_intitule',
					'type'              => 'text',
					'instructions'      => __( 'Nom exact du certificat délivré.', 'afsac' ),
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_afsac_formation_certificat',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
				),
				array(
					'key'          => 'field_afsac_formation_note',
					'label'        => __( 'Note (★, décoratif)', 'afsac' ),
					'name'         => 'afsac_note',
					'type'         => 'number',
					'instructions' => __( 'Optionnel, purement décoratif. N’est PAS émis en données structurées (évite tout signalement de faux avis).', 'afsac' ),
					'min'          => 0,
					'max'          => 5,
					'step'         => 0.5,
				),
			),
		)
	);
}

/**
 * Groupe « Détails session » — sur le CPT afsac_session.
 *
 * @return void
 */
function afsac_register_acf_group_session() {
	acf_add_local_field_group(
		array(
			'key'             => 'group_afsac_session_details',
			'title'           => __( 'Détails session', 'afsac' ),
			'location'        => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'afsac_session',
					),
				),
			),
			'menu_order'      => 0,
			'position'        => 'normal',
			'style'           => 'default',
			'label_placement' => 'top',
			'active'          => true,
			'show_in_rest'    => true,
			'fields'          => array(
				array(
					// IMPORTANT : name = clé de méta existante (includes/relations.php).
					// ACF lit/écrit la valeur dans `_afsac_formation_id`.
					'key'           => 'field_afsac_session_formation',
					'label'         => __( 'Formation liée', 'afsac' ),
					'name'          => '_afsac_formation_id',
					'type'          => 'post_object',
					'instructions'  => __( 'Formation dont cette session est une occurrence planifiée.', 'afsac' ),
					'required'      => 1,
					'post_type'     => array( 'afsac_formation' ),
					'return_format' => 'id',
					'multiple'      => 0,
					'allow_null'    => 0,
					'ui'            => 1,
				),
				array(
					'key'            => 'field_afsac_session_date_debut',
					'label'          => __( 'Date de début', 'afsac' ),
					'name'           => 'afsac_date_debut',
					'type'           => 'date_picker',
					'required'       => 1,
					'display_format' => 'd/m/Y',
					'return_format'  => 'Ymd',
					'first_day'      => 1,
				),
				array(
					'key'            => 'field_afsac_session_date_fin',
					'label'          => __( 'Date de fin', 'afsac' ),
					'name'           => 'afsac_date_fin',
					'type'           => 'date_picker',
					'display_format' => 'd/m/Y',
					'return_format'  => 'Ymd',
					'first_day'      => 1,
				),
				array(
					'key'   => 'field_afsac_session_lieu',
					'label' => __( 'Lieu', 'afsac' ),
					'name'  => 'afsac_lieu',
					'type'  => 'text',
				),
				array(
					// Réutilise la taxonomie afsac_langue (pas de duplication).
					// ACF affecte le terme au post (save/load terms activés).
					'key'           => 'field_afsac_session_langue',
					'label'         => __( 'Langue de la session', 'afsac' ),
					'name'          => 'afsac_langue_session',
					'type'          => 'taxonomy',
					'instructions'  => __( 'Langue d’animation (réutilise la taxonomie « Langue de formation »).', 'afsac' ),
					'taxonomy'      => 'afsac_langue',
					'field_type'    => 'radio',
					'add_term'      => 1,
					'save_terms'    => 1,
					'load_terms'    => 1,
					'return_format' => 'id',
					'allow_null'    => 0,
				),
				array(
					'key'           => 'field_afsac_session_places',
					'label'         => __( 'Places disponibles', 'afsac' ),
					'name'          => 'afsac_places',
					'type'          => 'number',
					'instructions'  => __( 'Nombre de places restantes (optionnel).', 'afsac' ),
					'required'      => 0,
					'min'           => 0,
					'step'          => 1,
				),
				array(
					'key'           => 'field_afsac_session_statut',
					'label'         => __( 'Statut', 'afsac' ),
					'name'          => 'afsac_statut',
					'type'          => 'select',
					'required'      => 1,
					'choices'       => array(
						'ouvert'  => __( 'Ouvert', 'afsac' ),
						'complet' => __( 'Complet', 'afsac' ),
						'archive' => __( 'Archivé', 'afsac' ),
					),
					'default_value' => 'ouvert',
					'ui'            => 1,
					'return_format' => 'value',
				),
				array(
					'key'          => 'field_afsac_session_hote',
					'label'        => __( 'Institution hôte', 'afsac' ),
					'name'         => 'afsac_hote',
					'type'         => 'text',
					'instructions' => __( 'Organisation qui héberge la session (ex. Autorité de l’aviation civile, AFSAC).', 'afsac' ),
				),
				array(
					'key'          => 'field_afsac_session_contact_nom',
					'label'        => __( 'Contact — nom', 'afsac' ),
					'name'         => 'afsac_session_contact_nom',
					'type'         => 'text',
					'instructions' => __( 'Personne de contact pour cette session (optionnel).', 'afsac' ),
				),
				array(
					'key'          => 'field_afsac_session_contact_email',
					'label'        => __( 'Contact — e-mail', 'afsac' ),
					'name'         => 'afsac_session_contact_email',
					'type'         => 'email',
					'instructions' => __( 'E-mail de contact affiché sur la page d’inscription (optionnel).', 'afsac' ),
				),
				array(
					'key'           => 'field_afsac_session_lat',
					'label'         => __( 'Latitude', 'afsac' ),
					'name'          => 'afsac_lat',
					'type'          => 'number',
					'instructions'  => __( 'Coordonnée pour la vue carte (optionnel). À défaut, déduite de la ville du lieu.', 'afsac' ),
					'required'      => 0,
					'min'           => -90,
					'max'           => 90,
					'step'          => 'any',
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_afsac_session_lng',
					'label'         => __( 'Longitude', 'afsac' ),
					'name'          => 'afsac_lng',
					'type'          => 'number',
					'instructions'  => __( 'Coordonnée pour la vue carte (optionnel). À défaut, déduite de la ville du lieu.', 'afsac' ),
					'required'      => 0,
					'min'           => -180,
					'max'           => 180,
					'step'          => 'any',
					'wrapper'       => array( 'width' => '50' ),
				),
			),
		)
	);
}
