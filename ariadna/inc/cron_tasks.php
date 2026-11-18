<?php
/**
 * ARIADNA · Tareas programadas (cron de Hostinger, cada hora):
 *  - Semáforo y cierres de semana; alerta inmediata cuando un asesor pasa a rojo.
 *  - Correo de punto de control al cerrar la Semana 3.
 *  - Aviso de la mañana (qué le toca hoy) y aviso de la noche (solo si no ha registrado),
 *    por notificación del celular y, si no la tiene, por correo. Nunca en días de descanso.
 *  - Aviso al director de temas de oficina sin fecha que sus asesores ya necesitan.
 *  - Reporte semanal (viernes 6 pm, configurable).
 */
declare(strict_types=1);

function run_cron(bool $verbose = false): array
{
    $log = [];
    $say = function (string $m) use (&$log, $verbose) { $log[] = $m; if ($verbose) echo $m, PHP_EOL; };
    foreach (all('SELECT * FROM offices WHERE active = 1') as $office) {
        foreach (all('SELECT * FROM advisors WHERE office_id = ? AND active = 1', [$office['id']]) as $adv) {
            try { cron_advisor($adv, $office, $say); }
            catch (Throwable $e) { $say('Error con asesor ' . $adv['id'] . ': ' . $e->getMessage()); }
        }
        try { cron_unscheduled_sessions($office, $say); } catch (Throwable $e) { $say('Error en sesiones de ' . $office['name'] . ': ' . $e->getMessage()); }
        try { cron_weekly_report($office, $say); } catch (Throwable $e) { $say('Error en reporte de ' . $office['name'] . ': ' . $e->getMessage()); }
    }
    set_setting('last_cron_run', date('Y-m-d H:i:s'));
    return $log;
}

function cron_advisor(array $adv, array $office, callable $say): void
{
    if ($adv['decision'] === 'no_continua') return;
    $s = advisor_stats($adv, $office);
    if (!$s['started']) return;
    $st = $s['status'];

    if ($s['closed_weeks'] > (int)$adv['closed_weeks']) {
        for ($w = (int)$adv['closed_weeks'] + 1; $w <= $s['closed_weeks']; $w++) {
            if ($w === 3) { send_checkpoint_email($adv, $office, $s); $say("Punto de control enviado: {$adv['name']}"); }
        }
        q('UPDATE advisors SET closed_weeks = ? WHERE id = ?', [$s['closed_weeks'], $adv['id']]);
    }
    if ($st['color'] === 'rojo' && $adv['status_color'] !== 'rojo') { send_red_alert($adv, $office, $s); $say("Alerta roja enviada: {$adv['name']}"); }
    if ($st['color'] !== $adv['status_color'] || $st['reason'] !== $adv['status_reason']) {
        q('UPDATE advisors SET status_color = ?, status_reason = ? WHERE id = ?', [$st['color'], $st['reason'], $adv['id']]);
    }

    // Avisos al asesor (nunca en descanso)
    if (setting('reminder_enabled') !== '1' || $s['finished'] || !$adv['consent_at']) return;
    $ctx = $s['ctx'];
    if (!$ctx || $ctx['type'] === 'rest') return;
    $hour = (int)office_now($office)->format('G');
    $today = $s['today'];
    $logged = isset($s['logs'][$today]);
    if ((int)$office['remind_morning'] === 1 && $hour >= (int)$office['remind_morning_hour'] && $hour < (int)$office['remind_morning_hour'] + 3
        && $adv['last_morning_reminder'] !== $today && !$logged) {
        q('UPDATE advisors SET last_morning_reminder = ? WHERE id = ?', [$today, $adv['id']]);
        $how = send_morning_reminder($adv, $office, $s);
        $say("Aviso de la mañana ($how): {$adv['name']}");
    }
    if ((int)$office['remind_night'] === 1 && $hour >= (int)$office['remind_night_hour']
        && $adv['last_night_reminder'] !== $today && !$logged) {
        q('UPDATE advisors SET last_night_reminder = ? WHERE id = ?', [$today, $adv['id']]);
        $how = send_night_reminder($adv, $office);
        $say("Aviso de la noche ($how): {$adv['name']}");
    }
}

function panel_link(array $office, ?int $advisorId = null): string
{
    return $advisorId ? base_url('asesor.php?id=' . $advisorId) : base_url('oficina.php');
}

/** Notificación al celular; si no tiene, correo. Regresa por dónde salió. */
function notify_advisor(array $adv, array $office, string $title, string $body, string $emailTitle, string $emailHtml, string $kind): string
{
    $url = advisor_link($adv);
    if (push_to_advisor((int)$adv['id'], $title, $body, $url) > 0) return 'celular';
    if ($adv['email']) {
        send_mail([$adv['email']], 'Ariadna · ' . $emailTitle, email_wrap($emailTitle, $emailHtml), [], $kind, (int)$office['id'], (int)$adv['id']);
        return 'correo';
    }
    return 'sin medio';
}

function send_morning_reminder(array $adv, array $office, array $s): string
{
    $items = day_items($s['ctx'], ['office' => $office, 'sessions' => $s['sessions'], 'goal_contactos' => goal_for($s['goals'], $s['ctx']['week'], 'contactos')]);
    $name = first_name($adv['name']);
    $head = headline_activity($items);
    $body = "Buen día, $name. Hoy te toca: $head. Antes que nada, tus contactos del día.";
    $list = '';
    foreach ($items as $it) {
        $extra = $it['session'] ? ' (' . time_label($it['session']['stime']) . ')' : '';
        $txt = $it['pending_session'] ? 'Pide a tu oficina tu sesión de: ' . $it['title'] : (kind_prefix($it['kind']) . $it['title'] . $extra);
        $list .= '<li>' . h($txt) . '</li>';
    }
    $html = '<p>Buen día, ' . h($name) . '. Soy Ariadna. Hoy es tu Semana ' . (int)$s['ctx']['week'] . ' · ' . h(ctx_label($s['ctx'])) . ' y esto te toca:</p><ul>' . $list . '</ul>'
        . '<p>Cuando termines, cuéntame cómo te fue (menos de 2 minutos):</p>' . email_button(advisor_link($adv), 'Abrir mi día')
        . '<p>No sueltes el hilo.<br>— Ariadna, tu Team Leader del Fast Track Residencial</p>';
    return notify_advisor($adv, $office, 'Ariadna · Tu día de hoy', $body, 'Tu día de hoy', $html, 'recordatorio');
}

function send_night_reminder(array $adv, array $office): string
{
    $name = first_name($adv['name']);
    $body = "$name, todavía no me cuentas cómo te fue hoy. Te toma menos de 2 minutos. No sueltes el hilo.";
    $html = '<p>' . h($body) . '</p>' . email_button(advisor_link($adv), 'Abrir mi día') . '<p>— Ariadna</p>';
    return notify_advisor($adv, $office, 'Ariadna', $body, 'Todavía no me cuentas cómo te fue', $html, 'aviso_noche');
}

function email_button(string $url, string $label): string
{
    return '<p><a href="' . h($url) . '" style="display:inline-block;background:#C75D3A;color:#fff;padding:12px 22px;border-radius:10px;text-decoration:none;font-weight:bold">' . h($label) . '</a></p>';
}

function director_name(int $officeId): string
{
    $n = val("SELECT name FROM users WHERE office_id = ? AND role = 'director' AND active = 1 ORDER BY id LIMIT 1", [$officeId]);
    return $n ? first_name((string)$n) : 'Hola';
}

function send_red_alert(array $adv, array $office, array $s): void
{
    $html = '<p>' . h(director_name((int)$office['id'])) . ', te aviso que <b>' . h($adv['name']) . '</b> (' . h($office['name']) . ') pasó a ' . email_dot('rojo') . '.</p>'
        . '<p><b>Motivo:</b> ' . h($s['status']['reason']) . '</p><p><b>Te sugiero:</b> ' . h($s['status']['action']) . '</p>'
        . '<p>Semana ' . (int)$s['week'] . ' · Actividades cumplidas: ' . $s['pct'] . '% · Contactos: ' . $s['totals']['contactos']
        . ' · Citas: ' . $s['totals']['citas'] . ' · Captaciones: ' . $s['totals']['captaciones'] . '</p>'
        . '<p><a href="' . h(panel_link($office, (int)$adv['id'])) . '" style="color:#C75D3A;font-weight:bold">Ver su detalle en Ariadna</a></p><p>— Ariadna</p>';
    send_mail(office_recipients((int)$office['id'], ['director']), 'Ariadna · ' . $adv['name'] . ' pasó a rojo',
        email_wrap('Alerta: asesor en rojo', $html), admin_recipients(), 'alerta_rojo', (int)$office['id'], (int)$adv['id']);
}

function send_low_goals_alert(array $adv, array $office, int $week, array $low): void
{
    $rows = '';
    foreach ($low as [$f, $label, $val, $min]) {
        $rows .= '<tr style="border-bottom:1px solid #E6DECB"><td style="padding:8px">' . h($label) . '</td><td style="padding:8px;color:#A94B2C;font-weight:bold">' . $val . '</td><td style="padding:8px">' . $min . '</td></tr>';
    }
    $html = '<p>' . h(director_name((int)$office['id'])) . ', te aviso que <b>' . h($adv['name']) . '</b> (Semana ' . $week . ') se puso metas por debajo del mínimo del programa. Se lo hice notar y decidió dejarlas así.</p>'
        . '<table cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;font-size:14px"><tr style="background:#F1EBDC;text-align:left"><th style="padding:8px">Meta</th><th style="padding:8px">Su meta</th><th style="padding:8px">Mínimo del programa</th></tr>' . $rows . '</table>'
        . '<p><b>Te sugiero:</b> hablar con él o ella hoy. Con estas metas es difícil que su arranque sea rentable; vale la pena revisar juntos su Mi Mapa y su compromiso con el curso.</p>'
        . '<p><a href="' . h(panel_link($office, (int)$adv['id'])) . '" style="color:#C75D3A;font-weight:bold">Ver su detalle en Ariadna</a></p><p>— Ariadna</p>';
    send_mail(office_recipients((int)$office['id'], ['director']), 'Ariadna · ' . $adv['name'] . ' se puso metas por debajo del mínimo',
        email_wrap('Metas por debajo del mínimo', $html), admin_recipients(), 'metas_bajas', (int)$office['id'], (int)$adv['id']);
}

function send_checkpoint_email(array $adv, array $office, array $s): void
{
    $cp = checkpoint($adv, $s, $office);
    $html = '<p>Hola, ' . h(director_name((int)$office['id'])) . '. ' . h($adv['name']) . ' cerró su Semana 3. Te comparto su punto de control para que decidas si continúa en el curso.</p>'
        . '<p style="font-size:18px"><b>Resultado: ' . ($cp['pass'] ? '<span style="color:#2E8B57">Cumple</span>' : '<span style="color:#C0392B">No cumple</span>') . '</b></p><ul>';
    foreach ($cp['checks'] as [$l, $ok]) $html .= '<li>' . ($ok ? '✅' : '❌') . ' ' . h($l) . '</li>';
    $html .= '</ul><table cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-size:14px;width:100%"><tr style="background:#F1EBDC"><th align="left">Criterio</th><th>Resultado</th><th>Puntos</th></tr>';
    foreach ($s['evaluation']['rows'] as $r) {
        $html .= '<tr style="border-bottom:1px solid #E6DECB"><td>' . h($r['label']) . '</td><td align="center">' . h((string)$r['result'])
            . '</td><td align="center">' . h(rtrim(rtrim(number_format($r['pts'], 1), '0'), '.')) . ' / ' . (int)$r['max'] . '</td></tr>';
    }
    $html .= '<tr><td colspan="2"><b>Total</b></td><td align="center"><b>' . (int)$s['evaluation']['total'] . ' / 100</b></td></tr></table>'
        . '<p>' . h($s['evaluation']['level_text']) . '</p>'
        . '<p><a href="' . h(panel_link($office, (int)$adv['id'])) . '" style="color:#C75D3A;font-weight:bold">Registrar la decisión en Ariadna</a></p><p>— Ariadna</p>';
    send_mail(office_recipients((int)$office['id']), 'Ariadna · Punto de control de ' . $adv['name'] . ': ' . ($cp['pass'] ? 'cumple' : 'no cumple'),
        email_wrap('Punto de control · Semana 3', $html), admin_recipients(), 'punto_control', (int)$office['id'], (int)$adv['id']);
}

/** Avisa al director (una sola vez por asesor y tema) de los temas de oficina que faltan programar. */
function cron_unscheduled_sessions(array $office, callable $say): void
{
    $new = [];
    foreach (office_unscheduled($office) as $n) {
        foreach ($n['advisors'] as $i => $aid) {
            if (notice_once('sesion_sin_fecha:' . $aid . ':' . $n['activity']['id'])) $new[$n['activity']['title']][] = $n['names'][$i];
        }
    }
    if (!$new) return;
    $list = '';
    foreach ($new as $title => $names) $list .= '<li><b>' . h($title) . '</b> · lo necesita: ' . h(implode(', ', $names)) . '</li>';
    $html = '<p>' . h(director_name((int)$office['id'])) . ', estos temas de oficina todavía no tienen fecha y tus asesores ya los necesitan:</p><ul>' . $list . '</ul>'
        . '<p>Programa el día y la hora en tu panel. Cuando lo hagas, yo se los muestro a cada asesor en su día.</p>'
        . '<p><a href="' . h(base_url('sesiones.php')) . '" style="color:#C75D3A;font-weight:bold">Programar sesiones</a></p><p>— Ariadna</p>';
    send_mail(office_recipients((int)$office['id'], ['director', 'gerente', 'team_leader']), 'Ariadna · Temas de oficina sin fecha',
        email_wrap('Temas de oficina sin fecha', $html), [], 'sesiones_sin_fecha', (int)$office['id']);
    $say('Aviso de temas sin fecha: ' . $office['name']);
}

function cron_weekly_report(array $office, callable $say, bool $force = false): void
{
    $tz = new DateTimeZone((string)setting('report_timezone') ?: 'America/Cancun');
    $now = new DateTimeImmutable('now', $tz);
    $today = $now->format('Y-m-d');
    if (!$force) {
        if ((int)$now->format('N') !== (int)setting('report_weekday')) return;
        if ((int)$now->format('G') < (int)setting('report_hour')) return;
        if ($office['last_report_date'] === $today) return;
    }
    send_weekly_report($office);
    q('UPDATE offices SET last_report_date = ? WHERE id = ?', [$today, $office['id']]);
    $say('Reporte semanal enviado: ' . $office['name']);
}

function send_weekly_report(array $office): bool
{
    $all = office_advisors_stats($office);
    $rows = array_values(array_filter($all, fn($r) => $r['stats']['started'] && $r['adv']['decision'] !== 'no_continua'
        && $r['stats']['elapsed'] < PROGRAM_DAYS + 7));
    $td = 'style="padding:8px;border-bottom:1px solid #E6DECB;font-size:13px;vertical-align:top"';
    $html = '<p>Hola, ' . h(director_name((int)$office['id'])) . '. Soy Ariadna, la Team Leader de tus asesores en el curso Fast Track Residencial. Te cuento cómo van en <b>' . h($office['name']) . '</b> esta semana.</p>';
    $nt = advisors_without_training($office);
    if ($nt) $html .= '<p style="background:#FBE9DF;padding:10px;border-radius:8px">Tienes <b>' . $nt . '</b> asesores nuevos sin día de capacitación interna.</p>';
    $un = office_unscheduled($office, $all);
    if ($un) $html .= '<p style="background:#FBE9DF;padding:10px;border-radius:8px"><b>' . count($un) . '</b> temas de oficina sin fecha que tus asesores ya necesitan: ' . h(implode(' · ', array_map(fn($n) => $n['activity']['title'], $un))) . '.</p>';
    if (!$rows) {
        $html .= '<p>No hay asesores activos en el curso.</p>';
    } else {
        $html .= '<table cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%"><tr style="background:#F1EBDC">'
            . '<th align="left" ' . $td . '>Asesor</th><th ' . $td . '>Semáforo</th><th ' . $td . '>Semana</th><th ' . $td . '>Actividades</th>'
            . '<th align="left" ' . $td . '>Resultados</th><th align="left" ' . $td . '>Te sugiero</th></tr>';
        foreach ($rows as $r) {
            $a = $r['adv']; $s = $r['stats']; $t = $s['totals'];
            $res = 'Contactos ' . $t['contactos'] . ' · Tratos ' . $t['tratos'] . ' · Analizadas ' . $t['analizadas']
                . ' · Promovidas ' . $t['promovidas'] . ' · Citas ' . $t['citas'] . ' · Captaciones ' . $t['captaciones'];
            $html .= '<tr><td ' . $td . '><b>' . h($a['name']) . '</b></td><td ' . $td . '>' . email_dot($s['status']['color']) . '</td>'
                . '<td ' . $td . ' align="center">' . ($s['finished'] ? 'Terminó' : (int)$s['week']) . '</td>'
                . '<td ' . $td . ' align="center">' . $s['done'] . ' de ' . $s['assigned'] . ' (' . $s['pct'] . '%)</td>'
                . '<td ' . $td . '>' . h($res) . '</td><td ' . $td . '>' . h($s['status']['action'] ?: '—') . '</td></tr>';
        }
        $html .= '</table>';
    }
    $html .= '<p style="margin-top:16px"><a href="' . h(panel_link($office)) . '" style="color:#C75D3A;font-weight:bold">Abrir el panel de la oficina</a></p><p>Seguimos el hilo.<br>— Ariadna</p>';
    return send_mail(office_recipients((int)$office['id']), 'Ariadna · Reporte semanal · ' . $office['name'],
        email_wrap('Reporte semanal', $html), admin_recipients(), 'reporte_semanal', (int)$office['id']);
}
