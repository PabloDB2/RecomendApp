<?php

require_once(__DIR__ . '/../../rutas.php');
require_once(CONFIG . 'dbConnection.php'); 
require_once(MODEL . 'Usuario.php');

class UsuarioController {
    
    public function getUserByName($nombre_usuario) {
        return Usuario::getUserByName($nombre_usuario);
    }

    public function getUserByEmail($email) {
        return Usuario::getUserByEmail($email);
    }
    
    public function getUserById($id_usuario) {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
            $sentencia->bindParam(1, $id_usuario);
            $sentencia->execute();
            $result = $sentencia->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $usuario = new Usuario();
                $usuario->setIdUsuario($result['id_usuario']);
                $usuario->setNombreUsuario($result['nombre_usuario']);
                $usuario->setEmail($result['email']);
                $usuario->setContraseña($result['contraseña']); 
                return $usuario;
            }

            return null;
        } catch (PDOException $e) {
            echo "Error al obtener el usuario por ID: " . $e->getMessage();
            return null;
        }
    }

    public function getAllUsers() {
        return Usuario::getAllUsers();
    }

    public function crearUsuario($nombre_usuario, $email, $contraseña) {
        if (Usuario::nombreUsuarioExistente($nombre_usuario)) {
            return "El nombre de usuario ya está en uso.";
        }

        if (Usuario::emailExistente($email)) {
            return "El correo electrónico ya está registrado.";
        }

        $nuevoUsuario = new Usuario();
        $nuevoUsuario->setNombreUsuario($nombre_usuario);
        $nuevoUsuario->setEmail($email);

        $nuevoUsuario->setContraseña(password_hash($contraseña, PASSWORD_DEFAULT));

        $nuevoUsuario->create();
        return null; 
    }
}

?>
