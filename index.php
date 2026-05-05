<?php
include_once 'includes/db.php';
include_once 'includes/config.php'; // BASE_URL défini ici
$b = BASE_URL;                       // raccourci
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SheGlamour — Beauté & Luxe</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

  <!-- Styles -->
  <link rel="stylesheet" href="<?= $b ?>/sidebar.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= $b ?>/index.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- ══ CURSEUR PERSONNALISÉ ═══════════════════════════════ -->
<div class="cursor-dot"  id="cursorDot"></div>
<div class="cursor-ring" id="cursorRing"></div>

<?php include_once 'includes/sidebar.php'; ?>
<?php include 'includes/header.php'; ?>

<!-- ══ HERO SLIDER ════════════════════════════════════════ -->
<section class="hero-slider">

  <!-- SLIDE 1 -->
  <div class="slide active">
    <picture>
      <source media="(max-width:600px)" srcset="<?= $b ?>/images/17665480944dd41369b657c8fc3cb68f38ca70b4a9_thumbnail_3600x.webp">
      <img src="<?= $b ?>/images/1766567932171684802b501201ae09c27d1f505da0_thumbnail_3600x.webp" alt="Nouvelle collection">
    </picture>
  </div>

  <!-- SLIDE 2 -->
  <div class="slide">
    <picture>
      <source media="(max-width:600px)" srcset="<?= $b ?>/images/17665480556f948b43e0330036a390fb039f3cf7fc_thumbnail_3600x.webp">
      <img src="<?= $b ?>/images/banner_3600x1740_optimized.webp" alt="Couleurs éclatantes">
    </picture>
    <div class="hero-content">
      <h1>Couleurs<br><em>éclatantes</em></h1>
      <p>Des teintes audacieuses pour révéler votre beauté unique.</p>
      <a href="<?= $b ?>/categorie.php?categorie=Tous" class="btn">Voir la boutique</a>
    </div>
  </div>

  <!-- SLIDE 3 -->
  <div class="slide">
    <picture>
      <source media="(max-width:600px)" srcset="<?= $b ?>/images/1766548121f695d7aa476d1661e3329edbdd09418a_thumbnail_3600x.webp">
      <img src="<?= $b ?>/images/banner_desktop_3600x1740_sharp.jpg" alt="Makeup professionnel">
    </picture>
    <div class="hero-content">
      <h1>Makeup<br><em>professionnel</em></h1>
      <p>Des produits haut de gamme à prix doux.</p>
      <a href="<?= $b ?>/categorie.php?categorie=Tous" class="btn">Shoppez maintenant</a>
    </div>
  </div>

  <!-- Navigation -->
  <div class="navigation">
    <span class="prev"><i class="fas fa-chevron-left"></i></span>
    <span class="next"><i class="fas fa-chevron-right"></i></span>
  </div>

  <!-- Dots -->
  <div class="slider-dots">
    <button class="slider-dot active"></button>
    <button class="slider-dot"></button>
    <button class="slider-dot"></button>
  </div>

</section>
<!-- ══ CREATE YOUR LOOK ═══════════════════════════════════ -->
<section class="create-look-section reveal">

  <div class="section-header">
    <h2 class="section-title">Crée ton <em>Look</em></h2>
  </div>

  <div class="create-look">

    <div class="create-item reveal reveal-delay-1">
      <a href="<?= $b ?>/categorie.php?categorie=Yeux">
        <div class="create-item-bg">
          <div class="category-visual">
            <!-- Remplacer le div.cat-icon par <img> si image disponible -->
            <div class="cat-icon cat-yeux"></div>
          </div>
          <img src="<?= $b ?>/images/17665480556f948b43e0330036a390fb039f3cf7fc_thumbnail_3600x.webp" alt="Yeux" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.6;">
        </div>
        <div class="create-overlay">
          <p class="create-name">Yeux</p>
          <span class="create-cta">Explorer</span>
        </div>
      </a>
    </div>

    <div class="create-item reveal reveal-delay-2">
      <a href="<?= $b ?>/categorie.php?categorie=L%C3%A8vres">
        <div class="create-item-bg">
          <div class="category-visual">
            <div class="cat-icon cat-levres"></div>
          </div>
          <img src="<?= $b ?>/images/17665480944dd41369b657c8fc3cb68f38ca70b4a9_thumbnail_3600x.webp" alt="Lèvres" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.6;">
        </div>
        <div class="create-overlay">
          <p class="create-name">Lèvres</p>
          <span class="create-cta">Explorer</span>
        </div>
      </a>
    </div>

    <div class="create-item reveal reveal-delay-3">
      <a href="<?= $b ?>/categorie.php?categorie=Teint">
        <div class="create-item-bg">
          <div class="category-visual">
            <div class="cat-icon cat-teint"></div>
          </div>
          <img src="<?= $b ?>/images/1766548121f695d7aa476d1661e3329edbdd09418a_thumbnail_3600x.webp" alt="Teint" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.6;">
        </div>
        <div class="create-overlay">
          <p class="create-name">Teint</p>
          <span class="create-cta">Explorer</span>
        </div>
      </a>
    </div>

    <div class="create-item reveal reveal-delay-4">
      <a href="<?= $b ?>/categorie.php?categorie=Accessoires">
        <div class="create-item-bg">
          <div class="category-visual">
            <div class="cat-icon cat-access"></div>
          </div>
          <img src="<?= $b ?>/images/placeholde.webp" alt="Accessoires" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.6;">
        </div>
        <div class="create-overlay">
          <p class="create-name">Accessoires</p>
          <span class="create-cta">Explorer</span>
        </div>
      </a>
    </div>

  </div>
</section>

<div class="divider"></div>

<!-- ══ WORTH THE HYPE ════════════════════════════════════ -->
<section class="worth-hype">

  <div class="hype-left reveal reveal-left">
    <div class="hype-image-frame">
      <span class="badge-bestseller">Best Seller</span>
      <img
        src="<?= $b ?>/images/174125510358a1215ecd9f25fdbf5165c9993d62fb_thumbnail_3600x.webp"
        alt="Best Seller">
      <span class="badge-new">New Drop</span>
    </div>
  </div>

  <div class="hype-right reveal reveal-right">

    <div class="hype-header">
      <span class="hype-eyebrow">Nos favoris du moment</span>
      <h2>Worth the <strong>Hype</strong></h2>
    </div>

    <div class="hype-products">
      <?php
      $stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC LIMIT 4");
      while ($product = $stmt->fetch(PDO::FETCH_ASSOC)):
        $productId = $product['id'];

        $shadeStmt = $pdo->prepare("SELECT COUNT(*) FROM teintes WHERE product_id = ?");
        $shadeStmt->execute([$productId]);
        $hasShades = $shadeStmt->fetchColumn() > 0;

        if (empty($product['image_url'])) continue;
        $image = str_starts_with($product['image_url'], 'http')
          ? $product['image_url']
          : $b . '/images/' . basename($product['image_url']);
      ?>

      <div class="product-card">

        <div class="product-image-wrapper">
          <a href="<?= $b ?>/product.php?id=<?= $productId ?>">
            <img
              src="<?= htmlspecialchars($image) ?>"
              alt="<?= htmlspecialchars($product['name']) ?>">
          </a>
          <button class="add-to-wishlist"
            data-product-id="<?= $productId ?>"
            data-name="<?= htmlspecialchars($product['name']) ?>"
            data-price="<?= htmlspecialchars($product['price']) ?>"
            data-image_url="<?= htmlspecialchars($image) ?>"
            data-has-shades="<?= $hasShades ? 1 : 0 ?>">
            <i class="fas fa-heart"></i>
          </button>
        </div>

        <div class="product-info">
          <!-- Optionnel : afficher la catégorie si disponible dans $product['categorie'] -->
          <?php if (!empty($product['categorie'])): ?>
            <p class="product-category-label"><?= htmlspecialchars($product['categorie']) ?></p>
          <?php endif; ?>

          <h3>
            <a href="<?= $b ?>/product.php?id=<?= $productId ?>">
              <?= htmlspecialchars($product['name']) ?>
            </a>
          </h3>

          <p class="price"><?= number_format($product['price'], 2, ',', ' ') ?> DA</p>

          <?php if ($hasShades): ?>
            <button class="choose-shade-btn"
              data-product-id="<?= $productId ?>"
              data-name="<?= htmlspecialchars($product['name']) ?>"
              data-price="<?= htmlspecialchars($product['price']) ?>"
              data-image_url="<?= htmlspecialchars($image) ?>">
              <i class="fas fa-palette"></i> Choisir une teinte
            </button>
          <?php else: ?>
            <div class="add-to-cart-wrapper">
              <div class="quantity-wrapper">
                <input type="number" name="quantity" value="1" min="1" step="1">
              </div>
              <button class="add-to-cart"
                data-product-id="<?= $productId ?>"
                data-name="<?= htmlspecialchars($product['name']) ?>"
                data-price="<?= htmlspecialchars($product['price']) ?>"
                data-image_url="<?= htmlspecialchars($image) ?>">
                <i class="fas fa-shopping-bag"></i> Ajouter au panier
              </button>
            </div>
          <?php endif; ?>
        </div>

      </div>
      <?php endwhile; ?>
    </div>

  </div>

</section>

<?php include 'includes/product_modal.php'; ?>

<!-- ══════════════════════════════════════════════════════ -->
<!--  SCRIPTS                                               -->
<!-- ══════════════════════════════════════════════════════ -->
<script>
/* ── CURSEUR CUSTOM ─────────────────────────────── */
(function() {
  const dot  = document.getElementById('cursorDot');
  const ring = document.getElementById('cursorRing');
  let rx = 0, ry = 0;

  document.addEventListener('mousemove', e => {
    dot.style.left  = e.clientX + 'px';
    dot.style.top   = e.clientY + 'px';
    rx += (e.clientX - rx) * .12;
    ry += (e.clientY - ry) * .12;
    ring.style.left = rx + 'px';
    ring.style.top  = ry + 'px';
  });

  // Agrandissement au hover interactif
  document.querySelectorAll('a, button, .create-item, .navigation span').forEach(el => {
    el.addEventListener('mouseenter', () => ring.style.transform = 'translate(-50%,-50%) scale(1.6)');
    el.addEventListener('mouseleave', () => ring.style.transform = 'translate(-50%,-50%) scale(1)');
  });
})();

/* ── SLIDER ──────────────────────────────────────── */
(function() {
  const slides = document.querySelectorAll('.slide');
  const dots   = document.querySelectorAll('.slider-dot');
  let idx = 0, timer;

  function goTo(i) {
    slides[idx].classList.remove('active');
    dots[idx].classList.remove('active');
    idx = (i + slides.length) % slides.length;
    slides[idx].classList.add('active');
    dots[idx].classList.add('active');
  }

  function next() { goTo(idx + 1); }
  function prev() { goTo(idx - 1); }

  function autoStart() { timer = setInterval(next, 5000); }
  function autoStop()  { clearInterval(timer); }

  document.querySelector('.next').addEventListener('click', () => { autoStop(); next(); autoStart(); });
  document.querySelector('.prev').addEventListener('click', () => { autoStop(); prev(); autoStart(); });

  dots.forEach((d, i) => d.addEventListener('click', () => { autoStop(); goTo(i); autoStart(); }));

  autoStart();
})();

/* ── HEADER SCROLL ───────────────────────────────── */
(function() {
  const header = document.querySelector('.header');
  const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 60);
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();

/* ── SCROLL REVEAL ───────────────────────────────── */
(function() {
  const els = document.querySelectorAll('.reveal');
  const io  = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); }
    });
  }, { threshold: .12 });
  els.forEach(el => io.observe(el));
})();

/* ── SPARKLES AU CLIC ────────────────────────────── */
document.addEventListener('click', e => {
  for (let i = 0; i < 6; i++) {
    const s = document.createElement('div');
    s.className = 'sparkle';
    s.style.cssText = `
      left: ${e.clientX}px;
      top:  ${e.clientY}px;
      width:  ${4 + Math.random() * 6}px;
      height: ${4 + Math.random() * 6}px;
      animation-duration: ${.6 + Math.random() * .8}s;
      animation-delay: ${Math.random() * .2}s;
      transform: translate(${(Math.random()-0.5)*60}px, ${(Math.random()-0.5)*60}px);
    `;
    document.body.appendChild(s);
    setTimeout(() => s.remove(), 1400);
  }
});
</script>

<script src="<?= $b ?>/js/shop.js?v=<?= time() ?>"></script>
<script src="<?= $b ?>/js/checkout.js" defer></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.body.style.visibility = 'visible';
  });
</script>
<?php include 'includes/footer.php'; ?>
</body>
</html>