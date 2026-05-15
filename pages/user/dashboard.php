<?php
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/middleware/auth.php';
requirePenyewa();
$pe = getPenyewa();
$pageTitle  = 'Dashboard Saya';
$activePage = 'beranda';
$initials   = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $pe['nama']), 0, 2))));

// Booking aktif
$stA = DB::prep("SELECT b.*, k.nomor_kamar, k.tipe, k.harga_per_bulan,
    pm.status_pembayaran, pm.catatan_admin, pm.alasan_penolakan, pm.id_pembayaran
    FROM booking b
    JOIN kamar k ON b.id_kamar = k.id_kamar
    LEFT JOIN pembayaran pm ON b.id_booking = pm.id_booking
    WHERE b.id_penyewa = ? AND b.status = 'Aktif'
    ORDER BY b.tanggal_mulai DESC LIMIT 1");
$stA->bind_param('i', $pe['id']); $stA->execute();
$aktif = $stA->get_result()->fetch_assoc();

// Pembayaran ditolak (perlu tindakan)
$stR = DB::prep("SELECT b.kode_booking, k.nomor_kamar, pm.catatan_admin, pm.id_pembayaran, b.id_booking
    FROM booking b
    JOIN kamar k ON b.id_kamar = k.id_kamar
    JOIN pembayaran pm ON b.id_booking = pm.id_booking
    WHERE b.id_penyewa = ? AND pm.status_pembayaran = 'Ditolak'
    ORDER BY b.tanggal_pemesanan DESC LIMIT 5");
$stR->bind_param('i', $pe['id']); $stR->execute();
$ditolak = $stR->get_result()->fetch_all(MYSQLI_ASSOC);

// Sisa hari
$sisaHari = 0;
if ($aktif) {
    $selDt    = new DateTime($aktif['tanggal_selesai']);
    $now      = new DateTime();
    $diff     = $now->diff($selDt);
    $sisaHari = $diff->invert ? 0 : $diff->days;
}

// Kamar tersedia
$kamarAvail = DB::q("SELECT k.id_kamar, k.nomor_kamar, k.tipe, k.harga_per_bulan, k.foto,
    GROUP_CONCAT(f.nama_fasilitas ORDER BY f.nama_fasilitas SEPARATOR '||') fas
    FROM kamar k
    LEFT JOIN kamar_fasilitas kf ON k.id_kamar = kf.id_kamar
    LEFT JOIN fasilitas f ON kf.id_fasilitas = f.id_fasilitas
    WHERE k.status_ketersediaan = 'Tersedia'
    GROUP BY k.id_kamar ORDER BY k.harga_per_bulan LIMIT 8");
$totalAvail = DB::q("SELECT COUNT(*) c FROM kamar WHERE status_ketersediaan='Tersedia'")->fetch_assoc()['c'];
?>
<?php include __DIR__ . '/../../pages/partials/header.php'; ?>

<div class="app-layout">
  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-profile">
      <div class="sidebar-avatar"><?= $initials ?></div>
      <div>
        <div class="sidebar-name"><?= htmlspecialchars($pe['nama']) ?></div>
        <div class="sidebar-role">Penyewa</div>
      </div>
    </div>
    <a href="<?= APP_URL ?>/pages/user/dashboard.php" class="sidebar-link active"><i class="fas fa-home"></i> Dashboard</a>
    <a href="<?= APP_URL ?>/pages/user/booking.php"   class="sidebar-link"><i class="fas fa-clipboard-list"></i> Reservasi Kamar</a>
    <a href="<?= APP_URL ?>/pages/user/verifikasi.php" class="sidebar-link"><i class="fas fa-credit-card"></i> Verifikasi Transaksi</a>
    <a href="<?= APP_URL ?>/pages/user/kalender.php"  class="sidebar-link"><i class="fas fa-calendar-alt"></i> Kalender Ketersediaan</a>
    <a href="<?= APP_URL ?>/pages/review.php" class="sidebar-link"><i class="fas fa-star"></i> Review</a>
    <div class="sidebar-sep"></div>
    <a href="javascript:void(0)" class="sidebar-link" onclick="showProfil()"><i class="fas fa-user"></i> Profil</a>
    <a href="javascript:void(0)" class="sidebar-link" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Keluar</a>
  </aside>

  <main class="main-content">

    <!-- ===== NOTIFIKASI DITOLAK ===== -->
    <?php if (!empty($ditolak)): ?>
    <div style="background:#fee2e2;border:1.5px solid #fca5a5;border-radius:var(--r2);padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:flex-start;gap:.85rem">
      <i class="fas fa-exclamation-triangle" style="color:#c0392b;font-size:1.2rem;margin-top:.1rem;flex-shrink:0"></i>
      <div>
        <div style="font-weight:700;color:#991b1b;margin-bottom:.3rem">⚠️ Pembayaran Anda Ditolak!</div>
        <?php foreach ($ditolak as $d): ?>
        <div style="font-size:.875rem;color:#7f1d1d;margin-bottom:.3rem">
          Booking <strong><?= htmlspecialchars($d['kode_booking']) ?></strong> — Kamar <?= htmlspecialchars($d['nomor_kamar']) ?>
          <?php if ($d['catatan_admin']): ?>
          <br><span style="color:#991b1b">Alasan: <?= htmlspecialchars($d['catatan_admin']) ?></span>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:.5rem">
          <a href="<?= APP_URL ?>/pages/user/verifikasi.php" class="btn btn-danger btn-sm">
            <i class="fas fa-redo"></i> Upload Ulang Bukti
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- GREETING -->
    <h1 style="font-size:1.7rem;font-weight:800;margin-bottom:.2rem">
      Selamat Datang di SILIWANGI KOS 
    </h1>
    <p style="color:var(--text3);font-size:.875rem;margin-bottom:1.5rem">
      Ringkasan informasi pemesanan anda.
    </p>

    <!-- STATUS CARDS -->
    <?php if ($aktif): ?>
    <div class="stats-row" style="margin-bottom:1.75rem">
      <!-- Kamar Aktif -->
      <div class="stat-card" style="cursor:pointer" onclick="viewKamar(<?= $aktif['id_kamar'] ?>)">
        <div class="stat-icon si-green"><i class="fas fa-door-open"></i></div>
        <div>
          <div class="stat-val"><?= htmlspecialchars($aktif['nomor_kamar']) ?></div>
          <div class="stat-lbl">Kamar Aktif</div>
          <div style="font-size:.73rem;color:var(--text3)">•Aktif sejak <?= date('M Y', strtotime($aktif['tanggal_mulai'])) ?></div>
        </div>
      </div>
      <!-- Sisa Hari -->
      <div class="stat-card">
        <div class="stat-icon si-blue"><i class="fas fa-clock"></i></div>
        <div>
          <div class="stat-val"><?= $sisaHari ?> Hari</div>
          <div class="stat-lbl">Sisa Masa Sewa</div>
          <div style="font-size:.73rem;color:var(--text3)">Perpanjangan sebelum <?= date('d M', strtotime($aktif['tanggal_selesai'])) ?></div>
        </div>
      </div>
      <!-- Status Bayar -->
      <div class="stat-card">
        <div class="stat-icon <?= $aktif['status_pembayaran']==='Disetujui'?'si-green':($aktif['status_pembayaran']==='Ditolak'?'si-red':'si-orange') ?>">
          <i class="fas fa-<?= $aktif['status_pembayaran']==='Disetujui'?'check-circle':($aktif['status_pembayaran']==='Ditolak'?'times-circle':'clock') ?>"></i>
        </div>
        <div>
          <div class="stat-val" style="font-size:1.1rem"><?= $aktif['status_pembayaran']==='Disetujui'?'Lunas':($aktif['status_pembayaran']??'Menunggu') ?></div>
          <div class="stat-lbl">Status Pembayaran</div>
          <div style="font-size:.73rem;color:var(--text3)"><?= date('M Y', strtotime($aktif['tanggal_mulai'])) ?> terbayar</div>
        </div>
      </div>
    </div>
    <?php else: ?>
    <!-- Belum punya booking -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.75rem">
      <div class="stat-card"><div class="stat-icon si-green"><i class="fas fa-users"></i></div><div><div class="stat-val">100+</div><div class="stat-lbl">Penghuni</div><div style="font-size:.73rem;color:var(--text3)">Bergabung bersama di SIKOS</div></div></div>
      <div class="stat-card"><div class="stat-icon si-blue"><i class="fas fa-star"></i></div><div><div class="stat-val">4.8/5</div><div class="stat-lbl">Rating</div><div style="font-size:.73rem;color:var(--text3)">Dari ulasan penghuni kos</div></div></div>
      <div class="stat-card"><div class="stat-icon si-orange"><i class="fas fa-check"></i></div><div><div class="stat-val">Lengkap</div><div class="stat-lbl">Fasilitas</div><div style="font-size:.73rem;color:var(--text3)">Pesan sebelum kehabisan!</div></div></div>
    </div>
    <?php endif; ?>

    <!-- ACTION BUTTONS -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.75rem">
      <div>
        <h2 style="font-size:1.2rem;font-weight:700">Kamar Tersedia</h2>
        <p style="color:var(--text3);font-size:.875rem"><?= $totalAvail ?> Kamar siap untuk dipesan</p>
      </div>
      <div style="display:flex;gap:.75rem;flex-wrap:wrap">
        <a href="<?= APP_URL ?>/pages/user/booking.php" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Pesan Kamar
        </a>
        <a href="<?= APP_URL ?>/pages/user/kalender.php" class="btn btn-outline btn-sm">
          <i class="fas fa-calendar"></i> Lihat Kalender
        </a>
        <?php if ($aktif): ?>
        <a href="<?= APP_URL ?>/pages/user/verifikasi.php" class="btn btn-outline btn-sm">
          <i class="fas fa-credit-card"></i> Lihat Validasi Reservasi
        </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- KAMAR CARDS -->
    <div style="display:flex;gap:1.25rem;overflow-x:auto;padding-bottom:.5rem;scrollbar-width:thin">
      <?php while ($k = $kamarAvail->fetch_assoc()):
        $imgUrl = $k['foto'] ? APP_URL . '/public/uploads/kamar/' . $k['foto'] : '';
      ?>
      <div onclick="viewKamar(<?= $k['id_kamar'] ?>)"
           style="min-width:240px;max-width:260px;flex-shrink:0;background:var(--white);border-radius:var(--r2);overflow:hidden;box-shadow:var(--shadow);cursor:pointer;transition:transform .2s,box-shadow .2s"
           onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='var(--shadow2)'"
           onmouseout="this.style.transform='';this.style.boxShadow='var(--shadow)'">
        <div style="height:170px;overflow:hidden;background:var(--green-lt);display:flex;align-items:center;justify-content:center">
          <?php if ($imgUrl): ?>
          <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Kamar <?= htmlspecialchars($k['nomor_kamar']) ?>" style="width:100%;height:100%;object-fit:cover">
          <?php else: ?>
          <i class="fas fa-door-open" style="font-size:3rem;color:var(--green)"></i>
          <?php endif; ?>
        </div>
        <div style="padding:1rem">
          <div style="font-weight:700;font-size:.95rem">Kamar <?= htmlspecialchars($k['nomor_kamar']) ?></div>
          <div style="color:var(--text3);font-size:.85rem;margin-top:.15rem">Rp. <?= number_format($k['harga_per_bulan'],0,',','.') ?> / bulan</div>
        </div>
      </div>
      <?php endwhile; ?>
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
        <div>
          <div style="font-weight:700"><?= htmlspecialchars($pe['nama']) ?></div>
          <div style="font-size:.8rem;color:var(--text3);margin-bottom:.25rem">Penghuni</div>
          <span class="badge b-green">User</span>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.1rem">
        <div><div style="font-weight:600;font-size:.875rem;margin-bottom:.15rem">Username</div><div style="color:var(--text3);font-size:.875rem"><?= htmlspecialchars($pe['username']) ?></div></div>
        <div><div style="font-weight:600;font-size:.875rem;margin-bottom:.15rem">Email</div><div style="color:var(--text3);font-size:.875rem"><?= htmlspecialchars($pe['email'] ?: '-') ?></div></div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-outline btn-sm" onclick="S.closeModal('profilModal')">Tutup</button>
    </div>
  </div>
</div>

<!-- KAMAR DETAIL MODAL -->
<div class="modal-bg" id="kamarModal">
  <div class="modal" style="max-width:540px">
    <div class="modal-head"><span class="modal-title" id="kmTitle"><i class="fas fa-door-open"></i> Detail Kamar</span><button class="modal-close" onclick="S.closeModal('kamarModal')"><i class="fas fa-times"></i></button></div>
    <div class="modal-body" id="kmBody"></div>
    <div class="modal-foot" id="kmFoot"></div>
  </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';

function showProfil() { S.openModal('profilModal'); }

function doLogout() { confirmLogout(); }

async function viewKamar(id) {
  S.openModal('kamarModal');
  document.getElementById('kmBody').innerHTML = '<div style="text-align:center;padding:3rem"><span class="spinner"></span></div>';
  const r = await S.req('get_kamar_detail','GET',{id});
  if (r.status !== 'success') {
    document.getElementById('kmBody').innerHTML = '<p class="alert alert-error">Gagal memuat data.</p>'; return;
  }
  const k = r.data;
  document.getElementById('kmTitle').innerHTML = `<i class="fas fa-door-open"></i> Kamar ${k.nomor_kamar}`;
  const img = k.foto
    ? `<img src="${APP_URL}/public/uploads/kamar/${k.foto}" style="width:100%;height:200px;object-fit:cover;border-radius:var(--r);margin-bottom:1rem">`
    : '';
  const fas = (k.fasilitas||[]).map(f=>`<span class="fas-tag">${f}</span>`).join('');

  document.getElementById('kmBody').innerHTML = `
    ${img}
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.5rem;margin-bottom:.75rem">
      <div>
        <h3 style="font-size:1.05rem">Kamar ${k.nomor_kamar} — ${k.tipe}</h3>
        <div style="font-size:.8rem;color:var(--text3)">Lantai ${k.lantai}</div>
      </div>
      <div style="text-align:right">
        <div style="font-size:1.1rem;font-weight:800;color:var(--green3)">Rp ${Number(k.harga_per_bulan).toLocaleString('id-ID')}<span style="font-size:.75rem;font-weight:400">/bln</span></div>
        ${k.status_ketersediaan==='Tersedia'?'<span class="badge b-green">Tersedia</span>':'<span class="badge b-red">Terisi</span>'}
      </div>
    </div>
    <p style="font-size:.875rem;color:var(--text3);margin-bottom:.85rem;line-height:1.7">${k.deskripsi||''}</p>
    <div class="kamar-fas">${fas||'<span style="color:var(--text3);font-size:.85rem">-</span>'}</div>
    ${k.reviews?.length ? `<div style="margin-top:.85rem;max-height:160px;overflow-y:auto">
      ${k.reviews.map(rv=>`<div style="padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.85rem">
        <span style="font-weight:600">${rv.nama_lengkap}</span>
        <span style="color:#f59e0b;margin:0 .35rem">${'★'.repeat(rv.rating)}</span>
        <span style="color:var(--text3)">${rv.komentar}</span>
      </div>`).join('')}
    </div>` : ''}`;

  document.getElementById('kmFoot').innerHTML = `
    <button class="btn btn-outline btn-sm" onclick="S.closeModal('kamarModal')">Tutup</button>
    ${k.status_ketersediaan==='Tersedia'
      ? `<a href="${APP_URL}/pages/user/booking.php?kamar_id=${k.id_kamar}" class="btn btn-primary btn-sm"><i class="fas fa-calendar-plus"></i> Pesan Kamar Ini</a>`
      : ''}`;
}
</script>
</body>
</html>
