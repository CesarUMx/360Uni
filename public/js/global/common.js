$(document).ready(function () {

    showOverlay(false);
    $("body").fadeIn(1500);
    toastr.options = {timeOut: 2500, preventDuplicates: true};

    setInterval(function () {
        ajaxRequest("index", {});
    }, 150000);



   


});


function changePeriodo(){
    $select=$("#periodo_seleccionado option:selected");
    $("#rango-periodo").html($select.data("inicio")+"-"+$select.data("fin"));
    
    cambiaPeriodo();
}



function showOverlay(hidden) {
    hidden = typeof hidden === "boolean" ? hidden : false;
    var overlay = $("#overlay");
    overlay.attr('aria-hidden', !hidden);
    $("body").toggleClass('noscroll');
    
    setTimeout(function () {
        overlay.scrollTop(0);
    }, 1000);
}


function ajaxRequestGlobal(accion, params, callback, id_destino) {
    $.ajax({
        url: "https://start.mondragonmexico.edu.mx/webservice/" + accion,
        type: "post",
        data: params,
        
       
        
        
        success: function (datos) {
            try {
                datos = JSON.parse(datos);
                if (typeof callback === "function")
                    callback.call(null, id_destino, datos);
                else
                    return datos;




            } catch (exception) {
                notificacion("Se ha producido la siguiente excepción: " + exception, "error");

            }
        }
    });
}


function ajaxRequest2(url, params, callback, id_destino) {
    $.ajax({
        url: url,
        type: "post",
        data: params,
        
       
        
        
        success: function (datos) {
            try {
                datos = JSON.parse(datos);
                if (typeof callback === "function")
                    callback.call(null, id_destino, datos);
                else
                    return datos;




            } catch (exception) {
                notificacion("Se ha producido la siguiente excepción: " + exception, "error");

            }
        }
    });
}





function ajaxRequest(accion, params, callback, id_destino) {
    //validar accion
    params = typeof params === 'object' ? params : new Object();
    id_destino = id_destino ? id_destino : "";



    $.ajax({
        url: base_url + 'ajax/' + accion,
        type: "post",
        data: params,
        success: function (datos) {
            try {
                datos = JSON.parse(datos);
                if (typeof callback === "function")
                    callback.call(null, id_destino, datos);
                else
                    return datos;
            } catch (exception) {
                notificacion("Se ha producido la siguiente excepción: " + exception, "error");

            }
        }
    });




}

function ajaxRequestFile(accion, files, callback, id_destino) {
    $.ajax({
        url: base_url + 'ajax/' + accion,
        type: "post",
        dataType: "text",
        data: files,
        cache: false,
        contentType: false,
        processData: false,
        success: function (datos) {
            try {
                datos = JSON.parse(datos);
                if (typeof callback === "function")
                    callback.call(null, id_destino, datos);
                else
                    return datos;
            } catch (exception) {
                notificacion("Se ha producido la siguiente excepción: " + exception, "error");
                //showOverlay(false);
            }
        },
        fail: function (jqXHR, textStatus) {
            notificacion("Se ha producido la siguiente excepción: " + textStatus, "error");
        }

    });

}


function notificacion(message, type, close) {
    message = message ? message : 'No se ha recibido mensaje!';
    type = type ? type : 'success';

    close = typeof close == 'function' ? close : function () {
        return false;
    };

    toastr[type](message, '', {onHidden: close, "positionClass": "toast-top-full-width"});
}

function reload() {
    document.location.href = document.location.href;
}

function ajaxNotification(id, datos) {
    for (var prop in datos)
        notificacion(datos[prop], prop);

    if (typeof id === "function")
        id.call(null, datos);
}

function removeOverlay() {
    showOverlay(false);
}

function rangoFechas(id_inicio, id_fin, delta) {
    delta = typeof delta === "undefined" ? 1 : delta;

    $("#" + id_inicio)
            .datepicker("option", "changeMonth", true)
            .datepicker("option", "maxDate", $("#" + id_fin).val())
            .datepicker("option", "onClose", function (selectedDate) {


                var splitFecha = selectedDate.split("-");
                var date = new Date(splitFecha[2] + "/" + splitFecha[1] + "/" + splitFecha[0]);
                date.setDate(date.getDate() + delta);
                $("#" + id_fin).datepicker("option", "minDate", date);
            });
    $("#" + id_fin)
            .datepicker("option", "changeMonth", true)
            .datepicker("option", "minDate", $("#" + id_inicio).val())
            .datepicker("option", "onClose", function (selectedDate) {
                var splitFecha = selectedDate.split("-");
                var date = new Date(splitFecha[2] + "/" + splitFecha[1] + "/" + splitFecha[0]);
                date.setDate(date.getDate() - delta);
                $("#" + id_inicio).datepicker("option", "maxDate", date);
            });
}

function invierteFecha(fecha) {
    var f = fecha.split("-");
    return f[2] + "-" + f[1] + "-" + f[0];
}

function imprimeTicket(id, datos) {
    var oIframe = document.getElementById('xframe');
    var oDoc = (oIframe.contentWindow || oIframe.contentDocument);
    if (oDoc.document)
        oDoc = oDoc.document;
    oDoc.write("<head><title>title</title>");
    oDoc.write("</head><body onload='this.focus(); this.print();'>");
    oDoc.write(datos.html + "</body>");
    oDoc.close();
}

function creaComboNMNC(id, datos) {
    creaComboGrupo(id, datos, false, false);
}

function creaComboMNC(id, datos) {
    creaComboGrupo(id, datos, false, true);
}

function creaComboMC(id, datos) {
    var nuevo_id = id.substring(id.indexOf("-") + 1);
    creaComboGrupo(id, datos, true, true);

    $(document).on('click', '[title="' + nuevo_id + '_clickable_optgroup"] .group-result', function () {
        var unselected = $(this).nextUntil('.group-result').not('.result-selected');
        if (unselected.length) {
            unselected.trigger('mouseup');
        } else {
            $(this).nextUntil('.group-result').each(function () {
                $('a.search-choice-close[data-option-array-index="' + $(this).data('option-array-index') + '"]').trigger('click');
            });
        }
    });

}

function creaComboM(id, datos, clic) {
    creaComboGrupo(id, datos, clic, true);
}


function creaCombo(id, datos) {
    var nuevo_id = id.substring(id.indexOf("-") + 1);
    var $select = $("<select name='" + nuevo_id + "' id='" + nuevo_id + "'>").addClass("form-control");

    $select.append("<option value='' disabled selected>Seleccione una opcion</option>");
    for (r in datos)
        $select.append("<option value='" + r + "'>" + datos[r] + "</option>");
    $("#" + id).replaceWith($select);
    $select.bind("change", window["change_" + nuevo_id]);
}

function creaComboData(id, datos) {
    var value = "";
    var nuevo_id = id.substring(id.indexOf("-") + 1);
    var $select = $("<select id='" + nuevo_id + "' class='form-control' onchange='change_" + nuevo_id + "()'><option value='' disabled selected>Seleccione una opción</option></select>");
    if (datos) {
        for (var valor in datos) {
            var $opt = $('<option value="' + datos[valor].id + '">' + datos[valor].nombre + '</option>');
            for (var prop in datos[valor])
                $opt.attr("data-" + prop, datos[valor][prop]);
            $select.append($opt.clone());
            //s += '<option data-tipo="' + datos[valor].tipo + '" data-precio_unitario="' + datos[valor].precio_unitario + '" value="' + valor + '">' + datos[valor].servicio + '</option>';
            value += datos[valor].id + ":" + datos[valor].nombre + ";";
        }

    }
    $("#" + id).replaceWith($select);
}

function creaComboMultiple(id, datos) {
    var nuevo_id = id.substring(id.indexOf("-") + 1);
    var optm = "multiple";
    var $select = $("<select name='" + nuevo_id + "' id='" + nuevo_id + "' title='" + nuevo_id + "_clickable' " + optm + ">").addClass("form-control").addClass("form-control-chosen").attr("data-placeholder", "Seleccione una opción");



    for (var index in datos)
        $select.append('<option value="' + index + '">' + datos[index] + '</option>');



    $("#" + id).replaceWith($select);
    $select.bind("change", window["change_" + nuevo_id]);
    $('#' + nuevo_id).chosen({
        width: '100%'
    });




}


function creaComboGrupo(id, datos, clic, multiple) {
    var nuevo_id = id.substring(id.indexOf("-") + 1);
    var optc = clic ? "chosen-container-optgroup-clickable" : "";
    var optm = multiple ? "multiple" : "";
    var $select = $("<select name='" + nuevo_id + "' id='" + nuevo_id + "' title='" + nuevo_id + "_clickable_optgroup' " + optm + ">").addClass("form-control").addClass("form-control-chosen-optgroup").attr("data-placeholder", "Seleccione una opción");
    var opsg = new Object();


    for (var index in datos) {
        if (!opsg[datos[index].grupo])
            opsg[datos[index].grupo] = [];
        opsg[datos[index].grupo].push({valor: datos[index].valor, label: datos[index].label});
    }


    for (index in opsg) {
        var $opt = $('<optgroup label="' + index + " " + (clic ? "(clic para agregar todos)" : "") + '">');

        for (var index2 in opsg[index])
            $opt.append('<option value="' + opsg[index][index2]["valor"] + '">' + opsg[index][index2]["label"] + '</option>');

        $select.append($opt);
    }
    $("#" + id).replaceWith($select);
    $select.bind("change", window["change_" + nuevo_id]);
    $('#' + nuevo_id).chosen({
        width: '100%'
    });


    $('[title="' + nuevo_id + '_clickable_optgroup"]').addClass(optc);

}

function creaComboAutocomplete(id, datos) {
    var nuevo_id = id.substring(id.indexOf("-") + 1);
    creaCombo(id, datos);

    $('#' + nuevo_id).addClass("form-control-chosen-required").attr("data-placeholder", "Selecione una opción").chosen({
        allow_single_deselect: false,
        width: '100%'
    });

    $("#" + id + "-text").on("change", window["change_" + nuevo_id]);
}


function creaComboDAutocomplete(id, datos) {
    var nuevo_id = id.substring(id.indexOf("-") + 1);
    creaComboData(id, datos);

    $('#' + nuevo_id).addClass("form-control-chosen-required").attr("data-placeholder", "Selecione una opción").chosen({
        allow_single_deselect: false,
        width: '100%'
    });

    $("#" + id + "-text").on("change", window["change_" + nuevo_id]);
}

function autocomplete_hidden(id, modelo, param) {
    ajaxRequest("getAutocomplete/" + modelo, param, autocompleta, id);
    $(id).autocomplete({
        select: function (event, ui) {
            $(id + "-id").val(ui.item.id).trigger('change');
            return false;
        }
    });
}


function autocompleta(id, data) {
    $(id).autocomplete({
        source: data,
        focus: function (event, ui) {
            $(this).val(ui.item.label);
            return false;
        }
    });
}

function autocompleteAll_hidden(id, modelo, param) {
    ajaxRequest("getAllDatos/" + modelo, param, autocompletaAll, id);
}

function autocompletaAll(id, datos) { //espero funcione :S
    var data = [];
    for (var d in datos) {
        //hay que poner los campos faltantes espera
        datos[d].label = datos[d].value = datos[d].nombre;

        data.push(datos[d]); //no es con push

    }





    $(id).autocomplete({
        minLength: 0,
        source: data,
        focus: function (event, ui) {
            $(id).val(ui.item.label);
            return false;
        },
        select: function (event, ui) {

            $(id).val(ui.item.label);

            for (var p in ui.item)
                $(id + "-" + p).val(ui.item[p]);





            return false;
        }
    });
}

function dialogo_confirmacion(titulo, pregunta, botones) {
    var dialogo = $("#dialog-confirm");


    $("#dialog-confirm .modal-title").html(titulo);
    $("#dialog-confirm .modal-body").html(pregunta);



    $("#dialog-confirm .modal-footer").html('<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>');



    var index = 1;
    for (var b in botones) {
        $("#dialog-confirm .modal-footer").append('<button type="button" id="confirm_btn' + index + '" class="btn btn-primary" data-bs-dismiss="modal">' + b + '</button>');

        $("#confirm_btn" + index).unbind("click").bind("click", function () {

            if (botones[b].hasOwnProperty("funcion"))
                botones[b]["funcion"].call(null, botones[b]["parametros"]);
        });
        index++;
    }


    dialogo.modal('show');

    /*dialogo.attr("title", titulo);
     $("#dialog-confirm p").html(pregunta);
     dialogo.dialog().dialog("destroy");
     
     for (var b in botones)
     dbotones[b] = function () {
     if (botones[b].hasOwnProperty("funcion"))
     botones[b]["funcion"].call(null, botones[b]["parametros"]);
     $(this).dialog("close");
     };
     
     dbotones["Cerrar"] = function () {
     $(this).dialog("close");
     };*/





}

/*DEBUG*/
function ImprimirObjeto(o) {
    var salida = '';
    for (var p in o) {
        salida += p + ': ' + o[p] + '\n';
    }
    alert(salida);
}