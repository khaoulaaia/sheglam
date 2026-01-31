<?php
include_once 'includes/db.php';
$query = $_GET['q'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM products WHERE name ILIKE ?");
$stmt->execute(["%$query%"]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Résultats de recherche</title>
<link rel="stylesheet" href="index.css">
</head>
<body class="search-page">

<div class="search-container">

  <!-- SIDEBAR GAUCHE -->
  <aside class="search-sidebar">
    <h4>Recently Searched</h4>
    <div class="recent-tags">
      <span class="tag">récile ✕</span>
    </div>

    <h4>Hot Searched</h4>
    <div class="hot-tags">
      <span class="tag">🔥 hair</span>
      <span class="tag">🔥 blush</span>
      <span class="tag">foundation</span>
      <span class="tag">highlighter</span>
      <span class="tag">primer</span>
      <span class="tag">contour</span>
      <span class="tag">bronzer</span>
      <span class="tag">lip</span>
      <span class="tag">concealer</span>
    </div>
  </aside>

  <!-- CONTENU DROIT -->
  <main class="search-results">
    <h3>Best Seller</h3>

    <div class="products-grid">
      <?php if (!$results): ?>
        <p>Aucun produit trouvé.</p>
      <?php else: ?>
        <?php foreach ($results as $p): ?>
          <div class="product-card">
            <img src="<?= $p['image_url'] ?>" alt="<?= $p['name'] ?>">
            <p class="product-name"><?= $p['name'] ?></p>
            <p class="product-price">$<?= number_format($p['price'], 2) ?></p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>

</div>

</body>

</html>
