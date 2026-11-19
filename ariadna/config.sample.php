<?php
/**
 * ARIADNA · Configuración
 * 1) Copia este archivo con el nombre config.php
 * 2) Llena los datos de tu base de datos (hPanel > Bases de datos > MySQL)
 * 3) Llena los datos de tu correo (hPanel > Correos > Configuración de cliente)
 */
return [
    // Dirección donde vive Ariadna, sin diagonal al final
    'base_url' => 'https://ariadna.oscarmarquez.com',

    // Zona horaria por defecto (cada oficina puede tener la suya)
    'timezone' => 'America/Cancun',

    // Redirigir siempre a https://
    'force_https' => true,

    'db' => [
        'host' => 'localhost',
        'name' => 'u000000000_ariadna',   // nombre de la base de datos
        'user' => 'u000000000_ariadna',   // usuario de la base de datos
        'pass' => 'CAMBIA-ESTA-CONTRASEÑA',
    ],

    'mail' => [
        'driver'    => 'smtp',              // 'smtp' para enviar, 'log' para solo guardar en storage/mail.log
        'host'      => 'smtp.hostinger.com',
        'port'      => 465,
        'secure'    => 'ssl',               // 'ssl' con puerto 465, 'tls' con puerto 587
        'user'      => 'ariadna@oscarmarquez.com',
        'pass'      => 'CONTRASEÑA-DEL-BUZÓN',
        'from'      => 'ariadna@oscarmarquez.com',
        'from_name' => 'Ariadna · R²CP Elite',
    ],

    // Edalo (Fast Track Industrial): su dirección y su remitente.
    // Envía con el mismo buzón de Ariadna; solo cambia el remitente (edalo@ es un alias de ariadna@).
    'edalo_url' => 'https://edalo.oscarmarquez.com',
    'mail_edalo' => [
        'from'      => 'edalo@oscarmarquez.com',
        'from_name' => 'Edalo · R²CP Elite',
    ],

    // Clave para ejecutar las tareas programadas desde una URL (si no usas el comando PHP)
    'cron_key' => 'CAMBIA-ESTO-POR-UNA-FRASE-LARGA',

    // Clave interna para firmar el acceso con PIN. Escribe cualquier frase larga y no la compartas.
    'app_secret' => 'CAMBIA-ESTO-POR-OTRA-FRASE-LARGA',
];
