<?php
/**
 * Guard de sesión para el panel admin. Incluir al inicio de cada página protegida.
 */

require_once __DIR__ . '/../config.tienda.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['tienda_admin'])) {
    header('Location: login.php');
    exit;
}
