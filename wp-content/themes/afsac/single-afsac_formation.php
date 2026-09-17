<?php
/**
 * Fiche formation (single) — maquette « Détail d'un cours ».
 *
 * Réutilise la couche afsac-core (sessions, helpers, accent) et les champs ACF.
 * Chaque bloc n'est rendu que si sa donnée existe. Héros teinté par l'accent du
 * domaine ; vignette = image à la une, sinon motif SVG (helper afsac_course_motif).
 * JSON-LD Course émis séparément par afsac-core/includes/schema.php.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
	ob_start();
	rank_math_the_breadcrumbs();
	$afsac_crumbs = trim( ob_get_clean() );
	if ( '' !== $afsac_crumbs ) {
		echo '<div class="afsac-breadcrumb"><div class="afsac-container">' . $afsac_crumbs . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sortie Rank Math déjà assainie.
	}
}

while ( have_posts() ) :
	the_post();

	$afsac_id      = get_the_ID();
	$afsac_has_acf = function_exists( 'get_field' );

	$afsac_abbr         = $afsac_has_acf ? (string) get_field( 'afsac_abbreviation', $afsac_id ) : '';
	$afsac_code         = $afsac_has_acf ? (string) get_field( 'afsac_code', $afsac_id ) : '';
	$afsac_note         = $afsac_has_acf ? (float) get_field( 'afsac_note', $afsac_id ) : 0;
	$afsac_objectifs    = $afsac_has_acf ? get_field( 'afsac_objectifs', $afsac_id ) : '';
	$afsac_structure    = $afsac_has_acf ? (string) get_field( 'afsac_structure', $afsac_id ) : '';
	$afsac_public_cible = $afsac_has_acf ? get_field( 'afsac_public_cible', $afsac_id ) : '';
	$afsac_prerequis    = $afsac_has_acf ? (string) get_field( 'afsac_prerequis', $afsac_id ) : '';
	$afsac_resultats    = $afsac_has_acf ? get_field( 'afsac_resultats', $afsac_id ) : '';
	$afsac_dev_par      = $afsac_has_acf ? (string) get_field( 'afsac_developpe_par', $afsac_id ) : '';
	$afsac_dev_detail   = $afsac_has_acf ? (string) get_field( 'afsac_developpe_par_detail', $afsac_id ) : '';
	$afsac_autres_lang  = $afsac_has_acf ? (string) get_field( 'afsac_autres_langues', $afsac_id ) : '';
	$afsac_niveau       = $afsac_has_acf ? (string) get_field( 'afsac_niveau', $afsac_id ) : '';
	$afsac_public_res   = $afsac_has_acf ? (string) get_field( 'afsac_public_resume', $afsac_id ) : '';
	$afsac_duree        = $afsac_has_acf ? (string) get_field( 'afsac_duree', $afsac_id ) : '';
	$afsac_frais        = $afsac_has_acf ? get_field( 'afsac_frais_montant', $afsac_id ) : '';
	$afsac_devise       = $afsac_has_acf ? (string) get_field( 'afsac_devise', $afsac_id ) : '';
	$afsac_methode      = $afsac_has_acf ? (string) get_field( 'afsac_methode', $afsac_id ) : '';
	$afsac_certificat   = $afsac_has_acf ? (bool) get_field( 'afsac_certificat', $afsac_id ) : false;
	$afsac_cert_label   = $afsac_has_acf ? (string) get_field( 'afsac_certificat_intitule', $afsac_id ) : '';

	/*
	 * Fiche descriptive officielle (PDF). Le champ ne stocke qu'un ID : l'URL et le
	 * poids sont relus ici, ce qui reste juste même si le média est remplacé. Le
	 * champ est par post, donc par langue — la fiche FR ne propose que l'édition
	 * française, la fiche EN que l'anglaise (aucune édition arabe publiée).
	 */
	$afsac_pdf_id   = $afsac_has_acf ? (int) get_field( 'afsac_fiche_pdf', $afsac_id ) : 0;
	$afsac_pdf_url  = $afsac_pdf_id ? (string) wp_get_attachment_url( $afsac_pdf_id ) : '';
	$afsac_pdf_path = $afsac_pdf_id ? (string) get_attached_file( $afsac_pdf_id ) : '';
	$afsac_pdf_size = ( '' !== $afsac_pdf_path && file_exists( $afsac_pdf_path ) ) ? size_format( (int) filesize( $afsac_pdf_path ) ) : '';

	// --- Taxonomies du cours. ---
	$afsac_langues   = get_the_terms( $afsac_id, 'afsac_langue' );
	$afsac_modalites = get_the_terms( $afsac_id, 'afsac_modalite' );
	$afsac_areas     = get_the_terms( $afsac_id, 'afsac_area' );
	$afsac_types     = get_the_terms( $afsac_id, 'afsac_type' );
	$afsac_langues   = ( $afsac_langues && ! is_wp_error( $afsac_langues ) ) ? $afsac_langues : array();
	$afsac_modalites = ( $afsac_modalites && ! is_wp_error( $afsac_modalites ) ) ? $afsac_modalites : array();
	$afsac_areas     = ( $afsac_areas && ! is_wp_error( $afsac_areas ) ) ? $afsac_areas : array();
	$afsac_types     = ( $afsac_types && ! is_wp_error( $afsac_types ) ) ? $afsac_types : array();

	// Domaine (parent) + sous-domaine (enfant).
	$afsac_domain = null;
	$afsac_child  = null;
	foreach ( $afsac_areas as $afsac_term ) {
		if ( 0 === (int) $afsac_term->parent && ! $afsac_domain ) {
			$afsac_domain = $afsac_term;
		} elseif ( (int) $afsac_term->parent > 0 && ! $afsac_child ) {
			$afsac_child = $afsac_term;
		}
	}
	if ( $afsac_child && ! $afsac_domain ) {
		$afsac_parent = get_term( $afsac_child->parent, 'afsac_area' );
		if ( $afsac_parent && ! is_wp_error( $afsac_parent ) ) {
			$afsac_domain = $afsac_parent;
		}
	}

	// Accent du domaine pour le héros (consommé en fond par .afsac-formation-hero ; repli navy).
	$afsac_accent      = ( $afsac_domain && function_exists( 'afsac_get_area_accent_color' ) ) ? afsac_get_area_accent_color( $afsac_domain ) : '';
	$afsac_hero_accent = ( '' !== $afsac_accent ) ? $afsac_accent : 'var(--afsac-navy-mid)';

	// Type de cours (badge) + clé stable + motif de repli.
	$afsac_type_name = '';
	$afsac_type_key  = '';
	if ( ! empty( $afsac_types ) ) {
		$afsac_type_name = $afsac_types[0]->name;
		$afsac_type_key  = (string) get_term_meta( $afsac_types[0]->term_id, 'afsac_type_key', true );
		if ( '' === $afsac_type_key ) {
			$afsac_type_key = $afsac_types[0]->slug;
		}
	}
	$afsac_sub_slugs = $afsac_child ? array( $afsac_child->slug ) : array();
	$afsac_motif     = function_exists( 'afsac_course_motif' ) ? afsac_course_motif( $afsac_sub_slugs, $afsac_type_key, $afsac_id ) : '';

	// Lien « Retour » : archive du domaine, sinon hub F&S.
	$afsac_back_url = '';
	if ( $afsac_domain ) {
		$afsac_link = get_term_link( $afsac_domain );
		if ( ! is_wp_error( $afsac_link ) ) {
			$afsac_back_url = $afsac_link;
		}
	}
	if ( ! $afsac_back_url ) {
		$afsac_hub = get_posts(
			array(
				'post_type'        => 'page',
				'posts_per_page'   => 1,
				'fields'           => 'ids',
				'meta_key'         => '_wp_page_template',
				'meta_value'       => 'template-formations-services.php',
				'suppress_filters' => false,
			)
		);
		if ( ! empty( $afsac_hub ) ) {
			$afsac_back_url = get_permalink( (int) $afsac_hub[0] );
		}
	}

	// Niveau : slug → libellé traduit.
	$afsac_niveaux_labels = array(
		'initiation'    => __( 'Initiation', 'afsac' ),
		'fondamental'   => __( 'Fondamental', 'afsac' ),
		'intermediaire' => __( 'Intermédiaire', 'afsac' ),
		'avance'        => __( 'Avancé', 'afsac' ),
		'technique'     => __( 'Technique', 'afsac' ),
		'management'    => __( 'Management', 'afsac' ),
	);
	$afsac_niveau_label = ( $afsac_niveau && isset( $afsac_niveaux_labels[ $afsac_niveau ] ) )
		? $afsac_niveaux_labels[ $afsac_niveau ]
		: $afsac_niveau;

	// Formatteur de plage de dates (Ymd → libellé localisé).
	$afsac_format_range = static function ( $debut, $fin ) {
		$d = DateTime::createFromFormat( 'Ymd', (string) $debut );
		if ( ! $d ) {
			return '';
		}
		$ts1 = $d->getTimestamp();
		if ( '' === (string) $fin ) {
			return wp_date( 'j M Y', $ts1 );
		}
		$f = DateTime::createFromFormat( 'Ymd', (string) $fin );
		if ( ! $f ) {
			return wp_date( 'j M Y', $ts1 );
		}
		$ts2 = $f->getTimestamp();
		if ( wp_date( 'Y', $ts1 ) !== wp_date( 'Y', $ts2 ) ) {
			return wp_date( 'j M Y', $ts1 ) . ' — ' . wp_date( 'j M Y', $ts2 );
		}
		if ( wp_date( 'm', $ts1 ) === wp_date( 'm', $ts2 ) ) {
			return wp_date( 'j', $ts1 ) . ' — ' . wp_date( 'j M Y', $ts2 );
		}
		return wp_date( 'j M', $ts1 ) . ' — ' . wp_date( 'j M Y', $ts2 );
	};

	// Cible d'inscription : page « inscription » sinon « contact » (Polylang-aware) ; ?formation=slug.
	$afsac_target = get_page_by_path( 'inscription' );
	if ( ! $afsac_target ) {
		$afsac_target = get_page_by_path( 'contact' );
	}
	if ( $afsac_target && function_exists( 'pll_get_post' ) ) {
		$afsac_tr = pll_get_post( $afsac_target->ID );
		if ( $afsac_tr ) {
			$afsac_target = get_post( $afsac_tr );
		}
	}
	$afsac_slug     = get_post_field( 'post_name', $afsac_id );
	$afsac_ins_base = function_exists( 'afsac_get_inscription_url' ) ? afsac_get_inscription_url() : '';
	if ( '' === $afsac_ins_base ) {
		$afsac_ins_base = $afsac_target ? get_permalink( $afsac_target ) : home_url( '/contact/' );
	}
	$afsac_reg_url = add_query_arg( 'formation', $afsac_slug, $afsac_ins_base );

	// Noms de termes pré-calculés.
	$afsac_lang_names = wp_list_pluck( $afsac_langues, 'name' );
	$afsac_mod_names  = wp_list_pluck( $afsac_modalites, 'name' );

	// Mode de prestation = méthode + modalité (même libellé que la liste de cours).
	$afsac_mode_label = '';
	if ( 'autorythme' === $afsac_methode ) {
		$afsac_mode_label = __( 'En ligne (auto-rythmé)', 'afsac' );
	} elseif ( 'instructeur' === $afsac_methode || $afsac_mod_names ) {
		$afsac_mode_label = __( 'Avec instructeur', 'afsac' );
		if ( $afsac_mod_names ) {
			$afsac_mode_label .= ' (' . implode( ' · ', $afsac_mod_names ) . ')';
		}
	}

	$afsac_has_frais = ( '' !== (string) $afsac_frais && null !== $afsac_frais );
	$afsac_has_cert  = ( $afsac_certificat || '' !== $afsac_cert_label );

	/*
	 * La carte « Informations sur le cours » ne porte plus QUE ce que la rangée
	 * de faits clés du héros n'affiche pas (langue, mode, durée, niveau,
	 * certificat et frais y sont déjà) : sinon le même tableau se lisait deux
	 * fois dans le même écran.
	 */
	$afsac_has_info = ( '' !== $afsac_autres_lang || '' !== $afsac_public_res );

	$afsac_sessions = function_exists( 'afsac_get_formation_sessions_for_display' )
		? afsac_get_formation_sessions_for_display( $afsac_id )
		: array();

	/*
	 * FAITS CLÉS du héros. Le catalogue est très inégalement renseigné (durée
	 * 72 % des fiches, méthode 27 %, niveau 16 %, frais 11 %) : chaque fait
	 * n'est ajouté que s'il existe, et la rangée entière disparaît si le cours
	 * n'en porte aucun — pas de gabarit à trous.
	 */
	$afsac_facts = array();
	if ( '' !== $afsac_duree ) {
		$afsac_facts[] = array( 'icon' => 'duration', 'label' => __( 'Durée', 'afsac' ), 'value' => $afsac_duree );
	}
	if ( $afsac_lang_names ) {
		$afsac_facts[] = array( 'icon' => 'lang', 'label' => __( 'Langue', 'afsac' ), 'value' => implode( ', ', $afsac_lang_names ) );
	}
	if ( '' !== $afsac_mode_label ) {
		$afsac_facts[] = array( 'icon' => 'method', 'label' => __( 'Mode de prestation', 'afsac' ), 'value' => $afsac_mode_label );
	}
	if ( '' !== $afsac_niveau_label ) {
		$afsac_facts[] = array( 'icon' => 'level', 'label' => __( 'Niveau', 'afsac' ), 'value' => $afsac_niveau_label );
	}
	if ( $afsac_has_cert ) {
		$afsac_facts[] = array( 'icon' => 'award', 'label' => __( 'Certificat', 'afsac' ), 'value' => ( '' !== $afsac_cert_label ? $afsac_cert_label : __( 'Oui', 'afsac' ) ) );
	}
	if ( $afsac_has_frais ) {
		$afsac_pd_hero = function_exists( 'afsac_price_display' ) ? afsac_price_display( $afsac_frais, $afsac_devise ) : array();
		$afsac_facts[] = array(
			'icon'  => 'fee',
			'label' => __( 'Frais', 'afsac' ),
			'value' => ! empty( $afsac_pd_hero ) ? $afsac_pd_hero['amount'] . ' ' . $afsac_pd_hero['currency'] : number_format_i18n( (float) $afsac_frais, 2 ),
		);
	}

	// Le CTA du héros dit la même chose que celui de la colonne : session ferme
	// s'il en existe une, demande sinon.
	$afsac_cta_label = ! empty( $afsac_sessions ) ? __( 'S’inscrire à ce cours', 'afsac' ) : __( 'Demander une session', 'afsac' );
	?>

	<article id="post-<?php echo esc_attr( $afsac_id ); ?>" <?php post_class( 'afsac-formation' ); ?> style="--afsac-hero-accent: <?php echo esc_attr( $afsac_hero_accent ); ?>;">

		<?php /* HERO */ ?>
		<header class="afsac-formation-hero">
			<div class="afsac-container afsac-formation-hero__inner">
				<?php if ( $afsac_back_url ) : ?>
					<a class="afsac-formation-hero__back" href="<?php echo esc_url( $afsac_back_url ); ?>"><span aria-hidden="true">‹</span> <?php esc_html_e( 'Retour', 'afsac' ); ?></a>
				<?php endif; ?>

				<div class="afsac-formation-hero__body">
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="afsac-formation-hero__media"><?php the_post_thumbnail( 'medium_large' ); ?></figure>
					<?php elseif ( '' !== $afsac_motif ) : ?>
						<div class="afsac-formation-hero__media afsac-formation-hero__media--motif" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false"><?php echo $afsac_motif; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Motif SVG statique du registre interne. ?></svg>
						</div>
					<?php endif; ?>

					<div class="afsac-formation-hero__head">
						<h1 class="afsac-formation-hero__title"><?php the_title(); ?><?php if ( '' !== $afsac_abbr ) : ?> <span class="afsac-formation-hero__abbr"><?php echo esc_html( $afsac_abbr ); ?></span><?php endif; ?></h1>

						<?php if ( '' !== $afsac_code ) : ?>
							<p class="afsac-formation-hero__code"><?php echo esc_html( $afsac_code ); ?></p>
						<?php endif; ?>

						<?php if ( '' !== $afsac_type_name || $afsac_note > 0 ) : ?>
							<div class="afsac-formation-hero__row">
								<?php if ( '' !== $afsac_type_name ) : ?>
									<span class="afsac-badge"><?php echo esc_html( $afsac_type_name ); ?></span>
								<?php endif; ?>
								<?php
								if ( $afsac_note > 0 ) :
									$afsac_full = max( 0, min( 5, (int) round( $afsac_note ) ) );
									?>
									<span class="afsac-stars" aria-hidden="true"><?php echo esc_html( str_repeat( '★', $afsac_full ) . str_repeat( '☆', 5 - $afsac_full ) ); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php /* Faits clés : ce qu'un acheteur cherche avant de lire le programme. */ ?>
						<?php if ( $afsac_facts ) : ?>
							<dl class="afsac-formation-facts">
								<?php foreach ( $afsac_facts as $afsac_fact ) : ?>
									<div class="afsac-formation-facts__item">
										<?php echo function_exists( 'afsac_meta_icon' ) ? afsac_meta_icon( $afsac_fact['icon'] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG du registre interne. ?>
										<div>
											<dt class="afsac-formation-facts__label"><?php echo esc_html( $afsac_fact['label'] ); ?></dt>
											<dd class="afsac-formation-facts__value"><?php echo esc_html( $afsac_fact['value'] ); ?></dd>
										</div>
									</div>
								<?php endforeach; ?>
							</dl>
						<?php endif; ?>

						<div class="afsac-formation-hero__actions">
							<a class="afsac-button" href="<?php echo esc_url( $afsac_reg_url ); ?>"><?php echo esc_html( $afsac_cta_label ); ?></a>
							<?php if ( ! empty( $afsac_sessions ) ) : ?>
								<a class="afsac-formation-hero__jump" href="#afsac-sessions"><?php esc_html_e( 'Voir les dates', 'afsac' ); ?> ›</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</header>

		<?php /* BANDEAU CONTEXTE */ ?>
		<?php
		if ( $afsac_domain ) :
			$afsac_domain_link = get_term_link( $afsac_domain );
			$afsac_child_link  = $afsac_child ? get_term_link( $afsac_child ) : '';
			?>
			<div class="afsac-formation-context">
				<div class="afsac-container afsac-formation-context__inner">
					<span class="afsac-formation-context__dot" aria-hidden="true"></span>
					<?php if ( ! is_wp_error( $afsac_domain_link ) ) : ?>
						<a class="afsac-formation-context__link" href="<?php echo esc_url( $afsac_domain_link ); ?>"><?php echo esc_html( $afsac_domain->name ); ?></a>
					<?php else : ?>
						<span class="afsac-formation-context__link"><?php echo esc_html( $afsac_domain->name ); ?></span>
					<?php endif; ?>
					<?php if ( $afsac_child ) : ?>
						<span class="afsac-formation-context__sep" aria-hidden="true">›</span>
						<?php if ( ! is_wp_error( $afsac_child_link ) ) : ?>
							<a class="afsac-formation-context__link" href="<?php echo esc_url( $afsac_child_link ); ?>"><?php echo esc_html( $afsac_child->name ); ?></a>
						<?php else : ?>
							<span class="afsac-formation-context__link"><?php echo esc_html( $afsac_child->name ); ?></span>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php /* GRILLE PRINCIPALE */ ?>
		<div class="afsac-container">
			<div class="afsac-formation-layout">

				<div class="afsac-formation-main">
					<?php if ( '' !== trim( get_the_content() ) ) : ?>
						<section class="afsac-formation-section">
							<h2 class="afsac-formation-section__title"><?php esc_html_e( 'Objectif', 'afsac' ); ?></h2>
							<div class="afsac-entry-content"><?php the_content(); ?></div>
						</section>
					<?php endif; ?>

					<?php if ( $afsac_objectifs ) : ?>
						<section class="afsac-formation-section">
							<h2 class="afsac-formation-section__title"><?php esc_html_e( 'Objectifs pédagogiques', 'afsac' ); ?></h2>
							<div class="afsac-formation-section__content"><?php echo wp_kses_post( afsac_repair_wrapped_list( $afsac_objectifs ) ); ?></div>
						</section>
					<?php endif; ?>

					<?php
					$afsac_modules = ( '' !== trim( $afsac_structure ) )
						? array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $afsac_structure ) ), 'strlen' ) )
						: array();
					if ( ! empty( $afsac_modules ) ) :
						?>
						<section class="afsac-formation-section">
							<h2 class="afsac-formation-section__title"><?php esc_html_e( 'Structure', 'afsac' ); ?></h2>
							<ol class="afsac-formation-modules">
								<?php
								foreach ( $afsac_modules as $afsac_i => $afsac_module ) :
									/* translators: %d: numéro de module (commence à 0). */
									$afsac_prefix = sprintf( __( 'Module %d :', 'afsac' ), $afsac_i );
									?>
									<li class="afsac-formation-modules__item"><strong class="afsac-formation-modules__label"><?php echo esc_html( $afsac_prefix ); ?></strong> <?php echo esc_html( $afsac_module ); ?></li>
								<?php endforeach; ?>
							</ol>
						</section>
					<?php endif; ?>

					<?php if ( $afsac_public_cible ) : ?>
						<section class="afsac-formation-section">
							<h2 class="afsac-formation-section__title"><?php esc_html_e( 'Public cible', 'afsac' ); ?></h2>
							<div class="afsac-formation-section__content"><?php echo wp_kses_post( afsac_repair_wrapped_list( $afsac_public_cible ) ); ?></div>
						</section>
					<?php endif; ?>

					<?php if ( '' !== trim( $afsac_prerequis ) ) : ?>
						<section class="afsac-formation-section">
							<h2 class="afsac-formation-section__title"><?php esc_html_e( 'Pré-requis', 'afsac' ); ?></h2>
							<div class="afsac-formation-section__content"><?php echo wp_kses_post( wpautop( $afsac_prerequis ) ); ?></div>
						</section>
					<?php endif; ?>

					<?php if ( $afsac_resultats ) : ?>
						<section class="afsac-formation-section">
							<h2 class="afsac-formation-section__title"><?php esc_html_e( 'Résultats attendus', 'afsac' ); ?></h2>
							<div class="afsac-formation-section__content"><?php echo wp_kses_post( afsac_repair_wrapped_list( $afsac_resultats ) ); ?></div>
						</section>
					<?php endif; ?>
				</div>

				<aside class="afsac-formation-aside">

					<?php /* a) Développé par */ ?>
					<?php if ( '' !== $afsac_dev_par ) : ?>
						<div class="afsac-card afsac-card--center">
							<span class="afsac-eyebrow afsac-card__eyebrow"><?php esc_html_e( 'Développé par', 'afsac' ); ?></span>
							<p class="afsac-card__dev"><strong><?php echo esc_html( $afsac_dev_par ); ?></strong></p>
							<?php if ( '' !== $afsac_dev_detail ) : ?>
								<p class="afsac-card__dev-detail"><?php echo esc_html( $afsac_dev_detail ); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php /* a bis) Fiche descriptive officielle (PDF fourni par le centre). */ ?>
					<?php if ( '' !== $afsac_pdf_url ) : ?>
						<div class="afsac-card afsac-card--pdf">
							<span class="afsac-eyebrow afsac-card__eyebrow"><?php esc_html_e( 'Fiche descriptive', 'afsac' ); ?></span>
							<p class="afsac-card__pdf-text"><?php esc_html_e( 'Objectifs, contenu, public visé et conditions d’accès dans le document officiel du centre.', 'afsac' ); ?></p>
							<a class="afsac-button afsac-card__pdf-cta" href="<?php echo esc_url( $afsac_pdf_url ); ?>" download>
								<svg class="afsac-card__pdf-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
									<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><polyline points="7 10 12 15 17 10" /><line x1="12" y1="15" x2="12" y2="3" />
								</svg>
								<?php esc_html_e( 'Télécharger le PDF', 'afsac' ); ?>
							</a>
							<?php if ( '' !== $afsac_pdf_size ) : ?>
								<p class="afsac-card__pdf-meta"><?php echo esc_html( sprintf( /* translators: %s: poids du fichier, ex. « 793 KB ». */ __( 'PDF · %s', 'afsac' ), $afsac_pdf_size ) ); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php /* b) Informations sur le cours */ ?>
					<?php if ( $afsac_has_info ) : ?>
						<div class="afsac-card">
							<span class="afsac-eyebrow afsac-card__eyebrow"><?php esc_html_e( 'Informations sur le cours', 'afsac' ); ?></span>
							<dl class="afsac-spec-list">
								<?php if ( '' !== $afsac_autres_lang ) : ?>
									<div class="afsac-spec-list__row"><dt><?php esc_html_e( 'Autres langues', 'afsac' ); ?></dt><dd><?php echo esc_html( $afsac_autres_lang ); ?></dd></div>
								<?php endif; ?>
								<?php if ( '' !== $afsac_public_res ) : ?>
									<div class="afsac-spec-list__row"><dt><?php esc_html_e( 'Public', 'afsac' ); ?></dt><dd><?php echo esc_html( $afsac_public_res ); ?></dd></div>
								<?php endif; ?>
							</dl>
						</div>
					<?php endif; ?>

					<?php /* c) Prochaines sessions */ ?>
					<div class="afsac-card afsac-formation-sessions" id="afsac-sessions">
						<span class="afsac-eyebrow afsac-card__eyebrow"><?php esc_html_e( 'Prochaines sessions', 'afsac' ); ?></span>

						<?php if ( ! empty( $afsac_sessions ) ) : ?>
							<ul class="afsac-session-list">
								<?php
								foreach ( $afsac_sessions as $afsac_session ) :
									$afsac_sid    = $afsac_session->ID;
									$afsac_range  = $afsac_format_range( get_post_meta( $afsac_sid, 'afsac_date_debut', true ), get_post_meta( $afsac_sid, 'afsac_date_fin', true ) );
									$afsac_lieu_s = (string) get_post_meta( $afsac_sid, 'afsac_lieu', true );
									$afsac_places = get_post_meta( $afsac_sid, 'afsac_places', true );
									$afsac_statut = get_post_meta( $afsac_sid, 'afsac_statut', true );
									$afsac_s_lt   = get_the_terms( $afsac_sid, 'afsac_langue' );
									$afsac_s_lang = ( $afsac_s_lt && ! is_wp_error( $afsac_s_lt ) ) ? implode( ' / ', wp_list_pluck( $afsac_s_lt, 'name' ) ) : '';

									$afsac_meta = array();
									if ( '' !== $afsac_lieu_s ) {
										$afsac_meta[] = '<span>' . esc_html( $afsac_lieu_s ) . '</span>';
									}
									if ( '' !== $afsac_s_lang ) {
										$afsac_meta[] = '<span>' . esc_html( $afsac_s_lang ) . '</span>';
									}
									if ( 'complet' === $afsac_statut ) {
										$afsac_meta[] = '<span class="afsac-badge afsac-badge--full">' . esc_html__( 'Complet', 'afsac' ) . '</span>';
									} elseif ( '' !== (string) $afsac_places && null !== $afsac_places ) {
										$afsac_meta[] = '<span class="afsac-session-list__seats">' . esc_html( number_format_i18n( (int) $afsac_places ) . ' ' . __( 'places', 'afsac' ) ) . '</span>';
									}
									?>
									<li class="afsac-session-list__item">
										<?php if ( '' !== $afsac_range ) : ?>
											<span class="afsac-session-list__date"><?php echo esc_html( $afsac_range ); ?></span>
										<?php endif; ?>
										<?php if ( $afsac_meta ) : ?>
											<span class="afsac-session-list__meta"><?php echo implode( ' · ', $afsac_meta ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fragments déjà échappés. ?></span>
										<?php endif; ?>
										<?php if ( 'complet' !== $afsac_statut ) : ?>
											<a class="afsac-session-list__cta" href="<?php echo esc_url( add_query_arg( 'session', $afsac_sid, $afsac_ins_base ) ); ?>"><?php esc_html_e( 'S’inscrire', 'afsac' ); ?> ›</a>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
							<a class="afsac-button afsac-formation-sessions__cta" href="<?php echo esc_url( $afsac_reg_url ); ?>"><?php esc_html_e( 'Inscription', 'afsac' ); ?></a>
						<?php else : ?>
							<p class="afsac-formation-sessions__empty"><?php esc_html_e( 'Aucune session programmée pour le moment.', 'afsac' ); ?></p>
							<?php /* Contour (pas --invert : blanc sur blanc sur la carte claire). */ ?>
							<a class="afsac-button afsac-formation-sessions__cta afsac-formation-sessions__cta--outline" href="<?php echo esc_url( $afsac_reg_url ); ?>"><?php esc_html_e( 'Demander une session', 'afsac' ); ?></a>
						<?php endif; ?>
					</div>

					<?php
					/*
					 * d) Carte « Une question ? » — TOUJOURS rendue.
					 *
					 * Le catalogue est très inégalement renseigné : sur une fiche pauvre
					 * (ni développeur, ni infos, ni session) la colonne se réduisait à une
					 * seule petite carte suivie de ~500 px de blanc. Cette carte ferme la
					 * colonne et donne une porte de sortie utile.
					 */
					$afsac_coord = function_exists( 'afsac_get_contact' ) ? afsac_get_contact() : array();
					$afsac_tel   = isset( $afsac_coord['phone_display'] ) ? (string) $afsac_coord['phone_display'] : '';
					$afsac_mail  = isset( $afsac_coord['email'] ) ? (string) $afsac_coord['email'] : '';
					$afsac_c_pg  = get_page_by_path( 'contact' );
					if ( $afsac_c_pg && function_exists( 'pll_get_post' ) ) {
						$afsac_c_tr = pll_get_post( $afsac_c_pg->ID );
						if ( $afsac_c_tr ) {
							$afsac_c_pg = get_post( $afsac_c_tr );
						}
					}
					$afsac_contact_url = $afsac_c_pg ? get_permalink( $afsac_c_pg ) : home_url( '/contact/' );
					?>
					<div class="afsac-card afsac-formation-help">
						<span class="afsac-eyebrow afsac-card__eyebrow"><?php esc_html_e( 'Une question ?', 'afsac' ); ?></span>
						<p class="afsac-formation-help__text"><?php esc_html_e( 'Programme détaillé, session sur mesure dans votre État, tarif de groupe : notre équipe vous répond.', 'afsac' ); ?></p>
						<ul class="afsac-formation-help__list">
							<?php if ( '' !== $afsac_tel ) : ?>
								<li><a href="<?php echo esc_url( 'tel:' . afsac_tel_href( $afsac_tel ) ); ?>"><?php echo esc_html( $afsac_tel ); ?></a></li>
							<?php endif; ?>
							<?php if ( '' !== $afsac_mail ) : ?>
								<li><a href="<?php echo esc_url( 'mailto:' . $afsac_mail ); ?>"><?php echo esc_html( $afsac_mail ); ?></a></li>
							<?php endif; ?>
						</ul>
						<a class="afsac-formation-help__cta" href="<?php echo esc_url( $afsac_contact_url ); ?>"><?php esc_html_e( 'Nous écrire', 'afsac' ); ?> ›</a>
					</div>

				</aside>

			</div>
		</div>

		<?php
		/*
		 * AUTRES COURS DU DOMAINE — la fiche se terminait en cul-de-sac : plus
		 * aucun lien vers le catalogue après le dernier bloc de contenu.
		 * Quatre cartes (grille 2×2 du catalogue OACI) + retour au domaine.
		 * `orderby => rand` sinon les mêmes voisins reviennent sur les 9 fiches
		 * d'un même domaine ; la requête ne porte que sur les IDs voulus.
		 */
		if ( $afsac_domain ) :
			$afsac_siblings = get_posts(
				array(
					'post_type'      => 'afsac_formation',
					'posts_per_page' => 4,
					'post__not_in'   => array( $afsac_id ),
					'orderby'        => 'rand',
					'no_found_rows'  => true,
					// Même jeu que la page du domaine : TRAINAIR PLUS des deux langues.
					'afsac_trainair_bilingue' => true,
					'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Archive d'un domaine, volume borné.
						array(
							'taxonomy' => 'afsac_area',
							'field'    => 'term_id',
							'terms'    => $afsac_domain->term_id,
						),
					),
				)
			);

			if ( ! empty( $afsac_siblings ) ) :
				$afsac_dom_link = get_term_link( $afsac_domain );
				?>
				<section class="afsac-section afsac-formation-related">
					<div class="afsac-container">
						<div class="afsac-formation-related__head afsac-reveal">
							<div>
								<span class="afsac-eyebrow"><?php esc_html_e( 'Poursuivre', 'afsac' ); ?></span>
								<h2 class="afsac-formation-related__title">
									<?php
									/* translators: %s: nom du domaine OACI (ex. « Aérodromes »). */
									printf( esc_html__( 'Autres cours du domaine %s', 'afsac' ), esc_html( $afsac_domain->name ) );
									?>
								</h2>
							</div>
							<?php if ( ! is_wp_error( $afsac_dom_link ) ) : ?>
								<a class="afsac-formation-related__all" href="<?php echo esc_url( $afsac_dom_link ); ?>"><?php esc_html_e( 'Voir tout le domaine', 'afsac' ); ?> ›</a>
							<?php endif; ?>
						</div>

						<div class="afsac-fgrid__grid afsac-formation-related__grid afsac-reveal afsac-reveal--fade">
							<?php
							foreach ( $afsac_siblings as $afsac_sib ) :
								get_template_part( 'template-parts/home/course-card-icao', null, afsac_build_course_card( $afsac_sib->ID ) );
							endforeach;
							?>
						</div>
					</div>
				</section>
				<?php
			endif;
		endif;
		?>

	</article>

	<?php
endwhile;

get_footer();
