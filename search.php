<?php
include 'includes/db.php';
include_once 'includes/config.php';
$b = BASE_URL;

$q = trim($_GET['q'] ?? '');
$products = [];
$total = 0;

if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare("
        SELECT * FROM products
        WHERE name LIKE :q OR description LIKE :q OR marque LIKE :q OR categorie LIKE :q
        ORDER BY id ASC
    ");
    $stmt->execute(['q' => $like]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total = count($products);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Recherche : "<?= htmlspecialchars($q) ?>" – SheGlamour</title>

  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="<?= $b ?>/categorie.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="<?= $b ?>/search.css?v=<?= time() ?>" />
</head>
<body>
<style>
  /* ══════════════════════════════════════════════════════════
   SEARCH — Résultats overlay + page search.php
   Modern Old Money · Palette Beige & Marron
   ══════════════════════════════════════════════════════════ */

/* ══ CARTE PRODUIT DANS L'OVERLAY ════════════════════════════ */

.search-product {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 10px;
  padding: 14px 10px 16px;
  background: var(--glass);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid var(--border-s);
  cursor: pointer;
  text-decoration: none;
  color: inherit;
  transition:
    border-color 0.3s var(--ease),
    transform 0.35s var(--ease),
    background 0.3s var(--ease),
    box-shadow 0.35s var(--ease);
  position: relative;
  overflow: hidden;
}

.search-product::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 1.5px;
  background: var(--gold);
  transition: width 0.35s var(--ease);
}

.search-product:hover {
  border-color: rgba(214, 196, 168, 0.8);
  transform: translateY(-4px);
  background: rgba(250, 246, 240, 0.92);
  box-shadow: 0 14px 36px var(--dark-12);
}

.search-product:hover::after { width: 50%; }

.search-product img {
  width: 100%;
  aspect-ratio: 1 / 1;
  object-fit: contain;
  background: rgba(255, 255, 255, 0.6);
  border: 1px solid var(--border-s);
  display: block;
  transition: transform 0.45s var(--ease);
}

.search-product:hover img { transform: scale(1.06); }

.search-product p {
  font-family: var(--serif);
  font-size: 12px;
  font-weight: 400;
  font-style: italic;
  letter-spacing: 0.04em;
  color: var(--dark);
  line-height: 1.4;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.search-product strong {
  font-family: var(--sans);
  font-size: 11px;
  font-weight: 500;
  color: var(--gold);
  letter-spacing: 0.08em;
}

/* ══ MESSAGE VIDE / ERREUR ═══════════════════════════════════ */

.search-empty {
  grid-column: 1 / -1;
  text-align: center;
  padding: 40px 20px;
  font-family: var(--serif);
  font-size: 16px;
  font-style: italic;
  color: var(--muted);
  letter-spacing: 0.04em;
}

/* ══════════════════════════════════════════════════════════
   PAGE SEARCH.PHP — résultats pleine page
   ══════════════════════════════════════════════════════════ */

/* ── Hero résultats ── */
.search-page-hero {
  margin-top: 90px;
  padding: 48px 6% 38px;
  background: var(--glass-s);
  backdrop-filter: blur(16px) saturate(160%);
  -webkit-backdrop-filter: blur(16px) saturate(160%);
  border-bottom: 1px solid var(--border-s);
  text-align: center;
  position: relative;
}

.search-page-hero::before {
  content: "✦  ✦  ✦";
  position: absolute;
  top: 18px;
  left: 6%;
  font-size: 9px;
  color: var(--gold);
  letter-spacing: 8px;
  opacity: .4;
}

.search-page-hero::after {
  content: "✦  ✦  ✦";
  position: absolute;
  top: 18px;
  right: 6%;
  font-size: 9px;
  color: var(--gold);
  letter-spacing: 8px;
  opacity: .4;
}

.search-page-hero h1 {
  font-family: var(--serif);
  font-size: clamp(26px, 4vw, 40px);
  font-weight: 300;
  font-style: italic;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--dark);
  line-height: 1.1;
  margin-bottom: 8px;
}

.search-page-hero h1 em {
  font-style: normal;
  color: var(--gold);
}

.search-page-hero .search-count {
  font-family: var(--sans);
  font-size: 9px;
  letter-spacing: 0.3em;
  text-transform: uppercase;
  color: var(--muted);
}

/* ── Barre de recherche intégrée à la page ── */
.search-page-bar {
  max-width: 560px;
  margin: 28px auto 0;
  display: flex;
  align-items: center;
  gap: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--border-s);
  transition: border-color 0.25s;
}

.search-page-bar:focus-within { border-color: var(--gold); }

.search-page-bar svg {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
  color: var(--muted);
  transition: color 0.25s;
}

.search-page-bar:focus-within svg { color: var(--gold); }

.search-page-bar input {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  font-family: var(--serif);
  font-size: 1.3rem;
  font-weight: 300;
  font-style: italic;
  letter-spacing: 0.04em;
  color: var(--dark);
}

.search-page-bar input::placeholder {
  color: var(--muted);
  font-style: italic;
}

/* ── Layout de la page ── */
.search-page-body {
  max-width: 1280px;
  margin: 48px auto 100px;
  padding: 0 48px;
}

/* ── Breadcrumb ── */
.search-breadcrumb {
  font-family: var(--sans);
  font-size: 9.5px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 32px;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
}

.search-breadcrumb a { color: var(--dark-60); transition: color 0.2s; }
.search-breadcrumb a:hover { color: var(--gold); }
.search-breadcrumb .sep { color: var(--dark-20); }
.search-breadcrumb .cur { color: var(--dark-80); font-weight: 500; }

/* ── Grille résultats pleine page ── */
.search-results-page {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 28px;
}

/* ── Carte produit pleine page (réutilise product-card) ── */
.search-results-page .product-card {
  background: var(--glass);
  backdrop-filter: blur(12px) saturate(160%);
  -webkit-backdrop-filter: blur(12px) saturate(160%);
  border: 1px solid var(--border-s);
  border-radius: 1px;
  padding: 18px;
  position: relative;
  text-align: center;
  transition:
    border-color 0.35s var(--ease),
    transform 0.4s var(--ease),
    box-shadow 0.4s var(--ease),
    background 0.35s;
  overflow: hidden;
}

.search-results-page .product-card::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 1.5px;
  background: var(--gold);
  transition: width 0.4s var(--ease);
}

.search-results-page .product-card:hover {
  border-color: rgba(214, 196, 168, 0.8);
  transform: translateY(-6px);
  background: rgba(250, 246, 240, 0.9);
  box-shadow: 0 20px 48px var(--dark-12);
}

.search-results-page .product-card:hover::after { width: 60%; }

.search-results-page .product-card img {
  width: 100%;
  height: 260px;
  object-fit: contain;
  border: 1px solid var(--border-s);
  background: rgba(255, 255, 255, 0.6);
  display: block;
  transition: transform 0.55s var(--ease);
}

.search-results-page .product-card:hover img { transform: scale(1.05); }

.search-results-page .product-card h3 {
  font-family: var(--serif);
  font-size: 14px;
  font-weight: 400;
  font-style: italic;
  letter-spacing: 0.04em;
  color: var(--dark);
  margin: 14px 0 8px;
  line-height: 1.5;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.search-results-page .price {
  font-family: var(--sans);
  font-size: 13px;
  font-weight: 500;
  color: var(--dark);
  letter-spacing: 0.06em;
}

.search-results-page .old-price {
  font-size: 11px;
  text-decoration: line-through;
  color: var(--muted);
  margin-right: 6px;
}

.search-results-page .sale-price { color: var(--gold); }

/* ── Aucun résultat ── */
.search-no-results {
  grid-column: 1 / -1;
  text-align: center;
  padding: 80px 20px;
}

.search-no-results i {
  font-size: 40px;
  color: var(--beige-d);
  display: block;
  margin-bottom: 20px;
}

.search-no-results p {
  font-family: var(--serif);
  font-size: 20px;
  font-style: italic;
  color: var(--muted);
  margin-bottom: 10px;
}

.search-no-results span {
  font-family: var(--sans);
  font-size: 9.5px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--dark-40);
}

/* ── Suggestions de recherche ── */
.search-suggestions {
  margin-top: 48px;
}

.search-suggestions h3 {
  font-family: var(--sans);
  font-size: 8.5px;
  font-weight: 600;
  letter-spacing: 0.32em;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 16px;
  position: relative;
  padding-bottom: 12px;
}

.search-suggestions h3::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 20px;
  height: 1px;
  background: var(--gold);
}

.suggestion-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.suggestion-tags .tag {
  padding: 8px 18px;
  border: 1px solid var(--border-s);
  border-radius: 100px;
  background: var(--glass);
  backdrop-filter: blur(8px);
  color: var(--marron);
  font-family: var(--sans);
  font-size: 10px;
  font-weight: 400;
  letter-spacing: 0.1em;
  cursor: pointer;
  text-decoration: none;
  transition:
    background 0.3s var(--ease),
    border-color 0.3s,
    color 0.3s,
    transform 0.3s var(--ease),
    box-shadow 0.3s;
}

.suggestion-tags .tag:hover {
  background: var(--marron);
  border-color: var(--marron);
  color: var(--beige-l);
  transform: translateY(-2px);
  box-shadow: 0 8px 20px var(--dark-12);
}

/* ══ RESPONSIVE ══════════════════════════════════════════════ */

@media (max-width: 992px) {
  .search-results-page {
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }
  .search-page-body { padding: 0 28px; margin-top: 36px; }
}

@media (max-width: 768px) {
  .search-results-page {
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
  }
  .search-page-body { padding: 0 16px; margin-top: 24px; }
  .search-results-page .product-card img { height: 180px; }
  .search-page-hero { padding: 36px 6% 28px; }
  .search-page-bar input { font-size: 1.1rem; }
}
</style>
<script>const BASE_URL = "<?= $b ?>";</script>

<?php include 'includes/sidebar.php'; ?>
<?php include 'includes/header.php'; ?>

<!-- ── Hero ── -->
<div class="search-page-hero">
  <h1>
    <?php if ($q !== ''): ?>
      Résultats pour <em>"<?= htmlspecialchars($q) ?>"</em>
    <?php else: ?>
      Recherche
    <?php endif; ?>
  </h1>
  <?php if ($q !== ''): ?>
    <p class="search-count">
      <?= $total ?> produit<?= $total > 1 ? 's' : '' ?> trouvé<?= $total > 1 ? 's' : '' ?>
    </p>
  <?php endif; ?>

  <!-- Barre de recherche pour affiner -->
  <form class="search-page-bar" method="GET" action="<?= $b ?>/search.php">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="10.5" cy="10.5" r="6.5"/><line x1="15.5" y1="15.5" x2="21" y2="21"/>
    </svg>
    <input
      type="text"
      name="q"
      value="<?= htmlspecialchars($q) ?>"
      placeholder="Affiner la recherche…"
      autocomplete="off"
    />
  </form>
</div>

<!-- ── Body ── -->
<div class="search-page-body">

  <!-- Breadcrumb -->
  <nav class="search-breadcrumb">
    <a href="<?= $b ?>/index.php">Accueil</a>
    <span class="sep">›</span>
    <span class="cur">Recherche<?= $q !== '' ? ' : "' . htmlspecialchars($q) . '"' : '' ?></span>
  </nav>

  <!-- Résultats -->
  <div class="search-results-page">

    <?php if ($q === ''): ?>
      <div class="search-no-results">
        <i class="fas fa-search"></i>
        <p>Entrez un terme pour commencer</p>
        <span>Produits, marques, catégories…</span>
      </div>

    <?php elseif ($total === 0): ?>
      <div class="search-no-results">
        <i class="fas fa-face-sad-tear"></i>
        <p>Aucun résultat pour "<?= htmlspecialchars($q) ?>"</p>
        <span>Essayez avec d'autres mots-clés</span>
      </div>

    <?php else: ?>
      <?php foreach ($products as $product):
        $productId = $product['id'];
        $imagePath = empty($product['image_url'])
          ? $b . '/images/placeholder.jpg'
          : (str_starts_with($product['image_url'], 'http')
              ? $product['image_url']
              : $b . '/images/' . basename($product['image_url']));

        $shadeStmt = $pdo->prepare("SELECT COUNT(*) FROM teintes WHERE product_id = ?");
        $shadeStmt->execute([$productId]);
        $hasShades = $shadeStmt->fetchColumn() > 0;
      ?>

      <a href="<?= $b ?>/product.php?id=<?= $productId ?>" class="product-card-link">
        <div class="product-card"
             data-price="<?= $product['price'] ?>"
             data-brand="<?= htmlspecialchars($product['marque'] ?? '') ?>">

          <div class="product-image-wrapper">
            <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy" />
            <button class="add-to-wishlist"
                    data-product-id="<?= $productId ?>"
                    data-name="<?= htmlspecialchars($product['name']) ?>"
                    data-price="<?= htmlspecialchars($product['price']) ?>"
                    data-image_url="<?= htmlspecialchars($imagePath) ?>"
                    data-has-shades="<?= $hasShades ? 1 : 0 ?>"
                    type="button">
              <i class="fas fa-heart"></i>
            </button>
          </div>

          <div class="product-info">
            <h3><?= htmlspecialchars($product['name']) ?></h3>
            <p class="price">
              <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
                <span class="old-price"><?= number_format($product['old_price'], 2, ',', ' ') ?>DA</span>
                <span class="sale-price"><?= number_format($product['price'], 2, ',', ' ') ?>DA</span>
              <?php else: ?>
                <?= number_format($product['price'], 2, ',', ' ') ?>DA
              <?php endif; ?>
            </p>

            <?php if ($hasShades): ?>
              <button class="choose-shade-btn"
                      data-product-id="<?= $productId ?>"
                      data-name="<?= htmlspecialchars($product['name']) ?>"
                      data-price="<?= htmlspecialchars($product['price']) ?>"
                      data-image_url="<?= htmlspecialchars($imagePath) ?>"
                      type="button">
                <i class="fas fa-palette"></i> Choisir une teinte
              </button>
            <?php else: ?>
              <button class="add-to-cart"
                      data-product-id="<?= $productId ?>"
                      data-name="<?= htmlspecialchars($product['name']) ?>"
                      data-price="<?= htmlspecialchars($product['price']) ?>"
                      data-image_url="<?= htmlspecialchars($imagePath) ?>"
                      type="button">
                <i class="fas fa-shopping-bag"></i> Ajouter au panier
              </button>
            <?php endif; ?>
          </div>

        </div>
      </a>

      <?php endforeach; ?>
    <?php endif; ?>

  </div><!-- .search-results-page -->

  <!-- Suggestions si pas de résultat -->
  <?php if ($q !== '' && $total === 0): ?>
  <div class="search-suggestions">
    <h3>Suggestions</h3>
    <div class="suggestion-tags">
      <?php
        $cats = $pdo->query("SELECT DISTINCT categorie FROM products LIMIT 8");
        while ($cat = $cats->fetch(PDO::FETCH_ASSOC)):
      ?>
        <a href="<?= $b ?>/categorie.php?categorie=<?= urlencode($cat['categorie']) ?>" class="tag">
          <?= htmlspecialchars($cat['categorie']) ?>
        </a>
      <?php endwhile; ?>
    </div>
  </div>
  <?php endif; ?>

</div><!-- .search-page-body -->

<!-- Modal teintes -->
<?php include 'includes/product_modal.php'; ?>
<script src="<?= $b ?>/js/shop.js?v=<?= time() ?>"></script>

<!-- Empêche le lien sur boutons d'action -->
<script>
document.addEventListener("click", e => {
  if (e.target.closest(".add-to-wishlist, .add-to-cart, .choose-shade-btn")) {
    e.preventDefault();
    e.stopPropagation();
  }
});
</script>


<?php include 'includes/footer.php'; ?>
</body>
</html>