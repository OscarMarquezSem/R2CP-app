<?php
declare(strict_types=1);
require __DIR__ . '/inc/app.php';
enforce_https();
start_session();

if (current_user()) redirect(is_admin() ? 'admin.php' : 'oficina.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = mb_strtolower(trim(post('email')));
    $pass = (string)($_POST['password'] ?? '');
    $passTrim = trim($pass);   // al copiar de WhatsApp a veces se cuela un espacio
    $key = 'login:' . $email . '|' . client_ip();
    if (too_many_attempts($key, 6, 15)) {
        $error = 'Demasiados intentos. Espera 15 minutos y vuelve a intentar.';
    } else {
        $u = one('SELECT * FROM users WHERE email = ? AND active = 1', [$email]);
        if ($u && (password_verify($pass, $u['password_hash']) || ($passTrim !== $pass && password_verify($passTrim, $u['password_hash'])))) {
            $pass = password_verify($pass, $u['password_hash']) ? $pass : $passTrim;
            clear_attempts($key);
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$u['id'];
            if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
                q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($pass, PASSWORD_DEFAULT), $u['id']]);
            }
            q('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$u['id']]);
            redirect($u['role'] === 'admin' ? 'admin.php' : 'oficina.php');
        }
        record_attempt($key);
        $error = 'El correo o la contraseña no coinciden.';
    }
}
page_start('Entrar', ['body' => 'auth', 'brand' => 'Ariadna · Edalo']);
?>
<div class="card narrow">
  <div class="center"><?= brand_mark('both') ?><p class="tagline">Sigue el hilo · Construye con método</p></div>
  <h1 class="center">Panel de la oficina</h1>
  <p class="center muted">Para directores, gerentes y team leaders.</p>
  <?php if ($error): ?><div class="flash flash-err"><?= h($error) ?></div><?php endif; ?>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <label>Correo<input type="email" name="email" required autocomplete="username" autocapitalize="none" autocorrect="off" spellcheck="false" value="<?= h(post('email')) ?>"></label>
    <label>Contraseña<input type="password" name="password" required autocomplete="current-password" autocapitalize="none" autocorrect="off" spellcheck="false"></label>
    <button class="btn btn-main btn-block">Entrar</button>
  </form>
  <p class="small muted center">¿Olvidaste tu contraseña? Pídele al administrador del programa que la restablezca.</p>
</div>
<?php page_end();
