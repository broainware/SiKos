<?php // partials/footer.php ?>
<footer>
  <div class="footer-grid">
    <div>
      <div class="footer-brand">
        <svg width="28" height="28" viewBox="0 0 32 32" fill="none">
          <path d="M16 3L3 14h4v15h8v-8h2v8h8V14h4L16 3z" fill="#3d6b4a"/>
        </svg>
        SIKOS
      </div>
      <p style="font-size:.78rem;color:var(--text3);font-weight:600;margin-bottom:.4rem">SIKOS</p>
      <p class="footer-desc">Sistem Informasi Pemesanan<br>Kamar Kos Berbasis Web</p>
    </div>
    <div>
      <div class="footer-head">Tautan Cepat</div>
      <a href="<?= APP_URL ?>/pages/tentang.php" class="footer-link">Tentang Kami</a>
      <a href="<?= APP_URL ?>/pages/tentang.php#metodologi" class="footer-link">Metodologi</a>
      <a href="<?= APP_URL ?>/pages/tentang.php#tim" class="footer-link">Tim Kami</a>
      <a href="<?= APP_URL ?>/pages/kontak.php" class="footer-link">Kontak</a>
    </div>
    <div>
      <div class="footer-head">Kontak Kami</div>
      <div class="footer-contact"><i class="fas fa-map-marker-alt"></i><span>Mugarsari Tamansari, Tasikmalaya</span></div>
      <div class="footer-contact"><i class="fas fa-phone"></i><span>+62 123 4567 890</span></div>
      <div class="footer-contact"><i class="fas fa-envelope"></i><span>sikos@gmail.com</span></div>
    </div>
    <div>
      <div class="footer-head">Media Sosial</div>
      <div class="footer-social">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-youtube"></i></a>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <span>2026 SIKOS. Hak Cipta Dilindungi</span>
    <div style="display:flex;gap:1.5rem">
      <a href="#" class="footer-link">Kebijakan Privasi</a>
      <a href="#" class="footer-link">Syarat &amp; Ketentuan</a>
    </div>
  </div>
</footer>
<div id="toasts"></div>
<script src="<?= APP_URL ?>/public/js/main.js"></script>
