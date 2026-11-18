-- ============================================================
-- ZENTEK360 — Tienda: esquema + datos semilla
-- Generado automáticamente. OPCIONAL: db.php ya crea y siembra
-- todo esto solo con visitar el sitio una vez. Usa este archivo
-- solo si preferís cargarlo vos mismo desde phpMyAdmin.
-- ============================================================
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS brands (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(80) NOT NULL UNIQUE,
    nombre VARCHAR(120) NOT NULL,
    logo_url VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS attributes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(80) NOT NULL UNIQUE,
    nombre VARCHAR(120) NOT NULL,
    tipo ENUM('texto','numero','booleano','seleccion','multi_seleccion') NOT NULL,
    unidad VARCHAR(20) NULL,
    es_filtrable TINYINT(1) NOT NULL DEFAULT 0,
    es_comparable TINYINT(1) NOT NULL DEFAULT 0,
    orden INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS attribute_options (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attribute_id INT UNSIGNED NOT NULL,
    valor VARCHAR(120) NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    INDEX (attribute_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS category_attributes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    attribute_id INT UNSIGNED NOT NULL,
    requerido TINYINT(1) NOT NULL DEFAULT 0,
    orden INT NOT NULL DEFAULT 0,
    UNIQUE KEY uniq_cat_attr (category_id, attribute_id),
    INDEX (attribute_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(80) NOT NULL UNIQUE,
    nombre VARCHAR(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product_variants (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product_attribute_values (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    attribute_id INT UNSIGNED NOT NULL,
    valor_texto VARCHAR(255) NULL,
    valor_numero DECIMAL(14,4) NULL,
    valor_booleano TINYINT(1) NULL,
    attribute_option_id INT UNSIGNED NULL,
    UNIQUE KEY uniq_prod_attr (product_id, attribute_id),
    INDEX (attribute_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product_tag (
    product_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (product_id, tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NULL,
    url VARCHAR(255) NOT NULL,
    alt VARCHAR(160) NULL,
    orden INT NOT NULL DEFAULT 0,
    es_principal TINYINT(1) NOT NULL DEFAULT 0,
    INDEX (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS exchange_rates (
    fecha DATE PRIMARY KEY,
    usd_pen_compra DECIMAL(8,4) NOT NULL,
    usd_pen_venta DECIMAL(8,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- DATOS SEMILLA (desde docs/taxonomia-zentek360.json) ----------

-- Categorías
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (1, 'computo', 'Cómputo', 1, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (2, 'laptops', 'Laptops', 2, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (3, 'laptops-estandar', 'Laptops estándar', 3, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (4, 'laptops-2en1', 'Laptops 2 en 1 / Convertibles', 3, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (5, 'laptops-workstation', 'Workstations móviles', 3, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (6, 'desktops', 'Computadoras de escritorio', 2, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (7, 'pc-escritorio', 'PC de escritorio', 3, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (8, 'all-in-one', 'All-in-One', 3, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (9, 'mini-pc', 'Mini PC', 3, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (10, 'workstations', 'Workstations', 3, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (11, 'servidores', 'Servidores', 2, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (12, 'servidores-torre', 'Servidores torre', 3, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (13, 'servidores-rack', 'Servidores rack', 3, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (14, 'nas', 'Almacenamiento en red (NAS)', 3, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (15, 'tablets', 'Tablets', 2, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (16, 'componentes', 'Componentes y Partes', 1, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (17, 'procesadores', 'Procesadores', 2, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (18, 'placas-madre', 'Placas madre', 2, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (19, 'memorias-ram', 'Memorias RAM', 2, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (20, 'almacenamiento', 'Almacenamiento', 2, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (21, 'ssd-sata', 'SSD SATA', 3, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (22, 'ssd-nvme', 'SSD NVMe / M.2', 3, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (23, 'hdd-interno', 'Discos duros internos', 3, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (24, 'almacenamiento-externo', 'Almacenamiento externo', 3, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (25, 'tarjetas-graficas', 'Tarjetas gráficas', 2, 5);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (26, 'fuentes-poder', 'Fuentes de poder', 2, 6);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (27, 'gabinetes', 'Gabinetes', 2, 7);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (28, 'refrigeracion', 'Refrigeración', 2, 8);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (29, 'tarjetas-internas', 'Tarjetas y adaptadores internos', 2, 9);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (30, 'perifericos', 'Periféricos y Accesorios', 1, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (31, 'monitores', 'Monitores', 2, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (32, 'teclados', 'Teclados', 2, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (33, 'mouse', 'Mouse', 2, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (34, 'combos-teclado-mouse', 'Combos teclado y mouse', 2, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (35, 'audio', 'Audio', 2, 5);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (36, 'audifonos', 'Audífonos', 3, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (37, 'parlantes', 'Parlantes', 3, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (38, 'microfonos', 'Micrófonos', 3, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (39, 'camaras-web', 'Cámaras web', 2, 6);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (40, 'hubs-docking', 'Hubs y docking stations', 2, 7);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (41, 'mochilas-maletines', 'Mochilas y maletines', 2, 8);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (42, 'impresion', 'Impresión y Escaneo', 1, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (43, 'impresoras', 'Impresoras', 2, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (44, 'impresoras-tinta', 'Tinta continua', 3, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (45, 'impresoras-laser', 'Láser', 3, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (46, 'impresoras-matriciales', 'Matriciales', 3, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (47, 'impresoras-termicas', 'Térmicas / Tickets', 3, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (48, 'multifuncionales', 'Multifuncionales', 2, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (49, 'escaneres', 'Escáneres', 2, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (50, 'plotters', 'Plotters', 2, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (51, 'suministros-impresion', 'Suministros', 2, 5);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (52, 'tintas', 'Tintas', 3, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (53, 'toner', 'Tóner', 3, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (54, 'cintas', 'Cintas', 3, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (55, 'cabezales', 'Cabezales y repuestos', 3, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (56, 'papel-consumibles', 'Papel y consumibles', 2, 6);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (57, 'redes', 'Redes y Conectividad', 1, 5);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (58, 'routers', 'Routers', 2, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (59, 'switches', 'Switches', 2, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (60, 'access-points', 'Access Points', 2, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (61, 'antenas-enlaces', 'Antenas y enlaces', 2, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (62, 'adaptadores-red', 'Adaptadores de red', 2, 5);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (63, 'firewall', 'Firewall y seguridad perimetral', 2, 6);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (64, 'cableado', 'Cableado Estructurado', 1, 6);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (65, 'cable-red', 'Cable de red', 2, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (66, 'cable-utp', 'Cable UTP', 3, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (67, 'cable-ftp-stp', 'Cable FTP / STP', 3, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (68, 'fibra-optica', 'Fibra óptica', 3, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (69, 'conectores', 'Conectores', 2, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (70, 'plugs-rj45', 'Plugs RJ45', 3, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (71, 'jacks-rj45', 'Jacks RJ45', 3, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (72, 'capuchones', 'Capuchones y botas', 3, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (73, 'patch-cords', 'Patch cords', 2, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (74, 'patch-panels', 'Patch panels y organizadores', 2, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (75, 'racks', 'Racks y gabinetes de comunicaciones', 2, 5);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (76, 'canaletas', 'Canaletas y ductos', 2, 6);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (77, 'herramientas-red', 'Herramientas de red', 2, 7);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (78, 'ponchadoras', 'Ponchadoras / Crimpadoras', 3, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (79, 'insertadoras', 'Insertadoras de impacto', 3, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (80, 'testers-red', 'Testers y certificadores', 3, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (81, 'peladores', 'Peladores de cable', 3, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (82, 'seguridad', 'Videovigilancia y Seguridad', 1, 7);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (83, 'camaras-ip', 'Cámaras IP', 2, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (84, 'camaras-analogicas', 'Cámaras analógicas (HDCVI / TVI / AHD)', 2, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (85, 'grabadores', 'Grabadores', 2, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (86, 'nvr', 'NVR', 3, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (87, 'dvr', 'DVR', 3, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (88, 'control-acceso', 'Control de acceso y biométricos', 2, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (89, 'alarmas', 'Alarmas y sensores', 2, 5);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (90, 'accesorios-cctv', 'Accesorios CCTV', 2, 6);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (91, 'energia', 'Energía y Respaldo', 1, 8);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (92, 'ups', 'UPS', 2, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (93, 'estabilizadores', 'Estabilizadores', 2, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (94, 'supresores', 'Supresores de pico', 2, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (95, 'baterias', 'Baterías', 2, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (96, 'cables-poder', 'Cables de poder', 2, 5);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (97, 'electronica', 'Electrónica y Herramientas', 1, 9);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (98, 'instrumentos-medicion', 'Instrumentos de medición', 2, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (99, 'multimetros', 'Multímetros', 3, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (100, 'pinzas-amperimetricas', 'Pinzas amperimétricas', 3, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (101, 'soldadura', 'Soldadura', 2, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (102, 'cautines', 'Cautines', 3, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (103, 'estaciones-soldadura', 'Estaciones de soldadura', 3, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (104, 'insumos-soldadura', 'Estaño, flux y mallas', 3, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (105, 'herramientas-manuales', 'Herramientas manuales', 2, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (106, 'componentes-electronicos', 'Componentes electrónicos', 2, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (107, 'placas-desarrollo', 'Placas de desarrollo (Arduino, Raspberry Pi, ESP32)', 2, 5);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (108, 'fuentes-laboratorio', 'Fuentes de laboratorio', 2, 6);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (109, 'software', 'Software y Licencias', 1, 10);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (110, 'sistemas-operativos', 'Sistemas operativos', 2, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (111, 'ofimatica', 'Ofimática', 2, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (112, 'antivirus', 'Antivirus y seguridad', 2, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (113, 'licencias-servidor', 'Licencias de servidor', 2, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (114, 'servicios', 'Servicios ZENTEK360', 1, 11);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (115, 'hosting', 'Hosting cPanel', 2, 1);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (116, 'dominios', 'Dominios', 2, 2);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (117, 'desarrollo-web', 'Desarrollo web', 2, 3);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (118, 'automatizacion', 'Automatización n8n', 2, 4);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (119, 'agentes-ia', 'Agentes de IA', 2, 5);
INSERT INTO categories (id, slug, nombre, nivel, orden) VALUES (120, 'soporte', 'Soporte y mantenimiento', 2, 6);
UPDATE categories SET parent_id = 1 WHERE id = 2;
UPDATE categories SET parent_id = 2 WHERE id = 3;
UPDATE categories SET parent_id = 2 WHERE id = 4;
UPDATE categories SET parent_id = 2 WHERE id = 5;
UPDATE categories SET parent_id = 1 WHERE id = 6;
UPDATE categories SET parent_id = 6 WHERE id = 7;
UPDATE categories SET parent_id = 6 WHERE id = 8;
UPDATE categories SET parent_id = 6 WHERE id = 9;
UPDATE categories SET parent_id = 6 WHERE id = 10;
UPDATE categories SET parent_id = 1 WHERE id = 11;
UPDATE categories SET parent_id = 11 WHERE id = 12;
UPDATE categories SET parent_id = 11 WHERE id = 13;
UPDATE categories SET parent_id = 11 WHERE id = 14;
UPDATE categories SET parent_id = 1 WHERE id = 15;
UPDATE categories SET parent_id = 16 WHERE id = 17;
UPDATE categories SET parent_id = 16 WHERE id = 18;
UPDATE categories SET parent_id = 16 WHERE id = 19;
UPDATE categories SET parent_id = 16 WHERE id = 20;
UPDATE categories SET parent_id = 20 WHERE id = 21;
UPDATE categories SET parent_id = 20 WHERE id = 22;
UPDATE categories SET parent_id = 20 WHERE id = 23;
UPDATE categories SET parent_id = 20 WHERE id = 24;
UPDATE categories SET parent_id = 16 WHERE id = 25;
UPDATE categories SET parent_id = 16 WHERE id = 26;
UPDATE categories SET parent_id = 16 WHERE id = 27;
UPDATE categories SET parent_id = 16 WHERE id = 28;
UPDATE categories SET parent_id = 16 WHERE id = 29;
UPDATE categories SET parent_id = 30 WHERE id = 31;
UPDATE categories SET parent_id = 30 WHERE id = 32;
UPDATE categories SET parent_id = 30 WHERE id = 33;
UPDATE categories SET parent_id = 30 WHERE id = 34;
UPDATE categories SET parent_id = 30 WHERE id = 35;
UPDATE categories SET parent_id = 35 WHERE id = 36;
UPDATE categories SET parent_id = 35 WHERE id = 37;
UPDATE categories SET parent_id = 35 WHERE id = 38;
UPDATE categories SET parent_id = 30 WHERE id = 39;
UPDATE categories SET parent_id = 30 WHERE id = 40;
UPDATE categories SET parent_id = 30 WHERE id = 41;
UPDATE categories SET parent_id = 42 WHERE id = 43;
UPDATE categories SET parent_id = 43 WHERE id = 44;
UPDATE categories SET parent_id = 43 WHERE id = 45;
UPDATE categories SET parent_id = 43 WHERE id = 46;
UPDATE categories SET parent_id = 43 WHERE id = 47;
UPDATE categories SET parent_id = 42 WHERE id = 48;
UPDATE categories SET parent_id = 42 WHERE id = 49;
UPDATE categories SET parent_id = 42 WHERE id = 50;
UPDATE categories SET parent_id = 42 WHERE id = 51;
UPDATE categories SET parent_id = 51 WHERE id = 52;
UPDATE categories SET parent_id = 51 WHERE id = 53;
UPDATE categories SET parent_id = 51 WHERE id = 54;
UPDATE categories SET parent_id = 51 WHERE id = 55;
UPDATE categories SET parent_id = 42 WHERE id = 56;
UPDATE categories SET parent_id = 57 WHERE id = 58;
UPDATE categories SET parent_id = 57 WHERE id = 59;
UPDATE categories SET parent_id = 57 WHERE id = 60;
UPDATE categories SET parent_id = 57 WHERE id = 61;
UPDATE categories SET parent_id = 57 WHERE id = 62;
UPDATE categories SET parent_id = 57 WHERE id = 63;
UPDATE categories SET parent_id = 64 WHERE id = 65;
UPDATE categories SET parent_id = 65 WHERE id = 66;
UPDATE categories SET parent_id = 65 WHERE id = 67;
UPDATE categories SET parent_id = 65 WHERE id = 68;
UPDATE categories SET parent_id = 64 WHERE id = 69;
UPDATE categories SET parent_id = 69 WHERE id = 70;
UPDATE categories SET parent_id = 69 WHERE id = 71;
UPDATE categories SET parent_id = 69 WHERE id = 72;
UPDATE categories SET parent_id = 64 WHERE id = 73;
UPDATE categories SET parent_id = 64 WHERE id = 74;
UPDATE categories SET parent_id = 64 WHERE id = 75;
UPDATE categories SET parent_id = 64 WHERE id = 76;
UPDATE categories SET parent_id = 64 WHERE id = 77;
UPDATE categories SET parent_id = 77 WHERE id = 78;
UPDATE categories SET parent_id = 77 WHERE id = 79;
UPDATE categories SET parent_id = 77 WHERE id = 80;
UPDATE categories SET parent_id = 77 WHERE id = 81;
UPDATE categories SET parent_id = 82 WHERE id = 83;
UPDATE categories SET parent_id = 82 WHERE id = 84;
UPDATE categories SET parent_id = 82 WHERE id = 85;
UPDATE categories SET parent_id = 85 WHERE id = 86;
UPDATE categories SET parent_id = 85 WHERE id = 87;
UPDATE categories SET parent_id = 82 WHERE id = 88;
UPDATE categories SET parent_id = 82 WHERE id = 89;
UPDATE categories SET parent_id = 82 WHERE id = 90;
UPDATE categories SET parent_id = 91 WHERE id = 92;
UPDATE categories SET parent_id = 91 WHERE id = 93;
UPDATE categories SET parent_id = 91 WHERE id = 94;
UPDATE categories SET parent_id = 91 WHERE id = 95;
UPDATE categories SET parent_id = 91 WHERE id = 96;
UPDATE categories SET parent_id = 97 WHERE id = 98;
UPDATE categories SET parent_id = 98 WHERE id = 99;
UPDATE categories SET parent_id = 98 WHERE id = 100;
UPDATE categories SET parent_id = 97 WHERE id = 101;
UPDATE categories SET parent_id = 101 WHERE id = 102;
UPDATE categories SET parent_id = 101 WHERE id = 103;
UPDATE categories SET parent_id = 101 WHERE id = 104;
UPDATE categories SET parent_id = 97 WHERE id = 105;
UPDATE categories SET parent_id = 97 WHERE id = 106;
UPDATE categories SET parent_id = 97 WHERE id = 107;
UPDATE categories SET parent_id = 97 WHERE id = 108;
UPDATE categories SET parent_id = 109 WHERE id = 110;
UPDATE categories SET parent_id = 109 WHERE id = 111;
UPDATE categories SET parent_id = 109 WHERE id = 112;
UPDATE categories SET parent_id = 109 WHERE id = 113;
UPDATE categories SET parent_id = 114 WHERE id = 115;
UPDATE categories SET parent_id = 114 WHERE id = 116;
UPDATE categories SET parent_id = 114 WHERE id = 117;
UPDATE categories SET parent_id = 114 WHERE id = 118;
UPDATE categories SET parent_id = 114 WHERE id = 119;
UPDATE categories SET parent_id = 114 WHERE id = 120;

-- Marcas
INSERT INTO brands (id, slug, nombre) VALUES (1, 'hp', 'HP');
INSERT INTO brands (id, slug, nombre) VALUES (2, 'dell', 'Dell');
INSERT INTO brands (id, slug, nombre) VALUES (3, 'lenovo', 'Lenovo');
INSERT INTO brands (id, slug, nombre) VALUES (4, 'asus', 'ASUS');
INSERT INTO brands (id, slug, nombre) VALUES (5, 'acer', 'Acer');
INSERT INTO brands (id, slug, nombre) VALUES (6, 'apple', 'Apple');
INSERT INTO brands (id, slug, nombre) VALUES (7, 'epson', 'Epson');
INSERT INTO brands (id, slug, nombre) VALUES (8, 'canon', 'Canon');
INSERT INTO brands (id, slug, nombre) VALUES (9, 'brother', 'Brother');
INSERT INTO brands (id, slug, nombre) VALUES (10, 'dahua', 'Dahua');
INSERT INTO brands (id, slug, nombre) VALUES (11, 'hikvision', 'Hikvision');
INSERT INTO brands (id, slug, nombre) VALUES (12, 'tp-link', 'TP-Link');
INSERT INTO brands (id, slug, nombre) VALUES (13, 'ubiquiti', 'Ubiquiti');
INSERT INTO brands (id, slug, nombre) VALUES (14, 'mikrotik', 'MikroTik');
INSERT INTO brands (id, slug, nombre) VALUES (15, 'cisco', 'Cisco');
INSERT INTO brands (id, slug, nombre) VALUES (16, 'satra', 'Satra');
INSERT INTO brands (id, slug, nombre) VALUES (17, 'panduit', 'Panduit');
INSERT INTO brands (id, slug, nombre) VALUES (18, 'apc', 'APC');
INSERT INTO brands (id, slug, nombre) VALUES (19, 'forza', 'Forza');
INSERT INTO brands (id, slug, nombre) VALUES (20, 'kingston', 'Kingston');
INSERT INTO brands (id, slug, nombre) VALUES (21, 'western-digital', 'Western Digital');
INSERT INTO brands (id, slug, nombre) VALUES (22, 'seagate', 'Seagate');
INSERT INTO brands (id, slug, nombre) VALUES (23, 'logitech', 'Logitech');
INSERT INTO brands (id, slug, nombre) VALUES (24, 'generico', 'Genérico');

-- Atributos + opciones
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (1, 'procesador_marca', 'Marca de procesador', 'seleccion', NULL, 1, 1, 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (1, 1, 'Intel', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (2, 1, 'AMD', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (3, 1, 'Apple', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (4, 1, 'Qualcomm', 3);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (2, 'procesador_modelo', 'Modelo de procesador', 'texto', NULL, 0, 1, 2);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (3, 'ram_gb', 'Memoria RAM', 'numero', 'GB', 1, 1, 3);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (4, 'ram_tipo', 'Tipo de RAM', 'seleccion', NULL, 1, 1, 4);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (5, 4, 'DDR4', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (6, 4, 'DDR5', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (7, 4, 'LPDDR4X', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (8, 4, 'LPDDR5', 3);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (5, 'almacenamiento_gb', 'Almacenamiento', 'numero', 'GB', 1, 1, 5);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (6, 'almacenamiento_tipo', 'Tipo de almacenamiento', 'seleccion', NULL, 1, 1, 6);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (9, 6, 'SSD NVMe', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (10, 6, 'SSD SATA', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (11, 6, 'HDD', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (12, 6, 'eMMC', 3);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (7, 'pantalla_pulgadas', 'Tamaño de pantalla', 'numero', 'pulg', 1, 1, 7);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (8, 'resolucion', 'Resolución', 'seleccion', NULL, 1, 1, 8);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (13, 8, 'HD', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (14, 8, 'Full HD', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (15, 8, '2K / QHD', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (16, 8, '4K / UHD', 3);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (9, 'grafica', 'Tarjeta gráfica', 'texto', NULL, 1, 1, 9);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (10, 'sistema_operativo', 'Sistema operativo', 'seleccion', NULL, 1, 1, 10);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (17, 10, 'Windows 11 Home', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (18, 10, 'Windows 11 Pro', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (19, 10, 'FreeDOS', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (20, 10, 'Sin sistema operativo', 3);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (21, 10, 'macOS', 4);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (22, 10, 'Linux', 5);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (11, 'peso_kg', 'Peso', 'numero', 'kg', 0, 1, 11);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (12, 'tasa_refresco_hz', 'Tasa de refresco', 'numero', 'Hz', 1, 1, 12);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (13, 'panel_tipo', 'Tipo de panel', 'seleccion', NULL, 1, 1, 13);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (23, 13, 'IPS', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (24, 13, 'VA', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (25, 13, 'TN', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (26, 13, 'OLED', 3);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (14, 'puertos_video', 'Puertos de video', 'multi_seleccion', NULL, 1, 1, 14);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (27, 14, 'HDMI', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (28, 14, 'DisplayPort', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (29, 14, 'VGA', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (30, 14, 'USB-C', 3);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (31, 14, 'DVI', 4);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (15, 'altavoces_integrados', 'Altavoces integrados', 'booleano', NULL, 1, 1, 15);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (16, 'tecnologia_impresion', 'Tecnología de impresión', 'seleccion', NULL, 1, 1, 16);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (32, 16, 'Tinta continua', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (33, 16, 'Cartucho de tinta', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (34, 16, 'Láser', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (35, 16, 'Matricial', 3);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (36, 16, 'Térmica', 4);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (17, 'color', 'Impresión a color', 'seleccion', NULL, 1, 1, 17);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (37, 17, 'Color', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (38, 17, 'Monocromo', 1);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (18, 'funciones', 'Funciones', 'multi_seleccion', NULL, 1, 1, 18);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (39, 18, 'Imprime', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (40, 18, 'Copia', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (41, 18, 'Escanea', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (42, 18, 'Fax', 3);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (19, 'velocidad_ppm', 'Velocidad de impresión', 'numero', 'ppm', 1, 1, 19);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (20, 'formato_maximo', 'Formato máximo', 'seleccion', NULL, 1, 1, 20);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (43, 20, 'A4', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (44, 20, 'A3', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (45, 20, 'A2', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (46, 20, 'A1', 3);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (47, 20, 'A0', 4);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (21, 'conectividad', 'Conectividad', 'multi_seleccion', NULL, 1, 1, 21);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (48, 21, 'USB', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (49, 21, 'Ethernet', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (50, 21, 'Wi-Fi', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (51, 21, 'Bluetooth', 3);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (52, 21, 'NFC', 4);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (22, 'duplex', 'Impresión dúplex', 'booleano', NULL, 1, 1, 22);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (23, 'ciclo_mensual', 'Ciclo de trabajo mensual', 'numero', 'páginas', 0, 1, 23);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (24, 'categoria_cable', 'Categoría de cable', 'seleccion', NULL, 1, 1, 24);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (53, 24, 'Cat5e', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (54, 24, 'Cat6', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (55, 24, 'Cat6A', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (56, 24, 'Cat7', 3);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (57, 24, 'Cat8', 4);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (25, 'blindaje', 'Blindaje', 'seleccion', NULL, 1, 1, 25);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (58, 25, 'UTP', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (59, 25, 'FTP', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (60, 25, 'STP', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (61, 25, 'SFTP', 3);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (26, 'material_conductor', 'Material del conductor', 'seleccion', NULL, 1, 1, 26);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (62, 26, 'Cobre puro', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (63, 26, 'CCA (aluminio recubierto)', 1);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (27, 'uso', 'Uso', 'seleccion', NULL, 1, 1, 27);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (64, 27, 'Interior', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (65, 27, 'Exterior', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (66, 27, 'Directo a tierra', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (67, 27, 'Plenum', 3);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (28, 'longitud_m', 'Longitud', 'numero', 'm', 1, 1, 28);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (29, 'color_producto', 'Color', 'seleccion', NULL, 1, 0, 29);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (68, 29, 'Negro', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (69, 29, 'Blanco', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (70, 29, 'Gris', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (71, 29, 'Azul', 3);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (72, 29, 'Rojo', 4);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (73, 29, 'Amarillo', 5);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (74, 29, 'Verde', 6);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (75, 29, 'Naranja', 7);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (30, 'awg', 'Calibre AWG', 'numero', 'AWG', 1, 1, 30);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (31, 'certificacion', 'Certificación', 'texto', NULL, 0, 1, 31);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (32, 'presentacion', 'Presentación', 'seleccion', NULL, 1, 1, 32);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (76, 32, 'Unidad', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (77, 32, 'Bolsa 50', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (78, 32, 'Bolsa 100', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (79, 32, 'Caja 305 m', 3);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (80, 32, 'Caja 100 m', 4);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (81, 32, 'Rollo', 5);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (33, 'chapado', 'Chapado de contactos', 'seleccion', NULL, 1, 1, 33);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (82, 33, 'Oro 50u', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (83, 33, 'Oro 30u', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (84, 33, 'Oro 15u', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (85, 33, 'Níquel', 3);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (34, 'bota', 'Con bota protectora', 'booleano', NULL, 1, 1, 34);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (35, 'puertos_cantidad', 'Cantidad de puertos', 'numero', NULL, 1, 1, 35);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (36, 'puertos_velocidad', 'Velocidad de puertos', 'seleccion', NULL, 1, 1, 36);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (86, 36, '10/100', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (87, 36, 'Gigabit', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (88, 36, '2.5G', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (89, 36, '10G', 3);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (37, 'poe', 'PoE', 'seleccion', NULL, 1, 1, 37);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (90, 37, 'No', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (91, 37, 'PoE', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (92, 37, 'PoE+', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (93, 37, 'PoE++', 3);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (38, 'presupuesto_poe_w', 'Presupuesto PoE', 'numero', 'W', 1, 1, 38);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (39, 'administrable', 'Administrable', 'seleccion', NULL, 1, 1, 39);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (94, 39, 'No administrable', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (95, 39, 'Smart', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (96, 39, 'Administrable L2', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (97, 39, 'Administrable L3', 3);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (40, 'puertos_sfp', 'Puertos SFP', 'numero', NULL, 1, 1, 40);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (41, 'rackeable', 'Rackeable', 'booleano', NULL, 1, 1, 41);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (42, 'wifi_estandar', 'Estándar Wi-Fi', 'seleccion', NULL, 1, 1, 42);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (98, 42, 'Wi-Fi 4', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (99, 42, 'Wi-Fi 5', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (100, 42, 'Wi-Fi 6', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (101, 42, 'Wi-Fi 6E', 3);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (102, 42, 'Wi-Fi 7', 4);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (43, 'bandas', 'Bandas', 'seleccion', NULL, 1, 1, 43);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (103, 43, 'Banda simple', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (104, 43, 'Doble banda', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (105, 43, 'Triple banda', 2);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (44, 'velocidad_mbps', 'Velocidad inalámbrica', 'numero', 'Mbps', 1, 1, 44);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (45, 'puertos_lan', 'Puertos LAN', 'numero', NULL, 1, 1, 45);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (46, 'poe_alimentacion', 'Se alimenta por PoE', 'booleano', NULL, 1, 1, 46);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (47, 'montaje', 'Montaje', 'seleccion', NULL, 1, 1, 47);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (106, 47, 'Techo', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (107, 47, 'Pared', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (108, 47, 'Escritorio', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (109, 47, 'Rack', 3);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (110, 47, 'Poste', 4);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (111, 47, 'Exterior', 5);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (48, 'resolucion_mp', 'Resolución', 'numero', 'MP', 1, 1, 48);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (49, 'tecnologia_camara', 'Tecnología', 'seleccion', NULL, 1, 1, 49);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (112, 49, 'IP', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (113, 49, 'HDCVI', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (114, 49, 'TVI', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (115, 49, 'AHD', 3);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (116, 49, 'Analógica', 4);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (50, 'formato_camara', 'Formato', 'seleccion', NULL, 1, 1, 50);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (117, 50, 'Bullet', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (118, 50, 'Domo', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (119, 50, 'PTZ', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (120, 50, 'Ojo de pez', 3);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (121, 50, 'Cubo', 4);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (122, 50, 'Oculta', 5);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (51, 'lente_mm', 'Lente', 'numero', 'mm', 1, 1, 51);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (52, 'vision_nocturna_m', 'Visión nocturna', 'numero', 'm', 1, 1, 52);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (53, 'grado_ip', 'Grado de protección', 'seleccion', NULL, 1, 1, 53);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (123, 53, 'IP66', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (124, 53, 'IP67', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (125, 53, 'IK10', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (126, 53, 'Sin certificación', 3);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (54, 'audio', 'Audio integrado', 'booleano', NULL, 1, 1, 54);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (55, 'ranura_sd', 'Ranura microSD', 'booleano', NULL, 1, 1, 55);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (56, 'canales', 'Canales', 'numero', NULL, 1, 1, 56);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (57, 'bahias_disco', 'Bahías de disco', 'numero', NULL, 1, 1, 57);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (58, 'capacidad_max_tb', 'Capacidad máxima', 'numero', 'TB', 1, 1, 58);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (59, 'resolucion_max', 'Resolución máxima de grabación', 'seleccion', NULL, 1, 1, 59);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (127, 59, '1080p', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (128, 59, '4MP', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (129, 59, '5MP', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (130, 59, '4K / 8MP', 3);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (131, 59, '12MP', 4);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (60, 'puertos_poe', 'Puertos PoE', 'numero', NULL, 1, 1, 60);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (61, 'salidas_video', 'Salidas de video', 'multi_seleccion', NULL, 1, 1, 61);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (132, 61, 'HDMI', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (133, 61, 'VGA', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (134, 61, 'BNC', 2);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (62, 'capacidad_va', 'Capacidad', 'numero', 'VA', 1, 1, 62);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (63, 'potencia_w', 'Potencia', 'numero', 'W', 1, 1, 63);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (64, 'topologia', 'Topología', 'seleccion', NULL, 1, 1, 64);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (135, 64, 'Offline / Standby', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (136, 64, 'Línea interactiva', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (137, 64, 'Online doble conversión', 2);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (65, 'tomas_cantidad', 'Cantidad de tomas', 'numero', NULL, 1, 1, 65);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (66, 'tipo_toma', 'Tipo de toma', 'seleccion', NULL, 1, 1, 66);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (138, 66, 'Universal', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (139, 66, 'NEMA 5-15', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (140, 66, 'IEC C13', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (141, 66, 'Schuko', 3);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (67, 'autonomia_min', 'Autonomía', 'numero', 'min', 1, 1, 67);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (68, 'tipo_herramienta', 'Tipo de herramienta', 'seleccion', NULL, 1, 1, 68);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (142, 68, 'Ponchadora', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (143, 68, 'Insertadora', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (144, 68, 'Tester', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (145, 68, 'Certificador', 3);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (146, 68, 'Pelador', 4);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (147, 68, 'Kit', 5);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (69, 'compatibilidad', 'Compatibilidad', 'multi_seleccion', NULL, 1, 1, 69);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (148, 69, 'RJ45', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (149, 69, 'RJ11', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (150, 69, 'RJ12', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (151, 69, 'Coaxial', 3);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (152, 69, 'Fibra', 4);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (70, 'trinquete', 'Con trinquete', 'booleano', NULL, 1, 1, 70);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (71, 'material', 'Material', 'texto', NULL, 0, 1, 71);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (72, 'compatibilidad_modelos', 'Modelos compatibles', 'texto', NULL, 0, 1, 72);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (73, 'color_cartucho', 'Color del suministro', 'seleccion', NULL, 1, 1, 73);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (153, 73, 'Negro', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (154, 73, 'Cian', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (155, 73, 'Magenta', 2);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (156, 73, 'Amarillo', 3);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (157, 73, 'Tricolor', 4);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (158, 73, 'Foto', 5);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (74, 'rendimiento_paginas', 'Rendimiento', 'numero', 'páginas', 1, 1, 74);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (75, 'contenido_ml', 'Contenido', 'numero', 'ml', 1, 1, 75);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (76, 'tipo_suministro', 'Tipo de suministro', 'seleccion', NULL, 1, 1, 76);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (159, 76, 'Original', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (160, 76, 'Compatible', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (161, 76, 'Remanufacturado', 2);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (77, 'periodicidad', 'Periodicidad', 'seleccion', NULL, 1, 1, 77);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (162, 77, 'Pago único', 0);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (163, 77, 'Mensual', 1);
INSERT INTO attribute_options (id, attribute_id, valor, orden) VALUES (164, 77, 'Anual', 2);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (78, 'cuentas_correo', 'Cuentas de correo', 'numero', NULL, 1, 1, 78);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (79, 'dominios_incluidos', 'Dominios incluidos', 'numero', NULL, 1, 1, 79);
INSERT INTO attributes (id, codigo, nombre, tipo, unidad, es_filtrable, es_comparable, orden) VALUES (80, 'soporte_incluido', 'Soporte incluido', 'texto', NULL, 0, 1, 80);

-- Relación categoría → atributos (herencia)
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (2, 1, 1, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (2, 2, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (2, 3, 1, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (2, 4, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (2, 5, 1, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (2, 6, 0, 6);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (2, 10, 0, 7);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (2, 9, 0, 8);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (2, 7, 0, 9);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (2, 8, 0, 10);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (2, 11, 0, 11);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (6, 1, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (6, 2, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (6, 3, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (6, 4, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (6, 5, 0, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (6, 6, 0, 6);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (6, 10, 0, 7);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (6, 9, 0, 8);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (11, 1, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (11, 2, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (11, 3, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (11, 4, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (11, 5, 0, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (11, 6, 0, 6);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (11, 10, 0, 7);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (11, 9, 0, 8);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (11, 41, 0, 9);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (15, 7, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (15, 8, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (15, 5, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (15, 3, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (15, 10, 0, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (15, 11, 0, 6);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (19, 3, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (19, 4, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (19, 44, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (20, 5, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (20, 6, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (31, 7, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (31, 8, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (31, 12, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (31, 13, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (31, 14, 0, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (31, 15, 0, 6);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (43, 16, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (43, 17, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (43, 19, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (43, 20, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (43, 21, 0, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (43, 22, 0, 6);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (43, 23, 0, 7);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (48, 16, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (48, 17, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (48, 18, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (48, 19, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (48, 20, 0, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (48, 21, 0, 6);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (48, 22, 0, 7);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (48, 23, 0, 8);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (49, 20, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (49, 21, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (49, 19, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (49, 22, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (50, 16, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (50, 17, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (50, 20, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (50, 21, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (51, 72, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (51, 73, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (51, 74, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (51, 75, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (51, 76, 0, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (65, 24, 1, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (65, 25, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (65, 26, 1, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (65, 27, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (65, 28, 0, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (65, 29, 0, 6);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (65, 30, 0, 7);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (65, 31, 0, 8);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (65, 32, 1, 9);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (69, 24, 1, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (69, 25, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (69, 32, 1, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (69, 33, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (73, 24, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (73, 25, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (73, 28, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (73, 29, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (73, 34, 0, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (74, 35, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (74, 24, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (74, 41, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (75, 47, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (75, 71, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (77, 68, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (77, 69, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (77, 70, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (77, 71, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (59, 35, 1, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (59, 36, 1, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (59, 37, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (59, 38, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (59, 39, 0, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (59, 40, 0, 6);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (59, 41, 0, 7);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (58, 42, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (58, 43, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (58, 44, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (58, 45, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (58, 41, 0, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (60, 42, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (60, 43, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (60, 44, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (60, 46, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (60, 47, 0, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (60, 53, 0, 6);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (61, 44, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (61, 53, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (61, 47, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (61, 46, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (83, 48, 1, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (83, 50, 1, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (83, 51, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (83, 52, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (83, 53, 0, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (83, 46, 0, 6);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (83, 54, 0, 7);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (83, 55, 0, 8);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (84, 48, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (84, 49, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (84, 50, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (84, 51, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (84, 52, 0, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (84, 53, 0, 6);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (84, 54, 0, 7);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (85, 56, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (85, 57, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (85, 58, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (85, 59, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (85, 60, 0, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (85, 61, 0, 6);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (85, 41, 0, 7);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (92, 62, 1, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (92, 63, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (92, 64, 1, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (92, 65, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (92, 66, 0, 5);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (92, 67, 0, 6);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (92, 41, 0, 7);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (93, 63, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (93, 65, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (93, 66, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (114, 77, 0, 1);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (114, 5, 0, 2);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (114, 78, 0, 3);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (114, 79, 0, 4);
INSERT IGNORE INTO category_attributes (category_id, attribute_id, requerido, orden) VALUES (114, 80, 0, 5);

-- Etiquetas
INSERT INTO tags (id, slug, nombre) VALUES (1, 'gamer', 'Gamer');
INSERT INTO tags (id, slug, nombre) VALUES (2, 'empresarial', 'Empresarial');
INSERT INTO tags (id, slug, nombre) VALUES (3, 'home-office', 'Home office');
INSERT INTO tags (id, slug, nombre) VALUES (4, 'poe', 'PoE');
INSERT INTO tags (id, slug, nombre) VALUES (5, 'rackeable', 'Rackeable');
INSERT INTO tags (id, slug, nombre) VALUES (6, 'oferta', 'Oferta');
INSERT INTO tags (id, slug, nombre) VALUES (7, 'nuevo-ingreso', 'Nuevo ingreso');
INSERT INTO tags (id, slug, nombre) VALUES (8, 'mas-vendido', 'Más vendido');
INSERT INTO tags (id, slug, nombre) VALUES (9, 'exterior', 'Para exterior');

-- Productos de ejemplo (podés editarlos o borrarlos desde el panel admin)
INSERT INTO products (id, slug, nombre, category_id, brand_id, tipo, condicion, mpn, garantia_meses, disponibilidad, dias_entrega, estado, destacado) VALUES (1, 'laptop-hp-probook-450-g10', 'Laptop HP ProBook 450 G10 15.6"', 3, 1, 'fisico', 'nuevo', '8A4Y5LA', 12, 'bajo_pedido', 5, 'publicado', 1);
INSERT INTO product_variants (product_id, sku, nombre_variante, precio_neto, moneda, unidad_medida, tipo_afectacion_igv, stock, es_default) VALUES (1, 'ZTK-LAP-HP-0001', '16 GB / 512 GB', 2711.8644, 'PEN', 'NIU', '10', 0, 1);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (1, 1, 'Intel', NULL, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (1, 2, 'Core i5-1335U', NULL, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (1, 3, NULL, 16, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (1, 4, 'DDR4', NULL, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (1, 5, NULL, 512, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (1, 6, 'SSD NVMe', NULL, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (1, 7, NULL, 15.6, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (1, 8, 'Full HD', NULL, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (1, 10, 'Windows 11 Pro', NULL, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (1, 11, NULL, 1.79, NULL);
INSERT IGNORE INTO product_tag (product_id, tag_id) VALUES (1, 2);
INSERT INTO products (id, slug, nombre, category_id, brand_id, tipo, condicion, mpn, garantia_meses, disponibilidad, dias_entrega, estado, destacado) VALUES (2, 'cable-utp-cat6-satra-cobre-caja-305m', 'Cable UTP Cat6 Satra Cobre Puro Interior Caja 305 m', 66, 16, 'fisico', 'nuevo', NULL, 0, 'stock_propio', NULL, 'publicado', 1);
INSERT INTO product_variants (product_id, sku, nombre_variante, precio_neto, moneda, unidad_medida, tipo_afectacion_igv, stock, es_default) VALUES (2, 'ZTK-UTP-SAT-0001', 'Azul / Caja 305 m', 466.1017, 'PEN', 'BX', '10', 12, 1);
INSERT INTO product_variants (product_id, sku, nombre_variante, precio_neto, moneda, unidad_medida, tipo_afectacion_igv, stock, es_default) VALUES (2, 'ZTK-UTP-SAT-0002', 'Gris / Caja 305 m', 466.1017, 'PEN', 'BX', '10', 5, 0);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (2, 24, 'Cat6', NULL, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (2, 25, 'UTP', NULL, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (2, 26, 'Cobre puro', NULL, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (2, 27, 'Interior', NULL, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (2, 28, NULL, 305, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (2, 29, 'Azul', NULL, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (2, 30, NULL, 23, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (2, 32, 'Caja 305 m', NULL, NULL);
INSERT INTO products (id, slug, nombre, category_id, brand_id, tipo, condicion, mpn, garantia_meses, disponibilidad, dias_entrega, estado, destacado) VALUES (3, 'ponchadora-rj45-rj11-trinquete', 'Ponchadora Profesional RJ45 / RJ11 con Trinquete', 78, 24, 'fisico', 'nuevo', NULL, 3, 'stock_propio', NULL, 'publicado', 1);
INSERT INTO product_variants (product_id, sku, nombre_variante, precio_neto, moneda, unidad_medida, tipo_afectacion_igv, stock, es_default) VALUES (3, 'ZTK-PON-GEN-0001', NULL, 38.1356, 'PEN', 'NIU', '10', 24, 1);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (3, 68, 'Ponchadora', NULL, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (3, 69, 'RJ45, RJ11', NULL, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (3, 70, NULL, NULL, 1);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (3, 71, 'Acero con mango ergonómico', NULL, NULL);
INSERT INTO products (id, slug, nombre, category_id, brand_id, tipo, condicion, mpn, garantia_meses, disponibilidad, dias_entrega, estado, destacado) VALUES (4, 'hosting-cpanel-basico', 'Hosting cPanel — Plan Básico', 115, NULL, 'servicio', 'nuevo', NULL, NULL, 'stock_propio', NULL, 'publicado', 1);
INSERT INTO product_variants (product_id, sku, nombre_variante, precio_neto, moneda, unidad_medida, tipo_afectacion_igv, stock, es_default) VALUES (4, 'ZTK-HOS-ZTK-0001', 'Mensual', 66.9492, 'PEN', 'ZZ', '10', 0, 1);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (4, 77, 'Mensual', NULL, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (4, 5, NULL, 10, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (4, 78, NULL, 5, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (4, 79, NULL, 1, NULL);
INSERT INTO product_attribute_values (product_id, attribute_id, valor_texto, valor_numero, valor_booleano) VALUES (4, 80, 'Soporte por WhatsApp en horario de oficina', NULL, NULL);
