<?php
/**
 * Template de repli générique.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="afsac-container">

	<?php if ( have_posts() ) : ?>

		<header class="afsac-page-header">
			<h1 class="afsac-entry-title"><?php bloginfo( 'name' ); ?></h1>
		</header>

		<div class="afsac-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'afsac-card' ); ?>>
					<div class="afsac-card__body">
						<h2 class="afsac-card__title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h2>
						<div class="afsac-card__excerpt"><?php the_excerpt(); ?></div>
					</div>
				</article>
				<?php
			endwhile;
			?>
		</div>

		<?php
		the_posts_pagination(
			array(
				'mid_size'  => 2,
				'prev_text' => esc_html__( 'Précédent', 'afsac' ),
				'next_text' => esc_html__( 'Suivant', 'afsac' ),
			)
		);
		?>

	<?php else : ?>

		<?php get_template_part( 'template-parts/content', 'none' ); ?>

	<?php endif; ?>

</div>

<?php
get_footer();
