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
