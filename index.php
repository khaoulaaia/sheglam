<?php
include_once 'includes/db.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SheGlamour</title>
  <link rel="stylesheet" href="modal.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">

</head>
<body>
<?php include_once 'includes/sidebar.php';
 ?>


 
<header class="header">
  <div class="menu-toggle" id="menu-toggle">
    <i class="fas fa-bars"></i>
  </div>

  <div class="logo">SheGlamour</div>

  <nav class="navbar" id="navbar">
    <a href="#">Accueil</a>
    <a href="sheglam/products">Boutique</a>
    <a href="#">Nouveautés</a>
    <a href="#">Contact</a>
  </nav>

 <form class="search-bar" action="recherche.php" method="get">
  <input type="text" name="q" placeholder="Rechercher un produit...">
  <button type="submit"><i class="fas fa-search"></i></button>
</form>
<div class="icons">
  <i class="fas fa-search mobile-search-icon"></i> 
  <a href="wishlist.php" title="Ma wishlist"><i class="fas fa-heart"></i></a>
  <a href="cart.php" title="Mon panier"><i class="fas fa-shopping-bag"></i></a>
  <a href="login.php" title="Mon compte"><i class="fas fa-user"></i></a>
</div>


</header>
<!-- Pop-up de recherche mobile -->
<div id="mobileSearchPopup" class="mobile-search-popup">
  <div class="mobile-search-content">
    <div class="search-header">
      <input type="text" id="mobileSearchInput" placeholder="Rechercher un produit...">
      <button id="closeSearchPopup"><i class="fas fa-times"></i></button>
    </div>
    <div id="mobileSearchResults"></div>
  </div>
</div>


<!-- Overlay (arrière-plan semi-transparent pour mobile) -->
<div class="overlay" id="overlay"></div>

<script>
  const menuToggle = document.getElementById('menu-toggle');
  const navbar = document.getElementById('navbar');
  const overlay = document.getElementById('overlay');

  // Ouvrir / fermer le menu
  menuToggle.addEventListener('click', () => {
    navbar.classList.toggle('active');
    overlay.classList.toggle('active');
    menuToggle.classList.toggle('active');
  });

  // Fermer le menu quand on clique sur l’overlay
  overlay.addEventListener('click', () => {
    navbar.classList.remove('active');
    overlay.classList.remove('active');
    menuToggle.classList.remove('active');
  });

  // Optionnel : fermer le menu quand on clique sur un lien
  document.querySelectorAll('.navbar a').forEach(link => {
    link.addEventListener('click', () => {
      navbar.classList.remove('active');
      overlay.classList.remove('active');
      menuToggle.classList.remove('active');
    });
  });
</script>
<section class="hero-slider">
  <div class="slide active" style="background-image: url('images/:1766567932171684802b501201ae09c27d1f505da0_thumbnail_3600x.webp');">
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
  <a href="/sheglam/produits/Yeux">
    <img src="images/look-eyes.jpg" alt="Maquillage des yeux">
    <div class="overlay"><h3>Yeux</h3></div>
  </a>
</div>

<div class="create-item">
  <a href="/sheglam/produits/Lèvres">
    <img src="images/look-lips.jpg" alt="Maquillage des lèvres">
    <div class="overlay"><h3>Lèvres</h3></div>
  </a>
</div>

<div class="create-item">
  <a href="/sheglam/produits/Teint">
    <img src="images/look-face.jpg" alt="Maquillage du teint">
    <div class="overlay"><h3>Teint</h3></div>
  </a>
</div>

<div class="create-item">
  <a href="/sheglam/produits/Accessoires">
    <img src="images/look-accessories.jpg" alt="Accessoires makeup">
    <div class="overlay"><h3>Accessoires</h3></div>
  </a>
</div>

</section>



<!-- SECTION PRODUITS -->
<section class="worth-hype">
  <div class="hype-left">
    <img src="images/worth-main.jpg" alt="Worth the Hype SheGlamour">
  </div>

  <div class="hype-right">
    <h2>Worth the Hype</h2>
    <div class="hype-products">
     <?php
$stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $productId = $row['id'];

    // Récupérer les teintes associées à ce produit
    $shadeStmt = $pdo->prepare("SELECT * FROM teintes WHERE product_id = ?");
    $shadeStmt->execute([$productId]);
    $shades = $shadeStmt->fetchAll(PDO::FETCH_ASSOC);

    echo '
    <div class="product-card">
        <div class="product-image-wrapper">
            <a href="product.php?id=' . $row['id'] . '">
                <img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['name']) . '">
            </a>
            <button class="add-to-wishlist"
                data-name="' . htmlspecialchars($row['name']) . '"
                data-price="' . htmlspecialchars($row['price']) . '"
                data-image="' . htmlspecialchars($row['image_url']) . '">
                <i class="fas fa-heart"></i>
            </button>
        </div>

        <div class="product-info">
            <h3><a href="product.php?id=' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</a></h3>
            <p class="price">€' . number_format($row['price'], 2, ',', ' ') . '</p>';
    
    // Si le produit a des teintes, seul le bouton "Choisir une teinte"
    if ($shades) {
        echo '
            <button class="choose-shade-btn" data-product-id="' . $productId . '">Choisir une teinte</button>';
    } else {
        // Si pas de teintes → bouton Ajouter au panier
        echo '
            <button class="add-to-cart"
                data-name="' . htmlspecialchars($row['name']) . '"
                data-price="' . htmlspecialchars($row['price']) . '"
                data-image="' . htmlspecialchars($row['image_url']) . '">
                <i class="fas fa-shopping-bag"></i> Ajouter au panier
            </button>';
    }

    echo '
        </div> <!-- fin product-info -->
    </div> <!-- fin product-card -->';
}

?>


    </div>
  </div>
</section>



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

<!-- MODAL TEINTES -->
<div id="shadeModal" class="shade-modal" aria-hidden="true">
  <div class="shade-modal-content">
    <button class="close-modal" aria-label="Fermer">&times;</button>
    <div class="modal-product-info">
      <img id="shadeProductImage" src="" alt="Produit" class="modal-product-img" style="max-width:80px; border-radius:8px; margin-right:15px;">
      <div style="display:inline-block; vertical-align:top;">
        <h3 id="shadeProductName"></h3>
        <p id="shadeProductPrice" class="price"></p>
      </div>
    </div>
    <hr>
    <h4>Choisissez votre teinte :</h4>
    <div id="shade-options-container"></div>
    <button id="confirmShadeBtn" class="btn">Ajouter au panier</button>
  </div>
</div>




<script>
document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.querySelector(".search-bar input");
  const resultsBox = document.createElement("div");
  resultsBox.className = "search-results";
  document.querySelector(".search-bar").appendChild(resultsBox);

  let timer;

  searchInput.addEventListener("input", () => {
    clearTimeout(timer);
    const query = searchInput.value.trim();

    if (query.length < 2) {
      resultsBox.innerHTML = "";
      resultsBox.style.display = "none";
      return;
    }

    timer = setTimeout(async () => {
      try {
        const response = await fetch(`includes/search_products.php?query=${encodeURIComponent(query)}`);
        const products = await response.json();

        if (!products || products.length === 0) {
          resultsBox.innerHTML = "<p class='no-results'>Aucun produit trouvé.</p>";
        } else {
          resultsBox.innerHTML = products.map(p => `
            <div class="search-item" data-id="${p.id}">
              <img src="${p.image_url}" alt="${p.name}">
              <div class="search-info">
                <h4>${p.name}</h4>
                <p>€${parseFloat(p.price).toFixed(2)}</p>
              </div>
            </div>
          `).join('');
        }

        resultsBox.style.display = "block";
      } catch (err) {
        console.error("Erreur de recherche :", err);
      }
    }, 300); // délai pour éviter trop de requêtes
  });

  // Cacher la liste quand on clique ailleurs
  document.addEventListener("click", (e) => {
    if (!document.querySelector(".search-bar").contains(e.target)) {
      resultsBox.style.display = "none";
    }
  });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const searchIcon = document.querySelector(".mobile-search-icon");
  const popup = document.getElementById("mobileSearchPopup");
  const closeBtn = document.getElementById("closeSearchPopup");
  const searchInput = document.getElementById("mobileSearchInput");
  const resultsBox = document.getElementById("mobileSearchResults");

  // Ouvrir le pop-up sur mobile
  searchIcon.addEventListener("click", (e) => {
    if (window.innerWidth <= 768) { // seulement mobile
      e.preventDefault();
      popup.classList.add("active");
      searchInput.focus();
    }
  });

  // Fermer le pop-up
  closeBtn.addEventListener("click", () => {
    popup.classList.remove("active");
    resultsBox.innerHTML = "";
  });

  // Fermer si clic hors du contenu
  popup.addEventListener("click", (e) => {
    if (e.target === popup) popup.classList.remove("active");
  });

  // Recherche en direct (AJAX)
  let timer;
  searchInput.addEventListener("input", () => {
    clearTimeout(timer);
    const query = searchInput.value.trim();
    if (query.length < 2) {
      resultsBox.innerHTML = "";
      return;
    }

    timer = setTimeout(async () => {
      try {
        const res = await fetch(`includes/search_products.php?query=${encodeURIComponent(query)}`);
        const products = await res.json();

        if (!products.length) {
          resultsBox.innerHTML = "<p>Aucun produit trouvé.</p>";
        } else {
          resultsBox.innerHTML = products.map(p => `
            <div class="search-item" data-id="${p.id}">
              <img src="${p.image_url}" alt="${p.name}">
              <div>
                <h4>${p.name}</h4>
                <p>€${parseFloat(p.price).toFixed(2)}</p>
              </div>
            </div>
          `).join("");
        }
      } catch (err) {
        console.error("Erreur :", err);
      }
    }, 300);
  });
});
</script>
</script>
<script src="sidebar.js"></script>
 <?php include_once 'includes/product_modal.php'; ?>
 <script src="/sheglam/js/product_modal.js"></script>


</body>
</html>
