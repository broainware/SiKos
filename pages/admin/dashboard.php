<?php
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/middleware/auth.php';
requireAdmin();
$adm        = getAdmin();
$pageTitle  = 'Dashboard Admin';
$activePage = 'beranda';

// Fetch ringkasan data
$kamarRows = DB::q("SELECT k.nomor_kamar, k.tipe, k.harga_per_bulan,
    GROUP_CONCAT(f.nama_fasilitas ORDER BY f.nama_fasilitas SEPARATOR ', ') fas,
    k.status_ketersediaan
    FROM kamar k
    LEFT JOIN kamar_fasilitas kf ON k.id_kamar=kf.id_kamar
    LEFT JOIN fasilitas f ON kf.id_fasilitas=f.id_fasilitas
    GROUP BY k.id_kamar ORDER BY k.lantai, k.nomor_kamar");

$bookingRows = DB::q("SELECT b.kode_booking, p.nama_lengkap, k.nomor_kamar, k.tipe,
    DATE_FORMAT(b.tanggal_pemesanan,'%d-%m-%Y') tgl, b.status
    FROM booking b
    JOIN penyewa p ON b.id_penyewa=p.id_penyewa
    JOIN kamar k ON b.id_kamar=k.id_kamar
    ORDER BY b.tanggal_pemesanan DESC LIMIT 10");

// Stats numbers
$statsK = DB::q("SELECT COUNT(*) t, SUM(IF(status_ketersediaan='Tersedia',1,0)) tersedia, SUM(IF(status_ketersediaan='Terisi',1,0)) terisi FROM kamar")->fetch_assoc();
$statsB = DB::q("SELECT COUNT(*) t, SUM(IF(status='Pending',1,0)) pending, SUM(IF(status='Aktif',1,0)) aktif FROM booking")->fetch_assoc();
$pendingVerif = DB::q("SELECT COUNT(*) c FROM pembayaran WHERE status_pembayaran='Proses Validasi'")->fetch_assoc()['c'];
?>
<?php include __DIR__ . '/../../pages/partials/header.php'; ?>

<div class="app-layout">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <main class="main-content">

    <!-- PAGE HEADER -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.75rem">
      <div>
        <h1 style="font-size:1.8rem;font-weight:800">Dashboard Admin</h1>
        <p style="color:var(--text3);font-size:.875rem;margin-top:.1rem"><?= date('l, d F Y') ?></p>
        <p style="color:var(--text3);font-size:.875rem">Ringkasan Operasional</p>
      </div>
      <div style="display:flex;gap:.75rem;flex-wrap:wrap">
        <a href="<?= APP_URL ?>/pages/admin/data-kamar.php" class="btn btn-outline">
          <i class="fas fa-door-open"></i> Lihat Data Kamar
        </a>
        <a href="<?= APP_URL ?>/pages/admin/data-reservasi.php" class="btn btn-primary">
          <i class="fas fa-clipboard-list"></i> Lihat Data Reservasi
        </a>
      </div>
    </div>

    <!-- NOTIFIKASI VERIFIKASI PENDING -->
    <?php if ($pendingVerif > 0): ?>
    <div style="background:#fef3c7;border:1.5px solid #f59e0b;border-radius:var(--r2);padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem">
      <div style="display:flex;align-items:center;gap:.75rem">
        <i class="fas fa-exclamation-triangle" style="color:#d97706;font-size:1.1rem"></i>
        <span style="font-weight:600;color:#92400e"><?= $pendingVerif ?> pembayaran menunggu verifikasi Anda</span>
      </div>
      <a href="<?= APP_URL ?>/pages/admin/verifikasi.php" class="btn btn-warning btn-sm">
        <i class="fas fa-shield-alt"></i> Verifikasi Sekarang
      </a>
    </div>
    <?php endif; ?>

    <!-- STATS CARDS -->
    <div class="stats-row" style="margin-bottom:2rem">
      <div class="stat-card">
        <div class="stat-icon si-green"><i class="fas fa-door-open"></i></div>
        <div><div class="stat-val"><?= $statsK['t'] ?></div><div class="stat-lbl">Total Kamar</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon si-blue"><i class="fas fa-check"></i></div>
        <div><div class="stat-val"><?= $statsK['tersedia'] ?></div><div class="stat-lbl">Kamar Tersedia</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon si-red"><i class="fas fa-bed"></i></div>
        <div><div class="stat-val"><?= $statsK['terisi'] ?></div><div class="stat-lbl">Kamar Terisi</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon si-orange"><i class="fas fa-clipboard-list"></i></div>
        <div><div class="stat-val"><?= $statsB['t'] ?></div><div class="stat-lbl">Total Booking</div></div>
      </div>
    </div>

    <!-- DATA KAMAR TABLE -->
    <div style="margin-bottom:2rem">
      <h3 style="font-size:1rem;font-weight:700;color:var(--green3);margin-bottom:.85rem;display:flex;align-items:center;gap:.5rem">
        <i class="fas fa-door-open"></i> Data Kamar
      </h3>
      <div class="tbl-wrap">
        <table class="tbl">
          <thead>
            <tr>
              <th>Tipe Kamar</th>
              <th>Harga</th>
              <th>Fasilitas</th>
              <th>Ketersediaan</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($r = $kamarRows->fetch_assoc()): ?>
            <tr>
              <td style="font-weight:600">Kamar <?= htmlspecialchars($r['nomor_kamar']) ?> <?= htmlspecialchars($r['tipe']) ?></td>
              <td>Rp <?= number_format($r['harga_per_bulan'],0,',','.') ?>/bulan</td>
              <td style="max-width:260px;font-size:.8rem;color:var(--text3)"><?= htmlspecialchars($r['fas'] ?? '-') ?></td>
              <td>
                <?php if ($r['status_ketersediaan'] === 'Tersedia'): ?>
                  <span class="badge b-green">Tersedia</span>
                <?php elseif ($r['status_ketersediaan'] === 'Terisi'): ?>
                  <span class="badge b-red">Terisi</span>
                <?php else: ?>
                  <span class="badge b-orange">Perbaikan</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- DATA RESERVASI TABLE -->
    <div>
      <h3 style="font-size:1rem;font-weight:700;color:var(--green3);margin-bottom:.85rem;display:flex;align-items:center;gap:.5rem">
        <i class="fas fa-clipboard-list"></i> Data Reservasi
      </h3>
      <div class="tbl-wrap">
        <table class="tbl">
          <thead>
            <tr>
              <th>Nama Penyewa</th>
              <th>Kamar</th>
              <th>Tanggal Booking</th>
              <th>Aktif/Non Aktif</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($r = $bookingRows->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
              <td>Kamar <?= htmlspecialchars($r['nomor_kamar']) ?> <span style="color:var(--text3);font-size:.8rem"><?= htmlspecialchars($r['tipe']) ?></span></td>
              <td><?= $r['tgl'] ?></td>
              <td>
                <?php if ($r['status'] === 'Aktif'): ?>
                  <span style="color:var(--green3);font-weight:600">Aktif</span>
                <?php elseif ($r['status'] === 'Pending'): ?>
                  <span style="color:#92400e;font-weight:600">Pending</span>
                <?php else: ?>
                  <span style="color:var(--text3)">Non Aktif</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<?php include __DIR__ . '/../../pages/partials/footer.php'; ?>
<?php include __DIR__ . '/partials/profil-modal.php'; ?>
</body>
</html>
