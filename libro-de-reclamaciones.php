<?php
/**
 * Libro de Reclamaciones Virtual — ZENTEK360 S.A.C.S.
 * Conforme a la Ley N° 29571 (Código de Protección y Defensa del Consumidor)
 * y el D.S. N° 011-2011-PCM (modificado por D.S. 101-2021-PCM).
 */

date_default_timezone_set('America/Lima');

define('EMPRESA_RAZON_SOCIAL', 'ZENTEK360 S.A.C.S.');
define('EMPRESA_RUC',          '20616110099');
define('EMPRESA_DIRECCION',    'AV. REPUBLICA DE CHILE NRO. 324 INT. 601 URB. SANTA BEATRIZ (EDIFICIO POLARIS) LIMA - LIMA - JESUS MARIA');
define('EMPRESA_EMAIL',        'info@zentek360.com');

$smtpConfigFile = __DIR__ . '/config.smtp.php';
if (file_exists($smtpConfigFile)) require $smtpConfigFile;

if (!defined('SMTP_PORT'))           define('SMTP_PORT', 465);
if (!defined('SMTP_FROM_NAME'))      define('SMTP_FROM_NAME', 'ZENTEK360');
if (!defined('EMPRESA_NOTIF_EMAIL')) define('EMPRESA_NOTIF_EMAIL', 'reclamaciones@zentek360.com');

$recordsDir  = __DIR__ . '/reclamaciones';
$recordsFile = $recordsDir . '/records.json';
$lockFile    = $recordsDir . '/records.lock';

if (!is_dir($recordsDir)) {
    if (!@mkdir($recordsDir, 0755, true))
        $errorMsg = 'Error de configuración del servidor: no se puede crear el directorio de registros.';
}

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

// ── Enviar correo SMTP ────────────────────────────────────────────────────────
function enviarCorreoSMTP(string $toEmail, string $subject, string $htmlBody, string $pdfB64 = '', string $pdfName = '', string &$err = ''): bool {
    if (!defined('SMTP_HOST') || !defined('SMTP_USER') || !defined('SMTP_PASS')) {
        $err = 'Configuración SMTP no encontrada (falta config.smtp.php).'; return false;
    }
    $enc = fn(string $s): string => '=?UTF-8?B?' . base64_encode($s) . '?=';
    $fromEmail = SMTP_USER;
    $eol = "\r\n";
    $headers  = 'Date: ' . date('r') . $eol;
    $headers .= 'From: ' . $enc(SMTP_FROM_NAME) . ' <' . $fromEmail . '>' . $eol;
    $headers .= 'To: <' . $toEmail . '>' . $eol;
    $headers .= 'Subject: ' . $enc($subject) . $eol;
    $headers .= 'Reply-To: ' . EMPRESA_EMAIL . $eol;
    $headers .= 'MIME-Version: 1.0' . $eol;
    if ($pdfB64) {
        $b = '----=_Part_' . md5(uniqid());
        $headers .= 'Content-Type: multipart/mixed; boundary="' . $b . '"' . $eol;
        $body  = '--' . $b . $eol . 'Content-Type: text/html; charset=UTF-8' . $eol . 'Content-Transfer-Encoding: 7bit' . $eol . $eol . $htmlBody . $eol;
        $body .= '--' . $b . $eol . 'Content-Type: application/pdf; name="' . $pdfName . '"' . $eol . 'Content-Transfer-Encoding: base64' . $eol . 'Content-Disposition: attachment; filename="' . $pdfName . '"' . $eol . $eol . $pdfB64 . $eol;
        $body .= '--' . $b . '--';
    } else {
        $headers .= 'Content-Type: text/html; charset=UTF-8' . $eol;
        $body = $htmlBody;
    }
    $message = str_replace($eol . '.', $eol . '..', str_replace("\n", $eol, str_replace(["\r\n","\r","\n"], "\n", $headers . $eol . $body)));
    $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]]);
    $useSSL = in_array(SMTP_PORT, [465, 4655]);
    $fp = @stream_socket_client(($useSSL ? 'ssl://' : '') . SMTP_HOST . ':' . SMTP_PORT, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) { $err = "Conexión SMTP fallida: $errstr ($errno)"; return false; }
    stream_set_timeout($fp, 20);
    $read = function() use ($fp): string { $d=''; while(($l=fgets($fp,515))!==false){$d.=$l;if(strlen($l)<4||$l[3]===' ')break;} return $d; };
    $cmd  = function(string $c) use ($fp, $read): string { fwrite($fp, $c . "\r\n"); return $read(); };
    $ok   = function(string $r, array $codes) use (&$err): bool { foreach($codes as $c){if(strncmp($r,$c,strlen($c))===0)return true;} $err=trim($r);return false; };
    $fail = function() use ($fp) { @fwrite($fp,"QUIT\r\n"); @fclose($fp); return false; };

    if (!$ok($read(),                              ['220'])) return $fail();
    if (!$ok($cmd('EHLO '.SMTP_HOST),             ['250'])) return $fail();
    if (!$ok($cmd('AUTH LOGIN'),                   ['334'])) return $fail();
    if (!$ok($cmd(base64_encode(SMTP_USER)),       ['334'])) return $fail();
    if (!$ok($cmd(base64_encode(SMTP_PASS)),       ['235'])) return $fail();
    if (!$ok($cmd('MAIL FROM:<'.$fromEmail.'>'),   ['250'])) return $fail();
    if (!$ok($cmd('RCPT TO:<'.$toEmail.'>'),  ['250','251'])) return $fail();
    if (!$ok($cmd('DATA'),                         ['354'])) return $fail();
    if (!$ok($cmd($message."\r\n."),               ['250'])) return $fail();
    $cmd('QUIT'); fclose($fp);
    return true;
}

$success = false; $errorMsg = ''; $generatedCode = ''; $submittedData = []; $debugLog = [];

set_error_handler(function($errno) use (&$errorMsg) {
    if ($errno === E_ERROR || $errno === E_USER_ERROR) $errorMsg = 'Error interno. Inténtelo más tarde.';
    return true;
});

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = fn(string $k, int $filter = FILTER_SANITIZE_SPECIAL_CHARS) => filter_input(INPUT_POST, $k, $filter);
    $nombres      = $f('nombres');
    $doc_tipo     = $f('doc_tipo');
    $doc_nro      = $f('doc_nro');
    $email        = $f('email', FILTER_VALIDATE_EMAIL);
    $telefono     = $f('telefono');
    $direccion    = $f('direccion');
    $departamento = $f('departamento');
    $provincia    = $f('provincia');
    $distrito     = $f('distrito');
    $menor_edad   = isset($_POST['menor_edad']);
    $ap_nombres   = $f('apoderado_nombres');
    $ap_doc_tipo  = $f('apoderado_doc_tipo');
    $ap_doc_nro   = $f('apoderado_doc_nro');
    $bien_tipo    = $f('bien_tipo');
    $monto        = $f('monto', FILTER_SANITIZE_NUMBER_FLOAT | FILTER_FLAG_ALLOW_FRACTION);
    $bien_desc    = $f('bien_desc');
    $reclamo_tipo = $f('reclamo_tipo');
    $detalle      = $f('detalle');
    $pedido       = $f('pedido');

    if (!$nombres||!$doc_tipo||!$doc_nro||!$email||!$direccion||!$bien_tipo||!$reclamo_tipo||!$detalle||!$pedido||!empty($errorMsg)) {
        if (empty($errorMsg)) $errorMsg = 'Por favor, rellene todos los campos obligatorios.';
    } else {
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
                'bien_tipo' => $bien_tipo,
                'monto'     => $monto ? number_format((float)$monto, 2, '.', '') : '0.00',
                'bien_desc' => $bien_desc, 'reclamo_tipo' => $reclamo_tipo,
                'detalle' => $detalle, 'pedido' => $pedido, 'estado' => 'Pendiente',
            ];
            $records[] = $submittedData;
            file_put_contents($recordsFile, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            flock($fp, LOCK_UN);
            $success = true;
        } else {
            $errorMsg = 'Error al registrar en el servidor. Inténtelo nuevamente.';
        }
        if ($fp) fclose($fp);

        if ($success) {
            $pdfBytes = '';
            if ($fpdfAvailable) { try { $pdfBytes = generarPDFReclamo($submittedData); $debugLog[] = 'PDF generado OK.'; } catch(Exception $e) { $debugLog[] = 'PDF falló: ' . $e->getMessage(); } }
            else { $debugLog[] = 'FPDF no disponible (falta lib/fpdf.php).'; }
            $pdfB64  = $pdfBytes ? chunk_split(base64_encode($pdfBytes)) : '';
            $pdfName = 'Hoja_Reclamacion_' . $generatedCode . '.pdf';
            if ($pdfBytes) @file_put_contents($recordsDir . '/' . $pdfName, $pdfBytes);

            $emailBody = "
            <div style='font-family:Inter,Arial,sans-serif;color:#0b142c;max-width:600px;margin:0 auto;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;'>
                <div style='background:#0b142c;padding:0;'>
                    <div style='background:#f3580e;height:4px;'></div>
                    <div style='padding:28px 32px;'>
                        <h2 style='margin:0 0 4px;font-size:20px;color:#ffffff;font-weight:700;letter-spacing:-.3px;'>HOJA DE RECLAMACIÓN VIRTUAL</h2>
                        <p style='margin:0;color:rgba(255,255,255,.6);font-size:13px;'>ZENTEK360 S.A.C.S. — Código: <strong style='color:#f3580e;'>$generatedCode</strong></p>
                    </div>
                </div>
                <div style='padding:28px 32px;line-height:1.7;font-size:14px;'>
                    <p style='margin:0 0 16px;'>Estimado(a) <strong>$nombres</strong>,</p>
                    <p style='margin:0 0 16px;'>Confirmamos la recepción de tu reclamación registrada el <strong>" . date('d/m/Y') . "</strong>. Adjunto encontrarás el cargo de tu Hoja de Reclamación Virtual.</p>
                    <p style='margin:0 0 24px;'>Daremos respuesta en un plazo máximo de <strong>15 días hábiles</strong> (Ley N° 29571).</p>
                    <div style='background:#f8f9fc;border-radius:8px;padding:20px;border:1px solid #e5e7eb;'>
                        <table style='width:100%;border-collapse:collapse;font-size:13px;'>
                            <tr><td style='padding:5px 0;font-weight:600;color:#6b7280;width:130px;'>Consumidor:</td><td style='color:#0b142c;'>$nombres ($doc_tipo $doc_nro)</td></tr>
                            <tr><td style='padding:5px 0;font-weight:600;color:#6b7280;'>Bien:</td><td style='text-transform:capitalize;color:#0b142c;'>$bien_tipo — S/. " . ($monto ?: '0.00') . "</td></tr>
                            <tr><td style='padding:5px 0;font-weight:600;color:#6b7280;'>Incidencia:</td><td style='font-weight:700;color:" . ($reclamo_tipo=='reclamo'?'#dc2626':'#d97706') . ";text-transform:capitalize;'>$reclamo_tipo</td></tr>
                            <tr><td style='padding:5px 0;font-weight:600;color:#6b7280;vertical-align:top;'>Detalle:</td><td style='color:#374151;'>$detalle</td></tr>
                            <tr><td style='padding:5px 0;font-weight:600;color:#6b7280;vertical-align:top;'>Pedido:</td><td style='color:#374151;'>$pedido</td></tr>
                        </table>
                    </div>
                </div>
                <div style='background:#f8f9fc;padding:16px 32px;text-align:center;font-size:11px;color:#9ca3af;border-top:1px solid #e5e7eb;'>Cargo automático — no responder a este mensaje · ZENTEK360 S.A.C.S. · RUC 20616110099</div>
            </div>";

            $errC = '';
            $okC  = enviarCorreoSMTP($email, "Cargo de Hoja de Reclamación N° $generatedCode — ZENTEK360", $emailBody, $pdfB64, $pdfName, $errC);
            $debugLog[] = $okC ? "Correo al cliente ($email): ENVIADO." : "Correo al cliente FALLÓ: $errC";

            $emailEmpresa = "
            <div style='font-family:Inter,Arial,sans-serif;color:#0b142c;max-width:600px;margin:0 auto;border:1px solid #e5e7eb;border-radius:12px;padding:28px 32px;'>
                <h2 style='color:#f3580e;margin-top:0;font-size:18px;'>Nueva Reclamación — $generatedCode</h2>
                <p>Plazo máximo de respuesta: <strong>15 días hábiles</strong>.</p>
                <p><strong>Reclamante:</strong> $nombres · $doc_tipo $doc_nro · $email · $telefono</p>
                <p><strong>Domicilio:</strong> $direccion, $distrito - $provincia ($departamento)</p>
                <p><strong>Bien:</strong> " . ucfirst($bien_tipo) . " · S/. " . ($monto ?: '0.00') . " · $bien_desc</p>
                <p><strong>Tipo:</strong> " . strtoupper($reclamo_tipo) . "</p>
                <div style='background:#fef2f2;padding:14px 16px;border-left:4px solid #f3580e;border-radius:4px;margin-bottom:12px;'><strong>Detalle:</strong><br>" . nl2br($detalle) . "</div>
                <div style='background:#eff6ff;padding:14px 16px;border-left:4px solid #3b82f6;border-radius:4px;'><strong>Pedido:</strong><br>" . nl2br($pedido) . "</div>
            </div>";
            $errE = '';
            $okE  = enviarCorreoSMTP(EMPRESA_NOTIF_EMAIL, "NUEVA RECLAMACIÓN N° $generatedCode — $nombres", $emailEmpresa, $pdfB64, $pdfName, $errE);
            $debugLog[] = $okE ? 'Correo a empresa (' . EMPRESA_NOTIF_EMAIL . '): ENVIADO.' : 'Correo a empresa FALLÓ: ' . $errE;
        }
    }
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

            <button type="submit" class="btn-orange w-full py-4 rounded-xl text-sm tracking-wide cursor-pointer flex items-center justify-center gap-2">
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
