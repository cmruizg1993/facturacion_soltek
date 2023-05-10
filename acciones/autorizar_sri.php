<?php
//Header('Content-type: text/xml');
require('../facturacion/clases.php');
require('../facturacion/connexion.php');
require('../settings/facturacion.php');
    $id = isset($_GET["id"]) ? $_GET["id"]: null;

    if(!$id) die("No existe la factura !");

    try{

        $conex = mysqli_connect($host,$user,$password);
        mysqli_select_db($conex,$database);


        $sql = "SELECT f.*, e.ruc, testing FROM fe_facturas f 
                    INNER JOIN transactions t ON f.transaction_id = t.id
                    INNER JOIN fe_empresa e ON t.business_id = e.business_id AND t.location_id = e.location_id
                    WHERE transaction_id = $id AND f.estado_sri = 'RECIBIDA' ORDER BY id DESC";
        $factura = null;
        $resultado=$conex->query($sql);
        while($fila = $resultado->fetch_array()){ 
            $factura = $fila;
        }

        $api = new FacturacionApi();
        // consumir endpoint de firma electrónica 
        $ruc = $factura['ruc'];
        $pruebas = $factura['testing'] == 1 ;    
        // consumir endpoint de recepción        
        $idVenta = $id;//(int)$factura->secuencial;
        $respuesta = $api->autorizacion($factura['clave_acceso'], $pruebas);
        print_r($respuesta);

        if(isset($respuesta->RespuestaAutorizacionComprobante)){
            
            $autorizaciones = isset ($respuesta->RespuestaAutorizacionComprobante->autorizaciones) ? $respuesta->RespuestaAutorizacionComprobante->autorizaciones: null;
            $autorizacion = null;
            if($autorizaciones) $autorizacion = $autorizaciones->autorizacion;
            $estado = '';
            $mensaje = '';
            if($autorizacion){
                $estado = isset($autorizacion->estado) ? $autorizacion->estado: '';
                $nroAutorizacion = isset($autorizacion->numeroAutorizacion) ? $autorizacion->numeroAutorizacion: '';
                $fechaAutorizacion = isset($autorizacion->fechaAutorizacion) ? $autorizacion->fechaAutorizacion: '';
                $ambiente = isset($autorizacion->ambiente) ? $autorizacion->ambiente: '';
            }
            $successState = 'AUTORIZADO';
            $successAuth = $successState == $successState;
            //if(!$successAuth) $estado = 'NO '.$successState;

            $sql4 = "INSERT INTO fe_facturas (estado_sri, transaction_id, respuesta_sri) VALUES ('$estado', '$id', '$mensaje');";
            $r = $conex->query($sql4);
            if($r === false){   
                $sql4 = "UPDATE fe_facturas SET estado_sri = '$estado', respuesta_sri =  '$mensaje' WHERE transaction_id = '$id';";
                $r = $conex->query($sql4);
            }
        }
    
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();

    }catch(Exception $ex){
        var_dump($ex);
    }

?>
