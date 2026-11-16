<?php
/**
 * ARIADNA · Pantalla del asesor: "Hoy"
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';
require __DIR__ . '/inc/advisor_auth.php';

enforce_https();
start_session();
[$adv, $office] = advisor_from_token();
$t = $adv['token'];
$self = 'hoy.php?t=' . $t;
$manifest = 'manifest.php?t=' . $t;
$opts = ['body' => 'advisor', 'manifest' => $manifest];

// ---------------------------------------------------------------------------
// Programa detenido por la oficina
// ---------------------------------------------------------------------------
if (!(int)$adv['active'] || $adv['decision'] === 'no_continua') {
    page_start('Ariadna', $opts);
    echo '<div class="card narrow center">' . brand_mark() . '<h2>Tu acompañamiento está en pausa</h2><p>Habla con tu director o tu Team Leader para saber cómo seguir.</p></div>';
    page_end();
    exit;
}

// ---------------------------------------------------------------------------
// Primera vez: aviso de privacidad y consentimiento
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'consent') {
    csrf_check();
    if (!empty($_POST['acepto'])) {
        q('UPDATE advisors SET consent_at = NOW(), consent_ip = ? WHERE id = ?', [client_ip(), $adv['id']]);
        redirect($self);
    }
    flash('Para continuar marca la casilla de aceptación.', 'err');
    redirect($self);
}
if (!$adv['consent_at']) {
    page_start('Bienvenida', $opts);
    ?>
    <div class="card narrow">
      <div class="center"><?= brand_mark() ?><p class="tagline">Sigue el hilo</p></div>
      <h1>Hola, <?= h(first_name($adv['name'])) ?></h1>
      <p>Ariadna te va a acompañar durante tus primeras 5 semanas en <b><?= h($office['name']) ?></b>. Cada día te dice qué hacer y tú registras tu avance en menos de 2 minutos.</p>
      <div class="box">
        <h3>Aviso de privacidad (resumen)</h3>
        <p>Usamos tu nombre, celular, correo, lo que registras cada día y las fotos que subas, solo para acompañarte en el programa y para que tu director, gerente y Team Leader vean tu avance. No compartimos tus datos con nadie más.</p>
        <p><a href="privacidad.php?t=<?= h($t) ?>">Leer el aviso de privacidad completo</a></p>
      </div>
      <form method="post" class="form">
        <?= csrf_field() ?><input type="hidden" name="t" value="<?= h($t) ?>"><input type="hidden" name="action" value="consent">
        <label class="check big"><input type="checkbox" name="acepto" value="1" required> Leí el aviso de privacidad y acepto que mis datos se usen para mi acompañamiento.</label>
        <button class="btn btn-main btn-block">Empezar</button>
      </form>
    </div>
    <?php
    page_end();
    exit;
}

// ---------------------------------------------------------------------------
// PIN opcional (activable por oficina)
// ---------------------------------------------------------------------------
if ((int)$office['pin_enabled'] === 1) {
    $lockKey = 'pin:' . $adv['id'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array(post('action'), ['set_pin', 'pin'], true)) {
        csrf_check();
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
        ?>
        <div class="card narrow center">
          <?= brand_mark() ?>
          <h2><?= $set ? 'Crea tu PIN de 4 dígitos' : 'Escribe tu PIN' ?></h2>
          <p class="muted"><?= $set ? 'Tu oficina pide un PIN para proteger tu información. Te lo pediremos de vez en cuando.' : 'Hola, ' . h(first_name($adv['name'])) . '.' ?></p>
          <form method="post" class="form pinform">
            <?= csrf_field() ?><input type="hidden" name="t" value="<?= h($t) ?>"><input type="hidden" name="action" value="<?= $set ? 'set_pin' : 'pin' ?>">
            <input class="pin" name="pin" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="off" required placeholder="••••" aria-label="PIN">
            <?php if ($set): ?><input class="pin" name="pin2" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="off" required placeholder="Repite" aria-label="Repite tu PIN"><?php endif; ?>
            <button class="btn btn-main btn-block"><?= $set ? 'Guardar mi PIN' : 'Entrar' ?></button>
          </form>
        </div>
        <?php
        page_end();
        exit;
    }
}

// ---------------------------------------------------------------------------
// Contexto del día
// ---------------------------------------------------------------------------
$stats = advisor_stats($adv, $office);
$today = $stats['today'];
$ctx = $stats['ctx'];
$log = $stats['logs'][$today] ?? null;
$items = $ctx ? ctx_items($ctx, week_done_keys($stats['logs'], $stats['schedule'], $ctx['week'], $today)) : [];
$week = $ctx['week'] ?? $stats['week'];
$pw = plan_weeks()[$week] ?? null;

// ---------------------------------------------------------------------------
// Guardar mi día
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'save' && $ctx) {
    csrf_check();
    $nums = [];
    foreach (RESULT_FIELDS as $f => $_) $nums[$f] = max(0, min(999, (int)($_POST['n'][$f] ?? 0)));
    $note = mb_substr(trim((string)($_POST['note'] ?? '')), 0, 2000);
    db()->beginTransaction();
    q('INSERT INTO daily_logs (advisor_id, log_date, week_no, day_type, day_label, contactos, tratos, analizadas, promovidas, citas, captaciones, note, created_at, updated_at)
       VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())
       ON DUPLICATE KEY UPDATE week_no = VALUES(week_no), day_type = VALUES(day_type), day_label = VALUES(day_label),
         contactos = VALUES(contactos), tratos = VALUES(tratos), analizadas = VALUES(analizadas), promovidas = VALUES(promovidas),
         citas = VALUES(citas), captaciones = VALUES(captaciones), note = VALUES(note), updated_at = NOW()',
        [$adv['id'], $today, $week, $ctx['type'], ctx_label($ctx), $nums['contactos'], $nums['tratos'], $nums['analizadas'],
         $nums['promovidas'], $nums['citas'], $nums['captaciones'], $note]);
    $logId = (int)val('SELECT id FROM daily_logs WHERE advisor_id = ? AND log_date = ?', [$adv['id'], $today]);
    $done = (array)($_POST['done'] ?? []);
    $res = (array)($_POST['res'] ?? []);
    $reviewAnswers = array_map(fn($x) => mb_substr(trim((string)$x), 0, 2000), (array)($_POST['review'] ?? []));
    foreach ($items as $it) {
        $k = $it['key'];
        $txt = mb_substr(trim((string)($res[$k] ?? '')), 0, 2000);
        $isDone = !empty($done[$k]) ? 1 : 0;
        if ($it['kind'] === 'repaso' && array_filter($reviewAnswers)) $isDone = 1;
        $photo = null;
        if (!empty($_FILES['photo']['name'][$k])) {
            $f = ['error' => $_FILES['photo']['error'][$k], 'size' => $_FILES['photo']['size'][$k], 'tmp_name' => $_FILES['photo']['tmp_name'][$k]];
            $photo = save_upload_photo($f, (int)$adv['id'], $today, $k);
            if ($photo) $isDone = 1;
        }
        q('INSERT INTO log_items (log_id, item_key, done, result_text, photo) VALUES (?,?,?,?,?)
           ON DUPLICATE KEY UPDATE done = VALUES(done), result_text = VALUES(result_text), photo = COALESCE(VALUES(photo), photo)',
            [$logId, $k, $isDone, $txt !== '' ? $txt : null, $photo]);
        if ($it['kind'] === 'repaso') {
            q('INSERT INTO week_reviews (advisor_id, week_no, answers, updated_at) VALUES (?,?,?,NOW()) ON DUPLICATE KEY UPDATE answers = VALUES(answers), updated_at = NOW()',
                [$adv['id'], $week, json_encode(array_values($reviewAnswers), JSON_UNESCAPED_UNICODE)]);
        }
    }
    // Mis logros (Open House, calidad de captación, renta)
    if (isset($_POST['logros'])) {
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
    flash($ctx['type'] === 'rest' ? '¡Gracias! Quedó anotado. Disfruta tu descanso.' : '¡Listo! Guardaste tu día. No sueltes el hilo.');
    redirect($self . '#top');
}

// ---------------------------------------------------------------------------
// Vista
// ---------------------------------------------------------------------------
page_start('Hoy', $opts);
$name = first_name($adv['name']);
$programPct = (int)round(min(PROGRAM_DAYS, max(0, $stats['elapsed'] + 1)) / PROGRAM_DAYS * 100);
$weekPct = $stats['week_assigned'][$week] ? (int)round(min(1, $stats['week_done'][$week] / $stats['week_assigned'][$week]) * 100) : 0;
?>
<a id="top"></a>
<header class="hero">
  <div class="hero-top"><?= brand_mark() ?><span class="muted small"><?= h(fecha_larga($today)) ?></span></div>
  <h1>Hola, <?= h($name) ?></h1>
  <?php if (!$stats['started']): ?>
    <p class="hero-sub">Tu programa empieza el <?= h(fecha_larga($adv['start_date'])) ?>.</p>
  <?php elseif ($stats['finished']): ?>
    <p class="hero-sub">Terminaste tus 5 semanas.</p>
  <?php else: ?>
    <p class="hero-sub">Semana <?= (int)$week ?> · <?= h(ctx_label($ctx)) ?></p>
  <?php endif; ?>
  <svg class="thread" viewBox="0 0 400 30" preserveAspectRatio="none" aria-hidden="true"><path d="M0 20 C 60 0, 110 30, 170 14 S 280 4, 330 18 S 390 22, 400 10" fill="none" stroke="#B89860" stroke-width="2"/></svg>
</header>

<?php if (!$stats['started']): ?>
  <div class="card"><p>Cuando llegue tu primer día, aquí verás tus actividades. Guarda este enlace en tu pantalla de inicio.</p></div>
  <?php install_hint(); page_end(); exit; ?>
<?php endif; ?>

<?php if ($stats['finished']): ?>
  <?php $ev = $stats['evaluation']; ?>
  <div class="card">
    <h2>Tu evaluación final: <?= (int)$ev['total'] ?> puntos</h2>
    <p class="lead"><?= h($ev['level_text']) ?></p>
    <?php eval_table($ev); ?>
    <p>Gracias por seguir el hilo. Tu plan de los próximos 30 días lo defines con tu Team Leader.</p>
  </div>
  <?php page_end(); exit; ?>
<?php endif; ?>

<section class="progress card">
  <div class="prog-row"><span>Tu semana</span><b><?= $weekPct ?>%</b></div>
  <?= bar($weekPct) ?>
  <div class="prog-row"><span>Tus 5 semanas · Semana <?= (int)$week ?> de 5</span><b><?= $programPct ?>%</b></div>
  <?= bar($programPct, 'gold') ?>
</section>

<form method="post" enctype="multipart/form-data" class="dayform" id="dayform">
<?= csrf_field() ?><input type="hidden" name="t" value="<?= h($t) ?>"><input type="hidden" name="action" value="save">

<?php if ($ctx['type'] === 'rest'): ?>
  <div class="card rest">
    <h2>Hoy descansas, pero no sueltes el hilo.</h2>
    <p>Si conoces a alguien que quiera comprar, vender o rentar, anótalo aquí.</p>
    <?php num_field('contactos', 'Contactos', (int)($log['contactos'] ?? 0), '¿A cuántas personas detectaste?'); ?>
    <input type="hidden" name="n[tratos]" value="<?= (int)($log['tratos'] ?? 0) ?>">
    <input type="hidden" name="n[analizadas]" value="<?= (int)($log['analizadas'] ?? 0) ?>">
    <input type="hidden" name="n[promovidas]" value="<?= (int)($log['promovidas'] ?? 0) ?>">
    <input type="hidden" name="n[citas]" value="<?= (int)($log['citas'] ?? 0) ?>">
    <input type="hidden" name="n[captaciones]" value="<?= (int)($log['captaciones'] ?? 0) ?>">
    <label class="lbl">¿Quién es y qué busca? (opcional)
      <textarea name="note" rows="3" placeholder="Ej. Mi primo quiere vender su casa en la Región 15"><?= h($log['note'] ?? '') ?></textarea></label>
    <button class="btn btn-main btn-block">Guardar</button>
  </div>
<?php else: ?>

  <?php if ($pw && $ctx['type'] !== 'training'): ?>
    <div class="box objective"><span class="eyebrow">Semana <?= (int)$week ?> · <?= h($pw['title']) ?></span><p><?= h($pw['objective']) ?></p></div>
  <?php endif; ?>
  <?php if ($ctx['type'] === 'training'): ?>
    <div class="box objective"><span class="eyebrow">Hoy</span><p>Hoy tienes tu capacitación interna en la oficina. Tus actividades del programa siguen el próximo día laborable.</p></div>
  <?php elseif ($ctx['type'] === 'catchup'): ?>
    <div class="box objective"><span class="eyebrow">Día extra</span><p>Hoy es un buen día para ponerte al corriente con lo que te quedó pendiente de la semana.</p></div>
  <?php endif; ?>

  <h2 class="sec">Tus actividades de hoy</h2>
  <?php if (!$items): ?>
    <div class="card"><p class="muted">Hoy no tienes actividades del programa. Registra tus resultados si hiciste contactos.</p></div>
  <?php endif; ?>
  <ol class="acts">
  <?php foreach ($items as $it):
      $li = $log['items'][$it['key']] ?? null;
      $checked = $li && (int)$li['done'] === 1;
      $k = h($it['key']);
  ?>
    <li class="act<?= $checked ? ' is-done' : '' ?>">
      <label class="act-main">
        <input type="checkbox" name="done[<?= $k ?>]" value="1" <?= $checked ? 'checked' : '' ?>>
        <span class="tick" aria-hidden="true"></span>
        <span class="act-text">
          <?php if ($it['origin']): ?><span class="eyebrow"><?= h($it['origin']) ?></span><?php endif; ?>
          <span class="act-title"><?= h($it['title']) ?></span>
          <?php if ($it['minutes']): ?><span class="chip"><?= h(minutes_label($it['minutes'])) ?></span><?php endif; ?>
        </span>
      </label>
      <details class="act-more"<?= $it['kind'] === 'repaso' ? ' open' : '' ?>>
        <summary>Cómo hacerla y anotar resultado</summary>
        <?php if ($it['guide']): ?><p class="guide"><?= nl2br(h($it['guide'])) ?></p><?php endif; ?>
        <?php if ($it['kind'] === 'agenda'): agenda_list($week); endif; ?>
        <?php if ($it['kind'] === 'repaso'): repaso_block($adv, $stats, $week); endif; ?>
        <label class="lbl">Resultado
          <textarea name="res[<?= $k ?>]" rows="2" placeholder="Escribe aquí cómo te fue"><?= h($li['result_text'] ?? '') ?></textarea></label>
        <?php if ($it['kind'] === 'foto'): ?>
          <label class="lbl file">Foto <input type="file" name="photo[<?= $k ?>]" accept="image/*"></label>
          <?php if (!empty($li['photo'])): ?><p class="small ok">✓ Ya subiste una foto. Si subes otra, se reemplaza.</p><?php endif; ?>
        <?php endif; ?>
      </details>
    </li>
  <?php endforeach; ?>
  </ol>

  <h2 class="sec">Tus resultados de hoy</h2>
  <div class="card nums">
    <?php foreach (RESULT_FIELDS as $f => $label) num_field($f, $label, (int)($log[$f] ?? 0), RESULT_HINTS[$f]); ?>
  </div>

  <details class="card logros">
    <summary><b>Mis logros</b> <span class="muted small">Open House, captación y renta</span></summary>
    <div class="logros-in">
      <label class="check"><input type="checkbox" name="logros[oh_done]" value="1" <?= (int)$adv['oh_done'] ? 'checked' : '' ?>> Ya realicé mi Open House</label>
      <div class="grid2">
        <label class="lbl">Fecha<input type="date" name="logros[oh_date]" value="<?= h($adv['oh_date'] ?? '') ?>"></label>
        <label class="lbl">Asistentes reales<input type="number" min="0" inputmode="numeric" name="logros[oh_attendees]" value="<?= (int)$adv['oh_attendees'] ?>"></label>
      </div>
      <p class="small muted">Si captaste una propiedad, marca lo que ya tienes completo:</p>
      <label class="check"><input type="checkbox" name="logros[cap_precio]" value="1" <?= (int)$adv['cap_precio'] ? 'checked' : '' ?>> Precio en valor de mercado</label>
      <label class="check"><input type="checkbox" name="logros[cap_docs]" value="1" <?= (int)$adv['cap_docs'] ? 'checked' : '' ?>> Documentos completos</label>
      <label class="check"><input type="checkbox" name="logros[cap_disp]" value="1" <?= (int)$adv['cap_disp'] ? 'checked' : '' ?>> Disponibilidad para mostrarla</label>
      <label class="check"><input type="checkbox" name="logros[renta_closed]" value="1" <?= (int)$adv['renta_closed'] ? 'checked' : '' ?>> Cerré una renta</label>
      <label class="lbl">Fecha de la renta<input type="date" name="logros[renta_date]" value="<?= h($adv['renta_date'] ?? '') ?>"></label>
    </div>
  </details>

  <div class="savebar"><button class="btn btn-main btn-block btn-xl"><?= $log ? 'Actualizar mi día' : 'Guardar mi día' ?></button></div>
<?php endif; ?>
</form>

<?php if ($ctx['type'] !== 'rest'): ?>
<details class="card">
  <summary><b>Mi avance en el programa</b></summary>
  <?php my_progress($stats); ?>
</details>
<?php if ($pw && trim((string)$pw['remember']) !== ''): ?>
<div class="box remember"><span class="eyebrow">Lo que debes recordar</span>
  <ul><?php foreach (array_filter(explode("\n", $pw['remember'])) as $r) echo '<li>' . h($r) . '</li>'; ?></ul>
</div>
<?php endif; ?>
<?php endif; ?>
<?php install_hint(); ?>
<?php
page_end();

// ---------------------------------------------------------------------------
// Componentes de la pantalla
// ---------------------------------------------------------------------------
function num_field(string $f, string $label, int $value, string $hint): void
{
    echo '<div class="num"><div class="num-l"><span class="num-label">' . h($label) . '</span><span class="num-hint">' . h($hint) . '</span></div>'
        . '<div class="stepper"><button type="button" class="st-btn" data-step="-1" aria-label="Menos">−</button>'
        . '<input type="number" min="0" max="999" inputmode="numeric" name="n[' . h($f) . ']" value="' . $value . '" aria-label="' . h($label) . '">'
        . '<button type="button" class="st-btn" data-step="1" aria-label="Más">+</button></div></div>';
}

function agenda_list(int $week): void
{
    $list = week_time_list($week);
    if (!$list) return;
    echo '<ul class="agenda">';
    foreach ($list as $a) {
        echo '<li><span class="eyebrow">Día ' . (int)$a['day'] . '</span> ' . h($a['title'])
            . ($a['minutes'] ? ' <span class="chip">' . h(minutes_label($a['minutes'])) . '</span>' : '') . '</li>';
    }
    echo '</ul>';
}

function repaso_block(array $adv, array $stats, int $week): void
{
    $qs = array_values(array_filter(array_map('trim', explode("\n", (string)(plan_weeks()[$week]['review_questions'] ?? '')))));
    $saved = json_decode((string)val('SELECT answers FROM week_reviews WHERE advisor_id = ? AND week_no = ?', [$adv['id'], $week]), true) ?: [];
    if ($week === 2) {
        $c = cum_totals($stats, 2);
        $minC = (int)setting('s2_min_contactos'); $minP = (int)setting('s2_min_promovidas');
        echo '<div class="mini"><b>Tus mínimos (semanas 1 y 2)</b><ul>'
            . '<li>' . ($c['contactos'] >= $minC ? '✅' : '⬜') . ' Contactos a tu círculo cercano: ' . $c['contactos'] . ' de ' . $minC . '</li>'
            . '<li>' . ($c['promovidas'] >= $minP ? '✅' : '⬜') . ' Propiedades de la oficina promovidas: ' . $c['promovidas'] . ' de ' . $minP . '</li>'
            . '<li>⬜ Redes creadas con 3 contenidos (anótalo en el resultado)</li></ul></div>';
    }
    if ($week === 3) {
        $cp = checkpoint($adv, $stats);
        echo '<div class="mini"><b>Tu punto de control a hoy</b><ul>';
        foreach ($cp['checks'] as [$label, $ok]) echo '<li>' . ($ok ? '✅' : '⬜') . ' ' . h($label) . '</li>';
        echo '</ul></div>';
    }
    if ($week === 5) {
        $ev = $stats['evaluation'];
        echo '<div class="mini"><b>Tu evaluación a hoy: ' . (int)$ev['total'] . ' de 100 puntos</b><p class="small">' . h($ev['level_text']) . '</p></div>';
    }
    foreach ($qs as $i => $qq) {
        echo '<label class="lbl">' . h(($i + 1) . '. ' . $qq) . '<textarea name="review[' . $i . ']" rows="2">' . h($saved[$i] ?? '') . '</textarea></label>';
    }
}

function my_progress(array $s): void
{
    kpi_grid($s);
    echo '<p class="small muted">Actividades cumplidas hasta hoy: ' . $s['done'] . ' de ' . $s['assigned'] . ' (' . $s['pct'] . '%). Evaluación estimada: ' . (int)$s['evaluation']['total'] . ' de 100 puntos.</p>';
}

function install_hint(): void
{
    echo '<p class="hint small center muted" id="a2hs">Para tenerla a la mano: en tu navegador toca <b>Compartir</b> o <b>⋮</b> y elige <b>Agregar a pantalla de inicio</b>.</p>';
}
