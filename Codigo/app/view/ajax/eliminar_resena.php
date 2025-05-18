<?php
session_start();
header('Content-Type: application/json');

require_once(__DIR__ . '/../../../rutas.php');
require_once(CONTROLLER . 'ResenaController.php');

$id_reseña = $_POST['id_reseña'] ?? null;


$resena = (new ResenaController())->getResenaById($id_reseña);


$success = $resena->delete();

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Reseña eliminada correctamente' : 'Error al eliminar la reseña.'
]);
