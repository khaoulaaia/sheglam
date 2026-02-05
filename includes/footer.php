<footer class="luxury-footer">
  <div class="footer-container">

    <!-- COLONNE 1 -->
    <div class="footer-col">
      <h4>SheGlamour</h4>
      <p>L’élégance du maquillage.<br>Le luxe au quotidien.</p>
    </div>

    <!-- COLONNE 2 -->
    <div class="footer-col">
      <h4>Maison</h4>
      <ul>
        <li><a href="#">À propos</a></li>
        <li><a href="#">Nos produits</a></li>
        <li><a href="#">Nouveautés</a></li>
        <li><a href="#">Contact</a></li>
      </ul>
    </div>

    <!-- COLONNE 3 -->
    <div class="footer-col">
      <h4>Service Client</h4>
      <ul>
        <li><a href="#">Livraison & retours</a></li>
        <li><a href="#">Conditions générales</a></li>
        <li><a href="#">Politique de confidentialité</a></li>
      </ul>
    </div>

    <!-- COLONNE 4 -->
    <div class="footer-col">
      <h4>Suivez-nous</h4>
      <div class="footer-socials">
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-tiktok"></i></a>
      </div>
    </div>

  </div>

  <div class="footer-bottom">
    <p>© <?= date('Y'); ?> SheGlamour — Tous droits réservés</p>
  </div>
</footer>
<style>
    .luxury-footer {
  background: #000;
  color: #fff;
  padding: 70px 0 30px;
  font-family: 'Poppins', sans-serif;
}

.footer-container {
  max-width: 1200px;
  margin: auto;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 50px;
  padding: 0 30px;
}

.footer-col h4 {
  font-size: 14px;
  letter-spacing: 2px;
  margin-bottom: 20px;
  text-transform: uppercase;
}

.footer-col p {
  font-size: 14px;
  color: #bbb;
  line-height: 1.8;
}

.footer-col ul {
  list-style: none;
  padding: 0;
}

.footer-col ul li {
  margin-bottom: 12px;
}

.footer-col ul li a {
  color: #bbb;
  font-size: 14px;
  text-decoration: none;
  transition: color 0.3s;
}

.footer-col ul li a:hover {
  color: #fff;
}

.footer-socials {
  display: flex;
  gap: 18px;
}

.footer-socials a {
  color: #fff;
  font-size: 18px;
  transition: transform 0.3s, opacity 0.3s;
}

.footer-socials a:hover {
  transform: translateY(-3px);
  opacity: 0.7;
}

.footer-bottom {
  margin-top: 60px;
  text-align: center;
  font-size: 13px;
  color: #777;
  border-top: 1px solid rgba(255,255,255,0.1);
  padding-top: 20px;
}

</style>