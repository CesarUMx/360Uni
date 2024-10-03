<?php

class CPregunta extends Catalogo
{
    public $id;
    public $nombre;
    public $id_seccion;
    public $id_funcionesp;
    public $id_categoria;
    public $id_tipopregunta;
    
    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("id_seccion", "CSeccion", "id");
        $this->belongsTo("id_funcionesp", "CFuncionesp", "id");
        $this->belongsTo("id_categoria", "CCategoria", "id");
        $this->belongsTo("id_tipopregunta", "CTipopregunta", "id");
        
    }
    
       
}
