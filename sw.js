/**
 * Service Worker for Easy Shopping A.R.S
 * Placeholder to allow PWA installation and basic offline capabilities.
 */

const CACHE_NAME = 'ars-cache-v3';
const OFFLINE_URL = './offline.php';

const urlsToCache = [
  './index.php',
  './manifest.json',
  './offline.php',
  './public/assets/img/logo.jpg',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return Promise.allSettled(
          urlsToCache.map(url => cache.add(url).catch(err => console.warn(`[PWA] Failed to cache: ${url}`, err)))
        );
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
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(event.request).catch(() => {
          // If the fetch fails (offline) and it's a navigation request, show offline page
          if (event.request.mode === 'navigate') {
            return caches.match(OFFLINE_URL);
          }
        });
      })
  );
});

