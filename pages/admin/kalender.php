<?php
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/middleware/auth.php';
requireAdmin();
$pageTitle = 'Kalender Master'; $activePage = 'beranda';
?>
<?php include __DIR__ . '/../../pages/partials/header.php'; ?>
<div class="app-layout">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <main class="main-content">
    <h1 style="font-size:1.7rem;font-weight:800;margin-bottom:1.75rem">Kalender Ketersediaan</h1>

    <!-- SUMMARY CARD -->
    <div class="card" style="margin-bottom:1.5rem;padding:1.25rem 1.5rem">
      <div id="summaryTitle" style="font-size:1.3rem;font-weight:700;font-family:'Poppins',sans-serif;margin-bottom:.75rem">Memuat...</div>
      <div id="summaryStats" style="display:flex;gap:1.5rem;flex-wrap:wrap;font-size:.875rem;color:var(--text3)">
        <span>Memuat statistik...</span>
      </div>
    </div>

    <!-- CALENDAR -->
    <div class="card" style="padding:1.5rem">
      <!-- Header row -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem">
        <div style="display:flex;align-items:center;gap:.75rem">
          <i class="fas fa-calendar-alt" style="color:var(--green);font-size:1.1rem"></i>
          <span id="calMonthTitle" style="font-size:1.1rem;font-weight:700;font-family:'Poppins',sans-serif">Memuat...</span>
        </div>
        <div style="display:flex;align-items:center;gap:.75rem">
          <select id="filterKamar" class="form-control" style="width:auto;min-width:160px;border-radius:var(--r3)" onchange="reloadCal()">
            <option value="">Semua Kamar</option>
          </select>
          <span id="calYear" style="font-weight:700;color:var(--text3)"></span>
          <i class="fas fa-chevron-down" style="color:var(--text3)"></i>
        </div>
      </div>

      <!-- Day headers -->
      <div id="calContainer"></div>

      <!-- Legend -->
      <div style="display:flex;gap:1.25rem;margin-top:1rem;flex-wrap:wrap;font-size:.8rem">
        <div style="display:flex;align-items:center;gap:.4rem"><div style="width:14px;height:14px;background:#fee2e2;border-radius:3px"></div><span>Penyewa Aktif</span></div>
        <div style="display:flex;align-items:center;gap:.4rem"><div style="width:14px;height:14px;background:var(--green-xl);border-radius:3px"></div><span>Kamar kosong</span></div>
        <div style="display:flex;align-items:center;gap:.4rem"><div style="width:14px;height:14px;background:#fef3c7;border-radius:3px"></div><span>Pending</span></div>
      </div>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../../pages/partials/footer.php'; ?>
<?php include __DIR__ . '/partials/profil-modal.php'; ?>

<script>
const APP_URL = '<?= APP_URL ?>';
let curDate = new Date();
let calEvents = [];
let calBookedMap = {};

function reloadCal() { renderCalendar(); }

async function loadCal() {
  const idKamar = document.getElementById('filterKamar').value;
  const bln = `${curDate.getFullYear()}-${String(curDate.getMonth()+1).padStart(2,'0')}`;
  const params = { bulan: bln };
  if (idKamar) params.id_kamar = idKamar;
  const r = await S.req('get_calendar','GET',params);
  if (r.status !== 'success') return;

  calEvents = r.events;
  const stats = r.stats;
  const pending = r.pending;

  // Fill kamar dropdown
  const sel = document.getElementById('filterKamar');
  if (sel.options.length <= 1) {
    r.kamar.forEach(k => {
      const o = document.createElement('option');
      o.value = k.id_kamar; o.textContent = `Kamar ${k.nomor_kamar}`;
      sel.appendChild(o);
    });
  }

  // Summary
  const mnName = curDate.toLocaleDateString('id-ID',{month:'long',year:'numeric'});
  document.getElementById('summaryTitle').textContent = mnName;
  document.getElementById('summaryStats').innerHTML = `
    <span>Kamar terisi : <strong>${stats?.terisi||0}</strong></span>
    <span>Kamar kosong : <strong>${stats?.tersedia||0}</strong></span>
    <span>Pending : <strong>${pending||0}</strong></span>`;
  document.getElementById('calMonthTitle').textContent = curDate.toLocaleDateString('id-ID',{month:'long'});
  document.getElementById('calYear').textContent = curDate.getFullYear();

  // Build booked map
  calBookedMap = {};
  calEvents.forEach(e => {
    const s = new Date(e.start), end = new Date(e.end);
    for (let d = new Date(s); d < end; d.setDate(d.getDate()+1)) {
      const key = d.toISOString().split('T')[0];
      if (!calBookedMap[key]) calBookedMap[key] = [];
      calBookedMap[key].push(e);
    }
  });

  renderCalendar();
}

function renderCalendar() {
  const y = curDate.getFullYear(), m = curDate.getMonth();
  const fd = new Date(y,m,1).getDay();
  const dim = new Date(y,m+1,0).getDate();
  const days = ['03','Mo','Tu','We','Th','Fr','Sa','Su'];
  const today = new Date(); today.setHours(0,0,0,0);

  let h = `<div style="display:grid;grid-template-columns:40px repeat(7,1fr);gap:2px">`;
  // Headers
  days.forEach(d => h += `<div style="text-align:center;font-size:.72rem;font-weight:700;color:var(--text3);padding:.4rem 0">${d}</div>`);

  // Week number col placeholder + empty cells
  h += `<div></div>`;
  for (let i = 0; i < fd; i++) h += `<div style="aspect-ratio:1"></div>`;

  for (let d = 1; d <= dim; d++) {
    const dt = new Date(y, m, d);
    const key = dt.toISOString().split('T')[0];
    const booked = calBookedMap[key] || [];
    const isToday = dt.getTime() === today.getTime();

    let bg = 'var(--green-xl)';
    let color = 'var(--green3)';
    if (booked.length) {
      const hasAktif = booked.some(b => b.status === 'Aktif');
      bg = hasAktif ? '#fee2e2' : '#fef3c7';
      color = hasAktif ? '#991b1b' : '#92400e';
    }

    // Add week number at start of week (Sunday = fd=0)
    if ((fd + d - 1) % 7 === 0) h += `<div style="display:flex;align-items:center;justify-content:center;font-size:.7rem;color:var(--text3);font-weight:700">${Math.ceil((fd+d)/7)}</div>`;

    const borderStyle = isToday ? '2px solid var(--green)' : '1px solid var(--border)';
    h += `<div style="aspect-ratio:1;display:flex;align-items:center;justify-content:center;font-size:.8rem;border-radius:6px;background:${bg};color:${color};border:${borderStyle};cursor:${booked.length?'pointer':'default'};font-weight:${isToday?'800':'400'}" title="${booked.map(b=>b.nama||b.kamar).join(', ')}" ${booked.length?`onclick="showDayDetail('${key}')"`:''}>
      ${d}
    </div>`;
  }
  h += '</div>';

  // Prev/Next nav
  h += `<div style="display:flex;align-items:center;justify-content:space-between;margin-top:1rem">
    <button class="btn btn-outline btn-sm" onclick="changeMonth(-1)"><i class="fas fa-chevron-left"></i></button>
    <span style="font-size:.875rem;color:var(--text3)">${curDate.toLocaleDateString('id-ID',{month:'long',year:'numeric'})}</span>
    <button class="btn btn-outline btn-sm" onclick="changeMonth(1)"><i class="fas fa-chevron-right"></i></button>
  </div>`;

  // Legend events
  if (calEvents.length) {
    h += `<div style="margin-top:1rem;font-size:.8rem;color:var(--text3)">`;
    calEvents.slice(0,5).forEach(e => {
      h += `<div style="display:flex;align-items:center;gap:.35rem;margin-bottom:.3rem">
        <div style="width:12px;height:12px;border-radius:3px;background:${e.color}"></div>
        <span>${e.nama||'Penyewa'} — ${e.kamar} (${S.fmtDate(e.start)} s/d ${S.fmtDate(e.end)})</span>
      </div>`;
    });
    h += '</div>';
  }

  document.getElementById('calContainer').innerHTML = h;
}

function changeMonth(delta) {
  curDate.setMonth(curDate.getMonth() + delta);
  loadCal();
}

function showDayDetail(key) {
  const events = calBookedMap[key] || [];
  if (!events.length) return;
  const info = events.map(e => `<div style="padding:.5rem 0;border-bottom:1px solid var(--border)">
    <div style="font-weight:600">${e.kamar} — ${e.status}</div>
    <div style="font-size:.8rem;color:var(--text3)">${e.nama||''} · ${e.kode}</div>
  </div>`).join('');
  const ex = document.getElementById('_dayModal');
  if (ex) ex.remove();
  document.body.insertAdjacentHTML('beforeend', `
    <div class="modal-bg open" id="_dayModal">
      <div class="modal" style="max-width:380px">
        <div class="modal-head"><span class="modal-title">Detail Tanggal ${S.fmtDateLong(key)}</span><button class="modal-close" onclick="document.getElementById('_dayModal').remove()"><i class="fas fa-times"></i></button></div>
        <div class="modal-body">${info}</div>
        <div class="modal-foot"><button class="btn btn-outline btn-sm" onclick="document.getElementById('_dayModal').remove()">Tutup</button></div>
      </div>
    </div>`);
}

loadCal();
</script>
</body>
</html>
