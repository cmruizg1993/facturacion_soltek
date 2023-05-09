<?php
    Header('Content-type: application/json');
    require('./clases.php');
    require('./xml-factura.php');
    require('./connexion.php');

    try{
        /* RECIBIR DATOS DE SUS SISTEMA */
        $nroFactura = '1';

        $conex = mysqli_connect($host,$user,$password);
        mysqli_select_db($conex,$database);
        $factura = new Factura();        
        
        
        /* INICIO SECCION DE DATOS QUEMADOS */
        $factura->ambiente = 1;
        $factura->tipoEmision = 1;
        $factura->codDoc = '01';
        $factura->estab = '001';
        $factura->ptoEmi = '001';
        $factura->propina = 0;
        $factura->moneda = 'DOLAR';

        /* FIN SECCION DE DATOS QUEMADOS */
        $sql1= "SELECT * FROM agencia";     
        $resultado1=$conex->query($sql1);
        while($fila1 = $resultado1->fetch_array()){ 
            $factura->razonSocial =  $fila1['razonSocial'];
            $factura->nombreComercial =  $fila1['nombreComercial'];
            $factura->dirMatriz = $fila1['direccion'];
            $factura->ruc =  $fila1['ruc'];
            $factura->obligadoContabilidad = strtoupper($fila1['obligadoContabilidad']) ;
            $factura->dirEstablecimiento = $fila1['direccion'];            
        }
        
        $sql2= "SELECT * FROM resumen_vtas r INNER JOIN cliente c ON  r.cedula_cli = c.ci WHERE nro_fact=$nroFactura LIMIT 1";     
        $resultado2=$conex->query($sql2);
        $idCliente = null;
        while($fila2 = $resultado2->fetch_array()){ 
            $factura->setSecuencial($fila2['nro_fact']);
            $factura->fechaEmision = new DateTime($fila2['fecha_emision']);
            $factura->tipoIdentificacionComprador = $fila2['tipoIdentificacion'];
            $factura->identificacionComprador = $fila2['ci'];
            $factura->razonSocialComprador = $fila2['nombres'];
            $factura->direccionComprador = $fila2['direccion'];
            $factura->totalDescuento = $fila2['descuentoTotal'];
            $factura->totalSinImpuestos = $fila2['subtotal'];
            $factura->importeTotal = $fila2['total'];
            
            if($fila2['subtotal12'] > 0){
                $impuesto12 = new Impuesto();
                $impuesto12->codigo = '2';
                $impuesto12->codigoPorcentaje = '2';
                $impuesto12->baseImponible = $fila2['subtotal12'];
                $impuesto12->valor = $fila2['iva'];
                $factura->agregarImpuesto($impuesto12);
            }
            if($fila2['subtotal0'] > 0){
                $impuesto0 = new Impuesto();
                $impuesto0->codigo = '2';
                $impuesto0->codigoPorcentaje = '0';
                $impuesto0->baseImponible = $fila2['subtotal0'];
                $impuesto0->valor = 0;
                $factura->agregarImpuesto($impuesto0);
            }
            $pago = new FormaPago();
            $pago->formaPago = $fila2['id_fpago'];
            $pago->plazo = $fila2['plazo'];
            $pago->total = $fila2['total'];
            $pago->unidadTiempo = 'Dias'; 
            $factura->agregarPago($pago);
        }
        $sql3= "SELECT * FROM `ventas`
                        INNER JOIN `inventarios` ON ventas.cod_inv = inventarios.cod_inv
                        INNER JOIN `productos` ON inventarios.cod_prod = productos.cod_prod
                        WHERE ventas.nro_factura = $nroFactura";     
        $resultado3=$conex->query($sql3);

        while($fila3 = $resultado3->fetch_array()){ 
            $detalle = new Detalle();
            $detalle->codigoPrincipal = $fila3['cod_prod'];
            $detalle->codigoAuxiliar = $fila3['cod_prod'];
            $detalle->descripcion = $fila3['detalle_prod'];
            $detalle->cantidad = $fila3['cantidad_vta'];
            $detalle->precioUnitario = $fila3['precio_venta'];
            $detalle->precioTotalSinImpuesto = $fila3['precio_entregado'];
            $tieneIva = $fila3['iva_apli'] == 's' ;
            if($tieneIva){
                $i12 = new Impuesto();
                $i12->codigo = '2';
                $i12->codigoPorcentaje = '2';
                $i12->baseImponible = $fila3['precio_entregado'];
                $i12->valor = $fila3['precio_entregado']*0.12 ;
                $i12->tarifa = '12';
                $detalle->agregarImpuesto($i12);
            }else{
                $i0 = new Impuesto();
                $i0->codigo = '2';
                $i0->codigoPorcentaje = '0';
                $i0->baseImponible = $fila3['precio_entregado'];
                $i0->valor = 0 ;
                $i0->tarifa = '0';
                $detalle->agregarImpuesto($i0);
            }
            $detalle->descuento = $fila3['descuento_usd'];
            $factura->agregarDetalle($detalle);
        }
        $dato1 = new InfoAdicional();
        $dato1->nombre = 'Sitio web de la empresa';
        $dato1->valor = 'www.google.com';
        $factura->agregarInfo($dato1);

        $xml = generarXmlFactura($factura);
        $myfile = fopen("factura-$factura->secuencial.xml", "w");
        fwrite($myfile, $xml);
        ob_clean();
        //print($xml);        
        $api = new FacturacionApi();
        // consumir endpoint de firma electrónica 
        $factura->ruc = '1717000556001';       
        $api->firmaXml($xml, $factura, $count);        
        // consumir endpoint de recepción
        /*
        $idVenta = $count;//(int)$factura->secuencial;
        $respuesta = $api->recepcion($idVenta, $factura->ruc);
        if(isset($respuesta->respuestaRecepcion)){
            $estado = $respuesta->respuestaRecepcion;
            $sql4 = "UPDATE resumen_vtas SET estado='$estado' WHERE nro_fact = $nroFactura";
            $r = $conex->query($sql4);
        }
        */
        // consumir endpoint de autorización
        //$api->autorizacion($idVenta, $factura->ruc);
        // consumir endpoint de ride
        //$api->ride($factura->secuencial, $factura->ruc);

    }catch(Exception $ex){
        //print($ex->getMessage());
    }
 
    
?>
