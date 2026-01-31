<?php
include 'includes/db.php';

// Récupération de la catégorie
$categorie = $_GET['categorie'] ?? 'Tous';

// Requête pour récupérer les produits selon la catégorie
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
<title><?= htmlspecialchars($categorie) ?> - SheGlamour</title>
<link rel="stylesheet" href="/sheglam/categorie.css?v=<?= time(); ?>">
<link rel="stylesheet" href="/sheglam/sidebar.css?v=<?= time(); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>
<?php include 'includes/header.php'; ?>

<aside class="filter-sidebar" id="filterSidebar">
  <h3>Filtres</h3>

  <!-- TRI PRIX -->
  <div class="filter-group">
    <label for="sortPrice">Prix</label>
    <select id="sortPrice">
      <option value="">--</option>
      <option value="asc">Croissant</option>
      <option value="desc">Décroissant</option>
    </select>
  </div>

  <!-- FILTRE PRODUITS EN SOLDE (toggle moderne) -->
  <div class="filter-group toggle-group">
    <span>Produits en solde</span>
    <label class="switch">
      <input type="checkbox" id="filterSale">
      <span class="slider"></span>
    </label>
  </div>

  <!-- FILTRE MARQUE -->
  <div class="filter-group">
    <label for="filterBrand">Marque</label>
    <select id="filterBrand">
      <option value="">Toutes</option>
      <!-- options dynamiques si nécessaire -->
    </select>
  </div>

  <!-- PRODUITS POPULAIRES -->
  <div class="filter-group best-sellers">
  <h4>Produits populaires</h4>
  <ul>
    <?php
      // Récupérer les 3 derniers produits ajoutés
      $bestSellers = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 3");
      while ($item = $bestSellers->fetch(PDO::FETCH_ASSOC)):
        $img = $item['image_url'] ? 'images/' . basename($item['image_url']) : '/images/placeholder.jpg';
    ?>
    <li>
      <a href="/sheglam/product.php?id=<?= $item['id'] ?>">
        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
        <div class="best-seller-info">
          <span class="name"><?= htmlspecialchars($item['name']) ?></span>
          <span class="price">€<?= number_format($item['price'], 2, ',', ' ') ?></span>
        </div>
      </a>
    </li>
    <?php endwhile; ?>
  </ul>
</div>

</aside>



<section class="products-section">
  
    <h1><?= htmlspecialchars($categorie) ?></h1>
    <!-- Breadcrumb -->
<nav class="breadcrumb">
  <a href="/sheglam/index.php">Accueil</a> &gt;
  <?php if($categorie === 'Tous'): ?>
    <span>Tous les produits</span>
  <?php else: ?>
    <a href="/sheglam/categorie.php?categorie=Tous">Tous</a> &gt;
    <span><?= htmlspecialchars($categorie) ?></span>
  <?php endif; ?>
</nav>

    <!-- Choix du nombre de produits par ligne -->
<div class="view-toggle">
  <!-- Vues seulement -->
  <button class="view-btn active" data-view="grid" title="Grille complète">
    <span></span><span></span><span></span><span></span>
  </button>
  <button class="view-btn" data-view="list" title="Liste compacte">
    <span></span><span></span><span></span>
  </button>
  
</div>





    <div class="products-grid">

<?php while ($product = $query->fetch(PDO::FETCH_ASSOC)): 
    $productId = $product['id'];

    // Image produit
    $imagePath = $product['image_url']
        ? (strpos($product['image_url'], 'http') === 0
            ? $product['image_url']
            : 'images/' . basename($product['image_url']))  
        : '/images/placeholder.jpg';

    // Vérifier si le produit a des teintes
    $shadeStmt = $pdo->prepare("SELECT COUNT(*) FROM teintes WHERE product_id = ?");
    $shadeStmt->execute([$productId]);
    $hasShades = $shadeStmt->fetchColumn() > 0;
?>

<!-- Chaque produit est cliquable -->
<a href="/sheglam/product.php?id=<?= $productId ?>" class="product-card-link">
    <div class="product-card"
         data-price="<?= $product['price'] ?>"
         data-brand="<?= htmlspecialchars($product['marque']) ?>"
         data-sale="<?= $product['is_sale'] ? '1' : '0' ?>">

        <div class="product-image-wrapper">
            <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            
            <!-- Wishlist -->
            <button class="add-to-wishlist"
                    data-product-id="<?= $productId ?>"
                    data-name="<?= htmlspecialchars($product['name']) ?>"
                    data-price="<?= htmlspecialchars($product['price']) ?>"
                    data-image_url="<?= htmlspecialchars($imagePath) ?>"
                    type="button">
                <i class="fas fa-heart"></i>
            </button>
        </div>

        <div class="product-info">
            <h3><?= htmlspecialchars($product['name']) ?></h3>
            <p class="price">
                <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
                    <span class="old-price">
                        €<?= number_format($product['old_price'], 2, ',', ' ') ?>
                    </span>
                    <span class="sale-price">
                        €<?= number_format($product['price'], 2, ',', ' ') ?>
                    </span>
                <?php else: ?>
                    €<?= number_format($product['price'], 2, ',', ' ') ?>
                <?php endif; ?>
            </p>

            <?php if ($hasShades): ?>
                <!-- Bouton pour produits avec teintes -->
                <button class="choose-shade-btn"
                        data-product-id="<?= $productId ?>"
                        data-name="<?= htmlspecialchars($product['name']) ?>"
                        data-price="<?= htmlspecialchars($product['price']) ?>"
                        data-image_url="<?= htmlspecialchars($imagePath) ?>"
                        type="button">
                    <i class="fas fa-palette"></i> Choisir une teinte
                </button>
            <?php else: ?>
                <!-- Bouton pour produits sans teintes -->
                <button class="add-to-cart"
                        data-product-id="<?= $productId ?>"
                        data-name="<?= htmlspecialchars($product['name']) ?>"
                        data-price="<?= htmlspecialchars($product['price']) ?>"
                        data-image_url="<?= htmlspecialchars($imagePath) ?>"
                        type="button">
                    <i class="fas fa-shopping-bag"></i> Ajouter au panier
                </button>
            <?php endif; ?>
        </div>
    </div>
</a>

<?php endwhile; ?>

</div>

</section>
  <script>
document.addEventListener('DOMContentLoaded', function() {
  const productsGrid = document.querySelector('.products-grid');

  // =============================
  // BOUTONS DE VUE
  // =============================
  const viewButtons = document.querySelectorAll('.view-btn');
  viewButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      // retirer active sur tous les boutons
      viewButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const view = btn.getAttribute('data-view');

      // Supprimer toutes les classes de vue
      productsGrid.classList.remove('products-list', 'products-list-detailed');
      productsGrid.classList.add('products-grid'); // classe par défaut

      if(view === 'list') {
        productsGrid.classList.add('products-list');
      } else if(view === 'list-detailed') {
        productsGrid.classList.add('products-list-detailed');
      }
    });
  });

  // =============================
  // BOUTONS COLONNES (1-3-5)
  // =============================
  const colsButtons = document.querySelectorAll('.cols-btn');
  
  // Appliquer 3 colonnes par défaut
  productsGrid.classList.add('cols-3');

  colsButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      // retirer active sur tous les boutons
      colsButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const cols = btn.getAttribute('data-cols');

      // Supprimer toutes les classes de colonnes
      productsGrid.classList.remove('cols-1', 'cols-3', 'cols-5');
      productsGrid.classList.add(`cols-${cols}`);
    });
  });
});
</script>

<script>
  // Empêche les boutons dans une carte produit de déclencher le lien
document.addEventListener("click", e => {
  const btn = e.target.closest(
    ".add-to-wishlist, .add-to-cart, .choose-shade-btn"
  );

  if (btn) {
    e.preventDefault();
    e.stopPropagation();
  }
});

</script>

<!-- Modal teintes -->
<?php include 'includes/product_modal.php'; ?>

<script src="/sheglam/js/shop.js?v=<?= time(); ?>"></script>

</body>
</html>
