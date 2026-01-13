<?php
session_start();
// Cargamos el carrito de la sesión (si existe)
$server_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Carrito | RetroPlay</title>
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

  <main>
    <!-- Contenido del carrito -->
    <h2>Tu Carrito</h2>
    <section id="cart-list"></section>
    <div id="cart-footer"><strong>Total: <span id="total">0.00</span> €</strong> <button id="checkout">Reservar</button></div>
  </main>

<script>
var serverCart = <?php echo json_encode($server_cart, JSON_HEX_TAG); ?> || [];

// Mostrar un mensaje simple en la esquina (toast)
function showToast(msg){
  var d = document.createElement('div');
  d.id = 'copilot-toast';
  d.textContent = msg;
  d.style.position = 'fixed'; d.style.right = '20px'; d.style.top = '20px'; d.style.background = '#333'; d.style.color = '#fff'; d.style.padding = '10px 14px'; d.style.borderRadius = '6px'; d.style.zIndex = 9999;
  document.body.appendChild(d);
  setTimeout(function(){ if (d.parentNode) d.parentNode.removeChild(d); }, 2000);
}

// Unir carrito local con carrito del servidor (suma cantidades)
function mergeCarts(local, server){
  var merged = [];
  for (var i = 0; i < local.length; i++) {
    merged.push({ id: local[i].id, title: local[i].title, price: local[i].price, img: local[i].img, qty: local[i].qty || 1 });
  }
  for (var j = 0; j < server.length; j++) {
    var found = false;
    for (var k = 0; k < merged.length; k++) {
      if (merged[k].id == server[j].id) { merged[k].qty = (merged[k].qty || 1) + (server[j].qty || 1); found = true; break; }
    }
    if (!found) merged.push({ id: server[j].id, title: server[j].title, price: server[j].price, img: server[j].img, qty: server[j].qty || 1 });
  }
  return merged;
}

function renderCart(){
  var list = document.getElementById('cart-list');
  var cart = JSON.parse(localStorage.getItem('cart') || '[]');
  if (!cart.length) { list.innerHTML = '<p>El carrito está vacío.</p>'; document.getElementById('total').textContent = '0.00'; return; }
  var html = '';
  var total = 0;
  for (var i = 0; i < cart.length; i++) {
    var item = cart[i];
    var price = parseFloat(String(item.price || '').replace('€','').replace(',','.')) || 0;
    total += price * (item.qty || 1);
    html += '<article><img src="' + (item.img || 'css/img/nintendogs.jpg') + '" alt="' + (item.title||'') + '"><h3>' + (item.title||'') + '</h3><p>' + price + ' € — Cantidad: ' + (item.qty||1) + '</p><a href="#" data-id="' + item.id + '" class="remove">Eliminar</a></article>';
  }
  list.innerHTML = html;
  document.getElementById('total').textContent = total.toFixed(2);
}

function updateCartServer(cart, cb){
  var fd = new FormData(); fd.append('accion','update_cart'); fd.append('cart', JSON.stringify(cart));
  fetch('../../back/Procesar/procesar.php', { method: 'POST', body: fd }).then(function(r){ return r.json(); }).then(function(res){ if (cb) cb(res); }).catch(function(){ if (cb) cb({ success: false }); });
}

// Eventos
document.addEventListener('DOMContentLoaded', function(){
  try {
    var local = JSON.parse(localStorage.getItem('cart') || '[]');
    var merged = mergeCarts(local, serverCart);
    localStorage.setItem('cart', JSON.stringify(merged));
    updateCartServer(merged, function(){ renderCart(); });
  } catch(e) { renderCart(); }

  document.body.addEventListener('click', function(e){
    if (e.target && e.target.className === 'remove'){
      e.preventDefault();
      var id = e.target.getAttribute('data-id');
      var cart = JSON.parse(localStorage.getItem('cart') || '[]');
      var newCart = [];
      for (var i = 0; i < cart.length; i++) { if (cart[i].id != id) newCart.push(cart[i]); }
      localStorage.setItem('cart', JSON.stringify(newCart));
      updateCartServer(newCart, function(){ renderCart(); showToast('Producto eliminado del carrito'); });
    }
  });

  document.getElementById('checkout').addEventListener('click', function(){
    var cart = JSON.parse(localStorage.getItem('cart') || '[]');
    if (!cart.length) { showToast('El carrito está vacío'); return; }
    if (!confirm('¿Confirmar reserva de ' + cart.length + ' productos?')) return;

    var items = [];
    for (var i = 0; i < cart.length; i++) { items.push({ id: cart[i].id, qty: cart[i].qty || 1 }); }

    var fd = new FormData(); fd.append('accion','create_reserva'); fd.append('reservas', JSON.stringify(items));
    fetch('../../back/Procesar/procesar.php', { method: 'POST', body: fd }).then(function(r){ return r.json(); }).then(function(data){
      if (data && data.success) {
        localStorage.setItem('cart','[]');
        updateCartServer([], function(){ showToast('¡Reserva registrada!'); setTimeout(function(){ window.location.href='../mis_reservas/reservas.php?reserved=1'; }, 900); });
      } else { alert('Error al crear reserva: ' + (data && data.error ? data.error : 'Error desconocido')); }
    }).catch(function(){ alert('Error de red. Inténtalo de nuevo.'); });

  });
});
</script>

</body>
</html>