<?php
header('Content-Type: application/json');
try {
    require_once(__DIR__ . '/../../../rutas.php');
    require_once(CONFIG . 'sesion.php');
    require_once(CONTROLLER . 'UsuarioController.php');
    require_once(CONTROLLER . 'ResenaController.php');

    $api_id = $_POST['api_id'] ?? null;
    $categoria = $_POST['categoria'] ?? null;
    $texto = isset($_POST['texto']) ? trim($_POST['texto']) : null;
    $puntuacion = isset($_POST['puntuacion']) ? intval($_POST['puntuacion']) : 0;
    $accion = $_POST['accion'] ?? 'crear';
    $id_resena = isset($_POST['id_resena']) ? intval($_POST['id_resena']) : 0;
    $usuarioController = new UsuarioController();
    $usuario = $usuarioController->getUserByName($_SESSION['nombre_usuario']);
    $id_usuario = $usuario->getIdUsuario();
    $resenaController = new ResenaController();

    if ($accion === 'crear') {
        $error = $resenaController->crearResena($id_usuario, $api_id, $categoria, $texto, $puntuacion);
    } elseif ($accion === 'actualizar') {
        $error = $resenaController->actualizarResena($id_resena, $texto, $puntuacion);
    } elseif ($accion === 'eliminar') {
        $error = $resenaController->eliminarResena($id_resena);
    } else {
        throw new Exception('Acción no válida.');
    }

    if ($error) throw new Exception($error);

    $resenas = $resenaController->getResenasByApiId($api_id, $categoria);
    echo json_encode([
        'success' => true,
        'message' => 'Accion realizada correctamente.',
        'resenas' => $resenas
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
