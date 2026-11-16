<?php
declare(strict_types=1);
require __DIR__ . '/inc/app.php';
enforce_https();
$u = require_user();
$office = $u['office_id'] ? one('SELECT * FROM offices WHERE id = ?', [$u['office_id']]) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $cur = (string)($_POST['current'] ?? ''); $new = (string)($_POST['new'] ?? ''); $new2 = (string)($_POST['new2'] ?? '');
    if (!password_verify($cur, $u['password_hash'])) flash('Tu contraseña actual no es correcta.', 'err');
    elseif (mb_strlen($new) < 10) flash('La nueva contraseña debe tener al menos 10 caracteres.', 'err');
    elseif ($new !== $new2) flash('Las contraseñas nuevas no coinciden.', 'err');
    else {
        q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $u['id']]);
        flash('Listo, cambiaste tu contraseña.');
    }
    redirect('cuenta.php');
}
page_start('Mi cuenta', ['nav' => $u, 'office' => $office, 'active' => 'cuenta']);
?>
<h1>Mi cuenta</h1>
<div class="card narrow-l">
  <p><b><?= h($u['name']) ?></b> · <?= h(ROLE_LABELS[$u['role']]) ?><br><span class="muted"><?= h($u['email']) ?></span></p>
  <h2>Cambiar contraseña</h2>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <label>Contraseña actual<input type="password" name="current" required autocomplete="current-password"></label>
    <label>Nueva contraseña (mínimo 10 caracteres)<input type="password" name="new" required minlength="10" autocomplete="new-password"></label>
    <label>Repite la nueva contraseña<input type="password" name="new2" required minlength="10" autocomplete="new-password"></label>
    <button class="btn btn-main">Guardar</button>
  </form>
</div>
<?php page_end();
