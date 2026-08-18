<?php
/**
 * Carte de session (page « Calendrier des sessions »).
 *
 * Refonte : l'ancienne ligne de <table> (Cours / Dates / Lieu / Frais / Action)
 * devient une CARTE lisible — VIGNETTE DU COURS à gauche, cours + dates + badges
 * au centre, tarif + inscription à droite. Les sessions sont regroupées par mois
 * par sessions-filters.js (les en-têtes de mois sont insérés dynamiquement après
 * filtrage/tri/pagination, à partir de data-month/data-month-label).
 *
 * Demande client (réunion) : « à la place des dates, on veut les images », comme
 * le calendrier de l'OACI. Le bloc date à gauche a donc cédé sa place à la
 * vignette (image à la une de la formation, sinon motif SVG) ; les dates
 * complètes sont reprises dans le corps de la carte, avec la durée.
 *
 * La colonne « Lieu » a disparu : toutes les sessions se tiennent au même
 * endroit (demande client), affiché une seule fois dans la barre d'outils.
 *
 * La carte n'est PAS un lien : elle contient déjà deux liens (le cours et
 * l'inscription) — imbriquer des <a> serait invalide.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_title   = isset( $args['title'] ) ? (string) $args['title'] : '';
$afsac_link    = isset( $args['link'] ) ? (string) $args['link'] : '';
$afsac_abbr    = isset( $args['abbr'] ) ? (string) $args['abbr'] : '';
$afsac_thumb   = isset( $args['thumb'] ) ? (string) $args['thumb'] : '';
$afsac_motif   = isset( $args['motif'] ) ? (string) $args['motif'] : '';
$afsac_datel   = isset( $args['date_label'] ) ? (string) $args['date_label'] : '';
$afsac_mode    = isset( $args['mode'] ) ? (string) $args['mode'] : 'presentiel';
$afsac_model   = isset( $args['mode_label'] ) ? (string) $args['mode_label'] : '';
$afsac_prog    = isset( $args['programme'] ) ? (string) $args['programme'] : '';
$afsac_progl   = isset( $args['prog_label'] ) ? (string) $args['prog_label'] : '';
$afsac_langl   = isset( $args['lang_label'] ) ? (string) $args['lang_label'] : '';
$afsac_langs   = ( isset( $args['lang_slugs'] ) && is_array( $args['lang_slugs'] ) ) ? $args['lang_slugs'] : array();
$afsac_place   = isset( $args['place'] ) ? (string) $args['place'] : '';
$afsac_areas   = ( isset( $args['area_slugs'] ) && is_array( $args['area_slugs'] ) ) ? $args['area_slugs'] : array();
$afsac_typek   = isset( $args['type_key'] ) ? (string) $args['type_key'] : '';
$afsac_date    = isset( $args['date'] ) ? (string) $args['date'] : '';
$afsac_mkey    = isset( $args['month_key'] ) ? (string) $args['month_key'] : '';
$afsac_mlabel  = isset( $args['month_label'] ) ? (string) $args['month_label'] : '';
$afsac_dur     = isset( $args['duration'] ) ? (string) $args['duration'] : '';
$afsac_price   = isset( $args['price'] ) ? (string) $args['price'] : '';
$afsac_cur     = isset( $args['currency'] ) ? (string) $args['currency'] : '';
$afsac_regurl  = isset( $args['reg_url'] ) ? (string) $args['reg_url'] : '';
$afsac_closed  = ! empty( $args['closed'] );
$afsac_rtl     = ! empty( $args['rtl'] );
?>
<article class="afsac-cal-card afsac-reveal afsac-reveal--fade"
	data-cal-row
	data-mode="<?php echo esc_attr( $afsac_mode ); ?>"
	data-prog="<?php echo esc_attr( $afsac_prog ); ?>"
	data-type="<?php echo esc_attr( $afsac_typek ); ?>"
	data-area="<?php echo esc_attr( implode( ' ', $afsac_areas ) ); ?>"
	data-lang="<?php echo esc_attr( implode( ' ', $afsac_langs ) ); ?>"
	data-date="<?php echo esc_attr( $afsac_date ); ?>"
	data-month="<?php echo esc_attr( $afsac_mkey ); ?>"
	data-month-label="<?php echo esc_attr( $afsac_mlabel ); ?>"
	data-title="<?php echo esc_attr( $afsac_title ); ?>">

	<?php
	/*
	 * VIGNETTE — image à la une de la formation si elle existe, sinon panneau
	 * motif (dégradé bleu OACI + pictogramme aviation, registre partagé avec la
	 * « Liste de cours »). Purement décoratif : le titre juste à côté porte
	 * l'information, donc alt="" / aria-hidden.
	 */
	?>
	<div class="afsac-cal-card__media">
		<?php if ( '' !== $afsac_thumb ) : ?>
			<?php echo $afsac_thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_the_post_thumbnail() renvoie du HTML déjà échappé. ?>
		<?php else : ?>
			<span class="afsac-cal-card__thumb afsac-cal-card__thumb--motif" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false"><?php echo $afsac_motif; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Motif SVG statique du registre interne. ?></svg>
				<?php if ( '' !== $afsac_abbr ) : ?>
					<b class="afsac-cal-card__thumbcode"><?php echo esc_html( $afsac_abbr ); ?></b>
				<?php endif; ?>
			</span>
		<?php endif; ?>
	</div>

	<div class="afsac-cal-card__what">
		<h3 class="afsac-cal-card__title">
			<?php if ( '' !== $afsac_link ) : ?>
				<a class="afsac-cal-card__titlelink" href="<?php echo esc_url( $afsac_link ); ?>"<?php echo $afsac_rtl ? ' dir="rtl"' : ''; ?>><?php echo esc_html( $afsac_title ); ?></a>
			<?php else : ?>
				<span<?php echo $afsac_rtl ? ' dir="rtl"' : ''; ?>><?php echo esc_html( $afsac_title ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $afsac_abbr ) : ?><span class="afsac-cal-card__code"><?php echo esc_html( $afsac_abbr ); ?></span><?php endif; ?>
		</h3>

		<?php /* Dates + durée : reprises ici depuis que la vignette occupe la colonne de gauche. */ ?>
		<?php if ( '' !== $afsac_datel || '' !== $afsac_dur ) : ?>
			<p class="afsac-cal-card__when">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
				<?php if ( '' !== $afsac_datel ) : ?>
					<time class="afsac-cal-card__dates" datetime="<?php echo esc_attr( '' !== $afsac_date ? substr( $afsac_date, 0, 4 ) . '-' . substr( $afsac_date, 4, 2 ) . '-' . substr( $afsac_date, 6, 2 ) : '' ); ?>"><?php echo esc_html( $afsac_datel ); ?></time>
				<?php endif; ?>
				<?php if ( '' !== $afsac_dur ) : ?>
					<span class="afsac-cal-card__dur"><?php echo esc_html( $afsac_dur ); ?></span>
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<div class="afsac-cal-card__badges">
			<?php
			/*
			 * PROGRAMME en tête de ligne : c'est l'axe des onglets depuis 08/2026,
			 * la carte doit donc porter la même clé de lecture que le filtre.
			 */
			?>
			<?php if ( '' !== $afsac_progl ) : ?>
				<span class="afsac-cal-badge afsac-cal-badge--prog afsac-cal-badge--prog-<?php echo esc_attr( $afsac_prog ); ?>"><?php echo esc_html( $afsac_progl ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $afsac_model ) : ?>
				<span class="afsac-cal-badge afsac-cal-badge--<?php echo esc_attr( $afsac_mode ); ?>"><?php echo esc_html( $afsac_model ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $afsac_langl ) : ?>
				<span class="afsac-cal-badge afsac-cal-badge--lang"><?php echo esc_html( $afsac_langl ); ?></span>
			<?php endif; ?>
			<?php
			/*
			 * Lieu : MASQUÉ en CSS quand toutes les sessions partagent le même lieu
			 * (classe .afsac-calendar--single-place sur <main>, le lieu est alors
			 * annoncé une seule fois dans la barre d'outils). Rendu ici pour ne pas
			 * perdre l'information si plusieurs lieux coexistent.
			 */
			?>
			<?php if ( '' !== $afsac_place ) : ?>
				<span class="afsac-cal-badge afsac-cal-card__place"><?php echo esc_html( $afsac_place ); ?></span>
			<?php endif; ?>
		</div>
	</div>

	<div class="afsac-cal-card__act">
		<?php if ( '' !== $afsac_price ) : ?>
			<span class="afsac-cal-card__price"><?php echo esc_html( $afsac_price ); ?><?php if ( '' !== $afsac_cur ) : ?><small><?php echo esc_html( $afsac_cur ); ?></small><?php endif; ?></span>
		<?php endif; ?>
		<?php if ( $afsac_closed || '' === $afsac_regurl ) : ?>
			<span class="afsac-cal-closed"><?php esc_html_e( 'Inscription clôturée', 'afsac' ); ?></span>
		<?php else : ?>
			<a class="afsac-btn afsac-btn--accent afsac-btn--sm" href="<?php echo esc_url( $afsac_regurl ); ?>">
				<?php esc_html_e( 'S’inscrire', 'afsac' ); ?>
				<span class="afsac-arrow" aria-hidden="true">&rarr;</span>
			</a>
		<?php endif; ?>
	</div>
</article>
