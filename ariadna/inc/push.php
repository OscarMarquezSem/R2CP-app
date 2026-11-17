<?php
/**
 * ARIADNA · Notificaciones al celular (Web Push, RFC 8291 + VAPID RFC 8292), sin librerías externas.
 * Requiere las extensiones openssl y curl de PHP (vienen activas en Hostinger).
 */
declare(strict_types=1);

function b64u_enc(string $s): string { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }
function b64u_dec(string $s): string { return (string)base64_decode(strtr($s, '-_', '+/') . str_repeat('=', (4 - strlen($s) % 4) % 4)); }

/** Crea (una sola vez) el par de llaves VAPID y lo guarda en settings. */
function vapid_keys(): ?array
{
    $priv = setting('vapid_private_pem');
    $pub = setting('vapid_public');
    if ($priv && $pub) return ['private' => $priv, 'public' => $pub];
    if (!function_exists('openssl_pkey_new')) return null;
    $key = @openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
    if (!$key) return null;
    openssl_pkey_export($key, $pem);
    $d = openssl_pkey_get_details($key);
    $raw = "\x04" . str_pad($d['ec']['x'], 32, "\0", STR_PAD_LEFT) . str_pad($d['ec']['y'], 32, "\0", STR_PAD_LEFT);
    set_setting('vapid_private_pem', $pem);
    set_setting('vapid_public', b64u_enc($raw));
    return ['private' => $pem, 'public' => b64u_enc($raw)];
}

function vapid_public_key(): string
{
    try { $k = vapid_keys(); return $k['public'] ?? ''; } catch (Throwable $e) { return ''; }
}

/** Llave pública P-256 en bruto (65 bytes) → PEM. */
function p256_raw_to_pem(string $raw): string
{
    $der = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200') . $raw;
    return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
}

/** Firma ECDSA DER → formato JOSE (r||s, 64 bytes). */
function ecdsa_der_to_raw(string $der): string
{
    $pos = 2;
    if (ord($der[1]) & 0x80) $pos += ord($der[1]) & 0x7f;
    $read = function () use ($der, &$pos): string {
        $pos++; // 0x02
        $len = ord($der[$pos++]);
        $v = substr($der, $pos, $len);
        $pos += $len;
        return str_pad(ltrim($v, "\0"), 32, "\0", STR_PAD_LEFT);
    };
    $r = $read();
    $s = $read();
    return $r . $s;
}

function vapid_jwt(string $audience, array $keys): string
{
    $sub = setting('admin_email') ?: 'ariadna@localhost';
    $h = b64u_enc(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
    $c = b64u_enc(json_encode(['aud' => $audience, 'exp' => time() + 12 * 3600, 'sub' => 'mailto:' . $sub]));
    openssl_sign($h . '.' . $c, $sig, $keys['private'], OPENSSL_ALGO_SHA256);
    return $h . '.' . $c . '.' . b64u_enc(ecdsa_der_to_raw($sig));
}

/** Cifra el mensaje para una suscripción (aes128gcm, RFC 8291). */
function webpush_encrypt(string $payload, string $p256dhB64, string $authB64): ?string
{
    $uaPublic = b64u_dec($p256dhB64);
    $auth = b64u_dec($authB64);
    if (strlen($uaPublic) !== 65 || strlen($auth) < 16) return null;
    $local = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
    $d = openssl_pkey_get_details($local);
    $asPublic = "\x04" . str_pad($d['ec']['x'], 32, "\0", STR_PAD_LEFT) . str_pad($d['ec']['y'], 32, "\0", STR_PAD_LEFT);
    $peer = openssl_pkey_get_public(p256_raw_to_pem($uaPublic));
    if (!$peer) return null;
    $ecdh = openssl_pkey_derive($peer, $local, 32);
    if ($ecdh === false) return null;
    $ecdh = str_pad($ecdh, 32, "\0", STR_PAD_LEFT);
    $ikm = hash_hkdf('sha256', $ecdh, 32, "WebPush: info\0" . $uaPublic . $asPublic, $auth);
    $salt = random_bytes(16);
    $cek = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\0", $salt);
    $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\0", $salt);
    $tag = '';
    $cipher = openssl_encrypt($payload . "\x02", 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag);
    if ($cipher === false) return null;
    return $salt . pack('N', 4096) . chr(65) . $asPublic . $cipher . $tag;
}

/** Envía una notificación. Regresa el código HTTP (201 = enviada; 404/410 = suscripción vencida). */
function webpush_send(array $sub, array $message): int
{
    $keys = vapid_keys();
    if (!$keys || !function_exists('curl_init')) return 0;
    $body = webpush_encrypt(json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $sub['p256dh'], $sub['auth']);
    if ($body === null) return 0;
    $u = parse_url($sub['endpoint']);
    $aud = ($u['scheme'] ?? 'https') . '://' . ($u['host'] ?? '');
    $ch = curl_init($sub['endpoint']);
    curl_setopt_array($ch, [
        CURLOPT_POST => true, CURLOPT_POSTFIELDS => $body, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/octet-stream', 'Content-Encoding: aes128gcm', 'TTL: 43200', 'Urgency: normal',
            'Authorization: vapid t=' . vapid_jwt($aud, $keys) . ', k=' . $keys['public'],
        ],
    ]);
    curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code;
}

/** Notifica a un asesor en todos sus dispositivos. Regresa cuántas notificaciones salieron. */
function push_to_advisor(int $advisorId, string $title, string $body, string $url): int
{
    $sent = 0;
    foreach (all('SELECT * FROM push_subscriptions WHERE advisor_id = ?', [$advisorId]) as $s) {
        $code = webpush_send($s, ['title' => $title, 'body' => $body, 'url' => $url]);
        if ($code >= 200 && $code < 300) $sent++;
        elseif (in_array($code, [404, 410], true)) q('DELETE FROM push_subscriptions WHERE id = ?', [$s['id']]);
    }
    return $sent;
}
