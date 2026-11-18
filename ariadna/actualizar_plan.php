<?php
/**
 * ARIADNA · Actualización del plan: citas con el Team Leader y Jornada de opinión de valor (archivo de UN SOLO USO).
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
    'g' => 'En tu bienvenida, aparta con tu Team Leader en el calendario de tu celular un bloque de tiempo para cada actividad de la semana. Abajo tienes la lista con el tiempo que pide cada una. Incluye todos los días tu bloque de prospección y tus citas con tu Team Leader de los días 2, 4 y 5.
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
    'd' => 4,
    'k' => 'tl',
    't' => 'Platica con tu Team Leader los conceptos básicos del video',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te pregunta los conceptos básicos para saber si quedaron claros y te ayuda a preparar tu examen de terminología. Escribe aquí qué te preguntó y en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el video: conceptos básicos de bienes raíces',
  ),
  4 => 
  array (
    'w' => 1,
    'd' => 5,
    'k' => 'tl',
    't' => 'Cierre de semana con tu Team Leader',
    'g' => 'Ve a tu oficina y siéntate con tu Team Leader. Revisen juntos tus actividades y tus números de la semana, tu repaso conmigo y lo que aprendiste en el Módulo 2. Escribe aquí los acuerdos a los que llegaron.',
    'm' => 45,
    'after' => NULL,
  ),
  5 => 
  array (
    'w' => 2,
    'd' => 3,
    'k' => 'tl',
    't' => 'Revisa con tu Team Leader lo que aprendiste del Módulo 3',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te pregunta qué aprendiste del Módulo 3 y te orienta con el trabajo que pide. Escribe aquí qué te preguntó y en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el Módulo 3: Marca personal desde el día 1',
  ),
  6 => 
  array (
    'w' => 2,
    'd' => 5,
    'k' => 'tl',
    't' => 'Cierre de semana con tu Team Leader',
    'g' => 'Ve a tu oficina y siéntate con tu Team Leader. Revisen juntos tus actividades y tus números de la semana, tu repaso conmigo y si cumpliste tus mínimos de las semanas 1 y 2. Platiquen también de lo que aprendiste en el Módulo 3 y en tus sesiones de documentación. Escribe aquí los acuerdos a los que llegaron.',
    'm' => 45,
    'after' => NULL,
  ),
  7 => 
  array (
    'w' => 3,
    'd' => 1,
    'k' => 'tl',
    't' => 'Revisa con tu Team Leader lo que aprendiste del Módulo 4',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te pregunta qué aprendiste del Módulo 4 y empiezan juntos a organizar tu {OH}. Escribe aquí qué te preguntó y en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el Módulo 4: Open House',
  ),
  8 => 
  array (
    'w' => 3,
    'd' => 2,
    'k' => 'tl',
    't' => 'Platica con tu Team Leader lo que aprendiste del video de opinión de valor',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te pide que le expliques cómo comparas una propiedad con otras parecidas de la zona, con una de las propiedades que vas a analizar. Escribe aquí qué te preguntó y en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el video: opinión de valor',
  ),
  9 => 
  array (
    'w' => 3,
    'd' => 3,
    'k' => 'tl',
    't' => 'Platica con tu Team Leader lo que aprendiste del video de perfilamiento',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader hace contigo un ejercicio: hace el papel de comprador y tú le haces las preguntas para perfilarlo. Escribe aquí qué te preguntó y en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el video: perfilamiento de compradores',
  ),
  10 => 
  array (
    'w' => 3,
    'd' => 5,
    'k' => 'tl',
    't' => 'Punto de control con tu Team Leader',
    'g' => 'Ve a tu oficina y siéntate con tu Team Leader. Revisen juntos tus actividades y tus números de la semana, tu repaso conmigo y cómo te fue en tu {OH}, tus citas y tus captaciones. Con eso deciden juntos cómo sigues en las semanas 4 y 5. Escribe aquí los acuerdos a los que llegaron.',
    'm' => 45,
    'after' => NULL,
  ),
  11 => 
  array (
    'w' => 4,
    'd' => 1,
    'k' => 'tl',
    't' => 'Revisa con tu Team Leader lo que aprendiste del Módulo 5',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te pregunta qué aprendiste del Módulo 5 y te ayuda a elegir la zona que vas a recorrer. Escribe aquí qué te preguntó y en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el Módulo 5: Tratos directos y la primera cita',
  ),
  12 => 
  array (
    'w' => 4,
    'd' => 5,
    'k' => 'tl',
    't' => 'Cierre de semana con tu Team Leader',
    'g' => 'Ve a tu oficina y siéntate con tu Team Leader. Revisen juntos tus actividades y tus números de la semana, tu repaso conmigo y lo que aprendiste en el Módulo 5, tus tratos directos y tus citas. Escribe aquí los acuerdos a los que llegaron.',
    'm' => 45,
    'after' => NULL,
  ),
  13 => 
  array (
    'w' => 5,
    'd' => 1,
    'k' => 'tl',
    't' => 'Revisa con tu Team Leader lo que aprendiste del Módulo 6',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te pregunta qué aprendiste del Módulo 6 y revisan juntos a qué prospectos les vas a dar seguimiento. Escribe aquí qué te preguntó y en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el Módulo 6: Seguimiento sin parecer desesperado',
  ),
  14 => 
  array (
    'w' => 5,
    'd' => 4,
    'k' => 'tl',
    't' => 'Revisa con tu Team Leader lo que aprendiste del Módulo 7',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te pregunta qué aprendiste del Módulo 7 y practican juntos cómo responder las objeciones que más te han hecho. Escribe aquí qué te preguntó y en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el Módulo 7: Manejo de objeciones básicas',
  ),
  15 => 
  array (
    'w' => 5,
    'd' => 5,
    'k' => 'tl',
    't' => 'Cierre del curso con tu Team Leader',
    'g' => 'Ve a tu oficina y siéntate con tu Team Leader. Revisen juntos tus actividades y tus números de la semana, tu repaso conmigo y tu evaluación final y tu plan de los próximos 30 días. Escribe aquí los acuerdos a los que llegaron.',
    'm' => 45,
    'after' => NULL,
  ),
);

$REMEMBER = array (
  1 => 'Ir a tu oficina es parte del programa: esta semana tu Team Leader te espera los días 1, 2, 4 y 5. Cada vez que veas un video del curso, ve a tu oficina a platicar con tu Team Leader lo que aprendiste.',
  2 => 'Esta semana tienes 2 citas con tu Team Leader en la oficina: el repaso del video y el cierre de semana.',
  3 => 'Esta semana tienes 4 citas con tu Team Leader en la oficina: una después de cada video (días 1, 2 y 3) y tu punto de control.',
  4 => 'Esta semana tienes 2 citas con tu Team Leader en la oficina: el repaso del video y el cierre de semana.',
  5 => 'Esta semana tienes 3 citas con tu Team Leader en la oficina: una después de cada video (Módulos 6 y 7) y el cierre del curso.',
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
// Líneas de borradores anteriores de esta actualización (por si ya se había subido la Actualización 5).
$OLDREM = [
    'Ir a tu oficina es parte del programa: esta semana tu Team Leader te espera los días 1, 3 y 5.',
    'Ir a tu oficina es parte del programa: esta semana tu Team Leader te espera los días 1, 2 y 5. Cada semana, después de ver el video del curso, ve a tu oficina a platicar con tu Team Leader lo que aprendiste.',
    'Esta semana tienes 2 citas con tu Team Leader en la oficina: el repaso de los videos y tu punto de control.',
    'Esta semana tienes 2 citas con tu Team Leader en la oficina: el repaso del video y tu punto de control.',
    'Esta semana tienes 2 citas con tu Team Leader en la oficina: el repaso del video y el cierre del curso.',
    'Esta semana tienes 2 citas con tu Team Leader en la oficina: el repaso de cada video (Módulos 6 y 7) y el cierre del curso.',
];
foreach ($REMEMBER as $w => $txt) {
    $first = strtok($txt, "\n");
    $cur = (string)val('SELECT remember FROM plan_weeks WHERE week_no = ?', [$w]);
    if (strpos($cur, $first) !== false) continue;
    $new = null;
    foreach ($OLDREM as $o) if (strpos($cur, $o) !== false) { $new = str_replace($o, $first, $cur); break; }
    q('UPDATE plan_weeks SET remember = ? WHERE week_no = ?', [$new ?? trim($first . "\n" . $cur), $w]);
    $changes[] = 'Semana ' . $w . ' · «Recuerda»: ' . $first;
}
$OHCH = array (
  'acts' => 
  array (
    0 => 
    array (
      'w' => 3,
      'd' => 1,
      'ot' => 'Organiza y promueve tu {OH} con la supervisión del Team Leader o gerente de tu oficina',
      'nt' => 'Organiza{evento} y promueve{/evento} tu {OH} con la supervisión del Team Leader o gerente de tu oficina',
      'og' => 'Cuando lo realices, regístralo en «Mis logros» con el número de asistentes reales (mínimo 5).',
      'ng' => '{jornada}Elige con tu Team Leader una propiedad de la oficina: vas a recorrer las dos cuadras a la redonda. Prepara un volante con tu foto, tus datos y la oferta: «Te doy una opinión de valor de tu casa, sin costo». {/jornada}Cuando lo realices, regístralo en «Mis logros» con el número de {ASISTENTES} (mínimo 5).',
    ),
    1 => 
    array (
      'w' => 3,
      'd' => 4,
      'ot' => 'Invita y prospecta para tu {OH}',
      'nt' => '{evento}Invita y prospecta para tu {OH}{/evento}{jornada}Haz tu {OH}: recorre las dos cuadras{/jornada}',
      'og' => 'Invita a las personas con las que has hablado. También puedes crear una invitación y llevársela a los vecinos de dos cuadras a la redonda: muchos de ellos quizá estén pensando en vender y quieren saber cómo se compara su casa. Anota a cada asistente con su celular, qué le interesó y el siguiente paso.',
      'ng' => '{evento}Invita a las personas con las que has hablado. También puedes crear una invitación y llevársela a los vecinos de dos cuadras a la redonda: muchos de ellos quizá estén pensando en vender y quieren saber cómo se compara su casa. Anota a cada asistente con su celular, qué le interesó y el siguiente paso.{/evento}{jornada}Toca puertas en las dos cuadras a la redonda de la propiedad que elegiste. Preséntate, entrega tu volante y ofrece una opinión de valor de su casa sin costo: muchos vecinos quizá estén pensando en vender y quieren saber cuánto vale su casa. Anota a cada propietario interesado con su nombre, celular, dirección y cuándo le entregas su opinión. Necesitas mínimo 5. Prepara cada opinión con lo que aprendiste en el video de opinión de valor y entrégala en persona: es tu oportunidad de captar.{/jornada}',
    ),
  ),
  'weeks' => 
  array (
    0 => 
    array (
      'w' => 3,
      'f' => 'remember',
      'o' => 'Registra a cada asistente de tu {OH} y dale seguimiento.',
      'n' => 'Da seguimiento a cada uno de los {ASISTENTES} de tu {OH}.',
    ),
    1 => 
    array (
      'w' => 3,
      'f' => 'review_questions',
      'o' => '¿Cuántas personas asistieron a tu {OH} y qué hiciste con ellas?',
      'n' => '¿Cuántos {ASISTENTES} tuviste en tu {OH} y qué hiciste con cada uno?',
    ),
  ),
);

// Jornada de opinión de valor: textos del plan que cambian según lo que haga la oficina.
foreach ($OHCH['acts'] as $c) {
    foreach (all('SELECT id, title, guide FROM plan_activities WHERE active = 1 AND week_no = ? AND day_no = ? AND title IN (?, ?)', [$c['w'], $c['d'], $c['ot'], $c['nt']]) as $r) {
        $t = $r['title'] === $c['ot'] ? $c['nt'] : $r['title'];
        $g = $r['guide'] === $c['og'] ? $c['ng'] : $r['guide'];
        if ($t === $r['title'] && $g === $r['guide']) continue;
        q('UPDATE plan_activities SET title = ?, guide = ? WHERE id = ?', [$t, $g, $r['id']]);
        $changes[] = 'S' . $c['w'] . ' D' . $c['d'] . ' · texto para la Jornada de opinión de valor';
    }
}
foreach ($OHCH['weeks'] as $c) {
    $f = $c['f'] === 'remember' ? 'remember' : 'review_questions';
    $cur = (string)val("SELECT $f FROM plan_weeks WHERE week_no = ?", [$c['w']]);
    if (strpos($cur, $c['o']) === false) continue;
    q("UPDATE plan_weeks SET $f = ? WHERE week_no = ?", [str_replace($c['o'], $c['n'], $cur), $c['w']]);
    $changes[] = 'Semana ' . $c['w'] . ' · ' . ($f === 'remember' ? '«Recuerda»' : 'preguntas del repaso') . ' para la Jornada de opinión de valor';
}

$ALIGN = array (
  'upd' => 
  array (
    0 => 
    array (
      'w' => 1,
      'd' => 5,
      'ot' => 'Abre y adapta con tu marca tus páginas de Facebook e Instagram (TikTok es opcional)',
      'nt' => 'Abre y adapta con tu marca tus páginas de Facebook e Instagram, y descarga WhatsApp Business (TikTok es opcional)',
      'ng' => 'Tus redes son tu oficina pública. Pasa tu WhatsApp a WhatsApp Business: respalda tu historial y lo migras sin perder nada. Escribe aquí los enlaces de tus páginas para que tu oficina los vea.',
      'k' => 'check',
      'm' => NULL,
    ),
    1 => 
    array (
      'w' => 1,
      'd' => 5,
      'ot' => 'Crea mínimo 3 contenidos',
      'nt' => 'Publica tus 3 propiedades en todas tus redes y en mínimo 5 grupos, con la fórmula del post perfecto',
      'ng' => 'Publícalas en Facebook (tu perfil, tu página de negocios y grupos), Instagram, TikTok y tu estatus de WhatsApp Business. Usa las 4 partes: gancho, descripción que haga soñar, precio y llamado a la acción («Comenta me interesa»). Busca de 20 a 25 grupos de tu zona y publica en mínimo 5. Contesta de inmediato a quien te escriba. Súmalas en «Propiedades promovidas» y escribe aquí los enlaces.',
      'k' => 'check',
      'm' => NULL,
    ),
    2 => 
    array (
      'w' => 2,
      'd' => 3,
      'ot' => 'Haz el trabajo que indica el video y súbelo a Ariadna',
      'nt' => 'Pon tu foto profesional y tu biografía en todas tus redes',
      'ng' => 'La misma foto en Facebook, Instagram, TikTok y WhatsApp: ropa de negocios, buena luz, sonrisa y fondo limpio. Tu biografía responde 3 preguntas: quién eres, a quién ayudas y cómo te contactan (tu WhatsApp). Sube una captura y escribe aquí los enlaces de tus perfiles.',
      'k' => 'foto',
      'm' => NULL,
    ),
    3 => 
    array (
      'w' => 2,
      'd' => 5,
      'ot' => 'Publica 5 propiedades de la oficina en 20 grupos de Facebook y en tus redes',
      'nt' => 'Publica 3 propiedades y 1 contenido de valor en todas tus redes y en mínimo 5 grupos',
      'ng' => 'Es tu ritmo de cada semana (regla 3 + 1): 3 propiedades de la oficina, con al menos 1 renta, y 1 contenido de valor, con la fórmula del post perfecto. Súmalas en «Propiedades promovidas».',
      'k' => 'check',
      'm' => NULL,
    ),
    4 => 
    array (
      'w' => 3,
      'd' => 4,
      'ot' => 'Promueve 3 propiedades en tus redes y en grupos',
      'nt' => 'Promueve 3 propiedades en todas tus redes y en mínimo 5 grupos',
      'ng' => 'Con la fórmula del post perfecto: gancho, descripción, precio y llamado a la acción. Súmalas en «Propiedades promovidas».',
      'k' => 'check',
      'm' => 120,
    ),
    5 => 
    array (
      'w' => 3,
      'd' => 5,
      'ot' => 'Publica 3 de las 5 propiedades en tus redes y en 10 grupos; contacta compradores, no a otros asesores',
      'nt' => 'Publica tu contenido de valor de la semana y contesta de inmediato a los interesados',
      'ng' => 'Regla 3 + 1: con tus 3 propiedades de la semana va 1 contenido de valor. Contesta a posibles compradores, no a otros asesores. Escribe aquí qué publicaste.',
      'k' => 'check',
      'm' => NULL,
    ),
    6 => 
    array (
      'w' => 4,
      'd' => 1,
      'ot' => 'Elige una zona y recórrela buscando propietarios que vendan por trato directo',
      'nt' => 'Identifica 20 propiedades de trato directo',
      'ng' => 'Búscalas en letreros y lonas de tu zona, en portales (usa el filtro «trato directo»), en Facebook Marketplace, en grupos de compraventa y en tu propio vecindario. Algunos serán asesores que se anuncian como dueños: anótalos de todas formas. Escribe aquí en qué zona buscaste.',
      'k' => 'check',
      'm' => NULL,
    ),
    7 => 
    array (
      'w' => 4,
      'd' => 2,
      'ot' => 'Continúa con tus tratos directos',
      'nt' => 'Llama a tus tratos directos con el diálogo de tu Mi Mapa',
      'ng' => 'Sigue los 5 pasos en orden: confirma que es el dueño, preséntate, «tengo una pregunta», «¿te interesaría mostrar tu propiedad a un cliente de mi inmobiliaria?» y propón tú el día y la hora. Si te pone condiciones: «No hay problema, nos vemos hoy a las 5». Anota qué te dijo cada uno, súmalos en «Tratos directos» y avísale a tu Team Leader de cada cita.',
      'k' => 'check',
      'm' => NULL,
    ),
    8 => 
    array (
      'w' => 5,
      'd' => 1,
      'ot' => 'Da seguimiento a tus tratos directos y prospectos',
      'nt' => 'Organiza todos tus contactos en una sola lista y clasifícalos',
      'ng' => 'Junta a todos en un solo lugar (WhatsApp Business con etiquetas, una hoja de Google o el CRM de tu oficina): tu Impulsor de Memoria, tus redes, tu {OH} y tus tratos directos. De cada uno: nombre, teléfono, de dónde lo conociste, qué busca, último contacto y fechas especiales. Clasifícalos como caliente, tibio o frío.',
      'k' => 'check',
      'm' => NULL,
    ),
  ),
  'ins' => 
  array (
    0 => 
    array (
      'w' => 1,
      'd' => 5,
      'after' => 'Abre y adapta con tu marca tus páginas de Facebook e Instagram, y descarga WhatsApp Business (TikTok es opcional)',
      'k' => 'check',
      't' => 'Pide a tu oficina 3 propiedades para promover: 2 en venta y 1 en renta',
      'g' => 'Solo promueves propiedades del inventario de la oficina. La renta es tu anzuelo: quien renta decide más rápido y te da práctica real con clientes. Escribe aquí cuáles te asignaron.',
      'm' => NULL,
      'h' => '',
    ),
    1 => 
    array (
      'w' => 1,
      'd' => 5,
      'after' => 'Publica tus 3 propiedades en todas tus redes y en mínimo 5 grupos, con la fórmula del post perfecto',
      'k' => 'check',
      't' => 'Publica 1 contenido de valor (regla 3 + 1)',
      'g' => 'Por cada 3 propiedades, 1 contenido que le sirva a tu cliente. Por ejemplo: qué impuestos se pagan al comprar una casa o los 5 riesgos más grandes al rentar. Escribe aquí cuál publicaste.',
      'm' => NULL,
      'h' => 'Cómo crear contenido | https://youtu.be/11XYCHJFeJU',
    ),
    2 => 
    array (
      'w' => 5,
      'd' => 1,
      'after' => 'Organiza todos tus contactos en una sola lista y clasifícalos',
      'k' => 'check',
      't' => 'Manda un mensaje con pretexto de valor a 10 contactos',
      'g' => 'Nada de «¿ya se decidieron?». Comparte algo útil: una propiedad nueva que le queda, cómo se movieron los precios en su zona o un dato de impuestos. Corto, con su nombre y que termine con una pregunta. Manda las capturas a tu Team Leader.',
      'm' => NULL,
      'h' => '',
    ),
    3 => 
    array (
      'w' => 5,
      'd' => 1,
      'after' => 'Manda un mensaje con pretexto de valor a 10 contactos',
      'k' => 'check',
      't' => 'Agenda 3 citas con propietarios esta semana',
      'g' => 'Desde esta semana, tu meta son 3 citas con propietarios cada semana. Súmalas en «Citas agendadas».',
      'm' => NULL,
      'h' => '',
    ),
    4 => 
    array (
      'w' => 5,
      'd' => 4,
      'after' => 'Sigue dando seguimiento a tus prospectos',
      'k' => 'check',
      't' => 'Practica las 5 objeciones en roleplay con un compañero',
      'g' => 'Uno hace de cliente y el otro de asesor; luego cambian. Practiquen «lo voy a pensar», «está muy caro», «ya tengo asesor», «no es el momento» y «lo tengo que platicar con mi pareja». Grábense si pueden. Escribe aquí con quién practicaste, qué objeciones y qué aprendiste.',
      'm' => NULL,
      'h' => '',
    ),
  ),
  'metas' => 
  array (
    1 => 
    array (
      'promovidas' => 3,
    ),
    2 => 
    array (
      'promovidas' => 3,
    ),
    4 => 
    array (
      'tratos' => 20,
    ),
    5 => 
    array (
      'citas' => 3,
    ),
  ),
  'remember' => 
  array (
    0 => 
    array (
      0 => 1,
      1 => 'En el Módulo 1 te pido mínimo 5 contactos al día; en tu arranque conmigo haces 20 llamadas diarias durante 5 días hábiles para agarrar ritmo.',
    ),
  ),
);

// Que el plan diga lo mismo que los videos del curso.
foreach ($ALIGN['upd'] as $c) {
    foreach (all('SELECT id, title, guide FROM plan_activities WHERE active = 1 AND week_no = ? AND day_no = ? AND title IN (?, ?)', [$c['w'], $c['d'], $c['ot'], $c['nt']]) as $r) {
        if ($r['title'] === $c['nt'] && $r['guide'] === $c['ng']) continue;
        q('UPDATE plan_activities SET title = ?, guide = ?, kind = ?, minutes = ? WHERE id = ?', [$c['nt'], $c['ng'], $c['k'], $c['m'], $r['id']]);
        $changes[] = 'S' . $c['w'] . ' D' . $c['d'] . ' · ' . $c['nt'] . ' (igual que el video)';
    }
}
foreach ($ALIGN['ins'] as $c) {
    if (one('SELECT id FROM plan_activities WHERE active = 1 AND week_no = ? AND day_no = ? AND title = ?', [$c['w'], $c['d'], $c['t']])) continue;
    $vsort = val('SELECT sort FROM plan_activities WHERE active = 1 AND week_no = ? AND day_no = ? AND title = ?', [$c['w'], $c['d'], $c['after']]);
    if ($vsort !== null) {
        $sort = (int)$vsort + 1;
        q('UPDATE plan_activities SET sort = sort + 2 WHERE week_no = ? AND day_no = ? AND sort > ?', [$c['w'], $c['d'], (int)$vsort]);
    } else {
        $sort = (int)val('SELECT COALESCE(MAX(sort), 0) FROM plan_activities WHERE week_no = ? AND day_no = ?', [$c['w'], $c['d']]) + 10;
    }
    q('INSERT INTO plan_activities (week_no, day_no, sort, title, guide, minutes, kind, help_links) VALUES (?,?,?,?,?,?,?,?)',
        [$c['w'], $c['d'], $sort, $c['t'], $c['g'], $c['m'], $c['k'], $c['h'] !== '' ? $c['h'] : null]);
    $changes[] = 'S' . $c['w'] . ' D' . $c['d'] . ' · ' . $c['t'] . ' (nueva, igual que el video)';
}
foreach ($ALIGN['metas'] as $w => $m) {
    foreach ($m as $f => $v) {
        if ((int)val("SELECT meta_$f FROM plan_weeks WHERE week_no = ?", [$w]) === (int)$v) continue;
        q("UPDATE plan_weeks SET meta_$f = ? WHERE week_no = ?", [(int)$v, $w]);
        $changes[] = 'Semana ' . $w . ' · mínimo de ' . RESULT_FIELDS[$f] . ': ' . $v;
    }
}
foreach ($ALIGN['remember'] as [$w, $line]) {
    $cur = (string)val('SELECT remember FROM plan_weeks WHERE week_no = ?', [$w]);
    if (strpos($cur, $line) !== false) continue;
    q('UPDATE plan_weeks SET remember = ? WHERE week_no = ?', [trim($line . "\n" . $cur), $w]);
    $changes[] = 'Semana ' . $w . ' · «Recuerda»: ' . $line;
}
if ((int)setting('s2_min_promovidas') === 5) {
    set_setting('s2_min_promovidas', '6');
    $changes[] = 'Semáforo · mínimo de propiedades promovidas al cerrar la Semana 2: 6 (3 por semana)';
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
