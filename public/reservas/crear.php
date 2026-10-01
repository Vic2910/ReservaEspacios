<?php

declare(strict_types=1);

/**
 * Formulario de registro de reservas (CRUD: Create de la entidad
 * relacionada).
 */

use App\Espacios\Espacio;

require __DIR__ . '/../../src/bootstrap.php';

$errores = $_SESSION['errores'] ?? [];
$viejo = $_SESSION['viejo'] ?? [];
unset($_SESSION['errores'], $_SESSION['viejo']);

/** @var Espacio[] $espacios */
$espacios = $repositorioEspacios->listar();

$datos = $viejo !== [] ? $viejo : ['fecha' => date('Y-m-d')];
$reservaActual = null;
$titulo = 'Registrar reserva';
$accion = '/reservas/guardar.php';
$textoBoton = 'Guardar reserva';

require __DIR__ . '/../../views/layout/encabezado.php';
require __DIR__ . '/../../views/reservas/formulario.php';
require __DIR__ . '/../../views/layout/pie.php';
