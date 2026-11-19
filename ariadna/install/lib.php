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
    $retos = require __DIR__ . '/retos_seed.php';
    foreach ($plan as $w => $p) {
        q('INSERT INTO plan_weeks (week_no, title, objective, material, remember, review_questions, routine_start_day,
            meta_contactos, meta_tratos, meta_analizadas, meta_promovidas, meta_citas) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)', [
            $w, $p['title'], $p['objective'], $p['material'], $p['remember'], $p['questions'], $p['routine_start_day'],
            $p['metas']['contactos'], $p['metas']['tratos'], $p['metas']['analizadas'], $p['metas']['promovidas'], $p['metas']['citas'],
        ]);
        $sort = [];
        foreach ($p['acts'] as $a) {
            $d = (int)$a['d'];
            $sort[$d] = ($sort[$d] ?? 0) + 10;
            $help = '';
            foreach ($a['h'] ?? [] as [$t, $u]) $help .= $t . ' | ' . $u . "\n";
            q('INSERT INTO plan_activities (week_no, day_no, sort, title, guide, minutes, kind, video_url, help_links, reto) VALUES (?,?,?,?,?,?,?,?,?,?)',
                [$w, $d, $sort[$d], $a['t'], $a['g'] ?? '', $a['m'] ?? null, $a['k'], $a['v'] ?? null, trim($help) ?: null, $a['k'] === 'video' ? ($retos[$a['t']] ?? null) : null]);
        }
    }
    if (val("SELECT v FROM settings WHERE k = 'routine_items'") === null) {
        set_setting('routine_items', setting_defaults()['routine_items']);
    }
}

/** Datos de prueba: 2 oficinas, 6 asesores en semanas distintas. */
function seed_demo(): array
{
    mt_srand(20261117);
    $pass = 'Demo2026!';
    q("INSERT INTO offices (name, city, timezone, workdays, pin_enabled, training_weekday, oh_mode) VALUES ('Oficina Demo Cancún', 'Cancún', 'America/Cancun', '1,2,3,4,5', 0, 4, 'open_house')");
    $o1 = one('SELECT * FROM offices WHERE id = ?', [db()->lastInsertId()]);
    q("INSERT INTO offices (name, city, timezone, workdays, pin_enabled, training_weekday, oh_mode) VALUES ('Oficina Demo Mérida', 'Mérida', 'America/Merida', '1,2,3,4,5,6', 1, NULL, 'recorrido')");
    $o2 = one('SELECT * FROM offices WHERE id = ?', [db()->lastInsertId()]);

    $users = [
        [$o1['id'], 'Laura Méndez', 'director.cancun@demo.mx', 'director'],
        [$o1['id'], 'Ricardo Salas', 'gerente.cancun@demo.mx', 'gerente'],
        [$o1['id'], 'Sofía Cano', 'tl.cancun@demo.mx', 'team_leader'],
        [$o2['id'], 'Jorge Peniche', 'director.merida@demo.mx', 'director'],
        [$o2['id'], 'Mariana Canché', 'tl.merida@demo.mx', 'team_leader'],
    ];
    foreach ($users as [$oid, $n, $e, $r]) create_user((int)$oid, $n, $e, $pass, $r);

    // Sesiones en la oficina (se repiten cada semana). Dos temas se dejan sin fecha a propósito.
    $acts = [];
    foreach (office_activities() as $a) $acts[$a['title']] = (int)$a['id'];
    $base = add_days(office_today($o1), -70);
    $weekday = function (string $from, int $n): string { return add_days($from, ($n - iso_weekday($from) + 7) % 7); };
    $plan1 = [
        ['Bienvenida e introducción al negocio', 1, '09:00', 'Sofía Cano (Team Leader)', 'Sala de juntas'],
        ['Llamadas a tu Impulsor de Memoria (acompañado)', 3, '10:00', 'Sofía Cano (Team Leader)', 'Sala de juntas'],
        ['Documentación de la propiedad e impuestos', 4, '10:00', 'Ricardo Salas (gerente)', 'Sala de juntas'],
        ['Inventario de la oficina', 1, '12:00', 'Sofía Cano (Team Leader)', ''],
        ['Procesos de la oficina', 3, '16:00', 'Ricardo Salas (gerente)', ''],
        ['Llamadas a tus tratos directos (acompañado)', 4, '11:00', 'Sofía Cano (Team Leader)', ''],
        ['Reforzamiento en lo que más necesites', 5, '10:00', 'Sofía Cano (Team Leader)', ''],
        ['Marco legal y antilavado', 2, '10:00', 'Ricardo Salas (gerente)', 'Sala de juntas'],
        ['Llamadas a prospectos y tratos pendientes (acompañado)', 3, '11:00', 'Sofía Cano (Team Leader)', ''],
        ['Simulacro de venta', 5, '12:00', 'Ricardo Salas (gerente)', 'Sala de juntas'],
    ];
    foreach ($plan1 as [$t, $dow, $time, $who, $place]) {
        if (!isset($acts[$t])) continue;
        q('INSERT INTO office_sessions (office_id, activity_id, sdate, stime, who, place, repeat_weekly) VALUES (?,?,?,?,?,?,1)',
            [$o1['id'], $acts[$t], $weekday($base, $dow), $time, $who, $place ?: null]);
    }
    foreach ([['Bienvenida e introducción al negocio', 1, '10:00'], ['Llamadas a tu Impulsor de Memoria (acompañado)', 3, '10:00'],
              ['Documentación de personas físicas y morales', 2, '10:00'], ['Formatos internos de la oficina', 2, '16:00']] as [$t, $dow, $time]) {
        if (!isset($acts[$t])) continue;
        q('INSERT INTO office_sessions (office_id, activity_id, sdate, stime, who, place, repeat_weekly) VALUES (?,?,?,?,?,?,1)',
            [$o2['id'], $acts[$t], $weekday($base, $dow), $time, 'Mariana Canché (Team Leader)', null]);
    }

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
        $email = strtolower(str_replace(' ', '.', iconv('UTF-8', 'ASCII//TRANSLIT', $name))) . '@demo.mx';
        $id = create_advisor($office, $name, $phone, $email, $start);
        q('UPDATE advisors SET consent_at = NOW(), consent_ip = ? WHERE id = ?', ['demo', $id]);
        $adv = one('SELECT * FROM advisors WHERE id = ?', [$id]);
        demo_goals($adv, $profile, $ago);
        demo_logs($adv, $office, $profile, $today);
        if ($profile === 'estrella') q('UPDATE advisors SET oh_done = 1, oh_date = ?, oh_attendees = 7, cap_precio = 1, cap_docs = 1, cap_disp = 1 WHERE id = ?', [add_days($start, 18), $id]);
        if ($profile === 'constante' && $ago > 14) q('UPDATE advisors SET oh_done = 1, oh_date = ?, oh_attendees = 5 WHERE id = ?', [add_days($start, 17), $id]);
        if ($profile === 'sin_citas') q('UPDATE advisors SET oh_done = 1, oh_date = ?, oh_attendees = 3 WHERE id = ?', [add_days($start, 18), $id]);
        $adv = one('SELECT * FROM advisors WHERE id = ?', [$id]);
        $st = advisor_stats($adv, $office);
        q('UPDATE advisors SET status_color = ?, status_reason = ? WHERE id = ?', [$st['status']['color'], $st['status']['reason'], $id]);
        foreach ($st['low_goals'] as $w => $_) notice_once('metas_bajas:' . $id . ':' . $w);
        $created[] = ['name' => $name, 'link' => advisor_link($adv), 'office' => $office['name'], 'week' => $st['week']];
    }
    // Retos del video de ejemplo: los videos que ya pasaron, con resultados variados (el último, pendiente).
    ensure_schema();
    foreach (all('SELECT a.*, o.timezone FROM advisors a JOIN offices o ON o.id = a.office_id') as $adv) {
        $office = one('SELECT * FROM offices WHERE id = ?', [$adv['office_id']]);
        $past = array_values(array_filter(advisor_stats($adv, $office)['retos']['list'], fn($r) => $r['date'] && $r['date'] < office_today($office)));
        array_pop($past);
        foreach ($past as $i => $r) {
            $ans = ['ooooo', 'ooroo', 'ororo', 'oooor', 'ooooo'][($adv['id'] + $i) % 5];
            q('INSERT INTO advisor_retos (advisor_id, activity_id, answers, ok_count, attempt, note, user_id, user_name, created_at, updated_at) VALUES (?,?,?,?,1,?,NULL,?,NOW(),NOW())',
                [$adv['id'], $r['activity']['id'], $ans, substr_count($ans, 'o'), substr_count($ans, 'o') < 4 ? 'Repasa la parte que marqué y lo platicamos en tu cierre.' : null, 'Sofía Cano']);
        }
    }

    return ['password' => $pass, 'users' => $users, 'advisors' => $created];
}

function demo_goals(array $adv, string $profile, int $ago): void
{
    $weeks = min(prog_weeks(), intdiv($ago, 7) + 1);
    for ($w = 2; $w <= $weeks; $w++) {
        $g = [];
        foreach (array_keys(result_fields()) as $f) $g[$f] = goal_minimum($w, $f);
        $low = 0;
        if ($profile === 'constante') { $g['contactos'] = 25; $g['citas'] = max(1, $g['citas']); $g['captaciones'] = 1; }
        if ($profile === 'estrella') { $g['contactos'] = 30; $g['citas'] += 2; $g['captaciones'] = 1; }
        if ($profile === 'se_detuvo') { $g['contactos'] = 8; $g['promovidas'] = 2; $low = 1; }
        q('INSERT INTO advisor_goals (advisor_id, week_no, contactos, tratos, analizadas, promovidas, citas, captaciones, low_accepted, updated_at)
           VALUES (?,?,?,?,?,?,?,?,?,NOW())', [$adv['id'], $w, $g['contactos'], $g['tratos'], $g['analizadas'], $g['promovidas'], $g['citas'], $g['captaciones'], $low]);
    }
}

function demo_logs(array $adv, array $office, string $profile, string $today): void
{
    $schedule = build_schedule($adv, $office);
    $sessions = advisor_sessions($adv, $office);
    $goals = advisor_goals((int)$adv['id']);
    $stopAfter = null;
    if ($profile === 'se_detuvo') {
        $wd = array_keys(array_filter($schedule, fn($c) => $c['type'] !== 'rest' && $c['date'] < $today));
        $stopAfter = $wd[count($wd) - 4] ?? null;
    }
    foreach ($schedule as $date => $ctx) {
        if ($date >= $today) break;
        if ($profile === 'tarde' && $ctx['week'] === 1) continue;
        if ($stopAfter && $date > $stopAfter) continue;
        if ($ctx['type'] === 'rest') {
            if ($profile === 'estrella' && mt_rand(1, 3) === 1) demo_insert_log($adv['id'], $date, $ctx, ['contactos' => 1], [], 'Mi vecina quiere rentar su departamento.', '');
            continue;
        }
        if ($profile === 'sin_citas' && mt_rand(1, 5) === 1) continue;
        if ($profile === 'tarde' && mt_rand(1, 3) === 1) continue;
        $items = day_items($ctx, ['office' => $office, 'sessions' => $sessions, 'done_elsewhere' => done_keys(advisor_logs((int)$adv['id'])),
            'goal_contactos' => goal_for($goals, $ctx['week'], 'contactos')]);
        $doneP = ['constante' => 88, 'se_detuvo' => 75, 'sin_citas' => 65, 'estrella' => 95, 'tarde' => 55][$profile] ?? 70;
        $doneItems = [];
        $n = array_fill_keys(array_keys(result_fields()), 0);
        foreach ($items as $it) {
            $force = $it['routine'] && in_array($profile, ['constante', 'estrella', 'se_detuvo'], true);
            if ($it['pending_session']) continue;
            if (!$force && mt_rand(1, 100) > $doneP) continue;
            $doneItems[$it['key']] = '';
            if ($it['key'] === 'R1') {
                $n['contactos'] += $ctx['arranque'] ? mt_rand(17, 22) : ($profile === 'estrella' ? mt_rand(26, 32) : ($profile === 'se_detuvo' ? mt_rand(6, 10) : mt_rand(19, 27)));
            }
            if (preg_match('/^Analiza (\d+)/u', $it['title'], $m)) $n['analizadas'] += (int)$m[1];
            if (preg_match('/^(?:Publica|Promueve) (?:tus )?(\d+) propiedades/u', $it['title'], $m)) $n['promovidas'] += (int)$m[1];
            if (str_contains($it['title'], 'tratos directos') || str_contains($it['title'], 'trato directo') || str_contains($it['title'], 'propietario')) $n['tratos'] += mt_rand(1, 3);
            if (str_starts_with($it['title'], 'Agenda tu primera cita') || str_starts_with($it['title'], 'Agenda tus citas')) $n['citas'] += 1;
            if ($it['kind'] === 'agenda') $doneItems[$it['key']] = 'Bloques de 9:00 a 11:00 y de 16:00 a 18:00.';
            if ($it['kind'] === 'oficina') $doneItems[$it['key']] = 'Me quedó claro; tengo que pedir la lista de documentos.';
        }
        if ($profile === 'sin_citas') { $n['citas'] = 0; $n['captaciones'] = 0; }
        if ($profile === 'estrella' && $ctx['week'] === 4 && $ctx['day_no'] === 4) { $n['captaciones'] = 1; $n['citas'] += 1; }
        if ($profile === 'estrella' && $ctx['week'] >= 4) $n['citas'] += mt_rand(0, 1);
        $analisis = $n['analizadas'] > 0 ? 'Casa en Región 15, 3 recámaras. Piden $2.8 M; creo que vale $2.5 M porque tres casas parecidas en la zona se ofrecen entre $2.4 y $2.6 M.' : '';
        demo_insert_log($adv['id'], $date, $ctx, $n, $doneItems, '', $analisis);
        if ($ctx['type'] === 'plan' && in_array(PLAN_DAYS_PER_WEEK, $ctx['days'], true)) {
            $qs = array_filter(explode("\n", (string)(plan_weeks()[$ctx['week']]['review_questions'] ?? '')));
            q('INSERT INTO week_reviews (advisor_id, week_no, answers, updated_at) VALUES (?,?,?,NOW()) ON DUPLICATE KEY UPDATE answers = VALUES(answers)',
                [$adv['id'], $ctx['week'], json_encode(array_values(array_map(fn($x) => 'Respuesta de prueba.', $qs)), JSON_UNESCAPED_UNICODE)]);
        }
    }
}

function demo_insert_log(int $advId, string $date, array $ctx, array $n, array $items, string $note, string $analisis): void
{
    $n = array_merge(array_fill_keys(array_keys(result_fields()), 0), $n);
    q('INSERT INTO daily_logs (advisor_id, log_date, week_no, day_type, day_label, contactos, tratos, analizadas, promovidas, citas, captaciones, analisis, note, created_at, updated_at)
       VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
        $advId, $date, $ctx['week'], $ctx['type'], ctx_label($ctx),
        $n['contactos'], $n['tratos'], $n['analizadas'], $n['promovidas'], $n['citas'], $n['captaciones'], $analisis ?: null, $note ?: null,
        $date . ' 19:00:00', $date . ' 19:00:00',
    ]);
    $logId = (int)db()->lastInsertId();
    foreach ($items as $k => $txt) q('INSERT INTO log_items (log_id, item_key, done, result_text) VALUES (?,?,1,?)', [$logId, $k, $txt ?: null]);
}
