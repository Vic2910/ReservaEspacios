<?php

/**
 * Listado de la entidad relacionada Reserva.
 *
 * Variables esperadas: Reserva[] $reservas
 */
?>
<section>
    <div class="encabezado-pagina">
        <h1>Reservas</h1>
        <a class="btn" href="/reservas/crear.php">Registrar reserva</a>
    </div>

    <?php if ($reservas === []): ?>
        <p class="vacio">Aun no hay reservas. <a href="/reservas/crear.php">Registra la primera reserva</a>.</p>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                <tr>
                    <th scope="col">Fecha</th>
                    <th scope="col">Horario</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Espacio</th>
                    <th scope="col">Horas</th>
                    <th scope="col">Costo estimado</th>
                    <th scope="col">Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($reservas as $reserva): ?>
                    <tr>
                        <td><?= e($reserva->getFecha()) ?></td>
                        <td><?= e($reserva->getHoraInicio()) ?> - <?= e($reserva->getHoraFin()) ?></td>
                        <td><?= e($reserva->getCliente()) ?></td>
                        <td>
                            <?= e($reserva->getEspacio()->getNombre()) ?>
                            <span class="insignia"><?= e($reserva->getEspacio()->descripcionTipo()) ?></span>
                        </td>
                        <td><?= e(number_format($reserva->horas(), 1)) ?></td>
                        <td class="numerico">S/ <?= e(number_format($reserva->costoEstimado(), 2)) ?></td>
                        <td>
                            <div class="acciones">
                                <a class="btn btn-mini btn-secundario" href="/reservas/ver.php?id=<?= e($reserva->getId()) ?>">Ver</a>
                                <a class="btn btn-mini" href="/reservas/editar.php?id=<?= e($reserva->getId()) ?>">Editar</a>
                                <a class="btn btn-mini btn-peligro" href="/reservas/eliminar.php?id=<?= e($reserva->getId()) ?>">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
