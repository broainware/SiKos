<?php
require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/middleware/auth.php';
$pageTitle = 'Tentang Kami';
$activePage = 'tentang';
?>
<?php include __DIR__ . '/partials/header.php'; ?>

<div style="max-width:1200px;margin:0 auto;padding:3rem 1.5rem">
  <!-- TIM HEADER -->
  <div style="text-align:center;margin-bottom:3rem">
    <h1 style="font-size:2rem;font-weight:800;margin-bottom:.5rem">Tim SIKOS</h1>
    <p style="color:var(--text3);max-width:500px;margin:0 auto">Mengenal lebih dekat dengan tim hebat yang berdedikasi untuk mengembangkan solusi berkelanjutan</p>
  </div>

  <!-- METODOLOGI -->
  <div id="metodologi" style="background:var(--white);border-radius:var(--r2);padding:2rem;box-shadow:var(--shadow);margin-bottom:3rem">
    <div style="display:flex;justify-content:center;margin-bottom:1.5rem">
      <span style="border:2px solid var(--border);border-radius:var(--r3);padding:.5rem 1.5rem;font-weight:700;font-size:.875rem;color:var(--text2)">PERKENALKAN</span>
    </div>
    <h2 style="text-align:center;font-size:1.6rem;font-weight:800;color:var(--green3);margin-bottom:.5rem">Tim Perancang SIKOS</h2>
    <p style="text-align:center;color:var(--text3);margin-bottom:2.5rem">Bersama-sama berkolaborasi dalam menciptakan perubahan positif</p>

    <!-- TEAM CARDS -->
    <div id="tim" style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem">
      <?php
      $team = [
        [
          'nama' => 'Aini Nurfadilah',
          'nim' => '247006111021',
          'foto' => 'aini.jpeg',
          'linkedin' => 'https://www.linkedin.com/in/aini-nurfadilah-a8866a2b6?utm_source=share_via&utm_content=profile&utm_medium=member_android',
          'github' => 'https://github.com/broainware',
          'instagram' => 'https://www.instagram.com/anfdlha?igsh=MTF3cTljaW0xNTIyeA=='
        ],
        [
          'nama' => 'Rina Natalia',
          'nim' => '247006111033',
          'foto' => 'rina.jpg',
          'linkedin' => ' https://www.linkedin.com/in/rina-natalia-199099301?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app',
          'github' => 'https://github.com/rinaant13',
          'instagram' => 'https://www.instagram.com/rinaant_'
        ],

        [
          'nama' => 'Anggi Salsabila M Y',
          'nim' => '247006111038',
          'foto' => 'anggi.jpeg',
          'linkedin' => ' https://www.linkedin.com/in/anggi-salsabila-2b3a63337?utm_source=share_via&utm_content=profile&utm_medium=member_android
',
          'github' => 'https://github.com/Salsabilasmy635',
          'instagram' => 'https://www.instagram.com/slsbilaa_635?igsh=MXRiZGttYm1qMmlweQ==
'
        ],
      ];

      foreach ($team as $m):
        $initials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $m['nama']), 0, 2))));
      ?>
        <div style="border-radius:var(--r2);overflow:hidden;box-shadow:var(--shadow);background:var(--white);text-align:center">
          <div style="height:220px;background:linear-gradient(135deg,#e8f0e4,var(--green-lt));display:flex;align-items:center;justify-content:center">
            <?php $fotoPath = __DIR__ . '/../public/uploads/tim/' . $m['foto']; ?>
            <?php if (file_exists($fotoPath)): ?>
              <img src="<?= APP_URL ?>/public/uploads/tim/<?= $m['foto'] ?>"
                alt="<?= htmlspecialchars($m['nama']) ?>"
                style="width:100%;height:100%;object-fit:cover">
            <?php else: ?>
              <div style="width:100px;height:100px;border-radius:50%;background:var(--green2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;font-family:'Poppins',sans-serif"><?= $initials ?></div>
            <?php endif; ?>
          </div>
          <div style="padding:1.25rem">
            <div style="font-weight:700;font-size:1rem;margin-bottom:.2rem"><?= htmlspecialchars($m['nama']) ?></div>
            <div style="font-weight:700;font-size:.875rem;color:var(--text3);margin-bottom:.85rem"><?= htmlspecialchars($m['nim']) ?></div>
            <div style="display:flex;justify-content:center;gap:.65rem">
              <a href="<?= $m['linkedin'] ?>" target="_blank" style="width:34px;height:34px;border-radius:50%;border:1.5px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text3);font-size:.85rem;transition:all .18s" onmouseover="this.style.background='var(--green2)';this.style.color='#fff'" onmouseout="this.style.background='';this.style.color='var(--text3)'"><i class="fab fa-linkedin-in"></i></a>

              <a href="<?= $m['github'] ?>" target="_blank" style="width:34px;height:34px;border-radius:50%;border:1.5px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text3);font-size:.85rem;transition:all .18s" onmouseover="this.style.background='var(--green2)';this.style.color='#fff'" onmouseout="this.style.background='';this.style.color='var(--text3)'"><i class="fab fa-github"></i></a>

              <a href="<?= $m['instagram'] ?>" target="_blank" style="width:34px;height:34px;border-radius:50%;border:1.5px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text3);font-size:.85rem;transition:all .18s" onmouseover="this.style.background='var(--green2)';this.style.color='#fff'" onmouseout="this.style.background='';this.style.color='var(--text3)'"><i class="fab fa-instagram"></i></a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ABOUT SYSTEM -->
  <div style="background:var(--white);border-radius:var(--r2);padding:2rem;box-shadow:var(--shadow)">
    <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:1rem">Tentang Sistem SIKOS</h2>
    <p style="color:var(--text3);line-height:1.8;margin-bottom:1rem">
      SIKOS (Sistem Informasi Manajemen Pemesanan Kamar Kos Berbasis Web) merupakan aplikasi web yang dikembangkan untuk mempermudah proses pemesanan kamar kos secara digital. Sistem ini memungkinkan penyewa untuk melihat ketersediaan kamar secara real-time, melakukan pemesanan, dan mengupload bukti pembayaran secara online.
    </p>
    <p style="color:var(--text3);line-height:1.8">
      Admin (pemilik kos) dapat mengelola data kamar, memverifikasi pembayaran, dan memantau semua reservasi melalui dashboard yang komprehensif. Dikembangkan menggunakan teknologi PHP native, MySQL, dan JavaScript modern untuk memberikan pengalaman pengguna yang optimal.
    </p>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>

<?php if (isPenyewa() || isAdmin()): ?>
  <script>
    const APP_URL = '<?= APP_URL ?>';
    async function doLogout() {
      const r = await S.req('logout', 'POST');
      if (r.redirect) location.href = r.redirect;
    }

    function showProfil() {}
  </script>
<?php endif; ?>
</body>

</html>