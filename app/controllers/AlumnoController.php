<?php


class AlumnoController extends ControllerBase {

    public function initialize() {
        parent::initialize();
        $this->assets
                ->addJs('js/alumno/common.js')
                ->addCss('css/global/component-chosen.min.css')
                ->addJs('js/global/chosen.jquery.min.js');
        
        
        $valido=false;
        $completo=false;
        $detalle= DetalleAlumno::findFirst(["conditions"=>"id_usuario=?1","bind"=>[1=>intval($this->session->get("usuario_id"))]]);
        
        if($detalle){
            $completo=true;
            $valido=$detalle->validado;
        }
        
        
        
        $this->view->setVar("menu",[["url"=>"/alumno/","nombre"=>"Dashboard","icono"=>"fas fa-chart-simple"],["url"=>"/alumno/autoevaluacion","nombre"=>"Autoevaluación","icono"=>"fas fa-file-signature"]]);
        
        $this->view->setVar("valido",$valido);
        $this->view->setVar("completo",$completo);
        
        
    }

    public function indexAction() {
        $this->dispatcher->forward(['controller' => 'alumno', 'action' => 'dashboard']);

    }
    
    
    
    public function dashboardAction(){
         $this->assets
                ->addCss('css/global/ui.jqgrid-bootstrap.css')
                  
                ->addJs('js/global/jquery.jqGrid.min.js')
                ->addJs('js/global/grid.locale-es.js')
                ->addJs('js/global/jqgrid_def.js')
                ->addJs('js/global/Chart.min.js')
                ->addJs('js/alumno/dashboard.js');
         
         
         $secciones=CSeccion::find(["order"=>"orden"]);
         
         $this->view->setVar("secciones", $secciones);
         
         
         
         
         
         
         
    }
    
    
    public function autoevaluacionAction(){
        
         $this->dispatcher->forward(['controller' => 'evaluador', 'action' => 'index']);
         
         
         
    }
    
    
    public function misEvaluacionesAction(){

          
    }
    
    
    

}
