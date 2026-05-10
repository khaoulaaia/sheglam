<?php
$q = trim($_GET['q'] ?? '');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Recherche : <?= htmlspecialchars($q) ?> – SheGlamour</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="index.css?v=<?php echo time(); ?>">


<style>
/* ============================= */
/* BASE */
/* ============================= */

body {
  font-family: var(--sans);
  background: var(--white);
  color: var(--text);
  margin: 0;
  padding: 120px 6%;
}

a,
a:visited,
a:hover,
a:active {
  color: inherit;
  text-decoration: none;
}

/* ============================= */
/* TITRE */
/* ============================= */

.search-title {
  font-family: var(--serif);
  font-size: 32px;
  font-weight: 400;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--dark);
  margin-bottom: 50px;
}

.search-title span {
  font-weight: 600;
  color: var(--bordeaux-s);
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
  border: 1px solid var(--border-s);
  padding: 20px;
  text-align: center;
  background: var(--white);
  transition: all 0.35s var(--ease);
}

.search-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 28px var(--dark-12);
  border-color: var(--border);
}

/* IMAGE */

.search-card img {
  width: 100%;
  height: 260px;
  object-fit: contain;
  border: 1px solid var(--border-s);
  margin-bottom: 20px;
}

/* NOM */

.search-card h3 {
  font-family: var(--serif);
  font-size: 15px;
  font-weight: 500;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--dark);
  margin-bottom: 10px;

  min-height: 42px;
  line-height: 1.4;

  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* PRIX */

.search-price {
  font-family: var(--sans);
  font-size: 15px;
  font-weight: 500;
  color: var(--bordeaux-s);
  margin-bottom: 16px;
}

/* BOUTON */

.search-btn {
  display: inline-block;
  padding: 10px 22px;
  background: var(--bordeaux);
  color: var(--white);
  font-family: var(--sans);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  text-decoration: none;
  transition: background 0.3s var(--ease), box-shadow 0.3s var(--ease);
}

.search-btn:hover {
  background: var(--bordeaux-s);
  box-shadow: 0 6px 18px var(--dark-20);
}

/* VIDE */

.no-result {
  font-size: 16px;
  color: var(--muted);
}

/* ============================= */
/* RESPONSIVE */
/* ============================= */

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
 <?php include 'includes/header.php'; ?>
 
<h1 class="search-title">
  Résultats pour <span>“<?= htmlspecialchars($q) ?>”</span>
</h1>

<div id="results" class="search-grid"></div>

<script>
fetch("/includes/search_products.php?query=<?= urlencode($q) ?>")
  .then(res => res.json())
  .then(data => {
    const results = document.getElementById("results");

    if (!data.length) {
      results.innerHTML = `<p class="no-result">Aucun produit trouvé.</p>`;
      return;
    }

    results.innerHTML = data.map(p => `
      <a href="/product.php?id=${p.id}" class="search-card">
        <img src="${p.image_url}" alt="${p.name}">
        <h3>${p.name}</h3>
        <div class="search-price">${parseFloat(p.price).toFixed(2)}DA</div>
        <span class="search-btn">Voir le produit</span>
      </a>
    `).join("");
  });
</script>

</body>
</html>
