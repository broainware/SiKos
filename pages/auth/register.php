<?php
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/middleware/auth.php';
if (isAdmin() || isPenyewa()) { header('Location: ' . APP_URL . '/index.php'); exit; }
$pageTitle = 'Daftar'; $activePage = '';
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<div style="min-height:calc(100vh - 65px);display:flex;align-items:center;justify-content:center;padding:2rem;background:var(--bg)">
  <div style="background:var(--white);border-radius:var(--r2);box-shadow:var(--shadow2);width:100%;max-width:480px;padding:2.5rem 2rem">

    <h2 style="text-align:center;font-size:1.5rem;margin-bottom:.4rem">DAFTAR</h2>
    <p style="text-align:center;color:var(--text3);font-size:.875rem;margin-bottom:1.75rem;font-weight:600">Buat akun SIKOS kamu</p>

    <div id="regAlert"></div>

    <!-- EMAIL dulu sebelum username -->
    <div class="form-group">
      <div class="input-icon">
        <i class="fas fa-envelope i-left" style="color:var(--green)"></i>
        <input type="email" id="reg_email" class="form-control" placeholder="Email aktif" autocomplete="email">
      </div>
    </div>

    <div class="form-group">
      <div class="input-icon">
        <i class="fas fa-user i-left" style="color:var(--green)"></i>
        <input type="text" id="reg_username" class="form-control" placeholder="Username (min. 4 karakter)" autocomplete="username">
      </div>
    </div>

    <div class="form-group">
      <div class="input-icon">
        <i class="fas fa-lock i-left" style="color:var(--green)"></i>
        <input type="password" id="reg_password" class="form-control" placeholder="Password (min. 6 karakter)" autocomplete="new-password" oninput="checkStrength(this.value)">
        <i class="fas fa-eye i-right" id="togglePw1" onclick="togglePass('reg_password','togglePw1')"></i>
      </div>
    </div>

    <!-- Password Strength -->
    <div id="pwStrength" style="margin:-0.5rem 0 1rem;display:none">
      <div style="height:4px;background:var(--border);border-radius:var(--r3);overflow:hidden;margin-bottom:.25rem">
        <div id="pwBar" style="height:100%;width:0;border-radius:var(--r3);transition:width .3s,background .3s"></div>
      </div>
      <span id="pwLabel" style="font-size:.72rem;color:var(--text3)"></span>
    </div>

    <div class="form-group">
      <div class="input-icon">
        <i class="fas fa-lock i-left" style="color:var(--green)"></i>
        <input type="password" id="reg_confirm" class="form-control" placeholder="Konfirmasi password" autocomplete="new-password">
        <i class="fas fa-eye i-right" id="togglePw2" onclick="togglePass('reg_confirm','togglePw2')"></i>
      </div>
    </div>

    <button class="btn btn-primary btn-full btn-lg" id="regBtn" onclick="doRegister()">Daftar</button>

    <div style="text-align:center;margin-top:1.25rem;font-size:.875rem;font-weight:600">
      <a href="<?= APP_URL ?>/pages/auth/login.php" style="color:var(--text)">Sudah terdaftar? Klik di sini untuk masuk.</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
<script>
function togglePass(fieldId, iconId) {
  const pw = document.getElementById(fieldId);
  const ic = document.getElementById(iconId);
  pw.type = pw.type === 'password' ? 'text' : 'password';
  ic.className = pw.type === 'text' ? 'fas fa-eye-slash i-right' : 'fas fa-eye i-right';
}

function checkStrength(v) {
  const el = document.getElementById('pwStrength');
  const bar = document.getElementById('pwBar');
  const lbl = document.getElementById('pwLabel');
  if (!v) { el.style.display='none'; return; }
  el.style.display='block';
  let score = 0;
  if (v.length >= 6) score++;
  if (v.length >= 10) score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;
  const levels = [
    {w:'20%',c:'#ef4444',t:'Sangat Lemah'},
    {w:'40%',c:'#f97316',t:'Lemah'},
    {w:'60%',c:'#eab308',t:'Cukup'},
    {w:'80%',c:'#22c55e',t:'Kuat'},
    {w:'100%',c:'#15803d',t:'Sangat Kuat'},
  ];
  const l = levels[Math.min(score, 4)];
  bar.style.width = l.w; bar.style.background = l.c;
  lbl.textContent = l.t; lbl.style.color = l.c;
}

document.addEventListener('keydown', e => { if (e.key === 'Enter') doRegister(); });

async function doRegister() {
  const email   = document.getElementById('reg_email').value.trim();
  const u       = document.getElementById('reg_username').value.trim();
  const p       = document.getElementById('reg_password').value;
  const confirm = document.getElementById('reg_confirm').value;
  const al      = document.getElementById('regAlert');

  al.innerHTML = '';
  if (!email) { al.innerHTML='<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Email wajib diisi.</div>'; return; }
  if (!u)     { al.innerHTML='<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Username wajib diisi.</div>'; return; }
  if (!p)     { al.innerHTML='<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Password wajib diisi.</div>'; return; }
  if (confirm && p !== confirm) {
    al.innerHTML='<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Konfirmasi password tidak cocok.</div>'; return;
  }

  const btn = document.getElementById('regBtn');
  S.loading(btn, true);
  const r = await S.req('register', 'POST', {
    email, username: u, password: p, confirm_password: confirm
  });
  S.loading(btn, false);
  if (r.status === 'success') {
    S.toast(r.message, 's');
    setTimeout(() => location.href = r.redirect, 900);
  } else {
    al.innerHTML = `<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ${r.message}</div>`;
  }
}
</script>
</body>
</html>
