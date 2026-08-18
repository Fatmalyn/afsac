<?php
/**
 * Carte d'article (page « Actualités ») — template-part réutilisable.
 *
 * Lit le post courant de la boucle (the_post() déjà appelé). Image à la une si
 * présente (lazy), sinon motif de repli navy. Badge catégorie (1re catégorie),
 * date localisée, titre lien, extrait, lien « Lire ». Variante vedette (--feat)
 * pour l'article en tête d'index.
 *
 * @param array $args {
 *     @type bool $featured Carte vedette (large). Défaut false.
 *     @type bool $reveal   Ajoute .afsac-reveal (cascade .afsac-stagger). Défaut false.
 * }
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_feat   = ! empty( $args['featured'] );
$afsac_reveal = ! empty( $args['reveal'] ) ? ' afsac-reveal' : '';
$afsac_cats = get_the_category();
$afsac_cat  = ( ! empty( $afsac_cats ) && ! is_wp_error( $afsac_cats ) ) ? $afsac_cats[0] : null;

// Motif de repli riche (système partagé), déterministe par catégorie puis ID.
$afsac_motif = function_exists( 'afsac_course_motif' )
	? afsac_course_motif( array(), $afsac_cat ? $afsac_cat->slug : '', get_the_ID() )
	: '<path d="M4 5h13v14H4z"/><path d="M17 9h3v8a2 2 0 0 1-2 2h-1z"/><path d="M7 8h7M7 11h7M7 14h4"/>';
?>
<article <?php post_class( 'afsac-news-card' . ( $afsac_feat ? ' afsac-news-card--feat' : '' ) . $afsac_reveal ); ?>>
	<a class="afsac-news-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( $afsac_feat ? 'large' : 'medium_large', array( 'class' => 'afsac-news-card__thumb', 'loading' => 'lazy', 'alt' => '' ) ); ?>
		<?php else : ?>
			<span class="afsac-news-card__thumb afsac-news-card__thumb--motif">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" focusable="false"><?php echo $afsac_motif; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Tracé SVG statique du registre interne. ?></svg>
			</span>
		<?php endif; ?>
	</a>

	<div class="afsac-news-card__body">
		<div class="afsac-news-card__meta">
			<?php if ( $afsac_cat ) : ?>
				<a class="afsac-news-card__cat" href="<?php echo esc_url( get_category_link( $afsac_cat ) ); ?>"><?php echo esc_html( $afsac_cat->name ); ?></a>
			<?php endif; ?>
			<time class="afsac-news-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
		</div>

		<h2 class="afsac-news-card__title">
			<a class="afsac-news-card__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<p class="afsac-news-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), $afsac_feat ? 38 : 24 ) ); ?></p>

		<span class="afsac-news-card__more"><?php esc_html_e( 'Lire l’article', 'afsac' ); ?> <span aria-hidden="true">›</span></span>
	</div>
</article>
