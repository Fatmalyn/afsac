<?php
/**
 * Import / export du catalogue par TABLEUR (CSV ou XLSX).
 *
 * Destiné au chargement en masse du catalogue client (~500 cours TRAINAIR PLUS
 * + AVSEC) : le client remplit un classeur (modèle fourni), on le téléverse une
 * fois, tout est créé ; ensuite chaque fiche reste modifiable normalement dans
 * wp-admin, et on peut en ajouter à la main sans repasser par le tableur.
 *
 * Principes :
 *   - IDEMPOTENT. La colonne `cle` est l'identifiant stable de la fiche
 *     (méta `_afsac_import_key`, la même que celle des seeds AVSEC / TRAINAIR).
 *     Ré-importer le fichier MET À JOUR les fiches existantes, ne duplique pas.
 *   - CELLULE VIDE = ON NE TOUCHE À RIEN (les saisies faites dans wp-admin ne
 *     sont jamais écrasées par une colonne laissée vide). Pour vider un champ,
 *     saisir un tiret « - ».
 *   - MULTILINGUE. Une ligne = UNE fiche dans UNE langue. Deux lignes partageant
 *     la même `cle` avec des `langue_fiche` différentes sont automatiquement
 *     appariées par Polylang (traductions liées).
 *   - LISTES FERMÉES. Domaine / famille / modalité / type / niveau se saisissent
 *     avec un CODE court (menu déroulant dans le modèle .xlsx) ; les termes de
 *     taxonomie sont créés au besoin dans chaque langue et reliés entre eux.
 *   - PAR LOTS. Le traitement avance par paquets de lignes (pas de timeout PHP
 *     sur 1 000 lignes) avec une barre de progression.
 *   - SIMULATION. Une case « simulation » valide le fichier sans rien écrire.
 *
 * Écran : Formations → « Import / Export (tableur) ».
 * WP-CLI : wp afsac import <fichier> [--type=formations|sessions] [--dry-run]
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Nombre de lignes traitées par requête (compromis timeout / nombre de tours).
 */
const AFSAC_IMPORT_BATCH = 40;

/**
 * Nom de l'option qui porte l'état du job d'import en cours.
 */
const AFSAC_IMPORT_JOB_OPTION = 'afsac_import_job';

/**
 * Nom de l'option qui porte le compte rendu du dernier import terminé.
 */
const AFSAC_IMPORT_REPORT_OPTION = 'afsac_import_last_report';

/* -------------------------------------------------------------------------
 * 1. RÉFÉRENTIELS — listes fermées proposées au client (code => noms par langue)
 * ---------------------------------------------------------------------- */

/**
 * Langues de fiche gérées (doivent exister dans Polylang).
 *
 * @return array<string,string> code => libellé.
 */
function afsac_import_ref_langues_fiche() {
	return array(
		'fr' => 'Français',
		'en' => 'English',
		'ar' => 'العربية',
	);
}

/**
 * Familles commerciales (taxonomie afsac_famille).
 *
 * @return array<string,array<string,string>>
 */
function afsac_import_ref_familles() {
	return array(
		'trainair' => array(
			'fr' => 'TRAINAIR PLUS',
			'en' => 'TRAINAIR PLUS',
			'ar' => 'TRAINAIR PLUS',
		),
		'avsec'    => array(
			'fr' => 'AVSEC',
			'en' => 'AVSEC',
			'ar' => 'AVSEC',
		),
	);
}

/**
 * Les 11 domaines OACI (taxonomie afsac_area). Noms FR/EN = ceux de seed-terms.php.
 *
 * @return array<string,array<string,string>>
 */
function afsac_import_ref_areas() {
	return array(
		'aerodromes'        => array(
			'fr' => 'Aérodromes',
			'en' => 'Aerodromes',
			'ar' => 'المطارات',
		),
		'transport'         => array(
			'fr' => 'Transport aérien',
			'en' => 'Air Transport',
			'ar' => 'النقل الجوي',
		),
		'formation'         => array(
			'fr' => 'Formation et développement des compétences',
			'en' => 'Training & Competency Development',
			'ar' => 'التدريب وتطوير الكفاءات',
		),
		'environnement'     => array(
			'fr' => 'Environnement',
			'en' => 'Environment',
			'ar' => 'البيئة',
		),
		'gestion'           => array(
			'fr' => 'Gestion de l’aviation',
			'en' => 'Aviation Management',
			'ar' => 'إدارة الطيران',
		),
		'navigation'        => array(
			'fr' => 'Services de navigation aérienne',
			'en' => 'Air Navigation Services',
			'ar' => 'خدمات الملاحة الجوية',
		),
		'securite'          => array(
			'fr' => 'Sécurité des vols et gestion de la sécurité',
			'en' => 'Flight Safety & Safety Management',
			'ar' => 'سلامة الطيران وإدارة السلامة',
		),
		'surete'            => array(
			'fr' => 'Sûreté de l’aviation',
			'en' => 'Aviation Security',
			'ar' => 'أمن الطيران',
		),
		'facilitation'      => array(
			'fr' => 'Facilitation',
			'en' => 'Facilitation',
			'ar' => 'التسهيلات',
		),
		'droit'             => array(
			'fr' => 'Droit aérien',
			'en' => 'Aviation Law',
			'ar' => 'القانون الجوي',
		),
		'nouveaux-entrants' => array(
			'fr' => 'Nouveaux entrants dans l’aviation',
			'en' => 'Aviation New Entrants',
			'ar' => 'الوافدون الجدد في مجال الطيران',
		),
	);
}

/**
 * Modalités de dispense (taxonomie afsac_modalite).
 *
 * @return array<string,array<string,string>>
 */
function afsac_import_ref_modalites() {
	return array(
		'presentiel'  => array(
			'fr' => 'Présentiel',
			'en' => 'On-site',
			'ar' => 'حضوري',
		),
		'distanciel'  => array(
			'fr' => 'Distanciel',
			'en' => 'Online',
			'ar' => 'عن بُعد',
		),
		'hybride'     => array(
			'fr' => 'Hybride',
			'en' => 'Hybrid',
			'ar' => 'مختلط',
		),
		'intra'       => array(
			'fr' => 'Intra-entreprise',
			'en' => 'In-house',
			'ar' => 'داخل المؤسسة',
		),
		'sur-mesure'  => array(
			'fr' => 'Sur-mesure',
			'en' => 'Bespoke',
			'ar' => 'حسب الطلب',
		),
	);
}

/**
 * Types de cours (taxonomie afsac_type). Le code devient la méta `afsac_type_key`
 * lue par les gabarits (l'onglet « Programmes » teste la clé « programme »).
 *
 * @return array<string,array<string,string>>
 */
function afsac_import_ref_types() {
	return array(
		'oaci'      => array(
			'fr' => 'Cours OACI',
			'en' => 'ICAO Course',
			'ar' => 'دورة الإيكاو',
		),
		'stp'       => array(
			'fr' => 'Ensemble pédagogique normalisé (STP)',
			'en' => 'Standardized Training Package (STP)',
			'ar' => 'حزمة تدريبية موحدة (STP)',
		),
		'atelier'   => array(
			'fr' => 'Atelier',
			'en' => 'Workshop',
			'ar' => 'ورشة عمل',
		),
		'programme' => array(
			'fr' => 'Programme',
			'en' => 'Programme',
			'ar' => 'برنامج',
		),
	);
}

/**
 * Langues de DISPENSATION (taxonomie afsac_langue), pour la colonne
 * `langues_dispensees` et la langue d'animation d'une session.
 *
 * @return array<string,array<string,string>>
 */
function afsac_import_ref_langues_cours() {
	return array(
		'fr' => array(
			'fr' => 'Français',
			'en' => 'French',
			'ar' => 'الفرنسية',
		),
		'en' => array(
			'fr' => 'Anglais',
			'en' => 'English',
			'ar' => 'الإنجليزية',
		),
		'ar' => array(
			'fr' => 'Arabe',
			'en' => 'Arabic',
			'ar' => 'العربية',
		),
	);
}

/**
 * Niveaux (champ ACF afsac_niveau — valeurs = slugs du select).
 *
 * @return array<string,string>
 */
function afsac_import_ref_niveaux() {
	return array(
		'initiation'    => 'Initiation',
		'fondamental'   => 'Fondamental',
		'intermediaire' => 'Intermédiaire',
		'avance'        => 'Avancé',
		'technique'     => 'Technique',
		'management'    => 'Management',
	);
}

/**
 * Méthodes de dispensation (champ ACF afsac_methode : onglets de la liste de cours).
 *
 * @return array<string,string>
 */
function afsac_import_ref_methodes() {
	return array(
		'instructeur' => 'Avec instructeur',
		'autorythme'  => 'Auto-rythmé (en ligne)',
	);
}

/**
 * Devises (champ ACF afsac_devise).
 *
 * @return array<string,string>
 */
function afsac_import_ref_devises() {
	return array(
		'USD' => 'USD',
		'EUR' => 'EUR',
		'TND' => 'TND',
	);
}

/**
 * Statuts de session (champ ACF afsac_statut).
 *
 * @return array<string,string>
 */
function afsac_import_ref_statuts_session() {
	return array(
		'ouvert'  => 'Ouvert',
		'complet' => 'Complet',
		'archive' => 'Archivé',
	);
}

/**
 * Statuts de publication acceptés dans la colonne `statut`.
 *
 * @return array<string,string>
 */
function afsac_import_ref_statuts_post() {
	return array(
		'publier'   => 'publish',
		'publish'   => 'publish',
		'publie'    => 'publish',
		'brouillon' => 'draft',
		'draft'     => 'draft',
		'prive'     => 'private',
		'private'   => 'private',
	);
}

/* -------------------------------------------------------------------------
 * 2. SPÉCIFICATION DES COLONNES — source unique (modèle, import, export, doc)
 * ---------------------------------------------------------------------- */

/**
 * Colonnes d'un onglet du classeur.
 *
 * Chaque colonne : label (FR), req (obligatoire), help (aide affichée dans le
 * modèle), ex (exemple), list (référentiel pour le menu déroulant), w (largeur),
 * fmt ('text' pour forcer le format texte dans le .xlsx).
 *
 * @param string $sheet « formations » ou « sessions ».
 * @return array<string,array<string,mixed>>
 */
function afsac_import_spec( $sheet = 'formations' ) {
	if ( 'sessions' === $sheet ) {
		return array(
			'cle_cours'     => array(
				'label' => 'Clé du cours',
				'req'   => true,
				'help'  => 'Doit correspondre EXACTEMENT à une clé de l’onglet Formations.',
				'ex'    => 'tp-tdc-fr',
				'w'     => 24,
				'fmt'   => 'text',
			),
			'cle_session'   => array(
				'label' => 'Clé de la session',
				'req'   => true,
				'help'  => 'Identifiant court, unique POUR CE COURS (s1, s2, 2026-09…).',
				'ex'    => 's1',
				'w'     => 16,
				'fmt'   => 'text',
			),
			'titre'         => array(
				'label' => 'Titre de la session',
				'help'  => 'Laisser vide = généré automatiquement (cours — lieu · dates).',
				'ex'    => 'TDC FR — Tunis · 14–25 sept. 2026',
				'w'     => 38,
			),
			'date_debut'    => array(
				'label' => 'Date de début',
				'req'   => true,
				'help'  => 'JJ/MM/AAAA (ex. 14/09/2026).',
				'ex'    => '14/09/2026',
				'w'     => 14,
				'fmt'   => 'text',
			),
			'date_fin'      => array(
				'label' => 'Date de fin',
				'help'  => 'JJ/MM/AAAA. Vide = même jour que le début.',
				'ex'    => '25/09/2026',
				'w'     => 14,
				'fmt'   => 'text',
			),
			'lieu'          => array(
				'label' => 'Lieu',
				'help'  => 'Ville, Pays (ex. Tunis, Tunisie). Sert aussi au repérage sur la carte.',
				'ex'    => 'Tunis, Tunisie',
				'w'     => 24,
			),
			'langue_session' => array(
				'label' => 'Langue d’animation',
				'help'  => 'fr / en / ar.',
				'ex'    => 'fr',
				'list'  => 'langues_cours',
				'w'     => 16,
			),
			'hote'          => array(
				'label' => 'Institution hôte',
				'help'  => 'Organisation qui héberge la session.',
				'ex'    => 'AFSAC',
				'w'     => 22,
			),
			'places'        => array(
				'label' => 'Places disponibles',
				'help'  => 'Nombre entier. Optionnel.',
				'ex'    => '20',
				'w'     => 12,
			),
			'statut_session' => array(
				'label' => 'Statut',
				'help'  => 'ouvert (défaut) / complet / archive.',
				'ex'    => 'ouvert',
				'list'  => 'statuts_session',
				'w'     => 14,
			),
			'contact_nom'   => array(
				'label' => 'Contact — nom',
				'help'  => 'Optionnel.',
				'ex'    => '',
				'w'     => 20,
			),
			'contact_email' => array(
				'label' => 'Contact — e-mail',
				'help'  => 'Optionnel.',
				'ex'    => '',
				'w'     => 24,
			),
			'latitude'      => array(
				'label' => 'Latitude',
				'help'  => 'Optionnel — déduite de la ville si vide.',
				'ex'    => '',
				'w'     => 12,
			),
			'longitude'     => array(
				'label' => 'Longitude',
				'help'  => 'Optionnel — déduite de la ville si vide.',
				'ex'    => '',
				'w'     => 12,
			),
			'langue_fiche'  => array(
				'label' => 'Langue de la fiche (avancé)',
				'help'  => 'Laisser vide. À ne remplir que pour forcer la langue du contenu.',
				'ex'    => '',
				'list'  => 'langues_fiche',
				'w'     => 18,
			),
		);
	}

	return array(
		'cle'                  => array(
			'label' => 'Clé unique du cours',
			'req'   => true,
			'help'  => 'Identifiant stable, en minuscules sans espaces (ex. avsec-fret-poste). LA MÊME clé sur la ligne FR et la ligne EN du même cours : elles seront liées comme traductions. Ne plus la changer ensuite.',
			'ex'    => 'avsec-fret-poste',
			'w'     => 26,
			'fmt'   => 'text',
		),
		'langue_fiche'         => array(
			'label' => 'Langue de la fiche',
			'req'   => true,
			'help'  => 'fr / en / ar — langue dans laquelle cette ligne est rédigée.',
			'ex'    => 'fr',
			'list'  => 'langues_fiche',
			'w'     => 16,
		),
		'titre'                => array(
			'label' => 'Titre du cours',
			'req'   => true,
			'help'  => 'Intitulé affiché (H1 de la fiche).',
			'ex'    => 'Sûreté du Fret et de la Poste',
			'w'     => 42,
		),
		'famille'              => array(
			'label' => 'Famille',
			'req'   => true,
			'help'  => 'trainair ou avsec.',
			'ex'    => 'avsec',
			'list'  => 'familles',
			'w'     => 14,
		),
		'domaine'              => array(
			'label' => 'Domaine OACI',
			'req'   => true,
			'help'  => 'Un des 11 domaines — voir l’onglet « Listes ».',
			'ex'    => 'surete',
			'list'  => 'areas',
			'w'     => 18,
		),
		'abreviation'          => array(
			'label' => 'Abréviation',
			'help'  => 'Acronyme affiché à côté du titre.',
			'ex'    => 'TDC FR',
			'w'     => 14,
		),
		'code'                 => array(
			'label' => 'Référence / code',
			'help'  => 'Référence complète du cours.',
			'ex'    => '214/001/TDC FR',
			'w'     => 20,
			'fmt'   => 'text',
		),
		'type_cours'           => array(
			'label' => 'Type de cours',
			'help'  => 'oaci / stp / atelier / programme.',
			'ex'    => 'oaci',
			'list'  => 'types',
			'w'     => 14,
		),
		'modalite'             => array(
			'label' => 'Modalité',
			'help'  => 'presentiel / distanciel / hybride / intra / sur-mesure.',
			'ex'    => 'presentiel',
			'list'  => 'modalites',
			'w'     => 16,
		),
		'methode'              => array(
			'label' => 'Méthode',
			'help'  => 'instructeur (défaut) ou autorythme — pilote les onglets du catalogue.',
			'ex'    => 'instructeur',
			'list'  => 'methodes',
			'w'     => 16,
		),
		'niveau'               => array(
			'label' => 'Niveau',
			'help'  => 'initiation / fondamental / intermediaire / avance / technique / management.',
			'ex'    => 'technique',
			'list'  => 'niveaux',
			'w'     => 16,
		),
		'langues_dispensees'   => array(
			'label' => 'Langues de dispensation',
			'help'  => 'Langues dans lesquelles le cours est DONNÉ, séparées par « ; » (ex. fr;en;ar). Différent de la langue de la fiche.',
			'ex'    => 'fr;en;ar',
			'w'     => 22,
		),
		'duree'                => array(
			'label' => 'Durée',
			'help'  => 'Texte libre (ex. « 5 jours », « 10 jours / 57 heures »).',
			'ex'    => '5 jours',
			'w'     => 18,
		),
		'frais_montant'        => array(
			'label' => 'Frais (montant)',
			'help'  => 'Nombre seul, sans devise (ex. 1500).',
			'ex'    => '1500',
			'w'     => 14,
		),
		'devise'               => array(
			'label' => 'Devise',
			'help'  => 'USD / EUR / TND.',
			'ex'    => 'USD',
			'list'  => 'devises',
			'w'     => 10,
		),
		'tarif_reduit'         => array(
			'label' => 'Tarif réduit (États ACA)',
			'help'  => 'oui / non.',
			'ex'    => 'non',
			'list'  => 'ouinon',
			'w'     => 16,
		),
		'certificat'           => array(
			'label' => 'Certificat délivré',
			'help'  => 'oui / non.',
			'ex'    => 'oui',
			'list'  => 'ouinon',
			'w'     => 14,
		),
		'certificat_intitule'  => array(
			'label' => 'Intitulé du certificat',
			'help'  => 'Nom exact du certificat (si « oui » ci-dessus).',
			'ex'    => 'Certificat de l’OACI',
			'w'     => 24,
		),
		'public_resume'        => array(
			'label' => 'Public (résumé court)',
			'help'  => 'Version courte pour la colonne latérale (ex. « Cadres & experts »).',
			'ex'    => 'Agents de sûreté',
			'w'     => 24,
		),
		'presentation'         => array(
			'label' => 'Présentation / but',
			'help'  => 'Paragraphe d’introduction affiché en haut de la fiche.',
			'ex'    => 'Permettre au personnel concerné de comprendre…',
			'w'     => 50,
		),
		'objectifs'            => array(
			'label' => 'Objectifs',
			'help'  => 'UN objectif PAR LIGNE dans la cellule (Alt+Entrée), ou séparés par « | ».',
			'ex'    => 'Comprendre les mesures de sûreté|Appliquer les contrôles appropriés',
			'w'     => 50,
		),
		'structure_modules'    => array(
			'label' => 'Structure (modules)',
			'help'  => 'UN module PAR LIGNE (Alt+Entrée) ou séparés par « | ». La numérotation (Module 0, 1, 2…) est automatique.',
			'ex'    => 'Contexte de la sûreté du fret|Concepts|Procédures',
			'w'     => 50,
		),
		'public_cible'         => array(
			'label' => 'Public cible (détaillé)',
			'help'  => 'Paragraphe, ou une entrée par ligne pour obtenir une liste à puces.',
			'ex'    => 'Employés chargés de la réception et de la manutention du fret…',
			'w'     => 50,
		),
		'prerequis'            => array(
			'label' => 'Prérequis',
			'help'  => 'UN prérequis PAR LIGNE (Alt+Entrée) ou séparés par « | ».',
			'ex'    => 'Maîtriser la langue d’enseignement|Exercer une fonction opérationnelle',
			'w'     => 40,
		),
		'resultats'            => array(
			'label' => 'Résultats attendus',
			'help'  => 'UN résultat PAR LIGNE (Alt+Entrée) ou séparés par « | ».',
			'ex'    => '',
			'w'     => 40,
		),
		'autres_langues'       => array(
			'label' => 'Autres langues (texte)',
			'help'  => 'Mention libre affichée sur la fiche (ex. « Espagnol, Russe »).',
			'ex'    => '',
			'w'     => 20,
		),
		'developpe_par'        => array(
			'label' => 'Développé par',
			'help'  => 'Défaut : ICAO · OACI.',
			'ex'    => 'ICAO · OACI',
			'w'     => 20,
		),
		'developpe_par_detail' => array(
			'label' => 'Développé par (détail)',
			'help'  => 'Ligne secondaire (organisation, ville).',
			'ex'    => 'International Civil Aviation Organization · Montréal, Canada',
			'w'     => 34,
		),
		'image'                => array(
			'label' => 'Visuel',
			'help'  => 'Nom du fichier déjà présent dans la médiathèque (ex. avsec-fret.jpg) OU adresse https complète. Optionnel.',
			'ex'    => '',
			'w'     => 26,
		),
		'statut'               => array(
			'label' => 'Statut de publication',
			'help'  => 'publier (défaut) / brouillon.',
			'ex'    => 'publier',
			'list'  => 'statuts_post',
			'w'     => 16,
		),
	);
}

/**
 * Résout le référentiel désigné par la clé `list` d'une colonne.
 *
 * @param string $name Nom du référentiel.
 * @return array<string,mixed> Table code => libellé(s).
 */
function afsac_import_ref_table( $name ) {
	switch ( $name ) {
		case 'familles':
			return afsac_import_ref_familles();
		case 'areas':
			return afsac_import_ref_areas();
		case 'modalites':
			return afsac_import_ref_modalites();
		case 'types':
			return afsac_import_ref_types();
		case 'langues_cours':
			return afsac_import_ref_langues_cours();
		case 'langues_fiche':
			return afsac_import_ref_langues_fiche();
		case 'niveaux':
			return afsac_import_ref_niveaux();
		case 'methodes':
			return afsac_import_ref_methodes();
		case 'devises':
			return afsac_import_ref_devises();
		case 'statuts_session':
			return afsac_import_ref_statuts_session();
		case 'statuts_post':
			return array(
				'publier'   => 'Publier',
				'brouillon' => 'Brouillon',
			);
		case 'ouinon':
			return array(
				'oui' => 'Oui',
				'non' => 'Non',
			);
	}
	return array();
}

/* -------------------------------------------------------------------------
 * 3. NORMALISATION & CONVERSIONS
 * ---------------------------------------------------------------------- */

/**
 * Normalise une chaîne pour comparaison : minuscules, sans accents ni ponctuation
 * décorative, espaces compactés.
 *
 * @param string $value Valeur brute.
 * @return string Valeur normalisée.
 */
function afsac_import_norm( $value ) {
	$value = (string) $value;
	$value = str_replace( array( '’', '‘', '`' ), "'", $value );
	if ( function_exists( 'remove_accents' ) ) {
		$value = remove_accents( $value );
	}
	$value = strtolower( trim( $value ) );
	$value = preg_replace( '/[\s_]+/', ' ', $value );
	return trim( (string) $value );
}

/**
 * Retrouve le CODE d'un référentiel à partir d'une saisie libre (code, nom FR,
 * nom EN, nom AR ou slug).
 *
 * @param string $value Valeur saisie.
 * @param array  $table Référentiel.
 * @return string Code, ou '' si introuvable.
 */
function afsac_import_resolve_ref( $value, $table ) {
	$needle = afsac_import_norm( $value );
	if ( '' === $needle ) {
		return '';
	}

	foreach ( $table as $code => $names ) {
		if ( afsac_import_norm( $code ) === $needle ) {
			return (string) $code;
		}
		foreach ( (array) $names as $name ) {
			if ( afsac_import_norm( $name ) === $needle ) {
				return (string) $code;
			}
		}
		// Tolérance : slug du nom FR (ex. « surete-de-l-aviation »).
		foreach ( (array) $names as $name ) {
			if ( sanitize_title( $name ) === sanitize_title( $value ) ) {
				return (string) $code;
			}
		}
	}

	return '';
}

/**
 * Interprète une valeur booléenne saisie par le client.
 *
 * @param string $value Valeur.
 * @return bool
 */
function afsac_import_bool( $value ) {
	$value = afsac_import_norm( $value );
	return in_array( $value, array( 'oui', 'o', 'yes', 'y', '1', 'true', 'vrai', 'x', 'nam' ), true );
}

/**
 * Convertit une date saisie (JJ/MM/AAAA, AAAA-MM-JJ, AAAAMMJJ, série Excel) en Ymd.
 *
 * @param string $value Valeur.
 * @return string Date au format Ymd, ou '' si illisible.
 */
function afsac_import_date( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}

	// Série Excel (nombre de jours depuis le 30/12/1899).
	if ( preg_match( '/^\d{5}(\.\d+)?$/', $value ) ) {
		$serial = (int) $value;
		if ( $serial > 20000 && $serial < 80000 ) {
			return gmdate( 'Ymd', ( $serial - 25569 ) * 86400 );
		}
	}

	if ( preg_match( '/^(\d{4})(\d{2})(\d{2})$/', $value, $m ) ) {
		return $m[1] . $m[2] . $m[3];
	}
	if ( preg_match( '#^(\d{4})[-/.](\d{1,2})[-/.](\d{1,2})#', $value, $m ) ) {
		return sprintf( '%04d%02d%02d', $m[1], $m[2], $m[3] );
	}
	if ( preg_match( '#^(\d{1,2})[-/.](\d{1,2})[-/.](\d{4})#', $value, $m ) ) {
		return sprintf( '%04d%02d%02d', $m[3], $m[2], $m[1] );
	}

	$ts = strtotime( $value );
	return $ts ? gmdate( 'Ymd', $ts ) : '';
}

/**
 * Découpe une cellule multi-valeurs en lignes (retours à la ligne ou « | »).
 *
 * @param string $value Valeur.
 * @return string[] Lignes non vides.
 */
function afsac_import_lines( $value ) {
	$parts = preg_split( '/\r\n|\r|\n|\s*\|\s*/', (string) $value );
	$out   = array();
	foreach ( (array) $parts as $part ) {
		$part = trim( (string) $part );
		// Puces éventuellement collées par le client.
		$part = ltrim( $part, "-•*\t " );
		if ( '' !== $part ) {
			$out[] = $part;
		}
	}
	return $out;
}

/**
 * Valeur d'une colonne pour une ligne donnée.
 *
 * @param array  $row Ligne associative.
 * @param string $key Colonne.
 * @return string Valeur nettoyée.
 */
function afsac_import_val( $row, $key ) {
	return isset( $row[ $key ] ) ? trim( (string) $row[ $key ] ) : '';
}

/**
 * Le client demande-t-il d'EFFACER le champ (tiret seul) ?
 *
 * @param string $value Valeur.
 * @return bool
 */
function afsac_import_is_erase( $value ) {
	return in_array( trim( (string) $value ), array( '-', '—', '–' ), true );
}

/* -------------------------------------------------------------------------
 * 4. LECTURE DES FICHIERS (CSV / XLSX)
 * ---------------------------------------------------------------------- */

/**
 * Lit un fichier tableur et retourne ses onglets sous forme de blocs.
 *
 * Un CSV = un seul bloc anonyme. Un .xlsx = un bloc par onglet, dans l'ordre du
 * classeur : c'est ce qui permet au client de découper sa saisie en un onglet
 * par domaine OACI (voir afsac_import_collect_rows()).
 *
 * @param string $path Chemin absolu du fichier.
 * @return array<int,array{sheet:string,rows:array[]}>|WP_Error
 */
function afsac_import_read_blocks( $path ) {
	if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
		return new WP_Error( 'afsac_import_file', __( 'Fichier introuvable ou illisible.', 'afsac' ) );
	}

	$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

	if ( 'xlsx' === $ext ) {
		return afsac_import_read_xlsx_blocks( $path );
	}

	$rows = afsac_import_read_csv( $path );
	if ( is_wp_error( $rows ) ) {
		return $rows;
	}

	return array(
		array(
			'sheet' => '',
			'rows'  => $rows,
		),
	);
}

/**
 * Lit un CSV : BOM retiré, séparateur détecté (; , tabulation), encodage réparé
 * si le fichier a été enregistré en Windows-1252 par Excel.
 *
 * @param string $path Chemin.
 * @return array[]|WP_Error
 */
function afsac_import_read_csv( $path ) {
	$raw = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( false === $raw ) {
		return new WP_Error( 'afsac_import_file', __( 'Lecture du CSV impossible.', 'afsac' ) );
	}

	// BOM UTF-8.
	$raw = preg_replace( '/^\xEF\xBB\xBF/', '', $raw );

	// Excel « CSV (séparateur point-virgule) » enregistre en ANSI : on répare.
	if ( ! mb_check_encoding( $raw, 'UTF-8' ) ) {
		$raw = mb_convert_encoding( $raw, 'UTF-8', 'Windows-1252' );
	}

	// Détection du séparateur sur la première ligne non vide.
	$first = '';
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		if ( '' !== trim( $line ) ) {
			$first = $line;
			break;
		}
	}
	$counts = array(
		';'  => substr_count( $first, ';' ),
		','  => substr_count( $first, ',' ),
		"\t" => substr_count( $first, "\t" ),
	);
	arsort( $counts );
	$sep = (string) key( $counts );
	if ( 0 === (int) current( $counts ) ) {
		$sep = ';';
	}

	$tmp = fopen( 'php://temp', 'r+' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	fwrite( $tmp, $raw ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
	rewind( $tmp );

	$rows = array();
	while ( false !== ( $cells = fgetcsv( $tmp, 0, $sep ) ) ) {
		if ( null === $cells ) {
			continue;
		}
		$rows[] = array_map(
			static function ( $cell ) {
				return null === $cell ? '' : trim( (string) $cell );
			},
			$cells
		);
	}
	fclose( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

	return $rows;
}

/**
 * Lit TOUS les onglets d'un .xlsx (zip + XML) sans dépendance externe.
 *
 * @param string $path Chemin.
 * @return array<int,array{sheet:string,rows:array[]}>|WP_Error
 */
function afsac_import_read_xlsx_blocks( $path ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return new WP_Error(
			'afsac_import_zip',
			__( 'L’extension PHP « zip » est absente : enregistrez le classeur en CSV (UTF-8) et réessayez.', 'afsac' )
		);
	}

	$zip = new ZipArchive();
	if ( true !== $zip->open( $path ) ) {
		return new WP_Error( 'afsac_import_zip', __( 'Fichier .xlsx illisible (archive invalide).', 'afsac' ) );
	}

	// 1) Chaînes partagées (Excel et Google Sheets les utilisent tous les deux).
	$shared = array();
	$ss_xml = $zip->getFromName( 'xl/sharedStrings.xml' );
	if ( $ss_xml ) {
		$ss = simplexml_load_string( $ss_xml );
		if ( $ss ) {
			foreach ( $ss->si as $si ) {
				$shared[] = afsac_import_xlsx_text( $si );
			}
		}
	}

	// 2) Liste ordonnée des onglets (nom + partie XML).
	$targets = array();
	$wb_xml  = $zip->getFromName( 'xl/workbook.xml' );
	$rl_xml  = $zip->getFromName( 'xl/_rels/workbook.xml.rels' );

	if ( $wb_xml && $rl_xml ) {
		$rels = array();
		$rx   = simplexml_load_string( $rl_xml );
		if ( $rx ) {
			foreach ( $rx->Relationship as $rel ) {
				$rels[ (string) $rel['Id'] ] = (string) $rel['Target'];
			}
		}
		$wb = simplexml_load_string( $wb_xml );
		if ( $wb ) {
			foreach ( $wb->sheets->sheet as $sheet ) {
				$rid   = '';
				$attrs = $sheet->attributes( 'http://schemas.openxmlformats.org/officeDocument/2006/relationships' );
				if ( isset( $attrs['id'] ) ) {
					$rid = (string) $attrs['id'];
				}
				if ( ! isset( $rels[ $rid ] ) ) {
					continue;
				}
				$part      = ltrim( $rels[ $rid ], '/' );
				$targets[] = array(
					'name' => (string) $sheet['name'],
					'part' => ( 0 === strpos( $part, 'xl/' ) ) ? $part : 'xl/' . $part,
				);
			}
		}
	}

	if ( ! $targets ) {
		$targets[] = array(
			'name' => '',
			'part' => 'xl/worksheets/sheet1.xml',
		);
	}

	$blocks = array();
	foreach ( $targets as $target ) {
		$sheet_xml = $zip->getFromName( $target['part'] );
		if ( ! $sheet_xml ) {
			continue;
		}
		$sx = simplexml_load_string( $sheet_xml );
		if ( ! $sx ) {
			continue;
		}
		$blocks[] = array(
			'sheet' => $target['name'],
			'rows'  => afsac_import_xlsx_rows( $sx, $shared ),
		);
	}

	$zip->close();

	if ( ! $blocks ) {
		return new WP_Error( 'afsac_import_zip', __( 'Aucun onglet lisible dans le classeur.', 'afsac' ) );
	}

	return $blocks;
}

/**
 * Convertit le XML d'un onglet en tableau de lignes.
 *
 * @param SimpleXMLElement $sx     Nœud <worksheet>.
 * @param string[]         $shared Chaînes partagées.
 * @return array[]
 */
function afsac_import_xlsx_rows( $sx, $shared ) {
	$rows = array();

	foreach ( $sx->sheetData->row as $row ) {
		$cells = array();
		$index = 0;
		foreach ( $row->c as $c ) {
			$ref = (string) $c['r'];
			$col = '' !== $ref ? afsac_import_xlsx_col_index( $ref ) : $index;
			$typ = (string) $c['t'];

			if ( 'inlineStr' === $typ ) {
				$val = afsac_import_xlsx_text( $c->is );
			} elseif ( 's' === $typ ) {
				$i   = (int) $c->v;
				$val = isset( $shared[ $i ] ) ? $shared[ $i ] : '';
			} else {
				$val = (string) $c->v;
			}

			$cells[ $col ] = trim( $val );
			$index         = $col + 1;
		}
		if ( empty( $cells ) ) {
			$rows[] = array();
			continue;
		}
		// Ré-indexation dense (les cellules vides sont omises par Excel).
		$max  = max( array_keys( $cells ) );
		$line = array();
		for ( $i = 0; $i <= $max; $i++ ) {
			$line[] = isset( $cells[ $i ] ) ? $cells[ $i ] : '';
		}
		$rows[] = $line;
	}

	return $rows;
}

/**
 * Texte d'un nœud xlsx (<si> ou <is>), runs compris.
 *
 * @param SimpleXMLElement $node Nœud.
 * @return string
 */
function afsac_import_xlsx_text( $node ) {
	if ( ! $node ) {
		return '';
	}
	$text = '';
	if ( isset( $node->t ) ) {
		$text .= (string) $node->t;
	}
	if ( isset( $node->r ) ) {
		foreach ( $node->r as $run ) {
			$text .= (string) $run->t;
		}
	}
	return $text;
}

/**
 * Index de colonne (0-based) à partir d'une référence de cellule (« BC12 » → 54).
 *
 * @param string $ref Référence.
 * @return int
 */
function afsac_import_xlsx_col_index( $ref ) {
	$letters = strtoupper( preg_replace( '/\d/', '', $ref ) );
	$num     = 0;
	$len     = strlen( $letters );
	for ( $i = 0; $i < $len; $i++ ) {
		$num = $num * 26 + ( ord( $letters[ $i ] ) - 64 );
	}
	return max( 0, $num - 1 );
}

/**
 * Repère la ligne d'en-tête et convertit les lignes en tableaux associatifs.
 *
 * Tolérant : l'en-tête peut porter les clés techniques OU les libellés FR ;
 * les colonnes peuvent être réordonnées ou absentes ; les lignes dont la 1re
 * cellule commence par « # » (aide, exemples) sont ignorées.
 *
 * @param array[] $rows  Lignes brutes.
 * @param array   $spec  Spécification des colonnes.
 * @return array{header:array<int,string>,rows:array<int,array{line:int,data:array}>,missing:string[]}
 */
function afsac_import_map_rows( $rows, $spec ) {
	$lookup = array();
	foreach ( $spec as $key => $col ) {
		$lookup[ afsac_import_norm( $key ) ]           = $key;
		$lookup[ afsac_import_norm( $col['label'] ) ]  = $key;
	}

	$header     = array();
	$header_idx = -1;
	$limit      = min( 12, count( $rows ) );

	for ( $i = 0; $i < $limit; $i++ ) {
		$map   = array();
		$score = 0;
		foreach ( (array) $rows[ $i ] as $pos => $cell ) {
			$norm = afsac_import_norm( ltrim( (string) $cell, '#* ' ) );
			if ( isset( $lookup[ $norm ] ) ) {
				$map[ $pos ] = $lookup[ $norm ];
				++$score;
			}
		}
		if ( $score >= 2 && $score > count( $header ) ) {
			$header     = $map;
			$header_idx = $i;
		}
	}

	$out = array(
		'header'  => $header,
		'rows'    => array(),
		'missing' => array(),
	);

	if ( $header_idx < 0 ) {
		return $out;
	}

	foreach ( $spec as $key => $col ) {
		if ( ! empty( $col['req'] ) && ! in_array( $key, $header, true ) ) {
			$out['missing'][] = $key;
		}
	}

	$total = count( $rows );
	for ( $i = $header_idx + 1; $i < $total; $i++ ) {
		$cells = (array) $rows[ $i ];
		if ( ! $cells ) {
			continue;
		}

		// Ligne d'aide / d'exemple commentée.
		$first = isset( $cells[0] ) ? trim( (string) $cells[0] ) : '';
		if ( '' !== $first && '#' === $first[0] ) {
			continue;
		}

		// Ligne entièrement vide.
		$empty = true;
		foreach ( $cells as $cell ) {
			if ( '' !== trim( (string) $cell ) ) {
				$empty = false;
				break;
			}
		}
		if ( $empty ) {
			continue;
		}

		$data = array();
		foreach ( $header as $pos => $key ) {
			$data[ $key ] = isset( $cells[ $pos ] ) ? trim( (string) $cells[ $pos ] ) : '';
		}

		$out['rows'][] = array(
			'line' => $i + 1, // Numéro affiché dans le tableur (1-based).
			'data' => $data,
		);
	}

	return $out;
}

/**
 * Retrouve un code de référentiel à partir d'un libellé approximatif.
 *
 * Plus tolérant que afsac_import_resolve_ref() : accepte qu'un NOM D'ONGLET ne
 * soit qu'un fragment du nom officiel (« FLIGHT SAFETY » → « Flight Safety &
 * Safety Management »). Réservé aux noms d'onglets, jamais aux cellules.
 *
 * @param string $value Libellé (nom d'onglet).
 * @param array  $table Référentiel.
 * @return string Code, ou '' si aucune correspondance sûre.
 */
function afsac_import_match_ref_loose( $value, $table ) {
	$exact = afsac_import_resolve_ref( $value, $table );
	if ( '' !== $exact ) {
		return $exact;
	}

	$needle = afsac_import_norm( $value );
	if ( strlen( $needle ) < 4 ) {
		return '';
	}

	foreach ( $table as $code => $names ) {
		foreach ( (array) $names as $name ) {
			$hay = afsac_import_norm( $name );
			if ( '' === $hay ) {
				continue;
			}
			/*
			 * a) le nom d'onglet est un PRÉFIXE du nom officiel
			 *    (« FLIGHT SAFETY » → « Flight Safety & Safety Management ») ;
			 * b) le nom officiel est CONTENU dans le nom d'onglet, préfixe ou
			 *    suffixe compris (« ICAO_Environment » → « Environment »). Seuil de
			 *    8 caractères : les noms de domaines les plus courts en font 10, un
			 *    fragment plus court ne serait pas discriminant.
			 */
			if ( 0 === strpos( $hay, $needle ) ) {
				return (string) $code;
			}
			if ( strlen( $hay ) >= 8 && false !== strpos( $needle, $hay ) ) {
				return (string) $code;
			}
		}
	}

	return '';
}

/**
 * Valeurs par défaut déduites du NOM d'un onglet.
 *
 * Le client peut découper sa saisie en un onglet par domaine OACI (« AVIATION
 * SECURITY », « Aviation LAW », « FLIGHT SAFETY »…) ou par programme
 * (« AVSEC », « TRAINAIR PLUS ») : les lignes de cet onglet héritent alors du
 * domaine / de la famille correspondants, SAUF si la cellule est renseignée.
 *
 * @param string $sheet_name Nom de l'onglet.
 * @return array<string,string> Valeurs par défaut (colonne => valeur).
 */
function afsac_import_sheet_defaults( $sheet_name ) {
	$defaults = array();
	$norm     = afsac_import_norm( $sheet_name );

	if ( '' === $norm ) {
		return $defaults;
	}

	// Onglets structurels du modèle : aucun héritage.
	$reserved = array( 'formations', 'formation', 'sessions', 'session', 'listes', 'liste', "mode d'emploi", 'feuille1', 'sheet1', 'feuil1' );
	if ( in_array( $norm, $reserved, true ) ) {
		return $defaults;
	}

	$area = afsac_import_match_ref_loose( $sheet_name, afsac_import_ref_areas() );
	if ( '' !== $area ) {
		$defaults['domaine'] = $area;
	}

	$famille = afsac_import_match_ref_loose( $sheet_name, afsac_import_ref_familles() );
	if ( '' !== $famille ) {
		$defaults['famille'] = $famille;
	}

	return $defaults;
}

/**
 * Rassemble les lignes de TOUS les onglets exploitables d'un fichier.
 *
 * Chaque onglet est confronté aux deux spécifications (formations / sessions) :
 * on ne garde que ceux qui correspondent au type demandé, ce qui laisse de côté
 * « Mode d'emploi », « Listes » et l'onglet de l'autre type. Les onglets nommés
 * d'après un domaine transmettent leur domaine aux lignes qui n'en portent pas.
 *
 * @param string $path Chemin du fichier.
 * @param string $type « formations » ou « sessions ».
 * @return array{entries:array,sheets:array,missing:array}|WP_Error
 */
function afsac_import_collect_rows( $path, $type ) {
	$blocks = afsac_import_read_blocks( $path );
	if ( is_wp_error( $blocks ) ) {
		return $blocks;
	}

	$spec  = afsac_import_spec( $type );
	$other = afsac_import_spec( 'sessions' === $type ? 'formations' : 'sessions' );

	$result = array(
		'entries' => array(),
		'sheets'  => array(),
		'missing' => array(),
	);

	foreach ( $blocks as $block ) {
		$mapped = afsac_import_map_rows( $block['rows'], $spec );
		if ( ! $mapped['header'] ) {
			continue;
		}

		// L'onglet ressemble-t-il davantage à l'AUTRE type ? (Sessions vs Formations.)
		$rival = afsac_import_map_rows( $block['rows'], $other );
		if ( count( $rival['header'] ) > count( $mapped['header'] ) ) {
			continue;
		}
		if ( count( $mapped['header'] ) < 3 ) {
			continue;
		}

		if ( $mapped['missing'] ) {
			$result['missing'][ $block['sheet'] ] = $mapped['missing'];
			continue;
		}

		$defaults = afsac_import_sheet_defaults( $block['sheet'] );

		foreach ( $mapped['rows'] as $row ) {
			$result['entries'][] = array(
				'sheet'    => $block['sheet'],
				'line'     => $row['line'],
				'data'     => $row['data'],
				'defaults' => $defaults,
			);
		}

		$result['sheets'][] = array(
			'name'     => '' !== $block['sheet'] ? $block['sheet'] : __( '(fichier CSV)', 'afsac' ),
			'rows'     => count( $mapped['rows'] ),
			'defaults' => $defaults,
		);
	}

	return $result;
}

/* -------------------------------------------------------------------------
 * 5. ÉCRITURE — termes, formations, sessions
 * ---------------------------------------------------------------------- */

/**
 * Retourne (en créant au besoin) l'ID du terme correspondant à un code de
 * référentiel, DANS la langue demandée, et relie les traductions entre elles.
 *
 * @param string $code     Code du référentiel.
 * @param string $taxonomy Taxonomie.
 * @param string $lang     Langue Polylang cible.
 * @param string $ref      Nom du référentiel (afsac_import_ref_table()).
 * @param array  $stats    Compteur created/reused passé par référence.
 * @return int ID du terme, 0 si échec.
 */
function afsac_import_term( $code, $taxonomy, $lang, $ref, &$stats ) {
	static $cache = array();

	if ( '' === $code || '' === $lang ) {
		return 0;
	}

	$table = afsac_import_ref_table( $ref );
	if ( ! isset( $table[ $code ] ) ) {
		return 0;
	}
	$names = (array) $table[ $code ];

	$ck = $taxonomy . '|' . $code;
	if ( isset( $cache[ $ck ][ $lang ] ) ) {
		return (int) $cache[ $ck ][ $lang ];
	}

	$site_langs = function_exists( 'pll_languages_list' ) ? (array) pll_languages_list() : array( $lang );

	// Langues à créer : celles pour lesquelles on a un nom + celle demandée.
	$targets = array_values( array_intersect( array_keys( $names ), $site_langs ) );
	if ( ! in_array( $lang, $targets, true ) && in_array( $lang, $site_langs, true ) ) {
		$targets[] = $lang;
	}

	$ids = isset( $cache[ $ck ] ) ? $cache[ $ck ] : array();
	foreach ( $targets as $l ) {
		if ( isset( $ids[ $l ] ) ) {
			continue;
		}
		$name = isset( $names[ $l ] ) ? $names[ $l ] : ( isset( $names['en'] ) ? $names['en'] : reset( $names ) );
		$id   = afsac_seed_term( $name, $taxonomy, $l, $stats );
		if ( $id ) {
			$ids[ $l ] = (int) $id;
			if ( 'afsac_type' === $taxonomy ) {
				update_term_meta( $id, 'afsac_type_key', $code );
			}
		}
	}

	if ( count( $ids ) > 1 && function_exists( 'pll_save_term_translations' ) ) {
		pll_save_term_translations( $ids );
	}

	$cache[ $ck ] = $ids;

	return isset( $ids[ $lang ] ) ? (int) $ids[ $lang ] : 0;
}

/**
 * Retrouve un post d'import par sa clé (et éventuellement sa langue).
 *
 * @param string $key       Clé stable.
 * @param string $post_type CPT.
 * @param string $meta_key  Méta d'idempotence.
 * @param string $lang      Langue Polylang, ou '' pour toutes.
 * @return int[] IDs trouvés.
 */
function afsac_import_find_by_key( $key, $post_type, $meta_key, $lang = '' ) {
	$args = array(
		'post_type'        => $post_type,
		'post_status'      => 'any',
		'posts_per_page'   => 20,
		'fields'           => 'ids',
		'no_found_rows'    => true,
		'suppress_filters' => false,
		'lang'             => $lang, // '' = toutes langues (Polylang).
		'meta_key'         => $meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value'       => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	);

	return array_map( 'intval', (array) get_posts( $args ) );
}

/**
 * Relie entre elles les traductions d'une même clé (Polylang).
 *
 * @param string $key Clé stable.
 * @return int Nombre de fiches appariées.
 */
function afsac_import_link_translations( $key ) {
	if ( ! function_exists( 'pll_save_post_translations' ) || ! function_exists( 'pll_get_post_language' ) ) {
		return 0;
	}

	$ids = afsac_import_find_by_key( $key, 'afsac_formation', '_afsac_import_key', '' );
	if ( count( $ids ) < 2 ) {
		return 0;
	}

	$map = array();
	foreach ( $ids as $id ) {
		$l = pll_get_post_language( $id );
		if ( $l && ! isset( $map[ $l ] ) ) {
			$map[ $l ] = $id;
		}
	}

	if ( count( $map ) < 2 ) {
		return 0;
	}

	pll_save_post_translations( $map );

	return count( $map );
}

/**
 * Retrouve une pièce jointe par nom de fichier.
 *
 * @param string $filename Nom du fichier (avec extension).
 * @return int ID de la pièce jointe, 0 sinon.
 */
function afsac_import_find_attachment( $filename ) {
	global $wpdb;

	$filename = basename( trim( (string) $filename ) );
	if ( '' === $filename ) {
		return 0;
	}

	$id = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s LIMIT 1",
			'%' . $wpdb->esc_like( $filename )
		)
	);

	return $id;
}

/**
 * Affecte le visuel d'une fiche depuis un nom de fichier ou une URL.
 *
 * @param int    $post_id ID du post.
 * @param string $value   Nom de fichier ou URL.
 * @return string '' si OK, message d'avertissement sinon.
 */
function afsac_import_set_image( $post_id, $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}

	$is_url    = (bool) preg_match( '#^https?://#i', $value );
	$filename  = $is_url ? basename( (string) wp_parse_url( $value, PHP_URL_PATH ) ) : $value;
	$attach_id = afsac_import_find_attachment( $filename );

	if ( ! $attach_id && $is_url ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attach_id = media_sideload_image( $value, $post_id, null, 'id' );
		if ( is_wp_error( $attach_id ) ) {
			return sprintf(
				/* translators: %s: message d'erreur du téléchargement. */
				__( 'visuel non téléchargé (%s)', 'afsac' ),
				$attach_id->get_error_message()
			);
		}
	}

	if ( ! $attach_id ) {
		return __( 'visuel introuvable dans la médiathèque', 'afsac' );
	}

	set_post_thumbnail( $post_id, (int) $attach_id );

	return '';
}

/**
 * Clé stable d'une ligne de formation : celle saisie, sinon dérivée du titre.
 *
 * Dérivation : slug du titre, tronqué à 80 caractères. Au-delà, la troncature
 * seule ferait COLLIDER deux cours dont les 80 premiers caractères sont
 * identiques (le catalogue OACI en compte) — la 2e ligne écraserait alors la 1re.
 * On suffixe donc par une empreinte du titre complet : déterministe (même titre =
 * même clé, l'idempotence est préservée) et distincte pour deux titres différents.
 *
 * @param array $row Ligne associative.
 * @return string Clé, ou '' si le titre est vide.
 */
function afsac_import_row_key( $row ) {
	$given = afsac_import_val( $row, 'cle' );
	if ( '' !== $given ) {
		return sanitize_title( $given );
	}

	$titre = afsac_import_val( $row, 'titre' );
	if ( '' === $titre ) {
		return '';
	}

	$slug = sanitize_title( $titre );
	if ( strlen( $slug ) <= 80 ) {
		return $slug;
	}

	return substr( $slug, 0, 72 ) . '-' . substr( md5( $titre ), 0, 6 );
}

/**
 * Importe UNE ligne de l'onglet « Formations ».
 *
 * @param array $row   Ligne associative.
 * @param array $opts  Options ( dry => bool ).
 * @param array $stats Compteurs passés par référence.
 * @return array{ok:bool,messages:string[],id:int}
 */
function afsac_import_row_formation( $row, $opts, &$stats ) {
	$res = array(
		'ok'       => false,
		'messages' => array(),
		'id'       => 0,
	);

	$titre = afsac_import_val( $row, 'titre' );
	$cle   = afsac_import_val( $row, 'cle' );
	$lang  = afsac_import_resolve_ref( afsac_import_val( $row, 'langue_fiche' ), afsac_import_ref_langues_fiche() );

	if ( '' === $titre ) {
		$res['messages'][] = __( 'titre manquant → ligne ignorée', 'afsac' );
		return $res;
	}

	if ( '' === $cle ) {
		$cle               = afsac_import_row_key( $row );
		$res['messages'][] = sprintf(
			/* translators: %s: clé générée. */
			__( 'clé absente → générée depuis le titre (%s)', 'afsac' ),
			$cle
		);
	} else {
		$cle = sanitize_title( $cle );
	}

	$site_langs = function_exists( 'pll_languages_list' ) ? (array) pll_languages_list() : array();
	$lang_raw   = afsac_import_val( $row, 'langue_fiche' );

	/*
	 * Une langue SAISIE mais non reconnue (« es », « pt »…) ne doit PAS retomber
	 * silencieusement sur le français : la fiche serait créée dans la mauvaise
	 * langue. On refuse la ligne et on nomme la valeur fautive.
	 */
	if ( '' !== $lang_raw && '' === $lang ) {
		$res['messages'][] = sprintf(
			/* translators: %s: valeur saisie dans langue_fiche. */
			__( 'langue « %s » inconnue (attendu fr / en / ar) → ligne ignorée', 'afsac' ),
			$lang_raw
		);
		return $res;
	}

	if ( '' === $lang ) {
		$lang              = function_exists( 'pll_default_language' ) ? (string) pll_default_language() : 'fr';
		$res['messages'][] = sprintf(
			/* translators: %s: code langue retenu. */
			__( 'langue_fiche vide → « %s » par défaut', 'afsac' ),
			$lang
		);
	}
	if ( $site_langs && ! in_array( $lang, $site_langs, true ) ) {
		$res['messages'][] = sprintf(
			/* translators: %s: code langue. */
			__( 'langue « %s » non configurée dans Polylang → ligne ignorée', 'afsac' ),
			$lang
		);
		return $res;
	}

	// Contrôles des listes fermées AVANT écriture (rapport de simulation utile).
	$famille  = afsac_import_resolve_ref( afsac_import_val( $row, 'famille' ), afsac_import_ref_familles() );
	$domaine  = afsac_import_resolve_ref( afsac_import_val( $row, 'domaine' ), afsac_import_ref_areas() );
	$modalite = afsac_import_resolve_ref( afsac_import_val( $row, 'modalite' ), afsac_import_ref_modalites() );
	$type     = afsac_import_resolve_ref( afsac_import_val( $row, 'type_cours' ), afsac_import_ref_types() );
	$niveau   = afsac_import_resolve_ref( afsac_import_val( $row, 'niveau' ), afsac_import_ref_niveaux() );
	$methode  = afsac_import_resolve_ref( afsac_import_val( $row, 'methode' ), afsac_import_ref_methodes() );
	$devise   = strtoupper( afsac_import_val( $row, 'devise' ) );

	foreach ( array(
		'famille'    => array( afsac_import_val( $row, 'famille' ), $famille ),
		'domaine'    => array( afsac_import_val( $row, 'domaine' ), $domaine ),
		'modalite'   => array( afsac_import_val( $row, 'modalite' ), $modalite ),
		'type_cours' => array( afsac_import_val( $row, 'type_cours' ), $type ),
		'niveau'     => array( afsac_import_val( $row, 'niveau' ), $niveau ),
		'methode'    => array( afsac_import_val( $row, 'methode' ), $methode ),
	) as $col => $pair ) {
		if ( '' !== $pair[0] && '' === $pair[1] ) {
			$res['messages'][] = sprintf(
				/* translators: 1: nom de colonne, 2: valeur non reconnue. */
				__( '%1$s : valeur « %2$s » non reconnue → ignorée', 'afsac' ),
				$col,
				$pair[0]
			);
		}
	}

	/*
	 * Sans domaine ni famille, la fiche existe mais n'apparaît dans aucune liste
	 * du catalogue : on le signale, sans bloquer (cf. guide-admin, pièges).
	 */
	if ( '' === $domaine ) {
		$res['messages'][] = __( 'domaine vide → le cours n’apparaîtra dans aucun domaine du catalogue', 'afsac' );
	}
	if ( '' === $famille ) {
		$res['messages'][] = __( 'famille vide → le cours ne sera rattaché ni à AVSEC ni à TRAINAIR PLUS', 'afsac' );
	}

	if ( '' !== $devise && ! isset( afsac_import_ref_devises()[ $devise ] ) ) {
		$res['messages'][] = sprintf(
			/* translators: %s: devise saisie. */
			__( 'devise « %s » non reconnue → ignorée', 'afsac' ),
			$devise
		);
		$devise = '';
	}

	$existing = afsac_import_find_by_key( $cle, 'afsac_formation', '_afsac_import_key', $lang );
	$post_id  = $existing ? (int) $existing[0] : 0;

	if ( ! empty( $opts['dry'] ) ) {
		$res['ok'] = true;
		$res['id'] = $post_id;
		if ( $post_id ) {
			$stats['updated']++;
		} else {
			$stats['created']++;
		}
		return $res;
	}

	// --- Écriture du post ------------------------------------------------
	$postarr = array(
		'post_type'  => 'afsac_formation',
		'post_title' => $titre,
	);

	$statut = afsac_import_val( $row, 'statut' );
	if ( '' !== $statut ) {
		$map = afsac_import_ref_statuts_post();
		$k   = afsac_import_norm( $statut );
		if ( isset( $map[ $k ] ) ) {
			$postarr['post_status'] = $map[ $k ];
		}
	}

	$presentation = afsac_import_val( $row, 'presentation' );
	if ( '' !== $presentation && ! afsac_import_is_erase( $presentation ) ) {
		$blocks = '';
		foreach ( afsac_import_lines( $presentation ) as $para ) {
			$blocks .= '<!-- wp:paragraph --><p>' . esc_html( $para ) . '</p><!-- /wp:paragraph -->';
		}
		$postarr['post_content'] = $blocks;
		$postarr['post_excerpt'] = wp_trim_words( $presentation, 32, '…' );
	}

	if ( $post_id ) {
		$postarr['ID'] = $post_id;
		$result        = wp_update_post( $postarr, true );
		$stats['updated']++;
	} else {
		if ( ! isset( $postarr['post_status'] ) ) {
			$postarr['post_status'] = 'publish';
		}
		$result = wp_insert_post( $postarr, true );
		$stats['created']++;
	}

	if ( is_wp_error( $result ) || ! $result ) {
		$res['messages'][] = is_wp_error( $result ) ? $result->get_error_message() : __( 'enregistrement impossible', 'afsac' );
		return $res;
	}

	$post_id   = (int) $result;
	$res['id'] = $post_id;

	// Langue Polylang AVANT les termes (cohérence par langue).
	if ( function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $post_id, $lang );
	}

	update_post_meta( $post_id, '_afsac_import', 1 );
	update_post_meta( $post_id, '_afsac_import_key', $cle );
	update_post_meta( $post_id, '_afsac_import_src', 'tableur' );

	// --- Taxonomies ------------------------------------------------------
	$terms = &$stats['terms'];

	if ( '' !== $famille ) {
		$tid = afsac_import_term( $famille, 'afsac_famille', $lang, 'familles', $terms );
		if ( $tid ) {
			wp_set_object_terms( $post_id, array( $tid ), 'afsac_famille', false );
		}
	}
	if ( '' !== $domaine ) {
		$tid = afsac_import_term( $domaine, 'afsac_area', $lang, 'areas', $terms );
		if ( $tid ) {
			wp_set_object_terms( $post_id, array( $tid ), 'afsac_area', false );
		}
	}
	if ( '' !== $modalite ) {
		$tid = afsac_import_term( $modalite, 'afsac_modalite', $lang, 'modalites', $terms );
		if ( $tid ) {
			wp_set_object_terms( $post_id, array( $tid ), 'afsac_modalite', false );
		}
	}
	if ( '' !== $type ) {
		$tid = afsac_import_term( $type, 'afsac_type', $lang, 'types', $terms );
		if ( $tid ) {
			wp_set_object_terms( $post_id, array( $tid ), 'afsac_type', false );
		}
	}

	$langues = afsac_import_val( $row, 'langues_dispensees' );
	if ( '' !== $langues && ! afsac_import_is_erase( $langues ) ) {
		$ids = array();
		foreach ( preg_split( '/[;,\/]+/', $langues ) as $one ) {
			$code = afsac_import_resolve_ref( $one, afsac_import_ref_langues_cours() );
			if ( '' === $code ) {
				continue;
			}
			$tid = afsac_import_term( $code, 'afsac_langue', $lang, 'langues_cours', $terms );
			if ( $tid ) {
				$ids[] = $tid;
			}
		}
		if ( $ids ) {
			wp_set_object_terms( $post_id, $ids, 'afsac_langue', false );
		}
	} elseif ( afsac_import_is_erase( $langues ) ) {
		wp_set_object_terms( $post_id, array(), 'afsac_langue', false );
	}

	// --- Champs ACF ------------------------------------------------------
	$set = static function ( $col, $field, $transform = null ) use ( $row, $post_id ) {
		$raw = afsac_import_val( $row, $col );
		if ( '' === $raw ) {
			return; // Vide = on ne touche pas.
		}
		if ( afsac_import_is_erase( $raw ) ) {
			afsac_import_set_field( $post_id, $field, '' );
			return;
		}
		afsac_import_set_field( $post_id, $field, $transform ? $transform( $raw ) : $raw );
	};

	$to_list = static function ( $value ) {
		$lines = afsac_import_lines( $value );
		if ( count( $lines ) < 2 ) {
			return '<p>' . esc_html( trim( (string) $value ) ) . '</p>';
		}
		return afsac_import_html_list( $lines );
	};
	$to_text_lines = static function ( $value ) {
		return implode( "\n", afsac_import_lines( $value ) );
	};

	$set( 'abreviation', 'afsac_abbreviation' );
	$set( 'code', 'afsac_code' );
	$set( 'duree', 'afsac_duree' );
	$set( 'public_resume', 'afsac_public_resume' );
	$set( 'autres_langues', 'afsac_autres_langues' );
	$set( 'developpe_par', 'afsac_developpe_par' );
	$set( 'developpe_par_detail', 'afsac_developpe_par_detail' );
	$set( 'certificat_intitule', 'afsac_certificat_intitule' );
	$set( 'objectifs', 'afsac_objectifs', $to_list );
	$set( 'resultats', 'afsac_resultats', $to_list );
	$set( 'public_cible', 'afsac_public_cible', $to_list );
	$set( 'structure_modules', 'afsac_structure', $to_text_lines );
	$set( 'prerequis', 'afsac_prerequis', $to_text_lines );
	$set( 'frais_montant', 'afsac_frais_montant', static function ( $v ) {
		return (int) preg_replace( '/[^\d]/', '', $v );
	} );

	if ( '' !== $niveau ) {
		afsac_import_set_field( $post_id, 'afsac_niveau', $niveau );
	}
	if ( '' !== $methode ) {
		afsac_import_set_field( $post_id, 'afsac_methode', $methode );
	}
	if ( '' !== $devise ) {
		afsac_import_set_field( $post_id, 'afsac_devise', $devise );
	}

	$certificat = afsac_import_val( $row, 'certificat' );
	if ( '' !== $certificat ) {
		afsac_import_set_field( $post_id, 'afsac_certificat', afsac_import_bool( $certificat ) ? 1 : 0 );
	}
	$tarif = afsac_import_val( $row, 'tarif_reduit' );
	if ( '' !== $tarif ) {
		afsac_import_set_field( $post_id, 'afsac_tarif_reduit', afsac_import_bool( $tarif ) ? 1 : 0 );
	}

	$image = afsac_import_val( $row, 'image' );
	if ( '' !== $image ) {
		if ( afsac_import_is_erase( $image ) ) {
			delete_post_thumbnail( $post_id );
		} else {
			$warn = afsac_import_set_image( $post_id, $image );
			if ( '' !== $warn ) {
				$res['messages'][] = $warn;
			}
		}
	}

	// Appariement Polylang des traductions portant la même clé.
	afsac_import_link_translations( $cle );

	$res['ok'] = true;

	return $res;
}

/**
 * Importe UNE ligne de l'onglet « Sessions ».
 *
 * @param array $row   Ligne associative.
 * @param array $opts  Options ( dry => bool ).
 * @param array $stats Compteurs passés par référence.
 * @return array{ok:bool,messages:string[],id:int}
 */
function afsac_import_row_session( $row, $opts, &$stats ) {
	$res = array(
		'ok'       => false,
		'messages' => array(),
		'id'       => 0,
	);

	$cle_cours = sanitize_title( afsac_import_val( $row, 'cle_cours' ) );
	$cle_sess  = sanitize_title( afsac_import_val( $row, 'cle_session' ) );
	$debut     = afsac_import_date( afsac_import_val( $row, 'date_debut' ) );

	if ( '' === $cle_cours ) {
		$res['messages'][] = __( 'cle_cours manquante → ligne ignorée', 'afsac' );
		return $res;
	}
	if ( '' === $debut ) {
		$res['messages'][] = __( 'date de début illisible → ligne ignorée', 'afsac' );
		return $res;
	}
	if ( '' === $cle_sess ) {
		$cle_sess = $debut;
	}

	$formations = afsac_import_find_by_key( $cle_cours, 'afsac_formation', '_afsac_import_key', '' );
	if ( ! $formations ) {
		$res['messages'][] = sprintf(
			/* translators: %s: clé du cours. */
			__( 'aucun cours ne porte la clé « %s » → session ignorée', 'afsac' ),
			$cle_cours
		);
		return $res;
	}

	// La relation pointe TOUJOURS sur l'ID canonique (langue par défaut).
	$formation_id = function_exists( 'afsac_get_default_lang_id' )
		? (int) afsac_get_default_lang_id( (int) $formations[0] )
		: (int) $formations[0];

	// Langue de la session = celle demandée, sinon celle de la formation liée.
	$lang = afsac_import_resolve_ref( afsac_import_val( $row, 'langue_fiche' ), afsac_import_ref_langues_fiche() );
	if ( '' === $lang && function_exists( 'pll_get_post_language' ) ) {
		$lang = (string) pll_get_post_language( $formation_id );
	}
	if ( '' === $lang ) {
		$lang = function_exists( 'pll_default_language' ) ? (string) pll_default_language() : 'fr';
	}

	$fin = afsac_import_date( afsac_import_val( $row, 'date_fin' ) );
	if ( '' === $fin ) {
		$fin = $debut;
	}

	$skey     = $cle_cours . '-' . $cle_sess;
	$existing = afsac_import_find_by_key( $skey, 'afsac_session', '_afsac_import_session_key', $lang );
	$post_id  = $existing ? (int) $existing[0] : 0;

	if ( ! empty( $opts['dry'] ) ) {
		$res['ok'] = true;
		$res['id'] = $post_id;
		if ( $post_id ) {
			$stats['updated']++;
		} else {
			$stats['created']++;
		}
		return $res;
	}

	$titre = afsac_import_val( $row, 'titre' );
	$lieu  = afsac_import_val( $row, 'lieu' );
	if ( '' === $titre ) {
		$abbr  = (string) get_post_meta( $formation_id, 'afsac_abbreviation', true );
		$base  = '' !== $abbr ? $abbr : get_the_title( $formation_id );
		$titre = trim( $base . ( '' !== $lieu ? ' — ' . $lieu : '' ) . ' · ' . mysql2date( 'j M Y', $debut ) );
	}

	$postarr = array(
		'post_type'  => 'afsac_session',
		'post_title' => $titre,
	);

	if ( $post_id ) {
		$postarr['ID'] = $post_id;
		$result        = wp_update_post( $postarr, true );
		$stats['updated']++;
	} else {
		$postarr['post_status'] = 'publish';
		$result                 = wp_insert_post( $postarr, true );
		$stats['created']++;
	}

	if ( is_wp_error( $result ) || ! $result ) {
		$res['messages'][] = is_wp_error( $result ) ? $result->get_error_message() : __( 'enregistrement impossible', 'afsac' );
		return $res;
	}

	$post_id   = (int) $result;
	$res['id'] = $post_id;

	if ( function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $post_id, $lang );
	}

	update_post_meta( $post_id, '_afsac_formation_id', $formation_id );
	update_post_meta( $post_id, '_afsac_import_session', 1 );
	update_post_meta( $post_id, '_afsac_import_session_key', $skey );

	afsac_import_set_field( $post_id, 'afsac_date_debut', $debut );
	afsac_import_set_field( $post_id, 'afsac_date_fin', $fin );

	$set = static function ( $col, $field ) use ( $row, $post_id ) {
		$raw = afsac_import_val( $row, $col );
		if ( '' === $raw ) {
			return;
		}
		afsac_import_set_field( $post_id, $field, afsac_import_is_erase( $raw ) ? '' : $raw );
	};

	$set( 'lieu', 'afsac_lieu' );
	$set( 'hote', 'afsac_hote' );
	$set( 'contact_nom', 'afsac_session_contact_nom' );
	$set( 'contact_email', 'afsac_session_contact_email' );
	$set( 'latitude', 'afsac_lat' );
	$set( 'longitude', 'afsac_lng' );

	$places = afsac_import_val( $row, 'places' );
	if ( '' !== $places ) {
		afsac_import_set_field( $post_id, 'afsac_places', (int) preg_replace( '/[^\d]/', '', $places ) );
	}

	$statut = afsac_import_resolve_ref( afsac_import_val( $row, 'statut_session' ), afsac_import_ref_statuts_session() );
	afsac_import_set_field( $post_id, 'afsac_statut', '' !== $statut ? $statut : 'ouvert' );

	// Langue d'animation : terme de taxonomie + champ ACF (taxonomy field).
	$lc = afsac_import_resolve_ref( afsac_import_val( $row, 'langue_session' ), afsac_import_ref_langues_cours() );
	if ( '' === $lc ) {
		$lc = in_array( $lang, array( 'fr', 'en', 'ar' ), true ) ? $lang : 'fr';
	}
	$tid = afsac_import_term( $lc, 'afsac_langue', $lang, 'langues_cours', $stats['terms'] );
	if ( $tid ) {
		wp_set_object_terms( $post_id, array( $tid ), 'afsac_langue', false );
		afsac_import_set_field( $post_id, 'afsac_langue_session', $tid );
	}

	$res['ok'] = true;

	return $res;
}

/* -------------------------------------------------------------------------
 * 6. JOB PAR LOTS
 * ---------------------------------------------------------------------- */

/**
 * État du job en cours.
 *
 * @return array|null
 */
function afsac_import_job_get() {
	$job = get_option( AFSAC_IMPORT_JOB_OPTION );
	return is_array( $job ) ? $job : null;
}

/**
 * Enregistre l'état du job.
 *
 * @param array $job État.
 * @return void
 */
function afsac_import_job_set( $job ) {
	update_option( AFSAC_IMPORT_JOB_OPTION, $job, false );
}

/**
 * Termine le job : purge le fichier temporaire et l'option.
 *
 * @param array $job État final.
 * @return void
 */
function afsac_import_job_finish( $job ) {
	if ( ! empty( $job['file'] ) && file_exists( $job['file'] ) ) {
		wp_delete_file( $job['file'] );
	}
	$job['done'] = true;
	update_option( AFSAC_IMPORT_REPORT_OPTION, $job, false );
	delete_option( AFSAC_IMPORT_JOB_OPTION );
}

/**
 * Prépare un job à partir d'un fichier déposé.
 *
 * @param string $path Chemin du fichier stocké.
 * @param string $type « formations » ou « sessions ».
 * @param bool   $dry  Simulation.
 * @return array|WP_Error État du job initialisé.
 */
function afsac_import_job_start( $path, $type, $dry ) {
	$collected = afsac_import_collect_rows( $path, $type );
	if ( is_wp_error( $collected ) ) {
		return $collected;
	}

	if ( ! $collected['sheets'] ) {
		if ( $collected['missing'] ) {
			$details = array();
			foreach ( $collected['missing'] as $sheet => $cols ) {
				$details[] = ( '' !== $sheet ? $sheet . ' : ' : '' ) . implode( ', ', $cols );
			}
			return new WP_Error(
				'afsac_import_header',
				sprintf(
					/* translators: %s: onglets et colonnes manquantes. */
					__( 'Colonnes obligatoires absentes — %s', 'afsac' ),
					implode( ' | ', $details )
				)
			);
		}
		return new WP_Error(
			'afsac_import_header',
			__( 'Aucun onglet exploitable : la 1re ligne de l’onglet doit reprendre les intitulés du modèle (Clé unique du cours, Titre du cours, …).', 'afsac' )
		);
	}

	if ( ! $collected['entries'] ) {
		return new WP_Error( 'afsac_import_empty', __( 'Onglet(s) reconnu(s), mais aucune ligne de données saisie en dessous de l’en-tête.', 'afsac' ) );
	}

	/*
	 * Doublons INTERNES au fichier : deux lignes de même clé ET même langue
	 * désignent la même fiche, la seconde écrase donc la première et un cours est
	 * perdu SANS erreur. Détecté ici, une fois, pour que la simulation le dise.
	 */
	$notes = array();
	if ( 'formations' === $type ) {
		$seen = array();
		foreach ( $collected['entries'] as $entry ) {
			$key = afsac_import_row_key( $entry['data'] );
			if ( '' === $key ) {
				continue;
			}
			$id = $key . '|' . afsac_import_norm( afsac_import_val( $entry['data'], 'langue_fiche' ) );
			if ( isset( $seen[ $id ] ) ) {
				$notes[] = array(
					'sheet' => $entry['sheet'],
					'line'  => $entry['line'],
					'msg'   => sprintf(
						/* translators: 1: clé, 2: onglet et ligne du premier exemplaire. */
						__( 'DOUBLON : même clé et même langue que %2$s (clé « %1$s ») → cette ligne écrase la précédente', 'afsac' ),
						$key,
						$seen[ $id ]
					),
				);
			} else {
				$seen[ $id ] = ( '' !== $entry['sheet'] ? $entry['sheet'] . ':' : 'ligne ' ) . $entry['line'];
			}
		}
	}

	return array(
		'file'    => $path,
		'type'    => $type,
		'dry'     => (bool) $dry,
		'offset'  => 0,
		'total'   => count( $collected['entries'] ),
		'sheets'  => $collected['sheets'],
		'created' => 0,
		'updated' => 0,
		'skipped' => 0,
		'terms'   => array(
			'created' => 0,
			'reused'  => 0,
		),
		'notes'   => $notes,
		'started' => time(),
		'done'    => false,
	);
}

/**
 * Traite un lot de lignes du job en cours.
 *
 * @param array $job   État du job (modifié).
 * @param int   $limit Nombre de lignes à traiter.
 * @return array État mis à jour.
 */
function afsac_import_run_batch( $job, $limit = AFSAC_IMPORT_BATCH ) {
	$collected = afsac_import_collect_rows( $job['file'], $job['type'] );
	if ( is_wp_error( $collected ) ) {
		$job['notes'][] = array(
			'sheet' => '',
			'line'  => 0,
			'msg'   => $collected->get_error_message(),
		);
		$job['offset'] = $job['total'];
		return $job;
	}

	$slice = array_slice( $collected['entries'], (int) $job['offset'], (int) $limit );

	$stats = array(
		'created' => 0,
		'updated' => 0,
		'terms'   => $job['terms'],
	);

	wp_defer_term_counting( true );

	foreach ( $slice as $entry ) {
		$data = $entry['data'];

		// Héritage depuis le nom de l'onglet : ne remplit QUE les cellules vides.
		foreach ( $entry['defaults'] as $col => $value ) {
			if ( '' === afsac_import_val( $data, $col ) ) {
				$data[ $col ] = $value;
			}
		}

		$result = ( 'sessions' === $job['type'] )
			? afsac_import_row_session( $data, array( 'dry' => $job['dry'] ), $stats )
			: afsac_import_row_formation( $data, array( 'dry' => $job['dry'] ), $stats );

		if ( ! $result['ok'] ) {
			$job['skipped']++;
		}

		foreach ( $result['messages'] as $msg ) {
			if ( count( $job['notes'] ) < 300 ) {
				$job['notes'][] = array(
					'sheet' => $entry['sheet'],
					'line'  => $entry['line'],
					'msg'   => $msg,
				);
			}
		}
	}

	wp_defer_term_counting( false );

	$job['created'] += $stats['created'];
	$job['updated'] += $stats['updated'];
	$job['terms']    = $stats['terms'];
	$job['offset']   = min( (int) $job['total'], (int) $job['offset'] + count( $slice ) );

	return $job;
}

/* -------------------------------------------------------------------------
 * 7. ADMINISTRATION
 * ---------------------------------------------------------------------- */

/**
 * Ajoute l'écran « Import / Export (tableur) » sous le menu Formations.
 *
 * @return void
 */
function afsac_import_menu() {
	add_submenu_page(
		'edit.php?post_type=afsac_formation',
		__( 'Import / Export (tableur)', 'afsac' ),
		__( 'Import / Export (tableur)', 'afsac' ),
		'manage_options',
		'afsac-import',
		'afsac_import_render_page'
	);
}
add_action( 'admin_menu', 'afsac_import_menu' );

/**
 * URL de l'écran d'import.
 *
 * @param array $args Paramètres additionnels.
 * @return string
 */
function afsac_import_page_url( $args = array() ) {
	return add_query_arg(
		array_merge(
			array(
				'post_type' => 'afsac_formation',
				'page'      => 'afsac-import',
			),
			$args
		),
		admin_url( 'edit.php' )
	);
}

/**
 * Dossier de stockage temporaire des fichiers déposés.
 *
 * @return string Chemin absolu terminé par un séparateur, ou '' en cas d'échec.
 */
function afsac_import_storage_dir() {
	$uploads = wp_upload_dir();
	if ( ! empty( $uploads['error'] ) ) {
		return '';
	}
	$dir = trailingslashit( $uploads['basedir'] ) . 'afsac-import/';
	if ( ! file_exists( $dir ) ) {
		wp_mkdir_p( $dir );
		// Pas d'indexation ni d'accès direct aux fichiers clients.
		file_put_contents( $dir . 'index.html', '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $dir . '.htaccess', "Deny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}
	return $dir;
}

/**
 * Traite les actions de l'écran (dépôt, lot suivant, annulation, téléchargements).
 *
 * Branché sur admin_init pour pouvoir émettre des fichiers (CSV) avant tout HTML.
 *
 * @return void
 */
function afsac_import_handle_actions() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	if ( 'afsac-import' !== $page ) {
		return;
	}

	// --- Téléchargements (modèle / export) : GET signé par nonce. -------
	$download = isset( $_GET['afsac_dl'] ) ? sanitize_key( wp_unslash( $_GET['afsac_dl'] ) ) : '';
	if ( '' !== $download && check_admin_referer( 'afsac_import_dl' ) ) {
		$type = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : 'formations';
		$type = ( 'sessions' === $type ) ? 'sessions' : 'formations';

		if ( 'modele' === $download ) {
			afsac_import_output_model_csv( $type );
		} elseif ( 'export' === $download ) {
			afsac_import_output_export_csv( $type );
		} elseif ( 'xlsx' === $download ) {
			afsac_import_output_model_xlsx();
		}
		exit;
	}

	// --- Dépôt du fichier ------------------------------------------------
	if ( isset( $_POST['afsac_import_upload'] ) && check_admin_referer( 'afsac_import_upload' ) ) {
		$type = isset( $_POST['afsac_type'] ) ? sanitize_key( wp_unslash( $_POST['afsac_type'] ) ) : 'formations';
		$type = ( 'sessions' === $type ) ? 'sessions' : 'formations';
		$dry  = ! empty( $_POST['afsac_dry'] );

		$error = '';
		$dir   = afsac_import_storage_dir();

		if ( '' === $dir ) {
			$error = __( 'Dossier de téléversement inaccessible.', 'afsac' );
		} elseif ( empty( $_FILES['afsac_file']['name'] ) || ! empty( $_FILES['afsac_file']['error'] ) ) {
			$error = __( 'Aucun fichier reçu (ou téléversement interrompu).', 'afsac' );
		} else {
			$name = sanitize_file_name( wp_unslash( $_FILES['afsac_file']['name'] ) );
			$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );

			if ( ! in_array( $ext, array( 'csv', 'txt', 'xlsx' ), true ) ) {
				$error = __( 'Format non pris en charge : déposez un .csv ou un .xlsx.', 'afsac' );
			} else {
				$dest = $dir . 'import-' . wp_generate_password( 8, false ) . '.' . $ext;
				if ( ! @move_uploaded_file( $_FILES['afsac_file']['tmp_name'], $dest ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_move_uploaded_file
					$error = __( 'Impossible d’enregistrer le fichier déposé.', 'afsac' );
				} else {
					$job = afsac_import_job_start( $dest, $type, $dry );
					if ( is_wp_error( $job ) ) {
						wp_delete_file( $dest );
						$error = $job->get_error_message();
					} else {
						delete_option( AFSAC_IMPORT_REPORT_OPTION );
						afsac_import_job_set( $job );
						wp_safe_redirect( afsac_import_page_url( array( 'run' => 1, '_wpnonce' => wp_create_nonce( 'afsac_import_run' ) ) ) );
						exit;
					}
				}
			}
		}

		set_transient( 'afsac_import_error', $error, 60 );
		wp_safe_redirect( afsac_import_page_url() );
		exit;
	}

	// --- Annulation ------------------------------------------------------
	if ( isset( $_GET['cancel'] ) && check_admin_referer( 'afsac_import_run' ) ) {
		$job = afsac_import_job_get();
		if ( $job ) {
			afsac_import_job_finish( $job );
		}
		wp_safe_redirect( afsac_import_page_url() );
		exit;
	}

	// --- Lot suivant -----------------------------------------------------
	if ( isset( $_GET['run'] ) && check_admin_referer( 'afsac_import_run' ) ) {
		$job = afsac_import_job_get();
		if ( ! $job ) {
			wp_safe_redirect( afsac_import_page_url() );
			exit;
		}

		@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$job = afsac_import_run_batch( $job );

		if ( (int) $job['offset'] >= (int) $job['total'] ) {
			afsac_import_job_finish( $job );
		} else {
			afsac_import_job_set( $job );
		}
	}
}
add_action( 'admin_init', 'afsac_import_handle_actions' );

/**
 * Écrit un CSV (UTF-8 + BOM, séparateur « ; ») directement dans la réponse.
 *
 * @param string  $filename Nom du fichier proposé.
 * @param array[] $rows     Lignes.
 * @return void
 */
function afsac_import_stream_csv( $filename, $rows ) {
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

	$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
	foreach ( $rows as $row ) {
		fputcsv( $out, $row, ';' );
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
}

/**
 * Modèle CSV vide (en-tête + ligne d'aide + ligne d'exemple, toutes deux commentées).
 *
 * @param string $type Onglet.
 * @return void
 */
function afsac_import_output_model_csv( $type ) {
	$spec   = afsac_import_spec( $type );
	$header = array();
	$help   = array();
	$sample = array();
	$first  = true;

	foreach ( $spec as $key => $col ) {
		$header[] = $key;
		$label    = $col['label'] . ( ! empty( $col['req'] ) ? ' (OBLIGATOIRE)' : '' ) . ' — ' . $col['help'];
		$help[]   = ( $first ? '# ' : '' ) . $label;
		$sample[] = ( $first ? '# ' : '' ) . ( isset( $col['ex'] ) ? $col['ex'] : '' );
		$first    = false;
	}

	afsac_import_stream_csv(
		'afsac-modele-' . $type . '.csv',
		array( $header, $help, $sample )
	);
}

/**
 * Export du contenu existant au FORMAT DU MODÈLE (aller-retour tableur).
 *
 * @param string $type Onglet.
 * @return void
 */
function afsac_import_output_export_csv( $type ) {
	$spec = afsac_import_spec( $type );
	$rows = array( array_keys( $spec ) );

	$posts = get_posts(
		array(
			'post_type'      => ( 'sessions' === $type ) ? 'afsac_session' : 'afsac_formation',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'lang'           => '',
		)
	);

	foreach ( $posts as $post ) {
		$rows[] = ( 'sessions' === $type )
			? afsac_import_export_session_row( $post, $spec )
			: afsac_import_export_formation_row( $post, $spec );
	}

	afsac_import_stream_csv( 'afsac-export-' . $type . '-' . gmdate( 'Y-m-d' ) . '.csv', $rows );
}

/**
 * Code de référentiel correspondant au 1er terme d'une taxonomie sur un post.
 *
 * @param int    $post_id  Post.
 * @param string $taxonomy Taxonomie.
 * @param string $ref      Référentiel.
 * @return string
 */
function afsac_import_export_term_code( $post_id, $taxonomy, $ref ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}
	$code = afsac_import_resolve_ref( $terms[0]->name, afsac_import_ref_table( $ref ) );
	return '' !== $code ? $code : $terms[0]->slug;
}

/**
 * Ligne d'export d'une formation.
 *
 * @param WP_Post $post Formation.
 * @param array   $spec Spécification.
 * @return array
 */
function afsac_import_export_formation_row( $post, $spec ) {
	$id  = $post->ID;
	$get = static function ( $field ) use ( $id ) {
		$value = function_exists( 'get_field' ) ? get_field( $field, $id ) : get_post_meta( $id, $field, true );
		return is_array( $value ) ? implode( ', ', $value ) : (string) $value;
	};
	$strip = static function ( $html ) {
		$text = preg_replace( '#</li>\s*#', "\n", (string) $html );
		$text = preg_replace( '#</p>\s*#', "\n", (string) $text );
		return trim( wp_strip_all_tags( (string) $text ) );
	};

	$langues = array();
	$terms   = get_the_terms( $id, 'afsac_langue' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$code = afsac_import_resolve_ref( $term->name, afsac_import_ref_langues_cours() );
			if ( '' !== $code ) {
				$langues[] = $code;
			}
		}
	}

	$key = (string) get_post_meta( $id, '_afsac_import_key', true );
	if ( '' === $key ) {
		$key = $post->post_name;
	}

	$values = array(
		'cle'                  => $key,
		'langue_fiche'         => function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $id ) : '',
		'titre'                => $post->post_title,
		'famille'              => afsac_import_export_term_code( $id, 'afsac_famille', 'familles' ),
		'domaine'              => afsac_import_export_term_code( $id, 'afsac_area', 'areas' ),
		'abreviation'          => $get( 'afsac_abbreviation' ),
		'code'                 => $get( 'afsac_code' ),
		'type_cours'           => afsac_import_export_term_code( $id, 'afsac_type', 'types' ),
		'modalite'             => afsac_import_export_term_code( $id, 'afsac_modalite', 'modalites' ),
		'methode'              => $get( 'afsac_methode' ),
		'niveau'               => $get( 'afsac_niveau' ),
		'langues_dispensees'   => implode( ';', $langues ),
		'duree'                => $get( 'afsac_duree' ),
		'frais_montant'        => $get( 'afsac_frais_montant' ),
		'devise'               => $get( 'afsac_devise' ),
		'tarif_reduit'         => $get( 'afsac_tarif_reduit' ) ? 'oui' : 'non',
		'certificat'           => $get( 'afsac_certificat' ) ? 'oui' : 'non',
		'certificat_intitule'  => $get( 'afsac_certificat_intitule' ),
		'public_resume'        => $get( 'afsac_public_resume' ),
		'presentation'         => $strip( $post->post_content ),
		'objectifs'            => $strip( $get( 'afsac_objectifs' ) ),
		'structure_modules'    => (string) $get( 'afsac_structure' ),
		'public_cible'         => $strip( $get( 'afsac_public_cible' ) ),
		'prerequis'            => (string) $get( 'afsac_prerequis' ),
		'resultats'            => $strip( $get( 'afsac_resultats' ) ),
		'autres_langues'       => $get( 'afsac_autres_langues' ),
		'developpe_par'        => $get( 'afsac_developpe_par' ),
		'developpe_par_detail' => $get( 'afsac_developpe_par_detail' ),
		'image'                => has_post_thumbnail( $id ) ? basename( (string) get_the_post_thumbnail_url( $id, 'full' ) ) : '',
		'statut'               => ( 'publish' === $post->post_status ) ? 'publier' : 'brouillon',
	);

	$row = array();
	foreach ( array_keys( $spec ) as $key_col ) {
		$row[] = isset( $values[ $key_col ] ) ? $values[ $key_col ] : '';
	}

	return $row;
}

/**
 * Ligne d'export d'une session.
 *
 * @param WP_Post $post Session.
 * @param array   $spec Spécification.
 * @return array
 */
function afsac_import_export_session_row( $post, $spec ) {
	$id           = $post->ID;
	$formation_id = (int) get_post_meta( $id, '_afsac_formation_id', true );
	$skey         = (string) get_post_meta( $id, '_afsac_import_session_key', true );
	$cle_cours    = (string) get_post_meta( $formation_id, '_afsac_import_key', true );

	$cle_session = $skey;
	if ( '' !== $cle_cours && 0 === strpos( $skey, $cle_cours . '-' ) ) {
		$cle_session = substr( $skey, strlen( $cle_cours ) + 1 );
	}

	$fmt = static function ( $ymd ) {
		$ymd = (string) $ymd;
		return preg_match( '/^\d{8}$/', $ymd ) ? substr( $ymd, 6, 2 ) . '/' . substr( $ymd, 4, 2 ) . '/' . substr( $ymd, 0, 4 ) : $ymd;
	};

	$langue = afsac_import_export_term_code( $id, 'afsac_langue', 'langues_cours' );

	$values = array(
		'cle_cours'      => $cle_cours,
		'cle_session'    => '' !== $cle_session ? $cle_session : (string) $id,
		'titre'          => $post->post_title,
		'date_debut'     => $fmt( get_post_meta( $id, 'afsac_date_debut', true ) ),
		'date_fin'       => $fmt( get_post_meta( $id, 'afsac_date_fin', true ) ),
		'lieu'           => (string) get_post_meta( $id, 'afsac_lieu', true ),
		'langue_session' => $langue,
		'hote'           => (string) get_post_meta( $id, 'afsac_hote', true ),
		'places'         => (string) get_post_meta( $id, 'afsac_places', true ),
		'statut_session' => (string) get_post_meta( $id, 'afsac_statut', true ),
		'contact_nom'    => (string) get_post_meta( $id, 'afsac_session_contact_nom', true ),
		'contact_email'  => (string) get_post_meta( $id, 'afsac_session_contact_email', true ),
		'latitude'       => (string) get_post_meta( $id, 'afsac_lat', true ),
		'longitude'      => (string) get_post_meta( $id, 'afsac_lng', true ),
		'langue_fiche'   => function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $id ) : '',
	);

	$row = array();
	foreach ( array_keys( $spec ) as $key_col ) {
		$row[] = isset( $values[ $key_col ] ) ? $values[ $key_col ] : '';
	}

	return $row;
}

/**
 * Sert le classeur modèle .xlsx s'il est présent dans le plugin.
 *
 * @return void
 */
function afsac_import_output_model_xlsx() {
	$path = AFSAC_CORE_PATH . 'modeles/AFSAC-modele-catalogue.xlsx';

	if ( ! file_exists( $path ) ) {
		wp_die( esc_html__( 'Le classeur modèle est introuvable dans le plugin (modeles/AFSAC-modele-catalogue.xlsx).', 'afsac' ) );
	}

	nocache_headers();
	header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
	header( 'Content-Disposition: attachment; filename="AFSAC-modele-catalogue.xlsx"' );
	header( 'Content-Length: ' . filesize( $path ) );
	readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
}

/**
 * Rendu de l'écran d'administration.
 *
 * @return void
 */
function afsac_import_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$job    = afsac_import_job_get();
	$report = get_option( AFSAC_IMPORT_REPORT_OPTION );
	$error  = get_transient( 'afsac_import_error' );
	if ( $error ) {
		delete_transient( 'afsac_import_error' );
	}

	echo '<div class="wrap afsac-import">';
	echo '<h1>' . esc_html__( 'Catalogue — import / export par tableur', 'afsac' ) . '</h1>';

	if ( $error ) {
		echo '<div class="notice notice-error"><p>' . esc_html( $error ) . '</p></div>';
	}

	// ---- Job en cours : progression + relance automatique ---------------
	if ( $job ) {
		$total   = max( 1, (int) $job['total'] );
		$done    = (int) $job['offset'];
		$percent = (int) floor( $done * 100 / $total );
		$next    = afsac_import_page_url(
			array(
				'run'      => 1,
				'_wpnonce' => wp_create_nonce( 'afsac_import_run' ),
			)
		);

		echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'Import en cours…', 'afsac' ) . '</strong> ';
		printf(
			/* translators: 1: lignes traitées, 2: total. */
			esc_html__( '%1$d / %2$d lignes traitées. Laissez cette page ouverte.', 'afsac' ),
			(int) $done,
			(int) $total
		);
		echo '</p>';
		echo '<div style="background:#e5e7eb;border-radius:9px;height:14px;max-width:520px;overflow:hidden;">';
		echo '<div style="background:#0054a4;height:100%;width:' . esc_attr( $percent ) . '%;"></div>';
		echo '</div>';
		echo '<p><a href="' . esc_url( $next ) . '" class="button button-primary">' . esc_html__( 'Continuer maintenant', 'afsac' ) . '</a> ';
		echo '<a href="' . esc_url( afsac_import_page_url( array( 'cancel' => 1, '_wpnonce' => wp_create_nonce( 'afsac_import_run' ) ) ) ) . '" class="button">' . esc_html__( 'Interrompre', 'afsac' ) . '</a></p>';
		echo '</div>';

		// Relance sans JavaScript.
		echo '<meta http-equiv="refresh" content="1;url=' . esc_attr( $next ) . '">';
		echo '</div>';
		return;
	}

	// ---- Compte rendu du dernier import ---------------------------------
	if ( is_array( $report ) && ! empty( $report['done'] ) ) {
		$dry = ! empty( $report['dry'] );
		echo '<div class="notice notice-success"><p><strong>';
		echo esc_html( $dry ? __( 'Simulation terminée (rien n’a été enregistré).', 'afsac' ) : __( 'Import terminé.', 'afsac' ) );
		echo '</strong><br>';
		printf(
			/* translators: 1: créées, 2: mises à jour, 3: ignorées, 4: termes créés. */
			esc_html__( '%1$d fiche(s) à créer/créée(s), %2$d mise(s) à jour, %3$d ligne(s) ignorée(s), %4$d terme(s) de taxonomie créé(s).', 'afsac' ),
			(int) $report['created'],
			(int) $report['updated'],
			(int) $report['skipped'],
			(int) $report['terms']['created']
		);
		echo '</p></div>';

		if ( ! empty( $report['sheets'] ) ) {
			echo '<h2>' . esc_html__( 'Onglets lus', 'afsac' ) . '</h2>';
			echo '<table class="widefat striped" style="max-width:900px"><thead><tr>';
			echo '<th>' . esc_html__( 'Onglet', 'afsac' ) . '</th><th style="width:120px">' . esc_html__( 'Lignes', 'afsac' ) . '</th>';
			echo '<th>' . esc_html__( 'Valeurs héritées du nom de l’onglet', 'afsac' ) . '</th></tr></thead><tbody>';
			foreach ( $report['sheets'] as $sheet ) {
				$inherited = array();
				foreach ( (array) $sheet['defaults'] as $col => $value ) {
					$inherited[] = $col . ' = ' . $value;
				}
				echo '<tr><td><strong>' . esc_html( $sheet['name'] ) . '</strong></td>';
				echo '<td>' . esc_html( $sheet['rows'] ) . '</td>';
				echo '<td>' . esc_html( $inherited ? implode( ' · ', $inherited ) : '—' ) . '</td></tr>';
			}
			echo '</tbody></table>';
		}

		if ( ! empty( $report['notes'] ) ) {
			echo '<h2>' . esc_html__( 'Points d’attention', 'afsac' ) . '</h2>';
			echo '<table class="widefat striped" style="max-width:1000px"><thead><tr>';
			echo '<th style="width:200px">' . esc_html__( 'Onglet', 'afsac' ) . '</th>';
			echo '<th style="width:90px">' . esc_html__( 'Ligne', 'afsac' ) . '</th>';
			echo '<th>' . esc_html__( 'Message', 'afsac' ) . '</th></tr></thead><tbody>';
			foreach ( $report['notes'] as $note ) {
				$sheet = isset( $note['sheet'] ) && '' !== $note['sheet'] ? $note['sheet'] : '—';
				echo '<tr><td>' . esc_html( $sheet ) . '</td>';
				echo '<td>' . esc_html( $note['line'] ? $note['line'] : '—' ) . '</td>';
				echo '<td>' . esc_html( $note['msg'] ) . '</td></tr>';
			}
			echo '</tbody></table>';
		}
	}

	$dl = static function ( $what, $type ) {
		return wp_nonce_url(
			afsac_import_page_url(
				array(
					'afsac_dl' => $what,
					'type'     => $type,
				)
			),
			'afsac_import_dl'
		);
	};

	// ---- 1. Modèles ------------------------------------------------------
	echo '<h2>' . esc_html__( '1. Le modèle à envoyer au client', 'afsac' ) . '</h2>';
	echo '<p>' . esc_html__( 'Le classeur Excel contient un mode d’emploi, les deux onglets à remplir (Formations, Sessions) et la liste des codes autorisés. Une ligne = un cours dans une langue.', 'afsac' ) . '</p>';
	echo '<p>';
	echo '<a class="button button-primary" href="' . esc_url( $dl( 'xlsx', 'formations' ) ) . '">' . esc_html__( 'Télécharger le classeur Excel (.xlsx)', 'afsac' ) . '</a> ';
	echo '<a class="button" href="' . esc_url( $dl( 'modele', 'formations' ) ) . '">' . esc_html__( 'Modèle CSV — Formations', 'afsac' ) . '</a> ';
	echo '<a class="button" href="' . esc_url( $dl( 'modele', 'sessions' ) ) . '">' . esc_html__( 'Modèle CSV — Sessions', 'afsac' ) . '</a>';
	echo '</p>';

	// ---- 2. Import -------------------------------------------------------
	echo '<h2>' . esc_html__( '2. Importer le fichier rempli', 'afsac' ) . '</h2>';
	echo '<form method="post" enctype="multipart/form-data" style="background:#fff;border:1px solid #dcdcde;padding:16px;max-width:760px">';
	wp_nonce_field( 'afsac_import_upload' );
	echo '<table class="form-table"><tbody>';

	echo '<tr><th scope="row"><label for="afsac_file">' . esc_html__( 'Fichier', 'afsac' ) . '</label></th><td>';
	echo '<input type="file" name="afsac_file" id="afsac_file" accept=".csv,.xlsx,.txt" required> ';
	echo '<p class="description">' . esc_html__( '.xlsx (classeur Excel / Google Sheets) ou .csv (UTF-8). TOUS les onglets qui reprennent les colonnes du modèle sont lus : le client peut donc dupliquer l’onglet « Formations » autant de fois qu’il veut, par exemple un onglet par domaine OACI.', 'afsac' ) . '</p>';
	echo '<p class="description">' . esc_html__( 'Un onglet nommé d’après un domaine (ex. « AVIATION SECURITY », « Aviation LAW », « Sûreté de l’aviation ») transmet ce domaine à ses lignes dont la colonne « domaine » est vide.', 'afsac' ) . '</p>';
	echo '</td></tr>';

	echo '<tr><th scope="row">' . esc_html__( 'Contenu', 'afsac' ) . '</th><td>';
	echo '<label><input type="radio" name="afsac_type" value="formations" checked> ' . esc_html__( 'Formations (cours)', 'afsac' ) . '</label><br>';
	echo '<label><input type="radio" name="afsac_type" value="sessions"> ' . esc_html__( 'Sessions (dates planifiées)', 'afsac' ) . '</label>';
	echo '</td></tr>';

	echo '<tr><th scope="row">' . esc_html__( 'Mode', 'afsac' ) . '</th><td>';
	echo '<label><input type="checkbox" name="afsac_dry" value="1" checked> <strong>' . esc_html__( 'Simulation', 'afsac' ) . '</strong> — ' . esc_html__( 'contrôle le fichier et signale les erreurs sans rien enregistrer.', 'afsac' ) . '</label>';
	echo '<p class="description">' . esc_html__( 'Décochez pour lancer l’import réel. Relancer le même fichier met à jour les fiches, il n’y a jamais de doublon (la colonne « cle » fait foi).', 'afsac' ) . '</p>';
	echo '</td></tr>';

	echo '</tbody></table>';
	echo '<p><button type="submit" name="afsac_import_upload" value="1" class="button button-primary">' . esc_html__( 'Analyser / importer', 'afsac' ) . '</button></p>';
	echo '</form>';

	// ---- 3. Export -------------------------------------------------------
	echo '<h2>' . esc_html__( '3. Exporter l’existant', 'afsac' ) . '</h2>';
	echo '<p>' . esc_html__( 'Exporte le catalogue actuel dans le format du modèle : pratique pour faire relire/compléter par le client, puis ré-importer le même fichier.', 'afsac' ) . '</p>';
	echo '<p>';
	echo '<a class="button" href="' . esc_url( $dl( 'export', 'formations' ) ) . '">' . esc_html__( 'Exporter les formations (CSV)', 'afsac' ) . '</a> ';
	echo '<a class="button" href="' . esc_url( $dl( 'export', 'sessions' ) ) . '">' . esc_html__( 'Exporter les sessions (CSV)', 'afsac' ) . '</a>';
	echo '</p>';

	// ---- Aide colonnes ---------------------------------------------------
	echo '<h2>' . esc_html__( 'Colonnes attendues', 'afsac' ) . '</h2>';
	foreach ( array( 'formations', 'sessions' ) as $sheet ) {
		echo '<h3>' . esc_html( 'formations' === $sheet ? __( 'Onglet « Formations »', 'afsac' ) : __( 'Onglet « Sessions »', 'afsac' ) ) . '</h3>';
		echo '<table class="widefat striped" style="max-width:1000px"><thead><tr>';
		echo '<th style="width:180px">' . esc_html__( 'Colonne', 'afsac' ) . '</th><th style="width:200px">' . esc_html__( 'Intitulé', 'afsac' ) . '</th><th>' . esc_html__( 'Règle', 'afsac' ) . '</th></tr></thead><tbody>';
		foreach ( afsac_import_spec( $sheet ) as $key => $col ) {
			echo '<tr><td><code>' . esc_html( $key ) . '</code>' . ( ! empty( $col['req'] ) ? ' <strong style="color:#b32d2e">*</strong>' : '' ) . '</td>';
			echo '<td>' . esc_html( $col['label'] ) . '</td>';
			echo '<td>' . esc_html( $col['help'] ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}

	echo '<p class="description">' . esc_html__( '* colonne obligatoire. Une cellule laissée vide ne modifie pas la valeur déjà saisie dans WordPress ; pour vider un champ, saisir un tiret « - ».', 'afsac' ) . '</p>';

	echo '</div>';
}

/* -------------------------------------------------------------------------
 * 8. WP-CLI
 * ---------------------------------------------------------------------- */

/**
 * Commande WP-CLI : wp afsac import <fichier> [--type=<type>] [--dry-run]
 *
 * @param array $args       Arguments positionnels.
 * @param array $assoc_args Options.
 * @return void
 */
function afsac_cli_import( $args, $assoc_args ) {
	$file = isset( $args[0] ) ? $args[0] : '';
	$type = isset( $assoc_args['type'] ) && 'sessions' === $assoc_args['type'] ? 'sessions' : 'formations';
	$dry  = isset( $assoc_args['dry-run'] );

	if ( ! $file || ! file_exists( $file ) ) {
		WP_CLI::error( 'Fichier introuvable : ' . $file );
		return;
	}

	$job = afsac_import_job_start( $file, $type, $dry );
	if ( is_wp_error( $job ) ) {
		WP_CLI::error( $job->get_error_message() );
		return;
	}

	$progress = WP_CLI\Utils\make_progress_bar( 'Import', (int) $job['total'] );
	while ( (int) $job['offset'] < (int) $job['total'] ) {
		$before = (int) $job['offset'];
		$job    = afsac_import_run_batch( $job, 25 );
		$progress->tick( (int) $job['offset'] - $before );
	}
	$progress->finish();

	foreach ( $job['sheets'] as $sheet ) {
		WP_CLI::log( sprintf( '  onglet « %s » : %d ligne(s)%s', $sheet['name'], (int) $sheet['rows'], $sheet['defaults'] ? ' (hérite : ' . implode( ', ', $sheet['defaults'] ) . ')' : '' ) );
	}

	foreach ( $job['notes'] as $note ) {
		WP_CLI::warning(
			sprintf(
				'%sligne %d : %s',
				! empty( $note['sheet'] ) ? '[' . $note['sheet'] . '] ' : '',
				(int) $note['line'],
				$note['msg']
			)
		);
	}

	// Le fichier source de la CLI n'appartient pas au plugin : ne pas le supprimer.
	WP_CLI::success(
		sprintf(
			'%d créée(s), %d mise(s) à jour, %d ignorée(s)%s.',
			(int) $job['created'],
			(int) $job['updated'],
			(int) $job['skipped'],
			$dry ? ' [simulation]' : ''
		)
	);
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'afsac import', 'afsac_cli_import' );
}
