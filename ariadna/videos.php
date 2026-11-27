<?php
/**
 * ARIADNA · Videos del curso para ponerlos en la oficina (director, gerente y team leader).
 * Sirve sobre todo a las oficinas donde los asesores ven los videos solo en la oficina.
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';

enforce_https();
$u = require_user();
ensure_schema();
$office = panel_office($u);
$qs = office_qs($office);
$progs = office_programs($office);
$pg = in_array($_GET['p'] ?? '', $progs, true) ? $_GET['p'] : $progs[0];
prog_set($pg);
$w = max(1, min(prog_weeks(), (int)($_GET['w'] ?? 1)));
$base = 'videos.php?' . ($qs ? $qs . '&' : '');

$videos = [];
foreach (plan_activities()[$w] ?? [] as $day => $list) {
    foreach ($list as $a) if ($a['kind'] === 'video') $videos[] = ['day' => (int)$day, 'a' => $a];
}

page_start('Videos', ['nav' => $u, 'office' => $office, 'active' => 'videos', 'wide' => true]);
?>
<div class="page-head"><div><span class="eyebrow">Panel de la oficina</span><h1>Videos del curso</h1></div></div>
<?php if (count($progs) > 1): ?>
<nav class="prog-tabs" aria-label="Programa" style="margin-bottom:18px"><?php foreach ($progs as $pk): ?><a class="<?= $pk === $pg ? 'on' : '' ?>" href="<?= h($base . 'p=' . $pk) ?>"><?= h(P($pk)['name'] . ' · ' . P($pk)['area']) ?></a><?php endforeach; ?></nav>
<?php endif; ?>
<p class="muted"><?= videos_office_only($office, $pg)
    ? 'En tu oficina, los asesores de ' . h(P()['name']) . ' ven los videos <b>aquí, en la oficina</b>: en su app solo aparece la actividad. Ponles el video del día y, al terminar, aplícales su Reto.'
    : 'Tus asesores de ' . h(P()['name']) . ' también los ven en su app. Aquí los tienes para ponerlos en la oficina o repasarlos con ellos.' ?></p>
<div class="tabs"><?php for ($i = 1; $i <= prog_weeks(); $i++): ?><a class="<?= $i === $w ? 'on' : '' ?>" href="<?= h($base . 'p=' . $pg . '&w=' . $i) ?>">Semana <?= $i ?></a><?php endfor; ?></div>
<?php if (!$videos): ?>
  <div class="box">Esta semana no tiene videos.</div>
<?php else: ?>
<div class="vid-list">
  <?php foreach ($videos as $v): $a = $v['a']; $src = video_embed_url($a['video_url']); ?>
    <div class="vid-card">
      <span class="eyebrow">Semana <?= $w ?> · Día <?= $v['day'] ?></span>
      <h3><?= h(preg_replace('/^Ve (el|los) /u', '', $a['title'])) ?></h3>
      <?php if ($src): ?>
        <div class="video-wrap"><iframe src="<?= h($src) ?>" loading="lazy" allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen title="<?= h($a['title']) ?>"></iframe></div>
      <?php else: ?>
        <div class="video-soon">Este video estará listo muy pronto.</div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php page_end();
