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
    $empresa = isset($_COOKIE['empresa']) ? $_COOKIE['empresa']:null;    
    if($method == "POST"){
        $empresa = isset($_POST['empresa']) ? $_POST['empresa']: null;
        setcookie('empresa', $empresa);
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
                <h5>Listado de facturas RECHAZADAS</h5>
            </div>
            <div class="col-3">
                <div class="row">
                    <label for="empresa" class="col-sm-4 col-form-label">Empresa</label>
                    <div class="col-sm-8">
                        <select name="empresa" id="selectEmpresa" class="form-control">
                            <option value="0">Seleccione</option>
                            <?php 
                                $sql1 = "SELECT `id`, `name` FROM business";
                                $resultado1=$conex->query($sql1);
                                while($fila1 = $resultado1->fetch_array()){
                                    $id = $fila1["id"]; 
                                    $nombre = $fila1["name"];
                                    echo "<option value='$id'>$nombre</option>";
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
        
        <div class="table-responsive" style="max-height: 550px;">
            <?php 
                include './tabla_facturas.php';
                imprimirTabla('RECHAZADAS', $conex, $empresa);
            ?>
        </div>
    </div>
    <script>
        <?php
            
            if($empresa){
                echo "document.getElementById('selectEmpresa').value = '$empresa';";
            }
        ?>
        
    </script>
</body>
</html>