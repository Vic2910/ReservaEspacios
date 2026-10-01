<?php

declare(strict_types=1);

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * Excepcion del dominio: se lanza cuando un valor viola una invariante
 * de los modelos y el objeto nunca debe quedar en un estado incorrecto.
 */
class DominioException extends InvalidArgumentException
{
}
