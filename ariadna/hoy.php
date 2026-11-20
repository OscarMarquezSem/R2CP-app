<?php
/**
 * ARIADNA · Pantalla del asesor: "Hoy"
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';
require __DIR__ . '/inc/advisor_auth.php';
require __DIR__ . '/inc/hoy_view.php';
require __DIR__ . '/inc/cron_tasks.php';

enforce_https();
start_session();
[$adv, $office] = advisor_from_token();
prog_set(advisor_program($adv));
$t = $adv['token'];
$self = 'hoy.php?t=' . $t;
$opts = ['body' => 'advisor prog-' . prog(), 'manifest' => 'manifest.php?t=' . $t, 'brand' => P()['name']];

// Curso detenido por la oficina
if (!(int)$adv['active'] || $adv['decision'] === 'no_continua') {
    page_start(P()['name'], $opts);
    echo '<div class="card narrow center">' . brand_mark() . '<h2>Tu acompañamiento está en pausa</h2><p>Habla con tu director para saber cómo seguir.</p></div>';
    page_end();
    exit;
}

// Primera vez: Ariadna se presenta, aviso de privacidad y consentimiento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'consent') {
    adv_csrf_check($t);
    if (!empty($_POST['acepto'])) {
        q('UPDATE advisors SET consent_at = NOW(), consent_ip = ? WHERE id = ?', [client_ip(), $adv['id']]);
        redirect($self);
    }
    flash('Para continuar marca la casilla de aceptación.', 'err');
    redirect($self);
}
if (!$adv['consent_at']) {
    page_start('Bienvenida', $opts);
    render_welcome(first_name($adv['name']), $office['name'], $t);
    page_end();
    exit;
}

// PIN opcional (activable por oficina)
if ((int)$office['pin_enabled'] === 1) {
    $lockKey = 'pin:' . $adv['id'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array(post('action'), ['set_pin', 'pin'], true)) {
        adv_csrf_check($t);
        $pin = preg_replace('/\D/', '', (string)post('pin'));
        if (post('action') === 'set_pin' && !$adv['pin_hash']) {
            if (strlen($pin) !== 4 || $pin !== preg_replace('/\D/', '', (string)post('pin2'))) {
                flash('Los PIN no coinciden o no tienen 4 dígitos.', 'err');
            } else {
                q('UPDATE advisors SET pin_hash = ? WHERE id = ?', [password_hash($pin, PASSWORD_DEFAULT), $adv['id']]);
                $adv = one('SELECT * FROM advisors WHERE id = ?', [$adv['id']]);
                pin_remember($adv);
            }
            redirect($self);
        }
        if (post('action') === 'pin' && $adv['pin_hash']) {
            if (too_many_attempts($lockKey, 5, 15)) {
                flash('Demasiados intentos. Espera 15 minutos o pide a tu oficina que restablezca tu PIN.', 'err');
            } elseif (password_verify($pin, $adv['pin_hash'])) {
                clear_attempts($lockKey);
                pin_remember($adv);
            } else {
                record_attempt($lockKey);
                flash('PIN incorrecto.', 'err');
            }
            redirect($self);
        }
    }
    if (!$adv['pin_hash'] || !pin_verified($adv)) {
        page_start('Tu PIN', $opts);
        $set = !$adv['pin_hash'];
        echo '<div class="card narrow center">' . brand_mark() . '<h2>' . ($set ? 'Crea tu PIN de 4 dígitos' : 'Escribe tu PIN') . '</h2>'
            . '<p class="muted">' . ($set ? 'Tu oficina pide un PIN para proteger tu información.' : 'Hola, ' . h(first_name($adv['name'])) . '.') . '</p>'
            . '<form method="post" class="form pinform">' . adv_csrf_field($t) . '<input type="hidden" name="t" value="' . h($t) . '"><input type="hidden" name="action" value="' . ($set ? 'set_pin' : 'pin') . '">'
            . '<input class="pin" name="pin" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="off" required placeholder="••••" aria-label="PIN">'
            . ($set ? '<input class="pin" name="pin2" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="off" required placeholder="Repite" aria-label="Repite tu PIN">' : '')
            . '<button class="btn btn-main btn-block">' . ($set ? 'Guardar mi PIN' : 'Entrar') . '</button></form></div>';
        page_end();
        exit;
    }
}

// ---------------------------------------------------------------------------
// Contexto: hoy, o el día anterior si se le olvidó (hasta mediodía)
// ---------------------------------------------------------------------------
$stats = advisor_stats($adv, $office);
$today = $stats['today'];
$ayer = previous_workday_unlogged($stats, $office);
$ayerMode = (($_GET['d'] ?? $_POST['d'] ?? '') === 'ayer') && $ayer;
$date = $ayerMode ? $ayer : $today;
$ctx = $stats['schedule'][$date] ?? null;
$log = $stats['logs'][$date] ?? null;
$goals = $stats['goals'];
$week = $ctx['week'] ?? $stats['week'];
$items = $ctx ? day_items($ctx, [
    'office' => $office, 'sessions' => $stats['sessions'],
    'done_elsewhere' => done_keys($stats['logs'], $date),
    'goal_contactos' => goal_for($goals, $week, 'contactos'),
]) : [];

// ---------------------------------------------------------------------------
// Guardar mi día
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'save' && $ctx) {
    adv_csrf_check($t);
    $nums = [];
    foreach (result_fields() as $f => $_) $nums[$f] = max(0, min(999, (int)($_POST['n'][$f] ?? 0)));
    $note = mb_substr(trim((string)($_POST['note'] ?? '')), 0, 2000);
    $analisis = ($nums['analizadas'] ?? 0) > 0 ? mb_substr(trim((string)($_POST['analisis'] ?? '')), 0, 4000) : '';
    $flashMsg = $ctx['type'] === 'rest' ? '¡Gracias! Quedó anotado. Disfruta tu descanso.' : T('saved');
    $flashType = 'ok';

    db()->beginTransaction();
    $fcols = array_keys($nums);
    q('INSERT INTO daily_logs (advisor_id, log_date, week_no, day_type, day_label, ' . implode(', ', $fcols) . ', analisis, note, created_at, updated_at)
       VALUES (?,?,?,?,?,' . implode(',', array_fill(0, count($fcols), '?')) . ',?,?,NOW(),NOW())
       ON DUPLICATE KEY UPDATE week_no = VALUES(week_no), day_type = VALUES(day_type), day_label = VALUES(day_label), '
        . implode(', ', array_map(fn($c) => "$c = VALUES($c)", $fcols)) . ', analisis = VALUES(analisis), note = VALUES(note), updated_at = NOW()',
        array_merge([$adv['id'], $date, $week, $ctx['type'], ctx_label($ctx)], array_values($nums), [$analisis ?: null, $note ?: null]));
    $logId = (int)val('SELECT id FROM daily_logs WHERE advisor_id = ? AND log_date = ?', [$adv['id'], $date]);
    $done = (array)($_POST['done'] ?? []);
    $res = (array)($_POST['res'] ?? []);
    $reviewAnswers = array_map(fn($x) => mb_substr(trim((string)$x), 0, 2000), (array)($_POST['review'] ?? []));
    $lowAlert = null;

    foreach ($items as $it) {
        $k = $it['key'];
        $txt = mb_substr(trim((string)($res[$k] ?? '')), 0, 2000);
        $isDone = !empty($done[$k]) ? 1 : 0;
        if ($it['kind'] === 'repaso' && array_filter($reviewAnswers)) $isDone = 1;

        if ($it['kind'] === 'metas' && isset($_POST['goals'])) {
            $g = [];
            foreach (result_fields() as $f => $_) $g[$f] = max(0, min(999, (int)($_POST['goals'][$f] ?? 0)));
            $low = goals_below_min($g, $it['week']);
            $accept = !empty($_POST['goals_accept_low']);
            if ($low && !$accept) {
                $flashMsg = 'Guardé tu día, pero tus metas quedaron por debajo del mínimo. Ajústalas o marca «Dejarlas así».';
                $flashType = 'err';
            } else {
                q('INSERT INTO advisor_goals (advisor_id, week_no, contactos, tratos, analizadas, promovidas, citas, captaciones, low_accepted, updated_at)
                   VALUES (?,?,?,?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE contactos = VALUES(contactos), tratos = VALUES(tratos), analizadas = VALUES(analizadas),
                   promovidas = VALUES(promovidas), citas = VALUES(citas), captaciones = VALUES(captaciones), low_accepted = VALUES(low_accepted), updated_at = NOW()',
                    [$adv['id'], $it['week'], $g['contactos'], $g['tratos'], $g['analizadas'], $g['promovidas'], $g['citas'], $g['captaciones'], $low ? 1 : 0]);
                $isDone = 1;
                if ($low) $lowAlert = [$it['week'], $low];
            }
        }

        $photo = null;
        if (!empty($_FILES['photo']['name'][$k])) {
            $f = ['error' => $_FILES['photo']['error'][$k], 'size' => $_FILES['photo']['size'][$k], 'tmp_name' => $_FILES['photo']['tmp_name'][$k]];
            $photo = save_upload_photo($f, (int)$adv['id'], $date, $k);
            if ($photo) $isDone = 1;
        }
        q('INSERT INTO log_items (log_id, item_key, done, result_text, photo) VALUES (?,?,?,?,?)
           ON DUPLICATE KEY UPDATE done = VALUES(done), result_text = VALUES(result_text), photo = COALESCE(VALUES(photo), photo)',
            [$logId, $k, $isDone, $txt !== '' ? $txt : null, $photo]);
        if ($it['kind'] === 'repaso') {
            q('INSERT INTO week_reviews (advisor_id, week_no, answers, updated_at) VALUES (?,?,?,NOW()) ON DUPLICATE KEY UPDATE answers = VALUES(answers), updated_at = NOW()',
                [$adv['id'], $it['week'], json_encode(array_values($reviewAnswers), JSON_UNESCAPED_UNICODE)]);
        }
    }
    if (isset($_POST['logros']) && prog() === 'industrial') {
        $L = (array)$_POST['logros'];
        $d = fn($k) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($L[$k] ?? '')) ? $L[$k] : null;
        $vals = [];
        foreach (['ind_cap', 'ind_oferta', 'ind_inq'] as $k) { $on = !empty($L[$k . '_done']) ? 1 : 0; $vals[] = $on; $vals[] = $on ? ($d($k . '_date') ?? $date) : null; }
        $r = !empty($L['renta_closed']) ? 1 : 0;
        q('UPDATE advisors SET ind_cap_done = ?, ind_cap_date = ?, ind_oferta_done = ?, ind_oferta_date = ?, ind_inq_done = ?, ind_inq_date = ?, renta_closed = ?, renta_date = ? WHERE id = ?',
            array_merge($vals, [$r, $r ? ($d('renta_date') ?? $date) : null, $adv['id']]));
    } elseif (isset($_POST['logros'])) {
        $L = (array)$_POST['logros'];
        $ohDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($L['oh_date'] ?? '')) ? $L['oh_date'] : null;
        $rDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($L['renta_date'] ?? '')) ? $L['renta_date'] : null;
        q('UPDATE advisors SET oh_done = ?, oh_date = ?, oh_attendees = ?, cap_precio = ?, cap_docs = ?, cap_disp = ?, renta_closed = ?, renta_date = ? WHERE id = ?', [
            !empty($L['oh_done']) ? 1 : 0, $ohDate, max(0, min(999, (int)($L['oh_attendees'] ?? 0))),
            !empty($L['cap_precio']) ? 1 : 0, !empty($L['cap_docs']) ? 1 : 0, !empty($L['cap_disp']) ? 1 : 0,
            !empty($L['renta_closed']) ? 1 : 0, $rDate, $adv['id'],
        ]);
    }
    db()->commit();

    if ($lowAlert && notice_once('metas_bajas:' . $adv['id'] . ':' . $lowAlert[0])) {
        send_low_goals_alert($adv, $office, $lowAlert[0], $lowAlert[1]);
    }
    if ($flashType === 'ok' && $ctx['type'] !== 'rest') {
        // Ariadna celebra; si cerró su repaso semanal, celebra la semana.
        $closedWeek = false;
        foreach ($items as $it) if ($it['kind'] === 'repaso' && (!empty($done[$it['key']]) || array_filter($reviewAnswers))) $closedWeek = true;
        $_SESSION['celebrate'] = $closedWeek
            ? sprintf(T('week_closed'), (int)$week)
            : $flashMsg;
    } else {
        flash($flashMsg, $flashType);
    }
    redirect($self . ($flashType === 'err' && $ayerMode ? '&d=ayer' : '') . (isset($_SESSION['celebrate']) ? '' : '#hoy'));
}

// ---------------------------------------------------------------------------
// Vista
// ---------------------------------------------------------------------------
$celebrate = $_SESSION['celebrate'] ?? null;
unset($_SESSION['celebrate']);
page_start('Hoy', $opts);
render_hoy([
    'celebrate' => $celebrate,
    'adv' => $adv, 'office' => $office, 'token' => $t, 'preview' => false,
    'date' => $date, 'today' => $today, 'ayer_mode' => $ayerMode, 'ayer_offer' => (!$ayerMode && $ayer) ? ['date' => $ayer] : null,
    'ctx' => $ctx ?? ['type' => 'rest', 'week' => $stats['week'], 'date' => $date, 'days' => [], 'day_no' => 0, 'arranque' => null],
    'items' => $items, 'log' => $log, 'stats' => $stats, 'goals' => $goals, 'sessions' => $stats['sessions'],
]);
page_end();

/** Día hábil anterior sin registrar, si aún estamos antes de la hora límite (mediodía). */
function previous_workday_unlogged(array $stats, array $office): ?string
{
    if (!$stats['started']) return null;
    if ((int)office_now($office)->format('G') >= (int)setting('ayer_until_hour')) return null;
    for ($d = add_days($stats['today'], -1), $i = 0; $i < 7; $d = add_days($d, -1), $i++) {
        $ctx = $stats['schedule'][$d] ?? null;
        if (!$ctx) return null;
        if ($ctx['type'] === 'rest') continue;
        return isset($stats['logs'][$d]) ? null : $d;
    }
    return null;
}
