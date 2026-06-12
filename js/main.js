'use strict';

// ── Nav: sombra al hacer scroll ──
const navEl = document.getElementById('main-nav');
window.addEventListener('scroll', () => {
  navEl.classList.toggle('scrolled', window.scrollY > 10);
}, { passive: true });

// ── Menú Móvil ──
const hamBtn  = document.getElementById('ham-btn');
const mobMenu = document.getElementById('mobile-menu');
let menuOpen  = false;

function openMob() {
  menuOpen = true;
  mobMenu.classList.remove('hidden');
  mobMenu.classList.add('flex');
  hamBtn.setAttribute('aria-expanded', 'true');
  document.body.style.overflow = 'hidden';
}

function closeMob() {
  menuOpen = false;
  mobMenu.classList.add('hidden');
  mobMenu.classList.remove('flex');
  hamBtn.setAttribute('aria-expanded', 'false');
  document.body.style.overflow = '';
}

hamBtn.addEventListener('click', () => menuOpen ? closeMob() : openMob());
document.addEventListener('keydown', e => { if (e.key === 'Escape' && menuOpen) closeMob(); });

// ── Smooth scroll con offset del header fijo (90px) ──
document.querySelectorAll('a[href^="#"]').forEach(link => {
  link.addEventListener('click', e => {
    const id = link.getAttribute('href');
    const target = document.querySelector(id);
    if (!target) return;
    e.preventDefault();
    const top = target.getBoundingClientRect().top + window.pageYOffset - 90;
    window.scrollTo({ top, behavior: 'smooth' });
    if (menuOpen) closeMob();
  });
});

// ── Scroll Reveal (IntersectionObserver) ──
const revealObs = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('on');
      revealObs.unobserve(entry.target);
    }
  });
}, { threshold: 0.08, rootMargin: '0px 0px -48px 0px' });

document.querySelectorAll('.rv').forEach(el => revealObs.observe(el));

// ── Contadores Animados (IntersectionObserver) ──
const counterObs = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (!entry.isIntersecting) return;
    const el     = entry.target;
    const target = parseInt(el.dataset.target, 10);
    const em     = el.querySelector('em');
    if (!em || el.dataset.animated) return;

    el.dataset.animated = '1';
    const duration = 1800;
    const startTime = performance.now();

    const tick = (now) => {
      const elapsed  = now - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const eased    = 1 - Math.pow(1 - progress, 3); // ease-out cubic
      em.textContent = Math.round(eased * target);
      if (progress < 1) requestAnimationFrame(tick);
    };

    requestAnimationFrame(tick);
    counterObs.unobserve(el);
  });
}, { threshold: 0.5 });

document.querySelectorAll('.counter[data-target]').forEach(el => counterObs.observe(el));

// ════════════════════════════════════════
//  SHOWCASE ORBITAL Z-BOT
//  Productos que giran/flotan alrededor del robot, guiados por scroll
// ════════════════════════════════════════
(function () {
  const stage = document.getElementById('zb-stage');
  const orbit = document.getElementById('zb-orbit');
  if (!stage || !orbit) return;

  const prods = Array.from(orbit.querySelectorAll('.zb-prod'));
  const N = prods.length;
  if (!N) return;

  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Geometría (se recalcula en resize)
  let Rx = 320, Ry = 44, baseY = -160;
  function measure() {
    const w = stage.clientWidth;
    const h = stage.clientHeight || 600;
    Rx = Math.min(w * 0.38, 450);
    Ry = Math.min(h * 0.09, 72);
  }
  measure();
  window.addEventListener('resize', measure, { passive: true });

  const idleSpeed = reduce ? 0 : 0.14;   // rad/s — giro continuo automático
  let idleAngle = 0;           // ángulo acumulado
  let paused = false;          // solo se pausa cuando el lightbox está abierto
  let frontEl = null;

  // Posiciona cada producto en función del ángulo (profundidad falsa por escala/opacidad/zIndex)
  function render() {
    const base = idleAngle;
    let bestCos = -2, best = null;

    for (let i = 0; i < N; i++) {
      const a = base + i * (Math.PI * 2 / N);
      const cos = Math.cos(a);                 // 1 = frente, -1 = atrás
      const sin = Math.sin(a);
      const depth = (cos + 1) / 2;             // 0..1

      const bob = reduce ? 0 : Math.sin(idleAngle * 1.25 + i * 1.3) * 7;
      const x = sin * Rx;
      const y = baseY + cos * Ry + bob;
      const scale = 0.60 + depth * 0.43;

      const el = prods[i];
      el.style.transform =
        `translate(calc(-50% + ${x.toFixed(1)}px), calc(-50% + ${y.toFixed(1)}px)) scale(${scale.toFixed(3)})`;
      el.style.opacity = (0.42 + depth * 0.58).toFixed(3);
      el.style.zIndex = String(30 + Math.round(cos * 22));
      el.style.filter = cos < 0 ? `blur(${(-cos * 1.1).toFixed(2)}px)` : 'none';

      if (cos > bestCos) { bestCos = cos; best = el; }
    }

    if (best !== frontEl) {
      if (frontEl) frontEl.classList.remove('is-front');
      best.classList.add('is-front');
      frontEl = best;
    }
  }

  // ── Lightbox: clic en una card → imagen completa ──
  const lb = document.getElementById('zb-lightbox');
  const lbImg = document.getElementById('zb-lb-img');
  const lbCap = document.getElementById('zb-lb-cap');
  let lbOpen = false, closeTimer = null;

  function openLB(fig) {
    const full = fig.getAttribute('data-full');
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
    lbOpen = false;
    paused = false;
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

  // (sin pausa en hover — el giro es continuo siempre)

  // Sin GSAP: render estático inicial
  if (!window.gsap) { render(); return; }

  // Parallax sutil con el mouse
  let mx = 0, my = 0;
  if (!reduce) {
    stage.addEventListener('mousemove', e => {
      const r = stage.getBoundingClientRect();
      mx = (e.clientX - r.left) / r.width - 0.5;
      my = (e.clientY - r.top)  / r.height - 0.5;
    }, { passive: true });
    stage.addEventListener('mouseleave', () => { mx = 0; my = 0; });
  }
  const robot = document.getElementById('zb-robot');

  // Bucle de animación puro — gira solo, sin depender del scroll
  let lastT = gsap.ticker.time;
  gsap.ticker.add(() => {
    const t = gsap.ticker.time;
    const dt = t - lastT; lastT = t;
    if (!paused) idleAngle += dt * idleSpeed;
    render();
    if (robot && !reduce) robot.style.translate = `${(mx * 14).toFixed(1)}px ${(my * 10).toFixed(1)}px`;
  });
})();

// ── Z-BOT 3 Chat Bubbles ──
(function () {
  const chatEl = document.getElementById('zbot3-chat');
  if (!chatEl) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const msgs = [
    { tag: 'n8n',      text: 'Automatiza procesos sin código con n8n.' },
    { tag: 'Hosting',  text: 'Servidores cloud con 99.9% uptime garantizado.' },
    { tag: 'Web',      text: 'Tu web profesional lista en tiempo récord.' },
    { tag: 'IA',       text: 'Integramos inteligencia artificial a tu negocio.' },
    { tag: 'ERP',      text: 'Conectamos tu ERP, CRM y plataformas.' },
    { tag: 'SEO',      text: '¡Aparece en Google con SEO optimizado!' },
    { tag: 'Soporte',  text: 'Respuesta garantizada en menos de 1 hora.' },
    { tag: 'API',      text: 'Integramos APIs y servicios externos.' },
    { tag: 'Cloud',    text: 'Migramos tu infra a AWS, Azure o GCP.' },
    { tag: 'Bot',      text: 'Chatbots que atienden a tus clientes 24/7.' },
  ];

  let idx = 0;

  function addBubble() {
    const m = msgs[idx % msgs.length];
    idx++;
    const el = document.createElement('div');
    el.className = 'zbot-bubble';
    el.innerHTML = `<span class="zbot-badge">${m.tag}</span><br>${m.text}`;
    chatEl.appendChild(el);
    const all = chatEl.querySelectorAll('.zbot-bubble');
    if (all.length > 3) all[0].remove();
    setTimeout(() => { if (el.parentNode) el.remove(); }, 3800);
  }

  setTimeout(addBubble, 700);
  const iv = setInterval(addBubble, 3200);
  window.addEventListener('unload', () => clearInterval(iv), { once: true });
})();
