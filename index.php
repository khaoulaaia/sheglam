<?php
include_once 'includes/db.php';
include_once 'includes/config.php'; // BASE_URL défini ici
$b = BASE_URL;                       // raccourci
$stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC LIMIT 8");
$featured = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
  <link rel="stylesheet" href="<?= $b ?>/index.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $b ?>/sidebar.css?v=<?= time() ?>">
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
      <source media="(max-width:600px)" srcset="<?= $b ?>/images/4c37d3cda08609335217051381ede22f.jpg">
      <img src="<?= $b ?>/images/8d6e7ad9be45433f0f482c6672df7f4f.jpg" alt="Nouvelle collection">
    </picture>
  </div>

  <!-- SLIDE 2 -->
  <div class="slide">
    <picture>
      <source media="(max-width:600px)" srcset="<?= $b ?>/images/162f63d8d9bd46d89e65ee122a6cfb64.jpg">
      <img src="<?= $b ?>/images/17627683847b4f5dafc00e2b5d9b78b5921d37525e_thumbnail_3600x.webp" alt="Couleurs éclatantes">
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
      <source media="(max-width:600px)" srcset="<?= $b ?>/images/797394d7c83cc5b68638c1a76cebc1d9.jpg">
      <img src="<?= $b ?>/images/adea3c1ccd83ac8c0c1bca88cd01747d.jpg" alt="Makeup professionnel">
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
<section class="create-look-section reveal" 
  style="background-image: linear-gradient(158deg, rgba(240,235,224,.82) 0%, rgba(210,200,178,.88) 100%), url('<?= $b ?>/images/9b195786327e4840f5b28f9f42d9d6c7.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;"
>

  <div class="section-header" >
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
          <img src="<?= $b ?>/images/cafff133fa60bc2cacd2e3562f2a95fe.jpg" alt="Yeux" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.6;">
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
          <img src="<?= $b ?>/images/fb8e9e21c817a8cff37b67d55197d902.jpg" alt="Lèvres" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.6;">
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
          <img src="<?= $b ?>/images/bb8cc02eba2cacd02569a3f5abf6f6c9.jpg" alt="Teint" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.6;">
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
          <img src="<?= $b ?>/images/067700c35a774da3a234d1a731a29ba8.jpg" alt="Accessoires" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.6;">
        </div>
        <div class="create-overlay">
          <p class="create-name">Accessoires</p>
          <span class="create-cta">Explorer</span>
        </div>
      </a>
    </div>

  </div>
</section>
<section 
  class="featured-section" 
  id="featuredProducts"
  style="background-image: linear-gradient(158deg, rgba(240,235,224,.82) 0%, rgba(210,200,178,.88) 100%), url('<?= $b ?>/images/9b195786327e4840f5b28f9f42d9d6c7.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;"
>

  <!-- Fond texturé : vignette uniquement, plus de SVG grain -->
  <div class="featured-bg" aria-hidden="true">
    <div class="featured-vignette"></div>
  </div>

  <div class="featured-inner">

    <!-- En-tête section -->
    <div class="featured-header reveal">
      <span class="featured-eyebrow">Collection</span>
      <h2 class="featured-title">
        Nos <em>Essentiels</em>
      </h2>
      <p class="featured-subtitle">
        Des formules raffinées, pensées pour sublimer chaque teint.
      </p>
      <div class="featured-rule"></div>
    </div>

    <!-- Grille produits -->
    <?php if (empty($featured)): ?>
  <p class="featured-empty">Aucun produit disponible pour le moment.</p>
<?php else: ?>
<div class="featured-track-wrapper">
  <div class="featured-track reveal-up">
    <?php foreach ($featured as $i => $p):
      $img = $p['image_url']
        ? (str_starts_with($p['image_url'], 'http') ? $p['image_url'] : $b . '/images/' . basename($p['image_url']))
        : $b . '/images/placeholder.jpg';
      $price = number_format((float)$p['price'], 2, ',', ' ');
    ?>
    <article
      class="fp-card"
      style="animation-delay: <?= $i * 0.07 ?>s"
      data-product-id="<?= $p['id'] ?>"
      data-name="<?= htmlspecialchars($p['name']) ?>"
      data-price="<?= $p['price'] ?>"
      data-image_url="<?= htmlspecialchars($img) ?>"
    >
      <div class="fp-img-wrap">
        <a href="<?= $b ?>/product.php?id=<?= $p['id'] ?>">
          <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
        </a>
        <button
          class="fp-wishlist add-to-wishlist"
          aria-label="Ajouter à la wishlist"
          data-product-id="<?= $p['id'] ?>"
          data-name="<?= htmlspecialchars($p['name']) ?>"
          data-price="<?= $p['price'] ?>"
          data-image_url="<?= htmlspecialchars($img) ?>"
        >
          <i class="fa-regular fa-heart"></i>
        </button>
      </div><!-- /.fp-img-wrap -->

      <div class="fp-info">
        <h3 class="fp-name">
          <a href="<?= $b ?>/product.php?id=<?= $p['id'] ?>">
            <?= htmlspecialchars($p['name']) ?>
          </a>
        </h3>
        <span class="fp-price"><?= $price ?> DA</span>
        <div class="fp-actions add-to-cart-wrapper">
          <input type="number" name="quantity" value="1" min="1" class="fp-qty">
          <button
            class="fp-cart-btn add-to-cart"
            data-product-id="<?= $p['id'] ?>"
            data-name="<?= htmlspecialchars($p['name']) ?>"
            data-price="<?= $p['price'] ?>"
            data-image_url="<?= htmlspecialchars($img) ?>"
          >
            <i class="fa-solid fa-bag-shopping"></i>
            Ajouter
          </button>
        </div>
      </div><!-- /.fp-info -->

    </article>
    <?php endforeach; ?>
  </div><!-- /.featured-track -->
</div><!-- /.featured-track-wrapper -->
<?php endif; ?>

    <!-- Bouton Voir tout -->
    <div class="featured-footer reveal">
      <a href="<?= $b ?>/catalogue.php" class="featured-cta">
        <span>Voir toute la collection</span>
        <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

  </div><!-- /.featured-inner -->
</section>
<div class="divider"></div>

<!-- ══ WORTH THE HYPE ════════════════════════════════════ -->
<section class="worth-hype">
<div class="hype-left reveal reveal-left">
  <div class="hype-image-frame">

    <picture>
      <!-- Mobile -->
      <source
        media="(max-width: 768px)"
        srcset="<?= $b ?>/images/45433ff5723689bcfaba43d9eee85b48.jpg">

      <!-- Desktop -->
      <img
        src="<?= $b ?>/images/dd7c54ac45646f83d9ef7eaa56599363.jpg"
        alt="Best Seller">
    </picture>

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
<div class="divider"></div>

<!-- ══ LIVRAISON & CONFIANCE ══════════════════════════════ -->
<section class="trust-section">
  <div class="trust-inner">
    <div class="trust-item">
      <div class="trust-icon"><i class="fa-solid fa-truck-fast"></i></div>
      <p class="trust-title">Livraison rapide</p>
      <p class="trust-desc">Expédition sous 24h — livraison partout en Algérie en 2 à 5 jours ouvrés.</p>
    </div>
    <div class="trust-item">
      <div class="trust-icon"><i class="fa-solid fa-box-open"></i></div>
      <p class="trust-title">Emballage soigné</p>
      <p class="trust-desc">Chaque commande est préparée avec soin pour arriver en parfait état.</p>
    </div>
    <div class="trust-item">
      <div class="trust-icon"><i class="fa-brands fa-whatsapp"></i></div>
      <p class="trust-title">Service client</p>
      <p class="trust-desc">Une question ? Notre équipe vous répond 7j/7 via WhatsApp ou Instagram.</p>
    </div>
  </div>
</section>

<!-- ══ AVIS CLIENTS ═══════════════════════════════════════ -->
<section class="reviews-section">
  <div class="reviews-inner">

    <div class="reviews-header reveal">
      <span class="reviews-eyebrow">Témoignages</span>
      <h2 class="reviews-title">Elles nous font <em>confiance</em></h2>
      <div class="reviews-rule"></div>
    </div>

    <div class="reviews-counter reveal">
      <div class="counter-num">+1 000</div>
      <div>
        <div class="counter-stars">★★★★★</div>
        <div class="counter-label">Clientes satisfaites</div>
      </div>
    </div>

    <div class="reviews-grid reveal-up">

      <div class="review-card">
        <div class="review-stars">★★★★★</div>
        <p class="review-text">« La qualité est incroyable pour le prix. Mon rouge à lèvres tient toute la journée, je ne cherche plus ailleurs ! »</p>
        <div class="review-author">
          <div class="review-avatar">SB</div>
          <div>
            <p class="review-name">Sarah B.</p>
            <p class="review-date">Alger — il y a 3 jours</p>
          </div>
        </div>
      </div>

      <div class="review-card">
        <div class="review-stars">★★★★★</div>
        <p class="review-text">« Livraison ultra rapide et emballage soigné. Les produits sont exactement comme sur les photos, très satisfaite ! »</p>
        <div class="review-author">
          <div class="review-avatar">LM</div>
          <div>
            <p class="review-name">Lina M.</p>
            <p class="review-date">Oran — il y a 1 semaine</p>
          </div>
        </div>
      </div>

      <div class="review-card">
        <div class="review-stars">★★★★★</div>
        <p class="review-text">« Je commande régulièrement chez SheGlamour. Le fond de teint est parfait pour ma carnation, je le recommande à toutes ! »</p>
        <div class="review-author">
          <div class="review-avatar">NR</div>
          <div>
            <p class="review-name">Nour R.</p>
            <p class="review-date">Constantine — il y a 2 semaines</p>
          </div>
        </div>
      </div>

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