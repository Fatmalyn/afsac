<?php
/**
 * Rangée d'actualité — format LISTE de la page « Actualités ».
 *
 * Demande client (08/2026) : les actualités ne sont plus une grille de cartes
 * mais une LISTE « image + texte », chaque entrée menant à sa page « Savoir
 * plus » (single.php). Les rangées alternent visuel à gauche / à droite
 * (modificateur --flip sur les rangs impairs) et se révèlent au défilement.
 *
 * Lit le post courant de la boucle (the_post() déjà appelé). Le visuel est
 * toujours présent : image à la une, sinon photo de repli (afsac_news_thumb_url).
 * Le titre porte le vrai lien ; son ::after couvre la rangée entière pour la
 * rendre cliquable sans dupliquer le lien pour les lecteurs d'écran.
 *
 * @param array $args {
 *     @type int  $index Rang dans la liste (0 = premier) — pilote l'alternance.
 *     @type bool $lead  Première actualité : rangée haute + badge « À la une ».
 * }
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_index = isset( $args['index'] ) ? (int) $args['index'] : 0;
$afsac_lead  = ! empty( $args['lead'] );
$afsac_flip  = ( 1 === $afsac_index % 2 );

$afsac_cats = get_the_category();
$afsac_cat  = ( ! empty( $afsac_cats ) && ! is_wp_error( $afsac_cats ) ) ? $afsac_cats[0] : null;

$afsac_thumb = function_exists( 'afsac_news_thumb_url' ) ? afsac_news_thumb_url( get_the_ID(), $afsac_lead ? 'large' : 'medium_large' ) : '';
$afsac_read  = function_exists( 'afsac_reading_time' ) ? afsac_reading_time( get_the_ID() ) : 0;

// Filtre par catégorie sur l'index (le badge est un raccourci, pas une archive).
$afsac_cat_url = $afsac_cat && function_exists( 'afsac_news_index_url' )
	? add_query_arg( 'categorie', $afsac_cat->slug, afsac_news_index_url() )
	: '';

$afsac_classes = 'afsac-nrow afsac-reveal';
if ( $afsac_flip ) {
	$afsac_classes .= ' afsac-nrow--flip';
}
if ( $afsac_lead ) {
	$afsac_classes .= ' afsac-nrow--lead';
}
?>
<article <?php post_class( $afsac_classes ); ?>>

	<div class="afsac-nrow__media">
		<span class="afsac-nrow__frame" aria-hidden="true">
			<?php if ( '' !== $afsac_thumb ) : ?>
				<img
					class="afsac-nrow__img"
					src="<?php echo esc_url( $afsac_thumb ); ?>"
					alt=""
					loading="<?php echo $afsac_lead ? 'eager' : 'lazy'; ?>"
					decoding="async"
				>
			<?php endif; ?>
			<span class="afsac-nrow__veil"></span>
		</span>

		<?php /* Pastille de date posée sur le visuel : repère de « fil » d'actualité. */ ?>
		<span class="afsac-nrow__stamp" aria-hidden="true">
			<b><?php echo esc_html( get_the_date( 'j' ) ); ?></b>
			<i><?php echo esc_html( get_the_date( 'M' ) ); ?></i>
			<em><?php echo esc_html( get_the_date( 'Y' ) ); ?></em>
		</span>

		<?php if ( $afsac_lead ) : ?>
			<span class="afsac-nrow__flag"><?php esc_html_e( 'À la une', 'afsac' ); ?></span>
		<?php endif; ?>
	</div>

	<div class="afsac-nrow__body">
		<div class="afsac-nrow__meta">
			<?php if ( $afsac_cat ) : ?>
				<a class="afsac-nrow__cat" href="<?php echo esc_url( $afsac_cat_url ); ?>"><?php echo esc_html( $afsac_cat->name ); ?></a>
			<?php endif; ?>
			<time class="afsac-nrow__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			<?php if ( $afsac_read ) : ?>
				<span class="afsac-nrow__read">
					<?php
					printf(
						/* translators: %d: durée de lecture en minutes. */
						esc_html( _n( '%d min de lecture', '%d min de lecture', $afsac_read, 'afsac' ) ),
						(int) $afsac_read
					);
					?>
				</span>
			<?php endif; ?>
		</div>

		<h2 class="afsac-nrow__title">
			<a class="afsac-nrow__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<p class="afsac-nrow__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), $afsac_lead ? 46 : 34 ) ); ?></p>

		<?php /* Faux bouton : le vrai lien est le titre, dont l'::after couvre la rangée. */ ?>
		<span class="afsac-nrow__cta">
			<?php esc_html_e( 'Savoir plus', 'afsac' ); ?>
			<span class="afsac-arrow" aria-hidden="true">&rarr;</span>
		</span>
	</div>

</article>
