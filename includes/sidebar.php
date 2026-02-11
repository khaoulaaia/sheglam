<!-- Sidebar Panier -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>Mon Panier</h3>
        <span id="closeSidebar">&times;</span>
    </div>
    <div class="sidebar-content" id="cartItems">
        <p>Votre panier est vide.</p>
    </div>
    <div class="sidebar-footer">
      <div class="cart-total">
    <span>Total</span>
    <strong id="cartTotal">0.00DA</strong>
  </div>
        <button class="btn checkout-btn">Passer à la caisse</button>
    </div>
</div>

<!-- Sidebar Wishlist -->
<div class="sidebar" id="wishlistSidebar">
    <div class="sidebar-header">
        <h3>Ma Liste de souhaits</h3>
        <span id="closeWishlist">&times;</span>
    </div>
    <div class="sidebar-content" id="wishlistItems">
        <p>Votre liste de souhaits est vide.</p>
    </div>
</div>

<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const sidebar = document.getElementById('sidebar');
  const wishlistSidebar = document.getElementById('wishlistSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const closeSidebarBtn = document.getElementById('closeSidebar');
  const closeWishlistBtn = document.getElementById('closeWishlist');
  const cartItems = document.getElementById('cartItems');
  const wishlistItems = document.getElementById('wishlistItems');

  // --- Charger panier et wishlist ---
  window.cart = JSON.parse(localStorage.getItem('cart')) || {};
  window.wishlist = JSON.parse(localStorage.getItem('wishlist')) || {};

  function saveCart() { localStorage.setItem('cart', JSON.stringify(window.cart)); }
  function saveWishlist() { localStorage.setItem('wishlist', JSON.stringify(window.wishlist)); }

  // --- Ouvrir / fermer sidebars ---
  window.openCart = () => { sidebar.classList.add('active'); overlay.classList.add('active'); };
  window.openWishlist = () => { wishlistSidebar.classList.add('active'); overlay.classList.add('active'); };

  closeSidebarBtn.addEventListener('click', () => {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
  });
  closeWishlistBtn.addEventListener('click', () => {
    wishlistSidebar.classList.remove('active');
    overlay.classList.remove('active');
  });
  overlay.addEventListener('click', () => {
    sidebar.classList.remove('active');
    wishlistSidebar.classList.remove('active');
    overlay.classList.remove('active');
  });

  // --- Rendu du panier ---
  window.renderCart = () => {
    if (!window.cart || Object.keys(window.cart).length === 0) {
      cartItems.innerHTML = '<p>Votre panier est vide.</p>';
      return;
    }

    cartItems.innerHTML = '';

    for (const key in window.cart) {
      const item = window.cart[key];
      if (!item || !item.name) continue;

      const imageSrc = item.image_url || '/images/placeholder.jpg';
      const price = parseFloat(item.price) || 0;
      const qty = parseInt(item.quantity) || 1;

      const itemHTML = document.createElement('div');
      itemHTML.classList.add('cart-item');
      itemHTML.innerHTML = `
        <img src="${imageSrc}" alt="${item.name}" class="cart-item-img">
        <div class="cart-item-info">
          <h4>${item.name}${item.shade ? ' - ' + item.shade : ''}</h4>
          <p>${price.toFixed(2)}DA</p>
          <div class="quantity-controls">
            <button class="decrease">-</button>
            <span class="quantity">${qty}</span>
            <button class="increase">+</button>
            <button class="remove-item">x</button>
          </div>
        </div>
      `;

      itemHTML.querySelector('.increase').addEventListener('click', () => {
        item.quantity += 1;
        saveCart(); renderCart();
      });

      itemHTML.querySelector('.decrease').addEventListener('click', () => {
        item.quantity -= 1;
        if (item.quantity <= 0) delete window.cart[key];
        saveCart(); renderCart();
      });

      itemHTML.querySelector('.remove-item').addEventListener('click', () => {
        delete window.cart[key];
        saveCart(); renderCart();
      });

      cartItems.appendChild(itemHTML);
    }
  };

  // --- Rendu de la wishlist ---
  window.renderWishlist = () => {
    if (!window.wishlist || Object.keys(window.wishlist).length === 0) {
      wishlistItems.innerHTML = '<p>Votre liste de souhaits est vide.</p>';
      return;
    }

    wishlistItems.innerHTML = '';
    for (const key in window.wishlist) {
      const item = window.wishlist[key];
      if (!item || !item.name) continue;

      const imageSrc = item.image_url || '/images/placeholder.jpg';
      const price = parseFloat(item.price) || 0;

      const itemHTML = document.createElement('div');
      itemHTML.classList.add('wishlist-item');
      itemHTML.innerHTML = `
  <img src="${imageSrc}" alt="${item.name}" class="wishlist-item-img">
  <div class="wishlist-item-info">
    <h4>${item.name}</h4>
    <div class="wishlist-meta">
      <p>${price.toFixed(2)}DA</p>
      <button class="remove-wishlist">x</button>
    </div>
  </div>
`;

      itemHTML.querySelector('.remove-wishlist').addEventListener('click', () => {
        delete window.wishlist[key];
        saveWishlist(); renderWishlist();
      });

      wishlistItems.appendChild(itemHTML);
    }
  };

  // --- Ajouter au panier ---
  document.querySelectorAll('.add-to-cart').forEach(btn => {
    btn.addEventListener('click', () => {
      const name = btn.dataset.name;
      const price = parseFloat(btn.dataset.price?.replace(',', '.')) || 0;
      const image_url = btn.dataset.image_url || '/images/placeholder.jpg';
      const shade = btn.dataset.shade || null;
      const key = shade ? `${name} - ${shade}` : name;

      if (window.cart[key]) window.cart[key].quantity += 1;
      else window.cart[key] = { name, price, image_url, quantity: 1, shade };

      saveCart();
      renderCart();
      openCart();
    });
  });

  // --- Ajouter à la wishlist ---
  document.querySelectorAll('.add-to-wishlist').forEach(btn => {
    btn.addEventListener('click', () => {
      const name = btn.dataset.name;
      const price = parseFloat(btn.dataset.price?.replace(',', '.')) || 0;
      const image_url = btn.dataset.image_url || '/images/placeholder.jpg';

      if (!window.wishlist[name]) window.wishlist[name] = { name, price, image_url };
      saveWishlist();
      renderWishlist();
      openWishlist();
    });
  });

  // --- Initialisation ---
  renderCart();
  renderWishlist();
});
</script>
<style>
/* =====================================================
   SIDEBAR – LUXE MINIMAL
===================================================== */

.sidebar {
  position: fixed;
  top: 0;
  right: -420px;
  width: 400px;
  height: 100vh;
  background: #fff;
  z-index: 5000;
  display: flex;
  flex-direction: column;
  transition: right .45s cubic-bezier(.4,0,.2,1);
  box-shadow: -12px 0 40px rgba(0,0,0,.18);
}

.sidebar.active {
  right: 0;
}

/* =====================================================
   HEADER
===================================================== */

.sidebar-header {
  padding: 28px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(0,0,0,.08);
}

.sidebar-header h3 {
  font-family: "Didot", "Playfair Display", serif;
  font-size: 18px;
  letter-spacing: .12em;
  text-transform: uppercase;
  margin: 0;
}

#closeSidebar,
#closeWishlist {
  font-size: 26px;
  font-weight: 300;
  cursor: pointer;
  opacity: .6;
  transition: opacity .25s ease;
}

#closeSidebar:hover,
#closeWishlist:hover {
  opacity: 1;
}

/* =====================================================
   CONTENT
===================================================== */

.sidebar-content {
  flex: 1;
  padding: 24px;
  overflow-y: auto;
}

.sidebar-content p {
  font-size: 14px;
  color: #777;
  text-align: center;
  margin-top: 40px;
}

/* =====================================================
   CART / WISHLIST ITEM
===================================================== */

.cart-item,
.wishlist-item {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
}
.cart-item-img,
.wishlist-item-img {
  width: 72px;
  height: 72px;
  object-fit: contain;   /* 🔥 clé */
  background: #ffff;
  border-radius: 6px;
}


/* INFO */
.cart-item-info,
.wishlist-item-info {
  flex: 1;
}

.cart-item-info h4,
.wishlist-item-info h4 {
  font-size: 13px;
  font-weight: 500;
  letter-spacing: .05em;
  text-transform: uppercase;
  margin-bottom: 6px;
}

.cart-item-info p,
.wishlist-item-info p {
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 10px;
}

/* =====================================================
   QUANTITY CONTROLS
===================================================== */

.quantity-controls {
  display: flex;
  align-items: center;
  gap: 10px;
}

.quantity-controls button {
  width: 26px;
  height: 26px;
  background: none;
  border: 1px solid rgba(0,0,0,.25);
  cursor: pointer;
  font-size: 14px;
  transition: all .25s ease;
}

.quantity-controls button:hover {
  background: #111;
  color: #fff;
}

.quantity-controls .quantity {
  font-size: 13px;
  min-width: 18px;
  text-align: center;
}

/* REMOVE */
.remove-item,
.remove-wishlist {
  margin-left: auto;
  background: none;
  border: none;
  font-size: 11px;
  letter-spacing: .08em;
  text-transform: uppercase;
  cursor: pointer;
  opacity: .6;
  transition: opacity .25s ease;
}

.remove-item:hover,
.remove-wishlist:hover {
  opacity: 1;
}

/* =====================================================
   FOOTER
===================================================== */

.sidebar-footer {
  padding: 24px;
  border-top: 1px solid rgba(0,0,0,.08);
}

.checkout-btn {
  width: 100%;
  padding: 14px;
  background: #111;
  color: #fff;
  border: none;
  font-size: 13px;
  letter-spacing: .18em;
  text-transform: uppercase;
  cursor: pointer;
  transition: background .3s ease;
}

.checkout-btn:hover {
  background: #000;
}

/* =====================================================
   OVERLAY
===================================================== */

.sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.55);
  opacity: 0;
  pointer-events: none;
  transition: opacity .35s ease;
  z-index: 4800;
}

.sidebar-overlay.active {
  opacity: 1;
  pointer-events: auto;
}

/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 768px) {
  .sidebar {
    width: 100%;
    right: -100%;
  }
}
.cart-item-price {
  font-size: 13px;
  font-weight: 600;
    color: #777;
  margin-bottom: 10px;
  letter-spacing: .02em;
}
.cart-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  font-weight: 600;
  padding: 12px 0;
  border-top: 1px solid rgba(0,0,0,.1);
  margin-bottom: 12px;
}

.cart-total span {
  letter-spacing: .08em;
  text-transform: uppercase;
  font-size: 12px;
}

.cart-total strong {
  font-size: 16px;
}

/* Wishlist meta row */
.wishlist-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* Price */
.wishlist-meta p {
  margin: 0;
  font-size: 13px;
  font-weight: 600;
}

/* Remove button */
.remove-wishlist {
  background: none;
  border: none;
  font-size: 11px;
  letter-spacing: .08em;
  text-transform: uppercase;
  cursor: pointer;
  opacity: .6;
  transition: opacity .25s ease;
}

.remove-wishlist:hover {
  opacity: 1;
}

</style>
