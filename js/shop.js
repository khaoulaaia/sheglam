document.addEventListener("DOMContentLoaded", () => {

  // ===============================
  // ÉTAT GLOBAL
  // ===============================
  const cart = JSON.parse(localStorage.getItem("cart")) || {};
  const wishlist = JSON.parse(localStorage.getItem("wishlist")) || {};

  const saveCart = () => localStorage.setItem("cart", JSON.stringify(cart));
  const saveWishlist = () => localStorage.setItem("wishlist", JSON.stringify(wishlist));

  // ===============================
  // OUTILS
  // ===============================
  const buildKey = (productId, shade) =>
    shade ? `${productId}__${shade}` : `${productId}`;

  const getProductData = el => {
  if (!el) return null;

  let image = el.dataset.image_url;
  if (!image) return null;

  // 🔥 NORMALISATION ABSOLUE
  if (!image.startsWith("http")) {
    image = "/sheglam/images/" + image.split("/").pop();
  }

  return {
    productId: el.dataset.productId,
    name: el.dataset.name,
    price: parseFloat(el.dataset.price.replace(",", ".")),
    image
  };
};

const updateCartTotal = () => {
  let total = 0;

  Object.values(cart).forEach(item => {
    total += item.price * item.quantity;
  });

  const totalEl = document.getElementById("cartTotal");
  if (totalEl) {
    totalEl.textContent = `${total.toFixed(2)}DA`;
  }
};

  // ===============================
  // PANIER – AJOUT
  // ===============================
  function addToCart({ productId, name, price, image, quantity = 1, shade = null }) {
  const key = buildKey(productId, shade);

  if (cart[key]) {
    cart[key].quantity += quantity;
  } else {
    cart[key] = {
      productId,
      name,
      price,
      image_url: image,
      shade,
      quantity
    };
  }

saveCart();
window.openCart?.();   // ouvre la sidebar (crée le DOM)
window.renderCart();   // maintenant #cartItems existe
}

  // ===============================
  // RENDU PANIER (SIDEBAR)
  // ===============================
window.renderCart = () => {
  const cartItemsEl = document.getElementById("cartItems");
  if (!cartItemsEl) return;

  cartItemsEl.innerHTML = "";

  if (!Object.keys(cart).length) {
    cartItemsEl.innerHTML = "<p>Votre panier est vide.</p>";
    return;
  }

  Object.entries(cart).forEach(([key, item]) => {
    let imageUrl = item.image_url;
    if (!imageUrl.startsWith("http")) {
      imageUrl = "/sheglam/images/" + imageUrl.split("/").pop();
    }

    const div = document.createElement("div");
    div.className = "cart-item";
    div.innerHTML = `
      <img src="${imageUrl}" alt="${item.name}" class="cart-item-img">
      <div class="cart-item-info">
  <h4>${item.name}${item.shade ? " - " + item.shade : ""}</h4>
  <div class="cart-item-price">${item.price.toFixed(2)}DA</div>
        <div class="quantity-controls">
          <button class="decrease">-</button>
          <span class="quantity">${item.quantity}</span>
          <button class="increase">+</button>
          <button class="remove-item">x</button>
        </div>
      </div>
    `;

    div.querySelector(".increase").onclick = () => {
      item.quantity++;
      saveCart();
        updateCartTotal();

      window.renderCart();

    };

    div.querySelector(".decrease").onclick = () => {
      item.quantity--;
      if (item.quantity <= 0) delete cart[key];
      saveCart();
        updateCartTotal();

      window.renderCart();
    };

    div.querySelector(".remove-item").onclick = () => {
      delete cart[key];
      saveCart();
        updateCartTotal();

      window.renderCart();
    };

    cartItemsEl.appendChild(div);
  });
  updateCartTotal();

};
document.body.addEventListener("click", e => {
  const btn = e.target.closest(".add-to-wishlist");
  if (!btn) return;

  const productId = btn.dataset.productId;
  const name = btn.dataset.name;
  const price = parseFloat(btn.dataset.price.replace(",", "."));
  const image = btn.dataset.image_url;
  const hasShades = btn.dataset.hasShades === "1";

  wishlist[productId] = {
    productId,
    name,
    price,
    image_url: image,
    hasShades
  };

  saveWishlist(); // 🔥 utilise ta fonction
  alert("Produit ajouté à la wishlist !");
});

  // ===============================
  // MODAL TEINTES
  // ===============================
  // ===============================
// MODAL PRODUIT – TEINTES + QUANTITÉ
// ===============================
const modal = document.getElementById("productModal");

if (modal) {
  const productNameEl = modal.querySelector("#productName");
  const productPriceEl = modal.querySelector("#productPrice");
  const productImageEl = modal.querySelector("#productMainImage");
  const shadeOptionsEl = modal.querySelector("#shadeOptions");
  const addFromModalBtn = modal.querySelector("#addToCartFromModal");
  const closeModalBtn = modal.querySelector(".close-product-modal");
  const qtyEl = modal.querySelector("#quantity");
  const increaseQtyBtn = modal.querySelector("#increaseQty");
  const decreaseQtyBtn = modal.querySelector("#decreaseQty");

  let currentProduct = null;
  let selectedShade = null;
  let currentQuantity = 1;

  const updateQuantityUI = () => {
    qtyEl.textContent = currentQuantity;
  };

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

  // ===============================
  // GESTION QUANTITÉ
  // ===============================
  increaseQtyBtn?.addEventListener("click", () => {
    currentQuantity++;
    updateQuantityUI();
      updateCartTotal();

  });

  decreaseQtyBtn?.addEventListener("click", () => {
    if (currentQuantity > 1) {
      currentQuantity--;
      updateQuantityUI();
        updateCartTotal();

    }
  });
  function renderWishlist() {
  const container = document.getElementById("wishlistItems");
  if (!container) return;

  container.innerHTML = "";

  const wishlistData = JSON.parse(localStorage.getItem("wishlist")) || {};

  if (!Object.keys(wishlistData).length) {
    container.innerHTML = "<p>Votre wishlist est vide.</p>";
    return;
  }

  Object.values(wishlistData).forEach(item => {
    const div = document.createElement("div");
    div.className = "wishlist-item";
    div.innerHTML = `
      <img src="${item.image_url}" alt="${item.name}" class="wishlist-item-img">
      <h4>${item.name}${item.shade ? " - " + item.shade : ""}</h4>
      <p>${item.price.toFixed(2)}DA</p>
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

  // ===============================
  // AJOUT AU PANIER MODAL
  // ===============================
 addFromModalBtn.addEventListener("click", () => {
  if (!selectedShade) {
    alert("Veuillez choisir une teinte.");
    return;
  }
  if (!currentProduct) return;

  addToCart({
    ...currentProduct,
    quantity: currentQuantity,
    shade: selectedShade
  });

  // 🔥 SI PROVIENT DE LA WISHLIST → SUPPRIMER
  if (modal.dataset.fromWishlist === "1") {
    const wishlist = JSON.parse(localStorage.getItem("wishlist")) || {};
    const key = modal.dataset.wishlistKey;

    if (wishlist[key]) {
      delete wishlist[key];
      localStorage.setItem("wishlist", JSON.stringify(wishlist));
    }

    // ✅ Mise à jour visuelle sans refresh
    if (typeof renderWishlist === "function") {
      renderWishlist();
    }
  }

  closeModal();
});


  // ===============================
  // OUVERTURE MODAL TEINTES
  // ===============================
  async function openShadeModal(button) {
    const data = getProductData(button);
    if (!data) return;

    currentProduct = data;
    const viewLink = document.getElementById("viewFullDetails");
if (viewLink) {
  viewLink.href = `/sheglam/product.php?id=${data.productId}`;
}

    selectedShade = null;
    currentQuantity = 1;
    updateQuantityUI();

    productNameEl.textContent = data.name;
    productPriceEl.textContent = `${data.price.toFixed(2)}DA`;
    productImageEl.src = data.image;
    shadeOptionsEl.innerHTML = "<p>Chargement...</p>";

    try {
      const res = await fetch(`/sheglam/includes/get_shades.php?product_id=${button.dataset.productId}`);
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

  // 👉 APPLICATION DIRECTE DU CODE COULEUR
  color.style.backgroundColor = s.code_couleur;

  option.appendChild(color);

  option.addEventListener("click", () => {
    shadeOptionsEl
      .querySelectorAll(".shade-option")
      .forEach(o => o.classList.remove("selected"));

    option.classList.add("selected");
    selectedShade = s.nom_teinte;

    // optionnel : afficher le nom
    const label = document.getElementById("selectedShadeName");
    if (label) label.textContent = s.nom_teinte;
  });

  shadeOptionsEl.appendChild(option);
});

      }
      modal.dataset.fromWishlist = button.dataset.fromWishlist || "";
      modal.dataset.wishlistKey = button.dataset.wishlistKey || "";

      openModal();
    } catch (err) {
      console.error("Erreur teintes", err);
      shadeOptionsEl.innerHTML = "<p>Erreur de chargement.</p>";
    }
  }

  document.body.addEventListener("click", e => {
    const shadeBtn = e.target.closest(".choose-shade-btn");
    if (shadeBtn) {
      e.preventDefault();
      openShadeModal(shadeBtn);
    }
  });
}


  // ===============================
  // AJOUT PANIER (SANS TEINTE)
  // ===============================
  document.body.addEventListener("click", e => {
    const addBtn = e.target.closest(".add-to-cart");
    if (!addBtn) return;

    e.preventDefault();

    const data = getProductData(addBtn);
    if (!data) return;

    const wrapper = addBtn.closest(".add-to-cart-wrapper");
    const qtyInput = wrapper?.querySelector("input[name='quantity']");
    const quantity = qtyInput ? Math.max(1, parseInt(qtyInput.value)) : 1;

    addToCart({ ...data, quantity });
    if (qtyInput) qtyInput.value = 1;
  });

  // ===============================
  // INIT
  // ===============================
  window.renderCart();
    updateCartTotal();

});

// ===============================
// FILTRES & TRI
// ===============================
const cards = [...document.querySelectorAll(".product-card")];
const grid = document.querySelector(".products-grid");

const sortPrice = document.getElementById("sortPrice");
const filterSale = document.getElementById("filterSale");
const filterBrand = document.getElementById("filterBrand");

// REMPLIR LES MARQUES
const brands = [...new Set(cards.map(c => c.dataset.brand).filter(Boolean))];
brands.forEach(b => {
  const opt = document.createElement("option");
  opt.value = b;
  opt.textContent = b;
  filterBrand.appendChild(opt);
});

function applyFilters() {
  let visibleCards = cards.filter(card => {
    const price = parseFloat(card.dataset.price);
    const brand = card.dataset.brand;
    const isSale = card.dataset.sale === "1";

    if (filterSale.checked && !isSale) return false;
    if (filterBrand.value && filterBrand.value !== brand) return false;

    return true;
  });

  // TRI PRIX
  if (sortPrice.value) {
    visibleCards.sort((a, b) => {
      const pa = parseFloat(a.dataset.price);
      const pb = parseFloat(b.dataset.price);
      return sortPrice.value === "asc" ? pa - pb : pb - pa;
    });
  }

  // RENDER
  grid.innerHTML = "";
  visibleCards.forEach(card => grid.appendChild(card));
}

// EVENTS
[sortPrice, filterSale, filterBrand].forEach(el =>
  el.addEventListener("change", applyFilters)
);
