<?php
/**
 * Template Name: Références & Témoignages
 *
 * Page « Références & Témoignages ». Refonte 08/2026 (demande client) : la page
 * ne conserve QUE TROIS sections, dans cet ordre —
 *   1. Nos partenaires  → bandeau de logos défilant, IDENTIQUE à l'accueil
 *                         (part partagé template-parts/shared/partners-marquee).
 *   2. Témoignages      → cartes vidéo (CPT afsac_temoignage), lecture en modale.
 *   3. Nos références   → un bandeau défilant par niveau de coopération
 *                         (CPT afsac_reference) ; les fiches sans logo sont
 *                         écartées.
 *
 * Ont été SUPPRIMÉES : carte d'intro, bandeau de statistiques, empreinte
 * régionale, registre filtrable (tableau + recherche), études de cas et bandeau
 * CTA « Devenir partenaire » — ainsi que leur CSS et refs-directory.js.
 *
 * Les deux CPT sont vides tant que le client n'a pas livré ses contenus : chaque
 * section retombe alors sur un jeu de DÉMONSTRATION (voir les tableaux $..._demo).
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Fil d'Ariane Rank Math, au-dessus du hero (chrome de page identique à la page
// « Qui sommes-nous »). RM émet son propre <nav aria-label>, on l'enveloppe d'un
// <div> (pas d'un second landmark). Rien d'émis si RM est désactivé.
if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
	ob_start();
	rank_math_the_breadcrumbs();
	$afsac_crumbs = trim( ob_get_clean() );
	if ( '' !== $afsac_crumbs ) {
		echo '<div class="afsac-breadcrumb"><div class="afsac-container">' . $afsac_crumbs . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sortie Rank Math déjà assainie (wp_kses_post).
	}
}

// HERO — hero clair partagé (charte OACI blanc + bleu), comme les autres pages internes.
$afsac_hero_eyebrow = function_exists( 'get_field' ) ? (string) get_field( 'afsac_page_eyebrow' ) : '';
if ( '' === $afsac_hero_eyebrow ) {
	$afsac_hero_eyebrow = __( 'Références · Témoignages', 'afsac' );
}
$afsac_hero_chapo = function_exists( 'get_field' ) ? (string) get_field( 'afsac_page_chapo' ) : '';
if ( '' === $afsac_hero_chapo ) {
	$afsac_hero_chapo = __( 'Partenaires institutionnels, paroles de participants et institutions qui se forment avec l’AFSAC.', 'afsac' );
}
get_template_part(
	'template-parts/shared/guide-hero',
	null,
	array(
		'eyebrow' => $afsac_hero_eyebrow,
		'title'   => __( 'Les autorités, aéroports et compagnies qui se forment avec l’AFSAC', 'afsac' ),
		'chapo'   => $afsac_hero_chapo,
	)
);

/**
 * Lit un champ ACF avec repli sur la méta brute (ACF peut être absent/inactif).
 *
 * @param int    $post_id ID du contenu.
 * @param string $key     Nom du champ (identique au nom de la méta).
 * @return string Valeur nettoyée.
 */
$afsac_field = static function ( $post_id, $key ) {
	$value = function_exists( 'get_field' ) ? get_field( $key, $post_id ) : get_post_meta( $post_id, $key, true );
	return is_scalar( $value ) ? trim( (string) $value ) : '';
};

/**
 * Monogramme (1 à 2 initiales) construit depuis un intitulé.
 *
 * @param string $label Nom de la personne ou de l'institution.
 * @return string Initiales en majuscules.
 */
$afsac_initials = static function ( $label ) {
	$words    = preg_split( '/[\s·\-]+/u', wp_strip_all_tags( $label ), -1, PREG_SPLIT_NO_EMPTY );
	$initials = '';
	foreach ( (array) $words as $word ) {
		// Ignore les civilités, qui ne portent aucune information.
		if ( in_array( mb_strtolower( rtrim( $word, '.' ) ), array( 'm', 'mme', 'mr', 'mrs', 'ms', 'dr', 'pr' ), true ) ) {
			continue;
		}
		$initials .= mb_substr( $word, 0, 1 );
		if ( mb_strlen( $initials ) >= 2 ) {
			break;
		}
	}
	return mb_strtoupper( '' !== $initials ? $initials : mb_substr( wp_strip_all_tags( $label ), 0, 1 ) );
};
?>

<main id="primary" class="afsac-refs">

	<?php
	/* 1. NOS PARTENAIRES — bandeau de logos, strictement le même qu'en accueil. */
	get_template_part(
		'template-parts/shared/partners-marquee',
		null,
		array(
			'eyebrow' => __( 'Ils nous font confiance', 'afsac' ),
			'title'   => __( 'Nos partenaires', 'afsac' ),
			'lead'    => __( 'Organisations internationales, autorités de l’aviation civile et opérateurs avec lesquels l’AFSAC conduit ses programmes de formation.', 'afsac' ),
			'id'      => 'partenaires',
			'class'   => 'afsac-partenaires--page',
			// Deux lignes à sens opposés (demande client du 11/08/2026).
			'rows'    => 2,
		)
	);
	?>

	<?php
	/*
	 * 2. TÉMOIGNAGES — cartes vidéo (demande client : la vidéo prime sur le texte).
	 * Source : CPT afsac_temoignage + champs ACF (auteur / fonction / organisation /
	 * citation / langue / durée / URL vidéo). Le bouton de lecture n'apparaît que
	 * si une URL vidéo est renseignée.
	 *
	 * DEUX registres de texte, volontairement distincts :
	 *   - `citation` (ACF) = propos VERBATIM → rendu en <blockquote> avec guillemets ;
	 *   - contenu de l'éditeur = simple description de la session filmée → rendu en
	 *     paragraphe neutre. Ne JAMAIS présenter une description entre guillemets :
	 *     ce serait attribuer à la personne des mots qu'elle n'a pas prononcés.
	 */
	$afsac_voices = array();

	if ( post_type_exists( 'afsac_temoignage' ) ) {
		$afsac_voices_query = new WP_Query(
			array(
				'post_type'              => 'afsac_temoignage',
				'post_status'            => 'publish',
				'posts_per_page'         => 24,
				'orderby'                => 'menu_order date',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
			)
		);

		while ( $afsac_voices_query->have_posts() ) {
			$afsac_voices_query->the_post();
			$afsac_vid = get_the_ID();

			$afsac_quote = $afsac_field( $afsac_vid, 'afsac_temoignage_citation' );
			$afsac_desc  = '' === $afsac_quote ? trim( wp_strip_all_tags( get_the_content() ) ) : '';

			$afsac_author = $afsac_field( $afsac_vid, 'afsac_temoignage_auteur' );
			if ( '' === $afsac_author ) {
				$afsac_author = get_the_title();
			}

			$afsac_voices[] = array(
				'quote'    => $afsac_quote,
				'desc'     => $afsac_desc,
				'author'   => $afsac_author,
				'role'     => $afsac_field( $afsac_vid, 'afsac_temoignage_fonction' ),
				'org'      => $afsac_field( $afsac_vid, 'afsac_temoignage_org' ),
				'lang'     => $afsac_field( $afsac_vid, 'afsac_temoignage_langue' ),
				'duration' => $afsac_field( $afsac_vid, 'afsac_temoignage_duree' ),
				'video'    => $afsac_field( $afsac_vid, 'afsac_temoignage_video_url' ),
				'poster'   => (string) get_the_post_thumbnail_url( $afsac_vid, 'large' ),
			);
		}
		wp_reset_postdata();
	}

	if ( empty( $afsac_voices ) ) {
		// DÉMO — remplacé dès que le client publie ses témoignages (Témoignages → Ajouter).
		$afsac_voices = array(
			array(
				'quote'    => __( 'Une formation de très haute qualité. L’expertise des formateurs AFSAC et leur connaissance approfondie des standards OACI ont permis à notre équipe d’atteindre un niveau opérationnel exemplaire.', 'afsac' ),
				'desc'     => '',
				'author'   => 'Mme. Aïcha Diallo',
				'role'     => __( 'Directrice Sûreté', 'afsac' ),
				'org'      => 'ANAC Sénégal',
				'lang'     => 'FR',
				'duration' => '',
				'video'    => '',
				'poster'   => '',
			),
			array(
				'quote'    => __( 'La rigueur méthodologique de l’AFSAC a transformé notre approche de la sûreté aéroportuaire : nos équipes appliquent les standards OACI avec une confiance nouvelle.', 'afsac' ),
				'desc'     => '',
				'author'   => 'Mr. K. Opondo',
				'role'     => __( 'Responsable Sûreté aéroportuaire', 'afsac' ),
				'org'      => 'Nairobi Airport',
				'lang'     => 'EN',
				'duration' => '',
				'video'    => '',
				'poster'   => '',
			),
			array(
				'quote'    => __( 'La collaboration avec l’AFSAC a élevé le niveau de notre académie : des supports de cours remarquables, transférés à nos formateurs.', 'afsac' ),
				'desc'     => '',
				'author'   => 'Mme. S. El Idrissi',
				'role'     => __( 'Directrice pédagogique', 'afsac' ),
				'org'      => 'Royal Air Maroc Academy',
				'lang'     => 'FR',
				'duration' => '',
				'video'    => '',
				'poster'   => '',
			),
		);
	}

	/*
	 * Mise en scène façon « playlist » (demande client) : à GAUCHE le lecteur de
	 * la vidéo sélectionnée, à DROITE la liste des témoignages. Le premier
	 * élément est actif au chargement ; assets/js/afsac-testimonials.js recopie
	 * les data-* de l'élément cliqué dans la scène et injecte le lecteur.
	 * Sans JS, chaque entrée de la liste reste un lien vers la vidéo (progressive
	 * enhancement) : la section n'est jamais un cul-de-sac.
	 */
	$afsac_current = $afsac_voices[0];
	$afsac_cur_org = '' !== $afsac_current['org'] ? $afsac_current['org'] : $afsac_current['author'];
	$afsac_total   = count( $afsac_voices );
	?>
	<section class="afsac-voices" id="temoignages">
		<div class="afsac-container">
			<?php
			/*
			 * En-tête HORS de la grille : les deux colonnes démarrent ainsi à la
			 * même hauteur (demande client « qu'ils soient alignés »).
			 */
			get_template_part(
				'template-parts/shared/section-header',
				null,
				array(
					'eyebrow' => __( 'Paroles de participants', 'afsac' ),
					'title'   => __( 'Témoignages', 'afsac' ),
					'reveal'  => true,
				)
			);
			?>
			<p class="afsac-voices__intro afsac-reveal">
				<?php
				printf(
					/* translators: %d : nombre de témoignages. */
					esc_html( _n( '%d retour d’expérience filmé sur le campus de Tunis, publié avec l’accord du participant.', '%d retours d’expérience filmés sur le campus de Tunis, publiés avec l’accord des participants.', $afsac_total, 'afsac' ) ),
					(int) $afsac_total
				);
				?>
			</p>

			<div class="afsac-voices__grid afsac-reveal">

				<?php /* Colonne gauche : scène de lecture. */ ?>
				<div class="afsac-voices__stage">
					<div class="afsac-voices__player" data-voice-player>
						<?php /* Affiche + bouton de lecture, remplacés par l'iframe au clic. */ ?>
						<div class="afsac-voices__poster" data-voice-poster>
							<?php if ( '' !== $afsac_current['poster'] ) : ?>
								<img src="<?php echo esc_url( $afsac_current['poster'] ); ?>" alt="" data-voice-img decoding="async" />
							<?php else : ?>
								<span class="afsac-voices__monogram" data-voice-monogram aria-hidden="true"><?php echo esc_html( $afsac_initials( $afsac_cur_org ) ); ?></span>
							<?php endif; ?>

							<?php if ( '' !== $afsac_current['video'] ) : ?>
								<a class="afsac-voices__play" href="<?php echo esc_url( $afsac_current['video'] ); ?>" data-voice-play>
									<span class="afsac-voices__play-icon" aria-hidden="true">
										<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M8 5.5v13l11-6.5z"/></svg>
									</span>
									<span class="screen-reader-text" data-voice-playlabel><?php
										/* translators: %s : nom de la personne filmée. */
										echo esc_html( sprintf( __( 'Lire le témoignage vidéo de %s', 'afsac' ), $afsac_current['author'] ) );
									?></span>
								</a>
							<?php endif; ?>

							<span class="afsac-voices__tags" data-voice-tags>
								<?php if ( '' !== $afsac_current['lang'] ) : ?>
									<span class="afsac-voices__tag" data-voice-lang><?php echo esc_html( $afsac_current['lang'] ); ?></span>
								<?php endif; ?>
								<?php if ( '' !== $afsac_current['duration'] ) : ?>
									<span class="afsac-voices__tag afsac-voices__tag--time" data-voice-duration><?php echo esc_html( $afsac_current['duration'] ); ?></span>
								<?php endif; ?>
							</span>
						</div>
					</div>

					<?php /* Fiche du témoignage affiché. */ ?>
					<div class="afsac-voices__now">
						<span class="afsac-voices__chip" data-voice-org<?php echo '' === $afsac_current['org'] ? ' hidden' : ''; ?>><?php echo esc_html( $afsac_current['org'] ); ?></span>

						<?php if ( '' !== $afsac_current['quote'] ) : ?>
							<blockquote class="afsac-voices__quote" data-voice-quote><?php echo esc_html( $afsac_current['quote'] ); ?></blockquote>
							<p class="afsac-voices__desc-text" data-voice-desc hidden></p>
						<?php else : ?>
							<blockquote class="afsac-voices__quote" data-voice-quote hidden></blockquote>
							<p class="afsac-voices__desc-text" data-voice-desc><?php echo esc_html( $afsac_current['desc'] ); ?></p>
						<?php endif; ?>

						<div class="afsac-voices__author">
							<span class="afsac-voices__avatar" data-voice-avatar aria-hidden="true"><?php echo esc_html( $afsac_initials( $afsac_current['author'] ) ); ?></span>
							<span class="afsac-voices__author-text">
								<span class="afsac-voices__author-name" data-voice-name><?php echo esc_html( $afsac_current['author'] ); ?></span>
								<span class="afsac-voices__author-role" data-voice-role><?php echo esc_html( $afsac_current['role'] ); ?></span>
							</span>
						</div>
					</div>
				</div>

				<?php /* Colonne droite : la playlist. */ ?>
				<div class="afsac-voices__side">
					<ul class="afsac-voices__list" data-voice-list>
						<?php foreach ( $afsac_voices as $afsac_i => $afsac_voice ) : ?>
							<?php $afsac_v_org = '' !== $afsac_voice['org'] ? $afsac_voice['org'] : $afsac_voice['author']; ?>
							<li>
								<a class="afsac-voices__item<?php echo 0 === $afsac_i ? ' is-active' : ''; ?>"
									href="<?php echo esc_url( '' !== $afsac_voice['video'] ? $afsac_voice['video'] : '#temoignages' ); ?>"
									<?php echo 0 === $afsac_i ? ' aria-current="true"' : ''; ?>
									data-video="<?php echo esc_url( $afsac_voice['video'] ); ?>"
									data-poster="<?php echo esc_url( $afsac_voice['poster'] ); ?>"
									data-initials="<?php echo esc_attr( $afsac_initials( $afsac_voice['author'] ) ); ?>"
									data-monogram="<?php echo esc_attr( $afsac_initials( $afsac_v_org ) ); ?>"
									data-name="<?php echo esc_attr( $afsac_voice['author'] ); ?>"
									data-role="<?php echo esc_attr( $afsac_voice['role'] ); ?>"
									data-org="<?php echo esc_attr( $afsac_voice['org'] ); ?>"
									data-lang="<?php echo esc_attr( $afsac_voice['lang'] ); ?>"
									data-duration="<?php echo esc_attr( $afsac_voice['duration'] ); ?>"
									data-quote="<?php echo esc_attr( $afsac_voice['quote'] ); ?>"
									data-desc="<?php echo esc_attr( $afsac_voice['desc'] ); ?>">
									<?php /* Vignette ronde de la vidéo (demande client), à la place du numéro. */ ?>
									<span class="afsac-voices__thumb">
										<?php if ( '' !== $afsac_voice['poster'] ) : ?>
											<img src="<?php echo esc_url( $afsac_voice['poster'] ); ?>" alt="" loading="lazy" decoding="async" />
										<?php else : ?>
											<span class="afsac-voices__thumb-fallback" aria-hidden="true"><?php echo esc_html( $afsac_initials( $afsac_voice['author'] ) ); ?></span>
										<?php endif; ?>
									</span>
									<span class="afsac-voices__item-text">
										<span class="afsac-voices__item-name"><?php echo esc_html( $afsac_voice['author'] ); ?></span>
										<?php
										/*
										 * « Fonction · Organisation », en n'assemblant que ce qui existe :
										 * sans ce filtre, une fiche sans organisation répéterait le nom de
										 * la personne (le repli monogramme, lui, l'utilise à dessein).
										 */
										$afsac_item_meta = array_filter( array( $afsac_voice['role'], $afsac_voice['org'] ) );
										if ( empty( $afsac_item_meta ) ) {
											$afsac_item_meta = array( $afsac_voice['author'] );
										}
										?>
										<span class="afsac-voices__item-role"><?php echo esc_html( implode( ' · ', $afsac_item_meta ) ); ?></span>
									</span>
									<span class="afsac-voices__item-aside">
										<?php if ( '' !== $afsac_voice['lang'] ) : ?>
											<span class="afsac-voices__item-lang"><?php echo esc_html( $afsac_voice['lang'] ); ?></span>
										<?php endif; ?>
										<?php if ( '' !== $afsac_voice['duration'] ) : ?>
											<span class="afsac-voices__item-dur"><?php echo esc_html( $afsac_voice['duration'] ); ?></span>
										<?php endif; ?>
									</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

			</div>
		</div>
	</section>

	<?php
	/*
	 * 3. NOS RÉFÉRENCES — un BANDEAU DÉFILANT par niveau de coopération
	 * (national / régional / international), comme la brochure « Nos Références »
	 * du client. Source : CPT afsac_reference, logo = image à la une.
	 *
	 * Refonte 11/08/2026 (demande client) : les trois grilles empilées imposaient
	 * une page interminable. Chaque niveau tient désormais sur UNE ligne qui
	 * défile en boucle (CSS pur, cf. .afsac-refs-marquee), sens alterné d'une
	 * ligne à l'autre. En prefers-reduced-motion la ligne redevient une grille.
	 *
	 * Une fiche SANS LOGO n'est plus affichée du tout (demande client) : un mur
	 * de logos ne se remplit pas avec des monogrammes. La fiche reste publiée en
	 * base — il suffit de lui poser une image à la une pour la voir apparaître.
	 */
	$afsac_levels = function_exists( 'afsac_get_reference_levels' ) ? afsac_get_reference_levels() : array();
	$afsac_groups = array();

	if ( post_type_exists( 'afsac_reference' ) ) {
		$afsac_refs_query = new WP_Query(
			array(
				'post_type'              => 'afsac_reference',
				'post_status'            => 'publish',
				'posts_per_page'         => 200,
				'orderby'                => 'menu_order title',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
			)
		);

		while ( $afsac_refs_query->have_posts() ) {
			$afsac_refs_query->the_post();
			$afsac_rid = get_the_ID();

			$afsac_logo = (string) get_the_post_thumbnail_url( $afsac_rid, 'medium' );
			if ( '' === $afsac_logo ) {
				continue; // Pas de logo : la fiche n'entre pas dans le mur.
			}

			$afsac_type = $afsac_field( $afsac_rid, 'afsac_reference_type' );
			if ( '' !== $afsac_type && function_exists( 'afsac_get_reference_type_label' ) ) {
				$afsac_type = afsac_get_reference_type_label( $afsac_type );
			}

			// Les fiches sans niveau forment un dernier groupe sans intertitre.
			$afsac_niveau = $afsac_field( $afsac_rid, 'afsac_reference_niveau' );
			if ( ! isset( $afsac_levels[ $afsac_niveau ] ) ) {
				$afsac_niveau = '';
			}

			$afsac_groups[ $afsac_niveau ][] = array(
				'name'    => get_the_title(),
				'country' => $afsac_field( $afsac_rid, 'afsac_reference_pays' ),
				'type'    => $afsac_type,
				'logo'    => $afsac_logo,
			);
		}
		wp_reset_postdata();
	}

	if ( empty( $afsac_groups ) ) {
		/*
		 * DÉMO — remplacé dès que le client publie ses références. Elle emprunte
		 * les logos institutionnels du thème : sans logo, une carte ne serait pas
		 * affichée (règle ci-dessus) et la section resterait vide.
		 */
		$afsac_demo_logo = static function ( $slug ) {
			foreach ( array( 'svg', 'png', 'webp', 'jpg' ) as $ext ) {
				if ( file_exists( get_theme_file_path( "assets/images/partners/{$slug}.{$ext}" ) ) ) {
					return get_theme_file_uri( "assets/images/partners/{$slug}.{$ext}" );
				}
			}
			return '';
		};
		$afsac_groups[''] = array_values(
			array_filter(
				array(
					array( 'name' => 'DGAC Tunisie', 'country' => __( 'Tunisie', 'afsac' ), 'type' => '', 'logo' => $afsac_demo_logo( 'dgac-tunisie' ) ),
					array( 'name' => 'ASECNA', 'country' => __( 'Multilatéral', 'afsac' ), 'type' => '', 'logo' => $afsac_demo_logo( 'asecna' ) ),
					array( 'name' => 'AFCAC', 'country' => __( 'Multilatéral', 'afsac' ), 'type' => '', 'logo' => $afsac_demo_logo( 'afcac' ) ),
				),
				static function ( $item ) {
					return '' !== $item['logo'];
				}
			)
		);
	}

	// Ordre d'affichage : celui du vocabulaire, puis le groupe sans niveau.
	$afsac_order = array_keys( $afsac_levels );
	$afsac_order[] = '';

	$afsac_total_refs = 0;
	foreach ( $afsac_groups as $afsac_g ) {
		$afsac_total_refs += count( $afsac_g );
	}

	/**
	 * Rendu d'une piste de cartes références (réutilisé pour le duplicata).
	 *
	 * Le duplicata est indispensable à la boucle sans saut : la piste translate
	 * d'exactement une largeur de groupe, le second exemplaire prend la place du
	 * premier. Il est masqué aux technologies d'assistance (contenu redondant).
	 *
	 * @param array $items     Références du niveau.
	 * @param bool  $duplicate Vrai pour le second exemplaire.
	 */
	$afsac_render_refs_row = static function ( $items, $duplicate = false ) {
		printf( '<ul class="afsac-refs-list__row%s"%s>', $duplicate ? ' afsac-refs-list__row--dup' : '', $duplicate ? ' aria-hidden="true"' : '' );
		foreach ( $items as $ref ) :
			$meta = array_filter( array( $ref['country'], $ref['type'] ) );
			?>
			<li class="afsac-ref-card"<?php echo ! empty( $meta ) ? ' title="' . esc_attr( implode( ' · ', $meta ) ) . '"' : ''; ?>>
				<span class="afsac-ref-card__mark">
					<img class="afsac-ref-card__logo" src="<?php echo esc_url( $ref['logo'] ); ?>" alt="<?php echo $duplicate ? '' : esc_attr( $ref['name'] ); ?>" loading="lazy" decoding="async" />
				</span>
				<span class="afsac-ref-card__name"><?php echo esc_html( $ref['name'] ); ?></span>
				<?php if ( '' !== $ref['country'] ) : ?>
					<span class="afsac-ref-card__country"><?php echo esc_html( $ref['country'] ); ?></span>
				<?php endif; ?>
			</li>
			<?php
		endforeach;
		echo '</ul>';
	};

	$afsac_row_index = 0;
	?>
	<section class="afsac-refs-list" id="references">
		<div class="afsac-container">
			<?php
			get_template_part(
				'template-parts/shared/section-header',
				null,
				array(
					'eyebrow' => __( 'Institutions accompagnées', 'afsac' ),
					'title'   => __( 'Nos références', 'afsac' ),
					'align'   => 'center',
					'reveal'  => true,
				)
			);
			?>
			<p class="afsac-refs-list__intro afsac-reveal">
				<?php
				printf(
					/* translators: %d : nombre d'institutions référencées. */
					esc_html( _n( '%d institution nous fait confiance, en Tunisie et à l’international.', '%d institutions nous font confiance, en Tunisie et à l’international.', $afsac_total_refs, 'afsac' ) ),
					(int) $afsac_total_refs
				);
				?>
			</p>

			<?php foreach ( $afsac_order as $afsac_key ) : ?>
				<?php
				if ( empty( $afsac_groups[ $afsac_key ] ) ) {
					continue;
				}
				$afsac_count = count( $afsac_groups[ $afsac_key ] );
				/*
				 * Durée proportionnelle au nombre de cartes : toutes les lignes
				 * défilent à la MÊME vitesse apparente (~3,6 s par carte), sinon
				 * la ligne « international » (13 logos) filerait trois fois plus
				 * vite que la ligne « national ». Plancher à 24 s pour qu'un
				 * niveau très court ne tourne pas comme un manège.
				 */
				$afsac_duration = max( 24, (int) round( $afsac_count * 3.6 ) );
				++$afsac_row_index;
				?>
				<div class="afsac-refs-list__group">
					<?php if ( '' !== $afsac_key ) : ?>
						<h3 class="afsac-refs-list__group-title afsac-reveal">
							<span><?php echo esc_html( $afsac_levels[ $afsac_key ] ); ?></span>
							<span class="afsac-refs-list__group-count"><?php echo esc_html( number_format_i18n( $afsac_count ) ); ?></span>
						</h3>
					<?php endif; ?>
					<?php /* Sens alterné : une ligne sur deux défile vers la droite. */ ?>
					<div class="afsac-refs-marquee afsac-reveal afsac-reveal--fade<?php echo 0 === $afsac_row_index % 2 ? ' afsac-refs-marquee--reverse' : ''; ?>" style="--afsac-refs-duration: <?php echo esc_attr( $afsac_duration ); ?>s">
						<div class="afsac-refs-marquee__track">
							<?php
							$afsac_render_refs_row( $afsac_groups[ $afsac_key ], false );
							$afsac_render_refs_row( $afsac_groups[ $afsac_key ], true ); // duplicata : boucle sans saut.
							?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

</main>

<?php
get_footer();
