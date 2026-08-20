<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Entidades\Reserva;
use App\Espacios\CanchaFutbol;
use App\Espacios\CanchaTenis;
use App\Espacios\Piscina;
use App\Servicios\ArchivoReservas;

$espacios = [
    new CanchaFutbol('FUT-01', 'Cancha de futbol 11', 35.00),
    new CanchaTenis('TEN-01', 'Cancha de tenis', 18.00),
    new Piscina('PIS-01', 'Piscina semiolimpica', 40.00),
];

$reservas = [
    new Reserva('R-001', 'Equipo Los Titanes', $espacios[0], '2026-09-10 18:00', 2, true),
    new Reserva('R-002', 'Maria Gonzalez', $espacios[1], '2026-09-10 10:00', 1),
    new Reserva('R-003', 'Club Nadadores UNI', $espacios[2], '2026-09-10 16:00', 2),
];

$archivo = new ArchivoReservas(__DIR__ . '/data/reservas.json');
$archivo->guardar($reservas);

printf("=== TORNEO-UNI | COMPLEJO DEPORTIVO ===\n");
printf("Reservas registradas: %d\n\n", count($archivo->leer()));

foreach ($reservas as $reserva) {
    printf(
        "[%s] %s | %s | %d hora(s) | $%.2f\n",
        $reserva->id,
        $reserva->cliente,
        $reserva->espacio->getNombre(),
        $reserva->horas,
        $reserva->costo
    );
}

printf("\nPolimorfismo: cada espacio calculo su costo con calcularCosto().\n");

try {
    new Reserva('R-004', '', $espacios[0], '2026-09-10 20:00', 1);
} catch (InvalidArgumentException $exception) {
    printf("Validacion demostrada: %s\n", $exception->getMessage());
}
