<?php
/**
 * ARIADNA · Actualización 5 del plan: citas con el Team Leader en la oficina (archivo de UN SOLO USO).
 * 1) Súbelo junto con los demás archivos.
 * 2) Entra como administrador y abre https://ariadna.oscarmarquez.com/actualizar_plan.php
 * 3) Revisa el resultado y BORRA este archivo.
 * Solo agrega y cambia actividades del plan: no toca asesores, registros ni oficinas.
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';

enforce_https();
$u = require_user(['admin']);

$ACTS = array (
  0 => 
  array (
    'w' => 1,
    'd' => 1,
    'k' => 'tl',
    't' => 'Bienvenida: conoce tu oficina con tu Team Leader',
    'g' => 'Ve a tu oficina. Tu Team Leader te presenta la oficina y a tu equipo, y te da la introducción al negocio. Ahí mismo agendan juntos tus horarios de trabajo. Escribe aquí lo más importante que aprendiste.',
    'm' => 90,
    'after' => NULL,
  ),
  1 => 
  array (
    'w' => 1,
    'd' => 1,
    'k' => 'agenda',
    't' => 'Agenda con tu Team Leader tus horarios de trabajo',
    'g' => 'En tu bienvenida, aparta con tu Team Leader en el calendario de tu celular un bloque de tiempo para cada actividad de la semana. Abajo tienes la lista con el tiempo que pide cada una. Incluye todos los días tu bloque de prospección y tus citas con tu Team Leader de los días 2 y 5.
Escribe aquí en qué horarios quedaron tus bloques.',
    'm' => NULL,
    'after' => NULL,
  ),
  2 => 
  array (
    'w' => 1,
    'd' => 2,
    'k' => 'tl',
    't' => 'Revisa con tu Team Leader lo que aprendiste del Módulo 1',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te pregunta qué aprendiste del Módulo 1 y te ayuda a empezar tu Mi Mapa. Escribe aquí qué te preguntó y en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el Módulo 1: Matemática de tu éxito',
  ),
  3 => 
  array (
    'w' => 1,
    'd' => 5,
    'k' => 'tl',
    't' => 'Cierre de semana con tu Team Leader',
    'g' => 'Ve a tu oficina y siéntate con tu Team Leader. Revisen juntos tus actividades y tus números de la semana, tu repaso conmigo y lo que aprendiste en el Módulo 2 y en el video de conceptos básicos. Escribe aquí los acuerdos a los que llegaron.',
    'm' => 45,
    'after' => NULL,
  ),
  4 => 
  array (
    'w' => 2,
    'd' => 3,
    'k' => 'tl',
    't' => 'Revisa con tu Team Leader lo que aprendiste del Módulo 3',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te pregunta qué aprendiste del Módulo 3 y te orienta con el trabajo que pide. Escribe aquí qué te preguntó y en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el Módulo 3: Marca personal desde el día 1',
  ),
  5 => 
  array (
    'w' => 2,
    'd' => 5,
    'k' => 'tl',
    't' => 'Cierre de semana con tu Team Leader',
    'g' => 'Ve a tu oficina y siéntate con tu Team Leader. Revisen juntos tus actividades y tus números de la semana, tu repaso conmigo y si cumpliste tus mínimos de las semanas 1 y 2. Platiquen también de lo que aprendiste en el Módulo 3 y en tus sesiones de documentación. Escribe aquí los acuerdos a los que llegaron.',
    'm' => 45,
    'after' => NULL,
  ),
  6 => 
  array (
    'w' => 3,
    'd' => 1,
    'k' => 'tl',
    't' => 'Revisa con tu Team Leader lo que aprendiste del Módulo 4',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te pregunta qué aprendiste del Módulo 4 y empiezan juntos a organizar tu {OH}. Escribe aquí qué te preguntó y en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el Módulo 4: Open House',
  ),
  7 => 
  array (
    'w' => 3,
    'd' => 5,
    'k' => 'tl',
    't' => 'Punto de control con tu Team Leader',
    'g' => 'Ve a tu oficina y siéntate con tu Team Leader. Revisen juntos tus actividades y tus números de la semana, tu repaso conmigo y lo que aprendiste en los videos de opinión de valor y perfilamiento, cómo te fue en tu {OH}, tus citas y tus captaciones. Con eso deciden juntos cómo sigues en las semanas 4 y 5. Escribe aquí los acuerdos a los que llegaron.',
    'm' => 45,
    'after' => NULL,
  ),
  8 => 
  array (
    'w' => 4,
    'd' => 1,
    'k' => 'tl',
    't' => 'Revisa con tu Team Leader lo que aprendiste del Módulo 5',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te pregunta qué aprendiste del Módulo 5 y te ayuda a elegir la zona que vas a recorrer. Escribe aquí qué te preguntó y en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el Módulo 5: Tratos directos y la primera cita',
  ),
  9 => 
  array (
    'w' => 4,
    'd' => 5,
    'k' => 'tl',
    't' => 'Cierre de semana con tu Team Leader',
    'g' => 'Ve a tu oficina y siéntate con tu Team Leader. Revisen juntos tus actividades y tus números de la semana, tu repaso conmigo y lo que aprendiste en el Módulo 5, tus tratos directos y tus citas. Escribe aquí los acuerdos a los que llegaron.',
    'm' => 45,
    'after' => NULL,
  ),
  10 => 
  array (
    'w' => 5,
    'd' => 1,
    'k' => 'tl',
    't' => 'Revisa con tu Team Leader lo que aprendiste del Módulo 6',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te pregunta qué aprendiste del Módulo 6 y revisan juntos a qué prospectos les vas a dar seguimiento. Escribe aquí qué te preguntó y en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el Módulo 6: Seguimiento sin parecer desesperado',
  ),
  11 => 
  array (
    'w' => 5,
    'd' => 5,
    'k' => 'tl',
    't' => 'Cierre del curso con tu Team Leader',
    'g' => 'Ve a tu oficina y siéntate con tu Team Leader. Revisen juntos tus actividades y tus números de la semana, tu repaso conmigo y lo que aprendiste en el Módulo 7, tu evaluación final y tu plan de los próximos 30 días. Escribe aquí los acuerdos a los que llegaron.',
    'm' => 45,
    'after' => NULL,
  ),
);

$REMEMBER = array (
  1 => 'Ir a tu oficina es parte del programa: esta semana tu Team Leader te espera los días 1, 2 y 5. Cada semana, después de ver el video del curso, ve a tu oficina a platicar con tu Team Leader lo que aprendiste.
Tu negocio es de actividad: sin contactos no hay citas.
Mi Mapa te dice cuántos contactos necesitas cada día para tu meta.
Después de ver cada módulo empiezas la tarea de inmediato.',
  2 => 'Esta semana tienes 2 citas con tu Team Leader en la oficina: el repaso del video y el cierre de semana.
Tus redes y tus contenidos deben estar terminados antes de avanzar.
Solo promueves propiedades del inventario de la oficina.
Cada contacto queda anotado con su siguiente paso en tu agenda, celular o CRM.',
  3 => 'Esta semana tienes 2 citas con tu Team Leader en la oficina: el repaso del video y tu punto de control.
Contacta a posibles compradores, no a otros asesores.
Perfila al comprador antes de enseñarle propiedades.
Registra a cada asistente de tu {OH} y dale seguimiento.',
  4 => 'Esta semana tienes 2 citas con tu Team Leader en la oficina: el repaso del video y el cierre de semana.
La tarea de la semana es recorrer tu zona y buscar vendedores por trato directo.
Cada trato directo queda en tu agenda, celular o CRM con su siguiente paso.
Una captación necesita ficha completa y acuerdo de promoción firmado.',
  5 => 'Esta semana tienes 2 citas con tu Team Leader en la oficina: el repaso del video y el cierre del curso.
Nunca dejes pasar más de una semana sin contactar a un prospecto activo.
Antes de cualquier operación, valida lo legal con tu oficina.
Tu plan de los próximos 30 días lo armamos el último día.',
);

$changes = [];
db()->beginTransaction();

foreach ($ACTS as $a) {
    $label = 'S' . $a['w'] . ' D' . $a['d'] . ' · ' . $a['t'];
    if ($a['k'] === 'agenda') {
        $row = one("SELECT * FROM plan_activities WHERE active = 1 AND week_no = 1 AND day_no = 1 AND kind = 'agenda' ORDER BY sort LIMIT 1");
        if ($row && ($row['title'] !== $a['t'] || $row['guide'] !== $a['g'])) {
            q('UPDATE plan_activities SET title = ?, guide = ? WHERE id = ?', [$a['t'], $a['g'], $row['id']]);
            $changes[] = $label . ' (texto nuevo)';
        }
        continue;
    }
    if (one('SELECT id FROM plan_activities WHERE active = 1 AND week_no = ? AND day_no = ? AND title = ?', [$a['w'], $a['d'], $a['t']])) continue;
    if ($a['w'] === 1 && $a['d'] === 1) {
        // La bienvenida de la Semana 1 deja de ser un tema que programa la oficina: ahora es la cita con el TL.
        $old = one("SELECT id FROM plan_activities WHERE active = 1 AND week_no = 1 AND day_no = 1 AND title = 'Bienvenida e introducción al negocio'");
        if ($old) {
            q("UPDATE plan_activities SET title = ?, guide = ?, minutes = ?, kind = 'tl' WHERE id = ?", [$a['t'], $a['g'], $a['m'], $old['id']]);
            $changes[] = $label . ' (antes «Bienvenida e introducción al negocio»)';
            continue;
        }
    }
    // El repaso con el TL va justo después de su video; si no, al final del día.
    $vsort = $a['after'] ? val('SELECT sort FROM plan_activities WHERE active = 1 AND week_no = ? AND day_no = ? AND title = ?', [$a['w'], $a['d'], $a['after']]) : null;
    if ($vsort !== null) {
        $sort = (int)$vsort + 1;
        q('UPDATE plan_activities SET sort = sort + 2 WHERE week_no = ? AND day_no = ? AND sort > ?', [$a['w'], $a['d'], (int)$vsort]);
    } else {
        $sort = (int)val('SELECT COALESCE(MAX(sort), 0) FROM plan_activities WHERE week_no = ? AND day_no = ?', [$a['w'], $a['d']]) + 10;
    }
    q('INSERT INTO plan_activities (week_no, day_no, sort, title, guide, minutes, kind) VALUES (?,?,?,?,?,?,?)',
        [$a['w'], $a['d'], $sort, $a['t'], $a['g'], $a['m'], $a['k']]);
    $changes[] = $label . ' (nueva)';
}

// «Recuerda» de cada semana: se agrega al principio la línea de las citas con el TL.
foreach ($REMEMBER as $w => $txt) {
    $first = strtok($txt, "\n");
    $cur = (string)val('SELECT remember FROM plan_weeks WHERE week_no = ?', [$w]);
    if (strpos($cur, $first) !== false) continue;
    q('UPDATE plan_weeks SET remember = ? WHERE week_no = ?', [trim($first . "\n" . $cur), $w]);
    $changes[] = 'Semana ' . $w . ' · «Recuerda»: ' . $first;
}
// Las citas con el TL cuentan para el semáforo a partir de hoy (no se marcan faltas de días anteriores).
if (val("SELECT v FROM settings WHERE k = 'tl_since'") === null) {
    set_setting('tl_since', date('Y-m-d'));
    $changes[] = 'Las citas con el Team Leader cuentan a partir de hoy';
}
db()->commit();

page_start('Actualización del plan', ['nav' => $u, 'active' => 'plan']);
echo '<h1>Actualización del plan</h1><div class="card">';
if ($changes) {
    echo '<p><b>Listo. Se aplicaron ' . count($changes) . ' cambios:</b></p><ul>';
    foreach ($changes as $c) echo '<li>' . h($c) . '</li>';
    echo '</ul>';
} else {
    echo '<p><b>Todo ya estaba actualizado.</b> No hubo nada que cambiar.</p>';
}
echo '<div class="box warn"><b>Importante:</b> ahora borra el archivo <b>actualizar_plan.php</b> de tu hosting.</div>';
echo '<p><a class="btn" href="admin_preview.php">Revisarlo en «Ver como asesor»</a></p></div>';
page_end();
