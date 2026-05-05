// =====================================================
//  checkout.js  —  SheGlamour
//  Gestion des commandes + notifications push/in-app
//  Adapté au schéma BD (orders, order_items, clientglam)
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
      const reg = await navigator.serviceWorker.register(SW_PATH);
      console.log("[SW] Registered:", reg.scope);
      return reg;
    } catch (err) {
      console.warn("[SW] Registration failed:", err);
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
        background: #fff; border-left: 4px solid #111; border-radius: 8px;
        padding: 14px 18px; min-width: 280px; max-width: 360px;
        box-shadow: 0 8px 30px rgba(0,0,0,.14);
        display: flex; align-items: flex-start; gap: 12px;
        animation: sgSlideIn .35s ease forwards;
        pointer-events: auto; position: relative; overflow: hidden;
      }
      .sg-toast.success { border-color: #22c55e; }
      .sg-toast.error   { border-color: #ef4444; }
      .sg-toast.info    { border-color: #6366f1; }
      .sg-toast.warning { border-color: #f59e0b; }
      .sg-toast-icon { font-size: 20px; flex-shrink: 0; margin-top: 2px; }
      .sg-toast-content { flex: 1; }
      .sg-toast-title { font-weight: 700; font-size: 13px; color: #111; margin-bottom: 2px; }
      .sg-toast-body  { font-size: 12px; color: #555; line-height: 1.5; }
      .sg-toast-close { background: none; border: none; cursor: pointer; font-size: 16px; color: #aaa; padding: 0; line-height: 1; flex-shrink: 0; }
      .sg-toast-progress {
        position: absolute; bottom: 0; left: 0; height: 3px;
        background: rgba(0,0,0,.12); animation: sgProgress linear forwards;
      }
      .sg-toast.success .sg-toast-progress { background: #22c55e; }
      .sg-toast.error   .sg-toast-progress { background: #ef4444; }
      .sg-toast.info    .sg-toast-progress { background: #6366f1; }
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
        height: 100vh; background: #fff; z-index: 5100;
        display: flex; flex-direction: column;
        transition: right .4s cubic-bezier(.4,0,.2,1);
        box-shadow: -8px 0 40px rgba(0,0,0,.12);
      }
      #sg-checkout-sidebar.active { right: 0; }
      .sg-checkout-header {
        padding: 24px 28px; border-bottom: 1px solid #f0f0f0;
        display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
      }
      .sg-checkout-header h2 { font-size: 16px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
      .sg-checkout-close { background: none; border: none; font-size: 26px; cursor: pointer; color: #666; line-height: 1; }
      .sg-steps {
        display: flex; padding: 16px 28px; gap: 0;
        border-bottom: 1px solid #f0f0f0; flex-shrink: 0;
      }
      .sg-step {
        flex: 1; text-align: center; font-size: 11px; letter-spacing: .1em;
        text-transform: uppercase; color: #aaa; padding-bottom: 8px;
        border-bottom: 2px solid #eee; transition: .3s;
      }
      .sg-step.active { color: #111; border-color: #111; font-weight: 700; }
      .sg-step.done   { color: #22c55e; border-color: #22c55e; }
      .sg-checkout-body { flex: 1; overflow-y: auto; padding: 28px; }
      .sg-order-summary { margin-bottom: 28px; }
      .sg-order-summary h3 { font-size: 13px; text-transform: uppercase; letter-spacing: .1em; margin-bottom: 16px; color: #666; }
      .sg-summary-item { display: flex; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f5f5f5; }
      .sg-summary-item img { width: 52px; height: 52px; object-fit: cover; border-radius: 6px; flex-shrink: 0; }
      .sg-summary-item-info { flex: 1; font-size: 13px; }
      .sg-summary-item-info strong { display: block; margin-bottom: 2px; }
      .sg-summary-item-info span { color: #888; font-size: 12px; }
      .sg-summary-item-price { font-weight: 700; font-size: 13px; white-space: nowrap; }
      .sg-order-total { display: flex; justify-content: space-between; padding: 16px 0 0; font-weight: 700; font-size: 15px; }
      .sg-form { display: flex; flex-direction: column; gap: 16px; }
      .sg-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
      .sg-field label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .1em; color: #777; margin-bottom: 6px; }
      .sg-field input, .sg-field select, .sg-field textarea {
        width: 100%; border: 1px solid #ddd; border-radius: 6px;
        padding: 10px 14px; font-size: 14px; transition: border-color .25s;
        background: #fafafa; font-family: inherit; resize: none;
      }
      .sg-field input:focus, .sg-field select:focus, .sg-field textarea:focus { outline: none; border-color: #111; background: #fff; }
      .sg-field-error { font-size: 11px; color: #ef4444; margin-top: 4px; display: none; }
      .sg-field.has-error .sg-field-error { display: block; }
      .sg-field.has-error input, .sg-field.has-error select { border-color: #ef4444; }
      .sg-payment-methods { display: flex; flex-direction: column; gap: 10px; margin-bottom: 8px; }
      .sg-payment-option {
        border: 1px solid #ddd; border-radius: 8px; padding: 14px 16px; cursor: pointer;
        display: flex; align-items: center; gap: 12px; transition: .25s;
      }
      .sg-payment-option:hover  { border-color: #999; }
      .sg-payment-option.selected { border-color: #111; background: #f9f9f9; }
      .sg-payment-option input[type="radio"] { width: auto; accent-color: #111; }
      .sg-payment-option-label { flex: 1; font-size: 14px; font-weight: 500; }
      .sg-payment-option-icon { font-size: 22px; }
      .sg-checkout-footer {
        padding: 20px 28px; border-top: 1px solid #f0f0f0; flex-shrink: 0;
        display: flex; flex-direction: column; gap: 10px;
      }
      .sg-btn-primary {
        width: 100%; padding: 15px; background: #111; color: #fff;
        border: none; border-radius: 8px; font-size: 14px; font-weight: 700;
        letter-spacing: .08em; text-transform: uppercase; cursor: pointer;
        transition: background .25s, transform .15s;
      }
      .sg-btn-primary:hover   { background: #333; }
      .sg-btn-primary:active  { transform: scale(.98); }
      .sg-btn-primary:disabled { background: #ccc; cursor: not-allowed; }
      .sg-btn-secondary {
        width: 100%; padding: 13px; background: none; border: 1px solid #ddd;
        border-radius: 8px; font-size: 13px; cursor: pointer; transition: .25s;
      }
      .sg-btn-secondary:hover { border-color: #999; background: #f5f5f5; }
      .sg-confirmation {
        text-align: center; padding: 40px 0;
        display: flex; flex-direction: column; align-items: center; gap: 16px;
      }
      .sg-confirmation-icon {
        width: 72px; height: 72px; background: #f0fdf4; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; font-size: 36px;
      }
      .sg-confirmation h3 { font-size: 18px; font-weight: 700; }
      .sg-confirmation p  { font-size: 13px; color: #666; max-width: 300px; line-height: 1.6; }
      .sg-order-id-badge {
        background: #f5f5f5; border-radius: 6px; padding: 8px 16px;
        font-size: 13px; font-weight: 700; letter-spacing: .1em; color: #111;
      }
      .sg-spinner {
        width: 20px; height: 20px; border: 2px solid rgba(255,255,255,.4);
        border-top-color: #fff; border-radius: 50%;
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
  // 7. ÉTAPES
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

  // ---- ÉTAPE 1 : RÉSUMÉ ----
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
          <span>Total</span>
          <span>${total.toFixed(2)} DA</span>
        </div>
      </div>`;

    footer.innerHTML = `
      <button class="sg-btn-primary"   id="sg-to-shipping">Continuer vers la livraison →</button>
      <button class="sg-btn-secondary" id="sg-close-checkout">Annuler</button>`;

    document.getElementById("sg-to-shipping").onclick    = () => renderStep(2);
    document.getElementById("sg-close-checkout").onclick = closeCheckout;
  }

  // ---- ÉTAPE 2 : LIVRAISON ----
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
            ${WILAYAS.map((w) => `<option value="${w}" ${saved.wilaya === w ? "selected" : ""}>${w}</option>`).join("")}
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
      </div>`;

    footer.innerHTML = `
      <button class="sg-btn-primary"   id="sg-to-payment">Continuer vers le paiement →</button>
      <button class="sg-btn-secondary" id="sg-back-summary">← Retour</button>`;

    document.getElementById("sg-to-payment").onclick  = () => { if (validateShipping()) renderStep(3); };
    document.getElementById("sg-back-summary").onclick = () => renderStep(1);
  }

  function validateShipping() {
    const fields = {
      firstName: { el: "sg-firstName", container: "field-firstName", validate: (v) => v.trim().length >= 2 },
      lastName:  { el: "sg-lastName",  container: "field-lastName",  validate: (v) => v.trim().length >= 2 },
      // ← numerotel correspond à la colonne BD clientglam.numerotel
      phone:     { el: "sg-phone",     container: "field-phone",     validate: (v) => /^\d[\d\s]{8,}$/.test(v.trim()) },
      wilaya:    { el: "sg-wilaya",    container: "field-wilaya",    validate: (v) => v.trim() !== "" },
      // ← adresse correspond à clientglam.adresse
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
      data.note          = document.getElementById("sg-note")?.value || "";
      orderData.shipping = data;
    }
    return valid;
  }

  // ---- ÉTAPE 3 : PAIEMENT ----
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
        selectedPayment             = opt.dataset.value;
        orderData.payment_method    = selectedPayment;   // ← correspond à orders.payment_method
        body.querySelectorAll(".sg-payment-option").forEach((o) => o.classList.remove("selected"));
        opt.classList.add("selected");
        opt.querySelector("input").checked = true;
      });
    });

    footer.innerHTML = `
      <button class="sg-btn-primary"   id="sg-place-order">Confirmer la commande</button>
      <button class="sg-btn-secondary" id="sg-back-shipping">← Retour</button>`;

    document.getElementById("sg-place-order").onclick  = placeOrder;
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
    let total  = 0;
    Object.values(cart).forEach((i) => (total += i.price * i.quantity));
    const s = orderData.shipping || {};
    return `
      <div style="font-size:13px;color:#555;line-height:1.8">
        <div><strong>Livraison :</strong> ${s.firstName || ""} ${s.lastName || ""}</div>
        <div><strong>Tél :</strong> ${s.phone || ""}</div>
        <div><strong>Wilaya :</strong> ${s.wilaya || ""}</div>
        <div><strong>Adresse :</strong> ${s.address || ""}</div>
        <div style="margin-top:10px;font-size:15px;font-weight:700">Total : ${total.toFixed(2)} DA</div>
      </div>`;
  }

  // ===================================================
  // 8. PLACE ORDER — Payload adapté au schéma BD
  //
  //  Table orders :
  //    order_id       → order.order_id
  //    status         → "pending"
  //    payment_method → order.payment_method   (ex-"payment")
  //    total          → order.total
  //    shipping       → order.shipping  (jsonb : {firstName, lastName, phone, wilaya, address, note})
  //
  //  Table order_items (générée côté PHP depuis order.items) :
  //    product_id  → item.product_id   (id du produit dans la table products)
  //    name        → item.name
  //    shade       → item.shade
  //    quantity    → item.quantity
  //    unit_price  → item.unit_price   (ex-"price")
  // ===================================================
  async function placeOrder() {
    const btn = document.getElementById("sg-place-order");
    if (!btn) return;

    btn.disabled = true;
    btn.innerHTML = `<span class="sg-spinner"></span>Envoi en cours…`;

    const cart  = getCart();
    const items = Object.values(cart);
    let total   = 0;
    items.forEach((i) => (total += i.price * i.quantity));

    // ── Objet commande mappé sur le schéma BD ──
    const order = {
      order_id:       generateOrderId(),           // → orders.order_id
      status:         "pending",                   // → orders.status
      payment_method: orderData.payment_method || "cash",  // → orders.payment_method
      total:          parseFloat(total.toFixed(2)),        // → orders.total
      shipping:       orderData.shipping,          // → orders.shipping (jsonb)
      items: items.map((item) => ({
        product_id: item.id          || item.product_id || null,  // → order_items.product_id
        name:       item.name,                                     // → order_items.name
        shade:      item.shade       || null,                     // → order_items.shade
        quantity:   item.quantity,                                 // → order_items.quantity
        unit_price: parseFloat(item.price.toFixed(2)),            // → order_items.unit_price
      })),
    };

    try {
      const res = await fetch("/includes/place_order.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(order),
      });

      let serverData = {};
      try { serverData = await res.json(); } catch {}

      if (!res.ok) {
        throw new Error(serverData.message || `Erreur serveur (${res.status})`);
      }

      // Sauvegarde locale (pour historique côté client)
      const localOrder = { ...order, created_at: new Date().toISOString() };
      const orders = getOrders();
      orders.unshift(localOrder);
      saveOrders(orders);

      // Vider le panier
      localStorage.removeItem("cart");

      // Mise à jour UI panier
      if (typeof window.renderCart === "function") window.renderCart();

      // Écran de confirmation
      showConfirmation(order);

      // Notifications
      sendNativeNotif(
        "🛍️ Commande confirmée !",
        `Commande ${order.order_id} bien reçue. Livraison sous 3-5 jours ouvrés.`
      );
      showInAppNotif(
        "Commande confirmée !",
        `Votre commande ${order.order_id} a été enregistrée.`,
        "success", 7000
      );

      const totalEl = document.getElementById("cartTotal");
      if (totalEl) totalEl.textContent = "0.00 DA";

    } catch (err) {
      console.error("[Order Error]", err);
      btn.disabled = false;
      btn.innerHTML = "Confirmer la commande";
      showInAppNotif("Erreur", err.message || "Impossible d'envoyer la commande. Réessayez.", "error");
    }
  }

  // ===================================================
  // 9. ÉCRAN DE CONFIRMATION
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
        <p style="font-size:12px;color:#999">Conservez ce numéro pour le suivi de votre livraison.</p>
      </div>`;

    footer.innerHTML = `
      <button class="sg-btn-primary" id="sg-continue-shopping">Continuer mes achats</button>`;

    document.getElementById("sg-continue-shopping").onclick = () => {
      closeCheckout();
      orderData = {};
    };
  }

  // ===================================================
  // 10. WILAYAS D'ALGÉRIE
  // ===================================================
  const WILAYAS = [
    "Adrar","Chlef","Laghouat","Oum El Bouaghi","Batna","Béjaïa","Biskra",
    "Béchar","Blida","Bouira","Tamanrasset","Tébessa","Tlemcen","Tiaret",
    "Tizi Ouzou","Alger","Djelfa","Jijel","Sétif","Saïda","Skikda",
    "Sidi Bel Abbès","Annaba","Guelma","Constantine","Médéa","Mostaganem",
    "M'Sila","Mascara","Ouargla","Oran","El Bayadh","Illizi","Bordj Bou Arréridj",
    "Boumerdès","El Tarf","Tindouf","Tissemsilt","El Oued","Khenchela",
    "Souk Ahras","Tipaza","Mila","Aïn Defla","Naâma","Aïn Témouchent",
    "Ghardaïa","Relizane","Timimoun","Bordj Badji Mokhtar","Ouled Djellal",
    "Béni Abbès","In Salah","In Guezzam","Touggourt","Djanet",
    "El M'Ghair","El Meniaa",
  ];

  // ===================================================
  // 11. INIT — Bouton "Passer commande"
  // ===================================================
  function injectCheckoutButton() {
    const cartFooters = document.querySelectorAll(
      "#cartSidebar .cart-footer, .cart-footer, #cart-footer"
    );
    cartFooters.forEach((footer) => {
      if (footer.querySelector(".sg-open-checkout")) return;
      const btn = document.createElement("button");
      btn.className  = "sg-open-checkout";
      btn.textContent = "Passer commande";
      btn.style.cssText = `
        display:block; width:100%; margin-top:12px; padding:15px;
        background:#111; color:#fff; border:none; border-radius:8px;
        font-size:14px; font-weight:700; letter-spacing:.08em;
        text-transform:uppercase; cursor:pointer;
      `;
      btn.addEventListener("click", () => {
        const cart = getCart();
        if (!Object.keys(cart).length) {
          showInAppNotif("Panier vide", "Ajoutez des produits avant de commander.", "warning");
          return;
        }
        openCheckout();
      });
      footer.appendChild(btn);
    });
  }

  // ===================================================
  // 12. EXPOSE ET INIT
  // ===================================================
  document.addEventListener("DOMContentLoaded", async () => {
    document.body.addEventListener("click", async () => {
      await requestPushPermission();
    }, { once: true });

    await registerSW();
    injectCheckoutButton();

    const observer = new MutationObserver(() => injectCheckoutButton());
    observer.observe(document.body, { childList: true, subtree: true });
  });

  window.SheGlamCheckout = {
    open:   openCheckout,
    close:  closeCheckout,
    notify: showInAppNotif,
  };

})();