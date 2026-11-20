<?php
/**
 * Genera los .html públicos (HTML puro, sin PHP) a partir de src/ + partials/.
 * Correr después de editar partials/header.php o partials/footer.php:
 *   php build.php
 * Herramienta de desarrollo — no se despliega al servidor (ver .cpanel.yml).
 */

$paginas = [
    'src/index.html'                  => 'index.html',
    'src/software-rrhh-smeta.html'    => 'software-rrhh-smeta.html',
];

foreach ($paginas as $origen => $destino) {
    ob_start();
    include __DIR__ . '/' . $origen;
    $html = ob_get_clean();
    file_put_contents(__DIR__ . '/' . $destino, $html);
    echo "Generado: $destino\n";
}
