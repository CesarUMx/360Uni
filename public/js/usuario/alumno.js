function tipo_lista(){
    init_lista(2);
}

function showEspera(){
    showOverlay(true);
    ajaxRequest("importaAlumnos",{},finImportar);
}


function finImportar(id,datos){
    ajaxNotification(id,datos);
    setTimeout(function(){
        showOverlay(false);
    },1500);
    
    $("#list_usuarios").trigger("reloadGrid");
}
