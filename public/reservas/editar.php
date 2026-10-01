<?php

declare(strict_types=1);

/**
 * Formulario de edicion de reservas (CRUD: Update).
 */

use App\Entidades\Reserva;
use App\Espacios\Espacio;

require __DIR__ . '/../../src/bootstrap.php';

$id = id_solicitud();
$reservaActual = $id === null ? null : $repositorioReservas->buscarPorId($id);

if ($reservaActual === null) {
    flash('La reserva solicitada no existe.', 'error');
    redirigir('/reservas/index.php');
}

$errores = $_SESSION['errores'] ?? [];
$viejo = $_SESSION['viejo'] ?? [];
unset($_SESSION['errores'], $_SESSION['viejo']);

/** @var Espacio[] $espacios */
$espacios = $repositorioEspacios->listar();

$datos = $viejo !== [] ? $viejo : [
    'espacio_id' => (string) $reservaActual->getEspacio()->getId(),
    'cliente' => $reservaActual->getCliente(),
    'fecha' => $reservaActual->getFecha(),
    'hora_inicio' => $reservaActual->getHoraInicio(),
    'hora_fin' => $reservaActual->getHoraFin(),
];

$titulo = 'Editar reserva';
$accion = '/reservas/actualizar.php';
$textoBoton = 'Guardar cambios';

require __DIR__ . '/../../views/layout/encabezado.php';
require __DIR__ . '/../../views/reservas/formulario.php';
require __DIR__ . '/../../views/layout/pie.php';
