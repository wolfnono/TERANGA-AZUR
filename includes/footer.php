<?php // includes/footer.php ?>

<footer>
  <div class="footer-grid">
    <!-- Brand -->
    <div>
      <div class="footer-logo">
        <img src="images/Logo.png" alt="Teranga Azur">
        <div class="footer-brand-name">TERANGA AZUR</div>
        <div class="footer-tagline">À toi ton toit de rêve</div>
      </div>
      <p class="footer-desc">
        Teranga Azur vous propose des villas d'exception face à l'océan, alliant luxe, authenticité et hospitalité africaine pour des séjours inoubliables.
      </p>
      <div class="social-links" style="margin-top:22px;">
        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
        <a href="#" title="TripAdvisor"><i class="fab fa-tripadvisor"></i></a>
      </div>
    </div>

    <!-- Navigation -->
    <div>
      <div class="footer-title">Navigation</div>
      <ul class="footer-links">
        <li><a href="index.php"><i class="fas fa-chevron-right fa-xs"></i> Accueil</a></li>
        <li><a href="villas.php"><i class="fas fa-chevron-right fa-xs"></i> Nos Villas</a></li>
        <li><a href="activites.php"><i class="fas fa-chevron-right fa-xs"></i> Activités</a></li>
        <li><a href="mes-reservations.php"><i class="fas fa-chevron-right fa-xs"></i> Mes Réservations</a></li>
        <li><a href="inscription.php"><i class="fas fa-chevron-right fa-xs"></i> S'inscrire</a></li>
      </ul>
    </div>

    <!-- Services -->
    <div>
      <div class="footer-title">Services</div>
      <ul class="footer-links">
        <li><a href="villas.php?filtre=piscine"><i class="fas fa-swimming-pool fa-xs"></i> Villas avec piscine</a></li>
        <li><a href="activites.php?type=mer"><i class="fas fa-water fa-xs"></i> Activités nautiques</a></li>
        <li><a href="activites.php?type=culture"><i class="fas fa-compass fa-xs"></i> Excursions culturelles</a></li>
        <li><a href="#"><i class="fas fa-concierge-bell fa-xs"></i> Services optionnels</a></li>
        <li><a href="#"><i class="fas fa-star fa-xs"></i> Offres spéciales</a></li>
      </ul>
    </div>

    <!-- Contact -->
    <div>
      <div class="footer-title">Contact</div>
      <div class="footer-contact-item">
        <i class="fas fa-map-marker-alt"></i>
        <span>Saly Portudal, Mbour<br>Sénégal</span>
      </div>
      <div class="footer-contact-item">
        <i class="fas fa-phone"></i>
        <span>+221 77 000 00 00</span>
      </div>
      <div class="footer-contact-item">
        <i class="fas fa-envelope"></i>
        <span>contact@teranga-azur.sn</span>
      </div>
      <div class="footer-contact-item">
        <i class="fas fa-clock"></i>
        <span>Lun–Sam : 9h – 19h</span>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <span>© <?= date('Y') ?> Teranga Azur. Tous droits réservés.</span>
    <div style="display:flex;gap:20px;">
      <a href="#" style="color:rgba(255,255,255,0.4);text-decoration:none;">Mentions légales</a>
      <a href="#" style="color:rgba(255,255,255,0.4);text-decoration:none;">Politique de confidentialité</a>
      <a href="#" style="color:rgba(255,255,255,0.4);text-decoration:none;">CGU</a>
    </div>
  </div>
</footer>

<!-- JS Global -->
<script src="js/main.js"></script>
<?php if (isset($extra_js)): ?>
<script src="js/<?= $extra_js ?>"></script>
<?php endif; ?>
</body>
</html>
