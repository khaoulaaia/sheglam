document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById('sidebar');
  const wishlistSidebar = document.getElementById('wishlistSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const closeSidebarBtn = document.getElementById('closeSidebar');
  const closeWishlistBtn = document.getElementById('closeWishlist');
  const cartItems = document.getElementById('cartItems');
  const wishlistItems = document.getElementById('wishlistItems');

  // --- Charger panier et wishlist depuis localStorage ---
  window.cart = JSON.parse(localStorage.getItem('cart')) || {};
  window.wishlist = JSON.parse(localStorage.getItem('wishlist')) || {};

  function saveCart() { localStorage.setItem('cart', JSON.stringify(window.cart)); }
  function saveWishlist() { localStorage.setItem('wishlist', JSON.stringify(window.wishlist)); }

  // --- Fonctions d'ouverture ---
  window.openCart = () => { sidebar.classList.add('active'); overlay.classList.add('active'); };
  window.openWishlist = () => { wishlistSidebar.classList.add('active'); overlay.classList.add('active'); };

  // --- Fermeture sidebars ---
  closeSidebarBtn.addEventListener('click', () => { sidebar.classList.remove('active'); overlay.classList.remove('active'); });
  closeWishlistBtn.addEventListener('click', () => { wishlistSidebar.classList.remove('active'); overlay.classList.remove('active'); });
  overlay.addEventListener('click', () => { sidebar.classList.remove('active'); wishlistSidebar.classList.remove('active'); overlay.classList.remove('active'); });

  // --- Rendu panier ---
  window.renderCart = () => {
    cartItems.innerHTML = '';
    if (!Object.keys(window.cart).length) {
      cartItems.innerHTML = '<p>Votre panier est vide.</p>';
      return;
    }
    for (const key in window.cart) {
  const item = window.cart[key];
  if (!item || !item.image_url) {
    console.warn("⚠️ Élément panier invalide supprimé :", key, item);
    delete window.cart[key]; // on nettoie les entrées corrompues
    continue; // on passe à l'item suivant
  }

  const div = document.createElement('div');
  div.className = 'cart-item';
  div.innerHTML = `
    <img src="${item.image_url || '/images/placeholder.jpg'}" alt="${item.name}" class="cart-item-img">
    <div class="cart-item-info">
      <h4>${item.name}${item.shade ? ' - ' + item.shade : ''}</h4>
      <p>€${parseFloat(item.price).toFixed(2)}</p>
      <div class="quantity-controls">
        <button class="decrease">-</button>
        <span class="quantity">${item.quantity}</span>
        <button class="increase">+</button>
        <button class="remove-item">Supprimer</button>
      </div>
    </div>
  `;

      div.querySelector('.increase').addEventListener('click', () => { item.quantity += 1; saveCart(); renderCart(); });
      div.querySelector('.decrease').addEventListener('click', () => { item.quantity -= 1; if(item.quantity<=0) delete window.cart[key]; saveCart(); renderCart(); });
      div.querySelector('.remove-item').addEventListener('click', () => { delete window.cart[key]; saveCart(); renderCart(); });
      cartItems.appendChild(div);
    }
  };

  // --- Rendu wishlist ---
  window.renderWishlist = () => {
    wishlistItems.innerHTML = '';
    if (!Object.keys(window.wishlist).length) {
      wishlistItems.innerHTML = '<p>Votre liste de souhaits est vide.</p>';
      return;
    }
    for (const key in window.wishlist) {
      const item = window.wishlist[key];
      const div = document.createElement('div');
      div.className = 'wishlist-item';
      div.innerHTML = `
        <img src="${item.image_url}" alt="${item.name}" class="wishlist-item-img">
        <div class="wishlist-item-info">
          <h4>${item.name}</h4>
          <p>€${parseFloat(item.price).toFixed(2)}</p>
          <button class="remove-wishlist">Supprimer</button>
        </div>
      `;
      div.querySelector('.remove-wishlist').addEventListener('click', () => { delete window.wishlist[key]; saveWishlist(); renderWishlist(); });
      wishlistItems.appendChild(div);
    }
  };

  // --- Ajouter au panier (depuis tous les boutons) ---
  document.body.addEventListener('click', e => {
    const btn = e.target.closest('.add-to-cart');
    if (!btn) return;

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

  // --- Ajouter à la wishlist ---
  document.body.addEventListener('click', e => {
    const btn = e.target.closest('.add-to-wishlist');
    if (!btn) return;

    const name = btn.dataset.name;
    const price = parseFloat(btn.dataset.price?.replace(',', '.')) || 0;
    const image_url = btn.dataset.image_url || '/images/placeholder.jpg';

    if (!window.wishlist[name]) window.wishlist[name] = { name, price, image_url };
    saveWishlist();
    renderWishlist();
    openWishlist();
  });

  // --- Initialisation ---
  renderCart();
  renderWishlist();
});
