<?php
/**
 * Amorçage du thème AFSAC.
 *
 * Présentation uniquement : aucune déclaration de CPT/taxonomie ici
 * (cf. plugin afsac-core, conformément à CLAUDE.md).
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Version du thème (cache-busting des assets).
 *
 * @var string
 */
/*
 * Clé de cache de TOUS les assets (style.css + scripts) : le numéro sert de
 * `?ver=` dans les URLs enqueue. À BUMPER À CHAQUE MODIFICATION de style.css ou
 * d'un fichier JS, sinon navigateurs et caches serveur continuent de servir
 * l'ancienne version sous la même URL (symptôme : markup neuf, style ancien).
 * 0.5.0 — refonte F&S / Catalogue / Calendrier / Qui sommes-nous.
 * 0.9.0 — accueil : carrousel de formations au format catalogue OACI (grille 2×2
 *         rotative) et refonte de « Pourquoi nous choisir » (anneau d'atouts) ;
 *         retrait des témoignages ; pied de page : brochure servie selon la
 *         langue, logo blanc, menu miroir du menu principal.
 * 0.9.1 — accueil : animations enrichies (entrée échelonnée des cartes, barre de
 *         progression du carrousel, flottement de l'emblème et filigrane
 *         « reflet ») ; chapô de « Pourquoi nous choisir » retiré ; emblème OACI
 *         régénéré (le recadrage précédent coupait le bas du logo).
 * 0.9.2 — accueil : cadence des deux carrousels ramenée à 2 s et animations
 *         d'AMBIANCE (reflet balayant chaque carte, onde sur la pastille du
 *         domaine) qui tournent en continu, sans attendre le survol.
 * 0.9.3 — cadences rejouées après retour client : 2 s était trop rapide. Grille
 *         de formations à 4 s, anneau « Pourquoi nous choisir » à 3,5 s ; les
 *         transitions retrouvent un peu d'ampleur (rotation de l'anneau 0,75 s).
 * 0.9.4 — verrou du Centre servi dans la LANGUE DE NAVIGATION (helper
 *         afsac_logo_centre()) : version française en FR, anglaise en EN, en
 *         haut comme en bas de page. Verrou anglais blanc généré pour le footer.
 * 0.9.5 — Formations & Services : section « Trouvez votre formation en 3 étapes »
 *         supprimée (demande client) ; piliers et cartes de services animés
 *         (reflet permanent sur les visuels, puces en cascade, icônes qui
 *         respirent, liseré d'accent au survol).
 * 0.9.6 — F&S : l'icône des cartes de service redevient blanche au survol (le
 *         SVG portait une couleur figée qui l'effaçait sur la pastille pleine).
 * 0.10.0 — Catalogue, volet AVSEC entièrement refait sur le modèle de TRAINAIR
 *         PLUS : 6 thématiques de sûreté en aplats de couleur (elles filtrent la
 *         liste), catalogue « cours certifiants / ateliers » filtrable
 *         (avsec-filters.js), et bandeau « prochaines sessions ». L'accès par
 *         langue est retiré. Correction au passage d'un décalage d'un jour sur
 *         toutes les dates de session (afsac_format_date, fuseau horaire).
 * 0.10.1 — Aplat de thématique actif : contour d'encre lisible sur les six teintes.
 * 0.11.0 — Retour client : plus de bleu FONCÉ. La bannière AVSEC passe en clair
 *         (jumelle de TRAINAIR PLUS) et les six aplats de thématique se limitent
 *         à deux bleus OACI en damier — le navy et le noir encre sont retirés.
 *         « Prochaines sessions AVSEC » adopte la carte illustrée de l'accueil :
 *         afsac_build_course_card() est sorti de home/formations-carousel.php
 *         pour être partagé, et course-card-icao accepte un bandeau `session`.
 * 0.11.1 — Coche du bouclier AVSEC en bleu OACI (elle restait en navy).
 * 0.12.0 — Catalogue, volet AVSEC simplifié (demande client) : les 6 aplats de
 *         thématique et la barre de filtres « Tout / Cours / Ateliers » sont
 *         retirés au profit d'UNE section coupée en deux — cours certifiants /
 *         ateliers OACI, illustration + texte de chaque côté — dont les deux
 *         « Voir plus » déplient la liste correspondante. avsec-filters.js est
 *         remplacé par avsec-catalogue.js.
 * 0.14.0 — Catalogue, volet AVSEC : texte de référence du client (07/08/2026).
 *         Deux partiels éditoriaux encadrent désormais le duo — avsec-cadre
 *         (Annexe 17 / Annexe 9, réseau ASTC) avant, avsec-programmes (les
 *         thématiques des ASTP et des ateliers + mot de fin « No Country Left
 *         Behind ») après.
 * 0.15.0 — Calendrier des sessions (demande client 07/08/2026) : les onglets
 *         filtrent par PROGRAMME (TRAINAIR PLUS / AVSEC) au lieu du mode,
 *         l'arabe sort du sélecteur de langue, le tri alphabétique disparaît
 *         (ordre chronologique imposé), et les tarifs sont servis dans la devise
 *         de la langue de navigation — EUR en FR, USD en EN (afsac_price_display,
 *         plugin). Toute l'échelle typographique de la page est revue à la
 *         hausse : vignettes 200 × 130, titres, dates, badges, prix, pagination.
 * 0.15.2 — Catalogue, volet AVSEC : texte de référence de l'OACI fourni par le
 *         client (07/08/2026). Le cadre international (Annexe 17 / Annexe 9,
 *         réseau ASTC) ouvre le volet avec UN paragraphe visible ; les contenus
 *         ASTP et ateliers sont REPLIÉS dans les deux cartes du duo, sous
 *         l'image — demande client : « trop de texte, fais minimaliste ».
 *         Nouveau composant partagé .afsac-more (<details> natif, sans
 *         JavaScript) ; textes justifiés ; mot de fin « No Country Left Behind ».
 * 0.16.0 — « Qui sommes-nous » : textes de référence du client (07/08/2026) sur
 *         quatre volets — Notre identité, Accréditations, Notre histoire et
 *         Mission & Vision. ⚠️ La chronologie officielle change : création en
 *         2008, TRAINAIR PLUS en 2016, ICAO ASTC en 2017 (le hero passe de
 *         « depuis 1981 » à « depuis 2008 »). La frise horizontale laisse place
 *         à un récit jalonné (.afsac-about__story, rail qui se dessine) ; les
 *         certificats deviennent deux blocs larges alternés portant chacun son
 *         sous-titre et ses trois paragraphes ; la vision passe en bloc bleu
 *         immersif (filigrane du globe OACI) face à quatre missions numérotées.
 *         Le volet « Perspectives » est RETIRÉ (son contenu était une
 *         extrapolation jamais validée) : la barre d'onglets passe de six à
 *         cinq entrées, .afsac-progtabs--hexa devient --penta.
 * 0.16.1 — « Qui sommes-nous », deux retours client du 07/08/2026 :
 *         1. les deux certificats reviennent CÔTE À CÔTE (cartes verticales en
 *            deux colonnes) au lieu de deux blocs larges empilés ;
 *         2. « Notre identité » est resserrée — chaque paragraphe long est coupé
 *            à une frontière de phrase, la seconde moitié passant derrière le
 *            dépliant partagé .afsac-more (<details> natif, ouverture animée
 *            sous .afsac-anim). Neuf dépliants sur la page, zéro mot perdu.
 * 0.19.0 — « Références & Témoignages », retours client du 11/08/2026 :
 *         1. « Nos partenaires » retrouve un bandeau DÉFILANT (la grille
 *            statique manquait de vie), sur DEUX lignes à sens opposés et en
 *            couleur : le part partagé gagne un paramètre `rows` et perd sa
 *            variante `layout => grid` ; l'accueil garde sa ligne unique ;
 *         2. « Nos références » : les trois grilles empilées (national /
 *            régional / international) deviennent trois bandeaux défilants à
 *            sens alterné, ce qui supprime l'essentiel du défilement vertical ;
 *         3. une référence SANS LOGO n'est plus affichée (19 fiches écartées sur
 *            78) : plus aucun monogramme de remplacement dans un mur de logos.
 * 0.21.0 — Demandes client du 12/08/2026 :
 *         1. formulaires — le contact gagne Prénom (requis), Fonction et
 *            Téléphone ; l'inscription gagne Téléphone (requis, elle avait déjà
 *            prénom et fonction). Assainissement/validation du numéro mutualisés
 *            dans le plugin (includes/form-helpers.php) ;
 *         2. page Contact — nouvelle section « Shorts » : les vidéos verticales de la
 *            chaîne YouTube de l'AFSAC, en rail 9/16 avec lecture en lightbox.
 *            Aucune iframe avant le clic ; liste éditable au Customizer
 *            (« Vidéos & Shorts »).
 * 0.22.0 — Demande client du 14/08/2026 : la brochure ne se télécharge plus d'un
 *          clic. La carte du pied de page ouvre un panneau « Recevoir la
 *          brochure » (e-mail requis) ; le PDF est ensuite servi par un lien à
 *          jeton, ce qui permet de savoir QUI l'a demandée et COMBIEN de
 *          personnes l'ont réellement téléchargée (plugin : CPT
 *          afsac_telechargement, includes/brochure.php). Le panneau est une
 *          fenêtre modale avec JavaScript, un formulaire dans le flux sans lui.
 * 0.23.0 — VISITE GUIDÉE (onboarding de première visite) : à sa première venue,
 *          le visiteur se voit proposer une visite en 7 étapes qui DÉSIGNENT
 *          tour à tour le menu, le catalogue, le calendrier, la recherche, la
 *          brochure et le contact (halo découpé dans un voile + bulle). Le
 *          passage est mémorisé par navigateur ; le pied de page garde un lien
 *          « Revoir la visite guidée » (et ?visite=1 sur l'accueil). Contenu et
 *          cibles côté serveur (afsac_tour_steps), moteur dans
 *          assets/js/afsac-tour.js, interrupteur au Customizer.
 * 0.35.2 — Correctif : la fenêtre « Recevoir la brochure » ne se fermait plus
 *          après l'envoi du formulaire. Les redirections du plugin finissent par
 *          #afsac-brochure : le panneau restait affiché par le filet de sécurité
 *          CSS `:target`, qu'un history.replaceState() ne suffit pas à lever.
 *          Le filet n'est désormais posé que si le script n'a pas démarré.
 */
define( 'AFSAC_THEME_VERSION', '0.42.1' );

/**
 * Réglages du thème (supports, menus, i18n).
 *
 * @return void
 */
function afsac_theme_setup() {
	load_theme_textdomain( 'afsac', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 56,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	/*
	 * Pas d'emplacement « pied de page » : la colonne « Liens utiles » du footer
	 * est un MIROIR de afsac_primary (demande client : mêmes entrées en haut et
	 * en bas). Cf. template-parts/footer/columns.php. L'ancien emplacement
	 * afsac_footer était déclaré mais rendu nulle part — un réglage fantôme.
	 */
	register_nav_menus(
		array(
			'afsac_primary' => __( 'Navigation principale', 'afsac' ),
			'afsac_topbar'  => __( 'Barre utilitaire (haut)', 'afsac' ),
		)
	);
}
add_action( 'after_setup_theme', 'afsac_theme_setup' );

/**
 * Anti-FOUC (global) : pose DEUX classes d'état AVANT le premier paint.
 *
 * - `afsac-js`   : JavaScript est disponible. Toujours posée. Les composants qui
 *   masquent une partie de leur contenu (pages du carrousel de formations,
 *   diapositives de « Pourquoi nous choisir ») ne le font que sous cette classe :
 *   sans JS tout reste affiché et navigable, et avec JS il n'y a pas de flash du
 *   contenu complet — main.js est chargé en pied de page, donc sa propre classe
 *   arriverait APRÈS le premier paint.
 * - `afsac-anim` : les animations sont autorisées (pas de prefers-reduced-motion).
 *   Toutes les règles qui MASQUENT (opacity:0 de .afsac-reveal) sont scopées ici,
 *   donc rien ne peut rester bloqué invisible.
 *
 * @return void
 */
function afsac_anim_no_fouc() {
	?>
<script>(function(d){var c=d.documentElement.classList;c.add('afsac-js');try{if(!matchMedia('(prefers-reduced-motion: reduce)').matches){c.add('afsac-anim');}}catch(e){}})(document);</script>
	<?php
}
add_action( 'wp_head', 'afsac_anim_no_fouc', 1 );

/**
 * Enfile les polices, styles et scripts du thème.
 *
 * @return void
 */
function afsac_enqueue_assets() {
	// Polices Google : Source Serif 4 (titres), Inter (corps), Cairo (arabe RTL).
	wp_enqueue_style(
		'afsac-fonts',
		'https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,wght@0,400;0,600;0,700;1,600&family=Inter:wght@400;500;600;700&family=Cairo:wght@400;600;700&display=swap',
		array(),
		null
	);

	// Feuille principale (toujours chargée, y compris en RTL).
	wp_enqueue_style( 'afsac-style', get_stylesheet_uri(), array( 'afsac-fonts' ), AFSAC_THEME_VERSION );

	// Surcharges RTL ajoutées EN COMPLÉMENT (pas en remplacement) pour l'arabe.
	if ( is_rtl() ) {
		wp_enqueue_style(
			'afsac-rtl',
			get_template_directory_uri() . '/style-rtl.css',
			array( 'afsac-style' ),
			AFSAC_THEME_VERSION
		);
	}

	// Script principal (menu mobile, recherche).
	wp_enqueue_script(
		'afsac-main',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		AFSAC_THEME_VERSION,
		true
	);

	// Moteur d'animations (révélation au scroll + compteurs) — GLOBAL : no-op sans
	// élément .afsac-reveal/.afsac-count sur la page.
	wp_enqueue_script(
		'afsac-animate',
		get_template_directory_uri() . '/assets/js/afsac-animate.js',
		array(),
		AFSAC_THEME_VERSION,
		true
	);

	// Modale de lecture des témoignages vidéo (page « Références & Témoignages »).
	// L'annuaire filtrable (refs-directory.js) a été retiré avec le registre.
	if ( is_page_template( 'template-references-temoignages.php' ) ) {
		wp_enqueue_script(
			'afsac-testimonials',
			get_template_directory_uri() . '/assets/js/afsac-testimonials.js',
			array(),
			AFSAC_THEME_VERSION,
			true
		);
	}

	/*
	 * Recherche / tri / pagination client. Sert l'archive d'un domaine OACI ET
	 * l'archive générique du CPT (même shell `.afsac-area`, mêmes lignes). Le
	 * volet AVSEC (?famille=avsec) a son propre balisage sans `[data-area-rows]` :
	 * le script y sortirait immédiatement, on évite quand même de le charger.
	 */
	$afsac_is_avsec = is_post_type_archive( 'afsac_formation' )
		&& isset( $_GET['famille'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Filtre de lecture.
		&& 'avsec' === sanitize_key( wp_unslash( $_GET['famille'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( is_tax( 'afsac_area' ) || ( is_post_type_archive( 'afsac_formation' ) && ! $afsac_is_avsec ) ) {
		wp_enqueue_script(
			'afsac-area-filters',
			get_template_directory_uri() . '/assets/js/area-filters.js',
			array(),
			AFSAC_THEME_VERSION,
			true
		);
	}

	/*
	 * Page calendrier : filtrage / tri / pagination client (jumeau d'area-filters).
	 * La VUE CARTE a été supprimée (demande client) : Leaflet et calendar-map.js
	 * ne sont donc plus chargés ici. La page contact garde sa propre carte, avec
	 * son propre enqueue de Leaflet (voir plus bas) — ne pas confondre.
	 */
	if ( is_page_template( 'template-calendrier.php' ) ) {
		wp_enqueue_script(
			'afsac-sessions-filters',
			get_template_directory_uri() . '/assets/js/sessions-filters.js',
			array(),
			AFSAC_THEME_VERSION,
			true
		);
	}

	// Page contact : carte Leaflet (marqueur unique, sans clé API) + tampon anti-spam.
	if ( is_page_template( 'template-contact.php' ) ) {
		wp_enqueue_style(
			'leaflet',
			'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
			array(),
			'1.9.4'
		);
		wp_enqueue_script(
			'leaflet',
			'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
			array(),
			'1.9.4',
			true
		);
		wp_enqueue_script(
			'afsac-contact-map',
			get_template_directory_uri() . '/assets/js/contact-map.js',
			array( 'leaflet' ),
			AFSAC_THEME_VERSION,
			true
		);
		$afsac_contact_coords = function_exists( 'afsac_contact_coords' ) ? afsac_contact_coords() : array();
		wp_localize_script(
			'afsac-contact-map',
			'afsacContactMap',
			array(
				'lat'   => isset( $afsac_contact_coords['lat'] ) ? $afsac_contact_coords['lat'] : '',
				'lng'   => isset( $afsac_contact_coords['lng'] ) ? $afsac_contact_coords['lng'] : '',
				'label' => get_bloginfo( 'name' ),
			)
		);
	}

	// Page inscription : script (time-trap + brouillon localStorage).
	if ( is_page_template( 'template-inscription.php' ) ) {
		wp_enqueue_script(
			'afsac-inscription',
			get_template_directory_uri() . '/assets/js/inscription.js',
			array(),
			AFSAC_THEME_VERSION,
			true
		);
	}

	// Diaporama de la section intro (accueil uniquement).
	if ( is_front_page() ) {
		wp_enqueue_script(
			'afsac-intro-slideshow',
			get_template_directory_uri() . '/assets/js/intro-slideshow.js',
			array(),
			AFSAC_THEME_VERSION,
			true
		);
	}

	/*
	 * Shorts YouTube : rail + lightbox. Le script est seulement ENREGISTRÉ ici ;
	 * c'est template-parts/shared/shorts.php qui l'enfile, au moment où il rend
	 * la section. La section peut ainsi être posée sur n'importe quelle page
	 * sans qu'on ait à tenir une liste de gabarits à jour ici.
	 */
	wp_register_script(
		'afsac-shorts',
		get_template_directory_uri() . '/assets/js/afsac-shorts.js',
		array(),
		AFSAC_THEME_VERSION,
		true
	);

	/*
	 * Visite guidée (onboarding de première visite). Même schéma que les Shorts :
	 * le script est seulement ENREGISTRÉ ici, et c'est
	 * template-parts/shared/tour.php qui l'enfile là où la visite est rendue
	 * (l'accueil). Rien n'est chargé sur les autres pages.
	 */
	wp_register_script(
		'afsac-tour',
		get_template_directory_uri() . '/assets/js/afsac-tour.js',
		array(),
		AFSAC_THEME_VERSION,
		true
	);

	/*
	 * Panneau « Recevoir la brochure » (e-mail demandé avant le PDF). La bande
	 * Documentation vit dans le pied de page, donc sur TOUTES les pages : le
	 * script est enfilé partout et sort de lui-même si le panneau n'est pas rendu
	 * (aucun PDF téléversé). Le formulaire fonctionne sans lui.
	 */
	wp_enqueue_script(
		'afsac-brochure',
		get_template_directory_uri() . '/assets/js/afsac-brochure.js',
		array(),
		AFSAC_THEME_VERSION,
		true
	);

	// Onglets : sélecteur de programme (catalogue) et 4 volets (Qui sommes-nous).
	if ( is_page_template( array( 'template-catalogue.php', 'template-qui-sommes-nous.php' ) ) ) {
		wp_enqueue_script(
			'afsac-programme-tabs',
			get_template_directory_uri() . '/assets/js/programme-tabs.js',
			array(),
			AFSAC_THEME_VERSION,
			true
		);
	}

	/*
	 * Le volet AVSEC du catalogue n'a plus de script : ses deux « Voir plus »
	 * dépliaient une liste sur place (avsec-catalogue.js), ils ouvrent désormais
	 * la page de liste ?famille=avsec&format=… (demande client 07/08/2026).
	 */

	/*
	 * Champ d'étoiles animé du hero « guidage » — GLOBAL, comme afsac-animate.
	 *
	 * Il était conditionné à une LISTE de gabarits, qui a cessé d'être à jour dès
	 * que guide-hero est devenu le hero commun : Références, Actualités et Contact
	 * affichaient le hero SANS son trait animé (le canvas restait vide), alors que
	 * Qui sommes-nous l'avait. Le script sort immédiatement s'il ne trouve aucun
	 * .afsac-guide-hero__stars : le charger partout coûte un no-op et supprime
	 * définitivement cette classe d'oubli.
	 */
	wp_enqueue_script(
		'afsac-guide-hero',
		get_template_directory_uri() . '/assets/js/afsac-guide-hero.js',
		array(),
		AFSAC_THEME_VERSION,
		true
	);

	/*
	 * Carrousel de formations (grille 2×2 rotative) et anneau « Pourquoi nous
	 * choisir » — GLOBAUX et auto-désactivants, même raisonnement que ci-dessus :
	 * chaque script sort immédiatement sans son marqueur data-*. Conditionner à
	 * une liste de gabarits finit toujours par se désynchroniser.
	 */
	wp_enqueue_script(
		'afsac-course-carousel',
		get_template_directory_uri() . '/assets/js/afsac-course-carousel.js',
		array(),
		AFSAC_THEME_VERSION,
		true
	);
	wp_enqueue_script(
		'afsac-pourquoi',
		get_template_directory_uri() . '/assets/js/afsac-pourquoi.js',
		array(),
		AFSAC_THEME_VERSION,
		true
	);

	// Page « Savoir plus » d'une actualité : jauge de lecture + copie du lien.
	// Même logique auto-désactivante que les deux scripts ci-dessus.
	wp_enqueue_script(
		'afsac-news',
		get_template_directory_uri() . '/assets/js/afsac-news.js',
		array(),
		AFSAC_THEME_VERSION,
		true
	);

	/*
	 * Hero vidéo (accueil) : injecte la <source> en desktop et hors
	 * reduced-motion uniquement. GLOBAL et auto-désactivant comme ci-dessus
	 * (sort immédiatement sans .afsac-video-hero__media dans la page).
	 */
	wp_enqueue_script(
		'afsac-hero-video',
		get_template_directory_uri() . '/assets/js/hero-video.js',
		array(),
		AFSAC_THEME_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'afsac_enqueue_assets' );

/**
 * Empêche la redirection canonique de ramener /actualites/page/N/ vers la page 1.
 *
 * Sur une Page, WordPress traite « page/N » comme une pagination intra-contenu et
 * redirige si la page n'a pas autant de sauts <!--nextpage-->. Le template
 * Actualités pagine une WP_Query secondaire : on neutralise ce redirect quand on
 * est sur ce template et qu'une page > 1 est demandée.
 *
 * @param string $redirect_url  URL de redirection proposée.
 * @param string $requested_url URL demandée.
 * @return string|false URL d'origine, ou false pour annuler la redirection.
 */
function afsac_news_keep_pagination( $redirect_url, $requested_url ) {
	if ( is_page_template( 'template-actualites.php' ) ) {
		$afsac_page = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		if ( $afsac_page > 1 ) {
			return false;
		}
	}
	return $redirect_url;
}
add_filter( 'redirect_canonical', 'afsac_news_keep_pagination', 10, 2 );

/**
 * Archive d'un domaine OACI : tri par titre + chargement complet (pas de
 * pagination), pour un filtrage 100 % client (mode / langue / recherche) sur le
 * jeu de cours du domaine (<= ~44). NOTE : la future recherche CATALOGUE GLOBALE
 * (~490 cours) devra passer en AJAX/serveur — ne pas généraliser ce -1.
 *
 * @param WP_Query $query Requête courante.
 * @return void
 */

function afsac_area_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_tax( 'afsac_area' ) ) {
		$query->set( 'posts_per_page', -1 );
		$query->set( 'orderby', 'title' );
		$query->set( 'order', 'ASC' );
	}

}
add_action( 'pre_get_posts', 'afsac_area_archive_query' );

/**
 * Archive des formations : filtre PRÉ-APPLIQUÉ depuis ?famille= / ?langue=
 * (cartes « département » de l'accueil). Tokens stables → 2 variantes de slug
 * Polylang (IN) : marche en FR ET EN. Priorité 99 + APPEND au tax_query existant
 * pour NE PAS écraser le filtre de langue de Polylang (sinon redirection /en/).
 *
 * @param WP_Query $query Requête courante.
 * @return void
 */
function afsac_formation_archive_filter( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'afsac_formation' ) ) {
		return;
	}
	// Slug par LANGUE COURANTE (jamais la variante d'une autre langue, sinon
	// Polylang bascule la requête et redirige vers /en/). Tokens stables côté URL.
	$afsac_lang = function_exists( 'pll_current_language' ) ? pll_current_language() : 'fr';
	if ( ! in_array( $afsac_lang, array( 'fr', 'en' ), true ) ) {
		$afsac_lang = 'fr';
	}
	$afsac_fam = array(
		'trainair' => array( 'fr' => 'trainair-plus-fr', 'en' => 'trainair-plus' ),
		'avsec'    => array( 'fr' => 'avsec-fr', 'en' => 'avsec' ),
	);
	$afsac_lng = array(
		'fr' => array( 'fr' => 'francais', 'en' => 'french' ),
		'en' => array( 'fr' => 'anglais', 'en' => 'english' ),
		'ar' => array( 'fr' => 'arabe', 'en' => 'arabic' ),
	);
	$afsac_f = isset( $_GET['famille'] ) ? sanitize_key( wp_unslash( $_GET['famille'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$afsac_l = isset( $_GET['langue'] ) ? sanitize_key( wp_unslash( $_GET['langue'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	/*
	 * Le rendu générique pagine CÔTÉ CLIENT (area-filters.js) : il lui faut donc
	 * tout le jeu d'un coup, sinon la recherche ne porterait que sur les 10 lignes
	 * de la page courante. Le volet AVSEC garde sa requête propre.
	 *
	 * ⚠️ Poids mesuré le 18/08/2026 : FR = 81 lignes / 203 Ko et 60 lignes pour
	 * ?famille=trainair, mais **EN = 358 lignes / 605 Ko** — les ~600 cours sans
	 * langue Polylang remontent tous côté anglais (cf. la note du même sujet dans
	 * afsac_build_course_card). Pas de troncature : mieux vaut une page lourde
	 * qu'un catalogue amputé en silence. Si ça devient gênant, la sortie est le
	 * passage en AJAX/serveur, déjà signalé en tête d'area-filters.js.
	 */
	if ( 'avsec' !== $afsac_f ) {
		$query->set( 'posts_per_page', -1 );
	}

	$afsac_clauses = array();
	if ( isset( $afsac_fam[ $afsac_f ][ $afsac_lang ] ) ) {
		$afsac_clauses[] = array(
			'taxonomy' => 'afsac_famille',
			'field'    => 'slug',
			'terms'    => array( $afsac_fam[ $afsac_f ][ $afsac_lang ] ),
		);
	}
	if ( isset( $afsac_lng[ $afsac_l ][ $afsac_lang ] ) ) {
		$afsac_clauses[] = array(
			'taxonomy' => 'afsac_langue',
			'field'    => 'slug',
			'terms'    => array( $afsac_lng[ $afsac_l ][ $afsac_lang ] ),
		);
	}
	if ( empty( $afsac_clauses ) ) {
		return;
	}

	// APPEND aux clauses existantes (dont celle de langue Polylang).
	$afsac_tax = $query->get( 'tax_query' );
	$afsac_tax = is_array( $afsac_tax ) ? $afsac_tax : array();
	foreach ( $afsac_clauses as $afsac_clause ) {
		$afsac_tax[] = $afsac_clause;
	}
	$query->set( 'tax_query', $afsac_tax ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
}
add_action( 'pre_get_posts', 'afsac_formation_archive_filter', 99 );

/**
 * noindex sur la page d'inscription (formulaire de capture, hors SEO).
 *
 * @param array $robots Directives wp_robots.
 * @return array
 */
function afsac_inscription_noindex( $robots ) {
	if ( is_page_template( 'template-inscription.php' ) ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
	} elseif ( is_page_template( 'template-espace-participant.php' ) ) {
		// Page d'attente (contenu mince) : noindex mais on garde « follow » (CTA internes).
		$robots['noindex'] = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'afsac_inscription_noindex' );

/**
 * Force noindex côté Rank Math sur la page d'inscription (évite qu'il réécrive la balise).
 *
 * @param array $robots Robots Rank Math.
 * @return array
 */
function afsac_inscription_rankmath_noindex( $robots ) {
	if ( is_page_template( 'template-inscription.php' ) ) {
		return array( 'noindex' => 'noindex', 'nofollow' => 'nofollow' );
	}
	if ( is_page_template( 'template-espace-participant.php' ) ) {
		return array( 'noindex' => 'noindex' );
	}
	return $robots;
}
add_filter( 'rank_math/frontend/robots', 'afsac_inscription_rankmath_noindex' );

/**
 * Préconnexion aux serveurs de polices Google (performance).
 *
 * @param array  $urls          URLs de pré-ressources.
 * @param string $relation_type Type de relation.
 * @return array
 */
function afsac_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type && wp_style_is( 'afsac-fonts', 'enqueued' ) ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
		$urls[] = 'https://fonts.googleapis.com';
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'afsac_resource_hints', 10, 2 );

/**
 * Valeurs par défaut des coordonnées (= maquette). Centralisées pour servir à la
 * fois de repli au rendu et de valeur par défaut des réglages Customizer.
 *
 * @return array<string,string>
 */
function afsac_contact_defaults() {
	return array(
		// UN seul numéro sur tout le site (demande client, 18/08/2026 : le second
		// numéro a été retiré du footer). Le réglage afsac_phone_2 reste offert au
		// Customizer — vide par défaut, le footer ne l'affiche donc pas — pour
		// pouvoir en remettre un sans retoucher le code.
		'phone'          => '+216 20 199 950',
		'phone_2'        => '',
		'email_contact'  => 'icao@afsactunisie.com',
		'email_training' => 'Training@afsactunisie.com',
		'email_bespoke'  => '',
		'address'        => "7, AV, Taha Houssein Montfleury, CP : 1008 Tunis - Tunisie",
	);
}

/**
 * Construit un href « tel: » à partir d'un numéro d'affichage (espaces retirés).
 *
 * @param string $display Numéro tel qu'affiché (ex. « +216 71 000 200 »).
 * @return string Numéro compact pour l'attribut href (ex. « +21671000200 »).
 */
function afsac_tel_href( $display ) {
	return preg_replace( '/[^0-9+]/', '', (string) $display );
}

/**
 * Coordonnées de contact du site (lues depuis le Customizer, avec replis).
 *
 * La forme du tableau (clés phone / phone_display / email / emails / address)
 * est conservée pour rester compatible avec tout appel existant.
 *
 * @return array{phone:string,phone_display:string,email:string,emails:array,address:string}
 */
function afsac_get_contact() {
	$defaults = afsac_contact_defaults();

	$phone_display   = get_theme_mod( 'afsac_phone', $defaults['phone'] );
	$phone_2_display = get_theme_mod( 'afsac_phone_2', $defaults['phone_2'] );
	$contact         = get_theme_mod( 'afsac_email_contact', $defaults['email_contact'] );
	$training        = get_theme_mod( 'afsac_email_training', $defaults['email_training'] );
	$bespoke         = get_theme_mod( 'afsac_email_bespoke', $defaults['email_bespoke'] );

	$emails = array_values( array_filter( array( $contact, $training, $bespoke ) ) );

	return apply_filters(
		'afsac_contact',
		array(
			'phone'           => afsac_tel_href( $phone_display ),
			'phone_display'   => $phone_display,
			'phone_2'         => afsac_tel_href( $phone_2_display ),
			'phone_2_display' => $phone_2_display,
			'email'           => $contact,
			'emails'          => $emails,
			'address'         => get_theme_mod( 'afsac_address', $defaults['address'] ),
		)
	);
}

/**
 * Liens vers les réseaux sociaux (Customizer). Une entrée n'est présente que si
 * son URL est renseignée : aucun lien mort (#) n'est jamais rendu.
 *
 * @return array<int,array{key:string,label:string,url:string}>
 */
function afsac_get_social_links() {
	$networks = array(
		'linkedin' => 'LinkedIn',
		'x'        => 'X (Twitter)',
		'facebook' => 'Facebook',
		'youtube'  => 'YouTube',
	);

	$links = array();

	foreach ( $networks as $key => $label ) {
		// Source unifiée : options ACF (afsac_social_*) avec repli Customizer.
		$url = function_exists( 'afsac_opt' ) ? afsac_opt( 'afsac_social_' . $key ) : '';
		if ( '' === $url ) {
			$url = get_theme_mod( 'afsac_social_' . $key, '' );
		}
		if ( $url ) {
			$links[] = array(
				'key'   => $key,
				'label' => $label,
				'url'   => $url,
			);
		}
	}

	return $links;
}

/**
 * Liens légaux du pied de page (Customizer), avec replis vers des pages locales.
 *
 * @return array<int,array{label:string,url:string}>
 */
function afsac_get_legal_links() {
	// Slug (langue par défaut) → libellé. Résolu en langue courante (Polylang)
	// via le helper, jamais un slug en dur. « Plan du site » retiré (sitemap XML
	// Rank Math conservé pour le SEO).
	$items = array(
		'mentions' => array( __( 'Mentions légales', 'afsac' ), 'mentions-legales' ),
		'privacy'  => array( __( 'Politique de confidentialité', 'afsac' ), 'politique-de-confidentialite' ),
	);

	$links = array();

	foreach ( $items as $key => $item ) {
		$default = function_exists( 'afsac_localized_page_url' ) ? afsac_localized_page_url( $item[1] ) : home_url( '/' . $item[1] . '/' );
		$url     = get_theme_mod( 'afsac_legal_' . $key, $default );
		if ( '' === $url ) {
			continue; // Page absente → on n'affiche pas un lien mort.
		}
		$links[] = array(
			'label' => $item[0],
			'url'   => $url,
		);
	}

	return $links;
}

/**
 * Statistiques clés affichées sous le hero (filtrables, traduisibles).
 *
 * @return array<int,array{value:string,label:string}>
 */
function afsac_hero_stats() {
	return apply_filters(
		'afsac_hero_stats',
		array(
			array( 'value' => '45+', 'label' => __( 'Années d’expertise', 'afsac' ) ),
			array( 'value' => '483', 'label' => __( 'Cours OACI', 'afsac' ) ),
			array( 'value' => '48',  'label' => __( 'États accompagnés', 'afsac' ) ),
			array( 'value' => '2',   'label' => __( 'Langues FR / EN', 'afsac' ) ),
		)
	);
}

/**
 * Retourne une icône SVG inline du thème.
 *
 * Jeu d'icônes minimal, sans dépendance externe. Sortie sûre (SVG statique).
 *
 * @param string $name  Identifiant de l'icône.
 * @param array  $attrs Attributs supplémentaires (class, etc.).
 * @return string Balisage SVG, ou chaîne vide si inconnu.
 */
function afsac_icon( $name, $attrs = array() ) {
	$paths = array(
		'phone'    => '<path d="M6.6 10.8a15.6 15.6 0 006.6 6.6l2.2-2.2a1 1 0 011-.24 11 11 0 003.5.56 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11 11 0 00.56 3.5 1 1 0 01-.24 1z"/>',
		'mail'     => '<path d="M3 5h18a1 1 0 011 1v12a1 1 0 01-1 1H3a1 1 0 01-1-1V6a1 1 0 011-1zm1.4 2L12 12l7.6-5z" fill-rule="evenodd"/>',
		'search'   => '<path d="M10 2a8 8 0 105.3 14l5.2 5.2 1.4-1.4-5.2-5.2A8 8 0 0010 2zm0 2a6 6 0 110 12 6 6 0 010-12z"/>',
		'user'     => '<path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-5 0-9 2.5-9 6v2h18v-2c0-3.5-4-6-9-6z"/>',
		'linkedin' => '<path d="M4.98 3.5A2.5 2.5 0 002.5 6a2.5 2.5 0 005 0 2.5 2.5 0 00-2.52-2.5zM3 9h4v12H3zM10 9h3.8v1.7h.05c.53-1 1.83-2.05 3.77-2.05 4.03 0 4.78 2.65 4.78 6.1V21H18.6v-5.4c0-1.3 0-2.95-1.8-2.95-1.8 0-2.08 1.4-2.08 2.85V21H10z"/>',
		'twitter'  => '<path d="M18.9 2H22l-7 8 8.2 12h-6.4l-5-7.3L6 22H3l7.5-8.6L2.5 2H9l4.5 6.6zm-1.1 18h1.7L7.3 3.8H5.5z"/>',
		'facebook' => '<path d="M22 12a10 10 0 10-11.6 9.9v-7H8v-2.9h2.4V9.8c0-2.4 1.4-3.7 3.6-3.7 1 0 2.1.2 2.1.2v2.4h-1.2c-1.2 0-1.5.7-1.5 1.5v1.7h2.6l-.4 2.9h-2.2v7A10 10 0 0022 12z"/>',
		'youtube'  => '<path d="M23 12s0-3.2-.4-4.7a2.5 2.5 0 00-1.8-1.8C19.3 5 12 5 12 5s-7.3 0-8.8.5A2.5 2.5 0 001.4 7.3C1 8.8 1 12 1 12s0 3.2.4 4.7a2.5 2.5 0 001.8 1.8C4.7 19 12 19 12 19s7.3 0 8.8-.5a2.5 2.5 0 001.8-1.8C23 15.2 23 12 23 12zM9.8 15.3V8.7l5.7 3.3z"/>',
		'chevron'  => '<path d="M8 5l8 7-8 7z"/>',
		'close'    => '<path d="M18.3 5.7L12 12l6.3 6.3-1.4 1.4L10.6 13.4 4.3 19.7 2.9 18.3 9.2 12 2.9 5.7 4.3 4.3l6.3 6.3 6.3-6.3z"/>',
		'menu'     => '<path d="M3 6h18v2H3zM3 11h18v2H3zM3 16h18v2H3z"/>',
		// Partage d'une actualité : « copier le lien » et son accusé de réception.
		'link'     => '<path d="M10.6 13.4a4 4 0 005.7 0l3-3a4 4 0 10-5.7-5.7l-1.3 1.3 1.4 1.4L15 6.1a2 2 0 112.8 2.8l-3 3a2 2 0 01-2.8 0zM13.4 10.6a4 4 0 00-5.7 0l-3 3a4 4 0 105.7 5.7l1.3-1.3-1.4-1.4L9 17.9a2 2 0 11-2.8-2.8l3-3a2 2 0 012.8 0z"/>',
		'check'    => '<path d="M9.6 16.2L5.4 12l-1.4 1.4 5.6 5.6L20.4 7.8 19 6.4z"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	$class = isset( $attrs['class'] ) ? $attrs['class'] : '';

	return sprintf(
		'<svg class="afsac-icon afsac-icon--%1$s %2$s" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $name ),
		esc_attr( $class ),
		$paths[ $name ] // SVG statique défini ci-dessus.
	);
}

/**
 * Couleur de texte lisible sur un fond donné (noir encre ou blanc).
 *
 * Les 11 domaines OACI sont peints en APLAT dans leur couleur officielle
 * (cf. template-catalogue.php). Or la palette de l'OACI mêle des teintes très
 * sombres (#39474e, #7c1952) et très claires (#fab50f jaune, #05c3c3 cyan) :
 * un texte blanc uniforme, tel que le fait icao.int, tombe à ~1,9:1 sur le
 * jaune — illisible. On choisit donc le texte d'après la luminance relative du
 * fond (WCAG 2.x), ce qui garde le rendu « aplat OACI » tout en restant lisible
 * quelle que soit la couleur saisie ensuite dans le champ ACF du terme.
 *
 * @param string $hex Couleur de fond (#RGB ou #RRGGBB).
 * @return string « #ffffff » ou la valeur du noir encre du thème.
 */
function afsac_readable_text_color( $hex ) {
	$hex = ltrim( trim( (string) $hex ), '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( ! preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
		return '#ffffff'; // Repli : les fonds par défaut du thème sont bleus.
	}

	// Luminance relative WCAG : linéarisation sRGB puis pondération.
	$channels = array();
	foreach ( array( 0, 2, 4 ) as $offset ) {
		$value      = hexdec( substr( $hex, $offset, 2 ) ) / 255;
		$channels[] = ( $value <= 0.03928 ) ? ( $value / 12.92 ) : pow( ( $value + 0.055 ) / 1.055, 2.4 );
	}
	$luminance = ( 0.2126 * $channels[0] ) + ( 0.7152 * $channels[1] ) + ( 0.0722 * $channels[2] );

	// Seuil 0,45 : au-dessus, le blanc passerait sous 3:1 (jaune, cyan clair).
	return ( $luminance > 0.45 ) ? '#0f1720' : '#ffffff';
}

/**
 * Registre des motifs SVG de repli des cours (aviation mono-trait, currentColor).
 *
 * @return array<string,string> slug => tracé SVG interne.
 */
function afsac_course_motifs() {
	return array(
		'tour'     => '<path d="M9 21h6"/><path d="M10 21l1-8h2l1 8"/><path d="M8 13h8"/><path d="M9 13l-1-5h8l-1 5"/><path d="M12 8V3l3 1.6"/>',
		'radar'    => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4.5"/><path d="M12 12l6.4-4.4"/><circle cx="15.6" cy="9.5" r="0.9" fill="currentColor" stroke="none"/>',
		'piste'    => '<path d="M9 21l1.6-17M15 21l-1.6-17"/><path d="M12 8v2M12 13v2M12 18v1"/>',
		'document' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6"/><path d="M9 17h6"/>',
		'salle'    => '<rect x="3" y="4" width="18" height="11" rx="1"/><path d="M7 20h10"/><path d="M9 15v5M15 15v5"/>',
		'bouclier' => '<path d="M12 3l7 3v5c0 4-3 7-7 8-4-1-7-4-7-8V6z"/><path d="M9 12l2 2 4-4"/>',
	);
}

/**
 * Choisit un motif de repli de façon DÉTERMINISTE : sous-domaine (indices
 * sémantiques) → type → ID. Réutilisé par course-row.php et single-afsac_formation.php.
 *
 * @param string[] $sub_slugs Slugs des sous-domaines du cours.
 * @param string   $type_key  Clé de type stable (afsac_type_key).
 * @param int      $post_id   ID du cours.
 * @return string Tracé SVG interne du motif.
 */
function afsac_course_motif( $sub_slugs, $type_key, $post_id ) {
	$motifs = afsac_course_motifs();
	$hints  = array( 'aim' => 'document', 'atm' => 'tour', 'cns' => 'radar' );
	$first  = ( is_array( $sub_slugs ) && ! empty( $sub_slugs ) ) ? (string) reset( $sub_slugs ) : '';

	if ( '' !== $first && isset( $hints[ $first ] ) ) {
		$key = $hints[ $first ];
	} else {
		$keys = array_keys( $motifs );
		$seed = ( '' !== $first ) ? $first : ( ( '' !== (string) $type_key ) ? (string) $type_key : (string) $post_id );
		$key  = $keys[ abs( crc32( $seed ) ) % count( $keys ) ];
	}
	return isset( $motifs[ $key ] ) ? $motifs[ $key ] : reset( $motifs );
}

/**
 * Photos de repli des vignettes de cours, quand la formation n'a pas d'image à
 * la une. Le motif SVG sur aplat bleu tenait lieu de vignette : le client le
 * juge trop pauvre au regard du calendrier de l'OACI, qui montre de vraies
 * images. On puise donc dans les photos aéronautiques déjà présentes.
 *
 * @return string[] URLs des photos disponibles (peut être vide).
 */
function afsac_course_photo_pool() {
	static $pool = null;
	if ( null !== $pool ) {
		return $pool;
	}
	/*
	 * Uniquement des visuels qui SUPPORTENT UN RECADRAGE CARRÉ : les vignettes de
	 * cours (accueil, calendrier) sont en 1:1. hero-aircraft.webp en a été retiré —
	 * c'est un panoramique 2.94:1 dont le centre n'est que du ciel, il rendait une
	 * carte sur quatre comme un aplat gris. Le fichier reste disponible pour un
	 * usage bandeau.
	 */
	$pool  = array();
	$files = array(
		'formation-tp-1.webp',
		'formation-tp-2.webp',
		'formation-tp-3.webp',
		'formation-tp-4.webp',
		'intro-slide-1.webp',
		'intro-slide-2.webp',
		'intro-slide-3.webp',
	);
	foreach ( $files as $file ) {
		if ( file_exists( get_theme_file_path( 'assets/images/' . $file ) ) ) {
			$pool[] = get_theme_file_uri( 'assets/images/' . $file );
		}
	}
	return $pool;
}

/**
 * Données d'affichage d'une carte de cours au format « catalogue OACI ».
 *
 * Alimente template-parts/home/course-card-icao.php. Vit ici (et non dans le
 * gabarit de l'accueil) parce que DEUX sections s'en servent : le carrousel
 * « Consulter nos formations à venir » de l'accueil et les « prochaines sessions AVSEC »
 * du catalogue. Ce partiel ne dépend pas de la boucle : on lui passe un ID.
 *
 * @param int $post_id ID de la formation.
 * @return array Tableau d'arguments prêt pour course-card-icao.
 */
function afsac_build_course_card( $post_id ) {
	$post_id = (int) $post_id;

	// Map nom/slug de langue -> code court (repli : 2 premières lettres).
	$lang_codes = array(
		'francais' => 'FR', 'français' => 'FR', 'french' => 'FR', 'fr' => 'FR',
		'anglais'  => 'EN', 'english' => 'EN', 'en' => 'EN',
		'arabe'    => 'AR', 'arabic' => 'AR', 'العربية' => 'AR', 'ar' => 'AR',
	);

	/*
	 * Langues : les termes portent déjà leur nom localisé (Français / English),
	 * mais un cours importé sans langue Polylang emporte le terme de sa langue
	 * d'origine — on le résout donc vers la langue de navigation.
	 */
	$lang_terms = get_the_terms( $post_id, 'afsac_langue' );
	$lang_terms = ( $lang_terms && ! is_wp_error( $lang_terms ) ) ? $lang_terms : array();
	if ( function_exists( 'afsac_localized_term' ) ) {
		$lang_terms = array_map( 'afsac_localized_term', $lang_terms );
	}
	$lang_names = wp_list_pluck( $lang_terms, 'name' );

	/*
	 * Repli quand la taxonomie est vide (~40 cours importés) : on retombe sur
	 * la langue Polylang du post, qui correspond toujours au titre affiché.
	 */
	if ( empty( $lang_names ) ) {
		$code = '';
		if ( function_exists( 'pll_get_post_language' ) ) {
			$pl = pll_get_post_language( $post_id, 'slug' );
			if ( $pl ) {
				$code = strtoupper( (string) $pl );
			}
		}
		if ( '' === $code ) {
			$loc  = strtolower( substr( (string) get_locale(), 0, 2 ) );
			$code = isset( $lang_codes[ $loc ] ) ? $lang_codes[ $loc ] : strtoupper( $loc );
		}
		if ( '' !== $code ) {
			$lang_names = array( $code );
		}
	}

	// Modalité : terme dédié si présent (résolu dans la langue courante),
	// sinon libellé de la méthode ACF.
	$mod_terms = get_the_terms( $post_id, 'afsac_modalite' );
	$mod_terms = ( $mod_terms && ! is_wp_error( $mod_terms ) ) ? $mod_terms : array();
	if ( ! empty( $mod_terms ) ) {
		$mod_term   = function_exists( 'afsac_localized_term' ) ? afsac_localized_term( $mod_terms[0] ) : $mod_terms[0];
		$meth_label = $mod_term->name;
	} else {
		$meth       = function_exists( 'get_field' ) ? (string) get_field( 'afsac_methode', $post_id ) : '';
		$meth_label = ( 'autorythme' === $meth )
			? __( 'En ligne (auto-rythmé)', 'afsac' )
			: __( 'Avec instructeur', 'afsac' );
	}

	// Domaine OACI de premier niveau + couleur officielle (méta de terme).
	$domain = function_exists( 'afsac_course_area_domain' ) ? afsac_course_area_domain( $post_id ) : null;
	$color  = ( $domain && function_exists( 'afsac_get_area_accent_color' ) ) ? afsac_get_area_accent_color( $domain ) : '';

	// Sous-domaines : servent au choix déterministe du motif de repli.
	$areas = get_the_terms( $post_id, 'afsac_area' );
	$areas = ( $areas && ! is_wp_error( $areas ) ) ? $areas : array();
	$subs  = wp_list_pluck( $areas, 'slug' );

	// « Développé par » : détail long (identique au catalogue OACI), repli court.
	$dev = '';
	if ( function_exists( 'get_field' ) ) {
		$dev = (string) get_field( 'afsac_developpe_par_detail', $post_id );
		if ( '' === trim( $dev ) ) {
			$dev = (string) get_field( 'afsac_developpe_par', $post_id );
		}
	}

	return array(
		'title'      => get_the_title( $post_id ),
		'url'        => get_permalink( $post_id ),
		'abbr'       => function_exists( 'get_field' ) ? (string) get_field( 'afsac_abbreviation', $post_id ) : '',
		'dev'        => trim( $dev ),
		// Écarte les pièces jointes `_afsac_doc_scan` (scans de fiches illisibles).
		'thumb'      => function_exists( 'afsac_course_thumb_url' ) ? afsac_course_thumb_url( $post_id, 'medium' ) : '',
		'motif'      => function_exists( 'afsac_course_motif' ) ? afsac_course_motif( $subs, '', $post_id ) : '',
		'lang_names' => $lang_names,
		'duree'      => function_exists( 'get_field' ) ? (string) get_field( 'afsac_duree', $post_id ) : '',
		'meth_label' => $meth_label,
		'area_name'  => $domain ? $domain->name : '',
		'area_color' => $color,
	);
}

/**
 * URL de la vignette d'un cours, ou '' si aucune image exploitable.
 *
 * Trois cas, dans l'ordre :
 *   1. image à la une — SAUF si la pièce jointe est marquée `_afsac_doc_scan`.
 *      L'import de l'ancien site a rattaché à ~26 cours leur FICHE DESCRIPTIVE
 *      (page de texte dense) et non une affiche : en vignette, c'est un pavé
 *      gris illisible, pire que pas d'image du tout ;
 *   2. repli photo déterministe (même cours = toujours la même photo, donc pas
 *      de valse d'images d'une page à l'autre) ;
 *   3. '' — l'appelant retombe alors sur le motif SVG.
 *
 * @param int    $post_id ID du cours.
 * @param string $size    Taille WordPress demandée pour l'image à la une.
 * @return string URL, ou '' .
 */
function afsac_course_thumb_url( $post_id, $size = 'medium_large' ) {
	$post_id = (int) $post_id;
	if ( ! $post_id ) {
		return '';
	}

	$thumb_id = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb_id && ! get_post_meta( $thumb_id, '_afsac_doc_scan', true ) ) {
		$url = wp_get_attachment_image_url( $thumb_id, $size );
		if ( $url ) {
			return (string) $url;
		}
	}

	$pool = afsac_course_photo_pool();
	if ( empty( $pool ) ) {
		return '';
	}
	return $pool[ abs( crc32( (string) $post_id ) ) % count( $pool ) ];
}

/**
 * Verrou du Centre — en-tête (couleur) et pied de page (blanc).
 *
 * L'EN-TÊTE porte depuis le 17/08/2026 le logo AFSAC du client, identique dans
 * toutes les langues (il n'intègre plus l'intitulé du Centre).
 *
 * Le PIED DE PAGE garde le verrou blanc, qui porte encore cet intitulé : il en
 * existe donc une version par langue, avec repli sur le français pour toute
 * langue sans fichier dédié — l'arabe aujourd'hui. Ajouter un
 * `logo-centre-oaci-ar-blanc.webp` et une entrée dans $map suffirait.
 *
 * @param bool $white true = version blanche (fond bleu du pied de page).
 * @return array{url:string,width:int,height:int} URL et dimensions intrinsèques.
 */
function afsac_logo_centre( $white = false ) {
	/*
	 * VERSION COULEUR (en-tête) : logo AFSAC fourni par le client le 17/08/2026
	 * (avion + « ICAO ASTC Tunisia »). Il ne porte plus l'intitulé complet du
	 * Centre : il est donc le MÊME dans toutes les langues, plus de verrou par
	 * langue à maintenir de ce côté.
	 */
	if ( ! $white ) {
		return array(
			'url'    => get_theme_file_uri( 'assets/images/logo-afsac.png' ),
			'width'  => 573,
			'height' => 224,
		);
	}

	$lang = function_exists( 'pll_current_language' )
		? (string) pll_current_language()
		: substr( (string) get_locale(), 0, 2 );
	$lang = strtolower( $lang );

	// [ fichier blanc, largeur, hauteur ] par langue.
	$map = array(
		'fr' => array( 'logo-centre-oaci-blanc.webp', 451, 85 ),
		'en' => array( 'logo-centre-oaci-en-blanc.webp', 622, 148 ),
	);
	$key = isset( $map[ $lang ] ) ? $lang : 'fr';

	$file   = $map[ $key ][0];
	$width  = $map[ $key ][1];
	$height = $map[ $key ][2];

	// Fichier manquant (asset non déployé) -> on retombe sur le verrou français.
	if ( ! file_exists( get_theme_file_path( 'assets/images/' . $file ) ) ) {
		$file   = $map['fr'][0];
		$width  = $map['fr'][1];
		$height = $map['fr'][2];
	}

	return array(
		'url'    => get_theme_file_uri( 'assets/images/' . $file ),
		'width'  => (int) $width,
		'height' => (int) $height,
	);
}

/**
 * Icônes MÉTA des cartes de cours (langue / durée / modalité / domaine).
 *
 * Jeu à TRAIT (fill="none" + stroke="currentColor"), volontairement distinct de
 * afsac_icon() qui est un jeu à APLAT 24×24 destiné au chrome (barre du haut,
 * pied de page) et dimensionné par des règles CSS propres à .afsac-icon.
 *
 * Les tracés étaient dupliqués — et avaient divergé — entre course-row.php et
 * la carte de l'accueil : source unique ici.
 *
 * @param string $name Identifiant : lang | duration | method | area.
 * @return string Balisage SVG statique, ou '' si inconnu.
 */
function afsac_meta_icon( $name ) {
	$paths = array(
		'lang'     => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 2.5 13.4 0 18M12 3c-2.5 2.6-2.5 13.4 0 18"/>',
		'duration' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'method'   => '<path d="M3 6h18v10H3z"/><path d="M8 20h8M12 16v4"/>',
		'area'     => '<path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 7v6l9 4 9-4V7"/>',
		// Ajoutés pour les « faits clés » du héros de la fiche cours.
		'level'    => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
		'award'    => '<circle cx="12" cy="9" r="5"/><path d="M8.5 13.5L7 22l5-2.5L17 22l-1.5-8.5"/>',
		'fee'      => '<path d="M20.6 12.6L12.6 4.6A2 2 0 0011.2 4H5a1 1 0 00-1 1v6.2c0 .5.2 1 .6 1.4l8 8a2 2 0 002.8 0l5.2-5.2a2 2 0 000-2.8z"/><path d="M8 8h.01"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="afsac-meta-icon afsac-meta-icon--%1$s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false">%2$s</svg>',
		esc_attr( $name ),
		$paths[ $name ]
	);
}

/**
 * Résout un terme vers sa traduction dans la LANGUE COURANTE.
 *
 * Nécessaire parce qu'une grande partie du catalogue importé n'a pas de langue
 * Polylang assignée : ces cours s'affichent dans les deux langues, en emportant
 * le terme de leur langue d'origine. Sans cette résolution, la page française
 * peut afficher « Aviation Security » au lieu de « Sûreté de l'aviation ».
 *
 * Repli sur le terme d'origine si Polylang est absent ou la traduction manquante
 * (mieux vaut un libellé dans l'autre langue que pas de libellé du tout).
 *
 * @param WP_Term|null $term Terme à résoudre.
 * @return WP_Term|null
 */
function afsac_localized_term( $term ) {
	if ( ! $term instanceof WP_Term || ! function_exists( 'pll_get_term' ) || ! function_exists( 'pll_current_language' ) ) {
		return $term;
	}

	$lang = pll_current_language();
	if ( ! $lang ) {
		return $term;
	}

	$translated_id = pll_get_term( $term->term_id, $lang );
	if ( ! $translated_id || (int) $translated_id === (int) $term->term_id ) {
		return $term;
	}

	$translated = get_term( $translated_id, $term->taxonomy );
	return ( $translated && ! is_wp_error( $translated ) ) ? $translated : $term;
}

/**
 * Domaine OACI de PREMIER NIVEAU d'un cours, dans la langue courante.
 *
 * Un cours peut n'être rattaché qu'à un sous-domaine : on remonte alors au
 * parent. Même logique que single-afsac_formation.php, factorisée ici pour ne
 * pas en faire une troisième copie.
 *
 * @param int $post_id ID du cours.
 * @return WP_Term|null Terme de premier niveau, ou null.
 */
function afsac_course_area_domain( $post_id ) {
	$terms = get_the_terms( (int) $post_id, 'afsac_area' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return null;
	}

	$domain = null;
	$child  = null;
	foreach ( $terms as $term ) {
		if ( 0 === (int) $term->parent && ! $domain ) {
			$domain = $term;
		} elseif ( (int) $term->parent > 0 && ! $child ) {
			$child = $term;
		}
	}

	if ( ! $domain && $child ) {
		$parent = get_term( $child->parent, 'afsac_area' );
		if ( $parent && ! is_wp_error( $parent ) ) {
			$domain = $parent;
		}
	}

	return afsac_localized_term( $domain );
}

/**
 * Traduit les UNITÉS d'une durée vers la langue de navigation.
 *
 * `afsac_duree` est un texte libre saisi tel quel à l'import : 6 fiches
 * françaises portent « 5 Days / 27 Hours », et les ~600 cours sans langue
 * Polylang s'affichent dans les DEUX langues en emportant l'unité de leur
 * langue d'origine (cf. [[formations-sans-langue-polylang]]). On ne traduit que
 * les mots d'unité connus : les chiffres, séparateurs et mentions libres
 * (« 5 jours (dont 1 en ligne) ») passent intacts.
 *
 * @param string $duree Durée telle que stockée.
 * @return string Durée dans la langue courante.
 */
function afsac_localize_duree( $duree ) {
	$duree = trim( (string) $duree );
	if ( '' === $duree ) {
		return '';
	}

	$lang = function_exists( 'pll_current_language' ) ? (string) pll_current_language() : '';
	if ( '' === $lang ) {
		$lang = ( 0 === strpos( (string) get_locale(), 'fr' ) ) ? 'fr' : 'en';
	}

	$to_fr = array(
		'days'    => 'jours',
		'day'     => 'jour',
		'hours'   => 'heures',
		'hour'    => 'heure',
		'weeks'   => 'semaines',
		'week'    => 'semaine',
		'months'  => 'mois',
		'month'   => 'mois',
	);
	$to_en = array(
		'jours'     => 'days',
		'jour'      => 'day',
		'heures'    => 'hours',
		'heure'     => 'hour',
		'semaines'  => 'weeks',
		'semaine'   => 'week',
		'mois'      => 'months',
	);

	$map = ( 'fr' === $lang ) ? $to_fr : $to_en;

	return (string) preg_replace_callback(
		'/\b(' . implode( '|', array_keys( $map ) ) . ')\b/iu',
		static function ( $m ) use ( $map ) {
			return $map[ strtolower( $m[1] ) ];
		},
		$duree
	);
}

/**
 * Arguments d'affichage d'une LIGNE de cours (template-parts/course-row.php).
 *
 * Pendant de afsac_build_course_card() pour le format « ligne riche ». Vit ici
 * parce que DEUX gabarits en ont besoin : l'archive d'un domaine OACI
 * (taxonomy-afsac_area.php) et l'archive générique du CPT
 * (archive-afsac_formation.php) — sinon c'était 90 lignes dupliquées.
 *
 * @param int          $post_id ID du cours.
 * @param WP_Post|null $next    Prochaine session déjà résolue (lot), ou null.
 * @return array Arguments prêts pour course-row.
 */
function afsac_build_course_row_args( $post_id, $next = null ) {
	$post_id = (int) $post_id;
	$field   = static function ( $key ) use ( $post_id ) {
		return function_exists( 'get_field' ) ? (string) get_field( $key, $post_id ) : '';
	};

	$meth = $field( 'afsac_methode' );
	if ( 'autorythme' !== $meth ) {
		$meth = 'instructeur';
	}

	$terms = static function ( $tax ) use ( $post_id ) {
		$t = get_the_terms( $post_id, $tax );
		return ( $t && ! is_wp_error( $t ) ) ? $t : array();
	};
	$langs = $terms( 'afsac_langue' );
	$mods  = $terms( 'afsac_modalite' );
	$types = $terms( 'afsac_type' );
	$areas = $terms( 'afsac_area' );

	// Sous-domaines : servent au motif de repli ET au filtrage par chips.
	$sub_slugs = array();
	foreach ( $areas as $term ) {
		if ( (int) $term->parent > 0 ) {
			$sub_slugs[] = $term->slug;
		}
	}

	/*
	 * Domaine OACI de premier niveau : alimente `data-area` (filtre « Domaine »
	 * de l'archive générique) ET la liste d'options que le gabarit construit à
	 * partir du jeu réellement affiché. Résolu dans la langue courante, sinon un
	 * cours importé sans langue Polylang proposerait « Aerodromes » sur la page FR.
	 */
	$domain = function_exists( 'afsac_course_area_domain' ) ? afsac_course_area_domain( $post_id ) : null;

	$type_name = '';
	$type_key  = '';
	if ( ! empty( $types ) ) {
		$type_name = $types[0]->name;
		$type_key  = (string) get_term_meta( $types[0]->term_id, 'afsac_type_key', true );
		if ( '' === $type_key ) {
			$type_key = $types[0]->slug;
		}
	}

	$mod_names = wp_list_pluck( $mods, 'name' );
	$mod_slugs = wp_list_pluck( $mods, 'slug' );

	if ( 'autorythme' === $meth ) {
		$meth_label = __( 'En ligne (auto-rythmé)', 'afsac' );
	} else {
		$meth_label = __( 'Avec instructeur', 'afsac' );
		if ( $mod_names ) {
			$meth_label .= ' (' . implode( ' · ', $mod_names ) . ')';
		}
	}

	// Lieu de la prochaine session : sert au filtre « Localisation » quand il existe.
	$loc_slug = '';
	$has_sess = ( $next instanceof WP_Post );
	if ( $has_sess ) {
		$lieu = (string) get_post_meta( $next->ID, 'afsac_lieu', true );
		if ( '' !== $lieu ) {
			$loc_slug = sanitize_title( $lieu );
		}
	}

	$dev        = $field( 'afsac_developpe_par' );
	$dev_detail = $field( 'afsac_developpe_par_detail' );

	return array(
		'abbr'        => $field( 'afsac_abbreviation' ),
		'dev'         => trim( $dev . ( ( $dev && $dev_detail ) ? ' — ' : '' ) . $dev_detail ),
		'lang_names'  => wp_list_pluck( $langs, 'name' ),
		'lang_slugs'  => wp_list_pluck( $langs, 'slug' ),
		'duree'       => afsac_localize_duree( $field( 'afsac_duree' ) ),
		'meth_label'  => $meth_label,
		'meth_slug'   => $meth,
		'type_name'   => $type_name,
		'type_key'    => $type_key,
		'sub_slugs'   => $sub_slugs,
		'area_slug'   => $domain ? $domain->slug : '',
		'area_name'   => $domain ? $domain->name : '',
		'virtual'     => ( 'autorythme' === $meth ) || (bool) array_intersect( array( 'distanciel', 'online' ), $mod_slugs ),
		'has_session' => $has_sess,
		'loc_slug'    => $loc_slug,
		'reduced'     => function_exists( 'get_field' ) ? (bool) get_field( 'afsac_tarif_reduit', $post_id ) : false,
	);
}

/**
 * Recolle les puces coupées par le retour à la ligne d'un PDF.
 *
 * L'import de l'ancien site a transformé CHAQUE ligne visuelle des fiches en un
 * <li> : une puce longue s'affiche donc en deux ou trois puces, dont des bouts
 * de phrase isolés (« aérodrome; », « de l'OACI. »). 29 des 63 fiches qui
 * portent une liste sont concernées — c'est le défaut le plus visible des
 * fiches cours.
 *
 * Deux garde-fous cumulés, pour ne JAMAIS fusionner une vraie liste courte
 * (« Responsable sûreté » / « Instructeur » / « Concepteur ») :
 *   1. la liste doit être PONCTUÉE (≥ 60 % des puces finissent par ; . : ! ?) ;
 *   2. la puce à recoller doit commencer par une MINUSCULE, et la précédente
 *      ne pas être déjà terminée.
 *
 * Bonus : une première puce terminée par « : » est une phrase d'introduction
 * (« … les participants seront aptes à : ») — elle sort de la liste et devient
 * un paragraphe de chapô.
 *
 * Réparation à l'AFFICHAGE : la donnée en base n'est pas touchée (l'import peut
 * être rejoué), et un cours corrigé à la main dans l'admin reste correct.
 *
 * @param string $html HTML riche issu d'ACF (wysiwyg).
 * @return string HTML réparé.
 */
function afsac_repair_wrapped_list( $html ) {
	$html = (string) $html;
	if ( false === stripos( $html, '<li' ) ) {
		return $html;
	}

	// Une seule liste par champ dans le corpus : on traite chaque <ul>/<ol> isolément.
	return (string) preg_replace_callback(
		'#<(ul|ol)\b[^>]*>(.*?)</\1>#is',
		static function ( $list ) {
			$tag = $list[1];
			if ( ! preg_match_all( '#<li\b[^>]*>(.*?)</li>#is', $list[2], $m ) ) {
				return $list[0];
			}
			$items = $m[1];
			if ( count( $items ) < 2 ) {
				return $list[0];
			}

			$plain = static function ( $frag ) {
				return trim( html_entity_decode( wp_strip_all_tags( $frag ), ENT_QUOTES, 'UTF-8' ) );
			};

			// Garde-fou 1 : la liste est-elle ponctuée ?
			$closed = 0;
			$counted = 0;
			foreach ( $items as $item ) {
				$t = $plain( $item );
				if ( '' === $t ) {
					continue;
				}
				++$counted;
				if ( preg_match( '/[;.:!?]$/u', $t ) ) {
					++$closed;
				}
			}
			if ( $counted < 2 || ( $closed / $counted ) < 0.6 ) {
				return $list[0];
			}

			// Garde-fou 2 : recollage ligne à ligne.
			$merged = array();
			foreach ( $items as $item ) {
				$item = trim( $item );
				if ( '' === $plain( $item ) ) {
					continue;
				}
				$last = count( $merged ) - 1;
				if (
					$last >= 0
					&& ! preg_match( '/[;.:!?]$/u', $plain( $merged[ $last ] ) )
					&& preg_match( '/^[\p{Ll}]/u', $plain( $item ) )
				) {
					$merged[ $last ] = rtrim( $merged[ $last ] ) . ' ' . ltrim( $item );
					continue;
				}
				$merged[] = $item;
			}

			// Chapô : première puce terminée par « : », s'il reste des puces après.
			$lead = '';
			if ( count( $merged ) > 1 && preg_match( '/:$/u', $plain( $merged[0] ) ) ) {
				$lead = '<p class="afsac-rich-lead">' . array_shift( $merged ) . '</p>';
			}

			$out = '';
			foreach ( $merged as $item ) {
				$out .= '<li>' . $item . '</li>';
			}

			return $lead . '<' . $tag . '>' . $out . '</' . $tag . '>';
		},
		$html
	);
}

/**
 * Menu de repli affiché quand aucun menu n'est assigné à « primary ».
 *
 * wp_nav_menu() passe (array) $args au fallback_cb : on honore `menu_class` pour
 * que le repli garde le style de son contexte (barre du haut OU colonne du pied
 * de page, qui affiche désormais ce même emplacement).
 *
 * @param array $args Arguments transmis par wp_nav_menu().
 * @return void
 */
function afsac_primary_menu_fallback( $args = array() ) {
	$class = ( is_array( $args ) && ! empty( $args['menu_class'] ) ) ? $args['menu_class'] : 'afsac-menu';
	printf( '<ul class="%s">', esc_attr( $class ) );
	printf(
		'<li class="menu-item"><a href="%s">%s</a></li>',
		esc_url( home_url( '/' ) ),
		esc_html__( 'Accueil', 'afsac' )
	);
	$archive = get_post_type_archive_link( 'afsac_formation' );
	if ( $archive ) {
		printf(
			'<li class="menu-item"><a href="%s">%s</a></li>',
			esc_url( $archive ),
			esc_html__( 'Formations', 'afsac' )
		);
	}
	echo '</ul>';
}

/**
 * Formate une date ACF (Ymd) vers un affichage localisé.
 *
 * @param string $ymd Date au format AAAAMMJJ.
 * @return string
 */
function afsac_format_date( $ymd ) {
	$date = afsac_date_from_ymd( $ymd );
	return $date ? wp_date( get_option( 'date_format' ), $date->getTimestamp() ) : '';
}

/**
 * Convertit une date ACF (Ymd) en DateTime à MINUIT, dans le fuseau du site.
 *
 * Le décalage d'un jour venait d'ici : createFromFormat( 'Ymd', … ) sans fuseau
 * ni marqueur « | » complète l'heure manquante avec l'heure COURANTE, en UTC.
 * Passé le début de soirée UTC, wp_date() reconvertissait donc le lendemain en
 * heure de Tunis (une session du 21 s'affichait « 22 »). Le « | » remet à zéro
 * tout ce qui n'est pas parsé, et wp_timezone() ancre la date sur le fuseau du
 * site : la journée affichée est exactement celle saisie, à toute heure.
 *
 * @param string $ymd Date au format AAAAMMJJ.
 * @return DateTime|null
 */
function afsac_date_from_ymd( $ymd ) {
	$ymd = trim( (string) $ymd );
	if ( '' === $ymd ) {
		return null;
	}
	$date = DateTime::createFromFormat( 'Ymd|', $ymd, wp_timezone() );
	return $date ? $date : null;
}

/**
 * Libellé traduit d'un statut de session.
 *
 * @param string $statut Valeur brute (ouvert|complet|archive).
 * @return string
 */
function afsac_session_statut_label( $statut ) {
	$labels = array(
		'ouvert'  => __( 'Ouvert', 'afsac' ),
		'complet' => __( 'Complet', 'afsac' ),
		'archive' => __( 'Archivé', 'afsac' ),
		'annule'  => __( 'Annulé', 'afsac' ),
	);
	return isset( $labels[ $statut ] ) ? $labels[ $statut ] : $statut;
}

/**
 * Affiche le sélecteur de langue du plugin si disponible.
 *
 * @param array $args Arguments transmis à afsac_language_switcher().
 * @return void
 */
function afsac_theme_language_switcher( $args = array() ) {
	if ( function_exists( 'afsac_language_switcher' ) ) {
		afsac_language_switcher( $args );
	}
}

/**
 * Slogan affiché à côté du logo (header), valeur par défaut = maquette.
 *
 * Le retour à la ligne est explicite (\n) pour reproduire exactement le
 * découpage en deux lignes de la maquette (« Centre régional » /
 * « de formation OACI »), rendu via white-space: pre-line dans le CSS.
 *
 * @return string
 */
function afsac_brand_tagline_default() {
	return __( "Centre régional\nde formation OACI", 'afsac' );
}

/**
 * Retourne le slogan du header (Customizer, avec repli sur la valeur maquette).
 *
 * @return string
 */
function afsac_get_brand_tagline() {
	return get_theme_mod( 'afsac_brand_tagline', afsac_brand_tagline_default() );
}

/**
 * Enregistre la section Customizer « Coordonnées AFSAC » et ses réglages.
 *
 * Tous les réglages sont préfixés afsac_ et assainis. Le header et le footer
 * lisent ces valeurs (jamais de coordonnées codées dans les templates).
 *
 * @param WP_Customize_Manager $wp_customize Gestionnaire du Customizer.
 * @return void
 */
function afsac_customize_register( $wp_customize ) {
	$defaults = afsac_contact_defaults();

	$wp_customize->add_section(
		'afsac_coordonnees',
		array(
			'title'       => __( 'Coordonnées AFSAC', 'afsac' ),
			'description' => __( 'Téléphone, emails, adresse, réseaux sociaux et liens légaux affichés dans le header et le footer.', 'afsac' ),
			'priority'    => 30,
		)
	);

	/**
	 * Petite fabrique interne : enregistre un réglage + son contrôle.
	 *
	 * @param string $id       Identifiant (préfixé afsac_).
	 * @param string $label    Libellé traduit.
	 * @param string $default  Valeur par défaut.
	 * @param string $sanitize Callback d'assainissement.
	 * @param string $type     Type de contrôle (text, email, url, textarea).
	 * @return void
	 */
	$afsac_add = function ( $id, $label, $default, $sanitize, $type = 'text' ) use ( $wp_customize ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $default,
				'sanitize_callback' => $sanitize,
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $label,
				'section' => 'afsac_coordonnees',
				'type'    => $type,
			)
		);
	};

	// Identité.
	$afsac_add( 'afsac_brand_tagline', __( 'Slogan à côté du logo (header)', 'afsac' ), afsac_brand_tagline_default(), 'sanitize_textarea_field', 'textarea' );
	$afsac_add( 'afsac_page_about', __( 'Page « Qui sommes-nous » (CTA accueil)', 'afsac' ), 0, 'absint', 'dropdown-pages' );

	// Contact.
	$afsac_add( 'afsac_phone', __( 'Téléphone (barre du haut + footer)', 'afsac' ), $defaults['phone'], 'sanitize_text_field' );
	$afsac_add( 'afsac_phone_2', __( 'Téléphone secondaire (footer uniquement)', 'afsac' ), $defaults['phone_2'], 'sanitize_text_field' );
	$afsac_add( 'afsac_email_contact', __( 'Email — contact', 'afsac' ), $defaults['email_contact'], 'sanitize_email', 'email' );
	$afsac_add( 'afsac_email_training', __( 'Email — formation (training)', 'afsac' ), $defaults['email_training'], 'sanitize_email', 'email' );
	$afsac_add( 'afsac_email_bespoke', __( 'Email — sur-mesure (bespoke)', 'afsac' ), $defaults['email_bespoke'], 'sanitize_email', 'email' );
	$afsac_add( 'afsac_address', __( 'Adresse (une ligne par retour à la ligne)', 'afsac' ), $defaults['address'], 'sanitize_textarea_field', 'textarea' );

	// Réseaux sociaux (vide = icône masquée).
	$afsac_add( 'afsac_social_linkedin', __( 'Réseau social — LinkedIn (URL)', 'afsac' ), '', 'esc_url_raw', 'url' );
	$afsac_add( 'afsac_social_x', __( 'Réseau social — X / Twitter (URL)', 'afsac' ), '', 'esc_url_raw', 'url' );
	$afsac_add( 'afsac_social_facebook', __( 'Réseau social — Facebook (URL)', 'afsac' ), '', 'esc_url_raw', 'url' );
	$afsac_add( 'afsac_social_youtube', __( 'Réseau social — YouTube (URL)', 'afsac' ), '', 'esc_url_raw', 'url' );

	// Liens légaux.
	$afsac_add( 'afsac_legal_mentions', __( 'Lien légal — Mentions légales (URL)', 'afsac' ), home_url( '/mentions-legales/' ), 'esc_url_raw', 'url' );
	$afsac_add( 'afsac_legal_privacy', __( 'Lien légal — Politique de confidentialité (URL)', 'afsac' ), home_url( '/politique-de-confidentialite/' ), 'esc_url_raw', 'url' );
	$afsac_add( 'afsac_legal_sitemap', __( 'Lien légal — Plan du site (URL)', 'afsac' ), home_url( '/plan-du-site/' ), 'esc_url_raw', 'url' );
}
add_action( 'customize_register', 'afsac_customize_register' );

/**
 * Force le chargement du bon fichier de traduction du thème selon la locale.
 *
 * Sous WordPress 6.7+ (chargement « just-in-time »), combiné à Polylang, le
 * domaine « afsac » peut être figé dès after_setup_theme avant l'application de
 * la langue de l'URL : les chaînes du thème restent alors en français sur /en/.
 * Au hook « wp » (frontend, langue définie, avant le rendu des templates) on
 * recharge explicitement le .mo correspondant à la locale courante — ex.
 * afsac-en_GB.mo. Sans fichier correspondant (français = langue source), on ne
 * fait rien : le rendu FR reste inchangé.
 *
 * @return void
 */
function afsac_reload_theme_textdomain() {
	$locale = determine_locale();
	$mofile = get_template_directory() . '/languages/afsac-' . $locale . '.mo';
	if ( is_readable( $mofile ) ) {
		unload_textdomain( 'afsac' );
		load_textdomain( 'afsac', $mofile, $locale );
	}
}
add_action( 'wp', 'afsac_reload_theme_textdomain' );

/**
 * Enveloppe une valeur métrique pour le compteur animé (afsac-count).
 *
 * Si la valeur est « à dominante numérique » (un entier suivi d'un suffixe non
 * chiffré : « 148 », « 96 % », « 45+ »), renvoie un <span class="afsac-count">
 * prêt à animer (data-target + data-suffix, textContent initial « 0 »). Sinon
 * — espace de milliers interne (« 12 400+ ») ou valeur non numérique
 * (« T+365 », « T3’25 ») — renvoie la valeur échappée telle quelle, non animée.
 *
 * @param string $value Valeur à afficher.
 * @return string HTML sûr (déjà échappé).
 */
function afsac_count_markup( $value ) {
	$value = (string) $value;
	if ( preg_match( '/^(\d+)(\D*)$/', $value, $afsac_m ) ) {
		return sprintf(
			'<span class="afsac-count" data-target="%1$s" data-suffix="%2$s">0</span>',
			esc_attr( $afsac_m[1] ),
			esc_attr( $afsac_m[2] )
		);
	}
	return esc_html( $value );
}

/* =========================================================================
 * ACTUALITÉS — helpers partagés par la liste (template-actualites.php),
 * la ligne de liste (template-parts/news-row.php) et la page « Savoir plus »
 * (single.php). Ils vivent ici parce que TROIS gabarits s'en servent.
 * ====================================================================== */

/**
 * URL de l'index « Actualités » dans la langue courante.
 *
 * La page porte le gabarit template-actualites.php ; on la retrouve par sa
 * méta plutôt que par un slug en dur (Polylang sert la traduction : la requête
 * n'est pas filtrée sur la langue par `suppress_filters => false`).
 *
 * @return string URL de l'index, ou l'accueil en dernier recours.
 */
function afsac_news_index_url() {
	static $url = null;
	if ( null !== $url ) {
		return $url;
	}

	$pages = get_posts(
		array(
			'post_type'        => 'page',
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'meta_key'         => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'       => 'template-actualites.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'suppress_filters' => false,
		)
	);
	$url = ! empty( $pages ) ? (string) get_permalink( (int) $pages[0] ) : home_url( '/' );

	return $url;
}

/**
 * Temps de lecture estimé d'un article, en minutes (plancher à 1).
 *
 * Base 200 mots/minute — moyenne usuelle pour de la prose institutionnelle.
 *
 * @param int $post_id ID de l'article (0 = article courant).
 * @return int Nombre de minutes.
 */
function afsac_reading_time( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	$content = (string) get_post_field( 'post_content', $post_id );

	/*
	 * str_word_count() est mono-octet : il coupe sur les accents (« réglementaires »
	 * = 2 mots) et renvoie 0 en arabe. On compte donc les suites de lettres/chiffres
	 * Unicode, ce qui reste juste sur les trois langues du site.
	 */
	$words = (int) preg_match_all( '/[\p{L}\p{N}]+/u', wp_strip_all_tags( strip_shortcodes( $content ) ) );

	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Visuel d'une actualité : image à la une, sinon photo de repli déterministe.
 *
 * La demande client est explicite — « image et texte » pour CHAQUE actualité de
 * la liste : un article sans image à la une ne doit pas casser la rangée. On
 * puise donc dans le vivier de photos aéronautiques déjà présentes dans le
 * thème (même vivier que les vignettes de cours), choisi par l'ID pour rester
 * stable d'une page à l'autre.
 *
 * @param int    $post_id ID de l'article (0 = article courant).
 * @param string $size    Taille WordPress de l'image à la une.
 * @return string URL du visuel, ou chaîne vide si aucun repli n'est disponible.
 */
function afsac_news_thumb_url( $post_id = 0, $size = 'large' ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();

	if ( has_post_thumbnail( $post_id ) ) {
		$url = get_the_post_thumbnail_url( $post_id, $size );
		if ( $url ) {
			return (string) $url;
		}
	}

	$pool = function_exists( 'afsac_course_photo_pool' ) ? afsac_course_photo_pool() : array();
	if ( empty( $pool ) ) {
		return '';
	}

	return (string) $pool[ abs( crc32( 'news-' . $post_id ) ) % count( $pool ) ];
}

/* =========================================================================
 * SHORTS YOUTUBE — vidéos verticales de la chaîne AFSAC
 * (@AFSACICAOASTCTUNISIA). Rendu par template-parts/shared/shorts.php.
 *
 * Aucune API YouTube : la liste des vidéos est une SAISIE (Customizer →
 * « Vidéos & Shorts »), avec repli sur les shorts publiés par la chaîne au
 * 12/08/2026. On ne charge JAMAIS d'iframe au chargement de la page — juste la
 * vignette ; le lecteur n'est injecté qu'au clic (assets/js/afsac-shorts.js).
 * ====================================================================== */

/**
 * URL de la chaîne YouTube de l'AFSAC.
 *
 * Réutilise le réglage « Réseau social — YouTube » du Customizer s'il est
 * renseigné, pour ne pas avoir deux sources de vérité.
 *
 * @return string
 */
function afsac_youtube_channel_url() {
	$url = trim( (string) get_theme_mod( 'afsac_social_youtube', '' ) );
	return '' !== $url ? $url : 'https://www.youtube.com/@AFSACICAOASTCTUNISIA';
}

/**
 * Extrait l'identifiant d'une vidéo YouTube.
 *
 * Accepte un identifiant nu ou toutes les formes d'URL de la plateforme
 * (watch?v=, youtu.be/, /shorts/, /embed/, /live/).
 *
 * @param string $value URL ou identifiant.
 * @return string Identifiant à 11 caractères, ou chaîne vide.
 */
function afsac_youtube_id( $value ) {
	$value = trim( (string) $value );
	if ( preg_match( '#^[\w-]{11}$#', $value ) ) {
		return $value;
	}
	if ( preg_match( '#(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/)|youtu\.be/)([\w-]{11})#', $value, $m ) ) {
		return $m[1];
	}
	return '';
}

/**
 * Liste de repli : les shorts publiés par la chaîne AFSAC (relevés le 12/08/2026).
 *
 * Format d'une ligne : « URL | Titre » — exactement celui attendu par le
 * réglage du Customizer, pour que le client puisse copier/coller/amender.
 * Les titres ne sont pas traduits : ce sont les titres RÉELS des vidéos
 * (déjà FR ou EN selon la publication), pas des libellés d'interface.
 *
 * @return string[]
 */
function afsac_shorts_defaults() {
	return array(
		'https://www.youtube.com/shorts/lgr9LGZr7yg | ✈️ Revivez les meilleurs moments du cours ICAO TRAINAIR PLUS de formation des instructeurs (TIC FR)',
		'https://www.youtube.com/shorts/Fpfbwc42FZM | 🎬 Immersion au cœur de la session internationale OACI TRAINAIR PLUS — TIC FR 2026',
		'https://www.youtube.com/shorts/MDcTgRMF5Xc | 🚨 Le fret aérien, l’une des principales cibles des menaces visant l’aviation civile',
		'https://www.youtube.com/shorts/8hMjhvEaaek | ✈️ Chaque jour, des milliers de tonnes de fret et de courrier aérien transitent à travers le monde',
		'https://www.youtube.com/shorts/5QSIM0Abqqk | 📌 Pourquoi choisir nos formations ?',
		'https://www.youtube.com/shorts/SYasWhQx0YI | 📌 Why opting for our aviation training packages?',
		'https://www.youtube.com/shorts/BXkcsqbyK1E | ICAO AVSEC Managers Course — 11-19 May 2026',
		'https://www.youtube.com/shorts/PiFG-MIxWws | 🌍 A week in the spotlight of local and international media',
	);
}

/**
 * Shorts à afficher : saisie du Customizer, sinon repli sur la chaîne.
 *
 * Une ligne = « URL | Titre » (le titre est facultatif). Les lignes vides, les
 * commentaires (#) et les URL non reconnues sont ignorés ; les doublons aussi.
 *
 * @return array<int,array{id:string,title:string,url:string,thumb:string}>
 */
function afsac_shorts_items() {
	$raw   = trim( (string) get_theme_mod( 'afsac_shorts_list', '' ) );
	$lines = '' !== $raw ? preg_split( '/\r\n|\r|\n/', $raw ) : afsac_shorts_defaults();

	$items = array();
	foreach ( (array) $lines as $line ) {
		$line = trim( (string) $line );
		if ( '' === $line || 0 === strpos( $line, '#' ) ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		$id    = afsac_youtube_id( $parts[0] );
		if ( '' === $id || isset( $items[ $id ] ) ) {
			continue;
		}
		$items[ $id ] = array(
			'id'    => $id,
			'title' => isset( $parts[1] ) ? $parts[1] : '',
			'url'   => 'https://www.youtube.com/shorts/' . $id,
			// « oardefault » = vignette au format d'origine, donc VERTICALE pour
			// un short (hqdefault renverrait un 16/9 recadré).
			'thumb' => 'https://i.ytimg.com/vi/' . $id . '/oardefault.jpg',
		);
	}

	return array_values( $items );
}

/**
 * Customizer : section « Vidéos & Shorts » (liste éditable par le client).
 *
 * @param WP_Customize_Manager $wp_customize Gestionnaire du Customizer.
 * @return void
 */
function afsac_customize_register_shorts( $wp_customize ) {
	$wp_customize->add_section(
		'afsac_videos',
		array(
			'title'       => __( 'Vidéos & Shorts', 'afsac' ),
			'description' => __( 'Shorts YouTube affichés sur la page Contact. Une ligne par vidéo, au format « URL | Titre ». Laisser vide pour afficher les shorts de la chaîne AFSAC connus du thème.', 'afsac' ),
			'priority'    => 31,
		)
	);
	$wp_customize->add_setting(
		'afsac_shorts_list',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'afsac_shorts_list',
		array(
			'label'       => __( 'Shorts YouTube (une par ligne : URL | Titre)', 'afsac' ),
			'section'     => 'afsac_videos',
			'type'        => 'textarea',
			'input_attrs' => array( 'rows' => 10, 'placeholder' => "https://www.youtube.com/shorts/XXXXXXXXXXX | Titre de la vidéo\n" ),
		)
	);
}
add_action( 'customize_register', 'afsac_customize_register_shorts' );

/* =============================================================================
 * VISITE GUIDÉE (onboarding de première visite)
 *
 * Demande client : accueillir le visiteur qui découvre le site par une visite
 * en quelques étapes, chacune DÉSIGNANT un élément réel de la page (le menu, le
 * catalogue, le calendrier, la recherche, la brochure, le contact).
 *
 * Le contenu vit ici — donc traduisible, et modifiable sans toucher au script.
 * Le rendu vit dans template-parts/shared/tour.php, le moteur dans
 * assets/js/afsac-tour.js.
 * ========================================================================== */

/**
 * Version du SCÉNARIO de visite (≠ version du thème).
 *
 * Elle sert de clé de mémorisation côté navigateur : la visite ne se relance
 * pas toute seule tant que cette valeur ne bouge pas. L'incrémenter quand les
 * étapes changent assez pour mériter d'être remontrées aux habitués.
 */
define( 'AFSAC_TOUR_VERSION', '1' );

/**
 * La visite guidée est-elle active ? (Customizer, activée par défaut.)
 *
 * @return bool
 */
function afsac_tour_enabled() {
	return (bool) get_theme_mod( 'afsac_tour_enabled', true );
}

/**
 * Lien de RELANCE de la visite (pied de page).
 *
 * Toujours l'accueil dans la langue courante : c'est là que la visite est
 * rendue. Depuis l'accueil, le script intercepte le clic et la joue sur place ;
 * depuis une autre page, le lien y ramène et « ?visite=1 » la déclenche.
 *
 * @return string
 */
function afsac_tour_relaunch_url() {
	return add_query_arg( 'visite', '1', home_url( '/' ) );
}

/**
 * Marque les entrées de menu que la visite doit pouvoir désigner.
 *
 * Viser « le 3e lien du menu » ou une URL en dur serait cassé au premier
 * réagencement de menu (et faux en EN / AR). On repère donc les entrées par le
 * GABARIT de la page pointée — stable, et identique pour toutes les traductions
 * Polylang, qui partagent le même `_wp_page_template`.
 *
 * @param array   $atts Attributs du <a> de l'entrée de menu.
 * @param WP_Post $item Entrée de menu.
 * @return array
 */
function afsac_tour_menu_marker( $atts, $item ) {
	if ( ! isset( $item->object ) || 'page' !== $item->object || empty( $item->object_id ) ) {
		return $atts;
	}

	$map = array(
		'template-catalogue.php'           => 'catalogue',
		'template-calendrier.php'          => 'calendrier',
		'template-contact.php'             => 'contact',
		'template-formations-services.php' => 'formations',
	);

	$template = (string) get_page_template_slug( (int) $item->object_id );
	if ( isset( $map[ $template ] ) ) {
		$atts['data-afsac-tour-item'] = $map[ $template ];
	}

	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'afsac_tour_menu_marker', 10, 2 );

/**
 * Étapes de la visite guidée.
 *
 * Chaque étape : `target` liste des sélecteurs par ORDRE DE PRÉFÉRENCE (le
 * script retient le premier élément visible — d'où le repli « bouton du menu
 * mobile » quand la barre de navigation est repliée) ; `place` est une simple
 * préférence de position, le script recalcule selon la place disponible.
 *
 * Les entrées de menu sont TOUJOURS visées sous `.afsac-primary-nav` : la
 * colonne « Liens utiles » du pied de page affiche le MÊME menu, donc les mêmes
 * marqueurs. Sans ce préfixe, une étape dont l'entrée d'en-tête est repliée
 * (mobile) désignerait son jumeau du pied de page au lieu de retomber sur le
 * repli prévu, et la visite ferait des allers-retours d'un bout à l'autre.
 *
 * Une étape sans cible visible n'est pas perdue : elle s'affiche centrée.
 *
 * @return array<int,array<string,mixed>> Étapes prêtes à être encodées en JSON.
 */
function afsac_tour_steps() {
	$catalogue = function_exists( 'afsac_get_catalogue_url' ) ? afsac_get_catalogue_url() : '';
	if ( '' === $catalogue ) {
		$catalogue = home_url( '/' );
	}

	$steps = array(
		array(
			'id'     => 'bienvenue',
			'target' => '',
			'title'  => __( 'Bienvenue au Centre régional de formation de l’OACI', 'afsac' ),
			'text'   => __( 'Première visite ? En moins d’une minute, voici où trouver un cours, les prochaines sessions et comment nous joindre. Vous pouvez quitter la visite à tout moment.', 'afsac' ),
		),
		array(
			'id'     => 'menu',
			'target' => '.afsac-primary-nav .afsac-menu, .afsac-primary-nav, .afsac-menu-toggle',
			'place'  => 'bottom',
			'title'  => __( 'Tout le site part d’ici', 'afsac' ),
			'text'   => __( 'Formations & services, catalogue, calendrier, références : chaque rubrique s’ouvre depuis ce menu, en <strong>français, anglais et arabe</strong>.', 'afsac' ),
		),
		array(
			'id'     => 'catalogue',
			'target' => '.afsac-primary-nav [data-afsac-tour-item="catalogue"], .afsac-video-hero__actions .afsac-button--accent',
			'place'  => 'bottom',
			'title'  => __( 'Le catalogue des formations', 'afsac' ),
			'text'   => __( 'Les cours <strong>TRAINAIR PLUS</strong> classés par les 11 domaines de l’OACI, et l’ensemble des cursus <strong>AVSEC</strong> : cours normalisés et ateliers.', 'afsac' ),
		),
		array(
			'id'     => 'calendrier',
			'target' => '.afsac-primary-nav [data-afsac-tour-item="calendrier"], [data-afsac-fgrid]',
			'title'  => __( 'Les prochaines sessions', 'afsac' ),
			'text'   => __( 'Dates, lieu, langue et tarifs : le calendrier liste les sessions ouvertes, et chaque fiche mène au programme du cours puis à l’inscription en ligne.', 'afsac' ),
		),
		array(
			'id'     => 'recherche',
			'target' => '.afsac-search-toggle',
			'place'  => 'bottom',
			'title'  => __( 'Vous cherchez un cours précis ?', 'afsac' ),
			'text'   => __( 'Un code de cours, un mot-clé ou un domaine suffit : la recherche interroge tout le catalogue.', 'afsac' ),
		),
		array(
			'id'     => 'brochure',
			'target' => '.afsac-docband__card, .afsac-docband',
			'place'  => 'top',
			'title'  => __( 'Le programme complet en PDF', 'afsac' ),
			'text'   => __( 'Laissez votre e-mail : nous vous envoyons le lien de téléchargement de la brochure, dans la langue de votre choix.', 'afsac' ),
		),
		array(
			'id'     => 'contact',
			'target' => '.afsac-primary-nav [data-afsac-tour-item="contact"], .afsac-footer__contact, .afsac-topbar__contact',
			'title'  => __( 'Une question, un besoin sur mesure ?', 'afsac' ),
			'text'   => __( 'Demande de devis, formation dans vos locaux, conseil ou audit : notre équipe vous répond. Bonne visite !', 'afsac' ),
			'cta'    => array(
				'url'   => $catalogue,
				'label' => __( 'Découvrir le catalogue', 'afsac' ),
			),
		),
	);

	/**
	 * Filtre les étapes de la visite guidée.
	 *
	 * @param array $steps Étapes.
	 */
	$steps = (array) apply_filters( 'afsac_tour_steps', $steps );

	// Le texte est injecté en innerHTML côté script : on n'y laisse passer que
	// de la mise en valeur, jamais de balise active.
	$allowed = array(
		'strong' => array(),
		'em'     => array(),
		'br'     => array(),
	);
	foreach ( $steps as $i => $step ) {
		$steps[ $i ]['title'] = isset( $step['title'] ) ? wp_strip_all_tags( (string) $step['title'] ) : '';
		$steps[ $i ]['text']  = isset( $step['text'] ) ? wp_kses( (string) $step['text'], $allowed ) : '';
		if ( isset( $step['cta']['url'] ) ) {
			$steps[ $i ]['cta']['url']   = esc_url_raw( $step['cta']['url'] );
			$steps[ $i ]['cta']['label'] = wp_strip_all_tags( (string) $step['cta']['label'] );
		}
	}

	return array_values( $steps );
}

/**
 * Customizer : section « Visite guidée ».
 *
 * @param WP_Customize_Manager $wp_customize Gestionnaire du Customizer.
 * @return void
 */
function afsac_customize_register_tour( $wp_customize ) {
	$wp_customize->add_section(
		'afsac_tour',
		array(
			'title'       => __( 'Visite guidée', 'afsac' ),
			'description' => __( 'Visite en 7 étapes proposée au visiteur qui découvre le site. Elle ne se lance qu’une fois par navigateur ; le lien « Revoir la visite guidée » du pied de page la rejoue, tout comme l’adresse ?visite=1 sur l’accueil.', 'afsac' ),
			'priority'    => 32,
		)
	);
	$wp_customize->add_setting(
		'afsac_tour_enabled',
		array(
			'default'           => true,
			'sanitize_callback' => 'wp_validate_boolean',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'afsac_tour_enabled',
		array(
			'label'   => __( 'Proposer la visite guidée à la première visite', 'afsac' ),
			'section' => 'afsac_tour',
			'type'    => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'afsac_customize_register_tour' );
