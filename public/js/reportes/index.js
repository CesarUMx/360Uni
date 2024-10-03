var chart = [];


$(document).ready(function () {
    ajaxRequest("getGrafica", {tipo: "semestre"}, creaGraficaDona, "poblacion");
    ajaxRequest("getGrafica", {tipo: "aecompletas"}, creaGraficaDona, "aecompletas");
    ajaxRequest("getGrafica", {tipo: "ejefes"}, creaGraficaDona, "ejefes");
    ajaxRequest("getGrafica", {tipo: "ecolegas"}, creaGraficaDona, "ecolegas");


    ajaxRequest("getGrafica", {tipo: "bajosT"}, creaGraficaDona, "btrans");
    ajaxRequest("getGrafica", {tipo: "bajosD"}, creaGraficaDona, "bdis");






    init_tablas("ecorreo", ["Rol", "Correo", "Error", "Acciones"], [{name: "rol", index: "rol", width: 120, search: false, hidden: false, align: "center"}, {name: "correo", index: "correo", width: 120, search: false, hidden: false, align: "center"}, {name: "error", index: "error", width: 120, search: false}, {name: "acciones", index: "acciones", width: 120, search: false}

    ], lcCorreo);






    init_general();



});


function cambiaFiltros() {

    ajaxRequest("getGrafica", {tipo: "bajosT", subtipo: $("#subtipo").val(), valor: $("#filtro_sub").val()}, cambiaGraficaDona, "btrans");
    ajaxRequest("getGrafica", {tipo: "bajosD", subtipo: $("#subtipo").val(), valor: $("#filtro_sub").val()}, cambiaGraficaDona, "bdis");
    
    ajaxRequest("getGrafica", {tipo: "semestre", subtipo: $("#subtipo").val(), valor: $("#filtro_sub").val()}, cambiaGraficaDona, "poblacion");
    ajaxRequest("getGrafica", {tipo: "aecompletas", subtipo: $("#subtipo").val(), valor: $("#filtro_sub").val()}, cambiaGraficaDona, "aecompletas");
    ajaxRequest("getGrafica", {tipo: "ejefes", subtipo: $("#subtipo").val(), valor: $("#filtro_sub").val()}, cambiaGraficaDona, "ejefes");
    ajaxRequest("getGrafica", {tipo: "ecolegas", subtipo: $("#subtipo").val(), valor: $("#filtro_sub").val()}, cambiaGraficaDona, "ecolegas");    
}

function lcCorreo() {
    var lista = $(this).getDataIDs();
    var accion;
    for (i = 0; i < lista.length; i++) {
        
        
       


        accion = '<button class="btn btn-primary" title="Marcar Notificación" class="ui-button-primary" onclick="notificado(' + lista[i] + ')"><i class="far fa-thumbs-up"></i></button> ';



        $(this).setRowData(lista[i], {acciones: accion});
    }
}

function notificado(id){
    ajaxRequest("limpiaNC",{id:id},ajaxNotification,reloadCC);
    
}

function reloadCC(){
    $("#list_ecorreo").trigger("reloadGrid");
}



function cambiaSubtipo(valor) {
    switch (valor) {
        case "carrera":

            ajaxRequest("getCarreras", {}, llenaFiltro, "Todas las carreras");
            break;


        case "semestre":

            llenaFiltro("Todos los semestres", {1: "1", 2: "2", 3: "3", 4: "4", 5: "5", 6: "6", 7: "7", 8: "8", 9: "9", 10: "10", });

            break;
            
            
            case "especialidad":

            ajaxRequest("getFuncionesP", {}, llenaFiltro, "Todas las especialidades");

            break;

        default:
            $("#filtro_sub").html("").attr("disabled", true);


    }


    cambiaFiltros();


}

function llenaFiltro(id, datos) {
    $("#filtro_sub").html("<option value='0' selected>" + id + "</option>");

    for (var d in datos)
        $("#filtro_sub").append("<option value='" + d + "'>" + datos[d] + "</option>");

    $("#filtro_sub").attr("disabled", false);

}

function cambiaGraficaDona(id, datos) {

    chart[id].updateOptions({
        series: datos.series,
        labels: datos.labels

    });

}

function exportaRep() {
    window.open(base_url + "exporta/reporte", '_blank');
}






function creaGraficaDona(id, datos) {
    var options = {
        series: datos.series,
        labels: datos.labels,
        chart: {
            foreColor: '#9ba7b2',
            height: 280,
            type: 'donut'
        },

        plotOptions: {
            pie: {
                donut: {
                    size: '40%'
                }
            }
        },
        responsive: [{
                breakpoint: 380,
                options: {
                    chart: {
                        height: 320
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }],
        legend: {
            position: 'bottom'

        }
    };
    chart[id] = new ApexCharts(document.querySelector("#grafica-" + id), options);
    chart[id].render();
}


function init_tablas(id, nombres, modelo, loadC) {
    $("#list_" + id).jqGrid({
        url: base_url + "ajax/getReporte",
        editurl: base_url + "ajax/index",
        datatype: "json",
        loadonce: false,
        mtype: "POST",
        postData: {
            tipo: id

        },
        colNames: nombres,
        colModel: modelo,
        shrinkToFit: true,

        rowNum: 50,
        height: 250,
        viewrecords: true,
        hidegrid: false,
        gridview: true,
        rownumbers: true,
        grouping: false,
        sortname: "id",
        sortorder: "desc",
        autoencode: false,
        pager: "#pager_" + id,
        loadComplete: loadC




    }).navGrid('#pager_' + id,
            // the buttons to appear on the toolbar of the grid
                    {edit: false, add: false, del: false, search: false, refresh: true, view: false, position: "left", cloneToTop: false},
                    );
            $('#pager_' + id + '_left').css('width', 'auto');







        }


function init_general() {
    $("#list_general").jqGrid({
        url: base_url + "ajax/getReporteGeneral",
        editurl: base_url + "ajax/index",
        datatype: "json",
        loadonce: false,
        mtype: "POST",

        colNames: ["Nombre", "Semestre", "Carrera", "Correo", "AutoEv", "Jefes", "Colegas", "Evaluación Jefe", "Evaluación Colega", "Último Acceso", "Puntuación Transversales", "Puntuación Disciplinarias"],
        colModel: [
            {name: "nombre", index: "nombre", width: 250, editable: false},
            {name: "semestre", index: "semestre", width: 80, editable: true},
            {name: "carrera", index: "carrera", width: 200, editable: true},
            {name: "correo", index: "correo", width: 150, editable: true},
            {name: "autoevaluacion", index: "autoevaluacion", width: 120, sortable: false},
            {name: "jefes", index: "jefes", width: 120, sortable: false},
            {name: "colegas", index: "colegas", width: 120, sortable: false},
            {name: "ejefes", index: "ejefes", width: 150, sortable: false},
            {name: "ecolegas", index: "ecolegas", width: 150, sortable: false},
            {name: "ultimo", index: "ultimo", width: 150, sortable: false},
            {name: "ptrans", index: "ptrans", width: 150, sortable: false},
            {name: "pdis", index: "pdis", width: 150, sortable: false}





        ],
        shrinkToFit: false,

        rowNum: 500,
        height: 450,
        viewrecords: true,
        hidegrid: false,
        gridview: true,
        rownumbers: true,
        grouping: false,
        sortname: "id",
        sortorder: "desc",
        autoencode: false,
        pager: "#pager_general",
        loadComplete: function () {
            var lista = $(this).getDataIDs();
            var accion;
            for (i = 0; i < lista.length; i++) {
                var rowData = $(this).getRowData(lista[i]);
                var ae = "danger";
                var bd = "danger";
                var bd2 = "danger";
                
                var bd3 = "danger";
                var bd4 = "danger";
                
                
                
                if (rowData.autoevaluacion == "Media")
                    ae = "warning";
                else if (rowData.autoevaluacion == "Alto")
                    ae = "success";
                else if (rowData.autoevaluacion == "Pendiente")
                    ae = "dark";
                
                
                
                

                if (rowData.ptrans == "Media")
                    bd = "warning";
                else if (rowData.ptrans == "Alto")
                    bd = "success";



                if (rowData.pdis == "Media")
                    bd2 = "warning";
                else if (rowData.pdis == "Alto")
                    bd2 = "success";
                
                
                if (rowData.colegas == "Media")
                    bd3 = "warning";
                else if (rowData.colegas == "Alto")
                    bd3 = "success";
                else if (rowData.colegas == "Pendiente")
                    bd3 = "dark";
                
                if (rowData.jefes == "Media")
                    bd4 = "warning";
                else if (rowData.jefes == "Alto")
                    bd4 = "success";
                else if (rowData.jefes == "Pendiente")
                    bd4 = "dark";
                

                accion = '<button class="btn btn-primary" title="Imprimir recibo" onclick="reciboServicio(' + lista[i] + ')"><i class="fa fa-print" aria-hidden="true"></i></button>';
                $(this).setRowData(lista[i], {autoevaluacion: '<span class="bgx badge bg-' + ae + '">' + rowData.autoevaluacion + '</span>', ptrans: '<span class="bgx badge bg-' + bd + '">' + rowData.ptrans + '</span>', pdis: '<span class="bgx badge bg-' + bd2 + '">' + rowData.pdis + '</span>', colegas: '<span class="bgx badge bg-' + bd3 + '">' + rowData.colegas + '</span>', jefes: '<span class="bgx badge bg-' + bd4 + '">' + rowData.jefes + '</span>'});
            }







        }




    }).navGrid('#pager_general',
            // the buttons to appear on the toolbar of the grid
                    {edit: false, add: false, del: false, search: false, refresh: true, view: false, position: "left", cloneToTop: false},
                    );
            $('#pager_general_left').css('width', 'auto');







        }
