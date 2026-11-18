<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../lib/tienda.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$pdo = tienda_db();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Token inválido.');
    }
    $accion = $_POST['accion'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($accion === 'crear') {
        $nombre = trim($_POST['nombre'] ?? '');
        if ($nombre === '') {
            $error = 'El nombre es obligatorio.';
        } else {
            $slug = tienda_slugify($nombre);
            $pdo->prepare("INSERT INTO brands (slug, nombre) VALUES (?, ?)")->execute([$slug, $nombre]);
        }
    } elseif ($accion === 'toggle' && $id) {
        $pdo->prepare("UPDATE brands SET activo = 1 - activo WHERE id = ?")->execute([$id]);
    }

    if (!$error) { header('Location: marcas.php'); exit; }
}

$marcas = $pdo->query("SELECT b.*, (SELECT COUNT(*) FROM products WHERE brand_id = b.id) AS total_productos FROM brands b ORDER BY nombre")->fetchAll();

$tituloPagina = 'Marcas';
$activo = 'marcas';
include __DIR__ . '/_layout.php';
?>
  <div class="row2" style="align-items:flex-start;">
    <div style="flex:2;min-width:300px;">
      <?php if (!$marcas): ?>
        <div class="card" style="text-align:center;color:#6b7280;">Todavía no hay marcas.</div>
      <?php else: ?>
      <table>
        <thead><tr><th>Marca</th><th>Productos</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <?php foreach ($marcas as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m['nombre']) ?> <code style="font-size:11px;color:#6b7280;"><?= htmlspecialchars($m['slug']) ?></code></td>
            <td><?= (int)$m['total_productos'] ?></td>
            <td><span class="badge <?= $m['activo'] ? 'badge-ok' : 'badge-off' ?>"><?= $m['activo'] ? 'Activa' : 'Oculta' ?></span></td>
            <td>
              <form method="post" style="display:inline;">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                <input type="hidden" name="accion" value="toggle">
                <button class="btn btn-sec" style="padding:6px 10px;font-size:12px;" type="submit"><?= $m['activo'] ? 'Ocultar' : 'Activar' ?></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
    <div style="flex:1;min-width:260px;">
      <div class="card">
        <h3 style="margin-top:0;font-size:15px;">Nueva marca</h3>
        <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
          <input type="hidden" name="accion" value="crear">
          <label for="nombre">Nombre</label>
          <input type="text" id="nombre" name="nombre" required placeholder="Ej: Lenovo">
          <button class="btn" type="submit" style="margin-top:16px;">Agregar</button>
        </form>
      </div>
    </div>
  </div>
<?php include __DIR__ . '/_layout_end.php'; ?>
