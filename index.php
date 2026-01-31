<?php
include_once 'includes/db.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SheGlamour</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/sheglam/sidebar.css?v=<?= time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="index.css?v=<?php echo time(); ?>">

</head>
<body>
<?php include_once 'includes/sidebar.php';
 ?>
 <?php include 'includes/header.php'; ?>


<section class="hero-slider">
  <div class="slide active" style="background-image: url('images/1766567932171684802b501201ae09c27d1f505da0_thumbnail_3600x.webp');">
    <div class="hero-content">
      <h1>Brillez avec SheGlamour</h1>
      <p>Découvrez notre nouvelle collection de maquillage glamour.</p>
      <a href="#" class="btn">Découvrir maintenant</a>
    </div>
  </div>

  <div class="slide" style="background-image: url('images/1766568099187bc63d385f046b820f583208e51c5c_thumbnail_3600x.webp');">
    <div class="hero-content">
      <h1>Couleurs éclatantes</h1>
      <p>Des teintes audacieuses pour révéler votre beauté unique.</p>
      <a href="#" class="btn">Voir la boutique</a>
    </div>
  </div>

  <div class="slide" style="background-image: url('images/17627683847b4f5dafc00e2b5d9b78b5921d37525e_thumbnail_3600x.webp');">
    <div class="hero-content">
      <h1>Makeup professionnel</h1>
      <p>Des produits haut de gamme à prix doux.</p>
      <a href="#" class="btn">Shoppez maintenant</a>
    </div>
  </div>

  <!-- Boutons de navigation -->
  <div class="navigation">
    <span class="prev"><i class="fas fa-chevron-left"></i></span>
    <span class="next"><i class="fas fa-chevron-right"></i></span>
  </div>
</section>
<section class="create-look">
  <div class="create-item">
<a href="/sheglam/categorie.php?categorie=Yeux">
    <img src="images/17665480556f948b43e0330036a390fb039f3cf7fc_thumbnail_3600x.webp" alt="Maquillage des yeux">
    <div class="overlay"><h3>Yeux</h3></div>
  </a>
</div>

<div class="create-item">
<a href="/sheglam/categorie.php?categorie=Lèvres">
    <img src="images/17665480944dd41369b657c8fc3cb68f38ca70b4a9_thumbnail_3600x.webp" alt="Maquillage des lèvres">
    <div class="overlay"><h3>Lèvres</h3></div>
  </a>
</div>

<div class="create-item">
<a href="/sheglam/categorie.php?categorie=Teint">
    <img src="images/1766548121f695d7aa476d1661e3329edbdd09418a_thumbnail_3600x.webp" alt="Maquillage du teint">
    <div class="overlay"><h3>Teint</h3></div>
  </a>
</div>

<div class="create-item">
<a href="/sheglam/categorie.php?categorie=Accessoires">
    <img src="images/placeholde.webp" alt="Accessoires makeup">
    <div class="overlay"><h3>Accessoires</h3></div>
  </a>
</div>

</section>
<!-- WORTH THE HYPE -->
<section class="worth-hype">
  <div class="hype-left">
    <img
      src="/sheglam/images/174125510358a1215ecd9f25fdbf5165c9993d62fb_thumbnail_3600x.webp"
      alt="Worth the Hype SheGlamour"
    >
  </div>

  <div class="hype-right">
    <h2>Worth the Hype</h2>

    <div class="hype-products">
      <?php
      $stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC LIMIT 4");

      while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $productId = $product['id'];

        // Vérifier les teintes
        $shadeStmt = $pdo->prepare("SELECT COUNT(*) FROM teintes WHERE product_id = ?");
        $shadeStmt->execute([$productId]);
        $hasShades = $shadeStmt->fetchColumn() > 0;

        // Image normalisée
        if (empty($product['image_url'])) continue;
        $image = str_starts_with($product['image_url'], 'http')
          ? $product['image_url']
          : '/sheglam/images/' . basename($product['image_url']);
      ?>

      <div class="product-card">

        <!-- IMAGE -->
        <div class="product-image-wrapper">
          <a href="product.php?id=<?= $productId ?>">
            <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
          </a>

          <!-- WISHLIST -->
          <button
            class="add-to-wishlist"
            data-product-id="<?= $productId ?>"
            data-name="<?= htmlspecialchars($product['name']) ?>"
            data-price="<?= htmlspecialchars($product['price']) ?>"
            data-image_url="<?= htmlspecialchars($image) ?>"
          >
            <i class="fas fa-heart"></i>
          </button>
        </div>

        <!-- INFOS -->
        <div class="product-info">
          <h3>
            <a href="product.php?id=<?= $productId ?>">
              <?= htmlspecialchars($product['name']) ?>
            </a>
          </h3>

          <p class="price">
            €<?= number_format($product['price'], 2, ',', ' ') ?>
          </p>

          <?php if ($hasShades): ?>

            <!-- AVEC TEINTES → MODAL -->
            <button
              class="choose-shade-btn"
              data-product-id="<?= $productId ?>"
              data-name="<?= htmlspecialchars($product['name']) ?>"
              data-price="<?= htmlspecialchars($product['price']) ?>"
              data-image_url="<?= htmlspecialchars($image) ?>"
            >
              <i class="fas fa-palette"></i> Choisir une teinte
            </button>

          <?php else: ?>

            <!-- SANS TEINTES → PANIER DIRECT -->
            <div class="add-to-cart-wrapper">

              <div class="quantity-wrapper">
                
                <input
                  type="number"
                  name="quantity"
                  value="1"
                  min="1"
                  step="1"
                >
              </div>

              <button
                class="add-to-cart"
                data-product-id="<?= $productId ?>"
                data-name="<?= htmlspecialchars($product['name']) ?>"
                data-price="<?= htmlspecialchars($product['price']) ?>"
                data-image_url="<?= htmlspecialchars($image) ?>"
              >
                <i class="fas fa-shopping-bag"></i> Ajouter au panier
              </button>

            </div>

          <?php endif; ?>

        </div>
      </div>

      <?php } ?>
    </div>
  </div>
</section>


<!-- INCLURE LE MODAL (OBLIGATOIRE POUR TEINTES) -->
<?php include 'includes/product_modal.php'; ?>

<script>
  const slides = document.querySelectorAll('.slide');
  const next = document.querySelector('.next');
  const prev = document.querySelector('.prev');
  let index = 0;

  function showSlide(i) {
    slides.forEach(slide => slide.classList.remove('active'));
    slides[i].classList.add('active');
  }

  function nextSlide() {
    index = (index + 1) % slides.length;
    showSlide(index);
  }

  function prevSlideFn() {
    index = (index - 1 + slides.length) % slides.length;
    showSlide(index);
  }

  next.addEventListener('click', nextSlide);
  prev.addEventListener('click', prevSlideFn);

  // Défilement automatique
  setInterval(nextSlide, 5000);
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const header = document.querySelector(".header");

  function onScroll() {
    if (window.scrollY > 60) {
      header.classList.add("scrolled");
    } else {
      header.classList.remove("scrolled");
    }
  }

  window.addEventListener("scroll", onScroll);
  onScroll(); // état initial
});
</script>

<script src="/sheglam/js/shop.js?v=<?= time(); ?>"></script>

</body>
</html>
