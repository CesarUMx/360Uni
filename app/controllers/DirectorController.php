<?php
class DirectorController extends ControllerBase {

    public function initialize() {
        parent::initialize();
        $this->assets
                
                ->addCss('css/global/component-chosen.min.css')
                ->addJs('js/global/chosen.jquery.min.js');
        
        
        
  
    }

    public function indexAction() {
        $this->dispatcher->forward(['controller' => 'director', 'action' => 'dashboard']);

    }
    
    
    
    public function dashboardAction(){
         $this->assets
                ->addCss('css/global/ui.jqgrid-bootstrap.css')
                  
                ->addJs('js/global/jquery.jqGrid.min.js')
                ->addJs('js/global/grid.locale-es.js')
                ->addJs('js/global/jqgrid_def.js')
                ->addJs('js/global/Chart.min.js')
                ->addJs('js/director/dashboard.js');   
    }
    
    
    public function seleccionesAction(){
         $this->assets
                ->addCss('css/global/ui.jqgrid-bootstrap.css') 
                ->addJs('js/global/jquery.jqGrid.min.js')
                ->addJs('js/global/grid.locale-es.js')
                ->addJs('js/global/jqgrid_def.js')
                ->addJs('js/global/Chart.min.js')
                ->addJs('js/director/selecciones.js');

    }
    
    public function preguntasAction(){
        $this->assets
                ->addCss('css/global/ui.jqgrid-bootstrap.css') 
                ->addCss('css/director/preguntas.css') 
                ->addJs('js/global/jquery.jqGrid.min.js')
                ->addJs('js/global/grid.locale-es.js')
                ->addJs('js/global/jqgrid_def.js')
                ->addJs('js/global/Chart.min.js')
                ->addJs('js/director/preguntas.js');
        
        
        
        $secciones= CSeccion::find(["order"=>"id"]);
        
        $this->view->setVar("secciones", $secciones);
        
    }
    
    
    
    
    
    
    

}
