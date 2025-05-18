<?php
require_once(__DIR__ . '/../../rutas.php');
require_once(CONFIG . 'dbConnection.php');

class Resena
{
    private $id_reseña;
    private $id_usuario;
    private $api_id;
    private $categoria;
    private $texto;
    private $puntuacion;
    private $likes;
    private $fecha;

    public function getIdResena() { return $this->id_reseña; }
    public function getIdUsuario() { return $this->id_usuario; }
    public function getApiId() { return $this->api_id; }
    public function getCategoria() { return $this->categoria; }
    public function getTexto() { return $this->texto; }
    public function getPuntuacion() { return $this->puntuacion; }
    public function getLikes() { return $this->likes; }
    public function getFecha() { return $this->fecha; }

    public function setIdResena($id_reseña) { $this->id_reseña = $id_reseña; }
    public function setIdUsuario($id_usuario) { $this->id_usuario = $id_usuario; }
    public function setApiId($api_id) { $this->api_id = $api_id; }
    public function setCategoria($categoria) { $this->categoria = $categoria; }
    public function setTexto($texto) { $this->texto = $texto; }
    public function setPuntuacion($puntuacion) { $this->puntuacion = $puntuacion; }
    public function setLikes($likes) { $this->likes = $likes; }
    public function setFecha($fecha) { $this->fecha = $fecha; }

    public function create()
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("INSERT INTO reseñas (id_usuario, api_id, categoria, texto, puntuacion, fecha) VALUES (?, ?, ?, ?, ?, NOW())");
            $sentencia->bindParam(1, $this->id_usuario);
            $sentencia->bindParam(2, $this->api_id);
            $sentencia->bindParam(3, $this->categoria);
            $sentencia->bindParam(4, $this->texto);
            $sentencia->bindParam(5, $this->puntuacion);
            return $sentencia->execute();
        } catch (PDOException $e) {
            echo "Error al crear la reseña: " . $e->getMessage();
            return false;
        }
    }

    public function update()
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("UPDATE reseñas SET texto = ?, puntuacion = ? WHERE id_reseña = ?");
            $sentencia->bindParam(1, $this->texto);
            $sentencia->bindParam(2, $this->puntuacion);
            $sentencia->bindParam(3, $this->id_reseña);
            return $sentencia->execute();
        } catch (PDOException $e) {
            echo "Error al actualizar la reseña: " . $e->getMessage();
            return false;
        }
    }

    public function delete()
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("DELETE FROM reseñas WHERE id_reseña = ?");
            $sentencia->bindParam(1, $this->id_reseña);
            return $sentencia->execute();
        } catch (PDOException $e) {
            echo "Error al eliminar la reseña: " . $e->getMessage();
            return false;
        }
    }

    public static function getById($id_reseña)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT * FROM reseñas WHERE id_reseña = ?");
            $sentencia->bindParam(1, $id_reseña);
            $sentencia->execute();
            $result = $sentencia->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $resena = new Resena();
                $resena->setIdResena($result['id_reseña']);
                $resena->setIdUsuario($result['id_usuario']);
                $resena->setApiId($result['api_id']);
                $resena->setCategoria($result['categoria']);
                $resena->setTexto($result['texto']);
                $resena->setPuntuacion($result['puntuacion']);
                $resena->setLikes($result['likes']);
                $resena->setFecha($result['fecha']);
                return $resena;
            }
            return null;
        } catch (PDOException $e) {
            echo "Error al obtener la reseña: " . $e->getMessage();
            return null;
        }
    }

    public static function getByApiId($api_id, $categoria)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT r.*, u.nombre_usuario, u.avatar FROM reseñas r 
                                        INNER JOIN usuarios u ON r.id_usuario = u.id_usuario 
                                        WHERE r.api_id = ? AND r.categoria = ? 
                                        ORDER BY r.fecha DESC");
            $sentencia->bindParam(1, $api_id);
            $sentencia->bindParam(2, $categoria);
            $sentencia->execute();
            return $sentencia->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al obtener las reseñas: " . $e->getMessage();
            return [];
        }
    }

    public static function getByUsuario($id_usuario)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT * FROM reseñas WHERE id_usuario = ? ORDER BY fecha DESC");
            $sentencia->bindParam(1, $id_usuario);
            $sentencia->execute();
            return $sentencia->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error al obtener las reseñas del usuario: " . $e->getMessage();
            return [];
        }
    }

    public static function usuarioTieneResena($id_usuario, $api_id, $categoria)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT COUNT(*) as count FROM reseñas WHERE id_usuario = ? AND api_id = ? AND categoria = ?");
            $sentencia->bindParam(1, $id_usuario);
            $sentencia->bindParam(2, $api_id);
            $sentencia->bindParam(3, $categoria);
            $sentencia->execute();
            $result = $sentencia->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            echo "Error al verificar si el usuario tiene reseña: " . $e->getMessage();
            return false;
        }
    }

    public static function getResenaUsuario($id_usuario, $api_id, $categoria)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT * FROM reseñas WHERE id_usuario = ? AND api_id = ? AND categoria = ?");
            $sentencia->bindParam(1, $id_usuario);
            $sentencia->bindParam(2, $api_id);
            $sentencia->bindParam(3, $categoria);
            $sentencia->execute();
            $result = $sentencia->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $resena = new Resena();
                $resena->setIdResena($result['id_reseña']);
                $resena->setIdUsuario($result['id_usuario']);
                $resena->setApiId($result['api_id']);
                $resena->setCategoria($result['categoria']);
                $resena->setTexto($result['texto']);
                $resena->setPuntuacion($result['puntuacion']);
                $resena->setLikes($result['likes']);
                $resena->setFecha($result['fecha']);
                return $resena;
            }
            return null;
        } catch (PDOException $e) {
            echo "Error al obtener la reseña del usuario: " . $e->getMessage();
            return null;
        }
    }

    public function incrementLikes()
    {
        try {
            $this->likes++;
            $conn = getDBConnection();
            $sentencia = $conn->prepare("UPDATE reseñas SET likes = ? WHERE id_reseña = ?");
            $sentencia->bindParam(1, $this->likes);
            $sentencia->bindParam(2, $this->id_reseña);
            return $sentencia->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar likes: " . $e->getMessage());
            return false;
        }
    }

    public static function usuarioDioLike($id_usuario, $id_resena)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT 1 FROM likes_resena WHERE id_usuario = ? AND id_resena = ?");
            $sentencia->bindParam(1, $id_usuario);
            $sentencia->bindParam(2, $id_resena);
            $sentencia->execute();
            return $sentencia->fetch() ? true : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function registrarLike($id_usuario, $id_resena)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("INSERT IGNORE INTO likes_resena (id_usuario, id_resena) VALUES (?, ?)");
            $sentencia->bindParam(1, $id_usuario);
            $sentencia->bindParam(2, $id_resena);
            return $sentencia->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function quitarLike($id_usuario, $id_resena)
    {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("DELETE FROM likes_resena WHERE id_usuario = ? AND id_resena = ?");
            $sentencia->bindParam(1, $id_usuario);
            $sentencia->bindParam(2, $id_resena);
            return $sentencia->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
