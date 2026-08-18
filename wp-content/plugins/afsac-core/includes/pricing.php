<?php
/**
 * Affichage des tarifs : devise servie selon la LANGUE DE NAVIGATION.
 *
 * Le catalogue est saisi une seule fois, en USD (devise de référence de l'OACI :
 * c'est la devise des grilles TRAINAIR PLUS et celle que remonte l'import
 * tableur, cf. `afsac_devise`). Demande client (08/2026) : le visiteur
 * francophone doit lire des EUROS, l'anglophone des DOLLARS — sans dupliquer la
 * saisie, donc par CONVERSION à l'affichage.
 *
 * Conséquences assumées :
 *   - la donnée stockée reste la source de vérité (USD) ; seule la vue change ;
 *   - le taux est un paramètre éditorial, pas un cours de change temps réel :
 *     il est figé, filtrable (`afsac_currency_rates`) et surchargeable par
 *     l'option `afsac_currency_rates` sans toucher au code ;
 *   - un montant converti est ARRONDI (dizaine la plus proche) : un tarif
 *     commercial « 1 840 € » se lit mieux que « 1 839,36 € », et la précision
 *     serait de toute façon illusoire avec un taux figé.
 *
 * @package AFSAC\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Taux de change, exprimés DEPUIS l'USD (devise pivot du catalogue).
 *
 * Surcharge sans code : option `afsac_currency_rates` (tableau code => taux),
 * ou filtre `afsac_currency_rates` pour un branchement dynamique.
 *
 * @return array<string,float> Code ISO 4217 => taux depuis 1 USD.
 */
function afsac_currency_rates() {
	$rates = array(
		'USD' => 1.0,
		'EUR' => 0.92,
	);

	$stored = get_option( 'afsac_currency_rates', array() );
	if ( is_array( $stored ) ) {
		foreach ( $stored as $code => $rate ) {
			$code = strtoupper( (string) $code );
			if ( '' !== $code && is_numeric( $rate ) && (float) $rate > 0 ) {
				$rates[ $code ] = (float) $rate;
			}
		}
	}

	/**
	 * Filtre les taux de change utilisés à l'affichage.
	 *
	 * @param array<string,float> $rates Code ISO => taux depuis 1 USD.
	 */
	return (array) apply_filters( 'afsac_currency_rates', $rates );
}

/**
 * Devise à AFFICHER dans la langue de navigation courante.
 *
 * @param string $lang Slug de langue (défaut : langue courante Polylang).
 * @return string Code ISO 4217.
 */
function afsac_display_currency( $lang = '' ) {
	if ( '' === $lang ) {
		$lang = function_exists( 'pll_current_language' ) ? (string) pll_current_language() : '';
	}
	if ( '' === $lang ) {
		$lang = substr( (string) get_locale(), 0, 2 );
	}

	// FR → euro ; toute autre langue de navigation (EN, AR) → dollar.
	$currency = ( 'fr' === $lang ) ? 'EUR' : 'USD';

	/**
	 * Filtre la devise d'affichage.
	 *
	 * @param string $currency Code ISO 4217.
	 * @param string $lang     Langue de navigation.
	 */
	return (string) apply_filters( 'afsac_display_currency', $currency, $lang );
}

/**
 * Convertit un montant d'une devise vers une autre.
 *
 * @param float  $amount Montant.
 * @param string $from   Devise d'origine (code ISO).
 * @param string $to     Devise cible (code ISO).
 * @return float|null Montant converti (arrondi à la dizaine), ou null si le
 *                    couple de devises n'est pas couvert par la table de taux.
 */
function afsac_convert_amount( $amount, $from, $to ) {
	$rates = afsac_currency_rates();
	$from  = strtoupper( (string) $from );
	$to    = strtoupper( (string) $to );

	if ( $from === $to ) {
		return (float) $amount;
	}
	if ( ! isset( $rates[ $from ], $rates[ $to ] ) || 0.0 === (float) $rates[ $from ] ) {
		return null;
	}

	$usd       = (float) $amount / (float) $rates[ $from ];
	$converted = $usd * (float) $rates[ $to ];

	// Arrondi commercial : dizaine la plus proche (jamais 0 pour un tarif payant).
	$rounded = round( $converted / 10 ) * 10;
	return ( $rounded > 0 ) ? $rounded : round( $converted, 2 );
}

/**
 * Prépare un tarif pour l'affichage : montant formaté + devise de la langue courante.
 *
 * Renvoie un tableau vide quand il n'y a rien à afficher (montant absent), afin
 * que les templates testent simplement `if ( $price )`.
 *
 * @param mixed  $amount   Montant saisi (champ ACF `afsac_frais_montant`).
 * @param string $currency Devise saisie (champ ACF `afsac_devise`). Défaut USD.
 * @return array{amount:string,currency:string,value:float,converted:bool}|array{}
 */
function afsac_price_display( $amount, $currency = '' ) {
	if ( null === $amount || '' === (string) $amount || ! is_numeric( $amount ) ) {
		return array();
	}

	$from = strtoupper( (string) $currency );
	if ( '' === $from ) {
		$from = 'USD'; // Catalogue saisi en dollars quand la devise n'est pas précisée.
	}
	$to = afsac_display_currency();

	$value     = afsac_convert_amount( (float) $amount, $from, $to );
	$converted = ( null !== $value && $from !== $to );
	if ( null === $value ) {
		// Devise exotique hors table : on affiche la valeur saisie telle quelle.
		$value = (float) $amount;
		$to    = $from;
	}

	// Pas de décimales : tous les tarifs du catalogue sont des montants ronds.
	$decimals = ( floor( $value ) === $value ) ? 0 : 2;

	return array(
		'amount'    => number_format_i18n( $value, $decimals ),
		'currency'  => $to,
		'value'     => (float) $value,
		'converted' => $converted,
	);
}
