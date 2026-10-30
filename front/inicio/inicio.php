<?php
session_start();
// Incluir el archivo de conexión a la base de datos.
include '../../back/Conexion_BD/conexion.php';
include '../includes/layout.php';

// Capturar mensaje flash (si existe) y eliminarlo de la sesión.
$flash = '';
if (isset($_SESSION['flash'])) {
  $flash = $_SESSION['flash'];
  unset($_SESSION['flash']);
}

// VERIFICACIÓN DE CONEXIÓN
if (!isset($conexion) || !$conexion) {
  echo '<h2 style="color:red">Error: no se pudo conectar a la base de datos.</h2>';
  if (isset($_GET['debug'])) {
    echo '<pre style="color:red">' . htmlspecialchars(mysqli_connect_error()) . '</pre>';
  }
  exit;
}

// Todos los productos: primero consolas, luego videojuegos (orden del catálogo).
$productos = [];
$res = mysqli_query($conexion, "SELECT id, categoria, titulo, descripcion, precio, imagen FROM producto ORDER BY LOWER(categoria) = 'videojuego', id");
if ($res) {
  while ($p = mysqli_fetch_assoc($res)) {
    $cat = strtolower(trim($p['categoria'])) === 'consola' ? 'consola' : 'videojuego';
    $folder = $cat === 'consola' ? 'consolas' : 'videojuegos';
    $file = trim($p['imagen']) !== '' ? basename($p['imagen']) : '';
    // Imagen por defecto si no existe el archivo.
    if ($file === '' || !file_exists(__DIR__ . "/../assets/img/$folder/$file")) {
      $folder = 'videojuegos';
      $file = 'nintendogs.jpg';
    }
    $p['cat'] = $cat;
    $p['img'] = "$folder/$file";
    // El precio se guarda como texto ("15€"): lo pasamos a número.
    $p['num'] = (float) str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $p['precio']));
    $productos[] = $p;
  }
} elseif (isset($_GET['debug'])) {
  echo "<p style='color:red'>Error consulta: " . htmlspecialchars(mysqli_error($conexion)) . "</p>";
}

$n_consolas = count(array_filter($productos, function ($p) { return $p['cat'] === 'consola'; }));
$n_juegos = count($productos) - $n_consolas;

rp_header('Catálogo', 'inicio');
?>

<main class="container">
  <section class="hero reveal">
    <h1 class="px">Revive los juegos <span>de siempre</span><i class="blink"></i></h1>
    <p>Alquila consolas y videojuegos retro por semanas. Elige, añade al carrito y reserva en un minuto.</p>
    <div class="cta">
      <a class="btn btn-primary" href="#catalogo">Ver catálogo</a>
      <a class="btn" href="../carrito/carrito.php"><?= rp_icon('cart') ?> Mi carrito</a>
    </div>
    <div class="stats">
      <div><strong><?= $n_consolas ?></strong> consolas</div>
      <div><strong><?= $n_juegos ?></strong> videojuegos</div>
      <div><strong>5€</strong> desde / semana</div>
    </div>
  </section>

  <div class="toolbar" id="catalogo">
    <label class="search">
      <span class="sr-only">Buscar productos</span>
      <?= rp_icon('search') ?>
      <input type="search" id="q" placeholder="Buscar Mario, Game Boy, PlayStation..." autocomplete="off">
    </label>
    <div class="chips" role="group" aria-label="Filtrar por categoría">
      <button class="chip active" type="button" data-filter="todo">Todo</button>
      <button class="chip" type="button" data-filter="consola">Consolas</button>
      <button class="chip" type="button" data-filter="videojuego">Videojuegos</button>
      <button class="chip" type="button" data-filter="fav">♥ Favoritos</button>
    </div>
    <label>
      <span class="sr-only">Ordenar por</span>
      <select id="sort">
        <option value="default">Destacados</option>
        <option value="price-asc">Precio: menor a mayor</option>
        <option value="price-desc">Precio: mayor a menor</option>
        <option value="name">Nombre A-Z</option>
      </select>
    </label>
  </div>

  <p class="muted" id="result-count" aria-live="polite"></p>

  <section class="grid" id="grid">
    <?php foreach ($productos as $i => $p): ?>
      <article class="card reveal" data-id="<?= (int) $p['id'] ?>" data-cat="<?= $p['cat'] ?>"
        data-title="<?= htmlspecialchars($p['titulo'], ENT_QUOTES) ?>" data-price="<?= $p['num'] ?>"
        data-img="<?= htmlspecialchars($p['img'], ENT_QUOTES) ?>"
        data-desc="<?= htmlspecialchars($p['descripcion'], ENT_QUOTES) ?>" data-order="<?= $i ?>">
        <span class="tag"><?= $p['cat'] === 'consola' ? 'CONSOLA' : 'JUEGO' ?></span>
        <button class="fav" type="button" aria-label="Marcar como favorito" aria-pressed="false"><?= rp_icon('heart') ?></button>
        <button class="thumb" type="button" data-action="details" aria-label="Ver detalles de <?= htmlspecialchars($p['titulo'], ENT_QUOTES) ?>">
          <img src="../assets/img/<?= htmlspecialchars($p['img'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($p['titulo'], ENT_QUOTES) ?>" loading="lazy">
        </button>
        <div class="body">
          <h3><?= htmlspecialchars($p['titulo']) ?></h3>
          <p class="desc"><?= htmlspecialchars($p['descripcion']) ?></p>
          <div class="foot">
            <span class="price"><?= number_format($p['num'], 0, ',', '.') ?>€ <small>/ semana</small></span>
            <button class="btn btn-primary btn-sm" type="button" data-action="add"><?= rp_icon('plus') ?> Añadir</button>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </section>

  <div class="empty" id="empty" hidden>
    <?= rp_icon('search') ?>
    <h2 class="px">Sin resultados</h2>
    <p>Prueba con otra búsqueda o cambia el filtro.</p>
    <button class="btn" type="button" id="reset-filters">Quitar filtros</button>
  </div>
</main>

<dialog id="product-dialog" aria-labelledby="pd-title">
  <button class="icon-btn modal-close" type="button" data-close aria-label="Cerrar"><?= rp_icon('x') ?></button>
  <div class="modal-body">
    <div class="modal-img"><img id="pd-img" src="" alt=""></div>
    <span class="tag" id="pd-tag" style="position:static;width:max-content"></span>
    <h2 id="pd-title"></h2>
    <p class="muted" id="pd-desc" style="margin:0"></p>
    <div class="modal-actions">
      <span class="price" id="pd-price" style="margin-right:auto"></span>
      <button class="btn btn-ghost" type="button" data-close>Cerrar</button>
      <button class="btn btn-primary" type="button" id="pd-add"><?= rp_icon('plus') ?> Añadir al carrito</button>
    </div>
  </div>
</dialog>

<?php if ($flash !== ''): ?>
  <script>window.addEventListener('DOMContentLoaded', function () { RP.toast(<?= json_encode($flash, JSON_HEX_TAG) ?>, 'ok'); });</script>
<?php endif; ?>

<?php rp_footer(['inicio.js']); ?>
