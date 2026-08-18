/**
 * Script principal du thème AFSAC.
 *
 * - Menu mobile en tiroir (toggle accessible + overlay + Échap).
 * - Panneau de recherche déroulant.
 *
 * Aucune dépendance externe.
 */
( function () {
	'use strict';

	var root = document.documentElement;
	root.classList.remove( 'no-js' );
	root.classList.add( 'js' );

	document.addEventListener( 'DOMContentLoaded', function () {
		setupMobileNav();
		setupSearchPanel();
	} );

	/**
	 * Navigation mobile : tiroir latéral avec overlay.
	 */
	function setupMobileNav() {
		var toggle = document.querySelector( '.afsac-menu-toggle' );
		var nav = document.querySelector( '.afsac-primary-nav' );

		if ( ! toggle || ! nav ) {
			return;
		}

		nav.id = nav.id || 'afsac-primary-nav';

		// Overlay cliquable pour fermer.
		var overlay = document.createElement( 'div' );
		overlay.className = 'afsac-nav-overlay';
		document.body.appendChild( overlay );

		function open() {
			nav.classList.add( 'is-open' );
			document.body.classList.add( 'afsac-nav-open' );
			toggle.setAttribute( 'aria-expanded', 'true' );
		}

		function close() {
			nav.classList.remove( 'is-open' );
			document.body.classList.remove( 'afsac-nav-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );
		}

		toggle.addEventListener( 'click', function () {
			if ( nav.classList.contains( 'is-open' ) ) {
				close();
			} else {
				open();
			}
		} );

		overlay.addEventListener( 'click', close );

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && nav.classList.contains( 'is-open' ) ) {
				close();
				toggle.focus();
			}
		} );
	}

	/**
	 * Panneau de recherche déroulant.
	 */
	function setupSearchPanel() {
		var toggle = document.querySelector( '.afsac-search-toggle' );
		var panel = document.getElementById( 'afsac-search-panel' );

		if ( ! toggle || ! panel ) {
			return;
		}

		toggle.addEventListener( 'click', function () {
			var isHidden = panel.hasAttribute( 'hidden' );

			if ( isHidden ) {
				panel.removeAttribute( 'hidden' );
				toggle.setAttribute( 'aria-expanded', 'true' );
				var field = panel.querySelector( 'input[type="search"]' );
				if ( field ) {
					field.focus();
				}
			} else {
				panel.setAttribute( 'hidden', '' );
				toggle.setAttribute( 'aria-expanded', 'false' );
			}
		} );
	}
}() );

// Sessions à venir — flèches de défilement de la bande roulante.
document.querySelectorAll('.afsac-sessions').forEach(function (section) {
  var track = section.querySelector('.afsac-sessions__track');
  if (!track) return;
  section.querySelectorAll('[data-afsac-scroll]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var card = track.querySelector('.afsac-session-card');
      var step = card ? card.offsetWidth + 24 : track.clientWidth * 0.8;
      track.scrollBy({ left: btn.dataset.afsacScroll === 'next' ? step : -step, behavior: 'smooth' });
    });
  });
});

// Guide flottant (accueil) — apparaît après défilement ; fermeture mémorisée
// pour la session (ne réapparaît plus après un clic sur la croix).
(function () {
  var guide = document.querySelector('[data-afsac-guide]');
  if (!guide) return;
  try { if (sessionStorage.getItem('afsacGuideDismissed')) { if (guide.parentNode) guide.parentNode.removeChild(guide); return; } } catch (e) {}
  var closeBtn = guide.querySelector('.afsac-guide__close');
  var shown = false;
  function onScroll() {
    var y = window.pageYOffset || document.documentElement.scrollTop || 0;
    if (!shown && y > 500) { guide.classList.add('is-visible'); shown = true; }
    else if (shown && y <= 500) { guide.classList.remove('is-visible'); shown = false; }
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
  if (closeBtn) closeBtn.addEventListener('click', function (e) {
    e.preventDefault();
    guide.classList.add('is-dismissed');
    guide.classList.remove('is-visible');
    try { sessionStorage.setItem('afsacGuideDismissed', '1'); } catch (err) {}
    window.setTimeout(function () { if (guide.parentNode) guide.parentNode.removeChild(guide); }, 350);
  });
})();
