<?php
class DetalleRespuesta extends PublicSchema {
    public $id;
    public $id_respuesta;
    public $id_pregunta;
    public $valor;
    
    
    

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("id_respuesta", "Respuesta", "id");
        $this->belongsTo("id_pregunta", "CPregunta", "id");
    }
}
