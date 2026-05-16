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

// ── Teintes (avec prix, stock, image) ────────────────────────────────────────
// ALTER TABLE teintes
//   ADD COLUMN prix  DECIMAL(10,2) DEFAULT NULL,
//   ADD COLUMN stock INT           DEFAULT 0,
//   ADD COLUMN image VARCHAR(255)  DEFAULT NULL;
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
    $s['prix']      = (float)$s['prix'];
    $s['stock']     = (int)$s['stock'];
    $s['image_url'] = !empty($s['image'])
                    ? $b . '/images/' . basename($s['image'])
                    : '';
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

<section class="product-page">

  <!-- Miniatures gauche -->
  <div class="product-left" id="thumbnailStrip">
    <?php foreach ($additionalImages as $img): ?>
      <img src="<?= htmlspecialchars($img) ?>"
           class="thumbnail"
           alt="<?= htmlspecialchars($product['name']) ?>">
    <?php endforeach; ?>
  </div>

  <!-- Image principale -->
  <div class="product-main">
    <div class="product-main-image">
      <?php if ($outOfStock && !$hasShades): ?>
        <div class="out-of-stock-badge">Rupture de stock</div>
      <?php endif; ?>
      <img id="mainImage"
           src="<?= htmlspecialchars($additionalImages[0]) ?>"
           alt="<?= htmlspecialchars($product['name']) ?>"
           class="<?= ($outOfStock && !$hasShades) ? 'img-out-of-stock' : '' ?>">
    </div>

    <div class="product-right">
      <h1><?= htmlspecialchars($product['name']) ?></h1>

      <!-- Badge stock — mis à jour par JS quand une teinte est choisie -->
      <span class="stock-badge <?= $hasShades ? 'neutral' : ($outOfStock ? 'out' : ($stock <= 5 ? 'low' : 'in')) ?>"
            id="stockBadge">
        <?php if ($hasShades): ?>
          <i class="fas fa-palette"></i> Choisissez une teinte
        <?php elseif ($outOfStock): ?>
          <i class="fas fa-times-circle"></i> Rupture de stock
        <?php elseif ($stock <= 5): ?>
          <i class="fas fa-exclamation-circle"></i> Plus que <?= $stock ?> en stock
        <?php else: ?>
          <i class="fas fa-check-circle"></i> En stock
        <?php endif; ?>
      </span>

      <!-- Étoiles -->
      <div class="reviews">
        <?php if ($reviews): ?>
          <div class="average-rating">
            <?php for ($i = 1; $i <= 5; $i++):
              if ($i <= floor($averageRating))      echo '<i class="fas fa-star"></i>';
              elseif ($i - $averageRating <= 0.5)   echo '<i class="fas fa-star-half-alt"></i>';
              else                                   echo '<i class="far fa-star"></i>';
            endfor; ?>
            <span>(<?= $averageRating ?>/5 — <?= count($reviews) ?> avis)</span>
          </div>
        <?php else: ?>
          <p class="no-reviews">Aucun avis pour le moment.</p>
        <?php endif; ?>
      </div>

      <!-- Prix — mis à jour par JS selon teinte -->
      <p class="price" id="priceBlock">
        <span class="old-price" id="oldPriceEl"
              style="<?= $baseOldPrice ? '' : 'display:none' ?>">
          <?= $baseOldPrice ? number_format($baseOldPrice, 2, ',', ' ') . ' DA' : '' ?>
        </span>
        <span id="currentPriceEl"><?= number_format($basePrice, 2, ',', ' ') ?> DA</span>
      </p>

      <p class="description"><?= htmlspecialchars($product['description']) ?></p>

      <!-- ── Boutons action ────────────────────────────────────────────── -->
      <div class="product-actions">

        <?php if ($hasShades): ?>

          <!-- Sélecteur de teintes : toutes les données viennent du PHP via data-* -->
          <div class="shade-selector" id="shadeSelectorBlock">
            <p class="shade-label">
              Choisir une teinte :
              <span id="selectedShadeName" class="shade-chosen-name"></span>
            </p>
            <div class="shade-dots-row" id="shadeDots">
              <?php foreach ($shades as $s): ?>
                <span class="shade-dot-inline"
                      data-nom="<?= htmlspecialchars($s['nom_teinte']) ?>"
                      data-prix="<?= $s['prix'] ?>"
                      data-stock="<?= $s['stock'] ?>"
                      data-image_url="<?= htmlspecialchars($s['image_url']) ?>"
                      title="<?= htmlspecialchars($s['nom_teinte']) ?>"
                      style="background:<?= htmlspecialchars($s['code_couleur'] ?? '#ccc') ?>">
                  <span class="shade-dot-tip"><?= htmlspecialchars($s['nom_teinte']) ?></span>
                </span>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="add-to-cart-wrapper">
            <input type="number" name="quantity" id="quantity"
                   value="1" min="1" max="1" step="1" disabled>
            <button id="addWithShadeBtn"
                    class="add-to-cart btn-secondary-action"
                    data-product-id="<?= $product['id'] ?>"
                    data-name="<?= htmlspecialchars($product['name']) ?>"
                    data-price="<?= $basePrice ?>"
                    data-image_url="<?= htmlspecialchars($additionalImages[0]) ?>"
                    disabled>
              <i class="fas fa-palette"></i> Choisissez une teinte
            </button>
          </div>

        <?php else: ?>

          <!-- Produit sans teintes -->
          <div class="add-to-cart-wrapper">
            <input type="number" name="quantity" id="quantity"
                   value="1" min="1" max="<?= $stock ?>" step="1"
                   <?= $outOfStock ? 'disabled' : '' ?>>
            <button class="add-to-cart btn-secondary-action"
                    data-product-id="<?= $product['id'] ?>"
                    data-name="<?= htmlspecialchars($product['name']) ?>"
                    data-price="<?= $basePrice ?>"
                    data-image_url="<?= htmlspecialchars($additionalImages[0]) ?>"
                    data-stock="<?= $stock ?>"
                    <?= $outOfStock ? 'disabled' : '' ?>>
              <i class="fas fa-<?= $outOfStock ? 'ban' : 'shopping-bag' ?>"></i>
              <?= $outOfStock ? 'Rupture de stock' : 'Ajouter au panier' ?>
            </button>
          </div>

        <?php endif; ?>

        <!-- Achat direct -->
        <button class="buy-now-btn" id="buyNowBtn"
                data-product-id="<?= $product['id'] ?>"
                data-name="<?= htmlspecialchars($product['name']) ?>"
                data-price="<?= $basePrice ?>"
                data-image_url="<?= htmlspecialchars($additionalImages[0]) ?>"
                <?= ($outOfStock && !$hasShades) ? 'disabled style="opacity:.5;cursor:not-allowed"' : '' ?>>
          <i class="fas fa-bolt"></i> Acheter maintenant
        </button>

      </div><!-- .product-actions -->

      <!-- Partage -->
      <div class="share-product">
        <span>Partager :</span>
        <a href="#" class="share-btn facebook" data-network="facebook" title="Facebook"><i class="fab fa-facebook-square"></i></a>
        <a href="#" class="share-btn twitter"  data-network="twitter"  title="Twitter"><i class="fab fa-x-twitter"></i></a>
        <a href="#" class="share-btn whatsapp" data-network="whatsapp" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
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
      $spImg     = empty($sp['image_url'])
                 ? $b . '/images/placeholder.jpg'
                 : (str_starts_with($sp['image_url'], 'http')
                    ? $sp['image_url']
                    : $b . '/images/' . basename($sp['image_url']));
      $spStock   = (int)($sp['stock'] ?? 0);
      $spNoStock = $spStock === 0;
    ?>
      <a href="<?= $b ?>/product.php?id=<?= $sp['id'] ?>"
         class="similar-item <?= $spNoStock ? 'out-of-stock' : '' ?>">
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
/* ── Constantes injectées par PHP ──────────────────────────────────── */
const BASE_URL       = <?= json_encode($b) ?>;
const BASE_PRICE     = <?= json_encode($basePrice) ?>;
const BASE_OLD_PRICE = <?= json_encode($baseOldPrice) ?>;
const BASE_IMAGE     = <?= json_encode($additionalImages[0]) ?>;

/* ── Image principale avec fondu ───────────────────────────────────── */
const mainImage = document.getElementById("mainImage");
mainImage.style.transition = "opacity 0.15s ease";

function setMainImage(url) {
  if (!url) return;
  mainImage.style.opacity = "0";
  setTimeout(() => { mainImage.src = url; mainImage.style.opacity = "1"; }, 150);
}

/* ── Thumbnails produit ────────────────────────────────────────────── */
const thumbnails = document.querySelectorAll("#thumbnailStrip .thumbnail");
thumbnails.forEach(img => {
  img.addEventListener("click", () => {
    setMainImage(img.src);
    thumbnails.forEach(t => t.classList.remove("active"));
    img.classList.add("active");
  });
});
if (thumbnails.length) thumbnails[0].classList.add("active");

/* ── Helpers UI ────────────────────────────────────────────────────── */
function setPrice(price, oldPrice) {
  const cur = document.getElementById("currentPriceEl");
  const old = document.getElementById("oldPriceEl");
  if (cur) cur.textContent = price.toLocaleString("fr-DZ", { minimumFractionDigits: 2 }) + " DA";
  if (old) {
    if (oldPrice && oldPrice > price) {
      old.textContent = oldPrice.toLocaleString("fr-DZ", { minimumFractionDigits: 2 }) + " DA";
      old.style.display = "";
    } else {
      old.style.display = "none";
    }
  }
}

function setStockBadge(stock, shadeSelected) {
  const badge = document.getElementById("stockBadge");
  if (!badge) return;
  if (!shadeSelected) {
    badge.className = "stock-badge neutral";
    badge.innerHTML = `<i class="fas fa-palette"></i> Choisissez une teinte`;
    return;
  }
  if (stock === 0) {
    badge.className = "stock-badge out";
    badge.innerHTML = `<i class="fas fa-times-circle"></i> Rupture de stock`;
  } else if (stock <= 5) {
    badge.className = "stock-badge low";
    badge.innerHTML = `<i class="fas fa-exclamation-circle"></i> Plus que ${stock} en stock`;
  } else {
    badge.className = "stock-badge in";
    badge.innerHTML = `<i class="fas fa-check-circle"></i> En stock`;
  }
}

/* ── Sélecteur de teintes ──────────────────────────────────────────── */
(function initShadeSelector() {
  const dotsRow   = document.getElementById("shadeDots");
  const nameLabel = document.getElementById("selectedShadeName");
  const addBtn    = document.getElementById("addWithShadeBtn");
  const qtyInput  = document.getElementById("quantity");
  if (!dotsRow || !addBtn) return;

  let selectedShade      = null;
  let selectedShadeImage = null;
  let selectedShadePrice = BASE_PRICE;
  let selectedShadeStock = 0;

  const dots = dotsRow.querySelectorAll(".shade-dot-inline");

  dots.forEach(dot => {
    dot.addEventListener("click", () => {
      // 1. Activer le dot cliqué
      dots.forEach(d => d.classList.remove("active"));
      dot.classList.add("active");

      // 2. Lire les données injectées par PHP dans les data-*
      selectedShade      = dot.dataset.nom;
      selectedShadePrice = parseFloat(dot.dataset.prix) || BASE_PRICE;
      selectedShadeStock = parseInt(dot.dataset.stock, 10) || 0;
      selectedShadeImage = dot.dataset.image_url || null;

      // 3. Mettre à jour l'affichage : nom, prix, stock
      nameLabel.textContent = "— " + selectedShade;
      setPrice(selectedShadePrice, BASE_OLD_PRICE);
      setStockBadge(selectedShadeStock, true);

      // 4. Changer l'image principale
      if (selectedShadeImage) {
        setMainImage(selectedShadeImage);
        thumbnails.forEach(t => t.classList.remove("active"));
      } else {
        setMainImage(BASE_IMAGE);
        if (thumbnails.length) thumbnails[0].classList.add("active");
      }

      // 5. Mettre à jour le bouton + input quantité
      if (selectedShadeStock === 0) {
        addBtn.disabled  = true;
        addBtn.innerHTML = `<i class="fas fa-ban"></i> Rupture de stock`;
        if (qtyInput) { qtyInput.disabled = true; qtyInput.max = 1; qtyInput.value = 1; }
      } else {
        addBtn.disabled  = false;
        addBtn.innerHTML = `<i class="fas fa-shopping-bag"></i> Ajouter au panier`;
        if (qtyInput) { qtyInput.disabled = false; qtyInput.max = selectedShadeStock; qtyInput.value = 1; }
      }
    });
  });

  // Clic "Ajouter au panier" avec la teinte sélectionnée
  addBtn.addEventListener("click", () => {
    if (addBtn.disabled || !selectedShade) return;
    const qty = Math.max(1, parseInt(qtyInput?.value || 1));
    window.addToCart({
      productId: addBtn.dataset.productId,
      name:      addBtn.dataset.name,
      price:     selectedShadePrice,
      image:     selectedShadeImage || BASE_IMAGE,
      quantity:  qty,
      shade:     selectedShade
    });
  });
})();

/* ── Étoiles avis ──────────────────────────────────────────────────── */
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

/* ── Partage réseaux sociaux ───────────────────────────────────────── */
document.querySelectorAll(".share-btn:not(.native)").forEach(btn => {
  btn.addEventListener("click", e => {
    e.preventDefault();
    const network      = btn.dataset.network;
    const productName  = <?= json_encode($product['name']) ?>;
    const productPrice = <?= json_encode(number_format($basePrice, 2, ',', ' ') . ' DA') ?>;
    const url          = encodeURIComponent(window.location.href);
    const message      = encodeURIComponent(`✨ J'ai trouvé ce produit incroyable sur SheGlamour ! 💄\n👉 ${productName} à ${productPrice}\n🔗 `);
    const urls = {
      whatsapp:  `https://api.whatsapp.com/send?text=${message}${url}`,
      twitter:   `https://twitter.com/intent/tweet?text=${message}${url}`,
      facebook:  `https://www.facebook.com/sharer/sharer.php?u=${url}`,
      pinterest: `https://pinterest.com/pin/create/button/?url=${url}`
    };
    if (urls[network]) window.open(urls[network], "_blank", "width=600,height=500");
  });
});

/* ── Partage natif mobile ──────────────────────────────────────────── */
const nativeShareBtn = document.getElementById("nativeShare");
if (navigator.share && nativeShareBtn) {
  nativeShareBtn.addEventListener("click", async () => {
    try {
      await navigator.share({
        title: <?= json_encode($product['name']) ?>,
        text:  "✨ J'ai trouvé ce produit incroyable sur SheGlamour ! 💄",
        url:   window.location.href
      });
    } catch {}
  });
} else if (nativeShareBtn) {
  nativeShareBtn.style.display = "none";
}

/* ── Achat direct ──────────────────────────────────────────────────── */
const buyNowBtn = document.getElementById("buyNowBtn");
if (buyNowBtn) {
  buyNowBtn.addEventListener("click", () => {
    const addBtn = document.getElementById("addWithShadeBtn");

    // Si teintes dispo mais aucune choisie → secouer le sélecteur
    if (addBtn && addBtn.disabled) {
      const selector = document.getElementById("shadeSelectorBlock");
      selector?.classList.add("shake");
      setTimeout(() => selector?.classList.remove("shake"), 600);
      return;
    }

    const qty      = parseInt(document.getElementById("quantity")?.value || 1);
    const pId      = buyNowBtn.dataset.productId;
    const shadeName = document.getElementById("selectedShadeName")
                        ?.textContent.replace(/^—\s*/, "").trim() || null;

    // Prix et image courants (teinte ou produit)
    const currentPrice = addBtn
      ? parseFloat(addBtn.dataset.price || buyNowBtn.dataset.price)
      : parseFloat(buyNowBtn.dataset.price);
    const currentImage = (addBtn && addBtn.dataset.image_url)
      ? addBtn.dataset.image_url
      : buyNowBtn.dataset.image_url;

    const cartKey = pId + (shadeName ? '__' + shadeName : '');
    const item = {
      name:      buyNowBtn.dataset.name,
      price:     currentPrice,
      image_url: currentImage,
      quantity:  qty,
      shade:     shadeName
    };

    const previousCart = localStorage.getItem("cart");
    localStorage.setItem("cart", JSON.stringify({ [cartKey]: item }));

    if (typeof openCheckout === "function") {
      openCheckout();
    } else {
      console.warn("checkout.js non chargé");
      localStorage.setItem("cart", previousCart || "{}");
      return;
    }

    // Restaurer le panier si checkout annulé
    const checkoutSidebar = document.getElementById("sg-checkout-sidebar");
    if (!checkoutSidebar) return;
    const observer = new MutationObserver(() => {
      if (!checkoutSidebar.classList.contains("active")) {
        const current = localStorage.getItem("cart");
        if (current !== null) localStorage.setItem("cart", previousCart || "{}");
        if (typeof window.renderCart === "function") window.renderCart();
        observer.disconnect();
      }
    });
    observer.observe(checkoutSidebar, { attributes: true, attributeFilter: ["class"] });
  });
}
</script>

<script src="<?= $b ?>/js/shop.js?v=<?= time() ?>"></script>
<script src="<?= $b ?>/js/checkout.js?v=<?= time() ?>"></script>
<?php include 'includes/footer.php'; ?>
</body>
</html>