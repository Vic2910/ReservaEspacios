<?php

declare(strict_types=1);

namespace App\Espacios;

use App\Exceptions\DominioException;

/**
 * Sala de reunion: tarifa por hora con recargo del 20% en horario pico.
 */
final class Sala extends Espacio
{
    public const TIPO = 'sala';

    private string $ubicacion;

    public function __construct(
        string $nombre,
        int $capacidad,
        float $tarifaBase,
        string $ubicacion,
        ?string $imagen = null,
        int $id = 0
    ) {
        parent::__construct($nombre, $capacidad, $tarifaBase, $imagen, $id);

        $ubicacion = trim($ubicacion);

        if ($ubicacion === '') {
            throw new DominioException('La ubicacion de la sala es obligatoria.');
        }

        if (mb_strlen($ubicacion) > 80) {
            throw new DominioException('La ubicacion no puede superar 80 caracteres.');
        }

        $this->ubicacion = $ubicacion;
    }

    public function getUbicacion(): string
    {
        return $this->ubicacion;
    }

    public function calcularCosto(float $horas, bool $horarioPico = false): float // [POLIMORFISMO]
    {
        $recargo = $horarioPico ? 1.20 : 1.0;

        return round($this->tarifaBase * $horas * $recargo, 2);
    }

    public static function etiquetaTipo(): string
    {
        return 'Sala de reunion';
    }

    public function datoCalculado(): string
    {
        return sprintf('2 h en horario pico: S/ %.2f', $this->calcularCosto(2, true));
    }

    public static function camposEspecificos(): array
    {
        return [
            [
                'name' => 'ubicacion',
                'etiqueta' => 'Ubicacion (edificio y salon)',
                'tipo' => 'text',
                'requerido' => true,
                'max' => 80,
                'ayuda' => 'Ejemplo: Edificio B, salon 204',
            ],
        ];
    }

    protected function datosEspecificos(): array
    {
        return ['ubicacion' => $this->ubicacion];
    }
}
