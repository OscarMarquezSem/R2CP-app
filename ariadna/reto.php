<?php
/**
 * ARIADNA · Reto del video: el Team Leader (o el gerente o el director) hace 5 preguntas
 * de palabra después de que el asesor ve el video, y marca cada una.
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';

enforce_https();
$u = require_user();
ensure_schema();
$office = panel_office($u);
$qs = office_qs($office);
$amp = $qs ? '&' . $qs : '';
$adv = office_advisor($office, (int)($_GET['id'] ?? $_POST['id'] ?? 0));
prog_set(advisor_program($adv));
$videos = reto_videos();
$aid = (int)($_GET['a'] ?? $_POST['a'] ?? 0);
$act = $videos[$aid] ?? null;
$back = 'asesor.php?id=' . (int)$adv['id'] . $amp . '#retos';
if (!$act) redirect($back);
$questions = array_slice(reto_questions($act), 0, 5);
$label = reto_label($act['title']);
$row = one('SELECT * FROM advisor_retos WHERE advisor_id = ? AND activity_id = ?', [$adv['id'], $aid]);
$prev = $row ? str_split((string)$row['answers']) : [];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $ans = '';
    foreach ($questions as $i => $_) {
        $v = $_POST['q'][$i] ?? '';
        if (!in_array($v, ['o', 'r'], true)) { $error = 'Marca todas las preguntas antes de guardar.'; break; }
        $ans .= $v;
    }
    if (!$error) {
        $ok = substr_count($ans, 'o');
        $note = mb_substr(trim((string)post('note')), 0, 1000);
        $now = date('Y-m-d H:i:s');
        if ($row) {
            q('UPDATE advisor_retos SET answers = ?, ok_count = ?, attempt = attempt + 1, note = ?, user_id = ?, user_name = ?, updated_at = ? WHERE id = ?',
                [$ans, $ok, $note, $u['id'], $u['name'], $now, $row['id']]);
        } else {
            q('INSERT INTO advisor_retos (advisor_id, activity_id, answers, ok_count, attempt, note, user_id, user_name, created_at, updated_at) VALUES (?,?,?,?,1,?,?,?,?,?)',
                [$adv['id'], $aid, $ans, $ok, $note, $u['id'], $u['name'], $now, $now]);
        }
        flash($ok >= 4 ? first_name($adv['name']) . ' superó su Reto del ' . $label . ' (' . $ok . ' de 5).'
            : ($ok === 3 ? 'Reto guardado: 3 de 5, medio punto. En su cierre de semana vuelve a hacerle las que no supo.'
            : 'Reto guardado: ' . $ok . ' de 5. Ariadna le pide repasar el video; en su cierre de semana le vuelves a preguntar.'));
        redirect($back);
    }
    $prev = array_map(fn($i) => (string)($_POST['q'][$i] ?? ''), array_keys($questions));
}

page_start('Reto del video', ['nav' => $u, 'office' => $office, 'active' => 'oficina']);
?>
<p class="small muted"><a href="<?= h($back) ?>">← <?= h($adv['name']) ?></a></p>
<div class="card reto-head">
  <span class="eyebrow">Reto del video</span>
  <h1 style="margin:4px 0"><?= h($label) ?></h1>
  <p><b><?= h($adv['name']) ?></b> · Semana <?= (int)$act['week_no'] ?>, día <?= (int)$act['day_no'] ?></p>
  <p>Hazle las 5 preguntas <b>de palabra</b>, sin que lea la respuesta. No tiene que decirlo igual: basta con que diga la idea. Si no la sabe, explícasela con la respuesta clave y dile en qué minuto del video está.</p>
  <?php if ($row): ?>
    <div class="box">Ya tiene un Reto guardado: <b><?= (int)$row['ok_count'] ?> de 5</b> (lo aplicó <?= h($row['user_name'] ?? '') ?>). Si lo vuelves a aplicar, por ejemplo en su cierre de semana, se guarda el resultado nuevo.</div>
  <?php endif; ?>
</div>
<?php if ($error): ?><div class="box warn"><?= h($error) ?></div><?php endif; ?>
<?php if (!$questions): ?>
  <div class="box warn">Este video todavía no tiene preguntas. El administrador las agrega en Plan.</div>
<?php else: ?>
<form method="post" class="form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)$adv['id'] ?>"><input type="hidden" name="a" value="<?= $aid ?>">
  <?php if (is_admin($u)): ?><input type="hidden" name="office" value="<?= (int)$office['id'] ?>"><?php endif; ?>
  <?php foreach ($questions as $i => $q): $p = $prev[$i] ?? ''; ?>
    <div class="card">
      <span class="eyebrow">Pregunta <?= $i + 1 ?><?= $q['min'] !== '' ? ' · minuto ' . h($q['min']) . ' del video' : '' ?></span>
      <p style="font-weight:600;margin:6px 0 10px"><?= h($q['q']) ?></p>
      <div class="box" style="margin:0 0 10px"><b>Respuesta clave:</b> <?= h($q['a']) ?></div>
      <div class="grid2">
        <label class="check"><input type="radio" name="q[<?= $i ?>]" value="o" required<?= $p === 'o' ? ' checked' : '' ?>> La entendió</label>
        <label class="check"><input type="radio" name="q[<?= $i ?>]" value="r"<?= $p === 'r' ? ' checked' : '' ?>> Hay que repasar</label>
      </div>
    </div>
  <?php endforeach; ?>
  <div class="card">
    <label>Comentario para <?= h(first_name($adv['name'])) ?> (opcional)
      <textarea name="note" rows="2" maxlength="1000" placeholder="Ej.: Repasa la parte de cómo promover tu Open House."><?= h($row['note'] ?? '') ?></textarea></label>
    <p class="small muted">4 o 5 de 5: supera el Reto y gana 1 punto · 3 de 5: medio punto · 2 o menos: repasa el video. En su cierre de semana le puedes volver a preguntar las que no supo.</p>
    <button class="btn btn-main btn-block">Guardar Reto</button>
  </div>
</form>
<?php endif; ?>
<?php page_end();
