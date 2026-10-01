<?php

declare(strict_types=1);

/**
 * Formulario de edicion de espacios (CRUD: Update), con la imagen actual
 * visible y opcion de reemplazarla.
 */

use App\Factories\EspacioFactory;

require __DIR__ . '/../../src/bootstrap.php';

$id = id_solicitud();
$espacioActual = $id === null ? null : $repositorioEspacios->buscarPorId($id);

if ($espacioActual === null) {
    flash('El espacio solicitado no existe.', 'error');
    redirigir('/espacios/index.php');
}

$errores = $_SESSION['errores'] ?? [];
$viejo = $_SESSION['viejo'] ?? [];
unset($_SESSION['errores'], $_SESSION['viejo']);

$tipos = EspacioFactory::tipos();
$tipo = (string) ($viejo['tipo'] ?? $espacioActual->getTipo());

if (!isset($tipos[$tipo])) {
    $tipo = (string) array_key_first($tipos);
}

$datos = $viejo !== [] ? $viejo : $espacioActual->aFila();
$titulo = 'Editar espacio';
$accion = '/espacios/actualizar.php';
$textoBoton = 'Guardar cambios';

require __DIR__ . '/../../views/layout/encabezado.php';
require __DIR__ . '/../../views/espacios/formulario.php';
require __DIR__ . '/../../views/layout/pie.php';
