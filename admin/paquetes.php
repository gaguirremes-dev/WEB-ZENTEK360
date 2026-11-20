<?php
/**
 * Administra las 5 imágenes de "paquetes" que orbitan en el home
 * (sección "Conoce a Z-BOT"). No usan base de datos: cada slug tiene un
 * par de archivos fijos en images/productos/{slug}.webp (miniatura) y
 * {slug}-full.webp (la que se ve en el lightbox al hacer clic), y subir
 * una foto nueva simplemente regenera esos dos archivos.
 */
require_once __DIR__ . '/auth.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

const PAQUETES = [
    'corporativo'   => 'Corporativo',
    'empresarial'   => 'Empresarial',
    'emprendedores' => 'Emprendedores',
    'mype'          => 'MYPE',
    'publicidad'    => 'Presencia Digital',
];

$dirImagenes = __DIR__ . '/../images/productos/';
$error = '';
$ok = '';

/** Redimensiona por ancho (mantiene proporción) y guarda como WebP. */
function zk_guardar_webp_redimensionado($src, int $anchoDestino, string $rutaDestino): void {
    $w = imagesx($src);
    $h = imagesy($src);
    $altoDestino = max(1, (int) round($h * ($anchoDestino / $w)));
    $dst = imagecreatetruecolor($anchoDestino, $altoDestino);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $anchoDestino, $altoDestino, $w, $h);
    imagewebp($dst, $rutaDestino, 82);
    imagedestroy($dst);
}

/** Devuelve null si todo salió bien, o un mensaje de error. */
function zk_procesar_paquete(array $file, string $dirImagenes, string $slug): ?string {
    if (!function_exists('imagewebp')) {
        return 'El servidor no tiene soporte de WebP en GD. Avisale a Claude para que revise esto.';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'Error al subir el archivo.';
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return 'La imagen no puede superar 5MB.';
    }
    $info = getimagesize($file['tmp_name']);
    if (!$info) {
        return 'El archivo no es una imagen válida.';
    }
    $src = match ($info[2]) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($file['tmp_name']),
        IMAGETYPE_PNG  => imagecreatefrompng($file['tmp_name']),
        IMAGETYPE_WEBP => imagecreatefromwebp($file['tmp_name']),
        default => null,
    };
    if (!$src) {
        return 'Formato no permitido — usá JPG, PNG o WEBP.';
    }

    zk_guardar_webp_redimensionado($src, 780, $dirImagenes . $slug . '.webp');
    zk_guardar_webp_redimensionado($src, 1240, $dirImagenes . $slug . '-full.webp');
    imagedestroy($src);
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Token inválido.');
    }

    $errores = [];
    $actualizados = [];
    foreach (PAQUETES as $slug => $nombre) {
        if (empty($_FILES[$slug]['name'])) continue;
        $err = zk_procesar_paquete($_FILES[$slug], $dirImagenes, $slug);
        if ($err) {
            $errores[] = "$nombre: $err";
        } else {
            $actualizados[] = $nombre;
        }
    }

    if ($errores) {
        $error = implode(' ', $errores);
    } elseif ($actualizados) {
        $ok = 'Se actualizó: ' . implode(', ', $actualizados) . '.';
    } else {
        $error = 'No seleccionaste ninguna imagen nueva.';
    }
}

$tituloPagina = 'Paquetes del home';
$activo = 'paquetes';
include __DIR__ . '/_layout.php';
?>
  <p class="hint">Estas son las 5 tarjetas que orbitan al robot en la portada ("Conoce a Z-BOT"). Subí una foto nueva para reemplazar cualquiera — se redimensiona y optimiza sola, no hace falta que la prepares vos.</p>

  <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($ok): ?><div class="ok-msg"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
    <div class="flex flex-wrap gap-5 mt-4">
      <?php foreach (PAQUETES as $slug => $nombre): ?>
        <div class="card" style="width:270px;">
          <img src="../images/productos/<?= $slug ?>.webp?t=<?= time() ?>" alt="<?= htmlspecialchars($nombre) ?>"
               class="w-full rounded-xl object-cover mb-3" style="aspect-ratio:4/5;">
          <p class="font-semibold text-primary text-[14px] mb-2"><?= htmlspecialchars($nombre) ?></p>
          <input type="file" name="<?= $slug ?>" accept=".jpg,.jpeg,.png,.webp" style="width:100%; font-size:12px;">
        </div>
      <?php endforeach; ?>
    </div>
    <button class="btn mt-6" type="submit">Guardar cambios</button>
  </form>
<?php include __DIR__ . '/_layout_end.php'; ?>
