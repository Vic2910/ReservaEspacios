<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

/**
 * Unica puerta de entrada a MySQL/MariaDB de la aplicacion.
 * Todas las conexiones pasan por aqui con PDO y consultas preparadas.
 */
final class Conexion
{
    /**
     * @param array<string, mixed> $config configuracion completa (clave 'db')
     */
    public static function desdeConfig(array $config): PDO // [INYECCION-DEPENDENCIAS]
    {
        $db = $config['db'];

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $db['host'],
            (int) $db['puerto'],
            $db['nombre'],
            $db['charset'] ?? 'utf8mb4'
        );

        return new PDO(
            $dsn,
            (string) $db['usuario'],
            (string) $db['clave'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Consultas preparadas reales: el servidor planifica la consulta
                // una vez y los datos nunca se interpretan como SQL. [SEGURIDAD]
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
}
