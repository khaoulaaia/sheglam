<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Panier</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
<style>

/* ======== Base ======== */
body {
  font-family: 'Roboto', sans-serif;
  background: #f9f8f7; /* subtil beige */
  color: #1a1a1a;
  margin: 0;
  padding: 0;
}

h1 {
  font-family: 'Playfair Display', serif;
  font-weight: 500;
  text-align: center;
  margin: 40px 0 20px 0;
  font-size: 2em;
  letter-spacing: 0.5px;
  color: #111;
}

/* ======== Container ======== */
#cartItems {
  max-width: 850px;
  margin: 0 auto;
  background: #fff;
  padding: 25px 30px;
  border-radius: 8px;
  box-sizing: border-box;
}

/* ======== Items ======== */
.cart-item {
  display: flex;
  align-items: center;
  border-bottom: 1px solid #eee;
  padding: 15px 0;
}

.cart-item:last-child {
  border-bottom: none;
}

.cart-item-img {
  width: 90px;
  height: 90px;
  object-fit: cover;
  border-radius: 6px;
  margin-right: 20px;
}

.cart-item-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.cart-item-info h4 {
  margin: 0 0 5px;
  font-family: 'Playfair Display', serif;
  font-weight: 400;
  font-size: 1.1em;
  color: #111;
}

.cart-item-price {
  font-weight: 300;
  color: #555;
  margin-bottom: 10px;
}

/* ======== Contrôles de quantité ======== */
.quantity-controls {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-wrap: wrap;
}

.quantity-controls button {
  background: transparent;
  border: 1px solid #111;
  color: #111;
  font-weight: 400;
  padding: 4px 10px;
  border-radius: none;
  cursor: pointer;
  font-size: 0.9em;
  transition: all 0.2s;
}

.quantity-controls button:hover {
  background: #111;
  color: #fff;
}

.quantity-controls span.quantity {
  font-weight: 400;
  font-size: 1em;
  min-width: 20px;
  text-align: center;
}

/* ======== Total ======== */
#cartTotal {
  text-align: right;
  font-size: 1.5em;
  font-weight: 500;
  margin-top: 25px;
  font-family: 'Playfair Display', serif;
  color: #111;
}

/* ======== Responsive ======== */
@media (max-width: 768px) {
  #cartItems {
    padding: 20px 15px;
  }

  .cart-item {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .cart-item-img {
    width: 80px;
    height: 80px;
    margin-bottom: 10px;
  }

  .cart-item-info {
    width: 100%;
    align-items: center;
  }

  .quantity-controls {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  h1 { font-size: 1.8em; margin: 25px 0 15px 0; }
  .cart-item-img { width: 70px; height: 70px; }
  .quantity-controls button { padding: 3px 8px; font-size: 0.85em; }
}
</style>
</head>
<body>
<h1>Votre Panier</h1>
<div id="cartItems"></div>
<div id="cartTotal">0.00DA</div>

<script>
// ======= Panier =======
const cart = JSON.parse(localStorage.getItem("cart")) || {};

function saveCart() { localStorage.setItem("cart", JSON.stringify(cart)); }

function updateCartTotal() {
    let total = 0;
    Object.values(cart).forEach(item => total += item.price * item.quantity);
document.getElementById("cartTotal").textContent = `${total.toFixed(2)} DA`;
}

function renderCart() {
    const cartEl = document.getElementById("cartItems");
    cartEl.innerHTML = "";

    if(!Object.keys(cart).length){ cartEl.innerHTML="<p>Votre panier est vide.</p>"; return; }

    Object.entries(cart).forEach(([key,item])=>{
        let div = document.createElement("div");
        div.className="cart-item";
        let img = item.image_url.startsWith("http") ? item.image_url : "/sheglam/images/"+item.image_url.split("/").pop();
        div.innerHTML=`
            <img src="${img}" alt="${item.name}" class="cart-item-img">
            <div class="cart-item-info">
                <h4>${item.name}${item.shade? " - "+item.shade : ""}</h4>
                <div class="cart-item-price">${item.price.toFixed(2)} DA</div>
                <div class="quantity-controls">
                    <button class="decrease">-</button>
                    <span class="quantity">${item.quantity}</span>
                    <button class="increase">+</button>
                    <button class="remove-item">x</button>
                </div>
            </div>
        `;
        div.querySelector(".increase").onclick=()=>{ item.quantity++; saveCart(); renderCart(); updateCartTotal(); };
        div.querySelector(".decrease").onclick=()=>{ item.quantity--; if(item.quantity<=0) delete cart[key]; saveCart(); renderCart(); updateCartTotal(); };
        div.querySelector(".remove-item").onclick=()=>{ delete cart[key]; saveCart(); renderCart(); updateCartTotal(); };
        cartEl.appendChild(div);
    });
    updateCartTotal();
}

renderCart();
</script>
</body>
</html>
