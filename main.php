<?php

declare(strict_types=1);

/**
 * Demostracion en consola de la Fase 1 conservada en la Fase 2.
 * El mismo modelo de clases que alimenta la aplicacion web puede ejecutarse
 * desde la terminal: php main.php
 */

require __DIR__ . '/vendor/autoload.php';

use App\Entidades\Reserva;
use App\Espacios\Cancha;
use App\Espacios\Escritorio;
use App\Espacios\Sala;
use App\Services\ArchivoReservas;

$espacios = [
    new Sala('Sala Aula Magna', 60, 120.00, 'Edificio principal, planta baja'),
    new Escritorio('Escritorio Coworking 1', 1, 12.00, 'Coworking'),
    new Cancha('Cancha de futbol 11', 22, 90.00, 'Futbol'),
];

$reservas = [
    new Reserva($espacios[0], 'Facultad de Ingenieria', '2026-10-05', '09:00', '11:00'),
    new Reserva($espacios[1], 'Ana Torres', '2026-10-05', '08:00', '12:00'),
    new Reserva($espacios[2], 'Club Deportivo UNI', '2026-10-05', '17:00', '19:00'),
];

$archivo = new ArchivoReservas(__DIR__ . '/data/reservas.json');
$archivo->guardar($reservas);

printf("=== TORNEO-UNI | RESERVA DE ESPACIOS (Fase 2) ===\n");
printf("Reservas registradas en JSON: %d\n\n", count($archivo->leer()));

foreach ($reservas as $reserva) {
    printf(
        "[%d] %s | %s (%s) | %s %s-%s | %.1f h | S/ %.2f\n",
        $reserva->getId(),
        $reserva->getCliente(),
        $reserva->getEspacio()->getNombre(),
        $reserva->getEspacio()->descripcionTipo(),
        $reserva->getFecha(),
        $reserva->getHoraInicio(),
        $reserva->getHoraFin(),
        $reserva->horas(),
        $reserva->costoEstimado()
    );
}

printf("\nPolimorfismo: cada espacio calculo su costo con calcularCosto().\n");
printf("Reporte por tipo:\n");

foreach ($espacios as $espacio) {
    printf(
        "  - %s | %s | 2 h: S/ %.2f | 2 h en horario pico: S/ %.2f\n",
        $espacio->descripcionTipo(),
        $espacio->getNombre(),
        $espacio->calcularCosto(2),
        $espacio->calcularCosto(2, true)
    );
}

try {
    new Reserva($espacios[0], '', '2026-10-05', '20:00', '22:00');
} catch (InvalidArgumentException $exception) {
    printf("\nValidacion de dominio demostrada: %s\n", $exception->getMessage());
}

try {
    new Reserva($espacios[0], 'Cliente valido', '2026-10-05', '22:00', '21:00');
} catch (InvalidArgumentException $exception) {
    printf("Validacion de horario demostrada: %s\n", $exception->getMessage());
}
