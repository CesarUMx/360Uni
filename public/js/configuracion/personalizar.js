$(document).ready(function () {

    var variables = ["principal", "texto", "nopagada", "pagada", "checkin", "checkout", "noshow", "mantenimiento"];

    for (var v in variables)
        $("#var_" + variables[v]).val($.trim(getComputedStyle(document.documentElement).getPropertyValue('--' + variables[v]))).change(
                function () {
                    var element = document.documentElement;
                    var variable=$(this).data("variable");
                    element.style.setProperty('--'+variable, $("#var_" + variable).val());
                });




$(".subeImagen").each(function(){
    var self=this;
        $("#"+this.id).fileinput({
        maxFilesNum: 1,
        theme: 'fas',
        language: 'es',
        showZoom: false,
        showUpload: true,
        showUploadStats: false,
        showRemove: true,
        uploadUrl: "/ajax/subeImagen/custom",
        autoOrientImage:false,
        uploadExtraData:{
            nombre: self.id+$(self).attr("accept")
        },
        previewSettings: {
            text: {width: "100%", height: "100%"}
        },
        allowedFileExtensions: [$(self).attr("accept").substring(1)]
    })
    .on('fileuploaded', function(event, previewId, index, fileId) {
        actualizaImagen(self.id);
    })
    .on('fileuploaderror', function(event, data, msg) {
        notificacion("Se ha producido un error al subir la imagen","error");
    });
});





});

function guardaCambios(){
    var valores=new Object();
    $("input[type=color]").each(function(){
       valores[$(this).data("variable")]=$(this).val(); 
    });
    

    
    ajaxRequest("personaliza",{variables:valores},ajaxNotification);
}

function actualizaImagen(id) {
    var image = document.getElementById(id+"_preview");
    if(image.complete) {
        var new_image = new Image();
        //set up the new image
        new_image.id = id+"_preview";
        new_image.className = "preview";
        new_image.src = image.src;           
        // insert new image and remove old
        image.parentNode.insertBefore(new_image,image);
        image.parentNode.removeChild(image);
    }

    $("#"+id).fileinput('clear');
}