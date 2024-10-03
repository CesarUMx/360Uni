<?php
class Usuario extends PublicSchema {
    public $id;
    public $nombre;
    public $id_rol;
    public $password;
    
    

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("id_rol", "CRol", "id");
    }
    
    public function beforeCreate() {
        $config=$this->getDI()->getConfig();
        $this->password=$config->password->default_hash;
        return true;
    }
    
    
}
