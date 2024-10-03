<?php

use Phalcon\Db\Column;

class AdminController extends ControllerBase {

    public function initialize() {
        parent::initialize();
        
        $this->view->setVar("menu",[["url"=>"/admin/","nombre"=>"Dashboard","icono"=>"fas fa-gear"],["url"=>"/admin/catalogos","nombre"=>"Catálogos","icono"=>"fas fa-gear"]]);
    }

    public function indexAction() {
        $this->dispatcher->forward(['controller' => 'admin', 'action' => 'dashboard']);

    }
    
    
    
    public function dashboardAction(){
         $this->assets
                ->addCss('css/global/ui.jqgrid-bootstrap.css')
                  
                ->addJs('js/global/jquery.jqGrid.min.js')
                ->addJs('js/global/grid.locale-es.js')
                ->addJs('js/global/jqgrid_def.js')
                ->addJs('js/global/Chart.min.js')
                ->addJs('js/admin/dashboard.js');
         
         
         $secciones=CSeccion::find(["order"=>"orden"]);
         
         $this->view->setVar("secciones", $secciones);
         
         
  
         
    }
    
    
    
    public function catalogosAction($catalogo = null){
        $this->assets
                ->addCss('css/global/ui.jqgrid-bootstrap.css')
                ->addJs('js/global/jquery.jqGrid.min.js')
                ->addJs('js/global/grid.locale-es.js')
                ->addJs('js/global/jqgrid_def.js')
                ->addCss('css/global/component-chosen.min.css')
                ->addJs('js/global/chosen.jquery.min.js')
                ->addJs('js/catalogos/catalogo.js');
        
        
        if (isset($catalogo) && $this->db->tableExists("catalogo_" . strtolower($catalogo))) {
            $nombre_catalogo = "catalogo_" . strtolower($catalogo);
            $relaciones = $this->mapaModelos->getRelacionesCatalogo($catalogo);

            $fields = $this->db->describeColumns($nombre_catalogo);
            $colmodel = array();
            foreach ($fields as $field) {
                $nombre = $field->getName();

                $columna = array();

                $columna["label"] = ucfirst(str_replace("_", " ", str_replace("id_", "", $nombre)));
                $columna["name"] = $nombre;
                $columna["editable"] = !$field->isAutoIncrement();
                $columna["hidden"] = $field->isAutoIncrement();
                $editrules = array();
                $editrules["required"] = $field->isNotNull();
                $editrules["numeric"] = $field->isNumeric();
                $editoptions = array();
                $size = $field->getSize();
                
                
                
                
                
                
                switch ($field->getType()) {


                    case Column::TYPE_BIGINTEGER:
                        //case Column::TYPE_INTEGER:
                        array_pop($editrules); //eliminar number por si causa problemas
                        $rel = null;

                        foreach ($relaciones as $relacion) {
                            $campos = $relacion->getFields();
                            if (is_array($campos)) {
                                
                            } else {
                                if ($campos == $nombre) {
                                    $rel = $relacion;
                                    break;
                                }
                            }
                        }
                        if ($rel != null && !$field->isPrimary()) {
                            $columna["edittype"] = "select";
                            $columna["formatter"] = "select";
                            $editoptions["multiple"] = false;
                            //$editoptions["buildSelect"]="creaCombo";
                            //$editoptions["dataUrl"]=$this->url->get("ajax/getCombo/".$rel->getReferencedModel()."/".$rel->getReferencedFields());
                            $value = "";
                            if (!$field->isNotNull())
                                $value = "0:Sin Valor;";



                            foreach ($this->mapaModelos->getValores($rel->getReferencedModel(), array("order" => "nombre ASC")) as $array)
                                $value .= $array["id"] . ":" . $array["nombre"] . ";";
                            $editoptions["value"] = substr($value, 0, -1);
                        } else {

                            $editrules["integer"] = true;
                            $columna["edittype"] = "custom";
                            $columna["hidden"] = $field->isAutoIncrement();
                            $editoptions["custom_element"] = "spinner_element";
                            $editoptions["custom_value"] = "spinner_value";
                            $editoptions["dataInit"] = "spinner_init";
                        }
                        break;

                    case Column::TYPE_INTEGER:
                        $editrules["integer"] = true;
                        $columna["edittype"] = "custom";
                        $columna["hidden"] = $field->isAutoIncrement();
                        $editoptions["custom_element"] = "spinner_element";
                        $editoptions["custom_value"] = "spinner_value";
                        $editoptions["dataInit"] = "spinner_init";

                        break;


                    case Column::TYPE_DATE:
                        $columna["formatter"] = "date";
                        $columna["formatoptions"] = array("srcformat" => "Y-m-d", "newformat" => "Y-m-d");
                        $editrules["date"] = true;
                        $editoptions["maxlengh"] = 10;
                        $editoptions["dataInit"] = "datepicker_element2";
                        break;

                    case Column::TYPE_VARCHAR:
                        if ($size > 200) {
                            $columna["edittype"] = "textarea";
                        } else {
                            $columna["edittype"] = "text";
                            $editoptions["maxlength"] = $size==0?20:$size;
                        }
                        break;

                    case Column::TYPE_DECIMAL:
                        $scale = $field->getScale();
                        $valor = "";
                        for ($i = 0; $i < ($size - $scale); $i++)
                            $valor .= "9";
                        $valor .= ".";
                        for ($i = 0; $i < $scale; $i++)
                            $valor .= "9";
                        $editrules["minValue"] = (float) ("-" . $valor);
                        $editrules["maxValue"] = (float) $valor;
                        $columna["formatter"] = "currency";
                        $columna["formatoptions"] = array("prefix" => '$', "thousandsSeparator" => ',', "decimalSeparator" => '.', "decimalPlaces" => $scale);

                        break;

                    case Column::TYPE_DATETIME:
                        break;

                    case Column::TYPE_ENUM:
                        $sql = "SHOW COLUMNS FROM " . $nombre_catalogo . " LIKE  '" . $nombre . "'";
                        $r = $this->db->query($sql)->fetch();
                        $value = "";
                        if (substr($r["Type"], 0, 4) === "enum") {
                            foreach (explode(',', substr($r["Type"], 5, -1)) as $e) {
                                $enum = substr($e, 1, -1);
                                $value .= $enum . ":" . $enum . ";";
                            }


                            $columna["edittype"] = "select";
                            $columna["formatter"] = "select";
                            $editoptions["multiple"] = false;


                            $editoptions["value"] = substr($value, 0, -1);
                        }

                        break;

                    case Column::TYPE_TEXT:
                        $columna["edittype"] = "textarea";
                        break;

                    case Column::TYPE_FLOAT:
                        break;

                    case Column::TYPE_BOOLEAN:
                        $columna["edittype"] = "checkbox";
                        $editoptions["value"] = "true:false";
                        break;

                    case Column::TYPE_DOUBLE:
                        break;
                }

                $columna["editrules"] = $editrules;
                $columna["editoptions"] = $editoptions;
                array_push($colmodel, $columna);
            }

            $this->view->setVar("colmodel", json_encode($colmodel));
            $this->view->setVar("catalogo", $catalogo);
        } else if (isset($catalogo)) {
            $this->flash->error("El catálogo no existe");
        }
        
        
    }
    
    
    public function getStatusEnvioAction() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');

        $sem_id = sem_get(271188, 1);
        $valor = 0;
        
        if (sem_acquire($sem_id)) {

            $shm_id = shmop_open("881127", "c", 0644, 5) or die("Imposible leer la de memoria");
             $valor = shmop_read($shm_id, 0, 5);
            shmop_close($shm_id);
            sem_release($sem_id);

        }
        echo "data: {$valor}" . PHP_EOL . PHP_EOL;
        flush();
        
       
    }
    
    
    
    
    public function getStatusEnvio2Action() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');

        $sem_id = sem_get(271199, 1);
        $valor = 0;
        
        if (sem_acquire($sem_id)) {

            $shm_id = shmop_open("991127", "c", 0644, 5) or die("Imposible leer la de memoria");
            $valor = shmop_read($shm_id, 0, 5);
            shmop_close($shm_id);
            sem_release($sem_id);

        }
        echo "data: {$valor}" . PHP_EOL . PHP_EOL;
        flush();
        
       
    }
    
    

}
