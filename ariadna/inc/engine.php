<?php
/**
 * ARIADNA · Motor del curso Fast Track Residencial
 * Calcula en qué semana y día va cada asesor, qué le toca, sus sesiones en la
 * oficina, su rutina de llamadas, su avance, su semáforo y su evaluación.
 */
declare(strict_types=1);

const PROGRAM_WEEKS = 5;
const PROGRAM_DAYS  = 35;
const PLAN_DAYS_PER_WEEK = 5;

const RESULT_FIELDS = [
    'contactos'   => 'Contactos',
    'tratos'      => 'Tratos directos',
    'analizadas'  => 'Propiedades analizadas',
    'promovidas'  => 'Propiedades promovidas',
    'citas'       => 'Citas agendadas',
    'captaciones' => 'Captaciones',
];

const RESULT_HINTS = [
    'contactos'   => 'Personas con las que hablaste hoy. Cada llamada cuenta como contacto.',
    'tratos'      => 'Propietarios que venden por trato directo que contactaste',
    'analizadas'  => 'Te pido que me cuentes cuáles',
    'promovidas'  => 'Propiedades de la oficina que publicaste hoy',
    'citas'       => 'Citas que dejaste agendadas hoy',
    'captaciones' => 'Propiedades captadas con autorización del propietario',
];

const KIND_LABELS = [
    'check'   => 'Normal',
    'foto'    => 'Con foto',
    'agenda'  => 'Agenda de la semana',
    'metas'   => 'Metas de la semana',
    'video'   => 'Video del curso',
    'oficina' => 'Sesión en tu oficina',
    'tl'      => 'Cita con tu Team Leader',
    'repaso'  => 'Repaso semanal',
];

// ---------------------------------------------------------------------------
// Plan (base de datos, editable por el administrador)
// ---------------------------------------------------------------------------
function plan_weeks(): array
{
    static $w = null;
    if ($w === null) {
        $w = [];
        foreach (all('SELECT * FROM plan_weeks ORDER BY week_no') as $r) $w[(int)$r['week_no']] = $r;
    }
    return $w;
}

function plan_activities(): array
{
    static $acts = null;
    if ($acts === null) {
        $acts = [];
        foreach (all('SELECT * FROM plan_activities WHERE active = 1 ORDER BY week_no, day_no, sort, id') as $r) {
            $acts[(int)$r['week_no']][(int)$r['day_no']][] = $r;
        }
    }
    return $acts;
}

function plan_activity_by_id(int $id): ?array
{
    foreach (plan_activities() as $days) foreach ($days as $list) foreach ($list as $a) if ((int)$a['id'] === $id) return $a;
    return null;
}

function office_activities(): array
{
    $out = [];
    foreach (plan_activities() as $w => $days) foreach ($days as $d => $list) foreach ($list as $a) if ($a['kind'] === 'oficina') $out[] = $a;
    return $out;
}

function day_activities(int $week, int $day): array
{
    return plan_activities()[$week][$day] ?? [];
}

function routine_items(): array
{
    $r = json_decode((string)setting('routine_items'), true);
    return is_array($r) ? $r : [];
}

/** Mínimo del programa de una semana para un campo (los «meta_» de plan_weeks). */
function week_min(int $week, string $field): int
{
    return (int)(plan_weeks()[$week]['meta_' . $field] ?? 0);
}

function plan_meta_total(string $field): int
{
    $t = 0;
    foreach (plan_weeks() as $w) $t += (int)($w['meta_' . $field] ?? 0);
    return $t;
}

function help_links(?string $raw): array
{
    $out = [];
    foreach (preg_split('/\R/', (string)$raw) as $line) {
        $p = array_map('trim', explode('|', $line, 2));
        if (count($p) === 2 && preg_match('#^https?://#i', $p[1])) $out[] = ['title' => $p[0], 'url' => $p[1]];
    }
    return $out;
}

const OH_MODES = [
    'open_house' => ['label' => 'Open House', 'count' => 'Asistentes reales', 'plural' => 'asistentes'],
    'recorrido'  => ['label' => 'Recorrido en vivo', 'count' => 'Interesados que dejaron sus datos', 'plural' => 'interesados'],
    'jornada'    => ['label' => 'Jornada de opinión de valor', 'count' => 'Propietarios interesados que dejaron sus datos', 'plural' => 'propietarios interesados'],
];

function oh_mode(?array $office): string
{
    $m = $office['oh_mode'] ?? 'open_house';
    return isset(OH_MODES[$m]) ? $m : 'open_house';
}

/** «Open House», «Recorrido en vivo» o «Jornada de opinión de valor», según elige la oficina. */
function oh_label(?array $office): string
{
    return OH_MODES[oh_mode($office)]['label'];
}

function oh_count_label(?array $office): string
{
    return OH_MODES[oh_mode($office)]['count'];
}

/**
 * Textos del plan: {OH} = la actividad de la oficina; {ASISTENTES} = asistentes / interesados / propietarios interesados.
 * {jornada}…{/jornada} solo se ve en la Jornada de opinión de valor; {evento}…{/evento} solo en Open House y Recorrido.
 */
function oh_text(?string $s, ?array $office): string
{
    $s = (string)$s;
    $j = oh_mode($office) === 'jornada';
    if (strpos($s, '{') !== false) {
        $s = preg_replace('~\{jornada\}(.*?)\{/jornada\}~s', $j ? '$1' : '', $s);
        $s = preg_replace('~\{evento\}(.*?)\{/evento\}~s', $j ? '' : '$1', $s);
    }
    return str_replace(['{OH}', '{ASISTENTES}'], [oh_label($office), OH_MODES[oh_mode($office)]['plural']], $s);
}

/** Convierte un enlace de Bunny/YouTube/Vimeo a su versión para insertar en la página. */
function video_embed_url(?string $url): ?string
{
    $url = trim((string)$url);
    if ($url === '') return null;
    if (preg_match('#mediadelivery\.net/(?:play|embed)/(\d+)/([a-f0-9-]{36})#i', $url, $m)) {
        return 'https://player.mediadelivery.net/embed/' . $m[1] . '/' . $m[2] . '?autoplay=false&preload=true&responsive=true';
    }
    if (preg_match('#(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/))([A-Za-z0-9_-]{6,})#', $url, $m)) {
        return 'https://www.youtube-nocookie.com/embed/' . $m[1];
    }
    if (preg_match('#vimeo\.com/(?:video/)?(\d+)#', $url, $m)) return 'https://player.vimeo.com/video/' . $m[1];
    return null;
}

// ---------------------------------------------------------------------------
// Fechas
// ---------------------------------------------------------------------------
function office_tz(array $office): DateTimeZone
{
    try { return new DateTimeZone($office['timezone'] ?: 'America/Cancun'); }
    catch (Throwable $e) { return new DateTimeZone('America/Cancun'); }
}

function office_now(array $office): DateTimeImmutable
{
    return new DateTimeImmutable('now', office_tz($office));
}

function office_today(array $office): string
{
    return office_now($office)->format('Y-m-d');
}

function add_days(string $ymd, int $n): string
{
    return (new DateTimeImmutable($ymd))->modify(($n >= 0 ? '+' : '') . $n . ' day')->format('Y-m-d');
}

function days_between(string $from, string $to): int
{
    return (int)(new DateTimeImmutable($from))->diff(new DateTimeImmutable($to))->format('%r%a');
}

function iso_weekday(string $ymd): int
{
    return (int)(new DateTimeImmutable($ymd))->format('N');
}

function parse_workdays(?string $s): array
{
    $d = array_values(array_unique(array_filter(array_map('intval', explode(',', (string)$s)), fn($x) => $x >= 1 && $x <= 7)));
    sort($d);
    return $d;
}

function advisor_workdays(array $adv, array $office): array
{
    $o = parse_workdays($adv['workdays_override'] ?? '');
    return $o ?: (parse_workdays($office['workdays']) ?: [1, 2, 3, 4, 5]);
}

function workdays_label(array $days): string
{
    return implode(', ', array_map(fn($d) => DIAS[$d], $days));
}

function time_label(?string $t): string
{
    if (!$t) return '';
    return substr($t, 0, 5);
}

// ---------------------------------------------------------------------------
// Calendario personal del asesor
// ---------------------------------------------------------------------------
/**
 * Para cada uno de los 35 días: type = plan | catchup | training | rest,
 * week, day_no (día hábil), days (días del plan que se muestran),
 * arranque (1..N si ese día cuenta para las llamadas de arranque).
 */
function build_schedule(array $adv, array $office): array
{
    $wd = advisor_workdays($adv, $office);
    $training = $adv['training_date'] ?: null;
    $out = [];
    for ($w = 1; $w <= PROGRAM_WEEKS; $w++) {
        $dates = [];
        for ($i = 0; $i < 7; $i++) $dates[] = add_days($adv['start_date'], ($w - 1) * 7 + $i);
        $workDates = array_values(array_filter($dates, fn($d) => in_array(iso_weekday($d), $wd, true) && $d !== $training));
        $W = count($workDates);
        $n = 0;
        foreach ($dates as $d) {
            $base = ['date' => $d, 'week' => $w, 'day_no' => 0, 'days' => [], 'arranque' => null];
            if ($d === $training) {
                $out[$d] = ['type' => 'training'] + $base;
            } elseif (!in_array(iso_weekday($d), $wd, true)) {
                $out[$d] = ['type' => 'rest'] + $base;
            } else {
                $n++;
                if ($n <= PLAN_DAYS_PER_WEEK) {
                    $days = ($n === $W && $W < PLAN_DAYS_PER_WEEK) ? range($n, PLAN_DAYS_PER_WEEK) : [$n];
                    $out[$d] = ['type' => 'plan', 'day_no' => $n, 'days' => $days] + $base;
                } else {
                    $out[$d] = ['type' => 'catchup', 'day_no' => $n] + $base;
                }
            }
        }
    }
    // Rutina de arranque: N llamadas diarias durante los primeros N días hábiles con rutina.
    $limit = max(0, (int)setting('arranque_dias'));
    $k = 0;
    foreach ($out as $d => $ctx) {
        if ($k >= $limit) break;
        if (routine_applies($ctx)) { $k++; $out[$d]['arranque'] = $k; }
    }
    return $out;
}

function ctx_label(array $ctx): string
{
    switch ($ctx['type']) {
        case 'plan':     return count($ctx['days']) > 1 ? 'Días ' . $ctx['days'][0] . ' a ' . end($ctx['days']) : 'Día ' . $ctx['day_no'];
        case 'catchup':  return 'Día ' . $ctx['day_no'] . ' · Ponte al corriente';
        case 'training': return 'Capacitación interna';
        default:         return 'Día de descanso';
    }
}

function routine_applies(array $ctx): bool
{
    if ($ctx['type'] === 'catchup') return true;
    if ($ctx['type'] !== 'plan') return false;
    $start = (int)(plan_weeks()[$ctx['week']]['routine_start_day'] ?? 1);
    return $ctx['days'][0] >= $start;
}

// ---------------------------------------------------------------------------
// Sesiones en la oficina (la oficina pone fecha y hora a cada tema)
// ---------------------------------------------------------------------------
function office_session_rows(int $officeId): array
{
    static $cache = [];
    if (!isset($cache[$officeId])) {
        $cache[$officeId] = all('SELECT * FROM office_sessions WHERE office_id = ? ORDER BY sdate, stime', [$officeId]);
    }
    return $cache[$officeId];
}

/** Primera fecha programada de un tema a partir de una fecha (incluye las que se repiten cada semana). */
function next_session(int $officeId, int $activityId, string $from): ?array
{
    $best = null;
    foreach (office_session_rows($officeId) as $r) {
        if ((int)$r['activity_id'] !== $activityId) continue;
        $date = null;
        if ($r['sdate'] >= $from) $date = $r['sdate'];
        elseif ((int)$r['repeat_weekly'] === 1) {
            $diff = (iso_weekday($r['sdate']) - iso_weekday($from) + 7) % 7;
            $date = add_days($from, $diff);
        }
        if ($date && (!$best || $date < $best['date'] || ($date === $best['date'] && (string)$r['stime'] < (string)$best['stime']))) {
            $best = $r + ['date' => $date];
        }
    }
    return $best;
}

/** Para un asesor: cada tema de oficina → su sesión asignada (o null si la oficina no la ha programado). */
function advisor_sessions(array $adv, array $office): array
{
    $map = [];
    foreach (office_activities() as $a) {
        $from = max($adv['start_date'], add_days($adv['start_date'], ((int)$a['week_no'] - 1) * 7));
        $map[(int)$a['id']] = next_session((int)$office['id'], (int)$a['id'], $from);
    }
    return $map;
}

// ---------------------------------------------------------------------------
// Qué le toca a un asesor en una fecha
// ---------------------------------------------------------------------------
function act_item(array $a, ?array $office, ?string $origin = null): array
{
    return [
        'key' => 'A' . $a['id'], 'id' => (int)$a['id'], 'title' => oh_text($a['title'], $office), 'guide' => oh_text($a['guide'], $office),
        'minutes' => $a['minutes'] !== null ? (int)$a['minutes'] : null,
        'kind' => $a['kind'], 'week' => (int)$a['week_no'], 'day' => (int)$a['day_no'],
        'origin' => $origin, 'routine' => false, 'video_url' => $a['video_url'] ?? null,
        'help' => help_links($a['help_links'] ?? ''), 'session' => null, 'pending_session' => false,
    ];
}

/**
 * Lista de actividades de una fecha.
 * $o: office, sessions (mapa de advisor_sessions), done_elsewhere (claves A# hechas en otros días),
 *     goal_contactos (meta diaria del asesor), preview (bool).
 */
function day_items(array $ctx, array $o = []): array
{
    $office = $o['office'] ?? null;
    $sessions = $o['sessions'] ?? [];
    $preview = !empty($o['preview']);
    $date = $ctx['date'] ?? null;
    $items = [];

    if ($ctx['type'] === 'training') {
        $items[] = ['key' => 'CI', 'id' => 0, 'title' => 'Capacitación interna: políticas y documentos de la oficina',
            'guide' => 'Hoy tu oficina te presenta sus políticas y documentos internos. Escribe aquí lo más importante que debes recordar.',
            'minutes' => null, 'kind' => 'check', 'week' => $ctx['week'], 'day' => 0, 'origin' => null, 'routine' => false,
            'video_url' => null, 'help' => [], 'session' => null, 'pending_session' => false];
    }

    $acts = [];
    if ($ctx['type'] === 'plan') {
        foreach ($ctx['days'] as $d) {
            foreach (day_activities($ctx['week'], $d) as $a) {
                $origin = count($ctx['days']) > 1 && $d !== $ctx['days'][0] ? 'Del día ' . $d : null;
                if ($a['kind'] === 'oficina' && !$preview) {
                    if (!empty($sessions[(int)$a['id']])) continue;           // aparece el día que la oficina la programó
                    $it = act_item($a, $office, $origin);
                    $it['pending_session'] = true;
                    $acts[] = $it;
                    continue;
                }
                $acts[] = act_item($a, $office, $origin);
            }
        }
    } elseif ($ctx['type'] === 'catchup') {
        $doneElsewhere = $o['done_elsewhere'] ?? [];
        for ($d = 1; $d <= PLAN_DAYS_PER_WEEK; $d++) {
            foreach (day_activities($ctx['week'], $d) as $a) {
                if (in_array('A' . $a['id'], $doneElsewhere, true)) continue;
                if ($a['kind'] === 'oficina' && !empty($sessions[(int)$a['id']])) continue;
                if (in_array($a['kind'], ['agenda', 'metas'], true)) continue;
                $acts[] = act_item($a, $office, 'Pendiente del día ' . $d);
            }
        }
    }

    // Sesiones en la oficina programadas para esta fecha
    if ($date && !$preview) {
        foreach ($sessions as $aid => $occ) {
            if (!$occ || $occ['date'] !== $date) continue;
            $a = plan_activity_by_id((int)$aid);
            if (!$a) continue;
            $it = act_item($a, $office, null);
            $it['session'] = $occ;
            $acts[] = $it;
        }
    }

    foreach ($acts as $a) if (in_array($a['kind'], ['agenda', 'metas'], true)) $items[] = $a;
    if (routine_applies($ctx)) {
        foreach (routine_items() as $r) {
            $it = ['key' => $r['key'], 'id' => 0, 'title' => $r['title'], 'guide' => $r['guide'], 'minutes' => null,
                'kind' => 'check', 'week' => $ctx['week'], 'day' => 0, 'origin' => 'RUTINA DIARIA', 'routine' => true,
                'video_url' => null, 'help' => [], 'session' => null, 'pending_session' => false];
            if ($r['key'] === 'R1') {
                if (!empty($ctx['arranque'])) {
                    $it['title'] = str_replace('{n}', (string)setting('arranque_llamadas'), (string)setting('r1_arranque_title'));
                    $it['origin'] = 'RUTINA DE ARRANQUE · ' . $ctx['arranque'] . ' DE ' . setting('arranque_dias');
                } else {
                    $g = $o['goal_contactos'] ?? (int)setting('goal_min_contactos_dia');
                    $it['title'] = str_replace('{meta}', (string)$g, (string)setting('r1_title'));
                }
            }
            $items[] = $it;
        }
    }
    // Si hoy hay sesión en la oficina, la cita con el TL va después (es la misma visita).
    $hasSession = (bool)array_filter($acts, fn($a) => !empty($a['session']));
    $rest = array_values(array_filter($acts, fn($a) => !in_array($a['kind'], ['agenda', 'metas'], true)));
    if ($hasSession) $rest = array_merge(array_filter($rest, fn($a) => $a['kind'] !== 'tl'), array_filter($rest, fn($a) => $a['kind'] === 'tl'));
    foreach ($rest as $a) $items[] = $a;
    return $items;
}

/** Actividades de una semana con su tiempo, para «Agenda tus horarios». */
function week_time_list(int $week, array $sessions = [], ?array $office = null): array
{
    $list = [];
    for ($d = 1; $d <= PLAN_DAYS_PER_WEEK; $d++) {
        foreach (day_activities($week, $d) as $a) {
            if (in_array($a['kind'], ['agenda', 'metas'], true)) continue;
            $when = null;
            if ($a['kind'] === 'oficina') {
                $occ = $sessions[(int)$a['id']] ?? null;
                $when = $occ ? fecha_corta($occ['date']) . ($occ['stime'] ? ' · ' . time_label($occ['stime']) : '') : 'tu oficina te dirá la fecha';
            } elseif ($a['kind'] === 'tl') {
                $when = 'acuerda la hora con tu Team Leader';
            }
            $list[] = ['day' => $d, 'title' => oh_text($a['title'], $office), 'kind' => $a['kind'],
                'minutes' => $a['minutes'] !== null ? (int)$a['minutes'] : null, 'when' => $when];
        }
    }
    return $list;
}

function minutes_label(?int $m): string
{
    if ($m === null || $m <= 0) return '';
    if ($m < 60) return $m . ' min';
    $h = intdiv($m, 60); $r = $m % 60;
    return $h . ' h' . ($r ? ' ' . $r . ' min' : '');
}

// ---------------------------------------------------------------------------
// Registros y metas
// ---------------------------------------------------------------------------
function advisor_logs(int $advisorId): array
{
    $logs = [];
    foreach (all('SELECT * FROM daily_logs WHERE advisor_id = ? ORDER BY log_date', [$advisorId]) as $l) {
        $l['items'] = [];
        $logs[$l['log_date']] = $l;
    }
    if ($logs) {
        $ids = array_column($logs, 'id');
        $byId = array_combine($ids, array_keys($logs));
        $in = implode(',', array_fill(0, count($ids), '?'));
        foreach (all("SELECT * FROM log_items WHERE log_id IN ($in)", $ids) as $it) {
            $logs[$byId[$it['log_id']]]['items'][$it['item_key']] = $it;
        }
    }
    return $logs;
}

function log_has_activity(array $log): bool
{
    foreach (RESULT_FIELDS as $f => $_) if ((int)$log[$f] > 0) return true;
    foreach ($log['items'] as $it) if ((int)$it['done'] === 1 || trim((string)$it['result_text']) !== '') return true;
    return trim((string)($log['note'] ?? '')) !== '';
}

function done_keys(array $logs, ?string $exceptDate = null): array
{
    $keys = [];
    foreach ($logs as $date => $l) {
        if ($date === $exceptDate) continue;
        foreach ($l['items'] as $k => $it) if ((int)$it['done'] === 1 && $k[0] === 'A') $keys[$k] = true;
    }
    return array_keys($keys);
}

function advisor_goals(int $advisorId): array
{
    $g = [];
    foreach (all('SELECT * FROM advisor_goals WHERE advisor_id = ?', [$advisorId]) as $r) $g[(int)$r['week_no']] = $r;
    return $g;
}

/** Meta vigente (la del asesor o, si no la ha puesto, el mínimo del programa). */
function goal_for(array $goals, int $week, string $field): int
{
    if (isset($goals[$week])) return (int)$goals[$week][$field];
    return $field === 'contactos' ? (int)setting('goal_min_contactos_dia') : week_min($week, $field);
}

function goal_minimum(int $week, string $field): int
{
    return $field === 'contactos' ? (int)setting('goal_min_contactos_dia') : week_min($week, $field);
}

/** Metas por debajo del mínimo: [[campo, etiqueta, meta, mínimo], …] */
function goals_below_min(array $row, int $week): array
{
    $low = [];
    foreach (RESULT_FIELDS as $f => $label) {
        $min = goal_minimum($week, $f);
        if ((int)$row[$f] < $min) $low[] = [$f, $f === 'contactos' ? 'Contactos al día' : $label, (int)$row[$f], $min];
    }
    return $low;
}

// ---------------------------------------------------------------------------
// Avance, semáforo y evaluación
// ---------------------------------------------------------------------------
function advisor_stats(array $adv, array $office, ?string $today = null): array
{
    $today = $today ?? office_today($office);
    $schedule = build_schedule($adv, $office);
    $logs = advisor_logs((int)$adv['id']);
    $sessions = advisor_sessions($adv, $office);
    $goals = advisor_goals((int)$adv['id']);
    $elapsed = days_between($adv['start_date'], $today);
    $started = $elapsed >= 0;
    $finished = $elapsed >= PROGRAM_DAYS;
    $week = $started ? min(PROGRAM_WEEKS, intdiv(max(0, $elapsed), 7) + 1) : 1;
    $closed = $started ? min(PROGRAM_WEEKS, intdiv($elapsed, 7)) : 0;
    $doneAll = done_keys($logs);

    $totals = array_fill_keys(array_keys(RESULT_FIELDS), 0);
    $weekTotals = [];
    for ($w = 1; $w <= PROGRAM_WEEKS; $w++) $weekTotals[$w] = array_fill_keys(array_keys(RESULT_FIELDS), 0);
    $weekHasActivity = array_fill(1, PROGRAM_WEEKS, false);
    foreach ($logs as $date => $l) {
        $w = $schedule[$date]['week'] ?? null;
        foreach (RESULT_FIELDS as $f => $_) {
            $totals[$f] += (int)$l[$f];
            if ($w) $weekTotals[$w][$f] += (int)$l[$f];
        }
        if ($w && log_has_activity($l)) $weekHasActivity[$w] = true;
    }

    // Asignadas vs. cumplidas
    $assignedA = []; $assignedOther = 0; $doneOther = 0;
    $weekA = array_fill(1, PROGRAM_WEEKS, []); $weekOther = array_fill(1, PROGRAM_WEEKS, 0); $weekOtherDone = array_fill(1, PROGRAM_WEEKS, 0);
    $workdaysElapsed = 0; $workdaysLogged = 0;
    $tlFirst = [];   // cita con el TL (clave A#) → fecha en que le tocaba por primera vez
    $vidFirst = [];  // video (clave A#) → fecha en que le tocaba por primera vez
    foreach ($schedule as $date => $ctx) {
        $w = $ctx['week'];
        $items = day_items($ctx, ['office' => $office, 'sessions' => $sessions, 'done_elsewhere' => $ctx['type'] === 'catchup' ? done_keys($logs, $date) : []]);
        foreach ($items as $it) {
            if ($it['key'][0] === 'A') {
                if ($it['kind'] === 'tl' && !isset($tlFirst[$it['key']])) $tlFirst[$it['key']] = ['date' => $date, 'week' => $w, 'title' => $it['title']];
                if ($it['kind'] === 'video' && !isset($vidFirst[$it['id']])) $vidFirst[$it['id']] = ['date' => $date, 'week' => $w];
                if ($date <= $today) $assignedA[$it['key']] = true;
                $weekA[$w][$it['key']] = true;
            } else {
                $isDone = isset($logs[$date]['items'][$it['key']]) && (int)$logs[$date]['items'][$it['key']]['done'] === 1;
                $weekOther[$w]++;
                if ($isDone) $weekOtherDone[$w]++;
                if ($date <= $today) { $assignedOther++; if ($isDone) $doneOther++; }
            }
        }
        if ($date <= $today && $ctx['type'] !== 'rest' && ($date < $today || isset($logs[$date]))) {
            $workdaysElapsed++;
            if (isset($logs[$date])) $workdaysLogged++;
        }
    }
    $assigned = count($assignedA) + $assignedOther;
    $done = count(array_intersect(array_keys($assignedA), $doneAll)) + $doneOther;
    $weekAssigned = []; $weekDone = [];
    for ($w = 1; $w <= PROGRAM_WEEKS; $w++) {
        $weekAssigned[$w] = count($weekA[$w]) + $weekOther[$w];
        $weekDone[$w] = count(array_intersect(array_keys($weekA[$w]), $doneAll)) + $weekOtherDone[$w];
    }

    // Citas con el TL: cumplidas y las que ya pasaron sin cumplir
    $tl = ['total' => count($tlFirst), 'due' => 0, 'done' => 0, 'missed' => [], 'week_due' => array_fill(1, PROGRAM_WEEKS, 0), 'week_done' => array_fill(1, PROGRAM_WEEKS, 0)];
    $tlSince = (string)setting('tl_since');
    foreach ($tlFirst as $key => $c) {
        $isDone = in_array($key, $doneAll, true);
        if ($c['date'] < $tlSince && !$isDone) { $tl['total']--; continue; }   // eran de antes de que existieran las citas
        if ($c['date'] >= $today && !$isDone) continue;      // todavía no le toca
        $tl['due']++; $tl['week_due'][$c['week']]++;
        if ($isDone) { $tl['done']++; $tl['week_done'][$c['week']]++; }
        else $tl['missed'][] = $c;
    }

    // Retos del video
    $rows = advisor_retos((int)$adv['id']);
    $retos = ['total' => 0, 'due' => 0, 'done' => 0, 'points' => 0.0, 'list' => []];
    foreach (reto_videos() as $vid => $a) {
        $when = $vidFirst[$vid] ?? ['date' => null, 'week' => (int)$a['week_no']];
        $row = $rows[$vid] ?? null;
        $isDue = $row || ($when['date'] && $when['date'] <= $today);
        $retos['total']++;
        if ($isDue) $retos['due']++;
        if ($row) { $retos['done']++; $retos['points'] += reto_points($row); }
        $retos['list'][] = ['activity' => $a, 'label' => reto_label($a['title']), 'date' => $when['date'], 'week' => $when['week'], 'due' => $isDue, 'row' => $row];
    }

    // Días hábiles seguidos sin registrar (sin contar hoy)
    $missed = 0;
    if ($started) {
        for ($d = add_days($today, -1); $d >= $adv['start_date']; $d = add_days($d, -1)) {
            $ctx = $schedule[$d] ?? null;
            if (!$ctx || $ctx['type'] === 'rest') continue;
            if (isset($logs[$d])) break;
            $missed++;
        }
    }

    $s = [
        'today' => $today, 'schedule' => $schedule, 'logs' => $logs, 'sessions' => $sessions, 'goals' => $goals,
        'started' => $started, 'finished' => $finished, 'elapsed' => $elapsed,
        'week' => $week, 'closed_weeks' => $closed, 'ctx' => $schedule[$today] ?? null,
        'totals' => $totals, 'week_totals' => $weekTotals, 'week_has_activity' => $weekHasActivity,
        'assigned' => $assigned, 'done' => $done, 'pct' => $assigned ? (int)round(min(1, $done / $assigned) * 100) : 0,
        'week_assigned' => $weekAssigned, 'week_done' => $weekDone,
        'missed' => $missed, 'last_log' => $logs ? max(array_keys($logs)) : null,
        'workdays_elapsed' => $workdaysElapsed, 'workdays_logged' => $workdaysLogged,
        'low_goals' => [], 'tl' => $tl, 'retos' => $retos,
    ];
    foreach ($goals as $w => $g) if ((int)$g['low_accepted'] === 1) $s['low_goals'][$w] = goals_below_min($g, $w);
    $s['evaluation'] = evaluation($adv, $s, $office);
    $s['status'] = compute_status($adv, $s);
    return $s;
}

function cum_totals(array $stats, int $uptoWeek): array
{
    $t = array_fill_keys(array_keys(RESULT_FIELDS), 0);
    for ($w = 1; $w <= $uptoWeek; $w++) foreach ($t as $f => $_) $t[$f] += $stats['week_totals'][$w][$f];
    return $t;
}

/** Semáforo con sugerencia de Ariadna para la oficina. */
function compute_status(array $adv, array $s): array
{
    $name = first_name($adv['name']);
    if (!$s['started']) return ['color' => 'verde', 'reason' => 'Aún no inicia', 'action' => '', 'rule' => null];

    for ($w = min(3, $s['closed_weeks']); $w >= 1; $w--) {
        if ($w === 1 && setting('red_s1_enabled') === '1') {
            if (!$s['week_has_activity'][1]) {
                return ['color' => 'rojo', 'rule' => 1, 'reason' => 'Cerró su Semana 1 sin actividad registrada',
                    'action' => "Reúnete hoy con $name: cerró su Semana 1 sin registrar actividad."];
            }
            break;
        }
        if ($w === 2) {
            $c = cum_totals($s, 2);
            $minC = (int)setting('s2_min_contactos'); $minP = (int)setting('s2_min_promovidas');
            if ($c['contactos'] < $minC || $c['promovidas'] < $minP) {
                return ['color' => 'rojo', 'rule' => 2,
                    'reason' => "No cumplió los mínimos de la Semana 2 ({$c['contactos']}/$minC contactos, {$c['promovidas']}/$minP promovidas)",
                    'action' => "Revisa hoy con $name sus mínimos: lleva {$c['contactos']} de $minC contactos y {$c['promovidas']} de $minP propiedades promovidas."];
            }
            break;
        }
        if ($w === 3 && setting('s3_red_enabled') === '1') {
            $c = cum_totals($s, 3);
            if ($c['citas'] === 0 && $c['captaciones'] === 0) {
                return ['color' => 'rojo', 'rule' => 3, 'reason' => 'Cerró su Semana 3 sin citas ni captaciones',
                    'action' => "Punto de control: $name no tiene citas ni captaciones. Habla con él o ella y decide si continúa."];
            }
            break;
        }
    }
    if (!$s['finished'] && $s['missed'] >= (int)setting('yellow_days')) {
        return ['color' => 'amarillo', 'rule' => null, 'reason' => $s['missed'] . ' días hábiles sin registrar',
            'action' => "Habla hoy con $name: {$s['missed']} días sin registrar."];
    }
    $tlMiss = $s['finished'] ? [] : array_values(array_filter($s['tl']['missed'], fn($c) => $c['week'] >= $s['week'] - 1));
    if (count($tlMiss) >= 2) {
        return ['color' => 'amarillo', 'rule' => null, 'reason' => 'Faltó a ' . count($tlMiss) . ' citas con su Team Leader',
            'action' => "Llama hoy a $name: faltó a " . count($tlMiss) . ' citas con su Team Leader en la oficina. Que venga esta semana.'];
    }
    if ($tlMiss) {
        return ['color' => 'verde', 'rule' => null, 'reason' => 'Faltó a una cita con su Team Leader',
            'action' => "Pregúntale a $name por su cita con su Team Leader («" . $tlMiss[0]['title'] . "»): no la registró como hecha. Agéndala de nuevo."];
    }
    if (!$s['finished']) {
        foreach ($s['retos']['list'] as $r) {
            if ($r['row'] && (int)$r['row']['ok_count'] < 4 && (int)$r['row']['attempt'] < 2 && $r['week'] >= $s['week'] - 1) {
                return ['color' => 'verde', 'rule' => null, 'reason' => 'Le toca repasar su Reto del ' . $r['label'],
                    'action' => "En su cierre de semana, vuelve a hacerle a $name las preguntas de su Reto del " . $r['label'] . ' que no supo.'];
            }
            if (!$r['row'] && $r['date'] && $r['date'] < $s['today'] && $r['week'] >= $s['week'] - 1) {
                return ['color' => 'verde', 'rule' => null, 'reason' => 'Falta su Reto del ' . $r['label'],
                    'action' => "Aplícale a $name su Reto del " . $r['label'] . ': ya vio el video y aún no tiene sus 5 preguntas.'];
            }
        }
    }
    if (!empty($s['low_goals'][$s['week']])) {
        return ['color' => 'verde', 'rule' => null, 'reason' => 'Se puso metas por debajo del mínimo',
            'action' => "Revisa con $name sus metas de esta semana: están por debajo del mínimo."];
    }
    return ['color' => 'verde', 'rule' => null, 'reason' => $s['finished'] ? 'Terminó el curso' : 'Registra y avanza', 'action' => ''];
}

/** Cuadro de Evaluación del Asesor (100 puntos), calculado con lo que registra el asesor. */
function evaluation(array $adv, array $s, ?array $office = null): array
{
    $t = $s['totals'];
    $prop = function (int $res, int $meta, float $max): float {
        if ($meta <= 0) return $res > 0 ? $max : 0.0;
        return round(min(1, $res / $meta) * $max, 1);
    };
    $m = fn($f) => plan_meta_total($f);
    $rows = [];
    $rows[] = ['group' => 'Actividad del mes', 'label' => 'Contactos a círculo cercano', 'meta' => $m('contactos'), 'result' => $t['contactos'], 'max' => 10, 'pts' => $prop($t['contactos'], $m('contactos'), 10)];
    $rows[] = ['group' => 'Actividad del mes', 'label' => 'Tratos directos contactados', 'meta' => $m('tratos'), 'result' => $t['tratos'], 'max' => 10, 'pts' => $prop($t['tratos'], $m('tratos'), 10)];
    $rows[] = ['group' => 'Actividad del mes', 'label' => 'Propiedades analizadas', 'meta' => $m('analizadas'), 'result' => $t['analizadas'], 'max' => 5, 'pts' => $prop($t['analizadas'], $m('analizadas'), 5)];
    $rows[] = ['group' => 'Actividad del mes', 'label' => 'Citas agendadas', 'meta' => $m('citas'), 'result' => $t['citas'], 'max' => 5, 'pts' => $prop($t['citas'], $m('citas'), 5)];
    $pctReg = $s['workdays_elapsed'] ? (int)round($s['workdays_logged'] / $s['workdays_elapsed'] * 100) : 0;
    $regOk = $s['workdays_elapsed'] > 0 && $pctReg >= (int)setting('registro_min_pct');
    $rows[] = ['group' => 'Actividad del mes', 'label' => 'Registro ordenado en Ariadna', 'meta' => setting('registro_min_pct') . '% de días', 'result' => $pctReg . '%', 'max' => 10, 'pts' => $regOk ? 10.0 : 0.0];

    $cap = $t['captaciones'] >= 1;
    $q = (int)$adv['cap_precio'] + (int)$adv['cap_docs'] + (int)$adv['cap_disp'];
    $qLabel = !$cap ? '—' : ($q === 3 ? 'Alta' : ($q === 2 ? 'Media' : 'Baja'));
    $rows[] = ['group' => 'Captación', 'label' => 'Logró captar al menos 1 propiedad', 'meta' => 'Sí', 'result' => $cap ? 'Sí' : 'No', 'max' => 10, 'pts' => $cap ? 10.0 : 0.0];
    $rows[] = ['group' => 'Captación', 'label' => 'Calidad de la captación (precio, documentos, disponibilidad)', 'meta' => 'Alta', 'result' => $qLabel, 'max' => 10, 'pts' => !$cap ? 0.0 : ($q === 3 ? 10.0 : ($q === 2 ? 5.0 : 0.0))];

    $ohL = oh_label($office);
    $oh = (int)$adv['oh_done'] === 1;
    $att = (int)$adv['oh_attendees'];
    $minAtt = (int)setting('oh_min_attendees');
    $rows[] = ['group' => $ohL, 'label' => "Realizó su $ohL", 'meta' => 'Sí', 'result' => $oh ? 'Sí' : 'No', 'max' => 10, 'pts' => $oh ? 10.0 : 0.0];
    $rows[] = ['group' => $ohL, 'label' => oh_count_label($office) . " (mínimo $minAtt)", 'meta' => $minAtt, 'result' => $oh ? $att : 0, 'max' => 10, 'pts' => $oh ? $prop($att, $minAtt, 10) : 0.0];

    $rt = $s['retos'] ?? ['total' => 0, 'points' => 0.0];
    $rows[] = ['group' => 'Aprendizaje', 'label' => 'Retos del video con su Team Leader (1 punto por video)', 'meta' => $rt['total'] . ' videos', 'result' => rtrim(rtrim(number_format($rt['points'], 1), '0'), '.') . ' de ' . $rt['total'],
        'max' => 10, 'pts' => $rt['total'] ? round(min(1, $rt['points'] / $rt['total']) * 10, 1) : 0.0];

    $renta = (int)$adv['renta_closed'] === 1;
    $rows[] = ['group' => 'Cierre de renta', 'label' => '¿Cerró una renta?', 'meta' => 'Sí', 'result' => $renta ? 'Sí' : 'No', 'max' => 10, 'pts' => $renta ? 10.0 : 0.0];

    $total = (int)round(array_sum(array_column($rows, 'pts')));
    if ($total >= 90)      $level = ['TOP', 'Nivel TOP: listo para avanzar al siguiente programa.'];
    elseif ($total >= 70)  $level = ['Buen inicio', 'Buen inicio: reforzar consistencia y técnica.'];
    elseif ($total >= 50)  $level = ['Incompleto', 'Proceso incompleto: repetir el mes con acompañamiento.'];
    else                   $level = ['Sin actividad', 'No hay actividad: no puede continuar.'];
    return ['rows' => $rows, 'total' => $total, 'level' => $level[0], 'level_text' => $level[1]];
}

/** Punto de control de la Semana 3. */
function checkpoint(array $adv, array $s, ?array $office = null): array
{
    $c = cum_totals($s, 3);
    $oh = (int)$adv['oh_done'] === 1;
    $capOrCita = $c['captaciones'] >= 1 || $c['citas'] >= 1;
    $min = (int)setting('checkpoint_min_score');
    $score = $s['evaluation']['total'];
    return [
        'checks' => [
            ['Realizó su ' . oh_label($office), $oh],
            ['Tiene al menos 1 captación o 1 cita (' . $c['captaciones'] . ' captaciones, ' . $c['citas'] . ' citas)', $capOrCita],
            ["Tiene $min puntos o más en su evaluación ($score puntos)", $score >= $min],
        ],
        'pass' => $oh && $capOrCita && $score >= $min,
        'score' => $score,
    ];
}

// ---------------------------------------------------------------------------
// Capacitación interna
// ---------------------------------------------------------------------------
function find_training_date(array $office, array $adv): ?string
{
    $start = $adv['start_date'];
    $end = add_days($start, PROGRAM_DAYS - 1);
    $wd = advisor_workdays($adv, $office);
    $cands = array_column(all('SELECT tdate FROM office_training_dates WHERE office_id = ? AND tdate > ? AND tdate <= ? ORDER BY tdate',
        [$office['id'], $start, $end]), 'tdate');
    if (!empty($office['training_weekday'])) {
        for ($d = add_days($start, 1); $d <= $end; $d = add_days($d, 1)) {
            if (iso_weekday($d) === (int)$office['training_weekday'] && in_array(iso_weekday($d), $wd, true)) { $cands[] = $d; break; }
        }
    }
    $cands = array_values(array_filter($cands, fn($d) => in_array(iso_weekday($d), $wd, true)));
    sort($cands);
    return $cands[0] ?? null;
}

function reassign_training(array $office): void
{
    $today = office_today($office);
    foreach (all('SELECT * FROM advisors WHERE office_id = ? AND active = 1 AND training_manual = 0', [$office['id']]) as $a) {
        if ($a['training_date'] && $a['training_date'] < $today) continue;
        if (days_between($a['start_date'], $today) >= PROGRAM_DAYS) continue;
        $new = find_training_date($office, $a);
        if ($new !== $a['training_date']) q('UPDATE advisors SET training_date = ? WHERE id = ?', [$new, $a['id']]);
    }
}

function advisors_without_training(array $office): int
{
    $today = office_today($office);
    return (int)val('SELECT COUNT(*) FROM advisors WHERE office_id = ? AND active = 1 AND training_date IS NULL
        AND start_date > DATE_SUB(?, INTERVAL ' . PROGRAM_DAYS . ' DAY)', [$office['id'], $today]);
}

/** Temas de oficina que algún asesor ya necesita y no tienen fecha. [[activity, [nombres]], …] */
function office_unscheduled(array $office, ?array $rows = null): array
{
    $rows = $rows ?? office_advisors_stats($office);
    $need = [];
    foreach ($rows as $r) {
        $a = $r['adv']; $s = $r['stats'];
        if (!$s['started'] || $s['finished'] || $a['decision'] === 'no_continua') continue;
        $done = done_keys($s['logs']);
        foreach (office_activities() as $act) {
            if ((int)$act['week_no'] > $s['week']) continue;
            if (!empty($s['sessions'][(int)$act['id']]) || in_array('A' . $act['id'], $done, true)) continue;
            $need[(int)$act['id']]['activity'] = $act;
            $need[(int)$act['id']]['names'][] = first_name($a['name']);
            $need[(int)$act['id']]['advisors'][] = (int)$a['id'];
        }
    }
    return array_values($need);
}

function office_advisors_stats(array $office, bool $onlyActive = true): array
{
    $rows = [];
    $sql = 'SELECT * FROM advisors WHERE office_id = ?' . ($onlyActive ? ' AND active = 1' : '') . ' ORDER BY start_date DESC, name';
    foreach (all($sql, [$office['id']]) as $a) $rows[] = ['adv' => $a, 'stats' => advisor_stats($a, $office)];
    $order = ['rojo' => 0, 'amarillo' => 1, 'verde' => 2];
    usort($rows, fn($x, $y) => [$order[$x['stats']['status']['color']], $x['adv']['name']] <=> [$order[$y['stats']['status']['color']], $y['adv']['name']]);
    return $rows;
}

function status_badge(string $color, string $title = ''): string
{
    $labels = ['verde' => 'Verde', 'amarillo' => 'Amarillo', 'rojo' => 'Rojo'];
    return '<span class="sem sem-' . h($color) . '" title="' . h($title) . '"><i></i>' . h($labels[$color] ?? $color) . '</span>';
}

/** Primera actividad de hoy que no es rutina (para recordatorios). */
function headline_activity(array $items): string
{
    $real = array_values(array_filter($items, fn($i) => !$i['routine'] && !$i['pending_session'] && !in_array($i['kind'], ['agenda', 'metas'], true)));
    if (!$real) return 'Tus contactos del día';
    return kind_prefix($real[0]['kind']) . $real[0]['title'] . (count($real) > 1 ? ' (+' . (count($real) - 1) . ')' : '');
}

// ---------------------------------------------------------------------------
// Reto del video: 5 preguntas que el Team Leader hace después de cada video
// ---------------------------------------------------------------------------
/** Preguntas de un video: [['q' => …, 'a' => …, 'min' => …], …] («Pregunta | Respuesta clave | minuto» por línea). */
function reto_questions(?array $a): array
{
    $out = [];
    foreach (preg_split('/\R/', (string)($a['reto'] ?? '')) as $line) {
        $p = array_map('trim', explode('|', $line));
        if (($p[0] ?? '') === '') continue;
        $out[] = ['q' => $p[0], 'a' => $p[1] ?? '', 'min' => $p[2] ?? ''];
    }
    return $out;
}

/** «Módulo 4: Open House» a partir de «Ve el Módulo 4: Open House». */
function reto_label(string $videoTitle): string
{
    return trim(preg_replace('/^Ve (el |la )?/u', '', $videoTitle));
}

/** Puntos de un Reto: 4 o 5 bien = 1; 3 = 0.5; menos = 0. */
function reto_points(?array $row): float
{
    if (!$row) return 0.0;
    $ok = (int)$row['ok_count'];
    return $ok >= 4 ? 1.0 : ($ok === 3 ? 0.5 : 0.0);
}

function advisor_retos(int $advisorId): array
{
    ensure_schema();
    $out = [];
    foreach (all('SELECT * FROM advisor_retos WHERE advisor_id = ?', [$advisorId]) as $r) $out[(int)$r['activity_id']] = $r;
    return $out;
}

/** Videos del plan que tienen Reto. */
function reto_points_label(float $p): string
{
    return rtrim(rtrim(number_format($p, 1), '0'), '.');
}

function reto_videos(): array
{
    $out = [];
    foreach (plan_activities() as $days) foreach ($days as $list) foreach ($list as $a) {
        if ($a['kind'] === 'video' && reto_questions($a)) $out[(int)$a['id']] = $a;
    }
    return $out;
}

/** Prefijo para las listas cortas (recordatorios, agenda). */
function kind_prefix(string $kind): string
{
    return $kind === 'oficina' ? 'Sesión en tu oficina: ' : ($kind === 'tl' ? 'Cita con tu TL en la oficina: ' : '');
}

/** Texto para pegar en el grupo de WhatsApp de la oficina. */
function daily_reminder_text(array $office): string
{
    $today = office_today($office);
    $lines = [];
    foreach (all('SELECT * FROM advisors WHERE office_id = ? AND active = 1 AND (decision IS NULL OR decision <> \'no_continua\') ORDER BY name', [$office['id']]) as $a) {
        $schedule = build_schedule($a, $office);
        $ctx = $schedule[$today] ?? null;
        if (!$ctx || $ctx['type'] === 'rest') continue;
        $items = day_items($ctx, ['office' => $office, 'sessions' => advisor_sessions($a, $office)]);
        $what = $ctx['type'] === 'training' ? 'Capacitación interna' : headline_activity($items);
        $lines[] = '• ' . first_name($a['name']) . ' (S' . $ctx['week'] . ' · ' . ctx_label($ctx) . '): ' . $what;
    }
    $txt = "🧵 *Ariadna · " . ucfirst(fecha_larga($today)) . "*\n\nBuenos días, equipo. Esto toca hoy:\n\n";
    $txt .= $lines ? implode("\n", $lines) : '• Hoy nadie tiene actividades del curso.';
    $txt .= "\n\nAntes de cualquier otra cosa, sus contactos del día. Al terminar, cuéntenme cómo les fue en Ariadna (menos de 2 minutos).\n\n¡No suelten el hilo!";
    return $txt;
}

/** Reporte semanal en texto, para pegarlo en WhatsApp. */
function weekly_report_text(array $office, array $rows): string
{
    $emoji = ['verde' => '🟢', 'amarillo' => '🟡', 'rojo' => '🔴'];
    $txt = "🧵 *Ariadna · Reporte semanal*\n" . $office['name'] . ' · ' . fecha_larga(office_today($office)) . "\n";
    foreach ($rows as $r) {
        $a = $r['adv']; $s = $r['stats'];
        if (!$s['started'] || $a['decision'] === 'no_continua') continue;
        $t = $s['totals'];
        $txt .= "\n" . $emoji[$s['status']['color']] . ' *' . $a['name'] . '* · ' . ($s['finished'] ? 'Terminó' : 'Semana ' . $s['week'])
            . "\nActividades " . $s['pct'] . '% · Contactos ' . $t['contactos'] . ' · Tratos ' . $t['tratos'] . ' · Analizadas ' . $t['analizadas']
            . ' · Promovidas ' . $t['promovidas'] . ' · Citas ' . $t['citas'] . ' · Captaciones ' . $t['captaciones']
            . ($s['tl']['due'] ? "\nCitas con su TL: " . $s['tl']['done'] . ' de ' . $s['tl']['due'] : '')
            . ($s['retos']['due'] ? ' · Retos del video: ' . reto_points_label($s['retos']['points']) . ' de ' . $s['retos']['due'] : '');
        if ($s['status']['action']) $txt .= "\n👉 " . $s['status']['action'];
        $txt .= "\n";
    }
    return $txt . "\n— Ariadna";
}

// ---------------------------------------------------------------------------
// Altas y avisos únicos
// ---------------------------------------------------------------------------
function create_user(?int $officeId, string $name, string $email, string $password, string $role): int
{
    q('INSERT INTO users (office_id, name, email, password_hash, role) VALUES (?,?,?,?,?)',
        [$officeId, $name, mb_strtolower(trim($email)), password_hash($password, PASSWORD_DEFAULT), $role]);
    return (int)db()->lastInsertId();
}

function create_advisor(array $office, string $name, string $phone, ?string $email, string $start): int
{
    q('INSERT INTO advisors (office_id, name, phone, email, start_date, token) VALUES (?,?,?,?,?,?)',
        [$office['id'], $name, $phone, $email ?: null, $start, new_token()]);
    $id = (int)db()->lastInsertId();
    $a = one('SELECT * FROM advisors WHERE id = ?', [$id]);
    $closed = max(0, min(PROGRAM_WEEKS, intdiv(days_between($start, office_today($office)), 7)));
    q('UPDATE advisors SET training_date = ?, closed_weeks = ? WHERE id = ?', [find_training_date($office, $a), $closed, $id]);
    return $id;
}

/** Marca un aviso como enviado. Regresa false si ya se había enviado antes. */
function notice_once(string $key): bool
{
    $st = q('INSERT IGNORE INTO notices (k, at) VALUES (?, NOW())', [mb_substr($key, 0, 190)]);
    return $st->rowCount() > 0;
}
