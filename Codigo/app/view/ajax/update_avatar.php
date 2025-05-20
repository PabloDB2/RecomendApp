<?php
session_start();

require_once(__DIR__ . '/../../../rutas.php');
require_once(CONTROLLER . 'UsuarioController.php');

function sendJsonResponse($success, $message) {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

$avatar = $_POST['avatar'];
$nombre_usuario = $_SESSION['nombre_usuario'];


$carpetaImagenes = '../images/avatars/'; //carpeta con todos los avatares
$avatares_disponibles = array_values(array_filter(scandir($carpetaImagenes), function($file) use ($carpetaImagenes) {
    return is_file($carpetaImagenes . $file);
}));


if (!in_array($avatar, $avatares_disponibles)) {
    sendJsonResponse(false, 'Avatar no válido');
}

try {
    $usuarioController = new UsuarioController();
    $result = $usuarioController->updateAvatar($nombre_usuario, $avatar);

    if ($result) {
        sendJsonResponse(true, 'Avatar actualizado correctamente');
    } else {
        sendJsonResponse(false, 'Error al actualizar el avatar');
    }
} catch (Exception $e) {
    error_log("Error in update_avatar.php: " . $e->getMessage());
    sendJsonResponse(false, 'Error interno del servidor');
}
