<?php
/** Manifiesto de la PWA. Con ?t= abre directo la pantalla "Hoy" del asesor. */
declare(strict_types=1);
$t = isset($_GET['t']) && preg_match('/^[a-f0-9]{64}$/', (string)$_GET['t']) ? $_GET['t'] : '';
$p = 'residencial';
if ($t) {
    require __DIR__ . '/inc/app.php';
    try { $p = (string)(val('SELECT program FROM advisors WHERE token = ?', [$t]) ?: 'residencial'); } catch (Throwable $e) { $p = 'residencial'; }
}
$ind = $p === 'industrial';
header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode([
    'name' => $ind ? 'Edalo · Construye con método' : 'Ariadna · Sigue el hilo',
    'short_name' => $ind ? 'Edalo' : 'Ariadna',
    'description' => 'Acompañamiento de arranque de carrera · R²CP Elite',
    'start_url' => $t ? 'hoy.php?t=' . $t : 'login.php',
    'scope' => './',
    'display' => 'standalone',
    'background_color' => $ind ? '#F3F4F2' : '#F7F3EA',
    'theme_color' => $ind ? '#F3F4F2' : '#F7F3EA',
    'lang' => 'es-MX',
    'icons' => [
        ['src' => $ind ? 'assets/edalo-icon-192.png' : 'assets/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
        ['src' => $ind ? 'assets/edalo-icon-512.png' : 'assets/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
        ['src' => $ind ? 'assets/edalo-icon-512.png' : 'assets/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
