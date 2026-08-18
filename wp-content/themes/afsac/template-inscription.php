<?php
/**
 * Template Name: Inscription participant
 *
 * Capture de lead (prospect anonyme). Panneau gauche = session + formation
 * (?session=ID / ?formation=slug en GET — un ID n'est pas une donnée perso).
 * Toutes les saisies passent en POST vers admin-post (action afsac_inscription_submit,
 * handler livré en étape 2). noindex (formulaire). Aucun système de comptes.
 *
 * @package AFSAC\Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// --- Résolution session / formation depuis l'URL (GET, IDs non personnels). ---
$afsac_session_id = isset( $_GET['session'] ) ? absint( $_GET['session'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture d'ID public, pas d'action.
$afsac_session    = $afsac_session_id ? get_post( $afsac_session_id ) : null;
$afsac_session    = ( $afsac_session instanceof WP_Post && 'afsac_session' === $afsac_session->post_type ) ? $afsac_session : null;

$afsac_formation_id = 0;
if ( $afsac_session && function_exists( 'afsac_get_session_formation_id_localized' ) ) {
	// Langue courante : la relation stocke l'ID canonique FR (sinon titre FR sur EN).
	$afsac_formation_id = afsac_get_session_formation_id_localized( $afsac_session->ID );
} elseif ( $afsac_session && function_exists( 'afsac_get_session_formation_id' ) ) {
	$afsac_formation_id = afsac_get_session_formation_id( $afsac_session->ID );
}
if ( ! $afsac_formation_id && isset( $_GET['formation'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$afsac_fp = get_page_by_path( sanitize_title( wp_unslash( $_GET['formation'] ) ), OBJECT, 'afsac_formation' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $afsac_fp ) {
		$afsac_formation_id = (int) $afsac_fp->ID;
	}
}
$afsac_formation = $afsac_formation_id ? get_post( $afsac_formation_id ) : null;

// --- Données panneau gauche. ---
$afsac_f_title = $afsac_formation ? get_the_title( $afsac_formation ) : '';
$afsac_f_link  = $afsac_formation ? get_permalink( $afsac_formation ) : '';
$afsac_f_duree = ( $afsac_formation && function_exists( 'get_field' ) ) ? (string) get_field( 'afsac_duree', $afsac_formation_id ) : '';
$afsac_f_frais = ( $afsac_formation && function_exists( 'get_field' ) ) ? get_field( 'afsac_frais_montant', $afsac_formation_id ) : '';
$afsac_f_dev   = ( $afsac_formation && function_exists( 'get_field' ) ) ? (string) get_field( 'afsac_devise', $afsac_formation_id ) : '';

$afsac_s_dates  = '';
$afsac_s_lieu   = '';
$afsac_s_hote   = '';
$afsac_s_places = null;
$afsac_s_cnom   = '';
$afsac_s_cmail  = '';
$afsac_s_lang   = '';
if ( $afsac_session ) {
	$afsac_g = function ( $k ) use ( $afsac_session ) {
		return function_exists( 'get_field' ) ? get_field( $k, $afsac_session->ID ) : get_post_meta( $afsac_session->ID, $k, true );
	};
	$afsac_s_debut  = (string) $afsac_g( 'afsac_date_debut' );
	$afsac_s_fin    = (string) $afsac_g( 'afsac_date_fin' );
	$afsac_s_dates  = function_exists( 'afsac_format_date' ) ? trim( afsac_format_date( $afsac_s_debut ) . ( $afsac_s_fin ? ' — ' . afsac_format_date( $afsac_s_fin ) : '' ) ) : '';
	$afsac_s_lieu   = (string) $afsac_g( 'afsac_lieu' );
	$afsac_s_hote   = (string) $afsac_g( 'afsac_hote' );
	$afsac_s_places = $afsac_g( 'afsac_places' );
	$afsac_s_cnom   = (string) $afsac_g( 'afsac_session_contact_nom' );
	$afsac_s_cmail  = (string) $afsac_g( 'afsac_session_contact_email' );
	$afsac_s_lt     = get_the_terms( $afsac_session->ID, 'afsac_langue' );
	$afsac_s_lang   = ( $afsac_s_lt && ! is_wp_error( $afsac_s_lt ) ) ? implode( ' / ', wp_list_pluck( $afsac_s_lt, 'name' ) ) : '';
}

/*
 * Vignette du cours — MÊME cascade que partout ailleurs (image à la une hors
 * scans de fiche, puis photo de repli déterministe, puis motif SVG du domaine).
 * Le gabarit affichait un `<span>` vide quand le cours n'avait pas d'image :
 * un pavé gris de 290×180 en haut de la colonne, sur les 57 fiches sans visuel.
 */
$afsac_f_thumb = ( $afsac_formation && function_exists( 'afsac_course_thumb_url' ) )
	? afsac_course_thumb_url( $afsac_formation_id, 'medium' )
	: '';
$afsac_f_motif = '';
$afsac_f_area  = null;
if ( $afsac_formation ) {
	$afsac_f_terms = get_the_terms( $afsac_formation_id, 'afsac_area' );
	$afsac_f_terms = ( $afsac_f_terms && ! is_wp_error( $afsac_f_terms ) ) ? $afsac_f_terms : array();
	if ( '' === $afsac_f_thumb && function_exists( 'afsac_course_motif' ) ) {
		$afsac_f_motif = afsac_course_motif( wp_list_pluck( $afsac_f_terms, 'slug' ), '', $afsac_formation_id );
	}
	$afsac_f_area = function_exists( 'afsac_course_area_domain' ) ? afsac_course_area_domain( $afsac_formation_id ) : null;
}
$afsac_f_accent = ( $afsac_f_area && function_exists( 'afsac_get_area_accent_color' ) )
	? afsac_get_area_accent_color( $afsac_f_area )
	: '';

/*
 * Sessions ouvertes du cours. Le bouton « Inscription » d'une fiche pointe vers
 * ?formation=slug SANS session : l'AFSAC recevait donc des inscriptions sans
 * date, même quand des dates existaient. On propose ici le choix, et on le dit
 * clairement quand il n'y a rien à programmer.
 */
$afsac_open_sessions = ( $afsac_formation && ! $afsac_session && function_exists( 'afsac_get_formation_sessions_for_display' ) )
	? afsac_get_formation_sessions_for_display( $afsac_formation_id )
	: array();
$afsac_no_date = ( $afsac_formation && ! $afsac_session && empty( $afsac_open_sessions ) );

// --- Dégradation : préremplissage uniquement si utilisateur WP connecté. ---
$afsac_user = is_user_logged_in() ? wp_get_current_user() : null;

// --- Erreurs / anciennes valeurs (PRG depuis l'étape 2 ; vides en étape 1). ---
$afsac_errors = array();
$afsac_old    = array();
if ( isset( $_GET['inscription'], $_GET['token'] ) && 'error' === $_GET['inscription'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$afsac_payload = get_transient( 'afsac_ins_' . sanitize_key( wp_unslash( $_GET['token'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( is_array( $afsac_payload ) ) {
		$afsac_errors = isset( $afsac_payload['errors'] ) ? (array) $afsac_payload['errors'] : array();
		$afsac_old    = isset( $afsac_payload['old'] ) ? (array) $afsac_payload['old'] : array();
	}
}
$afsac_success = ( isset( $_GET['inscription'] ) && 'ok' === $_GET['inscription'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$afsac_val = function ( $key, $fallback = '' ) use ( $afsac_old ) {
	return isset( $afsac_old[ $key ] ) ? (string) $afsac_old[ $key ] : (string) $fallback;
};
$afsac_pre_prenom = $afsac_user ? $afsac_user->first_name : '';
$afsac_pre_nom    = $afsac_user ? $afsac_user->last_name : '';
$afsac_pre_email  = $afsac_user ? $afsac_user->user_email : '';

// Listes d'options.
$afsac_civilites = array( 'M.' => __( 'M.', 'afsac' ), 'Mme' => __( 'Mme', 'afsac' ), 'Autre' => __( 'Autre', 'afsac' ) );
$afsac_ages      = array( '18-25', '26-35', '36-45', '46-55', '56+' );
$afsac_types_org = array(
	'autorite'  => __( 'Autorité / DGAC', 'afsac' ),
	'aeroport'  => __( 'Aéroport / ANSP', 'afsac' ),
	'compagnie' => __( 'Compagnie aérienne', 'afsac' ),
	'academie'  => __( 'Académie / École', 'afsac' ),
	'autre'     => __( 'Autre', 'afsac' ),
);
// Langues des supports : l'option « Arabe » a été retirée (demande client).
$afsac_langues_opt = array( 'fr' => __( 'Français', 'afsac' ), 'en' => __( 'Anglais', 'afsac' ) );
$afsac_paiements   = array(
	'bon_commande' => __( 'Bon de commande', 'afsac' ),
	'virement'     => __( 'Virement bancaire', 'afsac' ),
	'institution'  => __( 'Prise en charge institutionnelle', 'afsac' ),
	'autre'        => __( 'Autre', 'afsac' ),
);
?>

<main id="primary" class="afsac-inscription">

	<div class="afsac-inscription__subbar">
		<div class="afsac-container afsac-inscription__subbar-inner">
			<span class="afsac-inscription__subbar-prog">TRAINAIR PLUS — Electronic Management System</span>
			<span class="afsac-inscription__subbar-sep" aria-hidden="true">·</span>
			<span class="afsac-inscription__subbar-title"><?php esc_html_e( 'Inscription du participant', 'afsac' ); ?></span>
			<?php if ( $afsac_f_link ) : ?>
				<a class="afsac-inscription__subbar-back" href="<?php echo esc_url( $afsac_f_link ); ?>"><span aria-hidden="true">‹</span> <?php esc_html_e( 'Retour au cours', 'afsac' ); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<div class="afsac-container afsac-inscription__layout">

		<aside class="afsac-inscription__aside">
			<div class="afsac-inscription__course"<?php echo $afsac_f_accent ? ' style="--afsac-ins-accent: ' . esc_attr( $afsac_f_accent ) . ';"' : ''; ?>>
				<div class="afsac-inscription__course-media">
					<?php if ( '' !== $afsac_f_thumb ) : ?>
						<img class="afsac-inscription__thumb" src="<?php echo esc_url( $afsac_f_thumb ); ?>" alt="" loading="lazy" decoding="async">
					<?php elseif ( '' !== $afsac_f_motif ) : ?>
						<span class="afsac-inscription__thumb afsac-inscription__thumb--motif" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false"><?php echo $afsac_f_motif; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Motif SVG du registre interne. ?></svg>
						</span>
					<?php endif; ?>
				</div>
				<?php if ( $afsac_f_area ) : ?>
					<span class="afsac-inscription__course-area"><?php echo esc_html( $afsac_f_area->name ); ?></span>
				<?php endif; ?>
				<h1 class="afsac-inscription__course-title"><?php echo esc_html( $afsac_f_title ? $afsac_f_title : __( 'Inscription', 'afsac' ) ); ?></h1>
				<?php if ( $afsac_f_link ) : ?>
					<a class="afsac-button afsac-button--ghost afsac-inscription__details" href="<?php echo esc_url( $afsac_f_link ); ?>"><?php esc_html_e( 'Détails du cours', 'afsac' ); ?></a>
				<?php endif; ?>

				<?php if ( ! $afsac_session && ! $afsac_formation ) : ?>
						<p class="afsac-inscription__empty"><?php esc_html_e( 'Sélectionnez une session pour afficher les détails (dates, lieu, langue, places restantes).', 'afsac' ); ?></p>
					<?php endif; ?>
					<dl class="afsac-inscription__facts">
					<?php if ( '' !== $afsac_s_hote ) : ?>
						<div><dt><?php esc_html_e( 'Institution hôte', 'afsac' ); ?></dt><dd><?php echo esc_html( $afsac_s_hote ); ?></dd></div>
					<?php endif; ?>
					<?php if ( '' !== $afsac_s_dates ) : ?>
						<div><dt><?php esc_html_e( 'Dates', 'afsac' ); ?></dt><dd><?php echo esc_html( $afsac_s_dates ); ?></dd></div>
					<?php endif; ?>
					<?php if ( '' !== $afsac_s_lieu ) : ?>
						<div><dt><?php esc_html_e( 'Lieu', 'afsac' ); ?></dt><dd><?php echo esc_html( $afsac_s_lieu ); ?></dd></div>
					<?php endif; ?>
					<?php if ( '' !== $afsac_s_lang ) : ?>
						<div><dt><?php esc_html_e( 'Langue', 'afsac' ); ?></dt><dd><?php echo esc_html( $afsac_s_lang ); ?></dd></div>
					<?php endif; ?>
					<?php if ( '' !== $afsac_f_duree ) : ?>
						<div><dt><?php esc_html_e( 'Durée', 'afsac' ); ?></dt><dd><?php echo esc_html( $afsac_f_duree ); ?></dd></div>
					<?php endif; ?>
					<?php if ( '' !== (string) $afsac_s_places && null !== $afsac_s_places ) : ?>
						<div><dt><?php esc_html_e( 'Places restantes', 'afsac' ); ?></dt><dd><?php echo esc_html( number_format_i18n( (int) $afsac_s_places ) ); ?></dd></div>
					<?php endif; ?>
					<?php
					// Tarif servi dans la devise de la langue de navigation (EUR en FR, USD en EN).
					$afsac_f_pd = function_exists( 'afsac_price_display' ) ? afsac_price_display( $afsac_f_frais, $afsac_f_dev ) : array();
					?>
					<?php if ( ! empty( $afsac_f_pd ) ) : ?>
						<div><dt><?php esc_html_e( 'Frais', 'afsac' ); ?></dt><dd><?php echo esc_html( $afsac_f_pd['amount'] . ' ' . $afsac_f_pd['currency'] ); ?></dd></div>
					<?php endif; ?>
				</dl>

				<?php if ( '' !== $afsac_s_cnom || '' !== $afsac_s_cmail ) : ?>
					<div class="afsac-inscription__contact">
						<span class="afsac-eyebrow afsac-eyebrow--light"><?php esc_html_e( 'Contact', 'afsac' ); ?></span>
						<?php if ( '' !== $afsac_s_cnom ) : ?><p class="afsac-inscription__contact-name"><?php echo esc_html( $afsac_s_cnom ); ?></p><?php endif; ?>
						<?php if ( '' !== $afsac_s_cmail ) : ?><a class="afsac-inscription__contact-mail" href="mailto:<?php echo esc_attr( $afsac_s_cmail ); ?>"><?php echo esc_html( $afsac_s_cmail ); ?></a><?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php
			/*
			 * « Et ensuite ? » — TOUJOURS rendu. Le formulaire est long (5 blocs,
			 * 8 champs requis) et se terminait sur un bouton sans rien dire de la
			 * suite ; la colonne, elle, restait vide sous la carte du cours.
			 */
			?>
			<div class="afsac-inscription__next">
				<span class="afsac-eyebrow"><?php esc_html_e( 'Et ensuite ?', 'afsac' ); ?></span>
				<ol class="afsac-inscription__steps">
					<li><?php esc_html_e( 'Vous recevez un accusé de réception par e-mail, immédiatement.', 'afsac' ); ?></li>
					<li><?php esc_html_e( 'Notre équipe vérifie les pré-requis et la disponibilité de la session.', 'afsac' ); ?></li>
					<li><?php esc_html_e( 'Nous vous transmettons la confirmation, la convocation et les modalités de règlement.', 'afsac' ); ?></li>
				</ol>
				<p class="afsac-inscription__next-note"><?php esc_html_e( 'Cette demande ne vaut pas engagement de paiement : rien n’est facturé avant la confirmation écrite de l’AFSAC.', 'afsac' ); ?></p>
			</div>
		</aside>

		<div class="afsac-inscription__main">

			<?php if ( $afsac_success ) : ?>
				<div class="afsac-inscription__alert afsac-inscription__alert--ok" role="status">
					<h2><?php esc_html_e( 'Inscription reçue', 'afsac' ); ?></h2>
					<p><?php esc_html_e( 'Merci. Votre demande d’inscription a bien été enregistrée ; vous recevrez un e-mail de confirmation. Nos équipes vous recontacteront.', 'afsac' ); ?></p>
					<?php if ( $afsac_f_link ) : ?><a class="afsac-button" href="<?php echo esc_url( $afsac_f_link ); ?>"><?php esc_html_e( 'Retour au cours', 'afsac' ); ?></a><?php endif; ?>
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

				<form class="afsac-inscription__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate data-afsac-inscription>
					<input type="hidden" name="action" value="afsac_inscription_submit">
					<?php if ( empty( $afsac_open_sessions ) ) : ?>
						<input type="hidden" name="afsac_session_id" value="<?php echo esc_attr( $afsac_session_id ); ?>">
					<?php endif; ?>
					<input type="hidden" name="afsac_formation_id" value="<?php echo esc_attr( $afsac_formation_id ); ?>">
					<input type="hidden" name="afsac_ts" value="" data-afsac-ts>
					<?php wp_nonce_field( 'afsac_inscription_submit', 'afsac_ins_nonce' ); ?>
					<div class="afsac-inscription__hp" aria-hidden="true">
						<label>Website<input type="text" name="afsac_website" tabindex="-1" autocomplete="off"></label>
					</div>

					<?php
					/*
					 * CHOIX DE LA SESSION — rendu seulement si le cours a des dates
					 * ouvertes ET qu'aucune n'est arrivée par ?session=ID. Même nom de
					 * champ que le hidden (`afsac_session_id`) : le handler du plugin
					 * (includes/inscription.php) le lit déjà en absint(), rien à y changer.
					 */
					if ( ! empty( $afsac_open_sessions ) ) :
						/*
						 * Présélection : la PREMIÈRE session ouverte (la plus proche non
						 * complète). Sans elle, « Aucune de ces dates » — dont la valeur
						 * est vide côté ancien formulaire — se retrouvait cochée par
						 * défaut et l'AFSAC recevait une demande sans date alors qu'une
						 * date existait. Le choix explicite du visiteur (retour PRG après
						 * erreur) l'emporte toujours.
						 */
						$afsac_sel = $afsac_val( 'afsac_session_id' );
						if ( '' === $afsac_sel ) {
							foreach ( $afsac_open_sessions as $afsac_first ) {
								if ( 'complet' !== get_post_meta( $afsac_first->ID, 'afsac_statut', true ) ) {
									$afsac_sel = (string) $afsac_first->ID;
									break;
								}
							}
							if ( '' === $afsac_sel ) {
								$afsac_sel = '0'; // Toutes complètes : on retombe sur « Aucune de ces dates ».
							}
						}
						?>
						<fieldset class="afsac-inscription__section">
							<legend class="afsac-inscription__legend"><?php esc_html_e( 'Session souhaitée', 'afsac' ); ?></legend>
							<div class="afsac-inscription__sessions">
								<?php
								foreach ( $afsac_open_sessions as $afsac_os ) :
									$afsac_os_d1  = (string) get_post_meta( $afsac_os->ID, 'afsac_date_debut', true );
									$afsac_os_d2  = (string) get_post_meta( $afsac_os->ID, 'afsac_date_fin', true );
									$afsac_os_rng = function_exists( 'afsac_format_date' )
										? trim( afsac_format_date( $afsac_os_d1 ) . ( $afsac_os_d2 ? ' — ' . afsac_format_date( $afsac_os_d2 ) : '' ) )
										: '';
									$afsac_os_st  = (string) get_post_meta( $afsac_os->ID, 'afsac_statut', true );
									$afsac_os_full = ( 'complet' === $afsac_os_st );

									$afsac_os_meta = array();
									$afsac_os_lieu = (string) get_post_meta( $afsac_os->ID, 'afsac_lieu', true );
									if ( '' !== $afsac_os_lieu ) {
										$afsac_os_meta[] = $afsac_os_lieu;
									}
									$afsac_os_lt = get_the_terms( $afsac_os->ID, 'afsac_langue' );
									if ( $afsac_os_lt && ! is_wp_error( $afsac_os_lt ) ) {
										$afsac_os_meta[] = implode( ' / ', wp_list_pluck( $afsac_os_lt, 'name' ) );
									}
									$afsac_os_pl = get_post_meta( $afsac_os->ID, 'afsac_places', true );
									if ( ! $afsac_os_full && '' !== (string) $afsac_os_pl ) {
										/* translators: %s: nombre de places restantes. */
										$afsac_os_meta[] = sprintf( __( '%s places', 'afsac' ), number_format_i18n( (int) $afsac_os_pl ) );
									}
									?>
									<label class="afsac-inscription__session<?php echo $afsac_os_full ? ' is-full' : ''; ?>">
										<input type="radio" name="afsac_session_id" value="<?php echo esc_attr( $afsac_os->ID ); ?>" <?php checked( (string) $afsac_os->ID, $afsac_sel ); ?> <?php disabled( $afsac_os_full ); ?>>
										<span class="afsac-inscription__session-body">
											<span class="afsac-inscription__session-date"><?php echo esc_html( '' !== $afsac_os_rng ? $afsac_os_rng : get_the_title( $afsac_os ) ); ?></span>
											<?php if ( $afsac_os_meta ) : ?>
												<span class="afsac-inscription__session-meta"><?php echo esc_html( implode( ' · ', $afsac_os_meta ) ); ?></span>
											<?php endif; ?>
										</span>
										<?php if ( $afsac_os_full ) : ?>
											<span class="afsac-badge afsac-badge--full"><?php esc_html_e( 'Complet', 'afsac' ); ?></span>
										<?php endif; ?>
									</label>
								<?php endforeach; ?>

								<label class="afsac-inscription__session afsac-inscription__session--any">
									<input type="radio" name="afsac_session_id" value="0" <?php checked( '0', $afsac_sel ); ?>>
									<span class="afsac-inscription__session-body">
										<span class="afsac-inscription__session-date"><?php esc_html_e( 'Aucune de ces dates', 'afsac' ); ?></span>
										<span class="afsac-inscription__session-meta"><?php esc_html_e( 'Prévenez-moi de la prochaine session.', 'afsac' ); ?></span>
									</span>
								</label>
							</div>
						</fieldset>
					<?php elseif ( $afsac_no_date ) : ?>
						<p class="afsac-inscription__notice" role="status">
							<?php esc_html_e( 'Aucune date n’est encore programmée pour ce cours. Votre demande est enregistrée : nous vous proposerons la prochaine session ouverte, ou une session dédiée pour votre organisation.', 'afsac' ); ?>
						</p>
					<?php endif; ?>

					<p class="afsac-inscription__required">
						<span class="afsac-req">*</span> <?php esc_html_e( 'Champs obligatoires.', 'afsac' ); ?>
					</p>

					<fieldset class="afsac-inscription__section">
						<legend class="afsac-inscription__legend"><?php esc_html_e( 'Informations personnelles', 'afsac' ); ?></legend>
						<div class="afsac-inscription__grid afsac-inscription__grid--id">
							<p class="afsac-inscription__field afsac-inscription__field--s2">
								<label for="afsac_civilite"><?php esc_html_e( 'Civilité', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<select id="afsac_civilite" name="afsac_civilite" required>
									<option value=""><?php esc_html_e( 'Sélectionner', 'afsac' ); ?></option>
									<?php foreach ( $afsac_civilites as $afsac_k => $afsac_lbl ) : ?>
										<option value="<?php echo esc_attr( $afsac_k ); ?>" <?php selected( $afsac_val( 'afsac_civilite' ), $afsac_k ); ?>><?php echo esc_html( $afsac_lbl ); ?></option>
									<?php endforeach; ?>
								</select>
							</p>
							<p class="afsac-inscription__field afsac-inscription__field--s2">
								<label for="afsac_prenom"><?php esc_html_e( 'Prénom', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<input type="text" id="afsac_prenom" name="afsac_prenom" required value="<?php echo esc_attr( $afsac_val( 'afsac_prenom', $afsac_pre_prenom ) ); ?>">
							</p>
							<p class="afsac-inscription__field afsac-inscription__field--s2">
								<label for="afsac_nom"><?php esc_html_e( 'Nom', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<input type="text" id="afsac_nom" name="afsac_nom" required value="<?php echo esc_attr( $afsac_val( 'afsac_nom', $afsac_pre_nom ) ); ?>">
							</p>
							<p class="afsac-inscription__field afsac-inscription__field--s2">
								<label for="afsac_email"><?php esc_html_e( 'E-mail', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<input type="email" id="afsac_email" name="afsac_email" required autocomplete="email" value="<?php echo esc_attr( $afsac_val( 'afsac_email', $afsac_pre_email ) ); ?>">
								<span class="afsac-inscription__field-help"><?php esc_html_e( 'Une confirmation vous sera envoyée à cette adresse.', 'afsac' ); ?></span>
							</p>
							<p class="afsac-inscription__field afsac-inscription__field--s2">
								<label for="afsac_telephone"><?php esc_html_e( 'Téléphone', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<input type="tel" id="afsac_telephone" name="afsac_telephone" required autocomplete="tel" inputmode="tel" placeholder="+216 …" value="<?php echo esc_attr( $afsac_val( 'afsac_telephone' ) ); ?>">
								<span class="afsac-inscription__field-help"><?php esc_html_e( 'Indicatif pays compris, pour vous joindre avant la session.', 'afsac' ); ?></span>
							</p>
							<p class="afsac-inscription__field afsac-inscription__field--s2">
								<label for="afsac_age"><?php esc_html_e( 'Tranche d’âge', 'afsac' ); ?></label>
								<select id="afsac_age" name="afsac_age">
									<option value=""><?php esc_html_e( 'Non précisé', 'afsac' ); ?></option>
									<?php foreach ( $afsac_ages as $afsac_a ) : ?>
										<option value="<?php echo esc_attr( $afsac_a ); ?>" <?php selected( $afsac_val( 'afsac_age' ), $afsac_a ); ?>><?php echo esc_html( $afsac_a ); ?></option>
									<?php endforeach; ?>
								</select>
							</p>
						</div>
					</fieldset>

					<fieldset class="afsac-inscription__section">
						<legend class="afsac-inscription__legend"><?php esc_html_e( 'Informations professionnelles', 'afsac' ); ?></legend>
						<div class="afsac-inscription__grid">
							<p class="afsac-inscription__field">
								<label for="afsac_fonction"><?php esc_html_e( 'Fonction', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<input type="text" id="afsac_fonction" name="afsac_fonction" required value="<?php echo esc_attr( $afsac_val( 'afsac_fonction' ) ); ?>">
							</p>
							<p class="afsac-inscription__field">
								<label for="afsac_organisation"><?php esc_html_e( 'Organisation', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<input type="text" id="afsac_organisation" name="afsac_organisation" required value="<?php echo esc_attr( $afsac_val( 'afsac_organisation' ) ); ?>">
							</p>
							<p class="afsac-inscription__field">
								<label for="afsac_type_org"><?php esc_html_e( 'Type d’organisation', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<select id="afsac_type_org" name="afsac_type_org" required>
									<option value=""><?php esc_html_e( 'Sélectionner', 'afsac' ); ?></option>
									<?php foreach ( $afsac_types_org as $afsac_k => $afsac_lbl ) : ?>
										<option value="<?php echo esc_attr( $afsac_k ); ?>" <?php selected( $afsac_val( 'afsac_type_org' ), $afsac_k ); ?>><?php echo esc_html( $afsac_lbl ); ?></option>
									<?php endforeach; ?>
								</select>
							</p>
							<p class="afsac-inscription__field">
								<label for="afsac_pays"><?php esc_html_e( 'Pays', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<input type="text" id="afsac_pays" name="afsac_pays" required value="<?php echo esc_attr( $afsac_val( 'afsac_pays' ) ); ?>">
							</p>
						</div>
					</fieldset>

					<fieldset class="afsac-inscription__section">
						<legend class="afsac-inscription__legend"><?php esc_html_e( 'Préférences pédagogiques', 'afsac' ); ?></legend>
						<div class="afsac-inscription__grid">
							<p class="afsac-inscription__field">
								<label for="afsac_langue_supports"><?php esc_html_e( 'Langue préférée des supports', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<select id="afsac_langue_supports" name="afsac_langue_supports" required>
									<option value=""><?php esc_html_e( 'Sélectionner', 'afsac' ); ?></option>
									<?php foreach ( $afsac_langues_opt as $afsac_k => $afsac_lbl ) : ?>
										<option value="<?php echo esc_attr( $afsac_k ); ?>" <?php selected( $afsac_val( 'afsac_langue_supports' ), $afsac_k ); ?>><?php echo esc_html( $afsac_lbl ); ?></option>
									<?php endforeach; ?>
								</select>
							</p>
							<p class="afsac-inscription__field">
								<label for="afsac_regime"><?php esc_html_e( 'Régime alimentaire', 'afsac' ); ?></label>
								<input type="text" id="afsac_regime" name="afsac_regime" value="<?php echo esc_attr( $afsac_val( 'afsac_regime' ) ); ?>">
							</p>
							<p class="afsac-inscription__field afsac-inscription__field--full">
								<label for="afsac_accessibilite"><?php esc_html_e( 'Besoins d’accessibilité', 'afsac' ); ?></label>
								<input type="text" id="afsac_accessibilite" name="afsac_accessibilite" value="<?php echo esc_attr( $afsac_val( 'afsac_accessibilite' ) ); ?>">
							</p>
						</div>
					</fieldset>

					<fieldset class="afsac-inscription__section">
						<legend class="afsac-inscription__legend"><?php esc_html_e( 'Mode de paiement', 'afsac' ); ?></legend>
						<div class="afsac-inscription__grid">
							<p class="afsac-inscription__field">
								<label for="afsac_paiement"><?php esc_html_e( 'Mode de règlement', 'afsac' ); ?> <span class="afsac-req">*</span></label>
								<select id="afsac_paiement" name="afsac_paiement" required>
									<option value=""><?php esc_html_e( 'Sélectionner', 'afsac' ); ?></option>
									<?php foreach ( $afsac_paiements as $afsac_k => $afsac_lbl ) : ?>
										<option value="<?php echo esc_attr( $afsac_k ); ?>" <?php selected( $afsac_val( 'afsac_paiement' ), $afsac_k ); ?>><?php echo esc_html( $afsac_lbl ); ?></option>
									<?php endforeach; ?>
								</select>
							</p>
							<p class="afsac-inscription__field">
								<label for="afsac_bon_commande"><?php esc_html_e( 'N° de bon de commande', 'afsac' ); ?></label>
								<input type="text" id="afsac_bon_commande" name="afsac_bon_commande" value="<?php echo esc_attr( $afsac_val( 'afsac_bon_commande' ) ); ?>">
							</p>
						</div>
					</fieldset>

					<fieldset class="afsac-inscription__section afsac-inscription__consents">
						<label class="afsac-inscription__consent">
							<input type="checkbox" name="afsac_consent_exactitude" value="1" required <?php checked( '1', $afsac_val( 'afsac_consent_exactitude' ) ); ?>>
							<span><?php esc_html_e( 'Je certifie l’exactitude des informations fournies et j’accepte les conditions générales de l’AFSAC.', 'afsac' ); ?> <span class="afsac-req">*</span></span>
						</label>
						<label class="afsac-inscription__consent">
							<input type="checkbox" name="afsac_consent_rgpd" value="1" required <?php checked( '1', $afsac_val( 'afsac_consent_rgpd' ) ); ?>>
							<span><?php esc_html_e( 'J’accepte le traitement de mes données conformément au RGPD et à la loi n° 63-2004 relative à la protection des données personnelles.', 'afsac' ); ?> <span class="afsac-req">*</span></span>
						</label>
					</fieldset>

					<div class="afsac-inscription__actions">
						<?php if ( $afsac_f_link ) : ?><a class="afsac-button afsac-button--ghost" href="<?php echo esc_url( $afsac_f_link ); ?>"><?php esc_html_e( 'Annuler', 'afsac' ); ?></a><?php endif; ?>
						<button type="button" class="afsac-button afsac-button--invert" data-afsac-draft data-saved="<?php esc_attr_e( 'Brouillon enregistré ✓', 'afsac' ); ?>"><?php esc_html_e( 'Enregistrer le brouillon', 'afsac' ); ?></button>
						<button type="submit" class="afsac-button"><?php esc_html_e( 'Soumettre l’inscription', 'afsac' ); ?></button>
					</div>
				</form>

			<?php endif; ?>
		</div>

	</div>

</main>

<?php
get_footer();
