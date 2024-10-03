<?php
date_default_timezone_set('America/Mexico_City');
error_reporting(E_ALL);



use Phalcon\Mvc\Application;
use Phalcon\Config\Adapter\Ini as ConfigIni;
try {

    define('APP_PATH', realpath('..') . '/');
    /**
     * Read the configuration
     */
    
    
    $config = new ConfigIni(APP_PATH . 'app/config/config.ini');
    
    /**
     * Auto-loader configuration
     */
    require APP_PATH . 'app/config/loader.php';
    $application = new Application(new Services($config));
    // NGINX - PHP-FPM already set PATH_INFO variable to handle route
        echo $application->handle(rawurldecode($_SERVER['REQUEST_URI']))->getContent();
} catch (Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}