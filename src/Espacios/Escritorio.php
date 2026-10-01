<?php

declare(strict_types=1);

namespace App\Espacios;

use App\Exceptions\DominioException;

/**
 * Escritorio individual: tarifa por hora con recargo del 10% en horario pico.
 */
final class Escritorio extends Espacio
{
    public const TIPO = 'escritorio';

    /** Zonas disponibles: la clave es el valor guardado en la base de datos. */
    public const ZONAS = [
        'Coworking' => 'Zona compartida (coworking)',
        'Gabinete' => 'Gabinete individual',
        'Privado' => 'Oficina privada',
    ];

    private string $zona;

    public function __construct(
        string $nombre,
        int $capacidad,
        float $tarifaBase,
        string $zona,
        ?string $imagen = null,
        int $id = 0
    ) {
        parent::__construct($nombre, $capacidad, $tarifaBase, $imagen, $id);

        $zona = trim($zona);

        if ($zona === '') {
            throw new DominioException('La zona del escritorio es obligatoria.');
        }

        if (!array_key_exists($zona, self::ZONAS)) {
            throw new DominioException('La zona del escritorio no es valida.');
        }

        $this->zona = $zona;
    }

    public function getZona(): string
    {
        return $this->zona;
    }

    public function calcularCosto(float $horas, bool $horarioPico = false): float // [POLIMORFISMO]
    {
        $recargo = $horarioPico ? 1.10 : 1.0;

        return round($this->tarifaBase * $horas * $recargo, 2);
    }

    public static function etiquetaTipo(): string
    {
        return 'Escritorio';
    }

    public function datoCalculado(): string
    {
        return sprintf('4 h (media jornada) en pico: S/ %.2f', $this->calcularCosto(4, true));
    }

    public static function camposEspecificos(): array
    {
        return [
            [
                'name' => 'zona',
                'etiqueta' => 'Zona del escritorio',
                'tipo' => 'select',
                'requerido' => true,
                'opciones' => self::ZONAS,
            ],
        ];
    }

    protected function datosEspecificos(): array
    {
        return ['zona' => $this->zona];
    }
}
