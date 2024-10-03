$(document).ready(function () {
    init_catalogo();
    $('#pager_catalogo_left').css('width', 'auto');

    $("#form_catalogo").submit(function () {
        return false;
    });
    
    redraw_grid();

});


function limpiaCatalogo() {
    $("#form_catalogo input[type=text]").val("");
    $("#form_catalogo input[type=number]").val("");
    $("#form_catalogo select").val("");
    $("#form_catalogo textarea").val("");
    $("#form_catalogo input[type=checkbox]").prop("checked", false);
}

function dAgregaRegistro() {
    limpiaCatalogo();
    $("#catalogo_id").val("");
    $("#dialog_catalogo").modal("show");
}

function dEditaRegistro(rowid) {
    var rowData = $("#list_catalogo").getRowData(rowid);
    limpiaCatalogo();
    $("#catalogo_id").val(rowid);
    for (var r in rowData) {
        $input = $("#" + r);
        if ($input.length > 0) {



            if ($input.attr("type") == "checkbox") 
                $input.prop("checked", rowData[r] == "true" ? true : false);
                
            

            else
                $input.val(rowData[r]);


            if ($input.is("select"))
                $input.trigger('chosen:updated');

        }
    }

    $("#dialog_catalogo").modal("show");
}

function dEliminaRegistro(rowid) {
    dialogo_confirmacion("Confirmación", "¿En realidad desea eliminar el registro seleccionado?", {"Eliminar": {"funcion": eliminaRegistro, "parametros": rowid}});
}

function eliminaRegistro(rowid) {
    var postData = $("#list_catalogo").getGridParam("postData");
    var datos={oper:"del",modelo:postData.modelo,id:rowid};
    ajaxRequest("setDatosGrid", datos, finGuardaCatalogo);

}

function autoForm(campos) {


    for (var c in campos) {
        var requerido = campos[c]["editrules"]["required"] ? "required" : "";
        $input = $("#" + campos[c]["name"]);

        if ($input.length > 0) {



            if (campos[c]["edittype"] != "checkbox")
                $input.addClass(requerido);



            if (campos[c]["edittype"] == "text")
                $input.attr("maxlength", campos[c]["editoptions"]["maxlength"]);




        }



    }

}


function guardaCatalogo() {

    var form = $("#form_catalogo");
    var postData = $("#list_catalogo").getGridParam("postData");
    var datos = getValoresCatalogo();
    var id = $("#catalogo_id").val();
    
    datos["modelo"]=postData.modelo;
    datos["oper"]= id=== "" ? "add" : "edit";
    datos["id"]=id;

$("error").removeClass("error");

    form.validate().settings.ignore = ":disabled";

    if (validaCatalogo()) {
        ajaxRequest("setDatosGrid", datos, finGuardaCatalogo);
        $("#dialog_catalogo").modal("hide");
        $("catalogo_id").val("");

    }
    else
        notificacion("Favor de llenar todos los campos requeridos","error");
}

function finGuardaCatalogo(id, datos) {
    var message;
    try {

        message = datos[0];
        ;
    } catch (exception) {

    }


    if (message.lastIndexOf("Ok", 0) === 0)
        notificacion("Registro insertado correctamente", "success");
    else
        notificacion(message, "error");


    $("#list_catalogo").trigger("reloadGrid")

}

function init_tabla(id, datos) {

    if (datos.hasOwnProperty("error"))
        ajaxNotification(id, datos);
    else {
        autoForm(datos.colmodel);

        datos.colmodel.push({"label": "Acciones", "name": "acciones", "editable": false});
        $("#list_catalogo").jqGrid({
            url: base_url + "ajax/getDatosGrid",
            editurl: base_url + "ajax/setDatosGrid",
            postData: {
                modelo: datos.catalogo
            },
            datatype: "json",
            loadonce: false,
            height: 450,
            mtype: "POST",
            colModel: datos.colmodel,
            pager: "#pager_catalogo",
            shrinkToFit: false,
            multiselect: false,
            rowNum: 100,
            rowList: [100],
            sortname: "id",
            sortorder: "asc",
            rownumbers: true,
            viewrecords: true,

            autoencode: false,
            grouping: false,

            loadComplete: loadCompleteCatalogo
        }).navGrid("#pager_catalogo",
                // the buttons to appear on the toolbar of the grid
                        {edit: false, add: false, del: false, search: false, refresh: true, view: false, position: "left", cloneToTop: false},
                        // options for the Edit Dialog
                                {

                                    editData: $("#list_catalogo").getGridParam("postData"),

                                    errorTextFormat: errorTextFormat,

                                },
                                // options for the Add Dialog
                                        {

                                            editData: $("#list_catalogo").getGridParam("postData"),
                                            errorTextFormat: errorTextFormat

                                        },
                                        // options for the Delete Dailog
                                                {
                                                    width: 300,
                                                    delData: {
                                                        modelo: function () {
                                                            return datos.catalogo;
                                                        },
                                                        id: function () {
                                                            return $("#list_catalogo").jqGrid('getGridParam', 'selrow')
                                                        }
                                                    }

                                                });
                                    }
                                    
                                    
                                    
                                    
                                    $("#pager_catalogo_left").css('width', 'auto');

                        }