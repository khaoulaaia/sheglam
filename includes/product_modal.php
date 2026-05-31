<!-- includes/product_modal.php -->
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
        <div class="shade-label-row">
          <strong>Couleur :</strong>
          <span id="selectedShadeName">—</span>
        </div>
        <div id="shadeOptions" class="shade-options"></div>
      </div>

      <!-- QUANTITY -->
      <div class="quantity-selector">
        <button id="decreaseQty" type="button">−</button>
        <span id="quantity">1</span>
        <button id="increaseQty" type="button">+</button>
      </div>

      <!-- ACTIONS -->
      <button id="addToCartFromModal" class="btn-primary" disabled>
        Ajouter au panier
      </button>

      <a id="viewFullDetails" class="view-details-link" href="#">
        Voir la fiche produit
      </a>
    </div>

  </div>
</div>

<style>
/* ═══════════════════════════════════
   PALETTE
   #F5F1EE  crème ivoire
   #440B19  bordeaux profond
═══════════════════════════════════ */

.product-modal {
  position: fixed;
  inset: 0;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: rgba(68,11,25,0.82);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  z-index: 5000;
}

.product-modal-content {
  position: relative;
  width: 100%;
  max-width: 1040px;
  max-height: 92vh;
  overflow-y: auto;
  display: flex;
  gap: 42px;
  padding: 34px;
  background: #F5F1EE;
  border: 1px solid rgba(68,11,25,0.14);
  box-shadow: 0 30px 80px rgba(68,11,25,0.22);
}

/* ── CLOSE ── */
.close-product-modal {
  position: absolute;
  top: 20px; right: 22px;
  border: none;
  background: transparent;
  color: rgba(68,11,25,0.40);
  font-size: 28px;
  font-weight: 300;
  line-height: 1;
  cursor: pointer;
  transition: color .25s ease, transform .25s ease;
  z-index: 2;
}
.close-product-modal:hover { color: #440B19; transform: rotate(90deg); }

/* ── LEFT ── */
.product-modal-left {
  flex: 1;
  display: flex;
  gap: 16px;
}

/* Thumbnails */
.thumbnails {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.thumbnails img {
  width: 72px; height: 72px;
  object-fit: cover;
  border: 1px solid transparent;
  background: rgba(68,11,25,0.06);
  cursor: pointer;
  transition: border-color .25s ease, transform .25s ease, box-shadow .25s ease;
}
.thumbnails img:hover { transform: translateY(-2px); }
.thumbnails img.active {
  border-color: #440B19;
  box-shadow: 0 8px 20px rgba(68,11,25,0.22);
}

/* Main image */
.main-image {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(180deg, #F5F1EE, rgba(68,11,25,0.05));
  padding: 24px;
  min-height: 500px;
}
.main-image img {
  width: 100%;
  max-width: 430px;
  object-fit: contain;
  transition: opacity .18s ease;
}

/* ── RIGHT ── */
.product-modal-right {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.product-modal-right h2 {
  font-family: Georgia, "Times New Roman", serif;
  font-size: 2rem;
  font-weight: 600;
  line-height: 1.1;
  color: #1a0509;
  margin-bottom: 10px;
}
.product-modal-right .price {
  font-size: 1.1rem;
  font-weight: 500;
  letter-spacing: .04em;
  color: #5c1225;
  margin-bottom: 28px;
}

/* ── SHADE LABEL ── */
.shade-label-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 2px;
}
.shade-label-row strong {
  font-size: .78rem;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: #440B19;
}
#selectedShadeName {
  font-size: .85rem;
  color: #5c1225;
  font-style: italic;
}

/* ── SHADE OPTIONS ── */
.shade-options {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin: 10px 0 28px;
}

/* Chaque teinte */
.shade-option {
  width: 44px; height: 44px;
  border-radius: 50%;
  border: 2px solid rgba(68,11,25,0.15);
  cursor: pointer;
  position: relative;
  overflow: hidden;
  flex-shrink: 0;
  transition: border-color .2s ease, transform .2s ease, box-shadow .2s ease;
  background: #F5F1EE;
}
.shade-option:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(68,11,25,0.18);
}
.shade-option.selected {
  border: 2.5px solid #440B19;
  box-shadow: 0 8px 20px rgba(68,11,25,0.30);
}

/* Image de teinte */
.shade-option img.shade-thumb {
  width: 100%; height: 100%;
  object-fit: cover;
  border-radius: 50%;
  display: block;
}
/* Pastille couleur */
.shade-option .shade-color-dot {
  display: block;
  width: 100%; height: 100%;
  border-radius: 50%;
  border: 1px solid rgba(68,11,25,0.10);
}

/* OOS */
.shade-option.shade-oos {
  opacity: 0.38;
  cursor: not-allowed;
}
.shade-option.shade-oos::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 50%;
  background: repeating-linear-gradient(
    45deg, transparent, transparent 3px,
    rgba(0,0,0,0.28) 3px, rgba(0,0,0,0.28) 4px
  );
}

/* Texte chargement / erreur */
.shade-options p {
  font-size: .82rem;
  color: #888;
  margin: 0;
}

/* ── QUANTITY ── */
.quantity-selector {
  display: flex;
  align-items: center;
  gap: 18px;
  margin-bottom: 30px;
}
.quantity-selector button {
  width: 38px; height: 38px;
  border: 1px solid rgba(68,11,25,0.18);
  background: rgba(68,11,25,0.06);
  color: #5c1225;
  font-size: 20px;
  cursor: pointer;
  transition: background .25s ease, color .25s ease, transform .25s ease;
}
.quantity-selector button:hover {
  background: #440B19; color: #F5F1EE; transform: translateY(-2px);
}
.quantity-selector span {
  min-width: 22px;
  text-align: center;
  font-size: 1rem;
  font-weight: 600;
  color: #1a0509;
}

/* ── BUTTON ── */
.btn-primary {
  width: 100%;
  padding: 16px;
  border: none;
  background: linear-gradient(135deg, #6b1a2e, #440B19);
  color: #F5F1EE;
  font-size: .78rem;
  font-weight: 600;
  letter-spacing: .16em;
  text-transform: uppercase;
  cursor: pointer;
  transition: transform .25s ease, box-shadow .25s ease, opacity .25s ease;
  box-shadow: 0 18px 34px rgba(68,11,25,0.28);
  margin-bottom: 16px;
}
.btn-primary:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 0 24px 40px rgba(68,11,25,0.42);
}
.btn-primary:disabled {
  opacity: 0.48;
  cursor: not-allowed;
  transform: none;
}

/* ── LINK ── */
.view-details-link {
  text-align: center;
  font-size: .78rem;
  letter-spacing: .05em;
  color: rgba(68,11,25,0.50);
  text-decoration: underline;
  transition: color .25s ease;
}
.view-details-link:hover { color: #440B19; }

/* ══ TABLET 601–900px ══ */
@media (min-width: 601px) and (max-width: 900px) {
  .product-modal { padding: 16px; align-items: center; }
  .product-modal-content {
    max-width: 720px; max-height: 90vh;
    padding: 28px; gap: 28px;
    flex-direction: column;
  }
  .product-modal-left { flex-direction: column-reverse; }
  .thumbnails { flex-direction: row; overflow-x: auto; padding-bottom: 4px; }
  .thumbnails img { width: 64px; height: 64px; flex-shrink: 0; }
  .main-image { min-height: 360px; padding: 20px; }
  .main-image img { max-width: 300px; }
  .product-modal-right h2 { font-size: 1.65rem; }
  .product-modal-right .price { font-size: 1.05rem; margin-bottom: 22px; }
  .shade-options { gap: 10px; margin-bottom: 22px; }
}

/* ══ MOBILE ≤600px ══ */
@media (max-width: 600px) {
  .product-modal { padding: 12px; align-items: center; }
  .product-modal-content {
    width: 100%; max-width: 100%; max-height: 92vh;
    padding: 16px 14px 24px; gap: 14px;
    flex-direction: column; border-radius: 0; overflow-y: auto;
  }
  .close-product-modal { top: 12px; right: 14px; font-size: 22px; }
  .product-modal-left { flex-direction: column-reverse; gap: 10px; }
  .thumbnails { flex-direction: row; overflow-x: auto; gap: 8px; padding-bottom: 2px; }
  .thumbnails img { width: 52px; height: 52px; flex-shrink: 0; }
  .main-image { min-height: 200px; max-height: 230px; padding: 12px; border-radius: 10px; }
  .main-image img { max-width: 180px; }
  .product-modal-right { justify-content: flex-start; }
  .product-modal-right h2 { font-size: 1.1rem; margin-bottom: 6px; }
  .product-modal-right .price { font-size: .9rem; margin-bottom: 14px; }
  .shade-label-row strong { font-size: .72rem; }
  .shade-option { width: 36px; height: 36px; }
  .shade-options { gap: 8px; margin: 8px 0 16px; }
  .quantity-selector { gap: 12px; margin-bottom: 16px; }
  .quantity-selector button { width: 34px; height: 34px; font-size: 17px; }
  .quantity-selector span { font-size: .9rem; }
  .btn-primary { padding: 13px; font-size: .72rem; }
  .view-details-link { font-size: .72rem; }
}
</style>
