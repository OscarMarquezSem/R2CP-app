/* ARIADNA · interacciones ligeras (todo funciona también sin JavaScript) */
(function () {
  // Botones − / +
  document.querySelectorAll('.stepper').forEach(function (st) {
    var input = st.querySelector('input');
    st.querySelectorAll('.st-btn').forEach(function (b) {
      b.addEventListener('click', function () {
        var v = parseInt(input.value || '0', 10) + parseInt(b.dataset.step, 10);
        input.value = Math.max(0, Math.min(999, v));
        input.dispatchEvent(new Event('input', { bubbles: true }));
      });
    });
  });

  // Marcar la tarjeta como hecha al palomear
  document.querySelectorAll('.act-main input[type=checkbox]').forEach(function (c) {
    c.addEventListener('change', function () { c.closest('.act').classList.toggle('is-done', c.checked); });
  });

  // «Cuéntame qué propiedades analizaste» aparece cuando hay propiedades analizadas
  var an = document.querySelector('input[data-field=analizadas]');
  var box = document.getElementById('analisisBox');
  if (an && box) {
    var showAn = function () { box.classList.toggle('show', parseInt(an.value || '0', 10) > 0); };
    an.addEventListener('input', showAn); showAn();
  } else if (box) { box.classList.add('show'); }

  // Aviso amable de metas bajas
  document.querySelectorAll('[data-goals]').forEach(function (g) {
    var warn = g.querySelector('.goal-warn');
    var check = function () {
      var low = false;
      g.querySelectorAll('input[data-min]').forEach(function (i) {
        var isLow = parseInt(i.value || '0', 10) < parseInt(i.dataset.min, 10);
        i.closest('.goal-row').classList.toggle('is-low', isLow);
        if (isLow) low = true;
      });
      warn.hidden = !low;
    };
    g.addEventListener('input', check); check();
  });

  // Copiar al portapapeles
  document.querySelectorAll('[data-copy]').forEach(function (b) {
    b.addEventListener('click', function () {
      var el = document.getElementById(b.dataset.copy);
      var text = el.value !== undefined ? el.value : el.textContent;
      var done = function () { var t = b.textContent; b.textContent = '¡Copiado!'; setTimeout(function () { b.textContent = t; }, 1800); };
      if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(text).then(done);
      else { el.select && el.select(); document.execCommand('copy'); done(); }
    });
  });

  // Confirmaciones
  document.querySelectorAll('[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) { if (!confirm(f.dataset.confirm)) e.preventDefault(); });
  });

  // Evitar doble envío; en vista previa no se envía nada
  var form = document.getElementById('dayform');
  if (form) form.addEventListener('submit', function (e) {
    if (form.dataset.preview) { e.preventDefault(); return; }
    var b = form.querySelector('.savebar button, button.btn-main');
    if (b) { b.disabled = true; b.textContent = 'Guardando…'; }
  });

  // PWA y notificaciones
  var standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
  if (standalone) { var h = document.getElementById('a2hs'); if (h) h.style.display = 'none'; }
  if ('serviceWorker' in navigator && window.isSecureContext) {
    navigator.serviceWorker.register('sw.js').then(function (reg) {
      var card = document.getElementById('pushCard');
      var btn = document.getElementById('pushBtn');
      if (!card || !btn || !btn.dataset.key || !('PushManager' in window) || !('Notification' in window)) return;
      reg.pushManager.getSubscription().then(function (sub) {
        if (sub) { save(sub); return; }              // ya activadas: refrescamos el registro
        if (Notification.permission === 'denied') return;
        card.hidden = false;
      });
      function keyBytes(k) {
        var p = '='.repeat((4 - k.length % 4) % 4), s = atob((k + p).replace(/-/g, '+').replace(/_/g, '/'));
        var a = new Uint8Array(s.length); for (var i = 0; i < s.length; i++) a[i] = s.charCodeAt(i); return a;
      }
      function save(sub) {
        return fetch('push_subscribe.php', { method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ t: btn.dataset.t, subscription: sub.toJSON() }) });
      }
      btn.addEventListener('click', function () {
        btn.disabled = true;
        Notification.requestPermission().then(function (perm) {
          if (perm !== 'granted') { btn.textContent = 'No se activaron. Te aviso por correo.'; return; }
          return reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: keyBytes(btn.dataset.key) })
            .then(save).then(function () { card.innerHTML = '<p><b>¡Listo!</b> Te aviso por aquí. No sueltes el hilo.</p>'; });
        }).catch(function () { btn.disabled = false; btn.textContent = 'Inténtalo de nuevo'; });
      });
    }).catch(function () {});
  }
})();
