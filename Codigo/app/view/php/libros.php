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
  <link rel="stylesheet" href="../CSS/inicio.css">

  <style>
    h2 {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 75vh;
      color: var(--green);
    }
  </style>
</head>
<body>
  <h2>Disponible próximamente...</h2>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php include '../Generales/footer2.php'; ?>

