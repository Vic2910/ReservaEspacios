<?php

declare(strict_types=1);

/**
 * Punto de arranque de la aplicacion web: autoload, configuracion, sesion,
 * conexion PDO y servicios compartidos. Lo incluye cada pagina de public/.
 * Este archivo vive fuera de public/ por seguridad.
 */

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

$rutaConfig = __DIR__ . '/../config/config.php';

if (!is_file($rutaConfig)) {
    http_response_code(500);
    exit(
        'Falta la configuracion: copia config/config.example.php como config/config.php '
        . 'y ejecuta database/schema.sql y database/seed.sql (ver README.md).'
    );
}

/** @var array<string, mixed> $config */
$config = require $rutaConfig;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Los detalles de las excepciones (por ejemplo, errores de PDO) nunca se
 * muestran al usuario: se registran en el log y se responde con un mensaje
 * generico. [SEGURIDAD]
 */
set_exception_handler(static function (Throwable $ex): void {
    error_log('[ReservaEspacios] ' . $ex::class . ': ' . $ex->getMessage());

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }

    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
        . '<title>Error</title><link rel="stylesheet" href="/css/estilos.css"></head>'
        . '<body><main class="contenedor"><section class="tarjeta">'
        . '<h1>Ocurrio un error inesperado</h1>'
        . '<p class="alerta-error">El equipo tecnico ya fue notificado. '
        . 'Intentalo de nuevo en unos minutos.</p>'
        . '<p><a class="btn" href="/index.php">Volver al panel</a></p>'
        . '</section></main></body></html>';
    exit;
});

$conexion = App\Database\Conexion::desdeConfig($config);

// [INYECCION-DEPENDENCIAS] los repositorios y el gestor reciben sus dependencias.
$repositorioEspacios = new App\Repositories\EspacioRepositorio($conexion);
$repositorioReservas = new App\Repositories\ReservaRepositorio($conexion, $repositorioEspacios);
$gestorImagenes = new App\Services\GestorImagenes(
    dirname(__DIR__) . '/public/uploads',
    (int) ($config['imagenes']['max_bytes'] ?? 2 * 1024 * 1024)
);
