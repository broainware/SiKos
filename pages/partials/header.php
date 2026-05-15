<?php
// partials/header.php
// Usage: include with $pageTitle, $activePage defined
if (!defined('APP_URL')) {
    require_once __DIR__ . '/../../backend/config/database.php';
}
require_once __DIR__ . '/../../backend/middleware/auth.php';
$_adminSess = isAdmin() ? getAdmin() : null;
$_penyewaSess = isPenyewa() ? getPenyewa() : null;
$_isLoggedIn = $_adminSess || $_penyewaSess;
$_userName = $_adminSess ? $_adminSess['nama'] : ($_penyewaSess ? $_penyewaSess['nama'] : '');
$_pageTitle = $pageTitle ?? 'SIKOS';
$_activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="app-url" content="<?= APP_URL ?>">
<title><?= htmlspecialchars($_pageTitle) ?> — SIKOS</title>
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar" style="background:#E8F4DC !important;">
  <a href="<?= APP_URL ?>/index.php" class="nav-logo">
    <div class="nav-logo-icon">
      <svg width="28" height="28" viewBox="0 0 32 32" fill="none">
        <path d="M16 3L3 14h4v15h8v-8h2v8h8V14h4L16 3z" fill="#3d6b4a"/>
        <rect x="13" y="19" width="6" height="9" rx="1" fill="#6b8f71"/>
      </svg>
    </div>
    <span>SIKOS</span>
  </a>
  <a href="<?= APP_URL ?>/index.php" class="nav-link <?= $_activePage==='beranda'?'active':'' ?>">Beranda</a>
  <a href="<?= APP_URL ?>/pages/kamar.php" class="nav-link <?= $_activePage==='kamar'?'active':'' ?>">Kamar</a>
  <a href="<?= APP_URL ?>/pages/tentang.php" class="nav-link <?= $_activePage==='tentang'?'active':'' ?>">Tentang</a>
  <a href="<?= APP_URL ?>/pages/kontak.php" class="nav-link <?= $_activePage==='kontak'?'active':'' ?>">Kontak</a>
  <?php if ($_isLoggedIn): ?>
    <button class="nav-user" onclick="showProfil()">
      <?= htmlspecialchars(explode(' ', $_userName)[0]) ?>
      <div class="nav-user-icon"><i class="fas fa-user" style="font-size:.75rem"></i></div>
    </button>
  <?php else: ?>
    <a href="<?= APP_URL ?>/pages/auth/login.php" class="nav-guest">
      Masuk/Daftar
      <div class="nav-guest-icon"><i class="fas fa-user"></i></div>
    </a>
  <?php endif; ?>
</nav>
