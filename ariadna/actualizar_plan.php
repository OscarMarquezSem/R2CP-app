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
ensure_schema();   // crea las tablas nuevas (Reto del video) si faltan

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
    't' => 'Reto del Módulo 1 con tu Team Leader',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te hace las 5 preguntas de tu Reto del Módulo 1 (unos 10 minutos) y te ayuda a empezar tu Mi Mapa. No es para calificarte: es para asegurarnos de que agarraste el hilo. Escribe aquí en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el Módulo 1: Matemática de tu éxito',
  ),
  3 => 
  array (
    'w' => 1,
    'd' => 4,
    'k' => 'tl',
    't' => 'Reto del video de conceptos básicos con tu Team Leader',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te hace las 5 preguntas de tu Reto del video de conceptos básicos (unos 10 minutos) y te ayuda a preparar tu examen de terminología. No es para calificarte: es para asegurarnos de que agarraste el hilo. Escribe aquí en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el video: conceptos básicos de bienes raíces',
  ),
  4 => 
  array (
    'w' => 1,
    'd' => 5,
    'k' => 'tl',
    't' => 'Cierre de semana con tu Team Leader',
    'g' => 'Ve a tu oficina y siéntate con tu Team Leader. Revisen juntos tus actividades y tus números de la semana, tu repaso conmigo y tu Reto del Módulo 2: 5 preguntas del video. Escribe aquí los acuerdos a los que llegaron.',
    'm' => 45,
    'after' => NULL,
  ),
  5 => 
  array (
    'w' => 2,
    'd' => 3,
    'k' => 'tl',
    't' => 'Reto del Módulo 3 con tu Team Leader',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te hace las 5 preguntas de tu Reto del Módulo 3 (unos 10 minutos) y te orienta con tu foto y tu biografía. No es para calificarte: es para asegurarnos de que agarraste el hilo. Escribe aquí en qué te pidió reforzar.',
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
    't' => 'Reto del Módulo 4 con tu Team Leader',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te hace las 5 preguntas de tu Reto del Módulo 4 (unos 10 minutos) y empiezan juntos a organizar tu {OH}. No es para calificarte: es para asegurarnos de que agarraste el hilo. Escribe aquí en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el Módulo 4: Open House',
  ),
  8 => 
  array (
    'w' => 3,
    'd' => 2,
    'k' => 'tl',
    't' => 'Reto del video de opinión de valor con tu Team Leader',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te hace las 5 preguntas de tu Reto del video de opinión de valor (unos 10 minutos): le explicas cómo comparas una propiedad con otras parecidas de la zona. No es para calificarte: es para asegurarnos de que agarraste el hilo. Escribe aquí en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el video: opinión de valor',
  ),
  9 => 
  array (
    'w' => 3,
    'd' => 3,
    'k' => 'tl',
    't' => 'Reto del video de perfilamiento con tu Team Leader',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te hace las 5 preguntas de tu Reto del video de perfilamiento (unos 10 minutos) y hacen un ejercicio: tu Team Leader hace el papel de comprador y tú lo perfilas. No es para calificarte: es para asegurarnos de que agarraste el hilo. Escribe aquí en qué te pidió reforzar.',
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
    't' => 'Reto del Módulo 5 con tu Team Leader',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te hace las 5 preguntas de tu Reto del Módulo 5 (unos 10 minutos) y te ayuda a elegir dónde buscar tus 20 tratos directos. No es para calificarte: es para asegurarnos de que agarraste el hilo. Escribe aquí en qué te pidió reforzar.',
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
    't' => 'Reto del Módulo 6 con tu Team Leader',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te hace las 5 preguntas de tu Reto del Módulo 6 (unos 10 minutos) y revisan juntos cómo vas a organizar tus contactos. No es para calificarte: es para asegurarnos de que agarraste el hilo. Escribe aquí en qué te pidió reforzar.',
    'm' => 30,
    'after' => 'Ve el Módulo 6: Seguimiento sin parecer desesperado',
  ),
  14 => 
  array (
    'w' => 5,
    'd' => 4,
    'k' => 'tl',
    't' => 'Reto del Módulo 7 con tu Team Leader',
    'g' => 'En cuanto termines el video, ve a tu oficina. Tu Team Leader te hace las 5 preguntas de tu Reto del Módulo 7 (unos 10 minutos) y practican juntos cómo responder las objeciones que más te han hecho. No es para calificarte: es para asegurarnos de que agarraste el hilo. Escribe aquí en qué te pidió reforzar.',
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
  2 => 'Esta semana tienes 2 citas con tu Team Leader en la oficina: el Reto del video y el cierre de semana.',
  3 => 'Esta semana tienes 4 citas con tu Team Leader en la oficina: una después de cada video (días 1, 2 y 3) y tu punto de control.',
  4 => 'Esta semana tienes 2 citas con tu Team Leader en la oficina: el Reto del video y el cierre de semana.',
  5 => 'Esta semana tienes 3 citas con tu Team Leader en la oficina: una después de cada video (Módulos 6 y 7) y el cierre del curso.',
);

$TL_ALIAS = array (
  'Reto del Módulo 1 con tu Team Leader' => 'Revisa con tu Team Leader lo que aprendiste del Módulo 1',
  'Reto del video de conceptos básicos con tu Team Leader' => 'Platica con tu Team Leader los conceptos básicos del video',
  'Reto del Módulo 3 con tu Team Leader' => 'Revisa con tu Team Leader lo que aprendiste del Módulo 3',
  'Reto del Módulo 4 con tu Team Leader' => 'Revisa con tu Team Leader lo que aprendiste del Módulo 4',
  'Reto del video de opinión de valor con tu Team Leader' => 'Platica con tu Team Leader lo que aprendiste del video de opinión de valor',
  'Reto del video de perfilamiento con tu Team Leader' => 'Platica con tu Team Leader lo que aprendiste del video de perfilamiento',
  'Reto del Módulo 5 con tu Team Leader' => 'Revisa con tu Team Leader lo que aprendiste del Módulo 5',
  'Reto del Módulo 6 con tu Team Leader' => 'Revisa con tu Team Leader lo que aprendiste del Módulo 6',
  'Reto del Módulo 7 con tu Team Leader' => 'Revisa con tu Team Leader lo que aprendiste del Módulo 7',
);

$RETOS = array (
  'Ve el Módulo 1: Matemática de tu éxito' => '¿Por qué decimos que tu trabajo no es vender casas? | Tu trabajo es conseguir personas. Sin prospectos no hay ventas: un médico sin pacientes es solo alguien con bata blanca. | 4
¿Cómo calculas en Mi Mapa cuántos lados necesitas al año? | Gastos del mes × 12 = gasto anual. Ese número entre tu comisión promedio (lo que entra a tu bolsillo por una venta) te da los lados al año. | 8-13
¿Por qué el 70 % de tus lados debe ser del lado del propietario? | Con la propiedad, los compradores te siguen, puedes llevarte los dos lados y ganas estabilidad y experiencia real (no «5 veces el primer año»). | 15-17
¿Qué cuenta como un contacto y cuál es el número más importante de tu Mi Mapa? | Decir uno de los diálogos, no solo saludar. La letra H, tus contactos diarios (6 días a la semana): tu número mínimo de supervivencia. | 20-23
¿Cuáles son los 5 pasos del diálogo y qué haces si alguien te dice que quiere vender? | Confirmar, presentarte, captar la atención («necesito pedirte algo y no es dinero»), explicar y cerrar («¿conoces a alguien que quiera comprar, vender o rentar?»). Si quiere vender, le propones sentarse a platicar y pides que alguien de tu oficina te acompañe. | 27-35',
  'Ve el Módulo 2: Tu vitrina digital' => '¿Por qué las redes son tu «oficina pública» y cada cuándo publicas? | Son trabajo, no entretenimiento: la herramienta de marketing más poderosa y gratis (cuesta tiempo y esfuerzo). Publicas todos los días, no cuando tengas ganas. | 3-6
¿Qué diferencia hay entre Facebook, Instagram, TikTok y el estatus de WhatsApp? | Facebook: mayores de 30 con poder de compra; perfil, página de negocios y grupos, con foto, precio, zona y contacto. Instagram: visual, que se imaginen viviendo ahí. TikTok: alcance orgánico, historia y atrapar en 3 segundos. Estatus de WhatsApp Business: gente que ya confía en ti; mínimo una propiedad o contenido al día. | 6-14
¿Cuáles son las 4 partes del post perfecto? | Gancho (detiene el scroll), descripción que haga soñar con 3 o 4 datos clave, precio (obligatorio en México; filtra y atrae) y llamado a la acción específico. | 15-24
¿Por qué el llamado a la acción es «Comenta me interesa» y no tu número de WhatsApp? | La interacción hace que la red siga mostrando tu post. Si solo te llaman, la red cree que no funcionó y lo deja de promover. | 22-24
¿Por qué una de tus 3 propiedades debe ser renta y qué es la regla 3 + 1? | La renta tiene un ciclo corto (unos 30 días), contactos inmediatos, práctica sin presión y flujo de ingresos; el inquilino de hoy es el comprador de mañana. Regla 3 + 1: por cada 3 propiedades, 1 contenido de valor. | 24-37',
  'Ve el Módulo 3: Marca personal desde el día 1' => '¿Cuánto tiempo tienes para la primera impresión digital y en qué se fija la gente? | Menos de 10 segundos: tu foto, tu nombre y tu biografía. Un perfil descuidado es peor que no tener perfil. | 0-4
¿Cómo debe ser tu foto de perfil y cuáles no funcionan? | Ropa de negocios, buena luz, sonrisa genuina, fondo limpio, actual y la misma en todas tus redes. No: playa, boda, mascota, hijos, lentes de sol o fotos viejas. | 3-7
¿Qué 3 preguntas responde tu biografía? | Quién eres (asesor inmobiliario en tu ciudad o zona), a quién ayudas (tu avatar o especialidad) y cómo te contactan (tu número o enlace de WhatsApp, obligatorio). | 7-13
¿Por qué conviene especializarte y cuáles son los 3 nichos? | Te vuelves el referente de esa zona o ese tipo de cliente. Los nichos son salud, dinero y relaciones. | 8-12
¿Qué cuidas en tu comportamiento en redes? | Publicar todos los días (puedes programar con Meta Business Suite), la regla 3 + 1, nada de política, peleas ni quejas, cuidar tu perfil personal porque te investigan y responder en minutos. | 13-20',
  'Ve el Módulo 4: Open House' => '¿Por qué funciona un Open House y por qué hacerlo con otro asesor? | Sin cita ni presión; llegan prospectos más calificados (compradores y vendedores) y es práctica real. Con otro asesor hay doble energía, seguridad y leads compartidos; se acuerda antes cómo se reparten. | 3-8
¿Qué propiedad eliges y cómo la preparas? | Atractiva, bien ubicada, fácil de llegar y con precio competitivo. Limpia, ordenada, iluminada y que huela bien; si tiene daños, avísalo al recibir a la gente. | 5-14
¿Cómo lo promueves y por qué el anuncio dice «Comenta info y te confirmo el lugar»? | 7 días antes, todos los días y varias veces al día en redes, grupos y WhatsApp, con QR de registro e invitación a los vecinos. Sin dirección, para generar interacción y controlar los accesos. | 16-25
¿Qué 3 preguntas clasifican al prospecto en la entrada? | ¿Para vivir o para invertir (revender o rentar)?, ¿tienes un límite de tiempo? y ¿ya visitaste otras en la zona? Lo clasificas como caliente, tibio o frío. Haces preguntas en vez de afirmaciones y escuchas más. | 27-35
¿Cuándo das seguimiento y cuál es el «secreto» para conseguir inventario? | Esa misma tarde, con un mensaje que termine en pregunta («¿Te puedo llamar mañana?»). El secreto: invitar a los vecinos de 2 o 3 cuadras con una opinión de valor gratis; así consigues propietarios que quieren vender. | 36-40',
  'Ve el Módulo 5: Tratos directos y la primera cita' => '¿Qué es un trato directo y dónde lo encuentras? | Un propietario que vende por su cuenta, sin asesor, y al que tú buscas primero. En letreros y lonas, portales (filtro «trato directo»), Marketplace, grupos de compraventa de Facebook y tu propio vecindario. | 3-8
¿Cuáles son los 5 pasos del diálogo de trato directo? | Confirmar («¿eres el dueño de la casa en venta en…?»), presentarte (primer nombre y asesor inmobiliario), captar la atención («tengo una pregunta» y una pausa breve), explicar («¿te interesaría mostrar tu propiedad a un cliente de mi inmobiliaria?») y cerrar la cita proponiendo tú el día y la hora. | 8-16
¿Qué contestas si al teléfono te pone condiciones («no firmo exclusiva», «no pago comisión»)? | «No hay problema, nos vemos hoy a las 5». El objetivo es entrar a la casa, no discutir por teléfono. | 16-18
¿Cómo te preparas para la cita y qué preguntas haces ahí? | Investigas la zona y los precios de propiedades parecidas (datos, no opiniones) y llegas 5 minutos antes. Preguntas: cuánto tiempo lleva en venta (en promedio 6 semanas), si ha tenido visitas y qué le dijeron, por qué vende por su cuenta, qué tan pronto necesita vender, si trabaja con otros asesores y si tiene un plan de ventas. | 21-34
¿Qué es hablar de resultados y no de servicios, y con qué terminas siempre la cita? | En vez de «publicamos en portales», dices «tenemos clientes buscando este tipo de casa en esta zona». Nunca te vas sin el siguiente paso: volver a tomar fotos en 1 a 3 días, presentar tu plan de ventas, una llamada con fecha y hora, o una cita con alguien de tu oficina y con todos los que deciden. | 33-40',
  'Ve el Módulo 6: Seguimiento sin parecer desesperado' => '¿Por qué se muere el seguimiento y qué dice la estadística? | Por el miedo a molestar. El 48 % abandona después del primer intento y el 80 % de las ventas ocurre después del quinto contacto. Dar seguimiento es estar presente cuando el cliente decide. | 0-4
¿Cuáles son los 3 elementos del sistema y qué datos lleva tu lista? | Lista, frecuencia y pretexto de valor. La lista lleva nombre, teléfono, fuente, qué busca, último contacto con notas y fechas especiales (en WhatsApp Business con etiquetas, una hoja de Google o Excel, o el CRM de tu oficina). | 4-11
¿Cada cuándo das seguimiento a un prospecto caliente, uno tibio y uno frío? | Caliente: cada 1 a 5 días. Tibio: una vez por semana. Frío: 2 o 3 veces al mes, con redes, fechas especiales y novedades del mercado. | 11-18
¿Por qué no preguntas «¿ya se decidieron?» y cómo es un buen mensaje? | Esa pregunta da por hecho que ya están listos. Un buen mensaje lleva un pretexto de valor (propiedad nueva que le queda, precios de su zona, dato de impuestos), es corto, lleva su nombre y termina con una pregunta. Las llamadas, para los calientes y los momentos clave. | 18-29
¿Cuándo y cómo sueltas a un prospecto con elegancia? | Después de 5 o 6 contactos bien espaciados y con valor, sin respuesta. Mensaje de cierre: «Entiendo que quizá no es el momento… aquí voy a estar». Quita presión y muchos contestan; sigue en tu lista de distribución. | 31-36',
  'Ve el Módulo 7: Manejo de objeciones básicas' => '¿Qué es en realidad una objeción? | No es un no: es una pregunta disfrazada, «todavía no me has dado suficiente razón». Es una puerta entreabierta. | 2-4
¿Qué contestas a «lo voy a pensar»? | No dices «claro, tómese su tiempo» y te vas: es una cortina de humo. Validas y preguntas: «¿Qué es lo que te genera más dudas: el precio, la ubicación, el proceso, la comisión o la exclusiva?». | 3-6
¿Y a «está muy caro»? | No lo tomas personal: «Entiendo que el precio es importante, ¿con qué lo estás comparando?». Si es parecido, averiguas qué ofrece la competencia; si no, explicas la diferencia con datos. Terminas con «¿qué precio te haría sentir que es una buena decisión?». | 6-8
¿Y a «ya tengo asesor»? | Preguntas si ya firmó exclusiva. Si no, le ofreces otra perspectiva (tu plan de ventas). Si ya firmó, la respetas y te retiras con clase, sin hablar mal del otro asesor. | 8-10
¿Cómo respondes a «no es el momento» y a «lo tengo que platicar con mi pareja»? ¿Cuáles son las 3 reglas de oro? | «¿Qué tendría que pasar para que fuera el momento?» y sigues con seguimiento de valor. «¿Cuándo podemos reunirnos los tres?». Reglas de oro: no interrumpir, validar antes de responder y practicar en voz alta. | 10-13',
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
    $same = one('SELECT id, guide FROM plan_activities WHERE active = 1 AND week_no = ? AND day_no = ? AND title = ?', [$a['w'], $a['d'], $a['t']]);
    if (!$same && isset($TL_ALIAS[$a['t']])) {
        // La cita ya existía con su nombre anterior: ahora se llama «Reto … con tu Team Leader».
        $same = one('SELECT id, guide FROM plan_activities WHERE active = 1 AND week_no = ? AND day_no = ? AND title = ?', [$a['w'], $a['d'], $TL_ALIAS[$a['t']]]);
        if ($same) { q('UPDATE plan_activities SET title = ? WHERE id = ?', [$a['t'], $same['id']]); $same['guide'] = null; }
    }
    if ($same) {
        if ($same['guide'] !== $a['g']) { q('UPDATE plan_activities SET guide = ? WHERE id = ?', [$a['g'], $same['id']]); $changes[] = $label . ' (texto nuevo)'; }
        continue;
    }
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


// Las 5 preguntas del Reto de cada video (solo si el video aún no tiene preguntas).
foreach ($RETOS as $title => $txt) {
    foreach (all("SELECT id, reto FROM plan_activities WHERE active = 1 AND kind = 'video' AND title = ?", [$title]) as $r) {
        if (trim((string)$r['reto']) !== '') continue;
        q('UPDATE plan_activities SET reto = ? WHERE id = ?', [$txt, $r['id']]);
        $changes[] = 'Reto del video · ' . reto_label($title) . ': 5 preguntas';
    }
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
    'Esta semana tienes 2 citas con tu Team Leader en la oficina: el repaso del video y el cierre de semana.',
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
