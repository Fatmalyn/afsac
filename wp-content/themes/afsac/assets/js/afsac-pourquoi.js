/**
 * Section « Pourquoi nous choisir » — anneau d'atouts + panneau de texte.
 *
 * Les nœuds sont répartis sur un arc de 200° ; l'anneau pivote pour amener le
 * nœud actif face au texte (angle 0 = côté du panneau) et chaque nœud
 * contre-pivote du même angle pour rester droit.
 *
 *   pas          = 200 / n
 *   rotation     = -index * pas
 *   nœud i       : translate(cos(i*pas)*R, sin(i*pas)*R) puis rotate(-rotation)
 *
 * Le rayon R est lu dans la variable CSS --afsac-why-r : les points de rupture
 * ne vivent que dans la feuille de style.
 *
 * Au changement, le texte GLISSE : la nouvelle diapositive arrive de la droite
 * (sens « suivant », lecture latine) pendant que l'ancienne part vers la gauche,
 * ligne par ligne. Le sens vit dans la variable CSS --afsac-why-dir, écrite ici
 * même : sens de lecture (dirSign) × sens de navigation (+1 / -1). Elle
 * s'inverse donc sur « précédent » comme en arabe, sans dupliquer un seul
 * keyframe.
 *
 * Auto-désactivant : sort immédiatement sans [data-afsac-why].
 * En prefers-reduced-motion : positionnement statique, pas de défilement
 * automatique, mais nœuds et flèches restent fonctionnels.
 */
(function () {
  'use strict';

  var section = document.querySelector('[data-afsac-why]');
  if (!section) { return; }

  var ring = section.querySelector('[data-afsac-why-ring]');
  var items = section.querySelectorAll('.afsac-pourquoi__item');
  var slides = section.querySelectorAll('.afsac-pourquoi__slide');
  if (!ring || !items.length || items.length !== slides.length) { return; }

  var zone = section.querySelector('.afsac-pourquoi__ring-zone');
  var prev = section.querySelector('[data-afsac-why-prev]');
  var next = section.querySelector('[data-afsac-why-next]');
  var counter = section.querySelector('[data-afsac-why-current]');
  var slidesWrap = section.querySelector('.afsac-pourquoi__slides');
  var bar = section.querySelector('[data-afsac-why-bar]');
  var reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  // En RTL l'anneau passe de l'autre côté : on ouvre l'arc VERS le texte.
  var dirSign = getComputedStyle(section).direction === 'rtl' ? -1 : 1;

  var TOTAL = items.length;
  var ARC = 200;                 // Amplitude de l'arc, en degrés.
  var STEP = ARC / TOTAL;
  // La course du texte dure ~1,5 s : en dessous de cette cadence, la diapositive
  // repartirait presque aussitôt posée.
  var INTERVAL = 4500;
  // Durée totale de la sortie (0,22 s + 0,06 s de décalage) + une marge.
  var LEAVE_MS = 360;
  var current = 0;
  var timer = null;
  var paused = false;
  var rafId = null;
  var leaveTimer = null;

  // La jauge de cadence est peinte en CSS : elle lit l'intervalle d'ici, qui
  // reste la seule source de vérité.
  section.style.setProperty('--afsac-why-cadence', INTERVAL + 'ms');

  function radius() {
    if (!zone) { return 280; }
    var raw = parseFloat(getComputedStyle(zone).getPropertyValue('--afsac-why-r'));
    return isNaN(raw) ? 280 : raw;
  }

  /**
   * Distance entre la fin du bloc de texte et le bord de la section.
   *
   * Le CSS s'en sert deux fois : pour étendre la fenêtre de lecture jusqu'au bord
   * de l'écran, et pour que la course du texte parte de ce même bord. Ce n'est
   * mesurable qu'à l'exécution : la marge du conteneur dépend de la largeur de la
   * fenêtre. Recalculé au redimensionnement, avec le placement des nœuds.
   */
  function measureGap() {
    if (!slidesWrap) { return; }
    var sect = section.getBoundingClientRect();
    var box = slidesWrap.getBoundingClientRect();
    var gap = dirSign > 0 ? sect.right - box.right : box.left - sect.left;
    section.style.setProperty('--afsac-why-gap', Math.max(0, Math.round(gap)) + 'px');
  }

  // Place les nœuds sur l'arc et pivote l'anneau pour l'index actif.
  function layout() {
    var r = radius();
    var rotation = -current * STEP;
    ring.style.transform = 'rotate(' + rotation + 'deg)';
    items.forEach(function (item, i) {
      var rad = i * STEP * Math.PI / 180;   // Math.cos/sin attendent des RADIANS.
      var x = dirSign * Math.cos(rad) * r;
      var y = Math.sin(rad) * r;
      // Le nœud courant se détache un peu du cercle (même transition que la
      // rotation, donc le grossissement accompagne le pivot).
      var pop = i === current ? ' scale(1.07)' : '';
      item.style.transform = 'translate(' + x.toFixed(1) + 'px, ' + y.toFixed(1) + 'px) rotate(' + (-rotation) + 'deg)' + pop;
    });
  }

  // Relance une animation CSS depuis zéro : retirer la classe, forcer un reflow,
  // la reposer. Sans le reflow le navigateur
  // regroupe les deux écritures et ne voit aucun changement.
  function replay(el, cls) {
    if (!el) { return; }
    el.classList.remove(cls);
    void el.offsetWidth;
    el.classList.add(cls);
  }

  function restartBar() {
    if (!bar) { return; }
    bar.style.animation = 'none';
    void bar.offsetWidth;
    bar.style.animation = '';
  }

  /**
   * Affiche l'atout d'index i.
   *
   * @param {number}  i         Index visé (ramené dans [0, TOTAL[).
   * @param {boolean} moveFocus Déplacer le focus sur le nœud correspondant.
   * @param {number}  dir       Sens de navigation : +1 suivant, -1 précédent.
   *                            C'est l'APPELANT qui le fournit — l'écart entre
   *                            les index ne suffirait pas, le bouclage 06 → 01
   *                            restant un « suivant ».
   */
  function show(i, moveFocus, dir) {
    var previous = current;
    current = (i + TOTAL) % TOTAL;

    // Sens de lecture × sens de navigation → le texte arrive toujours du côté
    // « d'où l'on vient ».
    if (slidesWrap) {
      slidesWrap.style.setProperty('--afsac-why-dir', String(dirSign * (dir < 0 ? -1 : 1)));
    }

    // Les classes sont retirées PARTOUT, puis reposées après un reflow : sinon
    // rappeler l'atout déjà affiché (clic sur son nœud) ne rejouerait rien.
    items.forEach(function (item) { item.classList.remove('is-active'); });
    slides.forEach(function (s) { s.classList.remove('is-active', 'is-leaving'); });
    void section.offsetWidth;

    items.forEach(function (item, idx) {
      var on = idx === current;
      item.classList.toggle('is-active', on);
      item.setAttribute('aria-selected', on ? 'true' : 'false');
      item.setAttribute('tabindex', on ? '0' : '-1');
    });
    slides.forEach(function (s, idx) {
      var on = idx === current;
      s.classList.toggle('is-active', on);
      s.setAttribute('tabindex', on ? '0' : '-1');
      // La sortante reste peinte le temps du croisement.
      if (idx === previous && previous !== current) { s.classList.add('is-leaving'); }
    });
    // ... puis redevient invisible : sans cela elle resterait empilée au-dessus
    // de la nouvelle une fois son fondu terminé.
    window.clearTimeout(leaveTimer);
    leaveTimer = window.setTimeout(function () {
      slides.forEach(function (s) { s.classList.remove('is-leaving'); });
    }, LEAVE_MS);

    if (counter) {
      counter.textContent = ('0' + (current + 1)).slice(-2);
      replay(counter, 'is-swap');
    }
    restartBar();
    layout();
    if (moveFocus && items[current]) { items[current].focus(); }
  }

  function stop() {
    if (timer) { window.clearInterval(timer); timer = null; }
  }

  function start() {
    if (reduce || paused || document.hidden) { return; }
    stop();
    timer = window.setInterval(function () { show(current + 1, false, 1); }, INTERVAL);
  }

  function go(i, moveFocus, dir) { show(i, moveFocus, dir); start(); }

  items.forEach(function (item, idx) {
    // Sauter directement à un nœud : le texte vient du côté correspondant au
    // sens du saut (en avant ou en arrière dans la liste).
    item.addEventListener('click', function () { go(idx, false, idx < current ? -1 : 1); });
  });
  if (prev) { prev.addEventListener('click', function () { go(current - 1, false, -1); }); }
  if (next) { next.addEventListener('click', function () { go(current + 1, false, 1); }); }

  // Clavier sur l'anneau (tablist vertical) : haut/bas, Origine/Fin.
  ring.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowDown' || e.key === 'ArrowRight') { go(current + 1, true, 1); e.preventDefault(); }
    else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') { go(current - 1, true, -1); e.preventDefault(); }
    else if (e.key === 'Home') { go(0, true, -1); e.preventDefault(); }
    else if (e.key === 'End') { go(TOTAL - 1, true, 1); e.preventDefault(); }
  });

  // .is-paused fige la jauge (animation-play-state) ; à la reprise l'intervalle
  // repart de zéro, la jauge doit donc repartir de zéro elle aussi — sinon elle
  // annoncerait un changement qui n'arrive pas.
  function pause() { paused = true; stop(); section.classList.add('is-paused'); }
  function resume() {
    paused = false;
    section.classList.remove('is-paused');
    restartBar();
    start();
  }
  section.addEventListener('mouseenter', pause);
  section.addEventListener('mouseleave', resume);
  section.addEventListener('focusin', pause);
  section.addEventListener('focusout', resume);

  document.addEventListener('visibilitychange', function () {
    if (document.hidden) { stop(); } else { restartBar(); start(); }
  });

  // Le rayon change aux points de rupture : on repositionne (throttle rAF).
  window.addEventListener('resize', function () {
    if (rafId) { return; }
    rafId = requestAnimationFrame(function () { rafId = null; measureGap(); layout(); });
  }, { passive: true });

  measureGap();
  show(0, false, 1);
  start();
})();
