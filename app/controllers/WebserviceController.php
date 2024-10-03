<?php

header('Access-Control-Allow-Origin: *');

class WebServiceController extends \Phalcon\Mvc\Controller {

    public function initialize() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
    }

    public function indexAction() {
        header("HTTP/1.0 404 Not Found");
    }

    public function getAppsAction() {
        $result = array();
        $datos = array();
        $token = $this->request->getPost('token');

        if (isset($token)) {
            
            $usuario=$this->stringToArr($this->decrypt($token, $this->config->webservice->key));
            $acceso= Accesos::findFirst(["conditions"=>"usuario=?1","bind"=>[1=>$usuario["usuario"]]]);
            
            if($acceso) {
            
            
            if ($acceso->tipo_usuario !== "Administrador") {
                $apps = DetalleAplicacion::find(["id_acceso=?1", "bind" => [1 => $acceso->id]]);
                foreach ($apps as $ap)
                    $datos[] = $ap->Aplicacion;
            } else
                $datos = Aplicacion::find();
            foreach ($datos as $d)
                $result[] = get_object_vars($d);


            $result[] = ["url" => "https://start.mondragonmexico.edu.mx/index/logout", "icono" => "cecom", "nombre" => "Cerrar Sesión"];
            
            }
            else
                $result["error"]="No se ha encontrado la información de acceso";
            
        }
        else
            $result["error"]="No se ha enviado la información necesaria";
        echo json_encode($result);
    }
    
    
    public function setAplicacionAction() {
        $result = array();
        
        $usuario = $this->request->getPost('usuario');
        $app = $this->request->getPost('app');
        if (isset($usuario)&&isset($app)) {
            
            
            $acceso= Accesos::findFirst(["conditions"=>"usuario=?1","bind"=>[1=>$usuario]]);
            $aplicacion= Aplicacion::findFirst(["conditions"=>"nombre=?1","bind"=>[1=> ucfirst($app)]]);
            
            if($aplicacion&&$acceso) {
                $ant= DetalleAplicacion::findFirst(["conditions"=>"id_acceso=?1 and id_aplicacion=?2","bind"=>[1=>$acceso->id,2=>$acceso->id]]);
                if($ant)
                    $result["warning"]="El usuario ya tenia acceso a la aplicación";
                else {
                    $da=new DetalleAplicacion();
                    $da->id_acceso=$acceso->id;
                    $da->id_aplicacion=$aplicacion->id;
                    if($da->save())
                    
                        $result["success"]="El usuario ya tenia acceso a la aplicación";
                    else
                        $result["error"]="No se ha podido asignar la aplicación al usuario";
                }
                
            }
            else
                $result["error"]="No se ha encontrado la información necesaria";


            
        }
        else
            $result["error"]="No se ha enviado la información necesaria";
        echo json_encode($result);
    }
    
    
    
    
    

    public function getUsuarioAction() {
        $result = array();
        $nombre = $this->request->getPost("nombre");
        if (isset($nombre)) {

            $nombre = trim($nombre);
            $usuario = Accesos::findFirst(["conditions" => "usuario = ?1 and tipo_usuario<>?2", "bind" => [1 => $nombre, 2 => "Alumno"]]);
            if ($usuario)
                $result = ["nombre" => $usuario->nombre, "usuario" => $usuario->usuario];
        }
        echo json_encode($result);
    }

    private function encrypt($string, $key) {
        $iv = "3132333435363738";
        $encrypted = openssl_encrypt($string, 'AES-256-CBC', $key, $options = 0, $iv);
        return base64_encode($encrypted);
    }

    private function arrToString($arr) {
        return base64_encode(serialize($arr));
    }

    private function stringToArr($decrypted) {
        return unserialize(base64_decode($decrypted));
    }

    private function decrypt($encrypted, $key) {
        $data = base64_decode($encrypted);
        $iv = "3132333435363738";
        $decrypted = openssl_decrypt($data, 'AES-256-CBC', $key, $options = 0, $iv);

        return $decrypted;
    }

}
