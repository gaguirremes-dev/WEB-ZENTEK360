<?php
require_once __DIR__ . '/../config.tienda.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['tienda_admin'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    $_SESSION['login_window'] = $_SESSION['login_window'] ?? time();

    if (time() - $_SESSION['login_window'] > 900) {
        $_SESSION['login_attempts'] = 1;
        $_SESSION['login_window'] = time();
    }

    if ($_SESSION['login_attempts'] > 8) {
        $error = 'Demasiados intentos. Esperá unos minutos y volvé a intentar.';
    } else {
        $usuario = trim($_POST['usuario'] ?? '');
        $clave = $_POST['clave'] ?? '';

        if (hash_equals(TIENDA_ADMIN_USER, $usuario) && password_verify($clave, TIENDA_ADMIN_PASS_HASH)) {
            session_regenerate_id(true);
            $_SESSION['tienda_admin'] = true;
            unset($_SESSION['login_attempts'], $_SESSION['login_window']);
            header('Location: index.php');
            exit;
        }

        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Ingresar | Tienda ZENTEK360</title>
<style>
  * { box-sizing: border-box; }
  body { margin:0; font-family: -apple-system, Segoe UI, Roboto, Arial, sans-serif; background:#f4f5f7; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px; }
  .card { background:#fff; border-radius:14px; box-shadow:0 10px 30px rgba(11,20,44,.12); padding:36px 32px; width:100%; max-width:360px; }
  h1 { font-size:18px; color:#0b142c; margin:0 0 24px; text-align:center; }
  label { display:block; font-size:13px; font-weight:600; color:#0b142c; margin-bottom:6px; }
  input { width:100%; padding:11px 12px; margin-bottom:16px; border:1px solid #d9dde3; border-radius:8px; font-size:14px; }
  button { width:100%; padding:12px; border:none; border-radius:8px; background:#f3580e; color:#fff; font-weight:600; font-size:14px; cursor:pointer; }
  button:hover { background:#d94d0b; }
  .error { background:#fde8e4; color:#a3291a; font-size:13px; padding:10px 12px; border-radius:8px; margin-bottom:16px; }
</style>
</head>
<body>
  <form class="card" method="post" autocomplete="off">
    <h1>Panel de Tienda — ZENTEK360</h1>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <label for="usuario">Usuario</label>
    <input type="text" id="usuario" name="usuario" required autofocus>
    <label for="clave">Contraseña</label>
    <input type="password" id="clave" name="clave" required>
    <button type="submit">Ingresar</button>
  </form>
</body>
</html>
