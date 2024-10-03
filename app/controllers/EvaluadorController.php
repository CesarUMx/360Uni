<?php

class EvaluadorController extends ControllerBase {

    public function initialize() {
        parent::initialize();
    }

    public function indexAction() {
        $this->dispatcher->forward(['controller' => 'evaluador', 'action' => 'evaluacion']);
    }

    public function evaluacionAction($token = null) {
        $this->assets
                ->addCss('css/global/ui.jqgrid-bootstrap.css')
                ->addCss('css/alumno/cs-select.css')
                ->addCss('css/alumno/cs-skin-circular.css')
                ->addCss('css/global/particles.css')
                ->addCss('css/alumno/autoevaluacion.css')
                ->addJs('js/global/jquery.jqGrid.min.js')
                ->addJs('js/global/grid.locale-es.js')
                ->addJs('js/global/jqgrid_def.js')
                ->addJs('js/alumno/selectFx.js')
                ->addJs('js/global/anime.min.js')
                ->addJs('js/global/particles.js')
                ->addJs('js/evaluador/evaluacion.js');
        
        
        
       

        
        $da = false;
        if (isset($token)) {
            $arreglo = $this->token->stringToArr($this->token->decrypt($token, $this->config->webservice->key));

            if (is_array($arreglo)) {
                $da = DetalleAlumno::findFirst(["id_usuario=?1", "bind" => [1 => intval($arreglo["evaluado"])]]);
                $periodo = CPeriodo::findFirst(intval($arreglo["periodo"]));

            }
        } else {
            $da = DetalleAlumno::findFirst(["id_usuario=?1", "bind" => [1 => intval($this->session->get("usuario_id"))]]);
            
            $periodo = CPeriodo::findFirst(["activo=true"]);

            if ($da) {
                $arreglo["evaluado"] = $da->id_usuario;
                $arreglo["evaluador"] = $da->Usuario->correo;
            }
        }


        
        
        
        

        if (!$periodo)
            $this->flash->error("No existe el periodo seleccionado");
        else if (!$da)
            $this->flash->error("No existe el usuario al que desea evaluar, Favor de verificar la liga de acceso");
        
        
       

        else if ($da) {

            $ant = Respuesta::findFirst(["conditions" => "id_periodo=?1 and id_alumno=?2 and correo=?3", "bind" => [1 => $periodo->id, 2 => intval($arreglo["evaluado"]), 3 => $arreglo["evaluador"]]]);

            if ($ant) {
                $this->flash->notice("Ya existe una evaluación realizada el dia " . $this->invierteFecha($ant->fecha, 'd-m-Y'));
                $this->view->setVar("respuestas", $ant->id);
            }
            
            
            if(!$ant&&isset($token)&&intval($this->session->get("usuario_id"))){
                $this->flash->error("No es posible acceder a esta evaluacion");
                $this->view->setVar("respuestas", -1);
            }



            $secciones = CSeccion::find(["order" => "orden"]);
            $this->view->setVar("secciones", $secciones);
            $this->view->setVar("da", $da);
            $this->view->setVar("id_avatar2", $arreglo["evaluado"]);
            
            
            
        } else
            $this->flash->success("No se han encontrado datos del evaluador");
    }

    private function invierteFecha($fecha, $formato = 'Y-m-d') {
        return date($formato, strtotime($fecha));
    }

}
