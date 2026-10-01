<?php

declare(strict_types=1);

/**
 * Listado de espacios (CRUD: Read).
 */

use App\Espacios\Espacio;

require __DIR__ . '/../../src/bootstrap.php';

/** @var Espacio[] $espacios */
$espacios = $repositorioEspacios->listar();

$titulo = 'Espacios';
$seccion = 'espacios';

require __DIR__ . '/../../views/layout/encabezado.php';
require __DIR__ . '/../../views/espacios/listado.php';
require __DIR__ . '/../../views/layout/pie.php';
