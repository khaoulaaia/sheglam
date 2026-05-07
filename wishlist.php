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
    :root {
      --rose:      #f2c4ce;
      --rose-dark: #d4849a;
      --rose-pale: #fdf0f3;
      --noir:      #111111;
      --gris:      #888888;
      --border:    #ecdde1;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--rose-pale);
      color: var(--noir);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      visibility: hidden;
    }

    /* ── Hero ── */
    .page-hero {
      margin-top: 90px;
      background: linear-gradient(135deg, #fff 0%, var(--rose-pale) 60%, #fce4ec 100%);
      border-bottom: 1px solid var(--border);
      padding: 60px 6% 50px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .page-hero::before {
      content: "✦  ✦  ✦";
      position: absolute; top: 22px; left: 6%;
      font-size: 9px; color: var(--rose-dark);
      letter-spacing: 8px; opacity: .45;
    }
    .page-hero::after {
      content: "✦  ✦  ✦";
      position: absolute; top: 22px; right: 6%;
      font-size: 9px; color: var(--rose-dark);
      letter-spacing: 8px; opacity: .45;
    }
    .page-hero h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(34px, 5vw, 54px);
      font-weight: 400;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      line-height: 1.15;
    }
    .page-hero h1 em { font-style: italic; color: var(--rose-dark); }
    .page-hero .count {
      display: inline-block;
      margin-top: 14px;
      font-size: 11px;
      letter-spacing: 0.24em;
      text-transform: uppercase;
      color: var(--gris);
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
      font-size: 52px; color: var(--rose);
      margin-bottom: 24px; display: block;
      animation: pulse 2.5s ease-in-out infinite;
    }
    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 1; }
      50%       { transform: scale(1.08); opacity: .8; }
    }
    .empty-state p {
      font-family: 'Cormorant Garamond', serif;
      font-size: 24px; color: var(--gris);
      font-style: italic; margin-bottom: 32px;
    }
    .empty-state a {
      display: inline-block; padding: 14px 40px;
      background: var(--noir); color: #fff;
      font-size: 11px; letter-spacing: 0.26em;
      text-transform: uppercase; text-decoration: none;
      transition: background .35s;
    }
    .empty-state a:hover { background: var(--rose-dark); }

    /* ── Grille ── */
    .wishlist-grid { display: flex; flex-direction: column; }

    /* ── Carte article ── */
    .wishlist-item {
      display: grid;
      grid-template-columns: 130px 1fr auto;
      align-items: center;
      gap: 30px;
      padding: 30px 0;
      border-bottom: 1px solid var(--border);
      transition: background .25s, padding .25s, margin .25s;
    }
    .wishlist-item:first-child { border-top: 1px solid var(--border); }
    .wishlist-item:hover {
      background: rgba(242,196,206,.07);
      margin: 0 -18px; padding: 30px 18px;
    }

    .wishlist-item-img {
      width: 130px; height: 130px;
      object-fit: contain;
      background: #fff; border: 1px solid var(--border);
      display: block;
    }

    .wishlist-item-info { display: flex; flex-direction: column; gap: 7px; }
    .wishlist-item-info h4 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 19px; font-weight: 500;
      letter-spacing: 0.07em; text-transform: uppercase; line-height: 1.3;
    }
    .wishlist-item-price {
      font-size: 14px; font-weight: 500;
      color: var(--rose-dark); letter-spacing: 0.04em;
    }

    /* ── Actions ── */
    .wishlist-actions {
      display: flex; flex-direction: column;
      gap: 10px; align-items: flex-end; min-width: 175px;
    }

    .btn-add-cart,
    .btn-choose-shade {
      position: relative;
      padding: 12px 22px;
      font-family: 'DM Sans', sans-serif;
      font-size: 10px; letter-spacing: 0.22em;
      text-transform: uppercase; cursor: pointer;
      overflow: hidden; white-space: nowrap;
      isolation: isolate; width: 100%; text-align: center;
      transition: color .4s;
    }
    .btn-add-cart  { background: var(--noir); color: #fff; border: none; }
    .btn-choose-shade { background: transparent; color: var(--noir); border: 1px solid var(--border); }

    .btn-add-cart::before,
    .btn-choose-shade::before {
      content: ""; position: absolute; inset: 0;
      transform: translateX(-101%);
      transition: transform .4s cubic-bezier(.77,0,.18,1); z-index: -1;
    }
    .btn-add-cart::before    { background: var(--rose-dark); }
    .btn-choose-shade::before { background: var(--noir); }
    .btn-add-cart:hover::before,
    .btn-choose-shade:hover::before { transform: translateX(0); }
    .btn-choose-shade:hover { color: #fff; border-color: var(--noir); }

    .btn-share {
      background: none; border: none;
      font-size: 10px; letter-spacing: 0.14em; text-transform: uppercase;
      color: var(--gris); cursor: pointer;
      display: flex; align-items: center; gap: 6px;
      transition: color .25s; padding: 0;
    }
    .btn-share:hover { color: var(--rose-dark); }

    .btn-supprimer {
      background: none; border: none;
      font-size: 10px; letter-spacing: 0.14em; text-transform: uppercase;
      color: var(--gris); cursor: pointer;
      text-decoration: underline; text-underline-offset: 3px;
      transition: color .25s; padding: 0;
    }
    .btn-supprimer:hover { color: #c00; }

    /* ── Toast ── */
    .toast {
      position: fixed; bottom: 32px; right: 32px;
      background: var(--noir); color: #fff;
      padding: 14px 24px; font-size: 11px;
      letter-spacing: 0.16em; text-transform: uppercase;
      opacity: 0; transform: translateY(12px);
      transition: opacity .35s, transform .35s;
      z-index: 9999; pointer-events: none;
    }
    .toast.show { opacity: 1; transform: translateY(0); }

    /* ── Responsive ── */
    @media (max-width: 768px) {
      .wishlist-item {
        grid-template-columns: 90px 1fr;
        grid-template-rows: auto auto; gap: 14px;
      }
      .wishlist-actions {
        grid-column: 1 / -1; flex-direction: row;
        flex-wrap: wrap; align-items: center; min-width: unset;
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