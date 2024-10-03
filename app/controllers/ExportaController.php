<?php

class ExportaController extends \Phalcon\Mvc\Controller {

    public function initialize() {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
    }

    public function indexAction() {
        
    }







    public function reporteAction() {
	
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="metricas.xlsx"'); 
        header('Cache-Control: max-age=0');

        $per = CPeriodo::findFirst(["activo=true"]);
        
        
        $auto_id=[];
        
        
        //$pregs= CPregunta::find(["id_funcionesp>0"]);
           
	$tipo_usuario = $this->session->get("usuario_tipo");
        
        if($tipo_usuario === "Administrador"){
        	$valores = $this->db->query("select u.id, u.nombre,u.correo,da.semestre,cc.nombre as carrera,u.last_login from public.usuario as u inner join detalle_alumno as da on u.id=da.id_usuario inner join catalogo_funcionesp as cf on da.id_funcionesp=cf.id inner join catalogo_carrera as cc on cf.id_carrera=cc.id where u.activo=true order by semestre,carrera")->fetchAll();
        }elseif($tipo_usuario === "Director"){
		$id_director = $this->session->get("usuario_id");
		$carreras = $this->db->query("select id_carrera from detalle_director where id_director = $id_director")->fetchAll();
		$carrerasA = [];
		foreach ($carreras as $valor){
			$carrerasA[] = $valor["id_carrera"];
		}
		if(count($carrerasA) > 0){
			$str_carreras = '('.implode(",", $carrerasA).')';
			$valores = $this->db->query("select u.id, u.nombre,u.correo,da.semestre,cc.nombre as carrera,u.last_login from public.usuario as u inner join detalle_alumno as da on u.id=da.id_usuario inner join catalogo_funcionesp as cf on da.id_funcionesp=cf.id inner join catalogo_carrera as cc on cf.id_carrera=cc.id where u.activo=true and cc.id IN $str_carreras order by semestre,carrera")->fetchAll();
		}
	}elseif($tipo_usuario === "Validador"){
		$id_validador = $this->session->get("usuario_id");
                $carreras = $this->db->query("select id_carrera from detalle_validador where id_validador = $id_validador")->fetchAll();
                $carrerasA = [];
                foreach ($carreras as $valor){
                        $carrerasA[] = $valor["id_carrera"];
                }
                if(count($carrerasA) > 0){
                        $str_carreras = '('.implode(",", $carrerasA).')';
			$valores = $this->db->query("select u.id, u.nombre,u.correo,da.semestre,cc.nombre as carrera,u.last_login from public.usuario as u inner join detalle_alumno as da on u.id=da.id_usuario inner join catalogo_funcionesp as cf on da.id_funcionesp=cf.id inner join catalogo_carrera as cc on cf.id_carrera=cc.id where u.activo=true and cc.id IN $str_carreras order by semestre,carrera")->fetchAll();
		}
	}else{
		exit();
	}


	//FIN
	foreach ($valores as $valor) {
            $rows[$valor["id"]] = ["Nombre" => $valor["nombre"], "Semestre" => $valor["semestre"], "Carrera" => $valor["carrera"], "Correo" => $valor["correo"], "Último Acceso" => $valor["last_login"], "Autoevaluación" => 0,"Autoevaluación_Transversales"=>0,"Autoevaluación_Disciplinarias"=>0, "Jefes" => 0, "Colegas" => 0, "Evaluación_Jefes" => "No Asignado", "Evaluación_Colegas" => "No Asignado", "Transversales" => 0, "Disciplinarias" => 0, "Métrica_Transversales" => "Bajo", "Métrica_Disciplinarias" => "Bajo"];
        
            
            /*foreach ($pregs as $p){
                    $rows[$valor["id"]]["A_".$p->nombre]="";
                    $rows[$valor["id"]]["J_".$p->nombre]="";
                    $rows[$valor["id"]]["C_".$p->nombre]="";
                }*/
            
        }


        $autos = Respuesta::find(["id_periodo=?1", "bind" => [1 => $per->id]]);
        foreach ($autos as $a)
            if (array_key_exists($a->id_alumno, $rows)) {
                //$rows[$a->id_alumno]["Autoevaluación"] = $a->correo==$rows[$a->id_alumno]["Correo"]?1:0;
                $auto_id[]=$a->id;  
            }




        //2 colega 3 jefe
        $evs = $this->db->query("select count(*) as total,id_alumno from evaluador where id_rol=2 and activo=true group by id_alumno")->fetchAll();
        foreach ($evs as $e)
            if (array_key_exists($e["id_alumno"], $rows)) {
                $rows[$e["id_alumno"]]["Colegas"] = $e["total"];
                $rows[$e["id_alumno"]]["Evaluación_Colegas"] = "Pendiente";
            }


        $evs = $this->db->query("select count(*) as total,id_alumno from evaluador where id_rol=3 and activo=true group by id_alumno")->fetchAll();
        foreach ($evs as $e)
            if (array_key_exists($e["id_alumno"], $rows)) {
                $rows[$e["id_alumno"]]["Jefes"] = $e["total"];
                $rows[$e["id_alumno"]]["Evaluación_Jefes"] = "Pendiente";
            }



        //select count(*) as total,e.id_rol,r.id_alumno from respuesta as r inner join evaluador as e on r.correo=e.correo where id_periodo=2 group by e.id_rol,r.id_alumno
        $evs = $this->db->query("select count(*) as total,e.id_rol,r.id_alumno from respuesta as r inner join evaluador as e on r.correo=e.correo where id_periodo=" . $per->id . " group by e.id_rol,r.id_alumno")->fetchAll();
        foreach ($evs as $e)
            if (array_key_exists($e["id_alumno"], $rows)) {
                if ($e["id_rol"] == 2)
                    $rows[$e["id_alumno"]]["Evaluación_Colegas"] = $e["total"] == $rows[$e["id_alumno"]]["Evaluación_Colegas"] ? "Completado" : "Parcial";
                else
                    $rows[$e["id_alumno"]]["Evaluación_Jefes"] = $e["total"] == $rows[$e["id_alumno"]]["Evaluación_Jefes"] ? "Completado" : "Parcial";
            }



        //1 trans 2 dis
        //select sum(CAST(dr.valor as integer))/count(*) as total, r.id_alumno from detalle_respuesta as dr inner join respuesta as r on dr.id_respuesta=r.id where r.id_periodo=2 and dr.valor ~ '^[0-9]+$' group by r.id_alumno     


        $valores = $this->db->query("select sum(CAST(dr.valor as integer))/count(*) as media from public.detalle_respuesta as dr inner join respuesta as r on dr.id_respuesta=r.id inner join public.catalogo_pregunta as cp on dr.id_pregunta=cp.id inner join detalle_alumno as da on r.id_alumno=da.id_usuario inner join catalogo_funcionesp as cf on da.id_funcionesp=cf.id where r.id_periodo=" . $per->id . " and dr.valor ~ '^[0-9]+$' and cp.id_seccion=1")->fetchAll();

        $mediat = floatval($valores[0]["media"]);
        if (!isset($mediat) || $mediat < 1)
            $mediat = 1;


        $valores = $this->db->query("select sum(CAST(dr.valor as integer))/count(*) as media from public.detalle_respuesta as dr inner join respuesta as r on dr.id_respuesta=r.id inner join public.catalogo_pregunta as cp on dr.id_pregunta=cp.id inner join detalle_alumno as da on r.id_alumno=da.id_usuario inner join catalogo_funcionesp as cf on da.id_funcionesp=cf.id where r.id_periodo=" . $per->id . " and dr.valor ~ '^[0-9]+$' and cp.id_seccion=2")->fetchAll();

        $mediad = floatval($valores[0]["media"]);
        if (!isset($mediad) || $mediad < 1)
            $mediad = 1;
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        


        $evs = $this->db->query("select sum(CAST(dr.valor as integer))/count(*) as total, r.id_alumno from detalle_respuesta as dr inner join respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id where r.id_periodo=" . $per->id . " and cp.id_seccion=1 and dr.valor ~ '^[0-9]+$' group by r.id_alumno")->fetchAll();
        foreach ($evs as $e)
            if (array_key_exists($e["id_alumno"], $rows)) {
                $rows[$e["id_alumno"]]["Transversales"]=$e["total"];
                $rows[$e["id_alumno"]]["Métrica_Transversales"] =(floatval($e["total"])<$mediat?"Bajo":(floatval($e["total"])==$mediat?"Media":"Alto"));
            }



        $evs = $this->db->query("select sum(CAST(dr.valor as integer))/count(*) as total, r.id_alumno from detalle_respuesta as dr inner join respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id where r.id_periodo=" . $per->id . " and cp.id_seccion=2 and dr.valor ~ '^[0-9]+$' group by r.id_alumno")->fetchAll();
        foreach ($evs as $e)
            if (array_key_exists($e["id_alumno"], $rows)) {
                $rows[$e["id_alumno"]]["Disciplinarias"]=$e["total"];
                $rows[$e["id_alumno"]]["Métrica_Disciplinarias"] =(floatval($e["total"])<$mediad?"Bajo":(floatval($e["total"])==$mediad?"Media":"Alto"));
            }
            
            
            
            
            
            
            
        $auto1 = $this->db->query("select sum(CAST(dr.valor as integer))/count(*) as total, r.id_alumno from detalle_respuesta as dr inner join respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id where r.id_periodo=" . $per->id . " and cp.id_seccion=1 and r.id in (".implode(",", $auto_id).") and dr.valor ~ '^[0-9]+$' group by r.id_alumno")->fetchAll();
        foreach ($auto1 as $e)
            if (array_key_exists($e["id_alumno"], $rows)) {
                $rows[$e["id_alumno"]]["Autoevaluación"] =1;
                $rows[$e["id_alumno"]]["Autoevaluación_Transversales"]=$e["total"];
                
            }
            
            
            
            
          
            
            
        $auto2 = $this->db->query("select sum(CAST(dr.valor as integer))/count(*) as total, r.id_alumno from detalle_respuesta as dr inner join respuesta as r on dr.id_respuesta=r.id inner join catalogo_pregunta as cp on dr.id_pregunta=cp.id where r.id_periodo=" . $per->id . " and cp.id_seccion=2 and r.id in (".implode(",", $auto_id).") and dr.valor ~ '^[0-9]+$' group by r.id_alumno")->fetchAll();
        foreach ($auto1 as $e)
            if (array_key_exists($e["id_alumno"], $rows)) {
                $rows[$e["id_alumno"]]["Autoevaluación"] =1;
                $rows[$e["id_alumno"]]["Autoevaluación_Disciplinarias"]=$e["total"];
            }
            
            
            
            $datos2=[];
            $resps= Respuesta::find(["conditions"=>"id_periodo=".$per->id]);
            
            
            
            
            
            
            
           

            
            
            
            
            
            
            /*foreach ($resps as $rep){
                
                
                if (!array_key_exists($rep->id_alumno, $datos2))
                    $datos2[$rep->id_alumno]=[];
                
                
                
                $prefix="A";
                
                $ev= Evaluador::findFirst(["correo=?1","bind"=>[1=>$rep->correo]]);
                if($ev){
                    if($ev->id_rol==2)
                        $prefix="C";
                    else
                        $prefix="J";
                }
                
                
                foreach ($rep->detalleRespuesta as $dr)
                    
                    if($dr->CPregunta->id_funcionesp>0) {
                        
                       
                        
                        $rows[$rep->id_alumno][$prefix."_".$dr->CPregunta->nombre]=$dr->valor;
                    }
                   

                
            }*/
            
        
        
        
        
        echo $this->excel->genera(array_values($rows));
    }


    private function invierte_fecha($fecha, $formato = 'd-m-Y') {
        return date($formato, strtotime($fecha));
    }


}
