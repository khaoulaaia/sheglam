<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SheGlamour – Header</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    :root {
      --rose-nude:    #C9889A;
      --rose-profond: #9B4D68;
      --blush:        #F5E8EC;
      --noir:         #1A1A1A;
      --gris-perle:   #FAF7F5;
      --blanc:        #FFFFFF;
      --texte-doux:   #6B4E5A;
      --serif:  'Cormorant Garamond', Georgia, serif;
      --sans:   'Jost', 'Helvetica Neue', sans-serif;
      --ease:   cubic-bezier(0.4, 0, 0.2, 1);
      --dur:    0.38s;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: var(--sans);
      color: var(--noir);
      background: #f0e8ea;
      min-height: 200vh;
      /* espace pour la bottom bar mobile */
      padding-bottom: 0;
    }

    a { text-decoration: none; color: inherit; }

    /* =========================================
       HEADER
    ========================================= */
    .header {
      position: fixed;
      top: 0; left: 0;
      width: 100%;
      height: 68px;
      display: grid;
      grid-template-columns: 1fr auto 1fr;
      align-items: center;
      padding: 0 5%;
      z-index: 2000;
      background: transparent;
      transition: background var(--dur) var(--ease),
                  box-shadow var(--dur) var(--ease),
                  height var(--dur) var(--ease);
    }

    /* ✦ Blanc au scroll */
    .header.scrolled {
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      box-shadow: 0 1px 0 rgba(201, 136, 154, 0.18),
                  0 4px 24px rgba(155, 77, 104, 0.06);
      height: 62px;
    }

    /* Filet rose sous le header au scroll */
    .header::after {
      content: '';
      position: absolute;
      bottom: 0; left: 0; right: 0;
      height: 1px;
      background: rgba(201, 136, 154, 0);
      transition: background var(--dur) var(--ease);
    }
    .header.scrolled::after {
      background: rgba(201, 136, 154, 0.2);
    }

    /* =========================================
       LOGO
    ========================================= */
    .logo {
      font-family: var(--serif);
      font-size: 1.5rem;
      font-weight: 600;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: var(--noir);
      user-select: none;
      transition: color var(--dur);
      justify-self: start; /* col 1 : gauche */
    }

    .logo span { color: var(--rose-nude); }

    /* =========================================
       NAVBAR DESKTOP
    ========================================= */
    .navbar-desktop {
      display: flex;
      gap: 36px;
      align-items: center;
      justify-self: center; /* col 2 : centre */
    }

    .navbar-desktop a {
      font-family: var(--sans);
      font-size: 0.68rem;
      font-weight: 500;
      letter-spacing: 0.22em;
      text-transform: uppercase;
      color: var(--noir);
      padding: 6px 0;
      position: relative;
      transition: color var(--dur);
    }

    .navbar-desktop a::after {
      content: '';
      position: absolute;
      bottom: 0; left: 0;
      width: 0; height: 1px;
      background: var(--rose-nude);
      transition: width var(--dur) var(--ease);
    }

    .navbar-desktop a:hover { color: var(--rose-nude); }
    .navbar-desktop a:hover::after { width: 100%; }

    /* =========================================
       ICONS (desktop)
    ========================================= */
    .icons {
      display: flex;
      align-items: center;
      gap: 20px;
      justify-self: end; /* col 3 : droite */
    }

    /* ── Icônes header : boîte carrée uniforme ── */
    .icon-btn,
    .icons a {
      background: none;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      color: var(--noir);
      transition: color var(--dur), transform var(--dur);
      flex-shrink: 0;
    }

    /* Tous les SVG inline : même gabarit 20×20 */
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
      color: var(--rose-nude);
      transform: translateY(-1px);
    }

    .icons a:hover svg { stroke: var(--rose-nude); }

    /* Séparateur — masqué, la grille gère l'espacement */
    .header-divider { display: none; }

    /* =========================================
       HAMBURGER
    ========================================= */
    .menu-toggle {
      display: none;
      flex-direction: column;
      justify-content: space-between;
      width: 28px;
      height: 20px;
      background: none;
      border: none;
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
    }

    .menu-toggle span {
      display: block;
      width: 100%;
      height: 1px;
      background: var(--noir);
      transition: transform var(--dur) var(--ease), opacity var(--dur), width var(--dur);
      transform-origin: left center;
    }

    .menu-toggle span:nth-child(2) { width: 70%; }
    .menu-toggle:hover span:nth-child(2) { width: 100%; }

    .menu-toggle.active span:nth-child(1) { transform: rotate(42deg); }
    .menu-toggle.active span:nth-child(2) { opacity: 0; width: 0; }
    .menu-toggle.active span:nth-child(3) { transform: rotate(-42deg); }

    /* =========================================
       MENU MOBILE
    ========================================= */
    .navbar-mobile {
      position: fixed;
      top: 0; left: -100%;
      width: 78%;
      max-width: 300px;
      height: 100dvh;
      background: var(--blanc);
      display: flex;
      flex-direction: column;
      transition: left 0.4s var(--ease);
      z-index: 2600;
      overflow: hidden;
    }

    .navbar-mobile::before {
      content: '';
      display: block;
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, var(--rose-nude), var(--rose-profond));
      flex-shrink: 0;
    }

    .navbar-mobile.active { left: 0; }

    .mobile-menu-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 22px 18px;
      border-bottom: 1px solid rgba(201, 136, 154, 0.12);
      flex-shrink: 0;
    }

    .mobile-logo {
      font-family: var(--serif);
      font-size: 1.15rem;
      font-weight: 600;
      letter-spacing: 0.16em;
      text-transform: uppercase;
    }

    .mobile-close {
      background: none;
      border: 1px solid rgba(201, 136, 154, 0.3);
      border-radius: 50%;
      width: 30px; height: 30px;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer;
      font-size: 18px;
      font-weight: 300;
      color: var(--texte-doux);
      transition: border-color var(--dur), color var(--dur), transform var(--dur);
    }

    .mobile-close:hover {
      border-color: var(--rose-nude);
      color: var(--rose-profond);
      transform: rotate(90deg);
    }

    /* Tabs */
    .mobile-tabs {
      display: flex;
      border-bottom: 1px solid rgba(201, 136, 154, 0.12);
      flex-shrink: 0;
    }

    .tab-btn {
      flex: 1;
      padding: 13px 8px;
      background: none;
      border: none;
      font-family: var(--sans);
      font-size: 0.62rem;
      font-weight: 500;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: rgba(26, 26, 26, 0.4);
      cursor: pointer;
      border-bottom: 2px solid transparent;
      margin-bottom: -1px;
      transition: color var(--dur), border-color var(--dur);
    }

    .tab-btn.active {
      color: var(--rose-profond);
      border-bottom-color: var(--rose-nude);
    }

    .tab-content {
      display: none;
      flex-direction: column;
      flex: 1;
      overflow-y: auto;
    }

    .tab-content.active { display: flex; }

    .menu-item {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 13px 22px;
      font-size: 0.86rem;
      color: var(--noir);
      border-bottom: 1px solid rgba(201, 136, 154, 0.07);
      transition: background var(--dur), padding-left var(--dur), color var(--dur);
    }

    .menu-item:hover {
      background: var(--blush);
      padding-left: 28px;
      color: var(--rose-profond);
    }

    .menu-item img {
      width: 44px; height: 44px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid rgba(201, 136, 154, 0.15);
    }

    .menu-item::after {
      content: '→';
      font-size: 0.7rem;
      color: var(--rose-nude);
      margin-left: auto;
      opacity: 0;
      transform: translateX(-4px);
      transition: opacity var(--dur), transform var(--dur);
    }

    .menu-item:hover::after { opacity: 1; transform: translateX(0); }

    #brands a {
      display: block;
      padding: 14px 22px;
      font-size: 0.86rem;
      color: var(--noir);
      border-bottom: 1px solid rgba(201, 136, 154, 0.07);
      transition: background var(--dur), padding-left var(--dur), color var(--dur);
    }

    #brands a:hover {
      background: var(--blush);
      padding-left: 28px;
      color: var(--rose-profond);
    }

    .mobile-footer {
      padding: 16px 22px;
      border-top: 1px solid rgba(201, 136, 154, 0.12);
      font-family: var(--serif);
      font-size: 0.72rem;
      font-style: italic;
      color: rgba(155, 77, 104, 0.45);
      letter-spacing: 0.07em;
      flex-shrink: 0;
    }

    /* =========================================
       OVERLAY
    ========================================= */
    .menu-overlay {
      position: fixed;
      inset: 0;
      background: rgba(26, 26, 26, 0.35);
      backdrop-filter: blur(3px);
      -webkit-backdrop-filter: blur(3px);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.38s var(--ease);
      z-index: 2500;
    }

    .menu-overlay.active { opacity: 1; pointer-events: auto; }

    /* =========================================
       SEARCH OVERLAY
    ========================================= */
    .search-overlay {
      position: fixed;
      inset: 0;
      background: var(--blanc);
      z-index: 4000;
      transform: translateY(-100%);
      transition: transform 0.55s var(--ease);
      overflow-y: auto;
      height: 52%;
    }

    .search-overlay.active { transform: translateY(0); }

    .close-search {
      position: absolute;
      top: 20px; right: 28px;
      width: 34px; height: 34px;
      background: none;
      border: 1px solid rgba(201, 136, 154, 0.25);
      border-radius: 50%;
      cursor: pointer;
      font-size: 18px;
      font-weight: 300;
      color: var(--texte-doux);
      display: flex;
      align-items: center;
      justify-content: center;
      transition: border-color var(--dur), color var(--dur), transform var(--dur);
    }

    .close-search:hover {
      border-color: var(--rose-nude);
      color: var(--rose-profond);
      transform: rotate(90deg);
    }

    .search-container {
      padding: 80px 7% 60px;
      display: flex;
      flex-direction: column;
      gap: 44px;
    }

    .search-input-wrapper {
      max-width: 580px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      gap: 12px;
      border-bottom: 1px solid rgba(26, 26, 26, 0.18);
      padding-bottom: 12px;
      transition: border-color var(--dur);
    }

    .search-input-wrapper:focus-within { border-color: var(--rose-nude); }

    .search-input-wrapper i {
      color: rgba(26, 26, 26, 0.25);
      font-size: 13px;
      flex-shrink: 0;
      transition: color var(--dur);
    }

    .search-input-wrapper:focus-within i { color: var(--rose-nude); }

    .search-input-wrapper input {
      flex: 1;
      border: none;
      outline: none;
      background: transparent;
      text-align: center;
      font-family: var(--serif);
      font-size: 1.8rem;
      font-weight: 300;
      letter-spacing: 0.06em;
      color: var(--noir);
    }

    .search-input-wrapper input::placeholder {
      color: rgba(26, 26, 26, 0.22);
      font-style: italic;
    }

    .search-layout {
      display: grid;
      grid-template-columns: 26% 1fr;
      gap: 48px;
    }

    .search-sidebar {
      padding-right: 32px;
      border-right: 1px solid rgba(201, 136, 154, 0.12);
    }

    .search-sidebar h4 {
      font-family: var(--sans);
      font-size: 0.58rem;
      letter-spacing: 0.26em;
      text-transform: uppercase;
      color: rgba(26, 26, 26, 0.38);
      margin: 26px 0 12px;
    }

    .search-sidebar h4:first-child { margin-top: 0; }

    .recent-tags, .hot-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .tag {
      font-family: var(--sans);
      font-size: 0.73rem;
      letter-spacing: 0.05em;
      background: none;
      border: 1px solid rgba(201, 136, 154, 0.25);
      padding: 5px 13px;
      border-radius: 20px;
      cursor: pointer;
      color: var(--texte-doux);
      transition: background var(--dur), color var(--dur), border-color var(--dur);
    }

    .tag:hover {
      background: var(--blush);
      border-color: var(--rose-nude);
      color: var(--rose-profond);
    }

    .search-main h3 {
      font-family: var(--sans);
      font-size: 0.57rem;
      letter-spacing: 0.26em;
      text-transform: uppercase;
      color: rgba(26, 26, 26, 0.32);
      margin-bottom: 18px;
    }

    .search-results-grid {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 20px;
    }

    .search-product {
      text-align: center;
      cursor: pointer;
      transition: transform var(--dur);
    }

    .search-product:hover { transform: translateY(-4px); }

    .search-product img {
      width: 100%;
      aspect-ratio: 1 / 1;
      object-fit: contain;
      padding: 8px;
      border-radius: 10px;
      background: var(--blush);
      transition: transform var(--dur);
    }

    .search-product:hover img { transform: scale(1.05); }

    .search-product p {
      font-size: 0.7rem;
      margin-top: 7px;
      color: var(--noir);
    }

    .search-product strong {
      font-family: var(--serif);
      font-size: 0.78rem;
      color: var(--rose-profond);
      font-weight: 600;
    }

    .search-mobile-tags { display: none; }

    /* =========================================
       ✦ BOTTOM BAR MOBILE — Panier / Wishlist / Profil
    ========================================= */
    .mobile-bottom-bar {
      display: none; /* caché sur desktop */
    }

    /* =========================================
       RESPONSIVE
    ========================================= */
    @media (max-width: 1024px) {
      .navbar-desktop { display: none; }

      .header {
        display: flex; /* on quitte la grille sur mobile */
        justify-content: center;
        padding: 0 16px;
        height: 58px;
      }

      .logo {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        justify-self: unset;
        font-size: 1.2rem;
      }

      .menu-toggle {
        display: flex;
      }

      /* Sur mobile : cacher wishlist / panier / profil du header */
      .icons .hide-mobile {
        display: none;
      }

      /* Garder seulement la loupe dans le header */
      .icons {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        gap: 12px;
      }

      /* ---- BOTTOM BAR ---- */
      .mobile-bottom-bar {
        display: flex;
        position: fixed;
        bottom: 0; left: 0;
        width: 100%;
        height: 62px;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-top: 1px solid rgba(201, 136, 154, 0.18);
        box-shadow: 0 -4px 24px rgba(155, 77, 104, 0.08);
        z-index: 2900;
        justify-content: space-around;
        align-items: center;
        padding: 0 8px;
      }

      .bottom-bar-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        flex: 1;
        padding: 8px 0;
        color: var(--texte-doux);
        font-size: 0.55rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        font-family: var(--sans);
        font-weight: 500;
        transition: color var(--dur);
        text-decoration: none;
        position: relative;
      }

      .bottom-bar-item svg {
        width: 20px;
        height: 20px;
        display: block;
        transition: transform var(--dur);
      }

      .bottom-bar-item:hover,
      .bottom-bar-item:active {
        color: var(--rose-profond);
      }

      .bottom-bar-item:hover i {
        transform: translateY(-2px);
      }

      /* Séparateurs verticaux */
      .bottom-bar-item + .bottom-bar-item::before {
        content: '';
        position: absolute;
        left: 0; top: 20%; bottom: 20%;
        width: 1px;
        background: rgba(201, 136, 154, 0.15);
      }

      /* Badge panier */
      .bottom-bar-item .badge {
        position: absolute;
        top: 4px;
        left: 50%;
        margin-left: 4px;
        min-width: 16px;
        height: 16px;
        background: var(--rose-profond);
        color: white;
        border-radius: 99px;
        font-size: 0.6rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        letter-spacing: 0;
      }

      body { padding-bottom: 62px; }

      /* Search overlay */
      .search-container {
        padding: 68px 5% 40px;
        gap: 28px;
      }

      .search-layout { display: block; }
      .search-sidebar { display: none; }

      .search-results-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
      }

      .search-input-wrapper {
        max-width: 100%;
      }

      .search-input-wrapper input {
        font-size: 1.35rem;
      }

      .search-mobile-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
      }
    }

    /* Animations menu mobile */
    @keyframes slideIn {
      from { transform: translateX(-12px); opacity: 0; }
      to   { transform: translateX(0);     opacity: 1; }
    }

    .tab-content.active .menu-item:nth-child(1),
    .tab-content.active #brands a:nth-child(1) { animation: slideIn 0.28s var(--ease) 0.04s both; }
    .tab-content.active .menu-item:nth-child(2),
    .tab-content.active #brands a:nth-child(2) { animation: slideIn 0.28s var(--ease) 0.09s both; }
    .tab-content.active .menu-item:nth-child(3),
    .tab-content.active #brands a:nth-child(3) { animation: slideIn 0.28s var(--ease) 0.14s both; }
    .tab-content.active #brands a:nth-child(4) { animation: slideIn 0.28s var(--ease) 0.19s both; }
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
        <h4>Recherches récentes</h4>
        <div class="recent-tags"></div>
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