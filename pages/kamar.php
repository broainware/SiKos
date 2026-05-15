<?php
require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/middleware/auth.php';
$pageTitle = 'Katalog Kamar';
$activePage = 'kamar';
?>
<?php include __DIR__ . '/partials/header.php'; ?>

<div style="
  width:100%;
  background: linear-gradient(
    180deg,
  #E8F4DC 0%,
  #e4f4d0 45%,
  #B6C99C 75%,
  #B6C99C 100%
  );
">

  <div style="max-width:1200px;margin:0 auto;padding:2.5rem 1.5rem">
    <div style="margin-bottom:2rem">
      <h1 style="font-size:1.8rem;font-weight:800">Katalog Kamar</h1>
      <p style="color:var(--text3)">Temukan kamar yang sesuai dengan kebutuhan Anda</p>
    </div>

    <!-- FILTER BAR -->
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.75rem;background:var(--white);padding:1rem;border-radius:var(--r2);box-shadow:var(--shadow)">
      <div class="input-icon" style="flex:1;min-width:200px">
        <i class="fas fa-search i-left"></i>
        <input type="text" id="fSearch" class="form-control" placeholder="Cari kamar..." oninput="debounce()">
      </div>
      <select id="fStatus" class="form-control" style="width:auto;min-width:150px;border-radius:var(--r3)" onchange="loadKamar()">
        <option value="Tersedia">Tersedia</option>
        <option value="">Semua</option>
      </select>
      <select id="fTipe" class="form-control" style="width:auto;min-width:150px;border-radius:var(--r3)" onchange="loadKamar()">
        <option value="">Semua Tipe</option>
        <option value="Standar">Standar</option>
        <option value="Premier">Premier</option>
        <option value="Deluxe">Deluxe</option>
      </select>
    </div>

    <!-- KAMAR GRID -->
    <div class="kamar-grid" id="kamarGrid">
      <div style="grid-column:1/-1;text-align:center;padding:3rem"><span class="spinner"></span></div>
    </div>
  </div>

  <!-- DETAIL MODAL -->
  <div class="modal-bg" id="detailModal">
    <div class="modal modal-lg">
      <div class="modal-head">
        <span class="modal-title" id="detailTitle"><i class="fas fa-door-open"></i> Detail Kamar</span>
        <button class="modal-close" onclick="S.closeModal('detailModal')"><i class="fas fa-times"></i></button>
      </div>
      <div class="modal-body" id="detailBody"></div>
      <div class="modal-foot" id="detailFoot"></div>
    </div>
  </div>

  <?php include __DIR__ . '/partials/footer.php'; ?>
</div>
</div>
<script>
  const APP_URL = '<?= APP_URL ?>';
  const IS_PENYEWA = <?= isPenyewa() ? 'true' : 'false' ?>;

  let debTimer;

  function debounce() {
    clearTimeout(debTimer);
    debTimer = setTimeout(loadKamar, 350);
  }

  async function loadKamar() {
    const status = document.getElementById('fStatus').value;
    const tipe = document.getElementById('fTipe').value;
    const q = document.getElementById('fSearch').value;
    const grid = document.getElementById('kamarGrid');
    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:3rem"><span class="spinner"></span></div>';
    const r = await S.req('get_kamar', 'GET', {
      status,
      tipe,
      q
    });
    if (r.status !== 'success' || !r.data.length) {
      grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:4rem;color:var(--text3)"><i class="fas fa-door-open" style="font-size:2.5rem;display:block;margin-bottom:.75rem"></i><strong>Tidak ada kamar ditemukan</strong><br>Coba ubah filter pencarian Anda.</div>';
      return;
    }
    grid.innerHTML = r.data.map(k => {
      const img = k.foto ?
        `<img src="${APP_URL}/public/uploads/kamar/${k.foto}" alt="Kamar ${k.nomor_kamar}">` :
        `<div class="kamar-img-placeholder"><i class="fas fa-door-open"></i></div>`;
      const fas = (k.fasilitas || []).slice(0, 3).map(f => `<span class="fas-tag">${f}</span>`).join('');
      const badge = k.status_ketersediaan === 'Tersedia' ? '<span class="badge b-green">Tersedia</span>' : k.status_ketersediaan === 'Terisi' ? '<span class="badge b-red">Terisi</span>' : '<span class="badge b-orange">Perbaikan</span>';
      return `
    <div class="kamar-card">
      <div class="kamar-img">${img}<div class="kamar-badge">${badge}</div></div>
      <div class="kamar-body">
        <div class="kamar-name">Kamar ${k.nomor_kamar}</div>
        <div style="font-size:.8rem;color:var(--text3);margin-bottom:.2rem">${k.tipe} · Lantai ${k.lantai}</div>
        <div class="kamar-price">Rp ${Number(k.harga_per_bulan).toLocaleString('id-ID')}<span style="font-size:.8rem;font-weight:400">/bulan</span></div>
        <div class="kamar-fas">${fas}</div>
        <div class="kamar-actions">
          <button class="btn btn-outline btn-sm" onclick="openDetail(${k.id_kamar})"><i class="fas fa-eye"></i> Detail</button>
          ${k.status_ketersediaan==='Tersedia' && IS_PENYEWA
            ? `<a href="${APP_URL}/pages/user/booking.php?kamar_id=${k.id_kamar}" class="btn btn-primary btn-sm"><i class="fas fa-calendar-plus"></i> Pesan</a>`
            : k.status_ketersediaan==='Tersedia'
            ? `<a href="${APP_URL}/pages/auth/login.php" class="btn btn-primary btn-sm"><i class="fas fa-sign-in-alt"></i> Masuk & Pesan</a>`
            : ''}
        </div>
      </div>
    </div>`;
    }).join('');
  }

  async function openDetail(id) {
    S.openModal('detailModal');
    document.getElementById('detailBody').innerHTML = '<div style="text-align:center;padding:3rem"><span class="spinner"></span></div>';
    const r = await S.req('get_kamar_detail', 'GET', {
      id
    });
    if (r.status !== 'success') return;
    const k = r.data;
    document.getElementById('detailTitle').innerHTML = `<i class="fas fa-door-open"></i> Kamar ${k.nomor_kamar}`;
    const img = k.foto ? `<img src="${APP_URL}/public/uploads/kamar/${k.foto}" style="width:100%;height:240px;object-fit:cover;border-radius:var(--r);margin-bottom:1rem">` : '';
    const fas = (k.fasilitas || []).map(f => `<span class="fas-tag">${f}</span>`).join('');
    const stars = k.avg_rating ? `<span style="color:#f59e0b;font-size:1.1rem">${'★'.repeat(Math.round(k.avg_rating))}</span> <span style="color:var(--text3);font-size:.85rem">${k.avg_rating} (${k.total_reviews} ulasan)</span>` : '<span style="color:var(--text3);font-size:.85rem">Belum ada ulasan</span>';
    const revHtml = (k.reviews || []).map(rv => `
    <div style="padding:.75rem 0;border-bottom:1px solid var(--border)">
      <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem">
        <div style="width:30px;height:30px;border-radius:50%;background:var(--green2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700">${rv.nama_lengkap[0]}</div>
        <div>
          <div style="font-weight:600;font-size:.875rem">${rv.nama_lengkap}</div>
          <div style="color:#f59e0b;font-size:.8rem">${'★'.repeat(rv.rating)}${'☆'.repeat(5-rv.rating)}</div>
        </div>
      </div>
      <div style="font-size:.875rem;color:var(--text3)">${rv.komentar}</div>
    </div>`).join('') || '<p style="color:var(--text3);font-size:.85rem;padding:.5rem 0">Belum ada ulasan.</p>';

    document.getElementById('detailBody').innerHTML = `
    ${img}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:.75rem">
      <div>
        <h3 style="font-size:1.1rem">Kamar ${k.nomor_kamar} — ${k.tipe}</h3>
        <div style="font-size:.8rem;color:var(--text3)">Lantai ${k.lantai}</div>
      </div>
      <div>
        <div style="font-size:1.2rem;font-weight:800;color:var(--green3)">Rp ${Number(k.harga_per_bulan).toLocaleString('id-ID')}<span style="font-size:.8rem;font-weight:400">/bulan</span></div>
        ${k.status_ketersediaan === 'Tersedia' ? '<span class="badge b-green">Tersedia</span>' : k.status_ketersediaan === 'Terisi' ? '<span class="badge b-red">Terisi</span>' : '<span class="badge b-orange">Perbaikan</span>'}
      </div>
    </div>
    <p style="font-size:.875rem;color:var(--text3);margin-bottom:.85rem">${k.deskripsi||'Tidak ada deskripsi.'}</p>
    <div class="kamar-fas" style="margin-bottom:1rem">${fas}</div>
    <div style="margin-bottom:.5rem">${stars}</div>
    <div style="max-height:220px;overflow-y:auto">${revHtml}</div>`;

    document.getElementById('detailFoot').innerHTML = `
    <button class="btn btn-outline btn-sm" onclick="S.closeModal('detailModal')">Tutup</button>
    ${k.status_ketersediaan==='Tersedia' && IS_PENYEWA
      ? `<a href="${APP_URL}/pages/user/booking.php?kamar_id=${k.id_kamar}" class="btn btn-primary btn-sm"><i class="fas fa-calendar-plus"></i> Pesan Kamar Ini</a>`
      : k.status_ketersediaan==='Tersedia'
      ? `<a href="${APP_URL}/pages/auth/login.php" class="btn btn-primary btn-sm"><i class="fas fa-sign-in-alt"></i> Masuk & Pesan</a>`
      : ''}`;
  }

  loadKamar();
</script>
</body>

</html>