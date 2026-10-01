<?php

declare(strict_types=1);

/**
 * Detalle (ficha) de un espacio (CRUD: Read).
 */

use App\Entidades\Reserva;
use App\Espacios\Espacio;

require __DIR__ . '/../../src/bootstrap.php';

$id = id_solicitud();
$espacio = $id === null ? null : $repositorioEspacios->buscarPorId($id);

if ($espacio === null) {
    flash('El espacio solicitado no existe.', 'error');
    redirigir('/espacios/index.php');
}

/** @var Reserva[] $reservas */
$reservas = $repositorioReservas->listarPorEspacio($espacio->getId());

$titulo = 'Ficha del espacio';
$seccion = 'espacios';

require __DIR__ . '/../../views/layout/encabezado.php';
require __DIR__ . '/../../views/espacios/ficha.php';
require __DIR__ . '/../../views/layout/pie.php';
