<?php
/**
 * Générateur du classeur modèle « AFSAC-modele-catalogue.xlsx » envoyé au client.
 *
 * Le classeur est construit à partir de la MÊME spécification de colonnes que
 * l'importeur (includes/import-catalogue.php) : une seule source de vérité, donc
 * pas de dérive entre ce que le client remplit et ce que WordPress attend.
 *
 * Le fichier .xlsx est écrit tel quel (zip + XML Open XML), sans bibliothèque
 * externe. À relancer après toute modification de afsac_import_spec() :
 *
 *   php modeles/build-modele-xlsx.php
 *
 * (extension PHP « zip » requise ; sous Local :
 *   php -d extension_dir=<bin>/ext -d extension=zip modeles/build-modele-xlsx.php)
 *
 * @package AFSAC\Core
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( 'CLI uniquement.' );
}

// --- Bootstrap minimal : on charge la spec sans WordPress. -------------------
define( 'ABSPATH', __DIR__ );
if ( ! function_exists( 'add_action' ) ) {
	/**
	 * Stub : la spec est chargée hors WordPress.
	 *
	 * @return void
	 */
	function add_action() {}
}
if ( ! function_exists( '__' ) ) {
	/**
	 * Stub de traduction.
	 *
	 * @param string $text Texte.
	 * @return string
	 */
	function __( $text ) {
		return $text;
	}
}
if ( ! function_exists( 'sanitize_title' ) ) {
	/**
	 * Stub simplifié de sanitize_title (non utilisé à la génération).
	 *
	 * @param string $text Texte.
	 * @return string
	 */
	function sanitize_title( $text ) {
		return strtolower( trim( (string) $text ) );
	}
}

require_once dirname( __DIR__ ) . '/includes/import-catalogue.php';

/* -------------------------------------------------------------------------
 * Petits utilitaires Open XML
 * ---------------------------------------------------------------------- */

/**
 * Échappe une valeur pour du contenu XML.
 *
 * @param string $value Valeur.
 * @return string
 */
function afsac_x( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8' );
}

/**
 * Lettre(s) de colonne Excel pour un index 0-based (0 → A, 27 → AB).
 *
 * @param int $index Index.
 * @return string
 */
function afsac_col( $index ) {
	$letters = '';
	$index  += 1;
	while ( $index > 0 ) {
		$mod     = ( $index - 1 ) % 26;
		$letters = chr( 65 + $mod ) . $letters;
		$index   = (int) ( ( $index - $mod ) / 26 );
	}
	return $letters;
}

/**
 * Cellule texte.
 *
 * @param int    $col   Index de colonne.
 * @param int    $row   Numéro de ligne (1-based).
 * @param string $value Contenu.
 * @param int    $style Index de style.
 * @return string
 */
function afsac_cell( $col, $row, $value, $style = 0 ) {
	if ( '' === (string) $value ) {
		return $style ? '<c r="' . afsac_col( $col ) . $row . '" s="' . $style . '"/>' : '';
	}
	return '<c r="' . afsac_col( $col ) . $row . '" s="' . (int) $style . '" t="inlineStr"><is><t xml:space="preserve">'
		. afsac_x( $value ) . '</t></is></c>';
}

/**
 * Ligne complète à partir d'un tableau de valeurs.
 *
 * @param int      $row    Numéro de ligne.
 * @param string[] $values Valeurs.
 * @param int|int[] $style Style unique ou tableau par colonne.
 * @param int      $height Hauteur de ligne (0 = auto).
 * @return string
 */
function afsac_row( $row, $values, $style = 0, $height = 0 ) {
	$cells = '';
	foreach ( array_values( $values ) as $i => $value ) {
		$s      = is_array( $style ) ? ( isset( $style[ $i ] ) ? $style[ $i ] : 0 ) : $style;
		$cells .= afsac_cell( $i, $row, $value, $s );
	}
	$attrs = ' r="' . (int) $row . '"';
	if ( $height ) {
		$attrs .= ' ht="' . (int) $height . '" customHeight="1"';
	}
	return '<row' . $attrs . '>' . $cells . '</row>';
}

/* -------------------------------------------------------------------------
 * Feuilles de saisie (Formations / Sessions)
 * ---------------------------------------------------------------------- */

/**
 * Construit le XML d'une feuille de saisie.
 *
 * Disposition : ligne 1 = intitulés (en-tête figé), ligne 2 = noms techniques,
 * ligne 3 = règle de remplissage, lignes 4-5 = exemples. Les lignes 2 à 5
 * commencent par « # » : l'import les ignore.
 *
 * @param string $sheet    « formations » ou « sessions ».
 * @param array  $examples Lignes d'exemple (tableaux clé => valeur).
 * @return string XML de la feuille.
 */
function afsac_build_input_sheet( $sheet, $examples ) {
	$spec  = afsac_import_spec( $sheet );
	$keys  = array_keys( $spec );
	$last  = afsac_col( count( $keys ) - 1 );

	// Colonnes : largeur + style par défaut (jaune si obligatoire, texte si demandé).
	$cols = '<cols>';
	$i    = 1;
	foreach ( $spec as $col ) {
		$req   = ! empty( $col['req'] );
		$text  = isset( $col['fmt'] ) && 'text' === $col['fmt'];
		$style = 4; // Normal, retour à la ligne.
		if ( $req && $text ) {
			$style = 6;
		} elseif ( $req ) {
			$style = 5;
		} elseif ( $text ) {
			$style = 7;
		}
		$cols .= '<col min="' . $i . '" max="' . $i . '" width="' . (float) ( isset( $col['w'] ) ? $col['w'] : 20 )
			. '" customWidth="1" style="' . $style . '"/>';
		++$i;
	}
	$cols .= '</cols>';

	// Lignes d'en-tête.
	$labels = array();
	$techs  = array();
	$helps  = array();
	$first  = true;
	foreach ( $spec as $key => $col ) {
		$labels[] = $col['label'] . ( ! empty( $col['req'] ) ? ' *' : '' );
		$techs[]  = ( $first ? '# ' : '' ) . $key;
		$helps[]  = ( $first ? '# ' : '' ) . $col['help'];
		$first    = false;
	}

	$data  = afsac_row( 1, $labels, 1, 34 );
	$data .= afsac_row( 2, $techs, 2, 16 );
	$data .= afsac_row( 3, $helps, 3, 58 );

	$line = 4;
	foreach ( $examples as $example ) {
		$values = array();
		$first  = true;
		foreach ( $keys as $key ) {
			$value    = isset( $example[ $key ] ) ? $example[ $key ] : '';
			$values[] = ( $first ? '# ' : '' ) . $value;
			$first    = false;
		}
		$data .= afsac_row( $line, $values, 8, 30 );
		++$line;
	}

	// Menus déroulants sur les colonnes à liste fermée.
	$validations = '';
	$count       = 0;
	$i           = 0;
	foreach ( $spec as $col ) {
		if ( ! empty( $col['list'] ) ) {
			$codes = array_keys( afsac_import_ref_table( $col['list'] ) );
			$list  = implode( ',', $codes );
			if ( strlen( $list ) < 250 ) {
				$ref          = afsac_col( $i );
				$validations .= '<dataValidation type="list" allowBlank="1" showInputMessage="1" showErrorMessage="0" sqref="'
					. $ref . ( $line ) . ':' . $ref . '2000"><formula1>"' . afsac_x( $list ) . '"</formula1></dataValidation>';
				++$count;
			}
		}
		++$i;
	}
	if ( $count ) {
		$validations = '<dataValidations count="' . $count . '">' . $validations . '</dataValidations>';
	}

	return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
		. '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
		. '<dimension ref="A1:' . $last . '2000"/>'
		. '<sheetViews><sheetView workbookViewId="0">'
		. '<pane xSplit="1" ySplit="1" topLeftCell="B2" activePane="bottomRight" state="frozen"/>'
		. '</sheetView></sheetViews>'
		. '<sheetFormatPr defaultRowHeight="15"/>'
		. $cols
		. '<sheetData>' . $data . '</sheetData>'
		. '<autoFilter ref="A1:' . $last . '1"/>'
		. $validations
		. '</worksheet>';
}

/**
 * Feuille « Mode d'emploi ».
 *
 * @return string XML.
 */
function afsac_build_readme_sheet() {
	$lines = array(
		array( 'AFSAC — Chargement du catalogue de formations', 9 ),
		array( '', 0 ),
		array( 'Ce classeur sert à charger EN UNE SEULE FOIS l’ensemble des cours (TRAINAIR PLUS et AVSEC) et leurs sessions sur le site.', 10 ),
		array( 'Une fois importées, toutes les fiches restent modifiables normalement dans l’administration WordPress, et de nouveaux cours peuvent être ajoutés à la main sans repasser par ce fichier.', 10 ),
		array( '', 0 ),
		array( 'COMMENT REMPLIR', 9 ),
		array( '1. Onglet « Formations » : une ligne = un cours dans une langue. La ligne 1 (bleue) donne l’intitulé des colonnes ; les lignes grises 2 à 5 sont de l’aide et des exemples — elles commencent par « # » et sont ignorées à l’import : vous pouvez les laisser.', 10 ),
		array( '2. Commencez votre saisie à la ligne 6. Les colonnes sur fond jaune sont OBLIGATOIRES (5 seulement) ; tout le reste peut rester vide et être complété plus tard.', 10 ),
		array( '3. Colonne « cle » : un identifiant court, stable et unique par cours (ex. avsec-fret-poste), en minuscules, sans espaces ni accents. C’est LA colonne qui évite les doublons : si vous renvoyez le fichier corrigé, les fiches sont mises à jour au lieu d’être recréées. Ne la modifiez plus une fois le cours en ligne.', 10 ),
		array( '4. Version anglaise d’un cours : ajoutez une DEUXIÈME ligne avec LA MÊME « cle », en mettant « en » dans la colonne « langue_fiche ». Les deux fiches seront automatiquement liées comme traductions. Idem pour l’arabe (« ar »).', 10 ),
		array( '5. Colonnes à menu déroulant (domaine, famille, modalité, type, niveau…) : choisissez un code dans la liste. Tous les codes sont détaillés dans l’onglet « Listes ».', 10 ),
		array( '6. Listes à puces (objectifs, modules, prérequis, résultats) : saisissez un élément PAR LIGNE dans la cellule (Alt + Entrée), ou séparez-les par une barre verticale « | ».', 10 ),
		array( '7. Onglet « Sessions » : à remplir uniquement pour les cours dont les dates sont connues. La colonne « cle_cours » doit reprendre EXACTEMENT la « cle » du cours concerné.', 10 ),
		array( '', 0 ),
		array( 'UN ONGLET PAR DOMAINE (facultatif, recommandé au-delà de 100 cours)', 9 ),
		array( 'Vous pouvez dupliquer l’onglet « Formations » autant de fois que nécessaire — par exemple un onglet par domaine OACI — et répartir la saisie entre plusieurs personnes. Tous ces onglets sont importés ensemble.', 10 ),
		array( 'Nommez alors l’onglet d’après le domaine (« AVIATION SECURITY », « Aviation LAW », « FLIGHT SAFETY », « Sûreté de l’aviation »…) : la colonne « domaine » peut rester vide, elle est déduite du nom de l’onglet. Une valeur saisie dans la colonne reste prioritaire.', 10 ),
		array( 'Attention à conserver la ligne 1 (les intitulés bleus) dans chaque onglet dupliqué : c’est elle qui permet de reconnaître les colonnes.', 10 ),
		array( '', 0 ),
		array( 'À SAVOIR', 9 ),
		array( '• Une cellule laissée vide ne supprime rien : la valeur déjà présente sur le site est conservée. Pour vider volontairement un champ, saisissez un tiret « - ».', 10 ),
		array( '• Vous pouvez envoyer le fichier en plusieurs fois (par lots de cours) : chaque envoi complète le catalogue.', 10 ),
		array( '• N’ajoutez pas de colonne et ne renommez pas la ligne 1 ; en revanche l’ordre des colonnes peut être modifié sans risque.', 10 ),
		array( '• Les visuels ne sont pas obligatoires : laissez la colonne vide, une illustration par défaut est appliquée.', 10 ),
		array( '', 0 ),
		array( 'CONTENU DU CLASSEUR', 9 ),
		array( 'Formations — le catalogue des cours (l’essentiel du travail).', 10 ),
		array( 'Sessions — les dates planifiées, rattachées aux cours.', 10 ),
		array( 'Listes — tous les codes autorisés, avec leur libellé en français, anglais et arabe.', 10 ),
	);

	$data = '';
	$row  = 1;
	foreach ( $lines as $line ) {
		$data .= afsac_row( $row, array( $line[0] ), $line[1], '' === $line[0] ? 8 : 0 );
		++$row;
	}

	return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
		. '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
		. '<dimension ref="A1:A' . ( $row - 1 ) . '"/>'
		. '<sheetViews><sheetView tabSelected="1" workbookViewId="0"/></sheetViews>'
		. '<sheetFormatPr defaultRowHeight="15"/>'
		. '<cols><col min="1" max="1" width="130" customWidth="1" style="4"/></cols>'
		. '<sheetData>' . $data . '</sheetData>'
		. '</worksheet>';
}

/**
 * Feuille « Listes » : tous les référentiels, code + libellés.
 *
 * @return string XML.
 */
function afsac_build_lists_sheet() {
	$blocks = array(
		'Langue de la fiche (colonne langue_fiche)'        => 'langues_fiche',
		'Famille (colonne famille)'                        => 'familles',
		'Domaine OACI (colonne domaine)'                   => 'areas',
		'Type de cours (colonne type_cours)'               => 'types',
		'Modalité (colonne modalite)'                      => 'modalites',
		'Méthode (colonne methode)'                        => 'methodes',
		'Niveau (colonne niveau)'                          => 'niveaux',
		'Langues de dispensation (colonne langues_dispensees, séparées par « ; »)' => 'langues_cours',
		'Devise (colonne devise)'                          => 'devises',
		'Statut de session (colonne statut_session)'       => 'statuts_session',
	);

	$data = afsac_row( 1, array( 'Codes autorisés' ), 9, 26 );
	$row  = 3;

	foreach ( $blocks as $title => $ref ) {
		$data .= afsac_row( $row, array( $title ), 10 );
		++$row;
		$data .= afsac_row( $row, array( 'Code à saisir', 'Français', 'English', 'العربية' ), 1, 20 );
		++$row;
		foreach ( afsac_import_ref_table( $ref ) as $code => $names ) {
			$names = (array) $names;
			$data .= afsac_row(
				$row,
				array(
					$code,
					isset( $names['fr'] ) ? $names['fr'] : reset( $names ),
					isset( $names['en'] ) ? $names['en'] : '',
					isset( $names['ar'] ) ? $names['ar'] : '',
				),
				0
			);
			++$row;
		}
		++$row;
	}

	return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
		. '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
		. '<dimension ref="A1:D' . ( $row - 1 ) . '"/>'
		. '<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
		. '<sheetFormatPr defaultRowHeight="15"/>'
		. '<cols><col min="1" max="1" width="24" customWidth="1"/><col min="2" max="2" width="46" customWidth="1"/>'
		. '<col min="3" max="3" width="46" customWidth="1"/><col min="4" max="4" width="34" customWidth="1"/></cols>'
		. '<sheetData>' . $data . '</sheetData>'
		. '</worksheet>';
}

/**
 * Feuille de styles.
 *
 * @return string XML.
 */
function afsac_build_styles() {
	return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
		. '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
		. '<fonts count="5">'
		. '<font><sz val="11"/><color rgb="FF101828"/><name val="Calibri"/><family val="2"/></font>'
		. '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>'
		. '<font><i/><sz val="9"/><color rgb="FF667085"/><name val="Calibri"/><family val="2"/></font>'
		. '<font><b/><sz val="16"/><color rgb="FF0054A4"/><name val="Calibri"/><family val="2"/></font>'
		. '<font><b/><sz val="11"/><color rgb="FF0054A4"/><name val="Calibri"/><family val="2"/></font>'
		. '</fonts>'
		. '<fills count="5">'
		. '<fill><patternFill patternType="none"/></fill>'
		. '<fill><patternFill patternType="gray125"/></fill>'
		. '<fill><patternFill patternType="solid"><fgColor rgb="FF0054A4"/><bgColor indexed="64"/></patternFill></fill>'
		. '<fill><patternFill patternType="solid"><fgColor rgb="FFF1F3F5"/><bgColor indexed="64"/></patternFill></fill>'
		. '<fill><patternFill patternType="solid"><fgColor rgb="FFFFF7DB"/><bgColor indexed="64"/></patternFill></fill>'
		. '</fills>'
		. '<borders count="2">'
		. '<border><left/><right/><top/><bottom/><diagonal/></border>'
		. '<border><left style="thin"><color rgb="FFD0D5DD"/></left><right style="thin"><color rgb="FFD0D5DD"/></right>'
		. '<top style="thin"><color rgb="FFD0D5DD"/></top><bottom style="thin"><color rgb="FFD0D5DD"/></bottom><diagonal/></border>'
		. '</borders>'
		. '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
		. '<cellXfs count="11">'
		// 0 — normal.
		. '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
		// 1 — en-tête.
		. '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">'
		. '<alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
		// 2 — nom technique.
		. '<xf numFmtId="49" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">'
		. '<alignment vertical="center"/></xf>'
		// 3 — aide.
		. '<xf numFmtId="49" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">'
		. '<alignment vertical="top" wrapText="1"/></xf>'
		// 4 — colonne normale (retour à la ligne).
		. '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
		// 5 — colonne obligatoire.
		. '<xf numFmtId="0" fontId="0" fillId="4" borderId="0" xfId="0" applyFill="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
		// 6 — colonne obligatoire, format texte.
		. '<xf numFmtId="49" fontId="0" fillId="4" borderId="0" xfId="0" applyNumberFormat="1" applyFill="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
		// 7 — colonne format texte.
		. '<xf numFmtId="49" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
		// 8 — exemple.
		. '<xf numFmtId="49" fontId="2" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1">'
		. '<alignment vertical="top" wrapText="1"/></xf>'
		// 9 — titre.
		. '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf>'
		// 10 — sous-titre / paragraphe.
		. '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
		. '</cellXfs>'
		. '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
		. '</styleSheet>';
}

/* -------------------------------------------------------------------------
 * Assemblage du paquet
 * ---------------------------------------------------------------------- */

$sheets = array(
	array(
		'name' => 'Mode d’emploi',
		'xml'  => afsac_build_readme_sheet(),
	),
	array(
		'name' => 'Formations',
		'xml'  => afsac_build_input_sheet(
			'formations',
			array(
				array(
					'cle'                  => 'avsec-fret-poste',
					'langue_fiche'         => 'fr',
					'titre'                => 'Sûreté du Fret et de la Poste',
					'famille'              => 'avsec',
					'domaine'              => 'surete',
					'abreviation'          => 'AVSEC CARGO',
					'code'                 => '',
					'type_cours'           => 'oaci',
					'modalite'             => 'presentiel',
					'methode'              => 'instructeur',
					'niveau'               => 'fondamental',
					'langues_dispensees'   => 'fr;en;ar',
					'duree'                => '5 jours',
					'frais_montant'        => '1500',
					'devise'               => 'USD',
					'tarif_reduit'         => 'non',
					'certificat'           => 'oui',
					'certificat_intitule'  => 'Certificat de l’OACI',
					'public_resume'        => 'Agents de sûreté',
					'presentation'         => 'Permettre au personnel concerné de comprendre l’origine et le but des mesures de sûreté protégeant le fret et la poste.',
					'objectifs'            => "Comprendre la menace liée aux explosifs\nAppliquer les contrôles de sûreté aux expéditions",
					'structure_modules'    => "Contexte de la sûreté du fret\nConcepts de la sûreté du fret\nProcédures de la sûreté du fret",
					'public_cible'         => 'Employés chargés de la réception, de l’enregistrement et de la manutention du fret.',
					'prerequis'            => "Maîtriser la langue d’enseignement\nExercer une fonction opérationnelle",
					'resultats'            => '',
					'autres_langues'       => '',
					'developpe_par'        => 'ICAO · OACI',
					'developpe_par_detail' => 'International Civil Aviation Organization · Montréal, Canada',
					'image'                => '',
					'statut'               => 'publier',
				),
				array(
					'cle'                  => 'avsec-fret-poste',
					'langue_fiche'         => 'en',
					'titre'                => 'Air Cargo and Mail Security Course',
					'famille'              => 'avsec',
					'domaine'              => 'surete',
					'abreviation'          => 'AVSEC CARGO',
					'type_cours'           => 'oaci',
					'modalite'             => 'presentiel',
					'methode'              => 'instructeur',
					'niveau'               => 'fondamental',
					'langues_dispensees'   => 'fr;en;ar',
					'duree'                => '5 days',
					'frais_montant'        => '1500',
					'devise'               => 'USD',
					'certificat'           => 'oui',
					'certificat_intitule'  => 'ICAO Certificate',
					'presentation'         => 'To enable the target population to secure air cargo and mail against acts of unlawful interference.',
					'objectifs'            => "Explain the origin and purpose of required security measures\nDescribe the secure supply chain",
					'structure_modules'    => "Course Introduction\nCargo and Mail Security in Context\nThe Secure Supply Chain",
					'statut'               => 'publier',
				),
			)
		),
	),
	array(
		'name' => 'Sessions',
		'xml'  => afsac_build_input_sheet(
			'sessions',
			array(
				array(
					'cle_cours'      => 'avsec-fret-poste',
					'cle_session'    => 's1',
					'titre'          => '',
					'date_debut'     => '14/09/2026',
					'date_fin'       => '18/09/2026',
					'lieu'           => 'Tunis, Tunisie',
					'langue_session' => 'fr',
					'hote'           => 'AFSAC',
					'places'         => '20',
					'statut_session' => 'ouvert',
				),
			)
		),
	),
	array(
		'name' => 'Listes',
		'xml'  => afsac_build_lists_sheet(),
	),
);

$overrides = '';
$wb_sheets = '';
$wb_rels   = '';
$n         = 1;
foreach ( $sheets as $sheet ) {
	$overrides .= '<Override PartName="/xl/worksheets/sheet' . $n . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
	$wb_sheets .= '<sheet name="' . afsac_x( $sheet['name'] ) . '" sheetId="' . $n . '" r:id="rId' . $n . '"/>';
	$wb_rels   .= '<Relationship Id="rId' . $n . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $n . '.xml"/>';
	++$n;
}
$wb_rels .= '<Relationship Id="rId' . $n . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

$parts = array(
	'[Content_Types].xml'      => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
		. '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
		. '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
		. '<Default Extension="xml" ContentType="application/xml"/>'
		. '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
		. $overrides
		. '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
		. '</Types>',
	'_rels/.rels'              => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
		. '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
		. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
		. '</Relationships>',
	'xl/workbook.xml'          => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
		. '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
		. 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
		. '<sheets>' . $wb_sheets . '</sheets></workbook>',
	'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
		. '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $wb_rels . '</Relationships>',
	'xl/styles.xml'            => afsac_build_styles(),
);

$n = 1;
foreach ( $sheets as $sheet ) {
	$parts[ 'xl/worksheets/sheet' . $n . '.xml' ] = $sheet['xml'];
	++$n;
}

$target = __DIR__ . '/AFSAC-modele-catalogue.xlsx';
if ( file_exists( $target ) ) {
	unlink( $target );
}

$zip = new ZipArchive();
if ( true !== $zip->open( $target, ZipArchive::CREATE ) ) {
	exit( "Impossible de créer l'archive.\n" );
}
foreach ( $parts as $name => $content ) {
	$zip->addFromString( $name, $content );
}
$zip->close();

printf( "OK — %s (%d octets, %d parties)\n", basename( $target ), filesize( $target ), count( $parts ) );
