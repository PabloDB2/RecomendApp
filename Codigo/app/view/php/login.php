<?php
require_once(__DIR__ . '/../../../rutas.php');
require_once(CONTROLLER . 'UsuarioController.php');
require_once(MODEL . 'Usuario.php');
require_once(CONFIG . 'sesion.php'); 


if (isset($_GET['redirect'])) {
  $_SESSION['redirect_after_login'] = $_GET['redirect'];
}

if (isset($_SESSION['nombre_usuario'])) {
  header("Location: inicio.php");
  exit();
}

$usuarioController = new UsuarioController();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $identificador = $_POST['identificador'] ?? '';
  $contraseña = $_POST['contraseña'] ?? '';

  if (filter_var($identificador, FILTER_VALIDATE_EMAIL)) {
    $usuario = $usuarioController->getUserByEmail($identificador);
  } else {
    $usuario = $usuarioController->getUserByName($identificador);
  }

  if ($usuario && password_verify($contraseña, $usuario->getContraseña())) {
    $_SESSION['nombre_usuario'] = $usuario->getNombreUsuario();
    $redirect_url = $_SESSION['redirect_after_login'] ?? 'inicio.php';
    unset($_SESSION['redirect_after_login']);
    header("Location: " . $redirect_url);
    exit();
  } else {
    $error = "Credenciales incorrectas";
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Iniciar sesión</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../Generales/variables.css">
  <link rel="stylesheet" href="../CSS/login.css">
</head>

<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="signup-form-container">
          <h2 class="text-center">Iniciar sesión</h2>
          <p class="text-center textoPrincipal">Accede a tu cuenta para guardar recomendaciones</p>

          <form action="" method="post" autocomplete="off">
            <div class="mb-3">
              <label for="identificador" class="form-label">Nombre de usuario o email</label>
              <input type="text" class="form-control" id="identificador" name="identificador" placeholder="Introduce tu nombre de usuario o email" required>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Contraseña</label>
              <input type="password" class="form-control" id="password" name="contraseña" placeholder="Introduce tu contraseña" required>
            </div>
            <?php if (isset($error)): ?> <!-- font awesome para el triangulo de advertencia-->
              <p class="text-danger mb-3"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <div class="d-grid">
              <button type="submit" class="btn btn-signup">Iniciar sesión</button>
            </div>
            <div class="text-center mt-4">
              <p class="mb-0">
                ¿No tienes cuenta?
                <a href="registro.php" class="login-link">Registrarse</a>
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>

</html>