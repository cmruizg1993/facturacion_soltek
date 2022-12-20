<?php
//Header('Content-type: text/xml');
require('../facturacion/clases.php');
require('../facturacion/connexion.php');

    $id = isset($_GET["id"]) ? $_GET["id"]: null;

    if(!$id) die("No existe la factura !");

    try{

        $conex = mysqli_connect($host,$user,$password);
        mysqli_select_db($conex,$database);


        $sql = "SELECT * FROM fe_facturas f WHERE transaction_id = $id AND f.estado_sri = 'RECIBIDA' ORDER BY id DESC";
        $factura = null;
        $resultado=$conex->query($sql);
        while($fila = $resultado->fetch_array()){ 
            $factura = $fila;
        }

        $api = new FacturacionApi();
        // consumir endpoint de firma electrónica 
        $ruc = '1717000556001';       
        // consumir endpoint de recepción        
        $idVenta = $id;//(int)$factura->secuencial;
        $respuesta = $api->autorizacion($factura['clave_acceso'], $ruc);
        var_dump($respuesta);
        if(isset($respuesta->respuestaAutorizacion)){
            $index = stripos($respuesta->respuestaAutorizacion, "/");
            var_dump($index);
            $estado = $index !== false ? trim(substr($respuesta->respuestaRecepcion, 0, $index)):$respuesta->respuestaRecepcion;
            $mensaje = $index !== false ? substr($respuesta->respuestaRecepcion, $index+1):'';
            $sql4 = "INSERT INTO fe_facturas (estado_sri, transaction_id, respuesta_sri) VALUES ('$estado', '$id', '$mensaje');";
            var_dump($sql4);
            $r = $conex->query($sql4);
            if($r === false){   
                $sql4 = "UPDATE fe_facturas SET estado_sri = '$estado', respuesta_sri =  '$mensaje' WHERE transaction_id = '$id';";
                var_dump($sql4);
                $r = $conex->query($sql4);
            }
        }
        //ob_clean();
        //print($xml);
        // consumir endpoint de autorización
        //
        // consumir endpoint de ride
        //$api->ride($factura->secuencial, $factura->ruc);

    }catch(Exception $ex){
        var_dump($ex);
    }

?>