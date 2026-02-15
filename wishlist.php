<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Wishlist</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">

<style>
/* Corps de page */
body {
  font-family: 'Playfair Display', serif; /* Typo plus chic pour Dior */
  background: #fdfcf9; /* Beige très clair, chic et neutre */
  color: #1a1a1a;
  margin: 0;
  padding: 0;
}

/* Titre principal */
h1 {
  font-family: 'Playfair Display', serif;
  font-weight: 700;
  font-size: 2.5em;
  text-align: center;
  margin: 50px 0;
  letter-spacing: 1px;
  color: #111;
}

/* Container wishlist */
#wishlistItems {
  max-width: 900px;
  margin: 0 auto;      /* centre horizontalement */
  background: #fff;
  padding: 40px 20px;  /* padding horizontal réduit sur mobile */
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.08);
  box-sizing: border-box; /* évite que padding dépasse du container */
}


/* Item */
.wishlist-item {
  display: flex;
  align-items: center;
  border-bottom: 1px solid #eee;
  padding: 25px 0;
  transition: transform 0.3s;
}

.wishlist-item:hover {
  transform: translateY(-2px);
}

/* Image */
.wishlist-item-img {
  width: 120px;
  height: 120px;
  object-fit: cover;
  border-radius: 10px;
  margin-right: 25px;
}

/* Infos */
.wishlist-item-info h4 {
  font-family: 'Playfair Display', serif;
  font-weight: 500;
  font-size: 1.2em;
  margin: 0 0 5px;
  color: #111;
}

.wishlist-item-price {
  font-weight: 400;
  color: #555;
  margin-bottom: 15px;
}

/* Boutons */
.wishlist-controls button {
  background: #111;
  color: #fff;
  border: none;
  padding: 8px 18px;
  margin-right: 10px;
  cursor: pointer;
  font-weight: 500;
  border-radius: none;
  text-transform: uppercase;
  font-size: 0.9em;
  letter-spacing: 0.5px;
  transition: all 0.3s;
}

.wishlist-controls button:hover {
  background: #333;
  transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 600px) {
  .wishlist-item { flex-direction: column; align-items: flex-start; }
  .wishlist-item-img { width: 100px; height: 100px; margin-bottom: 10px; }
  .wishlist-controls button { margin-bottom: 5px; }
}@media (max-width: 768px) {
  .wishlist-item {
    flex-direction: column;       /* éléments empilés */
    align-items: center;          /* centre horizontalement */
    text-align: center;           /* texte centré */
    padding: 20px 0;
  }

  .wishlist-item-img {
    margin-bottom: 15px;
    width: 100px;
    height: 100px;
  }

  .wishlist-item-info {
    width: 100%;                  /* prend toute la largeur */
    display: flex;
    flex-direction: column;
    align-items: center;          /* centre les boutons et texte */
  }

  .wishlist-controls {
    display: flex;
    flex-direction: column;       /* boutons empilés */
    align-items: center;
    width: 100%;
  }

  .wishlist-controls button {
    width: 80%;                   /* boutons plus larges et centrés */
    margin: 5px 0;
  }
}
@media (max-width: 480px) {
  .wishlist-item-img {
    width: 90px;
    height: 90px;
  }

  .wishlist-controls button {
    width: 100%;
  }
}


</style>
</head>
<body>

<h1>Ma Wishlist</h1>
<div id="wishlistItems"></div>

<script>
document.addEventListener("DOMContentLoaded", function () {

  const wishlist = JSON.parse(localStorage.getItem("wishlist")) || {};
  const cart = JSON.parse(localStorage.getItem("cart")) || {};

  const saveWishlist = () => localStorage.setItem("wishlist", JSON.stringify(wishlist));
  const saveCart = () => localStorage.setItem("cart", JSON.stringify(cart));

  const buildKey = (productId, shade) =>
    shade ? `${productId}__${shade}` : `${productId}`;

  function renderWishlist() {
    const el = document.getElementById("wishlistItems");
    el.innerHTML = "";

    if (!Object.keys(wishlist).length) {
      el.innerHTML = "<p>Votre wishlist est vide.</p>";
      return;
    }

    Object.entries(wishlist).forEach(([key, item]) => {

      const div = document.createElement("div");
      div.className = "wishlist-item";

      const img = item.image_url.startsWith("http")
        ? item.image_url
        : "/sheglam/images/" + item.image_url.split("/").pop();

      let buttonHTML = "";

      if (item.hasShades) {
  buttonHTML = `
    <button class="choose-shade-btn"
        data-from-wishlist="1"
        data-wishlist-key="${key}"
        data-product-id="${item.productId}"
        data-name="${item.name}"
        data-price="${item.price}"
        data-image_url="${item.image_url}">
  Choisir une teinte
</button>

  `;
} else {
  buttonHTML = `<button class="move-to-cart">Ajouter au panier</button>`;
}


      div.innerHTML = `
        <img src="${img}" class="wishlist-item-img">
        <div class="wishlist-item-info">
          <h4>${item.name}</h4>
          <div class="wishlist-item-price">${parseFloat(item.price).toFixed(2)} DA</div>
          <div class="wishlist-controls">
            ${buttonHTML}
            <button class="remove-from-wishlist">Supprimer</button>
          </div>
        </div>
      `;

      // ===== Ajouter au panier (sans teintes) =====
      const moveBtn = div.querySelector(".move-to-cart");
      if (moveBtn) {
        moveBtn.addEventListener("click", function () {
          const cartKey = buildKey(item.productId, item.shade);
          const quantity = item.quantity || 1;

          if (cart[cartKey]) cart[cartKey].quantity += quantity;
          else cart[cartKey] = {
            productId: item.productId,
            name: item.name,
            price: item.price,
            image_url: item.image_url,
            shade: item.shade || null,
            quantity
          };

          saveCart();
          delete wishlist[key];
          saveWishlist();
          renderWishlist();
          alert("Produit ajouté au panier !");
        });
      }

      // ===== Choisir une teinte =====
      const shadeBtn = div.querySelector(".choose-shade-btn");
      if (shadeBtn) {
        shadeBtn.addEventListener("click", function () {

          if (typeof window.openShadeModal === "function") {
            window.openShadeModal(shadeBtn);
          } else {
            console.error("openShadeModal non disponible");
          }

        });
      }

      // ===== Supprimer =====
      const removeBtn = div.querySelector(".remove-from-wishlist");
      if (removeBtn) {
        removeBtn.addEventListener("click", function () {
          delete wishlist[key];
          saveWishlist();
          renderWishlist();
        });
      }

      el.appendChild(div);
    });
  }

  renderWishlist();
});

</script>
<?php include 'includes/product_modal.php'; ?>
<script src="/sheglam/js/shop.js?v=<?= time(); ?>"></script>

</body>
</html>
