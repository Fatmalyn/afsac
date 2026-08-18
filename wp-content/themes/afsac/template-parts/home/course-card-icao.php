<?php
/**
 * Carte de cours au format « catalogue OACI » (accueil).
 *
 * Vignette à gauche, puis titre, ligne « Développé par … », rangée de trois
 * méta icône+libellé (langue / durée / modalité) et, en pied, une pastille de la
 * couleur du domaine OACI suivie de son intitulé.
 *
 * Données précalculées passées par le gabarit appelant : ce partiel ne dépend
 * PAS de la boucle (l'accueil construit ses cartes par ID, à partir de deux
 * requêtes entrelacées). C'est ce qui le distingue de template-parts/course-row.php,
 * qui sert l'archive de domaine et embarque les attributs data-* de filtrage.
 *
 * La couleur d'accent suit l'idiome maison : propriété personnalisée posée en
 * ligne sur la racine, pastille vide qui la consomme (cf. .afsac-formation-context__dot).
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_title  = isset( $args['title'] ) ? (string) $args['title'] : '';
$afsac_url    = isset( $args['url'] ) ? (string) $args['url'] : '';
$afsac_abbr   = isset( $args['abbr'] ) ? (string) $args['abbr'] : '';
$afsac_dev    = isset( $args['dev'] ) ? (string) $args['dev'] : '';
$afsac_thumb  = isset( $args['thumb'] ) ? (string) $args['thumb'] : '';
$afsac_motif  = isset( $args['motif'] ) ? (string) $args['motif'] : '';
$afsac_langn  = ( isset( $args['lang_names'] ) && is_array( $args['lang_names'] ) ) ? $args['lang_names'] : array();
$afsac_duree  = isset( $args['duree'] ) ? (string) $args['duree'] : '';
$afsac_meth   = isset( $args['meth_label'] ) ? (string) $args['meth_label'] : '';
$afsac_area   = isset( $args['area_name'] ) ? (string) $args['area_name'] : '';
$afsac_accent = isset( $args['area_color'] ) ? (string) $args['area_color'] : '';

/*
 * Bandeau de SESSION — optionnel. Présent uniquement quand la carte est
 * affichée dans un contexte « prochaines sessions » (volet AVSEC du catalogue) :
 * la même carte sert alors de vitrine du cours ET d'annonce de sa date. Absent,
 * la carte est strictement celle de l'accueil.
 * Clés : date (obligatoire), lieu, statut, statut_label.
 */
$afsac_sess = ( isset( $args['session'] ) && is_array( $args['session'] ) ) ? $args['session'] : array();

/*
 * Chargement de la vignette. Les cartes des pages MASQUÉES du carrousel doivent
 * être en `eager` : un navigateur ne télécharge jamais une image `lazy` tant
 * qu'elle est en display:none, la vignette n'arriverait donc qu'au moment de la
 * rotation — carré vide visible à chaque premier passage. En `eager` elles sont
 * récupérées au chargement (priorité basse, car hors écran) et la rotation est
 * instantanée. La première page reste `lazy` : elle est sous la ligne de flottaison.
 */
$afsac_loading = ( isset( $args['loading'] ) && 'eager' === $args['loading'] ) ? 'eager' : 'lazy';

if ( '' === $afsac_title || '' === $afsac_url ) {
	return;
}
?>
<a class="afsac-icao-card" href="<?php echo esc_url( $afsac_url ); ?>"<?php echo '' !== $afsac_accent ? ' style="--afsac-area-accent: ' . esc_attr( $afsac_accent ) . ';"' : ''; ?>>

	<span class="afsac-icao-card__media">
		<?php if ( '' !== $afsac_thumb ) : ?>
			<img class="afsac-icao-card__thumb" src="<?php echo esc_url( $afsac_thumb ); ?>" alt="" loading="<?php echo esc_attr( $afsac_loading ); ?>" decoding="async">
		<?php else : ?>
			<span class="afsac-icao-card__thumb afsac-icao-card__thumb--motif" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false"><?php echo $afsac_motif; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Motif SVG statique du registre interne. ?></svg>
			</span>
		<?php endif; ?>
	</span>

	<span class="afsac-icao-card__body">
		<?php if ( ! empty( $afsac_sess['date'] ) ) : ?>
			<span class="afsac-icao-card__session">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="4" y="5" width="16" height="16" rx="1"/><path d="M4 10h16M8 3v4M16 3v4"/></svg>
				<b><?php echo esc_html( $afsac_sess['date'] ); ?></b>
				<?php if ( ! empty( $afsac_sess['lieu'] ) ) : ?>
					<span class="afsac-icao-card__session-lieu"><?php echo esc_html( $afsac_sess['lieu'] ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $afsac_sess['statut_label'] ) ) : ?>
					<span class="afsac-icao-card__session-statut afsac-icao-card__session-statut--<?php echo esc_attr( isset( $afsac_sess['statut'] ) ? $afsac_sess['statut'] : 'ouvert' ); ?>"><?php echo esc_html( $afsac_sess['statut_label'] ); ?></span>
				<?php endif; ?>
			</span>
		<?php endif; ?>

		<h3 class="afsac-icao-card__title" dir="auto">
			<?php echo esc_html( $afsac_title ); ?><?php if ( '' !== $afsac_abbr ) : ?> <span class="afsac-icao-card__abbr">(<?php echo esc_html( $afsac_abbr ); ?>)</span><?php endif; ?>
		</h3>

		<?php if ( '' !== $afsac_dev ) : ?>
			<span class="afsac-icao-card__dev"><?php printf( /* translators: %s: developer. */ esc_html__( 'Développé par %s', 'afsac' ), esc_html( $afsac_dev ) ); ?></span>
		<?php endif; ?>

		<span class="afsac-icao-card__meta">
			<?php if ( ! empty( $afsac_langn ) ) : ?>
				<span class="afsac-icao-card__meta-item"><?php echo afsac_meta_icon( 'lang' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?><?php echo esc_html( implode( ' · ', $afsac_langn ) ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $afsac_duree ) : ?>
				<span class="afsac-icao-card__meta-item"><?php echo afsac_meta_icon( 'duration' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?><?php echo esc_html( $afsac_duree ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $afsac_meth ) : ?>
				<span class="afsac-icao-card__meta-item"><?php echo afsac_meta_icon( 'method' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique. ?><?php echo esc_html( $afsac_meth ); ?></span>
			<?php endif; ?>
		</span>

		<?php if ( '' !== $afsac_area ) : ?>
			<span class="afsac-icao-card__area">
				<span class="afsac-icao-card__area-dot" aria-hidden="true"></span>
				<span dir="auto"><?php echo esc_html( $afsac_area ); ?></span>
			</span>
		<?php endif; ?>
	</span>
</a>
