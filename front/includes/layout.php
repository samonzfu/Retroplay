<?php
// Plantilla común: cabecera con navegación y pie de página.
// Cada página llama a rp_header() al principio y a rp_footer() al final.

// Iconos SVG (trazo, 24x24). Se usan en la navegación y en los botones.
function rp_icon($name)
{
  $paths = [
    'gamepad' => '<path d="M6 11h4M8 9v4M15 12h.01M18 10h.01"/><path d="M17.3 5H6.7a4 4 0 0 0-4 3.6C2.6 9.4 2 14.5 2 16a3 3 0 0 0 3 3c1 0 1.5-.5 2-1l1.4-1.4a2 2 0 0 1 1.4-.6h4.4a2 2 0 0 1 1.4.6L17 18c.5.5 1 1 2 1a3 3 0 0 0 3-3c0-1.5-.6-6.6-.7-7.3A4 4 0 0 0 17.3 5z"/>',
    'home' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
    'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
    'cart' => '<circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/><path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L21 8H6"/>',
    'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
    'moon' => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>',
    'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
    'heart' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z"/>',
    'chevron' => '<path d="m6 9 6 6 6-6"/>',
    'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
    'eye-off' => '<path d="M17.9 17.9A10.1 10.1 0 0 1 12 19c-6.5 0-10-7-10-7a18 18 0 0 1 5.1-5.9M9.9 5.2A9.7 9.7 0 0 1 12 5c6.5 0 10 7 10 7a18 18 0 0 1-2.2 3.2M1 1l22 22"/>',
    'check' => '<path d="M20 6 9 17l-5-5"/>',
    'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
    'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
    'login' => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/>',
    'repeat' => '<path d="m17 2 4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14M7 22l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>',
    'plus' => '<path d="M12 5v14M5 12h14"/>',
  ];
  $p = isset($paths[$name]) ? $paths[$name] : '';
  return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}

// Cabecera HTML + barra de navegación.
// $active: 'inicio' | 'carrito' | 'reservas' | 'cuenta'
function rp_header($title, $active = '')
{
  $logged = isset($_SESSION['user_id']);
  $links = [
    'inicio' => ['../inicio/inicio.php', 'home', 'Catálogo'],
    'reservas' => ['../mis_reservas/reservas.php', 'calendar', 'Reservas'],
    'cuenta' => ['../mi_cuenta/mi_cuenta.php', 'user', $logged ? 'Mi cuenta' : 'Entrar'],
  ];
  if (!$logged) {
    $links['cuenta'][0] = '../login/login.html';
  }
  ?>
  <!DOCTYPE html>
  <html lang="es" data-theme="dark">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> | RetroPlay</title>
    <script>
      // Aplicar el tema guardado antes de pintar para evitar parpadeos.
      try { document.documentElement.setAttribute('data-theme', localStorage.getItem('retroplay_theme') || 'dark'); } catch (e) { }
    </script>
    <link rel="stylesheet" href="../assets/css/retroplay.css">
  </head>

  <body>
    <header class="topbar">
      <div class="container">
        <a class="brand" href="../inicio/inicio.php" aria-label="RetroPlay, ir al catálogo">
          <?= rp_icon('gamepad') ?>
          <span>Retro<b>Play</b></span>
        </a>

        <nav class="nav" aria-label="Principal">
          <?php foreach ($links as $key => $l): ?>
            <a href="<?= $l[0] ?>" <?= $active === $key ? 'class="active" aria-current="page"' : '' ?>>
              <?= rp_icon($l[1]) ?><span><?= $l[2] ?></span>
            </a>
          <?php endforeach; ?>
          <a id="cart-link" href="../carrito/carrito.php" <?= $active === 'carrito' ? 'class="active" aria-current="page"' : '' ?>>
            <?= rp_icon('cart') ?><span>Carrito</span>
            <b id="cart-badge" class="badge" aria-label="Artículos en el carrito">0</b>
          </a>
          <button class="icon-btn" id="theme-toggle" type="button" aria-label="Cambiar tema claro u oscuro">
            <span class="i-sun"><?= rp_icon('sun') ?></span><span class="i-moon"><?= rp_icon('moon') ?></span>
          </button>
        </nav>
      </div>
    </header>
    <?php
}

// Pie de página y scripts comunes. $scripts: lista de ficheros JS de la página.
function rp_footer($scripts = [])
{
  ?>
    <footer class="footer">
      <div class="container"><b>RetroPlay</b> · Alquiler de consolas y videojuegos clásicos</div>
    </footer>
    <div id="toasts" class="toasts" aria-live="polite"></div>
    <script src="../assets/js/app.js"></script>
    <?php foreach ($scripts as $s): ?>
      <script src="../assets/js/<?= htmlspecialchars($s) ?>"></script>
    <?php endforeach; ?>
  </body>

  </html>
  <?php
}
