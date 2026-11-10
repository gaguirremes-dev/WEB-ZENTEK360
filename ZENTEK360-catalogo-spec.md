# ZENTEK360 — Especificación de Catálogo de Productos

Documento de referencia para implementación. Versión 1.0.

---

## 1. Principios de diseño

1. **La categoría responde "qué es" el producto.** Nada más.
2. **Máximo 3 niveles** de jerarquía: Departamento → Categoría → Subcategoría.
3. **Un producto tiene una sola categoría principal.** Si necesita estar en dos lugares, usa etiquetas.
4. **Marca, atributos y etiquetas nunca son categorías.**
5. **El SKU vive en la variante**, no en el producto.
6. **El precio neto (sin IGV) es la fuente de verdad.** El precio de vitrina se calcula.

### Antipatrones a evitar

| No hacer | Hacer |
|---|---|
| Categoría "Laptops HP" | Categoría "Laptops" + marca HP |
| Categoría "Laptops Gamer" | Categoría "Laptops" + etiqueta `gamer` |
| Categoría "Cable UTP Cat6" | Categoría "Cable UTP" + atributo `categoria_cable = Cat6` |
| Categoría "Ofertas" | Etiqueta `oferta` o flag en la variante |
| Precio con IGV incluido en BD | `precio_neto` + cálculo de IGV en presentación |

---

## 2. Árbol de categorías

Formato: `slug` — Nombre

### 01. `computo` — Cómputo
- `laptops` — Laptops
  - `laptops-estandar` — Laptops estándar
  - `laptops-2en1` — Laptops 2 en 1 / Convertibles
  - `laptops-workstation` — Workstations móviles
- `desktops` — Computadoras de escritorio
  - `pc-escritorio` — PC de escritorio
  - `all-in-one` — All-in-One
  - `mini-pc` — Mini PC
  - `workstations` — Workstations
- `servidores` — Servidores
  - `servidores-torre` — Servidores torre
  - `servidores-rack` — Servidores rack
  - `nas` — Almacenamiento en red (NAS)
- `tablets` — Tablets

### 02. `componentes` — Componentes y Partes
- `procesadores` — Procesadores
- `placas-madre` — Placas madre
- `memorias-ram` — Memorias RAM
- `almacenamiento` — Almacenamiento
  - `ssd-sata` — SSD SATA
  - `ssd-nvme` — SSD NVMe / M.2
  - `hdd-interno` — Discos duros internos
  - `almacenamiento-externo` — Almacenamiento externo
- `tarjetas-graficas` — Tarjetas gráficas
- `fuentes-poder` — Fuentes de poder
- `gabinetes` — Gabinetes
- `refrigeracion` — Refrigeración
- `tarjetas-internas` — Tarjetas y adaptadores internos

### 03. `perifericos` — Periféricos y Accesorios
- `monitores` — Monitores
- `teclados` — Teclados
- `mouse` — Mouse
- `combos-teclado-mouse` — Combos teclado y mouse
- `audio` — Audio
  - `audifonos` — Audífonos
  - `parlantes` — Parlantes
  - `microfonos` — Micrófonos
- `camaras-web` — Cámaras web
- `hubs-docking` — Hubs y docking stations
- `mochilas-maletines` — Mochilas y maletines

### 04. `impresion` — Impresión y Escaneo
- `impresoras` — Impresoras
  - `impresoras-tinta` — Tinta continua
  - `impresoras-laser` — Láser
  - `impresoras-matriciales` — Matriciales
  - `impresoras-termicas` — Térmicas / Tickets
- `multifuncionales` — Multifuncionales
- `escaneres` — Escáneres
- `plotters` — Plotters
- `suministros-impresion` — Suministros
  - `tintas` — Tintas
  - `toner` — Tóner
  - `cintas` — Cintas
  - `cabezales` — Cabezales y repuestos
- `papel-consumibles` — Papel y consumibles

### 05. `redes` — Redes y Conectividad
- `routers` — Routers
- `switches` — Switches
- `access-points` — Access Points
- `antenas-enlaces` — Antenas y enlaces
- `adaptadores-red` — Adaptadores de red
- `firewall` — Firewall y seguridad perimetral

### 06. `cableado` — Cableado Estructurado
- `cable-red` — Cable de red
  - `cable-utp` — Cable UTP
  - `cable-ftp-stp` — Cable FTP / STP
  - `fibra-optica` — Fibra óptica
- `conectores` — Conectores
  - `plugs-rj45` — Plugs RJ45
  - `jacks-rj45` — Jacks RJ45
  - `capuchones` — Capuchones y botas
- `patch-cords` — Patch cords
- `patch-panels` — Patch panels y organizadores
- `racks` — Racks y gabinetes de comunicaciones
- `canaletas` — Canaletas y ductos
- `herramientas-red` — Herramientas de red
  - `ponchadoras` — Ponchadoras / Crimpadoras
  - `insertadoras` — Insertadoras de impacto
  - `testers-red` — Testers y certificadores
  - `peladores` — Peladores de cable

### 07. `seguridad` — Videovigilancia y Seguridad
- `camaras-ip` — Cámaras IP
- `camaras-analogicas` — Cámaras analógicas (HDCVI / TVI / AHD)
- `grabadores` — Grabadores
  - `nvr` — NVR
  - `dvr` — DVR
- `control-acceso` — Control de acceso y biométricos
- `alarmas` — Alarmas y sensores
- `accesorios-cctv` — Accesorios CCTV

### 08. `energia` — Energía y Respaldo
- `ups` — UPS
- `estabilizadores` — Estabilizadores
- `supresores` — Supresores de pico
- `baterias` — Baterías
- `cables-poder` — Cables de poder

### 09. `electronica` — Electrónica y Herramientas
- `instrumentos-medicion` — Instrumentos de medición
  - `multimetros` — Multímetros
  - `pinzas-amperimetricas` — Pinzas amperimétricas
- `soldadura` — Soldadura
  - `cautines` — Cautines
  - `estaciones-soldadura` — Estaciones de soldadura
  - `insumos-soldadura` — Estaño, flux y mallas
- `herramientas-manuales` — Herramientas manuales
- `componentes-electronicos` — Componentes electrónicos
- `placas-desarrollo` — Placas de desarrollo (Arduino, Raspberry Pi, ESP32)
- `fuentes-laboratorio` — Fuentes de laboratorio

### 10. `software` — Software y Licencias
- `sistemas-operativos` — Sistemas operativos
- `ofimatica` — Ofimática
- `antivirus` — Antivirus y seguridad
- `licencias-servidor` — Licencias de servidor

### 11. `servicios` — Servicios ZENTEK360
- `hosting` — Hosting cPanel
- `dominios` — Dominios
- `desarrollo-web` — Desarrollo web
- `automatizacion` — Automatización n8n
- `agentes-ia` — Agentes de IA
- `soporte` — Soporte y mantenimiento

---

## 3. Modelo de datos

### `categories`
| Campo | Tipo | Nota |
|---|---|---|
| id | bigint PK | |
| parent_id | bigint FK null | null = departamento |
| slug | string unique | |
| nombre | string | |
| nivel | tinyint | 1, 2 o 3 |
| orden | int | orden de despliegue |
| icono | string null | |
| descripcion | text null | para SEO |
| meta_title | string null | |
| meta_description | string null | |
| activo | boolean | |

### `brands`
| Campo | Tipo |
|---|---|
| id | bigint PK |
| slug | string unique |
| nombre | string |
| logo_url | string null |
| activo | boolean |

### `products`
| Campo | Tipo | Nota |
|---|---|---|
| id | bigint PK | |
| slug | string unique | |
| nombre | string | |
| category_id | bigint FK | categoría principal, única |
| brand_id | bigint FK null | |
| tipo | enum | `fisico`, `digital`, `servicio` |
| condicion | enum | `nuevo`, `reacondicionado`, `open_box` |
| mpn | string null | part number del fabricante |
| descripcion_corta | string(300) | |
| descripcion_larga | text | |
| garantia_meses | int null | |
| disponibilidad | enum | `stock_propio`, `bajo_pedido`, `importacion` |
| dias_entrega | int null | para bajo pedido |
| estado | enum | `borrador`, `publicado`, `agotado`, `descontinuado` |
| destacado | boolean | |
| meta_title | string null | |
| meta_description | string null | |
| created_at / updated_at | timestamp | |

### `product_variants`
| Campo | Tipo | Nota |
|---|---|---|
| id | bigint PK | |
| product_id | bigint FK | |
| sku | string unique | ver convención abajo |
| nombre_variante | string null | "Negro / 512 GB" |
| codigo_barras | string null | EAN-13 |
| precio_neto | decimal(12,4) | **sin IGV — fuente de verdad** |
| precio_lista | decimal(12,4) null | precio tachado |
| moneda | char(3) | `PEN` o `USD` |
| unidad_medida | string | código catálogo 03 SUNAT |
| tipo_afectacion_igv | string | catálogo 07 SUNAT (`10` = gravado onerosa) |
| stock | int | |
| stock_minimo | int | |
| peso_kg | decimal null | |
| es_default | boolean | variante mostrada por defecto |
| activo | boolean | |

### `attributes`
| Campo | Tipo | Nota |
|---|---|---|
| id | bigint PK | |
| codigo | string unique | `ram_gb`, `categoria_cable` |
| nombre | string | "Memoria RAM" |
| tipo | enum | `texto`, `numero`, `booleano`, `seleccion`, `multi_seleccion` |
| unidad | string null | "GB", "m", "MP" |
| es_filtrable | boolean | aparece en filtros de la tienda |
| es_comparable | boolean | aparece en tabla comparativa |
| orden | int | |

### `attribute_options`
Valores permitidos para atributos de tipo `seleccion`.

| Campo | Tipo |
|---|---|
| id | bigint PK |
| attribute_id | bigint FK |
| valor | string |
| orden | int |

### `category_attributes`
Define qué atributos aplican a cada categoría. **Tabla clave del diseño.**

| Campo | Tipo | Nota |
|---|---|---|
| category_id | bigint FK | |
| attribute_id | bigint FK | |
| requerido | boolean | |
| orden | int | |

Regla de herencia: un producto hereda los atributos de su categoría **y de todos sus ancestros**.

### `product_attribute_values`
| Campo | Tipo |
|---|---|
| product_id | bigint FK |
| attribute_id | bigint FK |
| valor_texto | string null |
| valor_numero | decimal null |
| valor_booleano | boolean null |
| attribute_option_id | bigint FK null |

### `tags` y `product_tag`
| Campo | Tipo |
|---|---|
| id | bigint PK |
| slug | string unique |
| nombre | string |

Etiquetas iniciales sugeridas: `gamer`, `empresarial`, `home-office`, `poe`, `rackeable`, `oferta`, `nuevo-ingreso`, `mas-vendido`, `exterior`.

### `product_images`
| Campo | Tipo |
|---|---|
| id | bigint PK |
| product_id | bigint FK |
| variant_id | bigint FK null |
| url | string |
| alt | string |
| orden | int |
| es_principal | boolean |

### `exchange_rates`
| Campo | Tipo |
|---|---|
| fecha | date PK |
| usd_pen_compra | decimal(8,4) |
| usd_pen_venta | decimal(8,4) |

---

## 4. Convención de SKU

```
ZTK-{CAT}-{MARCA}-{NNNN}
```

- `ZTK` — prefijo fijo
- `{CAT}` — 3 letras de la categoría: `LAP`, `IMP`, `UTP`, `CAM`, `SWT`, `UPS`, `PON`
- `{MARCA}` — 2 a 4 letras: `HP`, `EPS`, `DAH`, `SAT`, `UBI`
- `{NNNN}` — correlativo de 4 dígitos por combinación categoría+marca

Ejemplos:
- `ZTK-LAP-HP-0012` — Laptop HP
- `ZTK-UTP-SAT-0003` — Cable UTP Satra
- `ZTK-CAM-DAH-0047` — Cámara Dahua

Para variantes, agrega sufijo: `ZTK-LAP-HP-0012-16GB`.

Guarda además el `mpn` (part number del fabricante) en `products`. Sirve para cotizar con distribuidores y para búsquedas.

---

## 5. Atributos por categoría

### Laptops / Desktops / All-in-One
`procesador_marca` (selección: Intel, AMD, Apple) · `procesador_modelo` (texto) · `ram_gb` (número, GB) · `ram_tipo` (selección: DDR4, DDR5, LPDDR5) · `almacenamiento_gb` (número, GB) · `almacenamiento_tipo` (selección: SSD NVMe, SSD SATA, HDD) · `pantalla_pulgadas` (número) · `resolucion` (selección) · `grafica` (texto) · `sistema_operativo` (selección) · `peso_kg` (número)

### Monitores
`pantalla_pulgadas` · `resolucion` · `tasa_refresco_hz` (número) · `panel_tipo` (selección: IPS, VA, TN, OLED) · `puertos_video` (multi: HDMI, DisplayPort, VGA, USB-C) · `altavoces_integrados` (booleano)

### Impresoras / Multifuncionales
`tecnologia_impresion` (selección: Tinta continua, Láser, Matricial, Térmica) · `color` (selección: Color, Monocromo) · `funciones` (multi: Imprime, Copia, Escanea, Fax) · `velocidad_ppm` (número) · `formato_maximo` (selección: A4, A3, A2) · `conectividad` (multi: USB, Ethernet, Wi-Fi, Bluetooth) · `duplex` (booleano) · `ciclo_mensual` (número)

### Cable UTP / FTP / STP
`categoria_cable` (selección: Cat5e, Cat6, Cat6A, Cat7) · `blindaje` (selección: UTP, FTP, STP, SFTP) · `material_conductor` (selección: Cobre puro, CCA) · `uso` (selección: Interior, Exterior, Directo a tierra) · `longitud_m` (número, m) · `color` (selección) · `awg` (número) · `certificacion` (texto)

### Plugs / Jacks RJ45
`categoria_cable` · `blindaje` · `presentacion` (selección: Unidad, Bolsa 100, Bolsa 50) · `chapado` (selección: Oro 50u, Oro 15u)

### Patch cords
`categoria_cable` · `longitud_m` · `color` · `blindaje` · `bota` (booleano)

### Switches
`puertos_cantidad` (número) · `puertos_velocidad` (selección: 10/100, Gigabit, 2.5G, 10G) · `poe` (selección: No, PoE, PoE+, PoE++) · `presupuesto_poe_w` (número, W) · `administrable` (selección: No administrable, Smart, Administrable L2, Administrable L3) · `puertos_sfp` (número) · `rackeable` (booleano)

### Routers / Access Points
`wifi_estandar` (selección: Wi-Fi 5, Wi-Fi 6, Wi-Fi 6E, Wi-Fi 7) · `bandas` (selección: Simple, Doble, Triple) · `velocidad_mbps` (número) · `puertos_lan` (número) · `poe_alimentacion` (booleano) · `montaje` (selección: Techo, Pared, Escritorio, Exterior)

### Cámaras IP / Analógicas
`resolucion_mp` (número, MP) · `tecnologia_camara` (selección: IP, HDCVI, TVI, AHD, Analógica) · `formato_camara` (selección: Bullet, Domo, PTZ, Ojo de pez, Cubo) · `lente_mm` (número, mm) · `vision_nocturna_m` (número, m) · `grado_ip` (selección: IP66, IP67, sin certificación) · `poe` (booleano) · `audio` (booleano) · `ranura_sd` (booleano)

### NVR / DVR
`canales` (número) · `bahias_disco` (número) · `capacidad_max_tb` (número, TB) · `resolucion_max` (selección) · `puertos_poe` (número) · `salidas_video` (multi: HDMI, VGA, BNC)

### UPS / Estabilizadores
`capacidad_va` (número, VA) · `potencia_w` (número, W) · `topologia` (selección: Offline, Línea interactiva, Online) · `tomas_cantidad` (número) · `tipo_toma` (selección) · `autonomia_min` (número, min) · `rackeable` (booleano)

### Herramientas de red
`tipo_herramienta` (selección: Ponchadora, Insertadora, Tester, Pelador, Kit) · `compatibilidad` (multi: RJ45, RJ11, RJ12, Coaxial) · `trinquete` (booleano) · `material` (texto)

### Suministros de impresión
`compatibilidad_modelos` (texto) · `color_cartucho` (selección: Negro, Cian, Magenta, Amarillo, Tricolor) · `rendimiento_paginas` (número) · `contenido_ml` (número, ml) · `tipo_suministro` (selección: Original, Compatible, Remanufacturado)

### Servicios ZENTEK360
`periodicidad` (selección: Único, Mensual, Anual) · `almacenamiento_gb` · `cuentas_correo` (número) · `dominios_incluidos` (número) · `soporte_incluido` (texto)

---

## 6. Reglas de negocio

1. **Precio de vitrina** = `precio_neto × 1.18`, redondeado a 2 decimales. Si `moneda = USD`, convertir primero con el tipo de cambio venta del día.
2. **Un producto sin variante activa** no se publica.
3. **Stock en cero + disponibilidad `stock_propio`** cambia el estado a `agotado` automáticamente.
4. **Disponibilidad `bajo_pedido`** ignora el stock y muestra `dias_entrega`.
5. **Los atributos `requerido = true`** de la categoría deben estar completos antes de publicar.
6. **Solo los atributos con `es_filtrable = true`** generan facetas en el listado de categoría.
7. **Servicios** (`tipo = servicio`) no manejan stock ni peso, y usan unidad de medida de servicios.

---

## 7. URLs

```
/tienda
/tienda/{departamento}
/tienda/{departamento}/{categoria}
/tienda/{departamento}/{categoria}/{subcategoria}
/producto/{slug}
/marca/{slug}
```

Filtros por query string: `?marca=hp&ram_gb=16&precio_min=2000&orden=precio_asc`

---

## 8. Orden de implementación sugerido

1. Migraciones de `categories`, `brands`, `attributes`, `attribute_options`, `category_attributes`
2. Seed del árbol de categorías y los atributos (usar `taxonomia-zentek360.json`)
3. Migraciones de `products`, `product_variants`, `product_attribute_values`, `product_images`
4. CRUD de administración con formulario de atributos dinámico según categoría
5. Listado público con facetas y buscador
6. Ficha de producto con variantes
7. Carrito y cotización por WhatsApp
8. Pasarela de pago (etapa posterior)
