<?php
class DetalleAlumno extends PublicSchema {
    public $id;
    public $id_usuario;
    public $id_functionesp;
    public $semestre;
    
    
    

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("id_usuario", "Usuario", "id");
        $this->belongsTo("id_funcionesp", "CFuncionesp", "id");
    }
}
