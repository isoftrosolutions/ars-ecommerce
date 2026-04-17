/**
 * Service Worker for Easy Shopping A.R.S
 * Production-ready PWA Service Worker
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

// CDN assets to cache
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
  console.log('[SW] Installing Service Worker...');
  
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(async cache => {
        console.log('[SW] Caching static assets');
        // Use Promise.allSettled to not fail if some assets don't exist
        const results = await Promise.allSettled(
          STATIC_ASSETS.map(url => 
            cache.add(url).catch(err => {
              console.warn('[SW] Skipping cache for:', url, err);
              return null;
            })
          )
        );
        console.log('[SW] Static caching complete');
      })
      .then(() => {
        console.log('[SW] Skip waiting to activate immediately');
        return self.skipWaiting();
      })
      .catch(err => {
        console.error('[SW] Cache install failed:', err);
        // Don't fail the install, just proceed
        return self.skipWaiting();
      })
  );
});

// Activate Event - Clean up old caches
self.addEventListener('activate', event => {
  console.log('[SW] Activating Service Worker...');
  
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames
            .filter(cacheName => {
              return cacheName.startsWith('ars-') && 
                     cacheName !== STATIC_CACHE && 
                     cacheName !== DYNAMIC_CACHE && 
                     cacheName !== IMAGE_CACHE;
            })
            .map(cacheName => {
              console.log('[SW] Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            })
        );
      })
      .then(() => self.clients.claim())
      .then(() => {
        // Notify all clients about the update
        self.clients.matchAll().then(clients => {
          clients.forEach(client => {
            client.postMessage({
              type: 'SW_ACTIVATED',
              version: CACHE_VERSION
            });
          });
        });
      })
  );
});

// Fetch Event - Handle all requests
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }

  // Skip API and backend requests
  if (shouldSkipCache(url.pathname)) {
    return;
  }

  // Handle different request types
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

// Cache-First Strategy (for images and static assets)
async function cacheFirst(request, cacheName = IMAGE_CACHE) {
  const cachedResponse = await caches.match(request);
  if (cachedResponse) {
    return cachedResponse;
  }

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

// Cache-First for Images with fallback
async function cacheFirstImage(request) {
  try {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      // Refresh cache in background
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
    // Return placeholder for failed images
    return new Response(
      `<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">
        <rect fill="#f1f5f9" width="400" height="400"/>
        <text fill="#94a3b8" font-family="sans-serif" font-size="14" x="50%" y="50%" text-anchor="middle">Image unavailable</text>
      </svg>`,
      { headers: { 'Content-Type': 'image/svg+xml' } }
    );
  }
}

// Network-First Strategy (for HTML pages)
async function networkFirst(request) {
  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    // Try to serve from cache, then offline page
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    
    if (request.mode === 'navigate') {
      const offlinePage = await caches.match(OFFLINE_URL);
      if (offlinePage) {
        return offlinePage;
      }
    }
    
    return new Response('Offline', { status: 503 });
  }
}

// Stale-While-Revalidate Strategy (for API and dynamic content)
async function staleWhileRevalidate(request) {
  const cachedResponse = await caches.match(request);
  
  const fetchPromise = fetchAndCache(request, DYNAMIC_CACHE);
  
  return cachedResponse || fetchPromise;
}

// Network-Only Strategy (for API endpoints)
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

// Helper Functions
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
  return request.mode === 'navigate' || 
         (request.destination === 'document');
}

function isAPIRequest(pathname) {
  return pathname.includes('/api/') || 
         pathname.includes('cart-action') ||
         pathname.includes('wishlist');
}

// Background Sync for Cart Operations
self.addEventListener('sync', event => {
  console.log('[SW] Background Sync:', event.tag);
  
  if (event.tag === 'sync-cart') {
    event.waitUntil(syncCart());
  }
});

async function syncCart() {
  try {
    const clients = await self.clients.matchAll();
    clients.forEach(client => {
      client.postMessage({
        type: 'CART_SYNC',
        success: true
      });
    });
  } catch (error) {
    console.error('[SW] Cart sync failed:', error);
  }
}

// Push Notification Handler (placeholder for future)
self.addEventListener('push', event => {
  if (!event.data) return;

  const data = event.data.json();
  const options = {
    body: data.body || 'New notification from ARS Shop',
    icon: '/ars/public/assets/img/pwa-icon-192.png',
    badge: '/ars/public/assets/img/pwa-icon-192.png',
    vibrate: [100, 50, 100],
    data: {
      url: data.url || '/ars/'
    },
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
        // Focus existing window if available
        for (const client of clientList) {
          if (client.url === url && 'focus' in client) {
            return client.focus();
          }
        }
        // Open new window
        if (self.clients.openWindow) {
          return self.clients.openWindow(url);
        }
      })
  );
});

// Message Handler for communication with main app
self.addEventListener('message', event => {
  console.log('[SW] Message received:', event.data);

  switch (event.data.type) {
    case 'SKIP_WAITING':
      self.skipWaiting();
      break;
      
    case 'GET_VERSION':
      event.ports[0].postMessage({ version: CACHE_VERSION });
      break;
      
    case 'CLEAR_CACHE':
      event.waitUntil(
        caches.keys().then(names => {
          return Promise.all(names.map(name => caches.delete(name)));
        })
      );
      break;
      
    case 'CACHE_URLS':
      event.waitUntil(
        caches.open(DYNAMIC_CACHE).then(cache => {
          return cache.addAll(event.data.urls);
        })
      );
      break;
  }
});

// Periodic Background Sync (if supported)
self.addEventListener('periodicsync', event => {
  if (event.tag === 'update-content') {
    event.waitUntil(updateContent());
  }
});

async function updateContent() {
  try {
    // Pre-cache important pages
    const pagesToCache = [
      '/ars/index.php',
      '/ars/shop.php'
    ];
    
    const cache = await caches.open(DYNAMIC_CACHE);
    await Promise.all(
      pagesToCache.map(url => 
        fetch(url).then(response => {
          if (response.ok) cache.put(url, response);
        }).catch(() => {})
      )
    );
  } catch (error) {
    console.error('[SW] Periodic sync failed:', error);
  }
}

console.log('[SW] Service Worker loaded - Version', CACHE_VERSION);
