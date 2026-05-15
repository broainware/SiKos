<?php
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/middleware/auth.php';
requireAdmin();
$pageTitle = 'Data Reservasi'; $activePage = 'beranda';

// Ambil data untuk form tambah
$kamarList  = DB::q("SELECT id_kamar,nomor_kamar,tipe,harga_per_bulan,status_ketersediaan FROM kamar ORDER BY lantai,nomor_kamar")->fetch_all(MYSQLI_ASSOC);
$penyewaList = DB::q("SELECT id_penyewa,nama_lengkap,username,no_hp,email FROM penyewa ORDER BY nama_lengkap")->fetch_all(MYSQLI_ASSOC);
?>
<?php include __DIR__ . '/../../pages/partials/header.php'; ?>
<div class="app-layout">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <main class="main-content">

    <div class="page-head-row" style="margin-bottom:1.75rem">
      <div>
        <h1 style="font-size:1.7rem;font-weight:800">Data Reservasi</h1>
        <p style="color:var(--text3);font-size:.875rem">Kelola semua pemesanan kamar — tambah, edit, dan hapus reservasi</p>
      </div>
      <button class="btn btn-primary" onclick="openTambah()">
        <i class="fas fa-plus"></i> Tambah Reservasi
      </button>
    </div>

    <!-- FILTER -->
    <div style="display:flex;gap:.75rem;margin-bottom:1.25rem;flex-wrap:wrap;align-items:center">
      <select id="fStatus" class="form-control" style="width:auto;min-width:160px;border-radius:var(--r3)" onchange="loadBookings()">
        <option value="">Semua Status</option>
        <option value="Pending">Pending</option>
        <option value="Aktif">Aktif</option>
        <option value="Ditolak">Ditolak</option>
        <option value="Selesai">Selesai</option>
        <option value="Dibatalkan">Dibatalkan</option>
      </select>
      <div class="input-icon" style="flex:1;max-width:320px">
        <i class="fas fa-search i-left"></i>
        <input type="text" id="fSearch" class="form-control" placeholder="Cari nama/kamar/kode..." oninput="debounceLoad()">
      </div>
    </div>

    <div class="tbl-wrap" id="tblWrap">
      <div style="text-align:center;padding:2.5rem"><span class="spinner"></span></div>
    </div>

    <!-- PERPANJANGAN SECTION -->
    <div style="margin-top:2rem">
      <h3 style="font-size:1rem;font-weight:700;color:var(--green3);margin-bottom:.85rem">
        <i class="fas fa-redo" style="margin-right:.4rem"></i> Pengajuan Perpanjangan Sewa
      </h3>
      <div class="tbl-wrap" id="perpWrap">
        <div style="text-align:center;padding:1.5rem"><span class="spinner"></span></div>
      </div>
    </div>

  </main>
</div>

<?php include __DIR__ . '/../../pages/partials/footer.php'; ?>
<?php include __DIR__ . '/partials/profil-modal.php'; ?>

<!-- ========== TAMBAH RESERVASI MODAL ========== -->
<div class="modal-bg" id="tambahModal">
  <div class="modal modal-lg">
    <div class="modal-head">
      <span class="modal-title"><i class="fas fa-plus"></i> Tambah Reservasi</span>
      <button class="modal-close" onclick="S.closeModal('tambahModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div id="tambahAlert"></div>
      <div class="alert alert-info" style="margin-bottom:1rem;font-size:.85rem">
        <i class="fas fa-info-circle"></i> Admin dapat menambahkan reservasi langsung tanpa melalui penyewa.
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">

        <!-- Penyewa: pilih dari daftar ATAU isi manual -->
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Mode Input Penyewa</label>
          <div style="display:flex;gap:.75rem">
            <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.875rem">
              <input type="radio" name="modeInput" value="existing" checked onchange="toggleMode(this.value)" style="accent-color:var(--green2)"> Pilih dari penyewa terdaftar
            </label>
            <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.875rem">
              <input type="radio" name="modeInput" value="manual" onchange="toggleMode(this.value)" style="accent-color:var(--green2)"> Input manual
            </label>
          </div>
        </div>

        <!-- Mode: existing penyewa -->
        <div id="modeExisting" class="form-group" style="grid-column:1/-1">
          <label class="form-label">Pilih Penyewa <span style="color:var(--red)">*</span></label>
          <select id="t_penyewa_id" class="form-control" onchange="fillPenyewa(this)">
            <option value="">-- Pilih Penyewa --</option>
            <?php foreach ($penyewaList as $p): ?>
            <option value="<?= $p['id_penyewa'] ?>" data-nama="<?= htmlspecialchars($p['nama_lengkap']) ?>" data-hp="<?= htmlspecialchars($p['no_hp']??'') ?>" data-email="<?= htmlspecialchars($p['email']??'') ?>">
              <?= htmlspecialchars($p['nama_lengkap']) ?> (<?= htmlspecialchars($p['username']) ?>)
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Mode: manual -->
        <div id="modeManual" style="display:none;grid-column:1/-1">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div class="form-group">
              <label class="form-label">Nama Lengkap <span style="color:var(--red)">*</span></label>
              <input type="text" id="t_nama" class="form-control" placeholder="Nama penyewa">
            </div>
            <div class="form-group">
              <label class="form-label">No. HP / WhatsApp</label>
              <input type="text" id="t_hp" class="form-control" placeholder="+62...">
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" id="t_email" class="form-control" placeholder="email@...">
            </div>
            <div class="form-group">
              <label class="form-label">Pekerjaan/Status</label>
              <select id="t_pkj" class="form-control">
                <option value="">Pilih Status</option>
                <option value="Mahasiswa">Mahasiswa</option>
                <option value="Karyawan">Karyawan</option>
                <option value="Wiraswasta">Wiraswasta</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </div>
            <div class="form-group" style="grid-column:1/-1">
              <label class="form-label">Alamat Asal</label>
              <textarea id="t_alamat" class="form-control" rows="2" placeholder="Alamat lengkap..."></textarea>
            </div>
          </div>
        </div>

        <!-- Kamar -->
        <div class="form-group">
          <label class="form-label">Kamar <span style="color:var(--red)">*</span></label>
          <select id="t_kamar" class="form-control" onchange="updateRingkasan()">
            <option value="">-- Pilih Kamar --</option>
            <?php foreach ($kamarList as $k): ?>
            <option value="<?= $k['id_kamar'] ?>"
              data-harga="<?= $k['harga_per_bulan'] ?>"
              data-nomor="<?= htmlspecialchars($k['nomor_kamar']) ?>"
              data-tipe="<?= htmlspecialchars($k['tipe']) ?>"
              data-status="<?= htmlspecialchars($k['status_ketersediaan']) ?>">
              Kamar <?= htmlspecialchars($k['nomor_kamar']) ?> (<?= htmlspecialchars($k['tipe']) ?>) — Rp <?= number_format($k['harga_per_bulan'],0,',','.') ?>/bln
              <?= $k['status_ketersediaan'] !== 'Tersedia' ? ' [' . $k['status_ketersediaan'] . ']' : '' ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Status Booking</label>
          <select id="t_status" class="form-control">
            <option value="Pending">Pending</option>
            <option value="Aktif" selected>Aktif</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Tanggal Mulai <span style="color:var(--red)">*</span></label>
          <input type="date" id="t_tgl_mulai" class="form-control" onchange="updateRingkasan()">
        </div>

        <div class="form-group">
          <label class="form-label">Durasi Sewa (bulan) <span style="color:var(--red)">*</span></label>
          <select id="t_durasi" class="form-control" onchange="updateRingkasan()">
            <?php for($i=1;$i<=24;$i++): ?>
            <option value="<?=$i?>" <?=$i==1?'selected':''?>><?=$i?> Bulan</option>
            <?php endfor; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Metode Pembayaran</label>
          <select id="t_metode" class="form-control">
            <option value="Transfer BRI">Transfer BRI</option>
            <option value="Transfer BNI">Transfer BNI</option>
            <option value="GoPay">GoPay</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Catatan (opsional)</label>
          <input type="text" id="t_catatan" class="form-control" placeholder="Catatan reservasi...">
        </div>
      </div>

      <!-- RINGKASAN -->
      <div style="background:var(--green-xl);border-radius:var(--r);padding:1rem;margin-top:.5rem;font-size:.875rem">
        <div style="font-weight:700;margin-bottom:.5rem">Ringkasan Reservasi</div>
        <div style="display:flex;justify-content:space-between;margin-bottom:.3rem"><span>Tanggal Selesai</span><span id="r_tgl_selesai" style="font-weight:600">-</span></div>
        <div style="display:flex;justify-content:space-between"><span style="font-weight:700">Total Harga</span><span id="r_total" style="font-weight:800;color:var(--green3);font-size:1rem">Rp 0</span></div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-outline btn-sm" onclick="S.closeModal('tambahModal')">Batal</button>
      <button class="btn btn-primary btn-sm" id="tambahBtn" onclick="submitTambah()"><i class="fas fa-save"></i> Simpan Reservasi</button>
    </div>
  </div>
</div>

<!-- ========== DETAIL/EDIT MODAL ========== -->
<div class="modal-bg" id="detailModal">
  <div class="modal modal-lg">
    <div class="modal-head">
      <span class="modal-title" id="detailModalTitle"><i class="fas fa-clipboard-list"></i> Detail Reservasi</span>
      <button class="modal-close" onclick="S.closeModal('detailModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body" id="detailBody"></div>
    <div class="modal-foot" id="detailFoot"></div>
  </div>
</div>

<!-- ========== VERIFIKASI PEMBAYARAN MODAL ========== -->
<div class="modal-bg" id="verifModal">
  <div class="modal" style="max-width:480px">
    <div class="modal-head">
      <span class="modal-title"><i class="fas fa-shield-alt"></i> Verifikasi Pembayaran</span>
      <button class="modal-close" onclick="S.closeModal('verifModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div id="verifModalAlert"></div>
      <input type="hidden" id="vm_idPm">
      <input type="hidden" id="vm_idPerp">

      <div id="vm_buktiWrap" style="margin-bottom:1rem"></div>

      <div class="form-group">
        <label class="form-label">Catatan / Alasan (jika ditolak)</label>
        <textarea id="vm_catatan" class="form-control" rows="3" placeholder="Isi alasan jika menolak pembayaran..."></textarea>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-outline btn-sm" onclick="S.closeModal('verifModal')">Batal</button>
      <button class="btn btn-danger btn-sm" id="vm_rejectBtn" onclick="doVerif('reject')"><i class="fas fa-times"></i> Tolak</button>
      <button class="btn btn-primary btn-sm" id="vm_approveBtn" onclick="doVerif('approve')"><i class="fas fa-check"></i> Setujui</button>
    </div>
  </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
let debTimer;
function debounceLoad() { clearTimeout(debTimer); debTimer=setTimeout(loadBookings,380); }

// ====== MODE TOGGLE ======
function toggleMode(mode) {
  document.getElementById('modeExisting').style.display = mode==='existing'?'block':'none';
  document.getElementById('modeManual').style.display   = mode==='manual'?'block':'none';
}

function fillPenyewa(sel) {
  const opt = sel.options[sel.selectedIndex];
  document.getElementById('t_nama').value  = opt.dataset.nama||'';
  document.getElementById('t_hp').value    = opt.dataset.hp||'';
  document.getElementById('t_email').value = opt.dataset.email||'';
}

// ====== RINGKASAN ======
function updateRingkasan() {
  const kSel = document.getElementById('t_kamar');
  const opt  = kSel.options[kSel.selectedIndex];
  const harga  = parseFloat(opt?.dataset?.harga||0);
  const durasi = parseInt(document.getElementById('t_durasi').value||1);
  const tglMulai = document.getElementById('t_tgl_mulai').value;

  const total = harga * durasi;
  document.getElementById('r_total').textContent = 'Rp ' + total.toLocaleString('id-ID');

  if (tglMulai) {
    const dt = new Date(tglMulai);
    dt.setMonth(dt.getMonth() + durasi);
    document.getElementById('r_tgl_selesai').textContent = dt.toLocaleDateString('id-ID',{day:'2-digit',month:'long',year:'numeric'});
  } else {
    document.getElementById('r_tgl_selesai').textContent = '-';
  }
}

// ====== TAMBAH RESERVASI ======
function openTambah() {
  document.getElementById('tambahAlert').innerHTML = '';
  document.getElementById('t_penyewa_id').value = '';
  document.getElementById('t_kamar').value = '';
  document.getElementById('t_tgl_mulai').value = new Date().toISOString().split('T')[0];
  document.getElementById('t_durasi').value = '1';
  document.getElementById('t_catatan').value = '';
  toggleMode('existing');
  updateRingkasan();
  S.openModal('tambahModal');
}

async function submitTambah() {
  const btn = document.getElementById('tambahBtn');
  const al  = document.getElementById('tambahAlert');

  const mode = document.querySelector('input[name="modeInput"]:checked').value;
  const idPenyewa = mode==='existing' ? document.getElementById('t_penyewa_id').value : '';
  const nama   = document.getElementById('t_nama').value.trim();
  const hp     = document.getElementById('t_hp').value.trim();
  const email  = document.getElementById('t_email').value.trim();
  const pkj    = document.getElementById('t_pkj').value;
  const alamat = document.getElementById('t_alamat').value.trim();
  const idKamar  = document.getElementById('t_kamar').value;
  const tglMulai = document.getElementById('t_tgl_mulai').value;
  const durasi   = document.getElementById('t_durasi').value;
  const status   = document.getElementById('t_status').value;
  const metode   = document.getElementById('t_metode').value;
  const catatan  = document.getElementById('t_catatan').value.trim();

  al.innerHTML = '';
  if (mode==='existing' && !idPenyewa) { al.innerHTML='<div class="alert alert-error">Pilih penyewa dari daftar.</div>'; return; }
  if (mode==='manual' && !nama) { al.innerHTML='<div class="alert alert-error">Nama penyewa wajib diisi.</div>'; return; }
  if (!idKamar) { al.innerHTML='<div class="alert alert-error">Pilih kamar terlebih dahulu.</div>'; return; }
  if (!tglMulai) { al.innerHTML='<div class="alert alert-error">Tanggal mulai wajib diisi.</div>'; return; }

  const fd = new FormData();
  fd.append('id_kamar', idKamar);
  fd.append('tanggal_mulai', tglMulai);
  fd.append('durasi_bulan', durasi);
  fd.append('metode_pembayaran', metode);
  fd.append('catatan', catatan);
  fd.append('admin_created', '1'); // Flag: dibuat admin
  fd.append('admin_status', status);

  // Penyewa info
  if (idPenyewa) {
    fd.append('id_penyewa_override', idPenyewa);
    const opt = document.getElementById('t_penyewa_id').options[document.getElementById('t_penyewa_id').selectedIndex];
    fd.append('nama_penyewa', opt.dataset.nama||'');
    fd.append('no_hp_penyewa', opt.dataset.hp||'');
    fd.append('email_penyewa', opt.dataset.email||'');
  } else {
    fd.append('nama_penyewa', nama);
    fd.append('no_hp_penyewa', hp);
    fd.append('email_penyewa', email);
    fd.append('pekerjaan', pkj);
    fd.append('alamat_asal', alamat);
  }

  S.loading(btn, true);
  const r = await S.req('create_booking','POST', fd);
  S.loading(btn, false);

  if (r.status === 'success') {
    S.toast('Reservasi berhasil ditambahkan!', 's');
    S.closeModal('tambahModal');
    loadBookings();
  } else {
    al.innerHTML = `<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ${r.message}</div>`;
  }
}

// ====== LOAD BOOKINGS ======
async function loadBookings() {
  const status = document.getElementById('fStatus').value;
  const q      = document.getElementById('fSearch').value;
  const wrap   = document.getElementById('tblWrap');
  wrap.innerHTML = '<div style="text-align:center;padding:2.5rem"><span class="spinner"></span></div>';
  const r = await S.req('get_bookings','GET',{status,q});
  if (r.status !== 'success' || !r.data.length) {
    wrap.innerHTML = '<div style="text-align:center;padding:3rem;color:#fffff">Tidak ada data reservasi.</div>';
    return;
  }
  wrap.innerHTML = `<table class="tbl">
    <thead><tr><th>ID Pesanan</th><th>Penyewa</th><th>Kamar</th><th>Tanggal Mulai</th><th>Durasi</th><th>Total</th><th>Pembayaran</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>${r.data.map(b => `
    <tr>
      <td style="font-size:.75rem;font-weight:600;color:var(--text3)">${b.kode_booking}</td>
      <td>
        <div style="font-weight:600;font-size:.875rem">${b.nama_lengkap||b.nama_penyewa||'-'}</div>
        <div style="font-size:.75rem;color:var(--text3)">${b.no_hp||b.no_hp_penyewa||''}</div>
      </td>
      <td>Kamar ${b.nomor_kamar} <span style="font-size:.78rem;color:var(--text3)">${b.tipe}</span></td>
      <td style="font-size:.8rem">${S.fmtDate(b.tanggal_mulai)}</td>
      <td style="text-align:center">${b.durasi_bulan} Bln</td>
      <td style="font-weight:600;font-size:.85rem">Rp ${Number(b.total_harga).toLocaleString('id-ID')}</td>
      <td>${S.badge(b.status_pembayaran||'Menunggu')}
        ${b.bukti_pembayaran && (b.status_pembayaran==='Proses Validasi')
          ? `<div style="margin-top:.25rem"><button class="btn btn-outline btn-sm" style="font-size:.72rem" onclick="openVerifModal(${b.id_pembayaran||0},0,'${b.bukti_pembayaran||''}')"><i class="fas fa-shield-alt"></i> Verifikasi</button></div>`
          : ''}
      </td>
      <td>${S.badge(b.status)}</td>
      <td style="white-space:nowrap">
        <button class="btn-icon" title="Detail" onclick="openDetail(${b.id_booking})"><i class="fas fa-eye"></i></button>
        <button class="btn-icon red" title="Hapus" onclick="hapus(${b.id_booking})"><i class="fas fa-trash"></i></button>
      </td>
    </tr>`).join('')}
    </tbody></table>`;
}

// ====== LOAD PERPANJANGAN ======
async function loadPerpanjangan() {
  const wrap = document.getElementById('perpWrap');
  const r = await S.req('get_perpanjangan','GET',{admin:1});
  if (r.status !== 'success' || !r.data.length) {
    wrap.innerHTML = '<div style="text-align:center;padding:1.5rem;color:#fffff;font-size:.875rem">Belum ada pengajuan perpanjangan.</div>';
    return;
  }
  wrap.innerHTML = `<table class="tbl">
    <thead><tr><th>Booking</th><th>Penyewa</th><th>Kamar</th><th>Durasi</th><th>Periode</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>${r.data.map(p => `
    <tr>
      <td style="font-size:.75rem;font-weight:600">${p.kode_booking}</td>
      <td style="font-size:.875rem">${p.nama_lengkap}</td>
      <td>Kamar ${p.nomor_kamar}</td>
      <td style="text-align:center">${p.durasi_tambah} Bln</td>
      <td style="font-size:.78rem">${S.fmtDate(p.tanggal_mulai)} — ${S.fmtDate(p.tanggal_selesai)}</td>
      <td style="font-weight:600">Rp ${Number(p.total_harga).toLocaleString('id-ID')}</td>
      <td>${S.badge(p.status)}</td>
      <td style="white-space:nowrap">
        ${p.status==='Proses Validasi'
          ? `<button class="btn btn-outline btn-sm" onclick="openVerifModal(0,${p.id_perpanjangan},'${p.bukti_pembayaran||''}')"><i class="fas fa-shield-alt"></i> Verifikasi</button>`
          : `<span style="font-size:.8rem;color:var(--text3)">${p.status}</span>`
        }
      </td>
    </tr>`).join('')}
    </tbody></table>`;
}

// ====== DETAIL ======
async function openDetail(id) {
  S.openModal('detailModal');
  document.getElementById('detailBody').innerHTML='<div style="text-align:center;padding:3rem"><span class="spinner"></span></div>';
  const r = await S.req('get_booking_detail','GET',{id});
  if (r.status !== 'success') { document.getElementById('detailBody').innerHTML='<p class="alert alert-error">Gagal memuat.</p>'; return; }
  const b = r.data;
  document.getElementById('detailBody').innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
      <div><div style="font-size:.75rem;font-weight:600;color:var(--text3)">ID Pesanan</div><div style="font-weight:700">${b.kode_booking}</div></div>
      <div><div style="font-size:.75rem;font-weight:600;color:var(--text3)">Status</div>${S.badge(b.status)}</div>
      <div><div style="font-size:.75rem;font-weight:600;color:var(--text3)">Penyewa</div><div>${b.nama_lengkap||b.nama_penyewa||'-'}</div></div>
      <div><div style="font-size:.75rem;font-weight:600;color:var(--text3)">No. HP</div><div>${b.no_hp||b.no_hp_penyewa||'-'}</div></div>
      <div><div style="font-size:.75rem;font-weight:600;color:var(--text3)">Email</div><div>${b.p_email||b.email_penyewa||'-'}</div></div>
      <div><div style="font-size:.75rem;font-weight:600;color:var(--text3)">Kamar</div><div>Kamar ${b.nomor_kamar} ${b.tipe}</div></div>
      <div><div style="font-size:.75rem;font-weight:600;color:var(--text3)">Tanggal Sewa</div><div style="font-size:.875rem">${S.fmtDate(b.tanggal_mulai)} — ${S.fmtDate(b.tanggal_selesai)}</div></div>
      <div><div style="font-size:.75rem;font-weight:600;color:var(--text3)">Durasi</div><div>${b.durasi_bulan} Bulan</div></div>
      <div style="grid-column:1/-1"><div style="font-size:.75rem;font-weight:600;color:var(--text3)">Total Harga</div><div style="font-size:1.1rem;font-weight:800;color:var(--green3)">Rp ${Number(b.total_harga).toLocaleString('id-ID')}</div></div>
    </div>
    <div style="background:var(--green-xl);border-radius:var(--r);padding:.9rem">
      <div style="font-weight:600;font-size:.875rem;margin-bottom:.4rem">Pembayaran</div>
      <div style="font-size:.875rem">${S.badge(b.status_pembayaran||'Menunggu')}</div>
      ${b.bukti_pembayaran?`<div style="margin-top:.5rem"><a href="${APP_URL}/public/uploads/pembayaran/${b.bukti_pembayaran}" target="_blank" class="btn btn-outline btn-sm"><i class="fas fa-file-image"></i> Lihat Bukti</a></div>`:'<div style="font-size:.8rem;color:var(--text3);margin-top:.25rem">Belum ada bukti pembayaran.</div>'}
      ${b.catatan_admin?`<div style="font-size:.8rem;margin-top:.5rem;color:var(--text3)"><strong>Catatan admin:</strong> ${b.catatan_admin}</div>`:''}
    </div>`;

  const statusOpts = ['Pending','Aktif','Ditolak','Selesai','Dibatalkan'].map(s=>`<option value="${s}" ${b.status===s?'selected':''}>${s}</option>`).join('');
  document.getElementById('detailFoot').innerHTML=`
    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;width:100%">
      <div style="flex:1">
        <label style="font-size:.8rem;font-weight:600;color:var(--text3);display:block;margin-bottom:.25rem">Update Status</label>
        <select id="newStatus" class="form-control" style="border-radius:var(--r3)">${statusOpts}</select>
      </div>
      <button class="btn btn-primary btn-sm" onclick="updateStatus(${b.id_booking})" style="align-self:flex-end"><i class="fas fa-save"></i> Simpan</button>
      <button class="btn btn-outline btn-sm" onclick="S.closeModal('detailModal')" style="align-self:flex-end">Tutup</button>
    </div>`;
}

async function updateStatus(id) {
  const status = document.getElementById('newStatus').value;
  const r = await S.req('update_booking','POST',{id_booking:id,status});
  if (r.status==='success') { S.toast(r.message,'s'); S.closeModal('detailModal'); loadBookings(); }
  else S.toast(r.message,'e');
}

// ====== VERIF MODAL ======
function openVerifModal(idPm, idPerp, bukti) {
  document.getElementById('vm_idPm').value   = idPm;
  document.getElementById('vm_idPerp').value  = idPerp;
  document.getElementById('vm_catatan').value = '';
  document.getElementById('verifModalAlert').innerHTML = '';
  const buktiHtml = bukti
    ? `<div style="margin-bottom:1rem"><a href="${APP_URL}/public/uploads/pembayaran/${bukti}" target="_blank" class="btn btn-outline btn-sm"><i class="fas fa-file-image"></i> Lihat Bukti Pembayaran</a></div>`
    : '<div class="alert alert-warning" style="margin-bottom:1rem">Belum ada bukti pembayaran yang diupload.</div>';
  document.getElementById('vm_buktiWrap').innerHTML = buktiHtml;
  S.openModal('verifModal');
}

async function doVerif(aksi) {
  const idPm   = document.getElementById('vm_idPm').value;
  const idPerp = document.getElementById('vm_idPerp').value;
  const catatan= document.getElementById('vm_catatan').value;
  const al     = document.getElementById('verifModalAlert');
  const btnId  = aksi==='approve' ? 'vm_approveBtn' : 'vm_rejectBtn';
  const btn    = document.getElementById(btnId);
  S.loading(btn, true);
  const r = await S.req('verifikasi','POST',{id_pembayaran:idPm||0,id_perpanjangan:idPerp||0,aksi,catatan});
  S.loading(btn, false);
  if (r.status==='success') {
    S.toast(r.message,'s');
    S.closeModal('verifModal');
    loadBookings(); loadPerpanjangan();
  } else {
    al.innerHTML=`<div class="alert alert-error">${r.message}</div>`;
  }
}

// ====== HAPUS ======
function hapus(id) {
  S.confirm('Hapus reservasi ini? Data tidak bisa dikembalikan.', async ok => {
    if (!ok) return;
    const r = await S.req('delete_booking','POST',{id_booking:id});
    if (r.status==='success') { S.toast(r.message,'s'); loadBookings(); }
    else S.toast(r.message,'e');
  });
}

// Set default tanggal hari ini
document.getElementById('t_tgl_mulai').value = new Date().toISOString().split('T')[0];

loadBookings();
loadPerpanjangan();
</script>
</body>
</html>
