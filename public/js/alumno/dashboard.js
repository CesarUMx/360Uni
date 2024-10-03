var grafica1;
var grafica2;
var grafica3;



$(document).ready(function () {


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




    init_evaluadores();
    
    init_evaluaciones();
    verDatosEvaluadores();

    ajaxRequest("getCatalogData/RolEvaluador", {}, creaCombo, "cont-rol_evaluador");
    ajaxRequest("getCategoriasT", {}, creaGrafica1);
    ajaxRequest("getCategoriasCD", {}, creaGrafica2);
    ajaxRequest("getPuntosPer", {}, llenaGrafica3);


});


function cerrarSesion(){
    document.location.href="/index/logout";
}


function cambiaPeriodo() {
    ajaxRequest("getCategoriasT", {periodo: $("#periodo_seleccionado").val()}, llenaGrafica1);
    ajaxRequest("getCategoriasCD", {periodo: $("#periodo_seleccionado").val()}, llenaGrafica2);
    $("#list_evaluadores").trigger("reloadGrid");
}



function llenaGrafica1(id, datos) {


    grafica1.data.datasets[0].data = datos.mia;
    grafica1.data.datasets[1].data = datos.jefe;
    grafica1.data.datasets[2].data = datos.peer;
    
    grafica1.update();
}


function llenaGrafica2(id, datos) {
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





function dDesactivaRegistro(id) {
    ajaxRequest("setEvaluador", {id: id}, finDesactiva);
}

function finDesactiva(id, datos) {
    ajaxNotification(id, datos);
    $("#list_evaluadores").trigger("reloadGrid");
}


function dEditaRegistro(id) {
    limpiaCatalogo();
    $("#catalogo_id").val(id);
    cargarRoles();
    $('#rol_evaluador').prop('disabled', true);
    ajaxRequest("getEvaluador", {id: id}, showEdita);
}

function cargarRoles(){
    $("#rol_evaluador").empty();
    $('#rol_evaluador').append('<option value="2">Colega / Peer</option>');
    $('#rol_evaluador').append('<option value="3">Jefe / Superior</option>');
}


function dAgregaRegistro() {
    limpiaCatalogo();
    $("#catalogo_id").val("");
    $("#dialog_catalogo").modal("show");
    cargarRoles();
    $('#rol_evaluador').prop('disabled', false);

    $.ajax({
        url: base_url + "ajax/getEvaluadores",
        type: "POST",
        dataType: "json",
        data: {
            alumno: usuario_id,
            periodo: $("#periodo_seleccionado").val()
        },
        success: function(response) {
            if (response !== null) {
                for (var i = 0; i < response.rows.length; i++) {
                    var rol = response.rows[i].id_rol;
                    if (rol == 2) {
                        $("#rol_evaluador option[value=2]").remove();
                    }
                    if (rol == 3) {
                        $("#rol_evaluador option[value=3]").remove();
                    }
                }
            }   
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener los datos:", error);
        }
    });

}

function showEdita(id, datos) {

    if (datos.hasOwnProperty("nombre")) {
        $("#nombre_evaluador").val(datos.nombre);
        $("#telefono_evaluador").val(datos.telefono);
        $("#correo_evaluador").val(datos.correo);
        $("#rol_evaluador").val(datos.rol);
        $("#dialog_catalogo").modal("show");
    } else
        notificacion("No se ha encontrado el evaluador seleccionado", "error");
}

function limpiaCatalogo() {
    $("#form_catalogo input[type=text]").val("");
    $("#form_catalogo input[type=number]").val("");
    $("#form_catalogo input[type=mail]").val("");
    $("#form_catalogo select").val("");
    $("#form_catalogo textarea").val("");
    $("#form_catalogo input[type=checkbox]").prop("checked", false);
    $("#catalogo_id").val("");
}

function enviaNotificacion(id) {
    ajaxRequest("reenviaCorreo", {id: id}, ajaxNotification);
}

function finRegistro() {
    $("#dialog_catalogo").modal("hide");
    limpiaCatalogo();
    $("#list_evaluadores").trigger("reloadGrid");
}

function guardaEvaluador() {
    var id = $("#catalogo_id").val();

    if (id == "")
        ajaxRequest("setDatosGrid", {oper: "add", modelo: "Evaluador", "id_rol": $("#rol_evaluador").val(), "nombre": $("#nombre_evaluador").val(), "telefono": $("#telefono_evaluador").val(), "correo": $("#correo_evaluador").val(), "id_alumno": usuario_id}, finRegistro);
    else
        ajaxRequest("setDatosGrid", {oper: "edit", id: id, modelo: "Evaluador", "id_rol": $("#rol_evaluador").val(), "nombre": $("#nombre_evaluador").val(), "telefono": $("#telefono_evaluador").val(), "correo": $("#correo_evaluador").val(), "id_alumno": usuario_id}, finRegistro);

    limpiaCatalogo();
}

function verEvaluacion(id) {
    ajaxRequest("verEvaluacion", {id: id, periodo: $("#periodo_seleccionado").val()}, showEvaluacion);
}

function showEvaluacion(id, datos) {
    if (datos.hasOwnProperty("url"))
        window.open("/evaluador/evaluacion/" + datos.url);
    else
        ajaxNotification(id, datos);

}



function init_evaluadores() {
    $("#list_evaluadores").jqGrid({
        url: base_url + "ajax/getEvaluadores",
        editurl: base_url + "ajax/seDatosGrid",
        datatype: "json",
        loadonce: false,
        mtype: "POST",
        postData: {
            
            alumno: usuario_id,
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
            {name: "activo", index: "activo", width: 80, sortable: true, align: "center",hidden:true, editable: true, editrules: {
                    required: false
                }
            },
            {name: "acciones", index: "acciones", width: 150, sortable: false}

        ],
        shrinkToFit: false,
        pager: "#pager_evaluadores",
        rowNum: 100,
        height: 450,
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
            var total=lista.length;

            $select = $("#periodo_seleccionado option:selected");




            for (i = 0; i < lista.length; i++) {
                var rowData = $(this).getRowData(lista[i]);

                var completa = parseInt(rowData.evaluacion === "false" ? 0 : 1);

                completadas += completa;

                if (completa)
                    accion = '';
                else
                    accion = '<button class="btn btn-primary" title="Editar Registro" onclick="dEditaRegistro(' + lista[i] + ')"><i class="fa-solid fa-edit"></i></button>';

                $(this).setRowData(lista[i], {evaluacion: '<i class="far fa-2x fa-circle-' + (rowData.evaluacion === "false" ? 'xmark' : 'check') + '"></i>', activo: '<i class="far fa-2x fa-circle-' + (rowData.activo === "false" ? 'xmark' : 'check') + '"></i>', acciones: accion});
            }
            
            if(total==0)
                total=1;

            var porcentaje = Math.round(completadas * 100 / total);
            if (isNaN(porcentaje))
                porcentaje = 0;

            $("#progress_evaluadores").attr("aria-valuenow", porcentaje).css("width", porcentaje + "%").html(porcentaje + "%");

        }

    }).navGrid('#pager_evaluadores',
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
            $('#pager_evaluadores_left').css('width', 'auto');

        }

        function verDatosEvaluadores() {
            $.ajax({
                url: base_url + "ajax/getEvaluadores",
                type: "POST",
                dataType: "json",
                data: {
                    alumno: usuario_id,
                    periodo: $("#periodo_seleccionado").val()
                },
                success: function(response) {
                    if (response !== null) {
                        if(response.rows.length>=2){
                            $("#agregaRegistro").hide();
                        }
                    }   
                },
                error: function(xhr, status, error) {
                    console.error("Error al obtener los datos:", error);
                }
            });
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
            
            {name: "promedio", index: "promedio", width: 250, sortable: false, editable: true, editrules: {
                    required: true
                }},
            
            {name: "id_periodo", index: "id_periodo", width: 250, editable: true, formatter: "select", edittype: "select",
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