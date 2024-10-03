$(document).ready(function(){

$("#list_release").jqGrid({
        url: base_url + "ajax/getDatosGrid",
        editurl: base_url + "ajax/setDatosGrid",
        datatype: "json",
        loadonce: false,
        mtype: "POST",
        postData: {
            modelo: "Release"
        },
        colNames: ["ID", "Nombre", "Fecha"],
        colModel: [
            {name: "id", index: "id", key: true, hidden: true},
            {name: "nombre", index: "nombre", width: 250, sortable: false, editable: true, editrules: {
                    required: true
                }},
            {name: "fecha", index: "fecha", width: 150, sortable: false, editable: true, align: "center", formatter: "date", formatoptions: {srcformat: 'Y-m-d', newformat: 'd-m-Y'}, editrules: {date: true}, editoptions: {maxlengh: 10, dataInit: datepicker_element}}
   
        ],
        shrinkToFit: false,
        pager: "#pager_release",
        rowNum: 100,
        viewrecords: true,
        hidegrid: false,
        gridview: true,
        rownumbers: true,
        grouping: false,
        sortname: "id",
        sortorder: "desc",
        autoencode: false,

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
            $("#" + subgrid_id).html("<table id='" + subgrid_table_id + "' class='scroll'></table><div id='" + pager_id + "' class='scroll'></div>");
            $("#" + subgrid_table_id).jqGrid({

                datatype: "json",
                url: base_url + "ajax/getDatosGrid",
                editurl: base_url + "ajax/setDatosGrid",
                postData: {
                    valor: row_id,
                    campo: "id_release",
                    modelo: "DetalleRelease",
                },

                colNames: ["ID", "Descripcion"],
                colModel: [
                    {name: "id", index: "id", key: true, hidden: true},
                    {name: "descripcion", index: "descripcion", width: 500, sortable: false, editable: true, edittype: "textarea",editoptions: {maxlength: 245}}

                ],
                rowNum: 30,
                shrinkToFit: true,
                pager: pager_id,
                sortname: 'id',
                sortorder: "asc",
                height: '100%'

            });
            $("#" + subgrid_table_id).jqGrid('navGrid', "#" + pager_id, {edit: true, add: true, del: true, search: false, refresh: true},
                    {
                        recreateForm: true,
                        closeAfterEdit: true,
                        modal: true,
                        reloadAfterSubmit: true,
                        editData: $("#" + subgrid_table_id).getGridParam("postData")
                        
                    },
                    {
                        recreateForm: true,
                        closeAfterAdd: true,
                        modal: true,
                        reloadAfterSubmit: true,
                        editData: $("#" + subgrid_table_id).getGridParam("postData")
                        

                    },
                    {
                        width: 300,
                        delData: {
                            modelo: function () {
                                return "DetalleRelease";
                            },
                            id: function () {
                                return $("#" + subgrid_table_id).jqGrid('getGridParam', 'selrow');
                            }
                        }
                    }
            );
            $("#" + pager_id + "_left").css('width', 'auto');
        }
    }).navGrid('#pager_release',
            // the buttons to appear on the toolbar of the grid
                    {edit: true, add: true, del: true, search: false, refresh: true, view: false, position: "left", cloneToTop: false},
                    {
                        recreateForm: true,
                        closeAfterEdit: true,
                        modal: true,
                        reloadAfterSubmit: true,
                        editData: $("#list_release").getGridParam("postData")

                    },
                    {
                        recreateForm: true,
                        closeAfterEdit: true,
                        modal: true,
                        reloadAfterSubmit: true,
                        editData: $("#list_release").getGridParam("postData")

                    },
                    {
                        width: 300,
                        delData: {
                            modelo: function () {
                                return "CRelease";
                            },
                            id: function () {
                                return $("#list_release").jqGrid('getGridParam', 'selrow');
                            }
                        }

                    }
            );
            $('#pager_release_left').css('width', 'auto');


            $(window).on("load", redraw_grid);
            $(window).on("resize", redraw_grid);


        });


function redraw_grid() {
    var $grid = $("#list_release");
    $grid.jqGrid("setGridWidth", Math.max($grid.closest(".ui-jqgrid").width() - 150, $(window).width() - 150), true);
    $("#pager_release").css("width", Math.min($grid.closest(".ui-jqgrid").width(), $(window).width()));
    $grid.jqGrid("setGridHeight", $(window).height() - 120, true);

}


