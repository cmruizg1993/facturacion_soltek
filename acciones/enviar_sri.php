<?php
header('Content-Type: text/html; charset=UTF-8');
//Header('Content-type: text/xml');
require('../facturacion/clases.php');
require('../facturacion/xml-factura.php');
require('../facturacion/generar-factura.php');
require('../settings/facturacion.php');
require('../facturacion/connexion.php');
    $id = isset($_GET["id"]) ? $_GET["id"]: null;

    if(!$id) die("No existe la factura !");

    try{
        $conex = mysqli_connect($host,$user,$password);
        mysqli_select_db($conex,$database);

        /* RECIBIR DATOS DE SUS SISTEMA */
        $nroFactura = $id;

        $factura = new Factura();
        $transaction = generarFactura($id, $factura, $conex);

        $xml = generarXmlFactura($factura);        
        $fileInput = "../files/factura-$factura->estab-$factura->ptoEmi-$factura->secuencial.xml";        
        writeUTF8File($fileInput, $xml);
        $fileInput = realpath($fileInput);
                
        $api = new FacturacionApi();
        $jarFile = realpath('../FirmaElectronica/FirmaElectronica.jar');
        
        // parametrizar archivo p12 y clave
        $p12File = realpath('../'. $transaction['p12_file_path']); 
        $p12Password = $transaction['p12_password'];  
        $pruebas = $transaction['testing'] == 1 ;         
        
        $fileOutput = $fileInput.".firmado";              
        $respuesta_firma = $api->firmarXml($jarFile, $fileInput, $p12File, $p12Password, $fileOutput);
        print_r($respuesta_firma);
        $successMessage = 'Documento Firmado Correctamente';
        if($respuesta_firma[0] != $successMessage){            
            die("Error al firmar documento");
        }
        
        $idVenta = $id;//(int)$factura->secuencial;
        
        $respuesta = $api->recepcion($fileOutput, $pruebas);
        print_r(json_encode($respuesta));
        
        if(isset($respuesta->RespuestaRecepcionComprobante)){
            
            $estado = $respuesta->RespuestaRecepcionComprobante->estado;            
            $successState = 'RECIBIDA';
            $successReceiv = $successState == $successState;

            $comprobante = $respuesta->RespuestaRecepcionComprobante->comprobantes->comprobante;
            $mensaje = json_encode($comprobante->mensajes);
            
            $sql4 = "INSERT INTO fe_facturas (estado_sri, transaction_id, respuesta_sri, clave_acceso) VALUES ('$estado', '$id', '$mensaje', '$factura->claveAcceso');";
            
            $r = $conex->query($sql4);
            if($r === false){   
                $sql4 = "UPDATE fe_facturas SET estado_sri = '$estado', respuesta_sri =  '$mensaje', clave_acceso = '$factura->claveAcceso' WHERE transaction_id = '$id';";
                $r = $conex->query($sql4);
            }
            echo $sql4;            
        }
        if($successReceiv){
            echo "RECIBIDA";
        }else{
            echo "ERROR";
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();

    }catch(Exception $ex){
        var_dump($ex);
    }
    function writeUTF8File($filename, $content) {
        $f = fopen($filename, "w");
        # Now UTF-8 - Add byte order mark
        fwrite($f, pack("CCC", 0xef, 0xbb, 0xbf));
        fwrite($f, $content);
        fclose($f);
    }
?>