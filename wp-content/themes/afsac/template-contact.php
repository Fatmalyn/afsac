<?php
/**
 * Template Name: Contact
 *
 * Page contact INDEXÉE : hero VIDÉO partagé (.afsac-video-hero, bandeau client)
 * puis 2 colonnes — formulaire (lead → CPT afsac_message, mêmes garde-fous que
 * l'inscription) et bloc coordonnées + carte Leaflet (1 marqueur, coords depuis
 * les options ACF, sans clé API). Le traitement POST vit dans le plugin
 * (includes/contact.php) ; les e-mails/CRM dans messaging.php.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// PRG : succès / erreurs (transient) renvoyés par le handler.
$afsac_errors  = array();
$afsac_old     = array();
$afsac_success = ( isset( $_GET['contact'] ) && 'ok' === $_GET['contact'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture d'état d'affichage.
if ( isset( $_GET['contact'], $_GET['token'] ) && 'error' === $_GET['contact'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$afsac_payload = get_transient( 'afsac_msg_' . sanitize_key( wp_unslash( $_GET['token'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( is_array( $afsac_payload ) ) {
		$afsac_errors = isset( $afsac_payload['errors'] ) ? (array) $afsac_payload['errors'] : array();
		$afsac_old    = isset( $afsac_payload['old'] ) ? (array) $afsac_payload['old'] : array();
	}
}
$afsac_val = function ( $key ) use ( $afsac_old ) {
	return isset( $afsac_old[ $key ] ) ? (string) $afsac_old[ $key ] : '';
};

// Pré-sélection du sujet via ?sujet= (ex. CTA « Demander un devis » → sujet=devis),
// si pas de valeur conservée par le PRG, et si la clé est dans la whitelist.
$afsac_sujet_pre = $afsac_val( 'afsac_sujet' );
if ( '' === $afsac_sujet_pre && isset( $_GET['sujet'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture d'un pré-remplissage, pas d'action.
	$afsac_g_sujet = sanitize_key( wp_unslash( $_GET['sujet'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$afsac_subj    = function_exists( 'afsac_contact_subjects' ) ? afsac_contact_subjects() : array();
	if ( isset( $afsac_subj[ $afsac_g_sujet ] ) ) {
		$afsac_sujet_pre = $afsac_g_sujet;
	}
}

$afsac_coords = function_exists( 'afsac_contact_coords' ) ? afsac_contact_coords() : array();
$afsac_c      = function ( $k ) use ( $afsac_coords ) {
	return isset( $afsac_coords[ $k ] ) ? (string) $afsac_coords[ $k ] : '';
};
$afsac_socials = array(
	'linkedin' => __( 'LinkedIn', 'afsac' ),
	'facebook' => __( 'Facebook', 'afsac' ),
	'x'        => __( 'X', 'afsac' ),
	'youtube'  => __( 'YouTube', 'afsac' ),
);
?>

<main id="primary" class="afsac-contact">

	<?php
	$afsac_eyebrow = function_exists( 'get_field' ) ? (string) get_field( 'afsac_page_eyebrow' ) : '';
	if ( '' === $afsac_eyebrow ) {
		$afsac_eyebrow = __( 'Nous contacter', 'afsac' );
	}
	$afsac_chapo = function_exists( 'get_field' ) ? (string) get_field( 'afsac_page_chapo' ) : '';
	if ( '' === $afsac_chapo ) {
		$afsac_chapo = __( 'Une question, un projet de formation ? Notre équipe vous répond.', 'afsac' );
	}
	/*
	 * Hero VIDÉO : c'est la variante nº 1 du bandeau « calendrier » livrée par le
	 * client, non retenue pour la page Calendrier (ses libellés de jours sont
	 * incohérents — « Vesday », « Frisit », dimanche en double). Elle est ici en
	 * simple ambiance monde + avion, avec voile renforcé (`dim`) : le texte
	 * couvre la partie fautive et le reste ne se lit pas.
	 */
	get_template_part(
		'template-parts/shared/video-hero',
		null,
		array(
			'layout'    => 'overlay',
			'align'     => 'start',
			'dim'       => true,
			'video_src' => get_theme_file_uri( 'assets/video/contact.mp4' ),
			'poster'    => get_theme_file_uri( 'assets/images/contact-hero-poster.jpg' ),
			'eyebrow'   => $afsac_eyebrow,
			'title'     => get_the_title( get_queried_object_id() ),
			'lead'      => $afsac_chapo,
			'stats'     => array(
				array(
					'value' => __( '48 h', 'afsac' ),
					'label' => __( 'Délai de réponse', 'afsac' ),
				),
				array(
					'value' => number_format_i18n( 2 ),
					'label' => __( 'Langues · FR EN', 'afsac' ),
				),
				array(
					'value' => '1981',
					'label' => __( 'Au service de l’aviation civile', 'afsac' ),
				),
			),
		)
	);
	?>

	<section class="afsac-contact-body">
		<div class="afsac-container afsac-contact-grid">

			<?php /* Colonne formulaire. */ ?>
			<div class="afsac-contact-formcol" id="afsac-contact-form">

				<?php if ( $afsac_success ) : ?>
					<div class="afsac-inscription__alert afsac-inscription__alert--ok" role="status">
						<h2><?php esc_html_e( 'Message envoyé', 'afsac' ); ?></h2>
						<p><?php esc_html_e( 'Merci. Votre message a bien été reçu ; nous reviendrons vers vous dans les meilleurs délais.', 'afsac' ); ?></p>
					</div>
				<?php else : ?>

					<?php if ( $afsac_errors ) : ?>
						<div class="afsac-inscription__alert afsac-inscription__alert--err" role="alert">
							<p><?php esc_html_e( 'Merci de corriger les points suivants :', 'afsac' ); ?></p>
							<ul>
								<?php foreach ( $afsac_errors as $afsac_err ) : ?>
									<li><?php echo esc_html( $afsac_err ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<form class="afsac-contact-form afsac-contact-panel" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
						<input type="hidden" name="action" value="afsac_contact_submit">
						<input type="hidden" name="afsac_ts" value="" data-afsac-ts>
						<?php wp_nonce_field( 'afsac_contact_submit', 'afsac_contact_nonce' ); ?>
						<div class="afsac-inscription__hp" aria-hidden="true">
							<label>Website<input type="text" name="afsac_website" tabindex="-1" autocomplete="off"></label>
						</div>

						<div class="afsac-contact-form__grid">
							<p class="afsac-contact__field">
								<label for="afsac_prenom"><?php esc_html_e( 'Prénom', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<input type="text" id="afsac_prenom" name="afsac_prenom" required autocomplete="given-name" value="<?php echo esc_attr( $afsac_val( 'afsac_prenom' ) ); ?>">
							</p>
							<p class="afsac-contact__field">
								<label for="afsac_nom"><?php esc_html_e( 'Nom', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<input type="text" id="afsac_nom" name="afsac_nom" required autocomplete="family-name" value="<?php echo esc_attr( $afsac_val( 'afsac_nom' ) ); ?>">
							</p>
							<p class="afsac-contact__field">
								<label for="afsac_email"><?php esc_html_e( 'E-mail', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<input type="email" id="afsac_email" name="afsac_email" required autocomplete="email" value="<?php echo esc_attr( $afsac_val( 'afsac_email' ) ); ?>">
							</p>
							<p class="afsac-contact__field">
								<label for="afsac_telephone"><?php esc_html_e( 'Téléphone', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<input type="tel" id="afsac_telephone" name="afsac_telephone" required autocomplete="tel" inputmode="tel" placeholder="+216 …" value="<?php echo esc_attr( $afsac_val( 'afsac_telephone' ) ); ?>">
							</p>
							<p class="afsac-contact__field">
								<label for="afsac_fonction"><?php esc_html_e( 'Fonction', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<input type="text" id="afsac_fonction" name="afsac_fonction" required autocomplete="organization-title" value="<?php echo esc_attr( $afsac_val( 'afsac_fonction' ) ); ?>">
							</p>
							<p class="afsac-contact__field">
								<label for="afsac_organisation"><?php esc_html_e( 'Organisation', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<input type="text" id="afsac_organisation" name="afsac_organisation" required autocomplete="organization" value="<?php echo esc_attr( $afsac_val( 'afsac_organisation' ) ); ?>">
							</p>
							<p class="afsac-contact__field afsac-contact__field--full">
								<label for="afsac_sujet"><?php esc_html_e( 'Sujet', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<select id="afsac_sujet" name="afsac_sujet" required>
									<option value=""><?php esc_html_e( 'Choisir un sujet', 'afsac' ); ?></option>
									<?php foreach ( ( function_exists( 'afsac_contact_subjects' ) ? afsac_contact_subjects() : array() ) as $afsac_sk => $afsac_slabel ) : ?>
										<option value="<?php echo esc_attr( $afsac_sk ); ?>" <?php selected( $afsac_sujet_pre, $afsac_sk ); ?>><?php echo esc_html( $afsac_slabel ); ?></option>
									<?php endforeach; ?>
								</select>
							</p>
							<p class="afsac-contact__field afsac-contact__field--full">
								<label for="afsac_message"><?php esc_html_e( 'Message', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<textarea id="afsac_message" name="afsac_message" rows="6" required><?php echo esc_textarea( $afsac_val( 'afsac_message' ) ); ?></textarea>
							</p>
						</div>

						<label class="afsac-inscription__consent afsac-contact__consent">
							<input type="checkbox" name="afsac_consent_rgpd" value="1" required <?php checked( '1', $afsac_val( 'afsac_consent_rgpd' ) ); ?>>
							<span><?php esc_html_e( 'J’accepte le traitement de mes données conformément au RGPD et à la loi n° 63-2004 relative à la protection des données personnelles.', 'afsac' ); ?> <span class="afsac-req">*</span></span>
						</label>

						<div class="afsac-contact-form__actions">
							<button type="submit" class="afsac-button"><?php esc_html_e( 'Envoyer le message', 'afsac' ); ?></button>
						</div>

						<p class="afsac-contact-reassure">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
							<?php esc_html_e( 'Réponse sous 48 h ouvrées.', 'afsac' ); ?>
						</p>
					</form>

				<?php endif; ?>
			</div>

			<?php /* Colonne coordonnées + carte. */ ?>
			<aside class="afsac-contact-aside">
				<div class="afsac-contact-info">
					<?php if ( '' !== $afsac_c( 'address' ) ) : ?>
						<div class="afsac-contact-info__item">
							<svg class="afsac-contact-info__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 21s-7-5.7-7-11a7 7 0 0 1 14 0c0 5.3-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
							<div>
								<span class="afsac-eyebrow"><?php esc_html_e( 'Adresse', 'afsac' ); ?></span>
								<p><?php echo nl2br( esc_html( $afsac_c( 'address' ) ) ); ?></p>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( '' !== $afsac_c( 'phone' ) ) : ?>
						<div class="afsac-contact-info__item">
							<svg class="afsac-contact-info__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg>
							<div>
								<span class="afsac-eyebrow"><?php esc_html_e( 'Téléphone', 'afsac' ); ?></span>
								<p><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $afsac_c( 'phone' ) ) ); ?>"><?php echo esc_html( $afsac_c( 'phone' ) ); ?></a></p>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( '' !== $afsac_c( 'email' ) ) : ?>
						<div class="afsac-contact-info__item">
							<svg class="afsac-contact-info__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
							<div>
								<span class="afsac-eyebrow"><?php esc_html_e( 'E-mail', 'afsac' ); ?></span>
								<p><a href="mailto:<?php echo esc_attr( $afsac_c( 'email' ) ); ?>"><?php echo esc_html( $afsac_c( 'email' ) ); ?></a></p>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( '' !== $afsac_c( 'hours' ) ) : ?>
						<div class="afsac-contact-info__item">
							<svg class="afsac-contact-info__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
							<div>
								<span class="afsac-eyebrow"><?php esc_html_e( 'Horaires', 'afsac' ); ?></span>
								<p><?php echo nl2br( esc_html( $afsac_c( 'hours' ) ) ); ?></p>
							</div>
						</div>
					<?php endif; ?>

					<?php
					$afsac_has_social = false;
					foreach ( array_keys( $afsac_socials ) as $afsac_sk ) {
						if ( '' !== $afsac_c( $afsac_sk ) ) {
							$afsac_has_social = true;
							break;
						}
					}
					?>
					<?php if ( $afsac_has_social ) : ?>
						<div class="afsac-contact-info__item">
							<svg class="afsac-contact-info__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="6" r="2.5"/><circle cx="18" cy="18" r="2.5"/><path d="M8.2 10.8l7.6-3.6M8.2 13.2l7.6 3.6"/></svg>
							<div>
								<span class="afsac-eyebrow"><?php esc_html_e( 'Réseaux', 'afsac' ); ?></span>
								<p class="afsac-contact-info__social">
								<?php foreach ( $afsac_socials as $afsac_sk => $afsac_slabel ) : ?>
									<?php if ( '' !== $afsac_c( $afsac_sk ) ) : ?>
										<a href="<?php echo esc_url( $afsac_c( $afsac_sk ) ); ?>" rel="noopener noreferrer" target="_blank"><?php echo esc_html( $afsac_slabel ); ?></a>
									<?php endif; ?>
								<?php endforeach; ?>
							</p>
						</div>
					</div>
					<?php endif; ?>
				</div>

				<?php if ( is_numeric( $afsac_c( 'lat' ) ) && is_numeric( $afsac_c( 'lng' ) ) ) : ?>
					<div class="afsac-contact-map" data-afsac-contact-map role="application" aria-label="<?php esc_attr_e( 'Carte de localisation AFSAC', 'afsac' ); ?>"></div>
				<?php endif; ?>
			</aside>

		</div>
	</section>

	<?php
	/*
	 * Shorts YouTube (demande client du 12/08/2026) : les vidéos verticales de la
	 * chaîne AFSAC closent la page contact, sous le formulaire et la carte. La
	 * section se retire d'elle-même si la liste du Customizer est vidée.
	 */
	get_template_part(
		'template-parts/shared/shorts',
		null,
		array(
			'lead' => __( 'Avant de nous écrire, découvrez le centre en images : coulisses des sessions, paroles d’instructeurs et repères de sûreté aérienne, en une minute.', 'afsac' ),
		)
	);
	?>

</main>

<?php
get_footer();
