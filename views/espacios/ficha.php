<?php

use App\Factories\EspacioFactory;

/**
 * Ficha completa de un espacio. Los datos especificos del tipo se obtienen
 * recorriendo la definicion de la fabrica: sin condicionales por tipo. [POLIMORFISMO]
 *
 * Variables esperadas: Espacio $espacio, Reserva[] $reservas
 */
?>
<section>
    <div class="encabezado-pagina">
        <h1>Ficha del espacio</h1>
        <div class="acciones">
            <a class="btn" href="/espacios/editar.php?id=<?= e($espacio->getId()) ?>">Editar</a>
            <a class="btn btn-peligro" href="/espacios/eliminar.php?id=<?= e($espacio->getId()) ?>">Eliminar</a>
            <a class="btn btn-secundario" href="/espacios/index.php">Volver al listado</a>
        </div>
    </div>

    <article class="ficha">
        <div class="ficha-media">
            <img src="<?= e($gestorImagenes->url($espacio->getImagen())) ?>"
                 alt="Fotografia de <?= e($espacio->getNombre()) ?>">
        </div>

        <div class="ficha-datos">
            <dl>
                <dt>Nombre</dt>
                <dd><?= e($espacio->getNombre()) ?></dd>

                <dt>Tipo</dt>
                <dd><span class="insignia"><?= e($espacio->descripcionTipo()) ?></span></dd>

                <dt>Capacidad</dt>
                <dd><?= e($espacio->getCapacidad()) ?> personas</dd>

                <dt>Tarifa base</dt>
                <dd>S/ <?= e(number_format($espacio->getTarifaBase(), 2)) ?> por hora</dd>

                <dt>Dato calculado</dt>
                <dd><?= e($espacio->datoCalculado()) ?></dd>

                <?php foreach (EspacioFactory::camposEspecificos($espacio->getTipo()) as $definicion): ?>
                    <?php
                    $nombre = (string) $definicion['name'];
                    $valor = (string) ($espacio->aFila()[$nombre] ?? '');
                    if (isset($definicion['opciones'])) {
                        $valor = (string) ($definicion['opciones'][$valor] ?? $valor);
                    }
                    ?>
                    <dt><?= e($definicion['etiqueta']) ?></dt>
                    <dd><?= e($valor) ?></dd>
                <?php endforeach; ?>

                <dt>Identificador</dt>
                <dd>#<?= e($espacio->getId()) ?></dd>
            </dl>
        </div>
    </article>

    <h2>Reservas registradas en este espacio</h2>

    <?php if ($reservas === []): ?>
        <p class="vacio">Este espacio todavia no tiene reservas.</p>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                <tr>
                    <th scope="col">Fecha</th>
                    <th scope="col">Horario</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Horas</th>
                    <th scope="col">Costo estimado</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($reservas as $reserva): ?>
                    <tr>
                        <td><?= e($reserva->getFecha()) ?></td>
                        <td><?= e($reserva->getHoraInicio()) ?> - <?= e($reserva->getHoraFin()) ?></td>
                        <td><?= e($reserva->getCliente()) ?></td>
                        <td><?= e(number_format($reserva->horas(), 1)) ?></td>
                        <td class="numerico">S/ <?= e(number_format($reserva->costoEstimado(), 2)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
