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
    const raw = el.dataset.image_url || el.dataset.imageUrl;
    if (!raw) return null;
    return {
      productId: el.dataset.productId,
      name:      el.dataset.name,
      price:     parseFloat((el.dataset.price || "0").replace(",", ".")),
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
    document.dispatchEvent(new CustomEvent("addedToCart"));
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
      price:     parseFloat((btn.dataset.price || "0").replace(",", ".")),
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

  // ═══════════════════════════════════════════════════════════
  // ─── MINI SHADE PICKER (carte produit — page catalogue) ───
  // ═══════════════════════════════════════════════════════════
  //
  // Quand l'utilisateur clique "Choisir une teinte" sur une carte,
  // on remplace le bouton par un mini-picker inline directement
  // sur la carte. Une fois la teinte choisie, le bouton devient
  // "Ajouter au panier" et exécute l'ajout immédiatement.
  //

  document.body.addEventListener("click", async e => {
    const shadeBtn = e.target.closest(".choose-shade-btn");
    // Ignore si c'est le bouton de la modale ou de la vue rapide
    if (!shadeBtn) return;
    if (shadeBtn.closest("#productModal") || shadeBtn.closest(".qv-modal")) return;
    e.preventDefault();
    e.stopPropagation();
    if (isOutOfStock(shadeBtn)) return;

    const card       = shadeBtn.closest(".product-card");
    const productInfo = card?.querySelector(".product-info");
    if (!productInfo) return;

    // Évite d'ouvrir plusieurs pickers sur la même carte
    if (productInfo.querySelector(".inline-shade-picker")) return;

    const productId = shadeBtn.dataset.productId;
    const name      = shadeBtn.dataset.name;
    const price     = parseFloat((shadeBtn.dataset.price || "0").replace(",", "."));
    const imageRaw  = shadeBtn.dataset.image_url || shadeBtn.dataset.imageUrl;
    const image     = normalizeImage(imageRaw);
    const stock     = parseInt(shadeBtn.dataset.stock, 10);

    // Masque le bouton d'origine le temps du picker
    shadeBtn.style.display = "none";

    // Conteneur picker
    const picker = document.createElement("div");
    picker.className = "inline-shade-picker";
    picker.innerHTML = `
      <div class="isp-label">Choisir une teinte</div>
      <div class="isp-dots"></div>
      <div class="isp-selected-name"></div>
      <div class="isp-actions">
        <button class="isp-add-btn" disabled>
          <i class="fas fa-shopping-bag"></i> Ajouter au panier
        </button>
        <button class="isp-cancel" title="Annuler">✕</button>
      </div>
    `;
    productInfo.appendChild(picker);

    const dotsEl       = picker.querySelector(".isp-dots");
    const selectedName = picker.querySelector(".isp-selected-name");
    const addBtn       = picker.querySelector(".isp-add-btn");
    const cancelBtn    = picker.querySelector(".isp-cancel");

    // Annulation : restaure le bouton d'origine
    cancelBtn.addEventListener("click", e => {
      e.preventDefault();
      e.stopPropagation();
      picker.remove();
      shadeBtn.style.display = "";
    });

    // Charge les teintes depuis l'API
    dotsEl.innerHTML = `<span class="isp-loading">Chargement…</span>`;
    let shades = [];
    try {
      const res = await fetch(`${B}/includes/get_shades.php?product_id=${productId}`);
      shades    = await res.json();
    } catch {
      dotsEl.innerHTML = `<span class="isp-loading">Erreur de chargement.</span>`;
      return;
    }

    dotsEl.innerHTML = "";
    let selectedShade = null;

    if (!shades.length) {
      // Aucune teinte en base → ajout direct sans teinte
      dotsEl.innerHTML = `<span class="isp-loading">Aucune teinte.</span>`;
      addBtn.disabled  = false;
    } else {
      shades.forEach(s => {
        const dot = document.createElement("span");
        dot.className        = "isp-dot";
        dot.title            = s.nom_teinte;
        dot.style.background = s.code_couleur || "#ccc";

        dot.addEventListener("click", e => {
          e.preventDefault();
          e.stopPropagation();
          dotsEl.querySelectorAll(".isp-dot").forEach(d => d.classList.remove("active"));
          dot.classList.add("active");
          selectedShade         = s.nom_teinte;
          selectedName.textContent = s.nom_teinte;
          addBtn.disabled       = false;
        });

        dotsEl.appendChild(dot);
      });
    }

    // Ajout au panier depuis le picker
    addBtn.addEventListener("click", e => {
      e.preventDefault();
      e.stopPropagation();
      addToCart({ productId, name, price, image, quantity: 1, shade: selectedShade });
      picker.remove();
      shadeBtn.style.display = "";
    });
  });


  // ═══════════════════════════════════════════════════════════
  // ─── VUE RAPIDE — SÉLECTION DE TEINTE ────────────────────
  // ═══════════════════════════════════════════════════════════
  //
  // Les swatches dans la modale aperçu rapide permettent de
  // sélectionner une teinte, puis le bouton "Ajouter au panier"
  // ajoute le produit avec la teinte choisie.
  //

  (function setupQuickViewCart() {
    const overlay    = document.getElementById("qvOverlay");
    if (!overlay) return;

    const closeBtn   = document.getElementById("qvClose");
    const cartBtn    = document.getElementById("qvCartBtn");
    const shadesRow  = document.getElementById("qvShadesRow");

    // État interne de la vue rapide
    let qvState = {
      productId:   null,
      name:        null,
      price:       null,
      image:       null,
      stock:       0,
      hasShades:   false,
      selectedShade: null,
    };

    // Expose openQV globalement pour le code PHP/inline
    window._qvOpenCallback = function(btn) {
      const id        = btn.dataset.productId;
      const name      = btn.dataset.name;
      const price     = parseFloat(btn.dataset.price);
      const oldPrice  = parseFloat(btn.dataset.oldPrice);
      const image     = btn.dataset.image;
      const brand     = btn.dataset.brand;
      const stock     = parseInt(btn.dataset.stock, 10);
      const hasShades = btn.dataset.hasShades === "1";
      const url       = btn.dataset.url;
      const desc      = btn.dataset.description || "";

      // Réinitialise la teinte sélectionnée
      qvState = { productId: id, name, price, image: normalizeImage(image), stock, hasShades, selectedShade: null };

      // Image
      const imgEl = document.getElementById("qvImg");
      imgEl.src = ""; requestAnimationFrame(() => { imgEl.src = image; imgEl.alt = name; });

      // Textes de base
      document.getElementById("qvBrand").textContent = brand || "";
      document.getElementById("qvName").textContent  = name;
      document.getElementById("qvDescription").textContent = desc;

      // Prix
      const priceEl = document.getElementById("qvPrice");
      if (!isNaN(oldPrice) && oldPrice > price) {
        priceEl.innerHTML =
          `<span class="qv-old">${fmtDA(oldPrice)}</span>
           <span class="qv-current">${fmtDA(price)}</span>`;
      } else {
        priceEl.innerHTML = `<span class="qv-normal">${fmtDA(price)}</span>`;
      }

      // Badge + stock
      const badge      = document.getElementById("qvBadge");
      const stockDot   = document.getElementById("qvStockDot");
      const stockLabel = document.getElementById("qvStockLabel");
      if (stock === 0) {
        badge.textContent = "Rupture"; badge.className = "qv-badge qv-badge--oos";
        stockDot.className = "qv-stock-dot qv-dot--out";
        stockLabel.textContent = "Rupture de stock";
      } else if (stock <= 5) {
        badge.textContent = "Stock limité"; badge.className = "qv-badge qv-badge--low";
        stockDot.className = "qv-stock-dot qv-dot--low";
        stockLabel.textContent = `Seulement ${stock} restant${stock > 1 ? "s" : ""}`;
      } else {
        badge.textContent = ""; badge.className = "qv-badge";
        stockDot.className = "qv-stock-dot qv-dot--in";
        stockLabel.textContent = "En stock";
      }

      // Lien fiche
      document.getElementById("qvDetailLink").href = url;

      // Teintes
      const shadesBlock = document.getElementById("qvShadesBlock");
      shadesRow.innerHTML = "";

      let shades = [];
      try { shades = JSON.parse(btn.dataset.shades || "[]"); } catch {}

      if (hasShades && shades.length) {
        // Label de teinte sélectionnée
        let labelEl = document.getElementById("qvSelectedShadeName");
        if (!labelEl) {
          labelEl = document.createElement("span");
          labelEl.id        = "qvSelectedShadeName";
          labelEl.className = "qv-selected-shade-name";
          shadesBlock.appendChild(labelEl);
        }
        labelEl.textContent = "";

        shades.forEach(s => {
          const dot = document.createElement("span");
          dot.className        = "qv-shade-dot";
          dot.title            = s.nom_teinte || "";
          dot.style.background = s.code_couleur || "#ccc";

          dot.addEventListener("click", () => {
            shadesRow.querySelectorAll(".qv-shade-dot").forEach(d => d.classList.remove("active"));
            dot.classList.add("active");
            qvState.selectedShade = s.nom_teinte;
            labelEl.textContent   = s.nom_teinte;
            // Active le bouton panier dès qu'une teinte est choisie
            updateQvCartBtn();
          });

          shadesRow.appendChild(dot);
        });

        shadesBlock.style.display = "flex";
      } else {
        shadesBlock.style.display = "none";
      }

      // Bouton panier initial
      updateQvCartBtn();

      // Ouvre la modale
      overlay.classList.add("active");
      document.body.style.overflow = "hidden";
      closeBtn.focus();
    };

    // Met à jour l'état du bouton panier de la vue rapide
    function updateQvCartBtn() {
      const { stock, hasShades, selectedShade } = qvState;
      const outOfStock     = stock === 0;
      const needsShade     = hasShades && !selectedShade;
      const disabled       = outOfStock || needsShade;

      cartBtn.disabled  = disabled;
      cartBtn.className = "qv-cart-btn";

      if (outOfStock) {
        cartBtn.innerHTML = `<i class="fas fa-ban"></i> Rupture de stock`;
      } else if (needsShade) {
        cartBtn.innerHTML = `<i class="fas fa-palette"></i> Sélectionnez une teinte`;
      } else {
        cartBtn.innerHTML = `<i class="fas fa-shopping-bag"></i> Ajouter au panier`;
      }
    }

    // Clic sur le bouton panier de la vue rapide
    cartBtn.addEventListener("click", () => {
      const { productId, name, price, image, stock, hasShades, selectedShade } = qvState;
      if (stock === 0) return;
      if (hasShades && !selectedShade) {
        // Secoue le bloc teintes pour indiquer qu'il faut choisir
        const shadesBlock = document.getElementById("qvShadesBlock");
        shadesBlock?.classList.add("qv-shake");
        setTimeout(() => shadesBlock?.classList.remove("qv-shake"), 500);
        return;
      }
      addToCart({ productId, name, price, image, quantity: 1, shade: selectedShade });
      closeQV();
    });

    function closeQV() {
      overlay.classList.remove("active");
      document.body.style.overflow = "";
      qvState.selectedShade = null;
    }

    closeBtn.addEventListener("click", closeQV);
    overlay.addEventListener("click", e => { if (e.target === overlay) closeQV(); });
    document.addEventListener("keydown", e => { if (e.key === "Escape") closeQV(); });
    document.addEventListener("addedToCart", closeQV);
  })();

  // Redirige les boutons quick-view vers le callback unifié
  document.querySelectorAll(".quick-view-btn").forEach(btn => {
    btn.addEventListener("click", e => {
      e.preventDefault();
      e.stopPropagation();
      window._qvOpenCallback?.(btn);
    });
  });


  // ─── MODAL TEINTES (product.php / wishlist) ───────────────
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

    // Listener choose-shade-btn dans la modale produit uniquement
    document.body.addEventListener("click", e => {
      const shadeBtn = e.target.closest(".choose-shade-btn");
      if (!shadeBtn) return;
      if (shadeBtn.closest("#productModal") || shadeBtn.closest(".qv-modal")) return;
      // Déjà géré par le listener inline-shade-picker ci-dessus
      // Ce listener ne traite QUE les boutons de la wishlist (fromWishlist)
      if (shadeBtn.dataset.fromWishlist !== "1") return;
      e.preventDefault();
      if (isOutOfStock(shadeBtn)) return;
      openShadeModal(shadeBtn);
    });
  }

  // ─── AJOUT PANIER DIRECT — avec vérification stock ───
  document.body.addEventListener("click", e => {
    const addBtn = e.target.closest(".add-to-cart");
    if (!addBtn) return;
    if (addBtn.closest("#productModal") || addBtn.closest(".qv-modal") || addBtn.closest(".inline-shade-picker")) return;
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
  const sortPrice    = document.getElementById("sortPrice");
  const filterSale   = document.getElementById("filterSale");
  const filterStock  = document.getElementById("filterInStock");
  const filterBrand  = document.getElementById("filterBrand");
  const grid         = document.querySelector(".products-grid");

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
      if (filterSale.checked  && card.dataset.sale  !== "1") return false;
      if (filterStock?.checked && card.closest(".product-card-link")?.dataset.instock === "0") return false;
      if (filterBrand.value   && card.dataset.brand !== filterBrand.value) return false;
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

  const els = [sortPrice, filterSale, filterBrand];
  if (filterStock) els.push(filterStock);
  els.forEach(el => el.addEventListener("change", applyFilters));
});


// ─── FORMAT montant algérien ────────────────────────────
function fmtDA(v) {
  return Number(v).toLocaleString("fr-DZ", { minimumFractionDigits: 2 }) + " DA";
}