/* Catálogo: búsqueda, filtros, orden, favoritos, detalle y añadir al carrito */
(function () {
  'use strict';

  var FAV_KEY = 'retroplay_favs';
  var grid = document.getElementById('grid');
  var cards = Array.prototype.slice.call(grid.querySelectorAll('.card'));
  var search = document.getElementById('q');
  var sortSel = document.getElementById('sort');
  var chips = document.querySelectorAll('.chip');
  var empty = document.getElementById('empty');
  var counter = document.getElementById('result-count');
  var dialog = document.getElementById('product-dialog');

  var state = { q: '', filter: 'todo', sort: 'default' };
  var favs = [];
  try { favs = JSON.parse(localStorage.getItem(FAV_KEY) || '[]'); } catch (e) { favs = []; }

  function saveFavs() {
    try { localStorage.setItem(FAV_KEY, JSON.stringify(favs)); } catch (e) { /* sin almacenamiento */ }
  }

  function productOf(card) {
    return {
      id: card.dataset.id,
      title: card.dataset.title,
      price: parseFloat(card.dataset.price) || 0,
      img: card.dataset.img,
      cat: card.dataset.cat,
      desc: card.dataset.desc
    };
  }

  /* ---------- Filtrado y orden ---------- */
  function apply() {
    var q = state.q.trim().toLowerCase();
    var shown = 0;

    cards.forEach(function (card) {
      var p = productOf(card);
      var okText = !q || (p.title + ' ' + p.desc).toLowerCase().indexOf(q) !== -1;
      var okCat = state.filter === 'todo' ||
        (state.filter === 'fav' ? favs.indexOf(p.id) !== -1 : p.cat === state.filter);
      var visible = okText && okCat;
      card.hidden = !visible;
      if (visible) shown++;
    });

    var sorted = cards.slice().sort(function (a, b) {
      var pa = productOf(a), pb = productOf(b);
      if (state.sort === 'price-asc') return pa.price - pb.price || a.dataset.order - b.dataset.order;
      if (state.sort === 'price-desc') return pb.price - pa.price || a.dataset.order - b.dataset.order;
      if (state.sort === 'name') return pa.title.localeCompare(pb.title, 'es');
      return a.dataset.order - b.dataset.order;
    });
    sorted.forEach(function (c) { grid.appendChild(c); });

    empty.hidden = shown !== 0;
    grid.hidden = shown === 0;
    counter.textContent = shown + (shown === 1 ? ' producto' : ' productos');
    RP.reveal(grid);
  }

  search.addEventListener('input', function () { state.q = search.value; apply(); });
  sortSel.addEventListener('change', function () { state.sort = sortSel.value; apply(); });
  chips.forEach(function (chip) {
    chip.addEventListener('click', function () {
      chips.forEach(function (c) { c.classList.remove('active'); });
      chip.classList.add('active');
      state.filter = chip.dataset.filter;
      apply();
    });
  });
  document.getElementById('reset-filters').addEventListener('click', function () {
    state = { q: '', filter: 'todo', sort: 'default' };
    search.value = '';
    sortSel.value = 'default';
    chips.forEach(function (c) { c.classList.toggle('active', c.dataset.filter === 'todo'); });
    apply();
  });

  /* ---------- Favoritos ---------- */
  function paintFav(card) {
    var on = favs.indexOf(card.dataset.id) !== -1;
    var btn = card.querySelector('.fav');
    btn.classList.toggle('on', on);
    btn.setAttribute('aria-pressed', on);
  }
  cards.forEach(paintFav);

  /* ---------- Añadir al carrito ---------- */
  function addToCart(card, fromEl, button) {
    var p = productOf(card);
    var ok = RP.cart.add({ id: p.id, title: p.title, price: p.price, img: p.img, cat: p.cat });
    if (!ok) return;
    RP.fly(fromEl, '../assets/img/' + p.img);
    RP.toast(p.title + ' añadido al carrito', 'ok');
    if (button) {
      var html = button.innerHTML;
      button.disabled = true;
      button.innerHTML = RP.icons.check + ' Añadido';
      setTimeout(function () { button.innerHTML = html; button.disabled = false; }, 1100);
    }
  }

  /* ---------- Detalle (modal) ---------- */
  var current = null;
  function openDetails(card) {
    var p = productOf(card);
    current = card;
    document.getElementById('pd-img').src = '../assets/img/' + p.img;
    document.getElementById('pd-img').alt = p.title;
    document.getElementById('pd-tag').textContent = p.cat === 'consola' ? 'CONSOLA' : 'JUEGO';
    document.getElementById('pd-title').textContent = p.title;
    document.getElementById('pd-desc').textContent = p.desc;
    document.getElementById('pd-price').innerHTML = RP.esc(RP.money(p.price)) + ' <small>/ semana</small>';
    if (dialog.showModal) dialog.showModal(); else dialog.setAttribute('open', '');
  }
  function closeDetails() { if (dialog.close) dialog.close(); else dialog.removeAttribute('open'); }

  dialog.addEventListener('click', function (e) {
    // clic en el fondo o en un botón de cerrar
    if (e.target === dialog || e.target.closest('[data-close]')) closeDetails();
  });
  document.getElementById('pd-add').addEventListener('click', function () {
    if (!current) return;
    addToCart(current, document.getElementById('pd-img'), null);
    closeDetails();
  });

  /* ---------- Eventos de las tarjetas ---------- */
  grid.addEventListener('click', function (e) {
    var card = e.target.closest('.card');
    if (!card) return;

    var fav = e.target.closest('.fav');
    if (fav) {
      var id = card.dataset.id;
      var i = favs.indexOf(id);
      if (i === -1) { favs.push(id); RP.toast('Añadido a favoritos'); } else { favs.splice(i, 1); }
      saveFavs();
      paintFav(card);
      if (state.filter === 'fav') apply();
      return;
    }

    var add = e.target.closest('[data-action="add"]');
    if (add) { addToCart(card, card.querySelector('.thumb img'), add); return; }

    if (e.target.closest('[data-action="details"]')) openDetails(card);
  });

  apply();
})();
