<?php

require_once(__DIR__ . '/../../rutas.php');
require_once(CONFIG . 'dbConnection.php'); 
require_once(MODEL . 'Usuario.php');

class UsuarioController {
    
    public function getUserByName($nombre_usuario) {
        return Usuario::getUserByName($nombre_usuario);
    }

    public function getAllUsers() {
        return Usuario::getAllUsers();
    }

    // Crear un nuevo usuario
    public function crearUsuario($nombre_usuario, $email, $contraseña) {
        // Verificar si el nombre de usuario ya existe
        if (Usuario::nombreUsuarioExistente($nombre_usuario)) {
            echo "<p>El nombre de usuario ya está en uso.</p>";
            return;
        }
    
        // Verificar si el email ya está registrado
        if (Usuario::emailExistente($email)) {
            echo "<p>El correo ya está registrado.</p>";
            return;
        }
    
        // Crear una instancia de Usuario
        $nuevoUsuario = new Usuario();
        $nuevoUsuario->setNombreUsuario($nombre_usuario);
        $nuevoUsuario->setEmail($email);

        // Hashear la contraseña aquí (¡NO antes!)
        $nuevoUsuario->setContraseña(password_hash($contraseña, PASSWORD_DEFAULT));

        // Guardar el usuario en la base de datos
        $nuevoUsuario->create();
    }
}

?>
