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
	 *
	 * Le voile (z-index 1040) recouvre la barre d'en-tête (1000), donc le
	 * hamburger n'est PAS cliquable tiroir ouvert : la fermeture passe par le
	 * bouton dédié du tiroir, le voile ou Échap.
	 *
	 * ⚠️ Le voile est inséré DANS `.afsac-mainbar`, pas dans <body> — voir le
	 * commentaire de `overlay` plus bas. Les deux valeurs de z-index (1040 pour
	 * le voile, 1050 pour le tiroir) ne se comparent que si les deux éléments
	 * partagent le MÊME contexte d'empilement.
	 *
	 * Verrouillage du défilement : `overflow: hidden` sur <body> ne suffit pas
	 * sur iOS. On fige donc le corps en `position: fixed` avec un `top` négatif,
	 * et on restaure la position exacte à la fermeture (sinon la page revient en
	 * haut, ce qui est très déroutant à mi-page).
	 */
	function setupMobileNav() {
		var toggle = document.querySelector( '.afsac-menu-toggle' );
		var nav = document.querySelector( '.afsac-primary-nav' );

		if ( ! toggle || ! nav ) {
			return;
		}

		nav.id = nav.id || 'afsac-primary-nav';

		var closeBtn = nav.querySelector( '[data-afsac-nav-close]' );
		var scrollY = 0;

		/*
		 * Voile cliquable pour fermer.
		 *
		 * Il est monté dans `.afsac-mainbar` et NON dans <body> : cette barre est
		 * `position: sticky; z-index: 1000`, donc elle ouvre un CONTEXTE
		 * D'EMPILEMENT. Le tiroir vit à l'intérieur (cf. header/nav.php) : son
		 * `z-index: 1050` ne vaut que dans ce contexte, et la barre entière reste
		 * plafonnée à 1000 face au reste de la page. Un voile posé sur <body> à
		 * 1040 passait donc PAR-DESSUS le tiroir : menu grisé, et surtout aucune
		 * entrée cliquable — chaque appui atterrissait sur le voile, qui refermait
		 * le tiroir au lieu de suivre le lien (bug relevé le 10/09/2026).
		 *
		 * Voile et tiroir désormais frères dans le même contexte : 1040 < 1050,
		 * l'ordre est respecté. Le voile reste `position: fixed`, donc il couvre
		 * bien tout l'écran (`sticky` ne crée pas de bloc conteneur pour `fixed`),
		 * et il recouvre au passage le logo et les boutons de la barre — c'est
		 * l'effet recherché.
		 */
		var overlayHost = nav.closest ? nav.closest( '.afsac-mainbar' ) : null;
		var overlay = document.createElement( 'div' );
		overlay.className = 'afsac-nav-overlay';
		( overlayHost || document.body ).appendChild( overlay );

		function open() {
			scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
			nav.classList.add( 'is-open' );
			document.body.classList.add( 'afsac-nav-open' );
			document.body.style.top = '-' + scrollY + 'px';
			toggle.setAttribute( 'aria-expanded', 'true' );
			toggle.setAttribute( 'aria-label', toggle.dataset.labelClose || toggle.getAttribute( 'aria-label' ) );
			// Le focus entre dans le tiroir : au clavier comme au lecteur d'écran,
			// la lecture reprend sur la commande de fermeture, pas derrière le voile.
			if ( closeBtn ) {
				closeBtn.focus();
			}
		}

		function close( restoreFocus ) {
			if ( ! nav.classList.contains( 'is-open' ) ) {
				return;
			}
			nav.classList.remove( 'is-open' );
			document.body.classList.remove( 'afsac-nav-open' );
			document.body.style.top = '';
			window.scrollTo( 0, scrollY );
			toggle.setAttribute( 'aria-expanded', 'false' );
			toggle.setAttribute( 'aria-label', toggle.dataset.labelOpen || toggle.getAttribute( 'aria-label' ) );
			if ( restoreFocus ) {
				toggle.focus();
			}
		}

		toggle.addEventListener( 'click', function () {
			if ( nav.classList.contains( 'is-open' ) ) {
				close( true );
			} else {
				open();
			}
		} );

		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', function () {
				close( true );
			} );
		}

		overlay.addEventListener( 'click', function () {
			close( false );
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key ) {
				close( true );
			}
		} );

		// Rotation du téléphone / passage au format tablette : au-dessus du point
		// de rupture le tiroir redevient une barre horizontale. Sans ce garde-fou,
		// le corps resterait figé en `position: fixed` — page bloquée.
		// 1151 px = jumeau du `@media (max-width: 1150px)` de style.css : les deux
		// valeurs doivent être modifiées ENSEMBLE.
		var desktop = window.matchMedia( '(min-width: 1151px)' );
		var onBreakpoint = function ( e ) {
			if ( e.matches ) {
				close( false );
			}
		};
		if ( desktop.addEventListener ) {
			desktop.addEventListener( 'change', onBreakpoint );
		} else if ( desktop.addListener ) {
			desktop.addListener( onBreakpoint ); // Safari < 14.
		}
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
