<?php
/**
 * ARIADNA · Sesiones de la oficina: fecha y hora de cada tema, y Open House o Recorrido en vivo.
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';

enforce_https();
$u = require_user();
$office = panel_office($u);
$qs = office_qs($office);
$self = 'sesiones.php' . ($qs ? '?' . $qs : '');
$topics = office_activities();
$topicIds = array_map(fn($a) => (int)$a['id'], $topics);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = post('action');
    if ($act === 'add') {
        $aid = (int)post('activity_id');
        $date = post('sdate');
        $time = post('stime');
        if (!in_array($aid, $topicIds, true) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            flash('Elige el tema y la fecha.', 'err');
        } else {
            q('INSERT INTO office_sessions (office_id, activity_id, sdate, stime, who, place, repeat_weekly) VALUES (?,?,?,?,?,?,?)', [
                $office['id'], $aid, $date, preg_match('/^\d{2}:\d{2}$/', $time) ? $time : null,
                mb_substr(post('who'), 0, 150) ?: null, mb_substr(post('place'), 0, 150) ?: null, !empty($_POST['repeat']) ? 1 : 0]);
            flash('Sesión guardada. Ariadna se la muestra a cada asesor en su día.');
        }
    } elseif ($act === 'del') {
        q('DELETE FROM office_sessions WHERE id = ? AND office_id = ?', [(int)post('sid'), $office['id']]);
        flash('Sesión eliminada.');
    } elseif ($act === 'oh') {
        q('UPDATE offices SET oh_mode = ? WHERE id = ?', [post('oh_mode') === 'recorrido' ? 'recorrido' : 'open_house', $office['id']]);
        flash('Guardado.');
    }
    redirect($self);
}

$today = office_today($office);
$rows = office_advisors_stats($office);
$need = [];
foreach (office_unscheduled($office, $rows) as $n) $need[(int)$n['activity']['id']] = $n['names'];
// Quién la necesita aunque ya tenga fecha (asesores en esa semana o antes, que no la han tomado)
$waiting = [];
foreach ($rows as $r) {
    $s = $r['stats'];
    if (!$s['started'] || $s['finished']) continue;
    $done = done_keys($s['logs']);
    foreach ($topics as $t) {
        if ((int)$t['week_no'] > $s['week'] + 1 || in_array('A' . $t['id'], $done, true)) continue;
        $occ = $s['sessions'][(int)$t['id']] ?? null;
        if ($occ) $waiting[(int)$t['id']][] = first_name($r['adv']['name']) . ' (' . fecha_corta($occ['date']) . ')';
    }
}
$scheduled = all('SELECT * FROM office_sessions WHERE office_id = ? AND (repeat_weekly = 1 OR sdate >= ?) ORDER BY sdate, stime', [$office['id'], $today]);
$byTopic = [];
foreach ($scheduled as $s) $byTopic[(int)$s['activity_id']][] = $s;
$staff = all('SELECT name, role FROM users WHERE office_id = ? AND active = 1 ORDER BY name', [$office['id']]);

page_start('Sesiones', ['nav' => $u, 'office' => $office, 'active' => 'sesiones', 'wide' => true]);
$hidden = is_admin($u) ? '<input type="hidden" name="office" value="' . (int)$office['id'] . '">' : '';
?>
<div class="page-head"><div><span class="eyebrow">Panel de la oficina</span><h1>Sesiones de tu oficina</h1>
  <p class="muted" style="margin:4px 0 0">Estos temas del curso Fast Track Residencial los da el Team Leader o el gerente de tu oficina. Programa el día y la hora: como entran asesores nuevos continuamente, Ariadna asigna a cada uno la siguiente fecha cuando llega a esa semana.</p></div></div>

<?php if ($need): ?>
  <div class="box warn"><b><?= count($need) ?> <?= count($need) === 1 ? 'tema sin fecha que tus asesores ya necesitan' : 'temas sin fecha que tus asesores ya necesitan' ?>:</b>
    <?php $parts = []; foreach ($topics as $t) if (isset($need[(int)$t['id']])) $parts[] = h($t['title']) . ' (' . h(implode(', ', $need[(int)$t['id']])) . ')'; echo implode(' · ', $parts); ?></div>
<?php endif; ?>

<div class="tbl-wrap">
<table class="tbl responsive">
  <thead><tr><th>Sem.</th><th>Tema</th><th>Duración</th><th>Fechas programadas</th><th>Asesores</th></tr></thead>
  <tbody>
  <?php foreach ($topics as $t): $tid = (int)$t['id']; ?>
    <tr>
      <td data-l="Semana"><?= (int)$t['week_no'] ?></td>
      <td class="first"><b><?= h($t['title']) ?></b></td>
      <td data-l="Duración"><?= h(minutes_label($t['minutes'] !== null ? (int)$t['minutes'] : null) ?: '—') ?></td>
      <td data-l="Fechas">
        <?php if (empty($byTopic[$tid])): ?><b style="color:var(--terra-d)">Sin fecha</b><?php endif; ?>
        <?php foreach ($byTopic[$tid] ?? [] as $s): ?>
          <div class="small" style="margin-bottom:4px"><?= (int)$s['repeat_weekly'] ? 'Cada ' . h(DIAS[iso_weekday($s['sdate'])]) : h(fecha_larga($s['sdate'])) ?><?= $s['stime'] ? ' · ' . h(time_label($s['stime'])) : '' ?><?= $s['who'] ? ' · ' . h($s['who']) : '' ?><?= $s['place'] ? ' · ' . h($s['place']) : '' ?>
            <form method="post" class="inline-form" data-confirm="¿Quitar esta fecha?"><?= csrf_field() . $hidden ?><input type="hidden" name="action" value="del"><input type="hidden" name="sid" value="<?= (int)$s['id'] ?>"><button class="btn btn-sm btn-ghost">Quitar</button></form></div>
        <?php endforeach; ?>
      </td>
      <td data-l="Asesores" class="small"><?= isset($need[$tid]) ? '<b style="color:var(--terra-d)">Sin fecha: ' . h(implode(', ', $need[$tid])) . '</b><br>' : '' ?><?= h(implode(', ', $waiting[$tid] ?? [])) ?: (isset($need[$tid]) ? '' : '—') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<div class="cols">
  <div class="card">
    <h2>Programar sesión</h2>
    <form method="post" class="form">
      <?= csrf_field() . $hidden ?><input type="hidden" name="action" value="add">
      <label>Tema<select name="activity_id" required><option value="">Elige el tema</option>
        <?php foreach ($topics as $t): ?><option value="<?= (int)$t['id'] ?>"<?= isset($need[(int)$t['id']]) ? ' style="font-weight:bold"' : '' ?>>Semana <?= (int)$t['week_no'] ?> · <?= h($t['title']) ?><?= isset($need[(int)$t['id']]) ? ' (sin fecha)' : '' ?></option><?php endforeach; ?>
      </select></label>
      <div class="grid2">
        <label>Fecha<input type="date" name="sdate" required min="<?= h($today) ?>"></label>
        <label>Hora<input type="time" name="stime" value="10:00"></label>
      </div>
      <label>Quién la da<input name="who" list="staff" placeholder="Ej. Ricardo Salas (gerente)"></label>
      <datalist id="staff"><?php foreach ($staff as $p) echo '<option value="' . h($p['name'] . ' (' . mb_strtolower(ROLE_LABELS[$p['role']]) . ')') . '">'; ?></datalist>
      <label>Lugar (opcional)<input name="place" placeholder="Ej. Sala de juntas"></label>
      <label class="check"><input type="checkbox" name="repeat" value="1" checked> Repetir cada semana el mismo día y hora</label>
      <button class="btn btn-main">Guardar sesión</button>
    </form>
  </div>
  <div class="card">
    <h2>Open House</h2>
    <p>En la Semana 3 cada asesor hace su primer evento con compradores, supervisado por el Team Leader o el gerente de la oficina. Elige qué hace tu oficina:</p>
    <form method="post" class="form">
      <?= csrf_field() . $hidden ?><input type="hidden" name="action" value="oh">
      <label class="check"><input type="radio" name="oh_mode" value="open_house"<?= $office['oh_mode'] !== 'recorrido' ? ' checked' : '' ?>> <span><b>Open House</b><br><span class="small muted">Puertas abiertas en una propiedad del inventario. Mínimo 5 asistentes reales.</span></span></label>
      <label class="check"><input type="radio" name="oh_mode" value="recorrido"<?= $office['oh_mode'] === 'recorrido' ? ' checked' : '' ?>> <span><b>Recorrido en vivo</b><br><span class="small muted">El asesor muestra en vivo una propiedad del inventario (transmisión en Facebook o Instagram, o recorrido presencial con interesados). Cuentan las personas interesadas que dejan sus datos: mínimo 5.</span></span></label>
      <p class="small muted">Los dos valen lo mismo en la evaluación de 100 puntos.</p>
      <button class="btn btn-main">Guardar</button>
    </form>
  </div>
</div>
<?php page_end();
