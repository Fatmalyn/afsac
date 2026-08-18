/**
 * Page « Liste de cours » d'un domaine : recherche plein texte, tri et
 * pagination — 100 % client. Sans dépendance, dégrade proprement sans JS
 * (toutes les lignes visibles).
 *
 * ⚠️ Demande client 18/08/2026 : « enlève tout type de filtre, laisse juste la
 * barre de recherche ». Les onglets (méthode), les chips de sous-domaines, les
 * dropdowns (localisation / langue / type) et les cases à cocher (sessions à
 * venir / virtuel / tarif réduit) ont été retirés du template ET d'ici. Les
 * attributs `data-*` des lignes (data-method, data-sub, data-location,
 * data-langs, data-session, data-virtual, data-reduced) sont TOUJOURS émis par
 * template-parts/course-row.php : il suffit de rebrancher un critère dans
 * match() pour rétablir un filtre.
 *
 * NB : adapté à un domaine (<= ~300 cours). Au-delà, passer en AJAX/serveur.
 */
( function () {
	'use strict';

	var root = document.querySelector( '.afsac-area' );
	if ( ! root ) { return; }
	var wrap = root.querySelector( '[data-area-rows]' );
	if ( ! wrap ) { return; }

	var rows     = Array.prototype.slice.call( wrap.querySelectorAll( '.afsac-course-row' ) );
	var pageSize = parseInt( wrap.getAttribute( 'data-page-size' ), 10 ) || 10;

	// Listes déroulantes de filtre. AUCUNE sur l'archive d'un domaine (client
	// 18/08/2026 : « laisse juste la barre de recherche ») ; l'archive générique
	// du CPT en a une, « Domaine », parce qu'elle couvre les 11 domaines OACI.
	var selects  = Array.prototype.slice.call( root.querySelectorAll( '[data-area-filter]' ) );
	var sortEl   = root.querySelector( '[data-area-sort]' );
	var search   = root.querySelector( '[data-area-search]' );
	var countEl  = root.querySelector( '[data-area-count]' );
	var noResult = root.querySelector( '[data-area-noresult]' );
	var pager    = root.querySelector( '[data-area-pager]' );

	var state = { area: '', q: '', sort: 'type', page: 1 };

	function match( row ) {
		if ( state.area && row.dataset.area !== state.area ) { return false; }
		if ( state.q && ( row.textContent || '' ).toLowerCase().indexOf( state.q ) === -1 ) { return false; }
		return true;
	}

	function sortRows( list ) {
		list.sort( function ( a, b ) {
			if ( state.sort === 'title' ) {
				return ( a.dataset.title || '' ).localeCompare( b.dataset.title || '' );
			}
			var ta = a.dataset.type || '', tb = b.dataset.type || '';
			if ( ta === tb ) { return ( a.dataset.title || '' ).localeCompare( b.dataset.title || '' ); }
			return ta.localeCompare( tb );
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
			b.className = 'afsac-area-pager__btn' + ( opts.active ? ' is-active' : '' ) + ( opts.nav ? ' afsac-area-pager__btn--nav' : '' );
			b.textContent = label;
			if ( opts.disabled ) {
				b.disabled = true;
			} else {
				b.addEventListener( 'click', function () { state.page = page; apply(); } );
			}
			pager.appendChild( b );
		};
		make( '‹', state.page - 1, { nav: true, disabled: state.page <= 1 } );
		var end = Math.min( pages, Math.max( 1, state.page - 2 ) + 4 );
		var start = Math.max( 1, end - 4 );
		for ( var p = start; p <= end; p++ ) { make( String( p ), p, { active: p === state.page } ); }
		make( '›', state.page + 1, { nav: true, disabled: state.page >= pages } );
	}

	function apply() {
		var filtered = sortRows( rows.filter( match ) );
		var pages = Math.max( 1, Math.ceil( filtered.length / pageSize ) );
		if ( state.page > pages ) { state.page = pages; }
		if ( state.page < 1 ) { state.page = 1; }

		rows.forEach( function ( r ) { r.hidden = true; } );
		var startI = ( state.page - 1 ) * pageSize;
		filtered.slice( startI, startI + pageSize ).forEach( function ( r ) {
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

	selects.forEach( function ( s ) {
		s.addEventListener( 'change', function () {
			state[ s.getAttribute( 'data-area-filter' ) ] = s.value;
			state.page = 1;
			apply();
		} );
	} );
	if ( sortEl ) { sortEl.addEventListener( 'change', function () { state.sort = sortEl.value; apply(); } ); }
	if ( search ) { search.addEventListener( 'input', function () { state.q = search.value.trim().toLowerCase(); state.page = 1; apply(); } ); }

	apply();
}() );
