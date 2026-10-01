<?php

use App\Factories\EspacioFactory;

/**
 * Formulario compartido de registro y edicion de espacios.
 *
 * Los campos propios de cada tipo se generan recorriendo camposEspecificos():
 * la vista nunca pregunta concreta de que tipo se trata (sin instanceof,
 * sin get_class() y sin condicionales por tipo). [POLIMORFISMO]
 *
 * Variables esperadas:
 * - string   $titulo      encabezado del formulario
 * - string   $accion      URL de destino del POST
 * - string   $textoBoton  etiqueta del boton de envio
 * - array    $datos       valores a conservar (POST anterior o del registro)
 * - array    $errores     errores por campo
 * - string   $tipo        tipo activo en el formulario
 * - ?Espacio $espacioActual registro en edicion o null al crear
 */

$tipoActivo = $tipo;
?>
<section>
    <div class="encabezado-pagina">
        <h1><?= e($titulo) ?></h1>
    </div>

    <?php if (isset($errores['general'])): ?>
        <p class="alerta alerta-error" role="alert"><?= e($errores['general']) ?></p>
    <?php endif; ?>

    <form class="formulario"
          action="<?= e($accion) ?>"
          method="post"
          enctype="multipart/form-data"
          novalidate>
        <?= csrf_input() ?>

        <?php if ($espacioActual !== null): ?>
            <input type="hidden" name="id" value="<?= e($espacioActual->getId()) ?>">
        <?php endif; ?>

        <fieldset>
            <legend>Tipo de espacio</legend>
            <div class="<?= e(clase_error($errores, 'tipo')) ?>">
                <label for="tipo">Tipo <span aria-hidden="true">*</span></label>
                <select id="tipo"
                        name="tipo"
                        data-selector-tipo
                        required
                        <?= isset($errores['tipo']) ? 'aria-invalid="true"' : '' ?>>
                    <?php foreach (EspacioFactory::tipos() as $clave => $etiqueta): ?>
                        <option value="<?= e($clave) ?>"<?= $clave === $tipoActivo ? ' selected' : '' ?>>
                            <?= e($etiqueta) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errores['tipo'])): ?>
                    <small class="mensaje-error"><?= e($errores['tipo']) ?></small>
                <?php endif; ?>
                <small class="ayuda">Al cambiar el tipo se muestran los campos propios de ese espacio.</small>
            </div>
        </fieldset>

        <fieldset>
            <legend>Datos generales</legend>

            <div class="<?= e(clase_error($errores, 'nombre')) ?>">
                <label for="nombre">Nombre <span aria-hidden="true">*</span></label>
                <input type="text"
                       id="nombre"
                       name="nombre"
                       value="<?= e(valor_viejo($datos, 'nombre')) ?>"
                       maxlength="80"
                       required
                       placeholder="Ejemplo: Sala Aula Magna"
                       <?= isset($errores['nombre']) ? 'aria-invalid="true"' : '' ?>>
                <?php if (isset($errores['nombre'])): ?>
                    <small class="mensaje-error"><?= e($errores['nombre']) ?></small>
                <?php endif; ?>
            </div>

            <div class="campo-duo">
                <div class="<?= e(clase_error($errores, 'capacidad')) ?>">
                    <label for="capacidad">Capacidad (personas) <span aria-hidden="true">*</span></label>
                    <input type="number"
                           id="capacidad"
                           name="capacidad"
                           value="<?= e(valor_viejo($datos, 'capacidad')) ?>"
                           min="1"
                           step="1"
                           required
                           <?= isset($errores['capacidad']) ? 'aria-invalid="true"' : '' ?>>
                    <?php if (isset($errores['capacidad'])): ?>
                        <small class="mensaje-error"><?= e($errores['capacidad']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="<?= e(clase_error($errores, 'tarifa_base')) ?>">
                    <label for="tarifa_base">Tarifa base por hora (S/) <span aria-hidden="true">*</span></label>
                    <input type="number"
                           id="tarifa_base"
                           name="tarifa_base"
                           value="<?= e(valor_viejo($datos, 'tarifa_base')) ?>"
                           min="0.01"
                           step="0.01"
                           required
                           <?= isset($errores['tarifa_base']) ? 'aria-invalid="true"' : '' ?>>
                    <?php if (isset($errores['tarifa_base'])): ?>
                        <small class="mensaje-error"><?= e($errores['tarifa_base']) ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </fieldset>

        <?php foreach (EspacioFactory::tipos() as $claveTipo => $etiquetaTipo): ?>
            <?php
            $activa = $claveTipo === $tipoActivo;
            $definiciones = EspacioFactory::camposEspecificos($claveTipo);
            ?>
            <fieldset class="campos-tipo" data-tipo="<?= e($claveTipo) ?>"<?= $activa ? '' : ' hidden' ?>>
                <legend>Campos de <?= e($etiquetaTipo) ?></legend>

                <?php foreach ($definiciones as $definicion): ?>
                    <?php
                    $campo = (string) $definicion['name'];
                    $valor = valor_viejo($datos, $campo);
                    $error = error_de_campo($errores, $campo);
                    $requerido = !empty($definicion['requerido']);
                    $atributos = ($requerido && $activa ? ' required' : '')
                        . ($requerido ? ' data-requerido="1"' : '');
                    $idCampo = 'campo-' . $campo;
                    ?>
                    <div class="<?= e($error !== null ? 'campo campo-error' : 'campo') ?>">
                        <label for="<?= e($idCampo) ?>"><?= e($definicion['etiqueta']) ?><?= $requerido ? ' <span aria-hidden="true">*</span>' : '' ?></label>

                        <?php if (($definicion['tipo'] ?? 'text') === 'select'): ?>
                            <select id="<?= e($idCampo) ?>"
                                    name="<?= e($campo) ?>"
                                    <?= $atributos ?>
                                    <?= $error !== null ? 'aria-invalid="true"' : '' ?>>
                                <option value="">-- Seleccione --</option>
                                <?php foreach (($definicion['opciones'] ?? []) as $claveOpcion => $etiquetaOpcion): ?>
                                    <option value="<?= e($claveOpcion) ?>"<?= $valor === (string) $claveOpcion ? ' selected' : '' ?>>
                                        <?= e($etiquetaOpcion) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="<?= e($definicion['tipo'] ?? 'text') ?>"
                                   id="<?= e($idCampo) ?>"
                                   name="<?= e($campo) ?>"
                                   value="<?= e($valor) ?>"
                                   <?= isset($definicion['max']) ? 'maxlength="' . (int) $definicion['max'] . '"' : '' ?>
                                   <?= $atributos ?>
                                   <?= $error !== null ? 'aria-invalid="true"' : '' ?>>
                        <?php endif; ?>

                        <?php if ($error !== null): ?>
                            <small class="mensaje-error"><?= e($error) ?></small>
                        <?php endif; ?>
                        <?php if (!empty($definicion['ayuda'])): ?>
                            <small class="ayuda"><?= e($definicion['ayuda']) ?></small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>

        <fieldset>
            <legend>Fotografia del espacio</legend>

            <?php if ($espacioActual !== null && $espacioActual->getImagen() !== null): ?>
                <div class="campo">
                    <span class="ayuda">Imagen actual:</span>
                    <img class="imagen-actual"
                         src="<?= e($gestorImagenes->url($espacioActual->getImagen())) ?>"
                         alt="Imagen actual de <?= e($espacioActual->getNombre()) ?>">
                    <small class="ayuda">Si subes otra imagen, la actual sera reemplazada y eliminada del servidor.</small>
                </div>
            <?php endif; ?>

            <div class="<?= e(clase_error($errores, 'imagen')) ?>">
                <label for="imagen">Imagen (JPG, PNG o WEBP, maximo 2 MB)</label>
                <input type="file"
                       id="imagen"
                       name="imagen"
                       accept="image/jpeg,image/png,image/webp"
                       data-preview>
                <?php if (isset($errores['imagen'])): ?>
                    <small class="mensaje-error"><?= e($errores['imagen']) ?></small>
                <?php else: ?>
                    <small class="ayuda">Opcional: si no se envia imagen, se mostrara la imagen por defecto.</small>
                <?php endif; ?>
            </div>

            <img class="vista-previa" data-vista-previa alt="Vista previa de la imagen seleccionada" hidden>
        </fieldset>

        <div class="formulario-acciones">
            <button class="btn" type="submit"><?= e($textoBoton) ?></button>
            <a class="btn btn-secundario" href="/espacios/index.php">Cancelar</a>
        </div>
    </form>
</section>
<script src="/js/formulario.js" defer></script>
