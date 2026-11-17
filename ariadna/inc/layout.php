<?php
/**
 * ARIADNA · Plantilla HTML común.
 */
declare(strict_types=1);

function page_start(string $title, array $opt = []): void
{
    security_headers();
    $manifest = $opt['manifest'] ?? 'manifest.php';
    $bodyClass = $opt['body'] ?? '';
    ?><!doctype html>
<html lang="es-MX">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#F7F3EA">
<meta name="robots" content="noindex, nofollow">
<title><?= h($title) ?> · Ariadna</title>
<link rel="manifest" href="<?= h($manifest) ?>">
<link rel="icon" type="image/png" href="assets/icon-192.png">
<link rel="apple-touch-icon" href="assets/icon-192.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Ariadna">
<link rel="stylesheet" href="assets/style.css?v=<?= APP_VERSION ?>">
</head>
<body class="<?= h($bodyClass) ?>">
<?php
    if (!empty($opt['nav'])) panel_nav($opt['nav'], $opt['office'] ?? null, $opt['active'] ?? '');
    echo '<main class="wrap' . (!empty($opt['wide']) ? ' wide' : '') . '">';
    foreach (flash() as [$type, $msg]) echo '<div class="flash flash-' . h($type) . '">' . h($msg) . '</div>';
}

function page_end(): void
{
    echo '</main><script src="assets/app.js?v=' . APP_VERSION . '"></script></body></html>';
}

function brand_mark(): string
{
    return '<span class="brand"><svg class="brand-thread" viewBox="0 0 40 24" aria-hidden="true"><path d="M2 18 C 10 2, 18 22, 26 8 S 36 6, 38 4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/><circle cx="38" cy="4" r="2.6" fill="#C75D3A"/></svg><span class="brand-txt"><b>ARIADNA</b><small>by Oscar Márquez Seminars</small></span></span>';
}

function panel_nav(array $u, ?array $office, string $active): void
{
    $qs = $office && is_admin($u) ? '?office=' . (int)$office['id'] : '';
    echo '<header class="topbar"><div class="topbar-in">';
    echo '<a class="logo" href="' . (is_admin($u) && !$office ? 'admin.php' : 'oficina.php' . $qs) . '">' . brand_mark() . '</a>';
    echo '<nav>';
    if ($office) {
        echo '<a class="' . ($active === 'oficina' ? 'on' : '') . '" href="oficina.php' . $qs . '">Asesores</a>';
        echo '<a class="' . ($active === 'sesiones' ? 'on' : '') . '" href="sesiones.php' . $qs . '">Sesiones</a>';
        echo '<a class="' . ($active === 'config' ? 'on' : '') . '" href="config_oficina.php' . $qs . '">Configuración</a>';
    }
    if (is_admin($u)) {
        echo '<a class="' . ($active === 'admin' ? 'on' : '') . '" href="admin.php">Oficinas</a>';
        echo '<a class="' . ($active === 'plan' ? 'on' : '') . '" href="admin_plan.php">Plan</a>';
        echo '<a class="' . ($active === 'preview' ? 'on' : '') . '" href="admin_preview.php">Ver como asesor</a>';
        echo '<a class="' . ($active === 'ajustes' ? 'on' : '') . '" href="admin_ajustes.php">Ajustes</a>';
    }
    echo '<a class="' . ($active === 'cuenta' ? 'on' : '') . '" href="cuenta.php">Mi cuenta</a>';
    echo '<a href="logout.php">Salir</a>';
    echo '<span class="r2cp">R²CP ELITE</span>';
    echo '</nav></div></header>';
    if ($office) {
        echo '<div class="subbar"><div class="topbar-in"><span>' . h($office['name']) . '</span><span class="muted">'
            . h($u['name']) . ' · ' . h(ROLE_LABELS[$u['role']] ?? $u['role']) . '</span></div></div>';
    }
}

function bar(int $pct, string $cls = ''): string
{
    $pct = max(0, min(100, $pct));
    return '<div class="bar ' . h($cls) . '"><span style="width:' . $pct . '%"></span></div>';
}

function eval_table(array $ev): void
{
    echo '<table class="tbl eval"><thead><tr><th>Criterio</th><th>Meta</th><th>Resultado</th><th>Puntos</th></tr></thead><tbody>';
    $g = '';
    foreach ($ev['rows'] as $r) {
        if ($r['group'] !== $g) { $g = $r['group']; echo '<tr class="grp"><td colspan="4">' . h($g) . '</td></tr>'; }
        echo '<tr><td>' . h($r['label']) . '</td><td>' . h((string)$r['meta']) . '</td><td>' . h((string)$r['result']) . '</td><td><b>'
            . h(rtrim(rtrim(number_format($r['pts'], 1), '0'), '.')) . '</b> / ' . (int)$r['max'] . '</td></tr>';
    }
    echo '<tr class="total"><td colspan="3">Total</td><td><b>' . (int)$ev['total'] . '</b> / 100</td></tr></tbody></table>';
}

function kpi_grid(array $s): void
{
    echo '<div class="kpis">';
    foreach (RESULT_FIELDS as $f => $label) {
        $meta = plan_meta_total($f);
        $v = $s['totals'][$f];
        echo '<div class="kpi"><span class="kpi-v">' . $v . ($meta ? '<small>/' . $meta . '</small>' : '') . '</span><span class="kpi-l">' . h($label) . '</span>'
            . ($meta ? bar((int)round(min(1, $v / $meta) * 100)) : '') . '</div>';
    }
    echo '</div>';
}
