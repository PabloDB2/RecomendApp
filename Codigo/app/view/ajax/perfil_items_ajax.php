<?php
require_once(__DIR__ . '/../../../rutas.php');
require_once(CONFIG . 'sesion.php'); 
$nombre_usuario = $_SESSION['nombre_usuario'] ?? null;
if (!$nombre_usuario) exit('Debes iniciar sesión.');

require_once(CONTROLLER . 'UsuarioController.php');
require_once(CONTROLLER . 'FavoritosController.php');
require_once(CONTROLLER . 'VisualizacionController.php');
require_once(CONTROLLER . 'ListaController.php');

$usuarioController = new UsuarioController();
$favoritoController = new FavoritosController();
$visualizacionController = new VisualizacionController();
$listaController = new ListaController();

$usuario = $usuarioController->getUserByName($nombre_usuario);
$id_usuario = $usuario->getIdUsuario();

$categoria = $_GET['categoria'] ?? 'pelicula';
$seccion = $_GET['seccion'] ?? 'favoritos';

$categorias_validas = ['pelicula', 'serie', 'libro'];
$secciones_validas = ['favoritos', 'vistos', 'lista'];
if (!in_array($categoria, $categorias_validas)) $categoria = 'pelicula';
if (!in_array($seccion, $secciones_validas)) $seccion = 'favoritos';

$items = [];
switch ($seccion) {
    case 'favoritos':
        $items = $favoritoController->obtenerFavoritosPorUsuarioYCategoria($id_usuario, $categoria);
        break;
    case 'vistos':
        $items = $visualizacionController->obtenerVisualizacionesPorUsuarioYCategoria($id_usuario, $categoria);
        break;
    case 'lista':
        $items = $listaController->obtenerListaPorUsuarioYCategoria($id_usuario, $categoria);
        break;
}

function obtenerDetallesItem($id, $tipo, $api_key) {
    $idioma = 'es-ES';
    if ($tipo == 'pelicula') {
        $url = "https://api.themoviedb.org/3/movie/{$id}?api_key={$api_key}&language={$idioma}";
    } elseif ($tipo == 'serie') {
        $url = "https://api.themoviedb.org/3/tv/{$id}?api_key={$api_key}&language={$idioma}";
    } else {
        return null;
    }
    $respuesta = @file_get_contents($url);
    return $respuesta ? json_decode($respuesta, true) : null;
}
function obtenerAño($fecha) { return date('Y', strtotime($fecha)); }
function obtenerImagen($ruta, $size = 'w342') { return $ruta ? "https://image.tmdb.org/t/p/{$size}{$ruta}" : null; }
function obtenerCategoria($categoria) {
    $nombres = ['pelicula' => 'Películas', 'serie' => 'Series', 'libro' => 'Libros'];
    return $nombres[$categoria] ?? $categoria;
}
function obtenerSeccion($seccion) {
    $nombres = ['favoritos' => 'Favoritos', 'vistos' => 'Vistos', 'lista' => 'Mi Lista'];
    return $nombres[$seccion] ?? $seccion;
}

$items_con_detalle = [];
foreach ($items as $item) {
    $detalles = obtenerDetallesItem($item['api_id'], $categoria, $api_key);
    if ($detalles) {
        $items_con_detalle[] = [
            'id' => $item['api_id'],
            'detalles' => $detalles
        ];
    }
}

if (empty($items_con_detalle)): ?>
    <div class="no-items">
        <i class="fas fa-<?= $categoria == 'pelicula' ? 'film' : ($categoria == 'serie' ? 'tv' : 'book') ?>"></i>
        <h3>No hay <?= strtolower(obtenerCategoria($categoria)) ?> en tu <?= strtolower(obtenerSeccion($seccion)) ?></h3>
        <p>Explora nuestro catálogo para añadir <?= strtolower(obtenerCategoria($categoria)) ?> a tu colección.</p>
        <a href="<?= $categoria == 'pelicula' ? 'peliculas.php' : ($categoria == 'serie' ? 'series.php' : 'libros.php') ?>" class="btn btn-outline-light mt-3">
            <i class="fas fa-search me-2"></i>Explorar <?= obtenerCategoria($categoria) ?>
        </a>
    </div>
<?php else: ?>
    <div class="items-grid">
        <?php foreach ($items_con_detalle as $item): 
            $poster_path = $categoria == 'pelicula' ? $item['detalles']['poster_path'] : 
                          ($categoria == 'serie' ? $item['detalles']['poster_path'] : null);
            $title = $categoria == 'pelicula' ? $item['detalles']['title'] : 
                    ($categoria == 'serie' ? $item['detalles']['name'] : $item['detalles']['title']);
            $release_date = $categoria == 'pelicula' ? $item['detalles']['release_date'] : 
                          ($categoria == 'serie' ? $item['detalles']['first_air_date'] : null);
            $detail_url = $categoria == 'pelicula' ? 'pelicula_detalle.php?id=' . $item['id'] : 
                        ($categoria == 'serie' ? 'serie_detalle.php?id=' . $item['id'] : 'libro-details.php?id=' . $item['id']);
        ?>
        <div class="item-card">
            <div class="item-poster">
                <?php if ($poster_path): ?>
                    <a href="<?= $detail_url ?>">
                        <img src="<?= obtenerImagen($poster_path) ?>" alt="<?= htmlspecialchars($title) ?>" loading="lazy">
                    </a>
                <?php else: ?>
                    <a href="<?= $detail_url ?>">
                        <div class="no-poster">
                            <i class="fas fa-<?= $categoria == 'pelicula' ? 'film' : ($categoria == 'serie' ? 'tv' : 'book') ?>"></i>
                            <span>Sin imagen</span>
                        </div>
                    </a>
                <?php endif; ?>
                <?php if (isset($item['detalles']['vote_average'])): ?>
                <div class="item-rating">
                    <span class="rating-value">
                        <i class="fas fa-star"></i>
                        <?= number_format($item['detalles']['vote_average'], 1) ?>
                    </span>
                </div>
                <?php endif; ?>
                <div class="item-actions">
                    <button class="btn-action" title="Quitar de <?= strtolower(obtenerSeccion($seccion)) ?>" data-id="<?= $item['id'] ?>" data-tipo="<?= $categoria ?>" data-accion="<?= $seccion ?>">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="item-info">
                <h3 class="item-title"><?= htmlspecialchars($title) ?></h3>
                <?php if (!empty($release_date)): ?>
                    <span class="item-year"><?= obtenerAño($release_date) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
