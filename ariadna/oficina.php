<?php
/**
 * ARIADNA · Panel de la oficina (director, gerente, team leader).
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';

enforce_https();
$u = require_user();
$office = panel_office($u);
$qs = office_qs($office);
$amp = $qs ? '&' . $qs : '';

// Alta de asesor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'add') {
    csrf_check();
    $name = post('name'); $phone = post('phone'); $email = mb_strtolower(post('email'));
    $start = post('start_date') ?: office_today($office);
    $err = '';
    if ($name === '') $err = 'Escribe el nombre del asesor.';
    elseif (strlen(preg_replace('/\D/', '', $phone)) < 10) $err = 'Escribe un celular de 10 dígitos.';
    elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'El correo no es válido.';
    elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) $err = 'La fecha de inicio no es válida.';
    if ($err) { flash($err, 'err'); redirect('oficina.php' . ($qs ? '?' . $qs : '') . '#alta'); }
    $prog = in_array(post('program'), office_programs($office), true) ? post('program') : office_programs($office)[0];
    $id = create_advisor($office, $name, $phone, $email ?: null, $start, $prog);
    flash('Listo, diste de alta a ' . $name . '. Ahora envíale su enlace por WhatsApp.');
    redirect('asesor.php?id=' . $id . $amp);
}

$rowsAll = office_advisors_stats($office);
// Pestañas Todos · Ariadna · Edalo (solo si la oficina tiene los dos programas)
$progs = office_programs($office);
$multi = count($progs) > 1;
$tab = $multi && in_array($_GET['p'] ?? '', $progs, true) ? $_GET['p'] : ($multi ? 'todos' : $progs[0]);
$rows = $tab === 'todos' ? $rowsAll : array_values(array_filter($rowsAll, fn($r) => advisor_program($r['adv']) === $tab));
$tabUrl = fn(string $p) => 'oficina.php?' . ltrim(($qs ? $qs . '&' : '') . 'p=' . $p, '&');
$counts = ['verde' => 0, 'amarillo' => 0, 'rojo' => 0];
$inProgram = 0;
foreach ($rows as $r) { $counts[$r['stats']['status']['color']]++; if (!$r['stats']['finished']) $inProgram++; }
$noTraining = advisors_without_training($office);
$reminder = daily_reminder_text($office);
$attention = array_values(array_filter($rows, fn($r) => $r['stats']['status']['action'] !== ''));
$unscheduled = office_unscheduled($office, $rows);
$waReport = weekly_report_text($office, $rowsAll);

page_start('Asesores', ['nav' => $u, 'office' => $office, 'active' => 'oficina', 'wide' => true]);
?>
<div class="page-head">
  <div><span class="eyebrow">Panel de la oficina</span><h1>Tus asesores</h1></div>
  <div class="actions">
    <button class="btn" type="button" data-copy="recordatorio">Copiar recordatorio del día</button>
    <button class="btn" type="button" data-copy="reporteWa">Copiar reporte para WhatsApp</button>
    <a class="btn btn-main" href="#alta">+ Alta de asesor</a>
  </div>
</div>

<?php if ($unscheduled): ?>
  <div class="box warn"><b><?= count($unscheduled) ?> <?= count($unscheduled) === 1 ? 'tema de oficina sin fecha' : 'temas de oficina sin fecha' ?></b> que tus asesores ya necesitan:
    <?= h(implode(' · ', array_map(fn($n) => $n['activity']['title'] . ' (' . implode(', ', $n['names']) . ')', $unscheduled))) ?>.
    <a href="sesiones.php<?= $qs ? '?' . $qs : '' ?>">Prográmalos aquí</a>.</div>
<?php endif; ?>
<?php if ($noTraining > 0): ?>
  <div class="box warn">Tienes <b><?= $noTraining ?></b> <?= $noTraining === 1 ? 'asesor nuevo' : 'asesores nuevos' ?> sin día de capacitación interna.
    <a href="config_oficina.php<?= $qs ? '?' . $qs : '' ?>">Prográmala aquí</a>.</div>
<?php endif; ?>

<?php if ($multi): ?>
<nav class="prog-tabs" aria-label="Programa">
  <a class="<?= $tab === 'todos' ? 'on' : '' ?>" href="<?= h($tabUrl('todos')) ?>">Todos · <?= count($rowsAll) ?></a>
  <?php foreach ($progs as $pk): $n = count(array_filter($rowsAll, fn($r) => advisor_program($r['adv']) === $pk)); ?>
    <a class="<?= $tab === $pk ? 'on' : '' ?>" href="<?= h($tabUrl($pk)) ?>"><?= h(P($pk)['name'] . ' · ' . P($pk)['area'] . ' · ' . $n) ?></a>
  <?php endforeach; ?>
</nav>
<?php endif; ?>
<div class="summary">
  <div class="kpi"><span class="kpi-v"><?= $inProgram ?></span><span class="kpi-l">En el programa</span></div>
  <div class="kpi"><span class="kpi-v"><?= $counts['verde'] ?></span><span class="kpi-l"><?= status_badge('verde') ?></span></div>
  <div class="kpi"><span class="kpi-v"><?= $counts['amarillo'] ?></span><span class="kpi-l"><?= status_badge('amarillo') ?></span></div>
  <div class="kpi"><span class="kpi-v"><?= $counts['rojo'] ?></span><span class="kpi-l"><?= status_badge('rojo') ?></span></div>
</div>

<?php $tlToday = [];
foreach ($rows as $r) {
    $ctx = $r['stats']['ctx'];
    if (!$ctx || $r['adv']['decision'] === 'no_continua') continue;
    foreach (day_items($ctx, ['office' => $office, 'sessions' => $r['stats']['sessions'], 'done_elsewhere' => $ctx['type'] === 'catchup' ? done_keys($r['stats']['logs'], $r['stats']['today']) : []]) as $it) {
        if ($it['kind'] !== 'tl') continue;
        $retoLink = null;
        foreach ($r['stats']['retos']['list'] as $rv) {
            if ((int)$rv['activity']['week_no'] !== (int)$it['week'] || (int)$rv['activity']['day_no'] !== (int)$it['day']) continue;
            $link = 'reto.php?id=' . (int)$r['adv']['id'] . '&a=' . (int)$rv['activity']['id'] . $amp;
            if (!$rv['row'] && !$retoLink) $retoLink = $link;   // con dos videos el mismo día: primero el que falta
            if (!$retoLink) $retoLink = $link;
        }
        $tlToday[] = [$r['adv'], $it, $retoLink];
    }
}
if ($tlToday): ?>
  <div class="card">
    <h2>Hoy vienen a la oficina con su Team Leader</h2>
    <ul class="attn">
      <?php foreach ($tlToday as [$x, $it, $retoLink]): ?>
        <li><?php if ($multi): ?><span class="prog-chip p-<?= h(advisor_program($x)) ?>"><?= h(P(advisor_program($x))['name']) ?></span> <?php endif; ?><a href="asesor.php?id=<?= (int)$x['id'] . $amp ?>"><b><?= h($x['name']) ?></b></a> · <?= h($it['title']) ?><?= $it['origin'] ? ' <span class="small muted">(' . h(mb_strtolower($it['origin'])) . ')</span>' : '' ?>
          <?php if ($retoLink): ?> <a class="btn btn-sm btn-main" href="<?= h($retoLink) ?>">Aplicar Reto</a><?php endif; ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($attention): ?>
  <div class="card">
    <h2><?= $tab === 'todos' ? 'Te sugiero atender hoy a' : h(P($tab)['name']) . ' te sugiere atender hoy a' ?></h2>
    <ul class="attn">
      <?php foreach ($attention as $r): ?>
        <li><?= status_badge($r['stats']['status']['color']) ?> <a href="asesor.php?id=<?= (int)$r['adv']['id'] . $amp ?>"><?= h($r['stats']['status']['action']) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="tbl-wrap">
<table class="tbl responsive">
  <thead><tr>
    <th>Asesor</th><?php if ($tab === 'todos'): ?><th>Programa</th><?php endif; ?><th>Semáforo</th><th>Semana</th><th>Actividades</th>
    <th>Citas con TL</th><th>Retos</th>
    <?php if ($tab === 'residencial'): ?><th>Contactos</th><th>Citas</th><th>Captaciones</th><?php elseif ($tab === 'industrial'): ?><th>Llamadas</th><th>Recorridos</th><th>Leads</th><th>Captación</th><?php endif; ?>
    <th>Último registro</th><th></th>
  </tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="12" class="muted">Aún no tienes asesores. Da de alta al primero abajo.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r): $a = $r['adv']; $s = $r['stats']; $st = $s['status']; ?>
    <tr>
      <td class="first"><a href="asesor.php?id=<?= (int)$a['id'] . $amp ?>"><b><?= h($a['name']) ?></b></a>
        <?php if ($st['action']): ?><span class="action-hint"><?= h($st['action']) ?></span><?php endif; ?>
        <?php if ($a['decision'] === 'no_continua'): ?><span class="action-hint">No continúa en el programa</span><?php endif; ?></td>
      <?php if ($tab === 'todos'): ?><td data-l="Programa"><span class="prog-chip p-<?= h(advisor_program($a)) ?>"><?= h(P(advisor_program($a))['name']) ?></span></td><?php endif; ?>
      <td data-l="Semáforo"><?= status_badge($st['color'], $st['reason']) ?></td>
      <td data-l="Semana"><?= !$s['started'] ? 'Inicia ' . h(fecha_corta($a['start_date'])) : ($s['finished'] ? 'Terminó' : 'S' . (int)$s['week'] . (!empty($s['ctx']['repeat']) ? ' (repite)' : '') . ' · ' . h($s['ctx'] ? ctx_label($s['ctx']) : '')) ?></td>
      <td data-l="Actividades" class="n"><?= (int)$s['pct'] ?>%</td>
      <td data-l="Citas con TL" class="n"><?= $s['tl']['due'] ? (int)$s['tl']['done'] . ' de ' . (int)$s['tl']['due'] : '—' ?></td>
      <td data-l="Retos" class="n"><?= $s['retos']['due'] ? h(reto_points_label($s['retos']['points'])) . ' de ' . (int)$s['retos']['due'] : '—' ?></td>
      <?php if ($tab === 'residencial'): ?>
      <td data-l="Contactos" class="n"><?= (int)$s['totals']['contactos'] ?></td>
      <td data-l="Citas" class="n"><?= (int)$s['totals']['citas'] ?></td>
      <td data-l="Captaciones" class="n"><?= (int)$s['totals']['captaciones'] ?></td>
      <?php elseif ($tab === 'industrial'): ?>
      <td data-l="Llamadas" class="n"><?= (int)$s['totals']['llamadas'] ?></td>
      <td data-l="Recorridos" class="n"><?= (int)$s['totals']['recorridos'] ?></td>
      <td data-l="Leads" class="n"><?= (int)$s['totals']['leads'] ?></td>
      <td data-l="Captación" class="n"><?= (int)$a['ind_cap_done'] ? 'Sí' : '—' ?></td>
      <?php endif; ?>
      <td data-l="Último registro"><?= h(fecha_corta($s['last_log'])) ?></td>
      <td data-l=""><a class="btn btn-sm btn-wa" href="<?= h(wa_link($a, $office)) ?>" target="_blank" rel="noopener">WhatsApp</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php $inactive = all('SELECT id, name FROM advisors WHERE office_id = ? AND active = 0 ORDER BY name', [$office['id']]);
if ($inactive): ?>
  <p class="small muted">Dados de baja: <?php foreach ($inactive as $i => $x) echo ($i ? ', ' : '') . '<a href="asesor.php?id=' . (int)$x['id'] . $amp . '">' . h($x['name']) . '</a>'; ?></p>
<?php endif; ?>

<div class="cols">
  <div class="card" id="alta">
    <h2>Alta de asesor</h2>
    <form method="post" class="form">
      <?= csrf_field() ?><input type="hidden" name="action" value="add">
      <?php if (is_admin($u)): ?><input type="hidden" name="office" value="<?= (int)$office['id'] ?>"><?php endif; ?>
      <?php if (count(office_programs($office)) > 1): ?>
      <fieldset class="progs"><legend><b>Programa</b></legend>
        <?php foreach (office_programs($office) as $i => $pk): ?>
          <label class="check"><input type="radio" name="program" value="<?= h($pk) ?>" <?= $i === 0 ? 'checked' : '' ?> required> <span><b><?= h(P($pk)['name']) ?></b> · <?= h(P($pk)['area']) ?></span></label>
        <?php endforeach; ?>
      </fieldset>
      <?php else: ?><input type="hidden" name="program" value="<?= h(office_programs($office)[0]) ?>"><?php endif; ?>
      <label>Nombre completo<input name="name" required maxlength="150"></label>
      <label>Celular (10 dígitos)<input type="tel" name="phone" required inputmode="tel" placeholder="998 123 4567"></label>
      <label>Correo (opcional)<input type="email" name="email"></label>
      <label>Fecha de inicio<input type="date" name="start_date" value="<?= h(office_today($office)) ?>"></label>
      <button class="btn btn-main">Dar de alta</button>
    </form>
  </div>
  <div class="card">
    <h2>Recordatorio del día</h2>
    <p class="small muted">Cópialo y pégalo en el grupo de WhatsApp de la oficina. Los asesores en descanso no aparecen.</p>
    <textarea id="recordatorio" class="copyarea" readonly><?= h($reminder) ?></textarea>
    <p><button class="btn" type="button" data-copy="recordatorio">Copiar recordatorio</button></p>
    <h2>Reporte para WhatsApp</h2>
    <p class="small muted">El mismo resumen del correo del viernes, en texto, para mandarlo a tu Team Leader o a tu gerente.</p>
    <textarea id="reporteWa" class="copyarea" readonly><?= h($waReport) ?></textarea>
    <p><button class="btn" type="button" data-copy="reporteWa">Copiar reporte</button></p>
  </div>
</div>
<?php page_end();
