<?php
/**
 * ARIADNA · Ver como asesor: recorre cualquier día del curso sin esperar 5 semanas.
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';
require __DIR__ . '/inc/hoy_view.php';

enforce_https();
$u = require_user(['admin']);
$offices = all('SELECT * FROM offices WHERE active = 1 ORDER BY name');
$officeId = (int)($_GET['office'] ?? 0);
$office = null;
foreach ($offices as $o) if ((int)$o['id'] === $officeId) $office = $o;
$office = $office ?? ($offices[0] ?? ['id' => 0, 'name' => 'Tu oficina', 'oh_mode' => 'open_house', 'timezone' => 'America/Cancun']);

$screens = ['dia' => 'Día del curso', 'bienvenida' => 'Bienvenida (primera vez)', 'descanso' => 'Día de descanso', 'capacitacion' => 'Capacitación interna'];
$screen = array_key_exists($_GET['s'] ?? '', $screens) ? $_GET['s'] : 'dia';
$w = max(1, min(PROGRAM_WEEKS, (int)($_GET['w'] ?? 1)));
$d = max(1, min(PLAN_DAYS_PER_WEEK, (int)($_GET['d'] ?? 1)));

// Navegación anterior / siguiente
$idx = ($w - 1) * PLAN_DAYS_PER_WEEK + ($d - 1);
$link = function (int $i) use ($office): string {
    $i = max(0, min(PROGRAM_WEEKS * PLAN_DAYS_PER_WEEK - 1, $i));
    return 'admin_preview.php?office=' . (int)$office['id'] . '&w=' . (intdiv($i, PLAN_DAYS_PER_WEEK) + 1) . '&d=' . ($i % PLAN_DAYS_PER_WEEK + 1);
};

ob_start(); ?>
<div class="preview-bar">
  <form method="get">
    <label>Pantalla<select name="s"><?php foreach ($screens as $k => $v) echo '<option value="' . $k . '"' . ($k === $screen ? ' selected' : '') . '>' . h($v) . '</option>'; ?></select></label>
    <label>Semana<select name="w"><?php for ($i = 1; $i <= PROGRAM_WEEKS; $i++) echo '<option' . ($i === $w ? ' selected' : '') . '>' . $i . '</option>'; ?></select></label>
    <label>Día<select name="d"><?php for ($i = 1; $i <= PLAN_DAYS_PER_WEEK; $i++) echo '<option' . ($i === $d ? ' selected' : '') . '>' . $i . '</option>'; ?></select></label>
    <label>Oficina<select name="office"><?php foreach ($offices as $o) echo '<option value="' . (int)$o['id'] . '"' . ((int)$o['id'] === (int)$office['id'] ? ' selected' : '') . '>' . h($o['name']) . '</option>'; ?></select></label>
    <button class="btn btn-sm">Ver</button>
  </form>
  <?php if ($screen === 'dia'): ?>
  <div class="pv-nav"><?php if ($idx > 0): ?><a href="<?= h($link($idx - 1)) ?>">← Día anterior</a><?php endif; ?>
    <?php if ($idx < PROGRAM_WEEKS * PLAN_DAYS_PER_WEEK - 1): ?><a href="<?= h($link($idx + 1)) ?>">Día siguiente →</a><?php endif; ?>
    <a href="admin_plan.php?w=<?= $w ?>">Editar esta semana</a></div>
  <?php endif; ?>
  <p class="small" style="margin:8px 0 0;color:#D5D9E3">Vista previa: nada se guarda. Las sesiones en la oficina muestran dónde iría su fecha y hora.</p>
</div>
<?php
$nav = ob_get_clean();

page_start('Ver como asesor', ['body' => 'advisor']);
echo '<p class="small"><a href="admin.php">← Volver al panel</a></p>';

if ($screen === 'bienvenida') {
    echo $nav;
    render_welcome('Ana', $office['name'], '', true);
    page_end();
    exit;
}

$fakeAdv = ['id' => 0, 'name' => 'Ana López', 'start_date' => add_days(date('Y-m-d'), -(($w - 1) * 7 + $d - 1)), 'token' => '',
    'oh_done' => 0, 'oh_date' => null, 'oh_attendees' => 0, 'cap_precio' => 0, 'cap_docs' => 0, 'cap_disp' => 0, 'renta_closed' => 0, 'renta_date' => null];
$type = ['dia' => 'plan', 'descanso' => 'rest', 'capacitacion' => 'training'][$screen];
// Día de arranque (llamadas) para esta posición del plan
$arr = null;
$k = 0;
for ($ww = 1; $ww <= PROGRAM_WEEKS && $arr === null; $ww++) {
    for ($dd = 1; $dd <= PLAN_DAYS_PER_WEEK; $dd++) {
        if ($dd < (int)(plan_weeks()[$ww]['routine_start_day'] ?? 1)) continue;
        $k++;
        if ($ww === $w && $dd === $d) { $arr = $k <= (int)setting('arranque_dias') ? $k : 0; break 2; }
        if ($k > (int)setting('arranque_dias')) { $arr = 0; break 2; }
    }
}
$ctx = ['type' => $type, 'week' => $w, 'day_no' => $type === 'plan' ? $d : 0, 'days' => $type === 'plan' ? [$d] : [], 'date' => date('Y-m-d'), 'arranque' => $arr ?: null];
$items = day_items($ctx, ['office' => $office, 'preview' => true]);
$zero = array_fill_keys(array_keys(RESULT_FIELDS), 0);
$stats = ['started' => true, 'finished' => false, 'elapsed' => ($w - 1) * 7 + $d - 1, 'week' => $w,
    'week_assigned' => array_fill(1, PROGRAM_WEEKS, 0), 'week_done' => array_fill(1, PROGRAM_WEEKS, 0),
    'totals' => $zero, 'week_totals' => array_fill(1, PROGRAM_WEEKS, $zero), 'done' => 0, 'assigned' => 0, 'pct' => 0,
    'workdays_elapsed' => 0, 'workdays_logged' => 0];
$stats['evaluation'] = evaluation($fakeAdv, $stats, $office);

render_hoy([
    'adv' => $fakeAdv, 'office' => $office, 'token' => '', 'preview' => true, 'preview_nav' => $nav,
    'date' => date('Y-m-d'), 'today' => date('Y-m-d'), 'ayer_mode' => false, 'ayer_offer' => null,
    'ctx' => $ctx, 'items' => $items, 'log' => null, 'stats' => $stats, 'goals' => [], 'sessions' => [],
]);
page_end();
