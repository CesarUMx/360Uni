<?php

include_once 'lib/email.php';
include_once 'lib/bd.php';

$shm_id = shmop_open(991127, "c", 0644, 5) or die("Imposible crear el segmento de memoria");
$shm_id2 = shmop_open(991111, "a", 0644, 1) or die("Imposible crear el segmento de memoria");

shmop_write($shm_id, "99999", 0);

$sem_id = sem_get(271199, 1);
$isAlive = true;

file_put_contents('/tmp/bienvenida.log', "Iniciando envios: " . date('d-m-Y H:s:i') . PHP_EOL, FILE_APPEND);

date_default_timezone_set('America/Mexico_City');
$bdLink = conectaBD();

$base_path = __DIR__ . "/../app/plantillashtml/";

$path1 = $base_path . "bienvenida.html";

shmop_write($shm_id2, "0", 0);

$plantilla = "";
$query = pg_query($bdLink, "select * from public.usuario where activo=true and enviado=false");

if (file_exists($path1))
    $plantilla = file_get_contents($path1);

if ($query && $plantilla != "") {

    $result = $plantilla;
    $pivote = pg_num_rows($query);

//echo pg_num_rows($query);



    while ($row = pg_fetch_assoc($query)) {

        $result = file_get_contents($path1);

        $email = trim($row["correo"]);
        //$email = "vantware@gmail.com";

        echo "enviando a " . $email;

//agregar validacion de correo

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $res_correo=envia("Bienvenido a la plataforma", $result, trim($email));
            if ($res_correo)
                pg_query($bdLink, "update public.usuario set enviado=true where id=" . $row["id"]);
            else
                pg_query($bdLink, "update public.usuario set error_correo='".substr($res_correo,0,1995)."' where id=" . $row["id"]);
        }



        sleep(1);

        $pivote--;

        shmop_write($shm_id, str_pad($pivote, 5, "0", STR_PAD_LEFT), 0);
        file_put_contents('/tmp/bienvenida.log', "Restantes: " . $pivote . PHP_EOL, FILE_APPEND);

        $activo = shmop_read($shm_id2, 0, 1);
        if ($activo === "0")
            $isAlive = false;



        if (!$isAlive)
            break;
    }
    file_put_contents('/tmp/bienvenida.log', "Fin envios: " . date('d-m-Y H:s:i') . PHP_EOL, FILE_APPEND);
}

function invierte_fecha($fecha) {
    return date('d-m-Y', strtotime($fecha));
}
