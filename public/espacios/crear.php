<?php

declare(strict_types=1);

/**
 * Formulario de registro de espacios (CRUD: Create).
 * Si venian errores de un intento anterior se muestran junto a cada campo
 * y se conservan los valores ingresados.
 */

require __DIR__ . '/../../src/bootstrap.php';

use App\Factories\EspacioFactory;

$errores = $_SESSION['errores'] ?? [];
$viejo = $_SESSION['viejo'] ?? [];
unset($_SESSION['errores'], $_SESSION['viejo']);

$tipos = EspacioFactory::tipos();
$tipo = (string) ($_GET['tipo'] ?? $viejo['tipo'] ?? array_key_first($tipos));

if (!isset($tipos[$tipo])) {
    $tipo = (string) array_key_first($tipos);
}

$datos = $viejo;
$espacioActual = null;
$titulo = 'Registrar espacio';
$accion = '/espacios/guardar.php';
$textoBoton = 'Guardar espacio';

require __DIR__ . '/../../views/layout/encabezado.php';
require __DIR__ . '/../../views/espacios/formulario.php';
require __DIR__ . '/../../views/layout/pie.php';
