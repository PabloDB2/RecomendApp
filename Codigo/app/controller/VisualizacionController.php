<?php
require_once(__DIR__ . '/../../rutas.php');
require_once(CONFIG . 'dbConnection.php');
require_once(MODEL . 'Visualizacion.php');

class VisualizacionController
{

    public function marcarComoVisto($id_usuario, $api_id, $categoria)
    {
        try {
            if (empty($id_usuario) || empty($api_id) || empty($categoria)) {
                error_log("Parámetros inválidos en marcarComoVisto");
                return false;
            }

            $visualizacionExistente = Visualizacion::getByUsuarioYContenido($id_usuario, $api_id, $categoria);

            if ($visualizacionExistente) {
                return $visualizacionExistente->delete();
            }

            $nuevaVisualizacion = new Visualizacion();
            $nuevaVisualizacion->setIdUsuario($id_usuario);
            $nuevaVisualizacion->setApiId($api_id);
            $nuevaVisualizacion->setCategoria($categoria);

            $resultado = $nuevaVisualizacion->create();
            return $resultado ? true : false;
        } catch (Exception $e) {
            error_log("Error en marcarComoVisto: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerVisualizacionesPorUsuarioYCategoria($id_usuario, $categoria)
    {
        try {
            if (empty($id_usuario) || empty($categoria)) {
                error_log("Parámetros inválidos en obtenerVisualizacionesPorUsuarioYCategoria");
                return [];
            }

            return Visualizacion::getVisualizacionesByUsuarioYCategoria($id_usuario, $categoria);
        } catch (Exception $e) {
            error_log("Error en obtenerVisualizacionesPorUsuarioYCategoria: " . $e->getMessage());
            return [];
        }
    }

    public function esVisto($id_usuario, $api_id, $categoria)
    {
        try {
            if (empty($id_usuario) || empty($api_id) || empty($categoria)) {
                error_log("Parámetros inválidos en esVisto");
                return false;
            }

            $visualizacion = Visualizacion::getByUsuarioYContenido($id_usuario, $api_id, $categoria);
            return $visualizacion !== null;
        } catch (Exception $e) {
            error_log("Error en esVisto: " . $e->getMessage());
            return false;
        }
    }
    public function contarVisualizaciones($id_usuario, $categoria = null)
    {
        try {
            if (empty($id_usuario)) {
                error_log("ID de usuario inválido en contarVisualizaciones");
                return 0;
            }

            return Visualizacion::countByUsuario($id_usuario, $categoria);
        } catch (Exception $e) {
            error_log("Error en contarVisualizaciones: " . $e->getMessage());
            return 0;
        }
    }
}
