var seccion=0;

$(document).ready(function () {
    $("#periodo_seleccionado").attr("disabled", true);
    ajaxRequest("getCarreras", {}, creaComboAutocomplete, "contenedor-carreras");
    init_preguntas();



    $(".seccion-preguntas").click(function () {
        $(".seccion-preguntas").removeClass("active");

        $("#list_preguntas").setGridParam({postData: {seccion: $(this).data("seccion")}}).trigger("reloadGrid");


        seccion=$(this).data("seccion");
        $(this).addClass("active");
       





    });

});



function change_carreras() {
    ajaxRequest("getPEstadisticas", {carrera: $("#carreras").val()}, setEstadisticas);
    $("#list_preguntas").setGridParam({postData: {carrera: $("#carreras").val()}}).trigger("reloadGrid");


}

function setEstadisticas(id, datos) {
    for (var d in datos)
        $("#seccion_" + d).html(datos[d]);
}

function init_preguntas() {
    $("#list_preguntas").jqGrid({
        url: base_url + "ajax/getPreguntasC",
        editurl: base_url + "ajax/setDatosGrid",
        datatype: "json",
        loadonce: false,
        mtype: "POST",
        postData: {
            modelo:"CPregunta",
            carrera: 0,
            seccion: 0
        },

        colNames: ["ID", "Nombre", "Descripción", "Tipo", "Requerido", "Función Profesional", "Semestre", "Categoria"],
        colModel: [
            {name: "id", index: "id", key: true, hidden: true},

            {name: "nombre", index: "nombre", width: 200, editable: true,editoptions:{maxlength:120},editrules: {required: true}},
            {name: "descripcion", index: "descripcion", width: 200, editable: true, edittype: "textarea", editoptions: {maxlength:500,rows: "2", cols: "50"},editrules: {required: true}},
            {name: "id_tipopregunta", index: "id_tipopregunta", width: 350, editable: true, formatter: "select", edittype: "select",
                editoptions: {
                    dataUrl: base_url + "ajax/getCatalogData/CTipopregunta/",
                    buildSelect: function (data, options) {
                        return creaComboGrid(data, {id: "id_tipopregunta"}, "#list_preguntas");
                    },
                    multiple: false,
                    value: getValuesFromSelect({modelo: "CTipopregunta"})

                }
               
            },
            {name: "requerido", index: "requerido", width: 80, editable: true, formatter: "select", edittype: "select", align: "center", editoptions: {value: "true:Sí;false:No"},editrules: {required: true}},

            {name: "id_funcionesp", index: "id_funcionesp", width: 350, editable: true, formatter: "select", edittype: "select",
                editoptions: {
                    dataUrl: base_url + "ajax/getCatalogData/CFuncionesp",
                    buildSelect: function (data, options) {
                        return creaComboGrid(data, {id: "id_funcionesp"}, "#list_preguntas");
                    },
                    multiple: false,
                    value: getValuesFromSelect({modelo: "CFuncionesp"})

                }
            },

            {name: "semestre", index: "semestre", width: 80, editable: true,editrules: {required: true}},
            {name: "id_categoria", index: "id_categoria", width: 350, editable: true, formatter: "select", edittype: "select",
                editoptions: {
                    dataUrl: base_url + "ajax/getCatalogData/CCategoria/",
                    buildSelect: function (data, options) {
                        return creaComboGrid(data, {id: "id_categoria"}, "#list_preguntas");
                    },
                    multiple: false,
                    value: getValuesFromSelect({modelo: "CCategoria"})

                }
               
            }
           



        ],
        shrinkToFit: false,
        pager: "#pager_preguntas",
        rowNum: 200,
        height: 550,
        viewrecords: true,
        hidegrid: false,
        gridview: true,
        rownumbers: true,
        grouping: true,
        groupingView: {
            groupField: ['id_funcionesp',  'semestre'],
            groupColumnShow: [true,  true],
            groupText: ['Función Profesional: <b>{0}</b> Total: <b>{1}</b>',  'Semestre: <b>{0}</b> Total: <b>{1}</b>'],
            groupDataSorted: true,
            groupSummaryPos: ['header',  'header']

        },

        sortname: "id",
        sortorder: "asc",
        autoencode: false,
        loadComplete: function () {
            var lista = $(this).getDataIDs();
            var accion;

            for (i = 0; i < lista.length; i++) {




                accion = '<button class="btn btn-success" title="Aceptar Seleccion" onclick="dAceptarSol(' + lista[i] + ')"><i class="fa-solid fa-check"></i></button> <button class="btn btn-danger" title="Rechazar Solicitud" onclick="dRechazarSol(' + lista[i] + ')"><i class="fa-solid fa-ban"></i></button>';



                $(this).setRowData(lista[i], {acciones: accion});
            }









        }

    }).navGrid('#pager_preguntas',
            // the buttons to appear on the toolbar of the grid
                    {edit: true, add: true, del: false, search: false, refresh: true, view: false, position: "left", cloneToTop: false},
                    {
                        closeAfterEdit: true,
                        reloadAfterSubmit: true,
                        editData: $("#list_preguntas").getGridParam("postData")
                    },
                    // options for the Add Dialog
                            {

                                closeAfterEdit: true,
                                reloadAfterSubmit: true,
                                editData: $("#list_preguntas").getGridParam("postData")
                            },
                            // options for the Delete Dailog
                                    {
                                        width: 300,
                                        delData: {
                                            modelo: function () {
                                                return "CPregunta";
                                            },
                                            id: function () {
                                                return $("#list_preguntas").jqGrid('getGridParam', 'selrow');
                                            }
                                        }

                                    });
                            $('#pager_preguntas_left').css('width', 'auto');







                        }