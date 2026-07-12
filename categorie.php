<?php
include 'includes/db.php';
include_once 'includes/config.php';
$b = BASE_URL;

$categorie      = $_GET['categorie']      ?? 'Tous';
$sous_categorie = $_GET['sous_categorie'] ?? '';
$pack           = isset($_GET['pack'])   && $_GET['pack']   == '1';
$soldes         = isset($_GET['soldes']) && $_GET['soldes']  == '1';

if ($pack) {
    $query = $pdo->query("SELECT * FROM products WHERE is_pack = TRUE ORDER BY id ASC");
} elseif ($soldes) {
    $query = $pdo->query("SELECT * FROM products WHERE old_price IS NOT NULL AND old_price > price ORDER BY id ASC");
} elseif ($categorie === 'Tous') {
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

$sous_cats = [];
if (!$pack && !$soldes && $categorie !== 'Tous') {
    $scStmt = $pdo->prepare(
        "SELECT sous_categorie, COUNT(*) AS cnt
         FROM products
         WHERE categorie = :categorie
           AND sous_categorie IS NOT NULL
           AND sous_categorie <> ''
         GROUP BY sous_categorie
         ORDER BY sous_categorie ASC"
    );
    $scStmt->execute(['categorie' => $categorie]);
    $sous_cats = $scStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>
    <?= $pack ? 'Coffrets' : ($soldes ? 'Promotions' : htmlspecialchars($categorie)) ?> - SheGlamour
  </title>
  <link rel="stylesheet" href="<?= $b ?>/categorie.css?v=<?= time() ?>">
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
  src="https://www.facebook.com/tr?id=1496845578585995&ev=PageView&noscript=1"/></noscript>
  <!-- End Meta Pixel Code -->

  <style>
  /* ── Pills sous-catégories ── */
  .sous-cats-bar {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    margin: 0 0 24px; padding-bottom: 16px;
    border-bottom: 1px solid rgba(68,11,25,.12);
  }
  .sous-cat-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 18px; border: 1px solid rgba(68,11,25,.25); border-radius: 999px;
    background: transparent; color: #6e1a2e;
    font-size: .7rem; font-weight: 500; letter-spacing: .14em;
    text-transform: uppercase; text-decoration: none; white-space: nowrap; cursor: pointer;
    transition: background .22s, border-color .22s, color .22s, box-shadow .22s, transform .2s;
  }
  .sous-cat-pill:hover {
    border-color: #440B19; color: #440B19;
    transform: translateY(-1px); box-shadow: 0 4px 14px rgba(68,11,25,.12);
  }
  .sous-cat-pill.active {
    background: #440B19; border-color: #440B19; color: #F5F1EE;
    box-shadow: 0 4px 18px rgba(68,11,25,.22);
  }
  .pill-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 18px; height: 18px; padding: 0 5px; border-radius: 99px;
    background: rgba(68,11,25,.12); font-size: .6rem; font-weight: 600; letter-spacing: 0;
  }
  .sous-cat-pill.active .pill-count { background: rgba(255,255,255,.22); }

  /* ── QV shade dots ── */
  .qv-shade-dot {
    width: 34px; height: 34px; border-radius: 50%;
    border: 2px solid transparent; cursor: pointer; flex-shrink: 0;
    position: relative; overflow: hidden; display: inline-block;
    transition: border-color .2s, transform .2s, box-shadow .2s;
  }
  .qv-shade-dot:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(68,11,25,.20); }
  .qv-shade-dot.active { border-color: #440B19; box-shadow: 0 6px 16px rgba(68,11,25,.28); }
  .qv-shade-dot img {
    width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block;
  }
  .qv-shade-dot--oos { opacity: 0.38; cursor: not-allowed; }
  .qv-shade-dot--oos::after {
    content: ''; position: absolute; inset: 0; border-radius: 50%;
    background: repeating-linear-gradient(45deg,transparent,transparent 3px,rgba(0,0,0,.28) 3px,rgba(0,0,0,.28) 4px);
  }

  /* Nom de teinte sélectionnée dans QV */
  .qv-shades-header { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
  .qv-shades-title { font-size: .75rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #440B19; }
  .qv-selected-shade-name { font-size: .82rem; color: #5c1225; font-style: italic; }

  /* ── ISP dots (picker inline) ── */
  .isp-dot { position: relative; overflow: hidden; }
  .isp-dot--oos { opacity: 0.38; cursor: not-allowed; }
  .isp-dot--oos::after {
    content: ''; position: absolute; inset: 0; border-radius: 50%;
    background: repeating-linear-gradient(45deg,transparent,transparent 3px,rgba(0,0,0,.28) 3px,rgba(0,0,0,.28) 4px);
  }

  /* ── Mobile QV ── */
  @media (max-width: 600px) {
    .qv-overlay { align-items: flex-end !important; padding: 0 !important; }
    .qv-modal {
      width: 100% !important; max-width: 100% !important; max-height: 90vh !important;
      border-radius: 18px 18px 0 0 !important; flex-direction: column !important;
      overflow-y: auto; padding: 16px 14px 28px !important; gap: 14px !important;
    }
    .qv-col-image {
      width: 100%; max-height: 220px; display: flex; align-items: center;
      justify-content: center; border-radius: 10px; overflow: hidden; flex-shrink: 0;
    }
    #qvImg { max-height: 200px; width: auto; max-width: 100%; object-fit: contain; }
    .qv-col-info { width: 100%; }
    .qv-name { font-size: 1.1rem !important; }
    .qv-brand { font-size: .7rem !important; }
    .qv-price { font-size: .95rem !important; }
    .qv-description {
      font-size: .82rem;
      display: -webkit-box; -webkit-line-clamp: 3;
      -webkit-box-orient: vertical; overflow: hidden;
    }
    .qv-shades-row { gap: 8px; }
    .qv-shade-dot { width: 30px !important; height: 30px !important; }
    .qv-cart-btn { width: 100%; padding: 13px !important; font-size: .75rem !important; }
    .qv-close { top: 12px !important; right: 14px !important; font-size: 22px !important; }
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
        <input type="checkbox" id="filterSale"><span class="slider"></span>
      </label>
    </div>
    <div class="filter-group toggle-group">
      <span>En stock uniquement</span>
      <label class="switch">
        <input type="checkbox" id="filterInStock"><span class="slider"></span>
      </label>
    </div>
    <div class="filter-group">
      <label for="filterBrand">Marque</label>
      <select id="filterBrand"><option value="">Toutes</option></select>
    </div>
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
    <h1>
      <?php
        if ($pack)               echo 'Coffrets';
        elseif ($soldes)         echo 'Promotions';
        elseif ($sous_categorie) echo htmlspecialchars($categorie) . ' — ' . htmlspecialchars($sous_categorie);
        else                     echo htmlspecialchars($categorie);
      ?>
    </h1>

    <nav class="breadcrumb">
      <a href="<?= $b ?>/index.php">Accueil</a> &gt;
      <?php if ($pack): ?>
        <span>Coffrets</span>
      <?php elseif ($soldes): ?>
        <span>Promotions</span>
      <?php elseif ($categorie === 'Tous'): ?>
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

    <?php if (!empty($sous_cats)): ?>
    <div class="sous-cats-bar">
      <?php
        $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE categorie = ?");
        $totalStmt->execute([$categorie]);
        $totalCat = (int)$totalStmt->fetchColumn();
      ?>
      <a href="<?= $b ?>/categorie.php?categorie=<?= urlencode($categorie) ?>"
         class="sous-cat-pill <?= $sous_categorie === '' ? 'active' : '' ?>">
        Tout <span class="pill-count"><?= $totalCat ?></span>
      </a>
      <?php foreach ($sous_cats as $sc): ?>
      <a href="<?= $b ?>/categorie.php?categorie=<?= urlencode($categorie) ?>&sous_categorie=<?= urlencode($sc['sous_categorie']) ?>"
         class="sous-cat-pill <?= $sous_categorie === $sc['sous_categorie'] ? 'active' : '' ?>">
        <?= htmlspecialchars($sc['sous_categorie']) ?>
        <span class="pill-count"><?= (int)$sc['cnt'] ?></span>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <button class="filter-toggle-btn" id="filterToggleBtn">
      <i class="fas fa-sliders-h"></i> Filtres
    </button>

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
          : (str_starts_with($product['image_url'], 'http')
              ? $product['image_url']
              : $b . '/images/' . basename($product['image_url']));

        /* Teintes — colonnes exactes de la table */
        $shadeStmt = $pdo->prepare("
            SELECT nom_teinte, code_couleur,
                   image,
                   COALESCE(stock, 0) AS stock
            FROM teintes
            WHERE product_id = ?
        ");
        $shadeStmt->execute([$productId]);
        $rawShades = $shadeStmt->fetchAll(PDO::FETCH_ASSOC);

        $productShades = [];
        foreach ($rawShades as $shade) {
            $raw = !empty($shade['image']) ? $shade['image'] : null;
            $shade['_img_src'] = $raw
                ? (str_starts_with($raw, 'http') ? $raw : $b . '/images/' . basename($raw))
                : null;
            $shade['stock'] = (int)$shade['stock'];
            $productShades[] = $shade;
        }

        $hasShades = !empty($productShades);
        $oldPrice  = $product['old_price'] ?? '';
      ?>

      <div class="product-card"
           data-price="<?= $product['price'] ?>"
           data-brand="<?= htmlspecialchars($product['marque'] ?? '') ?>"
           data-sale="<?= (!empty($oldPrice) && $oldPrice > $product['price']) ? '1' : '0' ?>"
           data-stock="<?= $stock ?>"
           data-instock="<?= $outOfStock ? '0' : '1' ?>">

        <div class="product-image-wrapper">
          <?php if ($outOfStock): ?>
            <span class="badge-oos">Rupture</span>
          <?php elseif ($stock <= 5): ?>
            <span class="badge-low">Stock limité</span>
          <?php endif; ?>
          <?php if (!empty($oldPrice) && $oldPrice > $product['price']): ?>
            <?php $pct = round((1 - $product['price'] / $oldPrice) * 100); ?>
            <span class="badge-sale">-<?= $pct ?>%</span>
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
                  type="button" aria-label="Ajouter aux favoris">
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
                  type="button" aria-label="Aperçu rapide">
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

          <!-- Pastilles teintes -->
          <?php if (!empty($productShades)): ?>
            <div class="card-shades">
              <?php foreach (array_slice($productShades, 0, 6) as $shade):
                $oosClass = $shade['stock'] === 0 ? ' shade-oos' : '';
              ?>
                <?php if ($shade['_img_src']): ?>
                  <span class="card-shade-dot card-shade-dot--img<?= $oosClass ?>"
                        title="<?= htmlspecialchars($shade['nom_teinte']) ?>"
                        style="background-image:url('<?= htmlspecialchars($shade['_img_src']) ?>')">
                  </span>
                <?php else: ?>
                  <span class="card-shade-dot<?= $oosClass ?>"
                        style="background:<?= htmlspecialchars($shade['code_couleur'] ?? '#ccc') ?>"
                        title="<?= htmlspecialchars($shade['nom_teinte']) ?>">
                  </span>
                <?php endif; ?>
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
                    type="button" <?= $outOfStock ? 'disabled' : '' ?>>
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
                    type="button" <?= $outOfStock ? 'disabled' : '' ?>>
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

<!-- ══ QUICK VIEW ══ -->
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
        <div class="qv-shades-header">
          <span class="qv-shades-title">Teinte :</span>
          <span class="qv-selected-shade-name" id="qvSelectedShadeName"></span>
        </div>
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
/* ══ Sidebar filtres mobile ══ */
document.addEventListener('DOMContentLoaded', () => {
  const sidebar   = document.getElementById('filterSidebar');
  const toggleBtn = document.querySelector('.filter-toggle-btn');
  const overlay   = document.querySelector('.filter-overlay');
  toggleBtn?.addEventListener('click', () => { sidebar.classList.add('active'); overlay.classList.add('active'); });
  overlay?.addEventListener('click', () => { sidebar.classList.remove('active'); overlay.classList.remove('active'); });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { sidebar.classList.remove('active'); overlay.classList.remove('active'); }
  });
});

/* ══ Vue grille / liste ══ */
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
</script>

<?php include 'includes/product_modal.php'; ?>
<script src="<?= $b ?>/js/shop.js?v=<?= time() ?>"></script>
<script src="/js/checkout.js?v=3" defer></script>
<?php include 'includes/footer.php'; ?>
</body>
</html>