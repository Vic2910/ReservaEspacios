<?php

declare(strict_types=1);

/**
 * Funciones auxiliares globales (autoload PSR-4 via "files" en composer.json).
 */

if (!function_exists('e')) {
    /**
     * Escapa cualquier dato antes de imprimirlo en HTML.
     * Evita que datos de la base de datos o del usuario inyecten codigo. [SEGURIDAD]
     */
    function e(mixed $valor): string
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    /** Token unico por sesion para formularios que modifican datos. [SEGURIDAD] */
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf'];
    }
}

if (!function_exists('csrf_input')) {
    /** Campo oculto con el token CSRF para imprimir dentro de los formularios. */
    function csrf_input(): string
    {
        return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_verificar')) {
    /** Verifica el token CSRF en cada POST que modifica datos. [SEGURIDAD] */
    function csrf_verificar(?string $token): bool
    {
        $esperado = (string) ($_SESSION['csrf'] ?? '');

        return is_string($token) && $token !== '' && hash_equals($esperado, $token);
    }
}

if (!function_exists('flash')) {
    /** Guarda un mensaje para mostrarlo despues de una redireccion (patron PRG). [PRG] */
    function flash(string $texto, string $tipo = 'exito'): void
    {
        $_SESSION['flash'] = ['texto' => $texto, 'tipo' => $tipo];
    }
}

if (!function_exists('redirigir')) {
    /**
     * Post-Redirect-Get: cierra el ciclo del formulario sin volver a procesar
     * la peticion POST al recargar la pagina. [PRG]
     */
    function redirigir(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('valor_viejo')) {
    /**
     * Recupera el valor enviado por el usuario para conservarlo en el formulario
     * despues de un error de validacion.
     */
    function valor_viejo(array $viejo, string $campo, string $default = ''): string
    {
        $valor = $viejo[$campo] ?? $default;

        return is_scalar($valor) ? (string) $valor : $default;
    }
}

if (!function_exists('error_de_campo')) {
    /** Devuelve el mensaje de error de un campo para mostrarlo junto al campo. */
    function error_de_campo(array $errores, string $campo): ?string
    {
        return isset($errores[$campo]) ? (string) $errores[$campo] : null;
    }
}

if (!function_exists('clase_error')) {
    /** Clase CSS comun para marcar campos con o sin error. */
    function clase_error(array $errores, string $campo): string
    {
        return isset($errores[$campo]) ? 'campo campo-error' : 'campo';
    }
}
