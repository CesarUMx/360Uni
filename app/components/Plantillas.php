<?php

use \Phalcon\Di\Injectable;

class Plantillas extends Injectable {
    public function getPlantilla($plantilla,$variables=null) {
        $path="../".$this->config->application->plantillasDir."/".$plantilla.".html";
        $result="";
        if(file_exists($path)) {
            $result=file_get_contents ($path);
            if(isset($variables)&& is_array($variables))
            foreach ($variables as $k=>$v)
                $result=str_replace("%_".$k."_%", $v,$result);
            
        }
        
        return $result;
        
        
    }
    
    public function setPlantilla($plantilla,$html) {
        $path="../".$this->config->application->plantillasDir."/";
        if(!file_exists($path))
            if(!mkdir($path,0744))
                    return false;
        return file_put_contents($path.$plantilla.".html", $html);        
        
    }
    

}
