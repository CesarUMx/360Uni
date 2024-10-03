var grafica1;
var grafica2;
var grafica3;



$(document).ready(function () {


    ajaxRequest("getIndicadores2", {}, llenaIndicadores);
    
    
    ajaxRequest("getCarreras", {}, creaComboAutocomplete,"cont-filtro_carrera");
    
    
    
    init_alumnos();
    init_evaluaciones();
    
    
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
    
    ajaxRequest("getCategoriasT", {id_alumno:0}, creaGrafica1);
    ajaxRequest("getCategoriasCD", {id_alumno:0}, creaGrafica2);
    ajaxRequest("getPuntosPer", {id_alumno:0}, llenaGrafica3);
    
});


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


function dGraficos(id_alumno,nombre) {
    ajaxRequest("getCategoriasT", {periodo: $("#periodo_seleccionado").val(),id_alumno:id_alumno}, llenaGrafica1);
    ajaxRequest("getCategoriasCD", {periodo: $("#periodo_seleccionado").val(),id_alumno:id_alumno}, llenaGrafica2);
    
    ajaxRequest("getPuntosPer", {id_alumno:id_alumno}, llenaGrafica3);
    
    
    
    $("#list_evaluaciones").setGridParam({postData: {alumno: id_alumno}}).trigger("reloadGrid");

    
    
    
    
    
    $("#titulo_graficas").html("Gráficos del Alumno "+nombre);
    
    
    $("#dialog_graficos").modal("show");
}




function llenaGrafica1(id, datos) {
    
    grafica1.data.labels = datos.categorias;
    grafica1.data.datasets[0].data = datos.mia;
    grafica1.data.datasets[1].data = datos.jefe;
    grafica1.data.datasets[2].data = datos.peer;
    
    grafica1.update();
}


function llenaGrafica2(id, datos) {
    grafica2.data.labels = datos.categorias;
    grafica2.data.datasets[0].data = datos.mia;
    grafica2.data.datasets[1].data = datos.jefe;
    grafica2.data.datasets[2].data = datos.peer;
    
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
                }
                ]
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


function limpiaFiltros(){
    
    $("#filtro_alumno").val("");
    $("#filtro_semestre").val("");
    $("#filtro_carrera").val("");
    
    $("#filtro_carrera").trigger('chosen:updated');
    
    change_filtros();
}


function change_filtro_carrera(){
    change_filtros();
}



function change_filtros(){
    $("#list_alumnos").setGridParam({postData: {alumno:$("#filtro_alumno").val(),semestre:$("#filtro_semestre").val(),carrera:$("#filtro_carrera").val()}}).trigger("reloadGrid");

}



function init_alumnos() {
    $("#list_alumnos").jqGrid({
        url: base_url + "ajax/getAlumnos",
        editurl: base_url + "ajax/index",
        datatype: "json",
        loadonce: false,
        mtype: "POST",
        postData:{
            alumno:"",
            semestre:"",
            carrera:""
            
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
        height: 450,
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
                colNames: ["ID", "Nombre", "Evaluación", "Rol", "Teléfono", "Correo Electrónico", "Activo", "Acciones"],
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
                    {name: "telefono", index: "telefono", width: 180, sortable: true, editable: true, editrules: {
                            required: true
                        }
                    },
                    {name: "correo", index: "correo", width: 180, sortable: true, editable: true, editrules: {
                            required: true
                        }
                    },
                    {name: "activo", index: "activo", width: 80, sortable: true, align: "center", editable: true, editrules: {
                            required: true
                        }
                    },
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
                    
                    
                    
                    var delta = $("#list_alumnos").getDataIDs().length;
                    
                    
                    if(delta<1)
                        delta=1;
                    
                    delta=100/delta;

                    $select = $("#periodo_seleccionado option:selected");




                    for (i = 0; i < lista.length; i++) {
                        var rowData = $(this).getRowData(lista[i]);

                        var completa = parseInt(rowData.evaluacion === "false" ? 0 : 1);

                        completadas += completa;


                        if (completa)
                            accion = '<button class="btn btn-info" title="Ver Evaluación" onclick="verEvaluacion(' + lista[i] + ',' + row_id + ')"><i class="fa-solid fa-eye"></i></button>';
                        else
                            accion = '';






                        $(this).setRowData(lista[i], {evaluacion: '<i class="far fa-2x fa-circle-' + (rowData.evaluacion === "false" ? 'xmark' : 'check') + '"></i>', activo: '<i class="far fa-2x fa-circle-' + (rowData.activo === "false" ? 'xmark' : 'check') + '"></i>', acciones: accion});
                    }




                    var porcentaje = Math.round(completadas * 100 / lista.length);
                    if (isNaN(porcentaje))
                        porcentaje = 0;


                    $("#progress_evaluadores_" + row_id).attr("aria-valuenow", porcentaje).css("width", porcentaje + "%").html(porcentaje + "%");
                    
                    if(porcentaje==100) {
                        var act=parseInt($("#progress_alumnos").attr("aria-valuenow"));
                        act+=delta;
                        if(act>100)
                            act=100;
                        
                        act=Math.round(act,2);
                        $("#progress_alumnos").attr("aria-valuenow", act).css("width", act + "%").html(act + "%");
                    }

                    
                    
                    
                    

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
                accion = '<button class="btn btn-mondragon2" title="Ver gráficos" onclick="dGraficos(' + rowData.id + ',\''+rowData.alumno+'\')"><i class="fas fa-chart-pie"></i></button>';
                $(this).setRowData(lista[i], {avance: avance, acciones: accion});
                $(this).expandSubGridRow(lista[i]);
                $(this).collapseSubGridRow(lista[i]);
                
                
                
                
                
                
                
                
                

            }









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



function init_evaluaciones() {
    $("#list_evaluaciones").jqGrid({
        url: base_url + "ajax/getEvaluaciones",
        editurl: base_url + "ajax/seDatosGrid",
        datatype: "json",
        loadonce: false,
        mtype: "POST",
        postData: {
            
            alumno: usuario_id
            
        },
        colNames: [ "Seccion", "Promedio", "Período"],
        colModel: [
            
            {name: "id_seccion", index: "id_seccion", width: 250, editable: true, formatter: "select", edittype: "select",
                editoptions: {
                    dataUrl: base_url + "ajax/getCatalogData/CSeccion",
                    buildSelect: function (data, options) {
                        return creaComboGrid(data, {id: "id_seccion"}, "#list_evaluaciones");
                    },
                    multiple: false,
                    value: getValuesFromSelect({modelo: "CSeccion"})

                },
                editrules: {required: true}
            },
            
            {name: "promedio", index: "promedio", width: 100, sortable: false, editable: true, editrules: {
                    required: true
                }},
            
            {name: "id_periodo", index: "id_periodo", width: 100, editable: true, formatter: "select", edittype: "select",
                editoptions: {
                    dataUrl: base_url + "ajax/getCatalogData/CPeriodo",
                    buildSelect: function (data, options) {
                        return creaComboGrid(data, {id: "id_rol"}, "#list_evaluaciones");
                    },
                    multiple: false,
                    value: getValuesFromSelect({modelo: "CPeriodo"})

                },
                editrules: {required: true}
            }
            
            
            
            


        ],
        shrinkToFit: false,
        pager: "#pager_evaluaciones",
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
        

    }).navGrid('#pager_evaluaciones',
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
            $('#pager_evaluaciones_left').css('width', 'auto');







        }   