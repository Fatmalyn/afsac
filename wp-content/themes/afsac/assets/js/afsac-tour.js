/**
 * Visite guidée (onboarding de première visite).
 *
 * Enchaîne quelques étapes qui DÉSIGNENT un élément réel de la page : un halo
 * découpé dans un voile sombre entoure la cible, une bulle explique à quoi elle
 * sert. Le visiteur avance / recule / passe la visite.
 *
 * Principes de robustesse (le thème évolue, la visite ne doit jamais casser) :
 *
 *   1. AUCUN texte ici. Étapes, libellés et cibles viennent du serveur
 *      (template-parts/shared/tour.php → JSON) : tout est traduisible.
 *   2. Chaque étape peut lister PLUSIEURS sélecteurs, par ordre de préférence :
 *      on retient le premier élément réellement VISIBLE. C'est ce qui permet de
 *      viser l'entrée de menu sur grand écran et le bouton « hamburger » sur
 *      mobile, sans dupliquer les étapes.
 *   3. Si aucune cible n'est trouvée (élément retiré du thème, page différente),
 *      l'étape ne disparaît pas : elle s'affiche au centre, comme une simple
 *      carte. Une visite ne doit jamais être un cul-de-sac.
 *   4. Le voile ne bloque pas le défilement : la bulle et le halo se
 *      repositionnent à chaque scroll / resize, donc ils restent collés à la
 *      cible quoi que fasse le visiteur.
 *
 * Le passage est MÉMORISÉ (localStorage, clé versionnée par le serveur) : la
 * visite ne se relance pas toute seule au deuxième passage, mais le lien
 * « Revoir la visite guidée » du pied de page la rejoue à volonté.
 */
(function () {
	'use strict';

	var root = document.querySelector('[data-afsac-tour]');
	if (!root) return;

	var stepsEl = root.querySelector('[data-afsac-tour-steps]');
	var steps = [];
	try {
		steps = JSON.parse(stepsEl ? stepsEl.textContent : '[]');
	} catch (e) {
		return;
	}
	if (!steps.length) return;

	var veil = root.querySelector('[data-tour-veil]');
	var spot = root.querySelector('[data-tour-spot]');
	var pop = root.querySelector('[data-tour-pop]');
	var arrow = root.querySelector('[data-tour-arrow]');
	var countEl = root.querySelector('[data-tour-count]');
	var titleEl = root.querySelector('[data-tour-title]');
	var textEl = root.querySelector('[data-tour-text]');
	var ctaEl = root.querySelector('[data-tour-cta]');
	var dotsEl = root.querySelector('[data-tour-dots]');
	var prevBtn = root.querySelector('[data-tour-prev]');
	var nextBtn = root.querySelector('[data-tour-next]');
	var skipBtn = root.querySelector('[data-tour-skip]');
	if (!veil || !spot || !pop || !nextBtn) return;

	var countTpl = root.getAttribute('data-count-tpl') || '%1$s / %2$s';
	var storeKey = root.getAttribute('data-tour-key') || 'afsac-tour';
	var reduce = false;
	try {
		reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
	} catch (e) {}

	var GAP = 14;   // distance entre la bulle et la cible.
	var EDGE = 12;  // marge minimale avec le bord de la fenêtre.

	var index = -1;
	var open = false;
	var opener = null;   // élément à qui rendre le focus en sortant.
	var target = null;   // cible de l'étape courante (null = étape centrée).
	var frame = 0;

	/* ------------------------------------------------------------------ *
	 * Mémoire du passage
	 * ------------------------------------------------------------------ */

	function seen() {
		try {
			return !!localStorage.getItem(storeKey);
		} catch (e) {
			return false;
		}
	}

	function remember() {
		try {
			localStorage.setItem(storeKey, '1');
		} catch (e) {}
	}

	/* ------------------------------------------------------------------ *
	 * Résolution des cibles
	 * ------------------------------------------------------------------ */

	/** Un élément est « visible » s'il occupe une place à l'écran. */
	function isVisible(el) {
		if (!el) return false;
		var rect = el.getBoundingClientRect();
		if (rect.width < 4 && rect.height < 4) return false;
		// Hors écran LATÉRALEMENT = pas une cible : c'est ainsi que le tiroir de
		// navigation mobile se met en retrait (translateX), tout en gardant une
		// taille. Le décalage VERTICAL, lui, est normal : la visite fait défiler.
		var vw = document.documentElement.clientWidth;
		if (rect.right <= 0 || rect.left >= vw) return false;

		var style = getComputedStyle(el);
		if ('hidden' === style.visibility) return false;
		// Un bloc « révélé au défilement » est encore à opacité 0 tant qu'il n'a
		// pas été atteint : il n'est pas caché, il attend son tour — et c'est
		// précisément la visite qui va l'amener à l'écran. L'écarter ici ferait
		// retomber l'étape sur une cible de repli plus large (la bande entière au
		// lieu de la carte).
		if (el.classList.contains('afsac-reveal') && !el.classList.contains('is-visible')) return true;
		return '0' !== style.opacity;
	}

	/**
	 * Premier élément VISIBLE parmi une liste de sélecteurs séparés par des
	 * virgules. On interroge sélecteur par sélecteur (et non d'un bloc) pour
	 * respecter l'ORDRE DE PRÉFÉRENCE : querySelectorAll, lui, rendrait l'ordre
	 * du document.
	 */
	function resolve(selectors) {
		if (!selectors) return null;
		var list = String(selectors).split(',');
		for (var i = 0; i < list.length; i++) {
			var sel = list[i].trim();
			if (!sel) continue;
			var nodes;
			try {
				nodes = document.querySelectorAll(sel);
			} catch (e) {
				continue;
			}
			for (var j = 0; j < nodes.length; j++) {
				if (isVisible(nodes[j])) return nodes[j];
			}
		}
		return null;
	}

	/* ------------------------------------------------------------------ *
	 * Placement du halo et de la bulle
	 * ------------------------------------------------------------------ */

	/** En dessous de 640 px la bulle devient un panneau collé en bas. */
	function isSheet() {
		return document.documentElement.clientWidth <= 640;
	}

	/** Repositionne halo + bulle sur la cible (ou centre la bulle sans cible). */
	function update() {
		if (!open) return;

		if (!target || !isVisible(target)) {
			root.classList.add('is-plain');
			spot.style.display = 'none';
			pop.style.top = '';
			pop.style.left = '';
			return;
		}

		root.classList.remove('is-plain');
		var raw = target.getBoundingClientRect();
		var pad = 8;
		var vhNow = document.documentElement.clientHeight;

		/*
		 * Une cible plus HAUTE que la fenêtre (une section entière, la grille de
		 * formations sur mobile…) donnerait un halo sans bords : on l'entoure
		 * alors sur sa seule partie visible. Le reste du placement travaille sur
		 * ce même rectangle, pour que la bulle reste accrochée à ce qu'on voit.
		 */
		var rect = {
			top: raw.top - pad,
			left: raw.left - pad,
			width: raw.width + pad * 2,
			height: raw.height + pad * 2
		};
		// Sur téléphone, la bulle occupe le bas de l'écran : le halo s'arrête
		// au-dessus d'elle, sinon on entourerait une zone qu'elle recouvre.
		var limitBottom = vhNow - (isSheet() ? pop.offsetHeight + 20 : 12);
		if (rect.height > limitBottom - 12) {
			var visibleTop = Math.max(rect.top, 12);
			var visibleBottom = Math.min(rect.top + rect.height, limitBottom);
			if (visibleBottom - visibleTop > 60) {
				rect.top = visibleTop;
				rect.height = visibleBottom - visibleTop;
			}
		}
		rect.bottom = rect.top + rect.height;

		spot.style.display = '';
		spot.style.top = rect.top + 'px';
		spot.style.left = rect.left + 'px';
		spot.style.width = rect.width + 'px';
		spot.style.height = rect.height + 'px';

		if (isSheet()) {
			// Panneau bas : la position est entièrement dictée par le CSS.
			pop.style.top = '';
			pop.style.left = '';
			return;
		}

		var vw = document.documentElement.clientWidth;
		var vh = document.documentElement.clientHeight;
		var pw = pop.offsetWidth;
		var ph = pop.offsetHeight;
		var pref = steps[index] && steps[index].place ? steps[index].place : 'auto';

		var roomBelow = vh - rect.bottom - GAP - EDGE;
		var roomAbove = rect.top - GAP - EDGE;
		var side;
		if ('top' === pref && roomAbove >= ph) {
			side = 'top';
		} else if ('bottom' === pref && roomBelow >= ph) {
			side = 'bottom';
		} else if (roomBelow >= ph) {
			side = 'bottom';
		} else if (roomAbove >= ph) {
			side = 'top';
		} else {
			side = roomBelow >= roomAbove ? 'bottom' : 'top';
		}

		var top = 'bottom' === side ? rect.bottom + GAP : rect.top - GAP - ph;
		top = Math.min(Math.max(EDGE, top), Math.max(EDGE, vh - ph - EDGE));
		var left = rect.left + rect.width / 2 - pw / 2;
		left = Math.min(Math.max(EDGE, left), Math.max(EDGE, vw - pw - EDGE));

		pop.style.top = Math.round(top) + 'px';
		pop.style.left = Math.round(left) + 'px';
		pop.setAttribute('data-side', side);

		if (arrow) {
			// La flèche pointe le CENTRE de la cible, sans sortir de la bulle.
			var x = rect.left + rect.width / 2 - left;
			arrow.style.left = Math.round(Math.min(Math.max(20, x), pw - 20)) + 'px';
		}
	}

	function schedule() {
		if (frame) return;
		frame = requestAnimationFrame(function () {
			frame = 0;
			update();
		});
	}

	/**
	 * Amène la cible dans une bande confortable de la fenêtre — et SEULEMENT si
	 * elle n'y est pas déjà : un en-tête collant ou un bouton du haut de page
	 * n'a aucune raison de faire sauter la page.
	 */
	function bringIntoView(el) {
		var vh = document.documentElement.clientHeight;
		var bandTop = 8;
		var bandBottom = vh - (isSheet() ? pop.offsetHeight + 28 : 8);
		var rect = el.getBoundingClientRect();

		if (rect.top >= bandTop && rect.bottom <= bandBottom) return;

		var focusY = (bandTop + bandBottom) / 2;
		var delta = rect.top + Math.min(rect.height, bandBottom - bandTop) / 2 - focusY;
		if (Math.abs(delta) < 8) return;

		if (window.scrollBy) {
			window.scrollBy({ top: delta, left: 0, behavior: reduce ? 'auto' : 'smooth' });
		} else {
			window.scrollTo(0, (window.pageYOffset || 0) + delta);
		}
	}

	/* ------------------------------------------------------------------ *
	 * Rendu d'une étape
	 * ------------------------------------------------------------------ */

	function label(btn, key) {
		var value = btn.getAttribute('data-label-' + key);
		if (value) btn.textContent = value;
	}

	function show(i) {
		index = Math.min(Math.max(0, i), steps.length - 1);
		var step = steps[index];
		target = resolve(step.target);

		if (titleEl) titleEl.textContent = step.title || '';
		if (textEl) textEl.innerHTML = step.text || '';
		if (countEl) {
			countEl.textContent = countTpl
				.replace('%1$s', String(index + 1))
				.replace('%2$s', String(steps.length));
		}

		if (ctaEl) {
			if (step.cta && step.cta.url && step.cta.label) {
				ctaEl.href = step.cta.url;
				ctaEl.textContent = step.cta.label;
				ctaEl.hidden = false;
			} else {
				ctaEl.hidden = true;
			}
		}

		// À la première étape, « Précédent » n'a rien à faire : on le retire de la
		// carte de bienvenue plutôt que de l'y laisser grisé.
		if (prevBtn) prevBtn.hidden = 0 === index;
		label(nextBtn, 0 === index ? 'start' : (index === steps.length - 1 ? 'done' : 'next'));

		if (dotsEl) {
			for (var d = 0; d < dotsEl.children.length; d++) {
				dotsEl.children[d].classList.toggle('is-active', d === index);
			}
		}

		root.classList.toggle('is-intro', !target);

		if (target) bringIntoView(target);
		update();
		// Le défilement doux étale le mouvement : on suit la cible pendant ~600 ms.
		var until = 0;
		var follow = window.setInterval(function () {
			update();
			if (++until > 20) window.clearInterval(follow);
		}, 30);

		pop.focus();
	}

	/* ------------------------------------------------------------------ *
	 * Ouverture / fermeture
	 * ------------------------------------------------------------------ */

	function start(from) {
		if (open) return;
		opener = from || null;
		open = true;
		root.hidden = false;
		document.documentElement.classList.add('afsac-tour-open');

		if (dotsEl && !dotsEl.children.length) {
			for (var i = 0; i < steps.length; i++) {
				dotsEl.appendChild(document.createElement('li'));
			}
		}

		window.addEventListener('scroll', schedule, { passive: true });
		window.addEventListener('resize', schedule);
		show(0);
	}

	function stop() {
		if (!open) return;
		open = false;
		remember();
		root.hidden = true;
		document.documentElement.classList.remove('afsac-tour-open');
		window.removeEventListener('scroll', schedule);
		window.removeEventListener('resize', schedule);
		if (opener && document.contains(opener)) opener.focus();
	}

	function go(delta) {
		var next = index + delta;
		if (next >= steps.length) {
			stop();
			return;
		}
		show(next);
	}

	/* ------------------------------------------------------------------ *
	 * Commandes
	 * ------------------------------------------------------------------ */

	nextBtn.addEventListener('click', function () { go(1); });
	if (prevBtn) prevBtn.addEventListener('click', function () { go(-1); });
	if (skipBtn) skipBtn.addEventListener('click', stop);
	root.addEventListener('click', function (e) {
		if (e.target.closest('[data-tour-close]')) stop();
	});
	// Cliquer dans le voile ferme la visite : le halo, lui, laisse voir la cible
	// mais n'est pas cliquable (pointer-events: none côté CSS).
	veil.addEventListener('click', stop);
	// Le lien de fin quitte la page : on note le passage avant de la suivre.
	if (ctaEl) ctaEl.addEventListener('click', remember);

	document.addEventListener('keydown', function (e) {
		if (!open) return;

		if ('Escape' === e.key) {
			e.preventDefault();
			stop();
			return;
		}
		if ('ArrowRight' === e.key) { go(1); return; }
		if ('ArrowLeft' === e.key) { go(-1); return; }
		if ('Tab' !== e.key) return;

		// Piège à focus : la tabulation reste dans la bulle.
		var items = Array.prototype.slice.call(
			pop.querySelectorAll('a[href], button:not([disabled])')
		).filter(function (el) { return !el.hidden && el.offsetParent !== null; });
		if (!items.length) return;
		var first = items[0];
		var last = items[items.length - 1];

		if (e.shiftKey && (document.activeElement === first || document.activeElement === pop)) {
			e.preventDefault();
			last.focus();
		} else if (!e.shiftKey && document.activeElement === last) {
			e.preventDefault();
			first.focus();
		}
	});

	/* ------------------------------------------------------------------ *
	 * Déclencheurs
	 * ------------------------------------------------------------------ */

	// « Revoir la visite guidée » : sur les autres pages c'est un vrai lien vers
	// l'accueil (?visite=1) ; ici, la visite est présente, on la joue sur place.
	Array.prototype.forEach.call(document.querySelectorAll('[data-afsac-tour-start]'), function (btn) {
		btn.addEventListener('click', function (e) {
			if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
			e.preventDefault();
			start(btn);
		});
	});

	var params = new URLSearchParams(window.location.search);
	var forced = '1' === params.get('visite') || '1' === params.get('tour');

	if (forced) {
		// Le paramètre a joué son rôle : on le retire pour qu'un F5 (ou un lien
		// partagé) ne relance pas la visite indéfiniment.
		if (window.history && window.history.replaceState) {
			params.delete('visite');
			params.delete('tour');
			var query = params.toString();
			window.history.replaceState({}, '', window.location.pathname + (query ? '?' + query : '') + window.location.hash);
		}
		start(null);
		return;
	}

	if ('1' !== root.getAttribute('data-tour-autostart') || seen()) return;
	// Arrivée sur une ancre = le visiteur vise déjà quelque chose ; et si le
	// panneau « brochure » s'ouvre au chargement, deux fenêtres se disputeraient
	// l'écran. Dans les deux cas, pas de lancement automatique.
	if (window.location.hash || document.querySelector('[data-bgate-open]')) return;

	window.setTimeout(function () {
		if (!open) start(null);
	}, parseInt(root.getAttribute('data-tour-delay'), 10) || 1400);
})();
