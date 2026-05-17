<?php
include 'includes/db.php';
include_once 'includes/config.php';
$b = BASE_URL;

$categorie     = $_GET['categorie']     ?? 'Tous';
$sous_categorie = $_GET['sous_categorie'] ?? '';

/* ── Requête produits ── */
if ($categorie === 'Tous') {
    $query = $pdo->query("SELECT * FROM products ORDER BY id ASC");
} elseif ($sous_categorie !== '') {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE categorie = :categorie AND sous_categorie = :sous ORDER BY id ASC");
    $stmt->execute(['categorie' => $categorie, 'sous' => $sous_categorie]);
    $query = $stmt;
} else {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE categorie = :categorie ORDER BY id ASC");
    $stmt->execute(['categorie' => $categorie]);
    $query = $stmt;
}

/* ── Récupère les sous-catégories disponibles pour cette catégorie ── */
$sous_cats = [];
if ($categorie !== 'Tous') {
    $scStmt = $pdo->prepare(
        "SELECT DISTINCT sous_categorie FROM products
         WHERE categorie = :categorie AND sous_categorie IS NOT NULL AND sous_categorie <> ''
         ORDER BY sous_categorie ASC"
    );
    $scStmt->execute(['categorie' => $categorie]);
    $sous_cats = $scStmt->fetchAll(PDO::FETCH_COLUMN);
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
  <link rel="icon" type="image/png" href="<?= $b ?>/images/logofib.png">

  <style>
  /* ── Sous-catégories pills ── */
  .sous-cats-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin: 0 0 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(68,11,25,.12);
  }

  .sous-cat-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 18px;
    border: 1px solid rgba(68,11,25,.25);
    border-radius: 999px;
    background: transparent;
    color: #6e1a2e;
    font-family: 'Jost', system-ui, sans-serif;
    font-size: .7rem;
    font-weight: 500;
    letter-spacing: .14em;
    text-transform: uppercase;
    text-decoration: none;
    cursor: pointer;
    transition:
      background .22s ease,
      border-color .22s ease,
      color .22s ease,
      box-shadow .22s ease,
      transform .2s ease;
    white-space: nowrap;
  }

  .sous-cat-pill:hover {
    border-color: #440B19;
    color: #440B19;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(68,11,25,.12);
  }

  .sous-cat-pill.active {
    background: #440B19;
    border-color: #440B19;
    color: #fff;
    box-shadow: 0 4px 18px rgba(68,11,25,.22);
  }

  .sous-cat-pill .pill-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    border-radius: 99px;
    background: rgba(68,11,25,.12);
    font-size: .58rem;
    font-weight: 600;
    letter-spacing: 0;
  }

  .sous-cat-pill.active .pill-count {
    background: rgba(255,255,255,.22);
  }
  </style>
</head>
<body>

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

    <div class="filter-group toggle-group">
      <span>En stock uniquement</span>
      <label class="switch">
        <input type="checkbox" id="filterInStock">
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
    <h1><?= htmlspecialchars($categorie) ?><?= $sous_categorie ? ' — ' . htmlspecialchars($sous_categorie) : '' ?></h1>

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
      <a href="<?= $b ?>/index.php">Accueil</a> &gt;
      <?php if ($categorie === 'Tous'): ?>
        <span>Tous les produits</span>
      <?php else: ?>
        <a href="<?= $b ?>/categorie.php?categorie=Tous">Tous</a> &gt;
        <?php if ($sous_categorie): ?>
          <a href="<?= $b ?>/categorie.php?categorie=<?= urlencode($categorie) ?>"><?= htmlspecialchars($categorie) ?></a> &gt;
          <span><?= htmlspecialchars($sous_categorie) ?></span>
        <?php else: ?>
          <span><?= htmlspecialchars($categorie) ?></span>
        <?php endif; ?>
      <?php endif; ?>
    </nav>

    <!-- ── Sous-catégories pills ── -->
    <?php if (!empty($sous_cats)): ?>
    <div class="sous-cats-bar">
      <!-- Pill "Tout" -->
      <a href="<?= $b ?>/categorie.php?categorie=<?= urlencode($categorie) ?>"
         class="sous-cat-pill <?= $sous_categorie === '' ? 'active' : '' ?>">
        Tout
      </a>

      <?php foreach ($sous_cats as $sc):
        /* Compte les produits par sous-catégorie */
        $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE categorie = ? AND sous_categorie = ?");
        $cntStmt->execute([$categorie, $sc]);
        $cnt = (int)$cntStmt->fetchColumn();
      ?>
      <a href="<?= $b ?>/categorie.php?categorie=<?= urlencode($categorie) ?>&sous_categorie=<?= urlencode($sc) ?>"
         class="sous-cat-pill <?= $sous_categorie === $sc ? 'active' : '' ?>">
        <?= htmlspecialchars($sc) ?>
        <span class="pill-count"><?= $cnt ?></span>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <button class="filter-toggle-btn" id="filterToggleBtn">
      <i class="fas fa-sliders-h"></i> Filtres
    </button>

    <!-- Contrôles vue -->
    <div class="filter-controls">
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
        $productId  = $product['id'];
        $stock      = (int)($product['stock'] ?? 0);
        $outOfStock = $stock === 0;
        $productUrl = $b . '/product.php?id=' . $productId;

        $imagePath = empty($product['image_url']) ? $b . '/images/placeholder.jpg'
          : (str_starts_with($product['image_url'], 'http') ? $product['image_url'] : $b . '/images/' . basename($product['image_url']));

        /* ── Teintes ── */
        $shadeStmt = $pdo->prepare("SELECT nom_teinte, code_couleur FROM teintes WHERE product_id = ?");
        $shadeStmt->execute([$productId]);
        $productShades = $shadeStmt->fetchAll(PDO::FETCH_ASSOC);
        $hasShades     = !empty($productShades);

        $oldPrice = $product['old_price'] ?? '';
      ?>

      <div class="product-card"
           data-price="<?= $product['price'] ?>"
           data-brand="<?= htmlspecialchars($product['marque'] ?? '') ?>"
           data-sale="<?= !empty($product['is_sale']) ? '1' : '0' ?>"
           data-stock="<?= $stock ?>"
           data-instock="<?= $outOfStock ? '0' : '1' ?>">

        <div class="product-image-wrapper">

          <?php if ($outOfStock): ?>
            <span class="badge-oos">Rupture</span>
          <?php elseif ($stock <= 5): ?>
            <span class="badge-low">Stock limité</span>
          <?php endif; ?>

          <a href="<?= $productUrl ?>" class="product-card-img-link" tabindex="-1">
            <img src="<?= htmlspecialchars($imagePath) ?>"
                 alt="<?= htmlspecialchars($product['name']) ?>"
                 class="<?= $outOfStock ? 'img-out-of-stock' : '' ?>">
          </a>

          <button class="add-to-wishlist"
                  data-product-id="<?= $productId ?>"
                  data-name="<?= htmlspecialchars($product['name']) ?>"
                  data-price="<?= htmlspecialchars($product['price']) ?>"
                  data-image_url="<?= htmlspecialchars($imagePath) ?>"
                  data-has-shades="<?= $hasShades ? 1 : 0 ?>"
                  type="button"
                  aria-label="Ajouter aux favoris">
            <i class="fas fa-heart"></i>
          </button>

          <button class="quick-view-btn"
                  data-product-id="<?= $productId ?>"
                  data-name="<?= htmlspecialchars($product['name']) ?>"
                  data-price="<?= htmlspecialchars($product['price']) ?>"
                  data-old-price="<?= htmlspecialchars($oldPrice) ?>"
                  data-image="<?= htmlspecialchars($imagePath) ?>"
                  data-brand="<?= htmlspecialchars($product['marque'] ?? '') ?>"
                  data-stock="<?= $stock ?>"
                  data-has-shades="<?= $hasShades ? 1 : 0 ?>"
                  data-shades="<?= htmlspecialchars(json_encode($productShades), ENT_QUOTES) ?>"
                  data-description="<?= htmlspecialchars($product['description'] ?? '') ?>"
                  data-url="<?= $productUrl ?>"
                  type="button"
                  aria-label="Aperçu rapide">
            <i class="fas fa-eye"></i>
            <span>Aperçu rapide</span>
          </button>

        </div>

        <div class="product-info">

          <a href="<?= $productUrl ?>" class="product-card-title-link">
            <h3><?= htmlspecialchars($product['name']) ?></h3>
          </a>

          <p class="price">
            <?php if (!empty($oldPrice) && $oldPrice > $product['price']): ?>
              <span class="old-price"><?= number_format($oldPrice, 2, ',', ' ') ?>DA</span>
              <span class="sale-price"><?= number_format($product['price'], 2, ',', ' ') ?>DA</span>
            <?php else: ?>
              <?= number_format($product['price'], 2, ',', ' ') ?>DA
            <?php endif; ?>
          </p>

          <?php if (!empty($productShades)): ?>
            <div class="card-shades">
              <?php foreach (array_slice($productShades, 0, 6) as $shade): ?>
                <span class="card-shade-dot"
                      style="background:<?= htmlspecialchars($shade['code_couleur'] ?? '#ccc') ?>"
                      title="<?= htmlspecialchars($shade['nom_teinte']) ?>"></span>
              <?php endforeach; ?>
              <?php if (count($productShades) > 6): ?>
                <span class="card-shade-more">+<?= count($productShades) - 6 ?></span>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="card-shades card-shades-placeholder"></div>
          <?php endif; ?>

          <?php if ($hasShades): ?>
            <button class="choose-shade-btn"
                    data-product-id="<?= $productId ?>"
                    data-name="<?= htmlspecialchars($product['name']) ?>"
                    data-price="<?= htmlspecialchars($product['price']) ?>"
                    data-image_url="<?= htmlspecialchars($imagePath) ?>"
                    data-stock="<?= $stock ?>"
                    type="button"
                    <?= $outOfStock ? 'disabled' : '' ?>>
              <i class="fas fa-<?= $outOfStock ? 'ban' : 'palette' ?>"></i>
              <?= $outOfStock ? 'Rupture de stock' : 'Choisir une teinte' ?>
            </button>
          <?php else: ?>
            <button class="add-to-cart"
                    data-product-id="<?= $productId ?>"
                    data-name="<?= htmlspecialchars($product['name']) ?>"
                    data-price="<?= htmlspecialchars($product['price']) ?>"
                    data-image_url="<?= htmlspecialchars($imagePath) ?>"
                    data-stock="<?= $stock ?>"
                    type="button"
                    <?= $outOfStock ? 'disabled' : '' ?>>
              <i class="fas fa-<?= $outOfStock ? 'ban' : 'shopping-bag' ?>"></i>
              <?= $outOfStock ? 'Rupture de stock' : 'Ajouter au panier' ?>
            </button>
          <?php endif; ?>

        </div>

      </div>

      <?php endwhile; ?>

    </div>
  </section>

</div>


<!-- ══ MODALE APERÇU RAPIDE ══════════════════════════════════════════ -->
<div class="qv-overlay" id="qvOverlay" role="dialog" aria-modal="true" aria-label="Aperçu rapide">
  <div class="qv-modal" id="qvModal">

    <button class="qv-close" id="qvClose" aria-label="Fermer">&times;</button>

    <div class="qv-col-image">
      <img id="qvImg" src="" alt="" loading="lazy">
      <span class="qv-badge" id="qvBadge"></span>
    </div>

    <div class="qv-col-info">
      <span class="qv-brand" id="qvBrand"></span>
      <h2 class="qv-name" id="qvName"></h2>
      <div class="qv-price" id="qvPrice"></div>

      <div class="qv-stock-line">
        <span class="qv-stock-dot" id="qvStockDot"></span>
        <span class="qv-stock-label" id="qvStockLabel"></span>
      </div>

      <div class="qv-divider"></div>
      <p class="qv-description" id="qvDescription"></p>

      <div class="qv-shades-block" id="qvShadesBlock">
        <span class="qv-shades-title">Teintes disponibles</span>
        <div class="qv-shades-row" id="qvShadesRow"></div>
      </div>

      <div class="qv-actions">
        <button class="qv-cart-btn" id="qvCartBtn" type="button"></button>
        <a class="qv-detail-link" id="qvDetailLink" href="#">
          Voir la fiche complète <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>

  </div>
</div>


<script>
/* ══ Sidebar filtres mobile ══════════════════════════════════ */
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
    if (e.key === 'Escape') {
      sidebar.classList.remove('active');
      overlay.classList.remove('active');
    }
  });
});

/* ══ Vue grille / liste ══════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  const grid        = document.querySelector('.products-grid');
  const viewButtons = document.querySelectorAll('.view-btn');
  viewButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      viewButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      grid.classList.toggle('products-list', btn.dataset.view === 'list');
    });
  });
});

/* ══ APERÇU RAPIDE ═══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  const overlay    = document.getElementById('qvOverlay');
  const closeBtn   = document.getElementById('qvClose');
  const cartBtn    = document.getElementById('qvCartBtn');
  const detailLink = document.getElementById('qvDetailLink');

  function fmtDA(v) {
    return Number(v).toLocaleString('fr-DZ', { minimumFractionDigits: 2 }) + ' DA';
  }

  function openQV(btn) {
    const id          = btn.dataset.productId;
    const name        = btn.dataset.name;
    const price       = parseFloat(btn.dataset.price);
    const oldPrice    = parseFloat(btn.dataset.oldPrice);
    const image       = btn.dataset.image;
    const brand       = btn.dataset.brand;
    const stock       = parseInt(btn.dataset.stock, 10);
    const hasShades   = btn.dataset.hasShades === '1';
    const url         = btn.dataset.url;
    const description = btn.dataset.description || '';

    const imgEl = document.getElementById('qvImg');
    imgEl.src = ''; requestAnimationFrame(() => { imgEl.src = image; imgEl.alt = name; });

    document.getElementById('qvBrand').textContent       = brand || '';
    document.getElementById('qvName').textContent        = name;
    document.getElementById('qvDescription').textContent = description;

    const priceEl = document.getElementById('qvPrice');
    priceEl.innerHTML = (!isNaN(oldPrice) && oldPrice > price)
      ? `<span class="qv-old">${fmtDA(oldPrice)}</span><span class="qv-current">${fmtDA(price)}</span>`
      : `<span class="qv-normal">${fmtDA(price)}</span>`;

    const badge      = document.getElementById('qvBadge');
    const stockDot   = document.getElementById('qvStockDot');
    const stockLabel = document.getElementById('qvStockLabel');
    if (stock === 0) {
      badge.textContent = 'Rupture'; badge.className = 'qv-badge qv-badge--oos';
      stockDot.className = 'qv-stock-dot qv-dot--out'; stockLabel.textContent = 'Rupture de stock';
    } else if (stock <= 5) {
      badge.textContent = 'Stock limité'; badge.className = 'qv-badge qv-badge--low';
      stockDot.className = 'qv-stock-dot qv-dot--low';
      stockLabel.textContent = `Seulement ${stock} restant${stock > 1 ? 's' : ''}`;
    } else {
      badge.textContent = ''; badge.className = 'qv-badge';
      stockDot.className = 'qv-stock-dot qv-dot--in'; stockLabel.textContent = 'En stock';
    }

    const shadesBlock = document.getElementById('qvShadesBlock');
    const shadesRow   = document.getElementById('qvShadesRow');
    shadesRow.innerHTML = '';
    let shades = [];
    try { shades = JSON.parse(btn.dataset.shades || '[]'); } catch {}

    let qvSelectedShade = null;

    if (hasShades && shades.length) {
      shades.forEach(s => {
        const dot = document.createElement('span');
        dot.className = 'qv-shade-dot';
        dot.title     = s.nom_teinte || '';
        dot.style.background = s.code_couleur || '#ccc';
        dot.addEventListener('click', () => {
          shadesRow.querySelectorAll('.qv-shade-dot').forEach(d => d.classList.remove('active'));
          dot.classList.add('active');
          qvSelectedShade = s.nom_teinte;
          updateQvBtn();
        });
        shadesRow.appendChild(dot);
      });
      shadesBlock.style.display = 'flex';
    } else {
      shadesBlock.style.display = 'none';
    }

    function updateQvBtn() {
      const needsShade = hasShades && !qvSelectedShade;
      cartBtn.disabled  = stock === 0 || needsShade;
      cartBtn.innerHTML = stock === 0
        ? '<i class="fas fa-ban"></i> Rupture de stock'
        : needsShade
          ? '<i class="fas fa-palette"></i> Sélectionnez une teinte'
          : '<i class="fas fa-shopping-bag"></i> Ajouter au panier';
    }
    updateQvBtn();

    const newCartBtn = cartBtn.cloneNode(true);
    cartBtn.replaceWith(newCartBtn);
    newCartBtn.addEventListener('click', () => {
      if (stock === 0) return;
      if (hasShades && !qvSelectedShade) {
        shadesBlock.classList.add('qv-shake');
        setTimeout(() => shadesBlock.classList.remove('qv-shake'), 500);
        return;
      }
      window.addToCart({ productId: id, name, price, image, quantity: 1, shade: qvSelectedShade || null });
      closeQV();
    });

    detailLink.href = url;
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    closeBtn.focus();
  }

  function closeQV() {
    overlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  document.querySelectorAll('.quick-view-btn').forEach(btn => {
    btn.addEventListener('click', e => { e.stopPropagation(); openQV(btn); });
  });

  closeBtn.addEventListener('click', closeQV);
  overlay.addEventListener('click', e => { if (e.target === overlay) closeQV(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeQV(); });
  document.addEventListener('addedToCart', closeQV);
});
</script>

<?php include 'includes/product_modal.php'; ?>
<script src="<?= $b ?>/js/shop.js?v=<?= time() ?>"></script>
<?php include 'includes/footer.php'; ?>

</body>
</html>