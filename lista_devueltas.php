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
    $empresa ;
    if($method == "POST"){
        $empresa = isset($_POST['empresa']) ? $_POST['empresa']: null;
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
                <h5>Listado de facturas</h5>
            </div>
            <div class="col-3">
                <div class="row">
                    <label for="empresa" class="col-sm-4 col-form-label">Empresa</label>
                    <div class="col-sm-8">
                        <select name="empresa" id="" class="form-control">
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
            <table class="table">
                <thead>
                <tr>
                    <th>Acciones</th>
                    <th>#</th>
                    <th>Secuencial</th>
                    <th>Fecha</th>
                    <th>Total</th>
                </tr>
                </thead>
                
                <tbody>
                    <?php 
                    if($method == "POST" && $empresa){
                        $counter = 1;
                        
                        $sql2 = "SELECT id, invoice_no, created_at, final_total FROM transactions LEFT JOIN  WHERE business_id = $empresa";
                        $resultado2=$conex->query($sql2);
                        while($fila2 = $resultado2->fetch_array()){
                            $id = $fila2['id'];
                            $secuencial = $fila2['invoice_no'];
                            $fecha = ($fila2['created_at']);
                            $total = $fila2['final_total'];
                            echo "<tr>";
                            echo "<td><a href='./acciones/enviar-sri.php?id=$id'>Enviar SRI</a></td>";
                            echo "<td>$counter</td>";
                            echo "<td>$secuencial</td>";
                            echo "<td>$fecha</td>";
                            echo "<td>$total</td>";
                            echo "</tr>";
                            $counter++;
                        }
                        $transactions = [];
                    }
                    
                    ?>
                </tbody>
            </table>
        </div>
    </div>
   
</body>
</html>