<?php
require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/middleware/auth.php';
$pageTitle = 'Kontak'; $activePage = 'kontak';
?>
<?php include __DIR__ . '/partials/header.php'; ?>

<div style="max-width:900px;margin:0 auto;padding:2.5rem 1.5rem">
  <!-- BREADCRUMB -->
  <div style="font-size:.85rem;color:var(--text3);margin-bottom:1.5rem">
    <a href="<?= APP_URL ?>/index.php" style="color:var(--text3)">Beranda</a>
    <span style="margin:0 .4rem">/</span>
    <span>Kontak</span>
  </div>

  <h1 style="font-size:2rem;font-weight:800;text-align:center;margin-bottom:.5rem">Hubungi Pemilik Kos</h1>
  <p style="text-align:center;color:var(--text3);margin-bottom:3rem">Informasi kosan lebih lanjut bisa hubungi pemilik kosan</p>

  <!-- CONTACT CARDS -->
  <div style="margin-bottom:2rem">
    <h2 style="font-size:1.1rem;font-weight:700;color:var(--green3);margin-bottom:1.25rem">Informasi Kontak</h2>

    <div style="border:1.5px solid var(--border);border-radius:var(--r2);padding:1.25rem;margin-bottom:1rem;background:var(--white)">
      <div style="width:36px;height:36px;border-radius:50%;background:var(--green-xl);display:flex;align-items:center;justify-content:center;margin-bottom:.75rem">
        <i class="fas fa-map-marker-alt" style="color:var(--green)"></i>
      </div>
      <div style="font-weight:700;color:var(--green3);margin-bottom:.35rem">Alamat</div>
      <p style="color:var(--text3);font-size:.9rem">Mugarsari Tamansari, Tasikmalaya,<br>Jawa Barat</p>
    </div>

    <div style="border:1.5px solid var(--border);border-radius:var(--r2);padding:1.25rem;margin-bottom:1rem;background:var(--white)">
      <div style="width:36px;height:36px;border-radius:50%;background:var(--green-xl);display:flex;align-items:center;justify-content:center;margin-bottom:.75rem">
        <i class="fas fa-phone" style="color:var(--green)"></i>
      </div>
      <div style="font-weight:700;color:var(--green3);margin-bottom:.35rem">Telepon</div>
      <p style="color:var(--text3);font-size:.9rem">+62 123 4567 890</p>
    </div>

    <div style="border:1.5px solid var(--border);border-radius:var(--r2);padding:1.25rem;margin-bottom:1.5rem;background:var(--white)">
      <div style="width:36px;height:36px;border-radius:50%;background:var(--green-xl);display:flex;align-items:center;justify-content:center;margin-bottom:.75rem">
        <i class="fas fa-envelope" style="color:var(--green)"></i>
      </div>
      <div style="font-weight:700;color:var(--green3);margin-bottom:.35rem">Email</div>
      <p style="color:var(--text3);font-size:.9rem">pemilikkos@gmail.com</p>
    </div>

    <!-- GOOGLE MAPS EMBED -->
    <div style="border-radius:var(--r2);overflow:hidden;box-shadow:var(--shadow)">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.3820856978455!2d108.2100842!3d-7.3606413!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6f57b0bd99fe49%3A0x8437f8d0a98e4e8e!2sTasikmalaya%2C%20West%20Java!5e0!3m2!1sen!2sid!4v1234567890"
        width="100%" height="300" style="border:0;display:block" allowfullscreen="" loading="lazy"
        referrerpolicy="no-referrer-when-downgrade" title="Lokasi SIKOS">
      </iframe>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
<?php if (isPenyewa() || isAdmin()): ?>
<script>
const APP_URL = '<?= APP_URL ?>';
async function doLogout() { const r = await S.req('logout','POST'); if(r.redirect) location.href=r.redirect; }
function showProfil() {}
</script>
<?php endif; ?>
</body>
</html>
