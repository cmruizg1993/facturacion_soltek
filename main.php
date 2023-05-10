<?php
session_start();
if (empty($_SESSION["usuario"])) {
    session_destroy();
    header("Location: ./public/index.php");
    exit();
}
include './facturacion/connexion.php';
$conex = mysqli_connect($host, $user, $password);
mysqli_select_db($conex, $database);
//invoice_no->secuencial
$method = $_SERVER['REQUEST_METHOD'];
$checked = 'false';
$empresa = null;
$dropBusiness = 0;
$dropLocation = 0;
$filtro = '';
$business = null;
$ruta = isset($_GET['ruta']) ? $_GET['ruta']: '';


$dropBusiness = isset($_REQUEST['dropBusiness']) ? $_REQUEST['dropBusiness'] : 0;
$dropLocation = isset($_REQUEST['dropLocation']) ? $_REQUEST['dropLocation'] : 0;
$filtro = isset($_REQUEST['filtro']) ? $_REQUEST['filtro'] : '';

if($dropBusiness > 0){
    setcookie('dropBusiness', $dropBusiness);
    setcookie("dropLocation", "", time() - 3600);
}else{
    $dropBusiness = isset($_COOKIE['dropBusiness']) ? $_COOKIE['dropBusiness']:0;
    $dropLocation = isset($_COOKIE['dropLocation']) ? $_COOKIE['dropLocation']:0;
} 
if($dropLocation > 0)setcookie('dropLocation', $dropLocation);

if ($method == "POST") {
    $business = isset($_POST['business']) ? $_POST['business'] : null;
    $location = isset($_POST['location']) ? $_POST['location'] : null;
}

include_once('./guardarEmpresa.php');

$sucursales = [];
if ($dropBusiness > 0) {
    $sql = "SELECT * FROM `business_locations` WHERE business_id = $dropBusiness;";
    $resultado = $conex->query($sql);
    if($resultado) $sucursales = $resultado;
}
if($dropLocation){
    $sql = "SELECT * FROM fe_empresa WHERE business_id = $dropBusiness AND location_id = $dropLocation;";
    $resultado = $conex->query($sql);
    if ($resultado) {
        while ($fila = $resultado->fetch_array()) {
            $empresa = $fila["id"];
            $business = $fila["business_id"];
            $ruc = $fila["ruc"];
            $razon = $fila["razon"];
            $nombre = $fila["nombre"];
            $obligado = $fila["obligado"];
            $establecimiento = $fila["establecimiento"];
            $punto_emision = $fila["punto_emision"];
            $direccion = $fila["direccion"];
            $p12_password = $fila["p12_password"];
            $testing = $fila["testing"];
            $checked = $testing == 1 ? 'true': 'false';
            
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
        <form method="GET">
            <div class="d-none">
                <input type="hidden" name="ruta"
                    <?php 
                        echo 'value="'.$ruta.'"';
                    ?>
                >
            </div>
            <div class="row justify-content-start mb-4">
                
                <h6 class="mb-3">Filtro</h6>
                
                <div class="col-3">
                    <div class="row">
                        <label for="dropBusiness" class="col-sm-4 col-form-label">Empresa</label>
                        <div class="col-sm-8">
                            <select name="dropBusiness" id="dropBusiness" class="form-control" 
                                <?php echo "value='$dropBusiness'"; ?>
                            >
                                <option value="0">Seleccione</option>
                                <?php
                                $sql1 = "SELECT `id`, `name` FROM business";
                                $resultado1 = $conex->query($sql1);
                                while ($fila1 = $resultado1->fetch_array()) {
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
                    <div class="row">
                        <label for="dropLocation" class="col-sm-4 col-form-label">Sucursal</label>
                        <div class="col-sm-8">
                            <select name="dropLocation" id="dropLocation" class="form-control" 
                            <?php echo "value='$dropLocation'"; ?>
                            >
                                <option value="0">Seleccione</option>
                                <?php
                                if($dropBusiness){
                                    while ($sucursal = $sucursales->fetch_array()) {
                                        $id2 = $sucursal["id"];
                                        $nombre2 = $sucursal["name"];
                                        echo "<option value='$id2'>$nombre2</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <?php 
                    if($ruta != 'empresa'){
                        ?>
                        <div class="col-3">
                    <div class="row">
                        <label for="filtro" class="col-sm-4 col-form-label">Filtro</label>
                        <div class="col-sm-8">
                            <input name="filtro" id="filtro" class="form-control" />
                        </div>
                    </div>
                </div>
                        <?php
                    }
                ?>
                <div class="col-3">

                    <button class="btn btn-primary" id="btnBuscar">Buscar</button>
                </div>
            </div>
        </form>
        <div class="container">
            <?php             
                if($ruta == 'empresa'){
                    include './formularioEmpresa.php';
                }elseif($ruta){
                    echo "<h4 class='text-primary'>FACTURAS $ruta</h4>";
                    echo '<div class="table-responsive" style="max-height: 550px;">';
                    include './tabla_facturas.php';
                    imprimirTabla($ruta, $conex, $dropBusiness, $dropLocation, $filtro);
                    echo '</div>';;
                }                
            ?>
        </div>
    </div>
    <script>
        let dropBusiness = document.getElementById("dropBusiness");
        let dropLocation = document.getElementById("dropLocation");

        dropBusiness.onchange = function(event) {
            let value = event.target.value;
            dropLocation.value = null;
            document.getElementById("btnBuscar").click();            
        }
        
        dropLocation.onchange = function(event) {
            let value = event.target.value;
            document.getElementById("btnBuscar").click();            
        }
        let inputEmpresa = document.getElementById("empresa");
        let inputBusiness = document.getElementById("business");
        let inputBusinessLocation = document.getElementById("location");

        /* */
        let inputRuc = document.getElementById("ruc");
        let inputRazon = document.getElementById("razon");
        let inputNombre = document.getElementById("nombre");
        let inputObligado = document.getElementById("obligado");
        let inputEstablecimiento = document.getElementById("establecimiento");
        let inputPuntoEmision = document.getElementById("punto_emision");
        let inputDireccion = document.getElementById("direccion");
        let inputPassword = document.getElementById("p12_password");
        let checkTesting = document.getElementById("testing");
        let inputFiltro = document.getElementById("filtro");
        <?php
        
        if ($dropBusiness) {
            echo "dropBusiness.value = $dropBusiness;\n";
            echo "if(inputBusiness)inputBusiness.value = $dropBusiness;\n";
        }
        if ($dropLocation) {
            echo "dropLocation.value = $dropLocation;\n";
            echo "if(inputBusinessLocation)inputBusinessLocation.value = $dropLocation;\n";
        }
        if ($ruta == 'empresa' && $empresa) { 
            
            echo "inputEmpresa.value = '$empresa';\n";
            echo "inputRuc.value = '$ruc';";
            echo "inputRazon.value = '$razon';";
            echo "inputNombre.value = '$nombre';";
            echo "inputObligado.value = '$obligado';";
            echo "inputEstablecimiento.value = '$establecimiento';";
            echo "inputPuntoEmision.value = '$punto_emision';";
            echo "inputDireccion.value = '$direccion';";
            echo "inputPassword.value = '$p12_password';";
            echo "checkTesting.checked = " . $checked . ";";
        }elseif ($ruta != 'empresa' && !$empresa){
            echo "inputFiltro.value = '$filtro';";
        }
        ?>
        if(inputBusiness != null){
            document.getElementById("btnGuardar").disabled = inputBusiness.value == 0 || inputBusiness.value == null;
        }
        
    </script>
    <script>

    </script>
</body>

</html>