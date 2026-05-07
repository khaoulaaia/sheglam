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
  <title>Mon Panier — SheGlamour</title>

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

    /* ── Layout ── */
    .page-body {
      flex: 1;
      max-width: 1160px;
      width: 100%;
      margin: 52px auto 100px;
      padding: 0 28px;
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 44px;
      align-items: flex-start;
    }

    /* ── Vide ── */
    .empty-state {
      grid-column: 1 / -1;
      text-align: center;
      padding: 100px 20px;
    }
    .empty-state .empty-icon {
      font-size: 52px;
      color: var(--rose);
      margin-bottom: 24px;
      display: block;
      animation: pulse 2.5s ease-in-out infinite;
    }
    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 1; }
      50%       { transform: scale(1.08); opacity: .8; }
    }
    .empty-state p {
      font-family: 'Cormorant Garamond', serif;
      font-size: 24px;
      color: var(--gris);
      font-style: italic;
      margin-bottom: 32px;
    }
    .empty-state a {
      display: inline-block;
      padding: 14px 40px;
      background: var(--noir);
      color: #fff;
      font-size: 11px;
      letter-spacing: 0.26em;
      text-transform: uppercase;
      text-decoration: none;
      transition: background .35s;
    }
    .empty-state a:hover { background: var(--rose-dark); }

    /* ── Liste articles ── */
    .cart-list-wrap {
      background: #fff;
      border: 1px solid var(--border);
    }

    .cart-list-header {
      padding: 20px 28px;
      border-bottom: 1px solid var(--border);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .cart-list-header span {
      font-size: 10px;
      letter-spacing: 0.22em;
      text-transform: uppercase;
      color: var(--gris);
    }

    .cart-item {
      display: grid;
      grid-template-columns: 105px 1fr;
      gap: 22px;
      padding: 26px 28px;
      border-bottom: 1px solid var(--border);
      transition: background .2s ease;
    }
    .cart-item:last-child { border-bottom: none; }
    .cart-item:hover { background: rgba(242,196,206,.05); }

    .cart-item-img {
      width: 105px;
      height: 105px;
      object-fit: contain;
      background: var(--rose-pale);
      border: 1px solid var(--border);
      display: block;
    }

    .cart-item-info {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .cart-item-top {}

    .cart-item-info h4 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 17px;
      font-weight: 500;
      letter-spacing: 0.07em;
      text-transform: uppercase;
      line-height: 1.3;
      margin-bottom: 4px;
    }
    .cart-item-shade {
      font-size: 11px;
      color: var(--gris);
      letter-spacing: 0.08em;
      margin-bottom: 5px;
    }
    .cart-item-shade i {
      font-size: 8px;
      color: var(--rose-dark);
      margin-right: 5px;
    }
    .cart-item-price {
      font-size: 13px;
      font-weight: 500;
      color: var(--rose-dark);
    }

    /* Ligne basse */
    .cart-item-bottom {
      display: flex;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
      margin-top: 14px;
    }

    /* Quantité */
    .qty-controls {
      display: flex;
      align-items: center;
    }
    .qty-controls button {
      width: 32px;
      height: 32px;
      background: #fff;
      border: 1px solid var(--border);
      color: var(--noir);
      font-size: 16px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all .25s;
    }
    .qty-controls button:hover {
      background: var(--noir);
      color: #fff;
      border-color: var(--noir);
    }
    .qty-controls .qty-display {
      width: 42px;
      height: 32px;
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      font-weight: 500;
    }

    /* Total ligne */
    .cart-item-line-total {
      font-family: 'Cormorant Garamond', serif;
      font-size: 17px;
      font-weight: 600;
      color: var(--noir);
      margin-left: auto;
    }

    /* Bouton retirer */
    .btn-retirer {
      background: none;
      border: none;
      font-size: 10px;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: var(--gris);
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: color .25s;
      padding: 0;
    }
    .btn-retirer:hover { color: #c00; }

    /* ── Récapitulatif ── */
    .cart-summary {
      background: #fff;
      border: 1px solid var(--border);
      padding: 34px 28px;
      position: sticky;
      top: 114px;
    }

    .cart-summary h2 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 20px;
      font-weight: 400;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      margin-bottom: 28px;
      padding-bottom: 18px;
      border-bottom: 1px solid var(--border);
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 11px 0;
      font-size: 12px;
      color: var(--gris);
      letter-spacing: 0.05em;
    }
    .summary-row.total {
      margin-top: 16px;
      padding-top: 20px;
      border-top: 2px solid var(--border);
      font-size: 13px;
      font-weight: 600;
      color: var(--noir);
    }
    .summary-row.total span:last-child {
      font-family: 'Cormorant Garamond', serif;
      font-size: 22px;
      color: var(--rose-dark);
    }

    /* Bouton caisse */
    .btn-caisse {
      position: relative;
      width: 100%;
      margin-top: 24px;
      padding: 18px 24px;
      background: var(--noir);
      color: #fff;
      border: none;
      font-family: 'DM Sans', sans-serif;
      font-size: 10px;
      letter-spacing: 0.3em;
      text-transform: uppercase;
      cursor: pointer;
      overflow: hidden;
      transition: color .45s;
      isolation: isolate;
    }
    .btn-caisse::before {
      content: "";
      position: absolute;
      inset: 0;
      background: var(--rose-dark);
      transform: translateX(-101%);
      transition: transform .45s cubic-bezier(.77,0,.18,1);
      z-index: -1;
    }
    .btn-caisse:hover::before { transform: translateX(0); }

    /* Continuer */
    .continue-link {
      display: block;
      text-align: center;
      margin-top: 16px;
      font-size: 10px;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: var(--gris);
      text-decoration: none;
      text-underline-offset: 3px;
      transition: color .25s;
    }
    .continue-link:hover { color: var(--noir); text-decoration: underline; }

    /* Code promo */
    .promo-wrap {
      margin-top: 24px;
      padding-top: 22px;
      border-top: 1px solid var(--border);
    }
    .promo-wrap label {
      display: block;
      font-size: 10px;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: var(--gris);
      margin-bottom: 10px;
    }
    .promo-row {
      display: flex;
      gap: 0;
    }
    .promo-row input {
      flex: 1;
      padding: 10px 14px;
      border: 1px solid var(--border);
      border-right: none;
      font-size: 12px;
      font-family: 'DM Sans', sans-serif;
      outline: none;
      background: var(--rose-pale);
      transition: border-color .25s;
    }
    .promo-row input:focus { border-color: var(--rose-dark); background: #fff; }
    .promo-row button {
      padding: 10px 16px;
      background: var(--noir);
      color: #fff;
      border: none;
      font-size: 10px;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      cursor: pointer;
      transition: background .3s;
      white-space: nowrap;
    }
    .promo-row button:hover { background: var(--rose-dark); }

    /* Badges sécurité */
    .secure-badges {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: center;
      margin-top: 22px;
      padding-top: 18px;
      border-top: 1px solid var(--border);
    }
    .secure-badge {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 10px;
      color: var(--gris);
      letter-spacing: 0.1em;
    }
    .secure-badge i { color: var(--rose-dark); font-size: 11px; }

    /* ── Responsive ── */
    @media (max-width: 940px) {
      .page-body { grid-template-columns: 1fr; }
      .cart-summary { position: static; }
    }
    @media (max-width: 600px) {
      .cart-item { grid-template-columns: 82px 1fr; gap: 14px; padding: 18px 18px; }
      .cart-list-header { padding: 16px 18px; }
      .cart-item-img { width: 82px; height: 82px; }
      .cart-summary { padding: 24px 18px; }
    }
  </style>
</head>
<body>

<?php include_once 'includes/sidebar.php'; ?>
<?php include 'includes/header.php'; ?>

<!-- Bannière -->
<div class="page-hero">
  <h1>Mon <em>Panier</em></h1>
  <span class="count" id="pageCartCount"></span>
</div>

<!-- Contenu -->
<main class="page-body" id="pageBody">

  <!-- Liste articles — remplie par JS -->
  <div id="pageCartList"></div>

  <!-- Récapitulatif — masqué si vide -->
  <aside class="cart-summary" id="pageSummary" style="display:none;">
    <h2>Récapitulatif</h2>
    <div class="summary-row">
      <span>Sous-total</span>
      <span id="pageSubtotal">0,00 DA</span>
    </div>
    <div class="summary-row">
      <span>Livraison</span>
      <span>Calculée à l'étape suivante</span>
    </div>
    <div class="summary-row total">
      <span>Total</span>
      <span id="pageTotal">0,00 DA</span>
    </div>

    <button class="btn-caisse" id="pageCaisseBtn">
      <i class="fas fa-lock" style="margin-right:9px;font-size:9px;"></i>Passer à la caisse
    </button>
    <a href="<?= $b ?>/categorie.php?categorie=Tous" class="continue-link">← Continuer mes achats</a>

    <!-- Code promo -->
    <div class="promo-wrap">
      <label>Code promo</label>
      <div class="promo-row">
        <input type="text" id="promoInput" placeholder="ex : BEAUTE20">
        <button id="promoBtn">Appliquer</button>
      </div>
      <div id="promoMsg" style="font-size:11px;color:var(--rose-dark);margin-top:8px;letter-spacing:.08em;"></div>
    </div>

    <!-- Badges -->
    <div class="secure-badges">
      <span class="secure-badge"><i class="fas fa-lock"></i> Paiement sécurisé</span>
      <span class="secure-badge"><i class="fas fa-undo"></i> Retours gratuits</span>
      <span class="secure-badge"><i class="fas fa-headset"></i> Support 7j/7</span>
    </div>
  </aside>

</main>

<!-- BASE_URL requis par shop.js et checkout.js -->
<script>const BASE_URL = "<?= $b ?>";</script>

<script>
/*
 * La page panier gère son propre rendu via #pageCartList.
 * shop.js gère uniquement la SIDEBAR (#cartItems dans sidebar).
 * On évite tout conflit en utilisant des IDs différents.
 */
document.addEventListener("DOMContentLoaded", function () {

  const B         = BASE_URL;
  const listEl    = document.getElementById("pageCartList");
  const summaryEl = document.getElementById("pageSummary");
  const countEl   = document.getElementById("pageCartCount");

  /* ── Helpers ── */
  const getCart  = () => JSON.parse(localStorage.getItem("cart") || "{}");
  const saveCart = (c) => localStorage.setItem("cart", JSON.stringify(c));

  const toPrice = (v) => {
    const n = parseFloat(String(v ?? 0).replace(",", "."));
    return isNaN(n) ? 0 : n;
  };

  const fmt = (v) =>
    toPrice(v).toFixed(2).replace(".", ",") + " DA";

  const normalizeImg = (url) => {
    if (!url) return B + "/images/placeholder.jpg";
    if (url.startsWith("http")) return url;
    return B + "/images/" + url.split("/").pop();
  };

  /* ── Calcul total ── */
  function calcTotal(cart) {
    let total = 0, count = 0;
    Object.values(cart).forEach(item => {
      const p = toPrice(item.price);
      const q = parseInt(item.quantity) || 0;
      total += p * q;
      count += q;
    });
    return { total, count };
  }

  /* ── Mise à jour du récap ── */
  function updateSummary(cart) {
    const { total, count } = calcTotal(cart);
    document.getElementById("pageTotal").textContent    = fmt(total);
    document.getElementById("pageSubtotal").textContent = fmt(total);
    countEl.textContent = count
      ? `${count} article${count > 1 ? "s" : ""}`
      : "";
  }

  /* ── Rendu complet ── */
  function renderPage() {
    const cart = getCart();
    const keys = Object.keys(cart).filter(k => cart[k]?.name);

    listEl.innerHTML = "";

    /* Panier vide */
    if (!keys.length) {
      summaryEl.style.display = "none";
      countEl.textContent     = "";
      listEl.innerHTML = `
        <div class="empty-state">
          <i class="fas fa-shopping-bag empty-icon"></i>
          <p>Votre panier est encore vide…</p>
          <a href="${B}/categorie.php?categorie=Tous">Découvrir la boutique</a>
        </div>`;
      return;
    }

    /* Panier rempli */
    summaryEl.style.display = "block";
    updateSummary(cart);

    /* Wrapper liste */
    const wrap = document.createElement("div");
    wrap.className = "cart-list-wrap";

    /* En-tête */
    const hdr = document.createElement("div");
    hdr.className = "cart-list-header";
    hdr.innerHTML = `
      <span>${keys.length} article${keys.length > 1 ? "s" : ""}</span>
      <span>Total ligne</span>`;
    wrap.appendChild(hdr);

    /* Articles */
    keys.forEach(key => {
      const item  = cart[key];
      const price = toPrice(item.price);
      const qty   = parseInt(item.quantity) || 1;
      const img   = normalizeImg(item.image_url);

      const div = document.createElement("div");
      div.className = "cart-item";
      div.innerHTML = `
        <img src="${img}" alt="${item.name}" class="cart-item-img"
             onerror="this.src='${B}/images/placeholder.jpg'">

        <div class="cart-item-info">
          <div class="cart-item-top">
            <h4>${item.name}</h4>
            ${item.shade
              ? `<div class="cart-item-shade">
                   <i class="fas fa-circle"></i>${item.shade}
                 </div>`
              : ""}
            <div class="cart-item-price">${fmt(price)} / unité</div>
          </div>
          <div class="cart-item-bottom">
            <div class="qty-controls">
              <button class="btn-decrease" aria-label="Diminuer">−</button>
              <span class="qty-display">${qty}</span>
              <button class="btn-increase" aria-label="Augmenter">+</button>
            </div>
            <div class="cart-item-line-total">${fmt(price * qty)}</div>
            <button class="btn-retirer">
              <i class="fas fa-trash-can" style="font-size:9px;"></i> Retirer
            </button>
          </div>
        </div>`;

      /* Événements */
      div.querySelector(".btn-increase").addEventListener("click", () => {
        const c = getCart();
        if (!c[key]) return;
        c[key].quantity = (parseInt(c[key].quantity) || 0) + 1;
        saveCart(c);
        renderPage();
        if (typeof window.__sidebarRenderCart === "function") window.__sidebarRenderCart();
      });

      div.querySelector(".btn-decrease").addEventListener("click", () => {
        const c = getCart();
        if (!c[key]) return;
        c[key].quantity = (parseInt(c[key].quantity) || 1) - 1;
        if (c[key].quantity <= 0) delete c[key];
        saveCart(c);
        renderPage();
        if (typeof window.__sidebarRenderCart === "function") window.__sidebarRenderCart();
      });

      div.querySelector(".btn-retirer").addEventListener("click", () => {
        const c = getCart();
        delete c[key];
        saveCart(c);
        renderPage();
        if (typeof window.__sidebarRenderCart === "function") window.__sidebarRenderCart();
      });

      wrap.appendChild(div);
    });

    listEl.appendChild(wrap);
    updateSummary(cart);
  }

  /* ── Code promo ── */
  const PROMOS = { "BEAUTE20": 20, "GLAMOUR10": 10, "SHEGLAM15": 15 };

  document.getElementById("promoBtn").addEventListener("click", () => {
    const code = document.getElementById("promoInput").value.trim().toUpperCase();
    const msg  = document.getElementById("promoMsg");
    if (PROMOS[code]) {
      msg.textContent = `✓ Code "${code}" appliqué — ${PROMOS[code]}% de remise`;
      msg.style.color = "var(--rose-dark)";
    } else {
      msg.textContent = "Code invalide ou expiré.";
      msg.style.color = "#c00";
    }
  });

  /* ── Bouton caisse ── */
  document.getElementById("pageCaisseBtn").addEventListener("click", () => {
    const cart = getCart();
    if (!Object.keys(cart).length) { alert("Votre panier est vide."); return; }
    if (typeof window.SheGlamCheckout?.open === "function") window.SheGlamCheckout.open();
    else if (typeof window.openCheckout === "function")      window.openCheckout();
    else alert("Redirection vers la caisse…");
  });

  window.__pageRenderCart = renderPage;

  /* Stocke l'original de shop.js après son chargement */
  const origRender = window.renderCart;
  window.renderCart = function (...args) {
    if (typeof origRender === "function") origRender(...args); // sidebar
    renderPage(); // notre page
  };
  window.__sidebarRenderCart = () => {
    if (typeof origRender === "function") origRender();
  };

  /* Sync si l'utilisateur a le panier ouvert dans un autre onglet */
  window.addEventListener("storage", renderPage);

  /* ── Init ── */
  renderPage();
  document.body.style.visibility = "visible";
});
</script>

<!-- shop.js et checkout.js chargés après notre init -->
<script src="<?= $b ?>/js/shop.js?v=<?= time() ?>"></script>
<script src="<?= $b ?>/js/checkout.js?v=<?= time() ?>"></script>

<?php include 'includes/footer.php'; ?>
</body>
</html>