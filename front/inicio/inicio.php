<?php
include '../../back/Conexion_BD/conexion.php';

// Activa la depuración si visitas la página con ?debug=1
if (isset($_GET['debug'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Comprobación de conexión a la base de datos
if (!isset($conexion) || !$conexion) {
    echo '<!DOCTYPE html><html><body>';
    echo '<h2 style="color:red">Error: no se pudo conectar a la base de datos.</h2>';
    if (isset($_GET['debug'])) {
        echo '<pre style="color:red">' . htmlspecialchars(mysqli_connect_error()) . '</pre>';
    }
    echo '</body></html>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RetroPlay | Inicio</title>
  <link rel="stylesheet" href="css/estilo.css">
</head>

<body>
  <!-- Navegación superior -->
  <nav>
    <a href="../mi_cuenta/mi_cuenta.php">
      <img src="css/img/mi_cuenta.png" alt="Acceso a mi cuenta">
    </a>
    <a href="../mis_reservas/reservas.html">
      <img src="css/img/reservas.png" alt="Ver reservas">
    </a>
    <a href="../carrito/carrito.html">
      <img src="css/img/carrito.png" alt="Ver carrito de compras">
    </a>
  </nav>

  <!-- Contenido principal -->
  <main>
    <h2>Videojuegos</h2>
    <section id="videojuegos">
      <?php
      // Selección insensible a mayúsculas y comprobación de errores (activar con ?debug=1)
      $sql_v = "SELECT * FROM producto WHERE LOWER(categoria) = 'videojuego'";
      $res_v = mysqli_query($conexion, $sql_v);
      if (!$res_v) {
        if (isset($_GET['debug'])) { echo "<p style='color:red'>Error consulta videojuegos: " . htmlspecialchars(mysqli_error($conexion)) . "</p>"; }
      } else {
        if (mysqli_num_rows($res_v) === 0 && isset($_GET['debug'])) { echo "<p style='color:orange'>Aviso: no se encontraron videojuegos.</p>"; }
        while ($p = mysqli_fetch_assoc($res_v)) {
          // Usar el nombre de imagen guardado en la BD (campo imagen). Sanitizar y buscar en carpeta videojuegos
          $filename = isset($p['imagen']) && trim($p['imagen']) !== '' ? basename($p['imagen']) : '';
          if ($filename !== '') {
            $img_path = "css/img/videojuegos/{$filename}";
            if (!file_exists(__DIR__ . '/' . $img_path)) {
              if (isset($_GET['debug'])) { echo "<p style='color:orange'>Aviso: imagen no encontrada: " . htmlspecialchars($img_path) . "</p>"; }
              $img_path = 'css/img/videojuegos/nintendogs.jpg';
            }
          } else {
            $img_path = 'css/img/videojuegos/nintendogs.jpg'; // imagen por defecto
          }
          echo "<article>";
          echo "<img src=\"{$img_path}\" alt=\"" . htmlspecialchars($p['titulo']) . "\">";
          echo "<h3>" . htmlspecialchars($p['titulo']) . "</h3>";
          echo "<h4>Disponibilidad</h4>";
          echo "<p>" . htmlspecialchars($p['precio']) . " por semana</p>";
          echo "<a href=\"../carrito/carrito.html?add=" . $p['id'] . "\">Añadir al carrito</a>";
          echo "</article>";
        }
      }
      ?>
    </section>

    <h2>Consolas</h2>
    <section id="consolas">
      <?php
      $sql_c = "SELECT * FROM producto WHERE LOWER(categoria) = 'consola'";
      $res_c = mysqli_query($conexion, $sql_c);
      if (!$res_c) {
        if (isset($_GET['debug'])) { echo "<p style='color:red'>Error consulta consolas: " . htmlspecialchars(mysqli_error($conexion)) . "</p>"; }
      } else {
        if (mysqli_num_rows($res_c) === 0 && isset($_GET['debug'])) { echo "<p style='color:orange'>Aviso: no se encontraron consolas.</p>"; }
        while ($p = mysqli_fetch_assoc($res_c)) {
          // Usar el nombre de imagen guardado en la BD (campo imagen). Sanitizar y buscar en carpeta consolas
          $filename = isset($p['imagen']) && trim($p['imagen']) !== '' ? basename($p['imagen']) : '';
          if ($filename !== '') {
            $img_path = "css/img/consolas/{$filename}";
            if (!file_exists(__DIR__ . '/' . $img_path)) {
              if (isset($_GET['debug'])) { echo "<p style='color:orange'>Aviso: imagen no encontrada: " . htmlspecialchars($img_path) . "</p>"; }
              $img_path = 'css/img/nintendogs.jpg';
            }
          } else {
            $img_path = 'css/img/nintendogs.jpg';
          }
          echo "<article>";
          echo "<img src=\"{$img_path}\" alt=\"" . htmlspecialchars($p['titulo']) . "\">";
          echo "<h3>" . htmlspecialchars($p['titulo']) . "</h3>";
          echo "<h4>Disponibilidad</h4>";
          echo "<p>" . htmlspecialchars($p['precio']) . " por semana</p>";
          echo "<a href=\"../carrito/carrito.html?add=" . $p['id'] . "\">Añadir al carrito</a>";
          echo "</article>";
        }
      }
      ?>
    </section>
  </main>
</body>
</html>
