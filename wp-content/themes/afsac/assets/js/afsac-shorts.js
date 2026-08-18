/**
 * Shorts YouTube (section .afsac-shorts) — rail défilant + lightbox de lecture.
 *
 * Deux comportements, tous deux facultatifs (le rendu serveur fonctionne seul) :
 *   1. les flèches font défiler le rail d'une « page » de cartes ;
 *   2. un clic sur une carte ouvre le short dans une lightbox 9/16, au lieu de
 *      quitter le site pour YouTube. L'iframe n'est créée qu'à ce moment-là :
 *      tant que le visiteur n'a rien demandé, aucune requête ne part vers
 *      YouTube (poids de page + vie privée).
 *
 * Les cartes RESTENT des liens : sans JS (ou si ce script casse), elles ouvrent
 * la vidéo sur youtube.com. On ne fait donc preventDefault() qu'une fois la
 * lightbox effectivement prise en charge.
 */
(function () {
	'use strict';

	var section = document.querySelector('[data-afsac-shorts]');
	if (!section) return;

	var track = section.querySelector('[data-afsac-shorts-track]');
	var cards = Array.prototype.slice.call(section.querySelectorAll('[data-afsac-short]'));
	if (!track || !cards.length) return;

	var i18nEl = section.querySelector('[data-afsac-shorts-i18n]');
	var i18n = {
		close: (i18nEl && i18nEl.getAttribute('data-close')) || 'Close',
		prev: (i18nEl && i18nEl.getAttribute('data-prev')) || 'Previous',
		next: (i18nEl && i18nEl.getAttribute('data-next')) || 'Next',
		watch: (i18nEl && i18nEl.getAttribute('data-watch')) || 'YouTube'
	};

	/* ------------------------------------------------------------------ *
	 * 1. Rail : flèches + état (désactivées en butée, masquées sans débord)
	 * ------------------------------------------------------------------ */

	var prevBtn = section.querySelector('[data-afsac-shorts-prev]');
	var nextBtn = section.querySelector('[data-afsac-shorts-next]');

	/** Largeur d'un saut : la largeur visible, arrondie à un nombre de cartes. */
	function step() {
		var card = cards[0].parentNode; // <li>
		var w = card.getBoundingClientRect().width + 16; // + la gouttière
		var visible = Math.max(1, Math.floor(track.clientWidth / w));
		return w * visible;
	}

	/** Reflète l'état du défilement sur les flèches (et les masque si inutiles). */
	function syncArrows() {
		if (!prevBtn || !nextBtn) return;
		var max = track.scrollWidth - track.clientWidth;
		var overflows = max > 4;
		section.classList.toggle('is-scrollable', overflows);
		// scrollLeft est NÉGATIF en RTL (Chrome/Firefox modernes) : on compare des
		// distances absolues pour que les butées soient justes dans les deux sens.
		var pos = Math.abs(track.scrollLeft);
		prevBtn.disabled = !overflows || pos <= 4;
		nextBtn.disabled = !overflows || pos >= max - 4;
	}

	function scrollBy(direction) {
		var rtl = getComputedStyle(track).direction === 'rtl';
		var delta = step() * direction * (rtl ? -1 : 1);
		if (typeof track.scrollBy === 'function') track.scrollBy({ left: delta, behavior: 'smooth' });
		else track.scrollLeft += delta;
	}

	if (prevBtn) prevBtn.addEventListener('click', function () { scrollBy(-1); });
	if (nextBtn) nextBtn.addEventListener('click', function () { scrollBy(1); });
	track.addEventListener('scroll', syncArrows, { passive: true });
	window.addEventListener('resize', syncArrows);
	syncArrows();

	/* ------------------------------------------------------------------ *
	 * 2. Lightbox de lecture
	 * ------------------------------------------------------------------ */

	var box = null;      // conteneur de la lightbox (créé à la première ouverture)
	var frame = null;    // hôte de l'iframe
	var titleEl = null;
	var watchEl = null;
	var current = 0;
	var opener = null;   // carte d'où l'on vient, pour rendre le focus

	/** Construit la lightbox une seule fois, puis la réutilise. */
	function build() {
		box = document.createElement('div');
		box.className = 'afsac-shorts-modal';
		box.setAttribute('role', 'dialog');
		box.setAttribute('aria-modal', 'true');
		box.hidden = true;

		box.innerHTML =
			'<div class="afsac-shorts-modal__backdrop" data-close></div>' +
			'<div class="afsac-shorts-modal__inner">' +
				'<button type="button" class="afsac-shorts-modal__btn afsac-shorts-modal__btn--close" data-close>' +
					'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>' +
					'<span class="screen-reader-text"></span>' +
				'</button>' +
				'<button type="button" class="afsac-shorts-modal__btn afsac-shorts-modal__btn--prev" data-prev>' +
					'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>' +
					'<span class="screen-reader-text"></span>' +
				'</button>' +
				'<div class="afsac-shorts-modal__stage"><div class="afsac-shorts-modal__frame" data-frame></div></div>' +
				'<button type="button" class="afsac-shorts-modal__btn afsac-shorts-modal__btn--next" data-next>' +
					'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>' +
					'<span class="screen-reader-text"></span>' +
				'</button>' +
				'<p class="afsac-shorts-modal__caption"><span data-title></span> <a class="afsac-shorts-modal__watch" target="_blank" rel="noopener noreferrer"></a></p>' +
			'</div>';

		frame = box.querySelector('[data-frame]');
		titleEl = box.querySelector('[data-title]');
		watchEl = box.querySelector('.afsac-shorts-modal__watch');

		box.querySelector('.afsac-shorts-modal__btn--close .screen-reader-text').textContent = i18n.close;
		box.querySelector('.afsac-shorts-modal__btn--prev .screen-reader-text').textContent = i18n.prev;
		box.querySelector('.afsac-shorts-modal__btn--next .screen-reader-text').textContent = i18n.next;
		watchEl.textContent = i18n.watch;

		box.addEventListener('click', function (e) {
			if (e.target.closest('[data-close]')) { close(); return; }
			if (e.target.closest('[data-prev]')) { show(current - 1); return; }
			if (e.target.closest('[data-next]')) { show(current + 1); }
		});

		document.body.appendChild(box);
	}

	/** Affiche le short d'indice i (bouclé), en (re)créant l'iframe. */
	function show(i) {
		current = (i + cards.length) % cards.length;
		var card = cards[current];
		var id = card.getAttribute('data-id');
		var title = card.getAttribute('data-title') || '';

		frame.innerHTML = '';
		var iframe = document.createElement('iframe');
		iframe.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(id) +
			'?autoplay=1&rel=0&playsinline=1&modestbranding=1';
		iframe.title = title;
		iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
		iframe.setAttribute('allowfullscreen', '');
		iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
		frame.appendChild(iframe);

		titleEl.textContent = title;
		watchEl.href = card.href;
		box.setAttribute('aria-label', title);
	}

	function open(i, from) {
		if (!box) build();
		opener = from || null;
		box.hidden = false;
		document.documentElement.classList.add('afsac-shorts-open');
		show(i);
		var closeBtn = box.querySelector('.afsac-shorts-modal__btn--close');
		if (closeBtn) closeBtn.focus();
	}

	function close() {
		if (!box || box.hidden) return;
		frame.innerHTML = ''; // coupe le son : on détruit l'iframe, pas juste l'affichage.
		box.hidden = true;
		document.documentElement.classList.remove('afsac-shorts-open');
		if (opener) opener.focus();
	}

	document.addEventListener('keydown', function (e) {
		if (!box || box.hidden) return;
		if (e.key === 'Escape') { close(); }
		else if (e.key === 'ArrowLeft') { show(current - 1); }
		else if (e.key === 'ArrowRight') { show(current + 1); }
	});

	cards.forEach(function (card, i) {
		card.addEventListener('click', function (e) {
			// Ctrl/⌘/clic milieu : on laisse le navigateur ouvrir YouTube.
			if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
			e.preventDefault();
			open(i, card);
		});
	});
})();
