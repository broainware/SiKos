<?php
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/middleware/auth.php';
requireAdmin();
$pageTitle = 'Verifikasi Transaksi'; $activePage = 'beranda';
?>
<?php include __DIR__ . '/../../pages/partials/header.php'; ?>
<div class="app-layout">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <main class="main-content">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.75rem">
      <div>
        <h1 style="font-size:1.7rem;font-weight:800">Verifikasi Transaksi</h1>
        <p style="color:var(--text3);font-size:.875rem">Kelola dan verifikasi semua pembayaran dari penyewa</p>
      </div>
      <div class="input-icon" style="max-width:320px;flex:1">
        <i class="fas fa-search i-left"></i>
        <input type="text" id="fSearch" class="form-control" placeholder="Cari ID pesanan atau kamar..." oninput="debounceLoad()">
      </div>
    </div>

    <!-- STATUS TABS -->
    <div style="display:flex;align-items:center;max-width:580px;margin-bottom:1.75rem">
      <button onclick="setTab('Menunggu')" id="tab_1"
        style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.3rem;background:none;border:none;cursor:pointer;padding:.5rem">
        <div id="circ_1" style="width:32px;height:32px;border-radius:50%;background:var(--green2);border:2px solid var(--green2);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;color:#fff">1</div>
        <span id="lbl_1" style="font-size:.72rem;font-weight:600;color:var(--green3);text-align:center">Menunggu<br>Validasi</span>
      </button>
      <div id="ln_1" style="flex:1;height:2px;background:var(--border);margin-bottom:1.6rem;transition:background .2s"></div>
      <button onclick="setTab('Proses Validasi')" id="tab_2"
        style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.3rem;background:none;border:none;cursor:pointer;padding:.5rem">
        <div id="circ_2" style="width:32px;height:32px;border-radius:50%;background:var(--card);border:2px solid var(--border);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;color:var(--text3)">2</div>
        <span id="lbl_2" style="font-size:.72rem;font-weight:600;color:var(--text3);text-align:center">Proses<br>Validasi</span>
      </button>
      <div id="ln_2" style="flex:1;height:2px;background:var(--border);margin-bottom:1.6rem;transition:background .2s"></div>
      <button onclick="setTab('Disetujui')" id="tab_3"
        style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.3rem;background:none;border:none;cursor:pointer;padding:.5rem">
        <div id="circ_3" style="width:32px;height:32px;border-radius:50%;background:var(--card);border:2px solid var(--border);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;color:var(--text3)">3</div>
        <span id="lbl_3" style="font-size:.72rem;font-weight:600;color:var(--text3)">Disetujui</span>
      </button>
    </div>

    <div class="tbl-wrap" id="tblWrap">
      <div style="text-align:center;padding:2.5rem"><span class="spinner"></span></div>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../pages/partials/footer.php'; ?>
<?php include __DIR__ . '/partials/profil-modal.php'; ?>

<!-- VERIFIKASI MODAL -->
<div class="modal-bg" id="verifModal">
  <div class="modal" style="max-width:520px">
    <div class="modal-head">
      <span class="modal-title"><i class="fas fa-shield-alt"></i> Verifikasi Pembayaran</span>
      <button class="modal-close" onclick="S.closeModal('verifModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div id="verifAlert"></div>
      <input type="hidden" id="v_idPm">
      <input type="hidden" id="v_idPerp">

      <!-- Info pembayaran -->
      <div id="v_info" style="background:var(--green-xl);border-radius:var(--r);padding:1rem;margin-bottom:1rem;font-size:.875rem">
        <div id="v_info_content">Memuat...</div>
      </div>

      <!-- Bukti -->
      <div id="v_bukti" style="margin-bottom:1rem"></div>

      <div class="form-group">
        <label class="form-label">Catatan / Alasan Penolakan</label>
        <textarea id="v_catatan" class="form-control" rows="3" placeholder="Isi alasan jika menolak, atau kosongkan jika menyetujui..."></textarea>
      </div>

      <div class="alert alert-warning" style="font-size:.82rem">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Jika ditolak:</strong> Booking tidak dihapus. Penyewa akan mendapat notifikasi alasan penolakan dan dapat mengupload ulang bukti pembayaran yang benar.
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-outline btn-sm" onclick="S.closeModal('verifModal')">Batal</button>
      <button class="btn btn-danger btn-sm" id="v_rejectBtn" onclick="doVerif('reject')"><i class="fas fa-times"></i> Tolak Pembayaran</button>
      <button class="btn btn-primary btn-sm" id="v_approveBtn" onclick="doVerif('approve')"><i class="fas fa-check"></i> Setujui & Aktifkan</button>
    </div>
  </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
let curTab = 'Menunggu';
let debTimer;
let verifBookingData = [];
let verifPerpData = [];
function debounceLoad() { clearTimeout(debTimer); debTimer=setTimeout(()=>loadData(curTab), 380); }

function setTab(status) {
  curTab = status;
  const map = {'Menunggu':1,'Proses Validasi':2,'Disetujui':3};
  const n = map[status];
  [1,2,3].forEach(i => {
    const active = i===n;
    document.getElementById('circ_'+i).style.background = active?'var(--green2)':'var(--card)';
    document.getElementById('circ_'+i).style.borderColor = active?'var(--green2)':'var(--border)';
    document.getElementById('circ_'+i).style.color = active?'#fff':'var(--text3)';
    document.getElementById('lbl_'+i).style.color = active?'var(--green3)':'var(--text3)';
  });
  [1,2].forEach(i => document.getElementById('ln_'+i).style.background = i<n?'var(--green2)':'var(--border)');
  loadData(status);
}

async function loadData(status) {
  const q = document.getElementById('fSearch').value;
  const wrap = document.getElementById('tblWrap');
  wrap.innerHTML = '<div style="text-align:center;padding:2.5rem"><span class="spinner"></span></div>';

  // Load pembayaran booking
  const r = await S.req('get_pembayaran','GET',{status,q});
  // Load perpanjangan
  const rP = await S.req('get_perpanjangan','GET',{admin:1,status});

  verifBookingData = r.status==='success' ? r.data : [];
  verifPerpData = rP.status==='success' ? rP.data : [];
  const bookingData  = verifBookingData.filter(p => {
    const s = status==='Menunggu'?p.status_pembayaran==='Menunggu':status==='Proses Validasi'?p.status_pembayaran==='Proses Validasi':p.status_pembayaran==='Disetujui';
    const matchQ = !q || p.kode_booking.toLowerCase().includes(q.toLowerCase()) || (p.nomor_kamar||'').toLowerCase().includes(q.toLowerCase());
    return s && matchQ;
  });
  const perpData     = verifPerpData.filter(p => {
    const s = status==='Menunggu'?p.status==='Menunggu':status==='Proses Validasi'?p.status==='Proses Validasi':p.status==='Disetujui';
    const matchQ = !q || p.kode_booking.toLowerCase().includes(q.toLowerCase()) || (p.nomor_kamar||'').toLowerCase().includes(q.toLowerCase());
    return s && matchQ;
  });

  if (!bookingData.length && !perpData.length) {
    wrap.innerHTML = '<div style="text-align:center;padding:3rem;color:#fffff">Tidak ada transaksi pada tahap ini.</div>';
    return;
  }

  let html = '';

  // Booking payments
  if (bookingData.length) {
    html += `<div style="font-weight:700;font-size:.875rem;color:var(--green3);margin-bottom:.5rem;padding:.5rem 1.1rem;background:var(--green-xl);border-radius:var(--r) var(--r) 0 0">Pembayaran Booking</div>
    <table class="tbl" style="margin-bottom:1.5rem">
    <thead><tr><th>ID Pesanan</th><th>Penyewa</th><th>Kamar</th><th>Total</th><th>Tgl Upload</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>${bookingData.map(p => {
      let aksi = '';
      if (p.status_pembayaran === 'Proses Validasi') {
        aksi = `<button class="btn btn-primary btn-sm" onclick="openVerif('booking',${p.id_pembayaran})"><i class="fas fa-shield-alt"></i> Verifikasi</button>`;
      } else if (p.status_pembayaran === 'Ditolak') {
        aksi = `<div style="font-size:.78rem;color:var(--red)"><i class="fas fa-times-circle"></i> Ditolak</div>
                <button class="btn btn-warning btn-sm" style="margin-top:.25rem;font-size:.72rem" onclick="openVerif('booking',${p.id_pembayaran})"><i class="fas fa-redo"></i> Re-verifikasi</button>`;
      } else if (p.status_pembayaran === 'Disetujui') {
        aksi = `<span style="color:var(--green3);font-size:.8rem"><i class="fas fa-check-circle"></i> Terverifikasi</span>`;
      } else {
        aksi = `<span style="color:var(--text3);font-size:.8rem"><i class="fas fa-clock"></i> Menunggu bukti</span>`;
      }
      return `<tr>
        <td style="font-size:.75rem;font-weight:600">${p.kode_booking}</td>
        <td style="font-size:.875rem">${p.nama_lengkap}</td>
        <td>Kamar ${p.nomor_kamar}</td>
        <td style="font-weight:600">Rp ${Number(p.total_harga||p.nominal).toLocaleString('id-ID')}</td>
        <td style="font-size:.8rem">${p.waktu_upload?S.fmtDate(p.waktu_upload):'-'}</td>
        <td>${S.badge(p.status_pembayaran)}</td>
        <td style="white-space:nowrap">${aksi}</td>
      </tr>`;
    }).join('')}</tbody></table>`;
  }

  // Perpanjangan
  if (perpData.length) {
    html += `<div style="font-weight:700;font-size:.875rem;color:var(--green3);margin-bottom:.5rem;padding:.5rem 1.1rem;background:var(--green-xl);border-radius:var(--r) var(--r) 0 0">Perpanjangan Sewa</div>
    <table class="tbl">
    <thead><tr><th>Booking</th><th>Penyewa</th><th>Kamar</th><th>Durasi</th><th>Periode</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>${perpData.map(p => {
      let aksi = '';
      if (p.status === 'Proses Validasi') {
        aksi = `<button class="btn btn-primary btn-sm" onclick="openVerif('perpanjangan',${p.id_perpanjangan})"><i class="fas fa-shield-alt"></i> Verifikasi</button>`;
      } else if (p.status === 'Ditolak') {
        aksi = `<div style="font-size:.78rem;color:var(--red)"><i class="fas fa-times-circle"></i> Ditolak</div>
                <button class="btn btn-warning btn-sm" style="margin-top:.25rem;font-size:.72rem" onclick="openVerif('perpanjangan',${p.id_perpanjangan})"><i class="fas fa-redo"></i> Re-verifikasi</button>`;
      } else if (p.status === 'Disetujui') {
        aksi = `<span style="color:var(--green3);font-size:.8rem"><i class="fas fa-check-circle"></i> Disetujui</span>`;
      } else {
        aksi = `<span style="color:var(--text3);font-size:.8rem"><i class="fas fa-clock"></i> Menunggu</span>`;
      }
      return `<tr>
        <td style="font-size:.75rem;font-weight:600">${p.kode_booking}</td>
        <td style="font-size:.875rem">${p.nama_lengkap}</td>
        <td>Kamar ${p.nomor_kamar}</td>
        <td style="text-align:center">${p.durasi_tambah} Bln</td>
        <td style="font-size:.78rem">${S.fmtDate(p.tanggal_mulai)} — ${S.fmtDate(p.tanggal_selesai)}</td>
        <td style="font-weight:600">Rp ${Number(p.total_harga).toLocaleString('id-ID')}</td>
        <td>${S.badge(p.status)}</td>
        <td style="white-space:nowrap">${aksi}</td>
      </tr>`;
    }).join('')}</tbody></table>`;
  }

  wrap.innerHTML = html;
}

function openVerif(type, id) {
  const isPerp = type === 'perpanjangan';
  const data = isPerp
    ? verifPerpData.find(p => p.id_perpanjangan == id)
    : verifBookingData.find(p => p.id_pembayaran == id);
  if (!data) { S.toast('Data verifikasi tidak ditemukan.', 'e'); return; }
  document.getElementById('v_idPm').value   = isPerp ? 0 : data.id_pembayaran;
  document.getElementById('v_idPerp').value  = isPerp ? data.id_perpanjangan : 0;
  document.getElementById('v_catatan').value = '';
  document.getElementById('verifAlert').innerHTML = '';

  // Info block
  const kode   = data.kode_booking || '-';
  const kamar  = data.nomor_kamar || '-';
  const total  = Number(data.total_harga || data.nominal || 0).toLocaleString('id-ID');
  const penyewa = data.nama_lengkap || '-';

  document.getElementById('v_info_content').innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem">
      <div><div style="font-size:.72rem;color:var(--text3)">ID Pesanan</div><div style="font-weight:700">${kode}</div></div>
      <div><div style="font-size:.72rem;color:var(--text3)">Penyewa</div><div style="font-weight:600">${penyewa}</div></div>
      <div><div style="font-size:.72rem;color:var(--text3)">Kamar</div><div>Kamar ${kamar}</div></div>
      <div><div style="font-size:.72rem;color:var(--text3)">Total</div><div style="font-weight:700;color:var(--green3)">Rp ${total}</div></div>
      ${isPerp?`<div style="grid-column:1/-1"><div style="font-size:.72rem;color:var(--text3)">Periode Perpanjangan</div><div>${S.fmtDate(data.tanggal_mulai)} — ${S.fmtDate(data.tanggal_selesai)}</div></div>`:''}
    </div>`;

  // Bukti
  const bukti = data.bukti_pembayaran;
  if (bukti) {
    const ext = bukti.split('.').pop().toLowerCase();
    const isImg = ['jpg','jpeg','png'].includes(ext);
    document.getElementById('v_bukti').innerHTML = `
      <div style="margin-bottom:.75rem">
        <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.4rem">Bukti Pembayaran</label>
        ${isImg
          ? `<img src="${APP_URL}/public/uploads/pembayaran/${bukti}" style="max-width:100%;max-height:220px;object-fit:contain;border-radius:var(--r);border:1px solid var(--border)">`
          : `<a href="${APP_URL}/public/uploads/pembayaran/${bukti}" target="_blank" class="btn btn-outline btn-sm"><i class="fas fa-file-pdf"></i> Lihat PDF</a>`
        }
      </div>`;
  } else {
    document.getElementById('v_bukti').innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Belum ada bukti pembayaran yang diupload oleh penyewa.</div>';
  }

  // Previous rejection note
  if (data.catatan_admin) {
    document.getElementById('v_catatan').value = '';
    document.getElementById('verifAlert').innerHTML = `<div class="alert alert-warning" style="margin-bottom:.75rem"><i class="fas fa-history"></i> <strong>Catatan sebelumnya:</strong> ${data.catatan_admin}</div>`;
  }

  S.openModal('verifModal');
}

async function doVerif(aksi) {
  const idPm   = document.getElementById('v_idPm').value;
  const idPerp = document.getElementById('v_idPerp').value;
  const catatan= document.getElementById('v_catatan').value.trim();
  const al     = document.getElementById('verifAlert');
  const btnId  = aksi==='approve'?'v_approveBtn':'v_rejectBtn';
  const btn    = document.getElementById(btnId);

  if (aksi==='reject' && !catatan) {
    al.innerHTML='<div class="alert alert-error">Wajib mengisi alasan penolakan agar penyewa tahu apa yang perlu diperbaiki.</div>';
    return;
  }

  S.loading(btn, true);
  const r = await S.req('verifikasi','POST',{
    id_pembayaran: idPm||0,
    id_perpanjangan: idPerp||0,
    aksi, catatan
  });
  S.loading(btn, false);

  if (r.status==='success') {
    S.toast(r.message,'s');
    S.closeModal('verifModal');
    loadData(curTab);
  } else {
    al.innerHTML=`<div class="alert alert-error">${r.message}</div>`;
  }
}

setTab('Menunggu');
</script>
</body>
</html>
