<?php
/**
 * ARIADNA · Administrador: todas las oficinas, vista comparativa y alta de oficinas.
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';

enforce_https();
$u = require_user(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'add_office') {
    csrf_check();
    $name = post('name');
    if ($name === '') { flash('Escribe el nombre de la oficina.', 'err'); redirect('admin.php'); }
    $tz = post('timezone') ?: 'America/Cancun';
    if (!in_array($tz, DateTimeZone::listIdentifiers(), true)) $tz = 'America/Cancun';
    $progs = array_values(array_intersect(array_keys(PROGRAMS), (array)($_POST['programs'] ?? [])));
    q('INSERT INTO offices (name, city, timezone, programs) VALUES (?,?,?,?)', [$name, post('city'), $tz, implode(',', $progs ?: ['residencial'])]);
    $id = (int)db()->lastInsertId();
    flash('Oficina creada. Ahora da de alta a su director, gerente y team leader.');
    redirect('admin_oficina.php?id=' . $id);
}

$offices = all('SELECT * FROM offices ORDER BY active DESC, name');
$cmp = [];
$global = ['asesores' => 0, 'verde' => 0, 'amarillo' => 0, 'rojo' => 0];
foreach ($offices as $o) {
    $rows = office_advisors_stats($o);
    $c = ['verde' => 0, 'amarillo' => 0, 'rojo' => 0];
    $t = array_fill_keys(array_keys(result_fields()), 0);
    $pct = 0; $score = 0; $inProgram = 0;
    foreach ($rows as $r) {
        $c[$r['stats']['status']['color']]++;
        foreach ($t as $f => $_) $t[$f] += $r['stats']['totals'][$f];
        $pct += $r['stats']['pct'];
        $score += $r['stats']['evaluation']['total'];
        if (!$r['stats']['finished']) $inProgram++;
    }
    $n = count($rows);
    $cmp[] = ['office' => $o, 'n' => $n, 'in' => $inProgram, 'c' => $c, 't' => $t,
        'pct' => $n ? (int)round($pct / $n) : 0, 'score' => $n ? (int)round($score / $n) : 0,
        'users' => (int)val('SELECT COUNT(*) FROM users WHERE office_id = ? AND active = 1', [$o['id']]),
        'notraining' => advisors_without_training($o)];
    $global['asesores'] += $n;
    foreach ($c as $k => $v) $global[$k] += $v;
}

page_start('Oficinas', ['nav' => $u, 'active' => 'admin', 'wide' => true]);
?>
<div class="page-head">
  <div><span class="eyebrow">Administrador · R²CP Elite</span><h1>Todas las oficinas</h1></div>
  <div class="actions">
    <a class="btn" href="export.php?type=oficinas">Exportar oficinas (CSV)</a>
    <a class="btn" href="export.php?type=asesores">Exportar asesores (CSV)</a>
    <a class="btn btn-main" href="#nueva">+ Nueva oficina</a>
  </div>
</div>

<div class="summary">
  <div class="kpi"><span class="kpi-v"><?= count($offices) ?></span><span class="kpi-l">Oficinas</span></div>
  <div class="kpi"><span class="kpi-v"><?= $global['asesores'] ?></span><span class="kpi-l">Asesores activos</span></div>
  <div class="kpi"><span class="kpi-v"><?= $global['verde'] ?></span><span class="kpi-l"><?= status_badge('verde') ?></span></div>
  <div class="kpi"><span class="kpi-v"><?= $global['amarillo'] ?></span><span class="kpi-l"><?= status_badge('amarillo') ?></span></div>
  <div class="kpi"><span class="kpi-v"><?= $global['rojo'] ?></span><span class="kpi-l"><?= status_badge('rojo') ?></span></div>
</div>

<div class="tbl-wrap">
<table class="tbl responsive">
  <thead><tr><th>Oficina</th><th>Asesores</th><th>Verde</th><th>Amarillo</th><th>Rojo</th><th>Actividades</th>
    <th>Contactos</th><th>Citas</th><th>Captaciones</th><th>Evaluación prom.</th><th></th></tr></thead>
  <tbody>
  <?php if (!$cmp): ?><tr><td colspan="11" class="muted">Aún no hay oficinas.</td></tr><?php endif; ?>
  <?php foreach ($cmp as $r): $o = $r['office']; ?>
    <tr>
      <td class="first"><a href="oficina.php?office=<?= (int)$o['id'] ?>"><b><?= h($o['name']) ?></b></a>
        <?= !(int)$o['active'] ? '<span class="action-hint">Inactiva</span>' : '' ?>
        <?= $r['users'] === 0 ? '<span class="action-hint">Sin usuarios de dirección</span>' : '' ?>
        <?= $r['notraining'] ? '<span class="action-hint">' . $r['notraining'] . ' sin capacitación interna</span>' : '' ?></td>
      <td data-l="Asesores" class="n"><?= $r['n'] ?></td>
      <td data-l="Verde" class="n"><?= $r['c']['verde'] ?></td>
      <td data-l="Amarillo" class="n"><?= $r['c']['amarillo'] ?></td>
      <td data-l="Rojo" class="n"><?= $r['c']['rojo'] ?></td>
      <td data-l="Actividades" class="n"><?= $r['pct'] ?>%</td>
      <td data-l="Contactos" class="n"><?= $r['t']['contactos'] ?></td>
      <td data-l="Citas" class="n"><?= $r['t']['citas'] ?></td>
      <td data-l="Captaciones" class="n"><?= $r['t']['captaciones'] ?></td>
      <td data-l="Evaluación prom." class="n"><?= $r['score'] ?></td>
      <td data-l=""><a class="btn btn-sm btn-ghost" href="admin_oficina.php?id=<?= (int)$o['id'] ?>">Editar y usuarios</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<div class="card narrow-l" id="nueva">
  <h2>Nueva oficina</h2>
  <form method="post" class="form">
    <?= csrf_field() ?><input type="hidden" name="action" value="add_office">
    <label>Nombre de la oficina<input name="name" required maxlength="150"></label>
    <label>Ciudad<input name="city" maxlength="120"></label>
    <label>Zona horaria
      <select name="timezone">
        <?php foreach (['America/Cancun' => 'Cancún (Quintana Roo)', 'America/Merida' => 'Mérida', 'America/Mexico_City' => 'Centro (CDMX, Guadalajara, Monterrey)', 'America/Chihuahua' => 'Chihuahua', 'America/Mazatlan' => 'Pacífico (Mazatlán)', 'America/Hermosillo' => 'Sonora', 'America/Tijuana' => 'Noroeste (Tijuana)'] as $k => $v): ?>
          <option value="<?= h($k) ?>"><?= h($v) ?></option>
        <?php endforeach; ?>
      </select></label>
    <fieldset class="progs"><legend><b>Programas de esta oficina</b></legend>
      <?php foreach (PROGRAMS as $pk => $pd): ?>
        <label class="check"><input type="checkbox" name="programs[]" value="<?= h($pk) ?>" <?= in_array($pk, ['residencial'], true) ? 'checked' : '' ?>> <span><b><?= h($pd['name'] . ' · ' . $pd['area']) ?></b><br><span class="small muted"><?= h($pd['course'] . ', ' . $pd['weeks'] . ' semanas') ?></span></span></label>
      <?php endforeach; ?>
      <p class="small muted">Si una oficina no tiene un programa, no le aparece en ningún lado. Sus directores, gerentes y TL usan el mismo usuario para los dos.</p>
    </fieldset>
    <button class="btn btn-main">Crear oficina</button>
  </form>
</div>
<?php page_end();
