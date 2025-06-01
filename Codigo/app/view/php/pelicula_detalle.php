<?php
require_once(__DIR__ . '/../../../rutas.php');
require_once(CONFIG . 'sesion.php');
$nombre_usuario = $_SESSION['nombre_usuario'] ?? null;
include '../Generales/nav.php';

if (empty($_GET['id'])) {
    header('Location: peliculas.php');
    exit;
}

$movie_id = intval($_GET['id']);
$esFavorito = $esVisto = $tieneResena = false;
$resenaUsuario = $avatarUsuario = null;
$language = 'es-ES';
$movie_url = "https://api.themoviedb.org/3/movie/{$movie_id}?api_key={$api_key}&language={$language}&append_to_response=credits,videos,images,recommendations,similar,keywords,watch/providers";
$movie_response = @file_get_contents($movie_url);
if (!$movie_response) {
    header('Location: peliculas.php');
    exit;
}

if ($nombre_usuario) {
    require_once(CONTROLLER . 'UsuarioController.php');
    require_once(CONTROLLER . 'FavoritosController.php');
    require_once(CONTROLLER . 'VisualizacionController.php');
    require_once(CONTROLLER . 'ListaController.php');
    $usuarioController = new UsuarioController();
    $usuario = $usuarioController->getUserByName($nombre_usuario);
    if ($usuario) {
        $id_usuario = $usuario->getIdUsuario();
        $favoritoController = new FavoritosController();
        $esFavorito = $favoritoController->esFavorito($id_usuario, $movie_id, 'pelicula');
        $visualizacionController = new VisualizacionController();
        $esVisto = $visualizacionController->esVisto($id_usuario, $movie_id, 'pelicula');
        $listaController = new ListaController();
        $estaEnLista = $listaController->estaEnLista($id_usuario, $movie_id, 'pelicula');
    }
}
if ($nombre_usuario && isset($usuario)) {
    require_once(CONTROLLER . 'ResenaController.php');
    require_once(MODEL . 'Resena.php');
    $resenaController = new ResenaController();
    $tieneResena = $resenaController->usuarioTieneResena($id_usuario, $movie_id, 'película');
    if ($tieneResena) {
        $resenaUsuario = $resenaController->getResenaUsuario($id_usuario, $movie_id, 'película');
        $avatarUsuario = $usuario->getAvatar() ?: '1default-avatar.jpg';
        $usuarioDioLike = Resena::usuarioDioLike($id_usuario, $resenaUsuario->getIdResena());
    }
}
$resenas = [];
if (isset($resenaController)) {
    $resenas = $resenaController->getResenasByApiId($movie_id, 'película');
    $unique = [];
    foreach ($resenas as $resena) {
        $unique[$resena['id_reseña']] = $resena;
    }
    $resenas = array_values($unique);
    foreach ($resenas as &$resena) {
        $resena['usuarioDioLike'] = $nombre_usuario ? Resena::usuarioDioLike($id_usuario, $resena['id_reseña']) : false;
    }
    unset($resena);
}
$movie = json_decode($movie_response, true);
$release_year = !empty($movie['release_date']) ? date('Y', strtotime($movie['release_date'])) : 'N/A';

function formatRuntime($minutes) {
    return $minutes < 60 ? "{$minutes} min" : floor($minutes / 60) . "h " . ($minutes % 60) . "min";
}
function formatMoney($amount) {
    return $amount == 0 ? "No disponible" : "$" . number_format($amount, 0, ',', '.');
}

$director = "No disponible";
if (!empty($movie['credits']['crew'])) {
    foreach ($movie['credits']['crew'] as $crew) {
        if ($crew['job'] === 'Director') {
            $director = $crew['name'];
            break;
        }
    }
}
$cast = !empty($movie['credits']['cast']) ? array_slice($movie['credits']['cast'], 0, 6) : [];
$providers = $movie['watch/providers']['results']['ES']['flatrate'] ?? [];
$posters = !empty($movie['images']['posters']) ? array_slice($movie['images']['posters'], 0, 10) : [];
$backdrops = !empty($movie['images']['backdrops']) ? array_slice($movie['images']['backdrops'], 0, 5) : [];
$videos = [];
if (!empty($movie['videos']['results'])) {
    foreach ($movie['videos']['results'] as $video) {
        if ($video['site'] === 'YouTube' && ($video['type'] === 'Trailer' || $video['type'] === 'Teaser')) {
            $videos[] = $video;
        }
    }
    $videos = array_slice($videos, 0, 3);
}
$similar_movies = !empty($movie['similar']['results']) ? array_slice($movie['similar']['results'], 0, 6) : [];
$recommendations = !empty($movie['recommendations']['results']) ? array_slice($movie['recommendations']['results'], 0, 6) : [];
$keywords = $movie['keywords']['keywords'] ?? [];
$languages = [
    'en' => 'Inglés', 'es' => 'Español', 'fr' => 'Francés', 'de' => 'Alemán',
    'it' => 'Italiano', 'ja' => 'Japonés', 'ko' => 'Coreano', 'zh' => 'Chino', 'ru' => 'Ruso'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($movie['title']) ?> (<?= $release_year ?>) - Detalles</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars(substr($movie['overview'], 0, 160)) ?>">
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <link rel="stylesheet" href="../CSS/peliculas.css">
    <link rel="stylesheet" href="../CSS/pelicula_detalle.css">
    <link rel="preconnect" href="https://image.tmdb.org">
</head>
<body>
    <main>
        <section class="movie-hero" style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.8), var(--black-dark)), url('https://image.tmdb.org/t/p/original<?= $movie['backdrop_path'] ?>');">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 col-lg-3">
                        <div class="movie-poster-container">
                            <?php if ($movie['poster_path']): ?>
                                <img src="https://image.tmdb.org/t/p/w500<?= $movie['poster_path'] ?>" alt="<?= htmlspecialchars($movie['title']) ?>" class="movie-poster-main">
                            <?php else: ?>
                                <div class="no-poster"><i class="fas fa-film"></i><span>Sin imagen</span></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-8 col-lg-9">
                        <div class="movie-hero-content">
                            <h1 class="movie-title">
                                <?= htmlspecialchars($movie['title']) ?>
                                <span class="movie-year2">(<?= $release_year ?>)</span>
                            </h1>

                            <?php if (!empty($movie['tagline'])): ?>
                                <div class="movie-tagline"><?= htmlspecialchars($movie['tagline']) ?></div>
                            <?php endif; ?>

                            <div class="movie-meta">
                                <?php if (!empty($movie['release_date'])): ?>
                                    <span class="movie-meta-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?= date('d/m/Y', strtotime($movie['release_date'])) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($movie['runtime'])): ?>
                                    <span class="movie-meta-item">
                                        <i class="fas fa-clock"></i>
                                        <?= formatRuntime($movie['runtime']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($movie['vote_average'])): ?>
                                    <span class="movie-meta-item movie-rating2" title="TMDB Rating">
                                        <i class="fas fa-star"></i>
                                        <?= number_format($movie['vote_average'], 1) ?>
                                        <span style="font-size:0.95em;color:var(--gray-light);">TMDB</span>
                                        (<?= number_format($movie['vote_count'], 0, ',', '.') ?> votos)
                                    </span>
                                <?php endif; ?>
                                <?php
                                $totalResenas = count($resenas);
                                $avgRecomendApp = $totalResenas > 0 ? array_sum(array_column($resenas, 'puntuacion')) / $totalResenas : 0;
                                ?>
                                <span class="movie-meta-item movie-rating2" title="RecomendApp Rating">
                                    <a href="#reseñas-section" id="recomendapp-rating-link" style="color:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                        <i class="fas fa-star-half-alt" style="color:var(--orange);"></i>
                                        <?= $totalResenas > 0 ? number_format($avgRecomendApp, 1) : '--' ?>
                                        <span style="font-size:0.95em;color:var(--gray-light);">RecomendApp</span>
                                        <span style="font-size:0.95em;color:var(--green);">(<?= $totalResenas ?> reseñas)</span>
                                    </a>
                                </span>
                            </div>

                            <div class="movie-genres">
                                <?php foreach ($movie['genres'] as $genre): ?>
                                    <span class="movie-genre"><?= htmlspecialchars($genre['name']) ?></span>
                                <?php endforeach; ?>
                            </div>

                            <?php if (!empty($movie['overview'])): ?>
                                <div class="movie-overview">
                                    <h3>Sinopsis</h3>
                                    <p><?= htmlspecialchars($movie['overview']) ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="movie-actions2">
                                <?php if (!empty($videos)): ?>
                                    <button class="btn btn-primary btn-trailer" data-bs-toggle="modal" data-bs-target="#trailerModal">
                                        <i class="fas fa-play-circle"></i> Ver Tráiler
                                    </button>
                                <?php endif; ?>
                                <?php if (!empty($movie['homepage'])): ?>
                                    <a href="<?= htmlspecialchars($movie['homepage']) ?>" target="_blank" class="btn btn-outline-light">
                                        <i class="fas fa-external-link-alt"></i> Sitio Oficial
                                    </a>
                                <?php endif; ?>

                                <button class="btn btn-outline-light btn-favorite <?= $esFavorito ? 'active' : '' ?>" data-movie-id="<?= $movie_id ?>" data-categoria="pelicula">
                                    <i class="<?= $esFavorito ? 'fas' : 'far' ?> fa-heart"></i>
                                </button>

                                <button class="btn btn-outline-light btn-vista <?= $esVisto ? 'active' : '' ?>" data-movie-id="<?= $movie_id ?>" data-categoria="pelicula">
                                    <i class="<?= $esVisto ? 'fas' : 'far' ?> fa-eye"></i>
                                </button>

                                <button class="btn btn-outline-light btn-guardar <?= $estaEnLista ? 'active' : '' ?>" data-movie-id="<?= $movie_id ?>" data-categoria="pelicula">
                                    <i class="<?= $estaEnLista ? 'fas' : 'far' ?> fa-bookmark"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="movie-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <?php if (!empty($posters)): ?>
                            <div class="content-section">
                                <h2 class="section-title">Galería de Imágenes</h2>
                                <div class="swiper-container poster-carousel">
                                    <div class="swiper-wrapper">
                                        <?php foreach ($posters as $poster): ?>
                                            <div class="swiper-slide">
                                                <img
                                                    src="https://image.tmdb.org/t/p/w342<?= $poster['file_path'] ?>"
                                                    alt="Poster de <?= htmlspecialchars($movie['title']) ?>"
                                                    class="poster-image"
                                                    data-full-image="https://image.tmdb.org/t/p/original<?= $poster['file_path'] ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="swiper-pagination"></div>
                                    <div class="swiper-button-next"></div>
                                    <div class="swiper-button-prev"></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($cast)): ?>
                            <div class="content-section">
                                <h2 class="section-title">Reparto Principal</h2>
                                <div class="cast-grid">
                                    <?php foreach ($cast as $actor): ?>
                                        <div class="cast-card">
                                            <?php if ($actor['profile_path']): ?>
                                                <img src="https://image.tmdb.org/t/p/w185<?= $actor['profile_path'] ?>" alt="<?= htmlspecialchars($actor['name']) ?>" class="cast-image">
                                            <?php else: ?>
                                                <div class="no-profile"><i class="fas fa-user"></i></div>
                                            <?php endif; ?>
                                            <div class="cast-info">
                                                <h4 class="cast-name"><?= htmlspecialchars($actor['name']) ?></h4>
                                                <p class="cast-character"><?= htmlspecialchars($actor['character']) ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($videos)): ?>
                            <div class="content-section">
                                <h2 class="section-title">Videos</h2>
                                <div class="videos-grid">
                                    <?php foreach ($videos as $video): ?>
                                        <div class="video-card">
                                            <div class="video-thumbnail" data-video-id="<?= $video['key'] ?>">
                                                <img src="https://img.youtube.com/vi/<?= $video['key'] ?>/mqdefault.jpg" alt="<?= htmlspecialchars($video['name']) ?>">
                                                <div class="play-button"><i class="fas fa-play"></i></div>
                                            </div>
                                            <h4 class="video-title"><?= htmlspecialchars($video['name']) ?></h4>
                                            <p class="video-type"><?= $video['type'] ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($similar_movies)): ?>
                            <div class="content-section">
                                <h2 class="section-title">Películas Similares</h2>
                                <div class="similar-movies-grid">
                                    <?php foreach ($similar_movies as $similar): ?>
                                        <div class="similar-movie-card">
                                            <a href="pelicula_detalle.php?id=<?= $similar['id'] ?>">
                                                <?php if ($similar['poster_path']): ?>
                                                    <img src="https://image.tmdb.org/t/p/w185<?= $similar['poster_path'] ?>" alt="<?= htmlspecialchars($similar['title']) ?>" class="similar-movie-poster">
                                                <?php else: ?>
                                                    <div class="no-poster"><i class="fas fa-film"></i></div>
                                                <?php endif; ?>
                                                <h4 class="similar-movie-title"><?= htmlspecialchars($similar['title']) ?></h4>
                                                <?php if (!empty($similar['release_date'])): ?>
                                                    <p class="similar-movie-year"><?= date('Y', strtotime($similar['release_date'])) ?></p>
                                                <?php endif; ?>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="content-section" id="reseñas-section">
                            <h2 class="section-title">Reseñas de usuarios</h2>
                            <?php if (!$nombre_usuario): ?>
                                <div class="text-center py-4">
                                    <p class="fechaReseña">Inicia sesión para ver las reseñas</p>
                                </div>
                            <?php else: ?>
                                <?php if ($nombre_usuario): ?>
                                    <div class="review-form-container mb-4">
                                        <?php if (!$tieneResena): ?>
                                            <h4>Escribe tu reseña</h4>
                                            <form id="reviewForm" class="review-form">
                                                <input type="hidden" name="api_id" value="<?= $movie_id ?>">
                                                <input type="hidden" name="categoria" value="película">
                                                <div class="mb-3">
                                                    <label for="puntuacion" class="form-label">Puntuación</label>
                                                    <div class="rating-stars">
                                                        <div class="star-rating">
                                                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                                                <input type="radio" id="rating-<?= $i ?>" name="puntuacion" value="<?= $i ?>" <?= $i == 5 ? 'checked' : '' ?>>
                                                                <label for="rating-<?= $i ?>"><i class="fas fa-star"></i></label>
                                                            <?php endfor; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="texto" class="form-label">Tu opinión</label>
                                                    <textarea class="form-control" id="texto" name="texto" rows="4" placeholder="Comparte tu opinión sobre esta película..."></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Publicar reseña</button>
                                            </form>
                                        <?php else: ?>
                                            <h4>Tu reseña</h4>
                                            <div id="userReview">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <div>
                                                                <div class="review-avatar me-3">
                                                            <img src="../Images/avatars/<?= htmlspecialchars($avatar) ?>" class="rounded-circle" width="50" height="50">
                                                        <span class="ms-2">
                                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                        <i class="fas fa-star <?= $i <= $resenaUsuario->getPuntuacion() ? 'text-warning' : 'text-muted' ?>"></i>
                                                                    <?php endfor; ?>
                                                                </span>
                                                        </div>
                                                            </div>
                                                            <div>
                                                                <button class="btn btn-sm btn-outline-primary edit-review-btn" type="button"><i class="fas fa-edit"></i> Editar</button>
                                                                <button class="btn btn-sm btn-outline-danger delete-review-btn" data-id="<?= $resenaUsuario->getIdResena() ?>"><i class="fas fa-trash"></i> Eliminar</button>
                                                            </div>
                                                        </div>
                                                        <p class="review-text" style="overflow-wrap: break-word; word-break: break-word; white-space: pre-line; width: 100%; max-width: 100%; display: block; box-sizing: border-box; margin-bottom: 10px;"><?= htmlspecialchars(ltrim($resenaUsuario->getTexto(), "\r\n"))?></p>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <small class="fechaReseña"><?= date('d/m/Y H:i', strtotime($resenaUsuario->getFecha())) ?></small>
                                                            <div class="like-button-container">
                                                                <button class="btn btn-outline-light btn-like-review <?= !empty($usuarioDioLike) ? 'active' : '' ?>" data-id="<?= $resenaUsuario->getIdResena() ?>">
                                                                    <i class="<?= !empty($usuarioDioLike) ? 'fas' : 'far' ?> fa-heart"></i>
                                                                    <span class="likes-count"><?= $resenaUsuario->getLikes() ?></span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <form id="editReviewForm" class="review-form d-none">
                                                <input type="hidden" name="api_id" value="<?= $movie_id ?>">
                                                <input type="hidden" name="categoria" value="película">
                                                <input type="hidden" name="accion" value="actualizar">
                                                <input type="hidden" name="id_resena" value="<?= $resenaUsuario->getIdResena() ?>">

                                                <div class="mb-3">
                                                    <label for="edit_puntuacion" class="form-label">Puntuación</label>
                                                    <div class="rating-stars">
                                                        <div class="star-rating">
                                                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                                                <input type="radio" id="edit-rating-<?= $i ?>" name="puntuacion" value="<?= $i ?>" <?= $i == $resenaUsuario->getPuntuacion() ? 'checked' : '' ?>>
                                                                <label for="edit-rating-<?= $i ?>"><i class="fas fa-star"></i></label>
                                                            <?php endfor; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="edit_texto" class="form-label">Tu opinión</label>
                                                    <textarea class="form-control" id="edit_texto" name="texto" rows="4"><?= htmlspecialchars($resenaUsuario->getTexto()) ?></textarea>
                                                </div>

                                                <div>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-save"></i> Confirmar
                                                    </button>
                                                    <button type="button" class="btn btn-secondary cancel-edit-btn">
                                                        <i class="fas fa-times"></i> Cancelar
                                                    </button>
                                                </div>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="reviews-container">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="mb-0">
                                            <?php
                                                if ($nombre_usuario) {
                                                    $numResenas = 0;
                                                    foreach ($resenas as $resena) {
                                                        if ($resena['id_usuario'] != $id_usuario) {
                                                            $numResenas++;
                                                        }
                                                    }
                                                } else {
                                                    $numResenas = is_array($resenas) ? count($resenas) : 0;
                                                }
                                                echo $numResenas . ' reseñas de usuarios';
                                            ?>
                                        </h4>
                                        <form method="get" class="d-flex align-items-center filter-container py-2 px-3 mb-0" id="sortReviewsForm" style="background: var(--dark-gray); border: 1px solid var(--light-gray); border-radius: var(--border-radius-md);">
                                            <input type="hidden" name="id" value="<?= $movie_id ?>">
                                            <label for="sortReviews" class="me-2 mb-0 filter-title" style="color: var(--white); font-weight: 500;">Ordenar por:</label>
                                            <select name="sort" id="sortReviews" class="form-select form-select-sm" style="width:auto; min-width: 150px; background: var(--black-dark); color: var(--white); border: 1px solid var(--green);">
                                                <option value="recientes" <?= (!isset($_GET['sort']) || $_GET['sort'] === 'recientes') ? 'selected' : '' ?>>Más recientes</option>
                                                <option value="antiguas" <?= (isset($_GET['sort']) && $_GET['sort'] === 'antiguas') ? 'selected' : '' ?>>Más antiguas</option>
                                                <option value="likeados" <?= (isset($_GET['sort']) && $_GET['sort'] === 'likeados') ? 'selected' : '' ?>>Más likeados</option>
                                            </select>
                                        </form>
                                    </div>
                                    <?php if ($numResenas === 0): ?>
                                        <div class="text-center py-4">
                                            <p class="fechaReseña">Aún no hay reseñas para esta película. ¡Sé el primero en opinar!</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="reviews-list">
                                            <?php foreach ($resenas as $resena):
                                                if ($nombre_usuario && $resena['id_usuario'] == $id_usuario) continue;
                                            ?>
                                                <div class="review-item mb-4">
                                                    <div class="d-flex">
                                                        <div class="review-avatar me-3">
                                                            <img src="../Images/avatars/<?= htmlspecialchars($resena['avatar']) ?>" alt="<?= htmlspecialchars($resena['nombre_usuario']) ?>" class="rounded-circle" width="50" height="50">
                                                        </div>
                                                        <div class="review-content w-100">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <div>
                                                                    <h5 class="mb-0"><?= htmlspecialchars($resena['nombre_usuario']) ?></h5>
                                                                    <div class="rating-display">
                                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                            <i class="fas fa-star <?= $i <= $resena['puntuacion'] ? 'text-warning' : 'text-muted' ?>"></i>
                                                                        <?php endfor; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="like-button-container">
                                                                    <?php if ($nombre_usuario): ?>
                                                                        <button type="button" class="btn btn-outline-light btn-like-review <?= $resena['usuarioDioLike'] ? 'active' : '' ?>" data-id="<?= $resena['id_reseña'] ?>">
                                                                            <i class="<?= $resena['usuarioDioLike'] ? 'fas' : 'far' ?> fa-heart"></i>
                                                                            <span class="likes-count"><?= $resena['likes'] ?></span>
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <span class="likes-count" style="color:var(--green);font-weight:600;"><i class="far fa-heart"></i> <?= $resena['likes'] ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <p class="review-text" style="overflow-wrap: break-word; word-break: break-word; white-space: pre-line; width: 100%; max-width: 100%; display: block; box-sizing: border-box; margin-bottom: 10px;"><?= htmlspecialchars(ltrim($resena['texto'], "\r\n"))?></p>
                                                            <small class="fechaReseña"><?= date('d/m/Y H:i', strtotime($resena['fecha'])) ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="sidebar">
                            <div class="sidebar-section">
                                <h3 class="sidebar-title">Información</h3>
                                <ul class="info-list">
                                    <li>
                                        <span class="info-label">Estado:</span>
                                        <span class="info-value"><?= htmlspecialchars($movie['status']) ?></span>
                                    </li>
                                    <li>
                                        <span class="info-label">Idioma original:</span>
                                        <span class="info-value">
                                            <?= isset($languages[$movie['original_language']])
                                                ? $languages[$movie['original_language']]
                                                : $movie['original_language'] ?>
                                        </span>
                                    </li>
                                    <li>
                                        <span class="info-label">Presupuesto:</span>
                                        <span class="info-value"><?= formatMoney($movie['budget']) ?></span>
                                    </li>
                                    <li>
                                        <span class="info-label">Ingresos:</span>
                                        <span class="info-value"><?= formatMoney($movie['revenue']) ?></span>
                                    </li>
                                    <?php if (!empty($director)): ?>
                                        <li>
                                            <span class="info-label">Director:</span>
                                            <span class="info-value"><?= htmlspecialchars($director) ?></span>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (!empty($movie['production_companies'])): ?>
                                        <li>
                                            <span class="info-label">Productoras:</span>
                                            <span class="info-value">
                                                <?= implode(', ', array_map(function ($company) {
                                                    return htmlspecialchars($company['name']);
                                                }, $movie['production_companies'])) ?>
                                            </span>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (!empty($movie['production_countries'])): ?>
                                        <li>
                                            <span class="info-label">Países:</span>
                                            <span class="info-value">
                                                <?= implode(', ', array_map(function ($country) {
                                                    return htmlspecialchars($country['name']);
                                                }, $movie['production_countries'])) ?>
                                            </span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <?php if (!empty($providers)): ?>
                                <div class="sidebar-section">
                                    <h3 class="sidebar-title">Dónde ver</h3>
                                    <div class="providers-list">
                                        <?php foreach ($providers as $provider): ?>
                                            <div class="provider-item">
                                                <?php if ($provider['logo_path']): ?>
                                                    <img src="https://image.tmdb.org/t/p/original<?= $provider['logo_path'] ?>" alt="<?= htmlspecialchars($provider['provider_name']) ?>" class="provider-logo">
                                                <?php endif; ?>
                                                <span class="provider-name"><?= htmlspecialchars($provider['provider_name']) ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($keywords)): ?>
                                <div class="sidebar-section">
                                    <h3 class="sidebar-title">Palabras clave</h3>
                                    <div class="keywords-list">
                                        <?php foreach ($keywords as $keyword): ?>
                                            <span class="keyword-badge"><?= htmlspecialchars($keyword['name']) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($recommendations)): ?>
                                <div class="sidebar-section">
                                    <h3 class="sidebar-title">Recomendaciones</h3>
                                    <div class="recommendations-list">
                                        <?php foreach ($recommendations as $rec): ?>
                                            <a href="pelicula_detalle.php?id=<?= $rec['id'] ?>" class="recommendation-item">
                                                <?php if ($rec['backdrop_path']): ?>
                                                    <img src="https://image.tmdb.org/t/p/w300<?= $rec['backdrop_path'] ?>" alt="<?= htmlspecialchars($rec['title']) ?>" class="recommendation-image">
                                                <?php else: ?>
                                                    <div class="no-backdrop"><i class="fas fa-film"></i></div>
                                                <?php endif; ?>
                                                <div class="recommendation-info">
                                                    <h4 class="recommendation-title"><?= htmlspecialchars($rec['title']) ?></h4>
                                                    <?php if (!empty($rec['release_date'])): ?>
                                                        <p class="recommendation-year"><?= date('Y', strtotime($rec['release_date'])) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div class="modal fade" id="trailerModal" tabindex="-1" aria-labelledby="trailerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="trailerModalLabel">Tráiler: <?= htmlspecialchars($movie['title']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <?php if (!empty($videos)): ?>
                        <div class="ratio ratio-16x9">
                            <iframe id="trailerIframe" src="/placeholder.svg" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    <?php else: ?>
                        <div class="p-4 text-center">
                            <p>No hay tráilers disponibles para esta película.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Imagen de <?= htmlspecialchars($movie['title']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 text-center">
                    <img id="fullSizeImage" src="/placeholder.svg" alt="Imagen en tamaño completo" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="../js/pelicula_detalle.js"></script>
</body>
<?php include '../Generales/footer.php'; ?>
</html>
