<?php include_once 'includes/sidebar.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ma Wishlist - SheGlamour</title>
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

<section class="wishlist-page">
  <h1>💖 Ma Wishlist</h1>
  <div id="wishlistContainer"></div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const wishlistContainer = document.getElementById("wishlistContainer");
  const wishlist = JSON.parse(localStorage.getItem("wishlist")) || {};

  function renderWishlist() {
    wishlistContainer.innerHTML = "";
    if (!Object.keys(wishlist).length) {
      wishlistContainer.innerHTML = "<p>Votre wishlist est vide.</p>";
      return;
    }

    for (const key in wishlist) {
      const item = wishlist[key];
      const itemEl = document.createElement("div");
      itemEl.className = "wishlist-item-page";
      itemEl.innerHTML = `
        <img src="${item.image_url}" alt="${item.name}" class="wishlist-img">
        <div class="wishlist-info">
          <h4>${item.name}</h4>
          <p>€${item.price.toFixed(2)}</p>
          <button class="add-to-cart-btn">Ajouter au panier</button>
          <button class="remove-btn">Supprimer</button>
        </div>
      `;

      itemEl.querySelector(".remove-btn").addEventListener("click", () => {
        delete wishlist[key];
        save();
      });
      itemEl.querySelector(".add-to-cart-btn").addEventListener("click", () => {
        const cart = JSON.parse(localStorage.getItem("cart")) || {};
        if (!cart[item.name]) cart[item.name] = { ...item, quantity: 1 };
        else cart[item.name].quantity++;
        localStorage.setItem("cart", JSON.stringify(cart));
        alert("Produit ajouté au panier !");
      });

      wishlistContainer.appendChild(itemEl);
    }
  }

  function save() {
    localStorage.setItem("wishlist", JSON.stringify(wishlist));
    renderWishlist();
  }

  renderWishlist();
});
</script>

</body>
</html>
