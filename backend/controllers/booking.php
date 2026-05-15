<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../middleware/auth.php';

function create(): void {
    // Admin bisa create tanpa requirePenyewa check
    $isAdminCreate = !empty($_POST['admin_created']);
    if (!$isAdminCreate) requirePenyewa();
    $pe = isPenyewa() ? getPenyewa() : ['id'=>0];
    // Admin override already handled below
    $idK=(int)($_POST['id_kamar']??0);
    $tgl=clean($_POST['tanggal_mulai']??'');
    $dur=(int)($_POST['durasi_bulan']??1);
    $metode=clean($_POST['metode_pembayaran']??'Transfer BRI');
    $nama=clean($_POST['nama_penyewa']??'');
    $hp=clean($_POST['no_hp_penyewa']??'');
    $email=clean($_POST['email_penyewa']??'');
    $pkj=clean($_POST['pekerjaan']??'');
    $alm=clean($_POST['alamat_asal']??'');
    // Admin override: bisa set penyewa dan status
    $adminCreated = !empty($_POST['admin_created']);
    $idPenyewaOverride = (int)($_POST['id_penyewa_override']??0);
    $adminStatus = clean($_POST['admin_status']??'Pending');

    if(!$idK||!$tgl||$dur<1) json(['status'=>'error','message'=>'Data tidak lengkap.']);
    $db=DB::get();
    $st=$db->prepare("SELECT * FROM kamar WHERE id_kamar=? LIMIT 1");
    $st->bind_param('i',$idK); $st->execute();
    $kamar=$st->get_result()->fetch_assoc();
    if(!$kamar) json(['status'=>'error','message'=>'Kamar tidak ditemukan.']);
    if($kamar['status_ketersediaan']!=='Tersedia') json(['status'=>'error','message'=>'Kamar tidak tersedia.']);
    $dt=new DateTime($tgl); $de=clone $dt; $de->modify("+{$dur} month");
    $tglS=$de->format('Y-m-d');
    // Anti double booking
    $st2=$db->prepare("SELECT id_booking FROM booking WHERE id_kamar=? AND status IN('Pending','Aktif') AND tanggal_mulai<?  AND tanggal_selesai>?");
    $st2->bind_param('iss',$idK,$tglS,$tgl); $st2->execute();
    if($st2->get_result()->num_rows>0) json(['status'=>'error','message'=>'Kamar sudah dipesan pada periode tersebut.']);
    $total=$kamar['harga_per_bulan']*$dur;
    $kode=genKode();
    $idP = $adminCreated && $idPenyewaOverride ? $idPenyewaOverride : (int)$pe['id'];
    $finalStatus = $adminCreated ? $adminStatus : 'Pending';
    $st3=$db->prepare("INSERT INTO booking(kode_booking,id_penyewa,id_kamar,nama_penyewa,no_hp_penyewa,email_penyewa,pekerjaan,alamat_asal,tanggal_mulai,durasi_bulan,tanggal_selesai,total_harga,metode_pembayaran,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $st3->bind_param('siissssssisdss',$kode,$idP,$idK,$nama,$hp,$email,$pkj,$alm,$tgl,$dur,$tglS,$total,$metode,$finalStatus);
    if(!$st3->execute()) json(['status'=>'error','message'=>'Gagal buat booking.']);
    $bid=DB::lastId();
    $st4=$db->prepare("INSERT INTO pembayaran(id_booking,nominal) VALUES(?,?)");
    $st4->bind_param('id',$bid,$total); $st4->execute();
    json(['status'=>'success','message'=>'Booking berhasil!','kode'=>$kode,'id'=>$bid,'total'=>$total]);
}

function getAll(): void {
    requireAdmin();
    $w="1=1";
    if(!empty($_GET['status'])) $w.=" AND b.status='".DB::esc($_GET['status'])."'";
    if(!empty($_GET['q'])){ $q='%'.DB::esc($_GET['q']).'%'; $w.=" AND (b.kode_booking LIKE '$q' OR p.nama_lengkap LIKE '$q' OR k.nomor_kamar LIKE '$q')"; }
    $sql="SELECT b.*,p.nama_lengkap,p.no_hp,p.email as p_email,k.nomor_kamar,k.tipe,k.harga_per_bulan,pm.status_pembayaran,pm.id_pembayaran,pm.bukti_pembayaran
          FROM booking b JOIN penyewa p ON b.id_penyewa=p.id_penyewa JOIN kamar k ON b.id_kamar=k.id_kamar LEFT JOIN pembayaran pm ON b.id_booking=pm.id_booking WHERE $w ORDER BY b.tanggal_pemesanan DESC";
    $r=DB::q($sql);
    json(['status'=>'success','data'=>$r->fetch_all(MYSQLI_ASSOC)]);
}

function getMine(): void {
    requirePenyewa();
    $pe=getPenyewa(); $idP=(int)$pe['id'];
    $sql="SELECT b.*,k.nomor_kamar,k.tipe,k.harga_per_bulan,k.foto,pm.status_pembayaran,pm.id_pembayaran,pm.bukti_pembayaran,pm.catatan_admin,pm.alasan_penolakan,pm.nominal
          FROM booking b JOIN kamar k ON b.id_kamar=k.id_kamar LEFT JOIN pembayaran pm ON b.id_booking=pm.id_booking
          WHERE b.id_penyewa=$idP ORDER BY b.tanggal_pemesanan DESC";
    $r=DB::q($sql);
    json(['status'=>'success','data'=>$r->fetch_all(MYSQLI_ASSOC)]);
}

function getDetail(): void {
    $id=(int)($_GET['id']??0);
    if(!$id) json(['status'=>'error','message'=>'ID tidak valid.'],400);
    $st=DB::prep("SELECT b.*,p.nama_lengkap,p.no_hp,p.email as p_email,p.nik,k.nomor_kamar,k.tipe,k.harga_per_bulan,k.foto,pm.status_pembayaran,pm.id_pembayaran,pm.bukti_pembayaran,pm.nominal,pm.catatan_admin,pm.waktu_upload
                  FROM booking b JOIN penyewa p ON b.id_penyewa=p.id_penyewa JOIN kamar k ON b.id_kamar=k.id_kamar LEFT JOIN pembayaran pm ON b.id_booking=pm.id_booking WHERE b.id_booking=?");
    $st->bind_param('i',$id); $st->execute();
    $row=$st->get_result()->fetch_assoc();
    if(!$row) json(['status'=>'error','message'=>'Tidak ditemukan.'],404);
    json(['status'=>'success','data'=>$row]);
}

function update(): void {
    requireAdmin();
    $id=(int)($_POST['id_booking']??0);
    $status=clean($_POST['status']??'');
    if(!$id||!$status) json(['status'=>'error','message'=>'Data tidak valid.']);
    $st=DB::prep("UPDATE booking SET status=? WHERE id_booking=?");
    $st->bind_param('si',$status,$id);
    if($st->execute()){
        $db=DB::get();
        if($status==='Aktif') $db->query("UPDATE kamar k JOIN booking b ON k.id_kamar=b.id_kamar SET k.status_ketersediaan='Terisi' WHERE b.id_booking=$id");
        elseif(in_array($status,['Ditolak','Dibatalkan','Selesai'])) $db->query("UPDATE kamar k JOIN booking b ON k.id_kamar=b.id_kamar SET k.status_ketersediaan='Tersedia' WHERE b.id_booking=$id AND NOT EXISTS(SELECT 1 FROM booking b2 WHERE b2.id_kamar=k.id_kamar AND b2.id_booking!=$id AND b2.status='Aktif')");
        json(['status'=>'success','message'=>'Status booking diperbarui.']);
    }
    json(['status'=>'error','message'=>'Gagal.']);
}

function delete(): void {
    requireAdmin();
    $id=(int)($_POST['id_booking']??0);
    if(!$id) json(['status'=>'error','message'=>'ID tidak valid.']);
    $st=DB::prep("DELETE FROM booking WHERE id_booking=?");
    $st->bind_param('i',$id); $st->execute()?json(['status'=>'success','message'=>'Booking dihapus.']):json(['status'=>'error','message'=>'Gagal.']);
}

function cek(): void {
    $kode=clean($_GET['kode']??'');
    $hp=clean($_GET['no_hp']??'');
    if(!$kode&&!$hp) json(['status'=>'error','message'=>'Masukkan ID Booking atau nomor HP.']);
    $db=DB::get();
    if($kode){
        $st=$db->prepare("SELECT b.*,k.nomor_kamar,k.tipe,pm.status_pembayaran FROM booking b JOIN kamar k ON b.id_kamar=k.id_kamar LEFT JOIN pembayaran pm ON b.id_booking=pm.id_booking WHERE b.kode_booking=?");
        $st->bind_param('s',$kode); $st->execute();
    } else {
        $st=$db->prepare("SELECT b.*,k.nomor_kamar,k.tipe,pm.status_pembayaran FROM booking b JOIN kamar k ON b.id_kamar=k.id_kamar JOIN penyewa p ON b.id_penyewa=p.id_penyewa LEFT JOIN pembayaran pm ON b.id_booking=pm.id_booking WHERE p.no_hp=? ORDER BY b.tanggal_pemesanan DESC LIMIT 5");
        $st->bind_param('s',$hp); $st->execute();
    }
    $r=$st->get_result()->fetch_all(MYSQLI_ASSOC);
    if(empty($r)) json(['status'=>'error','message'=>'Data tidak ditemukan.']);
    json(['status'=>'success','data'=>$r]);
}

function getPenyewaList(): void {
    requireAdmin();
    $r = DB::q("SELECT id_penyewa, nama_lengkap, username, no_hp, email FROM penyewa ORDER BY nama_lengkap");
    json(['status'=>'success','data'=>$r->fetch_all(MYSQLI_ASSOC)]);
}
