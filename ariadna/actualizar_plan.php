<?php
/**
 * EDALO · Actualización 7: carga el programa Fast Track Industrial (archivo de UN SOLO USO).
 * 1) Súbelo junto con los demás archivos.
 * 2) Entra como administrador y abre https://ariadna.oscarmarquez.com/actualizar_plan.php
 * 3) Revisa el resultado y BORRA este archivo.
 * Agrega a la base de datos lo que necesita Edalo (columnas nuevas y su plan de 4 semanas con sus 12 Retos).
 * No toca el plan de Ariadna, ni asesores, registros u oficinas. Si lo abres dos veces, no duplica nada.
 */
declare(strict_types=1);
require __DIR__ . '/inc/app.php';

enforce_https();
$u = require_user(['admin']);
ensure_schema();   // agrega las columnas de los dos programas (versión 4) si faltan
$PLAN = array (
  1 => 
  array (
    'title' => 'Prospectar y conocer el mercado comercial',
    'objective' => 'Empezar a prospectar locales, oficinas y bodegas chicas desde el primer día.',
    'material' => 'Video 1: Mercado comercial e industrial
Video 2: Matemática de tu éxito
Video 3: Marca personal desde el día 1
Mi Mapa Industrial (documento aparte)
Impulsor Industrial (documento aparte)',
    'remember' => 'Una empresa decide con números, no con emoción.
Empezamos con locales, oficinas y bodegas porque se cierran más rápido y te dan ingresos mientras llega tu primera nave.
Las 5 palabras: vacancia, absorción, plazo del contrato, depósito y clase A, B o C.',
    'questions' => 'Menciona dos diferencias entre vender una casa y rentar un local.
¿Por qué empezamos con locales y bodegas y no con naves?
¿Qué te dice una vacancia alta sobre el mercado?',
    'routine_start_day' => 1,
    'metas' => 
    array (
      'llamadas' => 50,
      'recorridos' => 2,
      'fichas' => 2,
      'cadenas' => 1,
      'leads' => 1,
      'piezas' => 1,
    ),
    'acts' => 
    array (
      0 => 
      array (
        'd' => 1,
        'k' => 'tl',
        't' => 'Bienvenida: conoce tu oficina con tu Team Leader',
        'm' => 60,
        'g' => 'Ve a tu oficina. Tu Team Leader te presenta la oficina y a tu equipo, y ahí mismo agendan juntos tus horarios de trabajo. Escribe aquí lo más importante que aprendiste.',
      ),
      1 => 
      array (
        'd' => 1,
        'k' => 'video',
        't' => 'Ve el Video 1: Mercado comercial e industrial',
        'm' => 45,
        'v' => 'https://player.mediadelivery.net/play/811430/50837d23-dd13-49ae-b88c-467dbea7c5fc',
        'g' => 'Pon atención a cómo decide cada tipo de cliente y a las 5 palabras que debes dominar.',
      ),
      2 => 
      array (
        'd' => 1,
        'k' => 'video',
        't' => 'Ve el Video 2: Matemática de tu éxito',
        'm' => 60,
        'v' => 'https://player.mediadelivery.net/play/811430/5d7691aa-7a9a-4d23-9ebf-a97b791f1ae5',
        'g' => 'Al terminar, empieza de inmediato tu tarea en la oficina.',
      ),
      3 => 
      array (
        'd' => 1,
        'k' => 'tl',
        'm' => 30,
        't' => 'Reto de los Videos 1 y 2 con tu Team Leader',
        'g' => 'En cuanto termines, quédate en la oficina: tu Team Leader te hace las preguntas de tu Reto de los Videos 1 y 2 (10 preguntas, unos 15 minutos). No es para calificarte: es para asegurarnos de que dominas el tema. Escribe aquí en qué te pidió reforzar.',
      ),
      4 => 
      array (
        'd' => 1,
        'k' => 'check',
        't' => 'Llena el Panorama de mi ciudad con tu Team Leader',
        'g' => 'Zonas industriales principales, parques conocidos, corredores comerciales, renta promedio de nave y de local, vacancia aproximada, giros que más rentan, y la fuente y fecha del dato. Escribe aquí lo más importante.',
      ),
      5 => 
      array (
        'd' => 1,
        'k' => 'check',
        't' => 'Elige tu primera zona para recorrer',
        'g' => 'Un parque industrial, un corredor o una colonia con bodegas y locales. Escribe aquí cuál elegiste.',
      ),
      6 => 
      array (
        'd' => 2,
        'k' => 'check',
        't' => 'Recorrido de zona 1: levanta fichas de letreros, construcciones y negocios saturados',
        'm' => 120,
        'g' => 'Busca letreros de «Se renta» o «Se vende», construcciones en proceso, negocios saturados y espacios vacíos. De cada hallazgo anota la ubicación, el tipo de inmueble, el teléfono o contacto y tu siguiente paso. Súmalo en «Recorridos de zona» y «Fichas de inmuebles levantadas».',
      ),
      7 => 
      array (
        'd' => 2,
        'k' => 'foto',
        't' => 'Toma fotos desde la calle',
        'g' => 'No entres a propiedades privadas ni a obras sin que te inviten, y ve acompañado si no conoces la zona. Sube aquí una de tus fotos.',
      ),
      8 => 
      array (
        'd' => 3,
        'k' => 'check',
        't' => 'Revisa tu meta de ingresos en Mi Mapa Industrial',
        'g' => 'Gastos anuales entre tu comisión promedio por operación: son las operaciones que necesitas al año; entre 12, las que necesitas al mes. Escribe aquí tus dos números.',
      ),
      9 => 
      array (
        'd' => 3,
        'k' => 'check',
        't' => 'Crea tu primera pieza (post o flyer) sobre una zona o parque que conociste',
        'g' => 'Súmala en «Posts o flyers creados» y escribe aquí dónde la publicaste.',
      ),
      10 => 
      array (
        'd' => 4,
        'k' => 'check',
        't' => 'Recorrido de zona 2',
        'm' => 120,
        'g' => 'Súmalo en «Recorridos de zona» y anota cada hallazgo con su siguiente paso.',
      ),
      11 => 
      array (
        'd' => 4,
        'k' => 'check',
        't' => 'Inicia tu primera cadena de contacto en una obra pequeña',
        'g' => 'Sube un escalón a la vez, con una sola pregunta en cada uno: albañil, encargado, arquitecto o residente, y dueño. Pregunta siempre en la entrada de la obra; no entres. Súmala en «Cadenas de contacto iniciadas».',
      ),
      12 => 
      array (
        'd' => 5,
        'k' => 'video',
        't' => 'Ve el Video 3: Marca personal desde el día 1',
        'm' => 45,
        'v' => 'https://player.mediadelivery.net/play/811430/d660d59b-ae2e-4fea-9a75-e3b3f2f221aa',
        'g' => 'Al terminar, revisa tu foto y tu biografía en tus redes.',
      ),
      13 => 
      array (
        'd' => 5,
        'k' => 'check',
        't' => 'Registra tus leads de la semana',
        'g' => 'Solo cuenta un lead con nombre, contacto (celular o correo) y una necesidad concreta. «Quizá conoce a alguien» no cuenta. Súmalos en «Leads válidos».',
      ),
      14 => 
      array (
        'd' => 5,
        'k' => 'foto',
        't' => 'Sube la foto de tus páginas llenas de Mi Mapa Industrial y de tu registro de llamadas',
        'g' => 'Toma foto de tus páginas llenas y súbela aquí. Tu Team Leader la ve en Edalo y así tienes respaldo de tu trabajo.',
      ),
      15 => 
      array (
        'd' => 5,
        'k' => 'repaso',
        't' => 'Revisión semanal conmigo: responde tu repaso',
        'g' => 'Responde con tus palabras. Tu Team Leader lo ve en Edalo.',
      ),
      16 => 
      array (
        'd' => 5,
        'k' => 'tl',
        'm' => 45,
        't' => 'Revisión semanal con tu Team Leader',
        'g' => 'Ve a tu oficina y siéntate con tu Team Leader. Revisen juntos tus números de la semana, tu repaso conmigo y tu Reto del Video 3. Escribe aquí los acuerdos a los que llegaron.',
      ),
    ),
  ),
  2 => 
  array (
    'title' => 'Captar y promover inmuebles comerciales',
    'objective' => 'Captar tu primer inmueble comercial y promoverlo.',
    'material' => 'Video 4: Captar un local comercial
Video 5: Tratos directos y la primera cita
Video 6: Vitrina digital
Formato de acuerdo de promoción (te lo da tu Team Leader)',
    'remember' => 'Los 7 datos de la ficha: ubicación, tamaño, estado, servicios, precio y condiciones, propietario y documentos.
La comisión no se habla por teléfono: se acuerda con tu Team Leader.
Nunca pongas el nombre del propietario en una pieza de promoción, aunque te den permiso.',
    'questions' => '¿Cuáles son las tres preocupaciones del propietario?
Nombra cuatro de los siete datos de la ficha.
¿Por qué no se habla de comisión por teléfono?',
    'routine_start_day' => 1,
    'metas' => 
    array (
      'llamadas' => 50,
      'recorridos' => 2,
      'fichas' => 3,
      'cadenas' => 1,
      'leads' => 1,
      'piezas' => 2,
    ),
    'acts' => 
    array (
      0 => 
      array (
        'd' => 1,
        'k' => 'video',
        't' => 'Ve el Video 4: Captar un local comercial',
        'm' => 60,
        'v' => 'https://player.mediadelivery.net/play/811430/73903675-fc17-4c4e-87a2-ae3b57750ff9',
        'g' => 'La meta de tu primera visita es salir con la autorización del propietario para promover el inmueble.',
      ),
      1 => 
      array (
        'd' => 1,
        'k' => 'video',
        't' => 'Ve el Video 5: Tratos directos y la primera cita',
        'm' => 60,
        'v' => 'https://player.mediadelivery.net/play/811430/62c7f889-d763-4b24-9a40-cba08d383d0b',
        'g' => 'Al terminar, empieza de inmediato tu tarea en la oficina.',
      ),
      2 => 
      array (
        'd' => 1,
        'k' => 'tl',
        'm' => 30,
        't' => 'Reto de los Videos 4 y 5 con tu Team Leader',
        'g' => 'En cuanto termines, quédate en la oficina: tu Team Leader te hace las preguntas de tu Reto de los Videos 4 y 5 (10 preguntas, unos 15 minutos). No es para calificarte: es para asegurarnos de que dominas el tema. Escribe aquí en qué te pidió reforzar.',
      ),
      3 => 
      array (
        'd' => 1,
        'k' => 'check',
        't' => 'Practica el diálogo de captación con un compañero',
        'g' => 'Confirmar: «¿Es usted el dueño del local?». Presentarte: «Mi nombre es (primer nombre), soy asesor inmobiliario». Captar la atención: «¿Puedo hacerle una pregunta sobre el local?». Explicar: «Trabajo con empresas que buscan espacio en esta zona y su local podría servirles». Cierre: «¿Me permite visitarlo hoy o mañana para conocerlo?».',
      ),
      4 => 
      array (
        'd' => 1,
        'k' => 'check',
        't' => 'Elige un local con letrero para visitar',
        'g' => 'Escribe aquí cuál elegiste.',
      ),
      5 => 
      array (
        'd' => 2,
        'k' => 'check',
        't' => 'Visita a un propietario de local o bodega con letrero',
        'm' => 90,
        'g' => 'Usa tu diálogo de captación y propón tú el día y la hora.',
      ),
      6 => 
      array (
        'd' => 2,
        'k' => 'check',
        't' => 'Llena la ficha de captación',
        'g' => 'Los 7 datos: ubicación, tamaño, estado, servicios, precio y condiciones, propietario y documentos, más el acuerdo de promoción firmado. Súmala en «Fichas de inmuebles levantadas». Si el propietario firmó el acuerdo, regístralo en «Mis logros».',
      ),
      7 => 
      array (
        'd' => 3,
        'k' => 'video',
        't' => 'Ve el Video 6: Vitrina digital',
        'm' => 60,
        'v' => 'https://player.mediadelivery.net/play/811430/42da8e49-59a5-45e1-ac92-9150fd57a4b8',
        'g' => 'Al terminar, crea la pieza de tu inmueble captado.',
      ),
      8 => 
      array (
        'd' => 3,
        'k' => 'tl',
        'm' => 30,
        't' => 'Reto del Video 6 con tu Team Leader',
        'g' => 'En cuanto termines, quédate en la oficina: tu Team Leader te hace las preguntas de tu Reto del Video 6. No es para calificarte: es para asegurarnos de que dominas el tema. Escribe aquí en qué te pidió reforzar.',
      ),
      9 => 
      array (
        'd' => 3,
        'k' => 'check',
        't' => 'Promoción: crea y publica la pieza de tu inmueble captado',
        'g' => 'Foto real, ubicación, m², tipo de inmueble y contacto. Solo inmuebles con acuerdo de promoción firmado y nunca con el nombre del propietario ni la comisión. Súmala en «Posts o flyers creados».',
      ),
      10 => 
      array (
        'd' => 4,
        'k' => 'check',
        't' => 'Recorrido de zona',
        'm' => 120,
        'g' => 'Súmalo en «Recorridos de zona».',
      ),
      11 => 
      array (
        'd' => 4,
        'k' => 'check',
        't' => 'Envía tu pieza a tus contactos del Impulsor',
        'g' => 'Y a las empresas de tu lista de leads que busquen espacio.',
      ),
      12 => 
      array (
        'd' => 4,
        'k' => 'check',
        't' => 'Cadena de contacto en una obra',
        'g' => 'Súmala en «Cadenas de contacto iniciadas».',
      ),
      13 => 
      array (
        'd' => 5,
        'k' => 'check',
        't' => 'Visitas y seguimiento a propietarios',
        'g' => 'Escribe aquí con quién hablaste y el siguiente paso.',
      ),
      14 => 
      array (
        'd' => 5,
        'k' => 'foto',
        't' => 'Sube la foto de tus páginas llenas de Mi Mapa Industrial y de tu registro de llamadas',
        'g' => 'Toma foto de tus páginas llenas y súbela aquí. Tu Team Leader la ve en Edalo y así tienes respaldo de tu trabajo.',
      ),
      15 => 
      array (
        'd' => 5,
        'k' => 'repaso',
        't' => 'Revisa tus mínimos conmigo y responde tu repaso',
        'g' => 'Para pasar a la Semana 3 debes cumplir, entre las semanas 1 y 2: 100 llamadas, 4 recorridos y 2 leads válidos.',
      ),
      16 => 
      array (
        'd' => 5,
        'k' => 'tl',
        'm' => 45,
        't' => 'Revisa tus mínimos con tu Team Leader',
        'g' => 'Ve a tu oficina y siéntate con tu Team Leader. Revisen juntos tus números y tu filtro de la Semana 2: 100 llamadas, 4 recorridos y 2 leads válidos entre las semanas 1 y 2. Escribe aquí los acuerdos a los que llegaron.',
      ),
    ),
  ),
  3 => 
  array (
    'title' => 'Calificar inquilinos, naves y números',
    'objective' => 'Aprender a calificar inquilinos y presentarles opciones, a leer y captar naves, y a calcular cuánto cuesta una renta.',
    'material' => 'Video 7: Calificar al inquilino y presentar opciones
Video 8: Cómo leer una nave y captarla
Video 9: Seguimiento sin parecer desesperado
Video 10: Números sencillos',
    'remember' => 'Califica al inquilino con 7 preguntas y pregunta siempre quién decide.
Presenta máximo tres opciones.
Los 6 datos clave de una nave: superficie, altura libre, andenes, piso, energía y ubicación logística.
Los tres estados: disponible ya, en construcción o hecha a la medida.
Las tres cuentas: renta mensual (m² × renta por m²), renta anual (× 12) y compromiso total (mensual × meses del plazo).
Compara el costo mensual total, no solo la renta.',
    'questions' => 'Menciona cuatro de las siete preguntas para calificar a un inquilino.
¿Por qué se presentan máximo tres opciones?
¿Qué le preguntas al cliente al final de una visita?
Menciona cuatro de los seis datos clave de una nave.
¿Por qué importan los andenes y la altura libre?
¿En qué se diferencia una nave en construcción de una hecha a la medida?
Calcula la renta mensual de 2,000 m² a US$5 por m² al mes.
¿Por qué no basta comparar solo la renta entre dos naves?
¿Qué dato le explicas al cliente, aparte de la renta mensual, para que entienda su compromiso?',
    'routine_start_day' => 1,
    'metas' => 
    array (
      'llamadas' => 50,
      'recorridos' => 2,
      'fichas' => 3,
      'cadenas' => 2,
      'leads' => 2,
      'piezas' => 2,
    ),
    'acts' => 
    array (
      0 => 
      array (
        'd' => 1,
        'k' => 'video',
        't' => 'Ve el Video 7: Calificar al inquilino y presentar opciones',
        'm' => 45,
        'v' => 'https://player.mediadelivery.net/play/811430/cf1c1a46-d87b-47e1-a1a9-6835c390a3d7',
        'g' => 'Al terminar, empieza de inmediato tu tarea en la oficina.',
      ),
      1 => 
      array (
        'd' => 1,
        'k' => 'tl',
        'm' => 30,
        't' => 'Reto del Video 7 con tu Team Leader',
        'g' => 'En cuanto termines, quédate en la oficina: tu Team Leader te hace las preguntas de tu Reto del Video 7. No es para calificarte: es para asegurarnos de que dominas el tema. Escribe aquí en qué te pidió reforzar.',
      ),
      2 => 
      array (
        'd' => 1,
        'k' => 'check',
        't' => 'Practica las 7 preguntas de calificación con un compañero',
        'g' => 'Giro, m² necesarios y mínimos, zona, presupuesto de renta mensual, fecha de entrada, plazo, y quién decide y quién más aprueba.',
      ),
      3 => 
      array (
        'd' => 1,
        'k' => 'check',
        't' => 'Pide a tu Team Leader las cifras vigentes de tu ciudad',
        'g' => 'Escribe aquí las que te dio.',
      ),
      4 => 
      array (
        'd' => 2,
        'k' => 'video',
        't' => 'Ve el Video 8: Cómo leer una nave y captarla',
        'm' => 45,
        'v' => 'https://player.mediadelivery.net/play/811430/60f49892-76b7-4c1e-b82f-fa5520fc17e0',
        'g' => 'Al terminar, resuelve el caso de los 5,000 m² de tu hoja.',
      ),
      5 => 
      array (
        'd' => 2,
        'k' => 'video',
        't' => 'Ve el Video 9: Seguimiento sin parecer desesperado',
        'm' => 60,
        'v' => 'https://player.mediadelivery.net/play/811430/9975e8fc-a00f-442d-96ff-81aa9643a4a0',
        'g' => 'Al terminar, da seguimiento a tus leads.',
      ),
      6 => 
      array (
        'd' => 2,
        'k' => 'tl',
        'm' => 30,
        't' => 'Reto de los Videos 8 y 9 con tu Team Leader',
        'g' => 'En cuanto termines, quédate en la oficina: tu Team Leader te hace las preguntas de tu Reto de los Videos 8 y 9 (10 preguntas, unos 15 minutos). No es para calificarte: es para asegurarnos de que dominas el tema. Escribe aquí en qué te pidió reforzar.',
      ),
      7 => 
      array (
        'd' => 2,
        'k' => 'check',
        't' => 'Recorrido en un parque industrial o corredor: levanta la ficha de una nave',
        'm' => 120,
        'g' => 'Fotos desde la calle. Los 6 datos del local más superficie y terreno, altura libre, andenes, piso, energía, ubicación logística, estado, renta y propietario. Pregunta al área comercial quién la administra. Súmala en «Fichas de inmuebles levantadas» y «Recorridos de zona».',
      ),
      8 => 
      array (
        'd' => 3,
        'k' => 'video',
        't' => 'Ve el Video 10: Números sencillos',
        'm' => 30,
        'v' => 'https://player.mediadelivery.net/play/811430/a06081b2-acbc-458c-b3cb-a07298728b17',
        'g' => 'Al terminar, llena la calculadora de renta.',
      ),
      9 => 
      array (
        'd' => 3,
        'k' => 'tl',
        'm' => 30,
        't' => 'Reto del Video 10 con tu Team Leader',
        'g' => 'En cuanto termines, quédate en la oficina: tu Team Leader te hace las preguntas de tu Reto del Video 10. No es para calificarte: es para asegurarnos de que dominas el tema. Escribe aquí en qué te pidió reforzar.',
      ),
      10 => 
      array (
        'd' => 3,
        'k' => 'check',
        't' => 'Llena la calculadora de renta con tres inmuebles reales',
        'g' => 'm², renta y mantenimiento por m², total mensual, anual, compromiso del contrato, depósito y moneda. Escribe aquí cuál conviene más y por qué.',
      ),
      11 => 
      array (
        'd' => 3,
        'k' => 'check',
        't' => 'Promoción: publica una pieza de un inmueble',
        'g' => 'Súmala en «Posts o flyers creados».',
      ),
      12 => 
      array (
        'd' => 4,
        'k' => 'check',
        't' => 'Recorrido de zona industrial',
        'm' => 120,
        'g' => 'Súmalo en «Recorridos de zona».',
      ),
      13 => 
      array (
        'd' => 4,
        'k' => 'check',
        't' => 'Califica a un inquilino interesado con la Hoja de calificación y agenda su visita',
        'g' => 'Las 7 preguntas, siempre con quién decide. Cuando hagas la visita con un inquilino calificado, regístrala en «Mis logros».',
      ),
      14 => 
      array (
        'd' => 4,
        'k' => 'check',
        't' => 'Prospecta una empresa que parezca necesitar más espacio',
        'g' => '«Veo que su operación está creciendo. Ayudo a empresas a encontrar bodegas y locales más grandes. ¿Platicamos 15 minutos esta semana?»',
      ),
      15 => 
      array (
        'd' => 4,
        'k' => 'check',
        't' => 'Cadena de contacto en obra',
        'g' => 'Súmala en «Cadenas de contacto iniciadas».',
      ),
      16 => 
      array (
        'd' => 5,
        'k' => 'check',
        't' => 'Resume tus resultados de la semana',
        'g' => 'Escribe aquí tus leads, captaciones e inquilinos calificados.',
      ),
      17 => 
      array (
        'd' => 5,
        'k' => 'foto',
        't' => 'Sube la foto de tus páginas llenas de Mi Mapa Industrial y de tu registro de llamadas',
        'g' => 'Toma foto de tus páginas llenas y súbela aquí. Tu Team Leader la ve en Edalo y así tienes respaldo de tu trabajo.',
      ),
      18 => 
      array (
        'd' => 5,
        'k' => 'repaso',
        't' => 'Punto de control conmigo: responde tu repaso',
        'g' => 'Al cerrar tu Semana 3 le envío a tu oficina tu punto de control.',
      ),
      19 => 
      array (
        'd' => 5,
        'k' => 'tl',
        'm' => 45,
        't' => 'Punto de control con tu Team Leader',
        'g' => 'Ve a tu oficina y siéntate con tu Team Leader. Revisen tus resultados: 50 o más puntos y, al menos, 5 leads válidos, 1 captación autorizada o 1 inquilino calificado con visita. Definan qué negocios buscarás cerrar en la Semana 4. Escribe aquí los acuerdos.',
      ),
    ),
  ),
  4 => 
  array (
    'title' => 'Oferta, negociación y cierre',
    'objective' => 'Llevar tus negocios desde la oferta hasta la firma y mantenerlos vivos.',
    'material' => 'Video 11: Oferta, negociación y cierre
Video 12: Manejo de objeciones básicas
Carta de intención: pide a tu Team Leader el formato que usa la oficina cuando tengas una oferta aceptada',
    'remember' => 'Una oferta formal va por escrito, la presentas tú al propietario y lleva datos reales.
Etapas: visita, oferta por escrito, negociación, documentos, contrato, firma y entrega.
Negocia en paquete, no punto por punto.
No improvises en lo legal: lo revisas con el abogado y respondes ese mismo día.
Nunca dejes pasar más de una semana sin contacto.',
    'questions' => 'Menciona los pasos de una renta, de la visita a la entrega.
¿Qué debe llevar una oferta por escrito?
¿Por qué se negocia en paquete y no punto por punto?
¿Qué haces cuando el cliente te pregunta por una cláusula del contrato?',
    'routine_start_day' => 1,
    'metas' => 
    array (
      'llamadas' => 50,
      'recorridos' => 2,
      'fichas' => 2,
      'cadenas' => 1,
      'leads' => 1,
      'piezas' => 1,
    ),
    'acts' => 
    array (
      0 => 
      array (
        'd' => 1,
        'k' => 'video',
        't' => 'Ve el Video 11: Oferta, negociación y cierre',
        'm' => 60,
        'v' => 'https://player.mediadelivery.net/play/811430/7feac66c-706f-4efa-996c-ba5f46600717',
        'g' => 'Al terminar, prepara tus ofertas.',
      ),
      1 => 
      array (
        'd' => 1,
        'k' => 'tl',
        'm' => 30,
        't' => 'Reto del Video 11 con tu Team Leader',
        'g' => 'En cuanto termines, quédate en la oficina: tu Team Leader te hace las preguntas de tu Reto del Video 11. No es para calificarte: es para asegurarnos de que dominas el tema. Escribe aquí en qué te pidió reforzar.',
      ),
      2 => 
      array (
        'd' => 1,
        'k' => 'check',
        't' => 'Si tienes un cliente interesado, prepara su oferta por escrito con el Formato de oferta',
        'g' => 'Renta mensual propuesta, plazo, depósito y forma de pago, fecha de entrada y condiciones especiales. Cuando la presentes al propietario, regístrala en «Mis logros».',
      ),
      3 => 
      array (
        'd' => 1,
        'k' => 'check',
        't' => 'Revisa la Lista de cierre de cada negocio activo',
        'g' => 'Carta de intención, documentos del inquilino, garantía con el abogado, contrato revisado, fecha de firma, fecha de entrega y visita de entrega.',
      ),
      4 => 
      array (
        'd' => 2,
        'k' => 'video',
        't' => 'Ve el Video 12: Manejo de objeciones básicas',
        'm' => 30,
        'v' => 'https://player.mediadelivery.net/play/811430/de993d1f-ae70-4537-8ade-995d67197484',
        'g' => 'Anota las objeciones que más te han hecho y cómo las vas a responder.',
      ),
      5 => 
      array (
        'd' => 2,
        'k' => 'tl',
        'm' => 30,
        't' => 'Reto del Video 12 con tu Team Leader',
        'g' => 'En cuanto termines, quédate en la oficina: tu Team Leader te hace las preguntas de tu Reto del Video 12. No es para calificarte: es para asegurarnos de que dominas el tema. Escribe aquí en qué te pidió reforzar.',
      ),
      6 => 
      array (
        'd' => 2,
        'k' => 'check',
        't' => 'Da seguimiento a todas tus ofertas y leads',
        'g' => '«¿Qué necesita de mi parte para que lo cerremos antes del (fecha)?»',
      ),
      7 => 
      array (
        'd' => 2,
        'k' => 'check',
        't' => 'Actualiza tu bitácora de seguimiento',
        'g' => 'Fecha, con quién hablaste, qué se acordó y siguiente paso.',
      ),
      8 => 
      array (
        'd' => 3,
        'k' => 'check',
        't' => 'Negocia con propietarios o inquilinos en proceso',
        'g' => 'En paquete, no punto por punto. Escribe aquí cómo va cada negocio.',
      ),
      9 => 
      array (
        'd' => 3,
        'k' => 'check',
        't' => 'Promoción: publica una pieza',
        'g' => 'Súmala en «Posts o flyers creados».',
      ),
      10 => 
      array (
        'd' => 4,
        'k' => 'check',
        't' => 'Recorrido de zona',
        'm' => 120,
        'g' => 'Súmalo en «Recorridos de zona».',
      ),
      11 => 
      array (
        'd' => 4,
        'k' => 'check',
        't' => 'Inicia la prospección de nuevas oportunidades',
        'g' => 'Escribe aquí cuáles encontraste.',
      ),
      12 => 
      array (
        'd' => 5,
        'k' => 'foto',
        't' => 'Sube la foto de tus páginas llenas de Mi Mapa Industrial y de tu registro de llamadas',
        'g' => 'Toma foto de tus páginas llenas y súbela aquí. Tu Team Leader la ve en Edalo y así tienes respaldo de tu trabajo.',
      ),
      13 => 
      array (
        'd' => 5,
        'k' => 'check',
        't' => 'Define tu plan de los próximos 30 días',
        'g' => 'Escribe aquí tu plan. Lo revisas con tu Team Leader en tu evaluación final.',
      ),
      14 => 
      array (
        'd' => 5,
        'k' => 'repaso',
        't' => 'Evaluación final: responde tu repaso y revisa tu evaluación',
        'g' => 'Calculo tu evaluación con todo lo que registraste en las 4 semanas.',
      ),
      15 => 
      array (
        'd' => 5,
        'k' => 'tl',
        'm' => 45,
        't' => 'Evaluación final con tu Team Leader',
        'g' => 'Ve a tu oficina y siéntate con tu Team Leader. Revisen tu evaluación de 100 puntos y definan juntos tu plan de los próximos 30 días. Escribe aquí los acuerdos.',
      ),
    ),
  ),
);

$RETOS = array (
  'Ve el Video 1: Mercado comercial e industrial' => '¿Cómo decide un cliente residencial, uno comercial y uno industrial? | El residencial, con el corazón: se enamora de la casa. El comercial, con corazón y con números. El industrial, solo con números: es una decisión fría, la toman varias personas, casi siempre es renta. | 2-5
Para una familia el inmueble es su patrimonio. ¿Qué es para una empresa? | Una herramienta de trabajo. Le debe servir para operar. | 6
¿Cuáles son los 5 tipos de inmuebles que vas a trabajar y con cuál empiezas? | Los 5 tipos: local comercial, oficina, bodega, nave industrial y terreno. Se empieza con locales y oficinas, porque es lo que da ingresos más rápido, en los primeros 30 días. | 6-10
¿A quién puedes representar, y por qué preguntas siempre el «para qué» de lo que pide el cliente? | Puedes representar al propietario (captas el inmueble) o al comprador o inquilino (le buscas opciones), o a los dos para hacer match. El «para qué» importa porque necesitas entender su necesidad real, no solo lo que pide. Por ejemplo, el terreno cerca del aeropuerto, pero con agua para refrigerar. | 10-13
¿Qué significan vacancia, absorción, plazo del contrato, depósito y clase A, B o C? | Vacancia: el % de espacios vacíos en una zona. Absorción: el espacio que se rentó en un periodo; dice si el mercado se mueve. Plazo: cuántos años renta el inquilino; en industrial, 10, 15 o 20 años. Depósito: los meses de renta que se dan en garantía. Clase A, B o C: la calidad del inmueble y su ubicación. | 12-17',
  'Ve el Video 2: Matemática de tu éxito' => '¿Por qué decimos que tu trabajo no es vender casas? | Tu trabajo es conseguir personas. Sin prospectos no hay ventas: un médico sin pacientes es solo alguien con bata blanca. | 4
¿Cómo calculas en Mi Mapa cuántos lados necesitas al año? | Gastos del mes × 12 = gasto anual. Ese número entre tu comisión promedio (lo que entra a tu bolsillo por una venta) te da los lados al año. | 8-13
¿Por qué el 70 % de tus lados debe ser del lado del propietario? | Con la propiedad, los compradores te siguen, puedes llevarte los dos lados y ganas estabilidad y experiencia real (no «5 veces el primer año»). | 15-17
¿Qué cuenta como un contacto y cuál es el número más importante de tu Mi Mapa? | Decir uno de los diálogos, no solo saludar. La letra H, tus contactos diarios (6 días a la semana): tu número mínimo de supervivencia. | 20-23
¿Cuáles son los 5 pasos del diálogo y qué haces si alguien te dice que quiere vender? | Confirmar, presentarte, captar la atención («necesito pedirte algo y no es dinero»), explicar y cerrar («¿conoces a alguien que quiera comprar, vender o rentar?»). Si quiere vender, le propones sentarse a platicar y pides que alguien de tu oficina te acompañe. | 27-35',
  'Ve el Video 3: Marca personal desde el día 1' => '¿Cuánto tiempo tienes para la primera impresión digital y en qué se fija la gente? | Menos de 10 segundos: tu foto, tu nombre y tu biografía. Un perfil descuidado es peor que no tener perfil. | 0-4
¿Cómo debe ser tu foto de perfil y cuáles no funcionan? | Ropa de negocios, buena luz, sonrisa genuina, fondo limpio, actual y la misma en todas tus redes. No: playa, boda, mascota, hijos, lentes de sol o fotos viejas. | 3-7
¿Qué 3 preguntas responde tu biografía? | Quién eres (asesor inmobiliario en tu ciudad o zona), a quién ayudas (tu avatar o especialidad) y cómo te contactan (tu número o enlace de WhatsApp, obligatorio). | 7-13
¿Por qué conviene especializarte y cuáles son los 3 nichos? | Te vuelves el referente de esa zona o ese tipo de cliente. Los nichos son salud, dinero y relaciones. | 8-12
¿Qué cuidas en tu comportamiento en redes? | Publicar todos los días (puedes programar con Meta Business Suite), la regla 3 + 1, nada de política, peleas ni quejas, cuidar tu perfil personal porque te investigan y responder en minutos. | 13-20',
  'Ve el Video 4: Captar un local comercial' => '¿Cuál es la meta de tu primera visita y por qué conviene captar primero en vez de buscar inquilinos? | La meta es salir de la primera visita con la autorización del propietario para promover el inmueble y cobrar comisión. Conviene captar primero porque el inquilino busca al que tiene el inmueble: «sin inmueble no hay negocio». Además, la captación vale 15 puntos. | 0-4
¿Cuáles son las tres preocupaciones del propietario y qué le ofreces para cada una? | Que siga vacío: le ofreces más interesados. Un inquilino que no pague: le ofreces un filtro de inquilinos. No saber cuánto cobrar: le ofreces un precio sustentado por el mercado. | 5-12
Menciona los 7 datos que levantas del inmueble. | Ubicación, tamaño (m² totales y utilizables), estado, servicios, precio y condiciones (renta, depósito, meses de gracia, plazo), propietario (contacto y quién decide) y documentos (uso de suelo, quién firma). | 12-21
¿Cuáles son los 5 pasos del diálogo de captación? | Confirmar que hablas con el dueño o el encargado. Presentarte con tu primer nombre y que eres asesor inmobiliario. «¿Puedo hacerle una pregunta?» «Trabajo con empresas que buscan espacio en esta zona y su local podría servirles.» Proponer tú el día y la hora de la visita. | 26-31
¿Qué contestas si te preguntan tu comisión por teléfono, y qué necesitas firmado? | Por teléfono: «Nuestra comisión es negociable; cuando nos veamos la platicamos y el número que acordemos, por ese trabajamos». La comisión nunca se habla por teléfono; la defines con tu Team Leader. Firmado: el acuerdo de promoción, que incluye la comisión. | 31-36',
  'Ve el Video 5: Tratos directos y la primera cita' => '¿Qué es un trato directo y dónde lo encuentras? | Un propietario que vende por su cuenta, sin asesor, y al que tú buscas primero. En letreros y lonas, portales (filtro «trato directo»), Marketplace, grupos de compraventa de Facebook y tu propio vecindario. | 3-8
¿Cuáles son los 5 pasos del diálogo de trato directo? | Confirmar («¿eres el dueño de la casa en venta en…?»), presentarte (primer nombre y asesor inmobiliario), captar la atención («tengo una pregunta» y una pausa breve), explicar («¿te interesaría mostrar tu propiedad a un cliente de mi inmobiliaria?») y cerrar la cita proponiendo tú el día y la hora. | 8-16
¿Qué contestas si al teléfono te pone condiciones («no firmo exclusiva», «no pago comisión»)? | «No hay problema, nos vemos hoy a las 5». El objetivo es entrar a la casa, no discutir por teléfono. | 16-18
¿Cómo te preparas para la cita y qué preguntas haces ahí? | Investigas la zona y los precios de propiedades parecidas (datos, no opiniones) y llegas 5 minutos antes. Preguntas: cuánto tiempo lleva en venta (en promedio 6 semanas), si ha tenido visitas y qué le dijeron, por qué vende por su cuenta, qué tan pronto necesita vender, si trabaja con otros asesores y si tiene un plan de ventas. | 21-34
¿Qué es hablar de resultados y no de servicios, y con qué terminas siempre la cita? | En vez de «publicamos en portales», dices «tenemos clientes buscando este tipo de casa en esta zona». Nunca te vas sin el siguiente paso: volver a tomar fotos en 1 a 3 días, presentar tu plan de ventas, una llamada con fecha y hora, o una cita con alguien de tu oficina y con todos los que deciden. | 33-40',
  'Ve el Video 6: Vitrina digital' => '¿Por qué las redes son tu «oficina pública» y cada cuándo publicas? | Son trabajo, no entretenimiento: la herramienta de marketing más poderosa y gratis (cuesta tiempo y esfuerzo). Publicas todos los días, no cuando tengas ganas. | 3-6
¿Qué diferencia hay entre Facebook, Instagram, TikTok y el estatus de WhatsApp? | Facebook: mayores de 30 con poder de compra; perfil, página de negocios y grupos, con foto, precio, zona y contacto. Instagram: visual, que se imaginen viviendo ahí. TikTok: alcance orgánico, historia y atrapar en 3 segundos. Estatus de WhatsApp Business: gente que ya confía en ti; mínimo una propiedad o contenido al día. | 6-14
¿Cuáles son las 4 partes del post perfecto? | Gancho (detiene el scroll), descripción que haga soñar con 3 o 4 datos clave, precio (obligatorio en México; filtra y atrae) y llamado a la acción específico. | 15-24
¿Por qué el llamado a la acción es «Comenta me interesa» y no tu número de WhatsApp? | La interacción hace que la red siga mostrando tu post. Si solo te llaman, la red cree que no funcionó y lo deja de promover. | 22-24
¿Por qué una de tus 3 propiedades debe ser renta y qué es la regla 3 + 1? | La renta tiene un ciclo corto (unos 30 días), contactos inmediatos, práctica sin presión y flujo de ingresos; el inquilino de hoy es el comprador de mañana. Regla 3 + 1: por cada 3 propiedades, 1 contenido de valor. | 24-37',
  'Ve el Video 7: Calificar al inquilino y presentar opciones' => '¿Cómo distingues a un cliente de un curioso? | Por su motivación, que se mide con la interacción. Si responde y pregunta, está interesado. Si te deja en visto durante días, no. | 0-3
Una renta se cierra solo si coinciden tres cosas. ¿Cuáles son? | Un inmueble que le funcione al inquilino, un inquilino que pueda pagar y un propietario que acepte la operación. Se verifican desde el principio, no al final. | 3-5
Menciona las 7 preguntas para calificar al inquilino. ¿Cuál es la que más se omite? | ¿A qué giro se dedica? ¿Cuántos m² necesita y cuál es su mínimo? ¿En qué zona, y por qué? ¿Qué presupuesto de renta mensual tiene? ¿Para cuándo necesita entrar, y por qué? ¿Por cuántos años va a rentar? ¿Quién decide y quién más aprueba? La que más se omite es la 7, y es la que más dinero cuesta. | 4-13
¿Por qué presentas máximo tres opciones y cómo las presentas? | Más de tres confunde y menos de tres no le deja comparar (como pasa con Netflix). Le explicas por qué escogiste cada opción y cómo le beneficia. | 13-16
¿Qué haces antes, durante y al final de la visita? | Antes: llegas primero y revisas que todo abra y funcione. Durante: dejas que recorran y pregunten, y te fijas en lo que les llama la atención. Al final: «¿Cuál les acomoda más y qué le faltó?» | 16-21',
  'Ve el Video 8: Cómo leer una nave y captarla' => '¿Para qué se renta una nave y por qué dos naves del mismo tamaño no le sirven igual a una empresa? | Se renta para la operación del negocio, es decir, su logística. Cada operación necesita cierta altura, ciertos andenes, cierta energía y ciertas instalaciones, así que dos naves del mismo tamaño pueden servir para cosas distintas. | 0-3
¿Cuáles son los 6 datos clave de una nave? | Superficie (de terreno y de construcción), altura libre, andenes o puertas de carga, piso (la resistencia para racks y maquinaria), energía (kVA) y ubicación logística. | 2-7
¿Qué diferencia hay entre una nave clase A, B y C? | A: la más moderna y de mayor especificación; casi siempre está en un parque industrial con vigilancia. B: buena, con especificaciones más sencillas. C: la más sencilla o antigua. Hay clientes para las tres. | 6-8
¿Cuáles son los tres estados de una nave? ¿En qué se diferencia una en construcción de una hecha a la medida? | Los tres estados son: disponible ya, en construcción o hecha a la medida (BTS, build to suit). La que está en construcción se renta antes de terminarla. La hecha a la medida se construye según lo que pide el cliente. | 8-11
Caso de los 5,000 m² (distribuye refacciones y recibe 5 camiones al día): ¿qué nave presentas, cuál descartas y por qué? | Presentas la A: 5,200 m², 12 m de altura, 6 andenes y acceso a carretera. La C, solo si le dan los tiempos: está en construcción. Descartas la B: 2 andenes no alcanzan para 5 camiones al día. | 17-21',
  'Ve el Video 9: Seguimiento sin parecer desesperado' => '¿Por qué se muere el seguimiento y qué dice la estadística? | Por el miedo a molestar. El 48 % abandona después del primer intento y el 80 % de las ventas ocurre después del quinto contacto. Dar seguimiento es estar presente cuando el cliente decide. | 0-4
¿Cuáles son los 3 elementos del sistema y qué datos lleva tu lista? | Lista, frecuencia y pretexto de valor. La lista lleva nombre, teléfono, fuente, qué busca, último contacto con notas y fechas especiales (en WhatsApp Business con etiquetas, una hoja de Google o Excel, o el CRM de tu oficina). | 4-11
¿Cada cuándo das seguimiento a un prospecto caliente, uno tibio y uno frío? | Caliente: cada 1 a 5 días. Tibio: una vez por semana. Frío: 2 o 3 veces al mes, con redes, fechas especiales y novedades del mercado. | 11-18
¿Por qué no preguntas «¿ya se decidieron?» y cómo es un buen mensaje? | Esa pregunta da por hecho que ya están listos. Un buen mensaje lleva un pretexto de valor (propiedad nueva que le queda, precios de su zona, dato de impuestos), es corto, lleva su nombre y termina con una pregunta. Las llamadas, para los calientes y los momentos clave. | 18-29
¿Cuándo y cómo sueltas a un prospecto con elegancia? | Después de 5 o 6 contactos bien espaciados y con valor, sin respuesta. Mensaje de cierre: «Entiendo que quizá no es el momento… aquí voy a estar». Quita presión y muchos contestan; sigue en tu lista de distribución. | 31-36',
  'Ve el Video 10: Números sencillos' => '¿Cuáles son las tres cuentas que siempre presentas? | Renta mensual: m² × renta por m². Renta anual: la mensual × 12. Compromiso total: la mensual × los meses del plazo. | 2-4
Calcula la renta mensual de 2,000 m² a US$5 por m² al mes. | US$10,000 al mes. | 15-17
¿Por qué nunca comparas solo la renta entre dos naves? | Porque el mantenimiento y lo que se paga aparte cambian el costo real. Por ejemplo, la nave B tiene la renta más barata, pero con su mantenimiento cuesta igual que la A. Se compara el costo mensual total. | 8-11
¿Qué preguntas siempre al propietario además de la renta? | Qué incluye la renta y qué se paga aparte: mantenimiento, seguro del inmueble, servicios, depósito y garantías. | 6-8
¿En qué moneda se rentan casi siempre las naves y qué cuidas al hablar de dinero? | Casi siempre en dólares. El tipo de cambio del día es solo una referencia: nunca prometes una cifra en moneda local. Al propietario le presentas tres comparables que te da tu Team Leader. | 11-14',
  'Ve el Video 11: Oferta, negociación y cierre' => '¿Cuáles son las etapas de una renta, de la visita a la entrega? | Interés y visita, oferta por escrito, negociación, documentos del inquilino, contrato revisado por los abogados, y firma, depósito y entrega. | 0-4
¿Qué debe llevar una oferta por escrito? | Renta mensual propuesta, plazo, depósito y forma de pago (de preferencia por transferencia), fecha de entrada y condiciones especiales (mejoras, meses de gracia y uso). | 4-8
¿Por qué se negocia en paquete y no punto por punto? | Porque lo que se cede en un punto se compensa en otro: renta, plazo, gracia y mejoras, fecha de entrada y garantías. La meta es ganar-ganar. | 10-15
¿Qué haces si el cliente te pregunta por una cláusula del contrato? | No improvisas. Lo revisas con el abogado y respondes ese mismo día. Antes de mandar el contrato, verificas que la renta, el plazo, las fechas, los nombres y los m² coincidan. | 19-22
¿Cómo cuidas la entrega y cómo mantienes vivo el negocio mientras deciden? | La entrega: haces un recorrido antes de firmar y otro en la entrega, con fotos y video. Mientras deciden: nunca pasas más de 7 días sin contacto, dejas siempre fecha para la siguiente plática y resumes por escrito lo acordado y lo que falta. | 22-29',
  'Ve el Video 12: Manejo de objeciones básicas' => '¿Qué es en realidad una objeción? | No es un no: es una pregunta disfrazada, «todavía no me has dado suficiente razón». Es una puerta entreabierta. | 2-4
¿Qué contestas a «lo voy a pensar»? | No dices «claro, tómese su tiempo» y te vas: es una cortina de humo. Validas y preguntas: «¿Qué es lo que te genera más dudas: el precio, la ubicación, el proceso, la comisión o la exclusiva?». | 3-6
¿Y a «está muy caro»? | No lo tomas personal: «Entiendo que el precio es importante, ¿con qué lo estás comparando?». Si es parecido, averiguas qué ofrece la competencia; si no, explicas la diferencia con datos. Terminas con «¿qué precio te haría sentir que es una buena decisión?». | 6-8
¿Y a «ya tengo asesor»? | Preguntas si ya firmó exclusiva. Si no, le ofreces otra perspectiva (tu plan de ventas). Si ya firmó, la respetas y te retiras con clase, sin hablar mal del otro asesor. | 8-10
¿Cómo respondes a «no es el momento» y a «lo tengo que platicar con mi pareja»? ¿Cuáles son las 3 reglas de oro? | «¿Qué tendría que pasar para que fuera el momento?» y sigues con seguimiento de valor. «¿Cuándo podemos reunirnos los tres?». Reglas de oro: no interrumpir, validar antes de responder y practicar en voz alta. | 10-13',
);

$log = [];
$prog = 'industrial';
if ((int)val('SELECT COUNT(*) FROM plan_weeks WHERE program = ?', [$prog]) > 0) {
    $log[] = 'El plan de Edalo ya estaba cargado: no se cambió nada.';
} else {
    $nw = 0; $na = 0; $nr = 0;
    foreach ($PLAN as $w => $p) {
        $cols = ['program', 'week_no', 'title', 'objective', 'material', 'remember', 'review_questions', 'routine_start_day'];
        $vals = [$prog, $w, $p['title'], $p['objective'], $p['material'], $p['remember'], $p['questions'], $p['routine_start_day']];
        foreach ($p['metas'] as $f => $v) { $cols[] = 'meta_' . $f; $vals[] = (int)$v; }
        q('INSERT INTO plan_weeks (' . implode(', ', $cols) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')', $vals);
        $nw++;
        $sort = [];
        foreach ($p['acts'] as $a) {
            $d = (int)$a['d'];
            $sort[$d] = ($sort[$d] ?? 0) + 10;
            $help = '';
            foreach ($a['h'] ?? [] as [$t, $url]) $help .= $t . ' | ' . $url . "\n";
            $reto = $a['k'] === 'video' ? ($RETOS[$a['t']] ?? null) : null;
            if ($reto) $nr++;
            q('INSERT INTO plan_activities (program, week_no, day_no, sort, title, guide, minutes, kind, video_url, help_links, reto) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                [$prog, $w, $d, $sort[$d], $a['t'], $a['g'] ?? '', $a['m'] ?? null, $a['k'], $a['v'] ?? null, trim($help) ?: null, $reto]);
            $na++;
        }
    }
    $log[] = "Plan de Edalo cargado: $nw semanas, $na actividades y $nr videos con su Reto.";
}
if (val('SELECT v FROM settings WHERE k = ?', ['routine_items_industrial']) === null) {
    set_setting('routine_items_industrial', setting_defaults()['routine_items_industrial']);
    $log[] = 'Rutina diaria de Edalo: 10 llamadas del Impulsor Industrial.';
}
$log[] = 'Base de datos en la versión ' . h((string)setting('schema_version')) . '.';
$log[] = (string)cfg('edalo_url', '') !== '' ? 'Dirección de Edalo: ' . h((string)cfg('edalo_url')) . '.'
    : 'Falta agregar la dirección de Edalo en config.php (edalo_url). Mientras tanto, los enlaces usan la de Ariadna.';
$log[] = !empty(cfg('mail_edalo.from')) ? 'Correo de Edalo: ' . h((string)cfg('mail_edalo.from')) . '.'
    : 'Falta agregar el correo de Edalo en config.php (mail_edalo). Mientras tanto, Edalo envía desde el correo de Ariadna.';

page_start('Actualización 7', ['nav' => $u]);
echo '<div class="card"><h1>Actualización 7 · Edalo</h1><ul>';
foreach ($log as $l) echo '<li>' . $l . '</li>';
echo '</ul><p><b>Listo.</b> Ahora borra este archivo (actualizar_plan.php) desde el Administrador de archivos.</p>'
    . '<p><a class="btn btn-main" href="admin.php">Ir a Oficinas para activar Edalo</a></p></div>';
page_end();