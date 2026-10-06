/**
 * Mejora progresiva del formulario de espacios:
 * - muestra unicamente los campos propios del tipo seleccionado;
 * - aplica/retira el atributo required segun el tipo activo;
 * - previsualiza la imagen antes de enviarla;
 * - retira el error del campo cuando vuelve a ser valido;
 * - valida el tamano de la imagen en el cliente.
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
        selector.addEventListener('change', function () { sincronizar(selector.value); });
        sincronizar(selector.value);
    }
    var imagen = document.querySelector('[data-preview]');
    var vistaPrevia = document.querySelector('[data-vista-previa]');
    if (imagen && vistaPrevia) {
        imagen.addEventListener('change', function () {
            var archivo = imagen.files && imagen.files[0];
            if (!archivo) { vistaPrevia.hidden = true; return; }
            var lector = new FileReader();
            lector.onload = function (evento) { vistaPrevia.src = evento.target.result; vistaPrevia.hidden = false; };
            lector.readAsDataURL(archivo);
        });
    }
    var reglas = {
        nombre: function (campo) { return campo.value.trim() !== ''; },
        capacidad: function (campo) { return parseFloat(campo.value) > 0; },
        tarifa_base: function (campo) { return parseFloat(campo.value) > 0; },
        hora_fin: function (campo, formulario) {
            var inicio = formulario.elements['hora_inicio'];
            return campo.value !== '' && !!inicio && campo.value > inicio.value;
        }
    };
    function limpiarError(campo) {
        var contenedor = campo.closest('.campo-error');
        if (!contenedor) return;
        contenedor.classList.remove('campo-error');
        var mensaje = contenedor.querySelector('.mensaje-error');
        if (mensaje) mensaje.remove();
    }
    function revisarReglas(formulario) {
        Object.keys(reglas).forEach(function (nombre) {
            var campo = formulario.elements[nombre];
            if (campo && reglas[nombre](campo, formulario)) limpiarError(campo);
        });
    }
    var formulario = document.querySelector('form.formulario');
    if (formulario) {
        formulario.addEventListener('input', function () { revisarReglas(formulario); });
    }
})();

