<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Entidades\Reserva;
use App\Espacios\Cancha;
use App\Espacios\Sala;
use App\Services\ArchivoReservas;

$sala = new Sala('Sala de prueba', 20, 120.00, 'Edificio de prueba');
$cancha = new Cancha('Cancha de prueba', 22, 90.00, 'Futbol');

$reservaSala = new Reserva($sala, 'Cliente de prueba', '2026-10-15', '17:00', '19:00');
$reservaCancha = new Reserva($cancha, 'Club de prueba', '2026-10-15', '10:00', '11:00');

// 120.00 * 2 h * 1.20 (pico) = 288.00  |  90.00 * 1 h * 1.00 = 90.00
if ($reservaSala->costoEstimado() !== 288.00 || $reservaCancha->costoEstimado() !== 90.00) {
    throw new RuntimeException('Las tarifas polimorficas no coinciden con lo esperado.');
}

$rutaTemporal = sys_get_temp_dir() . '/reservas-smoke.json';
$archivo = new ArchivoReservas($rutaTemporal);
$archivo->guardar([$reservaSala, $reservaCancha]);
$datos = $archivo->leer();

if (count($datos) !== 2) {
    throw new RuntimeException('El archivo JSON no contiene las reservas esperadas.');
}

unlink($rutaTemporal);

try {
    new Reserva($sala, 'Cliente de prueba', '2026-10-15', '19:00', '18:00');
    throw new RuntimeException('Se esperaba una excepcion por hora de fin anterior.');
} catch (InvalidArgumentException $ex) {
    // validacion de dominio correcta
}

echo "SMOKE_TEST_OK\n";
