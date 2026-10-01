<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ImagenException;

/**
 * Servicio de imagenes: valida errores de carga, tamano maximo (2 MB) y tipo
 * MIME real del archivo (JPG, PNG o WEBP), guarda el archivo con un nombre
 * aleatorio generado por el sistema y elimina las imagenes reemplazadas o
 * asociadas a registros borrados. [VALIDACION] [SEGURIDAD]
 */
final class GestorImagenes
{
    public const MAX_BYTES = 2 * 1024 * 1024;

    /** MIME real permitido => extension que se usa en el nombre generado. */
    private const PERMITIDOS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private string $directorio,
        private int $maxBytes = self::MAX_BYTES
    ) {
        if (!is_dir($this->directorio)) {
            mkdir($this->directorio, 0775, true);
        }
    }

    /**
     * Valida y almacena una imagen subida desde un formulario.
     *
     * @param array<string, mixed> $archivo elemento de $_FILES
     * @return string nombre unico del archivo (lo unico que se guarda en la BD)
     */
    public function guardar(array $archivo): string
    {
        $error = (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new ImagenException('No se selecciono ninguna imagen.');
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new ImagenException($this->mensajeDeError($error));
        }

        $tamano = (int) ($archivo['size'] ?? 0);

        if ($tamano > $this->maxBytes) {
            throw new ImagenException(sprintf(
                'La imagen supera el tamano maximo permitido de %d MB.',
                (int) ceil($this->maxBytes / 1024 / 1024)
            ));
        }

        $rutaTemporal = (string) ($archivo['tmp_name'] ?? '');

        if ($rutaTemporal === '' || !is_uploaded_file($rutaTemporal)) {
            throw new ImagenException('El archivo de la imagen no es valido.');
        }

        // El tipo real lo decide el contenido del archivo, jamas la extension. [SEGURIDAD]
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($rutaTemporal);

        if ($mime === false) {
            throw new ImagenException('No se pudo determinar el formato de la imagen.');
        }

        $extension = self::PERMITIDOS[$mime] ?? null;

        if ($extension === null) {
            throw new ImagenException('Formato no permitido. Use una imagen JPG, PNG o WEBP.');
        }

        // Nombre unico generado por el sistema: nunca el nombre original. [SEGURIDAD]
        $nombre = bin2hex(random_bytes(16)) . '.' . $extension;

        if (!move_uploaded_file($rutaTemporal, $this->directorio . '/' . $nombre)) {
            throw new ImagenException('No se pudo guardar la imagen en el servidor.');
        }

        return $nombre;
    }

    /** Elimina la imagen de un registro borrado o la reemplazada. */
    public function eliminar(?string $nombre): void
    {
        if ($nombre === null || $nombre === '') {
            return;
        }

        // basename() evita cualquier intento de salirse del directorio. [SEGURIDAD]
        $ruta = $this->directorio . '/' . basename($nombre);

        // Reintento breve: en Windows el antivirus puede bloquear el archivo
        // recien escrito un instante y dejar una imagen huerfana en uploads/.
        for ($intento = 0; $intento < 3 && is_file($ruta); $intento++) {
            if (@unlink($ruta)) {
                return;
            }

            usleep(100_000);
        }
    }

    /** URL para mostrar la imagen, o la imagen por defecto si no existe. */
    public function url(?string $nombre): string
    {
        if ($nombre === null || $nombre === '' || !is_file($this->directorio . '/' . basename($nombre))) {
            return '/img/sin-imagen.svg';
        }

        return '/uploads/' . basename($nombre);
    }

    private function mensajeDeError(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE =>
                'La imagen supera el tamano maximo permitido por el servidor.',
            UPLOAD_ERR_PARTIAL =>
                'La imagen se subio incompleta. Intentalo de nuevo.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE =>
                'El servidor no pudo almacenar la imagen.',
            default =>
                'No se pudo subir la imagen. Intentalo de nuevo.',
        };
    }
}
