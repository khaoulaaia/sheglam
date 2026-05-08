<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SheGlamour – Header</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
/* ═══════════════════════════════════
   VARIABLES
═══════════════════════════════════ */

:root {

  --beige:    #d9d0b4;
  --beige-l:  #d9d0b4;
  --beige-d:  #d9d0b4;
  --gold:     #d9d0b4;
  --marron:   #2E1A0A;
  --marron-d: #2E1A0A;
  --marron-n: #2E1A0A;


  --border:   #D6C4A8;
  --text:     #2E1A0A;
  --muted:    #c6b9a4;

  --c1:   #FAF6F0;
  --c2:   #F0E6D3;
  --c3:   #d9d0b4;
  --c6:   #d9d0b4;
  --nuit: #2E1A0A;

  --serif:    'Cormorant Garamond', Georgia, serif;
  --sans:     'DM Sans', 'Poppins', system-ui, sans-serif;
  --ease:     cubic-bezier(0.25, 0.46, 0.45, 0.94);
  --dur: .38s;
}

/* ═══════════════════════════════════
   RESET
═══════════════════════════════════ */

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: var(--sans);
  color: var(--text);
  background: var(--c1);
  min-height: 200vh;
  padding-bottom: 0;
}

a {
  text-decoration: none;
  color: inherit;
}

/* ═══════════════════════════════════
   HEADER
═══════════════════════════════════ */

.header {
  position: fixed;
  top: 0;
  left: 0;

  width: 100%;
  height: 68px;

  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;

  padding: 0 5%;

  z-index: 2000;

  background: transparent;

  transition:
    background var(--dur) var(--ease),
    box-shadow var(--dur) var(--ease),
    height var(--dur) var(--ease);
}

/* Header scrollé */

.header.scrolled {
  background: rgba(250, 246, 240, 0.92);

  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);

  box-shadow:
    0 1px 0 rgba(139, 106, 69, 0.10),
    0 10px 40px rgba(46, 26, 10, 0.06);

  height: 62px;
}

.header::after {
  content: '';

  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;

  height: 1px;

  background: transparent;

  transition: background var(--dur) var(--ease);
}

.header.scrolled::after {
  background: rgba(139, 106, 69, 0.14);
}

/* ═══════════════════════════════════
   LOGO
═══════════════════════════════════ */

.logo {
  font-family: var(--serif);
  font-size: 1.5rem;
  font-weight: 600;

  letter-spacing: .22em;
  text-transform: uppercase;

  color: #4B2E2B;

  user-select: none;

  justify-self: start;
}

.logo span {
  color:#4B2E2B;
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

  color: var(--text);

  transition: color var(--dur);
}

.navbar-desktop a::after {
  content: '';

  position: absolute;
  left: 0;
  bottom: 0;

  width: 0;
  height: 1px;

  background: var(--gold);

  transition: width var(--dur) var(--ease);
}

.navbar-desktop a:hover {
  color: var(--marron);
}

.navbar-desktop a:hover::after {
  width: 100%;
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
  width: 36px;
  height: 36px;

  display: flex;
  align-items: center;
  justify-content: center;

  flex-shrink: 0;

  border: none;
  background: none;

  cursor: pointer;

  color: var(--text);

  transition:
    color var(--dur),
    transform var(--dur);
}

.icon-btn svg,
.icons a svg,
.search-input-wrapper svg {
  width: 20px;
  height: 20px;

  display: block;

  flex-shrink: 0;
}

.icon-btn:hover,
.icons a:hover {
  color: var(--gold);
  transform: translateY(-1px);
}

.icons a:hover svg {
  stroke: var(--gold);
}

.header-divider {
  display: none;
}

/* ═══════════════════════════════════
   HAMBURGER
═══════════════════════════════════ */

.menu-toggle {
  display: none;

  flex-direction: column;
  justify-content: space-between;

  width: 28px;
  height: 20px;

  position: absolute;
  left: 18px;
  top: 50%;

  transform: translateY(-50%);

  border: none;
  background: none;

  cursor: pointer;
}

.menu-toggle span {
  width: 100%;
  height: 1px;

  display: block;

  background: var(--nuit);

  transform-origin: left center;

  transition:
    transform var(--dur) var(--ease),
    opacity var(--dur),
    width var(--dur);
}

.menu-toggle span:nth-child(2) {
  width: 70%;
}

.menu-toggle:hover span:nth-child(2) {
  width: 100%;
}

.menu-toggle.active span:nth-child(1) {
  transform: rotate(42deg);
}

.menu-toggle.active span:nth-child(2) {
  opacity: 0;
  width: 0;
}

.menu-toggle.active span:nth-child(3) {
  transform: rotate(-42deg);
}

/* ═══════════════════════════════════
   MENU MOBILE
═══════════════════════════════════ */

.navbar-mobile {
  position: fixed;
  top: 0;
  left: -100%;

  width: 78%;
  max-width: 300px;
  height: 100dvh;

  display: flex;
  flex-direction: column;

  overflow: hidden;

  background: var(--c1);

  transition: left .4s var(--ease);

  z-index: 2600;
}

.navbar-mobile.active {
  left: 0;
}

.navbar-mobile::before {
  content: '';

  width: 100%;
  height: 3px;

  display: block;

  flex-shrink: 0;

  background: linear-gradient(
    90deg,
    var(--gold),
    var(--marron)
  );
}

.mobile-menu-header {
  display: flex;
  align-items: center;
  justify-content: space-between;

  padding: 20px 22px 18px;

  border-bottom: 1px solid rgba(139, 106, 69, .12);
}

.mobile-logo {
  font-family: var(--serif);
  font-size: 1.15rem;
  font-weight: 600;

  letter-spacing: .16em;
  text-transform: uppercase;

  color: var(--nuit);
}

/* ═══════════════════════════════════
   TABS
═══════════════════════════════════ */

.mobile-tabs {
  display: flex;

  border-bottom: 1px solid rgba(139, 106, 69, .12);
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

  color: rgba(46, 26, 10, .35);

  border-bottom: 2px solid transparent;

  transition:
    color var(--dur),
    border-color var(--dur);
}

.tab-btn.active {
  color: var(--marron-d);
  border-bottom-color: var(--gold);
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

.tab-content.active {
  display: flex;
}

.menu-item,
#brands a {
  display: flex;
  align-items: center;
  gap: 14px;

  padding: 13px 22px;

  border-bottom: 1px solid rgba(139, 106, 69, .07);

  font-size: .86rem;

  color: var(--text);

  transition:
    background var(--dur),
    padding-left var(--dur),
    color var(--dur);
}

.menu-item:hover,
#brands a:hover {
  background: var(--c2);

  padding-left: 28px;

  color: var(--marron-d);
}

.menu-item img {
  width: 44px;
  height: 44px;

  object-fit: cover;

  border-radius: 8px;

  border: 1px solid rgba(139, 106, 69, .12);
}

/* ═══════════════════════════════════
   SEARCH OVERLAY
═══════════════════════════════════ */

.search-overlay {
  position: fixed;
  inset: 0;

  background: var(--c1);

  z-index: 4000;

  transform: translateY(-100%);

  transition: transform .55s var(--ease);

  overflow-y: auto;

  height: 72%;
}

.search-overlay.active {
  transform: translateY(0);
}

.search-input-wrapper {
  max-width: 580px;

  margin: 0 auto;

  display: flex;
  align-items: center;
  gap: 12px;

  padding-bottom: 12px;

  border-bottom: 1px solid rgba(46, 26, 10, .12);

  transition: border-color var(--dur);
}

.search-input-wrapper:focus-within {
  border-color: var(--gold);
}

.search-input-wrapper input {
  flex: 1;

  border: none;
  outline: none;
  background: transparent;

  text-align: center;

  font-family: var(--serif);
  font-size: 1.8rem;
  font-weight: 300;

  letter-spacing: .06em;

  color: var(--nuit);
}

.search-input-wrapper input::placeholder {
  color: rgba(46, 26, 10, .24);
  font-style: italic;
}

/* ═══════════════════════════════════
   BOTTOM BAR MOBILE
═══════════════════════════════════ */

.mobile-bottom-bar {
  display: none;
}

/* ═══════════════════════════════════
   RESPONSIVE
═══════════════════════════════════ */

@media (max-width: 1024px) {

  .navbar-desktop {
    display: none;
  }

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

    font-size: 1.2rem;
  }

  .menu-toggle {
    display: flex;
  }

  .icons {
    position: absolute;
    right: 16px;
    top: 50%;

    transform: translateY(-50%);
  }

  .icons .hide-mobile {
    display: none;
  }

  .mobile-bottom-bar {
    position: fixed;
    bottom: 0;
    left: 0;

    width: 100%;
    height: 62px;

    display: flex;
    align-items: center;
    justify-content: space-around;

    padding: 0 8px;

    background: rgba(250, 246, 240, 0.94);

    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);

    border-top: 1px solid rgba(139, 106, 69, .12);

    box-shadow:
      0 -4px 30px rgba(46, 26, 10, .05);

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

    color: var(--muted);

    font-family: var(--sans);
    font-size: .55rem;
    font-weight: 500;

    letter-spacing: .12em;
    text-transform: uppercase;

    transition: color var(--dur);
  }

  .bottom-bar-item svg {
    width: 20px;
    height: 20px;

    display: block;
  }

  .bottom-bar-item:hover,
  .bottom-bar-item:active {
    color: var(--gold);
  }

  .bottom-bar-item + .bottom-bar-item::before {
    content: '';

    position: absolute;
    left: 0;
    top: 20%;
    bottom: 20%;

    width: 1px;

    background: rgba(139, 106, 69, .12);
  }

  .bottom-bar-item .badge {
    position: absolute;
    top: 4px;
    left: 50%;

    margin-left: 4px;

    min-width: 16px;
    height: 16px;

    padding: 0 4px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 99px;

    background: var(--gold);

    color: var(--nuit);

    font-size: .6rem;
    font-weight: 600;
  }

  body {
    padding-bottom: 62px;
  }
}
/* ═══════════════════════════════════
   BOUTON FERMER
═══════════════════════════════════ */

.mobile-close,
.close-search {
  width: 34px;
  height: 34px;
  background: transparent;
  display: flex;
  align-items: center;
  justify-content: center;

  border: none;

  color: var(--marron);

  font-size: 18px;
  font-weight: 300;

  cursor: pointer;

}


/* ═══════════════════════════════════
   TITRES RECHERCHE
═══════════════════════════════════ */

.search-sidebar h4 {
  font-family: var(--sans);
  font-size: .58rem;
  font-weight: 500;

  letter-spacing: .26em;
  text-transform: uppercase;

  color: rgba(46, 26, 10, .38);

  margin: 26px 0 12px;
}

.search-sidebar h4:first-child {
  margin-top: 0;
}

/* ═══════════════════════════════════
   RECHERCHES RÉCENTES / TAGS
═══════════════════════════════════ */

.recent-tags,
.hot-tags,
.search-mobile-tags {
  display:none;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.tag {
  padding: 7px 14px;

  border-radius: 999px;

  background: rgba(240, 230, 211, .45);

  border: 1px solid rgba(139, 106, 69, .16);

  color: var(--marron);

  font-family: var(--sans);
  font-size: .72rem;
  font-weight: 500;

  letter-spacing: .04em;

  cursor: pointer;

  transition:
    background var(--dur),
    border-color var(--dur),
    color var(--dur),
    transform var(--dur),
    box-shadow var(--dur);
}

.tag:hover {
  background: var(--gold);

  border-color: var(--gold);

  color: var(--nuit);

  transform: translateY(-2px);

  box-shadow:
    0 10px 22px rgba(201, 168, 76, .14);
}

/* ═══════════════════════════════════
   MESSAGE AUCUNE RECHERCHE
═══════════════════════════════════ */

.recent-tags p {
  font-size: .74rem;
  font-style: italic;

  color: rgba(46, 26, 10, .32);
}
/* ═══════════════════════════════════
   SEARCH OVERLAY
═══════════════════════════════════ */

.search-overlay {
  position: fixed;
  inset: 0;
  z-index: 4000;

  background: rgba(250, 246, 240, 0.96);

  backdrop-filter: blur(22px);
  -webkit-backdrop-filter: blur(22px);

  transform: translateY(-100%);
  transition: transform .55s var(--ease);

  overflow-y: auto;

  height: 72%;
}

.search-overlay.active {
  transform: translateY(0);
}

/* ═══════════════════════════════════
   CONTAINER
═══════════════════════════════════ */

.search-container {
  padding: 82px 7% 50px;

  display: flex;
  flex-direction: column;
  gap: 42px;
}

/* ═══════════════════════════════════
   BOUTON FERMER
═══════════════════════════════════ */

.close-search {
  position: absolute;
  top: 24px;
  right: 28px;

  border: none;
  background: transparent;

  color: rgba(46, 26, 10, .55);

  font-size: 26px;
  font-weight: 300;
  line-height: 1;

  cursor: pointer;

  transition:
    color var(--dur),
    transform var(--dur);
}

.close-search:hover {
  color: var(--gold);
  transform: rotate(90deg);
}

/* ═══════════════════════════════════
   BARRE RECHERCHE
═══════════════════════════════════ */

.search-input-wrapper {
  width: 100%;
  max-width: 720px;

  margin: 0 auto;

  display: flex;
  align-items: center;
  justify-content: center;

  gap: 14px;

  padding: 0 0 16px;

  border-bottom: 1px solid rgba(139, 106, 69, .16);

  transition:
    border-color var(--dur),
    transform var(--dur);
}

.search-input-wrapper:focus-within {
  border-color: var(--gold);
}

.search-input-wrapper svg {
  width: 20px;
  height: 20px;

  flex-shrink: 0;

  color: rgba(46, 26, 10, .35);

  transition: color var(--dur);
}

.search-input-wrapper:focus-within svg {
  color: var(--gold);
}

.search-input-wrapper input {
  flex: 1;

  border: none;
  outline: none;
  background: transparent;

  font-family: var(--serif);
  font-size: 1.9rem;
  font-weight: 300;

  letter-spacing: .04em;

  color: var(--nuit);

  padding-top: 2px;
}

.search-input-wrapper input::placeholder {
  color: rgba(46, 26, 10, .24);
  font-style: italic;
}

/* ═══════════════════════════════════
   LAYOUT
═══════════════════════════════════ */

.search-layout {
  display: grid;
  grid-template-columns: 250px 1fr;
  gap: 52px;

  align-items: start;
}

/* ═══════════════════════════════════
   SIDEBAR
═══════════════════════════════════ */

.search-sidebar {
  padding-right: 28px;

  border-right: 1px solid rgba(139, 106, 69, .10);
}

.search-sidebar h4 {
  margin: 0 0 14px;

  font-family: var(--sans);
  font-size: .58rem;
  font-weight: 600;

  letter-spacing: .24em;
  text-transform: uppercase;

  color: rgba(46, 26, 10, .38);
}

.search-sidebar h4:not(:first-child) {
  margin-top: 30px;
}

/* ═══════════════════════════════════
   TAGS
═══════════════════════════════════ */

.recent-tags,
.hot-tags,
.search-mobile-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.tag {
  padding: 8px 15px;

  border: 1px solid rgba(139, 106, 69, .14);
  border-radius: 999px;

  background: rgba(240, 230, 211, .55);

  color: var(--marron);

  font-family: var(--sans);
  font-size: .72rem;
  font-weight: 500;

  letter-spacing: .03em;

  cursor: pointer;

  transition:
    background var(--dur),
    border-color var(--dur),
    color var(--dur),
    transform var(--dur),
    box-shadow var(--dur);
}

.tag:hover {
  background: var(--gold);
  border-color: var(--gold);

  color: var(--nuit);

  transform: translateY(-2px);

  box-shadow:
    0 10px 22px rgba(201, 168, 76, .16);
}

/* ═══════════════════════════════════
   MAIN
═══════════════════════════════════ */

.search-main h3 {
  margin-bottom: 20px;

  font-family: var(--sans);
  font-size: .58rem;
  font-weight: 600;

  letter-spacing: .24em;
  text-transform: uppercase;

  color: rgba(46, 26, 10, .35);
}

/* ═══════════════════════════════════
   GRID
═══════════════════════════════════ */

.search-results-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 22px;
}

/* ═══════════════════════════════════
   MESSAGE VIDE
═══════════════════════════════════ */

.recent-tags p {
  font-size: .74rem;
  font-style: italic;

  color: rgba(46, 26, 10, .34);
}

/* ═══════════════════════════════════
   MOBILE
═══════════════════════════════════ */

@media (max-width: 1024px) {

  .search-overlay {
    height: 100%;
  }

  .search-container {
    padding:
      78px
      20px
      120px;
  }

  .search-layout {
    display: block;
  }

  .search-sidebar {
    display: none;
  }

  .search-input-wrapper {
    max-width: 100%;
  }

  .search-input-wrapper input {
    font-size: 1.4rem;
  }

  .search-results-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
  }

  .search-mobile-tags {
    display: flex;
    justify-content: center;
  }

  .close-search {
    top: 20px;
    right: 18px;
  }
}
/* ══════════════════════════════════════════════════════════
   SEARCH — Résultats overlay + page search.php
   Modern Old Money · Palette Beige & Marron
   ══════════════════════════════════════════════════════════ */

/* ══ CARTE PRODUIT DANS L'OVERLAY ════════════════════════════ */

.search-product {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 10px;
  padding: 14px 10px 16px;
  background: var(--glass);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid var(--border-s);
  cursor: pointer;
  text-decoration: none;
  color: inherit;
  transition:
    border-color 0.3s var(--ease),
    transform 0.35s var(--ease),
    background 0.3s var(--ease),
    box-shadow 0.35s var(--ease);
  position: relative;
  overflow: hidden;
}

.search-product::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 1.5px;
  background: var(--gold);
  transition: width 0.35s var(--ease);
}

.search-product:hover {
  border-color: rgba(214, 196, 168, 0.8);
  transform: translateY(-4px);
  background: rgba(250, 246, 240, 0.92);
  box-shadow: 0 14px 36px var(--dark-12);
}

.search-product:hover::after { width: 50%; }

.search-product img {
  width: 100%;
  aspect-ratio: 1 / 1;
  object-fit: contain;
  background: rgba(255, 255, 255, 0.6);
  border: 1px solid var(--border-s);
  display: block;
  transition: transform 0.45s var(--ease);
}

.search-product:hover img { transform: scale(1.06); }

.search-product p {
  font-family: var(--serif);
  font-size: 12px;
  font-weight: 400;
  font-style: italic;
  letter-spacing: 0.04em;
  color: var(--dark);
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
  color: var(--gold);
  letter-spacing: 0.08em;
}

/* ══ MESSAGE VIDE / ERREUR ═══════════════════════════════════ */

.search-empty {
  grid-column: 1 / -1;
  text-align: center;
  padding: 40px 20px;
  font-family: var(--serif);
  font-size: 16px;
  font-style: italic;
  color: var(--muted);
  letter-spacing: 0.04em;
}

/* ══════════════════════════════════════════════════════════
   PAGE SEARCH.PHP — résultats pleine page
   ══════════════════════════════════════════════════════════ */

/* ── Hero résultats ── */
.search-page-hero {
  margin-top: 90px;
  padding: 48px 6% 38px;
  background: var(--glass-s);
  backdrop-filter: blur(16px) saturate(160%);
  -webkit-backdrop-filter: blur(16px) saturate(160%);
  border-bottom: 1px solid var(--border-s);
  text-align: center;
  position: relative;
}

.search-page-hero::before {
  content: "✦  ✦  ✦";
  position: absolute;
  top: 18px;
  left: 6%;
  font-size: 9px;
  color: var(--gold);
  letter-spacing: 8px;
  opacity: .4;
}

.search-page-hero::after {
  content: "✦  ✦  ✦";
  position: absolute;
  top: 18px;
  right: 6%;
  font-size: 9px;
  color: var(--gold);
  letter-spacing: 8px;
  opacity: .4;
}

.search-page-hero h1 {
  font-family: var(--serif);
  font-size: clamp(26px, 4vw, 40px);
  font-weight: 300;
  font-style: italic;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--dark);
  line-height: 1.1;
  margin-bottom: 8px;
}

.search-page-hero h1 em {
  font-style: normal;
  color: var(--gold);
}

.search-page-hero .search-count {
  font-family: var(--sans);
  font-size: 9px;
  letter-spacing: 0.3em;
  text-transform: uppercase;
  color: var(--muted);
}

/* ── Barre de recherche intégrée à la page ── */
.search-page-bar {
  max-width: 560px;
  margin: 28px auto 0;
  display: flex;
  align-items: center;
  gap: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--border-s);
  transition: border-color 0.25s;
}

.search-page-bar:focus-within { border-color: var(--gold); }

.search-page-bar svg {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
  color: var(--muted);
  transition: color 0.25s;
}

.search-page-bar:focus-within svg { color: var(--gold); }

.search-page-bar input {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  font-family: var(--serif);
  font-size: 1.3rem;
  font-weight: 300;
  font-style: italic;
  letter-spacing: 0.04em;
  color: var(--dark);
}

.search-page-bar input::placeholder {
  color: var(--muted);
  font-style: italic;
}

/* ── Layout de la page ── */
.search-page-body {
  max-width: 1280px;
  margin: 48px auto 100px;
  padding: 0 48px;
}

/* ── Breadcrumb ── */
.search-breadcrumb {
  font-family: var(--sans);
  font-size: 9.5px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 32px;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
}

.search-breadcrumb a { color: var(--dark-60); transition: color 0.2s; }
.search-breadcrumb a:hover { color: var(--gold); }
.search-breadcrumb .sep { color: var(--dark-20); }
.search-breadcrumb .cur { color: var(--dark-80); font-weight: 500; }

/* ── Grille résultats pleine page ── */
.search-results-page {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 28px;
}

/* ── Carte produit pleine page (réutilise product-card) ── */
.search-results-page .product-card {
  background: var(--glass);
  backdrop-filter: blur(12px) saturate(160%);
  -webkit-backdrop-filter: blur(12px) saturate(160%);
  border: 1px solid var(--border-s);
  border-radius: 1px;
  padding: 18px;
  position: relative;
  text-align: center;
  transition:
    border-color 0.35s var(--ease),
    transform 0.4s var(--ease),
    box-shadow 0.4s var(--ease),
    background 0.35s;
  overflow: hidden;
}

.search-results-page .product-card::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 1.5px;
  background: var(--gold);
  transition: width 0.4s var(--ease);
}

.search-results-page .product-card:hover {
  border-color: rgba(214, 196, 168, 0.8);
  transform: translateY(-6px);
  background: rgba(250, 246, 240, 0.9);
  box-shadow: 0 20px 48px var(--dark-12);
}

.search-results-page .product-card:hover::after { width: 60%; }

.search-results-page .product-card img {
  width: 100%;
  height: 260px;
  object-fit: contain;
  border: 1px solid var(--border-s);
  background: rgba(255, 255, 255, 0.6);
  display: block;
  transition: transform 0.55s var(--ease);
}

.search-results-page .product-card:hover img { transform: scale(1.05); }

.search-results-page .product-card h3 {
  font-family: var(--serif);
  font-size: 14px;
  font-weight: 400;
  font-style: italic;
  letter-spacing: 0.04em;
  color: var(--dark);
  margin: 14px 0 8px;
  line-height: 1.5;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.search-results-page .price {
  font-family: var(--sans);
  font-size: 13px;
  font-weight: 500;
  color: var(--dark);
  letter-spacing: 0.06em;
}

.search-results-page .old-price {
  font-size: 11px;
  text-decoration: line-through;
  color: var(--muted);
  margin-right: 6px;
}

.search-results-page .sale-price { color: var(--gold); }

/* ── Aucun résultat ── */
.search-no-results {
  grid-column: 1 / -1;
  text-align: center;
  padding: 80px 20px;
}

.search-no-results i {
  font-size: 40px;
  color: var(--beige-d);
  display: block;
  margin-bottom: 20px;
}

.search-no-results p {
  font-family: var(--serif);
  font-size: 20px;
  font-style: italic;
  color: var(--muted);
  margin-bottom: 10px;
}

.search-no-results span {
  font-family: var(--sans);
  font-size: 9.5px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--dark-40);
}

/* ── Suggestions de recherche ── */
.search-suggestions {
  margin-top: 48px;
}

.search-suggestions h3 {
  font-family: var(--sans);
  font-size: 8.5px;
  font-weight: 600;
  letter-spacing: 0.32em;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 16px;
  position: relative;
  padding-bottom: 12px;
}

.search-suggestions h3::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 20px;
  height: 1px;
  background: var(--gold);
}

.suggestion-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.suggestion-tags .tag {
  padding: 8px 18px;
  border: 1px solid var(--border-s);
  border-radius: 100px;
  background: var(--glass);
  backdrop-filter: blur(8px);
  color: var(--marron);
  font-family: var(--sans);
  font-size: 10px;
  font-weight: 400;
  letter-spacing: 0.1em;
  cursor: pointer;
  text-decoration: none;
  transition:
    background 0.3s var(--ease),
    border-color 0.3s,
    color 0.3s,
    transform 0.3s var(--ease),
    box-shadow 0.3s;
}

.suggestion-tags .tag:hover {
  background: var(--marron);
  border-color: var(--marron);
  color: var(--beige-l);
  transform: translateY(-2px);
  box-shadow: 0 8px 20px var(--dark-12);
}

/* ══ RESPONSIVE ══════════════════════════════════════════════ */

@media (max-width: 992px) {
  .search-results-page {
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }
  .search-page-body { padding: 0 28px; margin-top: 36px; }
}

@media (max-width: 768px) {
  .search-results-page {
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
  }
  .search-page-body { padding: 0 16px; margin-top: 24px; }
  .search-results-page .product-card img { height: 180px; }
  .search-page-hero { padding: 36px 6% 28px; }
  .search-page-bar input { font-size: 1.1rem; }
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

  <div class="logo">She<span>Glamour</span></div>

  <nav class="navbar-desktop">
    <a href="/categorie.php?categorie=Yeux">Yeux</a>
    <a href="/categorie.php?categorie=Lèvres">Lèvres</a>
    <a href="/categorie.php?categorie=Teint">Teint</a>
    <a href="/categorie.php?categorie=Accessoires">Accessoires</a>
  </nav>

  <div class="header-divider"></div>

  <div class="icons">
    <!-- Loupe : visible partout -->
    <button class="icon-btn" id="openSearch" aria-label="Rechercher"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="10.5" cy="10.5" r="6.5"/><line x1="15.5" y1="15.5" x2="21" y2="21"/></svg></button>
    <!-- Masqués sur mobile → bottom bar -->
    <a href="/wishlist.php" class="hide-mobile" aria-label="Wishlist"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></a>
    <a href="/cart.php" class="hide-mobile" aria-label="Panier"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></a>
    <a href="/login.php" class="hide-mobile" aria-label="Mon compte"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg></a>
  </div>

</header>

<!-- =============================================
     BOTTOM BAR MOBILE (wishlist / panier / profil)
============================================= -->
<nav class="mobile-bottom-bar" aria-label="Navigation rapide">

  <a href="/wishlist.php" class="bottom-bar-item"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg><span>Favoris</span></a>

  <a href="/cart.php" class="bottom-bar-item"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg><!-- <span class="badge">2</span> --><span>Panier</span></a>

  <a href="/login.php" class="bottom-bar-item"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg><span>Compte</span></a>

</nav>

<!-- =============================================
     MENU MOBILE
============================================= -->
<nav class="navbar-mobile" id="navbar">

  <div class="mobile-menu-header">
    <div class="mobile-logo">SheGlamour</div>
    <button class="mobile-close" id="mobileClose" aria-label="Fermer">&times;</button>
  </div>

  <div class="mobile-tabs">
    <button class="tab-btn active" data-tab="categories">Catégories</button>
    <button class="tab-btn" data-tab="brands">Marques</button>
  </div>

  <div class="tab-content active" id="categories">
    <a href="/categorie.php?categorie=Blush" class="menu-item">
      <img src="images/blush.jpg" alt="Blush" />
      <span>Blush</span>
    </a>
    <a href="/categorie.php?categorie=Lèvres" class="menu-item">
      <img src="images/lips.jpg" alt="Lèvres" />
      <span>Lèvres</span>
    </a>
    <a href="/categorie.php?categorie=Yeux" class="menu-item">
      <img src="images/eyes.jpg" alt="Yeux" />
      <span>Yeux</span>
    </a>
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
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="10.5" cy="10.5" r="6.5"/><line x1="15.5" y1="15.5" x2="21" y2="21"/></svg>
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

  /* ---- Scroll → header blanc ---- */
  const onScroll = () => header.classList.toggle("scrolled", window.scrollY > 30);
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  /* ---- Menu mobile ---- */
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

  menuToggle.addEventListener("click", () => navbar.classList.contains("active") ? closeMenu() : openMenu());
  menuOverlay.addEventListener("click", closeMenu);
  mobileClose.addEventListener("click", closeMenu);

  /* ---- Tabs ---- */
  document.querySelectorAll(".tab-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
      document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));
      btn.classList.add("active");
      document.getElementById(btn.dataset.tab).classList.add("active");
    });
  });

  /* ---- Recherches récentes ---- */
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
      : `<p style="font-size:0.73rem;color:#bbb;font-style:italic">Aucune recherche</p>`;
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

  /* ---- Search overlay ---- */
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

  /* ---- Recherche live ---- */
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
        if (!res.ok) throw new Error('not ok');
        const data = await res.json();
        if (!Array.isArray(data)) throw new Error('bad data');
        results.innerHTML = data.length
          ? data.map(p => `
              <div class="search-product">
                <img src="${p.image_url}" alt="${p.name}" onerror="this.style.display='none'" />
                <p>${p.name}</p>
                <strong>DA ${parseFloat(p.price).toFixed(2)}</strong>
              </div>`).join("")
          : `<p style="font-size:0.78rem;color:#bbb;font-style:italic;grid-column:1/-1">Aucun produit trouvé pour "<strong>${q}</strong>".</p>`;
      } catch {
        // En local / sans PHP : afficher un message propre plutôt qu'une erreur
        results.innerHTML = `<p style="font-size:0.78rem;color:#bbb;font-style:italic;grid-column:1/-1">Les résultats s'afficheront ici lors de la recherche.</p>`;
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