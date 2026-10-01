<?php

declare(strict_types=1);

namespace App\Validation;

/**
 * Validacion en el servidor (capa obligatoria): revisa cada campo de $_POST,
 * acumula los errores en un arreglo asociativo campo => mensaje y permite
 * mostrarlos todos a la vez junto al campo correspondiente. [VALIDACION]
 */
final class Validador
{
    /** @var array<string, string> */
    private array $errores = [];

    /** @param array<string, string> $errores */
    public function __construct(array $errores = [])
    {
        $this->errores = $errores;
    }

    public function requerido(string $campo, mixed $valor, string $etiqueta): self
    {
        if ($this->vacio($valor)) {
            $this->agregarError($campo, "El campo {$etiqueta} es obligatorio.");
        }

        return $this;
    }

    public function maximo(string $campo, mixed $valor, int $max, string $etiqueta): self
    {
        if ($this->vacio($valor)) {
            return $this;
        }

        if (mb_strlen((string) $valor) > $max) {
            $this->agregarError($campo, "El campo {$etiqueta} no puede superar {$max} caracteres.");
        }

        return $this;
    }

    public function enteroMayorQue(string $campo, mixed $valor, int $min, string $etiqueta): self
    {
        if ($this->vacio($valor)) {
            return $this;
        }

        if (!preg_match('/^-?\d+$/', trim((string) $valor))) {
            $this->agregarError($campo, "El campo {$etiqueta} debe ser un numero entero.");

            return $this;
        }

        if ((int) $valor <= $min) {
            $this->agregarError($campo, "El campo {$etiqueta} debe ser mayor que {$min}.");
        }

        return $this;
    }

    public function decimalMayorQue(string $campo, mixed $valor, float $min, string $etiqueta): self
    {
        if ($this->vacio($valor)) {
            return $this;
        }

        if (!is_numeric((string) $valor)) {
            $this->agregarError($campo, "El campo {$etiqueta} debe ser un numero.");

            return $this;
        }

        if ((float) $valor <= $min) {
            $this->agregarError($campo, sprintf('El campo %s debe ser mayor que %s.', $etiqueta, $this->formatear($min)));
        }

        return $this;
    }

    /** @param array<int|string, mixed> $permitidos */
    public function unoDe(string $campo, mixed $valor, array $permitidos, string $etiqueta): self
    {
        if ($this->vacio($valor)) {
            return $this;
        }

        if (!in_array($valor, $permitidos, true)) {
            $this->agregarError($campo, "El campo {$etiqueta} no es una opcion valida.");
        }

        return $this;
    }

    public function fecha(string $campo, mixed $valor, string $etiqueta): self
    {
        if ($this->vacio($valor)) {
            return $this;
        }

        $texto = (string) $valor;

        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $texto, $partes)
            || !checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1])
        ) {
            $this->agregarError($campo, "El campo {$etiqueta} debe ser una fecha valida (AAAA-MM-DD).");
        }

        return $this;
    }

    public function hora(string $campo, mixed $valor, string $etiqueta): self
    {
        if ($this->vacio($valor)) {
            return $this;
        }

        $texto = (string) $valor;

        if (!preg_match('/^(\d{2}):(\d{2})$/', $texto, $partes)
            || (int) $partes[1] > 23
            || (int) $partes[2] > 59
        ) {
            $this->agregarError($campo, "El campo {$etiqueta} debe ser una hora valida (HH:MM).");
        }

        return $this;
    }

    public function horaPosterior(
        string $campo,
        mixed $valor,
        string $referencia,
        string $etiqueta,
        string $etiquetaReferencia
    ): self {
        if ($this->vacio($valor) || $this->vacio($referencia)) {
            return $this;
        }

        if ((string) $valor <= (string) $referencia) {
            $this->agregarError($campo, "El campo {$etiqueta} debe ser posterior al campo {$etiquetaReferencia}.");
        }

        return $this;
    }

    public function agregarError(string $campo, string $mensaje): self
    {
        if (!isset($this->errores[$campo])) {
            $this->errores[$campo] = $mensaje;
        }

        return $this;
    }

    /**
     * Valida en bloque los campos propios del tipo de espacio a partir de su
     * definicion (camposEspecificos). La regla es generica: el validador nunca
     * pregunta de que subtipo se trata. [VALIDACION] [FABRICA]
     *
     * @param list<array<string, mixed>> $definiciones
     * @param array<string, mixed> $datos
     */
    public function camposEspecificos(array $definiciones, array $datos): self
    {
        foreach ($definiciones as $definicion) {
            $campo = (string) $definicion['name'];
            $etiqueta = (string) $definicion['etiqueta'];
            $valor = $datos[$campo] ?? '';

            if (!empty($definicion['requerido'])) {
                $this->requerido($campo, $valor, $etiqueta);
            }

            $tipo = (string) ($definicion['tipo'] ?? 'text');

            if ($tipo === 'select') {
                $this->unoDe($campo, $valor, array_keys($definicion['opciones'] ?? []), $etiqueta);
            } elseif ($tipo === 'number') {
                $this->enteroMayorQue($campo, $valor, (int) ($definicion['min'] ?? 0), $etiqueta);
            }

            if (isset($definicion['max'])) {
                $this->maximo($campo, $valor, (int) $definicion['max'], $etiqueta);
            }
        }

        return $this;
    }

    public function esValido(): bool
    {
        return $this->errores === [];
    }

    /** @return array<string, string> */
    public function errores(): array
    {
        return $this->errores;
    }

    private function vacio(mixed $valor): bool
    {
        return $valor === null || (is_scalar($valor) && trim((string) $valor) === '');
    }

    private function formatear(float $numero): string
    {
        return rtrim(rtrim(number_format($numero, 2, '.', ''), '0'), '.');
    }
}
