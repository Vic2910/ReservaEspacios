<?php

/**
 * Listado de espacios: miniatura, datos clave, dato calculado polimorfico
 * y acciones de ver / editar / eliminar.
 *
 * Variables esperadas: Espacio[] $espacios
 */
?>
<section>
    <div class="encabezado-pagina">
        <h1>Espacios disponibles</h1>
        <a class="btn" href="/espacios/crear.php">Registrar espacio</a>
    </div>

    <?php if ($espacios === []): ?>
        <p class="vacio">Aun no hay espacios registrados. <a href="/espacios/crear.php">Crea el primer espacio</a>.</p>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                <tr>
                    <th scope="col">Imagen</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Capacidad</th>
                    <th scope="col">Tarifa base</th>
                    <th scope="col">Dato calculado</th>
                    <th scope="col">Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($espacios as $espacio): ?>
                    <tr>
                        <td>
                            <img class="miniatura"
                                 src="<?= e($gestorImagenes->url($espacio->getImagen())) ?>"
                                 alt="Imagen de <?= e($espacio->getNombre()) ?>">
                        </td>
                        <td><?= e($espacio->getNombre()) ?></td>
                        <td><span class="insignia"><?= e($espacio->descripcionTipo()) ?></span></td>
                        <td><?= e($espacio->getCapacidad()) ?> <?= $espacio->getCapacidad() === 1 ? 'persona' : 'personas' ?></td>
                        <td class="numerico">S/ <?= e(number_format($espacio->getTarifaBase(), 2)) ?></td>
                        <td><?= e($espacio->datoCalculado()) ?></td>
                        <td>
                            <div class="acciones">
                                <a class="btn btn-mini btn-secundario" href="/espacios/ver.php?id=<?= e($espacio->getId()) ?>">Ver</a>
                                <a class="btn btn-mini" href="/espacios/editar.php?id=<?= e($espacio->getId()) ?>">Editar</a>
                                <a class="btn btn-mini btn-peligro" href="/espacios/eliminar.php?id=<?= e($espacio->getId()) ?>">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
