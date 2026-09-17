/**
 * Page contact : tampon anti-spam (time-trap).
 *
 * Horodate le formulaire à l'affichage ; le handler serveur rejette un envoi
 * intervenu moins de 3 s après (includes/contact.php). Sans JavaScript le champ
 * reste vide, et le serveur laisse alors passer : on ne bloque pas un visiteur
 * légitime, on ne freine que les robots qui postent instantanément.
 *
 * Ce fichier remplace contact-map.js, supprimé avec la carte Leaflet le
 * 02/09/2026 (la carte est passée à un <iframe> Google Maps, sans JS).
 */
( function () {
	'use strict';

	var stamp = document.querySelector( '[data-afsac-ts]' );
	if ( stamp ) {
		stamp.value = Math.floor( Date.now() / 1000 );
	}
}() );
