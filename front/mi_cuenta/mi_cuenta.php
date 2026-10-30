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

// Incluir el archivo de conexión a la base de datos y la plantilla.
include '../../back/Conexion_BD/conexion.php';
include '../includes/layout.php';

// Obtener id del usuario desde la sesión (como entero)
$id = intval($_SESSION['user_id']);

$stmt = $conexion->prepare("SELECT nickname, correo, telefono FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc() ?: ['nickname' => '', 'correo' => '', 'telefono' => ''];
$stmt->close();

// Estadísticas rápidas
$stmt = $conexion->prepare("SELECT COUNT(*) AS n FROM reservas WHERE usuario_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$n_reservas = (int) $stmt->get_result()->fetch_assoc()['n'];
$stmt->close();

$inicial = mb_strtoupper(mb_substr($user['nickname'] !== '' ? $user['nickname'] : '?', 0, 1));

rp_header('Mi cuenta', 'cuenta');
?>

<main class="container">
  <h1 class="page-title px">Mi cuenta</h1>

  <div class="account">
    <aside class="panel profile reveal">
      <div class="avatar"><?= htmlspecialchars($inicial) ?></div>
      <h2><?= htmlspecialchars($user['nickname']) ?></h2>
      <p class="muted" style="margin:0"><?= htmlspecialchars($user['correo']) ?></p>
      <div class="mini-stats">
        <div><strong><?= $n_reservas ?></strong><span>Reservas</span></div>
        <div><strong id="stat-cart">0</strong><span>En el carrito</span></div>
      </div>
      <a class="btn btn-block" href="../mis_reservas/reservas.php"><?= rp_icon('calendar') ?> Ver mis reservas</a>
      <a class="btn btn-ghost btn-danger btn-block" style="margin-top:10px" href="../login/logout.php"><?= rp_icon('logout') ?> Cerrar sesión</a>
    </aside>

    <section class="panel reveal">
      <h2>Información personal</h2>

      <?php if (isset($_GET['ok'])): ?>
        <p class="alert success show" role="status" style="margin-bottom:16px">Datos actualizados correctamente.</p>
      <?php elseif (isset($_GET['error'])): ?>
        <p class="alert show" role="alert" style="margin-bottom:16px"><?= htmlspecialchars($_GET['error']) ?></p>
      <?php endif; ?>

      <form class="form" method="post" action="../../back/Procesar/procesar.php">
        <label class="field">
          <span>Usuario</span>
          <input class="input" type="text" name="nickname" value="<?= htmlspecialchars($user['nickname']) ?>" required>
        </label>
        <label class="field">
          <span>Correo</span>
          <input class="input" type="email" name="correo" value="<?= htmlspecialchars($user['correo']) ?>" required>
        </label>
        <label class="field">
          <span>Teléfono</span>
          <input class="input" type="tel" name="telefono" value="<?= htmlspecialchars($user['telefono']) ?>">
        </label>
        <div class="field">
          <label for="new_password">Nueva contraseña (opcional)</label>
          <div class="pw-wrap">
            <input class="input" type="password" id="new_password" name="new_password" placeholder="Déjala en blanco para mantener la actual" autocomplete="new-password">
            <button type="button" data-toggle-pw="new_password" aria-label="Mostrar u ocultar contraseña">
              <span class="i-show"><?= rp_icon('eye') ?></span><span class="i-hide" hidden><?= rp_icon('eye-off') ?></span>
            </button>
          </div>
          <span class="hint muted">Mínimo 8 caracteres, con mayúscula, minúscula, número y símbolo.</span>
        </div>
        <input type="hidden" name="accion" value="update_profile">
        <div><button class="btn btn-primary" type="submit">Guardar cambios</button></div>
      </form>
    </section>
  </div>
</main>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('stat-cart').textContent = RP.cart.count();
  });
</script>
<?php rp_footer(['password.js']); ?>
