<?php
/**
 * Import des TÉMOIGNAGES VIDÉO et des RÉFÉRENCES depuis la chaîne YouTube AFSAC.
 *
 * Source : https://www.youtube.com/@AFSACICAOASTCTUNISIA — 26 retours
 * d'expérience de participants (les vidéos de couverture presse et la vidéo
 * « métier d'agent de sûreté » sont volontairement exclues : ce ne sont pas des
 * témoignages). Chaque entrée porte l'identité réelle du participant telle
 * qu'annoncée dans le titre/description de la vidéo.
 *
 * IMPORTANT — le champ `desc` est une DESCRIPTION de la session filmée, tirée de
 * la description YouTube ; ce n'est PAS un propos verbatim. Il est stocké dans
 * le contenu de l'éditeur, et le thème le rend en paragraphe neutre. Le champ
 * ACF `afsac_temoignage_citation` reste vide : il est réservé aux vraies
 * citations, que seul le client peut transcrire depuis les vidéos.
 *
 * Idempotent : la clé `_afsac_yt_id` (identifiant de la vidéo) sert de pivot ;
 * relancer met à jour sans dupliquer.
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Les 26 témoignages de participants publiés sur la chaîne YouTube AFSAC.
 *
 * @return array<int,array<string,string>>
 */
function afsac_temoignages_data() {
	return array(
		// ---------------------------------------------------------------- FR.
		array(
			'yt' => 'Cni2XlS_UXw', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '1:47',
			'auteur' => 'M. Alain Sibgue Dambil', 'fonction' => 'Chef de Division Contrôle Qualité',
			'org' => 'ADAC Tchad', 'pays' => 'Tchad',
			'desc' => 'Retour d’expérience à l’issue du cours OACI « Sûreté du fret et de la poste aérienne », organisé du 13 au 17 juillet 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => 'KGjVg-q7KW4', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '2:00',
			'auteur' => 'M. Rakoto Razafindrabe Dimby', 'fonction' => 'Responsable Sûreté Ground Handling – Passage',
			'org' => '', 'pays' => 'Madagascar',
			'desc' => 'Témoignage recueilli à l’issue de la formation AVSEC OACI « Instructeurs en sûreté de l’aviation civile », organisée du 22 au 30 juin 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => 'JLFyxz9kLCw', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '1:45',
			'auteur' => 'M. Delinot Rakotonjatovo', 'fonction' => 'Responsable Sûreté',
			'org' => 'Trans Air MTA', 'pays' => 'Madagascar',
			'desc' => 'Témoignage recueilli à l’issue de la formation AVSEC OACI « Instructeurs en sûreté de l’aviation civile », tenue du 22 au 30 juin 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => 'tpLc92xIVZo', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '2:29',
			'auteur' => 'M. Abdelhadmi Aouadi', 'fonction' => 'Instructeur en sûreté de l’aviation civile',
			'org' => '', 'pays' => 'France',
			'desc' => 'Retour d’expérience à l’issue de la formation AVSEC OACI « Instructeurs en sûreté de l’aviation civile », organisée du 22 au 30 juin 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => '1Ru9Nl8psPM', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '1:54',
			'auteur' => 'Mme Yasmine Laouar H.', 'fonction' => 'CEO',
			'org' => 'Onboarding Group', 'pays' => 'France',
			'desc' => 'Retour d’expérience à l’issue de la session OACI TRAINAIR PLUS « Training Instructors Course » (TIC FR), organisée du 18 au 22 mai 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => 'BuYFaMTsEUQ', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '2:16',
			'auteur' => 'M. Ousmane Seck', 'fonction' => 'Superviseur Sécurité',
			'org' => 'Aéroport International Blaise Diagne de Dakar', 'pays' => 'Sénégal',
			'desc' => 'Retour d’expérience à l’issue de la session OACI TRAINAIR PLUS « Training Instructors Course » (TIC FR), organisée du 18 au 22 mai 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => 'Tc8yMehlyFE', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '1:36',
			'auteur' => 'M. Ghassen Zallama', 'fonction' => 'Officier Pilote de Ligne',
			'org' => 'Tunisair Express', 'pays' => 'Tunisie',
			'desc' => 'Retour d’expérience à l’issue de la session OACI TRAINAIR PLUS « Formation des instructeurs » (TIC FR), tenue du 18 au 22 mai 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => 'VdO24Xt9JaM', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '1:11',
			'auteur' => 'M. Mohamed Walid Rafrafi', 'fonction' => 'Instructeur & CEO',
			'org' => 'Megara Voyage Tunis', 'pays' => 'Tunisie',
			'desc' => 'Retour d’expérience à l’issue de la session OACI TRAINAIR PLUS « Formation des instructeurs » (TIC FR), organisée du 18 au 22 mai 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => 'Y-4VTOwpZyA', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '1:36',
			'auteur' => 'M. Adel Mehannech', 'fonction' => 'Directeur Sûreté',
			'org' => 'Tassili Travail Aérien', 'pays' => 'Algérie',
			'desc' => 'Retour d’expérience à l’issue de la session OACI AVSEC Managers (FR), organisée du 11 au 19 mai 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => 'jhJviOMh2kQ', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '1:56',
			'auteur' => 'M. N’Da Kouakou Céleste Yao', 'fonction' => 'Chef du Service Contrôle de la Qualité, Suivi et Évaluation de la Conformité',
			'org' => 'ANAC Côte d’Ivoire', 'pays' => 'Côte d’Ivoire',
			'desc' => 'Retour d’expérience à l’issue de la session OACI AVSEC Managers (FR), tenue du 11 au 19 mai 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => 'g7FU58XOgqw', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '0:58',
			'auteur' => 'M. Balla Ahmedou Balla', 'fonction' => '',
			'org' => 'Aéroport International de Nouakchott', 'pays' => 'Mauritanie',
			'desc' => 'Retour d’expérience à l’issue de la formation « Mise en œuvre et suivi des mesures de sûreté de l’aviation civile dans les aéroports », organisée du 27 avril au 5 mai 2026.',
		),
		array(
			'yt' => '17l1huUbfV0', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '1:39',
			'auteur' => 'M. Mohamed Lemine El Boukhari', 'fonction' => 'Chef du Service Réglementation et Formation',
			'org' => 'ANAC Mauritanie', 'pays' => 'Mauritanie',
			'desc' => 'Retour d’expérience à l’issue de la session OACI AVSEC Managers (FR), tenue du 23 au 31 mars 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => 'MhL9sV4ri4g', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '1:26',
			'auteur' => 'M. Ghaielene Messaedi', 'fonction' => 'Chef de Cabine',
			'org' => 'Tunisair', 'pays' => 'Tunisie',
			'desc' => 'Retour d’expérience à l’issue de la session OACI AVSEC Managers (FR), tenue du 23 au 31 mars 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => 'RhgZ25196Ng', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '3:13',
			'auteur' => 'M. Jean Marc Yombe', 'fonction' => 'Commandant d’Aéroport',
			'org' => 'ANAC Congo Brazzaville', 'pays' => 'Congo',
			'desc' => 'Retour d’expérience à l’issue de la session AVSEC OACI « Inspecteurs en sûreté de l’aviation civile », tenue du 5 au 13 mars 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => 'zctqLG609xM', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '3:04',
			'auteur' => 'M. Basaroudine Mougammadou', 'fonction' => 'Instructeur certifié DGAC France',
			'org' => 'DGAC France', 'pays' => 'France',
			'desc' => 'Retour d’expérience à l’issue de la session AVSEC OACI « Instructeurs en sûreté de l’aviation civile », organisée du 23 février au 3 mars 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => 'yCCg5tWfVcU', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '3:23',
			'auteur' => 'M. Ishak Boukerroum', 'fonction' => 'Chef de département Certification Sûreté de l’Aviation Civile',
			'org' => 'ANAC Algérie', 'pays' => 'Algérie',
			'desc' => 'Retour d’expérience à l’issue de la session AVSEC OACI « Inspecteurs en sûreté de l’aviation civile », organisée du 5 au 13 février 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => 'EC66heTok7s', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '2:10',
			'auteur' => 'M. Sambatra Rakotoarisoa', 'fonction' => 'Responsable Planification & SGS',
			'org' => 'Ravinala Airports', 'pays' => 'Madagascar',
			'desc' => 'Retour d’expérience à l’issue de la session AVSEC OACI « Inspecteurs en sûreté de l’aviation civile », tenue du 5 au 13 février 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => 'tc9u54UV6bE', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '2:34',
			'auteur' => 'M. Makram Megdiche', 'fonction' => 'Directeur & Instructeur certifié DGAC France',
			'org' => 'Groupe 2M', 'pays' => 'Tunisie',
			'desc' => 'Retour d’expérience à l’issue de la session AVSEC OACI « Instructeurs en sûreté de l’aviation civile », organisée du 23 février au 3 mars 2026 à l’ICAO ASTC Tunis.',
		),
		array(
			'yt' => 'Wmp4pcZm86I', 'lang' => 'fr', 'badge' => 'FR', 'duree' => '1:48',
			'auteur' => 'Mme Noro Herimanitra Andrianalivola', 'fonction' => 'Responsable Accès et Sûreté',
			'org' => 'Ravinala Airports', 'pays' => 'Madagascar',
			'desc' => 'Retour d’expérience à l’issue de la formation OACI AVSEC « Inspecteurs en sûreté de l’aviation civile », organisée du 5 au 13 mars 2026 à l’ICAO ASTC Tunis.',
		),

		// ---------------------------------------------------------------- EN.
		array(
			'yt' => 'iK7q-EfGtnc', 'lang' => 'en', 'badge' => 'EN', 'duree' => '1:41',
			'auteur' => 'Ms Lillian Naa Lamiley Lamptey', 'fonction' => 'AVSEC Quality Control Inspector',
			'org' => 'Ghana Civil Aviation Authority (GCAA)', 'pays' => 'Ghana',
			'desc' => 'Feedback shared after attending the ICAO Aviation Security Inspectors Course, hosted at ICAO ASTC Tunis from 2 to 10 July 2026.',
		),
		array(
			'yt' => 'Nw_2IZ69oEM', 'lang' => 'en', 'badge' => 'EN', 'duree' => '1:40',
			'auteur' => 'Mr Francis Oppong Nyarko', 'fonction' => 'AVSEC Quality Control Inspector',
			'org' => 'Ghana Civil Aviation Authority (GCAA)', 'pays' => 'Ghana',
			'desc' => 'Feedback shared after attending the ICAO Aviation Security Inspectors Course, hosted at ICAO ASTC Tunis from 2 to 10 July 2026.',
		),
		array(
			'yt' => 'EQhIPhOAhvQ', 'lang' => 'en', 'badge' => 'EN', 'duree' => '2:33',
			'auteur' => 'Mr Brian Akibawe', 'fonction' => 'Senior AVSEC Inspector',
			'org' => 'Uganda Civil Aviation Authority (UCAA)', 'pays' => 'Uganda',
			'desc' => 'Feedback shared after attending the ICAO National Civil Aviation Security Quality Control Programme (NCASQCP) Workshop, hosted at ICAO ASTC Tunis from 8 to 12 June 2026.',
		),
		array(
			'yt' => 'ShC-ekvKNyQ', 'lang' => 'en', 'badge' => 'EN', 'duree' => '2:28',
			'auteur' => 'Mr Togar Weyea', 'fonction' => 'AVSEC / Facilitation Inspector, Head of Evaluation',
			'org' => 'Liberia Civil Aviation Authority (LCAA)', 'pays' => 'Liberia',
			'desc' => 'Feedback shared after attending the ICAO Aviation Security Managers Course, hosted at ICAO ASTC Tunis from 11 to 19 May 2026.',
		),
		array(
			'yt' => 'jRBA-ntW5b8', 'lang' => 'en', 'badge' => 'EN', 'duree' => '2:16',
			'auteur' => 'Mr Kutubu Francis', 'fonction' => 'AVSEC Inspector',
			'org' => 'Sierra Leone Civil Aviation Authority (SLCAA)', 'pays' => 'Sierra Leone',
			'desc' => 'Feedback shared after attending the ICAO Aviation Security Managers Course, hosted at ICAO ASTC Tunis from 11 to 19 May 2026.',
		),
		array(
			'yt' => '_xMsREkiohc', 'lang' => 'en', 'badge' => 'EN', 'duree' => '1:56',
			'auteur' => 'Mr Khaled Alsawafi', 'fonction' => 'AVSEC Inspector',
			'org' => 'Civil Aviation Authority of Oman', 'pays' => 'Oman',
			'desc' => 'Feedback shared after attending the ICAO In-flight Security Measures course, hosted at ICAO ASTC Tunis from 20 to 24 April 2026.',
		),
		array(
			'yt' => 'M6peHUn1tp8', 'lang' => 'en', 'badge' => 'EN', 'duree' => '1:28',
			'auteur' => 'Mr Fahn F. Borbor', 'fonction' => 'AVSEC Inspector',
			'org' => 'Liberia Civil Aviation Authority (LCAA)', 'pays' => 'Liberia',
			'desc' => 'Feedback shared after attending the ICAO Annex 9 – Facilitation course, hosted at ICAO ASTC Tunis from 20 to 24 April 2026.',
		),
	);
}

/**
 * Les références de l'AFSAC — source : brochure client « Nos Références »
 * (logos extraits du PDF, rangés dans assets/references/<logo>.png) complétée
 * par les institutions citées dans les témoignages vidéo (sans logo fourni).
 *
 * Colonnes : `fr` / `en` = intitulé par langue (identique quand c'est un nom
 * propre) ; `type` = slug de afsac_get_reference_types() ; `niveau` = slug de
 * afsac_get_reference_levels() ; `logo` = nom de fichier (sans .png) dans
 * assets/references, chaîne vide si le client n'a pas fourni de logo.
 *
 * `pays` VOLONTAIREMENT VIDE quand la brochure ne permet pas d'attribuer
 * l'institution à un pays avec certitude : mieux vaut un champ vide qu'une
 * attribution inventée. Le client complète en deux clics au besoin.
 *
 * @return array<int,array<string,string>>
 */
function afsac_references_data() {
	return array(
		// ------------------------------------------------ Au niveau national.
		array( 'key' => 'min-justice',           'fr' => 'Ministère de la Justice',                                    'en' => 'Ministry of Justice',                                       'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'autorite',  'niveau' => 'national', 'logo' => 'tn-embleme' ),
		array( 'key' => 'min-interieur',         'fr' => 'Ministère de l’Intérieur',                                   'en' => 'Ministry of the Interior',                                  'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'autorite',  'niveau' => 'national', 'logo' => 'tn-embleme' ),
		array( 'key' => 'dgac-tunisie',          'fr' => 'Direction générale de l’aviation civile',                    'en' => 'Directorate General of Civil Aviation',                     'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'autorite',  'niveau' => 'national', 'logo' => 'tn-embleme' ),
		array( 'key' => 'douane-tunisienne',     'fr' => 'Douane Tunisienne — Ministère des Finances',                 'en' => 'Tunisian Customs — Ministry of Finance',                    'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'autorite',  'niveau' => 'national', 'logo' => 'douane-tunisienne' ),
		array( 'key' => 'min-justice-prisons',   'fr' => 'Ministère de la Justice — Prisons et autorités correctionnelles', 'en' => 'Ministry of Justice — Prisons and Correctional Authorities', 'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'autorite',  'niveau' => 'national', 'logo' => 'min-justice-prisons' ),
		array( 'key' => 'armee-air-tn',          'fr' => 'Ministère de la Défense Nationale — Armée de l’air',         'en' => 'Ministry of National Defence — Air Force',                  'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'autorite',  'niveau' => 'national', 'logo' => 'armee-air-tn' ),
		array( 'key' => 'usgn',                  'fr' => 'Ministère de l’Intérieur — Unité Spéciale Garde Nationale',  'en' => 'Ministry of the Interior — National Guard Special Unit',    'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'autorite',  'niveau' => 'national', 'logo' => 'usgn' ),
		array( 'key' => 'unite-speciale-tn',     'fr' => 'Ministère de l’Intérieur — Unité Spéciale',                  'en' => 'Ministry of the Interior — Special Unit',                   'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'autorite',  'niveau' => 'national', 'logo' => 'unite-speciale-tn' ),
		array( 'key' => 'oaca',                  'fr' => 'OACA — Office de l’Aviation Civile et des Aéroports',        'en' => 'OACA — Civil Aviation and Airports Authority',              'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'aeroport',  'niveau' => 'national', 'logo' => 'oaca' ),
		array( 'key' => 'tunisair',              'fr' => 'Tunisair',                                                   'en' => 'Tunisair',                                                  'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'compagnie', 'niveau' => 'national', 'logo' => 'tunisair' ),
		array( 'key' => 'tunisair-express',      'fr' => 'Tunisair Express',                                           'en' => 'Tunisair Express',                                          'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'compagnie', 'niveau' => 'national', 'logo' => 'tunisair-express' ),
		array( 'key' => 'tunisair-handling',     'fr' => 'Tunisair Handling',                                          'en' => 'Tunisair Handling',                                         'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'compagnie', 'niveau' => 'national', 'logo' => 'tunisair-handling' ),
		array( 'key' => 'tunisavia',             'fr' => 'Tunisavia',                                                  'en' => 'Tunisavia',                                                 'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'compagnie', 'niveau' => 'national', 'logo' => 'tunisavia' ),
		array( 'key' => 'nouvelair',             'fr' => 'Nouvelair',                                                  'en' => 'Nouvelair',                                                 'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'compagnie', 'niveau' => 'national', 'logo' => 'nouvelair' ),
		array( 'key' => 'newrest',               'fr' => 'Newrest',                                                    'en' => 'Newrest',                                                   'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'compagnie', 'niveau' => 'national', 'logo' => 'newrest' ),
		array( 'key' => 'tunisie-catering',      'fr' => 'Tunisie Catering',                                           'en' => 'Tunisie Catering',                                          'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'compagnie', 'niveau' => 'national', 'logo' => 'tunisie-catering' ),
		array( 'key' => 'agil',                  'fr' => 'AGIL',                                                       'en' => 'AGIL',                                                      'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'compagnie', 'niveau' => 'national', 'logo' => 'agil' ),
		array( 'key' => 'stars-airlines',        'fr' => 'STARS Airlines Services',                                    'en' => 'STARS Airlines Services',                                   'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'compagnie', 'niveau' => 'national', 'logo' => 'stars-airlines' ),
		array( 'key' => 'hamila-duty-free',      'fr' => 'Hamila Duty Free',                                           'en' => 'Hamila Duty Free',                                          'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'compagnie', 'niveau' => 'national', 'logo' => 'hamila-duty-free' ),
		array( 'key' => 'carthage-sky-services', 'fr' => 'Carthage Sky Services',                                      'en' => 'Carthage Sky Services',                                     'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'compagnie', 'niveau' => 'national', 'logo' => 'carthage-sky-services' ),
		array( 'key' => 'tav-airports',          'fr' => 'TAV Airports',                                               'en' => 'TAV Airports',                                              'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'aeroport',  'niveau' => 'national', 'logo' => 'tav-airports' ),
		array( 'key' => 'kars-international',    'fr' => 'KARS International',                                         'en' => 'KARS International',                                        'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'compagnie', 'niveau' => 'national', 'logo' => 'kars-international' ),
		array( 'key' => 'atacs',                 'fr' => 'ATACS',                                                      'en' => 'ATACS',                                                     'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'compagnie', 'niveau' => 'national', 'logo' => 'atacs' ),
		array( 'key' => 'sabena-technics',       'fr' => 'Sabena technics',                                            'en' => 'Sabena technics',                                           'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'compagnie', 'niveau' => 'national', 'logo' => 'sabena-technics' ),
		array( 'key' => 'megara-voyage',         'fr' => 'Megara Voyage Tunis',                                        'en' => 'Megara Voyage Tunis',                                       'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'compagnie', 'niveau' => 'national', 'logo' => '' ),
		array( 'key' => 'groupe-2m',             'fr' => 'Groupe 2M',                                                  'en' => 'Groupe 2M',                                                 'pays_fr' => 'Tunisie', 'pays_en' => 'Tunisia', 'type' => 'compagnie', 'niveau' => 'national', 'logo' => '' ),

		// ------------------------------------------------ Au niveau régional.
		array( 'key' => 'aviation-civile-madagascar', 'fr' => 'Aviation Civile Madagascar',                                        'en' => 'Aviation Civile Madagascar',                                       'pays_fr' => 'Madagascar',    'pays_en' => 'Madagascar',    'type' => 'autorite',  'niveau' => 'regional', 'logo' => 'aviation-civile-madagascar' ),
		array( 'key' => 'onsfag',                     'fr' => 'ONSFAG — Office National de Sûreté et de Facilitation des Aéroports du Gabon', 'en' => 'ONSFAG — National Airport Security and Facilitation Office of Gabon', 'pays_fr' => 'Gabon',         'pays_en' => 'Gabon',         'type' => 'aeroport',  'niveau' => 'regional', 'logo' => 'onsfag' ),
		array( 'key' => 'libyan-caa',                 'fr' => 'Libyan Civil Aviation Authority',                                   'en' => 'Libyan Civil Aviation Authority',                                  'pays_fr' => 'Libye',         'pays_en' => 'Libya',         'type' => 'autorite',  'niveau' => 'regional', 'logo' => 'libyan-caa' ),
		array( 'key' => 'libyan-airports',            'fr' => 'Libyan Airports Authority',                                         'en' => 'Libyan Airports Authority',                                        'pays_fr' => 'Libye',         'pays_en' => 'Libya',         'type' => 'aeroport',  'niveau' => 'regional', 'logo' => 'libyan-airports-authority' ),
		array( 'key' => 'salt-togo',                  'fr' => 'S.A.L.T. — Société Aéroportuaire de Lomé-Tokoin',                   'en' => 'S.A.L.T. — Lomé-Tokoin Airport Company',                           'pays_fr' => 'Togo',          'pays_en' => 'Togo',          'type' => 'aeroport',  'niveau' => 'regional', 'logo' => 'salt-togo' ),
		array( 'key' => 'tassili-airlines',           'fr' => 'Tassili Airlines',                                                  'en' => 'Tassili Airlines',                                                 'pays_fr' => 'Algérie',       'pays_en' => 'Algeria',       'type' => 'compagnie', 'niveau' => 'regional', 'logo' => 'tassili-airlines' ),
		array( 'key' => 'sgsp-scorpion',              'fr' => 'SGSP Scorpion',                                                     'en' => 'SGSP Scorpion',                                                    'pays_fr' => '',              'pays_en' => '',              'type' => 'compagnie', 'niveau' => 'regional', 'logo' => 'sgsp-scorpion' ),
		array( 'key' => 'anac-tchad',                 'fr' => 'Agence Nationale de l’Aviation Civile du Tchad',                    'en' => 'National Civil Aviation Agency of Chad',                           'pays_fr' => 'Tchad',         'pays_en' => 'Chad',          'type' => 'autorite',  'niveau' => 'regional', 'logo' => 'anac-tchad' ),
		array( 'key' => 'aac-rdc',                    'fr' => 'AAC — Autorité de l’Aviation Civile',                               'en' => 'AAC — Civil Aviation Authority',                                   'pays_fr' => 'RD Congo',      'pays_en' => 'DR Congo',      'type' => 'autorite',  'niveau' => 'regional', 'logo' => 'aac-rdc' ),
		array( 'key' => 'anac-niger',                 'fr' => 'ANAC Niger',                                                        'en' => 'ANAC Niger',                                                       'pays_fr' => 'Niger',         'pays_en' => 'Niger',         'type' => 'autorite',  'niveau' => 'regional', 'logo' => 'anac-niger' ),
		array( 'key' => 'anacm',                      'fr' => 'ANACM — Agence Nationale de l’Aviation Civile et de la Météorologie', 'en' => 'ANACM — National Civil Aviation and Meteorology Agency',          'pays_fr' => '',              'pays_en' => '',              'type' => 'autorite',  'niveau' => 'regional', 'logo' => 'anacm' ),
		array( 'key' => 'asecna',                     'fr' => 'ASECNA',                                                            'en' => 'ASECNA',                                                           'pays_fr' => 'Multilatéral',  'pays_en' => 'Multilateral',  'type' => 'aeroport',  'niveau' => 'regional', 'logo' => 'asecna' ),
		array( 'key' => 'anac-gabon',                 'fr' => 'ANAC Gabon',                                                        'en' => 'ANAC Gabon',                                                       'pays_fr' => 'Gabon',         'pays_en' => 'Gabon',         'type' => 'autorite',  'niveau' => 'regional', 'logo' => 'anac-gabon' ),
		array( 'key' => 'anacim-senegal',             'fr' => 'ANACIM — Agence Nationale de l’Aviation Civile et de la Météorologie', 'en' => 'ANACIM — National Civil Aviation and Meteorology Agency',         'pays_fr' => 'Sénégal',       'pays_en' => 'Senegal',       'type' => 'autorite',  'niveau' => 'regional', 'logo' => 'anacim-senegal' ),
		array( 'key' => 'sogeac-conakry',             'fr' => 'SOGEAC — Aéroport de Conakry',                                      'en' => 'SOGEAC — Conakry Airport',                                         'pays_fr' => 'Guinée',        'pays_en' => 'Guinea',        'type' => 'aeroport',  'niveau' => 'regional', 'logo' => 'sogeac-conakry' ),
		array( 'key' => 'mellitah-oil-gas',           'fr' => 'Mellitah Oil & Gas B.V. — Libya Branch',                            'en' => 'Mellitah Oil & Gas B.V. — Libya Branch',                           'pays_fr' => 'Libye',         'pays_en' => 'Libya',         'type' => 'compagnie', 'niveau' => 'regional', 'logo' => 'mellitah-oil-gas' ),
		array( 'key' => 'afriqiyah-airways',          'fr' => 'Afriqiyah Airways',                                                 'en' => 'Afriqiyah Airways',                                                'pays_fr' => 'Libye',         'pays_en' => 'Libya',         'type' => 'compagnie', 'niveau' => 'regional', 'logo' => 'afriqiyah-airways' ),
		array( 'key' => 'aeria-abidjan',              'fr' => 'AERIA — Aéroport International Félix Houphouët-Boigny d’Abidjan',   'en' => 'AERIA — Félix Houphouët-Boigny International Airport, Abidjan',    'pays_fr' => 'Côte d’Ivoire', 'pays_en' => 'Côte d’Ivoire', 'type' => 'aeroport',  'niveau' => 'regional', 'logo' => 'aeria-abidjan' ),
		array( 'key' => 'camair-co',                  'fr' => 'Camair-Co',                                                         'en' => 'Camair-Co',                                                        'pays_fr' => 'Cameroun',      'pays_en' => 'Cameroon',      'type' => 'compagnie', 'niveau' => 'regional', 'logo' => 'camair-co' ),
		array( 'key' => 'air-cote-divoire',           'fr' => 'Air Côte d’Ivoire',                                                 'en' => 'Air Côte d’Ivoire',                                                'pays_fr' => 'Côte d’Ivoire', 'pays_en' => 'Côte d’Ivoire', 'type' => 'compagnie', 'niveau' => 'regional', 'logo' => 'air-cote-divoire' ),
		array( 'key' => 'ecair',                      'fr' => 'ECAir — Equatorial Congo Airlines',                                 'en' => 'ECAir — Equatorial Congo Airlines',                                'pays_fr' => 'Congo',         'pays_en' => 'Congo',         'type' => 'compagnie', 'niveau' => 'regional', 'logo' => 'ecair' ),
		array( 'key' => 'libyan-wings',               'fr' => 'Libyan Wings',                                                      'en' => 'Libyan Wings',                                                     'pays_fr' => 'Libye',         'pays_en' => 'Libya',         'type' => 'compagnie', 'niveau' => 'regional', 'logo' => 'libyan-wings' ),
		// Institutions citées par les témoignages vidéo — logo non fourni à ce jour.
		array( 'key' => 'adac-tchad',        'fr' => 'ADAC Tchad',                                    'en' => 'ADAC Chad',                                     'pays_fr' => 'Tchad',         'pays_en' => 'Chad',          'type' => 'autorite',  'niveau' => 'regional', 'logo' => '' ),
		array( 'key' => 'anac-algerie',      'fr' => 'ANAC Algérie',                                  'en' => 'ANAC Algeria',                                  'pays_fr' => 'Algérie',       'pays_en' => 'Algeria',       'type' => 'autorite',  'niveau' => 'regional', 'logo' => '' ),
		array( 'key' => 'anac-congo',        'fr' => 'ANAC Congo Brazzaville',                        'en' => 'ANAC Congo Brazzaville',                        'pays_fr' => 'Congo',         'pays_en' => 'Congo',         'type' => 'autorite',  'niveau' => 'regional', 'logo' => '' ),
		array( 'key' => 'anac-civ',          'fr' => 'ANAC Côte d’Ivoire',                            'en' => 'ANAC Côte d’Ivoire',                            'pays_fr' => 'Côte d’Ivoire', 'pays_en' => 'Côte d’Ivoire', 'type' => 'autorite',  'niveau' => 'regional', 'logo' => '' ),
		array( 'key' => 'anac-mauritanie',   'fr' => 'ANAC Mauritanie',                               'en' => 'ANAC Mauritania',                               'pays_fr' => 'Mauritanie',    'pays_en' => 'Mauritania',    'type' => 'autorite',  'niveau' => 'regional', 'logo' => '' ),
		array( 'key' => 'gcaa-ghana',        'fr' => 'Ghana Civil Aviation Authority (GCAA)',         'en' => 'Ghana Civil Aviation Authority (GCAA)',         'pays_fr' => 'Ghana',         'pays_en' => 'Ghana',         'type' => 'autorite',  'niveau' => 'regional', 'logo' => '' ),
		array( 'key' => 'lcaa-liberia',      'fr' => 'Liberia Civil Aviation Authority (LCAA)',       'en' => 'Liberia Civil Aviation Authority (LCAA)',       'pays_fr' => 'Liberia',       'pays_en' => 'Liberia',       'type' => 'autorite',  'niveau' => 'regional', 'logo' => '' ),
		array( 'key' => 'slcaa-sierraleone', 'fr' => 'Sierra Leone Civil Aviation Authority (SLCAA)', 'en' => 'Sierra Leone Civil Aviation Authority (SLCAA)', 'pays_fr' => 'Sierra Leone',  'pays_en' => 'Sierra Leone',  'type' => 'autorite',  'niveau' => 'regional', 'logo' => '' ),
		array( 'key' => 'ucaa-uganda',       'fr' => 'Uganda Civil Aviation Authority (UCAA)',        'en' => 'Uganda Civil Aviation Authority (UCAA)',        'pays_fr' => 'Ouganda',       'pays_en' => 'Uganda',        'type' => 'autorite',  'niveau' => 'regional', 'logo' => '' ),
		array( 'key' => 'aibd-dakar',        'fr' => 'Aéroport International Blaise Diagne de Dakar',  'en' => 'Blaise Diagne International Airport, Dakar',    'pays_fr' => 'Sénégal',       'pays_en' => 'Senegal',       'type' => 'aeroport',  'niveau' => 'regional', 'logo' => '' ),
		array( 'key' => 'aiu-nouakchott',    'fr' => 'Aéroport International de Nouakchott',           'en' => 'Nouakchott International Airport',              'pays_fr' => 'Mauritanie',    'pays_en' => 'Mauritania',    'type' => 'aeroport',  'niveau' => 'regional', 'logo' => '' ),
		array( 'key' => 'ravinala-airports', 'fr' => 'Ravinala Airports',                             'en' => 'Ravinala Airports',                             'pays_fr' => 'Madagascar',    'pays_en' => 'Madagascar',    'type' => 'aeroport',  'niveau' => 'regional', 'logo' => '' ),
		array( 'key' => 'tassili-ta',        'fr' => 'Tassili Travail Aérien',                        'en' => 'Tassili Travail Aérien',                        'pays_fr' => 'Algérie',       'pays_en' => 'Algeria',       'type' => 'compagnie', 'niveau' => 'regional', 'logo' => '' ),
		array( 'key' => 'trans-air-mta',     'fr' => 'Trans Air MTA',                                 'en' => 'Trans Air MTA',                                 'pays_fr' => 'Madagascar',    'pays_en' => 'Madagascar',    'type' => 'compagnie', 'niveau' => 'regional', 'logo' => '' ),

		// -------------------------------------------- Au niveau international.
		array( 'key' => 'dubai-police',                'fr' => 'Dubai Police',                                'en' => 'Dubai Police',                                'pays_fr' => 'Émirats arabes unis', 'pays_en' => 'United Arab Emirates', 'type' => 'autorite',     'niveau' => 'international', 'logo' => 'dubai-police' ),
		array( 'key' => 'saudia',                      'fr' => 'SAUDIA',                                      'en' => 'SAUDIA',                                      'pays_fr' => 'Arabie Saoudite',     'pays_en' => 'Saudi Arabia',         'type' => 'compagnie',    'niveau' => 'international', 'logo' => 'saudia' ),
		array( 'key' => 'royal-oman-police',           'fr' => 'Royal Oman Police',                           'en' => 'Royal Oman Police',                           'pays_fr' => 'Oman',                'pays_en' => 'Oman',                 'type' => 'autorite',     'niveau' => 'international', 'logo' => 'royal-oman-police' ),
		array( 'key' => 'gaca-saudi',                  'fr' => 'GACA — General Authority of Civil Aviation',  'en' => 'GACA — General Authority of Civil Aviation',  'pays_fr' => 'Arabie Saoudite',     'pays_en' => 'Saudi Arabia',         'type' => 'autorite',     'niveau' => 'international', 'logo' => 'gaca-saudi' ),
		array( 'key' => 'gcaa-uae',                    'fr' => 'General Civil Aviation Authority',            'en' => 'General Civil Aviation Authority',            'pays_fr' => 'Émirats arabes unis', 'pays_en' => 'United Arab Emirates', 'type' => 'autorite',     'niveau' => 'international', 'logo' => 'gcaa-uae' ),
		array( 'key' => 'dubai-caa',                   'fr' => 'Dubai Civil Aviation Authority',              'en' => 'Dubai Civil Aviation Authority',              'pays_fr' => 'Émirats arabes unis', 'pays_en' => 'United Arab Emirates', 'type' => 'autorite',     'niveau' => 'international', 'logo' => 'dubai-caa' ),
		array( 'key' => 'civil-aviation-authority',    'fr' => 'Civil Aviation Authority',                    'en' => 'Civil Aviation Authority',                    'pays_fr' => '',                    'pays_en' => '',                     'type' => 'autorite',     'niveau' => 'international', 'logo' => 'civil-aviation-authority' ),
		array( 'key' => 'dgca',                        'fr' => 'Directorate General of Civil Aviation',       'en' => 'Directorate General of Civil Aviation',       'pays_fr' => '',                    'pays_en' => '',                     'type' => 'autorite',     'niveau' => 'international', 'logo' => 'dgca' ),
		array( 'key' => 'kuwait-airways',              'fr' => 'Kuwait Airways',                              'en' => 'Kuwait Airways',                              'pays_fr' => 'Koweït',              'pays_en' => 'Kuwait',               'type' => 'compagnie',    'niveau' => 'international', 'logo' => 'kuwait-airways' ),
		array( 'key' => 'mea',                         'fr' => 'MEA — Middle East Airlines',                  'en' => 'MEA — Middle East Airlines',                  'pays_fr' => 'Liban',               'pays_en' => 'Lebanon',              'type' => 'compagnie',    'niveau' => 'international', 'logo' => 'mea' ),
		array( 'key' => 'amarante-international',      'fr' => 'Amarante International',                      'en' => 'Amarante International',                      'pays_fr' => '',                    'pays_en' => '',                     'type' => 'compagnie',    'niveau' => 'international', 'logo' => 'amarante-international' ),
		array( 'key' => 'al-hayat-college',            'fr' => 'Al-Hayat College Company for Educational Services', 'en' => 'Al-Hayat College Company for Educational Services', 'pays_fr' => '',              'pays_en' => '',                     'type' => 'academie',     'niveau' => 'international', 'logo' => 'al-hayat-college' ),
		array( 'key' => 'college-aviation-technology', 'fr' => 'CAT — College of Aviation Technology',        'en' => 'CAT — College of Aviation Technology',        'pays_fr' => '',                    'pays_en' => '',                     'type' => 'academie',     'niveau' => 'international', 'logo' => 'college-aviation-technology' ),
		array( 'key' => 'dgac-france',                 'fr' => 'DGAC France',                                 'en' => 'DGAC France',                                 'pays_fr' => 'France',              'pays_en' => 'France',               'type' => 'autorite',     'niveau' => 'international', 'logo' => '' ),
		array( 'key' => 'caa-oman',                    'fr' => 'Civil Aviation Authority of Oman',            'en' => 'Civil Aviation Authority of Oman',            'pays_fr' => 'Oman',                'pays_en' => 'Oman',                 'type' => 'autorite',     'niveau' => 'international', 'logo' => '' ),
		array( 'key' => 'onboarding-group',            'fr' => 'Onboarding Group',                            'en' => 'Onboarding Group',                            'pays_fr' => 'France',              'pays_en' => 'France',               'type' => 'compagnie',    'niveau' => 'international', 'logo' => '' ),
	);
}

/**
 * Écrit un champ ACF (ou la méta brute si ACF est absent).
 *
 * @param int    $post_id ID du contenu.
 * @param string $key     Nom du champ.
 * @param mixed  $value   Valeur.
 * @return void
 */
function afsac_temoignage_set_field( $post_id, $key, $value ) {
	if ( function_exists( 'update_field' ) ) {
		update_field( $key, $value, $post_id );
		return;
	}
	update_post_meta( $post_id, $key, $value );
}

/**
 * Retrouve un contenu déjà importé via sa clé pivot.
 *
 * @param string $post_type Type de contenu.
 * @param string $meta_key  Nom de la méta pivot.
 * @param string $value     Valeur de la clé.
 * @param string $lang      Code langue Polylang, ou '' pour chercher dans TOUTES
 *                         les langues.
 * @return int ID trouvé, 0 sinon.
 */
function afsac_seed_find_by_key( $post_type, $meta_key, $value, $lang = '' ) {
	/*
	 * `lang` est TOUJOURS transmis, y compris vide : sans cet argument Polylang
	 * restreint silencieusement la requête à la langue courante (le français en
	 * CLI), la fiche anglaise devient introuvable et l'import « idempotent »
	 * recrée un doublon à chaque passage. `lang => ''` signifie « toutes langues ».
	 */
	$found = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'lang'           => $lang,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'meta_key'       => $meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $value, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);

	return ! empty( $found ) ? (int) $found[0] : 0;
}

/**
 * Téléverse la vignette YouTube d'une vidéo et la place en image à la une.
 *
 * Ne fait rien si le contenu a déjà une image à la une (idempotence, et respect
 * d'un visuel remplacé à la main par le client).
 *
 * @param int    $post_id ID du témoignage.
 * @param string $yt_id   Identifiant de la vidéo YouTube.
 * @param string $title   Intitulé utilisé pour le texte alternatif.
 * @return bool Vrai si une vignette a été importée.
 */
function afsac_temoignage_import_thumb( $post_id, $yt_id, $title ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return false;
	}

	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// maxresdefault n'existe pas pour toutes les vidéos : repli sur hqdefault.
	foreach ( array( 'maxresdefault', 'hqdefault' ) as $size ) {
		$url      = 'https://i.ytimg.com/vi/' . $yt_id . '/' . $size . '.jpg';
		$response = wp_remote_head( $url, array( 'timeout' => 15 ) );
		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			continue;
		}

		$attachment_id = media_sideload_image( $url, $post_id, $title, 'id' );
		if ( is_wp_error( $attachment_id ) ) {
			continue;
		}

		set_post_thumbnail( $post_id, (int) $attachment_id );
		update_post_meta( (int) $attachment_id, '_wp_attachment_image_alt', $title );

		return true;
	}

	return false;
}

/**
 * Crée / met à jour un témoignage.
 *
 * @param array $t       Entrée de afsac_temoignages_data().
 * @param array $report  Compteurs, passés par référence.
 * @return int ID du témoignage.
 */
function afsac_seed_temoignage( $t, &$report ) {
	// Recherche dans TOUTES les langues : la fiche porte la langue de la vidéo.
	$existing = afsac_seed_find_by_key( 'afsac_temoignage', '_afsac_yt_id', $t['yt'], '' );

	// Titre = « Auteur — Organisation » (ou le pays si l'organisation est inconnue).
	$suffix = '' !== $t['org'] ? $t['org'] : $t['pays'];
	$title  = $t['auteur'] . ' — ' . $suffix;

	$postarr = array(
		'post_type'    => 'afsac_temoignage',
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_content' => '<!-- wp:paragraph --><p>' . esc_html( $t['desc'] ) . '</p><!-- /wp:paragraph -->',
		'post_excerpt' => wp_trim_words( $t['desc'], 30, '…' ),
	);

	if ( $existing ) {
		$postarr['ID'] = $existing;
		$post_id       = wp_update_post( $postarr, true );
		$report['updated']++;
	} else {
		$post_id = wp_insert_post( $postarr, true );
		$report['created']++;
	}

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		$report['errors'][] = $t['yt'];
		return 0;
	}
	$post_id = (int) $post_id;

	update_post_meta( $post_id, '_afsac_yt_id', $t['yt'] );

	if ( function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $post_id, $t['lang'] );
	}

	afsac_temoignage_set_field( $post_id, 'afsac_temoignage_auteur', $t['auteur'] );
	afsac_temoignage_set_field( $post_id, 'afsac_temoignage_fonction', $t['fonction'] );
	afsac_temoignage_set_field( $post_id, 'afsac_temoignage_org', $t['org'] );
	afsac_temoignage_set_field( $post_id, 'afsac_temoignage_langue', $t['badge'] );
	afsac_temoignage_set_field( $post_id, 'afsac_temoignage_duree', $t['duree'] );
	afsac_temoignage_set_field( $post_id, 'afsac_temoignage_video_url', 'https://www.youtube.com/watch?v=' . $t['yt'] );
	// La citation reste VIDE : seul le client peut transcrire les propos exacts.

	if ( afsac_temoignage_import_thumb( $post_id, $t['yt'], $title ) ) {
		$report['thumbs']++;
	}

	return $post_id;
}

/**
 * Place le logo d'une référence (fichier livré dans assets/references) en image
 * à la une.
 *
 * Le fichier n'est téléversé QU'UNE FOIS dans la médiathèque : les imports
 * suivants — y compris la traduction anglaise de la même fiche — réutilisent la
 * pièce jointe existante, repérée par la méta `_afsac_ref_logo`. Ne remplace
 * jamais une image déjà posée (le client reste maître de ses visuels).
 *
 * @param int    $post_id ID de la référence.
 * @param string $slug    Nom du fichier (sans extension) dans assets/references.
 * @param string $title   Intitulé, utilisé en texte alternatif.
 * @return bool Vrai si une image à la une vient d'être posée.
 */
function afsac_reference_import_logo( $post_id, $slug, $title ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return false;
	}

	$source = AFSAC_CORE_PATH . 'assets/references/' . $slug . '.png';
	if ( ! file_exists( $source ) ) {
		return false;
	}

	// Pièce jointe déjà téléversée pour ce logo ?
	$known = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_afsac_ref_logo', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $slug, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);

	if ( ! empty( $known ) ) {
		set_post_thumbnail( $post_id, (int) $known[0] );
		return true;
	}

	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// Copie dans un fichier temporaire : media_handle_sideload() DÉPLACE la
	// source, on ne veut pas vider assets/references au premier import.
	$tmp = wp_tempnam( $slug . '.png' );
	if ( ! $tmp || ! copy( $source, $tmp ) ) {
		return false;
	}

	$attachment_id = media_handle_sideload(
		array(
			'name'     => 'afsac-ref-' . $slug . '.png',
			'tmp_name' => $tmp,
		),
		0,
		$title
	);

	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- nettoyage best effort.
		return false;
	}

	update_post_meta( (int) $attachment_id, '_afsac_ref_logo', $slug );
	update_post_meta( (int) $attachment_id, '_wp_attachment_image_alt', $title );
	set_post_thumbnail( $post_id, (int) $attachment_id );

	return true;
}

/**
 * Crée / met à jour une référence dans les deux langues, et les apparie.
 *
 * @param array $r      Entrée de afsac_references_data().
 * @param array $report Compteurs, passés par référence.
 * @return array<string,int> Codes langue => ID.
 */
function afsac_seed_reference( $r, &$report ) {
	$ids       = array();
	$has_pll   = function_exists( 'pll_set_post_language' ) && function_exists( 'pll_save_post_translations' );
	$languages = array( 'fr', 'en' );

	foreach ( $languages as $lang ) {
		$existing = afsac_seed_find_by_key( 'afsac_reference', '_afsac_ref_key', $r['key'], $has_pll ? $lang : '' );

		$postarr = array(
			'post_type'   => 'afsac_reference',
			'post_status' => 'publish',
			'post_title'  => $r[ $lang ],
		);

		if ( $existing ) {
			$postarr['ID'] = $existing;
			$post_id       = wp_update_post( $postarr, true );
			$report['ref_updated']++;
		} else {
			$post_id = wp_insert_post( $postarr, true );
			$report['ref_created']++;
		}

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			$report['errors'][] = $r['key'] . '/' . $lang;
			continue;
		}
		$post_id = (int) $post_id;

		update_post_meta( $post_id, '_afsac_ref_key', $r['key'] );
		if ( $has_pll ) {
			pll_set_post_language( $post_id, $lang );
		}

		afsac_temoignage_set_field( $post_id, 'afsac_reference_pays', $r[ 'pays_' . $lang ] );
		afsac_temoignage_set_field( $post_id, 'afsac_reference_type', $r['type'] );
		afsac_temoignage_set_field( $post_id, 'afsac_reference_niveau', $r['niveau'] );

		if ( '' !== $r['logo'] && afsac_reference_import_logo( $post_id, $r['logo'], $r[ $lang ] ) ) {
			$report['logos']++;
		}

		$ids[ $lang ] = $post_id;
	}

	if ( $has_pll && count( $ids ) === count( $languages ) ) {
		pll_save_post_translations( $ids );
	}

	return $ids;
}

/**
 * Lance l'import complet (témoignages + références).
 *
 * @return array Rapport d'exécution.
 */
function afsac_seed_temoignages() {
	$report = array(
		'ok'          => true,
		'created'     => 0,
		'updated'     => 0,
		'thumbs'      => 0,
		'logos'       => 0,
		'ref_created' => 0,
		'ref_updated' => 0,
		'errors'      => array(),
		'items'       => array(),
	);

	foreach ( afsac_temoignages_data() as $t ) {
		$id = afsac_seed_temoignage( $t, $report );
		if ( $id ) {
			$report['items'][] = array(
				'id'     => $id,
				'lang'   => strtoupper( $t['lang'] ),
				'auteur' => $t['auteur'],
				'org'    => '' !== $t['org'] ? $t['org'] : '—',
			);
		}
	}

	foreach ( afsac_references_data() as $r ) {
		afsac_seed_reference( $r, $report );
	}

	$report['ok'] = empty( $report['errors'] );

	$report['message'] = sprintf(
		/* translators: 1: témoignages créés, 2: mis à jour, 3: vignettes importées, 4: références créées, 5: références mises à jour, 6: logos posés. */
		__( 'Témoignages : %1$d créés, %2$d mis à jour, %3$d vignettes importées. Références : %4$d créées, %5$d mises à jour, %6$d logos posés.', 'afsac' ),
		$report['created'],
		$report['updated'],
		$report['thumbs'],
		$report['ref_created'],
		$report['ref_updated'],
		$report['logos']
	);

	if ( ! empty( $report['errors'] ) ) {
		$report['message'] .= ' ' . sprintf(
			/* translators: %s : liste des entrées en échec. */
			__( 'Échecs : %s.', 'afsac' ),
			implode( ', ', $report['errors'] )
		);
	}

	return $report;
}

/**
 * Page d'admin sous « Outils » pour lancer l'import.
 *
 * @return void
 */
function afsac_temoignages_admin_menu() {
	add_management_page(
		__( 'Import témoignages vidéo', 'afsac' ),
		__( 'Import témoignages vidéo', 'afsac' ),
		'manage_options',
		'afsac-import-temoignages',
		'afsac_temoignages_admin_page'
	);
}
add_action( 'admin_menu', 'afsac_temoignages_admin_menu' );

/**
 * Rendu de la page d'admin (et exécution sur soumission).
 *
 * @return void
 */
function afsac_temoignages_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$report = null;
	if ( isset( $_POST['afsac_temoignages_run'] ) && check_admin_referer( 'afsac_import_temoignages' ) ) {
		$report = afsac_seed_temoignages();
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Import des témoignages vidéo', 'afsac' ) . '</h1>';
	echo '<p>' . esc_html__( 'Importe les retours d’expérience publiés sur la chaîne YouTube de l’AFSAC (vidéo, vignette, auteur, organisation, langue) ainsi que les institutions citées, dans « Références ». Idempotent : relancer met à jour sans dupliquer, et ne remplace jamais une image à la une posée à la main.', 'afsac' ) . '</p>';

	if ( is_array( $report ) ) {
		$class = $report['ok'] ? 'notice-success' : 'notice-error';
		echo '<div class="notice ' . esc_attr( $class ) . '"><p>' . esc_html( $report['message'] ) . '</p></div>';

		if ( ! empty( $report['items'] ) ) {
			echo '<table class="widefat striped"><thead><tr><th>#</th><th>' . esc_html__( 'Langue', 'afsac' ) . '</th><th>' . esc_html__( 'Auteur', 'afsac' ) . '</th><th>' . esc_html__( 'Organisation', 'afsac' ) . '</th></tr></thead><tbody>';
			foreach ( $report['items'] as $item ) {
				echo '<tr><td>' . esc_html( $item['id'] ) . '</td><td>' . esc_html( $item['lang'] ) . '</td><td>' . esc_html( $item['auteur'] ) . '</td><td>' . esc_html( $item['org'] ) . '</td></tr>';
			}
			echo '</tbody></table>';
		}
	}

	echo '<form method="post" style="margin-top:1em;">';
	wp_nonce_field( 'afsac_import_temoignages' );
	echo '<p><button type="submit" name="afsac_temoignages_run" value="1" class="button button-primary">' . esc_html__( 'Lancer l’import', 'afsac' ) . '</button></p>';
	echo '</form>';
	echo '</div>';
}
