/**
 * Témoignages vidéo en « playlist » (page « Références & Témoignages »).
 *
 * Colonne gauche = scène de lecture, colonne droite = liste. Un clic sur une
 * entrée recopie ses data-* dans la scène ET lance la vidéo dans un iframe
 * (le clic est le geste utilisateur qui autorise l'autoplay). Un clic sur le
 * bouton de lecture joue le témoignage affiché.
 *
 * Les entrées de la liste et le bouton de lecture sont des LIENS vers la vidéo :
 * sans JS, ils fonctionnent toujours. On ne fait donc preventDefault() que quand
 * on prend effectivement la main.
 */
(function () {
	var section = document.querySelector('.afsac-voices');
	if (!section) return;

	var list = section.querySelector('[data-voice-list]');
	var player = section.querySelector('[data-voice-player]');
	if (!list || !player) return;

	var items = Array.prototype.slice.call(list.querySelectorAll('.afsac-voices__item'));
	if (!items.length) return;

	var poster = player.querySelector('[data-voice-poster]');
	var img = player.querySelector('[data-voice-img]');
	var monogram = player.querySelector('[data-voice-monogram]');
	var playBtn = player.querySelector('[data-voice-play]');
	var playLabel = player.querySelector('[data-voice-playlabel]');
	var elLang = player.querySelector('[data-voice-lang]');
	var elDuration = player.querySelector('[data-voice-duration]');

	var elOrg = section.querySelector('[data-voice-org]');
	var elQuote = section.querySelector('[data-voice-quote]');
	var elDesc = section.querySelector('[data-voice-desc]');
	var elAvatar = section.querySelector('[data-voice-avatar]');
	var elName = section.querySelector('[data-voice-name]');
	var elRole = section.querySelector('[data-voice-role]');

	var current = 0;
	// Gabarit du libellé accessible ("Lire le témoignage vidéo de %s"), déduit du
	// rendu serveur : on reste dans la langue de la page sans la coder en dur.
	var labelTpl = playLabel
		? playLabel.textContent.replace(items[0].getAttribute('data-name'), '%s')
		: '%s';

	/** Extrait l'identifiant d'une URL YouTube (watch, youtu.be, shorts, embed). */
	function youtubeId(url) {
		var m = url.match(/(?:youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{11})/);
		return m ? m[1] : '';
	}

	/** Construit le lecteur adapté au lien fourni. */
	function buildPlayer(url, label) {
		var yt = youtubeId(url);
		var vimeo = url.match(/vimeo\.com\/(?:video\/)?(\d+)/);
		var embed = '';

		if (yt) embed = 'https://www.youtube-nocookie.com/embed/' + yt + '?autoplay=1&rel=0';
		else if (vimeo) embed = 'https://player.vimeo.com/video/' + vimeo[1] + '?autoplay=1';

		if (!embed && /\.(mp4|webm|ogv|ogg|mov)(\?|#|$)/i.test(url)) {
			var video = document.createElement('video');
			video.src = url;
			video.controls = true;
			video.autoplay = true;
			video.playsInline = true;
			video.setAttribute('preload', 'metadata');
			return video;
		}

		var iframe = document.createElement('iframe');
		iframe.src = embed || url;
		iframe.title = label || '';
		iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
		iframe.setAttribute('allowfullscreen', '');
		iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
		return iframe;
	}

	/** Retire le lecteur en cours et remet l'affiche. */
	function resetPlayer() {
		var frame = player.querySelector('.afsac-voices__frame');
		if (frame) frame.remove();
		if (poster) poster.hidden = false;
	}

	/** Remplit un élément texte, en le masquant si la valeur est vide. */
	function fill(el, value) {
		if (!el) return;
		el.textContent = value || '';
		el.hidden = !value;
	}

	/** Applique les données de l'entrée i à la scène. */
	function render(i) {
		var d = items[i].dataset;

		if (img) {
			if (d.poster) {
				img.src = d.poster;
				img.hidden = false;
			} else {
				img.hidden = true;
			}
		}
		if (monogram) {
			monogram.textContent = d.monogram || '';
			monogram.hidden = !!d.poster;
		}

		fill(elLang, d.lang);
		fill(elDuration, d.duration);
		fill(elOrg, d.org);
		fill(elQuote, d.quote);
		fill(elDesc, d.quote ? '' : d.desc);
		if (elAvatar) elAvatar.textContent = d.initials || '';
		if (elName) elName.textContent = d.name || '';
		fill(elRole, d.role);

		if (playBtn) {
			playBtn.hidden = !d.video;
			if (d.video) playBtn.href = d.video;
		}
		if (playLabel) playLabel.textContent = labelTpl.replace('%s', d.name || '');
	}

	/** Sélectionne une entrée ; play=true lance aussi la lecture. */
	function select(i, play) {
		current = (i + items.length) % items.length;

		items.forEach(function (el, idx) {
			var on = idx === current;
			el.classList.toggle('is-active', on);
			if (on) el.setAttribute('aria-current', 'true');
			else el.removeAttribute('aria-current');
		});

		resetPlayer();
		render(current);

		if (play) start();
	}

	/** Injecte le lecteur pour le témoignage affiché. */
	function start() {
		var d = items[current].dataset;
		if (!d.video) return;

		resetPlayer();
		var frame = document.createElement('div');
		frame.className = 'afsac-voices__frame';
		frame.appendChild(buildPlayer(d.video, d.name));
		player.appendChild(frame);
		if (poster) poster.hidden = true;
	}

	items.forEach(function (el, idx) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			select(idx, true);
			// Sur mobile la liste est sous la scène : on y ramène le regard.
			if (window.matchMedia('(max-width: 900px)').matches) {
				player.scrollIntoView({ block: 'center', behavior: 'smooth' });
			}
		});
	});

	if (playBtn) {
		playBtn.addEventListener('click', function (e) {
			e.preventDefault();
			start();
		});
	}
})();
