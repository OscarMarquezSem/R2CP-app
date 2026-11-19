<?php
/**
 * R²CP · Programas de la plataforma: Ariadna (Residencial) y Edalo (Comercial e Industrial).
 * Una sola instalación y una sola base de datos. Cada asesor pertenece a un programa
 * y ve solo su marca, su plan y sus números. La oficina los ve juntos en su panel.
 */
declare(strict_types=1);

const PROGRAMS = [
    'residencial' => [
        'name'    => 'Ariadna',
        'brand'   => 'ARIADNA',
        'course'  => 'Fast Track Residencial',
        'area'    => 'Residencial',
        'weeks'   => 5,
        'url_key' => 'base_url',
        'mail_key'=> 'mail',
        'tagline' => 'Sigue el hilo',
        'img'     => 'ariadna',
        'routine_key' => 'routine_items',
        'fields'  => [
            'contactos'   => 'Contactos',
            'tratos'      => 'Tratos directos',
            'analizadas'  => 'Propiedades analizadas',
            'promovidas'  => 'Propiedades promovidas',
            'citas'       => 'Citas agendadas',
            'captaciones' => 'Captaciones',
        ],
    ],
    'industrial' => [
        'name'    => 'Edalo',
        'brand'   => 'EDALO',
        'course'  => 'Fast Track Industrial',
        'area'    => 'Comercial e Industrial',
        'weeks'   => 4,
        'url_key' => 'edalo_url',
        'mail_key'=> 'mail_edalo',
        'tagline' => 'Construye con método',
        'img'     => 'edalo',
        'routine_key' => 'routine_items_industrial',
        'fields'  => [
            'llamadas'   => 'Llamadas del Impulsor',
            'recorridos' => 'Recorridos de zona',
            'fichas'     => 'Fichas de inmuebles levantadas',
            'cadenas'    => 'Cadenas de contacto iniciadas',
            'leads'      => 'Leads válidos',
            'piezas'     => 'Posts o flyers creados',
        ],
    ],
];

/** Programa activo para lo que se está calculando o mostrando (por defecto, Residencial). */
function prog(): string
{
    return $GLOBALS['__r2cp_prog'] ?? 'residencial';
}

function prog_set(?string $p): void
{
    $GLOBALS['__r2cp_prog'] = isset(PROGRAMS[$p ?? '']) ? $p : 'residencial';
}

/** Definición de un programa (el activo si no se indica). */
function P(?string $p = null): array
{
    return PROGRAMS[$p ?? prog()] ?? PROGRAMS['residencial'];
}

function prog_weeks(?string $p = null): int
{
    return (int)P($p)['weeks'];
}

/** Campos que el asesor registra cada día en su programa. */
function result_fields(?string $p = null): array
{
    return P($p)['fields'];
}

/** Programas que tiene una oficina. */
function office_programs(array $office): array
{
    $list = array_values(array_filter(explode(',', (string)($office['programs'] ?? 'residencial')), fn($x) => isset(PROGRAMS[$x])));
    return $list ?: ['residencial'];
}

function advisor_program(array $adv): string
{
    return isset(PROGRAMS[$adv['program'] ?? '']) ? $adv['program'] : 'residencial';
}

/** Dirección pública de un programa (sin diagonal final). Edalo usa su subdominio si está configurado. */
function prog_base_url(?string $p = null): string
{
    $key = P($p)['url_key'];
    $url = (string)cfg($key, "");
    if ($url === "") $url = (string)cfg("base_url", "");
    return rtrim($url, '/');
}

/** Frases que cambian según quién acompaña al asesor (Ariadna habla en femenino; Edalo, en masculino). */
const PROGRAM_TEXTS = [
    'residencial' => [
        'msg_reto'    => 'Ve el video con calma y después ve a tu oficina: te va a hacer 5 preguntas. No es para calificarte, es para asegurarnos de que agarraste el hilo.',
        'msg_first'   => 'Antes, tus contactos del día.',
        'msg_default' => 'Empieza por tus contactos del día y luego sigue la lista conmigo.',
        'msg_week'    => 'Primero agenda tus horarios y pon tus metas; luego sigue la lista conmigo.',
        'grad_title'  => '¡Saliste del laberinto!',
        'grad_text'   => 'Terminaste el curso Fast Track Residencial. Estoy muy orgullosa de ti.',
        'grad_end'    => 'Gracias por seguir el hilo. Tu plan de los próximos 30 días lo revisas con tu oficina.',
        'rest_title'  => 'Hoy descansas, pero no sueltes el hilo.',
        'rest_text'   => 'Si conoces a alguien que quiera comprar, vender o rentar, anótalo aquí.',
        'rest_field'  => 'contactos',
        'rest_hint'   => '¿A cuántas personas detectaste?',
        'rest_ph'     => 'Ej. Mi primo quiere vender su casa en la Región 15',
        'push_btn'    => 'Activar avisos de Ariadna',
        'reto_ok'     => 'Agarraste el hilo: sigue así.',
        'saved'       => '¡Listo! Ya guardé tu día. Mañana seguimos. No sueltes el hilo.',
        'week_closed' => '¡Cerraste tu Semana %d! Estoy muy orgullosa de ti. Descansa y prepárate: la que viene es todavía mejor. No sueltes el hilo.',
        'celebrate_alt' => 'Ariadna celebra contigo',
    ],
    'industrial' => [
        'msg_reto'    => 'Ve tu video en la oficina y quédate: tu Team Leader te hace las preguntas de tu Reto. No es para calificarte, es para asegurarnos de que dominas el tema.',
        'msg_first'   => 'Antes, tus 10 llamadas del Impulsor.',
        'msg_default' => 'Empieza por tus 10 llamadas del Impulsor y luego sigue la lista conmigo.',
        'msg_week'    => 'Sigue la lista conmigo. Recuerda: una empresa decide con números, no con emoción.',
        'grad_title'  => '¡Terminaste tu Fast Track Industrial!',
        'grad_text'   => 'Construiste tu base en el mercado comercial e industrial. Estoy muy orgulloso de ti.',
        'grad_end'    => 'Sigue construyendo con método. Tu plan de los próximos 30 días lo revisas con tu Team Leader.',
        'rest_title'  => 'Hoy descansas, pero tu negocio sigue en marcha.',
        'rest_text'   => 'Si alguien te habló de un local, una bodega o una empresa que busca espacio, anótalo aquí.',
        'rest_field'  => 'leads',
        'rest_hint'   => 'Con nombre, contacto y una necesidad concreta',
        'rest_ph'     => 'Ej. Mi contador tiene un cliente que busca una bodega de 800 m²',
        'push_btn'    => 'Activar avisos de Edalo',
        'reto_ok'     => 'Dominas el tema: sigue así.',
        'saved'       => '¡Listo! Ya guardé tu día. Mañana seguimos construyendo.',
        'week_closed' => '¡Cerraste tu Semana %d! Estoy muy orgulloso de ti. Descansa y prepárate: la que viene es todavía mejor.',
        'celebrate_alt' => 'Edalo celebra contigo',
    ],
];

function T(string $key, ?string $p = null): string
{
    return PROGRAM_TEXTS[$p ?? prog()][$key] ?? PROGRAM_TEXTS['residencial'][$key] ?? '';
}

/** Imagen del acompañante: avatar, welcome, celebra, descanso, graduacion. Edalo usa su ilustración provisional hasta tener sus imágenes. */
function prog_img(string $kind, ?string $p = null): string
{
    $base = P($p)['img'];
    $file = $kind === 'welcome' ? $base . '.jpg' : $base . '-' . $kind . '.jpg';
    if (is_file(APP_ROOT . '/assets/' . $file)) return 'assets/' . $file;
    return is_file(APP_ROOT . '/assets/' . $base . '.svg') ? 'assets/' . $base . '.svg' : 'assets/ariadna-avatar.jpg';
}
