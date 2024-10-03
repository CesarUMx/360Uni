
$(document).ready(function () {
    
    
    
    init_pendientes();

});







function dAceptarSol(id){
    dialogo_confirmacion("Confirmación","¿En realidad desea aceptar esta selección, está operación no se puede deshacer?",{"Confirmar":{"funcion":aceptaSeleccion,"parametros":id}});
}

function dRechazarSol(id){
    dialogo_confirmacion("Confirmación","¿En realidad desea rechazar esta selección, está operación no se puede deshacer?",{"Rechazar":{"funcion":rechazaSeleccion,"parametros":id}});
}



function aceptaSeleccion(id){
    ajaxRequest("setSeleccion",{id:id,valor:true},finSeleccion);
}

function rechazaSeleccion(id){
        ajaxRequest("setSeleccion",{id:id,valor:false},finSeleccion);
}

function finSeleccion(id,datos){
    ajaxNotification(id,datos);
    $("#list_pendientes").trigger("reloadGrid");
    
}




function init_pendientes() {
    $("#list_pendientes").jqGrid({
        url: base_url + "ajax/getAlumnosPendientes",
        editurl: base_url + "ajax/index",
        datatype: "json",
        loadonce: false,
        mtype: "POST",
        
        colNames: ["ID", "Alumno", "Semestre","Carrera","Funciones Profesionales","Acciones"],
        colModel: [
            {name: "id", index: "id", key: true, hidden: true},

            
            {name: "id_usuario", index: "id_usuario", width: 250, editable: true, formatter: "select", edittype: "select",
                editoptions: {
                    dataUrl: base_url + "ajax/getCatalogData/Usuario",
                    buildSelect: function (data, options) {
                        return creaComboGrid(data, {id: "id_usuario"}, "#list_pendientes");
                    },
                    multiple: false,
                    value: getValuesFromSelect({modelo: "Usuario"})

                },
                editrules: {required: true}
            },
            
            {name: "semestre", index: "semestre", width: 80, editable: true},
            {name: "id_carrera", index: "id_carrera", width: 300, editable: true, formatter: "select", edittype: "select",
                editoptions: {
                    dataUrl: base_url + "ajax/getCatalogData/CCarrera",
                    buildSelect: function (data, options) {
                        return creaComboGrid(data, {id: "id_carrera"}, "#list_pendientes");
                    },
                    multiple: false,
                    value: getValuesFromSelect({modelo: "CCarrera"})

                },
                editrules: {required: true}
            },
            {name: "id_funcionesp", index: "id_funcionesp", width: 350, editable: true, formatter: "select", edittype: "select",
                editoptions: {
                    dataUrl: base_url + "ajax/getCatalogData/CFuncionesp",
                    buildSelect: function (data, options) {
                        return creaComboGrid(data, {id: "id_funcionesp"}, "#list_pendientes");
                    },
                    multiple: false,
                    value: getValuesFromSelect({modelo: "CFuncionesp"})

                },
                editrules: {required: true}
            },
            {name: "acciones", index: "acciones", width: 150, sortable: false}

        ],
        shrinkToFit: false,
        pager: "#pager_pendientes",
        rowNum: 100,
        height: 550,
        viewrecords: true,
        hidegrid: false,
        gridview: true,
        rownumbers: true,
        grouping: true,
        groupingView: {
            groupField: ['id_carrera'],
            groupColumnShow: [true],
            groupText: ['Carrera: <b>{0}</b> Total: <b>{1}</b>'],
            groupDataSorted: true,
            groupSummaryPos: ['header']

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

    }).navGrid('#pager_pendientes',
            // the buttons to appear on the toolbar of the grid
                    {edit: false, add: false, del: false, search: false, refresh: true, view: false, position: "left", cloneToTop: false},
                    {

                    },
                    {

                    },
                    {

                    }, {
                multipleSearch: false, multipleGroup: false, showQuery: false,
                sopt: ['cn']
            }
            );
            $('#pager_pendientes_left').css('width', 'auto');







        }