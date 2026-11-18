<?php
/**
 * Reglas de negocio y helpers del catálogo (docs/ZENTEK360-catalogo-spec.md, sección 6).
 */

const IGV = 0.18;

/** Precio de vitrina = precio_neto * (1+IGV), convertido a PEN si la variante está en USD. */
function tienda_precio_vitrina(PDO $pdo, array $variante): float {
    $neto = (float)$variante['precio_neto'];
    if (($variante['moneda'] ?? 'PEN') === 'USD') {
        $tc = $pdo->query("SELECT usd_pen_venta FROM exchange_rates ORDER BY fecha DESC LIMIT 1")->fetchColumn();
        $neto *= $tc ? (float)$tc : 3.8; // fallback conservador si no hay tipo de cambio cargado
    }
    return round($neto * (1 + IGV), 2);
}

/** Devuelve [id => categoria] de todas las categorías, y un mapa id => hijos directos. */
function tienda_categorias_todas(PDO $pdo): array {
    return $pdo->query("SELECT * FROM categories ORDER BY nivel, orden, nombre")->fetchAll();
}

/** "Cómputo > Laptops > Laptops estándar" */
function tienda_categoria_ruta(PDO $pdo, int $categoryId): string {
    $partes = [];
    $id = $categoryId;
    $stmt = $pdo->prepare("SELECT nombre, parent_id FROM categories WHERE id = ?");
    $guard = 0;
    while ($id && $guard++ < 5) {
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) break;
        array_unshift($partes, $row['nombre']);
        $id = $row['parent_id'];
    }
    return implode(' > ', $partes);
}

/** slug completo /tienda/{departamento}/{categoria}/{subcategoria} hasta la categoría dada. */
function tienda_categoria_slugs_ruta(PDO $pdo, int $categoryId): array {
    $partes = [];
    $id = $categoryId;
    $stmt = $pdo->prepare("SELECT slug, parent_id FROM categories WHERE id = ?");
    $guard = 0;
    while ($id && $guard++ < 5) {
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) break;
        array_unshift($partes, $row['slug']);
        $id = $row['parent_id'];
    }
    return $partes;
}

/** IDs de la categoría + todos sus ancestros (para heredar atributos, regla de la spec). */
function tienda_categoria_y_ancestros(PDO $pdo, int $categoryId): array {
    $ids = [];
    $id = $categoryId;
    $stmt = $pdo->prepare("SELECT id, parent_id FROM categories WHERE id = ?");
    $guard = 0;
    while ($id && $guard++ < 5) {
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) break;
        $ids[] = (int)$row['id'];
        $id = $row['parent_id'];
    }
    return $ids;
}

/** Atributos efectivos (heredados de categoría + ancestros) con sus opciones, ordenados. */
function tienda_atributos_para_categoria(PDO $pdo, int $categoryId): array {
    $catIds = tienda_categoria_y_ancestros($pdo, $categoryId);
    if (!$catIds) return [];
    $in = implode(',', array_fill(0, count($catIds), '?'));
    $stmt = $pdo->prepare("
        SELECT a.*, ca.requerido, ca.orden AS ca_orden
        FROM category_attributes ca
        JOIN attributes a ON a.id = ca.attribute_id
        WHERE ca.category_id IN ($in)
        ORDER BY ca.orden, a.orden
    ");
    $stmt->execute($catIds);
    $attrs = $stmt->fetchAll();

    $optStmt = $pdo->prepare("SELECT valor FROM attribute_options WHERE attribute_id = ? ORDER BY orden");
    foreach ($attrs as &$a) {
        if (in_array($a['tipo'], ['seleccion', 'multi_seleccion'], true)) {
            $optStmt->execute([$a['id']]);
            $a['opciones'] = $optStmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $a['opciones'] = [];
        }
    }
    return $attrs;
}

/** [category_id => [atributos efectivos con opciones]] para TODAS las categorías de una sola pasada (evita N+1). */
function tienda_mapa_atributos_por_categoria(PDO $pdo): array {
    $cats = $pdo->query("SELECT id, parent_id FROM categories")->fetchAll();
    $parentOf = [];
    foreach ($cats as $c) $parentOf[(int)$c['id']] = $c['parent_id'] ? (int)$c['parent_id'] : null;

    $caRows = $pdo->query("
        SELECT ca.category_id, ca.requerido, ca.orden AS ca_orden, a.*
        FROM category_attributes ca JOIN attributes a ON a.id = ca.attribute_id
        ORDER BY ca.orden, a.orden
    ")->fetchAll();
    $porCategoriaPropia = [];
    foreach ($caRows as $row) {
        $porCategoriaPropia[(int)$row['category_id']][] = $row;
    }

    $opciones = [];
    foreach ($pdo->query("SELECT * FROM attribute_options ORDER BY orden")->fetchAll() as $o) {
        $opciones[(int)$o['attribute_id']][] = $o['valor'];
    }

    $mapa = [];
    foreach ($parentOf as $catId => $_) {
        $cadena = [];
        $id = $catId;
        $guard = 0;
        while ($id !== null && $guard++ < 5) {
            $cadena[] = $id;
            $id = $parentOf[$id] ?? null;
        }
        $efectivos = [];
        foreach ($cadena as $cid) {
            foreach ($porCategoriaPropia[$cid] ?? [] as $a) {
                $attrId = (int)$a['id'];
                if (isset($efectivos[$attrId])) continue; // la definición más cercana (hoja) gana
                $a['opciones'] = $opciones[$attrId] ?? [];
                $efectivos[$attrId] = $a;
            }
        }
        $mapa[$catId] = array_values($efectivos);
    }
    return $mapa;
}

/** Valores de atributos ya guardados para un producto: attribute_id => valor crudo. */
function tienda_valores_producto(PDO $pdo, int $productId): array {
    $stmt = $pdo->prepare("SELECT * FROM product_attribute_values WHERE product_id = ?");
    $stmt->execute([$productId]);
    $out = [];
    foreach ($stmt->fetchAll() as $row) {
        if ($row['valor_texto'] !== null) $out[$row['attribute_id']] = $row['valor_texto'];
        elseif ($row['valor_numero'] !== null) $out[$row['attribute_id']] = $row['valor_numero'];
        elseif ($row['valor_booleano'] !== null) $out[$row['attribute_id']] = (bool)$row['valor_booleano'];
    }
    return $out;
}

/** Valida que los atributos requerido=1 de la categoría tengan valor. Devuelve lista de nombres faltantes. */
function tienda_atributos_faltantes(array $atributosCategoria, array $valoresEnviados): array {
    $faltan = [];
    foreach ($atributosCategoria as $a) {
        if (!$a['requerido']) continue;
        $v = $valoresEnviados[$a['id']] ?? null;
        if ($v === null || $v === '') $faltan[] = $a['nombre'];
    }
    return $faltan;
}

/**
 * Regla 3: stock en 0 + disponibilidad stock_propio => agotado (automático).
 * Regla 4: bajo_pedido ignora el stock.
 * Solo alterna entre publicado/agotado; nunca toca borrador/descontinuado.
 */
function tienda_recalcular_estado(PDO $pdo, int $productId): void {
    $prod = $pdo->prepare("SELECT estado, disponibilidad FROM products WHERE id = ?");
    $prod->execute([$productId]);
    $p = $prod->fetch();
    if (!$p || !in_array($p['estado'], ['publicado', 'agotado'], true)) return;

    if ($p['disponibilidad'] !== 'stock_propio') {
        if ($p['estado'] === 'agotado') {
            $pdo->prepare("UPDATE products SET estado='publicado' WHERE id=?")->execute([$productId]);
        }
        return;
    }

    $stmtStock = $pdo->prepare("SELECT COALESCE(SUM(stock),0) FROM product_variants WHERE product_id = ? AND activo = 1");
    $stmtStock->execute([$productId]);
    $stockTotal = (int)$stmtStock->fetchColumn();

    $nuevoEstado = $stockTotal > 0 ? 'publicado' : 'agotado';
    if ($nuevoEstado !== $p['estado']) {
        $pdo->prepare("UPDATE products SET estado = ? WHERE id = ?")->execute([$nuevoEstado, $productId]);
    }
}

/**
 * Lista productos publicados (con al menos una variante activa — regla 2) con su
 * variante por defecto, imagen principal y datos de marca/categoría.
 * $filtros admite: category_id (incluye descendientes), brand_id, destacado, buscar, orden.
 */
function tienda_listar_productos(PDO $pdo, array $filtros = []): array {
    $where = ["p.estado IN ('publicado','agotado')", "v.id IS NOT NULL"];
    $params = [];

    if (!empty($filtros['category_id'])) {
        $ids = tienda_categoria_y_descendientes($pdo, (int)$filtros['category_id']);
        $in = implode(',', array_fill(0, count($ids), '?'));
        $where[] = "p.category_id IN ($in)";
        array_push($params, ...$ids);
    }
    if (!empty($filtros['brand_id'])) {
        $where[] = "p.brand_id = ?";
        $params[] = (int)$filtros['brand_id'];
    }
    if (!empty($filtros['destacado'])) {
        $where[] = "p.destacado = 1";
    }
    if (!empty($filtros['buscar'])) {
        $where[] = "(p.nombre LIKE ? OR p.descripcion_corta LIKE ?)";
        $like = '%' . $filtros['buscar'] . '%';
        $params[] = $like; $params[] = $like;
    }

    $orden = match ($filtros['orden'] ?? '') {
        'precio_asc'  => 'precio_neto ASC',
        'precio_desc' => 'precio_neto DESC',
        default       => 'p.destacado DESC, p.created_at DESC',
    };

    $sql = "
        SELECT p.*, c.nombre AS categoria_nombre, c.slug AS categoria_slug,
               b.nombre AS marca_nombre, b.slug AS marca_slug,
               v.id AS variant_id, v.sku, v.precio_neto, v.moneda, v.stock,
               (SELECT url FROM product_images WHERE product_id = p.id ORDER BY es_principal DESC, orden ASC LIMIT 1) AS imagen
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN brands b ON b.id = p.brand_id
        LEFT JOIN product_variants v ON v.id = (
            SELECT id FROM product_variants WHERE product_id = p.id AND activo = 1 ORDER BY es_default DESC, id ASC LIMIT 1
        )
        WHERE " . implode(' AND ', $where) . "
        ORDER BY $orden
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/** IDs de una categoría + todos sus descendientes (para listar por departamento/categoría). */
function tienda_categoria_y_descendientes(PDO $pdo, int $categoryId): array {
    $todas = $pdo->query("SELECT id, parent_id FROM categories")->fetchAll();
    $hijosDe = [];
    foreach ($todas as $c) $hijosDe[$c['parent_id'] ?: 0][] = (int)$c['id'];

    $ids = [$categoryId];
    $pendientes = [$categoryId];
    while ($pendientes) {
        $actual = array_pop($pendientes);
        foreach ($hijosDe[$actual] ?? [] as $hijo) {
            $ids[] = $hijo;
            $pendientes[] = $hijo;
        }
    }
    return $ids;
}

/** Mensaje de WhatsApp con nombre + SKU de la variante elegida. */
function tienda_whatsapp_link(string $nombreProducto, ?string $sku = null): string {
    $msg = 'Hola ZENTEK360, quiero cotizar: ' . $nombreProducto . ($sku ? " (SKU: $sku)" : '');
    return 'https://wa.me/' . TIENDA_WHATSAPP . '?text=' . rawurlencode($msg);
}

function tienda_slugify(string $texto): string {
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto) ?: $texto;
    $texto = strtolower($texto);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    return trim($texto, '-');
}
