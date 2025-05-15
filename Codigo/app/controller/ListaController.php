<?php
require_once(__DIR__ . '/../../rutas.php');
require_once(CONFIG . 'dbConnection.php');
require_once(MODEL . 'Lista.php');

class ListaController
{

    public function añadirALista($id_usuario, $api_id, $categoria)
    {
        try {
            if (empty($id_usuario) || empty($api_id) || empty($categoria)) {
                error_log("Error al añadir a lista");
                return false;
            }

            $elementoExistente = Lista::getByUsuarioYContenido($id_usuario, $api_id, $categoria);

            if ($elementoExistente) {
                return $elementoExistente->delete();
            }
            $nuevoElemento = new Lista();
            $nuevoElemento->setIdUsuario($id_usuario);
            $nuevoElemento->setApiId($api_id);
            $nuevoElemento->setCategoria($categoria);

            $resultado = $nuevoElemento->create();
            return $resultado ? true : false;
        } catch (Exception $e) {
            error_log("Error en añadirALista: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarDeLista($id_usuario, $api_id, $categoria)
    {
        try {
            if (empty($id_usuario) || empty($api_id) || empty($categoria)) {
                error_log("Parámetros inválidos en eliminarDeLista");
                return false;
            }

            $elemento = Lista::getByUsuarioYContenido($id_usuario, $api_id, $categoria);

            if ($elemento) {
                return $elemento->delete();
            }

            return false;
        } catch (Exception $e) {
            error_log("Error en eliminarDeLista: " . $e->getMessage());
            return false;
        }
    }
    public function eliminarDeListaPorId($id_lista)
    {
        try {
            if (empty($id_lista)) {
                error_log("ID de lista inválido en eliminarDeListaPorId");
                return false;
            }

            return Lista::deleteById($id_lista);
        } catch (Exception $e) {
            error_log("Error en eliminarDeListaPorId: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerListaPorUsuario($id_usuario)
    {
        try {
            if (empty($id_usuario)) {
                error_log("ID de usuario inválido en obtenerListaPorUsuario");
                return [];
            }

            return Lista::getListaByUsuario($id_usuario);
        } catch (Exception $e) {
            error_log("Error en obtenerListaPorUsuario: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerListaPorUsuarioYCategoria($id_usuario, $categoria)
    {
        try {
            if (empty($id_usuario) || empty($categoria)) {
                error_log("Parámetros inválidos en obtenerListaPorUsuarioYCategoria");
                return [];
            }

            return Lista::getListaByUsuarioYCategoria($id_usuario, $categoria);
        } catch (Exception $e) {
            error_log("Error en obtenerListaPorUsuarioYCategoria: " . $e->getMessage());
            return [];
        }
    }

    public function estaEnLista($id_usuario, $api_id, $categoria)
    {
        try {
            if (empty($id_usuario) || empty($api_id) || empty($categoria)) {
                error_log("Parámetros inválidos en estaEnLista");
                return false;
            }

            $elemento = Lista::getByUsuarioYContenido($id_usuario, $api_id, $categoria);
            return $elemento !== null;
        } catch (Exception $e) {
            error_log("Error en estaEnLista: " . $e->getMessage());
            return false;
        }
    }
    public function contarElementosLista($id_usuario, $categoria = null)
    {
        try {
            if (empty($id_usuario)) {
                error_log("ID de usuario inválido en contarElementosLista");
                return 0;
            }

            return Lista::countByUsuario($id_usuario, $categoria);
        } catch (Exception $e) {
            error_log("Error en contarElementosLista: " . $e->getMessage());
            return 0;
        }
    }
}
