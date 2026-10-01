<?php

declare(strict_types=1);

/**
 * Procesamiento del formulario de reservas: validacion en el servidor,
 * rechazo de traslapes en el mismo espacio y patron PRG.
 */

use App\Exceptions\DominioException;
use App\Factories\ReservaFactory;
use App\Validation\Validador;

require __DIR__ . '/../../src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('/reservas/crear.php'); // [PRG]
}

if (!csrf_verificar($_POST['csrf'] ?? null)) {
    flash('La sesion expiro o el formulario no es valido. Intentalo de nuevo.', 'error'); // [SEGURIDAD]
    redirigir('/reservas/crear.php');
}

$datos = $_POST;

// [VALIDACION] todas las reglas se aplican en el servidor, no solo en el navegador.
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
        $reserva = ReservaFactory::desdeFormulario($datos, $espacio); // [FABRICA]
    } catch (DominioException $ex) {
        $validador->agregarError('general', $ex->getMessage());
    }
}

if ($validador->esValido()) {
    // Rechazo de reservas traslapadas en el mismo espacio y dia. [VALIDACION]
    if ($repositorioReservas->existeTraslape(
        (int) $datos['espacio_id'],
        (string) $datos['fecha'],
        (string) $datos['hora_inicio'],
        (string) $datos['hora_fin']
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
    redirigir('/reservas/crear.php'); // [PRG]
}

try {
    $repositorioReservas->crear($reserva); // [CRUD-CREATE]
} catch (PDOException $ex) {
    error_log('[ReservaEspacios] alta de reserva: ' . $ex->getMessage());
    $_SESSION['errores'] = ['general' => 'No se pudo guardar la reserva. Intentalo de nuevo.'];
    $_SESSION['viejo'] = $datos;
    redirigir('/reservas/crear.php'); // [PRG]
}

flash('Reserva registrada correctamente.');
redirigir('/reservas/index.php'); // [PRG]
