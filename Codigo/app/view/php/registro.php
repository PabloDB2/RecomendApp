<?php
require_once(__DIR__ . '/../../../rutas.php');
require_once(CONTROLLER . 'UsuarioController.php'); 
require_once(MODEL . 'Usuario.php'); 
session_start();

$usuarioController = new UsuarioController();

if (isset($_POST['formCreate']) && $_POST['formCreate'] == 'crearUsuario') {
    if (
        isset($_POST["nombre_usuario"], $_POST["email"], $_POST["contraseña"], $_POST["repetir_contraseña"]) &&
        $_POST["contraseña"] === $_POST["repetir_contraseña"]
    ) {
        $nombreUsuario = htmlspecialchars($_POST["nombre_usuario"]);
        $email = htmlspecialchars($_POST["email"]);
        $contraseña = $_POST["contraseña"];

        $usuarioController->crearUsuario($nombreUsuario, $email, $contraseña);

        $usuario = $usuarioController->getUserByName($nombreUsuario);

        if ($usuario && password_verify($contraseña, $usuario->getContraseña())) {
            $_SESSION['nombre_usuario'] = $usuario->getNombreUsuario();
            header("Location: inicio.php");
            exit();
        } else {
            $error = "Error al iniciar sesión automáticamente.";
        }
    } else {
        $error = "Las contraseñas no coinciden o faltan datos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../CSS/variables.css">
  <link rel="stylesheet" href="../CSS/registro.css">
</head>
<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="signup-form-container">
          <h2 class="text-center">Crear cuenta</h2>
          <p class="text-center textoPrincipal">Crear una cuenta te permitirá guardar recomendaciones</p>
          <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
              <?php echo htmlspecialchars($error); ?>
            </div>
          <?php endif; ?>
          <form id="signupForm" action="" method="post" autocomplete="off">
            <input type="hidden" name="formCreate" value="crearUsuario">

            <div class="mb-3">
              <label for="username" class="form-label">Nombre de usuario</label>
              <input type="text" class="form-control" id="username" name="nombre_usuario" placeholder="Introduce un nombre de usuario" required>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Introduce un email" required>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Contraseña</label>
              <input type="password" class="form-control" id="password" name="contraseña" placeholder="Introduce una contraseña" required>
            </div>

            <div class="mb-4">
              <label for="repeatPassword" class="form-label">Repetir contraseña</label>
              <input type="password" class="form-control" id="repeatPassword" name="repetir_contraseña" placeholder="Repite la contraseña" required>
              <div id="passwordError" class="error-message" style="display:none;">Las contraseñas no coinciden</div>
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-signup" id="submitBtn">Crear cuenta</button>
            </div>

            <div class="text-center mt-4">
              <p class="mb-0">
                ¿Ya tienes una cuenta?
                <a href="login.php" class="login-link">Iniciar sesión</a>
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    const username = document.getElementById('username');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const repeatPassword = document.getElementById('repeatPassword');
    const passwordError = document.getElementById('passwordError');
    const submitBtn = document.getElementById('submitBtn');

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

    function validatePasswordMatch() {
      const match = password.value === repeatPassword.value;
      toggleValidationStyle(repeatPassword, match);
      passwordError.style.display = match || repeatPassword.value.length === 0 ? 'none' : 'block';
    }

    password.addEventListener('input', validatePasswordMatch);
    repeatPassword.addEventListener('input', validatePasswordMatch);

    document.getElementById("signupForm").addEventListener("submit", function(event) {
      if (password.value !== repeatPassword.value) {
        event.preventDefault();
        passwordError.style.display = 'block'; 
      }
    });
  </script>
</body>
</html>
