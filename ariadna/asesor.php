<?php
/**
 * ARIADNA · Detalle de un asesor (panel de la oficina).
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';

enforce_https();
$u = require_user();
$office = panel_office($u);
$qs = office_qs($office);
$amp = $qs ? '&' . $qs : '';
$adv = office_advisor($office, (int)($_GET['id'] ?? $_POST['id'] ?? 0));
$self = 'asesor.php?id=' . (int)$adv['id'] . $amp;
// Dar de baja: solo el director y el gerente (y el administrador). La decisión del punto de control la registra cualquiera de los tres.
$canDecide = in_array($u['role'], ['admin', 'director', 'gerente'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = post('action');
    if ($act === 'edit') {
        $name = post('name'); $phone = post('phone'); $email = mb_strtolower(post('email'));
        $start = post('start_date');
        $wd = array_map('intval', (array)($_POST['workdays'] ?? []));
        $override = !empty($_POST['custom_days']) && $wd ? implode(',', parse_workdays(implode(',', $wd))) : null;
        $tmode = post('training_mode');
        $tdate = post('training_date');
        if ($name === '' || strlen(preg_replace('/\D/', '', $phone)) < 10 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)
            || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))) {
            flash('Revisa nombre, celular (10 dígitos), correo y fecha de inicio.', 'err');
            redirect($self);
        }
        q('UPDATE advisors SET name = ?, phone = ?, email = ?, start_date = ?, workdays_override = ? WHERE id = ?',
            [$name, $phone, $email ?: null, $start, $override, $adv['id']]);
        if ($tmode === 'manual' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tdate)) {
            q('UPDATE advisors SET training_date = ?, training_manual = 1 WHERE id = ?', [$tdate, $adv['id']]);
        } else {
            $a2 = one('SELECT * FROM advisors WHERE id = ?', [$adv['id']]);
            q('UPDATE advisors SET training_date = ?, training_manual = 0 WHERE id = ?', [find_training_date($office, $a2), $adv['id']]);
        }
        flash('Cambios guardados.');
    } elseif ($act === 'regen') {
        q('UPDATE advisors SET token = ? WHERE id = ?', [new_token(), $adv['id']]);
        flash('Se generó un enlace nuevo. El anterior ya no funciona: envíale el nuevo por WhatsApp.');
    } elseif ($act === 'reset_pin') {
        q('UPDATE advisors SET pin_hash = NULL WHERE id = ?', [$adv['id']]);
        q('DELETE FROM login_attempts WHERE k = ?', ['pin:' . $adv['id']]);
        flash('PIN restablecido. La próxima vez que entre creará uno nuevo.');
    } elseif ($act === 'toggle' && !$canDecide) {
        flash('Solo el director o el gerente pueden dar de baja a un asesor.', 'err');
    } elseif ($act === 'toggle') {
        $note = mb_substr(post('note'), 0, 1000);
        if ((int)$adv['active'] && $note === '') {
            flash('Para dar de baja escribe el motivo.', 'err');
        } else {
            q('UPDATE advisors SET active = 1 - active WHERE id = ?', [$adv['id']]);
            advisor_event((int)$adv['id'], $u, (int)$adv['active'] ? 'baja' : 'reactivacion', $note);
            flash((int)$adv['active'] ? 'Asesor dado de baja. Su enlace deja de funcionar.' : 'Asesor reactivado.');
        }
    } elseif ($act === 'decision') {
        $d = post('decision');
        $note = mb_substr(post('decision_note'), 0, 500);
        if ($d === 'no_continua' && $note === '') {
            flash('Para marcar «No continúa» escribe el motivo en el comentario.', 'err');
        } elseif (in_array($d, ['continua', 'no_continua', 'pendiente'], true)) {
            q('UPDATE advisors SET decision = ?, decision_note = ?, decision_by = ?, decision_at = NOW() WHERE id = ?',
                [$d, $note, $u['id'], $adv['id']]);
            advisor_event((int)$adv['id'], $u, 'decision_' . $d, $note);
            flash('Decisión registrada.');
        }
    }
    redirect($self);
}

$s = advisor_stats($adv, $office);
$st = $s['status'];
$wdOffice = parse_workdays($office['workdays']);
$wdAdv = advisor_workdays($adv, $office);
$reviews = [];
foreach (all('SELECT * FROM week_reviews WHERE advisor_id = ?', [$adv['id']]) as $r) $reviews[(int)$r['week_no']] = json_decode((string)$r['answers'], true) ?: [];

// Nombres de actividades para el historial
$titles = ['CI' => 'Capacitación interna'];
foreach (routine_items() as $r) $titles[$r['key']] = $r['title'];
foreach (all('SELECT id, title FROM plan_activities') as $r) $titles['A' . $r['id']] = $r['title'];

page_start($adv['name'], ['nav' => $u, 'office' => $office, 'active' => 'oficina', 'wide' => true]);
?>
<p><a href="oficina.php<?= $qs ? '?' . $qs : '' ?>">← Todos los asesores</a></p>
<div class="page-head">
  <div>
    <span class="eyebrow"><?= !$s['started'] ? 'Inicia el ' . h(fecha_larga($adv['start_date'])) : ($s['finished'] ? 'Terminó el programa' : 'Semana ' . (int)$s['week'] . ' · ' . h($s['ctx'] ? ctx_label($s['ctx']) : '')) ?></span>
    <h1><?= h($adv['name']) ?> <?= status_badge($st['color'], $st['reason']) ?></h1>
    <p class="muted"><?= h($st['reason']) ?><?= !(int)$adv['active'] ? ' · <b>Dado de baja</b>' : '' ?></p>
  </div>
  <div class="actions">
    <a class="btn btn-wa" href="<?= h(wa_link($adv, $office)) ?>" target="_blank" rel="noopener">Enviar enlace por WhatsApp</a>
    <button class="btn" type="button" data-copy="advlink">Copiar enlace</button>
  </div>
</div>
<input type="text" id="advlink" value="<?= h(advisor_link($adv)) ?>" readonly class="small" style="margin-bottom:16px">

<?php if ($st['action']): ?><div class="box warn"><b>Ariadna te sugiere:</b> <?= h($st['action']) ?></div><?php endif; ?>

<?php if ($s['closed_weeks'] >= 3 || $adv['decision']): $cp = checkpoint($adv, $s, $office); ?>
<div class="card">
  <h2>Punto de control (Semana 3): <?= $cp['pass'] ? '<span class="ok">Cumple</span>' : '<span style="color:var(--red)">No cumple</span>' ?></h2>
  <ul><?php foreach ($cp['checks'] as [$l, $ok]) echo '<li>' . ($ok ? '✅' : '⬜') . ' ' . h($l) . '</li>'; ?></ul>
  <form method="post" class="form">
    <?= csrf_field() ?><input type="hidden" name="action" value="decision"><input type="hidden" name="id" value="<?= (int)$adv['id'] ?>">
    <?php if (is_admin($u)): ?><input type="hidden" name="office" value="<?= (int)$office['id'] ?>"><?php endif; ?>
    <label>¿Continúa en el programa?
      <select name="decision">
        <option value="pendiente" <?= ($adv['decision'] ?? 'pendiente') === 'pendiente' ? 'selected' : '' ?>>Pendiente de decidir</option>
        <option value="continua" <?= $adv['decision'] === 'continua' ? 'selected' : '' ?>>Sí, continúa</option>
        <option value="no_continua" <?= $adv['decision'] === 'no_continua' ? 'selected' : '' ?>>No continúa (su enlace se pausa)</option>
      </select></label>
    <label>Comentario (obligatorio si no continúa: ¿por qué?)<input name="decision_note" maxlength="500" value="<?= h($adv['decision_note'] ?? '') ?>"></label>
    <button class="btn btn-main">Guardar decisión</button>
  </form>
</div>
<?php endif; ?>

<?php $rt = $s['retos']; if ($rt['total']): ?>
<div class="card" id="retos">
  <h2>Retos del video <span class="small muted">· <?= h(reto_points_label($rt['points'])) ?> de <?= (int)$rt['total'] ?> puntos</span></h2>
  <p class="small muted">Después de cada video, su Team Leader le hace 5 preguntas de palabra. 4 o 5 de 5 = 1 punto · 3 de 5 = medio punto. Valen 10 puntos de su evaluación.</p>
  <div class="tbl-wrap"><table class="tbl responsive">
    <thead><tr><th>Video</th><th>Cuándo</th><th>Resultado</th><th>Lo aplicó</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rt['list'] as $r): $row = $r['row']; ?>
      <tr>
        <td class="first"><?= h($r['label']) ?></td>
        <td data-l="Cuándo">Semana <?= (int)$r['week'] ?><?= $r['date'] ? ' · ' . h(fecha_corta($r['date'])) : '' ?></td>
        <td data-l="Resultado"><?php if ($row): ?><span><b><?= (int)$row['ok_count'] ?> de 5</b><?= (int)$row['ok_count'] >= 4 ? ' · superado' : ((int)$row['ok_count'] === 3 ? ' · medio punto' : ' · repasar') ?></span><?php elseif ($r['due']): ?><span style="color:var(--red)">Pendiente</span><?php else: ?><span class="muted">Más adelante</span><?php endif; ?></td>
        <td data-l="Lo aplicó"><?= $row ? h(($row['user_name'] ?? '') . ' · ' . fecha_corta(substr((string)$row['updated_at'], 0, 10))) : '—' ?></td>
        <td data-l=""><?php if ($row || $r['due']): ?><a class="btn btn-sm<?= $row ? ' btn-ghost' : ' btn-main' ?>" href="reto.php?id=<?= (int)$adv['id'] ?>&amp;a=<?= (int)$r['activity']['id'] . h($amp) ?>"><?= $row ? 'Volver a aplicar' : 'Aplicar Reto' ?></a><?php endif; ?></td>
      </tr>
      <?php if ($row && trim((string)$row['note']) !== ''): ?><tr><td colspan="5" class="small muted">Comentario: <?= h($row['note']) ?></td></tr><?php endif; ?>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
<?php endif; ?>

<div class="cols">
  <div class="card">
    <h2>Resultados acumulados</h2>
    <?php kpi_grid($s); ?>
    <p class="small muted">Actividades cumplidas: <?= $s['done'] ?> de <?= $s['assigned'] ?> (<?= $s['pct'] ?>%) · Días registrados: <?= $s['workdays_logged'] ?> de <?= $s['workdays_elapsed'] ?></p>
    <h3>Citas con su Team Leader en la oficina</h3>
    <p class="small"><?= $s['tl']['due'] ? 'Fue a <b>' . (int)$s['tl']['done'] . ' de ' . (int)$s['tl']['due'] . '</b> citas que ya le tocaban' : 'Aún no le toca ninguna' ?> (son <?= (int)$s['tl']['total'] ?> en el curso).</p>
    <?php if ($s['tl']['missed']): ?><ul class="small"><?php foreach ($s['tl']['missed'] as $c): ?><li>⬜ Semana <?= (int)$c['week'] ?> · <?= h(fecha_corta($c['date'])) ?> · <?= h($c['title']) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <h3>Logros</h3>
    <ul>
      <li><?= h(oh_label($office)) ?>: <?= (int)$adv['oh_done'] ? 'Sí, ' . h(fecha_corta($adv['oh_date'])) . ' · ' . (int)$adv['oh_attendees'] . ' ' . OH_MODES[oh_mode($office)]['plural'] : 'Aún no' ?></li>
      <li>Calidad de la captación: precio <?= (int)$adv['cap_precio'] ? '✅' : '⬜' ?> · documentos <?= (int)$adv['cap_docs'] ? '✅' : '⬜' ?> · disponibilidad <?= (int)$adv['cap_disp'] ? '✅' : '⬜' ?></li>
      <li>Renta cerrada: <?= (int)$adv['renta_closed'] ? 'Sí ' . h(fecha_corta($adv['renta_date'])) : 'Aún no' ?></li>
    </ul>
  </div>
  <div class="card">
    <h2>Evaluación: <?= (int)$s['evaluation']['total'] ?> / 100</h2>
    <p class="muted"><?= h($s['evaluation']['level_text']) ?></p>
    <div class="tbl-wrap"><?php eval_table($s['evaluation']); ?></div>
  </div>
</div>

<h2 class="sec">Metas de cada semana</h2>
<div class="tbl-wrap">
<table class="tbl">
  <thead><tr><th>Semana</th><?php foreach (RESULT_FIELDS as $f => $l) echo '<th>' . h($f === 'contactos' ? 'Contactos al día' : $l) . '</th>'; ?><th></th></tr></thead>
  <tbody>
  <?php for ($w = 2; $w <= PROGRAM_WEEKS; $w++): $g = $s['goals'][$w] ?? null; ?>
    <tr><td><b>S<?= $w ?></b></td>
      <?php foreach (RESULT_FIELDS as $f => $l): $min = goal_minimum($w, $f); ?>
        <td class="n"><?php if ($g): ?><span style="<?= (int)$g[$f] < $min ? 'color:var(--terra-d);font-weight:700' : '' ?>"><?= (int)$g[$f] ?></span><?php else: ?><span class="muted">—</span><?php endif; ?><span class="small muted"> / mín. <?= $min ?></span></td>
      <?php endforeach; ?>
      <td class="small"><?= $g && (int)$g['low_accepted'] ? '<b style="color:var(--terra-d)">Metas bajas</b>' : ($g ? '' : '<span class="muted">Aún no las pone</span>') ?></td></tr>
  <?php endfor; ?>
  </tbody>
</table>
</div>

<h2 class="sec">Historial diario</h2>
<div class="tbl-wrap">
<table class="tbl">
  <thead><tr><th>Fecha</th><th>Día</th><th>Actividades y resultados</th><th>Números</th></tr></thead>
  <tbody>
  <?php
  $dates = array_keys($s['schedule']);
  rsort($dates);
  $shown = 0;
  foreach ($dates as $d):
      if ($d > $s['today']) continue;
      $ctx = $s['schedule'][$d];
      $l = $s['logs'][$d] ?? null;
      if ($ctx['type'] === 'rest' && !$l) continue;
      $shown++;
  ?>
    <tr>
      <td><b><?= h(fecha_corta($d)) ?></b><br><span class="small muted"><?= h(DIAS[iso_weekday($d)]) ?></span></td>
      <td>S<?= (int)$ctx['week'] ?> · <?= h(ctx_label($ctx)) ?></td>
      <td>
        <?php if (!$l): ?>
          <span class="muted"><?= $d === $s['today'] ? 'Aún no registra hoy' : 'Sin registro' ?></span>
        <?php else: ?>
          <?php foreach ($l['items'] as $k => $it): if (!(int)$it['done'] && !$it['result_text'] && !$it['photo']) continue; ?>
            <div class="small"><?= (int)$it['done'] ? '✅' : '⬜' ?> <?= h($titles[$k] ?? $k) ?>
              <?php if ($it['result_text']): ?><br><span class="muted">“<?= h($it['result_text']) ?>”</span><?php endif; ?>
              <?php if ($it['photo']): ?><br><a href="foto.php?id=<?= (int)$it['id'] ?>" target="_blank"><img class="photo-thumb" src="foto.php?id=<?= (int)$it['id'] ?>" alt="Foto"></a><?php endif; ?>
            </div>
          <?php endforeach; ?>
          <?php if (trim((string)($l['analisis'] ?? '')) !== ''): ?><div class="small">🏠 <b>Propiedades analizadas:</b> <?= h($l['analisis']) ?></div><?php endif; ?>
          <?php if (trim((string)$l['note']) !== ''): ?><div class="small">📝 <?= h($l['note']) ?></div><?php endif; ?>
        <?php endif; ?>
      </td>
      <td class="small"><?php if ($l) { $parts = []; foreach (RESULT_FIELDS as $f => $lab) if ((int)$l[$f]) $parts[] = $lab . ': ' . (int)$l[$f]; echo $parts ? h(implode(' · ', $parts)) : '—'; } ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$shown): ?><tr><td colspan="4" class="muted">Todavía no hay días que mostrar.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>

<?php if ($reviews): ?>
<h2 class="sec">Repasos semanales</h2>
<div class="cols">
<?php foreach ($reviews as $w => $ans):
    $qs2 = array_values(array_filter(array_map('trim', explode("\n", oh_text(plan_weeks()[$w]['review_questions'] ?? '', $office))))); ?>
  <div class="card"><h3>Semana <?= (int)$w ?></h3>
    <?php foreach ($qs2 as $i => $qq): ?><p class="small"><b><?= h($qq) ?></b><br><?= h($ans[$i] ?? '—') ?></p><?php endforeach; ?>
  </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
  <h2>Datos del asesor</h2>
  <form method="post" class="form">
    <?= csrf_field() ?><input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?= (int)$adv['id'] ?>">
    <?php if (is_admin($u)): ?><input type="hidden" name="office" value="<?= (int)$office['id'] ?>"><?php endif; ?>
    <div class="grid2">
      <label>Nombre<input name="name" required value="<?= h($adv['name']) ?>"></label>
      <label>Celular<input type="tel" name="phone" required value="<?= h($adv['phone']) ?>"></label>
      <label>Correo<input type="email" name="email" value="<?= h($adv['email'] ?? '') ?>"></label>
      <label>Fecha de inicio<input type="date" name="start_date" required value="<?= h($adv['start_date']) ?>"></label>
    </div>
    <label class="check"><input type="checkbox" name="custom_days" value="1" <?= $adv['workdays_override'] ? 'checked' : '' ?>> Este asesor tiene días hábiles distintos a los de la oficina (<?= h(workdays_label($wdOffice)) ?>)</label>
    <div class="days-pick">
      <?php foreach (DIAS as $n => $dn): ?><label><input type="checkbox" name="workdays[]" value="<?= $n ?>" <?= in_array($n, $wdAdv, true) ? 'checked' : '' ?>> <?= h(ucfirst(mb_substr($dn, 0, 3))) ?></label><?php endforeach; ?>
    </div>
    <label>Capacitación interna
      <select name="training_mode">
        <option value="auto" <?= !(int)$adv['training_manual'] ? 'selected' : '' ?>>Automática: siguiente fecha de la oficina<?= $adv['training_date'] && !(int)$adv['training_manual'] ? ' (' . h(fecha_larga($adv['training_date'])) . ')' : '' ?></option>
        <option value="manual" <?= (int)$adv['training_manual'] ? 'selected' : '' ?>>Elegir fecha a mano</option>
      </select></label>
    <label>Fecha (si eliges a mano)<input type="date" name="training_date" value="<?= h($adv['training_date'] ?? '') ?>"></label>
    <?php if (!$adv['training_date']): ?><p class="small" style="color:var(--terra-d)">Sin fecha de capacitación interna. Prográmala en Configuración.</p><?php endif; ?>
    <button class="btn btn-main">Guardar cambios</button>
  </form>
  <hr style="border:0;border-top:1px solid var(--line);margin:20px 0">
  <div class="actions">
    <?php foreach ([['regen', 'Generar enlace nuevo', '¿Generar un enlace nuevo? El anterior dejará de funcionar.'],
                    ['reset_pin', 'Restablecer PIN', '¿Restablecer el PIN de este asesor?'],
                    ] as [$a, $label, $conf]):
        if ($a === 'reset_pin' && !(int)$office['pin_enabled']) continue; ?>
      <form method="post" class="inline-form" data-confirm="<?= h($conf) ?>">
        <?= csrf_field() ?><input type="hidden" name="action" value="<?= $a ?>"><input type="hidden" name="id" value="<?= (int)$adv['id'] ?>">
        <?php if (is_admin($u)): ?><input type="hidden" name="office" value="<?= (int)$office['id'] ?>"><?php endif; ?>
        <button class="btn btn-sm <?= $a === 'toggle' && (int)$adv['active'] ? 'btn-danger' : 'btn-ghost' ?>"><?= h($label) ?></button>
      </form>
    <?php endforeach; ?>
  </div>
  <?php if (!$canDecide): ?>
    <p class="small muted" style="margin-top:12px">Solo el director o el gerente pueden dar de baja a un asesor.</p>
  <?php else: ?>
    <form method="post" class="form" style="margin-top:16px" data-confirm="<?= (int)$adv['active'] ? '¿Dar de baja a este asesor? Su enlace dejará de funcionar.' : '¿Reactivar a este asesor?' ?>">
      <?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$adv['id'] ?>">
      <?php if (is_admin($u)): ?><input type="hidden" name="office" value="<?= (int)$office['id'] ?>"><?php endif; ?>
      <label><?= (int)$adv['active'] ? 'Motivo de la baja (obligatorio)' : 'Comentario (opcional)' ?>
        <textarea name="note" rows="2" maxlength="1000"<?= (int)$adv['active'] ? ' required' : '' ?> placeholder="<?= (int)$adv['active'] ? 'Ej. Dejó la oficina; no registró actividad en 2 semanas…' : '' ?>"></textarea></label>
      <button class="btn btn-sm <?= (int)$adv['active'] ? 'btn-danger' : 'btn-ghost' ?>"><?= (int)$adv['active'] ? 'Dar de baja' : 'Reactivar' ?></button>
    </form>
  <?php endif; ?>
</div>

<?php
ensure_schema();
$events = all('SELECT * FROM advisor_events WHERE advisor_id = ? ORDER BY created_at DESC, id DESC', [$adv['id']]);
$evLabels = ['baja' => 'Dio de baja', 'reactivacion' => 'Reactivó', 'decision_continua' => 'Punto de control: sí continúa',
    'decision_no_continua' => 'Punto de control: no continúa', 'decision_pendiente' => 'Punto de control: pendiente de decidir'];
?>
<h2 class="sec">Movimientos</h2>
<div class="card">
  <?php if (!$events): ?><p class="muted">Aún no hay movimientos (bajas, reactivaciones o decisiones del punto de control).</p><?php endif; ?>
  <?php foreach ($events as $e): ?>
    <div class="plan-act">
      <b><?= h($evLabels[$e['kind']] ?? $e['kind']) ?></b>
      <span class="small muted"> · <?= h(fecha_corta(substr($e['created_at'], 0, 10))) ?> <?= h(substr($e['created_at'], 11, 5)) ?> · <?= h($e['user_name'] ?? '') ?> (<?= h(ROLE_LABELS[$e['user_role']] ?? $e['user_role']) ?>)</span>
      <?php if (trim((string)$e['note']) !== ''): ?><div>“<?= h($e['note']) ?>”</div><?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
<?php page_end();
