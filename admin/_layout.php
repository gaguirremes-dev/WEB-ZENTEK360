<?php
/** Header visual + nav del panel. Incluir después de auth.php. Variables: $tituloPagina, $activo ('productos'|'marcas'|'categorias'). */
$activo = $activo ?? '';
function admin_nav_link(string $href, string $label, string $clave, string $activo): string {
    $cls = $clave === $activo
        ? 'text-secondary font-bold'
        : 'text-white/55 font-semibold hover:text-white transition-colors';
    return "<a href=\"$href\" class=\"text-[13px] no-underline $cls\">$label</a>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= htmlspecialchars($tituloPagina ?? 'Panel') ?> | Tienda ZENTEK360</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/tailwind.css">
<style>
  * { box-sizing: border-box; }
  body { margin:0; font-family:'Inter',-apple-system,Segoe UI,Roboto,Arial,sans-serif; background:#f5f6fa; color:#0b142c; }

  /* Compatibilidad con marcas.php / categorias.php / producto-form.php,
     que todavia usan estas clases/selectores "clasicos" en vez de utilities. */
  .btn { display:inline-flex; align-items:center; gap:6px; padding:10px 18px; border-radius:10px; background:#f3580e; color:#fff; text-decoration:none; font-weight:600; font-size:13px; border:none; cursor:pointer; transition:background .15s ease, transform .15s ease; }
  .btn:hover { background:#d94d0b; }
  .btn:active { transform:scale(.97); }
  .btn-sec { background:#fff; color:#0b142c; border:1px solid #e2e5ea; }
  .btn-sec:hover { background:#f9fafb; }
  .btn-danger { background:#dc2626; }
  .btn-danger:hover { background:#b91c1c; }
  table { width:100%; border-collapse:collapse; background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 1px 2px rgba(11,20,44,.04), 0 8px 24px rgba(11,20,44,.06); }
  th, td { padding:14px 16px; text-align:left; font-size:13.5px; border-bottom:1px solid #f0f1f4; vertical-align:top; }
  th { background:#fafbfc; font-weight:700; text-transform:uppercase; font-size:10.5px; letter-spacing:.06em; color:#6b7280; }
  tbody tr:hover { background:#fafbfd; }
  tbody tr:last-child td { border-bottom:none; }
  .badge { display:inline-flex; align-items:center; gap:4px; padding:4px 11px; border-radius:999px; font-size:11px; font-weight:700; }
  .badge-ok { background:#e3f7e8; color:#1a7a35; }
  .badge-off { background:#f1f1f3; color:#6b7280; }
  .badge-warn { background:#fff3cd; color:#8a6400; }
  .badge-danger { background:#fde8e4; color:#a3291a; }
  .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px; }
  .card { background:#fff; border-radius:16px; padding:28px; box-shadow:0 1px 2px rgba(11,20,44,.04), 0 8px 24px rgba(11,20,44,.06); }
  label { display:block; font-size:13px; font-weight:600; margin-bottom:6px; margin-top:16px; color:#374151; }
  input[type=text], input[type=number], input[type=search], textarea, select {
    width:100%; padding:10px 12px; border:1px solid #e2e5ea; border-radius:10px; font-size:14px; font-family:inherit; background:#fff; transition:border-color .15s ease, box-shadow .15s ease;
  }
  input[type=text]:focus, input[type=number]:focus, input[type=search]:focus, textarea:focus, select:focus {
    outline:none; border-color:#f3580e; box-shadow:0 0 0 3px rgba(243,88,14,.12);
  }
  textarea { resize:vertical; min-height:80px; }
  .row2 { display:flex; gap:14px; flex-wrap:wrap; }
  .row2 > div { flex:1; min-width:180px; }
  .check { display:flex; align-items:center; gap:8px; margin-top:16px; }
  .check input { width:auto; }
  .error { background:#fde8e4; color:#a3291a; font-size:13px; padding:10px 12px; border-radius:10px; margin-top:16px; }
  .ok-msg { background:#e3f7e8; color:#1a7a35; font-size:13px; padding:10px 12px; border-radius:10px; margin-top:16px; }
  .section-title { font-size:15px; font-weight:800; margin:32px 0 4px; }
  .hint { font-size:12px; color:#6b7280; margin:0 0 12px; }
  .variant-row, .attr-row { border:1px solid #f0f1f4; border-radius:12px; padding:14px; margin-top:10px; }
</style>
</head>
<body>
<header class="bg-primary text-white px-6 py-4 flex items-center justify-between gap-4 flex-wrap sticky top-0 z-10 shadow-lg shadow-primary/10">
  <div class="flex items-center gap-2.5">
    <div class="w-7 h-7 rounded-md bg-secondary flex items-center justify-center font-black text-[13px]">Z</div>
    <h1 class="text-[14px] font-bold m-0">Panel de Tienda</h1>
  </div>
  <nav class="flex items-center gap-6 flex-wrap">
    <?= admin_nav_link('index.php', 'Productos', 'productos', $activo) ?>
    <?= admin_nav_link('marcas.php', 'Marcas', 'marcas', $activo) ?>
    <?= admin_nav_link('categorias.php', 'Categorías', 'categorias', $activo) ?>
    <?= admin_nav_link('paquetes.php', 'Paquetes (home)', 'paquetes', $activo) ?>
    <a href="logout.php" class="text-[12px] text-white/45 hover:text-white/80 transition-colors no-underline">Cerrar sesión ↗</a>
  </nav>
</header>
<div class="wrap max-w-[1200px] mx-auto p-6">
