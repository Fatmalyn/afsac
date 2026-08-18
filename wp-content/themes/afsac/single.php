<?php
/**
 * Page « Savoir plus » d'une actualité — articles WP natifs, à la charte AFSAC.
 *
 * Demande client (08/2026) : chaque entrée de la LISTE d'actualités
 * (template-actualites.php + template-parts/news-row.php) mène à sa propre page
 * de lecture. D'où ce gabarit : jauge de lecture, bandeau illustré plein cadre,
 * rail de partage collant, navigation article précédent / suivant, puis les
 * articles liés.
 *
 * Le H1, le <title> et les métas SEO sont laissés à Rank Math — non
 * court-circuités. Le fil d'Ariane Rank Math est simplement remonté DANS le
 * bandeau (fond bleu), au lieu de la bande blanche qui le précédait.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$afsac_news_url = afsac_news_index_url();

while ( have_posts() ) :
	the_post();

	$afsac_cats  = get_the_category();
	$afsac_cat   = ( ! empty( $afsac_cats ) && ! is_wp_error( $afsac_cats ) ) ? $afsac_cats[0] : null;
	$afsac_media = afsac_news_thumb_url( get_the_ID(), 'full' );
	$afsac_read  = afsac_reading_time( get_the_ID() );
	$afsac_perma = get_permalink();
	$afsac_title = get_the_title();

	// Réseaux de partage : URL d'intention publiques, sans script tiers ni traceur.
	$afsac_share = array(
		array(
			'key'   => 'linkedin',
			'icon'  => 'linkedin',
			'label' => __( 'Partager sur LinkedIn', 'afsac' ),
			'url'   => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $afsac_perma ),
		),
		array(
			'key'   => 'x',
			'icon'  => 'twitter',
			'label' => __( 'Partager sur X', 'afsac' ),
			'url'   => 'https://twitter.com/intent/tweet?url=' . rawurlencode( $afsac_perma ) . '&text=' . rawurlencode( $afsac_title ),
		),
		array(
			'key'   => 'facebook',
			'icon'  => 'facebook',
			'label' => __( 'Partager sur Facebook', 'afsac' ),
			'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $afsac_perma ),
		),
	);
	?>

	<main id="primary" class="afsac-single afsac-article">

		<?php /* Jauge de lecture : peinte par assets/js/afsac-news.js (no-op sans JS). */ ?>
		<div class="afsac-progress" aria-hidden="true"><span class="afsac-progress__bar"></span></div>

		<article <?php post_class( 'afsac-single__article' ); ?>>

			<header class="afsac-ahero<?php echo ( '' !== $afsac_media ) ? ' afsac-ahero--media' : ''; ?>">
				<?php if ( '' !== $afsac_media ) : ?>
					<span class="afsac-ahero__media" aria-hidden="true">
						<img src="<?php echo esc_url( $afsac_media ); ?>" alt="" loading="eager" decoding="async" fetchpriority="high">
					</span>
				<?php endif; ?>
				<span class="afsac-ahero__veil" aria-hidden="true"></span>

				<div class="afsac-container afsac-ahero__inner">
					<?php
					if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
						ob_start();
						rank_math_the_breadcrumbs();
						$afsac_crumbs = trim( ob_get_clean() );
						if ( '' !== $afsac_crumbs ) {
							echo '<div class="afsac-ahero__crumbs">' . $afsac_crumbs . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sortie Rank Math déjà assainie.
						}
					}
					?>

					<?php if ( $afsac_cat ) : ?>
						<a class="afsac-ahero__cat" href="<?php echo esc_url( add_query_arg( 'categorie', $afsac_cat->slug, $afsac_news_url ) ); ?>"><?php echo esc_html( $afsac_cat->name ); ?></a>
					<?php endif; ?>

					<h1 class="afsac-ahero__title"><?php the_title(); ?></h1>

					<ul class="afsac-ahero__meta">
						<li>
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						</li>
						<li>
							<?php
							printf(
								/* translators: %d: durée de lecture en minutes. */
								esc_html( _n( '%d min de lecture', '%d min de lecture', $afsac_read, 'afsac' ) ),
								(int) $afsac_read
							);
							?>
						</li>
					</ul>
				</div>
			</header>

			<div class="afsac-container afsac-article__grid">

				<?php /* Rail de partage : collant sur grand écran, en ligne sinon. */ ?>
				<aside class="afsac-share" aria-label="<?php esc_attr_e( 'Partager cette actualité', 'afsac' ); ?>">
					<span class="afsac-share__label"><?php esc_html_e( 'Partager', 'afsac' ); ?></span>
					<ul class="afsac-share__list">
						<?php foreach ( $afsac_share as $afsac_net ) : ?>
							<li>
								<a class="afsac-share__btn" href="<?php echo esc_url( $afsac_net['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $afsac_net['label'] ); ?>">
									<?php echo afsac_icon( $afsac_net['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique du thème. ?>
								</a>
							</li>
						<?php endforeach; ?>
						<li>
							<button
								type="button"
								class="afsac-share__btn afsac-share__copy"
								data-url="<?php echo esc_url( $afsac_perma ); ?>"
								data-done="<?php esc_attr_e( 'Lien copié', 'afsac' ); ?>"
								aria-label="<?php esc_attr_e( 'Copier le lien de l’article', 'afsac' ); ?>"
							>
								<?php echo afsac_icon( 'link' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique du thème. ?>
								<?php echo afsac_icon( 'check', array( 'class' => 'afsac-share__ok' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique du thème. ?>
							</button>
						</li>
					</ul>
				</aside>

				<div class="afsac-article__main">

					<?php $afsac_lead = get_the_excerpt(); ?>
					<?php if ( '' !== trim( (string) $afsac_lead ) ) : ?>
						<p class="afsac-article__lead"><?php echo esc_html( $afsac_lead ); ?></p>
					<?php endif; ?>

					<div class="afsac-prose">
						<?php the_content(); ?>
					</div>

					<?php
					$afsac_tags = get_the_tags();
					if ( $afsac_tags && ! is_wp_error( $afsac_tags ) ) :
						?>
						<ul class="afsac-article__tags" aria-label="<?php esc_attr_e( 'Mots-clés', 'afsac' ); ?>">
							<?php foreach ( $afsac_tags as $afsac_tag ) : ?>
								<li><a href="<?php echo esc_url( get_tag_link( $afsac_tag ) ); ?>">#<?php echo esc_html( $afsac_tag->name ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<div class="afsac-article__back">
						<a class="afsac-button afsac-button--outline" href="<?php echo esc_url( $afsac_news_url ); ?>">
							<span class="afsac-article__back-arrow" aria-hidden="true">&larr;</span>
							<?php esc_html_e( 'Retour aux actualités', 'afsac' ); ?>
						</a>
					</div>

				</div>
			</div>

		</article>

		<?php
		// Navigation de lecture : article précédent / suivant (même langue, Polylang).
		$afsac_prev = get_previous_post();
		$afsac_next = get_next_post();
		if ( $afsac_prev || $afsac_next ) :
			?>
			<nav class="afsac-anav" aria-label="<?php esc_attr_e( 'Navigation entre les actualités', 'afsac' ); ?>">
				<div class="afsac-container afsac-anav__grid">
					<?php
					$afsac_around = array(
						array( 'post' => $afsac_prev, 'dir' => 'prev', 'kicker' => __( 'Actualité précédente', 'afsac' ) ),
						array( 'post' => $afsac_next, 'dir' => 'next', 'kicker' => __( 'Actualité suivante', 'afsac' ) ),
					);
					foreach ( $afsac_around as $afsac_side ) :
						if ( ! $afsac_side['post'] ) {
							continue;
						}
						$afsac_sid   = (int) $afsac_side['post']->ID;
						$afsac_sthumb = afsac_news_thumb_url( $afsac_sid, 'medium' );
						?>
						<a class="afsac-anav__card afsac-anav__card--<?php echo esc_attr( $afsac_side['dir'] ); ?> afsac-reveal" href="<?php echo esc_url( (string) get_permalink( $afsac_sid ) ); ?>">
							<?php if ( '' !== $afsac_sthumb ) : ?>
								<span class="afsac-anav__thumb" aria-hidden="true"><img src="<?php echo esc_url( $afsac_sthumb ); ?>" alt="" loading="lazy" decoding="async"></span>
							<?php endif; ?>
							<span class="afsac-anav__text">
								<span class="afsac-anav__kicker">
									<span class="afsac-anav__arrow" aria-hidden="true"><?php echo ( 'prev' === $afsac_side['dir'] ) ? '&larr;' : '&rarr;'; ?></span>
									<?php echo esc_html( $afsac_side['kicker'] ); ?>
								</span>
								<span class="afsac-anav__title"><?php echo esc_html( get_the_title( $afsac_sid ) ); ?></span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</nav>
			<?php
		endif;
		?>

		<?php
		// Articles liés : même catégorie, langue courante (Polylang), hors article courant.
		if ( $afsac_cat ) :
			$afsac_related = new WP_Query(
				array(
					'post_type'           => 'post',
					'post_status'         => 'publish',
					'posts_per_page'      => 3,
					'cat'                 => (int) $afsac_cat->term_id,
					'post__not_in'        => array( get_the_ID() ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
				)
			);
			if ( $afsac_related->have_posts() ) :
				?>
				<section class="afsac-news-list afsac-single__related">
					<div class="afsac-container">
						<h2 class="afsac-single__related-title afsac-reveal"><?php esc_html_e( 'À lire aussi', 'afsac' ); ?></h2>
						<div class="afsac-news-grid afsac-stagger">
							<?php
							while ( $afsac_related->have_posts() ) :
								$afsac_related->the_post();
								get_template_part( 'template-parts/news-card', null, array( 'reveal' => true ) );
							endwhile;
							?>
						</div>
						<div class="afsac-single__related-more">
							<a class="afsac-button" href="<?php echo esc_url( $afsac_news_url ); ?>"><?php esc_html_e( 'Toutes les actualités', 'afsac' ); ?></a>
						</div>
					</div>
				</section>
				<?php
				wp_reset_postdata();
			endif;
		endif;
		?>

	</main>

	<?php
endwhile;

get_footer();
