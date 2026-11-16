<?php
/**
 * ARIADNA · Acceso del asesor por enlace personal (token) + PIN opcional.
 */
declare(strict_types=1);

function token_from_request(): string
{
    $t = $_POST['t'] ?? $_GET['t'] ?? '';
    return (is_string($t) && preg_match('/^[a-f0-9]{64}$/', $t)) ? $t : '';
}

/** Regresa [asesor, oficina] o muestra un aviso amable y termina. */
function advisor_from_token(): array
{
    $t = token_from_request();
    $a = $t ? one('SELECT * FROM advisors WHERE token = ?', [$t]) : null;
    $o = $a ? one('SELECT * FROM offices WHERE id = ?', [$a['office_id']]) : null;
    if (!$a || !$o || !(int)$o['active']) {
        http_response_code(404);
        page_start('Enlace no válido', ['body' => 'auth']);
        echo '<div class="card narrow center">' . brand_mark() . '<h2>Este enlace no es válido</h2><p>Pídele a tu oficina que te envíe de nuevo tu enlace personal de Ariadna.</p></div>';
        page_end();
        exit;
    }
    return [$a, $o];
}

function pin_cookie_name(array $a): string
{
    return 'ariadna_pin_' . (int)$a['id'];
}

function pin_cookie_value(array $a): string
{
    return hash_hmac('sha256', $a['id'] . '|' . $a['pin_hash'] . '|' . $a['token'], (string)cfg('app_secret', 'ariadna'));
}

function pin_verified(array $a): bool
{
    start_session();
    if (!empty($_SESSION['pin_ok'][$a['id']])) return true;
    $c = $_COOKIE[pin_cookie_name($a)] ?? '';
    return is_string($c) && $a['pin_hash'] && hash_equals(pin_cookie_value($a), $c);
}

function pin_remember(array $a): void
{
    start_session();
    $_SESSION['pin_ok'][$a['id']] = true;
    setcookie(pin_cookie_name($a), pin_cookie_value($a), [
        'expires' => time() + 30 * 86400, 'path' => '/', 'secure' => is_https(), 'httponly' => true, 'samesite' => 'Lax',
    ]);
}

/** Guarda la foto subida: se valida, se reduce y se vuelve a codificar como JPG. */
function save_upload_photo(array $file, int $advisorId, string $date, string $key): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > 12 * 1024 * 1024) return null;
    $info = @getimagesize($file['tmp_name']);
    if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true)) return null;
    $img = @imagecreatefromstring((string)file_get_contents($file['tmp_name']));
    if (!$img) return null;
    if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $exif = @exif_read_data($file['tmp_name']);
        $rot = ['3' => 180, '6' => -90, '8' => 90][(string)($exif['Orientation'] ?? '')] ?? 0;
        if ($rot) $img = imagerotate($img, $rot, 0);
    }
    $w = imagesx($img); $h = imagesy($img); $max = 1600;
    if (max($w, $h) > $max) {
        $r = $max / max($w, $h);
        $img = imagescale($img, (int)round($w * $r), (int)round($h * $r));
    }
    $dir = APP_ROOT . '/uploads/' . $advisorId;
    if (!is_dir($dir)) mkdir($dir, 0750, true);
    $name = $date . '_' . preg_replace('/[^A-Za-z0-9]/', '', $key) . '_' . bin2hex(random_bytes(6)) . '.jpg';
    imagejpeg($img, $dir . '/' . $name, 82);
    imagedestroy($img);
    return $advisorId . '/' . $name;
}
