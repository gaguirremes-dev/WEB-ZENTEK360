<?php
/**
 * Header + menú móvil compartido por todo el sitio.
 * Definir antes del include:
 *   $BASE       = '' en index.html (anclas en la misma página), 'index.html' en el resto.
 *   $navActive  = 'tienda' para resaltar el link activo (opcional).
 */
$BASE = $BASE ?? '';
$navActive = $navActive ?? '';
$claseTienda = $navActive === 'tienda'
    ? 'nav-link text-[13.5px] font-semibold text-secondary transition-colors duration-200'
    : 'nav-link text-[13.5px] font-medium text-[#0b142c]/65 hover:text-[#0b142c] transition-colors duration-200';
$claseTiendaMobile = $navActive === 'tienda'
    ? 'text-[22px] font-semibold text-secondary transition-colors'
    : 'text-[22px] font-semibold text-primary hover:text-secondary transition-colors';
?>
<!-- ═══════════════════════
     HEADER
═══════════════════════ -->
<header id="main-nav" class="fixed top-0 inset-x-0 z-[900] bg-white border-b border-transparent">
  <div class="max-w-[1320px] mx-auto px-6 lg:px-10 h-full flex items-center justify-between gap-6">

    <!-- IMAGOTIPO completo: símbolo circular Z + ZENTEK360 + DESARROLLO TECNOLÓGICO -->
    <a href="<?= $BASE ?: '/' ?>" aria-label="ZENTEK360 — Inicio" class="flex-shrink-0 flex items-center gap-3">
      <!-- Imagen PNG primaria -->
      <img id="logo-img" src="images/zentek360_logo_sinfondo.png" width="3125" height="778" alt="ZENTEK360 Desarrollo Tecnológico" class="h-[56px] w-auto object-contain"
           onerror="document.getElementById('logo-img').style.display='none'; document.getElementById('logo-fallback').style.display='flex';">
      <!-- Fallback HTML imagotipo -->
      <div id="logo-fallback" class="items-center gap-3" style="display:none">
        <svg viewBox="0 0 56 56" width="56" height="56" aria-hidden="true">
          <circle cx="28" cy="28" r="27" fill="#0b142c"/>
          <circle cx="28" cy="28" r="27" fill="none" stroke="#f3580e" stroke-width="1.5"/>
          <text x="28" y="37" text-anchor="middle" font-family="League Spartan, sans-serif" font-weight="900" font-size="27" fill="#ffffff">Z</text>
        </svg>
        <div>
          <div style="font-family:'League Spartan',sans-serif;font-weight:900;font-size:21px;color:#0b142c;line-height:1;letter-spacing:-.3px;">ZENTEK<span style="color:#f3580e">360</span></div>
          <div style="font-family:'Inter',sans-serif;font-weight:600;font-size:8.5px;letter-spacing:.2em;color:#b0aab3;text-transform:uppercase;margin-top:3px;">Desarrollo Tecnológico</div>
        </div>
      </div>
    </a>

    <!-- Navegación desktop -->
    <nav class="hidden xl:flex items-center gap-6" aria-label="Navegación principal">
      <a href="<?= $BASE ?>#inicio"      class="nav-link text-[13.5px] font-medium text-[#0b142c]/65 hover:text-[#0b142c] transition-colors duration-200">Inicio</a>
      <a href="<?= $BASE ?>#metodologia" class="nav-link text-[13.5px] font-medium text-[#0b142c]/65 hover:text-[#0b142c] transition-colors duration-200">Nosotros</a>
      <a href="<?= $BASE ?>#proyectos"   class="nav-link text-[13.5px] font-medium text-[#0b142c]/65 hover:text-[#0b142c] transition-colors duration-200">Proyectos</a>
      <a href="/tienda" class="<?= $claseTienda ?>">Tienda</a>
      <a href="<?= $BASE ?>#contacto"    class="nav-link text-[13.5px] font-medium text-[#0b142c]/65 hover:text-[#0b142c] transition-colors duration-200">Contacto</a>
    </nav>

    <!-- CTA + hamburger -->
    <div class="flex items-center gap-3">
      <a href="<?= $BASE ?>#contacto"
         class="hidden lg:inline-flex items-center gap-2 bg-secondary hover:bg-[#d94d0b] text-white text-[13.5px] font-semibold px-5 py-[11px] rounded-lg no-underline transition-all duration-200 hover:-translate-y-px hover:shadow-[0_6px_20px_rgba(243,88,14,.35)]">
        Solicitar Consultoría
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2.5 7h9M7.5 3l3.5 4-3.5 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
      <button id="ham-btn" class="xl:hidden p-2.5 rounded-md hover:bg-[#0b142c]/5 transition-colors" aria-label="Abrir menú" aria-expanded="false">
        <svg width="22" height="22" viewBox="0 0 22 22" fill="none" stroke="#0b142c" stroke-width="1.8" stroke-linecap="round">
          <line class="bar-top" x1="3" y1="6"  x2="19" y2="6"/>
          <line class="bar-mid" x1="3" y1="11" x2="19" y2="11"/>
          <line class="bar-bot" x1="3" y1="16" x2="19" y2="16"/>
        </svg>
      </button>
    </div>
  </div>
</header>

<!-- Menú móvil -->
<div id="mobile-menu" class="fixed inset-0 bg-white z-[899] flex-col items-center justify-center gap-8 pt-[90px]" role="dialog" aria-modal="true">
  <a href="<?= $BASE ?>#inicio"      onclick="closeMob()" class="text-[22px] font-semibold text-primary hover:text-secondary transition-colors">Inicio</a>
  <a href="<?= $BASE ?>#metodologia" onclick="closeMob()" class="text-[22px] font-semibold text-primary hover:text-secondary transition-colors">Nosotros</a>
  <a href="<?= $BASE ?>#proyectos"   onclick="closeMob()" class="text-[22px] font-semibold text-primary hover:text-secondary transition-colors">Proyectos</a>
  <a href="/tienda" class="<?= $claseTiendaMobile ?>">Tienda</a>
  <a href="<?= $BASE ?>#contacto"    onclick="closeMob()" class="text-[22px] font-semibold text-primary hover:text-secondary transition-colors">Contacto</a>
  <a href="<?= $BASE ?>#contacto" onclick="closeMob()"
     class="mt-2 bg-secondary hover:bg-[#d94d0b] text-white text-[16px] font-semibold px-9 py-4 rounded-xl no-underline transition-colors">
    Solicitar Consultoría
  </a>
</div>
