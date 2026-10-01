<?php

declare(strict_types=1);

/*
 * Plantilla de configuracion. Se sube al repositorio sin credenciales reales.
 * Para ejecutar la aplicacion copia este archivo como config/config.php
 * (config/config.php esta en .gitignore y NUNCA se sube).
 */

return [
    'db' => [
        'host' => '127.0.0.1',
        'puerto' => 3306,
        'nombre' => 'reserva_espacios',
        'usuario' => 'root',
        'clave' => '',
        'charset' => 'utf8mb4',
    ],

    'app' => [
        'nombre' => 'ReservaEspacios',
        'institucion' => 'TORNEO-UNI',
        'caso' => 'Caso A · Reservas de espacios',
    ],

    'imagenes' => [
        'max_bytes' => 2 * 1024 * 1024,
    ],
];
