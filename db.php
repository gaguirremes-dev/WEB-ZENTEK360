<?php
/**
 * Conexión PDO + esquema de la Tienda (ver docs/ZENTEK360-catalogo-spec.md).
 * Crea las tablas si no existen y siembra categorías/marcas/atributos/tags
 * desde docs/taxonomia-zentek360.json la primera vez (tabla categories vacía).
 */

require_once __DIR__ . '/config.tienda.php';

function tienda_db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        parent_id INT UNSIGNED NULL,
        slug VARCHAR(80) NOT NULL UNIQUE,
        nombre VARCHAR(120) NOT NULL,
        nivel TINYINT UNSIGNED NOT NULL,
        orden INT NOT NULL DEFAULT 0,
        icono VARCHAR(80) NULL,
        descripcion TEXT NULL,
        meta_title VARCHAR(160) NULL,
        meta_description VARCHAR(200) NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        INDEX (parent_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS brands (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(80) NOT NULL UNIQUE,
        nombre VARCHAR(120) NOT NULL,
        logo_url VARCHAR(255) NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS attributes (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(80) NOT NULL UNIQUE,
        nombre VARCHAR(120) NOT NULL,
        tipo ENUM('texto','numero','booleano','seleccion','multi_seleccion') NOT NULL,
        unidad VARCHAR(20) NULL,
        es_filtrable TINYINT(1) NOT NULL DEFAULT 0,
        es_comparable TINYINT(1) NOT NULL DEFAULT 0,
        orden INT NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS attribute_options (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        attribute_id INT UNSIGNED NOT NULL,
        valor VARCHAR(120) NOT NULL,
        orden INT NOT NULL DEFAULT 0,
        INDEX (attribute_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS category_attributes (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        category_id INT UNSIGNED NOT NULL,
        attribute_id INT UNSIGNED NOT NULL,
        requerido TINYINT(1) NOT NULL DEFAULT 0,
        orden INT NOT NULL DEFAULT 0,
        UNIQUE KEY uniq_cat_attr (category_id, attribute_id),
        INDEX (attribute_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS tags (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(80) NOT NULL UNIQUE,
        nombre VARCHAR(120) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(160) NOT NULL UNIQUE,
        nombre VARCHAR(180) NOT NULL,
        category_id INT UNSIGNED NOT NULL,
        brand_id INT UNSIGNED NULL,
        tipo ENUM('fisico','digital','servicio') NOT NULL DEFAULT 'fisico',
        condicion ENUM('nuevo','reacondicionado','open_box') NOT NULL DEFAULT 'nuevo',
        mpn VARCHAR(80) NULL,
        descripcion_corta VARCHAR(300) NULL,
        descripcion_larga TEXT NULL,
        garantia_meses SMALLINT UNSIGNED NULL,
        disponibilidad ENUM('stock_propio','bajo_pedido','importacion') NOT NULL DEFAULT 'stock_propio',
        dias_entrega SMALLINT UNSIGNED NULL,
        estado ENUM('borrador','publicado','agotado','descontinuado') NOT NULL DEFAULT 'borrador',
        destacado TINYINT(1) NOT NULL DEFAULT 0,
        meta_title VARCHAR(160) NULL,
        meta_description VARCHAR(200) NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (category_id), INDEX (brand_id), INDEX (estado)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS product_variants (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT UNSIGNED NOT NULL,
        sku VARCHAR(60) NOT NULL UNIQUE,
        nombre_variante VARCHAR(120) NULL,
        codigo_barras VARCHAR(40) NULL,
        precio_neto DECIMAL(12,4) NOT NULL DEFAULT 0,
        precio_lista DECIMAL(12,4) NULL,
        moneda CHAR(3) NOT NULL DEFAULT 'PEN',
        unidad_medida VARCHAR(10) NOT NULL DEFAULT 'NIU',
        tipo_afectacion_igv VARCHAR(4) NOT NULL DEFAULT '10',
        stock INT NOT NULL DEFAULT 0,
        stock_minimo INT NOT NULL DEFAULT 0,
        peso_kg DECIMAL(8,3) NULL,
        es_default TINYINT(1) NOT NULL DEFAULT 0,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        INDEX (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS product_attribute_values (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT UNSIGNED NOT NULL,
        attribute_id INT UNSIGNED NOT NULL,
        valor_texto VARCHAR(255) NULL,
        valor_numero DECIMAL(14,4) NULL,
        valor_booleano TINYINT(1) NULL,
        attribute_option_id INT UNSIGNED NULL,
        UNIQUE KEY uniq_prod_attr (product_id, attribute_id),
        INDEX (attribute_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS product_tag (
        product_id INT UNSIGNED NOT NULL,
        tag_id INT UNSIGNED NOT NULL,
        PRIMARY KEY (product_id, tag_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS product_images (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT UNSIGNED NOT NULL,
        variant_id INT UNSIGNED NULL,
        url VARCHAR(255) NOT NULL,
        alt VARCHAR(160) NULL,
        orden INT NOT NULL DEFAULT 0,
        es_principal TINYINT(1) NOT NULL DEFAULT 0,
        INDEX (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS exchange_rates (
        fecha DATE PRIMARY KEY,
        usd_pen_compra DECIMAL(8,4) NOT NULL,
        usd_pen_venta DECIMAL(8,4) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $yaSembrado = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() > 0;
    if (!$yaSembrado) {
        tienda_sembrar_taxonomia($pdo);
    }

    return $pdo;
}

/** Siembra categorías, marcas, atributos, opciones, category_attributes, tags
 *  y 4 productos de ejemplo desde docs/taxonomia-zentek360.json. Solo corre
 *  una vez (categories vacía la dispara). */
function tienda_sembrar_taxonomia(PDO $pdo): void {
    $jsonPath = __DIR__ . '/docs/taxonomia-zentek360.json';
    if (!is_file($jsonPath)) return;
    $tax = json_decode(file_get_contents($jsonPath), true);
    if (!$tax) return;

    $pdo->beginTransaction();
    try {
        // Categorías: dos pasadas (insertar, luego resolver parent_id por slug)
        $slugToId = [];
        $insCat = $pdo->prepare("INSERT INTO categories (slug, nombre, nivel, orden) VALUES (?,?,?,?)");
        foreach ($tax['categories'] as $c) {
            $insCat->execute([$c['slug'], $c['nombre'], $c['nivel'], $c['orden']]);
            $slugToId[$c['slug']] = (int)$pdo->lastInsertId();
        }
        $updParent = $pdo->prepare("UPDATE categories SET parent_id = ? WHERE id = ?");
        foreach ($tax['categories'] as $c) {
            if (!empty($c['parent']) && isset($slugToId[$c['parent']])) {
                $updParent->execute([$slugToId[$c['parent']], $slugToId[$c['slug']]]);
            }
        }

        // Marcas
        $insBrand = $pdo->prepare("INSERT INTO brands (slug, nombre) VALUES (?,?)");
        $brandSlugToId = [];
        foreach ($tax['brands'] ?? [] as $b) {
            $insBrand->execute([$b['slug'], $b['nombre']]);
            $brandSlugToId[$b['slug']] = (int)$pdo->lastInsertId();
        }

        // Atributos + sus opciones
        $insAttr = $pdo->prepare("INSERT INTO attributes (codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (?,?,?,?,?,?,?)");
        $insOpt  = $pdo->prepare("INSERT INTO attribute_options (attribute_id, valor, orden) VALUES (?,?,?)");
        $codigoToId = [];
        $orden = 0;
        foreach ($tax['attributes'] ?? [] as $a) {
            $orden++;
            $insAttr->execute([$a['codigo'], $a['nombre'], $a['tipo'], $a['unidad'] ?? null, !empty($a['es_filtrable']) ? 1 : 0, !empty($a['es_comparable']) ? 1 : 0, $orden]);
            $attrId = (int)$pdo->lastInsertId();
            $codigoToId[$a['codigo']] = $attrId;
            foreach ($a['opciones'] ?? [] as $i => $valor) {
                $insOpt->execute([$attrId, $valor, $i]);
            }
        }

        // category_attributes
        $insCA = $pdo->prepare("INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (?,?,?,?)");
        foreach ($tax['category_attributes'] ?? [] as $ca) {
            if (!isset($slugToId[$ca['category_slug']]) || !isset($codigoToId[$ca['attribute_codigo']])) continue;
            $insCA->execute([$slugToId[$ca['category_slug']], $codigoToId[$ca['attribute_codigo']], !empty($ca['requerido']) ? 1 : 0, $ca['orden'] ?? 0]);
        }

        // Tags
        $insTag = $pdo->prepare("INSERT INTO tags (slug, nombre) VALUES (?,?)");
        $tagSlugToId = [];
        foreach ($tax['tags'] ?? [] as $t) {
            $insTag->execute([$t['slug'], $t['nombre']]);
            $tagSlugToId[$t['slug']] = (int)$pdo->lastInsertId();
        }

        // Productos de ejemplo (para que la tienda no arranque vacía)
        $insProd = $pdo->prepare("INSERT INTO products (slug, nombre, category_id, brand_id, tipo, condicion, mpn, garantia_meses, disponibilidad, dias_entrega, estado, destacado) VALUES (?,?,?,?,?,?,?,?,?,?,?,1)");
        $insVar  = $pdo->prepare("INSERT INTO product_variants (product_id, sku, nombre_variante, precio_neto, moneda, unidad_medida, tipo_afectacion_igv, stock, es_default) VALUES (?,?,?,?,?,?,?,?,?)");
        $insPAV  = $pdo->prepare("INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (?,?,?,?,?)");
        foreach ($tax['ejemplos_producto'] ?? [] as $ej) {
            if (!isset($slugToId[$ej['category_slug']])) continue;
            $brandId = $brandSlugToId[$ej['brand_slug']] ?? null;
            $insProd->execute([
                $ej['slug'], $ej['nombre'], $slugToId[$ej['category_slug']], $brandId,
                $ej['tipo'], $ej['condicion'], $ej['mpn'] ?? null, $ej['garantia_meses'] ?? null,
                $ej['disponibilidad'], $ej['dias_entrega'] ?? null, $ej['estado'] ?? 'publicado',
            ]);
            $productId = (int)$pdo->lastInsertId();

            foreach ($ej['variantes'] ?? [] as $v) {
                $insVar->execute([
                    $productId, $v['sku'], $v['nombre_variante'] ?? null, $v['precio_neto'],
                    $v['moneda'] ?? 'PEN', $v['unidad_medida'] ?? 'NIU', $v['tipo_afectacion_igv'] ?? '10',
                    $v['stock'] ?? 0, !empty($v['es_default']) ? 1 : 0,
                ]);
            }

            foreach ($ej['atributos'] ?? [] as $codigo => $valor) {
                if (!isset($codigoToId[$codigo])) continue;
                $esArray = is_array($valor);
                $esNumero = !$esArray && (is_int($valor) || is_float($valor));
                $esBool = !$esArray && is_bool($valor);
                $insPAV->execute([
                    $productId, $codigoToId[$codigo],
                    $esNumero || $esBool ? null : ($esArray ? implode(', ', $valor) : (string)$valor),
                    $esNumero ? $valor : null,
                    $esBool ? ($valor ? 1 : 0) : null,
                ]);
            }

            if (!empty($ej['tags'])) {
                foreach ($ej['tags'] as $tagSlug) {
                    if (isset($tagSlugToId[$tagSlug])) {
                        $pdo->prepare("INSERT IGNORE INTO product_tag (product_id, tag_id) VALUES (?,?)")->execute([$productId, $tagSlugToId[$tagSlug]]);
                    }
                }
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
