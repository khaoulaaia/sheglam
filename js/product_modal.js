document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("productModal");
  if (!modal) return; // sécurité si le modal n'est pas sur la page

  const closeBtn = modal.querySelector(".close-product-modal");
  const productNameEl = document.getElementById("productName");
  const productPriceEl = document.getElementById("productPrice");
  const productMainImage = document.getElementById("productMainImage");
  const shadeOptionsContainer = document.getElementById("shadeOptions");
  const selectedShadeName = document.getElementById("selectedShadeName");
  const addToCartBtn = document.getElementById("addToCartFromModal");

  let selectedShade = null;
  let selectedProduct = null;
  let selectedPrice = 0;
  let selectedImage = "";

  // --- Ouvrir le modal quand on clique sur "Choisir une teinte"
  document.addEventListener("click", async (e) => {
    if (e.target.classList.contains("choose-shade-btn")) {
      const btn = e.target;
      const productId = btn.dataset.productId;
      const card = btn.closest(".product-card");

      selectedProduct = card.querySelector("h3").textContent;
      selectedPrice = parseFloat(card.querySelector(".price").textContent.replace(/[^\d,.-]/g, '').replace(',', '.')) || 0;
      selectedImage = card.querySelector("img").src;

      // Remplir le contenu du modal
      productNameEl.textContent = selectedProduct;
      productPriceEl.textContent = "€" + selectedPrice.toFixed(2);
      productMainImage.src = selectedImage;
      shadeOptionsContainer.innerHTML = "";
      selectedShadeName.textContent = "-";

      try {
        const res = await fetch(`/sheglam/includes/get_shades.php?product_id=${productId}`);
        const shades = await res.json();

        if (shades.length === 0) {
          shadeOptionsContainer.innerHTML = "<p>Aucune teinte disponible.</p>";
        } else {
          shades.forEach(shade => {
  const div = document.createElement("div");
  div.classList.add("shade-option");
  div.innerHTML = `
    <span class="shade-color" style="background-color:${shade.code_couleur};"></span>
    <span class="shade-name">${shade.nom_teinte}</span>
  `;

  div.addEventListener("click", () => {
    document.querySelectorAll(".shade-option").forEach(opt => opt.classList.remove("selected"));
    div.classList.add("selected");
    selectedShade = shade.nom_teinte;
  });

  shadeOptionsContainer.appendChild(div);
});

        }

        modal.style.display = "flex";
        modal.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden"; // empêche le scroll

      } catch (err) {
        console.error("Erreur lors du chargement des teintes :", err);
      }
    }
  });

  // --- Fermer le modal
  const closeModal = () => {
    modal.style.display = "none";
    modal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "auto";
  };
  closeBtn.addEventListener("click", closeModal);
  modal.addEventListener("click", e => { if (e.target === modal) closeModal(); });

  // --- Ajouter au panier
  addToCartBtn.addEventListener("click", () => {
    if (!selectedShade) {
      alert("Veuillez choisir une teinte avant d’ajouter au panier !");
      return;
    }

    if (!window.cart) window.cart = JSON.parse(localStorage.getItem('cart')) || {};
    const key = `${selectedProduct} - ${selectedShade}`;

    if (window.cart[key]) {
      window.cart[key].quantity += 1;
    } else {
      window.cart[key] = {
        name: selectedProduct,
        price: selectedPrice,
        image: selectedImage,
        shade: selectedShade,
        quantity: 1
      };
    }

    localStorage.setItem('cart', JSON.stringify(window.cart));
    if (window.renderCart) window.renderCart();
    if (window.openCart) window.openCart();
    closeModal();
  });
});
