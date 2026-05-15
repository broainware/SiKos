<?php
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/middleware/auth.php';
requirePenyewa();
$pe = getPenyewa();
$pageTitle = 'Kalender Ketersediaan'; $activePage = 'beranda';
$initials = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ', $pe['nama']), 0, 2))));
$kamarList = DB::q("SELECT id_kamar,nomor_kamar,tipe,status_ketersediaan FROM kamar ORDER BY lantai,nomor_kamar")->fetch_all(MYSQLI_ASSOC);
?>
<?php include __DIR__ . '/../../pages/partials/header.php'; ?>

<div class="app-layout">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-profile">
      <div class="sidebar-avatar"><?= $initials ?></div>
      <div><div class="sidebar-name"><?= htmlspecialchars($pe['nama']) ?></div><div class="sidebar-role">Penyewa</div></div>
    </div>
    <a href="<?= APP_URL ?>/pages/user/dashboard.php" class="sidebar-link"><i class="fas fa-home"></i> Dashboard</a>
    <a href="<?= APP_URL ?>/pages/user/booking.php" class="sidebar-link"><i class="fas fa-clipboard-list"></i> Reservasi Kamar</a>
    <a href="<?= APP_URL ?>/pages/user/verifikasi.php" class="sidebar-link"><i class="fas fa-credit-card"></i> Verifikasi Transaksi</a>
    <a href="<?= APP_URL ?>/pages/user/kalender.php" class="sidebar-link active"><i class="fas fa-calendar-alt"></i> Kalender Ketersediaan</a>
    <a href="<?= APP_URL ?>/pages/review.php" class="sidebar-link"><i class="fas fa-star"></i> Review</a>
    <div class="sidebar-sep"></div>
    <a href="javascript:void(0)" class="sidebar-link" onclick="showProfil()"><i class="fas fa-user"></i> Profil</a>
    <a href="javascript:void(0)" class="sidebar-link" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Keluar</a>
  </aside>

  <main class="main-content">
    <h1 style="font-size:1.7rem;font-weight:800;margin-bottom:1.5rem">Kalender Ketersediaan</h1>

    <!-- FILTER KAMAR -->
    <div style="margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap">
      <label class="form-label" style="margin:0;white-space:nowrap">Filter Kamar:</label>
      <select id="filterKamar" class="form-control" style="width:auto;min-width:180px;border-radius:var(--r3)" onchange="initCal()">
        <option value="">Semua Kamar</option>
        <?php foreach ($kamarList as $k): ?>
        <option value="<?= $k['id_kamar'] ?>">Kamar <?= htmlspecialchars($k['nomor_kamar']) ?> (<?= htmlspecialchars($k['tipe']) ?>)</option>
        <?php endforeach; ?>
      </select>
      <a href="<?= APP_URL ?>/pages/user/booking.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Pesan Kamar</a>
    </div>

    <!-- KAMAR STATUS GRID -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:.75rem;margin-bottom:1.5rem">
      <?php foreach ($kamarList as $k):
        $statusColor = $k['status_ketersediaan'] === 'Tersedia' ? '#d1fae5' : ($k['status_ketersediaan'] === 'Terisi' ? '#fee2e2' : '#fef3c7');
        $textColor   = $k['status_ketersediaan'] === 'Tersedia' ? '#065f46' : ($k['status_ketersediaan'] === 'Terisi' ? '#991b1b' : '#92400e');
      ?>
      <div style="background:<?= $statusColor ?>;border-radius:var(--r);padding:.75rem;text-align:center;cursor:pointer;transition:transform .15s" onclick="filterToKamar(<?= $k['id_kamar'] ?>)" title="Kamar <?= htmlspecialchars($k['nomor_kamar']) ?>">
        <div style="font-weight:700;font-size:.9rem;color:<?= $textColor ?>">Kamar <?= htmlspecialchars($k['nomor_kamar']) ?></div>
        <div style="font-size:.72rem;color:<?= $textColor ?>;margin-top:.15rem"><?= htmlspecialchars($k['status_ketersediaan']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- CALENDAR -->
    <div class="card" style="padding:1.5rem">
      <div id="calContainer"></div>
    </div>

    <!-- CEK STATUS BOOKING -->
    <div class="card" style="padding:1.5rem;margin-top:1.25rem">
      <h3 style="font-size:1rem;font-weight:700;margin-bottom:1rem"><i class="fas fa-search" style="color:var(--green)"></i> Cek Status Booking</h3>
      <div style="display:flex;gap:.75rem;flex-wrap:wrap">
        <input type="text" id="cekKode" class="form-control" style="flex:1;min-width:200px;border-radius:var(--r3)" placeholder="Masukkan ID Booking (SKS-...)">
        <button class="btn btn-primary" onclick="cekBooking()"><i class="fas fa-search"></i> Cek</button>
      </div>
      <div id="cekResult" style="margin-top:1rem"></div>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../pages/partials/footer.php'; ?>

<!-- PROFIL MODAL -->
<div class="modal-bg" id="profilModal">
  <div class="modal" style="max-width:500px">
    <div class="modal-head"><span class="modal-title"><i class="fas fa-user"></i> Profil User</span><button class="modal-close" onclick="S.closeModal('profilModal')"><i class="fas fa-times"></i></button></div>
    <div class="modal-body">
      <div style="background:var(--green-xl);border-radius:var(--r);padding:1rem;display:flex;align-items:center;gap:.85rem;margin-bottom:1.25rem">
        <div style="width:52px;height:52px;border-radius:50%;background:var(--green2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.3rem"><i class="fas fa-user"></i></div>
        <div><div style="font-weight:700"><?= htmlspecialchars($pe['nama']) ?></div><div style="font-size:.8rem;color:var(--text3)">Penghuni</div><span class="badge b-green">User</span></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div><div style="font-weight:600;font-size:.875rem">Username</div><div style="font-size:.875rem;color:var(--text3)"><?= htmlspecialchars($pe['username']) ?></div></div>
        <div><div style="font-weight:600;font-size:.875rem">Email</div><div style="font-size:.875rem;color:var(--text3)"><?= htmlspecialchars($pe['email'] ?: '-') ?></div></div>
        <div style="grid-column:1/-1"><div style="font-weight:600;font-size:.875rem">No. Hp</div><div style="font-size:.875rem;color:var(--text3)"><?= htmlspecialchars($pe['no_hp'] ?: '-') ?></div></div>
      </div>
    </div>
    <div class="modal-foot"><button class="btn btn-outline btn-sm" onclick="S.closeModal('profilModal')">Tutup</button></div>
  </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
function showProfil() { S.openModal('profilModal'); }
function doLogout() { confirmLogout(); }

let calInstance = null;
function initCal() {
  const idKamar = document.getElementById('filterKamar').value || null;
  calInstance = new SmartCal('calContainer', { idKamar: idKamar ? parseInt(idKamar) : null, selectable: false });
}
function filterToKamar(id) {
  document.getElementById('filterKamar').value = id;
  initCal();
}

async function cekBooking() {
  const kode = document.getElementById('cekKode').value.trim();
  if (!kode) { S.toast('Masukkan ID Booking terlebih dahulu.','w'); return; }
  const r = await S.req('cek_booking','GET',{kode});
  const div = document.getElementById('cekResult');
  if (r.status !== 'success') {
    div.innerHTML = '<div class="alert alert-error"><i class="fas fa-times-circle"></i> ' + r.message + '</div>';
    return;
  }
  div.innerHTML = r.data.map(b => `
    <div style="background:var(--green-xl);border-radius:var(--r);padding:1rem;margin-bottom:.75rem">
      <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem">
        <div>
          <div style="font-weight:700">${b.kode_booking}</div>
          <div style="font-size:.85rem;color:var(--text3)">Kamar ${b.nomor_kamar} ${b.tipe}</div>
        </div>
        <div style="text-align:right">
          ${S.badge(b.status)}
          <div style="font-size:.8rem;color:var(--text3);margin-top:.2rem">${S.fmtDate(b.tanggal_mulai)} — ${S.fmtDate(b.tanggal_selesai)}</div>
        </div>
      </div>
    </div>`).join('');
}

initCal();
</script>
</body>
</html>
