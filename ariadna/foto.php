<?php
/** Muestra una foto subida por un asesor, solo a quien tiene permiso. */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';
start_session();

$id = (int)($_GET['id'] ?? 0);
$row = one('SELECT li.photo, a.id AS advisor_id, a.office_id, a.token FROM log_items li
            JOIN daily_logs l ON l.id = li.log_id JOIN advisors a ON a.id = l.advisor_id WHERE li.id = ?', [$id]);
if (!$row || !$row['photo']) { http_response_code(404); exit; }

$u = current_user();
$allowed = $u && ($u['role'] === 'admin' || (int)$u['office_id'] === (int)$row['office_id']);
$t = (string)($_GET['t'] ?? '');
if (!$allowed && $t !== '' && hash_equals($row['token'], $t)) $allowed = true;
if (!$allowed) { http_response_code(403); exit; }

$path = realpath(APP_ROOT . '/uploads/' . $row['photo']);
$base = realpath(APP_ROOT . '/uploads');
if (!$path || !$base || strpos($path, $base) !== 0 || !is_file($path)) { http_response_code(404); exit; }
header('Content-Type: image/jpeg');
header('Cache-Control: private, max-age=86400');
header('X-Content-Type-Options: nosniff');
readfile($path);
