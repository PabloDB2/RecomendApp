<?php

$nombre_usuario = $_SESSION['nombre_usuario'] ?? null;
$current_page = basename($_SERVER['PHP_SELF']);
require_once(__DIR__ . '/../../../rutas.php');
require_once(CONFIG . 'sesion.php');
$redirect = urlencode($_SERVER['REQUEST_URI']); // guarda la url actual para redirigir al cerrar sesion

$avatar = '1default-avatar.jpg';

if ($nombre_usuario) {
    require_once(CONTROLLER . 'UsuarioController.php');

    $usuarioController = new UsuarioController();
    $usuario = $usuarioController->getUserByName($nombre_usuario);

    if ($usuario && !empty($usuario->getAvatar())) {
        $avatar = $usuario->getAvatar();
    }
}
// Cerrar sesión
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    $redirect_url = $_GET['redirect'] ?? 'inicio.php'; //para redirigir a la pagina desde la que se cerro sesión( o por defecto a inicio)
    session_destroy();
    header("Location: " . $redirect_url);
    exit();
}
?>
<link rel="stylesheet" href="../Generales/variables.css">
<link rel="stylesheet" href="../CSS/nav.css">
<!-- Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
                        <i class="fas fa-home nav-icon"></i>
                        <span>Inicio</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'peliculas.php') ? 'active' : ''; ?>" href="peliculas.php">
                        <i class="fas fa-film nav-icon"></i>
                        <span>Películas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'series.php') ? 'active' : ''; ?>" href="series.php">
                        <i class="fas fa-tv nav-icon"></i>
                        <span>Series</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'libros.php') ? 'active' : ''; ?>" href="libros.php">
                        <i class="fas fa-book nav-icon"></i>
                        <span>Libros</span>
                    </a>
                </li>
            </ul>

            <div class="navbar-account ms-lg-auto">
                <?php if ($nombre_usuario): ?>
                    <div class="dropdown user-dropdown">
                        <a href="#" class="dropdown-toggle user-menu" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="avatarNav">
                                <img src="../Images/avatars/<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="user-avatar">
                            </div>
                            <span class="user-name"><?php echo htmlspecialchars($nombre_usuario); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu" aria-labelledby="userDropdown">

                            <li>
                                <a class="dropdown-item" href="perfil.php">
                                    <i class="fas fa-user dropdown-item-icon"></i>
                                    <span>Mi Perfil</span>
                                </a>
                            </li>

                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item logout-item" href="?logout=true&redirect=<?php echo $redirect; ?>">
                                    <i class="fas fa-sign-out-alt dropdown-item-icon"></i>
                                    <span>Cerrar Sesión</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="botonAutenticarse login-btn <?php echo ($current_page == 'login.php') ? 'active' : ''; ?>" href="login.php?redirect=<?php echo $redirect; ?>">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Iniciar Sesión</span>
                    </a>
                    <a class="botonAutenticarse register-btn <?php echo ($current_page == 'registro.php') ? 'active' : ''; ?>" href="registro.php">
                        <i class="fas fa-user-plus"></i>
                        <span>Registrarse</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>