$(document).ready(function(){
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
    $grid.jqGrid("setGridWidth", Math.max($grid.closest(".ui-jqgrid").width() - 320, $(window).width() - 320), true);
    $("#paginador").css("width", Math.min($grid.closest(".ui-jqgrid").width(), $(window).width()));
    $grid.jqGrid("setGridHeight", $(window).height() - 300, true);
    
}

function creaAutoComplete(id,datos) {
    var path=document.location.pathname.split("/");
    creaComboAutocomplete(id,datos);
    $(".chosen-single > span").html(path[3]);
}



