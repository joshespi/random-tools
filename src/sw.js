// Network-first for pages (so PHP edits show), cache-first for static assets.
const CACHE = 'random-tools-v1';
// Only the shell; tool pages are cached as they're visited (network-first below).
const PRECACHE = ['/', '/manifest.json', '/favicon.svg'];

self.addEventListener('install', e => {
    e.waitUntil(caches.open(CACHE).then(c => c.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', e => {
    const req = e.request;
    if (req.method !== 'GET') return;
    const url = new URL(req.url);
    const sameOrigin = url.origin === location.origin;
    const isFont = url.hostname === 'fonts.googleapis.com' || url.hostname === 'fonts.gstatic.com';
    if (!sameOrigin && !isFont) return;

    const isStatic = isFont || /\.(css|js|png|svg|woff2?)$/.test(url.pathname);
    if (isStatic && url.pathname !== '/sw.js') {
        e.respondWith(
            caches.match(req).then(hit => hit || fetch(req).then(res => {
                if (res.ok || res.type === 'opaque') {
                    const copy = res.clone();
                    // Replace older ?v= versions of our own files instead of accumulating them.
                    // (Not for fonts: Google's css2 URLs differ only by query.)
                    caches.open(CACHE).then(c =>
                        (sameOrigin ? c.delete(req, { ignoreSearch: true }) : Promise.resolve())
                            .then(() => c.put(req, copy)));
                }
                return res;
            }).catch(() => caches.match(req, { ignoreSearch: true }))) // older ?v= copy beats no styles
        );
        return;
    }

    e.respondWith(
        fetch(req).then(res => {
            if (res.ok) {
                const copy = res.clone();
                caches.open(CACHE).then(c => c.put(req, copy));
            }
            return res;
        }).catch(() => caches.match(req, { ignoreSearch: true })
            .then(hit => hit || (req.mode === 'navigate' ? caches.match('/') : undefined)))
    );
});
