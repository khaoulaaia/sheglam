<?php
include 'includes/db.php'; // Connexion DB

$categorie = $_GET['categorie'] ?? 'Tous';

if ($categorie === 'Tous') {
    $query = $pdo->query("SELECT * FROM products ORDER BY id ASC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE categorie = :categorie ORDER BY id ASC");
    $stmt->execute(['categorie' => $categorie]);
    $query = $stmt;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($categorie) ?> - SheGlamour</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>

<?php include_once 'includes/sidebar.php';
 ?>
<?php include 'includes/header.php'; ?>

<section class="products-section">
    <h1><?= htmlspecialchars($categorie) ?></h1>
    <div class="products-grid">

    <?php while ($product = $query->fetch(PDO::FETCH_ASSOC)):
        $productId = $product['id'];
        $shadeStmt = $pdo->prepare("SELECT * FROM teintes WHERE product_id = ?");
        $shadeStmt->execute([$productId]);
        $shades = $shadeStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

        <div class="product-card">
            <div class="product-image-wrapper">
                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <button class="add-to-wishlist"
                        data-name="<?= htmlspecialchars($product['name']) ?>"
                        data-price="<?= htmlspecialchars($product['price']) ?>"
                        data-image="<?= htmlspecialchars($product['image_url']) ?>">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
            <div class="product-info">
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p class="price">€<?= number_format($product['price'], 2, ',', ' ') ?></p>

                <?php if ($shades): ?>
                    <button class="choose-shade-btn" data-product-id="<?= $productId ?>">Choisir une teinte</button>
                <?php else: ?>
                    <button class="add-to-cart"
                            data-name="<?= htmlspecialchars($product['name']) ?>"
                            data-price="<?= htmlspecialchars($product['price']) ?>"
                            data-image="<?= htmlspecialchars($product['image_url']) ?>">
                        <i class="fas fa-shopping-bag"></i> Ajouter au panier
                    </button>
                <?php endif; ?>
            </div>
        </div>

    <?php endwhile; ?>

    </div>
</section>


<!-- Scripts -->
 <script src="/sheglam/js/sidebar.js"></script>
<script src="/sheglam/js/shop.js"></script>
 <?php include_once 'includes/product_modal.php'; ?>
 <script src="/sheglam/js/product_modal.js"></script>


</body>
</html>
