<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <title>SheGlamour – Header</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

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
  --cream:      #F5F1EE;
  --border:     rgba(68, 11, 25, 0.15);
  --text:       #440B19;
  --muted:      #6e1a2e;
  --serif:      'Cormorant Garamond', Georgia, serif;
  --sans:       'Jost', system-ui, sans-serif;
  --ease:       cubic-bezier(0.25, 0.46, 0.45, 0.94);
  --dur:        .38s;

  /* ── Safe area (iPhone home bar) ── */
  --sai-bottom: env(safe-area-inset-bottom, 0px);
  --sai-top:    env(safe-area-inset-top,    0px);

  /* ── Bottom bar height ── */
  --bb-height: 62px;
}

/* ═══════════════════════════════════
   RESET
═══════════════════════════════════ */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

html { -webkit-text-size-adjust: 100%; }

body {
  font-family: var(--sans);
  color: var(--text);
  background: var(--white);
  min-height: 200vh;
  /* espace en bas = bottom bar + safe-area */
  padding-bottom: 0;
}

a { text-decoration: none; color: inherit; }

/* ═══════════════════════════════════
   HEADER
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

.header.scrolled {
  background: var(--cream);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  box-shadow: 0 1px 0 var(--border), 0 10px 40px rgba(68, 11, 25, 0.06);
  height: 64px;
}

.header.scrolled::after { background: var(--border); }

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
  transition: opacity .25s var(--ease), transform .25s var(--ease);
}

.logo:hover img { opacity: .88; transform: scale(1.02) translateZ(0); }

/* ═══════════════════════════════════
   NAVBAR DESKTOP
═══════════════════════════════════ */
.navbar-desktop {
  display: flex;
  align-items: center;
  gap: 36px;
  justify-self: center;
  height: 100%;
}

/* ── Wrapper de chaque item nav (pour positionner le mega-menu) ── */
.nav-item {
  position: static; /* mega-menu s'attache au header, pas à l'item */
  height: 100%;
  display: flex;
  align-items: center;
}

.nav-item > a {
  position: relative;
  padding: 6px 0;
  font-family: var(--sans);
  font-size: .68rem;
  font-weight: 500;
  letter-spacing: .24em;
  text-transform: uppercase;
  color: var(--white);
  transition: color var(--dur), opacity var(--dur);
  white-space: nowrap;
  cursor: pointer;
}

.nav-item > a::after {
  content: '';
  position: absolute;
  left: 0; bottom: 0;
  width: 0; height: 1px;
  background: var(--white);
  transition: width var(--dur) var(--ease);
}

.nav-item:hover > a { opacity: .75; }
.nav-item:hover > a::after { width: 100%; }

.header.scrolled .nav-item > a { color: var(--bordeaux); }
.header.scrolled .nav-item > a::after { background: var(--bordeaux); }
.header.scrolled .nav-item:hover > a { opacity: 1; color: var(--bordeaux-s); }

/* ═══════════════════════════════════
   MEGA MENU
═══════════════════════════════════ */
.mega-menu {
  position: fixed;
  top: 64px; /* colle sous le header scrolled */
  left: 0;
  width: 100%;
  background: var(--cream);
  border-top: 1px solid var(--border);
  box-shadow: 0 20px 60px rgba(68,11,25,.10);
  z-index: 1900;
  /* Animation */
  opacity: 0;
  transform: translateY(-8px);
  pointer-events: none;
  transition:
    opacity .28s var(--ease),
    transform .28s var(--ease);
}

/* Visible quand l'item est survolé */
.nav-item:hover .mega-menu {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}

/* Mega-menu ouvert → header forcé opaque même si pas encore scrollé */
.header.mega-open {
  background: var(--cream) !important;
  box-shadow: 0 1px 0 var(--border), 0 10px 40px rgba(68, 11, 25, 0.06) !important;
  height: 64px !important;
}

.header.mega-open .nav-item > a,
.header.mega-open .icon-btn,
.header.mega-open .icons a { color: var(--bordeaux) !important; }

.header.mega-open .nav-item > a::after { background: var(--bordeaux) !important; }

.header.mega-open .menu-toggle span { background: var(--bordeaux) !important; }

.header.mega-open #openSearch { color: var(--bordeaux) !important; }

/* ── Position mega-menu selon état header ── */
.header:not(.scrolled):not(.mega-open) .mega-menu { top: 72px; }
.header.mega-open .mega-menu { top: 64px; }

.mega-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 32px 5% 36px;
  display: flex;
  gap: 0;
}

/* ── Titre de la catégorie parente ── */
.mega-title {
  width: 160px;
  flex-shrink: 0;
  padding-right: 28px;
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  gap: 10px;
}

.mega-title span {
  font-family: var(--serif);
  font-size: 1.5rem;
  font-weight: 400;
  font-style: italic;
  color: var(--bordeaux);
  line-height: 1.2;
}

.mega-title a.voir-tout {
  font-family: var(--sans);
  font-size: .58rem;
  font-weight: 500;
  letter-spacing: .2em;
  text-transform: uppercase;
  color: var(--bordeaux-l);
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: color var(--dur), gap var(--dur);
}
.mega-title a.voir-tout::after {
  content: '→';
  transition: transform var(--dur);
}
.mega-title a.voir-tout:hover { color: var(--bordeaux); }
.mega-title a.voir-tout:hover::after { transform: translateX(4px); }

/* ── Grille de sous-catégories ── */
.mega-grid {
  flex: 1;
  padding-left: 36px;
  display: flex;
  gap: 20px;
  flex-wrap: nowrap;
  overflow-x: auto;
  scrollbar-width: none;
}
.mega-grid::-webkit-scrollbar { display: none; }

/* ── Carte sous-catégorie ── */
.mega-card {
  flex: 1;
  min-width: 120px;
  max-width: 160px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: var(--bordeaux);
  padding: 12px 10px 14px;
  border: 1px solid transparent;
  transition:
    border-color .25s var(--ease),
    background .25s var(--ease),
    transform .3s var(--ease);
}

.mega-card:hover {
  border-color: var(--border);
  background: var(--white);
  transform: translateY(-3px);
}

.mega-card-img {
  width: 100%;
  aspect-ratio: 1 / 1;
  object-fit: cover;
  display: block;
  border: 1px solid var(--border);
  transition: transform .4s var(--ease);
}

.mega-card:hover .mega-card-img { transform: scale(1.04); }

.mega-card-label {
  font-family: var(--sans);
  font-size: .62rem;
  font-weight: 500;
  letter-spacing: .18em;
  text-transform: uppercase;
  text-align: center;
  color: var(--bordeaux);
  transition: color var(--dur);
}

.mega-card:hover .mega-card-label { color: var(--bordeaux-s); }

/* ═══════════════════════════════════
   ICONS (desktop)
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
  color: var(--white);
  transition: color var(--dur), transform var(--dur), opacity var(--dur);
}

.icon-btn svg,
.icons a svg { width: 20px; height: 20px; display: block; flex-shrink: 0; }

.icon-btn:hover,
.icons a:hover { opacity: .75; transform: translateY(-1px); }

.header.scrolled .icon-btn,
.header.scrolled .icons a { color: var(--bordeaux); }

.header.scrolled .icon-btn:hover,
.header.scrolled .icons a:hover { opacity: 1; color: var(--bordeaux-l); }

#openSearch { color: var(--white) !important; }
.header.scrolled #openSearch { color: var(--bordeaux) !important; }

/* ── Badge panier desktop ── */
.icons a[href="/cart.php"] { position: relative; }

.cart-count-badge {
  position: absolute;
  top: 2px; right: 2px;
  min-width: 17px; height: 17px;
  padding: 0 4px;
  border-radius: 99px;
  background: var(--bordeaux);
  color: var(--white);
  font-size: .58rem;
  font-weight: 600;
  display: none;
  align-items: center;
  justify-content: center;
  pointer-events: none;
}

.cart-count-badge.bump {
  animation: cartBump .4s cubic-bezier(.36,.07,.19,.97);
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
  background: var(--white);
  transform-origin: left center;
  transition: transform var(--dur) var(--ease), opacity var(--dur), width var(--dur), background var(--dur);
}

.menu-toggle span:nth-child(2) { width: 70%; }
.menu-toggle:hover span:nth-child(2) { width: 100%; }
.menu-toggle.active span:nth-child(1) { transform: rotate(42deg); }
.menu-toggle.active span:nth-child(2) { opacity: 0; width: 0; }
.menu-toggle.active span:nth-child(3) { transform: rotate(-42deg); }
.header.scrolled .menu-toggle span { background: var(--bordeaux); }

/* ═══════════════════════════════════
   MENU MOBILE (drawer)
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

.navbar-mobile.active { left: 0; background: var(--cream) !important; }

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

.mobile-logo { display: flex; align-items: center; text-decoration: none; }

.mobile-logo img {
  height: 150px !important;
  width: auto;
  display: block;
  object-fit: contain;
  image-rendering: -webkit-optimize-contrast;
  image-rendering: crisp-edges;
}

/* ── Tabs ── */
.mobile-tabs { display: flex; border-bottom: 1px solid var(--border); }

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
.tab-btn.active { color: var(--bordeaux); border-bottom-color: var(--bordeaux); }

.tab-content { display: none; flex-direction: column; flex: 1; overflow-y: auto; }
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
#brands a:hover { background: rgba(68, 11, 25, .04); padding-left: 28px; color: var(--bordeaux-s); }

.menu-item img { width: 44px; height: 44px; object-fit: cover; border: 1px solid var(--border); }

.mobile-footer {
  padding: 18px 22px;
  font-family: var(--serif);
  font-size: .78rem;
  font-style: italic;
  color: var(--muted);
  border-top: 1px solid var(--border);
  margin-top: auto;
}

/* ── Fermer ── */
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
.mobile-close:hover { color: var(--bordeaux-s); transform: rotate(90deg); }

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
.close-search:hover { color: var(--bordeaux); transform: rotate(90deg); }

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
.menu-overlay.active { display: block; opacity: 1; }

/* ═══════════════════════════════════
   BOTTOM BAR MOBILE
   ── Correction du vide sous la barre ──
   On utilise un wrapper qui :
     1. occupe la hauteur fixe + la safe-area
     2. a un fond plein qui couvre la zone sous le trait
   Le contenu est centré dans les 62px fixes.
═══════════════════════════════════ */
.mobile-bottom-bar {
  display: none; /* affiché via media query */
}

@media (max-width: 1024px) {
  .mobile-bottom-bar {
    display: flex;
    position: fixed;
    bottom: 0; left: 0;
    width: 100%;
    /* Hauteur totale = barre cliquable + safe-area iPhone */
    height: calc(var(--bb-height) + var(--sai-bottom));
    /* Contenu aligné en haut dans les 62 px, pas dans la safe-area */
    align-items: flex-start;
    padding-top: 0;
    padding-left: 8px;
    padding-right: 8px;
    /* Safe-area en bas = fond seulement, pas de contenu dedans */
    padding-bottom: var(--sai-bottom);
    background: var(--cream);
    border-top: 1px solid var(--border);
    box-shadow: 0 -4px 30px rgba(68, 11, 25, .05);
    z-index: 2900;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    /* Transition scroll hide/show */
    transition: transform .35s var(--ease);
    will-change: transform;
  }

  /* Cachée en scrollant vers le bas */
  .mobile-bottom-bar.hidden {
    transform: translateY(calc(100% + var(--sai-bottom)));
  }

  /* Couleur de fond identique sous la safe-area (évite le flash blanc) */
  @supports (padding-bottom: env(safe-area-inset-bottom)) {
    body::after {
      content: '';
      display: block;
      position: fixed;
      bottom: 0; left: 0;
      width: 100%;
      height: var(--sai-bottom);
      background: var(--cream);
      z-index: 2899;
      pointer-events: none;
    }
  }

  body {
    padding-bottom: calc(var(--bb-height) + var(--sai-bottom));
  }
}

/* ── Items de la bottom bar ── */
.bottom-bar-item {
  position: relative;
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  /* hauteur cliquable strictement sur les 62px, safe-area exclue */
  height: var(--bb-height);
  text-decoration: none;
  color: var(--bordeaux-l);
  font-family: var(--sans);
  font-size: .55rem;
  font-weight: 500;
  letter-spacing: .12em;
  text-transform: uppercase;
  transition: color var(--dur);
  -webkit-tap-highlight-color: transparent;
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

/* ── Badge bottom bar ── */
.bottom-bar-item .badge {
  display: none;
  position: absolute;
  top: 8px; left: 50%;
  margin-left: 4px;
  min-width: 16px; height: 16px;
  padding: 0 4px;
  align-items: center;
  justify-content: center;
  border-radius: 99px;
  background: var(--bordeaux);
  color: var(--white);
  font-size: .6rem;
  font-weight: 600;
  pointer-events: none;
}

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
.search-input-wrapper svg { width: 20px; height: 20px; display: block; flex-shrink: 0; color: rgba(68,11,25,.35); transition: color var(--dur); }
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
.search-input-wrapper input::placeholder { color: rgba(68,11,25,.24); font-style: italic; }

.search-layout {
  display: grid;
  grid-template-columns: 250px 1fr;
  gap: 52px;
  align-items: start;
}

.search-sidebar { padding-right: 28px; border-right: 1px solid var(--border); }
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

.recent-tags, .hot-tags, .search-mobile-tags { display: flex; flex-wrap: wrap; gap: 10px; }

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
  transition: background var(--dur), border-color var(--dur), color var(--dur), transform var(--dur), box-shadow var(--dur);
}
.tag:hover {
  background: var(--bordeaux);
  border-color: var(--bordeaux);
  color: var(--white);
  transform: translateY(-2px);
  box-shadow: 0 10px 22px rgba(68,11,25,.16);
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

.search-results-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 22px; }

/* ── Search product card ── */
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
  transition: border-color .3s var(--ease), transform .35s var(--ease), box-shadow .35s var(--ease);
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
.search-product:hover { border-color: var(--bordeaux); transform: translateY(-4px); box-shadow: 0 14px 36px rgba(68,11,25,.10); }
.search-product:hover::after { width: 50%; }
.search-product img { width: 100%; aspect-ratio: 1/1; object-fit: contain; background: var(--white); border: 1px solid var(--border); display: block; transition: transform .45s var(--ease); }
.search-product:hover img { transform: scale(1.06); }
.search-product p { font-family: var(--serif); font-size: 12px; font-weight: 400; font-style: italic; letter-spacing: .04em; color: var(--bordeaux); line-height: 1.4; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.search-product strong { font-family: var(--sans); font-size: 11px; font-weight: 500; color: var(--bordeaux-l); letter-spacing: .08em; }

/* ═══════════════════════════════════
   FLY TO CART
═══════════════════════════════════ */
.fly-item {
  position: fixed;
  width: 60px; height: 60px;
  border-radius: 4px;
  border: 2px solid var(--bordeaux);
  object-fit: cover;
  pointer-events: none;
  z-index: 9999;
  box-shadow: 0 8px 24px rgba(68,11,25,.18);
  will-change: transform, opacity;
}

@keyframes cartBump {
  0%   { transform: scale(1); }
  40%  { transform: scale(1.6); }
  70%  { transform: scale(.85); }
  100% { transform: scale(1); }
}

/* ═══════════════════════════════════
   BACK TO TOP
═══════════════════════════════════ */
.back-to-top {
  position: fixed;
  /* Au-dessus de la bottom bar sur mobile, sinon coin bas-droite */
  right: 20px;
  bottom: calc(var(--bb-height) + var(--sai-bottom) + 16px);
  width: 44px; height: 44px;
  border-radius: 50%;
  background: var(--bordeaux);
  color: var(--white);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2800;
  /* Caché par défaut */
  opacity: 0;
  pointer-events: none;
  transform: translateY(12px);
  transition:
    opacity .3s var(--ease),
    transform .3s var(--ease),
    background .2s var(--ease),
    box-shadow .2s var(--ease);
  box-shadow: 0 4px 18px rgba(68,11,25,.25);
}

.back-to-top.visible {
  opacity: 1;
  pointer-events: auto;
  transform: translateY(0);
}

.back-to-top:hover {
  background: var(--bordeaux-s);
  box-shadow: 0 8px 28px rgba(68,11,25,.35);
  transform: translateY(-2px);
}

.back-to-top svg { width: 20px; height: 20px; display: block; }

/* Sur desktop, placer la flèche un peu plus bas (pas de bottom bar) */
@media (min-width: 1025px) {
  .back-to-top {
    bottom: 28px;
  }
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

  .logo {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    justify-self: unset;
  }

  .logo img { height: 36px; max-width: 200px; }

  .menu-toggle { display: flex; }

  .icons {
    position: absolute;
    right: 16px; top: 50%;
    transform: translateY(-50%);
  }

  .icons .hide-mobile { display: none; }

  /* Search overlay plein écran sur mobile */
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

@media (max-width: 768px) {
  .logo img { height: 70px !important; }
}

/* ═══════════════════════════════════
   MENU MOBILE — ACCORDÉON SOUS-CATÉGORIES
═══════════════════════════════════ */

/* Parent avec chevron */
.mobile-cat-parent {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 13px 22px;
  border-bottom: 1px solid var(--border);
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
  transition: background var(--dur);
}
.mobile-cat-parent:hover { background: rgba(68,11,25,.04); }

.mobile-cat-parent-left {
  display: flex;
  align-items: center;
  gap: 14px;
  text-decoration: none;
  color: var(--bordeaux);
  font-size: .86rem;
  flex: 1;
}
.mobile-cat-parent-left img {
  width: 44px; height: 44px;
  object-fit: cover;
  border: 1px solid var(--border);
  flex-shrink: 0;
}

/* Chevron */
.mobile-cat-chevron {
  width: 28px; height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: var(--bordeaux-l);
  transition: transform var(--dur) var(--ease), color var(--dur);
}
.mobile-cat-chevron svg { width: 14px; height: 14px; display: block; }

.mobile-cat-parent.open .mobile-cat-chevron { transform: rotate(180deg); color: var(--bordeaux); }

/* Sous-catégories : scroll horizontal */
.mobile-sub-cats {
  display: none;
  overflow-x: auto;
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
  padding: 14px 16px 16px;
  gap: 12px;
  border-bottom: 1px solid var(--border);
  background: rgba(68,11,25,.025);
}
.mobile-sub-cats::-webkit-scrollbar { display: none; }
.mobile-sub-cats.open { display: flex; }

/* Carte sous-catégorie mobile */
.mobile-sub-card {
  flex-shrink: 0;
  width: 80px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  color: var(--bordeaux);
}
.mobile-sub-card img {
  width: 68px; height: 68px;
  object-fit: cover;
  border: 1px solid var(--border);
  display: block;
  transition: transform .3s var(--ease);
}
.mobile-sub-card:active img { transform: scale(.96); }
.mobile-sub-card span {
  font-family: var(--sans);
  font-size: .52rem;
  font-weight: 500;
  letter-spacing: .14em;
  text-transform: uppercase;
  text-align: center;
  color: var(--bordeaux-l);
  line-height: 1.3;
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

    <!-- YEUX -->
    <div class="nav-item">
      <a href="/categorie.php?categorie=Yeux">Yeux</a>
      <div class="mega-menu">
        <div class="mega-inner">
          <div class="mega-title">
            <span>Yeux</span>
            <a href="/categorie.php?categorie=Yeux" class="voir-tout">Voir tout</a>
          </div>
          <div class="mega-grid">
            <a href="/categorie.php?categorie=Mascara" class="mega-card">
              <img class="mega-card-img" src="/images/mascara.jpg" alt="Mascara" />
              <span class="mega-card-label">Mascara</span>
            </a>
            <a href="/categorie.php?categorie=Eyeliner" class="mega-card">
              <img class="mega-card-img" src="/images/eyeliner.jpg" alt="Eyeliner" />
              <span class="mega-card-label">Eyeliner</span>
            </a>
            <a href="/categorie.php?categorie=Fard+à+paupières" class="mega-card">
              <img class="mega-card-img" src="/images/eyeshadow.jpg" alt="Fard à paupières" />
              <span class="mega-card-label">Fards à paupières</span>
            </a>
            <a href="/categorie.php?categorie=Sourcils" class="mega-card">
              <img class="mega-card-img" src="/images/sourcils.jpg" alt="Sourcils" />
              <span class="mega-card-label">Sourcils</span>
            </a>
            <a href="/categorie.php?categorie=Faux+cils" class="mega-card">
              <img class="mega-card-img" src="/images/fauxcils.jpg" alt="Faux cils" />
              <span class="mega-card-label">Faux cils</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- LÈVRES -->
    <div class="nav-item">
      <a href="/categorie.php?categorie=Lèvres">Lèvres</a>
      <div class="mega-menu">
        <div class="mega-inner">
          <div class="mega-title">
            <span>Lèvres</span>
            <a href="/categorie.php?categorie=Lèvres" class="voir-tout">Voir tout</a>
          </div>
          <div class="mega-grid">
            <a href="/categorie.php?categorie=Rouge+à+lèvres" class="mega-card">
              <img class="mega-card-img" src="/images/lips.jpg" alt="Rouge à lèvres" />
              <span class="mega-card-label">Rouge à lèvres</span>
            </a>
            <a href="/categorie.php?categorie=Lip+gloss" class="mega-card">
              <img class="mega-card-img" src="/images/lipgloss.jpg" alt="Lip gloss" />
              <span class="mega-card-label">Lip gloss</span>
            </a>
            <a href="/categorie.php?categorie=Crayon+lèvres" class="mega-card">
              <img class="mega-card-img" src="/images/lipliner.jpg" alt="Crayon lèvres" />
              <span class="mega-card-label">Crayon lèvres</span>
            </a>
            <a href="/categorie.php?categorie=Baume" class="mega-card">
              <img class="mega-card-img" src="/images/baume.jpg" alt="Baume" />
              <span class="mega-card-label">Baume</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- TEINT -->
    <div class="nav-item">
      <a href="/categorie.php?categorie=Teint">Teint</a>
      <div class="mega-menu">
        <div class="mega-inner">
          <div class="mega-title">
            <span>Teint</span>
            <a href="/categorie.php?categorie=Teint" class="voir-tout">Voir tout</a>
          </div>
          <div class="mega-grid">
            <a href="/categorie.php?categorie=Fond+de+teint" class="mega-card">
              <img class="mega-card-img" src="/images/teint.jpg" alt="Fond de teint" />
              <span class="mega-card-label">Fond de teint</span>
            </a>
            <a href="/categorie.php?categorie=Blush" class="mega-card">
              <img class="mega-card-img" src="/images/blush.jpg" alt="Blush" />
              <span class="mega-card-label">Blush</span>
            </a>
            <a href="/categorie.php?categorie=Highlighter" class="mega-card">
              <img class="mega-card-img" src="/images/highlighter.jpg" alt="Highlighter" />
              <span class="mega-card-label">Highlighter</span>
            </a>
            <a href="/categorie.php?categorie=Poudre" class="mega-card">
              <img class="mega-card-img" src="/images/poudre.jpg" alt="Poudre" />
              <span class="mega-card-label">Poudre</span>
            </a>
            <a href="/categorie.php?categorie=Contour" class="mega-card">
              <img class="mega-card-img" src="/images/contour.jpg" alt="Contour" />
              <span class="mega-card-label">Contour</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- ACCESSOIRES -->
    <div class="nav-item">
      <a href="/categorie.php?categorie=Accessoires">Accessoires</a>
      <div class="mega-menu">
        <div class="mega-inner">
          <div class="mega-title">
            <span>Accessoires</span>
            <a href="/categorie.php?categorie=Accessoires" class="voir-tout">Voir tout</a>
          </div>
          <div class="mega-grid">
            <a href="/categorie.php?categorie=Pinceaux" class="mega-card">
              <img class="mega-card-img" src="/images/acc.jpg" alt="Pinceaux" />
              <span class="mega-card-label">Pinceaux</span>
            </a>
            <a href="/categorie.php?categorie=Éponges" class="mega-card">
              <img class="mega-card-img" src="/images/eponge.jpg" alt="Éponges" />
              <span class="mega-card-label">Éponges</span>
            </a>
            <a href="/categorie.php?categorie=Trousses" class="mega-card">
              <img class="mega-card-img" src="/images/trousse.jpg" alt="Trousses" />
              <span class="mega-card-label">Trousses</span>
            </a>
          </div>
        </div>
      </div>
    </div>

  </nav>

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
    <a href="/cart.php" class="hide-mobile" id="cartIconDesktop" aria-label="Panier">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
        <line x1="3" y1="6" x2="21" y2="6"/>
        <path d="M16 10a4 4 0 0 1-8 0"/>
      </svg>
      <span class="cart-count-badge" id="cartCountBadge">0</span>
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
  <a href="/cart.php" class="bottom-bar-item" id="cartIconMobile">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
      <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
      <line x1="3" y1="6" x2="21" y2="6"/>
      <path d="M16 10a4 4 0 0 1-8 0"/>
    </svg>
    <span class="badge" id="cartCountBadgeMobile">0</span>
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
     BACK TO TOP
============================================= -->
<button class="back-to-top" id="backToTop" aria-label="Retour en haut">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <polyline points="18 15 12 9 6 15"/>
  </svg>
</button>


<!-- =============================================
     MENU MOBILE (drawer)
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

    <!-- YEUX -->
    <div class="mobile-cat-parent" data-sub="sub-yeux">
      <a href="/categorie.php?categorie=Yeux" class="mobile-cat-parent-left" onclick="event.stopPropagation()">
        <img src="/images/eyes.jpg" alt="Yeux" /><span>Yeux</span>
      </a>
      <span class="mobile-cat-chevron">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
      </span>
    </div>
    <div class="mobile-sub-cats" id="sub-yeux">
      <a href="/categorie.php?categorie=Mascara"        class="mobile-sub-card"><img src="/images/mascara.jpg"   alt="Mascara" /><span>Mascara</span></a>
      <a href="/categorie.php?categorie=Eyeliner"       class="mobile-sub-card"><img src="/images/eyeliner.jpg"  alt="Eyeliner" /><span>Eyeliner</span></a>
      <a href="/categorie.php?categorie=Fard+à+paupières" class="mobile-sub-card"><img src="/images/eyeshadow.jpg" alt="Fards" /><span>Fards</span></a>
      <a href="/categorie.php?categorie=Sourcils"       class="mobile-sub-card"><img src="/images/sourcils.jpg"  alt="Sourcils" /><span>Sourcils</span></a>
      <a href="/categorie.php?categorie=Faux+cils"      class="mobile-sub-card"><img src="/images/fauxcils.jpg"  alt="Faux cils" /><span>Faux cils</span></a>
    </div>

    <!-- LÈVRES -->
    <div class="mobile-cat-parent" data-sub="sub-levres">
      <a href="/categorie.php?categorie=Lèvres" class="mobile-cat-parent-left" onclick="event.stopPropagation()">
        <img src="/images/lips.jpg" alt="Lèvres" /><span>Lèvres</span>
      </a>
      <span class="mobile-cat-chevron">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
      </span>
    </div>
    <div class="mobile-sub-cats" id="sub-levres">
      <a href="/categorie.php?categorie=Rouge+à+lèvres" class="mobile-sub-card"><img src="/images/lips.jpg"     alt="Rouge à lèvres" /><span>Rouge à lèvres</span></a>
      <a href="/categorie.php?categorie=Lip+gloss"      class="mobile-sub-card"><img src="/images/lipgloss.jpg" alt="Lip gloss" /><span>Lip gloss</span></a>
      <a href="/categorie.php?categorie=Crayon+lèvres"  class="mobile-sub-card"><img src="/images/lipliner.jpg" alt="Crayon" /><span>Crayon lèvres</span></a>
      <a href="/categorie.php?categorie=Baume"          class="mobile-sub-card"><img src="/images/baume.jpg"    alt="Baume" /><span>Baume</span></a>
    </div>

    <!-- TEINT -->
    <div class="mobile-cat-parent" data-sub="sub-teint">
      <a href="/categorie.php?categorie=Teint" class="mobile-cat-parent-left" onclick="event.stopPropagation()">
        <img src="/images/teint.jpg" alt="Teint" /><span>Teint</span>
      </a>
      <span class="mobile-cat-chevron">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
      </span>
    </div>
    <div class="mobile-sub-cats" id="sub-teint">
      <a href="/categorie.php?categorie=Fond+de+teint" class="mobile-sub-card"><img src="/images/teint.jpg"       alt="Fond de teint" /><span>Fond de teint</span></a>
      <a href="/categorie.php?categorie=Blush"          class="mobile-sub-card"><img src="/images/blush.jpg"       alt="Blush" /><span>Blush</span></a>
      <a href="/categorie.php?categorie=Highlighter"    class="mobile-sub-card"><img src="/images/highlighter.jpg" alt="Highlighter" /><span>Highlighter</span></a>
      <a href="/categorie.php?categorie=Poudre"         class="mobile-sub-card"><img src="/images/poudre.jpg"      alt="Poudre" /><span>Poudre</span></a>
      <a href="/categorie.php?categorie=Contour"        class="mobile-sub-card"><img src="/images/contour.jpg"     alt="Contour" /><span>Contour</span></a>
    </div>

    <!-- ACCESSOIRES -->
    <div class="mobile-cat-parent" data-sub="sub-acc">
      <a href="/categorie.php?categorie=Accessoires" class="mobile-cat-parent-left" onclick="event.stopPropagation()">
        <img src="/images/acc.jpg" alt="Accessoires" /><span>Accessoires</span>
      </a>
      <span class="mobile-cat-chevron">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
      </span>
    </div>
    <div class="mobile-sub-cats" id="sub-acc">
      <a href="/categorie.php?categorie=Pinceaux" class="mobile-sub-card"><img src="/images/acc.jpg"     alt="Pinceaux" /><span>Pinceaux</span></a>
      <a href="/categorie.php?categorie=Éponges"  class="mobile-sub-card"><img src="/images/eponge.jpg"  alt="Éponges" /><span>Éponges</span></a>
      <a href="/categorie.php?categorie=Trousses" class="mobile-sub-card"><img src="/images/trousse.jpg" alt="Trousses" /><span>Trousses</span></a>
    </div>

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
  const backToTop     = document.getElementById("backToTop");

  /* ─────────────────────────────────
     Scroll : header + swap logo + back-to-top
  ───────────────────────────────── */
  const onScroll = () => {
    const scrolled = window.scrollY > 30;
    header.classList.toggle("scrolled", scrolled);
    if (headerLogo) {
      headerLogo.src = (scrolled || header.classList.contains("mega-open"))
        ? "/images/logofib.png"
        : "/images/logowhite.png";
    }
    backToTop.classList.toggle("visible", window.scrollY > 300);
  };
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  /* Back to top — smooth scroll */
  backToTop.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  /* ─────────────────────────────────
     Bottom bar : masquer en descendant, afficher en remontant
     Chrome Android redimensionne le viewport quand sa barre UI
     disparaît → faux scroll "vers le haut" de quelques px.
     On ignore les delta < THRESHOLD pour filtrer ce bruit.
  ───────────────────────────────── */
  const bottomBar = document.querySelector(".mobile-bottom-bar");
  const THRESHOLD = 8; // px minimum pour considérer un vrai scroll
  let lastScrollY = window.scrollY;
  let ticking = false;

  window.addEventListener("scroll", () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
      const currentY = window.scrollY;
      const delta    = currentY - lastScrollY;

      if (Math.abs(delta) >= THRESHOLD) {
        if (delta > 0 && currentY > 60) {
          // Descente réelle → on cache
          bottomBar.classList.add("hidden");
        } else if (delta < 0) {
          // Remontée réelle → on affiche
          bottomBar.classList.remove("hidden");
        }
        lastScrollY = currentY;
      }

      ticking = false;
    });
  }, { passive: true });

  /* ─────────────────────────────────
     Mega-menu : force le header opaque à l'ouverture
     (le hover CSS ne couvre pas le panel hors header)
  ───────────────────────────────── */
  document.querySelectorAll(".nav-item").forEach(item => {
    item.addEventListener("mouseenter", () => {
      header.classList.add("mega-open");
      if (headerLogo) headerLogo.src = "/images/logofib.png";
    });
    item.addEventListener("mouseleave", () => {
      header.classList.remove("mega-open");
      onScroll();
    });
  });

  /* ─────────────────────────────────
     Accordéon sous-catégories mobile
  ───────────────────────────────── */
  document.querySelectorAll(".mobile-cat-parent").forEach(parent => {
    parent.addEventListener("click", () => {
      const subId  = parent.dataset.sub;
      const sub    = document.getElementById(subId);
      const isOpen = sub.classList.contains("open");

      // Ferme tous les autres
      document.querySelectorAll(".mobile-sub-cats.open").forEach(el => el.classList.remove("open"));
      document.querySelectorAll(".mobile-cat-parent.open").forEach(el => el.classList.remove("open"));

      // Toggle celui-ci
      if (!isOpen) {
        sub.classList.add("open");
        parent.classList.add("open");
      }
    });
  });

  /* ─────────────────────────────────
     Menu mobile
  ───────────────────────────────── */
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

  /* ─────────────────────────────────
     Tabs menu mobile
  ───────────────────────────────── */
  document.querySelectorAll(".tab-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
      document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));
      btn.classList.add("active");
      document.getElementById(btn.dataset.tab).classList.add("active");
    });
  });

  /* ─────────────────────────────────
     Recherches récentes
  ───────────────────────────────── */
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

  /* ─────────────────────────────────
     Search overlay
  ───────────────────────────────── */
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

  /* ─────────────────────────────────
     Live search
  ───────────────────────────────── */
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

<script>
  /* Badge panier — appelle bumpCartBadge() si définie dans shop.js */
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
      typeof bumpCartBadge === "function" && bumpCartBadge();
    });
  } else {
    typeof bumpCartBadge === "function" && bumpCartBadge();
  }
</script>

</body>
</html>