$(document).ready(function () {

    $("body").fadeIn('slow');
    $("#password2").keydown(function (e) {
        if (e.keyCode == 13) {
            cambiaPassword();
        }
    });




    (function () {
        // trim polyfill : https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/Trim
        if (!String.prototype.trim) {
            (function () {
                // Make sure we trim BOM and NBSP
                var rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;
                String.prototype.trim = function () {
                    return this.replace(rtrim, '');
                };
            })();
        }

        [].slice.call(document.querySelectorAll('input.input__field')).forEach(function (inputEl) {
            // in case the input is already filled..
            if (inputEl.value.trim() !== '') {
                classie.add(inputEl.parentNode, 'input--filled');
            }

            // events:
            inputEl.addEventListener('focus', onInputFocus);
            inputEl.addEventListener('blur', onInputBlur);
        });

        function onInputFocus(ev) {
            classie.add(ev.target.parentNode, 'input--filled');
        }

        function onInputBlur(ev) {
            if (ev.target.value.trim() === '') {
                classie.remove(ev.target.parentNode, 'input--filled');
            }
        }
    })();


});


function cambiaPassword() {
    var usuario = $.trim($("#usuario").val());
    var password = $.trim($("#password").val());
    var password2 = $.trim($("#password2").val());
    var token = $.trim($("#token").val());

    if (usuario == "")
        notificacion("Por favor escriba su correo electrónico", "warning");
    else if (password == "")
        notificacion("Por favor escriba su nueva contraseña", "warning");
    else if (password2 == "")
        notificacion("Por favor escriba la confirmación de nueva contraseña", "warning");
    else if (password != password2)
        notificacion("Las contraseñas no coinciden. Favor de revisarlo", "warning");
    else if (token == "")
        notificacion("Token de seguridad incorrecto. Favor de reintentar", "error");
    else {

        ajaxRequest("setPassword", {usuario: usuario, password: password,token:token}, ajaxNotification, reload_notification);
        $("#usuario").val("");
        $("#password").val("");
        $("#password2").val("");
    }

}



function reload_notification(datos) {
    if (datos.hasOwnProperty("success"))
        setTimeout(reload, 2500);
}



function ajaxRequest(accion, params, callback, id_destino) {
    //validar accion
    params = typeof params === 'object' ? params : new Object();
    id_destino = id_destino ? id_destino : "";



    $.ajax({
        url: base_url + 'ajax/' + accion,
        type: "post",
        data: params,
        success: function (datos) {
            try {
                datos = JSON.parse(datos);
                if (typeof callback === "function")
                    callback.call(null, id_destino, datos);
                else
                    return datos;
            } catch (exception) {
                notificacion("Se ha producido la siguiente excepción: " + exception, "error");

            }
        }
    });
}


function ajaxNotification(id, datos) {
    for (var prop in datos)
        notificacion(datos[prop], prop);

    if (typeof id === "function")
        id.call(null, datos);
}

function notificacion(message, type, close) {
    message = message ? message : 'No se ha recibido mensaje!';
    type = type ? type : 'success';

    close = typeof close == 'function' ? close : function () {
        return false;
    };

    toastr[type](message, '', {onHidden: close, "positionClass": "toast-top-full-width"});
}

function reload() {
    document.location.href = "/";
}