/* ARIADNA · Service worker mínimo: guarda estilos e íconos; las páginas siempre van a la red. */
const CACHE = 'ariadna-v1';
const ASSETS = ['assets/style.css', 'assets/app.js', 'assets/icon-192.png', 'assets/icon-512.png'];
self.addEventListener('install', e => e.waitUntil(caches.open(CACHE).then(c => c.addAll(ASSETS)).then(() => self.skipWaiting())));
self.addEventListener('activate', e => e.waitUntil(
  caches.keys().then(keys => Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))).then(() => self.clients.claim())
));
self.addEventListener('fetch', e => {
  const url = new URL(e.request.url);
  if (e.request.method !== 'GET' || url.origin !== location.origin) return;
  if (url.pathname.includes('/assets/')) {
    e.respondWith(caches.match(e.request, { ignoreSearch: true }).then(r => r || fetch(e.request)));
  }
});
