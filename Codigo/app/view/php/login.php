<?php
require_once(__DIR__ . '/../../../rutas.php');
require_once(CONTROLLER . 'UsuarioController.php');
require_once(MODEL . 'Usuario.php');

session_start();

// Si ya está logueado, redirige a inicio
if (isset($_SESSION['nombre_usuario'])) {
    header("Location: inicio.php");
    exit();
}

$usuarioController = new UsuarioController();

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nombre_usuario'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';

    // Obtener el usuario por nombre
    $usuario = $usuarioController->getUserByName($nombre_usuario);

    if ($usuario) {
        // Verifica la contraseña hasheada
        if (password_verify($contraseña, $usuario->getContraseña())) {
            $_SESSION['nombre_usuario'] = $usuario->getNombreUsuario();
            header("Location: inicio.php");
            exit();
        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "Usuario no encontrado";
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
  <link rel="stylesheet" href="../CSS/variables.css">
  <link rel="stylesheet" href="../CSS/login.css">
</head>
<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="signup-form-container">
          <h2 class="text-center">Iniciar sesión</h2>
          <p class="text-center textoPrincipal">Accede a tu cuenta para guardar tus recomendaciones</p>
          <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
              <?php echo htmlspecialchars($error); ?>
            </div>
          <?php endif; ?>
          <form id="loginForm" action="" method="post" autocomplete="off">
            <div class="mb-3">
              <label for="username" class="form-label">Nombre de usuario</label>
              <input type="text" class="form-control" id="username" name="nombre_usuario" placeholder="Introduce tu nombre de usuario" required>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Contraseña</label>
              <input type="password" class="form-control" id="password" name="contraseña" placeholder="Introduce tu contraseña" required>
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-signup" id="submitBtn">Iniciar sesión</button>
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

  <script>
    const username = document.getElementById('username');
    const password = document.getElementById('password');
    const submitBtn = document.getElementById('submitBtn');

    // Función para cambiar el estilo de validación (sin validaciones por ahora)
    function toggleValidationStyle(input, isValid) {
      if (input.value.length === 0) {
        input.classList.remove('valid-input', 'invalid-input');
        return;
      }

      if (isValid) {
        input.classList.add('valid-input');
        input.classList.remove('invalid-input');
      } else {
        input.classList.add('invalid-input');
        input.classList.remove('valid-input');
      }
    }

    // Comprobamos si el nombre de usuario y la contraseña están completos antes de enviar el formulario
    document.getElementById("loginForm").addEventListener("submit", function(event) {
      let isValid = true;
      if (username.value.length === 0) {
        toggleValidationStyle(username, false);
        isValid = false;
      } else {
        toggleValidationStyle(username, true);
      }

      if (password.value.length === 0) {
        toggleValidationStyle(password, false);
        isValid = false;
      } else {
        toggleValidationStyle(password, true);
      }

      if (!isValid) {
        event.preventDefault(); // Evitar que el formulario se envíe si no es válido
      }
    });
  </script>
</body>
</html>
