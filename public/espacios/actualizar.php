<?php

declare(strict_types=1);

/**
 * Procesamiento de la edicion de espacios. Si se sube una imagen nueva se
 * reemplaza la anterior y el archivo viejo se elimina del servidor.
 */

use App\Exceptions\DominioException;
use App\Exceptions\ImagenException;
use App\Factories\EspacioFactory;
use App\Validation\Validador;

require __DIR__ . '/../../src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('/espacios/index.php'); // [PRG]
}

if (!csrf_verificar($_POST['csrf'] ?? null)) {
    flash('La sesion expiro o el formulario no es valido. Intentalo de nuevo.', 'error'); // [SEGURIDAD]
    redirigir('/espacios/index.php');
}

$id = id_solicitud();
$espacioActual = $id === null ? null : $repositorioEspacios->buscarPorId($id);

if ($espacioActual === null) {
    flash('El espacio solicitado no existe.', 'error');
    redirigir('/espacios/index.php');
}

/** @var array<string, string> $tipos */
$tipos = EspacioFactory::tipos();
$datos = $_POST;
$tipo = trim((string) ($datos['tipo'] ?? ''));

$validador = new Validador();
$validador->unoDe('tipo', $tipo, array_keys($tipos), 'Tipo de espacio')
    ->requerido('nombre', $datos['nombre'] ?? '', 'Nombre')
    ->maximo('nombre', $datos['nombre'] ?? '', 80, 'Nombre')
    ->requerido('capacidad', $datos['capacidad'] ?? '', 'Capacidad')
    ->enteroMayorQue('capacidad', $datos['capacidad'] ?? '', 0, 'Capacidad')
    ->requerido('tarifa_base', $datos['tarifa_base'] ?? '', 'Tarifa base')
    ->decimalMayorQue('tarifa_base', $datos['tarifa_base'] ?? '', 0, 'Tarifa base');

if (isset($tipos[$tipo])) {
    $validador->camposEspecificos(EspacioFactory::camposEspecificos($tipo), $datos);
}

if (!$validador->esValido()) {
    $_SESSION['errores'] = $validador->errores();
    $_SESSION['viejo'] = $datos;
    redirigir('/espacios/editar.php?id=' . $espacioActual->getId()); // [PRG]
}

$archivo = $_FILES['imagen'] ?? null;
$subioImagen = is_array($archivo)
    && (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

$rutaImagen = null;

try {
    if ($subioImagen) {
        $rutaImagen = $gestorImagenes->guardar($archivo); // [VALIDACION] [SEGURIDAD]
    }

    $imagen = $rutaImagen ?? $espacioActual->getImagen();
    $espacio = EspacioFactory::desdeFormulario($datos, $imagen, $espacioActual->getId()); // [FABRICA]
    $repositorioEspacios->actualizar($espacio); // [CRUD-UPDATE]

    if ($rutaImagen !== null && $rutaImagen !== $espacioActual->getImagen()) {
        $gestorImagenes->eliminar($espacioActual->getImagen());
    }
} catch (ImagenException $ex) {
    $_SESSION['errores'] = ['imagen' => $ex->getMessage()];
    $_SESSION['viejo'] = $datos;
    redirigir('/espacios/editar.php?id=' . $espacioActual->getId()); // [PRG]
} catch (DominioException $ex) {
    $gestorImagenes->eliminar($rutaImagen);
    $_SESSION['errores'] = ['general' => $ex->getMessage()];
    $_SESSION['viejo'] = $datos;
    redirigir('/espacios/editar.php?id=' . $espacioActual->getId()); // [PRG]
} catch (PDOException $ex) {
    $gestorImagenes->eliminar($rutaImagen);
    error_log('[ReservaEspacios] edicion de espacio: ' . $ex->getMessage());

    $_SESSION['errores'] = $ex->getCode() === '23000'
        ? ['nombre' => 'Ya existe un espacio con ese nombre.']
        : ['general' => 'No se pudo actualizar el espacio. Intentalo de nuevo.'];
    $_SESSION['viejo'] = $datos;
    redirigir('/espacios/editar.php?id=' . $espacioActual->getId()); // [PRG]
}

flash('Espacio actualizado correctamente.');
redirigir('/espacios/ver.php?id=' . $espacioActual->getId()); // [PRG]
