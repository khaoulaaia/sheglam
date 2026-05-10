<!-- includes/product_modal.php -->
<!-- PRODUCT MODAL -->
<div id="productModal" class="product-modal" aria-hidden="true">
  <div class="product-modal-content">

    <button class="close-product-modal" aria-label="Fermer">&times;</button>

    <!-- LEFT -->
    <div class="product-modal-left">
      <div class="thumbnails" id="productThumbnails"></div>

      <div class="main-image">
        <img id="productMainImage" src="" alt="Produit">
      </div>
    </div>

    <!-- RIGHT -->
    <div class="product-modal-right">
      <h2 id="productName"></h2>
      <p id="productPrice" class="price"></p>

      <!-- SHADES -->
      <div id="shadeSection">
        <strong>Couleur :</strong>
        <span id="selectedShadeName"></span>

        <div id="shadeOptions" class="shade-options"></div>
      </div>

      <!-- QUANTITY -->
      <div class="quantity-selector">
        <button id="decreaseQty" type="button">−</button>
        <span id="quantity">1</span>
        <button id="increaseQty" type="button">+</button>
      </div>

      <!-- ACTIONS -->
      <button id="addToCartFromModal" class="btn-primary">
        Ajouter au panier
      </button>

      <a id="viewFullDetails" class="view-details-link" href="#">
        Voir la fiche produit
      </a>
    </div>

  </div>
</div>

<style>/* ═══════════════════════════════════
   PRODUCT MODAL
═══════════════════════════════════ */
/* ═══════════════════════════════════
   PRODUCT MODAL
═══════════════════════════════════ */

.product-modal {
  position: fixed;
  inset: 0;

  display: none;
  align-items: center;
  justify-content: center;

  padding: 24px;

  background: var(--dark-80);

  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);

  z-index: 5000;
}

/* ═══════════════════════════════════
   MODAL CONTENT
═══════════════════════════════════ */

.product-modal-content {
  position: relative;

  width: 100%;
  max-width: 1040px;
  max-height: 92vh;

  overflow-y: auto;

  display: flex;
  gap: 42px;

  padding: 34px;

  border-radius: 26px;

  background: var(--white);

  border: 1px solid var(--border);

  box-shadow: 0 30px 80px var(--dark-20);
}

/* ═══════════════════════════════════
   CLOSE BUTTON
═══════════════════════════════════ */

.close-product-modal {
  position: absolute;
  top: 20px;
  right: 22px;

  border: none;
  background: transparent;

  color: var(--dark-40);

  font-size: 28px;
  font-weight: 300;
  line-height: 1;

  cursor: pointer;

  transition:
    color 0.25s var(--ease),
    transform 0.25s var(--ease);
}

.close-product-modal:hover {
  color: var(--bordeaux);
  transform: rotate(90deg);
}

/* ═══════════════════════════════════
   LEFT SIDE
═══════════════════════════════════ */

.product-modal-left {
  flex: 1;

  display: flex;
  gap: 16px;
}

/* THUMBNAILS */

.thumbnails {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.thumbnails img {
  width: 72px;
  height: 72px;

  object-fit: cover;

  border-radius: 14px;

  border: 1px solid transparent;

  background: var(--dark-06);

  cursor: pointer;

  transition:
    border-color 0.25s var(--ease),
    transform 0.25s var(--ease),
    box-shadow 0.25s var(--ease);
}

.thumbnails img:hover {
  transform: translateY(-2px);
}

.thumbnails img.active {
  border-color: var(--bordeaux);

  box-shadow: 0 8px 20px var(--dark-20);
}

/* MAIN IMAGE */

.main-image {
  flex: 1;

  display: flex;
  align-items: center;
  justify-content: center;

  background: linear-gradient(
    180deg,
    var(--white),
    var(--dark-06)
  );

  border-radius: 22px;

  padding: 24px;

  min-height: 500px;
}

.main-image img {
  width: 100%;
  max-width: 430px;

  object-fit: contain;
}

/* ═══════════════════════════════════
   RIGHT SIDE
═══════════════════════════════════ */

.product-modal-right {
  flex: 1;

  display: flex;
  flex-direction: column;
  justify-content: center;
}

/* TITLE */

.product-modal-right h2 {
  font-family: var(--serif);
  font-size: 2rem;
  font-weight: 600;

  line-height: 1.1;

  color: var(--dark);

  margin-bottom: 10px;
}

/* PRICE */

.product-modal-right .price {
  font-size: 1.1rem;
  font-weight: 500;

  letter-spacing: .04em;

  color: var(--bordeaux-s);

  margin-bottom: 28px;
}

/* ═══════════════════════════════════
   SHADES
═══════════════════════════════════ */

.shade-options {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;

  margin: 12px 0 28px;
}

.shade-option {
  width: 42px;
  height: 42px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 50%;

  background: var(--white);

  border: 1px solid var(--border);

  cursor: pointer;

  transition:
    border-color 0.25s var(--ease),
    transform 0.25s var(--ease),
    box-shadow 0.25s var(--ease);
}

.shade-option:hover {
  transform: translateY(-2px);
}

.shade-option.selected {
  border: 2px solid var(--bordeaux);

  box-shadow: 0 10px 20px var(--dark-20);
}

.shade-color {
  width: 28px;
  height: 28px;

  border-radius: 50%;

  border: 1px solid var(--dark-12);
}

/* ═══════════════════════════════════
   QUANTITY
═══════════════════════════════════ */

.quantity-selector {
  display: flex;
  align-items: center;
  gap: 18px;

  margin-bottom: 30px;
}

.quantity-selector button {
  width: 38px;
  height: 38px;

  border: 1px solid var(--border);

  border-radius: 12px;

  background: var(--dark-06);

  color: var(--bordeaux-s);

  font-size: 20px;

  cursor: pointer;

  transition:
    background 0.25s var(--ease),
    color 0.25s var(--ease),
    transform 0.25s var(--ease);
}

.quantity-selector button:hover {
  background: var(--bordeaux);
  color: var(--white);

  transform: translateY(-2px);
}

.quantity-selector span {
  min-width: 22px;

  text-align: center;

  font-size: 1rem;
  font-weight: 600;

  color: var(--dark);
}

/* ═══════════════════════════════════
   BUTTON
═══════════════════════════════════ */

.btn-primary {
  width: 100%;

  padding: 16px;

  border: none;
  border-radius: 14px;

  background: linear-gradient(
    135deg,
    var(--bordeaux-l),
    var(--bordeaux)
  );

  color: var(--white);

  font-family: var(--sans);
  font-size: .78rem;
  font-weight: 600;

  letter-spacing: .16em;
  text-transform: uppercase;

  cursor: pointer;

  transition:
    transform 0.25s var(--ease),
    box-shadow 0.25s var(--ease),
    opacity 0.25s var(--ease);

  box-shadow: 0 18px 34px var(--dark-20);

  margin-bottom: 16px;
}

.btn-primary:hover {
  transform: translateY(-3px);

  box-shadow: 0 24px 40px var(--dark-40);
}

/* ═══════════════════════════════════
   DETAILS LINK
═══════════════════════════════════ */

.view-details-link {
  text-align: center;

  font-size: .78rem;
  letter-spacing: .05em;

  color: var(--muted);

  text-decoration: underline;

  transition: color 0.25s var(--ease);
}

.view-details-link:hover {
  color: var(--bordeaux);
}

/* ═══════════════════════════════════
   MOBILE
═══════════════════════════════════ */

@media (max-width: 900px) {

  .product-modal {
    padding: 0;
    align-items: flex-end;
  }

  .product-modal-content {
    width: 100%;
    max-width: 100%;
    max-height: 94vh;

    border-radius: 28px 28px 0 0;

    padding: 22px;

    gap: 26px;

    flex-direction: column;
  }

  .product-modal-left {
    flex-direction: column-reverse;
  }

  .thumbnails {
    flex-direction: row;
    overflow-x: auto;

    padding-bottom: 2px;
  }

  .thumbnails img {
    width: 62px;
    height: 62px;

    flex-shrink: 0;
  }

  .main-image {
    min-height: 320px;
    padding: 18px;
  }

  .main-image img {
    max-width: 260px;
  }

  .product-modal-right h2 {
    font-size: 1.45rem;
  }

  .product-modal-right .price {
    font-size: 1rem;
    margin-bottom: 22px;
  }

  .shade-options {
    gap: 10px;
    margin-bottom: 24px;
  }

  .shade-option {
    width: 38px;
    height: 38px;
  }

  .shade-color {
    width: 24px;
    height: 24px;
  }

  .btn-primary {
    padding: 15px;
    font-size: .72rem;
  }

  .close-product-modal {
    top: 18px;
    right: 18px;
  }
}
</style>