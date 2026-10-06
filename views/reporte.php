<?php

/**
 * Reporte web polimorfico: reservas de una fecha con el costo calculado
 * por cada tipo de espacio, mas las tarifas del dia calculadas con
 * calcularCosto(). Ninguna fila pregunta concreta de que tipo es el
 * espacio: el polimorfismo resuelve el dato. [POLIMORFISMO]
 *
 * Variables esperadas:
 * - string $fecha
 * - Reserva[] $reservas
 * - float $totalDia
 * - Espacio[] $espacios
 * - float $totalTarifas2Horas
 * - float $totalTarifas2HorasPico
 */
?>
<section>
    <div class="encabezado-pagina">
        <h1>Reporte web del dia</h1>
    </div>

    <form class="formulario formulario-inline" action="/reporte.php" method="get">
        <div class="campo">
            <label for="fecha">Fecha del reporte</label>
            <input type="date" id="fecha" name="fecha" value="<?= e($fecha) ?>" required>
        </div>
        <button class="btn" type="submit">Actualizar reporte</button>
    </form>

    <div class="resumen-flex">
        <span class="pastilla">Fecha: <?= e($fecha) ?></span>
        <span class="pastilla">Reservas: <?= e(count($reservas)) ?></span>
        <span class="pastilla">Costo estimado del dia: S/ <?= e(number_format($totalDia, 2)) ?></span>
    </div>

    <h2>Reservas del dia</h2>

    <?php if ($reservas === []): ?>
        <p class="vacio">No hay reservas para la fecha seleccionada.</p>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                <tr>
                    <th scope="col">Horario</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Espacio</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Horas</th>
                    <th scope="col">Horario pico</th>
                    <th scope="col">Costo</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($reservas as $reserva): ?>
                    <tr>
                        <td><?= e($reserva->getHoraInicio()) ?> - <?= e($reserva->getHoraFin()) ?></td>
                        <td><?= e($reserva->getCliente()) ?></td>
                        <td><?= e($reserva->getEspacio()->getNombre()) ?></td>
                        <td><span class="insignia"><?= e($reserva->getEspacio()->descripcionTipo()) ?></span></td>
                        <td><?= e(number_format($reserva->horas(), 1)) ?></td>
                        <td><?= $reserva->enHorarioPico() ? 'Si' : 'No' ?></td>
                        <td class="numerico">S/ <?= e(number_format($reserva->costoEstimado(), 2)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <h2>Tarifas polimorficas por espacio</h2>

    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
            <tr>
                <th scope="col">Espacio</th>
                <th scope="col">Tipo</th>
                <th scope="col">Tarifa base</th>
                <th scope="col">2 horas</th>
                <th scope="col">2 horas en horario pico</th>
                <th scope="col">Dato calculado</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($espacios as $espacio): ?>
                <tr>
                    <td><?= e($espacio->getNombre()) ?></td>
                    <td><span class="insignia"><?= e($espacio->descripcionTipo()) ?></span></td>
                    <td class="numerico">S/ <?= e(number_format($espacio->getTarifaBase(), 2)) ?></td>
                    <td class="numerico">S/ <?= e(number_format($espacio->calcularCosto(2), 2)) ?></td>
                    <td class="numerico">S/ <?= e(number_format($espacio->calcularCosto(2, true), 2)) ?></td>
                    <td><?= e($espacio->datoCalculado()) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr class="fila-totales">
                <td colspan="3">Totales</td>
                <td class="numerico">S/ <?= e(number_format($totalTarifas2Horas, 2)) ?></td>
                <td class="numerico">S/ <?= e(number_format($totalTarifas2HorasPico, 2)) ?></td>
                <td></td>
            </tr>
            </tfoot>
        </table>
    </div>
</section>
