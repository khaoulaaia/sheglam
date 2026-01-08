<?php
include_once 'includes/db.php';

// Récupération du produit via l’ID dans l’URL
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: /sheglam'); // redirige si pas d’ID
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<h2>Produit introuvable.</h2>";
    exit;
}

// Récupération des teintes
$shadeStmt = $pdo->prepare("SELECT * FROM teintes WHERE product_id = ?");
$shadeStmt->execute([$id]);
$shades = $shadeStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - SheGlamour</title>
    <link rel="stylesheet" href="/sheglam/index.css">
    <link rel="stylesheet" href="/sheglam/modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include_once 'includes/sidebar.php'; ?>
<?php include_once 'includes/header.php'; ?>

<section class="product-page">
    <div class="product-container">
        <div class="product-image">
            <img src="/sheglam/<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>

        <div class="product-details">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <p class="price">€<?= number_format($product['price'], 2, ',', ' ') ?></p>
            <p class="description"><?= htmlspecialchars($product['description']) ?></p>
            <p class="category">Catégorie : <?= htmlspecialchars($product['categorie']) ?></p>

            <div class="product-actions">
                <?php if ($shades): ?>
                    <button class="choose-shade-btn" data-product-id="<?= $product['id'] ?>">
                        <i class="fas fa-palette"></i> Choisir une teinte
                    </button>
                <?php else: ?>
                    <button class="add-to-cart"
                            data-name="<?= htmlspecialchars($product['name']) ?>"
                            data-price="<?= htmlspecialchars($product['price']) ?>"
                            data-image="/sheglam/<?= htmlspecialchars($product['image_url']) ?>">
                        <i class="fas fa-shopping-bag"></i> Ajouter au panier
                    </button>
                <?php endif; ?>

                <button class="add-to-wishlist"
                        data-name="<?= htmlspecialchars($product['name']) ?>"
                        data-price="<?= htmlspecialchars($product['price']) ?>"
                        data-image="/sheglam/<?= htmlspecialchars($product['image_url']) ?>">
                    <i class="fas fa-heart"></i> Ajouter aux favoris
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Section "Produits similaires" -->
<section class="related-products">
    <h2>Vous pourriez aussi aimer</h2>
    <div class="products-grid">
        <?php
        $related = $pdo->prepare("SELECT * FROM products WHERE categorie = ? AND id != ? LIMIT 4");
        $related->execute([$product['categorie'], $product['id']]);
        while ($rel = $related->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="product-card">
                <a href="/sheglam/product.php?id=<?= $rel['id'] ?>">
                    <img src="/sheglam/<?= htmlspecialchars($rel['image_url']) ?>" alt="<?= htmlspecialchars($rel['name']) ?>">
                </a>
                <h3><?= htmlspecialchars($rel['name']) ?></h3>
                <p class="price">€<?= number_format($rel['price'], 2, ',', ' ') ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<?php include_once 'includes/product_modal.php'; ?>
<script src="/sheglam/js/sidebar.js"></script>
<script src="/sheglam/js/product_modal.js"></script>

<style>
/* === STYLE PAGE PRODUIT === */
.product-page {
    padding: 80px 10%;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    flex-wrap: wrap;
}
.product-container {
    display: flex;
    gap: 60px;
    flex-wrap: wrap;
    align-items: flex-start;
    max-width: 1200px;
}
.product-image img {
    width: 450px;
    border-radius: 10px;
    object-fit: cover;
}
.product-details {
    max-width: 500px;
}
.product-details h1 {
    font-size: 2rem;
    margin-bottom: 10px;
}
.price {
    color: #e91e63;
    font-weight: bold;
    font-size: 1.5rem;
    margin-bottom: 10px;
}
.description {
    margin: 15px 0;
    color: #555;
    line-height: 1.5;
}
.category {
    font-style: italic;
    color: #777;
    margin-bottom: 20px;
}
.product-actions button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin: 5px;
    padding: 12px 20px;
    border: none;
    background-color: #111;
    color: white;
    border-radius: 5px;
    cursor: pointer;
    transition: 0.3s;
}
.product-actions button:hover {
    background-color: #e91e63;
}
.related-products {
    margin-top: 100px;
    padding: 0 10%;
}
.related-products h2 {
    text-align: center;
    margin-bottom: 30px;
}
.products-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 25px;
}
.product-card {
    width: 220px;
    text-align: center;
}
.product-card img {
    width: 100%;
    border-radius: 8px;
}
</style>

</body>
</html>
