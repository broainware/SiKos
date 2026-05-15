<?php
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/middleware/auth.php';
requirePenyewa();
$pe = getPenyewa();
$pageTitle = 'Reservasi Kamar'; $activePage = 'beranda';
$initials = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ', $pe['nama']), 0, 2))));
$preselKamar = (int)($_GET['kamar_id'] ?? 0);

// All kamar tersedia
$kamarAll = DB::q("SELECT k.*,GROUP_CONCAT(f.nama_fasilitas ORDER BY f.nama_fasilitas SEPARATOR '||') fas FROM kamar k LEFT JOIN kamar_fasilitas kf ON k.id_kamar=kf.id_kamar LEFT JOIN fasilitas f ON kf.id_fasilitas=f.id_fasilitas WHERE k.status_ketersediaan='Tersedia' GROUP BY k.id_kamar ORDER BY k.tipe,k.harga_per_bulan")->fetch_all(MYSQLI_ASSOC);

// Group by type for step 2
$kamarByTipe = [];
foreach ($kamarAll as $k) {
    $kamarByTipe[$k['tipe']][] = $k;
}
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
    <a href="<?= APP_URL ?>/pages/user/booking.php" class="sidebar-link active"><i class="fas fa-clipboard-list"></i> Reservasi Kamar</a>
    <a href="<?= APP_URL ?>/pages/user/verifikasi.php" class="sidebar-link"><i class="fas fa-credit-card"></i> Verifikasi Transaksi</a>
    <a href="<?= APP_URL ?>/pages/user/kalender.php" class="sidebar-link"><i class="fas fa-calendar-alt"></i> Kalender Ketersediaan</a>
    <a href="<?= APP_URL ?>/pages/review.php" class="sidebar-link"><i class="fas fa-star"></i> Review</a>
    <div class="sidebar-sep"></div>
    <a href="javascript:void(0)" class="sidebar-link" onclick="showProfil()"><i class="fas fa-user"></i> Profil</a>
    <a href="javascript:void(0)" class="sidebar-link" onclick="confirmLogout()"><i class="fas fa-sign-out-alt"></i> Keluar</a>
  </aside>

  <main class="main-content">
    <div style="margin-bottom:1.5rem">
      <a href="<?= APP_URL ?>/pages/user/dashboard.php" style="display:inline-flex;align-items:center;gap:.4rem;font-size:.875rem;color:var(--text3);font-weight:500">
        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
      </a>
    </div>

    <h1 style="font-size:1.7rem;font-weight:800;margin-bottom:.25rem">Form Pemesanan Kamar</h1>
    <p style="color:var(--text3);font-size:.875rem;margin-bottom:1rem">Isi data lengkap untuk melanjutkan pemesanan kamar.</p>
    <div id="bookingBlockedAlert" class="alert alert-warning" style="display:none;margin-bottom:1.25rem">
      <i class="fas fa-exclamation-circle"></i> <strong>Anda sudah memiliki booking sebelumnya.</strong> Formulir pemesanan tidak dapat diisi ulang saat booking masih tercatat.
    </div>

    <!-- STEP INDICATOR -->
    <div class="steps" id="stepsRow">
      <div class="step active" id="st1"><div class="step-num">1</div><div class="step-lbl">Data Diri</div></div>
      <div class="step" id="st2"><div class="step-num">2</div><div class="step-lbl">Pilih Kamar</div></div>
      <div class="step" id="st3"><div class="step-num">3</div><div class="step-lbl">Kalender</div></div>
      <div class="step" id="st4"><div class="step-num">4</div><div class="step-lbl">Konfirmasi Pembayaran</div></div>
    </div>

    <!-- ==================== STEP 1: DATA DIRI ==================== -->
    <div id="step1" class="card" style="padding:1.75rem">
      <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:1.5rem">
        <i class="fas fa-user" style="color:var(--green);font-size:1.1rem"></i>
        <div><div style="font-weight:700;font-size:1rem">Data Diri</div><div style="font-size:.8rem;color:var(--text3)">Isi data diri langsung dibawah ini</div></div>
      </div>
      <div id="step1Alert"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group">
          <label class="form-label">Nama Lengkap <span style="color:var(--red)">*</span></label>
          <input type="text" id="s1_nama" class="form-control" placeholder="Nama Lengkap sesuai KTP" value="<?= htmlspecialchars($pe['nama']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Nomor WhatsApp <span style="color:var(--red)">*</span></label>
          <input type="text" id="s1_hp" class="form-control" placeholder="+62..." value="<?= htmlspecialchars($pe['no_hp'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" id="s1_email" class="form-control" placeholder="example@gmail.com" value="<?= htmlspecialchars($pe['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Pekerjaan/Status</label>
          <select id="s1_pkj" class="form-control">
            <option value="">Pilih Status</option>
            <option value="Mahasiswa">Mahasiswa</option>
            <option value="Karyawan">Karyawan</option>
            <option value="Wiraswasta">Wiraswasta</option>
            <option value="Lainnya">Lainnya</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Alamat Asal</label>
        <textarea id="s1_alamat" class="form-control" rows="3" placeholder="Alamat Lengkap"></textarea>
      </div>
      <div style="text-align:right;margin-top:.5rem">
        <button class="btn btn-primary" onclick="goStep(2)">Next <i class="fas fa-chevron-right"></i></button>
      </div>
    </div>

    <!-- ==================== STEP 2: PILIH KAMAR ==================== -->
    <div id="step2" class="card" style="padding:1.75rem;display:none">
      <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:1.5rem">
        <i class="fas fa-bed" style="color:var(--green);font-size:1.1rem"></i>
        <div><div style="font-weight:700;font-size:1rem">Pilih Kamar</div><div style="font-size:.8rem;color:var(--text3)">Pilih kamar yang tersedia sesuai kebutuhan Anda</div></div>
      </div>
      <div id="step2Alert"></div>

      <!-- TIPE SELECTOR -->
      <div class="form-group">
        <label class="form-label">Tipe Kamar</label>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap" id="tipeCards">
          <?php foreach ($kamarByTipe as $tipe => $list):
            $minH = min(array_column($list, 'harga_per_bulan'));
            $hasAvail = count($list) > 0;
          ?>
          <div class="tipe-card" data-tipe="<?= htmlspecialchars($tipe) ?>" onclick="selectTipe('<?= htmlspecialchars($tipe) ?>')"
               style="flex:1;min-width:160px;max-width:200px;border:2px solid var(--border);border-radius:var(--r2);padding:1rem;cursor:pointer;text-align:center;transition:all .18s;background:var(--white)">
            <div style="font-weight:700;font-size:.95rem">Kamar <?= htmlspecialchars($tipe) ?></div>
            <div style="color:var(--green3);font-size:.85rem;font-weight:600;margin:.25rem 0">Rp <?= number_format($minH,0,',','.') ?>/bulan</div>
            <div style="font-size:.75rem;color:var(--green3)"><i class="fas fa-circle" style="font-size:.5rem"></i> Tersedia</div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- NOMOR KAMAR DROPDOWN -->
      <div class="form-group">
        <label class="form-label">Nomor Kamar</label>
        <select id="s2_kamar" class="form-control" onchange="updateRingkasan()">
          <option value="">Pilih nomor kamar...</option>
          <?php foreach ($kamarAll as $k): ?>
          <option value="<?= $k['id_kamar'] ?>" data-harga="<?= $k['harga_per_bulan'] ?>" data-tipe="<?= htmlspecialchars($k['tipe']) ?>" data-nomor="<?= htmlspecialchars($k['nomor_kamar']) ?>">
            Kamar <?= htmlspecialchars($k['nomor_kamar']) ?> — Rp <?= number_format($k['harga_per_bulan'],0,',','.') ?>/bln
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- RINGKASAN BIAYA -->
      <div style="background:var(--green-xl);border-radius:var(--r);padding:1rem;margin-bottom:1rem">
        <div style="font-weight:600;font-size:.875rem;margin-bottom:.5rem">Ringkasan Biaya</div>
        <div style="display:flex;justify-content:space-between;font-size:.9rem">
          <span>Total</span>
          <span id="s2_total" style="font-weight:700;color:var(--green3)">Rp 0</span>
        </div>
      </div>

      <div style="display:flex;justify-content:space-between;margin-top:.5rem">
        <button class="btn btn-outline" onclick="goStep(1)"><i class="fas fa-chevron-left"></i> Back</button>
        <button class="btn btn-primary" onclick="goStep(3)">Next <i class="fas fa-chevron-right"></i></button>
      </div>
    </div>

    <!-- ==================== STEP 3: KALENDER ==================== -->
    <div id="step3" class="card" style="padding:1.75rem;display:none">
      <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:1.5rem">
        <i class="fas fa-calendar-alt" style="color:var(--green);font-size:1.1rem"></i>
        <div><div style="font-weight:700;font-size:1rem">Kalender Ketersediaan</div><div style="font-size:.8rem;color:var(--text3)">Periksa jadwal kamar pilihan Anda secara real-time</div></div>
      </div>
      <div id="step3Alert"></div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
        <!-- Calendar -->
        <div id="calContainer"></div>

        <!-- Right panel -->
        <div>
          <div style="margin-bottom:1rem">
            <label class="form-label">Pilih Tanggal Sewa</label>
            <div style="display:flex;align-items:center;gap:.5rem;background:var(--green-xl);border-radius:var(--r);padding:.75rem;font-size:.875rem">
              <i class="fas fa-calendar" style="color:var(--green)"></i>
              <span id="s3_tglDisplay">Pilih dari kalender</span>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Durasi Sewa</label>
            <select id="s3_durasi" class="form-control" onchange="updateStep3Summary()">
              <?php for ($i=1;$i<=12;$i++): ?>
              <option value="<?= $i ?>"><?= $i ?> bulan</option>
              <?php endfor; ?>
            </select>
          </div>

          <!-- Pesanan Saya -->
          <div id="myBookingInfo" style="background:var(--green-xl);border-radius:var(--r);padding:.9rem;margin-bottom:1rem;font-size:.85rem;display:none">
            <div style="font-weight:600;margin-bottom:.35rem">Pemesanan Saya</div>
            <div id="myBookingDetail"></div>
          </div>

          <!-- Ringkasan -->
          <div style="background:var(--white);border:1.5px solid var(--border);border-radius:var(--r);padding:1rem;font-size:.875rem">
            <div style="font-weight:600;margin-bottom:.6rem">Ringkasan Pemesanan</div>
            <div style="display:flex;justify-content:space-between;margin-bottom:.35rem"><span>Kamar</span><span id="s3_kamarLabel" style="font-weight:600">-</span></div>
            <div style="display:flex;justify-content:space-between;margin-bottom:.35rem"><span>Durasi</span><span id="s3_durasiLabel">1 Bulan</span></div>
            <div style="display:flex;justify-content:space-between;border-top:1px solid var(--border);padding-top:.5rem;margin-top:.5rem"><span style="font-weight:600">Total</span><span id="s3_totalLabel" style="font-weight:700;color:var(--green3)">Rp 0</span></div>
          </div>
          <button class="btn btn-primary btn-full" style="margin-top:.75rem" onclick="goStep3Pay()">Bayar</button>
        </div>
      </div>

      <div style="display:flex;justify-content:space-between;margin-top:1rem">
        <button class="btn btn-outline" onclick="goStep(2)"><i class="fas fa-chevron-left"></i> Back</button>
        <button class="btn btn-primary" onclick="goStep(4)">Next <i class="fas fa-chevron-right"></i></button>
      </div>
    </div>

    <!-- ==================== STEP 4: KONFIRMASI PEMBAYARAN ==================== -->
    <div id="step4" class="card" style="padding:1.75rem;display:none">
      <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:1.5rem">
        <i class="fas fa-credit-card" style="color:var(--green);font-size:1.1rem"></i>
        <div><div style="font-weight:700;font-size:1rem">Konfirmasi Pembayaran</div><div style="font-size:.8rem;color:var(--text3)">Periksa jadwal kamar pilihan Anda secara real-time</div></div>
      </div>
      <div id="step4Alert"></div>

      <!-- PAYMENT METHODS -->
      <div class="form-group">
        <label class="form-label">Transaksi Pembayaran</label>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem">
          <div class="pay-method active" data-metode="Transfer BRI" onclick="selectMetode(this)" style="border:2px solid var(--green2);border-radius:var(--r);padding:.85rem;cursor:pointer;transition:all .18s;background:var(--white)">
            <div style="font-size:.8rem;font-weight:700">TF Bank BRI</div>
            <div style="font-size:.75rem;color:var(--text3);margin-top:.2rem">1234567890</div>
          </div>
          <div class="pay-method" data-metode="Transfer BNI" onclick="selectMetode(this)" style="border:2px solid var(--border);border-radius:var(--r);padding:.85rem;cursor:pointer;transition:all .18s;background:var(--white)">
            <div style="font-size:.8rem;font-weight:700">TF Bank BNI</div>
            <div style="font-size:.75rem;color:var(--text3);margin-top:.2rem">1234567890</div>
          </div>
          <div class="pay-method" data-metode="GoPay" onclick="selectMetode(this)" style="border:2px solid var(--border);border-radius:var(--r);padding:.85rem;cursor:pointer;transition:all .18s;background:var(--white)">
            <div style="font-size:.8rem;font-weight:700">TF Gopay</div>
            <div style="font-size:.75rem;color:var(--text3);margin-top:.2rem">1234567890</div>
          </div>
        </div>
      </div>

      <!-- ORDER SUMMARY -->
      <div style="background:var(--green-xl);border-radius:var(--r);padding:1rem;margin-bottom:1rem;font-size:.875rem">
        <div style="display:flex;justify-content:space-between;margin-bottom:.4rem">
          <span>Total Pemesanan</span>
          <span id="s4_total" style="font-weight:700">Rp 0</span>
        </div>
        <div style="display:flex;justify-content:space-between">
          <span>ID Pesanan</span>
          <span id="s4_kode" style="font-weight:700;font-size:.8rem">-</span>
        </div>
      </div>

      <!-- UPLOAD BUKTI -->
      <div class="form-group">
        <label class="form-label">Unggah Bukti Pembayaran</label>
        <label for="s4_bukti" style="display:flex;align-items:center;gap:.75rem;border:2px dashed var(--border);border-radius:var(--r);padding:1.1rem;cursor:pointer;transition:all .18s;background:var(--white)" id="uploadLabel">
          <i class="fas fa-upload" style="color:var(--green);font-size:1.1rem"></i>
          <span id="uploadText" style="font-size:.875rem;color:var(--text3)">Unggah Bukti Pembayaran</span>
        </label>
        <input type="file" id="s4_bukti" style="display:none" accept="image/*,.pdf" onchange="previewBukti(this)">
        <div id="buktiPreview"></div>
      </div>

      <div style="display:flex;justify-content:space-between;margin-top:.5rem">
        <button class="btn btn-outline" onclick="goStep(3)"><i class="fas fa-chevron-left"></i> Back</button>
        <a href="javascript:void(0)" id="lihatVerif" class="btn btn-primary" onclick="goVerif()" style="display:none">Lihat Verifikasi <i class="fas fa-chevron-right"></i></a>
        <button class="btn btn-primary" id="s4_submitBtn" onclick="submitBooking()"><i class="fas fa-paper-plane"></i> Kirim</button>
      </div>
    </div>

  </main>
</div>

<?php include __DIR__ . '/../../pages/partials/footer.php'; ?>

<!-- Profil modal -->
<div class="modal-bg" id="profilModal">
  <div class="modal" style="max-width:500px">
    <div class="modal-head"><span class="modal-title"><i class="fas fa-user"></i> Profil User</span><button class="modal-close" onclick="S.closeModal('profilModal')"><i class="fas fa-times"></i></button></div>
    <div class="modal-body">
      <div style="background:var(--green-xl);border-radius:var(--r);padding:1rem;display:flex;align-items:center;gap:.85rem;margin-bottom:1.25rem">
        <div style="width:52px;height:52px;border-radius:50%;background:var(--green2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.3rem"><i class="fas fa-user"></i></div>
        <div>
          <div style="font-weight:700"><?= htmlspecialchars($pe['nama']) ?></div>
          <div style="font-size:.8rem;color:var(--text3)">Penghuni</div>
          <span class="badge b-green">User</span>
        </div>
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
let curStep = 1;
let bookingResult = null;
let selectedKamarId = <?= $preselKamar ?: 0 ?>;
let selectedTglMulai = null;
let calInstance = null;
let bookingLocked = false;

function showProfil() { S.openModal('profilModal'); }
function doLogout() { confirmLogout(); }

async function checkExistingBooking() {
  const r = await S.req('get_my_bookings','GET',{});
  if (r.status === 'success' && Array.isArray(r.data)) {
    const hasBooking = r.data.some(b => ['Pending','Aktif'].includes(b.status));
    if (hasBooking) {
      bookingLocked = true;
      document.getElementById('bookingBlockedAlert').style.display = 'block';
      document.querySelectorAll('#step1 input,#step1 textarea,#step1 select,#step1 button,#step2 select,#step2 button,#step3 button,#step4 button,#step4 input,#step4 .pay-method').forEach(el => {
        el.disabled = true;
        if (el.classList && el.classList.contains('pay-method')) el.style.cursor = 'not-allowed';
      });
    }
  }
}

// ---- STEP NAVIGATION ----
function goStep(n) {
  if (n > curStep) {
    if (curStep === 1 && !validateStep1()) return;
    if (curStep === 2 && !validateStep2()) return;
    if (curStep === 3 && !validateStep3()) return;
  }
  document.getElementById('step' + curStep).style.display = 'none';
  [1,2,3,4].forEach(i => {
    const el = document.getElementById('st'+i);
    el.className = 'step' + (i < n ? ' done' : i === n ? ' active' : '');
  });
  curStep = n;
  document.getElementById('step' + n).style.display = 'block';
  if (n === 3) initStep3();
  window.scrollTo(0, 0);
}

// ---- STEP 1 VALIDATION ----
function validateStep1() {
  const nama = document.getElementById('s1_nama').value.trim();
  const hp   = document.getElementById('s1_hp').value.trim();
  if (!nama || !hp) {
    document.getElementById('step1Alert').innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Nama dan nomor WhatsApp wajib diisi.</div>';
    return false;
  }
  document.getElementById('step1Alert').innerHTML = '';
  return true;
}

// ---- STEP 2 ----
function selectTipe(tipe) {
  document.querySelectorAll('.tipe-card').forEach(c => {
    const isActive = c.dataset.tipe === tipe;
    c.style.background = isActive ? 'var(--green2)' : 'var(--white)';
    c.style.color = isActive ? '#fff' : '';
    c.style.borderColor = isActive ? 'var(--green2)' : 'var(--border)';
    c.querySelectorAll('div').forEach(d => d.style.color = isActive ? 'rgba(255,255,255,.85)' : '');
  });
  // Filter dropdown
  const sel = document.getElementById('s2_kamar');
  Array.from(sel.options).forEach(o => {
    if (!o.value) return;
    o.hidden = o.dataset.tipe !== tipe;
  });
  sel.value = '';
  updateRingkasan();
}

function updateRingkasan() {
  const sel = document.getElementById('s2_kamar');
  const opt = sel.options[sel.selectedIndex];
  if (!opt || !opt.value) { document.getElementById('s2_total').textContent = 'Rp 0'; return; }
  const harga = parseFloat(opt.dataset.harga);
  selectedKamarId = parseInt(opt.value);
  document.getElementById('s2_total').textContent = 'Rp ' + harga.toLocaleString('id-ID');
}

function validateStep2() {
  const sel = document.getElementById('s2_kamar');
  if (!sel.value) {
    document.getElementById('step2Alert').innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Pilih nomor kamar terlebih dahulu.</div>';
    return false;
  }
  selectedKamarId = parseInt(sel.value);
  document.getElementById('step2Alert').innerHTML = '';
  return true;
}

// ---- STEP 3 INIT ----
function initStep3() {
  const sel = document.getElementById('s2_kamar');
  const opt = sel.options[sel.selectedIndex];
  if (opt && opt.value) {
    document.getElementById('s3_kamarLabel').textContent = opt.dataset.nomor || '-';
  }
  updateStep3Summary();
  // Init SmartCalendar
  calInstance = new SmartCal('calContainer', {
    idKamar: selectedKamarId,
    selectable: true,
    onSelect(start, end) {
      selectedTglMulai = start;
      const fmt = start.toLocaleDateString('id-ID',{day:'2-digit',month:'2-digit',year:'numeric'});
      document.getElementById('s3_tglDisplay').textContent = fmt;
      updateStep3Summary();
      // Show existing booking if any
      document.getElementById('myBookingInfo').style.display = 'block';
      document.getElementById('myBookingDetail').innerHTML = `
        <div style="font-weight:600">Kamar ${opt?.dataset?.nomor||''}</div>
        <div>1 — 30 ${start.toLocaleDateString('id-ID',{month:'long',year:'numeric'})}</div>
        <span class="badge b-green" style="margin-top:.25rem">Aktif</span>`;
    }
  });
}

function updateStep3Summary() {
  const durasi = parseInt(document.getElementById('s3_durasi').value);
  const sel = document.getElementById('s2_kamar');
  const opt = sel.options[sel.selectedIndex];
  const harga = opt?.dataset?.harga ? parseFloat(opt.dataset.harga) : 0;
  const total = harga * durasi;
  document.getElementById('s3_durasiLabel').textContent = durasi + ' Bulan';
  document.getElementById('s3_totalLabel').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

function validateStep3() {
  if (!selectedTglMulai) {
    document.getElementById('step3Alert').innerHTML = '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Pilih tanggal mulai dari kalender.</div>';
    return false;
  }
  document.getElementById('step3Alert').innerHTML = '';
  // Pre-fill step 4 summary
  const sel = document.getElementById('s2_kamar');
  const opt = sel.options[sel.selectedIndex];
  const harga = parseFloat(opt?.dataset?.harga || 0);
  const durasi = parseInt(document.getElementById('s3_durasi').value);
  const total = harga * durasi;
  document.getElementById('s4_total').textContent = 'Rp ' + total.toLocaleString('id-ID');
  return true;
}

function goStep3Pay() {
  if (!validateStep3()) return;
  goStep(4);
}

// ---- STEP 4 ----
function selectMetode(el) {
  document.querySelectorAll('.pay-method').forEach(m => {
    m.style.borderColor = 'var(--border)';
    m.style.background = 'var(--white)';
  });
  el.style.borderColor = 'var(--green2)';
  el.style.background = 'var(--green-xl)';
}

function previewBukti(input) {
  const file = input.files[0];
  if (!file) return;
  document.getElementById('uploadText').textContent = file.name;
  const reader = new FileReader();
  reader.onload = e => {
    if (file.type.startsWith('image/')) {
      document.getElementById('buktiPreview').innerHTML = `<img src="${e.target.result}" style="max-width:100%;max-height:200px;border-radius:var(--r);margin-top:.5rem">`;
    } else {
      document.getElementById('buktiPreview').innerHTML = `<div style="margin-top:.5rem;font-size:.8rem;color:var(--text3)"><i class="fas fa-file-pdf"></i> ${file.name}</div>`;
    }
  };
  reader.readAsDataURL(file);
}

async function submitBooking() {
  const btn = document.getElementById('s4_submitBtn');
  const al  = document.getElementById('step4Alert');
  const sel = document.getElementById('s2_kamar');
  const opt = sel.options[sel.selectedIndex];
  const metode = document.querySelector('.pay-method[style*="var(--green-xl)"]')?.dataset?.metode || 'Transfer BRI';
  const tgl = selectedTglMulai ? selectedTglMulai.toISOString().split('T')[0] : '';
  const durasi = document.getElementById('s3_durasi').value;

  if (!tgl) { al.innerHTML='<div class="alert alert-error">Pilih tanggal sewa terlebih dahulu.</div>'; return; }

  const fd = new FormData();
  fd.append('id_kamar', selectedKamarId);
  fd.append('tanggal_mulai', tgl);
  fd.append('durasi_bulan', durasi);
  fd.append('metode_pembayaran', metode);
  fd.append('nama_penyewa', document.getElementById('s1_nama').value);
  fd.append('no_hp_penyewa', document.getElementById('s1_hp').value);
  fd.append('email_penyewa', document.getElementById('s1_email').value);
  fd.append('pekerjaan', document.getElementById('s1_pkj').value);
  fd.append('alamat_asal', document.getElementById('s1_alamat').value);

  S.loading(btn, true);
  const r = await S.req('create_booking','POST', fd);
  S.loading(btn, false);

  if (r.status !== 'success') { al.innerHTML=`<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> ${r.message}</div>`; return; }

  bookingResult = r;
  al.innerHTML = '';
  document.getElementById('s4_kode').textContent = r.kode;
  document.getElementById('s4_total').textContent = 'Rp ' + Number(r.total).toLocaleString('id-ID');

  // Upload bukti if any
  const buktiFile = document.getElementById('s4_bukti').files[0];
  if (buktiFile) {
    const fd2 = new FormData();
    fd2.append('id_booking', r.id);
    fd2.append('bukti', buktiFile);
    await S.req('upload_bukti','POST', fd2);
  }

  btn.style.display = 'none';
  document.getElementById('lihatVerif').style.display = 'inline-flex';
  al.innerHTML = `<div class="alert alert-success"><i class="fas fa-check-circle"></i> <strong>Booking berhasil!</strong> ID Pemesanan: <strong>${r.kode}</strong>. Bukti pembayaran menunggu verifikasi.</div>`;
  S.toast('Booking berhasil dibuat!', 's');
}

function goVerif() { location.href = APP_URL + '/pages/user/verifikasi.php'; }

// Init preselected kamar
document.addEventListener('DOMContentLoaded', () => {
  if (selectedKamarId) {
    const sel = document.getElementById('s2_kamar');
    for (let i=0; i<sel.options.length; i++) {
      if (parseInt(sel.options[i].value) === selectedKamarId) { sel.selectedIndex = i; updateRingkasan(); break; }
    }
  }
  checkExistingBooking();
});
</script>
</body>
</html>
