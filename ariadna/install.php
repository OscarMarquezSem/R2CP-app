<?php
/**
 * ARIADNA · Instalador (se usa una sola vez y después se borra).
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';
require __DIR__ . '/install/lib.php';

start_session();
page_start('Instalación', ['body' => 'auth']);
echo '<div class="card narrow"><div class="center">' . brand_mark() . '<p class="muted">Instalación</p></div>';

if (!$GLOBALS['CONFIG']) {
    echo '<h2>Paso previo: crea tu archivo de configuración</h2>
    <p>En el Administrador de archivos de Hostinger, copia <b>config.sample.php</b> con el nombre <b>config.php</b> y llena los datos de tu base de datos y de tu correo. Después recarga esta página.</p></div>';
    page_end();
    exit;
}

try {
    db();
} catch (Throwable $e) {
    echo '<h2>No me pude conectar a la base de datos</h2><p>Revisa en <b>config.php</b> el nombre de la base de datos, el usuario y la contraseña.</p><p class="muted small">' . h($e->getMessage()) . '</p></div>';
    page_end();
    exit;
}

$installed = false;
try { $installed = (int)val("SELECT COUNT(*) FROM users WHERE role = 'admin'") > 0; } catch (Throwable $e) {}

if ($installed) {
    echo '<h2>Ariadna ya está instalada</h2><p>Por seguridad, <b>borra el archivo install.php</b> y la carpeta <b>install</b> de tu hosting.</p><p><a class="btn" href="login.php">Ir a iniciar sesión</a></p></div>';
    page_end();
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = post('name'); $email = mb_strtolower(post('email')); $pass = (string)($_POST['password'] ?? '');
    if ($name === '') $errors[] = 'Escribe tu nombre.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Escribe un correo válido.';
    if (mb_strlen($pass) < 10) $errors[] = 'La contraseña debe tener al menos 10 caracteres.';
    if (!$errors) {
        install_schema();
        seed_plan();
        create_user(null, $name, $email, $pass, 'admin');
        set_setting('admin_email', $email);
        $demo = !empty($_POST['demo']) ? seed_demo() : null;
        echo '<h2>¡Listo! Ariadna quedó instalada.</h2>';
        echo '<p>Entra con tu correo <b>' . h($email) . '</b> y la contraseña que acabas de crear.</p>';
        if ($demo) {
            echo '<h3>Datos de prueba</h3><p>Contraseña de todos los usuarios de prueba: <b>' . h($demo['password']) . '</b></p><ul>';
            foreach ($demo['users'] as $u) echo '<li>' . h($u[2]) . ' · ' . h(ROLE_LABELS[$u[3]]) . '</li>';
            echo '</ul><p>Enlaces de los asesores de prueba:</p><ul>';
            foreach ($demo['advisors'] as $a) echo '<li><a href="' . h($a['link']) . '">' . h($a['name']) . '</a> · ' . h($a['office']) . ' · Semana ' . (int)$a['week'] . '</li>';
            echo '</ul>';
        }
        echo '<div class="box warn"><b>Importante:</b> ahora borra el archivo <b>install.php</b> y la carpeta <b>install</b> de tu hosting.</div>';
        echo '<p><a class="btn" href="login.php">Ir a iniciar sesión</a></p></div>';
        page_end();
        exit;
    }
}
?>
<h2>Crea tu cuenta de administrador</h2>
<?php foreach ($errors as $e) echo '<div class="flash flash-err">' . h($e) . '</div>'; ?>
<form method="post" class="form">
  <?= csrf_field() ?>
  <label>Tu nombre<input name="name" required value="<?= h(post('name', 'Oscar Márquez')) ?>"></label>
  <label>Tu correo<input type="email" name="email" required value="<?= h(post('email')) ?>"></label>
  <label>Contraseña (mínimo 10 caracteres)<input type="password" name="password" required minlength="10"></label>
  <label class="check"><input type="checkbox" name="demo" value="1" checked> Cargar datos de prueba (2 oficinas y 6 asesores)</label>
  <button class="btn btn-main">Instalar Ariadna</button>
</form>
</div>
<?php page_end();
