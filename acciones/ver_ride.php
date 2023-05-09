<?php 
require('../facturacion/clases.php');
require('../facturacion/connexion.php');
try {
    $id = isset($_GET["id"]) ? $_GET["id"]: null;

    if(!$id) die("No existe la factura !");
    $conex = mysqli_connect($host,$user,$password);
    mysqli_select_db($conex,$database);


    $sql = "SELECT f.*, e.ruc, e.razon, c.email FROM fe_facturas f 
                INNER JOIN transactions t ON f.transaction_id = t.id
                INNER JOIN contacts c ON c.id = t.contact_id 
                INNER JOIN fe_empresa e ON t.business_id = e.business_id 
                WHERE transaction_id = $id AND f.estado_sri = 'AUTORIZADO' ORDER BY id DESC";
    var_dump($sql);
    $factura = null;
    
    $resultado=$conex->query($sql);
    while($fila = $resultado->fetch_array()){ 
        $factura = $fila;
    }

    $clave =$factura["clave_acceso"];
    $ruc =$factura["ruc"];
    
    $api = new FacturacionApi();
    $respuesta = $api->ride($clave, $ruc);
    
    if(isset($respuesta->rutaPDF)){
        $fileName = "$clave.pdf";
        $rutaPdf = $respuesta->rutaPDF;
        header('Location: ' . $rutaPdf);
        exit();
    }
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>