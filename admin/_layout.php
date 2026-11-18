<?php
/** Header visual + nav del panel. Incluir después de auth.php. Variables: $tituloPagina, $activo ('productos'|'marcas'|'categorias'). */
$activo = $activo ?? '';
function admin_nav_link(string $href, string $label, string $clave, string $activo): string {
    $cls = $clave === $activo
        ? 'color:#f3580e;font-weight:700;'
        : 'color:#0b142c99;font-weight:600;';
    return "<a href=\"$href\" style=\"text-decoration:none;font-size:13px;$cls\">$label</a>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= htmlspecialchars($tituloPagina ?? 'Panel') ?> | Tienda ZENTEK360</title>
<style>
  * { box-sizing: border-box; }
  body { margin:0; font-family: -apple-system, Segoe UI, Roboto, Arial, sans-serif; background:#f4f5f7; color:#0b142c; }
  header { background:#0b142c; color:#fff; padding:16px 24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; }
  header h1 { font-size:15px; margin:0; }
  header nav { display:flex; gap:18px; flex-wrap:wrap; align-items:center; }
  header nav a { color:#fff; }
  header .logout { color:#fff; opacity:.6; text-decoration:none; font-size:12px; }
  .wrap { max-width:1200px; margin:0 auto; padding:24px; }
  .btn { display:inline-block; padding:10px 18px; border-radius:8px; background:#f3580e; color:#fff; text-decoration:none; font-weight:600; font-size:13px; border:none; cursor:pointer; }
  .btn:hover { background:#d94d0b; }
  .btn-sec { background:#fff; color:#0b142c; border:1px solid #d9dde3; }
  .btn-danger { background:#a3291a; }
  table { width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 1px 4px rgba(11,20,44,.08); }
  th, td { padding:12px 14px; text-align:left; font-size:13px; border-bottom:1px solid #eef0f3; vertical-align:top; }
  th { background:#f9fafb; font-weight:700; text-transform:uppercase; font-size:11px; letter-spacing:.04em; color:#0b142c99; }
  .badge { display:inline-block; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:700; }
  .badge-ok { background:#e3f7e8; color:#1a7a35; }
  .badge-off { background:#f1f1f3; color:#6b7280; }
  .badge-warn { background:#fff3cd; color:#8a6400; }
  .badge-danger { background:#fde8e4; color:#a3291a; }
  .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
  .card { background:#fff; border-radius:12px; padding:28px; box-shadow:0 1px 4px rgba(11,20,44,.08); }
  label { display:block; font-size:13px; font-weight:600; margin-bottom:6px; margin-top:16px; }
  input[type=text], input[type=number], input[type=search], textarea, select {
    width:100%; padding:10px 12px; border:1px solid #d9dde3; border-radius:8px; font-size:14px; font-family:inherit; background:#fff;
  }
  textarea { resize:vertical; min-height:80px; }
  .row2 { display:flex; gap:14px; flex-wrap:wrap; }
  .row2 > div { flex:1; min-width:180px; }
  .check { display:flex; align-items:center; gap:8px; margin-top:16px; }
  .check input { width:auto; }
  .error { background:#fde8e4; color:#a3291a; font-size:13px; padding:10px 12px; border-radius:8px; margin-top:16px; }
  .ok-msg { background:#e3f7e8; color:#1a7a35; font-size:13px; padding:10px 12px; border-radius:8px; margin-top:16px; }
  .section-title { font-size:15px; font-weight:800; margin:32px 0 4px; }
  .hint { font-size:12px; color:#6b7280; margin:0 0 12px; }
  .variant-row, .attr-row { border:1px solid #eef0f3; border-radius:10px; padding:14px; margin-top:10px; }
</style>
</head>
<body>
<header>
  <h1>Panel de Tienda — ZENTEK360</h1>
  <nav>
    <?= admin_nav_link('index.php', 'Productos', 'productos', $activo) ?>
    <?= admin_nav_link('marcas.php', 'Marcas', 'marcas', $activo) ?>
    <?= admin_nav_link('categorias.php', 'Categorías', 'categorias', $activo) ?>
    <a class="logout" href="logout.php">Cerrar sesión ↗</a>
  </nav>
</header>
<div class="wrap">
