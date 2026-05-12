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
  <link rel="icon" type="image/png" href="<?= $b ?>/images/logofib.png">
  <style>
/* ══════════════════════════════════════════════════════════
   PANIER — Blanc & Bordeaux
   ══════════════════════════════════════════════════════════ */

:root {
  --bordeaux:   #440B19;
  --bordeaux-s: #5c1022;
  --bordeaux-l: #6e1a2e;
  --bordeaux-xl:#8a2a3e;

  --white:      #ffffff;

  --border:     rgba(68, 11, 25, 0.15);
  --border-s:   rgba(68, 11, 25, 0.10);

  --text:       #440B19;
  --muted:      #6e1a2e;

  --dark:       #440B19;
  --dark-80:    rgba(68, 11, 25, .80);
  --dark-60:    rgba(68, 11, 25, .60);
  --dark-40:    rgba(68, 11, 25, .40);
  --dark-20:    rgba(68, 11, 25, .20);
  --dark-12:    rgba(68, 11, 25, .12);
  --dark-06:    rgba(68, 11, 25, .06);

  --glass:      rgba(255, 255, 255, 0.72);
  --glass-s:    rgba(255, 255, 255, 0.88);

  --serif:      'Cormorant Garamond', Georgia, serif;
  --sans:       'Jost', system-ui, sans-serif;
  --ease:       cubic-bezier(0.25, 0.46, 0.45, 0.94);
  --ease-back:  cubic-bezier(0.34, 1.56, 0.64, 1);
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: var(--sans);
  background: var(--white);
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
  border-bottom: 1px solid var(--border-s);
  padding: 56px 6% 46px;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.page-hero::before {
  content: "✦  ✦  ✦";
  position: absolute; top: 22px; left: 6%;
  font-size: 9px; color: var(--bordeaux);
  letter-spacing: 8px; opacity: .25;
}
.page-hero::after {
  content: "✦  ✦  ✦";
  position: absolute; top: 22px; right: 6%;
  font-size: 9px; color: var(--bordeaux);
  letter-spacing: 8px; opacity: .25;
}

.page-hero h1 {
  font-family: var(--serif);
  font-size: clamp(32px, 5vw, 50px);
  font-weight: 300;
  font-style: italic;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  line-height: 1.1;
  color: var(--bordeaux);
}

.page-hero h1 em { font-style: normal; color: var(--bordeaux-l); }

.page-hero .count {
  display: inline-block;
  margin-top: 14px;
  font-size: 9px;
  letter-spacing: 0.32em;
  text-transform: uppercase;
  color: var(--muted);
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
  font-size: 48px;
  color: var(--bordeaux-l);
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
  color: var(--bordeaux);
  border: 1px solid var(--bordeaux);
  font-family: var(--sans);
  font-size: 9px;
  letter-spacing: 0.28em;
  text-transform: uppercase;
  text-decoration: none;
  border-radius: 0;
  transition: background .3s var(--ease), color .3s var(--ease), box-shadow .3s;
}

.empty-state a:hover {
  background: var(--bordeaux);
  color: var(--white);
  box-shadow: 0 8px 28px var(--dark-20);
}

/* ── Liste articles ── */
.cart-list-wrap {
  background: var(--glass);
  border: 1px solid var(--border-s);
}

.cart-list-header {
  padding: 18px 26px;
  border-bottom: 1px solid var(--border-s);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.cart-list-header span {
  font-family: var(--sans);
  font-size: 8.5px;
  letter-spacing: 0.32em;
  text-transform: uppercase;
  color: var(--muted);
}

/* ── Item panier ── */
.cart-item {
  display: grid;
  grid-template-columns: 100px 1fr;
  gap: 20px;
  padding: 24px 26px;
  border-bottom: 1px solid var(--border-s);
  transition: background .2s ease;
}

.cart-item:last-child { border-bottom: none; }
.cart-item:hover { background: rgba(68, 11, 25, .03); }

.cart-item-img {
  width: 100px;
  height: 100px;
  object-fit: contain;
  background: var(--white);
  border: 1px solid var(--border-s);
  border-radius: 0;
  display: block;
  transition: border-color .3s;
}

.cart-item:hover .cart-item-img { border-color: var(--border); }

.cart-item-info {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.cart-item-info h4 {
  font-family: var(--serif);
  font-size: 16px;
  font-weight: 400;
  font-style: italic;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  line-height: 1.3;
  margin-bottom: 4px;
  color: var(--bordeaux);
}

.cart-item-shade {
  font-family: var(--sans);
  font-size: 10px;
  color: var(--muted);
  letter-spacing: 0.1em;
  margin-bottom: 4px;
}

.cart-item-shade i {
  font-size: 8px;
  color: var(--bordeaux-l);
  margin-right: 5px;
}

.cart-item-price {
  font-family: var(--sans);
  font-size: 12px;
  font-weight: 500;
  color: var(--bordeaux-l);
  letter-spacing: 0.06em;
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
.qty-controls { display: flex; align-items: center; }

.qty-controls button {
  width: 30px;
  height: 30px;
  background: var(--white);
  border: 1px solid var(--border-s);
  border-radius: 0;
  color: var(--bordeaux);
  font-size: 15px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background .25s, color .25s, border-color .25s;
}

.qty-controls button:hover {
  background: var(--bordeaux);
  color: var(--white);
  border-color: var(--bordeaux);
}

.qty-controls .qty-display {
  width: 40px;
  height: 30px;
  border-top: 1px solid var(--border-s);
  border-bottom: 1px solid var(--border-s);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--sans);
  font-size: 12px;
  font-weight: 500;
  color: var(--bordeaux);
  background: var(--white);
}

/* Total ligne */
.cart-item-line-total {
  font-family: var(--serif);
  font-size: 16px;
  font-weight: 600;
  color: var(--bordeaux);
  margin-left: auto;
  letter-spacing: 0.04em;
}

/* Bouton retirer */
.btn-retirer {
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
  text-decoration: underline;
  text-underline-offset: 3px;
}
.btn-retirer:hover { color: #c00; }

/* ── Récapitulatif ── */
.cart-summary {
  background: var(--glass-s);
  border: 1px solid var(--border-s);
  padding: 32px 26px;
  position: sticky;
  top: 114px;
}

.cart-summary h2 {
  font-family: var(--serif);
  font-size: 18px;
  font-weight: 300;
  font-style: italic;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--bordeaux);
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--border-s);
  position: relative;
}

.cart-summary h2::after {
  content: '';
  position: absolute;
  bottom: -1px;
  left: 0;
  width: 24px;
  height: 1px;
  background: var(--bordeaux);
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  font-family: var(--sans);
  font-size: 11px;
  color: var(--muted);
  letter-spacing: 0.06em;
}

.summary-row.total {
  margin-top: 14px;
  padding-top: 18px;
  border-top: 1px solid var(--border-s);
  font-size: 12px;
  font-weight: 600;
  color: var(--bordeaux);
}

.summary-row.total span:last-child {
  font-family: var(--serif);
  font-size: 22px;
  font-weight: 400;
  color: var(--bordeaux-l);
  letter-spacing: 0.04em;
}

/* Bouton caisse */
.btn-caisse {
  position: relative;
  width: 100%;
  margin-top: 22px;
  padding: 16px 24px;
  background: var(--bordeaux);
  color: var(--white);
  border: 1px solid var(--bordeaux);
  font-family: var(--sans);
  font-size: 9px;
  letter-spacing: 0.3em;
  text-transform: uppercase;
  cursor: pointer;
  overflow: hidden;
  border-radius: 0;
  transition: color .45s, box-shadow .3s;
  isolation: isolate;
}

.btn-caisse::before {
  content: "";
  position: absolute;
  inset: 0;
  background: var(--bordeaux-s);
  transform: translateX(-101%);
  transition: transform .45s cubic-bezier(.77,0,.18,1);
  z-index: -1;
  border-radius: 0;
}

.btn-caisse:hover::before { transform: translateX(0); }
.btn-caisse:hover { box-shadow: 0 8px 28px var(--dark-20); }

/* Continuer */
.continue-link {
  display: block;
  text-align: center;
  margin-top: 14px;
  font-family: var(--sans);
  font-size: 9px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--muted);
  text-decoration: none;
  text-underline-offset: 3px;
  transition: color .25s;
}
.continue-link:hover { color: var(--bordeaux); text-decoration: underline; }

/* Code promo */
.promo-wrap {
  margin-top: 22px;
  padding-top: 20px;
  border-top: 1px solid var(--border-s);
}

.promo-wrap label {
  display: block;
  font-family: var(--sans);
  font-size: 8.5px;
  letter-spacing: 0.28em;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 10px;
}

.promo-row { display: flex; gap: 0; }

.promo-row input {
  flex: 1;
  padding: 10px 14px;
  border: 1px solid var(--border-s);
  border-right: none;
  border-radius: 0;
  font-family: var(--sans);
  font-size: 11px;
  font-weight: 300;
  outline: none;
  background: var(--white);
  color: var(--bordeaux);
  letter-spacing: 0.04em;
  transition: border-color .25s;
}

.promo-row input:focus { border-color: var(--bordeaux); }
.promo-row input::placeholder { color: var(--muted); }

.promo-row button {
  padding: 10px 16px;
  background: var(--bordeaux);
  color: var(--white);
  border: none;
  border-radius: 0;
  font-family: var(--sans);
  font-size: 9px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  cursor: pointer;
  transition: background .3s;
  white-space: nowrap;
}
.promo-row button:hover { background: var(--bordeaux-s); }

/* Badges sécurité */
.secure-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: center;
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid var(--border-s);
}

.secure-badge {
  display: flex;
  align-items: center;
  gap: 5px;
  font-family: var(--sans);
  font-size: 9px;
  color: var(--muted);
  letter-spacing: 0.1em;
}

.secure-badge i { color: var(--bordeaux-l); font-size: 10px; }

/* ── Responsive ── */
@media (max-width: 940px) {
  .page-body { grid-template-columns: 1fr; }
  .cart-summary { position: static; }
}

@media (max-width: 600px) {
  .cart-item { grid-template-columns: 80px 1fr; gap: 14px; padding: 18px 18px; }
  .cart-list-header { padding: 14px 18px; }
  .cart-item-img { width: 80px; height: 80px; }
  .cart-summary { padding: 22px 18px; }
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
      <div id="promoMsg" style="font-size:11px;color:var(--bordeaux-l);margin-top:8px;letter-spacing:.08em;"></div>
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
      msg.style.color = "var(--bordeaux-l)";
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

  const origRender = window.renderCart;
  window.renderCart = function (...args) {
    if (typeof origRender === "function") origRender(...args);
    renderPage();
  };
  window.__sidebarRenderCart = () => {
    if (typeof origRender === "function") origRender();
  };

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