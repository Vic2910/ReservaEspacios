<?php

declare(strict_types=1);

/**
 * Reporte web polimorfico: reservas de una fecha con los costos calculados
 * por cada tipo de espacio y tarifas del dia, leidos desde la base de datos
 * y sin ningun condicional por tipo concreto. [POLIMORFISMO]
 */

use App\Entidades\Reserva;
use App\Espacios\Espacio;
use App\Factories\EspacioFactory;

require __DIR__ . '/../src/bootstrap.php';

$fecha = fecha_solicitud(date('Y-m-d'));

// El filtro por tipo se valida contra los tipos reales del proyecto: si el
// valor recibido no existe se ignora y el reporte muestra todos. [VALIDACION]
$tiposEspacios = EspacioFactory::tipos();
$tipoSolicitado = trim((string) ($_GET['tipo'] ?? ''));
$tipo = array_key_exists($tipoSolicitado, $tiposEspacios) ? $tipoSolicitado : '';

/** @var Reserva[] $reservas */
$reservas = $repositorioReservas->listarPorFecha($fecha);

if ($tipo !== '') {
    $reservas = array_values(array_filter(
        $reservas,
        static fn (Reserva $reserva): bool => $reserva->getEspacio()->getTipo() === $tipo
    ));
}

$totalDia = array_sum(array_map(
    static fn (Reserva $reserva): float => $reserva->costoEstimado(),
    $reservas
));

$espacios = $repositorioEspacios->listar();

if ($tipo !== '') {
    $espacios = array_values(array_filter(
        $espacios,
        static fn (Espacio $espacio): bool => $espacio->getTipo() === $tipo
    ));
}

$totalTarifas2Horas = array_sum(array_map(
    static fn (Espacio $espacio): float => $espacio->calcularCosto(2),
    $espacios
));

$totalTarifas2HorasPico = array_sum(array_map(
    static fn (Espacio $espacio): float => $espacio->calcularCosto(2, true),
    $espacios
));

$titulo = 'Reporte web';
$seccion = 'reporte';

require __DIR__ . '/../views/layout/encabezado.php';
require __DIR__ . '/../views/reporte.php';
require __DIR__ . '/../views/layout/pie.php';
