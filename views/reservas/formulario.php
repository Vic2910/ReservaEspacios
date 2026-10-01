<?php

/**
 * Formulario compartido de registro y edicion de reservas.
 *
 * Variables esperadas:
 * - string    $titulo
 * - string    $accion
 * - string    $textoBoton
 * - array     $datos     valores conservados
 * - array     $errores   errores por campo
 * - Espacio[] $espacios  opciones del selector
 * - ?Reserva  $reservaActual registro en edicion o null al crear
 */
?>
<section>
    <div class="encabezado-pagina">
        <h1><?= e($titulo) ?></h1>
    </div>

    <?php if (isset($errores['general'])): ?>
        <p class="alerta alerta-error" role="alert"><?= e($errores['general']) ?></p>
    <?php endif; ?>

    <form class="formulario" action="<?= e($accion) ?>" method="post" novalidate>
        <?= csrf_input() ?>

        <?php if ($reservaActual !== null): ?>
            <input type="hidden" name="id" value="<?= e($reservaActual->getId()) ?>">
        <?php endif; ?>

        <fieldset>
            <legend>Datos de la reserva</legend>

            <div class="<?= e(clase_error($errores, 'espacio_id')) ?>">
                <label for="espacio_id">Espacio <span aria-hidden="true">*</span></label>
                <select id="espacio_id" name="espacio_id" required
                    <?= isset($errores['espacio_id']) ? 'aria-invalid="true"' : '' ?>>
                    <option value="">-- Seleccione un espacio --</option>
                    <?php foreach ($espacios as $espacio): ?>
                        <option value="<?= e($espacio->getId()) ?>"
                            <?= valor_viejo($datos, 'espacio_id') === (string) $espacio->getId() ? 'selected' : '' ?>>
                            <?= e($espacio->getNombre()) ?> (<?= e($espacio->descripcionTipo()) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errores['espacio_id'])): ?>
                    <small class="mensaje-error"><?= e($errores['espacio_id']) ?></small>
                <?php endif; ?>
            </div>

            <div class="<?= e(clase_error($errores, 'cliente')) ?>">
                <label for="cliente">Cliente <span aria-hidden="true">*</span></label>
                <input type="text"
                       id="cliente"
                       name="cliente"
                       value="<?= e(valor_viejo($datos, 'cliente')) ?>"
                       maxlength="80"
                       required
                       placeholder="Nombre de la persona o area que reserva"
                    <?= isset($errores['cliente']) ? 'aria-invalid="true"' : '' ?>>
                <?php if (isset($errores['cliente'])): ?>
                    <small class="mensaje-error"><?= e($errores['cliente']) ?></small>
                <?php endif; ?>
            </div>

            <div class="<?= e(clase_error($errores, 'fecha')) ?>">
                <label for="fecha">Fecha <span aria-hidden="true">*</span></label>
                <input type="date"
                       id="fecha"
                       name="fecha"
                       value="<?= e(valor_viejo($datos, 'fecha')) ?>"
                       required
                    <?= isset($errores['fecha']) ? 'aria-invalid="true"' : '' ?>>
                <?php if (isset($errores['fecha'])): ?>
                    <small class="mensaje-error"><?= e($errores['fecha']) ?></small>
                <?php endif; ?>
            </div>

            <div class="campo-duo">
                <div class="<?= e(clase_error($errores, 'hora_inicio')) ?>">
                    <label for="hora_inicio">Hora de inicio <span aria-hidden="true">*</span></label>
                    <input type="time"
                           id="hora_inicio"
                           name="hora_inicio"
                           value="<?= e(valor_viejo($datos, 'hora_inicio')) ?>"
                           required
                        <?= isset($errores['hora_inicio']) ? 'aria-invalid="true"' : '' ?>>
                    <?php if (isset($errores['hora_inicio'])): ?>
                        <small class="mensaje-error"><?= e($errores['hora_inicio']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="<?= e(clase_error($errores, 'hora_fin')) ?>">
                    <label for="hora_fin">Hora de fin <span aria-hidden="true">*</span></label>
                    <input type="time"
                           id="hora_fin"
                           name="hora_fin"
                           value="<?= e(valor_viejo($datos, 'hora_fin')) ?>"
                           required
                        <?= isset($errores['hora_fin']) ? 'aria-invalid="true"' : '' ?>>
                    <?php if (isset($errores['hora_fin'])): ?>
                        <small class="mensaje-error"><?= e($errores['hora_fin']) ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <small class="ayuda">La hora de fin debe ser posterior al inicio y el horario no puede traslaparse con otra reserva del mismo espacio y dia.</small>
        </fieldset>

        <div class="formulario-acciones">
            <button class="btn" type="submit"><?= e($textoBoton) ?></button>
            <a class="btn btn-secundario" href="/reservas/index.php">Cancelar</a>
        </div>
    </form>
</section>
