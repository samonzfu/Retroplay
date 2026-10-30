<?php
session_start();
include '../../back/Conexion_BD/conexion.php';
include '../includes/layout.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../login/login.html');
  exit;
}

$uid = intval($_SESSION['user_id']);
$ahora = time();

// Precio guardado como texto ("15€") -> número
function precio_num($precio)
{
  return (float) str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $precio));
}

// Reservas del usuario con sus líneas, calculando cuándo termina cada alquiler.
$reservas = [];
$stmt = $conexion->prepare("SELECT id, fecha, total FROM reservas WHERE usuario_id = ? ORDER BY fecha DESC, id DESC");
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();

$stmt2 = $conexion->prepare("SELECT p.id, p.titulo, p.precio, p.imagen, p.categoria, lr.cantidad, lr.semanas
                             FROM lineareservas lr JOIN producto p ON lr.producto_id = p.id
                             WHERE lr.reservas_id = ?");
while ($r = $res->fetch_assoc()) {
  $inicio = strtotime($r['fecha']);
  $fin_max = $inicio;
  $suma = 0;
  $lineas = [];

  $rid = intval($r['id']);
  $stmt2->bind_param("i", $rid);
  $stmt2->execute();
  $res2 = $stmt2->get_result();
  while ($p = $res2->fetch_assoc()) {
    $cat = strtolower(trim($p['categoria'])) === 'consola' ? 'consola' : 'videojuego';
    $folder = $cat === 'consola' ? 'consolas' : 'videojuegos';
    $file = trim($p['imagen']) !== '' ? basename($p['imagen']) : '';
    if ($file === '' || !file_exists(__DIR__ . "/../assets/img/$folder/$file")) {
      $folder = 'videojuegos';
      $file = 'nintendogs.jpg';
    }
    $p['cat'] = $cat;
    $p['img'] = "$folder/$file";
    $p['num'] = precio_num($p['precio']);
    $p['sub'] = $p['num'] * $p['cantidad'] * $p['semanas'];
    $suma += $p['sub'];
    $fin_max = max($fin_max, strtotime("+{$p['semanas']} weeks", $inicio));
    $lineas[] = $p;
  }

  $r['lineas'] = $lineas;
  // Las reservas anteriores a la columna "total" se calculan con las líneas.
  $r['total_calc'] = $r['total'] > 0 ? (float) $r['total'] : $suma;
  $r['fin'] = $fin_max;
  $r['activa'] = $fin_max > $ahora;
  $reservas[] = $r;
}
$stmt2->close();
$stmt->close();

$activas = count(array_filter($reservas, function ($r) { return $r['activa']; }));

rp_header('Mis reservas', 'reservas');
?>

<main class="container">
  <h1 class="page-title px">Mis reservas
    <small><?= count($reservas) ?> en total · <?= $activas ?> activas</small>
  </h1>

  <?php if (isset($_GET['reserved'])): ?>
    <p class="alert success show" role="status" style="margin-bottom:20px">¡Reserva confirmada! Aquí tienes los detalles.</p>
  <?php endif; ?>

  <?php if (count($reservas) === 0): ?>
    <div class="empty">
      <?= rp_icon('calendar') ?>
      <h2>Aún no tienes reservas</h2>
      <p>Cuando reserves algo aparecerá aquí.</p>
      <a class="btn btn-primary" href="../inicio/inicio.php">Explorar el catálogo</a>
    </div>
  <?php else: ?>
    <div class="reservas">
      <?php foreach ($reservas as $i => $r): ?>
        <details class="reserva reveal" <?= $i === 0 ? 'open' : '' ?>>
          <summary>
            <span class="title">Reserva #<?= (int) $r['id'] ?></span>
            <span class="status <?= $r['activa'] ? 'active' : 'done' ?>"><?= $r['activa'] ? 'Activa' : 'Finalizada' ?></span>
            <span class="date"><?= date('d/m/Y H:i', strtotime($r['fecha'])) ?></span>
            <span class="price"><?= number_format($r['total_calc'], 2, ',', '.') ?> €</span>
            <span class="chev"><?= rp_icon('chevron') ?></span>
          </summary>
          <div class="content">
            <div class="lines">
              <?php foreach ($r['lineas'] as $l): ?>
                <div class="line">
                  <img src="../assets/img/<?= htmlspecialchars($l['img'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($l['titulo'], ENT_QUOTES) ?>" loading="lazy">
                  <div>
                    <strong><?= htmlspecialchars($l['titulo']) ?></strong>
                    <small><?= (int) $l['cantidad'] ?> ud. · <?= (int) $l['semanas'] ?> <?= $l['semanas'] == 1 ? 'semana' : 'semanas' ?> · <?= number_format($l['num'], 2, ',', '.') ?> €/semana</small>
                  </div>
                  <span class="sub"><?= number_format($l['sub'], 2, ',', '.') ?> €</span>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="actions">
              <span class="muted"><?= $r['activa'] ? 'Devolver antes del ' : 'Terminó el ' ?><strong><?= date('d/m/Y', $r['fin']) ?></strong></span>
              <button class="btn btn-sm repeat" type="button"
                data-items="<?= htmlspecialchars(json_encode(array_map(function ($l) {
                  return ['id' => (string) $l['id'], 'title' => $l['titulo'], 'price' => $l['num'], 'img' => $l['img'], 'cat' => $l['cat'], 'qty' => (int) $l['cantidad'], 'weeks' => (int) $l['semanas']];
                }, $r['lineas'])), ENT_QUOTES) ?>">
                <?= rp_icon('repeat') ?> Repetir reserva
              </button>
            </div>
          </div>
        </details>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<script>
  // "Repetir reserva": vuelve a añadir sus artículos al carrito.
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.repeat');
    if (!btn) return;
    JSON.parse(btn.dataset.items).forEach(function (it) {
      var cur = RP.cart.items().filter(function (x) { return x.id === it.id; })[0];
      if (cur) RP.cart.update(it.id, { qty: cur.qty + it.qty, weeks: it.weeks }); else RP.cart.add(it);
    });
    RP.toast('Artículos añadidos al carrito', 'ok');
    setTimeout(function () { window.location.href = '../carrito/carrito.php'; }, 700);
  });
</script>

<?php rp_footer(); ?>
