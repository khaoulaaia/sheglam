<?php
include 'includes/db.php';
include_once 'includes/config.php';
$b = BASE_URL;

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
  <title><?= htmlspecialchars($categorie) ?> - SheGlamour</title>
  <link rel="stylesheet" href="<?= $b ?>/categorie.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= $b ?>/sidebar.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- BASE_URL pour le JS -->
<script>const BASE_URL = "<?= $b ?>";</script>

<?php include 'includes/sidebar.php'; ?>
<?php include 'includes/header.php'; ?>

<div class="page-layout">

  <!-- SIDEBAR FILTRES -->
  <aside class="filter-sidebar" id="filterSidebar">
    <h3>Filtres</h3>

    <div class="filter-group">
      <label for="sortPrice">Prix</label>
      <select id="sortPrice">
        <option value="">--</option>
        <option value="asc">Croissant</option>
        <option value="desc">Décroissant</option>
      </select>
    </div>

    <div class="filter-group toggle-group">
      <span>Produits en solde</span>
      <label class="switch">
        <input type="checkbox" id="filterSale">
        <span class="slider"></span>
      </label>
    </div>

    <div class="filter-group">
      <label for="filterBrand">Marque</label>
      <select id="filterBrand">
        <option value="">Toutes</option>
      </select>
    </div>

    <!-- Produits populaires -->
    <div class="filter-group best-sellers">
      <h4>Produits populaires</h4>
      <ul>
        <?php
          $bestSellers = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 3");
          while ($item = $bestSellers->fetch(PDO::FETCH_ASSOC)):
            $img = $item['image_url']
              ? (str_starts_with($item['image_url'], 'http') ? $item['image_url'] : $b . '/images/' . basename($item['image_url']))
              : $b . '/images/placeholder.jpg';
        ?>
        <li>
          <a href="<?= $b ?>/product.php?id=<?= $item['id'] ?>">
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
            <div class="best-seller-info">
              <span class="name"><?= htmlspecialchars($item['name']) ?></span>
              <span class="price"><?= number_format($item['price'], 2, ',', ' ') ?>DA</span>
            </div>
          </a>
        </li>
        <?php endwhile; ?>
      </ul>
    </div>
  </aside>

  <div class="filter-overlay"></div>

  <section class="products-section">
    <h1><?= htmlspecialchars($categorie) ?></h1>

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
      <a href="<?= $b ?>/index.php">Accueil</a> &gt;
      <?php if ($categorie === 'Tous'): ?>
        <span>Tous les produits</span>
      <?php else: ?>
        <a href="<?= $b ?>/categorie.php?categorie=Tous">Tous</a> &gt;
        <span><?= htmlspecialchars($categorie) ?></span>
      <?php endif; ?>
    </nav>

    <!-- Contrôles filtres + vue -->
    <div class="filter-controls">
      <button class="filter-toggle-btn">Filtres</button>
      <div class="view-toggle">
        <button class="view-btn active" data-view="grid" title="Grille">
          <span></span><span></span><span></span><span></span>
        </button>
        <button class="view-btn" data-view="list" title="Liste">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>

    <div class="products-grid">

      <?php while ($product = $query->fetch(PDO::FETCH_ASSOC)):
        $productId = $product['id'];

        $imagePath = empty($product['image_url']) ? $b . '/images/placeholder.jpg'
          : (str_starts_with($product['image_url'], 'http') ? $product['image_url'] : $b . '/images/' . basename($product['image_url']));

        $shadeStmt = $pdo->prepare("SELECT COUNT(*) FROM teintes WHERE product_id = ?");
        $shadeStmt->execute([$productId]);
        $hasShades = $shadeStmt->fetchColumn() > 0;
      ?>

      <a href="<?= $b ?>/product.php?id=<?= $productId ?>" class="product-card-link">
        <div class="product-card"
             data-price="<?= $product['price'] ?>"
             data-brand="<?= htmlspecialchars($product['marque'] ?? '') ?>"
             data-sale="<?= !empty($product['is_sale']) ? '1' : '0' ?>">

          <div class="product-image-wrapper">
            <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <button class="add-to-wishlist"
                    data-product-id="<?= $productId ?>"
                    data-name="<?= htmlspecialchars($product['name']) ?>"
                    data-price="<?= htmlspecialchars($product['price']) ?>"
                    data-image_url="<?= htmlspecialchars($imagePath) ?>"
                    data-has-shades="<?= $hasShades ? 1 : 0 ?>"
                    type="button">
              <i class="fas fa-heart"></i>
            </button>
          </div>

          <div class="product-info">
            <h3><?= htmlspecialchars($product['name']) ?></h3>
            <p class="price">
              <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
                <span class="old-price"><?= number_format($product['old_price'], 2, ',', ' ') ?>DA</span>
                <span class="sale-price"><?= number_format($product['price'], 2, ',', ' ') ?>DA</span>
              <?php else: ?>
                <?= number_format($product['price'], 2, ',', ' ') ?>DA
              <?php endif; ?>
            </p>

            <?php if ($hasShades): ?>
              <button class="choose-shade-btn"
                      data-product-id="<?= $productId ?>"
                      data-name="<?= htmlspecialchars($product['name']) ?>"
                      data-price="<?= htmlspecialchars($product['price']) ?>"
                      data-image_url="<?= htmlspecialchars($imagePath) ?>"
                      type="button">
                <i class="fas fa-palette"></i> Choisir une teinte
              </button>
            <?php else: ?>
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

    </div><!-- .products-grid -->
  </section><!-- .products-section -->

</div><!-- .page-layout -->

<script>
/* ── Sidebar mobile ── */
document.addEventListener('DOMContentLoaded', () => {
  const sidebar   = document.getElementById('filterSidebar');
  const toggleBtn = document.querySelector('.filter-toggle-btn');
  const overlay   = document.querySelector('.filter-overlay');

  toggleBtn?.addEventListener('click', () => {
    sidebar.classList.add('active');
    overlay.classList.add('active');
  });
  overlay?.addEventListener('click', () => {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
  });
  document.addEventListener('keydown', e => {
    if (e.key === "Escape") {
      sidebar.classList.remove('active');
      overlay.classList.remove('active');
    }
  });
});

/* ── Vue grille / liste ── */
document.addEventListener('DOMContentLoaded', () => {
  const grid       = document.querySelector('.products-grid');
  const viewButtons = document.querySelectorAll('.view-btn');

  viewButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      viewButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      grid.classList.remove('products-list');
      if (btn.dataset.view === 'list') grid.classList.add('products-list');
    });
  });
});

/* ── Empêche le lien sur boutons d'action ── */
document.addEventListener("click", e => {
  if (e.target.closest(".add-to-wishlist, .add-to-cart, .choose-shade-btn")) {
    e.preventDefault();
    e.stopPropagation();
  }
});
</script>

<!-- Modal teintes -->
<?php include 'includes/product_modal.php'; ?>
<script src="<?= $b ?>/js/shop.js?v=<?= time() ?>"></script>
<?php include 'includes/footer.php'; ?>

</body>
</html>