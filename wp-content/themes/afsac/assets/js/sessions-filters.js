/**
 * Page « Calendrier des sessions » : onglets de PROGRAMME (TRAINAIR PLUS /
 * AVSEC), chips de type, dropdowns (domaine / langue), recherche et pagination
 * — 100 % client.
 *
 * Refonte (demande client) : plus de filtre localisation / institution hôte
 * (un seul lieu), plus de plage de dates « Du / Au », plus de case « tarif
 * réduit », plus de vue carte. Puis, 08/2026 : les onglets passent du MODE
 * (présentiel / virtuel) au PROGRAMME, et le TRI disparaît — un calendrier se
 * lit dans l'ordre du temps, l'ordre est donc toujours chronologique et les
 * en-têtes de mois toujours pertinents.
 *
 * Sans dépendance, dégrade proprement sans JS (toutes les cartes visibles).
 */
( function () {
	'use strict';

	var root = document.querySelector( '.afsac-calendar' );
	if ( ! root ) { return; }
	var wrap = root.querySelector( '[data-cal-rows]' );
	if ( ! wrap ) { return; }

	var rows     = Array.prototype.slice.call( wrap.querySelectorAll( '[data-cal-row]' ) );
	var pageSize = parseInt( wrap.getAttribute( 'data-page-size' ), 10 ) || 10;

	var tabs     = Array.prototype.slice.call( root.querySelectorAll( '[data-cal-tab]' ) );
	var chips    = Array.prototype.slice.call( root.querySelectorAll( '[data-cal-chip]' ) );
	var selects  = Array.prototype.slice.call( root.querySelectorAll( '[data-cal-filter]' ) );
	var search   = root.querySelector( '[data-cal-search]' );
	var countEl  = root.querySelector( '[data-cal-count]' );
	var noResult = root.querySelector( '[data-cal-noresult]' );
	var pager    = root.querySelector( '[data-cal-pager]' );

	var state = { prog: 'all', type: '', area: '', lang: '', q: '', page: 1 };

	function tokens( el, attr ) {
		return ( el.getAttribute( attr ) || '' ).split( /\s+/ ).filter( Boolean );
	}

	function match( row ) {
		var d = row.dataset;
		if ( state.prog !== 'all' && d.prog !== state.prog ) { return false; }
		if ( state.type && d.type !== state.type ) { return false; }
		if ( state.area && tokens( row, 'data-area' ).indexOf( state.area ) === -1 ) { return false; }
		if ( state.lang && tokens( row, 'data-lang' ).indexOf( state.lang ) === -1 ) { return false; }
		if ( state.q && ( row.textContent || '' ).toLowerCase().indexOf( state.q ) === -1 ) { return false; }
		return true;
	}

	// Ordre CHRONOLOGIQUE, toujours (le tri alphabétique a été retiré) ; à date
	// égale on départage par titre pour que l'ordre reste stable d'un rendu à l'autre.
	function sortRows( list ) {
		list.sort( function ( a, b ) {
			var da = parseInt( a.dataset.date || '0', 10 ) || 0;
			var db = parseInt( b.dataset.date || '0', 10 ) || 0;
			if ( da === db ) { return ( a.dataset.title || '' ).localeCompare( b.dataset.title || '' ); }
			return da - db;
		} );
		return list;
	}

	function renderPager( pages ) {
		if ( ! pager ) { return; }
		pager.innerHTML = '';
		if ( pages <= 1 ) { return; }
		var make = function ( label, page, opts ) {
			opts = opts || {};
			var b = document.createElement( 'button' );
			b.type = 'button';
			b.className = 'afsac-cal-page' + ( opts.active ? ' is-active' : '' ) + ( opts.nav ? ' afsac-cal-page--nav' : '' );
			b.textContent = label;
			if ( opts.disabled ) {
				b.disabled = true;
			} else {
				b.addEventListener( 'click', function () { state.page = page; apply(); root.scrollIntoView( { behavior: 'smooth', block: 'start' } ); } );
			}
			pager.appendChild( b );
		};
		make( '‹', state.page - 1, { nav: true, disabled: state.page <= 1 } );
		var end = Math.min( pages, Math.max( 1, state.page - 2 ) + 4 );
		var start = Math.max( 1, end - 4 );
		for ( var p = start; p <= end; p++ ) { make( String( p ), p, { active: p === state.page } ); }
		make( '›', state.page + 1, { nav: true, disabled: state.page >= pages } );
	}

	// Compte les sessions par mois sur l'ENSEMBLE filtré (pas seulement la page).
	function monthCounts( list ) {
		var counts = {};
		list.forEach( function ( r ) {
			var k = r.dataset.month || '';
			counts[ k ] = ( counts[ k ] || 0 ) + 1;
		} );
		return counts;
	}

	// `first` : le premier en-tête rendu ne prend pas de marge haute. Un simple
	// `:first-child` en CSS ne marcherait pas — les cartes masquées restent en
	// tête du conteneur, donc l'en-tête n'est jamais le premier enfant du DOM.
	function makeMonthHeader( label, count, first ) {
		var h = document.createElement( 'div' );
		h.className = 'afsac-cal-month' + ( first ? ' is-first' : '' );
		h.setAttribute( 'data-cal-month-head', '' );
		var t = document.createElement( 'h2' );
		t.className = 'afsac-cal-month__title';
		t.textContent = label;
		var c = document.createElement( 'span' );
		c.className = 'afsac-cal-month__count';
		c.textContent = count;
		h.appendChild( t );
		h.appendChild( c );
		return h;
	}

	function apply() {
		var filtered = sortRows( rows.filter( match ) );
		var pages = Math.max( 1, Math.ceil( filtered.length / pageSize ) );
		if ( state.page > pages ) { state.page = pages; }
		if ( state.page < 1 ) { state.page = 1; }

		// Purge les en-têtes de mois de la passe précédente.
		Array.prototype.slice.call( wrap.querySelectorAll( '[data-cal-month-head]' ) )
			.forEach( function ( h ) { h.parentNode.removeChild( h ); } );

		rows.forEach( function ( r ) { r.hidden = true; } );

		var startI = ( state.page - 1 ) * pageSize;
		var page = filtered.slice( startI, startI + pageSize );
		var counts = monthCounts( filtered );
		var lastMonth = null;

		page.forEach( function ( r ) {
			var m = r.dataset.month || '';
			if ( m !== lastMonth ) {
				wrap.appendChild( makeMonthHeader( r.dataset.monthLabel || '', counts[ m ] || 0, lastMonth === null ) );
				lastMonth = m;
			}
			r.hidden = false;
			wrap.appendChild( r );
		} );

		if ( countEl ) {
			var unit = ( filtered.length === 1 )
				? ( countEl.getAttribute( 'data-singular' ) || '' )
				: ( countEl.getAttribute( 'data-plural' ) || '' );
			countEl.textContent = filtered.length + ' ' + unit;
		}
		if ( noResult ) { noResult.hidden = filtered.length !== 0; }
		renderPager( pages );
	}

	function resetPage() { state.page = 1; }

	tabs.forEach( function ( t ) {
		t.addEventListener( 'click', function () {
			tabs.forEach( function ( x ) { x.classList.remove( 'is-active' ); x.removeAttribute( 'aria-current' ); } );
			t.classList.add( 'is-active' );
			t.setAttribute( 'aria-current', 'true' );
			state.prog = t.getAttribute( 'data-cal-tab' );
			resetPage(); apply();
		} );
	} );
	chips.forEach( function ( c ) {
		c.addEventListener( 'click', function () {
			chips.forEach( function ( x ) { x.classList.remove( 'afsac-pill--active' ); } );
			c.classList.add( 'afsac-pill--active' );
			state.type = c.getAttribute( 'data-cal-chip' );
			resetPage(); apply();
		} );
	} );
	selects.forEach( function ( s ) {
		s.addEventListener( 'change', function () {
			state[ s.getAttribute( 'data-cal-filter' ) ] = s.value;
			resetPage(); apply();
		} );
	} );
	if ( search ) { search.addEventListener( 'input', function () { state.q = search.value.trim().toLowerCase(); resetPage(); apply(); } ); }

	apply();
}() );
