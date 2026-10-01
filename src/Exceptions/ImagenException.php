<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Excepcion de la capa de imagenes: errores de carga, tamano o tipo MIME.
 */
class ImagenException extends RuntimeException
{
}
