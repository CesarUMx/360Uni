<?php

class Respuesta extends PublicSchema
{
    public $id;
    public $id_alumno;
    public $id_periodo;
    public $correo;
    public $fecha;

    
    
    
    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("id_alumno", "Usuario", "id");
        $this->belongsTo("id_periodo", "CPeriodo", "id");
        $this->hasMany("id", "DetalleRespuesta", "id_respuesta");
        
    }
    
    

    
    

}