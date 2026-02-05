<?php
$q = trim($_GET['q'] ?? '');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Recherche : <?= htmlspecialchars($q) ?> – SheGlamour</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* ============================= */
/* BASE */
/* ============================= */
body {
  font-family: "Didot", "Playfair Display", serif;
  background: #fff;
  color: #111;
  margin: 0;
  padding: 120px 6%;
}
/* SUPPRIME BLEU / MAUVE DES LIENS */
a,
a:visited,
a:hover,
a:active {
  color: inherit;
  text-decoration: none;
}

/* TITRE */
.search-title {
  font-size: 32px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  margin-bottom: 50px;
}

.search-title span {
  font-weight: 500;
}

/* ============================= */
/* GRILLE RÉSULTATS */
/* ============================= */
.search-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 40px;
}

/* ============================= */
/* CARTE PRODUIT */
/* ============================= */
.search-card {
  border: 1px solid #eee;
  padding: 20px;
  text-align: center;
  background: #fff;
  transition: all 0.35s ease;
}

.search-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 28px rgba(0,0,0,0.06);
}

/* IMAGE */
.search-card img {
  width: 100%;
  height: 260px;
  object-fit: contain;
  border: 1px solid #eee;
  margin-bottom: 20px;
}

/* NOM */
/* NOM PRODUIT – HAUTEUR FIXE */
.search-card h3 {
  font-size: 15px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-bottom: 10px;

  /* FIX ALIGNEMENT */
  min-height: 42px;               /* hauteur identique pour tous */
  line-height: 1.4;

  display: -webkit-box;
  -webkit-line-clamp: 2;          /* max 2 lignes */
  -webkit-box-orient: vertical;
  overflow: hidden;
}


/* PRIX */
.search-price {
  font-size: 15px;
  font-weight: 500;
  margin-bottom: 16px;
}

/* BOUTON */
.search-btn {
  display: inline-block;
  padding: 10px 22px;
  background: #000;
  color: #fff;
  font-size: 11px;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  text-decoration: none;
  transition: all 0.3s ease;
}

.search-btn:hover {
  background: #111;
}

/* VIDE */
.no-result {
  font-size: 16px;
  color: #666;
}

/* RESPONSIVE */
@media (max-width: 768px) {
  body {
    padding: 120px 20px;
  }

  .search-title {
    font-size: 24px;
  }
}
</style>
</head>

<body>

<h1 class="search-title">
  Résultats pour <span>“<?= htmlspecialchars($q) ?>”</span>
</h1>

<div id="results" class="search-grid"></div>

<script>
fetch("/sheglam/includes/search_products.php?query=<?= urlencode($q) ?>")
  .then(res => res.json())
  .then(data => {
    const results = document.getElementById("results");

    if (!data.length) {
      results.innerHTML = `<p class="no-result">Aucun produit trouvé.</p>`;
      return;
    }

    results.innerHTML = data.map(p => `
      <a href="/sheglam/product.php?id=${p.id}" class="search-card">
        <img src="${p.image_url}" alt="${p.name}">
        <h3>${p.name}</h3>
        <div class="search-price">DA${parseFloat(p.price).toFixed(2)}</div>
        <span class="search-btn">Voir le produit</span>
      </a>
    `).join("");
  });
</script>

</body>
</html>
