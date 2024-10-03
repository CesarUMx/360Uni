<?php

include_once 'lib/email.php';
include_once 'lib/bd.php';

$shm_id = shmop_open(881127, "c", 0644, 5) or die("Imposible crear el segmento de memoria");
$shm_id2 = shmop_open(881111, "a", 0644, 1) or die("Imposible crear el segmento de memoria");

shmop_write($shm_id, "99999", 0);

$sem_id = sem_get(271188, 1);
$isAlive = true;

file_put_contents('/tmp/envios.log', "Iniciando envios: " . date('d-m-Y H:s:i') . PHP_EOL, FILE_APPEND);

date_default_timezone_set('America/Mexico_City');
$bdLink = conectaBD();

$base_path = __DIR__ . "/../app/plantillashtml/";

$path1 = $base_path . "evaluador.html";



shmop_write($shm_id2, "0", 0);

$plantilla = "";
$query = pg_query($bdLink, "select e.id,e.nombre as nombre,a.nombre as alumno,e.correo,a.id as id_alumno from public.evaluador as e inner join public.usuario as a on e.id_alumno=a.id where e.activo=true and e.enviado=false");

if (file_exists($path1))
    $plantilla = file_get_contents($path1);

if ($query && $plantilla != "") {

    $result = $plantilla;
    $pivote = pg_num_rows($query);

//echo pg_num_rows($query);
    
    $query_periodo = pg_query($bdLink, "select * from public.catalogo_periodo where activo=true");
    $periodo=2;
    
    while ($rowP = pg_fetch_assoc($query_periodo))
            $periodo=$rowP["id"];
    
    



    while ($row = pg_fetch_assoc($query)) {
        
        $result = file_get_contents($path1);


        $result = str_replace("%_nombre_%", $row["nombre"], $result);

        $result = str_replace("%_alumno_%", $row["alumno"], $result);
        
        $email = trim($row["correo"]);
        
        
        $url=encrypt(arrToString(["evaluador" => $email, "evaluado" =>$row["id_alumno"] , "periodo" => $periodo]), "%M0ndr4g0n%");
        
        
        
        
        $result = str_replace("%_url_%", $url, $result);

        
        //$email = "vantware@gmail.com";

        echo "enviando a " . $email;

//agregar validacion de correo

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            
            $res_correo=envia("Evaluación de Competencias", $result, trim($email));
            
            if($res_correo)
                    pg_query($bdLink, "update public.evaluador set enviado=true where id=".$row["id"]);
            else
                pg_query($bdLink, "update public.evaluador set error_correo='".substr($res_correo,0,1995)."' where id=" . $row["id"]);
        }
                    
        
                    
                    
        sleep(1);

        $pivote--;



        shmop_write($shm_id, str_pad($pivote, 5, "0", STR_PAD_LEFT), 0);
        //file_put_contents('/tmp/envios.log', "Restantes: " . $pivote . PHP_EOL, FILE_APPEND);

        $activo = shmop_read($shm_id2, 0, 1);
        if ($activo === "0")
            $isAlive = false;
        
        
        
        
        
               if(!$isAlive)
           break;
        
    }
}

function invierte_fecha($fecha) {
    return date('d-m-Y', strtotime($fecha));
}




 function encrypt($string, $key) {
        $iv = "3132333435363738";
        $encrypted = openssl_encrypt($string, 'AES-256-CBC', $key, $options=0, $iv);
        return base64_encode($encrypted);
    }

     function arrToString($arr) {
        return base64_encode(serialize($arr));
    }

     function stringToArr($decrypted) {
        return unserialize(base64_decode($decrypted));
    }

     function decrypt($encrypted, $key) {
        $data= base64_decode($encrypted);
        $iv = "3132333435363738";
        $decrypted = openssl_decrypt($data, 'AES-256-CBC', $key, $options=0, $iv);

        return $decrypted;
    }