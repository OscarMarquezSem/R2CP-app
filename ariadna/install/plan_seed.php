<?php
/**
 * ARIADNA · Plan inicial de 5 semanas (Fast Track Residencial).
 * Tomado de las Hojas del Asesor Semana 1 a 5 y del Cuadro de Evaluación.
 * Después de instalar, todo se edita desde el panel del administrador (Plan).
 * Las metas semanales son ilustrativas y se ajustan desde el panel.
 *
 * Formato de cada actividad: [día, título, guía, minutos|null, tipo]
 * tipo: check | foto | agenda | repaso
 */

$AGENDA_GUIDE = 'Antes de empezar tu semana, aparta en el calendario de tu celular un bloque de tiempo para cada actividad. '
    . 'Abajo tienes la lista de la semana con el tiempo que pide cada una. Incluye todos los días tu bloque de prospección para tus 2 contactos. '
    . 'Escribe aquí en qué horarios quedaron tus bloques.';

$FOTO_GUIDE = 'Toma foto de tus páginas llenas de Mi Mapa y de tu registro y súbela aquí. Tu oficina la ve en Ariadna.';

return [
    1 => [
        'title' => 'Inmersión, filosofía y herramientas',
        'objective' => 'Conocer el negocio, definir tu meta de ingresos y empezar a contactar a tu círculo cercano.',
        'material' => "Mi Mapa (documento aparte)\nImpulsor de Memoria (documento aparte)\nMódulo 1: Matemática de tu éxito\nMódulo 2: Tu vitrina digital\nLos videos y documentos te los envía tu Team Leader.",
        'remember' => "Tu negocio es de actividad: sin contactos no hay citas.\nMi Mapa te dice cuántos contactos necesitas cada día para tu meta.\nDespués de ver cada módulo te quedas en la oficina y empiezas la tarea de inmediato.",
        'questions' => "¿Cuántos contactos diarios te marca tu Mi Mapa (renglón H)?\n¿Qué te respondieron las personas a las que llamaste?\n¿Qué fue lo más difícil de tus primeros contactos?",
        'routine_start_day' => 3,
        'metas' => ['contactos' => 10, 'tratos' => 0, 'analizadas' => 0, 'promovidas' => 0, 'citas' => 0],
        'acts' => [
            [1, 'Agenda en tu calendario los horarios de esta semana', $AGENDA_GUIDE . ' Organiza tu agenda diaria con bloques de prospección y seguimiento.', null, 'agenda'],
            [1, 'Sesión con tu Team Leader: bienvenida e introducción al negocio', 'Sesión de 90 minutos con tu Team Leader. Escribe aquí lo más importante que aprendiste.', 90, 'check'],
            [1, 'Agenda con tu Team Leader la sesión del Módulo 1', 'La sesión del Módulo 1 dura 2 horas. Escribe aquí el día y la hora que acordaron.', null, 'check'],
            [2, 'Ve el Módulo 1: Matemática de tu éxito', 'Video de aproximadamente 45 minutos; te lo envía tu Team Leader. Al terminar, quédate en la oficina y empieza la tarea de inmediato.', 45, 'check'],
            [2, 'Llena Mi Mapa (tu meta real de ingresos)', 'Mi Mapa es un documento aparte. Ahí defines tu meta real de ingresos y te dice cuántos contactos necesitas cada día para lograrla. Escribe aquí cuántos contactos diarios te marca (renglón H).', null, 'foto'],
            [2, 'Llena tu Impulsor de Memoria lo más que puedas', 'El Impulsor de Memoria es una hoja que te entrega tu Team Leader y se llena a mano. Apóyate en los contactos de tu celular para recordar a más personas. De aquí salen tus 2 contactos diarios. Escribe cuántos nombres anotaste; si quieres, sube una foto.', null, 'foto'],
            [2, 'Agenda tu sesión de llamadas para mañana', 'La sesión de llamadas a tu Impulsor dura 90 minutos. Escribe aquí la hora que acordaste.', null, 'check'],
            [3, 'Sesión con tu Team Leader: llamadas a tu Impulsor de Memoria', 'Sesión de 90 minutos. Anota las respuestas en tu registro de contactos con lo que te dijeron y el siguiente paso.', 90, 'check'],
            [4, 'Sesión con tu Team Leader: revisión de resultados y conceptos básicos de bienes raíces', 'Sesión de 90 minutos. Escribe aquí los conceptos que más te costaron.', 90, 'check'],
            [4, 'Prepárate para el examen de terminología', 'Repasa los conceptos básicos de bienes raíces que viste en tu sesión.', null, 'check'],
            [5, 'Ve el Módulo 2: Tu vitrina digital', 'Video de 3 horas. Al terminar, empieza de inmediato a trabajar en tus páginas.', 180, 'check'],
            [5, 'Abre y adapta con tu marca tus páginas de Facebook e Instagram (TikTok es opcional)', 'Escribe aquí los enlaces de tus páginas para que tu oficina los vea.', null, 'check'],
            [5, 'Crea mínimo 3 contenidos', 'Publica al menos 3 contenidos en tus páginas. Escribe aquí cuáles fueron.', null, 'check'],
            [5, 'Sube la foto de tus páginas llenas de Mi Mapa y de tu registro', $FOTO_GUIDE, null, 'foto'],
            [5, 'Revisión semanal: responde tu repaso de la semana', 'Responde con tus palabras las preguntas del repaso. Tu oficina lo ve en Ariadna.', null, 'repaso'],
        ],
    ],
    2 => [
        'title' => 'Promoción, gestión y documentación',
        'objective' => 'Tener tu vitrina digital funcionando, entender la documentación de una operación y empezar a promover propiedades de la oficina.',
        'material' => "Módulo 3: Marca personal desde el día 1\nSesiones con tu Team Leader: documentación de personas físicas y morales; documentación de la propiedad e impuestos\nLos videos y documentos te los envía tu Team Leader.",
        'remember' => "Tus redes y tus contenidos deben estar terminados antes de avanzar.\nSolo promueves propiedades del inventario de la oficina.\nCada contacto queda anotado con su siguiente paso.",
        'questions' => "¿Qué publicaste esta semana y qué reacciones tuviste?\n¿Qué documentos te faltaban conocer y ya entiendes?\n¿A cuántas personas contactaste y qué aprendiste?",
        'routine_start_day' => 1,
        'metas' => ['contactos' => 10, 'tratos' => 0, 'analizadas' => 2, 'promovidas' => 5, 'citas' => 0],
        'acts' => [
            [1, 'Agenda en tu calendario los horarios de esta semana', $AGENDA_GUIDE, null, 'agenda'],
            [1, 'Revisa que tus páginas en redes y tus contenidos publicados estén terminados', 'Si tus páginas o tus contenidos no están terminados, no avanzas a la siguiente tarea. Escribe aquí los enlaces para que tu oficina los revise.', null, 'check'],
            [2, 'Sesión con tu Team Leader: documentación de personas físicas y personas morales', 'Sesión de 3 horas. Escribe aquí qué documentos no conocías.', 180, 'check'],
            [3, 'Ve el Módulo 3: Marca personal desde el día 1', 'Video de 3 horas.', 180, 'check'],
            [3, 'Haz el trabajo que indica el video y súbelo a Ariadna antes de terminar el día', 'Sube una foto o escribe el enlace de tu trabajo. Tu oficina lo ve en Ariadna.', null, 'foto'],
            [4, 'Sesión con tu Team Leader: documentación de la propiedad e impuestos', 'Sesión de 3 horas.', 180, 'check'],
            [4, 'Analiza 2 propiedades y llena tu registro', 'Para cada propiedad anota fecha, precio, comparables revisados y tu opinión. Súmalas en «Propiedades analizadas».', null, 'check'],
            [5, 'Habla con tu Team Leader y solicita las propiedades que vas a promover', 'Solo promueves propiedades del inventario de la oficina. Escribe aquí cuáles te asignaron.', null, 'check'],
            [5, 'Elige 5 propiedades del inventario de la oficina y publícalas en 20 grupos de Facebook y en tus redes', 'Anota en tu registro de promoción la fecha, la propiedad, la pieza, dónde la publicaste y los interesados. Súmalas en «Propiedades promovidas».', null, 'check'],
            [5, 'Asigna con tu Team Leader la fecha de tu Open House', 'Escribe aquí la fecha que acordaron.', null, 'check'],
            [5, 'Sube la foto de tus páginas llenas de Mi Mapa y de tu registro', $FOTO_GUIDE, null, 'foto'],
            [5, 'Revisa tus mínimos de la semana y responde tu repaso', 'Para continuar debes cumplir los tres mínimos (acumulado de las semanas 1 y 2): 20 o más contactos a tu círculo cercano, redes creadas con 3 contenidos y 5 propiedades de la oficina promovidas.', null, 'repaso'],
        ],
    ],
    3 => [
        'title' => 'Inventario y análisis de mercado',
        'objective' => 'Aprender a valorar propiedades, perfilar compradores y hacer tu primer Open House.',
        'material' => "Módulo 4: Open House\nSesiones con tu Team Leader: opinión de valor; inventario de la oficina y perfilamiento de compradores\nLos videos y documentos te los envía tu Team Leader.",
        'remember' => "Contacta a posibles compradores, no a otros asesores.\nPerfila al comprador antes de enseñarle propiedades.\nRegistra a cada asistente del Open House y dale seguimiento.",
        'questions' => "¿Cuántas personas asistieron a tu Open House y qué hiciste con ellas?\n¿Qué propiedad analizaste mejor y por qué?\n¿Qué te falta para agendar más citas?",
        'routine_start_day' => 1,
        'metas' => ['contactos' => 10, 'tratos' => 0, 'analizadas' => 5, 'promovidas' => 3, 'citas' => 1],
        'acts' => [
            [1, 'Agenda en tu calendario los horarios de esta semana', $AGENDA_GUIDE, null, 'agenda'],
            [1, 'Ve el Módulo 4: Open House', 'Video de 2 horas.', 120, 'check'],
            [1, 'Ayuda a organizar y promover el Open House; serás anfitrión', 'Escribe aquí qué te tocó hacer.', null, 'check'],
            [1, 'Acuerda con tu Team Leader la fecha de tu Open House de esta semana', 'Cuando lo realices, regístralo en «Mis logros» con el número de asistentes reales (mínimo 5).', null, 'check'],
            [2, 'Sesión con tu Team Leader: opinión de valor', 'Sesión de 3 horas. Usa el resumen de opinión de valor: propiedad, m², estado, 3 comparables, rango de valor y qué dijo el propietario.', 180, 'check'],
            [2, 'Analiza 2 propiedades y llena tu registro', 'Súmalas en «Propiedades analizadas».', null, 'check'],
            [3, 'Sesión con tu Team Leader: inventario de la oficina y perfilamiento de compradores', 'Sesión de 3 horas. Perfil del comprador: presupuesto real, motivo, tiempos, quién decide, quién tiene el recurso, beneficiario final, tipo de crédito, zonas y siguiente paso.', 180, 'check'],
            [3, 'Analiza 3 propiedades y llena tu registro', 'Súmalas en «Propiedades analizadas».', null, 'check'],
            [4, 'Invita y prospecta para el Open House', 'Registra a cada asistente con su celular, qué le interesó y el siguiente paso.', null, 'check'],
            [4, 'Promueve 3 propiedades en tus redes y en grupos', 'Bloque de 2 horas. Súmalas en «Propiedades promovidas».', 120, 'check'],
            [4, 'Agenda tu primera cita', 'Súmala en «Citas agendadas».', null, 'check'],
            [5, 'Toma 3 de las 5 propiedades y publícalas en tus redes y en 10 grupos', 'Bloque de 3 horas. Contacta posibles compradores, no a otros asesores.', 180, 'check'],
            [5, 'Analiza una ficha técnica y practica el teatro de ventas con un compañero', 'Escribe aquí qué aprendiste.', null, 'check'],
            [5, 'Sube la foto de tus páginas llenas de Mi Mapa y de tu registro', $FOTO_GUIDE, null, 'foto'],
            [5, 'Punto de control: responde tu repaso de la semana', 'Al cerrar tu Semana 3, Ariadna envía a tu oficina tu punto de control. Debes haber realizado tu Open House, tener al menos 1 propiedad captada con autorización del propietario o 1 cita agendada, y 50 o más puntos en tu evaluación.', null, 'repaso'],
        ],
    ],
    4 => [
        'title' => 'Tratos directos, formatos y procesos',
        'objective' => 'Salir a buscar propietarios por trato directo, conocer los formatos y procesos de la oficina y agendar citas.',
        'material' => "Módulo 5: Tratos directos y la primera cita\nSesiones con tu Team Leader: formatos internos; procesos; llamadas a tus tratos directos\nLos videos y documentos te los envía tu Team Leader.",
        'remember' => "La tarea de la semana es recorrer tu zona y buscar vendedores por trato directo.\nCada trato directo queda en tu registro con su siguiente paso.\nUna captación necesita ficha completa y acuerdo de promoción firmado.",
        'questions' => "¿A cuántos propietarios contactaste y cuál te interesó más?\n¿Qué formato de la oficina te costó más trabajo entender?\n¿Qué citas agendaste?",
        'routine_start_day' => 1,
        'metas' => ['contactos' => 10, 'tratos' => 6, 'analizadas' => 3, 'promovidas' => 0, 'citas' => 2],
        'acts' => [
            [1, 'Agenda en tu calendario los horarios de esta semana', $AGENDA_GUIDE, null, 'agenda'],
            [1, 'Ve el Módulo 5: Tratos directos y la primera cita', 'Video de 1 hora.', 60, 'check'],
            [1, 'Elige una zona y recórrela buscando propietarios que vendan por trato directo', 'Escribe aquí qué zona elegiste.', null, 'check'],
            [1, 'Registra cada propietario en tu registro de tratos directos', 'Anota fecha, dirección, contacto, qué dijo y siguiente paso. Súmalos en «Tratos directos».', null, 'check'],
            [2, 'Sesión con tu Team Leader: formatos internos de la oficina', 'Sesión de 2 horas.', 120, 'check'],
            [2, 'Continúa con tus tratos directos', 'Súmalos en «Tratos directos».', null, 'check'],
            [3, 'Sesión con tu Team Leader: procesos de la oficina', 'Sesión de 2 horas.', 120, 'check'],
            [3, 'Analiza 3 propiedades y llena tu registro', 'Súmalas en «Propiedades analizadas».', null, 'check'],
            [4, 'Sesión de llamadas con tu Team Leader o gerente a tus tratos directos', 'Sesión de 2 horas.', 120, 'check'],
            [4, 'Agenda tus citas', 'Súmalas en «Citas agendadas». Si captas una propiedad, llena la ficha de captación y regístrala en «Mis logros».', null, 'check'],
            [5, 'Reforzamiento con tu Team Leader en lo que más necesites', 'Escribe aquí en qué te reforzaron.', null, 'check'],
            [5, 'Sube la foto de tus páginas llenas de Mi Mapa y de tu registro', $FOTO_GUIDE, null, 'foto'],
            [5, 'Revisión semanal: responde tu repaso de la semana', 'Responde con tus palabras las preguntas del repaso. Tu oficina lo ve en Ariadna.', null, 'repaso'],
        ],
    ],
    5 => [
        'title' => 'Seguimiento, legal y cierre',
        'objective' => 'Dar seguimiento a todos tus prospectos, conocer el marco legal de la operación y cerrar el programa con tu evaluación.',
        'material' => "Módulo 6: Seguimiento sin parecer desesperado\nMódulo 7: Manejo de objeciones básicas\nSesiones con tu Team Leader: marco legal de la operación; llamadas a prospectos pendientes; simulacro de venta\nLos videos y documentos te los envía tu Team Leader.",
        'remember' => "Nunca dejes pasar más de una semana sin contactar a un prospecto activo.\nAntes de cualquier operación, valida con tu Team Leader lo legal.\nTu plan de los próximos 30 días se define el último día de la semana.",
        'questions' => "¿Qué prospectos siguen activos y cuál es el siguiente paso de cada uno?\n¿Qué objeción te cuesta más responder?\n¿Qué harás distinto en tus próximos 30 días?",
        'routine_start_day' => 1,
        'metas' => ['contactos' => 10, 'tratos' => 4, 'analizadas' => 0, 'promovidas' => 0, 'citas' => 2],
        'acts' => [
            [1, 'Agenda en tu calendario los horarios de esta semana', $AGENDA_GUIDE, null, 'agenda'],
            [1, 'Ve el Módulo 6: Seguimiento sin parecer desesperado', 'Video de 2 horas.', 120, 'check'],
            [1, 'Da seguimiento a los tratos directos que contactaste o visitaste y a los prospectos que generaste', 'Usa tu bitácora de seguimiento: fecha, con quién hablaste, qué se acordó y siguiente paso.', null, 'check'],
            [2, 'Sesión con tu Team Leader: marco legal de la operación', 'Sesión de 3 horas; puede ser grupal.', 180, 'check'],
            [3, 'Sesión de llamadas con tu Team Leader o gerente a tus prospectos y tratos directos pendientes', 'Sesión de 2 horas.', 120, 'check'],
            [4, 'Ve el Módulo 7: Manejo de objeciones básicas', 'Video de 2 horas.', 120, 'check'],
            [4, 'Sigue dando seguimiento a tus prospectos', 'Anota cada seguimiento en tu bitácora.', null, 'check'],
            [5, 'Simulacro de venta con tu Team Leader', 'Sesión de 3 horas.', 180, 'check'],
            [5, 'Sube la foto de tus páginas llenas de Mi Mapa y de tu registro', $FOTO_GUIDE, null, 'foto'],
            [5, 'Define con tu Team Leader tu plan de los próximos 30 días', 'Escribe aquí tu plan.', null, 'check'],
            [5, 'Evaluación final: responde tu repaso y revisa tu evaluación de 100 puntos', 'Ariadna calcula tu evaluación con todo lo que registraste en las 5 semanas.', null, 'repaso'],
        ],
    ],
];
