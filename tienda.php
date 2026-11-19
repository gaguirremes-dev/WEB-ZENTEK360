<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/lib/tienda.php';

$pdo = tienda_db();
$departamentos = $pdo->query("SELECT * FROM categories WHERE nivel = 1 AND activo = 1 ORDER BY orden")->fetchAll();
$destacados = tienda_listar_productos($pdo, ['destacado' => true]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tienda ZENTEK360 — Cómputo, Redes, Seguridad y más</title>
<meta name="description" content="Catálogo de equipos de cómputo, redes, cableado, videovigilancia y energía. Consulta precio y stock, y cotiza directo por WhatsApp.">
<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
<link rel="canonical" href="https://zentek360.com/tienda">
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32.png">
<link rel="icon" type="image/png" sizes="192x192" href="/images/favicon-192.png">
<link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
<meta name="theme-color" content="#0b142c">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=League+Spartan:wght@700;800;900&display=swap" rel="stylesheet">

<link rel="stylesheet" href="/css/tailwind.css">

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NZGL7RKL');</script>
<!-- End Google Tag Manager -->

<link rel="stylesheet" href="/css/styles.css">
<link rel="stylesheet" href="/css/tienda.css">
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NZGL7RKL"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<?php $BASE = '/index.html'; $navActive = 'tienda'; include __DIR__ . '/partials/header.php'; ?>

<section class="tienda-hero">
  <div class="max-w-[1320px] mx-auto px-6 lg:px-10 text-center">
    <h1 class="font-spartan font-black text-white text-[32px] md:text-[42px] leading-tight">Tienda ZENTEK360</h1>
    <p class="text-white/60 text-[14.5px] mt-3 max-w-[560px] mx-auto">Cómputo, redes, cableado, videovigilancia y energía. Elegí, consultá precio y stock, y cerrá tu compra por WhatsApp.</p>
  </div>
</section>

<section class="bg-canvas py-10">
  <div class="max-w-[1320px] mx-auto px-6 lg:px-10">
    <h2 class="text-[13px] font-bold text-primary uppercase tracking-[.14em] mb-5">Departamentos</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-12">
      <?php foreach ($departamentos as $d): ?>
        <a href="/tienda/<?= urlencode($d['slug']) ?>" class="depto-tile no-underline">
          <?= htmlspecialchars($d['nombre']) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if ($destacados): ?>
      <h2 class="text-[13px] font-bold text-primary uppercase tracking-[.14em] mb-5">Destacados</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($destacados as $p) include __DIR__ . '/partials/producto-card.php'; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/js/main.js"></script>
</body>
</html>
