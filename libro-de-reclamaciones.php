<?php
/**
 * Libro de Reclamaciones Virtual — ZENTEK360 S.A.C.S.
 * Conforme a la Ley N° 29571 (Código de Protección y Defensa del Consumidor)
 * y el D.S. N° 011-2011-PCM (modificado por D.S. 101-2021-PCM).
 */

// Session: started by reclamosIniciarSesion() (HttpOnly, SameSite=Lax, Secure on HTTPS).
date_default_timezone_set('America/Lima');

define('DOMINIO_PERMITIDO', 'zentek360.com');

define('EMPRESA_RAZON_SOCIAL', 'ZENTEK360 S.A.C.S.');
define('EMPRESA_RUC',          '20616110099');
define('EMPRESA_DIRECCION',    'AV. REPUBLICA DE CHILE NRO. 324 INT. 601 URB. SANTA BEATRIZ (EDIFICIO POLARIS) LIMA - LIMA - JESUS MARIA');
define('EMPRESA_EMAIL',        'reclamaciones@zentek360.com');

$smtpConfigFile = __DIR__ . '/config.smtp.php';
if (file_exists($smtpConfigFile)) require $smtpConfigFile;

if (!defined('SMTP_PORT'))           define('SMTP_PORT', 465);
if (!defined('SMTP_FROM_NAME'))      define('SMTP_FROM_NAME', 'ZENTEK360');
if (!defined('EMPRESA_NOTIF_EMAIL')) define('EMPRESA_NOTIF_EMAIL', 'reclamaciones@zentek360.com');

if (!defined('HCAPTCHA_SITE_KEY'))   define('HCAPTCHA_SITE_KEY',   '');
if (!defined('HCAPTCHA_SECRET_KEY')) define('HCAPTCHA_SECRET_KEY', '');

$recordsDir  = __DIR__ . '/reclamaciones';
$recordsFile = $recordsDir . '/records.json';
$lockFile    = $recordsDir . '/records.lock';

$logFile      = $recordsDir . '/security.log'; // directory is prepared by reclamosPrepararDirectorio()

$fpdfPath      = __DIR__ . '/lib/fpdf.php';
$fpdfAvailable = file_exists($fpdfPath);
if ($fpdfAvailable) require_once $fpdfPath;

// ── Generar PDF ───────────────────────────────────────────────────────────────
function generarPDFReclamo(array $d): string {
    if (!class_exists('FPDF')) return '';
    $c = fn(string $s): string => iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $s);

    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();

    // Cabecera navy con acento naranja
    $pdf->SetFillColor(11, 20, 44);
    $pdf->Rect(0, 0, 210, 6, 'F');
    $pdf->SetFillColor(243, 88, 14);
    $pdf->Rect(0, 6, 210, 3, 'F');
    $pdf->SetFillColor(11, 20, 44);
    $pdf->Rect(0, 9, 210, 30, 'F');

    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetXY(10, 12);
    $pdf->Cell(0, 8, $c('HOJA DE RECLAMACIÓN VIRTUAL'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 8.5);
    $pdf->SetXY(10, 21);
    $pdf->Cell(0, 5, $c('ZENTEK360 S.A.C.S. — Ley N° 29571 Código de Protección y Defensa del Consumidor'), 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(243, 88, 14);
    $pdf->SetXY(10, 28);
    $pdf->Cell(0, 5, $c('Código: ' . $d['codigo'] . '     Fecha: ' . $d['fecha']), 0, 1, 'C');

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetY(46);

    // Proveedor
    $pdf->SetFillColor(248, 249, 252);
    $pdf->SetDrawColor(220, 224, 230);
    $pdf->SetFont('Arial', 'B', 8.5);
    $pdf->SetTextColor(11, 20, 44);
    $pdf->Cell(0, 6, $c('PROVEEDOR'), 'LTR', 1, 'L', true);
    $pdf->SetFont('Arial', '', 8.5);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(0, 5, $c('Razón Social: ' . EMPRESA_RAZON_SOCIAL . '   RUC: ' . EMPRESA_RUC), 'LR', 'L');
    $pdf->MultiCell(0, 5, $c('Domicilio: ' . EMPRESA_DIRECCION), 'LBR', 'L');
    $pdf->Ln(3);

    $col1W = 48; $col2W = 132;

    foreach ([
        ['1. IDENTIFICACIÓN DEL CONSUMIDOR RECLAMANTE', [
            ['Nombre completo:', $d['nombres']],
            ['Documento:', $d['doc_tipo'] . ' N° ' . $d['doc_nro']],
            ['Domicilio:', $d['direccion'] . ', ' . $d['distrito'] . ' - ' . $d['provincia'] . ' (' . $d['departamento'] . ')'],
            ['Teléfono:', $d['telefono'] ?: '-'],
            ['Email:', $d['email']],
            ...($d['menor_edad'] ? [['Apoderado:', $d['apoderado_nombres'] . ' (' . $d['apoderado_doc_tipo'] . ' ' . $d['apoderado_doc_nro'] . ')']] : []),
        ]],
        ['2. IDENTIFICACIÓN DEL BIEN CONTRATADO', [
            ['Tipo de bien:', ucfirst($d['bien_tipo'])],
            ['Monto reclamado:', 'S/. ' . $d['monto']],
            ['Descripción:', $d['bien_desc'] ?: '-'],
        ]],
    ] as [$title, $rows]) {
        $pdf->SetFillColor(11, 20, 44);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(0, 6, $c($title), 0, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(255, 255, 255);
        foreach ($rows as [$label, $val]) {
            $pdf->SetFont('Arial', 'B', 8); $pdf->Cell($col1W, 5.5, $c($label), 'B', 0, 'L');
            $pdf->SetFont('Arial', '', 8);  $pdf->Cell($col2W, 5.5, $c($val),   'B', 1, 'L');
        }
        $pdf->Ln(3);
    }

    // Sección 3
    $pdf->SetFillColor(11, 20, 44);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 8.5);
    $pdf->Cell(0, 6, $c('3. DETALLE DE LA RECLAMACIÓN Y PEDIDO DEL CONSUMIDOR'), 0, 1, 'L', true);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 9); $pdf->Cell(42, 6, $c('Tipo de incidencia:'), 0, 0);
    $pdf->SetFont('Arial', '', 9);
    $esReclamo = strtolower($d['reclamo_tipo']) === 'reclamo';
    $pdf->Cell(5, 6, $esReclamo ? 'X' : 'O', 1, 0, 'C');
    $pdf->Cell(24, 6, $c(' Reclamo'), 0, 0);
    $pdf->Cell(5, 6, !$esReclamo ? 'X' : 'O', 1, 0, 'C');
    $pdf->Cell(24, 6, $c(' Queja'), 0, 1);
    $pdf->Ln(1);
    $pdf->SetFillColor(248, 249, 252);
    $pdf->SetFont('Arial', 'B', 8.5); $pdf->Cell(0, 5, $c('Detalle del hecho:'), 0, 1);
    $pdf->SetFont('Arial', '', 8.5);  $pdf->MultiCell(0, 5, $c($d['detalle']), 1, 'L', true);
    $pdf->Ln(2);
    $pdf->SetFont('Arial', 'B', 8.5); $pdf->Cell(0, 5, $c('Pedido del consumidor:'), 0, 1);
    $pdf->SetFont('Arial', '', 8.5);  $pdf->MultiCell(0, 5, $c($d['pedido']), 1, 'L', true);
    $pdf->Ln(5);

    $pdf->SetFont('Arial', '', 8.5);
    $pdf->Cell(90, 5, $c('Firma del Consumidor'), 'T', 0, 'C');
    $pdf->Cell(10, 5, '', 0, 0);
    $pdf->Cell(90, 5, $c('Firma del Proveedor'), 'T', 1, 'C');
    $pdf->Ln(3);

    $pdf->SetFont('Arial', 'I', 7.5);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->MultiCell(0, 4, $c('La formulación del reclamo no impide acudir a otras vías de solución de controversias ni es requisito previo para interponer una denuncia ante el INDECOPI.'), 0, 'L');
    $pdf->SetFont('Arial', 'BI', 7.5);
    $pdf->MultiCell(0, 4, $c('El proveedor debe dar respuesta al reclamo o queja en un plazo no mayor a quince (15) días hábiles improrrogables (D.S. 101-2021-PCM).'), 0, 'L');

    return $pdf->Output('', 'S');
}

// ══════════════════════════════════════════════════════════════════════════════
// HARDENING CORE - identical in every complaints book. Keep the six copies in sync.
// ══════════════════════════════════════════════════════════════════════════════

// Optional limits / switches (override them in config.smtp.php).
foreach ([
    'RATE_LIMIT_MAX'      => 1,     // submissions per IP inside RATE_LIMIT_WINDOW
    'RATE_LIMIT_WINDOW'   => 1800,  // seconds (30 min)
    'RATE_LIMIT_DAY_MAX'  => 5,     // submissions per IP per 24 h
    'SITE_LIMIT_HOUR_MAX' => 20,    // site-wide submissions per hour
    'SITE_LIMIT_DAY_MAX'  => 80,    // site-wide submissions per 24 h
    'SMTP_VERIFY_TLS'     => true,  // verify the SMTP server certificate
    'IP_HASH_SALT'        => '',    // optional; empty = random per-install key file
] as $reclamosK => $reclamosV) {
    if (!defined($reclamosK)) define($reclamosK, $reclamosV);
}

// Cloudflare published ranges (https://www.cloudflare.com/ips/). Review from time to time.
define('RECLAMOS_CF_RANGES', [
    '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
    '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
    '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
    '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
    '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
    '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
]);
const RECLAMOS_MAX_BODY  = 65536; // reject bigger POST bodies (bytes)
const RECLAMOS_MIN_AGE   = 5;     // seconds after render before a submit is accepted
const RECLAMOS_MAX_AGE   = 7200;  // seconds after render before the form expires

function reclamosCabecerasSeguridad(): void {
    if (headers_sent()) return;
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header('Cache-Control: no-store');
    header_remove('X-Powered-By');
}

function reclamosIniciarSesion(): void {
    if (session_status() !== PHP_SESSION_NONE) return;
    $https = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
          || (int)($_SERVER['SERVER_PORT'] ?? 0) === 443
          || (strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https' && reclamosEsCloudflare((string)($_SERVER['REMOTE_ADDR'] ?? '')));
    @ini_set('session.use_strict_mode', '1');
    @ini_set('session.use_only_cookies', '1');
    session_cache_limiter(''); // keep our own Cache-Control header
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// ── Client IP: REMOTE_ADDR, or CF-Connecting-IP only when the peer is Cloudflare ──
function reclamosNormalizarIp(string $ip): string {
    $ip = trim($ip);
    if (preg_match('/^::ffff:(\d{1,3}(?:\.\d{1,3}){3})$/i', $ip, $m)) return $m[1];
    return $ip;
}
function reclamosIpEnCidr(string $ip, string $cidr): bool {
    $parts  = explode('/', $cidr, 2);
    $ipBin  = @inet_pton($ip);
    $netBin = @inet_pton($parts[0]);
    if ($ipBin === false || $netBin === false || strlen($ipBin) !== strlen($netBin)) return false;
    $bits  = isset($parts[1]) ? (int)$parts[1] : strlen($ipBin) * 8;
    $bytes = intdiv($bits, 8);
    $rem   = $bits % 8;
    if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($netBin, 0, $bytes)) return false;
    if ($rem === 0) return true;
    $mask = (0xFF << (8 - $rem)) & 0xFF;
    return (ord($ipBin[$bytes]) & $mask) === (ord($netBin[$bytes]) & $mask);
}
function reclamosEsCloudflare(string $ip): bool {
    $ip = reclamosNormalizarIp($ip);
    if (filter_var($ip, FILTER_VALIDATE_IP) === false) return false;
    foreach (RECLAMOS_CF_RANGES as $cidr) {
        if (reclamosIpEnCidr($ip, $cidr)) return true;
    }
    return false;
}
function reclamosClientIp(): string {
    $remote = reclamosNormalizarIp((string)($_SERVER['REMOTE_ADDR'] ?? ''));
    if (filter_var($remote, FILTER_VALIDATE_IP) === false) return '0.0.0.0';
    if (reclamosEsCloudflare($remote)) {
        $cf = reclamosNormalizarIp((string)($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
        if ($cf !== '' && filter_var($cf, FILTER_VALIDATE_IP) !== false) return $cf;
    }
    return $remote;
}

// ── Records directory: protected, never listable, per-install secret key ─────
function reclamosPrepararDirectorio(string $dir): bool {
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) return false;
    $ht  = $dir . '/.htaccess';
    $cur = is_file($ht) ? (string)@file_get_contents($ht) : '';
    if (strpos($cur, 'Require all denied') === false || strpos($cur, 'Options -Indexes') === false) {
        @file_put_contents($ht,
            "# Protected records directory: never served over HTTP.\n" .
            "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n" .
            "<IfModule !mod_authz_core.c>\n    Order allow,deny\n    Deny from all\n</IfModule>\n" .
            "Options -Indexes\n");
    }
    if (!is_file($dir . '/index.html')) @file_put_contents($dir . '/index.html', '');
    return is_writable($dir);
}
function reclamosClaveInstalacion(string $dir): string {
    static $cache = [];
    if (isset($cache[$dir])) return $cache[$dir];
    $path = $dir . '/install.key';
    $key  = is_file($path) ? trim((string)@file_get_contents($path)) : '';
    if (strlen($key) < 32) {
        $fp = @fopen($path, 'c+');
        $key = '';
        if ($fp && flock($fp, LOCK_EX)) {
            $existing = trim((string)stream_get_contents($fp));
            if (strlen($existing) >= 32) {
                $key = $existing;
            } else {
                $new = bin2hex(random_bytes(32));
                ftruncate($fp, 0); rewind($fp); fwrite($fp, $new); fflush($fp);
                @chmod($path, 0600);
                $key = $new;
            }
            flock($fp, LOCK_UN);
        }
        if ($fp) fclose($fp);
    }
    return $cache[$dir] = $key;
}
// Salted SHA-256 of the IP: the only form in which an IP is ever stored.
function reclamosHashIp(string $ip, string $dir): string {
    $salt = (string)IP_HASH_SALT;
    if ($salt === '') $salt = reclamosClaveInstalacion($dir);
    if ($salt === '') return '';
    return hash('sha256', $salt . '|' . $ip);
}
function reclamosHuellaContenido(string $dir, string $detalle, string $email): string {
    $norm = mb_strtolower(preg_replace('/\s+/u', ' ', trim($detalle)), 'UTF-8') . '|' . strtolower($email);
    return hash_hmac('sha256', $norm, reclamosClaveInstalacion($dir));
}

// ── Logging (never secrets, bodies or personal data) ─────────────────────────
function logSecurityEvent(string $logFile, string $event, array $context = []): void {
    foreach (['email','nombres','doc_nro','apoderado_nombres','apoderado_doc_nro','detalle','pedido'] as $k) unset($context[$k]);
    if (@is_file($logFile) && @filesize($logFile) > 2097152) @rename($logFile, $logFile . '.1'); // crude rotation
    $line = date('Y-m-d H:i:s') . ' | ' . $event . ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR) . PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

// ── Time trap: HMAC-signed render timestamp, bound to the session CSRF token ──
function reclamosTokenTiempo(string $dir, string $csrf): string {
    $key = reclamosClaveInstalacion($dir);
    if ($key === '' || $csrf === '') return '';
    $ts = (string)time();
    return $ts . '.' . hash_hmac('sha256', 'ts|' . $ts . '|' . $csrf, $key);
}
// Returns '' when valid, otherwise a short reason code.
function reclamosVerificarTiempo(string $dir, string $csrf, string $token): string {
    $key = reclamosClaveInstalacion($dir);
    if ($key === '' || $csrf === '') return 'TT_NO_KEY';
    if (!preg_match('/^(\d{9,11})\.([a-f0-9]{64})$/', $token, $m)) return 'TT_MALFORMED';
    $expected = hash_hmac('sha256', 'ts|' . $m[1] . '|' . $csrf, $key);
    if (!hash_equals($expected, $m[2])) return 'TT_BAD_SIGNATURE';
    $age = time() - (int)$m[1];
    if ($age < RECLAMOS_MIN_AGE) return 'TT_TOO_FAST';
    if ($age > RECLAMOS_MAX_AGE) return 'TT_EXPIRED';
    return '';
}

// ── Origin / Referer: compare parsed hosts, never substrings ─────────────────
function reclamosHostPermitido(string $host): bool {
    $host = rtrim(strtolower(trim($host)), '.');
    $dom  = strtolower((string)DOMINIO_PERMITIDO);
    return $host !== '' && ($host === $dom || $host === 'www.' . $dom);
}
function reclamosOrigenValido(): bool {
    $origin  = trim((string)($_SERVER['HTTP_ORIGIN']  ?? ''));
    $referer = trim((string)($_SERVER['HTTP_REFERER'] ?? ''));
    if ($origin !== '') return reclamosHostPermitido((string)parse_url($origin, PHP_URL_HOST)); // "null" -> rejected
    if ($referer !== '') return reclamosHostPermitido((string)parse_url($referer, PHP_URL_HOST));
    return true; // some privacy setups strip both; CSRF + captcha + time trap still apply
}

// ── hCaptcha: fails closed (no keys / no answer / bad hostname => false) ─────
function verificarHCaptcha(string $token, string $secretKey): bool {
    if ($token === '' || strlen($token) > 4096 || $secretKey === '') return false;
    $post = ['secret' => $secretKey, 'response' => $token];
    if (HCAPTCHA_SITE_KEY !== '') $post['sitekey'] = HCAPTCHA_SITE_KEY;
    $ctx = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query($post),
        'timeout' => 10,
    ]]);
    $result = @file_get_contents('https://hcaptcha.com/siteverify', false, $ctx);
    if ($result === false) return false;
    $data = json_decode($result, true);
    if (!is_array($data) || ($data['success'] ?? false) !== true) return false;
    if (isset($data['hostname']) && is_string($data['hostname']) && $data['hostname'] !== '') {
        if (!reclamosHostPermitido($data['hostname'])) return false;
    }
    return true;
}

// ── Input helpers ─────────────────────────────────────────────────────────────
function sanitizarCabecera(string $s): string {
    $s = str_ireplace(['%0d', '%0a'], '', $s);
    return trim((string)preg_replace('/[\x00-\x1F\x7F]+/', '', $s));
}
function esc(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
// Strips tags, control chars and invisible bidi/format chars; enforces UTF-8 and max length.
function limpiarTexto(?string $s, int $maxLen = 500, bool $multiline = false): string {
    if ($s === null || $s === '') return '';
    if (!mb_check_encoding($s, 'UTF-8')) return '';
    $s = strip_tags($s);
    $s = str_replace(["\r\n", "\r"], "\n", $s);
    $s = $multiline
        ? (string)preg_replace('/[\x00-\x09\x0B-\x1F\x7F]/', '', $s)
        : (string)preg_replace('/[\x00-\x1F\x7F]+/', ' ', $s);
    $s = (string)preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}\x{FEFF}]/u', '', $s);
    if ($multiline) $s = (string)preg_replace("/\n{3,}/", "\n\n", $s);
    return mb_substr(trim($s), 0, $maxLen, 'UTF-8');
}
// Valid address (<= 254 chars, no header-breaking chars) or '' when invalid.
function reclamosEmail($raw): string {
    $e = trim((string)$raw);
    if ($e === '' || strlen($e) > 254 || preg_match('/[\x00-\x20\x7F<>,;"\'\\\\]/', $e)) return '';
    $ok = filter_var($e, FILTER_VALIDATE_EMAIL);
    return $ok === false ? '' : (string)$ok;
}
// Spam heuristics: more than 2 URLs in the free text, <a ...> tags or [url=] BBCode.
function reclamosContenidoSospechoso(array $textos): bool {
    $all = implode("\n", array_map('strval', $textos));
    if (preg_match('/<\s*a[\s>]/i', $all) || preg_match('/\[\s*(?:url|link)\b/i', $all)) return true;
    return preg_match_all('~(?:https?://|ftp://|www\.)\S+~i', $all) > 2;
}

// ── Request guards. Returns '' when everything passes, else a Spanish message ─
function reclamosGuardarSolicitud(string $dir, string $logFile, string $ipHash): string {
    $log = function (string $ev, array $ctx = []) use ($logFile, $ipHash): void {
        logSecurityEvent($logFile, $ev, $ctx + ['ip_hash' => $ipHash]);
    };
    $contacto = defined('EMPRESA_EMAIL') ? ' o escríbenos a ' . EMPRESA_EMAIL : '';

    if ($ipHash === '') {
        $log('NO_KEY_AVAILABLE');
        return 'No pudimos procesar tu solicitud en este momento. Inténtalo más tarde' . $contacto . '.';
    }
    if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > RECLAMOS_MAX_BODY) {
        $log('PAYLOAD_TOO_LARGE');
        http_response_code(413);
        return 'La solicitud es demasiado grande. Reduce el texto e inténtalo de nuevo.';
    }
    // Honeypot: off-screen field, humans never fill it. Answer 200 and do nothing.
    if (!empty($_POST['hp_website'] ?? '')) {
        $log('HONEYPOT_TRIGGERED');
        http_response_code(200);
        exit;
    }
    foreach ($_POST as $v) {
        if (!is_string($v) || !mb_check_encoding($v, 'UTF-8')) {
            $log('BAD_INPUT_ENCODING');
            return 'El formulario contiene datos no válidos. Recarga la página e inténtalo de nuevo.';
        }
    }
    $csrfPost = (string)($_POST['csrf_token'] ?? '');
    $csrfSess = (string)($_SESSION['csrf_token'] ?? '');
    if ($csrfPost === '' || $csrfSess === '' || !hash_equals($csrfSess, $csrfPost)) {
        $log('CSRF_FAILED');
        return 'Token de seguridad inválido. Recarga la página e inténtalo de nuevo.';
    }
    $tt = reclamosVerificarTiempo($dir, $csrfSess, (string)($_POST['form_ts'] ?? ''));
    if ($tt !== '') {
        $log('TIME_TRAP', ['reason' => $tt]);
        return $tt === 'TT_TOO_FAST'
            ? 'Enviaste el formulario demasiado rápido. Espera unos segundos e inténtalo de nuevo.'
            : 'El formulario expiró o no es válido. Revisa tus datos e inténtalo de nuevo.';
    }
    if (!reclamosOrigenValido()) {
        $log('ORIGIN_REJECTED');
        return 'Solicitud rechazada. Recarga la página e inténtalo de nuevo.';
    }
    if (reclamosContenidoSospechoso([$_POST['detalle'] ?? '', $_POST['pedido'] ?? '', $_POST['bien_desc'] ?? '', $_POST['servicio'] ?? ''])) {
        $log('SPAM_CONTENT');
        return 'Tu mensaje contiene enlaces o formato no permitido. Quita los enlaces e inténtalo de nuevo.';
    }
    if (HCAPTCHA_SECRET_KEY === '' || HCAPTCHA_SITE_KEY === '') {
        $log('CAPTCHA_NOT_CONFIGURED');
        return 'El servicio no está disponible temporalmente. Inténtalo más tarde' . $contacto . '.';
    }
    if (!verificarHCaptcha((string)($_POST['h-captcha-response'] ?? ''), HCAPTCHA_SECRET_KEY)) {
        $log('CAPTCHA_FAILED');
        return 'Por favor, completa el desafío de seguridad (captcha) antes de enviar.';
    }
    return '';
}

// ── Atomic rate limit (per IP-hash + site-wide) and duplicate-content check ──
// Reserves the slot when everything passes. Returns '' on success, else a Spanish message.
function reclamosReservarEnvio(string $dir, string $ipHash, string $dupHash, string $logFile): string {
    $contacto = defined('EMPRESA_EMAIL') ? ' o escríbenos a ' . EMPRESA_EMAIL : '';
    $generic  = 'No pudimos registrar tu solicitud en este momento. Inténtalo más tarde' . $contacto . '.';
    $stateFile = $dir . '/rate_state.json';
    @unlink($dir . '/rate_limits.json');      // legacy file kept raw IPs
    @unlink($dir . '/rate_limits.json.lock');
    $fp = @fopen($stateFile . '.lock', 'c');
    if (!$fp || !flock($fp, LOCK_EX)) {
        if ($fp) fclose($fp);
        logSecurityEvent($logFile, 'RATE_LOCK_FAILED', ['ip_hash' => $ipHash]);
        return $generic; // fail closed
    }
    $now  = time();
    $data = [];
    if (is_file($stateFile)) {
        $raw  = (string)@file_get_contents($stateFile);
        $data = $raw !== '' ? (json_decode($raw, true) ?: []) : [];
    }
    $ips  = is_array($data['ip']   ?? null) ? $data['ip']   : [];
    $site = is_array($data['site'] ?? null) ? $data['site'] : [];
    $dups = is_array($data['dup']  ?? null) ? $data['dup']  : [];
    $fresh = function ($ts) use ($now): bool { return is_int($ts) && ($now - $ts) < 86400; };
    $site = array_values(array_filter($site, $fresh));
    $dups = array_filter($dups, $fresh);
    foreach ($ips as $h => $list) {
        $ips[$h] = is_array($list) ? array_values(array_filter($list, $fresh)) : [];
        if (!$ips[$h]) unset($ips[$h]);
    }
    $mine     = $ips[$ipHash] ?? [];
    $inWindow = count(array_filter($mine, function ($t) use ($now) { return ($now - $t) < RATE_LIMIT_WINDOW; }));
    $siteHour = count(array_filter($site, function ($t) use ($now) { return ($now - $t) < 3600; }));
    $err = '';
    if ($inWindow >= RATE_LIMIT_MAX) {
        $err = 'RATE_LIMIT_IP';
        $msg = 'Has enviado un reclamo recientemente. Por favor, espera ' . (int)ceil(RATE_LIMIT_WINDOW / 60) . ' minutos antes de volver a intentarlo.';
    } elseif (count($mine) >= RATE_LIMIT_DAY_MAX) {
        $err = 'RATE_LIMIT_IP_DAY';
        $msg = 'Has alcanzado el límite diario de envíos desde tu conexión. Inténtalo nuevamente mañana' . $contacto . '.';
    } elseif ($siteHour >= SITE_LIMIT_HOUR_MAX || count($site) >= SITE_LIMIT_DAY_MAX) {
        $err = 'RATE_LIMIT_SITE';
        $msg = 'Estamos recibiendo muchas solicitudes en este momento. Inténtalo nuevamente en unos minutos' . $contacto . '.';
    } elseif ($dupHash !== '' && isset($dups[$dupHash])) {
        $err = 'DUPLICATE_CONTENT';
        $msg = 'Ya recibimos un reclamo con el mismo contenido. Si necesitas ayuda, comunícate con nosotros' . $contacto . '.';
    }
    if ($err !== '') {
        flock($fp, LOCK_UN); fclose($fp);
        logSecurityEvent($logFile, $err, ['ip_hash' => $ipHash]);
        return $msg;
    }
    $mine[]         = $now;
    $ips[$ipHash]   = $mine;
    $site[]         = $now;
    if ($dupHash !== '') $dups[$dupHash] = $now;
    $written = @file_put_contents($stateFile, json_encode(['ip' => $ips, 'site' => $site, 'dup' => $dups]));
    flock($fp, LOCK_UN); fclose($fp);
    if ($written === false) {
        logSecurityEvent($logFile, 'RATE_WRITE_FAILED', ['ip_hash' => $ipHash]);
        return $generic;
    }
    return '';
}

// ── SMTP (implicit TLS on 465, STARTTLS otherwise; certificate verified by default) ──
function enviarCorreoSMTP(string $toEmail, string $subject, string $htmlBody, string $pdfB64 = '', string $pdfName = '', string &$err = ''): bool {
    if (!defined('SMTP_HOST') || !defined('SMTP_USER') || !defined('SMTP_PASS')) {
        $err = 'SMTP configuration missing.';
        return false;
    }
    $toEmail = sanitizarCabecera($toEmail);
    if (strlen($toEmail) > 254 || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        $err = 'Invalid recipient address.';
        return false;
    }
    $subject   = mb_substr(sanitizarCabecera($subject), 0, 200, 'UTF-8');
    $pdfName   = (string)preg_replace('/[^A-Za-z0-9._-]/', '_', $pdfName);
    $fromEmail = sanitizarCabecera(SMTP_USER);
    $replyTo   = sanitizarCabecera(EMPRESA_EMAIL);
    $enc = function (string $s): string { return '=?UTF-8?B?' . base64_encode($s) . '?='; };
    $eol = "\r\n";
    $headers  = 'Date: ' . date('r') . $eol;
    $headers .= 'From: ' . $enc(sanitizarCabecera(SMTP_FROM_NAME)) . ' <' . $fromEmail . '>' . $eol;
    $headers .= 'To: <' . $toEmail . '>' . $eol;
    $headers .= 'Subject: ' . $enc($subject) . $eol;
    $headers .= 'Reply-To: ' . $replyTo . $eol;
    $headers .= 'MIME-Version: 1.0' . $eol;
    if ($pdfB64) {
        $boundary = '----=_Part_' . md5(uniqid('', true));
        $headers .= 'Content-Type: multipart/mixed; boundary="' . $boundary . '"' . $eol;
        $body  = '--' . $boundary . $eol;
        $body .= 'Content-Type: text/html; charset=UTF-8' . $eol;
        $body .= 'Content-Transfer-Encoding: 7bit' . $eol . $eol;
        $body .= $htmlBody . $eol;
        $body .= '--' . $boundary . $eol;
        $body .= 'Content-Type: application/pdf; name="' . $pdfName . '"' . $eol;
        $body .= 'Content-Transfer-Encoding: base64' . $eol;
        $body .= 'Content-Disposition: attachment; filename="' . $pdfName . '"' . $eol . $eol;
        $body .= $pdfB64 . $eol;
        $body .= '--' . $boundary . '--';
    } else {
        $headers .= 'Content-Type: text/html; charset=UTF-8' . $eol;
        $body = $htmlBody;
    }
    $message = $headers . $eol . $body;
    $message = str_replace(["\r\n", "\r", "\n"], "\n", $message);
    $message = str_replace("\n", $eol, $message);
    $message = str_replace($eol . '.', $eol . '..', $message);

    $verify = filter_var(SMTP_VERIFY_TLS, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $verify = ($verify === null) ? true : $verify;
    $sslOpts = ['verify_peer' => $verify, 'verify_peer_name' => $verify, 'allow_self_signed' => !$verify, 'SNI_enabled' => true];
    if ($verify) $sslOpts['peer_name'] = SMTP_HOST;
    $ctx = stream_context_create(['ssl' => $sslOpts]);
    $implicitTls = in_array((int)SMTP_PORT, [465, 4655], true);
    $fp = @stream_socket_client(($implicitTls ? 'ssl://' : '') . SMTP_HOST . ':' . SMTP_PORT, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) { $err = "SMTP connection failed: $errstr ($errno)"; return false; }
    stream_set_timeout($fp, 20);
    $read = function () use ($fp): string {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) { $data .= $line; if (strlen($line) < 4 || $line[3] === ' ') break; }
        return $data;
    };
    $cmd = function (string $c) use ($fp, $read): string { fwrite($fp, $c . "\r\n"); return $read(); };
    $ok  = function (string $resp, array $codes) use (&$err): bool {
        foreach ($codes as $code) { if (strncmp($resp, $code, strlen($code)) === 0) return true; }
        $err = trim($resp); return false;
    };
    $fail = function () use ($fp) { @fwrite($fp, "QUIT\r\n"); @fclose($fp); return false; };

    if (!$ok($read(), ['220']))                                   return $fail();
    if (!$ok($cmd('EHLO ' . SMTP_HOST), ['250']))                 return $fail();
    if (!$implicitTls) { // never send credentials in clear text
        if (!$ok($cmd('STARTTLS'), ['220']))                      return $fail();
        if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { $err = 'STARTTLS negotiation failed.'; return $fail(); }
        if (!$ok($cmd('EHLO ' . SMTP_HOST), ['250']))             return $fail();
    }
    if (!$ok($cmd('AUTH LOGIN'), ['334']))                        return $fail();
    if (!$ok($cmd(base64_encode(SMTP_USER)), ['334']))            return $fail();
    if (!$ok($cmd(base64_encode(SMTP_PASS)), ['235']))            return $fail();
    if (!$ok($cmd('MAIL FROM:<' . $fromEmail . '>'), ['250']))    return $fail();
    if (!$ok($cmd('RCPT TO:<' . $toEmail . '>'), ['250', '251'])) return $fail();
    if (!$ok($cmd('DATA'), ['354']))                              return $fail();
    if (!$ok($cmd($message . "\r\n."), ['250']))                  return $fail();
    $cmd('QUIT'); fclose($fp);
    return true;
}

// ── Runtime bootstrap (hardened) ──────────────────────────────────────────────
@ini_set('display_errors', '0');
@ini_set('html_errors', '0');
reclamosCabecerasSeguridad();
if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD', 'POST'], true)) {
    http_response_code(405);
    header('Allow: GET, HEAD, POST');
    exit;
}
reclamosIniciarSesion();

$success = false; $errorMsg = ''; $generatedCode = ''; $submittedData = []; $debugLog = [];
if (!reclamosPrepararDirectorio($recordsDir)) {
    $errorMsg = 'El servicio no está disponible temporalmente. Inténtalo más tarde o escríbenos a ' . EMPRESA_EMAIL . '.';
}
// The raw IP is never kept: $clientIP holds the salted hash (it is what the logs record).
$ipHash   = reclamosHashIp(reclamosClientIp(), $recordsDir);
$clientIP = $ipHash;

set_error_handler(function ($errno) use (&$errorMsg) {
    if ($errno === E_ERROR || $errno === E_USER_ERROR) $errorMsg = 'Error interno del servidor. Por favor, inténtelo más tarde.';
    return true;
});

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errorMsg === '') {

    // ── 1-5. Guards: size, honeypot, encoding, CSRF, time trap, origin, spam, captcha ──
    $errorMsg = reclamosGuardarSolicitud($recordsDir, $logFile, $ipHash);

    // ── 6. Sanitizar y validar ────────────────────────────────────────────────
    if (empty($errorMsg)) {
        $nombres      = limpiarTexto($_POST['nombres']      ?? '', 200);
        $doc_tipo     = $_POST['doc_tipo']     ?? '';
        $doc_nro      = limpiarTexto($_POST['doc_nro']      ?? '', 20);
        $email        = reclamosEmail($_POST['email'] ?? '');
        $telefono     = limpiarTexto($_POST['telefono']     ?? '', 20);
        $direccion    = limpiarTexto($_POST['direccion']    ?? '', 300);
        $departamento = limpiarTexto($_POST['departamento'] ?? '', 100);
        $provincia    = limpiarTexto($_POST['provincia']    ?? '', 100);
        $distrito     = limpiarTexto($_POST['distrito']     ?? '', 100);
        $menor_edad   = isset($_POST['menor_edad']);
        $ap_nombres   = limpiarTexto($_POST['apoderado_nombres']  ?? '', 200);
        $ap_doc_tipo  = $_POST['apoderado_doc_tipo'] ?? '';
        $ap_doc_nro   = limpiarTexto($_POST['apoderado_doc_nro']  ?? '', 20);
        $bien_tipo    = $_POST['bien_tipo']    ?? '';
        $bien_desc    = limpiarTexto($_POST['bien_desc']    ?? '', 500, true);
        $reclamo_tipo = $_POST['reclamo_tipo'] ?? '';
        $detalle      = limpiarTexto($_POST['detalle']      ?? '', 2000, true);
        $pedido       = limpiarTexto($_POST['pedido']       ?? '', 2000, true);
        $monto        = number_format(max(0.0, (float)preg_replace('/[^\d.]/', '', $_POST['monto'] ?? '0')), 2, '.', '');

        if (!in_array($doc_tipo,     ['DNI','CE','PASAPORTE','RUC'], true)) $doc_tipo     = 'DNI';
        if (!in_array($ap_doc_tipo,  ['DNI','CE','PASAPORTE'],        true)) $ap_doc_tipo  = 'DNI';
        if (!in_array($bien_tipo,    ['producto','servicio'],          true)) $bien_tipo    = 'producto';
        if (!in_array($reclamo_tipo, ['reclamo','queja'],              true)) $reclamo_tipo = 'reclamo';

        if (!$nombres || !$doc_nro || !$email || !$direccion || !$detalle || !$pedido) {
            $errorMsg = 'Por favor, rellene todos los campos obligatorios.';
        }
    }

    // ── 6b. Atomic rate limit (IP + site-wide) and duplicate-content check ─────
    if (empty($errorMsg)) {
        $errorMsg = reclamosReservarEnvio($recordsDir, $ipHash, reclamosHuellaContenido($recordsDir, $detalle, $email), $logFile);
    }

    // ── 7. Guardar registro ───────────────────────────────────────────────────
    if (empty($errorMsg)) {
        $fp = fopen($lockFile, 'w');
        if ($fp && flock($fp, LOCK_EX)) {
            $year    = date('Y');
            $records = file_exists($recordsFile) ? (json_decode(file_get_contents($recordsFile), true) ?: []) : [];
            $count   = count(array_filter($records, fn($r) => ($r['year'] ?? '') == $year));
            $generatedCode = sprintf('REC-%s-%04d', $year, $count + 1);
            $submittedData = [
                'codigo' => $generatedCode, 'year' => $year, 'fecha' => date('d/m/Y h:i A'),
                'nombres' => $nombres, 'doc_tipo' => $doc_tipo, 'doc_nro' => $doc_nro,
                'email' => $email, 'telefono' => $telefono, 'direccion' => $direccion,
                'departamento' => $departamento, 'provincia' => $provincia, 'distrito' => $distrito,
                'menor_edad' => $menor_edad,
                'apoderado_nombres'  => $menor_edad ? $ap_nombres  : '',
                'apoderado_doc_tipo' => $menor_edad ? $ap_doc_tipo : '',
                'apoderado_doc_nro'  => $menor_edad ? $ap_doc_nro  : '',
                'bien_tipo' => $bien_tipo, 'monto' => $monto, 'bien_desc' => $bien_desc,
                'reclamo_tipo' => $reclamo_tipo, 'detalle' => $detalle, 'pedido' => $pedido,
                'estado' => 'Pendiente',
            ];
            $records[] = $submittedData;
            file_put_contents($recordsFile, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            flock($fp, LOCK_UN);
            $success = true;
        } else {
            $errorMsg = 'Error al registrar en el servidor. Inténtelo nuevamente.';
        }
        if ($fp) fclose($fp);
    }

    // ── 8. Generar PDF + enviar correos + redirigir ───────────────────────────
    if ($success) {
        logSecurityEvent($logFile, 'RECLAMO_OK', ['ip' => $clientIP, 'codigo' => $generatedCode]);
        $pdfBytes = '';
        if ($fpdfAvailable) { try { $pdfBytes = generarPDFReclamo($submittedData); } catch(Exception $e) { } }
        $pdfB64  = $pdfBytes ? chunk_split(base64_encode($pdfBytes)) : '';
        $pdfName = 'Hoja_Reclamacion_' . $generatedCode . '.pdf';
        if ($pdfBytes) @file_put_contents($recordsDir . '/' . $pdfName, $pdfBytes);

        $emailBody = "
        <div style='font-family:Inter,Arial,sans-serif;color:#0b142c;max-width:600px;margin:0 auto;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;'>
            <div style='background:#0b142c;padding:0;'>
                <div style='background:#f3580e;height:4px;'></div>
                <div style='padding:28px 32px;'>
                    <h2 style='margin:0 0 4px;font-size:20px;color:#ffffff;font-weight:700;letter-spacing:-.3px;'>HOJA DE RECLAMACIÓN VIRTUAL</h2>
                    <p style='margin:0;color:rgba(255,255,255,.6);font-size:13px;'>ZENTEK360 S.A.C.S. — Código: <strong style='color:#f3580e;'>" . esc($generatedCode) . "</strong></p>
                </div>
            </div>
            <div style='padding:28px 32px;line-height:1.7;font-size:14px;'>
                <p style='margin:0 0 16px;'>Estimado(a) <strong>" . esc($nombres) . "</strong>,</p>
                <p style='margin:0 0 16px;'>Confirmamos la recepción de tu reclamación registrada el <strong>" . date('d/m/Y') . "</strong>. Adjunto encontrarás el cargo de tu Hoja de Reclamación Virtual.</p>
                <p style='margin:0 0 24px;'>Daremos respuesta en un plazo máximo de <strong>15 días hábiles</strong> (Ley N° 29571).</p>
                <div style='background:#f8f9fc;border-radius:8px;padding:20px;border:1px solid #e5e7eb;'>
                    <table style='width:100%;border-collapse:collapse;font-size:13px;'>
                        <tr><td style='padding:5px 0;font-weight:600;color:#6b7280;width:130px;'>Consumidor:</td><td style='color:#0b142c;'>" . esc($nombres) . " (" . esc($doc_tipo) . " " . esc($doc_nro) . ")</td></tr>
                        <tr><td style='padding:5px 0;font-weight:600;color:#6b7280;'>Bien:</td><td style='text-transform:capitalize;color:#0b142c;'>" . esc($bien_tipo) . " — S/. " . esc($monto) . "</td></tr>
                        <tr><td style='padding:5px 0;font-weight:600;color:#6b7280;'>Incidencia:</td><td style='font-weight:700;color:" . ($reclamo_tipo==='reclamo'?'#dc2626':'#d97706') . ";text-transform:capitalize;'>" . esc($reclamo_tipo) . "</td></tr>
                        <tr><td style='padding:5px 0;font-weight:600;color:#6b7280;vertical-align:top;'>Detalle:</td><td style='color:#374151;'>" . esc($detalle) . "</td></tr>
                        <tr><td style='padding:5px 0;font-weight:600;color:#6b7280;vertical-align:top;'>Pedido:</td><td style='color:#374151;'>" . esc($pedido) . "</td></tr>
                    </table>
                </div>
            </div>
            <div style='background:#f8f9fc;padding:16px 32px;text-align:center;font-size:11px;color:#9ca3af;border-top:1px solid #e5e7eb;'>Cargo automático — no responder a este mensaje · ZENTEK360 S.A.C.S. · RUC 20616110099</div>
        </div>";

        $errC = '';
        $mailClienteSubj = 'Cargo de Hoja de Reclamación N° ' . $generatedCode . ' — ZENTEK360';
        $mailClienteBody = $emailBody;

        $emailEmpresa = "
        <div style='font-family:Inter,Arial,sans-serif;color:#0b142c;max-width:600px;margin:0 auto;border:1px solid #e5e7eb;border-radius:12px;padding:28px 32px;'>
            <h2 style='color:#f3580e;margin-top:0;font-size:18px;'>Nueva Reclamación — " . esc($generatedCode) . "</h2>
            <p>Plazo máximo de respuesta: <strong>15 días hábiles</strong>.</p>
            <p><strong>Reclamante:</strong> " . esc($nombres) . " · " . esc($doc_tipo) . " " . esc($doc_nro) . " · " . esc($email) . " · " . esc($telefono) . "</p>
            <p><strong>Domicilio:</strong> " . esc($direccion) . ", " . esc($distrito) . " - " . esc($provincia) . " (" . esc($departamento) . ")</p>
            <p><strong>Bien:</strong> " . esc(ucfirst($bien_tipo)) . " · S/. " . esc($monto) . " · " . esc($bien_desc) . "</p>
            <p><strong>Tipo:</strong> " . esc(strtoupper($reclamo_tipo)) . "</p>
            <div style='background:#fef2f2;padding:14px 16px;border-left:4px solid #f3580e;border-radius:4px;margin-bottom:12px;'><strong>Detalle:</strong><br>" . nl2br(esc($detalle)) . "</div>
            <div style='background:#eff6ff;padding:14px 16px;border-left:4px solid #3b82f6;border-radius:4px;'><strong>Pedido:</strong><br>" . nl2br(esc($pedido)) . "</div>
        </div>";
        $errE = '';
        $okEmpresa = enviarCorreoSMTP(EMPRESA_NOTIF_EMAIL, 'NUEVA RECLAMACIÓN N° ' . $generatedCode . ' — ' . $nombres, $emailEmpresa, $pdfB64, $pdfName, $errE);
        if ($okEmpresa) {
            // Consumer copy only after the internal mail was accepted (anti mail-bombing).
            if (!enviarCorreoSMTP($email, $mailClienteSubj, $mailClienteBody, $pdfB64, $pdfName, $errC)) {
                logSecurityEvent($logFile, 'SMTP_CLIENT_FAILED', ['detail' => substr($errC, 0, 160)]);
            }
        } else {
            logSecurityEvent($logFile, 'SMTP_INTERNAL_FAILED', ['detail' => substr($errE, 0, 160)]);
        }

        $_SESSION['csrf_token']      = bin2hex(random_bytes(32));
        session_regenerate_id(true);
        $_SESSION['reclamo_success'] = $submittedData;
        header('Location: libro-de-reclamaciones.php?ok=1');
        exit;
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_GET['ok']) && !empty($_SESSION['reclamo_success'])) {
    $success       = true;
    $submittedData = $_SESSION['reclamo_success'];
    $generatedCode = $submittedData['codigo'] ?? '';
    unset($_SESSION['reclamo_success']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libro de Reclamaciones — ZENTEK360</title>
    <meta name="description" content="Libro de Reclamaciones Virtual de ZENTEK360 S.A.C.S. Conforme al Código de Protección al Consumidor Ley N° 29571.">
    <link rel="icon" type="image/jpeg" href="images/FAVICON - ZENTEK 360.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=League+Spartan:wght@700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        spartan: ['League Spartan', 'sans-serif'],
                    },
                    colors: {
                        primary:   '#0b142c',
                        secondary: '#f3580e',
                        canvas:    '#f5f6fa',
                        ink:       '#374151',
                    }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; }
        body { background: #f5f6fa; color: #0b142c; font-family: 'Inter', sans-serif; overflow-x: hidden; }

        /* Grid pattern hero */
        .hero-pattern {
            background-color: #ffffff;
            background-image: linear-gradient(rgba(11,20,44,.04) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(11,20,44,.04) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        /* Cards */
        .card { background: #ffffff; border: 1px solid rgba(11,20,44,.07); border-radius: 16px; box-shadow: 0 1px 3px rgba(11,20,44,.05), 0 8px 24px rgba(11,20,44,.04); }

        /* Form inputs */
        .form-input {
            width: 100%;
            background: #ffffff;
            border: 1.5px solid rgba(11,20,44,.12);
            border-radius: 10px;
            padding: 10px 14px;
            color: #0b142c;
            font-size: .875rem;
            font-family: 'Inter', sans-serif;
            transition: border-color .18s, box-shadow .18s;
            outline: none;
            appearance: none;
        }
        .form-input::placeholder { color: rgba(11,20,44,.3); }
        .form-input:focus { border-color: #f3580e; box-shadow: 0 0 0 3px rgba(243,88,14,.1); }
        select.form-input { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%230b142c' fill-opacity='.4' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }

        /* Section badge */
        .section-badge { width: 28px; height: 28px; border-radius: 8px; background: #f3580e; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: #ffffff; flex-shrink: 0; }

        /* Buttons */
        .btn-primary { background: #0b142c; color: #ffffff; font-weight: 700; transition: all .2s; }
        .btn-primary:hover { background: #131f3d; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(11,20,44,.25); }
        .btn-orange { background: #f3580e; color: #ffffff; font-weight: 700; transition: all .2s; }
        .btn-orange:hover { background: #d94d0b; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(243,88,14,.3); }
        .btn-outline { background: transparent; border: 1.5px solid rgba(11,20,44,.2); color: #0b142c; font-weight: 600; transition: all .2s; }
        .btn-outline:hover { border-color: #0b142c; background: rgba(11,20,44,.03); }

        /* Tag / badge */
        .law-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(243,88,14,.08); border: 1px solid rgba(243,88,14,.2); border-radius: 100px; padding: 4px 14px; font-size: 11px; font-weight: 700; color: #f3580e; text-transform: uppercase; letter-spacing: .08em; }

        /* Label style */
        .field-label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: rgba(11,20,44,.45); margin-bottom: 6px; }

        /* Radio card */
        .radio-card { position: relative; border: 1.5px solid rgba(11,20,44,.1); border-radius: 10px; padding: 14px; cursor: pointer; transition: border-color .18s, background .18s; background: #ffffff; }
        .radio-card:hover { border-color: rgba(243,88,14,.35); background: rgba(243,88,14,.02); }
        .radio-card input:checked ~ .radio-card-inner { color: #f3580e; }
        .radio-card:has(input:checked) { border-color: #f3580e; background: rgba(243,88,14,.04); }

        /* Success card */
        .success-badge { width: 64px; height: 64px; border-radius: 50%; background: rgba(34,197,94,.1); border: 2px solid rgba(34,197,94,.25); display: flex; align-items: center; justify-content: center; }

        /* Sidebar info row */
        .info-row { padding: 10px 0; border-bottom: 1px solid rgba(11,20,44,.06); }
        .info-row:last-child { border-bottom: none; padding-bottom: 0; }

        /* Divider accent */
        .accent-line { width: 36px; height: 3px; background: #f3580e; border-radius: 2px; }

        @media print {
            body { background: white !important; font-size: 11px !important; }
            .no-print { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        }
    </style>
<?php if (defined('HCAPTCHA_SITE_KEY') && HCAPTCHA_SITE_KEY !== ''): ?>
<script src="https://js.hcaptcha.com/1/api.js" async defer></script>
<?php endif; ?>
</head>
<body class="min-h-screen flex flex-col">

<!-- ════════════════════════════
     HEADER
════════════════════════════ -->
<header class="no-print w-full bg-white border-b border-primary/8 sticky top-0 z-50" style="box-shadow:0 1px 12px rgba(11,20,44,.06);">
    <div class="max-w-6xl mx-auto px-5 lg:px-8 h-16 flex items-center justify-between gap-4">
        <a href="index.html" class="flex items-center gap-3 flex-shrink-0" aria-label="ZENTEK360 — Inicio">
            <img src="images/ZENTEK360.png" alt="ZENTEK360" class="h-10 w-auto object-contain"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="items-center gap-2.5" style="display:none">
                <svg viewBox="0 0 44 44" width="44" height="44">
                    <circle cx="22" cy="22" r="21" fill="#0b142c"/>
                    <circle cx="22" cy="22" r="21" fill="none" stroke="#f3580e" stroke-width="1.5"/>
                    <text x="22" y="30" text-anchor="middle" font-family="League Spartan,sans-serif" font-weight="900" font-size="21" fill="#fff">Z</text>
                </svg>
                <div>
                    <div style="font-family:'League Spartan',sans-serif;font-weight:900;font-size:18px;color:#0b142c;line-height:1;">ZENTEK<span style="color:#f3580e">360</span></div>
                    <div style="font-size:8px;letter-spacing:.18em;color:#b0aab3;text-transform:uppercase;margin-top:2px;">Desarrollo Tecnológico</div>
                </div>
            </div>
        </a>
        <a href="index.html" class="flex items-center gap-1.5 text-primary/40 hover:text-primary text-[13px] font-medium transition-colors duration-200 no-underline">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10 12L6 8l4-4"/>
            </svg>
            Volver al inicio
        </a>
    </div>
</header>

<main class="flex-grow max-w-6xl w-full mx-auto px-5 lg:px-8 py-10 lg:py-14">

<?php if ($success): ?>
<!-- ════════════════════════════
     PANTALLA DE ÉXITO
════════════════════════════ -->
<div class="max-w-3xl mx-auto">
    <div class="card p-8 sm:p-12">
        <div class="no-print text-center mb-10">
            <div class="success-badge mx-auto mb-5">
                <svg width="28" height="28" viewBox="0 0 28 28" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 14l6 6L23 8"/>
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-primary mb-2">Reclamación Registrada</h1>
            <p class="text-primary/50 text-sm max-w-md mx-auto leading-relaxed">
                Tu reclamo ha sido procesado. Se envió un cargo al correo
                <strong class="text-primary"><?= htmlspecialchars($submittedData['email']) ?></strong>.
            </p>
        </div>

        <!-- Hoja de reclamación -->
        <div class="bg-canvas rounded-2xl border border-primary/6 p-6 sm:p-8">

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-6 border-b border-primary/8">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-[.12em] text-primary/40 mb-1">Hoja de Reclamación Virtual</div>
                    <div class="font-bold text-lg text-primary">ZENTEK360 S.A.C.S.</div>
                    <div class="text-xs text-primary/40 mt-0.5">Ley N° 29571 / D.S. N° 011-2011-PCM</div>
                </div>
                <div class="text-left sm:text-right">
                    <div class="text-[10px] font-bold uppercase tracking-[.1em] text-secondary mb-1">Código de Reclamación</div>
                    <div class="text-xl font-bold text-green-600"><?= htmlspecialchars($submittedData['codigo']) ?></div>
                    <div class="text-[11px] text-primary/35 mt-0.5">Fecha: <?= htmlspecialchars($submittedData['fecha']) ?></div>
                </div>
            </div>

            <!-- Proveedor -->
            <div class="py-5 border-b border-primary/6">
                <div class="text-[10px] font-bold uppercase tracking-[.1em] text-primary/40 mb-3">Proveedor</div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                    <div><span class="block text-[10px] font-semibold text-primary/35 uppercase tracking-wider mb-0.5">Razón Social</span><span class="font-semibold text-primary"><?= EMPRESA_RAZON_SOCIAL ?></span></div>
                    <div><span class="block text-[10px] font-semibold text-primary/35 uppercase tracking-wider mb-0.5">RUC</span><span class="font-semibold text-primary"><?= EMPRESA_RUC ?></span></div>
                    <div class="sm:col-span-1"><span class="block text-[10px] font-semibold text-primary/35 uppercase tracking-wider mb-0.5">Domicilio</span><span class="text-primary/70 text-xs leading-relaxed"><?= EMPRESA_DIRECCION ?></span></div>
                </div>
            </div>

            <?php foreach ([
                ['1. Identificación del Consumidor', [
                    ['Nombre', '<strong>' . htmlspecialchars($submittedData['nombres']) . '</strong>'],
                    ['Documento', htmlspecialchars($submittedData['doc_tipo']) . ' — ' . htmlspecialchars($submittedData['doc_nro'])],
                    ['Domicilio', htmlspecialchars($submittedData['direccion']) . ', ' . htmlspecialchars($submittedData['distrito']) . ' - ' . htmlspecialchars($submittedData['provincia']) . ' (' . htmlspecialchars($submittedData['departamento']) . ')'],
                    ['Contacto', 'Email: ' . htmlspecialchars($submittedData['email']) . ' &nbsp;·&nbsp; Tel: ' . htmlspecialchars($submittedData['telefono'] ?: '—')],
                ]],
                ['2. Identificación del Bien Contratado', [
                    ['Tipo de bien', '<span class="capitalize">' . htmlspecialchars($submittedData['bien_tipo']) . '</span>'],
                    ['Monto reclamado', 'S/. ' . htmlspecialchars($submittedData['monto'])],
                    ['Descripción', htmlspecialchars($submittedData['bien_desc'] ?: '—')],
                ]],
            ] as [$title, $fields]): ?>
            <div class="py-5 border-b border-primary/6">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="accent-line"></div>
                    <div class="text-[11px] font-bold uppercase tracking-[.1em] text-primary/60"><?= $title ?></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <?php foreach ($fields as [$label, $val]): ?>
                    <div class="text-sm">
                        <span class="block text-[10px] font-semibold text-primary/35 uppercase tracking-wider mb-0.5"><?= $label ?></span>
                        <span class="text-primary/80"><?= $val ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if ($submittedData['menor_edad']): ?>
            <div class="py-4 border-b border-primary/6">
                <div class="text-[10px] font-semibold text-primary/35 uppercase tracking-wider mb-0.5">Apoderado</div>
                <div class="text-sm text-primary/80">
                    <?= htmlspecialchars($submittedData['apoderado_nombres']) ?>
                    (<?= htmlspecialchars($submittedData['apoderado_doc_tipo']) ?> <?= htmlspecialchars($submittedData['apoderado_doc_nro']) ?>)
                </div>
            </div>
            <?php endif; ?>

            <div class="pt-5">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="accent-line"></div>
                    <div class="text-[11px] font-bold uppercase tracking-[.1em] text-primary/60">3. Detalle de la Reclamación y Pedido</div>
                </div>
                <div class="space-y-4 text-sm">
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] font-semibold text-primary/35 uppercase tracking-wider">Tipo:</span>
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider <?= $submittedData['reclamo_tipo']==='reclamo'?'bg-red-50 text-red-600 border border-red-200':'bg-amber-50 text-amber-600 border border-amber-200' ?>">
                            <?= htmlspecialchars($submittedData['reclamo_tipo']) ?>
                        </span>
                    </div>
                    <div>
                        <div class="text-[10px] font-semibold text-primary/35 uppercase tracking-wider mb-1.5">Detalle del hecho</div>
                        <div class="bg-white border border-primary/8 rounded-lg p-4 text-primary/75 whitespace-pre-wrap leading-relaxed"><?= htmlspecialchars($submittedData['detalle']) ?></div>
                    </div>
                    <div>
                        <div class="text-[10px] font-semibold text-primary/35 uppercase tracking-wider mb-1.5">Pedido del consumidor</div>
                        <div class="bg-white border border-primary/8 rounded-lg p-4 text-primary/75 whitespace-pre-wrap leading-relaxed"><?= htmlspecialchars($submittedData['pedido']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nota legal -->
        <div class="no-print mt-6 bg-canvas border border-primary/6 rounded-xl p-4 text-xs text-primary/50 leading-relaxed">
            La formulación del reclamo no impide acudir a otras vías de solución de controversias ni es requisito previo para interponer una denuncia ante el INDECOPI.
            <span class="font-semibold text-primary/65">El proveedor debe dar respuesta al reclamo o queja en un plazo no mayor a quince (15) días hábiles improrrogables (D.S. 101-2021-PCM).</span>
        </div>

        <!-- Acciones -->
        <div class="no-print mt-8 flex flex-col sm:flex-row gap-3">
            <button onclick="window.print()" class="btn-primary flex-1 py-3.5 px-6 rounded-xl text-sm flex items-center justify-center gap-2 cursor-pointer">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Imprimir / Guardar PDF
            </button>
            <a href="index.html" class="btn-outline flex-1 py-3.5 px-6 rounded-xl text-sm flex items-center justify-center gap-2 no-underline">
                Volver al inicio
            </a>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ════════════════════════════
     FORMULARIO
════════════════════════════ -->

<!-- Hero -->
<div class="no-print text-center mb-12">
    <div class="law-badge mb-5">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Ley N° 29571 · Código de Protección al Consumidor
    </div>
    <h1 class="text-3xl sm:text-4xl font-bold text-primary leading-tight mb-3" style="letter-spacing:-.5px;">
        Libro de Reclamaciones
        <span style="color:#f3580e;">Virtual</span>
    </h1>
    <p class="text-primary/50 text-sm sm:text-base max-w-xl mx-auto leading-relaxed">
        Completa el formulario para registrar tu queja o reclamo.<br>
        Respondemos en un plazo máximo de <strong class="text-primary/70">15 días hábiles</strong>.
    </p>
</div>

<?php if (!empty($errorMsg)): ?>
<div class="no-print mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl text-sm flex items-center gap-3 max-w-4xl mx-auto">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= $errorMsg ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-7 xl:gap-9 items-start">

    <!-- FORMULARIO PRINCIPAL -->
    <div class="card p-7 sm:p-9">
        <form method="POST" action="libro-de-reclamaciones.php" class="space-y-9">
            <input type="hidden" name="csrf_token" value="<?= esc($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="form_ts" value="<?= esc(reclamosTokenTiempo($recordsDir, (string)($_SESSION['csrf_token'] ?? ''))) ?>">
            <div style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                <label for="hp_website">Sitio web (no llenar)</label>
                <input type="text" name="hp_website" id="hp_website" tabindex="-1" autocomplete="off" value="">
            </div>

            <!-- SECCIÓN 1 -->
            <div>
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-primary/6">
                    <div class="section-badge">1</div>
                    <div>
                        <div class="font-bold text-primary text-[15px]">Identificación del Consumidor</div>
                        <div class="text-xs text-primary/40 mt-0.5">Datos de la persona reclamante</div>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="field-label">Nombres y Apellidos completos <span class="text-secondary">*</span></label>
                        <input type="text" name="nombres" required placeholder="Ingresa tus nombres y apellidos completos" class="form-input" value="<?= isset($_POST['nombres'])?htmlspecialchars($_POST['nombres']):'' ?>">
                    </div>
                    <div>
                        <label class="field-label">Tipo de Documento <span class="text-secondary">*</span></label>
                        <select name="doc_tipo" required class="form-input">
                            <option value="DNI" <?= (($_POST['doc_tipo']??'')==='DNI'||!isset($_POST['doc_tipo']))?'selected':'' ?>>DNI (Perú)</option>
                            <option value="CE" <?= (($_POST['doc_tipo']??'')==='CE')?'selected':'' ?>>Carnet de Extranjería</option>
                            <option value="PASAPORTE" <?= (($_POST['doc_tipo']??'')==='PASAPORTE')?'selected':'' ?>>Pasaporte</option>
                            <option value="RUC" <?= (($_POST['doc_tipo']??'')==='RUC')?'selected':'' ?>>RUC</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">N° de Documento <span class="text-secondary">*</span></label>
                        <input type="text" name="doc_nro" required placeholder="Número de documento" class="form-input" value="<?= isset($_POST['doc_nro'])?htmlspecialchars($_POST['doc_nro']):'' ?>">
                    </div>
                    <div>
                        <label class="field-label">Correo Electrónico <span class="text-secondary">*</span></label>
                        <input type="email" name="email" required placeholder="nombre@correo.com" class="form-input" value="<?= isset($_POST['email'])?htmlspecialchars($_POST['email']):'' ?>">
                    </div>
                    <div>
                        <label class="field-label">Teléfono / Celular</label>
                        <input type="tel" name="telefono" placeholder="Número de contacto" class="form-input" value="<?= isset($_POST['telefono'])?htmlspecialchars($_POST['telefono']):'' ?>">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label">Dirección Completa <span class="text-secondary">*</span></label>
                        <input type="text" name="direccion" required placeholder="Av., Calle, Nro., Dpto., Urb." class="form-input" value="<?= isset($_POST['direccion'])?htmlspecialchars($_POST['direccion']):'' ?>">
                    </div>
                    <div>
                        <label class="field-label">Departamento <span class="text-secondary">*</span></label>
                        <input type="text" name="departamento" required placeholder="Ej: Lima" class="form-input" value="<?= isset($_POST['departamento'])?htmlspecialchars($_POST['departamento']):'' ?>">
                    </div>
                    <div>
                        <label class="field-label">Provincia <span class="text-secondary">*</span></label>
                        <input type="text" name="provincia" required placeholder="Ej: Lima" class="form-input" value="<?= isset($_POST['provincia'])?htmlspecialchars($_POST['provincia']):'' ?>">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label">Distrito <span class="text-secondary">*</span></label>
                        <input type="text" name="distrito" required placeholder="Ej: Jesús María" class="form-input" value="<?= isset($_POST['distrito'])?htmlspecialchars($_POST['distrito']):'' ?>">
                    </div>
                </div>

                <!-- Menor de edad -->
                <div class="mt-5 bg-canvas border border-primary/6 rounded-xl p-4">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" id="menor_edad" name="menor_edad" onclick="toggleApoderado()"
                               class="w-4 h-4 mt-0.5 rounded flex-shrink-0 cursor-pointer accent-secondary"
                               <?= isset($_POST['menor_edad'])?'checked':'' ?>>
                        <div>
                            <span class="text-sm font-medium text-primary/70">Soy menor de edad</span>
                            <span class="text-xs text-primary/40 block mt-0.5">Se requiere ingresar los datos de un tutor o apoderado.</span>
                        </div>
                    </label>
                    <div id="apoderado_fields" class="mt-5 pt-4 border-t border-primary/8 grid grid-cols-1 sm:grid-cols-2 gap-4 hidden">
                        <div class="sm:col-span-2">
                            <label class="field-label">Nombres del Apoderado <span class="text-secondary">*</span></label>
                            <input type="text" id="apoderado_nombres" name="apoderado_nombres" placeholder="Nombres del padre, madre o apoderado" class="form-input" value="<?= isset($_POST['apoderado_nombres'])?htmlspecialchars($_POST['apoderado_nombres']):'' ?>">
                        </div>
                        <div>
                            <label class="field-label">Tipo Doc. Apoderado <span class="text-secondary">*</span></label>
                            <select id="apoderado_doc_tipo" name="apoderado_doc_tipo" class="form-input">
                                <option value="DNI">DNI</option>
                                <option value="CE">Carnet de Extranjería</option>
                                <option value="PASAPORTE">Pasaporte</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">N° Doc. Apoderado <span class="text-secondary">*</span></label>
                            <input type="text" id="apoderado_doc_nro" name="apoderado_doc_nro" placeholder="Nro de documento" class="form-input" value="<?= isset($_POST['apoderado_doc_nro'])?htmlspecialchars($_POST['apoderado_doc_nro']):'' ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2 -->
            <div>
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-primary/6">
                    <div class="section-badge">2</div>
                    <div>
                        <div class="font-bold text-primary text-[15px]">Identificación del Bien Contratado</div>
                        <div class="text-xs text-primary/40 mt-0.5">Producto o servicio objeto de la reclamación</div>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Tipo de bien <span class="text-secondary">*</span></label>
                        <div class="grid grid-cols-2 gap-3 mt-1">
                            <label class="radio-card">
                                <input type="radio" name="bien_tipo" value="producto" required class="sr-only" <?= (!isset($_POST['bien_tipo'])||$_POST['bien_tipo']==='producto')?'checked':'' ?>>
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded-full border-2 border-primary/25 flex items-center justify-center flex-shrink-0">
                                        <div class="w-2 h-2 rounded-full bg-secondary hidden radio-dot"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-primary/80">Producto</span>
                                </div>
                            </label>
                            <label class="radio-card">
                                <input type="radio" name="bien_tipo" value="servicio" class="sr-only" <?= (($_POST['bien_tipo']??'')==='servicio')?'checked':'' ?>>
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded-full border-2 border-primary/25 flex items-center justify-center flex-shrink-0">
                                        <div class="w-2 h-2 rounded-full bg-secondary hidden radio-dot"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-primary/80">Servicio</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="field-label">Monto Reclamado (S/. opcional)</label>
                        <input type="number" step="0.01" min="0" name="monto" placeholder="0.00" class="form-input" value="<?= isset($_POST['monto'])?htmlspecialchars($_POST['monto']):'' ?>">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="field-label">Descripción del bien o servicio</label>
                        <textarea rows="2" name="bien_desc" placeholder="Describe brevemente el producto o servicio contratado con ZENTEK360" class="form-input resize-none"><?= isset($_POST['bien_desc'])?htmlspecialchars($_POST['bien_desc']):'' ?></textarea>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 3 -->
            <div>
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-primary/6">
                    <div class="section-badge">3</div>
                    <div>
                        <div class="font-bold text-primary text-[15px]">Detalle del Reclamo o Queja</div>
                        <div class="text-xs text-primary/40 mt-0.5">Describa lo ocurrido y su pedido concreto</div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="field-label">Tipo de reclamación <span class="text-secondary">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1">
                            <label class="radio-card">
                                <input type="radio" name="reclamo_tipo" value="reclamo" required class="sr-only" <?= (!isset($_POST['reclamo_tipo'])||$_POST['reclamo_tipo']==='reclamo')?'checked':'' ?>>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-4 h-4 rounded-full border-2 border-primary/25 flex items-center justify-center flex-shrink-0">
                                            <div class="w-2 h-2 rounded-full bg-secondary hidden radio-dot"></div>
                                        </div>
                                        <span class="text-sm font-bold text-primary uppercase tracking-wide">Reclamo</span>
                                    </div>
                                    <p class="text-xs text-primary/45 leading-relaxed pl-6">Disconformidad relacionada a los productos o servicios contratados.</p>
                                </div>
                            </label>
                            <label class="radio-card">
                                <input type="radio" name="reclamo_tipo" value="queja" class="sr-only" <?= (($_POST['reclamo_tipo']??'')==='queja')?'checked':'' ?>>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-4 h-4 rounded-full border-2 border-primary/25 flex items-center justify-center flex-shrink-0">
                                            <div class="w-2 h-2 rounded-full bg-secondary hidden radio-dot"></div>
                                        </div>
                                        <span class="text-sm font-bold text-primary uppercase tracking-wide">Queja</span>
                                    </div>
                                    <p class="text-xs text-primary/45 leading-relaxed pl-6">Disconformidad no relacionada a los productos o servicios. Malestar respecto a la atención recibida.</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="field-label">Detalle de tu queja o reclamo <span class="text-secondary">*</span></label>
                        <textarea rows="5" name="detalle" required placeholder="Describe de forma detallada y cronológica los hechos que motivan tu reclamación..." class="form-input resize-none"><?= isset($_POST['detalle'])?htmlspecialchars($_POST['detalle']):'' ?></textarea>
                    </div>
                    <div>
                        <label class="field-label">Pedido concreto (¿Qué solicitas?) <span class="text-secondary">*</span></label>
                        <textarea rows="3" name="pedido" required placeholder="Indica tu solicitud concreta: cambio, devolución, compensación, solución técnica, etc." class="form-input resize-none"><?= isset($_POST['pedido'])?htmlspecialchars($_POST['pedido']):'' ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Declaraciones -->
            <div class="pt-2 space-y-3 border-t border-primary/6">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" required class="w-4 h-4 mt-0.5 rounded flex-shrink-0 cursor-pointer accent-secondary">
                    <span class="text-xs text-primary/50 leading-relaxed">Declaro ser el usuario titular y que los datos consignados son reales, verídicos y de mi plena responsabilidad.</span>
                </label>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" required class="w-4 h-4 mt-0.5 rounded flex-shrink-0 cursor-pointer accent-secondary">
                    <span class="text-xs text-primary/50 leading-relaxed">Acepto el tratamiento de mis datos personales conforme a la <strong class="text-primary/65">Ley N° 29733</strong> — Ley de Protección de Datos Personales.</span>
                </label>
            </div>

            <?php if (defined('HCAPTCHA_SITE_KEY') && HCAPTCHA_SITE_KEY !== ''): ?>
            <div class="flex justify-center pt-2">
                <div class="h-captcha" data-sitekey="<?= esc(HCAPTCHA_SITE_KEY) ?>" data-theme="dark"></div>
            </div>
            <?php else: ?>
            <p class="text-amber-400/70 text-[10px] text-center">⚠️ hCaptcha no configurado — define <code>HCAPTCHA_SITE_KEY</code> en <code>config.smtp.php</code>.</p>
            <?php endif; ?>
            <button type="submit" id="btn-submit" class="btn-orange w-full py-4 rounded-xl text-sm tracking-wide cursor-pointer flex items-center justify-center gap-2" onclick="this.disabled=true;this.form.submit()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13"/><path d="M22 2L15 22 11 13 2 9l20-7z"/></svg>
                PRESENTAR RECLAMACIÓN
            </button>

        </form>
    </div>

    <!-- SIDEBAR -->
    <div class="space-y-5">

        <!-- Datos del Proveedor -->
        <div class="card p-6">
            <div class="text-[10px] font-bold uppercase tracking-[.12em] text-primary/40 mb-4">Datos del Proveedor</div>
            <div class="space-y-0">
                <div class="info-row">
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-primary/35 mb-0.5">Razón Social</div>
                    <div class="font-bold text-primary text-sm"><?= EMPRESA_RAZON_SOCIAL ?></div>
                </div>
                <div class="info-row">
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-primary/35 mb-0.5">RUC</div>
                    <div class="font-semibold text-primary text-sm"><?= EMPRESA_RUC ?></div>
                </div>
                <div class="info-row">
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-primary/35 mb-0.5">Domicilio Fiscal</div>
                    <div class="text-xs text-primary/65 leading-relaxed"><?= EMPRESA_DIRECCION ?></div>
                </div>
                <div class="info-row">
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-primary/35 mb-0.5">Email</div>
                    <div class="text-xs text-secondary font-medium"><?= EMPRESA_EMAIL ?></div>
                </div>
            </div>
        </div>

        <!-- Aviso Virtual INDECOPI -->
        <div class="card p-6">
            <div class="flex items-center gap-2 mb-4">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f3580e" stroke-width="2" stroke-linecap="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                <div class="text-[11px] font-bold uppercase tracking-[.1em] text-primary/60">Aviso Virtual</div>
            </div>
            <p class="text-xs text-primary/45 leading-relaxed mb-4">
                Conforme al Código de Protección y Defensa del Consumidor, disponemos de un Libro de Reclamaciones Virtual.
            </p>
            <div class="relative rounded-xl overflow-hidden border border-primary/8 bg-canvas group cursor-pointer" onclick="openNoticeModal()">
                <img src="Libro-reclamaciones/AvisoVirtual_page1.png" alt="Aviso Virtual INDECOPI" class="w-full h-auto object-cover transition-transform duration-300 group-hover:scale-105">
                <div class="absolute inset-0 bg-primary/20 flex items-end group-hover:bg-primary/10 transition-all duration-300">
                    <div class="w-full bg-gradient-to-t from-primary/70 to-transparent p-3 pb-3">
                        <span class="text-white text-[11px] font-semibold flex items-center gap-1.5">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Ver a pantalla completa
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plazos legales -->
        <div class="card p-6">
            <div class="flex items-center gap-2 mb-4">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f3580e" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <div class="text-[11px] font-bold uppercase tracking-[.1em] text-primary/60">Plazos Legales</div>
            </div>
            <div class="space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-secondary/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <span class="font-bold text-secondary text-xs">15</span>
                    </div>
                    <div class="text-xs text-primary/55 leading-relaxed">
                        <strong class="text-primary/75 block mb-0.5">Días hábiles para responder</strong>
                        Plazo máximo establecido por el D.S. 101-2021-PCM.
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-primary/6 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0b142c" stroke-width="2" stroke-linecap="round" fill-opacity=".5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div class="text-xs text-primary/55 leading-relaxed">
                        <strong class="text-primary/75 block mb-0.5">Regulado por INDECOPI</strong>
                        Puedes acudir a INDECOPI si no recibes respuesta.
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?php endif; ?>

</main>

<!-- FOOTER -->
<footer class="no-print border-t border-primary/8 bg-white mt-14 py-7">
    <div class="max-w-6xl mx-auto px-5 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4 text-[12px] text-primary/40">
        <span>© <?= date('Y') ?> <?= EMPRESA_RAZON_SOCIAL ?> · RUC <?= EMPRESA_RUC ?></span>
        <span class="text-[10px] text-primary/25">Regulado por INDECOPI · Ley N° 29571 · Ley N° 29733</span>
    </div>
</footer>

<!-- MODAL AVISO VIRTUAL -->
<div id="notice-modal" class="no-print fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    <div class="absolute inset-0 cursor-pointer" onclick="closeNoticeModal()"></div>
    <div class="relative z-10 max-w-md w-full bg-white rounded-2xl overflow-hidden shadow-2xl flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-primary/8">
            <span class="text-sm font-semibold text-primary">Aviso Oficial — INDECOPI</span>
            <button onclick="closeNoticeModal()" class="w-8 h-8 rounded-lg hover:bg-canvas flex items-center justify-center text-primary/40 hover:text-primary transition-colors cursor-pointer text-lg">✕</button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
            <img src="Libro-reclamaciones/AvisoVirtual_page1.png" alt="Aviso Virtual Oficial INDECOPI" class="w-full h-auto">
        </div>
        <div class="px-5 py-3 border-t border-primary/6 text-center text-[10px] text-primary/35">Aviso oficial de disponibilidad de Libro de Reclamaciones · INDECOPI</div>
    </div>
</div>

<script>
function toggleApoderado() {
    const cb = document.getElementById('menor_edad');
    const f  = document.getElementById('apoderado_fields');
    const n  = document.getElementById('apoderado_nombres');
    const d  = document.getElementById('apoderado_doc_nro');
    if (cb.checked) { f.classList.remove('hidden'); n.required = true; d.required = true; }
    else            { f.classList.add('hidden');    n.required = false; d.required = false; n.value = ''; d.value = ''; }
}
window.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('menor_edad')) toggleApoderado();
    <?php if (!empty($errorMsg)): ?>
    if (typeof hcaptcha !== 'undefined') { hcaptcha.reset(); }
    <?php endif; ?>
    // Visual radio feedback
    document.querySelectorAll('.radio-card input[type=radio]').forEach(input => {
        const updateDot = () => {
            document.querySelectorAll(`input[name="${input.name}"]`).forEach(r => {
                r.closest('.radio-card').querySelector('.radio-dot')?.classList.toggle('hidden', !r.checked);
                r.closest('.radio-card').querySelector('.w-4').style.borderColor = r.checked ? '#f3580e' : '';
            });
        };
        input.addEventListener('change', updateDot);
        if (input.checked) updateDot();
    });
});
function openNoticeModal()  { const m = document.getElementById('notice-modal'); m.classList.remove('hidden'); m.classList.add('flex'); document.body.style.overflow = 'hidden'; }
function closeNoticeModal() { const m = document.getElementById('notice-modal'); m.classList.add('hidden'); m.classList.remove('flex'); document.body.style.overflow = ''; }
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeNoticeModal(); });
</script>
</body>
</html>
