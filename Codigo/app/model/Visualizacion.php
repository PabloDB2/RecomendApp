<?php
require_once(__DIR__ . '/../../rutas.php');
require_once(CONFIG . 'dbConnection.php');

class Visualizacion {
    private $id_visualizacion;
    private $id_usuario;
    private $api_id;
    private $categoria;
    private $fecha;
    
    public function getIdVisualizacion() {
        return $this->id_visualizacion;
    }
    
    public function getIdUsuario() {
        return $this->id_usuario;
    }
    
    public function getApiId() {
        return $this->api_id;
    }
    
    public function getCategoria() {
        return $this->categoria;
    }
    
    public function getFecha() {
        return $this->fecha;
    }
    
    public function setIdVisualizacion($id_visualizacion) {
        $this->id_visualizacion = $id_visualizacion;
    }
    
    public function setIdUsuario($id_usuario) {
        $this->id_usuario = $id_usuario;
    }
    
    public function setApiId($api_id) {
        $this->api_id = $api_id;
    }
    
    public function setCategoria($categoria) {
        $this->categoria = $categoria;
    }
    
    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }
    
    public function create() {
        try {
            if (empty($this->id_usuario) || empty($this->api_id) || empty($this->categoria)) {
                return null;
            }
            
            $conn = getDBConnection();
            $sentencia = $conn->prepare("INSERT INTO visualizaciones (id_usuario, api_id, categoria, fecha) VALUES (?, ?, ?, NOW())");
            $sentencia->bindParam(1, $this->id_usuario);
            $sentencia->bindParam(2, $this->api_id);
            $sentencia->bindParam(3, $this->categoria);
            $sentencia->execute();
            $this->id_visualizacion = $conn->lastInsertId();
            return $this->id_visualizacion;
        } catch (PDOException $e) {
            error_log("Error al registrar la visualización: " . $e->getMessage());
            return null;
        }
    }
    
    public function delete() {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("DELETE FROM visualizaciones WHERE id_visualizacion = ?");
            $sentencia->bindParam(1, $this->id_visualizacion);
            return $sentencia->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar la visualización: " . $e->getMessage());
            return false;
        }
    }
    
    public static function getByUsuarioYContenido($id_usuario, $api_id, $categoria) {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT * FROM visualizaciones WHERE id_usuario = ? AND api_id = ? AND categoria = ?");
            $sentencia->bindParam(1, $id_usuario);
            $sentencia->bindParam(2, $api_id);
            $sentencia->bindParam(3, $categoria);
            $sentencia->execute();
            $result = $sentencia->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $visualizacion = new Visualizacion();
                $visualizacion->setIdVisualizacion($result['id_visualizacion']);
                $visualizacion->setIdUsuario($result['id_usuario']);
                $visualizacion->setApiId($result['api_id']);
                $visualizacion->setCategoria($result['categoria']);
                $visualizacion->setFecha($result['fecha']);
                return $visualizacion;
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error al obtener la visualización: " . $e->getMessage());
            return null;
        }
    }
    
    public static function getVisualizacionesByUsuarioYCategoria($id_usuario, $categoria) {
        try {
            $conn = getDBConnection();
            $sentencia = $conn->prepare("SELECT * FROM visualizaciones WHERE id_usuario = ? AND categoria = ? ORDER BY fecha DESC");
            $sentencia->bindParam(1, $id_usuario);
            $sentencia->bindParam(2, $categoria);
            $sentencia->execute();
            return $sentencia->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener las visualizaciones del usuario por categoría: " . $e->getMessage());
            return [];
        }
    }
    
    public static function countByUsuario($id_usuario, $categoria = null) {
        try {
            $conn = getDBConnection();
            
            if ($categoria) {
                $sentencia = $conn->prepare("SELECT COUNT(*) FROM visualizaciones WHERE id_usuario = ? AND categoria = ?");
                $sentencia->bindParam(1, $id_usuario);
                $sentencia->bindParam(2, $categoria);
            } else {
                $sentencia = $conn->prepare("SELECT COUNT(*) FROM visualizaciones WHERE id_usuario = ?");
                $sentencia->bindParam(1, $id_usuario);
            }
            
            $sentencia->execute();
            return $sentencia->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error al contar visualizaciones: " . $e->getMessage());
            return 0;
        }
    }
}
?>
