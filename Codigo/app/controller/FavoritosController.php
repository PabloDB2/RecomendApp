<?php
require_once(__DIR__ . '/../../rutas.php');
require_once(CONFIG . 'dbConnection.php');
require_once(MODEL . 'Favorito.php');

class FavoritosController
{

    public function marcarFavorito($id_usuario, $api_id, $categoria)
    {
        try {
            if (empty($id_usuario) || empty($api_id) || empty($categoria)) {
                error_log("Parámetros inválidos en marcarFavorito");
                return false;
            }

            $favoritoExistente = Favorito::getByUsuarioYContenido($id_usuario, $api_id, $categoria);

            if ($favoritoExistente) {
                return $favoritoExistente->delete();
            }

            $nuevoFavorito = new Favorito();
            $nuevoFavorito->setIdUsuario($id_usuario);
            $nuevoFavorito->setApiId($api_id);
            $nuevoFavorito->setCategoria($categoria);

            $resultado = $nuevoFavorito->create();
            return $resultado ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function eliminarFavorito($id_usuario, $api_id, $categoria)
    {
        try {
            if (empty($id_usuario) || empty($api_id) || empty($categoria)) {
                return false;
            }

            $favorito = Favorito::getByUsuarioYContenido($id_usuario, $api_id, $categoria);

            if ($favorito) {
                return $favorito->delete();
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function obtenerFavoritosPorUsuarioYCategoria($id_usuario, $categoria)
    {
        try {
            return Favorito::getFavoritosByUsuarioYCategoria($id_usuario, $categoria);
        } catch (Exception $e) {
            error_log("Error en obtenerFavoritosPorUsuarioYCategoria: " . $e->getMessage());
            return [];
        }
    }

    public function esFavorito($id_usuario, $api_id, $categoria)
    {
        try {
            if (empty($id_usuario) || empty($api_id) || empty($categoria)) {
                return false;
            }

            $favorito = Favorito::getByUsuarioYContenido($id_usuario, $api_id, $categoria);
            return $favorito !== null;
        } catch (Exception $e) {
            return false;
        }
    }

    public function contarFavoritos($id_usuario, $categoria = null)
    {
        try {
            if (empty($id_usuario)) {
                return 0;
            }

            return Favorito::countByUsuario($id_usuario, $categoria);
        } catch (Exception $e) {
            return 0;
        }
    }
}
