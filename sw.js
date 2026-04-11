/**
 * Service Worker for Easy Shopping A.R.S
 * Placeholder to allow PWA installation and basic offline capabilities.
 */

const CACHE_NAME = 'ars-cache-v2';
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

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);

  // Skip caching for admin, auth, backend, and API routes
  if (url.pathname.includes('/admin/') ||
      url.pathname.includes('/auth/') ||
      url.pathname.includes('/backend/') ||
      url.pathname.includes('/api/') ||
      event.request.method !== 'GET') {
    return; // Let these requests go directly to network
  }

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
