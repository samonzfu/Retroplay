<?php
session_start();
include '../../back/Conexion_BD/conexion.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../login/login.html');
  exit;
}

$uid = intval($_SESSION['user_id']);
$sql = "SELECT id, fecha FROM reservas WHERE usuario_id = $uid ORDER BY fecha DESC";
$res = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reservas | RetroPlay</title>
  <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
  <nav>
    <a href="../inicio/inicio.php"><img src="css/img/productos.png" alt="Acceso a mi cuenta"></a>
    <a href="../mi_cuenta/mi_cuenta.php"><img src="css/img/mi_cuenta.png" alt="Acceso a mi cuenta"></a>
    <a href="../mis_reservas/reservas.php"><img src="css/img/reservas.png" alt="Ver reservas"></a>
    <a href="../carrito/carrito.php"><img src="css/img/carrito.png" alt="Ver carrito de compras"></a>
  </nav>

  <main>
    <h2>Reservas Activas</h2>

    <section id="reservas-activas">
      <?php
      if (!mysqli_num_rows($res)) {
        echo '<p>No tienes reservas registradas en el servidor.</p>';
      } else {
        while ($r = mysqli_fetch_assoc($res)) {
          echo '<section class="reserva">';
          echo '<h3>Reserva #' . htmlspecialchars($r['id']) . ' — ' . htmlspecialchars($r['fecha']) . '</h3>';
          echo '<div class="items">';

          $rid = intval($r['id']);
          $sql2 = "SELECT p.id, p.titulo, p.precio, p.imagen FROM lineareservas lr JOIN producto p ON lr.producto_id = p.id WHERE lr.reservas_id = $rid";
          $res2 = mysqli_query($conexion, $sql2);

          while ($p = mysqli_fetch_assoc($res2)) {
            $img = !empty($p['imagen']) ? 'css/img/videojuegos/' . htmlspecialchars(basename($p['imagen'])) : 'css/img/nintendogs.jpg';
            echo '<article><img src="' . $img . '" alt="' . htmlspecialchars($p['titulo']) . '"><h4>' . htmlspecialchars($p['titulo']) . '</h4><p>' . htmlspecialchars($p['precio']) . '</p></article>';
          }

          echo '</div></section>';
        }
      }
      ?>
    </section>

  </main>
</body>
</html>