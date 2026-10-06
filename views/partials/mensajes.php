<?php

/**
 * Mensajes flash: se muestran una sola vez despues de una redireccion
 * (patron Post/Redirect/Get). [PRG]
 */

if (!empty(['flash'])) {
     = ['flash'];
    unset(['flash']);
     = ['tipo'] ?? 'exito';
    ?>
    <p class="alerta alerta-<?= e() ?>"
       role="<?=  === 'error' ? 'alert' : 'status' ?>"
       aria-live="polite"><?= e(['texto'] ?? '') ?></p>
    <?php
}
