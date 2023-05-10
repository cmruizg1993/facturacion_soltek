<?php 
if ($business && $location) {

    $empresa = isset($_POST['empresa']) ? $_POST['empresa'] : null;
    $ruc = isset($_POST['ruc']) ? $_POST['ruc'] : '';
    $razon = isset($_POST['razon']) ? $_POST['razon'] : '';
    $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
    $obligado = isset($_POST['obligado']) ? $_POST['obligado'] : '';
    $establecimiento = isset($_POST['establecimiento']) ? $_POST['establecimiento'] : '';
    $punto_emision = isset($_POST['punto_emision']) ? $_POST['punto_emision'] : '';
    $direccion = isset($_POST['direccion']) ? $_POST['direccion'] : '';
    $p12_password = isset($_POST['p12_password']) ? $_POST['p12_password'] : '';
    $telefono = isset($_POST['telefono']) ? $_POST['telefono'] : '';
    $correo = isset($_POST['correo']) ? $_POST['correo'] : '';
    $testing = isset($_POST['testing']) ? $_POST['testing'] : 0;
    $checked = $testing == 1 ? 'true': 'false';

    /* SUBIR ARCHIVO P12*/ 
    $target_dir_p12 = "uploads/firmas/";
    $target_dir_logo = "uploads/logos/";
    
    $uniqidP12 = uniqid();
    $uniqidLogo = uniqid();
    $target_file_p12 = $target_dir_p12 . $uniqidP12;
    $target_file_logo = $target_dir_logo . $uniqidLogo;

    $uploadOk = 1;
    $uploadOkLogo = 1;

    $imageFileTypeP12 = strtolower(pathinfo($_FILES["p12_file"]["name"], PATHINFO_EXTENSION));
    $imageFileTypeLogo = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));
    

    // Check file size
    if ($_FILES["p12_file"]["size"] > 500000) {
        $uploadOk = 0;
    }
    if ($_FILES["logo"]["size"] > 500000) {
        $uploadOkLogo = 0;
    }

    // Allow certain file formats
    if (
        $imageFileTypeP12 != "p12" && $imageFileTypeP12 != "pfx"
    ) {
        $uploadOk = 0;
    }

    if (
        $imageFileTypeLogo != "png"
    ) {
        $uploadOkLogo = 0;
    }

    $target_file_p12.=".".$imageFileTypeP12;
    $target_file_logo.=".".$imageFileTypeLogo;

    if ($uploadOk == 1) {
        $uploadOk = move_uploaded_file($_FILES["p12_file"]["tmp_name"], $target_file_p12) ? 1:0;
    }
    if ($uploadOkLogo == 1 ) {
        $uploadOkLogo = move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file_logo) ? 1:0;
    }

    $subQuery = "";
    if($uploadOk && $empresa){
        $subQuery.=",p12_file_path='$target_file_p12', p12_password='$p12_password' ";
    }

    if($uploadOkLogo && $empresa){
        $subQuery.=",logo='$target_file_logo' ";
    }

    if ($empresa) {
        $sql = "UPDATE fe_empresa SET ruc = '$ruc', nombre='$nombre', razon='$razon', obligado='$obligado', establecimiento='$establecimiento', punto_emision='$punto_emision', direccion='$direccion', testing=$testing, telefono='$telefono', correo='$correo' $subQuery  WHERE id='$empresa'";
    } else {
        $sql = "INSERT INTO fe_empresa (business_id, location_id, ruc, razon, nombre, obligado, establecimiento, punto_emision, direccion, p12_file_path, p12_password, testing, telefono, correo) VALUES('$business', '$location', '$ruc', '$razon', '$nombre', '$obligado', '$establecimiento', '$punto_emision', '$direccion', '$target_file_p12', '$p12_password', $testing, '$telefono', '$correo')";
    }

    $resultado = $conex->query($sql);

    
}
?>