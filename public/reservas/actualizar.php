<?php

declare(strict_types=1);

/**
 * Procesamiento de la edicion de reservas; excluye la propia reserva al
 * verificar traslapes.
 */

use App\Exceptions\DominioException;
use App\Factories\ReservaFactory;
use App\Validation\Validador;

require __DIR__ . '/../../src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('/reservas/index.php'); // [PRG]
}

if (!csrf_verificar($_POST['csrf'] ?? null)) {
    flash('La sesion expiro o el formulario no es valido. Intentalo de nuevo.', 'error'); // [SEGURIDAD]
    redirigir('/reservas/index.php');
}

$id = id_solicitud();
$reservaActual = $id === null ? null : $repositorioReservas->buscarPorId($id);

if ($reservaActual === null) {
    flash('La reserva solicitada no existe.', 'error');
    redirigir('/reservas/index.php');
}

$datos = $_POST;

$validador = new Validador();
$validador->requerido('espacio_id', $datos['espacio_id'] ?? '', 'Espacio')
    ->enteroMayorQue('espacio_id', $datos['espacio_id'] ?? '', 0, 'Espacio')
    ->requerido('cliente', $datos['cliente'] ?? '', 'Cliente')
    ->maximo('cliente', $datos['cliente'] ?? '', 80, 'Cliente')
    ->requerido('fecha', $datos['fecha'] ?? '', 'Fecha')
    ->fecha('fecha', $datos['fecha'] ?? '', 'Fecha')
    ->requerido('hora_inicio', $datos['hora_inicio'] ?? '', 'Hora de inicio')
    ->hora('hora_inicio', $datos['hora_inicio'] ?? '', 'Hora de inicio')
    ->requerido('hora_fin', $datos['hora_fin'] ?? '', 'Hora de fin')
    ->hora('hora_fin', $datos['hora_fin'] ?? '', 'Hora de fin')
    ->horaPosterior(
        'hora_fin',
        $datos['hora_fin'] ?? '',
        (string) ($datos['hora_inicio'] ?? ''),
        'Hora de fin',
        'Hora de inicio'
    );

$espacio = null;

if ($validador->esValido()) {
    $espacio = $repositorioEspacios->buscarPorId((int) $datos['espacio_id']);

    if ($espacio === null) {
        $validador->agregarError('espacio_id', 'El espacio seleccionado no existe.');
    }
}

$reserva = null;

if ($validador->esValido()) {
    try {
        $reserva = ReservaFactory::desdeFormulario($datos, $espacio, $reservaActual->getId()); // [FABRICA]
    } catch (DominioException $ex) {
        $validador->agregarError('general', $ex->getMessage());
    }
}

if ($validador->esValido()) {
    // Al editar se excluye la propia reserva de la comprobacion de traslape. [VALIDACION]
    if ($repositorioReservas->existeTraslape(
        (int) $datos['espacio_id'],
        (string) $datos['fecha'],
        (string) $datos['hora_inicio'],
        (string) $datos['hora_fin'],
        $reservaActual->getId()
    )) {
        $validador->agregarError(
            'hora_inicio',
            'El espacio ya tiene una reserva que se traslapa con ese horario.'
        );
    }
}

if (!$validador->esValido()) {
    $_SESSION['errores'] = $validador->errores();
    $_SESSION['viejo'] = $datos;
    redirigir('/reservas/editar.php?id=' . $reservaActual->getId()); // [PRG]
}

try {
    $repositorioReservas->actualizar($reserva); // [CRUD-UPDATE]
} catch (PDOException $ex) {
    error_log('[ReservaEspacios] edicion de reserva: ' . $ex->getMessage());
    $_SESSION['errores'] = ['general' => 'No se pudo actualizar la reserva. Intentalo de nuevo.'];
    $_SESSION['viejo'] = $datos;
    redirigir('/reservas/editar.php?id=' . $reservaActual->getId()); // [PRG]
}

flash('Reserva actualizada correctamente.');
redirigir('/reservas/ver.php?id=' . $reservaActual->getId()); // [PRG]
