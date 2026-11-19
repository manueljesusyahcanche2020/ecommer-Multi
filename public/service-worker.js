const CACHE_NAME = 'catalogo-virtual-v2'; // 👈 Incrementado a v2 para limpiar el caché anterior

const FILES_TO_CACHE = [
    '/',
    '/login',
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png' // Agregado si ya existe en public/icons/
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return Promise.allSettled(
                FILES_TO_CACHE.map(url =>
                    cache.add(url).catch(err => console.warn(`No se pudo cachear ${url}:`, err))
                )
            );
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.map(key => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then(networkResponse => {
                // Guarda en caché respuestas válidas de tu propio dominio (basic) o recursos externos (cors)
                if (networkResponse && networkResponse.status === 200 && (networkResponse.type === 'basic' || networkResponse.type === 'cors')) {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, responseClone);
                    });
                }
                return networkResponse;
            })
            .catch(() => {
                // Si falla la red (offline), busca en el caché
                return caches.match(event.request);
            })
    );
});