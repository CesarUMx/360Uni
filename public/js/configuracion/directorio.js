$(document).ready(function(){


$("#list_directorio").jqGrid({
            url: base_url + "ajax/getDirectorio",
        editurl: base_url + "ajax/gestionaDirectorio",
        
            datatype: "json",
            loadonce: false,
            mtype: "POST",
            colNames: ["Id","Nombre", "Teléfono 1", "Teléfono 2", "Observaciones"],
        colModel: [
            {name: "id", index: "id", hidden: true, key: true},
            {name: "nombre", index: "nombre", width: 120, editable: true,editoptions:{maxlength:80},editrules:{required:true}},
            {name: "telefono", index: "telefono",editable: true,editoptions:{maxlength:15},editrules:{required:true}},
            {name: "telefono2", index: "telefono2",editable: true,editoptions:{maxlength:15}},
            {name: "observaciones", index: "observaciones", width: 200, sortable: false, editable: true, edittype: "textarea",editoptions:{maxlength:180}}
        ],
            pager: "#pager_directorio",
            shrinkToFit: true,
            multiselect: false,
            multiSort : true,
            rowNum: 50,
            rowList: [10, 20, 30, 50, 100, 200],
            viewrecords: true,
            hidegrid: false,
            gridview: true,
            rownumbers: true,
            sortname: "id",
            sortorder: "asc",
            autoencode: false
        }).navGrid('#pager_directorio',
                // the buttons to appear on the toolbar of the grid
                        {edit: true, add: true, del: true, search: false, refresh: true, view: false, position: "left", cloneToTop: false},
                // options for the Edit Dialog
                {
                    width: 400,
                    recreateForm: true,
                    closeAfterEdit: false,
                    reloadAfterSubmit: true,

                    
                    errorTextFormat: errorTextFormat
                },
                // options for the Add Dialog
                {
                    closeAfterAdd: false,
                    width: 400,
                    modal: true,
                    recreateForm: true,
                    reloadAfterSubmit: true,

                   
                    errorTextFormat: errorTextFormat
                },
                // options for the Delete Dailog
                {
                    width: 300,
                    
                    errorTextFormat: errorTextFormat
                },
                {
                    multipleSearch: false, multipleGroup: false, showQuery: false,
                    sopt: ['eq', 'bw', 'cn', 'ew']
                });
                $('#pager_directorio_left').css('width', 'auto');


                $(window).on("load", redraw_grid);
                $(window).on("resize", redraw_grid);
                
                                    redraw_grid();
});

function redraw_grid() {
    
    var $grid = $("#list_directorio");
    height=120;
    $grid.jqGrid("setGridWidth", Math.max($grid.closest(".ui-jqgrid").width() - 150, $(window).width() - 150), true);
    $("#paginador").css("width", Math.min($grid.closest(".ui-jqgrid").width(), $(window).width()));
    $grid.jqGrid("setGridHeight", $(window).height() - height, true); 
}