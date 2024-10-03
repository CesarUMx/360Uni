<?php

class ControllerBase extends \Phalcon\Mvc\Controller {

    protected $menu;
    protected $submenu;
    private $assetsSent = false;

    public function initialize() {
        if (!$this->assetsSent) {

            $this->assets
                    ->addCss('css/global/font-awesome.min.css')
                    ->addCss('https://use.typekit.net/ngq1jna.css', null, false)
                    ->addCss('css/global/toastr.min.css')
                    ->addCss('https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&family=Roboto&display=swap', null, false)
                    ->addCss('plantilla/plugins/metismenu/css/metisMenu.min.css')
                    ->addCss('plantilla/bootstrap.min.css')
                    ->addCss('plantilla/app.css')
                    ->addCss('css/global/jquery-ui.css')
                    ->addCss('css/global/tippy.css')
                    ->addCss('css/custom/custom.css')
                    ->addJs('plantilla/bootstrap.bundle.min.js')
                    ->addJs('plantilla/jquery.min.js')
                    ->addJs('plantilla/plugins/metismenu/js/metisMenu.min.js')
                    ->addJs('js/global/jquery-ui.min.js')
                    ->addJs('js/global/jquery-migrate.min.js')
                    ->addJs('js/global/jquery.datepicker-es.js')
                    ->addJs('js/global/modernizr.custom.js')
                    ->addJs('js/global/classie.js')
                    ->addJs('js/global/toastr.min.js')
                    ->addJs('js/global/popper.min.js')
                    ->addJs('js/global/common.js')
                    ->addJs('js/global/common.js')
                    ->addJs('plantilla/app.js');

            $this->assetsSent = true;
        }
    }

    public function afterExecuteRoute($dispatcher) {
        $this->submenu = array();

        if ($this->session->has("usuario_tipo")) {




                $this->menu = $this->modelsManager->createBuilder()
                        ->from(array("m" => 'CMenu'))
                        ->leftJoin('CMenurol', 'm.id = mr.id_menu', 'mr')
                        ->leftJoin('CRol', 'mr.id_rol = r.id', 'r')
                        ->where("r.id=" . $this->session->get("usuario_rol"))
                        ->andWhere("m.id_menu=0")
                        ->orderBy("m.orden")
                        ->getQuery()
                        ->execute();

                foreach ($this->menu as $menu)
                    $this->submenu[$menu->id] = $this->modelsManager->createBuilder()
                            ->from(array("m" => 'CMenu'))
                            ->leftJoin('CMenurol', 'm.id = mr.id_menu', 'mr')
                            ->leftJoin('CRol', 'mr.id_rol = r.id', 'r')
                            ->where("r.id=" . $this->session->get("usuario_rol"))
                            ->andwhere("m.id_menu=" . $menu->id)
                            ->orderBy("m.orden")
                            ->getQuery()
                            ->execute();
            
        }













        $this->view->setVar("menu", $this->menu);
        $this->view->setVar("submenu", $this->submenu);
        
        
        
        $this->view->setVar("id_avatar", $this->session->get("usuario_id"));

        $periodo = CPeriodo::findFirst(["activo=true"]);
        
        $periodos = CPeriodo::find();
        $this->view->setVar("periodos", $periodos);

        if ($periodo) 
            $this->view->setVar("periodo", $periodo);
            
        
    }

}
