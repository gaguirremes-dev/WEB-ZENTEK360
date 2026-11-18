<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$pdo = tienda_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Token inválido.');
    }
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['accion'] ?? '') === 'toggle' && $id) {
        $pdo->prepare("UPDATE categories SET activo = 1 - activo WHERE id = ?")->execute([$id]);
    }
    header('Location: categorias.php');
    exit;
}

$cats = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) AS total_productos FROM categories c ORDER BY nivel, orden, nombre")->fetchAll();
$porPadre = [];
foreach ($cats as $c) {
    $porPadre[$c['parent_id'] ?: 0][] = $c;
}

function render_categoria_arbol(array $porPadre, $parentId, string $csrf, int $prof = 0) {
    foreach ($porPadre[$parentId ?: 0] ?? [] as $c) {
        $pad = 18 + $prof * 24;
        echo '<tr>';
        echo '<td style="padding-left:' . $pad . 'px;">' . ($prof > 0 ? '└ ' : '') . htmlspecialchars($c['nombre']) . ' <code style="font-size:11px;color:#6b7280;">' . htmlspecialchars($c['slug']) . '</code></td>';
        echo '<td>' . (int)$c['total_productos'] . '</td>';
        echo '<td><span class="badge ' . ($c['activo'] ? 'badge-ok' : 'badge-off') . '">' . ($c['activo'] ? 'Activa' : 'Oculta') . '</span></td>';
        echo '<td><form method="post" style="margin:0;"><input type="hidden" name="csrf" value="' . htmlspecialchars($csrf) . '"><input type="hidden" name="id" value="' . (int)$c['id'] . '"><input type="hidden" name="accion" value="toggle"><button class="btn btn-sec" style="padding:6px 10px;font-size:12px;" type="submit">' . ($c['activo'] ? 'Ocultar' : 'Activar') . '</button></form></td>';
        echo '</tr>';
        render_categoria_arbol($porPadre, $c['id'], $csrf, $prof + 1);
    }
}

$tituloPagina = 'Categorías';
$activo = 'categorias';
include __DIR__ . '/_layout.php';
?>
  <p class="hint">El árbol de categorías viene de <code>docs/taxonomia-zentek360.json</code> y se siembra una sola vez. Acá solo podés ocultar categorías que no uses, no crear nuevas (eso se edita en el JSON).</p>
  <table>
    <thead><tr><th>Categoría</th><th>Productos</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
      <?php render_categoria_arbol($porPadre, 0, $_SESSION['csrf']); ?>
    </tbody>
  </table>
<?php include __DIR__ . '/_layout_end.php'; ?>
