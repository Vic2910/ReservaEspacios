<?php

declare(strict_types=1);

/**
 * Procesamiento del formulario de alta de espacios.
 * Valida en el servidor, aplica el patron PRG y verifica el token CSRF.
 */

use App\Exceptions\DominioException;
use App\Exceptions\ImagenException;
use App\Factories\EspacioFactory;
use App\Validation\Validador;

require __DIR__ . '/../../src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('/espacios/crear.php'); // [PRG]
}

if (!csrf_verificar($_POST['csrf'] ?? null)) {
    flash('La sesion expiro o el formulario no es valido. Intentalo de nuevo.', 'error'); // [SEGURIDAD]
    redirigir('/espacios/crear.php');
}

/** @var array<string, string> $tipos */
$tipos = EspacioFactory::tipos();
$datos = $_POST;
$tipo = trim((string) ($datos['tipo'] ?? ''));

// [VALIDACION] capa del servidor: cada campo se revisa y se acumula su error.
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
    redirigir('/espacios/crear.php?tipo=' . rawurlencode($tipo)); // [PRG]
}

$archivo = $_FILES['imagen'] ?? null;
$subioImagen = is_array($archivo)
    && (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

$rutaImagen = null;

try {
    if ($subioImagen) {
        $rutaImagen = $gestorImagenes->guardar($archivo); // [VALIDACION] [SEGURIDAD]
    }

    $espacio = EspacioFactory::desdeFormulario($datos, $rutaImagen); // [FABRICA]
    $repositorioEspacios->crear($espacio); // [CRUD-CREATE]
} catch (ImagenException $ex) {
    $_SESSION['errores'] = ['imagen' => $ex->getMessage()];
    $_SESSION['viejo'] = $datos;
    redirigir('/espacios/crear.php?tipo=' . rawurlencode($tipo)); // [PRG]
} catch (DominioException $ex) {
    $gestorImagenes->eliminar($rutaImagen);
    $_SESSION['errores'] = ['general' => $ex->getMessage()];
    $_SESSION['viejo'] = $datos;
    redirigir('/espacios/crear.php?tipo=' . rawurlencode($tipo)); // [PRG]
} catch (PDOException $ex) {
    $gestorImagenes->eliminar($rutaImagen);
    error_log('[ReservaEspacios] alta de espacio: ' . $ex->getMessage());

    $_SESSION['errores'] = $ex->getCode() === '23000'
        ? ['nombre' => 'Ya existe un espacio con ese nombre.']
        : ['general' => 'No se pudo guardar el espacio. Intentalo de nuevo.'];
    $_SESSION['viejo'] = $datos;
    redirigir('/espacios/crear.php?tipo=' . rawurlencode($tipo)); // [PRG]
}

flash('Espacio registrado correctamente.');
redirigir('/espacios/index.php'); // [PRG]
