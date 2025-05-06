<?php

require_once(__DIR__ . '/../../rutas.php');
require_once(CONFIG . 'dbConnection.php'); 

class UsuarioController {
    
    public function getUserByName($nombre_usuario) {
        return Usuario::getUserByName($nombre_usuario);
    }

    public function getAllUsers() {
        return Usuario::getAllUsers();
    }

    // Crear un nuevo usuario
    public function crearUsuario($nombre_usuario, $email, $contraseña) {  // Cambié 'correo' a 'email'
        // Verificar si el nombre de usuario ya existe
        if (Usuario::nombreUsuarioExistente($nombre_usuario)) {
            echo "<p>El nombre de usuario ya está en uso.</p>";
            return;
        }
    
        // Verificar si el correo ya está registrado
        if (Usuario::emailExistente($email)) {  // Cambié 'correo' a 'email'
            echo "<p>El correo ya está registrado.</p>";
            return;
        }
    
        // Crear una instancia de Usuario
        $nuevoUsuario = new Usuario();
        $nuevoUsuario->setNombreUsuario($nombre_usuario);
        $nuevoUsuario->setEmail($email);  // Cambié 'correo' a 'email'
        $nuevoUsuario->setContraseña(password_hash($contraseña, PASSWORD_DEFAULT));  // Asegúrate de hashear la contraseña
    
        // Guardar el usuario en la base de datos
        $nuevoUsuario->create();
    }
}

?>
