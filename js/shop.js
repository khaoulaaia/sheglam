document.addEventListener("DOMContentLoaded", () => {
  console.log("✅ shop.js chargé");

  // --- Initialisation globale ---
  window.cart = JSON.parse(localStorage.getItem('cart')) || {};
  window.wishlist = JSON.parse(localStorage.getItem('wishlist')) || {};

  let selectedShade = null;
  let selectedProduct = null;

  function addToCart(name, price, image_url, shade = null) {
  const key = shade ? `${name} - ${shade}` : name;
  if (window.cart[key]) {
    window.cart[key].quantity += 1;
  } else {
    window.cart[key] = { name, price, image_url, shade, quantity: 1 };
  }
  localStorage.setItem('cart', JSON.stringify(window.cart));
  if (typeof window.renderCart === "function") window.renderCart();
  if (typeof window.openCart === "function") window.openCart();
}

function addToWishlist(name, price, image_url) {
  if (!window.wishlist[name]) {
    window.wishlist[name] = { name, price, image_url };
  }
  localStorage.setItem('wishlist', JSON.stringify(window.wishlist));
  if (typeof window.renderWishlist === "function") window.renderWishlist();
}


  // --- Boutons "Ajouter au panier" (sans teinte) ---
  document.body.addEventListener("click", e => {
    const btn = e.target.closest(".add-to-cart");
    if (!btn) return;

    const { name, price, image_url} = btn.dataset;
    addToCart(name, parseFloat(price), image_url);
  });

  // --- Boutons "Ajouter à la wishlist" ---
  document.body.addEventListener("click", e => {
    const btn = e.target.closest(".add-to-wishlist");
    if (!btn) return;

    const { name, price, image_url} = btn.dataset;
    addToWishlist(name, parseFloat(price), image_url);
  });

  // --- Gestion du modal de teinte ---
  const shadeModal = document.getElementById("shadeModal");
  const shadeProductImage = document.getElementById("shadeProductImage");
  const shadeProductName = document.getElementById("shadeProductName");
  const shadeProductPrice = document.getElementById("shadeProductPrice");
  const shadeOptionsContainer = document.getElementById("shade-options-container");
  const confirmShadeBtn = document.getElementById("confirmShadeBtn");
  const closeModalBtn = shadeModal?.querySelector(".close-modal");

  // Ouvrir le modal
  document.body.addEventListener("click", async e => {
    const button = e.target.closest(".choose-shade-btn");
    if (!button) return;

    const card = button.closest(".product-card");
    const productId = button.dataset.productId;
    const productName = card.querySelector("h3").textContent;
    const productPrice = card.querySelector(".price").textContent;
    const productImage = card.querySelector("img").src;

    selectedProduct = productName;
    selectedShade = null;

    shadeProductImage.src = productImage;
    shadeProductName.textContent = productName;
    shadeProductPrice.textContent = productPrice;
    shadeOptionsContainer.innerHTML = "<p>Chargement...</p>";

    try {
      const res = await fetch(`includes/get_shades.php?product_id=${productId}`);
      const shades = await res.json();

      shadeOptionsContainer.innerHTML = "";

      if (shades.length === 0) {
        shadeOptionsContainer.innerHTML = "<p>Aucune teinte disponible.</p>";
      } else {
        shades.forEach(shade => {
          const span = document.createElement("span");
          span.classList.add("shade-option");
          span.textContent = shade.nom_teinte;
          span.addEventListener("click", () => {
            document.querySelectorAll(".shade-option").forEach(s => s.classList.remove("selected"));
            span.classList.add("selected");
            selectedShade = shade.nom_teinte;
          });
          shadeOptionsContainer.appendChild(span);
        });
      }

      shadeModal.style.display = "flex";
      shadeModal.setAttribute("aria-hidden", "false");
    } catch (err) {
      console.error("Erreur de chargement des teintes :", err);
      shadeOptionsContainer.innerHTML = "<p>Erreur de chargement des teintes.</p>";
    }
  });

  // Fermer le modal
  closeModalBtn?.addEventListener("click", () => {
    shadeModal.style.display = "none";
    shadeModal.setAttribute("aria-hidden", "true");
  });
  shadeModal?.addEventListener("click", e => {
    if (e.target === shadeModal) {
      shadeModal.style.display = "none";
      shadeModal.setAttribute("aria-hidden", "true");
    }
  });

  // Ajouter au panier depuis le modal
  confirmShadeBtn?.addEventListener("click", () => {
    if (!selectedShade) {
      alert("Veuillez choisir une teinte avant d’ajouter au panier !");
      return;
    }

    const price = parseFloat(shadeProductPrice.textContent.replace(/[^\d,.-]/g, '').replace(',', '.')) || 0;
    const image = shadeProductImage.src;
    addToCart(selectedProduct, price, image, selectedShade);

    shadeModal.style.display = "none";
    shadeModal.setAttribute("aria-hidden", "true");
  });
});
