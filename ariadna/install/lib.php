<?php
/**
 * ARIADNA · Funciones de instalación: tablas, plan inicial y datos de prueba.
 */
declare(strict_types=1);

function install_schema(): void
{
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $sql = preg_replace('/^--.*$/m', '', $sql);
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) db()->exec($stmt);
}

function seed_plan(bool $replace = false): void
{
    if (!$replace && (int)val('SELECT COUNT(*) FROM plan_weeks') > 0) return;
    q('DELETE FROM plan_activities');
    q('DELETE FROM plan_weeks');
    $plan = require __DIR__ . '/plan_seed.php';
    foreach ($plan as $w => $p) {
        q('INSERT INTO plan_weeks (week_no, title, objective, material, remember, review_questions, routine_start_day,
            meta_contactos, meta_tratos, meta_analizadas, meta_promovidas, meta_citas) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)', [
            $w, $p['title'], $p['objective'], $p['material'], $p['remember'], $p['questions'], $p['routine_start_day'],
            $p['metas']['contactos'], $p['metas']['tratos'], $p['metas']['analizadas'], $p['metas']['promovidas'], $p['metas']['citas'],
        ]);
        $sort = [];
        foreach ($p['acts'] as [$day, $title, $guide, $min, $kind]) {
            $sort[$day] = ($sort[$day] ?? 0) + 10;
            q('INSERT INTO plan_activities (week_no, day_no, sort, title, guide, minutes, kind) VALUES (?,?,?,?,?,?,?)',
                [$w, $day, $sort[$day], $title, $guide, $min, $kind]);
        }
    }
    if (val("SELECT v FROM settings WHERE k = 'routine_items'") === null) {
        set_setting('routine_items', setting_defaults()['routine_items']);
    }
}

/** Datos de prueba: 2 oficinas, 6 asesores en semanas distintas. */
function seed_demo(): array
{
    mt_srand(20261116);
    $pass = 'Demo2026!';
    q("INSERT INTO offices (name, city, timezone, workdays, pin_enabled, training_weekday) VALUES ('Oficina Demo Cancún', 'Cancún', 'America/Cancun', '1,2,3,4,5', 0, 4)");
    $o1 = one('SELECT * FROM offices WHERE id = ?', [db()->lastInsertId()]);
    q("INSERT INTO offices (name, city, timezone, workdays, pin_enabled, training_weekday) VALUES ('Oficina Demo Mérida', 'Mérida', 'America/Merida', '1,2,3,4,5,6', 1, NULL)");
    $o2 = one('SELECT * FROM offices WHERE id = ?', [db()->lastInsertId()]);

    $users = [
        [$o1['id'], 'Laura Méndez', 'director.cancun@demo.mx', 'director'],
        [$o1['id'], 'Ricardo Salas', 'gerente.cancun@demo.mx', 'gerente'],
        [$o1['id'], 'Sofía Cano', 'tl.cancun@demo.mx', 'team_leader'],
        [$o2['id'], 'Jorge Peniche', 'director.merida@demo.mx', 'director'],
        [$o2['id'], 'Mariana Canché', 'tl.merida@demo.mx', 'team_leader'],
    ];
    foreach ($users as [$oid, $n, $e, $r]) create_user((int)$oid, $n, $e, $pass, $r);

    $today1 = office_today($o1);
    $today2 = office_today($o2);
    // [oficina, nombre, celular, días desde el inicio, perfil]
    $people = [
        [$o1, 'Ana López',      '9981234501', 2,  'constante'],
        [$o1, 'Bruno Pérez',    '9981234502', 10, 'se_detuvo'],
        [$o1, 'Carla Ruiz',     '9981234503', 16, 'constante'],
        [$o1, 'Diego Torres',   '9981234504', 23, 'sin_citas'],
        [$o2, 'Elena Gómez',    '9991234505', 31, 'estrella'],
        [$o2, 'Fernando Díaz',  '9991234506', 8,  'tarde'],
    ];
    $created = [];
    foreach ($people as [$office, $name, $phone, $ago, $profile]) {
        $today = office_today($office);
        $start = add_days($today, -$ago);
        $id = create_advisor($office, $name, $phone, strtolower(str_replace(' ', '.', iconv('UTF-8', 'ASCII//TRANSLIT', $name))) . '@demo.mx', $start);
        q('UPDATE advisors SET consent_at = NOW(), consent_ip = ? WHERE id = ?', ['demo', $id]);
        $adv = one('SELECT * FROM advisors WHERE id = ?', [$id]);
        demo_logs($adv, $office, $profile, $today);
        if ($profile === 'estrella') {
            q('UPDATE advisors SET oh_done = 1, oh_date = ?, oh_attendees = 7, cap_precio = 1, cap_docs = 1, cap_disp = 1 WHERE id = ?', [add_days($start, 18), $id]);
        }
        if ($profile === 'constante' && $ago > 14) {
            q('UPDATE advisors SET oh_done = 1, oh_date = ?, oh_attendees = 5 WHERE id = ?', [add_days($start, 17), $id]);
        }
        if ($profile === 'sin_citas') {
            q('UPDATE advisors SET oh_done = 1, oh_date = ?, oh_attendees = 3 WHERE id = ?', [add_days($start, 18), $id]);
        }
        $adv = one('SELECT * FROM advisors WHERE id = ?', [$id]);
        $st = advisor_stats($adv, $office);
        q('UPDATE advisors SET status_color = ?, status_reason = ? WHERE id = ?', [$st['status']['color'], $st['status']['reason'], $id]);
        $created[] = ['name' => $name, 'link' => advisor_link($adv), 'office' => $office['name'], 'week' => $st['week']];
    }
    return ['password' => $pass, 'users' => $users, 'advisors' => $created];
}

function demo_logs(array $adv, array $office, string $profile, string $today): void
{
    $schedule = build_schedule($adv, $office);
    $workSeen = 0;
    $stopAfter = null;
    if ($profile === 'se_detuvo') {
        // Deja de registrar en sus últimos 3 días laborables.
        $wd = array_keys(array_filter($schedule, fn($c) => $c['type'] !== 'rest' && $c['date'] < $today));
        $stopAfter = $wd[count($wd) - 4] ?? null;
    }
    foreach ($schedule as $date => $ctx) {
        if ($date >= $today) break;
        if ($profile === 'tarde' && $ctx['week'] === 1) continue;           // no registró nada en su Semana 1
        if ($stopAfter && $date > $stopAfter) continue;
        if ($ctx['type'] === 'rest') {
            if ($profile === 'estrella' && mt_rand(1, 3) === 1) {
                demo_insert_log($adv['id'], $date, $ctx, ['contactos' => 1], [], 'Mi vecina quiere rentar su departamento.');
            }
            continue;
        }
        $workSeen++;
        if ($profile === 'sin_citas' && mt_rand(1, 5) === 1) continue;
        if ($profile === 'tarde' && mt_rand(1, 3) === 1) continue;
        $items = ctx_items($ctx, week_done_keys(advisor_logs((int)$adv['id']), $schedule, $ctx['week']));
        $doneP = ['constante' => 85, 'se_detuvo' => 75, 'sin_citas' => 65, 'estrella' => 95, 'tarde' => 55][$profile] ?? 70;
        $doneItems = [];
        $n = array_fill_keys(array_keys(RESULT_FIELDS), 0);
        foreach ($items as $it) {
            $force = $it['routine'] && in_array($profile, ['constante', 'estrella', 'se_detuvo'], true);
            if (!$force && mt_rand(1, 100) > $doneP) continue;
            $doneItems[$it['key']] = '';
            if ($it['key'] === 'R1') $n['contactos'] += mt_rand($profile === 'constante' ? 3 : 2, $profile === 'constante' ? 5 : ($profile === 'estrella' ? 4 : 3));
            if (preg_match('/^Analiza (\d+)/u', $it['title'], $m)) $n['analizadas'] += (int)$m[1];
            if (preg_match('/^Elige (\d+) propiedades/u', $it['title'], $m)) $n['promovidas'] += (int)$m[1];
            if (preg_match('/^Promueve (\d+)/u', $it['title'], $m)) $n['promovidas'] += (int)$m[1];
            if (str_contains($it['title'], 'tratos directos') || str_contains($it['title'], 'trato directo')) $n['tratos'] += mt_rand(1, 3);
            if (str_starts_with($it['title'], 'Agenda tu primera cita') || str_starts_with($it['title'], 'Agenda tus citas')) $n['citas'] += 1;
            if ($it['kind'] === 'agenda') $doneItems[$it['key']] = 'Bloques de 9:00 a 11:00 y de 16:00 a 18:00.';
        }
        if ($profile === 'sin_citas') { $n['citas'] = 0; $n['captaciones'] = 0; }
        if ($profile === 'estrella' && $ctx['week'] === 4 && $ctx['day_no'] === 4) { $n['captaciones'] = 1; $n['citas'] += 1; }
        if ($profile === 'estrella' && $ctx['week'] >= 4) $n['citas'] += mt_rand(0, 1);
        demo_insert_log($adv['id'], $date, $ctx, $n, $doneItems, '');
        if ($ctx['type'] === 'plan' && in_array(PLAN_DAYS_PER_WEEK, $ctx['days'], true)) {
            $qs = array_filter(explode("\n", (string)(plan_weeks()[$ctx['week']]['review_questions'] ?? '')));
            $ans = array_map(fn($x) => 'Respuesta de prueba.', $qs);
            q('INSERT INTO week_reviews (advisor_id, week_no, answers, updated_at) VALUES (?,?,?,NOW()) ON DUPLICATE KEY UPDATE answers = VALUES(answers)',
                [$adv['id'], $ctx['week'], json_encode(array_values($ans), JSON_UNESCAPED_UNICODE)]);
        }
    }
}

function demo_insert_log(int $advId, string $date, array $ctx, array $n, array $items, string $note): void
{
    $n = array_merge(array_fill_keys(array_keys(RESULT_FIELDS), 0), $n);
    q('INSERT INTO daily_logs (advisor_id, log_date, week_no, day_type, day_label, contactos, tratos, analizadas, promovidas, citas, captaciones, note, created_at, updated_at)
       VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
        $advId, $date, $ctx['week'], $ctx['type'], ctx_label($ctx),
        $n['contactos'], $n['tratos'], $n['analizadas'], $n['promovidas'], $n['citas'], $n['captaciones'], $note,
        $date . ' 19:00:00', $date . ' 19:00:00',
    ]);
    $logId = (int)db()->lastInsertId();
    foreach ($items as $k => $txt) q('INSERT INTO log_items (log_id, item_key, done, result_text) VALUES (?,?,1,?)', [$logId, $k, $txt ?: null]);
}
