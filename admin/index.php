<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../lib/tienda.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Token inválido.');
    }

    $id = (int)($_POST['id'] ?? 0);
    $accion = $_POST['accion'] ?? '';
    $pdo = tienda_db();

    if ($accion === 'eliminar' && $id) {
        $imgs = $pdo->prepare("SELECT url FROM product_images WHERE product_id = ?");
        $imgs->execute([$id]);
        foreach ($imgs->fetchAll(PDO::FETCH_COLUMN) as $url) {
            $f = __DIR__ . '/../images/tienda-productos/' . basename($url);
            if (is_file($f)) unlink($f);
        }
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    } elseif ($accion === 'toggle' && $id) {
        $pdo->prepare("UPDATE products SET estado = IF(estado='publicado','borrador','publicado') WHERE id = ? AND estado IN ('publicado','borrador')")->execute([$id]);
    } elseif ($accion === 'destacar' && $id) {
        $pdo->prepare("UPDATE products SET destacado = 1 - destacado WHERE id = ?")->execute([$id]);
    }

    header('Location: index.php');
    exit;
}

$pdo = tienda_db();
$productos = $pdo->query("
    SELECT p.*, c.nombre AS categoria_nombre, b.nombre AS marca_nombre,
           (SELECT MIN(precio_neto) FROM product_variants WHERE product_id = p.id AND activo = 1) AS precio_min,
           (SELECT COALESCE(SUM(stock),0) FROM product_variants WHERE product_id = p.id AND activo = 1) AS stock_total,
           (SELECT sku FROM product_variants WHERE product_id = p.id ORDER BY es_default DESC, id ASC LIMIT 1) AS sku_default,
           (SELECT url FROM product_images WHERE product_id = p.id ORDER BY es_principal DESC, orden ASC LIMIT 1) AS imagen_principal
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN brands b ON b.id = p.brand_id
    ORDER BY p.created_at DESC
")->fetchAll();

$badges = [
    'publicado'     => ['Publicado', 'badge-ok'],
    'borrador'      => ['Borrador', 'badge-off'],
    'agotado'       => ['Agotado', 'badge-warn'],
    'descontinuado' => ['Descontinuado', 'badge-danger'],
];

$tituloPagina = 'Productos';
$activo = 'productos';
include __DIR__ . '/_layout.php';
?>
  <?php
    $totalStock = array_sum(array_column($productos, 'stock_total'));
    $totalPublicados = count(array_filter($productos, fn($p) => $p['estado'] === 'publicado'));
  ?>
  <div class="flex flex-wrap gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-sm shadow-primary/5 px-5 py-4 flex-1 min-w-[140px]">
      <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wide">Productos</div>
      <div class="text-[26px] font-black text-primary mt-1"><?= count($productos) ?></div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm shadow-primary/5 px-5 py-4 flex-1 min-w-[140px]">
      <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wide">Publicados</div>
      <div class="text-[26px] font-black text-green-600 mt-1"><?= $totalPublicados ?></div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm shadow-primary/5 px-5 py-4 flex-1 min-w-[140px]">
      <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wide">Stock total</div>
      <div class="text-[26px] font-black text-primary mt-1"><?= (int)$totalStock ?></div>
    </div>
  </div>

  <div class="top-bar">
    <div class="flex items-center gap-3 flex-wrap flex-1">
      <input type="search" id="buscador" placeholder="Buscar por nombre, SKU, marca o categoría…" class="!w-auto flex-1 min-w-[220px] max-w-[360px]">
      <select id="filtroEstado" class="!w-auto">
        <option value="">Todos los estados</option>
        <option value="publicado">Publicado</option>
        <option value="borrador">Borrador</option>
        <option value="agotado">Agotado</option>
        <option value="descontinuado">Descontinuado</option>
      </select>
    </div>
    <a class="btn" href="producto-form.php">
      <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
      Nuevo producto
    </a>
  </div>

  <?php if (!$productos): ?>
    <div class="card text-center text-gray-500 py-14">
      <p class="text-[14px] mb-4">Todavía no hay productos.</p>
      <a class="btn" href="producto-form.php">+ Nuevo producto</a>
    </div>
  <?php else: ?>
  <div class="overflow-x-auto rounded-2xl">
  <table>
    <thead>
      <tr><th>Foto</th><th>Nombre</th><th>Categoría</th><th>Marca</th><th>SKU</th><th>Desde</th><th>Stock</th><th>Estado</th><th>Acciones</th></tr>
    </thead>
    <tbody id="filas-productos">
      <?php foreach ($productos as $p): $b = $badges[$p['estado']] ?? ['—', 'badge-off']; ?>
      <tr data-buscar="<?= htmlspecialchars(mb_strtolower($p['nombre'] . ' ' . ($p['sku_default'] ?? '') . ' ' . ($p['marca_nombre'] ?? '') . ' ' . ($p['categoria_nombre'] ?? ''))) ?>" data-estado="<?= htmlspecialchars($p['estado']) ?>">
        <td>
          <?php if ($p['imagen_principal']): ?>
            <img src="../images/tienda-productos/<?= htmlspecialchars($p['imagen_principal']) ?>" alt="" class="w-11 h-11 rounded-lg object-cover bg-gray-100">
          <?php else: ?>
            <div class="w-11 h-11 rounded-lg bg-gray-100 flex items-center justify-center text-gray-300">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            </div>
          <?php endif; ?>
        </td>
        <td>
          <span class="font-semibold text-primary"><?= htmlspecialchars($p['nombre']) ?></span>
          <?php if ($p['destacado']): ?><span class="badge badge-ok ml-1.5">★ destacado</span><?php endif; ?>
        </td>
        <td class="text-gray-600"><?= htmlspecialchars($p['categoria_nombre'] ?? '—') ?></td>
        <td class="text-gray-600"><?= htmlspecialchars($p['marca_nombre'] ?? '—') ?></td>
        <td><code class="text-[11px] bg-gray-50 px-1.5 py-0.5 rounded"><?= htmlspecialchars($p['sku_default'] ?? '—') ?></code></td>
        <td class="font-semibold"><?= $p['precio_min'] !== null ? 'S/ ' . number_format($p['precio_min'] * 1.18, 2) : '—' ?></td>
        <td><?= (int)$p['stock_total'] ?></td>
        <td><span class="badge <?= $b[1] ?>"><?= $b[0] ?></span></td>
        <td>
          <div class="flex gap-1.5 flex-wrap">
            <a class="btn btn-sec !px-2.5 !py-1.5 !text-[12px]" href="producto-form.php?id=<?= (int)$p['id'] ?>">Editar</a>
            <form method="post" class="m-0">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="accion" value="destacar">
              <button class="btn btn-sec !px-2.5 !py-1.5 !text-[12px]" type="submit"><?= $p['destacado'] ? 'Quitar ★' : 'Destacar ★' ?></button>
            </form>
            <?php if (in_array($p['estado'], ['publicado', 'borrador'], true)): ?>
            <form method="post" class="m-0">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="accion" value="toggle">
              <button class="btn btn-sec !px-2.5 !py-1.5 !text-[12px]" type="submit"><?= $p['estado'] === 'publicado' ? 'Ocultar' : 'Publicar' ?></button>
            </form>
            <?php endif; ?>
            <form method="post" class="m-0" onsubmit="return confirm('¿Eliminar este producto? No se puede deshacer.');">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="accion" value="eliminar">
              <button class="btn btn-danger !px-2.5 !py-1.5 !text-[12px]" type="submit">Eliminar</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <p id="sin-resultados" hidden class="text-center text-gray-400 text-[13px] py-10">Ningún producto coincide con la búsqueda.</p>
  <?php endif; ?>

<script>
  const buscador = document.getElementById('buscador');
  const filtroEstado = document.getElementById('filtroEstado');
  const filas = document.querySelectorAll('#filas-productos tr');
  const sinResultados = document.getElementById('sin-resultados');

  function aplicarFiltros() {
    const texto = buscador.value.trim().toLowerCase();
    const estado = filtroEstado.value;
    let visibles = 0;
    filas.forEach(fila => {
      const coincideTexto = !texto || fila.dataset.buscar.includes(texto);
      const coincideEstado = !estado || fila.dataset.estado === estado;
      const mostrar = coincideTexto && coincideEstado;
      fila.hidden = !mostrar;
      if (mostrar) visibles++;
    });
    if (sinResultados) sinResultados.hidden = visibles > 0;
  }
  if (buscador) {
    buscador.addEventListener('input', aplicarFiltros);
    filtroEstado.addEventListener('change', aplicarFiltros);
  }
</script>
<?php include __DIR__ . '/_layout_end.php'; ?>
