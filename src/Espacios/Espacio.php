<?php

declare(strict_types=1);

namespace App\Espacios;

use App\Contratos\Exportable;
use App\Contratos\Reservable;
use InvalidArgumentException;

abstract class Espacio implements Reservable, Exportable
{
    protected string $nombre;
    protected float $tarifaBase;
    private array $reservas = [];

    public function __construct(
        public readonly string $codigo,
        string $nombre,
        float $tarifaBase
    ) {
        if ($codigo === '' || $nombre === '') {
            throw new InvalidArgumentException('El codigo y el nombre son obligatorios.');
        }

        if ($tarifaBase <= 0) {
            throw new InvalidArgumentException('La tarifa base debe ser mayor que cero.');
        }

        $this->nombre = $nombre;
        $this->tarifaBase = $tarifaBase;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function reservar(string $inicio, int $horas): void
    {
        if ($horas < 1 || $horas > 8) {
            throw new InvalidArgumentException('Una reserva debe durar entre 1 y 8 horas.');
        }

        if (!$this->estaDisponible($inicio, $horas)) {
            throw new InvalidArgumentException("El espacio {$this->codigo} no esta disponible.");
        }

        $this->reservas[] = ['inicio' => $inicio, 'horas' => $horas];
    }

    public function estaDisponible(string $inicio, int $horas): bool
    {
        if ($horas < 1) {
            return false;
        }

        $nuevoInicio = new \DateTimeImmutable($inicio);
        $nuevoFin = $nuevoInicio->modify("+{$horas} hours");

        foreach ($this->reservas as $reserva) {
            $inicioExistente = new \DateTimeImmutable($reserva['inicio']);
            $finExistente = $inicioExistente->modify("+{$reserva['horas']} hours");

            if ($nuevoInicio < $finExistente && $nuevoFin > $inicioExistente) {
                return false;
            }
        }

        return true;
    }

    abstract public function calcularCosto(int $horas, bool $horarioPico = false): float;

    public function aArray(): array
    {
        return [
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'tipo' => static::class,
            'reservas' => $this->reservas,
        ];
    }
}
