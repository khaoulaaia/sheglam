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

    /* ── Bannière page ──────────────────────────────────── */
    .page-hero {
      margin-top: 90px;
      background: linear-gradient(135deg, #fff 0%, var(--rose-pale) 60%, #fce4ec 100%);
      border-bottom: 1px solid var(--border);
      padding: 56px 6% 48px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .page-hero::before {
      content: "✦";
      position: absolute;
      top: 20px; left: 6%;
      font-size: 10px;
      color: var(--rose-dark);
      letter-spacing: 6px;
      opacity: .5;
    }
    .page-hero::after {
      content: "✦";
      position: absolute;
      top: 20px; right: 6%;
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

    /* ── Layout deux colonnes ───────────────────────────── */
    .page-body {
      flex: 1;
      max-width: 1140px;
      width: 100%;
      margin: 48px auto 80px;
      padding: 0 24px;
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 40px;
      align-items: flex-start;
    }

    /* ── Vide ───────────────────────────────────────────── */
    .empty-state {
      grid-column: 1 / -1;
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

    /* ── Liste articles ─────────────────────────────────── */
    .cart-list {
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
      font-size: 11px;
      letter-spacing: 0.22em;
      text-transform: uppercase;
      color: var(--gris);
    }

    /* Article */
    .cart-item {
      display: grid;
      grid-template-columns: 100px 1fr;
      gap: 20px;
      padding: 24px 28px;
      border-bottom: 1px solid var(--border);
      transition: background .2s ease;
    }

    .cart-item:last-child { border-bottom: none; }
    .cart-item:hover { background: rgba(242,196,206,.05); }

    .cart-item-img {
      width: 100px;
      height: 100px;
      object-fit: contain;
      background: var(--rose-pale);
      border: 1px solid var(--border);
    }

    .cart-item-info {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .cart-item-info h4 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 17px;
      font-weight: 500;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: var(--noir);
      line-height: 1.3;
    }

    .cart-item-shade {
      font-size: 12px;
      color: var(--gris);
      letter-spacing: 0.08em;
      margin-top: 2px;
    }

    .cart-item-price {
      font-size: 14px;
      font-weight: 500;
      color: var(--rose-dark);
      margin-top: 4px;
    }

    /* Contrôles quantité */
    .quantity-controls {
      display: flex;
      align-items: center;
      gap: 0;
      margin-top: 12px;
    }

    .quantity-controls button {
      width: 30px;
      height: 30px;
      background: #fff;
      border: 1px solid var(--border);
      color: var(--noir);
      font-size: 16px;
      cursor: pointer;
      transition: all .25s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      line-height: 1;
    }

    .quantity-controls button:hover {
      background: var(--noir);
      color: #fff;
      border-color: var(--noir);
    }

    .quantity-controls .quantity {
      width: 40px;
      height: 30px;
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
      border-left: none;
      border-right: none;
      text-align: center;
      font-size: 13px;
      font-weight: 500;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .remove-item {
      margin-left: auto;
      background: none;
      border: none;
      font-size: 11px;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--gris);
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 5px;
      transition: color .25s ease;
    }
    .remove-item:hover { color: #c00; }

    /* Séparation haut/bas de la carte article */
    .cart-item-top {
      margin-bottom: 12px;
    }

    .cart-item-bottom {
      display: flex;
      align-items: center;
      gap: 0;
      flex-wrap: wrap;
      gap: 12px;
    }

    /* Total ligne (prix × qté) */
    .cart-item-line-total {
      font-family: 'Cormorant Garamond', serif;
      font-size: 16px;
      font-weight: 600;
      color: var(--noir);
      min-width: 80px;
      text-align: right;
      margin-left: auto;
    }

    /* ── Récapitulatif commande ─────────────────────────── */
    .cart-summary {
      background: #fff;
      border: 1px solid var(--border);
      padding: 32px 28px;
      position: sticky;
      top: 110px;
    }

    .cart-summary h2 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 20px;
      font-weight: 400;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      margin-bottom: 28px;
      padding-bottom: 16px;
      border-bottom: 1px solid var(--border);
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      font-size: 13px;
      color: var(--gris);
      letter-spacing: 0.04em;
    }

    .summary-row.total {
      margin-top: 16px;
      padding-top: 20px;
      border-top: 1px solid var(--border);
      font-size: 16px;
      font-weight: 600;
      color: var(--noir);
    }

    .summary-row.total span:last-child {
      font-family: 'Cormorant Garamond', serif;
      font-size: 22px;
      color: var(--rose-dark);
    }

    /* Bouton caisse */
    .checkout-btn {
      position: relative;
      width: 100%;
      margin-top: 24px;
      padding: 17px 24px;
      background: var(--noir);
      color: #fff;
      border: none;
      font-family: 'DM Sans', sans-serif;
      font-size: 11px;
      letter-spacing: 0.28em;
      text-transform: uppercase;
      cursor: pointer;
      overflow: hidden;
      transition: color .45s ease;
      isolation: isolate;
    }

    .checkout-btn::before {
      content: "";
      position: absolute;
      inset: 0;
      background: var(--rose-dark);
      transform: translateX(-101%);
      transition: transform .45s cubic-bezier(.77,0,.18,1);
      z-index: -1;
    }

    .checkout-btn:hover::before { transform: translateX(0); }
    .checkout-btn:hover { color: #fff; outline: none; }

    /* Lien continuer */
    .continue-link {
      display: block;
      text-align: center;
      margin-top: 16px;
      font-size: 11px;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      color: var(--gris);
      text-decoration: none;
      text-underline-offset: 3px;
      transition: color .25s ease;
    }
    .continue-link:hover {
      color: var(--noir);
      text-decoration: underline;
    }

    /* ── Responsive ─────────────────────────────────────── */
    @media (max-width: 900px) {
      .page-body {
        grid-template-columns: 1fr;
      }
      .cart-summary {
        position: static;
      }
    }

    @media (max-width: 600px) {
      .cart-item {
        grid-template-columns: 80px 1fr;
        gap: 14px;
        padding: 18px 18px;
      }
      .cart-list-header { padding: 16px 18px; }
      .cart-item-img { width: 80px; height: 80px; }
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
  <span class="count" id="cartCount">— articles</span>
</div>

<!-- Contenu -->
<main class="page-body">

  <!-- Liste articles -->
  <div class="cart-list" id="cartItems"></div>

  <!-- Récapitulatif -->
  <aside class="cart-summary" id="cartSummary" style="display:none;">
    <h2>Récapitulatif</h2>

    <div class="summary-row">
      <span>Sous-total</span>
      <span id="subtotal">0,00 DA</span>
    </div>
    <div class="summary-row">
      <span>Livraison</span>
      <span>Calculée à l'étape suivante</span>
    </div>
    <div class="summary-row total">
      <span>Total</span>
      <span id="cartTotal">0,00 DA</span>
    </div>

    <button class="checkout-btn checkoutBtn">
      <i class="fas fa-lock" style="margin-right:8px;font-size:10px;"></i>Passer à la caisse
    </button>
    <a href="<?= $b ?>/categorie.php?categorie=Tous" class="continue-link">← Continuer mes achats</a>
  </aside>

</main>

<!-- BASE_URL requis par shop.js et checkout.js -->
<script>const BASE_URL = "<?= $b ?>";</script>

<script>
/*
 * IMPORTANT : on initialise la page panier AVANT shop.js
 * pour que notre renderPage() s'exécute en premier.
 * shop.js sera chargé après et gérera uniquement la sidebar + wishlist.
 */
document.addEventListener("DOMContentLoaded", function () {

  const B       = "<?= $b ?>";
  const cartEl  = document.getElementById("cartItems");
  const summary = document.getElementById("cartSummary");
  const countEl = document.getElementById("cartCount");

  /* ── Helpers ─────────────────────────────────────────── */
  const getCart  = () => JSON.parse(localStorage.getItem("cart") || "{}");
  const saveCart = (c) => localStorage.setItem("cart", JSON.stringify(c));

  const fmt = (v) => {
    const n = parseFloat(v);
    return (isNaN(n) ? 0 : n).toFixed(2).replace(".", ",") + " DA";
  };

  const toPrice = (v) => {
    const n = parseFloat(String(v ?? 0).replace(",", "."));
    return isNaN(n) ? 0 : n;
  };

  const normalizeImg = (url) => {
    if (!url) return B + "/images/placeholder.jpg";
    if (url.startsWith("http")) return url;
    return B + "/images/" + url.split("/").pop();
  };

  /* ── Calcul et affichage du total ────────────────────── */
  function updateTotal(cart) {
    let total = 0, count = 0;
    Object.values(cart).forEach(item => {
      const p = toPrice(item.price);
      const q = parseInt(item.quantity) || 0;
      total += p * q;
      count += q;
    });

    document.getElementById("cartTotal").textContent  = fmt(total);
    document.getElementById("subtotal").textContent   = fmt(total);
    countEl.textContent = count ? `${count} article${count > 1 ? "s" : ""}` : "";
  }

  /* ── Rendu complet de la liste ───────────────────────── */
  function renderPage() {
    const cart = getCart();
    const keys = Object.keys(cart).filter(k => cart[k]?.name);

    cartEl.innerHTML = "";

    /* Panier vide */
    if (!keys.length) {
      summary.style.display = "none";
      countEl.textContent   = "";
      document.getElementById("cartTotal").textContent = fmt(0);
      document.getElementById("subtotal").textContent  = fmt(0);
      cartEl.innerHTML = `
        <div class="empty-state">
          <i class="fas fa-shopping-bag"></i>
          <p>Votre panier est vide…</p>
          <a href="${B}/categorie.php?categorie=Tous">Découvrir la boutique</a>
        </div>`;
      return;
    }

    /* Panier rempli */
    summary.style.display = "block";

    /* En-tête */
    const header = document.createElement("div");
    header.className = "cart-list-header";
    header.innerHTML = `
      <span>${keys.length} article${keys.length > 1 ? "s" : ""}</span>
      <span>Total ligne</span>`;
    cartEl.appendChild(header);

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
                   <i class="fas fa-circle" style="font-size:8px;color:var(--rose-dark);margin-right:5px;"></i>
                   ${item.shade}
                 </div>`
              : ""}
            <div class="cart-item-price">${fmt(price)} / unité</div>
          </div>
          <div class="cart-item-bottom">
            <div class="quantity-controls">
              <button class="decrease" aria-label="Diminuer">−</button>
              <span class="quantity">${qty}</span>
              <button class="increase" aria-label="Augmenter">+</button>
            </div>
            <div class="cart-item-line-total">${fmt(price * qty)}</div>
            <button class="remove-item">
              <i class="fas fa-trash-can"></i> Retirer
            </button>
          </div>
        </div>`;

      /* Événements quantité */
      div.querySelector(".increase").addEventListener("click", () => {
        const c = getCart();
        if (!c[key]) return;
        c[key].quantity = (parseInt(c[key].quantity) || 0) + 1;
        saveCart(c);
        renderPage();
      });

      div.querySelector(".decrease").addEventListener("click", () => {
        const c = getCart();
        if (!c[key]) return;
        c[key].quantity = (parseInt(c[key].quantity) || 1) - 1;
        if (c[key].quantity <= 0) delete c[key];
        saveCart(c);
        renderPage();
      });

      div.querySelector(".remove-item").addEventListener("click", () => {
        const c = getCart();
        delete c[key];
        saveCart(c);
        renderPage();
      });

      cartEl.appendChild(div);
    });

    /* Calcul total sur le même objet cart (pas de re-lecture) */
    updateTotal(cart);
  }

  /* ── Bouton caisse ───────────────────────────────────── */
  document.querySelector(".checkoutBtn")?.addEventListener("click", () => {
    const cart = getCart();
    if (!Object.keys(cart).length) {
      alert("Votre panier est vide.");
      return;
    }
    /* checkout.js est chargé après → SheGlamCheckout sera dispo */
    if (typeof window.SheGlamCheckout?.open === "function") {
      window.SheGlamCheckout.open();
    } else if (typeof window.openCheckout === "function") {
      window.openCheckout();
    }
  });

  /* ── Patch : empêche shop.js d'écraser notre DOM ─────── */
  const _origRenderCart = window.renderCart;
  window.renderCart = function () {
    /* On laisse shop.js mettre à jour la sidebar panier (cartItems dans sidebar),
       mais on re-rend notre page pour rester en sync */
    if (typeof _origRenderCart === "function") _origRenderCart();
    renderPage(); /* re-sync la page */
  };

  /* ── Initialisation ──────────────────────────────────── */
  renderPage();
  document.body.style.visibility = "visible";
});
</script>

<!-- shop.js après notre init → gère sidebar + wishlist + modal teintes -->
<script src="<?= $b ?>/js/shop.js?v=<?= time() ?>"></script>
<script src="<?= $b ?>/js/checkout.js?v=<?= time() ?>"></script>

<?php include 'includes/footer.php'; ?>
</body>
</html>