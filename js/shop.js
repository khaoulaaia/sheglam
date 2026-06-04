/**
 * shop.js — SheGlamour
 * BASE_URL est injecté par PHP dans chaque page via :
 *   <script>const BASE_URL = "<?= $b ?>";</script>
 */

// ─── BADGE PANIER ─────────────────────────────────────
function bumpCartBadge() {
  const cart  = JSON.parse(localStorage.getItem("cart") || "{}");
  const total = Object.values(cart).reduce((s, i) => s + (i.quantity || 0), 0);
  ["cartCountBadge", "cartCountBadgeMobile"].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = total > 0 ? total : "0";
    el.style.display = total > 0 ? "flex" : "none";
    el.classList.remove("bump");
    void el.offsetWidth;
    el.classList.add("bump");
    el.addEventListener("animationend", () => el.classList.remove("bump"), { once: true });
  });
}
document.addEventListener("DOMContentLoaded", bumpCartBadge);

document.addEventListener("DOMContentLoaded", () => {

  const B = (typeof BASE_URL !== "undefined") ? BASE_URL : "";
  const isCataloguePage = !!document.querySelector(".products-grid");

  const cart     = JSON.parse(localStorage.getItem("cart"))     || {};
  const wishlist = JSON.parse(localStorage.getItem("wishlist")) || {};
  const saveCart     = () => localStorage.setItem("cart",     JSON.stringify(cart));
  const saveWishlist = () => localStorage.setItem("wishlist", JSON.stringify(wishlist));

  const buildKey = (productId, shade) => shade ? `${productId}__${shade}` : `${productId}`;

  const normalizeImage = (url) => {
    if (!url) return B + "/images/placeholder.jpg";
    if (url.startsWith("http")) return url;
    return B + "/images/" + url.split("/").pop();
  };

  /* Résout img_src d'une teinte retournée par get_shades.php
     Priorité : img_src (calculé PHP) > image > null */
  const resolveShadeImg = (s) => {
    const raw = s.img_src || s.image || null;
    if (!raw || !raw.trim()) return null;
    if (raw.startsWith("http")) return raw;
    return B + "/images/" + raw.split("/").pop();
  };

  const getProductData = (el) => {
    if (!el) return null;
    const raw = el.dataset.image_url || el.dataset.imageUrl;
    if (!raw) return null;
    return {
      productId : el.dataset.productId,
      name      : el.dataset.name,
      price     : parseFloat((el.dataset.price || "0").replace(",", ".")),
      image     : normalizeImage(raw)
    };
  };

  const isOutOfStock = (btn) => btn.disabled || btn.dataset.stock === "0";

  const updateCartTotal = () => {
    let total = 0;
    Object.values(cart).forEach(item => { total += item.price * item.quantity; });
    const el = document.getElementById("cartTotal");
    if (el) el.textContent = `${total.toFixed(2)} DA`;
  };

  // ─── PANIER — AJOUT ──────────────────────────────────
  window.addToCart = function({ productId, name, price, image, quantity = 1, shade = null, _originEl = null }) {
    const key = buildKey(productId, shade);
    if (cart[key]) cart[key].quantity += quantity;
    else cart[key] = { productId, name, price, image_url: image, shade, quantity };
    saveCart();
    bumpCartBadge();
    if (_originEl) flyToCart(_originEl, image, () => {
      window.openCart?.(); window.renderCart();
      document.dispatchEvent(new CustomEvent("addedToCart"));
    });
    else {
      window.openCart?.(); window.renderCart();
      document.dispatchEvent(new CustomEvent("addedToCart"));
    }
  };

  function flyToCart(originEl, imgSrc, onDone) {
    const cartIconEl = document.querySelector(
      "#cartIconDesktop, .icons a[href='/cart.php'], .bottom-bar-item[href='/cart.php']"
    );
    if (!cartIconEl) { onDone(); return; }
    const or = originEl.getBoundingClientRect();
    const cr = cartIconEl.getBoundingClientRect();
    const fly = document.createElement("img");
    fly.className = "fly-item"; fly.src = imgSrc;
    const startX = or.left + or.width  / 2 - 30;
    const startY = or.top  + or.height / 2 - 30;
    const endX   = cr.left + cr.width  / 2 - 30;
    const endY   = cr.top  + cr.height / 2 - 30;
    fly.style.left = startX + "px"; fly.style.top = startY + "px";
    document.body.appendChild(fly);
    const dur = 1100; const t0 = performance.now();
    (function step(now) {
      const t    = Math.min((now - t0) / dur, 1);
      const ease = t < .5 ? 4*t*t*t : 1 - Math.pow(-2*t+2,3)/2;
      const arc  = -160 * Math.sin(Math.PI * t);
      const scale   = 1 - 0.55 * ease;
      const opacity = t < 0.75 ? 1 : 1 - ((t - 0.75) / 0.25);
      fly.style.transform = `translate(${(endX-startX)*ease}px,${(endY-startY)*ease+arc}px) scale(${scale})`;
      fly.style.opacity   = opacity;
      if (t < 1) requestAnimationFrame(step);
      else { fly.remove(); bumpCartBadge(); onDone(); }
    })(t0);
  }

  // ─── PANIER — RENDU ──────────────────────────────────
  window.renderCart = () => {
    const cartItemsEl = document.getElementById("cartItems");
    if (!cartItemsEl) return;
    cartItemsEl.innerHTML = "";
    if (!Object.keys(cart).length) {
      cartItemsEl.innerHTML = "<p>Votre panier est vide.</p>";
      updateCartTotal(); return;
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
        </div>`;
      div.querySelector(".increase").onclick = () => { item.quantity++; saveCart(); updateCartTotal(); window.renderCart(); };
      div.querySelector(".decrease").onclick = () => {
        item.quantity--;
        if (item.quantity <= 0) delete cart[key];
        saveCart(); updateCartTotal(); window.renderCart();
      };
      div.querySelector(".remove-item").onclick = () => { delete cart[key]; saveCart(); updateCartTotal(); window.renderCart(); };
      cartItemsEl.appendChild(div);
    });
    updateCartTotal();
  };

  // ─── WISHLIST ────────────────────────────────────────
  document.body.addEventListener("click", e => {
    const btn = e.target.closest(".add-to-wishlist");
    if (!btn) return;
    const productId = btn.dataset.productId;
    wishlist[productId] = {
      productId, name: btn.dataset.name,
      price: parseFloat((btn.dataset.price || "0").replace(",", ".")),
      image_url: normalizeImage(btn.dataset.image_url),
      hasShades: btn.dataset.hasShades === "1"
    };
    saveWishlist();
    alert("Produit ajouté à la wishlist !");
  });

  function renderWishlist() {
    const container = document.getElementById("wishlistItems");
    if (!container) return;
    container.innerHTML = "";
    const wl = JSON.parse(localStorage.getItem("wishlist")) || {};
    if (!Object.keys(wl).length) { container.innerHTML = "<p>Votre wishlist est vide.</p>"; return; }
    Object.values(wl).forEach(item => {
      const div = document.createElement("div");
      div.className = "wishlist-item";
      div.innerHTML = `
        <img src="${normalizeImage(item.image_url)}" alt="${item.name}" class="wishlist-item-img">
        <h4>${item.name}${item.shade ? " — " + item.shade : ""}</h4>
        <p>${item.price.toFixed(2)} DA</p>
        <button class="remove-wishlist" data-product-id="${item.productId}">Supprimer</button>
        <button class="add-to-cart-wishlist"
          data-product-id="${item.productId}" data-name="${item.name}"
          data-price="${item.price}" data-image_url="${item.image_url}"
          data-has-shades="${item.hasShades ? 1 : 0}">Ajouter au panier</button>`;
      container.appendChild(div);
    });
  }

  // ═══════════════════════════════════════════════════════
  // ─── MODAL TEINTES ────────────────────────────────────
  // ═══════════════════════════════════════════════════════

  const modal = document.getElementById("productModal");

  async function openShadeModal(button) {
    if (!modal) return;

    const data = getProductData(button);
    if (!data) return;

    let currentProduct  = data;
    let selectedShade   = null;
    let currentQuantity = 1;

    const productNameEl   = modal.querySelector("#productName");
    const productPriceEl  = modal.querySelector("#productPrice");
    const productImageEl  = modal.querySelector("#productMainImage");
    const shadeOptionsEl  = modal.querySelector("#shadeOptions");
    const qtyEl           = modal.querySelector("#quantity");
    const thumbsEl        = modal.querySelector("#productThumbnails");
    const shadeNameEl     = modal.querySelector("#selectedShadeName");

    // Reset
    selectedShade = null; currentQuantity = 1;
    if (qtyEl)        qtyEl.textContent       = 1;
    if (shadeNameEl)  shadeNameEl.textContent  = "—";
    if (thumbsEl)     thumbsEl.innerHTML       = "";
    if (shadeOptionsEl) shadeOptionsEl.innerHTML = "<p>Chargement…</p>";

    const viewLink = document.getElementById("viewFullDetails");
    if (viewLink) viewLink.href = B + "/product.php?id=" + data.productId;

    if (productNameEl)  productNameEl.textContent  = data.name;
    if (productPriceEl) productPriceEl.textContent = data.price.toFixed(2) + " DA";
    if (productImageEl) { productImageEl.src = data.image; productImageEl.style.opacity = "1"; }

    // Quantité
    ["increaseQty", "decreaseQty"].forEach(id => {
      const old = modal.querySelector("#" + id);
      if (!old) return;
      const neo = old.cloneNode(true); old.replaceWith(neo);
      neo.addEventListener("click", () => {
        if (id === "increaseQty") currentQuantity++;
        else if (currentQuantity > 1) currentQuantity--;
        if (qtyEl) qtyEl.textContent = currentQuantity;
      });
    });

    // Bouton Ajouter — clone
    const addOld = modal.querySelector("#addToCartFromModal");
    const addBtn = addOld.cloneNode(true);
    addOld.replaceWith(addBtn);
    addBtn.disabled = true;
    addBtn.textContent = "Choisissez une teinte";

    addBtn.addEventListener("click", () => {
      if (!selectedShade || addBtn.disabled) return;
      const imgToSend = (productImageEl && productImageEl.src) ? productImageEl.src : currentProduct.image;
      window.addToCart({
        productId : currentProduct.productId,
        name      : currentProduct.name,
        price     : currentProduct.price,
        image     : imgToSend,
        quantity  : currentQuantity,
        shade     : selectedShade
      });
      if (modal.dataset.fromWishlist === "1") {
        const wl = JSON.parse(localStorage.getItem("wishlist") || "{}");
        const key = modal.dataset.wishlistKey;
        if (wl[key]) { delete wl[key]; localStorage.setItem("wishlist", JSON.stringify(wl)); }
        renderWishlist();
      }
      closeModal();
    });

    modal.dataset.fromWishlist = button.dataset.fromWishlist || "";
    modal.dataset.wishlistKey  = button.dataset.wishlistKey  || "";
    openModal();

    // Charger teintes
    try {
      const res    = await fetch(B + "/includes/get_shades.php?product_id=" + button.dataset.productId);
      const shades = await res.json();

      if (shadeOptionsEl) shadeOptionsEl.innerHTML = "";

      if (!shades.length) {
        if (shadeOptionsEl) shadeOptionsEl.innerHTML = "<p>Aucune teinte disponible.</p>";
        addBtn.disabled = false;
        addBtn.textContent = "Ajouter au panier";
        return;
      }

      shades.forEach(s => {
        const imgSrc = resolveShadeImg(s);
        const isOos  = parseInt(s.stock, 10) === 0;

        const option = document.createElement("div");
        option.className = "shade-option" + (isOos ? " shade-oos" : "");
        option.title     = s.nom_teinte + (isOos ? " (rupture)" : "");

        if (imgSrc) {
          const img = document.createElement("img");
          img.className = "shade-thumb";
          img.src       = imgSrc;
          img.alt       = s.nom_teinte;
          option.appendChild(img);
        } else {
          const dot = document.createElement("span");
          dot.className        = "shade-color-dot";
          dot.style.background = s.code_couleur || "#ccc";
          option.appendChild(dot);
        }

        option.addEventListener("click", () => {
          if (isOos) return;
          shadeOptionsEl.querySelectorAll(".shade-option").forEach(o => o.classList.remove("selected"));
          option.classList.add("selected");
          selectedShade = s.nom_teinte;
          if (shadeNameEl) shadeNameEl.textContent = s.nom_teinte;

          // Changer image principale avec fondu
          if (imgSrc && productImageEl) {
            productImageEl.style.opacity = "0";
            setTimeout(() => { productImageEl.src = imgSrc; productImageEl.style.opacity = "1"; }, 180);
          }

          // Activer bouton
          addBtn.disabled = false;
          addBtn.innerHTML = '<i class="fas fa-shopping-bag" style="margin-right:8px"></i>Ajouter au panier';
        });

        shadeOptionsEl.appendChild(option);
      });

    } catch (err) {
      console.error("Erreur teintes modal", err);
      if (shadeOptionsEl) shadeOptionsEl.innerHTML = "<p>Erreur de chargement.</p>";
    }
  }

  function openModal() {
    if (!modal) return;
    modal.style.display = "flex";
    document.body.style.overflow = "hidden";
  }
  function closeModal() {
    if (!modal) return;
    modal.style.display = "none";
    document.body.style.overflow = "auto";
  }

  if (modal) {
    modal.querySelector(".close-product-modal")?.addEventListener("click", closeModal);
    modal.addEventListener("click", e => { if (e.target === modal) closeModal(); });
    document.addEventListener("keydown", e => { if (e.key === "Escape") closeModal(); });
  }

  // ═══════════════════════════════════════════════════════
  // ─── DISPATCHER choose-shade-btn ──────────────────────
  // ═══════════════════════════════════════════════════════

  document.body.addEventListener("click", async e => {
    const shadeBtn = e.target.closest(".choose-shade-btn");
    if (!shadeBtn) return;
    if (shadeBtn.closest("#productModal") || shadeBtn.closest(".qv-modal")) return;
    e.preventDefault(); e.stopPropagation();
    if (isOutOfStock(shadeBtn)) return;

    const fromWishlist = shadeBtn.dataset.fromWishlist === "1";
    const inCatalogue  = isCataloguePage && !fromWishlist;

    if (inCatalogue) {
      // ── Picker inline ────────────────────────────────
      const card        = shadeBtn.closest(".product-card");
      const productInfo = card?.querySelector(".product-info");
      if (!productInfo || productInfo.querySelector(".inline-shade-picker")) return;

      const productId = shadeBtn.dataset.productId;
      const name      = shadeBtn.dataset.name;
      const price     = parseFloat((shadeBtn.dataset.price || "0").replace(",", "."));
      const imageRaw  = shadeBtn.dataset.image_url || shadeBtn.dataset.imageUrl;
      const image     = normalizeImage(imageRaw);

      shadeBtn.style.display = "none";

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
        </div>`;
      productInfo.appendChild(picker);

      const dotsEl       = picker.querySelector(".isp-dots");
      const selectedName = picker.querySelector(".isp-selected-name");
      const addBtn       = picker.querySelector(".isp-add-btn");
      const cancelBtn    = picker.querySelector(".isp-cancel");

      cancelBtn.addEventListener("click", e => {
        e.preventDefault(); e.stopPropagation();
        picker.remove(); shadeBtn.style.display = "";
      });

      dotsEl.innerHTML = `<span class="isp-loading">Chargement…</span>`;
      let shades = [];
      try {
        const res = await fetch(B + "/includes/get_shades.php?product_id=" + productId);
        shades    = await res.json();
      } catch {
        dotsEl.innerHTML = `<span class="isp-loading">Erreur de chargement.</span>`; return;
      }

      dotsEl.innerHTML = "";
      let selectedShade = null;

      if (!shades.length) {
        dotsEl.innerHTML = `<span class="isp-loading">Aucune teinte.</span>`;
        addBtn.disabled  = false;
      } else {
        shades.forEach(s => {
          const imgSrc = resolveShadeImg(s);
          const isOos  = parseInt(s.stock, 10) === 0;

          const dot = document.createElement("span");
          dot.className = "isp-dot" + (isOos ? " isp-dot--oos" : "");
          dot.title     = s.nom_teinte + (isOos ? " (rupture)" : "");
          dot.style.position = "relative";
          dot.style.overflow = "hidden";

          if (imgSrc) {
            const img = document.createElement("img");
            img.src   = imgSrc;
            img.alt   = s.nom_teinte;
            img.style.cssText = "width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;";
            dot.appendChild(img);
          } else {
            dot.style.background = s.code_couleur || "#ccc";
          }

          dot.addEventListener("click", e => {
            e.preventDefault(); e.stopPropagation();
            if (isOos) return;
            dotsEl.querySelectorAll(".isp-dot").forEach(d => d.classList.remove("active"));
            dot.classList.add("active");
            selectedShade            = s.nom_teinte;
            selectedName.textContent = s.nom_teinte;
            addBtn.disabled          = false;
          });
          dotsEl.appendChild(dot);
        });
      }

      addBtn.addEventListener("click", e => {
        e.preventDefault(); e.stopPropagation();
        const imgEl = card?.querySelector("img");
        addToCart({ productId, name, price, image, quantity: 1, shade: selectedShade, _originEl: imgEl || addBtn });
        picker.remove(); shadeBtn.style.display = "";
      });

    } else {
      openShadeModal(shadeBtn);
    }
  });

  // ═══════════════════════════════════════════════════════
  // ─── QUICK VIEW ───────────────────────────────────────
  // ═══════════════════════════════════════════════════════

  (function setupQuickViewCart() {
    const overlay   = document.getElementById("qvOverlay");
    if (!overlay) return;
    const closeBtn  = document.getElementById("qvClose");
    const shadesRow = document.getElementById("qvShadesRow");

    let qvState = { productId:null, name:null, price:null, image:null, stock:0, hasShades:false, selectedShade:null };

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

      qvState = { productId:id, name, price, image:normalizeImage(image), stock, hasShades, selectedShade:null };

      const imgEl = document.getElementById("qvImg");
      imgEl.src = ""; requestAnimationFrame(() => { imgEl.src = image; imgEl.alt = name; });

      document.getElementById("qvBrand").textContent       = brand || "";
      document.getElementById("qvName").textContent        = name;
      document.getElementById("qvDescription").textContent = desc;

      const priceEl = document.getElementById("qvPrice");
      priceEl.innerHTML = (!isNaN(oldPrice) && oldPrice > price)
        ? `<span class="qv-old">${fmtDA(oldPrice)}</span><span class="qv-current">${fmtDA(price)}</span>`
        : `<span class="qv-normal">${fmtDA(price)}</span>`;

      const badge      = document.getElementById("qvBadge");
      const stockDot   = document.getElementById("qvStockDot");
      const stockLabel = document.getElementById("qvStockLabel");
      if (stock === 0) {
        badge.textContent = "Rupture"; badge.className = "qv-badge qv-badge--oos";
        stockDot.className = "qv-stock-dot qv-dot--out"; stockLabel.textContent = "Rupture de stock";
      } else if (stock <= 5) {
        badge.textContent = "Stock limité"; badge.className = "qv-badge qv-badge--low";
        stockDot.className = "qv-stock-dot qv-dot--low";
        stockLabel.textContent = `Seulement ${stock} restant${stock > 1 ? "s" : ""}`;
      } else {
        badge.textContent = ""; badge.className = "qv-badge";
        stockDot.className = "qv-stock-dot qv-dot--in"; stockLabel.textContent = "En stock";
      }

      document.getElementById("qvDetailLink").href = url;

      const shadesBlock  = document.getElementById("qvShadesBlock");
      const shadeNameEl  = document.getElementById("qvSelectedShadeName");
      shadesRow.innerHTML = "";
      if (shadeNameEl) shadeNameEl.textContent = "";

      let shades = [];
      try { shades = JSON.parse(btn.dataset.shades || "[]"); } catch {}

      if (hasShades && shades.length) {
        shades.forEach(s => {
          const imgSrc = resolveShadeImg(s);
          const isOos  = parseInt(s.stock, 10) === 0;

          const dot = document.createElement("span");
          dot.className = "qv-shade-dot" + (isOos ? " qv-shade-dot--oos" : "");
          dot.title     = s.nom_teinte + (isOos ? " (rupture)" : "");
          dot.style.cssText = "position:relative;overflow:hidden;display:inline-block;";

          if (imgSrc) {
            const img = document.createElement("img");
            img.src   = imgSrc; img.alt = s.nom_teinte;
            img.style.cssText = "width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;";
            dot.appendChild(img);
          } else {
            dot.style.background = s.code_couleur || "#ccc";
          }

          dot.addEventListener("click", () => {
            if (isOos) return;
            shadesRow.querySelectorAll(".qv-shade-dot").forEach(d => d.classList.remove("active"));
            dot.classList.add("active");
            qvState.selectedShade = s.nom_teinte;
            if (shadeNameEl) shadeNameEl.textContent = s.nom_teinte;
            // Changer image QV si la teinte en a une
            if (imgSrc) { imgEl.style.opacity="0"; setTimeout(() => { imgEl.src=imgSrc; imgEl.style.opacity="1"; }, 150); }
            updateQvCartBtn();
          });
          shadesRow.appendChild(dot);
        });
        shadesBlock.style.display = "flex";
      } else {
        shadesBlock.style.display = "none";
      }

      // Bouton panier QV — clone
      const cartBtn    = document.getElementById("qvCartBtn");
      const newCartBtn = cartBtn.cloneNode(true);
      cartBtn.replaceWith(newCartBtn);

      function updateQvCartBtn() {
        const { stock, hasShades, selectedShade } = qvState;
        const needsShade = hasShades && !selectedShade;
        newCartBtn.disabled = stock === 0 || needsShade;
        newCartBtn.innerHTML = stock === 0
          ? '<i class="fas fa-ban"></i> Rupture de stock'
          : needsShade
            ? '<i class="fas fa-palette"></i> Sélectionnez une teinte'
            : '<i class="fas fa-shopping-bag"></i> Ajouter au panier';
      }
      updateQvCartBtn();

      newCartBtn.addEventListener("click", () => {
        const { productId, name, price, image, stock, hasShades, selectedShade } = qvState;
        if (stock === 0) return;
        if (hasShades && !selectedShade) {
          shadesBlock?.classList.add("qv-shake");
          setTimeout(() => shadesBlock?.classList.remove("qv-shake"), 500); return;
        }
        const qvImg = document.getElementById("qvImg");
        addToCart({ productId, name, price, image, quantity:1, shade:selectedShade, _originEl: qvImg });
        closeQV();
      });

      overlay.classList.add("active");
      document.body.style.overflow = "hidden";
      closeBtn.focus();
    };

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

  // Bind quick-view buttons
  document.querySelectorAll(".quick-view-btn").forEach(btn => {
    btn.addEventListener("click", e => { e.preventDefault(); e.stopPropagation(); window._qvOpenCallback?.(btn); });
  });

  // ─── AJOUT PANIER DIRECT ─────────────────────────────
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
    const imgEl    = addBtn.closest(".product-card")?.querySelector("img");
    addToCart({ ...data, quantity, _originEl: imgEl || addBtn });
    if (qtyInput) qtyInput.value = 1;
  });

  // ─── CHECKOUT ────────────────────────────────────────
  document.body.addEventListener("click", e => {
    if (e.target.closest(".checkoutBtn, #checkoutBtn, [data-action='checkout']")) {
      e.preventDefault();
      const cartData = JSON.parse(localStorage.getItem("cart") || "{}");
      if (!Object.keys(cartData).length) {
        window.SheGlamCheckout?.notify("Panier vide", "Ajoutez des produits avant de commander.", "warning"); return;
      }
      window.SheGlamCheckout?.open();
    }
  });

  window.renderCart();
  updateCartTotal();
  bumpCartBadge();
});

// ─── FILTRES catalogue ────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  const sortPrice   = document.getElementById("sortPrice");
  const filterSale  = document.getElementById("filterSale");
  const filterStock = document.getElementById("filterInStock");
  const filterBrand = document.getElementById("filterBrand");
  const grid        = document.querySelector(".products-grid");
  if (!sortPrice || !filterSale || !filterBrand || !grid) return;

  const cards = [...document.querySelectorAll(".product-card")];
  const brands = [...new Set(cards.map(c => c.dataset.brand).filter(Boolean))];
  brands.forEach(b => {
    const opt = document.createElement("option");
    opt.value = b; opt.textContent = b; filterBrand.appendChild(opt);
  });

  function applyFilters() {
    let visible = cards.filter(card => {
      if (filterSale.checked   && card.dataset.sale    !== "1") return false;
      if (filterStock?.checked && card.dataset.instock === "0") return false;
      if (filterBrand.value    && card.dataset.brand   !== filterBrand.value) return false;
      return true;
    });
    if (sortPrice.value) {
      visible.sort((a, b) => {
        const pa = parseFloat(a.dataset.price), pb = parseFloat(b.dataset.price);
        return sortPrice.value === "asc" ? pa - pb : pb - pa;
      });
    }
    grid.innerHTML = "";
    visible.forEach(card => grid.appendChild(card.closest("a") || card));
  }

  [sortPrice, filterSale, filterBrand, filterStock].filter(Boolean)
    .forEach(el => el.addEventListener("change", applyFilters));
});

// ─── FORMAT DA ────────────────────────────────────────
function fmtDA(v) {
  return Number(v).toLocaleString("fr-DZ", { minimumFractionDigits: 2 }) + " DA";
}
window.bumpCartBadge = bumpCartBadge;