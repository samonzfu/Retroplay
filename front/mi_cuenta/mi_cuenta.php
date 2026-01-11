<?php
// Iniciar el manejo de sesiones. Esto es necesario para acceder a las variables $_SESSION.
session_start();

// Verificar si el usuario NO ha iniciado sesión comprobando si 'user_id' no existe.
if (!isset($_SESSION['user_id'])) {
  // Si no hay sesión activa, redirigir al usuario al formulario de login.
  header('Location: ../login/login.html');
  // Detener la ejecución del script para evitar que se cargue el resto de la página.
  exit;
}

// Incluir el archivo de conexión a la base de datos.
include '../../back/Conexion_BD/conexion.php';

// Obtener el ID del usuario desde la sesión actual.
$id = $_SESSION['user_id'];

// --- FORMA INSEGURA (Consulta Directa - Estilo Procedural) ---
// Concatenamos la variable $id directamente. Estilo similar a procesar.php
$sql = "SELECT nickname, correo, telefono FROM usuarios WHERE id = $id";
$res = mysqli_query($conexion, $sql);

if ($res && mysqli_num_rows($res) > 0) {
  $user = mysqli_fetch_assoc($res);
} else {
  $user = ['nickname' => '', 'correo' => '', 'telefono' => ''];
}

/*
// --- FORMA SEGURA (Prepared Statement) [COMENTADA] ---
// Esta es la forma recomendada usando consultas preparadas para evitar inyecciones SQL. 

// Preparar una consulta segura (Prepared Statement) para obtener los datos del usuario.
// Usar '?' evita inyecciones SQL.
$stmt = $conexion->prepare("SELECT nickname, correo, telefono FROM usuarios WHERE id = ?");

if ($stmt) {
  // Enlazar el parámetro $id a la consulta (la 'i' indica que es un entero).
  $stmt->bind_param("i", $id);

  // Ejecutar la consulta.
  $stmt->execute();

  // Obtener el resultado de la consulta.
  $res = $stmt->get_result();

  // Obtener los datos del usuario como un array asociativo.
  $user = $res->fetch_assoc();

  // Cerrar la sentencia preparada para liberar recursos.
  $stmt->close();
} else {
  // Si falla la preparación, inicializar $user vacío para evitar errores posteriores.
  $user = ['nickname' => '', 'correo' => '', 'telefono' => ''];
}
*/
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