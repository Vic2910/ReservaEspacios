<?php

declare(strict_types=1);

/**
 * Eliminacion de reservas: confirmacion en GET y borrado por POST con
 * token CSRF. [CRUD-DELETE] [SEGURIDAD] [PRG]
 */

require __DIR__ . '/../../src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verificar($_POST['csrf'] ?? null)) {
        flash('La sesion expiro o el formulario no es valido. Intentalo de nuevo.', 'error'); // [SEGURIDAD]
        redirigir('/reservas/index.php');
    }

    $id = id_solicitud();
    $reserva = $id === null ? null : $repositorioReservas->buscarPorId($id);

    if ($reserva === null) {
        flash('La reserva solicitada no existe.', 'error');
        redirigir('/reservas/index.php');
    }

    try {
        $repositorioReservas->eliminar($reserva->getId()); // [CRUD-DELETE]
        flash('Reserva eliminada correctamente.');
        redirigir('/reservas/index.php'); // [PRG]
    } catch (PDOException $ex) {
        error_log('[ReservaEspacios] baja de reserva: ' . $ex->getMessage());
        flash('No se pudo eliminar la reserva. Intentalo de nuevo.', 'error');
        redirigir('/reservas/index.php');
    }
}

$id = id_solicitud();
$reserva = $id === null ? null : $repositorioReservas->buscarPorId($id);

if ($reserva === null) {
    flash('La reserva solicitada no existe.', 'error');
    redirigir('/reservas/index.php');
}

$filas = [
    'Cliente' => $reserva->getCliente(),
    'Espacio' => $reserva->getEspacio()->getNombre() . ' (' . $reserva->getEspacio()->descripcionTipo() . ')',
    'Fecha' => $reserva->getFecha(),
    'Horario' => $reserva->getHoraInicio() . ' - ' . $reserva->getHoraFin(),
    'Duracion' => number_format($reserva->horas(), 1) . ' horas',
    'Costo estimado' => 'S/ ' . number_format($reserva->costoEstimado(), 2),
];

$titulo = 'Confirmar eliminacion de reserva';
$nombreRegistro = 'la reserva de ' . $reserva->getCliente();
$accion = '/reservas/eliminar.php';
$urlVolver = '/reservas/ver.php?id=' . $reserva->getId();

require __DIR__ . '/../../views/layout/encabezado.php';
require __DIR__ . '/../../views/partials/confirmar.php';
require __DIR__ . '/../../views/layout/pie.php';
