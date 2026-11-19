<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/lib/tienda.php';

$pdo = tienda_db();
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM brands WHERE slug = ? AND activo = 1");
$stmt->execute([$slug]);
$marca = $stmt->fetch();
$productos = $marca ? tienda_listar_productos($pdo, ['brand_id' => $marca['id']]) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $marca ? htmlspecialchars($marca['nombre']) . ' | Tienda ZENTEK360' : 'Marca no encontrada | ZENTEK360' ?></title>
<meta name="robots" content="<?= $marca ? 'index, follow' : 'noindex, follow' ?>">
<?php if ($marca): ?><link rel="canonical" href="https://zentek360.com/marca/<?= urlencode($slug) ?>"><?php endif; ?>
<link rel="icon" href="/favicon.ico" sizes="any">
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
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NZGL7RKL" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?php $BASE = '/index.html'; $navActive = 'tienda'; include __DIR__ . '/partials/header.php'; ?>

<section class="tienda-hero" style="padding:140px 0 24px;">
  <div class="max-w-[1320px] mx-auto px-6 lg:px-10">
    <h1 class="font-spartan font-black text-white text-[26px] md:text-[34px]"><?= $marca ? htmlspecialchars($marca['nombre']) : 'Marca no encontrada' ?></h1>
  </div>
</section>

<section class="bg-canvas py-10">
  <div class="max-w-[1320px] mx-auto px-6 lg:px-10">
    <?php if (!$marca): ?>
      <p class="text-center text-primary/55 py-20">Esa marca no existe. <a href="/tienda" class="text-secondary">Volver a la tienda</a>.</p>
    <?php elseif (!$productos): ?>
      <p class="text-center text-primary/55 py-20">Todavía no hay productos publicados de esta marca.</p>
    <?php else: ?>
      <p class="text-[13px] text-primary/55 mb-5"><?= count($productos) ?> producto<?= count($productos) === 1 ? '' : 's' ?></p>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($productos as $p) include __DIR__ . '/partials/producto-card.php'; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/js/main.js"></script>
</body>
</html>
