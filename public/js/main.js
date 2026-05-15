/* SIKOS Main JS */
const API = document.querySelector('meta[name=app-url]')?.content + '/api/index.php';

const S = {
  async req(action, method='GET', data=null) {
    const url = new URL(API, location.origin);
    const opts = { method, headers:{'X-Requested-With':'XMLHttpRequest'} };
    if (method==='GET') {
      url.searchParams.set('action', action);
      if (data) Object.entries(data).forEach(([k,v]) => url.searchParams.set(k,v));
    } else {
      url.searchParams.set('action', action);
      if (data instanceof FormData) { data.append('action', action); opts.body = data; }
      else if (data) {
        const fd = new FormData(); fd.append('action', action);
        Object.entries(data).forEach(([k,v]) => fd.append(k,v)); opts.body = fd;
      }
    }
    try { const r = await fetch(url, opts); return await r.json(); }
    catch { return {status:'error',message:'Koneksi gagal. Periksa server.'}; }
  },

  toast(msg, type='s', dur=3200) {
    let c = document.getElementById('toasts');
    if (!c) { c = document.createElement('div'); c.id = 'toasts'; document.body.appendChild(c); }
    const ic = {s:'fa-check-circle',e:'fa-times-circle',w:'fa-exclamation-triangle'};
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas ${ic[type]||ic.s}"></i><span>${msg}</span>`;
    c.appendChild(t);
    setTimeout(()=>{ t.style.animation='slIn .3s ease reverse'; setTimeout(()=>t.remove(),300); }, dur);
  },

  fmtRp(n) { return 'Rp. ' + Number(n).toLocaleString('id-ID') + ' / bulan'; },
  fmtRpShort(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); },
  fmtDate(s) { if(!s) return '-'; return new Date(s).toLocaleDateString('id-ID',{day:'2-digit',month:'2-digit',year:'numeric'}); },
  fmtDateLong(s) { if(!s) return '-'; return new Date(s).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'}); },

  badge(status) {
    const m = {
      'Tersedia':'b-green','Aktif':'b-green','Disetujui':'b-green','Tayang':'b-green',
      'Pending':'b-orange','Proses Validasi':'b-orange','Menunggu':'b-orange',
      'Terisi':'b-red','Ditolak':'b-red','Disembunyikan':'b-red',
      'Perbaikan':'b-orange','Selesai':'b-blue','Dibatalkan':'b-gray',
    };
    return `<span class="badge ${m[status]||'b-gray'}">${status}</span>`;
  },

  openModal(id) { document.getElementById(id)?.classList.add('open'); },
  closeModal(id) { document.getElementById(id)?.classList.remove('open'); },
  closeAll() { document.querySelectorAll('.modal-bg.open').forEach(m=>m.classList.remove('open')); },

  loading(btn, on) {
    if (on) { btn._html=btn.innerHTML; btn.innerHTML='<span class="spinner sp-sm"></span> Memproses...'; btn.disabled=true; }
    else { btn.innerHTML=btn._html||btn.innerHTML; btn.disabled=false; }
  },

  confirm(msg, cb, yesLabel='Ya, Lanjutkan', noLabel='Batal') {
    let m = document.getElementById('_confirm_modal');
    if (!m) {
      document.body.insertAdjacentHTML('beforeend',`
        <div class="modal-bg" id="_confirm_modal">
          <div class="modal" style="max-width:380px">
            <div class="modal-head"><span class="modal-title">Konfirmasi</span></div>
            <div class="modal-body" style="text-align:center;padding:1.75rem 1.5rem">
              <p id="_confirm_msg" style="font-size:.95rem"></p>
            </div>
            <div class="modal-foot">
              <button class="btn btn-outline btn-sm" id="_confirm_no"></button>
              <button class="btn btn-danger btn-sm" id="_confirm_yes"></button>
            </div>
          </div>
        </div>`);
      m = document.getElementById('_confirm_modal');
    }
    document.getElementById('_confirm_msg').textContent = msg;
    document.getElementById('_confirm_yes').textContent = yesLabel;
    document.getElementById('_confirm_no').textContent = noLabel;
    m.classList.add('open');
    document.getElementById('_confirm_yes').onclick = () => { m.classList.remove('open'); cb(true); };
    document.getElementById('_confirm_no').onclick = () => { m.classList.remove('open'); cb(false); };
  },

  stars(container, onRate) {
    let cur = 0;
    container.innerHTML = [1,2,3,4,5].map(i=>`<span class="star fas fa-star" data-v="${i}">&#9733;</span>`).join('');
    container.querySelectorAll('.star').forEach(s => {
      s.addEventListener('mouseenter', ()=>{ container.querySelectorAll('.star').forEach((ss,j)=>ss.classList.toggle('hover',j<s.dataset.v)); });
      s.addEventListener('mouseleave', ()=>{ container.querySelectorAll('.star').forEach((ss,j)=>ss.classList.toggle('on',j<cur)); });
      s.addEventListener('click', ()=>{ cur=+s.dataset.v; container.querySelectorAll('.star').forEach((ss,j)=>{ ss.classList.toggle('on',j<cur); ss.classList.remove('hover'); }); if(onRate) onRate(cur); });
    });
    return ()=>cur;
  },
};

// Close modal on overlay click
document.addEventListener('click', e => { if(e.target.classList.contains('modal-bg')) e.target.classList.remove('open'); });
document.addEventListener('keydown', e => { if(e.key==='Escape') S.closeAll(); });

async function confirmLogout() {
  S.confirm('Apakah Anda yakin ingin keluar?', async ok => {
    if (!ok) return;
    const r = await S.req('logout','POST');
    if (r.redirect) location.href = r.redirect;
  }, 'Ya, Keluar', 'Batal');
}

/* Smart Calendar */
class SmartCal {
  constructor(el, opts={}) {
    this.el = typeof el==='string' ? document.getElementById(el) : el;
    this.opts = { idKamar:null, selectable:false, onSelect:null, onMonth:null, ...opts };
    this.now = new Date(); this.now.setHours(0,0,0,0);
    this.cur = new Date(this.now.getFullYear(), this.now.getMonth(), 1);
    this.booked = []; this.selStart = null; this.selEnd = null;
    this.init();
  }
  async init() {
    const bln = `${this.cur.getFullYear()}-${String(this.cur.getMonth()+1).padStart(2,'0')}`;
    const params = { bulan: bln };
    if (this.opts.idKamar) params.id_kamar = this.opts.idKamar;
    const res = await S.req('get_calendar','GET',params);
    if (res.status==='success') {
      this.booked = res.events.map(e=>({ start:new Date(e.start), end:new Date(e.end), status:e.status, kamar:e.kamar }));
      if (this.opts.onMonth) this.opts.onMonth(res);
    }
    this.render();
  }
  status(d) {
    for (const b of this.booked) if (d>=b.start && d<b.end) return b.status==='Aktif'?'terisi':'pending';
    return 'avail';
  }
  render() {
    const y=this.cur.getFullYear(), m=this.cur.getMonth();
    const mn=this.cur.toLocaleDateString('id-ID',{month:'long',year:'numeric'});
    const fd=new Date(y,m,1).getDay(), dim=new Date(y,m+1,0).getDate();
    const days=['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
    let h=`<div class="cal-wrap"><div class="cal-head">
      <button class="btn btn-outline btn-sm" id="_cprev"><i class="fas fa-chevron-left"></i></button>
      <span class="cal-title">${mn}</span>
      <button class="btn btn-outline btn-sm" id="_cnext"><i class="fas fa-chevron-right"></i></button>
    </div><div class="cal-grid">
    ${days.map(d=>`<div class="cal-dh">${d}</div>`).join('')}`;
    for(let i=0;i<fd;i++) h+=`<div class="cal-d other"></div>`;
    for(let d=1;d<=dim;d++){
      const dt=new Date(y,m,d); const st=this.status(dt);
      const isToday=dt.getTime()===this.now.getTime();
      const isPast=dt<this.now;
      const isSel=this.selStart&&dt.getTime()===this.selStart.getTime();
      const isRange=this.selStart&&this.selEnd&&dt>this.selStart&&dt<=this.selEnd;
      let cls='cal-d';
      if(isToday) cls+=' today';
      if(isSel) cls+=' sel';
      else if(isRange) cls+=' range';
      else if(!isPast) cls+=` ${st}`;
      if(isPast) cls+=' past';
      const canSel=this.opts.selectable&&!isPast&&st==='avail';
      if(canSel) cls+=' selectable';
      h+=`<div class="${cls}" data-d="${dt.toISOString().split('T')[0]}">${d}</div>`;
    }
    h+=`</div><div class="cal-legend">
      <div class="leg-item"><div class="leg-dot" style="background:#d1fae5"></div>Tersedia</div>
      <div class="leg-item"><div class="leg-dot" style="background:#fef3c7"></div>Pending</div>
      <div class="leg-item"><div class="leg-dot" style="background:#fee2e2"></div>Terisi</div>
    </div></div>`;
    this.el.innerHTML = h;
    document.getElementById('_cprev')?.addEventListener('click',()=>{ this.cur.setMonth(this.cur.getMonth()-1); this.init(); });
    document.getElementById('_cnext')?.addEventListener('click',()=>{ this.cur.setMonth(this.cur.getMonth()+1); this.init(); });
    if(this.opts.selectable) {
      this.el.querySelectorAll('.cal-d.selectable').forEach(el=>{
        el.addEventListener('click',()=>{
          const d=new Date(el.dataset.d);
          if(!this.selStart||(this.selStart&&this.selEnd)){ this.selStart=d; this.selEnd=null; }
          else { if(d>this.selStart){ this.selEnd=d; if(this.opts.onSelect) this.opts.onSelect(this.selStart,this.selEnd); } else { this.selStart=d; this.selEnd=null; } }
          this.render();
        });
      });
    }
  }
}
