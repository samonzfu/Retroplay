/* RetroPlay · utilidades compartidas: carrito, avisos, tema y animaciones */
(function () {
  'use strict';

  var CART_KEY = 'retroplay_cart_v2';
  var MAX_QTY = 10;
  var MAX_WEEKS = 8;
  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function clamp(n, min, max) { return Math.max(min, Math.min(max, n)); }

  var moneyFmt = new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' });
  function money(n) { return moneyFmt.format(n || 0); }

  function icon(path) {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + path + '</svg>';
  }
  var icons = {
    trash: icon('<path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>'),
    check: icon('<path d="M20 6 9 17l-5-5"/>')
  };

  /* ---------- Avisos (toasts) ---------- */
  function toast(msg, type) {
    var box = document.getElementById('toasts');
    if (!box) {
      box = document.createElement('div');
      box.id = 'toasts';
      box.className = 'toasts';
      box.setAttribute('aria-live', 'polite');
      document.body.appendChild(box);
    }
    var t = document.createElement('div');
    t.className = 'toast' + (type ? ' ' + type : '');
    t.textContent = msg;
    box.appendChild(t);
    setTimeout(function () {
      t.classList.add('out');
      setTimeout(function () { t.remove(); }, 300);
    }, 2600);
  }

  /* ---------- Carrito (localStorage) ---------- */
  function readCart() {
    try {
      var data = JSON.parse(localStorage.getItem(CART_KEY) || '[]');
      return Array.isArray(data) ? data : [];
    } catch (e) { return []; }
  }

  function writeCart(items) {
    try { localStorage.setItem(CART_KEY, JSON.stringify(items)); } catch (e) { /* modo privado */ }
    document.dispatchEvent(new CustomEvent('cart:change', { detail: items }));
  }

  var cart = {
    items: readCart,
    count: function () {
      return readCart().reduce(function (n, it) { return n + it.qty; }, 0);
    },
    lineTotal: function (it) { return it.price * it.qty * it.weeks; },
    total: function () {
      return readCart().reduce(function (n, it) { return n + cart.lineTotal(it); }, 0);
    },
    add: function (product) {
      var items = readCart();
      var found = items.filter(function (it) { return it.id === product.id; })[0];
      if (found) {
        if (found.qty >= MAX_QTY) { toast('Máximo ' + MAX_QTY + ' unidades por producto', 'error'); return false; }
        found.qty++;
      } else {
        items.push({
          id: product.id, title: product.title, price: product.price,
          img: product.img, cat: product.cat, qty: product.qty || 1, weeks: product.weeks || 1
        });
      }
      writeCart(items);
      return true;
    },
    update: function (id, changes) {
      var items = readCart();
      items.forEach(function (it) {
        if (it.id !== id) return;
        if ('qty' in changes) it.qty = clamp(changes.qty, 1, MAX_QTY);
        if ('weeks' in changes) it.weeks = clamp(changes.weeks, 1, MAX_WEEKS);
      });
      writeCart(items);
    },
    remove: function (id) {
      writeCart(readCart().filter(function (it) { return it.id !== id; }));
    },
    clear: function () { writeCart([]); }
  };

  function updateBadge(bump) {
    var badge = document.getElementById('cart-badge');
    if (!badge) return;
    var n = cart.count();
    badge.textContent = n > 99 ? '99+' : n;
    badge.classList.toggle('show', n > 0);
    if (bump) {
      badge.classList.remove('bump');
      void badge.offsetWidth;
      badge.classList.add('bump');
    }
  }

  /* El producto "vuela" desde la tarjeta hasta el icono del carrito */
  function fly(fromEl, src, done) {
    var target = document.getElementById('cart-link');
    if (reducedMotion || !fromEl || !target) { done && done(); return; }
    var a = fromEl.getBoundingClientRect();
    var b = target.getBoundingClientRect();
    var ghost = document.createElement('img');
    ghost.className = 'fly';
    ghost.src = src;
    ghost.alt = '';
    ghost.style.left = (a.left + a.width / 2 - 32) + 'px';
    ghost.style.top = (a.top + a.height / 2 - 32) + 'px';
    document.body.appendChild(ghost);
    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        var dx = (b.left + b.width / 2) - (a.left + a.width / 2);
        var dy = (b.top + b.height / 2) - (a.top + a.height / 2);
        ghost.style.transform = 'translate(' + dx + 'px,' + dy + 'px) scale(.25) rotate(25deg)';
        ghost.style.opacity = '.3';
      });
    });
    setTimeout(function () { ghost.remove(); done && done(); }, 760);
  }

  /* ---------- Tema claro / oscuro ---------- */
  function applyTheme(t) {
    document.documentElement.setAttribute('data-theme', t);
    try { localStorage.setItem('retroplay_theme', t); } catch (e) { /* sin almacenamiento */ }
  }

  function initTheme() {
    var btn = document.getElementById('theme-toggle');
    if (!btn) return;
    btn.addEventListener('click', function () {
      var now = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
      applyTheme(now);
    });
  }

  /* ---------- Aparición al hacer scroll ---------- */
  function reveal(root) {
    var els = (root || document).querySelectorAll('.reveal:not(.in)');
    if (!('IntersectionObserver' in window) || reducedMotion) {
      els.forEach(function (el) { el.classList.add('in'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
      });
    }, { threshold: 0.08 });
    els.forEach(function (el, i) {
      el.style.setProperty('--d', Math.min(i % 12, 8) * 0.04 + 's');
      io.observe(el);
    });
  }

  document.addEventListener('cart:change', function () { updateBadge(true); });
  window.addEventListener('storage', function (e) {
    if (e.key === CART_KEY) {
      updateBadge(false);
      document.dispatchEvent(new CustomEvent('cart:external'));
    }
  });

  document.addEventListener('DOMContentLoaded', function () {
    updateBadge(false);
    initTheme();
    reveal();
  });

  window.RP = {
    cart: cart, toast: toast, money: money, esc: esc, fly: fly, reveal: reveal,
    icons: icons, clamp: clamp, MAX_QTY: MAX_QTY, MAX_WEEKS: MAX_WEEKS
  };
})();
