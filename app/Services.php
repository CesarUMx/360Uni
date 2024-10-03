<?php

use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Url;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Flash\Direct as FlashDirect;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Model\Metadata\Memory as MetaData;
use Phalcon\Session\Bag;

class Services extends \Base\Services {

    /**
     * We register the events manager
     */
    protected function initDispatcher() {
        $eventsManager = new EventsManager;
        /**
         * Check if the user is allowed to access certain action using the SecurityPlugin
         */
        //$eventsManager->attach('dispatch:beforeExecuteRoute', new SecurityPlugin);
        /**
         * Handle exceptions and not-found exceptions using NotFoundPlugin
         */
        $eventsManager->attach('dispatch:beforeException', new NotFoundPlugin);
        $dispatcher = new Dispatcher;
        $dispatcher->setEventsManager($eventsManager);
        return $dispatcher;
    }

    /**
     * The URL component is used to generate all kind of urls in the application
     */
    protected function initUrl() {
        $url = new Url();
        $url->setBaseUri($this->get('config')->application->baseUrl);
        return $url;
    }

    protected function initView() {
        $view = new View();
        $view->setViewsDir(APP_PATH . $this->get('config')->application->viewsDir);

        return $view;
    }

    /**
     * If the configuration specify the use of metadata adapter use it or use memory otherwise
     */
    protected function initModelsMetadata() {
        return new MetaData();
    }

    /**
     * Database connection is created based in the parameters defined in the configuration file
     */
    protected function initDb() {
        $config = $this->get('config')->get('database')->toArray();
        $dbClass = 'Phalcon\Db\Adapter\Pdo\\' . $config['adapter'];
        unset($config['adapter']);
        return new $dbClass($config);
    }
    
    
    protected function initDbUsuarios() {
        $config = $this->get('config')->get('dbo_usuarios')->toArray();
        $dbClass = 'Phalcon\Db\Adapter\Pdo\\' . $config['adapter'];
        unset($config['adapter']);
        return new $dbClass($config);
    }

    /**
     * Start the session the first time some component request the session service
     */
    protected function initSharedSession() {
        $session = new Manager();
        $files = new Stream(
                [
            'savePath' => sys_get_temp_dir(),
                ]
        );
        
        session_set_cookie_params(0, '/', '360.mondragonmexico.edu.mx');
        $session->setAdapter($files);
        $session->setName('competencias');
        $session->start();
        
        return $session;
    }
    
    protected function initSessionBag() {
        return new Bag('mondragon');
    }

    /**
     * Register the flash service with custom CSS classes
     */
    protected function initFlash() {
        $flash = new FlashDirect();
            $flash->setImplicitFlush(false);
            $flash->setCssClasses([
                'error' => 'alert alert-danger',
                'success' => 'alert alert-success',
                'notice' => 'alert alert-info',
                'warning' => 'alert alert-warning'
            ]);

            return $flash;
    }

   

    protected function initCorreo() {
        return new Correo();
    }

   protected function initPlantillas() {
        return new Plantillas();
    }
    
    protected function initToken() {
        return new Token();
    }
    
    protected function initmapaModelos() {
        return new mapaModelos();
    }
    
    protected function initExcel() {
        return new Excel();
    }
    
    protected function initSharedRouter(){
        return require APP_PATH . '/app/config/routes.php';
    }


}
