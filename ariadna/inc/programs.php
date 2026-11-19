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
