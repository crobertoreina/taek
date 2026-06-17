const CACHE_NAME = 'taek-poomsae-v4';
const urlsToCache = [
  'lib/jquery-1.11.1.min.js',
  'lib/jquery.mobile-1.4.5.min.js',
  'lib/jquery.mobile.structure-1.4.5.min.css',
  'themes/takwondoTheme.min.css',
  'themes/jquery.mobile.icons.min.css',
  'images/sonbae.jpg'
];

self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.filter(name => name !== CACHE_NAME)
          .map(name => caches.delete(name))
      );
    })
  );
});

self.addEventListener('fetch', event => {
  if (event.request.url.includes('.php')) {
    event.respondWith(fetch(event.request));
    return;
  }
  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request))
  );
});
