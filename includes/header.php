<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SheGlamour – Header</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="<?= $b ?>/categorie.css?v=<?= time() ?>">
      <link rel="stylesheet" href="<?= $b ?>/sidebar.css?v=<?= time() ?>">


  <style>

/* ═══════════════════════════════════
   VARIABLES
═══════════════════════════════════ */

:root {
  --bordeaux:   #440B19;
  --bordeaux-s: #5c1022;
  --bordeaux-l: #6e1a2e;
  --bordeaux-xl:#8a2a3e;

  --white:      #ffffff;

  --border:     rgba(68, 11, 25, 0.15);
  --border-d:   #440B19;

  --text:       #440B19;
  --text-muted: #6e1a2e;
  --muted:      #6e1a2e;

  --serif:  'Cormorant Garamond', Georgia, serif;
  --sans:   'Jost', system-ui, sans-serif;
  --ease:   cubic-bezier(0.25, 0.46, 0.45, 0.94);
  --dur:    .38s;
}


/* ═══════════════════════════════════
   RESET
═══════════════════════════════════ */

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: var(--sans);
  color: var(--text);
  background: var(--white);
  min-height: 200vh;
}

a { text-decoration: none; color: inherit; }


/* ═══════════════════════════════════
   HEADER — TRANSPARENT PAR DÉFAUT
═══════════════════════════════════ */

.header {
  position: fixed;
  top: 0; left: 0;
  width: 100%;
  height: 72px;
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  padding: 0 5%;
  z-index: 2000;
  background: transparent;
  transition:
    background var(--dur) var(--ease),
    box-shadow  var(--dur) var(--ease),
    height      var(--dur) var(--ease);
}

.header::after {
  content: '';
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 1px;
  background: transparent;
  transition: background var(--dur) var(--ease);
}

/* ═══════════════════════════════════
   HEADER — SCROLLED
═══════════════════════════════════ */

.header.scrolled {
  background: #F5F1EE;
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  box-shadow:
    0 1px 0 var(--border),
    0 10px 40px rgba(68, 11, 25, 0.06);
  height: 64px;
}

.header.scrolled::after {
  background: var(--border);
}


/* ═══════════════════════════════════
   LOGO
═══════════════════════════════════ */

.logo {
  justify-self: start;
  display: flex;
  align-items: center;
  text-decoration: none;
  position: relative;
  z-index: 20;
}

.logo img {
  height: 70px;
  width: auto;
  display: block;
  object-fit: contain;
  image-rendering: -webkit-optimize-contrast;
  image-rendering: crisp-edges;
  backface-visibility: hidden;
  transform: translateZ(0);
  transition:
    opacity .25s var(--ease),
    transform .25s var(--ease);
}

.logo:hover img {
  opacity: .88;
  transform: scale(1.02) translateZ(0);
}


/* ═══════════════════════════════════
   NAVBAR DESKTOP
═══════════════════════════════════ */

.navbar-desktop {
  display: flex;
  align-items: center;
  gap: 36px;
  justify-self: center;
}

.navbar-desktop a {
  position: relative;
  padding: 6px 0;
  font-family: var(--sans);
  font-size: .68rem;
  font-weight: 500;
  letter-spacing: .24em;
  text-transform: uppercase;
  color: var(--white);
  transition: color var(--dur), opacity var(--dur);
}

.navbar-desktop a::after {
  content: '';
  position: absolute;
  left: 0; bottom: 0;
  width: 0; height: 1px;
  background: var(--white);
  transition: width var(--dur) var(--ease);
}

.navbar-desktop a:hover { opacity: .75; }
.navbar-desktop a:hover::after { width: 100%; }

/* Scrolled — bordeaux */
.header.scrolled .navbar-desktop a {
  color: var(--bordeaux);
}

.header.scrolled .navbar-desktop a::after {
  background: var(--bordeaux);
}

.header.scrolled .navbar-desktop a:hover {
  opacity: 1;
  color: var(--bordeaux-s);
}


/* ═══════════════════════════════════
   ICONS
═══════════════════════════════════ */

.icons {
  display: flex;
  align-items: center;
  gap: 20px;
  justify-self: end;
}

.icon-btn,
.icons a {
  width: 36px; height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: none;
  background: none;
  cursor: pointer;

  /* Blanc par défaut */
  color: var(--white);

  transition: color var(--dur), transform var(--dur), opacity var(--dur);
}

.icon-btn svg,
.icons a svg {
  width: 20px; height: 20px;
  display: block;
  flex-shrink: 0;
}

.icon-btn:hover,
.icons a:hover {
  opacity: .75;
  transform: translateY(-1px);
}

/* Scrolled — bordeaux */
.header.scrolled .icon-btn,
.header.scrolled .icons a {
  color: var(--bordeaux);
}

.header.scrolled .icon-btn:hover,
.header.scrolled .icons a:hover {
  opacity: 1;
  color: var(--bordeaux-l);
}

.header-divider { display: none; }

.search-input-wrapper svg {
  width: 20px; height: 20px;
  display: block;
  flex-shrink: 0;
}


/* ═══════════════════════════════════
   HAMBURGER
═══════════════════════════════════ */

.menu-toggle {
  display: none;
  flex-direction: column;
  justify-content: space-between;
  width: 28px; height: 20px;
  position: absolute;
  left: 18px; top: 50%;
  transform: translateY(-50%);
  border: none;
  background: none;
  cursor: pointer;
}

.menu-toggle span {
  width: 100%; height: 1px;
  display: block;

  /* Blanc par défaut */
  background: var(--white);

  transform-origin: left center;
  transition:
    transform     var(--dur) var(--ease),
    opacity       var(--dur),
    width         var(--dur),
    background    var(--dur);
}

.menu-toggle span:nth-child(2) { width: 70%; }
.menu-toggle:hover span:nth-child(2) { width: 100%; }
.menu-toggle.active span:nth-child(1) { transform: rotate(42deg); }
.menu-toggle.active span:nth-child(2) { opacity: 0; width: 0; }
.menu-toggle.active span:nth-child(3) { transform: rotate(-42deg); }

/* Scrolled — bordeaux */
.header.scrolled .menu-toggle span {
  background: var(--bordeaux);
}


/* ═══════════════════════════════════
   MENU MOBILE
═══════════════════════════════════ */

.navbar-mobile {
  position: fixed;
  top: 0; left: -100%;
  width: 78%; max-width: 300px;
  height: 100dvh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background: var(--white);
  transition: left .4s var(--ease);
  z-index: 2600;
}

.navbar-mobile.active { left: 0; background: #F5F1EE !important; }

.navbar-mobile::before {
  content: '';
  width: 100%; height: 3px;
  display: block;
  flex-shrink: 0;
  background: linear-gradient(90deg, var(--bordeaux-l), var(--bordeaux));
}

.mobile-menu-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 22px 18px;
  border-bottom: 1px solid var(--border);
}

.mobile-logo {
  display: flex;
  align-items: center;
  text-decoration: none;
}

.mobile-logo img {
  height: 150px !important;
  width: auto;
  display: block;
  object-fit: contain;
  image-rendering: -webkit-optimize-contrast;
  image-rendering: crisp-edges;
  backface-visibility: hidden;
  transform: translateZ(0);
}


/* ═══════════════════════════════════
   TABS
═══════════════════════════════════ */

.mobile-tabs {
  display: flex;
  border-bottom: 1px solid var(--border);
}

.tab-btn {
  flex: 1;
  padding: 13px 8px;
  border: none;
  background: none;
  cursor: pointer;
  font-family: var(--sans);
  font-size: .62rem;
  font-weight: 500;
  letter-spacing: .18em;
  text-transform: uppercase;
  color: rgba(68, 11, 25, .35);
  border-bottom: 2px solid transparent;
  transition: color var(--dur), border-color var(--dur);
}

.tab-btn.active {
  color: var(--bordeaux);
  border-bottom-color: var(--bordeaux);
}


/* ═══════════════════════════════════
   ITEMS MENU MOBILE
═══════════════════════════════════ */

.tab-content {
  display: none;
  flex-direction: column;
  flex: 1;
  overflow-y: auto;
}

.tab-content.active { display: flex; }

.menu-item,
#brands a {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 13px 22px;
  border-bottom: 1px solid var(--border);
  font-size: .86rem;
  color: var(--bordeaux);
  transition: background var(--dur), padding-left var(--dur), color var(--dur);
}

.menu-item:hover,
#brands a:hover {
  background: rgba(68, 11, 25, .04);
  padding-left: 28px;
  color: var(--bordeaux-s);
}

.menu-item img {
  width: 44px; height: 44px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid var(--border);
}

.mobile-footer {
  padding: 18px 22px;
  font-family: var(--serif);
  font-size: .78rem;
  font-style: italic;
  color: var(--muted);
  border-top: 1px solid var(--border);
  margin-top: auto;
}


/* ═══════════════════════════════════
   BOUTONS FERMER
═══════════════════════════════════ */

.mobile-close {
  width: 34px; height: 34px;
  background: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  color: var(--bordeaux);
  font-size: 22px;
  font-weight: 300;
  cursor: pointer;
  transition: color var(--dur), transform var(--dur);
}

.mobile-close:hover {
  color: var(--bordeaux-s);
  transform: rotate(90deg);
}

.close-search {
  position: absolute;
  top: 24px; right: 28px;
  width: 34px; height: 34px;
  border: none;
  background: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(68, 11, 25, .55);
  font-size: 26px;
  font-weight: 300;
  line-height: 1;
  cursor: pointer;
  transition: color var(--dur), transform var(--dur);
}

.close-search:hover {
  color: var(--bordeaux);
  transform: rotate(90deg);
}


/* ═══════════════════════════════════
   MENU OVERLAY
═══════════════════════════════════ */

.menu-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(68, 11, 25, .38);
  backdrop-filter: blur(3px);
  -webkit-backdrop-filter: blur(3px);
  z-index: 2500;
  opacity: 0;
  transition: opacity .35s var(--ease);
}

.menu-overlay.active {
  display: block;
  opacity: 1;
}


/* ═══════════════════════════════════
   BOTTOM BAR MOBILE
═══════════════════════════════════ */

.mobile-bottom-bar { display: none; }


/* ═══════════════════════════════════
   SEARCH OVERLAY
═══════════════════════════════════ */

.search-overlay {
  position: fixed;
  inset: 0;
  z-index: 4000;
  background: rgba(255, 255, 255, 0.97);
  backdrop-filter: blur(22px);
  -webkit-backdrop-filter: blur(22px);
  transform: translateY(-100%);
  transition: transform .55s var(--ease);
  overflow-y: auto;
  height: 72%;
}

.search-overlay.active { transform: translateY(0); }

.search-container {
  padding: 82px 7% 50px;
  display: flex;
  flex-direction: column;
  gap: 42px;
}

.search-input-wrapper {
  width: 100%;
  max-width: 720px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  padding: 0 0 16px;
  border-bottom: 1px solid var(--border);
  transition: border-color var(--dur);
}

.search-input-wrapper:focus-within { border-color: var(--bordeaux); }

.search-input-wrapper svg {
  width: 20px; height: 20px;
  flex-shrink: 0;
  color: rgba(68, 11, 25, .35);
  transition: color var(--dur);
}

.search-input-wrapper:focus-within svg { color: var(--bordeaux); }

.search-input-wrapper input {
  flex: 1;
  border: none; outline: none;
  background: transparent;
  font-family: var(--serif);
  font-size: 1.9rem;
  font-weight: 300;
  letter-spacing: .04em;
  color: var(--bordeaux);
  padding-top: 2px;
}

.search-input-wrapper input::placeholder {
  color: rgba(68, 11, 25, .24);
  font-style: italic;
}

.search-layout {
  display: grid;
  grid-template-columns: 250px 1fr;
  gap: 52px;
  align-items: start;
}

.search-sidebar {
  padding-right: 28px;
  border-right: 1px solid var(--border);
}

.search-sidebar h4 {
  margin: 0 0 14px;
  font-family: var(--sans);
  font-size: .58rem;
  font-weight: 600;
  letter-spacing: .24em;
  text-transform: uppercase;
  color: var(--bordeaux-l);
}

.search-sidebar h4:not(:first-child) { margin-top: 30px; }

.recent-tags,
.hot-tags,
.search-mobile-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.tag {
  padding: 8px 15px;
  border: 1px solid var(--border);
  border-radius: 999px;
  background: transparent;
  color: var(--bordeaux);
  font-family: var(--sans);
  font-size: .72rem;
  font-weight: 500;
  letter-spacing: .03em;
  cursor: pointer;
  transition:
    background  var(--dur),
    border-color var(--dur),
    color        var(--dur),
    transform    var(--dur),
    box-shadow   var(--dur);
}

.tag:hover {
  background: var(--bordeaux);
  border-color: var(--bordeaux);
  color: var(--white);
  transform: translateY(-2px);
  box-shadow: 0 10px 22px rgba(68, 11, 25, .16);
}

.search-main h3 {
  margin-bottom: 20px;
  font-family: var(--sans);
  font-size: .58rem;
  font-weight: 600;
  letter-spacing: .24em;
  text-transform: uppercase;
  color: var(--bordeaux-l);
}

.search-results-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 22px;
}

.recent-tags p {
  font-size: .74rem;
  font-style: italic;
  color: var(--bordeaux-l);
}


/* ═══════════════════════════════════
   SEARCH PRODUCT CARD
═══════════════════════════════════ */

.search-product {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 10px;
  padding: 14px 10px 16px;
  background: var(--white);
  border: 1px solid var(--border);
  cursor: pointer;
  text-decoration: none;
  color: inherit;
  position: relative;
  overflow: hidden;
  transition:
    border-color .3s  var(--ease),
    transform    .35s var(--ease),
    box-shadow   .35s var(--ease);
}

.search-product::after {
  content: '';
  position: absolute;
  bottom: 0; left: 50%;
  transform: translateX(-50%);
  width: 0; height: 1.5px;
  background: var(--bordeaux);
  transition: width .35s var(--ease);
}

.search-product:hover {
  border-color: var(--bordeaux);
  transform: translateY(-4px);
  box-shadow: 0 14px 36px rgba(68, 11, 25, .10);
}

.search-product:hover::after { width: 50%; }

.search-product img {
  width: 100%;
  aspect-ratio: 1 / 1;
  object-fit: contain;
  background: var(--white);
  border: 1px solid var(--border);
  display: block;
  transition: transform .45s var(--ease);
}

.search-product:hover img { transform: scale(1.06); }

.search-product p {
  font-family: var(--serif);
  font-size: 12px;
  font-weight: 400;
  font-style: italic;
  letter-spacing: 0.04em;
  color: var(--bordeaux);
  line-height: 1.4;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.search-product strong {
  font-family: var(--sans);
  font-size: 11px;
  font-weight: 500;
  color: var(--bordeaux-l);
  letter-spacing: 0.08em;
}


/* ═══════════════════════════════════
   RESPONSIVE ≤ 1024px
═══════════════════════════════════ */

@media (max-width: 1024px) {

  .navbar-desktop { display: none; }

  .header {
    display: flex;
    justify-content: center;
    padding: 0 16px;
    height: 58px;
  }

  /* Logo centré sur mobile */
  .logo {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    justify-self: unset;
  }

  .logo img {
    height: 36px;
    max-width: 200px;
  }

  .menu-toggle { display: flex; }

  .icons {
    position: absolute;
    right: 16px; top: 50%;
    transform: translateY(-50%);
  }

  .icons .hide-mobile { display: none; }

  /* Bottom bar */
  .mobile-bottom-bar {
    position: fixed;
    bottom: 0; left: 0;
    width: 100%; height: 62px;
    display: flex;
    align-items: center;
    justify-content: space-around;
    padding: 0 8px;
    background: #F5F1EE;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-top: 1px solid var(--border);
    box-shadow: 0 -4px 30px rgba(68, 11, 25, .05);
    z-index: 2900;
  }

  .bottom-bar-item {
    position: relative;
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 8px 0;
    text-decoration: none;
    color: var(--bordeaux-l);
    font-family: var(--sans);
    font-size: .55rem;
    font-weight: 500;
    letter-spacing: .12em;
    text-transform: uppercase;
    transition: color var(--dur);
  }

  .bottom-bar-item svg { width: 20px; height: 20px; display: block; }

  .bottom-bar-item:hover,
  .bottom-bar-item:active { color: var(--bordeaux); }

  .bottom-bar-item + .bottom-bar-item::before {
    content: '';
    position: absolute;
    left: 0; top: 20%; bottom: 20%;
    width: 1px;
    background: var(--border);
  }

  .bottom-bar-item .badge {
    position: absolute;
    top: 4px; left: 50%;
    margin-left: 4px;
    min-width: 16px; height: 16px;
    padding: 0 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 99px;
    background: var(--bordeaux);
    color: var(--white);
    font-size: .6rem;
    font-weight: 600;
  }

  body { padding-bottom: 62px; }

  /* Search overlay mobile */
  .search-overlay        { height: 100%; }
  .search-container      { padding: 78px 20px 120px; }
  .search-layout         { display: block; }
  .search-sidebar        { display: none; }
  .search-input-wrapper  { max-width: 100%; }
  .search-input-wrapper input { font-size: 1.4rem; }
  .search-results-grid   { grid-template-columns: repeat(2, 1fr); gap: 14px; }
  .search-mobile-tags    { display: flex; justify-content: center; }
  .close-search          { top: 20px; right: 18px; }
}
#openSearch {
  color: var(--white) !important;
}

.header.scrolled #openSearch {
  color: var(--bordeaux) !important;
}

/* Mobile (≤ 768 px) */
@media (max-width: 768px) {
  .logo img {
    height: 70px !important;
  }
}
  </style>
</head>
<body>

<!-- =============================================
     HEADER
============================================= -->
<header class="header" id="header">

  <button class="menu-toggle" id="menuToggle" aria-label="Ouvrir le menu">
    <span></span><span></span><span></span>
  </button>

  <a href="/index.php" class="logo">
    <img src="/images/logowhite.png" alt="SheGlamour" id="headerLogo" />
  </a>

  <nav class="navbar-desktop">
    <a href="/categorie.php?categorie=Yeux">Yeux</a>
    <a href="/categorie.php?categorie=Lèvres">Lèvres</a>
    <a href="/categorie.php?categorie=Teint">Teint</a>
    <a href="/categorie.php?categorie=Accessoires">Accessoires</a>
  </nav>

  <div class="header-divider"></div>

  <div class="icons">
    <button class="icon-btn" id="openSearch" aria-label="Rechercher">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="10.5" cy="10.5" r="6.5"/><line x1="15.5" y1="15.5" x2="21" y2="21"/>
      </svg>
    </button>
    <a href="/wishlist.php" class="hide-mobile" aria-label="Wishlist">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
      </svg>
    </a>
    <a href="/cart.php" class="hide-mobile" aria-label="Panier">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
        <line x1="3" y1="6" x2="21" y2="6"/>
        <path d="M16 10a4 4 0 0 1-8 0"/>
      </svg>
    </a>
    <a href="/login.php" class="hide-mobile" aria-label="Mon compte">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="8" r="4"/>
        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
      </svg>
    </a>
  </div>

</header>


<!-- =============================================
     BOTTOM BAR MOBILE
============================================= -->
<nav class="mobile-bottom-bar" aria-label="Navigation rapide">
  <a href="/wishlist.php" class="bottom-bar-item">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
      <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
    </svg>
    <span>Favoris</span>
  </a>
  <a href="/cart.php" class="bottom-bar-item">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
      <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
      <line x1="3" y1="6" x2="21" y2="6"/>
      <path d="M16 10a4 4 0 0 1-8 0"/>
    </svg>
    <span>Panier</span>
  </a>
  <a href="/login.php" class="bottom-bar-item">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="8" r="4"/>
      <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
    </svg>
    <span>Compte</span>
  </a>
</nav>


<!-- =============================================
     MENU MOBILE
============================================= -->
<nav class="navbar-mobile" id="navbar">

  <div class="mobile-menu-header">
    <a href="/index.php" class="mobile-logo">
      <img src="/images/logofib.png" alt="SheGlamour" />
    </a>
    <button class="mobile-close" id="mobileClose" aria-label="Fermer">&times;</button>
  </div>

  <div class="mobile-tabs">
    <button class="tab-btn active" data-tab="categories">Catégories</button>
    <button class="tab-btn" data-tab="brands">Marques</button>
  </div>

  <div class="tab-content active" id="categories">
    <a href="/categorie.php?categorie=Blush"      class="menu-item"><img src="/images/blush.jpg" alt="Blush" /><span>Blush</span></a>
    <a href="/categorie.php?categorie=Lèvres"     class="menu-item"><img src="/images/lips.jpg"  alt="Lèvres" /><span>Lèvres</span></a>
    <a href="/categorie.php?categorie=Yeux"       class="menu-item"><img src="/images/eyes.jpg"  alt="Yeux" /><span>Yeux</span></a>
    <a href="/categorie.php?categorie=Teint"      class="menu-item"><img src="/images/teint.jpg" alt="Teint" /><span>Teint</span></a>
    <a href="/categorie.php?categorie=Accessoires" class="menu-item"><img src="/images/acc.jpg"  alt="Accessoires" /><span>Accessoires</span></a>
  </div>

  <div class="tab-content" id="brands">
    <a href="/marque.php?marque=VelvetLab">VelvetLab</a>
    <a href="/marque.php?marque=Rare%20Beauty">Rare Beauty</a>
    <a href="/marque.php?marque=Maybelline">Maybelline</a>
    <a href="/marque.php?marque=Huda%20Beauty">Huda Beauty</a>
  </div>

  <div class="mobile-footer">Beauté éditée avec soin ✦</div>

</nav>

<div class="menu-overlay" id="menuOverlay"></div>


<!-- =============================================
     SEARCH OVERLAY
============================================= -->
<div class="search-overlay" id="searchOverlay">
  <button class="close-search" id="closeSearch" aria-label="Fermer">&times;</button>
  <div class="search-container">

    <div class="search-input-wrapper">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="10.5" cy="10.5" r="6.5"/><line x1="15.5" y1="15.5" x2="21" y2="21"/>
      </svg>
      <input id="searchInput" placeholder="Rechercher un produit…" autocomplete="off" />
    </div>

    <div class="search-mobile-tags"></div>

    <div class="search-layout">
      <aside class="search-sidebar">
        <h4>Tendances</h4>
        <div class="hot-tags">
          <span class="tag">Blush</span>
          <span class="tag">Lip gloss</span>
          <span class="tag">Fond de teint</span>
          <span class="tag">Mascara</span>
        </div>
        <h4>Recherches récentes</h4>
        <div class="recent-tags"></div>
      </aside>
      <main class="search-main">
        <h3>Résultats</h3>
        <div id="searchResults" class="search-results-grid"></div>
      </main>
    </div>

  </div>
</div>


<!-- =============================================
     SCRIPTS
============================================= -->
<script>
document.addEventListener("DOMContentLoaded", () => {

  const header        = document.getElementById("header");
  const headerLogo    = document.getElementById("headerLogo");
  const menuToggle    = document.getElementById("menuToggle");
  const navbar        = document.getElementById("navbar");
  const menuOverlay   = document.getElementById("menuOverlay");
  const mobileClose   = document.getElementById("mobileClose");
  const openSearch    = document.getElementById("openSearch");
  const closeSearch   = document.getElementById("closeSearch");
  const searchOverlay = document.getElementById("searchOverlay");
  const input         = document.getElementById("searchInput");
  const results       = document.getElementById("searchResults");
  const recentBox     = document.querySelector(".recent-tags");
  const mobileTagsBox = document.querySelector(".search-mobile-tags");

  /* ── Scroll : header + swap logo ── */
  const onScroll = () => {
    const scrolled = window.scrollY > 30;
    header.classList.toggle("scrolled", scrolled);
    if (headerLogo) {
      headerLogo.src = scrolled
        ? "/images/logofib.png"
        : "/images/logowhite.png";
    }
  };
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll(); // état initial


  /* ── Menu mobile ── */
  const openMenu = () => {
    menuToggle.classList.add("active");
    navbar.classList.add("active");
    menuOverlay.classList.add("active");
    document.body.style.overflow = "hidden";
  };
  const closeMenu = () => {
    menuToggle.classList.remove("active");
    navbar.classList.remove("active");
    menuOverlay.classList.remove("active");
    document.body.style.overflow = "";
  };

  menuToggle.addEventListener("click",  () => navbar.classList.contains("active") ? closeMenu() : openMenu());
  menuOverlay.addEventListener("click", closeMenu);
  mobileClose.addEventListener("click", closeMenu);


  /* ── Tabs menu mobile ── */
  document.querySelectorAll(".tab-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
      document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));
      btn.classList.add("active");
      document.getElementById(btn.dataset.tab).classList.add("active");
    });
  });


  /* ── Recherches récentes ── */
  const getRecent  = () => JSON.parse(localStorage.getItem("recentSearches") || "[]");
  const saveRecent = (term) => {
    let list = getRecent();
    if (!list.includes(term)) {
      list.unshift(term);
      if (list.length > 8) list.pop();
      localStorage.setItem("recentSearches", JSON.stringify(list));
    }
  };

  const bindTagRedirect = (container) => {
    if (!container) return;
    container.querySelectorAll(".tag").forEach(tag => {
      tag.addEventListener("click", () => {
        const q = tag.textContent.trim();
        if (q) window.location.href = `/search.php?q=${encodeURIComponent(q)}`;
      });
    });
  };

  const renderRecent = () => {
    if (!recentBox) return;
    const list = getRecent();
    recentBox.innerHTML = list.length
      ? list.map(t => `<span class="tag">${t}</span>`).join("")
      : `<p style="font-size:.73rem;color:var(--bordeaux-l);font-style:italic">Aucune recherche</p>`;
    bindTagRedirect(recentBox);
  };

  const renderMobileTags = () => {
    if (!mobileTagsBox) return;
    const recent = getRecent();
    let html = recent.map(t => `<span class="tag">${t}</span>`).join("");
    html += `<span class="tag">Blush</span><span class="tag">Lip gloss</span>`;
    mobileTagsBox.innerHTML = html;
    bindTagRedirect(mobileTagsBox);
  };


  /* ── Search overlay ── */
  openSearch.addEventListener("click", () => {
    searchOverlay.classList.add("active");
    document.body.style.overflow = "hidden";
    renderRecent();
    renderMobileTags();
    setTimeout(() => input.focus(), 100);
  });

  closeSearch.addEventListener("click", () => {
    searchOverlay.classList.remove("active");
    document.body.style.overflow = "";
    input.value = "";
    results.innerHTML = "";
  });

  document.addEventListener("keydown", e => {
    if (e.key === "Escape") {
      searchOverlay.classList.remove("active");
      document.body.style.overflow = "";
    }
  });


  /* ── Live search ── */
  let timer;
  input.addEventListener("input", () => {
    clearTimeout(timer);
    const q = input.value.trim();
    if (q.length < 2) { results.innerHTML = ""; return; }
    timer = setTimeout(async () => {
      saveRecent(q);
      renderRecent();
      try {
        const res  = await fetch(`/includes/search_products.php?query=${encodeURIComponent(q)}`);
        if (!res.ok) throw new Error("not ok");
        const data = await res.json();
        if (!Array.isArray(data)) throw new Error("bad data");
        results.innerHTML = data.length
          ? data.map(p => `
              <a href="/product.php?id=${p.id}" class="search-product">
                <img src="${p.image_url}" alt="${p.name}" onerror="this.style.display='none'" />
                <p>${p.name}</p>
                <strong>DA ${parseFloat(p.price).toFixed(2)}</strong>
              </a>`).join("")
          : `<p style="font-size:.78rem;color:var(--bordeaux-l);font-style:italic;grid-column:1/-1">
               Aucun produit trouvé pour "<strong>${q}</strong>".
             </p>`;
      } catch {
        results.innerHTML = `<p style="font-size:.78rem;color:var(--bordeaux-l);font-style:italic;grid-column:1/-1">
          Les résultats s'afficheront ici lors de la recherche.
        </p>`;
      }
    }, 300);
  });

  input.addEventListener("keydown", e => {
    if (e.key === "Enter") {
      const q = input.value.trim();
      if (q) window.location.href = `/search.php?q=${encodeURIComponent(q)}`;
    }
  });

});
</script>

</body>
</html>