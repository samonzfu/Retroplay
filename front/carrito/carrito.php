<?php
session_start();
include '../includes/layout.php';

// El contenido del carrito vive en el navegador (localStorage); aquí solo
// necesitamos saber si hay sesión para permitir reservar.
$logged = isset($_SESSION['user_id']);

rp_header('Carrito', 'carrito');
?>

<main class="container">
  <h1 class="page-title px">Tu carrito <small id="cart-sub"></small></h1>

  <div class="cart-layout" id="cart-layout" hidden>
    <div class="cart-items" id="cart-list"></div>

    <aside class="summary">
      <h2 class="px">Resumen</h2>
      <dl>
        <div><dt>Artículos</dt><dd id="s-items">0</dd></div>
        <div><dt>Consolas</dt><dd id="s-consolas">0,00 €</dd></div>
        <div><dt>Videojuegos</dt><dd id="s-juegos">0,00 €</dd></div>
      </dl>
      <div class="total"><span>Total</span><strong id="total">0,00 €</strong></div>

      <?php if ($logged): ?>
        <button class="btn btn-primary btn-block" id="checkout" type="button">Reservar ahora</button>
      <?php else: ?>
        <a class="btn btn-primary btn-block" href="../login/login.html"><?= rp_icon('login') ?> Inicia sesión para reservar</a>
        <p class="note">Tu carrito se guarda: podrás continuar después de entrar.</p>
      <?php endif; ?>
      <button class="btn btn-ghost btn-sm link-clear btn-danger" id="clear-cart" type="button">Vaciar carrito</button>
    </aside>
  </div>

  <div class="empty" id="cart-empty" hidden>
    <?= rp_icon('cart') ?>
    <h2>Tu carrito está vacío</h2>
    <p>Añade consolas y juegos desde el catálogo.</p>
    <a class="btn btn-primary" href="../inicio/inicio.php">Ir al catálogo</a>
  </div>
</main>

<!-- Confirmación de la reserva -->
<dialog id="confirm-dialog" aria-labelledby="cd-title">
  <div class="modal-body" id="cd-body">
    <h2 id="cd-title">Confirmar reserva</h2>
    <p class="muted" id="cd-text" style="margin:0"></p>
    <div class="alert" id="cd-error" role="alert"></div>
    <div class="modal-actions">
      <button class="btn btn-ghost" type="button" id="cd-cancel">Cancelar</button>
      <button class="btn btn-primary" type="button" id="cd-ok">Confirmar</button>
    </div>
  </div>
</dialog>

<script>
  window.RP_LOGGED = <?= $logged ? 'true' : 'false' ?>;
</script>
<?php rp_footer(['carrito.js']); ?>
