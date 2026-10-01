<?php

declare(strict_types=1);

/**
 * Panel inicial de la aplicacion.
 */

use App\Entidades\Reserva;

require __DIR__ . '/../src/bootstrap.php';

$totalesPorTipo = array_column($repositorioEspacios->contarPorTipo(), 'total', 'tipo');
$totalEspacios = $repositorioEspacios->contar();
$totalReservas = $repositorioReservas->contar();

$fechaHoy = date('Y-m-d');
$listaHoy = $repositorioReservas->listarPorFecha($fechaHoy);
$reservasHoy = count($listaHoy);
$ingresoHoy = array_sum(array_map(
    static fn (Reserva $reserva): float => $reserva->costoEstimado(),
    $listaHoy
));

// Proximas reservas: desde hoy en adelante, ordenadas por fecha y hora.
$proximasReservas = array_values(array_filter(
    $repositorioReservas->listar(),
    static fn (Reserva $reserva): bool => $reserva->getFecha() >= $fechaHoy
));
usort(
    $proximasReservas,
    static fn (Reserva $a, Reserva $b): int => [$a->getFecha(), $a->getHoraInicio()] <=> [$b->getFecha(), $b->getHoraInicio()]
);
$proximasReservas = array_slice($proximasReservas, 0, 5);

$titulo = 'Panel';
$seccion = 'panel';

require __DIR__ . '/../views/layout/encabezado.php';
require __DIR__ . '/../views/inicio.php';
require __DIR__ . '/../views/layout/pie.php';
