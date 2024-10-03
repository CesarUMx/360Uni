<?php
class CFuncionesp extends Catalogo {
    public $id;
    public $id_carrera;
    public $nombre;
    
    

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("id_carrera", "CCarrera", "id");
    }
}
