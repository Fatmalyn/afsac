/**
 * Page « Savoir plus » d'une actualité : jauge de lecture + copie du lien.
 *
 * GLOBAL et auto-désactivant, comme afsac-guide-hero.js / afsac-pourquoi.js :
 * sans .afsac-progress ni .afsac-share__copy dans la page, le script sort
 * immédiatement. Conditionner l'enfilement à une liste de gabarits finit
 * toujours par se désynchroniser.
 */
(function () {
  var bar = document.querySelector('.afsac-progress__bar');
  var article = document.querySelector('.afsac-article__main');

  if (bar && article) {
    var ticking = false;

    function draw() {
      ticking = false;
      var doc = document.documentElement;
      var y = window.pageYOffset || doc.scrollTop || 0;
      // Le texte commence à être « lu » quand son haut atteint le haut de la
      // fenêtre, et l'est entièrement quand son bas en atteint le bas.
      var start = article.getBoundingClientRect().top + y;
      var span = article.offsetHeight - window.innerHeight;
      var p;

      if (span > 40) {
        p = (y - start) / span;
      } else {
        // Actualité plus courte qu'une fenêtre : la jauge suivrait un saut de 0
        // à 1 dès le premier défilement. On suit alors la page entière.
        var max = doc.scrollHeight - window.innerHeight;
        p = max > 0 ? y / max : 0;
      }

      bar.style.transform = 'scaleX(' + Math.max(0, Math.min(1, p)).toFixed(4) + ')';
    }

    function onScroll() {
      if (!ticking) {
        ticking = true;
        requestAnimationFrame(draw);
      }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    draw();
  }

  var copy = document.querySelector('.afsac-share__copy');
  if (copy) {
    copy.addEventListener('click', function () {
      var url = copy.getAttribute('data-url') || window.location.href;

      function done() {
        copy.classList.add('is-done');
        var label = copy.getAttribute('data-done');
        if (label) {
          copy.setAttribute('title', label);
        }
        window.setTimeout(function () {
          copy.classList.remove('is-done');
        }, 2200);
      }

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(done, function () {});
        return;
      }
      // Repli sans API presse-papiers (http local, vieux navigateurs).
      var tmp = document.createElement('textarea');
      tmp.value = url;
      tmp.setAttribute('readonly', '');
      tmp.style.position = 'absolute';
      tmp.style.left = '-9999px';
      document.body.appendChild(tmp);
      tmp.select();
      try {
        document.execCommand('copy');
        done();
      } catch (e) {
        /* Silencieux : le lien reste visible dans la barre d'adresse. */
      }
      document.body.removeChild(tmp);
    });
  }
})();
