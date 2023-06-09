b<?php
Header('ContentType: ');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailerException;
//Load Composer's autoloader
$sendMail = true;
require('../ride.php'); 


//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);
$email = null;
try {
    $id = isset($_GET["id"]) ? $_GET["id"]: null;
    
    if(!$id) die("No existe la factura !");
    //$conex = mysqli_connect($host,$user,$password);
    //mysqli_select_db($conex,$database);
    

      


    $sql = "SELECT f.*, e.ruc, e.razon, c.name as cliente, c.email, t.final_total FROM fe_facturas f 
                INNER JOIN transactions t ON f.transaction_id = t.id
                INNER JOIN contacts c ON c.id = t.contact_id 
                INNER JOIN fe_empresa e ON t.business_id = e.business_id
                WHERE transaction_id = $id AND f.estado_sri = 'AUTORIZADO' ORDER BY id DESC";
    $f = null;
    $resultado=$conex->query($sql);
    while($fila = $resultado->fetch_array()){ 
        $f = $fila;
    }
    $razon = $f["razon"];
    $nombre = $f["cliente"];
    $email = $f["email"];
    $clave =$f["clave_acceso"];
    $ruc =$f["ruc"];
    $total = $f["final_total"];
    /*
    $api = new FacturacionApi();
    $respuesta = $api->ride($clave, $ruc);
    var_dump($respuesta);
    */
    var_dump($pdfName);
    if(isset($pdfName)){
        /*
        $fileName = "$clave.pdf";
        $rutaPdf = $respuesta->rutaPDF;
        $respuestaDescarga = $api->getRequest('', $rutaPdf, true, $fileName);
        if($respuestaDescarga!==true){
            die("Error al descargar el archivo PDF");
        }
        */
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'soltek.facturas@gmail.com';                     //SMTP username
        $mail->Password   = 'hhpjrkarbduzpzyt';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        //$mail->setFrom('from@example.com', 'Mailer');
        //$mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient
        $mail->addAddress($email);               //Name is optional
        //$mail->addReplyTo('info@example.com', 'Information');
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');

        //Attachments
        $fileInput = "../files/factura-$factura->estab-$factura->ptoEmi-$factura->secuencial.xml";        
        $mail->addAttachment($fileInput);         //Add attachments
        $mail->addAttachment(".$pdfName");         //Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

        //Content
        $mail->isHTML(true);                            //Set email format to HTML
        $mail->Subject = utf8_decode('Facturación electrónica');
        $mail->Body    = "Estimado: $nombre<br>
        Acabas de recibir un Documento Electrónico<br>
        Emisor: $razon<br>
        Tipo Documento: FACTURA<br>
        Clave de Acceso: $clave<br>
        <p>Valor: $total</p></br></br>
        <b>Para ver tu factura, por favor, haz click en el siguiente enlace. De preferencia en un navegador Chrome o Edge.</b></br>
        <a href='https://www.soltekpos.online/facturacion/ride.php?id=$id'>Ver Factura</a>";
        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        $sql4 = "UPDATE fe_facturas SET estado_mail = '1' WHERE transaction_id = '$id';";
        $r = $conex->query($sql4);
        header('Location: /facturacion/main.php?ruta=AUTORIZADAS');
        exit();
    }else{
        echo "Error al obtener el PDF";
    }
} catch (MailerException $e) {
    if($email){
        header('Location: ' . $_SERVER['HTTP_REFERER'].'&error=No se pudo enviar el mensaje a la dirección de correo: '.$email.'Mailer Error: '.$mail->ErrorInfo);
    }else{
        header('Location: ' . $_SERVER['HTTP_REFERER'].'&error=El email del destinatario no es válido.');
    }    
    exit();
}

?>
