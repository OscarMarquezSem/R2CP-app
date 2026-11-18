<?php
/**
 * ARIADNA · Actualización de textos del plan (archivo de UN SOLO USO).
 * 1) Súbelo junto con los demás archivos.
 * 2) Entra como administrador y abre https://ariadna.oscarmarquez.com/actualizar_plan.php
 * 3) Revisa el resultado y BORRA este archivo.
 * Solo cambia textos del plan: no toca asesores, registros ni oficinas.
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';

enforce_https();
$u = require_user(['admin']);

$ROWS = array (
  0 => 
  array (
    'w' => 1,
    'd' => 1,
    't' => 'Agenda en tu calendario los horarios de esta semana',
    'g' => 'Antes de empezar tu semana, aparta en el calendario de tu celular un bloque de tiempo para cada actividad. Abajo tienes la lista de la semana con el tiempo que pide cada una y la fecha de tus sesiones en la oficina. Incluye todos los días tu bloque de prospección.
Escribe aquí en qué horarios quedaron tus bloques.',
    'force' => false,
  ),
  1 => 
  array (
    'w' => 1,
    'd' => 1,
    't' => 'Bienvenida e introducción al negocio',
    'g' => 'Sesión con el Team Leader o el gerente de tu oficina. Escribe aquí lo más importante que aprendiste.',
    'force' => false,
  ),
  2 => 
  array (
    'w' => 1,
    'd' => 1,
    't' => 'Aparta 2 horas en tu agenda para el Módulo 1',
    'g' => 'Mañana ves el Módulo 1. Escribe aquí a qué hora lo vas a ver.',
    'force' => false,
  ),
  3 => 
  array (
    'w' => 1,
    'd' => 2,
    't' => 'Ve el Módulo 1: Matemática de tu éxito',
    'g' => 'Al terminar, empieza de inmediato la tarea: llenar Mi Mapa.',
    'force' => false,
  ),
  4 => 
  array (
    'w' => 1,
    'd' => 2,
    't' => 'Llena Mi Mapa (tu meta real de ingresos)',
    'g' => 'Mi Mapa se llena a mano. Ahí defines tu meta real de ingresos y te dice cuántos contactos necesitas cada día. Escribe aquí cuántos contactos diarios te marca (renglón H) y sube la foto. En tu Mi Mapa también vienen los 3 diálogos para tus llamadas: léelos antes de tu sesión de llamadas de mañana.',
    'force' => true,
  ),
  5 => 
  array (
    'w' => 1,
    'd' => 2,
    't' => 'Llena a mano tu Impulsor de Memoria lo más que puedas',
    'g' => 'Pídele la hoja a tu oficina. Llénala a mano y apóyate en los contactos de tu celular para recordar a más personas. De aquí salen tus 20 llamadas diarias. Escribe cuántos nombres anotaste; si quieres, sube una foto.',
    'force' => false,
  ),
  6 => 
  array (
    'w' => 1,
    'd' => 2,
    't' => 'Confirma con tu oficina tu sesión de llamadas de mañana',
    'g' => 'Mañana haces tus primeras llamadas acompañado. Escribe aquí la hora.',
    'force' => false,
  ),
  7 => 
  array (
    'w' => 1,
    'd' => 3,
    't' => 'Llamadas a tu Impulsor de Memoria (acompañado)',
    'g' => 'Haces tus primeras llamadas acompañado por el Team Leader o el gerente de tu oficina. Ten a la mano los 3 diálogos de tu Mi Mapa. Anota cada respuesta y su siguiente paso en tu agenda, celular o CRM.',
    'force' => true,
  ),
  8 => 
  array (
    'w' => 1,
    'd' => 4,
    't' => 'Ve el video: conceptos básicos de bienes raíces',
    'g' => 'Repasa los conceptos básicos del negocio.',
    'force' => false,
  ),
  9 => 
  array (
    'w' => 1,
    'd' => 4,
    't' => 'Prepárate para el examen de terminología',
    'g' => 'Repasa los conceptos básicos del video.',
    'force' => false,
  ),
  10 => 
  array (
    'w' => 1,
    'd' => 5,
    't' => 'Ve el Módulo 2: Tu vitrina digital',
    'g' => 'Al terminar, empieza de inmediato a trabajar en tus páginas.',
    'force' => false,
  ),
  11 => 
  array (
    'w' => 1,
    'd' => 5,
    't' => 'Abre y adapta con tu marca tus páginas de Facebook e Instagram (TikTok es opcional)',
    'g' => 'Escribe aquí los enlaces de tus páginas para que tu oficina los vea.',
    'force' => false,
  ),
  12 => 
  array (
    'w' => 1,
    'd' => 5,
    't' => 'Crea mínimo 3 contenidos',
    'g' => 'Publica al menos 3 contenidos en tus páginas. Escribe aquí cuáles fueron.',
    'force' => false,
  ),
  13 => 
  array (
    'w' => 1,
    'd' => 5,
    't' => 'Sube la foto de tus páginas llenas de Mi Mapa',
    'g' => 'Toma foto de tus páginas llenas de Mi Mapa y súbela aquí. Tu oficina la ve en Ariadna. Tus registros detallados (contactos, tratos, propiedades) guárdalos en tu agenda, la lista de tu celular o el CRM de tu oficina.',
    'force' => false,
  ),
  14 => 
  array (
    'w' => 1,
    'd' => 5,
    't' => 'Revisión semanal conmigo: responde tu repaso',
    'g' => 'Responde con tus palabras. Tu oficina lo ve en Ariadna.',
    'force' => false,
  ),
  15 => 
  array (
    'w' => 2,
    'd' => 1,
    't' => 'Agenda en tu calendario los horarios de esta semana',
    'g' => 'Antes de empezar tu semana, aparta en el calendario de tu celular un bloque de tiempo para cada actividad. Abajo tienes la lista de la semana con el tiempo que pide cada una y la fecha de tus sesiones en la oficina. Incluye todos los días tu bloque de prospección.
Escribe aquí en qué horarios quedaron tus bloques.',
    'force' => false,
  ),
  16 => 
  array (
    'w' => 2,
    'd' => 1,
    't' => 'Pon tus metas de la semana',
    'g' => 'Usa tu Mi Mapa: te dice cuántos contactos necesitas cada día para llegar a tu meta de ingresos. Tus metas aparecen en tu pantalla de cada día. El semáforo y la evaluación se miden con los mínimos del programa.',
    'force' => false,
  ),
  17 => 
  array (
    'w' => 2,
    'd' => 1,
    't' => 'Revisa que tus páginas en redes y tus contenidos estén terminados',
    'g' => 'Si no están terminados, no avanzas a la siguiente tarea. Escribe aquí los enlaces.',
    'force' => false,
  ),
  18 => 
  array (
    'w' => 2,
    'd' => 2,
    't' => 'Documentación de personas físicas y morales',
    'g' => 'Lleva libreta y pregunta todo. Escribe aquí qué documentos no conocías.',
    'force' => false,
  ),
  19 => 
  array (
    'w' => 2,
    'd' => 3,
    't' => 'Ve el Módulo 3: Marca personal desde el día 1',
    'g' => 'Al terminar, haz de inmediato el trabajo que pide el video; lo subes en la siguiente actividad.',
    'force' => false,
  ),
  20 => 
  array (
    'w' => 2,
    'd' => 3,
    't' => 'Haz el trabajo que indica el video y súbelo a Ariadna',
    'g' => 'Sube una foto o escribe el enlace de tu trabajo antes de terminar el día.',
    'force' => false,
  ),
  21 => 
  array (
    'w' => 2,
    'd' => 4,
    't' => 'Documentación de la propiedad e impuestos',
    'g' => 'Escribe aquí lo más importante que aprendiste.',
    'force' => false,
  ),
  22 => 
  array (
    'w' => 2,
    'd' => 4,
    't' => 'Analiza 3 propiedades de la oficina y cuéntame qué opinas de cada una',
    'g' => 'Entra a las propiedades de la oficina y elige 3 que te llamen la atención. Revisa su ficha y, si puedes, ve a verlas en persona. Analiza el precio, la condición y la zona de cada una. Súmalas en «Propiedades analizadas» y escríbeme qué opinas de cada una: qué propiedad es, qué opinas de su valor y por qué.',
    'force' => true,
  ),
  23 => 
  array (
    'w' => 2,
    'd' => 5,
    't' => 'Pide a tu oficina las propiedades que vas a promover',
    'g' => 'Solo promueves propiedades del inventario de la oficina. Escribe aquí cuáles te asignaron.',
    'force' => false,
  ),
  24 => 
  array (
    'w' => 2,
    'd' => 5,
    't' => 'Publica 5 propiedades de la oficina en 20 grupos de Facebook y en tus redes',
    'g' => 'Súmalas en «Propiedades promovidas».',
    'force' => false,
  ),
  25 => 
  array (
    'w' => 2,
    'd' => 5,
    't' => 'Acuerda con tu oficina la fecha de tu {OH}',
    'g' => 'Escribe aquí la fecha que acordaron.',
    'force' => false,
  ),
  26 => 
  array (
    'w' => 2,
    'd' => 5,
    't' => 'Sube la foto de tus páginas llenas de Mi Mapa',
    'g' => 'Toma foto de tus páginas llenas de Mi Mapa y súbela aquí. Tu oficina la ve en Ariadna. Tus registros detallados (contactos, tratos, propiedades) guárdalos en tu agenda, la lista de tu celular o el CRM de tu oficina.',
    'force' => false,
  ),
  27 => 
  array (
    'w' => 2,
    'd' => 5,
    't' => 'Revisa tus mínimos conmigo y responde tu repaso',
    'g' => 'Para continuar debes cumplir los mínimos de las semanas 1 y 2.',
    'force' => false,
  ),
  28 => 
  array (
    'w' => 3,
    'd' => 1,
    't' => 'Agenda en tu calendario los horarios de esta semana',
    'g' => 'Antes de empezar tu semana, aparta en el calendario de tu celular un bloque de tiempo para cada actividad. Abajo tienes la lista de la semana con el tiempo que pide cada una y la fecha de tus sesiones en la oficina. Incluye todos los días tu bloque de prospección.
Escribe aquí en qué horarios quedaron tus bloques.',
    'force' => false,
  ),
  29 => 
  array (
    'w' => 3,
    'd' => 1,
    't' => 'Pon tus metas de la semana',
    'g' => 'Usa tu Mi Mapa: te dice cuántos contactos necesitas cada día para llegar a tu meta de ingresos. Tus metas aparecen en tu pantalla de cada día. El semáforo y la evaluación se miden con los mínimos del programa.',
    'force' => false,
  ),
  30 => 
  array (
    'w' => 3,
    'd' => 1,
    't' => 'Ve el Módulo 4: Open House',
    'g' => 'Pon atención a cómo se organiza y se promueve; esta semana harás el tuyo.',
    'force' => false,
  ),
  31 => 
  array (
    'w' => 3,
    'd' => 1,
    't' => 'Organiza y promueve tu {OH} con la supervisión del Team Leader o gerente de tu oficina',
    'g' => 'Cuando lo realices, regístralo en «Mis logros» con el número de asistentes reales (mínimo 5).',
    'force' => false,
  ),
  32 => 
  array (
    'w' => 3,
    'd' => 2,
    't' => 'Ve el video: opinión de valor',
    'g' => 'Aprende a comparar una propiedad con otras parecidas de la zona; hoy mismo lo aplicas al analizar tus propiedades.',
    'force' => false,
  ),
  33 => 
  array (
    'w' => 3,
    'd' => 2,
    't' => 'Analiza 2 propiedades y cuéntame qué opinas de su valor',
    'g' => 'Súmalas en «Propiedades analizadas».',
    'force' => false,
  ),
  34 => 
  array (
    'w' => 3,
    'd' => 3,
    't' => 'Inventario de la oficina',
    'g' => 'Siéntate con el Team Leader o el gerente de tu oficina a analizar el inventario: qué propiedades hay, en qué zonas, sus precios y lo más importante de cada una. Así, cuando un cliente te pregunte por algo, vas a poder responderle bien. Escribe aquí qué propiedades te llamaron más la atención y por qué.',
    'force' => true,
  ),
  35 => 
  array (
    'w' => 3,
    'd' => 3,
    't' => 'Ve el video: perfilamiento de compradores',
    'g' => 'Aprende qué preguntarle a un comprador antes de enseñarle propiedades: presupuesto, motivo, tiempos, quién decide y tipo de crédito.',
    'force' => false,
  ),
  36 => 
  array (
    'w' => 3,
    'd' => 3,
    't' => 'Analiza 3 propiedades y cuéntame qué opinas de su valor',
    'g' => 'Súmalas en «Propiedades analizadas».',
    'force' => false,
  ),
  37 => 
  array (
    'w' => 3,
    'd' => 4,
    't' => 'Invita y prospecta para tu {OH}',
    'g' => 'Invita a las personas con las que has hablado. También puedes crear una invitación y llevársela a los vecinos de dos cuadras a la redonda: muchos de ellos quizá estén pensando en vender y quieren saber cómo se compara su casa. Anota a cada asistente con su celular, qué le interesó y el siguiente paso.',
    'force' => true,
  ),
  38 => 
  array (
    'w' => 3,
    'd' => 4,
    't' => 'Promueve 3 propiedades en tus redes y en grupos',
    'g' => 'Súmalas en «Propiedades promovidas».',
    'force' => false,
  ),
  39 => 
  array (
    'w' => 3,
    'd' => 4,
    't' => 'Agenda tu primera cita',
    'g' => 'Súmala en «Citas agendadas».',
    'force' => false,
  ),
  40 => 
  array (
    'w' => 3,
    'd' => 5,
    't' => 'Publica 3 de las 5 propiedades en tus redes y en 10 grupos; contacta compradores, no a otros asesores',
    'g' => 'Publícalas en tus redes y en 10 grupos. Contesta a posibles compradores, no a otros asesores. Súmalas en «Propiedades promovidas».',
    'force' => false,
  ),
  41 => 
  array (
    'w' => 3,
    'd' => 5,
    't' => 'Analiza una ficha técnica y practica el teatro de ventas con un compañero',
    'g' => 'Escribe aquí qué aprendiste.',
    'force' => false,
  ),
  42 => 
  array (
    'w' => 3,
    'd' => 5,
    't' => 'Sube la foto de tus páginas llenas de Mi Mapa',
    'g' => 'Toma foto de tus páginas llenas de Mi Mapa y súbela aquí. Tu oficina la ve en Ariadna. Tus registros detallados (contactos, tratos, propiedades) guárdalos en tu agenda, la lista de tu celular o el CRM de tu oficina.',
    'force' => false,
  ),
  43 => 
  array (
    'w' => 3,
    'd' => 5,
    't' => 'Punto de control conmigo: responde tu repaso',
    'g' => 'Al cerrar tu Semana 3 le envío a tu oficina tu punto de control.',
    'force' => false,
  ),
  44 => 
  array (
    'w' => 4,
    'd' => 1,
    't' => 'Agenda en tu calendario los horarios de esta semana',
    'g' => 'Antes de empezar tu semana, aparta en el calendario de tu celular un bloque de tiempo para cada actividad. Abajo tienes la lista de la semana con el tiempo que pide cada una y la fecha de tus sesiones en la oficina. Incluye todos los días tu bloque de prospección.
Escribe aquí en qué horarios quedaron tus bloques.',
    'force' => false,
  ),
  45 => 
  array (
    'w' => 4,
    'd' => 1,
    't' => 'Pon tus metas de la semana',
    'g' => 'Usa tu Mi Mapa: te dice cuántos contactos necesitas cada día para llegar a tu meta de ingresos. Tus metas aparecen en tu pantalla de cada día. El semáforo y la evaluación se miden con los mínimos del programa.',
    'force' => false,
  ),
  46 => 
  array (
    'w' => 4,
    'd' => 1,
    't' => 'Ve el Módulo 5: Tratos directos y la primera cita',
    'g' => 'Al terminar, elige tu zona y sal a recorrerla.',
    'force' => false,
  ),
  47 => 
  array (
    'w' => 4,
    'd' => 1,
    't' => 'Elige una zona y recórrela buscando propietarios que vendan por trato directo',
    'g' => 'Escribe aquí qué zona elegiste.',
    'force' => false,
  ),
  48 => 
  array (
    'w' => 4,
    'd' => 1,
    't' => 'Anota cada propietario en tu agenda, celular o CRM de la oficina',
    'g' => 'Fecha, dirección, contacto, qué dijo y siguiente paso. Súmalos en «Tratos directos».',
    'force' => false,
  ),
  49 => 
  array (
    'w' => 4,
    'd' => 2,
    't' => 'Formatos internos de la oficina',
    'g' => 'Pide los formatos que usa tu oficina y aprende cuándo se usa cada uno. Escribe aquí cuál te costó más entender.',
    'force' => false,
  ),
  50 => 
  array (
    'w' => 4,
    'd' => 2,
    't' => 'Continúa con tus tratos directos',
    'g' => 'Súmalos en «Tratos directos».',
    'force' => false,
  ),
  51 => 
  array (
    'w' => 4,
    'd' => 3,
    't' => 'Procesos de la oficina',
    'g' => 'Aprende paso a paso cómo se lleva una operación en tu oficina, desde la captación hasta el cierre. Escribe aquí tus dudas.',
    'force' => false,
  ),
  52 => 
  array (
    'w' => 4,
    'd' => 3,
    't' => 'Analiza 3 propiedades y cuéntame qué opinas de su valor',
    'g' => 'Súmalas en «Propiedades analizadas».',
    'force' => false,
  ),
  53 => 
  array (
    'w' => 4,
    'd' => 4,
    't' => 'Llamadas a tus tratos directos (acompañado)',
    'g' => 'Llama a los propietarios que encontraste, acompañado por el Team Leader o el gerente de tu oficina. Anota qué te dijo cada uno y el siguiente paso.',
    'force' => false,
  ),
  54 => 
  array (
    'w' => 4,
    'd' => 4,
    't' => 'Agenda tus citas',
    'g' => 'Súmalas en «Citas agendadas». Si captas una propiedad, regístrala en «Mis logros».',
    'force' => false,
  ),
  55 => 
  array (
    'w' => 4,
    'd' => 5,
    't' => 'Reforzamiento en lo que más necesites',
    'g' => 'Lleva anotado en qué te sientes menos seguro; el Team Leader o el gerente de tu oficina te refuerza en eso.',
    'force' => false,
  ),
  56 => 
  array (
    'w' => 4,
    'd' => 5,
    't' => 'Sube la foto de tus páginas llenas de Mi Mapa',
    'g' => 'Toma foto de tus páginas llenas de Mi Mapa y súbela aquí. Tu oficina la ve en Ariadna. Tus registros detallados (contactos, tratos, propiedades) guárdalos en tu agenda, la lista de tu celular o el CRM de tu oficina.',
    'force' => false,
  ),
  57 => 
  array (
    'w' => 4,
    'd' => 5,
    't' => 'Revisión semanal conmigo: responde tu repaso',
    'g' => 'Responde con tus palabras. Tu oficina lo ve en Ariadna.',
    'force' => false,
  ),
  58 => 
  array (
    'w' => 5,
    'd' => 1,
    't' => 'Agenda en tu calendario los horarios de esta semana',
    'g' => 'Antes de empezar tu semana, aparta en el calendario de tu celular un bloque de tiempo para cada actividad. Abajo tienes la lista de la semana con el tiempo que pide cada una y la fecha de tus sesiones en la oficina. Incluye todos los días tu bloque de prospección.
Escribe aquí en qué horarios quedaron tus bloques.',
    'force' => false,
  ),
  59 => 
  array (
    'w' => 5,
    'd' => 1,
    't' => 'Pon tus metas de la semana',
    'g' => 'Usa tu Mi Mapa: te dice cuántos contactos necesitas cada día para llegar a tu meta de ingresos. Tus metas aparecen en tu pantalla de cada día. El semáforo y la evaluación se miden con los mínimos del programa.',
    'force' => false,
  ),
  60 => 
  array (
    'w' => 5,
    'd' => 1,
    't' => 'Ve el Módulo 6: Seguimiento sin parecer desesperado',
    'g' => 'Al terminar, da seguimiento de inmediato a tus prospectos y tratos directos.',
    'force' => false,
  ),
  61 => 
  array (
    'w' => 5,
    'd' => 1,
    't' => 'Da seguimiento a tus tratos directos y prospectos',
    'g' => 'Anota cada seguimiento en tu agenda, celular o CRM: con quién hablaste, qué se acordó y siguiente paso.',
    'force' => false,
  ),
  62 => 
  array (
    'w' => 5,
    'd' => 2,
    't' => 'Marco legal y antilavado',
    'g' => 'Tu oficina te explica lo legal y la ley antilavado según sus políticas. Escribe aquí tus dudas.',
    'force' => false,
  ),
  63 => 
  array (
    'w' => 5,
    'd' => 3,
    't' => 'Llamadas a prospectos y tratos pendientes (acompañado)',
    'g' => 'Llama, acompañado, a los prospectos y tratos que dejaste pendientes. Anota el siguiente paso de cada uno.',
    'force' => false,
  ),
  64 => 
  array (
    'w' => 5,
    'd' => 4,
    't' => 'Ve el Módulo 7: Manejo de objeciones básicas',
    'g' => 'Anota las objeciones que más te han hecho y cómo las vas a responder.',
    'force' => false,
  ),
  65 => 
  array (
    'w' => 5,
    'd' => 4,
    't' => 'Sigue dando seguimiento a tus prospectos',
    'g' => 'Nunca dejes pasar más de una semana sin contactar a un prospecto activo.',
    'force' => false,
  ),
  66 => 
  array (
    'w' => 5,
    'd' => 5,
    't' => 'Simulacro de venta',
    'g' => 'Practicas una venta completa con el Team Leader o el gerente de tu oficina como si fuera real. Escribe aquí qué te salió bien y qué vas a mejorar.',
    'force' => false,
  ),
  67 => 
  array (
    'w' => 5,
    'd' => 5,
    't' => 'Sube la foto de tus páginas llenas de Mi Mapa',
    'g' => 'Toma foto de tus páginas llenas de Mi Mapa y súbela aquí. Tu oficina la ve en Ariadna. Tus registros detallados (contactos, tratos, propiedades) guárdalos en tu agenda, la lista de tu celular o el CRM de tu oficina.',
    'force' => false,
  ),
  68 => 
  array (
    'w' => 5,
    'd' => 5,
    't' => 'Arma conmigo tu plan de los próximos 30 días',
    'g' => 'Escribe aquí tu plan. Tu oficina lo revisa y lo aprueba.',
    'force' => false,
  ),
  69 => 
  array (
    'w' => 5,
    'd' => 5,
    't' => 'Evaluación final: responde tu repaso y revisa tu evaluación',
    'g' => 'Calculo tu evaluación con todo lo que registraste en las 5 semanas.',
    'force' => false,
  ),
);

$changes = [];
db()->beginTransaction();

// 1) Guías del plan: se escriben las que están vacías y las que cambiaron en la revisión.
foreach ($ROWS as $r) {
    if (trim($r['g']) === '') continue;
    $titles = [$r['t']];
    if ($r['t'] === 'Analiza 3 propiedades de la oficina y cuéntame qué opinas de cada una') $titles[] = 'Analiza 2 propiedades y cuéntame qué opinas de su valor';
    foreach ($titles as $t) {
        foreach (all('SELECT id, title, guide FROM plan_activities WHERE active = 1 AND week_no = ? AND day_no = ? AND title = ?', [$r['w'], $r['d'], $t]) as $a) {
            $needTitle = $a['title'] !== $r['t'];
            $needGuide = trim((string)$a['guide']) === '' || ($r['force'] && $a['guide'] !== $r['g']);
            if (!$needTitle && !$needGuide) continue;
            q('UPDATE plan_activities SET title = ?, guide = ? WHERE id = ?', [$r['t'], $needGuide ? $r['g'] : $a['guide'], $a['id']]);
            $changes[] = 'S' . $r['w'] . ' D' . $r['d'] . ' · ' . $r['t'] . ($needTitle ? ' (nuevo título)' : '') . ($needGuide ? ' (guía)' : '');
        }
    }
}

// 2) Mínimo de propiedades analizadas de la Semana 2: 3
if ((int)val('SELECT meta_analizadas FROM plan_weeks WHERE week_no = 2') < 3) {
    q('UPDATE plan_weeks SET meta_analizadas = 3 WHERE week_no = 2');
    $changes[] = 'Semana 2 · mínimo de propiedades analizadas: 3';
}

// 3) Rutina diaria: «Usa 1 de los 3 diálogos para llamar…»
$routine = routine_items();
foreach ($routine as $i => $r) {
    if (($r['key'] ?? '') === 'R1' && strpos((string)$r['guide'], '3 diálogos') === false) {
        $routine[$i]['guide'] = 'Antes de hacer cualquier otra cosa, haz tus llamadas. Usa 1 de los 3 diálogos para llamar que vienen en tu hoja de Mi Mapa. Cada llamada cuenta como contacto. Contactar todos los días es lo que mantiene tu semana en movimiento. Súmalos en «Contactos».';
        $changes[] = 'Rutina diaria · «Usa 1 de los 3 diálogos para llamar…»';
    }
}
set_setting('routine_items', json_encode($routine, JSON_UNESCAPED_UNICODE));
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
