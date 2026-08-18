/**
 * Page inscription : time-trap (anti-bot) + brouillon localStorage. Sans dépendance.
 */
( function () {
	'use strict';
	var form = document.querySelector( '[data-afsac-inscription]' );
	if ( ! form ) { return; }

	// Time-trap : tampon de chargement (le serveur rejette les envois trop rapides).
	var ts = form.querySelector( '[data-afsac-ts]' );
	if ( ts ) { ts.value = String( Math.floor( Date.now() / 1000 ) ); }

	var KEY = 'afsac_inscription_draft';
	var fields = Array.prototype.slice.call( form.querySelectorAll( 'input, select, textarea' ) ).filter( function ( el ) {
		return el.name && el.type !== 'hidden' && el.name !== 'afsac_website';
	} );

	// Restauration du brouillon.
	try {
		var saved = JSON.parse( localStorage.getItem( KEY ) || '{}' );
		fields.forEach( function ( el ) {
			if ( ! ( el.name in saved ) ) { return; }
			if ( el.type === 'checkbox' ) { el.checked = !! saved[ el.name ]; }
			else { el.value = saved[ el.name ]; }
		} );
	} catch ( e ) {}

	var draftBtn = form.querySelector( '[data-afsac-draft]' );
	if ( draftBtn ) {
		draftBtn.addEventListener( 'click', function () {
			var data = {};
			fields.forEach( function ( el ) {
				data[ el.name ] = ( el.type === 'checkbox' ) ? ( el.checked ? 1 : 0 ) : el.value;
			} );
			try { localStorage.setItem( KEY, JSON.stringify( data ) ); } catch ( e ) {}
			draftBtn.textContent = draftBtn.getAttribute( 'data-saved' ) || 'Brouillon enregistré';
		} );
	}

	// Nettoyage du brouillon à la soumission.
	form.addEventListener( 'submit', function () {
		try { localStorage.removeItem( KEY ); } catch ( e ) {}
	} );
}() );
