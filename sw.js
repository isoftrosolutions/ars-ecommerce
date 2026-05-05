/**
 * Service Worker for Easy Shopping A.R.S
 * Version: 2.0.0
 */

const CACHE_VERSION = 'v2.0.0';
const STATIC_CACHE = `ars-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `ars-dynamic-${CACHE_VERSION}`;
const IMAGE_CACHE = `ars-images-${CACHE_VERSION}`;
const OFFLINE_URL = '/ars/offline.php';

// Static assets to cache on install
const STATIC_ASSETS = [
  '/ars/',
  '/ars/index.php',
  '/ars/manifest.json',
  '/ars/offline.php',
  '/ars/shop.php',
  '/ars/public/assets/img/pwa-icon-192.png',
  '/ars/public/assets/img/pwa-icon-512.png',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css'
];

const CDN_ASSETS = [
  'https://cdn.jsdelivr.net/',
  'https://fonts.googleapis.com/'
];

// API endpoints to bypass cache
const SKIP_CACHE_PATTERNS = [
  '/ars/admin/',
  '/ars/auth/',
  '/ars/backend/',
  '/ars/api/',
  '/ars/cart-action',
  '/ars/checkout.php',
  '/ars/wishlist'
];

// Install Event - Cache static assets
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(async cache => {
        await Promise.allSettled(
          STATIC_ASSETS.map(url => cache.add(url).catch(() => null))
        );
      })
      .then(() => self.skipWaiting())
      .catch(() => self.skipWaiting())
  );
});

// Activate Event - Clean up old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames
            .filter(cacheName =>
              cacheName.startsWith('ars-') &&
              cacheName !== STATIC_CACHE &&
              cacheName !== DYNAMIC_CACHE &&
              cacheName !== IMAGE_CACHE
            )
            .map(cacheName => caches.delete(cacheName))
        );
      })
      .then(() => self.clients.claim())
      .then(() => {
        self.clients.matchAll().then(clients => {
          clients.forEach(client => {
            client.postMessage({ type: 'SW_ACTIVATED', version: CACHE_VERSION });
          });
        });
      })
  );
});

// Fetch Event - Handle all requests
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  if (request.method !== 'GET') return;
  if (!url.protocol.startsWith('http')) return;
  if (shouldSkipCache(url.pathname)) return;

  if (isImageRequest(request)) {
    event.respondWith(cacheFirstImage(request));
  } else if (isStaticAsset(url.pathname)) {
    event.respondWith(cacheFirst(request, STATIC_CACHE));
  } else if (isNavigationRequest(request)) {
    event.respondWith(networkFirst(request));
  } else if (isAPIRequest(url.pathname)) {
    event.respondWith(networkOnly(request));
  } else {
    event.respondWith(staleWhileRevalidate(request));
  }
});

// Cache-First Strategy
async function cacheFirst(request, cacheName = IMAGE_CACHE) {
  const cachedResponse = await caches.match(request);
  if (cachedResponse) return cachedResponse;

  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      const cache = await caches.open(cacheName);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    return caches.match('/ars/public/assets/img/placeholder.png');
  }
}

// Cache-First for Images with background refresh
async function cacheFirstImage(request) {
  try {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      fetchAndCache(request, IMAGE_CACHE);
      return cachedResponse;
    }

    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      const cache = await caches.open(IMAGE_CACHE);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    return new Response(
      `<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">
        <rect fill="#f1f5f9" width="400" height="400"/>
        <text fill="#94a3b8" font-family="sans-serif" font-size="14" x="50%" y="50%" text-anchor="middle">Image unavailable</text>
      </svg>`,
      { headers: { 'Content-Type': 'image/svg+xml' } }
    );
  }
}

// Network-First Strategy (HTML pages)
async function networkFirst(request) {
  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) return cachedResponse;

    if (request.mode === 'navigate') {
      const offlinePage = await caches.match(OFFLINE_URL);
      if (offlinePage) return offlinePage;
    }

    return new Response('Offline', { status: 503 });
  }
}

// Stale-While-Revalidate Strategy
async function staleWhileRevalidate(request) {
  const cachedResponse = await caches.match(request);
  const fetchPromise = fetchAndCache(request, DYNAMIC_CACHE);
  return cachedResponse || fetchPromise;
}

// Network-Only Strategy
async function networkOnly(request) {
  return fetch(request);
}

// Fetch and cache helper
async function fetchAndCache(request, cacheName) {
  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      const cache = await caches.open(cacheName);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    throw error;
  }
}

function shouldSkipCache(pathname) {
  return SKIP_CACHE_PATTERNS.some(pattern => pathname.includes(pattern));
}

function isImageRequest(request) {
  return request.destination === 'image' ||
         /\.(jpg|jpeg|png|gif|webp|svg|ico)$/i.test(request.url);
}

function isStaticAsset(pathname) {
  return /\.(js|css|woff2?|ttf|eot)$/i.test(pathname) ||
         pathname.includes('/public/assets/');
}

function isNavigationRequest(request) {
  return request.mode === 'navigate' || request.destination === 'document';
}

function isAPIRequest(pathname) {
  return pathname.includes('/api/') ||
         pathname.includes('cart-action') ||
         pathname.includes('wishlist');
}

// Background Sync for Cart Operations
self.addEventListener('sync', event => {
  if (event.tag === 'sync-cart') {
    event.waitUntil(syncCart());
  }
});

async function syncCart() {
  try {
    const clients = await self.clients.matchAll();
    clients.forEach(client => {
      client.postMessage({ type: 'CART_SYNC', success: true });
    });
  } catch (error) {
    // sync failed — browser will retry
  }
}

// Push Notification Handler
self.addEventListener('push', event => {
  if (!event.data) return;

  const data = event.data.json();
  const options = {
    body: data.body || 'New notification from ARS Shop',
    icon: '/ars/public/assets/img/pwa-icon-192.png',
    badge: '/ars/public/assets/img/pwa-icon-192.png',
    vibrate: [100, 50, 100],
    data: { url: data.url || '/ars/' },
    actions: [
      { action: 'open', title: 'View' },
      { action: 'close', title: 'Dismiss' }
    ]
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'ARS Shop', options)
  );
});

// Notification Click Handler
self.addEventListener('notificationclick', event => {
  event.notification.close();
  if (event.action === 'close') return;

  const url = event.notification.data?.url || '/ars/';

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true })
      .then(clientList => {
        for (const client of clientList) {
          if (client.url === url && 'focus' in client) return client.focus();
        }
        if (self.clients.openWindow) return self.clients.openWindow(url);
      })
  );
});

// Message Handler
self.addEventListener('message', event => {
  switch (event.data.type) {
    case 'SKIP_WAITING':
      self.skipWaiting();
      break;
    case 'GET_VERSION':
      event.ports[0].postMessage({ version: CACHE_VERSION });
      break;
    case 'CLEAR_CACHE':
      event.waitUntil(
        caches.keys().then(names => Promise.all(names.map(n => caches.delete(n))))
      );
      break;
    case 'CACHE_URLS':
      event.waitUntil(
        caches.open(DYNAMIC_CACHE).then(cache => cache.addAll(event.data.urls))
      );
      break;
  }
});

// Periodic Background Sync
self.addEventListener('periodicsync', event => {
  if (event.tag === 'update-content') {
    event.waitUntil(updateContent());
  }
});

async function updateContent() {
  try {
    const pagesToCache = ['/ars/index.php', '/ars/shop.php'];
    const cache = await caches.open(DYNAMIC_CACHE);
    await Promise.all(
      pagesToCache.map(url =>
        fetch(url).then(response => { if (response.ok) cache.put(url, response); }).catch(() => {})
      )
    );
  } catch (error) {
    // periodic sync failed — will retry next interval
  }
}
