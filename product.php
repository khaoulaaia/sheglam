<?php
require_once __DIR__ . '/includes/db.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<h2>Produit introuvable.</h2>";
    exit;
}

// Charger les teintes
$shadesStmt = $pdo->prepare("SELECT * FROM teintes WHERE product_id = ?");
$shadesStmt->execute([$product_id]);
$shades = $shadesStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($product['name']); ?> - Sheglam</title>
<link rel="stylesheet" href="assets/css/styles.css">
<script src="/sheglam/js/sidebar.js" defer></script>
<script src="/sheglam/js/product.js" defer></script>
</head>
<body>

<section class="product-detail">
  <div class="product-image">
    <img src="<?= htmlspecialchars($product['image_url'] ?? '/images/placeholder.jpg'); ?>" alt="<?= htmlspecialchars($product['name']); ?>" id="mainProductImage">
  </div>

  <div class="product-info">
    <h1><?= htmlspecialchars($product['name']); ?></h1>
    <p class="price">€<?= number_format($product['price'], 2, ',', ' '); ?></p>
    <p><?= htmlspecialchars($product['description']); ?></p>

    <?php if ($shades): ?>
      <label for="shadeSelect">Choisir une teinte :</label>
      <select id="shadeSelect">
        <option value="">--Aucune--</option>
        <?php foreach ($shades as $shade): ?>
          <option value="<?= htmlspecialchars($shade['nom_teinte']); ?>"
                  data-price="<?= htmlspecialchars($shade['price'] ?? $product['price']); ?>"
                  data-image="<?= htmlspecialchars($shade['image_url'] ?? $product['image_url']); ?>">
            <?= htmlspecialchars($shade['nom_teinte']); ?> <?= isset($shade['price']) ? "(+€".$shade['price'].")" : "" ?>
          </option>
        <?php endforeach; ?>
      </select>
    <?php endif; ?>

    <button id="addToCartBtn"
            data-name="<?= htmlspecialchars($product['name']); ?>"
            data-price="<?= htmlspecialchars($product['price']); ?>"
            data-image="<?= htmlspecialchars($product['image_url']); ?>">
      🛒 Ajouter au panier
    </button>
  </div>
</section>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

</body>
</html>
