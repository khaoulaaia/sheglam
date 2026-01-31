<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Wishlist</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
<style>
/* ===== Body & Titles ===== */
body { font-family: 'Roboto', sans-serif; background:#f8f8f8; margin:0; padding:0; color:#111; }
h1 { font-family: 'Playfair Display', serif; text-align:center; margin:30px 0; font-weight:700; letter-spacing:1px; }

/* ===== Container ===== */
#wishlistItems {
  max-width:900px;
  margin:0 auto;
  background:#fff;
  padding:30px;
  box-shadow:0 4px 20px rgba(0,0,0,0.1);
  border-radius:8px;
}

/* ===== Item ===== */
.wishlist-item {
  display:flex;
  align-items:center;
  border-bottom:1px solid #eee;
  padding:20px 0;
}
.wishlist-item:last-child { border-bottom:none; }

.wishlist-item-img {
  width:100px;
  height:100px;
  object-fit:cover;
  border-radius:8px;
  margin-right:20px;
}

.wishlist-item-info { flex:1; }
.wishlist-item-info h4 {
  margin:0 0 5px;
  font-family:'Playfair Display', serif;
  font-weight:500;
  font-size:1.1em;
}
.wishlist-item-price { font-weight:500; margin-bottom:10px; color:#333; }

/* ===== Buttons ===== */
.wishlist-controls button {
  background:#111;
  color:#fff;
  border:none;
  padding:6px 14px;
  margin-right:10px;
  cursor:pointer;
  font-weight:500;
  border-radius:4px;
  transition:0.3s;
}
.wishlist-controls button:hover { background:#444; }

/* ===== Responsive ===== */
@media (max-width:600px) {
  .wishlist-item { flex-direction:column; align-items:flex-start; }
  .wishlist-item-img { margin-bottom:10px; width:80px; height:80px; }
  .wishlist-controls button { margin-bottom:5px; }
}
</style>
</head>
<body>

<h1>Ma Wishlist</h1>
<div id="wishlistItems"></div>

<script>
// ======= Wishlist Logic =======
const wishlist = JSON.parse(localStorage.getItem("wishlist")) || {};
const cart = JSON.parse(localStorage.getItem("cart")) || {};

function saveWishlist() { localStorage.setItem("wishlist", JSON.stringify(wishlist)); }
function saveCart() { localStorage.setItem("cart", JSON.stringify(cart)); }

function renderWishlist() {
  const el = document.getElementById("wishlistItems");
  el.innerHTML = "";

  if (!Object.keys(wishlist).length) {
    el.innerHTML = "<p>Votre wishlist est vide.</p>";
    return;
  }

  Object.entries(wishlist).forEach(([key, item]) => {
    let div = document.createElement("div");
    div.className = "wishlist-item";

    // ID unique pour DOM
    const itemId = item.shade ? `${item.productId}__${item.shade}` : item.productId;
    div.setAttribute("data-product-id", itemId);

    let img = item.image_url.startsWith("http") ? item.image_url : "/sheglam/images/" + item.image_url.split("/").pop();
    div.innerHTML = `
      <img src="${img}" alt="${item.name}" class="wishlist-item-img">
      <div class="wishlist-item-info">
        <h4>${item.name}${item.shade ? " - " + item.shade : ""}</h4>
        <div class="wishlist-item-price">€${item.price.toFixed(2)}</div>
        <div class="wishlist-controls">
          <button class="move-to-cart">Ajouter au panier</button>
          <button class="remove-from-wishlist">Supprimer</button>
        </div>
      </div>
    `;

    // ===== Ajouter au panier =====
    div.querySelector(".move-to-cart").onclick = () => {
      const cartKey = item.shade ? `${item.productId}__${item.shade}` : item.productId;
      const quantityToAdd = item.quantity ? item.quantity : 1;

      if (cart[cartKey]) {
        cart[cartKey].quantity += quantityToAdd;
      } else {
        cart[cartKey] = { ...item, quantity: quantityToAdd };
      }

      saveCart();
      delete wishlist[key];
      saveWishlist();
      renderWishlist();
      alert("Produit ajouté au panier !");
    };

    // ===== Supprimer de la wishlist =====
    div.querySelector(".remove-from-wishlist").onclick = () => {
      delete wishlist[key];
      saveWishlist();
      renderWishlist();
    };

    el.appendChild(div);
  });
}

renderWishlist();
</script>
</body>
</html>
