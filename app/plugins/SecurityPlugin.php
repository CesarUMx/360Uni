<?php

use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Acl\Component;
use Phalcon\Acl\Role;
use Phalcon\Acl\Enum;
use Phalcon\Di\Injectable;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;

/**
 * SecurityPlugin
 *
 * This is the security plugin which controls that users only have access to the modules they're assigned to
 */
class SecurityPlugin extends Injectable {

    /**
     * Returns an existing or new access control list
     *
     * @returns AclList
     */
    protected function getAcl(): AclList {
        if (isset($this->persistent->acl)) {
            return $this->persistent->acl;
        }

        $acl = new AclList();
        $acl->setDefaultAction(Enum::DENY);
        $roles = array();
        // Register roles


        $rol = new Role("usuario");
        $roles["usuario"] = $rol;

        $acl->addRole($rol);

        //Private area resources

        $privateResources = array(
            'index' => array('menu', 'logout','perfil')
        );

        foreach ($privateResources as $resource => $actions) {
            $acl->addComponent(new Component($resource), $actions);
        }

        //Grant access to private area to role Users
        foreach ($privateResources as $resource => $actions) {
            foreach ($actions as $action) {
                $acl->allow("usuario", $resource, $action);
            }
        }



        //Public area resources
        $publicResources = array(
            'index' => array('index', 'error404','error401', 'error500', 'menu', 'logout','ocr','password'),
            'ajax' => array('index', 'login','setResetPass','setPassword'),
            'webservice' => array('index', 'getApps', 'getUsuario','setAplicacion'),
        );
        foreach ($publicResources as $resource => $actions) {
            $acl->addComponent(new Component($resource), $actions);
        }

        $roles["invitado"] = new Role('Invitado', 'Cualquiera que navegue en el sistema.');
        $roles["usuario"] = new Role('Usuario', 'Cualquiera logeado sistema.');
        $acl->addRole($roles["invitado"]);
       $acl->addRole($roles["usuario"]);

        //Grant access to public areas to both users and guests
        foreach ($roles as $role) {
            foreach ($publicResources as $resource => $actions) {
                foreach ($actions as $action) {
                    $acl->allow($role->getName(), $resource, $action);
                }
            }
        }










        //The acl is stored in session, APC would be useful here too
        $this->persistent->acl = $acl;

        return $acl;
    }

    /**
     * This action is executed before execute any action in the application
     *
     * @param Event $event
     * @param Dispatcher $dispatcher
     * @return bool
     */
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher) {
        if (!$this->session->has('usuario'))
            $role = 'Invitado';
        else
            $role = 'Usuario';

        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();
        
        
       
        $acl = $this->getAcl();
        
        
             

        if (!$acl->isComponent($controller)) {
            $dispatcher->forward([
                'controller' => 'index',
                'action' => 'error404',
            ]);

            return false;
        }
        $allowed = $acl->isAllowed($role, $controller, $action);
        if (!$allowed) {
            $dispatcher->forward([
                'controller' => 'index',
                'action' => 'error404',
            ]);

            

            return false;
        }




        return true;
    }

}
