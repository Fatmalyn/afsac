<?php
/**
 * Bandeau PARTAGÉ « Nos partenaires » — logos défilant en boucle continue.
 *
 * Utilisé par l'accueil (template-parts/home/partenaires.php) ET par la page
 * « Références & Témoignages » : le client veut le MÊME bandeau de logos aux
 * deux endroits — toute évolution se fait donc ICI, pas dans les appelants.
 *
 * Source : CPT afsac_reference (logo = image à la une). Les références cochées
 * « Afficher dans le bandeau Partenaires » (méta afsac_reference_partenaire)
 * ont la priorité ; si aucune n'est cochée, toutes les références sont prises.
 * Fallback DÉMO : logos posés dans assets/images/partners/<slug>.(svg|png|webp|jpg).
 *
 * Le bandeau défile en CSS (pas de JS) : chaque ligne rend ses logos DEUX fois
 * (groupe + duplicata aria-hidden) pour une boucle sans saut ; en
 * prefers-reduced-motion le duplicata est masqué et la ligne passe en grille.
 *
 * Il n'existe qu'UNE mise en forme : le bandeau défilant. La variante « grille
 * statique » qu'utilisait « Références & Témoignages » a été retirée le
 * 11/08/2026 (demande client : « fais défiler les logos »). Ce qui gênait la
 * lecture à l'époque n'était pas le défilement mais le filtre grayscale : la
 * page pose donc `afsac-partenaires--page`, qui garde les logos en couleur.
 *
 * @param array $args {
 *     @type string $eyebrow Sur-titre (défaut « Ils nous font confiance »).
 *     @type string $title   Titre de section (défaut « Nos partenaires »).
 *     @type string $lead    Paragraphe d'introduction optionnel.
 *     @type string $id      Ancre optionnelle posée sur la <section>.
 *     @type string $class   Classe(s) supplémentaire(s) sur la <section>.
 *     @type int    $limit   Nombre maximum de logos (défaut 24).
 *     @type int    $rows    Nombre de lignes défilantes (défaut 1). À 2, les
 *                           logos sont répartis en alternance sur deux lignes
 *                           qui défilent en SENS OPPOSÉS — mise en scène
 *                           demandée par le client le 11/08/2026 pour la page
 *                           « Références & Témoignages ».
 * }
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_pm_eyebrow = isset( $args['eyebrow'] ) ? (string) $args['eyebrow'] : __( 'Ils nous font confiance', 'afsac' );
$afsac_pm_title   = isset( $args['title'] ) ? (string) $args['title'] : __( 'Nos partenaires', 'afsac' );
$afsac_pm_lead    = isset( $args['lead'] ) ? (string) $args['lead'] : '';
$afsac_pm_id      = isset( $args['id'] ) ? (string) $args['id'] : '';
$afsac_pm_class   = isset( $args['class'] ) ? (string) $args['class'] : '';
$afsac_pm_limit   = isset( $args['limit'] ) ? (int) $args['limit'] : 24;
$afsac_pm_rows    = isset( $args['rows'] ) ? max( 1, (int) $args['rows'] ) : 1;

/*
 * PARTENAIRES ≠ RÉFÉRENCES. Ce bandeau montre les organisations partenaires
 * (OACI, IATA, ACI, ECAC, ASECNA, AFCAC…), PAS le millier de clients du CPT
 * afsac_reference. Il ne retient donc QUE les fiches explicitement cochées
 * « Afficher dans le bandeau Partenaires » (méta afsac_reference_partenaire)
 * et pourvues d'un logo ; sinon il sert le jeu institutionnel du thème
 * (assets/images/partners/).
 *
 * Ne JAMAIS y remettre un repli « toutes les références » : importer des logos
 * de références remplacerait aussitôt les logos partenaires, sur cette page ET
 * sur l'accueil (régression vécue le 10/08/2026).
 */
$afsac_pm_pinned = array();

if ( post_type_exists( 'afsac_reference' ) ) {
	$afsac_pm_query = new WP_Query(
		array(
			'post_type'              => 'afsac_reference',
			'post_status'            => 'publish',
			'posts_per_page'         => $afsac_pm_limit,
			'orderby'                => 'menu_order title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			// Seules les fiches cochées « partenaire » entrent dans ce bandeau.
			'meta_key'               => 'afsac_reference_partenaire', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'             => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);

	while ( $afsac_pm_query->have_posts() ) {
		$afsac_pm_query->the_post();

		$afsac_pm_logo = (string) get_the_post_thumbnail_url( get_the_ID(), 'medium' );
		if ( '' === $afsac_pm_logo ) {
			continue; // Pas de logo : rien à montrer dans un mur de logos.
		}

		$afsac_pm_pinned[] = array(
			'name' => get_the_title(),
			'logo' => $afsac_pm_logo,
			'slug' => '',
		);
	}
	wp_reset_postdata();
}

$afsac_pm_items = $afsac_pm_pinned;

if ( empty( $afsac_pm_items ) ) {
	// Jeu institutionnel du thème — logos dans assets/images/partners/<slug>.(svg|png|webp|jpg).
	$afsac_pm_items = array(
		array( 'name' => 'ICAO · OACI',  'slug' => 'icao' ),
		array( 'name' => 'ACI Africa',   'slug' => 'aci-africa' ),
		array( 'name' => 'IATA',         'slug' => 'iata' ),
		array( 'name' => 'ECAC · CEAC',  'slug' => 'ecac' ),
		array( 'name' => 'ASECNA',       'slug' => 'asecna' ),
		array( 'name' => 'CEMAC',        'slug' => 'cemac' ),
		array( 'name' => 'AFCAC',        'slug' => 'afcac' ),
		array( 'name' => 'Arabian CAA',  'slug' => 'arabian-caa' ),
		array( 'name' => 'DGAC Tunisie', 'slug' => 'dgac-tunisie' ),
		array( 'name' => 'EASA',         'slug' => 'easa' ),
	);
}

if ( empty( $afsac_pm_items ) ) {
	return;
}

// Résout le logo depuis le dossier du thème quand aucun logo (CPT) n'est fourni.
$afsac_pm_dir = trailingslashit( get_theme_file_path( 'assets/images/partners' ) );
$afsac_pm_uri = trailingslashit( get_theme_file_uri( 'assets/images/partners' ) );
foreach ( $afsac_pm_items as &$afsac_pm_item ) {
	if ( empty( $afsac_pm_item['logo'] ) && ! empty( $afsac_pm_item['slug'] ) ) {
		foreach ( array( 'svg', 'png', 'webp', 'jpg' ) as $afsac_pm_ext ) {
			if ( file_exists( $afsac_pm_dir . $afsac_pm_item['slug'] . '.' . $afsac_pm_ext ) ) {
				$afsac_pm_item['logo'] = $afsac_pm_uri . $afsac_pm_item['slug'] . '.' . $afsac_pm_ext;
				break;
			}
		}
	}
}
unset( $afsac_pm_item );

/*
 * RÉPARTITION EN LIGNES. En alternance (0, 2, 4… / 1, 3, 5…) et non par moitiés :
 * les deux lignes restent équilibrées et mêlent les mêmes familles de logos.
 */
$afsac_pm_lines = array_fill( 0, $afsac_pm_rows, array() );
foreach ( array_values( $afsac_pm_items ) as $afsac_pm_i => $afsac_pm_p ) {
	$afsac_pm_lines[ $afsac_pm_i % $afsac_pm_rows ][] = $afsac_pm_p;
}
$afsac_pm_lines = array_values( array_filter( $afsac_pm_lines ) );

/*
 * DENSITÉ MINIMALE. Une ligne plus étroite que l'écran laisserait un trou béant
 * pendant la boucle : on répète ses logos jusqu'à couvrir largement la fenêtre
 * (8 tuiles ≈ 1 850 px). La répétition n'existe que pour l'œil — seule la
 * première occurrence de chaque logo porte un texte alternatif.
 */
$afsac_pm_min = 8;
foreach ( $afsac_pm_lines as &$afsac_pm_line ) {
	$afsac_pm_line = array( 'unique' => count( $afsac_pm_line ), 'items' => $afsac_pm_line );
	while ( count( $afsac_pm_line['items'] ) < $afsac_pm_min ) {
		$afsac_pm_line['items'] = array_merge( $afsac_pm_line['items'], array_slice( $afsac_pm_line['items'], 0, $afsac_pm_line['unique'] ) );
	}
}
unset( $afsac_pm_line );

/**
 * Rendu d'un groupe de cartes partenaires (réutilisé pour le duplicata).
 *
 * @param array $items     Liste des partenaires (répétitions comprises).
 * @param int   $unique    Nombre de logos distincts en tête de liste : au-delà,
 *                         les tuiles sont des répétitions décoratives.
 * @param bool  $duplicate Vrai pour le groupe dupliqué (masqué aux AT).
 */
$afsac_pm_render_group = static function ( $items, $unique, $duplicate = false ) {
	printf( '<ul class="afsac-partenaires__group%s"%s>', $duplicate ? ' afsac-partenaires__group--dup' : '', $duplicate ? ' aria-hidden="true"' : '' );
	foreach ( $items as $index => $p ) :
		$mute = $duplicate || $index >= $unique;
		?>
		<li class="afsac-partenaire"<?php echo $mute ? ' aria-hidden="true"' : ''; ?>>
			<?php if ( ! empty( $p['logo'] ) ) : ?>
				<img class="afsac-partenaire__logo" src="<?php echo esc_url( $p['logo'] ); ?>" alt="<?php echo $mute ? '' : esc_attr( $p['name'] ); ?>" loading="lazy" decoding="async" />
			<?php else : ?>
				<span class="afsac-partenaire__name"><?php echo esc_html( $p['name'] ); ?></span>
			<?php endif; ?>
		</li>
	<?php endforeach;
	echo '</ul>';
};
?>
<section class="afsac-section afsac-partenaires<?php echo $afsac_pm_class ? ' ' . esc_attr( $afsac_pm_class ) : ''; ?>"<?php echo $afsac_pm_id ? ' id="' . esc_attr( $afsac_pm_id ) . '"' : ''; ?>>
  <div class="afsac-container">
    <div class="afsac-partenaires__head afsac-reveal">
      <?php if ( '' !== $afsac_pm_eyebrow ) : ?>
        <p class="afsac-eyebrow"><?php echo esc_html( $afsac_pm_eyebrow ); ?></p>
      <?php endif; ?>
      <?php if ( '' !== $afsac_pm_title ) : ?>
        <h2 class="afsac-partenaires__title"><?php echo esc_html( $afsac_pm_title ); ?></h2>
      <?php endif; ?>
      <?php if ( '' !== $afsac_pm_lead ) : ?>
        <p class="afsac-partenaires__lead"><?php echo esc_html( $afsac_pm_lead ); ?></p>
      <?php endif; ?>
    </div>

    <?php foreach ( $afsac_pm_lines as $afsac_pm_n => $afsac_pm_line ) : ?>
      <?php
      /*
       * Durée proportionnelle au nombre de tuiles : toutes les lignes défilent à
       * la MÊME vitesse apparente (~4,2 s par tuile, soit les 42 s historiques
       * de l'accueil pour dix logos). Une ligne sur deux part en sens inverse.
       */
      $afsac_pm_duration = max( 24, (int) round( count( $afsac_pm_line['items'] ) * 4.2 ) );
      ?>
      <div class="afsac-partenaires__viewport afsac-reveal afsac-reveal--fade<?php echo 1 === $afsac_pm_n % 2 ? ' afsac-partenaires__viewport--reverse' : ''; ?>" style="--afsac-pm-duration: <?php echo esc_attr( $afsac_pm_duration ); ?>s">
        <div class="afsac-partenaires__track">
          <?php
          $afsac_pm_render_group( $afsac_pm_line['items'], $afsac_pm_line['unique'], false );
          $afsac_pm_render_group( $afsac_pm_line['items'], $afsac_pm_line['unique'], true ); // duplicata pour boucle continue.
          ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
