<?php
require_once(__DIR__ . '/../../../rutas.php');

require_once(CONFIG . 'sesion.php');
$nombre_usuario = $_SESSION['nombre_usuario'] ?? null;
include '../Generales/nav.php';

if (!$nombre_usuario) {
    header('Location: login.php'); //si no esta logeado redirige a login
    exit;
}
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
$email = $usuario->getEmail();

$avatar = $usuario->getAvatar() ?? 'default-avatar.jpg';
$stats = [ // se obtienen las estadisticas de la base de datos
    'peliculas' => [
        'favoritos' => $favoritoController->contarFavoritos($id_usuario, 'pelicula'),
        'vistos' => $visualizacionController->contarVisualizaciones($id_usuario, 'pelicula'),
        'lista' => $listaController->contarElementosLista($id_usuario, 'pelicula')
    ],
    'series' => [
        'favoritos' => $favoritoController->contarFavoritos($id_usuario, 'serie'),
        'vistos' => $visualizacionController->contarVisualizaciones($id_usuario, 'serie'),
        'lista' => $listaController->contarElementosLista($id_usuario, 'serie')
    ],
    'libros' => [
        'favoritos' => $favoritoController->contarFavoritos($id_usuario, 'libro'),
        'vistos' => $visualizacionController->contarVisualizaciones($id_usuario, 'libro'),
        'lista' => $listaController->contarElementosLista($id_usuario, 'libro')
    ]
];

$totales = [
    'favoritos' => array_sum(array_column($stats, 'favoritos')),
    'vistos' => array_sum(array_column($stats, 'vistos')),
    'lista' => array_sum(array_column($stats, 'lista'))
];
$categoria = $_GET['categoria'] ?? 'pelicula';
$seccion = $_GET['seccion'] ?? 'favoritos';
$categorias_validas = ['pelicula', 'serie', 'libro'];
$secciones_validas = ['favoritos', 'vistos', 'lista'];
if (!in_array($categoria, $categorias_validas)) {
    $categoria = 'pelicula';
}
if (!in_array($seccion, $secciones_validas)) {
    $seccion = 'favoritos';
}
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

// obtener datos de peliculas y series
function obtenerDetallesItem($id, $tipo, $api_key)
{
    $idioma = 'es-ES';
    $url = '';
    if ($tipo == 'pelicula') {
        $url = "https://api.themoviedb.org/3/movie/{$id}?api_key={$api_key}&language={$idioma}";
    } elseif ($tipo == 'serie') {
        $url = "https://api.themoviedb.org/3/tv/{$id}?api_key={$api_key}&language={$idioma}";
    } else {
        return null; // pendiente añadir libros
    }

    $respuesta = @file_get_contents($url);
    return $respuesta ? json_decode($respuesta, true) : null;
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

function obtenerAño($fecha)
{
    return date('Y', strtotime($fecha));
}
function obtenerImagen($ruta, $tamaño = 'w342')
{
    return $ruta ? "https://image.tmdb.org/t/p/{$tamaño}{$ruta}" : null;
}
function obtenerCategoria($categoria)
{
    $nombres = [
        'pelicula' => 'Películas',
        'serie' => 'Series',
        'libro' => 'Libros'
    ];
    return $nombres[$categoria] ?? $categoria;
}

function obtenerSeccion($seccion)
{
    $nombres = [
        'favoritos' => 'Favoritos',
        'vistos' => 'Vistos',
        'lista' => 'Mi Lista'
    ];
    return $nombres[$seccion] ?? $seccion;
}

$carpetaImagenes = '../images/avatars/';
$avatares_disponibles = array_values(array_filter(scandir($carpetaImagenes), function ($imagen) use ($carpetaImagenes) {
    return is_file($carpetaImagenes . $imagen);
}));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi perfil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../favicon.ico" type="image/x-icon"> <!-- pendiente cambiar el favicon por mi logo -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <link rel="stylesheet" href="../CSS/pelicula_detalle.css">
    <link rel="stylesheet" href="../CSS/perfil.css">
    <link rel="preconnect" href="https://image.tmdb.org">
</head>

<body>
    <main>
        <section class="profile-hero" style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.8), var(--black-dark)), url('../Images/avatars/<?= htmlspecialchars($avatar) ?>');">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-4 col-lg-3 text-center text-md-start">
                        <div class="avatar-container">
                            <img src="../Images/avatars/<?= htmlspecialchars($avatar) ?>" class="profile-avatar" id="current-avatar">
                            <div class="avatar-overlay">
                                <button type="button" class="btn-change-avatar" data-bs-toggle="modal" data-bs-target="#avatarModal">
                                    <i class="fas fa-camera"></i>
                                    <span>Cambiar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 col-lg-9">
                        <div class="profile-info">
                            <h1 class="profile-username"><?= htmlspecialchars($nombre_usuario) ?></h1>
                        </div>
                        <div class="profile-info">
                            <h3 class="profile-username"><?= htmlspecialchars($email) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="container mt-4">
            <div class="stats-section">
                <h2 class="stats-title">
                    <i class="fas fa-chart-pie me-2"></i>Estadísticas
                </h2>
                <div class="detailed-stats">
                    <div class="detailed-stat-card">
                        <div class="detailed-stat-header">
                            <div class="detailed-stat-icon">
                                <i class="fas fa-film"></i>
                            </div>
                            <h3 class="detailed-stat-title">Películas</h3>
                        </div>
                        <div class="detailed-stat-content">
                            <div class="detailed-stat-item">
                                <div class="detailed-stat-value"><?= $stats['peliculas']['vistos'] ?></div>
                                <div class="detailed-stat-label">Vistas</div>
                            </div>
                            <div class="detailed-stat-item">
                                <div class="detailed-stat-value"><?= $stats['peliculas']['favoritos'] ?></div>
                                <div class="detailed-stat-label">Favoritas</div>
                            </div>
                            <div class="detailed-stat-item">
                                <div class="detailed-stat-value"><?= $stats['peliculas']['lista'] ?></div>
                                <div class="detailed-stat-label">En Lista</div>
                            </div>
                        </div>
                        <?php if ($totales['vistos'] > 0): ?>
                            <div class="progress-container">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?= ($stats['peliculas']['vistos'] / $totales['vistos']) * 100 ?>%" aria-valuenow="<?= ($stats['peliculas']['vistos'] / $totales['vistos']) * 100 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="progress-label">
                                    <span><?= round(($stats['peliculas']['vistos'] / $totales['vistos']) * 100) ?>% del total visto</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="detailed-stat-card">
                        <div class="detailed-stat-header">
                            <div class="detailed-stat-icon">
                                <i class="fas fa-tv"></i>
                            </div>
                            <h3 class="detailed-stat-title">Series</h3>
                        </div>
                        <div class="detailed-stat-content">
                            <div class="detailed-stat-item">
                                <div class="detailed-stat-value"><?= $stats['series']['vistos'] ?></div>
                                <div class="detailed-stat-label">Vistas</div>
                            </div>
                            <div class="detailed-stat-item">
                                <div class="detailed-stat-value"><?= $stats['series']['favoritos'] ?></div>
                                <div class="detailed-stat-label">Favoritas</div>
                            </div>
                            <div class="detailed-stat-item">
                                <div class="detailed-stat-value"><?= $stats['series']['lista'] ?></div>
                                <div class="detailed-stat-label">En Lista</div>
                            </div>
                        </div>
                        <?php if ($totales['vistos'] > 0): ?>
                            <div class="progress-container">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?= ($stats['series']['vistos'] / $totales['vistos']) * 100 ?>%" aria-valuenow="<?= ($stats['series']['vistos'] / $totales['vistos']) * 100 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="progress-label">
                                    <span><?= round(($stats['series']['vistos'] / $totales['vistos']) * 100) ?>% del total visto</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="detailed-stat-card">
                        <div class="detailed-stat-header">
                            <div class="detailed-stat-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <h3 class="detailed-stat-title">Libros</h3>
                        </div>
                        <div class="detailed-stat-content">
                            <div class="detailed-stat-item">
                                <div class="detailed-stat-value"><?= $stats['libros']['vistos'] ?></div>
                                <div class="detailed-stat-label">Leídos</div>
                            </div>
                            <div class="detailed-stat-item">
                                <div class="detailed-stat-value"><?= $stats['libros']['favoritos'] ?></div>
                                <div class="detailed-stat-label">Favoritos</div>
                            </div>
                            <div class="detailed-stat-item">
                                <div class="detailed-stat-value"><?= $stats['libros']['lista'] ?></div>
                                <div class="detailed-stat-label">En Lista</div>
                            </div>
                        </div>
                        <?php if ($totales['vistos'] > 0): ?>
                            <div class="progress-container">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?= ($stats['libros']['vistos'] / $totales['vistos']) * 100 ?>%" aria-valuenow="<?= ($stats['libros']['vistos'] / $totales['vistos']) * 100 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="progress-label">
                                    <span><?= round(($stats['libros']['vistos'] / $totales['vistos']) * 100) ?>% del total visto</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <section class="container mt-4">
            <div class="content-section">
                <h2 class="section-title">Mi Colección</h2>

                <!-- categorias -->
                <div class="category-tabs" id="categoryTabs">
                    <?php foreach ($categorias_validas as $cat): ?>
                        <a href="?categoria=<?= $cat ?>&seccion=<?= $seccion ?>" class="category-tab <?= ($categoria == $cat) ? 'active' : '' ?>" data-categoria="<?= $cat ?>">
                            <i class="fas fa-<?= $cat == 'pelicula' ? 'film' : ($cat == 'serie' ? 'tv' : 'book') ?> me-2"></i>
                            <?= obtenerCategoria($cat) ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- secciones -->
                <div class="section-tabs" id="sectionTabs">
                    <?php foreach ($secciones_validas as $sec): ?>
                        <a href="?categoria=<?= $categoria ?>&seccion=<?= $sec ?>" class="section-tab <?= ($seccion == $sec) ? 'active' : '' ?>" data-seccion="<?= $sec ?>">
                            <i class="fas fa-<?= $sec == 'favoritos' ? 'heart' : ($sec == 'vistos' ? 'eye' : 'bookmark') ?> me-2"></i>
                            <?= obtenerSeccion($sec) ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div id="itemsContainer">
                    <?php if (empty($items_con_detalle)): ?>
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
                            <?php foreach ($items_con_detalle as $item): ?>
                                <div class="item-card">
                                    <div class="item-poster">
                                        <?php
                                        $ruta_poster = $categoria == 'pelicula' ? $item['detalles']['poster_path'] : ($categoria == 'serie' ? $item['detalles']['poster_path'] : null);
                                        $titulo = $categoria == 'pelicula' ? $item['detalles']['title'] : ($categoria == 'serie' ? $item['detalles']['name'] : $item['detalles']['title']);
                                        $fecha_lanzamiento = $categoria == 'pelicula' ? $item['detalles']['release_date'] : ($categoria == 'serie' ? $item['detalles']['first_air_date'] : null);
                                        $url_detalle = $categoria == 'pelicula' ? 'pelicula_detalle.php?id=' . $item['id'] : ($categoria == 'serie' ? 'serie_detalle.php?id=' . $item['id'] : 'libro-detalles.php?id=' . $item['id']);
                                        ?>

                                        <?php if ($ruta_poster): ?>
                                            <a href="<?= $url_detalle ?>">
                                                <img src="<?= obtenerImagen($ruta_poster) ?>" alt="<?= htmlspecialchars($titulo) ?>" loading="lazy">
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= $url_detalle ?>">
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
                                        <h3 class="item-title"><?= htmlspecialchars($titulo) ?></h3>
                                        <?php if ($fecha_lanzamiento): ?>
                                            <span class="item-year"><?= obtenerAño($fecha_lanzamiento) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
    <div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title" id="avatarModalLabel">Seleccionar Avatar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="avatar-grid">
                        <?php foreach ($avatares_disponibles as $avatar_file): ?>
                            <div class="avatar-option" data-avatar="<?= htmlspecialchars($avatar_file) ?>">
                                <img src="../Images/avatars/<?= htmlspecialchars($avatar_file) ?>" alt="Avatar option" class="avatar-thumbnail">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/perfil.js"></script>
</body>

</html>