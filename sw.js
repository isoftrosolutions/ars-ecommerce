/**
 * Service Worker for Easy Shopping A.R.S
 * Placeholder to allow PWA installation and basic offline capabilities.
 */

const CACHE_NAME = 'ars-cache-v1';
const urlsToCache = [
  '/',
  '/assets/logo.jpeg'
];

self.addEventListener('install', event => {
  // Perform install steps
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache).catch(err => console.log('Partial cache failure', err));
      })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Cache hit - return response
        if (response) {
          return response;
        }
        return fetch(event.request);
      }
    )
  );
});
