<?php
/**
 * ARIADNA · Tareas programadas (para el cron de Hostinger).
 * Comando: php /home/USUARIO/domains/oscarmarquez.com/public_html/ariadna/cron/run.php
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require dirname(__DIR__) . '/inc/app.php';
require dirname(__DIR__) . '/inc/cron_tasks.php';
run_cron(true);
