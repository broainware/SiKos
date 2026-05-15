<?php
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/middleware/auth.php';
$pageTitle = 'Lupa Kata Sandi'; $activePage = '';
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<div style="min-height:calc(100vh - 65px);display:flex;align-items:center;justify-content:center;padding:2rem;background:var(--bg)">
  <div style="background:var(--white);border-radius:var(--r2);box-shadow:var(--shadow2);width:100%;max-width:480px;padding:2.5rem 2rem">
    <h2 style="text-align:center;font-size:1.4rem;margin-bottom:.4rem">LUPA KATA SANDI</h2>
    <p style="text-align:center;color:var(--text3);font-size:.875rem;margin-bottom:1.75rem;font-weight:600">Masukkan username untuk mereset password</p>
    <div id="lupAlert"></div>
    <div class="form-group">
      <div class="input-icon">
        <i class="fas fa-user i-left" style="color:var(--green)"></i>
        <input type="text" id="username" class="form-control" placeholder="Username">
      </div>
    </div>
    <button class="btn btn-primary btn-full btn-lg" id="lupBtn" onclick="doReset()">Reset Password</button>
    <div style="text-align:center;margin-top:1.25rem;font-size:.875rem;font-weight:600">
      <a href="<?= APP_URL ?>/pages/auth/login.php" style="color:var(--text)">Kembali ke Masuk</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
<script>
async function doReset() {
  const u = document.getElementById('username').value.trim();
  const al = document.getElementById('lupAlert');
  if (!u) { al.innerHTML='<div class="alert alert-error">Username wajib diisi.</div>'; return; }
  al.innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Fitur reset password akan dikirim ke email terdaftar. Hubungi admin jika butuh bantuan.</div>';
}
</script>
</body>
</html>
