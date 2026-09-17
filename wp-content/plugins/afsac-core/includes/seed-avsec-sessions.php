<?php
/**
 * Seed idempotent des SESSIONS planifiées des cours AVSEC.
 *
 * Les fiches AVSEC importées par seed-formations.php sont des fiches de
 * catalogue : elles ne portent aucune date. La page Catalogue (volet AVSEC) et
 * le calendrier ont besoin de sessions pour afficher « prochaines sessions ».
 *
 * ⚠️ Depuis le 04/09/2026, ce calendrier n'est PLUS une démonstration : ce sont
 * les 63 sessions RÉELLES relevées dans les deux brochures du client — 35 dans
 * « Programme des Formations AVSEC/OACI 2026 » (FR) et 28 dans « Aviation
 * Security Annual Training Program 2026 » (EN). Les 11 sessions inventées qui
 * les précédaient (Alger, Casablanca, Dakar, dates 2027) ont été supprimées :
 * le client les avait repérées. Toute session ajoutée ici doit venir d'un
 * document, pas d'une extrapolation.
 *
 * Les dates couvrent l'année civile 2026 : la majeure partie est donc passée
 * quand on relit ce fichier. Le calendrier public n'affiche que les sessions à
 * venir, les autres restent en base comme historique de l'année.
 *
 * Règles respectées :
 *   - la session porte la MÊME langue Polylang que la fiche qu'elle vise (une
 *     session FR sous une fiche EN n'apparaîtrait dans aucune des deux listes) ;
 *   - la relation `_afsac_formation_id` stocke l'ID CANONIQUE (langue par
 *     défaut, FR) de la formation — cf. includes/relations.php ;
 *   - la fiche visée est retrouvée par sa clé stable `_afsac_import_key`, jamais
 *     par un ID en dur.
 *
 * Idempotent : marqueur `_afsac_import_session_key` → relancer met à jour, ne
 * duplique pas. Réutilise afsac_import_set_field() (seed-formations.php).
 *
 * Lancement : « wp afsac seed-avsec-sessions » OU Outils → « Sessions AVSEC ».
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calendrier planifié (dates au format Ymd attendu par le date picker ACF).
 *
 * `course` = clé d'import de la fiche visée ; `lang` = langue de la session, qui
 * doit exister pour cette fiche (« landside » n'existe qu'en anglais,
 * « maintenance des équipements » qu'en français).
 *
 * `key` = `2026-<langue>-<cours>-<MMJJ>` : stable, et lisible dans l'admin.
 * Un même cours revient plusieurs fois dans l'année, c'est la date qui distingue.
 *
 * @return array<int,array<string,string>>
 */
function afsac_avsec_planned_sessions() {
	return array(
		// --- Français : 35 sessions du programme 2026 ---
		array(
			'key'    => '2026-fr-formation-base-0202',
			'course' => 'avsec-formation-base',
			'lang'   => 'fr',
			'title'  => 'Formation de base — Tunis · 2–13 févr. 2026',
			'debut'  => '20260202',
			'fin'    => '20260213',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-superviseurs-0216',
			'course' => 'avsec-superviseurs',
			'lang'   => 'fr',
			'title'  => 'Superviseurs — Tunis · 16–20 févr. 2026',
			'debut'  => '20260216',
			'fin'    => '20260220',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-instructeurs-0223',
			'course' => 'avsec-instructeurs',
			'lang'   => 'fr',
			'title'  => 'Instructeurs — Tunis · 23 févr.–3 mars 2026',
			'debut'  => '20260223',
			'fin'    => '20260303',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-inspecteurs-0305',
			'course' => 'avsec-inspecteurs',
			'lang'   => 'fr',
			'title'  => 'Inspecteurs nationaux — Tunis · 5–13 mars 2026',
			'debut'  => '20260305',
			'fin'    => '20260313',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-fret-poste-0309',
			'course' => 'avsec-fret-poste',
			'lang'   => 'fr',
			'title'  => 'Fret et poste — Tunis · 9–13 mars 2026',
			'debut'  => '20260309',
			'fin'    => '20260313',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-responsables-0323',
			'course' => 'avsec-responsables',
			'lang'   => 'fr',
			'title'  => 'AVSEC Managers — Tunis · 23–31 mars 2026',
			'debut'  => '20260323',
			'fin'    => '20260331',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-imagerie-0406',
			'course' => 'avsec-imagerie',
			'lang'   => 'fr',
			'title'  => 'Imagerie radioscopique — Tunis · 6–10 avril 2026',
			'debut'  => '20260406',
			'fin'    => '20260410',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-maintenance-equipements-surete-0413',
			'course' => 'avsec-maintenance-equipements-surete',
			'lang'   => 'fr',
			'title'  => 'Maintenance des équipements — Tunis · 13–17 avril 2026',
			'debut'  => '20260413',
			'fin'    => '20260417',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-facilitation-0420',
			'course' => 'avsec-facilitation',
			'lang'   => 'fr',
			'title'  => 'Annexe 9 — Facilitation — Tunis · 20–24 avril 2026',
			'debut'  => '20260420',
			'fin'    => '20260424',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-behaviour-detection-0427',
			'course' => 'avsec-behaviour-detection',
			'lang'   => 'fr',
			'title'  => 'Détection des comportements — Tunis · 27 avril–1 mai 2026',
			'debut'  => '20260427',
			'fin'    => '20260501',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-culture-surete-0504',
			'course' => 'avsec-culture-surete',
			'lang'   => 'fr',
			'title'  => 'Culture de sûreté — Tunis · 4–7 mai 2026',
			'debut'  => '20260504',
			'fin'    => '20260507',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-responsables-0511',
			'course' => 'avsec-responsables',
			'lang'   => 'fr',
			'title'  => 'AVSEC Managers — Tunis · 11–19 mai 2026',
			'debut'  => '20260511',
			'fin'    => '20260519',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-formation-base-0601',
			'course' => 'avsec-formation-base',
			'lang'   => 'fr',
			'title'  => 'Formation de base — Tunis · 1–12 juin 2026',
			'debut'  => '20260601',
			'fin'    => '20260612',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-superviseurs-0615',
			'course' => 'avsec-superviseurs',
			'lang'   => 'fr',
			'title'  => 'Superviseurs — Tunis · 15–19 juin 2026',
			'debut'  => '20260615',
			'fin'    => '20260619',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-instructeurs-0622',
			'course' => 'avsec-instructeurs',
			'lang'   => 'fr',
			'title'  => 'Instructeurs — Tunis · 22–30 juin 2026',
			'debut'  => '20260622',
			'fin'    => '20260630',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-inspecteurs-0702',
			'course' => 'avsec-inspecteurs',
			'lang'   => 'fr',
			'title'  => 'Inspecteurs nationaux — Tunis · 2–10 juil. 2026',
			'debut'  => '20260702',
			'fin'    => '20260710',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-fret-poste-0713',
			'course' => 'avsec-fret-poste',
			'lang'   => 'fr',
			'title'  => 'Fret et poste — Tunis · 13–17 juil. 2026',
			'debut'  => '20260713',
			'fin'    => '20260717',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-responsables-0720',
			'course' => 'avsec-responsables',
			'lang'   => 'fr',
			'title'  => 'AVSEC Managers — Tunis · 20–28 juil. 2026',
			'debut'  => '20260720',
			'fin'    => '20260728',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-behaviour-detection-0803',
			'course' => 'avsec-behaviour-detection',
			'lang'   => 'fr',
			'title'  => 'Détection des comportements — Tunis · 3–7 août 2026',
			'debut'  => '20260803',
			'fin'    => '20260807',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-facilitation-0810',
			'course' => 'avsec-facilitation',
			'lang'   => 'fr',
			'title'  => 'Annexe 9 — Facilitation — Tunis · 10–14 août 2026',
			'debut'  => '20260810',
			'fin'    => '20260814',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-imagerie-0817',
			'course' => 'avsec-imagerie',
			'lang'   => 'fr',
			'title'  => 'Imagerie radioscopique — Tunis · 17–21 août 2026',
			'debut'  => '20260817',
			'fin'    => '20260821',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-maintenance-equipements-surete-0824',
			'course' => 'avsec-maintenance-equipements-surete',
			'lang'   => 'fr',
			'title'  => 'Maintenance des équipements — Tunis · 24–28 août 2026',
			'debut'  => '20260824',
			'fin'    => '20260828',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-fret-poste-0907',
			'course' => 'avsec-fret-poste',
			'lang'   => 'fr',
			'title'  => 'Fret et poste — Tunis · 7–11 sept. 2026',
			'debut'  => '20260907',
			'fin'    => '20260911',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-culture-surete-0921',
			'course' => 'avsec-culture-surete',
			'lang'   => 'fr',
			'title'  => 'Culture de sûreté — Tunis · 21–24 sept. 2026',
			'debut'  => '20260921',
			'fin'    => '20260924',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-risque-interne-0928',
			'course' => 'avsec-risque-interne',
			'lang'   => 'fr',
			'title'  => 'Risque interne — Tunis · 28 sept.–2 oct. 2026',
			'debut'  => '20260928',
			'fin'    => '20261002',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-formation-base-1005',
			'course' => 'avsec-formation-base',
			'lang'   => 'fr',
			'title'  => 'Formation de base — Tunis · 5–16 oct. 2026',
			'debut'  => '20261005',
			'fin'    => '20261016',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-superviseurs-1019',
			'course' => 'avsec-superviseurs',
			'lang'   => 'fr',
			'title'  => 'Superviseurs — Tunis · 19–23 oct. 2026',
			'debut'  => '20261019',
			'fin'    => '20261023',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-instructeurs-1026',
			'course' => 'avsec-instructeurs',
			'lang'   => 'fr',
			'title'  => 'Instructeurs — Tunis · 26 oct.–3 nov. 2026',
			'debut'  => '20261026',
			'fin'    => '20261103',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-inspecteurs-1104',
			'course' => 'avsec-inspecteurs',
			'lang'   => 'fr',
			'title'  => 'Inspecteurs nationaux — Tunis · 4–12 nov. 2026',
			'debut'  => '20261104',
			'fin'    => '20261112',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-fret-poste-1116',
			'course' => 'avsec-fret-poste',
			'lang'   => 'fr',
			'title'  => 'Fret et poste — Tunis · 16–20 nov. 2026',
			'debut'  => '20261116',
			'fin'    => '20261120',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-responsables-1123',
			'course' => 'avsec-responsables',
			'lang'   => 'fr',
			'title'  => 'AVSEC Managers — Tunis · 23 nov.–1 déc. 2026',
			'debut'  => '20261123',
			'fin'    => '20261201',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-risque-interne-1130',
			'course' => 'avsec-risque-interne',
			'lang'   => 'fr',
			'title'  => 'Risque interne — Tunis · 30 nov.–4 déc. 2026',
			'debut'  => '20261130',
			'fin'    => '20261204',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-imagerie-1207',
			'course' => 'avsec-imagerie',
			'lang'   => 'fr',
			'title'  => 'Imagerie radioscopique — Tunis · 7–11 déc. 2026',
			'debut'  => '20261207',
			'fin'    => '20261211',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-behaviour-detection-1214',
			'course' => 'avsec-behaviour-detection',
			'lang'   => 'fr',
			'title'  => 'Détection des comportements — Tunis · 14–18 déc. 2026',
			'debut'  => '20261214',
			'fin'    => '20261218',
			'lieu'   => 'Tunis, Tunisie',
		),
		array(
			'key'    => '2026-fr-maintenance-equipements-surete-1214',
			'course' => 'avsec-maintenance-equipements-surete',
			'lang'   => 'fr',
			'title'  => 'Maintenance des équipements — Tunis · 14–18 déc. 2026',
			'debut'  => '20261214',
			'fin'    => '20261218',
			'lieu'   => 'Tunis, Tunisie',
		),

		// --- English : 28 sessions du programme 2026 ---
		array(
			'key'    => '2026-en-formation-base-0202',
			'course' => 'avsec-formation-base',
			'lang'   => 'en',
			'title'  => 'Basic Course — Tunis · 2–6 Feb 2026',
			'debut'  => '20260202',
			'fin'    => '20260206',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-superviseurs-0216',
			'course' => 'avsec-superviseurs',
			'lang'   => 'en',
			'title'  => 'Supervisors — Tunis · 16–20 Feb 2026',
			'debut'  => '20260216',
			'fin'    => '20260220',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-instructeurs-0223',
			'course' => 'avsec-instructeurs',
			'lang'   => 'en',
			'title'  => 'National Instructors — Tunis · 23–27 Feb 2026',
			'debut'  => '20260223',
			'fin'    => '20260227',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-inspecteurs-0305',
			'course' => 'avsec-inspecteurs',
			'lang'   => 'en',
			'title'  => 'National Inspectors — Tunis · 5–13 Mar 2026',
			'debut'  => '20260305',
			'fin'    => '20260313',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-responsables-0323',
			'course' => 'avsec-responsables',
			'lang'   => 'en',
			'title'  => 'AVSEC Managers — Tunis · 23–31 Mar 2026',
			'debut'  => '20260323',
			'fin'    => '20260331',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-facilitation-0420',
			'course' => 'avsec-facilitation',
			'lang'   => 'en',
			'title'  => 'ICAO Annex 9 — Tunis · 20–24 Apr 2026',
			'debut'  => '20260420',
			'fin'    => '20260424',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-behaviour-detection-0427',
			'course' => 'avsec-behaviour-detection',
			'lang'   => 'en',
			'title'  => 'Behaviour Detection — Tunis · 27 Apr–1 May 2026',
			'debut'  => '20260427',
			'fin'    => '20260501',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-culture-surete-0504',
			'course' => 'avsec-culture-surete',
			'lang'   => 'en',
			'title'  => 'Security Culture — Tunis · 4–7 May 2026',
			'debut'  => '20260504',
			'fin'    => '20260507',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-landside-security-0511',
			'course' => 'avsec-landside-security',
			'lang'   => 'en',
			'title'  => 'Airport Landside Security — Tunis · 11–15 May 2026',
			'debut'  => '20260511',
			'fin'    => '20260515',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-responsables-0511',
			'course' => 'avsec-responsables',
			'lang'   => 'en',
			'title'  => 'AVSEC Managers — Tunis · 11–19 May 2026',
			'debut'  => '20260511',
			'fin'    => '20260519',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-formation-base-0601',
			'course' => 'avsec-formation-base',
			'lang'   => 'en',
			'title'  => 'Basic Course — Tunis · 1–5 Jun 2026',
			'debut'  => '20260601',
			'fin'    => '20260605',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-superviseurs-0615',
			'course' => 'avsec-superviseurs',
			'lang'   => 'en',
			'title'  => 'Supervisors — Tunis · 15–19 Jun 2026',
			'debut'  => '20260615',
			'fin'    => '20260619',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-instructeurs-0622',
			'course' => 'avsec-instructeurs',
			'lang'   => 'en',
			'title'  => 'National Instructors — Tunis · 22–26 Jun 2026',
			'debut'  => '20260622',
			'fin'    => '20260626',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-inspecteurs-0702',
			'course' => 'avsec-inspecteurs',
			'lang'   => 'en',
			'title'  => 'National Inspectors — Tunis · 2–10 Jul 2026',
			'debut'  => '20260702',
			'fin'    => '20260710',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-landside-security-0713',
			'course' => 'avsec-landside-security',
			'lang'   => 'en',
			'title'  => 'Airport Landside Security — Tunis · 13–17 Jul 2026',
			'debut'  => '20260713',
			'fin'    => '20260717',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-responsables-0720',
			'course' => 'avsec-responsables',
			'lang'   => 'en',
			'title'  => 'AVSEC Managers — Tunis · 20–28 Jul 2026',
			'debut'  => '20260720',
			'fin'    => '20260728',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-behaviour-detection-0803',
			'course' => 'avsec-behaviour-detection',
			'lang'   => 'en',
			'title'  => 'Behaviour Detection — Tunis · 3–7 Aug 2026',
			'debut'  => '20260803',
			'fin'    => '20260807',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-facilitation-0810',
			'course' => 'avsec-facilitation',
			'lang'   => 'en',
			'title'  => 'ICAO Annex 9 — Tunis · 10–14 Aug 2026',
			'debut'  => '20260810',
			'fin'    => '20260814',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-landside-security-0914',
			'course' => 'avsec-landside-security',
			'lang'   => 'en',
			'title'  => 'Airport Landside Security — Tunis · 14–18 Sep 2026',
			'debut'  => '20260914',
			'fin'    => '20260918',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-culture-surete-0921',
			'course' => 'avsec-culture-surete',
			'lang'   => 'en',
			'title'  => 'Security Culture — Tunis · 21–24 Sep 2026',
			'debut'  => '20260921',
			'fin'    => '20260924',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-risque-interne-0928',
			'course' => 'avsec-risque-interne',
			'lang'   => 'en',
			'title'  => 'Insider Risk — Tunis · 28 Sep–2 Oct 2026',
			'debut'  => '20260928',
			'fin'    => '20261002',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-formation-base-1005',
			'course' => 'avsec-formation-base',
			'lang'   => 'en',
			'title'  => 'Basic Course — Tunis · 5–9 Oct 2026',
			'debut'  => '20261005',
			'fin'    => '20261009',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-superviseurs-1019',
			'course' => 'avsec-superviseurs',
			'lang'   => 'en',
			'title'  => 'Supervisors — Tunis · 19–23 Oct 2026',
			'debut'  => '20261019',
			'fin'    => '20261023',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-instructeurs-1026',
			'course' => 'avsec-instructeurs',
			'lang'   => 'en',
			'title'  => 'National Instructors — Tunis · 26–30 Oct 2026',
			'debut'  => '20261026',
			'fin'    => '20261030',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-inspecteurs-1104',
			'course' => 'avsec-inspecteurs',
			'lang'   => 'en',
			'title'  => 'National Inspectors — Tunis · 4–12 Nov 2026',
			'debut'  => '20261104',
			'fin'    => '20261112',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-responsables-1123',
			'course' => 'avsec-responsables',
			'lang'   => 'en',
			'title'  => 'AVSEC Managers — Tunis · 23 Nov–1 Dec 2026',
			'debut'  => '20261123',
			'fin'    => '20261201',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-risque-interne-1130',
			'course' => 'avsec-risque-interne',
			'lang'   => 'en',
			'title'  => 'Insider Risk — Tunis · 30 Nov–4 Dec 2026',
			'debut'  => '20261130',
			'fin'    => '20261204',
			'lieu'   => 'Tunis, Tunisia',
		),
		array(
			'key'    => '2026-en-behaviour-detection-1214',
			'course' => 'avsec-behaviour-detection',
			'lang'   => 'en',
			'title'  => 'Behaviour Detection — Tunis · 14–18 Dec 2026',
			'debut'  => '20261214',
			'fin'    => '20261218',
			'lieu'   => 'Tunis, Tunisia',
		),
		);
}

/**
 * Retrouve une fiche AVSEC par sa clé d'import, dans une langue donnée.
 *
 * @param string $key  Clé `_afsac_import_key`.
 * @param string $lang Langue Polylang (« fr » | « en »).
 * @return int ID du post, ou 0 si absent dans cette langue.
 */
function afsac_avsec_find_course( $key, $lang ) {
	$found = get_posts(
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

	return ! empty( $found ) ? (int) $found[0] : 0;
}

/**
 * Crée ou met à jour une session AVSEC planifiée.
 *
 * @param array $s      Entrée du calendrier.
 * @param array $report Compteurs passés par référence.
 * @return int ID de la session, ou 0 si la fiche visée est introuvable.
 */
function afsac_seed_avsec_session( $s, &$report ) {
	$formation_id = afsac_avsec_find_course( $s['course'], $s['lang'] );
	if ( ! $formation_id ) {
		$report['skipped'][] = $s['key'] . ' (fiche « ' . $s['course'] . ' » absente en ' . strtoupper( $s['lang'] ) . ')';
		return 0;
	}

	// La relation vise TOUJOURS l'ID canonique (langue par défaut).
	$canonical_id = function_exists( 'afsac_get_default_lang_id' )
		? (int) afsac_get_default_lang_id( $formation_id )
		: $formation_id;

	$skey     = 'avsec-' . $s['key'];
	$existing = get_posts(
		array(
			'post_type'      => 'afsac_session',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'lang'           => $s['lang'],
			'meta_key'       => '_afsac_import_session_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $skey, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);

	$postarr = array(
		'post_type'   => 'afsac_session',
		'post_status' => 'publish',
		'post_title'  => $s['title'],
	);

	if ( ! empty( $existing ) ) {
		$postarr['ID'] = (int) $existing[0];
		$session_id    = wp_update_post( $postarr, true );
		$report['updated']++;
	} else {
		$session_id = wp_insert_post( $postarr, true );
		$report['created']++;
	}

	if ( is_wp_error( $session_id ) || ! $session_id ) {
		return 0;
	}
	$session_id = (int) $session_id;

	if ( function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $session_id, $s['lang'] );
	}

	update_post_meta( $session_id, '_afsac_formation_id', $canonical_id );

	afsac_import_set_field( $session_id, 'afsac_date_debut', $s['debut'] );
	afsac_import_set_field( $session_id, 'afsac_date_fin', $s['fin'] );
	afsac_import_set_field( $session_id, 'afsac_lieu', $s['lieu'] );
	afsac_import_set_field( $session_id, 'afsac_hote', 'AFSAC' );
	afsac_import_set_field( $session_id, 'afsac_statut', 'ouvert' );

	// Langue d'animation : terme de taxonomie apparié FR↔EN (idempotent).
	$langue = ( 'fr' === $s['lang'] )
		? afsac_import_term_pair( 'Français', 'French', 'afsac_langue', $report['terms'] )
		: afsac_import_term_pair( 'Anglais', 'English', 'afsac_langue', $report['terms'] );

	if ( ! empty( $langue[ $s['lang'] ] ) ) {
		wp_set_object_terms( $session_id, array( (int) $langue[ $s['lang'] ] ), 'afsac_langue', false );
		afsac_import_set_field( $session_id, 'afsac_langue_session', (int) $langue[ $s['lang'] ] );
	}

	update_post_meta( $session_id, '_afsac_import_session', 1 );
	update_post_meta( $session_id, '_afsac_import_session_key', $skey );

	$report['items'][] = array(
		'id'    => $session_id,
		'lang'  => strtoupper( $s['lang'] ),
		'title' => $s['title'],
		'debut' => $s['debut'],
	);

	return $session_id;
}

/**
 * Exécute le seed complet des sessions AVSEC.
 *
 * @return array{ok:bool,message:string,created:int,updated:int,skipped:array,terms:array,items:array}
 */
function afsac_seed_avsec_sessions() {
	$report = array(
		'ok'      => false,
		'message' => '',
		'created' => 0,
		'updated' => 0,
		'skipped' => array(),
		'terms'   => array(
			'created' => 0,
			'reused'  => 0,
		),
		'items'   => array(),
	);

	if ( ! function_exists( 'pll_set_post_language' ) ) {
		$report['message'] = 'Polylang est requis (fonctions pll_* introuvables).';
		return $report;
	}
	if ( ! function_exists( 'afsac_import_set_field' ) || ! function_exists( 'afsac_import_term_pair' ) ) {
		$report['message'] = 'Helpers d’import introuvables (seed-formations.php doit être chargé).';
		return $report;
	}

	foreach ( afsac_avsec_planned_sessions() as $s ) {
		afsac_seed_avsec_session( $s, $report );
	}

	$report['ok']      = true;
	$report['message'] = sprintf(
		'%d session(s) créée(s), %d mise(s) à jour%s.',
		$report['created'],
		$report['updated'],
		$report['skipped'] ? ' ; ' . count( $report['skipped'] ) . ' ignorée(s) : ' . implode( ', ', $report['skipped'] ) : ''
	);

	return $report;
}

/**
 * Commande WP-CLI : wp afsac seed-avsec-sessions
 *
 * @return void
 */
function afsac_cli_seed_avsec_sessions() {
	$r = afsac_seed_avsec_sessions();
	if ( ! $r['ok'] ) {
		WP_CLI::error( $r['message'] );
		return;
	}
	if ( $r['items'] ) {
		WP_CLI\Utils\format_items( 'table', $r['items'], array( 'id', 'lang', 'debut', 'title' ) );
	}
	WP_CLI::success( $r['message'] );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'afsac seed-avsec-sessions', 'afsac_cli_seed_avsec_sessions' );
}

/**
 * Repli non-CLI : page d'admin sous « Outils ».
 *
 * @return void
 */
function afsac_avsec_sessions_admin_menu() {
	add_management_page(
		__( 'Sessions AVSEC', 'afsac' ),
		__( 'Sessions AVSEC', 'afsac' ),
		'manage_options',
		'afsac-seed-avsec-sessions',
		'afsac_avsec_sessions_admin_page'
	);
}
add_action( 'admin_menu', 'afsac_avsec_sessions_admin_menu' );

/**
 * Rendu de la page d'admin (et exécution sur soumission).
 *
 * @return void
 */
function afsac_avsec_sessions_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$report = null;
	if ( isset( $_POST['afsac_avsec_sessions_run'] ) && check_admin_referer( 'afsac_seed_avsec_sessions' ) ) {
		$report = afsac_seed_avsec_sessions();
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Sessions AVSEC planifiées', 'afsac' ) . '</h1>';
	echo '<p>' . esc_html__( 'Crée / met à jour le calendrier des sessions AVSEC (dates, lieu, langue) sur les fiches importées. Idempotent : relancer met à jour sans dupliquer. Les dates restent modifiables ensuite dans Sessions.', 'afsac' ) . '</p>';

	if ( is_array( $report ) ) {
		$class = $report['ok'] ? 'notice-success' : 'notice-error';
		echo '<div class="notice ' . esc_attr( $class ) . '"><p>' . esc_html( $report['message'] ) . '</p></div>';

		if ( ! empty( $report['items'] ) ) {
			echo '<table class="widefat striped"><thead><tr><th>#</th><th>' . esc_html__( 'Langue', 'afsac' ) . '</th><th>' . esc_html__( 'Début', 'afsac' ) . '</th><th>' . esc_html__( 'Session', 'afsac' ) . '</th></tr></thead><tbody>';
			foreach ( $report['items'] as $item ) {
				echo '<tr><td>' . esc_html( $item['id'] ) . '</td><td>' . esc_html( $item['lang'] ) . '</td><td>' . esc_html( $item['debut'] ) . '</td><td>' . esc_html( $item['title'] ) . '</td></tr>';
			}
			echo '</tbody></table>';
		}
	}

	echo '<form method="post" style="margin-top:1em;">';
	wp_nonce_field( 'afsac_seed_avsec_sessions' );
	echo '<p><button type="submit" name="afsac_avsec_sessions_run" value="1" class="button button-primary">' . esc_html__( 'Planifier les sessions', 'afsac' ) . '</button></p>';
	echo '</form>';
	echo '</div>';
}
