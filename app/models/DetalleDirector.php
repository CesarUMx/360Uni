<?php
class DetalleDirector extends PublicSchema {
    public $id;
    public $id_usuario;
    public $id_carrera;
    
    
    
    

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("id_director", "Usuario", "id");
        $this->belongsTo("id_carrera", "CCarrera", "id");
        
    }
}
