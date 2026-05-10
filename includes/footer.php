<footer class="luxury-footer">
  <div class="footer-container">

    <!-- COLONNE 1 -->
    <div class="footer-col footer-brand">
      <h3 class="footer-logo">SheGlamour</h3>
      <p>L'élégance du maquillage.<br>Le luxe au quotidien.</p>
      <div class="footer-socials">
        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
      </div>
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
        <li><a href="#">Confidentialité</a></li>
        <li><a href="#">FAQ</a></li>
      </ul>
    </div>

    <!-- COLONNE 4 -->
    <div class="footer-col">
      <h4>Contact</h4>
      <ul class="footer-contact">
        <li><i class="fab fa-whatsapp"></i> WhatsApp</li>
        <li><i class="fab fa-instagram"></i> Instagram DM</li>
        <li><i class="fa-solid fa-location-dot"></i> Algérie</li>
      </ul>
    </div>

  </div>

  <!-- Séparateur décoratif -->
  <div class="footer-divider">
    <span></span><span class="footer-diamond">◆</span><span></span>
  </div>

  <div class="footer-bottom">
    <p>© <?= date('Y') ?> SheGlamour — Tous droits réservés</p>
  </div>
</footer>

<style>
/* ══ FOOTER — Blanc & Bordeaux ════════════════════════════ */
.luxury-footer {
  background: #440B19;
  color: #ffffff;
  padding: 72px 0 0;
  font-family: 'DM Sans', Georgia, sans-serif;
  position: relative;
}

.luxury-footer::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: #6e1a2e;
}

/* ── Grille ─────────────────────────────────────────────── */
.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1.6fr 1fr 1fr 1fr;
  gap: 48px;
  padding: 0 5%;
}

/* ── Colonne brand ──────────────────────────────────────── */
.footer-logo {
  font-family: 'Cormorant Garamond', Georgia, serif;
  font-size: 26px;
  font-weight: 300;
  letter-spacing: .08em;
  color: #ffffff;
  margin: 0 0 14px;
}

.footer-brand p {
  font-size: 13.5px;
  color: rgba(255, 255, 255, .55);
  line-height: 1.8;
  margin: 0 0 24px;
}

/* ── Réseaux sociaux ────────────────────────────────────── */
.footer-socials {
  display: flex;
  gap: 12px;
}

.footer-socials a {
  width: 36px; height: 36px;
  border-radius: 50%;
  border: 1.5px solid rgba(255, 255, 255, .25);
  color: #ffffff;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  transition: background .25s, border-color .25s, color .25s, transform .25s;
}

.footer-socials a:hover {
  background: #ffffff;
  border-color: #ffffff;
  color: #440B19;
  transform: translateY(-3px);
}

/* ── Colonnes liens ─────────────────────────────────────── */
.footer-col h4 {
  font-family: 'DM Sans', sans-serif;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: .3em;
  text-transform: uppercase;
  color: #ffffff;
  margin: 0 0 20px;
}

.footer-col ul {
  list-style: none;
  padding: 0; margin: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.footer-col ul li a {
  color: rgba(255, 255, 255, .55);
  font-size: 13.5px;
  text-decoration: none;
  transition: color .2s, padding-left .2s;
  display: inline-block;
}

.footer-col ul li a:hover {
  color: #ffffff;
  padding-left: 4px;
}

/* ── Contact ────────────────────────────────────────────── */
.footer-contact {
  list-style: none;
  padding: 0; margin: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.footer-contact li {
  color: rgba(255, 255, 255, .55);
  font-size: 13.5px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.footer-contact li i {
  color: rgba(255, 255, 255, .75);
  font-size: 15px;
  width: 16px;
}

/* ── Séparateur décoratif ───────────────────────────────── */
.footer-divider {
  display: flex;
  align-items: center;
  gap: 16px;
  margin: 52px 5% 0;
  opacity: .2;
}

.footer-divider span:not(.footer-diamond) {
  flex: 1; height: 1px;
  background: #ffffff;
}

.footer-diamond {
  font-size: 8px;
  color: #ffffff;
}

/* ── Copyright ──────────────────────────────────────────── */
.footer-bottom {
  text-align: center;
  font-size: 12px;
  color: rgba(255, 255, 255, .30);
  letter-spacing: .06em;
  padding: 20px 5% 28px;
}

/* ══ RESPONSIVE ════════════════════════════════════════════ */
@media (max-width: 1024px) {
  .footer-container {
    grid-template-columns: 1fr 1fr;
    gap: 36px;
  }
  .footer-brand { grid-column: 1 / -1; }
  .footer-brand p { max-width: 360px; }
}

@media (max-width: 600px) {
  .luxury-footer { padding-top: 52px; }
  .footer-container {
    grid-template-columns: 1fr;
    gap: 28px;
    text-align: center;
  }
  .footer-brand p { max-width: 100%; }
  .footer-socials  { justify-content: center; }
  .footer-contact li { justify-content: center; }
  .footer-col ul li a:hover { padding-left: 0; }
}
</style>