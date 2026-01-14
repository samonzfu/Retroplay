<?php
session_start();
// Incluir el archivo de conexión a la base de datos.
include '../../back/Conexion_BD/conexion.php';

// Capturar mensaje flash (si existe) y eliminarlo de la sesión.
$flash = '';
if (isset($_SESSION['flash'])) {
  $flash = $_SESSION['flash'];
  unset($_SESSION['flash']);
}

// VERIFICACIÓN DE CONEXIÓN
// Verificamos si la variable $conexion existe y es válida.
if (!isset($conexion) || !$conexion) {
  echo '<h2 style="color:red">Error: no se pudo conectar a la base de datos.</h2>';
  // Si el modo debug está activo, mostramos el error específico de MySQL.
  if (isset($_GET['debug'])) {
    echo '<pre style="color:red">' . htmlspecialchars(mysqli_connect_error()) . '</pre>';
  }
  echo '</body></html>';
  // Detenemos la ejecución si no hay base de datos.
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
  <!-- Navegación -->
  <nav>
    <a href="../inicio/inicio.php">
      <img src="css/img/productos.png" alt="Acceso a mi cuenta">
    </a>
    <a href="../mi_cuenta/mi_cuenta.php">
      <img src="css/img/mi_cuenta.png" alt="Acceso a mi cuenta">
    </a>
    <a href="../mis_reservas/reservas.php">
      <img src="css/img/reservas.png" alt="Ver reservas">
    </a>
    <a href="../carrito/carrito.php">
      <img src="css/img/carrito.png" alt="Ver carrito de compras">
    </a>
  </nav>

  <?php if (!empty($flash)) { echo "<div id='flash' style='position:fixed;right:20px;top:20px;background:#333;color:#fff;padding:10px;border-radius:6px;z-index:9999'>" . htmlspecialchars($flash) . "</div><script>setTimeout(()=>{var e=document.getElementById('flash'); if(e) e.remove();},2000);</script>"; } ?>

  <!-- Contenido principal -->
  <main>
    <h2>Videojuegos</h2>
    <section id="videojuegos">
      <?php
      // SECCIÓN DE VIDEOJUEGOS
      
      // Consulta SQL para seleccionar todos los productos donde la categoría sea 'videojuego'.
      // LOWER() convierte la categoría a minúsculas para evitar problemas de mayúsculas/minúsculas.
      $sql_v = "SELECT * FROM producto WHERE LOWER(categoria) = 'videojuego'";

      // Ejecutar la consulta contra la base de datos.
      $res_v = mysqli_query($conexion, $sql_v);

      // Verificar si la consulta falló.
      if (!$res_v) {
        if (isset($_GET['debug'])) {
          echo "<p style='color:red'>Error consulta videojuegos: " . htmlspecialchars(mysqli_error($conexion)) . "</p>";
        }
      } else {
        // Verificar si no se encontraron productos.
        if (mysqli_num_rows($res_v) === 0 && isset($_GET['debug'])) {
          echo "<p style='color:orange'>Aviso: no se encontraron videojuegos.</p>";
        }

        // Bucle while: recorre cada fila devuelta por la base de datos.
        while ($p = mysqli_fetch_assoc($res_v)) {

          // Lógica para determinar la imagen del producto.
          // Si el campo 'imagen' no está vacío, usamos ese nombre.
          $filename = isset($p['imagen']) && trim($p['imagen']) !== '' ? basename($p['imagen']) : '';

          if ($filename !== '') {
            $img_path = "css/img/videojuegos/{$filename}";
            // Verificamos si el archivo de imagen realmente existe en el servidor.
            if (!file_exists(__DIR__ . '/' . $img_path)) {
              if (isset($_GET['debug'])) {
                echo "<p style='color:orange'>Aviso: imagen no encontrada: " . htmlspecialchars($img_path) . "</p>";
              }
              // Imagen por defecto si no existe el archivo.
              $img_path = 'css/img/videojuegos/nintendogs.jpg';
            }
          } else {
            // Imagen por defecto si no hay nombre de imagen en la BD.
            $img_path = 'css/img/videojuegos/nintendogs.jpg';
          }

          // Renderizar el HTML de cada artículo (producto).
          echo "<article>";
          // Mostramos la imagen del producto.
          echo "<img src=\"{$img_path}\" alt=\"" . htmlspecialchars($p['titulo']) . "\">";
          // Mostramos el título.
          echo "<h3>" . htmlspecialchars($p['titulo']) . "</h3>";
          echo "<h4>Disponibilidad</h4>";
          // Mostramos el precio.
          echo "<p>" . htmlspecialchars($p['precio']) . " por semana</p>";
          // Botón de 'Añadir al carrito' con atributos data-* para que JavaScript los lea.
          echo '<a href="#" class="add-to-cart" data-id="' . htmlspecialchars($p['id'], ENT_QUOTES) . '" data-title="' . htmlspecialchars($p['titulo'], ENT_QUOTES) . '" data-price="' . htmlspecialchars($p['precio'], ENT_QUOTES) . '" data-img="' . htmlspecialchars($img_path, ENT_QUOTES) . '">Añadir al carrito</a>';
          echo "</article>";
        }
      }
      ?>
    </section>

    <h2>Consolas</h2>
    <section id="consolas">
      <?php

      // SECCIÓN DE CONSOLAS

      // Consulta SQL para seleccionar consolas.
      $sql_c = "SELECT * FROM producto WHERE LOWER(categoria) = 'consola'";

      // Ejecutar consulta.
      $res_c = mysqli_query($conexion, $sql_c);

      // Comprobar errores.
      if (!$res_c) {
        if (isset($_GET['debug'])) {
          echo "<p style='color:red'>Error consulta consolas: " . htmlspecialchars(mysqli_error($conexion)) . "</p>";
        }
      } else {
        // Comprobar si está vacío.
        if (mysqli_num_rows($res_c) === 0 && isset($_GET['debug'])) {
          echo "<p style='color:orange'>Aviso: no se encontraron consolas.</p>";
        }

        // Recorrer los resultados.
        while ($p = mysqli_fetch_assoc($res_c)) {
          // Lógica de imagen (similar a videojuegos pero buscando en carpeta consolas).
          $filename = isset($p['imagen']) && trim($p['imagen']) !== '' ? basename($p['imagen']) : '';

          if ($filename !== '') {
            $img_path = "css/img/consolas/{$filename}";
            // Verificación de existencia del archivo.
            if (!file_exists(__DIR__ . '/' . $img_path)) {
              if (isset($_GET['debug'])) {
                echo "<p style='color:orange'>Aviso: imagen no encontrada: " . htmlspecialchars($img_path) . "</p>";
              }
              $img_path = 'css/img/nintendogs.jpg'; // Imagen fallback (puede que quieras cambiarla a una genérica de consola)
            }
          } else {
            $img_path = 'css/img/nintendogs.jpg';
          }

          // Renderizar HTML.
          echo "<article>";
          echo "<img src=\"{$img_path}\" alt=\"" . htmlspecialchars($p['titulo']) . "\">";
          echo "<h3>" . htmlspecialchars($p['titulo']) . "</h3>";
          echo "<h4>Disponibilidad</h4>";
          echo "<p>" . htmlspecialchars($p['precio']) . " por semana</p>";
          // Botón 'Añadir al carrito'.
          echo '<a href="#" class="add-to-cart" data-id="' . htmlspecialchars($p['id'], ENT_QUOTES) . '" data-title="' . htmlspecialchars($p['titulo'], ENT_QUOTES) . '" data-price="' . htmlspecialchars($p['precio'], ENT_QUOTES) . '" data-img="' . htmlspecialchars($img_path, ENT_QUOTES) . '">Añadir al carrito</a>';
          echo "</article>";
        }
      }
      ?>
    </section>
  </main>

  <script>
    // Añadir al carrito: guarda producto en localStorage y muestra notificación (sin redirigir)
    function showToast(msg) {//mensaje flotante
      var existing = document.getElementById('copilot-toast');
      if (existing) { clearTimeout(existing._timeout); existing.remove(); }
      var d = document.createElement('div'); d.id = 'copilot-toast'; d.textContent = msg;
      d.style.position = 'fixed'; d.style.right = '20px'; d.style.top = '20px'; d.style.background = '#333'; d.style.color = '#fff'; d.style.padding = '10px 14px'; d.style.borderRadius = '6px'; d.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)'; d.style.zIndex = 9999; d.style.opacity = 1; d.style.transition = 'opacity 0.3s';
      document.body.appendChild(d);
      d._timeout = setTimeout(function () { d.style.opacity = '0'; setTimeout(function () { if (d.parentNode) d.parentNode.removeChild(d); }, 300); }, 2000);
    }

    document.addEventListener('click', function (e) {
      if (e.target.matches('.add-to-cart')) {// si clicka en el carrito hace:
        e.preventDefault();
        var el = e.target;
        var product = {
          id: el.dataset.id,
          title: el.dataset.title,
          price: parseFloat((el.dataset.price || '').replace(',', '.')) || 0,
          img: el.dataset.img,
          qty: 1
        };
        var cart = JSON.parse(localStorage.getItem('cart') || '[]');
        var existing = cart.find(function (it) { return it.id === product.id; });
        if (existing) { existing.qty = (existing.qty || 1) + 1; }
        else { cart.push(product); }
        localStorage.setItem('cart', JSON.stringify(cart));
        showToast('Producto añadido al carrito');
      }
    });
  </script>

</body>

</html>