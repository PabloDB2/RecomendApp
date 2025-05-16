<?php
header('Content-Type: application/json; charset=utf-8');
require_once(__DIR__ . '/../../../rutas.php');
require_once(CONFIG . 'sesion.php');

$nombre_usuario = $_SESSION['nombre_usuario'] ?? null;
$id_usuario = null;
$enListaArr = [];
if ($nombre_usuario) {
    require_once(CONTROLLER . 'UsuarioController.php');
    require_once(CONTROLLER . 'ListaController.php');
    $usuarioController = new UsuarioController();
    $usuario = $usuarioController->getUserByName($nombre_usuario);
    if ($usuario) {
        $id_usuario = $usuario->getIdUsuario();
        $listaController = new ListaController();
    }
}

$region = $_GET['region'] ?? 'ES';
$genero = $_GET['genero'] ?? '';
$anio = $_GET['anio'] ?? '';
$pais = $_GET['pais'] ?? '';
$duracion = $_GET['duracion'] ?? '';
$proveedor = $_GET['proveedor'] ?? '';
$productora = $_GET['productora'] ?? '';
$orden = $_GET['orden'] ?? '';
$busqueda = $_GET['busqueda'] ?? '';
$page = intval($_GET['page'] ?? 1);
$base_url = $busqueda 
    ? "https://api.themoviedb.org/3/search/movie?api_key=$api_key&language=es-ES&query=" . urlencode($busqueda) . "&page=$page"
    : "https://api.themoviedb.org/3/discover/movie?api_key=$api_key&language=es-ES&page=$page";

$filters = [];
if ($genero) $filters[] = "with_genres=$genero";
if ($anio) $filters[] = "primary_release_year=$anio";
if ($pais) $filters[] = "with_origin_country=$pais";
if ($duracion) {
    $duracion_map = [
        '<60' => "with_runtime.lte=60",
        '60-90' => "with_runtime.gte=60&with_runtime.lte=90",
        '90-120' => "with_runtime.gte=90&with_runtime.lte=120",
        '120-180' => "with_runtime.gte=120&with_runtime.lte=180",
        '>180' => "with_runtime.gte=180"
    ];
    if (isset($duracion_map[$duracion])) $filters[] = $duracion_map[$duracion];
}
if ($proveedor) $filters[] = "with_watch_providers=$proveedor&watch_region=$region";
if ($productora) $filters[] = "with_companies=$productora";
if ($orden) {
    $filters[] = "sort_by=$orden";
    if ($orden == 'vote_average.desc') $filters[] = "vote_count.gte=1000";
    if ($orden == 'release_date.desc') $filters[] = "release_date.lte=" . date('Y-m-d');
}

$url = $base_url . (count($filters) ? '&' . implode('&', $filters) : '');
$response = @file_get_contents($url);
$data = $response ? json_decode($response, true) : [];
$peliculas = $data['results'] ?? [];

foreach ($peliculas as &$peli) {
    $peli['enLista'] = false;
    if ($id_usuario && isset($listaController)) {
        $peli['enLista'] = $listaController->estaEnLista($id_usuario, $peli['id'], 'pelicula');
    }
}

$peliculas_simple = array_map(function($peli) {
    return [
        'id' => $peli['id'],
        'title' => $peli['title'] ?? '',
        'poster_path' => $peli['poster_path'] ?? '',
        'release_date' => $peli['release_date'] ?? '',
        'vote_average' => $peli['vote_average'] ?? 0,
        'overview' => $peli['overview'] ?? '',
        'enLista' => $peli['enLista'] ?? false,
    ];
}, $peliculas);

echo json_encode([
    'peliculas' => $peliculas_simple
]);
