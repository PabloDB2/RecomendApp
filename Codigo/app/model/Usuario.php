<?php
require_once(__DIR__ . '/../../rutas.php');
require_once(CONFIG . 'dbConnection.php');

class Usuario
{
    private $nombre_usuario;
    private $email;
    private $contraseña;

    private $id_usuario;

    private $avatar;

    public function getIdUsuario()
    {
        return $this->id_usuario;
    }

    public function setIdUsuario($id_usuario)
    {
        $this->id_usuario = $id_usuario;
    }

    public function getNombreUsuario()
    {
        return $this->nombre_usuario;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getContraseña()
    {
        return $this->contraseña;
    }

    public function getAvatar()
    {
        return $this->avatar;
    }

    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }
    public function setNombreUsuario($nombre_usuario)
    {
        $this->nombre_usuario = $nombre_usuario;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setContraseña($contraseña)
    {
        $this->contraseña = $contraseña;
    }



    public function create()
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("INSERT INTO usuarios (nombre_usuario, email, contraseña) VALUES (?, ?, ?)");
            $sentencia->bindParam(1, $this->nombre_usuario);
            $sentencia->bindParam(2, $this->email);
            $sentencia->bindParam(3, $this->contraseña);
            $sentencia->execute();
        } catch (PDOException $e) {
            echo "Error al registrar el usuario: " . $e->getMessage();
        }
    }

    public static function getUserByName($nombre_usuario)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ?");
            $sentencia->bindParam(1, $nombre_usuario);
            $sentencia->execute();
            $result = $sentencia->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $usuario = new Usuario();
                $usuario->setIdUsuario($result['id_usuario']);
                $usuario->setNombreUsuario($result['nombre_usuario']);
                $usuario->setEmail($result['email']);
                $usuario->setContraseña($result['contraseña']);
                $usuario->setAvatar($result['avatar'] ?? 'default-avatar.jpg');

                return $usuario;
            }

            return null;
        } catch (PDOException $e) {
            echo "Error al obtener el usuario: " . $e->getMessage();
            return null;
        }
    }
    public static function getUserByEmail($email)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
            $sentencia->bindParam(1, $email);
            $sentencia->execute();
            $result = $sentencia->fetch(PDO::FETCH_ASSOC);

            if ($result) {

                $usuario = new Usuario();
                $usuario->setIdUsuario($result['id_usuario']);

                $usuario->setNombreUsuario($result['nombre_usuario']);
                $usuario->setEmail($result['email']);
                $usuario->setContraseña($result['contraseña']);
                $usuario->setAvatar($result['avatar'] ?? '1default-avatar.jpg');

                return $usuario;
            }

            return null;
        } catch (PDOException $e) {
            echo "Error al obtener el usuario por email: " . $e->getMessage();
            return null;
        }
    }

    public static function emailExistente($email)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
            $sentencia->bindParam(1, $email);
            $sentencia->execute();
            $result = $sentencia->fetch(PDO::FETCH_ASSOC);

            return $result ? true : false;
        } catch (PDOException $e) {
            echo "Error al comprobar email: " . $e->getMessage();
            return false;
        }
    }

    public static function nombreUsuarioExistente($nombre_usuario)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ?");
            $sentencia->bindParam(1, $nombre_usuario);
            $sentencia->execute();
            $result = $sentencia->fetch(PDO::FETCH_ASSOC);

            return $result ? true : false;
        } catch (PDOException $e) {
            echo "Error al comprobar nombre de usuario: " . $e->getMessage();
            return false;
        }
    }
    public static function getUserById($id_usuario)
    {
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
                $usuario->setAvatar($result['avatar'] ?? '1default-avatar.jpg');

                return $usuario;
            }

            return null;
        } catch (PDOException $e) {
            echo "Error al obtener el usuario por ID: " . $e->getMessage();
            return null;
        }
    }
    public static function getAllUsers()
    {
        try {
            $conn = getDBConnection();
            $query = $conn->query("SELECT * FROM usuarios");
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            echo "Error al ejecutar la query" . $e->getMessage();
            return [];
        }
    }

    public function update()
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("UPDATE usuarios SET nombre_usuario = ?, email = ?, contraseña = ?, avatar = ? WHERE id_usuario = ?");
            $sentencia->bindParam(1, $this->nombre_usuario);
            $sentencia->bindParam(2, $this->email);
            $sentencia->bindParam(3, $this->contraseña);
            $sentencia->bindParam(4, $this->avatar);
            $sentencia->bindParam(5, $this->id_usuario);
            return $sentencia->execute();
        } catch (PDOException $e) {
            echo "Error al actualizar el usuario: " . $e->getMessage();
            return false;
        }
    }

    public function delete()
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
            $sentencia->bindParam(1, $this->id_usuario);
            return $sentencia->execute();
        } catch (PDOException $e) {
            echo "Error al eliminar el usuario: " . $e->getMessage();
            return false;
        }
    }
}
