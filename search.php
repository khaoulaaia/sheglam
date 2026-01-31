<?php
$q = trim($_GET['q'] ?? '');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Recherche : <?= htmlspecialchars($q) ?></title>
</head>
<body>

<h1>Résultats pour “<?= htmlspecialchars($q) ?>”</h1>

<div id="results"></div>

<script>
fetch("/sheglam/includes/search_products.php?query=<?= urlencode($q) ?>")
  .then(res => res.json())
  .then(data => {
    document.getElementById("results").innerHTML =
      data.length
        ? data.map(p => `
          <div>
            <img src="${p.image_url}" width="80">
            <p>${p.name}</p>
            <strong>€${parseFloat(p.price).toFixed(2)}</strong>
          </div>
        `).join("")
        : "<p>Aucun produit trouvé.</p>";
  });
</script>

</body>
</html>
