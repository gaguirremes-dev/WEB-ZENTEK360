<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/lib/tienda.php';

$pdo = tienda_db();
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT p.*, c.nombre AS categoria_nombre, c.id AS cat_id, b.nombre AS marca_nombre, b.slug AS marca_slug
                        FROM products p LEFT JOIN categories c ON c.id = p.category_id LEFT JOIN brands b ON b.id = p.brand_id
                        WHERE p.slug = ? AND p.estado IN ('publicado','agotado')");
$stmt->execute([$slug]);
$producto = $stmt->fetch();

if (!$producto) {
    http_response_code(404);
}

if ($producto) {
    $stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? AND activo = 1 ORDER BY es_default DESC, id ASC");
    $stmt->execute([$producto['id']]);
    $variantes = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY es_principal DESC, orden ASC");
    $stmt->execute([$producto['id']]);
    $imagenes = $stmt->fetchAll();

    $valores = tienda_valores_producto($pdo, $producto['id']);
    $atributos = tienda_atributos_para_categoria($pdo, $producto['cat_id']);

    $stmt = $pdo->prepare("SELECT t.nombre FROM product_tag pt JOIN tags t ON t.id = pt.tag_id WHERE pt.product_id = ?");
    $stmt->execute([$producto['id']]);
    $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $default = $variantes[0] ?? null;
    $rutaCategoria = tienda_categoria_ruta($pdo, $producto['cat_id']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $producto ? htmlspecialchars($producto['meta_title'] ?: $producto['nombre']) . ' | ZENTEK360' : 'Producto no encontrado | ZENTEK360' ?></title>
<meta name="description" content="<?= htmlspecialchars($producto['meta_description'] ?? ($producto['descripcion_corta'] ?? 'Catálogo ZENTEK360')) ?>">
<meta name="robots" content="<?= $producto ? 'index, follow' : 'noindex, follow' ?>">
<?php if ($producto): ?><link rel="canonical" href="https://zentek360.com/producto/<?= urlencode($slug) ?>"><?php endif; ?>
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=League+Spartan:wght@700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = { theme: { extend: {
    fontFamily: { sans: ['Inter', 'sans-serif'], spartan: ['League Spartan', 'sans-serif'] },
    colors: { primary: '#0b142c', secondary: '#f3580e', aux: '#d8d7f5', aux2: '#b0aab3', canvas: '#f5f6fa' },
  } } }
</script>
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

<section class="bg-canvas" style="padding-top:120px;">
  <div class="max-w-[1100px] mx-auto px-6 lg:px-10 py-10">

  <?php if (!$producto): ?>
    <p class="text-center text-primary/55 py-20">Ese producto no existe o ya no está disponible. <a href="/tienda" class="text-secondary">Volver a la tienda</a>.</p>
  <?php else: ?>

    <p class="breadcrumb mb-5"><a href="/tienda">Tienda</a> / <?= htmlspecialchars($rutaCategoria) ?></p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
      <div>
        <?php if ($imagenes): ?>
          <img src="/images/tienda-productos/<?= htmlspecialchars($imagenes[0]['url']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="w-full rounded-2xl bg-white" style="aspect-ratio:1/1;object-fit:cover;">
          <?php if (count($imagenes) > 1): ?>
          <div class="flex gap-3 mt-3 flex-wrap">
            <?php foreach (array_slice($imagenes, 1) as $img): ?>
              <img src="/images/tienda-productos/<?= htmlspecialchars($img['url']) ?>" alt="" class="rounded-lg" style="width:72px;height:72px;object-fit:cover;">
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="w-full rounded-2xl bg-white" style="aspect-ratio:1/1;"></div>
        <?php endif; ?>
      </div>

      <div>
        <?php if ($producto['marca_nombre']): ?>
          <a href="/marca/<?= urlencode($producto['marca_slug']) ?>" class="text-[11px] font-bold text-secondary uppercase tracking-wide no-underline"><?= htmlspecialchars($producto['marca_nombre']) ?></a>
        <?php endif; ?>
        <h1 class="font-spartan font-black text-primary text-[24px] md:text-[30px] leading-tight mt-1 mb-3"><?= htmlspecialchars($producto['nombre']) ?></h1>

        <?php if ($tags): ?>
          <div class="flex flex-wrap gap-2 mb-4">
            <?php foreach ($tags as $t): ?><span class="text-[10px] font-semibold text-primary/55 border border-primary/15 px-2.5 py-1 rounded-full uppercase"><?= htmlspecialchars($t) ?></span><?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if ($producto['descripcion_corta']): ?>
          <p class="text-[14px] text-primary/70 leading-relaxed mb-5"><?= htmlspecialchars($producto['descripcion_corta']) ?></p>
        <?php endif; ?>

        <?php if ($default): $precio = tienda_precio_vitrina($pdo, $default); ?>
          <div class="flex items-center gap-3 mb-2">
            <span class="text-[30px] font-black text-primary">S/ <?= number_format($precio, 2) ?></span>
            <?php if ($default['precio_lista']): $listaVitrina = tienda_precio_vitrina($pdo, ['precio_neto' => $default['precio_lista'], 'moneda' => $default['moneda']]); ?>
              <span class="text-[16px] text-primary/40 line-through">S/ <?= number_format($listaVitrina, 2) ?></span>
            <?php endif; ?>
          </div>
          <p class="text-[11px] text-primary/45 mb-4">Incluye IGV · SKU <?= htmlspecialchars($default['sku']) ?></p>

          <?php if ($producto['estado'] === 'agotado'): ?>
            <span class="stock-badge stock-off mb-4 inline-block">Agotado</span>
          <?php elseif ($producto['disponibilidad'] === 'bajo_pedido'): ?>
            <span class="stock-badge stock-warn mb-4 inline-block">Bajo pedido · entrega en <?= (int)$producto['dias_entrega'] ?> días</span>
          <?php else: ?>
            <span class="stock-badge stock-ok mb-4 inline-block">Stock disponible</span>
          <?php endif; ?>

          <?php if (count($variantes) > 1): ?>
            <label class="block text-[12px] font-bold text-primary uppercase tracking-wide mb-2 mt-3">Presentación</label>
            <div class="flex flex-wrap gap-2 mb-5">
              <?php foreach ($variantes as $v): ?>
                <span class="filtro-chip <?= $v['id'] === $default['id'] ? 'activo' : '' ?>"><?= htmlspecialchars($v['nombre_variante'] ?: $v['sku']) ?></span>
              <?php endforeach; ?>
            </div>
            <p class="hint text-[11px] text-primary/45 mb-4">Consultá por WhatsApp la presentación que necesites — te confirmamos precio y stock al instante.</p>
          <?php endif; ?>

          <a href="<?= htmlspecialchars(tienda_whatsapp_link($producto['nombre'], $default['sku'])) ?>" target="_blank" rel="noopener noreferrer"
             class="w-full inline-flex items-center justify-center gap-2 bg-secondary hover:bg-[#d94d0b] text-white text-[14px] font-semibold px-6 py-3.5 rounded-xl no-underline transition-colors duration-200">
            Cotizar por WhatsApp
          </a>
        <?php endif; ?>

        <?php if ($producto['garantia_meses']): ?>
          <p class="text-[12.5px] text-primary/55 mt-4">Garantía: <?= (int)$producto['garantia_meses'] ?> meses</p>
        <?php endif; ?>

        <?php if ($atributos): ?>
          <h3 class="text-[12px] font-bold text-primary uppercase tracking-wide mt-7 mb-3">Especificaciones</h3>
          <table class="w-full text-[13px]">
            <?php foreach ($atributos as $a):
              $v = $valores[$a['id']] ?? null;
              if ($v === null || $v === '') continue;
              $vTexto = is_bool($v) ? ($v ? 'Sí' : 'No') : $v;
            ?>
              <tr class="border-b border-primary/8">
                <td class="py-2 text-primary/55"><?= htmlspecialchars($a['nombre']) ?><?= $a['unidad'] ? ' (' . htmlspecialchars($a['unidad']) . ')' : '' ?></td>
                <td class="py-2 font-semibold text-primary text-right"><?= htmlspecialchars((string)$vTexto) ?></td>
              </tr>
            <?php endforeach; ?>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($producto['descripcion_larga']): ?>
      <div class="mt-10 pt-8 border-t border-primary/8">
        <h3 class="text-[14px] font-bold text-primary uppercase tracking-wide mb-3">Descripción</h3>
        <p class="text-[14px] text-primary/70 leading-relaxed"><?= nl2br(htmlspecialchars($producto['descripcion_larga'])) ?></p>
      </div>
    <?php endif; ?>

  <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/js/main.js"></script>
</body>
</html>
