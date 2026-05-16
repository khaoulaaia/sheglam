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
            <strong id="cartTotal">0.00 DA</strong>
        </div>
        <button class="checkoutBtn">Passer à la caisse</button>
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
<script src="/js/checkout.js" defer></script>

<script>
// =============================================================================
// Tout est déclaré HORS de DOMContentLoaded pour être accessible
// immédiatement par product.php et shop.js (chargés après sidebar.php).
// =============================================================================

window.cart     = JSON.parse(localStorage.getItem('cart'))     || {};
window.wishlist = JSON.parse(localStorage.getItem('wishlist')) || {};

function saveCart()     { localStorage.setItem('cart',     JSON.stringify(window.cart)); }
function saveWishlist() { localStorage.setItem('wishlist', JSON.stringify(window.wishlist)); }

// ── Ouvrir sidebars ───────────────────────────────────────────────────────────
window.openCart = () => {
    document.getElementById('sidebar').classList.add('active');
    document.getElementById('sidebarOverlay').classList.add('active');
};
window.openWishlist = () => {
    document.getElementById('wishlistSidebar').classList.add('active');
    document.getElementById('sidebarOverlay').classList.add('active');
};

// ── Rendu du panier ───────────────────────────────────────────────────────────
window.renderCart = () => {
    const cartItemsEl = document.getElementById('cartItems');
    const cartTotalEl = document.getElementById('cartTotal');
    if (!cartItemsEl) return;

    // Panier vide
    if (!window.cart || Object.keys(window.cart).length === 0) {
        cartItemsEl.innerHTML = '<p>Votre panier est vide.</p>';
        if (cartTotalEl) cartTotalEl.textContent = '0.00 DA';
        return;
    }

    cartItemsEl.innerHTML = '';
    let total = 0;

    for (const key in window.cart) {
        const item = window.cart[key];
        if (!item || !item.name) continue;

        const price = parseFloat(item.price)  || 0;
        const qty   = parseInt(item.quantity)  || 1;
        total += price * qty;

        // Image : priorité à image_url de la teinte (passée lors du addToCart),
        // fallback sur placeholder
        const imageSrc = (item.image_url && item.image_url.trim())
                       ? item.image_url
                       : '/images/placeholder.jpg';

        // Nom affiché : "Produit — Teinte" si teinte présente
        const displayName = item.shade
                          ? `${item.name} <span class="cart-shade-tag">— ${item.shade}</span>`
                          : item.name;

        const itemEl = document.createElement('div');
        itemEl.classList.add('cart-item');
        itemEl.innerHTML = `
            <img src="${imageSrc}"
                 alt="${item.name}"
                 class="cart-item-img"
                 onerror="this.src='/images/placeholder.jpg'">
            <div class="cart-item-info">
                <h4>${displayName}</h4>
                <p class="cart-item-price">${price.toFixed(2)} DA</p>
                <div class="quantity-controls">
                    <button class="decrease">−</button>
                    <span class="quantity">${qty}</span>
                    <button class="increase">+</button>
                    <button class="remove-item">✕</button>
                </div>
            </div>
        `;

        // Événements quantité / suppression
        itemEl.querySelector('.increase').addEventListener('click', () => {
            window.cart[key].quantity += 1;
            saveCart();
            window.renderCart();
        });
        itemEl.querySelector('.decrease').addEventListener('click', () => {
            window.cart[key].quantity -= 1;
            if (window.cart[key].quantity <= 0) delete window.cart[key];
            saveCart();
            window.renderCart();
        });
        itemEl.querySelector('.remove-item').addEventListener('click', () => {
            delete window.cart[key];
            saveCart();
            window.renderCart();
        });

        cartItemsEl.appendChild(itemEl);
    }

    // Mise à jour du total
    if (cartTotalEl) cartTotalEl.textContent = total.toFixed(2) + ' DA';
};

// ── Ajouter au panier ─────────────────────────────────────────────────────────
// Utilisé par product.php (teintes) ET shop.js (catalogue, wishlist…)
window.addToCart = ({ productId, name, price, image, quantity, shade }) => {
    // Clé unique : "Produit - Teinte" ou "Produit"
    const key       = shade ? `${name} - ${shade}` : name;
    const image_url = (image && image.trim()) ? image : '/images/placeholder.jpg';
    quantity        = parseInt(quantity) || 1;
    price           = parseFloat(price)  || 0;

    if (window.cart[key]) {
        window.cart[key].quantity += quantity;
        // Mettre à jour l'image si elle a changé (ex : teinte avec image)
        if (image_url !== '/images/placeholder.jpg') {
            window.cart[key].image_url = image_url;
        }
    } else {
        window.cart[key] = { name, price, image_url, quantity, shade: shade || null };
    }

    saveCart();
    window.renderCart();
    window.openCart();
};

// ── Rendu de la wishlist ──────────────────────────────────────────────────────
window.renderWishlist = () => {
    const wishlistItemsEl = document.getElementById('wishlistItems');
    if (!wishlistItemsEl) return;

    if (!window.wishlist || Object.keys(window.wishlist).length === 0) {
        wishlistItemsEl.innerHTML = '<p>Votre liste de souhaits est vide.</p>';
        return;
    }

    wishlistItemsEl.innerHTML = '';
    for (const key in window.wishlist) {
        const item = window.wishlist[key];
        if (!item || !item.name) continue;

        const imageSrc = (item.image_url && item.image_url.trim())
                       ? item.image_url
                       : '/images/placeholder.jpg';
        const price = parseFloat(item.price) || 0;

        const itemEl = document.createElement('div');
        itemEl.classList.add('wishlist-item');
        itemEl.innerHTML = `
            <img src="${imageSrc}"
                 alt="${item.name}"
                 class="wishlist-item-img"
                 onerror="this.src='/images/placeholder.jpg'">
            <div class="wishlist-item-info">
                <h4>${item.name}</h4>
                <div class="wishlist-meta">
                    <p>${price.toFixed(2)} DA</p>
                    <button class="remove-wishlist">✕</button>
                </div>
            </div>
        `;

        itemEl.querySelector('.remove-wishlist').addEventListener('click', () => {
            delete window.wishlist[key];
            saveWishlist();
            window.renderWishlist();
        });

        wishlistItemsEl.appendChild(itemEl);
    }
};

// =============================================================================
// Listeners DOM — après le chargement de la page
// =============================================================================
document.addEventListener("DOMContentLoaded", function () {
    const overlay         = document.getElementById('sidebarOverlay');
    const sidebar         = document.getElementById('sidebar');
    const wishlistSidebar = document.getElementById('wishlistSidebar');

    // ── Fermeture ─────────────────────────────────────────────────────────────
    document.getElementById('closeSidebar').addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });
    document.getElementById('closeWishlist').addEventListener('click', () => {
        wishlistSidebar.classList.remove('active');
        overlay.classList.remove('active');
    });
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        wishlistSidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    // ── Boutons .add-to-cart classiques (pages sans teintes) ──────────────────
    // Note : sur product.php avec teintes, c'est window.addToCart() qui est
    // appelé directement depuis le JS inline — ces listeners ne s'en occupent pas.
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        // Éviter de double-binder le bouton addWithShadeBtn géré par product.php
        if (btn.id === 'addWithShadeBtn') return;

        btn.addEventListener('click', () => {
            if (btn.disabled) return;
            const name      = btn.dataset.name;
            const price     = parseFloat((btn.dataset.price || '0').replace(',', '.')) || 0;
            const image_url = btn.dataset.image_url || '/images/placeholder.jpg';
            const shade     = btn.dataset.shade     || null;
            const key       = shade ? `${name} - ${shade}` : name;

            if (window.cart[key]) window.cart[key].quantity += 1;
            else window.cart[key] = { name, price, image_url, quantity: 1, shade };

            saveCart();
            window.renderCart();
            window.openCart();
        });
    });

    // ── Boutons .add-to-wishlist ──────────────────────────────────────────────
    document.querySelectorAll('.add-to-wishlist').forEach(btn => {
        btn.addEventListener('click', () => {
            const name      = btn.dataset.name;
            const price     = parseFloat((btn.dataset.price || '0').replace(',', '.')) || 0;
            const image_url = btn.dataset.image_url || '/images/placeholder.jpg';

            if (!window.wishlist[name]) {
                window.wishlist[name] = { name, price, image_url };
            }
            saveWishlist();
            window.renderWishlist();
            window.openWishlist();
        });
    });

    // ── Initialisation ────────────────────────────────────────────────────────
    window.renderCart();
    window.renderWishlist();
});
</script>