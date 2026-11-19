<?php
/**
 * EDALO · Plan inicial del curso Fast Track Industrial (4 semanas).
 * Basado en las Hojas del Asesor Semana 1 a 4, el Calendario de Videos, Mi Mapa Industrial,
 * el Impulsor Industrial y el Playbook del Team Leader, con los cambios aprobados:
 * 10 llamadas diarias, citas con el Team Leader y Reto después de cada video.
 * Los Videos 2, 3, 5, 6, 9 y 12 son los mismos de Residencial.
 */

$B = 'https://player.mediadelivery.net/play/811430/';

$RETO = fn(string $videos, string $extra = ''): array => ['k' => 'tl', 'm' => 30, 't' => "Reto $videos con tu Team Leader",
    'g' => "En cuanto termines, quédate en la oficina: tu Team Leader te hace las preguntas de tu Reto $videos$extra. No es para calificarte: es para asegurarnos de que dominas el tema. Escribe aquí en qué te pidió reforzar."];

$FOTO = ['k' => 'foto', 't' => 'Sube la foto de tus páginas llenas de Mi Mapa Industrial y de tu registro de llamadas',
    'g' => 'Toma foto de tus páginas llenas y súbela aquí. Tu Team Leader la ve en Edalo y así tienes respaldo de tu trabajo.'];

$REPASO = fn(string $t, string $g): array => ['k' => 'repaso', 't' => $t, 'g' => $g];

$CIERRE = fn(string $t, string $g): array => ['k' => 'tl', 'm' => 45, 't' => $t, 'g' => $g];

return [
    1 => [
        'title' => 'Prospectar y conocer el mercado comercial',
        'objective' => 'Empezar a prospectar locales, oficinas y bodegas chicas desde el primer día.',
        'material' => "Video 1: Mercado comercial e industrial\nVideo 2: Matemática de tu éxito\nVideo 3: Marca personal desde el día 1\nMi Mapa Industrial (documento aparte)\nImpulsor Industrial (documento aparte)",
        'remember' => "Una empresa decide con números, no con emoción.\nEmpezamos con locales, oficinas y bodegas porque se cierran más rápido y te dan ingresos mientras llega tu primera nave.\nLas 5 palabras: vacancia, absorción, plazo del contrato, depósito y clase A, B o C.",
        'questions' => "Menciona dos diferencias entre vender una casa y rentar un local.\n¿Por qué empezamos con locales y bodegas y no con naves?\n¿Qué te dice una vacancia alta sobre el mercado?",
        'routine_start_day' => 1,
        'metas' => ['llamadas' => 50, 'recorridos' => 2, 'fichas' => 2, 'cadenas' => 1, 'leads' => 1, 'piezas' => 1],
        'acts' => [
            ['d' => 1, 'k' => 'tl', 't' => 'Bienvenida: conoce tu oficina con tu Team Leader', 'm' => 60, 'g' => 'Ve a tu oficina. Tu Team Leader te presenta la oficina y a tu equipo, y ahí mismo agendan juntos tus horarios de trabajo. Escribe aquí lo más importante que aprendiste.'],
            ['d' => 1, 'k' => 'video', 't' => 'Ve el Video 1: Mercado comercial e industrial', 'm' => 45, 'v' => $B . '50837d23-dd13-49ae-b88c-467dbea7c5fc', 'g' => 'Pon atención a cómo decide cada tipo de cliente y a las 5 palabras que debes dominar.'],
            ['d' => 1, 'k' => 'video', 't' => 'Ve el Video 2: Matemática de tu éxito', 'm' => 60, 'v' => $B . '5d7691aa-7a9a-4d23-9ebf-a97b791f1ae5', 'g' => 'Al terminar, empieza de inmediato tu tarea en la oficina.'],
            ['d' => 1] + $RETO('de los Videos 1 y 2', ' (10 preguntas, unos 15 minutos)'),
            ['d' => 1, 'k' => 'check', 't' => 'Llena el Panorama de mi ciudad con tu Team Leader', 'g' => 'Zonas industriales principales, parques conocidos, corredores comerciales, renta promedio de nave y de local, vacancia aproximada, giros que más rentan, y la fuente y fecha del dato. Escribe aquí lo más importante.'],
            ['d' => 1, 'k' => 'check', 't' => 'Elige tu primera zona para recorrer', 'g' => 'Un parque industrial, un corredor o una colonia con bodegas y locales. Escribe aquí cuál elegiste.'],
            ['d' => 2, 'k' => 'check', 't' => 'Recorrido de zona 1: levanta fichas de letreros, construcciones y negocios saturados', 'm' => 120, 'g' => 'Busca letreros de «Se renta» o «Se vende», construcciones en proceso, negocios saturados y espacios vacíos. De cada hallazgo anota la ubicación, el tipo de inmueble, el teléfono o contacto y tu siguiente paso. Súmalo en «Recorridos de zona» y «Fichas de inmuebles levantadas».'],
            ['d' => 2, 'k' => 'foto', 't' => 'Toma fotos desde la calle', 'g' => 'No entres a propiedades privadas ni a obras sin que te inviten, y ve acompañado si no conoces la zona. Sube aquí una de tus fotos.'],
            ['d' => 3, 'k' => 'check', 't' => 'Revisa tu meta de ingresos en Mi Mapa Industrial', 'g' => 'Gastos anuales entre tu comisión promedio por operación: son las operaciones que necesitas al año; entre 12, las que necesitas al mes. Escribe aquí tus dos números.'],
            ['d' => 3, 'k' => 'check', 't' => 'Crea tu primera pieza (post o flyer) sobre una zona o parque que conociste', 'g' => 'Súmala en «Posts o flyers creados» y escribe aquí dónde la publicaste.'],
            ['d' => 4, 'k' => 'check', 't' => 'Recorrido de zona 2', 'm' => 120, 'g' => 'Súmalo en «Recorridos de zona» y anota cada hallazgo con su siguiente paso.'],
            ['d' => 4, 'k' => 'check', 't' => 'Inicia tu primera cadena de contacto en una obra pequeña', 'g' => 'Sube un escalón a la vez, con una sola pregunta en cada uno: albañil, encargado, arquitecto o residente, y dueño. Pregunta siempre en la entrada de la obra; no entres. Súmala en «Cadenas de contacto iniciadas».'],
            ['d' => 5, 'k' => 'video', 't' => 'Ve el Video 3: Marca personal desde el día 1', 'm' => 45, 'v' => $B . 'd660d59b-ae2e-4fea-9a75-e3b3f2f221aa', 'g' => 'Al terminar, revisa tu foto y tu biografía en tus redes.'],
            ['d' => 5, 'k' => 'check', 't' => 'Registra tus leads de la semana', 'g' => 'Solo cuenta un lead con nombre, contacto (celular o correo) y una necesidad concreta. «Quizá conoce a alguien» no cuenta. Súmalos en «Leads válidos».'],
            ['d' => 5] + $FOTO,
            ['d' => 5] + $REPASO('Revisión semanal conmigo: responde tu repaso', 'Responde con tus palabras. Tu Team Leader lo ve en Edalo.'),
            ['d' => 5] + $CIERRE('Revisión semanal con tu Team Leader', 'Ve a tu oficina y siéntate con tu Team Leader. Revisen juntos tus números de la semana, tu repaso conmigo y tu Reto del Video 3. Escribe aquí los acuerdos a los que llegaron.'),
        ],
    ],
    2 => [
        'title' => 'Captar y promover inmuebles comerciales',
        'objective' => 'Captar tu primer inmueble comercial y promoverlo.',
        'material' => "Video 4: Captar un local comercial\nVideo 5: Tratos directos y la primera cita\nVideo 6: Vitrina digital\nFormato de acuerdo de promoción (te lo da tu Team Leader)",
        'remember' => "Los 7 datos de la ficha: ubicación, tamaño, estado, servicios, precio y condiciones, propietario y documentos.\nLa comisión no se habla por teléfono: se acuerda con tu Team Leader.\nNunca pongas el nombre del propietario en una pieza de promoción, aunque te den permiso.",
        'questions' => "¿Cuáles son las tres preocupaciones del propietario?\nNombra cuatro de los siete datos de la ficha.\n¿Por qué no se habla de comisión por teléfono?",
        'routine_start_day' => 1,
        'metas' => ['llamadas' => 50, 'recorridos' => 2, 'fichas' => 3, 'cadenas' => 1, 'leads' => 1, 'piezas' => 2],
        'acts' => [
            ['d' => 1, 'k' => 'video', 't' => 'Ve el Video 4: Captar un local comercial', 'm' => 60, 'v' => $B . '73903675-fc17-4c4e-87a2-ae3b57750ff9', 'g' => 'La meta de tu primera visita es salir con la autorización del propietario para promover el inmueble.'],
            ['d' => 1, 'k' => 'video', 't' => 'Ve el Video 5: Tratos directos y la primera cita', 'm' => 60, 'v' => $B . '62c7f889-d763-4b24-9a40-cba08d383d0b', 'g' => 'Al terminar, empieza de inmediato tu tarea en la oficina.'],
            ['d' => 1] + $RETO('de los Videos 4 y 5', ' (10 preguntas, unos 15 minutos)'),
            ['d' => 1, 'k' => 'check', 't' => 'Practica el diálogo de captación con un compañero', 'g' => 'Confirmar: «¿Es usted el dueño del local?». Presentarte: «Mi nombre es (primer nombre), soy asesor inmobiliario». Captar la atención: «¿Puedo hacerle una pregunta sobre el local?». Explicar: «Trabajo con empresas que buscan espacio en esta zona y su local podría servirles». Cierre: «¿Me permite visitarlo hoy o mañana para conocerlo?».'],
            ['d' => 1, 'k' => 'check', 't' => 'Elige un local con letrero para visitar', 'g' => 'Escribe aquí cuál elegiste.'],
            ['d' => 2, 'k' => 'check', 't' => 'Visita a un propietario de local o bodega con letrero', 'm' => 90, 'g' => 'Usa tu diálogo de captación y propón tú el día y la hora.'],
            ['d' => 2, 'k' => 'check', 't' => 'Llena la ficha de captación', 'g' => 'Los 7 datos: ubicación, tamaño, estado, servicios, precio y condiciones, propietario y documentos, más el acuerdo de promoción firmado. Súmala en «Fichas de inmuebles levantadas». Si el propietario firmó el acuerdo, regístralo en «Mis logros».'],
            ['d' => 3, 'k' => 'video', 't' => 'Ve el Video 6: Vitrina digital', 'm' => 60, 'v' => $B . '42da8e49-59a5-45e1-ac92-9150fd57a4b8', 'g' => 'Al terminar, crea la pieza de tu inmueble captado.'],
            ['d' => 3] + $RETO('del Video 6'),
            ['d' => 3, 'k' => 'check', 't' => 'Promoción: crea y publica la pieza de tu inmueble captado', 'g' => 'Foto real, ubicación, m², tipo de inmueble y contacto. Solo inmuebles con acuerdo de promoción firmado y nunca con el nombre del propietario ni la comisión. Súmala en «Posts o flyers creados».'],
            ['d' => 4, 'k' => 'check', 't' => 'Recorrido de zona', 'm' => 120, 'g' => 'Súmalo en «Recorridos de zona».'],
            ['d' => 4, 'k' => 'check', 't' => 'Envía tu pieza a tus contactos del Impulsor', 'g' => 'Y a las empresas de tu lista de leads que busquen espacio.'],
            ['d' => 4, 'k' => 'check', 't' => 'Cadena de contacto en una obra', 'g' => 'Súmala en «Cadenas de contacto iniciadas».'],
            ['d' => 5, 'k' => 'check', 't' => 'Visitas y seguimiento a propietarios', 'g' => 'Escribe aquí con quién hablaste y el siguiente paso.'],
            ['d' => 5] + $FOTO,
            ['d' => 5] + $REPASO('Revisa tus mínimos conmigo y responde tu repaso', 'Para pasar a la Semana 3 debes cumplir, entre las semanas 1 y 2: 100 llamadas, 4 recorridos y 2 leads válidos.'),
            ['d' => 5] + $CIERRE('Revisa tus mínimos con tu Team Leader', 'Ve a tu oficina y siéntate con tu Team Leader. Revisen juntos tus números y tu filtro de la Semana 2: 100 llamadas, 4 recorridos y 2 leads válidos entre las semanas 1 y 2. Escribe aquí los acuerdos a los que llegaron.'),
        ],
    ],
    3 => [
        'title' => 'Calificar inquilinos, naves y números',
        'objective' => 'Aprender a calificar inquilinos y presentarles opciones, a leer y captar naves, y a calcular cuánto cuesta una renta.',
        'material' => "Video 7: Calificar al inquilino y presentar opciones\nVideo 8: Cómo leer una nave y captarla\nVideo 9: Seguimiento sin parecer desesperado\nVideo 10: Números sencillos",
        'remember' => "Califica al inquilino con 7 preguntas y pregunta siempre quién decide.\nPresenta máximo tres opciones.\nLos 6 datos clave de una nave: superficie, altura libre, andenes, piso, energía y ubicación logística.\nLos tres estados: disponible ya, en construcción o hecha a la medida.\nLas tres cuentas: renta mensual (m² × renta por m²), renta anual (× 12) y compromiso total (mensual × meses del plazo).\nCompara el costo mensual total, no solo la renta.",
        'questions' => "Menciona cuatro de las siete preguntas para calificar a un inquilino.\n¿Por qué se presentan máximo tres opciones?\n¿Qué le preguntas al cliente al final de una visita?\nMenciona cuatro de los seis datos clave de una nave.\n¿Por qué importan los andenes y la altura libre?\n¿En qué se diferencia una nave en construcción de una hecha a la medida?\nCalcula la renta mensual de 2,000 m² a US$5 por m² al mes.\n¿Por qué no basta comparar solo la renta entre dos naves?\n¿Qué dato le explicas al cliente, aparte de la renta mensual, para que entienda su compromiso?",
        'routine_start_day' => 1,
        'metas' => ['llamadas' => 50, 'recorridos' => 2, 'fichas' => 3, 'cadenas' => 2, 'leads' => 2, 'piezas' => 2],
        'acts' => [
            ['d' => 1, 'k' => 'video', 't' => 'Ve el Video 7: Calificar al inquilino y presentar opciones', 'm' => 45, 'v' => $B . 'cf1c1a46-d87b-47e1-a1a9-6835c390a3d7', 'g' => 'Al terminar, empieza de inmediato tu tarea en la oficina.'],
            ['d' => 1] + $RETO('del Video 7'),
            ['d' => 1, 'k' => 'check', 't' => 'Practica las 7 preguntas de calificación con un compañero', 'g' => 'Giro, m² necesarios y mínimos, zona, presupuesto de renta mensual, fecha de entrada, plazo, y quién decide y quién más aprueba.'],
            ['d' => 1, 'k' => 'check', 't' => 'Pide a tu Team Leader las cifras vigentes de tu ciudad', 'g' => 'Escribe aquí las que te dio.'],
            ['d' => 2, 'k' => 'video', 't' => 'Ve el Video 8: Cómo leer una nave y captarla', 'm' => 45, 'v' => $B . '60f49892-76b7-4c1e-b82f-fa5520fc17e0', 'g' => 'Al terminar, resuelve el caso de los 5,000 m² de tu hoja.'],
            ['d' => 2, 'k' => 'video', 't' => 'Ve el Video 9: Seguimiento sin parecer desesperado', 'm' => 60, 'v' => $B . '9975e8fc-a00f-442d-96ff-81aa9643a4a0', 'g' => 'Al terminar, da seguimiento a tus leads.'],
            ['d' => 2] + $RETO('de los Videos 8 y 9', ' (10 preguntas, unos 15 minutos)'),
            ['d' => 2, 'k' => 'check', 't' => 'Recorrido en un parque industrial o corredor: levanta la ficha de una nave', 'm' => 120, 'g' => 'Fotos desde la calle. Los 6 datos del local más superficie y terreno, altura libre, andenes, piso, energía, ubicación logística, estado, renta y propietario. Pregunta al área comercial quién la administra. Súmala en «Fichas de inmuebles levantadas» y «Recorridos de zona».'],
            ['d' => 3, 'k' => 'video', 't' => 'Ve el Video 10: Números sencillos', 'm' => 30, 'v' => $B . 'a06081b2-acbc-458c-b3cb-a07298728b17', 'g' => 'Al terminar, llena la calculadora de renta.'],
            ['d' => 3] + $RETO('del Video 10'),
            ['d' => 3, 'k' => 'check', 't' => 'Llena la calculadora de renta con tres inmuebles reales', 'g' => 'm², renta y mantenimiento por m², total mensual, anual, compromiso del contrato, depósito y moneda. Escribe aquí cuál conviene más y por qué.'],
            ['d' => 3, 'k' => 'check', 't' => 'Promoción: publica una pieza de un inmueble', 'g' => 'Súmala en «Posts o flyers creados».'],
            ['d' => 4, 'k' => 'check', 't' => 'Recorrido de zona industrial', 'm' => 120, 'g' => 'Súmalo en «Recorridos de zona».'],
            ['d' => 4, 'k' => 'check', 't' => 'Califica a un inquilino interesado con la Hoja de calificación y agenda su visita', 'g' => 'Las 7 preguntas, siempre con quién decide. Cuando hagas la visita con un inquilino calificado, regístrala en «Mis logros».'],
            ['d' => 4, 'k' => 'check', 't' => 'Prospecta una empresa que parezca necesitar más espacio', 'g' => '«Veo que su operación está creciendo. Ayudo a empresas a encontrar bodegas y locales más grandes. ¿Platicamos 15 minutos esta semana?»'],
            ['d' => 4, 'k' => 'check', 't' => 'Cadena de contacto en obra', 'g' => 'Súmala en «Cadenas de contacto iniciadas».'],
            ['d' => 5, 'k' => 'check', 't' => 'Resume tus resultados de la semana', 'g' => 'Escribe aquí tus leads, captaciones e inquilinos calificados.'],
            ['d' => 5] + $FOTO,
            ['d' => 5] + $REPASO('Punto de control conmigo: responde tu repaso', 'Al cerrar tu Semana 3 le envío a tu oficina tu punto de control.'),
            ['d' => 5] + $CIERRE('Punto de control con tu Team Leader', 'Ve a tu oficina y siéntate con tu Team Leader. Revisen tus resultados: 50 o más puntos y, al menos, 5 leads válidos, 1 captación autorizada o 1 inquilino calificado con visita. Definan qué negocios buscarás cerrar en la Semana 4. Escribe aquí los acuerdos.'),
        ],
    ],
    4 => [
        'title' => 'Oferta, negociación y cierre',
        'objective' => 'Llevar tus negocios desde la oferta hasta la firma y mantenerlos vivos.',
        'material' => "Video 11: Oferta, negociación y cierre\nVideo 12: Manejo de objeciones básicas\nCarta de intención: pide a tu Team Leader el formato que usa la oficina cuando tengas una oferta aceptada",
        'remember' => "Una oferta formal va por escrito, la presentas tú al propietario y lleva datos reales.\nEtapas: visita, oferta por escrito, negociación, documentos, contrato, firma y entrega.\nNegocia en paquete, no punto por punto.\nNo improvises en lo legal: lo revisas con el abogado y respondes ese mismo día.\nNunca dejes pasar más de una semana sin contacto.",
        'questions' => "Menciona los pasos de una renta, de la visita a la entrega.\n¿Qué debe llevar una oferta por escrito?\n¿Por qué se negocia en paquete y no punto por punto?\n¿Qué haces cuando el cliente te pregunta por una cláusula del contrato?",
        'routine_start_day' => 1,
        'metas' => ['llamadas' => 50, 'recorridos' => 2, 'fichas' => 2, 'cadenas' => 1, 'leads' => 1, 'piezas' => 1],
        'acts' => [
            ['d' => 1, 'k' => 'video', 't' => 'Ve el Video 11: Oferta, negociación y cierre', 'm' => 60, 'v' => $B . '7feac66c-706f-4efa-996c-ba5f46600717', 'g' => 'Al terminar, prepara tus ofertas.'],
            ['d' => 1] + $RETO('del Video 11'),
            ['d' => 1, 'k' => 'check', 't' => 'Si tienes un cliente interesado, prepara su oferta por escrito con el Formato de oferta', 'g' => 'Renta mensual propuesta, plazo, depósito y forma de pago, fecha de entrada y condiciones especiales. Cuando la presentes al propietario, regístrala en «Mis logros».'],
            ['d' => 1, 'k' => 'check', 't' => 'Revisa la Lista de cierre de cada negocio activo', 'g' => 'Carta de intención, documentos del inquilino, garantía con el abogado, contrato revisado, fecha de firma, fecha de entrega y visita de entrega.'],
            ['d' => 2, 'k' => 'video', 't' => 'Ve el Video 12: Manejo de objeciones básicas', 'm' => 30, 'v' => $B . 'de993d1f-ae70-4537-8ade-995d67197484', 'g' => 'Anota las objeciones que más te han hecho y cómo las vas a responder.'],
            ['d' => 2] + $RETO('del Video 12'),
            ['d' => 2, 'k' => 'check', 't' => 'Da seguimiento a todas tus ofertas y leads', 'g' => '«¿Qué necesita de mi parte para que lo cerremos antes del (fecha)?»'],
            ['d' => 2, 'k' => 'check', 't' => 'Actualiza tu bitácora de seguimiento', 'g' => 'Fecha, con quién hablaste, qué se acordó y siguiente paso.'],
            ['d' => 3, 'k' => 'check', 't' => 'Negocia con propietarios o inquilinos en proceso', 'g' => 'En paquete, no punto por punto. Escribe aquí cómo va cada negocio.'],
            ['d' => 3, 'k' => 'check', 't' => 'Promoción: publica una pieza', 'g' => 'Súmala en «Posts o flyers creados».'],
            ['d' => 4, 'k' => 'check', 't' => 'Recorrido de zona', 'm' => 120, 'g' => 'Súmalo en «Recorridos de zona».'],
            ['d' => 4, 'k' => 'check', 't' => 'Inicia la prospección de nuevas oportunidades', 'g' => 'Escribe aquí cuáles encontraste.'],
            ['d' => 5] + $FOTO,
            ['d' => 5, 'k' => 'check', 't' => 'Define tu plan de los próximos 30 días', 'g' => 'Escribe aquí tu plan. Lo revisas con tu Team Leader en tu evaluación final.'],
            ['d' => 5] + $REPASO('Evaluación final: responde tu repaso y revisa tu evaluación', 'Calculo tu evaluación con todo lo que registraste en las 4 semanas.'),
            ['d' => 5] + $CIERRE('Evaluación final con tu Team Leader', 'Ve a tu oficina y siéntate con tu Team Leader. Revisen tu evaluación de 100 puntos y definan juntos tu plan de los próximos 30 días. Escribe aquí los acuerdos.'),
        ],
    ],
];
