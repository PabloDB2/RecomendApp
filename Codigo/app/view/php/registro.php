<?php
require_once(__DIR__ . '/../../../rutas.php');
require_once(CONTROLLER . 'UsuarioController.php');
require_once(MODEL . 'Usuario.php');
require_once(CONFIG . 'sesion.php'); 

$usuarioController = new UsuarioController();

$nombreUsuario = '';
$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombreUsuario = $_POST["nombre_usuario"] ?? '';
  $email = $_POST["email"] ?? '';
  $contraseña = $_POST["contraseña"] ?? '';
  $repetir = $_POST["repetir_contraseña"] ?? '';

  if (empty($nombreUsuario) || empty($email) || empty($contraseña) || empty($repetir)) {
    $error = "Todos los campos son obligatorios.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "El email no es válido.";
  } elseif ($contraseña !== $repetir) {
    $error = "Las contraseñas no coinciden.";
  } else {
    // Comprobacion si ya existe el nombre de usuario
    $usuarioExistente = $usuarioController->getUserByName($nombreUsuario);
    if ($usuarioExistente) {
      $error = "El nombre de usuario ya está en uso.";
    }
    // Comprobacion si ya existe el email
    elseif ($usuarioController->getUserByEmail($email)) {
      $error = "El correo electrónico ya está registrado.";
    } else {
      $usuarioController->crearUsuario($nombreUsuario, $email, $contraseña);
      $usuario = $usuarioController->getUserByName($nombreUsuario);

      if ($usuario && password_verify($contraseña, $usuario->getContraseña())) {
        $_SESSION['nombre_usuario'] = $usuario->getNombreUsuario();
        header("Location: inicio.php");
        exit();
      } else {
        $error = "Error al iniciar sesión automáticamente.";
      }
    }
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
          <h2 class="text-center mb-3">Crear cuenta</h2>
          <p class="text-center textoPrincipal">Crear una cuenta te permitirá guardar recomendaciones</p>
          <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>
          <form method="post" autocomplete="off" class="needs-validation" novalidate>
            <div class="mb-3">
              <label for="username" class="form-label">Nombre de usuario</label>
              <input type="text" class="form-control" id="username" name="nombre_usuario"
                value="<?= htmlspecialchars($nombreUsuario) ?>"
                placeholder="Introduce un nombre de usuario" minlength="4" required maxlength="20"
                autocomplete="username" oninput="checkUsername()">
              <div class="invalid-feedback" id="usernameFeedback">Introduce un nombre de usuario (mínimo 4 caracteres)</div>
              <div class="valid-feedback" id="usernameValidFeedback">Nombre de usuario disponible</div>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email"
                value="<?= htmlspecialchars($email) ?>"
                placeholder="Introduce tu email" required maxlength="60"
                autocomplete="email" oninput="checkEmail()">
              <div class="invalid-feedback" id="emailFeedback">Introduce un email válido.</div>
              <div class="valid-feedback" id="emailValidFeedback">Email disponible</div>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Contraseña</label>
              <input type="password" class="form-control" id="password" name="contraseña"
                placeholder="Introduce una contraseña" required minlength="6" maxlength="50"
                autocomplete="new-password" oninput="showRepeatPasswordField()">
              <div class="invalid-feedback">Introduce una contraseña (mínimo 6 caracteres).</div>
            </div>

            <div class="mb-4" id="repeatPasswordContainer" style="display:none;">
              <label for="repeatPassword" class="form-label">Repetir contraseña</label>
              <input type="password" class="form-control" id="repeatPassword" name="repetir_contraseña"
                placeholder="Repite la contraseña" required minlength="6" maxlength="50"
                autocomplete="new-password" oninput="checkPasswords()">
              <small id="passwordHelp" class="form-text"></small>
              <div class="invalid-feedback" id="repeatPasswordFeedback">Las contraseñas no coinciden.</div>
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-signup">Crear cuenta</button>
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
    (() => {
      'use strict';
      const forms = document.querySelectorAll('.needs-validation');
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    })();

    // Mostrar campo de repetir contraseña cuando la contraseña sea válida
    function showRepeatPasswordField() {
      const password = document.getElementById('password').value;
      const repeatContainer = document.getElementById('repeatPasswordContainer');
      if (password.length >= 6) {
        repeatContainer.style.display = 'block';
      } else {
        repeatContainer.style.display = 'none';
        document.getElementById('repeatPassword').value = '';
        document.getElementById('repeatPassword').classList.remove('is-valid', 'is-invalid');
        document.getElementById('repeatPasswordFeedback').style.display = 'none';
      }
    }

    // Validación de contraseñas (en tiempo real)
    function checkPasswords() {
      const pass = document.getElementById('password').value;
      const repeat = document.getElementById('repeatPassword').value;
      const feedback = document.getElementById('repeatPasswordFeedback');

      if (repeat && pass !== repeat) {
        feedback.style.display = 'block';
        document.getElementById('repeatPassword').classList.add('is-invalid');
        document.getElementById('repeatPassword').classList.remove('is-valid');
      } else if (repeat) {
        feedback.style.display = 'none';
        document.getElementById('repeatPassword').classList.remove('is-invalid');
        document.getElementById('repeatPassword').classList.add('is-valid');
      }
    }

    // Verificar si el nombre de usuario ya existe
    let usernameTimer;

    function checkUsername() {
      const username = document.getElementById('username').value;
      const feedback = document.getElementById('usernameFeedback');

      // Limpiar el temporizador anterior
      clearTimeout(usernameTimer);

      // Verificar longitud mínima
      if (username.length < 4) {
        document.getElementById('username').classList.remove('is-valid');
        if (username.length > 0) {
          document.getElementById('username').classList.add('is-invalid');
          feedback.textContent = "El nombre de usuario debe tener al menos 4 caracteres";
        } else {
          document.getElementById('username').classList.remove('is-invalid');
        }
        return;
      }

      // Esperar 500ms después de que el usuario deje de escribir
      usernameTimer = setTimeout(() => {
        // Crear objeto FormData
        const formData = new FormData();
        formData.append('username', username);

        // Realizar la petición AJAX
        fetch('../ajax/check_credenciales.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.exists) {
              document.getElementById('username').classList.add('is-invalid');
              document.getElementById('username').classList.remove('is-valid');
              feedback.textContent = "Este nombre de usuario ya está en uso";
            } else {
              document.getElementById('username').classList.remove('is-invalid');
              document.getElementById('username').classList.add('is-valid');
            }
          })
          .catch(error => {
            console.error('Error:', error);
          });
      }, 500);
    }

    // Verificar si el email ya existe
    let emailTimer;

    function checkEmail() {
      const email = document.getElementById('email').value;
      const feedback = document.getElementById('emailFeedback');

      // Limpiar el temporizador anterior
      clearTimeout(emailTimer);

      // Verificar si es un email válido
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        document.getElementById('email').classList.remove('is-valid');
        if (email.length > 0) {
          document.getElementById('email').classList.add('is-invalid');
          feedback.textContent = "Introduce un email válido";
        } else {
          document.getElementById('email').classList.remove('is-invalid');
        }
        return;
      }

      // Esperar 500ms después de que el usuario deje de escribir
      emailTimer = setTimeout(() => {
        // Crear objeto FormData
        const formData = new FormData();
        formData.append('email', email);

        // Realizar la petición AJAX
        fetch('../ajax/check_credenciales.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.exists) {
              document.getElementById('email').classList.add('is-invalid');
              document.getElementById('email').classList.remove('is-valid');
              feedback.textContent = "Este email ya está registrado";
            } else {
              document.getElementById('email').classList.remove('is-invalid');
              document.getElementById('email').classList.add('is-valid');
            }
          })
          .catch(error => {
            console.error('Error:', error);
          });
      }, 500);
    }
  </script>
</body>

</html>