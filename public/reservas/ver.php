<?php

declare(strict_types=1);

/**
 * Detalle (ficha) de una reserva (CRUD: Read).
 */

require __DIR__ . '/../../src/bootstrap.php';

$id = id_solicitud();
$reserva = $id === null ? null : $repositorioReservas->buscarPorId($id);

if ($reserva === null) {
    flash('La reserva solicitada no existe.', 'error');
    redirigir('/reservas/index.php');
}

$titulo = 'Ficha de la reserva';
$seccion = 'reservas';

require __DIR__ . '/../../views/layout/encabezado.php';
require __DIR__ . '/../../views/reservas/ficha.php';
require __DIR__ . '/../../views/layout/pie.php';
