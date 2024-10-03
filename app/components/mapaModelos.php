<?php

use \Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\Query;

class mapaModelos extends Injectable {

    public function getModelo($catalogo) {
        try {
            $reflectionClass = new ReflectionClass($catalogo);
            $modelo = $reflectionClass->newInstanceArgs();
        } catch (Exception $ex) {
            $modelo = null;
        }
        return $modelo;
    }
    
    public function getRelaciones($catalogo) {
        try {
            $modelo = $this->getModelo($catalogo);
            $this->modelsManager->initialize($modelo);
            $relaciones = $this->modelsManager->getRelations($catalogo);
        } catch (Exception $ex) {
            $relaciones = array();
        }
        return $relaciones;
    }

    public function getRelacionesCatalogo($catalogo) {
        return $this->getRelaciones($this->getCatalogoName($catalogo));
    }

    public function getCatalogoName($key) {
        return "C" . ucwords($key);
    }

    public function getValores($modelo, $filtro = "") {
        $rows = $modelo::find($filtro);
        $rows->setHydrateMode(Resultset::HYDRATE_ARRAYS);
        return $rows;
    }
    
    public function saveCatalogo($catalogo,$valores) {
        return $this->saveRecord($this->getModelo($catalogo),$valores);
    }

    public function saveRecord($modelo, $valores) {
        $result = "Error al guardar el registro en ".$modelo->getSource();
        if (isset($modelo)) {
            foreach ($valores as $k => $v) {
                //$modelo->$k = is_string($k)?utf8_decode($v):$v;
                $modelo->$k = trim($v);
            }
            if ($modelo->save() == false) {
                foreach ($modelo->getMessages() as $message) {
                    $result.= " ".$message . ".";
                }
            } else {
                $result = "Ok";
                if(isset($modelo->id))
                    $result.=$modelo->id;
            }
        }
        return $result;
    }
    
    
    public function updateCatalogo($catalogo,$valores) {
        return $this->updateRecord($catalogo,$valores);
    }
    
    
    
    public function updateRecord($modelo, $valores) {
        $result = "Error al actualizar el registro en " . $modelo;
        if (isset($modelo) && isset($valores["id"])) {
            $row = $modelo::findFirst($valores["id"]);
            if ($row) {
                unset($valores["id"]);


                foreach ($valores as $k => $v) {
                    
                    $row->$k = $v;
                }
                if ($row->update() === false) {
                    foreach ($row->getMessages() as $message) {
                        $result.= " " . $message . ".";
                    }
                } else {
                    $result = "Ok";
                    if (isset($row->id))
                        $result.=$row->id;
                }
            }
        }
        return $result;
    }

    public function deleteCatalogo($catalogo, $id) {
        return $this->deleteRecord($catalogo, $id);
    }

    public function deleteRecord($modelo, $id) {
        $result = "Error al eliminar el registro";
        if (isset($modelo)) {
            $row = $modelo::findFirst($id);
            if ($row != false) {
                if ($row->delete()) {
                    $result = "Ok";
                } else {
                    foreach ($row->getMessages() as $message) {
                        $result.= $message . ".";
                    }
                }
            }
        }
        return $result;
    }
    
    public function query($sql) {
        $query=new Query($sql,$this->getDI());
        return $query->execute();
    }

}
