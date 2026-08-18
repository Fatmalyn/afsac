<?php
/**
 * Panneau « laissez votre e-mail » qui précède le téléchargement de la brochure.
 *
 * Rendu UNE SEULE FOIS, dans la bande Documentation du pied de page (donc sur
 * toutes les pages). Deux visages pour le MÊME balisage :
 *   - sans JavaScript : un panneau ordinaire, à la suite de la carte ;
 *   - avec JavaScript (html.afsac-js) : une fenêtre modale, masquée jusqu'au
 *     clic sur la carte (voir assets/js/afsac-brochure.js).
 * Le CSS porte cette bascule, pas le PHP : un seul formulaire à maintenir, et
 * le téléchargement reste possible si le script ne se charge pas.
 *
 * Trois états, décidés côté serveur d'après le PRG du plugin :
 *   ?brochure=ok&dl=JETON  → remerciement + lien de téléchargement ;
 *   ?brochure=error&token= → formulaire + erreurs + valeurs conservées ;
 *   sinon                  → formulaire vierge.
 *
 * ATTENTION : aucune classe .afsac-reveal / .afsac-count ici. Le panneau est
 * masqué au chargement sous .afsac-js, et l'observateur d'animations laisserait
 * son contenu bloqué à opacity:0.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$afsac_files = function_exists( 'afsac_brochure_files' ) ? afsac_brochure_files() : array();
if ( ! $afsac_files ) {
	return;
}

// --- État renvoyé par le handler (PRG) -------------------------------------
$afsac_state = isset( $_GET['brochure'] ) ? sanitize_key( wp_unslash( $_GET['brochure'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture d'état d'affichage.

$afsac_errors = array();
$afsac_old    = array();
// Les jetons sont à CASSE MIXTE : afsac_brochure_clean_token() et surtout PAS
// sanitize_key(), qui les passerait en minuscules (cf. le plugin).
if ( 'error' === $afsac_state && isset( $_GET['token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$afsac_payload = get_transient( 'afsac_dlform_' . afsac_brochure_clean_token( wp_unslash( $_GET['token'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( is_array( $afsac_payload ) ) {
		$afsac_errors = isset( $afsac_payload['errors'] ) ? (array) $afsac_payload['errors'] : array();
		$afsac_old    = isset( $afsac_payload['old'] ) ? (array) $afsac_payload['old'] : array();
	}
}

// Succès : le jeton doit être encore valable, sinon on repart sur le formulaire.
$afsac_dl_url = '';
if ( 'ok' === $afsac_state && isset( $_GET['dl'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$afsac_token = afsac_brochure_clean_token( wp_unslash( $_GET['dl'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( afsac_brochure_read_token( $afsac_token ) ) {
		$afsac_dl_url = afsac_brochure_download_url( $afsac_token );
	}
}
$afsac_success = ( '' !== $afsac_dl_url );

// Le panneau s'ouvre de lui-même quand il porte un résultat à montrer.
$afsac_open = ( $afsac_success || ! empty( $afsac_errors ) || 'expired' === $afsac_state );

$afsac_val = function ( $key ) use ( $afsac_old ) {
	return isset( $afsac_old[ $key ] ) ? (string) $afsac_old[ $key ] : '';
};

// --- Édition proposée par défaut -------------------------------------------
$afsac_default_key = $afsac_val( 'afsac_dl_edition' );
if ( '' === $afsac_default_key || ! isset( $afsac_files[ $afsac_default_key ] ) ) {
	$afsac_default_key = afsac_brochure_default_key();
}
$afsac_picked = isset( $afsac_files[ $afsac_default_key ] ) ? $afsac_files[ $afsac_default_key ] : reset( $afsac_files );
$afsac_size   = ! empty( $afsac_picked['filesize'] ) ? size_format( (int) $afsac_picked['filesize'] ) : '';

// Arguments de réassurance : ce que contient le PDF (contenu, pas interface bavarde).
// L'arabe n'est plus annoncé (demande client) : la brochure n'existe qu'en FR/EN,
// promettre une troisième langue ici contredirait le sélecteur d'édition.
$afsac_points = array(
	__( 'Les 11 domaines OACI du programme TRAINAIR PLUS', 'afsac' ),
	__( 'Le cursus AVSEC complet, en français et en anglais', 'afsac' ),
	__( 'Modalités, durées et publics visés de chaque cours', 'afsac' ),
);
?>
<div class="afsac-bgate<?php echo $afsac_open ? ' is-open' : ''; ?>" id="afsac-brochure" data-afsac-gate<?php echo $afsac_open ? ' data-bgate-open' : ''; ?>>
	<div class="afsac-bgate__backdrop" data-bgate-close></div>

	<div class="afsac-bgate__dialog" data-bgate-dialog>
		<button type="button" class="afsac-bgate__close" data-bgate-close>
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18"/></svg>
			<span class="screen-reader-text"><?php esc_html_e( 'Fermer', 'afsac' ); ?></span>
		</button>

		<?php /* Volet visuel : rappelle l'objet du formulaire une fois la modale ouverte. */ ?>
		<div class="afsac-bgate__aside">
			<span class="afsac-bgate__stack" aria-hidden="true">
				<span class="afsac-bgate__sheet afsac-bgate__sheet--3"></span>
				<span class="afsac-bgate__sheet afsac-bgate__sheet--2"></span>
				<span class="afsac-bgate__sheet afsac-bgate__sheet--1"><span class="afsac-bgate__sheet-tag">PDF</span></span>
			</span>
			<p class="afsac-bgate__aside-eyebrow"><?php esc_html_e( 'Programme de formation', 'afsac' ); ?></p>
			<p class="afsac-bgate__aside-title"><?php esc_html_e( 'Brochure AFSAC', 'afsac' ); ?></p>
			<ul class="afsac-bgate__points">
				<?php foreach ( $afsac_points as $afsac_point ) : ?>
					<li>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 12.5l5 5L20 6.5"/></svg>
						<?php echo esc_html( $afsac_point ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $afsac_size ) : ?>
				<p class="afsac-bgate__aside-meta"><?php echo esc_html( sprintf( /* translators: %s: taille du fichier (ex. « 4 Mo »). */ __( 'PDF · %s', 'afsac' ), $afsac_size ) ); ?></p>
			<?php endif; ?>
		</div>

		<div class="afsac-bgate__main">

			<?php if ( $afsac_success ) : ?>

				<?php /* ---------- État 3 : c'est parti ---------- */ ?>
				<div class="afsac-bgate__done" role="status">
					<span class="afsac-bgate__done-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12.5l5 5L20 6.5"/></svg>
					</span>
					<h2 class="afsac-bgate__title" id="afsac-bgate-title"><?php esc_html_e( 'Merci, votre brochure arrive', 'afsac' ); ?></h2>
					<p class="afsac-bgate__text"><?php esc_html_e( 'Le téléchargement démarre automatiquement. Nous vous avons également envoyé le lien par e-mail : il reste valable 7 jours.', 'afsac' ); ?></p>
					<a class="afsac-button afsac-bgate__submit" href="<?php echo esc_url( $afsac_dl_url ); ?>" data-bgate-auto>
						<?php esc_html_e( 'Télécharger la brochure', 'afsac' ); ?>
					</a>
					<p class="afsac-bgate__note"><?php esc_html_e( 'Le téléchargement n’a pas démarré ? Utilisez le bouton ci-dessus.', 'afsac' ); ?></p>
				</div>

			<?php else : ?>

				<?php /* ---------- États 1 & 2 : le formulaire ---------- */ ?>
				<h2 class="afsac-bgate__title" id="afsac-bgate-title"><?php esc_html_e( 'Recevoir la brochure', 'afsac' ); ?></h2>
				<p class="afsac-bgate__text"><?php esc_html_e( 'Indiquez-nous simplement qui vous êtes : le téléchargement démarre aussitôt et le lien vous est envoyé par e-mail.', 'afsac' ); ?></p>

				<?php if ( 'expired' === $afsac_state ) : ?>
					<div class="afsac-bgate__alert afsac-bgate__alert--err" role="alert">
						<p><?php esc_html_e( 'Ce lien de téléchargement a expiré. Renseignez à nouveau votre e-mail pour en recevoir un nouveau.', 'afsac' ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( $afsac_errors ) : ?>
					<div class="afsac-bgate__alert afsac-bgate__alert--err" role="alert">
						<ul>
							<?php foreach ( $afsac_errors as $afsac_err ) : ?>
								<li><?php echo esc_html( $afsac_err ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<form class="afsac-bgate__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
					<input type="hidden" name="action" value="afsac_brochure_submit">
					<input type="hidden" name="afsac_ts" value="" data-afsac-ts>
					<?php
					/*
					 * Langue de navigation portée par le formulaire : le handler tourne
					 * sur admin-post.php, où Polylang n'a AUCUNE page à interroger et
					 * renverrait une langue vide.
					 */
					?>
					<input type="hidden" name="afsac_dl_lang" value="<?php echo esc_attr( function_exists( 'pll_current_language' ) ? (string) pll_current_language() : substr( (string) get_locale(), 0, 2 ) ); ?>">
					<?php wp_nonce_field( 'afsac_brochure_submit', 'afsac_brochure_nonce' ); ?>
					<div class="afsac-bgate__hp" aria-hidden="true">
						<label>Website<input type="text" name="afsac_website" tabindex="-1" autocomplete="off"></label>
					</div>

					<div class="afsac-bgate__grid">
						<p class="afsac-bgate__field afsac-bgate__field--full">
							<label for="afsac_dl_nom"><?php esc_html_e( 'Nom et prénom', 'afsac' ); ?> <span class="afsac-req">*</span></label>
							<input type="text" id="afsac_dl_nom" name="afsac_dl_nom" required autocomplete="name" value="<?php echo esc_attr( $afsac_val( 'afsac_dl_nom' ) ); ?>">
						</p>
						<p class="afsac-bgate__field afsac-bgate__field--full">
							<label for="afsac_dl_email"><?php esc_html_e( 'E-mail professionnel', 'afsac' ); ?> <span class="afsac-req">*</span></label>
							<input type="email" id="afsac_dl_email" name="afsac_dl_email" required autocomplete="email" value="<?php echo esc_attr( $afsac_val( 'afsac_dl_email' ) ); ?>">
						</p>
						<p class="afsac-bgate__field">
							<label for="afsac_dl_organisation"><?php esc_html_e( 'Organisation', 'afsac' ); ?> <span class="afsac-req">*</span></label>
							<input type="text" id="afsac_dl_organisation" name="afsac_dl_organisation" required autocomplete="organization" value="<?php echo esc_attr( $afsac_val( 'afsac_dl_organisation' ) ); ?>">
						</p>
						<p class="afsac-bgate__field">
							<label for="afsac_dl_pays"><?php esc_html_e( 'Pays', 'afsac' ); ?></label>
							<input type="text" id="afsac_dl_pays" name="afsac_dl_pays" autocomplete="country-name" value="<?php echo esc_attr( $afsac_val( 'afsac_dl_pays' ) ); ?>">
						</p>
					</div>

					<?php if ( count( $afsac_files ) > 1 ) : ?>
						<?php /* Choix de l'édition : autonymes LITTÉRAUX (contenu), jamais traduits. */ ?>
						<fieldset class="afsac-bgate__editions">
							<legend><?php esc_html_e( 'Édition souhaitée', 'afsac' ); ?></legend>
							<div class="afsac-bgate__editions-list">
								<?php foreach ( $afsac_files as $afsac_key => $afsac_f ) : ?>
									<label class="afsac-bgate__edition"<?php echo $afsac_f['rtl'] ? ' dir="rtl"' : ''; ?>>
										<input type="radio" name="afsac_dl_edition" value="<?php echo esc_attr( $afsac_key ); ?>" <?php checked( $afsac_default_key, $afsac_key ); ?>>
										<span class="afsac-bgate__edition-code"><?php echo esc_html( $afsac_f['code'] ); ?></span>
										<span class="afsac-bgate__edition-name"><?php echo esc_html( $afsac_f['name'] ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</fieldset>
					<?php else : ?>
						<input type="hidden" name="afsac_dl_edition" value="<?php echo esc_attr( $afsac_default_key ); ?>">
					<?php endif; ?>

					<label class="afsac-bgate__consent">
						<input type="checkbox" name="afsac_consent_rgpd" value="1" required <?php checked( '1', $afsac_val( 'afsac_consent_rgpd' ) ); ?>>
						<span><?php esc_html_e( 'J’accepte que mes données soient utilisées pour me transmettre la brochure et les informations d’AFSAC, conformément au RGPD et à la loi n° 63-2004.', 'afsac' ); ?> <span class="afsac-req">*</span></span>
					</label>

					<button type="submit" class="afsac-button afsac-bgate__submit">
						<?php esc_html_e( 'Recevoir la brochure', 'afsac' ); ?>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/></svg>
					</button>

					<p class="afsac-bgate__note">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 3l7 3v6c0 4.2-2.9 7.6-7 9-4.1-1.4-7-4.8-7-9V6z"/></svg>
						<?php esc_html_e( 'Vos données ne sont ni revendues ni cédées. Aucun compte à créer.', 'afsac' ); ?>
					</p>
				</form>

			<?php endif; ?>
		</div>
	</div>
</div>
