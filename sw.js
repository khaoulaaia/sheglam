const CACHE_NAME = "sheglamour-v1";
const STATIC_ASSETS = [
  "/",
  "/images/logofib.png",
  "/images/logowhite.png",
  "/index.css",
  "/sidebar.css",
  "/js/shop.js",
  "/js/checkout.js",
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
    )
  );
  self.clients.claim();
});

self.addEventListener("fetch", (event) => {
  if (event.request.method !== "GET") return;

  // Ne jamais intercepter les requêtes PHP dynamiques
  if (event.request.url.includes("/includes/")) return;
  if (event.request.url.match(/\.(php)(\?|$)/)) return;

  // Ne pas mettre en cache les appels API / panier / commandes
  const bypass = ["/cart", "/order", "/checkout", "/admin", "/api/"];
  if (bypass.some((p) => event.request.url.includes(p))) return;

  event.respondWith(
    caches.match(event.request).then(
      (cached) => cached || fetch(event.request).then((response) => {
        // Mettre en cache uniquement les assets statiques valides
        if (
          response.ok &&
          event.request.url.match(/\.(css|js|png|jpg|jpeg|webp|svg|woff2?)(\?|$)/)
        ) {
          const clone = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
        }
        return response;
      })
    )
  );
});

self.addEventListener("push", (event) => {
  let data = { title: "SheGlamour", body: "Vous avez un nouveau message." };
  try { data = event.data.json(); } catch {}

  event.waitUntil(
    self.registration.showNotification(data.title, {
      body:    data.body,
      icon:    data.icon  || "/images/logofib.png",
      badge:              "/images/logofib.png",
      vibrate: [200, 100, 200],
      tag:     data.tag   || "sheglamour",
      actions: [
        { action: "open",    title: "Voir" },
        { action: "dismiss", title: "Ignorer" },
      ],
      data: { url: data.url || "/" },
    })
  );
});

self.addEventListener("notificationclick", (event) => {
  event.notification.close();
  if (event.action === "dismiss") return;

  const url = event.notification.data?.url || "/";

  event.waitUntil(
    clients.matchAll({ type: "window", includeUncontrolled: true }).then((list) => {
      for (const c of list) {
        if (c.url === url && "focus" in c) return c.focus();
      }
      if (clients.openWindow) return clients.openWindow(url);
    })
  );
});