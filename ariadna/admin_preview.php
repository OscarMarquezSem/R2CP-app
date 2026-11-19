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

prog_set((string)($_GET['prog'] ?? 'residencial'));
$pg = prog();
$screens = ['dia' => 'Día del curso', 'bienvenida' => 'Bienvenida (primera vez)', 'descanso' => 'Día de descanso', 'capacitacion' => 'Capacitación interna', 'celebracion' => 'Al guardar el día (celebración)', 'graduacion' => 'Fin del curso (graduación)'];
$screen = array_key_exists($_GET['s'] ?? '', $screens) ? $_GET['s'] : 'dia';
$w = max(1, min(prog_weeks(), (int)($_GET['w'] ?? 1)));
$d = max(1, min(PLAN_DAYS_PER_WEEK, (int)($_GET['d'] ?? 1)));

// Navegación anterior / siguiente
$idx = ($w - 1) * PLAN_DAYS_PER_WEEK + ($d - 1);
$link = function (int $i) use ($office): string {
    $i = max(0, min(prog_weeks() * PLAN_DAYS_PER_WEEK - 1, $i));
    return 'admin_preview.php?prog=' . prog() . '&office=' . (int)$office['id'] . '&w=' . (intdiv($i, PLAN_DAYS_PER_WEEK) + 1) . '&d=' . ($i % PLAN_DAYS_PER_WEEK + 1);
};

ob_start(); ?>
<div class="preview-bar">
  <form method="get">
    <label>Programa<select name="prog"><?php foreach (PROGRAMS as $pk => $pd) echo '<option value="' . $pk . '"' . ($pk === $pg ? ' selected' : '') . '>' . h($pd['name']) . '</option>'; ?></select></label>
    <label>Pantalla<select name="s"><?php foreach ($screens as $k => $v) echo '<option value="' . $k . '"' . ($k === $screen ? ' selected' : '') . '>' . h($v) . '</option>'; ?></select></label>
    <label>Semana<select name="w"><?php for ($i = 1; $i <= prog_weeks(); $i++) echo '<option' . ($i === $w ? ' selected' : '') . '>' . $i . '</option>'; ?></select></label>
    <label>Día<select name="d"><?php for ($i = 1; $i <= PLAN_DAYS_PER_WEEK; $i++) echo '<option' . ($i === $d ? ' selected' : '') . '>' . $i . '</option>'; ?></select></label>
    <label>Oficina<select name="office"><?php foreach ($offices as $o) echo '<option value="' . (int)$o['id'] . '"' . ((int)$o['id'] === (int)$office['id'] ? ' selected' : '') . '>' . h($o['name']) . '</option>'; ?></select></label>
    <button class="btn btn-sm">Ver</button>
  </form>
  <?php if ($screen === 'dia'): ?>
  <div class="pv-nav"><?php if ($idx > 0): ?><a href="<?= h($link($idx - 1)) ?>">← Día anterior</a><?php endif; ?>
    <?php if ($idx < prog_weeks() * PLAN_DAYS_PER_WEEK - 1): ?><a href="<?= h($link($idx + 1)) ?>">Día siguiente →</a><?php endif; ?>
    <a href="admin_plan.php?prog=<?= h($pg) ?>&amp;w=<?= $w ?>">Editar esta semana</a></div>
  <?php endif; ?>
  <p class="small" style="margin:8px 0 0;color:#D5D9E3">Vista previa: nada se guarda. Las sesiones en la oficina muestran dónde iría su fecha y hora.</p>
</div>
<?php
$nav = ob_get_clean();

page_start('Ver como asesor', ['body' => 'advisor prog-' . $pg, 'brand' => P()['name']]);
echo '<p class="small"><a href="admin.php">← Volver al panel</a></p>';

if ($screen === 'bienvenida') {
    echo $nav;
    render_welcome($pg === 'industrial' ? 'Luis' : 'Ana', $office['name'], '', true);
    page_end();
    exit;
}

$fakeAdv = ['id' => 0, 'program' => $pg, 'name' => $pg === 'industrial' ? 'Luis Herrera' : 'Ana López',
    'ind_cap_done' => 0, 'ind_cap_date' => null, 'ind_oferta_done' => 0, 'ind_oferta_date' => null, 'ind_inq_done' => 0, 'ind_inq_date' => null, 'know_props' => null, 'know_zones' => null, 'decision' => null, 'start_date' => add_days(date('Y-m-d'), -(($w - 1) * 7 + $d - 1)), 'token' => '',
    'oh_done' => 0, 'oh_date' => null, 'oh_attendees' => 0, 'cap_precio' => 0, 'cap_docs' => 0, 'cap_disp' => 0, 'renta_closed' => 0, 'renta_date' => null];
$type = ['dia' => 'plan', 'descanso' => 'rest', 'capacitacion' => 'training', 'celebracion' => 'plan', 'graduacion' => 'plan'][$screen];
// Día de arranque (llamadas) para esta posición del plan
$arr = null;
$k = 0;
for ($ww = 1; $ww <= prog_weeks() && $arr === null; $ww++) {
    for ($dd = 1; $dd <= PLAN_DAYS_PER_WEEK; $dd++) {
        if ($dd < (int)(plan_weeks()[$ww]['routine_start_day'] ?? 1)) continue;
        $k++;
        if ($ww === $w && $dd === $d) { $arr = $k <= (int)setting('arranque_dias') ? $k : 0; break 2; }
        if ($k > (int)setting('arranque_dias') || $pg === 'industrial') { $arr = 0; break 2; }
    }
}
$ctx = ['type' => $type, 'week' => $w, 'day_no' => $type === 'plan' ? $d : 0, 'days' => $type === 'plan' ? [$d] : [], 'date' => date('Y-m-d'), 'arranque' => $arr ?: null];
$items = day_items($ctx, ['office' => $office, 'preview' => true]);
$zero = array_fill_keys(array_keys(result_fields()), 0);
$stats = ['started' => true, 'finished' => false, 'elapsed' => ($w - 1) * 7 + $d - 1, 'week' => $w,
    'week_assigned' => array_fill(1, prog_weeks(), 0), 'week_done' => array_fill(1, prog_weeks(), 0),
    'totals' => $zero, 'week_totals' => array_fill(1, prog_weeks(), $zero), 'done' => 0, 'assigned' => 0, 'pct' => 0,
    'workdays_elapsed' => 0, 'workdays_logged' => 0, 'program' => $pg, 'program_days' => prog_weeks() * 7,
    'retos' => ['total' => 0, 'due' => 0, 'done' => 0, 'points' => 0.0, 'list' => []], 'filtro' => null];
$stats['evaluation'] = evaluation($fakeAdv, $stats, $office);

if ($screen === 'graduacion') { $stats['finished'] = true; }
render_hoy([
    'celebrate' => $screen === 'celebracion' ? T('saved') : null,
    'adv' => $fakeAdv, 'office' => $office, 'token' => '', 'preview' => true, 'preview_nav' => $nav,
    'date' => date('Y-m-d'), 'today' => date('Y-m-d'), 'ayer_mode' => false, 'ayer_offer' => null,
    'ctx' => $ctx, 'items' => $items, 'log' => null, 'stats' => $stats, 'goals' => [], 'sessions' => [],
]);
page_end();
