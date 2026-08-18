/**
 * Brochure sous formulaire — passage du panneau en fenêtre modale.
 *
 * Le balisage est rendu par le serveur (template-parts/shared/brochure-gate) et
 * fonctionne SEUL : sans ce script, la carte du pied de page est une ancre qui
 * mène au formulaire, le formulaire est un POST classique, et le téléchargement
 * se fait par le bouton de l'écran de remerciement. Ce script ne fait
 * qu'améliorer l'expérience :
 *
 *   1. il sort le panneau dans <body> — sans quoi l'`overflow: hidden` de la
 *      bande Documentation pourrait rogner la fenêtre ;
 *   2. il ouvre / ferme la modale (clic sur la carte, Échap, clic sur le fond)
 *      en piégeant le focus, et le rend à son point de départ ;
 *   3. il lance le téléchargement tout seul après l'envoi du formulaire, via une
 *      iframe cachée : si le jeton a expiré, c'est l'iframe qui part sur la page
 *      d'erreur, pas l'onglet du visiteur ;
 *   4. il horodate le formulaire (piège à robots côté serveur).
 *
 * Le compteur de téléchargements du plugin est incrémenté par le serveur à
 * chaque appel du lien : on efface donc les paramètres d'URL après le
 * déclenchement automatique, pour qu'un simple F5 ne recompte pas.
 */
(function () {
	'use strict';

	var gate = document.querySelector('[data-afsac-gate]');
	if (!gate) return;

	/*
	 * « Le script pilote le panneau » : le CSS coupe alors son filet de sécurité
	 * `:target`. Sans cela, le panneau ouvert par l'ancre (#afsac-brochure, que
	 * le plugin ajoute à TOUTES ses redirections) ne se refermerait jamais :
	 * close() retire bien `is-open` et nettoie l'URL, mais un
	 * history.replaceState() ne réévalue PAS :target — l'élément reste la cible
	 * du document et la règle continue de l'afficher.
	 */
	document.documentElement.classList.add('afsac-bgate-js');

	/* --- 1. Sortie dans <body> ------------------------------------------- */
	if (gate.parentNode !== document.body) {
		document.body.appendChild(gate);
	}

	var dialog = gate.querySelector('[data-bgate-dialog]');
	var opener = null;

	if (dialog) {
		dialog.setAttribute('role', 'dialog');
		dialog.setAttribute('aria-modal', 'true');
		if (gate.querySelector('#afsac-bgate-title')) {
			dialog.setAttribute('aria-labelledby', 'afsac-bgate-title');
		}
	}

	/** Éléments focalisables du panneau, dans l'ordre du document. */
	function focusables() {
		return Array.prototype.slice.call(
			gate.querySelectorAll('a[href], button, input, select, textarea, [tabindex]:not([tabindex="-1"])')
		).filter(function (el) {
			return !el.disabled && el.offsetParent !== null;
		});
	}

	function open(from) {
		opener = from || null;
		gate.classList.add('is-open');
		document.documentElement.classList.add('afsac-bgate-open');
		// Le premier élément du VOLET DROIT : le champ « Nom et prénom » sur le
		// formulaire, le bouton de téléchargement sur l'écran de remerciement.
		// Viser le premier élément du panneau tout court donnerait le bouton de
		// fermeture, qui ouvre le balisage : on accueillerait le visiteur par
		// « fermer ».
		var first = gate.querySelector(
			'.afsac-bgate__main input:not([type="hidden"]):not([tabindex="-1"]),' +
			'.afsac-bgate__main a[href], .afsac-bgate__main button'
		) || gate.querySelector('[data-bgate-close]');
		if (first) first.focus();
	}

	function close() {
		gate.classList.remove('is-open');
		document.documentElement.classList.remove('afsac-bgate-open');
		// Le panneau peut aussi être ouvert par :target (filet de sécurité CSS) :
		// tant que l'ancre reste dans l'URL, il se rouvrirait aussitôt.
		if (window.location.hash === '#' + gate.id && window.history && window.history.replaceState) {
			window.history.replaceState({}, '', window.location.pathname + window.location.search);
		}
		if (opener) opener.focus();
	}

	/* --- 2. Ouverture / fermeture ---------------------------------------- */
	var triggers = document.querySelectorAll('[data-bgate-trigger]');
	Array.prototype.forEach.call(triggers, function (trigger) {
		trigger.addEventListener('click', function (e) {
			// Ctrl/⌘/clic milieu : on laisse le navigateur suivre l'ancre.
			if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
			e.preventDefault();
			open(trigger);
		});
	});

	gate.addEventListener('click', function (e) {
		if (e.target.closest('[data-bgate-close]')) {
			e.preventDefault();
			close();
		}
	});

	document.addEventListener('keydown', function (e) {
		if (!gate.classList.contains('is-open')) return;

		if (e.key === 'Escape') {
			close();
			return;
		}
		if (e.key !== 'Tab') return;

		// Piège à focus : la tabulation ne doit pas repartir dans la page du fond.
		var items = focusables();
		if (!items.length) return;
		var first = items[0];
		var last = items[items.length - 1];

		if (e.shiftKey && document.activeElement === first) {
			e.preventDefault();
			last.focus();
		} else if (!e.shiftKey && document.activeElement === last) {
			e.preventDefault();
			first.focus();
		}
	});

	/* --- 3. Panneau déjà ouvert au chargement ----------------------------- */
	// Deux cas : le serveur a rendu un résultat à montrer (data-bgate-open), ou
	// le visiteur arrive sur l'ancre #afsac-brochure. Dans les deux cas on passe
	// par open(), pour que le script pilote un panneau qu'il pourra refermer.
	if (gate.hasAttribute('data-bgate-open') || window.location.hash === '#' + gate.id) {
		open(null);
	}

	/* --- 4. Téléchargement automatique ------------------------------------ */
	var auto = gate.querySelector('[data-bgate-auto]');
	if (auto) {
		window.setTimeout(function () {
			var frame = document.createElement('iframe');
			frame.style.display = 'none';
			frame.src = auto.href;
			document.body.appendChild(frame);

			// Les paramètres ont joué leur rôle : on les retire pour qu'un simple
			// F5 ne relance pas le téléchargement (donc un second comptage). On
			// ne touche QU'À eux : la page peut en porter d'autres (?famille=…).
			if (window.history && window.history.replaceState && window.URL) {
				var url = new URL(window.location.href);
				['brochure', 'token', 'dl'].forEach(function (key) {
					url.searchParams.delete(key);
				});
				window.history.replaceState({}, '', url.pathname + url.search + url.hash);
			}
		}, 500);
	}

	/* --- 5. Piège à robots : tampon d'ouverture du formulaire -------------- */
	var stamp = gate.querySelector('[data-afsac-ts]');
	if (stamp) {
		stamp.value = String(Math.floor(Date.now() / 1000));
	}
})();
