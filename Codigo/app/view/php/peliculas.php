<?php
require_once(__DIR__ . '/../../../rutas.php');
require_once(CONFIG . 'sesion.php');
include '../Generales/nav.php';

if (isset($_SESSION['nombre_usuario'])) {
    $nombre_usuario = $_SESSION['nombre_usuario'];
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
$pagina = intval($_GET['pagina'] ?? 1);
$busqueda = $_GET['busqueda'] ?? '';
$rating_min = $_GET['rating_min'] ?? '0';
$rating_max = $_GET['rating_max'] ?? '10';
$certificacion = $_GET['certificacion'] ?? '';

// cambio de certificaciones de edad USA->España
$certificaciones = [ 'G' => 'Todas las edades', 'PG' => '+7', 'PG-13' => '+12', 'R' => '+16', 'NC-17' => '+18' ];

$paises = [ 'ES' => 'España', 'US' => 'Estados Unidos', 'FR' => 'Francia', 'DE' => 'Alemania', 'IT' => 'Italia',
            'JP' => 'Japón', 'CN' => 'China', 'RU' => 'Rusia', 'SE' => 'Suecia', 'PL' => 'Polonia',
            'IR' => 'Irán', 'KR' => 'Corea del Sur', 'MX' => 'México', 'AR' => 'Argentina', 'IN' => 'India', 'RS' => 'Serbia' ];

$genero_map = [ 'accion' => 28, 'aventura' => 12, 'animacion' => 16, 'comedia' => 35, 'criminal' => 80,
                'documental' => 99, 'drama' => 18, 'familia' => 10751, 'fantasia' => 14, 'historia' => 36,
                'terror' => 27, 'musica' => 10402, 'misterio' => 9648, 'romance' => 10749, 'ciencia-ficcion' => 878,
                'tv-movie' => 10770, 'thriller' => 53, 'guerra' => 10752, 'western' => 37 ];

                $duracion_map = [ '<60' => "with_runtime.lte=60", '60-90' => "with_runtime.gte=60&with_runtime.lte=90",
                  '90-120' => "with_runtime.gte=90&with_runtime.lte=120", '120-180' => "with_runtime.gte=120&with_runtime.lte=180",
                  '>180' => "with_runtime.gte=180" ];

// Obtener proveedores (segun la region)
$providers_url = "https://api.themoviedb.org/3/watch/providers/movie?api_key=$api_key&language=es-ES&watch_region=$region";
$providers_response = @file_get_contents($providers_url);
$providers_data = $providers_response ? json_decode($providers_response, true) : [];
$region_providers = [];

if (!empty($providers_data['results'])) {
    foreach ($providers_data['results'] as $prov) {
        $region_providers[$prov['provider_name']] = $prov['provider_id'];
    }
}

$filters = [];

if ($genero && isset($genero_map[$genero])) {
    $filters[] = "with_genres=" . $genero_map[$genero];
}
if ($anio) {
    $filters[] = "primary_release_year=$anio";
}
if ($pais && isset($paises[$pais])) {
    $filters[] = "with_origin_country=$pais";
}
if ($duracion && isset($duracion_map[$duracion])) {
    $filters[] = $duracion_map[$duracion];
}
if ($proveedor) {
    $filters[] = "with_watch_providers=$proveedor&watch_region=$region";
}
if ($productora) {
    $filters[] = "with_companies=$productora";
}
if ($rating_min !== '0' || $rating_max !== '10') {
    $filters[] = "vote_average.gte=$rating_min&vote_average.lte=$rating_max";
    $filters[] = "vote_count.gte=50";
}
if ($certificacion) {
    $filters[] = "certification_country=US&certification=$certificacion";
}
if ($orden) {
    $filters[] = "sort_by=$orden";

    if ($orden === 'vote_average.desc') {
        $filters[] = "vote_count.gte=100";
    }

    if ($orden === 'release_date.desc') {
        $fecha_limite = $busqueda ? date('Y-m-d', strtotime('+3 month')) : date('Y-m-d');
        $filters[] = "release_date.lte=$fecha_limite";
    }
}

// construcción de URL 
if ($busqueda) {
    $base_url = "https://api.themoviedb.org/3/search/movie?api_key=$api_key&language=es-ES&query=" . urlencode($busqueda) . "&page=$pagina";
} else {
    $base_url = "https://api.themoviedb.org/3/discover/movie?api_key=$api_key&language=es-ES&page=$pagina";
}

$final_url = $base_url . (count($filters) ? '&' . implode('&', $filters) : '');
$response = file_get_contents($final_url);
$data = json_decode($response, true);

$peliculas = $data['results'] ?? [];
$total_pages = $data['total_pages'] ?? 1;
$total_results = $data['total_results'] ?? 0;

// Contar filtros activos
$active_filters = count(array_filter([$genero, $anio, $pais, $duracion, $proveedor, $productora]));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Películas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explora y filtra películas por género, año, duración y más">
    <!-- pendiente cambiar el favicon con mi logo -->
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/peliculas.css">
    <!-- precargar las imagenes importantes de la base de datos -->
    <link rel="preconnect" href="https://image.tmdb.org">
</head>
<body>
    <main>
        <!-- Hero Section (es la seccion destacada en la parte superior de una web-->
        <section class="hero-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h1 class="hero-title">Catálogo de películas</h1>
                        <p class="hero-subtitle">Encuentra la película que buscas según tus preferencias</p>
                    </div>
                    <div class="col-lg-6 d-none d-lg-block">
                        <div class="hero-image-container">
                            <div class="hero-image-grid">
                                <?php
                                $popular_movies = array_slice($peliculas, 0, 4);
                                foreach ($popular_movies as $index => $movie):
                                    if (!empty($movie['backdrop_path'])):
                                ?>
                                        <div class="hero-image-item hero-image-<?= $index + 1 ?> movie-container">
                                            <img src="https://image.tmdb.org/t/p/w780<?= $movie['backdrop_path'] ?>"
                                                alt="<?= htmlspecialchars($movie['title']) ?>"
                                                class="movie-img hero-backdrop">
                                            <div class="movie-title"><?= htmlspecialchars($movie['title']) ?></div>
                                        </div>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Filtros -->
        <section class="filters-section">
            <div class="container">
                <div class="filter-container">
                    <div class="filter-header">
                        <div class="filter-title-group">
                            <h2 class="filter-title">
                                <i class="fas fa-filter me-2"></i>Filtros
                                <?php if ($active_filters > 0): ?>
                                    <span class="filter-badge"> activos: <?= $active_filters ?></span>
                                <?php endif; ?>
                            </h2>
                            <button type="button" class="btn btn-sm btn-toggle-filters" id="toggleFilters">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                    <div class="filter-body" id="filterBody">
                        <form method="get" action="" id="filterForm">
                            <div class="filters-row">
                                <div class="filter-item">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-alt me-1"></i>Año de estreno
                                    </label>
                                    <select class="form-select custom-select" name="anio">
                                        <option value="">Cualquiera</option>
                                        <?php
                                        for ($y = date('Y'); $y >= 1900; $y--) {
                                            $selected = ($anio == $y) ? 'selected' : '';
                                            echo "<option value=\"$y\" $selected>$y</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Filtro Género -->
                                <div class="filter-item">
                                    <label class="form-label">
                                        <i class="fas fa-film me-1"></i>Género
                                    </label>
                                    <select class="form-select custom-select" name="genero">
                                        <option value="">Cualquiera</option>
                                        <?php foreach ($genero_map as $key => $id): ?>
                                            <option value="<?= $key ?>" <?= ($genero == $key) ? 'selected' : '' ?>>
                                                <?= ucfirst(str_replace('-', ' ', $key)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Filtro Duración -->
                                <div class="filter-item">
                                    <label class="form-label">
                                        <i class="fas fa-clock me-1"></i>Duración
                                    </label>
                                    <select class="form-select custom-select" name="duracion">
                                        <option value="">Cualquiera</option>
                                        <option value="<60" <?= ($duracion == '<60') ? 'selected' : '' ?>>Menos de 60 min</option>
                                        <option value="60-90" <?= ($duracion == '60-90') ? 'selected' : '' ?>>60 a 90 min</option>
                                        <option value="90-120" <?= ($duracion == '90-120') ? 'selected' : '' ?>>90 a 120 min</option>
                                        <option value="120-180" <?= ($duracion == '120-180') ? 'selected' : '' ?>>120 a 180 min</option>
                                        <option value=">180" <?= ($duracion == '>180') ? 'selected' : '' ?>>Más de 180 min</option>
                                    </select>
                                </div>

                                <!-- Filtro Valoración -->
                                <div class="filter-item">
                                    <label class="form-label">
                                        <i class="fas fa-star me-1"></i>Valoración
                                    </label>
                                    <div class="rating-range"> <input type="range"
                                            class="form-range"
                                            id="ratingRange"
                                            name="rating_min"
                                            min="0"
                                            max="9"
                                            step="0.1"
                                            value="<?= htmlspecialchars($rating_min) ?>">
                                        <div class="rating-values">
                                            <span id="ratingValue"><?= $rating_min ?></span>/10
                                        </div>
                                    </div>
                                </div>

                                <!-- Filtro Certificación -->
                                <div class="filter-item">
                                    <label class="form-label">
                                        <i class="fas fa-certificate me-1"></i>Clasificación por edad
                                    </label>
                                    <select class="form-select custom-select" name="certificacion">
                                        <option value="">Cualquiera</option>
                                        <?php foreach ($certificaciones as $code => $desc): ?>
                                            <option value="<?= $code ?>" <?= ($certificacion == $code) ? 'selected' : '' ?>>
                                                <?= $desc ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Filtro Productora -->
                                <div class="filter-item">
                                    <label class="form-label">
                                        <i class="fas fa-building me-1"></i>Productora
                                    </label>
                                    <select class="form-select custom-select" name="productora">
                                        <option value="">Cualquiera</option>
                                        <option value="1" <?= (isset($_GET['productora']) && $_GET['productora'] == '1') ? 'selected' : '' ?>>Lucasfilm</option>
                                        <option value="420" <?= (isset($_GET['productora']) && $_GET['productora'] == '420') ? 'selected' : '' ?>>Marvel Studios</option>
                                        <option value="2" <?= (isset($_GET['productora']) && $_GET['productora'] == '2') ? 'selected' : '' ?>>Walt Disney</option>
                                        <option value="174" <?= (isset($_GET['productora']) && $_GET['productora'] == '174') ? 'selected' : '' ?>>Warner Bros.</option>
                                        <option value="33" <?= (isset($_GET['productora']) && $_GET['productora'] == '33') ? 'selected' : '' ?>>Universal Pictures</option>
                                        <option value="4" <?= (isset($_GET['productora']) && $_GET['productora'] == '4') ? 'selected' : '' ?>>Paramount Pictures</option>
                                        <option value="5" <?= (isset($_GET['productora']) && $_GET['productora'] == '5') ? 'selected' : '' ?>>Columbia Pictures</option>
                                        <option value="10342" <?= (isset($_GET['productora']) && $_GET['productora'] == '10342') ? 'selected' : '' ?>>Studio Ghibli</option>
                                        <option value="3" <?= (isset($_GET['productora']) && $_GET['productora'] == '3') ? 'selected' : '' ?>>Pixar</option>
                                        <option value="25" <?= (isset($_GET['productora']) && $_GET['productora'] == '25') ? 'selected' : '' ?>>20th Century Studios</option>
                                        <option value="12" <?= (isset($_GET['productora']) && $_GET['productora'] == '12') ? 'selected' : '' ?>>New Line Cinema</option>
                                        <option value="21" <?= (isset($_GET['productora']) && $_GET['productora'] == '21') ? 'selected' : '' ?>>MGM</option>
                                        <option value="9993" <?= (isset($_GET['productora']) && $_GET['productora'] == '9993') ? 'selected' : '' ?>>DC Films</option>
                                        <option value="1632" <?= (isset($_GET['productora']) && $_GET['productora'] == '1632') ? 'selected' : '' ?>>Lionsgate</option>
                                        <option value="41077" <?= (isset($_GET['productora']) && $_GET['productora'] == '41077') ? 'selected' : '' ?>>A24</option>
                                        <option value="2885" <?= (isset($_GET['productora']) && $_GET['productora'] == '2885') ? 'selected' : '' ?>>Netflix</option>
                                        <option value="521" <?= (isset($_GET['productora']) && $_GET['productora'] == '521') ? 'selected' : '' ?>>DreamWorks Animation</option>
                                        <option value="3172" <?= (isset($_GET['productora']) && $_GET['productora'] == '3172') ? 'selected' : '' ?>>Blumhouse Productions</option>
                                        <option value="513" <?= (isset($_GET['productora']) && $_GET['productora'] == '513') ? 'selected' : '' ?>>Toho</option>
                                        <option value="6704" <?= (isset($_GET['productora']) && $_GET['productora'] == '6704') ? 'selected' : '' ?>>Illumination Entertainment</option>
                                    </select>

                                </div>
                                <!-- Filtro País -->
                                <div class="filter-item">
                                    <label class="form-label">
                                        <i class="fas fa-globe-americas me-1"></i>País de producción
                                    </label>
                                    <select class="form-select custom-select" name="pais">
                                        <option value="">Cualquiera</option>
                                        <?php foreach ($paises as $code => $nombre): ?>
                                            <option value="<?= $code ?>" <?= ($pais == $code) ? 'selected' : '' ?>><?= $nombre ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div> <!-- Filtro plataforma y región -->
                                <div class="filter-item platforms-region">
                                    <label class="form-label">
                                        <i class="fas fa-tv me-1"></i>Plataforma y región
                                    </label>
                                    <div class="platforms-region-selects">
                                        <select class="form-select custom-select platform-select" name="proveedor">
                                            <option value="">Cualquiera</option>
                                            <?php foreach ($region_providers as $nombre => $id): ?>
                                                <option value="<?= $id ?>" <?= ($proveedor == $id) ? 'selected' : '' ?>><?= htmlspecialchars($nombre) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <select class="form-select custom-select region-select" name="region" id="regionSelect">
                                            <?php foreach ($paises as $code => $nombre): ?>
                                                <option value="<?= $code ?>" <?= ($region == $code) ? 'selected' : '' ?>><?= $nombre ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="filter-actions">
                                <button type="submit" class="btn btn-apply" form="filterForm">
                                    <i class="fas fa-check"></i>
                                    <span>Aplicar</span>
                                </button>
                                <a href="peliculas.php" class="btn btn-reset">
                                    <i class="fas fa-undo"></i>
                                    <span>Resetear</span>
                                </a>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </section>

        <!-- Barra de búsqueda y ordenamiento -->
        <section class="search-actions-section">
            <div class="container">
                <div class="search-actions-container">

                    <div class="search-box">

                        <div class="search-input-wrapper">
                            <input
                                type="text"
                                class="search-input"
                                name="busqueda"
                                form="filterForm"
                                placeholder="Buscar películas..."
                                value="<?= htmlspecialchars($busqueda) ?>">
                        </div>
                    </div>

                    <div class="sort-box">
                        <label for="orden" class="sort-label">
                            <i class="fas fa-sort"></i>
                            <span>Ordenar por:</span>
                        </label>
                        <select class="sort-select" name="orden" id="orden" form="filterForm">
                            <option value="">Tendencias</option>
                            <option value="release_date.desc" <?= ($_GET['orden'] ?? '') == 'release_date.desc' ? 'selected' : '' ?>>Más reciente</option>
                            <option value="release_date.asc" <?= ($_GET['orden'] ?? '') == 'release_date.asc' ? 'selected' : '' ?>>Más antiguo</option>
                            <option value="vote_average.desc" <?= ($_GET['orden'] ?? '') == 'vote_average.desc' ? 'selected' : '' ?>>Mejor valoradas</option>
                            <option value="vote_count.desc" <?= ($_GET['orden'] ?? '') == 'vote_count.desc' ? 'selected' : '' ?>>Más vistas</option>
                        </select>
                    </div>
                </div>
            </div>
        </section>
        <!-- Resultados -->
        <section class="results-section">
            <div class="container">
                <div class="results-header">
                    <div class="results-count">
                        <h3>
                            <span class="results-number"><?= number_format($total_results, 0, ',', '.') ?></span>
                            películas encontradas
                        </h3>
                    </div>
                    <div class="view-options">
                        <button class="btn btn-view active" data-view="grid">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button class="btn btn-view" data-view="list">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
                <?php if (empty($peliculas)): ?>
                    <div class="no-results">
                        <div class="no-results-icon">
                            <i class="fas fa-film-slash"></i>
                        </div>
                        <h3>No se encontraron películas</h3>
                        <p>Intenta con otros filtros o <a href="peliculas.php">resetea todos los filtros</a>.</p>
                    </div>
                <?php else: ?>
                    <!-- Vista Grid-->
                    <div class="movies-grid" id="moviesGrid">
                        <?php foreach ($peliculas as $peli): ?>
                            <div class="movie-card" data-id="<?= $peli['id'] ?>">
                                <div class="movie-poster">
                                    <?php if ($peli['poster_path']): ?>
                                        <img
                                            src="https://image.tmdb.org/t/p/w342<?= $peli['poster_path'] ?>"
                                            alt="<?= htmlspecialchars($peli['title']) ?>"
                                            loading="lazy">
                                    <?php else: ?>
                                        <div class="no-poster">
                                            <i class="fas fa-film"></i>
                                            <span>Sin imagen</span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="movie-rating">
                                        <span class="rating-value">
                                            <i class="fas fa-star"></i>
                                            <?= number_format($peli['vote_average'], 1) ?>
                                        </span>
                                    </div>
                                    <div class="movie-actions">
                                        <?php
                                        $enLista = false;
                                        if (isset($listaController) && isset($id_usuario)) {
                                            $enLista = $listaController->estaEnLista($id_usuario, $peli['id'], 'pelicula');
                                        }
                                        ?>
                                        <button class="btn-action btn-guardar <?= $enLista ? 'active' : '' ?>"
                                            title="Añadir a mi lista"
                                            data-id="<?= $peli['id'] ?>"
                                            data-tipo="pelicula">
                                            <i class="<?= $enLista ? 'fas' : 'far' ?> fa-bookmark"></i>
                                        </button>
                                        <button class="btn-action btn-details" title="Ver detalles">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="movie-info">
                                    <div class="movie-meta">
                                        <?php if (!empty($peli['release_date'])): ?>
                                            <span class="movie-year"><?= date('Y', strtotime($peli['release_date'])) ?></span>
                                            <h3 class="movie-title"><?= htmlspecialchars($peli['title']) ?></h3>
                                        <?php endif; ?>
                                    </div>


                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Vista lista -->
                    <div class="movies-list" id="moviesList" style="display: none;">
                        <?php foreach ($peliculas as $peli): ?>
                            <div class="movie-list-item" data-id="<?= $peli['id'] ?>">
                                <div class="movie-list-poster">
                                    <?php if ($peli['poster_path']): ?>
                                        <img
                                            src="https://image.tmdb.org/t/p/w154<?= $peli['poster_path'] ?>"
                                            alt="<?= htmlspecialchars($peli['title']) ?>"
                                            loading="lazy">
                                    <?php else: ?>
                                        <div class="no-poster">
                                            <i class="fas fa-film"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="movie-list-info">
                                    <h3 class="movie-list-title"><?= htmlspecialchars($peli['title']) ?></h3>
                                    <div class="movie-list-meta">
                                        <?php if (!empty($peli['release_date'])): ?>
                                            <span class="movie-list-year">
                                                <i class="fas fa-calendar-alt"></i>
                                                <?= 'Estreno: ' . date('d/m/Y', strtotime($peli['release_date'])) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="movie-list-rating">
                                            <i class="fas fa-star"></i>
                                            <?= number_format($peli['vote_average'], 1) ?>
                                        </span>
                                    </div>
                                    <p class="movie-list-overview">
                                        <?= !empty($peli['overview']) ?
                                            (strlen($peli['overview']) > 500 ?
                                                substr(htmlspecialchars($peli['overview']), 0, 200) . '...' :
                                                htmlspecialchars($peli['overview'])) :
                                            'No hay descripción disponible.' ?>
                                    </p>
                                </div>
                                <div class="movie-list-actions">
                                    <?php
                                    $enLista = false;
                                    if (isset($listaController) && isset($id_usuario)) {
                                        $enLista = $listaController->estaEnLista($id_usuario, $peli['id'], 'pelicula');
                                    }
                                    ?>
                                    <button class="btn-action btn-guardar <?= $enLista ? 'active' : '' ?>"
                                        title="Añadir a mi lista"
                                        data-id="<?= $peli['id'] ?>"
                                        data-tipo="pelicula">
                                        <i class="<?= $enLista ? 'fas' : 'far' ?> fa-bookmark"></i>
                                    </button>
                                    <button class="btn-action btn-details" title="Ver detalles">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <!-- Volver arriba -->
    <button id="backToTop" class="back-to-top" title="Volver arriba">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Indicador de carga (scroll inifnito) -->
    <div id="infiniteLoader" class="loading-indicator" style="display:none;">
        <div class="loading-animation">
            <div class="spinner"></div>
            <p class="loading-text">Cargando más películas...</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const els = {
                toggleFiltersBtn: document.getElementById('toggleFilters'),
                filterBody: document.getElementById('filterBody'),
                regionSelect: document.getElementById('regionSelect'),
                viewButtons: document.querySelectorAll('.btn-view'),
                moviesGrid: document.getElementById('moviesGrid'),
                moviesList: document.getElementById('moviesList'),
                backToTopButton: document.getElementById('backToTop'),
                ratingRange: document.getElementById('ratingRange'),
                ratingValue: document.getElementById('ratingValue')
            }; 

            const updateFilterState = () => {
                const activeFilters = document.querySelectorAll('.filter-item select:not([value=""]), .filter-item input:not([value="0"])');
                activeFilters.forEach(filter => {
                    filter.closest('.filter-item').classList.add('active');
                });
            };

            // animacion cerrar filtros
            els.toggleFiltersBtn?.addEventListener('click', () => {
                els.filterBody.classList.toggle('collapsed');
                const icon = els.toggleFiltersBtn.querySelector('i');
                icon.classList.toggle('fa-chevron-down');
                icon.classList.toggle('fa-chevron-up');

                // animacion suave
                if (!els.filterBody.classList.contains('collapsed')) {
                    els.filterBody.style.height = els.filterBody.scrollHeight + 'px';
                    setTimeout(() => {
                        els.filterBody.style.height = 'auto';
                    }, 300);
                } else {
                    els.filterBody.style.height = els.filterBody.scrollHeight + 'px';
                    requestAnimationFrame(() => {
                        els.filterBody.style.height = '0';
                    });
                }
            });

            document.querySelectorAll('.filter-item select, .filter-item input').forEach(filter => {
                filter.addEventListener('change', function() {
                    const filterItem = this.closest('.filter-item');
                    if (this.value && this.value !== '0') {
                        filterItem.classList.add('active');
                    } else {
                        filterItem.classList.remove('active');
                    }
                });
            });
            updateFilterState();

            els.regionSelect?.addEventListener('change', () => document.getElementById('filterForm').submit());
            
            const ordenSelect = document.getElementById('orden');
            ordenSelect?.addEventListener('change', () => document.getElementById('filterForm').submit());

            els.viewButtons.forEach(btn => btn.addEventListener('click', function() {
                const view = this.dataset.view;
                els.viewButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                els.moviesGrid.style.display = view === 'grid' ? 'grid' : 'none';
                els.moviesList.style.display = view === 'grid' ? 'none' : 'block';
                localStorage.setItem('preferredView', view);
            }));

            window.addEventListener('scroll', () => {
                els.backToTopButton.classList.toggle('show', window.pageYOffset > 300);
                if (!isLoading && currentPage < totalPages &&
                    (window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 400)) loadMoreMovies();
            });

            els.backToTopButton?.addEventListener('click', () => window.scrollTo({
                top: 0,
                behavior: 'smooth'
            }));

            const savedView = localStorage.getItem('preferredView');
            if (savedView) document.querySelector(`.btn-view[data-view="${savedView}"]`)?.click();

            const hero = document.querySelector('.hero-image-grid');
            if (hero) {
                const title = document.createElement('h2');
                title.textContent = 'Películas destacadas';
                title.classList.add('popular-title');
                hero.parentNode.insertBefore(title, hero);
            } 
            //slider rating
            if (els.ratingRange && els.ratingValue) {
                const updateSlider = (value) => {
                    els.ratingValue.textContent = value;
                    // calcular porcentaje para gradient
                    const percentage = (value / els.ratingRange.max) * 100;
                    els.ratingRange.style.setProperty('--rating-percentage', `${percentage}%`);
                };

                updateSlider(els.ratingRange.value);

                // actalizar mientras se hace slide
                els.ratingRange.addEventListener('input', (e) => {
                    updateSlider(e.target.value);
                });

                // actualizar cuando deja de hacer slide
                els.ratingRange.addEventListener('change', (e) => {
                    updateSlider(e.target.value);
                });
            }

            attachDynamicEvents();
        });

        let currentPage = <?= (int)$pagina ?>,
            totalPages = <?= (int)$total_pages ?>,
            isLoading = false;
        const infiniteLoader = document.getElementById('infiniteLoader');

        function verificarLogin() {
            <?php if (!$nombre_usuario): ?>
                alert('Debes iniciar sesión para usar esta función');
                return false;
            <?php else: ?>
                return true;
            <?php endif; ?>
        }

        function appendMovies(movies, view) {
            const grid = document.getElementById('moviesGrid');
            const list = document.getElementById('moviesList');

            movies.forEach(peli => {
                const year = peli.release_date ? `<span class="movie-${view === 'list' ? 'list-' : ''}year">${new Date(peli.release_date).getFullYear()}</span>` : '';
                const poster = peli.poster_path ?
                    `<img loading="lazy" src="https://image.tmdb.org/t/p/${view === 'list' ? 'w154' : 'w342'}${peli.poster_path}" alt="${peli.title.replace(/"/g, '&quot;')}">` :
                    `<div class="no-poster">${view === 'list' ? '<i class="fas fa-film"></i>' : '<span>Sin imagen</span>'}</div>`;
                const bookmarkBtn = `<button class="btn-action btn-guardar${peli.enLista ? ' active' : ''}" title="Añadir a mi lista" data-id="${peli.id}" data-tipo="pelicula"><i class="${peli.enLista ? 'fas' : 'far'} fa-bookmark"></i></button>`;
                const detailsBtn = `<button class="btn-action btn-details" title="Ver detalles"><i class="fas fa-info-circle"></i></button>`;

                if (view !== 'list') {
                    const card = document.createElement('div');
                    card.className = 'movie-card';
                    card.setAttribute('data-id', peli.id);
                    card.innerHTML = `<div class="movie-poster">${poster}<div class="movie-rating"><span class="rating-value"><i class="fas fa-star"></i> ${parseFloat(peli.vote_average).toFixed(1)}</span></div><div class="movie-actions">${bookmarkBtn}${detailsBtn}</div></div><div class="movie-info"><div class="movie-meta">${year}<h3 class="movie-title">${peli.title}</h3></div></div>`;
                    grid.appendChild(card);
                }

                const listItem = document.createElement('div');
                listItem.className = 'movie-list-item';
                listItem.setAttribute('data-id', peli.id);
                listItem.innerHTML = `<div class="movie-list-poster">${poster}</div><div class="movie-list-info"><h3 class="movie-list-title">${peli.title}</h3><div class="movie-list-meta">${year}<span class="movie-list-rating"><i class="fas fa-star"></i> ${parseFloat(peli.vote_average).toFixed(1)}</span></div><p class="movie-list-overview">${peli.overview ? (peli.overview.length > 200 ? peli.overview.substring(0, 200) + '...' : peli.overview) : 'No hay descripción disponible.'}</p></div><div class="movie-list-actions">${bookmarkBtn}${detailsBtn}</div>`;
                list.appendChild(listItem);
            });
        }

        function attachDynamicEvents() {
            document.querySelectorAll('.btn-details').forEach(btn => {
                btn.onclick = () => window.location.href = `pelicula_detalle.php?id=${btn.closest('[data-id]').getAttribute('data-id')}`;
            });

            document.querySelectorAll('.btn-guardar').forEach(btn => {
                btn.onclick = () => {
                    if (!verificarLogin()) return;
                    const movieId = btn.getAttribute('data-id');
                    const tipo = btn.getAttribute('data-tipo');
                    const icon = btn.querySelector('i');

                    fetch('../ajax/toggle_lista.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `api_id=${movieId}&categoria=${tipo}`
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                icon.classList.toggle('far');
                                icon.classList.toggle('fas');
                                btn.classList.toggle('active');
                            } else alert(data.message || 'Error al procesar la solicitud');
                        })
                        .catch(e => alert('Error al procesar la solicitud: ' + e.message));
                };
            });
        }
        const loadMoreMovies = () => {
            if (isLoading || currentPage >= totalPages) return;
            isLoading = true;
            infiniteLoader.style.display = 'block';

            fetch(`../ajax/peliculas_infinite.php?page=${currentPage + 1}&<?= http_build_query($_GET) ?>`)
                .then(res => res.json())
                .then(data => {
                    if (data.peliculas?.length) {
                        appendMovies(data.peliculas, localStorage.getItem('preferredView') || 'grid');
                        attachDynamicEvents();
                        currentPage++;
                    }
                })
                .finally(() => {
                    isLoading = false;
                    infiniteLoader.style.display = 'none';
                });
        };
    </script>
</body>
</html>