<?php
/**
 * Ligne de cours riche (page « Liste de cours » d'un domaine).
 *
 * Markup unique (ligne desktop → carte mobile en CSS). Lien étiré → fiche cours.
 * Données précalculées passées par le gabarit. Les attributs data-* alimentent le
 * filtrage / tri / pagination client (area-filters.js). Vignette : image à la une
 * si présente (lazy), sinon motif SVG illustré (panneau dégradé teinté accent).
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_abbr    = isset( $args['abbr'] ) ? (string) $args['abbr'] : '';
$afsac_dev     = isset( $args['dev'] ) ? (string) $args['dev'] : '';
$afsac_langn   = ( isset( $args['lang_names'] ) && is_array( $args['lang_names'] ) ) ? $args['lang_names'] : array();
$afsac_langs   = ( isset( $args['lang_slugs'] ) && is_array( $args['lang_slugs'] ) ) ? $args['lang_slugs'] : array();
$afsac_duree   = isset( $args['duree'] ) ? (string) $args['duree'] : '';
$afsac_methl   = isset( $args['meth_label'] ) ? (string) $args['meth_label'] : '';
$afsac_meths   = isset( $args['meth_slug'] ) ? (string) $args['meth_slug'] : 'instructeur';
$afsac_typen   = isset( $args['type_name'] ) ? (string) $args['type_name'] : '';
$afsac_typek   = isset( $args['type_key'] ) ? (string) $args['type_key'] : '';
$afsac_subs    = ( isset( $args['sub_slugs'] ) && is_array( $args['sub_slugs'] ) ) ? $args['sub_slugs'] : array();
// Domaine OACI de premier niveau : cible du filtre « Domaine » de l'archive
// générique. Toujours émis — inutile sur l'archive d'un domaine (tout le jeu y
// partage la même valeur), mais inoffensif et ça garde un seul balisage.
$afsac_areasl  = isset( $args['area_slug'] ) ? (string) $args['area_slug'] : '';
$afsac_virtual = ! empty( $args['virtual'] );
$afsac_hassess = ! empty( $args['has_session'] );
$afsac_locslug = isset( $args['loc_slug'] ) ? (string) $args['loc_slug'] : '';
$afsac_reduced = ! empty( $args['reduced'] );

// Motif de repli (helper partagé : registre + choix déterministe).
$afsac_motif = function_exists( 'afsac_course_motif' ) ? afsac_course_motif( $afsac_subs, $afsac_typek, get_the_ID() ) : '';

// Jeu d'icônes à trait partagé avec la carte de l'accueil (afsac_meta_icon()).
$afsac_icon_lang = afsac_meta_icon( 'lang' );
$afsac_icon_dur  = afsac_meta_icon( 'duration' );
$afsac_icon_meth = afsac_meta_icon( 'method' );
?>
<article class="afsac-course-row"
	data-method="<?php echo esc_attr( $afsac_meths ); ?>"
	data-type="<?php echo esc_attr( $afsac_typek ); ?>"
	data-sub="<?php echo esc_attr( implode( ' ', $afsac_subs ) ); ?>"
	data-area="<?php echo esc_attr( $afsac_areasl ); ?>"
	data-langs="<?php echo esc_attr( implode( ' ', $afsac_langs ) ); ?>"
	data-virtual="<?php echo $afsac_virtual ? '1' : '0'; ?>"
	data-session="<?php echo $afsac_hassess ? '1' : '0'; ?>"
	data-location="<?php echo esc_attr( $afsac_locslug ); ?>"
	data-reduced="<?php echo $afsac_reduced ? '1' : '0'; ?>"
	data-title="<?php echo esc_attr( get_the_title() ); ?>">

	<div class="afsac-course-row__media">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'medium', array( 'class' => 'afsac-course-row__thumb', 'loading' => 'lazy', 'alt' => '' ) ); ?>
		<?php else : ?>
			<span class="afsac-course-row__thumb afsac-course-row__thumb--motif" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false"><?php echo $afsac_motif; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Motif SVG statique du registre interne. ?></svg>
			</span>
		<?php endif; ?>
	</div>

	<div class="afsac-course-row__body">
		<h2 class="afsac-course-row__title">
			<a class="afsac-course-row__link" href="<?php the_permalink(); ?>"><?php the_title(); ?><?php if ( '' !== $afsac_abbr ) : ?> <span class="afsac-course-row__abbr">(<?php echo esc_html( $afsac_abbr ); ?>)</span><?php endif; ?></a>
		</h2>

		<?php if ( '' !== $afsac_dev ) : ?>
			<p class="afsac-course-row__dev"><?php printf( /* translators: %s: developer. */ esc_html__( 'Développé par %s', 'afsac' ), esc_html( $afsac_dev ) ); ?></p>
		<?php endif; ?>

		<div class="afsac-course-row__meta">
			<?php if ( ! empty( $afsac_langn ) ) : ?>
				<span class="afsac-course-row__meta-item"><?php echo $afsac_icon_lang; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?><?php echo esc_html( implode( ' · ', $afsac_langn ) ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $afsac_duree ) : ?>
				<span class="afsac-course-row__meta-item"><?php echo $afsac_icon_dur; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?><?php echo esc_html( $afsac_duree ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $afsac_methl ) : ?>
				<span class="afsac-course-row__meta-item"><?php echo $afsac_icon_meth; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?><?php echo esc_html( $afsac_methl ); ?></span>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( '' !== $afsac_typen ) : ?>
		<div class="afsac-course-row__aside">
			<span class="afsac-course-row__type"><?php echo esc_html( $afsac_typen ); ?></span>
			<?php if ( $afsac_reduced ) : ?>
				<span class="afsac-course-row__reduced"><?php esc_html_e( 'Tarif réduit ACA', 'afsac' ); ?></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</article>
