<?php
$loader = new \Phalcon\Loader();
/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs([
    APP_PATH . $config->application->controllersDir,
    APP_PATH . $config->application->pluginsDir,
    APP_PATH . $config->application->libraryDir,
    APP_PATH . $config->application->modelsDir,
    APP_PATH . $config->application->componentsDir,
    APP_PATH . $config->application->vendorDir
])->register();

$loader->registerNamespaces([
    
    'PHPMailer\PHPMailer'  => APP_PATH . $config->application->vendorDir . '/phpmailer/phpmailer/src',
        'ZipStream'  => APP_PATH . $config->application->vendorDir . '/maennchen/zipstream-php/src',
    'MyCLabs\Enum'  => APP_PATH . $config->application->vendorDir . '/myclabs/php-enum/src',
        'PhpOffice'  => APP_PATH . $config->application->vendorDir . '/phpoffice/phpspreadsheet/src'
    
    
    ])->register();



$loader->registerClasses([
    'Services' => APP_PATH . 'app/Services.php'
]);