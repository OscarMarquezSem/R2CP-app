<?php
/**
 * ARIADNA · Tareas programadas.
 * Se ejecutan con el cron de Hostinger (cada hora es suficiente):
 *  - Actualiza el semáforo y detecta cierres de semana.
 *  - Alerta inmediata cuando un asesor pasa a rojo.
 *  - Correo de punto de control al cerrar la Semana 3.
 *  - Recordatorio diario al asesor (solo días laborables y si no ha registrado).
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
        try { cron_weekly_report($office, $say); }
        catch (Throwable $e) { $say('Error en reporte de ' . $office['name'] . ': ' . $e->getMessage()); }
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

    // Cierres de semana
    if ($s['closed_weeks'] > (int)$adv['closed_weeks']) {
        for ($w = (int)$adv['closed_weeks'] + 1; $w <= $s['closed_weeks']; $w++) {
            if ($w === 3) {
                send_checkpoint_email($adv, $office, $s);
                $say("Punto de control enviado: {$adv['name']}");
            }
        }
        q('UPDATE advisors SET closed_weeks = ? WHERE id = ?', [$s['closed_weeks'], $adv['id']]);
    }

    // Cambio a rojo
    if ($st['color'] === 'rojo' && $adv['status_color'] !== 'rojo') {
        send_red_alert($adv, $office, $s);
        $say("Alerta roja enviada: {$adv['name']}");
    }
    if ($st['color'] !== $adv['status_color'] || $st['reason'] !== $adv['status_reason']) {
        q('UPDATE advisors SET status_color = ?, status_reason = ? WHERE id = ?', [$st['color'], $st['reason'], $adv['id']]);
    }

    // Recordatorio del día (nunca en días de descanso)
    if (setting('reminder_enabled') === '1' && !$s['finished'] && $adv['email'] && $adv['consent_at']) {
        $ctx = $s['ctx'];
        $hour = (int)office_now($office)->format('G');
        if ($ctx && $ctx['type'] !== 'rest' && !isset($s['logs'][$s['today']])
            && $hour >= (int)setting('reminder_hour') && $adv['last_reminder_date'] !== $s['today']) {
            send_reminder($adv, $office, $s);
            q('UPDATE advisors SET last_reminder_date = ? WHERE id = ?', [$s['today'], $adv['id']]);
            $say("Recordatorio enviado: {$adv['name']}");
        }
    }
}

function panel_link(array $office, ?int $advisorId = null): string
{
    return $advisorId ? base_url('asesor.php?id=' . $advisorId) : base_url('oficina.php');
}

function send_red_alert(array $adv, array $office, array $s): void
{
    $html = '<p><b>' . h($adv['name']) . '</b> (' . h($office['name']) . ') pasó a ' . email_dot('rojo') . '.</p>'
        . '<p><b>Motivo:</b> ' . h($s['status']['reason']) . '</p>'
        . '<p><b>Acción sugerida:</b> ' . h($s['status']['action']) . '</p>'
        . '<p>Semana ' . (int)$s['week'] . ' · Actividades cumplidas: ' . $s['pct'] . '% · Contactos: ' . $s['totals']['contactos']
        . ' · Citas: ' . $s['totals']['citas'] . ' · Captaciones: ' . $s['totals']['captaciones'] . '</p>'
        . '<p><a href="' . h(panel_link($office, (int)$adv['id'])) . '" style="color:#C75D3A;font-weight:bold">Ver su detalle en Ariadna</a></p>';
    send_mail(office_recipients((int)$office['id'], ['director']), 'Ariadna · ' . $adv['name'] . ' pasó a rojo',
        email_wrap('Alerta: asesor en rojo', $html), admin_recipients(), 'alerta_rojo', (int)$office['id'], (int)$adv['id']);
}

function send_checkpoint_email(array $adv, array $office, array $s): void
{
    $cp = checkpoint($adv, $s);
    $html = '<p>' . h($adv['name']) . ' cerró su Semana 3. Este es su punto de control para decidir si continúa en el programa.</p>'
        . '<p style="font-size:18px"><b>Resultado: ' . ($cp['pass'] ? '<span style="color:#2E8B57">Cumple</span>' : '<span style="color:#C0392B">No cumple</span>') . '</b></p><ul>';
    foreach ($cp['checks'] as [$l, $ok]) $html .= '<li>' . ($ok ? '✅' : '❌') . ' ' . h($l) . '</li>';
    $html .= '</ul><table cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-size:14px;width:100%">'
        . '<tr style="background:#F1EBDC"><th align="left">Criterio</th><th>Resultado</th><th>Puntos</th></tr>';
    foreach ($s['evaluation']['rows'] as $r) {
        $html .= '<tr style="border-bottom:1px solid #E6DECB"><td>' . h($r['label']) . '</td><td align="center">' . h((string)$r['result'])
            . '</td><td align="center">' . h(rtrim(rtrim(number_format($r['pts'], 1), '0'), '.')) . ' / ' . (int)$r['max'] . '</td></tr>';
    }
    $html .= '<tr><td colspan="2"><b>Total</b></td><td align="center"><b>' . (int)$s['evaluation']['total'] . ' / 100</b></td></tr></table>'
        . '<p>' . h($s['evaluation']['level_text']) . '</p>'
        . '<p><a href="' . h(panel_link($office, (int)$adv['id'])) . '" style="color:#C75D3A;font-weight:bold">Registrar la decisión en Ariadna</a></p>';
    send_mail(office_recipients((int)$office['id']), 'Ariadna · Punto de control de ' . $adv['name'] . ': ' . ($cp['pass'] ? 'cumple' : 'no cumple'),
        email_wrap('Punto de control · Semana 3', $html), admin_recipients(), 'punto_control', (int)$office['id'], (int)$adv['id']);
}

function send_reminder(array $adv, array $office, array $s): void
{
    $items = array_values(array_filter(ctx_items($s['ctx']), fn($i) => !$i['routine']));
    $list = '';
    foreach (array_slice($items, 0, 6) as $it) $list .= '<li>' . h($it['title']) . '</li>';
    $html = '<p>Hola, ' . h(first_name($adv['name'])) . '. Hoy es Semana ' . (int)$s['ctx']['week'] . ' · ' . h(ctx_label($s['ctx'])) . '.</p>'
        . ($list ? '<p>Esto te toca hoy:</p><ul>' . $list . '</ul>' : '')
        . '<p>Antes de cualquier otra cosa, tus 2 contactos del día. Al terminar, registra tu día en menos de 2 minutos:</p>'
        . '<p><a href="' . h(advisor_link($adv)) . '" style="display:inline-block;background:#C75D3A;color:#fff;padding:12px 22px;border-radius:10px;text-decoration:none;font-weight:bold">Abrir mi día</a></p>'
        . '<p>No sueltes el hilo.</p>';
    send_mail([$adv['email']], 'Ariadna · Tu día de hoy', email_wrap('Tu día de hoy', $html), [], 'recordatorio', (int)$office['id'], (int)$adv['id']);
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
    $rows = office_advisors_stats($office);
    $rows = array_values(array_filter($rows, fn($r) => $r['stats']['started'] && $r['adv']['decision'] !== 'no_continua'
        && $r['stats']['elapsed'] < PROGRAM_DAYS + 7)); // incluye a quienes terminaron esta semana
    $td = 'style="padding:8px;border-bottom:1px solid #E6DECB;font-size:13px;vertical-align:top"';
    $html = '<p>Así va el arranque de tus asesores en <b>' . h($office['name']) . '</b> esta semana.</p>';
    $nt = advisors_without_training($office);
    if ($nt) $html .= '<p style="background:#FBE9DF;padding:10px;border-radius:8px">Tienes <b>' . $nt . '</b> asesores nuevos sin día de capacitación interna.</p>';
    if (!$rows) {
        $html .= '<p>No hay asesores activos en el programa.</p>';
    } else {
        $html .= '<table cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%"><tr style="background:#F1EBDC">'
            . '<th align="left" ' . $td . '>Asesor</th><th ' . $td . '>Semáforo</th><th ' . $td . '>Semana</th><th ' . $td . '>Actividades</th>'
            . '<th align="left" ' . $td . '>Resultados</th><th align="left" ' . $td . '>Acción sugerida</th></tr>';
        foreach ($rows as $r) {
            $a = $r['adv']; $s = $r['stats'];
            $res = 'Contactos ' . $s['totals']['contactos'] . ' · Tratos ' . $s['totals']['tratos'] . ' · Analizadas ' . $s['totals']['analizadas']
                . ' · Promovidas ' . $s['totals']['promovidas'] . ' · Citas ' . $s['totals']['citas'] . ' · Captaciones ' . $s['totals']['captaciones'];
            $html .= '<tr><td ' . $td . '><b>' . h($a['name']) . '</b></td><td ' . $td . '>' . email_dot($s['status']['color']) . '</td>'
                . '<td ' . $td . ' align="center">' . ($s['finished'] ? 'Terminó' : (int)$s['week']) . '</td>'
                . '<td ' . $td . ' align="center">' . $s['done'] . ' de ' . $s['assigned'] . ' (' . $s['pct'] . '%)</td>'
                . '<td ' . $td . '>' . h($res) . '</td><td ' . $td . '>' . h($s['status']['action'] ?: '—') . '</td></tr>';
        }
        $html .= '</table>';
    }
    $html .= '<p style="margin-top:16px"><a href="' . h(panel_link($office)) . '" style="color:#C75D3A;font-weight:bold">Abrir el panel de la oficina</a></p>';
    return send_mail(office_recipients((int)$office['id']), 'Ariadna · Reporte semanal · ' . $office['name'],
        email_wrap('Reporte semanal', $html), admin_recipients(), 'reporte_semanal', (int)$office['id']);
}
