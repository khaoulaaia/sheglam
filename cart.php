<?php
include_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mon Panier - SheGlamour</title>
  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { font-family: 'Poppins', sans-serif; background: #f5f5f5; margin: 0; padding: 0;}
    .cart-page { max-width: 900px; margin: 50px auto; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);}
    h1 { text-align: center; margin-bottom: 30px; }
    .cart-items { display: flex; flex-direction: column; gap: 20px; }
    .cart-item { display: flex; gap: 20px; align-items: center; background: #fafafa; padding: 15px; border-radius: 8px; }
    .cart-item img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
    .cart-item-info { flex: 1; }
    .cart-item-info h4 { margin: 0 0 5px; }
    .cart-item-info p { margin: 0 0 10px; }
    .quantity-controls { display: flex; align-items: center; gap: 10px; }
    .quantity-controls button { padding: 5px 10px; cursor: pointer; border: 1px solid #ccc; background: #fff; border-radius: 5px; }
    .remove-item { background: #e91e63; color: #fff; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; }
    .cart-footer { margin-top: 30px; display: flex; justify-content: space-between; align-items: center; }
    .checkout-btn { padding: 10px 20px; background: #003366; color: #fff; border: none; border-radius: 8px; cursor: pointer; transition: 0.3s; }
    .checkout-btn:hover { background: #002244; }
    .empty-cart { text-align: center; font-size: 1.2rem; color: #555; }
  </style>
</head>
<body>

<div class="cart-page">
  <h1>Mon Panier</h1>
  <div class="cart-items" id="cartItemsPage">
    <p class="empty-cart">Votre panier est vide.</p>
  </div>
  <div class="cart-footer">
    <p id="totalPrice">Total: €0.00</p>
    <button class="checkout-btn">Passer à la caisse</button>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // --- Récupérer le panier depuis localStorage ---
    window.cart = JSON.parse(localStorage.getItem('cart')) || {};

    const cartItemsContainer = document.getElementById('cartItemsPage');
    const totalPriceEl = document.getElementById('totalPrice');

    function saveCart() {
        localStorage.setItem('cart', JSON.stringify(window.cart));
    }

    function renderCartPage() {
        cartItemsContainer.innerHTML = '';
        let total = 0;

        if(Object.keys(window.cart).length === 0) {
            cartItemsContainer.innerHTML = '<p class="empty-cart">Votre panier est vide.</p>';
            totalPriceEl.textContent = "Total: €0.00";
            return;
        }

        for(const key in window.cart) {
            const item = window.cart[key];
            total += item.price * item.quantity;

            const div = document.createElement('div');
            div.classList.add('cart-item');
            div.innerHTML = `
                <img src="${item.image}" alt="${item.name}">
                <div class="cart-item-info">
                    <h4>${item.name}${item.shade ? ' - ' + item.shade : ''}</h4>
                    <p>€${item.price.toFixed(2)}</p>
                    <div class="quantity-controls">
                        <button class="decrease">-</button>
                        <span class="quantity">${item.quantity}</span>
                        <button class="increase">+</button>
                        <button class="remove-item">Supprimer</button>
                    </div>
                </div>
            `;

            // Boutons
            div.querySelector('.increase').addEventListener('click', () => {
                item.quantity += 1;
                saveCart();
                renderCartPage();
            });
            div.querySelector('.decrease').addEventListener('click', () => {
                item.quantity -= 1;
                if(item.quantity <= 0) delete window.cart[key];
                saveCart();
                renderCartPage();
            });
            div.querySelector('.remove-item').addEventListener('click', () => {
                delete window.cart[key];
                saveCart();
                renderCartPage();
            });

            cartItemsContainer.appendChild(div);
        }

        totalPriceEl.textContent = `Total: €${total.toFixed(2)}`;
    }

    renderCartPage();
});
</script>

</body>
</html>
