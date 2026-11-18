<?php
/**
 * Plantilla de configuración de la Tienda.
 * Copia este archivo a config.tienda.php y rellena tus credenciales reales.
 * config.tienda.php NO debe subirse al repositorio (.gitignore).
 */

// Base de datos MySQL (cPanel → MySQL Databases)
define('DB_HOST', 'localhost');
define('DB_NAME', 'TU_BASE_DE_DATOS');
define('DB_USER', 'TU_USUARIO_BD');
define('DB_PASS', 'TU_CONTRASEÑA_BD');

// Acceso al panel admin (/admin/)
define('TIENDA_ADMIN_USER', 'admin');
// Genera el hash con: php -r "echo password_hash('tu_clave', PASSWORD_DEFAULT);"
define('TIENDA_ADMIN_PASS_HASH', '$2y$10$TU_HASH_AQUI');

// Número de WhatsApp para los botones "Comprar por WhatsApp" (formato: código de país + número, sin espacios ni +)
define('TIENDA_WHATSAPP', '51974667498');
