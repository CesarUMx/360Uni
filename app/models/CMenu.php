<?php
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;
class CMenu extends Catalogo
{
    public $id;
    public $nombre;
    
    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("id_menu", "CMenu", "id");
        
    }
    
       public function validation() {


        $validator = new Validation();

        $validator->add('nombre', new Uniqueness([
            'model' => $this,
            'message' => "El nombre del menú debe ser único"
        ]));

        return $this->validate($validator);
    }
}
