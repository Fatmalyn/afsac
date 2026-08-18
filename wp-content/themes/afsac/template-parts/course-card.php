<?php
/**
 * Composant : carte de cours (style Catalogue, à plat).
 *
 * Utilise le post courant dans la boucle. Réutilisable par l'archive de domaine
 * (taxonomy-afsac_area) et, plus tard, par la recherche / le calendrier. Métas
 * affichées seulement si présentes (langue / modalité via taxonomies, durée via
 * ACF). Lien étiré : toute la carte est cliquable sans <a> imbriqué.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_id    = get_the_ID();
$afsac_duree = function_exists( 'get_field' ) ? (string) get_field( 'afsac_duree', $afsac_id ) : '';

$afsac_langues   = get_the_terms( $afsac_id, 'afsac_langue' );
$afsac_modalites = get_the_terms( $afsac_id, 'afsac_modalite' );
$afsac_langues   = ( $afsac_langues && ! is_wp_error( $afsac_langues ) ) ? $afsac_langues : array();
$afsac_modalites = ( $afsac_modalites && ! is_wp_error( $afsac_modalites ) ) ? $afsac_modalites : array();

$afsac_lang_names = wp_list_pluck( $afsac_langues, 'name' );
$afsac_mod_names  = wp_list_pluck( $afsac_modalites, 'name' );

// Métas clés (libellé, valeur) — chacune n'est ajoutée que si renseignée.
$afsac_metas = array();
if ( $afsac_lang_names ) {
	$afsac_metas[] = array( __( 'Langue', 'afsac' ), implode( ', ', $afsac_lang_names ) );
}
if ( $afsac_mod_names ) {
	$afsac_metas[] = array( __( 'Modalité', 'afsac' ), implode( ', ', $afsac_mod_names ) );
}
if ( '' !== $afsac_duree ) {
	$afsac_metas[] = array( __( 'Durée', 'afsac' ), $afsac_duree );
}
?>
<article <?php post_class( 'afsac-course-card' ); ?>>
	<h2 class="afsac-course-card__title">
		<a class="afsac-course-card__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</h2>

	<?php if ( $afsac_metas ) : ?>
		<dl class="afsac-course-card__meta">
			<?php foreach ( $afsac_metas as $afsac_m ) : ?>
				<div class="afsac-course-card__meta-row">
					<dt><?php echo esc_html( $afsac_m[0] ); ?></dt>
					<dd><?php echo esc_html( $afsac_m[1] ); ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>
	<?php endif; ?>

	<span class="afsac-course-card__cta" aria-hidden="true"><?php esc_html_e( 'Voir le cours', 'afsac' ); ?></span>
</article>
