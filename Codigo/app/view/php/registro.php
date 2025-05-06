<?php
require_once(__DIR__ . '/../../../rutas.php');
require_once(CONTROLLER . 'UsuarioController.php'); 
require_once(MODEL . 'Usuario.php'); 
session_start();

$usuarioController = new UsuarioController();

// Comprobar si el formulario ha sido enviado
if (isset($_POST['formCreate']) && $_POST['formCreate'] == 'crearUsuario') {
    // Verificar que se han recibido los datos correctamente
    if (isset($_POST["nombre_usuario"], $_POST["correo"], $_POST["contraseña"])) {
        
        // Sanitize y asignar las variables
        $nombreUsuario = htmlspecialchars($_POST["nombre_usuario"]);
        $correo = htmlspecialchars($_POST["correo"]);
        $contraseña = $_POST["contraseña"];

        // Crear el usuario
        $usuarioController->crearUsuario($nombreUsuario, $correo, $contraseña);


        // Realizar el login automáticamente (si es necesario)
        $usuario = $usuarioController->getUserByName($nombreUsuario);

        if ($usuario) {
            $_SESSION['nombre_usuario'] = $usuario->getNombreUsuario();
            header("Location: inicio.php"); // Redirige a la página principal después de registrar
            exit();
        }

    }
}
?>
