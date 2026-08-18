<?php
/**
 * Helpers PARTAGÉS des formulaires publics (inscription + contact).
 *
 * Mutualise ce que les DEUX handlers doivent traiter de la même façon — à ce
 * jour le numéro de téléphone, ajouté aux deux formulaires (demande client du
 * 12/08/2026). Sans ce fichier, la même paire assainissement/validation serait
 * dupliquée dans includes/inscription.php et includes/contact.php.
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assainit un numéro de téléphone saisi (format international toléré).
 *
 * On NE normalise PAS vers l'E.164 : les leads viennent d'une trentaine de pays
 * et un « 00 216 71 … » réécrit de force serait plus souvent faux qu'utile. On
 * se contente d'écarter tout ce qui ne peut pas composer un numéro (lettres,
 * balises, séparateurs exotiques) et d'aplatir les espaces.
 *
 * @param string $value Valeur brute (déjà unslashée).
 * @return string Numéro nettoyé, éventuellement vide.
 */
function afsac_sanitize_phone( $value ) {
	$value = sanitize_text_field( (string) $value );
	$value = preg_replace( '/[^0-9+()\/.\s-]/u', '', $value );
	return trim( (string) preg_replace( '/\s+/', ' ', (string) $value ) );
}

/**
 * Un numéro assaini est-il plausible ?
 *
 * Bornes volontairement larges : 6 chiffres (numéros nationaux courts) à 15
 * (maximum absolu de la recommandation UIT-T E.164, indicatif compris).
 *
 * @param string $value Numéro déjà passé par afsac_sanitize_phone().
 * @return bool
 */
function afsac_is_valid_phone( $value ) {
	$digits = preg_replace( '/\D/', '', (string) $value );
	$length = strlen( (string) $digits );
	return $length >= 6 && $length <= 15;
}
