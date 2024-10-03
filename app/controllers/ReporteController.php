<?php


class ReporteController extends ControllerBase {

    public function initialize() {
        parent::initialize();
        
 
    }

    public function indexAction() {
        $this->assets
                ->addCss('css/global/ui.jqgrid-bootstrap.css')
                  
                ->addJs('js/global/jquery.jqGrid.min.js')
                ->addJs('js/global/grid.locale-es.js')
               
                ->addJs('js/global/jqgrid_def.js')
                ->addCss('css/reportes/apexcharts.css')
                ->addJs('js/reportes/apexcharts.min.js')
                 ->addJs('js/global/Chart.min.js')
                ->addJs('js/reportes/index.js');
        $user =  $this->session->get("usuario_tipo"); 
//        if ($this->session->get("usuario_tipo") !== "Administrador")
        if ($user !== "Administrador" && $user !== "Director" && $user !== "Validador")
                $this->assets->addJs('js/reportes/otro.js');
        
        

    }
    
    
    

    
    
    

}
