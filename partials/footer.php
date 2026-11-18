<?php
/**
 * Footer compartido por todo el sitio.
 * Definir antes del include: $BASE = '' en index.html, 'index.html' en el resto.
 */
$BASE = $BASE ?? '';
?>
<!-- ═══════════════════════
     FOOTER
═══════════════════════ -->
<footer class="bg-white border-t-4 border-secondary">

  <!-- Logo + descripción arriba -->
  <div class="max-w-[1320px] mx-auto px-6 lg:px-10 pt-16 pb-10 border-b border-primary/8">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
      <a href="<?= $BASE ?: '/' ?>" aria-label="ZENTEK360" class="flex items-center gap-3 flex-shrink-0">
        <img src="images/zentek360_logo_sinfondo.png" width="3125" height="778" alt="ZENTEK360" class="h-[52px] w-auto object-contain"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="items-center gap-3" style="display:none">
          <svg viewBox="0 0 56 56" width="56" height="56">
            <circle cx="28" cy="28" r="27" fill="#0b142c"/>
            <circle cx="28" cy="28" r="27" fill="none" stroke="#f3580e" stroke-width="1.5"/>
            <text x="28" y="37" text-anchor="middle" font-family="League Spartan,sans-serif" font-weight="900" font-size="27" fill="#ffffff">Z</text>
          </svg>
          <div>
            <div style="font-family:'League Spartan',sans-serif;font-weight:900;font-size:21px;color:#0b142c;line-height:1;">ZENTEK<span style="color:#f3580e">360</span></div>
            <div style="font-size:8.5px;letter-spacing:.2em;color:#b0aab3;text-transform:uppercase;margin-top:3px;">Desarrollo Tecnológico</div>
          </div>
        </div>
      </a>
      <p class="text-[13.5px] text-primary/55 max-w-[500px] leading-relaxed">
        ZENTEK360 TECHNOLOGY SOLUTIONS S.A.C. — Fábrica de Software, Inteligencia Artificial, Automatización Empresarial y Transformación Digital para organizaciones líderes en Perú y el mundo.
      </p>
    </div>
  </div>

  <!-- 4 columnas exactas del brief -->
  <div class="max-w-[1320px] mx-auto px-6 lg:px-10 py-12">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">

      <!-- Columna 1: Empresa -->
      <div>
        <h4 class="text-[11px] font-bold text-primary uppercase tracking-[.18em] mb-6">Empresa</h4>
        <ul class="space-y-3 list-none">
          <li><a href="<?= $BASE ?>#metodologia" class="text-[13px] text-primary/55 hover:text-secondary transition-colors duration-200 no-underline">Nuestra Metodología</a></li>
          <li><a href="<?= $BASE ?>#proyectos"   class="text-[13px] text-primary/55 hover:text-secondary transition-colors duration-200 no-underline">Proyectos Realizados</a></li>
          <li><a href="#"            class="text-[13px] text-primary/55 hover:text-secondary transition-colors duration-200 no-underline">Sobre ZENTEK360</a></li>
          <li><a href="#"            class="text-[13px] text-primary/55 hover:text-secondary transition-colors duration-200 no-underline">Política de Privacidad</a></li>
          <li><a href="#"            class="text-[13px] text-primary/55 hover:text-secondary transition-colors duration-200 no-underline">Términos de Servicio</a></li>
        </ul>
        <img src="images/certificaciones/iso-20000-1.svg" width="648" height="318"
             alt="Certificación ISO 20000-1 – Sistema de Gestión de Servicios de TI"
             decoding="async" loading="lazy" class="mt-6 w-[230px] h-auto">
      </div>

      <!-- Columna 2: Servicios -->
      <div>
        <h4 class="text-[11px] font-bold text-primary uppercase tracking-[.18em] mb-6">Servicios</h4>
        <ul class="space-y-3 list-none">
          <li><a href="software-rrhh-smeta.html" class="text-[13px] text-primary/55 hover:text-secondary transition-colors duration-200 no-underline">Software de RRHH para auditorías SMETA</a></li>
          <li><a href="<?= $BASE ?>#contacto" class="text-[13px] text-primary/55 hover:text-secondary transition-colors duration-200 no-underline">Desarrollo de Software</a></li>
          <li><a href="<?= $BASE ?>#contacto" class="text-[13px] text-primary/55 hover:text-secondary transition-colors duration-200 no-underline">Automatización de Procesos</a></li>
          <li><a href="<?= $BASE ?>#contacto" class="text-[13px] text-primary/55 hover:text-secondary transition-colors duration-200 no-underline">Inteligencia Artificial</a></li>
          <li><a href="<?= $BASE ?>#contacto" class="text-[13px] text-primary/55 hover:text-secondary transition-colors duration-200 no-underline">Integración de Sistemas</a></li>
        </ul>
      </div>

      <!-- Columna 3: Contacto -->
      <div>
        <h4 class="text-[11px] font-bold text-primary uppercase tracking-[.18em] mb-6">Contacto</h4>
        <ul class="space-y-3 list-none">
          <li>
            <a href="mailto:comercial@zentek360.com" class="text-[13px] text-primary/55 hover:text-secondary transition-colors duration-200 no-underline flex items-center gap-2">
              <svg width="13" height="13" viewBox="0 0 13 13" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"><rect x="1" y="2.5" width="11" height="8" rx="1.5"/><path d="M1 4l5.5 3.5L12 4"/></svg>
              comercial@zentek360.com
            </a>
          </li>
          <li>
            <span class="text-[13px] text-primary/55 flex items-center gap-2">
              <svg width="13" height="13" viewBox="0 0 13 13" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"><path d="M6.5 1C4.01 1 2 3.01 2 5.5c0 3.25 4.5 6.5 4.5 6.5S11 8.75 11 5.5C11 3.01 8.99 1 6.5 1z"/><circle cx="6.5" cy="5.5" r="1.5"/></svg>
              Lima, Perú
            </span>
          </li>
          <li><span class="text-[13px] text-primary/55">Operaciones 24/7</span></li>
          <li>
            <a href="https://rrhh.zentek360.com" target="_blank" rel="noopener noreferrer" class="text-[13px] text-primary/55 hover:text-secondary transition-colors duration-200 no-underline">
              Intranet ↗
            </a>
          </li>
          <li class="pt-3">
            <a href="libro-de-reclamaciones.php"
               title="Libro de Reclamaciones Virtual — Ley N° 29571"
               class="block w-[130px] rounded-2xl overflow-hidden bg-white shadow-md shadow-primary/15 ring-1 ring-primary/10 transition-all duration-300 motion-safe:hover:-translate-y-0.5 hover:shadow-xl hover:shadow-secondary/20">
              <picture class="block">
                <source srcset="Libro-reclamaciones/libro-reclamaciones-card.webp" type="image/webp">
                <img src="Libro-reclamaciones/libro-reclamaciones-card.png"
                     alt="Libro de Reclamaciones virtual – INDECOPI"
                     width="600" height="600" loading="lazy" decoding="async"
                     class="block w-full h-auto aspect-square object-cover rounded-2xl">
              </picture>
            </a>
            <p class="text-[11px] mt-2 leading-tight">
              <a href="mailto:reclamaciones@zentek360.com" class="text-primary/45 hover:text-secondary transition-colors duration-200 no-underline">reclamaciones@zentek360.com</a>
            </p>
          </li>
        </ul>
      </div>

      <!-- Columna 4: Redes Sociales -->
      <div>
        <h4 class="text-[11px] font-bold text-primary uppercase tracking-[.18em] mb-6">Redes Sociales</h4>
        <ul class="space-y-4 list-none">
          <li>
            <a href="https://www.facebook.com/somoszentek360" target="_blank" rel="noopener noreferrer" aria-label="Facebook ZENTEK360" class="flex items-center gap-3 text-[13px] text-primary/55 hover:text-secondary transition-colors duration-200 no-underline group">
              <div class="w-9 h-9 rounded-lg border border-primary/12 group-hover:border-secondary bg-canvas flex items-center justify-center transition-all duration-200">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
              </div>
              Facebook
            </a>
          </li>
          <li>
            <a href="https://www.instagram.com/somoszentek360?stkn=MTI5czB0ZmFzZWphcg==" target="_blank" rel="noopener noreferrer" aria-label="Instagram ZENTEK360" class="flex items-center gap-3 text-[13px] text-primary/55 hover:text-secondary transition-colors duration-200 no-underline group">
              <div class="w-9 h-9 rounded-lg border border-primary/12 group-hover:border-secondary bg-canvas flex items-center justify-center transition-all duration-200">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".5" fill="currentColor" stroke="none"/></svg>
              </div>
              Instagram
            </a>
          </li>
          <li>
            <a href="https://www.tiktok.com/@somoszentek360" target="_blank" rel="noopener noreferrer" aria-label="TikTok ZENTEK360" class="flex items-center gap-3 text-[13px] text-primary/55 hover:text-secondary transition-colors duration-200 no-underline group">
              <div class="w-9 h-9 rounded-lg border border-primary/12 group-hover:border-secondary bg-canvas flex items-center justify-center transition-all duration-200">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16.6 5.82c-1.36-1.39-1.89-2.81-2.06-3.82H11.5v12.4a2.59 2.59 0 01-2.59 2.5c-1.42 0-2.6-1.16-2.6-2.6c0-1.72 1.66-3.01 3.37-2.48V8.7c-3.45-.46-6.47 2.22-6.47 5.64c0 3.33 2.76 5.7 5.69 5.7c3.14 0 5.69-2.55 5.69-5.7V9.01a7.35 7.35 0 004.3 1.38V7.3c0 0-1.88.09-3.29-1.48z"/></svg>
              </div>
              TikTok
            </a>
          </li>
          <li>
            <a href="https://www.youtube.com/@somoszentek360peru" target="_blank" rel="noopener noreferrer" aria-label="YouTube ZENTEK360" class="flex items-center gap-3 text-[13px] text-primary/55 hover:text-secondary transition-colors duration-200 no-underline group">
              <div class="w-9 h-9 rounded-lg border border-primary/12 group-hover:border-secondary bg-canvas flex items-center justify-center transition-all duration-200">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02" fill="currentColor" stroke="none"/></svg>
              </div>
              YouTube
            </a>
          </li>
        </ul>
      </div>

    </div>
  </div>

  <!-- Copyright -->
  <div class="bg-canvas border-t border-primary/8">
    <div class="max-w-[1320px] mx-auto px-6 lg:px-10 py-5 flex flex-col sm:flex-row justify-between items-center gap-4">
      <p class="text-[12.5px] text-primary/45">© 2026 ZENTEK360 - Desarrollo Tecnológico. Todos los derechos reservados.</p>
      <div class="flex flex-wrap gap-2">
        <span class="text-[10px] font-semibold text-primary/45 border border-primary/12 px-3 py-1 rounded-full uppercase tracking-wide">GDPR</span>
        <span class="text-[10px] font-semibold text-primary/45 border border-primary/12 px-3 py-1 rounded-full uppercase tracking-wide">ISO 27001</span>
        <span class="text-[10px] font-semibold text-primary/45 border border-primary/12 px-3 py-1 rounded-full uppercase tracking-wide">OWASP</span>
        <span class="text-[10px] font-semibold text-primary/45 border border-primary/12 px-3 py-1 rounded-full uppercase tracking-wide">Ley 29733</span>
      </div>
    </div>
  </div>

</footer>
