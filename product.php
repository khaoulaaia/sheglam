<?php
include 'includes/db.php';


$productId = $_GET['id'] ?? null;
if (!$productId) die("Produit introuvable");

// --- TRAITEMENT FORMULAIRE AVIS ICI ---
if (isset($_POST['submit_review'])) {
    $name = trim($_POST['customer_name']);
    $rating = (int) $_POST['rating'];
    $comment = trim($_POST['comment']);

    if ($name && $rating >= 1 && $rating <= 5 && $comment) {
        $insert = $pdo->prepare("
            INSERT INTO product_reviews (product_id, customer_name, rating, comment)
            VALUES (?, ?, ?, ?)
        ");
        $insert->execute([$productId, $name, $rating, $comment]);

        // Redirection pour éviter double soumission
        header("Location: product.php?id=" . $productId);
        exit;
    }
}
// Récupération du produit
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) die("Produit introuvable");

// Images supplémentaires (product_images)
$imageStmt = $pdo->prepare("SELECT image FROM product_images WHERE product_id = ?");
$imageStmt->execute([$productId]);
$additionalImages = $imageStmt->fetchAll(PDO::FETCH_COLUMN);

// Si pas d'images supplémentaires, on ajoute l'image principale
if (empty($additionalImages)) $additionalImages = [$product['image_url']];

// Vérifier si le produit a des teintes
$shadeStmt = $pdo->prepare("SELECT COUNT(*) FROM teintes WHERE product_id = ?");
$shadeStmt->execute([$productId]);
$hasShades = $shadeStmt->fetchColumn() > 0;

// Avis clients
$reviewStmt = $pdo->prepare("SELECT * FROM product_reviews WHERE product_id = ? ORDER BY date_creation DESC");
$reviewStmt->execute([$productId]);
$reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);

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
<link rel="stylesheet" href="/sheglam/product.css?v=<?= time(); ?>">
<link rel="stylesheet" href="/sheglam/sidebar.css?v=<?= time(); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- Font Awesome 6 Free (Solid) via CDN -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">


</head>
<body>

<?php include 'includes/sidebar.php'; ?>
 <?php include 'includes/header.php'; ?>
<!-- Breadcrumb -->
<section class="breadcrumb">
  <div class="breadcrumb-container">
    <a href="/sheglam/index.php">Accueil</a>
    <a href="/sheglam/shop.php?categorie=<?= urlencode($product['categorie']) ?>">
      <?= htmlspecialchars($product['categorie']) ?>
    </a>
    <span><?= htmlspecialchars($product['name']) ?></span>
  </div>
</section>


<section class="product-page">
  <!-- Images supplémentaires à gauche -->
  <div class="product-left">
    <?php foreach($additionalImages as $img): ?>
<img 
  src="<?= htmlspecialchars($img) ?>" 
  class="thumbnail"
  onclick="document.getElementById('mainImage').src='<?= htmlspecialchars($img) ?>'"
>
    <?php endforeach; ?>
  </div>

  <!-- Image principale + infos produit -->
  <div class="product-main">
    <div class="product-main-image">
      <img id="mainImage" src="<?= htmlspecialchars($additionalImages[0]) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
    </div>

    <div class="product-right">
      <h1><?= htmlspecialchars($product['name']) ?></h1>
      
<?php
$averageRating = 0;
if ($reviews) {
    $sum = array_sum(array_column($reviews, 'rating'));
    $count = count($reviews);
    $averageRating = round($sum / $count, 1); // 1 chiffre après la virgule
}
?>
<div class="reviews">
  <h3>Avis clients</h3>

  <?php if ($reviews): ?>
    <div class="average-rating">
      <?php
      // Affichage 5 étoiles
      for ($i = 1; $i <= 5; $i++):
          if ($i <= floor($averageRating)) {
              // Étoile pleine
              echo '<i class="fas fa-star"></i>';
          } elseif ($i - $averageRating <= 0.5) {
              // Demi-étoile
              echo '<i class="fas fa-star-half-alt"></i>';
          } else {
              // Étoile vide
              echo '<i class="far fa-star"></i>';
          }
      endfor;
      ?>
      <span>(<?= $averageRating ?>/5)</span>
    </div>
  <?php else: ?>
    <p>Aucun avis pour le moment.</p>
  <?php endif; ?>
</div>
<p class="price">
    <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
        <span class="old-price"><?= number_format($product['old_price'], 2, ',', ' ') ?>DA</span>
    <?php endif; ?>
    <?= number_format($product['price'], 2, ',', ' ') ?>DA
</p>
      <p class="description"><?= htmlspecialchars($product['description']) ?></p>

      <!-- Avis clients -->
       
      <!-- Tes boutons wishlist / teinte / panier restent inchangés -->
    

<?php if ($hasShades): ?>

  <button class="choose-shade-btn"
    data-product-id="<?= $product['id'] ?>"
    data-name="<?= htmlspecialchars($product['name']) ?>"
    data-price="<?= htmlspecialchars($product['price']) ?>"
    data-image_url="<?= htmlspecialchars($additionalImages[0]) ?>">
    <i class="fas fa-palette"></i> Choisir une teinte
  </button>

<?php else: ?>

  <div class="add-to-cart-wrapper">

    <div class="quantity-wrapper">
      <label for="quantity">Quantité :</label>
      <input type="number" id="quantity" name="quantity" value="1" min="1" step="1">
    </div>

    <button class="add-to-cart"
      data-product-id="<?= $product['id'] ?>"
      data-name="<?= htmlspecialchars($product['name']) ?>"
      data-price="<?= htmlspecialchars($product['price']) ?>"
      data-image_url="<?= htmlspecialchars($additionalImages[0]) ?>">
      <i class="fas fa-shopping-bag"></i> Ajouter au panier
    </button>

  </div>

<?php endif; ?>

   
<!-- PARTAGE RESEAUX SOCIAUX --><div class="share-product">
  <span>Partager :</span>

  <a href="#" class="share-btn facebook" data-network="facebook" title="Partager sur Facebook">
    <i class="fab fa-facebook-square"></i>
  </a>

  <a href="#" class="share-btn twitter" data-network="twitter" title="Partager sur X / Twitter">
    <i class="fab fa-x-twitter"></i>
  </a>

  <a href="#" class="share-btn whatsapp" data-network="whatsapp" title="Partager sur WhatsApp">
    <i class="fab fa-whatsapp"></i>
  </a>

  <a href="#" class="share-btn pinterest" data-network="pinterest" title="Partager sur Pinterest">
    <i class="fab fa-pinterest-p"></i>
  </a>

  <!-- Bouton partage natif (mobile) -->
  <button id="nativeShare" class="share-btn native" title="Partager">
    <i class="fas fa-share-alt"></i>
  </button>
</div>

</section>

<!-- Laisser un avis -->
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

    <button type="submit" name="submit_review">
      Envoyer l’avis
    </button>
  </form>
</div>
<!-- Produits similaires -->
<section class="similar-products">
  <h2>Produits similaires</h2>
  <div class="similar-container">
    <?php foreach($similarProducts as $sp): ?>
  <a href="/sheglam/product.php?id=<?= $sp['id'] ?>" class="similar-item">
    <img src="<?= htmlspecialchars($sp['image_url']) ?>" alt="<?= htmlspecialchars($sp['name']) ?>">
    <h4><?= htmlspecialchars($sp['name']) ?></h4>
    <p class="price"><?= number_format($sp['price'], 2, ',', ' ') ?> DA</p>
  </a>
<?php endforeach; ?>

  </div>
</section>

<!-- Modal teintes -->
<?php include 'includes/product_modal.php'; ?>
<script src="/sheglam/js/shop.js?v=<?= time(); ?>"></script>
<?php include 'includes/footer.php'; ?>


<script>
const stars = document.querySelectorAll("#starRating i");
const ratingInput = document.getElementById("ratingInput");

stars.forEach(star => {
  star.addEventListener("click", () => {
    const value = star.dataset.value;
    ratingInput.value = value;

    stars.forEach(s => {
      s.classList.toggle("fa-solid", s.dataset.value <= value);
      s.classList.toggle("fa-regular", s.dataset.value > value);
    });
  });
});
</script><script>
document.querySelectorAll(".share-btn:not(.native)").forEach(btn => {
  btn.addEventListener("click", e => {
    e.preventDefault();

    const network = btn.dataset.network;
    if (!network) return;

    const productName = "<?= addslashes($product['name']) ?>";
    const productPrice = "<?= number_format($product['price'], 2, ',', ' ') ?> DA";
    const url = encodeURIComponent(window.location.href);

    const message = encodeURIComponent(
      `✨ J’ai trouvé ce produit incroyable sur SheGlamour ! 💄\n` +
      `👉 ${productName} à ${productPrice}\n` +
      `🔗 `
    );

    let shareUrl = "";

    switch (network) {
      case "whatsapp":
        shareUrl = `https://api.whatsapp.com/send?text=${message}${url}`;
        break;
      case "twitter":
        shareUrl = `https://twitter.com/intent/tweet?text=${message}${url}`;
        break;
      case "facebook":
        shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
        break;
    }

    window.open(shareUrl, "_blank", "width=600,height=500");
  });
});
</script>

<script>
const shareBtn = document.getElementById("nativeShare");

if (navigator.share && shareBtn) {
  shareBtn.addEventListener("click", async () => {
    try {
      await navigator.share({
        title: "<?= addslashes($product['name']) ?>",
        text: "✨ J’ai trouvé ce produit incroyable sur SheGlamour ! 💄",
        url: window.location.href
      });
    } catch (err) {
      console.log("Partage annulé");
    }
  });
}
</script>
<script>
const thumbnails = document.querySelectorAll(".product-left img");
const mainImage = document.getElementById("mainImage");

thumbnails.forEach(img => {
  img.addEventListener("click", () => {
    // Changer image principale
    mainImage.src = img.src;

    // Retirer active partout
    thumbnails.forEach(t => t.classList.remove("active"));

    // Ajouter active à celle cliquée
    img.classList.add("active");
  });
});

// Activer la première image par défaut
if (thumbnails.length) {
  thumbnails[0].classList.add("active");
}
</script>

</body>
</html>