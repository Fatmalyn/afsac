<?php
/**
 * Formations & Services — section « Services » (portefeuille de services).
 *
 * Données : CPT afsac_service (langue courante, ordre menu_order puis date).
 * Le titre = titre du post ; description + icône + lien via ACF. L'icône stocke
 * un slug résolu en tracé SVG par le registre interne ci-dessous (mêmes tracés
 * et même enveloppe qu'auparavant : aucun changement visuel). Aucun service
 * publié → la section ne s'affiche pas (état vide discret).
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Registre d'icônes (slug => tracé SVG, copié verbatim de l'ancien tableau).
// Les slugs = options du champ ACF « icône » du CPT afsac_service (acf-fields.php).
$afsac_service_icons = array(
	'document'   => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6"/><path d="M9 17h6"/>',
	'crayon'     => '<path d="M16.5 4.5l3 3L8 19l-4 1 1-4z"/><path d="M14.5 6.5l3 3"/>',
	'batiment'   => '<path d="M4 21h16"/><path d="M6 21V5a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v16"/><path d="M9 8h2"/><path d="M13 8h2"/><path d="M9 12h2"/><path d="M13 12h2"/><path d="M10 21v-4h4v4"/>',
	'equipe'     => '<path d="M16 19v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1"/><circle cx="9" cy="8" r="3"/><path d="M22 19v-1a4 4 0 0 0-3-3.87"/><path d="M16 5.13a4 4 0 0 1 0 7.75"/>',
	'calendrier' => '<rect x="4" y="5" width="16" height="16" rx="1"/><path d="M4 10h16"/><path d="M8 3v4"/><path d="M16 3v4"/>',
	'conseil'    => '<path d="M21 11.5a8.5 8.5 0 0 1-12.1 7.7L3 21l1.8-5.9A8.5 8.5 0 1 1 21 11.5z"/>',
);

$afsac_svg_open  = '<svg class="afsac-fs-services__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" aria-hidden="true" focusable="false">';
$afsac_svg_close = '</svg>';

$afsac_services_q = new WP_Query(
	array(
		'post_type'              => 'afsac_service',
		'post_status'            => 'publish',
		'posts_per_page'         => 12,
		'orderby'                => array(
			'menu_order' => 'ASC',
			'date'       => 'ASC',
		),
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	)
);

// État vide discret : aucune carte → on ne rend pas la section.
if ( ! $afsac_services_q->have_posts() ) {
	wp_reset_postdata();
	return;
}
?>
<section class="afsac-fs-services" id="services">
	<div class="afsac-container">
		<?php
		get_template_part(
			'template-parts/shared/section-head',
			null,
			array(
				'eyebrow' => __( 'Notre accompagnement', 'afsac' ),
				'title'   => __( 'Notre portefeuille de services', 'afsac' ),
				'center'  => true,
			)
		);
		?>
		<div class="afsac-feature-grid afsac-stagger">
			<?php
			while ( $afsac_services_q->have_posts() ) :
				$afsac_services_q->the_post();
				$afsac_sid  = get_the_ID();
				$afsac_desc = function_exists( 'get_field' ) ? (string) get_field( 'afsac_service_description', $afsac_sid ) : '';
				$afsac_ico  = function_exists( 'get_field' ) ? (string) get_field( 'afsac_service_icone', $afsac_sid ) : '';
				$afsac_lien = function_exists( 'get_field' ) ? (string) get_field( 'afsac_service_lien', $afsac_sid ) : '';
				$afsac_url  = ( '' !== $afsac_lien ) ? $afsac_lien : get_permalink( $afsac_sid );
				$afsac_path = isset( $afsac_service_icons[ $afsac_ico ] ) ? $afsac_service_icons[ $afsac_ico ] : '';
				?>
				<article class="afsac-feature-card afsac-reveal">
					<?php if ( '' !== $afsac_path ) : ?>
						<span class="afsac-feature-card__icon"><?php echo $afsac_svg_open . $afsac_path . $afsac_svg_close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Tracé SVG statique issu du registre interne. ?></span>
					<?php endif; ?>
					<h3 class="afsac-feature-card__title"><?php the_title(); ?></h3>
					<?php if ( '' !== $afsac_desc ) : ?>
						<p class="afsac-feature-card__text"><?php echo esc_html( $afsac_desc ); ?></p>
					<?php endif; ?>
					<a class="afsac-feature-card__link" href="<?php echo esc_url( $afsac_url ); ?>"><?php esc_html_e( 'En savoir plus', 'afsac' ); ?> <span class="afsac-arrow" aria-hidden="true">›</span></a>
				</article>
			<?php endwhile; ?>
		</div>
	</div>
</section>
<?php
wp_reset_postdata();
