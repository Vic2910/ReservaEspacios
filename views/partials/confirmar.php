<?php

/**
 * Confirmacion de eliminacion: el borrado se ejecuta unicamente mediante
 * POST con token CSRF; el GET solo muestra esta pantalla. [SEGURIDAD] [CRUD-DELETE]
 *
 * Variables esperadas:
 * - string $titulo        encabezado
 * - string $nombreRegistro nombre del registro que se va a eliminar
 * - array  $filas         pares etiqueta => valor a mostrar
 * - string $accion        URL que recibe el POST
 * - string $id            identificador del registro
 * - string $urlVolver     enlace de cancelacion
 */
?>
<section class="confirmacion">
    <div class="encabezado-pagina">
        <h1><?= e($titulo) ?></h1>
    </div>

    <div class="tarjeta">
        <p class="alerta alerta-advertencia">
            Vas a eliminar <strong><?= e($nombreRegistro) ?></strong>.
            Esta accion no se puede deshacer y tambien borrara la imagen asociada.
        </p>

        <form action="<?= e($accion) ?>" method="post">
            <?= csrf_input() ?>
            <input type="hidden" name="id" value="<?= e($id) ?>">

            <dl>
                <?php foreach ($filas as $etiqueta => $valor): ?>
                    <dt><?= e($etiqueta) ?></dt>
                    <dd><?= e($valor) ?></dd>
                <?php endforeach; ?>
            </dl>

            <div class="formulario-acciones">
                <button class="btn btn-peligro" type="submit">Si, eliminar</button>
                <a class="btn btn-secundario" href="<?= e($urlVolver) ?>">Cancelar</a>
            </div>
        </form>
    </div>
</section>
