/* Carrito: cantidades, semanas de alquiler, resumen y reserva */
(function () {
  'use strict';

  var list = document.getElementById('cart-list');
  var layout = document.getElementById('cart-layout');
  var emptyBox = document.getElementById('cart-empty');
  var sub = document.getElementById('cart-sub');
  var dialog = document.getElementById('confirm-dialog');
  var checkoutBtn = document.getElementById('checkout');

  function weeksOptions(selected) {
    var html = '';
    for (var w = 1; w <= RP.MAX_WEEKS; w++) {
      html += '<option value="' + w + '"' + (w === selected ? ' selected' : '') + '>' + w + (w === 1 ? ' semana' : ' semanas') + '</option>';
    }
    return html;
  }

  function itemHtml(it) {
    var id = RP.esc(it.id);
    return '<article class="cart-item" data-id="' + id + '">' +
      '<img src="../assets/img/' + RP.esc(it.img) + '" alt="' + RP.esc(it.title) + '">' +
      '<div>' +
        '<h3>' + RP.esc(it.title) + '</h3>' +
        '<span class="price">' + RP.money(it.price) + ' <small>/ semana</small></span>' +
        '<div class="controls">' +
          '<label>Cantidad' +
            '<span class="stepper">' +
              '<button type="button" data-act="dec" aria-label="Quitar una unidad">−</button>' +
              '<output aria-live="polite">' + it.qty + '</output>' +
              '<button type="button" data-act="inc" aria-label="Añadir una unidad">+</button>' +
            '</span>' +
          '</label>' +
          '<label>Duración' +
            '<select data-act="weeks" aria-label="Semanas de alquiler">' + weeksOptions(it.weeks) + '</select>' +
          '</label>' +
        '</div>' +
      '</div>' +
      '<div class="line-total">' +
        '<span class="price" data-line>' + RP.money(RP.cart.lineTotal(it)) + '</span>' +
        '<button class="btn btn-ghost btn-sm btn-danger" type="button" data-act="remove">' + RP.icons.trash + ' Quitar</button>' +
      '</div>' +
    '</article>';
  }

  // Resumen lateral (se actualiza sin repintar las líneas, para no cortar animaciones).
  function renderSummary(items) {
    var units = 0, consolas = 0, juegos = 0;
    items.forEach(function (it) {
      units += it.qty;
      if (it.cat === 'consola') consolas += RP.cart.lineTotal(it); else juegos += RP.cart.lineTotal(it);
    });
    document.getElementById('s-items').textContent = units;
    document.getElementById('s-consolas').textContent = RP.money(consolas);
    document.getElementById('s-juegos').textContent = RP.money(juegos);
    document.getElementById('total').textContent = RP.money(consolas + juegos);
    sub.textContent = units + (units === 1 ? ' artículo' : ' artículos');
  }

  function render() {
    var items = RP.cart.items();
    var has = items.length > 0;
    layout.hidden = !has;
    emptyBox.hidden = has;
    if (!has) { sub.textContent = ''; list.innerHTML = ''; return; }

    list.innerHTML = items.map(itemHtml).join('');
    // las líneas aparecen escalonadas
    Array.prototype.forEach.call(list.children, function (el, i) {
      el.classList.add('reveal');
      el.style.setProperty('--d', i * 0.05 + 's');
    });
    RP.reveal(list);
    renderSummary(items);
  }

  // Actualiza solo los números de una línea (cantidad / subtotal) y el resumen.
  function refreshLine(id) {
    var items = RP.cart.items();
    var it = items.filter(function (x) { return x.id === id; })[0];
    var row = list.querySelector('[data-id="' + id + '"]');
    if (it && row) {
      row.querySelector('output').textContent = it.qty;
      row.querySelector('[data-line]').textContent = RP.money(RP.cart.lineTotal(it));
    }
    renderSummary(items);
  }

  list.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-act]');
    if (!btn || btn.tagName === 'SELECT') return;
    var row = btn.closest('.cart-item');
    var id = row.dataset.id;
    var it = RP.cart.items().filter(function (x) { return x.id === id; })[0];
    if (!it) return;

    if (btn.dataset.act === 'inc') {
      if (it.qty >= RP.MAX_QTY) { RP.toast('Máximo ' + RP.MAX_QTY + ' unidades', 'error'); return; }
      RP.cart.update(id, { qty: it.qty + 1 });
    } else if (btn.dataset.act === 'dec') {
      if (it.qty <= 1) { removeRow(row, id); return; }
      RP.cart.update(id, { qty: it.qty - 1 });
    } else if (btn.dataset.act === 'remove') {
      removeRow(row, id);
      return;
    }
    refreshLine(id);
  });

  list.addEventListener('change', function (e) {
    if (e.target.dataset.act !== 'weeks') return;
    var id = e.target.closest('.cart-item').dataset.id;
    RP.cart.update(id, { weeks: parseInt(e.target.value, 10) });
    refreshLine(id);
  });

  function removeRow(row, id) {
    var title = row.querySelector('h3').textContent;
    row.classList.add('removing');
    setTimeout(function () {
      RP.cart.remove(id);
      render();
      RP.toast(title + ' eliminado del carrito');
    }, 280);
  }

  document.getElementById('clear-cart').addEventListener('click', function () {
    Array.prototype.forEach.call(list.children, function (el) { el.classList.add('removing'); });
    setTimeout(function () { RP.cart.clear(); render(); RP.toast('Carrito vaciado'); }, 280);
  });

  /* ---------- Reserva ---------- */
  var errorBox = document.getElementById('cd-error');
  var okBtn = document.getElementById('cd-ok');

  function closeDialog() { if (dialog.close) dialog.close(); else dialog.removeAttribute('open'); }

  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', function () {
      var items = RP.cart.items();
      if (!items.length) return;
      var units = items.reduce(function (n, it) { return n + it.qty; }, 0);
      document.getElementById('cd-text').textContent =
        'Vas a reservar ' + units + (units === 1 ? ' artículo' : ' artículos') + ' por un total de ' + RP.money(RP.cart.total()) + '.';
      errorBox.classList.remove('show');
      okBtn.classList.remove('loading');
      if (dialog.showModal) dialog.showModal(); else dialog.setAttribute('open', '');
    });
  }

  document.getElementById('cd-cancel').addEventListener('click', closeDialog);
  dialog.addEventListener('click', function (e) { if (e.target === dialog) closeDialog(); });

  okBtn.addEventListener('click', function () {
    var items = RP.cart.items().map(function (it) { return { id: it.id, qty: it.qty, weeks: it.weeks }; });
    var fd = new FormData();
    fd.append('accion', 'create_reserva');
    fd.append('reservas', JSON.stringify(items));
    okBtn.classList.add('loading');
    errorBox.classList.remove('show');

    fetch('../../back/Procesar/procesar.php', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data || !data.success) { throw new Error(data && data.error ? data.error : 'Error desconocido'); }
        RP.cart.clear();
        document.getElementById('cd-body').innerHTML =
          '<div class="success-check">' + '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>' + '</div>' +
          '<h2 class="center">¡Reserva #' + data.reserva_id + ' confirmada!</h2>' +
          '<p class="muted center" style="margin:0">Te llevamos a tus reservas...</p>';
        setTimeout(function () { window.location.href = '../mis_reservas/reservas.php?reserved=1'; }, 1700);
      })
      .catch(function (err) {
        okBtn.classList.remove('loading');
        errorBox.textContent = err.message === 'Failed to fetch' ? 'Error de red. Inténtalo de nuevo.' : err.message;
        errorBox.classList.remove('show');
        void errorBox.offsetWidth;
        errorBox.classList.add('show');
      });
  });

  // Cambios hechos desde otra pestaña
  document.addEventListener('cart:external', render);

  render();
})();
