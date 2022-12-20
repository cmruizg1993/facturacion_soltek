<?php 
    session_start();
    if(empty($_SESSION["usuario"])){
        session_destroy();
        header("Location: ./public/index.php");
        exit();
    }
    include './facturacion/connexion.php';
    $conex = mysqli_connect($host,$user,$password);
    mysqli_select_db($conex,$database);
    //invoice_no->secuencial
    $method = $_SERVER['REQUEST_METHOD'];
    $empresa = null;
    $dropBusiness = null;
    $business = null;
    if($method == "POST"){
        $dropBusiness = isset($_POST['dropBusiness']) ? $_POST['dropBusiness']: null;
        $business = isset($_POST['business']) ? $_POST['business']: null;
    }
    if($business){
        
        $empresa = isset($_POST['empresa']) ? $_POST['empresa']: null;
        $ruc = isset($_POST['ruc']) ? $_POST['ruc']: '';
        $razon = isset($_POST['razon']) ? $_POST['razon']: '';
        $nombre = isset($_POST['nombre']) ? $_POST['nombre']: '';
        $obligado = isset($_POST['obligado']) ? $_POST['obligado']: '';
        $establecimiento = isset($_POST['establecimiento']) ? $_POST['establecimiento']: '';
        $punto_emision = isset($_POST['punto_emision']) ? $_POST['punto_emision']: '';
        $direccion = isset($_POST['direccion']) ? $_POST['direccion']: '';
        if($empresa){
            $sql = "UPDATE fe_empresa SET ruc = '$ruc', nombre='$nombre', razon='$razon', obligado='$obligado', establecimiento='$establecimiento', punto_emision='$punto_emision', direccion='$direccion' WHERE id='$empresa'";
        }else{
            $sql = "INSERT INTO fe_empresa (business_id, ruc, razon, nombre, obligado, establecimiento, punto_emision, direccion) VALUES('$business', '$ruc', '$razon', '$nombre', '$obligado', '$establecimiento', '$punto_emision', '$direccion')";
        }
        
        $resultado = $conex->query($sql);
        
    }
    if($dropBusiness){
        //
        $sql = "SELECT * FROM fe_empresa WHERE business_id = $dropBusiness;";
        $resultado=$conex->query($sql);
        if($resultado){
            while($fila = $resultado->fetch_array()){
                $empresa = $fila["id"];
                $business = $fila["business_id"];
                $ruc = $fila["ruc"];
                $razon = $fila["razon"];
                $nombre = $fila["nombre"];
                $obligado = $fila["obligado"];
                $establecimiento = $fila["establecimiento"];
                $punto_emision = $fila["punto_emision"];
                $direccion = $fila["direccion"];
            }
        }
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
     <!-- JavaScript Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <title>Facturas</title>
</head>
<body>
    <div class="container-fluid">
    <?php 
            include './templates/header.php'
        ?>
    </div>
    <div class="container-fluid p-5">
        <form method="POST">
        <div class="row justify-content-start mb-4">
            <div class="col-3">
                <h5>Datos de la empresa</h5>
            </div>
            <div class="col-3">
                <div class="row">
                    <label for="dropBusiness" class="col-sm-4 col-form-label">Empresa</label>
                    <div class="col-sm-8">
                        <select name="dropBusiness" id="dropBusiness" class="form-control">
                            <option value="0">Seleccione</option>
                            <?php 
                                $sql1 = "SELECT `id`, `name` FROM business";
                                $resultado1=$conex->query($sql1);
                                while($fila1 = $resultado1->fetch_array()){
                                    $id1 = $fila1["id"]; 
                                    $nombre1 = $fila1["name"];
                                    echo "<option value='$id1'>$nombre1</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                
            </div>
            <div class="col-3">
                
                <button class="btn btn-primary">Buscar</button>
            </div>
        </div>
        </form>
        <div class="container">
            <form action="" method="POST">
            <div class="row">
                <div class="col">
                    <div class="form-group"><label for="">Ruc</label><input type="text" name="ruc" id="ruc" class="form-control"></div>
                </div>
                <div class="col">
                    <div class="form-group"><label for="">Razón Social</label><input type="text" name="razon" id="razon" class="form-control"></div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group"><label for="">Nombre Comercial</label><input type="text" name="nombre" id="nombre" class="form-control"></div>
                </div>
                <div class="col">
                    <div class="form-group"><label for="">Obligado Contabilidad</label>
                    <select name="obligado" id="obligado" class="form-control">
                        <option value="NO">NO</option>
                        <option value="SI">SI</option>
                    </select></div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group"><label for="">Establecimiento</label><input type="text" name="establecimiento" id="establecimiento" class="form-control"></div>
                </div>
                <div class="col">
                    <div class="form-group"><label for="">Punto Emisión</label><input type="text" name="punto_emision" id="punto_emision" class="form-control"></div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group"><label for="">Dirección</label><input type="text" name="direccion" id="direccion" class="form-control"></div>
                </div>

            </div>
            <div class="row">
                <input type="hidden" name="business" id="business">
                <input type="hidden" name="empresa" id="empresa">
            </div>
            <div class="row justify-content-end mt-3">
                <div class="col-6">
                    <button class="btn btn-primary" id="btnGuardar">Guardar</button>
                </div>
            </div>
            </form>
        </div>
    </div>
     <script>
        let dropBusiness = document.getElementById("dropBusiness");
        dropBusiness.onchange = function(event){
            let value = event.target.value;
            document.getElementById("btnGuardar").disabled = true ;
        }
        let inputEmpresa = document.getElementById("empresa");
        let inputBusiness = document.getElementById("business");

        /* */
        let inputRuc = document.getElementById("ruc");
        let inputRazon = document.getElementById("razon");
        let inputNombre = document.getElementById("nombre");
        let inputObligado = document.getElementById("obligado");
        let inputEstablecimiento = document.getElementById("establecimiento");
        let inputPuntoEmision = document.getElementById("punto_emision");
        let inputDireccion = document.getElementById("direccion");
        <?php
            if($dropBusiness){
                echo "dropBusiness.value = $dropBusiness;";
                echo "inputBusiness.value = $dropBusiness;";
            }
            if($empresa){
                echo "inputEmpresa.value = '$empresa';";
                echo "inputRuc.value = '$ruc';";
                echo "inputRazon.value = '$razon';";
                echo "inputNombre.value = '$nombre';";
                echo "inputObligado.value = '$obligado';";
                echo "inputEstablecimiento.value = '$establecimiento';";
                echo "inputPuntoEmision.value = '$punto_emision';";
                echo "inputDireccion.value = '$direccion';";
                
            } 
        ?>
        document.getElementById("btnGuardar").disabled = inputBusiness.value == 0 || inputBusiness.value == null ;
     </script>                           
</body>
</html>