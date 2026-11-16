<?php
/**
 * ARIADNA · Motor del programa
 * Calcula en qué semana y día va cada asesor, qué actividades le tocan,
 * su avance, su semáforo y su evaluación de 100 puntos.
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
    'contactos'   => 'Personas de tu círculo cercano que contactaste hoy',
    'tratos'      => 'Propietarios que venden por trato directo que contactaste',
    'analizadas'  => 'Propiedades que analizaste y anotaste en tu registro',
    'promovidas'  => 'Propiedades de la oficina que publicaste hoy',
    'citas'       => 'Citas que dejaste agendadas hoy',
    'captaciones' => 'Propiedades captadas con autorización del propietario',
];

// ---------------------------------------------------------------------------
// Plan (de la base de datos, editable por el administrador)
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

function day_activities(int $week, int $day): array
{
    return plan_activities()[$week][$day] ?? [];
}

function routine_items(): array
{
    $r = json_decode((string)setting('routine_items'), true);
    return is_array($r) ? $r : [];
}

function plan_meta_total(string $field): int
{
    $t = 0;
    foreach (plan_weeks() as $w) $t += (int)($w['meta_' . $field] ?? 0);
    return $t;
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
    $a = new DateTimeImmutable($from);
    $b = new DateTimeImmutable($to);
    return (int)$a->diff($b)->format('%r%a');
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

// ---------------------------------------------------------------------------
// Calendario personal del asesor
// ---------------------------------------------------------------------------
/**
 * Regresa, para cada uno de los 35 días del programa, qué le toca al asesor:
 *  type = plan | catchup | training | rest
 *  week, day_no (número de día laborable), days (días del plan que se muestran)
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
            if ($d === $training) {
                $out[$d] = ['date' => $d, 'week' => $w, 'type' => 'training', 'day_no' => 0, 'days' => []];
            } elseif (!in_array(iso_weekday($d), $wd, true)) {
                $out[$d] = ['date' => $d, 'week' => $w, 'type' => 'rest', 'day_no' => 0, 'days' => []];
            } else {
                $n++;
                if ($n <= PLAN_DAYS_PER_WEEK) {
                    // Si la semana tiene menos de 5 días disponibles, el último junta los que faltan.
                    $days = ($n === $W && $W < PLAN_DAYS_PER_WEEK) ? range($n, PLAN_DAYS_PER_WEEK) : [$n];
                    $out[$d] = ['date' => $d, 'week' => $w, 'type' => 'plan', 'day_no' => $n, 'days' => $days];
                } else {
                    $out[$d] = ['date' => $d, 'week' => $w, 'type' => 'catchup', 'day_no' => $n, 'days' => []];
                }
            }
        }
    }
    return $out;
}

function ctx_label(array $ctx): string
{
    switch ($ctx['type']) {
        case 'plan':
            return count($ctx['days']) > 1 ? 'Días ' . $ctx['days'][0] . ' a ' . end($ctx['days']) : 'Día ' . $ctx['day_no'];
        case 'catchup':  return 'Día ' . $ctx['day_no'] . ' · Ponte al corriente';
        case 'training': return 'Capacitación interna';
        default:         return 'Día de descanso';
    }
}

function routine_applies(array $ctx): bool
{
    if (!in_array($ctx['type'], ['plan', 'catchup'], true)) return false;
    $start = (int)(plan_weeks()[$ctx['week']]['routine_start_day'] ?? 1);
    $first = $ctx['days'] ? $ctx['days'][0] : $ctx['day_no'];
    return $first >= $start || $ctx['type'] === 'catchup';
}

/** Actividad del plan con forma de "item" para la pantalla. */
function act_item(array $a, ?string $origin = null): array
{
    return [
        'key' => 'A' . $a['id'], 'title' => $a['title'], 'guide' => $a['guide'],
        'minutes' => $a['minutes'] !== null ? (int)$a['minutes'] : null,
        'kind' => $a['kind'], 'week' => (int)$a['week_no'], 'day' => (int)$a['day_no'],
        'origin' => $origin, 'routine' => false,
    ];
}

/**
 * Lista de actividades que se muestran en una fecha.
 * $doneElsewhere: claves A# marcadas en OTROS días de la misma semana (para "ponte al corriente").
 */
function ctx_items(array $ctx, array $doneElsewhere = []): array
{
    $items = [];
    if ($ctx['type'] === 'training') {
        return [[
            'key' => 'CI', 'title' => 'Capacitación interna: políticas y documentos de la oficina',
            'guide' => 'Hoy tu oficina te presenta sus políticas y documentos internos. Escribe aquí lo más importante que debes recordar.',
            'minutes' => null, 'kind' => 'check', 'week' => $ctx['week'], 'day' => 0, 'origin' => null, 'routine' => false,
        ]];
    }
    if ($ctx['type'] === 'rest') return [];

    $acts = [];
    if ($ctx['type'] === 'plan') {
        foreach ($ctx['days'] as $d) {
            foreach (day_activities($ctx['week'], $d) as $a) {
                $acts[] = act_item($a, count($ctx['days']) > 1 && $d !== $ctx['days'][0] ? 'Del día ' . $d : null);
            }
        }
    } else { // catchup: lo que quedó pendiente en la semana
        for ($d = 1; $d <= PLAN_DAYS_PER_WEEK; $d++) {
            foreach (day_activities($ctx['week'], $d) as $a) {
                if (!in_array('A' . $a['id'], $doneElsewhere, true)) $acts[] = act_item($a, 'Pendiente del día ' . $d);
            }
        }
    }
    // Orden: "agenda" primero, luego la rutina diaria, luego lo demás.
    foreach ($acts as $a) if ($a['kind'] === 'agenda') $items[] = $a;
    if (routine_applies($ctx)) {
        foreach (routine_items() as $r) {
            $items[] = ['key' => $r['key'], 'title' => $r['title'], 'guide' => $r['guide'], 'minutes' => null,
                'kind' => 'check', 'week' => $ctx['week'], 'day' => 0, 'origin' => 'Rutina diaria', 'routine' => true];
        }
    }
    foreach ($acts as $a) if ($a['kind'] !== 'agenda') $items[] = $a;
    return $items;
}

/** Todas las actividades de una semana con su tiempo (para la actividad "Agenda tus horarios"). */
function week_time_list(int $week): array
{
    $list = [];
    for ($d = 1; $d <= PLAN_DAYS_PER_WEEK; $d++) {
        foreach (day_activities($week, $d) as $a) {
            if ($a['kind'] === 'agenda') continue;
            $list[] = ['day' => $d, 'title' => $a['title'], 'minutes' => $a['minutes'] !== null ? (int)$a['minutes'] : null];
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
// Registros diarios
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

/** Claves A# marcadas en una semana, excluyendo opcionalmente una fecha. */
function week_done_keys(array $logs, array $schedule, int $week, ?string $exceptDate = null): array
{
    $keys = [];
    foreach ($logs as $date => $l) {
        if ($date === $exceptDate || ($schedule[$date]['week'] ?? 0) !== $week) continue;
        foreach ($l['items'] as $k => $it) if ((int)$it['done'] === 1 && $k[0] === 'A') $keys[] = $k;
    }
    return array_values(array_unique($keys));
}

// ---------------------------------------------------------------------------
// Avance, semáforo y evaluación
// ---------------------------------------------------------------------------
function advisor_stats(array $adv, array $office, ?string $today = null): array
{
    $today = $today ?? office_today($office);
    $schedule = build_schedule($adv, $office);
    $logs = advisor_logs((int)$adv['id']);
    $elapsed = days_between($adv['start_date'], $today);
    $started = $elapsed >= 0;
    $finished = $elapsed >= PROGRAM_DAYS;
    $week = $started ? min(PROGRAM_WEEKS, intdiv(max(0, $elapsed), 7) + 1) : 1;
    $closed = $started ? min(PROGRAM_WEEKS, intdiv($elapsed, 7)) : 0;

    // Totales de resultados
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

    // Actividades asignadas vs. cumplidas (hasta hoy)
    $assigned = 0; $done = 0;
    $weekAssigned = array_fill(1, PROGRAM_WEEKS, 0);
    $weekDone = array_fill(1, PROGRAM_WEEKS, 0);
    $doneA = [];
    $routineN = count(routine_items());
    $workdaysElapsed = 0; $workdaysLogged = 0;
    foreach ($schedule as $date => $ctx) {
        $w = $ctx['week'];
        $n = 0;
        if ($ctx['type'] === 'plan') {
            foreach ($ctx['days'] as $d) $n += count(day_activities($w, $d));
            if (routine_applies($ctx)) $n += $routineN;
        } elseif ($ctx['type'] === 'training') {
            $n = 1;
        } elseif ($ctx['type'] === 'catchup' && routine_applies($ctx)) {
            $n = $routineN;
        }
        $weekAssigned[$w] += $n;
        if ($date <= $today) {
            $assigned += $n;
            if ($ctx['type'] !== 'rest' && ($date < $today || isset($logs[$date]))) {
                $workdaysElapsed++;
                if (isset($logs[$date])) $workdaysLogged++;
            }
        }
        if (isset($logs[$date])) {
            foreach ($logs[$date]['items'] as $k => $it) {
                if ((int)$it['done'] !== 1) continue;
                if ($k[0] === 'A') { $doneA[$w][$k] = true; }
                else { $weekDone[$w]++; if ($date <= $today) $done++; }
            }
        }
    }
    foreach ($doneA as $w => $keys) { $weekDone[$w] += count($keys); $done += count($keys); }

    // Días laborables seguidos sin registrar (sin contar hoy)
    $missed = 0;
    if ($started) {
        $d = add_days($today, -1);
        while ($d >= $adv['start_date']) {
            $ctx = $schedule[$d] ?? null;
            if ($ctx && $ctx['type'] !== 'rest') {
                if (isset($logs[$d])) break;
                $missed++;
            }
            $d = add_days($d, -1);
        }
    }

    $last = $logs ? max(array_keys($logs)) : null;
    $s = [
        'today' => $today, 'schedule' => $schedule, 'logs' => $logs,
        'started' => $started, 'finished' => $finished, 'elapsed' => $elapsed,
        'week' => $week, 'closed_weeks' => $closed,
        'ctx' => $schedule[$today] ?? null,
        'totals' => $totals, 'week_totals' => $weekTotals, 'week_has_activity' => $weekHasActivity,
        'assigned' => $assigned, 'done' => min($done, max($assigned, $done)),
        'pct' => $assigned ? (int)round(min(1, $done / $assigned) * 100) : 0,
        'week_assigned' => $weekAssigned, 'week_done' => $weekDone,
        'missed' => $missed, 'last_log' => $last,
        'workdays_elapsed' => $workdaysElapsed, 'workdays_logged' => $workdaysLogged,
    ];
    $s['evaluation'] = evaluation($adv, $s);
    $s['status'] = compute_status($adv, $s);
    return $s;
}

function cum_totals(array $stats, int $uptoWeek): array
{
    $t = array_fill_keys(array_keys(RESULT_FIELDS), 0);
    for ($w = 1; $w <= $uptoWeek; $w++) foreach ($t as $f => $_) $t[$f] += $stats['week_totals'][$w][$f];
    return $t;
}

/** Semáforo. Regresa color, motivo y acción sugerida para la oficina. */
function compute_status(array $adv, array $s): array
{
    $name = first_name($adv['name']);
    if (!$s['started']) return ['color' => 'verde', 'reason' => 'Aún no inicia', 'action' => '', 'rule' => null];

    // Rojo: se evalúa al cierre de la semana personal. Aplica la regla de la última semana cerrada que tenga regla.
    for ($w = min(3, $s['closed_weeks']); $w >= 1; $w--) {
        if ($w === 1 && setting('red_s1_enabled') === '1') {
            if (!$s['week_has_activity'][1]) {
                return ['color' => 'rojo', 'rule' => 1,
                    'reason' => 'Cerró su Semana 1 sin actividad registrada',
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
                return ['color' => 'rojo', 'rule' => 3,
                    'reason' => 'Cerró su Semana 3 sin citas ni captaciones',
                    'action' => "Punto de control: $name no tiene citas ni captaciones. Habla con él o ella y decide si continúa."];
            }
            break;
        }
    }
    $y = (int)setting('yellow_days');
    if (!$s['finished'] && $s['missed'] >= $y) {
        return ['color' => 'amarillo', 'rule' => null,
            'reason' => $s['missed'] . ' días laborables sin registrar',
            'action' => "Habla hoy con $name: {$s['missed']} días sin registrar."];
    }
    return ['color' => 'verde', 'rule' => null, 'reason' => $s['finished'] ? 'Terminó el programa' : 'Registra y avanza', 'action' => ''];
}

/** Cuadro de Evaluación del Asesor (100 puntos), calculado con lo que registra el asesor. */
function evaluation(array $adv, array $s): array
{
    $t = $s['totals'];
    $prop = function (int $res, int $meta, float $max): float {
        if ($meta <= 0) return $res > 0 ? $max : 0.0;
        return round(min(1, $res / $meta) * $max, 1);
    };
    $rows = [];
    $m = fn($f) => plan_meta_total($f);
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
    $qPts = !$cap ? 0.0 : ($q === 3 ? 10.0 : ($q === 2 ? 5.0 : 0.0));
    $rows[] = ['group' => 'Captación', 'label' => 'Logró captar al menos 1 propiedad', 'meta' => 'Sí', 'result' => $cap ? 'Sí' : 'No', 'max' => 10, 'pts' => $cap ? 10.0 : 0.0];
    $rows[] = ['group' => 'Captación', 'label' => 'Calidad de la captación (precio, documentos, disponibilidad)', 'meta' => 'Alta', 'result' => $qLabel, 'max' => 10, 'pts' => $qPts];

    $oh = (int)$adv['oh_done'] === 1;
    $att = (int)$adv['oh_attendees'];
    $minAtt = (int)setting('oh_min_attendees');
    $rows[] = ['group' => 'Open House', 'label' => 'Realizó el Open House', 'meta' => 'Sí', 'result' => $oh ? 'Sí' : 'No', 'max' => 10, 'pts' => $oh ? 10.0 : 0.0];
    $rows[] = ['group' => 'Open House', 'label' => "Asistentes reales (mínimo $minAtt)", 'meta' => $minAtt, 'result' => $oh ? $att : 0, 'max' => 10, 'pts' => $oh ? $prop($att, $minAtt, 10) : 0.0];

    $renta = (int)$adv['renta_closed'] === 1;
    $rows[] = ['group' => 'Cierre de renta', 'label' => '¿Cerró una renta?', 'meta' => 'Sí', 'result' => $renta ? 'Sí' : 'No', 'max' => 20, 'pts' => $renta ? 20.0 : 0.0];

    $total = (int)round(array_sum(array_column($rows, 'pts')));
    if ($total >= 90)      $level = ['TOP', 'Nivel TOP: listo para avanzar al siguiente programa.'];
    elseif ($total >= 70)  $level = ['Buen inicio', 'Buen inicio: reforzar consistencia y técnica.'];
    elseif ($total >= 50)  $level = ['Incompleto', 'Proceso incompleto: repetir el mes con acompañamiento.'];
    else                   $level = ['Sin actividad', 'No hay actividad: no puede continuar.'];
    return ['rows' => $rows, 'total' => $total, 'level' => $level[0], 'level_text' => $level[1]];
}

/** Punto de control de la Semana 3. */
function checkpoint(array $adv, array $s): array
{
    $c = cum_totals($s, 3);
    $oh = (int)$adv['oh_done'] === 1;
    $capOrCita = $c['captaciones'] >= 1 || $c['citas'] >= 1;
    $min = (int)setting('checkpoint_min_score');
    $score = $s['evaluation']['total'];
    return [
        'checks' => [
            ['Realizó su Open House', $oh],
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

/** Reasigna la capacitación a los asesores cuya fecha aún no pasa (y que no fue fijada a mano). */
function reassign_training(array $office): void
{
    $today = office_today($office);
    foreach (all('SELECT * FROM advisors WHERE office_id = ? AND active = 1 AND training_manual = 0', [$office['id']]) as $a) {
        if ($a['training_date'] && $a['training_date'] < $today) continue; // ya la tomó
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

/** Asesores activos de una oficina con sus estadísticas. */
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

/** Texto para pegar en el grupo de WhatsApp de la oficina. */
function daily_reminder_text(array $office): string
{
    $today = office_today($office);
    $lines = [];
    foreach (all('SELECT * FROM advisors WHERE office_id = ? AND active = 1 AND (decision IS NULL OR decision <> \'no_continua\') ORDER BY name', [$office['id']]) as $a) {
        $schedule = build_schedule($a, $office);
        $ctx = $schedule[$today] ?? null;
        if (!$ctx || $ctx['type'] === 'rest') continue;
        if ($ctx['type'] === 'training') { $what = 'Capacitación interna'; }
        else {
            $items = array_values(array_filter(ctx_items($ctx), fn($i) => !$i['routine']));
            $what = $items ? $items[0]['title'] : 'Rutina de prospección';
            if (count($items) > 1) $what .= ' (+' . (count($items) - 1) . ')';
        }
        $lines[] = '• ' . first_name($a['name']) . ' (S' . $ctx['week'] . ' · ' . ctx_label($ctx) . '): ' . $what;
    }
    $txt = "🧵 *Ariadna · " . ucfirst(fecha_larga($today)) . "*\n\nBuenos días, equipo. Esto toca hoy:\n\n";
    $txt .= $lines ? implode("\n", $lines) : '• Hoy nadie tiene actividades del programa.';
    $txt .= "\n\nAntes de cualquier otra cosa, sus 2 contactos del día. Al terminar, registren su día en su enlace de Ariadna (menos de 2 minutos).\n\n¡No suelten el hilo!";
    return $txt;
}

// ---------------------------------------------------------------------------
// Altas
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
    q('UPDATE advisors SET training_date = ? WHERE id = ?', [find_training_date($office, $a), $id]);
    // Las semanas ya transcurridas (si la fecha de inicio es pasada) no generan correos retroactivos.
    $closed = max(0, min(PROGRAM_WEEKS, intdiv(days_between($start, office_today($office)), 7)));
    q('UPDATE advisors SET closed_weeks = ? WHERE id = ?', [$closed, $id]);
    return $id;
}
