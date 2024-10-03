/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function mensajeRecibido(params) {
    var dest=params.destinatarios.split(",");
    if(dest.indexOf(usuario)!=-1)
        toastr.info('El usuario '+params.remitente+' te ha enviado un mensaje. <a href="'+base_url+'mensajes/recibidos">Ver mensaje</a>', 'Mensaje Recibido', {timeOut: 5000});
}

function push(params) {
    toastr[params.tipo](params.mensaje,{"closeButton": true,"showDuration": "0","timeOut": "0"});
}

function mensajePersonal(params){
    if(params.destinatario==usuario)
        toastr[params.tipo](params.mensaje);
}

function ws_cierraSesion() {
    
}