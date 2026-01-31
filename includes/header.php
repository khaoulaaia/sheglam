<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- Font Awesome 6 Free (Solid) via CDN -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">


<body>

<header class="header">
  <button class="menu-toggle" id="menuToggle" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>

  <div class="logo">SheGlamour</div>

  <nav class="navbar" id="navbar">
    <a href="/">Accueil</a>
    <a href="/sheglam/products">Boutique</a>
    <a href="#">Nouveautés</a>
    <a href="#">Contact</a>
  </nav>
<div class="icons">
  <button class="icon-btn" id="openSearch">
    <i class="fa-solid fa-magnifying-glass"></i>
  </button>

  <a href="/sheglam/wishlist.php">
    <i class="fas fa-heart"></i>
  </a>

  <!-- SVG sac shopping -->
  <a href="/sheglam/cart.php">
  <svg class="icon-shopping-bag" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="transparent" stroke="#111" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
    <!-- Corps du sac plus arrondi -->
    <path d="M5 8c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V8z"/>
    <!-- Anses fines et arrondies -->
    <path d="M8 8V6a4 4 0 0 1 8 0v2"/>
  </svg>
</a>


  <a href="/sheglam/login.php">
    <i class="fas fa-user"></i>
  </a>
</div>

</header>

<div class="menu-overlay" id="menuOverlay"></div>
</section>

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
<style>/* =====================================================
   RESET
===================================================== */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

html, body {
  font-family: Inter, sans-serif;
  color: #111;
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

.header a,
.header .logo,
.header .icon-btn {
  color: #000;
  text-decoration: none;
}

.header.scrolled {
  background: rgba(255,255,255,.92);
  backdrop-filter: blur(12px);
  box-shadow: 0 4px 20px rgba(0,0,0,.08);
}

.header.search-open {
  pointer-events: none;
}

/* =====================================================
   NAVBAR DESKTOP
===================================================== */
.navbar {
  display: flex;
  gap: 22px;
}

.navbar a {
  font-size: .9rem;
}

/* =====================================================
   ICONS
===================================================== */
.icons {
  display: flex;
  gap: 16px;
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
   MENU OVERLAY
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

/* =====================================================
   MOBILE
===================================================== */
@media (max-width: 768px) {

  .menu-toggle {
    display: block;
  }

  .navbar {
    position: fixed;
    top: 64px;
    left: -100%;
    width: 80%;
    max-width: 320px;
    height: calc(100vh - 64px);
    background: #fff;
    flex-direction: column;
    padding: 24px;
    gap: 18px;
    transition: left .35s ease;
    z-index: 2600;
    box-shadow: 8px 0 30px rgba(0,0,0,.12);
  }

  .navbar.active {
    left: 0;
  }

  .search-container {
    padding: 70px 24px 40px;
    gap: 32px;
  }

  .search-layout {
    display: block;
  }

  .search-sidebar {
    display: none;
  }

  .search-mobile-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    margin: 12px 0;
  }

  .search-results-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
  }

  .search-input-wrapper {
    max-width: 90%;
    padding: 12px 18px;
  }

  .search-input-wrapper input {
    font-size: 20px;
  }
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
          console.log("Menu hamburger ouvert ?", menuToggle.classList.contains("active"));

  });

  menuOverlay.addEventListener("click", () => {
    menuToggle.classList.remove("active");
    navbar.classList.remove("active");
    menuOverlay.classList.remove("active");
          console.log("Menu hamburger fermé");

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
              <strong>€${parseFloat(p.price).toFixed(2)}</strong>
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
