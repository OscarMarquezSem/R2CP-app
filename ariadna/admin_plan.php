<?php
/**
 * ARIADNA · Administrador: editar el plan de 5 semanas sin tocar código.
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';

enforce_https();
$u = require_user(['admin']);
$w = max(1, min(PROGRAM_WEEKS, (int)($_GET['w'] ?? $_POST['w'] ?? 1)));
$self = 'admin_plan.php?w=' . $w;
$KINDS = ['check' => 'Normal', 'foto' => 'Con foto', 'agenda' => 'Agenda de la semana', 'repaso' => 'Repaso semanal'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    db()->beginTransaction();
    $m = fn($k) => max(0, (int)($_POST['meta'][$k] ?? 0));
    q('UPDATE plan_weeks SET title = ?, objective = ?, material = ?, remember = ?, review_questions = ?, routine_start_day = ?,
        meta_contactos = ?, meta_tratos = ?, meta_analizadas = ?, meta_promovidas = ?, meta_citas = ? WHERE week_no = ?', [
        post('title'), post('objective'), post('material'), post('remember'), post('review_questions'),
        max(1, min(PLAN_DAYS_PER_WEEK, (int)post('routine_start_day'))),
        $m('contactos'), $m('tratos'), $m('analizadas'), $m('promovidas'), $m('citas'), $w,
    ]);
    foreach ((array)($_POST['act'] ?? []) as $id => $a) {
        $id = (int)$id;
        if (!empty($a['delete'])) { q('UPDATE plan_activities SET active = 0 WHERE id = ? AND week_no = ?', [$id, $w]); continue; }
        $title = trim((string)($a['title'] ?? ''));
        if ($title === '') continue;
        $kind = array_key_exists($a['kind'] ?? '', $KINDS) ? $a['kind'] : 'check';
        $min = trim((string)($a['minutes'] ?? '')) === '' ? null : max(0, (int)$a['minutes']);
        q('UPDATE plan_activities SET day_no = ?, sort = ?, title = ?, guide = ?, minutes = ?, kind = ? WHERE id = ? AND week_no = ?', [
            max(1, min(PLAN_DAYS_PER_WEEK, (int)$a['day_no'])), (int)$a['sort'], mb_substr($title, 0, 255), trim((string)$a['guide']), $min, $kind, $id, $w]);
    }
    foreach ((array)($_POST['new'] ?? []) as $a) {
        $title = trim((string)($a['title'] ?? ''));
        if ($title === '') continue;
        $kind = array_key_exists($a['kind'] ?? '', $KINDS) ? $a['kind'] : 'check';
        $min = trim((string)($a['minutes'] ?? '')) === '' ? null : max(0, (int)$a['minutes']);
        $day = max(1, min(PLAN_DAYS_PER_WEEK, (int)$a['day_no']));
        $sort = (int)val('SELECT COALESCE(MAX(sort), 0) + 10 FROM plan_activities WHERE week_no = ? AND day_no = ?', [$w, $day]);
        q('INSERT INTO plan_activities (week_no, day_no, sort, title, guide, minutes, kind) VALUES (?,?,?,?,?,?,?)',
            [$w, $day, $sort, mb_substr($title, 0, 255), trim((string)$a['guide']), $min, $kind]);
    }
    db()->commit();
    flash('Semana ' . $w . ' guardada. Los cambios ya se ven en la app de los asesores.');
    redirect($self);
}

$week = one('SELECT * FROM plan_weeks WHERE week_no = ?', [$w]);
$acts = all('SELECT * FROM plan_activities WHERE week_no = ? AND active = 1 ORDER BY day_no, sort, id', [$w]);
$byDay = [];
foreach ($acts as $a) $byDay[(int)$a['day_no']][] = $a;
page_start('Plan', ['nav' => $u, 'active' => 'plan', 'wide' => true]);
?>
<div class="page-head"><div><span class="eyebrow">Administrador</span><h1>Plan de 5 semanas</h1></div></div>
<div class="tabs"><?php for ($i = 1; $i <= PROGRAM_WEEKS; $i++): ?><a class="<?= $i === $w ? 'on' : '' ?>" href="admin_plan.php?w=<?= $i ?>">Semana <?= $i ?></a><?php endfor; ?></div>
<p class="small muted">Los días son días laborables de la semana personal de cada asesor (Día 1 a Día 5). La rutina diaria (2 contactos y registro) se agrega sola; se edita en Ajustes. Un día puede quedar vacío.</p>

<form method="post" class="form">
<?= csrf_field() ?><input type="hidden" name="w" value="<?= $w ?>">
<div class="card">
  <h2>Semana <?= $w ?></h2>
  <label>Título<input name="title" required value="<?= h($week['title']) ?>"></label>
  <label>Objetivo de la semana<textarea name="objective" rows="2"><?= h($week['objective']) ?></textarea></label>
  <div class="grid2">
    <label>Material de la semana (uno por renglón)<textarea name="material" rows="4"><?= h($week['material']) ?></textarea></label>
    <label>Lo que debes recordar (uno por renglón)<textarea name="remember" rows="4"><?= h($week['remember']) ?></textarea></label>
  </div>
  <label>Preguntas del repaso semanal (una por renglón)<textarea name="review_questions" rows="3"><?= h($week['review_questions']) ?></textarea></label>
  <label>La rutina diaria empieza el
    <select name="routine_start_day"><?php for ($d = 1; $d <= PLAN_DAYS_PER_WEEK; $d++): ?><option value="<?= $d ?>" <?= (int)$week['routine_start_day'] === $d ? 'selected' : '' ?>>Día <?= $d ?></option><?php endfor; ?></select></label>
  <h3>Metas de la semana</h3>
  <div class="grid2">
    <?php foreach (['contactos', 'tratos', 'analizadas', 'promovidas', 'citas'] as $f): ?>
      <label><?= h(RESULT_FIELDS[$f]) ?><input type="number" min="0" name="meta[<?= $f ?>]" value="<?= (int)$week['meta_' . $f] ?>"></label>
    <?php endforeach; ?>
  </div>
</div>

<?php for ($d = 1; $d <= PLAN_DAYS_PER_WEEK; $d++): ?>
<div class="card">
  <h2>Día <?= $d ?></h2>
  <?php if (empty($byDay[$d])): ?><p class="muted">Día sin actividades (solo la rutina diaria, si aplica).</p><?php endif; ?>
  <?php foreach ($byDay[$d] ?? [] as $a): $id = (int)$a['id']; ?>
    <div class="plan-act">
      <label>Actividad<input name="act[<?= $id ?>][title]" value="<?= h($a['title']) ?>"></label>
      <label>Guía (qué es y cómo hacerla)<textarea name="act[<?= $id ?>][guide]" rows="2"><?= h($a['guide']) ?></textarea></label>
      <div class="grid-act">
        <label>Tipo<select name="act[<?= $id ?>][kind]"><?php foreach ($KINDS as $k => $v): ?><option value="<?= $k ?>" <?= $a['kind'] === $k ? 'selected' : '' ?>><?= h($v) ?></option><?php endforeach; ?></select></label>
        <label>Minutos<input type="number" min="0" name="act[<?= $id ?>][minutes]" value="<?= h($a['minutes'] ?? '') ?>"></label>
        <label>Día<select name="act[<?= $id ?>][day_no]"><?php for ($x = 1; $x <= PLAN_DAYS_PER_WEEK; $x++): ?><option value="<?= $x ?>" <?= (int)$a['day_no'] === $x ? 'selected' : '' ?>>Día <?= $x ?></option><?php endfor; ?></select></label>
        <label>Orden<input type="number" name="act[<?= $id ?>][sort]" value="<?= (int)$a['sort'] ?>"></label>
      </div>
      <label class="check small"><input type="checkbox" name="act[<?= $id ?>][delete]" value="1"> Quitar esta actividad</label>
    </div>
  <?php endforeach; ?>
  <details>
    <summary class="small"><b>+ Agregar actividad al Día <?= $d ?></b></summary>
    <div class="plan-act">
      <input type="hidden" name="new[<?= $d ?>][day_no]" value="<?= $d ?>">
      <label>Actividad<input name="new[<?= $d ?>][title]"></label>
      <label>Guía<textarea name="new[<?= $d ?>][guide]" rows="2"></textarea></label>
      <div class="grid2">
        <label>Tipo<select name="new[<?= $d ?>][kind]"><?php foreach ($KINDS as $k => $v): ?><option value="<?= $k ?>"><?= h($v) ?></option><?php endforeach; ?></select></label>
        <label>Minutos<input type="number" min="0" name="new[<?= $d ?>][minutes]"></label>
      </div>
    </div>
  </details>
</div>
<?php endfor; ?>
<div class="savebar"><button class="btn btn-main btn-block btn-xl">Guardar Semana <?= $w ?></button></div>
</form>
<?php page_end();
