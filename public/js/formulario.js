/**
 * Mejora progresiva del formulario de espacios:
 * - muestra unicamente los campos propios del tipo seleccionado;
 * - aplica/retira el atributo required segun el tipo activo;
 * - previsualiza la imagen antes de enviarla.
 * Sin JavaScript el formulario sigue funcionando: la validacion del
 * servidor es la que no puede faltar.
 */
(function () {
    'use strict';

    function sincronizar(tipoActivo) {
        document.querySelectorAll('.campos-tipo').forEach(function (fieldset) {
            var activo = fieldset.getAttribute('data-tipo') === tipoActivo;
            fieldset.hidden = !activo;

            fieldset.querySelectorAll('input, select, textarea').forEach(function (campo) {
                campo.required = activo && campo.getAttribute('data-requerido') === '1';
            });
        });
    }

    var selector = document.querySelector('[data-selector-tipo]');

    if (selector) {
        selector.addEventListener('change', function () {
            sincronizar(selector.value);
        });
        sincronizar(selector.value);
    }

    var imagen = document.querySelector('[data-preview]');
    var vistaPrevia = document.querySelector('[data-vista-previa]');

    if (imagen && vistaPrevia) {
        imagen.addEventListener('change', function () {
            var archivo = imagen.files && imagen.files[0];

            if (!archivo) {
                vistaPrevia.hidden = true;
                return;
            }

            var lector = new FileReader();
            lector.onload = function (evento) {
                vistaPrevia.src = evento.target.result;
                vistaPrevia.hidden = false;
            };
            lector.readAsDataURL(archivo);
        });
    }
})();
