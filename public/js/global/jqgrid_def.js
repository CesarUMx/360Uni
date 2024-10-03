$.jgrid.defaults.styleUI = 'Bootstrap4';
$.jgrid.defaults.iconSet = "fontAwesome";
$.jgrid.defaults.mtype = 'POST';
$.jgrid.defaults.width = null;
$.jgrid.defaults.viewrecords = true;
$.jgrid.defaults.autoencode = true;
$.jgrid.defaults.rowNum = 50;
$.jgrid.defaults.rowNum = [50, 100, 200];
$.jgrid.defaults.autoencode = true;
$.jgrid.defaults.beforeRequest=function(){
    $("#overlay").addClass("super");

    return true;
};

$.jgrid.defaults.gridComplete=function(){
    $("#overlay").removeClass("super");
    
    return true;
};


function spinner_element(value, options) {
    value = value === '' ? 0 : value;
    return '<input type="text" value="' + value + '" />';
}

function spinner_value(elem, operation, value) {
    if (operation === "get") {
        return $(elem).val();
    } else if (operation === "set") {
        $(elem).val(value);
    } else {
        return "";
    }
}

function spinner_init(elem) {
    $(elem).find(">input").spinner({
        min: 0
    });
}

function afterSubmitDelete(response, postdata) {
    var message =response.responseText;
    try{
        response = JSON.parse(response.responseText);
        message=response[0];;
    }
    catch(exception){
        
    }

     
    if (message.lastIndexOf("Ok", 0) === 0)
        return [true, "", message.substring(2)];
    else
        return [false, message, ""];
}

function datepicker_element(element) {
    $(element).datepicker({
        dateFormat: 'dd-mm-yy',
        showAnim: 'drop'
    });
}

function datepicker_element2(element) {
    $(element).datepicker({
        dateFormat: 'yy-mm-dd',
        showAnim: 'drop'
    });
}

function getCombo(modelo, filtro) {
    ajaxRequest("getCatalogData/"+modelo, {}, creaComboGrid);
}

function creaComboGridData(datos,options,id) {
    var value = "";
    datos = JSON.parse(datos);
    var $select = $("<select><option value='' disabled selected>Seleccione una opción</option></select>");
    if (datos) {
        for (var valor in datos) {
            var $opt=$('<option value="' + datos[valor].id + '">'+datos[valor].nombre+'</option>');
            for(var prop in datos[valor])
                $opt.attr("data-"+prop,datos[valor][prop]);
            $select.append($opt.clone());
            //s += '<option data-tipo="' + datos[valor].tipo + '" data-precio_unitario="' + datos[valor].precio_unitario + '" value="' + valor + '">' + datos[valor].servicio + '</option>';
            value += datos[valor].id + ":" + datos[valor].nombre + ";";
        }
        $(id).setColProp(options["id"], {editoptions: {value: value}});
    }
    return $select.wrapAll('<div>').parent().html();
}

function creaComboGrid(data, options, id) {
    var datos = JSON.parse(data);
    var value = "";

    var s = '<select><option></option>';
    if (datos) {
        for (var valor in datos) {
            s += '<option value="' + valor + '">' + datos[valor] + '</option>';
            value += valor + ":" + datos[valor] + ";";
        }
        //$(id).setColProp(options["id"], {editoptions: {value: value}});

    }
    return s + "</select>";
}

function errorTextFormat(data) {
    return 'Error: ' + data.responseText;
}


function reloadGrid($grid) {
    $grid.trigger("reloadGrid", [{page: 1}]);
    lastSelection = null;
    return true;
}

function getValuesFromSelect(params) {
    var resultado="";
    $.ajax({
        url: base_url+"ajax/getValueSelectGrid", 
        method :"POST",
        data:params,
        async: false, 
        success: function(data, result) {
            try {
                  data=JSON.parse(data);
                  resultado= data;
                }
                catch(exception) {
                    notificacion("Se ha producido la siguiente excepción: "+exception,"error");
                    showOverlay(false);
                }
        },
        error: function() {
            notificacion("Se ha producido un error al intentar cargar los datos");
        }
    });
    return resultado; 
}

function redraw_grid(id,height) {
    
    var $grid = $("#list_"+id);
    height=typeof height==="undefined"?0:height;
    $grid.jqGrid("setGridWidth", Math.max($grid.closest(".ui-jqgrid").width() - 150, $(window).width() - 150), true);
    $("#pager_"+id).css("width", Math.min($grid.closest(".ui-jqgrid").width(), $(window).width()));
    $grid.jqGrid("setGridHeight", $(window).height() - height, true); 
}

function getSpecificValue(model,params) {
    var resultado="";
    $.ajax({
        url: base_url+"ajax/getDatos/"+model, 
        method :"POST",
        data:params,
        async: false, 
        success: function(data, result) {
                try {
                  data=JSON.parse(data);
                  resultado= data;
                }
                catch(exception) {
                    notificacion("Se ha producido la siguiente excepción: "+exception,"error");
                    showOverlay(false);
                }
            
        },
        error: function() {
            notificacion("Se ha producido un error al intentar cargar los datos");
        }
    });
    return resultado; 
}

function autocomplete_element(value, options, modelo) {

    var $ac = $('<input type="text" class="FormElement"/>');
    $ac.val(value);
    $ac.autocomplete({source: base_url + "ajax/getAutocompleteGrid/" + modelo, minLength: 3,
        select: function (event, ui) {
            $("#hidden-" + options.id).val(ui.item.id).trigger("change");
        }});
    return $ac;
}

function autocomplete_value(elem, op, value) {
    if (op === "set") {
        $(elem).val(value);
    }
    return $(elem).val();
}

function spinnerH_element(value, options) {
    value = value === '' ? 0 : value;
    return '<input type="text" value="' + value + '" />';
}

function spinnerH_value(elem, operation, value) {
    if (operation === "get") {
        return $( elem ).val();
    } else if (operation === "set") {
        $( elem ).val( value );
    } else {
        return "";
    }
}

function spinnerH_init(elem) {
    $(elem).children("input").timespinner();
}