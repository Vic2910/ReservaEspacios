<?php

/**
 * Mensajes flash: se muestran una sola vez despues de una redireccion
 * (patron Post/Redirect/Get). [PRG]
 */

if (!empty($_SESSION['flash'])) {
    $mensaje = $_SESSION['flash'];
    unset($_SESSION['flash']);
    ?>
    <p class="alerta alerta-<?= e($mensaje['tipo'] ?? 'exito') ?>" role="status"><?= e($mensaje['texto'] ?? '') ?></p>
    <?php
}
