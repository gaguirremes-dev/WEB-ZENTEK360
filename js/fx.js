'use strict';
// ══════════════════════════════════════════
//  FX — partículas del Z-BOT, parallax y
//  animaciones de scroll (GSAP ScrollTrigger)
// ══════════════════════════════════════════
(function () {
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ── Emisor de partículas tipo "polvo estelar / propulsor" ──
  function crearEmisorParticulas(canvas, opciones) {
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const cfg = Object.assign({
      colorPrincipal: [243, 88, 14],   // naranja ZENTEK
      colorBrillo:    [255, 205, 140],
      porFrame: 1,
      vidaMin: 36, vidaMax: 72,
      velYMin: 0.5, velYMax: 1.6,
      velXMin: -0.35, velXMax: 0.35,
      tamMin: 1, tamMax: 2.6,
      origenXMin: 0.40, origenXMax: 0.60,
      origenYMin: 0.78, origenYMax: 0.94,
    }, opciones || {});

    let particulas = [];
    let dpr = Math.min(window.devicePixelRatio || 1, 2);

    function ajustarTamano() {
      const rect = canvas.getBoundingClientRect();
      canvas.width  = Math.max(1, rect.width * dpr);
      canvas.height = Math.max(1, rect.height * dpr);
    }
    ajustarTamano();
    window.addEventListener('resize', ajustarTamano);

    function nacerParticula() {
      particulas.push({
        x: canvas.width * (cfg.origenXMin + Math.random() * (cfg.origenXMax - cfg.origenXMin)),
        y: canvas.height * (cfg.origenYMin + Math.random() * (cfg.origenYMax - cfg.origenYMin)),
        vx: (cfg.velXMin + Math.random() * (cfg.velXMax - cfg.velXMin)) * dpr,
        vy: (cfg.velYMin + Math.random() * (cfg.velYMax - cfg.velYMin)) * dpr,
        edad: 0,
        vidaMax: cfg.vidaMin + Math.random() * (cfg.vidaMax - cfg.vidaMin),
        r: (cfg.tamMin + Math.random() * (cfg.tamMax - cfg.tamMin)) * dpr,
        brillo: Math.random() < 0.3,
      });
    }

    let activo = true;
    function frame() {
      if (!activo) return;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      for (let i = 0; i < cfg.porFrame; i++) nacerParticula();

      for (let i = particulas.length - 1; i >= 0; i--) {
        const p = particulas[i];
        p.edad++;
        p.x += p.vx;
        p.y += p.vy;
        p.vy *= 0.986;
        const t = p.edad / p.vidaMax;
        if (t >= 1) { particulas.splice(i, 1); continue; }
        const alpha = 1 - t;
        const c = p.brillo ? cfg.colorBrillo : cfg.colorPrincipal;
        ctx.beginPath();
        ctx.fillStyle = `rgba(${c[0]},${c[1]},${c[2]},${alpha.toFixed(2)})`;
        ctx.arc(p.x, p.y, Math.max(0.3, p.r * (1 - t * 0.5)), 0, Math.PI * 2);
        ctx.fill();
      }
      requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);

    // Pausar cuando la pestaña no está visible (ahorro de batería/CPU)
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) { activo = false; }
      else if (!activo) { activo = true; requestAnimationFrame(frame); }
    });
  }

  if (!reduceMotion) {
    crearEmisorParticulas(document.getElementById('zb-robot-fx'));
    crearEmisorParticulas(document.getElementById('zb-guide-fx'), { porFrame: 0.6, vidaMax: 55 });
  }

  // ── El resto son animaciones de scroll con GSAP; si no cargó, se omiten ──
  if (!window.gsap || !window.ScrollTrigger || reduceMotion) return;
  gsap.registerPlugin(ScrollTrigger);

  // Parallax suave de la portada
  const heroImg = document.querySelector('.hero-cover-img');
  if (heroImg) {
    gsap.to(heroImg, {
      yPercent: 12,
      scale: 1.08,
      ease: 'none',
      scrollTrigger: { trigger: '.hero-cover', start: 'top top', end: 'bottom top', scrub: true },
    });
  }

  // Metodología: las líneas conectoras se "dibujan" al entrar en viewport
  const conectores = document.querySelectorAll('#metodologia .tl-connector');
  if (conectores.length) {
    gsap.to(conectores, {
      scaleX: 1,
      duration: 0.7,
      ease: 'power2.out',
      stagger: 0.18,
      scrollTrigger: { trigger: '#metodologia', start: 'top 65%' },
    });
  }

  // Proyectos Realizados: entrada del panel del robot + tiras
  const zbot4Robot = document.querySelector('.zbot4-robot-wrap');
  const zbot4Left   = document.querySelector('.zbot4-left');
  if (zbot4Robot && zbot4Left) {
    gsap.from(zbot4Robot, {
      opacity: 0, scale: 0.85, duration: 0.8, ease: 'power2.out',
      scrollTrigger: { trigger: '#proyectos', start: 'top 60%' },
    });
    gsap.from(zbot4Left, {
      opacity: 0, x: -30, duration: 0.8, ease: 'power2.out',
      scrollTrigger: { trigger: '#proyectos', start: 'top 60%' },
    });
  }
})();
