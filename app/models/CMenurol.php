<?php

class CMenurol extends Catalogo
{
    public $id;
    public $id_rol;
    public $id_menu;
    
    
    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("id_rol", "CRol", "id");
        $this->belongsTo("id_menu", "CMenu", "id");
    }
}
