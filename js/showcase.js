'use strict';
// ========================================
//  SHOWCASE ORBITAL Z-BOT
//  Productos rotando alrededor del robot
// ========================================
(function () {
  const stage = document.getElementById('zb-stage');
  const orbit = document.getElementById('zb-orbit');
  if (!stage || !orbit) return;

  const prods = Array.from(orbit.querySelectorAll('.zb-prod'));
  const N = prods.length;
  if (!N) return;

  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Geometria (se recalcula en resize)
  let Rx = 320, Ry = 44, baseY = -160;
  function measure() {
    const w = stage.clientWidth;
    const h = stage.clientHeight || 600;
    Rx = Math.min(w * 0.38, 450);
    Ry = Math.min(h * 0.09, 72);
  }
  measure();
  window.addEventListener('resize', measure, { passive: true });

  const idleSpeed = reduce ? 0 : 0.14;
  let idleAngle = 0;
  let paused    = false;
  let frontEl   = null;

  // Posiciona cada producto segun el angulo (profundidad falsa)
  function render() {
    const base = idleAngle;
    let bestCos = -2, best = null;

    for (let i = 0; i < N; i++) {
      const a     = base + i * (Math.PI * 2 / N);
      const cos   = Math.cos(a);
      const sin   = Math.sin(a);
      const depth = (cos + 1) / 2;

      const bob   = reduce ? 0 : Math.sin(idleAngle * 1.25 + i * 1.3) * 7;
      const x     = sin * Rx;
      const y     = baseY + cos * Ry + bob;
      const scale = 0.60 + depth * 0.43;

      const el = prods[i];
      el.style.transform =
        `translate(calc(-50% + ${x.toFixed(1)}px), calc(-50% + ${y.toFixed(1)}px)) scale(${scale.toFixed(3)})`;
      el.style.opacity = (0.42 + depth * 0.58).toFixed(3);
      el.style.zIndex  = String(30 + Math.round(cos * 22));
      el.style.filter  = cos < 0 ? `blur(${(-cos * 1.1).toFixed(2)}px)` : 'none';

      if (cos > bestCos) { bestCos = cos; best = el; }
    }

    if (best !== frontEl) {
      if (frontEl) frontEl.classList.remove('is-front');
      best.classList.add('is-front');
      frontEl = best;
    }
  }

  // Lightbox: clic en una card -> imagen completa
  const lb      = document.getElementById('zb-lightbox');
  const lbImg   = document.getElementById('zb-lb-img');
  const lbCap   = document.getElementById('zb-lb-cap');
  let lbOpen    = false;
  let closeTimer = null;

  function openLB(fig) {
    const full  = fig.getAttribute('data-full');
    const title = fig.getAttribute('data-title') || '';
    if (!full || !lb) return;
    if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }
    lbImg.src = full; lbImg.alt = title; lbCap.textContent = title;
    lb.hidden = false;
    lbOpen = true; paused = true;
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => lb.classList.add('is-open'));
  }

  function closeLB() {
    if (!lb) return;
    lb.classList.remove('is-open');
    lbOpen  = false;
    paused  = false;
    document.body.style.overflow = '';
    closeTimer = setTimeout(() => { if (!lbOpen) lb.hidden = true; }, 320);
  }

  if (lb) {
    prods.forEach(fig => fig.addEventListener('click', () => openLB(fig)));
    document.getElementById('zb-lb-close').addEventListener('click', closeLB);
    document.getElementById('zb-lb-cta').addEventListener('click', closeLB);
    lb.addEventListener('click', e => { if (e.target === lb) closeLB(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && lbOpen) closeLB(); });
  }

  // Sin GSAP: render estatico inicial
  if (!window.gsap) { render(); return; }

  // Parallax sutil con el mouse
  let mx = 0, my = 0;
  if (!reduce) {
    stage.addEventListener('mousemove', e => {
      const r = stage.getBoundingClientRect();
      mx = (e.clientX - r.left) / r.width  - 0.5;
      my = (e.clientY - r.top)  / r.height - 0.5;
    }, { passive: true });
    stage.addEventListener('mouseleave', () => { mx = 0; my = 0; });
  }
  const robot = document.getElementById('zb-robot');

  // Bucle de animacion — gira solo, sin scroll
  let lastT = gsap.ticker.time;
  gsap.ticker.add(() => {
    const t  = gsap.ticker.time;
    const dt = t - lastT; lastT = t;
    if (!paused) idleAngle += dt * idleSpeed;
    render();
    // CSS individual transform property (no interfiere con la animacion CSS)
    if (robot && !reduce) robot.style.translate = `${(mx * 14).toFixed(1)}px ${(my * 10).toFixed(1)}px`;
  });
})();
