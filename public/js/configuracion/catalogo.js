$(document).ready(function () {
    ajaxRequest("getAllCatalogos", {}, creaAutoComplete, "cont-catalogos");
});

function afterSubmit(resp, postdata) {
    
    var message = resp.responseText;
    if (message.lastIndexOf("Ok", 0) === 0)
        return [true, "", message.substring(2)];
    else
        return [false, message, ""];
}

function redraw_grid() {
    var $grid = $("#catalogo");
    $grid.jqGrid("setGridHeight", $(window).height() - 350, true);
}

function creaAutoComplete(id, datos) {
    var path = document.location.pathname.split("/");
    creaComboAutocomplete(id, datos);
    $(".chosen-single > span").html(path[3]);
    init_catalogo(path[3]);
    
}

function change_catalogos() {
    document.location.href = base_url + "configuracion/catalogo/" + $("#catalogos").val();
}


function init_catalogo(catalogo){
    
    
    
    if(typeof(catalogo)!="undefined"&&catalogo!="")
    
    $("#catalogo").jqGrid({
        url: base_url + "ajax/getCatalogo/" + catalogo,
        editurl: base_url + "ajax/gestionaCatalogo/" + catalogo,
        datatype: "json",
        loadonce: false,
        mtype: "POST",
        colModel: colmodel,
        pager: "#paginador",
        shrinkToFit: true,
        multiselect: false,
        multiSort: true,
        rowNum: 50,
        rowList: [50, 100, 200],
        viewrecords: true,
        hidegrid: false,
        gridview: true,
        rownumbers: true,
        sortname: "id",
        sortorder: "asc",
        autoencode: false
    }).navGrid('#paginador',
            // the buttons to appear on the toolbar of the grid
                    {edit: true, add: true, del: true, search: true, refresh: true, view: false, position: "left", cloneToTop: false},
                    // options for the Edit Dialog
                            {
                                width: 400,
                                recreateForm: true,
                                closeAfterEdit: false,
                                reloadAfterSubmit: true,
                                afterSubmit: afterSubmit,
                                errorTextFormat: errorTextFormat
                            },
                            // options for the Add Dialog
                                    {
                                        closeAfterAdd: false,
                                        width: 400,
                                        modal: true,
                                        recreateForm: true,
                                        reloadAfterSubmit: true,
                                        afterSubmit: afterSubmit,
                                        errorTextFormat: errorTextFormat
                                    },
                                    // options for the Delete Dailog
                                            {
                                                width: 300,
                                                afterSubmit: afterSubmit,
                                                errorTextFormat: errorTextFormat
                                            },
                                            {
                                                multipleSearch: false, multipleGroup: false, showQuery: false,
                                                sopt: ['eq', 'bw', 'cn', 'ew']
                                            });
                                    $('#paginador_left').css('width', 'auto');
                                    $(window).on("load", redraw_grid);
                                    $(window).on("resize", redraw_grid);
redraw_grid();




                                    var funcion = "beforeShowForm_" + catalogo;
                                    if (typeof window[funcion] === 'function') {
                                        $.extend($.jgrid.edit, {
                                            beforeSubmit: funcion
                                        });
                                        $.extend($.jgrid.add, {
                                            beforeSubmit: funcion
                                        });
                                    }
                                    

}



