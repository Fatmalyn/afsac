<?php
/**
 * Seed des formations AVSEC (CPT afsac_formation + Polylang + ACF).
 *
 * Importe les fiches descriptives AVSEC du client (dossier ZIP fourni) sous
 * forme de formations structurées, en FR et EN, appariées par Polylang. Le
 * contenu (but, objectifs, modules, public cible, prérequis, durée…) est
 * extrait des fiches.
 *
 * Le PDF d'origine, lui, est proposé au téléchargement sur la fiche : il vit
 * dans la médiathèque, rattaché au post par le champ ACF `afsac_fiche_pdf`
 * (un par langue). Ce seed ne le touche pas — il ne gère que le contenu.
 *
 * PÉRIMÈTRE (arrêté avec le client le 03/09/2026) : ce dataset est la liste
 * EXHAUSTIVE des cours AVSEC du centre, celle des deux brochures « Programme
 * des Formations AVSEC/OACI 2026 » (FR) et « Aviation Security Annual Training
 * Program 2026 » (EN) — programme annuel + formations à la demande. Les cours
 * du catalogue mondial OACI classés en sûreté n'y ont PAS leur place : ils ont
 * été supprimés de la famille AVSEC à cette date.
 *
 * Rattachement systématique : famille = AVSEC, domaine (area) = Sûreté de
 * l'aviation / Aviation Security, langue = Français (post FR) / English (post
 * EN). Les termes sont ceux de seed-terms.php (créés/reliés au besoin).
 *
 * Idempotent : marqueurs `_afsac_import` + `_afsac_import_key` → ré-exécuter met
 * à jour au lieu de dupliquer.
 *
 * Exécution :
 *   - WP-CLI ......... `wp afsac seed-formations`
 *   - Repli (admin) .. Outils → « Import fiches AVSEC » (si WP-CLI indisponible).
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jeu de données des formations AVSEC (paires FR/EN ; 2 cours EN seulement).
 *
 * Chaque cours : clé stable + drapeau `certificat` + un bloc par langue
 * disponible (`fr`, `en`) contenant title / duree / goal / objectifs[] /
 * modules[] / public / prerequis[].
 *
 * Les modules sont des INTITULÉS NUS (le gabarit numérote Module 0, 1, 2…).
 *
 * @return array<int,array<string,mixed>>
 */
function afsac_import_courses() {
	return array(

		// 1 — Sûreté du Fret et de la Poste / Air Cargo and Mail Security.
		array(
			'key'        => 'avsec-fret-poste',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Sûreté du fret et de la poste',
				'duree'     => '5 jours',
				'frais'     => 1000,
				'goal'      => 'Permettre au personnel concerné de comprendre l’origine et le but des mesures et procédures de sûreté nécessaires à la protection du fret, du courrier et de la poste contre les actes d’intervention illicite, et d’appliquer les contrôles de sûreté appropriés aux expéditions, conformément à l’Annexe 17 et au Manuel de sûreté de l’aviation de l’OACI (Doc 8973).',
				'objectifs' => array(
					'Comprendre pleinement l’origine et le but des mesures et des procédures de sûreté nécessaires à la protection du fret, du courrier et des colis express de la poste et des provisions de bord.',
					'Comprendre la nature de la menace que constituent pour l’aviation les explosifs et autres matières dangereuses.',
					'Appliquer les mesures de contrôles de sûreté appropriées aux expéditions de fret.',
					'Assurer l’inspection, le filtrage ou la fouille des expéditions conformément aux principes de sûreté prescrits.',
					'Veiller à ce que tous les services et installations, les véhicules, les conteneurs et les équipements associés au fret fassent l’objet de mesures de contrôles de sûreté.',
					'Prendre les mesures appropriées en réponse à une situation d’urgence de sûreté relative à des marchandises suspectes.',
				),
				'modules'   => array(
					'Contexte de la sûreté du fret',
					'Concepts de la sûreté du fret',
					'Procédures de la sûreté du fret',
					'Gestion de la sûreté du fret',
					'Méthodes de filtrage du fret',
					'Autres contrôles de sûreté',
					'Sûreté de la poste',
					'Activités de clôture',
				),
				'public'    => 'La formation est donnée en totalité ou en partie aux employés chargés de la réception, de l’enregistrement et de la manutention du fret, du courrier et des colis express, de la poste et des provisions de bord, afin qu’ils puissent appliquer les mesures préventives de sûreté appropriées, en conformité aux programmes approuvés de sûreté de l’aviation.',
				'prerequis' => array(
					'Maîtriser l’expression écrite et orale de la langue d’enseignement du stage.',
					'Assurer, au sein d’organisations correspondant à la population cible, des fonctions de réception, d’enregistrement ou de manutention de fret.',
				),
			),
			'en'         => array(
				'title'     => 'Air Cargo and Mail Security Course',
				'duree'     => '5 days',
				'goal'      => 'To enable the target population to integrate knowledge of ICAO standards and recommended practices (SARPs) into the context of their designated role in their organization as part of a national responsibility to secure air cargo and mail against acts of unlawful interference, in accordance with Annex 17 and the ICAO Aviation Security Manual (Doc 8973 – Restricted).',
				'objectifs' => array(
					'Explain the origin and purpose of required security measures and procedures at the international and national level to protect air cargo and mail from unauthorized interference.',
					'Describe the roles and responsibilities of the appropriate national authority and other stakeholders to meet ICAO requirements for air cargo and mail security.',
					'Explain the purpose and key components of the air cargo secure supply chain for both cargo and mail.',
					'Describe the purpose and procedures relevant to each of the 6 pillars of the air cargo secure supply chain.',
					'Identify the need for additional specific processes to handle mail, dangerous goods, and restricted articles, where applicable.',
				),
				'modules'   => array(
					'Course Introduction',
					'Cargo and Mail Security in Context',
					'The Secure Supply Chain',
					'Facility and Personnel Security and Training',
					'Screening',
					'Chain of Custody',
					'Oversight and Compliance',
					'Security of Mail',
					'Restricted Articles: Prohibited Items and Dangerous Goods',
					'Course Closing',
				),
				'public'    => 'AVSEC personnel responsible for the development, implementation and oversight of air cargo and mail security measures, including civil aviation authority personnel, other regulatory authorities, airports, airlines and other stakeholders, as well as national AVSEC instructors responsible for training on similar subject matter.',
				'prerequis' => array(
					'Participants should work for the State’s appropriate authority responsible for aviation security, other regulatory authorities, airports, airlines, and/or other stakeholders that have an active role in maintaining air cargo and mail security.',
					'There is no minimum level of responsibility for those meeting the target audience criteria.',
				),
			),
		),

		// 2 — Culture de la Sûreté / Security Culture.
		array(
			'key'        => 'avsec-culture-surete',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Atelier sur la Culture de Sûreté',
				'duree'     => '4 jours',
				'frais'     => 800,
				'goal'      => 'Sensibiliser le personnel des aéroports sur l’importance de l’aspect sûreté dans l’exercice de leur travail et leur permettre d’expliquer les principes, l’importance et les avantages d’une culture de la sûreté efficace, ainsi que de présenter les outils et les pratiques optimales pour développer une culture de la sûreté solide et durable.',
				'objectifs' => array(
					'Définir le contexte de l’atelier, en soulignant l’importance de la culture de la sûreté pour dissuader, détecter et prévenir les actes d’intervention illicite.',
					'Décrire les objectifs, la méthodologie et la structure de l’atelier.',
					'Souligner l’importance de la participation sous forme de contribution à des discussions actives et à des travaux de groupe.',
				),
				'modules'   => array(
					'Introduction',
					'Comprendre la culture de la sûreté',
					'Avantages d’une culture de la sûreté efficace',
					'Mise en œuvre d’une culture de la sûreté solide et efficace',
					'Exercice final : culture de la sûreté',
				),
				'public'    => 'La population cible est constituée par les nouvelles recrues et par le personnel en fonction au niveau de départ, employés par l’autorité ou l’organisation ayant la responsabilité primordiale d’appliquer les mesures préventives de sûreté de l’aviation aux aéroports, ainsi que le personnel de même niveau d’autres organismes aéronautiques chargés des activités d’appui.',
				'prerequis' => array(
					'Avoir suivi avec succès le cycle d’enseignement secondaire ou un niveau d’éducation équivalent.',
					'Maîtriser l’expression écrite et orale de la langue d’enseignement du cours.',
				),
			),
			'en'         => array(
				'title'     => 'Security Culture Workshop',
				'duree'     => '4 days',
				'frais'     => 800,
				'goal'      => 'The purpose of this workshop is to equip participants with the knowledge and tools to cultivate and maintain a robust security culture within their organizations, enhancing their ability to deter, detect, and prevent unlawful interference effectively.',
				'objectifs' => array(
					'Explain the principles, importance and benefits of an effective security culture in deterring, detecting and preventing acts of unlawful interference.',
					'Present tools and best practices for the development of a strong and sustainable security culture.',
					'Develop, implement, and maintain a robust and positive security culture within their organization.',
				),
				'modules'   => array(
					'Course Introduction',
					'Understanding Security Culture',
					'Benefits of an Effective Security Culture',
					'Implementation of a Strong and Effective Security Culture',
					'Final Exercise',
					'Workshop Closing',
				),
				'public'    => 'The target population will be new entrants and existing personnel at the basic level employed by the authority or organization primarily responsible for the application of aviation security preventive measures at airports and from such other aviation related agencies engaged in support activities.',
				'prerequis' => array(
					'Have written and oral command of the language of instruction.',
					'Be engaged within organizations mentioned in the target population.',
				),
			),
		),

		// 3 — Gestion de Crises / Crisis Management.
		array(
			'key'        => 'avsec-gestion-crises',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Gestion de crise en sûreté de l’aviation civile',
				'duree'     => '5 jours',
				'goal'      => 'Donner au personnel de direction les connaissances et les compétences nécessaires pour établir et mettre en œuvre des procédures efficaces de gestion de crises, afin de riposter aux urgences majeures de sûreté survenant dans un aéroport.',
				'objectifs' => array(
					'Expliquer la nécessité d’établir un plan de gestion de crises pour riposter aux urgences majeures de sûreté survenant à un aéroport.',
					'Décrire les éléments essentiels d’un plan de gestion de crises.',
					'Indiquer la composition et les fonctions d’une équipe de gestion de crises.',
					'Décrire les installations et les équipements essentiels requis pour une riposte planifiée.',
					'Indiquer les éléments de vérification du système nécessaires pour assurer la validité continue des plans de gestion de crises.',
				),
				'modules'   => array(
					'Contexte de la gestion de crises',
					'Concepts de la gestion de crises',
					'Plan de gestion de crises',
					'Équipe de gestion de crises',
					'Installations et équipements de gestion de crises',
				),
				'public'    => 'Cet atelier s’adresse au personnel intermédiaire et supérieur de gestion travaillant au sein d’organisations d’un État qui ont pour responsabilité, aux termes du Programme national de sûreté de l’aviation civile, de faire partie de l’instance de gestion de crises chargée de riposter aux actes d’intervention illicite survenant sur le territoire de cet État.',
				'prerequis' => array(
					'Avoir suivi avec succès le cycle d’enseignement secondaire ou un niveau d’éducation équivalent.',
					'Maîtriser l’expression écrite et orale de la langue d’enseignement du cours.',
				),
			),
			'en'         => array(
				'title'     => 'Crisis Management Workshop',
				'duree'     => '5 days',
				'goal'      => 'This ICAO workshop is developed to equip participants with the knowledge and skills necessary to develop and implement effective crisis management plans tailored to the unique challenges of aviation environments, ensuring preparedness to respond swiftly and effectively to major security emergencies at airports.',
				'objectifs' => array(
					'Explain the need for a crisis management plan to respond to major security emergencies occurring at airports.',
					'Describe the essential components of a crisis management team.',
					'Identify the composition and function of a crisis management team.',
					'Describe the essential facilities necessary in support of a planned response to crises.',
					'List the system testing features necessary to ensure currency in crisis management plans.',
					'Understand the elements necessary to effectively respond to major security emergencies in the aviation environment.',
					'Create a draft Crisis Management Plan that can be implemented in each participant’s own State or airport.',
				),
				'modules'   => array(
					'Introduction',
					'Crisis Management Background',
					'Crisis Management Concepts',
					'Crisis Management Plan',
					'Crisis Management Team',
					'Crisis Management Facilities',
					'Testing and Exercises',
					'Closing Activities',
				),
				'public'    => 'This workshop is intended for personnel at the mid to senior management level of organizations within a State who have been assigned responsibility under that State’s National Civil Aviation Security Programme to evaluate and maintain the effectiveness of the crisis management portion of the response to acts of unlawful interference occurring within that State.',
				'prerequis' => array(
					'Have written and oral command of the language of instruction.',
					'Be engaged within organizations mentioned in the target population.',
					'Three years’ experience in a management/supervisory position within organizations mentioned in the target population.',
					'Have attended a specialized aviation security management course.',
				),
			),
		),

		// 4 — Gestion des Risques / Risk Management.
		array(
			'key'        => 'avsec-gestion-risques',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Gestion des risques',
				'duree'     => '5 jours',
				'goal'      => 'L’atelier vise à doter les participants des compétences nécessaires pour identifier, évaluer et gérer les risques en aviation civile. Ils apprendront à reconnaître les menaces, les vulnérabilités et les conséquences, tout en appliquant la méthodologie de gestion des risques de l’OACI pour déterminer les mesures de sûreté appropriées. L’atelier inclut des exercices pratiques pour renforcer la compréhension des techniques de gestion des risques.',
				'objectifs' => array(
					'Identifier et évaluer les menaces potentielles et les conséquences.',
					'Identifier et évaluer les vulnérabilités.',
					'Comprendre les risques dans le contexte de la gestion des risques.',
					'Mettre en place des méthodes, outils et techniques de gestion des risques pour soutenir l’élaboration d’une gestion efficace.',
				),
				'modules'   => array(
					'Introduction',
					'Exigences de l’OACI',
					'Définitions',
					'Gestion des risques',
					'Identifier et évaluer',
					'Atténuer, surveiller et examiner',
					'Communiquer',
					'Exercice récapitulatif',
				),
				'public'    => 'Atelier conçu pour le personnel de gestion de la sûreté de l’aviation.',
				'prerequis' => array(
					'Avoir suivi avec succès la formation de Base de sûreté (123 Base-OACI).',
					'Avoir suivi avec succès le cycle d’enseignement supérieur ou un niveau d’éducation équivalent.',
					'Maîtriser l’expression écrite et orale de la langue d’enseignement du cours.',
				),
			),
			'en'         => array(
				'title'     => 'Risk Management Workshop',
				'duree'     => '4 days',
				'goal'      => 'This workshop is designed to help trainees address security vulnerabilities in their organization and handle the aftermath of a crisis. It improves their understanding of risk management and how to prepare a risk assessment of their organization by correctly assessing and quantifying threat, and how to minimize risk by reviewing current threats to the aviation industry.',
				'objectifs' => array(
					'Establish and maintain risk management capabilities.',
					'Foster a risk management culture.',
					'Establish a consistent approach to assess and manage aviation security risks.',
					'Analyze emerging trends in attacks against civil aviation.',
					'Identify areas of vulnerability and prepare a risk assessment.',
					'Apply risk management principles to aviation security operations.',
					'Develop a crisis management plan.',
				),
				'modules'   => array(
					'Course Introduction',
					'Risk Management Overview',
					'Establishing the Context (Understanding the Process)',
					'Risk Assessment: Identification',
					'Risk Assessment: Analysis',
					'Risk Assessment: Evaluation',
					'Risk Mitigation',
					'Communication and Consultation',
					'Monitor and Review',
					'Closing Activities',
				),
				'public'    => 'This workshop is recommended for civil aviation security staff, airport and airline staff, legislation and administration personnel, aviation security professionals, risk and crisis management teams, regulatory compliance officers, emergency response personnel and corporate security managers.',
				'prerequis' => array(
					'Secondary school graduate.',
					'One-year aviation security experience.',
					'Computer literacy.',
				),
			),
		),

		// 5 — PCQSAC / NCASQCP.
		array(
			'key'        => 'avsec-pcqsac',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Programme de Contrôle Qualité en Sûreté de l’Aviation Civile (PCQSAC)',
				'duree'     => '5 jours',
				'goal'      => 'L’atelier vise à fournir aux responsables de la sûreté de l’aviation les connaissances nécessaires pour développer et appliquer un Programme national de contrôle de la qualité de la sûreté de l’aviation civile (PNCQSAC). Il couvre les nouvelles normes de l’OACI, les principes du contrôle qualité, et aide à l’élaboration, à la mise en œuvre et à la maintenance du PNCQSAC.',
				'objectifs' => array(
					'Fournir au personnel de gestion de la sûreté de l’aviation les connaissances et compétences nécessaires pour élaborer des mesures efficaces de contrôle de la qualité de la sûreté de l’aviation.',
					'Aider à l’élaboration de la documentation, à la méthodologie de mise en œuvre et au maintien de procédures appropriées de surveillance et d’assurance qualité interne.',
					'Déterminer la méthodologie appropriée nécessaire pour élaborer un programme national de contrôle de la qualité et les processus visant à en assurer la maintenance, les mises à jour et les révisions.',
					'Élaborer et rédiger un programme national de contrôle de la qualité de la sûreté de l’aviation civile.',
				),
				'modules'   => array(
					'Expériences des États participants',
					'PCQSAC : les objectifs, les bénéfices',
					'Les normes de l’OACI',
					'Les thèmes du PCQSAC',
					'Rédaction d’un PCQSAC',
				),
				'public'    => 'Cadres supérieurs ou intermédiaires responsables de l’élaboration, de l’approbation et/ou de la mise en œuvre d’activités de contrôle de la qualité. Il est suggéré que les États en cours de rédaction ou de mise à jour de leur PNCQSAC nomment à cet atelier les personnes qui se consacrent à cette tâche.',
				'prerequis' => array(
					'Être à la haute direction ou à la direction intermédiaire, responsable de l’élaboration, de l’approbation et/ou de la mise en œuvre des activités de contrôle de la qualité.',
					'Avoir des connaissances et/ou une expérience antérieure dans les fonctions de surveillance de la sûreté de l’aviation (AVSEC).',
				),
			),
			'en'         => array(
				'title'     => 'National Civil Aviation Security Quality Control Program Workshop',
				'duree'     => '5 days',
				'goal'      => 'To provide aviation security management personnel the knowledge and skills needed to develop effective aviation security quality control measures under a National Civil Aviation Security Quality Control Programme (NCASQCP). The workshop provides tools to assist the development of documentation, implementation methodology and maintenance of appropriate oversight and internal quality assurance procedures.',
				'objectifs' => array(
					'Describe the general principles of quality control as it relates to aviation security.',
					'Draft portions of a simulated national civil aviation security quality control programme.',
				),
				'modules'   => array(
					'Workshop Introduction',
					'NQCP Objectives',
					'NQCP Model Outline',
					'Drafting the NQCP',
					'Closing activities',
				),
				'public'    => 'Participants should be at the middle or senior management level with responsibilities in the development, approval and/or implementation of quality control activities.',
				'prerequis' => array(
					'Participants should work for the National Appropriate Authority responsible for aviation security, other regulatory authorities, and/or other stakeholders that have an active role in the development and/or implementation of national quality control oversight activities.',
					'Participants should have previous knowledge and/or experience in aviation security oversight functions.',
					'Participants should have written and oral command of the language of instruction.',
				),
			),
		),

		// 6 — PNSAC / NCASP.
		array(
			'key'        => 'avsec-pnsac',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Programme National de Sûreté de l’Aviation Civile (PNSAC)',
				'duree'     => '5 jours',
				'goal'      => 'L’atelier vise à permettre aux participants de comprendre, rédiger et réviser le Programme national de sûreté de l’aviation civile (PNSAC). Ils apprendront les méthodologies appropriées pour développer, maintenir et actualiser le programme, tout en acquérant des compétences pour élaborer un plan national d’urgence en aviation civile.',
				'objectifs' => array(
					'Familiariser les participants avec les exigences du programme national de sûreté de l’aviation civile (PNSAC) et leur permettre de rédiger et réviser un tel programme.',
					'Déterminer la méthodologie appropriée nécessaire pour élaborer un PNSAC et les processus visant à en assurer la maintenance, les mises à jour et les révisions.',
					'Élaborer et rédiger un programme national d’urgence en aviation civile.',
				),
				'modules'   => array(
					'Méthodologie, emploi du temps et travaux personnels',
					'Actes d’intervention illicite et contre-mesures',
					'Rôles et responsabilités au plan organisationnel',
					'Autorité légale',
					'Zones de sûreté à accès réglementé',
					'Zones réglementées et contrôle d’accès',
					'Grille d’évaluation de conformité',
				),
				'public'    => 'Il est recommandé que les candidats soient au niveau des cadres supérieurs ou intermédiaires au sein de l’autorité appropriée de l’État, responsables du contrôle, de la rédaction, de la révision, de la mise à jour et/ou de la mise en œuvre du PNSAC.',
				'prerequis' => array(
					'Avoir suivi avec succès les formations Base de sûreté (123 Base-OACI) et Gestion de la sûreté (ou équivalentes).',
					'Avoir suivi avec succès le cycle d’enseignement supérieur ou un niveau d’éducation équivalent.',
					'Maîtriser l’expression écrite et orale de la langue d’enseignement du cours.',
				),
			),
			'en'         => array(
				'title'     => 'National Civil Aviation Security Program Workshop',
				'duree'     => '5 days',
				'goal'      => 'This interactive workshop enables participants to communicate the objectives of the National Civil Aviation Security Programme (NCASP), allocate the responsibilities of aviation security stakeholders, and develop and draft a National Civil Aviation Security Programme in accordance with ICAO requirements.',
				'objectifs' => array(
					'Communicate the objectives of the NCASP by drafting an Objective Statement.',
					'Identify roles and allocate the responsibilities of all aviation security stakeholders as described in the NCASP.',
					'Describe the supporting legislation required for an effective NCASP and determine whether it is based on international or national requirements.',
					'Describe the requirements and methods a State shall implement to ensure an effective communications system at the international and national levels.',
					'Describe the security requirements for transfer and high-risk cargo and common baseline measures for cargo carried on passenger and all-cargo aircraft.',
					'List security measures regarding the protection of airports, aircraft and navigational aids.',
					'List security measures regarding the control of persons and items, including persons other than passengers and vehicles.',
					'List training requirements and options for meeting them.',
					'Determine the make-up of the NCASP development team.',
					'Develop and draft a National Civil Aviation Security Programme.',
				),
				'modules'   => array(
					'Regulatory Requirements',
					'Acts of Unlawful Interference and Countermeasures',
					'Organizational Roles and Responsibilities',
					'Supervisory missions of the competent authority',
					'Development of procedures and guidelines',
					'Coordination and Communication',
					'Development of the quality assurance Programme',
					'Subcontractor supervision procedures',
					'Security Restricted Areas and Access Controls',
					'Training, Effectiveness, and Catering',
					'Development Team',
				),
				'public'    => 'It is recommended that nominees be at the senior or middle management level within the State’s appropriate authority who are responsible for the control, drafting, review, updating and/or implementation of the NCASP.',
				'prerequis' => array(
					'It is strongly advised that the candidate has acquired the regulatory knowledge contained in the basic training.',
				),
			),
		),

		// 7 — Risque Interne / Insider Risk.
		array(
			'key'        => 'avsec-risque-interne',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Atelier sur le risque interne',
				'duree'     => '5 jours',
				'frais'     => 1000,
				'goal'      => 'L’objectif de cet atelier est d’équiper le personnel de sûreté de l’aviation des connaissances et compétences nécessaires pour développer des scénarios crédibles de menaces internes et créer des plans d’action efficaces pour les atténuer. À la fin de l’atelier, les participants seront capables d’identifier les menaces internes potentielles, d’évaluer les risques associés en utilisant la méthodologie d’évaluation des risques de l’OACI, et de développer des stratégies d’atténuation pratiques.',
				'objectifs' => array(
					'Comprendre le concept des menaces internes et appliquer les principes généraux de la gestion des risques.',
					'Définir et comprendre l’évaluation des risques appliquée aux risques liés aux menaces internes dans la sûreté de l’aviation.',
					'Comprendre les différents éléments internes et développer des scénarios de menaces internes.',
					'Comprendre les normes pertinentes de l’Annexe 17.',
					'Comprendre le processus d’évaluation des risques de l’OACI.',
					'Pratiquer l’évaluation des risques basés sur les menaces internes.',
					'Appliquer les principes de gestion des risques aux vulnérabilités liées aux menaces internes dans les opérations de sûreté de l’aviation.',
				),
				'modules'   => array(
					'La gestion du risque lié aux menaces internes',
					'L’élément interne',
					'Méthodologie d’évaluation des risques de l’OACI',
					'Scénarios de menaces internes',
					'L’évaluation',
					'Entreprendre l’évaluation',
					'Atténuation du risque',
					'Aspects pratiques',
					'Surveillance et réexamen',
				),
				'public'    => 'Cet atelier est recommandé pour le personnel de sûreté de l’aviation de niveau débutant ayant un potentiel de promotion au niveau de supervision, ainsi que pour le personnel déjà en poste à ce niveau, employé par l’autorité, une compagnie aérienne, un aéroport ou toute autre organisation responsable de l’application des mesures de sûreté de l’aviation.',
				'prerequis' => array(
					'Expérience en sûreté de l’aviation.',
					'Bonne connaissance des réglementations internationales et nationales en matière de sécurité.',
					'Maîtrise écrite et orale de la langue française.',
				),
			),
			'en'         => array(
				'title'     => 'Insider Risk Workshop',
				'duree'     => '5 days',
				'frais'     => 1000,
				'goal'      => 'The purpose of this workshop is to equip aviation security personnel with the knowledge and skills necessary to develop credible insider threat scenarios and create effective mitigation action plans. By the end of the workshop, participants will be able to identify potential insider threats, assess associated risks using the ICAO Risk Assessment Methodology, and develop practical mitigation strategies.',
				'objectifs' => array(
					'Understand the concept of insider threats and apply general principles of risk management.',
					'Define and understand risk assessment as it applies to insider-based risks in aviation security.',
					'Understand the various types of insiders and develop insider threat scenarios.',
					'Understand relevant Annex 17 standards.',
					'Understand the ICAO risk assessment process.',
					'Practice assessing insider-based risks.',
					'Apply risk management principles to insider-based vulnerabilities in aviation security operations.',
				),
				'modules'   => array(
					'The Management of Insider Risk',
					'The Insider',
					'ICAO Risk Assessment Methodology',
					'Insider Threat Scenarios',
					'The Assessment',
					'Undertaking The Assessment',
					'Risk Mitigation',
					'Practical Aspects',
					'Monitor and Review',
				),
				'public'    => 'This workshop is recommended for aviation security personnel at the basic level with the potential for promotion to the supervisory level, and existing personnel at that level employed by the authority, airline, airport or any other organization primarily responsible for the application of aviation security measures.',
				'prerequis' => array(
					'Experience in aviation security.',
					'Good knowledge of international and national security regulations.',
					'Have written and oral command of the English language.',
				),
			),
		),

		// 8 — Systèmes de Certification / Certification Systems.
		array(
			'key'        => 'avsec-certification',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Systèmes de Certification en Sûreté de l’Aviation',
				'duree'     => '5 jours',
				'goal'      => 'L’atelier permet aux participants de découvrir les éléments indicatifs figurant dans le Manuel de sûreté de l’aviation et offre l’occasion de concevoir des projets de programme pouvant servir de base à l’élaboration de systèmes de certification spécifiques aux États et/ou être intégrés à des systèmes existants.',
				'objectifs' => array(
					'Décrire les exigences et les avantages d’un système de certification tel que prévu par l’Annexe 17.',
					'Identifier la différence entre formation et certification.',
					'Démontrer une connaissance pratique des critères de mise en place des systèmes de certification, ainsi que des indications fournies dans le Manuel de sûreté de l’OACI – Doc 8973/13 (à diffusion restreinte).',
					'Établir les critères de recrutement, de sélection et de formation des personnels pouvant être certifiés.',
					'Identifier les exigences réglementaires définies au niveau national liées au système de certification et de re-certification.',
					'Établir les critères d’un système national de certification.',
				),
				'modules'   => array(
					'Annexe 17 – Définitions relatives aux systèmes de certification',
					'Les systèmes de certification : vue d’ensemble',
					'Recrutement et critères de sélection',
					'Critères de formation',
					'Les systèmes de certification : mise en œuvre',
					'Maintien de la certification',
				),
				'public'    => 'Cet atelier s’adresse au personnel en charge de l’élaboration, de la mise en œuvre et/ou de la supervision des systèmes de certification de l’AVSEC.',
				'prerequis' => array(
					'Avoir suivi avec succès le cycle d’enseignement secondaire ou un niveau d’éducation équivalent.',
					'Maîtriser l’expression écrite et orale de la langue d’enseignement du cours.',
				),
			),
			'en'         => array(
				'title'     => 'Aviation Security Certification Systems Workshop',
				'duree'     => '5 days',
				'goal'      => 'This ICAO workshop allows participants to discover the guidance material contained in the Aviation Security Manual and provides an opportunity to design a draft program that can serve as a basis for the development of State-specific certification systems and/or be integrated into existing systems.',
				'objectifs' => array(
					'Describe the requirements and benefits of a certification system as provided for in Annex 17.',
					'Identify the difference between training and certification.',
					'Demonstrate a working knowledge of the criteria for the establishment of certification systems, as well as the indications provided in the ICAO Security Manual – Doc 8973/8 (restricted).',
					'Establish the criteria for the recruitment, selection and training of personnel who can be certified.',
					'Identify nationally defined regulatory requirements related to the certification and recertification system.',
					'Establish criteria for a national certification system.',
				),
				'modules'   => array(
					'Opening Ceremony and Introduction',
					'Annex 17 Definitions relating to Certification Systems',
					'Certification Systems Overview',
					'Recruitment and Selection Criteria',
					'Training Criteria',
					'ICAO Security Manual, Chapter 8 and Appendices 7 to 10',
					'Implemented Certification Systems',
					'Maintenance of Certification',
				),
				'public'    => 'This workshop is intended for personnel responsible for the development, implementation and/or supervision of AVSEC certification systems.',
				'prerequis' => array(
					'Have written and oral command of the language of instruction.',
					'Engaged within organizations mentioned in the target population.',
				),
			),
		),

		// 9 — Responsables AVSEC / Managers.
		array(
			'key'        => 'avsec-responsables',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Formation des responsables de la sûreté de l’aviation civile (AVSEC Managers)',
				'duree'     => '7 jours',
				'frais'     => 1500,
				'goal'      => 'Permettre à des membres du personnel d’encadrement de planifier, coordonner et faire appliquer des mesures de prévention axées sur la sûreté aéroportuaire, conformément aux programmes nationaux et aéroportuaires agréés en la matière.',
				'objectifs' => array(
					'Expliquer l’origine et la finalité des instruments juridiques de l’OACI liés à la sûreté de l’aviation (Annexe 17, Manuel de sûreté – Doc 8973) et les éléments qui caractérisent les actes d’intervention illicite.',
					'Appliquer les concepts de base en matière de gestion de la sûreté de l’aviation et indiquer la spécificité de la fonction du responsable qui en a la charge.',
					'Planifier, réunir et gérer les ressources humaines et matérielles nécessaires au bon fonctionnement d’une unité chargée de la sûreté de l’aviation.',
					'Planifier et administrer le budget d’une unité chargée de la sûreté.',
					'Prodiguer des conseils concernant l’élaboration de programmes de sûreté aérienne et de procédures d’exploitation normalisées (SOP).',
					'Surveiller et faire appliquer les programmes de sûreté aérienne et les SOP.',
					'S’assurer que le personnel chargé de la sûreté soit capable de mettre en œuvre les mesures préventives de sûreté.',
					'Prodiguer des conseils concernant l’élaboration de plans d’urgence aéroportuaires et gérer les mesures d’intervention en cas d’urgence.',
					'Apporter son concours, le cas échéant, à une équipe de gestion de crises touchant au domaine de l’aviation.',
					'Élaborer et assurer une formation AVSEC destinée aux agents de sûreté et aux autres personnels et usagers des aéroports.',
					'Établir et maintenir un lien avec d’autres aéroports ainsi qu’avec des organismes et services extérieurs.',
				),
				'modules'   => array(
					'Menaces dirigées contre l’aviation',
					'Contremesures',
					'Programme de sûreté d’aéroport',
					'Notions de base sur la sûreté aéroportuaire',
					'Financement et prévision des besoins',
					'Développement du personnel',
					'Recrutement et formation',
					'Ressources matérielles, matériel et équipements',
					'Procédures d’exploitation normalisées (SOP)',
					'Contrôle de la qualité',
					'Planification des mesures d’exception',
				),
				'public'    => 'La formation s’adresse à des membres de la direction ou à des cadres supérieurs principalement chargés de l’application des mesures préventives de sûreté de l’aviation dans les aéroports.',
				'prerequis' => array(
					'Être membre du personnel de l’autorité nationale chargée de la sûreté de l’aviation, de l’administration aéroportuaire en charge de la sûreté, d’autres organismes de réglementation et/ou d’autres parties prenantes œuvrant activement à la gestion de la sûreté de l’aviation.',
					'Aucun niveau minimum de responsabilité n’est exigé pour ceux qui remplissent les critères de participation.',
				),
			),
			'en'         => array(
				'title'     => 'Aviation Security Managers Course',
				'duree'     => '7 days',
				'frais'     => 1500,
				'goal'      => 'To enable participants at the managerial level to plan, coordinate and implement the application of airport security preventive measures in accordance with approved national and airport security programmes.',
				'objectifs' => array(
					'Plan, coordinate and implement the application of airport security preventive measures in accordance with approved programmes.',
					'Apply basic concepts of management to aviation security and explain the specialist role of the aviation security manager.',
					'Develop and implement an Airport Security Programme and associated Standard Operating Procedures (SOPs).',
					'Develop and implement contingency plans, crisis management plans, and crisis management exercises.',
					'Develop and implement AVSEC training for airport personnel.',
				),
				'modules'   => array(
					'Course Introduction',
					'The Threat to Aviation',
					'Industry Counter-Measures',
					'Airport Security Programme (ASP)',
					'Airport Security Overview',
					'Finance and Forecasting',
					'Personnel Development',
					'Recruitment and Training',
					'Resources and Equipment',
					'Standard Operating Procedures (SOPs)',
					'Contingency Planning',
					'Course Closing',
				),
				'public'    => 'The target population should be at the managerial or senior supervisory level, primarily responsible for the application of aviation security preventive measures at airports.',
				'prerequis' => array(
					'Participants should work for the State’s appropriate authority responsible for aviation security, airport authority for aviation security, other regulatory authorities, and/or other stakeholders that have an active role in the management of aviation security.',
					'There is no minimum level of responsibility for those meeting the target audience criteria.',
				),
			),
		),

		// 10 — Formation de Base / Basic Course.
		array(
			'key'        => 'avsec-formation-base',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Formation de base du personnel de sûreté d’aéroport',
				'duree'     => '10 jours',
				'frais'     => 1200,
				'goal'      => 'Le cours, suivi d’une période minimale de six mois d’expérience de travail pratique sous la supervision d’un superviseur AVSEC qualifié, fournit au personnel de sûreté aéroportuaire la formation de base nécessaire pour mettre en œuvre, surveiller et appliquer les mesures préventives de sûreté de l’aéroport, conformément aux programmes approuvés localement.',
				'objectifs' => array(
					'Travailler et circuler en sécurité à l’aéroport.',
					'Communiquer et coopérer avec d’autres services de l’aéroport.',
					'Contrôler les déplacements des personnes et des véhicules.',
					'Garder les zones, installations et aéronefs vulnérables en effectuant des gardes et des patrouilles.',
					'Reconnaître des armes et des engins explosifs ou incendiaires.',
					'Inspecter, filtrer et fouiller des passagers et des bagages.',
					'Réagir aux situations d’urgence à l’aéroport.',
				),
				'modules'   => array(
					'Exposé général de l’aviation civile internationale',
					'Travailler à l’aéroport',
					'Contrôle de l’accès des personnes',
					'Contrôle de l’accès des véhicules',
					'Reconnaissance des engins explosifs et des armes offensives',
					'Procédures de fouille des bâtiments',
					'Patrouille et garde',
					'Filtrage des passagers et fouille physique des passagers',
					'Équipement radioscopique conventionnel',
					'Protection des aéronefs stationnés',
				),
				'public'    => 'La population cible est constituée par les nouvelles recrues et par le personnel en fonction au niveau de départ, employés par l’autorité ou l’organisation ayant la responsabilité primordiale d’appliquer les mesures préventives de sûreté de l’aviation aux aéroports, ainsi que le personnel de même niveau d’autres organismes aéronautiques chargés des activités d’appui.',
				'prerequis' => array(
					'Avoir suivi avec succès le cycle d’enseignement secondaire ou un niveau d’éducation équivalent.',
					'Maîtriser l’expression écrite et orale de la langue d’enseignement du cours.',
				),
			),
			'en'         => array(
				'title'     => 'Aviation Security Basic Course',
				'duree'     => '5 days',
				'frais'     => 1000,
				'goal'      => 'To educate security personnel, and those responsible for the implementation of aviation security measures, in order to enhance overall protection against acts of unlawful interference.',
				'objectifs' => array(
					'Apply knowledge, skills and abilities desired for an aviation security officer.',
					'Understand the procedures and processes required when implementing preventive aviation security measures.',
				),
				'modules'   => array(
					'Course Introduction',
					'Threat to Civil Aviation',
					'Civil Aviation Countermeasures',
					'Working at the Airport',
					'Access Control',
					'Prohibited Items and Dangerous Goods',
					'Passenger – Baggage Screening',
					'Hold Baggage Screening',
					'Protection of Aircraft',
					'Course Closing',
				),
				'public'    => 'The target population will be new entrants and existing personnel at the basic level employed by the authority or organization primarily responsible for the application of aviation security measures at airports and from such other aviation-related agencies engaged in support activities.',
				'prerequis' => array(
					'Participants should work for the authority responsible for aviation security, other regulatory authorities, airports, airlines, and/or other stakeholders that have an active role in training AVSEC personnel.',
					'Participants should have written and oral command of the language of instruction.',
				),
			),
		),

		// 11 — Imagerie Radioscopique / X-Ray Training.
		array(
			'key'        => 'avsec-imagerie',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Imagerie radioscopique en sûreté de l’aviation civile',
				'duree'     => '5 jours',
				'frais'     => 1000,
				'goal'      => 'Ce cours vise à fournir aux participants les compétences nécessaires pour utiliser un système radioscopique dans un aéroport. Ils apprendront à comprendre l’importance de la prévention, à reconnaître des objets dangereux, à analyser des images radioscopiques et à appliquer les protocoles de sécurité. Le cours combine théorie et pratique pour garantir une maîtrise complète du système radioscopique et de la résolution des menaces.',
				'objectifs' => array(
					'Comprendre l’importance de la prévention en rappelant quelques incidents survenus.',
					'Décrire les principaux éléments constitutifs d’un système radioscopique.',
					'Expliquer le principe de fonctionnement d’un système radioscopique.',
					'Exécuter le test de bon fonctionnement.',
					'Lister les principales règles de sécurité.',
					'Reconnaître les armes, les engins explosifs, les objets dangereux et usuels, notamment lorsqu’ils sont démontés, camouflés ou dissimulés.',
					'Appliquer le protocole d’analyse de l’image radioscopique.',
					'Appliquer le protocole de résolution de la menace (test de progression).',
					'Utiliser le système radioscopique mis en place sur l’aéroport.',
				),
				'modules'   => array(
					'Activités d’ouverture, introduction et administration du stage',
					'Connaissance de base en imagerie radioscopique',
					'Articles réglementés, objets usuels et images associées',
					'Processus d’analyse de l’image radioscopique',
					'Filtrage des passagers et fouille physique des passagers',
					'Exercice pratique',
				),
				'public'    => 'Tout personnel aéroportuaire susceptible de tenir un poste lié à l’imagerie radioscopique.',
				'prerequis' => array(
					'Maîtrise du français parlé et écrit.',
					'Avoir suivi avec succès le cycle d’enseignement secondaire ou un niveau équivalent.',
				),
			),
			'en'         => array(
				'title'     => 'X-Ray Training in Aviation Security',
				'duree'     => '5 days',
				'goal'      => 'The purpose of this course is to enable participants to proficiently utilize X-ray imaging systems to detect and resolve threats effectively at airports. They will understand the operational principles, safety protocols, and image analysis techniques necessary to identify prohibited items, weapons, and concealed threats, ensuring robust aviation security.',
				'objectifs' => array(
					'Understand the importance of prevention by recalling a few incidents that have occurred.',
					'Describe the main components of an x-ray system.',
					'Explain the operating principle of an X-ray system.',
					'Perform the proper functioning test.',
					'List the main safety rules.',
					'Recognize weapons, explosive devices, dangerous and everyday objects, especially when dismantled or concealed.',
					'Apply the radioscopic image analysis protocol.',
					'Apply the threat resolution protocol (progress test).',
					'Use the x-ray system set up at the airport.',
				),
				'modules'   => array(
					'Introduction',
					'Basic knowledge in radioscopic imaging',
					'Prohibited articles, common objects, and associated images',
					'Radioscopic image analysis process',
					'Imaging theory and practice',
					'Practical part: identification of real images, test kit, XRT4 Simulator – CASRA',
				),
				'public'    => 'The target population will be all airport personnel likely to hold a position related to radioscopic imaging.',
				'prerequis' => array(
					'Have successfully completed secondary education or an equivalent level.',
					'Have written and oral command of the language of instruction.',
				),
			),
		),

		// 12 — Inspecteurs Nationaux / National Inspectors.
		array(
			'key'        => 'avsec-inspecteurs',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Inspecteurs nationaux en sûreté de l’aviation civile',
				'duree'     => '7 jours',
				'frais'     => 1100,
				'goal'      => 'Permettre aux auditeurs et inspecteurs de sûreté de l’aviation d’acquérir les connaissances théoriques et pratiques nécessaires pour conduire des audits, inspections, enquêtes et/ou tests nationaux, en appliquant les compétences pertinentes et conformément à l’Annexe 17 et au Manuel de sûreté de l’aviation de l’OACI (Doc 8973 – à diffusion restreinte).',
				'objectifs' => array(
					'Déterminer quels sont les éléments de surveillance constitutifs d’un programme national de contrôle de qualité de la sûreté de l’aviation.',
					'Décrire la base juridique pour les audits, inspections, enquêtes et tests relatifs à la sûreté de l’aviation.',
					'Définir les audits, inspections, enquêtes et tests relatifs à la sûreté de l’aviation.',
					'Déterminer les devoirs et compétences spécifiques requis par un inspecteur.',
					'Déterminer la méthodologie appropriée pour la conduite d’audits et d’inspections dans le cadre d’un programme national.',
					'Préparer et conduire un audit ou une inspection en sûreté de l’aviation et en présenter les conclusions dans le cadre d’un exercice.',
				),
				'modules'   => array(
					'Définitions et activités du Programme national de contrôle de la qualité en sûreté de l’aviation civile',
					'Profil de l’inspecteur',
					'Méthodologie de l’audit et de l’inspection',
					'Techniques d’inspection et d’audit',
					'Techniques de préparation d’une inspection',
					'Mener et conduire des tests et investigations de sûreté',
				),
				'public'    => 'Des personnels existant au niveau de la supervision ou de la gestion, ayant vocation à devenir auditeur et/ou inspecteur en sûreté de l’aviation au sein de leur État.',
				'prerequis' => array(
					'Expérience avérée en sûreté de l’aviation.',
					'Bonne connaissance de la réglementation internationale et nationale en sûreté.',
				),
			),
			'en'         => array(
				'title'     => 'Aviation Security National Inspectors Course',
				'duree'     => '7 days',
				'frais'     => 1100,
				'goal'      => 'The purpose of this course is to enable aviation security auditors/inspectors to acquire the theoretical and practical knowledge necessary for conducting national audits, inspections and/or tests through the application of the relevant competencies and in accordance with Annex 17 – Aviation Security and the ICAO Aviation Security Manual (Doc 8973 – Restricted).',
				'objectifs' => array(
					'Determine monitoring elements contained in a National Civil Aviation Security Quality Control Programme (NCASQP).',
					'Describe the legal basis for security audits, inspections and tests.',
					'Define aviation security audits, inspections and tests.',
					'Determine the duties and specific skills required by an auditor/inspector.',
					'Determine the appropriate national programme methodology for conducting quality assurance activities.',
					'Conduct the various aspects of a simulated aviation security inspection.',
					'Define effective Root Cause Analysis and resolution.',
				),
				'modules'   => array(
					'Course Introduction',
					'AVSEC Oversight',
					'NQCP Monitoring Activities',
					'Inspectors Profile',
					'Audit Planning',
					'Audit Communication',
					'Audit Activity',
					'Audit Report',
					'Corrective Actions, Tests and Investigations',
					'Quality Assurance',
					'Course Closing',
				),
				'public'    => 'The target population will be aviation security personnel who are employed at the national level primarily responsible for the national quality control oversight.',
				'prerequis' => array(
					'Participants should work for the State’s appropriate authority responsible for aviation security, other regulatory authorities, airports, airlines, and/or other stakeholders that have an active role in their national aviation security quality control oversight programme.',
					'Participants should have attended a specialized AVSEC training activity and/or have regulatory experience.',
					'Participants should have written and oral command of the language of instruction.',
				),
			),
		),

		// 13 — Instructeurs Nationaux / National Instructors.
		array(
			'key'        => 'avsec-instructeurs',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Formation des instructeurs en sûreté de l’aviation civile',
				'duree'     => '7 jours',
				'frais'     => 1100,
				'goal'      => 'Permettre au personnel de sûreté de l’aviation de dispenser à un personnel sélectionné des cours spécialisés de formation à la sûreté de l’aviation, validés et axés sur les matériaux didactiques, tels que les Mallettes pédagogiques normalisées (MPN) et les Mallettes de formation à la sûreté de l’aviation (MPSA).',
				'objectifs' => array(
					'Dispenser une formation AVSEC en appliquant les principes généraux d’apprentissage et d’enseignement.',
					'Présenter efficacement des cours de formation validés et axés sur les matériaux didactiques.',
					'Identifier, sélectionner et préparer les aides pédagogiques appropriées.',
					'Concevoir ou modifier les objectifs de la formation et les examens pour les adapter aux exigences nationales.',
					'Identifier et utiliser les méthodes d’enseignement appropriées.',
				),
				'modules'   => array(
					'Introduction au stage',
					'Rôle d’un instructeur AVSEC',
					'Principes d’apprentissage et d’enseignement',
					'Organisation et préparation d’un stage',
					'Pratique des présentations',
					'Évaluation des performances',
					'Familiarisation avec la MPSA Base – Pratique des présentations',
					'Présentations de groupe',
					'Activités de clôture',
				),
				'public'    => 'Personnel auquel sont ou seront assignées des fonctions de formation liées à la sûreté de l’aviation.',
				'prerequis' => array(
					'Avoir une expérience professionnelle en matière de sûreté de l’aviation.',
					'Avoir le niveau scolaire du secondaire ou un niveau équivalent.',
					'Avoir la maîtrise écrite et orale de la langue d’enseignement.',
					'Avoir fait preuve d’une aptitude pour les fonctions de formation.',
				),
			),
			'en'         => array(
				'title'     => 'Aviation Security National Instructors Course',
				'duree'     => '5 days',
				'frais'     => 1000,
				'goal'      => 'The purpose of this course is to enable aviation security instructors to perform a competency-based and effective role in the delivery of their national aviation security (AVSEC) training activities, through the application of the relevant competencies and in accordance with Annex 17 – Aviation Security and the ICAO Aviation Security Manual (Doc 8973 – Restricted).',
				'objectifs' => array(
					'Apply knowledge, skills, and abilities desired for an aviation security instructor.',
					'Identify Annex 17 – Aviation Security requirements and ICAO guidance for aviation security training.',
					'Perform a competency-based delivery of national aviation security training activities through the application of the relevant competencies.',
				),
				'modules'   => array(
					'Course Introduction',
					'Role of the AVSEC Instructor',
					'Principles of Learning and Instruction',
					'Course Organization and Preparation',
					'AVSEC Instructional Techniques',
					'Presentation Practice',
					'Assessment of Performance',
					'ASTP Individual Presentations',
					'Course Closing',
				),
				'public'    => 'This course is designed for personnel involved with the instruction and/or development and management of training materials related to a national civil aviation security training programme.',
				'prerequis' => array(
					'Participants should work for the State’s appropriate authority responsible for aviation security, other regulatory authorities, airports, airlines, and/or other stakeholders that have an active role in training AVSEC personnel.',
					'There is no minimum level of responsibility for those meeting the target audience criteria.',
					'Participants should have written and oral command of the language of instruction.',
				),
			),
		),

		// 14 — Programme de Sûreté d’Aéroport (PSA) / Airport Security Programme (ASP).
		array(
			'key'        => 'avsec-psa',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Programme de Sûreté de l’Aéroport (PSA)',
				'duree'     => '5 jours',
				'goal'      => 'Cet atelier interactif est conçu pour familiariser les participants aux exigences du Programme de sûreté d’aéroport (PSA) et leur permettre de rédiger et de mettre à jour ce programme.',
				'objectifs' => array(
					'Décrire les exigences de l’OACI en matière de Programme de sûreté d’aéroport (PSA).',
					'Connaître le PSA type de l’OACI ainsi que les indications contenues dans le Manuel de sûreté de l’OACI (Doc 8973/9 – à diffusion restreinte).',
					'Déterminer la méthodologie appropriée requise pour élaborer un PSA et les processus visant à en assurer la révision et la mise à jour.',
					'Acquérir une expérience pratique à travers la rédaction de certaines parties du PSA.',
				),
				'modules'   => array(
					'Sûreté de l’aviation civile – Exigences de l’OACI : les programmes et normes de référence',
					'Le PSA',
					'L’Annexe 17 de l’OACI et le Manuel de sûreté Doc 8973',
					'Exercices',
					'Éléments à inclure dans un PSA et maintien à jour du PSA et des PEN',
				),
				'public'    => 'Il est recommandé que les candidats soient au niveau des cadres supérieurs ou intermédiaires au sein de l’autorité compétente de l’État ou au sein d’un aéroport, responsables de l’approbation, du contrôle de la qualité, de la rédaction et de la mise à jour.',
				'prerequis' => array(
					'Occuper une fonction de cadre supérieur ou intermédiaire au sein de l’autorité compétente de l’État ou d’un aéroport, en charge de l’approbation, du contrôle de la qualité, de la rédaction et/ou de la mise à jour des PSA.',
				),
			),
			'en'         => array(
				'title'     => 'Airport Security Program Workshop',
				'duree'     => '5 days',
				'goal'      => 'By the end of the Airport Security Programme Workshop, participants will understand the requirements of an Airport Security Programme (ASP), be capable of developing and reviewing ASPs, and be equipped with the methodology and processes necessary for ASP maintenance, updates and revisions, ensuring compliance with relevant regulations and enhancing airport security measures.',
				'objectifs' => array(
					'Familiarize participants with the requirements of an Airport Security Programme (ASP) and enable them to draft and review such programmes.',
					'Determine the appropriate methodology required to develop an Airport Security Programme and the processes to ensure its maintenance, updates and revisions.',
					'Develop and draft an Airport Security Programme.',
				),
				'modules'   => array(
					'Acts of Unlawful Interference and Countermeasures',
					'Review of Annex 17 and Doc 8973',
					'Elements of ASP',
					'The requirements of an Airport Security Programme (ASP)',
					'Methodology required to develop an Airport Security Programme',
					'Processes to ensure ASP maintenance, updates, and revisions',
					'Implementation of ASPs',
					'Draft an Airport Security Programme',
					'Review of draft ASP Chapters',
				),
				'public'    => 'It is recommended that nominees be at the senior or middle management level within the State’s appropriate authority, or within an airport, who are responsible for the approval, quality control, drafting, updating and/or implementation of ASPs.',
				'prerequis' => array(
					'Attended a specialized AVSEC training activity, such as the ICAO National Civil Aviation Security Programme Workshop or Management Course.',
					'Written and oral command of the language of instruction.',
				),
			),
		),

		// 15 — PNFSAC / NCASTP.
		array(
			'key'        => 'avsec-pnfsac',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Programme National de Formation en Sûreté de l’Aviation Civile (PNFSAC)',
				'duree'     => '5 jours',
				'goal'      => 'L’atelier vise à familiariser les participants avec les principes d’un Programme national de formation en sûreté de l’aviation civile (PNFSAC). Il leur apporte les compétences nécessaires pour élaborer, mettre en œuvre et maintenir un PNFSAC conforme aux exigences de certification de l’OACI. Les participants apprendront également à rédiger un projet de PNFSAC basé sur le modèle de l’OACI.',
				'objectifs' => array(
					'Se familiariser avec les principes généraux d’un programme national de formation en sûreté de l’aviation civile (PNFSAC).',
					'Acquérir les connaissances et habiletés nécessaires à l’élaboration et à la mise en œuvre d’un programme répondant aux exigences de la formation, y compris la certification.',
					'Élaborer un projet de PNFSAC en utilisant le modèle de l’OACI.',
					'Déterminer la méthodologie appropriée requise pour élaborer un PNFSAC et les processus visant à en assurer la maintenance, les mises à jour et les révisions.',
					'Élaborer et rédiger un programme national de formation en sûreté de l’aviation civile.',
				),
				'modules'   => array(
					'Normes et pratiques recommandées',
					'Certification ; objectifs et portée d’un PNFSAC',
					'Rôles et responsabilités',
					'Rédaction du PNFSAC',
					'Sensibilisation à la sûreté et formation AVSEC',
					'Système de sûreté de l’aviation et contremesures',
					'Développement du PNFSAC',
				),
				'public'    => 'Les participants devraient être au niveau supérieur ou intermédiaire avec un rôle dans l’élaboration, la mise en œuvre et/ou la supervision du PNFSAC.',
				'prerequis' => array(
					'Avoir suivi avec succès les formations Base de sûreté (123 Base-OACI) et/ou Gestion de la sûreté.',
					'Avoir suivi avec succès le cycle d’enseignement supérieur ou un niveau d’éducation équivalent.',
					'Maîtriser l’expression écrite et orale de la langue d’enseignement du cours.',
				),
			),
			'en'         => array(
				'title'     => 'National Civil Aviation Security Training Program Workshop',
				'duree'     => '5 days',
				'goal'      => 'The goal of the workshop is to enable participants to identify the general principles of a National Civil Aviation Security Training Programme (NCASTP) and to acknowledge the training requirements. Participants will develop a draft NCASTP using the provided template, in accordance with the ICAO Aviation Security Manual (Doc 8973 – Restricted).',
				'objectifs' => array(
					'State the ICAO requirements for a NCASTP.',
					'State the objectives and scope of a NCASTP.',
					'Outline responsibilities of the Appropriate Authority with regard to the State’s NCASTP, including those responsibilities which may be delegated.',
					'Describe recommended monitoring activities related to AVSEC training and list the four subsystems which make up the Aviation Security System.',
					'State the primary benefit of completing a job description and job analysis as part of the recruitment process.',
					'List the three phases of course development.',
					'Define security culture and identify the needs of security awareness and AVSEC training, in accordance with the ICAO Global Aviation Security Plan (GASeP).',
					'Using the NCASTP exercises template provided, draft assigned sections of a NCASTP.',
				),
				'modules'   => array(
					'Introduction to NCASTP',
					'Standards and Recommended Practices',
					'Objectives and Scope of a NCASTP',
					'Roles and Responsibilities',
					'Course Development',
					'Training Objectives and Tests',
					'Design of Modules and Course Development Team',
					'Security Culture and Awareness Training',
					'Final Review and Closing Activities',
				),
				'public'    => 'Participants should be at the senior or middle management level with a role in the development, implementation and/or oversight of the NCASTP.',
				'prerequis' => array(
					'Have successfully completed secondary education or an equivalent level.',
					'Have written and oral command of the language of instruction.',
				),
			),
		),

		// 16 — Superviseurs de Sûreté d’Aéroport / Airport Security Supervisors.
		array(
			'key'        => 'avsec-superviseurs',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Superviseur en sûreté d’aéroport',
				'duree'     => '5 jours',
				'frais'     => 1000,
				'goal'      => 'S’assurer que le personnel concerné des aéroports peut superviser et contrôler la mise en œuvre des mesures préventives de sûreté de l’aviation (AVSEC) en appliquant les compétences que doivent nécessairement avoir les superviseurs de sûreté.',
				'objectifs' => array(
					'Superviser la mise en œuvre des mesures préventives de sûreté de l’aviation et contrôler la qualité de l’exécution des diverses tâches.',
					'Contribuer à la sécurité et à la sûreté des passagers, de l’équipage, du personnel au sol et du public en général.',
				),
				'modules'   => array(
					'Menace contre l’aviation',
					'Supervision d’aéroport',
					'Procédures d’exploitation normalisées',
					'Efficacité des équipements',
					'Emploi du temps, déploiement et affectation',
					'Supervision des tâches opérationnelles',
					'Évaluation du personnel et formation en cours d’emploi',
					'Réponse aux incidents',
				),
				'public'    => 'Personnel chargé de la formation des superviseurs dans différentes organisations ; personnel AVSEC de niveau de base ayant le potentiel d’être promu au niveau de superviseur ; superviseurs en poste n’ayant pas reçu de formation formelle ; personnel AVSEC de l’autorité de l’aviation civile ou des compagnies aériennes ; personnel des agences liées à l’aviation engagé dans des activités de soutien à la sûreté.',
				'prerequis' => array(
					'Avoir achevé avec succès le cours de Formation de base de l’OACI ou son équivalent.',
					'Avoir une bonne connaissance écrite et orale de la langue dans laquelle la formation est dispensée.',
					'Avoir achevé avec succès des études secondaires ou avoir un niveau d’éducation équivalent.',
				),
			),
			'en'         => array(
				'title'     => 'Airport Security Supervisors Course',
				'duree'     => '5 days',
				'frais'     => 1000,
				'goal'      => 'To ensure the relevant personnel at airports can supervise and monitor the implementation of aviation security preventive measures through the application of the relevant competencies required for security supervisors.',
				'objectifs' => array(
					'Supervise the implementation of the aviation security preventive measures and monitor the quality of the performance of the various tasks.',
					'Contribute to the safety and security of passengers, crew, ground personnel, and the general public.',
				),
				'modules'   => array(
					'Threat to Aviation Security',
					'Airport Supervision',
					'Standard Operating Procedures',
					'Equipment Effectiveness',
					'Duty Roster, Deployment and Assignment',
					'Supervisors Operational Duties',
					'Performance Appraisals and On-The-Job Training',
					'Incident Response',
				),
				'public'    => 'Personnel responsible for setting up supervisory training within their organizations; AVSEC personnel at the basic level with the potential for promotion to the supervisory level; existing supervisors who have not received formal supervisory training; AVSEC personnel from the civil aviation authority or airlines; and personnel from aviation related agencies engaged in aviation security support activities.',
				'prerequis' => array(
					'Participants should work for the Airport’s Authority responsible for aviation security, other regulatory authorities, and/or other stakeholders that have an active role in the supervision of aviation security.',
					'Participants should have written and oral command of the language of instruction.',
				),
			),
		),

		// 17 — Facilitation (Annexe 9) / ICAO Annex 9 – Facilitation.
		array(
			'key'        => 'avsec-facilitation',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Cours sur l’Annexe 9 de l’OACI – Facilitation',
				'duree'     => '5 jours',
				'frais'     => 1000,
				'goal'      => 'Le cours sur l’Annexe 9 de l’OACI – Facilitation fournit une formation globale en facilitation au personnel compétent des autorités de l’aviation civile (AAC) et des autorités de contrôle frontalier, pour leur permettre d’aborder de manière collective et coordonnée entre organismes les dispositions relatives à la facilitation contenues dans l’Annexe 9, et d’appuyer l’élaboration et la mise en œuvre du Programme national de facilitation du transport aérien (PNFTA), du Comité national de facilitation du transport aérien (CNFTA) et du Comité de facilitation des aéroports.',
				'objectifs' => array(
					'Expliquer les articles de la Convention de Chicago liés à la facilitation contenus dans l’Annexe 9.',
					'Décrire le contenu de l’Annexe 9 – Facilitation et expliquer les dispositions spécifiques relatives à l’entrée et à la sortie des passagers, du fret, de la poste, des équipages et des aéronefs.',
					'Identifier les parties prenantes concernées ou responsables de la mise en œuvre du Programme national de facilitation du transport aérien (PNFTA).',
					'Décrire la base juridique, le contenu et la structure d’un PNFTA fondé sur les exigences de la Convention de Chicago et de l’Annexe 9.',
					'Communiquer la base juridique, la composition et la fonction d’un Comité national de facilitation du transport aérien.',
					'Décrire les dispositions de l’Annexe 9 relatives aux systèmes d’échange de données des passagers.',
					'Rédiger un projet de PNFTA.',
				),
				'modules'   => array(
					'Introduction',
					'Aperçu général du Programme de facilitation de l’OACI',
					'Entrée et sortie des aéronefs',
					'Entrée et sortie des personnes et de leurs bagages',
					'Entrée et sortie des marchandises et autres articles',
					'Personnes non admissibles et personnes expulsées',
					'Dispositions de facilitation couvrant des sujets spécifiques',
					'Systèmes d’échange de données sur les passagers',
					'Dispositions relatives à la santé',
					'Programmes nationaux de facilitation du transport aérien',
				),
				'public'    => 'Personnel de niveau intermédiaire de l’autorité de l’aviation civile et des autorités compétentes de contrôle aux frontières, responsables et/ou impliqués dans l’établissement et la mise en œuvre du PNFTA et de la CNFTA (douanes, immigration, santé, quarantaine, autorités de délivrance des visas et des documents de voyage, etc.).',
				'prerequis' => array(
					'Maîtrise de la langue française.',
					'Expérience dans le domaine de la facilitation.',
					'Connaissances en informatique.',
				),
			),
			'en'         => array(
				'title'     => 'ICAO Annex 9 - Facilitation',
				'duree'     => '5 days',
				'frais'     => 1000,
				'goal'      => 'The ICAO Annex 9 – Facilitation course provides relevant staff from Civil Aviation Authorities (CAA) and border control authorities with the competencies to deal collectively, in an inter-agency manner, with Annex 9’s facilitation provisions and to support the development and implementation of a National Air Transport Facilitation Programme (NATFP), National Air Transport Facilitation Committee (NATFC) and Airport Facilitation Committee.',
				'objectifs' => array(
					'Explain the facilitation-related Articles of the Chicago Convention contained in Annex 9.',
					'Describe the content of Annex 9 – Facilitation and explain specific provisions on entry and departure of passengers, cargo, mail, crews and aircraft.',
					'Identify relevant stakeholders involved with or responsible for implementation of the National Air Transport Facilitation Programme.',
					'Describe the legal basis, content and structure of a National Air Transport Facilitation Programme based on the facilitation requirements of the Chicago Convention and of Annex 9.',
					'Communicate the legal basis, composition and function of a National Air Transport Facilitation Committee.',
					'Describe the Annex 9 provisions related to Passenger Data Exchange Systems.',
					'Draft a sample NATFP.',
				),
				'modules'   => array(
					'Introduction',
					'ICAO Facilitation Program Overview',
					'Entry and Departure of Aircraft',
					'Entry and Departure of Persons and their Baggage',
					'Entry and Departure of Cargo and Other Articles',
					'Inadmissible Persons and Deportees',
					'Facilitation Provisions Covering Specific Subjects',
					'Passenger Data Exchange Systems',
					'Health-Related Provisions',
					'National Air Transport Facilitation Programme',
				),
				'public'    => 'Middle management within Civil Aviation Authorities and border-control authorities involved in establishing and implementing the NATFP and NATFC (Customs, Immigration, Health, Quarantine, visa-issuing authorities, travel document-issuing authorities, etc.), as well as middle management personnel in charge of facilitation activities such as aircraft operators, airport operators and ground handling agents.',
				'prerequis' => array(
					'Proficiency in the English language.',
					'Experience in the area of facilitation.',
					'Computer literacy.',
				),
			),
		),

		// 18 — Airport Landside Security (EN uniquement).
		array(
			'key'        => 'avsec-landside-security',
			'certificat' => 1,
			'en'         => array(
				'title'     => 'Airport Landside Security',
				'duree'     => '5 days',
				'frais'     => 1500,
				'goal'      => 'This course provides relevant aviation security personnel at the national/airport level with the necessary knowledge and skills to design and implement preventive security measures in the landside area of an airport, in accordance with ICAO Annex 17 (12th edition), the Aviation Security Manual Doc 8973 – Restricted (13th edition), the National Civil Aviation Security Programme (NCASP) and the Airport Security Programme (ASP).',
				'objectifs' => array(
					'Analyse existing and emerging threats and risks for landside security.',
					'Develop the appropriate security measures in the landside areas.',
					'Design the security measures of airport facilities in the landside area.',
					'Develop the crisis management plans in the landside of the airport.',
					'Enhance the security culture programme (landside area).',
				),
				'modules'   => array(
					'Introduction',
					'Risk management of Landside Security',
					'Development of Landside Security Measures',
					'Security design of airport landside facilities',
					'Development of the Crisis Management Plans for the landside Security',
					'Security Culture at the Airport',
				),
				'public'    => 'Personnel from national civil aviation authorities, airport police and airport authorities who are responsible for security operations.',
				'prerequis' => array(
					'Trainees must have working experience in airport security or the civil aviation industry and have completed secondary education.',
					'English language proficiency and computer literacy are also required.',
				),
			),
		),

		// 19 — Formation sur la détection des comportements / Behaviour Detection Course.
		array(
			'key'        => 'avsec-behaviour-detection',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Formation sur la détection des comportements',
				'duree'     => '5 jours',
				'frais'     => 1000,
				'goal'      => 'Former le personnel de sûreté et les responsables de la mise en œuvre des mesures de sûreté de l’aviation aux techniques et aux méthodes de détection des comportements, afin de renforcer la protection d’ensemble contre les actes d’intervention illicite.',
				'objectifs' => array(
					'Identifier les responsabilités de l’État en matière de formation à la détection des comportements.',
					'Expliquer la définition de la détection des comportements et les applications de l’évaluation de sûreté fondée sur les risques dans le contexte aéroportuaire.',
					'Reconnaître et évaluer les menaces pesant sur la sûreté de l’aviation, y compris les modes opératoires et l’élaboration des contre-mesures.',
					'Décrire les avantages de la détection des comportements dans le cadre de la sûreté de l’aviation.',
					'Identifier les instruments juridiques sur lesquels s’appuie un programme de détection des comportements.',
					'Expliquer les principes, les objectifs et la stratégie de déploiement des agents de détection comportementale dans les aéroports.',
					'Effectuer les contrôles documentaires et repérer les signes critiques dans les documents.',
					'Reconnaître les différents types de documents et leurs caractéristiques.',
					'Identifier et interpréter les signes suspects et critiques présentés par les bagages.',
				),
				'modules'   => array(
					'Contexte de la détection des comportements',
					'Les menaces contre l’aviation civile',
					'La première impression',
					'La seconde impression',
					'Documents et bagages',
					'L’évaluation',
					'Stratégie',
					'Relation avec le passager',
				),
				'public'    => 'Cette formation s’adresse aux personnes intervenant dans la sûreté de l’aviation : personnel de sûreté, personnel aéroportuaire, forces de l’ordre, agents publics chargés des politiques de sûreté et professionnels du secteur aéronautique participant à la détection et à la prévention des menaces contre la sûreté aéroportuaire.',
				'prerequis' => array(
					'Être titulaire d’un diplôme de fin d’études secondaires.',
					'Justifier d’une année d’expérience en sûreté de l’aviation.',
					'Maîtriser les outils informatiques.',
				),
			),
			'en'         => array(
				'title'     => 'Behaviour Detection Course',
				'duree'     => '5 days',
				'frais'     => 1000,
				'goal'      => 'This course is designed to educate security personnel, and those responsible for the implementation of aviation security measures, in behaviour detection techniques and methods to enhance overall protection against acts of unlawful interference.',
				'objectifs' => array(
					'Identify the State’s responsibilities regarding behaviour detection training.',
					'Explain the definition of behaviour detection and risk-based security assessment applications in the airport context.',
					'Recognize and assess threats to aviation security, including methods of attack and development of countermeasures.',
					'Describe the benefits of behaviour detection within the aviation security framework.',
					'Identify the legal instruments that support a behaviour detection programme.',
					'Explain the principles, objectives, and deployment strategy of Behaviour Detection Officers at airports.',
					'Perform document checks and identify critical signs in documents.',
					'Recognize different document types and their features.',
					'Identify and interpret suspicious and critical signs in baggage.',
				),
				'modules'   => array(
					'Behaviour Detection Background',
					'The Threats to Civil Aviation',
					'The First Impression',
					'The Second Impression',
					'Documents and Baggage',
					'The Assessment',
					'Strategy',
					'Customer Service',
				),
				'public'    => 'This course is typically addressed to individuals involved in aviation security, including security personnel, airport staff, law enforcement officers, government officials responsible for security policies, and professionals in the aviation industry involved in the detection and prevention of threats to airport security.',
				'prerequis' => array(
					'Secondary school graduate.',
					'One-year aviation security experience.',
					'Computer literacy.',
				),
			),
		),

		// 20 — Maintenance des équipements de Sûreté (FR uniquement : absent du
		// programme annuel anglais). Contenu repris de l'import catalogue (STP
		// AVSEC/139/MES/278FR) ; durée alignée sur le Programme 2026 (05 jours).
		array(
			'key'        => 'avsec-maintenance-equipements-surete',
			'certificat' => 1,
			'langues'    => array( 'fr' ),
			'fr'         => array(
				'title'     => 'Maintenance des équipements de Sûreté',
				'duree'     => '5 jours',
				'frais'     => 1000,
				'goal'      => 'Permettre aux techniciens et aux agents de sûreté d’identifier les équipements de sûreté et la fonction qu’ils assurent, puis d’en assurer la maintenance préventive et curative, afin de garantir la disponibilité et le niveau de performance des moyens de contrôle déployés à l’aéroport.',
				'objectifs' => array(
					'Identifier les équipements de sûreté et la fonction assurée par chacun d’eux.',
					'Assurer la maintenance préventive des équipements de sûreté.',
					'Assurer la maintenance curative des équipements de sûreté.',
				),
				'modules'   => array(
					'Introduction',
					'Maintenance préventive du RX',
					'Maintenance préventive du portique',
					'Maintenance préventive du magnétomètre',
					'Maintenance curative du RX',
					'Maintenance curative du portique',
					'Maintenance curative du magnétomètre',
				),
				'public'    => 'Les agents de sûreté (opérateurs, agents de fouille, agents fret…) et les techniciens de maintenance des équipements de sûreté. Public cible secondaire : superviseurs et inspecteurs de sûreté.',
				'prerequis' => array(
					'Avoir des notions de base en sûreté et/ou en maintenance des équipements de sûreté.',
					'Justifier d’au moins un an d’expérience professionnelle dans le domaine de la sûreté et/ou de la maintenance des équipements de sûreté.',
				),
			),
		),

		/*
		 * 21 & 22 — Recyclages « à la demande » (section 2 des deux programmes 2026 :
		 * « Recyclage des inspecteurs / instructeurs nationaux », 3 jours).
		 *
		 * Le client n'a fourni AUCUNE fiche descriptive pour ces deux cours : seuls
		 * l'intitulé, la durée et la modalité sont attestés. Objectifs et modules sont
		 * donc laissés VIDES plutôt qu'inventés — le gabarit de la fiche masque les
		 * sections vides. À compléter dès réception des fiches.
		 */
		array(
			'key'        => 'avsec-recyclage-inspecteurs',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Recyclage des inspecteurs nationaux',
				'duree'     => '3 jours',
				'goal'      => 'Permettre aux inspecteurs nationaux en sûreté de l’aviation civile déjà certifiés d’actualiser leurs connaissances et de maintenir leur qualification, au regard des évolutions de l’Annexe 17 et du Manuel de sûreté de l’aviation de l’OACI (Doc 8973). Ce cours est dispensé à la demande, en Tunisie ou sur le site du bénéficiaire.',
				'objectifs' => array(),
				'modules'   => array(),
				'public'    => 'Inspecteurs nationaux en sûreté de l’aviation civile en fonction, ayant déjà suivi la formation initiale correspondante.',
				'prerequis' => array(
					'Avoir suivi la formation « Inspecteurs nationaux en sûreté de l’aviation civile ».',
					'Exercer des fonctions d’inspection au sein de l’autorité compétente.',
				),
			),
			'en'         => array(
				'title'     => 'AVSEC Inspector Refresh Course',
				'duree'     => '3 days',
				'goal'      => 'To allow certified national aviation security inspectors to refresh their knowledge and maintain their qualification in line with developments in Annex 17 and the ICAO Aviation Security Manual (Doc 8973). Delivered on demand, in Tunisia or at the requesting party’s site.',
				'objectifs' => array(),
				'modules'   => array(),
				'public'    => 'Serving national civil aviation security inspectors who have already completed the corresponding initial course.',
				'prerequis' => array(
					'Completion of the Aviation Security National Inspectors Course.',
					'Currently performing inspection duties within the appropriate authority.',
				),
			),
		),

		array(
			'key'        => 'avsec-recyclage-instructeurs',
			'certificat' => 1,
			'fr'         => array(
				'title'     => 'Recyclage des instructeurs nationaux',
				'duree'     => '3 jours',
				'goal'      => 'Permettre aux instructeurs nationaux en sûreté de l’aviation civile déjà certifiés d’actualiser leurs compétences pédagogiques et leurs connaissances techniques, et de maintenir leur qualification d’instructeur. Ce cours est dispensé à la demande, en Tunisie ou sur le site du bénéficiaire.',
				'objectifs' => array(),
				'modules'   => array(),
				'public'    => 'Instructeurs nationaux en sûreté de l’aviation civile en fonction, ayant déjà suivi la formation initiale correspondante.',
				'prerequis' => array(
					'Avoir suivi la formation « Instructeurs nationaux en sûreté de l’aviation civile ».',
					'Assurer des activités d’instruction en sûreté de l’aviation.',
				),
			),
			'en'         => array(
				'title'     => 'AVSEC Instructor Refresh Course',
				'duree'     => '3 days',
				'goal'      => 'To allow certified national aviation security instructors to refresh their instructional skills and technical knowledge and to maintain their instructor qualification. Delivered on demand, in Tunisia or at the requesting party’s site.',
				'objectifs' => array(),
				'modules'   => array(),
				'public'    => 'Serving national civil aviation security instructors who have already completed the corresponding initial course.',
				'prerequis' => array(
					'Completion of the Aviation Security National Instructors Course.',
					'Currently delivering aviation security training.',
				),
			),
		),

	);
}

/**
 * Construit une liste HTML `<ul>` à partir d'un tableau d'intitulés (échappés).
 *
 * @param array<int,string> $items Éléments.
 * @return string HTML `<ul>…</ul>` ou '' si vide.
 */
function afsac_import_html_list( $items ) {
	if ( empty( $items ) || ! is_array( $items ) ) {
		return '';
	}
	$out = '';
	foreach ( $items as $item ) {
		$out .= '<li>' . esc_html( $item ) . '</li>';
	}
	return '<ul>' . $out . '</ul>';
}

/**
 * Prépare un champ « riche » (public cible) : liste si tableau, paragraphe sinon.
 *
 * @param array|string $value Valeur.
 * @return string HTML.
 */
function afsac_import_richtext( $value ) {
	if ( is_array( $value ) ) {
		return afsac_import_html_list( $value );
	}
	$value = trim( (string) $value );
	return '' === $value ? '' : '<p>' . esc_html( $value ) . '</p>';
}

/**
 * Écrit un champ ACF (ou une méta de repli si ACF est absent).
 *
 * @param int    $post_id ID du post.
 * @param string $name    Nom du champ.
 * @param mixed  $value   Valeur.
 * @return void
 */
function afsac_import_set_field( $post_id, $name, $value ) {
	if ( function_exists( 'update_field' ) ) {
		update_field( $name, $value, $post_id );
	} else {
		update_post_meta( $post_id, $name, $value );
	}
}

/**
 * Crée (ou réutilise) une paire de termes FR↔EN et les relie via Polylang.
 *
 * Réutilise afsac_seed_term() (idempotent, Polylang-aware) de seed-terms.php.
 *
 * @param string $fr_name  Nom du terme FR.
 * @param string $en_name  Nom du terme EN.
 * @param string $taxonomy Taxonomie.
 * @param array  $report   Compteur (created/reused) passé par référence.
 * @return array{fr:int,en:int} IDs des termes (0 si échec).
 */
function afsac_import_term_pair( $fr_name, $en_name, $taxonomy, &$report ) {
	$fr = afsac_seed_term( $fr_name, $taxonomy, 'fr', $report );
	$en = afsac_seed_term( $en_name, $taxonomy, 'en', $report );

	if ( $fr && $en && function_exists( 'pll_save_term_translations' ) ) {
		pll_save_term_translations(
			array(
				'fr' => $fr,
				'en' => $en,
			)
		);
	}

	return array(
		'fr' => (int) $fr,
		'en' => (int) $en,
	);
}

/**
 * Insère ou met à jour une formation dans une langue donnée + champs + taxos.
 *
 * @param array  $d       Données de la formation pour la langue.
 * @param string $key     Clé stable de la formation.
 * @param string $lang    Langue Polylang (« fr »/« en »).
 * @param array  $terms   IDs de termes pour CETTE langue (famille/area/langue).
 * @param array  $report  Compteur passé par référence.
 * @return int ID du post (0 si échec).
 */
function afsac_seed_formation_post( $d, $key, $lang, $terms, &$report ) {
	$existing = get_posts(
		array(
			'post_type'      => 'afsac_formation',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'lang'           => $lang,
			'meta_key'       => '_afsac_import_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);

	$goal    = isset( $d['goal'] ) ? (string) $d['goal'] : '';
	$content = '' !== $goal ? '<!-- wp:paragraph --><p>' . esc_html( $goal ) . '</p><!-- /wp:paragraph -->' : '';
	$excerpt = '' !== $goal ? wp_trim_words( $goal, 32, '…' ) : '';

	$postarr = array(
		'post_type'    => 'afsac_formation',
		'post_status'  => 'publish',
		'post_title'   => $d['title'],
		'post_excerpt' => $excerpt,
		'post_content' => $content,
	);

	if ( ! empty( $existing ) ) {
		$postarr['ID'] = (int) $existing[0];

		/*
		 * SLUG FIGÉ. Sans `post_name`, WordPress le recalcule à partir du titre dès
		 * que celui-ci change : le 03/09/2026 un simple ajustement d'intitulé a
		 * ainsi déplacé une fiche déjà en ligne (404 sur l'ancienne URL). Le seed
		 * doit pouvoir corriger un libellé sans jamais casser un lien.
		 */
		$existing_slug = get_post_field( 'post_name', $postarr['ID'] );
		if ( '' !== (string) $existing_slug ) {
			$postarr['post_name'] = $existing_slug;
		}

		$post_id = wp_update_post( $postarr, true );
		$report['updated']++;
	} else {
		$post_id = wp_insert_post( $postarr, true );
		$report['created']++;
	}

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return 0;
	}
	$post_id = (int) $post_id;

	// Langue Polylang AVANT l'affectation des termes (cohérence par langue).
	if ( function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $post_id, $lang );
	}

	// Taxonomies : famille AVSEC, domaine (area), langue de la formation.
	if ( ! empty( $terms['famille'] ) ) {
		wp_set_object_terms( $post_id, array( (int) $terms['famille'] ), 'afsac_famille', false );
	}
	if ( ! empty( $terms['area'] ) ) {
		wp_set_object_terms( $post_id, array( (int) $terms['area'] ), 'afsac_area', false );
	}
	// Langues de DISPENSATION (multiples : un cours peut être donné en FR + EN + AR).
	// Ce n'est PAS la langue de la fiche : celle-ci est portée par Polylang.
	if ( ! empty( $terms['langues'] ) ) {
		wp_set_object_terms( $post_id, array_map( 'intval', (array) $terms['langues'] ), 'afsac_langue', false );
	}

	// Champs ACF structurés.
	afsac_import_set_field( $post_id, 'afsac_duree', isset( $d['duree'] ) ? $d['duree'] : '' );

	/*
	 * Tarif : relevé dans les brochures 2026 — EUR côté français, USD côté
	 * anglais, comme les deux documents. Les cours « à la demande » n'y sont pas
	 * chiffrés : leur `frais` est absent du dataset et le champ reste vide, ce
	 * que la fiche et le calendrier savent afficher (« sur demande »).
	 */
	if ( isset( $d['frais'] ) && '' !== (string) $d['frais'] ) {
		afsac_import_set_field( $post_id, 'afsac_frais_montant', (int) $d['frais'] );
		afsac_import_set_field( $post_id, 'afsac_devise', ( 'fr' === $lang ) ? 'EUR' : 'USD' );
	}
	afsac_import_set_field( $post_id, 'afsac_objectifs', afsac_import_html_list( isset( $d['objectifs'] ) ? $d['objectifs'] : array() ) );
	afsac_import_set_field( $post_id, 'afsac_structure', implode( "\n", isset( $d['modules'] ) ? $d['modules'] : array() ) );
	afsac_import_set_field( $post_id, 'afsac_public_cible', afsac_import_richtext( isset( $d['public'] ) ? $d['public'] : '' ) );
	afsac_import_set_field( $post_id, 'afsac_prerequis', implode( "\n", isset( $d['prerequis'] ) ? $d['prerequis'] : array() ) );
	afsac_import_set_field( $post_id, 'afsac_developpe_par', 'ICAO · OACI' );
	afsac_import_set_field( $post_id, 'afsac_developpe_par_detail', 'International Civil Aviation Organization · Montréal, Canada' );

	if ( ! empty( $d['certificat'] ) ) {
		afsac_import_set_field( $post_id, 'afsac_certificat', 1 );
		afsac_import_set_field( $post_id, 'afsac_certificat_intitule', 'fr' === $lang ? 'Certificat de l’OACI' : 'ICAO Certificate' );
	}

	// Marqueurs d'idempotence.
	update_post_meta( $post_id, '_afsac_import', 1 );
	update_post_meta( $post_id, '_afsac_import_key', $key );

	return $post_id;
}

/**
 * Exécute le seed complet des formations AVSEC (paires FR↔EN + EN seul).
 *
 * @return array{ok:bool,message:string,created:int,updated:int,linked:int,pairs:array}
 */
function afsac_seed_formations() {
	$report = array(
		'ok'      => false,
		'message' => '',
		'created' => 0,
		'updated' => 0,
		'linked'  => 0,
		'pairs'   => array(),
	);

	if ( ! function_exists( 'pll_set_post_language' ) || ! function_exists( 'pll_save_post_translations' ) || ! function_exists( 'pll_languages_list' ) ) {
		$report['message'] = 'Polylang est requis (fonctions pll_* introuvables).';
		return $report;
	}
	$langs = pll_languages_list();
	if ( ! in_array( 'fr', $langs, true ) || ! in_array( 'en', $langs, true ) ) {
		$report['message'] = 'Les langues Polylang « fr » et « en » doivent être configurées. Trouvées : ' . implode( ', ', $langs );
		return $report;
	}

	// 1) Termes de rattachement (idempotents + reliés). Compteur séparé.
	$termrep = array(
		'created' => 0,
		'reused'  => 0,
	);
	$fam    = afsac_import_term_pair( 'AVSEC', 'AVSEC', 'afsac_famille', $termrep );
	$area   = afsac_import_term_pair( 'Sûreté de l’aviation', 'Aviation Security', 'afsac_area', $termrep );
	$lg_fr  = afsac_import_term_pair( 'Français', 'French', 'afsac_langue', $termrep );  // concept « français »
	$lg_en  = afsac_import_term_pair( 'Anglais', 'English', 'afsac_langue', $termrep );  // concept « anglais »

	/*
	 * Table des termes de LANGUE DE DISPENSATION : [concept][langue du post] => term_id.
	 * Modèle validé avec le client : afsac_langue liste les langues dans lesquelles le
	 * cours est DISPENSÉ (pas la langue de la fiche, portée par Polylang). Un post FR
	 * porte donc les termes côté FR (« Français », « Anglais »).
	 *
	 * Le concept « arabe » n'y figure plus : le terme existe encore en base pour les
	 * fiches hors AVSEC, mais le seed ne l'affecte ni ne le recrée.
	 */
	$lang_terms = array(
		'fr' => array( 'fr' => $lg_fr['fr'], 'en' => $lg_fr['en'] ),
		'en' => array( 'fr' => $lg_en['fr'], 'en' => $lg_en['en'] ),
	);

	$base_fr = array(
		'famille' => $fam['fr'],
		'area'    => $area['fr'],
	);
	$base_en = array(
		'famille' => $fam['en'],
		'area'    => $area['en'],
	);

	// 2) Formations : FR + EN, puis liaison de la paire (si les deux existent).
	foreach ( afsac_import_courses() as $course ) {
		$key   = $course['key'];
		$fr_id = 0;
		$en_id = 0;

		/*
		 * Langues de DISPENSATION : FR + EN par défaut. Un cours peut surcharger via
		 * `langues` (ex. 'langues' => array( 'en' ) si un cours n'existe qu'en anglais).
		 *
		 * L'ARABE A ÉTÉ RETIRÉ (demande client, 03/09/2026 : « just français et anglais,
		 * l'arabe non »), comme il l'a déjà été de la brochure, de la vitrine et du
		 * sélecteur du calendrier. La note A de la brochure « Aviation Security Annual
		 * Training Program 2026 » annonce pourtant trois langues (EN/FR/AR) : le cours
		 * RESTE dispensable en arabe sur demande, le site ne l'annonce simplement plus.
		 */
		$delivered = ! empty( $course['langues'] )
			? (array) $course['langues']
			: array( 'fr', 'en' );

		// IDs de termes, résolus dans la langue de CHAQUE post.
		$langues_fr = array();
		$langues_en = array();
		foreach ( $delivered as $afsac_d ) {
			if ( ! isset( $lang_terms[ $afsac_d ] ) ) {
				continue;
			}
			if ( $lang_terms[ $afsac_d ]['fr'] ) {
				$langues_fr[] = (int) $lang_terms[ $afsac_d ]['fr'];
			}
			if ( $lang_terms[ $afsac_d ]['en'] ) {
				$langues_en[] = (int) $lang_terms[ $afsac_d ]['en'];
			}
		}

		$terms_fr = $base_fr + array( 'langues' => $langues_fr );
		$terms_en = $base_en + array( 'langues' => $langues_en );

		if ( isset( $course['fr'] ) ) {
			$data          = $course['fr'];
			$data['certificat'] = isset( $course['certificat'] ) ? $course['certificat'] : 0;
			$fr_id         = afsac_seed_formation_post( $data, $key, 'fr', $terms_fr, $report );
		}
		if ( isset( $course['en'] ) ) {
			$data          = $course['en'];
			$data['certificat'] = isset( $course['certificat'] ) ? $course['certificat'] : 0;
			$en_id         = afsac_seed_formation_post( $data, $key, 'en', $terms_en, $report );
		}

		if ( $fr_id && $en_id ) {
			pll_save_post_translations(
				array(
					'fr' => $fr_id,
					'en' => $en_id,
				)
			);
			$report['linked']++;
			$report['pairs'][] = array(
				'fr' => $course['fr']['title'] . ' (#' . $fr_id . ')',
				'en' => $course['en']['title'] . ' (#' . $en_id . ')',
			);
		} elseif ( $en_id ) {
			$report['pairs'][] = array(
				'fr' => '(EN uniquement)',
				'en' => $course['en']['title'] . ' (#' . $en_id . ')',
			);
		}
	}

	$report['ok']      = true;
	$report['message'] = sprintf(
		'%d formation(s) créée(s), %d mise(s) à jour, %d paire(s) FR↔EN liée(s) ; termes de rattachement : %d créé(s)/%d réutilisé(s).',
		$report['created'],
		$report['updated'],
		$report['linked'],
		$termrep['created'],
		$termrep['reused']
	);

	return $report;
}

/**
 * Commande WP-CLI : wp afsac seed-formations
 *
 * @return void
 */
function afsac_cli_seed_formations() {
	$r = afsac_seed_formations();
	if ( ! $r['ok'] ) {
		WP_CLI::error( $r['message'] );
		return;
	}
	WP_CLI::log( '— Paires FR↔EN —' );
	if ( $r['pairs'] ) {
		WP_CLI\Utils\format_items( 'table', $r['pairs'], array( 'fr', 'en' ) );
	}
	WP_CLI::success( $r['message'] );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'afsac seed-formations', 'afsac_cli_seed_formations' );
}

/**
 * Repli sans WP-CLI : page d'admin Outils → « Import fiches AVSEC ».
 *
 * Bouton protégé (capacité manage_options + nonce) qui lance
 * afsac_seed_formations() et affiche le compte rendu. Nécessaire car le
 * bootstrap WP-CLI échoue sur certains postes (« Prérequis non remplis »).
 *
 * @return void
 */
function afsac_import_admin_menu() {
	add_management_page(
		__( 'Import fiches AVSEC', 'afsac' ),
		__( 'Import fiches AVSEC', 'afsac' ),
		'manage_options',
		'afsac-import-formations',
		'afsac_import_admin_page'
	);
}
add_action( 'admin_menu', 'afsac_import_admin_menu' );

/**
 * Rendu de la page d'admin d'import (et exécution sur soumission).
 *
 * @return void
 */
function afsac_import_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$report = null;
	if ( isset( $_POST['afsac_import_run'] ) && check_admin_referer( 'afsac_import_formations' ) ) {
		$report = afsac_seed_formations();
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Import des fiches AVSEC', 'afsac' ) . '</h1>';
	echo '<p>' . esc_html__( 'Crée / met à jour les formations AVSEC (FR + EN) à partir des fiches descriptives fournies. Idempotent : relancer met à jour sans dupliquer.', 'afsac' ) . '</p>';

	if ( is_array( $report ) ) {
		$class = $report['ok'] ? 'notice-success' : 'notice-error';
		echo '<div class="notice ' . esc_attr( $class ) . '"><p>' . esc_html( $report['message'] ) . '</p></div>';

		if ( ! empty( $report['pairs'] ) ) {
			echo '<table class="widefat striped"><thead><tr><th>FR</th><th>EN</th></tr></thead><tbody>';
			foreach ( $report['pairs'] as $pair ) {
				echo '<tr><td>' . esc_html( $pair['fr'] ) . '</td><td>' . esc_html( $pair['en'] ) . '</td></tr>';
			}
			echo '</tbody></table>';
		}
	}

	echo '<form method="post" style="margin-top:1em;">';
	wp_nonce_field( 'afsac_import_formations' );
	echo '<p><button type="submit" name="afsac_import_run" value="1" class="button button-primary">' . esc_html__( 'Lancer l’import', 'afsac' ) . '</button></p>';
	echo '</form>';
	echo '</div>';
}
