<?php

declare(strict_types=1);

namespace App\Contratos;

/**
 * Contrato de todo espacio reservable: cada subclase calcula su propio
 * costo, de modo que el resto del sistema nunca pregunta por el tipo. [ABSTRACCION]
 */
interface Reservable
{
    /**
     * Costo de una reserva en el espacio.
     * Cada subclase aplica su propia regla de tarifa (polimorfismo genuino).
     */
    public function calcularCosto(float $horas, bool $horarioPico = false): float;
}
