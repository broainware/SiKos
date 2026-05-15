<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../middleware/auth.php';

function updateProfil(): void {
    requirePenyewa();
    $pe=getPenyewa();
    $nama=clean($_POST['nama_lengkap']??'');
    $email=clean($_POST['email']??'');
    $hp=clean($_POST['no_hp']??'');
    $nik=clean($_POST['nik']??'');
    $pw=$_POST['password']??'';
    if(!$nama) json(['status'=>'error','message'=>'Nama wajib diisi.']);
    if($pw){
        if(strlen($pw)<6) json(['status'=>'error','message'=>'Password min 6 karakter.']);
        $hash=password_hash($pw,PASSWORD_BCRYPT);
        $st=DB::prep("UPDATE penyewa SET nama_lengkap=?,email=?,no_hp=?,nik=?,password=? WHERE id_penyewa=?");
        $st->bind_param('sssssi',$nama,$email,$hp,$nik,$hash,$pe['id']);
    } else {
        $st=DB::prep("UPDATE penyewa SET nama_lengkap=?,email=?,no_hp=?,nik=? WHERE id_penyewa=?");
        $st->bind_param('ssssi',$nama,$email,$hp,$nik,$pe['id']);
    }
    if($st->execute()){ $_SESSION['penyewa_nama']=$nama; $_SESSION['penyewa_email']=$email; $_SESSION['penyewa_no_hp']=$hp; json(['status'=>'success','message'=>'Profil diperbarui.']); }
    json(['status'=>'error','message'=>'Gagal.']);
}
