<?php
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;
class CSeccion extends Catalogo
{
    public $id;
    public $nombre;
    
    public function initialize()
    {
        parent::initialize();
        $this->hasMany("id", "CPregunta", "id_seccion");
        
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
