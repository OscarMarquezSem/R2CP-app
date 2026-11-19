<?php
/**
 * ARIADNA · Pantalla «Hoy» del asesor (la usan hoy.php y la vista previa del administrador).
 */
declare(strict_types=1);

function ariadna_bubble(string $html): string
{
    return '<div class="bubble"><img class="bubble-av" style="width:48px;height:48px;border-radius:50%;object-fit:cover;flex:0 0 48px" src="' . h(prog_img('avatar')) . '" alt="" width="48" height="48"><p><b>' . h(P()['name']) . ':</b> ' . $html . '</p></div>';
}

function advisor_header(string $dateLabel): void
{
    echo '<div class="adv-head">' . brand_mark() . '<span class="adv-head-r"><b>R²CP ELITE</b><span>' . h($dateLabel) . '</span></span></div>';
}

/** Mensaje de Ariadna según el día. */
function ariadna_message(array $ctx, array $items, ?array $office): string
{
    if ($ctx['type'] === 'training') return 'Hoy te toca tu capacitación interna en la oficina. Pon mucha atención a sus políticas y documentos. Mañana seguimos juntos.';
    if ($ctx['type'] === 'catchup') return 'Hoy es un buen día para ponerte al corriente con lo que te quedó pendiente de la semana.';
    foreach ($items as $it) if ($it['kind'] === 'tl' && strpos($it['title'], 'Reto ') === 0) return 'Hoy tienes tu ' . h(str_replace(' con tu Team Leader', '', $it['title'])) . ' con tu Team Leader. ' . T('msg_reto');
    foreach ($items as $it) if ($it['kind'] === 'tl') return 'Hoy te espera tu Team Leader en la oficina. Yo te acompaño todos los días, pero tu equipo está allá: no faltes. ' . T('msg_first');
    foreach ($items as $it) if ($it['kind'] === 'oficina' && $it['session']) return 'Hoy tienes un tema con tu oficina. Lleva libreta y pregunta todo lo que no te quede claro. ' . T('msg_first');
    if (!empty($ctx['arranque'])) return 'Hoy tus ' . h((string)setting('arranque_llamadas')) . ' llamadas van primero. Cada llamada te quita un poco de miedo: haz de esto un hábito.';
    if (($ctx['days'][0] ?? 0) === 1) {
        $pw = plan_weeks()[$ctx['week']] ?? null;
        return 'Empezamos tu Semana ' . (int)$ctx['week'] . ($pw ? ': ' . h(mb_strtolower(oh_text($pw['title'], $office))) : '') . '. ' . T('msg_week');
    }
    return T('msg_default');
}

/**
 * $V: adv, office, token, preview(bool), preview_nav(html), date, today, ayer_mode(bool), ayer_offer(?array),
 *     ctx, items, log, stats, goals, sessions
 */
function render_hoy(array $V): void
{
    $adv = $V['adv']; $office = $V['office']; $ctx = $V['ctx']; $items = $V['items']; $log = $V['log'];
    $stats = $V['stats']; $goals = $V['goals']; $preview = !empty($V['preview']);
    $week = $ctx['week'] ?? $stats['week'];
    $pw = plan_weeks()[$week] ?? null;
    $name = first_name($adv['name']);
    $t = $V['token'] ?? '';

    advisor_header(fecha_larga($V['date']));
    if (!empty($V['preview_nav'])) echo $V['preview_nav'];

    echo '<header class="hero"><h1>' . (!empty($V['ayer_offer']) ? '¡Buen día, ' . h($name) . '!' : 'Hola, ' . h($name)) . '</h1>';
    if (!$stats['started']) echo '<p class="hero-sub">Tu curso empieza el ' . h(fecha_larga($adv['start_date'])) . '.</p>';
    elseif ($stats['finished'] && empty($V['ayer_mode'])) echo '<p class="hero-sub">Terminaste el curso ' . h(P()['course']) . '.</p>';
    elseif (!empty($V['ayer_mode'])) echo '<p class="hero-sub">Registrando ' . h(fecha_larga($V['date'])) . ' · Semana ' . (int)$week . ' · ' . h(ctx_label($ctx)) . '</p>';
    else echo '<p class="hero-sub">' . (!empty($ctx['repeat']) ? 'Repites tu Semana ' : 'Semana ') . (int)$week . ' · ' . h(ctx_label($ctx)) . '</p>';
    echo '<svg class="thread" viewBox="0 0 400 30" preserveAspectRatio="none" aria-hidden="true"><path d="M0 20 C 60 0, 110 30, 170 14 S 280 4, 330 18 S 390 22, 400 10" fill="none" stroke="#B89860" stroke-width="2"/></svg></header>';

    if (!empty($V['celebrate'])) {
        echo '<div class="card celebrate"><img style="display:block;width:70%;max-width:280px;height:auto;border-radius:50%;margin:0 auto 10px" src="' . h(prog_img('celebra')) . '" alt="' . h(T('celebrate_alt')) . '" width="600" height="600"><p>' . h($V['celebrate']) . '</p></div>';
    }
    if (!empty($V['ayer_offer'])) {
        $o = $V['ayer_offer'];
        echo '<div class="card ayer">' . ariadna_bubble('<b>Ayer no me contaste cómo te fue.</b> ¿Lo registramos rápido antes de empezar tu día?')
            . '<a class="btn btn-main btn-block" href="hoy.php?t=' . h($t) . '&amp;d=ayer">Registrar ' . h(DIAS[iso_weekday($o['date'])] . ' ' . (int)substr($o['date'], 8, 2)) . '</a>'
            . '<a class="btn btn-block" href="#hoy">Ir a mi día de hoy</a>'
            . '<p class="small muted center">Puedes registrar ese día hasta las ' . (int)setting('ayer_until_hour') . ':00 de hoy.</p></div>';
    }
    if (!empty($V['ayer_mode'])) echo '<div class="flash flash-ok">Estás registrando el día anterior. <a href="hoy.php?t=' . h($t) . '">Volver a hoy</a></div>';

    if (!$stats['started']) { echo '<div class="card"><p>Cuando llegue tu primer día, aquí verás tus actividades. Guarda este enlace en tu pantalla de inicio.</p></div>'; install_hint(); return; }
    if ($stats['finished'] && empty($V['ayer_mode'])) {
        $ev = $stats['evaluation'];
        echo '<div class="card"><img class="welcome-img" style="display:block;width:100%;max-width:100%;height:auto" src="' . h(prog_img('graduacion')) . '" alt="' . h(P()['name']) . ' te felicita por terminar el curso" width="600" height="600">'
            . ariadna_bubble('<b>' . h(T('grad_title')) . '</b> ' . h(T('grad_text')))
            . '<h2>Tu evaluación final: ' . (int)$ev['total'] . ' puntos</h2><p class="lead">' . h($ev['level_text']) . '</p>';
        echo '<div class="tbl-wrap">';
        eval_table($ev);
        echo '</div>';
        echo '<p>' . h(T('grad_end')) . '</p></div>';
        return;
    }

    echo '<a id="hoy"></a>';
    if ($ctx['type'] !== 'rest') echo ariadna_bubble(ariadna_message($ctx, $items, $office));
    if (!empty($stats['filtro']) && $stats['filtro']['repeating'] && !$preview) filtro_box($stats['filtro']);
    if (!$preview && !$stats['finished'] && $stats['closed_weeks'] >= 3 && in_array($adv['decision'] ?? null, [null, '', 'pendiente'], true)) checkpoint_box($adv, $stats, $office);

    $pd = max(1, (int)($stats['program_days'] ?? 35)); $programPct = (int)round(min($pd, max(0, $stats['elapsed'] + 1)) / $pd * 100);
    $weekPct = !empty($stats['week_assigned'][$week]) ? (int)round(min(1, $stats['week_done'][$week] / $stats['week_assigned'][$week]) * 100) : 0;
    echo '<section class="progress card"><div class="prog-row"><span>Tu semana</span><b>' . $weekPct . '%</b></div>' . bar($weekPct)
        . '<div class="prog-row"><span>' . h(P()['course']) . ' · Semana ' . (int)$week . ' de ' . prog_weeks() . '</span><b>' . $programPct . '%</b></div>' . bar($programPct, 'gold') . '</section>';

    if (!$preview) {
        echo '<div class="card push-card" id="pushCard" hidden><p><b>¿Te aviso por aquí?</b> Te mando un aviso en la mañana con lo que te toca y otro a las 8 pm solo si no me has contado cómo te fue.</p>'
            . '<button type="button" class="btn btn-block" id="pushBtn" data-key="' . h(vapid_public_key()) . '" data-t="' . h($t) . '">' . h(T('push_btn')) . '</button></div>';
    }

    echo '<form method="post" enctype="multipart/form-data" class="dayform" id="dayform"' . ($preview ? ' data-preview="1"' : '') . '>';
    echo csrf_field() . '<input type="hidden" name="t" value="' . h($t) . '"><input type="hidden" name="action" value="save">';
    if (!empty($V['ayer_mode'])) echo '<input type="hidden" name="d" value="ayer">';

    if ($ctx['type'] === 'rest') {
        echo '<div class="card rest"><img class="welcome-img" style="display:block;width:100%;max-width:100%;height:auto" src="' . h(prog_img('descanso')) . '" alt="' . h(P()['name']) . ' descansando" width="600" height="600"><h2>' . h(T('rest_title')) . '</h2><p>' . h(T('rest_text')) . '</p>';
        $rf = T('rest_field');
        num_field($rf, result_fields()[$rf], (int)($log[$rf] ?? 0), T('rest_hint'), '');
        foreach (array_keys(result_fields()) as $f) if ($f !== $rf) echo '<input type="hidden" name="n[' . $f . ']" value="' . (int)($log[$f] ?? 0) . '">';
        echo '<label class="lbl">¿Quién es y qué busca? (opcional)<textarea name="note" rows="3" placeholder="' . h(T('rest_ph')) . '">' . h($log['note'] ?? '') . '</textarea></label>';
        echo '<button class="btn btn-main btn-block"' . ($preview ? ' disabled' : '') . '>Guardar</button></div></form>';
        install_hint();
        return;
    }

    if ($pw && $ctx['type'] !== 'training') {
        echo '<div class="box objective"><span class="eyebrow">' . h(P()['course']) . ' · Semana ' . (int)$week . ' · ' . h(oh_text($pw['title'], $office)) . '</span><p>' . h(oh_text($pw['objective'], $office)) . '</p></div>';
    }

    echo '<h2 class="sec">Esto te toca hoy</h2>';
    if (!$items) echo '<div class="card"><p class="muted">Hoy no tienes actividades del curso. Si hiciste contactos, cuéntame abajo.</p></div>';
    echo '<ol class="acts">';
    foreach ($items as $it) render_item($it, $V);
    echo '</ol>';

    echo '<h2 class="sec">¿Cómo te fue hoy?</h2><div class="card nums">';
    foreach (result_fields() as $f => $label) {
        $goal = '';
        if (prog() === 'industrial') {
            $min = week_min($week, $f);
            $goal = $f === 'llamadas' ? 'Meta: 10 al día · ' . $min . ' esta semana · llevas ' . (int)($stats['week_totals'][$week][$f] ?? 0)
                : ($min > 0 ? 'Meta de la semana: ' . $min . ' · llevas ' . (int)($stats['week_totals'][$week][$f] ?? 0) : '');
        } elseif ($f === 'contactos') {
            $goal = !empty($ctx['arranque']) ? 'Meta de arranque: ' . setting('arranque_llamadas') . ' llamadas' : 'Tu meta: ' . goal_for($goals, $week, 'contactos') . ' al día';
        } else {
            $g = goal_for($goals, $week, $f);
            if ($g > 0) $goal = 'Tu meta: ' . $g . ' esta semana · llevas ' . (int)($stats['week_totals'][$week][$f] ?? 0);
        }
        num_field($f, $label, (int)($log[$f] ?? 0), RESULT_HINTS[$f], $goal);
    }
    echo '</div>';
    if (prog() === 'residencial') echo '<div class="card analisis" id="analisisBox"><b>Cuéntame qué propiedades analizaste</b><p class="small muted">Qué propiedad es, qué opinas de su valor y por qué.</p>'
        . '<textarea name="analisis" rows="4" placeholder="Ej. Casa en Región 15, 3 recámaras. Piden $2.8 M; creo que vale $2.5 M porque tres casas parecidas se ofrecen entre $2.4 y $2.6 M.">' . h($log['analisis'] ?? '') . '</textarea></div>';

    if (prog() === 'industrial') { logros_industrial($adv); } else {
    $ohL = oh_label($office);
    echo '<details class="card logros"><summary><b>Mis logros</b> <span class="muted small">' . h($ohL) . ', captación y renta</span></summary><div class="logros-in">'
        . '<label class="check"><input type="checkbox" name="logros[oh_done]" value="1"' . ((int)$adv['oh_done'] ? ' checked' : '') . '> Ya realicé mi ' . h($ohL) . ', supervisado por mi oficina</label>'
        . '<div class="grid2"><label class="lbl">Fecha<input type="date" name="logros[oh_date]" value="' . h($adv['oh_date'] ?? '') . '"></label>'
        . '<label class="lbl">' . oh_count_label($office) . '<input type="number" min="0" inputmode="numeric" name="logros[oh_attendees]" value="' . (int)$adv['oh_attendees'] . '"></label></div>'
        . '<p class="small muted">Si captaste una propiedad, marca lo que ya tienes completo:</p>'
        . '<label class="check"><input type="checkbox" name="logros[cap_precio]" value="1"' . ((int)$adv['cap_precio'] ? ' checked' : '') . '> Precio en valor de mercado</label>'
        . '<label class="check"><input type="checkbox" name="logros[cap_docs]" value="1"' . ((int)$adv['cap_docs'] ? ' checked' : '') . '> Documentos completos</label>'
        . '<label class="check"><input type="checkbox" name="logros[cap_disp]" value="1"' . ((int)$adv['cap_disp'] ? ' checked' : '') . '> Disponibilidad para mostrarla</label>'
        . '<label class="check"><input type="checkbox" name="logros[renta_closed]" value="1"' . ((int)$adv['renta_closed'] ? ' checked' : '') . '> Cerré una renta</label>'
        . '<label class="lbl">Fecha de la renta<input type="date" name="logros[renta_date]" value="' . h($adv['renta_date'] ?? '') . '"></label></div></details>';
    }

    echo '<div class="savebar"><button class="btn btn-main btn-block btn-xl"' . ($preview ? ' type="button" disabled' : '') . '>'
        . ($preview ? 'Vista previa: no se guarda' : ($log ? 'Actualizar mi día' : 'Guardar mi día')) . '</button></div></form>';

    echo '<details class="card"><summary><b>Mi avance en el curso</b></summary>';
    kpi_grid($stats);
    echo '<p class="small muted">Actividades cumplidas hasta hoy: ' . (int)$stats['done'] . ' de ' . (int)$stats['assigned'] . ' (' . (int)$stats['pct'] . '%). Evaluación estimada: ' . (int)$stats['evaluation']['total'] . ' de 100 puntos.</p>';
    $mine = array_filter($stats['retos']['list'] ?? [], fn($r) => $r['due']);
    if ($mine) {
        echo '<h3>Mis Retos del video</h3><ul class="retos-mine">';
        foreach ($mine as $r) {
            $ok = $r['row'] ? (int)$r['row']['ok_count'] : null;
            echo '<li>' . h($r['label']) . ': <b>' . ($ok === null ? 'pendiente' : $ok . ' de 5' . ($ok >= 4 ? ' · superado' : ' · repasar')) . '</b></li>';
        }
        echo '</ul>';
    }
    echo '</details>';
    if ($pw && trim((string)$pw['remember']) !== '') {
        echo '<div class="box remember"><span class="eyebrow">Lo que te pido que recuerdes</span><ul>';
        foreach (array_filter(explode("\n", oh_text($pw['remember'], $office))) as $r) echo '<li>' . h($r) . '</li>';
        echo '</ul></div>';
    }
    install_hint();
}

function render_item(array $it, array $V): void
{
    $log = $V['log']; $preview = !empty($V['preview']); $office = $V['office'];
    $li = $log['items'][$it['key']] ?? null;
    $checked = $li && (int)$li['done'] === 1;
    $k = h($it['key']);
    $isSession = $it['kind'] === 'oficina';
    echo '<li class="act' . ($checked ? ' is-done' : '') . ($isSession || $it['kind'] === 'tl' ? ' act-office' : '') . '">';
    $isTl = $it['kind'] === 'tl';
    if ($isSession) echo '<div class="act-band">SESIÓN EN TU OFICINA</div>';
    if ($isTl) echo '<div class="act-band">CITA CON TU TEAM LEADER · EN LA OFICINA</div>';
    echo '<label class="act-main"><input type="checkbox" name="done[' . $k . ']" value="1"' . ($checked ? ' checked' : '') . '><span class="tick" aria-hidden="true"></span><span class="act-text">';
    $origin = $it['origin'] ?: ($it['kind'] === 'video' ? 'VIDEO DEL CURSO' : null);
    if ($origin) echo '<span class="eyebrow">' . h($origin) . '</span>';
    echo '<span class="act-title">' . h($it['title']) . '</span>';
    if ($isSession && $it['session']) {
        $s = $it['session'];
        echo '<span class="act-meta">' . h(ucfirst(fecha_larga($s['date']))) . ($s['stime'] ? ' · ' . h(time_label($s['stime'])) : '') . '</span>';
        if ($s['who'] || $s['place']) echo '<span class="act-meta">' . h(trim(($s['who'] ? 'Con ' . $s['who'] : '') . ($s['place'] ? ' · ' . $s['place'] : ''), ' ·')) . '</span>';
    } elseif ($isSession && $preview) {
        echo '<span class="act-meta">La fecha y la hora las programa tu oficina</span>';
    }
    if ($isTl) echo '<span class="act-meta">Acuerda la hora con tu Team Leader. Márcala cuando vayas.</span>';
    if ($it['minutes']) echo '<span class="chip">' . h(time_chip($it['kind'], $it['minutes'])) . '</span>';
    echo '</span></label>';

    if ($it['pending_session']) {
        echo '<div class="act-pend">Pide a tu oficina tu sesión de <b>' . h(mb_strtolower($it['title'])) . '</b>. Ya le avisé a tu director.</div>';
    }
    if ($it['kind'] === 'video') {
        $src = video_embed_url($it['video_url']);
        echo '<div class="act-video">';
        if ($src) {
            echo '<div class="video-wrap"><iframe src="' . h($src) . '" loading="lazy" allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen title="' . h($it['title']) . '"></iframe></div>';
        } else {
            echo '<div class="video-soon">Este video estará listo muy pronto.</div>';
        }
        echo '</div>';
        reto_result_box($it, $V);
    }

    $open = in_array($it['kind'], ['repaso', 'metas'], true);
    echo '<details class="act-more"' . ($open ? ' open' : '') . '><summary>Te explico cómo hacerla</summary>';
    if (trim((string)$it['guide']) !== '') echo '<p class="guide">' . nl2br(h($it['guide'])) . '</p>';
    if ($it['kind'] === 'agenda') agenda_list($it['week'], $V['sessions'] ?? [], $office);
    if ($it['kind'] === 'metas') goals_block($V, $it['week']);
    if ($it['kind'] === 'repaso') repaso_block($V, $it['week']);
    if ($it['help']) {
        echo '<div class="help-links"><span class="eyebrow">Si lo necesitas · Videos de apoyo</span>';
        foreach ($it['help'] as $hl) echo '<a href="' . h($hl['url']) . '" target="_blank" rel="noopener"><span class="play" aria-hidden="true">▶</span>' . h($hl['title']) . '</a>';
        echo '</div>';
    }
    if ($it['kind'] !== 'metas') {
        echo '<label class="lbl">Cuéntame cómo te fue<textarea name="res[' . $k . ']" rows="2" placeholder="Escribe aquí cómo te fue">' . h($li['result_text'] ?? '') . '</textarea></label>';
    }
    if ($it['kind'] === 'foto') {
        echo '<label class="lbl file">Foto <input type="file" name="photo[' . $k . ']" accept="image/*"></label>';
        if (!empty($li['photo'])) echo '<p class="small ok">✓ Ya subiste una foto. Si subes otra, se reemplaza.</p>';
    }
    echo '</details></li>';
}

/** Resultado del Reto del video, con lo que hay que repasar. */
function reto_result_box(array $it, array $V): void
{
    foreach ($V['stats']['retos']['list'] ?? [] as $r) {
        if ((int)$r['activity']['id'] !== (int)$it['id'] || !$r['row']) continue;
        $row = $r['row']; $ok = (int)$row['ok_count'];
        if ($ok >= 4) {
            echo '<div class="box reto-ok"><b>¡Superaste tu Reto del ' . h($r['label']) . '!</b> ' . $ok . ' de 5. ' . h(T('reto_ok')) . '</div>';
            return;
        }
        $qs = reto_questions($r['activity']); $mins = [];
        foreach (str_split((string)$row['answers']) as $i => $v) if ($v === 'r' && ($qs[$i]['min'] ?? '') !== '') $mins[] = $qs[$i]['min'];
        echo '<div class="box warn reto-rep"><b>Te toca repasar el ' . h($r['label']) . '</b> (' . $ok . ' de 5). Vuelve a ver el video'
            . ($mins ? ', sobre todo los minutos ' . h(implode(', ', $mins)) : '') . '.'
            . (trim((string)$row['note']) !== '' ? '<br>Tu Team Leader te dice: «' . h($row['note']) . '»' : '')
            . '<br>En tu cierre de semana te vuelve a preguntar y puedes ganar el punto completo.</div>';
        return;
    }
}

/** Edalo · el asesor repite su Semana 2: qué le falta, con números exactos, para que no parezca un error. */
function filtro_box(array $f): void
{
    $lab = ['llamadas' => 'Llamadas del Impulsor', 'recorridos' => 'Recorridos de zona', 'leads' => 'Leads válidos'];
    echo '<div class="card filtro"><span class="eyebrow">Tu filtro de la Semana 2 · semanas 1 y 2 juntas</span>';
    echo '<p>Esta semana repites tu Semana 2. No es un error: es el filtro de esfuerzo del programa. Lo industrial empieza en la Semana 3, y para llegar ahí primero necesitas el ritmo de prospección.</p>';
    foreach (FILTRO_S2 as $k => $min) {
        $v = (int)$f['sums'][$k];
        $pct = (int)round(min(1, $v / $min) * 100);
        echo '<div class="prog-row"><span>' . h($lab[$k]) . '</span><b>' . $v . ' de ' . $min . '</b></div>' . bar($pct, $v >= $min ? '' : 'gold');
    }
    if ($f['faltan']) echo '<p class="lead"><b>' . h(ucfirst(filtro_falta_frase($f['faltan'], 'Te'))) . '.</b> En cuanto lo completes, te abro la Semana 3 al día siguiente.</p>';
    echo '<p class="small muted">Tus actividades de la Semana 2, tus 10 llamadas diarias y tus citas con tu Team Leader siguen igual. Tu oficina ya sabe que repites la semana y te va a apoyar.</p></div>';
}

/** Punto de control al cerrar la Semana 3: el asesor ve cómo quedó mientras su oficina decide. */
function checkpoint_box(array $adv, array $stats, array $office): void
{
    $cp = checkpoint($adv, $stats, $office);
    echo '<div class="card filtro"><span class="eyebrow">Tu punto de control · Semana 3</span>';
    echo '<p>Cerraste tu Semana 3. ' . ($cp['pass'] ? '<b>Cumples tu punto de control.</b>' : '<b>Todavía no cumples tu punto de control.</b>') . '</p><ul>';
    foreach ($cp['checks'] as [$label, $ok]) echo '<li>' . ($ok ? '✅' : '⬜') . ' ' . h($label) . '</li>';
    echo '</ul><p class="small">Tu oficina va a platicar contigo para decidir juntos cómo sigues en el curso. Mientras tanto, sigue con tu plan de hoy: todo lo que hagas cuenta.</p></div>';
}

/** Edalo · Mis logros: captación, oferta, inquilino calificado con visita y renta cerrada. */
function logros_industrial(array $adv): void
{
    $row = function (string $k, string $label) use ($adv): string {
        $done = (int)($adv[$k . '_done'] ?? 0) === 1;
        return '<label class="check"><input type="checkbox" name="logros[' . $k . '_done]" value="1"' . ($done ? ' checked' : '') . '> ' . h($label) . '</label>'
            . '<label class="lbl">Fecha<input type="date" name="logros[' . $k . '_date]" value="' . h($adv[$k . '_date'] ?? '') . '"></label>';
    };
    echo '<details class="card logros"><summary><b>Mis logros</b> <span class="muted small">captación, oferta, inquilino y renta</span></summary><div class="logros-in">'
        . $row('ind_cap', 'Capté un inmueble con autorización del propietario (acuerdo de promoción firmado)')
        . $row('ind_oferta', 'Presenté al propietario una oferta formal por escrito')
        . $row('ind_inq', 'Hice una visita con un inquilino calificado')
        . '<label class="check"><input type="checkbox" name="logros[renta_closed]" value="1"' . ((int)$adv['renta_closed'] ? ' checked' : '') . '> Cerré una renta (comercial o industrial)</label>'
        . '<label class="lbl">Fecha de la renta<input type="date" name="logros[renta_date]" value="' . h($adv['renta_date'] ?? '') . '"></label>'
        . '<p class="small muted">Una oferta formal va por escrito (renta, plazo y fecha de entrada), la presentaste tú al propietario y lleva nombre y contacto reales del cliente y del propietario.</p></div></details>';
}

/** En los videos, el tiempo es para el video y su tarea; así nadie piensa que el video dura eso. */
function time_chip(string $kind, int $minutes): string
{
    return $kind === 'video' ? 'Aparta ' . minutes_label($minutes) . ': video y tarea' : minutes_label($minutes);
}

function num_field(string $f, string $label, int $value, string $hint, string $goal): void
{
    echo '<div class="num"><div class="num-l"><span class="num-label">' . h($label) . '</span><span class="num-hint">' . h($hint) . '</span>'
        . ($goal !== '' ? '<span class="num-goal">' . h($goal) . '</span>' : '') . '</div>'
        . '<div class="stepper"><button type="button" class="st-btn" data-step="-1" aria-label="Menos">−</button>'
        . '<input type="number" min="0" max="999" inputmode="numeric" name="n[' . h($f) . ']" value="' . $value . '" aria-label="' . h($label) . '" data-field="' . h($f) . '">'
        . '<button type="button" class="st-btn" data-step="1" aria-label="Más">+</button></div></div>';
}

function agenda_list(int $week, array $sessions, ?array $office): void
{
    $list = week_time_list($week, $sessions, $office);
    if (!$list) return;
    echo '<ul class="agenda">';
    foreach ($list as $a) {
        echo '<li><span class="eyebrow">Día ' . (int)$a['day'] . '</span> ' . h(kind_prefix($a['kind'])) . h($a['title'])
            . ($a['minutes'] ? ' <span class="chip">' . h(time_chip($a['kind'], $a['minutes'])) . '</span>' : '')
            . ($a['when'] ? ' <span class="small muted">(' . h($a['when']) . ')</span>' : '') . '</li>';
    }
    echo '</ul>';
}

function goals_block(array $V, int $week): void
{
    $goals = $V['goals'];
    echo '<div class="goals" data-goals>';
    foreach (result_fields() as $f => $label) {
        $min = goal_minimum($week, $f);
        $val = goal_for($goals, $week, $f);
        echo '<div class="goal-row"><div><span class="num-label">' . h($f === 'contactos' ? 'Contactos al día' : $label) . '</span><span class="num-hint">Mínimo del programa: ' . $min . '</span></div>'
            . '<div class="stepper"><button type="button" class="st-btn" data-step="-1" aria-label="Menos">−</button>'
            . '<input type="number" min="0" max="999" inputmode="numeric" name="goals[' . $f . ']" value="' . $val . '" data-min="' . $min . '" aria-label="' . h($label) . '">'
            . '<button type="button" class="st-btn" data-step="1" aria-label="Más">+</button></div></div>';
    }
    $accepted = isset($goals[$week]) && (int)$goals[$week]['low_accepted'] === 1;
    echo '<div class="goal-warn" hidden>Hay metas por debajo del mínimo del programa. Con estas metas es difícil llegar a tu meta de ingresos. ¿Quieres ajustarlas?'
        . '<label class="check small"><input type="checkbox" name="goals_accept_low" value="1"' . ($accepted ? ' checked' : '') . '> Dejarlas así. Sé que le avisarás a mi director y a Oscar.</label></div>';
    echo '</div>';
}

function repaso_block(array $V, int $week): void
{
    $adv = $V['adv']; $stats = $V['stats']; $office = $V['office'];
    $qs = array_values(array_filter(array_map('trim', explode("\n", oh_text(plan_weeks()[$week]['review_questions'] ?? '', $office)))));
    $saved = !empty($adv['id']) ? (json_decode((string)val('SELECT answers FROM week_reviews WHERE advisor_id = ? AND week_no = ?', [$adv['id'], $week]), true) ?: []) : [];
    if ($week === 2 && prog() === 'industrial') {
        $f = filtro_s2(['id' => (int)($adv['id'] ?? 0), 'start_date' => $adv['start_date'] ?? office_today($office)], office_today($office));
        echo '<div class="mini"><b>Tu filtro para pasar a la Semana 3 (semanas 1 y 2 juntas)</b><ul>';
        foreach (FILTRO_S2 as $k => $min) { $v = (int)$f['sums'][$k]; echo '<li>' . ($v >= $min ? '✅' : '⬜') . ' ' . h(result_fields()[$k]) . ': ' . $v . ' de ' . $min . '</li>'; }
        echo '</ul></div>';
    } elseif ($week === 2) {
        $c = cum_totals($stats, 2);
        $minC = (int)setting('s2_min_contactos'); $minP = (int)setting('s2_min_promovidas');
        echo '<div class="mini"><b>Tus mínimos (semanas 1 y 2)</b><ul>'
            . '<li>' . ($c['contactos'] >= $minC ? '✅' : '⬜') . ' Contactos: ' . $c['contactos'] . ' de ' . $minC . '</li>'
            . '<li>' . ($c['promovidas'] >= $minP ? '✅' : '⬜') . ' Propiedades de la oficina promovidas: ' . $c['promovidas'] . ' de ' . $minP . '</li>'
            . '<li>⬜ Redes creadas con 3 contenidos (cuéntamelo abajo)</li></ul></div>';
    }
    if ($week === 3) {
        $cp = checkpoint($adv, $stats, $office);
        echo '<div class="mini"><b>Así vas en tu punto de control</b><ul>';
        foreach ($cp['checks'] as [$label, $ok]) echo '<li>' . ($ok ? '✅' : '⬜') . ' ' . h($label) . '</li>';
        echo '</ul></div>';
    }
    if ($week === prog_weeks()) {
        $ev = $stats['evaluation'];
        echo '<div class="mini"><b>Tu evaluación a hoy: ' . (int)$ev['total'] . ' de 100 puntos</b><p class="small">' . h($ev['level_text']) . '</p></div>';
    }
    foreach ($qs as $i => $qq) echo '<label class="lbl">' . h(($i + 1) . '. ' . $qq) . '<textarea name="review[' . $i . ']" rows="2">' . h($saved[$i] ?? '') . '</textarea></label>';
}

function install_hint(): void
{
    echo '<p class="hint small center muted" id="a2hs">Para tenerla a la mano: en tu navegador toca <b>Compartir</b> o <b>⋮</b> y elige <b>Agregar a pantalla de inicio</b>.</p>';
}

/** Bienvenida de Edalo: se presenta y explica su nombre (en honor a Dédalo, el arquitecto del laberinto). */
function welcome_industrial(string $name, string $officeName): void
{
    echo '<div class="card narrow welcome">';
    echo '<div class="adv-head">' . brand_mark('industrial') . '<span class="adv-head-r"><b>R²CP ELITE</b><span class="tagline">Construye con método</span></span></div>';
    echo '<img class="welcome-img" style="display:block;width:100%;max-width:100%;height:auto" src="' . h(prog_img('welcome')) . '" alt="Edalo, tu Team Leader del curso Fast Track Industrial" width="600" height="600">';
    echo '<h1>Hola, ' . h($name) . '. Soy Edalo.</h1>';
    echo '<p>Soy tu Team Leader en el curso <b>Fast Track Industrial</b> y <b>te voy a acompañar en tu arranque en el mercado comercial e industrial</b> durante sus 4 semanas en <b>' . h($officeName) . '</b>.</p>';
    echo '<p>Cada día te digo qué te toca hacer, te comparto los videos del curso y me cuentas tus números en menos de 2 minutos: llamadas, recorridos, fichas, cadenas, leads y piezas de promoción.</p>';
    echo '<p><b>Yo te acompaño todos los días, pero tu equipo está en la oficina.</b> Después de cada video te espera tu Team Leader para tu Reto: unas preguntas para asegurarnos de que dominas el tema. Quienes van a su oficina son quienes se quedan en el negocio.</p>';
    echo '<div class="myth"><div class="myth-h"><svg width="44" height="44" viewBox="0 0 48 48" aria-hidden="true"><rect x="4" y="4" width="40" height="40" rx="4" fill="none" stroke="#18263A" stroke-width="2"/><path d="M4 16 H30 M18 16 V30 M30 30 H44 M10 30 V44 M30 16 V24" fill="none" stroke="#18263A" stroke-width="2"/><circle cx="37" cy="37" r="3" fill="#B89860"/></svg><h2>¿Por qué me llamo Edalo?</h2></div>'
        . '<p>Mi nombre viene de <b>Dédalo</b>, el gran arquitecto de la mitología griega. Él construyó el laberinto de Creta, y fue quien le dio a Ariadna la idea del hilo para que Teseo encontrara la salida.</p>'
        . '<p>El mercado comercial e industrial también es un laberinto: naves, bodegas, locales, terrenos, contratos y números. Yo lo conozco como quien lo diseñó: cada pasillo, cada medida y cada regla.</p>'
        . '<p class="myth-end">Por eso te voy a decir muchas veces: construye con método.</p></div>';
}

/** Bienvenida: Ariadna se presenta y explica su nombre. */
function render_welcome(string $name, string $officeName, string $token, bool $preview = false): void
{
    if (prog() === 'industrial') { welcome_industrial($name, $officeName); } else {
    echo '<div class="card narrow welcome">';
    echo '<div class="adv-head">' . brand_mark() . '<span class="adv-head-r"><b>R²CP ELITE</b><span class="tagline">Sigue el hilo</span></span></div>';
    echo '<img class="welcome-img" style="display:block;width:100%;max-width:100%;height:auto" src="assets/ariadna.jpg" alt="Ariadna, tu Team Leader del curso Fast Track Residencial" width="720" height="720">';
    echo '<h1>Hola, ' . h($name) . '. Soy Ariadna.</h1>';
    echo '<p>Soy tu Team Leader en el curso <b>Fast Track Residencial</b> y <b>te voy a acompañar en el arranque de esta carrera</b> durante sus 5 semanas en <b>' . h($officeName) . '</b>.</p>';
    echo '<p>Cada día te digo qué te toca hacer, te explico cómo hacerlo y te comparto los videos del curso. Tú me cuentas cómo te fue, en menos de 2 minutos. Los temas internos te los da tu oficina, y tu director ve tu avance para apoyarte.</p>';
    echo '<p><b>Yo te acompaño todos los días, pero tu equipo está en la oficina.</b> Cada semana tienes citas con tu Team Leader de la oficina para repasar lo que aprendiste en los videos y cerrar tu semana. No faltes: quienes van a su oficina son quienes se quedan en el negocio.</p>';
    echo '<div class="myth"><div class="myth-h"><svg width="44" height="44" viewBox="0 0 48 48" aria-hidden="true"><rect x="4" y="4" width="40" height="40" rx="4" fill="none" stroke="#1F2A44" stroke-width="2"/><path d="M4 16 H30 M18 16 V30 M30 30 H44 M10 30 V44 M30 16 V24" fill="none" stroke="#1F2A44" stroke-width="2"/><path d="M8 10 C 20 10, 26 12, 24 22 S 14 36, 26 37 S 40 40, 40 40" fill="none" stroke="#C75D3A" stroke-width="2" stroke-dasharray="3 3"/></svg><h2>¿Por qué me llamo Ariadna?</h2></div>'
        . '<p>En la mitología griega, Ariadna, hija del rey Minos de Creta, le dio a Teseo un ovillo de hilo antes de que entrara al laberinto del Minotauro. Teseo lo fue desenrollando a su paso y, siguiendo ese hilo, encontró la salida.</p>'
        . '<p>Los primeros meses en bienes raíces pueden sentirse como un laberinto. Yo soy tu hilo: cada día te doy el siguiente paso. Si lo sigues todos los días, vas a encontrar el camino.</p>'
        . '<p class="myth-end">Por eso te voy a decir muchas veces: sigue el hilo.</p></div>';
    }
    echo '<div class="box"><h3>Aviso de privacidad (resumen)</h3><p>Uso tu nombre, celular, correo, lo que registras cada día y las fotos que subas solo para acompañarte en el curso y para que tu director, gerente y Team Leader de tu oficina vean tu avance. No comparto tus datos con nadie más.</p>'
        . '<p><a href="privacidad.php' . ($token ? '?t=' . h($token) : '') . '">Leer el aviso de privacidad completo</a></p></div>';
    echo '<form method="post" class="form">' . csrf_field() . '<input type="hidden" name="t" value="' . h($token) . '"><input type="hidden" name="action" value="consent">'
        . '<label class="check big"><input type="checkbox" name="acepto" value="1" required> Leí el aviso de privacidad y acepto que mis datos se usen para mi acompañamiento.</label>'
        . '<button class="btn btn-main btn-block"' . ($preview ? ' type="button" disabled' : '') . '>Empecemos</button></form></div>';
}
