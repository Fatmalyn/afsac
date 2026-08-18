<?php
/**
 * Template Name: Actualités
 *
 * Index des actualités : articles WP NATIFS (aucun CPT) filtrés par langue
 * courante (Polylang). Bandeau partagé (.afsac-guide-hero) — le client fournira
 * sa propre bannière, la structure ne change donc pas ici — puis une LISTE
 * « image + texte » (demande client 08/2026, en remplacement de la grille de
 * cartes), chaque entrée menant à sa page « Savoir plus » (single.php).
 *
 * Une barre de filtres par catégorie précède la liste : ce sont de vrais liens
 * (?categorie=slug), donc le filtrage fonctionne SANS JavaScript et reste
 * compatible avec la pagination serveur. SEO délégué à Rank Math.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Pagination : sur une Page, la variable est « page » ; en archive paginée, « paged ».
$afsac_paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$afsac_per   = 8;

// Filtre de catégorie (lien simple, pas un formulaire : pas de nonce à vérifier).
$afsac_filter = isset( $_GET['categorie'] ) ? sanitize_title( wp_unslash( $_GET['categorie'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// Catégories réellement peuplées, dans la langue courante (Polylang filtre seul).
$afsac_cats = get_categories(
	array(
		'hide_empty' => true,
		'orderby'    => 'name',
	)
);
$afsac_cats = is_wp_error( $afsac_cats ) ? array() : $afsac_cats;

// Un filtre inconnu (lien périmé) est ignoré plutôt que de renvoyer une page vide.
$afsac_slugs = wp_list_pluck( $afsac_cats, 'slug' );
if ( '' !== $afsac_filter && ! in_array( $afsac_filter, $afsac_slugs, true ) ) {
	$afsac_filter = '';
}

$afsac_args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => $afsac_per,
	'paged'          => $afsac_paged,
);
if ( '' !== $afsac_filter ) {
	$afsac_args['category_name'] = $afsac_filter;
}
$afsac_list = new WP_Query( $afsac_args );

$afsac_total = (int) $afsac_list->found_posts;
$afsac_index = afsac_news_index_url();
?>

<main id="primary" class="afsac-news">

	<?php
	$afsac_eyebrow = function_exists( 'get_field' ) ? (string) get_field( 'afsac_page_eyebrow' ) : '';
	if ( '' === $afsac_eyebrow ) {
		$afsac_eyebrow = __( 'Le fil de l’AFSAC', 'afsac' );
	}
	$afsac_chapo = function_exists( 'get_field' ) ? (string) get_field( 'afsac_page_chapo' ) : '';
	if ( '' === $afsac_chapo ) {
		$afsac_chapo = __( 'Décryptages, actualités réglementaires et temps forts de la formation aéronautique civile.', 'afsac' );
	}
	// Bandeau VIDÉO fourni par le client (17/08/2026) — c'est la « propre
	// bannière » annoncée en tête de fichier. Voile renforcé : le plan de
	// tarmac est très détaillé, il doit rester en fond.
	get_template_part(
		'template-parts/shared/video-hero',
		null,
		array(
			'layout'    => 'overlay',
			'align'     => 'start',
			'dim'       => true,
			'video_src' => get_theme_file_uri( 'assets/video/actualites.mp4' ),
			'poster'    => get_theme_file_uri( 'assets/images/actualites-hero-poster.jpg' ),
			'eyebrow'   => $afsac_eyebrow,
			'title'     => get_the_title( get_queried_object_id() ),
			'lead'      => $afsac_chapo,
		)
	);
	?>

	<?php if ( $afsac_total || '' !== $afsac_filter ) : ?>
		<div class="afsac-news-bar">
			<div class="afsac-container afsac-news-bar__inner">

				<p class="afsac-news-bar__count">
					<?php echo afsac_count_markup( (string) $afsac_total ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Balisage déjà échappé par le helper. ?>
					<span><?php echo esc_html( _n( 'actualité publiée', 'actualités publiées', $afsac_total, 'afsac' ) ); ?></span>
				</p>

				<?php if ( count( $afsac_cats ) > 1 ) : ?>
					<nav class="afsac-news-filters" aria-label="<?php esc_attr_e( 'Filtrer les actualités par catégorie', 'afsac' ); ?>">
						<a
							class="afsac-news-filters__chip<?php echo ( '' === $afsac_filter ) ? ' is-active' : ''; ?>"
							href="<?php echo esc_url( $afsac_index ); ?>"
							<?php echo ( '' === $afsac_filter ) ? ' aria-current="page"' : ''; ?>
						><?php esc_html_e( 'Toutes', 'afsac' ); ?></a>
						<?php foreach ( $afsac_cats as $afsac_term ) : ?>
							<?php $afsac_on = ( $afsac_term->slug === $afsac_filter ); ?>
							<a
								class="afsac-news-filters__chip<?php echo $afsac_on ? ' is-active' : ''; ?>"
								href="<?php echo esc_url( add_query_arg( 'categorie', $afsac_term->slug, $afsac_index ) ); ?>"
								<?php echo $afsac_on ? ' aria-current="page"' : ''; ?>
							>
								<?php echo esc_html( $afsac_term->name ); ?>
								<span class="afsac-news-filters__n"><?php echo esc_html( (string) $afsac_term->count ); ?></span>
							</a>
						<?php endforeach; ?>
					</nav>
				<?php endif; ?>

			</div>
		</div>
	<?php endif; ?>

	<section class="afsac-news-list">
		<div class="afsac-container">

			<?php if ( $afsac_list->have_posts() ) : ?>

				<div class="afsac-news-rows">
					<?php
					$afsac_i = 0;
					while ( $afsac_list->have_posts() ) :
						$afsac_list->the_post();
						get_template_part(
							'template-parts/news-row',
							null,
							array(
								'index' => $afsac_i,
								// Rangée « à la une » : la plus récente, page 1 seulement.
								'lead'  => ( 0 === $afsac_i && 1 === $afsac_paged ),
							)
						);
						++$afsac_i;
					endwhile;
					?>
				</div>

				<?php
				$afsac_links = paginate_links(
					array(
						'total'     => (int) $afsac_list->max_num_pages,
						'current'   => $afsac_paged,
						'base'      => trailingslashit( get_permalink() ) . 'page/%#%/',
						'format'    => '',
						'add_args'  => ( '' !== $afsac_filter ) ? array( 'categorie' => $afsac_filter ) : array(),
						'type'      => 'array',
						'mid_size'  => 2,
						'prev_text' => esc_html__( 'Précédent', 'afsac' ),
						'next_text' => esc_html__( 'Suivant', 'afsac' ),
					)
				);
				if ( ! empty( $afsac_links ) ) :
					?>
					<nav class="afsac-news-pager" aria-label="<?php esc_attr_e( 'Pagination des actualités', 'afsac' ); ?>">
						<?php foreach ( $afsac_links as $afsac_link ) : ?>
							<?php echo wp_kses_post( str_replace( 'page-numbers', 'afsac-news-pager__btn', $afsac_link ) ); ?>
						<?php endforeach; ?>
					</nav>
				<?php endif; ?>

			<?php elseif ( '' !== $afsac_filter ) : ?>

				<div class="afsac-area__empty">
					<h2 class="afsac-area__empty-title"><?php esc_html_e( 'Aucune actualité dans cette catégorie', 'afsac' ); ?></h2>
					<p class="afsac-area__empty-text"><?php esc_html_e( 'Retirez le filtre pour retrouver l’ensemble du fil d’actualité.', 'afsac' ); ?></p>
					<a class="afsac-button" href="<?php echo esc_url( $afsac_index ); ?>"><?php esc_html_e( 'Voir toutes les actualités', 'afsac' ); ?></a>
				</div>

			<?php else : ?>

				<div class="afsac-area__empty">
					<h2 class="afsac-area__empty-title"><?php esc_html_e( 'Aucune actualité pour le moment', 'afsac' ); ?></h2>
					<p class="afsac-area__empty-text"><?php esc_html_e( 'Revenez bientôt : nous publions régulièrement des actualités sur l’aviation civile et nos formations.', 'afsac' ); ?></p>
					<a class="afsac-button" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Nous contacter', 'afsac' ); ?></a>
				</div>

			<?php endif; ?>

		</div>
	</section>

</main>

<?php
wp_reset_postdata();
get_footer();
