<?php

declare(strict_types=1);

namespace App\Factories;

use App\Entidades\Reserva;
use App\Espacios\Espacio;
use App\Exceptions\DominioException;

/**
 * Fabrica de reservas: construye la entidad a partir de una fila de la base
 * de datos (junto con su espacio resuelto) o de los datos del formulario. [FABRICA]
 */
final class ReservaFactory
{
    /** @return array<string, mixed> */
    public static function desdeFila(array $fila, Espacio $espacio): Reserva // [FABRICA]
    {
        $cliente = (string) ($fila['cliente'] ?? '');
        $fecha = (string) ($fila['fecha'] ?? '');
        $horaInicio = self::hora((string) ($fila['hora_inicio'] ?? ''));
        $horaFin = self::hora((string) ($fila['hora_fin'] ?? ''));
        $id = (int) ($fila['id'] ?? 0);

        if ($fecha === '' || $horaInicio === '' || $horaFin === '') {
            throw new DominioException('La fila de la reserva esta incompleta.');
        }

        return new Reserva($espacio, $cliente, $fecha, $horaInicio, $horaFin, $id);
    }

    /** @param array<string, mixed> $datos */
    public static function desdeFormulario(array $datos, Espacio $espacio, int $id = 0): Reserva // [FABRICA]
    {
        return new Reserva(
            $espacio,
            trim((string) ($datos['cliente'] ?? '')),
            trim((string) ($datos['fecha'] ?? '')),
            trim((string) ($datos['hora_inicio'] ?? '')),
            trim((string) ($datos['hora_fin'] ?? '')),
            $id
        );
    }

    /** MySQL devuelve TIME como HH:MM:SS; el formulario usa HH:MM. */
    private static function hora(string $valor): string
    {
        if (strlen($valor) === 8) {
            return substr($valor, 0, 5);
        }

        return $valor;
    }
}
