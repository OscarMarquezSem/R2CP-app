<?php
/**
 * ARIADNA · Administrador: editar el plan del curso Fast Track Residencial sin tocar código.
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';
ensure_schema();

enforce_https();
$u = require_user(['admin']);
prog_set((string)($_GET['prog'] ?? $_POST['prog'] ?? 'residencial'));
$pg = prog();
$w = max(1, min(prog_weeks(), (int)($_GET['w'] ?? $_POST['w'] ?? 1)));
$self = 'admin_plan.php?prog=' . $pg . '&w=' . $w;
$KINDS = KIND_LABELS;

$clean = function (array $a) use ($KINDS): array {
    $kind = array_key_exists($a['kind'] ?? '', $KINDS) ? $a['kind'] : 'check';
    $min = trim((string)($a['minutes'] ?? '')) === '' ? null : max(0, (int)$a['minutes']);
    $video = trim((string)($a['video_url'] ?? ''));
    $help = [];
    foreach (preg_split('/\R/', (string)($a['help_links'] ?? '')) as $line) {
        $p = array_map('trim', explode('|', $line, 2));
        if (count($p) === 2 && $p[0] !== '' && preg_match('#^https?://#i', $p[1])) $help[] = $p[0] . ' | ' . $p[1];
    }
    $reto = [];
    foreach (preg_split('/\R/', (string)($a['reto'] ?? '')) as $line) {
        $p = array_map('trim', explode('|', $line));
        if ($p[0] !== '') $reto[] = $p[0] . ' | ' . ($p[1] ?? '') . ' | ' . ($p[2] ?? '');
    }
    return [$kind, $min, preg_match('#^https?://#i', $video) ? mb_substr($video, 0, 500) : null, $help ? implode("\n", $help) : null, $kind === 'video' && $reto ? implode("\n", $reto) : null];
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    db()->beginTransaction();
    $m = fn($k) => max(0, (int)($_POST['meta'][$k] ?? 0));
    $mf = $pg === 'industrial' ? array_keys(result_fields()) : ['contactos', 'tratos', 'analizadas', 'promovidas', 'citas'];
    q('UPDATE plan_weeks SET title = ?, objective = ?, material = ?, remember = ?, review_questions = ?, routine_start_day = ?, '
        . implode(', ', array_map(fn($f) => "meta_$f = ?", $mf)) . ' WHERE program = ? AND week_no = ?', array_merge([
        post('title'), post('objective'), post('material'), post('remember'), post('review_questions'),
        max(1, min(PLAN_DAYS_PER_WEEK, (int)post('routine_start_day'))),
    ], array_map($m, $mf), [$pg, $w]));
    foreach ((array)($_POST['act'] ?? []) as $id => $a) {
        $id = (int)$id;
        if (!empty($a['delete'])) { q('UPDATE plan_activities SET active = 0 WHERE id = ? AND program = ? AND week_no = ?', [$id, $pg, $w]); continue; }
        $title = trim((string)($a['title'] ?? ''));
        if ($title === '') continue;
        [$kind, $min, $video, $help, $reto] = $clean($a);
        q('UPDATE plan_activities SET day_no = ?, sort = ?, title = ?, guide = ?, minutes = ?, kind = ?, video_url = ?, help_links = ?, reto = ? WHERE id = ? AND program = ? AND week_no = ?', [
            max(1, min(PLAN_DAYS_PER_WEEK, (int)$a['day_no'])), (int)$a['sort'], mb_substr($title, 0, 255), trim((string)$a['guide']), $min, $kind, $video, $help, $reto, $id, $pg, $w]);
    }
    foreach ((array)($_POST['new'] ?? []) as $a) {
        $title = trim((string)($a['title'] ?? ''));
        if ($title === '') continue;
        [$kind, $min, $video, $help, $reto] = $clean($a);
        $day = max(1, min(PLAN_DAYS_PER_WEEK, (int)$a['day_no']));
        $sort = (int)val('SELECT COALESCE(MAX(sort), 0) + 10 FROM plan_activities WHERE program = ? AND week_no = ? AND day_no = ?', [$pg, $w, $day]);
        q('INSERT INTO plan_activities (program, week_no, day_no, sort, title, guide, minutes, kind, video_url, help_links, reto) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
            [$pg, $w, $day, $sort, mb_substr($title, 0, 255), trim((string)$a['guide']), $min, $kind, $video, $help, $reto]);
    }
    db()->commit();
    flash('Semana ' . $w . ' guardada. Los cambios ya se ven en la app de los asesores.');
    redirect($self);
}

$week = one('SELECT * FROM plan_weeks WHERE program = ? AND week_no = ?', [$pg, $w]);
$acts = all('SELECT * FROM plan_activities WHERE program = ? AND week_no = ? AND active = 1 ORDER BY day_no, sort, id', [$pg, $w]);
$byDay = [];
foreach ($acts as $a) $byDay[(int)$a['day_no']][] = $a;
page_start('Plan', ['nav' => $u, 'active' => 'plan', 'wide' => true]);

$actFields = function (string $n, array $a) use ($KINDS): void { ?>
  <label>Actividad<input name="<?= $n ?>[title]" value="<?= h($a['title'] ?? '') ?>"></label>
  <label>Guía (qué es y cómo hacerla)<textarea name="<?= $n ?>[guide]" rows="2"><?= h($a['guide'] ?? '') ?></textarea></label>
  <div class="grid-act">
    <label>Tipo<select name="<?= $n ?>[kind]"><?php foreach ($KINDS as $k => $v): ?><option value="<?= $k ?>" <?= ($a['kind'] ?? 'check') === $k ? 'selected' : '' ?>><?= h($v) ?></option><?php endforeach; ?></select></label>
    <label>Minutos<input type="number" min="0" name="<?= $n ?>[minutes]" value="<?= h($a['minutes'] ?? '') ?>"></label>
    <label>Día<select name="<?= $n ?>[day_no]"><?php for ($x = 1; $x <= PLAN_DAYS_PER_WEEK; $x++): ?><option value="<?= $x ?>" <?= (int)($a['day_no'] ?? 1) === $x ? 'selected' : '' ?>>Día <?= $x ?></option><?php endfor; ?></select></label>
    <label>Orden<input type="number" name="<?= $n ?>[sort]" value="<?= (int)($a['sort'] ?? 0) ?>"></label>
  </div>
  <label>Enlace del video (solo tipo «Video del curso»: el enlace de Bunny, YouTube o Vimeo)<input name="<?= $n ?>[video_url]" value="<?= h($a['video_url'] ?? '') ?>" placeholder="https://player.mediadelivery.net/play/…"></label>
  <label>Videos de apoyo opcionales (uno por renglón: Título | enlace)<textarea name="<?= $n ?>[help_links]" rows="2" placeholder="Cómo utilizar Canva | https://youtu.be/…"><?= h($a['help_links'] ?? '') ?></textarea></label>
  <label>Reto del video (solo tipo «Video del curso»: 5 renglones, cada uno «Pregunta | Respuesta clave | minuto»)<textarea name="<?= $n ?>[reto]" rows="<?= ($a['kind'] ?? '') === 'video' ? 6 : 1 ?>" placeholder="¿Qué cuenta como un contacto? | Decir uno de los diálogos, no solo saludar. | 20-23"><?= h($a['reto'] ?? '') ?></textarea></label>
<?php };
?>
<div class="page-head"><div><span class="eyebrow">Administrador · <?= h(P()['name']) ?></span><h1>Plan del curso <?= h(P()['course']) ?></h1></div>
  <div class="actions"><a class="btn" href="admin_preview.php?prog=<?= h($pg) ?>&amp;w=<?= $w ?>&amp;d=1">Ver esta semana como asesor</a></div></div>
<nav class="prog-tabs" aria-label="Programa"><?php foreach (PROGRAMS as $pk => $pd): ?><a class="<?= $pk === $pg ? 'on' : '' ?>" href="admin_plan.php?prog=<?= h($pk) ?>"><?= h($pd['name'] . ' · ' . $pd['area']) ?></a><?php endforeach; ?></nav>
<div class="tabs"><?php for ($i = 1; $i <= prog_weeks(); $i++): ?><a class="<?= $i === $w ? 'on' : '' ?>" href="admin_plan.php?prog=<?= h($pg) ?>&amp;w=<?= $i ?>">Semana <?= $i ?></a><?php endfor; ?></div>
<p class="small muted">Los días son días hábiles de la semana personal de cada asesor (Día 1 a Día 5). La rutina diaria (llamadas y registro) se agrega sola; se edita en Ajustes. Tipos: <b>Sesión en tu oficina</b> la da el TL o el gerente y la oficina le pone fecha y hora; <b>Video del curso</b> se reproduce dentro de Ariadna; si no tiene enlace, el asesor ve «Este video estará listo muy pronto». Escribe {OH} donde deba decir «Open House», «Recorrido en vivo» o «Jornada de opinión de valor» según la oficina, y {ASISTENTES} para «asistentes», «interesados» o «propietarios interesados». Lo que va entre {jornada} y {/jornada} solo lo ven las oficinas con Jornada; lo que va entre {evento} y {/evento}, solo las de Open House o Recorrido.</p>

<form method="post" class="form">
<?= csrf_field() ?><input type="hidden" name="w" value="<?= $w ?>"><input type="hidden" name="prog" value="<?= h($pg) ?>">
<div class="card">
  <h2>Semana <?= $w ?></h2>
  <label>Título<input name="title" required value="<?= h($week['title']) ?>"></label>
  <label>Objetivo de la semana<textarea name="objective" rows="2"><?= h($week['objective']) ?></textarea></label>
  <div class="grid2">
    <label>Material de la semana (uno por renglón)<textarea name="material" rows="4"><?= h($week['material']) ?></textarea></label>
    <label>Lo que te pido que recuerdes (uno por renglón)<textarea name="remember" rows="4"><?= h($week['remember']) ?></textarea></label>
  </div>
  <label>Preguntas del repaso semanal (una por renglón)<textarea name="review_questions" rows="3"><?= h($week['review_questions']) ?></textarea></label>
  <label>La rutina diaria empieza el
    <select name="routine_start_day"><?php for ($d = 1; $d <= PLAN_DAYS_PER_WEEK; $d++): ?><option value="<?= $d ?>" <?= (int)$week['routine_start_day'] === $d ? 'selected' : '' ?>>Día <?= $d ?></option><?php endfor; ?></select></label>
  <h3>Mínimos del programa en esta semana</h3>
  <p class="small muted">Se usan para el semáforo, la evaluación y el aviso de metas bajas. Los contactos son el total de la semana; el mínimo diario está en Ajustes.</p>
  <div class="grid2">
    <?php foreach ($pg === 'industrial' ? array_keys(result_fields()) : ['contactos', 'tratos', 'analizadas', 'promovidas', 'citas'] as $f): ?>
      <label><?= h(result_fields()[$f]) ?><input type="number" min="0" name="meta[<?= $f ?>]" value="<?= (int)$week['meta_' . $f] ?>"></label>
    <?php endforeach; ?>
  </div>
</div>

<?php for ($d = 1; $d <= PLAN_DAYS_PER_WEEK; $d++): ?>
<div class="card">
  <h2>Día <?= $d ?> <a class="small" href="admin_preview.php?w=<?= $w ?>&amp;d=<?= $d ?>" style="font-weight:600">Ver como asesor</a></h2>
  <?php if (empty($byDay[$d])): ?><p class="muted">Día sin actividades (solo la rutina diaria, si aplica).</p><?php endif; ?>
  <?php foreach ($byDay[$d] ?? [] as $a): ?>
    <div class="plan-act"><?php $actFields('act[' . (int)$a['id'] . ']', $a); ?>
      <label class="check small"><input type="checkbox" name="act[<?= (int)$a['id'] ?>][delete]" value="1"> Quitar esta actividad</label></div>
  <?php endforeach; ?>
  <details>
    <summary class="small"><b>+ Agregar actividad al Día <?= $d ?></b></summary>
    <div class="plan-act"><input type="hidden" name="new[<?= $d ?>][day_no]" value="<?= $d ?>"><?php $actFields('new[' . $d . ']', ['day_no' => $d]); ?></div>
  </details>
</div>
<?php endfor; ?>
<div class="savebar"><button class="btn btn-main btn-block btn-xl">Guardar Semana <?= $w ?></button></div>
</form>
<?php page_end();
