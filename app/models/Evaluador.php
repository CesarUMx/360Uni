<?php

class Evaluador extends PublicSchema
{
    public $id;
    public $nombre;
    public $telefono;
    public $correo;
    public $id_rol;
    public $activo;
    public $id_alumno;
    
    
    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("id_rol", "RolEvaluador", "id");
        $this->belongsTo("id_alumno", "Usuario", "id");
    }
    
    public function beforeCreate(){
        
        $revisar= RolEvaluador::findFirst($this->id_rol);
        

        $this->validado=$revisar->autovalidado;

        return $revisar;
    }
    
    

    
    

}