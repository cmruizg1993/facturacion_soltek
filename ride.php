<?php
require 'vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Dompdf\Dompdf;
use Picqer\Barcode\BarcodeGeneratorPNG;

require('facturacion/connexion.php');
require('facturacion/clases.php');
require('facturacion/generar-factura.php');

$id = isset($_GET["id"]) ? $_GET["id"] : null;

if (!$id) die("No existe la factura !");

try {
    $conex = mysqli_connect($host, $user, $password);
    mysqli_select_db($conex, $database);

    /* RECIBIR DATOS DE SUS SISTEMA */
    $nroFactura = $id;

    $factura = new Factura();
    $transaction = generarFactura($id, $factura, $conex);
    //print_r($factura);
    // instantiate and use the dompdf class

    $loader = new FilesystemLoader(__DIR__ . '/templates');
    $twig = new Environment($loader);


    $dompdf = new Dompdf();

    $path = $transaction['logo'];

    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 =  base64_encode($data);
    $factura->logo = $base64;
    $factura->claveAcceso = $transaction['clave_acceso'];
    
    $generator = new BarcodeGeneratorPNG();
    $codigoBarras = base64_encode($generator->getBarcode($factura->claveAcceso, $generator::TYPE_CODE_128));


    $html = $twig->render('ride.html', [
        'factura' => $factura,
        'codigoBarras' => $codigoBarras
    ]);
    //echo $html;
    $dompdf->loadHtml($html);

    // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4');

    // Render the HTML as PDF
    $dompdf->render();

    $output = $dompdf->output();
    $pdfName = "./files/ride/factura-$factura->estab-$factura->ptoEmi-$factura->secuencial.pdf";
    file_put_contents("$pdfName", $output);
    if(isset($sendMail)){
        exit();
    }
    print_r($html);
    //header('Location: ' . $pdfName);
    exit();
    
} catch (Exception $e) {
}


