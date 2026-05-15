<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../middleware/auth.php';

function getStats(): void {
    requireAdmin();
    $kamar=DB::q("SELECT COUNT(*) t,SUM(IF(status_ketersediaan='Terisi',1,0)) terisi,SUM(IF(status_ketersediaan='Tersedia',1,0)) tersedia,SUM(IF(status_ketersediaan='Perbaikan',1,0)) perbaikan FROM kamar")->fetch_assoc();
    $booking=DB::q("SELECT COUNT(*) t,SUM(IF(status='Pending',1,0)) pending,SUM(IF(status='Aktif',1,0)) aktif,SUM(IF(status='Ditolak',1,0)) ditolak FROM booking")->fetch_assoc();
    $rev=DB::q("SELECT IFNULL(SUM(nominal),0) total FROM pembayaran WHERE status_pembayaran='Disetujui'")->fetch_assoc();
    $pv=DB::q("SELECT COUNT(*) c FROM pembayaran WHERE status_pembayaran='Proses Validasi'")->fetch_assoc();
    json(['status'=>'success','kamar'=>$kamar,'booking'=>$booking,'revenue'=>$rev['total'],'pending_verif'=>$pv['c']]);
}

function updateProfil(): void {
    requireAdmin();
    $adm=getAdmin();
    $nama=clean($_POST['nama_admin']??'');
    $email=clean($_POST['email']??'');
    $hp=clean($_POST['no_hp']??'');
    $pw=$_POST['password']??'';
    if(!$nama||!$email) json(['status'=>'error','message'=>'Nama & email wajib.']);
    if($pw) {
        if(strlen($pw)<6) json(['status'=>'error','message'=>'Password min 6 karakter.']);
        $hash=password_hash($pw,PASSWORD_BCRYPT);
        $st=DB::prep("UPDATE admin SET nama_admin=?,email=?,no_hp=?,password=? WHERE id_admin=?");
        $st->bind_param('ssssi',$nama,$email,$hp,$hash,$adm['id']);
    } else {
        $st=DB::prep("UPDATE admin SET nama_admin=?,email=?,no_hp=? WHERE id_admin=?");
        $st->bind_param('sssi',$nama,$email,$hp,$adm['id']);
    }
    if($st->execute()){ $_SESSION['admin_nama']=$nama; $_SESSION['admin_email']=$email; $_SESSION['admin_no_hp']=$hp; json(['status'=>'success','message'=>'Profil diperbarui.']); }
    json(['status'=>'error','message'=>'Gagal.']);
}
