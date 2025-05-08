<?php
// nav.php
// Asegúrate de llamar a session_start() antes de incluir este archivo en tus páginas
$nombre_usuario = $_SESSION['nombre_usuario'] ?? null;
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    :root {
        --black: #181818;
        --green: #2ecc40;
        --white-green: #eaffea;
        --primary-green: #2e8b57;
    }
    .navbar {
        background-color: var(--black) !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .navbar-brand {
        color: var(--green) !important;
        font-weight: bold;
    }
    .nav-link {
        color: var(--white-green) !important;
        font-size: 16px;
        padding: 10px 15px !important;
        transition: color 0.3s;
    }
    .nav-link:hover, .nav-link.active {
        color: var(--primary-green) !important;
        text-decoration: underline;
    }
    .navbar-account {
        display: flex;
        align-items: center;
        margin-left: auto;
    }
    .navbar-account a {
        color: var(--white-green);
        display: flex;
        align-items: center;
        font-weight: 500;
        text-decoration: none;
        transition: color 0.2s;
        margin-left: 1rem;
    }
    .navbar-account a:hover {
        color: var(--primary-green);
        text-decoration: underline;
    }
    .navbar-account svg {
        margin-right: 6px;
        width: 28px;
        height: 28px;
        fill: var(--green);
    }
    @media (max-width: 991.98px) {
        .navbar-nav {
            text-align: center;
        }
        .navbar-account {
            margin-top: 1rem;
            margin-left: 0;
            justify-content: center;
        }
    }
</style>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">RecomendApp</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="index.php">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'peliculas.php') ? 'active' : ''; ?>" href="peliculas.php">Películas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'series.php') ? 'active' : ''; ?>" href="series.php">Series</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'libros.php') ? 'active' : ''; ?>" href="libros.php">Libros</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'recetas.php') ? 'active' : ''; ?>" href="recetas.php">Recetas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'canciones.php') ? 'active' : ''; ?>" href="canciones.php">Canciones</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'artistas.php') ? 'active' : ''; ?>" href="artistas.php">Artistas</a>
                </li>
            </ul>
            <?php if ($nombre_usuario): ?>
                <div class="navbar-account ms-lg-auto">
                    <a href="cuenta.php">
                        <!-- Bootstrap icon: person-circle -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                          <path d="M11 10a2 2 0 1 0-4 0 2 2 0 0 0 4 0z"/>
                          <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37c.69-1.19 2.06-2.37 5.468-2.37s4.778 1.18 5.468 2.37A7 7 0 0 0 8 1z"/>
                        </svg>
                        <?php echo htmlspecialchars($nombre_usuario); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
