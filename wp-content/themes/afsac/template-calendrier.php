<?php
/**
 * Template Name: Calendrier des sessions
 *
 * Page « Calendrier des sessions de formation ». DYNAMIQUE : toutes les sessions
 * à venir (CPT afsac_session, statut ≠ archive, date_début >= aujourd'hui) sont
 * rendues côté serveur avec attributs data-* ; le filtrage / tri / pagination /
 * compteurs se font côté client (sessions-filters.js), jumeau d'area-filters.js.
 *
 * Chaque ligne mêle données SESSION (dates, lieu, hôte, langue, statut) et données
 * héritées de la FORMATION liée (titre, abréviation, type, domaine, frais, mode).
 * Le mode (présentiel / virtuel / en ligne) vit sur la formation (afsac_methode +
 * afsac_modalite) — même logique que la page « Liste de cours ».
 *
 * Demande client (08/2026) — trois corrections sur la barre de filtres :
 *   1. les onglets ne trient plus par MODE (présentiel / virtuel) mais par
 *      PROGRAMME (TRAINAIR PLUS / AVSEC) : c'est la première question du
 *      visiteur, le mode reste lisible sur le badge de chaque carte ;
 *   2. l'arabe sort du sélecteur de langue (l'offre commercialisée en ligne est
 *      FR/EN ; les fiches arabes restent visibles, simplement non filtrables) ;
 *   3. plus de tri alphabétique : un calendrier se lit dans l'ordre du temps,
 *      point. Le sélecteur de tri disparaît donc entièrement.
 *
 * Les tarifs sont convertis à l'affichage dans la devise de la langue de
 * navigation (EUR en FR, USD en EN) — cf. afsac_price_display(), plugin.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Fil d'Ariane Rank Math (même wrapper que les autres templates).
if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
	ob_start();
	rank_math_the_breadcrumbs();
	$afsac_crumbs = trim( ob_get_clean() );
	if ( '' !== $afsac_crumbs ) {
		echo '<div class="afsac-breadcrumb"><div class="afsac-container">' . $afsac_crumbs . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sortie Rank Math déjà assainie (wp_kses_post).
	}
}

// --- Requête : sessions à venir, non archivées, triées par date de début. ---
$afsac_today = (int) current_time( 'Ymd' );
$afsac_query = new WP_Query(
	array(
		'post_type'      => 'afsac_session',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'meta_value',
		'meta_key'       => 'afsac_date_debut', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- tri chronologique attendu.
		'order'          => 'ASC',
		'no_found_rows'  => true,
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- filtrage métier (à venir, non archivé).
			'relation' => 'AND',
			array(
				'key'     => 'afsac_statut',
				'value'   => 'archive',
				'compare' => '!=',
			),
			array(
				'key'     => 'afsac_date_debut',
				'value'   => (string) $afsac_today,
				'compare' => '>=',
				'type'    => 'NUMERIC',
			),
		),
	)
);

// --- URL de base de la page d'inscription (lien ?session=ID par ligne). ---
$afsac_ins_base = function_exists( 'afsac_get_inscription_url' ) ? afsac_get_inscription_url() : '';

// Libellés courts de mode (badge + onglets).
$afsac_mode_short = array(
	'presentiel' => __( 'Présentiel', 'afsac' ),
	'virtuel'    => __( 'Virtuel', 'afsac' ),
	'enligne'    => __( 'En ligne', 'afsac' ),
);

// Langues EXCLUES du sélecteur (demande client : pas d'arabe dans le filtre).
// Slugs des deux variantes Polylang du terme « Arabe » / « Arabic ».
$afsac_lang_hidden = array( 'ar', 'arabe', 'arabic' );

// --- Passe d'affichage : buffer des lignes + collecte des options + compteurs. ---
$afsac_rows_html = '';
$afsac_locs      = array(); // slug => nom (lieu).
$afsac_areas       = array(); // slug => nom (domaine parent effectivement programmé).
$afsac_area_counts = array(); // slug => nombre de sessions (suffixe des options du sélecteur).
$afsac_hosts     = array(); // slug => nom (institution hôte).
$afsac_flangs    = array(); // slug => nom (langue session) — options du SÉLECTEUR (sans arabe).
$afsac_langs_all = array(); // slug => nom (langue session) — TOUTES, pour la statistique du hero.
$afsac_ftypes    = array(); // key  => nom (type).
$afsac_progs     = array( 'trainair' => 0, 'avsec' => 0 ); // Compteurs d'onglets.
$afsac_total     = 0;

if ( $afsac_query->have_posts() ) {
	ob_start();
	while ( $afsac_query->have_posts() ) :
		$afsac_query->the_post();
		$afsac_sid = get_the_ID();

		// --- Données SESSION. ---
		$afsac_debut  = (string) get_post_meta( $afsac_sid, 'afsac_date_debut', true );
		$afsac_fin    = (string) get_post_meta( $afsac_sid, 'afsac_date_fin', true );
		$afsac_lieu   = (string) get_post_meta( $afsac_sid, 'afsac_lieu', true );
		$afsac_hote   = (string) get_post_meta( $afsac_sid, 'afsac_hote', true );
		$afsac_statut = (string) get_post_meta( $afsac_sid, 'afsac_statut', true );

		$afsac_dates = function_exists( 'afsac_format_date' )
			? trim( afsac_format_date( $afsac_debut ) . ( '' !== $afsac_fin ? ' — ' . afsac_format_date( $afsac_fin ) : '' ) )
			: $afsac_debut;

		$afsac_slt   = get_the_terms( $afsac_sid, 'afsac_langue' );
		$afsac_slt   = ( $afsac_slt && ! is_wp_error( $afsac_slt ) ) ? $afsac_slt : array();
		$afsac_langn = wp_list_pluck( $afsac_slt, 'name' );
		$afsac_langg = wp_list_pluck( $afsac_slt, 'slug' );
		foreach ( $afsac_slt as $afsac_lt ) {
			// Statistique du hero : TOUTES les langues effectivement programmées —
			// des sessions en arabe existent et leur badge le dit sur la carte,
			// annoncer « 2 langues » les contredirait.
			$afsac_langs_all[ $afsac_lt->slug ] = $afsac_lt->name;
			// Sélecteur : l'arabe en est retiré (demande client).
			if ( in_array( $afsac_lt->slug, $afsac_lang_hidden, true ) ) {
				continue;
			}
			$afsac_flangs[ $afsac_lt->slug ] = $afsac_lt->name;
		}

		// --- Formation liée, résolue dans la langue courante (Polylang). ---
		$afsac_fid       = function_exists( 'afsac_get_session_formation_id_localized' ) ? afsac_get_session_formation_id_localized( $afsac_sid ) : 0;
		$afsac_formation = $afsac_fid ? get_post( $afsac_fid ) : null;
		$afsac_formation = ( $afsac_formation instanceof WP_Post && 'afsac_formation' === $afsac_formation->post_type ) ? $afsac_formation : null;

		$afsac_f_title = $afsac_formation ? get_the_title( $afsac_formation ) : get_the_title();
		$afsac_f_link  = $afsac_formation ? (string) get_permalink( $afsac_formation ) : '';
		$afsac_abbr    = ( $afsac_formation && function_exists( 'get_field' ) ) ? (string) get_field( 'afsac_abbreviation', $afsac_fid ) : '';
		$afsac_meth    = ( $afsac_formation && function_exists( 'get_field' ) ) ? (string) get_field( 'afsac_methode', $afsac_fid ) : 'instructeur';
		$afsac_reduit  = ( $afsac_formation && function_exists( 'get_field' ) ) ? (bool) get_field( 'afsac_tarif_reduit', $afsac_fid ) : false;
		$afsac_frais   = ( $afsac_formation && function_exists( 'get_field' ) ) ? get_field( 'afsac_frais_montant', $afsac_fid ) : '';
		$afsac_dev     = ( $afsac_formation && function_exists( 'get_field' ) ) ? (string) get_field( 'afsac_devise', $afsac_fid ) : '';

		// Mode = méthode + modalité (logique identique à taxonomy-afsac_area).
		$afsac_mods      = $afsac_formation ? get_the_terms( $afsac_fid, 'afsac_modalite' ) : array();
		$afsac_mods      = ( $afsac_mods && ! is_wp_error( $afsac_mods ) ) ? $afsac_mods : array();
		$afsac_mod_slugs = wp_list_pluck( $afsac_mods, 'slug' );
		if ( 'autorythme' === $afsac_meth ) {
			$afsac_mode = 'enligne';
		} elseif ( array_intersect( array( 'distanciel', 'online' ), $afsac_mod_slugs ) ) {
			$afsac_mode = 'virtuel';
		} else {
			$afsac_mode = 'presentiel';
		}
		++$afsac_total;

		// Programme (TRAINAIR PLUS / AVSEC) : axe des onglets depuis 08/2026.
		$afsac_prog = ( $afsac_fid && function_exists( 'afsac_formation_programme' ) ) ? afsac_formation_programme( $afsac_fid ) : '';
		if ( isset( $afsac_progs[ $afsac_prog ] ) ) {
			++$afsac_progs[ $afsac_prog ];
		}

		// Type (clé via term meta afsac_type_key, repli slug).
		$afsac_typs     = $afsac_formation ? get_the_terms( $afsac_fid, 'afsac_type' ) : array();
		$afsac_typs     = ( $afsac_typs && ! is_wp_error( $afsac_typs ) ) ? $afsac_typs : array();
		$afsac_type_key = '';
		if ( ! empty( $afsac_typs ) ) {
			$afsac_type_key = (string) get_term_meta( $afsac_typs[0]->term_id, 'afsac_type_key', true );
			if ( '' === $afsac_type_key ) {
				$afsac_type_key = $afsac_typs[0]->slug;
			}
			$afsac_ftypes[ $afsac_type_key ] = $afsac_typs[0]->name;
		}

		// Domaines (area) parents + sous-domaines (ces derniers pilotent le motif de vignette).
		$afsac_ars       = $afsac_formation ? get_the_terms( $afsac_fid, 'afsac_area' ) : array();
		$afsac_ars       = ( $afsac_ars && ! is_wp_error( $afsac_ars ) ) ? $afsac_ars : array();
		$afsac_area_slug = array();
		$afsac_sub_slug  = array();
		foreach ( $afsac_ars as $afsac_ar ) {
			if ( 0 === (int) $afsac_ar->parent ) {
				$afsac_area_slug[]              = $afsac_ar->slug;
				$afsac_areas[ $afsac_ar->slug ] = $afsac_ar->name;
				// Compteur par domaine : le sélecteur liste les 11 domaines OACI,
				// il doit dire lesquels ont effectivement une session programmée.
				$afsac_area_counts[ $afsac_ar->slug ] = ( isset( $afsac_area_counts[ $afsac_ar->slug ] ) ? $afsac_area_counts[ $afsac_ar->slug ] : 0 ) + 1;
			} else {
				$afsac_sub_slug[] = $afsac_ar->slug;
			}
		}

		// Hôte (option de filtre).
		$afsac_host_slug = '';
		if ( '' !== $afsac_hote ) {
			$afsac_host_slug                 = sanitize_title( $afsac_hote );
			$afsac_hosts[ $afsac_host_slug ] = $afsac_hote;
		}

		// Lieu / libellé d'emplacement. On ne recense comme « lieux » que les
		// sessions EN PRÉSENTIEL (« Virtuel » / « En ligne » ne sont pas des lieux).
		$afsac_loc_slug = '';
		if ( '' !== $afsac_lieu && 'presentiel' === $afsac_mode ) {
			$afsac_loc_slug                = sanitize_title( $afsac_lieu );
			$afsac_locs[ $afsac_loc_slug ] = $afsac_lieu;
		}
		if ( '' !== $afsac_lieu ) {
			$afsac_place = $afsac_lieu;
		} elseif ( 'enligne' === $afsac_mode ) {
			$afsac_place = __( 'En ligne', 'afsac' );
		} elseif ( 'virtuel' === $afsac_mode ) {
			$afsac_place = __( 'Virtuel', 'afsac' );
		} else {
			$afsac_place = '';
		}

		/*
		 * Découpe temporelle pour la carte de session : mois de regroupement (les
		 * en-têtes sont injectés par sessions-filters.js), libellé de dates complet
		 * et durée. (La vue carte géographique a été supprimée à la demande du
		 * client : plus de lat/lng ici.)
		 *
		 * Depuis que la VIGNETTE du cours occupe la colonne de gauche (demande
		 * client : « à la place des dates, les images », comme le calendrier de
		 * l'OACI), l'ancienne plage compacte « 02–13 » n'a plus de place dédiée :
		 * on produit directement un libellé lisible façon OACI, « 23 – 25 juil.
		 * 2026 », où l'année n'apparaît qu'une fois.
		 */
		$afsac_d1 = DateTime::createFromFormat( '!Ymd', $afsac_debut );
		$afsac_d2 = ( '' !== $afsac_fin ) ? DateTime::createFromFormat( '!Ymd', $afsac_fin ) : null;

		$afsac_mkey   = $afsac_d1 ? $afsac_d1->format( 'Y-m' ) : '';
		$afsac_mlabel = $afsac_d1 ? date_i18n( 'F Y', $afsac_d1->getTimestamp() ) : '';

		if ( $afsac_d1 && $afsac_d2 ) {
			// Même mois → « 23 – 25 juil. 2026 » ; à cheval → « 23 févr. – 3 mars 2026 ».
			$afsac_datelabel = ( $afsac_d1->format( 'Ym' ) === $afsac_d2->format( 'Ym' ) )
				? $afsac_d1->format( 'j' ) . ' – ' . date_i18n( 'j M Y', $afsac_d2->getTimestamp() )
				: date_i18n( 'j M', $afsac_d1->getTimestamp() ) . ' – ' . date_i18n( 'j M Y', $afsac_d2->getTimestamp() );
			$afsac_days      = (int) $afsac_d1->diff( $afsac_d2 )->days + 1;
		} elseif ( $afsac_d1 ) {
			$afsac_datelabel = date_i18n( 'j M Y', $afsac_d1->getTimestamp() );
			$afsac_days      = 1;
		} else {
			$afsac_datelabel = $afsac_dates;
			$afsac_days      = 0;
		}
		$afsac_duration = $afsac_days > 0
			/* translators: %s: number of days. */
			? sprintf( _n( '%s jour', '%s jours', $afsac_days, 'afsac' ), number_format_i18n( $afsac_days ) )
			: '';

		/*
		 * Vignette du cours — demande client : « les images, exactement comme
		 * celles de l'OACI ». Le calendrier de l'OACI montre une vraie photo par
		 * session ; ici 59 cours TRAINAIR PLUS sur 71 n'ont pas d'image à la une
		 * et retombaient sur le motif SVG (aplat bleu + pictogramme), jugé trop
		 * pauvre. afsac_course_thumb_url() rend l'image à la une quand elle est
		 * exploitable, sinon une photo aéronautique déterministe. Le motif SVG ne
		 * sert plus que de dernier recours (aucune photo dans le thème).
		 */
		$afsac_thumb_url = ( $afsac_fid && function_exists( 'afsac_course_thumb_url' ) )
			? afsac_course_thumb_url( $afsac_fid, 'medium' )
			: '';
		$afsac_thumb     = ( '' !== $afsac_thumb_url )
			? sprintf(
				'<img class="afsac-cal-card__thumb" src="%s" alt="" loading="lazy" decoding="async">',
				esc_url( $afsac_thumb_url )
			)
			: '';
		$afsac_motif = function_exists( 'afsac_course_motif' ) ? afsac_course_motif( $afsac_sub_slug, $afsac_type_key, $afsac_fid ? $afsac_fid : $afsac_sid ) : '';

		/*
		 * Frais (formation) — saisis une seule fois, en USD, puis CONVERTIS dans
		 * la devise de la langue de navigation (EUR en FR, USD en EN).
		 */
		$afsac_pd    = function_exists( 'afsac_price_display' ) ? afsac_price_display( $afsac_frais, $afsac_dev ) : array();
		$afsac_price = isset( $afsac_pd['amount'] ) ? $afsac_pd['amount'] : '';
		$afsac_cur   = isset( $afsac_pd['currency'] ) ? $afsac_pd['currency'] : '';

		// Action : clôturée si statut complet ou date dépassée ; sinon ?session=ID.
		$afsac_ref    = '' !== $afsac_fin ? (int) $afsac_fin : (int) $afsac_debut;
		$afsac_closed = ( 'complet' === $afsac_statut ) || ( $afsac_ref > 0 && $afsac_ref < $afsac_today );
		$afsac_regurl = ( ! $afsac_closed && '' !== $afsac_ins_base ) ? add_query_arg( 'session', $afsac_sid, $afsac_ins_base ) : '';

		get_template_part(
			'template-parts/session-row',
			null,
			array(
				'title'       => $afsac_f_title,
				'link'        => $afsac_f_link,
				'abbr'        => $afsac_abbr,
				'thumb'       => $afsac_thumb,
				'motif'       => $afsac_motif,
				'date_label'  => $afsac_datelabel,
				'mode'        => $afsac_mode,
				'mode_label'  => isset( $afsac_mode_short[ $afsac_mode ] ) ? $afsac_mode_short[ $afsac_mode ] : '',
				'programme'   => $afsac_prog,
				'prog_label'  => ( function_exists( 'afsac_programme_labels' ) && isset( afsac_programme_labels()[ $afsac_prog ] ) ) ? afsac_programme_labels()[ $afsac_prog ] : '',
				'lang_label'  => implode( ' · ', $afsac_langn ),
				'lang_slugs'  => $afsac_langg,
				'place'       => $afsac_place,
				'area_slugs'  => $afsac_area_slug,
				'type_key'    => $afsac_type_key,
				'date'        => $afsac_debut,
				'month_key'   => $afsac_mkey,
				'month_label' => $afsac_mlabel,
				'duration'    => $afsac_duration,
				'price'       => $afsac_price,
				'currency'    => $afsac_cur,
				'reg_url'     => $afsac_regurl,
				'closed'      => $afsac_closed,
				'rtl'         => (bool) in_array( 'ar', $afsac_langg, true ) || in_array( 'arabe', $afsac_langg, true ) || in_array( 'arabic', $afsac_langg, true ),
			)
		);
	endwhile;
	$afsac_rows_html = ob_get_clean();
	wp_reset_postdata();
}

asort( $afsac_locs );
asort( $afsac_areas );
asort( $afsac_hosts );
asort( $afsac_flangs );
asort( $afsac_ftypes );

/*
 * Onglets de PROGRAMME (TRAINAIR PLUS / AVSEC) — remplacent les anciens onglets
 * de mode (présentiel / virtuel) à la demande du client : c'est par programme
 * que l'offre du centre se lit, le mode restant affiché sur chaque carte.
 *
 * Même garde-fou qu'avant : on n'affiche QUE les programmes qui ont au moins une
 * session (un onglet « AVSEC (0) » est un filtre mort), et si un seul programme
 * subsiste le sélecteur entier disparaît (choisir entre « Tous » et l'unique
 * programme ne filtre rien).
 */
$afsac_progs_all = array(
	array( 'val' => 'trainair', 'label' => 'TRAINAIR PLUS', 'count' => $afsac_progs['trainair'] ),
	array( 'val' => 'avsec', 'label' => 'AVSEC', 'count' => $afsac_progs['avsec'] ),
);
$afsac_progs_used = array_values(
	array_filter(
		$afsac_progs_all,
		static function ( $afsac_p ) {
			return $afsac_p['count'] > 0;
		}
	)
);
$afsac_tabs = ( count( $afsac_progs_used ) > 1 )
	? array_merge(
		array( array( 'val' => 'all', 'label' => __( 'Tous les programmes', 'afsac' ), 'count' => $afsac_total ) ),
		$afsac_progs_used
	)
	: array();

/*
 * Chips de TYPE : n'ont de sens qu'à partir de DEUX types distincts. Avec un
 * seul type, « Tous les types » et ce type donnent presque le même résultat —
 * pire, les sessions sans type disparaissent sans explication.
 */
if ( count( $afsac_ftypes ) < 2 ) {
	$afsac_ftypes = array();
}

/*
 * Sélecteur de DOMAINE : les 11 domaines OACI de TRAINAIR PLUS, en entier
 * (demande client 07/08/2026). Il ne listait que les domaines effectivement
 * programmés — soit 5 sur 11 au 08/2026 — ce qui donnait à voir une offre plus
 * étroite qu'elle ne l'est. La liste canonique vient de la taxonomie
 * (afsac_get_area_domains(), déjà résolue dans la langue courante), et chaque
 * option porte son NOMBRE DE SESSIONS : un domaine sans session programmée reste
 * visible — il annonce le domaine — mais dit « 0 » plutôt que de mener à une
 * liste vide sans explication.
 *
 * Repli sur les domaines relevés dans les sessions si le plugin est absent.
 */
$afsac_area_opts = array();
if ( function_exists( 'afsac_get_area_domains' ) ) {
	foreach ( afsac_get_area_domains() as $afsac_dom ) {
		$afsac_area_opts[ $afsac_dom->slug ] = array(
			'label' => $afsac_dom->name,
			'count' => isset( $afsac_area_counts[ $afsac_dom->slug ] ) ? (int) $afsac_area_counts[ $afsac_dom->slug ] : 0,
		);
	}
}
if ( empty( $afsac_area_opts ) ) {
	foreach ( $afsac_areas as $afsac_slug => $afsac_name ) {
		$afsac_area_opts[ $afsac_slug ] = array(
			'label' => $afsac_name,
			'count' => isset( $afsac_area_counts[ $afsac_slug ] ) ? (int) $afsac_area_counts[ $afsac_slug ] : 0,
		);
	}
}
// Lieu UNIQUE (demande client : un seul lieu de formation, aucune carte).
// S'il n'y a qu'un lieu distinct, on l'affiche une fois dans la barre d'outils.
$afsac_place_label = ( 1 === count( $afsac_locs ) ) ? reset( $afsac_locs ) : '';

/*
 * Hero VIDÉO (bandeau client « calendrier », 17/08/2026) : plein cadre derrière
 * le texte, avec voile renforcé (`dim`). Hauteur commune à toutes les pages.
 * Le client a fourni DEUX variantes ; c'est la nº 2 qui est retenue, la nº 1
 * affichant des libellés de calendrier incohérents (« Vesday », « Frisit »,
 * dimanche en double). Ici la grille Sun→Sat et les quantièmes sont justes.
 */
get_template_part(
	'template-parts/shared/video-hero',
	null,
	array(
		'layout'    => 'overlay',
		'align'     => 'start',
		'video_src' => get_theme_file_uri( 'assets/video/calendrier.mp4' ),
		'poster'    => get_theme_file_uri( 'assets/images/calendrier-hero-poster.jpg' ),
		'eyebrow'   => __( 'Sessions OACI à venir', 'afsac' ),
		'title'     => __( 'Calendrier des sessions de formation', 'afsac' ),
		'lead'      => __( 'Toutes nos sessions programmées. Inscription en ligne, places limitées.', 'afsac' ),
		// Bandeau chargé (grille du calendrier) : voile renforcé pour qu'il reste
		// une ambiance derrière le texte.
		'dim'       => true,
		'stats'     => array(
			array(
				'value' => number_format_i18n( $afsac_total ),
				'label' => __( 'Sessions à venir', 'afsac' ),
			),
			array(
				'value' => number_format_i18n( max( 1, count( $afsac_langs_all ) ) ),
				'label' => __( 'Langues', 'afsac' ),
			),
			array(
				'value' => number_format_i18n( max( 1, count( $afsac_locs ) ) ),
				// Le libellé était figé au singulier alors que le compteur affiche
				// le nombre de villes d'accueil (10 au 08/2026) : « 10 Lieu de formation ».
				'label' => _n( 'Lieu de formation', 'Lieux de formation', max( 1, count( $afsac_locs ) ), 'afsac' ),
			),
		),
	)
);
?>

<main id="primary" class="afsac-calendar<?php echo '' !== $afsac_place_label ? ' afsac-calendar--single-place' : ''; ?>">

	<?php if ( $afsac_total > 0 ) : ?>

		<section class="afsac-calendar-filters">
			<div class="afsac-container afsac-reveal">

				<?php /* Sélecteur de PROGRAMME — masqué s'il n'y a qu'un seul programme (rien à filtrer). */ ?>
				<?php if ( ! empty( $afsac_tabs ) ) : ?>
					<nav class="afsac-calendar-tabs" aria-label="<?php esc_attr_e( 'Programmes de formation', 'afsac' ); ?>">
						<?php foreach ( $afsac_tabs as $afsac_i => $afsac_tab ) : ?>
							<button type="button" class="afsac-calendar-tab<?php echo 0 === $afsac_i ? ' is-active' : ''; ?>" data-cal-tab="<?php echo esc_attr( $afsac_tab['val'] ); ?>"<?php echo 0 === $afsac_i ? ' aria-current="true"' : ''; ?>>
								<?php echo esc_html( $afsac_tab['label'] ); ?>
								<span class="afsac-calendar-tab__count"><?php echo esc_html( number_format_i18n( $afsac_tab['count'] ) ); ?></span>
							</button>
						<?php endforeach; ?>
					</nav>
				<?php endif; ?>

				<?php /* Chips de TYPE — dérivées des sessions réelles, filtrent par afsac_type. */ ?>
				<?php if ( ! empty( $afsac_ftypes ) ) : ?>
					<div class="afsac-calendar-types" role="group" aria-label="<?php esc_attr_e( 'Types de session', 'afsac' ); ?>">
						<button type="button" class="afsac-pill afsac-pill--active" data-cal-chip=""><?php esc_html_e( 'Tous les types', 'afsac' ); ?></button>
						<?php foreach ( $afsac_ftypes as $afsac_key => $afsac_name ) : ?>
							<button type="button" class="afsac-pill" data-cal-chip="<?php echo esc_attr( $afsac_key ); ?>"><?php echo esc_html( $afsac_name ); ?></button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php
				/*
				 * Barre de filtres SIMPLIFIÉE (demande client) : plus de « Localisation »
				 * ni d'« Institution hôte » (un seul lieu), plus de plage de dates
				 * « Du / Au », plus de case « tarif réduit », plus de bascule Carte,
				 * et depuis 08/2026 plus de sélecteur de TRI (le classement reste
				 * chronologique, seul ordre qui a du sens pour un calendrier).
				 * Restent : domaine, langue, recherche.
				 */
				?>
				<div class="afsac-calendar-filterbar">
					<?php /* Les 11 domaines OACI, avec le nombre de sessions programmées. */ ?>
					<?php if ( ! empty( $afsac_area_opts ) ) : ?>
						<select class="afsac-field afsac-field--select afsac-field--area" data-cal-filter="area" aria-label="<?php esc_attr_e( 'Domaine', 'afsac' ); ?>">
							<option value=""><?php esc_html_e( 'Tous les domaines', 'afsac' ); ?></option>
							<?php foreach ( $afsac_area_opts as $afsac_slug => $afsac_opt ) : ?>
								<option value="<?php echo esc_attr( $afsac_slug ); ?>"><?php echo esc_html( $afsac_opt['label'] . ' (' . number_format_i18n( $afsac_opt['count'] ) . ')' ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
					<?php if ( ! empty( $afsac_flangs ) ) : ?>
						<select class="afsac-field afsac-field--select" data-cal-filter="lang" aria-label="<?php esc_attr_e( 'Langue', 'afsac' ); ?>">
							<option value=""><?php esc_html_e( 'Toutes les langues', 'afsac' ); ?></option>
							<?php foreach ( $afsac_flangs as $afsac_slug => $afsac_name ) : ?>
								<option value="<?php echo esc_attr( $afsac_slug ); ?>"><?php echo esc_html( $afsac_name ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
					<div class="afsac-field afsac-field--icon afsac-field--search">
						<svg class="afsac-field__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
						<input type="search" class="afsac-field__input" data-cal-search placeholder="<?php esc_attr_e( 'Rechercher…', 'afsac' ); ?>" aria-label="<?php esc_attr_e( 'Rechercher une session', 'afsac' ); ?>">
					</div>
					<?php if ( '' !== $afsac_place_label ) : ?>
						<span class="afsac-calendar-place">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/></svg>
							<?php
							printf(
								/* translators: %s: unique training location. */
								esc_html__( 'Toutes les sessions à %s', 'afsac' ),
								'<b>' . esc_html( $afsac_place_label ) . '</b>'
							);
							?>
						</span>
					<?php endif; ?>
				</div>

				<div class="afsac-calendar-resulthead">
					<span class="afsac-calendar-resultcount" data-cal-count data-singular="<?php esc_attr_e( 'session à venir', 'afsac' ); ?>" data-plural="<?php esc_attr_e( 'sessions à venir', 'afsac' ); ?>"></span>
				</div>

			</div>
		</section>

		<section class="afsac-cal-list">
			<div class="afsac-container">
				<?php /* Cartes de session. Les en-têtes de mois sont insérés par sessions-filters.js. */ ?>
				<div class="afsac-cal-cards" data-cal-rows data-page-size="10">
					<?php echo $afsac_rows_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Cartes échappées dans session-row.php. ?>
				</div>

				<p class="afsac-cal-noresult" data-cal-noresult hidden><?php esc_html_e( 'Aucune session ne correspond à vos critères.', 'afsac' ); ?></p>

				<nav class="afsac-cal-pagination" data-cal-pager aria-label="<?php esc_attr_e( 'Pagination', 'afsac' ); ?>"></nav>
			</div>
		</section>

	<?php else : ?>

		<section class="afsac-cal-list">
			<div class="afsac-container">
				<div class="afsac-area__empty">
					<h2 class="afsac-area__empty-title"><?php esc_html_e( 'Aucune session programmée pour le moment', 'afsac' ); ?></h2>
					<p class="afsac-area__empty-text"><?php esc_html_e( 'De nouvelles sessions sont planifiées régulièrement. Contactez-nous pour organiser une session sur mesure ou recevoir le calendrier détaillé.', 'afsac' ); ?></p>
					<a class="afsac-button" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Demander une session', 'afsac' ); ?></a>
				</div>
			</div>
		</section>

	<?php endif; ?>

	<?php /* Section « formations spécifiques » + bouton « Autres » — affichée même sans session programmée. */ ?>
	<?php get_template_part( 'template-parts/page-formations/formations-demande' ); ?>

</main>

<?php
get_footer();
