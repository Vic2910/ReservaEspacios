<?php

/**
 * Ficha completa de una reserva con su costo calculado polimorficamente
 * por el espacio asociado.
 *
 * Variables esperadas: Reserva $reserva
 */

$reserva_espacio = $reserva->getEspacio();
?>
<section>
    <div class="encabezado-pagina">
        <h1>Ficha de la reserva</h1>
        <div class="acciones">
            <a class="btn" href="/reservas/editar.php?id=<?= e($reserva->getId()) ?>">Editar</a>
            <a class="btn btn-peligro" href="/reservas/eliminar.php?id=<?= e($reserva->getId()) ?>">Eliminar</a>
            <a class="btn btn-secundario" href="/reservas/index.php">Volver al listado</a>
        </div>
    </div>

    <article class="ficha">
        <div class="ficha-media">
            <img src="<?= e($gestorImagenes->url($reserva_espacio->getImagen())) ?>"
                 alt="Fotografia de <?= e($reserva_espacio->getNombre()) ?>">
        </div>

        <div class="ficha-datos">
            <dl>
                <dt>Cliente</dt>
                <dd><?= e($reserva->getCliente()) ?></dd>

                <dt>Espacio</dt>
                <dd>
                    <a href="/espacios/ver.php?id=<?= e($reserva_espacio->getId()) ?>"><?= e($reserva_espacio->getNombre()) ?></a>
                    <span class="insignia"><?= e($reserva_espacio->descripcionTipo()) ?></span>
                </dd>

                <dt>Fecha</dt>
                <dd><?= e($reserva->getFecha()) ?></dd>

                <dt>Horario</dt>
                <dd><?= e($reserva->getHoraInicio()) ?> - <?= e($reserva->getHoraFin()) ?></dd>

                <dt>Duracion</dt>
                <dd><?= e(number_format($reserva->horas(), 1)) ?> horas</dd>

                <dt>Horario pico</dt>
                <dd><?= $reserva->enHorarioPico() ? 'Si (a partir de las 17:00)' : 'No' ?></dd>

                <dt>Costo estimado</dt>
                <dd><strong>S/ <?= e(number_format($reserva->costoEstimado(), 2)) ?></strong></dd>

                <dt>Tarifa del espacio</dt>
                <dd><?= e($reserva_espacio->datoCalculado()) ?></dd>

                <dt>Identificador</dt>
                <dd>#<?= e($reserva->getId()) ?></dd>
            </dl>
        </div>
    </article>
</section>
