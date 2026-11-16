<?php
/**
 * ARIADNA · Administrador: umbrales del semáforo, reporte semanal, recordatorios y rutina diaria.
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';

enforce_https();
$u = require_user(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (post('action') === 'test_mail') {
        $ok = send_mail([$u['email']], 'Prueba de correo de Ariadna', email_wrap('¡Funciona!', '<p>Si lees esto, Ariadna ya puede enviar correos.</p>'), [], 'prueba');
        $err = $ok ? '' : (string)val("SELECT error FROM email_log WHERE kind = 'prueba' ORDER BY id DESC LIMIT 1");
        flash($ok ? 'Correo de prueba enviado a ' . $u['email'] . '.' : 'No se pudo enviar: ' . $err, $ok ? 'ok' : 'err');
        redirect('admin_ajustes.php');
    }
    if (post('action') === 'delete_demo') {
        $n = (int)val("SELECT COUNT(*) FROM offices WHERE name LIKE 'Oficina Demo %'");
        q("DELETE FROM offices WHERE name LIKE 'Oficina Demo %'");
        q("DELETE FROM users WHERE email LIKE '%@demo.mx' AND role <> 'admin'");
        flash($n ? 'Datos de prueba borrados.' : 'No había datos de prueba.');
        redirect('admin_ajustes.php');
    }
    $ints = ['yellow_days' => [1, 30], 's2_min_contactos' => [0, 999], 's2_min_promovidas' => [0, 999], 'checkpoint_min_score' => [0, 100],
             'oh_min_attendees' => [1, 999], 'registro_min_pct' => [0, 100], 'report_weekday' => [1, 7], 'report_hour' => [0, 23], 'reminder_hour' => [0, 23]];
    foreach ($ints as $k => [$lo, $hi]) set_setting($k, (string)max($lo, min($hi, (int)post($k))));
    foreach (['red_s1_enabled', 's3_red_enabled', 'reminder_enabled'] as $k) set_setting($k, !empty($_POST[$k]) ? '1' : '0');
    $tz = post('report_timezone');
    set_setting('report_timezone', in_array($tz, DateTimeZone::listIdentifiers(), true) ? $tz : 'America/Cancun');
    $ae = post('admin_email');
    set_setting('admin_email', filter_var($ae, FILTER_VALIDATE_EMAIL) ? $ae : '');
    $routine = [];
    foreach ((array)($_POST['routine'] ?? []) as $i => $r) {
        if (trim((string)($r['title'] ?? '')) === '') continue;
        $routine[] = ['key' => 'R' . (count($routine) + 1), 'title' => trim((string)$r['title']), 'guide' => trim((string)$r['guide'])];
    }
    set_setting('routine_items', json_encode($routine, JSON_UNESCAPED_UNICODE));
    flash('Ajustes guardados.');
    redirect('admin_ajustes.php');
}

$s = fn($k) => h((string)setting($k));
$routine = routine_items();
while (count($routine) < 3) $routine[] = ['key' => '', 'title' => '', 'guide' => ''];
$mails = all('SELECT * FROM email_log ORDER BY id DESC LIMIT 15');
page_start('Ajustes', ['nav' => $u, 'active' => 'ajustes']);
?>
<h1>Ajustes</h1>
<form method="post" class="form">
<?= csrf_field() ?>
<div class="card">
  <h2>Semáforo</h2>
  <p class="small muted">El amarillo se revisa todos los días. El rojo se evalúa al cierre de la semana personal de cada asesor.</p>
  <label>Amarillo: días laborables seguidos sin registrar<input type="number" name="yellow_days" min="1" value="<?= $s('yellow_days') ?>"></label>
  <label class="check"><input type="checkbox" name="red_s1_enabled" value="1" <?= setting('red_s1_enabled') === '1' ? 'checked' : '' ?>> Rojo al cerrar la Semana 1 sin actividad registrada</label>
  <p><b>Rojo al cerrar la Semana 2</b> si no cumple los mínimos acumulados:</p>
  <div class="grid2">
    <label>Contactos mínimos (semanas 1 y 2)<input type="number" name="s2_min_contactos" min="0" value="<?= $s('s2_min_contactos') ?>"></label>
    <label>Propiedades promovidas mínimas<input type="number" name="s2_min_promovidas" min="0" value="<?= $s('s2_min_promovidas') ?>"></label>
  </div>
  <label class="check"><input type="checkbox" name="s3_red_enabled" value="1" <?= setting('s3_red_enabled') === '1' ? 'checked' : '' ?>> Rojo al cerrar la Semana 3 con cero citas y cero captaciones</label>
</div>
<div class="card">
  <h2>Evaluación y punto de control</h2>
  <div class="grid2">
    <label>Puntos mínimos en el punto de control (Semana 3)<input type="number" name="checkpoint_min_score" min="0" max="100" value="<?= $s('checkpoint_min_score') ?>"></label>
    <label>Asistentes mínimos al Open House<input type="number" name="oh_min_attendees" min="1" value="<?= $s('oh_min_attendees') ?>"></label>
    <label>«Registro ordenado»: % mínimo de días registrados<input type="number" name="registro_min_pct" min="0" max="100" value="<?= $s('registro_min_pct') ?>"></label>
  </div>
  <p class="small muted">Puntos de actividad: proporcionales a la meta, con tope en el máximo de cada renglón. Las metas se editan en Plan, semana por semana. Calidad de captación: 3 de 3 criterios = Alta (10), 2 = Media (5), 1 = Baja (0).</p>
</div>
<div class="card">
  <h2>Correos</h2>
  <div class="grid2">
    <label>Reporte semanal: día
      <select name="report_weekday"><?php foreach (DIAS as $n => $dn): ?><option value="<?= $n ?>" <?= (int)setting('report_weekday') === $n ? 'selected' : '' ?>><?= h(ucfirst($dn)) ?></option><?php endforeach; ?></select></label>
    <label>Hora (0 a 23)<input type="number" name="report_hour" min="0" max="23" value="<?= $s('report_hour') ?>"></label>
    <label>Zona horaria del reporte
      <select name="report_timezone"><?php foreach (['America/Cancun', 'America/Merida', 'America/Mexico_City', 'America/Chihuahua', 'America/Mazatlan', 'America/Hermosillo', 'America/Tijuana'] as $tz): ?><option <?= setting('report_timezone') === $tz ? 'selected' : '' ?>><?= h($tz) ?></option><?php endforeach; ?></select></label>
    <label>Correo del administrador (copia de reportes y alertas)<input type="email" name="admin_email" value="<?= $s('admin_email') ?>"></label>
  </div>
  <label class="check"><input type="checkbox" name="reminder_enabled" value="1" <?= setting('reminder_enabled') === '1' ? 'checked' : '' ?>> Enviar recordatorio por correo al asesor en sus días laborables si aún no registra su día</label>
  <label>Hora del recordatorio (hora de su oficina)<input type="number" name="reminder_hour" min="0" max="23" value="<?= $s('reminder_hour') ?>"></label>
</div>
<div class="card">
  <h2>Rutina diaria</h2>
  <p class="small muted">Aparece todos los días laborables (en la Semana 1, desde el día que indiques en Plan).</p>
  <?php foreach ($routine as $i => $r): ?>
    <div class="plan-act">
      <label>Actividad<input name="routine[<?= $i ?>][title]" value="<?= h($r['title']) ?>"></label>
      <label>Guía<textarea name="routine[<?= $i ?>][guide]" rows="2"><?= h($r['guide']) ?></textarea></label>
    </div>
  <?php endforeach; ?>
</div>
<div class="savebar"><button class="btn btn-main btn-block btn-xl">Guardar ajustes</button></div>
</form>

<div class="card">
  <h2>Correos enviados</h2>
  <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="test_mail"><button class="btn btn-sm">Enviarme un correo de prueba</button></form>
  <div class="tbl-wrap" style="margin-top:12px">
  <table class="tbl"><thead><tr><th>Fecha</th><th>Tipo</th><th>Asunto</th><th>Estado</th></tr></thead><tbody>
  <?php if (!$mails): ?><tr><td colspan="4" class="muted">Aún no se envían correos.</td></tr><?php endif; ?>
  <?php foreach ($mails as $m): ?>
    <tr><td class="small"><?= h($m['created_at']) ?></td><td><?= h($m['kind']) ?></td><td class="small"><?= h($m['subject']) ?><br><span class="muted"><?= h($m['recipients']) ?></span></td>
      <td><?= h($m['status']) ?><?= $m['error'] ? '<br><span class="small" style="color:var(--red)">' . h($m['error']) . '</span>' : '' ?></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
</div>
<div class="card">
  <h2>Datos de prueba</h2>
  <p class="small muted">Borra las oficinas «Oficina Demo», sus asesores y sus usuarios. Tus oficinas reales no se tocan.</p>
  <form method="post" data-confirm="¿Borrar todos los datos de prueba? No se puede deshacer."><?= csrf_field() ?><input type="hidden" name="action" value="delete_demo"><button class="btn btn-danger">Borrar datos de prueba</button></form>
</div>
<?php page_end();
