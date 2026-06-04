<?php
include 'includes/db.php';
include_once 'includes/config.php';
$b = BASE_URL;

$productId = $_GET['id'] ?? null;
if (!$productId) die("Produit introuvable");

// ── Traitement avis ──────────────────────────────────────────────────────────
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

// ── Produit ──────────────────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) die("Produit introuvable");

$stock        = (int)($product['stock'] ?? 0);
$outOfStock   = $stock === 0;
$basePrice    = (float)$product['price'];
$baseOldPrice = (!empty($product['old_price']) && $product['old_price'] > $product['price'])
              ? (float)$product['old_price'] : null;

// ── Images produit ───────────────────────────────────────────────────────────
$imageStmt = $pdo->prepare("SELECT image FROM product_images WHERE product_id = ?");
$imageStmt->execute([$productId]);
$additionalImages = $imageStmt->fetchAll(PDO::FETCH_COLUMN);

if (!empty($product['image_url'])) {
    array_unshift($additionalImages, $product['image_url']);
}
if (empty($additionalImages)) {
    $additionalImages = [$b . '/images/placeholder.jpg'];
}
$additionalImages = array_values(array_unique($additionalImages));
$additionalImages = array_map(function ($img) use ($b) {
    if (empty($img)) return $b . '/images/placeholder.jpg';
    return str_starts_with($img, 'http') ? $img : $b . '/images/' . basename($img);
}, $additionalImages);

// ── Teintes ──────────────────────────────────────────────────────────────────
$shadeStmt = $pdo->prepare("
    SELECT id, nom_teinte, code_couleur,
           COALESCE(prix, 0)  AS prix,
           COALESCE(stock, 0) AS stock,
           image
    FROM teintes
    WHERE product_id = ?
    ORDER BY id ASC
");
$shadeStmt->execute([$productId]);
$shades    = $shadeStmt->fetchAll(PDO::FETCH_ASSOC);
$hasShades = count($shades) > 0;

foreach ($shades as &$s) {
    $s['prix']  = (float)$s['prix'];
    $s['stock'] = (int)$s['stock'];
    if (!empty($s['image'])) {
        $s['image_url'] = str_starts_with($s['image'], 'http')
            ? $s['image']
            : $b . '/images/' . basename($s['image']);
    } else {
        $s['image_url'] = '';
    }
}
unset($s);

// ── Avis ─────────────────────────────────────────────────────────────────────
$reviewStmt = $pdo->prepare("SELECT * FROM product_reviews WHERE product_id = ? ORDER BY date_creation DESC");
$reviewStmt->execute([$productId]);
$reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);

$averageRating = 0;
if ($reviews) {
    $averageRating = round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1);
}

// ── Produits similaires ───────────────────────────────────────────────────────
$similarStmt = $pdo->prepare("SELECT * FROM products WHERE categorie = ? AND id != ? LIMIT 4");
$similarStmt->execute([$product['categorie'], $productId]);
$similarProducts = $similarStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($product['name']) ?> - SheGlamour</title>
  <link rel="stylesheet" href="<?= $b ?>/product.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= $b ?>/sidebar.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="icon" type="image/png" href="<?= $b ?>/images/logofib.png">
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

<!-- ════════════════════════════════════════════════════════════
     PAGE PRODUIT — LAYOUT 2 COLONNES
═════════════════════════════════════════════════════════════ -->
<section class="product-page">

  <!-- ── COLONNE GAUCHE : galerie ─────────────────────────── -->
  <div class="product-gallery-col">

    <div class="gallery-main-wrap">
      <?php if ($outOfStock && !$hasShades): ?>
        <div class="oos-ribbon"><span>Rupture de stock</span></div>
      <?php endif; ?>

      <?php if ($baseOldPrice): ?>
        <?php $pct = round((1 - $basePrice / $baseOldPrice) * 100); ?>
        <div class="promo-pill">-<?= $pct ?>%</div>
      <?php endif; ?>

      <div class="gallery-main-inner" id="galleryMainInner">
        <img id="mainImage"
             src="<?= htmlspecialchars($additionalImages[0]) ?>"
             alt="<?= htmlspecialchars($product['name']) ?>"
             class="gallery-main-img <?= ($outOfStock && !$hasShades) ? 'img-out-of-stock' : '' ?>">
        <div class="zoom-hint"><i class="fas fa-search-plus"></i></div>
      </div>

      <div class="gallery-dots" id="galleryDots">
        <?php foreach ($additionalImages as $i => $img): ?>
          <button class="gdot <?= $i === 0 ? 'active' : '' ?>" data-idx="<?= $i ?>"></button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Strip miniatures -->
    <div class="gallery-strip" id="thumbnailStrip">
      <?php foreach ($additionalImages as $i => $img): ?>
        <button class="thumb-btn <?= $i === 0 ? 'active' : '' ?>"
                data-src="<?= htmlspecialchars($img) ?>"
                data-idx="<?= $i ?>"
                style="--delay:<?= $i * 0.06 ?>s">
          <img src="<?= htmlspecialchars($img) ?>"
               alt="<?= htmlspecialchars($product['name']) ?> vue <?= $i + 1 ?>">
          <span class="thumb-overlay"></span>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- Accordéon description -->
    <div class="desc-accordion" id="descAccordion">
      <button class="acc-trigger" id="accTrigger" aria-expanded="false">
        <span class="acc-trigger-label">
          <i class="fas fa-align-left"></i>
          Description du produit
        </span>
        <span class="acc-trigger-icon">
          <i class="fas fa-chevron-down"></i>
        </span>
      </button>
      <div class="acc-panel" id="accPanel" role="region" aria-labelledby="accTrigger">
        <div class="acc-panel-inner">
          <div class="acc-desc-text">
            <?= nl2br(htmlspecialchars($product['description'] ?? 'Aucune description disponible.')) ?>
          </div>
          <?php if ($product['marque']): ?>
          <div class="acc-meta-row">
            <span class="acc-meta-label"><i class="fas fa-tag"></i> Marque</span>
            <span class="acc-meta-value"><?= htmlspecialchars($product['marque']) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($product['categorie']): ?>
          <div class="acc-meta-row">
            <span class="acc-meta-label"><i class="fas fa-folder"></i> Catégorie</span>
            <span class="acc-meta-value"><?= htmlspecialchars($product['categorie']) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($product['sous_categorie']): ?>
          <div class="acc-meta-row">
            <span class="acc-meta-label"><i class="fas fa-layer-group"></i> Sous-catégorie</span>
            <span class="acc-meta-value"><?= htmlspecialchars($product['sous_categorie']) ?></span>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div><!-- /.product-gallery-col -->

  <!-- ── COLONNE DROITE : infos produit ───────────────────── -->
  <div class="product-info-col">

    <?php if ($product['categorie']): ?>
    <p class="info-category-tag">
      <a href="<?= $b ?>/categorie.php?categorie=<?= urlencode($product['categorie']) ?>">
        <?= htmlspecialchars($product['categorie']) ?>
      </a>
    </p>
    <?php endif; ?>

    <h1 class="info-name"><?= htmlspecialchars($product['name']) ?></h1>

    <!-- Étoiles & avis -->
    <div class="info-rating-row">
      <?php if ($reviews): ?>
        <div class="star-row">
          <?php for ($i = 1; $i <= 5; $i++):
            if ($i <= floor($averageRating))      echo '<i class="fas fa-star"></i>';
            elseif ($i - $averageRating <= 0.5)   echo '<i class="fas fa-star-half-alt"></i>';
            else                                   echo '<i class="far fa-star"></i>';
          endfor; ?>
        </div>
        <span class="rating-count"><?= $averageRating ?>/5 &mdash; <?= count($reviews) ?> avis</span>
      <?php else: ?>
        <span class="no-reviews-tag">Soyez la première à laisser un avis</span>
      <?php endif; ?>
    </div>

    <div class="info-divider"></div>

    <!-- Prix -->
    <div class="info-price-block" id="priceBlock">
      <?php if ($baseOldPrice): ?>
        <span class="price-old" id="oldPriceEl"><?= number_format($baseOldPrice, 2, ',', ' ') ?> DA</span>
      <?php else: ?>
        <span class="price-old" id="oldPriceEl" style="display:none"></span>
      <?php endif; ?>
      <span class="price-current" id="currentPriceEl"><?= number_format($basePrice, 2, ',', ' ') ?> DA</span>
    </div>

    <!-- Badge stock -->
    <div class="info-stock-badge <?= $hasShades ? 'shade-pending' : ($outOfStock ? 'stock-out' : ($stock <= 5 ? 'stock-low' : 'stock-in')) ?>"
         id="stockBadge">
      <?php if ($hasShades): ?>
        <i class="fas fa-palette"></i><span>Choisissez une teinte</span>
      <?php elseif ($outOfStock): ?>
        <i class="fas fa-times-circle"></i><span>Rupture de stock</span>
      <?php elseif ($stock <= 5): ?>
        <i class="fas fa-fire"></i><span>Plus que <?= $stock ?> en stock !</span>
      <?php else: ?>
        <i class="fas fa-check-circle"></i><span>En stock</span>
      <?php endif; ?>
    </div>

    <!-- ══ SÉLECTEUR DE TEINTES ══ -->
    <?php if ($hasShades): ?>
    <div class="shade-section" id="shadeSelectorBlock">
      <p class="shade-section-label">
        Teinte sélectionnée :
        <strong id="selectedShadeName">—</strong>
      </p>
      <div class="shade-grid" id="shadeDots">
        <?php foreach ($shades as $s): ?>
          <button class="shade-item"
                  data-nom="<?= htmlspecialchars($s['nom_teinte']) ?>"
                  data-prix="<?= $s['prix'] ?>"
                  data-stock="<?= $s['stock'] ?>"
                  data-image_url="<?= htmlspecialchars($s['image_url']) ?>"
                  data-code_couleur="<?= htmlspecialchars($s['code_couleur'] ?? '') ?>"
                  title="<?= htmlspecialchars($s['nom_teinte']) ?>">
            <?php if (!empty($s['image_url'])): ?>
              <span class="shade-swatch shade-swatch--img">
                <img src="<?= htmlspecialchars($s['image_url']) ?>"
                     alt="<?= htmlspecialchars($s['nom_teinte']) ?>">
              </span>
            <?php else: ?>
              <span class="shade-swatch shade-swatch--color"
                    style="background:<?= htmlspecialchars($s['code_couleur'] ?? '#ccc') ?>">
              </span>
            <?php endif; ?>
            <span class="shade-item-name"><?= htmlspecialchars($s['nom_teinte']) ?></span>
            <?php if ($s['stock'] === 0): ?>
              <span class="shade-oos-mark"><i class="fas fa-times"></i></span>
            <?php endif; ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- ══ ACTIONS ══ -->
    <div class="product-actions">

      <!-- Quantité -->
      <div class="qty-row">
        <label class="qty-label">Quantité</label>
        <div class="qty-control">
          <button type="button" class="qty-btn" id="qtyMinus"><i class="fas fa-minus"></i></button>
          <input type="number" id="quantity" value="1" min="1"
                 max="<?= $hasShades ? 1 : $stock ?>"
                 <?= ($outOfStock && !$hasShades) ? 'disabled' : '' ?>>
          <button type="button" class="qty-btn" id="qtyPlus"><i class="fas fa-plus"></i></button>
        </div>
      </div>

      <!-- Boutons -->
      <div class="action-btns">
        <?php if ($hasShades): ?>
          <!-- Bouton AVEC teinte -->
          <button id="addWithShadeBtn"
                  class="btn-add-cart"
                  data-product-id="<?= $product['id'] ?>"
                  data-name="<?= htmlspecialchars($product['name']) ?>"
                  data-price="<?= $basePrice ?>"
                  data-image_url="<?= htmlspecialchars($additionalImages[0]) ?>"
                  disabled>
            <i class="fas fa-palette"></i>
            <span>Choisissez une teinte</span>
          </button>
        <?php else: ?>
          <!-- Bouton SANS teinte — CORRECTION : classe add-to-cart ajoutée -->
          <button class="btn-add-cart add-to-cart"
                  data-product-id="<?= $product['id'] ?>"
                  data-name="<?= htmlspecialchars($product['name']) ?>"
                  data-price="<?= $basePrice ?>"
                  data-image_url="<?= htmlspecialchars($additionalImages[0]) ?>"
                  data-stock="<?= $stock ?>"
                  <?= $outOfStock ? 'disabled' : '' ?>>
            <i class="fas fa-<?= $outOfStock ? 'ban' : 'shopping-bag' ?>"></i>
            <span><?= $outOfStock ? 'Rupture de stock' : 'Ajouter au panier' ?></span>
          </button>
        <?php endif; ?>

        <button class="btn-buy-now" id="buyNowBtn"
                data-product-id="<?= $product['id'] ?>"
                data-name="<?= htmlspecialchars($product['name']) ?>"
                data-price="<?= $basePrice ?>"
                data-image_url="<?= htmlspecialchars($additionalImages[0]) ?>"
                <?= ($outOfStock && !$hasShades) ? 'disabled' : '' ?>>
          <i class="fas fa-bolt"></i>
          <span>Acheter maintenant</span>
        </button>
      </div>

    </div><!-- /.product-actions -->

    <!-- Garanties -->
    <div class="trust-row">
      <div class="trust-item">
        <i class="fas fa-shield-alt"></i>
        <span>Paiement sécurisé</span>
      </div>
      <div class="trust-item">
        <i class="fas fa-truck"></i>
        <span>Livraison rapide</span>
      </div>
      <div class="trust-item">
        <i class="fas fa-undo"></i>
        <span>Retour facile</span>
      </div>
    </div>

    <!-- Partage -->
    <div class="share-product">
      <span>Partager</span>
      <a href="#" class="share-btn" data-network="facebook"  title="Facebook"><i class="fab fa-facebook-f"></i></a>
      <a href="#" class="share-btn" data-network="twitter"   title="Twitter"><i class="fab fa-x-twitter"></i></a>
      <a href="#" class="share-btn" data-network="whatsapp"  title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
      <a href="#" class="share-btn" data-network="pinterest" title="Pinterest"><i class="fab fa-pinterest-p"></i></a>
      <button id="nativeShare" class="share-btn" title="Plus"><i class="fas fa-share-alt"></i></button>
    </div>

  </div><!-- /.product-info-col -->

</section><!-- /.product-page -->

<!-- ════════════════════════════════════════════════════════════
     AVIS
═════════════════════════════════════════════════════════════ -->
<section class="reviews-section">
  <div class="reviews-inner">

    <div class="review-form-wrap">
      <h3 class="section-title">Laisser un avis</h3>
      <form method="POST" class="review-form">
        <input type="hidden" name="product_id" value="<?= $productId ?>">

        <div class="form-row">
          <label>Votre nom</label>
          <input type="text" name="customer_name" required placeholder="ex : Sarah M.">
        </div>

        <div class="form-row">
          <label>Votre note</label>
          <div class="stars-input" id="starRating">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <i class="fa-regular fa-star" data-value="<?= $i ?>"></i>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="rating" id="ratingInput" required>
        </div>

        <div class="form-row">
          <label>Votre avis</label>
          <textarea name="comment" rows="4" required placeholder="Partagez votre expérience…"></textarea>
        </div>

        <button type="submit" name="submit_review" class="btn-review-submit">
          <i class="fas fa-paper-plane"></i> Envoyer
        </button>
      </form>
    </div>

    <?php if ($reviews): ?>
    <div class="review-list-wrap">
      <h3 class="section-title">
        Ce que disent nos clientes
        <span class="review-avg-pill"><?= $averageRating ?> / 5</span>
      </h3>
      <div class="review-list">
        <?php foreach ($reviews as $review): ?>
        <div class="review-card">
          <div class="review-card-header">
            <div class="reviewer-avatar">
              <?= strtoupper(mb_substr($review['customer_name'], 0, 1)) ?>
            </div>
            <div class="reviewer-meta">
              <strong><?= htmlspecialchars($review['customer_name']) ?></strong>
              <div class="review-stars-small">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <i class="fa<?= $i <= $review['rating'] ? 's' : 'r' ?> fa-star"></i>
                <?php endfor; ?>
              </div>
            </div>
            <time class="review-date"><?= date('d/m/Y', strtotime($review['date_creation'])) ?></time>
          </div>
          <p class="review-body"><?= htmlspecialchars($review['comment']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- ════════════════════════════════════════════════════════════
     PRODUITS SIMILAIRES
═════════════════════════════════════════════════════════════ -->
<?php if ($similarProducts): ?>
<section class="similar-products">
  <h2 class="section-title centered">
    Vous aimerez aussi
    <span class="title-deco"></span>
  </h2>
  <div class="similar-grid">
    <?php foreach ($similarProducts as $sp):
      $spImg  = empty($sp['image_url'])
              ? $b . '/images/placeholder.jpg'
              : (str_starts_with($sp['image_url'], 'http')
                 ? $sp['image_url']
                 : $b . '/images/' . basename($sp['image_url']));
      $spStock = (int)($sp['stock'] ?? 0);
      $spOos   = $spStock === 0;
    ?>
      <a href="<?= $b ?>/product.php?id=<?= $sp['id'] ?>" class="similar-card <?= $spOos ? 'oos' : '' ?>">
        <?php if ($spOos): ?>
          <span class="sim-oos-badge">Rupture</span>
        <?php endif; ?>
        <div class="sim-img-wrap">
          <img src="<?= htmlspecialchars($spImg) ?>"
               alt="<?= htmlspecialchars($sp['name']) ?>"
               class="<?= $spOos ? 'img-out-of-stock' : '' ?>">
        </div>
        <div class="sim-info">
          <h4><?= htmlspecialchars($sp['name']) ?></h4>
          <p class="sim-price"><?= number_format($sp['price'], 2, ',', ' ') ?> DA</p>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php include 'includes/product_modal.php'; ?>

<script>
/* ═══════════════════════════════════════════════════════════
   CONSTANTES PHP → JS
══════════════════════════════════════════════════════════ */
const BASE_URL       = <?= json_encode($b) ?>;
const BASE_PRICE     = <?= json_encode($basePrice) ?>;
const BASE_OLD_PRICE = <?= json_encode($baseOldPrice) ?>;
const BASE_IMAGE     = <?= json_encode($additionalImages[0]) ?>;

/* ═══════════════════════════════════════════════════════════
   GALERIE — image principale
══════════════════════════════════════════════════════════ */
const mainImg   = document.getElementById('mainImage');
const thumbBtns = document.querySelectorAll('#thumbnailStrip .thumb-btn');
const gdots     = document.querySelectorAll('#galleryDots .gdot');

mainImg.style.transition = 'opacity .18s ease';

function setMainImage(url, idx) {
  if (!url) return;
  mainImg.style.opacity = '0';
  setTimeout(() => {
    mainImg.src          = url;
    mainImg.style.opacity = '1';
  }, 180);
  thumbBtns.forEach((t, i) => t.classList.toggle('active', i === idx));
  gdots.forEach((d, i)     => d.classList.toggle('active', i === idx));
}

thumbBtns.forEach((btn, i) => {
  btn.addEventListener('click', () => setMainImage(btn.dataset.src, i));
});
gdots.forEach((d, i) => {
  d.addEventListener('click', () => {
    const btn = thumbBtns[i];
    if (btn) setMainImage(btn.dataset.src, i);
  });
});

/* ═══════════════════════════════════════════════════════════
   ACCORDÉON DESCRIPTION
══════════════════════════════════════════════════════════ */
const accTrigger = document.getElementById('accTrigger');
const accPanel   = document.getElementById('accPanel');

accTrigger.addEventListener('click', () => {
  const expanded = accTrigger.getAttribute('aria-expanded') === 'true';
  accTrigger.setAttribute('aria-expanded', String(!expanded));
  accPanel.style.maxHeight = expanded ? '0' : accPanel.scrollHeight + 'px';
  accTrigger.classList.toggle('open', !expanded);
});

/* ═══════════════════════════════════════════════════════════
   HELPERS PRIX / STOCK
══════════════════════════════════════════════════════════ */
function setPrice(price, oldPrice) {
  const cur = document.getElementById('currentPriceEl');
  const old = document.getElementById('oldPriceEl');
  if (cur) cur.textContent = price.toLocaleString('fr-DZ', { minimumFractionDigits: 2 }) + ' DA';
  if (old) {
    if (oldPrice && oldPrice > price) {
      old.textContent    = oldPrice.toLocaleString('fr-DZ', { minimumFractionDigits: 2 }) + ' DA';
      old.style.display  = '';
    } else {
      old.style.display = 'none';
    }
  }
}

function setStockBadge(stock, shadeChosen) {
  const badge = document.getElementById('stockBadge');
  if (!badge) return;
  badge.className = 'info-stock-badge';
  if (!shadeChosen) {
    badge.classList.add('shade-pending');
    badge.innerHTML = '<i class="fas fa-palette"></i><span>Choisissez une teinte</span>';
  } else if (stock === 0) {
    badge.classList.add('stock-out');
    badge.innerHTML = '<i class="fas fa-times-circle"></i><span>Rupture de stock</span>';
  } else if (stock <= 5) {
    badge.classList.add('stock-low');
    badge.innerHTML = `<i class="fas fa-fire"></i><span>Plus que ${stock} en stock !</span>`;
  } else {
    badge.classList.add('stock-in');
    badge.innerHTML = '<i class="fas fa-check-circle"></i><span>En stock</span>';
  }
}

/* ═══════════════════════════════════════════════════════════
   QUANTITÉ
══════════════════════════════════════════════════════════ */
const qtyInput = document.getElementById('quantity');
const qtyMinus = document.getElementById('qtyMinus');
const qtyPlus  = document.getElementById('qtyPlus');

if (qtyMinus && qtyPlus && qtyInput) {
  qtyMinus.addEventListener('click', () => {
    const v = parseInt(qtyInput.value) || 1;
    if (v > 1) qtyInput.value = v - 1;
  });
  qtyPlus.addEventListener('click', () => {
    const v   = parseInt(qtyInput.value) || 1;
    const max = parseInt(qtyInput.max)   || 999;
    if (v < max) qtyInput.value = v + 1;
  });
}

/* ═══════════════════════════════════════════════════════════
   SÉLECTEUR DE TEINTES
══════════════════════════════════════════════════════════ */
(function initShades() {
  const dotsRow   = document.getElementById('shadeDots');
  const nameLabel = document.getElementById('selectedShadeName');
  const addBtn    = document.getElementById('addWithShadeBtn');
  if (!dotsRow || !addBtn) return;

  let selShade = null, selPrice = BASE_PRICE, selStock = 0, selImage = null;
  const items = dotsRow.querySelectorAll('.shade-item');

  items.forEach(item => {
    item.addEventListener('click', () => {

      // Agitation si rupture
      if (parseInt(item.dataset.stock) === 0) {
        item.classList.add('shake');
        setTimeout(() => item.classList.remove('shake'), 500);
        return; // Ne pas sélectionner une teinte en rupture
      }

      items.forEach(i => i.classList.remove('active'));
      item.classList.add('active');

      selShade = item.dataset.nom;
      selPrice = parseFloat(item.dataset.prix) || BASE_PRICE;
      selStock = parseInt(item.dataset.stock, 10) || 0;
      selImage = item.dataset.image_url || null;

      nameLabel.textContent = selShade;
      setPrice(selPrice, BASE_OLD_PRICE);
      setStockBadge(selStock, true);

      // Changer image principale
      if (selImage) {
        setMainImage(selImage, -1);
        thumbBtns.forEach(t => t.classList.remove('active'));
      } else {
        setMainImage(BASE_IMAGE, 0);
        if (thumbBtns[0]) thumbBtns[0].classList.add('active');
      }

      // Bouton + quantité
      addBtn.disabled = false;
      addBtn.innerHTML = '<i class="fas fa-shopping-bag"></i><span>Ajouter au panier</span>';
      if (qtyInput) {
        qtyInput.disabled = false;
        qtyInput.max      = selStock;
        qtyInput.value    = 1;
      }
    });
  });

  // ── CORRECTION : addToCart avec _originEl pour déclencher animation + badge ──
  addBtn.addEventListener('click', () => {
    if (addBtn.disabled || !selShade) return;
    const qty = Math.max(1, parseInt(qtyInput?.value || 1));
    window.addToCart?.({
      productId : addBtn.dataset.productId,
      name      : addBtn.dataset.name,
      price     : selPrice,
      image     : selImage || BASE_IMAGE,
      quantity  : qty,
      shade     : selShade,
      _originEl : mainImg   // ← déclenche flyToCart + bumpCartBadge
    });
  });
})();

/* ═══════════════════════════════════════════════════════════
   ÉTOILES AVIS
══════════════════════════════════════════════════════════ */
document.querySelectorAll('#starRating i').forEach(star => {
  star.addEventListener('click', () => {
    const v = parseInt(star.dataset.value);
    document.getElementById('ratingInput').value = v;
    document.querySelectorAll('#starRating i').forEach(s => {
      s.className = parseInt(s.dataset.value) <= v ? 'fas fa-star' : 'far fa-star';
    });
  });
  star.addEventListener('mouseenter', () => {
    const v = parseInt(star.dataset.value);
    document.querySelectorAll('#starRating i').forEach(s => {
      s.style.transform = parseInt(s.dataset.value) <= v ? 'scale(1.2)' : '';
    });
  });
  star.addEventListener('mouseleave', () => {
    document.querySelectorAll('#starRating i').forEach(s => s.style.transform = '');
  });
});

/* ═══════════════════════════════════════════════════════════
   PARTAGE
══════════════════════════════════════════════════════════ */
document.querySelectorAll('.share-btn:not(#nativeShare)').forEach(btn => {
  btn.addEventListener('click', e => {
    e.preventDefault();
    const network = btn.dataset.network;
    const url     = encodeURIComponent(window.location.href);
    const msg     = encodeURIComponent('✨ ' + <?= json_encode($product['name']) ?> + ' sur SheGlamour 💄\n');
    const urls = {
      whatsapp : 'https://api.whatsapp.com/send?text=' + msg + url,
      twitter  : 'https://twitter.com/intent/tweet?text=' + msg + url,
      facebook : 'https://www.facebook.com/sharer/sharer.php?u=' + url,
      pinterest: 'https://pinterest.com/pin/create/button/?url=' + url
    };
    if (urls[network]) window.open(urls[network], '_blank', 'width=620,height=500');
  });
});

const nativeShareBtn = document.getElementById('nativeShare');
if (navigator.share && nativeShareBtn) {
  nativeShareBtn.addEventListener('click', async () => {
    try { await navigator.share({ title: <?= json_encode($product['name']) ?>, url: window.location.href }); } catch {}
  });
} else if (nativeShareBtn) {
  nativeShareBtn.style.display = 'none';
}

/* ═══════════════════════════════════════════════════════════
   ACHAT DIRECT
══════════════════════════════════════════════════════════ */
const buyNowBtn = document.getElementById('buyNowBtn');
if (buyNowBtn) {
  buyNowBtn.addEventListener('click', () => {
    const addBtn = document.getElementById('addWithShadeBtn');

    // Si teintes disponibles et aucune sélectionnée → agiter le sélecteur
    if (addBtn && addBtn.disabled) {
      const sel = document.getElementById('shadeSelectorBlock');
      sel?.classList.add('shake');
      setTimeout(() => sel?.classList.remove('shake'), 600);
      return;
    }

    const qty       = parseInt(qtyInput?.value || 1);
    const pId       = buyNowBtn.dataset.productId;
    const shadeName = document.getElementById('selectedShadeName')?.textContent.replace(/^—\s*/, '').trim() || null;
    const price     = addBtn
                    ? parseFloat(addBtn.dataset.price   || buyNowBtn.dataset.price)
                    : parseFloat(buyNowBtn.dataset.price);
    const image     = (addBtn && addBtn.dataset.image_url)
                    ? addBtn.dataset.image_url
                    : buyNowBtn.dataset.image_url;

    const cartKey = pId + (shadeName ? '__' + shadeName : '');
    const item    = { name: buyNowBtn.dataset.name, price, image_url: image, quantity: qty, shade: shadeName };
    const prev    = localStorage.getItem('cart');

    localStorage.setItem('cart', JSON.stringify({ [cartKey]: item }));
    if (typeof openCheckout === 'function') openCheckout();
    else { localStorage.setItem('cart', prev || '{}'); return; }

    const sidebar = document.getElementById('sg-checkout-sidebar');
    if (!sidebar) return;

    const obs = new MutationObserver(() => {
      if (!sidebar.classList.contains('active')) {
        localStorage.setItem('cart', prev || '{}');
        window.renderCart?.();
        obs.disconnect();
      }
    });
    obs.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
  });
}
</script>

<script src="<?= $b ?>/js/shop.js?v=<?= time() ?>"></script>
<script src="/js/checkout.js?v=2" defer></script>
<?php include 'includes/footer.php'; ?>
</body>
</html>