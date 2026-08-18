/**
 * Carrousel de formations de l'accueil — grille 2×2 rotative.
 *
 * Motif ARIA « carrousel à onglets » : les points SONT des onglets (role=tab) et
 * chaque page un panneau (role=tabpanel). On récupère gratuitement la navigation
 * au clavier et une sémantique correcte.
 *
 * Auto-désactivant : sort immédiatement sans [data-afsac-fgrid].
 * En prefers-reduced-motion : pas de défilement automatique, mais points et
 * flèches restent pleinement fonctionnels.
 */
(function () {
  'use strict';

  var root = document.querySelector('[data-afsac-fgrid]');
  if (!root) { return; }

  var pages = root.querySelectorAll('.afsac-fgrid__page');
  var dots = root.querySelectorAll('.afsac-fgrid__dot');
  if (pages.length < 2 || dots.length !== pages.length) { return; }

  var prev = root.querySelector('[data-afsac-fgrid-prev]');
  var next = root.querySelector('[data-afsac-fgrid-next]');
  var reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  var rtl = getComputedStyle(root).direction === 'rtl';
  /*
   * Cadence de la grille. 2 s a été jugé trop rapide (on n'a pas le temps de lire
   * une carte), 6 s trop mou : 4 s est le réglage retenu. Les animations
   * d'ambiance (reflet, onde) entretiennent le mouvement entre deux rotations.
   */
  var INTERVAL = 4000;
  var current = 0;
  var timer = null;
  var paused = false;

  // La barre de progression du point actif est pilotée en CSS : on lui donne la
  // durée réelle du cycle pour qu'elles ne puissent pas diverger.
  root.style.setProperty('--afsac-fgrid-interval', INTERVAL + 'ms');

  function show(i, moveFocus) {
    current = (i + pages.length) % pages.length;
    pages.forEach(function (p, idx) {
      p.classList.toggle('is-active', idx === current);
    });
    dots.forEach(function (d, idx) {
      var on = idx === current;
      d.classList.toggle('is-active', on);
      d.setAttribute('aria-selected', on ? 'true' : 'false');
      // Tabindex mobile : un seul point atteignable au Tab (motif tablist).
      d.setAttribute('tabindex', on ? '0' : '-1');
    });
    /*
     * Le remplissage du point actif redémarre tout seul : l'animation est portée
     * par `.is-active::after`, donc chaque changement de classe crée une nouvelle
     * animation. Rien à réinitialiser à la main.
     */
    if (moveFocus && dots[current]) { dots[current].focus(); }
  }

  function stop() {
    if (timer) { window.clearInterval(timer); timer = null; }
  }

  function start() {
    if (reduce || paused || document.hidden) { return; }
    stop();
    timer = window.setInterval(function () { show(current + 1, false); }, INTERVAL);
  }

  // Interaction manuelle : on affiche puis on relance le minuteur.
  function go(i, moveFocus) { show(i, moveFocus); start(); }

  dots.forEach(function (d, idx) {
    d.addEventListener('click', function () { go(idx, false); });
  });
  if (prev) { prev.addEventListener('click', function () { go(current - 1, false); }); }
  if (next) { next.addEventListener('click', function () { go(current + 1, false); }); }

  // Clavier sur la barre d'onglets : flèches (inversées en RTL), Origine/Fin.
  var tablist = root.querySelector('.afsac-fgrid__dots');
  if (tablist) {
    tablist.addEventListener('keydown', function (e) {
      var step = 0;
      if (e.key === 'ArrowRight') { step = rtl ? -1 : 1; }
      else if (e.key === 'ArrowLeft') { step = rtl ? 1 : -1; }
      else if (e.key === 'Home') { go(0, true); e.preventDefault(); return; }
      else if (e.key === 'End') { go(pages.length - 1, true); e.preventDefault(); return; }
      if (step) { go(current + step, true); e.preventDefault(); }
    });
  }

  // Pause au survol et au focus clavier ; reprise à la sortie. La classe
  // `is-paused` fige aussi la barre de progression du point actif.
  function pause() { paused = true; stop(); root.classList.add('is-paused'); }
  function resume() { paused = false; root.classList.remove('is-paused'); start(); }
  root.addEventListener('mouseenter', pause);
  root.addEventListener('mouseleave', resume);
  root.addEventListener('focusin', pause);
  root.addEventListener('focusout', resume);

  // Onglet en arrière-plan : inutile de faire tourner le carrousel.
  document.addEventListener('visibilitychange', function () {
    if (document.hidden) { stop(); } else { start(); }
  });

  show(0, false);
  start();
})();
