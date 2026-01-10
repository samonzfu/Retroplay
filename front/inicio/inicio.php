<?php
include '../../back/Conexion_BD/conexion.php';
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
    <a href="../mi_cuenta/mi_cuenta.html">
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
      $sql_v = "SELECT * FROM producto WHERE categoria = 'videojuego'";
      $res_v = mysqli_query($conexion, $sql_v);
      while ($p = mysqli_fetch_assoc($res_v)) {
        // Intentamos buscar una imagen con el nombre "slug" derivado del título
        $slug = preg_replace('/[^a-z0-9]+/i', '_', strtolower($p['titulo']));
        $img_path = "css/img/{$slug}.jpg";
        if (!file_exists(__DIR__ . '/' . $img_path)) {
          $img_path = 'css/img/nintendogs.jpg'; // imagen por defecto
        }
        echo "<article>";
        echo "<img src=\"{$img_path}\" alt=\"" . htmlspecialchars($p['titulo']) . "\">";
        echo "<h3>" . htmlspecialchars($p['titulo']) . "</h3>";
        echo "<h4>Disponibilidad</h4>";
        echo "<p>" . htmlspecialchars($p['precio']) . " € por semana</p>";
        echo "<a href=\"../carrito/carrito.html?add=" . $p['id'] . "\">Añadir al carrito</a>";
        echo "</article>";
      }
      ?>
    </section>

    <h2>Consolas</h2>
    <section id="consolas">
      <?php
      $sql_c = "SELECT * FROM producto WHERE categoria = 'consola'";
      $res_c = mysqli_query($conexion, $sql_c);
      while ($p = mysqli_fetch_assoc($res_c)) {
        $slug = preg_replace('/[^a-z0-9]+/i', '_', strtolower($p['titulo']));
        $img_path = "css/img/{$slug}.jpg";
        if (!file_exists(__DIR__ . '/' . $img_path)) {
          $img_path = 'css/img/nintendogs.jpg';
        }
        echo "<article>";
        echo "<img src=\"{$img_path}\" alt=\"" . htmlspecialchars($p['titulo']) . "\">";
        echo "<h3>" . htmlspecialchars($p['titulo']) . "</h3>";
        echo "<h4>Disponibilidad</h4>";
        echo "<p>" . htmlspecialchars($p['precio']) . " € por semana</p>";
        echo "<a href=\"../carrito/carrito.html?add=" . $p['id'] . "\">Añadir al carrito</a>";
        echo "</article>";
      }
      ?>
    </section>
  </main>
</body>
</html>
