$(document).ready(function () {



    init_select();


    $("div.pregunta").removeClass("pregunta");





    (function () {




        const bttn = document.querySelector('button.particles-button');

        if (bttn) {


            let particlesOpts = {
                type: 'rectangle',
                style: 'stroke',
                size: 15,
                color: '#3c2e9e',
                duration: 600,
                easing: [0.2, 1, 0.7, 1],
                oscillationCoefficient: 5,
                particlesAmountCoefficient: 2,
                direction: 'right'
            };
            particlesOpts.complete = () => {
                if (!buttonVisible) {
                    var faltante = false;
                    window.scrollTo({top: 0, behavior: 'smooth'});
                    buttonVisible = !buttonVisible;



                    $(".pregunta").each(function () {

                        faltante |= this.value === "";


                    });


                    if (faltante) {
                        particles.integrate({
                            duration: 800,
                            easing: 'easeOutSine'
                        });

                        notificacion("Por favor, complete el cuestionario para continuar", "error");

                    } else
                        guardaEvaluacion();



                }
            };
            const particles = new Particles(bttn, particlesOpts);

            let buttonVisible = true;
            bttn.addEventListener('click', () => {
                if (!particles.isAnimating() && buttonVisible) {
                    particles.disintegrate();
                    buttonVisible = !buttonVisible;
                }
            });

        }

    })();



    $(".pregunta").change(function () {
        $preguntas = $('.pregunta[data-seccion="' + $(this).data("seccion") + '"]');
        var total = $preguntas.length;
        var contestadas = 0;

        $preguntas.each(function () {
            if (this.value != "")
                contestadas++;
        });

        var porcentaje = Math.round(contestadas * 100 / total, 2);
        if (isNaN(porcentaje))
            porcentaje = 0;


        $("#progress_seccion" + $(this).data("seccion")).attr("aria-valuenow", porcentaje).css("width", porcentaje + "%").html(porcentaje + "%");

    });




    if (respuestas)
        ajaxRequest("getRespuestas", {id: respuestas}, llenaRespuestas);

});


function llenaRespuestas(id, datos) {
    $(".progress").remove();
    $(".cs-select,.pregunta").attr("disabled", true).css("pointer-events", "none");

    for (var d in datos) {
        const el = document.getElementById("pregunta_"+d);


        if (el.classList.contains("cs-select")) {
            el.previousElementSibling.previousElementSibling.style.backgroundImage = 'url(/img/evaluacion/' + datos[d] + '.png)';
            el.parentNode.previousElementSibling.innerHTML = el.children[datos[d]].dataset.valor;
        }
        else
            el.value=datos[d];


    }
}



function guardaEvaluacion() {
    var formData = new FormData();

    var path = window.location.pathname.split("/");
    $("#overlay").addClass("super");
    showOverlay(true);



    $(".pregunta").each(function () {
        formData.append(this.id, this.value);

    });



    formData.append("token", path[3]);

    ajaxRequestFile("guardaEvaluacion", formData, finGuardaE);






}

function finGuardaE(id, datos) {
    $("#overlay").removeClass("super");
    showOverlay(false);
    ajaxNotification(id, datos);
    setTimeout(function(){
        document.location.href=document.location.href;
    },3500);
}




function init_select() {

    [].slice.call(document.querySelectorAll('select.cs-select')).forEach(function (el) {
        new SelectFx(el, {
            stickyPlaceholder: false,
            onChange: function (val) {
                var img = document.createElement('img');
                img.src = '/img/evaluacion/' + val + '.png';
                img.onload = function () {
                    el.previousElementSibling.previousElementSibling.style.backgroundImage = 'url(/img/evaluacion/' + val + '.png)';
                    el.parentNode.previousElementSibling.innerHTML = el.children[val].dataset.valor;


                    $preguntas = $('.pregunta[data-seccion="' + $(el).data("seccion") + '"]');
                    var total = $preguntas.length;
                    var contestadas = 0;


                    $preguntas.each(function () {
                        if (this.value != "")
                            contestadas++;
                    });




                    var porcentaje = Math.round(contestadas * 100 / total, 2);
                    if (isNaN(porcentaje))
                        porcentaje = 0;


                    $("#progress_seccion" + $(el).data("seccion")).attr("aria-valuenow", porcentaje).css("width", porcentaje + "%").html(porcentaje + "%");


                };
            }
        });



    });

}



