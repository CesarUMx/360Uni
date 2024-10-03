<?php
function conectaBD() {
    
        
    $conf=parse_ini_file(str_replace("\\","/",(dirname(__DIR__)))."/../app/config/config.ini", true) or die("No se ha leido el archivo de configuracion");
    
    $servername = $conf['database']['host'];
    $username = $conf['database']['username'];
    $password = $conf['database']['password'];
    $bd = $conf['database']['dbname'];

// Create connection
    
    
   
    
    
    $link = pg_connect("host=$servername dbname=$bd user=$username password=$password options='--client_encoding=UTF8'" ) or die("Error al conectarse al motor de base de datos");

// Check connection
    if (!$link) {
        die("Connection failed: ");
    }
    

    return $link;
}