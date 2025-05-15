<?php
require_once(__DIR__ . '/../../../rutas.php');
require_once(CONTROLLER . 'UsuarioController.php');

// Comprobar en tiempo real si ya existe el email o nombre de usuario al registrarse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioController = new UsuarioController();
    $response = ['exists' => false];
    
    if (isset($_POST['username'])) {
        $username = $_POST['username'] ?? '';
        
        if (!empty($username)) {
            $usuario = $usuarioController->getUserByName($username);
            $response['exists'] = ($usuario !== null);
        }
    }
    
    if (isset($_POST['email'])) {
        $email = $_POST['email'] ?? '';
        
        if (!empty($email)) {
            $usuario = $usuarioController->getUserByEmail($email);
            $response['exists'] = ($usuario !== null);
        }
    }
    
    echo json_encode($response);
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Método no permitido']);
}
