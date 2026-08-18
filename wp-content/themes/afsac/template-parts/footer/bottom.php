<?php
/**
 * Footer — bande de badges institutionnels + barre basse
 * (copyright + liens légaux du Customizer).
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_legal = afsac_get_legal_links();
?>
<?php /* B. Strip 3 cartes (séparateurs = gap 1px sur fond 5 % du conteneur). */ ?>
<div class="afsac-footer__badges-band">
	<div class="afsac-container">
		<ul class="afsac-footer__badges">
			<li class="afsac-badge-inst afsac-badge-inst--icao">
				<span class="afsac-badge-inst__lines">
					<span class="afsac-badge-inst__sup">ICAO · OACI</span>
					<span class="afsac-badge-inst__main"><?php esc_html_e( 'Member State', 'afsac' ); ?></span>
				</span>
			</li>
			<li class="afsac-badge-inst afsac-badge-inst--trainair">
				<span class="afsac-badge-inst__main">TRAINAIR PLUS</span>
			</li>
			<li class="afsac-badge-inst afsac-badge-inst--avsec">
				<span class="afsac-badge-inst__lines">
					<span class="afsac-badge-inst__sup"><?php esc_html_e( 'Regional', 'afsac' ); ?></span>
					<span class="afsac-badge-inst__main"><?php esc_html_e( 'AVSEC Training Centre', 'afsac' ); ?></span>
				</span>
			</li>
		</ul>
	</div>
</div>

<?php /* Barre du bas. */ ?>
<div class="afsac-footer__bottom">
	<div class="afsac-container afsac-footer__bottom-inner">
		<p class="afsac-footer__copyright">
			<?php
			printf(
				/* translators: 1: année, 2: marque (AFSAC). */
				esc_html__( '© %1$s %2$s · ICAO ASTC Tunisia. Tous droits réservés.', 'afsac' ),
				esc_html( wp_date( 'Y' ) ),
				esc_html( 'AFSAC' )
			);
			?>
		</p>

		<?php
		/*
		 * Relance de la visite guidée. C'est un LIEN vers l'accueil (?visite=1),
		 * pas un bouton : sans JavaScript il ramène à la page où la visite est
		 * rendue, et depuis l'accueil le script intercepte le clic pour la jouer
		 * sur place.
		 */
		?>
		<?php if ( function_exists( 'afsac_tour_enabled' ) && afsac_tour_enabled() ) : ?>
			<a class="afsac-footer__tour" href="<?php echo esc_url( afsac_tour_relaunch_url() ); ?>" data-afsac-tour-start>
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.6 9.4a2.5 2.5 0 1 1 3.3 2.9c-.6.2-.9.8-.9 1.4v.4"/><path d="M12 17.2h.01"/></svg>
				<span><?php esc_html_e( 'Revoir la visite guidée', 'afsac' ); ?></span>
			</a>
		<?php endif; ?>

		<?php if ( ! empty( $afsac_legal ) ) : ?>
			<nav class="afsac-footer__legal" aria-label="<?php esc_attr_e( 'Mentions légales', 'afsac' ); ?>">
				<?php foreach ( $afsac_legal as $afsac_legal_link ) : ?>
					<a href="<?php echo esc_url( $afsac_legal_link['url'] ); ?>"><?php echo esc_html( $afsac_legal_link['label'] ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</div>
</div>
