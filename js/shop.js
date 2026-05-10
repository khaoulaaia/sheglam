/**
 * shop.js — SheGlamour
 * BASE_URL est injecté par PHP dans chaque page via :
 *   <script>const BASE_URL = "<?= $b ?>";</script>
 */

document.addEventListener("DOMContentLoaded", () => {

  const B = (typeof BASE_URL !== "undefined") ? BASE_URL : "";

  // ─── ÉTAT GLOBAL ─────────────────────────────────────
  const cart     = JSON.parse(localStorage.getItem("cart"))     || {};
  const wishlist = JSON.parse(localStorage.getItem("wishlist")) || {};

  const saveCart     = () => localStorage.setItem("cart",     JSON.stringify(cart));
  const saveWishlist = () => localStorage.setItem("wishlist", JSON.stringify(wishlist));

  // ─── UTILS ───────────────────────────────────────────
  const buildKey = (productId, shade) => shade ? `${productId}__${shade}` : `${productId}`;

  const normalizeImage = (url) => {
    if (!url) return B + "/images/placeholder.jpg";
    if (url.startsWith("http")) return url;
    return B + "/images/" + url.split("/").pop();
  };

  const getProductData = (el) => {
    if (!el) return null;
    const raw = el.dataset.image_url;
    if (!raw) return null;
    return {
      productId: el.dataset.productId,
      name:      el.dataset.name,
      price:     parseFloat(el.dataset.price.replace(",", ".")),
      image:     normalizeImage(raw)
    };
  };

  const isOutOfStock = (btn) =>
    btn.disabled || btn.dataset.stock === "0";

  const updateCartTotal = () => {
    let total = 0;
    Object.values(cart).forEach(item => { total += item.price * item.quantity; });
    const el = document.getElementById("cartTotal");
    if (el) el.textContent = `${total.toFixed(2)} DA`;
  };

  // ─── PANIER — AJOUT ──────────────────────────────────
  function addToCart({ productId, name, price, image, quantity = 1, shade = null }) {
    const key = buildKey(productId, shade);
    if (cart[key]) {
      cart[key].quantity += quantity;
    } else {
      cart[key] = { productId, name, price, image_url: image, shade, quantity };
    }
    saveCart();
    window.openCart?.();
    window.renderCart();
  }

  // ─── PANIER — RENDU ──────────────────────────────────
  window.renderCart = () => {
    const cartItemsEl = document.getElementById("cartItems");
    if (!cartItemsEl) return;

    cartItemsEl.innerHTML = "";

    if (!Object.keys(cart).length) {
      cartItemsEl.innerHTML = "<p>Votre panier est vide.</p>";
      updateCartTotal();
      return;
    }

    Object.entries(cart).forEach(([key, item]) => {
      const imgUrl = normalizeImage(item.image_url);
      const div = document.createElement("div");
      div.className = "cart-item";
      div.innerHTML = `
        <img src="${imgUrl}" alt="${item.name}" class="cart-item-img">
        <div class="cart-item-info">
          <h4>${item.name}${item.shade ? " — " + item.shade : ""}</h4>
          <div class="cart-item-price">${item.price.toFixed(2)} DA</div>
          <div class="quantity-controls">
            <button class="decrease">−</button>
            <span class="quantity">${item.quantity}</span>
            <button class="increase">+</button>
            <button class="remove-item">✕</button>
          </div>
        </div>
      `;

      div.querySelector(".increase").onclick = () => {
        item.quantity++;
        saveCart(); updateCartTotal(); window.renderCart();
      };
      div.querySelector(".decrease").onclick = () => {
        item.quantity--;
        if (item.quantity <= 0) delete cart[key];
        saveCart(); updateCartTotal(); window.renderCart();
      };
      div.querySelector(".remove-item").onclick = () => {
        delete cart[key];
        saveCart(); updateCartTotal(); window.renderCart();
      };

      cartItemsEl.appendChild(div);
    });

    updateCartTotal();
  };

  // ─── WISHLIST — AJOUT ────────────────────────────────
  document.body.addEventListener("click", e => {
    const btn = e.target.closest(".add-to-wishlist");
    if (!btn) return;

    const productId = btn.dataset.productId;
    wishlist[productId] = {
      productId,
      name:      btn.dataset.name,
      price:     parseFloat(btn.dataset.price.replace(",", ".")),
      image_url: normalizeImage(btn.dataset.image_url),
      hasShades: btn.dataset.hasShades === "1"
    };
    saveWishlist();
    alert("Produit ajouté à la wishlist !");
  });

  // ─── WISHLIST — RENDU ────────────────────────────────
  function renderWishlist() {
    const container = document.getElementById("wishlistItems");
    if (!container) return;
    container.innerHTML = "";
    const wl = JSON.parse(localStorage.getItem("wishlist")) || {};
    if (!Object.keys(wl).length) {
      container.innerHTML = "<p>Votre wishlist est vide.</p>";
      return;
    }
    Object.values(wl).forEach(item => {
      const div = document.createElement("div");
      div.className = "wishlist-item";
      div.innerHTML = `
        <img src="${normalizeImage(item.image_url)}" alt="${item.name}" class="wishlist-item-img">
        <h4>${item.name}${item.shade ? " — " + item.shade : ""}</h4>
        <p>${item.price.toFixed(2)} DA</p>
        <button class="remove-wishlist" data-product-id="${item.productId}">Supprimer</button>
        <button class="add-to-cart-wishlist"
          data-product-id="${item.productId}"
          data-name="${item.name}"
          data-price="${item.price}"
          data-image_url="${item.image_url}"
          data-has-shades="${item.hasShades ? 1 : 0}"
        >Ajouter au panier</button>
      `;
      container.appendChild(div);
    });
  }

  // ─── MODAL TEINTES ───────────────────────────────────
  const modal = document.getElementById("productModal");

  if (modal) {
    const productNameEl   = modal.querySelector("#productName");
    const productPriceEl  = modal.querySelector("#productPrice");
    const productImageEl  = modal.querySelector("#productMainImage");
    const shadeOptionsEl  = modal.querySelector("#shadeOptions");
    const addFromModalBtn = modal.querySelector("#addToCartFromModal");
    const closeModalBtn   = modal.querySelector(".close-product-modal");
    const qtyEl           = modal.querySelector("#quantity");
    const increaseQtyBtn  = modal.querySelector("#increaseQty");
    const decreaseQtyBtn  = modal.querySelector("#decreaseQty");
    const thumbsEl        = modal.querySelector("#productThumbnails");

    let currentProduct  = null;
    let selectedShade   = null;
    let currentQuantity = 1;

    const updateQuantityUI = () => { if (qtyEl) qtyEl.textContent = currentQuantity; };

    const openModal = () => {
      currentQuantity = 1;
      updateQuantityUI();
      modal.style.display = "flex";
      document.body.style.overflow = "hidden";
    };

    const closeModal = () => {
      modal.style.display = "none";
      document.body.style.overflow = "auto";
      selectedShade = null;
      currentProduct = null;
      currentQuantity = 1;
    };

    closeModalBtn?.addEventListener("click", closeModal);
    modal.addEventListener("click", e => e.target === modal && closeModal());
    increaseQtyBtn?.addEventListener("click", () => { currentQuantity++; updateQuantityUI(); });
    decreaseQtyBtn?.addEventListener("click", () => {
      if (currentQuantity > 1) { currentQuantity--; updateQuantityUI(); }
    });

    addFromModalBtn?.addEventListener("click", () => {
      if (!selectedShade)  { alert("Veuillez choisir une teinte."); return; }
      if (!currentProduct) return;
      addToCart({ ...currentProduct, quantity: currentQuantity, shade: selectedShade });

      if (modal.dataset.fromWishlist === "1") {
        const wl  = JSON.parse(localStorage.getItem("wishlist")) || {};
        const key = modal.dataset.wishlistKey;
        if (wl[key]) { delete wl[key]; localStorage.setItem("wishlist", JSON.stringify(wl)); }
        renderWishlist();
      }
      closeModal();
    });

    async function openShadeModal(button) {
      const data = getProductData(button);
      if (!data) return;

      currentProduct  = data;
      selectedShade   = null;
      currentQuantity = 1;
      updateQuantityUI();

      const viewLink = document.getElementById("viewFullDetails");
      if (viewLink) viewLink.href = `${B}/product.php?id=${data.productId}`;

      if (productNameEl)  productNameEl.textContent  = data.name;
      if (productPriceEl) productPriceEl.textContent = `${data.price.toFixed(2)} DA`;
      if (productImageEl) productImageEl.src          = data.image;
      if (thumbsEl)       thumbsEl.innerHTML          = "";
      if (shadeOptionsEl) shadeOptionsEl.innerHTML    = "<p>Chargement…</p>";

      try {
        const res    = await fetch(`${B}/includes/get_shades.php?product_id=${button.dataset.productId}`);
        const shades = await res.json();

        shadeOptionsEl.innerHTML = "";

        if (!shades.length) {
          shadeOptionsEl.innerHTML = "<p>Aucune teinte disponible.</p>";
        } else {
          shades.forEach(s => {
            const option = document.createElement("div");
            option.className = "shade-option";
            const color = document.createElement("span");
            color.className = "shade-color";
            color.style.backgroundColor = s.code_couleur;
            option.appendChild(color);
            option.addEventListener("click", () => {
              shadeOptionsEl.querySelectorAll(".shade-option").forEach(o => o.classList.remove("selected"));
              option.classList.add("selected");
              selectedShade = s.nom_teinte;
              const label = document.getElementById("selectedShadeName");
              if (label) label.textContent = s.nom_teinte;
            });
            shadeOptionsEl.appendChild(option);
          });
        }

        modal.dataset.fromWishlist = button.dataset.fromWishlist || "";
        modal.dataset.wishlistKey  = button.dataset.wishlistKey  || "";
        openModal();

      } catch (err) {
        console.error("Erreur teintes", err);
        if (shadeOptionsEl) shadeOptionsEl.innerHTML = "<p>Erreur de chargement.</p>";
      }
    }

    // Listener choose-shade-btn — avec vérification stock
    document.body.addEventListener("click", e => {
      const shadeBtn = e.target.closest(".choose-shade-btn");
      if (!shadeBtn) return;
      e.preventDefault();
      if (isOutOfStock(shadeBtn)) return;
      openShadeModal(shadeBtn);
    });
  }

  // ─── AJOUT PANIER DIRECT — avec vérification stock ───
  document.body.addEventListener("click", e => {
    const addBtn = e.target.closest(".add-to-cart");
    if (!addBtn) return;
    e.preventDefault();
    if (isOutOfStock(addBtn)) return;
    const data = getProductData(addBtn);
    if (!data) return;
    const wrapper  = addBtn.closest(".add-to-cart-wrapper");
    const qtyInput = wrapper?.querySelector("input[name='quantity']");
    const quantity = qtyInput ? Math.max(1, parseInt(qtyInput.value) || 1) : 1;
    addToCart({ ...data, quantity });
    if (qtyInput) qtyInput.value = 1;
  });

  // ─── CHECKOUT ────────────────────────────────────────
  document.body.addEventListener("click", e => {
    if (e.target.closest(".checkoutBtn, #checkoutBtn, [data-action='checkout']")) {
      e.preventDefault();
      const cartData = JSON.parse(localStorage.getItem("cart") || "{}");
      if (!Object.keys(cartData).length) {
        window.SheGlamCheckout?.notify("Panier vide", "Ajoutez des produits avant de commander.", "warning");
        return;
      }
      window.SheGlamCheckout?.open();
    }
  });

  // ─── INIT ────────────────────────────────────────────
  window.renderCart();
  updateCartTotal();

});


// ─── FILTRES (page catalogue uniquement) ─────────────
document.addEventListener("DOMContentLoaded", () => {
  const sortPrice   = document.getElementById("sortPrice");
  const filterSale  = document.getElementById("filterSale");
  const filterBrand = document.getElementById("filterBrand");
  const grid        = document.querySelector(".products-grid");

  if (!sortPrice || !filterSale || !filterBrand || !grid) return;

  const cards = [...document.querySelectorAll(".product-card")];

  // Remplir marques dynamiquement
  const brands = [...new Set(cards.map(c => c.dataset.brand).filter(Boolean))];
  brands.forEach(b => {
    const opt = document.createElement("option");
    opt.value = b; opt.textContent = b;
    filterBrand.appendChild(opt);
  });

  function applyFilters() {
    let visible = cards.filter(card => {
      if (filterSale.checked && card.dataset.sale !== "1") return false;
      if (filterBrand.value && card.dataset.brand !== filterBrand.value) return false;
      return true;
    });

    if (sortPrice.value) {
      visible.sort((a, b) => {
        const pa = parseFloat(a.dataset.price);
        const pb = parseFloat(b.dataset.price);
        return sortPrice.value === "asc" ? pa - pb : pb - pa;
      });
    }

    grid.innerHTML = "";
    visible.forEach(card => {
      const link = card.closest("a") || card;
      grid.appendChild(link);
    });
  }

  [sortPrice, filterSale, filterBrand].forEach(el => el.addEventListener("change", applyFilters));
});