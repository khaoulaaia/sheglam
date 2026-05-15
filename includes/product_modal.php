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
   PALETTE
   #F5F1EE  — crème ivoire (fond clair)
   #440B19  — bordeaux profond (accent)
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

  background: rgba(68, 11, 25, 0.82);   /* #440B19 à 82 % */
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

  background: #F5F1EE;                  /* crème ivoire */
  border: 1px solid rgba(68, 11, 25, 0.14);
  box-shadow: 0 30px 80px rgba(68, 11, 25, 0.22);
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

  color: rgba(68, 11, 25, 0.40);        /* bordeaux à 40 % */
  font-size: 28px;
  font-weight: 300;
  line-height: 1;

  cursor: pointer;
  transition:
    color 0.25s ease,
    transform 0.25s ease;
}

.close-product-modal:hover {
  color: #440B19;
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
  border: 1px solid transparent;
  background: rgba(68, 11, 25, 0.06);
  cursor: pointer;
  transition:
    border-color 0.25s ease,
    transform 0.25s ease,
    box-shadow 0.25s ease;
}

.thumbnails img:hover {
  transform: translateY(-2px);
}

.thumbnails img.active {
  border-color: #440B19;
  box-shadow: 0 8px 20px rgba(68, 11, 25, 0.22);
}

/* MAIN IMAGE */

.main-image {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;

  background: linear-gradient(
    180deg,
    #F5F1EE,
    rgba(68, 11, 25, 0.05)
  );

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
  font-family: Georgia, "Times New Roman", serif;
  font-size: 2rem;
  font-weight: 600;
  line-height: 1.1;
  color: #1a0509;                        /* quasi-noir teinté bordeaux */
  margin-bottom: 10px;
}

/* PRICE */

.product-modal-right .price {
  font-size: 1.1rem;
  font-weight: 500;
  letter-spacing: .04em;
  color: #5c1225;                        /* bordeaux légèrement éclairci */
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
  background: #F5F1EE;
  border: 1px solid rgba(68, 11, 25, 0.14);
  cursor: pointer;
  transition:
    border-color 0.25s ease,
    transform 0.25s ease,
    box-shadow 0.25s ease;
}

.shade-option:hover {
  transform: translateY(-2px);
}

.shade-option.selected {
  border: 2px solid #440B19;
  box-shadow: 0 10px 20px rgba(68, 11, 25, 0.22);
}

.shade-color {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 1px solid rgba(68, 11, 25, 0.12);
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
  border: 1px solid rgba(68, 11, 25, 0.18);
  background: rgba(68, 11, 25, 0.06);
  color: #5c1225;
  font-size: 20px;
  cursor: pointer;
  transition:
    background 0.25s ease,
    color 0.25s ease,
    transform 0.25s ease;
}

.quantity-selector button:hover {
  background: #440B19;
  color: #F5F1EE;
  transform: translateY(-2px);
}

.quantity-selector span {
  min-width: 22px;
  text-align: center;
  font-size: 1rem;
  font-weight: 600;
  color: #1a0509;
}

/* ═══════════════════════════════════
   BUTTON
═══════════════════════════════════ */

.btn-primary {
  width: 100%;
  padding: 16px;
  border: none;

  background: linear-gradient(
    135deg,
    #6b1a2e,                             /* bordeaux éclairci */
    #440B19                              /* bordeaux profond */
  );

  color: #F5F1EE;
  font-size: .78rem;
  font-weight: 600;
  letter-spacing: .16em;
  text-transform: uppercase;

  cursor: pointer;
  transition:
    transform 0.25s ease,
    box-shadow 0.25s ease,
    opacity 0.25s ease;

  box-shadow: 0 18px 34px rgba(68, 11, 25, 0.28);
  margin-bottom: 16px;
}

.btn-primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 24px 40px rgba(68, 11, 25, 0.42);
}

/* ═══════════════════════════════════
   DETAILS LINK
═══════════════════════════════════ */

.view-details-link {
  text-align: center;
  font-size: .78rem;
  letter-spacing: .05em;
  color: rgba(68, 11, 25, 0.50);
  text-decoration: underline;
  transition: color 0.25s ease;
}

.view-details-link:hover {
  color: #440B19;
}

/* ═══════════════════════════════════
   TABLET  (601 px → 900 px)
═══════════════════════════════════ */

@media (min-width: 601px) and (max-width: 900px) {

  .product-modal {
    padding: 16px;
    align-items: center;
  }

  .product-modal-content {
    max-width: 720px;
    max-height: 90vh;
    padding: 28px;
    gap: 28px;
    flex-direction: column;
  }

  .product-modal-left {
    flex-direction: column-reverse;
  }

  .thumbnails {
    flex-direction: row;
    overflow-x: auto;
    padding-bottom: 4px;
  }

  .thumbnails img {
    width: 64px;
    height: 64px;
    flex-shrink: 0;
  }

  .main-image {
    min-height: 360px;
    padding: 20px;
  }

  .main-image img {
    max-width: 300px;
  }

  .product-modal-right h2 {
    font-size: 1.65rem;
  }

  .product-modal-right .price {
    font-size: 1.05rem;
    margin-bottom: 22px;
  }

  .shade-options {
    gap: 10px;
    margin-bottom: 22px;
  }
}

/* ═══════════════════════════════════
   MOBILE  (≤ 600 px)
═══════════════════════════════════ */

@media (max-width: 600px) {

  .product-modal {
    padding: 0;
    align-items: flex-end;
  }

  .product-modal-content {
    width: 100%;
    max-width: 100%;
    max-height: 94vh;

    padding: 20px 16px 28px;
    gap: 20px;
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
    width: 58px;
    height: 58px;
    flex-shrink: 0;
  }

  .main-image {
    min-height: 280px;
    padding: 16px;
  }

  .main-image img {
    max-width: 220px;
  }

  .product-modal-right h2 {
    font-size: 1.35rem;
  }

  .product-modal-right .price {
    font-size: .95rem;
    margin-bottom: 18px;
  }

  .shade-options {
    gap: 8px;
    margin-bottom: 20px;
  }

  .shade-option {
    width: 36px;
    height: 36px;
  }

  .shade-color {
    width: 22px;
    height: 22px;
  }

  .quantity-selector {
    gap: 14px;
    margin-bottom: 22px;
  }

  .quantity-selector button {
    width: 36px;
    height: 36px;
    font-size: 18px;
  }

  .btn-primary {
    padding: 14px;
    font-size: .72rem;
  }

  .close-product-modal {
    top: 16px;
    right: 16px;
    font-size: 24px;
  }
}
</style>