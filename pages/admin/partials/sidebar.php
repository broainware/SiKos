<?php
// pages/admin/partials/sidebar.php
$adm = getAdmin();
$initials = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ', $adm['nama']), 0, 2))));
$curPage = basename($_SERVER['PHP_SELF'], '.php');
function sideActive(string $page): string {
    global $curPage;
    return $curPage === $page ? 'active' : '';
}
?>
<aside class="sidebar admin-sidebar" id="sidebar">
  <!-- Profile Block -->
  <div class="sidebar-profile">
    <div class="sidebar-avatar"><?= htmlspecialchars($initials) ?></div>
    <div>
      <div class="sidebar-name"><?= htmlspecialchars($adm['nama']) ?></div>
      <div class="sidebar-role">Pemilik Kos</div>
    </div>
  </div>

  <!-- Nav Links -->
  <a href="<?= APP_URL ?>/pages/admin/dashboard.php" class="sidebar-link <?= sideActive('dashboard') ?>">
    <i class="fas fa-home"></i> Dashboard
  </a>
  <a href="<?= APP_URL ?>/pages/admin/data-kamar.php" class="sidebar-link <?= sideActive('data-kamar') ?>">
    <i class="fas fa-door-open"></i> Data Kamar
  </a>
  <a href="<?= APP_URL ?>/pages/admin/data-reservasi.php" class="sidebar-link <?= sideActive('data-reservasi') ?>">
    <i class="fas fa-clipboard-list"></i> Data Reservasi
  </a>
  <a href="<?= APP_URL ?>/pages/admin/verifikasi.php" class="sidebar-link <?= sideActive('verifikasi') ?>">
    <i class="fas fa-credit-card"></i> Verifikasi Transaksi
  </a>
  <a href="<?= APP_URL ?>/pages/admin/kalender.php" class="sidebar-link <?= sideActive('kalender') ?>">
    <i class="fas fa-calendar-alt"></i> Kalender Master
  </a>

  <a href="<?= APP_URL ?>/pages/admin/review.php" class="sidebar-link <?= sideActive('review') ?>">
    <i class="fas fa-star"></i> Manajemen Review
  </a>

  <div class="sidebar-sep"></div>

  <a href="javascript:void(0)" class="sidebar-link <?= sideActive('profil-admin') ?>" onclick="showProfil()">
    <i class="fas fa-user"></i> Profil Admin
  </a>
  <a href="javascript:void(0)" class="sidebar-link" onclick="confirmLogout()">
    <i class="fas fa-sign-out-alt"></i> Keluar
  </a>
</aside>
