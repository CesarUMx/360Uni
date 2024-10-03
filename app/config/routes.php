<?php

use Phalcon\Mvc\Router;

$router = new Router();

$router->removeExtraSlashes(true);



//$router->setEventsManager($eventsManager);



$router->add('/restablecerPassword/{token}', [
    'controller' => 'index',
    'action'     => 'password'
])->setName('front.contact');



return $router;
