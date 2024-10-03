function tipo_lista(){
    init_lista(4);
    init_carreras();
}



function formComplete() {
    var lista = $(this).getDataIDs();
    var accion;
    for (i = 0; i < lista.length; i++) {

        accion = '<button class="btn btn-mondragon" title="Restablecer Password" onclick="confirmaRestablecer(' + lista[i] + ')"><i class="fas fa-key"></i></button> <button class="btn btn-mondragon2" title="Ver Carreras" onclick="dUsuario(' + lista[i] + ')"><i class="fas fa-puzzle-piece"></i></button>';
        $(this).setRowData(lista[i], { acciones: accion});
       

    }
}


function dUsuario(id){
    $("#list_carreras").setGridParam({postData: {valor:id}}).trigger("reloadGrid");
    
    $("#dialog_usuario").modal("show");
}


function init_carreras() {
    $("#list_carreras").jqGrid({
        url: base_url + "ajax/getDatosGrid",
        editurl: base_url + "ajax/setDatosGrid",
        datatype: "json",
        loadonce: false,
        mtype: "POST",
        postData: {
            modelo:"DetalleValidador",
            campo: "id_validador",
            valor: 0
        },
        colNames: ["ID", "Carrera"],
        colModel: [
            {name: "id", index: "id", key: true, hidden: true},

            
            {name: "id_carrera", index: "id_carrera", width: 250, editable: true, formatter: "select", edittype: "select",
                editoptions: {
                    dataUrl: base_url + "ajax/getCatalogData/CCarrera",
                    buildSelect: function (data, options) {
                        return creaComboGrid(data, {id: "id_carrera"}, "#list_carreras");
                    },
                    multiple: false,
                    value: getValuesFromSelect({modelo: "CCarrera"})

                },
                editrules: {required: true}
            }
        ],
        shrinkToFit: true,
        pager: "#pager_carreras",
        rowNum: 100,
        height: 450,
        viewrecords: true,
        hidegrid: false,
        gridview: true,
        rownumbers: true,
        grouping: false,
        sortname: "id",
        sortorder: "asc",
        autoencode: true
       

    }).navGrid('#pager_carreras',
            // the buttons to appear on the toolbar of the grid
                    {edit: true, add: true, del: true, search: false, refresh: true, view: false, position: "left", cloneToTop: false},
                    {
                        recreateForm: true,
                        closeAfterEdit: true,
                        
                        reloadAfterSubmit: true,
                        editData: $("#list_carreras").getGridParam("postData")

                    },
                    {
                        recreateForm: true,
                        closeAfterAdd: true,
                        
                        reloadAfterSubmit: true,
                        editData: $("#list_carreras").getGridParam("postData")

                    },
                    {
                        width: 300,
                        delData: {
                            modelo: function () {
                                return "DetalleDirector";
                            },
                            id: function () {
                                return $("#list_carreras").jqGrid('getGridParam', 'selrow');
                            }
                        }

                    }, {
                multipleSearch: false, multipleGroup: false, showQuery: false,
                sopt: ['cn']
            }
            );
            $('#pager_carreras_left').css('width', 'auto');







        }
