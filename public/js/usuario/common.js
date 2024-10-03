$(document).ready(function () {
    tipo_lista();
});



function redraw_grid() {
    var $grid = $("#list_usuarios");
    $grid.jqGrid("setGridWidth", Math.max($grid.closest(".ui-jqgrid").width() - 320, $(window).width() - 320), true);
    $("#pager_usuarios").css("width", Math.min($grid.closest(".ui-jqgrid").width(), $(window).width()));
    $grid.jqGrid("setGridHeight", $(window).height() - 280, true);
}

function confirmaRestablecer(id) {
    dialogo_confirmacion("Confirmación", "¿En realidad desea restablecer el password del usuario? Se enviará un correo para realizar el cambio", {"Restablecer": {"funcion": restablecerP, "parametros": id}}); 
}

function restablecerP(id){
    ajaxRequest("restablecePassword",{id:id},ajaxNotification);
}

function formComplete() {
    var lista = $(this).getDataIDs();
    var accion;
    for (i = 0; i < lista.length; i++) {

        accion = '<button class="btn btn-mondragon" title="Restablecer Password" onclick="confirmaRestablecer(' + lista[i] + ')"><i class="fas fa-key"></i></button>';
        $(this).setRowData(lista[i], { acciones: accion});
       

    }
}


function dUsuario(){
    $("#dialog_usuario").modal("show");
}


function init_lista(tipo) {
    $("#list_usuarios").jqGrid({
        url: base_url + "ajax/getDatosGrid",
        editurl: base_url + "ajax/setDatosGrid",
        postData: {
            valor: tipo,
            campo: "id_rol",
            modelo: "Usuario"

        },
        datatype: "json",
        loadonce: false,
        height: 300,
        mtype: "POST",
        colNames: ["Correo", "Nombre","Notificado","Activo", "Acciones"],
        colModel: [
            {name: "correo", index: "correo", width: 150, sortable: false, editable: true, align: "center", editrules: {required: true}, editoptions: {maxlength: 120}},
            {name: "nombre", index: "nombre", width: 150, sortable: false, editable: true, align: "center", editrules: {required: true}, editoptions: {maxlength: 120}},
            {name: "enviado", index: "enviado", width:100,formatter:'select', editoptions:{value:"true:Sí;false:No"}},
            {name: "activo", index: "activo", width:100,formatter:'select', editoptions:{value:"true:Sí;false:No"}},
            {name: "acciones", index: "acciones", width: 200, sortable: false, editable: false}
        ],
        pager: "#pager_usuarios",
        shrinkToFit: true,
        multiselect: false,
        rowNum: 100,
        rowList: [100,200],
        sortname: "id",
        sortorder: "asc",
        rownumbers: true,
        grouping: false,
        viewrecords: true,
        autoencode: false,
        loadComplete:formComplete

    }).navGrid("#pager_usuarios", {edit: true, add: true, del: true, search: false, refresh: true, view: false, position: "left", cloneToTop: false},
            {
                recreateForm: true,
                closeAfterEdit: true,
                modal: true,
                reloadAfterSubmit: true,

                errorTextFormat: errorTextFormat,
                afterSubmit: afterSubmitDelete,
                editData: $("#list_usuarios").getGridParam("postData")
            },
            {
                recreateForm: true,
                closeAfterAdd: true,
                modal: true,
                reloadAfterSubmit: true,
                editData: $("#list_usuarios").getGridParam("postData"),

                afterSubmit: afterSubmitDelete,
                errorTextFormat: errorTextFormat
            },
            {
                width: 300,
                delData: {
                    modelo: function () {
                        return "EventoSala";
                    },
                    id: function () {
                        return $("#list_usuarios").jqGrid('getGridParam', 'selrow')
                    }
                },
                errorTextFormat: errorTextFormat
            }
    );



    $('#pager_usuarios_left').css('width', 'auto');

    $(window).on("load", redraw_grid);
    $(window).on("resize", redraw_grid);
    redraw_grid();
}