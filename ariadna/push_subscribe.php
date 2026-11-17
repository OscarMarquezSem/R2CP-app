<?php
/** ARIADNA · Guarda la suscripción a notificaciones del celular del asesor. */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('{}'); }
$in = json_decode((string)file_get_contents('php://input'), true) ?: [];
$t = (string)($in['t'] ?? '');
$adv = preg_match('/^[a-f0-9]{64}$/', $t) ? one('SELECT id, active FROM advisors WHERE token = ?', [$t]) : null;
$sub = $in['subscription'] ?? null;
$endpoint = (string)($sub['endpoint'] ?? '');
$p256 = (string)($sub['keys']['p256dh'] ?? '');
$auth = (string)($sub['keys']['auth'] ?? '');
if (!$adv || !(int)$adv['active'] || !preg_match('#^https://#', $endpoint) || strlen($endpoint) > 600 || !$p256 || !$auth) {
    http_response_code(400);
    exit('{"ok":false}');
}
q('INSERT INTO push_subscriptions (advisor_id, endpoint, endpoint_hash, p256dh, auth) VALUES (?,?,?,?,?)
   ON DUPLICATE KEY UPDATE advisor_id = VALUES(advisor_id), p256dh = VALUES(p256dh), auth = VALUES(auth)',
    [$adv['id'], $endpoint, hash('sha256', $endpoint), mb_substr($p256, 0, 200), mb_substr($auth, 0, 100)]);
echo '{"ok":true}';
