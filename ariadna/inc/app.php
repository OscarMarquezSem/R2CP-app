<?php
/**
 * ARIADNA · Núcleo de la aplicación
 * Configuración, base de datos, sesiones, seguridad y utilidades.
 */
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('APP_VERSION', '1.4.0');

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

require_once __DIR__ . '/programs.php';
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
        's2_min_promovidas'      => '6',   // mínimo acumulado S1+S2 (3 por semana)
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
        'tl_since'               => '2000-01-01', // desde qué fecha cuentan las citas con el TL (para el semáforo)
        'ayer_until_hour'        => '12',  // hasta qué hora se puede registrar el día anterior
        'admin_email'            => '',
        'routine_items'          => json_encode([
            ['key' => 'R1', 'title' => 'Haz tus contactos del día',
             'guide' => 'Antes de hacer cualquier otra cosa, haz tus llamadas. Usa 1 de los 3 diálogos para llamar que vienen en tu hoja de Mi Mapa. Cada llamada cuenta como contacto. Contactar todos los días es lo que mantiene tu semana en movimiento. Súmalos en «Contactos».'],
            ['key' => 'R2', 'title' => 'Anota cada contacto en tu agenda, la lista de tu celular o el CRM de tu oficina',
             'guide' => 'Con lo que te respondió y el siguiente paso. Tus registros detallados viven ahí; en Ariadna solo me cuentas cuántos fueron.'],
        ], JSON_UNESCAPED_UNICODE),
        'routine_items_industrial' => json_encode([
            ['key' => 'R1', 'title' => 'Haz tus 10 llamadas del Impulsor Industrial',
             'guide' => 'Antes de hacer cualquier otra cosa, haz tus 10 llamadas. Usa el diálogo a conocidos de tu Impulsor Industrial y cierra siempre pidiendo un referido. Prospectar todos los días es lo que mantiene tu semana en movimiento. Súmalas en «Llamadas del Impulsor».'],
            ['key' => 'R2', 'title' => 'Anota tus llamadas en tu registro con el siguiente paso de cada contacto',
             'guide' => 'Si hoy hiciste un recorrido o una visita, anota también lo que viste. Tus registros detallados viven en tu Mi Mapa Industrial; en Edalo solo me cuentas cuántos fueron.'],
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

/**
 * Actualiza la base de datos sola cuando una versión nueva de Ariadna necesita tablas nuevas.
 * Así no hay que correr nada a mano al subir una actualización.
 */
function ensure_schema(): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    $v = (int)setting('schema_version');
    if ($v >= 4) return;
    if ($v >= 3) { ensure_schema_v4(); return; }
    if ($v < 2) db()->exec("CREATE TABLE IF NOT EXISTS advisor_events (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        advisor_id INT UNSIGNED NOT NULL,
        user_id INT UNSIGNED NULL,
        user_name VARCHAR(150) NULL,
        user_role VARCHAR(20) NULL,
        kind VARCHAR(20) NOT NULL,
        note TEXT NULL,
        created_at DATETIME NOT NULL,
        KEY idx_adv (advisor_id, created_at),
        CONSTRAINT fk_ev_adv FOREIGN KEY (advisor_id) REFERENCES advisors(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    // Versión 3: Reto del video (preguntas por video y resultados por asesor).
    if (!all("SHOW COLUMNS FROM plan_activities LIKE 'reto'")) db()->exec('ALTER TABLE plan_activities ADD COLUMN reto TEXT NULL');
    db()->exec("CREATE TABLE IF NOT EXISTS advisor_retos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  advisor_id INT UNSIGNED NOT NULL,
  activity_id INT UNSIGNED NOT NULL,
  answers VARCHAR(20) NOT NULL,
  ok_count TINYINT NOT NULL DEFAULT 0,
  attempt TINYINT NOT NULL DEFAULT 1,
  note TEXT NULL,
  user_id INT UNSIGNED NULL,
  user_name VARCHAR(150) NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_adv_act (advisor_id, activity_id),
  CONSTRAINT fk_reto_adv FOREIGN KEY (advisor_id) REFERENCES advisors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    set_setting('schema_version', '3');
    ensure_schema_v4();
}

/** Versión 4: dos programas (Ariadna · Residencial y Edalo · Comercial e Industrial). */
function ensure_schema_v4(): void
{
    $has = fn(string $t, string $c): bool => (bool)all("SHOW COLUMNS FROM `$t` LIKE '$c'");
    if (!$has('offices', 'programs')) db()->exec("ALTER TABLE offices ADD COLUMN programs VARCHAR(40) NOT NULL DEFAULT 'residencial'");
    if (!$has('advisors', 'program')) db()->exec("ALTER TABLE advisors ADD COLUMN program VARCHAR(20) NOT NULL DEFAULT 'residencial'");
    foreach (['ind_cap_done' => 'TINYINT(1) NOT NULL DEFAULT 0', 'ind_cap_date' => 'DATE NULL', 'ind_oferta_done' => 'TINYINT(1) NOT NULL DEFAULT 0',
              'ind_oferta_date' => 'DATE NULL', 'ind_inq_done' => 'TINYINT(1) NOT NULL DEFAULT 0', 'ind_inq_date' => 'DATE NULL',
              'know_props' => 'TINYINT NULL', 'know_zones' => 'TINYINT NULL'] as $c => $def) {
        if (!$has('advisors', $c)) db()->exec("ALTER TABLE advisors ADD COLUMN $c $def");
    }
    foreach (['llamadas', 'recorridos', 'fichas', 'cadenas', 'leads', 'piezas'] as $c) {
        if (!$has('daily_logs', $c)) db()->exec("ALTER TABLE daily_logs ADD COLUMN $c INT NOT NULL DEFAULT 0");
        if (!$has('plan_weeks', "meta_$c")) db()->exec("ALTER TABLE plan_weeks ADD COLUMN meta_$c INT NOT NULL DEFAULT 0");
    }
    if (!$has('plan_activities', 'program')) {
        db()->exec("ALTER TABLE plan_activities ADD COLUMN program VARCHAR(20) NOT NULL DEFAULT 'residencial'");
        db()->exec('ALTER TABLE plan_activities ADD KEY idx_prog (program, week_no, day_no)');
    }
    if (!$has('plan_weeks', 'program')) {
        db()->exec("ALTER TABLE plan_weeks ADD COLUMN program VARCHAR(20) NOT NULL DEFAULT 'residencial' FIRST");
        db()->exec('ALTER TABLE plan_weeks DROP PRIMARY KEY, ADD PRIMARY KEY (program, week_no)');
    }
    set_setting('schema_version', '4');
}

/** Registra un movimiento del asesor (baja, reactivación, decisión del punto de control). */
function advisor_event(int $advisorId, array $user, string $kind, string $note): void
{
    ensure_schema();
    q('INSERT INTO advisor_events (advisor_id, user_id, user_name, user_role, kind, note, created_at) VALUES (?,?,?,?,?,?,NOW())',
        [$advisorId, $user['id'], $user['name'], $user['role'], $kind, $note !== '' ? $note : null]);
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

/**
 * Protección de los formularios del asesor sin depender de la sesión del servidor:
 * la firma sale de su enlace personal, así que no caduca aunque deje la página abierta horas en el celular.
 */
function adv_csrf(string $t): string
{
    return hash_hmac('sha256', 'hoy|' . $t, (string)cfg('app_secret', 'ariadna'));
}

function adv_csrf_field(string $t): string
{
    return '<input type="hidden" name="csrf" value="' . h(adv_csrf($t)) . '">';
}

function adv_csrf_check(string $t): void
{
    $v = $_POST['csrf'] ?? '';
    if (!is_string($v) || $t === '' || !hash_equals(adv_csrf($t), $v)) {
        http_response_code(400);
        exit('No pudimos guardar. Regresa, recarga la página y vuelve a intentarlo.');
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
    $b = prog_base_url(advisor_program($a));
    return ($b !== '' ? $b . '/' : base_url()) . 'hoy.php?t=' . $a['token'];
}

const WA_WELCOME_DEFAULTS = [
    'residencial' => "Hola {nombre}, soy Ariadna, tu Team Leader del curso Fast Track Residencial en {oficina}. Te doy la bienvenida.\n\nEste es tu enlace personal (no lo compartas):\n{enlace}\n\n1. Ábrelo en tu celular.\n2. Agrégalo a tu pantalla de inicio.\n3. Cada día te digo qué te toca y registras tu avance en menos de 2 minutos.\n\nSigue el hilo.",
    'industrial'  => "Hola {nombre}, soy Edalo, tu Team Leader del curso Fast Track Industrial en {oficina}. Te doy la bienvenida.\n\nEste es tu enlace personal (no lo compartas):\n{enlace}\n\n1. Ábrelo en tu celular.\n2. Agrégalo a tu pantalla de inicio.\n3. Cada día te digo qué te toca, empezando por tus 10 llamadas, y registras tu avance en menos de 2 minutos.\n\nConstruye con método.",
];

/** Texto de bienvenida por WhatsApp del programa (editable en Ajustes). */
function wa_welcome_template(string $prog): string
{
    $t = trim((string)setting('wa_welcome_' . $prog));
    return $t !== '' ? $t : (WA_WELCOME_DEFAULTS[$prog] ?? WA_WELCOME_DEFAULTS['residencial']);
}

function wa_link(array $a, array $office): string
{
    $tpl = wa_welcome_template(advisor_program($a));
    if (strpos($tpl, '{enlace}') === false) $tpl .= "\n\n{enlace}";   // el enlace nunca puede faltar
    $msg = strtr($tpl, ['{nombre}' => first_name($a['name']), '{oficina}' => $office['name'], '{enlace}' => advisor_link($a)]);
    return 'https://wa.me/' . wa_phone($a['phone']) . '?text=' . rawurlencode($msg);
}

// La base de datos se pone al día sola (columnas y tablas nuevas) la primera vez que se usa una versión nueva.
if (!empty($GLOBALS['CONFIG']) && basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'install.php') {
    try { ensure_schema(); } catch (Throwable $e) { /* la instalación aún no termina */ }
}
