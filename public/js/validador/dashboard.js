var grafica1;
var grafica2;
var grafica3;


$(document).ready(function () {


    ajaxRequest("getIndicadores3", {}, llenaIndicadores);
    ajaxRequest("getCarreras2", {}, creaComboAutocomplete, "cont-filtro_carrera");
    init_alumnos();



    grafica3 = new Chart(document.getElementById("chart3"), {
        type: 'bar',
        data: {

        },
        options: {
            maintainAspectRatio: false,
            title: {
                display: true,
                text: 'Puntos obtenidos por periodo'
            }
        }
    });

    ajaxRequest("getCategoriasT", {id_alumno: 0}, creaGrafica1);
    ajaxRequest("getCategoriasCD", {id_alumno: 0}, creaGrafica2);
    ajaxRequest("getPuntosPer", {id_alumno: 0}, llenaGrafica3);


    

});

function cargaPorcentajes(){
    showOverlay(true);
    setTimeout(function () {

        var lista = $("#list_alumnos").getDataIDs();
       
        for (i = 0; i < lista.length; i++) {
            $("#list_alumnos").expandSubGridRow(lista[i]);
            $("#list_alumnos").collapseSubGridRow(lista[i]);
        }
        showOverlay(false);


    }, 1000);
}


function cambiaPeriodo() {
    $("#list_alumnos").setGridParam({postData: {periodo: $("#periodo_seleccionado").val()}}).trigger("reloadGrid");
}


function llenaIndicadores(id, datos) {

    for (var d in datos)
        $("#indicador_" + d).html(datos[d]);
}



function verEvaluacion(id, alumno) {
    ajaxRequest("verEvaluacion", {id: id, id_alumno: alumno, periodo: $("#periodo_seleccionado").val()}, showEvaluacion);
}

function showEvaluacion(id, datos) {
    if (datos.hasOwnProperty("url"))
        window.open("/evaluador/evaluacion/" + datos.url);
    else
        ajaxNotification(id, datos);

}


function dGraficos(id_alumno, nombre) {
    ajaxRequest("getCategoriasT", {periodo: $("#periodo_seleccionado").val(), id_alumno: id_alumno}, llenaGrafica1);
    ajaxRequest("getCategoriasCD", {periodo: $("#periodo_seleccionado").val(), id_alumno: id_alumno}, llenaGrafica2);

    ajaxRequest("getPuntosPer", {id_alumno: id_alumno}, llenaGrafica3);


    $("#titulo_graficas").html("Gráficos del Alumno " + nombre);


    $("#dialog_graficos").modal("show");
}




function llenaGrafica1(id, datos) {

    grafica1.data.labels = datos.categorias;
    grafica1.data.datasets[0].data = datos.mia;
    grafica1.data.datasets[1].data = datos.jefe;
    grafica1.data.datasets[2].data = datos.peer;
    grafica1.data.datasets[3].data = datos.proveedor;
    grafica1.update();
}


function llenaGrafica2(id, datos) {
    grafica2.data.labels = datos.categorias;
    grafica2.data.datasets[0].data = datos.mia;
    grafica2.data.datasets[1].data = datos.jefe;
    grafica2.data.datasets[2].data = datos.peer;
    grafica2.data.datasets[3].data = datos.proveedor;
    grafica2.update();
}



function llenaGrafica3(id, datos) {


    grafica3.data.labels = [];
    for (var l in datos.labels)
        grafica3.data.labels.push(datos.labels[l]);

    grafica3.data.datasets = [];
    for (var d in datos.dataset)
        grafica3.data.datasets.push(datos.dataset[d]);

    grafica3.update();
}

function creaGrafica1(id, datos) {
    grafica1 = new Chart(document.getElementById("chart1"), {
        type: 'radar',
        data: {
            labels: datos.categorias,
            datasets: [{
                    label: "Auto",
                    fill: true,
                    backgroundColor: "rgba(179,181,198,0.2)",
                    borderColor: "rgba(179,181,198,1)",
                    pointBorderColor: "#fff",
                    pointBackgroundColor: "rgba(179,181,198,1)",
                    data: datos.mia
                }, {
                    label: "Jefe",
                    fill: true,
                    backgroundColor: "rgba(50,171,19,0.2)",
                    borderColor: "rgba(50,171,19,1)",
                    pointBorderColor: "#fff",
                    pointBackgroundColor: "rgba(50,171,19,1)",
                    pointBorderColor: "#fff",
                    data: datos.jefe
                },
                {
                    label: "Peer",
                    fill: true,
                    backgroundColor: "rgba(214,51,132,0.2)",
                    borderColor: "rgba(214,51,132,1)",
                    pointBorderColor: "#fff",
                    pointBackgroundColor: "rgba(214,51,132,1)",
                    pointBorderColor: "#fff",
                    data: datos.peer
                },
                {
                    label: "Proveedor",
                    fill: true,
                    backgroundColor: "rgba(13,110,253,0.2)",
                    borderColor: "rgba(13,110,253,1)",
                    pointBorderColor: "#fff",
                    pointBackgroundColor: "rgba(13,110,253,1)",
                    pointBorderColor: "#fff",
                    data: datos.proveedor
                }]
        },
        options: {
            maintainAspectRatio: false,
            title: {
                display: false

            }
        }
    });
}


function creaGrafica2(id, datos) {
    grafica2 = new Chart(document.getElementById("chart2"), {
        type: 'radar',
        data: {
            labels: datos.categorias,
            datasets: [{
                    label: "Auto",
                    fill: true,
                    backgroundColor: "rgba(179,181,198,0.2)",
                    borderColor: "rgba(179,181,198,1)",
                    pointBorderColor: "#fff",
                    pointBackgroundColor: "rgba(179,181,198,1)",
                    data: datos.mia
                }, {
                    label: "Jefe",
                    fill: true,
                    backgroundColor: "rgba(50,171,19,0.2)",
                    borderColor: "rgba(50,171,19,1)",
                    pointBorderColor: "#fff",
                    pointBackgroundColor: "rgba(50,171,19,1)",
                    pointBorderColor: "#fff",
                    data: datos.jefe
                },
                {
                    label: "Peer",
                    fill: true,
                    backgroundColor: "rgba(214,51,132,0.2)",
                    borderColor: "rgba(214,51,132,1)",
                    pointBorderColor: "#fff",
                    pointBackgroundColor: "rgba(214,51,132,1)",
                    pointBorderColor: "#fff",
                    data: datos.peer
                },
                {
                    label: "Proveedor",
                    fill: true,
                    backgroundColor: "rgba(13,110,253,0.2)",
                    borderColor: "rgba(13,110,253,1)",
                    pointBorderColor: "#fff",
                    pointBackgroundColor: "rgba(13,110,253,1)",
                    pointBorderColor: "#fff",
                    data: datos.proveedor
                }]
        },
        options: {
            maintainAspectRatio: false,
            title: {
                display: false

            }
        }
    });
}





function dValidar(id) {
    dialogo_confirmacion("Confirmación", "¿En realidad desea validar este evaluador, está operación no se puede deshacer?", {"Confirmar": {"funcion": validaEvaluador, "parametros": id}});
}


function validaEvaluador(id) {
    ajaxRequest("validaEvaluador", {id: id}, finSeleccion);
}



function finSeleccion(id, datos) {
    ajaxNotification(id, datos);
    ajaxRequest("getIndicadores3", {}, llenaIndicadores);
    $("#list_alumnos").trigger("reloadGrid");

}


function limpiaFiltros() {

    $("#filtro_alumno").val("");
    $("#filtro_semestre").val("");
    $("#filtro_carrera").val("");

    $("#filtro_carrera").trigger('chosen:updated');

    change_filtros();
}


function change_filtro_carrera() {
    change_filtros();
}



function change_filtros() {
    $("#list_alumnos").setGridParam({postData: {alumno: $("#filtro_alumno").val(), semestre: $("#filtro_semestre").val(), carrera: $("#filtro_carrera").val()}}).trigger("reloadGrid");

}


function init_alumnos() {
    $("#list_alumnos").jqGrid({
        url: base_url + "ajax/getAlumnos2",
        editurl: base_url + "ajax/index",
        datatype: "json",
        loadonce: false,
        mtype: "POST",
        postData: {
            alumno: "",
            semestre: "",
            carrera: ""

        },

        colNames: ["ID", "Alumno", "Avance", "Semestre", "Carrera", "Funciones Profesionales", "Acciones"],
        colModel: [
            {name: "id", index: "id", key: true, hidden: true},
            {name: "alumno", index: "alumno", width: 250, editable: false},
            {name: "avance", index: "avance", width: 150, sortable: false},
            {name: "semestre", index: "semestre", width: 80, editable: true},
            {name: "id_carrera", index: "id_carrera", width: 300, editable: true, formatter: "select", edittype: "select",
                editoptions: {
                    dataUrl: base_url + "ajax/getCatalogData/CCarrera",
                    buildSelect: function (data, options) {
                        return creaComboGrid(data, {id: "id_carrera"}, "#list_alumnos");
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
                        return creaComboGrid(data, {id: "id_funcionesp"}, "#list_alumnos");
                    },
                    multiple: false,
                    value: getValuesFromSelect({modelo: "CFuncionesp"})

                },
                editrules: {required: true}
            },
            {name: "acciones", index: "acciones", width: 150, sortable: false}

        ],
        shrinkToFit: false,
        pager: "#pager_alumnos",
        rowNum: 100,
        height: 400,
        viewrecords: true,
        hidegrid: false,
        gridview: true,
        rownumbers: true,
        grouping: false,
        groupingView: {
            groupField: ['id_carrera'],
            groupColumnShow: [true],
            groupText: ['Carrera: <b>{0}</b> Total: <b>{1}</b>'],
            groupDataSorted: true,
            groupSummaryPos: ['header']

        },
        subGrid: true,
        subGridRowColapsed: function (subgrid_id, row_id) {
            // this function is called before removing the data
            var subgrid_table_id;
            subgrid_table_id = subgrid_id + "_t";
            $("#" + subgrid_table_id).remove();
        },
        subGridRowExpanded: function (subgrid_id, row_id) {
            var subgrid_table_id, pager_id;
            subgrid_table_id = subgrid_id + "_t";
            pager_id = "p_" + subgrid_table_id;
            var rowData = $("#list_checkout_facturacion").getRowData(row_id);
            $("#" + subgrid_id).html("<table id='" + subgrid_table_id + "' class='scroll'></table><div id='" + pager_id + "' class='scroll'></div>");
            $("#" + subgrid_table_id).jqGrid({

                url: base_url + "ajax/getEvaluadores",
                editurl: base_url + "ajax/seDatosGrid",
                datatype: "json",
                loadonce: false,
                mtype: "POST",
                postData: {

                    alumno: row_id,
                    periodo: $("#periodo_seleccionado").val()
                },
                colNames: ["ID", "Nombre", "Evaluación", "Rol", "Teléfono", "Correo Electrónico", "Activo", "Validado", "Acciones"],
                colModel: [
                    {name: "id", index: "id", key: true, hidden: true},

                    {name: "nombre", index: "nombre", width: 250, sortable: false, editable: true, editrules: {
                            required: true
                        }},

                    {name: "evaluacion", index: "evaluacion", width: 80, sortable: true, align: "center", editable: true, editrules: {
                            required: true
                        }
                    },
                    {name: "id_rol", index: "id_rol", width: 250, editable: true, formatter: "select", edittype: "select",
                        editoptions: {
                            dataUrl: base_url + "ajax/getCatalogData/RolEvaluador",
                            buildSelect: function (data, options) {
                                return creaComboGrid(data, {id: "id_rol"}, "#list_evaluadores");
                            },
                            multiple: false,
                            value: getValuesFromSelect({modelo: "RolEvaluador"}),

                        },
                        editrules: {required: true}
                    },
                    {name: "telefono", index: "telefono", width: 180, sortable: true, editable: true},
                    {name: "correo", index: "correo", width: 180, sortable: true, editable: true},
                    {name: "activo", index: "activo", width: 80, sortable: true, align: "center", editable: true},
                    {name: "validado", index: "validado", width: 80, sortable: true, align: "center", editable: true},
                    {name: "acciones", index: "acciones", width: 150, sortable: false}

                ],
                shrinkToFit: false,
                pager: "#pager_evaluadores",
                rowNum: 100,
                height: 250,
                viewrecords: true,
                hidegrid: false,
                gridview: true,
                rownumbers: true,
                grouping: false,
                sortname: "id",
                sortorder: "asc",
                autoencode: false,
                loadComplete: function () {
                    var lista = $(this).getDataIDs();
                    var accion;
                    var completadas = 0;

                    $select = $("#periodo_seleccionado option:selected");




                    for (i = 0; i < lista.length; i++) {
                        var rowData = $(this).getRowData(lista[i]);

                        var completa = parseInt(rowData.evaluacion === "false" ? 0 : 1);

                        completadas += completa;


                        if (completa)
                            accion = '<button class="btn btn-info" title="Ver Evaluación" onclick="verEvaluacion(' + lista[i] + ',' + row_id + ')"><i class="fa-solid fa-eye"></i></button>';
                        else
                            accion = '';



                        if (rowData.validado == "false")
                            accion = '<button class="btn btn-success" title="Validar" onclick="dValidar(' + lista[i] + ')"><i class="fa-solid fa-check"></i></button>';



                        $(this).setRowData(lista[i], {evaluacion: '<i class="far fa-2x fa-circle-' + (rowData.evaluacion === "false" ? 'xmark' : 'check') + '"></i>', validado: '<i class="far fa-2x fa-circle-' + (rowData.validado === "false" ? 'xmark' : 'check') + '"></i>', activo: '<i class="far fa-2x fa-circle-' + (rowData.activo === "false" ? 'xmark' : 'check') + '"></i>', acciones: accion});
                    }




                    var porcentaje = Math.round(completadas * 100 / lista.length);
                    if (isNaN(porcentaje))
                        porcentaje = 0;




                    $("#progress_evaluadores_" + row_id).attr("aria-valuenow", porcentaje).css("width", porcentaje + "%").html(porcentaje + "%");

                }

            });
            $("#" + subgrid_table_id).jqGrid('navGrid', "#" + pager_id, {edit: false, add: false, del: false, search: false});
        },

        sortname: "id",
        sortorder: "asc",
        autoencode: false,
        loadComplete: function () {
            var lista = $(this).getDataIDs();
            var accion;
            for (i = 0; i < lista.length; i++) {
                var rowData = $(this).getRowData(lista[i]);



                avance = '<div class="progress"><div id="progress_evaluadores_' + rowData.id + '" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">0%</div></div>';
                accion = '<button class="btn btn-mondragon2" title="Ver gráficos" onclick="dGraficos(' + rowData.id + ',\'' + rowData.alumno + '\')"><i class="fas fa-chart-pie"></i></button>';
                $(this).setRowData(lista[i], {avance: avance, acciones: accion});


            }
            
            cargaPorcentajes();









        }

    }).navGrid('#pager_alumnos',
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
            $('#pager_alumnos_left').css('width', 'auto');
        }



