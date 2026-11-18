<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../lib/tienda.php';

$pdo = tienda_db();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$error = '';
$errores = [];

$producto = [
    'nombre' => '', 'slug' => '', 'category_id' => '', 'brand_id' => '',
    'tipo' => 'fisico', 'condicion' => 'nuevo', 'mpn' => '', 'descripcion_corta' => '',
    'descripcion_larga' => '', 'garantia_meses' => '', 'disponibilidad' => 'stock_propio',
    'dias_entrega' => '', 'estado' => 'borrador', 'destacado' => 0,
    'meta_title' => '', 'meta_description' => '',
];
$variantes = [];
$valoresAtributos = [];
$imagenes = [];
$tagsSeleccionados = [];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) $producto = $found;

    $stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id");
    $stmt->execute([$id]);
    $variantes = $stmt->fetchAll();

    $valoresAtributos = tienda_valores_producto($pdo, $id);

    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY es_principal DESC, orden ASC");
    $stmt->execute([$id]);
    $imagenes = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT tag_id FROM product_tag WHERE product_id = ?");
    $stmt->execute([$id]);
    $tagsSeleccionados = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$categorias = tienda_categorias_todas($pdo);
$marcas = $pdo->query("SELECT * FROM brands WHERE activo = 1 ORDER BY nombre")->fetchAll();
$tags = $pdo->query("SELECT * FROM tags ORDER BY nombre")->fetchAll();
$mapaAtributos = tienda_mapa_atributos_por_categoria($pdo);

$dirImagenes = __DIR__ . '/../images/tienda-productos/';
if (!is_dir($dirImagenes)) {
    mkdir($dirImagenes, 0755, true);
}

function guardar_imagen_subida(array $file, string $dir): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > 3 * 1024 * 1024) return null;
    $info = getimagesize($file['tmp_name']);
    $permitidos = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($permitidos[$info[2]])) return null;
    $nombreArchivo = 'prod_' . bin2hex(random_bytes(8)) . '.' . $permitidos[$info[2]];
    return move_uploaded_file($file['tmp_name'], $dir . $nombreArchivo) ? $nombreArchivo : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Token inválido.');
    }

    $producto['nombre'] = trim($_POST['nombre'] ?? '');
    $producto['slug'] = tienda_slugify($_POST['slug'] ?? '') ?: tienda_slugify($producto['nombre']);
    $producto['category_id'] = (int)($_POST['category_id'] ?? 0);
    $producto['brand_id'] = $_POST['brand_id'] !== '' ? (int)$_POST['brand_id'] : null;
    $producto['tipo'] = in_array($_POST['tipo'] ?? '', ['fisico', 'digital', 'servicio'], true) ? $_POST['tipo'] : 'fisico';
    $producto['condicion'] = in_array($_POST['condicion'] ?? '', ['nuevo', 'reacondicionado', 'open_box'], true) ? $_POST['condicion'] : 'nuevo';
    $producto['mpn'] = trim($_POST['mpn'] ?? '');
    $producto['descripcion_corta'] = trim($_POST['descripcion_corta'] ?? '');
    $producto['descripcion_larga'] = trim($_POST['descripcion_larga'] ?? '');
    $producto['garantia_meses'] = $_POST['garantia_meses'] !== '' ? (int)$_POST['garantia_meses'] : null;
    $producto['disponibilidad'] = in_array($_POST['disponibilidad'] ?? '', ['stock_propio', 'bajo_pedido', 'importacion'], true) ? $_POST['disponibilidad'] : 'stock_propio';
    $producto['dias_entrega'] = $_POST['dias_entrega'] !== '' ? (int)$_POST['dias_entrega'] : null;
    $producto['estado'] = in_array($_POST['estado'] ?? '', ['borrador', 'publicado', 'agotado', 'descontinuado'], true) ? $_POST['estado'] : 'borrador';
    $producto['destacado'] = isset($_POST['destacado']) ? 1 : 0;
    $producto['meta_title'] = trim($_POST['meta_title'] ?? '');
    $producto['meta_description'] = trim($_POST['meta_description'] ?? '');

    if ($producto['nombre'] === '') $errores[] = 'El nombre es obligatorio.';
    if (!$producto['category_id']) $errores[] = 'Elegí una categoría.';
    if ($producto['tipo'] === 'servicio') {
        // Regla 7: los servicios no manejan stock ni peso — se ignoran esos campos al guardar variantes.
    }

    // Variantes enviadas (filas con SKU no vacío cuentan)
    $variantesForm = [];
    $skus = $_POST['variante_sku'] ?? [];
    foreach ($skus as $i => $sku) {
        $sku = trim($sku);
        if ($sku === '') continue;
        $variantesForm[] = [
            'sku' => $sku,
            'nombre_variante' => trim($_POST['variante_nombre'][$i] ?? ''),
            'precio_neto' => (float)str_replace(',', '.', $_POST['variante_precio_neto'][$i] ?? '0'),
            'precio_lista' => $_POST['variante_precio_lista'][$i] !== '' ? (float)str_replace(',', '.', $_POST['variante_precio_lista'][$i]) : null,
            'moneda' => in_array($_POST['variante_moneda'][$i] ?? 'PEN', ['PEN', 'USD'], true) ? $_POST['variante_moneda'][$i] : 'PEN',
            'stock' => $producto['tipo'] === 'servicio' ? 0 : max(0, (int)($_POST['variante_stock'][$i] ?? 0)),
            'es_default' => ((int)($_POST['variante_default'] ?? -1)) === $i ? 1 : 0,
        ];
    }
    if (!$variantesForm) {
        $errores[] = 'Agregá al menos una variante (SKU y precio).';
    } elseif (!array_filter($variantesForm, fn($v) => $v['es_default'])) {
        $variantesForm[0]['es_default'] = 1; // regla: siempre hay una variante por defecto
    }

    // Atributos efectivos para la categoría elegida (incluye heredados)
    $atributosCategoria = $producto['category_id'] ? tienda_atributos_para_categoria($pdo, $producto['category_id']) : [];
    $valoresEnviados = [];
    foreach ($atributosCategoria as $a) {
        $campo = 'attr_' . $a['id'];
        if ($a['tipo'] === 'booleano') {
            $valoresEnviados[$a['id']] = isset($_POST[$campo]) ? true : null;
        } elseif ($a['tipo'] === 'multi_seleccion') {
            $vals = $_POST[$campo] ?? [];
            $valoresEnviados[$a['id']] = $vals ? implode(', ', $vals) : null;
        } else {
            $v = trim($_POST[$campo] ?? '');
            $valoresEnviados[$a['id']] = $v !== '' ? $v : null;
        }
    }

    if ($producto['estado'] === 'publicado') {
        $faltan = tienda_atributos_faltantes($atributosCategoria, $valoresEnviados);
        if ($faltan) $errores[] = 'Faltan atributos obligatorios para publicar: ' . implode(', ', $faltan) . '.';
    }

    if (!$errores) {
        $pdo->beginTransaction();
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE products SET nombre=?, slug=?, category_id=?, brand_id=?, tipo=?, condicion=?, mpn=?, descripcion_corta=?, descripcion_larga=?, garantia_meses=?, disponibilidad=?, dias_entrega=?, estado=?, destacado=?, meta_title=?, meta_description=? WHERE id=?");
                $stmt->execute([
                    $producto['nombre'], $producto['slug'], $producto['category_id'], $producto['brand_id'],
                    $producto['tipo'], $producto['condicion'], $producto['mpn'], $producto['descripcion_corta'],
                    $producto['descripcion_larga'], $producto['garantia_meses'], $producto['disponibilidad'],
                    $producto['dias_entrega'], $producto['estado'], $producto['destacado'],
                    $producto['meta_title'], $producto['meta_description'], $id,
                ]);
                $productId = $id;
            } else {
                $stmt = $pdo->prepare("INSERT INTO products (nombre, slug, category_id, brand_id, tipo, condicion, mpn, descripcion_corta, descripcion_larga, garantia_meses, disponibilidad, dias_entrega, estado, destacado, meta_title, meta_description) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([
                    $producto['nombre'], $producto['slug'], $producto['category_id'], $producto['brand_id'],
                    $producto['tipo'], $producto['condicion'], $producto['mpn'], $producto['descripcion_corta'],
                    $producto['descripcion_larga'], $producto['garantia_meses'], $producto['disponibilidad'],
                    $producto['dias_entrega'], $producto['estado'], $producto['destacado'],
                    $producto['meta_title'], $producto['meta_description'],
                ]);
                $productId = (int)$pdo->lastInsertId();
            }

            // Variantes: se reemplazan todas (simple y consistente para un panel admin)
            $pdo->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$productId]);
            $insVar = $pdo->prepare("INSERT INTO product_variants (product_id, sku, nombre_variante, precio_neto, precio_lista, moneda, stock, es_default) VALUES (?,?,?,?,?,?,?,?)");
            foreach ($variantesForm as $v) {
                $insVar->execute([$productId, $v['sku'], $v['nombre_variante'], $v['precio_neto'], $v['precio_lista'], $v['moneda'], $v['stock'], $v['es_default']]);
            }

            // Atributos: se reemplazan los valores
            $pdo->prepare("DELETE FROM product_attribute_values WHERE product_id = ?")->execute([$productId]);
            $insVal = $pdo->prepare("INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (?,?,?,?,?)");
            foreach ($valoresEnviados as $attrId => $valor) {
                if ($valor === null) continue;
                $attrDef = null;
                foreach ($atributosCategoria as $a) if ((int)$a['id'] === (int)$attrId) { $attrDef = $a; break; }
                $esNumero = $attrDef && $attrDef['tipo'] === 'numero';
                $esBool = $attrDef && $attrDef['tipo'] === 'booleano';
                $insVal->execute([
                    $productId, $attrId,
                    (!$esNumero && !$esBool) ? (string)$valor : null,
                    $esNumero ? (float)$valor : null,
                    $esBool ? 1 : null,
                ]);
            }

            // Tags: se reemplazan
            $pdo->prepare("DELETE FROM product_tag WHERE product_id = ?")->execute([$productId]);
            $tagsForm = array_map('intval', $_POST['tags'] ?? []);
            if ($tagsForm) {
                $insTag = $pdo->prepare("INSERT IGNORE INTO product_tag (product_id, tag_id) VALUES (?,?)");
                foreach ($tagsForm as $tagId) $insTag->execute([$productId, $tagId]);
            }

            // Imágenes: eliminar marcadas
            $eliminarImgs = array_map('intval', $_POST['eliminar_imagen'] ?? []);
            if ($eliminarImgs) {
                $in = implode(',', array_fill(0, count($eliminarImgs), '?'));
                $stmtImg = $pdo->prepare("SELECT url FROM product_images WHERE id IN ($in) AND product_id = ?");
                $stmtImg->execute([...$eliminarImgs, $productId]);
                foreach ($stmtImg->fetchAll(PDO::FETCH_COLUMN) as $url) {
                    $f = $dirImagenes . basename($url);
                    if (is_file($f)) unlink($f);
                }
                $pdo->prepare("DELETE FROM product_images WHERE id IN ($in) AND product_id = ?")->execute([...$eliminarImgs, $productId]);
            }

            // Imagen principal nueva
            if (!empty($_FILES['imagen_principal']['name'])) {
                $nombreArchivo = guardar_imagen_subida($_FILES['imagen_principal'], $dirImagenes);
                if ($nombreArchivo) {
                    $pdo->prepare("UPDATE product_images SET es_principal = 0 WHERE product_id = ?")->execute([$productId]);
                    $pdo->prepare("INSERT INTO product_images (product_id, url, alt, orden, es_principal) VALUES (?,?,?,0,1)")
                        ->execute([$productId, $nombreArchivo, $producto['nombre']]);
                }
            }

            // Fotos adicionales nuevas
            if (!empty($_FILES['imagenes_extra']['name'][0])) {
                $max = count($_FILES['imagenes_extra']['name']);
                $insImg = $pdo->prepare("INSERT INTO product_images (product_id, url, alt, orden, es_principal) VALUES (?,?,?,?,0)");
                for ($i = 0; $i < $max; $i++) {
                    if ($_FILES['imagenes_extra']['error'][$i] !== UPLOAD_ERR_OK) continue;
                    $file = [
                        'name' => $_FILES['imagenes_extra']['name'][$i],
                        'type' => $_FILES['imagenes_extra']['type'][$i],
                        'tmp_name' => $_FILES['imagenes_extra']['tmp_name'][$i],
                        'error' => $_FILES['imagenes_extra']['error'][$i],
                        'size' => $_FILES['imagenes_extra']['size'][$i],
                    ];
                    $nombreArchivo = guardar_imagen_subida($file, $dirImagenes);
                    if ($nombreArchivo) $insImg->execute([$productId, $nombreArchivo, $producto['nombre'], $i + 1]);
                }
            }

            $pdo->commit();
            tienda_recalcular_estado($pdo, $productId);
            header('Location: index.php');
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errores[] = 'Error al guardar: ' . $e->getMessage();
        }
    }
    $error = implode(' ', $errores);
    $variantes = array_map(fn($v) => $v + ['id' => null], $variantesForm);
    $valoresAtributos = $valoresEnviados;
}

$tituloPagina = $id ? 'Editar producto' : 'Nuevo producto';
$activoNav = 'productos';
$activo = $activoNav;
include __DIR__ . '/_layout.php';
?>
  <div class="card">
    <h2 style="margin-top:0;font-size:18px;"><?= $id ? 'Editar producto' : 'Nuevo producto' ?></h2>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" id="form-producto">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
      <?php if ($id): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>

      <div class="row2">
        <div>
          <label for="nombre">Nombre *</label>
          <input type="text" id="nombre" name="nombre" required value="<?= htmlspecialchars($producto['nombre']) ?>">
        </div>
        <div>
          <label for="slug">Slug (URL)</label>
          <input type="text" id="slug" name="slug" placeholder="se genera solo si lo dejas vacío" value="<?= htmlspecialchars($producto['slug']) ?>">
        </div>
      </div>

      <div class="row2">
        <div>
          <label for="category_id">Categoría *</label>
          <select id="category_id" name="category_id" required onchange="zkToggleAtributos()">
            <option value="">— elegir —</option>
            <?php foreach ($categorias as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= (int)$producto['category_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                <?= str_repeat('— ', $c['nivel'] - 1) . htmlspecialchars($c['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="brand_id">Marca</label>
          <select id="brand_id" name="brand_id">
            <option value="">Sin marca</option>
            <?php foreach ($marcas as $m): ?>
              <option value="<?= (int)$m['id'] ?>" <?= (string)$producto['brand_id'] === (string)$m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="row2">
        <div>
          <label for="tipo">Tipo</label>
          <select id="tipo" name="tipo">
            <?php foreach (['fisico' => 'Físico', 'digital' => 'Digital', 'servicio' => 'Servicio'] as $k => $v): ?>
              <option value="<?= $k ?>" <?= $producto['tipo'] === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="condicion">Condición</label>
          <select id="condicion" name="condicion">
            <?php foreach (['nuevo' => 'Nuevo', 'reacondicionado' => 'Reacondicionado', 'open_box' => 'Open box'] as $k => $v): ?>
              <option value="<?= $k ?>" <?= $producto['condicion'] === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="mpn">MPN (part number)</label>
          <input type="text" id="mpn" name="mpn" value="<?= htmlspecialchars($producto['mpn']) ?>">
        </div>
      </div>

      <label for="descripcion_corta">Descripción corta</label>
      <input type="text" id="descripcion_corta" name="descripcion_corta" maxlength="300" value="<?= htmlspecialchars($producto['descripcion_corta']) ?>">

      <label for="descripcion_larga">Descripción larga</label>
      <textarea id="descripcion_larga" name="descripcion_larga"><?= htmlspecialchars($producto['descripcion_larga']) ?></textarea>

      <div class="row2">
        <div>
          <label for="garantia_meses">Garantía (meses)</label>
          <input type="number" min="0" id="garantia_meses" name="garantia_meses" value="<?= htmlspecialchars((string)$producto['garantia_meses']) ?>">
        </div>
        <div>
          <label for="disponibilidad">Disponibilidad</label>
          <select id="disponibilidad" name="disponibilidad" onchange="zkToggleDiasEntrega()">
            <?php foreach (['stock_propio' => 'Stock propio', 'bajo_pedido' => 'Bajo pedido', 'importacion' => 'Importación'] as $k => $v): ?>
              <option value="<?= $k ?>" <?= $producto['disponibilidad'] === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div id="campo-dias-entrega">
          <label for="dias_entrega">Días de entrega</label>
          <input type="number" min="0" id="dias_entrega" name="dias_entrega" value="<?= htmlspecialchars((string)$producto['dias_entrega']) ?>">
        </div>
      </div>

      <div class="row2">
        <div>
          <label for="estado">Estado</label>
          <select id="estado" name="estado">
            <?php foreach (['borrador' => 'Borrador', 'publicado' => 'Publicado', 'agotado' => 'Agotado', 'descontinuado' => 'Descontinuado'] as $k => $v): ?>
              <option value="<?= $k ?>" <?= $producto['estado'] === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
          <p class="hint">Si hay stock_propio y el stock llega a 0, el sitio lo pasa a "Agotado" solo.</p>
        </div>
        <div class="check" style="margin-top:30px;">
          <input type="checkbox" id="destacado" name="destacado" <?= $producto['destacado'] ? 'checked' : '' ?>>
          <label for="destacado" style="margin:0;">Destacado (aparece primero en la tienda)</label>
        </div>
      </div>

      <div class="section-title">Etiquetas</div>
      <div style="display:flex;flex-wrap:wrap;gap:12px;">
        <?php foreach ($tags as $t): ?>
          <label style="display:flex;align-items:center;gap:6px;font-weight:400;margin:0;">
            <input type="checkbox" name="tags[]" value="<?= (int)$t['id'] ?>" <?= in_array($t['id'], $tagsSeleccionados) ? 'checked' : '' ?>>
            <?= htmlspecialchars($t['nombre']) ?>
          </label>
        <?php endforeach; ?>
      </div>

      <div class="section-title">Atributos de la categoría</div>
      <p class="hint">Cambian según la categoría elegida arriba. Los marcados con * son obligatorios para publicar.</p>
      <div id="bloques-atributos">
        <?php foreach ($mapaAtributos as $catId => $attrs): ?>
          <div class="attrs-categoria" data-cat="<?= (int)$catId ?>" style="display:none;">
            <?php foreach ($attrs as $a):
              $valorActual = $valoresAtributos[$a['id']] ?? null;
              $campo = 'attr_' . $a['id'];
            ?>
              <div class="attr-row">
                <label for="<?= $campo ?>"><?= htmlspecialchars($a['nombre']) ?><?= $a['requerido'] ? ' *' : '' ?><?= $a['unidad'] ? ' (' . htmlspecialchars($a['unidad']) . ')' : '' ?></label>
                <?php if ($a['tipo'] === 'booleano'): ?>
                  <input type="checkbox" id="<?= $campo ?>" name="<?= $campo ?>" <?= $valorActual ? 'checked' : '' ?>>
                <?php elseif ($a['tipo'] === 'seleccion'): ?>
                  <select id="<?= $campo ?>" name="<?= $campo ?>">
                    <option value="">—</option>
                    <?php foreach ($a['opciones'] as $op): ?>
                      <option value="<?= htmlspecialchars($op) ?>" <?= (string)$valorActual === (string)$op ? 'selected' : '' ?>><?= htmlspecialchars($op) ?></option>
                    <?php endforeach; ?>
                  </select>
                <?php elseif ($a['tipo'] === 'multi_seleccion'):
                  $seleccionadas = $valorActual ? array_map('trim', explode(',', $valorActual)) : [];
                ?>
                  <div style="display:flex;flex-wrap:wrap;gap:10px;">
                    <?php foreach ($a['opciones'] as $op): ?>
                      <label style="display:flex;align-items:center;gap:5px;font-weight:400;margin:0;">
                        <input type="checkbox" name="<?= $campo ?>[]" value="<?= htmlspecialchars($op) ?>" <?= in_array($op, $seleccionadas, true) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($op) ?>
                      </label>
                    <?php endforeach; ?>
                  </div>
                <?php elseif ($a['tipo'] === 'numero'): ?>
                  <input type="number" step="any" id="<?= $campo ?>" name="<?= $campo ?>" value="<?= htmlspecialchars((string)$valorActual) ?>">
                <?php else: ?>
                  <input type="text" id="<?= $campo ?>" name="<?= $campo ?>" value="<?= htmlspecialchars((string)$valorActual) ?>">
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="section-title">Variantes (precio y stock) *</div>
      <p class="hint">El precio es <strong>neto, sin IGV</strong> — en la tienda se muestra con IGV incluido (x1.18). Marcá cuál es la variante por defecto.</p>
      <div id="bloques-variantes">
        <?php
        $filas = $variantes ?: [['sku' => '', 'nombre_variante' => '', 'precio_neto' => '', 'precio_lista' => '', 'moneda' => 'PEN', 'stock' => 0, 'es_default' => 1]];
        foreach ($filas as $i => $v):
        ?>
          <div class="variant-row">
            <div class="row2">
              <div><label>SKU</label><input type="text" name="variante_sku[]" value="<?= htmlspecialchars($v['sku'] ?? '') ?>" placeholder="ZTK-LAP-HP-0001"></div>
              <div><label>Nombre variante</label><input type="text" name="variante_nombre[]" value="<?= htmlspecialchars($v['nombre_variante'] ?? '') ?>" placeholder="16GB / 512GB"></div>
              <div><label>Por defecto</label>
                <label style="font-weight:400;display:flex;align-items:center;gap:6px;height:38px;">
                  <input type="radio" name="variante_default" value="<?= $i ?>" <?= !empty($v['es_default']) ? 'checked' : '' ?>> Sí
                </label>
              </div>
            </div>
            <div class="row2">
              <div><label>Precio neto (sin IGV)</label><input type="number" step="0.0001" min="0" name="variante_precio_neto[]" value="<?= htmlspecialchars((string)($v['precio_neto'] ?? '')) ?>"></div>
              <div><label>Precio lista (tachado, opcional)</label><input type="number" step="0.0001" min="0" name="variante_precio_lista[]" value="<?= htmlspecialchars((string)($v['precio_lista'] ?? '')) ?>"></div>
              <div><label>Moneda</label>
                <select name="variante_moneda[]">
                  <option value="PEN" <?= ($v['moneda'] ?? 'PEN') === 'PEN' ? 'selected' : '' ?>>PEN</option>
                  <option value="USD" <?= ($v['moneda'] ?? '') === 'USD' ? 'selected' : '' ?>>USD</option>
                </select>
              </div>
              <div><label>Stock</label><input type="number" min="0" name="variante_stock[]" value="<?= htmlspecialchars((string)($v['stock'] ?? 0)) ?>"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="btn btn-sec" style="margin-top:12px;" onclick="zkAgregarVariante()">+ Agregar variante</button>

      <div class="section-title">Fotos</div>
      <?php if ($imagenes): ?>
        <div style="display:flex;flex-wrap:wrap;gap:14px;margin-top:10px;">
          <?php foreach ($imagenes as $img): ?>
            <div style="text-align:center;">
              <img src="../images/tienda-productos/<?= htmlspecialchars($img['url']) ?>" style="width:90px;height:90px;object-fit:cover;border-radius:8px;display:block;margin-bottom:4px;" alt="">
              <label style="font-weight:400;font-size:11px;display:flex;align-items:center;gap:4px;justify-content:center;">
                <input type="checkbox" name="eliminar_imagen[]" value="<?= (int)$img['id'] ?>"> Borrar
              </label>
              <?php if ($img['es_principal']): ?><span class="badge badge-ok" style="margin-top:4px;">Principal</span><?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <label for="imagen_principal" style="margin-top:16px;">Foto principal (reemplaza la actual)</label>
      <input type="file" id="imagen_principal" name="imagen_principal" accept=".jpg,.jpeg,.png,.webp">
      <label for="imagenes_extra">Fotos adicionales</label>
      <input type="file" id="imagenes_extra" name="imagenes_extra[]" accept=".jpg,.jpeg,.png,.webp" multiple>

      <button class="btn" type="submit" style="margin-top:28px;">Guardar</button>
      <a class="btn btn-sec" href="index.php">Cancelar</a>
    </form>
  </div>

<script>
function zkToggleAtributos() {
  var catId = document.getElementById('category_id').value;
  document.querySelectorAll('.attrs-categoria').forEach(function (el) {
    el.style.display = (el.dataset.cat === catId) ? 'block' : 'none';
  });
}
function zkToggleDiasEntrega() {
  var disp = document.getElementById('disponibilidad').value;
  document.getElementById('campo-dias-entrega').style.display = (disp === 'bajo_pedido') ? 'block' : 'none';
}
function zkAgregarVariante() {
  var bloque = document.getElementById('bloques-variantes');
  var fila = bloque.querySelector('.variant-row').cloneNode(true);
  fila.querySelectorAll('input[type=text], input[type=number]').forEach(function (i) { i.value = ''; });
  fila.querySelectorAll('input[type=radio]').forEach(function (i) { i.checked = false; });
  bloque.appendChild(fila);
}
zkToggleAtributos();
zkToggleDiasEntrega();
</script>
<?php include __DIR__ . '/_layout_end.php'; ?>
