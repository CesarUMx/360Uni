$(document).ready(function(){
    
    
    
    
    if(!valido) {
        if(completo)
            $("#form_completo").remove();
        else
            $("#form_valido").remove();
    
    
    
        ajaxRequest("getAllDatos/CCarrera",{},creaComboDAutocomplete,"cont-carreras");
        ajaxRequest("getCatalogData/CFuncionesp",{campo:"id_carrera",valor:0},creaComboAutocomplete,"cont-funcionesp");
    
    
        showOverlay(true);
    }
    else {
        $(".form_alumno").remove();
        showOverlay(false);
    }
    
    
});


function change_carreras(){
    ajaxRequest("getCatalogData/CFuncionesp",{campo:"id_carrera",valor:$("#carreras").val()},actualizaFP);
    
    $("#semestre").html('<option value="" selected disabled>Seleccione una opción</option>');
    
    
    
    for(var i=5;i<=8;i++)
        $("#semestre").append('<option value="'+i+'">'+i+'° Semestre</option>');
    
}


function actualizaFP(id,datos){
    $("#funcionesp").html('<option value="" selected disabled>Seleccione una opción</option>');
    
    for(var d in datos)
        $("#funcionesp").append('<option value="'+d+'">'+datos[d]+'</option>');
    
    
    $("#funcionesp").trigger('chosen:updated');
    
}

function guardaDatos() {
    
    var nombre=$.trim($("#nombre_alumno").val());
    var carrera=parseInt($("#carreras").val());
    var funcionp=parseInt($("#funcionesp").val());
    var semestre=parseInt($("#semestre").val());
    
    if(nombre=="")
        notificacion("Favor de ingresar su nombre","error");
    else if (isNaN(carrera)||carrera==0)
        notificacion("Favor de ingresar su carrera","error");
    else if (isNaN(funcionp)||funcionp==0)
        notificacion("Favor de ingresar su función profesional","error");
    else if (isNaN(semestre)||semestre==0)
        notificacion("Favor de ingresar su semestre","error");
    else
        ajaxRequest("guardaDAlumno",{nombre:nombre,funcionp:funcionp,carrera:carrera,semestre:semestre},ajaxNotification,reload);
}