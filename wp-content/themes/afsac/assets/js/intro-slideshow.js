/**
 * Diaporama de la section intro (accueil) : fondu enchaîné automatique entre les
 * photos, puces cliquables, pause au survol/focus.
 *
 * Chaque slide porte `data-block` : l'index du bloc de texte qui l'accompagne. Quand
 * la photo change, on active le paragraphe `.afsac-intro__block[data-block]` de même
 * index — plusieurs photos peuvent partager le même bloc (le texte ne bouge alors pas).
 *
 * Le défilement ne tourne que lorsque la section est à l'écran (IntersectionObserver),
 * pour qu'on arrive toujours sur la 1re photo et le 1er bloc.
 * En prefers-reduced-motion : pas de défilement automatique (la 1re photo reste, les
 * puces restent utilisables).
 */
(function () {
	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	document.querySelectorAll('[data-afsac-slideshow]').forEach(function (box) {
		var slides = Array.prototype.slice.call(box.querySelectorAll('.afsac-slideshow__slide'));
		var dots = Array.prototype.slice.call(box.querySelectorAll('.afsac-slideshow__dot'));
		if (slides.length < 2) {
			return;
		}

		// Blocs de texte pilotés par ce diaporama (dans la même section).
		var section = box.closest('.afsac-intro') || document;
		var blocks = Array.prototype.slice.call(section.querySelectorAll('.afsac-intro__blocks--rotating .afsac-intro__block'));

		var current = 0;
		var timer = null;
		var visible = true;
		var hovered = false;
		var delay = parseInt(box.getAttribute('data-delay'), 10) || 5000;

		function syncBlock(index) {
			if (!blocks.length) {
				return;
			}
			var target = slides[index].getAttribute('data-block');
			if (null === target) {
				return;
			}
			blocks.forEach(function (block) {
				var on = block.getAttribute('data-block') === target;
				block.classList.toggle('is-active', on);
				if (on) {
					block.removeAttribute('aria-hidden');
				} else {
					block.setAttribute('aria-hidden', 'true');
				}
			});
		}

		function show(n) {
			n = (n + slides.length) % slides.length;
			slides[current].classList.remove('is-active');
			if (dots[current]) {
				dots[current].classList.remove('is-active');
				dots[current].setAttribute('aria-selected', 'false');
			}
			current = n;
			slides[current].classList.add('is-active');
			if (dots[current]) {
				dots[current].classList.add('is-active');
				dots[current].setAttribute('aria-selected', 'true');
			}
			syncBlock(current);
		}

		function stop() {
			if (timer) {
				window.clearInterval(timer);
				timer = null;
			}
		}

		function start() {
			if (reduce || !visible || hovered) {
				return;
			}
			stop();
			timer = window.setInterval(function () {
				show(current + 1);
			}, delay);
		}

		dots.forEach(function (dot, index) {
			dot.addEventListener('click', function () {
				show(index);
				start();
			});
		});

		box.addEventListener('mouseenter', function () {
			hovered = true;
			stop();
		});
		box.addEventListener('mouseleave', function () {
			hovered = false;
			start();
		});
		box.addEventListener('focusin', function () {
			hovered = true;
			stop();
		});
		box.addEventListener('focusout', function () {
			hovered = false;
			start();
		});

		if ('IntersectionObserver' in window) {
			new IntersectionObserver(
				function (entries) {
					visible = entries[0].isIntersecting;
					if (visible) {
						start();
					} else {
						stop();
					}
				},
				{ threshold: 0.35 }
			).observe(box);
		}

		syncBlock(0);
		start();
	});
})();
