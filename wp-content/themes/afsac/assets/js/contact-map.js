/**
 * Page contact : tampon anti-spam (time-trap) + carte Leaflet à marqueur unique.
 *
 * Le marqueur et le centre proviennent des options ACF (afsacContactMap, lat/lng/
 * label localisés). Sans clé API (OpenStreetMap), comme le calendrier. Dégrade
 * proprement sans Leaflet (le formulaire reste fonctionnel).
 */
( function () {
	'use strict';

	// Tampon time-trap : horodate le formulaire (rejet serveur si < 3 s).
	var stamp = document.querySelector( '[data-afsac-ts]' );
	if ( stamp ) {
		stamp.value = Math.floor( Date.now() / 1000 );
	}

	var el = document.querySelector( '[data-afsac-contact-map]' );
	if ( ! el || typeof window.L === 'undefined' ) {
		return;
	}

	var cfg = window.afsacContactMap || {};
	var lat = parseFloat( cfg.lat );
	var lng = parseFloat( cfg.lng );
	if ( isNaN( lat ) || isNaN( lng ) ) {
		return;
	}

	var iconBase = 'https://unpkg.com/leaflet@1.9.4/dist/images/';
	var icon = L.icon( {
		iconUrl:       iconBase + 'marker-icon.png',
		iconRetinaUrl: iconBase + 'marker-icon-2x.png',
		shadowUrl:     iconBase + 'marker-shadow.png',
		iconSize:    [ 25, 41 ],
		iconAnchor:  [ 12, 41 ],
		popupAnchor: [ 1, -34 ],
		shadowSize:  [ 41, 41 ]
	} );

	var map = L.map( el, { scrollWheelZoom: false } ).setView( [ lat, lng ], 14 );
	L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 18,
		attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
	} ).addTo( map );

	var marker = L.marker( [ lat, lng ], { icon: icon, title: cfg.label || '' } ).addTo( map );
	if ( cfg.label ) {
		marker.bindPopup( cfg.label );
	}

	// Conteneur potentiellement masqué au 1er rendu → recalcule la taille.
	window.setTimeout( function () { map.invalidateSize(); }, 200 );
}() );
