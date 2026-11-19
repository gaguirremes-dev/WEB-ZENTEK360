<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/lib/tienda.php';

$pdo = tienda_db();
$slug = $_GET['cat'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND activo = 1");
$stmt->execute([$slug]);
$categoria = $stmt->fetch();

if (!$categoria) {
    http_response_code(404);
    $titulo = 'Categoría no encontrada';
    $productos = [];
    $atributosFiltrables = [];
} else {
    $filtros = ['category_id' => $categoria['id'], 'orden' => $_GET['orden'] ?? ''];
    if (!empty($_GET['marca'])) {
        $m = $pdo->prepare("SELECT id FROM brands WHERE slug = ?");
        $m->execute([$_GET['marca']]);
        $marcaId = $m->fetchColumn();
        if ($marcaId) $filtros['brand_id'] = $marcaId;
    }
    $productos = tienda_listar_productos($pdo, $filtros);

    // Filtros por atributo via query string (?ram_gb=16, etc. — solo filtra en memoria, catálogo chico)
    foreach ($_GET as $clave => $valor) {
        if (in_array($clave, ['cat', 'marca', 'orden'], true) || $valor === '') continue;
        $attrStmt = $pdo->prepare("SELECT id FROM attributes WHERE codigo = ?");
        $attrStmt->execute([$clave]);
        $attrId = $attrStmt->fetchColumn();
        if (!$attrId) continue;
        $productos = array_values(array_filter($productos, function ($p) use ($pdo, $attrId, $valor) {
            $v = tienda_valores_producto($pdo, $p['id']);
            return isset($v[$attrId]) && (string)$v[$attrId] === (string)$valor;
        }));
    }

    $atributosFiltrables = array_values(array_filter(
        tienda_atributos_para_categoria($pdo, $categoria['id']),
        fn($a) => $a['es_filtrable']
    ));
    $titulo = $categoria['nombre'];
    $rutaCategoria = tienda_categoria_ruta($pdo, $categoria['id']);
}

$marcasDisponibles = $pdo->query("SELECT DISTINCT b.* FROM brands b JOIN products p ON p.brand_id = b.id ORDER BY b.nombre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($titulo) ?> | Tienda ZENTEK360</title>
<meta name="description" content="<?= htmlspecialchars($titulo) ?> — consulta precio y stock, cotiza por WhatsApp.">
<meta name="robots" content="<?= $categoria ? 'index, follow' : 'noindex, follow' ?>">
<?php if ($categoria): ?><link rel="canonical" href="https://zentek360.com/tienda/<?= urlencode($slug) ?>"><?php endif; ?>
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
    <?php if ($categoria): ?>
      <p class="breadcrumb" style="color:rgba(255,255,255,.5);"><a href="/tienda" style="color:rgba(255,255,255,.5);">Tienda</a> / <?= htmlspecialchars($rutaCategoria) ?></p>
    <?php endif; ?>
    <h1 class="font-spartan font-black text-white text-[26px] md:text-[34px] leading-tight mt-2"><?= htmlspecialchars($titulo) ?></h1>
  </div>
</section>

<section class="bg-canvas py-10">
  <div class="max-w-[1320px] mx-auto px-6 lg:px-10">
    <?php if (!$categoria): ?>
      <p class="text-center text-primary/55 py-20">Esa categoría no existe. <a href="/tienda" class="text-secondary">Volver a la tienda</a>.</p>
    <?php else: ?>

      <div class="flex flex-col lg:flex-row gap-8">
        <aside class="lg:w-[220px] flex-shrink-0">
          <h3 class="text-[12px] font-bold text-primary uppercase tracking-wide mb-3">Marca</h3>
          <div class="flex lg:flex-col flex-wrap gap-2 mb-6">
            <?php foreach ($marcasDisponibles as $m): $qs = $_GET; $qs['marca'] = $m['slug']; ?>
              <a href="?<?= http_build_query($qs) ?>" class="filtro-chip <?= ($_GET['marca'] ?? '') === $m['slug'] ? 'activo' : '' ?>"><?= htmlspecialchars($m['nombre']) ?></a>
            <?php endforeach; ?>
          </div>

          <?php foreach ($atributosFiltrables as $a): if (!$a['opciones']) continue; ?>
            <h3 class="text-[12px] font-bold text-primary uppercase tracking-wide mb-3 mt-5"><?= htmlspecialchars($a['nombre']) ?></h3>
            <div class="flex lg:flex-col flex-wrap gap-2 mb-6">
              <?php foreach ($a['opciones'] as $op): $qs = $_GET; $qs[$a['codigo']] = $op; ?>
                <a href="?<?= http_build_query($qs) ?>" class="filtro-chip <?= ($_GET[$a['codigo']] ?? '') === $op ? 'activo' : '' ?>"><?= htmlspecialchars($op) ?></a>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>

          <?php if (count($_GET) > 1): ?>
            <a href="?cat=<?= urlencode($slug) ?>" class="text-[12px] text-secondary no-underline">✕ Limpiar filtros</a>
          <?php endif; ?>
        </aside>

        <div class="flex-1">
          <div class="flex justify-between items-center mb-5">
            <span class="text-[13px] text-primary/55"><?= count($productos) ?> producto<?= count($productos) === 1 ? '' : 's' ?></span>
            <select onchange="location.href='?' + new URLSearchParams({...Object.fromEntries(new URLSearchParams(location.search)), orden: this.value})" class="text-[13px] border border-primary/15 rounded-lg px-3 py-2">
              <option value="">Relevancia</option>
              <option value="precio_asc" <?= ($_GET['orden'] ?? '') === 'precio_asc' ? 'selected' : '' ?>>Precio: menor a mayor</option>
              <option value="precio_desc" <?= ($_GET['orden'] ?? '') === 'precio_desc' ? 'selected' : '' ?>>Precio: mayor a menor</option>
            </select>
          </div>

          <?php if (!$productos): ?>
            <p class="text-center text-primary/55 py-20">No hay productos publicados en esta categoría todavía.</p>
          <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
              <?php foreach ($productos as $p) include __DIR__ . '/partials/producto-card.php'; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/js/main.js"></script>
</body>
</html>
