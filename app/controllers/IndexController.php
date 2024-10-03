<?php

use Phalcon\Mvc\Controller;

class IndexController extends Controller {

    public function initialize() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_LAYOUT);
    }

    public function indexAction() {

        if ($this->session->has("usuario"))
            if ($this->session->get("usuario_tipo") === "Administrador")
                $this->response->redirect('/admin/dashboard');
            else if ($this->session->get("usuario_tipo") === "Director")
                $this->response->redirect('/director/dashboard');

            else if ($this->session->get("usuario_tipo") === "Validador")
                $this->response->redirect('/validador/dashboard');
            else //es alumno
                $this->response->redirect('/alumno/dashboard');
        else
            $this->assets
                    ->addCss('css/global/font-awesome.min.css')
                    ->addCss('css/global/toastr.min.css')
                    ->addCss('plantilla/bootstrap.min.css')
                    ->addCss('plantilla/app.css')
                    ->addCss('css/custom/custom.css')
                    ->addCss('css/index/index.css')
                    ->addJs('js/global/jquery-3.4.1.min.js')
                    ->addJs('js/global/jquery-ui.min.js')
                    ->addJs('js/global/modernizr.custom.js')
                    ->addJs('js/global/classie.js')
                    ->addJs('js/global/toastr.min.js')
                    ->addJs('js/global/popper.min.js')
                    ->addJs('js/global/tippy.all.min.js')
                    ->addJs('js/index/index.js');
    }

    public function menuAction() {
        $this->assets
                ->addCss('https://use.typekit.net/ngq1jna.css', null, false)
                ->addCss('https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&family=Roboto&display=swap', null, false)
                ->addCss('css/custom/custom.css')
                ->addCss('css/global/font-awesome.min.css')
                ->addCss('css/index/menu.css')
                ->addJs('js/index/menu.js');

        if ($this->session->get("usuario_tipo") === "Administrativo") {
            $apps = DetalleAplicacion::find(["id_acceso=?1", "bind" => [1 => $this->session->get("usuario_id")]]);
            foreach ($apps as $ap)
                $datos[] = $ap->Aplicacion;

            $datos = Aplicacion::find(["conditions" => "common=true"]);
            foreach ($datos as $d)
                $datos[] = $d;
        } else if ($this->session->get("usuario_tipo") === "Alumno")
            $datos = Aplicacion::find(["conditions" => "alumnos=true or common=true"]);

        else if ($this->session->get("usuario_tipo") === "Administrador")
            $datos = Aplicacion::find();
        else
            $datos = Aplicacion::find(["conditions" => "common=true"]);


        $this->view->setVar("menu", $datos);
    }

    public function logOutAction() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
        $this->flash->notice("Sesión cerrada correctamente. Gracias por utilizar el sistema");
        $this->session->destroy();
        $this->response->redirect('/');
    }

    public function error404Action() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_LAYOUT);
        $this->assets
                ->addCss('plantilla/bootstrap.min.css')
                ->addCss('plantilla/app.css')
                ->addJs('plantilla/bootstrap.bundle.min.js');
    }

    public function error500Action() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_LAYOUT);
        $this->assets
                ->addCss('plantilla/bootstrap.min.css')
                ->addCss('plantilla/app.css')
                ->addJs('plantilla/bootstrap.bundle.min.js');
    }

    public function error401Action() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_LAYOUT);
        $this->assets
                ->addCss('plantilla/bootstrap.min.css')
                ->addCss('plantilla/app.css')
                ->addJs('plantilla/bootstrap.bundle.min.js');
    }

    public function perfilAction() {

        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);

        $this->assets
                ->addCss('plantilla/bootstrap.min.css')
                ->addCss('plantilla/app.css')
                ->addJs('plantilla/bootstrap.bundle.min.js');
    }

    public function passwordAction($token = null) {



        if (!isset($token))
            $this->response->redirect('/');
        else {


            $usuario = $this->token->decrypt($token, $this->config->webservice->key);

            if (intval($usuario) > 0) {




                $this->assets
                        ->addCss('css/global/font-awesome.min.css')
                        ->addCss('css/global/toastr.min.css')
                        ->addCss('plantilla/bootstrap.min.css')
                        ->addCss('plantilla/app.css')
                        ->addCss('css/custom/custom.css')
                        ->addCss('css/index/index.css')
                        ->addJs('js/global/jquery-3.4.1.min.js')
                        ->addJs('js/global/jquery-ui.min.js')
                        ->addJs('js/global/modernizr.custom.js')
                        ->addJs('js/global/classie.js')
                        ->addJs('js/global/toastr.min.js')
                        ->addJs('js/global/popper.min.js')
                        ->addJs('js/global/tippy.all.min.js')
                        ->addJs('js/index/password.js');
                
                
                $this->view->setVar("token", $token);
            } else
                $this->response->redirect('/');
        }
    }

}
