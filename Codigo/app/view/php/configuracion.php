<?php
require_once(__DIR__ . '/../../../rutas.php');
require_once(CONFIG . 'sesion.php');
$nombre_usuario = $_SESSION['nombre_usuario'] ?? null;
$email_usuario = $_SESSION['email'] ?? '';
include '../Generales/nav.php';

$error = '';

function passwordValida($pass) {
    return preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/', $pass);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['config_save'])) {
    require_once(CONTROLLER . 'UsuarioController.php');
    $usuarioController = new UsuarioController();
    $usuario = $usuarioController->getUserByName($_SESSION['nombre_usuario']);
    if ($usuario) {
        $nuevo_nombre = trim($_POST['username'] ?? '');
        $nuevo_email = trim($_POST['email'] ?? '');
        $nueva_password = $_POST['new_password'] ?? '';
        $current_password = $_POST['current_password'] ?? '';
        $cambiar_nombre = $nuevo_nombre && $nuevo_nombre !== $usuario->getNombreUsuario();
        $cambiar_email = $nuevo_email && $nuevo_email !== $usuario->getEmail();
        $cambiar_password = !empty($nueva_password);
        if ($cambiar_nombre && strlen($nuevo_nombre) < 4) {
            echo "<script>alert('El nombre de usuario debe tener al menos 4 caracteres.');</script>";
        } elseif ($cambiar_email && !filter_var($nuevo_email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('Introduce un email válido.');</script>";
        } elseif ($cambiar_password && !passwordValida($nueva_password)) {
            echo "<script>alert('La nueva contraseña debe tener al menos 6 caracteres, una letra y un número.');</script>";
        } elseif ($cambiar_password && !password_verify($current_password, $usuario->getContraseña())) {
            echo "<script>alert('Contraseña actual incorrecta.');</script>";
        } else {
            $resultado = $usuarioController->actualizarUsuario(
                $usuario->getIdUsuario(),
                $cambiar_nombre ? $nuevo_nombre : $usuario->getNombreUsuario(),
                $cambiar_email ? $nuevo_email : $usuario->getEmail(),
                $cambiar_password ? $nueva_password : null
            );
            if ($resultado === true || $resultado === null) {
                if ($cambiar_nombre) $_SESSION['nombre_usuario'] = $nuevo_nombre;
                if ($cambiar_email) $_SESSION['email'] = $nuevo_email;
                header('Location: configuracion.php');
                exit();
            } else {
                $error = $resultado;
            }
        }
    } else {
        $error = 'Usuario no encontrado.';
    }
}
// Eliminar cuenta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_cuenta'])) {
    require_once(CONTROLLER . 'UsuarioController.php');
    $usuarioController = new UsuarioController();
    $usuario = $usuarioController->getUserByName($_SESSION['nombre_usuario']);
    if ($usuario) {
        $usuarioController->eliminarUsuario($usuario->getIdUsuario());
        session_unset();
        session_destroy();
        header('Location: inicio.php');
        exit();
    } else {
        $error = 'No se pudo eliminar la cuenta.';
    }
}

if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
   
    session_destroy();
    header("Location: inicio.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Configuración</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
   <link rel="stylesheet" href="../Generales/variables.css">

  <link rel="stylesheet" href="../CSS/inicio.css">
  <style>
    .config-container {
      max-width: 480px;
      margin: 3rem auto 2rem auto;
      background: var(--darker-bg, #181a1b);
      border-radius: var(--border-radius-md, 16px);
      box-shadow: 0 4px 24px rgba(0,0,0,0.25);
      padding: 2.5rem 2rem 2rem 2rem;
      color: var(--white, #fff);
    }
    .config-title {
      text-align: center;
      color: var(--green, #00ff99);
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
    }
    .config-form label {
      color: var(--green, #00ff99);
      font-weight: 600;
      margin-bottom: 0.3rem;
    }
    .config-form input[type="text"],
    .config-form input[type="email"],
    .config-form input[type="password"] {
      width: 100%;
      background: var(--black-dark, #101112);
      border: 1.5px solid var(--green, #00ff99);
      color: var(--white, #fff);
      border-radius: var(--border-radius, 8px);
      padding: 0.7rem 1rem;
      margin-bottom: 1.2rem;
      font-size: 1rem;
      transition: border 0.2s;
    }
    .config-form input:focus {
      outline: none;
      border-color: var(--secondary-green, #00e676);
    }
    .config-form .form-group {
      margin-bottom: 1.2rem;
    }
    .btn-save {
      width: 100%;
      background: var(--green, #00ff99);
      color: var(--black-dark, #101112);
      font-weight: 700;
      border: none;
      border-radius: var(--border-radius, 8px);
      padding: 0.8rem 0;
      font-size: 1.1rem;
      box-shadow: 0 2px 12px rgba(0,255,128,0.10);
      transition: background 0.2s, color 0.2s;
      margin-top: 0.5rem;
    }
    .btn-save:hover {
      background: var(--secondary-green, #00e676);
      color: var(--black-dark, #101112);
    }
    .config-form .required {
      color: var(--light-orange, #ffb347);
      font-size: 1.1em;
      margin-left: 2px;
    }
    .config-form .note {
      color: var(--gray-light, #aaa);
      font-size: 0.95em;
      margin-bottom: 1.2rem;
      text-align: center;
    }
    .btn-red {
      width: 100%;
      background: var(--red, #ff6b6b) !important;
      color: var(--white, #fff) !important;
      font-weight: 700;
      border: none;
      border-radius: var(--border-radius, 8px);
      padding: 0.8rem 0;
      font-size: 1.1rem;
      box-shadow: 0 2px 12px rgba(255,107,107,0.10);
      transition: background 0.2s, color 0.2s;
      margin-top: 0.5rem;
    }
    .btn-red:hover {
      background: var(--orange-darker, #e74c3c) !important;
      color: var(--white, #fff) !important;
    }
    .btn-white {
      width: 100%;
      background: var(--white, #fff) !important;
      color: var(--black-dark, #101112) !important;
      font-weight: 700;
      border: none;
      border-radius: var(--border-radius, 8px);
      padding: 0.8rem 0;
      font-size: 1.1rem;
      box-shadow: 0 2px 12px rgba(0,0,0,0.05);
      transition: background 0.2s, color 0.2s;
      margin-top: 0.5rem;
    }
    .btn-white:hover {
      background: var(--gray-light, #ecf0f1) !important;
      color: var(--black-dark, #101112) !important;
    }
    .config-error {
      text-align: center;
      color: #ff4d4d;
      margin-bottom: 1.2rem;
      font-weight: 600;
    }
  </style>
</head>
<body>
  <main>
    <div class="config-container">
      <h2 class="config-title">Configuración de cuenta</h2>
      <form class="config-form" method="post">
        <div class="form-group">
          <label for="username">Nombre de usuario</label>
          <input type="text" id="username" name="username" value="<?= htmlspecialchars($nombre_usuario) ?>" autocomplete="username">
        </div>
        <div class="form-group">
          <label for="email">Correo electrónico</label>
          <input type="email" id="email" name="email" value="<?php
            if (!empty($email_usuario)) {
              echo htmlspecialchars($email_usuario);
            } else if (isset($_SESSION['nombre_usuario'])) {
              require_once(CONTROLLER . 'UsuarioController.php');
              $usuarioController = new UsuarioController();
              $usuario_email = $usuarioController->getUserByName($_SESSION['nombre_usuario']);
              echo $usuario_email ? htmlspecialchars($usuario_email->getEmail()) : '';
            } else {
              echo '';
            }
          ?>" autocomplete="email">
        </div>
        <div class="form-group">
          <label for="new_password">Nueva contraseña</label>
          <input type="password" id="new_password" name="new_password" autocomplete="new-password" placeholder="Dejar en blanco para no cambiar">
        </div>
        <div class="form-group">
          <label for="current_password">Contraseña actual</label>
          <input type="password" id="current_password" name="current_password" autocomplete="current-password">
          <div class="note">Necesaria para cambiar la contraseña</div>
        </div>
        <button type="submit" name="config_save" class="btn-save">Guardar cambios</button>
        <?php if ($error): ?>
          <div class="config-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
      </form>
      <hr style="margin:2.5rem 0 1.5rem 0; border-color: var(--green, #00ff99); opacity:0.25;">
      <div style="display:flex; flex-direction:column; gap:1.2rem;">
        <form method="post" onsubmit="return confirm('¿Seguro que quieres eliminar tu cuenta? Esta acción no se puede deshacer.');">
          <button type="submit" name="eliminar_cuenta" class="btn-save btn-red">Eliminar cuenta</button>
        </form>
        <a href="?logout=true" class="btn-save btn-white" style="text-align:center; text-decoration:none;">Cerrar sesión</a>
      </div>
    </div>
  </main>
  <script>
    // Validar requisitos de contraseña antes de enviar (igual que en registro)
    const nuevacontraseña = document.getElementById('new_password');
    const form = document.querySelector('.config-form');
    if(form) {
      form.addEventListener('submit', function(e) {
        const username = document.getElementById('username');
        if(username && username.value.trim().length > 0 && username.value.trim().length < 4) {
          e.preventDefault();
          alert('El nombre de usuario debe tener al menos 4 caracteres.');
          username.focus();
          return false;
        }
        const email = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if(email && email.value.trim() && !emailRegex.test(email.value.trim())) {
          e.preventDefault();
          alert('Introduce un email válido.');
          email.focus();
          return false;
        }
        // Validar contraseña
        if(nuevacontraseña.value.length > 0) {
          const regex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/;
          if(!regex.test(nuevacontraseña.value)) {
            e.preventDefault();
            alert('La nueva contraseña debe tener al menos 6 caracteres, una letra y un número.');
            nuevacontraseña.focus();
            return false;
          }
        }
      });
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
