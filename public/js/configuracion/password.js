$(document).ready(function(){
    
    $("input").keydown(function (e) {
        if (e.keyCode == 13) {
            cambiaPassword();
        }
    });
});


function cambiaPassword() {
    if($.trim($("#actual").val())==="")
        notificacion("Favor de especificar su cotraseña actual","warning");
    
    else if($("#password1").val()===$("#password2").val())
        if($.trim($("#password1").val())==="")
            notificacion("Favor de especificar su nueva contraseña","warning");
    else {    
        ajaxRequest("cambiaPassword",{password_actual:$("#actual").val(),password_nuevo:$("#password1").val()},ajaxNotification);
        $("input").val("");
    }
    else
        notificacion("Las contraseñas no coinciden","error");

    
}