<?php
function generarXmlFactura(Factura $f){
    $factura = new SimpleXMLElement('<factura/>');
    $factura->addAttribute('id', 'comprobante');
    $factura->addAttribute('version','1.1.0');
    $infoTributaria = $factura->addChild('infoTributaria');
    $infoFactura    = $factura->addChild('infoFactura');
    $detalles       = $factura->addChild('detalles');
    $infoAdicional  = $factura->addChild('infoAdicional');
    
    /* INFO TRIBUTARIA */
    $infoTributaria->addChild("ambiente", "$f->ambiente");
    $infoTributaria->addChild("tipoEmision", "$f->tipoEmision");
    $infoTributaria->addChild("razonSocial", "$f->razonSocial");
    $infoTributaria->addChild("nombreComercial", "$f->nombreComercial");
    $infoTributaria->addChild("ruc", "$f->ruc");
    $f->getClaveAcceso();
    $infoTributaria->addChild("claveAcceso", "$f->claveAcceso");
    $infoTributaria->addChild("codDoc", "$f->codDoc");
    $infoTributaria->addChild("estab", "$f->estab");
    $infoTributaria->addChild("ptoEmi", "$f->ptoEmi");
    $infoTributaria->addChild("secuencial", "$f->secuencial");
    $infoTributaria->addChild("dirMatriz", "$f->dirMatriz");
    
    /* INFO FACTURA */
    $infoFactura->addChild("fechaEmision", $f->fechaEmision->format('d/m/Y'));
    $infoFactura->addChild("dirEstablecimiento", "$f->dirEstablecimiento");
    $infoFactura->addChild("obligadoContabilidad", "$f->obligadoContabilidad");
    $infoFactura->addChild("tipoIdentificacionComprador", "$f->tipoIdentificacionComprador");
    $infoFactura->addChild("razonSocialComprador", "$f->razonSocialComprador");
    $infoFactura->addChild("identificacionComprador", "$f->identificacionComprador");
    $infoFactura->addChild("direccionComprador", "$f->direccionComprador");
    $infoFactura->addChild("totalSinImpuestos", "$f->totalSinImpuestos");
    $infoFactura->addChild("totalDescuento", "$f->totalDescuento");
    
    /* IMPUESTOS INFOFACTURA */
    $totalConImpuestos = $infoFactura->addChild("totalConImpuestos");
    
    foreach($f->impuestos as $i){
        $totalImpuesto = $totalConImpuestos->addChild("totalImpuesto");
        $totalImpuesto->addChild("codigo", "$i->codigo");
        $totalImpuesto->addChild("codigoPorcentaje", "$i->codigoPorcentaje");
        $totalImpuesto->addChild("baseImponible", "$i->baseImponible");
        $totalImpuesto->addChild("valor", "$i->valor");
    }

    $infoFactura->addChild("propina", "$f->propina");
    $infoFactura->addChild("importeTotal", "$f->importeTotal");
    $infoFactura->addChild("moneda", "$f->moneda");
    
    /* PAGOS INFOFACTURA */
    $pagos = $infoFactura->addChild("pagos");
    foreach($f->pagos as $p){
        $pago = $pagos->addChild("pago");
        $pago->addChild("formaPago", "$p->formaPago");
        $pago->addChild("total", "$p->total");
        $pago->addChild("plazo", "$p->plazo");
        $pago->addChild("unidadTiempo", "$p->unidadTiempo");
    }
    
    foreach($f->detalles as $d){
        $detalle = $detalles->addChild("detalle");
        $detalle->addChild("codigoPrincipal", "$d->codigoPrincipal");
        $detalle->addChild("codigoAuxiliar", "$d->codigoAuxiliar");
        $detalle->addChild("descripcion", "$d->descripcion");
        $detalle->addChild("cantidad", "$d->cantidad");
        $detalle->addChild("precioUnitario", "$d->precioUnitario");
        $detalle->addChild("descuento", "$d->descuento");
        $detalle->addChild("precioTotalSinImpuesto", "$d->precioTotalSinImpuesto");
        $impuestosDetalle = $detalle->addChild("impuestos");
        foreach($d->impuestos as $i){
            $impuesto = $impuestosDetalle->addChild("impuesto");
            $impuesto->addChild("codigo", "$i->codigo");
            $impuesto->addChild("codigoPorcentaje", "$i->codigoPorcentaje");
            $impuesto->addChild("tarifa", "$i->tarifa");
            $impuesto->addChild("baseImponible", round($i->baseImponible,2));
            $impuesto->addChild("valor", round($i->valor,2));
            
        }
        if(count($d->infoAdicional) > 0){
            $detallesAdicionales = $detalle->addChild("detallesAdicionales");
    
            /* INFO ADICIONAL */
            foreach($d->infoAdicional as $infod){
                $campoAdicional = $detallesAdicionales->addChild("detAdicional");
                $campoAdicional->addAttribute("nombre", "$infod->nombre");
                $campoAdicional->addAttribute("valor", "$infod->valor");
            }
        }
    }
    if(count($f->infoAdicional) > 0){
        /* INFO ADICIONAL */
        foreach($f->infoAdicional as $info){
            $campoAdicional = $infoAdicional->addChild("campoAdicional", "$info->valor");
            $campoAdicional->addAttribute("nombre", "$info->nombre");
        }
    }
    
    return $factura->asXML();
} 
?>