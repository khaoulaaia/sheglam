<?php
include 'includes/db.php';
include_once 'includes/config.php';
$b = BASE_URL;

$productId = $_GET['id'] ?? null;
if (!$productId) die("Produit introuvable");

// Traitement avis
if (isset($_POST['submit_review'])) {
    $name    = trim($_POST['customer_name']);
    $rating  = (int) $_POST['rating'];
    $comment = trim($_POST['comment']);
    if ($name && $rating >= 1 && $rating <= 5 && $comment) {
        $insert = $pdo->prepare("INSERT INTO product_reviews (product_id, customer_name, rating, comment) VALUES (?, ?, ?, ?)");
        $insert->execute([$productId, $name, $rating, $comment]);
        header("Location: product.php?id=" . $productId);
        exit;
    }
}

// Produit
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) die("Produit introuvable");

$stock      = (int)($product['stock'] ?? 0);
$outOfStock = $stock === 0;

// Images supplémentaires
$imageStmt = $pdo->prepare("SELECT image FROM product_images WHERE product_id = ?");
$imageStmt->execute([$productId]);
$additionalImages = $imageStmt->fetchAll(PDO::FETCH_COLUMN);
if (empty($additionalImages)) $additionalImages = [$product['image_url']];

$additionalImages = array_map(function($img) use ($b) {
    if (empty($img)) return $b . '/images/placeholder.jpg';
    return str_starts_with($img, 'http') ? $img : $b . '/images/' . basename($img);
}, $additionalImages);

// Teintes
$shadeStmt = $pdo->prepare("SELECT COUNT(*) FROM teintes WHERE product_id = ?");
$shadeStmt->execute([$productId]);
$hasShades = $shadeStmt->fetchColumn() > 0;

// Avis
$reviewStmt = $pdo->prepare("SELECT * FROM product_reviews WHERE product_id = ? ORDER BY date_creation DESC");
$reviewStmt->execute([$productId]);
$reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);

// Note moyenne
$averageRating = 0;
if ($reviews) {
    $averageRating = round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1);
}

// Produits similaires
$similarStmt = $pdo->prepare("SELECT * FROM products WHERE categorie = ? AND id != ? LIMIT 4");
$similarStmt->execute([$product['categorie'], $productId]);
$similarProducts = $similarStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($product['name']) ?> - SheGlamour</title>
  <link rel="stylesheet" href="<?= $b ?>/product.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= $b ?>/sidebar.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>
<?php include 'includes/header.php'; ?>

<!-- Breadcrumb -->
<section class="breadcrumb">
  <div class="breadcrumb-container">
    <a href="<?= $b ?>/index.php">Accueil</a>
    <a href="<?= $b ?>/categorie.php?categorie=<?= urlencode($product['categorie']) ?>">
      <?= htmlspecialchars($product['categorie']) ?>
    </a>
    <span><?= htmlspecialchars($product['name']) ?></span>
  </div>
</section>

<section class="product-page">

  <!-- Miniatures gauche -->
  <div class="product-left">
    <?php foreach ($additionalImages as $img): ?>
      <img src="<?= htmlspecialchars($img) ?>"
           class="thumbnail"
           onclick="document.getElementById('mainImage').src='<?= htmlspecialchars($img) ?>'">
    <?php endforeach; ?>
  </div>

  <!-- Image principale -->
  <div class="product-main">
    <div class="product-main-image">
      <?php if ($outOfStock): ?>
        <div class="out-of-stock-badge">Rupture de stock</div>
      <?php endif; ?>
      <img id="mainImage"
           src="<?= htmlspecialchars($additionalImages[0]) ?>"
           alt="<?= htmlspecialchars($product['name']) ?>"
           class="<?= $outOfStock ? 'img-out-of-stock' : '' ?>">
    </div>

    <div class="product-right">
      <h1><?= htmlspecialchars($product['name']) ?></h1>

      <!-- Badge stock -->
      <?php if ($outOfStock): ?>
        <span class="stock-badge out">
          <i class="fas fa-times-circle"></i> Rupture de stock
        </span>
      <?php elseif ($stock <= 5): ?>
        <span class="stock-badge low">
          <i class="fas fa-exclamation-circle"></i> Plus que <?= $stock ?> en stock
        </span>
      <?php else: ?>
        <span class="stock-badge in">
          <i class="fas fa-check-circle"></i> En stock
        </span>
      <?php endif; ?>

      <!-- Étoiles -->
      <div class="reviews">
        <h3>Avis clients</h3>
        <?php if ($reviews): ?>
          <div class="average-rating">
            <?php for ($i = 1; $i <= 5; $i++):
              if ($i <= floor($averageRating))        echo '<i class="fas fa-star"></i>';
              elseif ($i - $averageRating <= 0.5)     echo '<i class="fas fa-star-half-alt"></i>';
              else                                     echo '<i class="far fa-star"></i>';
            endfor; ?>
            <span>(<?= $averageRating ?>/5)</span>
          </div>
        <?php else: ?>
          <p>Aucun avis pour le moment.</p>
        <?php endif; ?>
      </div>

      <!-- Prix -->
      <p class="price">
        <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
          <span class="old-price"><?= number_format($product['old_price'], 2, ',', ' ') ?>DA</span>
        <?php endif; ?>
        <?= number_format($product['price'], 2, ',', ' ') ?>DA
      </p>

      <p class="description"><?= htmlspecialchars($product['description']) ?></p>

      <!-- Bouton teinte ou panier -->
      <?php if ($hasShades): ?>
        <button class="choose-shade-btn"
          data-product-id="<?= $product['id'] ?>"
          data-name="<?= htmlspecialchars($product['name']) ?>"
          data-price="<?= htmlspecialchars($product['price']) ?>"
          data-image_url="<?= htmlspecialchars($additionalImages[0]) ?>"
          data-stock="<?= $stock ?>"
          <?= $outOfStock ? 'disabled' : '' ?>>
          <i class="fas fa-<?= $outOfStock ? 'ban' : 'palette' ?>"></i>
          <?= $outOfStock ? 'Rupture de stock' : 'Choisir une teinte' ?>
        </button>
      <?php else: ?>
        <div class="add-to-cart-wrapper">
          <?php if (!$outOfStock): ?>
            <div class="quantity-wrapper">
              <label for="quantity">Quantité :</label>
              <input type="number" id="quantity" name="quantity"
                     value="1" min="1" max="<?= $stock ?>" step="1">
            </div>
          <?php endif; ?>
          <button class="add-to-cart"
            data-product-id="<?= $product['id'] ?>"
            data-name="<?= htmlspecialchars($product['name']) ?>"
            data-price="<?= htmlspecialchars($product['price']) ?>"
            data-image_url="<?= htmlspecialchars($additionalImages[0]) ?>"
            data-stock="<?= $stock ?>"
            <?= $outOfStock ? 'disabled' : '' ?>>
            <i class="fas fa-<?= $outOfStock ? 'ban' : 'shopping-bag' ?>"></i>
            <?= $outOfStock ? 'Rupture de stock' : 'Ajouter au panier' ?>
          </button>
        </div>
      <?php endif; ?>

      <!-- Partage -->
      <div class="share-product">
        <span>Partager :</span>
        <a href="#" class="share-btn facebook" data-network="facebook" title="Facebook"><i class="fab fa-facebook-square"></i></a>
        <a href="#" class="share-btn twitter"   data-network="twitter"   title="Twitter"><i class="fab fa-x-twitter"></i></a>
        <a href="#" class="share-btn whatsapp"  data-network="whatsapp"  title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
        <a href="#" class="share-btn pinterest" data-network="pinterest" title="Pinterest"><i class="fab fa-pinterest-p"></i></a>
        <button id="nativeShare" class="share-btn native" title="Partager"><i class="fas fa-share-alt"></i></button>
      </div>

    </div><!-- .product-right -->
  </div><!-- .product-main -->

</section>

<!-- Formulaire avis -->
<div class="review-form">
  <h3>Laisser un avis</h3>
  <form method="POST">
    <input type="hidden" name="product_id" value="<?= $productId ?>">
    <label>Votre nom</label>
    <input type="text" name="customer_name" required>
    <label>Votre note</label>
    <div class="stars" id="starRating">
      <i class="fa-regular fa-star" data-value="1"></i>
      <i class="fa-regular fa-star" data-value="2"></i>
      <i class="fa-regular fa-star" data-value="3"></i>
      <i class="fa-regular fa-star" data-value="4"></i>
      <i class="fa-regular fa-star" data-value="5"></i>
    </div>
    <input type="hidden" name="rating" id="ratingInput" required>
    <label>Votre avis</label>
    <textarea name="comment" rows="4" required></textarea>
    <button type="submit" name="submit_review">Envoyer l'avis</button>
  </form>
</div>

<!-- Liste des avis -->
<?php if ($reviews): ?>
<div class="reviews-list">
  <h3>Ce que disent nos clients</h3>
  <?php foreach ($reviews as $review): ?>
    <div class="review-item">
      <div class="review-header">
        <strong><?= htmlspecialchars($review['customer_name']) ?></strong>
        <div class="review-stars">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <i class="fa<?= $i <= $review['rating'] ? 's' : 'r' ?> fa-star"></i>
          <?php endfor; ?>
        </div>
        <span class="review-date"><?= date('d/m/Y', strtotime($review['date_creation'])) ?></span>
      </div>
      <p class="review-comment"><?= htmlspecialchars($review['comment']) ?></p>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Produits similaires -->
<section class="similar-products">
  <h2>Produits similaires</h2>
  <div class="similar-container">
    <?php foreach ($similarProducts as $sp):
      $spImg      = empty($sp['image_url']) ? $b . '/images/placeholder.jpg'
                  : (str_starts_with($sp['image_url'], 'http') ? $sp['image_url'] : $b . '/images/' . basename($sp['image_url']));
      $spStock    = (int)($sp['stock'] ?? 0);
      $spNoStock  = $spStock === 0;
    ?>
      <a href="<?= $b ?>/product.php?id=<?= $sp['id'] ?>" class="similar-item <?= $spNoStock ? 'out-of-stock' : '' ?>">
        <?php if ($spNoStock): ?>
          <span class="similar-badge-oos">Rupture</span>
        <?php endif; ?>
        <img src="<?= htmlspecialchars($spImg) ?>"
             alt="<?= htmlspecialchars($sp['name']) ?>"
             class="<?= $spNoStock ? 'img-out-of-stock' : '' ?>">
        <h4><?= htmlspecialchars($sp['name']) ?></h4>
        <p class="price"><?= number_format($sp['price'], 2, ',', ' ') ?> DA</p>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php include 'includes/product_modal.php'; ?>

<script>
const BASE_URL = "<?= $b ?>";

/* ── Étoiles avis ── */
document.querySelectorAll("#starRating i").forEach(star => {
  star.addEventListener("click", () => {
    const v = star.dataset.value;
    document.getElementById("ratingInput").value = v;
    document.querySelectorAll("#starRating i").forEach(s => {
      s.classList.toggle("fa-solid",   s.dataset.value <= v);
      s.classList.toggle("fa-regular", s.dataset.value >  v);
    });
  });
});

/* ── Thumbnails ── */
const thumbnails = document.querySelectorAll(".product-left img");
const mainImage  = document.getElementById("mainImage");
thumbnails.forEach(img => {
  img.addEventListener("click", () => {
    mainImage.src = img.src;
    thumbnails.forEach(t => t.classList.remove("active"));
    img.classList.add("active");
  });
});
if (thumbnails.length) thumbnails[0].classList.add("active");

/* ── Partage réseaux sociaux ── */
document.querySelectorAll(".share-btn:not(.native)").forEach(btn => {
  btn.addEventListener("click", e => {
    e.preventDefault();
    const network = btn.dataset.network;
    if (!network) return;
    const productName  = "<?= addslashes($product['name']) ?>";
    const productPrice = "<?= number_format($product['price'], 2, ',', ' ') ?> DA";
    const url = encodeURIComponent(window.location.href);
    const message = encodeURIComponent(`✨ J'ai trouvé ce produit incroyable sur SheGlamour ! 💄\n👉 ${productName} à ${productPrice}\n🔗 `);
    const urls = {
      whatsapp:  `https://api.whatsapp.com/send?text=${message}${url}`,
      twitter:   `https://twitter.com/intent/tweet?text=${message}${url}`,
      facebook:  `https://www.facebook.com/sharer/sharer.php?u=${url}`,
      pinterest: `https://pinterest.com/pin/create/button/?url=${url}`
    };
    if (urls[network]) window.open(urls[network], "_blank", "width=600,height=500");
  });
});

/* ── Partage natif (mobile) ── */
const shareBtn = document.getElementById("nativeShare");
if (navigator.share && shareBtn) {
  shareBtn.addEventListener("click", async () => {
    try {
      await navigator.share({
        title: "<?= addslashes($product['name']) ?>",
        text: "✨ J'ai trouvé ce produit incroyable sur SheGlamour ! 💄",
        url: window.location.href
      });
    } catch (err) {}
  });
}
</script>

<script src="<?= $b ?>/js/shop.js?v=<?= time() ?>"></script>
<?php include 'includes/footer.php'; ?>
</body>
</html>