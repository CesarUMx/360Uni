


var imagen = document.getElementById("_avatar");


imagen.src = "https://hpcdata.mondragonmexico.edu.mx/usuarios/" + id_usuario + "/avatar.png";



if(!imagen.complete){ 
            imagen.src = "/img/avatar.png";
        }