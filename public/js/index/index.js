$(document).ready(function () {
    
    $("body").fadeIn('slow');
    $("#password").keydown(function (e) {
        if (e.keyCode == 13) {
            login();
        }
    });
    
    
    
    
    (function() {
				// trim polyfill : https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/Trim
				if (!String.prototype.trim) {
					(function() {
						// Make sure we trim BOM and NBSP
						var rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;
						String.prototype.trim = function() {
							return this.replace(rtrim, '');
						};
					})();
				}

				[].slice.call( document.querySelectorAll( 'input.input__field' ) ).forEach( function( inputEl ) {
					// in case the input is already filled..
					if( inputEl.value.trim() !== '' ) {
						classie.add( inputEl.parentNode, 'input--filled' );
					}

					// events:
					inputEl.addEventListener( 'focus', onInputFocus );
					inputEl.addEventListener( 'blur', onInputBlur );
				} );

				function onInputFocus( ev ) {
					classie.add( ev.target.parentNode, 'input--filled' );
				}

				function onInputBlur( ev ) {
					if( ev.target.value.trim() === '' ) {
						classie.remove( ev.target.parentNode, 'input--filled' );
					}
				}
			})();


});


function login() {    
    ajaxRequest("login", {usuario: $("#usuario").val(), password: $("#password").val()}, ajaxNotification,reload_notification);
    $("#password").val("");

}



function reload_notification(datos) {
    if(datos.hasOwnProperty("success"))
        setTimeout(reload,2500);
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
    document.location.href = document.location.href;
}

function showOverlay(hidden) {
    hidden = typeof hidden === "boolean" ? hidden : false;
    var overlay = $("#overlay");
    overlay.attr('aria-hidden', !hidden);
    $("body").toggleClass('noscroll');
    
    setTimeout(function () {
        overlay.scrollTop(0);
    }, 1000);
}


function showReset(){
    $("#form-login").hide();
    $("#form-reset").show();
    
    
}

function showLogin(){
    $("#form-login").show();
    $("#form-reset").hide();
}

function recuperar(){
    showOverlay(true);
    ajaxRequest("setResetPass",{correo:$("#email").val()},finRecuperar);
}

function finRecuperar(id,datos){
    
    showOverlay(false);
    ajaxNotification(id,datos);
    
    
    if(datos.hasOwnProperty("success"))
        showLogin();
    
    $("#email").val("");
}