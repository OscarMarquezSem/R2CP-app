<?php
declare(strict_types=1);
require __DIR__ . '/inc/app.php';
$t = isset($_GET['t']) && preg_match('/^[a-f0-9]{64}$/', (string)$_GET['t']) ? $_GET['t'] : '';
$contact = setting('admin_email') ?: 'el correo de contacto de tu oficina';
page_start('Aviso de privacidad', ['body' => 'auth']);
?>
<div class="card narrow prose">
  <div class="center"><?= brand_mark() ?></div>
  <h1>Aviso de privacidad</h1>
  <p><b>Responsable.</b> Ariadna es la herramienta de acompañamiento del programa R²CP Elite de Oscar Márquez Seminars, que se usa junto con la oficina inmobiliaria a la que perteneces.</p>
  <p><b>Datos que usamos.</b> Tu nombre, celular, correo electrónico, fecha de inicio, las actividades y resultados que registras cada día, tus respuestas de repaso y las fotos que subas.</p>
  <p><b>Para qué los usamos.</b> Para mostrarte qué hacer cada día, medir tu avance en el programa Fast Track, calcular tu evaluación y enviar a tu director, gerente y Team Leader los reportes de avance. No usamos tus datos para publicidad.</p>
  <p><b>Quién los ve.</b> Solo tú, el equipo de dirección de tu oficina (director, gerente y Team Leader) y el administrador del programa. Ninguna otra oficina ni otro asesor puede verlos. No vendemos ni transferimos tus datos a terceros.</p>
  <p><b>Cuánto tiempo.</b> Mientras dure tu acompañamiento y el tiempo necesario para dar seguimiento a tu desarrollo; puedes pedir que se eliminen.</p>
  <p><b>Tus derechos.</b> Puedes pedir acceso, corrección, cancelación u oposición al uso de tus datos (derechos ARCO) escribiendo a <?= h($contact) ?>.</p>
  <p><b>Seguridad.</b> Tu enlace es personal: no lo compartas. La información viaja cifrada (HTTPS).</p>
  <?php if ($t): ?><p><a class="btn" href="hoy.php?t=<?= h($t) ?>">Regresar</a></p><?php endif; ?>
</div>
<?php page_end();
