<?php
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/middleware/auth.php';
if (isAdmin())   { header('Location: ' . APP_URL . '/pages/admin/dashboard.php'); exit; }
if (isPenyewa()) { header('Location: ' . APP_URL . '/pages/user/dashboard.php'); exit; }
$pageTitle  = 'Masuk';
$activePage = '';
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<div style="min-height:calc(100vh - 65px);display:flex;align-items:center;justify-content:center;padding:2rem;background: linear-gradient(
     to bottom,
  #E8F4DC 0%,
  #e4f4d0 45%,
  #B6C99C 75%,
  #B6C99C 100%
  );
  background-attachment: fixed;
  color:var(--text);
  line-height:1.6;">
  <div style="background:var(--white);border-radius:var(--r2);box-shadow:var(--shadow2);width:100%;max-width:480px;padding:2.5rem 2rem">

    <h2 style="text-align:center;font-size:1.5rem;margin-bottom:.4rem">MASUK</h2>
    <p style="text-align:center;color:var(--text3);font-size:.875rem;margin-bottom:1.75rem;font-weight:600">Selamat Datang Kembali</p>

    <div id="loginAlert"></div>

    <div class="form-group">
      <div class="input-icon">
        <i class="fas fa-user i-left" style="color:var(--green)"></i>
        <input type="text" id="username" class="form-control" placeholder="Username atau Email" autocomplete="username">
      </div>
    </div>

    <div class="form-group">
      <div class="input-icon">
        <i class="fas fa-lock i-left" style="color:var(--green)"></i>
        <input type="password" id="password" class="form-control" placeholder="Password" autocomplete="current-password">
        <i class="fas fa-eye i-right" id="togglePw" onclick="togglePass()"></i>
      </div>
    </div>

    <button class="btn btn-primary btn-full btn-lg" id="loginBtn" onclick="doLogin()">Masuk</button>

    <div style="text-align:center;margin-top:1.25rem;font-size:.875rem;font-weight:600">
      <a href="<?= APP_URL ?>/pages/auth/lupa-sandi.php" style="color:var(--text);display:block;margin-bottom:.5rem">Lupa Kata Sandi?</a>
      <a href="<?= APP_URL ?>/pages/auth/register.php" style="color:var(--text)">Belum punya akun? Daftar sekarang, yuk!</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

<script>
function togglePass() {
  const pw = document.getElementById('password');
  const ic = document.getElementById('togglePw');
  const show = pw.type === 'password';
  pw.type = show ? 'text' : 'password';
  ic.className = show ? 'fas fa-eye-slash i-right' : 'fas fa-eye i-right';
}

document.addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });

async function doLogin() {
  const u = document.getElementById('username').value.trim();
  const p = document.getElementById('password').value;
  const alert = document.getElementById('loginAlert');

  if (!u || !p) {
    alert.innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Username atau email dan password wajib diisi.</div>';
    return;
  }
  alert.innerHTML = '';
  const btn = document.getElementById('loginBtn');
  S.loading(btn, true);

  const r = await S.req('login', 'POST', { username: u, password: p });
  S.loading(btn, false);

  if (r.status === 'success') {
    S.toast(r.message, 's');
    setTimeout(() => location.href = r.redirect, 600);
  } else {
    alert.innerHTML = `<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ${r.message}</div>`;
  }
}
</script>
</body>
</html>
