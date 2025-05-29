<?php
require_once(__DIR__ . '/../../rutas.php');
require_once(CONFIG . 'dbConnection.php');

class Lista
{
    private $id_lista;
    private $id_usuario;
    private $api_id;
    private $categoria;
    public function getIdLista()
    {
        return $this->id_lista;
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
    public function setIdLista($id_lista)
    {
        $this->id_lista = $id_lista;
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
            $sentencia = $conn->prepare("INSERT INTO lista (id_usuario, api_id, categoria) VALUES (?, ?, ?)");
            $sentencia->bindParam(1, $this->id_usuario);
            $sentencia->bindParam(2, $this->api_id);
            $sentencia->bindParam(3, $this->categoria);
            $sentencia->execute();
            $this->id_lista = $conn->lastInsertId();
            return $this->id_lista;
        } catch (PDOException $e) {
            error_log("Error al añadir a la lista: " . $e->getMessage());
            return null;
        }
    }

    public function delete()
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("DELETE FROM lista WHERE id_usuario = ? AND api_id = ? AND categoria = ?");
            $sentencia->bindParam(1, $this->id_usuario);
            $sentencia->bindParam(2, $this->api_id);
            $sentencia->bindParam(3, $this->categoria);
            return $sentencia->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar de la lista: " . $e->getMessage());
            return false;
        }
    }

    public static function getByUsuarioYContenido($id_usuario, $api_id, $categoria)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT * FROM lista WHERE id_usuario = ? AND api_id = ? AND categoria = ?");
            $sentencia->bindParam(1, $id_usuario);
            $sentencia->bindParam(2, $api_id);
            $sentencia->bindParam(3, $categoria);
            $sentencia->execute();
            $result = $sentencia->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $lista = new Lista();
                $lista->setIdLista($result['id_lista']);
                $lista->setIdUsuario($result['id_usuario']);
                $lista->setApiId($result['api_id']);
                $lista->setCategoria($result['categoria']);
                return $lista;
            }

            return null;
        } catch (PDOException $e) {
            error_log("Error al obtener elemento de la lista: " . $e->getMessage());
            return null;
        }
    }

    public static function getListaByUsuarioYCategoria($id_usuario, $categoria)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT * FROM lista WHERE id_usuario = ? AND categoria = ? ORDER BY id_lista DESC");
            $sentencia->bindParam(1, $id_usuario);
            $sentencia->bindParam(2, $categoria);
            $sentencia->execute();
            return $sentencia->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener la lista del usuario por categoría: " . $e->getMessage());
            return [];
        }
    }
    public static function countByUsuario($id_usuario, $categoria = null)
    {
        try {
            $conn = getDBConnection();

            if ($categoria) {
                $sentencia = $conn->prepare("SELECT COUNT(*) FROM lista WHERE id_usuario = ? AND categoria = ?");
                $sentencia->bindParam(1, $id_usuario);
                $sentencia->bindParam(2, $categoria);
            } else {
                $sentencia = $conn->prepare("SELECT COUNT(*) FROM lista WHERE id_usuario = ?");
                $sentencia->bindParam(1, $id_usuario);
            }

            $sentencia->execute();
            return $sentencia->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error al contar elementos de la lista: " . $e->getMessage());
            return 0;
        }
    }
}
