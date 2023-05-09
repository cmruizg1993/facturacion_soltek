<?php
//Header('Content-type: text/xml');
require('../facturacion/clases.php');
require('../facturacion/xml-factura.php');
require('../facturacion/connexion.php');
require('../settings/facturacion.php');

    $id = isset($_GET["id"]) ? $_GET["id"]: null;

    if(!$id) die("No existe la factura !");

    try{
        
        /* RECIBIR DATOS DE SUS SISTEMA */
        $nroFactura = $id;

        $conex = mysqli_connect($host,$user,$password);
        mysqli_select_db($conex,$database);
        $factura = new Factura();        
        
        
        /* INICIO SECCION DE DATOS QUEMADOS */
        $factura->ambiente = $pruebas ? '1':'2';
        $factura->tipoEmision = 1;
        $factura->codDoc = '01';

        $sql = "SELECT t.id, invoice_no, t.created_at, final_total, total_before_tax, discount_amount, 
                    ruc, nombre, razon, obligado, establecimiento, punto_emision, direccion, 
                    c.name as cliente, c.contact_id as dni, c.address_line_1, c.address_line_2 
                    FROM transactions t 
                    INNER JOIN fe_empresa e ON t.business_id = e.business_id 
                    INNER JOIN contacts c ON c.id = t.contact_id 
                    WHERE t.id = $id ;";
        $transaction = null;
        $resultado=$conex->query($sql);
        while($fila = $resultado->fetch_array()){ 
            $transaction = $fila;
        }


        $factura->estab = $transaction["establecimiento"];
        $factura->ptoEmi = $transaction["punto_emision"];
        $factura->propina = 0;
        $factura->moneda = 'DOLAR';

        $factura->razonSocial =  $transaction["razon"];
        $factura->nombreComercial =  $transaction["nombre"];
        $factura->dirMatriz = $transaction['direccion'];
        $factura->ruc =  $transaction['ruc'];
        $factura->obligadoContabilidad = strtoupper($transaction['obligado']) ;
        $factura->dirEstablecimiento = $transaction['direccion'];
        

        
        $factura->setSecuencial($transaction['invoice_no']);//verificar

        $factura->fechaEmision = new DateTime($transaction['created_at']) ;
        $tipoDni = strlen($transaction["dni"]) == 13 ? '04':'05';
        $factura->tipoIdentificacionComprador = $tipoDni;
        $factura->identificacionComprador = $transaction['dni'];
        $factura->razonSocialComprador = $transaction['cliente'];
        $direccion = $transaction["address_line_1"].', '.$transaction["address_line_2"];
        $factura->direccionComprador = $direccion;


        
        
        $sql3= "SELECT t.quantity, t.unit_price_before_discount, t.unit_price, t.line_discount_amount, t.unit_price_inc_tax, t.item_tax, t.tax_id, 
                    p.id as cod, p.sku, p.name as producto,
                    tax.amount 
                    FROM `transaction_sell_lines` t 
                    INNER JOIN products p ON t.product_id = p.id
                    LEFT JOIN tax_rates tax ON t.tax_id = tax.id
                    WHERE transaction_id = $id";     
        $resultado3=$conex->query($sql3);
        $lines = null;
        $subtotal0 = 0;
        $subtotal12 = 0;
        $iva12 = 0;
        $descuento = 0;
        while($fila3 = $resultado3->fetch_array()){
            $detalle = new Detalle();
            $detalle->codigoPrincipal = $fila3['sku'];
            $detalle->codigoAuxiliar = $fila3['cod'];
            $detalle->descripcion = $fila3['producto'];
            $detalle->cantidad = $fila3['quantity'];

            $detalle->precioUnitario = round($fila3['unit_price'],2);
            $detalle->precioTotalSinImpuesto = round($fila3['unit_price']*$fila3['quantity'],2);
            $tieneIva = (int)$fila3['amount'] > 0 ;

            if($tieneIva){
                
                $porcentaje = (int)$fila3['amount']/100;
                $pvp = $fila3['unit_price_inc_tax'];
                $precio = $pvp/(1 + $porcentaje);
                $iva = $precio * $porcentaje ;
                
                
                $detalle->precioUnitario = round($precio, 2);
                $detalle->precioTotalSinImpuesto = round($precio*$fila3['quantity'], 2);
                
                $i12 = new Impuesto();
                $i12->codigo = '2';
                $i12->codigoPorcentaje = '2';
                $i12->baseImponible = round($precio*$fila3['quantity'],2);
                $i12->valor = round($iva*$fila3['quantity'] ,2);
                $i12->tarifa = '12';
                $detalle->agregarImpuesto($i12);
                $subtotal12 += $precio*$fila3['quantity'];
                $iva12 += $iva*$fila3['quantity'] ;
            }else{
                $i0 = new Impuesto();
                $i0->codigo = '2';
                $i0->codigoPorcentaje = '0';
                $i0->baseImponible = round($fila3['unit_price']*$fila3['quantity'],2);
                $i0->valor = 0 ;
                $i0->tarifa = '0';
                $detalle->agregarImpuesto($i0);
                $subtotal0 += $fila3['unit_price']*$fila3['quantity'];
            }

            $detalle->descuento = round(0, 2);
            $factura->agregarDetalle($detalle);
        }
        //var_dump($factura);
        $factura->totalDescuento = round(0, 2);
        $factura->totalSinImpuestos = round($subtotal0 + $subtotal12,2);
        $factura->importeTotal = round($transaction['final_total'],2);

        if($subtotal12 > 0){
            $impuesto12 = new Impuesto();
            $impuesto12->codigo = '2';
            $impuesto12->codigoPorcentaje = '2';
            $impuesto12->baseImponible = round($subtotal12,2);
            $impuesto12->valor = round($iva12,2);
            $factura->agregarImpuesto($impuesto12);
        }
        if($subtotal0 > 0){
            $impuesto0 = new Impuesto();
            $impuesto0->codigo = '2';
            $impuesto0->codigoPorcentaje = '0';
            $impuesto0->baseImponible = round($subtotal0,2);
            $impuesto0->valor = 0;
            $factura->agregarImpuesto($impuesto0);
        }
        /*
        $pago = new FormaPago();
        $pago->formaPago = $fila2['id_fpago'];
        $pago->plazo = $fila2['plazo'];
        $pago->total = $fila2['total'];
        $pago->unidadTiempo = 'Dias'; 
        $factura->agregarPago($pago);
        
        */

        //$dato1 = new InfoAdicional();
        //$dato1->nombre = 'Sitio web de la empresa';
        //$dato1->valor = 'miempresa.com';
        //$factura->agregarInfo($dato1);
        
        $xml = generarXmlFactura($factura);
        
        $myfile = fopen("../files/factura-$factura->secuencial.xml", "w");
        fwrite($myfile, $xml);    
        
        
        $api = new FacturacionApi();
        // consumir endpoint de firma electrónica 
        //$factura->ruc = '1717000556001';       
        $respuesta_firma = $api->firmaXml($xml, $factura, $id);        
        // consumir endpoint de recepción
        /*
        $testJson = [];
        $testJson["xml"] = $xml;
        $testJson["claveAcceso"] = $factura->claveAcceso;
        print(json_encode($testJson));
        */
        var_dump($respuesta_firma);
        $idVenta = $id;//(int)$factura->secuencial;
        $respuesta = $api->recepcion($factura->claveAcceso, $factura->ruc, $pruebas);
        //var_dump($respuesta);
        if(isset($respuesta->respuestaRecepcion)){
            $index = stripos($respuesta->respuestaRecepcion, "/");
            $estado = $index !== false ? trim(substr($respuesta->respuestaRecepcion, 0, $index)):$respuesta->respuestaRecepcion;
            $mensaje = $index !== false ? substr($respuesta->respuestaRecepcion, $index+1):'';
            $sql4 = "INSERT INTO fe_facturas (estado_sri, transaction_id, respuesta_sri, clave_acceso) VALUES ('$estado', '$id', '$mensaje', '$factura->claveAcceso');";
            
            $r = $conex->query($sql4);
            if($r === false){   
                $sql4 = "UPDATE fe_facturas SET estado_sri = '$estado', respuesta_sri =  '$mensaje', clave_acceso = '$factura->claveAcceso' WHERE transaction_id = '$id';";
                $r = $conex->query($sql4);
            }
            echo $sql4;
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
        //ob_clean();
        //print($xml);
        // consumir endpoint de autorización
        //$api->autorizacion($idVenta, $factura->ruc);
        // consumir endpoint de ride
        //$api->ride($factura->secuencial, $factura->ruc);

    }catch(Exception $ex){
        var_dump($ex);
    }

?>