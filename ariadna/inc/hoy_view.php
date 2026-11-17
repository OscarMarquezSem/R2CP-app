<?php
/**
 * ARIADNA · Pantalla «Hoy» del asesor (la usan hoy.php y la vista previa del administrador).
 */
declare(strict_types=1);

function ariadna_bubble(string $html): string
{
    return '<div class="bubble"><span class="bubble-av" aria-hidden="true"><svg width="26" height="16" viewBox="0 0 40 24"><path d="M2 18 C 10 2, 18 22, 26 8 S 36 6, 38 4" fill="none" stroke="#B89860" stroke-width="3" stroke-linecap="round"/><circle cx="38" cy="4" r="3" fill="#C75D3A"/></svg></span><p><b>Ariadna:</b> ' . $html . '</p></div>';
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
    foreach ($items as $it) if ($it['kind'] === 'oficina' && $it['session']) return 'Hoy tienes un tema con tu oficina. Lleva libreta y pregunta todo lo que no te quede claro. Antes, tus contactos del día.';
    if (!empty($ctx['arranque'])) return 'Hoy tus ' . h((string)setting('arranque_llamadas')) . ' llamadas van primero. Cada llamada te quita un poco de miedo: haz de esto un hábito.';
    if (($ctx['days'][0] ?? 0) === 1) {
        $pw = plan_weeks()[$ctx['week']] ?? null;
        return 'Empezamos tu Semana ' . (int)$ctx['week'] . ($pw ? ': ' . h(mb_strtolower(oh_text($pw['title'], $office))) : '') . '. Primero agenda tus horarios y pon tus metas; luego sigue la lista conmigo.';
    }
    return 'Empieza por tus contactos del día y luego sigue la lista conmigo.';
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
    elseif ($stats['finished'] && empty($V['ayer_mode'])) echo '<p class="hero-sub">Terminaste el curso Fast Track Residencial.</p>';
    elseif (!empty($V['ayer_mode'])) echo '<p class="hero-sub">Registrando ' . h(fecha_larga($V['date'])) . ' · Semana ' . (int)$week . ' · ' . h(ctx_label($ctx)) . '</p>';
    else echo '<p class="hero-sub">Semana ' . (int)$week . ' · ' . h(ctx_label($ctx)) . '</p>';
    echo '<svg class="thread" viewBox="0 0 400 30" preserveAspectRatio="none" aria-hidden="true"><path d="M0 20 C 60 0, 110 30, 170 14 S 280 4, 330 18 S 390 22, 400 10" fill="none" stroke="#B89860" stroke-width="2"/></svg></header>';

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
        echo '<div class="card"><h2>Tu evaluación final: ' . (int)$ev['total'] . ' puntos</h2><p class="lead">' . h($ev['level_text']) . '</p>';
        eval_table($ev);
        echo '<p>Gracias por seguir el hilo. Tu plan de los próximos 30 días lo revisas con tu oficina.</p></div>';
        return;
    }

    echo '<a id="hoy"></a>';
    if ($ctx['type'] !== 'rest') echo ariadna_bubble(ariadna_message($ctx, $items, $office));

    $programPct = (int)round(min(PROGRAM_DAYS, max(0, $stats['elapsed'] + 1)) / PROGRAM_DAYS * 100);
    $weekPct = !empty($stats['week_assigned'][$week]) ? (int)round(min(1, $stats['week_done'][$week] / $stats['week_assigned'][$week]) * 100) : 0;
    echo '<section class="progress card"><div class="prog-row"><span>Tu semana</span><b>' . $weekPct . '%</b></div>' . bar($weekPct)
        . '<div class="prog-row"><span>Fast Track Residencial · Semana ' . (int)$week . ' de 5</span><b>' . $programPct . '%</b></div>' . bar($programPct, 'gold') . '</section>';

    if (!$preview) {
        echo '<div class="card push-card" id="pushCard" hidden><p><b>¿Te aviso por aquí?</b> Te mando un aviso en la mañana con lo que te toca y otro a las 8 pm solo si no me has contado cómo te fue.</p>'
            . '<button type="button" class="btn btn-block" id="pushBtn" data-key="' . h(vapid_public_key()) . '" data-t="' . h($t) . '">Activar avisos de Ariadna</button></div>';
    }

    echo '<form method="post" enctype="multipart/form-data" class="dayform" id="dayform"' . ($preview ? ' data-preview="1"' : '') . '>';
    echo csrf_field() . '<input type="hidden" name="t" value="' . h($t) . '"><input type="hidden" name="action" value="save">';
    if (!empty($V['ayer_mode'])) echo '<input type="hidden" name="d" value="ayer">';

    if ($ctx['type'] === 'rest') {
        echo '<div class="card rest"><h2>Hoy descansas, pero no sueltes el hilo.</h2><p>Si conoces a alguien que quiera comprar, vender o rentar, anótalo aquí.</p>';
        num_field('contactos', 'Contactos', (int)($log['contactos'] ?? 0), '¿A cuántas personas detectaste?', '');
        foreach (['tratos', 'analizadas', 'promovidas', 'citas', 'captaciones'] as $f) echo '<input type="hidden" name="n[' . $f . ']" value="' . (int)($log[$f] ?? 0) . '">';
        echo '<label class="lbl">¿Quién es y qué busca? (opcional)<textarea name="note" rows="3" placeholder="Ej. Mi primo quiere vender su casa en la Región 15">' . h($log['note'] ?? '') . '</textarea></label>';
        echo '<button class="btn btn-main btn-block"' . ($preview ? ' disabled' : '') . '>Guardar</button></div></form>';
        install_hint();
        return;
    }

    if ($pw && $ctx['type'] !== 'training') {
        echo '<div class="box objective"><span class="eyebrow">Fast Track Residencial · Semana ' . (int)$week . ' · ' . h(oh_text($pw['title'], $office)) . '</span><p>' . h(oh_text($pw['objective'], $office)) . '</p></div>';
    }

    echo '<h2 class="sec">Esto te toca hoy</h2>';
    if (!$items) echo '<div class="card"><p class="muted">Hoy no tienes actividades del curso. Si hiciste contactos, cuéntame abajo.</p></div>';
    echo '<ol class="acts">';
    foreach ($items as $it) render_item($it, $V);
    echo '</ol>';

    echo '<h2 class="sec">¿Cómo te fue hoy?</h2><div class="card nums">';
    foreach (RESULT_FIELDS as $f => $label) {
        $goal = '';
        if ($f === 'contactos') {
            $goal = !empty($ctx['arranque']) ? 'Meta de arranque: ' . setting('arranque_llamadas') . ' llamadas' : 'Tu meta: ' . goal_for($goals, $week, 'contactos') . ' al día';
        } else {
            $g = goal_for($goals, $week, $f);
            if ($g > 0) $goal = 'Tu meta: ' . $g . ' esta semana · llevas ' . (int)($stats['week_totals'][$week][$f] ?? 0);
        }
        num_field($f, $label, (int)($log[$f] ?? 0), RESULT_HINTS[$f], $goal);
    }
    echo '</div>';
    echo '<div class="card analisis" id="analisisBox"><b>Cuéntame qué propiedades analizaste</b><p class="small muted">Qué propiedad es, qué opinas de su valor y por qué.</p>'
        . '<textarea name="analisis" rows="4" placeholder="Ej. Casa en Región 15, 3 recámaras. Piden $2.8 M; creo que vale $2.5 M porque tres casas parecidas se ofrecen entre $2.4 y $2.6 M.">' . h($log['analisis'] ?? '') . '</textarea></div>';

    $ohL = oh_label($office);
    echo '<details class="card logros"><summary><b>Mis logros</b> <span class="muted small">' . h($ohL) . ', captación y renta</span></summary><div class="logros-in">'
        . '<label class="check"><input type="checkbox" name="logros[oh_done]" value="1"' . ((int)$adv['oh_done'] ? ' checked' : '') . '> Ya realicé mi ' . h($ohL) . ', supervisado por mi oficina</label>'
        . '<div class="grid2"><label class="lbl">Fecha<input type="date" name="logros[oh_date]" value="' . h($adv['oh_date'] ?? '') . '"></label>'
        . '<label class="lbl">' . ($ohL === 'Open House' ? 'Asistentes reales' : 'Interesados que dejaron sus datos') . '<input type="number" min="0" inputmode="numeric" name="logros[oh_attendees]" value="' . (int)$adv['oh_attendees'] . '"></label></div>'
        . '<p class="small muted">Si captaste una propiedad, marca lo que ya tienes completo:</p>'
        . '<label class="check"><input type="checkbox" name="logros[cap_precio]" value="1"' . ((int)$adv['cap_precio'] ? ' checked' : '') . '> Precio en valor de mercado</label>'
        . '<label class="check"><input type="checkbox" name="logros[cap_docs]" value="1"' . ((int)$adv['cap_docs'] ? ' checked' : '') . '> Documentos completos</label>'
        . '<label class="check"><input type="checkbox" name="logros[cap_disp]" value="1"' . ((int)$adv['cap_disp'] ? ' checked' : '') . '> Disponibilidad para mostrarla</label>'
        . '<label class="check"><input type="checkbox" name="logros[renta_closed]" value="1"' . ((int)$adv['renta_closed'] ? ' checked' : '') . '> Cerré una renta</label>'
        . '<label class="lbl">Fecha de la renta<input type="date" name="logros[renta_date]" value="' . h($adv['renta_date'] ?? '') . '"></label></div></details>';

    echo '<div class="savebar"><button class="btn btn-main btn-block btn-xl"' . ($preview ? ' type="button" disabled' : '') . '>'
        . ($preview ? 'Vista previa: no se guarda' : ($log ? 'Actualizar mi día' : 'Guardar mi día')) . '</button></div></form>';

    echo '<details class="card"><summary><b>Mi avance en el curso</b></summary>';
    kpi_grid($stats);
    echo '<p class="small muted">Actividades cumplidas hasta hoy: ' . (int)$stats['done'] . ' de ' . (int)$stats['assigned'] . ' (' . (int)$stats['pct'] . '%). Evaluación estimada: ' . (int)$stats['evaluation']['total'] . ' de 100 puntos.</p></details>';
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
    echo '<li class="act' . ($checked ? ' is-done' : '') . ($isSession ? ' act-office' : '') . '">';
    if ($isSession) echo '<div class="act-band">SESIÓN EN TU OFICINA</div>';
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
    if ($it['minutes']) echo '<span class="chip">' . h(minutes_label($it['minutes'])) . '</span>';
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
        echo '<li><span class="eyebrow">Día ' . (int)$a['day'] . '</span> ' . ($a['kind'] === 'oficina' ? 'Sesión en tu oficina: ' : '') . h($a['title'])
            . ($a['minutes'] ? ' <span class="chip">' . h(minutes_label($a['minutes'])) . '</span>' : '')
            . ($a['when'] ? ' <span class="small muted">(' . h($a['when']) . ')</span>' : '') . '</li>';
    }
    echo '</ul>';
}

function goals_block(array $V, int $week): void
{
    $goals = $V['goals'];
    echo '<div class="goals" data-goals>';
    foreach (RESULT_FIELDS as $f => $label) {
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
    if ($week === 2) {
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
    if ($week === 5) {
        $ev = $stats['evaluation'];
        echo '<div class="mini"><b>Tu evaluación a hoy: ' . (int)$ev['total'] . ' de 100 puntos</b><p class="small">' . h($ev['level_text']) . '</p></div>';
    }
    foreach ($qs as $i => $qq) echo '<label class="lbl">' . h(($i + 1) . '. ' . $qq) . '<textarea name="review[' . $i . ']" rows="2">' . h($saved[$i] ?? '') . '</textarea></label>';
}

function install_hint(): void
{
    echo '<p class="hint small center muted" id="a2hs">Para tenerla a la mano: en tu navegador toca <b>Compartir</b> o <b>⋮</b> y elige <b>Agregar a pantalla de inicio</b>.</p>';
}

/** Bienvenida: Ariadna se presenta y explica su nombre. */
function render_welcome(string $name, string $officeName, string $token, bool $preview = false): void
{
    echo '<div class="card narrow welcome">';
    echo '<div class="adv-head">' . brand_mark() . '<span class="adv-head-r"><b>R²CP ELITE</b><span class="tagline">Sigue el hilo</span></span></div>';
    echo '<h1>Hola, ' . h($name) . '. Soy Ariadna.</h1>';
    echo '<p>Soy tu Team Leader en el curso <b>Fast Track Residencial</b> y <b>te voy a acompañar en el arranque de esta carrera</b> durante sus 5 semanas en <b>' . h($officeName) . '</b>.</p>';
    echo '<p>Cada día te digo qué te toca hacer, te explico cómo hacerlo y te comparto los videos del curso. Tú me cuentas cómo te fue, en menos de 2 minutos. Los temas internos te los da tu oficina, y tu director ve tu avance para apoyarte.</p>';
    echo '<div class="myth"><div class="myth-h"><svg width="44" height="44" viewBox="0 0 48 48" aria-hidden="true"><rect x="4" y="4" width="40" height="40" rx="4" fill="none" stroke="#1F2A44" stroke-width="2"/><path d="M4 16 H30 M18 16 V30 M30 30 H44 M10 30 V44 M30 16 V24" fill="none" stroke="#1F2A44" stroke-width="2"/><path d="M8 10 C 20 10, 26 12, 24 22 S 14 36, 26 37 S 40 40, 40 40" fill="none" stroke="#C75D3A" stroke-width="2" stroke-dasharray="3 3"/></svg><h2>¿Por qué me llamo Ariadna?</h2></div>'
        . '<p>En la mitología griega, Ariadna, hija del rey Minos de Creta, le dio a Teseo un ovillo de hilo antes de que entrara al laberinto del Minotauro. Teseo lo fue desenrollando a su paso y, siguiendo ese hilo, encontró la salida.</p>'
        . '<p>Los primeros meses en bienes raíces pueden sentirse como un laberinto. Yo soy tu hilo: cada día te doy el siguiente paso. Si lo sigues todos los días, vas a encontrar el camino.</p>'
        . '<p class="myth-end">Por eso te voy a decir muchas veces: sigue el hilo.</p></div>';
    echo '<div class="box"><h3>Aviso de privacidad (resumen)</h3><p>Uso tu nombre, celular, correo, lo que registras cada día y las fotos que subas solo para acompañarte en el curso y para que tu director, gerente y Team Leader de tu oficina vean tu avance. No comparto tus datos con nadie más.</p>'
        . '<p><a href="privacidad.php' . ($token ? '?t=' . h($token) : '') . '">Leer el aviso de privacidad completo</a></p></div>';
    echo '<form method="post" class="form">' . csrf_field() . '<input type="hidden" name="t" value="' . h($token) . '"><input type="hidden" name="action" value="consent">'
        . '<label class="check big"><input type="checkbox" name="acepto" value="1" required> Leí el aviso de privacidad y acepto que mis datos se usen para mi acompañamiento.</label>'
        . '<button class="btn btn-main btn-block"' . ($preview ? ' type="button" disabled' : '') . '>Empecemos</button></form></div>';
}
