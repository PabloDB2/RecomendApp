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
            return false;
        }
    }

    public function obtenerListaPorUsuarioYCategoria($id_usuario, $categoria)
    {
        try {
            if (empty($id_usuario) || empty($categoria)) {
                return [];
            }

            return Lista::getListaByUsuarioYCategoria($id_usuario, $categoria);
        } catch (Exception $e) {
            return [];
        }
    }

    public function estaEnLista($id_usuario, $api_id, $categoria)
    {
        try {
            if (empty($id_usuario) || empty($api_id) || empty($categoria)) {
                return false;
            }

            $elemento = Lista::getByUsuarioYContenido($id_usuario, $api_id, $categoria);
            return $elemento !== null;
        } catch (Exception $e) {
            return false;
        }
    }
    public function contarElementosLista($id_usuario, $categoria = null)
    {
        try {
            if (empty($id_usuario)) {
                return 0;
            }

            return Lista::countByUsuario($id_usuario, $categoria);
        } catch (Exception $e) {
            error_log("Error en contarElementosLista: " . $e->getMessage());
            return 0;
        }
    }
}
