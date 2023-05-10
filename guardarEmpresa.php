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
    $testing = isset($_POST['testing']) ? $_POST['testing'] : 0;
    $checked = $testing == 1 ? 'true': 'false';

    /* SUBIR ARCHIVO */ 
    $target_dir = "uploads/";
    $guid = uniqid();
    $target_file = $target_dir . $guid;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));


    // Check file size
    if ($_FILES["p12_file"]["size"] > 500000) {
        $uploadOk = 0;
    }

    // Allow certain file formats
    if (
        $imageFileType != "p12" && $imageFileType != "pfx"
    ) {
        $uploadOk = 0;
    }

    if (move_uploaded_file($_FILES["p12_file"]["tmp_name"], $target_file)) {
        $uploadOk = 1;
    }

    if($uploadOk == 1){
        if ($empresa) {
            $sql = "UPDATE fe_empresa SET ruc = '$ruc', nombre='$nombre', razon='$razon', obligado='$obligado', establecimiento='$establecimiento', punto_emision='$punto_emision', direccion='$direccion', p12_file_path='$target_file', p12_password='$p12_password', testing=$testing  WHERE id='$empresa'";
        } else {
            $sql = "INSERT INTO fe_empresa (business_id, location_id, ruc, razon, nombre, obligado, establecimiento, punto_emision, direccion, p12_file_path, p12_password, testing) VALUES('$business', '$location', '$ruc', '$razon', '$nombre', '$obligado', '$establecimiento', '$punto_emision', '$direccion', '$target_file', '$p12_password', $testing)";
        }
    
        $resultado = $conex->query($sql);
    }else{
        if ($empresa) {
            $sql = "UPDATE fe_empresa SET ruc = '$ruc', nombre='$nombre', razon='$razon', obligado='$obligado', establecimiento='$establecimiento', punto_emision='$punto_emision', direccion='$direccion', testing=$testing WHERE id='$empresa'";
        } else {
            $sql = "INSERT INTO fe_empresa (business_id, location_id, ruc, razon, nombre, obligado, establecimiento, punto_emision, direccion, p12_file_path, p12_password, testing) VALUES('$business', '$location', '$ruc', '$razon', '$nombre', '$obligado', '$establecimiento', '$punto_emision', '$direccion', '', '', $testing)";
        }    
        $resultado = $conex->query($sql);
    }
}
?>