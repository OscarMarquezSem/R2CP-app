<?php
/**
 * ARIADNA · Administrador: editar oficina y sus usuarios (director, gerente, team leader).
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';
require __DIR__ . '/inc/cron_tasks.php';

enforce_https();
$u = require_user(['admin']);
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$office = one('SELECT * FROM offices WHERE id = ?', [$id]);
if (!$office) redirect('admin.php');
$self = 'admin_oficina.php?id=' . $id;
$tzs = ['America/Cancun' => 'Cancún (Quintana Roo)', 'America/Merida' => 'Mérida', 'America/Mexico_City' => 'Centro (CDMX, Guadalajara, Monterrey)', 'America/Chihuahua' => 'Chihuahua', 'America/Mazatlan' => 'Pacífico (Mazatlán)', 'America/Hermosillo' => 'Sonora', 'America/Tijuana' => 'Noroeste (Tijuana)'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = post('action');
    if ($act === 'office') {
        $tz = array_key_exists(post('timezone'), $tzs) ? post('timezone') : 'America/Cancun';
        $progs = array_values(array_intersect(array_keys(PROGRAMS), (array)($_POST['programs'] ?? [])));
        $vo = array_values(array_intersect(array_keys(PROGRAMS), (array)($_POST['videos_office'] ?? [])));
        q('UPDATE offices SET name = ?, city = ?, timezone = ?, active = ?, programs = ?, videos_office = ? WHERE id = ?',
            [post('name') ?: $office['name'], post('city'), $tz, !empty($_POST['active']) ? 1 : 0, implode(',', $progs ?: ['residencial']), implode(',', $vo), $id]);
        flash('Oficina actualizada.');
    } elseif ($act === 'add_user') {
        $email = mb_strtolower(post('email'));
        $pass = trim((string)($_POST['password'] ?? ''));
        $role = post('role');
        if (post('name') === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($pass) < 10 || !in_array($role, ['director', 'gerente', 'team_leader'], true)) {
            flash('Revisa nombre, correo, rol y que la contraseña tenga al menos 10 caracteres.', 'err');
        } elseif (val('SELECT id FROM users WHERE email = ?', [$email])) {
            flash('Ya existe un usuario con ese correo.', 'err');
        } else {
            create_user($id, post('name'), $email, $pass, $role);
            flash('Usuario creado. Mándale su correo (' . $email . ') y su contraseña: ' . $pass . ' · Puede cambiarla en «Mi cuenta».');
        }
    } elseif ($act === 'edit_user') {
        $uid = (int)post('user_id');
        $email = mb_strtolower(trim(post('email')));
        $role = post('role');
        $name = trim(post('name'));
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, ['director', 'gerente', 'team_leader'], true)) {
            flash('Revisa el nombre, el correo y el rol.', 'err');
        } elseif (val('SELECT id FROM users WHERE email = ? AND id <> ?', [$email, $uid])) {
            flash('Ya existe otro usuario con ese correo.', 'err');
        } else {
            q('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ? AND office_id = ?', [$name, $email, $role, $uid, $id]);
            flash('Usuario actualizado. Si cambiaste el correo, desde ahora entra con el nuevo (la contraseña es la misma).');
        }
    } elseif ($act === 'reset_pass') {
        $pass = trim((string)($_POST['password'] ?? ''));
        $who = one('SELECT * FROM users WHERE id = ? AND office_id = ?', [(int)post('user_id'), $id]);
        if (mb_strlen($pass) < 10) flash('La contraseña debe tener al menos 10 caracteres.', 'err');
        elseif ($who) {
            q('UPDATE users SET password_hash = ?, active = 1 WHERE id = ?', [password_hash($pass, PASSWORD_DEFAULT), $who['id']]);
            q('DELETE FROM login_attempts WHERE k LIKE ?', ['login:' . $who['email'] . '|%']);
            flash('Contraseña nueva de ' . $who['name'] . ': ' . $pass . ' · Mándasela junto con su correo (' . $who['email'] . ').');
        }
    } elseif ($act === 'delete_office') {
        if ((int)$office['active'] === 1) {
            flash('Primero desactiva la oficina.', 'err');
        } elseif (trim(post('confirm_name')) !== $office['name']) {
            flash('Para borrarla escribe el nombre de la oficina exactamente igual.', 'err');
        } else {
            delete_office($id);
            flash('La oficina ' . $office['name'] . ' se borró con sus usuarios, asesores y registros.');
            redirect('admin.php');
        }
    } elseif ($act === 'send_report') {
        $ok = send_weekly_report($office);
        flash($ok ? 'Reporte enviado a la dirección de la oficina (con copia para ti).' : 'No se pudo enviar. Revisa que la oficina tenga usuarios y los ajustes de correo.', $ok ? 'ok' : 'err');
    } elseif ($act === 'delete_user') {
        q('DELETE FROM users WHERE id = ? AND office_id = ? AND active = 0 AND role <> "admin"', [(int)post('user_id'), $id]);
        flash('Usuario borrado.');
    } elseif ($act === 'toggle_user') {
        q('UPDATE users SET active = 1 - active WHERE id = ? AND office_id = ?', [(int)post('user_id'), $id]);
        flash('Usuario actualizado.');
    }
    redirect($self);
}

$users = all('SELECT * FROM users WHERE office_id = ? ORDER BY FIELD(role, "director", "gerente", "team_leader"), name', [$id]);
page_start($office['name'], ['nav' => $u, 'active' => 'admin']);
?>
<p><a href="admin.php">← Todas las oficinas</a> · <a href="oficina.php?office=<?= $id ?>">Ver panel de esta oficina</a></p>
<div class="page-head"><h1><?= h($office['name']) ?></h1>
  <form method="post" class="inline-form" data-confirm="¿Enviar ahora el reporte semanal de esta oficina?"><?= csrf_field() ?><input type="hidden" name="action" value="send_report"><input type="hidden" name="id" value="<?= $id ?>"><button class="btn">Enviar reporte semanal ahora</button></form>
</div>

<div class="card">
  <h2>Datos de la oficina</h2>
  <form method="post" class="form">
    <?= csrf_field() ?><input type="hidden" name="action" value="office"><input type="hidden" name="id" value="<?= $id ?>">
    <label>Nombre<input name="name" required value="<?= h($office['name']) ?>"></label>
    <label>Ciudad<input name="city" value="<?= h($office['city'] ?? '') ?>"></label>
    <label>Zona horaria
      <select name="timezone"><?php foreach ($tzs as $k => $v): ?><option value="<?= h($k) ?>" <?= $office['timezone'] === $k ? 'selected' : '' ?>><?= h($v) ?></option><?php endforeach; ?></select></label>
    <fieldset class="progs"><legend><b>Programas de esta oficina</b></legend>
      <?php foreach (PROGRAMS as $pk => $pd): ?>
        <label class="check"><input type="checkbox" name="programs[]" value="<?= h($pk) ?>" <?= in_array($pk, office_programs($office), true) ? 'checked' : '' ?>> <span><b><?= h($pd['name'] . ' · ' . $pd['area']) ?></b><br><span class="small muted"><?= h($pd['course'] . ', ' . $pd['weeks'] . ' semanas') ?></span></span></label>
      <?php endforeach; ?>
      <p class="small muted">Si una oficina no tiene un programa, no le aparece en ningún lado. Sus directores, gerentes y TL usan el mismo usuario para los dos.</p>
    </fieldset>
    <fieldset class="progs"><legend><b>¿Dónde ven los videos sus asesores?</b></legend>
      <?php foreach (PROGRAMS as $pk => $pd): ?>
        <label class="check"><input type="checkbox" name="videos_office[]" value="<?= h($pk) ?>" <?= videos_office_only($office, $pk) ? 'checked' : '' ?>> <span><b><?= h($pd['name']) ?>: solo en la oficina</b><br><span class="small muted">El asesor ve la actividad del video, pero sin reproductor: lo ve en la oficina, en la página «Videos» del panel.</span></span></label>
      <?php endforeach; ?>
      <p class="small muted">Sin marcar, los asesores ven los videos en su app, como siempre.</p>
    </fieldset>
    <label class="check"><input type="checkbox" name="active" value="1" <?= (int)$office['active'] ? 'checked' : '' ?>> Oficina activa (si la desactivas, nadie de la oficina puede entrar)</label>
    <button class="btn btn-main">Guardar</button>
  </form>
</div>

<div class="card">
  <h2>Usuarios de la oficina</h2>
  <?php if (!$users): ?><p class="muted">Aún no hay usuarios.</p><?php endif; ?>
  <?php foreach ($users as $x): ?>
    <div class="plan-act">
      <b><?= h($x['name']) ?></b> · <?= h(ROLE_LABELS[$x['role']]) ?> · <span class="muted"><?= h($x['email']) ?></span>
      <?= !(int)$x['active'] ? ' · <b style="color:var(--red)">Inactivo</b>' : '' ?>
      <br><span class="small muted"><?= $x['last_login_at'] ? 'Último acceso: ' . h(date('d/m/Y H:i', strtotime($x['last_login_at']))) : 'Todavía no ha entrado' ?></span>
      <div class="actions" style="margin-top:8px">
        <form method="post" class="inline-form actions"><?= csrf_field() ?><input type="hidden" name="action" value="reset_pass"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="user_id" value="<?= (int)$x['id'] ?>">
          <input type="text" name="password" value="<?= h(readable_password()) ?>" minlength="10" style="max-width:220px;min-height:38px;margin:0" aria-label="Contraseña nueva"><button class="btn btn-sm btn-ghost">Restablecer contraseña</button></form>
        <details class="edit-user" style="flex-basis:100%"><summary class="btn btn-sm btn-ghost">Editar nombre, correo o rol</summary>
          <form method="post" class="form" style="margin-top:10px"><?= csrf_field() ?><input type="hidden" name="action" value="edit_user"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="user_id" value="<?= (int)$x['id'] ?>">
            <div class="grid2">
              <label>Nombre<input name="name" required value="<?= h($x['name']) ?>"></label>
              <label>Correo<input type="email" name="email" required value="<?= h($x['email']) ?>"></label>
            </div>
            <label>Rol<select name="role"><?php foreach (['director', 'gerente', 'team_leader'] as $r): ?><option value="<?= $r ?>" <?= $x['role'] === $r ? 'selected' : '' ?>><?= h(ROLE_LABELS[$r]) ?></option><?php endforeach; ?></select></label>
            <button class="btn btn-main">Guardar cambios</button>
          </form>
        </details>
        <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_user"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="user_id" value="<?= (int)$x['id'] ?>">
          <button class="btn btn-sm <?= (int)$x['active'] ? 'btn-danger' : 'btn-ghost' ?>"><?= (int)$x['active'] ? 'Desactivar' : 'Activar' ?></button></form>
        <?php if (!(int)$x['active']): ?>
        <form method="post" class="inline-form" data-confirm="¿Borrar para siempre a <?= h($x['name']) ?>? No se puede deshacer."><?= csrf_field() ?><input type="hidden" name="action" value="delete_user"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="user_id" value="<?= (int)$x['id'] ?>">
          <button class="btn btn-sm btn-danger">Borrar</button></form>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <h3 style="margin-top:20px">Agregar usuario</h3>
  <form method="post" class="form">
    <?= csrf_field() ?><input type="hidden" name="action" value="add_user"><input type="hidden" name="id" value="<?= $id ?>">
    <div class="grid2">
      <label>Nombre<input name="name" required></label>
      <label>Rol<select name="role"><option value="director">Director (dueño de oficina)</option><option value="gerente">Gerente</option><option value="team_leader">Team Leader</option></select></label>
      <label>Correo<input type="email" name="email" required></label>
      <label>Contraseña inicial<input type="text" name="password" required minlength="10" value="<?= h(readable_password()) ?>"></label>
    </div>
    <button class="btn btn-main">Crear usuario</button>
  </form>
</div>
<div class="card" id="borrar">
  <h2>Borrar oficina</h2>
  <?php if ((int)$office['active'] === 1): ?>
    <p class="small muted">Para borrar una oficina, primero desactívala arriba (quita la marca de «Oficina activa» y guarda). Así nadie la borra por error.</p>
  <?php else: ?>
    <p>Se borra para siempre <b><?= h($office['name']) ?></b> con todos sus usuarios, asesores, registros, Retos y fotos. <b>No se puede deshacer.</b></p>
    <form method="post" class="form" data-confirm="¿Borrar para siempre la oficina <?= h($office['name']) ?>?">
      <?= csrf_field() ?><input type="hidden" name="action" value="delete_office"><input type="hidden" name="id" value="<?= $id ?>">
      <label>Para confirmar, escribe el nombre de la oficina: <b><?= h($office['name']) ?></b>
        <input name="confirm_name" required autocomplete="off"></label>
      <button class="btn btn-danger">Borrar oficina para siempre</button>
    </form>
  <?php endif; ?>
</div>
<?php page_end();
