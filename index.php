<?php
include_once 'includes/config.php';
include_once 'includes/db.php';

$b = BASE_URL;

$stmt = $pdo->query("SELECT * FROM products WHERE old_price IS NOT NULL AND old_price > price ORDER BY id ASC LIMIT 8");
$featured = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtNew = $pdo->query("SELECT id, name, price, old_price, image_url, stock, categorie FROM products ORDER BY id DESC LIMIT 8");
$newProducts = $stmtNew->fetchAll(PDO::FETCH_ASSOC);

$stmtPacks = $pdo->query("SELECT * FROM products WHERE is_pack = TRUE ORDER BY id DESC LIMIT 8");
$packs = $stmtPacks->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>SheGlamour — Beauté & Luxe</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $b ?>/index.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= $b ?>/sidebar.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="icon" type="image/png" href="<?= $b ?>/images/logofib.png">
  <!-- Meta Pixel Code -->
  <script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '1496845578585995');
  fbq('track', 'PageView');
  </script>
  <noscript><img height="1" width="1" style="display:none"
  src="https://www.facebook.com/tr?id=1496845578585995&ev=PageView&noscript=1"
  /></noscript>
  <!-- End Meta Pixel Code -->
</head>
<body>

<div class="cursor-dot" id="cursorDot"></div>
<div class="cursor-ring" id="cursorRing"></div>

<?php include_once 'includes/sidebar.php'; ?>

<!-- ══ BANNIÈRE LIVRAISON GRATUITE (au-dessus du header) ═══ -->
<div class="livraison-banner" id="livraisonBanner">
  <div class="livraison-banner-inner">
    <span class="livraison-item">
      <i class="fa-solid fa-truck-fast" aria-hidden="true"></i>
      Livraison gratuite dès <strong>10 000 DA</strong>
    </span>
    <span class="livraison-sep" aria-hidden="true">·</span>
    
    <span class="livraison-sep" aria-hidden="true">·</span>
    <span class="livraison-item">
      <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
      Livraison dans les <strong>58 wilayas</strong>
    </span>
  </div>
</div>

<?php include 'includes/header.php'; ?>

<!-- ══ 1. HERO SLIDER ══════════════════════════════════════ -->
<section class="hero-slider">
  <div class="slide active">
    <picture>
      <source media="(max-width:600px)" srcset="<?= $b ?>/images/2ab4601fecaf19c74d1c3247c8699fc4.jpg">
      <img src="<?= $b ?>/images/c8d0308b-18a7-492f-9beb-72bcf33af240-1.png" alt="Nouvelle collection">
    </picture>
  </div>
</section>

<!-- ══ 2. NOUVEAUTÉS ═══════════════════════════════════════ -->
<section class="featured-section nouveautes-section" id="newProducts">
  <div class="featured-inner">

    <div class="featured-header reveal">
      <span class="featured-eyebrow">Dernières arrivées</span>
      <h2 class="featured-title">Nouveautés</h2>
      <div class="featured-rule"></div>
      <p class="featured-subtitle">Les pépites fraîchement ajoutées à notre boutique</p>
    </div>

    <?php if (empty($newProducts)): ?>
      <p class="featured-empty">Aucune nouveauté pour le moment.</p>
    <?php else: ?>

    <div class="featured-track-wrapper">
      <div class="featured-track reveal-up">
        <?php foreach ($newProducts as $i => $p):
          $img = $p['image_url']
            ? (str_starts_with($p['image_url'], 'http') ? $p['image_url'] : $b . '/images/' . basename($p['image_url']))
            : $b . '/images/placeholder.jpg';
          $price   = number_format((float)$p['price'], 2, ',', ' ');
          $stock   = (int)($p['stock'] ?? 1);
          $rupture = $stock === 0;
          $livGratuite = (float)$p['price'] >= 10000;

          $shadeStmt = $pdo->prepare("SELECT COUNT(*) FROM teintes WHERE product_id = ?");
          $shadeStmt->execute([$p['id']]);
          $hasShades = $shadeStmt->fetchColumn() > 0;
        ?>
        <article class="fp-card" style="animation-delay:<?= $i * 0.07 ?>s">
          <div class="fp-img-wrap">
            <a href="<?= $b ?>/product.php?id=<?= $p['id'] ?>">
              <img src="<?= htmlspecialchars($img) ?>"
                   alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
            </a>
            <span class="badge badge-new-drop">New</span>
            <?php if ($livGratuite): ?>
              <span class="badge-livgratuite">Livraison offerte</span>
            <?php endif; ?>
            <?php if ($rupture): ?>
              <span class="out-of-stock-badge">Rupture</span>
            <?php endif; ?>
            <button
              class="fp-wishlist add-to-wishlist"
              aria-label="Ajouter à la wishlist"
              data-product-id="<?= $p['id'] ?>"
              data-name="<?= htmlspecialchars($p['name']) ?>"
              data-price="<?= $p['price'] ?>"
              data-image_url="<?= htmlspecialchars($img) ?>"
            ><i class="fa-regular fa-heart"></i></button>
          </div>
          <div class="fp-info">
            <?php if (!empty($p['categorie'])): ?>
              <p class="product-category-label"><?= htmlspecialchars($p['categorie']) ?></p>
            <?php endif; ?>
            <h3 class="fp-name">
              <a href="<?= $b ?>/product.php?id=<?= $p['id'] ?>">
                <?= htmlspecialchars($p['name']) ?>
              </a>
            </h3>
            <div class="fp-price-wrap">
              <?php if (!empty($p['old_price']) && $p['old_price'] > $p['price']): ?>
                <span class="fp-old-price"><?= number_format((float)$p['old_price'], 2, ',', ' ') ?> DA</span>
                <span class="fp-price fp-price--sale"><?= $price ?> DA</span>
                <span class="badge-solde">-<?= round((1 - $p['price'] / $p['old_price']) * 100) ?>%</span>
              <?php else: ?>
                <span class="fp-price"><?= $price ?> DA</span>
              <?php endif; ?>
            </div>
            <div class="fp-actions add-to-cart-wrapper">
              <input type="number" name="quantity" value="1" min="1"
                     class="fp-qty" <?= $rupture ? 'disabled' : '' ?>>
              <?php if ($hasShades): ?>
                <button
                  class="fp-cart-btn choose-shade-btn"
                  data-product-id="<?= $p['id'] ?>"
                  data-name="<?= htmlspecialchars($p['name']) ?>"
                  data-price="<?= $p['price'] ?>"
                  data-image_url="<?= htmlspecialchars($img) ?>"
                  data-stock="<?= $stock ?>"
                  <?= $rupture ? 'disabled' : '' ?>
                ><i class="fa-solid fa-palette"></i>
                  <?= $rupture ? 'Rupture de stock' : 'Choisir une teinte' ?>
                </button>
              <?php else: ?>
                <button
                  class="fp-cart-btn add-to-cart"
                  data-product-id="<?= $p['id'] ?>"
                  data-name="<?= htmlspecialchars($p['name']) ?>"
                  data-price="<?= $p['price'] ?>"
                  data-image_url="<?= htmlspecialchars($img) ?>"
                  data-stock="<?= $stock ?>"
                  <?= $rupture ? 'disabled' : '' ?>
                ><i class="fa-solid fa-bag-shopping"></i>
                  <?= $rupture ? 'Rupture de stock' : 'Ajouter' ?>
                </button>
              <?php endif; ?>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>

    <?php endif; ?>

    <div class="featured-footer reveal">
      <a href="<?= $b ?>/categorie.php?categorie=Tous" class="featured-cta">
        <span>Voir toute la collection</span>
        <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

  </div>
</section>

<div class="divider"></div>

<!-- ══ 3. CRÉE TON LOOK ════════════════════════════════════ -->
<section class="create-look-section reveal">
  <div class="section-header">
    <h2 class="section-title">Crée ton Look</h2>
  </div>
  <div class="create-look">

    <div class="create-item reveal reveal-delay-1">
      <a href="<?= $b ?>/categorie.php?categorie=Yeux">
        <div class="create-item-bg">
          <img src="<?= $b ?>/images/cafff133fa60bc2cacd2e3562f2a95fe.jpg" alt="Yeux"
               style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
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
          <img src="<?= $b ?>/images/fb8e9e21c817a8cff37b67d55197d902.jpg" alt="Lèvres"
               style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
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
          <img src="<?= $b ?>/images/bb8cc02eba2cacd02569a3f5abf6f6c9.jpg" alt="Teint"
               style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
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
          <img src="<?= $b ?>/images/067700c35a774da3a234d1a731a29ba8.jpg" alt="Accessoires"
               style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
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

<!-- ══ 4. WORTH THE HYPE ══════════════════════════════════ -->
<section class="worth-hype">

  <div class="hype-left reveal reveal-left">
    <div class="hype-image-frame">
      <picture>
        <source media="(max-width: 768px)" srcset="<?= $b ?>/images/adea3c1ccd83ac8c0c1bca88cd01747d.jpg">
        <img src="<?= $b ?>/images/48cdd3f716df344a4ac66ec6e464eb77.jpg" alt="Best Seller">
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
      $hypeStmt = $pdo->query("SELECT * FROM products ORDER BY id ASC LIMIT 4");
      while ($product = $hypeStmt->fetch(PDO::FETCH_ASSOC)):
        $productId   = $product['id'];
        $stock       = (int)($product['stock'] ?? 1);
        $rupture     = $stock === 0;
        $livGratuite = (float)$product['price'] >= 10000;

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
            <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
          </a>
          <?php if ($livGratuite): ?>
            <span class="badge-livgratuite">Livraison offerte</span>
          <?php endif; ?>
          <?php if ($rupture): ?>
            <span class="out-of-stock-badge">Rupture</span>
          <?php endif; ?>
          <button
            class="add-to-wishlist"
            data-product-id="<?= $productId ?>"
            data-name="<?= htmlspecialchars($product['name']) ?>"
            data-price="<?= htmlspecialchars($product['price']) ?>"
            data-image_url="<?= htmlspecialchars($image) ?>"
            data-has-shades="<?= $hasShades ? 1 : 0 ?>">
            <i class="fas fa-heart"></i>
          </button>
        </div>

        <div class="product-info">
          <?php if (!empty($product['categorie'])): ?>
            <p class="product-category-label"><?= htmlspecialchars($product['categorie']) ?></p>
          <?php endif; ?>
          <h3>
            <a href="<?= $b ?>/product.php?id=<?= $productId ?>">
              <?= htmlspecialchars($product['name']) ?>
            </a>
          </h3>
          <div class="fp-price-wrap">
            <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
              <span class="fp-old-price"><?= number_format((float)$product['old_price'], 2, ',', ' ') ?> DA</span>
              <span class="fp-price fp-price--sale"><?= number_format((float)$product['price'], 2, ',', ' ') ?> DA</span>
              <span class="badge-solde">-<?= round((1 - $product['price'] / $product['old_price']) * 100) ?>%</span>
            <?php else: ?>
              <p class="price"><?= number_format($product['price'], 2, ',', ' ') ?> DA</p>
            <?php endif; ?>
          </div>

          <?php if ($hasShades): ?>
            <button
              class="choose-shade-btn"
              data-product-id="<?= $productId ?>"
              data-name="<?= htmlspecialchars($product['name']) ?>"
              data-price="<?= htmlspecialchars($product['price']) ?>"
              data-image_url="<?= htmlspecialchars($image) ?>"
              data-stock="<?= $stock ?>"
              <?= $rupture ? 'disabled' : '' ?>>
              <i class="fas fa-palette"></i>
              <?= $rupture ? 'Rupture de stock' : 'Choisir une teinte' ?>
            </button>
          <?php else: ?>
            <div class="add-to-cart-wrapper">
              <div class="quantity-wrapper">
                <input type="number" name="quantity" value="1" min="1" step="1"
                       <?= $rupture ? 'disabled' : '' ?>>
              </div>
              <button
                class="add-to-cart"
                data-product-id="<?= $productId ?>"
                data-name="<?= htmlspecialchars($product['name']) ?>"
                data-price="<?= htmlspecialchars($product['price']) ?>"
                data-image_url="<?= htmlspecialchars($image) ?>"
                data-stock="<?= $stock ?>"
                <?= $rupture ? 'disabled' : '' ?>>
                <i class="fas fa-shopping-bag"></i>
                <?= $rupture ? 'Rupture de stock' : 'Ajouter au panier' ?>
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

<!-- ══ 5. COFFRETS ════════════════════════════════════════ -->
<?php if (!empty($packs)): ?>
<section class="featured-section coffrets-section" id="coffrets">
  <div class="featured-inner">

    <div class="featured-header reveal">
      <span class="featured-eyebrow">Sélections exclusives</span>
      <h2 class="featured-title">Coffrets</h2>
      <div class="featured-rule"></div>
      <p class="featured-subtitle">Tout ce qu'il vous faut en un seul écrin</p>
    </div>

    <div class="featured-track-wrapper">
      <div class="featured-track reveal-up">

        <?php foreach ($packs as $i => $p):
          $img = $p['image_url']
            ? (str_starts_with($p['image_url'], 'http') ? $p['image_url'] : $b . '/images/' . basename($p['image_url']))
            : $b . '/images/placeholder.jpg';
          $price       = number_format((float)$p['price'], 2, ',', ' ');
          $stock       = (int)($p['stock'] ?? 1);
          $rupture     = $stock === 0;
          $livGratuite = (float)$p['price'] >= 10000;

          $shadeStmt = $pdo->prepare("SELECT COUNT(*) FROM teintes WHERE product_id = ?");
          $shadeStmt->execute([$p['id']]);
          $hasShades = $shadeStmt->fetchColumn() > 0;
        ?>

        <article class="fp-card coffret-card" style="animation-delay:<?= $i * 0.07 ?>s">
          <div class="fp-img-wrap">
            <a href="<?= $b ?>/product.php?id=<?= $p['id'] ?>">
              <img src="<?= htmlspecialchars($img) ?>"
                   alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
            </a>
            <span class="badge-coffret">Coffret</span>
            <?php if ($livGratuite): ?>
              <span class="badge-livgratuite">Livraison offerte</span>
            <?php endif; ?>
            <?php if ($rupture): ?>
              <span class="out-of-stock-badge">Rupture</span>
            <?php endif; ?>
            <button
              class="fp-wishlist add-to-wishlist"
              aria-label="Ajouter à la wishlist"
              data-product-id="<?= $p['id'] ?>"
              data-name="<?= htmlspecialchars($p['name']) ?>"
              data-price="<?= $p['price'] ?>"
              data-image_url="<?= htmlspecialchars($img) ?>"
            ><i class="fa-regular fa-heart"></i></button>
          </div>

          <div class="fp-info">
            <?php if (!empty($p['categorie'])): ?>
              <p class="product-category-label"><?= htmlspecialchars($p['categorie']) ?></p>
            <?php endif; ?>
            <h3 class="fp-name">
              <a href="<?= $b ?>/product.php?id=<?= $p['id'] ?>">
                <?= htmlspecialchars($p['name']) ?>
              </a>
            </h3>
            <div class="fp-price-wrap">
              <?php if (!empty($p['old_price']) && $p['old_price'] > $p['price']): ?>
                <span class="fp-old-price"><?= number_format((float)$p['old_price'], 2, ',', ' ') ?> DA</span>
                <span class="fp-price fp-price--sale"><?= $price ?> DA</span>
                <span class="badge-solde">-<?= round((1 - $p['price'] / $p['old_price']) * 100) ?>%</span>
              <?php else: ?>
                <span class="fp-price"><?= $price ?> DA</span>
              <?php endif; ?>
            </div>
            <div class="fp-actions add-to-cart-wrapper">
              <input type="number" name="quantity" value="1" min="1"
                     class="fp-qty" <?= $rupture ? 'disabled' : '' ?>>
              <?php if ($hasShades): ?>
                <button
                  class="fp-cart-btn choose-shade-btn"
                  data-product-id="<?= $p['id'] ?>"
                  data-name="<?= htmlspecialchars($p['name']) ?>"
                  data-price="<?= $p['price'] ?>"
                  data-image_url="<?= htmlspecialchars($img) ?>"
                  data-stock="<?= $stock ?>"
                  <?= $rupture ? 'disabled' : '' ?>
                ><i class="fa-solid fa-palette"></i>
                  <?= $rupture ? 'Rupture de stock' : 'Choisir une teinte' ?>
                </button>
              <?php else: ?>
                <button
                  class="fp-cart-btn add-to-cart"
                  data-product-id="<?= $p['id'] ?>"
                  data-name="<?= htmlspecialchars($p['name']) ?>"
                  data-price="<?= $p['price'] ?>"
                  data-image_url="<?= htmlspecialchars($img) ?>"
                  data-stock="<?= $stock ?>"
                  <?= $rupture ? 'disabled' : '' ?>
                ><i class="fa-solid fa-bag-shopping"></i>
                  <?= $rupture ? 'Rupture de stock' : 'Ajouter' ?>
                </button>
              <?php endif; ?>
            </div>
          </div>
        </article>

        <?php endforeach; ?>
      </div>
    </div>

    <div class="featured-footer reveal">
      <a href="<?= $b ?>/categorie.php?categorie=Tous&pack=1" class="featured-cta">
        <span>Voir tous les coffrets</span>
        <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

  </div>
</section>
<div class="divider"></div>
<?php endif; ?>

<!-- ══ 6. PROMOTIONS ══════════════════════════════════════ -->
<?php if (!empty($featured)): ?>
<section class="featured-section" id="featuredProducts">
  <div class="featured-inner">

    <div class="featured-header reveal">
      <span class="featured-eyebrow">Durée limitée</span>
      <h2 class="featured-title">Promotions</h2>
      <div class="featured-rule"></div>
      <p class="featured-subtitle">Nos offres en cours — profitez-en avant qu'il soit trop tard</p>
    </div>

    <div class="featured-track-wrapper">
      <div class="featured-track reveal-up">

        <?php foreach ($featured as $i => $p):
          $img = $p['image_url']
            ? (str_starts_with($p['image_url'], 'http') ? $p['image_url'] : $b . '/images/' . basename($p['image_url']))
            : $b . '/images/placeholder.jpg';
          $price       = number_format((float)$p['price'], 2, ',', ' ');
          $stock       = (int)($p['stock'] ?? 1);
          $rupture     = $stock === 0;
          $pct         = round((1 - $p['price'] / $p['old_price']) * 100);
          $livGratuite = (float)$p['price'] >= 10000;

          $shadeStmt = $pdo->prepare("SELECT COUNT(*) FROM teintes WHERE product_id = ?");
          $shadeStmt->execute([$p['id']]);
          $hasShades = $shadeStmt->fetchColumn() > 0;
        ?>

        <article class="fp-card" style="animation-delay:<?= $i * 0.07 ?>s">
          <div class="fp-img-wrap">
            <a href="<?= $b ?>/product.php?id=<?= $p['id'] ?>">
              <img src="<?= htmlspecialchars($img) ?>"
                   alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
            </a>
            <span class="badge-solde badge-solde--img">-<?= $pct ?>%</span>
            <?php if ($livGratuite): ?>
              <span class="badge-livgratuite">Livraison offerte</span>
            <?php endif; ?>
            <?php if ($rupture): ?>
              <span class="out-of-stock-badge">Rupture</span>
            <?php endif; ?>
            <button
              class="fp-wishlist add-to-wishlist"
              aria-label="Ajouter à la wishlist"
              data-product-id="<?= $p['id'] ?>"
              data-name="<?= htmlspecialchars($p['name']) ?>"
              data-price="<?= $p['price'] ?>"
              data-image_url="<?= htmlspecialchars($img) ?>"
            ><i class="fa-regular fa-heart"></i></button>
          </div>

          <div class="fp-info">
            <?php if (!empty($p['categorie'])): ?>
              <p class="product-category-label"><?= htmlspecialchars($p['categorie']) ?></p>
            <?php endif; ?>
            <h3 class="fp-name">
              <a href="<?= $b ?>/product.php?id=<?= $p['id'] ?>">
                <?= htmlspecialchars($p['name']) ?>
              </a>
            </h3>
            <div class="fp-price-wrap">
              <span class="fp-old-price"><?= number_format((float)$p['old_price'], 2, ',', ' ') ?> DA</span>
              <span class="fp-price fp-price--sale"><?= $price ?> DA</span>
            </div>
            <div class="fp-actions add-to-cart-wrapper">
              <input type="number" name="quantity" value="1" min="1"
                     class="fp-qty" <?= $rupture ? 'disabled' : '' ?>>
              <?php if ($hasShades): ?>
                <button
                  class="fp-cart-btn choose-shade-btn"
                  data-product-id="<?= $p['id'] ?>"
                  data-name="<?= htmlspecialchars($p['name']) ?>"
                  data-price="<?= $p['price'] ?>"
                  data-image_url="<?= htmlspecialchars($img) ?>"
                  data-stock="<?= $stock ?>"
                  <?= $rupture ? 'disabled' : '' ?>
                ><i class="fa-solid fa-palette"></i>
                  <?= $rupture ? 'Rupture de stock' : 'Choisir une teinte' ?>
                </button>
              <?php else: ?>
                <button
                  class="fp-cart-btn add-to-cart"
                  data-product-id="<?= $p['id'] ?>"
                  data-name="<?= htmlspecialchars($p['name']) ?>"
                  data-price="<?= $p['price'] ?>"
                  data-image_url="<?= htmlspecialchars($img) ?>"
                  data-stock="<?= $stock ?>"
                  <?= $rupture ? 'disabled' : '' ?>
                ><i class="fa-solid fa-bag-shopping"></i>
                  <?= $rupture ? 'Rupture de stock' : 'Ajouter' ?>
                </button>
              <?php endif; ?>
            </div>
          </div>
        </article>

        <?php endforeach; ?>
      </div>
    </div>

    <div class="featured-footer reveal">
      <a href="<?= $b ?>/categorie.php?categorie=Tous&soldes=1" class="featured-cta">
        <span>Voir toutes les promos</span>
        <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

  </div>
</section>
<div class="divider"></div>
<?php endif; ?>

<!-- ══ 7. TRUST ════════════════════════════════════════════ -->
<section class="trust-section">
  <div class="trust-inner">
    <div class="trust-item">
      <div class="trust-icon"><i class="fa-solid fa-truck-fast" aria-hidden="true"></i></div>
      <p class="trust-title">Livraison rapide</p>
      <p class="trust-desc">Expédition sous 24h — livraison dans les <strong>58 wilayas</strong> en 2 à 5 jours ouvrés.</p>
    </div>
    <div class="trust-item">
      <div class="trust-icon"><i class="fa-solid fa-money-bill-wave" aria-hidden="true"></i></div>
      <p class="trust-title">Paiement à la livraison</p>
      <p class="trust-desc">Vous payez uniquement à la réception. Zéro avance, zéro risque.</p>
    </div>
    <div class="trust-item">
      <div class="trust-icon"><i class="fa-solid fa-box-open" aria-hidden="true"></i></div>
      <p class="trust-title">Emballage soigné</p>
      <p class="trust-desc">Chaque commande préparée avec soin pour arriver en parfait état.</p>
    </div>
    <div class="trust-item">
      <div class="trust-icon"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></div>
      <p class="trust-title">Service client 7j/7</p>
      <p class="trust-desc">Une question ? Notre équipe vous répond via WhatsApp ou Instagram.</p>
    </div>
  </div>
</section>

<!-- ══ 8. AVIS CLIENTS ════════════════════════════════════ -->
<section class="reviews-section">
  <div class="reviews-inner">

    <div class="reviews-header reveal">
      <span class="reviews-eyebrow">Témoignages</span>
      <h2 class="reviews-title">Elles nous font <em>confiance</em></h2>
      <div class="reviews-rule"></div>
    </div>

    <div class="reviews-counter reveal">
      <div class="counter-num" id="satisfiedCounter">0</div>
      <div>
        <div class="counter-stars">★★★★★</div>
        <div class="counter-label">Clientes satisfaites</div>
      </div>
    </div>

    <div class="reviews-grid reveal-up">

      <div class="review-card">
        <div class="review-stars">★★★★★</div>
        <p class="review-text">« Wallah la qualité c'est pas à discuter ! Mon rouge à lèvres tient toute la journée même après le café, je commande plus qu'ici ! »</p>
        <div class="review-author">
          <div class="review-avatar">SB</div>
          <div>
            <p class="review-name">Sara B.</p>
            <p class="review-date">Alger</p>
          </div>
        </div>
      </div>

      <div class="review-card">
        <div class="review-stars">★★★★★</div>
        <p class="review-text">« La livraison a arrivé super vite et l'emballage trop beau, on dirait un cadeau ! Les produits exactement comme les photos, ana radia a 100% ! »</p>
        <div class="review-author">
          <div class="review-avatar">LM</div>
          <div>
            <p class="review-name">Lina M.</p>
            <p class="review-date">Oran</p>
          </div>
        </div>
      </div>

      <div class="review-card">
        <div class="review-stars">★★★★★</div>
        <p class="review-text">« Ncommandi dima de chez SheGlamour, le fond de teint machi kima les autres rebi yewafaq. Nensah bikoum ! »</p>
        <div class="review-author">
          <div class="review-avatar">NR</div>
          <div>
            <p class="review-name">Nour R.</p>
            <p class="review-date">Constantine</p>
          </div>
        </div>
      </div>

      <div class="review-card">
        <div class="review-stars">★★★★★</div>
        <p class="review-text">« J'avais peur de commander en ligne mais SheGlamour m'a convaincue, le mascara c'est le meilleur que j'ai testé ! »</p>
        <div class="review-author">
          <div class="review-avatar">AM</div>
          <div>
            <p class="review-name">Amira M.</p>
            <p class="review-date">Annaba</p>
          </div>
        </div>
      </div>

      <div class="review-card">
        <div class="review-stars">★★★★★</div>
        <p class="review-text">« Les prix raisonnables et la qualité premium, on trouve pas ça ailleurs en Algérie. Le gloss tient bien et la couleur trop jolie ! »</p>
        <div class="review-author">
          <div class="review-avatar">RK</div>
          <div>
            <p class="review-name">Rania K.</p>
            <p class="review-date">Blida</p>
          </div>
        </div>
      </div>

      <div class="review-card">
        <div class="review-stars">★★★★★</div>
        <p class="review-text">« Commande arrivée en 2 jours à Tizi, super rapide yatikom saha »</p>
        <div class="review-author">
          <div class="review-avatar">DH</div>
          <div>
            <p class="review-name">Djamila H.</p>
            <p class="review-date">Tizi Ouzou</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ══ WHATSAPP FLOTTANT ═══════════════════════════════════ -->
<a href="https://wa.me/213XXXXXXXXX?text=Bonjour%2C%20j%27ai%20une%20question%20sur%20ma%20commande"
   class="whatsapp-float"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="Contacter via WhatsApp">
  <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
</a>

<?php include 'includes/product_modal.php'; ?>

<script>
(function() {
  const dot  = document.getElementById('cursorDot');
  const ring = document.getElementById('cursorRing');
  if (!dot || !ring) return;
  let rx = 0, ry = 0;
  document.addEventListener('mousemove', e => {
    dot.style.left  = e.clientX + 'px';
    dot.style.top   = e.clientY + 'px';
    rx += (e.clientX - rx) * .12;
    ry += (e.clientY - ry) * .12;
    ring.style.left = rx + 'px';
    ring.style.top  = ry + 'px';
  });
  document.querySelectorAll('a, button, .create-item, .navigation span').forEach(el => {
    el.addEventListener('mouseenter', () => ring.style.transform = 'translate(-50%,-50%) scale(1.6)');
    el.addEventListener('mouseleave', () => ring.style.transform = 'translate(-50%,-50%) scale(1)');
  });
})();

(function() {
  const slides  = document.querySelectorAll('.slide');
  const dots    = document.querySelectorAll('.slider-dot');
  const nextBtn = document.querySelector('.next');
  const prevBtn = document.querySelector('.prev');
  if (slides.length < 2) return;
  let idx = 0, timer;
  function goTo(i) {
    slides[idx].classList.remove('active');
    if (dots[idx]) dots[idx].classList.remove('active');
    idx = (i + slides.length) % slides.length;
    slides[idx].classList.add('active');
    if (dots[idx]) dots[idx].classList.add('active');
  }
  const next = () => goTo(idx + 1);
  const prev = () => goTo(idx - 1);
  const autoStart = () => { timer = setInterval(next, 5000); };
  const autoStop  = () => { clearInterval(timer); };
  nextBtn?.addEventListener('click', () => { autoStop(); next(); autoStart(); });
  prevBtn?.addEventListener('click', () => { autoStop(); prev(); autoStart(); });
  dots.forEach((d, i) => d.addEventListener('click', () => { autoStop(); goTo(i); autoStart(); }));
  autoStart();
})();

(function() {
  const header     = document.querySelector('.header');
  const headerLogo = document.getElementById('headerLogo');
  const banner     = document.getElementById('livraisonBanner');
  const onScroll   = () => {
    const scrolled = window.scrollY > 60;
    header.classList.toggle('scrolled', scrolled);
    if (banner) banner.classList.toggle('hidden', scrolled);
    if (headerLogo) headerLogo.src = scrolled ? '/images/logofib.png' : '/images/logowhite.png';
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();

(function() {
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); }
    });
  }, { threshold: .12 });
  document.querySelectorAll('.reveal').forEach(el => io.observe(el));
})();

document.addEventListener('click', e => {
  for (let i = 0; i < 6; i++) {
    const s = document.createElement('div');
    s.className = 'sparkle';
    s.style.cssText = `
      left:${e.clientX}px;top:${e.clientY}px;
      width:${4+Math.random()*6}px;height:${4+Math.random()*6}px;
      animation-duration:${.6+Math.random()*.8}s;
      animation-delay:${Math.random()*.2}s;
      transform:translate(${(Math.random()-.5)*60}px,${(Math.random()-.5)*60}px);
    `;
    document.body.appendChild(s);
    setTimeout(() => s.remove(), 1400);
  }
});

(function() {
  const el = document.getElementById('satisfiedCounter');
  if (!el) return;
  const target = 1247, dur = 2000, step = 16;
  const inc    = Math.ceil(target / (dur / step));
  let current  = 0;
  const io = new IntersectionObserver(entries => {
    if (!entries[0].isIntersecting) return;
    io.disconnect();
    const timer = setInterval(() => {
      current = Math.min(current + inc, target);
      el.textContent = '+' + current.toLocaleString('fr-DZ');
      if (current >= target) clearInterval(timer);
    }, step);
  }, { threshold: 0.4 });
  io.observe(el);
})();
</script>

<script>const BASE_URL = "<?= $b ?>";</script>
<script src="<?= $b ?>/js/shop.js?v=<?= time() ?>"></script>
<script src="/js/checkout.js?v=2" defer></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.body.style.visibility = 'visible';
  });
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>