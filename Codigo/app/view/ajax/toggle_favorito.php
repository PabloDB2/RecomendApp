<?php
session_start();
header('Content-Type: application/json');

require_once(__DIR__ . '/../../../rutas.php');
require_once(CONTROLLER . 'UsuarioController.php');
require_once(CONTROLLER . 'FavoritosController.php');

if (!isset($_SESSION['nombre_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para realizar esta acción.']);
    exit;
}

$api_id = $_POST['api_id'] ?? '';
$categoria = $_POST['categoria'] ?? '';
$nombre_usuario = $_SESSION['nombre_usuario'];

if (empty($api_id) || empty($categoria)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

$usuarioController = new UsuarioController();
$usuario = $usuarioController->getUserByName($nombre_usuario);

if (!$usuario) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
    exit;
}

$id_usuario = $usuario->getIdUsuario();

$favoritoController = new FavoritosController();
if ($favoritoController->esFavorito($id_usuario, $api_id, $categoria)) {
    $favoritoController->eliminarFavorito($id_usuario, $api_id, $categoria);
    echo json_encode(['success' => true, 'message' => 'Eliminado de favoritos.', 'esFavorito' => false]);
} else {
    $favoritoController->marcarFavorito($id_usuario, $api_id, $categoria);
    echo json_encode(['success' => true, 'message' => 'Añadido a favoritos.', 'esFavorito' => true]);
}
exit;
