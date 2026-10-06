<?php

declare(strict_types=1);

/**
 * Exportacion CSV de las reservas del dia mostrado en el reporte.
 * Aplica los mismos criterios de fecha y tipo que /reporte.php: la fecha
 * se valida con fecha_solicitud() y el tipo con EspacioFactory::tipos(),
 * de modo que un tipo invalido se ignora. [CRUD-READ]
 *
 * La respuesta contiene exclusivamente CSV: sin HTML ni plantillas.
 */

use App\Entidades\Reserva;
use App\Factories\EspacioFactory;

require __DIR__ . '/../src/bootstrap.php';

$fecha = fecha_solicitud(date('Y-m-d'));

$tipoSolicitado = trim((string) ($_GET['tipo'] ?? ''));
$tipo = array_key_exists($tipoSolicitado, EspacioFactory::tipos()) ? $tipoSolicitado : '';

/** @var Reserva[] $reservas */
$reservas = $repositorioReservas->listarPorFecha($fecha);

if ($tipo !== '') {
    $reservas = array_values(array_filter(
        $reservas,
        static fn (Reserva $reserva): bool => $reserva->getEspacio()->getTipo() === $tipo
    ));
}

// $fecha ya quedo validada como AAAA-MM-DD: el nombre es seguro para la cabecera.
$nombreArchivo = 'reporte-' . $fecha . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

$salida = fopen('php://output', 'w');

fputcsv($salida, ['Horario', 'Cliente', 'Espacio', 'Tipo', 'Horas', 'Horario pico', 'Costo'], ';', '"', '');

foreach ($reservas as $reserva) {
    fputcsv(
        $salida,
        [
            $reserva->getHoraInicio() . ' - ' . $reserva->getHoraFin(),
            $reserva->getCliente(),
            $reserva->getEspacio()->getNombre(),
            $reserva->getEspacio()->descripcionTipo(),
            number_format($reserva->horas(), 1, '.', ''),
            $reserva->enHorarioPico() ? 'Si' : 'No',
            number_format($reserva->costoEstimado(), 2, '.', ''),
        ],
        ';',
        '"',
        ''
    );
}

fclose($salida);
