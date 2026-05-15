<?php // pages/admin/partials/profil-modal.php
$adm = getAdmin();
?>
<!-- PROFIL ADMIN MODAL -->
<div class="modal-bg" id="profilModal">
  <div class="modal" style="max-width:500px">
    <div class="modal-head">
      <span class="modal-title"><i class="fas fa-user"></i> Profil Admin</span>
      <button class="modal-close" onclick="S.closeModal('profilModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div style="background:var(--green-xl);border-radius:var(--r);padding:1rem;display:flex;align-items:center;gap:.85rem;margin-bottom:1.25rem">
        <div style="width:52px;height:52px;border-radius:50%;background:var(--green2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.3rem"><i class="fas fa-user"></i></div>
        <div>
          <div style="font-weight:700;font-size:1rem"><?= htmlspecialchars($adm['nama']) ?></div>
          <div style="font-size:.8rem;color:var(--text3);margin-bottom:.25rem">Pemilik Kos</div>
          <span class="badge b-blue">Admin</span>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.1rem;padding:.25rem 0">
        <div>
          <div style="font-weight:600;font-size:.875rem;margin-bottom:.2rem">Username</div>
          <div style="color:var(--text3);font-size:.875rem"><?= htmlspecialchars($adm['username']) ?></div>
        </div>
        <div>
          <div style="font-weight:600;font-size:.875rem;margin-bottom:.2rem">Email</div>
          <div style="color:var(--text3);font-size:.875rem"><?= htmlspecialchars($adm['email'] ?: '-') ?></div>
        </div>
        <div style="grid-column:1/-1">
          <div style="font-weight:600;font-size:.875rem;margin-bottom:.2rem">No. Hp</div>
          <div style="color:var(--text3);font-size:.875rem"><?= htmlspecialchars($adm['no_hp'] ?: '-') ?></div>
        </div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-outline btn-sm" onclick="S.closeModal('profilModal')">Tutup</button>
    </div>
  </div>
</div>

<script>
function showProfil() { S.openModal('profilModal'); }
function doLogout() { confirmLogout(); }
</script>
