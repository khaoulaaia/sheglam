<!-- includes/product_modal.php -->
<div id="productModal" class="product-modal" aria-hidden="true">
  <div class="product-modal-content">
    <button class="close-product-modal" aria-label="Fermer">&times;</button>

    <div class="product-modal-left">
      <div class="thumbnails" id="productThumbnails"></div>
      <div class="main-image">
        <img id="productMainImage" src="" alt="Produit" />
      </div>
    </div>

    <div class="product-modal-right">
      <h2 id="productName"></h2>
      <p id="productPrice" class="price"></p>

      <div id="shadeSection" style="margin-top: 10px;">
        <strong>Color:</strong> <span id="selectedShadeName">-</span>
        <div id="shadeOptions" class="shade-options"></div>
      </div>

      <div class="quantity-selector">
        <button id="decreaseQty">−</button>
        <span id="quantity">1</span>
        <button id="increaseQty">+</button>
      </div>

      <button id="addToCartFromModal" class="btn">Add to Cart</button>
      <a id="viewFullDetails" href="#" class="view-details-link">View Full Details</a>
    </div>
  </div>
</div>
<style>
    .product-modal {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.product-modal-content {
  background: #fff;
  border-radius: 10px;
  max-width: 900px;
  width: 90%;
  display: flex;
  flex-wrap: wrap;
  overflow-y: auto;
  padding: 20px;
  position: relative;
}

.close-product-modal {
  position: absolute;
  top: 15px;
  right: 15px;
  font-size: 24px;
  background: none;
  border: none;
  cursor: pointer;
}

.product-modal-left {
  flex: 1;
  min-width: 250px;
  margin-right: 20px;
}

.thumbnails {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 10px;
}

.thumbnails img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 5px;
  cursor: pointer;
  border: 2px solid transparent;
}

.thumbnails img.active {
  border-color: #000;
}

.main-image img {
  width: 100%;
  border-radius: 8px;
}

.product-modal-right {
  flex: 1;
  min-width: 300px;
}

.shade-options {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}

.shade-option {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  border: 2px solid #ccc;
  cursor: pointer;
}

.shade-option.selected {
  border-color: #000;
}

.quantity-selector {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 15px 0;
}

.quantity-selector button {
  width: 30px;
  height: 30px;
  font-size: 18px;
  background: #f0f0f0;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}

.btn {
  background: black;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

</style>