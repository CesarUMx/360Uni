<?php
class DetalleValidador extends PublicSchema {
    public $id;
    public $id_validador;
    public $id_carrera;
    
    
    
    

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("id_validador", "Usuario", "id");
        $this->belongsTo("id_carrera", "CCarrera", "id");
        
    }
}
