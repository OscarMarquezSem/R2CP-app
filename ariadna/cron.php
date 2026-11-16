<?php
/**
 * ARIADNA · Tareas programadas por URL (alternativa al comando PHP).
 * https://ariadna.oscarmarquez.com/cron.php?key=TU_CRON_KEY
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';
require __DIR__ . '/inc/cron_tasks.php';
$key = (string)($_GET['key'] ?? '');
$real = (string)cfg('cron_key', '');
if ($real === '' || str_starts_with($real, 'CAMBIA') || !hash_equals($real, $key)) { http_response_code(403); exit('Acceso denegado'); }
header('Content-Type: text/plain; charset=utf-8');
$log = run_cron(false);
echo "OK " . date('Y-m-d H:i:s') . "\n" . implode("\n", $log);
