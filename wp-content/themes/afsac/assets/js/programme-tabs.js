/**
 * Catalogue — sélecteur de PROGRAMME (TRAINAIR PLUS / AVSEC) en onglets.
 *
 * Pourquoi : empilés verticalement, les visiteurs voyaient TRAINAIR PLUS et
 * n'atteignaient jamais AVSEC. Les deux programmes sont désormais deux onglets
 * à poids égal, sans scroll pour passer de l'un à l'autre.
 *
 * Progressive enhancement : le serveur rend les DEUX panneaux visibles. C'est ce
 * script qui masque l'inactif — sans JS, tout le contenu reste accessible (et
 * indexable). Motif ARIA « tabs » : rôles, aria-selected, roving tabindex,
 * flèches ← →, Origine/Fin. Lien profond via #trainair / #avsec.
 */
( function () {
	'use strict';

	var root = document.querySelector( '[data-prog-tabs]' );
	if ( ! root ) { return; }

	var tabs = Array.prototype.slice.call( root.querySelectorAll( '[data-prog-tab]' ) );
	var panels = Array.prototype.slice.call( document.querySelectorAll( '[data-prog-panel]' ) );
	if ( ! tabs.length || ! panels.length ) { return; }

	function keyOf( el ) {
		return el.getAttribute( 'data-prog-tab' ) || el.getAttribute( 'data-prog-panel' ) || '';
	}

	// Dès que l'utilisateur a compris qu'il peut basculer, on coupe l'appel visuel
	// (pulsation du filet de l'onglet inactif). Voir .is-touched dans style.css.
	function touched() {
		root.classList.add( 'is-touched' );
	}
	root.addEventListener( 'pointerenter', touched, { once: true } );
	root.addEventListener( 'focusin', touched, { once: true } );

	function activate( key, focusTab ) {
		var found = false;

		tabs.forEach( function ( t ) {
			var on = keyOf( t ) === key;
			if ( on ) { found = true; }
			t.classList.toggle( 'is-active', on );
			t.setAttribute( 'aria-selected', on ? 'true' : 'false' );
			t.tabIndex = on ? 0 : -1;
			if ( on && focusTab ) { t.focus(); }
		} );
		if ( ! found ) { return false; }

		panels.forEach( function ( p ) {
			p.hidden = keyOf( p ) !== key;
		} );

		return true;
	}

	tabs.forEach( function ( t, i ) {
		t.addEventListener( 'click', function () {
			touched();
			var key = keyOf( t );
			if ( ! activate( key, false ) ) { return; }
			// Met à jour l'URL sans provoquer de saut de scroll.
			if ( window.history && window.history.replaceState ) {
				window.history.replaceState( null, '', '#' + key );
			}
		} );

		t.addEventListener( 'keydown', function ( e ) {
			var next = null;
			if ( e.key === 'ArrowRight' || e.key === 'ArrowDown' ) { next = tabs[ ( i + 1 ) % tabs.length ]; }
			else if ( e.key === 'ArrowLeft' || e.key === 'ArrowUp' ) { next = tabs[ ( i - 1 + tabs.length ) % tabs.length ]; }
			else if ( e.key === 'Home' ) { next = tabs[ 0 ]; }
			else if ( e.key === 'End' ) { next = tabs[ tabs.length - 1 ]; }
			if ( ! next ) { return; }
			e.preventDefault();
			activate( keyOf( next ), true );
		} );
	} );

	// État initial : ancre (#avsec) si elle correspond à un onglet, sinon le premier.
	var initial = ( window.location.hash || '' ).replace( '#', '' );
	if ( ! initial || ! activate( initial, false ) ) {
		activate( keyOf( tabs[ 0 ] ), false );
	}

	// Permet aux liens internes « …/catalogue/#avsec » de fonctionner à chaud.
	window.addEventListener( 'hashchange', function () {
		var key = ( window.location.hash || '' ).replace( '#', '' );
		if ( key ) { activate( key, false ); }
	} );
}() );
