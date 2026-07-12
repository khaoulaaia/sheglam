// =====================================================
//  checkout.js  —  SheGlamour
//  Gestion des commandes + notifications push/in-app
// =====================================================

(function () {
  "use strict";

  // ===================================================
  // 1. UTILITAIRES
  // ===================================================
  const getCart   = () => JSON.parse(localStorage.getItem("cart")   || "{}");
  const getOrders = () => JSON.parse(localStorage.getItem("orders") || "[]");
  const saveOrders = (orders) => localStorage.setItem("orders", JSON.stringify(orders));

  function generateOrderId() {
    const ts   = Date.now().toString(36).toUpperCase();
    const rand = Math.random().toString(36).substring(2, 6).toUpperCase();
    return `SHG-${ts}-${rand}`;
  }

  // ===================================================
  // 2. SERVICE WORKER + PUSH NOTIFICATIONS
  // ===================================================
  const SW_PATH = "/sw.js";

  async function registerSW() {
    if (!("serviceWorker" in navigator)) return null;
    try {
      // Check the file exists before registering to avoid noisy 404 errors
      const probe = await fetch(SW_PATH, { method: "HEAD" });
      if (!probe.ok) return null;
      const reg = await navigator.serviceWorker.register('/sw.js');
      console.log("[SW] Registered:", reg.scope);
      return reg;
    } catch (err) {
      // Silent — SW is optional (push notifications only)
      return null;
    }
  }

  async function requestPushPermission() {
    if (!("Notification" in window)) return "unsupported";
    if (Notification.permission === "granted") return "granted";
    if (Notification.permission === "denied")  return "denied";
    return await Notification.requestPermission();
  }

  function sendNativeNotif(title, body, icon = "/images/logo.png") {
    if (Notification.permission === "granted") {
      new Notification(title, {
        body, icon, badge: icon,
        vibrate: [200, 100, 200],
        tag: "sheglam-order",
      });
    } else {
      showInAppNotif(title, body, "success");
    }
  }

  // ===================================================
  // 3. NOTIFICATION IN-APP (toast)
  // ===================================================
  function injectToastStyles() {
    if (document.getElementById("sg-toast-styles")) return;
    const style = document.createElement("style");
    style.id = "sg-toast-styles";
    style.textContent = `
      #sg-toast-container {
        position: fixed; top: 80px; right: 20px; z-index: 9999;
        display: flex; flex-direction: column; gap: 12px; pointer-events: none;
      }
      .sg-toast {
        background: #F5F1EE; border-left: 4px solid #440B19; border-radius: 8px;
        padding: 14px 18px; min-width: 280px; max-width: 360px;
        box-shadow: 0 8px 30px rgba(0,0,0,.14);
        display: flex; align-items: flex-start; gap: 12px;
        animation: sgSlideIn .35s ease forwards;
        pointer-events: auto; position: relative; overflow: hidden;
      }
      .sg-toast.success { border-color: #22c55e; }
      .sg-toast.error   { border-color: #ef4444; }
      .sg-toast.info    { border-color: #440B19; }
      .sg-toast.warning { border-color: #f59e0b; }
      .sg-toast-icon { font-size: 20px; flex-shrink: 0; margin-top: 2px; }
      .sg-toast-content { flex: 1; }
      .sg-toast-title { font-weight: 700; font-size: 13px; color: #440B19; margin-bottom: 2px; }
      .sg-toast-body  { font-size: 12px; color: #6e1a2e; line-height: 1.5; }
      .sg-toast-close { background: none; border: none; cursor: pointer; font-size: 16px; color: #8a2a3e; padding: 0; line-height: 1; flex-shrink: 0; }
      .sg-toast-progress {
        position: absolute; bottom: 0; left: 0; height: 3px;
        background: rgba(68,11,25,.15); animation: sgProgress linear forwards;
      }
      .sg-toast.success .sg-toast-progress { background: #22c55e; }
      .sg-toast.error   .sg-toast-progress { background: #ef4444; }
      .sg-toast.info    .sg-toast-progress { background: #440B19; }
      .sg-toast.warning .sg-toast-progress { background: #f59e0b; }
      @keyframes sgSlideIn  { from { opacity:0; transform:translateX(40px); } to { opacity:1; transform:translateX(0); } }
      @keyframes sgSlideOut { from { opacity:1; transform:translateX(0); } to { opacity:0; transform:translateX(40px); } }
      @keyframes sgProgress { from { width:100%; } to { width:0%; } }
      @media (max-width: 480px) {
        #sg-toast-container { right: 10px; left: 10px; top: 70px; }
        .sg-toast { min-width: unset; max-width: 100%; }
      }
    `;
    document.head.appendChild(style);
  }

  function getToastContainer() {
    let c = document.getElementById("sg-toast-container");
    if (!c) {
      c = document.createElement("div");
      c.id = "sg-toast-container";
      document.body.appendChild(c);
    }
    return c;
  }

  const ICONS = { success: "✅", error: "❌", info: "ℹ️", warning: "⚠️" };

  function showInAppNotif(title, body, type = "info", duration = 5000) {
    injectToastStyles();
    const container = getToastContainer();
    const toast = document.createElement("div");
    toast.className = `sg-toast ${type}`;
    toast.innerHTML = `
      <span class="sg-toast-icon">${ICONS[type] || "🔔"}</span>
      <div class="sg-toast-content">
        <div class="sg-toast-title">${title}</div>
        <div class="sg-toast-body">${body}</div>
      </div>
      <button class="sg-toast-close" aria-label="Fermer">&times;</button>
      <div class="sg-toast-progress" style="animation-duration:${duration}ms"></div>
    `;
    const dismiss = () => {
      toast.style.animation = "sgSlideOut .3s ease forwards";
      setTimeout(() => toast.remove(), 300);
    };
    toast.querySelector(".sg-toast-close").addEventListener("click", dismiss);
    setTimeout(dismiss, duration);
    container.appendChild(toast);
  }

  window.showNotif = showInAppNotif;

  // ===================================================
  // 4. SIDEBAR CHECKOUT
  // ===================================================
  function injectCheckoutSidebarStyles() {
    if (document.getElementById("sg-checkout-styles")) return;
    const style = document.createElement("style");
    style.id = "sg-checkout-styles";
    style.textContent = `
      #sg-checkout-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,.45);
        z-index: 5000; opacity: 0; pointer-events: none; transition: opacity .35s;
      }
      #sg-checkout-overlay.active { opacity: 1; pointer-events: auto; }
      #sg-checkout-sidebar {
        position: fixed; top: 0; right: -480px; width: 100%; max-width: 480px;
        height: 100vh; background: #F5F1EE; z-index: 5100;
        display: flex; flex-direction: column;
        transition: right .4s cubic-bezier(.4,0,.2,1);
        box-shadow: -8px 0 40px rgba(0,0,0,.12);
      }
      #sg-checkout-sidebar.active { right: 0; }
      .sg-checkout-header {
        padding: 24px 28px; border-bottom: 1px solid rgba(68,11,25,.12);
        display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
        background: #440B19;
      }
      .sg-checkout-header h2 {
        font-size: 16px; font-weight: 700; letter-spacing: .12em;
        text-transform: uppercase; color: #F5F1EE;
        font-family: 'Cormorant Garamond', Georgia, serif;
      }
      .sg-checkout-close {
        background: none; border: none; font-size: 26px; cursor: pointer;
        color: rgba(245,241,238,.7); line-height: 1; transition: color .2s;
      }
      .sg-checkout-close:hover { color: #F5F1EE; }
      .sg-steps {
        display: flex; padding: 0 28px; gap: 0;
        border-bottom: 1px solid rgba(68,11,25,.12); flex-shrink: 0;
        background: #F5F1EE;
      }
      .sg-step {
        flex: 1; text-align: center; font-size: 11px; letter-spacing: .1em;
        text-transform: uppercase; color: rgba(68,11,25,.35);
        padding: 14px 0; border-bottom: 2px solid transparent; transition: .3s;
      }
      .sg-step.active { color: #440B19; border-color: #440B19; font-weight: 700; }
      .sg-step.done   { color: #22c55e; border-color: #22c55e; }
      .sg-checkout-body { flex: 1; overflow-y: auto; padding: 28px; background: #F5F1EE; }
      .sg-order-summary { margin-bottom: 28px; }
      .sg-order-summary h3 {
        font-size: 11px; text-transform: uppercase; letter-spacing: .15em;
        margin-bottom: 16px; color: #6e1a2e; font-weight: 600;
      }
      .sg-summary-item {
        display: flex; gap: 12px; padding: 10px 0;
        border-bottom: 1px solid rgba(68,11,25,.10);
      }
      .sg-summary-item img {
        width: 52px; height: 52px; object-fit: cover; flex-shrink: 0;
        border: 1px solid rgba(68,11,25,.10);
      }
      .sg-summary-item-info { flex: 1; font-size: 13px; color: #440B19; }
      .sg-summary-item-info strong { display: block; margin-bottom: 2px; color: #440B19; }
      .sg-summary-item-info span { color: #6e1a2e; font-size: 12px; }
      .sg-summary-item-price { font-weight: 700; font-size: 13px; white-space: nowrap; color: #440B19; }
      .sg-order-total {
        display: flex; justify-content: space-between; padding: 16px 0 0;
        font-weight: 700; font-size: 16px; color: #440B19;
        font-family: 'Cormorant Garamond', Georgia, serif; letter-spacing: .04em;
      }
      .sg-order-subtotal-line {
        display: flex; justify-content: space-between; padding: 6px 0;
        font-size: 13px; color: #6e1a2e;
      }
      .sg-delivery-price-box {
        margin-top: 4px; padding: 14px 16px; background: rgba(68,11,25,.05);
        border: 1px solid rgba(68,11,25,.15);
      }
      .sg-delivery-price-box .sg-order-subtotal-line span:last-child { color: #440B19; font-weight: 600; }
      .sg-delivery-price-total {
        display: flex; justify-content: space-between; padding: 10px 0 0;
        margin-top: 6px; border-top: 1px solid rgba(68,11,25,.15);
        font-weight: 700; font-size: 15px; color: #440B19;
        font-family: 'Cormorant Garamond', Georgia, serif; letter-spacing: .04em;
      }
      .sg-delivery-price-hint {
        font-size: 11px; color: rgba(68,11,25,.5); margin-top: 4px;
      }
      .sg-form { display: flex; flex-direction: column; gap: 16px; }
      .sg-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
      .sg-field label {
        display: block; font-size: 10px; text-transform: uppercase;
        letter-spacing: .15em; color: #6e1a2e; margin-bottom: 6px; font-weight: 600;
      }
      .sg-field input, .sg-field select, .sg-field textarea {
        width: 100%; border: 1px solid rgba(68,11,25,.25); border-radius: 0;
        padding: 10px 14px; font-size: 14px; transition: border-color .25s;
        background: #fff; font-family: inherit; resize: none; color: #440B19;
        box-sizing: border-box;
      }
      .sg-field input:focus, .sg-field select:focus, .sg-field textarea:focus {
        outline: none; border-color: #440B19; background: #fff;
        box-shadow: 0 0 0 3px rgba(68,11,25,.08);
      }
      .sg-field input::placeholder, .sg-field textarea::placeholder { color: rgba(68,11,25,.35); }
      .sg-field-error { font-size: 11px; color: #ef4444; margin-top: 4px; display: none; }
      .sg-field.has-error .sg-field-error { display: block; }
      .sg-field.has-error input, .sg-field.has-error select { border-color: #ef4444; }
      .sg-payment-methods { display: flex; flex-direction: column; gap: 10px; margin-bottom: 8px; }
      .sg-payment-option {
        border: 1px solid rgba(68,11,25,.2); border-radius: 0; padding: 14px 16px;
        cursor: pointer; display: flex; align-items: center; gap: 12px;
        transition: .25s; background: #fff;
      }
      .sg-payment-option:hover  { border-color: #6e1a2e; }
      .sg-payment-option.selected { border-color: #440B19; background: #fdf6f8; }
      .sg-payment-option input[type="radio"] { width: auto; accent-color: #440B19; }
      .sg-payment-option-label { flex: 1; font-size: 14px; font-weight: 500; color: #440B19; }
      .sg-payment-option-icon { font-size: 22px; }
      .sg-payment-option-price { font-size: 13px; font-weight: 700; color: #440B19; white-space: nowrap; }
      .sg-payment-option.disabled { opacity: .4; cursor: not-allowed; pointer-events: none; }
      .sg-checkout-footer {
        padding: 20px 28px; border-top: 1px solid rgba(68,11,25,.12); flex-shrink: 0;
        display: flex; flex-direction: column; gap: 10px; background: #F5F1EE;
      }
      .sg-btn-primary {
        width: 100%; padding: 15px; background: #440B19; color: #F5F1EE;
        border: none; border-radius: 0; font-size: 12px; font-weight: 700;
        letter-spacing: .14em; text-transform: uppercase; cursor: pointer;
        transition: background .25s, transform .15s; font-family: inherit;
      }
      .sg-btn-primary:hover   { background: #5c1022; }
      .sg-btn-primary:active  { transform: scale(.98); }
      .sg-btn-primary:disabled { background: rgba(68,11,25,.3); cursor: not-allowed; }
      .sg-btn-secondary {
        width: 100%; padding: 13px; background: none;
        border: 1px solid rgba(68,11,25,.25); border-radius: 0;
        font-size: 12px; cursor: pointer; transition: .25s;
        color: #440B19; letter-spacing: .1em; text-transform: uppercase;
        font-family: inherit;
      }
      .sg-btn-secondary:hover { border-color: #440B19; background: rgba(68,11,25,.05); }
      .sg-confirmation {
        text-align: center; padding: 40px 0;
        display: flex; flex-direction: column; align-items: center; gap: 16px;
      }
      .sg-confirmation-icon {
        width: 72px; height: 72px; background: rgba(68,11,25,.08); border-radius: 50%;
        display: flex; align-items: center; justify-content: center; font-size: 36px;
      }
      .sg-confirmation h3 {
        font-size: 20px; font-weight: 700; color: #440B19;
        font-family: 'Cormorant Garamond', Georgia, serif; letter-spacing: .06em;
      }
      .sg-confirmation p { font-size: 13px; color: #6e1a2e; max-width: 300px; line-height: 1.6; }
      .sg-order-id-badge {
        background: rgba(68,11,25,.08); border: 1px solid rgba(68,11,25,.2);
        padding: 8px 20px; font-size: 13px; font-weight: 700;
        letter-spacing: .12em; color: #440B19;
      }
      .sg-spinner {
        width: 18px; height: 18px; border: 2px solid rgba(245,241,238,.35);
        border-top-color: #F5F1EE; border-radius: 50%;
        animation: sgSpin .7s linear infinite;
        display: inline-block; vertical-align: middle; margin-right: 8px;
      }
      @keyframes sgSpin { to { transform: rotate(360deg); } }
      @media (max-width: 480px) { #sg-checkout-sidebar { max-width: 100%; } }
    `;
    document.head.appendChild(style);
  }

  // ===================================================
  // 5. BUILD CHECKOUT SIDEBAR DOM
  // ===================================================
  function buildCheckoutSidebar() {
    injectCheckoutSidebarStyles();
    if (document.getElementById("sg-checkout-sidebar")) return;

    const overlay = document.createElement("div");
    overlay.id = "sg-checkout-overlay";
    document.body.appendChild(overlay);

    const sidebar = document.createElement("div");
    sidebar.id = "sg-checkout-sidebar";
    sidebar.innerHTML = `
      <div class="sg-checkout-header">
        <h2>Commande</h2>
        <button class="sg-checkout-close" aria-label="Fermer">&times;</button>
      </div>
      <div class="sg-steps">
        <div class="sg-step active" data-step="1">Résumé</div>
        <div class="sg-step"        data-step="2">Livraison</div>
        <div class="sg-step"        data-step="3">Paiement</div>
      </div>
      <div class="sg-checkout-body"   id="sg-checkout-body"></div>
      <div class="sg-checkout-footer" id="sg-checkout-footer"></div>
    `;
    document.body.appendChild(sidebar);

    overlay.addEventListener("click", closeCheckout);
    sidebar.querySelector(".sg-checkout-close").addEventListener("click", closeCheckout);
  }

  // ===================================================
  // 6. OPEN / CLOSE
  // ===================================================
  function openCheckout() {
    buildCheckoutSidebar();
    currentStep = 1;
    renderStep(1);
    document.getElementById("sg-checkout-overlay").classList.add("active");
    document.getElementById("sg-checkout-sidebar").classList.add("active");
    document.body.style.overflow = "hidden";
  }

  function closeCheckout() {
    const overlay = document.getElementById("sg-checkout-overlay");
    const sidebar = document.getElementById("sg-checkout-sidebar");
    if (overlay) overlay.classList.remove("active");
    if (sidebar) sidebar.classList.remove("active");
    document.body.style.overflow = "";
  }

  window.openCheckout  = openCheckout;
  window.closeCheckout = closeCheckout;

  // ===================================================
  // 7. ETAPES
  // ===================================================
  let currentStep = 1;
  let orderData   = {};

  function setStep(n) {
    currentStep = n;
    document.querySelectorAll(".sg-step").forEach((el) => {
      const s = parseInt(el.dataset.step);
      el.classList.remove("active", "done");
      if      (s < n) el.classList.add("done");
      else if (s === n) el.classList.add("active");
    });
  }

  function renderStep(n) {
    setStep(n);
    const body   = document.getElementById("sg-checkout-body");
    const footer = document.getElementById("sg-checkout-footer");
    if (!body || !footer) return;
    if      (n === 1) renderStepSummary(body, footer);
    else if (n === 2) renderStepShipping(body, footer);
    else if (n === 3) renderStepPayment(body, footer);
  }

  // ---- ETAPE 1 : RESUME ----
  function renderStepSummary(body, footer) {
    const cart  = getCart();
    const items = Object.values(cart);
    let total = 0;

    const itemsHTML = items.map((item) => {
      total += item.price * item.quantity;
      let img = item.image_url || "";
      if (img && !img.startsWith("http")) img = "/images/" + img.split("/").pop();
      return `
        <div class="sg-summary-item">
          <img src="${img}" alt="${item.name}" onerror="this.style.display='none'">
          <div class="sg-summary-item-info">
            <strong>${item.name}${item.shade ? " – " + item.shade : ""}</strong>
            <span>Qté : ${item.quantity}</span>
          </div>
          <div class="sg-summary-item-price">${(item.price * item.quantity).toFixed(2)} DA</div>
        </div>`;
    }).join("");

    body.innerHTML = `
      <div class="sg-order-summary">
        <h3>Votre commande (${items.length} article${items.length > 1 ? "s" : ""})</h3>
        ${itemsHTML}
        <div class="sg-order-total">
          <span>Sous-total</span>
          <span>${total.toFixed(2)} DA</span>
        </div>
      </div>`;

    footer.innerHTML = `
      <button class="sg-btn-primary"   id="sg-to-shipping">Continuer vers la livraison →</button>
      <button class="sg-btn-secondary" id="sg-close-checkout">Annuler</button>`;

    document.getElementById("sg-to-shipping").onclick    = () => renderStep(2);
    document.getElementById("sg-close-checkout").onclick = closeCheckout;
  }

  // ---- ETAPE 2 : LIVRAISON ----
  function renderStepShipping(body, footer) {
    const saved = orderData.shipping || {};
    body.innerHTML = `
      <div class="sg-form">
        <div class="sg-form-row">
          <div class="sg-field" id="field-firstName">
            <label>Prénom *</label>
            <input id="sg-firstName" type="text" value="${saved.firstName || ""}" placeholder="Lina" autocomplete="given-name">
            <div class="sg-field-error">Champ requis (min 2 caractères)</div>
          </div>
          <div class="sg-field" id="field-lastName">
            <label>Nom *</label>
            <input id="sg-lastName" type="text" value="${saved.lastName || ""}" placeholder="Benali" autocomplete="family-name">
            <div class="sg-field-error">Champ requis (min 2 caractères)</div>
          </div>
        </div>
        <div class="sg-field" id="field-phone">
          <label>Téléphone *</label>
          <input id="sg-phone" type="tel" value="${saved.phone || ""}" placeholder="0550 000 000" autocomplete="tel">
          <div class="sg-field-error">Numéro invalide (min 9 chiffres)</div>
        </div>
        <div class="sg-field" id="field-wilaya">
          <label>Wilaya *</label>
          <select id="sg-wilaya">
            <option value="">— Choisir —</option>
            ${WILAYAS.map((w) => `<option value="${w.code}" ${saved.wilayaCode === w.code ? "selected" : ""}>${w.name} (${w.code})</option>`).join("")}
          </select>
          <div class="sg-field-error">Champ requis</div>
        </div>
        <div class="sg-field" id="field-deliveryMode">
          <label>Mode de livraison *</label>
          <select id="sg-deliveryMode">
            <option value="domicile" ${saved.deliveryMode === "domicile" ? "selected" : ""}>Livraison à domicile</option>
            <option value="stopDesk" ${saved.deliveryMode === "stopDesk" ? "selected" : ""}>Retrait Stop Desk</option>
          </select>
          <div class="sg-field-error">Champ requis</div>
        </div>
        <div class="sg-field" id="field-address">
          <label>Adresse *</label>
          <input id="sg-address" type="text" value="${saved.address || ""}" placeholder="Rue, N°, Commune" autocomplete="street-address">
          <div class="sg-field-error">Adresse trop courte (min 5 caractères)</div>
        </div>
        <div class="sg-field">
          <label>Note (optionnel)</label>
          <textarea id="sg-note" rows="2" placeholder="Instructions de livraison...">${saved.note || ""}</textarea>
        </div>
        <div class="sg-delivery-price-box" id="sg-delivery-price-box"></div>
      </div>`;

    footer.innerHTML = `
      <button class="sg-btn-primary"   id="sg-to-payment">Continuer vers le paiement →</button>
      <button class="sg-btn-secondary" id="sg-back-summary">← Retour</button>`;

    document.getElementById("sg-to-payment").onclick   = () => { if (validateShipping()) renderStep(3); };
    document.getElementById("sg-back-summary").onclick = () => renderStep(1);

    document.getElementById("sg-wilaya").addEventListener("change", updateDeliveryPriceBox);
    document.getElementById("sg-deliveryMode").addEventListener("change", updateDeliveryPriceBox);
    updateDeliveryPriceBox();
  }

  // ---- APERÇU LIVE DU PRIX DE LIVRAISON (étape Livraison) ----
  function getCartSubtotal() {
    const cart = getCart();
    let subtotal = 0;
    Object.values(cart).forEach((i) => (subtotal += i.price * i.quantity));
    return subtotal;
  }

  function updateDeliveryPriceBox() {
    const box = document.getElementById("sg-delivery-price-box");
    if (!box) return;

    const wilayaCode = document.getElementById("sg-wilaya")?.value || "";
    const mode       = document.getElementById("sg-deliveryMode")?.value || "domicile";
    const subtotal   = getCartSubtotal();

    if (!wilayaCode) {
      box.innerHTML = `<div class="sg-delivery-price-hint">Choisissez une wilaya pour voir les frais de livraison.</div>`;
      return;
    }

    const fee   = getDeliveryFee(wilayaCode, mode);
    const total = subtotal + fee;
    const modeLabel = mode === "stopDesk" ? "Stop Desk" : "Domicile";

    box.innerHTML = `
      <div class="sg-order-subtotal-line">
        <span>Sous-total</span><span>${subtotal.toFixed(2)} DA</span>
      </div>
      <div class="sg-order-subtotal-line">
        <span>Livraison (${modeLabel})</span><span>${fee.toFixed(2)} DA</span>
      </div>
      <div class="sg-delivery-price-total">
        <span>Total</span><span>${total.toFixed(2)} DA</span>
      </div>`;
  }

  function validateShipping() {
    const fields = {
      firstName: { el: "sg-firstName", container: "field-firstName", validate: (v) => v.trim().length >= 2 },
      lastName:  { el: "sg-lastName",  container: "field-lastName",  validate: (v) => v.trim().length >= 2 },
      phone:     { el: "sg-phone",     container: "field-phone",     validate: (v) => /^\d[\d\s]{8,}$/.test(v.trim()) },
      wilaya:    { el: "sg-wilaya",    container: "field-wilaya",    validate: (v) => v.trim() !== "" },
      address:   { el: "sg-address",   container: "field-address",   validate: (v) => v.trim().length >= 5 },
    };

    let valid = true;
    const data = {};

    Object.entries(fields).forEach(([key, cfg]) => {
      const input     = document.getElementById(cfg.el);
      const container = document.getElementById(cfg.container);
      const val       = input?.value || "";
      if (!cfg.validate(val)) {
        container?.classList.add("has-error");
        valid = false;
      } else {
        container?.classList.remove("has-error");
        data[key] = val;
      }
    });

    if (valid) {
      const wilayaCode      = document.getElementById("sg-wilaya")?.value || "";
      const wilayaMatch      = WILAYAS.find((w) => w.code === wilayaCode);
      data.wilayaCode        = wilayaCode;
      data.wilaya            = wilayaMatch ? wilayaMatch.name : "";
      data.deliveryMode      = document.getElementById("sg-deliveryMode")?.value || "domicile";
      data.note              = document.getElementById("sg-note")?.value || "";
      data.deliveryFee       = getDeliveryFee(wilayaCode, data.deliveryMode);
      orderData.shipping     = data;
    }
    return valid;
  }

  // ---- FRAIS DE LIVRAISON ----
  function getDeliveryFee(wilayaCode, mode) {
    const tarif = TARIFS_LIVRAISON[wilayaCode];
    if (!tarif) return 0;
    return mode === "stopDesk" ? tarif.stopDesk : tarif.domicile;
  }

  // ---- ETAPE 3 : PAIEMENT ----
  let selectedPayment = "cash";

  function renderStepPayment(body, footer) {
    selectedPayment = orderData.payment_method || "cash";

    body.innerHTML = `
      <div class="sg-order-summary" style="margin-bottom:24px">
        <h3>Récapitulatif</h3>
        ${buildMiniSummary()}
      </div>
      <div class="sg-form">
        <div class="sg-field">
          <label>Mode de paiement *</label>
          <div class="sg-payment-methods" id="sg-payment-methods">
            ${buildPaymentOptions()}
          </div>
        </div>
      </div>`;

    body.querySelectorAll(".sg-payment-option").forEach((opt) => {
      opt.addEventListener("click", () => {
        selectedPayment          = opt.dataset.value;
        orderData.payment_method = selectedPayment;
        body.querySelectorAll(".sg-payment-option").forEach((o) => o.classList.remove("selected"));
        opt.classList.add("selected");
        opt.querySelector("input").checked = true;
      });
    });

    footer.innerHTML = `
      <button class="sg-btn-primary"   id="sg-place-order">Confirmer la commande</button>
      <button class="sg-btn-secondary" id="sg-back-shipping">← Retour</button>`;

    document.getElementById("sg-place-order").onclick   = placeOrder;
    document.getElementById("sg-back-shipping").onclick = () => renderStep(2);
  }

  function buildPaymentOptions() {
    const methods = [
      { value: "cash",      label: "Paiement à la livraison", icon: "💵" },
      { value: "ccp",       label: "Virement CCP",            icon: "🏦" },
      { value: "baridimob", label: "Baridi Mob",              icon: "📱" },
    ];
    return methods.map((m) => `
      <label class="sg-payment-option${selectedPayment === m.value ? " selected" : ""}" data-value="${m.value}">
        <input type="radio" name="sg-payment" value="${m.value}" ${selectedPayment === m.value ? "checked" : ""} style="display:none">
        <span class="sg-payment-option-icon">${m.icon}</span>
        <span class="sg-payment-option-label">${m.label}</span>
      </label>`).join("");
  }

  function buildMiniSummary() {
    const cart = getCart();
    let subtotal  = 0;
    Object.values(cart).forEach((i) => (subtotal += i.price * i.quantity));
    const s = orderData.shipping || {};
    const deliveryFee = s.deliveryFee || 0;
    const total = subtotal + deliveryFee;
    const modeLabel = s.deliveryMode === "stopDesk" ? "Stop Desk" : "Domicile";
    return `
      <div style="font-size:13px;color:#6e1a2e;line-height:1.8">
        <div><strong style="color:#440B19">Livraison :</strong> ${s.firstName || ""} ${s.lastName || ""}</div>
        <div><strong style="color:#440B19">Tél :</strong> ${s.phone || ""}</div>
        <div><strong style="color:#440B19">Wilaya :</strong> ${s.wilaya || ""} (${s.wilayaCode || ""})</div>
        <div><strong style="color:#440B19">Mode :</strong> ${modeLabel}</div>
        <div><strong style="color:#440B19">Adresse :</strong> ${s.address || ""}</div>
        <div class="sg-order-subtotal-line">
          <span>Sous-total</span><span>${subtotal.toFixed(2)} DA</span>
        </div>
        <div class="sg-order-subtotal-line">
          <span>Livraison (${modeLabel})</span><span>${deliveryFee.toFixed(2)} DA</span>
        </div>
        <div style="margin-top:12px;font-size:16px;font-weight:700;color:#440B19;font-family:'Cormorant Garamond',Georgia,serif;letter-spacing:.04em">
          Total : ${total.toFixed(2)} DA
        </div>
      </div>`;
  }

  // ===================================================
  // 8. PLACE ORDER
  // ===================================================
  async function placeOrder() {
    const btn = document.getElementById("sg-place-order");
    if (!btn) return;

    btn.disabled = true;
    btn.innerHTML = `<span class="sg-spinner"></span>Envoi en cours…`;

    const cart  = getCart();
    const items = Object.values(cart);
    let subtotal   = 0;
    items.forEach((i) => (subtotal += i.price * i.quantity));

    const deliveryFee = (orderData.shipping && orderData.shipping.deliveryFee) || 0;
    const total = subtotal + deliveryFee;

    const order = {
      order_id:       generateOrderId(),
      status:         "pending",
      payment_method: orderData.payment_method || "cash",
      subtotal:       parseFloat(subtotal.toFixed(2)),
      delivery_fee:   parseFloat(deliveryFee.toFixed(2)),
      total:          parseFloat(total.toFixed(2)),
      shipping:       orderData.shipping,
      items: items.map((item) => ({
        product_id: item.id          || item.product_id || null,
        name:       item.name,
        shade:      item.shade       || null,
        quantity:   item.quantity,
        unit_price: parseFloat(item.price.toFixed(2)),
      })),
    };

    // ── Envoi serveur (gracieux — fonctionne aussi sans back-end) ─────────────
    let serverError = null;
    try {
      const res = await fetch("/includes/place_order.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(order),
      });

      let serverData = {};
      try { serverData = await res.json(); } catch {}

      if (!res.ok && res.status !== 404) {
        // 404 = endpoint pas encore créé → mode local ; autre erreur = bloquant
        serverError = serverData.message || `Erreur serveur (${res.status})`;
      } else if (res.ok) {
        console.log("[Order] Enregistré côté serveur :", serverData);
      } else {
        console.warn("[Order] place_order.php introuvable — mode local.");
      }
    } catch (networkErr) {
      // Hors-ligne ou CORS → mode local silencieux
      console.warn("[Order] Erreur réseau, mode local :", networkErr.message);
    }

    if (serverError) {
      btn.disabled = false;
      btn.innerHTML = "Confirmer la commande";
      showInAppNotif("Erreur", serverError, "error");
      return;
    }

    // ── Succès (serveur ou local) ─────────────────────────────────────────────
    const localOrder = { ...order, created_at: new Date().toISOString() };
    const orders = getOrders();
    orders.unshift(localOrder);
    saveOrders(orders);

    localStorage.removeItem("cart");
    window.cart = {};

    if (typeof window.renderCart === "function") window.renderCart();
    if (typeof window.bumpCartBadge === "function") window.bumpCartBadge();
    if (typeof window.closeCart === "function") window.closeCart();

    showConfirmation(order);

    sendNativeNotif(
      "🛍️ Commande confirmée !",
      `Commande ${order.order_id} bien reçue. Livraison sous 3-5 jours ouvrés.`
    );
    showInAppNotif(
      "Commande confirmée !",
      `Votre commande ${order.order_id} a été enregistrée.`,
      "success", 7000
    );
  }

  // ===================================================
  // 9. ECRAN DE CONFIRMATION
  // ===================================================
  function showConfirmation(order) {
    const body   = document.getElementById("sg-checkout-body");
    const footer = document.getElementById("sg-checkout-footer");
    if (!body || !footer) return;

    document.querySelectorAll(".sg-step").forEach((el) => {
      el.classList.remove("active");
      el.classList.add("done");
    });

    body.innerHTML = `
      <div class="sg-confirmation">
        <div class="sg-confirmation-icon">✅</div>
        <h3>Commande confirmée !</h3>
        <p>Merci pour votre confiance. Votre commande a été enregistrée et sera traitée dans les plus brefs délais.</p>
        <div class="sg-order-id-badge">${order.order_id}</div>
        <p style="font-size:12px;color:rgba(68,11,25,.45)">Conservez ce numéro pour le suivi de votre livraison.</p>
      </div>`;

    footer.innerHTML = `
      <button class="sg-btn-primary" id="sg-continue-shopping">Continuer mes achats</button>`;

    document.getElementById("sg-continue-shopping").onclick = () => {
      closeCheckout();
      orderData = {};
    };
  }

  // ===================================================
  // 10. WILAYAS D'ALGERIE (code + nom)
  // ===================================================
  const WILAYAS = [
    { code: "1",  name: "Adrar" },
    { code: "2",  name: "Chlef" },
    { code: "3",  name: "Laghouat" },
    { code: "4",  name: "Oum El Bouaghi" },
    { code: "5",  name: "Batna" },
    { code: "6",  name: "Béjaïa" },
    { code: "7",  name: "Biskra" },
    { code: "8",  name: "Béchar" },
    { code: "9",  name: "Blida" },
    { code: "10", name: "Bouira" },
    { code: "11", name: "Tamanrasset" },
    { code: "12", name: "Tébessa" },
    { code: "13", name: "Tlemcen" },
    { code: "14", name: "Tiaret" },
    { code: "15", name: "Tizi Ouzou" },
    { code: "16", name: "Alger" },
    { code: "17", name: "Djelfa" },
    { code: "18", name: "Jijel" },
    { code: "19", name: "Sétif" },
    { code: "20", name: "Saïda" },
    { code: "21", name: "Skikda" },
    { code: "22", name: "Sidi Bel Abbès" },
    { code: "23", name: "Annaba" },
    { code: "24", name: "Guelma" },
    { code: "25", name: "Constantine" },
    { code: "26", name: "Médéa" },
    { code: "27", name: "Mostaganem" },
    { code: "28", name: "M'Sila" },
    { code: "29", name: "Mascara" },
    { code: "30", name: "Ouargla" },
    { code: "31", name: "Oran" },
    { code: "32", name: "El Bayadh" },
    { code: "33", name: "Illizi" },
    { code: "34", name: "Bordj Bou Arréridj" },
    { code: "35", name: "Boumerdès" },
    { code: "36", name: "El Tarf" },
    { code: "37", name: "Tindouf" },
    { code: "38", name: "Tissemsilt" },
    { code: "39", name: "El Oued" },
    { code: "40", name: "Khenchela" },
    { code: "41", name: "Souk Ahras" },
    { code: "42", name: "Tipaza" },
    { code: "43", name: "Mila" },
    { code: "44", name: "Aïn Defla" },
    { code: "45", name: "Naâma" },
    { code: "46", name: "Aïn Témouchent" },
    { code: "47", name: "Ghardaïa" },
    { code: "48", name: "Relizane" },
    { code: "49", name: "Timimoun" },
    { code: "50", name: "Bordj Badji Mokhtar" },
    { code: "51", name: "Ouled Djellal" },
    { code: "52", name: "Béni Abbès" },
    { code: "53", name: "In Salah" },
    { code: "54", name: "In Guezzam" },
    { code: "55", name: "Touggourt" },
    { code: "57", name: "El M'Ghair" },
    { code: "58", name: "El Menia" },
  ];

  // ===================================================
  // 10bis. TARIFS DE LIVRAISON (par code wilaya)
  // ===================================================
  const TARIFS_LIVRAISON = {
    "16": { domicile: 500,  stopDesk: 250  },
    "35": { domicile: 500,  stopDesk: 300  },
    "9":  { domicile: 550,  stopDesk: 250  },
    "42": { domicile: 550,  stopDesk: 250  },
    "15": { domicile: 600,  stopDesk: 300  },
    "10": { domicile: 650,  stopDesk: 300  },
    "26": { domicile: 650,  stopDesk: 250  },
    "2":  { domicile: 700,  stopDesk: 350  },
    "6":  { domicile: 700,  stopDesk: 350  },
    "14": { domicile: 700,  stopDesk: 350  },
    "19": { domicile: 700,  stopDesk: 350  },
    "25": { domicile: 700,  stopDesk: 350  },
    "31": { domicile: 700,  stopDesk: 350  },
    "4":  { domicile: 750,  stopDesk: 350  },
    "5":  { domicile: 750,  stopDesk: 350  },
    "13": { domicile: 750,  stopDesk: 350  },
    "18": { domicile: 750,  stopDesk: 350  },
    "21": { domicile: 750,  stopDesk: 350  },
    "22": { domicile: 750,  stopDesk: 350  },
    "23": { domicile: 750,  stopDesk: 350  },
    "27": { domicile: 750,  stopDesk: 350  },
    "28": { domicile: 750,  stopDesk: 350  },
    "29": { domicile: 750,  stopDesk: 350  },
    "34": { domicile: 750,  stopDesk: 350  },
    "38": { domicile: 750,  stopDesk: 350  },
    "41": { domicile: 750,  stopDesk: 350  },
    "43": { domicile: 750,  stopDesk: 350  },
    "44": { domicile: 750,  stopDesk: 350  },
    "46": { domicile: 750,  stopDesk: 350  },
    "48": { domicile: 750,  stopDesk: 350  },
    "12": { domicile: 800,  stopDesk: 350  },
    "20": { domicile: 800,  stopDesk: 350  },
    "24": { domicile: 800,  stopDesk: 350  },
    "36": { domicile: 800,  stopDesk: 350  },
    "40": { domicile: 800,  stopDesk: 350  },
    "7":  { domicile: 900,  stopDesk: 350  },
    "51": { domicile: 900,  stopDesk: 350  },
    "3":  { domicile: 1000, stopDesk: 500  },
    "17": { domicile: 1000, stopDesk: 500  },
    "30": { domicile: 1000, stopDesk: 500  },
    "39": { domicile: 1000, stopDesk: 500  },
    "47": { domicile: 1000, stopDesk: 500  },
    "55": { domicile: 1000, stopDesk: 500  },
    "57": { domicile: 1000, stopDesk: 500  },
    "58": { domicile: 1000, stopDesk: 500  },
    "8":  { domicile: 1100, stopDesk: 600  },
    "32": { domicile: 1100, stopDesk: 600  },
    "45": { domicile: 1100, stopDesk: 600  },
    "52": { domicile: 1100, stopDesk: 600  },
    "1":  { domicile: 1400, stopDesk: 700  },
    "37": { domicile: 1400, stopDesk: 600  },
    "49": { domicile: 1400, stopDesk: 700  },
    "11": { domicile: 1850, stopDesk: 1000 },
    "53": { domicile: 1850, stopDesk: 1000 },
    "33": { domicile: 2000, stopDesk: 1000 },
  };

  // ===================================================
  // 11. INIT
  // ===================================================
  document.addEventListener("DOMContentLoaded", async () => {
    document.body.addEventListener("click", async () => {
      await requestPushPermission();
    }, { once: true });

    await registerSW();
  });

  window.SheGlamCheckout = {
    open:   openCheckout,
    close:  closeCheckout,
    notify: showInAppNotif,
  };

})();