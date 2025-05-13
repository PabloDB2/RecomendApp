<?php
$nombre_usuario = $_SESSION['nombre_usuario'] ?? null;
$current_page = basename($_SERVER['PHP_SELF']);

// Verificar si el usuario quiere cerrar sesión
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    $redirect_url = $_GET['redirect'] ?? 'inicio.php';
    session_unset();  // Eliminar todas las variables de sesión
    session_destroy(); // Destruir la sesión
    header("Location: " . $redirect_url);
    exit();
}
?>

<link rel="stylesheet" href="../CSS/navbar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="inicio.php">
            <span class="brand-text">RecomendApp</span>
        </a>

        <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav" aria-controls="navbarNav"
            aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'inicio.php') ? 'active' : ''; ?>" href="inicio.php">
                        <span>Inicio</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'peliculas.php') ? 'active' : ''; ?>" href="peliculas.php">
                        <span>Películas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'series.php') ? 'active' : ''; ?>" href="series.php">
                        <span>Series</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'libros.php') ? 'active' : ''; ?>" href="libros.php">
                        <span>Libros</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'recetas.php') ? 'active' : ''; ?>" href="recetas.php">
                        <span>Recetas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'canciones.php') ? 'active' : ''; ?>" href="canciones.php">
                        <span>Canciones</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'artistas.php') ? 'active' : ''; ?>" href="artistas.php">
                        <span>Artistas</span>
                    </a>
                </li>
            </ul>

            <div class="navbar-account ms-lg-auto">
                <?php if ($nombre_usuario): ?>
                    <?php $redirect = urlencode($_SERVER['REQUEST_URI']); ?>
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle user-menu" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                                <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
                            </svg>
                            <span class="ms-2"><?php echo htmlspecialchars($nombre_usuario); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="cuenta.php">Mi Perfil</a></li>
                            <li><a class="dropdown-item" href="favoritos.php">Mis Favoritos</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="?logout=true&redirect=<?php echo $redirect; ?>">Cerrar Sesión</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <?php $redirect = urlencode($_SERVER['REQUEST_URI']); ?>
                    <a class="nav-link d-inline px-2 <?php echo ($current_page == 'login.php') ? 'active' : ''; ?>" href="login.php?redirect=<?php echo $redirect; ?>">
                        <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión
                    </a>
                    <a class="nav-link d-inline px-2 <?php echo ($current_page == 'registro.php') ? 'active' : ''; ?>" href="registro.php">
                        <i class="fas fa-user-plus me-1"></i> Registrarse
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
