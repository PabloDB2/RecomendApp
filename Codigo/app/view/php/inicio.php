<?php
session_start();
$nombre_usuario = $_SESSION['nombre_usuario'] ?? null;
include '../Generales/nav.php';
?>
<div class="container mt-5">
    <div class="text-center">
        <h1 class="mb-3" style="color: var(--green); font-weight: bold;">
            <?php if ($nombre_usuario): ?>
                ¡Hola, <?php echo htmlspecialchars($nombre_usuario); ?>!
            <?php else: ?>
                ¡Bienvenido a RecomendApp!
            <?php endif; ?>
        </h1>
        <p class="lead" style="color: var(--white-green);">
            Descubre películas, series, libros, recetas, canciones y artistas recomendados según tus gustos.
        </p>
    </div>
    <div class="row justify-content-center mt-4">
        <!-- Tarjetas de acceso rápido -->
        <div class="col-md-2 col-6 mb-3">
            <a href="peliculas.php" class="btn btn-dark w-100 py-3" style="background: var(--primary-green); color: #fff;">
                🎬 Películas
            </a>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <a href="series.php" class="btn btn-dark w-100 py-3" style="background: var(--primary-green); color: #fff;">
                📺 Series
            </a>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <a href="libros.php" class="btn btn-dark w-100 py-3" style="background: var(--primary-green); color: #fff;">
                📚 Libros
            </a>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <a href="recetas.php" class="btn btn-dark w-100 py-3" style="background: var(--primary-green); color: #fff;">
                🍳 Recetas
            </a>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <a href="canciones.php" class="btn btn-dark w-100 py-3" style="background: var(--primary-green); color: #fff;">
                🎵 Canciones
            </a>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <a href="artistas.php" class="btn btn-dark w-100 py-3" style="background: var(--primary-green); color: #fff;">
                🎤 Artistas
            </a>
        </div>
    </div>
    <div class="text-center mt-4">
        <p style="color: var(--gray);">
            Elige una categoría y responde unas preguntas para recibir recomendaciones personalizadas.
        </p>
    </div>
</div>
