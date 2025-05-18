<?php
require_once(__DIR__ . '/../../rutas.php');
require_once(CONFIG . 'dbConnection.php');
require_once(MODEL . 'Resena.php');

class ResenaController
{
    public function getResenaById($id_reseña)
    {
        return Resena::getById($id_reseña);
    }

    public function getResenasByApiId($api_id, $categoria)
    {
        return Resena::getByApiId($api_id, $categoria);
    }

    public function getResenasByUsuario($id_usuario)
    {
        return Resena::getByUsuario($id_usuario);
    }

    public function usuarioTieneResena($id_usuario, $api_id, $categoria)
    {
        return Resena::usuarioTieneResena($id_usuario, $api_id, $categoria);
    }

    public function getResenaUsuario($id_usuario, $api_id, $categoria)
    {
        return Resena::getResenaUsuario($id_usuario, $api_id, $categoria);
    }

    public function crearResena($id_usuario, $api_id, $categoria, $texto, $puntuacion)
    {     
        if (Resena::usuarioTieneResena($id_usuario, $api_id, $categoria)) {
            return "Ya has publicado una reseña para este contenido.";
        }

        $nuevaResena = new Resena();
        $nuevaResena->setIdUsuario($id_usuario);
        $nuevaResena->setApiId($api_id);
        $nuevaResena->setCategoria($categoria);
        $nuevaResena->setTexto($texto);
        $nuevaResena->setPuntuacion($puntuacion);

        if ($nuevaResena->create()) {
            return null; 
        } else {
            return "Error al crear la reseña.";
        }
    }

    public function actualizarResena($id_reseña, $texto, $puntuacion)
    {
        $resena = Resena::getById($id_reseña);
        $resena->setTexto($texto);
        $resena->setPuntuacion($puntuacion);
        if ($resena->update()) {
            return null;
        } else {
            return "Error al actualizar la reseña.";
        }
    }

    public function eliminarResena($id_reseña)
    {
        $resena = Resena::getById($id_reseña);
        if ($resena->delete()) {
            return null; 
        } else {
            return "Error al eliminar la reseña.";
        }
    }

    public function addLikeToResena($id_resena)
    {
        $resena = $this->getResenaById($id_resena);
        if ($resena->incrementLikes()) {
            return null; 
        } else {
            return "Error al dar like a la reseña.";
        }
    }

    public function removeLikeFromResena($id_resena)
    {
        $resena = $this->getResenaById($id_resena);
        if ($resena->getLikes() > 0) {
            $resena->setLikes($resena->getLikes() - 1);
            if ($resena->update()) {
                return null;
            }
        }
        return "Error al quitar like a la reseña.";
    }

    public function usuarioDioLike($id_usuario, $id_resena)
    {
        return Resena::usuarioDioLike($id_usuario, $id_resena);
    }

    public function registrarLike($id_usuario, $id_resena)
    {
        if ($this->usuarioDioLike($id_usuario, $id_resena)) {
            return "El usuario ya dio like a esta reseña.";
        }

        if (Resena::registrarLike($id_usuario, $id_resena)) {
            $this->addLikeToResena($id_resena);
            return null;
        }
        return "Error al registrar el like.";
    }

    public function quitarLike($id_usuario, $id_resena)
    {
        if (!$this->usuarioDioLike($id_usuario, $id_resena)) {
            return "El usuario no ha dado like a esta reseña.";
        }

        if (Resena::quitarLike($id_usuario, $id_resena)) {
            $this->removeLikeFromResena($id_resena);
            return null;
        }
        return "Error al quitar el like.";
    }
}
