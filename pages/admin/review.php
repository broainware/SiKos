<?php
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/middleware/auth.php';
requireAdmin();
$pageTitle = 'Manajemen Review'; $activePage = 'beranda';
?>
<?php include __DIR__ . '/../../pages/partials/header.php'; ?>
<div class="app-layout">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <main class="main-content">
    <div class="page-head-row" style="margin-bottom:1.75rem">
      <div>
        <h1 style="font-size:1.7rem;font-weight:800">Manajemen Review</h1>
        <p style="color:var(--text3);font-size:.875rem">Kelola ulasan dari penyewa</p>
      </div>
    </div>

    <div class="tbl-wrap" id="tblWrap">
      <div style="text-align:center;padding:2.5rem"><span class="spinner"></span></div>
    </div>
  </main>
</div>
<?php include __DIR__ . '/../../pages/partials/footer.php'; ?>
<?php include __DIR__ . '/partials/profil-modal.php'; ?>
<script>
const APP_URL = '<?= APP_URL ?>';
async function loadReviews() {
  const r = await S.req('get_reviews','GET',{});
  const wrap = document.getElementById('tblWrap');
  if (r.status !== 'success' || !r.data.length) {
    wrap.innerHTML='<div style="text-align:center;padding:3rem;color:var(--text3)">Belum ada review.</div>'; return;
  }
  wrap.innerHTML = `<table class="tbl">
    <thead><tr><th>Penyewa</th><th>Kamar</th><th>Rating</th><th>Komentar</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>${r.data.map(rv=>`
      <tr>
        <td style="font-weight:600">${rv.nama_lengkap}</td>
        <td>Kamar ${rv.nomor_kamar}</td>
        <td><span style="color:#f59e0b">${'★'.repeat(rv.rating)}</span></td>
        <td style="max-width:220px;font-size:.85rem;color:var(--text3)">${rv.komentar.substring(0,80)}${rv.komentar.length>80?'...':''}</td>
        <td style="font-size:.8rem">${S.fmtDate(rv.tanggal_review)}</td>
        <td>${S.badge(rv.status_tayang)}</td>
        <td style="white-space:nowrap">
          <button class="btn-icon" title="${rv.status_tayang==='Tayang'?'Sembunyikan':'Tampilkan'}" onclick="toggleReview(${rv.id_review})">
            <i class="fas ${rv.status_tayang==='Tayang'?'fa-eye-slash':'fa-eye'}"></i>
          </button>
          <button class="btn-icon red" title="Hapus" onclick="hapusReview(${rv.id_review})"><i class="fas fa-trash"></i></button>
        </td>
      </tr>`).join('')}
    </tbody></table>`;
}
async function toggleReview(id) {
  const r = await S.req('toggle_review','POST',{id_review:id});
  if (r.status==='success') { S.toast(r.message,'s'); loadReviews(); }
  else S.toast(r.message,'e');
}
function hapusReview(id) {
  S.confirm('Hapus review ini?', async ok => {
    if (!ok) return;
    const r = await S.req('delete_review','POST',{id_review:id});
    if (r.status==='success') { S.toast(r.message,'s'); loadReviews(); }
    else S.toast(r.message,'e');
  });
}
loadReviews();
</script>
</body>
</html>
