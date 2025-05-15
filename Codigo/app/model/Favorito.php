<?php
require_once(__DIR__ . '/../../rutas.php');
require_once(CONFIG . 'dbConnection.php');

class Favorito
{
    private $id_favorito;
    private $id_usuario;
    private $api_id;
    private $categoria;

    public function getIdFavorito()
    {
        return $this->id_favorito;
    }

    public function getIdUsuario()
    {
        return $this->id_usuario;
    }

    public function getApiId()
    {
        return $this->api_id;
    }

    public function getCategoria()
    {
        return $this->categoria;
    }
    public function setIdFavorito($id_favorito)
    {
        $this->id_favorito = $id_favorito;
    }

    public function setIdUsuario($id_usuario)
    {
        $this->id_usuario = $id_usuario;
    }

    public function setApiId($api_id)
    {
        $this->api_id = $api_id;
    }

    public function setCategoria($categoria)
    {
        $this->categoria = $categoria;
    }

    public function create()
    {
        try {
            if (empty($this->id_usuario) || empty($this->api_id) || empty($this->categoria)) {
                return null;
            }

            $conn = getDBConnection();
            $sentencia = $conn->prepare("INSERT INTO favoritos (id_usuario, api_id, categoria) VALUES (?, ?, ?)");
            $sentencia->bindParam(1, $this->id_usuario);
            $sentencia->bindParam(2, $this->api_id);
            $sentencia->bindParam(3, $this->categoria);
            $sentencia->execute();
            $this->id_favorito = $conn->lastInsertId();
            return $this->id_favorito;
        } catch (PDOException $e) {
            error_log("Error al registrar el favorito: " . $e->getMessage());
            return null;
        }
    }

    public function delete()
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("DELETE FROM favoritos WHERE id_usuario = ? AND api_id = ? AND categoria = ?");
            $sentencia->bindParam(1, $this->id_usuario);
            $sentencia->bindParam(2, $this->api_id);
            $sentencia->bindParam(3, $this->categoria);
            return $sentencia->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar el favorito: " . $e->getMessage());
            return false;
        }
    }

    public static function getByUsuarioYContenido($id_usuario, $api_id, $categoria)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT * FROM favoritos WHERE id_usuario = ? AND api_id = ? AND categoria = ?");
            $sentencia->bindParam(1, $id_usuario);
            $sentencia->bindParam(2, $api_id);
            $sentencia->bindParam(3, $categoria);
            $sentencia->execute();
            $result = $sentencia->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $favorito = new Favorito();
                $favorito->setIdFavorito($result['id_favorito']);
                $favorito->setIdUsuario($result['id_usuario']);
                $favorito->setApiId($result['api_id']);
                $favorito->setCategoria($result['categoria']);
                return $favorito;
            }

            return null;
        } catch (PDOException $e) {
            error_log("Error al obtener el favorito: " . $e->getMessage());
            return null;
        }
    }

    public static function getFavoritosByUsuario($id_usuario)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT * FROM favoritos WHERE id_usuario = ? ORDER BY id_favorito DESC");
            $sentencia->bindParam(1, $id_usuario);
            $sentencia->execute();
            return $sentencia->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener los favoritos del usuario: " . $e->getMessage());
            return [];
        }
    }

    public static function getFavoritosByUsuarioYCategoria($id_usuario, $categoria)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT * FROM favoritos WHERE id_usuario = ? AND categoria = ? ORDER BY id_favorito DESC");
            $sentencia->bindParam(1, $id_usuario);
            $sentencia->bindParam(2, $categoria);
            $sentencia->execute();
            return $sentencia->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener los favoritos del usuario por categoría: " . $e->getMessage());
            return [];
        }
    }

    public static function deleteById($id_favorito)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("DELETE FROM favoritos WHERE id_favorito = ?");
            $sentencia->bindParam(1, $id_favorito);
            return $sentencia->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar el favorito por ID: " . $e->getMessage());
            return false;
        }
    }

    public static function countByUsuario($id_usuario, $categoria = null)
    {
        try {
            $conn = getDBConnection();

            if ($categoria) {
                $sentencia = $conn->prepare("SELECT COUNT(*) FROM favoritos WHERE id_usuario = ? AND categoria = ?");
                $sentencia->bindParam(1, $id_usuario);
                $sentencia->bindParam(2, $categoria);
            } else {
                $sentencia = $conn->prepare("SELECT COUNT(*) FROM favoritos WHERE id_usuario = ?");
                $sentencia->bindParam(1, $id_usuario);
            }

            $sentencia->execute();
            return $sentencia->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error al contar favoritos: " . $e->getMessage());
            return 0;
        }
    }
}
