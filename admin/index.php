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
  <div class="top-bar">
    <strong><?= count($productos) ?> producto<?= count($productos) === 1 ? '' : 's' ?></strong>
    <a class="btn" href="producto-form.php">+ Nuevo producto</a>
  </div>

  <?php if (!$productos): ?>
    <div class="card" style="text-align:center;color:#6b7280;">Todavía no hay productos. Hacé clic en "Nuevo producto" para empezar.</div>
  <?php else: ?>
  <table>
    <thead>
      <tr><th>Foto</th><th>Nombre</th><th>Categoría</th><th>Marca</th><th>SKU</th><th>Desde</th><th>Stock</th><th>Estado</th><th>Acciones</th></tr>
    </thead>
    <tbody>
      <?php foreach ($productos as $p): $b = $badges[$p['estado']] ?? ['—', 'badge-off']; ?>
      <tr>
        <td>
          <?php if ($p['imagen_principal']): ?>
            <img src="../images/tienda-productos/<?= htmlspecialchars($p['imagen_principal']) ?>" alt="" style="width:44px;height:44px;object-fit:cover;border-radius:6px;background:#f0f0f0;">
          <?php else: ?>
            <div style="width:44px;height:44px;border-radius:6px;background:#f0f0f0;"></div>
          <?php endif; ?>
        </td>
        <td>
          <?= htmlspecialchars($p['nombre']) ?>
          <?php if ($p['destacado']): ?><span class="badge badge-ok" style="margin-left:6px;">★ destacado</span><?php endif; ?>
        </td>
        <td><?= htmlspecialchars($p['categoria_nombre'] ?? '—') ?></td>
        <td><?= htmlspecialchars($p['marca_nombre'] ?? '—') ?></td>
        <td><code style="font-size:11px;"><?= htmlspecialchars($p['sku_default'] ?? '—') ?></code></td>
        <td><?= $p['precio_min'] !== null ? 'S/ ' . number_format($p['precio_min'] * 1.18, 2) : '—' ?></td>
        <td><?= (int)$p['stock_total'] ?></td>
        <td><span class="badge <?= $b[1] ?>"><?= $b[0] ?></span></td>
        <td>
          <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <a class="btn btn-sec" style="padding:6px 10px;font-size:12px;" href="producto-form.php?id=<?= (int)$p['id'] ?>">Editar</a>
            <form method="post" style="margin:0;">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="accion" value="destacar">
              <button class="btn btn-sec" style="padding:6px 10px;font-size:12px;" type="submit"><?= $p['destacado'] ? 'Quitar ★' : 'Destacar ★' ?></button>
            </form>
            <?php if (in_array($p['estado'], ['publicado', 'borrador'], true)): ?>
            <form method="post" style="margin:0;">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="accion" value="toggle">
              <button class="btn btn-sec" style="padding:6px 10px;font-size:12px;" type="submit"><?= $p['estado'] === 'publicado' ? 'Ocultar' : 'Publicar' ?></button>
            </form>
            <?php endif; ?>
            <form method="post" style="margin:0;" onsubmit="return confirm('¿Eliminar este producto? No se puede deshacer.');">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="accion" value="eliminar">
              <button class="btn btn-danger" style="padding:6px 10px;font-size:12px;" type="submit">Eliminar</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
<?php include __DIR__ . '/_layout_end.php'; ?>
