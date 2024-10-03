<?php

class AjaxController extends \Phalcon\Mvc\Controller {

    public function initialize() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
    }

    public function beforeExecuteRoute($dispatcher) {
        if (!$this->request->isPost() || !$this->request->isAjax()) {
            $dispatcher->forward(
                    array(
                        'controller' => 'index',
                        'action' => 'error404'
                    )
            );
        }
    }

    public function indexAction() {
        echo json_encode(array());
    }

    public function loginAction() {
        $result = array();
        $usuario = $this->request->getPost('usuario');
        $password = $this->request->getPost('password');
        if (isset($usuario) && isset($password) && !empty($usuario) && !empty($password)) {

            $pass = sha1($password . $this->config->password->sal);
            $user = Usuario::findFirst(["conditions" => "correo=?1 and password=?2", "bind" => [1 => $usuario, 2 => $pass]]);

            if ($user) {
                $this->session->set("usuario_nombre", $user->nombre);
                $this->session->set("usuario", $user->correo);
                $this->session->set("usuario_id", $user->id);
                $this->session->set("usuario_tipo", $user->CRol->nombre);
                $this->session->set("usuario_rol", $user->id_rol);
                $result["success"] = "Bienvenido " . $this->session->get("usuario_nombre");

                $user->last_login = date('Y-m-d');
                $user->save();
            } else
                $result["error"] = "Usuario y/o contraseña inválidas. Intente de nuevo porfavor ";
        } else
            $result["error"] = "Favor de proporcionar credenciales válidas para acceder al sistema";
        echo json_encode($result);
    }

    public function limpiaNCAction() {
        $id = $this->request->getPost('id');
        $result = [];

        if (isset($id)) {
            $usuario = Usuario::findFirst(intval($id));
            $evaluador = Evaluador::findFirst(intval($id));

            if ($usuario) {
                $usuario->error_correo = null;
                if ($usuario->save())
                    $result["success"] = "Registro actualizado correctamente";
                else
                    $result["error"] = "Se ha producido un error al actualizar el registro";
            } else if ($evaluador) {

                $evaluador->error_correo = null;
                if ($evaluador->save())
                    $result["success"] = "Registro actualizado correctamente";
                else
                    $result["error"] = "Se ha producido un error al actualizar el registro";
            } else
                $result["error"] = "No se ha encontrado el registro correspondiente";
        } else
            $result["error"] = "No se ha enviado la información necesaria";

        echo json_encode($result);
    }

    public function getDatosGridAction() {
        $filtro = array();
        $id = $this->request->getPost('valor');
        $campo = $this->request->getPost('campo');
        $modelo = $this->request->getPost('modelo');
        $condiciones = $this->request->getPost('filtro');

        if (isset($condiciones)) {
            $bind = [];
            $conds = "";

            foreach ($condiciones as $k => $v) {
                $conds .= $k . "=:" . $k . ": and ";
                $bind[$k] = $v;
            }
            $conds .= " id is not null";
            $filtro = array($conds, "bind" => $bind);
        } else if (isset($id) && isset($modelo) && isset($campo))
            $filtro = array($campo . "=:" . $campo . ":", "bind" => array($campo => $id));
        echo $this->getDatosGrid($modelo, $filtro);
    }

    public function reenviaCorreoAction() {
        $id = $this->request->getPost('id');

        $result = [];

        $ev = Evaluador::findFirst(intval($id));
        $correo = $ev->correo;

        $periodo = CPeriodo::findFirst(["activo=true"]);

        if (isset($id) && isset($correo) && $periodo) {
            $msg = $this->plantillas->getPlantilla("correo", ["alumno" => $this->session->get("usuario_nombre"), "url" => $this->token->encrypt($this->token->arrToString(["evaluador" => $correo, "evaluado" => $this->session->get("usuario_id"), "periodo" => $periodo->id]), $this->config->webservice->key)]);

            $result["success"] = $this->enviaCorreo($msg, "Evaluación de Competencias", $correo);
        }
        echo json_encode($result);
    }

    public function getEvaluadorAction() {
        $id = $this->request->getPost('id');
        $alumno = $this->session->get("usuario_id");
        $result = [];

        if (isset($id)) {
            $evaluador = Evaluador::findFirst(["id=?1 and id_alumno=?2", "bind" => [1 => intval($id), 2 => $alumno]]);
            if ($evaluador)
                $result = ["nombre" => $evaluador->nombre, "correo" => $evaluador->correo, "telefono" => $evaluador->telefono, "rol" => $evaluador->id_rol];
        }

        echo json_encode($result);
    }

    public function importaAlumnosAction() {
        $result = [];
        $alumnos = Accesos::find(["conditions" => "activo=true and tipo_usuario='Universitario'"]);
        $total = 0;

        $rol = CRol::findFirst(["nombre='Alumno'"]);
        if ($rol)
            foreach ($alumnos as $alumno) {
                $ant = Usuario::findFirst(["correo=?1", "bind" => [1 => $alumno->usuario]]);
                if (!$ant) {

                    $nuevo = new Usuario();
                    $nuevo->nombre = $alumno->nombre;
                    $nuevo->correo = $alumno->usuario;
                    $nuevo->password = $this->config->password->default_hash;
                    $nuevo->id_rol = $rol->id;
                    if ($nuevo->save())
                        $total++;
                }
            }


        $result["success"] = "Proceso terminado. Se han importado " . $total . " alumnos";

        echo json_encode($result);
    }

    public function setEvaluadorAction() {
        $id = $this->request->getPost('id');

        $alumno = $this->session->get("usuario_id");
        $result = [];

        if (isset($id)) {
            $evaluador = Evaluador::findFirst(["id=?1 and id_alumno=?2", "bind" => [1 => intval($id), 2 => $alumno]]);
            if ($evaluador) {

                $evaluador->activo = !$evaluador->activo;
                if ($evaluador->save())
                    $result["success"] = "Evaluador actualizado correctamente";
                else
                    $result["success"] = "Se ha producido un error al actualizar el Evaluador";
            } else
                $result["error"] = "No se ha encontrado el evaluador seleccionado";
        } else
            $result["error"] = "No se ha enviado la información necesaria";

        echo json_encode($result);
    }

    public function verEvaluacionAction() {
        $id = $this->request->getPost('id');
        $periodo = $this->request->getPost('periodo');

        $id_alumno = $this->request->getPost('id_alumno');

        if (!(isset($id_alumno)))
            $id_alumno = $this->session->get("usuario_id");

        $result = [];

        $ev = Evaluador::findFirst(intval($id));
        $correo = $ev->correo;

        if (isset($id) && isset($correo))
            $result["url"] = $this->token->encrypt($this->token->arrToString(["evaluador" => $correo, "evaluado" => intval($id_alumno), "periodo" => $periodo]), $this->config->webservice->key);
        else
            $result["error"] = "No se ha encontrado la informacion necesaria";
        echo json_encode($result);
    }

    public function restablecePasswordAction() {
        $result = [];
        $id = $this->request->getPost('id');

        if (isset($id)) {
            $usuario = Usuario::findFirst(intval($id));
            if ($usuario) {




                $correo = $usuario->correo;
                //$correo="vantware@gmail.com";

                if (isset($correo)) {
                    $msg = $this->plantillas->getPlantilla("password", ["usuario" => $usuario->nombre, "url" => $this->token->encrypt($usuario->id, $this->config->webservice->key)]);

                    $result["success"] = $this->enviaCorreo($msg, "Restablecer Contraseña", $correo);
                }
            } else
                $result["error"] = "No se ha encontrado el usuario seleccionado";
        } else
            $result["error"] = "No se ha enviado la información necesaria";
        echo json_encode($result);
    }

    public function getPreguntasCAction() {
        $result = array("total" => 0, "page" => 1, "records" => 0, "rows" => array());

        $seccion = $this->request->getPost('seccion');
        $carrera = $this->request->getPost('carrera');

        if (isset($seccion) && isset($carrera) && intval($seccion) > 0 && intval($carrera) > 0) {
            $preguntas = $this->db->query("select cp.* from public.catalogo_pregunta as cp left join public.catalogo_funcionesp as cf on cp.id_funcionesp=cf.id inner join public.catalogo_seccion as cs on cp.id_seccion=cs.id where (cf.id_carrera=" . $carrera . " or cs.global=true) and cp.id_seccion=" . $seccion)->fetchAll();

            foreach ($preguntas as $pregunta)
                $result["rows"][] = $pregunta;



            $result["total"] = $result["records"] = count($preguntas);
        }


        echo json_encode($result);
    }

    public function getReporteAction() {
        $tipo = $this->request->getPost('tipo');
        $result = array("total" => 1, "page" => 1, "records" => 0, "rows" => array());

        switch ($tipo) {

            case "ecorreo":

                $eus = Usuario::find(["conditions" => "error_correo is not null"]);
                $ees = Evaluador::find(["conditions" => "error_correo is not null"]);

                foreach ($eus as $eu)
                    $result["rows"][] = ["rol" => $eu->CRol->nombre, "correo" => $eu->correo, "error" => $eu->error_correo];

                foreach ($ees as $es)
                    $result["rows"][] = ["rol" => $es->RolEvaluador->nombre, "correo" => $es->correo, "error" => $es->error_correo];

                break;

            case "siningreso":

                $eus = Usuario::find(["conditions" => "last_login is null and id_rol=2"]);

                foreach ($eus as $eu)
                    $result["rows"][] = ["nombre" => $eu->nombre, "correo" => $eu->correo];




                break;
        }


        echo json_encode($result);
    }

    public function getReporteGeneralAction() {
        $result = array("total" => 1, "page" => 1, "records" => 0, "rows" => array());
        $per = CPeriodo::findFirst(["activo=true"]);

        $rows = [];

        $valores = $this->db->query("select sum(CAST(dr.valor as integer))/count(*) as media from public.detalle_respuesta as dr inner join respuesta as r on dr.id_respuesta=r.id inner join public.catalogo_pregunta as cp on dr.id_pregunta=cp.id inner join detalle_alumno as da on r.id_alumno=da.id_usuario inner join catalogo_funcionesp as cf on da.id_funcionesp=cf.id where r.id_periodo=" . $per->id . " and dr.valor ~ '^[0-9]+$' and cp.id_seccion=1")->fetchAll();

        $mediat = floatval($valores[0]["media"]);
        if (!isset($mediat) || $mediat < 1)
            $mediat = 1;


        $valores = $this->db->query("select sum(CAST(dr.valor as integer))/count(*) as media from public.detalle_respuesta as dr inner join respuesta as r on dr.id_respuesta=r.id inner join public.catalogo_pregunta as cp on dr.id_pregunta=cp.id inner join detalle_alumno as da on r.id_alumno=da.id_usuario inner join catalogo_funcionesp as cf on da.id_funcionesp=cf.id where r.id_periodo=" . $per->id . " and dr.valor ~ '^[0-9]+$' and cp.id_seccion=2")->fetchAll();

        $mediad = floatval($valores[0]["media"]);
        if (!isset($mediad) || $mediad < 1)
            $mediad = 1;



        $extra = "";

        $filtro = $this->session->get("usuario_tipo");
        $id_usuario = $this->session->get("usuario_id");

        if ($filtro == "Director") {
            $dcs = DetalleDirector::find(["id_director=?1", "bind" => [1 => $id_usuario]]);

            $extra = " and cc.id in (";

            foreach ($dcs as $dc)
                $extra .= $dc->id_carrera.",";

            $extra .= "0)";
        } elseif ($filtro == "Validador") {
            $dcs = DetalleValidador::find(["id_validador=?1", "bind" => [1 => $id_usuario]]);

            $extra = " and cc.id in (";

            foreach ($dcs as $dc)
                $extra .= $dc->id_carrera.",";

            $extra .= "0)";
        }



        $valores = $this->db->query("select u.id, u.nombre,u.correo,da.semestre,cc.nombre as carrera,u.last_login from public.usuario as u inner join detalle_alumno as da on u.id=da.id_usuario inner join catalogo_funcionesp as cf on da.id_funcionesp=cf.id inner join catalogo_carrera as cc on cf.id_carrera=cc.id WHERE u.activo = TRUE " . $extra . " order by semestre,carrera")->fetchAll();
        foreach ($valores as $valor)
            $rows[$valor["id"]] = ["id" => $valor["id"], "nombre" => $valor["nombre"], "semestre" => $valor["semestre"], "carrera" => $valor["carrera"], "correo" => $valor["correo"], "ultimo" => $valor["last_login"], "autoevaluacion" => "Pendiente", "jefes" => "Pendiente", "colegas" => "Pendiente", "jefes2" => 0, "colegas2" => 0, "ejefes" => "No Asignado", "ecolegas" => "No Asignado", "trans" => 0, "dis" => 0, "ptrans" => "Bajo", "pdis" => "bajo"];

        //2 colega 3 jefe
        $evs = $this->db->query("select count(*) as total,id_alumno from evaluador where id_rol=2 and activo=true group by id_alumno")->fetchAll();

        foreach ($evs as $e)
            if (array_key_exists($e["id_alumno"], $rows)) {
                $rows[$e["id_alumno"]]["colegas2"] = $e["total"];
                $rows[$e["id_alumno"]]["colegas"] = "Pendiente";
                $rows[$e["id_alumno"]]["ecolegas"] = "Pendiente";
            }


        $evs = $this->db->query("select count(*) as total,id_alumno from evaluador where id_rol=3 and activo=true group by id_alumno")->fetchAll();
        foreach ($evs as $e)
            if (array_key_exists($e["id_alumno"], $rows)) {
                $rows[$e["id_alumno"]]["jefes2"] = $e["total"];
                $rows[$e["id_alumno"]]["jefes"] = "Pendiente";
                $rows[$e["id_alumno"]]["ejefes"] = "Pendiente";
            }



        //select count(*) as total,e.id_rol,r.id_alumno from respuesta as r inner join evaluador as e on r.correo=e.correo where id_periodo=2 group by e.id_rol,r.id_alumno
        $evs = $this->db->query("select count(*) as total,e.id_rol,r.id_alumno from respuesta as r inner join evaluador as e on r.correo=e.correo where id_periodo=" . $per->id . " group by e.id_rol,r.id_alumno")->fetchAll();
        foreach ($evs as $e)
            if (array_key_exists($e["id_alumno"], $rows)) {
                if ($e["id_rol"] == 2)
                    $rows[$e["id_alumno"]]["ecolegas"] = $e["total"] == $rows[$e["id_alumno"]]["colegas2"] ? "Completado" : "Parcial";
                else if ($e["id_rol"] == 3)
                    $rows[$e["id_alumno"]]["ejefes"] = $e["total"] == $rows[$e["id_alumno"]]["jefes2"] ? "Completado" : "Parcial";
            }



        //1 trans 2 dis
        //select sum(CAST(dr.valor as integer))/count(*) as total, r.id_alumno from detalle_respuesta as dr inner join respuesta as r on dr.id_respuesta=r.id where r.id_periodo=2 and dr.valor ~ '^[0-9]+$' group by r.id_alumno     





        $evs = $this->db->query("select sum(CAST(dr.valor as integer))/count(*) as total, r.id_alumno from detalle_respuesta as dr inner join respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id where r.id_periodo=" . $per->id . " and cp.id_seccion=1 and dr.valor ~ '^[0-9]+$' group by r.id_alumno")->fetchAll();
        foreach ($evs as $e)
            if (array_key_exists($e["id_alumno"], $rows))
                $rows[$e["id_alumno"]]["ptrans"] = (floatval($e["total"]) < $mediat ? "Bajo" : (floatval($e["total"]) == $mediat ? "Media" : "Alto"));




        $evs = $this->db->query("select sum(CAST(dr.valor as integer))/count(*) as total, r.id_alumno from detalle_respuesta as dr inner join respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id inner join evaluador as e on r.correo=e.correo where e.id_rol=2 and r.id_periodo=" . $per->id . " and cp.id_seccion=1 and dr.valor ~ '^[0-9]+$' group by r.id_alumno")->fetchAll();
        foreach ($evs as $e)
            if (array_key_exists($e["id_alumno"], $rows))
                $rows[$e["id_alumno"]]["colegas"] = (floatval($e["total"]) < $mediat ? "Bajo" : (floatval($e["total"]) == $mediat ? "Media" : "Alto"));











        $evs = $this->db->query("select sum(CAST(dr.valor as integer))/count(*) as total, r.id_alumno from detalle_respuesta as dr inner join respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id where r.id_periodo=" . $per->id . " and cp.id_seccion=2 and dr.valor ~ '^[0-9]+$' group by r.id_alumno")->fetchAll();
        foreach ($evs as $e)
            if (array_key_exists($e["id_alumno"], $rows))
                $rows[$e["id_alumno"]]["pdis"] = (floatval($e["total"]) < $mediad ? "Bajo" : (floatval($e["total"]) == $mediad ? "Media" : "Alto"));





        $evs = $this->db->query("select sum(CAST(dr.valor as integer))/count(*) as total, r.id_alumno from detalle_respuesta as dr inner join respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id inner join evaluador as e on r.correo=e.correo where e.id_rol=3 and r.id_periodo=" . $per->id . " and cp.id_seccion=1 and dr.valor ~ '^[0-9]+$' group by r.id_alumno")->fetchAll();
        foreach ($evs as $e)
            if (array_key_exists($e["id_alumno"], $rows))
                $rows[$e["id_alumno"]]["jefes"] = (floatval($e["total"]) < $mediat ? "Bajo" : (floatval($e["total"]) == $mediat ? "Media" : "Alto"));



        $evs = $this->db->query("select sum(CAST(dr.valor as integer))/count(*) as total, r.id_alumno from detalle_respuesta as dr inner join respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id inner join usuario as u on r.correo=u.correo where u.id_rol=2 and r.id_periodo=" . $per->id . " and cp.id_seccion=1 and dr.valor ~ '^[0-9]+$' group by r.id_alumno")->fetchAll();
        foreach ($evs as $e)
            if (array_key_exists($e["id_alumno"], $rows))
                $rows[$e["id_alumno"]]["autoevaluacion"] = (floatval($e["total"]) < $mediat ? "Bajo" : (floatval($e["total"]) == $mediat ? "Media" : "Alto"));









        $result["rows"] = array_values($rows);

        echo json_encode($result);
    }

    //graficas

    public function getGraficaAction() {
        $result = ["series" => [], "labels" => []];
    
        try {
            $tipo = $this->request->getPost('tipo');
            $tipo_usuario = $this->session->get("usuario_tipo");
    
            if (!$tipo_usuario) {
                throw new Exception('User type not found in session');
            }
    
            // Retrieve active period
            $per = CPeriodo::findFirst(["activo=true"]);
            if (!$per) {
                throw new Exception('Active period not found');
            }
    
            if ($tipo_usuario === "Director" || $tipo_usuario === "Validador") {
                $id_director = $this->session->get("usuario_id");
                if (!$id_director) {
                    throw new Exception('Director ID not found in session');
                }
    
                if ($tipo_usuario === "Director") {
                    $carreras = $this->db->query("select id_carrera from detalle_director where id_director = $id_director")->fetchAll();
                    if (!$carreras) {
                        throw new Exception('No carreras found for the director');
                    }
                } elseif ($tipo_usuario === "Validador") {
                    $carreras = $this->db->query("select id_carrera from detalle_validador where id_validador = $id_director")->fetchAll();
                    if (!$carreras) {
                        throw new Exception('No carreras found for the validador');
                    }
                }
               
                
                $carrerasA = array_column($carreras, 'id_carrera');
                $str_carreras = '('.implode(",", $carrerasA).')';
            }
    
            $subtipo = $this->request->getPost('subtipo', 'general'); 
            $valor = $this->request->getPost('valor');
    
            $extra = '';
            if ($valor != 0 && $valor != "") {
                switch ($subtipo) {
                    case "carrera":
                        $extra = " and cf.id_carrera=" . intval($valor);
                        break;
                    case "semestre":
                        $extra = " and da.semestre=" . intval($valor);
                        break;
                    case "especialidad":
                        $extra = " and cf.id=" . intval($valor);
                        break;
                    default:
                        throw new Exception('Invalid subtype');
                }
            }
    
            switch ($tipo) {
                case "semestre":
                    $valores = $this->getSemestreData($tipo_usuario, $extra, $str_carreras);
                    foreach ($valores as $valor) {
                        $result["series"][] = $valor["total"];
                        $result["labels"][] = "Semestre " . $valor["semestre"];
                    }
                    break;
    
                case "aecompletas":
                    $this->handleAecompletas($result, $tipo_usuario, $per, $extra, $str_carreras);
                    break;
    
                case "ejefes":
                    $this->handleEjefes($result, $tipo_usuario, $per, $extra, $str_carreras);
                    break;
    
                case "ecolegas":
                    $this->handleEcolegas($result, $tipo_usuario, $per, $extra, $str_carreras);
                    break;
    
                case "bajosT":
                case "bajosD":
                    $this->handleBajos($result, $tipo_usuario, $per, $tipo, $extra, $str_carreras);
                    break;
    
                default:
                    throw new Exception('Invalid type');
            }
    
            $result["tipo"] = $tipo_usuario;
            echo json_encode($result);
    
        } catch (Exception $e) {
            // Log the error for debugging purposes
            error_log($e->getMessage());
    
            // Return an error message to the client
            $result = [
                "error" => true,
                "message" => "An error occurred: " . $e->getMessage()
            ];
            echo json_encode($result);
        }
    }
    
    private function getSemestreData($tipo_usuario, $extra, $str_carreras) {
        try {
            if ($tipo_usuario === "Administrador") {
                return $this->db->query("SELECT count(*) as total, semestre FROM public.detalle_alumno as da INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp=cf.id WHERE validado=true AND id_usuario IN (SELECT id FROM public.usuario WHERE id_rol = 2 AND activo = TRUE) $extra GROUP BY semestre")->fetchAll();
            } elseif ($tipo_usuario === "Director" || $tipo_usuario === "Validador") {
                $extra .= " and cf.id_carrera IN $str_carreras";
                return $this->db->query("SELECT count(*) as total, semestre FROM public.detalle_alumno as da INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp=cf.id WHERE validado=true AND id_usuario IN (SELECT id FROM public.usuario WHERE id_rol = 2 AND activo = TRUE) $extra GROUP BY semestre")->fetchAll();
            } else {
                throw new Exception('User type not allowed for this query');
            }
        } catch (Exception $e) {
            throw new Exception("Failed to retrieve semestre data: " . $e->getMessage());
        }
    }

    private function handleAecompletas(&$result, $tipo_usuario, $per, $extra, $str_carreras) {
        try {
            $rol = CRol::findFirst(["nombre='Alumno'"]);
            if (!$rol || !$per) {
                throw new Exception('Role or period not found');
            }
    
            $result["labels"][] = "Realizadas";
            $result["labels"][] = "Pendientes";
    
            if ($tipo_usuario === "Administrador") {
                $valores = $this->db->query("SELECT count(*) as total FROM public.usuario as u 
                                            INNER JOIN detalle_alumno as da ON u.id=da.id_usuario 
                                            INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp=cf.id 
                                            WHERE id_rol =" . $rol->id . " AND activo = TRUE $extra AND correo IN 
                                            (SELECT correo FROM public.respuesta WHERE respuesta.id_periodo =" . $per->id . ")")->fetchAll();
    
                $valores_tot = $this->db->query("SELECT count(*) as total FROM public.usuario as u 
                                            INNER JOIN detalle_alumno as da ON u.id=da.id_usuario 
                                            INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp=cf.id 
                                            WHERE id_rol =" . $rol->id . " AND activo = TRUE $extra")->fetchAll();
            } elseif ($tipo_usuario === "Director" || $tipo_usuario === "Validador") {
                $valores = $this->db->query("SELECT count(*) as total FROM public.usuario as u 
                                            INNER JOIN detalle_alumno as da ON u.id=da.id_usuario 
                                            INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp=cf.id 
                                            WHERE cf.id_carrera IN $str_carreras AND id_rol =" . $rol->id . " AND activo = TRUE $extra 
                                            AND correo IN (SELECT correo FROM public.respuesta WHERE respuesta.id_periodo =" . $per->id . ")")->fetchAll();
    
                $valores_tot = $this->db->query("SELECT count(*) as total FROM public.usuario as u 
                                            INNER JOIN detalle_alumno as da ON u.id=da.id_usuario 
                                            INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp=cf.id 
                                            WHERE cf.id_carrera IN $str_carreras AND id_rol =" . $rol->id . " AND activo = TRUE $extra")->fetchAll();
            }
    
            $total = $valores_tot[0]["total"];
            $result["series"][] = intval($valores[0]["total"]);
            $result["series"][] = intval($total - $valores[0]["total"]);
        } catch (Exception $e) {
            throw new Exception("Error processing 'aecompletas': " . $e->getMessage());
        }
    }

    private function handleEjefes(&$result, $tipo_usuario, $per, $extra, $str_carreras) {
        try {
            $rol = RolEvaluador::findFirst(["nombre='Jefe / Superior'"]);
            if (!$rol || !$per) {
                throw new Exception('Role or period not found');
            }
    
            $result["labels"][] = "Realizadas";
            $result["labels"][] = "Pendientes";
    
            if ($tipo_usuario === "Administrador") {
                $valores = $this->db->query("SELECT count(*) as total FROM public.evaluador as e 
                                            INNER JOIN public.usuario as u ON e.id_alumno = u.id 
                                            INNER JOIN detalle_alumno as da ON u.id = da.id_usuario 
                                            INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp = cf.id 
                                            WHERE e.id_rol = $rol->id $extra AND e.activo = TRUE AND u.activo=TRUE 
                                            AND e.correo IN (SELECT correo FROM public.respuesta WHERE id_periodo =" . $per->id . ")")->fetchAll();
    
                $valores_tot = $this->db->query("SELECT count(*) as total FROM public.evaluador as e 
                                            INNER JOIN public.usuario as u ON e.id_alumno = u.id 
                                            INNER JOIN detalle_alumno as da ON u.id = da.id_usuario 
                                            INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp = cf.id 
                                            WHERE e.id_rol = $rol->id $extra AND e.activo = TRUE AND u.activo=TRUE")->fetchAll();
            } elseif ($tipo_usuario === "Director" || $tipo_usuario === "Validador") {
                $valores = $this->db->query("SELECT count(*) as total FROM public.evaluador as e 
                                            INNER JOIN public.usuario as u ON e.id_alumno = u.id 
                                            INNER JOIN detalle_alumno as da ON u.id = da.id_usuario 
                                            INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp = cf.id 
                                            WHERE cf.id_carrera IN $str_carreras AND e.id_rol = $rol->id $extra 
                                            AND e.activo = TRUE AND u.activo=TRUE 
                                            AND e.correo IN (SELECT correo FROM public.respuesta WHERE id_periodo =" . $per->id . ")")->fetchAll();
    
                $valores_tot = $this->db->query("SELECT count(*) as total FROM public.evaluador as e 
                                            INNER JOIN public.usuario as u ON e.id_alumno = u.id 
                                            INNER JOIN detalle_alumno as da ON u.id = da.id_usuario 
                                            INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp = cf.id 
                                            WHERE cf.id_carrera IN $str_carreras AND e.id_rol = $rol->id $extra 
                                            AND e.activo = TRUE AND u.activo=TRUE")->fetchAll();
            }
    
            $result["series"][] = intval($valores[0]["total"]);
            $result["series"][] = intval($valores_tot[0]["total"] - $valores[0]["total"]);
        } catch (Exception $e) {
            throw new Exception("Error processing 'ejefes': " . $e->getMessage());
        }
    }
    
    private function handleEcolegas(&$result, $tipo_usuario, $per, $extra, $str_carreras) {
        try {
            $rol = RolEvaluador::findFirst(["nombre='Colega / Peer'"]);
            if (!$rol || !$per) {
                throw new Exception('Role or period not found');
            }
    
            $result["labels"][] = "Realizadas";
            $result["labels"][] = "Pendientes";
    
            if ($tipo_usuario === "Administrador") {
                $valores = $this->db->query("SELECT count(*) as total FROM public.evaluador AS e 
                                            INNER JOIN public.usuario AS u ON e.id_alumno = u.id 
                                            INNER JOIN detalle_alumno as da ON u.id = da.id_usuario 
                                            INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp = cf.id 
                                            WHERE e.id_rol = $rol->id $extra AND e.activo = TRUE AND u.activo=TRUE 
                                            AND e.correo IN (SELECT correo FROM public.respuesta WHERE id_periodo =" . $per->id . ")")->fetchAll();
    
                $valores_tot = $this->db->query("SELECT count(*) as total FROM public.evaluador AS e 
                                            INNER JOIN public.usuario AS u ON e.id_alumno = u.id 
                                            INNER JOIN detalle_alumno as da ON u.id = da.id_usuario 
                                            INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp = cf.id 
                                            WHERE e.id_rol = $rol->id $extra AND e.activo = TRUE AND u.activo=TRUE")->fetchAll();
            } elseif ($tipo_usuario === "Director" || $tipo_usuario === "Validador") {
                $valores = $this->db->query("SELECT count(*) as total FROM public.evaluador AS e 
                                            INNER JOIN public.usuario AS u ON e.id_alumno = u.id 
                                            INNER JOIN detalle_alumno as da ON u.id = da.id_usuario 
                                            INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp = cf.id 
                                            WHERE cf.id_carrera IN $str_carreras AND e.id_rol = $rol->id $extra 
                                            AND e.activo = TRUE AND u.activo=TRUE 
                                            AND e.correo IN (SELECT correo FROM public.respuesta WHERE id_periodo =" . $per->id . ")")->fetchAll();
    
                $valores_tot = $this->db->query("SELECT count(*) as total FROM public.evaluador AS e 
                                            INNER JOIN public.usuario AS u ON e.id_alumno = u.id 
                                            INNER JOIN detalle_alumno as da ON u.id = da.id_usuario 
                                            INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp = cf.id 
                                            WHERE cf.id_carrera IN $str_carreras AND e.id_rol = $rol->id $extra 
                                            AND e.activo = TRUE AND u.activo=TRUE")->fetchAll();
            }
    
            $result["series"][] = intval($valores[0]["total"]);
            $result["series"][] = intval($valores_tot[0]["total"] - $valores[0]["total"]);
        } catch (Exception $e) {
            throw new Exception("Error processing 'ecolegas': " . $e->getMessage());
        }
    }

    private function handleBajos(&$result, $tipo_usuario, $per, $tipo, $extra, $str_carreras) {
        try {
            $secc = ($tipo === "bajosD") ? 2 : 1;
            if ($tipo_usuario === "Director") {
                $extra .= " and cf.id_carrera IN $str_carreras";
            }
    
            $valores = $this->db->query("SELECT sum(CAST(dr.valor as integer))/count(*) as media 
                                        FROM public.detalle_respuesta as dr 
                                        INNER JOIN respuesta as r ON dr.id_respuesta=r.id 
                                        INNER JOIN public.catalogo_pregunta as cp ON dr.id_pregunta=cp.id 
                                        INNER JOIN detalle_alumno as da ON r.id_alumno=da.id_usuario 
                                        INNER JOIN usuario as u ON da.id_usuario=u.id 
                                        INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp=cf.id 
                                        WHERE u.activo=true AND r.id_periodo=" . $per->id . " 
                                        AND dr.valor ~ '^[0-9]+$' AND cp.id_seccion=" . $secc . $extra)->fetchAll();
    
            $media = intval($valores[0]["media"]);
            $media = max($media, 1); // Default to 1 if media is less than 1
    
            $result["labels"] = ["Menor a $media", "Media ($media)", "Mayor a $media"];
    
            $qt1 = $this->db->query("SELECT DISTINCT(da.id_usuario) FROM public.detalle_respuesta as dr 
                                    INNER JOIN public.respuesta as r ON dr.id_respuesta=r.id 
                                    INNER JOIN public.catalogo_pregunta as cp ON dr.id_pregunta=cp.id 
                                    INNER JOIN detalle_alumno as da ON r.id_alumno=da.id_usuario 
                                    INNER JOIN usuario as u ON da.id_usuario=u.id 
                                    INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp=cf.id 
                                    WHERE u.activo=true AND r.id_periodo=" . $per->id . " 
                                    AND dr.valor ~ '^[0-9]+$' AND cp.id_seccion=" . $secc . $extra . 
                                    " GROUP BY da.id_usuario HAVING sum(CAST(dr.valor as integer))/count(*)>" . $media)->fetchAll();
    
            $qt2 = $this->db->query("SELECT DISTINCT(da.id_usuario) FROM public.detalle_respuesta as dr 
                                    INNER JOIN public.respuesta as r ON dr.id_respuesta=r.id 
                                    INNER JOIN public.catalogo_pregunta as cp ON dr.id_pregunta=cp.id 
                                    INNER JOIN detalle_alumno as da ON r.id_alumno=da.id_usuario 
                                    INNER JOIN usuario as u ON da.id_usuario=u.id 
                                    INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp=cf.id 
                                    WHERE u.activo=true AND r.id_periodo=" . $per->id . " 
                                    AND dr.valor ~ '^[0-9]+$' AND cp.id_seccion=" . $secc . $extra . 
                                    " GROUP BY da.id_usuario HAVING sum(CAST(dr.valor as integer))/count(*)=" . $media)->fetchAll();
    
            $qt3 = $this->db->query("SELECT DISTINCT(da.id_usuario) FROM public.detalle_respuesta as dr 
                                    INNER JOIN public.respuesta as r ON dr.id_respuesta=r.id 
                                    INNER JOIN public.catalogo_pregunta as cp ON dr.id_pregunta=cp.id 
                                    INNER JOIN detalle_alumno as da ON r.id_alumno=da.id_usuario 
                                    INNER JOIN usuario as u ON da.id_usuario=u.id 
                                    INNER JOIN catalogo_funcionesp as cf ON da.id_funcionesp=cf.id 
                                    WHERE u.activo=true AND r.id_periodo=" . $per->id . " 
                                    AND dr.valor ~ '^[0-9]+$' AND cp.id_seccion=" . $secc . $extra . 
                                    " GROUP BY da.id_usuario HAVING sum(CAST(dr.valor as integer))/count(*)<" . $media)->fetchAll();
    
            $result["series"][] = intval(count($qt1));
            $result["series"][] = intval(count($qt2));
            $result["series"][] = intval(count($qt3));
        } catch (Exception $e) {
            throw new Exception("Error processing 'bajos': " . $e->getMessage());
        }
    }
    
    // fin graficas

    public function getRespuestasAction() {
        $result = [];
        $id = $this->request->getPost('id');

        if (isset($id) && intval($id) > 0) {

            $detalleR = DetalleRespuesta::find(["conditions" => "id_respuesta=?1", "bind" => [1 => $id]]);
            foreach ($detalleR as $dr)
                $result[$dr->id_pregunta] = $dr->valor;
        }


        echo json_encode($result);
    }

    public function validaEvaluadorAction() {
        $id = $this->request->getPost('id');

        $result = [];

        if (isset($id)) {

            $da = Evaluador::findFirst(intval($id));
            if ($da && !$da->validado) {





                $da->validado = true;

                if ($da->save())
                    $result["success"] = "Datos guardados correctamente";
                else
                    $result["error"] = "Se ha producido un error al guardar los datos";
            } else
                $result["error"] = "No se ha encontrado la información solicitada";
        } else
            $result["error"] = "No se ha recibido la informacion necesaria";


        echo json_encode($result);
    }

    public function setPasswordAction() {
        $token = $this->request->getPost('token');
        $correo = $this->request->getPost('usuario');
        $password = $this->request->getPost('password');

        $result = [];

        if ((isset($token) && isset($correo) && isset($password))) {




            $usuario_id = $this->token->decrypt($token, $this->config->webservice->key);

            if ($usuario_id) {
                $usuario = Usuario::findFirst(intval($usuario_id));

                if ($usuario) {

                    if ($usuario->correo == trim($correo)) {
                        $pass = sha1($password . $this->config->password->sal);
                        $usuario->password = $pass;

                        if ($usuario->save())
                            $result["success"] = "Contraseña actualizada correctamente";
                        else
                            $result["error"] = "Se ha producido un error al cambiar la contraseña";
                    } else
                        $result["error"] = "El correo ingresado no corresponde al usuario";
                } else
                    $result["error"] = "No se ha encontrado al usuario";
            } else
                $result["error"] = "Usuario no encontrado" . $usuario_id;
        } else
            $result["error"] = "No se ha enviado la información necesaria";


        echo json_encode($result);
    }

    public function setSeleccionAction() {
        $id = $this->request->getPost('id');
        $valor = $this->request->getPost('valor');
        $result = [];

        if (isset($id) && isset($valor)) {

            $da = DetalleAlumno::findFirst(intval($id));
            if ($da && !$da->validado) {

                $da->validado = $valor;
                $da->pendiente = false;

                if ($da->save())
                    $result["success"] = "Datos guardados correctamente";
                else
                    $result["error"] = "Se ha producido un error al guardar los datos";
            } else
                $result["error"] = "No se ha encontrado la información solicitada";
        } else
            $result["error"] = "No se ha recibido la informacion necesaria";


        echo json_encode($result);
    }

    public function getAlumnosPendientesAction() {
        $result = array("total" => 0, "page" => 1, "records" => 0, "rows" => array());

        $id_director = $this->session->get("usuario_id");

        $pendientes = $this->db->query("select cf.id_carrera,da.* from public.detalle_alumno as da inner join public.catalogo_funcionesp as cf on da.id_funcionesp=cf.id where da.validado=false and pendiente=true and cf.id_carrera in (select id_carrera from public.detalle_director where id_director=" . $id_director . ")")->fetchAll();
        foreach ($pendientes as $pendiente)
            $result["rows"][] = ["id" => $pendiente["id"], "id_carrera" => $pendiente["id_carrera"], "id_funcionesp" => $pendiente["id_funcionesp"], "semestre" => $pendiente["semestre"], "id_usuario" => $pendiente["id_usuario"]];


        $result["total"] = $result["records"] = count($pendientes);

        echo json_encode($result);
    }

    public function getEvaluadoresPendientesAction() {
        $result = array("total" => 0, "page" => 1, "records" => 0, "rows" => array());

        $pendientes = Evaluador::find(["validado=false"]);

        foreach ($pendientes as $pendiente) {

            $da = DetalleAlumno::findFirst(["id_usuario=?1", "bind" => [1 => $pendiente->id_alumno]]);

            $result["rows"][] = ["id" => $pendiente->id, "alumno" => $pendiente->Usuario->nombre, "semestre" => $da->semestre, "carrera" => $da->CFuncionesp->id_carrera, "evaluador" => $pendiente->nombre, "correo" => $pendiente->correo, "telefono" => $pendiente->telefono, "id_rol" => $pendiente->id_rol];
        }


        $result["total"] = $result["records"] = count($pendientes);

        echo json_encode($result);
    }

    function getCarrerasAction() {
        $result = [];
        $id_usuario = $this->session->get("usuario_id");

        $filtro = $this->session->get("usuario_tipo");

        if ($filtro == "Administrador") {
            $dcs = CCarrera::find();
            foreach ($dcs as $dc)
                $result[$dc->id] = $dc->nombre;
        } else {
            if ($filtro == "Director")
                $dcs = DetalleDirector::find(["id_director=?1", "bind" => [1 => $id_usuario]]);
            else if ($filtro == "Validador")
                $dcs = DetalleValidador::find(["id_validador=?1", "bind" => [1 => $id_usuario]]);


            foreach ($dcs as $dc)
                $result[$dc->id_carrera] = $dc->CCarrera->nombre;
        }

        echo json_encode($result);
    }

    function getCarreras2Action() {
        $result = [];
        $id_director = $this->session->get("usuario_id");

        $dcs = DetalleValidador::find(["id_validador=?1", "bind" => [1 => $id_director]]);

        foreach ($dcs as $dc)
            $result[$dc->id_carrera] = $dc->CCarrera->nombre;

        echo json_encode($result);
    }

    function getFuncionesPAction() {
        $result = [];
        $id_director = $this->session->get("usuario_id");

        $filtro = $this->session->get("usuario_tipo");

        if ($filtro == "Administrador") {
            $funciones = CFuncionesp::find();
            foreach ($funciones as $f)
                $result[$f->id] = $f->nombre;
        } if ($filtro == "Director") {
            $dcs = DetalleDirector::find(["id_director=?1", "bind" => [1 => $id_director]]);

            foreach ($dcs as $dc) {
                $funciones = CFuncionesp::find(["id_carrera=?1", "bind" => [1 => $dc->id_carrera]]);
                foreach ($funciones as $f)
                    $result[$f->id] = $f->nombre;
            }
        }

        echo json_encode($result);
    }

    public function getPEstadisticasAction() {
        $id_carrera = (int) $this->request->getPost('carrera');
        $result = [];
        $carrera = CCarrera::findFirst(intval($id_carrera));

        $secciones = CSeccion::find();

        foreach ($secciones as $seccion) {
            $result[$seccion->id] = 0;

            if ($seccion->global)
                $result[$seccion->id] = CPregunta::count(["id_seccion=?1", "bind" => [1 => $seccion->id]]);
            else {

                $preguntas = $this->db->query("select count(*) as total from public.catalogo_pregunta as cp inner join public.catalogo_funcionesp as cf on cp.id_funcionesp=cf.id where cp.id_seccion=" . $seccion->id . " and cf.id_carrera=" . $id_carrera)->fetchAll();

                foreach ($preguntas as $p)
                    $result[$seccion->id] = $p["total"];
            }
        }


        echo json_encode($result);
    }

    public function getAlumnosAction() {
        $result = array("total" => 1, "page" => 1, "records" => 0, "rows" => array());
        $id_director = $this->session->get("usuario_id");
        $mis_carreras = DetalleDirector::find(["conditions" => "id_director=?1", "bind" => [1 => $id_director]]);

        $semestre = $this->request->getPost('semestre');
        $alumno = $this->request->getPost('alumno');
        $carrera = $this->request->getPost('carrera');

        $filtro = "da.id>0";

        if (isset($alumno) && $alumno != "") {
            $filtro .= " and a.nombre ilike '%" . trim($alumno) . "%'";
        }

        if (isset($semestre) && intval($semestre) > 0) {
            $filtro .= " and da.semestre=" . intval($semestre);
        }




        $car = [];
        if (isset($carrera) && intval($carrera) > 0) {
            $car[] = intval($carrera);
        } else
            foreach ($mis_carreras as $mc)
                $car[] = $mc->id_carrera;

                $mis_alumnos = $this->db->query("
                    SELECT a.nombre, da.*, cf.id_carrera
                    FROM detalle_alumno AS da
                    INNER JOIN catalogo_funcionesp AS cf ON da.id_funcionesp = cf.id
                    INNER JOIN usuario AS a ON da.id_usuario = a.id
                    WHERE a.activo = 't'
                    AND " . $filtro . "
                    AND da.id_funcionesp IN (
                        SELECT id
                        FROM catalogo_funcionesp
                        WHERE validado = true 
                        AND id_carrera IN (" . implode(",", $car) . ")
                    )
                ")->fetchAll();


        foreach ($mis_alumnos as $alumno)
            $result["rows"][] = ["id" => $alumno["id_usuario"], "id_carrera" => $alumno["id_carrera"], "id_funcionesp" => $alumno["id_funcionesp"], "semestre" => $alumno["semestre"], "alumno" => $alumno["nombre"]];


        $result["records"] = count($mis_alumnos);

        echo json_encode($result);
    }

    public function getEvaluacionesAction() {
        $result = array("total" => 0, "page" => 1, "records" => 0, "rows" => array());
        $id_alumno = $this->request->getPost('alumno');

        if (!isset($id_alumno))
            $id_alumno = $this->session->get("usuario_id");

        //select r.id_periodo,cp.id_seccion,sum(CAST(SUBSTRING(dr.valor, '([\d]{1,9})') AS integer)) as puntos,count(*) from public.detalle_respuesta as dr inner join public.respuesta as r on dr.id_respuesta=r.id inner join public.catalogo_pregunta as cp on dr.id_pregunta=cp.id group by r.id_periodo,cp.id_seccion 
        $evaluaciones = $this->db->query("select r.id_periodo as id_periodo,cp.id_seccion as id_seccion,sum(CAST(SUBSTRING(dr.valor, '([\d]{1,9})') AS integer)) as puntos,count(*) as total from public.detalle_respuesta as dr inner join public.respuesta as r on dr.id_respuesta=r.id inner join public.catalogo_pregunta as cp on dr.id_pregunta=cp.id where id_alumno=" . intval($id_alumno) . " group by r.id_periodo,cp.id_seccion")->fetchAll();

        foreach ($evaluaciones as $e)
            $result["rows"][] = ["id_periodo" => $e["id_periodo"], "id_seccion" => $e["id_seccion"], "promedio" => round($e["puntos"] / (intval($e["total"]) == 0 ? 1 : intval($e["total"])), 2)];


        $result["total"] = $result["records"] = count($evaluaciones);

        echo json_encode($result);
    }

    public function getAlumnos2Action() {
        $result = array("total" => 0, "page" => 1, "records" => 0, "rows" => array());
        $id_validador = $this->session->get("usuario_id");
        $mis_carreras = DetalleValidador::find(["conditions" => "id_validador=?1", "bind" => [1 => $id_validador]]);

        $semestre = $this->request->getPost('semestre');
        $alumno = $this->request->getPost('alumno');
        $carrera = $this->request->getPost('carrera');

        $filtro = "da.id>0";

        if (isset($alumno) && $alumno != "") {
            $filtro .= " and a.nombre ilike '%" . trim($alumno) . "%'";
        }

        if (isset($semestre) && intval($semestre) > 0) {
            $filtro .= " and da.semestre=" . intval($semestre);
        }


        if (isset($carrera) && intval($carrera) > 0) {
            $filtro .= " and cf.id_carrera=" . intval($carrera);
        }


        $car = [0];
        if (isset($carrera) && intval($carrera) > 0) {
            $car[] = intval($carrera);
        } else
            foreach ($mis_carreras as $mc)
                $car[] = $mc->id_carrera;

                

                $mis_alumnos = $this->db->query("
                    SELECT a.nombre, da.*, cf.id_carrera
                    FROM detalle_alumno AS da
                    INNER JOIN catalogo_funcionesp AS cf ON da.id_funcionesp = cf.id
                    INNER JOIN usuario AS a ON da.id_usuario = a.id
                    WHERE a.activo = 't'
                    AND " . $filtro . "
                    AND da.id_funcionesp IN (
                        SELECT id
                        FROM catalogo_funcionesp
                        WHERE validado = true 
                        AND id_carrera IN (" . implode(",", $car) . ")
                    )
                ")->fetchAll();


        foreach ($mis_alumnos as $alumno)
            $result["rows"][] = ["id" => $alumno["id_usuario"], "id_carrera" => $alumno["id_carrera"], "id_funcionesp" => $alumno["id_funcionesp"], "semestre" => $alumno["semestre"], "alumno" => $alumno["nombre"]];


        $result["records"] = count($mis_alumnos);
        $result["total"] = 1;

        echo json_encode($result);
    }

    public function getEvaluadoresAction() {
        $result = array("total" => 0, "page" => 1, "records" => 0, "rows" => array());

        $id_alumno = $this->request->getPost('alumno');
        $id_per = $this->request->getPost('periodo');

        if (!isset($id_alumno))
            $id_alumno = $this->session->get("usuario_id");


        if (isset($id_per))
            $periodo = CPeriodo::findFirst(intval($id_per));

        if (!$periodo)
            $periodo = CPeriodo::findFirst(["activo=true"]);


        $evaluadores = Evaluador::find(["id_alumno=?1 AND activo='t'", "bind" => [1 => $id_alumno]]);
        foreach ($evaluadores as $evaluador) {
            $evaluacion = Respuesta::findFirst(["correo=?1 and id_periodo=?2", "bind" => [1 => $evaluador->correo, 2 => $periodo->id]]);

            $result["rows"][] = ["id" => $evaluador->id, "nombre" => $evaluador->nombre, "correo" => $evaluador->correo, "telefono" => $evaluador->telefono, "id_rol" => $evaluador->id_rol, "activo" => $evaluador->activo, "validado" => $evaluador->validado, "evaluacion" => $evaluacion ? true : false];
        }


        $result["total"] = $result["records"] = $evaluadores->count();

        echo json_encode($result);
    }

    private function enviaCorreo($msg, $subject, $destinatario, $archivos = NULL) {
        $enviado = false;
        ob_start();
        $resp = $this->correo->envia($msg, $subject, $destinatario, $archivos);

        $enviado = $resp === "Ok";
        ob_end_clean();
        return $enviado;
    }

    private function getDatosGrid($modelo, $filtro = []) {
        $result = array("total" => 0, "page" => 1, "records" => 0, "rows" => array());
        $limit = (int) $this->request->getPost('rows');
        $page = (int) $this->request->getPost('page');
        $order = $this->request->getPost('sidx');
        $dir = $this->request->getPost('sord');

        $order_real = $this->request->getPost('order');

        $o = explode(",", $order);

        if (count($o) > 1) {
            unset($o[0]);
            $order = join(",", $o);
        }
        if (isset($order_real)) {
            $order = $order_real;
            $dir = "";
        }

        $params = array("limit" => $limit, "offset" => ($limit * ($page - 1)), "order" => $order . " " . $dir);
        $search = $this->request->getPost('_search');
        if ($search && $search === "true") {
            $oper = $this->request->getPost('searchOper');
            $campo = $this->request->getPost('searchField');
            $cadena = $this->request->getPost('searchString');
            $like = "";
            switch ($oper) {
                case "eq":
                    $like = "'$cadena'";
                    break;
                case "bw":
                    $like = "'%$cadena'";
                    break;

                case "cn":
                    $like = "'%$cadena%'";
                    break;

                case "ew":
                    $like = "'$cadena%'";
                    break;
            }

            $relaciones = $this->mapaModelos->getRelaciones($modelo);
            $isrel = false;

            foreach ($relaciones as $relacion) {
                $campos = $relacion->getFields();
                if (is_array($campos)) {
                    
                } else {

                    if ($campos == $campo) {

                        $tmp = $this->mapaModelos->getModelo($relacion->getReferencedModel())->findFirst(["conditions" => "nombre like " . $like]);

                        if ($tmp)
                            $like = $campo . "=" . $tmp->id;
                        else
                            $like = "id=-1";


                        $isrel = true;
                        break;
                    }
                }
            }
            if (!$isrel)
                $like = $campo . " like " . $like;



            if (isset($filtro[0]))
                $filtro[0] .= " and " . $like;
            else
                $filtro = array_merge($filtro, [$like]);
        }

        if (isset($modelo)) {
            $params = array_merge($params, $filtro);

            $count = $modelo::find($filtro)->count();

            $datos = $this->mapaModelos->getValores($modelo, $params);
            $result["total"] = max(ceil($count / $limit), 1);
            $result["records"] = $count;
            $result["page"] = $page;

            foreach ($datos as $data)
                array_push($result["rows"], $data);
        }

        return json_encode($result);
    }

    public function iniciaEnviosAction() {
        $shm_id = $this->getMemoryId(881111);
        shmop_write($shm_id, "1", 0);
        shmop_close($shm_id);
        $pid = exec('nohup /usr/bin/php -f /var/www/html/competencias/cronjobs/mensajes.php </dev/null >/tmp/salida 2>&1 & echo $!;');

        $fp = fopen("serial.pid", "w");
        fputs($fp, $pid);
        fclose($fp);

        echo json_encode(["success" => "Iniciando el proceso de envios. PID " . $pid]);
    }

    public function iniciaEnvios2Action() {
        $shm_id = $this->getMemoryId(991111);
        shmop_write($shm_id, "1", 0);
        shmop_close($shm_id);
        $pid = exec('nohup /usr/bin/php -f /var/www/html/competencias/cronjobs/invitaciones.php </dev/null >/tmp/salida 2>&1 & echo $!;');

        $fp = fopen("serial2.pid", "w");
        fputs($fp, $pid);
        fclose($fp);

        echo json_encode(["success" => "Iniciando el proceso de envios. PID " . $pid]);
    }

    public function finEnviosAction() {

        $shm_id = $this->getMemoryId(881111);
        shmop_write($shm_id, "0", 0);
        shmop_close($shm_id);
        unlink("serial.pid");

        echo json_encode(["success" => "Proceso finalizado correctamente"]);
    }

    public function finEnvios2Action() {

        $shm_id = $this->getMemoryId(991111);
        shmop_write($shm_id, "0", 0);
        shmop_close($shm_id);
        unlink("serial2.pid");
        echo json_encode(["success" => "Proceso finalizado correctamente"]);
    }

    private function getMemoryId($id = 881127) {
        $x = shmop_open($id, "c", 0644, 5) or die("Imposible leer la de memoria");
        return $x;
    }

    public function setDatosGridAction() {

        $valores = $this->request->getPost();

        if (isset($valores["campo"]) && isset($valores["valor"]))
            $valores[$valores["campo"]] = $valores["valor"];

        /* pos no se como hacer para que invierta en otros casos asi que lo hara siempre */

        foreach ($valores as $k => $v) {
            if (preg_match('/^([0-9]{2})\-([0-9]{2})\-([0-9]{4})/', $v))
                $valores[$k] = $this->invierteFecha($v);
        }
        echo json_encode([$this->gestionaGrid($valores["modelo"], $valores)]);
    }

    public function getValueSelectGridAction() {
        $result = array();
        $modelo = $this->request->getPost('modelo');
        if (isset($modelo)) {
            $datos = $this->getDatos($modelo);
            foreach ($datos as $dato)
                $result[$dato["id"]] = $dato["nombre"];
        }
        echo json_encode($result);
    }

    public function getCatalogDataAction($modelo = null, $filtro = null, $cfiltro = null) {
        $result = array();
        $campo = $this->request->getPost('campo');
        $valor = $this->request->getPost('valor');
        // ver los datos le los parametros

        if (isset($modelo)) {



            if (isset($filtro)) {
                if (isset($cfiltro))
                    $filtro = $cfiltro . "=" . $filtro;
                else
                    $filtro = "nombre like '%" . $filtro . "%'";
            } else if (isset($campo) && isset($valor))
            {
                $filtro = $campo . "=" . $valor;

            } 
            
            if ($modelo = "CFuncionesp") {
                $filtro = "";
            }

            $rows = $this->mapaModelos->getValores($modelo, $filtro);

            foreach ($rows as $row)
                $result[$row['id']] = $row['nombre'];
        }
        echo json_encode($result);
    }

    public function guardaEvaluacionAction() {
        $result = [];
        $valores = $campo = $this->request->getPost();
        $token = $campo = $this->request->getPost("token");
        unset($valores["token"]);

        $evaluador = $this->session->get("usuario");

        $evaluado = $this->session->get("usuario_id");

        $periodo = CPeriodo::findFirst(["activo=true"]);

        $bande = false;

        if (isset($token)) {
            $arreglo = $this->token->stringToArr($this->token->decrypt($token, $this->config->webservice->key));
            if (is_array($arreglo)) {
                $evaluador = $arreglo["evaluador"];
                $evaluado = $arreglo["evaluado"];
                $bande = true;
            }
        }


        if (isset($evaluado) && isset($evaluador)) {
            $ant = Respuesta::findFirst(["conditions" => "id_periodo=?1 and id_alumno=?2 and correo=?3", "bind" => [1 => $periodo->id, 2 => $evaluado, 3 => $evaluador]]);
            if ($ant)
                $result["warning"] = "Ya existe una evaluación realizada el dia " . $ant->fecha;
            else {

                $respuesta = new Respuesta();
                $respuesta->id_alumno = $evaluado;
                $respuesta->id_periodo = $periodo->id;
                $respuesta->correo = $evaluador;
                $respuesta->fecha = date('Y-m-d');
                $ok = true;

                if ($bande) {
                    $ev = Evaluador::findFirst(["id_alumno=?1 and correo=?2 and evaluacion=false and activo=true", "bind" => [1 => $evaluado, 2 => $evaluador]]);

                    if ($ev) {
                        $ev->evaluacion = true;
                        $bande = $ev->save();
                    }
                } else
                    $bande = true;






                if ($bande && $respuesta->save()) {
                    foreach ($valores as $k => $v) {
                        $dr = new DetalleRespuesta();
                        $dr->valor = $v;
                        $dr->id_respuesta = $respuesta->id;
                        $dr->id_pregunta = intval(substr($k, 9));
                        $ok &= $dr->save();
                    }





                    if ($ok)
                        $result["success"] = "Respuestas guardadas exitosamente";
                    else
                        $result["error"] = "Se han producido algunos errores al guardar las respuestas";
                } else
                    $result["error"] = "Se ha producido un error al guardar las respuestas";
            }
        } else
            $result["error"] = "No se ha enviado la información necesaria";











        echo json_encode($result);
    }

    private function getDatos($modelo, $campo = null, $valor = null) {
        $filtro = "";
        if (isset($campo) && isset($valor))
            $filtro = array($campo . "=:" . $campo . ":", "bind" => array($campo => $valor));

        return $this->mapaModelos->getValores($modelo, $filtro);
    }

    private function gestionaGrid($modelo, $valores) {
        $result = "Error al guardar el registro.";
        if (isset($modelo) && isset($valores)) {


            switch ($valores["oper"]) {
                case "add":
                    unset($valores["oper"]);
                    unset($valores["id"]);
                    $valores["create_at"] = date('Y-m-d H:m:s');
                    $valores["create_by"] = $this->session->get("user");
                    $result = $this->mapaModelos->saveCatalogo($modelo, $valores);
                    break;

                case "edit":
                    unset($valores["oper"]);
                    $valores["update_by"] = $this->session->get("user");
                    $result = $this->mapaModelos->updateCatalogo($modelo, $valores);
                    break;

                case "del":
                    $result = $this->mapaModelos->deleteCatalogo($modelo, $valores["id"]);
                    break;
            }
        } else {
            $result .= "No se ha enviado la información necesaria para realizar la operación";
        }
        return $result;
    }

    public function getAllDatosAction($modelo = null) {
        $result = array();
        $campo = $this->request->getPost('campo');
        $valor = $this->request->getPost('valor');
        $filtro = "";
        if (isset($modelo)) {
            if (isset($campo) && isset($valor))
                $filtro .= $campo . "='" . $valor . "'";
            $rows = $modelo::find($filtro);
            foreach ($rows as $row)
                array_push($result, get_object_vars($row));
        }
        echo json_encode($result);
    }

    public function getAllCatalogosAction() {
        $result = array();
        $sql = "SELECT substring(table_name,10) as nombre FROM information_schema.tables where table_schema='public' and table_name like 'catalogo_%'";

        $catalogos = $this->db->query($sql)->fetchAll();
        if (isset($catalogos)) {


            foreach ($catalogos as $catalogo)
                $result[$catalogo['nombre']] = $catalogo['nombre'];
        }
        echo json_encode($result);
    }

    public function getCatalogoAction($catalogo = null) {
        $result = array("total" => 0, "page" => 1, "records" => 0, "rows" => array());
        $limit = (int) $this->request->getPost('rows');
        $page = (int) $this->request->getPost('page');
        $order = $this->request->getPost('sidx');
        $dir = $this->request->getPost('sord');
        $o = explode(",", $order);
        if (count($o) > 1) {
            unset($o[0]);
            $order = join(",", $o);
        }

        if (isset($catalogo)) {
            $modelo = $this->mapaModelos->getCatalogoName($catalogo);
            $count = $modelo::find()->count();
            $datos = $this->mapaModelos->getValores($modelo, array("limit" => $limit, "offset" => ($limit * ($page - 1)), "order" => $order . " " . $dir));
            $result["total"] = max(ceil($count / $limit), 1);
            $result["records"] = $count;
            $result["page"] = $page;

            foreach ($datos as $data)
                array_push($result["rows"], $data);
        }

        echo json_encode($result);
    }

    public function gestionaCatalogoAction($catalogo = null, $isCatalogo = 1) {
        $result = "Error al guardar el registro.";
        if (isset($catalogo)) {
            $valores = $this->request->getPost();
            $catalogo = $isCatalogo === 1 ? $this->mapaModelos->getCatalogoName($catalogo) : $catalogo;
            $result = $this->gestionaGrid($catalogo, $valores);
        } else {
            $result .= " No se ha especificado un catálogo";
        }
        echo $result;
    }

    public function guardaDAlumnoAction() {
        $nombre = $this->request->getPost('nombre');
        $funcionp = $this->request->getPost('funcionp');
        $carrera = $this->request->getPost('carrera');
        $semestre = $this->request->getPost('semestre');

        $result = [];

        if (!isset($nombre) || trim($nombre) == "")
            $result["error"] = "El nombre no puede estar vacio";
        else if (!isset($funcionp) || intval($funcionp) == 0)
            $result["error"] = "La función profesional no puede estar vacia";
        else if (!isset($semestre) || intval($semestre) == 0)
            $result["error"] = "El SEMESTRE no puede estar vacio";
        else {

            $id = intval($this->session->get("usuario_id"));

            $usuario = Usuario::findFirst($id);
            $detalle = DetalleAlumno::findFirst(["conditions" => "id_usuario=?1", "bind" => [1 => $id]]);

            if ($usuario) {
                $usuario->nombre = $nombre;
                if (!$usuario->save())
                    $result["error"] = "Error al guardar el nombre de usuario";

                if (!$detalle) {
                    $detalle = new DetalleAlumno();
                    $detalle->id_usuario = $id;
                }

                $detalle->id_funcionesp = $funcionp;
                $detalle->semestre = $semestre;
                $detalle->validado = true;

                if ($detalle->save())
                    $result["success"] = "Datos guardados correctamente";
                else
                    $result["error"] = "Ha ocurrido un error al guardar los datos del alumno";
            } else
                $result["error"] = "No se ha encontrado al usuario";
        }



        echo json_encode($result);
    }

    public function getPuntosPerAction() {

        $result = ["labels" => [], "dataset" => []];

        $id_alumno = $this->request->getPost('id_alumno');

        if (!isset($id_alumno))
            $id_alumno = $this->session->get("usuario_id");



        if ($id_alumno == "")
            $id_alumno = 0;

        $secciones = CSeccion::find(["graficable=true", "order" => "id"]);
        foreach ($secciones as $secc)
            $result["labels"][] = $secc->nombre;


        $periodos = CPeriodo::find();
        foreach ($periodos as $periodo) {
            $data = [];

            $puntos = $this->db->query("select cp.id_seccion,sum(CAST(SUBSTRING(dr.valor, '([\d]{1,9})') AS integer)) as puntos,count(r.*) as evaluadores from public.detalle_respuesta as dr inner join public.respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id where r.id_periodo=" . $periodo->id . " and r.id_alumno=" . $id_alumno . " group by cp.id_seccion order by cp.id_seccion")->fetchAll();
            foreach ($puntos as $p)
                $data[] = round(intval($p["puntos"]) / intval($p["evaluadores"]), 2);




            $result["dataset"][] = [
                "label" => $periodo->nombre,
                "backgroundColor" => sprintf("#%06x", rand(0, 16777215)),
                "data" => $data
            ];
        }


        return json_encode($result);
    }

    public function getCategoriasTAction() {


        $id_per = $this->request->getPost('periodo');

        $id_alumno = $this->request->getPost('id_alumno');

        if (!isset($id_alumno))
            $id_alumno = $this->session->get("usuario_id");


        $cats = CCategoria::find(["conditions" => "id_seccion=1", "order" => "id"]);
        $result = ["categorias" => [], "mia" => [], "jefe" => [], "peer" => []];

        $da = DetalleAlumno::findFirst(["conditions" => "id_usuario=?1 and validado=true", "bind" => [1 => $id_alumno]]);

        //select count(*) as total, id_categoria as idc from catalogo_pregunta where id_seccion=1 group by id_categoria



        if (isset($id_per))
            $periodo = CPeriodo::findFirst(intval($id_per));




        if (!$periodo)
            $periodo = CPeriodo::findFirst(["activo=true"]);








        $qtot = $this->db->query("select count(*) as total, id_categoria as idc from public.catalogo_pregunta where id_seccion=1 and id_tipopregunta!=3 group by id_categoria order by id_categoria")->fetchAll();

        $totales = [];
        foreach ($qtot as $q)
            $totales[intval($q["idc"])] = $q["total"];




        foreach ($cats as $cat) {

            $idc = intval($cat->id);

            $result["categorias"][$idc] = $cat->nombre;
            $result["mia"][$idc] = 0;
            $result["jefe"][$idc] = 0;
            $result["peer"][$idc] = 0;

            if ($periodo && $da) {
                //select sum(CAST(SUBSTRING(valor, '([\d]{1,9})') AS integer)) from public.detalle_respuesta
                //select sum(CAST(SUBSTRING(dr.valor, '([\d]{1,9})') AS integer)) from public.detalle_respuesta as dr inner join public.respuesta as r on dr.id_respuesta=r.id where r.id_periodo=1 and r.id_alumno=3 and r.correo='vantware@gmail.com'
                //select cp.id_categoria,sum(CAST(SUBSTRING(dr.valor, '([\d]{1,9})') AS integer)) from public.detalle_respuesta as dr inner join public.respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id where r.id_periodo=1 and r.id_alumno=3 and r.correo='vantware@gmail.com' and cp.id_categoria>0 group by cp.id_categoria
                //select id_rol,string_agg(correo,',') from public.evaluador group by id_rol


                $strJefe = $this->db->query("select count(*)as cuenta,string_agg(correo,',') as correo from public.evaluador where id_alumno=" . $da->id_usuario . " and id_rol=3")->fetchAll();
                $strPeer = $this->db->query("select count(*)as cuenta,string_agg(correo,',') as correo from public.evaluador where id_alumno=" . $da->id_usuario . " and id_rol=2")->fetchAll();
                //$strProv = $this->db->query("select count(*)as cuenta,string_agg(correo,',') as correo from public.evaluador where id_alumno=" . $da->id_usuario . " and id_rol=1")->fetchAll();

                $jefe = [];
                $peer = [];

                $mias = $this->db->query("select sum(CAST(SUBSTRING(dr.valor, '([\d]{1,9})') AS integer)) as puntos from public.detalle_respuesta as dr inner join public.respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id where r.id_periodo=" . $periodo->id . " and r.id_alumno=" . $da->id_usuario . " and r.correo='" . $da->Usuario->correo . "' and cp.id_categoria=" . $cat->id)->fetchAll();

                if (isset($strJefe[0]))
                    $jefe = $this->db->query("select sum(CAST(SUBSTRING(dr.valor, '([\d]{1,9})') AS integer)) as puntos from public.detalle_respuesta as dr inner join public.respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id where r.id_periodo=" . $periodo->id . " and r.id_alumno=" . $da->id_usuario . " and r.correo in ('" . str_replace(",", "','", $strJefe[0]["correo"]) . "') and cp.id_categoria=" . $cat->id)->fetchAll();
                if (isset($strPeer[0]))
                    $peer = $this->db->query("select sum(CAST(SUBSTRING(dr.valor, '([\d]{1,9})') AS integer)) as puntos from public.detalle_respuesta as dr inner join public.respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id where r.id_periodo=" . $periodo->id . " and r.id_alumno=" . $da->id_usuario . " and r.correo in ('" . str_replace(",", "','", $strPeer[0]["correo"]) . "') and cp.id_categoria=" . $cat->id)->fetchAll();


                if (isset($mias[0]))
                    $result["mia"][$idc] = $mias[0]["puntos"] / $totales[$idc];
                if (isset($jefe[0]) && $strJefe[0]["cuenta"] > 0)
                    $result["jefe"][$idc] = $jefe[0]["puntos"] / ($totales[$idc] * $strJefe[0]["cuenta"]);
                if (isset($peer[0]) && $strPeer[0]["cuenta"] > 0)
                    $result["peer"][$idc] = $peer[0]["puntos"] / ($totales[$idc] * $strPeer[0]["cuenta"]);
            }
        }






        $result["categorias"] = array_values($result["categorias"]);
        $result["mia"] = array_values($result["mia"]);
        $result["jefe"] = array_values($result["jefe"]);
        $result["peer"] = array_values($result["peer"]);

        echo json_encode($result);
    }

    public function setResetPassAction() {
        $correo = $this->request->getPost('correo');
        $result = [];

        if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $usuario = Usuario::findFirst(["conditions" => "correo=?1", "bind" => [1 => $correo]]);

            if ($usuario) {

                $msg = $this->plantillas->getPlantilla("password", ["usuario" => $usuario->nombre, "url" => $this->token->encrypt($usuario->id, $this->config->webservice->key)]);
                if ($this->enviaCorreo($msg, "Restablecer Contraseña", $correo))
                    $result["success"] = "Correo enviado correctamente";
                else
                    $result["error"] = "Se ha producido un error al enviar el correo";
            } else
                $result["error"] = "Usuario no encontrado. Verifique sus datos e intente nuevamente";
        } else
            $result["error"] = "No se ha introducido un email válido";








        echo json_encode($result);
    }

    public function getIndicadoresAction() {
        $result = [];

        $roles = CRol::find();

        foreach ($roles as $rol)
            $result[strtolower($rol->nombre)] = Usuario::count(["id_rol=?1 AND activo='t'", "bind" => [1 => $rol->id]]);


        $result["carrera"] = CCarrera::count();
        $result["funcionesp"] = CFuncionesp::count();
        $result["competencia"] = CPregunta::count();

        $result["pendientes"] = Evaluador::count(["enviado=false and activo=true"]);

        $result["invitaciones"] = Usuario::count(["enviado=false and activo=true"]);

        echo json_encode($result);
    }

    public function getIndicadores2Action() {
        $result = [];
        $id_director = $this->session->get("usuario_id");

        $mis_carreras = DetalleDirector::find(["conditions" => "id_director=?1", "bind" => [1 => $id_director]]);

        $car = [0];
        foreach ($mis_carreras as $mc)
            $car[] = $mc->id_carrera;


            $mis_alumnos = $this->db->query("
                SELECT da.validado, COUNT(*) AS total
                FROM detalle_alumno da
                JOIN usuario u ON da.id_usuario = u.id
                WHERE u.activo = 't'
                AND da.id_funcionesp IN (
                    SELECT cf.id 
                    FROM catalogo_funcionesp cf 
                    WHERE cf.id_carrera IN (" . implode(",", $car) . ")
                )
                GROUP BY da.validado
            ")->fetchAll();

        $total = 0;
        $totala = 0;
        $totalp = 0;

        foreach ($mis_alumnos as $ma) {
            $total += $ma["total"];
            if ($ma["validado"] == "true")
                $totala = $ma["total"];
            else
                $totalp = $ma["total"];
        }
        $result["carreras"] = $mis_carreras->count();
        $result["alumnos"] = $total;
        $result["asignados"] = $totala;
        $result["pendientes"] = $totalp;
        echo json_encode($result);
    }

    public function getIndicadores3Action() {
        $result = [];
        $id_director = $this->session->get("usuario_id");

        $mis_carreras = DetalleValidador::find(["conditions" => "id_validador=?1", "bind" => [1 => $id_director]]);

        $car = [0];
        foreach ($mis_carreras as $mc)
            $car[] = $mc->id_carrera;

        $mis_alumnos = $this->db->query("
            SELECT da.validado, COUNT(*) AS total
            FROM detalle_alumno da
            JOIN usuario u ON da.id_usuario = u.id
            WHERE u.activo = 't'
            AND da.id_funcionesp IN (
                SELECT cf.id 
                FROM catalogo_funcionesp cf 
                WHERE cf.id_carrera IN (" . implode(",", $car) . ")
            )
            GROUP BY da.validado
        ")->fetchAll();


        $total = 0;
        $totala = 0;
        $totalp = 0;

        foreach ($mis_alumnos as $ma) {
            $total += $ma["total"];
            if ($ma["validado"] == "true")
                $totala = $ma["total"];
            else
                $totalp = $ma["total"];
        }
        $result["carreras"] = $mis_carreras->count();
        $result["alumnos"] = $total;
        $result["asignados"] = $totala;
        $result["pendientes"] = $totalp;
        echo json_encode($result);
    }

    public function getCategoriasCDAction() {
        $id_per = $this->request->getPost('periodo');

        $id_alumno = $this->request->getPost('id_alumno');

        if (!isset($id_alumno))
            $id_alumno = $this->session->get("usuario_id");



        $da = DetalleAlumno::findFirst(["id_usuario=?1", "bind" => [1 => $id_alumno]]);

        $result = ["categorias" => [], "mia" => [], "jefe" => [], "peer" => []];

        if ($da)
            $cats = CPregunta::find(["conditions" => "id_seccion=2 and (semestre=?1 or 1=1) and id_funcionesp=?2", "bind" => [1 => $da->semestre, 2 => $da->id_funcionesp]]);



        //echo "id_seccion=2 and semestre=".$da->semestre." and id_funcionesp=".$da->id_funcionesp;
        //select count(*) as total, id_categoria as idc from catalogo_pregunta where id_seccion=1 group by id_categoria

        if (isset($id_per))
            $periodo = CPeriodo::findFirst(intval($id_per));




        if (!$periodo)
            $periodo = CPeriodo::findFirst(["activo=true"]);


        $totales = [];
        foreach ($cats as $c)
            $totales[$c->id] = 1;






        foreach ($cats as $cat) {

            $idc = intval($cat->id);

            $result["categorias"][$idc] = substr($cat->nombre, 0, 25);
            $result["mia"][$idc] = 0;
            $result["jefe"][$idc] = 0;
            $result["peer"][$idc] = 0;

            if ($periodo && $da && isset($totales[$idc])) {
                //select sum(CAST(SUBSTRING(valor, '([\d]{1,9})') AS integer)) from public.detalle_respuesta
                //select sum(CAST(SUBSTRING(dr.valor, '([\d]{1,9})') AS integer)) from public.detalle_respuesta as dr inner join public.respuesta as r on dr.id_respuesta=r.id where r.id_periodo=1 and r.id_alumno=3 and r.correo='vantware@gmail.com'
                //select cp.id_categoria,sum(CAST(SUBSTRING(dr.valor, '([\d]{1,9})') AS integer)) from public.detalle_respuesta as dr inner join public.respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id where r.id_periodo=1 and r.id_alumno=3 and r.correo='vantware@gmail.com' and cp.id_categoria>0 group by cp.id_categoria
                //select id_rol,string_agg(correo,',') from public.evaluador group by id_rol


                $strJefe = $this->db->query("select count(*)as cuenta,string_agg(correo,',') as correo from public.evaluador where id_alumno=" . $da->id_usuario . " and id_rol=3")->fetchAll();
                $strPeer = $this->db->query("select count(*)as cuenta,string_agg(correo,',') as correo from public.evaluador where id_alumno=" . $da->id_usuario . " and id_rol=2")->fetchAll();

                $jefe = [];
                $peer = [];

                $mias = $this->db->query("select sum(CAST(SUBSTRING(dr.valor, '([\d]{1,9})') AS integer)) as puntos from public.detalle_respuesta as dr inner join public.respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id where r.id_periodo=" . $periodo->id . " and r.id_alumno=" . $da->id_usuario . " and r.correo='" . $da->Usuario->correo . "' and cp.id_seccion=2 and cp.id=" . $cat->id)->fetchAll();

                if (isset($strJefe[0]))
                    $jefe = $this->db->query("select sum(CAST(SUBSTRING(dr.valor, '([\d]{1,9})') AS integer)) as puntos from public.detalle_respuesta as dr inner join public.respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id where r.id_periodo=" . $periodo->id . " and r.id_alumno=" . $da->id_usuario . " and r.correo in ('" . str_replace(",", "','", $strJefe[0]["correo"]) . "') and cp.id_seccion=2 and cp.id=" . $cat->id)->fetchAll();
                if (isset($strPeer[0]))
                    $peer = $this->db->query("select sum(CAST(SUBSTRING(dr.valor, '([\d]{1,9})') AS integer)) as puntos from public.detalle_respuesta as dr inner join public.respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id where r.id_periodo=" . $periodo->id . " and r.id_alumno=" . $da->id_usuario . " and r.correo in ('" . str_replace(",", "','", $strPeer[0]["correo"]) . "') and cp.id_seccion=2 and cp.id=" . $cat->id)->fetchAll();




                if (isset($mias[0]))
                    $result["mia"][$idc] = $mias[0]["puntos"] / $totales[$idc];
                if (isset($jefe[0]) && $strJefe[0]["cuenta"] > 0)
                    $result["jefe"][$idc] = $jefe[0]["puntos"] / ($totales[$idc] * $strJefe[0]["cuenta"]);
                if (isset($peer[0]) && $strPeer[0]["cuenta"] > 0)
                    $result["peer"][$idc] = $peer[0]["puntos"] / ($totales[$idc] * $strPeer[0]["cuenta"]);
            }
        }











        $result["categorias"] = array_values($result["categorias"]);
        $result["mia"] = array_values($result["mia"]);
        $result["jefe"] = array_values($result["jefe"]);
        $result["peer"] = array_values($result["peer"]);

        echo json_encode($result);
    }

}
