<?php
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/middleware/auth.php';
requirePenyewa();
$pe = getPenyewa();
$pageTitle = 'Verifikasi Transaksi'; $activePage = 'beranda';
$initials = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ', $pe['nama']), 0, 2))));
?>
<?php include __DIR__ . '/../../pages/partials/header.php'; ?>

<div class="app-layout">
  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-profile">
      <div class="sidebar-avatar"><?= $initials ?></div>
      <div><div class="sidebar-name"><?= htmlspecialchars($pe['nama']) ?></div><div class="sidebar-role">Penyewa</div></div>
    </div>
    <a href="<?= APP_URL ?>/pages/user/dashboard.php" class="sidebar-link"><i class="fas fa-home"></i> Dashboard</a>
    <a href="<?= APP_URL ?>/pages/user/booking.php" class="sidebar-link"><i class="fas fa-clipboard-list"></i> Reservasi Kamar</a>
    <a href="<?= APP_URL ?>/pages/user/verifikasi.php" class="sidebar-link active"><i class="fas fa-credit-card"></i> Verifikasi Transaksi</a>
    <a href="<?= APP_URL ?>/pages/user/kalender.php" class="sidebar-link"><i class="fas fa-calendar-alt"></i> Kalender Ketersediaan</a>
    <a href="<?= APP_URL ?>/pages/review.php" class="sidebar-link"><i class="fas fa-star"></i> Review</a>
    <div class="sidebar-sep"></div>
    <a href="javascript:void(0)" class="sidebar-link" onclick="showProfil()"><i class="fas fa-user"></i> Profil</a>
    <a href="javascript:void(0)" class="sidebar-link" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Keluar</a>
  </aside>

  <main class="main-content">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.75rem">
      <div>
        <div style="margin-bottom:.75rem">
          <a href="<?= APP_URL ?>/pages/user/dashboard.php" style="display:inline-flex;align-items:center;gap:.4rem;font-size:.875rem;color:var(--text3)">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
          </a>
        </div>
        <h1 style="font-size:1.7rem;font-weight:800">Verifikasi Transaksi</h1>
        <p style="color:var(--text3);font-size:.875rem">Melihat validasi reservasi yang diajukan</p>
      </div>
      <div class="input-icon" style="max-width:300px;flex:1">
        <i class="fas fa-search i-left"></i>
        <input type="text" class="form-control" id="fSearch" placeholder="Cari id pesanan atau kamar..." oninput="debounceLoad()">
      </div>
    </div>

    <!-- 3-STEP TABS (sesuai mockup) -->
    <div style="display:flex;align-items:center;max-width:520px;margin-bottom:1.75rem">
      <!-- Step 1 -->
      <button onclick="setTab('menunggu')" id="tab_1"
        style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.3rem;background:none;border:none;cursor:pointer;padding:.5rem .25rem">
        <div id="circle_1" style="width:32px;height:32px;border-radius:50%;background:var(--card);border:2px solid var(--border);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;color:var(--text3);transition:all .2s">1</div>
        <span id="label_1" style="font-size:.72rem;font-weight:600;color:var(--text3);text-align:center;line-height:1.3">Menunggu<br>Validasi</span>
      </button>
      <div id="line_1" style="flex:1;height:2px;background:var(--border);margin-bottom:1.6rem;transition:background .2s"></div>
      <!-- Step 2 -->
      <button onclick="setTab('proses')" id="tab_2"
        style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.3rem;background:none;border:none;cursor:pointer;padding:.5rem .25rem">
        <div id="circle_2" style="width:32px;height:32px;border-radius:50%;background:var(--card);border:2px solid var(--border);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;color:var(--text3);transition:all .2s">2</div>
        <span id="label_2" style="font-size:.72rem;font-weight:600;color:var(--text3);text-align:center;line-height:1.3">Proses<br>Validasi</span>
      </button>
      <div id="line_2" style="flex:1;height:2px;background:var(--border);margin-bottom:1.6rem;transition:background .2s"></div>
      <!-- Step 3 -->
      <button onclick="setTab('disetujui')" id="tab_3"
        style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.3rem;background:none;border:none;cursor:pointer;padding:.5rem .25rem">
        <div id="circle_3" style="width:32px;height:32px;border-radius:50%;background:var(--card);border:2px solid var(--border);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;color:var(--text3);transition:all .2s">3</div>
        <span id="label_3" style="font-size:.72rem;font-weight:600;color:var(--text3)">Disetujui</span>
      </button>
    </div>

    <!-- INFO PESAN DITOLAK -->
    <div id="rejectedBanner" style="display:none;margin-bottom:1rem"></div>

    <!-- TABLE AREA -->
    <div class="tbl-wrap" id="tblWrap">
      <div style="text-align:center;padding:2.5rem"><span class="spinner"></span></div>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../pages/partials/footer.php'; ?>

<!-- ========== UPLOAD BUKTI MODAL ========== -->
<div class="modal-bg" id="uploadModal">
  <div class="modal" style="max-width:500px">
    <div class="modal-head">
      <span class="modal-title"><i class="fas fa-upload"></i> <span id="uploadModalTitle">Unggah Bukti Pembayaran</span></span>
      <button class="modal-close" onclick="S.closeModal('uploadModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div id="uploadAlert"></div>
      <input type="hidden" id="upload_booking_id">
      <input type="hidden" id="upload_perp_id">
      <input type="hidden" id="upload_type" value="booking">

      <!-- Info rekening -->
      <div style="background:var(--green-xl);border-radius:var(--r);padding:1rem;margin-bottom:1rem">
        <div style="font-weight:600;font-size:.875rem;margin-bottom:.5rem"><i class="fas fa-info-circle" style="color:var(--green)"></i> Informasi Transfer</div>
        <div style="font-size:.85rem;color:var(--text3)">
          <div style="margin-bottom:.25rem">🏦 <strong>BRI:</strong> 1234567890 a/n Aini Nurfadhilah</div>
          <div style="margin-bottom:.25rem">🏦 <strong>BNI:</strong> 1234567890 a/n Aini Nurfadhilah</div>
          <div>💚 <strong>GoPay:</strong> 1234567890</div>
        </div>
      </div>

      <!-- Alasan ditolak (jika ada) -->
      <div id="rejectedReason" style="display:none;margin-bottom:1rem"></div>

      <!-- Total -->
      <div id="uploadTotalInfo" style="background:var(--white);border:1.5px solid var(--border);border-radius:var(--r);padding:.85rem;margin-bottom:1rem;font-size:.875rem;display:none">
        <div style="display:flex;justify-content:space-between">
          <span>Total Pembayaran</span>
          <span id="uploadTotalAmt" style="font-weight:700;color:var(--green3)">Rp 0</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:.3rem">
          <span>Periode</span>
          <span id="uploadPeriode" style="font-weight:600;font-size:.8rem">-</span>
        </div>
      </div>

      <!-- Pilih metode -->
      <div class="form-group">
        <label class="form-label">Metode Pembayaran</label>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem">
          <div class="pay-opt active-pay" data-v="Transfer BRI" onclick="selectPay(this)" style="border:2px solid var(--green2);border-radius:var(--r);padding:.65rem .5rem;cursor:pointer;text-align:center;background:var(--green-xl);font-size:.8rem;font-weight:700">BRI</div>
          <div class="pay-opt" data-v="Transfer BNI" onclick="selectPay(this)" style="border:2px solid var(--border);border-radius:var(--r);padding:.65rem .5rem;cursor:pointer;text-align:center;background:var(--white);font-size:.8rem;font-weight:700">BNI</div>
          <div class="pay-opt" data-v="GoPay" onclick="selectPay(this)" style="border:2px solid var(--border);border-radius:var(--r);padding:.65rem .5rem;cursor:pointer;text-align:center;background:var(--white);font-size:.8rem;font-weight:700">GoPay</div>
        </div>
      </div>

      <!-- Upload file -->
      <div class="form-group">
        <label class="form-label">Bukti Transfer <span style="color:var(--red)">*</span></label>
        <label for="bukti_file" id="buktiLabel" style="display:flex;align-items:center;gap:.75rem;border:2px dashed var(--border);border-radius:var(--r);padding:1.1rem;cursor:pointer;background:var(--white);transition:border-color .18s">
          <i class="fas fa-upload" style="color:var(--green);font-size:1.1rem"></i>
          <span id="buktiFileName" style="font-size:.875rem;color:var(--text3)">Klik untuk pilih file (JPG, PNG, PDF — maks 5MB)</span>
        </label>
        <input type="file" id="bukti_file" style="display:none" accept="image/*,.pdf" onchange="previewFile(this)">
        <div id="buktiPreview" style="margin-top:.5rem"></div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-outline btn-sm" onclick="S.closeModal('uploadModal')">Batal</button>
      <button class="btn btn-primary btn-sm" id="uploadBtn" onclick="submitUpload()"><i class="fas fa-upload"></i> Kirim</button>
    </div>
  </div>
</div>

<!-- ========== PERPANJANGAN MODAL ========== -->
<div class="modal-bg" id="perpModal">
  <div class="modal" style="max-width:480px">
    <div class="modal-head">
      <span class="modal-title"><i class="fas fa-redo"></i> Form Perpanjangan Sewa</span>
      <button class="modal-close" onclick="S.closeModal('perpModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div id="perpAlert"></div>
      <input type="hidden" id="perp_booking_id">

      <div style="background:var(--green-xl);border-radius:var(--r);padding:1rem;margin-bottom:1rem">
        <div style="font-size:.875rem;font-weight:600;margin-bottom:.25rem">Info Perpanjangan</div>
        <div id="perpCurrentInfo" style="font-size:.85rem;color:var(--text3)">-</div>
      </div>

      <div class="form-group">
        <label class="form-label">Durasi Perpanjangan</label>
        <select id="perp_durasi" class="form-control" onchange="updatePerpTotal()">
          <?php for($i=1;$i<=12;$i++): ?>
          <option value="<?=$i?>"><?=$i?> Bulan</option>
          <?php endfor; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Metode Pembayaran</label>
        <select id="perp_metode" class="form-control">
          <option value="Transfer BRI">Transfer BRI</option>
          <option value="Transfer BNI">Transfer BNI</option>
          <option value="GoPay">GoPay</option>
        </select>
      </div>

      <div style="background:var(--white);border:1.5px solid var(--border);border-radius:var(--r);padding:1rem;font-size:.875rem">
        <div style="font-weight:600;margin-bottom:.5rem">Ringkasan</div>
        <div style="display:flex;justify-content:space-between;margin-bottom:.3rem"><span>Kamar</span><span id="perp_kamar_lbl">-</span></div>
        <div style="display:flex;justify-content:space-between;margin-bottom:.3rem"><span>Mulai dari</span><span id="perp_mulai_lbl">-</span></div>
        <div style="display:flex;justify-content:space-between;margin-bottom:.3rem"><span>Sampai</span><span id="perp_selesai_lbl">-</span></div>
        <div style="display:flex;justify-content:space-between;border-top:1px solid var(--border);padding-top:.5rem;margin-top:.5rem">
          <strong>Total</strong><strong id="perp_total_lbl" style="color:var(--green3)">Rp 0</strong>
        </div>
      </div>

      <div class="alert alert-info" style="margin-top:1rem;font-size:.85rem">
        <i class="fas fa-info-circle"></i> Setelah mengajukan perpanjangan, silakan upload bukti pembayaran untuk diverifikasi admin.
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-outline btn-sm" onclick="S.closeModal('perpModal')">Batal</button>
      <button class="btn btn-primary btn-sm" id="perpBtn" onclick="submitPerp()"><i class="fas fa-paper-plane"></i> Ajukan Perpanjangan</button>
    </div>
  </div>
</div>

<!-- PROFIL MODAL -->
<div class="modal-bg" id="profilModal">
  <div class="modal" style="max-width:500px">
    <div class="modal-head"><span class="modal-title"><i class="fas fa-user"></i> Profil User</span><button class="modal-close" onclick="S.closeModal('profilModal')"><i class="fas fa-times"></i></button></div>
    <div class="modal-body">
      <div style="background:var(--green-xl);border-radius:var(--r);padding:1rem;display:flex;align-items:center;gap:.85rem;margin-bottom:1.25rem">
        <div style="width:52px;height:52px;border-radius:50%;background:var(--green2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.3rem"><i class="fas fa-user"></i></div>
        <div><div style="font-weight:700"><?= htmlspecialchars($pe['nama']) ?></div><div style="font-size:.8rem;color:var(--text3)">Penghuni</div><span class="badge b-green">User</span></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div><div style="font-weight:600;font-size:.875rem">Username</div><div style="font-size:.875rem;color:var(--text3)"><?= htmlspecialchars($pe['username']) ?></div></div>
        <div><div style="font-weight:600;font-size:.875rem">Email</div><div style="font-size:.875rem;color:var(--text3)"><?= htmlspecialchars($pe['email'] ?: '-') ?></div></div>
        <div style="grid-column:1/-1"><div style="font-weight:600;font-size:.875rem">No. Hp</div><div style="font-size:.875rem;color:var(--text3)"><?= htmlspecialchars($pe['no_hp'] ?: '-') ?></div></div>
      </div>
    </div>
    <div class="modal-foot"><button class="btn btn-outline btn-sm" onclick="S.closeModal('profilModal')">Tutup</button></div>
  </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
let currentTab = 'menunggu';
let allBookings = [];
let perpData = {};   // {id_booking: {kamar, harga, tgl_selesai, nomor_kamar}}
let debTimer;

function showProfil() { S.openModal('profilModal'); }
function doLogout() { confirmLogout(); }
function debounceLoad() { clearTimeout(debTimer); debTimer=setTimeout(renderTable, 350); }

// ====== TAB NAVIGATION ======
function setTab(tab) {
  currentTab = tab;
  const tabMap = { menunggu:1, proses:2, disetujui:3 };
  const n = tabMap[tab];

  [1,2,3].forEach(i => {
    const circ = document.getElementById('circle_'+i);
    const lbl  = document.getElementById('label_'+i);
    const isActive = i === n;
    circ.style.background = isActive ? 'var(--green2)' : 'var(--card)';
    circ.style.borderColor = isActive ? 'var(--green2)' : 'var(--border)';
    circ.style.color = isActive ? '#fff' : 'var(--text3)';
    lbl.style.color = isActive ? 'var(--green3)' : 'var(--text3)';
  });
  // Activate lines up to current
  [1,2].forEach(i => {
    document.getElementById('line_'+i).style.background = i < n ? 'var(--green2)' : 'var(--border)';
  });

  renderTable();
}

// ====== LOAD ALL DATA ======
async function loadAll() {
  const r = await S.req('get_my_bookings','GET',{});
  const rP = await S.req('get_perpanjangan','GET',{});
  allBookings = r.status==='success' ? r.data : [];
  const perpList = rP.status==='success' ? rP.data : [];

  // Build perp map by booking id
  window.perpByBooking = {};
  perpList.forEach(p => {
    if (!window.perpByBooking[p.id_booking]) window.perpByBooking[p.id_booking] = [];
    window.perpByBooking[p.id_booking].push(p);
  });

  // Cache booking info for perpanjangan
  allBookings.forEach(b => {
    perpData[b.id_booking] = {
      nomor_kamar: b.nomor_kamar, tipe: b.tipe,
      harga: parseFloat(b.harga_per_bulan), tgl_selesai: b.tanggal_selesai,
      kode: b.kode_booking
    };
  });

  // Check for rejected payments — show banner
  const rejected = allBookings.filter(b => b.status_pembayaran === 'Ditolak');
  const banner = document.getElementById('rejectedBanner');
  if (rejected.length) {
    banner.style.display = 'block';
    banner.innerHTML = `<div class="alert alert-error" style="display:flex;align-items:flex-start;gap:.75rem">
      <i class="fas fa-exclamation-triangle" style="font-size:1.1rem;flex-shrink:0;margin-top:.1rem"></i>
      <div>
        <strong>Pembayaran Ditolak!</strong>
        <div style="font-size:.85rem;margin-top:.25rem">
          ${rejected.map(b=>`Booking <strong>${b.kode_booking}</strong> — Kamar ${b.nomor_kamar} pembayarannya ditolak. Silakan upload ulang bukti yang benar.`).join('<br>')}
        </div>
      </div>
    </div>`;
  } else {
    banner.style.display = 'none';
  }

  renderTable();
}

// ====== RENDER TABLE ======
function renderTable() {
  const q = document.getElementById('fSearch').value.toLowerCase();
  const wrap = document.getElementById('tblWrap');

  // Filter booking berdasarkan tab
  let filtered = allBookings.filter(b => {
    const matchQ = !q ||
      b.kode_booking.toLowerCase().includes(q) ||
      (b.nomor_kamar||'').toLowerCase().includes(q);

    if (currentTab === 'menunggu') {
      // Step 1: belum ada bukti ATAU status_pembayaran Menunggu ATAU Ditolak (perlu upload ulang)
      return matchQ && (
        !b.bukti_pembayaran ||
        b.status_pembayaran === 'Menunggu' ||
        b.status_pembayaran === 'Ditolak'
      );
    }
    if (currentTab === 'proses') {
      return matchQ && b.status_pembayaran === 'Proses Validasi';
    }
    if (currentTab === 'disetujui') {
      return matchQ && (b.status_pembayaran === 'Disetujui' || b.status === 'Aktif' || b.status === 'Selesai');
    }
    return false;
  });

  if (!filtered.length) {
    const msgs = { menunggu:'Tidak ada transaksi yang menunggu validasi.', proses:'Tidak ada transaksi dalam proses validasi.', disetujui:'Belum ada transaksi yang disetujui.' };
    wrap.innerHTML = `<div style="text-align:center;padding:3rem;color:#fffff"><i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:.5rem"></i>${msgs[currentTab]}</div>`;

    // Perpanjangan di step disetujui
    if (currentTab === 'disetujui') renderPerpTable(wrap, q);
    return;
  }

  let rows = filtered.map(b => {
    let aksiHtml = '';

    if (currentTab === 'menunggu') {
      if (b.status_pembayaran === 'Ditolak') {
        // Pembayaran ditolak — tampilkan upload ulang + alasan
        aksiHtml = `<button class="btn btn-warning btn-sm" onclick="openUploadUlang(${b.id_booking},${b.id_pembayaran||0})">
          <i class="fas fa-redo"></i> Upload Ulang
        </button>`;
      } else {
        // Belum upload
        aksiHtml = `<button class="btn btn-primary btn-sm" onclick="openUpload(${b.id_booking})">
          <i class="fas fa-upload"></i> Upload Bukti
        </button>`;
      }
    } else if (currentTab === 'proses') {
      aksiHtml = `<span style="color:var(--text3);font-size:.8rem;display:flex;align-items:center;gap:.35rem"><i class="fas fa-clock"></i> Menunggu Admin</span>`;
    } else if (currentTab === 'disetujui') {
      aksiHtml = `<button class="btn btn-outline btn-sm" onclick="markSelesai(${b.id_booking})"><i class="fas fa-check"></i> Selesai</button>`;
    }

    // Status badge khusus
    let statusDisplay = b.status_pembayaran || 'Menunggu';
    if (b.status_pembayaran === 'Ditolak') {
      statusDisplay = `<span class="badge b-red" style="cursor:pointer" onclick="showAlasan(${b.id_booking})" title="Klik lihat alasan">Ditolak <i class="fas fa-info-circle"></i></span>`;
    } else {
      statusDisplay = S.badge(statusDisplay);
    }

    return `<tr>
      <td style="font-size:.78rem;font-weight:600">${b.kode_booking}</td>
      <td>Kamar ${b.nomor_kamar} <span style="color:var(--text3);font-size:.8rem">${b.tipe}</span></td>
      <td>${S.fmtDate(b.tanggal_pemesanan)}</td>
      <td>${statusDisplay}</td>
      <td style="white-space:nowrap">${aksiHtml}</td>
    </tr>`;
  }).join('');

  wrap.innerHTML = `<table class="tbl">
    <thead><tr><th>ID Pesanan</th><th>Kamar</th><th>Tanggal Booking</th><th>Status Validasi</th><th>Aksi</th></tr></thead>
    <tbody>${rows}</tbody>
  </table>`;

  // Tabel perpanjangan di step 3
  if (currentTab === 'disetujui') renderPerpTable(wrap, q);
}

// ====== PERPANJANGAN TABLE (step 3) ======
function renderPerpTable(wrap, q) {
  // Booking aktif yang bisa diperpanjang
  const aktifBookings = allBookings.filter(b =>
    (b.status_pembayaran === 'Disetujui' || b.status === 'Aktif') &&
    (!q || b.kode_booking.toLowerCase().includes(q) || (b.nomor_kamar||'').toLowerCase().includes(q))
  );

  // Riwayat perpanjangan
  const allPerp = [];
  Object.values(window.perpByBooking || {}).forEach(arr => allPerp.push(...arr));
  const filteredPerp = allPerp.filter(p => !q || p.kode_booking.toLowerCase().includes(q) || (p.nomor_kamar||'').toLowerCase().includes(q));

  if (!aktifBookings.length && !filteredPerp.length) return;

  let perpSection = `<div style="margin-top:1.5rem">
    <h3 style="font-size:1rem;font-weight:700;color:var(--green3);margin-bottom:.85rem">
      <i class="fas fa-redo" style="margin-right:.4rem"></i> Perpanjangan Sewa
    </h3>`;

  // Tombol perpanjang untuk booking aktif
  if (aktifBookings.length) {
    perpSection += `<div style="margin-bottom:1rem;display:flex;gap:.75rem;flex-wrap:wrap">
      ${aktifBookings.map(b => {
        const hasPending = (window.perpByBooking[b.id_booking]||[]).some(p=>['Menunggu','Proses Validasi'].includes(p.status));
        return `<button class="btn btn-primary btn-sm" ${hasPending?'disabled title="Ada perpanjangan pending"':''} onclick="openPerp(${b.id_booking})">
          <i class="fas fa-plus"></i> Perpanjang Kamar ${b.nomor_kamar}
        </button>`;
      }).join('')}
    </div>`;
  }

  // Tabel riwayat perpanjangan
  if (filteredPerp.length) {
    perpSection += `<table class="tbl">
      <thead><tr><th>Booking</th><th>Kamar</th><th>Durasi</th><th>Periode</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>${filteredPerp.map(p => {
        let aksi = '';
        if (p.status === 'Menunggu') {
          aksi = `<button class=\"btn btn-primary btn-sm\" onclick=\"openUploadPerp(${p.id_booking},${p.id_perpanjangan},${p.total_harga},'${p.tanggal_mulai}','${p.tanggal_selesai}')\">\n            <i class=\"fas fa-upload\"></i> Upload Bukti\n          </button>`;
        } else if (p.status === 'Ditolak') {
          aksi = `<button class=\"btn btn-warning btn-sm\" onclick=\"openUploadPerp(${p.id_booking},${p.id_perpanjangan},${p.total_harga},'${p.tanggal_mulai}','${p.tanggal_selesai}',true)\">\n            <i class=\"fas fa-redo\"></i> Upload Ulang\n          </button>`;
        } else if (p.status === 'Proses Validasi') {
          aksi = `<span style=\"font-size:.8rem;color:var(--text3)\"><i class=\"fas fa-clock\"></i> Menunggu</span>`;
        } else {
          aksi = `<span style=\"font-size:.8rem;color:var(--green3)\"><i class=\"fas fa-check-circle\"></i> Selesai</span>`;
        }
        return `<tr>\n          <td style=\"font-size:.78rem;font-weight:600\">${p.kode_booking}</td>\n          <td>Kamar ${p.nomor_kamar}</td>\n          <td>${p.durasi_tambah} Bulan</td>\n          <td style=\"font-size:.8rem\">${S.fmtDate(p.tanggal_mulai)} — ${S.fmtDate(p.tanggal_selesai)}</td>\n          <td style=\"font-weight:600;color:var(--green3)\">Rp ${Number(p.total_harga).toLocaleString('id-ID')}</td>\n          <td>${S.badge(p.status)}</td>\n          <td>${aksi}</td>\n        </tr>`;
      }).join('')}
      </tbody></table>`;
  }

  perpSection += '</div>';
  wrap.insertAdjacentHTML('beforeend', perpSection);
}

// ====== OPEN UPLOAD ======
function openUpload(idBooking) {
  document.getElementById('upload_booking_id').value = idBooking;
  document.getElementById('upload_perp_id').value = '';
  document.getElementById('upload_type').value = 'booking';
  document.getElementById('uploadModalTitle').textContent = 'Unggah Bukti Pembayaran';
  document.getElementById('rejectedReason').style.display = 'none';
  document.getElementById('uploadTotalInfo').style.display = 'none';
  resetUploadForm();
  S.openModal('uploadModal');
}

function openUploadUlang(idBooking, idPm) {
  // Tampilkan alasan penolakan
  const b = allBookings.find(x => x.id_booking == idBooking);
  document.getElementById('upload_booking_id').value = idBooking;
  document.getElementById('upload_perp_id').value = '';
  document.getElementById('upload_type').value = 'booking';
  document.getElementById('uploadModalTitle').textContent = 'Upload Ulang Bukti Pembayaran';
  const reason = b?.catatan_admin || 'Bukti tidak sesuai. Silakan upload ulang.';
  document.getElementById('rejectedReason').style.display = 'block';
  document.getElementById('rejectedReason').innerHTML = `<div class="alert alert-error"><i class="fas fa-times-circle"></i> <strong>Alasan Penolakan:</strong> ${reason}</div>`;
  document.getElementById('uploadTotalInfo').style.display = 'block';
  document.getElementById('uploadTotalAmt').textContent = 'Rp ' + Number(b?.total_harga||0).toLocaleString('id-ID');
  document.getElementById('uploadPeriode').textContent = S.fmtDate(b?.tanggal_mulai) + ' — ' + S.fmtDate(b?.tanggal_selesai);
  resetUploadForm();
  S.openModal('uploadModal');
}

function openUploadPerp(idBooking, idPerp, total, tglMulai, tglSelesai, isRedo=false) {
  document.getElementById('upload_booking_id').value = idBooking;
  document.getElementById('upload_perp_id').value = idPerp;
  document.getElementById('upload_type').value = 'perpanjangan';
  document.getElementById('uploadModalTitle').textContent = isRedo ? 'Upload Ulang Bukti Perpanjangan' : 'Unggah Bukti Perpanjangan';
  document.getElementById('rejectedReason').style.display = 'none';
  document.getElementById('uploadTotalInfo').style.display = 'block';
  document.getElementById('uploadTotalAmt').textContent = 'Rp ' + Number(total).toLocaleString('id-ID');
  document.getElementById('uploadPeriode').textContent = S.fmtDate(tglMulai) + ' — ' + S.fmtDate(tglSelesai);
  resetUploadForm();
  S.openModal('uploadModal');
}

function resetUploadForm() {
  document.getElementById('buktiFileName').textContent = 'Klik untuk pilih file (JPG, PNG, PDF — maks 5MB)';
  document.getElementById('buktiPreview').innerHTML = '';
  document.getElementById('uploadAlert').innerHTML = '';
  document.getElementById('bukti_file').value = '';
  document.querySelectorAll('.pay-opt').forEach((el,i) => {
    el.style.borderColor = i===0?'var(--green2)':'var(--border)';
    el.style.background = i===0?'var(--green-xl)':'var(--white)';
    if(i===0) el.classList.add('active-pay'); else el.classList.remove('active-pay');
  });
}

function selectPay(el) {
  document.querySelectorAll('.pay-opt').forEach(e => {
    e.style.borderColor='var(--border)'; e.style.background='var(--white)';
  });
  el.style.borderColor='var(--green2)'; el.style.background='var(--green-xl)';
}

function previewFile(input) {
  const f = input.files[0]; if(!f) return;
  document.getElementById('buktiFileName').textContent = f.name;
  if (f.type.startsWith('image/')) {
    const r = new FileReader();
    r.onload = e => document.getElementById('buktiPreview').innerHTML=`<img src="${e.target.result}" style="max-width:100%;max-height:160px;border-radius:var(--r);margin-top:.4rem">`;
    r.readAsDataURL(f);
  } else {
    document.getElementById('buktiPreview').innerHTML=`<div style="font-size:.8rem;color:var(--text3);margin-top:.4rem"><i class="fas fa-file-pdf" style="color:var(--red)"></i> ${f.name}</div>`;
  }
}

async function submitUpload() {
  const file = document.getElementById('bukti_file').files[0];
  const al = document.getElementById('uploadAlert');
  if (!file) { al.innerHTML='<div class="alert alert-error">Pilih file bukti terlebih dahulu.</div>'; return; }
  const idBooking = document.getElementById('upload_booking_id').value;
  const idPerp    = document.getElementById('upload_perp_id').value;
  const type      = document.getElementById('upload_type').value;
  const btn = document.getElementById('uploadBtn');
  const fd = new FormData();
  fd.append('id_booking', idBooking);
  fd.append('type', type);
  if (idPerp) fd.append('id_perpanjangan', idPerp);
  fd.append('bukti', file);
  S.loading(btn, true);
  const r = await S.req('upload_bukti','POST', fd);
  S.loading(btn, false);
  if (r.status === 'success') {
    S.toast(r.message, 's');
    S.closeModal('uploadModal');
    await loadAll();
    setTab('proses');
  } else {
    al.innerHTML=`<div class="alert alert-error">${r.message}</div>`;
  }
}

// ====== SHOW ALASAN PENOLAKAN ======
function showAlasan(idBooking) {
  const b = allBookings.find(x => x.id_booking == idBooking);
  const alasan = b?.catatan_admin || 'Tidak ada keterangan dari admin.';
  const ex = document.getElementById('_alasanModal'); if(ex) ex.remove();
  document.body.insertAdjacentHTML('beforeend', `
    <div class="modal-bg open" id="_alasanModal">
      <div class="modal" style="max-width:420px">
        <div class="modal-head" style="background:#c0392b"><span class="modal-title"><i class="fas fa-times-circle"></i> Pembayaran Ditolak</span><button class="modal-close" onclick="document.getElementById('_alasanModal').remove()"><i class="fas fa-times"></i></button></div>
        <div class="modal-body">
          <div class="alert alert-error" style="margin-bottom:1rem"><strong>Alasan Penolakan:</strong><br>${alasan}</div>
          <p style="font-size:.875rem;color:var(--text3)">Silakan perbaiki bukti pembayaran Anda dan upload ulang melalui tab <strong>Menunggu Validasi</strong>. Booking Anda tetap tersimpan dan tidak dihapus.</p>
          <div style="margin-top:.75rem;font-size:.85rem;background:var(--green-xl);border-radius:var(--r);padding:.85rem">
            <strong>Booking:</strong> ${b?.kode_booking}<br>
            <strong>Kamar:</strong> Kamar ${b?.nomor_kamar}<br>
            <strong>Total:</strong> Rp ${Number(b?.total_harga||0).toLocaleString('id-ID')}
          </div>
        </div>
        <div class="modal-foot">
          <button class="btn btn-outline btn-sm" onclick="document.getElementById('_alasanModal').remove()">Tutup</button>
          <button class="btn btn-warning btn-sm" onclick="document.getElementById('_alasanModal').remove();openUploadUlang(${idBooking},0)"><i class="fas fa-redo"></i> Upload Ulang</button>
        </div>
      </div>
    </div>`);
}

// ====== PERPANJANGAN ======
function openPerp(idBooking) {
  const info = perpData[idBooking];
  if (!info) { S.toast('Data booking tidak ditemukan','e'); return; }
  document.getElementById('perp_booking_id').value = idBooking;
  document.getElementById('perp_kamar_lbl').textContent = `Kamar ${info.nomor_kamar} (${info.tipe})`;
  document.getElementById('perpCurrentInfo').textContent = `Sewa saat ini berakhir: ${S.fmtDateLong(info.tgl_selesai)}`;
  window._perpInfo = info;
  document.getElementById('perp_durasi').value = '1';
  document.getElementById('perpAlert').innerHTML = '';
  updatePerpTotal();
  S.openModal('perpModal');
}

function updatePerpTotal() {
  const info = window._perpInfo; if(!info) return;
  const dur = parseInt(document.getElementById('perp_durasi').value);
  const total = info.harga * dur;
  // Hitung tanggal dari tgl_selesai booking saat ini
  const mulai = new Date(info.tgl_selesai);
  const selesai = new Date(info.tgl_selesai);
  selesai.setMonth(selesai.getMonth() + dur);
  document.getElementById('perp_mulai_lbl').textContent = mulai.toLocaleDateString('id-ID',{day:'2-digit',month:'long',year:'numeric'});
  document.getElementById('perp_selesai_lbl').textContent = selesai.toLocaleDateString('id-ID',{day:'2-digit',month:'long',year:'numeric'});
  document.getElementById('perp_total_lbl').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

async function submitPerp() {
  const btn = document.getElementById('perpBtn');
  const al  = document.getElementById('perpAlert');
  const idBooking = document.getElementById('perp_booking_id').value;
  const dur = document.getElementById('perp_durasi').value;
  const metode = document.getElementById('perp_metode').value;
  S.loading(btn, true);
  const r = await S.req('create_perpanjangan','POST',{id_booking:idBooking,durasi_tambah:dur,metode_pembayaran:metode});
  S.loading(btn, false);
  if (r.status === 'success') {
    S.toast(r.message,'s');
    S.closeModal('perpModal');
    await loadAll();
    // Langsung buka upload bukti untuk perpanjangan yang baru dibuat
    setTimeout(() => {
      openUploadPerp(idBooking, r.id_perpanjangan, r.total, window._perpInfo?.tgl_selesai, '');
    }, 400);
  } else {
    al.innerHTML=`<div class="alert alert-error">${r.message}</div>`;
  }
}

// ====== SELESAI ======
async function markSelesai(id) {
  S.confirm('Tandai booking ini sebagai selesai?', async ok => {
    if (!ok) return;
    const r = await S.req('update_booking','POST',{id_booking:id,status:'Selesai'});
    if (r.status==='success') { S.toast(r.message,'s'); loadAll(); }
    else S.toast(r.message,'e');
  });
}

// Init
loadAll().then(() => setTab('menunggu'));
</script>
</body>
</html>
