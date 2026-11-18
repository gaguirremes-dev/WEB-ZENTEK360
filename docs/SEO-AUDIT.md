# Auditoría SEO — ZENTEK360 (zentek360.com)

Fecha: 2026-11-18 · Sitio en producción, español, Perú · Verificado contra código fuente y HTTP en vivo (curl).

## Resumen

El sitio está técnicamente sano: HTTPS forzado, redirecciones canónicas correctas, robots.txt permite rastreo, GTM instalado una sola vez por página, títulos/descripciones en rango, H1 único por página. El bloqueo principal no es de SEO sino de despliegue: `tienda.php` todavía responde **404 en producción** porque el código nuevo no se subió al servidor. No hay acceso a GA4/Search Console/Google Business Profile — esas partes quedan como pendientes manuales (👤).

## Tabla por fase

| Fase | Elemento | Estado | Evidencia | Acción | Prioridad |
|---|---|---|---|---|---|
| F1 | Etiqueta GTM | ✅ | `GTM-NZGL7RKL` aparece 1 vez en `<script>` + 1 vez en `<noscript>` (fallback) en las 5 páginas, sin duplicar con `gtag.js` | Ninguna | — |
| F1 | GA4 ↔ Search Console, eventos clave | 👤 | Requiere login en GA4/GTM | Vincular en GA4 → Admin → Product links; crear triggers para clic en "Comprar por WhatsApp" y envío del formulario de reclamaciones | P2 |
| F2 | Search Console (propiedad verificada) | 👤 | Hay `<meta name="google-site-verification">` en `index.html`, indicio de verificación previa, pero no puedo confirmar estado actual | Entrar a Search Console y confirmar que la propiedad sigue verificada y sin mensajes de seguridad | P2 |
| F2 | Google Business Profile | 👤 | Sin acceso | Verificar NAP (nombre/dirección/teléfono) coincide con el footer y el JSON-LD | P3 |
| F3 | `robots.txt` | ✅ | `curl` → 200, `Allow: /`, declara `Sitemap:` | Ninguna | — |
| F3 | Meta robots por página | ✅ | Home y RRHH: `index, follow`; `404.html`: `noindex, follow` (correcto) | Ninguna | — |
| F3 | `sitemap.xml` | ✅ | Se agregó `libro-de-reclamaciones.php` (confirmado por el usuario) | Aplicado | — |
| F3 | `tienda.php` en producción | ❌ | `curl -I https://zentek360.com/tienda.php` → **404 Not Found** | Desplegar el código de la tienda (git push ya hecho localmente, falta el deploy a cPanel) | **P1 — bloquea** |
| F4 | `<title>` home / RRHH | ✅ | 51 / 50 caracteres | Ninguna | — |
| F4 | Meta description home / RRHH | ✅ | 158 / 156 caracteres | Ninguna | — |
| F4 | `<title>` / description `libro-de-reclamaciones.php` | ✅ | Ampliados a 47 / 160 caracteres | Aplicado | — |
| F4 | Canonical `libro-de-reclamaciones.php` | ✅ | Se agregó `<link rel="canonical">` | Aplicado | — |
| F4 | `<title>` `tienda.php` (local, no desplegado) | ✅ | Era "Tienda \| ZENTEK360" (19 car.), ahora "Tienda ZENTEK360 — Productos y Soluciones Tecnológicas" | Aplicado | — |
| F4 | H1 único por página | ✅ | 1 H1 en cada página revisada | Ninguna | — |
| F4 | `alt` en imágenes | ✅ | Sin `<img>` sin `alt` en `index.html` ni `software-rrhh-smeta.html` | Ninguna | — |
| F6 | HTTPS + redirecciones | ✅ | `http→https` y `www→sin-www` devuelven 301 hacia la versión canónica | Ninguna | — |
| F6 | HSTS | ✅ | Se agregó `Strict-Transport-Security: max-age=31536000` en `.htaccess` | Aplicado | — |
| F6 | JSON-LD | ✅ | Home: `Organization` + `ProfessionalService`. RRHH: 3 bloques `ld+json` | Validar con Rich Results Test (👤) | P3 |
| F6 | Core Web Vitals | 👤 | Cuota pública de PageSpeed Insights agotada hoy, no pude medir | Revisar en Search Console → Core Web Vitals, o `pagespeed.web.dev` manualmente | P2 |
| F6 | Archivos sensibles bloqueados | ✅ | `cpanel.yml` → 403; `.md`, `.env` bloqueados por `.htaccess` | Ninguna | — |
| F7 | Búsqueda con IA | ✅ | No exige nada adicional a F3–F6, ya cubierto | Ninguna | — |

## Pendientes manuales (👤) — pasos exactos

1. **GA4 ↔ Search Console**: GA4 → Admin → Product links → Search Console links → vincular la propiedad de zentek360.com.
2. **Eventos clave en GTM**: crear un trigger de clic para los botones `Comprar por WhatsApp` (clase `.producto-card a[href*="wa.me"]`) y otro para el envío exitoso del formulario de `libro-de-reclamaciones.php`.
3. **Confirmar Search Console**: entrar y revisar que no haya acciones manuales ni problemas de seguridad; revisar informe "Páginas indexadas".
4. **Google Business Profile**: confirmar que nombre/dirección/teléfono coinciden con el footer del sitio y el JSON-LD (`+51964155424`, Lima, Perú).
5. **Core Web Vitals**: revisar en Search Console o en `https://pagespeed.web.dev/?url=https://zentek360.com` (manual, la API pública se quedó sin cuota hoy).
6. **Rich Results Test**: pegar `https://zentek360.com/` en `https://search.google.com/test/rich-results` para validar el JSON-LD.

## Cambios aplicados en esta sesión

- `sitemap.xml`: agregada la entrada de `libro-de-reclamaciones.php`.
- `libro-de-reclamaciones.php`: agregado `<link rel="canonical">`, `<meta name="robots">` explícito, y título/descripción ampliados a largo correcto.
- `tienda.php`: título ampliado (sigue pendiente de deploy, ver tabla F3).
- `.htaccess`: agregada cabecera `Strict-Transport-Security`.

**Pendiente, a propósito, sin aplicar:** `tienda.php` NO se agregó al `sitemap.xml` todavía — hacerlo ahora haría que Google encuentre una URL que responde 404. Agregarla recién cuando el deploy esté hecho y la página devuelva 200.

## Próxima revisión

Volver a medir en 2 a 4 semanas una vez que: (a) se despliegue `tienda.php`, (b) se vinculen GA4 y Search Console, y (c) se definan los eventos clave. Search Console tarda en acumular datos nuevos — no esperar movimiento inmediato en el informe de Rendimiento.
