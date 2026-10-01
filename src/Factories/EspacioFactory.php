<?php

declare(strict_types=1);

namespace App\Factories;

use App\Espacios\Cancha;
use App\Espacios\Escritorio;
use App\Espacios\Espacio;
use App\Espacios\Sala;
use App\Exceptions\DominioException;

/**
 * Fabrica de espacios: UNICO lugar del sistema que conoce las clases
 * concretas de la jerarquia. Decide que subclase instanciar a partir de
 * una fila de la base de datos o de los datos de un formulario, y tambien
 * centraliza la union de columnas de la tabla unica (STI). [FABRICA]
 */
final class EspacioFactory
{
    /** Union de columnas especificas de la tabla `espacios` (NULL si no aplican). */
    public const COLUMNAS = ['ubicacion', 'zona', 'deporte'];

    /** @var array<string, class-string<Espacio>> mapa tipo => subclase concreta */
    private const MAPA = [
        'sala' => Sala::class,
        'escritorio' => Escritorio::class,
        'cancha' => Cancha::class,
    ];

    /**
     * Tipos disponibles para el selector del formulario y para los reportes.
     *
     * @return array<string, string>
     */
    public static function tipos(): array
    {
        $tipos = [];

        foreach (self::MAPA as $clave => $clase) {
            $tipos[$clave] = $clase::etiquetaTipo();
        }

        return $tipos;
    }

    public static function etiqueta(string $tipo): string
    {
        return self::tipos()[$tipo] ?? 'Tipo desconocido';
    }

    /** @return class-string<Espacio> */
    public static function clasePara(string $tipo): string // [FABRICA]
    {
        if (!isset(self::MAPA[$tipo])) {
            throw new DominioException('El tipo de espacio no es valido.');
        }

        return self::MAPA[$tipo];
    }

    /**
     * Definicion de los campos propios del tipo, para el formulario y el validador.
     *
     * @return list<array<string, mixed>>
     */
    public static function camposEspecificos(string $tipo): array // [FABRICA]
    {
        $clase = self::clasePara($tipo);

        return $clase::camposEspecificos();
    }

    /** Construye el objeto de dominio a partir de una fila de la base de datos. [FABRICA] */
    public static function desdeFila(array $fila): Espacio
    {
        $tipo = (string) ($fila['tipo'] ?? '');
        $nombre = (string) ($fila['nombre'] ?? '');
        $capacidad = (int) ($fila['capacidad'] ?? 0);
        $tarifa = (float) ($fila['tarifa_base'] ?? 0);
        $imagen = ($fila['imagen'] ?? null) !== null ? (string) $fila['imagen'] : null;
        $id = (int) ($fila['id'] ?? 0);

        return match (self::clasePara($tipo)) {
            Sala::class => new Sala(
                $nombre,
                $capacidad,
                $tarifa,
                (string) ($fila['ubicacion'] ?? ''),
                $imagen,
                $id
            ),
            Escritorio::class => new Escritorio(
                $nombre,
                $capacidad,
                $tarifa,
                (string) ($fila['zona'] ?? ''),
                $imagen,
                $id
            ),
            Cancha::class => new Cancha(
                $nombre,
                $capacidad,
                $tarifa,
                (string) ($fila['deporte'] ?? ''),
                $imagen,
                $id
            ),
            default => throw new DominioException('No se pudo construir el espacio desde la base de datos.'),
        };
    }

    /** Construye el objeto de dominio a partir de los datos enviados por el formulario. [FABRICA] */
    public static function desdeFormulario(array $datos, ?string $imagen = null, int $id = 0): Espacio
    {
        $tipo = trim((string) ($datos['tipo'] ?? ''));
        $nombre = trim((string) ($datos['nombre'] ?? ''));
        $capacidad = (int) ($datos['capacidad'] ?? 0);
        $tarifa = (float) ($datos['tarifa_base'] ?? 0);

        return match (self::clasePara($tipo)) {
            Sala::class => new Sala(
                $nombre,
                $capacidad,
                $tarifa,
                trim((string) ($datos['ubicacion'] ?? '')),
                $imagen,
                $id
            ),
            Escritorio::class => new Escritorio(
                $nombre,
                $capacidad,
                $tarifa,
                trim((string) ($datos['zona'] ?? '')),
                $imagen,
                $id
            ),
            Cancha::class => new Cancha(
                $nombre,
                $capacidad,
                $tarifa,
                trim((string) ($datos['deporte'] ?? '')),
                $imagen,
                $id
            ),
            default => throw new DominioException('El tipo de espacio no es valido.'),
        };
    }

    /**
     * Fila completa para el repositorio: completa las columnas especificas de
     * los demas tipos con NULL y agrega las del tipo actual. [FABRICA]
     *
     * @return array<string, mixed>
     */
    public static function filaPara(Espacio $espacio): array
    {
        // + solo agrega las claves que falten: las columnas del tipo actual
        // vienen del objeto y las de los demas tipos quedan en NULL.
        return $espacio->aFila() + array_fill_keys(self::COLUMNAS, null);
    }
}
