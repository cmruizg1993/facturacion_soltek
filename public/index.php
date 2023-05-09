<?php 
    include '../login.php'
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación</title>

    <!-- CSS only -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
</head>
<body style="background: #f0f0f0;">

    <div class="container">
        <div class="row justify-content-center p-5">
            <div class="col-md-4 bg-white p-5">
                <form  method="post">
                <h1 class="mb-2">Iniciar Sesión</h1>
                <?php 
                    if(isset($mensajes)){
                        foreach($mensajes as $m){
                            $html = "<div class='alert alert-danger' role='alert'>$m</div>";
                            echo $html;
                        }
                    }
                ?>
                <div class="form-group mt-2">
                    <label for="">Usuario</label>
                    <input type="text" name="usuario" placeholder="Usuario" class="form-control" value="<?php echo isset($_POST["usuario"]) ? ($_POST["usuario"]): "";?>">
                </div>
                <div class="form-group mt-2">
                    <label for="">Contraseña</label>
                    <input type="password"  name="clave" placeholder="Contraseña" class="form-control">
                </div>
                <div class="form-group m-4 d-flex justify-content-center">
                    <button class="btn btn-block btn-primary">Enviar Credenciales</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>