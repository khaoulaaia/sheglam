<?php
/**
 * sitemap.php — Placer à la racine du site
 * Accès : https://sheglamour.fr/sitemap.php
 * Génère un sitemap XML dynamique à partir de la base de données.
 */
include_once 'includes/config.php';
include_once 'includes/db.php';

header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';

$today = date('Y-m-d');
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

  <!-- Pages statiques -->
  <url>
    <loc>https://sheglamour.fr/</loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
    <lastmod><?= $today ?></lastmod>
  </url>
  <url>
    <loc>https://sheglamour.fr/categorie.php?categorie=Tous</loc>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
    <lastmod><?= $today ?></lastmod>
  </url>
  <url>
    <loc>https://sheglamour.fr/categorie.php?categorie=Yeux</loc>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
  <url>
    <loc>https://sheglamour.fr/categorie.php?categorie=L%C3%A8vres</loc>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
  <url>
    <loc>https://sheglamour.fr/categorie.php?categorie=Teint</loc>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
  <url>
    <loc>https://sheglamour.fr/categorie.php?categorie=Accessoires</loc>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>

  <!-- Pages produits dynamiques -->
  <?php
  $stmt = $pdo->query("SELECT id, name, updated_at FROM products ORDER BY id ASC");
  while ($p = $stmt->fetch(PDO::FETCH_ASSOC)):
    $lastmod = !empty($p['updated_at']) ? date('Y-m-d', strtotime($p['updated_at'])) : $today;
  ?>
  <url>
    <loc>https://sheglamour.fr/product.php?id=<?= (int)$p['id'] ?></loc>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
    <lastmod><?= $lastmod ?></lastmod>
  </url>
  <?php endwhile; ?>

</urlset>