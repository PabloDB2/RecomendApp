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
$proveedor = $_GET['proveedor'] ?? '';
$orden = $_GET['orden'] ?? '';
$busqueda = $_GET['busqueda'] ?? '';
$page = intval($_GET['page'] ?? 1);
$base_url = $busqueda 
    ? "https://api.themoviedb.org/3/search/tv?api_key=$api_key&language=es-ES&query=" . urlencode($busqueda) . "&page=$page"
    : "https://api.themoviedb.org/3/discover/tv?api_key=$api_key&language=es-ES&page=$page";

$filters = [];
if ($genero) {
    $genero_map = [ 
        'accion-aventura' => 10759, 'animacion' => 16, 'comedia' => 35, 'crimen' => 80,
        'documental' => 99, 'drama' => 18, 'familia' => 10751, 'infantil' => 10762, 
        'misterio' => 9648, 'noticias' => 10763, 'reality' => 10764, 
        'sci-fi-fantasia' => 10765, 'soap' => 10766, 'talk' => 10767, 
        'guerra-politica' => 10768, 'western' => 37 
    ];
    
    if (isset($genero_map[$genero])) {
        $filters[] = "with_genres=" . $genero_map[$genero];
    }
}
if ($anio) $filters[] = "first_air_date_year=$anio";
if ($pais) $filters[] = "with_origin_country=$pais";
if ($proveedor) {
    $filters[] = "with_watch_providers=$proveedor&watch_region=$region";
}

if ($orden) {
    $filters[] = "sort_by=$orden";
    
    if ($orden === 'vote_average.desc') {
        $filters[] = "vote_count.gte=100";
    }
    
    if ($orden === 'first_air_date.desc') {
        $fecha_limite = $busqueda ? date('Y-m-d', strtotime('+3 month')) : date('Y-m-d');
        $filters[] = "first_air_date.lte=$fecha_limite";
    }
}

$url = $base_url . (count($filters) ? '&' . implode('&', $filters) : '');
$response = @file_get_contents($url);
$data = $response ? json_decode($response, true) : [];
$series = $data['results'] ?? [];

foreach ($series as &$serie) {
    $serie['enLista'] = false;
    if ($id_usuario && isset($listaController)) {
        $serie['enLista'] = $listaController->estaEnLista($id_usuario, $serie['id'], 'serie');
    }
}

$series_simple = array_map(function($serie) {
    return [
        'id' => $serie['id'],
        'name' => $serie['name'] ?? '',
        'poster_path' => $serie['poster_path'] ?? '',
        'first_air_date' => $serie['first_air_date'] ?? '',
        'vote_average' => $serie['vote_average'] ?? 0,
        'overview' => $serie['overview'] ?? '',
        'enLista' => $serie['enLista'] ?? false,
    ];
}, $series);

echo json_encode([
    'series' => $series_simple
]);
