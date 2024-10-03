<?php
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;
class CCategoria extends Catalogo
{
    public $id;
    public $id_seccion;
    public $nombre;
    
    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("id_seccion", "CSeccion", "id");
        
    }
    
       public function validation() {


        $validator = new Validation();

        $validator->add('nombre', new Uniqueness([
            'model' => $this,
            'message' => "El nombre de la sección debe ser único"
        ]));

        return $this->validate($validator);
    }
}
