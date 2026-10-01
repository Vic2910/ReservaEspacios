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
$reservasHoy = $repositorioReservas->listarPorFecha($fechaHoy);
$ingresoHoy = array_sum(array_map(
    static fn (Reserva $reserva): float => $reserva->costoEstimado(),
    $reservasHoy
));

$proximasReservas = array_slice($repositorioReservas->listar(), 0, 5);

$titulo = 'Panel';
$seccion = 'panel';

require __DIR__ . '/../views/layout/encabezado.php';
require __DIR__ . '/../views/inicio.php';
require __DIR__ . '/../views/layout/pie.php';
