<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Entidades\Reserva;
use App\Espacios\CanchaFutbol;
use App\Espacios\CanchaTenis;
use App\Servicios\ArchivoReservas;

$futbol = new CanchaFutbol('TEST-FUT', 'Cancha de prueba', 35.00);
$tenis = new CanchaTenis('TEST-TEN', 'Cancha de tenis de prueba', 18.00);

$reservaFutbol = new Reserva('TEST-001', 'Equipo de prueba', $futbol, '2026-09-15 18:00', 2, true);
$reservaTenis = new Reserva('TEST-002', 'Persona de prueba', $tenis, '2026-09-15 10:00', 1);

if ($reservaFutbol->costo !== 87.50 || $reservaTenis->costo !== 23.00) {
    throw new RuntimeException('Las tarifas polimorficas no coinciden con lo esperado.');
}

$rutaTemporal = sys_get_temp_dir() . '/reservas-smoke.json';
$archivo = new ArchivoReservas($rutaTemporal);
$archivo->guardar([$reservaFutbol, $reservaTenis]);
$datos = $archivo->leer();

if (count($datos) !== 2) {
    throw new RuntimeException('El archivo JSON no contiene las reservas esperadas.');
}

unlink($rutaTemporal);
echo "SMOKE_TEST_OK\n";