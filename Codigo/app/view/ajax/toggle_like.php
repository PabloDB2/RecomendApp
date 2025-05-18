<?php
session_start();
header('Content-Type: application/json');

require_once(__DIR__ . '/../../../rutas.php');
require_once(CONTROLLER . 'UsuarioController.php');
require_once(CONTROLLER . 'ResenaController.php');
require_once(MODEL . 'Resena.php');

$id_resena = isset($_POST['id_resena']) ? (int)$_POST['id_resena'] : 0;
$nombre_usuario = $_SESSION['nombre_usuario'] ?? null;

if (!$nombre_usuario || $id_resena <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos o sesión no iniciada.'
    ]);
    exit;
}

$usuarioController = new UsuarioController();
$usuario = $usuarioController->getUserByName($nombre_usuario);

if (!$usuario) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no encontrado.'
    ]);
    exit;
}

$id_usuario = $usuario->getIdUsuario();
$resenaController = new ResenaController();
$resena = $resenaController->getResenaById($id_resena);

if (!$resena) {
    echo json_encode([
        'success' => false,
        'message' => 'Reseña no encontrada.'
    ]);
    exit;
}

$conn = getDBConnection();

try {
    if (Resena::usuarioDioLike($id_usuario, $id_resena)) {
        // Quitar like
        $sentencia = $conn->prepare("DELETE FROM likes_resena WHERE id_usuario = ? AND id_resena = ?");
        $sentencia->execute([$id_usuario, $id_resena]);

        if ($resena->getLikes() > 0) {
            $resena->setLikes($resena->getLikes() - 1);
            $update = $conn->prepare("UPDATE reseñas SET likes = ? WHERE id_reseña = ?");
            $update->execute([$resena->getLikes(), $id_resena]);
        }

        $likesStmt = $conn->prepare("SELECT likes FROM reseñas WHERE id_reseña = ?");
        $likesStmt->execute([$id_resena]);
        $likes = (int)$likesStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'message' => 'Like quitado correctamente',
            'likes' => $likes,
            'liked' => false
        ]);
        exit;
    } else {
        // Dar like
        if ($resena->incrementLikes()) {
            if (Resena::registrarLike($id_usuario, $id_resena)) {
                $likesStmt = $conn->prepare("SELECT likes FROM reseñas WHERE id_reseña = ?");
                $likesStmt->execute([$id_resena]);
                $likes = (int)$likesStmt->fetchColumn();

                echo json_encode([
                    'success' => true,
                    'message' => 'Like dado correctamente',
                    'likes' => $likes,
                    'liked' => true
                ]);
                exit;
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al registrar el like.'
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al incrementar los likes.'
            ]);
            exit;
        }
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
    exit;
}
