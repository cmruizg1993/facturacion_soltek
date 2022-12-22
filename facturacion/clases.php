<?php
class Factura{
    /* info tributaria */
    public $ambiente;
    public $tipoEmision;
    public $razonSocial;
    public $nombreComercial;
    public $ruc;
    public $claveAcceso;
    public $codDoc;
    public $estab;
    public $ptoEmi;
    public $secuencial;
    public $dirMatriz;
    /* info factura */
    public $fechaEmision;
    public $dirEstablecimiento;
    public $obligadoContabilidad;
    public $tipoIdentificacionComprador;
    public $razonSocialComprador;
    public $identificacionComprador;
    public $direccionComprador;
    public $totalSinImpuestos;
    public $totalDescuento;
    public $propina;
    public $importeTotal;
    public $moneda;
    public $impuestos = [];
    public $pagos = [];
    public $infoAdicional = [];
    public $detalles = [];
    /* */
    public function agregarDetalle($d){
        $this->detalles[] = $d;
    }
    public function agregarImpuesto($i){
        $this->impuestos[] = $i;
    }
    public function agregarPago($p){
        $this->pagos[] = $p;
    }
    public function agregarInfo($i){
        $this->infoAdicional[] = $i;
    }
    public function setSecuencial($nro_fact){
        $secuencial = $nro_fact.'';
        while(strlen($secuencial) < 9)$secuencial = '0'.$secuencial;
        $this->secuencial = $secuencial;
    }
    public function getClaveAcceso(){
        $n = rand(10000000, 99999999);
        $digito8 = $n.'';
        $fecha = $this->fechaEmision->format('dmY');
        $claveAcceso = "$fecha$this->codDoc$this->ruc$this->ambiente$this->estab$this->ptoEmi$this->secuencial$digito8$this->tipoEmision";
        $suma = 0;
        $factor = 7;
        foreach(str_split($claveAcceso) as $item ){
            $suma = $suma + (int)$item * $factor;
            $factor = $factor - 1;
            if ($factor == 1)$factor = 7;
        }
        $digitoVerificador = ($suma % 11);
        $digitoVerificador = 11 - $digitoVerificador;
        if ($digitoVerificador == 11)$digitoVerificador = 0;
        if ($digitoVerificador == 10)$digitoVerificador = 1;
        $claveAcceso=$claveAcceso.$digitoVerificador;
        $this->claveAcceso = $claveAcceso;        
        return $claveAcceso;
    }   
}
class Detalle{
    public $codigoPrincipal;
    public $codigoAuxiliar;
    public $descripcion;
    public $cantidad;
    public $precioUnitario;
    public $descuento;
    public $precioTotalSinImpuesto;
    public $impuestos = [];
    public $infoAdicional = [];
    public function agregarImpuesto($i){
        $this->impuestos[] = $i;
    }
    public function agregarInfo($i){
        $this->infoAdicional[] = $i;
    }
}
class Impuesto{
    public $codigo;
    public $codigoPorcentaje;
    public $baseImponible;
    public $valor;
    public $tarifa;
}
class FormaPago{
    public $formaPago;
    public $total;
    public $plazo;
    public $unidadTiempo;
}
class InfoAdicional{
    public $nombre;
    public $valor;
}
class FacturacionApi{
    //private $url = "http://marceloyamberla-001-site1.etempurl.com";
    private $url = "http://klever2022-001-site1.gtempurl.com";
    public function firmaXml($xml, Factura $factura, $idVenta){
        $data = [];
        $xml = str_replace("\n","", $xml);
        $xml = str_replace("\"", "'", $xml);
        $data["idComprobanteVenta"] = $idVenta;//(int) $factura->secuencial;
        $data["xmlGenerado"] = $xml;
        $data["rucEmpresa"] = $factura->ruc;
        $data["claveAcceso"] = $factura->claveAcceso;
        $endpoint = "/api/facturacion/FirmaXml";
        $ch = curl_init( "$this->url$endpoint" );
        # Setup request to send json via POST.
        $payload = json_encode( $data );
        // Prepare new cURL resource
        $ch = curl_init($this->url.$endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        // Set HTTP Header for POST request 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload))
        );
        // Submit the POST request
        $result = curl_exec($ch);
        //$respuesta = json_decode($result, true);
        return $result;
    }
    public function recepcion($claveAcceso, $ruc){
        $endpoint = "/api/facturacion/RecepcionPrueba?RucEmpresa=$ruc&ClaveAcceso=$claveAcceso";
        $result = $this->getRequest($endpoint);
        $respuesta = json_decode($result);
        //print_r($result);
        return $respuesta;
    }
    public function autorizacion($claveAcceso, $ruc){
        $endpoint = "/api/facturacion/AutorizacionPrueba?RucEmpresa=$ruc&ClaveAcceso=$claveAcceso";
        var_dump($endpoint);
        $result = $this->getRequest($endpoint);
        $respuesta = json_decode($result);
        return $respuesta;
    }
    public function ride($claveAcceso, $ruc){
        $endpoint = "/api/facturacion/GeneracionRide?RucEmpresa=$ruc&ClaveAcceso=$claveAcceso";
        $result = $this->getRequest($endpoint);
        $respuesta = json_decode($result);
        return $respuesta;
    }
    public function getRequest($endpoint, $resource = null, $isFile = false, $file = null){
        $ch = null;
        if($resource){
            $ch = curl_init("$resource");    
        }else{
            $ch = curl_init("$this->url$endpoint");
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($isFile){
            //The path & filename to save to.
            $saveTo = "../files/$file";
            //Open file handler.
            $fp = fopen($saveTo, 'w+');
            //If $fp is FALSE, something went wrong.
            if($fp === false){
                throw new Exception('Could not open: ' . $saveTo);
            }
            //Pass our file handle to cURL.
            curl_setopt($ch, CURLOPT_FILE, $fp);
        }
        $result = curl_exec($ch);
        if($isFile){
            fclose($fp);
        }
        if(curl_error($ch)) {
            //print_r($ch);
        }
        curl_close($ch);
        var_dump($result);
        return $result;
    }
}
?>