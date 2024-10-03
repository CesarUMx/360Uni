<?php
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;
class CTipopregunta extends Catalogo
{
    public $id;
    public $nombre;
    
    public function initialize()
    {
        parent::initialize();
        
        
    }
    
       public function validation() {


        $validator = new Validation();

        $validator->add('nombre', new Uniqueness([
            'model' => $this,
            'message' => "El nombre del tipo debe ser único"
        ]));

        return $this->validate($validator);
    }
}
