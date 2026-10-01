<?php

declare(strict_types=1);

namespace App\Espacios;

use App\Contratos\Exportable;
use App\Contratos\Reservable;
use App\Exceptions\DominioException;

/**
 * Clase abstracta de la jerarquia de espacios (Fase 1 conservada y mejorada).
 * Concentra los datos comunes, las invariantes y las operaciones polimorficas
 * que cada subtipo (Sala, Escritorio, Cancha) resuelve a su manera. [HERENCIA]
 */
abstract class Espacio implements Reservable, Exportable
{
    /** Valor de la columna `tipo` en la base de datos (tabla unica / STI). */
    public const TIPO = '';

    protected int $id;
    protected string $nombre;
    protected int $capacidad;
    protected float $tarifaBase;
    protected ?string $imagen;

    public function __construct(
        string $nombre,
        int $capacidad,
        float $tarifaBase,
        ?string $imagen = null,
        int $id = 0
    ) {
        $nombre = trim($nombre);

        if ($nombre === '') {
            throw new DominioException('El nombre del espacio es obligatorio.');
        }

        if (mb_strlen($nombre) > 80) {
            throw new DominioException('El nombre del espacio no puede superar 80 caracteres.');
        }

        if ($capacidad <= 0) {
            throw new DominioException('La capacidad debe ser mayor que cero.');
        }

        if ($tarifaBase <= 0) {
            throw new DominioException('La tarifa base debe ser mayor que cero.');
        }

        $this->id = $id;
        $this->nombre = $nombre;
        $this->capacidad = $capacidad;
        $this->tarifaBase = $tarifaBase;
        $this->imagen = $imagen;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getCapacidad(): int
    {
        return $this->capacidad;
    }

    public function getTarifaBase(): float
    {
        return $this->tarifaBase;
    }

    public function getImagen(): ?string
    {
        return $this->imagen;
    }

    public function getTipo(): string
    {
        return static::TIPO;
    }

    public function setImagen(?string $imagen): void
    {
        $this->imagen = $imagen;
    }

    /** Cada subtipo calcula su costo con su propia regla. [POLIMORFISMO] */
    abstract public function calcularCosto(float $horas, bool $horarioPico = false): float;

    /** Etiqueta legible del tipo; la comparten la fabrica, las vistas y el reporte. */
    abstract public static function etiquetaTipo(): string;

    /** Nombre legible del tipo, usado por vistas y reportes sin condicionales. */
    public function descripcionTipo(): string
    {
        return static::etiquetaTipo();
    }

    /** Dato calculado polimorfico para el listado y el reporte web. */
    abstract public function datoCalculado(): string;

    /**
     * Definicion (no valores) de los campos propios de cada tipo.
     * Las vistas y el validador recorren esta lista sin saber que subclase es. [ABSTRACCION]
     *
     * @return list<array<string, mixed>>
     */
    abstract public static function camposEspecificos(): array;

    /**
     * Columnas propias del subtipo para la tabla unica de la base de datos.
     *
     * @return array<string, mixed>
     */
    abstract protected function datosEspecificos(): array;

    /**
     * Fila completa para el repositorio: datos comunes + los propios del tipo.
     * El metodo es final para que todos los subtipos produzcan la misma forma. [ENCAPSULAMIENTO]
     *
     * @return array<string, mixed>
     */
    final public function aFila(): array
    {
        return [
            'tipo' => static::TIPO,
            'nombre' => $this->nombre,
            'capacidad' => $this->capacidad,
            'tarifa_base' => $this->tarifaBase,
            'imagen' => $this->imagen,
        ] + $this->datosEspecificos();
    }

    /** @return array<string, mixed> */
    public function aArray(): array // [INTERFAZ]
    {
        return [
            'id' => $this->id,
            'tipo' => static::TIPO,
            'nombre' => $this->nombre,
            'capacidad' => $this->capacidad,
            'tarifa_base' => $this->tarifaBase,
            'imagen' => $this->imagen,
        ] + $this->datosEspecificos();
    }
}
