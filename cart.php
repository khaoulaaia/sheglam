<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Panier</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
<style>
body { font-family: 'Roboto', sans-serif; background:#f8f8f8; margin:0; padding:0; color:#111; }
h1 { font-family: 'Playfair Display', serif; text-align:center; margin:30px 0; font-weight:700; letter-spacing:1px; }
#cartItems { max-width:900px; margin:0 auto; background:#fff; padding:30px; box-shadow:0 4px 20px rgba(0,0,0,0.1); border-radius:8px; }
.cart-item { display:flex; align-items:center; border-bottom:1px solid #eee; padding:20px 0; }
.cart-item:last-child { border-bottom:none; }
.cart-item-img { width:100px; height:100px; object-fit:cover; border-radius:8px; margin-right:20px; }
.cart-item-info { flex:1; }
.cart-item-info h4 { margin:0 0 5px; font-family:'Playfair Display', serif; font-weight:500; font-size:1.1em; }
.cart-item-price { font-weight:500; margin-bottom:10px; color:#333; }
.quantity-controls button { background:#111; color:#fff; border:none; padding:5px 12px; margin-right:5px; cursor:pointer; font-weight:500; border-radius:4px; transition:0.3s; }
.quantity-controls button:hover { background:#444; }
#cartTotal { text-align:right; font-size:1.5em; font-weight:700; margin-top:20px; font-family:'Playfair Display', serif; }
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
