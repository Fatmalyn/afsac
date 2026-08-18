(function(){
  var reduce=matchMedia('(prefers-reduced-motion: reduce)').matches;
  var counts=document.querySelectorAll('.afsac-count');
  function grp(n){return String(n).replace(/\B(?=(\d{3})+(?!\d))/g,' ');}
  function fin(el){el.textContent=grp(el.dataset.target)+(el.dataset.suffix||'');}
  if(reduce){counts.forEach(fin);return;}
  var io=new IntersectionObserver(function(es){es.forEach(function(e){
    if(e.isIntersecting){e.target.classList.add('is-visible');io.unobserve(e.target);}});},
    {threshold:0.15,rootMargin:'0px 0px -8% 0px'});
  document.querySelectorAll('.afsac-reveal').forEach(function(el){io.observe(el);});
  var cio=new IntersectionObserver(function(es){es.forEach(function(e){
    if(e.isIntersecting){count(e.target);cio.unobserve(e.target);}});},{threshold:0.4});
  counts.forEach(function(el){cio.observe(el);});
  function count(el){var t=parseInt(el.dataset.target,10)||0,s=el.dataset.suffix||'',
    d=1400,a=null;function step(n){a||(a=n);var p=Math.min((n-a)/d,1),e=1-Math.pow(1-p,3);
    el.textContent=grp(Math.round(e*t))+s;p<1&&requestAnimationFrame(step);}
    requestAnimationFrame(step);}

  // Parallaxe léger du contenu du hero (désactivé en reduced-motion : on est
  // déjà sorti plus haut le cas échéant). On pilote une CSS var lue par le CSS.
  var heroContent=document.querySelector('.afsac-video-hero__content');
  if(heroContent){
    var hero=heroContent.closest('.afsac-video-hero'),hTick=false;
    function heroParallax(){hTick=false;
      var r=hero.getBoundingClientRect();
      if(r.bottom<0||r.top>window.innerHeight)return;
      var y=window.pageYOffset||document.documentElement.scrollTop||0;
      heroContent.style.setProperty('--afsac-hero-shift',(y*0.15).toFixed(1)+'px');}
    window.addEventListener('scroll',function(){
      if(!hTick){hTick=true;requestAnimationFrame(heroParallax);}},{passive:true});
  }
})();
