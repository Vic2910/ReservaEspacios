<?php

declare(strict_types=1);

/**
 * Listado de la entidad relacionada Reserva (CRUD: Read).
 */

use App\Entidades\Reserva;

require __DIR__ . '/../../src/bootstrap.php';

/** @var Reserva[] $reservas */
$reservas = $repositorioReservas->listar();

$titulo = 'Reservas';
$seccion = 'reservas';

require __DIR__ . '/../../views/layout/encabezado.php';
require __DIR__ . '/../../views/reservas/listado.php';
require __DIR__ . '/../../views/layout/pie.php';
