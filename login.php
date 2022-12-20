<?php 
    $method = $_SERVER['REQUEST_METHOD'];
    if($method == "POST"){
        
        
        $usuario = isset ($_POST["usuario"]) ? $_POST["usuario"]:null;
        $clave = isset ($_POST["clave"]) ? $_POST["clave"]:null;
        $mensajes = verificarCredenciales($usuario, $clave);
        
        if(count($mensajes) == 0){
            session_start();
            $_SESSION["usuario"] = $usuario;
            header("Location: ../lista_pendientes.php");
            die();
        }
    }else{
        session_start();
        if(isset($_SESSION["usuario"])){
            header("Location: ../lista_pendientes.php");
            exit();
        }
    }

    function verificarCredenciales($usuario, $clave ){
        $user = "soltek";
        $password = "rAE7jUGHY2X6DoB0";
        $mensajes = [];
        if($user != $usuario || $password != $clave){
            $mensajes[] = "Usuario o contraseña invalidos";
        }        
        return $mensajes;
    }
?>