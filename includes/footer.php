<footer class="luxury-footer">
  <div class="footer-top">

    <div class="footer-brand">
      <h3 class="footer-logo">SheGlamour</h3>
      <p>L'élégance du maquillage.<br>Le luxe au quotidien.</p>
      <div class="footer-socials">
        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
      </div>
    </div>

    <div class="footer-col">
      <p class="footer-col-title">Boutique</p>
      <ul>
        <li><a href="#">Nouveautés</a></li>
        <li><a href="#">Promotions</a></li>
        <li><a href="#">Coffrets</a></li>
        <li><a href="#">Tous les produits</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <p class="footer-col-title">Aide</p>
      <ul>
        <li><a href="#">Livraison & retours</a></li>
        <li><a href="#">FAQ</a></li>
        <li><a href="#">Conditions générales</a></li>
        <li><a href="#">Confidentialité</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <p class="footer-col-title">Contact</p>
      <ul class="footer-contact">
        <li><i class="fab fa-whatsapp"></i> WhatsApp</li>
        <li><i class="fab fa-instagram"></i> Instagram DM</li>
        <li><i class="fas fa-map-marker-alt"></i> Algérie</li>
      </ul>
    </div>

  </div>

  <div class="footer-bottom">
    <p>© <?= date('Y') ?> SheGlamour — Tous droits réservés</p>
    <div class="footer-badges">
      <span>Paiement à la livraison</span>
      <span>Livraison DZ</span>
    </div>
  </div>
</footer>

<style>
.luxury-footer {
  background: #440B19;
  color: #fff;
  font-family: 'DM Sans', sans-serif;
  padding: 48px 5% 0;
  border-top: 3px solid #6e1a2e;
}

.footer-top {
  display: grid;
  grid-template-columns: 1.4fr 1fr 1fr 1fr;
  gap: 40px;
  padding-bottom: 40px;
  border-bottom: 1px solid rgba(255,255,255,0.12);
}

.footer-logo {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-size: 22px;
  font-weight: 300;
  letter-spacing: 0.1em;
  color: #fff;
  margin: 0 0 10px;
}

.footer-brand p {
  font-size: 13px;
  color: rgba(255,255,255,0.5);
  line-height: 1.75;
  margin: 0 0 20px;
}

.footer-socials {
  display: flex;
  gap: 10px;
}

.footer-socials a {
  width: 34px; height: 34px;
  border: 1px solid rgba(255,255,255,0.2);
  color: rgba(255,255,255,0.7);
  font-size: 13px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  transition: all 0.2s;
}

.footer-socials a:hover {
  background: #fff;
  color: #440B19;
  border-color: #fff;
}

.footer-col-title {
  font-size: 9px;
  font-weight: 600;
  letter-spacing: 0.3em;
  text-transform: uppercase;
  color: rgba(255,255,255,0.4);
  margin: 0 0 16px;
}

.footer-col ul {
  list-style: none;
  padding: 0; margin: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.footer-col ul li a {
  font-size: 13px;
  color: rgba(255,255,255,0.65);
  text-decoration: none;
  transition: color 0.2s;
}

.footer-col ul li a:hover { color: #fff; }

.footer-contact {
  list-style: none;
  padding: 0; margin: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.footer-contact li {
  font-size: 13px;
  color: rgba(255,255,255,0.65);
  display: flex;
  align-items: center;
  gap: 8px;
}

.footer-contact li i {
  font-size: 14px;
  color: rgba(255,255,255,0.4);
  width: 16px;
}

.footer-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 0 24px;
}

.footer-bottom p {
  font-size: 12px;
  color: rgba(255,255,255,0.28);
  letter-spacing: 0.04em;
}

.footer-badges {
  display: flex;
  gap: 8px;
}

.footer-badges span {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: rgba(255,255,255,0.35);
  border: 1px solid rgba(255,255,255,0.15);
  padding: 3px 10px;
}

@media (max-width: 1024px) {
  .footer-top { grid-template-columns: 1fr 1fr; gap: 32px; }
  .footer-brand { grid-column: 1 / -1; }
}

@media (max-width: 600px) {
  .footer-top { grid-template-columns: 1fr; gap: 24px; text-align: center; }
  .footer-socials { justify-content: center; }
  .footer-contact li { justify-content: center; }
  .footer-bottom { flex-direction: column; gap: 12px; text-align: center; }
}
</style>