/* Botón de mostrar/ocultar contraseña (cualquier botón con data-toggle-pw="idDelInput") */
document.addEventListener('click', function (e) {
  var btn = e.target.closest('[data-toggle-pw]');
  if (!btn) return;
  var input = document.getElementById(btn.dataset.togglePw);
  var show = input.type === 'password';
  input.type = show ? 'text' : 'password';
  btn.querySelector('.i-show').hidden = show;
  btn.querySelector('.i-hide').hidden = !show;
});
