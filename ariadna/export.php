<?php
/** ARIADNA · Exportación a CSV (administrador). Se abre bien en Excel. */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';

enforce_https();
$u = require_user(['admin']);
$type = ($_GET['type'] ?? '') === 'oficinas' ? 'oficinas' : 'asesores';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="ariadna_' . $type . '_' . date('Y-m-d') . '.csv"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel respete acentos

$offices = all('SELECT * FROM offices ORDER BY name');
if ($type === 'oficinas') {
    fputcsv($out, ['Oficina', 'Ciudad', 'Asesores activos', 'Verde', 'Amarillo', 'Rojo', '% actividades (promedio)', 'Contactos', 'Tratos directos', 'Propiedades analizadas', 'Propiedades promovidas', 'Citas', 'Captaciones', 'Evaluación promedio']);
    foreach ($offices as $o) {
        $rows = office_advisors_stats($o);
        $c = ['verde' => 0, 'amarillo' => 0, 'rojo' => 0]; $t = array_fill_keys(array_keys(RESULT_FIELDS), 0); $pct = 0; $sc = 0;
        foreach ($rows as $r) { $c[$r['stats']['status']['color']]++; foreach ($t as $f => $_) $t[$f] += $r['stats']['totals'][$f]; $pct += $r['stats']['pct']; $sc += $r['stats']['evaluation']['total']; }
        $n = count($rows);
        fputcsv($out, array_merge([$o['name'], $o['city'], $n, $c['verde'], $c['amarillo'], $c['rojo'], $n ? round($pct / $n) : 0], array_values($t), [$n ? round($sc / $n) : 0]));
    }
} else {
    fputcsv($out, ['Oficina', 'Asesor', 'Celular', 'Correo', 'Inicio', 'Semana', 'Semáforo', 'Motivo', '% actividades', 'Contactos', 'Tratos directos', 'Propiedades analizadas', 'Propiedades promovidas', 'Citas', 'Captaciones', 'Open House', 'Asistentes', 'Renta', 'Evaluación', 'Último registro', 'Decisión S3']);
    foreach ($offices as $o) {
        foreach (office_advisors_stats($o, false) as $r) {
            $a = $r['adv']; $s = $r['stats'];
            fputcsv($out, array_merge([$o['name'], $a['name'], $a['phone'], $a['email'], $a['start_date'], $s['finished'] ? 'Terminó' : $s['week'],
                $s['status']['color'], $s['status']['reason'], $s['pct']], array_values($s['totals']),
                [(int)$a['oh_done'] ? 'Sí' : 'No', (int)$a['oh_attendees'], (int)$a['renta_closed'] ? 'Sí' : 'No', $s['evaluation']['total'], $s['last_log'], $a['decision'] ?? '']));
        }
    }
}
fclose($out);
