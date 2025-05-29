<?php

require_once(__DIR__ . '/../../rutas.php');
require_once(CONFIG . 'dbConnection.php');
require_once(MODEL . 'Usuario.php');

class UsuarioController
{
    public function getUserByName($nombre_usuario)
    {
        return Usuario::getUserByName($nombre_usuario);
    }

    public function getUserByEmail($email)
    {
        return Usuario::getUserByEmail($email);
    }

    public function getUserById($id_usuario)
    {
        return Usuario::getUserById($id_usuario);
    }

    public function getAllUsers()
    {
        return Usuario::getAllUsers();
    }

    public function crearUsuario($nombre_usuario, $email, $contraseña)
    {

        $nuevoUsuario = new Usuario();
        $nuevoUsuario->setNombreUsuario($nombre_usuario);
        $nuevoUsuario->setEmail($email);

        $nuevoUsuario->setContraseña(password_hash($contraseña, PASSWORD_DEFAULT));

        $nuevoUsuario->create();
        return null;
    }

    public function actualizarUsuario($id_usuario, $nombre_usuario, $email, $contraseña = null)
    {
        $usuario = Usuario::getUserById($id_usuario);
        if (!$usuario) {
            return "Usuario no encontrado.";
        }

        if ($usuario->getNombreUsuario() != $nombre_usuario && Usuario::nombreUsuarioExistente($nombre_usuario)) {
            return "El nombre de usuario ya está en uso.";
        }

        if ($usuario->getEmail() != $email && Usuario::emailExistente($email)) {
            return "El email ya está registrado.";
        }

        $usuario->setNombreUsuario($nombre_usuario);
        $usuario->setEmail($email);

        if ($contraseña !== null) {
            $usuario->setContraseña(password_hash($contraseña, PASSWORD_DEFAULT));
        }
        return $usuario->update();
    }

    public function eliminarUsuario($id_usuario)
    {
        $usuario = Usuario::getUserById($id_usuario);
        if (!$usuario) {
            return "Usuario no encontrado.";
        }
        return $usuario->delete();
    }

    public function updateAvatar($nombre_usuario, $avatar)
    {
        try {
            $usuario = $this->getUserByName($nombre_usuario);

            if (!$usuario) {
                return false;
            }

            $usuario->setAvatar($avatar);
            return $usuario->update();
        } catch (Exception $e) {
            return false;
        }
    }
}
