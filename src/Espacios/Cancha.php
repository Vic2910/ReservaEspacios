<?php

declare(strict_types=1);

namespace App\Espacios;

use App\Exceptions\DominioException;

/**
 * Cancha deportiva: tarifa por hora con recargo del 25% en horario pico.
 */
final class Cancha extends Espacio
{
    public const TIPO = 'cancha';

    /** Deportos admitidos: la clave es el valor guardado en la base de datos. */
    public const DEPORTES = [
        'Futbol' => 'Futbol 11 / 7',
        'Tenis' => 'Tenis',
        'Basquet' => 'Basquetbol',
        'Voleibol' => 'Voleibol',
    ];

    private string $deporte;

    public function __construct(
        string $nombre,
        int $capacidad,
        float $tarifaBase,
        string $deporte,
        ?string $imagen = null,
        int $id = 0
    ) {
        parent::__construct($nombre, $capacidad, $tarifaBase, $imagen, $id);

        $deporte = trim($deporte);

        if ($deporte === '') {
            throw new DominioException('El deporte de la cancha es obligatorio.');
        }

        if (!array_key_exists($deporte, self::DEPORTES)) {
            throw new DominioException('El deporte de la cancha no es valido.');
        }

        $this->deporte = $deporte;
    }

    public function getDeporte(): string
    {
        return $this->deporte;
    }

    public function calcularCosto(float $horas, bool $horarioPico = false): float // [POLIMORFISMO]
    {
        $recargo = $horarioPico ? 1.25 : 1.0;

        return round($this->tarifaBase * $horas * $recargo, 2);
    }

    public static function etiquetaTipo(): string
    {
        return 'Cancha deportiva';
    }

    public function datoCalculado(): string
    {
        return sprintf('1 h (partido) en pico: S/ %.2f', $this->calcularCosto(1, true));
    }

    public static function camposEspecificos(): array
    {
        return [
            [
                'name' => 'deporte',
                'etiqueta' => 'Deporte de la cancha',
                'tipo' => 'select',
                'requerido' => true,
                'opciones' => self::DEPORTES,
            ],
        ];
    }

    protected function datosEspecificos(): array
    {
        return ['deporte' => $this->deporte];
    }
}
