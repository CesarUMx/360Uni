$(document).ready(function () {
    ajaxRequest("getClasificacionPlantillas/", {}, autocompleta, "#clasificacion");





    $('#editor').summernote({
        lang: 'es-ES',
        height: 750,
        placeholder: 'Por favor escriba el template...',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'superscript', 'subscript', 'strikethrough', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']], // Still buggy
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video', 'hr']],
            ['view', ['fullscreen', 'codeview']],
            ['help', ['help']],
            ['myoptions', ['tags', 'save']]

        ],
        buttons: {
            tags: creaBotonEtiquetas,
            save: creaBotonGuardar
        },
        disableResizeEditor: true
    });
$('.note-statusbar').remove(); 

    $('#editor').summernote('disable');





});

function setPlantillas() {
    ajaxRequest("getPlantillasDisponibles/", {dir: $("#clasificacion").val()}, autocompleta, "#plantilla");
}


function autocompleta(id, data) {
    $(id).autocomplete({
        source: data,
        focus: function (event, ui) {
            $(this).val(ui.item.label);
            return false;
        },
        select: function () {
            $(this).trigger("change");
        }

    });
}

function setEditor(id, datos) {
    if (datos.hasOwnProperty("html"))
        $("#" + id).summernote('code', datos.html);
    else
        ajaxNotification(id, datos);
}

function creaBotonEtiquetas(context) {

    var ui = $.summernote.ui;

    var button = ui.button({
        contents: '<i class="fa fa-tags"/>',
        tooltip: 'Agregar campo',
        click: function () {
            // invoke insertText method with 'hello' on editor module.
            context.invoke('editor.insertText', '%_campo_%');
        }
    });

    return button.render();   // return button as jquery object 
}

function creaBotonGuardar(context) {

    var ui = $.summernote.ui;

    var button = ui.button({
        contents: '<i class="fa fa-save"/>',
        tooltip: 'Guardar Plantilla',
        click: function () {
            ajaxRequest("setPlantilla", {grupo: $("#clasificacion").val(), plantilla: $("#plantilla").val(), html: context.invoke('code')}, ajaxNotification);




        }
    });

    return button.render();   // return button as jquery object 
}


function habilitaEditor() {
    if ($("#clasificacion").val() != "" && $("#plantilla").val() != "") {
        ajaxRequest("getPlantilla", {grupo: $("#clasificacion").val(), plantilla: $("#plantilla").val()}, setEditor, "editor");
        $('#editor').summernote('enable');
    }
    else
        $('#editor').summernote('disable');
}
