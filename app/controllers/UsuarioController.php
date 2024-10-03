<?php

class UsuarioController extends ControllerBase {

    public function initialize() {
        parent::initialize();
        $this->assets

                ->addCss('css/global/ui.jqgrid-bootstrap.css')
                ->addJs('js/global/jquery.jqGrid.min.js')
                ->addJs('js/global/grid.locale-es.js')
                ->addJs('js/global/jqgrid_def.js')
               
               
               
                ->addJs('js/usuario/common.js');
        
    }

    public function indexAction() {
        $this->dispatcher->forward(['controller' => 'usuario', 'action' => 'alumno']);
    }

    public function alumnoAction() {
        $this->assets
                
                ->addJs('js/usuario/alumno.js');

        
    }

    public function directorAction() {
        $this->assets
                
                ->addJs('js/usuario/director.js');

        
    }

    public function administradorAction() {
        $this->assets
                
                ->addJs('js/usuario/administrador.js');

        
    }
    
    
    public function validadorAction() {
        $this->assets
                
                ->addJs('js/usuario/validador.js');

        
    }

}
