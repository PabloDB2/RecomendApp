<?php
session_start();
header('Content-Type: application/json');

require_once(__DIR__ . '/../../../rutas.php');
require_once(CONTROLLER . 'UsuarioController.php');
require_once(CONTROLLER . 'ListaController.php');

$api_id = $_POST['api_id'] ?? '';
$categoria = $_POST['categoria'] ?? '';
$nombre_usuario = $_SESSION['nombre_usuario'] ?? null;

if (empty($api_id) || empty($categoria) || empty($nombre_usuario)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos o usuario no autenticado']);
    exit;
}

$usuarioController = new UsuarioController();
$usuario = $usuarioController->getUserByName($nombre_usuario);

if (!$usuario) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    exit;
}

$id_usuario = $usuario->getIdUsuario();

// Añadir/quitar de la lista
$listaController = new ListaController();
$resultado = $listaController->añadirALista($id_usuario, $api_id, $categoria);

echo json_encode([
    'success' => true, 
    'enLista' => $listaController->estaEnLista($id_usuario, $api_id, $categoria)
]);
?>
