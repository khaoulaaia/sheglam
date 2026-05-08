<?php
include_once 'includes/db.php';
include_once 'includes/config.php';
$b = BASE_URL;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ma Wishlist — SheGlamour</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= $b ?>/sidebar.css?v=<?= time() ?>">

  <style>
   /* ══════════════════════════════════════════════════════════
   WISHLIST — Modern Old Money · Palette Beige & Marron
   ══════════════════════════════════════════════════════════ */

:root {
  --beige:      #d9d0b4;
  --beige-l:    #FAF6F0;
  --beige-d:    #D6C4A8;
  --beige-mid:  #EDE5D8;
  --marron:     #2E1A0A;
  --marron-mid: #5C3A1E;
  --gold:       #B8924A;
  --gold-l:     rgba(184, 146, 74, 0.15);
  --border:     #D6C4A8;
  --border-s:   rgba(214, 196, 168, 0.5);
  --text:       #2E1A0A;
  --muted:      #9a8a76;
  --dark:       #2E1A0A;
  --dark-80:    rgba(46, 26, 10, .80);
  --dark-60:    rgba(46, 26, 10, .60);
  --dark-40:    rgba(46, 26, 10, .40);
  --dark-20:    rgba(46, 26, 10, .20);
  --dark-12:    rgba(46, 26, 10, .12);
  --dark-06:    rgba(46, 26, 10, .06);
  --glass:      rgba(250, 246, 240, 0.72);
  --glass-s:    rgba(250, 246, 240, 0.88);
  --white:      #ffffff;
  --serif:      'Cormorant Garamond', Georgia, serif;
  --sans:       'Jost', system-ui, sans-serif;
  --ease:       cubic-bezier(0.25, 0.46, 0.45, 0.94);
  --ease-back:  cubic-bezier(0.34, 1.56, 0.64, 1);
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: var(--sans);
  background-color: var(--beige-l);
  background-image: url('/images/0059376_betina-white-damask-wallpaper.jpeg');
  background-repeat: repeat;
  background-size: 420px auto;
  background-attachment: fixed;
  color: var(--text);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  visibility: hidden;
  -webkit-font-smoothing: antialiased;
}

/* ── Hero ── */
.page-hero {
  margin-top: 90px;
  background: var(--glass-s);
  backdrop-filter: blur(16px) saturate(160%);
  -webkit-backdrop-filter: blur(16px) saturate(160%);
  border-bottom: 1px solid var(--border-s);
  padding: 56px 6% 46px;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.page-hero::before {
  content: "✦  ✦  ✦";
  position: absolute; top: 22px; left: 6%;
  font-size: 9px; color: var(--gold);
  letter-spacing: 8px; opacity: .4;
}
.page-hero::after {
  content: "✦  ✦  ✦";
  position: absolute; top: 22px; right: 6%;
  font-size: 9px; color: var(--gold);
  letter-spacing: 8px; opacity: .4;
}

.page-hero h1 {
  font-family: var(--serif);
  font-size: clamp(32px, 5vw, 50px);
  font-weight: 300;
  font-style: italic;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  line-height: 1.1;
  color: var(--dark);
}

.page-hero h1 em { font-style: normal; color: var(--gold); }

.page-hero .count {
  display: inline-block;
  margin-top: 14px;
  font-size: 9px;
  letter-spacing: 0.32em;
  text-transform: uppercase;
  color: var(--muted);
}

/* ── Body ── */
.page-body {
  flex: 1;
  max-width: 980px;
  width: 100%;
  margin: 52px auto 100px;
  padding: 0 28px;
}

/* ── État vide ── */
.empty-state { text-align: center; padding: 100px 20px; }

.empty-state .empty-icon {
  font-size: 48px;
  color: var(--beige-d);
  margin-bottom: 24px;
  display: block;
  animation: pulse 2.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 1; }
  50%       { transform: scale(1.08); opacity: .7; }
}

.empty-state p {
  font-family: var(--serif);
  font-size: 22px;
  color: var(--muted);
  font-style: italic;
  margin-bottom: 32px;
}

.empty-state a {
  display: inline-block;
  padding: 13px 38px;
  background: transparent;
  color: var(--marron);
  border: 1px solid var(--marron);
  font-family: var(--sans);
  font-size: 9px;
  letter-spacing: 0.28em;
  text-transform: uppercase;
  text-decoration: none;
  border-radius: 100px;
  transition: background .3s var(--ease), color .3s var(--ease), box-shadow .3s;
}

.empty-state a:hover {
  background: var(--marron);
  color: var(--beige-l);
  box-shadow: 0 8px 28px var(--dark-20);
}

/* ── Grille ── */
.wishlist-grid { display: flex; flex-direction: column; }

/* ── Carte article ── */
.wishlist-item {
  display: grid;
  grid-template-columns: 120px 1fr auto;
  align-items: center;
  gap: 28px;
  padding: 28px 0;
  border-bottom: 1px solid var(--border-s);
  transition: background .25s, padding .25s, margin .25s;
}

.wishlist-item:first-child { border-top: 1px solid var(--border-s); }

.wishlist-item:hover {
  background: var(--glass);
  backdrop-filter: blur(8px);
  margin: 0 -18px;
  padding: 28px 18px;
}

.wishlist-item-img {
  width: 120px;
  height: 120px;
  object-fit: contain;
  background: var(--glass);
  border: 1px solid var(--border-s);
  display: block;
  transition: border-color .3s;
}

.wishlist-item:hover .wishlist-item-img { border-color: var(--beige-d); }

.wishlist-item-info { display: flex; flex-direction: column; gap: 6px; }

.wishlist-item-info h4 {
  font-family: var(--serif);
  font-size: 18px;
  font-weight: 400;
  font-style: italic;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  line-height: 1.3;
  color: var(--dark);
}

.wishlist-item-price {
  font-family: var(--sans);
  font-size: 13px;
  font-weight: 500;
  color: var(--gold);
  letter-spacing: 0.06em;
}

/* ── Actions ── */
.wishlist-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
  align-items: flex-end;
  min-width: 170px;
}

.btn-add-cart,
.btn-choose-shade {
  position: relative;
  padding: 11px 20px;
  font-family: var(--sans);
  font-size: 9px;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  cursor: pointer;
  overflow: hidden;
  white-space: nowrap;
  isolation: isolate;
  width: 100%;
  text-align: center;
  border-radius: 100px;
  transition: color .35s var(--ease), box-shadow .35s;
}

.btn-add-cart {
  background: var(--marron);
  color: var(--beige-l);
  border: 1px solid var(--marron);
}

.btn-choose-shade {
  background: transparent;
  color: var(--marron);
  border: 1px solid var(--border);
}

.btn-add-cart::before,
.btn-choose-shade::before {
  content: "";
  position: absolute;
  inset: 0;
  transform: translateX(-101%);
  transition: transform .4s cubic-bezier(.77,0,.18,1);
  z-index: -1;
  border-radius: 100px;
}

.btn-add-cart::before    { background: var(--marron-mid); }
.btn-choose-shade::before { background: var(--marron); }

.btn-add-cart:hover::before,
.btn-choose-shade:hover::before { transform: translateX(0); }

.btn-add-cart:hover { box-shadow: 0 6px 20px var(--dark-20); }
.btn-choose-shade:hover { color: var(--beige-l); border-color: var(--marron); }

.btn-share {
  background: none;
  border: none;
  font-family: var(--sans);
  font-size: 9px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: color .25s;
  padding: 0;
}
.btn-share:hover { color: var(--gold); }

.btn-supprimer {
  background: none;
  border: none;
  font-family: var(--sans);
  font-size: 9px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--muted);
  cursor: pointer;
  text-decoration: underline;
  text-underline-offset: 3px;
  transition: color .25s;
  padding: 0;
}
.btn-supprimer:hover { color: #c00; }

/* ── Toast ── */
.toast {
  position: fixed;
  bottom: 32px;
  right: 32px;
  background: var(--marron);
  color: var(--beige-l);
  padding: 13px 24px;
  font-family: var(--sans);
  font-size: 9px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  border-radius: 100px;
  opacity: 0;
  transform: translateY(12px);
  transition: opacity .35s, transform .35s;
  z-index: 9999;
  pointer-events: none;
  box-shadow: 0 8px 28px var(--dark-20);
}
.toast.show { opacity: 1; transform: translateY(0); }

/* ── Responsive ── */
@media (max-width: 768px) {
  .wishlist-item {
    grid-template-columns: 90px 1fr;
    grid-template-rows: auto auto;
    gap: 14px;
  }
  .wishlist-actions {
    grid-column: 1 / -1;
    flex-direction: row;
    flex-wrap: wrap;
    align-items: center;
    min-width: unset;
  }
  .btn-add-cart, .btn-choose-shade { width: auto; flex: 1; }
  .wishlist-item-img { width: 90px; height: 90px; }
}

@media (max-width: 480px) {
  .wishlist-item { gap: 10px; }
  .btn-add-cart, .btn-choose-shade { width: 100%; }
}
  </style>
</head>
<body>

<?php include_once 'includes/sidebar.php'; ?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/product_modal.php'; ?>

<div class="page-hero">
  <h1>Ma <em>Wishlist</em></h1>
  <span class="count" id="wishlistCount"></span>
</div>

<main class="page-body">
  <div class="wishlist-grid" id="wishlistContainer"></div>
</main>

<div class="toast" id="toast"></div>

<!-- BASE_URL disponible avant shop.js -->
<script>const BASE_URL = "<?= $b ?>";</script>

<!--
  ORDRE DE CHARGEMENT CRITIQUE :
  1. shop.js en premier → initialise window.cart, window.wishlist,
     window.renderCart(), window.renderWishlist(), window.openCart()
     et enregistre l'écouteur délégué body → click → .choose-shade-btn
  2. Notre script de page APRÈS → peut appeler ces fonctions
-->
<script src="<?= $b ?>/js/shop.js?v=<?= time() ?>"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

  const B = BASE_URL;

  /* ── Helpers ── */
  const buildKey = (productId, shade) =>
    shade ? `${productId}__${shade}` : String(productId);

  const normalizeImg = (url) => {
    if (!url) return B + "/images/placeholder.jpg";
    if (url.startsWith("http")) return url;
    return B + "/images/" + url.split("/").pop();
  };

  const fmt = (v) =>
    (isNaN(parseFloat(v)) ? 0 : parseFloat(v))
      .toFixed(2).replace(".", ",") + " DA";

  /* ── Écriture synchronisée localStorage + objet mémoire sidebar ──
   *
   * La sidebar (sidebar.php) lit window.cart / window.wishlist EN MÉMOIRE.
   * Notre page lit localStorage.
   * On doit mettre à jour les DEUX en même temps pour que :
   *   - la sidebar reflète l'ajout immédiatement (sans rechargement)
   *   - les autres pages trouvent la bonne valeur dans localStorage
   */
  function cartAdd(productId, name, price, image_url, shade) {
    const key   = buildKey(productId, shade);
    const entry = { productId, name, price: parseFloat(price), image_url, shade: shade || null, quantity: 1 };

    /* localStorage */
    const stored = JSON.parse(localStorage.getItem("cart") || "{}");
    if (stored[key]) stored[key].quantity += 1;
    else             stored[key] = entry;
    localStorage.setItem("cart", JSON.stringify(stored));

    /* Objet mémoire sidebar */
    if (window.cart) {
      if (window.cart[key]) window.cart[key].quantity += 1;
      else                  window.cart[key] = { ...entry };
    }

    /* Rafraîchir sidebar */
    if (typeof window.renderCart === "function") window.renderCart();
    if (typeof window.openCart   === "function") window.openCart();
  }

  function wishlistRemove(key) {
    /* localStorage */
    const stored = JSON.parse(localStorage.getItem("wishlist") || "{}");
    delete stored[key];
    localStorage.setItem("wishlist", JSON.stringify(stored));

    /* Objet mémoire sidebar */
    if (window.wishlist) delete window.wishlist[key];

    /* Rafraîchir sidebar wishlist */
    if (typeof window.renderWishlist === "function") window.renderWishlist();
  }

  /* ── Toast ── */
  let toastTimer;
  function showToast(msg) {
    const t = document.getElementById("toast");
    t.textContent = msg;
    t.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove("show"), 2800);
  }

  /* ── Rendu de la page wishlist ── */
  function renderPage() {
    /* Source de vérité = localStorage (indépendant de l'objet mémoire sidebar) */
    const wl      = JSON.parse(localStorage.getItem("wishlist") || "{}");
    const el      = document.getElementById("wishlistContainer");
    const countEl = document.getElementById("wishlistCount");
    el.innerHTML  = "";

    const keys = Object.keys(wl).filter(k => wl[k]?.name);

    if (!keys.length) {
      countEl.textContent = "";
      el.innerHTML = `
        <div class="empty-state">
          <i class="fas fa-heart empty-icon"></i>
          <p>Votre wishlist est encore vide…</p>
          <a href="${B}/categorie.php?categorie=Tous">Découvrir la boutique</a>
        </div>`;
      return;
    }

    countEl.textContent =
      `${keys.length} article${keys.length > 1 ? "s" : ""} sauvegardé${keys.length > 1 ? "s" : ""}`;

    keys.forEach(key => {
      const item  = wl[key];
      if (!item?.name) return;

      const img   = normalizeImg(item.image_url);
      const price = parseFloat(item.price) || 0;

      /*
       * Bouton "Choisir une teinte" :
       *   On lui donne la classe "choose-shade-btn" que shop.js écoute
       *   via délégation sur document.body → le clic sera intercepté
       *   naturellement par shop.js sans avoir besoin d'appeler
       *   openShadeModal() directement.
       *
       *   On ajoute data-from-wishlist et data-wishlist-key pour que
       *   shop.js puisse retirer l'article de la wishlist après ajout.
       */
      const actionBtn = item.hasShades
        ? `<button class="btn-choose-shade choose-shade-btn"
                data-product-id="${item.productId}"
                data-name="${item.name.replace(/"/g, '&quot;')}"
                data-price="${price}"
                data-image_url="${item.image_url || ''}"
                data-from-wishlist="1"
                data-wishlist-key="${key}">
             <i class="fas fa-palette" style="margin-right:7px;font-size:9px;"></i>Choisir une teinte
           </button>`
        : `<button class="btn-add-cart js-add-btn"
                data-key="${key}"
                data-product-id="${item.productId}"
                data-name="${item.name.replace(/"/g, '&quot;')}"
                data-price="${price}"
                data-image_url="${item.image_url || ''}"
                data-shade="${item.shade || ''}">
             <i class="fas fa-shopping-bag" style="margin-right:7px;font-size:9px;"></i>Ajouter au panier
           </button>`;

      const div = document.createElement("div");
      div.className = "wishlist-item";
      div.innerHTML = `
        <img src="${img}" class="wishlist-item-img"
             alt="${item.name}"
             onerror="this.src='${B}/images/placeholder.jpg'">
        <div class="wishlist-item-info">
          <h4>${item.name}</h4>
          <div class="wishlist-item-price">${fmt(price)}</div>
        </div>
        <div class="wishlist-actions">
          ${actionBtn}
          <button class="btn-share js-share-btn">
            <i class="fas fa-share" style="font-size:9px;"></i> Partager
          </button>
          <button class="btn-supprimer js-del-btn" data-key="${key}">
            <i class="fas fa-trash" style="font-size:9px;margin-right:4px;"></i>Supprimer
          </button>
        </div>`;

      el.appendChild(div);
    });

    /* ── Événements ── */

    /* Ajouter au panier (sans teinte) */
    el.querySelectorAll(".js-add-btn").forEach(btn => {
      btn.addEventListener("click", () => {
        cartAdd(
          btn.dataset.productId,
          btn.dataset.name,
          btn.dataset.price,
          btn.getAttribute("data-image_url"),
          btn.dataset.shade || null
        );
        wishlistRemove(btn.dataset.key);
        showToast("Produit ajouté au panier ✓");
        renderPage();
      });
    });

    /*
     * "Choisir une teinte" → shop.js écoute body → click → .choose-shade-btn
     * Le bouton a déjà la classe choose-shade-btn, donc le clic natif
     * remontera et sera capté par shop.js. Rien à faire ici.
     *
     * Après que shop.js ajoute l'article au panier depuis la modale,
     * il supprime la clé de localStorage.wishlist.
     * L'événement "storage" ci-dessous rechargera la page wishlist.
     */

    /* Partager */
    el.querySelectorAll(".js-share-btn").forEach(btn => {
      btn.addEventListener("click", () => {
        if (navigator.share) {
          navigator.share({ title: document.title, url: window.location.href }).catch(() => {});
        } else {
          navigator.clipboard.writeText(window.location.href)
            .then(() => showToast("Lien copié !"))
            .catch(() => showToast("Impossible de copier."));
        }
      });
    });

    /* Supprimer */
    el.querySelectorAll(".js-del-btn").forEach(btn => {
      btn.addEventListener("click", () => {
        wishlistRemove(btn.dataset.key);
        showToast("Article retiré de la wishlist.");
        renderPage();
      });
    });
  }

  /*
   * Quand shop.js (modale teinte) modifie localStorage,
   * l'événement "storage" est émis → on re-rend la page.
   * (Ne se déclenche PAS sur le même onglet dans tous les navigateurs,
   *  mais on écoute quand même pour la synchro multi-onglets.)
   */
  window.addEventListener("storage", (e) => {
    if (e.key === "wishlist" || e.key === "cart") renderPage();
  });

  /*
   * Patch : quand shop.js appelle renderWishlist() depuis la modale
   * (après avoir retiré l'article), on re-rend aussi notre page.
   */
  const _origRenderWishlist = window.renderWishlist;
  window.renderWishlist = function (...args) {
    if (typeof _origRenderWishlist === "function") _origRenderWishlist(...args);
    renderPage();
  };

  /* ── Init ── */
  renderPage();
  document.body.style.visibility = "visible";
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>