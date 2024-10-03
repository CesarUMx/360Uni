<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

class CPeriodo extends Catalogo {

    public $id;
    public $nombre;
    public $fecha_inicio;
    public $fecha_fin;
    public $activo;

    public function initialize() {
        parent::initialize();
    }

    public function beforeSave() {
        $ant = CPeriodo::findFirst(["activo=true"]);
        $band = true;

        if ($this->activo) {
            $ant->activo = false;
            $band &= $ant->save();

            $evs = Evaluador::find();
            foreach ($evs as $ev) {
                $ev->enviado = false;
                $band &= $ev->save();
            }
            
            
            
        }
        
        if (!$band)
                echo "Error al seleccionar este periodo como activo";

        return $band;
    }

    public function validation() {
        $validator = new Validation();
        $validator->add('nombre', new Uniqueness([
                    'model' => $this,
                    'message' => "El nombre del período debe ser único"
        ]));

        return $this->validate($validator);
    }

}
