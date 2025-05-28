<?php
require_once(__DIR__ . '/../../../rutas.php');
require_once(CONFIG . 'sesion.php'); 
$nombre_usuario = $_SESSION['nombre_usuario'] ?? null;
include '../Generales/nav.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RecomendApp</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
   <link rel="stylesheet" href="../Generales/variables.css">

  <link rel="stylesheet" href="../CSS/inicio.css">
</head>
<body>
  <div class="hero-section">
    <div class="hero-content container">
      <h1 class="hero-title">RecomendApp</h1>
      <p class="hero-subtitle">Descubre y lleva un seguimiento de tu contenido favorito</p>
      <div class="hero-buttons">
        <a href="peliculas.php" class="btn btn-primary btn-lg">Explorar Películas</a>
        <a href="series.php" class="btn btn-primary btn-lg">Explorar Series</a>
      </div>
    </div>
    <div class="hero-overlay"></div> <!-- overlay para oscurecer el hero section -->
  </div>

  <main class="container py-5">
    <div class="featured-content mb-5">
      <div class="section-header">
        <h2 id="peliculas" class="section-title">Películas del momento</h2>
        <a href="peliculas.php" class="section-link">Ver todas <i class="fas fa-arrow-right"></i></a>
      </div>
      
      <?php
      $url_peliculas = "https://api.themoviedb.org/3/trending/movie/week?api_key=$api_key&language=es-ES";
      $data_peliculas = @file_get_contents($url_peliculas);
      $peliculas = $data_peliculas ? array_slice(json_decode($data_peliculas, true)['results'], 0, 20) : [];
      ?>
      
      <div class="carousel-container">
        <button class="carousel-arrow left" type="button" aria-label="Anterior" data-target="peliculas-carousel"><i class="fas fa-chevron-left"></i></button>
        <div class="scroll-row" id="peliculas-carousel">
          <?php foreach ($peliculas as $peli): ?>
            <a class="content-card" href="pelicula_detalle.php?id=<?= $peli['id'] ?>">
              <div class="card-poster">
                <?php if (!empty($peli['poster_path'])): ?>
                  <img src="https://image.tmdb.org/t/p/w342<?= $peli['poster_path'] ?>" alt="<?= htmlspecialchars($peli['title']) ?>">
                <?php else: ?>
                  <div class="no-img">Sin imagen</div>
                <?php endif; ?>
              </div>
              <div class="card-info">
                <h3 class="card-title"><?= htmlspecialchars($peli['title']) ?></h3>
                <div class="card-meta">
                  <span class="card-rating"><i class="fas fa-star"></i> <?= number_format($peli['vote_average'], 1) ?></span>
                  <span class="card-year"><?= substr($peli['release_date'] ?? '', 0, 4) ?></span>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
        <button class="carousel-arrow right" type="button" aria-label="Siguiente" data-target="peliculas-carousel"><i class="fas fa-chevron-right"></i></button>
      </div>
    </div>
    <div class="featured-content">
      <div class="section-header">
        <h2 id="series" class="section-title">Series del momento</h2>
        <a href="series.php" class="section-link">Ver todas <i class="fas fa-arrow-right"></i></a>
      </div>
      
      <?php
      $url_series = "https://api.themoviedb.org/3/trending/tv/week?api_key=$api_key&language=es-ES";
      $data_series = @file_get_contents($url_series);
      $series = $data_series ? array_slice(json_decode($data_series, true)['results'], 0, 20) : [];
      ?>
      
      <div class="carousel-container">
        <button class="carousel-arrow left" type="button" aria-label="Anterior" data-target="series-carousel"><i class="fas fa-chevron-left"></i></button>
        <div class="scroll-row" id="series-carousel">
          <?php foreach ($series as $serie): ?>
            <a class="content-card" href="serie_detalle.php?id=<?= $serie['id'] ?>">
              <div class="card-poster">
                <?php if (!empty($serie['poster_path'])): ?>
                  <img src="https://image.tmdb.org/t/p/w342<?= $serie['poster_path'] ?>" alt="<?= htmlspecialchars($serie['name']) ?>">
                <?php else: ?>
                  <div class="no-img">Sin imagen</div>
                <?php endif; ?>
              </div>
              <div class="card-info">
                <h3 class="card-title"><?= htmlspecialchars($serie['name']) ?></h3>
                <div class="card-meta">
                  <span class="card-rating"><i class="fas fa-star"></i> <?= number_format($serie['vote_average'], 1) ?></span>
                  <span class="card-year"><?= substr($serie['first_air_date'] ?? '', 0, 4) ?></span>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
        <button class="carousel-arrow right" type="button" aria-label="Siguiente" data-target="series-carousel"><i class="fas fa-chevron-right"></i></button>
      </div>
    </div>

    <!-- Genres Section -->
    <div class="genres-section mt-5">
      <h2 class="section-title mb-4">Explorar por géneros</h2>
      <?php
  //generos disponibles en peliculas.php
      $generos_disponibles = [
        'accion', 'aventura', 'animacion', 'comedia', 'criminal', 'documental', 'drama', 'familia', 'fantasia',
        'historia', 'terror', 'musica', 'misterio', 'romance', 'ciencia-ficcion', 'tv-movie', 'thriller', 'guerra', 'western'
      ];
      ?>
      <div class="row g-3">
         <div class="col-6 col-md-4 col-lg-3">
          <a href="#" id="random-genre-link" class="genre-card" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('../Images/dados.png')">
            <span><i class="fas fa-dice"></i> Aleatorio</span>
          </a>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
          <a href="peliculas.php?genero=accion" class="genre-card" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('../Images/matrix.png')">
            <span>Acción</span>
          </a>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
          <a href="peliculas.php?genero=comedia" class="genre-card" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('../Images/alig.png')">
            <span>Comedia</span>
          </a>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
          <a href="peliculas.php?genero=drama" class="genre-card" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('../Images/cadenaperpetua.png')">
            <span>Drama</span>
          </a>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
          <a href="peliculas.php?genero=terror" class="genre-card" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('../Images/halloween.png')">
            <span>Terror</span>
          </a>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
          <a href="peliculas.php?genero=animacion" class="genre-card" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('../Images/mosntruossa.png')">
            <span>Animación</span>
          </a>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
          <a href="peliculas.php?genero=fantasia" class="genre-card" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('../Images/gollum.png')">
            <span>Fantasía</span>
          </a>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
          <a href="peliculas.php?genero=ciencia-ficcion" class="genre-card" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('../Images/avatar.png')">
            <span>Ciencia ficción</span>
          </a>
        </div>
      </div>
    </div>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Flechas carrousel
    document.querySelectorAll('.carousel-arrow').forEach(btn => {
      btn.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const row = document.getElementById(targetId);
        if (!row) return;
        const card = row.querySelector('.content-card');
        const scrollAmount = card ? (card.offsetWidth + 160) : 300;
        if (this.classList.contains('left')) {
          row.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        } else {
          row.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }
      });
    });
    // genero aleatorio
    const generosDisponibles = [
      'accion', 'aventura', 'animacion', 'comedia', 'criminal', 'documental', 'drama', 'familia', 'fantasia',
      'historia', 'terror', 'musica', 'misterio', 'romance', 'ciencia-ficcion', 'tv-movie', 'thriller', 'guerra', 'western'
    ];
    document.getElementById('random-genre-link').addEventListener('click', function(e) {
      e.preventDefault();
      const randomGenero = generosDisponibles[Math.floor(Math.random() * generosDisponibles.length)];
      window.location.href = `peliculas.php?genero=${randomGenero}`;
    });
  </script>
</body>
<?php include '../Generales/footer.php'; ?>

</html>
