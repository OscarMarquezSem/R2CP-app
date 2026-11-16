/* ARIADNA · interacciones ligeras (todo funciona también sin JavaScript) */
(function () {
  // Botones − / + de los resultados
  document.querySelectorAll('.stepper').forEach(function (st) {
    var input = st.querySelector('input');
    st.querySelectorAll('.st-btn').forEach(function (b) {
      b.addEventListener('click', function () {
        var v = parseInt(input.value || '0', 10) + parseInt(b.dataset.step, 10);
        input.value = Math.max(0, Math.min(999, v));
      });
    });
  });

  // Marcar la tarjeta como hecha al palomear
  document.querySelectorAll('.act-main input[type=checkbox]').forEach(function (c) {
    c.addEventListener('change', function () {
      c.closest('.act').classList.toggle('is-done', c.checked);
    });
  });

  // Copiar al portapapeles
  document.querySelectorAll('[data-copy]').forEach(function (b) {
    b.addEventListener('click', function () {
      var el = document.getElementById(b.dataset.copy);
      var text = el.value !== undefined ? el.value : el.textContent;
      var done = function () { var t = b.textContent; b.textContent = '¡Copiado!'; setTimeout(function () { b.textContent = t; }, 1800); };
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(done);
      } else { el.select && el.select(); document.execCommand('copy'); done(); }
    });
  });

  // Confirmaciones
  document.querySelectorAll('[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) { if (!confirm(f.dataset.confirm)) e.preventDefault(); });
  });

  // Evitar doble envío del formulario del día
  var form = document.getElementById('dayform');
  if (form) form.addEventListener('submit', function () {
    var b = form.querySelector('.savebar button, button.btn-main');
    if (b) { b.disabled = true; b.textContent = 'Guardando…'; }
  });

  // PWA: service worker sencillo
  if ('serviceWorker' in navigator && window.isSecureContext) {
    navigator.serviceWorker.register('sw.js').catch(function () {});
  }
  // Si ya está instalada, ocultar la sugerencia
  if (window.matchMedia('(display-mode: standalone)').matches || navigator.standalone) {
    var h = document.getElementById('a2hs'); if (h) h.style.display = 'none';
  }
})();
