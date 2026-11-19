<?php
/**
 * ARIADNA · Envío de correos por SMTP (sin librerías externas).
 * Compatible con Hostinger: smtp.hostinger.com, puerto 465 (SSL) o 587 (STARTTLS).
 * Con driver = 'log' los correos se guardan en storage/mail.log (útil para pruebas).
 */
declare(strict_types=1);

function send_mail(array $to, string $subject, string $html, array $cc = [], string $kind = 'general', ?int $officeId = null, ?int $advisorId = null): bool
{
    $to = array_values(array_unique(array_filter(array_map('trim', $to), fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))));
    $cc = array_values(array_diff(array_unique(array_filter(array_map('trim', $cc), fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))), $to));
    if (!$to && $cc) { $to = [array_shift($cc)]; }
    if (!$to) return false;

    $driver = cfg('mail.driver', 'log');
    $error = null;
    try {
        if ($driver === 'smtp') smtp_send($to, $cc, $subject, $html);
        else mail_log_file($to, $cc, $subject, $html);
        $ok = true;
    } catch (Throwable $e) {
        $ok = false;
        $error = $e->getMessage();
    }
    try {
        q('INSERT INTO email_log (kind, office_id, advisor_id, recipients, subject, status, error, created_at) VALUES (?,?,?,?,?,?,?,NOW())',
            [$kind, $officeId, $advisorId, implode(', ', array_merge($to, $cc)), mb_substr($subject, 0, 250), $ok ? 'enviado' : 'error', $error]);
    } catch (Throwable $e) { /* no detener por el log */ }
    return $ok;
}

function mail_log_file(array $to, array $cc, string $subject, string $html): void
{
    $dir = APP_ROOT . '/storage';
    if (!is_dir($dir)) mkdir($dir, 0750, true);
    $txt = "==== " . date('Y-m-d H:i:s') . "\nPara: " . implode(', ', $to) . "\nCC: " . implode(', ', $cc) . "\nAsunto: $subject\n\n" . $html . "\n\n";
    file_put_contents($dir . '/mail.log', $txt, FILE_APPEND | LOCK_EX);
}

/**
 * Datos del remitente según el programa activo. Edalo usa 'mail_edalo' de config.php si existe.
 * Lo que no ponga ahí (servidor, usuario, contraseña) lo toma de 'mail': así Edalo puede enviar
 * con el mismo buzón de Ariadna y solo cambiar el remitente (un alias como edalo@).
 */
function mail_cfg(): array
{
    $base = (array)cfg('mail', []);
    $key = P()['mail_key'] ?? 'mail';
    if ($key !== 'mail') {
        $own = array_filter((array)cfg($key, []), fn($v) => $v !== '' && $v !== null);
        if ($own) return array_merge($base, $own);
    }
    return $base;
}

function smtp_send(array $to, array $cc, string $subject, string $html): void
{
    $m = mail_cfg();
    $host = (string)($m['host'] ?? '');
    $port = (int)($m['port'] ?? 465);
    $secure = (string)($m['secure'] ?? 'ssl');
    $user = (string)($m['user'] ?? '');
    $pass = (string)($m['pass'] ?? '');
    $from = (string)($m['from'] ?? $user);
    $fromName = (string)($m['from_name'] ?? P()['name']);

    $remote = ($secure === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'SNI_enabled' => true]]);
    $fp = @stream_socket_client($remote, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) throw new RuntimeException("No se pudo conectar a $host:$port ($errstr)");
    stream_set_timeout($fp, 20);

    $read = function () use ($fp): string {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') break;
        }
        return $data;
    };
    $cmd = function (string $c, array $okCodes) use ($fp, $read): string {
        if ($c !== '') fwrite($fp, $c . "\r\n");
        $r = $read();
        $code = (int)substr($r, 0, 3);
        if (!in_array($code, $okCodes, true)) throw new RuntimeException('SMTP: ' . trim($r));
        return $r;
    };

    $ehlo = 'ariadna.local';
    $cmd('', [220]);
    $cmd('EHLO ' . $ehlo, [250]);
    if ($secure === 'tls') {
        $cmd('STARTTLS', [220]);
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) throw new RuntimeException('No se pudo iniciar TLS');
        $cmd('EHLO ' . $ehlo, [250]);
    }
    if ($user !== '') {
        $cmd('AUTH LOGIN', [334]);
        $cmd(base64_encode($user), [334]);
        $cmd(base64_encode($pass), [235]);
    }
    $cmd('MAIL FROM:<' . $from . '>', [250]);
    foreach (array_merge($to, $cc) as $rcpt) $cmd('RCPT TO:<' . $rcpt . '>', [250, 251]);
    $cmd('DATA', [354]);

    $enc = fn($s) => '=?UTF-8?B?' . base64_encode($s) . '?=';
    $boundary = 'b' . bin2hex(random_bytes(8));
    $text = trim(html_entity_decode(strip_tags(preg_replace('/<(br|\/p|\/tr|\/h\d|\/li)[^>]*>/i', "\n", $html)), ENT_QUOTES, 'UTF-8'));
    $headers = [
        'Date: ' . date('r'),
        'From: ' . $enc($fromName) . ' <' . $from . '>',
        'To: ' . implode(', ', $to),
    ];
    if ($cc) $headers[] = 'Cc: ' . implode(', ', $cc);
    $headers[] = 'Subject: ' . $enc($subject);
    $headers[] = 'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . (explode('@', $from)[1] ?? 'ariadna') . '>';
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
    $body = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($text))
        . "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($html))
        . "--$boundary--\r\n";
    $msg = implode("\r\n", $headers) . "\r\n\r\n" . $body;
    $msg = preg_replace('/^\./m', '..', $msg);
    fwrite($fp, $msg . "\r\n.\r\n");
    $cmd('', [250]);
    $cmd('QUIT', [221, 250]);
    fclose($fp);
}

/** Plantilla HTML de los correos (fondo claro, colores de la marca). */
function email_wrap(string $title, string $content): string
{
    $pp = P();
    $ind = prog() === 'industrial';
    $navy = $ind ? '#18263A' : '#1F2A44';
    $bg = $ind ? '#F3F4F2' : '#FBF8F1';
    $line = $ind ? '#DCE1E6' : '#E6DECB';
    $foot = $pp['name'] . ' · Team Leader del curso ' . $pp['course'] . ' · R²CP Elite de Oscar Márquez';
    return '<!doctype html><html><body style="margin:0;background:' . $bg . ';font-family:Arial,Helvetica,sans-serif;color:' . $navy . '">'
        . '<table width="100%" cellpadding="0" cellspacing="0" style="background:' . $bg . ';padding:24px 0"><tr><td align="center">'
        . '<table width="680" cellpadding="0" cellspacing="0" style="max-width:680px;width:100%;background:#ffffff;border:1px solid ' . $line . ';border-radius:12px">'
        . '<tr><td style="padding:20px 28px;border-bottom:3px solid #B89860"><table width="100%" cellpadding="0" cellspacing="0"><tr>'
        . '<td><div style="font-size:22px;font-weight:bold;letter-spacing:2px;color:' . $navy . '">' . h($pp['brand']) . '</div><div style="font-size:12px;color:#5F6576">by Oscar Márquez Seminars</div></td>'
        . '<td align="right"><div style="font-size:14px;font-weight:bold;letter-spacing:1px;color:#7A6235">R²CP ELITE</div><div style="font-size:12px;color:#7A6235;font-style:italic">' . h($pp['tagline']) . '</div></td>'
        . '</tr></table></td></tr>'
        . '<tr><td style="padding:24px 28px"><h1 style="font-size:20px;margin:0 0 14px;color:' . $navy . '">' . h($title) . '</h1>' . $content . '</td></tr>'
        . '<tr><td style="padding:16px 28px;font-size:12px;color:#5F6576;border-top:1px solid ' . $line . '">' . h($foot) . '</td></tr>'
        . '</table></td></tr></table></body></html>';
}

function email_dot(string $color): string
{
    $c = ['verde' => '#2E8B57', 'amarillo' => '#D9A400', 'rojo' => '#C0392B'][$color] ?? '#999';
    return '<span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:' . $c . ';vertical-align:middle"></span> ' . h(ucfirst($color));
}

/** Correos de dirección de la oficina (director, gerente, team leader). */
function office_recipients(int $officeId, array $roles = ['director', 'gerente', 'team_leader']): array
{
    $in = implode(',', array_fill(0, count($roles), '?'));
    return array_column(all("SELECT email FROM users WHERE office_id = ? AND active = 1 AND role IN ($in)", array_merge([$officeId], $roles)), 'email');
}

function admin_recipients(): array
{
    $list = array_column(all("SELECT email FROM users WHERE role = 'admin' AND active = 1"), 'email');
    $extra = trim((string)setting('admin_email'));
    if ($extra !== '') $list[] = $extra;
    return array_values(array_unique($list));
}
