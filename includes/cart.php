<?php include_once 'includes/sidebar.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Votre Panier - SheGlamour</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="index.css">
  <script src="/sheglam/js/sidebar.js" defer></script>
</head>
<body>

<header class="header">
  <div class="logo"><a href="/">SheGlamour</a></div>
  <nav>
    <a href="/">Accueil</a>
    <a href="/sheglam/products">Boutique</a>
  </nav>
</header>

<section class="cart-page">
  <h1>🛍️ Votre Panier</h1>
  <div id="cartContainer"></div>
  <div class="cart-total">
    <h3>Total : <span id="cartTotal">€0.00</span></h3>
    <button id="checkoutBtn" class="btn">Passer la commande</button>
  </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const cartContainer = document.getElementById("cartContainer");
  const cartTotalEl = document.getElementById("cartTotal");
  const cart = JSON.parse(localStorage.getItem("cart")) || {};

  function renderCart() {
    cartContainer.innerHTML = "";
    let total = 0;
    if (!Object.keys(cart).length) {
      cartContainer.innerHTML = "<p>Votre panier est vide.</p>";
      cartTotalEl.textContent = "€0.00";
      return;
    }

    for (const key in cart) {
      const item = cart[key];
      const itemEl = document.createElement("div");
      itemEl.className = "cart-item-page";
      total += item.price * item.quantity;
      itemEl.innerHTML = `
        <img src="${item.image_url}" alt="${item.name}" class="cart-img">
        <div class="cart-info">
          <h4>${item.name}${item.shade ? ' - ' + item.shade : ''}</h4>
          <p>Prix : €${item.price.toFixed(2)}</p>
          <div class="qty">
            <button class="minus">-</button>
            <span>${item.quantity}</span>
            <button class="plus">+</button>
          </div>
          <button class="remove">Supprimer</button>
        </div>
      `;

      itemEl.querySelector(".minus").addEventListener("click", () => {
        item.quantity--;
        if (item.quantity <= 0) delete cart[key];
        save();
      });
      itemEl.querySelector(".plus").addEventListener("click", () => {
        item.quantity++;
        save();
      });
      itemEl.querySelector(".remove").addEventListener("click", () => {
        delete cart[key];
        save();
      });

      cartContainer.appendChild(itemEl);
    }
    cartTotalEl.textContent = `€${total.toFixed(2)}`;
  }

  function save() {
    localStorage.setItem("cart", JSON.stringify(cart));
    renderCart();
  }

  renderCart();
});
</script>

</body>
</html>
