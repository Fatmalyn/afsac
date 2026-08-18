/**
 * Scène animée du hero « guidage » (F&S + Catalogue).
 *
 * Peint sur <canvas class="afsac-guide-hero__stars"> :
 *   - un champ d'étoiles qui scintille ;
 *   - une ligne d'orbite dorée avec une « lumière de vol » qui la parcourt
 *     en continu (comète dorée à traînée) ;
 *   - de rares étoiles filantes.
 *
 * Respecte prefers-reduced-motion : en mouvement réduit, une seule image
 * statique est peinte (aucune boucle). Sans JS, le hero reste lisible (dégradé
 * + halo CSS + texte).
 */
(function () {
	'use strict';

	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var canvases = document.querySelectorAll('.afsac-guide-hero__stars');
	if (!canvases.length) {
		return;
	}

	Array.prototype.forEach.call(canvases, function (cv) {
		var ctx = cv.getContext('2d');
		if (!ctx) {
			return;
		}
		var dpr = Math.min(window.devicePixelRatio || 1, 2);
		var W = 0;
		var H = 0;
		var stars = [];
		var shoots = [];
		var resizeTimer;

		function build() {
			var rect = cv.getBoundingClientRect();
			W = cv.width = Math.max(1, Math.round(rect.width * dpr));
			H = cv.height = Math.max(1, Math.round(rect.height * dpr));
			stars = [];
			var n = Math.round((rect.width * rect.height) / 9000);
			for (var i = 0; i < n; i++) {
				stars.push({
					x: Math.random() * W,
					y: Math.random() * H,
					r: (Math.random() * 1.5 + 0.3) * dpr,
					o: Math.random() * 0.55 + 0.15,
					tw: Math.random() * Math.PI * 2
				});
			}
		}

		// Point sur la courbe d'orbite (Bézier quadratique) au paramètre t ∈ [0,1].
		function orbit(t) {
			var mt = 1 - t;
			var p0x = -10 * dpr, p0y = H * 0.82;
			var p1x = W * 0.5, p1y = H * 0.32;
			var p2x = W + 10 * dpr, p2y = H * 0.42;
			return {
				x: mt * mt * p0x + 2 * mt * t * p1x + t * t * p2x,
				y: mt * mt * p0y + 2 * mt * t * p1y + t * t * p2y
			};
		}

		function drawStars(t) {
			for (var i = 0; i < stars.length; i++) {
				var s = stars[i];
				var a = reduce ? s.o : s.o * (0.6 + 0.4 * Math.sin(t / 900 + s.tw));
				ctx.beginPath();
				ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
				ctx.fillStyle = 'rgba(0,84,164,' + (a * 0.5) + ')';
				ctx.fill();
			}
		}

		function drawOrbit() {
			ctx.beginPath();
			ctx.moveTo(-10 * dpr, H * 0.82);
			ctx.quadraticCurveTo(W * 0.5, H * 0.32, W + 10 * dpr, H * 0.42);
			ctx.strokeStyle = 'rgba(0,84,164,0.16)';
			ctx.lineWidth = 1.4 * dpr;
			ctx.stroke();
		}

		// Comète dorée qui parcourt l'orbite (~9 s le tour).
		function drawFlight(t) {
			var prog = (t / 9000) % 1;
			for (var k = 11; k >= 1; k--) {
				var tt = prog - k * 0.010;
				if (tt < 0) {
					continue;
				}
				var p = orbit(tt);
				ctx.beginPath();
				ctx.arc(p.x, p.y, Math.max(0.4, (1.7 - k * 0.12)) * dpr, 0, Math.PI * 2);
				ctx.fillStyle = 'rgba(26,111,192,' + (0.05 * (12 - k)) + ')';
				ctx.fill();
			}
			var head = orbit(prog);
			var g = ctx.createRadialGradient(head.x, head.y, 0, head.x, head.y, 10 * dpr);
			g.addColorStop(0, 'rgba(0,84,164,0.55)');
			g.addColorStop(1, 'rgba(0,84,164,0)');
			ctx.beginPath();
			ctx.fillStyle = g;
			ctx.arc(head.x, head.y, 10 * dpr, 0, Math.PI * 2);
			ctx.fill();
			ctx.beginPath();
			ctx.fillStyle = 'rgba(0,84,164,0.9)';
			ctx.arc(head.x, head.y, 2 * dpr, 0, Math.PI * 2);
			ctx.fill();
		}

		function updateShoots() {
			if (shoots.length < 2 && Math.random() < 0.012) {
				shoots.push({
					x: Math.random() * W * 0.7 + W * 0.2,
					y: Math.random() * H * 0.4,
					vx: -(3 + Math.random() * 2) * dpr,
					vy: (1.4 + Math.random()) * dpr,
					life: 1
				});
			}
			for (var i = shoots.length - 1; i >= 0; i--) {
				var sh = shoots[i];
				sh.x += sh.vx;
				sh.y += sh.vy;
				sh.life -= 0.012;
				if (sh.life <= 0 || sh.x < -60 || sh.y > H + 60) {
					shoots.splice(i, 1);
					continue;
				}
				var tx = sh.x - sh.vx * 6;
				var ty = sh.y - sh.vy * 6;
				var grad = ctx.createLinearGradient(sh.x, sh.y, tx, ty);
				grad.addColorStop(0, 'rgba(0,84,164,' + (0.4 * sh.life) + ')');
				grad.addColorStop(1, 'rgba(0,84,164,0)');
				ctx.beginPath();
				ctx.strokeStyle = grad;
				ctx.lineWidth = 1.4 * dpr;
				ctx.moveTo(sh.x, sh.y);
				ctx.lineTo(tx, ty);
				ctx.stroke();
			}
		}

		function frame(t) {
			ctx.clearRect(0, 0, W, H);
			drawStars(t);
			drawOrbit();
			drawFlight(t);
			if (!reduce) {
				updateShoots();
				window.requestAnimationFrame(frame);
			}
		}

		function staticFrame() {
			ctx.clearRect(0, 0, W, H);
			drawStars(0);
			drawOrbit();
			drawFlight(28000); // comète figée à ~mi-parcours
		}

		build();
		if (reduce) {
			staticFrame();
		} else {
			window.requestAnimationFrame(frame);
		}

		window.addEventListener('resize', function () {
			window.clearTimeout(resizeTimer);
			resizeTimer = window.setTimeout(function () {
				build();
				if (reduce) {
					staticFrame();
				}
			}, 150);
		});
	});
})();
