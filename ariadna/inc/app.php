<?php
/**
 * ARIADNA · Núcleo de la aplicación
 * Configuración, base de datos, sesiones, seguridad y utilidades.
 */
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('APP_VERSION', '1.1.0');

if (!is_file(APP_ROOT . '/config.php')) {
    if (PHP_SAPI !== 'cli' && basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'install.php') {
        header('Location: install.php');
        exit;
    }
    $GLOBALS['CONFIG'] = null;
} else {
    $GLOBALS['CONFIG'] = require APP_ROOT . '/config.php';
}

date_default_timezone_set(cfg('timezone', 'America/Cancun'));
mb_internal_encoding('UTF-8');

require_once __DIR__ . '/engine.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/push.php';
require_once __DIR__ . '/layout.php';

// ---------------------------------------------------------------------------
// Configuración
// ---------------------------------------------------------------------------
function cfg(string $key, $default = null)
{
    $c = $GLOBALS['CONFIG'] ?? null;
    if (!$c) return $default;
    $parts = explode('.', $key);
    foreach ($parts as $p) {
        if (!is_array($c) || !array_key_exists($p, $c)) return $default;
        $c = $c[$p];
    }
    return $c;
}

function base_url(string $path = ''): string
{
    $b = rtrim((string)cfg('base_url', ''), '/');
    if ($b === '' && PHP_SAPI !== 'cli') {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        $b = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $dir;
    }
    return $b . '/' . ltrim($path, '/');
}

// ---------------------------------------------------------------------------
// Base de datos (PDO + consultas preparadas siempre)
// ---------------------------------------------------------------------------
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = cfg('db');
        $dsn = 'mysql:host=' . $c['host'] . ';dbname=' . $c['name'] . ';charset=utf8mb4';
        if (!empty($c['port'])) $dsn .= ';port=' . (int)$c['port'];
        $pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function one(string $sql, array $params = []): ?array
{
    $r = q($sql, $params)->fetch();
    return $r ?: null;
}

function all(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

function val(string $sql, array $params = [])
{
    $r = q($sql, $params)->fetchColumn();
    return $r === false ? null : $r;
}

// ---------------------------------------------------------------------------
// Ajustes editables por el administrador (tabla settings)
// ---------------------------------------------------------------------------
function setting_defaults(): array
{
    return [
        'yellow_days'            => '2',   // días hábiles seguidos sin registrar => amarillo
        'red_s1_enabled'         => '1',   // rojo al cierre S1 sin actividad
        's2_min_contactos'       => '100', // mínimo acumulado S1+S2
        's2_min_promovidas'      => '5',   // mínimo acumulado S1+S2
        's3_red_enabled'         => '1',   // rojo al cierre S3 con 0 citas y 0 captaciones
        'checkpoint_min_score'   => '50',  // puntos mínimos punto de control S3
        'oh_min_attendees'       => '5',
        'registro_min_pct'       => '80',  // % de días registrados para "registro ordenado"
        'arranque_dias'          => '5',   // días hábiles seguidos de llamadas de arranque
        'arranque_llamadas'      => '20',  // llamadas diarias de arranque
        'goal_min_contactos_dia' => '20',  // mínimo de contactos al día para las metas propias
        'r1_arranque_title'      => 'Haz tus {n} llamadas a tu Impulsor de Memoria',
        'r1_title'               => 'Haz tus contactos del día (tu meta: {meta})',
        'report_weekday'         => '5',   // 1=lunes ... 7=domingo
        'report_hour'            => '18',
        'report_timezone'        => 'America/Cancun',
        'reminder_enabled'       => '1',   // interruptor general de recordatorios a asesores
        'ayer_until_hour'        => '12',  // hasta qué hora se puede registrar el día anterior
        'admin_email'            => '',
        'routine_items'          => json_encode([
            ['key' => 'R1', 'title' => 'Haz tus contactos del día',
             'guide' => 'Antes de hacer cualquier otra cosa, haz tus llamadas. Usa 1 de los 3 diálogos para llamar que vienen en tu hoja de Mi Mapa. Cada llamada cuenta como contacto. Contactar todos los días es lo que mantiene tu semana en movimiento. Súmalos en «Contactos».'],
            ['key' => 'R2', 'title' => 'Anota cada contacto en tu agenda, la lista de tu celular o el CRM de tu oficina',
             'guide' => 'Con lo que te respondió y el siguiente paso. Tus registros detallados viven ahí; en Ariadna solo me cuentas cuántos fueron.'],
        ], JSON_UNESCAPED_UNICODE),
    ];
}

function setting(string $key)
{
    static $cache = null;
    if ($key === '__flush') { $cache = null; return null; }
    if ($cache === null) {
        $cache = [];
        try {
            foreach (all('SELECT k, v FROM settings') as $r) $cache[$r['k']] = $r['v'];
        } catch (Throwable $e) { /* tabla aún no existe */ }
    }
    if (array_key_exists($key, $cache)) return $cache[$key];
    return setting_defaults()[$key] ?? null;
}

function set_setting(string $key, string $value): void
{
    q('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', [$key, $value]);
    setting('__flush');
}

// ---------------------------------------------------------------------------
// Sesión y seguridad
// ---------------------------------------------------------------------------
function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name('ariadna_s');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function security_headers(): void
{
    if (headers_sent()) return;
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin'); // Bunny necesita saber desde qué dominio se ve el video
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(self), fullscreen=*');
    if (is_https()) header('Strict-Transport-Security: max-age=31536000');
}

function enforce_https(): void
{
    if (PHP_SAPI === 'cli' || !cfg('force_https', false) || is_https()) return;
    header('Location: https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
    exit;
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

function csrf_check(): void
{
    start_session();
    $t = $_POST['csrf'] ?? '';
    if (!is_string($t) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $t)) {
        http_response_code(400);
        exit('La sesión expiró. Regresa y vuelve a intentarlo.');
    }
}

function new_token(): string
{
    return bin2hex(random_bytes(32)); // 64 caracteres, 256 bits
}

function h($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(string $msg = null, string $type = 'ok')
{
    start_session();
    if ($msg !== null) { $_SESSION['flash'][] = [$type, $msg]; return null; }
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function post(string $k, $default = '')
{
    $v = $_POST[$k] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

function client_ip(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

// ---------------------------------------------------------------------------
// Usuarios de oficina y administrador
// ---------------------------------------------------------------------------
const ROLE_LABELS = [
    'admin'       => 'Administrador',
    'director'    => 'Director',
    'gerente'     => 'Gerente',
    'team_leader' => 'Team Leader',
];

function current_user(): ?array
{
    start_session();
    static $u = false;
    if ($u !== false) return $u;
    $u = null;
    if (!empty($_SESSION['uid'])) {
        $u = one('SELECT * FROM users WHERE id = ? AND active = 1', [$_SESSION['uid']]);
    }
    return $u;
}

function require_user(array $roles = []): array
{
    $u = current_user();
    if (!$u) redirect('login.php');
    if ($roles && !in_array($u['role'], $roles, true)) {
        http_response_code(403);
        exit('No tienes acceso a esta sección.');
    }
    return $u;
}

function is_admin(?array $u = null): bool
{
    $u = $u ?? current_user();
    return $u && $u['role'] === 'admin';
}

/** Oficina que el usuario puede ver. El admin puede elegir cualquiera con ?office=ID. */
function panel_office(array $u): array
{
    if (is_admin($u)) {
        $id = (int)($_GET['office'] ?? $_POST['office'] ?? 0);
        $o = $id ? one('SELECT * FROM offices WHERE id = ?', [$id]) : null;
        if (!$o) redirect('admin.php');
        return $o;
    }
    $o = one('SELECT * FROM offices WHERE id = ? AND active = 1', [$u['office_id']]);
    if (!$o) { http_response_code(403); exit('Tu oficina no está activa.'); }
    return $o;
}

/** Asesor por id, solo si pertenece a la oficina permitida. */
function office_advisor(array $office, int $id): array
{
    $a = one('SELECT * FROM advisors WHERE id = ? AND office_id = ?', [$id, $office['id']]);
    if (!$a) { http_response_code(404); exit('Asesor no encontrado.'); }
    return $a;
}

function office_qs(array $office): string
{
    return is_admin() ? ('office=' . (int)$office['id']) : '';
}

function too_many_attempts(string $key, int $max = 6, int $minutes = 15): bool
{
    q('DELETE FROM login_attempts WHERE at < (NOW() - INTERVAL 1 DAY)');
    $n = (int)val('SELECT COUNT(*) FROM login_attempts WHERE k = ? AND at > (NOW() - INTERVAL ' . (int)$minutes . ' MINUTE)', [$key]);
    return $n >= $max;
}

function record_attempt(string $key): void
{
    q('INSERT INTO login_attempts (k, at) VALUES (?, NOW())', [$key]);
}

function clear_attempts(string $key): void
{
    q('DELETE FROM login_attempts WHERE k = ?', [$key]);
}

// ---------------------------------------------------------------------------
// Utilidades de texto y fechas (español de México)
// ---------------------------------------------------------------------------
const DIAS = [1 => 'lunes', 2 => 'martes', 3 => 'miércoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sábado', 7 => 'domingo'];
const MESES = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

function fecha_larga(string $ymd): string
{
    $d = new DateTimeImmutable($ymd);
    return DIAS[(int)$d->format('N')] . ' ' . (int)$d->format('j') . ' de ' . MESES[(int)$d->format('n')];
}

function fecha_corta(?string $ymd): string
{
    if (!$ymd) return '—';
    $d = new DateTimeImmutable($ymd);
    return (int)$d->format('j') . ' ' . mb_substr(MESES[(int)$d->format('n')], 0, 3);
}

function first_name(string $name): string
{
    $p = preg_split('/\s+/', trim($name));
    return $p[0] ?? $name;
}

function wa_phone(string $phone): string
{
    $d = preg_replace('/\D+/', '', $phone);
    if (strlen($d) === 10) $d = '52' . $d; // celular de México a 10 dígitos
    return $d;
}

function advisor_link(array $a): string
{
    return base_url('hoy.php?t=' . $a['token']);
}

function wa_link(array $a, array $office): string
{
    $msg = "Hola " . first_name($a['name']) . ", te doy la bienvenida a Ariadna, tu acompañamiento de arranque en " . $office['name'] . ".\n\n"
        . "Este es tu enlace personal (no lo compartas):\n" . advisor_link($a) . "\n\n"
        . "Ábrelo en tu celular y agrégalo a tu pantalla de inicio. Cada día te dice qué hacer y registras tu avance en menos de 2 minutos.\n\n"
        . "Sigue el hilo. 🧵";
    return 'https://wa.me/' . wa_phone($a['phone']) . '?text=' . rawurlencode($msg);
}
