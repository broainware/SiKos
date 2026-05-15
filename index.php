<?php
require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/middleware/auth.php';

// Auto-redirect jika sudah login
if (isAdmin())   { header('Location: ' . APP_URL . '/pages/admin/dashboard.php'); exit; }
if (isPenyewa()) { header('Location: ' . APP_URL . '/pages/user/dashboard.php'); exit; }

$pageTitle  = 'Beranda';
$activePage = 'beranda';

// Stats langsung
$statsKamar = DB::q("SELECT COUNT(*) t, SUM(IF(status_ketersediaan='Tersedia',1,0)) tersedia FROM kamar")->fetch_assoc();
$avgRating  = DB::q("SELECT ROUND(AVG(rating),1) avg FROM review WHERE status_tayang='Tayang'")->fetch_assoc();

// Kamar tersedia untuk preview
$kamarPreview = DB::q("SELECT k.id_kamar, k.nomor_kamar, k.tipe, k.harga_per_bulan, k.foto,
    GROUP_CONCAT(f.nama_fasilitas ORDER BY f.nama_fasilitas SEPARATOR '||') fas
    FROM kamar k
    LEFT JOIN kamar_fasilitas kf ON k.id_kamar=kf.id_kamar
    LEFT JOIN fasilitas f ON kf.id_fasilitas=f.id_fasilitas
    WHERE k.status_ketersediaan='Tersedia'
    GROUP BY k.id_kamar ORDER BY k.harga_per_bulan LIMIT 8");
?>
<?php include __DIR__ . '/pages/partials/header.php'; ?>

<!-- ==================== HERO ==================== -->
<section style="
  background: linear-gradient(rgba(0,0,0,.40), rgba(0,0,0,.45)),
              url('<?= APP_URL ?>/public/images/hero-bg.png') center/cover no-repeat;
  min-height: 380px;
  display: flex;
  align-items: center;
  padding: 3.5rem 2rem;
">
  <div style="max-width:580px">
    <h1 style="color:#fff;font-size:clamp(1.8rem,4vw,2.8rem);line-height:1.2;margin-bottom:.5rem;font-family:'Poppins',sans-serif">
      SiKos (Siliwangi Kos)
    </h1>
    <h2 style="color:#a7f3c0;font-size:clamp(1.1rem,3vw,1.9rem);font-weight:700;margin-bottom:1.1rem;font-family:'Poppins',sans-serif">
      Booking Langsung, Online
    </h2>
    <p style="color:rgba(255,255,255,.88);font-size:.95rem;line-height:1.7;margin-bottom:1.75rem;max-width:460px">
      Dari nama yang familiar, lahir hunian yang beda. Reservasi real-time, bayar mudah, betah lama.
    </p>
    <a href="<?= APP_URL ?>/pages/kamar.php" class="btn btn-primary btn-lg">Lihat Kamar</a>
  </div>
</section>

<!-- ==================== STATS BAR ==================== -->
<section style="background:var(--white);padding:1.5rem 2rem;border-bottom:1px solid var(--border)">
  <div style="max-width:1200px;margin:0 auto;display:flex;gap:3.5rem;flex-wrap:wrap">
    <div>
      <div style="font-size:1.6rem;font-weight:800;font-family:'Poppins',sans-serif;color:var(--text)"><?= $statsKamar['t'] ?? 24 ?></div>
      <div style="font-size:.8rem;color:var(--text3)">Total Kamar</div>
    </div>
    <div>
      <div style="font-size:1.6rem;font-weight:800;font-family:'Poppins',sans-serif;color:var(--text)"><?= $statsKamar['tersedia'] ?? 8 ?></div>
      <div style="font-size:.8rem;color:var(--text3)">Tersedia</div>
    </div>
    <div>
      <div style="font-size:1.6rem;font-weight:800;font-family:'Poppins',sans-serif;color:var(--text)"><?= $avgRating['avg'] ?? '4.9' ?>★</div>
      <div style="font-size:.8rem;color:var(--text3)">Rating</div>
    </div>
  </div>
</section>

<!-- ==================== FITUR UNGGULAN ==================== -->
<section style="padding:2.5rem 2rem;background: linear-gradient(
    180deg,
    #E8F4DC 0%,
    #e4f4d0 100%,
    #B6C99C 100%
  );">
  <div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
    <?php foreach ([['Fasilitas','AC + Lemari'],['Keamanan','CCTV 24 Jam'],['Kebersihan','Rutin Mingguan'],['WiFi','100 Mbps']] as $f): ?>
    <div style="background:var(--white);border-radius:var(--r2);padding:1.25rem;box-shadow:var(--shadow)">
      <div style="font-weight:700;font-size:.9rem;color:var(--text);margin-bottom:.2rem"><?= $f[0] ?></div>
      <div style="font-size:.85rem;color:var(--text3)"><?= $f[1] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ==================== KAMAR TERSEDIA ==================== -->
<section style="padding:1rem 2rem 3rem;background: linear-gradient(
    180deg,
    #e4f4d0 45%,
    #e4f4d0 70%,
    #c4d8a9 100%
  );">
  <div style="max-width:1200px;margin:0 auto">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem">
      <div>
        <h2 style="font-size:1.4rem;font-weight:800">Kamar Tersedia</h2>
        <p style="color:var(--text3);font-size:.875rem"><?= $statsKamar['tersedia'] ?? 0 ?> Kamar siap untuk dipesan</p>
      </div>
      <a href="<?= APP_URL ?>/pages/auth/login.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Pesan Kamar
      </a>
    </div>

    <!-- Horizontal scroll kamar cards -->
    <div style="display:flex;gap:1.25rem;overflow-x:auto;padding-bottom:.5rem;scrollbar-width:thin">
      <?php while ($k = $kamarPreview->fetch_assoc()):
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
  </div>
</section>

<!-- ==================== REVIEW SNIPPET ==================== -->
<section style="padding:2rem 2rem 3rem;background: linear-gradient(
    180deg,
    #c4d8a9 90%,
    #B6C99C 100%
  );border-top:1px solid var(--border)">
  <div style="max-width:1200px;margin:0 auto">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem">
      <h2 style="font-size:1.3rem;font-weight:800">Ulasan Penghuni</h2>
      <a href="<?= APP_URL ?>/pages/review.php" style="font-size:.875rem;color:var(--green3);font-weight:600">Lihat Semua →</a>
    </div>
    <div id="reviewSnippet" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1rem">
      <div style="text-align:center;padding:2rem;color:var(--text3)"><span class="spinner"></span></div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/pages/partials/footer.php'; ?>

<!-- KAMAR DETAIL MODAL -->
<div class="modal-bg" id="kamarDetailModal">
  <div class="modal" style="max-width:560px">
    <div class="modal-head">
      <span class="modal-title" id="kdModalTitle"><i class="fas fa-door-open"></i> Detail Kamar</span>
      <button class="modal-close" onclick="S.closeModal('kamarDetailModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body" id="kdModalBody"></div>
    <div class="modal-foot" id="kdModalFoot"></div>
  </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';

// Load review snippet
async function loadReviews() {
  const r = await S.req('get_reviews','GET',{});
  const div = document.getElementById('reviewSnippet');
  if (r.status !== 'success' || !r.data.length) {
    div.innerHTML = '<p style="color:var(--text3);grid-column:1/-1;text-align:center">Belum ada ulasan.</p>';
    return;
  }
  div.innerHTML = r.data.slice(0,3).map(rv => `
    <div style="background:var(--white);border:1.5px solid var(--border);border-radius:var(--r2);padding:1.1rem">
      <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:.6rem">
        <div style="width:36px;height:36px;border-radius:50%;background:var(--green2);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.875rem;flex-shrink:0">${rv.nama_lengkap[0]}</div>
        <div>
          <div style="font-weight:700;font-size:.875rem">${rv.nama_lengkap}</div>
          <div style="color:var(--text3);font-size:.75rem">${S.fmtDateLong(rv.tanggal_review)}</div>
        </div>
      </div>
      <div style="color:#f59e0b;font-size:.9rem;margin-bottom:.35rem">${'★'.repeat(rv.rating)}${'☆'.repeat(5-rv.rating)}</div>
      <p style="font-size:.85rem;color:var(--text3);line-height:1.6">${rv.komentar}</p>
    </div>`).join('');
}

async function viewKamar(id) {
  S.openModal('kamarDetailModal');
  document.getElementById('kdModalBody').innerHTML = '<div style="text-align:center;padding:3rem"><span class="spinner"></span></div>';
  const r = await S.req('get_kamar_detail','GET',{id});
  if (r.status !== 'success') {
    document.getElementById('kdModalBody').innerHTML = '<p class="alert alert-error">Gagal memuat data.</p>';
    return;
  }
  const k = r.data;
  document.getElementById('kdModalTitle').innerHTML = `<i class="fas fa-door-open"></i> Kamar ${k.nomor_kamar}`;
  const img = k.foto
    ? `<img src="${APP_URL}/public/uploads/kamar/${k.foto}" style="width:100%;height:220px;object-fit:cover;border-radius:var(--r);margin-bottom:1rem">`
    : '';
  const fas = (k.fasilitas||[]).map(f=>`<span class="fas-tag">${f}</span>`).join('');
  const stars = k.avg_rating
    ? `<span style="color:#f59e0b">${'★'.repeat(Math.round(k.avg_rating))}</span> <span style="color:var(--text3);font-size:.85rem">${k.avg_rating}/5 (${k.total_reviews} ulasan)</span>`
    : '<span style="color:var(--text3);font-size:.85rem">Belum ada ulasan</span>';

  document.getElementById('kdModalBody').innerHTML = `
    ${img}
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.5rem;margin-bottom:.75rem">
      <div>
        <h3 style="font-size:1.05rem;margin-bottom:.1rem">Kamar ${k.nomor_kamar} — ${k.tipe}</h3>
        <div style="font-size:.8rem;color:var(--text3)">Lantai ${k.lantai}</div>
      </div>
      <div style="text-align:right">
        <div style="font-size:1.1rem;font-weight:800;color:var(--green3)">Rp ${Number(k.harga_per_bulan).toLocaleString('id-ID')}<span style="font-size:.75rem;font-weight:400">/bulan</span></div>
        ${k.status_ketersediaan==='Tersedia'?'<span class="badge b-green">Tersedia</span>':'<span class="badge b-red">Terisi</span>'}
      </div>
    </div>
    <p style="font-size:.875rem;color:var(--text3);margin-bottom:.85rem;line-height:1.7">${k.deskripsi||'Tidak ada deskripsi.'}</p>
    <div class="kamar-fas" style="margin-bottom:.85rem">${fas||'<span style="color:var(--text3);font-size:.85rem">-</span>'}</div>
    <div>${stars}</div>`;

  document.getElementById('kdModalFoot').innerHTML = `
    <button class="btn btn-outline btn-sm" onclick="S.closeModal('kamarDetailModal')">Tutup</button>
    ${k.status_ketersediaan==='Tersedia'
      ? `<a href="${APP_URL}/pages/auth/login.php" class="btn btn-primary btn-sm"><i class="fas fa-sign-in-alt"></i> Masuk & Pesan</a>`
      : ''}`;
}

loadReviews();
</script>
</body>
</html>
