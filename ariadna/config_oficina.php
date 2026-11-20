<?php
/**
 * ARIADNA · Configuración de la oficina: días hábiles, PIN y capacitación interna.
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';

enforce_https();
$u = require_user();
$office = panel_office($u);
$qs = office_qs($office);
$self = 'config_oficina.php' . ($qs ? '?' . $qs : '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = post('action');
    if ($act === 'general') {
        $wd = parse_workdays(implode(',', array_map('intval', (array)($_POST['workdays'] ?? []))));
        if (!$wd) { flash('Elige al menos un día hábil.', 'err'); redirect($self); }
        $tw = (int)post('training_weekday');
        $hm = max(5, min(13, (int)post('remind_morning_hour')));
        $hn = max(14, min(23, (int)post('remind_night_hour')));
        q('UPDATE offices SET workdays = ?, pin_enabled = ?, training_weekday = ?, remind_morning = ?, remind_morning_hour = ?, remind_night = ?, remind_night_hour = ? WHERE id = ?',
            [implode(',', $wd), !empty($_POST['pin_enabled']) ? 1 : 0, ($tw >= 1 && $tw <= 7) ? $tw : null,
             !empty($_POST['remind_morning']) ? 1 : 0, $hm, !empty($_POST['remind_night']) ? 1 : 0, $hn, $office['id']]);
        flash('Configuración guardada.');
    } elseif ($act === 'add_date') {
        $d = post('tdate');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
            q('INSERT IGNORE INTO office_training_dates (office_id, tdate) VALUES (?, ?)', [$office['id'], $d]);
            flash('Fecha agregada.');
        }
    } elseif (in_array($act, ['team_toggle', 'team_delete'], true)) {
        $t = one('SELECT * FROM users WHERE id = ? AND office_id = ?', [(int)post('user_id'), $office['id']]);
        if (!$t || !can_manage_user($u, $t)) {
            flash('No tienes permiso para cambiar a esa persona.', 'err');
        } elseif ($act === 'team_toggle') {
            q('UPDATE users SET active = 1 - active WHERE id = ?', [$t['id']]);
            flash((int)$t['active'] ? $t['name'] . ' quedó inactivo: ya no puede entrar.' : $t['name'] . ' puede volver a entrar.');
        } elseif (!(int)$t['active']) {
            q('DELETE FROM users WHERE id = ? AND active = 0', [$t['id']]);
            flash($t['name'] . ' se borró. Si regresa, el administrador lo da de alta otra vez.');
        }
        redirect($self);
    } elseif ($act === 'del_date') {
        q('DELETE FROM office_training_dates WHERE id = ? AND office_id = ?', [(int)post('date_id'), $office['id']]);
        flash('Fecha eliminada.');
    }
    $office = one('SELECT * FROM offices WHERE id = ?', [$office['id']]);
    reassign_training($office);
    redirect($self);
}

$wd = parse_workdays($office['workdays']);
$dates = all('SELECT * FROM office_training_dates WHERE office_id = ? AND tdate >= ? ORDER BY tdate', [$office['id'], office_today($office)]);
$noTraining = advisors_without_training($office);
$team = all('SELECT * FROM users WHERE office_id = ? ORDER BY active DESC, FIELD(role, "director", "gerente", "team_leader"), name', [$office['id']]);
$canTeam = in_array($u['role'], ['admin', 'director', 'gerente'], true);
page_start('Configuración', ['nav' => $u, 'office' => $office, 'active' => 'config']);
$hidden = is_admin($u) ? '<input type="hidden" name="office" value="' . (int)$office['id'] . '">' : '';
?>
<h1>Configuración de la oficina</h1>
<?php if ($noTraining > 0): ?>
  <div class="box warn">Tienes <b><?= $noTraining ?></b> <?= $noTraining === 1 ? 'asesor nuevo' : 'asesores nuevos' ?> sin día de capacitación interna. Programa un día fijo o agrega una fecha.</div>
<?php endif; ?>

<div class="card">
  <form method="post" class="form">
    <?= csrf_field() . $hidden ?><input type="hidden" name="action" value="general">
    <h2>Días hábiles</h2>
    <p class="small muted">Los días que no marques son de descanso: Ariadna no muestra actividades ni envía recordatorios. Puedes ajustar los días de un asesor en su detalle.</p>
    <div class="days-pick">
      <?php foreach (DIAS as $n => $dn): ?><label><input type="checkbox" name="workdays[]" value="<?= $n ?>" <?= in_array($n, $wd, true) ? 'checked' : '' ?>> <?= h(ucfirst($dn)) ?></label><?php endforeach; ?>
    </div>
    <h2>Capacitación interna</h2>
    <p class="small muted">Ariadna asigna a cada asesor nuevo la siguiente fecha disponible después de su arranque. Ese día ve «Capacitación interna: políticas y documentos de la oficina» y sus actividades se recorren al siguiente día hábil.</p>
    <label>Día fijo cada semana
      <select name="training_weekday">
        <option value="0">Sin día fijo</option>
        <?php foreach (DIAS as $n => $dn): ?><option value="<?= $n ?>" <?= (int)$office['training_weekday'] === $n ? 'selected' : '' ?>>Todos los <?= h($dn) ?></option><?php endforeach; ?>
      </select></label>
    <h2>Avisos a los asesores</h2>
    <p class="small muted">Solo en días hábiles. Llegan como notificación del celular o, si el asesor no la activó, por correo.</p>
    <div class="grid2">
      <label class="check"><input type="checkbox" name="remind_morning" value="1" <?= (int)$office['remind_morning'] ? 'checked' : '' ?>> Aviso de la mañana: qué le toca hoy</label>
      <label>Hora<select name="remind_morning_hour"><?php for ($i = 6; $i <= 12; $i++) echo '<option value="' . $i . '"' . ((int)$office['remind_morning_hour'] === $i ? ' selected' : '') . '>' . $i . ':00</option>'; ?></select></label>
      <label class="check"><input type="checkbox" name="remind_night" value="1" <?= (int)$office['remind_night'] ? 'checked' : '' ?>> Aviso de la noche: solo si aún no guardó su día</label>
      <label>Hora<select name="remind_night_hour"><?php for ($i = 17; $i <= 22; $i++) echo '<option value="' . $i . '"' . ((int)$office['remind_night_hour'] === $i ? ' selected' : '') . '>' . $i . ':00</option>'; ?></select></label>
    </div>
    <p class="small muted">Si se le olvida, a la mañana siguiente Ariadna le propone registrar el día anterior, hasta las <?= (int)setting('ayer_until_hour') ?>:00.</p>
    <h2>Seguridad</h2>
    <label class="check"><input type="checkbox" name="pin_enabled" value="1" <?= (int)$office['pin_enabled'] ? 'checked' : '' ?>> Pedir a los asesores un PIN de 4 dígitos además de su enlace</label>
    <button class="btn btn-main">Guardar</button>
  </form>
</div>

<div class="card">
  <h2>Fechas específicas de capacitación</h2>
  <p class="small muted">Úsalas además del día fijo, o en lugar de él.</p>
  <?php if ($dates): ?>
    <ul>
    <?php foreach ($dates as $d): ?>
      <li><?= h(ucfirst(fecha_larga($d['tdate']))) ?>
        <form method="post" class="inline-form"><?= csrf_field() . $hidden ?><input type="hidden" name="action" value="del_date"><input type="hidden" name="date_id" value="<?= (int)$d['id'] ?>"><button class="btn btn-sm btn-ghost">Quitar</button></form></li>
    <?php endforeach; ?>
    </ul>
  <?php else: ?><p class="muted">No hay fechas próximas.</p><?php endif; ?>
  <form method="post" class="form grid2">
    <?= csrf_field() . $hidden ?><input type="hidden" name="action" value="add_date">
    <label>Nueva fecha<input type="date" name="tdate" required min="<?= h(office_today($office)) ?>"></label>
    <div style="align-self:end;margin-bottom:14px"><button class="btn">Agregar fecha</button></div>
  </form>
</div>
<?php if ($canTeam): ?>
<div class="card" id="equipo">
  <h2>Equipo de la oficina</h2>
  <p class="small muted">El director puede desactivar y borrar a gerentes y team leaders; el gerente, a team leaders. Primero se desactiva (ya no puede entrar) y después, si no va a regresar, se borra. Para agregar a alguien nuevo o cambiar su correo, escríbele a Oscar.</p>
  <?php foreach ($team as $x): $can = can_manage_user($u, $x); ?>
    <div class="plan-act">
      <b><?= h($x['name']) ?></b> · <?= h(ROLE_LABELS[$x['role']] ?? $x['role']) ?> · <span class="muted"><?= h($x['email']) ?></span>
      <?= !(int)$x['active'] ? ' · <b style="color:var(--red)">Inactivo</b>' : '' ?>
      <?php if ((int)$x['id'] === (int)$u['id']): ?><span class="muted small"> · eres tú</span><?php endif; ?>
      <?php if ($can): ?>
      <div class="actions" style="margin-top:8px">
        <form method="post" class="inline-form" data-confirm="<?= (int)$x['active'] ? '¿Desactivar a ' . h($x['name']) . '? Ya no podrá entrar.' : '¿Activar otra vez a ' . h($x['name']) . '?' ?>"><?= csrf_field() . $hidden ?><input type="hidden" name="action" value="team_toggle"><input type="hidden" name="user_id" value="<?= (int)$x['id'] ?>">
          <button class="btn btn-sm <?= (int)$x['active'] ? 'btn-danger' : 'btn-ghost' ?>"><?= (int)$x['active'] ? 'Desactivar' : 'Activar' ?></button></form>
        <?php if (!(int)$x['active']): ?>
        <form method="post" class="inline-form" data-confirm="¿Borrar para siempre a <?= h($x['name']) ?>? No se puede deshacer."><?= csrf_field() . $hidden ?><input type="hidden" name="action" value="team_delete"><input type="hidden" name="user_id" value="<?= (int)$x['id'] ?>">
          <button class="btn btn-sm btn-danger">Borrar</button></form>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php page_end();
