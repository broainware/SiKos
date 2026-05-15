<?php
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/middleware/auth.php';
requireAdmin();
$pageTitle = 'Data Kamar'; $activePage = 'beranda';

// Stats
$stats = DB::q("SELECT COUNT(*) t,
  SUM(IF(status_ketersediaan='Terisi',1,0)) terisi,
  SUM(IF(status_ketersediaan='Tersedia',1,0)) tersedia,
  SUM(IF(status_ketersediaan='Perbaikan',1,0)) perbaikan
  FROM kamar")->fetch_assoc();

$fasAll = DB::q("SELECT * FROM fasilitas ORDER BY nama_fasilitas")->fetch_all(MYSQLI_ASSOC);
?>
<?php include __DIR__ . '/../../pages/partials/header.php'; ?>
<div class="app-layout">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <main class="main-content">

    <!-- PAGE HEADER -->
    <div class="page-head-row" style="margin-bottom:1.75rem">
      <div>
        <h1 style="font-size:1.7rem;font-weight:800">Manajemen Data Kamar</h1>
        <p style="color:var(--text3);font-size:.875rem">Kelola seluruh kamar kos — tambah, edit, dan hapus data kamar</p>
      </div>
      <button class="btn btn-primary" onclick="openTambah()">
        <i class="fas fa-plus"></i> Tambah Kamar
      </button>
    </div>

    <!-- STATS -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-icon si-green"><i class="fas fa-door-open"></i></div>
        <div><div class="stat-val"><?= $stats['t'] ?></div><div class="stat-lbl">Total Kamar</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon si-red"><i class="fas fa-check"></i></div>
        <div><div class="stat-val"><?= $stats['terisi'] ?></div><div class="stat-lbl">Terisi bulan ini</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon si-green"><i class="fas fa-door-open"></i></div>
        <div><div class="stat-val"><?= $stats['tersedia'] ?></div><div class="stat-lbl">Tersedia</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon si-orange"><i class="fas fa-wrench"></i></div>
        <div><div class="stat-val"><?= $stats['perbaikan'] ?></div><div class="stat-lbl">Perbaikan</div></div>
      </div>
    </div>

    <!-- FILTER -->
    <div style="display:flex;gap:.75rem;margin-bottom:1.25rem;flex-wrap:wrap">
      <select id="filterStatus" class="form-control" style="width:auto;min-width:150px;border-radius:var(--r3)" onchange="loadKamar()">
        <option value="">Semua Status</option>
        <option value="Tersedia">Tersedia</option>
        <option value="Terisi">Terisi</option>
        <option value="Perbaikan">Perbaikan</option>
      </select>
      <select id="filterTipe" class="form-control" style="width:auto;min-width:150px;border-radius:var(--r3)" onchange="loadKamar()">
        <option value="">Semua Tipe</option>
        <option value="Standar">Standar</option>
        <option value="Premier">Premier</option>
        <option value="Deluxe">Deluxe</option>
      </select>
    </div>

    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;flex-wrap:wrap">
      <div style="display:flex;gap:.5rem">
        <button id="viewCardBtn" class="btn btn-outline btn-sm active" onclick="setKamarView('card')">Card View</button>
        <button id="viewTableBtn" class="btn btn-outline btn-sm" onclick="setKamarView('table')">Table View</button>
      </div>
    </div>

    <!-- KAMAR GRID -->
    <div class="kamar-grid" id="kamarGrid">
      <div style="grid-column:1/-1;text-align:center;padding:2rem"><span class="spinner"></span></div>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../pages/partials/footer.php'; ?>
<?php include __DIR__ . '/partials/profil-modal.php'; ?>

<!-- TAMBAH KAMAR MODAL -->
<div class="modal-bg" id="tambahModal">
  <div class="modal modal-lg">
    <div class="modal-head">
      <span class="modal-title"><i class="fas fa-plus"></i> Tambah Kamar</span>
      <button class="modal-close" onclick="S.closeModal('tambahModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div id="tambahAlert"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group">
          <label class="form-label">Nomor Kamar <span style="color:var(--red)">*</span></label>
          <input type="text" id="t_nomor" class="form-control" placeholder="Contoh: A1">
        </div>
        <div class="form-group">
          <label class="form-label">Tipe <span style="color:var(--red)">*</span></label>
          <select id="t_tipe" class="form-control">
            <option value="Standar">Standar</option>
            <option value="Premier">Premier</option>
            <option value="Deluxe">Deluxe</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Lantai <span style="color:var(--red)">*</span></label>
          <input type="number" id="t_lantai" class="form-control" value="1" min="1">
        </div>
        <div class="form-group">
          <label class="form-label">Harga/Bulan (Rp) <span style="color:var(--red)">*</span></label>
          <input type="number" id="t_harga" class="form-control" placeholder="600000">
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select id="t_status" class="form-control">
            <option value="Tersedia">Tersedia</option>
            <option value="Terisi">Terisi</option>
            <option value="Perbaikan">Perbaikan</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Foto Kamar</label>
          <input type="file" id="t_foto" class="form-control" accept="image/*">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Deskripsi</label>
        <textarea id="t_desc" class="form-control" rows="3" placeholder="Deskripsi kamar..."></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Fasilitas</label>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;background:var(--green-xl);border-radius:var(--r);padding:.85rem">
          <?php foreach ($fasAll as $f): ?>
          <label style="display:flex;align-items:center;gap:.35rem;font-size:.85rem;cursor:pointer;background:var(--white);padding:.3rem .7rem;border-radius:var(--r3);border:1.5px solid var(--border)">
            <input type="checkbox" name="t_fas[]" value="<?= $f['id_fasilitas'] ?>" style="accent-color:var(--green2)">
            <?= htmlspecialchars($f['nama_fasilitas']) ?>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-outline btn-sm" onclick="S.closeModal('tambahModal')">Batal</button>
      <button class="btn btn-primary btn-sm" id="tambahBtn" onclick="submitTambah()"><i class="fas fa-save"></i> Simpan</button>
    </div>
  </div>
</div>

<!-- EDIT KAMAR MODAL -->
<div class="modal-bg" id="editModal">
  <div class="modal modal-lg">
    <div class="modal-head">
      <span class="modal-title"><i class="fas fa-edit"></i> Edit Kamar</span>
      <button class="modal-close" onclick="S.closeModal('editModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div id="editAlert"></div>
      <input type="hidden" id="e_id">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group">
          <label class="form-label">Nomor Kamar <span style="color:var(--red)">*</span></label>
          <input type="text" id="e_nomor" class="form-control">
        </div>
        <div class="form-group">
          <label class="form-label">Tipe <span style="color:var(--red)">*</span></label>
          <select id="e_tipe" class="form-control">
            <option value="Standar">Standar</option><option value="Premier">Premier</option><option value="Deluxe">Deluxe</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Lantai</label>
          <input type="number" id="e_lantai" class="form-control" min="1">
        </div>
        <div class="form-group">
          <label class="form-label">Harga/Bulan (Rp)</label>
          <input type="number" id="e_harga" class="form-control">
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select id="e_status" class="form-control">
            <option value="Tersedia">Tersedia</option><option value="Terisi">Terisi</option><option value="Perbaikan">Perbaikan</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Deskripsi</label>
        <textarea id="e_desc" class="form-control" rows="3"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Fasilitas</label>
        <div id="e_fas_container" style="display:flex;flex-wrap:wrap;gap:.5rem;background:var(--green-xl);border-radius:var(--r);padding:.85rem">
          <?php foreach ($fasAll as $f): ?>
          <label style="display:flex;align-items:center;gap:.35rem;font-size:.85rem;cursor:pointer;background:var(--white);padding:.3rem .7rem;border-radius:var(--r3);border:1.5px solid var(--border)">
            <input type="checkbox" class="e_fas_cb" value="<?= $f['id_fasilitas'] ?>" style="accent-color:var(--green2)">
            <?= htmlspecialchars($f['nama_fasilitas']) ?>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-outline btn-sm" onclick="S.closeModal('editModal')">Batal</button>
      <button class="btn btn-primary btn-sm" id="editBtn" onclick="submitEdit()"><i class="fas fa-save"></i> Simpan</button>
    </div>
  </div>
</div>

<!-- HAPUS MODAL -->
<div class="modal-bg" id="hapusModal">
  <div class="modal" style="max-width:400px">
    <div class="modal-head" style="background:#c0392b">
      <span class="modal-title"><i class="fas fa-trash"></i> Hapus Kamar</span>
      <button class="modal-close" onclick="S.closeModal('hapusModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body" style="text-align:center;padding:2rem 1.5rem">
      <i class="fas fa-exclamation-triangle" style="font-size:2.5rem;color:var(--orange);margin-bottom:1rem;display:block"></i>
      <p style="font-size:.95rem;font-weight:600">Hapus kamar <strong id="hapusNama"></strong>?</p>
      <p style="font-size:.85rem;color:var(--text3);margin-top:.5rem">Data yang dihapus tidak bisa dikembalikan.</p>
      <input type="hidden" id="hapusId">
    </div>
    <div class="modal-foot" style="justify-content:center">
      <button class="btn btn-outline btn-sm" onclick="S.closeModal('hapusModal')">Batal</button>
      <button class="btn btn-danger btn-sm" id="hapusBtn" onclick="submitHapus()"><i class="fas fa-trash"></i> Ya, Hapus</button>
    </div>
  </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
let kamarViewMode = 'card';

function setKamarView(mode) {
  kamarViewMode = mode;
  const grid = document.getElementById('kamarGrid');
  grid.classList.toggle('table-view', mode === 'table');
  document.getElementById('viewCardBtn').classList.toggle('active', mode === 'card');
  document.getElementById('viewTableBtn').classList.toggle('active', mode === 'table');
  loadKamar();
}

async function loadKamar() {
  const status = document.getElementById('filterStatus').value;
  const tipe   = document.getElementById('filterTipe').value;
  const grid   = document.getElementById('kamarGrid');
  grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:2rem"><span class="spinner"></span></div>';
  const r = await S.req('get_kamar','GET',{status,tipe});
  if (r.status !== 'success' || !r.data.length) {
    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--text3)"><i class="fas fa-door-open" style="font-size:2rem;margin-bottom:.5rem;display:block"></i>Tidak ada kamar ditemukan.</div>';
    return;
  }
  if (kamarViewMode === 'table') {
    grid.innerHTML = `
      <div class="tbl-wrap">
        <table class="tbl">
          <thead>
            <tr>
              <th>#</th>
              <th>Nomor</th>
              <th>Tipe</th>
              <th>Harga</th>
              <th>Status</th>
              <th>Fasilitas</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            ${r.data.map((k,i) => {
              const fas = k.fasilitas.slice(0,3).map(f=>`<span class="fas-tag">${f}</span>`).join(' ');
              const badge = k.status_ketersediaan==='Tersedia'?'<span class="badge b-green">Tersedia</span>':k.status_ketersediaan==='Terisi'?'<span class="badge b-red">Terisi</span>':'<span class="badge b-orange">Perbaikan</span>';
              return `
                <tr>
                  <td>${i+1}</td>
                  <td>${k.nomor_kamar}</td>
                  <td>${k.tipe}</td>
                  <td>Rp ${Number(k.harga_per_bulan).toLocaleString('id-ID')}</td>
                  <td>${badge}</td>
                  <td>${fas}</td>
                  <td style="white-space:nowrap">
                    <button class="btn btn-outline btn-sm" onclick="openDetail(${k.id_kamar})"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-outline btn-sm" onclick="openEdit(${k.id_kamar})"><i class="fas fa-pencil-alt"></i></button>
                    <button class="btn btn-danger btn-sm" onclick="openHapus(${k.id_kamar},'${k.nomor_kamar}')"><i class="fas fa-trash"></i></button>
                  </td>
                </tr>`;
            }).join('')}
          </tbody>
        </table>
      </div>`;
    return;
  }

  grid.innerHTML = r.data.map(k => {
    const img = k.foto ? `<img src="${APP_URL}/public/uploads/kamar/${k.foto}" alt="Kamar ${k.nomor_kamar}">` : `<div class="kamar-img-placeholder"><i class="fas fa-door-open"></i></div>`;
    const fas = k.fasilitas.slice(0,3).map(f=>`<span class="fas-tag">${f}</span>`).join('');
    const badge = k.status_ketersediaan==='Tersedia'?'<span class="badge b-green">Tersedia</span>':k.status_ketersediaan==='Terisi'?'<span class="badge b-red">Terisi</span>':'<span class="badge b-orange">Perbaikan</span>';
    return `
    <div class="kamar-card">
      <div class="kamar-img">${img}<div class="kamar-badge">${badge}</div></div>
      <div class="kamar-body">
        <div class="kamar-name">Kamar ${k.nomor_kamar} ${k.tipe}</div>
        <div class="kamar-price">Rp ${Number(k.harga_per_bulan).toLocaleString('id-ID')}/bulan</div>
        <div class="kamar-fas">${fas}</div>
        <div class="kamar-actions">
          <button class="btn btn-outline btn-sm" onclick="openDetail(${k.id_kamar})"><i class="fas fa-eye"></i> Detail</button>
          <button class="btn-icon" onclick="openEdit(${k.id_kamar})"><i class="fas fa-pencil-alt"></i></button>
          <button class="btn-icon red" onclick="openHapus(${k.id_kamar},'${k.nomor_kamar}')"><i class="fas fa-trash"></i></button>
        </div>
      </div>
    </div>`;
  }).join('');
}

function openTambah() { document.getElementById('tambahAlert').innerHTML=''; S.openModal('tambahModal'); }

async function submitTambah() {
  const btn = document.getElementById('tambahBtn');
  const al = document.getElementById('tambahAlert');
  const fd = new FormData();
  fd.append('nomor_kamar', document.getElementById('t_nomor').value.trim());
  fd.append('tipe', document.getElementById('t_tipe').value);
  fd.append('lantai', document.getElementById('t_lantai').value);
  fd.append('harga_per_bulan', document.getElementById('t_harga').value);
  fd.append('status_ketersediaan', document.getElementById('t_status').value);
  fd.append('deskripsi', document.getElementById('t_desc').value);
  document.querySelectorAll('input[name="t_fas[]"]:checked').forEach(cb => fd.append('fasilitas[]', cb.value));
  const fotoFile = document.getElementById('t_foto').files[0];
  if (fotoFile) fd.append('foto', fotoFile);
  S.loading(btn, true);
  const r = await S.req('create_kamar','POST',fd);
  S.loading(btn, false);
  if (r.status === 'success') { S.toast(r.message,'s'); S.closeModal('tambahModal'); loadKamar(); }
  else al.innerHTML = `<div class="alert alert-error">${r.message}</div>`;
}

async function openEdit(id) {
  const r = await S.req('get_kamar_detail','GET',{id});
  if (r.status !== 'success') { S.toast('Gagal memuat data','e'); return; }
  const k = r.data;
  document.getElementById('e_id').value = k.id_kamar;
  document.getElementById('e_nomor').value = k.nomor_kamar;
  document.getElementById('e_tipe').value = k.tipe;
  document.getElementById('e_lantai').value = k.lantai;
  document.getElementById('e_harga').value = k.harga_per_bulan;
  document.getElementById('e_status').value = k.status_ketersediaan;
  document.getElementById('e_desc').value = k.deskripsi || '';
  const fasIds = (k.fas_ids_arr || []).map(String);
  document.querySelectorAll('.e_fas_cb').forEach(cb => cb.checked = fasIds.includes(cb.value));
  document.getElementById('editAlert').innerHTML = '';
  S.openModal('editModal');
}

async function submitEdit() {
  const btn = document.getElementById('editBtn');
  const al = document.getElementById('editAlert');
  const fd = new FormData();
  fd.append('id_kamar', document.getElementById('e_id').value);
  fd.append('nomor_kamar', document.getElementById('e_nomor').value.trim());
  fd.append('tipe', document.getElementById('e_tipe').value);
  fd.append('lantai', document.getElementById('e_lantai').value);
  fd.append('harga_per_bulan', document.getElementById('e_harga').value);
  fd.append('status_ketersediaan', document.getElementById('e_status').value);
  fd.append('deskripsi', document.getElementById('e_desc').value);
  document.querySelectorAll('.e_fas_cb:checked').forEach(cb => fd.append('fasilitas[]', cb.value));
  S.loading(btn, true);
  const r = await S.req('update_kamar','POST',fd);
  S.loading(btn, false);
  if (r.status === 'success') { S.toast(r.message,'s'); S.closeModal('editModal'); loadKamar(); }
  else al.innerHTML = `<div class="alert alert-error">${r.message}</div>`;
}

function openHapus(id, nama) {
  document.getElementById('hapusId').value = id;
  document.getElementById('hapusNama').textContent = `Kamar ${nama}`;
  S.openModal('hapusModal');
}

async function submitHapus() {
  const btn = document.getElementById('hapusBtn');
  const id = document.getElementById('hapusId').value;
  S.loading(btn, true);
  const r = await S.req('delete_kamar','POST',{id_kamar: id});
  S.loading(btn, false);
  if (r.status === 'success') { S.toast(r.message,'s'); S.closeModal('hapusModal'); loadKamar(); }
  else S.toast(r.message,'e');
}

async function openDetail(id) {
  const r = await S.req('get_kamar_detail','GET',{id});
  if (r.status !== 'success') { S.toast('Gagal memuat','e'); return; }
  const k = r.data;
  const img = k.foto ? `<img src="${APP_URL}/public/uploads/kamar/${k.foto}" style="width:100%;height:220px;object-fit:cover;border-radius:var(--r)">` : '';
  const fas = (k.fasilitas||[]).map(f=>`<span class="fas-tag">${f}</span>`).join('');
  const revHtml = (k.reviews||[]).map(rv=>`
    <div style="padding:.7rem 0;border-bottom:1px solid var(--border)">
      <div style="font-weight:600;font-size:.85rem">${rv.nama_lengkap}</div>
      <div style="color:#f59e0b;font-size:.8rem">${'★'.repeat(rv.rating)}${'☆'.repeat(5-rv.rating)}</div>
      <div style="font-size:.85rem;color:var(--text3)">${rv.komentar}</div>
    </div>`).join('') || '<p style="color:var(--text3);font-size:.85rem">Belum ada review.</p>';

  const existing = document.getElementById('_detailModal');
  if (existing) existing.remove();
  document.body.insertAdjacentHTML('beforeend', `
    <div class="modal-bg open" id="_detailModal">
      <div class="modal modal-lg">
        <div class="modal-head">
          <span class="modal-title"><i class="fas fa-door-open"></i> Detail Kamar ${k.nomor_kamar}</span>
          <button class="modal-close" onclick="document.getElementById('_detailModal').remove()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
          ${img}
          <div style="margin-top:1rem">
            <h3 style="font-size:1.1rem">Kamar ${k.nomor_kamar} — ${k.tipe}</h3>
            <div style="color:var(--green3);font-weight:700;font-size:1rem;margin:.3rem 0">Rp ${Number(k.harga_per_bulan).toLocaleString('id-ID')}/bulan</div>
            <p style="font-size:.875rem;color:var(--text3);margin-bottom:.85rem">${k.deskripsi||''}</p>
            <div class="kamar-fas" style="margin-bottom:1rem">${fas}</div>
            <div style="font-weight:700;font-size:.875rem;margin-bottom:.5rem">Ulasan (${k.avg_rating}★ · ${k.total_reviews} ulasan)</div>
            <div>${revHtml}</div>
          </div>
        </div>
        <div class="modal-foot">
          <button class="btn btn-outline btn-sm" onclick="document.getElementById('_detailModal').remove()">Tutup</button>
          <button class="btn btn-primary btn-sm" onclick="document.getElementById('_detailModal').remove();openEdit(${k.id_kamar})"><i class="fas fa-edit"></i> Edit</button>
        </div>
      </div>
    </div>`);
}

loadKamar();
</script>
</body>
</html>
