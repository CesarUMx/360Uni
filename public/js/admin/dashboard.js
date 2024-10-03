var servidor;
var servidor2;



var super_total;
var super_total2;


$(document).ready(function () {
    ajaxRequest("getIndicadores", {}, llenaIndicadores);
    $("#periodo_seleccionado").attr("disabled", true);

});


function iniciaNotificaciones() {

    $("#btn_startN").toggleClass("d-none");
    $("#btn_stopN").toggleClass("d-none");
    $("#progress_envios").parent().toggleClass("d-none");

    ajaxRequest("iniciaEnvios", {}, ajaxNotification);


    setTimeout(function () {

        servidor = new EventSource("/admin/getStatusEnvio");
        servidor.onmessage = function (event) {
            var avance = parseInt(event.data);



            if (!isNaN(avance) && avance !== 0) {

                if (avance != 99999) {
                    var porcentaje = Math.round((super_total-avance) * 100 / super_total);
                    
                   
                    
                    if (isNaN(porcentaje))
                        porcentaje = 0;

                    $("#indicador_pendientes").html(avance);

                    $("#progress_envios").attr("aria-valuenow", porcentaje).css("width", porcentaje + "%").html(porcentaje + "%");




                }
            } else
                stopNotificaciones();


        };






    }, 1000);

}

function stopNotificaciones() {
    if (typeof (servidor) !== "undefined")
        servidor.close();

    ajaxRequest("finEnvios", {}, ajaxNotification);
    $("#btn_startN").toggleClass("d-none");
    $("#btn_stopN").toggleClass("d-none");
    $("#progress_envios").parent().toggleClass("d-none");
    ajaxRequest("getIndicadores", {}, llenaIndicadores);

}





function iniciaInvitaciones() {

    $("#btn_startI").toggleClass("d-none");
    $("#btn_stopI").toggleClass("d-none");
    $("#progress_invitaciones").parent().toggleClass("d-none");

    ajaxRequest("iniciaEnvios2", {}, ajaxNotification);


    setTimeout(function () {

        servidor = new EventSource("/admin/getStatusEnvio2");
        servidor.onmessage = function (event) {
            var avance = parseInt(event.data);



            if (!isNaN(avance) && avance !== 0) {

                if (avance != 99999) {
                    var porcentaje = Math.round((super_total-avance) * 100 / super_total);
                    
                   
                    
                    if (isNaN(porcentaje))
                        porcentaje = 0;

                    $("#indicador_invitaciones").html(avance);

                    $("#progress_invitaciones").attr("aria-valuenow", porcentaje).css("width", porcentaje + "%").html(porcentaje + "%");




                }
            } else
                stopNotificaciones();


        };






    }, 1000);

}

function stopInvitaciones() {
    if (typeof (servidor2) !== "undefined")
        servidor2.close();

    ajaxRequest("finEnvios2", {}, ajaxNotification);
    $("#btn_startI").toggleClass("d-none");
    $("#btn_stopI").toggleClass("d-none");
    $("#progress_invitaciones").parent().toggleClass("d-none");
    ajaxRequest("getIndicadores", {}, llenaIndicadores);

}






function llenaIndicadores(id, datos) {

    for (var d in datos)
        $("#indicador_" + d).html(datos[d]);

    if (datos.pendientes == 0)
        $("#btn_startN").toggleClass("d-none");
    else {
        super_total = datos.pendientes;
        $("#progress_envios").attr("aria-valuenow", 0).css("width", 0 + "%").html(0 + "%");
    }
    
    if (datos.invitaciones == 0)
        $("#btn_startI").toggleClass("d-none");
    else {
        super_total2 = datos.invitaciones;
        $("#progress_invitaciones").attr("aria-valuenow", 0).css("width", 0 + "%").html(0 + "%");
    }
    
    
    
    
    
    
    
    
}