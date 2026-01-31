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
        <span id="selectedShadeName">—</span>

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

<style>
  /* ===============================
   PRODUCT MODAL
================================ */

.product-modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.55);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 5000;
}

.product-modal-content {
  background: #fff;
  width: 90%;
  max-width: 960px;
  display: flex;
  gap: 32px;
  padding: 32px;
  position: relative;
  border-radius: 10px;
}

/* CLOSE */
.close-product-modal {
  position: absolute;
  top: 16px;
  right: 18px;
  font-size: 26px;
  background: none;
  border: none;
  cursor: pointer;
}

/* LEFT */
.product-modal-left {
  flex: 1;
  display: flex;
  gap: 12px;
}

.thumbnails {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.thumbnails img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  cursor: pointer;
  border: 1px solid transparent;
}

.thumbnails img.active {
  border-color: #111;
}

.main-image img {
  width: 100%;
  max-width: 420px;
}

/* RIGHT */
.product-modal-right {
  flex: 1;
}

.product-modal-right h2 {
  font-size: 22px;
  margin-bottom: 6px;
}

.product-modal-right .price {
  font-size: 18px;
  margin-bottom: 14px;
}

/* SHADES */
.shade-options {
  display: flex;
  gap: 10px;
  margin: 10px 0 20px;
}

.shade-option {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 2px solid #ccc;
  cursor: pointer;
}

.shade-option.selected {
  border-color: #000;
}

/* QUANTITY */
.quantity-selector {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 20px;
}

.quantity-selector button {
  width: 32px;
  height: 32px;
  border: none;
  background: #eee;
  font-size: 18px;
  cursor: pointer;
}

/* BUTTON */
.btn-primary {
  width: 100%;
  padding: 14px;
  background: #111;
  color: #fff;
  border: none;
  cursor: pointer;
  margin-bottom: 12px;
}

/* LINK */
.view-details-link {
  display: block;
  text-align: center;
  font-size: 13px;
  text-decoration: underline;
  color: #111;
}


</style>