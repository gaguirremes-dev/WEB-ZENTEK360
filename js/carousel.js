'use strict';
// ══════════════════════════════════════════
//  LIGHTBOX PROYECTOS — Carrusel
// ══════════════════════════════════════════
(function () {
  const lb        = document.getElementById('proj-lb');
  const track     = document.getElementById('proj-car-track');
  const dotsEl    = document.getElementById('proj-car-dots');
  const prevBtn   = document.getElementById('proj-car-prev');
  const nextBtn   = document.getElementById('proj-car-next');
  const tagEl     = document.getElementById('proj-lb-tag');
  const titleEl   = document.getElementById('proj-lb-title');
  const descEl    = document.getElementById('proj-lb-desc');
  const counterEl = document.getElementById('proj-lb-counter');
  const closeBtn  = document.getElementById('proj-lb-close');
  const visitBtn  = document.getElementById('proj-lb-visit');
  const quoteBtn  = document.getElementById('proj-lb-quote');
  if (!lb) return;

  const WHATSAPP_NUMERO = '51964155424';

  // Completar con el link real de cada proyecto cuando esté disponible.
  // Los que queden en '' simplemente no muestran el botón "Visitar sitio".
  const URLS = {
    'Supply360':        'https://supplylogistic360.com',
    'Capacita':         'https://somosescuelacapacita.com',
    'Ynsumedic':        'https://ynsumedic.com',
    'Quantuk':          'https://quantukperu.com',
    'Rekuperat':        'https://rekuperat.com',
    'Flujos N8N':       '',
    'Convive':          'https://convive360.com',
    'Parkeat':          'https://parkeatcar.com',
    'Arbicop Tienda':   '',
    'Tributa':          'https://somostributa.com',
    'SSOMA':            'https://ssomasafe.com',
    'TuPerfil360':      'https://tuperfil360.com',
    'Agrosumak':        'https://agrosumak.com',
    'TrustAgroExport':  'https://trustagroexport.com',
    'Sitemar RRHH':     '',
    'Arbicop':          'https://arbicop.com',
    'Pura Estética':    '',
    'Hosting cPanel':   '',
    'Agente IA':        '',
    'Abubu':            'https://abubu.com.pe',
    'SureLife360':      'https://surelife360.com',
    'Corp. Yanayacu':   'https://corporacionyanayacu.com',
    'Agua Yanayacu':    'https://aguayanayacu.com',
    'Yanayacu Clea':    'https://yanayacuclean.com',
    'cPanel Hosting':   '',
    'Webmail':          '',
    'Virgen Asunta':    'https://conciliavirgenasunta.com',
  };

  const DESC = {
    'Supply360':        'Plataforma logística integral para gestión de cadena de suministro, inventarios y distribución en tiempo real.',
    'Capacita':         'Plataforma educativa con aulas virtuales, evaluaciones automatizadas, gamificación y seguimiento detallado del progreso.',
    'Ynsumedic':        'Portal médico con gestión de citas, historial clínico y telemedicina integrada para centros de salud.',
    'Quantuk':          'Solución tecnológica a medida con integraciones avanzadas, dashboards ejecutivos y automatización de procesos.',
    'Rekuperat':        'Plataforma fintech RegTech para recuperación de cartera y conciliación de deudas entre bancos y deudores, regulada por la SBS, UIF e INDECOPI.',
    'Flujos N8N':       'Automatización de flujos de trabajo empresariales con n8n eliminando tareas repetitivas e integrando sistemas heterogéneos.',
    'Convive':          'Plataforma comunitaria para gestión de condominios, comunicados, votaciones y pagos de cuotas en línea.',
    'Parkeat':          'App móvil para reserva de espacios de estacionamiento y servicios de alimentación con pagos integrados.',
    'Arbicop Tienda':   'Tienda en línea para distribución de productos con catálogos dinámicos, pedidos y facturación integrada.',
    'Tributa':          'Facturación electrónica integrada con SUNAT, gestión contable y reportería tributaria automatizada para MYPEs.',
    'SSOMA':            'Sistema de gestión de Seguridad, Salud Ocupacional y Medio Ambiente con reportes y seguimiento de incidentes.',
    'TuPerfil360':      'Plataforma de perfil profesional 360° con portafolio, métricas de rendimiento y gestión de talento.',
    'Agrosumak':        'Portal agrícola con gestión de cultivos, trazabilidad de productos y comercialización directa al consumidor.',
    'TrustAgroExport':  'Sitio corporativo para exportadora agrícola, con certificación ISO 14001, líneas de producto y asistente virtual para consultas de cultivo.',
    'Sitemar RRHH':     'Sistema web de gestión de recursos humanos con nómina, asistencia, evaluaciones y procesos de selección.',
    'Arbicop':          'Sitio web corporativo de alto impacto con CMS personalizado, SEO técnico avanzado y analytics integrado.',
    'Pura Estética':    'Plataforma de reservas y gestión para centros de estética con catálogo de servicios y fidelización de clientes.',
    'Hosting cPanel':   'Infraestructura cloud gestionada con cPanel, monitoreo 24/7, backups automáticos y soporte técnico prioritario.',
    'Agente IA':        'Agente conversacional con IA para atención automatizada multicanal con procesamiento de lenguaje natural.',
    'Abubu':            'Tienda en línea de ropa, accesorios y juguetes para bebés, con carrito de compras, envíos a todo el país y atención por WhatsApp.',
    'SureLife360':      'Portal de autogestión para asegurados con consultas de pólizas, reporte de siniestros y pagos en línea.',
    'Corp. Yanayacu':   'Plataforma corporativa para gestión de proyectos comunitarios, comunicación y transparencia institucional.',
    'Agua Yanayacu':    'Sitio web de gestión de servicios de agua potable con reportes de consumo y atención al usuario.',
    'Yanayacu Clea':    'Portal de energías limpias con información de proyectos sustentables, impacto ambiental y comunidad.',
    'cPanel Hosting':   'Solución de hosting empresarial con cPanel avanzado, dominios, correos y herramientas de administración web.',
    'Webmail':          'Servicio de correo corporativo con webmail personalizado, filtros antispam y gestión de buzones empresariales.',
    'Virgen Asunta':    'Centro de conciliación extrajudicial y arbitraje, con especialidades separadas por materia y agenda de consultas en línea.',
  };

  const CAT_COLORS = {
    'Logística':       ['#0c1e48','#1e4080'], 'Educación':      ['#1a0f44','#3820a0'],
    'Salud':           ['#0a2e1c','#18783a'], 'Tecnología':     ['#0c1828','#183060'],
    'Automatización':  ['#1a0f44','#3820a0'], 'Comunidad':      ['#082428','#145e60'],
    'App':             ['#0c1e48','#1e4080'], 'E-commerce':     ['#1c0a44','#4c1a90'],
    'Fintech':         ['#081830','#102868'], 'Seguridad':      ['#1a0808','#501818'],
    'Plataforma':      ['#141428','#303060'], 'Agro':           ['#0a2e10','#185e28'],
    'RRHH':            ['#141428','#303060'], 'Corporativo':    ['#141428','#303060'],
    'Estética':        ['#3c0828','#8e1850'], 'Infraestructura':['#082840','#1068a0'],
    'IA':              ['#0c0828','#281870'], 'Servicios':      ['#1a0830','#481460'],
    'Seguros':         ['#0c0c28','#202060'], 'Ambiental':      ['#082018','#145e3a'],
    'Legal':           ['#1a1408','#5c4a18'],
  };
  const DEFAULT_COLORS = ['#0c1430','#1a2860'];

  let slides = [], current = 0, lbOpen = false, closeTimer = null;

  function buildSlides(tag, title, imgSrc) {
    track.innerHTML = '';
    dotsEl.innerHTML = '';
    slides = [];

    // Una sola pantalla: la captura real del proyecto. Nada de rellenos
    // genericos — si no hay imagen, un fondo de color simple y listo.
    const slide = document.createElement('div');
    slide.className = 'proj-slide';
    if (imgSrc) {
      const img = document.createElement('img');
      img.src      = imgSrc;
      img.alt      = title;
      img.loading  = 'lazy';
      img.decoding = 'async';
      slide.appendChild(img);
    } else {
      const [c1, c2] = CAT_COLORS[tag] || DEFAULT_COLORS;
      slide.style.background = `linear-gradient(135deg, ${c1}, ${c2})`;
    }
    track.appendChild(slide);
    slides.push(slide);

    // Con una sola pantalla no hace falta navegacion ni contador
    [prevBtn, nextBtn, dotsEl, counterEl].forEach(el => { if (el) el.hidden = true; });
  }

  function goTo(idx) {
    current = ((idx % slides.length) + slides.length) % slides.length;
    track.style.transform = `translateX(-${current * 100}%)`;
    dotsEl.querySelectorAll('.proj-car-dot').forEach((d, i) =>
      d.classList.toggle('active', i === current));
    if (counterEl) counterEl.textContent = `${current + 1} / ${slides.length}`;
  }

  function openLB(tag, title, imgSrc) {
    if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }
    tagEl.textContent   = tag;
    titleEl.textContent = title;
    descEl.textContent  = DESC[title] || 'Solución de software a medida desarrollada por el equipo Zentek360.';

    const url = URLS[title];
    if (url) {
      visitBtn.href = url;
      visitBtn.hidden = false;
    } else {
      visitBtn.hidden = true;
    }
    const msj = `Hola ZENTEK360, quiero cotizar una página como "${title}"`;
    quoteBtn.href = `https://wa.me/${WHATSAPP_NUMERO}?text=${encodeURIComponent(msj)}`;

    buildSlides(tag, title, imgSrc);
    goTo(0);
    lb.hidden = false;
    lbOpen    = true;
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => lb.classList.add('is-open'));
  }

  function closeLB() {
    lb.classList.remove('is-open');
    lbOpen = false;
    document.body.style.overflow = '';
    closeTimer = setTimeout(() => { if (!lbOpen) lb.hidden = true; }, 340);
  }

  // Delegar clic en todas las cards (incluye duplicados del loop)
  document.querySelectorAll('.zbot4-card').forEach(card => {
    card.setAttribute('tabindex', '0');
    card.setAttribute('role', 'button');
    const activate = () => {
      const tag    = card.querySelector('.zbot4-card-tag')?.textContent.trim()   || '';
      const title  = card.querySelector('.zbot4-card-title')?.textContent.trim() || '';
      const imgEl  = card.querySelector('.zbot4-card-media img');
      const imgSrc = imgEl ? imgEl.src : null;
      openLB(tag, title, imgSrc);
    };
    card.addEventListener('click', activate);
    card.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); activate(); }
    });
  });

  prevBtn.addEventListener('click', () => goTo(current - 1));
  nextBtn.addEventListener('click', () => goTo(current + 1));
  closeBtn.addEventListener('click', closeLB);
  lb.addEventListener('click', e => { if (e.target === lb) closeLB(); });

  document.addEventListener('keydown', e => {
    if (!lbOpen) return;
    if (e.key === 'Escape')     closeLB();
    if (e.key === 'ArrowLeft')  goTo(current - 1);
    if (e.key === 'ArrowRight') goTo(current + 1);
  });

  // Swipe táctil
  let tx = 0;
  lb.addEventListener('touchstart', e => { tx = e.touches[0].clientX; }, { passive: true });
  lb.addEventListener('touchend',   e => {
    const dx = e.changedTouches[0].clientX - tx;
    if (Math.abs(dx) > 44) goTo(current + (dx < 0 ? 1 : -1));
  }, { passive: true });
})();
