<?php


function generarFactura($id, Factura &$factura, $conex){

    try{
        
        /* RECIBIR DATOS DE SUS SISTEMA */
        $nroFactura = $id;

        
        $factura = new Factura();        
        
        
        /* INICIO SECCION DE DATOS QUEMADOS */
        
        $factura->tipoEmision = 1;
        $factura->codDoc = '01';

        $sql = "SELECT t.id, invoice_no, t.transaction_date, final_total, total_before_tax, discount_amount, 
                    ruc, nombre, razon, obligado, establecimiento, punto_emision, direccion, p12_file_path, p12_password, testing, logo, telefono, correo,
                    c.name as cliente, c.contact_id as dni, c.address_line_1, c.address_line_2, c.city, c.email, c.mobile,
                    f.clave_acceso, f.fecha_autorizacion, f.numero_autorizacion, f.ambiente
                    FROM transactions t 
                    INNER JOIN fe_empresa e ON t.business_id = e.business_id AND t.location_id = e.location_id
                    INNER JOIN contacts c ON c.id = t.contact_id 
                    LEFT JOIN fe_facturas f ON f.transaction_id = t.id 
                    WHERE t.id = $id ;";
        $transaction = null;
        $resultado=$conex->query($sql);
        while($fila = $resultado->fetch_array()){ 
            $transaction = $fila;
        }
        $pruebas = $transaction['testing'] == 1 ;
        $factura->ambiente = $pruebas ? '1':'2';

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
        $factura->telefono = $transaction['telefono'];
        $factura->correo = $transaction['correo'];

        $factura->emailCliente = $transaction['email'];
        $factura->telefonoCliente = $transaction['mobile'];
        
        $factura->setSecuencial($transaction['invoice_no']);//verificar

        /* autorizacion */
        $factura->fechaAutorizacion = $transaction['fecha_autorizacion'] ? $transaction['fecha_autorizacion']:null;
        $factura->numeroAutorizacion = $transaction['numero_autorizacion'] ? $transaction['numero_autorizacion']:null;
        $factura->ambienteAutorizacion = $transaction['ambiente'] ? $transaction['ambiente']:null;
        /***************/

        $factura->fechaEmision = new DateTime($transaction['transaction_date']) ;
        $tipoDni = strlen($transaction["dni"]) == 13 ? '04':'05';
        $factura->tipoIdentificacionComprador = $tipoDni;
        $factura->identificacionComprador = $transaction['dni'];
        $factura->razonSocialComprador = $transaction['cliente'];
        $direccion = $transaction["address_line_1"].', '.$transaction["address_line_2"] .' - '. $transaction['city'];
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
        
        return $transaction;

    }catch(Exception $ex){
        var_dump($ex);
    }
}
?>