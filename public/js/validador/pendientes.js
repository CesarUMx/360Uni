
$(document).ready(function () {
    
    
    
    init_pendientes();

});







function dAceptar(id){
    dialogo_confirmacion("Confirmación","¿En realidad desea aceptar esta selección, está operación no se puede deshacer?",{"Confirmar":{"funcion":aceptaSeleccion,"parametros":id}});
}





function aceptaSeleccion(id){
    ajaxRequest("validaEvaluador",{id:id},finSeleccion);
}



function finSeleccion(id,datos){
    ajaxNotification(id,datos);
    $("#list_pendientes").trigger("reloadGrid");
    
}




function init_pendientes() {
    $("#list_pendientes").jqGrid({
        url: base_url + "ajax/getEvaluadoresPendientes",
        editurl: base_url + "ajax/index",
        datatype: "json",
        loadonce: false,
        mtype: "POST",
        
        colNames: ["ID", "Alumno", "Semestre","Carrera","Evaluador","Correo","Teléfono","Rol","Acciones"],
        colModel: [
            {name: "id", index: "id", key: true, hidden: true},

            
            {name: "alumno", index: "alumno", width: 80, editable: true},
            
            {name: "semestre", index: "semestre", width: 80, editable: true},
            {name: "carrera", index: "carrera", width: 300, editable: true, formatter: "select", edittype: "select",
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
            {name: "evaluador", index: "evaluador", width: 80, editable: true},
            {name: "correo", index: "correo", width: 80, editable: true},
            {name: "telefono", index: "telefono", width: 80, editable: true},
            
            
            {name: "id_rol", index: "id_rol", width: 350, editable: true, formatter: "select", edittype: "select",
                editoptions: {
                    dataUrl: base_url + "ajax/getCatalogData/RolEvaluador",
                    buildSelect: function (data, options) {
                        return creaComboGrid(data, {id: "id_rol"}, "#list_pendientes");
                    },
                    multiple: false,
                    value: getValuesFromSelect({modelo: "RolEvaluador"})

                },
                editrules: {required: true}
            },
            {name: "acciones", index: "acciones", width: 150, sortable: false}

        ],
        shrinkToFit: true,
        pager: "#pager_pendientes",
        rowNum: 100,
        height: 550,
        viewrecords: true,
        hidegrid: false,
        gridview: true,
        rownumbers: true,
        grouping: true,
        groupingView: {
            groupField: ['carrera'],
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
               
                


                accion = '<button class="btn btn-success" title="Aceptar Seleccion" onclick="dAceptar(' + lista[i] + ')"><i class="fa-solid fa-check"></i></button>';


                    
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