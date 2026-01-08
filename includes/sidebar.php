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
  console.log("✅ Sidebar panier/wishlist chargée");

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
          <p>€${price.toFixed(2)}</p>
          <div class="quantity-controls">
            <button class="decrease">-</button>
            <span class="quantity">${qty}</span>
            <button class="increase">+</button>
            <button class="remove-item">Supprimer</button>
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
          <p>€${price.toFixed(2)}</p>
          <button class="remove-wishlist">Supprimer</button>
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
/* ==================== STYLE SIDEBAR ==================== */
.sidebar {
    position: fixed;
    right: -100%;
    top: 0;
    width: 350px;
    height: 100%;
    background: #fff;
    transition: 0.3s;
    z-index: 1000;
    box-shadow: -2px 0 5px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
}
.sidebar.active { right: 0; }

.sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #ddd;
}
.sidebar-content {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
}
.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid #ddd;
}
.sidebar-overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    opacity: 0;
    pointer-events: none;
    transition: 0.3s;
    z-index: 999;
}
.sidebar-overlay.active { opacity: 1; pointer-events: all; }

#closeSidebar, #closeWishlist { cursor: pointer; font-size: 1.5rem; }

.cart-item, .wishlist-item {
    display: flex;
    margin-bottom: 1rem;
    gap: 0.5rem;
}
.cart-item-img, .wishlist-item-img {
    width: 70px;
    height: 70px;
    object-fit: cover;
}
.cart-item-info, .wishlist-item-info { flex: 1; }
.quantity-controls { display: flex; align-items: center; gap: 0.5rem; }
.quantity-controls button { padding: 0.3rem 0.6rem; }
</style>
