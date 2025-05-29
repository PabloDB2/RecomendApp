<?php
require_once(__DIR__ . '/../../../rutas.php');
require_once(CONTROLLER . 'UsuarioController.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioController = new UsuarioController();
    $response = [];

    if (!empty($_POST['username'])) {
        $usuario = $usuarioController->getUserByName($_POST['username']);
        $response['usernameExists'] = ($usuario !== null);
    }

    if (!empty($_POST['email'])) {
        $usuario = $usuarioController->getUserByEmail($_POST['email']);
        $response['emailExists'] = ($usuario !== null);
    }

    echo json_encode($response);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
exit;
