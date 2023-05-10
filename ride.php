<?php
require 'vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Dompdf\Dompdf;

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

    $html = $twig->render('ride.html', [
        'factura' => $factura
    ]);
    //echo $html;
    $dompdf->loadHtml($html);

    // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4', 'landscape');

    // Render the HTML as PDF
    $dompdf->render();

    // Output the generated PDF to Browser
    $dompdf->stream();
} catch (Exception $e) {
}


