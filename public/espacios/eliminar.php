<?php

declare(strict_types=1);

/**
 * Eliminacion de espacios: el GET solo muestra la confirmacion y el
 * borrado (incluida la imagen) se ejecuta unicamente por POST con
 * verificacion de token CSRF. [CRUD-DELETE] [SEGURIDAD] [PRG]
 */

use App\Espacios\Espacio;
use App\Factories\EspacioFactory;

require __DIR__ . '/../../src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verificar($_POST['csrf'] ?? null)) {
        flash('La sesion expiro o el formulario no es valido. Intentalo de nuevo.', 'error'); // [SEGURIDAD]
        redirigir('/espacios/index.php');
    }

    $id = id_solicitud();
    $espacio = $id === null ? null : $repositorioEspacios->buscarPorId($id);

    if ($espacio === null) {
        flash('El espacio solicitado no existe.', 'error');
        redirigir('/espacios/index.php');
    }

    if ($repositorioEspacios->tieneReservas($espacio->getId())) {
        flash(
            'No se puede eliminar el espacio: tiene reservas asociadas. '
            . 'Elimina primero sus reservas.',
            'error'
        );
        redirigir('/espacios/ver.php?id=' . $espacio->getId());
    }

    try {
        $repositorioEspacios->eliminar($espacio->getId()); // [CRUD-DELETE]
        $gestorImagenes->eliminar($espacio->getImagen());
        flash('Espacio eliminado correctamente.');
        redirigir('/espacios/index.php'); // [PRG]
    } catch (PDOException $ex) {
        error_log('[ReservaEspacios] baja de espacio: ' . $ex->getMessage());
        flash('No se pudo eliminar el espacio por sus reservas asociadas.', 'error');
        redirigir('/espacios/ver.php?id=' . $espacio->getId());
    }
}

$id = id_solicitud();
$espacio = $id === null ? null : $repositorioEspacios->buscarPorId($id);

if ($espacio === null) {
    flash('El espacio solicitado no existe.', 'error');
    redirigir('/espacios/index.php');
}

/** @var Espacio $espacio */
$filas = [
    'Tipo' => $espacio->descripcionTipo(),
    'Capacidad' => $espacio->getCapacidad() . ' personas',
    'Tarifa base' => 'S/ ' . number_format($espacio->getTarifaBase(), 2) . ' por hora',
    'Imagen' => $espacio->getImagen() !== null ? 'Fotografia propia' : 'Imagen por defecto',
];

// Los campos especificos se listan recorriendo la definicion de la fabrica,
// sin preguntar concretamente de que tipo es el espacio. [FABRICA]
foreach (EspacioFactory::camposEspecificos($espacio->getTipo()) as $definicion) {
    $nombre = (string) $definicion['name'];
    $valor = (string) ($espacio->aFila()[$nombre] ?? '');

    if (isset($definicion['opciones'])) {
        $valor = (string) ($definicion['opciones'][$valor] ?? $valor);
    }

    $filas[(string) $definicion['etiqueta']] = $valor;
}

$titulo = 'Confirmar eliminacion de espacio';
$nombreRegistro = $espacio->getNombre();
$accion = '/espacios/eliminar.php';
$urlVolver = '/espacios/ver.php?id=' . $espacio->getId();

require __DIR__ . '/../../views/layout/encabezado.php';
require __DIR__ . '/../../views/partials/confirmar.php';
require __DIR__ . '/../../views/layout/pie.php';
