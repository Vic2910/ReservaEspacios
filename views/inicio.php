<?php

use App\Factories\EspacioFactory;

/**
 * Panel inicial: nombre del sistema, caso de estudio, accesos a cada
 * modulo y resumen general.
 *
 * Variables esperadas:
 * - array<string,int> $totalesPorTipo
 * - int $totalEspacios, int $totalReservas, int $reservasHoy
 * - float $ingresoHoy
 * - Reserva[] $proximasReservas
 */
?>
<section>
    <div class="encabezado-pagina">
        <div>
            <h1><?= e($config['app']['nombre']) ?></h1>
            <p class="ayuda"><?= e($config['app']['institucion']) ?> · <?= e($config['app']['caso']) ?></p>
        </div>
        <a class="btn" href="/espacios/crear.php">Registrar espacio</a>
    </div>

    <p>
        Aplicacion web de la Fase 2: los espacios (salas, escritorios y canchas) y sus reservas
        se guardan en MySQL mediante PDO y se administran con un CRUD completo.
    </p>

    <div class="rejilla">
        <div class="tarjeta-resumen">
            <span class="valor"><?= e($totalEspacios) ?></span>
            <span class="etiqueta">Espacios registrados</span>
        </div>
        <div class="tarjeta-resumen">
            <span class="valor"><?= e($totalReservas) ?></span>
            <span class="etiqueta">Reservas totales</span>
        </div>
        <div class="tarjeta-resumen">
            <span class="valor"><?= e($reservasHoy) ?></span>
            <span class="etiqueta">Reservas de hoy</span>
        </div>
        <div class="tarjeta-resumen">
            <span class="valor">S/ <?= e(number_format($ingresoHoy, 2)) ?></span>
            <span class="etiqueta">Costo estimado de hoy</span>
        </div>
    </div>

    <h2>Modulos del sistema</h2>

    <div class="rejilla">
        <a class="tarjeta tarjeta-enlace" href="/espacios/index.php">
            <h3>Espacios</h3>
            <p>Alta, listado, detalle, edicion y eliminacion de salas, escritorios y canchas, con fotografia.</p>
        </a>
        <a class="tarjeta tarjeta-enlace" href="/reservas/index.php">
            <h3>Reservas</h3>
            <p>Registro de reservas por espacio, fecha y horario, con rechazo automatico de traslapes.</p>
        </a>
        <a class="tarjeta tarjeta-enlace" href="/reporte.php">
            <h3>Reporte web</h3>
            <p>Reservas del dia y tarifas calculadas polimorficamente por cada tipo de espacio.</p>
        </a>
    </div>

    <h2>Espacios por tipo</h2>

    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
            <tr>
                <th scope="col">Tipo</th>
                <th scope="col">Cantidad</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (EspacioFactory::tipos() as $clave => $etiqueta): ?>
                <tr>
                    <td><?= e($etiqueta) ?></td>
                    <td class="numerico"><?= e($totalesPorTipo[$clave] ?? 0) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h2>Proximas reservas</h2>

    <?php if ($proximasReservas === []): ?>
        <p class="vacio">No hay reservas registradas. <a href="/reservas/crear.php">Agenda la primera</a>.</p>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                <tr>
                    <th scope="col">Fecha</th>
                    <th scope="col">Horario</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Espacio</th>
                    <th scope="col">Costo estimado</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($proximasReservas as $reserva): ?>
                    <tr>
                        <td><?= e($reserva->getFecha()) ?></td>
                        <td><?= e($reserva->getHoraInicio()) ?> - <?= e($reserva->getHoraFin()) ?></td>
                        <td><?= e($reserva->getCliente()) ?></td>
                        <td>
                            <?= e($reserva->getEspacio()->getNombre()) ?>
                            <span class="insignia"><?= e($reserva->getEspacio()->descripcionTipo()) ?></span>
                        </td>
                        <td class="numerico">S/ <?= e(number_format($reserva->costoEstimado(), 2)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
