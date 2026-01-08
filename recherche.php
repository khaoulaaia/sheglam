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
<body>
<h2>Résultats pour : <?= htmlspecialchars($query) ?></h2>
<div class="products-grid">
<?php
if (!$results) {
    echo "<p>Aucun produit trouvé.</p>";
} else {
    foreach ($results as $p) {
        echo "
        <div class='product-card'>
            <img src='{$p['image_url']}' alt='{$p['name']}'>
            <h3>{$p['name']}</h3>
            <p>€" . number_format($p['price'], 2, ',', ' ') . "</p>
        </div>";
    }
}
?>
</div>
</body>
</html>
