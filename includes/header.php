<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- Font Awesome 6 Free (Solid) via CDN -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">


<body>

<header class="header">
  <!-- LOGO -->
  <div class="logo">SheGlamour</div>

  <!-- NAVBAR DESKTOP -->
  <nav class="navbar-desktop">
    <a href="/sheglam/categorie.php?categorie=Yeux">Yeux</a>
    <a href="/sheglam/categorie.php?categorie=Lèvres">Lèvres</a>
    <a href="/sheglam/categorie.php?categorie=Teint">Teint</a>
    <a href="/sheglam/categorie.php?categorie=Accessoires">Accessoires</a>
  </nav>

  <!-- ICONS -->
  <div class="icons">
    <button class="icon-btn" id="openSearch">
      <i class="fa-solid fa-magnifying-glass"></i>
    </button>
    <a href="/sheglam/wishlist.php"><i class="fas fa-heart"></i></a>
    <a href="/sheglam/cart.php">
      <svg class="icon-shopping-bag" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="transparent" stroke="#111" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
        <path d="M5 8c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V8z"/>
        <path d="M8 8V6a4 4 0 0 1 8 0v2"/>
      </svg>
    </a>
    <a href="/sheglam/login.php"><i class="fas fa-user"></i></a>
  </div>

  <!-- HAMBURGER MOBILE -->
  <button class="menu-toggle" id="menuToggle" aria-label="Menu">
    <span></span>
    <span></span>
    <span></span>
  </button>

  <!-- MENU MOBILE -->
  <nav class="navbar-mobile" id="navbar">
    <div class="mobile-menu-header">
  <div class="mobile-logo">SheGlamour</div>
  <button class="mobile-close" id="mobileClose">&times;</button>
</div>

    <div class="mobile-tabs">
      <button class="tab-btn active" data-tab="categories">Catégories</button>
      <button class="tab-btn" data-tab="brands">Marques</button>
    </div>

    <!-- CATEGORIES PANEL -->
    <div class="tab-content active" id="categories">
      <a href="/sheglam/categorie.php?categorie=Blush" class="menu-item"><img src="images/blush.jpg" alt=""><span>Blush</span></a>
      <a href="/sheglam/categorie.php?categorie=Lèvres" class="menu-item"><img src="images/lips.jpg" alt=""><span>Lèvres</span></a>
      <a href="/sheglam/categorie.php?categorie=Yeux" class="menu-item"><img src="images/eyes.jpg" alt=""><span>Yeux</span></a>
    </div>

    <!-- BRANDS PANEL -->
    <div class="tab-content" id="brands">
  <a href="/sheglam/marque.php?marque=VelvetLab">VelvetLab</a>
  <a href="/sheglam/marque.php?marque=Rare%20Beauty">Rare Beauty</a>
  <a href="/sheglam/marque.php?marque=Maybelline">Maybelline</a>
  <a href="/sheglam/marque.php?marque=Huda%20Beauty">Huda Beauty</a>
</div>

  </nav>

  <!-- OVERLAY MOBILE -->
  <div class="menu-overlay" id="menuOverlay"></div>
</header>

<!-- SEARCH OVERLAY -->
<div class="search-overlay" id="searchOverlay">
  <button class="close-search" id="closeSearch">&times;</button>
  <div class="search-container">
    <div class="search-input-wrapper">
      <input id="searchInput" placeholder="Rechercher un produit…" />
    </div>
    <div class="search-mobile-tags"></div>

    <div class="search-layout">
      <aside class="search-sidebar">
        <h4>Recherches récentes</h4>
        <div class="recent-tags"></div>
        <h4>Tendances</h4>
        <div class="hot-tags">
          <span class="tag">Blush</span>
          <span class="tag">Lip gloss</span>
        </div>
      </aside>

      <main class="search-main">
        <h3>Résultats</h3>
        <div id="searchResults" class="search-results-grid"></div>
      </main>
    </div>
  </div>
</div>

</body>
<style>
/* =====================================================
   RESET
===================================================== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: "Inter", sans-serif;
  color: #111;
  line-height: 1.6;
}

a {
  text-decoration: none;
  color: inherit;
}

/* =====================================================
   HEADER
===================================================== */
.header {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 64px;
  padding: 0 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  z-index: 3000;
  background: transparent;
  transition: all .35s ease;
}

.header.scrolled {
  background: rgba(255,255,255,.95);
  backdrop-filter: blur(12px);
  box-shadow: 0 4px 20px rgba(0,0,0,.08);
}


/* LOGO */
.logo {
  font-size: 20px;
  font-weight: bold;
}

/* =====================================================
   NAVBAR DESKTOP
===================================================== */
.navbar-desktop {
  display: flex;
  gap: 25px;
}

.navbar-desktop a {
  font-size: 14px;
  font-weight: 500;
  padding: 10px 0;
  position: relative;
}

.navbar-desktop a::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0%;
  height: 2px;
  background: #111;
  transition: width .3s;
}

.navbar-desktop a:hover::after {
  width: 100%;
}

/* =====================================================
   ICONS
===================================================== */
.icons {
  display: flex;
  gap: 16px;
  align-items: center;
}

.icon-btn {
  background: none;
  border: none;
  cursor: pointer;
}

/* =====================================================
   HAMBURGER
===================================================== */
.menu-toggle {
  display: none;
  width: 36px;
  height: 28px;
  background: none;
  border: none;
  position: relative;
  cursor: pointer;
}

.menu-toggle span {
  position: absolute;
  left: 0;
  width: 100%;
  height: 2px;
  background: #111;
  transition: .3s;
}

.menu-toggle span:nth-child(1) { top: 4px; }
.menu-toggle span:nth-child(2) { top: 13px; }
.menu-toggle span:nth-child(3) { top: 22px; }

.menu-toggle.active span:nth-child(1) {
  transform: rotate(45deg);
  top: 13px;
}

.menu-toggle.active span:nth-child(2) {
  opacity: 0;
}

.menu-toggle.active span:nth-child(3) {
  transform: rotate(-45deg);
  top: 13px;
}

/* =====================================================
   MENU MOBILE
===================================================== */
.navbar-mobile {
  position: fixed;
  top: 0;
  left: -100%;
  width: 80%;
  max-width: 320px;
  height: 100vh;
  background: #fff;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 18px;
  transition: left .35s ease;
  z-index: 2600;
  box-shadow: 8px 0 30px rgba(0,0,0,.12);
}

.navbar-mobile.active {
  left: 0;
}

/* Mobile Tabs */
.mobile-tabs {
  display: flex;
  border-bottom: 1px solid #eee;
  margin-bottom: 16px;
}

.tab-btn {
  flex: 1;
  padding: 14px;
  background: none;
  border: none;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: .25s;
}

.tab-btn.active {
  border-bottom: 2px solid #111;
}

.tab-content {
  display: none;
  flex-direction: column;
  gap: 16px;
}

.tab-content.active {
  display: flex;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 15px;
}

.menu-item img {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 10px;
}

/* =====================================================
   OVERLAY
===================================================== */
.menu-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.45);
  opacity: 0;
  pointer-events: none;
  transition: .3s;
  z-index: 2500;
}

.menu-overlay.active {
  opacity: 1;
  pointer-events: auto;
}

/* =====================================================
   MOBILE LAYOUT
===================================================== */
@media (max-width: 1024px) {

  /* cacher menu desktop */
  .navbar-desktop {
    display: none;
  }

  .header {
    justify-content: center;
    padding: 0 16px;
  }

  /* logo parfaitement centré */
  .logo {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
  }

  /* hamburger à gauche */
  .menu-toggle {
    display: block;
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
  }

  /* icons à droite */
  .icons {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
  }
}

/* =====================================================
   SEARCH OVERLAY
===================================================== */
.search-overlay {
  position: fixed;
  inset: 0;
  background: #fff;
  z-index: 4000;
  transform: translateY(-100%);
  transition: transform .6s cubic-bezier(.4,0,.2,1);
  overflow-y: auto;
  height: 50%;
}

.search-overlay.active {
  transform: translateY(0);
}

/* =====================================================
   CLOSE SEARCH
===================================================== */
.close-search {
  position: fixed;
  top: 24px;
  right: 32px;
  font-size: 32px;
  font-weight: 300;
  background: none;
  border: none;
  cursor: pointer;
  opacity: .6;
  transition: opacity .3s ease;
}

.close-search:hover {
  opacity: 1;
}

/* =====================================================
   SEARCH CONTAINER
===================================================== */
.search-container {
  padding: 90px 80px 60px;
  display: flex;
  flex-direction: column;
  gap: 48px;
}

/* =====================================================
   SEARCH INPUT – COUTURE
===================================================== */
.search-input-wrapper {
  max-width: 620px;
  margin: 0 auto;
  padding: 14px 24px;
  border: 1px solid rgba(0,0,0,.35);
  display: flex;
  align-items: center;
}

.search-input-wrapper input {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  text-align: center;
  font-family: "Didot", "Playfair Display", serif;
  font-size: 26px;
  letter-spacing: .05em;
  color: #111;
}

.search-input-wrapper input::placeholder {
  color: rgba(0,0,0,.45);
  font-style: italic;
}

.search-input-wrapper:focus-within {
  border-color: #111;
}

/* =====================================================
   SEARCH LAYOUT
===================================================== */
.search-layout {
  display: grid;
  grid-template-columns: 28% 72%;
  gap: 48px;
  
}

/* =====================================================
   SIDEBAR – ÉDITORIAL
===================================================== */
.search-sidebar {
  padding-right: 32px;
  border-right: 1px solid rgba(0,0,0,.06);
}

.search-sidebar h4 {
  font-family: "Didot", "Playfair Display", serif;
  font-size: 12px;
  letter-spacing: .24em;
  text-transform: uppercase;
  margin: 32px 0 16px;
}

.search-sidebar h4:first-child {
  margin-top: 0;
}

.recent-tags,
.hot-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
}

.tag {
  font-size: 12px;
  letter-spacing: .08em;
  background: none;
  border: none;
  padding: 4px 0;
  cursor: pointer;
  border-bottom: 1px solid transparent;
  transition: border-color .25s ease;
}

.tag:hover {
  border-color: #111;
}

/* =====================================================
   SEARCH RESULTS
===================================================== */
.search-main h3 {
  font-size: 11px;
  letter-spacing: .22em;
  text-transform: uppercase;
  color: #999;
  margin-bottom: 20px;
}

.search-results-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 22px;
}

.search-product {
  text-align: center;
  cursor: pointer;
  transition: transform .3s ease;
}

.search-product:hover {
  transform: translateY(-4px);
}

.search-product img {
  width: 100%;
  aspect-ratio: 1 / 1;
  object-fit: contain;
  padding: 10px;
  transition: transform .35s ease;
}

.search-product:hover img {
  transform: scale(1.06);
}

.search-product p,
.search-product strong {
  font-size: 12px;
  margin-top: 8px;
}

/* =====================================================
   TAGS MOBILE (HIDDEN DESKTOP)
===================================================== */
.search-mobile-tags {
  display: none;
}

  /* ======================
     BRANDS LIST
  ====================== */
  #brands a {
    text-decoration: none;
    color: #111;
    font-size: 15px;
    padding: 10px 0;
    border-bottom: 1px solid #f1f1f1;
  }
/* ======================
   SEARCH MOBILE FIX
====================== */
@media (max-width: 1024px) {

  .search-container {
    padding: 70px 20px 40px;
    gap: 32px;
  }

  .search-layout {
    display: block;
  }

  .search-sidebar {
    display: none;
  }

  .search-results-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
  }

  .search-input-wrapper {
    max-width: 100%;
    padding: 12px 18px;
  }

  .search-input-wrapper input {
    font-size: 20px;
  }

}

  /* ======================
     ANIMATION
  ====================== */
  @keyframes slideFade {
    from {
      opacity: 0;
      transform: translateX(10px);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }
/* ======================
   SEARCH MOBILE DISPLAY
====================== */
@media (max-width: 1024px) {

  .search-mobile-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: center;
    margin-top: 10px;
  }

}
.mobile-menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.mobile-logo {
  font-size: 18px;
  font-weight: bold;
}

.mobile-close {
  background: none;
  border: none;
  font-size: 26px;
  cursor: pointer;
}

/* Header blanc quand menu ouvert */
body.menu-open .header {
  box-shadow: 0 4px 20px rgba(0,0,0,.08);
  backdrop-filter: none !important;
}
</style>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const menuToggle = document.getElementById("menuToggle");
  const navbar = document.getElementById("navbar");
  const menuOverlay = document.getElementById("menuOverlay");
  const openSearch = document.getElementById("openSearch");
  const closeSearch = document.getElementById("closeSearch");
  const overlay = document.getElementById("searchOverlay");
  const input = document.getElementById("searchInput");
  const results = document.getElementById("searchResults");
  const recentBox = document.querySelector(".recent-tags");
  const mobileTagsBox = document.querySelector(".search-mobile-tags");
  

  /* ==========================
       MENU MOBILE
  ========================== */
  menuToggle.addEventListener("click", () => {
    menuToggle.classList.toggle("active");
    navbar.classList.toggle("active");
    menuOverlay.classList.toggle("active");
    document.body.classList.toggle("menu-open"); // <-- ajoute ça
});

menuOverlay.addEventListener("click", () => {
    menuToggle.classList.remove("active");
    navbar.classList.remove("active");
    menuOverlay.classList.remove("active");
    document.body.classList.remove("menu-open"); // <-- et ici
});


  /* ==========================
       RECHERCHES RÉCENTES
  ========================== */
  function getRecentSearches() {
    return JSON.parse(localStorage.getItem("recentSearches") || "[]");
  }

  function saveRecentSearch(term) {
    let list = getRecentSearches();
    if (!list.includes(term)) {
      list.unshift(term);
      if (list.length > 8) list.pop();
      localStorage.setItem("recentSearches", JSON.stringify(list));
    }
  }

  function bindTagRedirect(container) {
    if (!container) return;
    container.querySelectorAll(".tag").forEach(tag => {
      tag.addEventListener("click", () => {
        const q = tag.textContent.trim();
        if (q) window.location.href = `/sheglam/search.php?q=${encodeURIComponent(q)}`;
      });
    });
  }

  function renderRecentSearches() {
    const list = getRecentSearches();
    if (!recentBox) return;

    recentBox.innerHTML = list.length
      ? list.map(t => `<span class="tag">${t}</span>`).join("")
      : "<p>Aucune recherche</p>";

    bindTagRedirect(recentBox);
  }

  function renderMobileTags() {
    if (!mobileTagsBox) return;

    const recent = getRecentSearches();
    let html = "";

    if (recent.length) html += recent.map(t => `<span class="tag">${t}</span>`).join("");

    // Tendance
    html += `<span class="tag">Blush</span><span class="tag">Lip gloss</span>`;

    mobileTagsBox.innerHTML = html;
    bindTagRedirect(mobileTagsBox);
  }

  /* ==========================
       OUVERTURE / FERMETURE OVERLAY
  ========================== */
  openSearch.addEventListener("click", () => {
    overlay.classList.add("active");
    document.querySelector(".header").classList.add("search-open");
    renderRecentSearches();
    renderMobileTags();
    setTimeout(() => input.focus(), 100);
  });

  closeSearch.addEventListener("click", () => {
    overlay.classList.remove("active");
    document.querySelector(".header").classList.remove("search-open");
    input.value = "";
    results.innerHTML = "";
  });

  /* ==========================
       RECHERCHE EN DIRECT
  ========================== */
  let timer;
  input.addEventListener("input", () => {
    clearTimeout(timer);
    const q = input.value.trim();
    if (q.length < 2) {
      results.innerHTML = "";
      return;
    }

    timer = setTimeout(async () => {
      saveRecentSearch(q);
      renderRecentSearches();

      try {
        const res = await fetch(`/sheglam/includes/search_products.php?query=${encodeURIComponent(q)}`);
        const data = await res.json();
        results.innerHTML = data.length
          ? data.map(p => `
            <div class="search-product">
              <img src="${p.image_url}" alt="${p.name}">
              <p>${p.name}</p>
              <strong>DA${parseFloat(p.price).toFixed(2)}</strong>
            </div>
          `).join("")
          : "<p>Aucun produit trouvé.</p>";
      } catch (err) {
        results.innerHTML = "<p>Erreur lors de la recherche.</p>";
        console.error(err);
      }
    }, 300);
  });

  /* ==========================
       BOUTON "OK" / ENTRÉE
  ========================== */
  input.addEventListener("keydown", e => {
    if (e.key === "Enter") {
      const q = input.value.trim();
      if (q) window.location.href = `/sheglam/search.php?q=${encodeURIComponent(q)}`;
    }
  });
});
</script>
<script>
  /* ==========================
   SWITCH TABS MOBILE MENU
========================== */
const tabButtons = document.querySelectorAll(".tab-btn");
const tabContents = document.querySelectorAll(".tab-content");

tabButtons.forEach(btn => {
  btn.addEventListener("click", () => {

    // remove active
    tabButtons.forEach(b => b.classList.remove("active"));
    tabContents.forEach(c => c.classList.remove("active"));

    // add active
    btn.classList.add("active");
    document.getElementById(btn.dataset.tab).classList.add("active");

  });
});
const mobileClose = document.getElementById("mobileClose");

mobileClose.addEventListener("click", () => {
  menuToggle.classList.remove("active");
  navbar.classList.remove("active");
  menuOverlay.classList.remove("active");
  document.body.classList.remove("menu-open");
});

</script>