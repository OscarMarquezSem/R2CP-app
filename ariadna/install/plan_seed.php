<?php
/**
 * ARIADNA · Plan inicial del curso Fast Track Residencial (5 semanas).
 * Basado en las Hojas del Asesor Semana 1 a 5 y en el Cuadro de Evaluación,
 * con los ajustes aprobados en el mockup.
 * Todo se edita después desde el panel del administrador (Plan).
 *
 * Tipos (k):
 *   agenda  · Agenda tus horarios (muestra la lista de la semana con sus tiempos)
 *   metas   · Pon tus metas de la semana
 *   video   · Video del curso (enlace de Bunny en v)
 *   oficina · Sesión en tu oficina (la oficina pone fecha y hora)
 *   foto    · Con foto
 *   repaso  · Repaso semanal con Ariadna
 *   check   · Normal
 * {OH} se cambia por «Open House» o «Recorrido en vivo», según la oficina.
 * Las metas de cada semana son los MÍNIMOS del programa (semáforo y evaluación).
 */

$B = 'https://player.mediadelivery.net/play/811430/';

$AG = ['k' => 'agenda', 't' => 'Agenda en tu calendario los horarios de esta semana',
    'g' => "Antes de empezar tu semana, aparta en el calendario de tu celular un bloque de tiempo para cada actividad. Abajo tienes la lista de la semana con el tiempo que pide cada una y la fecha de tus sesiones en la oficina. Incluye todos los días tu bloque de prospección.\nEscribe aquí en qué horarios quedaron tus bloques."];

$MT = ['k' => 'metas', 't' => 'Pon tus metas de la semana',
    'g' => 'Usa tu Mi Mapa: te dice cuántos contactos necesitas cada día para llegar a tu meta de ingresos. Tus metas aparecen en tu pantalla de cada día. El semáforo y la evaluación se miden con los mínimos del programa.'];

$FOTO = ['k' => 'foto', 't' => 'Sube la foto de tus páginas llenas de Mi Mapa',
    'g' => 'Toma foto de tus páginas llenas de Mi Mapa y súbela aquí. Tu oficina la ve en Ariadna. Tus registros detallados (contactos, tratos, propiedades) guárdalos en tu agenda, la lista de tu celular o el CRM de tu oficina.'];

return [
    1 => [
        'title' => 'Inmersión, filosofía y herramientas',
        'objective' => 'Conocer el negocio, definir tu meta de ingresos y empezar a contactar a tu círculo cercano.',
        'material' => "Mi Mapa (documento aparte, se llena a mano)\nImpulsor de Memoria (hoja aparte, se llena a mano)\nMódulo 1: Matemática de tu éxito\nMódulo 2: Tu vitrina digital",
        'remember' => "Tu negocio es de actividad: sin contactos no hay citas.\nMi Mapa te dice cuántos contactos necesitas cada día para tu meta.\nDespués de ver cada módulo empiezas la tarea de inmediato.",
        'questions' => "¿Cuántos contactos diarios te marca tu Mi Mapa (renglón H)?\n¿Qué te respondieron las personas a las que llamaste?\n¿Qué fue lo más difícil de tus primeros contactos?",
        'routine_start_day' => 3,
        'metas' => ['contactos' => 60, 'tratos' => 0, 'analizadas' => 0, 'promovidas' => 0, 'citas' => 0],
        'acts' => [
            ['d' => 1] + $AG,
            ['d' => 1, 'k' => 'oficina', 't' => 'Bienvenida e introducción al negocio', 'm' => 90, 'g' => 'Sesión con el Team Leader o el gerente de tu oficina. Escribe aquí lo más importante que aprendiste.'],
            ['d' => 1, 'k' => 'check', 't' => 'Aparta 2 horas en tu agenda para el Módulo 1', 'g' => 'Mañana ves el Módulo 1. Escribe aquí a qué hora lo vas a ver.'],
            ['d' => 2, 'k' => 'video', 't' => 'Ve el Módulo 1: Matemática de tu éxito', 'm' => 45, 'v' => $B . '5d7691aa-7a9a-4d23-9ebf-a97b791f1ae5', 'g' => 'Al terminar, empieza de inmediato la tarea: llenar Mi Mapa.'],
            ['d' => 2, 'k' => 'foto', 't' => 'Llena Mi Mapa (tu meta real de ingresos)', 'g' => 'Mi Mapa se llena a mano. Ahí defines tu meta real de ingresos y te dice cuántos contactos necesitas cada día. Escribe aquí cuántos contactos diarios te marca (renglón H) y sube la foto. En tu Mi Mapa también vienen los 3 diálogos para tus llamadas: léelos antes de tu sesión de llamadas de mañana.'],
            ['d' => 2, 'k' => 'foto', 't' => 'Llena a mano tu Impulsor de Memoria lo más que puedas', 'g' => 'Pídele la hoja a tu oficina. Llénala a mano y apóyate en los contactos de tu celular para recordar a más personas. De aquí salen tus 20 llamadas diarias. Escribe cuántos nombres anotaste; si quieres, sube una foto.'],
            ['d' => 2, 'k' => 'check', 't' => 'Confirma con tu oficina tu sesión de llamadas de mañana', 'g' => 'Mañana haces tus primeras llamadas acompañado. Escribe aquí la hora.'],
            ['d' => 3, 'k' => 'oficina', 't' => 'Llamadas a tu Impulsor de Memoria (acompañado)', 'm' => 90, 'g' => 'Haces tus primeras llamadas acompañado por el Team Leader o el gerente de tu oficina. Ten a la mano los 3 diálogos de tu Mi Mapa. Anota cada respuesta y su siguiente paso en tu agenda, celular o CRM.'],
            ['d' => 4, 'k' => 'video', 't' => 'Ve el video: conceptos básicos de bienes raíces', 'g' => 'Repasa los conceptos básicos del negocio.'],
            ['d' => 4, 'k' => 'check', 't' => 'Prepárate para el examen de terminología', 'g' => 'Repasa los conceptos básicos del video.'],
            ['d' => 5, 'k' => 'video', 't' => 'Ve el Módulo 2: Tu vitrina digital', 'm' => 180, 'v' => $B . '42da8e49-59a5-45e1-ac92-9150fd57a4b8', 'g' => 'Al terminar, empieza de inmediato a trabajar en tus páginas.'],
            ['d' => 5, 'k' => 'check', 't' => 'Abre y adapta con tu marca tus páginas de Facebook e Instagram (TikTok es opcional)', 'g' => 'Escribe aquí los enlaces de tus páginas para que tu oficina los vea.',
                'h' => [['Cómo abrir una página de negocios en Facebook', 'https://youtu.be/1tOM7uOKwD0'], ['Cómo abrir una cuenta en Instagram', 'https://youtu.be/2cnjbzghWXU']]],
            ['d' => 5, 'k' => 'check', 't' => 'Crea mínimo 3 contenidos', 'g' => 'Publica al menos 3 contenidos en tus páginas. Escribe aquí cuáles fueron.',
                'h' => [['Cómo utilizar Canva', 'https://youtu.be/8GHGqNEK800'], ['Cómo crear contenido', 'https://youtu.be/11XYCHJFeJU']]],
            ['d' => 5] + $FOTO,
            ['d' => 5, 'k' => 'repaso', 't' => 'Revisión semanal conmigo: responde tu repaso', 'g' => 'Responde con tus palabras. Tu oficina lo ve en Ariadna.'],
        ],
    ],
    2 => [
        'title' => 'Promoción, gestión y documentación',
        'objective' => 'Tener tu vitrina digital funcionando, entender la documentación de una operación y empezar a promover propiedades de la oficina.',
        'material' => "Módulo 3: Marca personal desde el día 1\nSesiones en tu oficina: documentación de personas físicas y morales; documentación de la propiedad e impuestos",
        'remember' => "Tus redes y tus contenidos deben estar terminados antes de avanzar.\nSolo promueves propiedades del inventario de la oficina.\nCada contacto queda anotado con su siguiente paso en tu agenda, celular o CRM.",
        'questions' => "¿Qué publicaste esta semana y qué reacciones tuviste?\n¿Qué documentos te faltaban conocer y ya entiendes?\n¿A cuántas personas contactaste y qué aprendiste?",
        'routine_start_day' => 1,
        'metas' => ['contactos' => 100, 'tratos' => 0, 'analizadas' => 2, 'promovidas' => 5, 'citas' => 0],
        'acts' => [
            ['d' => 1] + $AG,
            ['d' => 1] + $MT,
            ['d' => 1, 'k' => 'check', 't' => 'Revisa que tus páginas en redes y tus contenidos estén terminados', 'g' => 'Si no están terminados, no avanzas a la siguiente tarea. Escribe aquí los enlaces.'],
            ['d' => 2, 'k' => 'oficina', 't' => 'Documentación de personas físicas y morales', 'm' => 180, 'g' => 'Lleva libreta y pregunta todo. Escribe aquí qué documentos no conocías.'],
            ['d' => 3, 'k' => 'video', 't' => 'Ve el Módulo 3: Marca personal desde el día 1', 'm' => 180, 'v' => $B . 'd660d59b-ae2e-4fea-9a75-e3b3f2f221aa'],
            ['d' => 3, 'k' => 'foto', 't' => 'Haz el trabajo que indica el video y súbelo a Ariadna', 'g' => 'Sube una foto o escribe el enlace de tu trabajo antes de terminar el día.'],
            ['d' => 4, 'k' => 'oficina', 't' => 'Documentación de la propiedad e impuestos', 'm' => 180, 'g' => 'Escribe aquí lo más importante que aprendiste.'],
            ['d' => 4, 'k' => 'check', 't' => 'Analiza 2 propiedades y cuéntame qué opinas de su valor', 'g' => 'Súmalas en «Propiedades analizadas» y cuéntame qué propiedad es, qué opinas de su valor y por qué.'],
            ['d' => 5, 'k' => 'check', 't' => 'Pide a tu oficina las propiedades que vas a promover', 'g' => 'Solo promueves propiedades del inventario de la oficina. Escribe aquí cuáles te asignaron.'],
            ['d' => 5, 'k' => 'check', 't' => 'Publica 5 propiedades de la oficina en 20 grupos de Facebook y en tus redes', 'g' => 'Súmalas en «Propiedades promovidas».'],
            ['d' => 5, 'k' => 'check', 't' => 'Acuerda con tu oficina la fecha de tu {OH}', 'g' => 'Escribe aquí la fecha que acordaron.'],
            ['d' => 5] + $FOTO,
            ['d' => 5, 'k' => 'repaso', 't' => 'Revisa tus mínimos conmigo y responde tu repaso', 'g' => 'Para continuar debes cumplir los mínimos de las semanas 1 y 2.'],
        ],
    ],
    3 => [
        'title' => 'Inventario y análisis de mercado',
        'objective' => 'Aprender a valorar propiedades, perfilar compradores y hacer tu primer {OH}.',
        'material' => "Módulo 4: Open House\nVideos: opinión de valor; perfilamiento de compradores\nSesión en tu oficina: inventario de la oficina",
        'remember' => "Contacta a posibles compradores, no a otros asesores.\nPerfila al comprador antes de enseñarle propiedades.\nRegistra a cada asistente de tu {OH} y dale seguimiento.",
        'questions' => "¿Cuántas personas asistieron a tu {OH} y qué hiciste con ellas?\n¿Qué propiedad analizaste mejor y por qué?\n¿Qué te falta para agendar más citas?",
        'routine_start_day' => 1,
        'metas' => ['contactos' => 100, 'tratos' => 0, 'analizadas' => 5, 'promovidas' => 3, 'citas' => 1],
        'acts' => [
            ['d' => 1] + $AG,
            ['d' => 1] + $MT,
            ['d' => 1, 'k' => 'video', 't' => 'Ve el Módulo 4: Open House', 'm' => 120, 'v' => $B . '1049b1d5-37f5-4a49-a31e-3a5679cc41ac'],
            ['d' => 1, 'k' => 'check', 't' => 'Organiza y promueve tu {OH} con la supervisión del Team Leader o gerente de tu oficina', 'g' => 'Cuando lo realices, regístralo en «Mis logros» con el número de asistentes reales (mínimo 5).'],
            ['d' => 2, 'k' => 'video', 't' => 'Ve el video: opinión de valor'],
            ['d' => 2, 'k' => 'check', 't' => 'Analiza 2 propiedades y cuéntame qué opinas de su valor', 'g' => 'Súmalas en «Propiedades analizadas».'],
            ['d' => 3, 'k' => 'oficina', 't' => 'Inventario de la oficina', 'm' => 60],
            ['d' => 3, 'k' => 'video', 't' => 'Ve el video: perfilamiento de compradores'],
            ['d' => 3, 'k' => 'check', 't' => 'Analiza 3 propiedades y cuéntame qué opinas de su valor', 'g' => 'Súmalas en «Propiedades analizadas».'],
            ['d' => 4, 'k' => 'check', 't' => 'Invita y prospecta para tu {OH}', 'g' => 'Anota a cada asistente con su celular, qué le interesó y el siguiente paso.'],
            ['d' => 4, 'k' => 'check', 't' => 'Promueve 3 propiedades en tus redes y en grupos', 'm' => 120, 'g' => 'Súmalas en «Propiedades promovidas».'],
            ['d' => 4, 'k' => 'check', 't' => 'Agenda tu primera cita', 'g' => 'Súmala en «Citas agendadas».'],
            ['d' => 5, 'k' => 'check', 't' => 'Publica 3 de las 5 propiedades en tus redes y en 10 grupos; contacta compradores, no a otros asesores', 'm' => 180],
            ['d' => 5, 'k' => 'check', 't' => 'Analiza una ficha técnica y practica el teatro de ventas con un compañero', 'g' => 'Escribe aquí qué aprendiste.'],
            ['d' => 5] + $FOTO,
            ['d' => 5, 'k' => 'repaso', 't' => 'Punto de control conmigo: responde tu repaso', 'g' => 'Al cerrar tu Semana 3 le envío a tu oficina tu punto de control.'],
        ],
    ],
    4 => [
        'title' => 'Tratos directos, formatos y procesos',
        'objective' => 'Salir a buscar propietarios por trato directo, conocer los formatos y procesos de la oficina y agendar citas.',
        'material' => "Módulo 5: Tratos directos y la primera cita\nSesiones en tu oficina: formatos internos; procesos; llamadas a tus tratos directos; reforzamiento",
        'remember' => "La tarea de la semana es recorrer tu zona y buscar vendedores por trato directo.\nCada trato directo queda en tu agenda, celular o CRM con su siguiente paso.\nUna captación necesita ficha completa y acuerdo de promoción firmado.",
        'questions' => "¿A cuántos propietarios contactaste y cuál te interesó más?\n¿Qué formato de la oficina te costó más trabajo entender?\n¿Qué citas agendaste?",
        'routine_start_day' => 1,
        'metas' => ['contactos' => 100, 'tratos' => 6, 'analizadas' => 3, 'promovidas' => 0, 'citas' => 2],
        'acts' => [
            ['d' => 1] + $AG,
            ['d' => 1] + $MT,
            ['d' => 1, 'k' => 'video', 't' => 'Ve el Módulo 5: Tratos directos y la primera cita', 'm' => 60, 'v' => $B . '62c7f889-d763-4b24-9a40-cba08d383d0b'],
            ['d' => 1, 'k' => 'check', 't' => 'Elige una zona y recórrela buscando propietarios que vendan por trato directo', 'g' => 'Escribe aquí qué zona elegiste.'],
            ['d' => 1, 'k' => 'check', 't' => 'Anota cada propietario en tu agenda, celular o CRM de la oficina', 'g' => 'Fecha, dirección, contacto, qué dijo y siguiente paso. Súmalos en «Tratos directos».'],
            ['d' => 2, 'k' => 'oficina', 't' => 'Formatos internos de la oficina', 'm' => 120],
            ['d' => 2, 'k' => 'check', 't' => 'Continúa con tus tratos directos', 'g' => 'Súmalos en «Tratos directos».'],
            ['d' => 3, 'k' => 'oficina', 't' => 'Procesos de la oficina', 'm' => 120],
            ['d' => 3, 'k' => 'check', 't' => 'Analiza 3 propiedades y cuéntame qué opinas de su valor', 'g' => 'Súmalas en «Propiedades analizadas».'],
            ['d' => 4, 'k' => 'oficina', 't' => 'Llamadas a tus tratos directos (acompañado)', 'm' => 120],
            ['d' => 4, 'k' => 'check', 't' => 'Agenda tus citas', 'g' => 'Súmalas en «Citas agendadas». Si captas una propiedad, regístrala en «Mis logros».'],
            ['d' => 5, 'k' => 'oficina', 't' => 'Reforzamiento en lo que más necesites'],
            ['d' => 5] + $FOTO,
            ['d' => 5, 'k' => 'repaso', 't' => 'Revisión semanal conmigo: responde tu repaso'],
        ],
    ],
    5 => [
        'title' => 'Seguimiento, legal y cierre',
        'objective' => 'Dar seguimiento a todos tus prospectos, conocer el marco legal de la operación y cerrar el curso con tu evaluación.',
        'material' => "Módulo 6: Seguimiento sin parecer desesperado\nMódulo 7: Manejo de objeciones básicas\nSesiones en tu oficina: marco legal y antilavado; llamadas a prospectos pendientes; simulacro de venta",
        'remember' => "Nunca dejes pasar más de una semana sin contactar a un prospecto activo.\nAntes de cualquier operación, valida lo legal con tu oficina.\nTu plan de los próximos 30 días lo armamos el último día.",
        'questions' => "¿Qué prospectos siguen activos y cuál es el siguiente paso de cada uno?\n¿Qué objeción te cuesta más responder?\n¿Qué harás distinto en tus próximos 30 días?",
        'routine_start_day' => 1,
        'metas' => ['contactos' => 100, 'tratos' => 4, 'analizadas' => 0, 'promovidas' => 0, 'citas' => 2],
        'acts' => [
            ['d' => 1] + $AG,
            ['d' => 1] + $MT,
            ['d' => 1, 'k' => 'video', 't' => 'Ve el Módulo 6: Seguimiento sin parecer desesperado', 'm' => 120, 'v' => $B . '9975e8fc-a00f-442d-96ff-81aa9643a4a0'],
            ['d' => 1, 'k' => 'check', 't' => 'Da seguimiento a tus tratos directos y prospectos', 'g' => 'Anota cada seguimiento en tu agenda, celular o CRM: con quién hablaste, qué se acordó y siguiente paso.'],
            ['d' => 2, 'k' => 'oficina', 't' => 'Marco legal y antilavado', 'm' => 180],
            ['d' => 3, 'k' => 'oficina', 't' => 'Llamadas a prospectos y tratos pendientes (acompañado)', 'm' => 120],
            ['d' => 4, 'k' => 'video', 't' => 'Ve el Módulo 7: Manejo de objeciones básicas', 'm' => 120, 'v' => $B . 'de993d1f-ae70-4537-8ade-995d67197484'],
            ['d' => 4, 'k' => 'check', 't' => 'Sigue dando seguimiento a tus prospectos'],
            ['d' => 5, 'k' => 'oficina', 't' => 'Simulacro de venta', 'm' => 180],
            ['d' => 5] + $FOTO,
            ['d' => 5, 'k' => 'check', 't' => 'Arma conmigo tu plan de los próximos 30 días', 'g' => 'Escribe aquí tu plan. Tu oficina lo revisa y lo aprueba.'],
            ['d' => 5, 'k' => 'repaso', 't' => 'Evaluación final: responde tu repaso y revisa tu evaluación', 'g' => 'Calculo tu evaluación con todo lo que registraste en las 5 semanas.'],
        ],
    ],
];
