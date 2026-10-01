<?php

declare(strict_types=1);

namespace App\Entidades;

use App\Contratos\Exportable;
use App\Espacios\Espacio;
use App\Exceptions\DominioException;
use DateTimeImmutable;

/**
 * Reserva de un espacio: relaciona la jerarquia de espacios con un cliente,
 * una fecha y un horario. La reserva CONPO NE a un espacio (relacion 1 a N
 * que en la base de datos es la llave foranea `espacio_id`). [COMPOSICION]
 */
final class Reserva implements Exportable
{
    private int $id;
    private Espacio $espacio;
    private string $cliente;
    private string $fecha;
    private string $horaInicio;
    private string $horaFin;

    public function __construct(
        Espacio $espacio,
        string $cliente,
        string $fecha,
        string $horaInicio,
        string $horaFin,
        int $id = 0
    ) {
        $cliente = trim($cliente);

        if ($cliente === '') {
            throw new DominioException('El cliente es obligatorio.');
        }

        if (mb_strlen($cliente) > 80) {
            throw new DominioException('El cliente no puede superar 80 caracteres.');
        }

        if (!self::fechaValida($fecha)) {
            throw new DominioException('La fecha de la reserva no es valida. Use el formato AAAA-MM-DD.');
        }

        if (!self::horaValida($horaInicio) || !self::horaValida($horaFin)) {
            throw new DominioException('Las horas de la reserva no son validas. Use el formato HH:MM.');
        }

        if ($horaFin <= $horaInicio) {
            throw new DominioException('La hora de fin debe ser posterior a la hora de inicio.');
        }

        $this->id = $id;
        $this->espacio = $espacio;
        $this->cliente = $cliente;
        $this->fecha = $fecha;
        $this->horaInicio = $horaInicio;
        $this->horaFin = $horaFin;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEspacio(): Espacio
    {
        return $this->espacio;
    }

    public function getCliente(): string
    {
        return $this->cliente;
    }

    public function getFecha(): string
    {
        return $this->fecha;
    }

    public function getHoraInicio(): string
    {
        return $this->horaInicio;
    }

    public function getHoraFin(): string
    {
        return $this->horaFin;
    }

    /** Duracion en horas (admite media hora). */
    public function horas(): float
    {
        $inicio = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $this->fecha . ' ' . $this->horaInicio);
        $fin = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $this->fecha . ' ' . $this->horaFin);

        if ($inicio === false || $fin === false) {
            return 0.0;
        }

        return ($fin->getTimestamp() - $inicio->getTimestamp()) / 3600;
    }

    /** Horario pico: a partir de las 17:00. */
    public function enHorarioPico(): bool
    {
        return $this->horaInicio >= '17:00';
    }

    /**
     * Delega el calculo al espacio: el costo lo resuelve cada subclase con
     * su propia tarifa, sin preguntar nunca de que tipo es el espacio. [POLIMORFISMO]
     */
    public function costoEstimado(): float
    {
        return $this->espacio->calcularCosto($this->horas(), $this->enHorarioPico());
    }

    /** @return array<string, mixed> */
    public function aArray(): array // [INTERFAZ]
    {
        return [
            'id' => $this->id,
            'cliente' => $this->cliente,
            'espacio' => $this->espacio->getNombre(),
            'fecha' => $this->fecha,
            'hora_inicio' => $this->horaInicio,
            'hora_fin' => $this->horaFin,
            'horas' => $this->horas(),
            'costo' => $this->costoEstimado(),
        ];
    }

    private static function fechaValida(string $fecha): bool
    {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fecha, $partes)) {
            return false;
        }

        return checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1]);
    }

    private static function horaValida(string $hora): bool
    {
        if (!preg_match('/^(\d{2}):(\d{2})$/', $hora, $partes)) {
            return false;
        }

        return (int) $partes[1] <= 23 && (int) $partes[2] <= 59;
    }
}
