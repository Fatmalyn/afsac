/**
 * Active la ou les vidéos de fond des heros uniquement sur desktop ET hors
 * reduced-motion. La <source> est injectée à la volée (pas de chargement de la
 * vidéo sur mobile ni en préférence de mouvement réduit) : ailleurs, l'attribut
 * poster du <video> prend le relais.
 */
(function () {
	var medias = document.querySelectorAll('.afsac-video-hero__media[data-src]');
	if (
		!medias.length ||
		!window.matchMedia('(min-width:769px)').matches ||
		window.matchMedia('(prefers-reduced-motion: reduce)').matches
	) {
		return;
	}

	Array.prototype.forEach.call(medias, function (v) {
		var s = document.createElement('source');
		s.src = v.dataset.src;
		s.type = 'video/mp4';
		v.appendChild(s);
		v.load();
		v.play().catch(function () {});
	});
})();
