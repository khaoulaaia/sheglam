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
    /* ── Variables ─────────────────────────────────────── */
    :root {
      --rose:       #f2c4ce;
      --rose-dark:  #d4849a;
      --rose-pale:  #fdf0f3;
      --noir:       #111111;
      --gris:       #888888;
      --border:     #ecdde1;
    }

    /* ── Reset & Base ───────────────────────────────────── */
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

    /* ── Hero bannière page ─────────────────────────────── */
    .page-hero {
      margin-top: 90px; /* compense header fixe */
      background: linear-gradient(135deg, #fff 0%, var(--rose-pale) 60%, #fce4ec 100%);
      border-bottom: 1px solid var(--border);
      padding: 56px 6% 48px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    /* Décoration florale légère */
    .page-hero::before {
      content: "✦";
      position: absolute;
      top: 20px;
      left: 6%;
      font-size: 10px;
      color: var(--rose-dark);
      letter-spacing: 6px;
      opacity: .5;
    }
    .page-hero::after {
      content: "✦";
      position: absolute;
      top: 20px;
      right: 6%;
      font-size: 10px;
      color: var(--rose-dark);
      letter-spacing: 6px;
      opacity: .5;
    }

    .page-hero h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(32px, 5vw, 52px);
      font-weight: 400;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--noir);
      line-height: 1.15;
    }

    .page-hero h1 em {
      font-style: italic;
      color: var(--rose-dark);
    }

    .page-hero .count {
      display: inline-block;
      margin-top: 12px;
      font-size: 12px;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: var(--gris);
    }

    /* ── Contenu principal ──────────────────────────────── */
    .page-body {
      flex: 1;
      max-width: 960px;
      width: 100%;
      margin: 48px auto 80px;
      padding: 0 24px;
    }

    /* ── Vide ───────────────────────────────────────────── */
    .empty-state {
      text-align: center;
      padding: 80px 20px;
    }
    .empty-state i {
      font-size: 48px;
      color: var(--rose);
      margin-bottom: 20px;
      display: block;
    }
    .empty-state p {
      font-family: 'Cormorant Garamond', serif;
      font-size: 22px;
      color: var(--gris);
      font-style: italic;
      margin-bottom: 28px;
    }
    .empty-state a {
      display: inline-block;
      padding: 13px 36px;
      background: var(--noir);
      color: #fff;
      font-size: 11px;
      letter-spacing: 0.24em;
      text-transform: uppercase;
      text-decoration: none;
      transition: background .3s ease;
    }
    .empty-state a:hover { background: #333; }

    /* ── Carte produit wishlist ─────────────────────────── */
    .wishlist-grid {
      display: flex;
      flex-direction: column;
      gap: 0;
    }

    .wishlist-item {
      display: grid;
      grid-template-columns: 120px 1fr auto;
      align-items: center;
      gap: 28px;
      padding: 28px 0;
      border-bottom: 1px solid var(--border);
      transition: background .25s ease;
    }

    .wishlist-item:first-child { border-top: 1px solid var(--border); }

    .wishlist-item:hover {
      background: rgba(242, 196, 206, 0.06);
      margin: 0 -16px;
      padding: 28px 16px;
    }

    /* Image */
    .wishlist-item-img {
      width: 120px;
      height: 120px;
      object-fit: contain;
      background: #fff;
      border: 1px solid var(--border);
    }

    /* Infos */
    .wishlist-item-info {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .wishlist-item-info h4 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 18px;
      font-weight: 500;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: var(--noir);
      line-height: 1.3;
    }

    .wishlist-item-price {
      font-size: 14px;
      font-weight: 500;
      color: var(--rose-dark);
      letter-spacing: 0.04em;
    }

    /* Actions */
    .wishlist-actions {
      display: flex;
      flex-direction: column;
      gap: 10px;
      align-items: flex-end;
    }

    .btn-panier,
    .choose-shade-btn {
      position: relative;
      padding: 11px 22px;
      background: var(--noir);
      color: #fff;
      border: none;
      font-family: 'DM Sans', sans-serif;
      font-size: 11px;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      cursor: pointer;
      overflow: hidden;
      transition: color .4s ease;
      white-space: nowrap;
    }

    .btn-panier::before,
    .choose-shade-btn::before {
      content: "";
      position: absolute;
      inset: 0;
      background: var(--rose-dark);
      transform: translateX(-101%);
      transition: transform .4s cubic-bezier(.77,0,.18,1);
      z-index: -1;
    }

    .btn-panier:hover::before,
    .choose-shade-btn:hover::before { transform: translateX(0); }

    .btn-panier { isolation: isolate; }
    .choose-shade-btn { isolation: isolate; }

    .btn-supprimer {
      background: none;
      border: none;
      font-size: 11px;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: var(--gris);
      cursor: pointer;
      text-decoration: underline;
      text-underline-offset: 3px;
      transition: color .25s ease;
    }
    .btn-supprimer:hover { color: #c00; }

    /* ── Responsive ─────────────────────────────────────── */
    @media (max-width: 768px) {
      .wishlist-item {
        grid-template-columns: 90px 1fr;
        grid-template-rows: auto auto;
        gap: 16px;
      }

      .wishlist-actions {
        grid-column: 1 / -1;
        flex-direction: row;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
      }

      .wishlist-item-img {
        width: 90px;
        height: 90px;
      }
    }

    @media (max-width: 480px) {
      .wishlist-item { gap: 12px; }
      .btn-panier, .choose-shade-btn { width: 100%; text-align: center; }
    }
  </style>
</head>
<body>

<?php include_once 'includes/sidebar.php'; ?>
<?php include 'includes/header.php'; ?>

<!-- Bannière -->
<div class="page-hero">
  <h1>Ma <em>Wishlist</em></h1>
  <span class="count" id="wishlistCount">— articles sauvegardés</span>
</div>

<!-- Contenu -->
<main class="page-body">
  <div class="wishlist-grid" id="wishlistItems"></div>
</main>

<?php include 'includes/product_modal.php'; ?>

<script src="<?= $b ?>/js/shop.js?v=<?= time() ?>"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const wishlist = JSON.parse(localStorage.getItem("wishlist")) || {};
  const cart     = JSON.parse(localStorage.getItem("cart"))     || {};
  const b        = "<?= $b ?>";

  const saveWishlist = () => localStorage.setItem("wishlist", JSON.stringify(wishlist));
  const saveCart     = () => localStorage.setItem("cart",     JSON.stringify(cart));
  const buildKey     = (productId, shade) => shade ? `${productId}__${shade}` : `${productId}`;

  function renderWishlist() {
    const el    = document.getElementById("wishlistItems");
    const count = document.getElementById("wishlistCount");
    el.innerHTML = "";

    const keys = Object.keys(wishlist);
    count.textContent = keys.length
      ? `${keys.length} article${keys.length > 1 ? 's' : ''} sauvegardé${keys.length > 1 ? 's' : ''}`
      : '';

    if (!keys.length) {
      el.innerHTML = `
        <div class="empty-state">
          <i class="fas fa-heart"></i>
          <p>Votre wishlist est vide…</p>
          <a href="${b}/categorie.php?categorie=Tous">Découvrir la boutique</a>
        </div>`;
      return;
    }

    keys.forEach(key => {
      const item = wishlist[key];
      if (!item || !item.name) return;

      const img = item.image_url && item.image_url.startsWith("http")
        ? item.image_url
        : b + '/images/' + (item.image_url || 'placeholder.jpg').split("/").pop();

      const actionBtn = item.hasShades
        ? `<button class="choose-shade-btn"
              data-from-wishlist="1"
              data-wishlist-key="${key}"
              data-product-id="${item.productId}"
              data-name="${item.name}"
              data-price="${item.price}"
              data-image_url="${item.image_url}">
            <i class="fas fa-palette"></i> Choisir une teinte
           </button>`
        : `<button class="btn-panier move-to-cart">
            <i class="fas fa-shopping-bag"></i> Ajouter au panier
           </button>`;

      const div = document.createElement("div");
      div.className = "wishlist-item";
      div.innerHTML = `
        <img src="${img}" class="wishlist-item-img" alt="${item.name}">
        <div class="wishlist-item-info">
          <h4>${item.name}</h4>
          <div class="wishlist-item-price">${parseFloat(item.price).toFixed(2)} DA</div>
        </div>
        <div class="wishlist-actions">
          ${actionBtn}
          <button class="btn-supprimer remove-from-wishlist">Supprimer</button>
        </div>`;

      // Ajouter au panier
      const moveBtn = div.querySelector(".move-to-cart");
      if (moveBtn) {
        moveBtn.addEventListener("click", () => {
          const cartKey = buildKey(item.productId, item.shade);
          if (cart[cartKey]) cart[cartKey].quantity += 1;
          else cart[cartKey] = { productId: item.productId, name: item.name, price: item.price, image_url: item.image_url, shade: item.shade || null, quantity: 1 };
          saveCart();
          delete wishlist[key];
          saveWishlist();
          renderWishlist();
        });
      }

      // Choisir teinte
      const shadeBtn = div.querySelector(".choose-shade-btn");
      if (shadeBtn) {
        shadeBtn.addEventListener("click", () => {
          if (typeof window.openShadeModal === "function") window.openShadeModal(shadeBtn);
        });
      }

      // Supprimer
      div.querySelector(".remove-from-wishlist").addEventListener("click", () => {
        delete wishlist[key];
        saveWishlist();
        renderWishlist();
      });

      el.appendChild(div);
    });
  }

  renderWishlist();
  document.body.style.visibility = 'visible';
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>