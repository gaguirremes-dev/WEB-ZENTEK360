'use strict';
// â”€â”€ Z-BOT 3 Chat Bubbles â”€â”€
(function () {
  const chatEl = document.getElementById('zbot3-chat');
  if (!chatEl) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const msgs = [
    { tag: 'n8n',      text: 'Automatiza procesos sin cÃ³digo con n8n.' },
    { tag: 'Hosting',  text: 'Servidores cloud con 99.9% uptime garantizado.' },
    { tag: 'Web',      text: 'Tu web profesional lista en tiempo rÃ©cord.' },
    { tag: 'IA',       text: 'Integramos inteligencia artificial a tu negocio.' },
    { tag: 'ERP',      text: 'Conectamos tu ERP, CRM y plataformas.' },
    { tag: 'SEO',      text: 'Â¡Aparece en Google con SEO optimizado!' },
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
