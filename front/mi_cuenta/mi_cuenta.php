<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: ../login/login.html');
  exit;
}
include '../../back/Conexion_BD/conexion.php';

$id = $_SESSION['user_id'];
$stmt = $conexion->prepare("SELECT nickname, correo, telefono FROM usuarios WHERE id = ?");
if ($stmt) {
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  $user = $res->fetch_assoc();
  $stmt->close();
} else {
  $user = ['nickname' => '', 'correo' => '', 'telefono' => ''];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RetroPlay | Mi Cuenta</title>
  <link rel="stylesheet" href="css/estilo.css">
</head>

<body>
  <nav>
    <a href="../inicio/inicio.php">
      <img src="css/img/productos.png" alt="Acceso a mi cuenta">
    </a>
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

  <main>
    <h2>Información Personal</h2>
    <section id="datos-personales">

      <article>
        <form method="post" action="../../back/Procesar/procesar.php">
          <label>Usuario
            <input type="text" name="nickname" value="<?= htmlspecialchars($user['nickname']) ?>" required>
          </label>
          <label>Correo
            <input type="email" name="correo" value="<?= htmlspecialchars($user['correo']) ?>" required>
          </label>
          <label>Teléfono
            <input type="text" name="telefono" value="<?= htmlspecialchars($user['telefono']) ?>">
          </label>
          <label>Nueva contraseña (opcional)
            <input type="password" name="new_password" placeholder="Dejar en blanco para mantener">
          </label>
          <input type="hidden" name="accion" value="update_profile">
          <input type="submit" value="Actualizar">
        </form>
        <p><a href="../login/logout.php">Cerrar sesión</a></p>
      </article>

    </section>

  </main>
</body>
</html>