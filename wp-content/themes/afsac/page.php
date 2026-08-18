<?php
/**
 * Template des pages éditoriales (blocs Gutenberg natifs).
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="afsac-container">

	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'afsac-entry' ); ?>>

			<header class="afsac-page-header">
				<h1 class="afsac-entry-title"><?php the_title(); ?></h1>
			</header>

			<div class="afsac-entry-content">
				<?php
				the_content();

				wp_link_pages(
					array(
						'before' => '<nav class="afsac-page-links">' . esc_html__( 'Pages :', 'afsac' ),
						'after'  => '</nav>',
					)
				);
				?>
			</div>

		</article>
		<?php
	endwhile;
	?>

</div>

<?php
get_footer();
