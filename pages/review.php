<?php
require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/middleware/auth.php';
$pageTitle = 'Review & Testimoni'; $activePage = '';

$totalRev = DB::q("SELECT COUNT(*) c FROM review WHERE status_tayang='Tayang'")->fetch_assoc()['c'];
$avgRating = DB::q("SELECT ROUND(AVG(rating),1) avg FROM review WHERE status_tayang='Tayang'")->fetch_assoc()['avg'] ?? 0;
$dist = DB::q("SELECT rating, COUNT(*) c FROM review WHERE status_tayang='Tayang' GROUP BY rating ORDER BY rating DESC")->fetch_all(MYSQLI_ASSOC);
$distMap = []; foreach ($dist as $d) $distMap[$d['rating']] = $d['c'];
?>
<?php include __DIR__ . '/partials/header.php'; ?>

<div style="max-width:1200px;margin:0 auto;padding:2rem 1.5rem">
  <div style="margin-bottom:1rem">
    <a href="<?= APP_URL ?>/index.php" style="font-size:.875rem;color:var(--text3);display:inline-flex;align-items:center;gap:.4rem">
      <i class="fas fa-arrow-left"></i> Kembali ke Beranda
    </a>
  </div>
  <h1 style="font-size:1.8rem;font-weight:800;text-align:center;margin-bottom:2rem">Review dan Testimoni Asli</h1>

  <div style="display:grid;grid-template-columns:1fr 2fr;gap:2rem;align-items:start">
    <!-- LEFT: Rating Summary -->
    <div>
      <div style="background:var(--white);border-radius:var(--r2);padding:1.5rem;box-shadow:var(--shadow)">
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem">
          <div id="reviewAvg" style="font-size:2.5rem;font-weight:800;font-family:'Poppins',sans-serif">★ <?= $avgRating ?></div>
          <div id="reviewTotal" style="font-size:.95rem;font-weight:600"><?= $totalRev ?> ulasan total:</div>
        </div>
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem">
          <span style="font-size:.85rem;font-weight:600;min-width:30px">5★</span>
          <div style="flex:1;background:var(--border);border-radius:var(--r3);height:8px;overflow:hidden"><div id="reviewDist5Bar" style="background:#1a2e1a;height:100%;border-radius:var(--r3);width:<?= $totalRev ? round(($distMap[5]??0)/$totalRev*100) : 0 ?>%"></div></div>
          <span id="reviewDist5Count" style="font-size:.8rem;color:var(--text3);min-width:28px">(<?= $distMap[5]??0 ?>)</span>
        </div>
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem">
          <span style="font-size:.85rem;font-weight:600;min-width:30px">4★</span>
          <div style="flex:1;background:var(--border);border-radius:var(--r3);height:8px;overflow:hidden"><div id="reviewDist4Bar" style="background:#1a2e1a;height:100%;border-radius:var(--r3);width:<?= $totalRev ? round(($distMap[4]??0)/$totalRev*100) : 0 ?>%"></div></div>
          <span id="reviewDist4Count" style="font-size:.8rem;color:var(--text3);min-width:28px">(<?= $distMap[4]??0 ?>)</span>
        </div>
        <div style="display:flex;align-items:center;gap:.5rem">
          <span style="font-size:.85rem;font-weight:600;min-width:30px">3★</span>
          <div style="flex:1;background:var(--border);border-radius:var(--r3);height:8px;overflow:hidden"><div id="reviewDist3Bar" style="background:#1a2e1a;height:100%;border-radius:var(--r3);width:<?= $totalRev ? round(($distMap[3]??0)/$totalRev*100) : 0 ?>%"></div></div>
          <span id="reviewDist3Count" style="font-size:.8rem;color:var(--text3);min-width:28px">(<?= $distMap[3]??0 ?>)</span>
        </div>
      </div>

      <?php if (!isPenyewa() && !isAdmin()): ?>
      <div style="margin-top:1.5rem;text-align:center;background:var(--white);border-radius:var(--r2);padding:1.5rem;box-shadow:var(--shadow)">
        <div style="font-size:1rem;font-weight:700;margin-bottom:.75rem">Silahkan Masuk/Daftar untuk reservasi atau memberikan review!</div>
        <a href="<?= APP_URL ?>/pages/auth/login.php" class="btn btn-primary btn-full">Masuk / Daftar</a>
      </div>
      <?php endif; ?>

      <?php if (isPenyewa()): ?>
      <div style="margin-top:1.5rem;background:var(--white);border-radius:var(--r2);padding:1.5rem;box-shadow:var(--shadow)">
        <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1rem">Tulis Ulasan</h2>
        <div id="reviewFormAlert"></div>
        <div class="form-group">
          <label class="form-label">Pilih Booking</label>
          <select id="review_booking" class="form-control">
            <option value="">Pilih booking untuk ditinjau...</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Rating</label>
          <div id="reviewStars" style="display:flex;gap:.35rem;font-size:1.25rem"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Komentar</label>
          <textarea id="reviewKomentar" class="form-control" rows="4" placeholder="Tuliskan pengalaman Anda..."></textarea>
        </div>
        <button class="btn btn-primary btn-full" onclick="submitReview()">Kirim Ulasan</button>
      </div>
      <?php endif; ?>
    </div>

    <!-- RIGHT: Reviews List -->
    <div>
      <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1rem">Ulasan Terbaru</h2>
      <div id="reviewList">
        <div style="text-align:center;padding:2rem"><span class="spinner"></span></div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
<script>
const APP_URL = '<?= APP_URL ?>';
async function loadReviews() {
  const r = await S.req('get_reviews','GET',{});
  const div = document.getElementById('reviewList');
  if (r.status !== 'success' || !r.data.length) {
    div.innerHTML = '<p style="color:var(--text3)">Belum ada ulasan.</p>';
    if (r.summary) updateReviewSummary(r.summary);
    return;
  }
  div.innerHTML = r.data.map(rv => `
    <div style="background:var(--white);border-radius:var(--r2);padding:1.1rem;margin-bottom:.85rem;box-shadow:var(--shadow)">
      <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:.5rem">
        <div style="width:36px;height:36px;border-radius:50%;background:var(--green2);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.875rem;flex-shrink:0">${rv.nama_lengkap[0]}</div>
        <div>
          <div style="font-weight:700;font-size:.9rem">${rv.nama_lengkap}</div>
          <div style="font-size:.75rem;color:var(--text3)">${S.fmtDateLong(rv.tanggal_review)}</div>
        </div>
      </div>
      <div style="color:#f59e0b;margin-bottom:.35rem">${'★'.repeat(rv.rating)}${'☆'.repeat(5-rv.rating)}</div>
      <p style="font-size:.875rem;color:var(--text3)">${rv.komentar}</p>
    </div>`).join('');
  if (r.summary) updateReviewSummary(r.summary);
}

function updateReviewSummary(summary) {
  document.getElementById('reviewAvg').textContent = '★ ' + Number(summary.avg || 0).toFixed(1);
  document.getElementById('reviewTotal').textContent = `${summary.total || 0} ulasan total:`;
  const getPct = (value) => summary.total ? Math.round(((summary.dist?.[value]||0) / summary.total) * 100) : 0;
  document.getElementById('reviewDist5Bar').style.width = getPct(5) + '%';
  document.getElementById('reviewDist4Bar').style.width = getPct(4) + '%';
  document.getElementById('reviewDist3Bar').style.width = getPct(3) + '%';
  document.getElementById('reviewDist5Count').textContent = `(${summary.dist?.[5]||0})`;
  document.getElementById('reviewDist4Count').textContent = `(${summary.dist?.[4]||0})`;
  document.getElementById('reviewDist3Count').textContent = `(${summary.dist?.[3]||0})`;
}

loadReviews();
<?php if (isPenyewa()): ?>
let reviewRating = 0;
function initReviewStars() {
  reviewRating = 0;
  const container = document.getElementById('reviewStars');
  if (!container) return;
  const getRating = S.stars(container, value => reviewRating = value);
  reviewRating = getRating();
}

async function loadReviewBookings() {
  const sel = document.getElementById('review_booking');
  const r = await S.req('get_my_bookings','GET',{});
  if (r.status !== 'success' || !Array.isArray(r.data)) {
    sel.innerHTML = '<option value="">Tidak ada booking tersedia untuk review.</option>';
    return;
  }
  const bookings = r.data.filter(b => ['Aktif','Selesai'].includes(b.status));
  if (!bookings.length) {
    sel.innerHTML = '<option value="">Belum ada booking yang dapat direview.</option>';
    return;
  }
  sel.innerHTML = '<option value="">Pilih booking untuk ditinjau...</option>' + bookings.map(b =>
    `<option value="${b.id_booking}" data-kamar="${b.id_kamar}">Booking ${b.kode_booking} — Kamar ${b.nomor_kamar} (${b.tipe})</option>`
  ).join('');
}

async function submitReview() {
  const alert = document.getElementById('reviewFormAlert');
  alert.innerHTML = '';
  const sel = document.getElementById('review_booking');
  const komentar = document.getElementById('reviewKomentar').value.trim();
  const bookingId = sel.value;
  const kamarId = sel.selectedOptions[0]?.dataset?.kamar || '';
  if (!bookingId) { alert.innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Pilih booking terlebih dahulu.</div>'; return; }
  if (!reviewRating) { alert.innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Pilih rating 1-5 bintang.</div>'; return; }
  if (!komentar) { alert.innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Komentar tidak boleh kosong.</div>'; return; }
  const r = await S.req('create_review','POST',{
    id_booking: bookingId,
    id_kamar: kamarId,
    rating: reviewRating,
    komentar
  });
  if (r.status === 'success') {
    S.toast(r.message,'s');
    document.getElementById('reviewKomentar').value = '';
    reviewRating = 0;
    initReviewStars();
    loadReviewBookings();
    loadReviews();
  } else {
    alert.innerHTML = `<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ${r.message}</div>`;
  }
}

initReviewStars();
loadReviewBookings();
<?php endif; ?>
</script>
</body>
</html>
