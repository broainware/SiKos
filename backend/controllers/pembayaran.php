<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../middleware/auth.php';

function upload(): void {
    requirePenyewa();
    $pe=getPenyewa(); $idP=(int)$pe['id'];
    $id=(int)($_POST['id_booking']??0);
    $type=$_POST['type']??'booking'; // 'booking' or 'perpanjangan'
    $idPerp=(int)($_POST['id_perpanjangan']??0);

    if(!$id||empty($_FILES['bukti']['name'])) json(['status'=>'error','message'=>'Data tidak lengkap.']);
    $v=validateUpload($_FILES['bukti']);
    if(!$v['ok']) json(['status'=>'error','message'=>$v['msg']]);

    if(!is_dir(UPLOAD_BAYAR)) mkdir(UPLOAD_BAYAR,0755,true);
    $fn='bukti_'.($type==='perpanjangan'?'perp_'.$idPerp:'bk_'.$id).'_'.time().'.'.$v['ext'];
    if(!move_uploaded_file($_FILES['bukti']['tmp_name'],UPLOAD_BAYAR.$fn)) json(['status'=>'error','message'=>'Gagal upload.']);

    $now=date('Y-m-d H:i:s');
    $db=DB::get();

    if($type==='perpanjangan' && $idPerp) {
        // Upload untuk perpanjangan
        $st=$db->prepare("SELECT id_perpanjangan FROM perpanjangan WHERE id_perpanjangan=? AND id_penyewa=?");
        $st->bind_param('ii',$idPerp,$idP); $st->execute();
        if($st->get_result()->num_rows===0) json(['status'=>'error','message'=>'Akses ditolak.'],403);

        $st2=$db->prepare("UPDATE perpanjangan SET bukti_pembayaran=?,waktu_upload=?,status='Proses Validasi' WHERE id_perpanjangan=?");
        $st2->bind_param('ssi',$fn,$now,$idPerp);
        $st2->execute()?json(['status'=>'success','message'=>'Bukti perpanjangan berhasil diunggah. Menunggu verifikasi admin.','filename'=>$fn]):json(['status'=>'error','message'=>'Gagal simpan.']);
    } else {
        // Upload untuk booking
        $st=$db->prepare("SELECT id_booking FROM booking WHERE id_booking=? AND id_penyewa=?");
        $st->bind_param('ii',$id,$idP); $st->execute();
        if($st->get_result()->num_rows===0) json(['status'=>'error','message'=>'Akses ditolak.'],403);

        $st2=$db->prepare("UPDATE pembayaran SET bukti_pembayaran=?,waktu_upload=?,status_pembayaran='Proses Validasi' WHERE id_booking=?");
        $st2->bind_param('ssi',$fn,$now,$id);
        $st2->execute()?json(['status'=>'success','message'=>'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.','filename'=>$fn]):json(['status'=>'error','message'=>'Gagal simpan.']);
    }
}

function verifikasi(): void {
    requireAdmin();
    $idPm=(int)($_POST['id_pembayaran']??0);
    $idPerp=(int)($_POST['id_perpanjangan']??0);
    $aksi=clean($_POST['aksi']??'');
    $catatan=clean($_POST['catatan']??'');

    if(!in_array($aksi,['approve','reject'])) json(['status'=>'error','message'=>'Aksi tidak valid.']);

    $db=$DB=DB::get();
    $now=date('Y-m-d H:i:s');

    if($idPerp) {
        // Verifikasi perpanjangan
        $spay=$aksi==='approve'?'Disetujui':'Ditolak';
        $st=$db->prepare("UPDATE perpanjangan SET status=?,waktu_verifikasi=?,catatan_admin=? WHERE id_perpanjangan=?");
        $st->bind_param('sssi',$spay,$now,$catatan,$idPerp); $st->execute();

        if($aksi==='approve') {
            // Perpanjang tanggal selesai booking
            $perp=$db->query("SELECT * FROM perpanjangan WHERE id_perpanjangan=$idPerp")->fetch_assoc();
            if($perp) {
                $newEnd=$perp['tanggal_selesai'];
                $stb=$db->prepare("UPDATE booking SET tanggal_selesai=?,durasi_bulan=durasi_bulan+?,status='Aktif' WHERE id_booking=?");
                $stb->bind_param('sii',$newEnd,$perp['durasi_tambah'],$perp['id_booking']); $stb->execute();
                // Update kamar tetap Terisi
                $db->query("UPDATE kamar k JOIN booking b ON k.id_kamar=b.id_kamar SET k.status_ketersediaan='Terisi' WHERE b.id_booking={$perp['id_booking']}");
            }
            json(['status'=>'success','message'=>'Perpanjangan disetujui. Masa sewa diperpanjang.']);
        } else {
            json(['status'=>'success','message'=>'Perpanjangan ditolak. Silakan upload ulang atau hubungi admin.']);
        }
    } elseif($idPm) {
        // Verifikasi pembayaran booking
        $spay=$aksi==='approve'?'Disetujui':'Ditolak';
        $sbook=$aksi==='approve'?'Aktif':'Ditolak';
        $al=null;
        if($aksi==='reject') $al=$catatan;

        $st=$db->prepare("UPDATE pembayaran SET status_pembayaran=?,waktu_verifikasi=?,catatan_admin=?,alasan_penolakan=? WHERE id_pembayaran=?");
        $st->bind_param('ssssi',$spay,$now,$catatan,$al,$idPm); $st->execute();

        $st2=$db->prepare("UPDATE booking b JOIN pembayaran pm ON b.id_booking=pm.id_booking SET b.status=? WHERE pm.id_pembayaran=?");
        $st2->bind_param('si',$sbook,$idPm); $st2->execute();

        if($aksi==='approve') {
            $db->query("UPDATE kamar k JOIN booking b ON k.id_kamar=b.id_kamar JOIN pembayaran pm ON b.id_booking=pm.id_booking SET k.status_ketersediaan='Terisi' WHERE pm.id_pembayaran=$idPm");
            json(['status'=>'success','message'=>'Pembayaran diverifikasi. Booking aktif.']);
        } else {
            // Pembayaran ditolak: booking kembali ke Pending agar user bisa upload ulang
            $st3=$db->prepare("UPDATE booking b JOIN pembayaran pm ON b.id_booking=pm.id_booking SET b.status='Pending' WHERE pm.id_pembayaran=?");
            $st3->bind_param('i',$idPm); $st3->execute();
            json(['status'=>'success','message'=>'Pembayaran ditolak. User akan menerima notifikasi alasan penolakan.']);
        }
    } else {
        json(['status'=>'error','message'=>'ID tidak valid.']);
    }
}

function getAll(): void {
    requireAdmin();
    $w="1=1";
    if(!empty($_GET['status'])) $w.=" AND pm.status_pembayaran='".DB::esc($_GET['status'])."'";
    if(!empty($_GET['q'])){ $q='%'.DB::esc($_GET['q']).'%'; $w.=" AND (b.kode_booking LIKE '$q' OR p.nama_lengkap LIKE '$q' OR k.nomor_kamar LIKE '$q')"; }
    $sql="SELECT pm.*,b.kode_booking,b.total_harga,b.status as status_booking,b.tanggal_pemesanan,b.tanggal_mulai,b.id_penyewa,b.id_booking as bid,p.nama_lengkap,k.nomor_kamar,k.tipe
          FROM pembayaran pm
          JOIN booking b ON pm.id_booking=b.id_booking
          JOIN penyewa p ON b.id_penyewa=p.id_penyewa
          JOIN kamar k ON b.id_kamar=k.id_kamar
          WHERE $w ORDER BY FIELD(pm.status_pembayaran,'Proses Validasi','Menunggu','Ditolak','Disetujui'), pm.waktu_upload DESC";
    $r=DB::q($sql);
    json(['status'=>'success','data'=>$r->fetch_all(MYSQLI_ASSOC)]);
}

function getPerpanjangan(): void {
    $onlyAdmin = !empty($_GET['admin']);
    if($onlyAdmin) requireAdmin();
    else requirePenyewa();

    $db=DB::get();
    if($onlyAdmin) {
        $w="1=1";
        if(!empty($_GET['status'])) $w.=" AND pr.status='".DB::esc($_GET['status'])."'";
        $sql="SELECT pr.*,p.nama_lengkap,k.nomor_kamar,k.tipe,b.kode_booking FROM perpanjangan pr JOIN penyewa p ON pr.id_penyewa=p.id_penyewa JOIN kamar k ON pr.id_kamar=k.id_kamar JOIN booking b ON pr.id_booking=b.id_booking WHERE $w ORDER BY pr.created_at DESC";
    } else {
        $pe=getPenyewa(); $idP=(int)$pe['id'];
        $sql="SELECT pr.*,k.nomor_kamar,k.tipe,b.kode_booking FROM perpanjangan pr JOIN kamar k ON pr.id_kamar=k.id_kamar JOIN booking b ON pr.id_booking=b.id_booking WHERE pr.id_penyewa=$idP ORDER BY pr.created_at DESC";
    }
    $r=DB::q($sql);
    json(['status'=>'success','data'=>$r->fetch_all(MYSQLI_ASSOC)]);
}

function createPerpanjangan(): void {
    requirePenyewa();
    $pe=getPenyewa(); $idP=(int)$pe['id'];
    $idBook=(int)($_POST['id_booking']??0);
    $dur=(int)($_POST['durasi_tambah']??1);
    $metode=clean($_POST['metode_pembayaran']??'Transfer BRI');

    if(!$idBook||$dur<1) json(['status'=>'error','message'=>'Data tidak lengkap.']);

    $db=DB::get();
    // Ambil data booking aktif milik penyewa
    $st=$db->prepare("SELECT b.*,k.harga_per_bulan FROM booking b JOIN kamar k ON b.id_kamar=k.id_kamar WHERE b.id_booking=? AND b.id_penyewa=? AND b.status='Aktif'");
    $st->bind_param('ii',$idBook,$idP); $st->execute();
    $book=$st->get_result()->fetch_assoc();
    if(!$book) json(['status'=>'error','message'=>'Booking aktif tidak ditemukan.']);

    // Cek tidak ada perpanjangan pending
    $stC=$db->prepare("SELECT id_perpanjangan FROM perpanjangan WHERE id_booking=? AND status IN('Menunggu','Proses Validasi')");
    $stC->bind_param('i',$idBook); $stC->execute();
    if($stC->get_result()->num_rows>0) json(['status'=>'error','message'=>'Masih ada perpanjangan yang sedang diproses.']);

    // Hitung tanggal perpanjangan dari tanggal_selesai booking saat ini
    $tglMulai=$book['tanggal_selesai'];
    $dt=new DateTime($tglMulai); $dt->modify("+{$dur} month");
    $tglSelesai=$dt->format('Y-m-d');
    $total=$book['harga_per_bulan']*$dur;

    $st2=$db->prepare("INSERT INTO perpanjangan(id_booking,id_penyewa,id_kamar,durasi_tambah,tanggal_mulai,tanggal_selesai,total_harga,metode_pembayaran) VALUES(?,?,?,?,?,?,?,?)");
    $st2->bind_param('iiisssds',$idBook,$idP,$book['id_kamar'],$dur,$tglMulai,$tglSelesai,$total,$metode);
    if($st2->execute()) {
        json(['status'=>'success','message'=>'Perpanjangan berhasil diajukan. Silakan upload bukti pembayaran.','id_perpanjangan'=>DB::lastId(),'total'=>$total,'tgl_selesai'=>$tglSelesai]);
    } else {
        json(['status'=>'error','message'=>'Gagal mengajukan perpanjangan.']);
    }
}

function verifikasiPerpanjangan(): void {
    requireAdmin();
    $idPerp=(int)($_POST['id_perpanjangan']??0);
    $aksi=clean($_POST['aksi']??'');
    $catatan=clean($_POST['catatan']??'');
    // Forward ke verifikasi umum
    $_POST['id_perpanjangan']=$idPerp;
    $_POST['aksi']=$aksi;
    $_POST['catatan']=$catatan;
    $_POST['id_pembayaran']=0;
    verifikasi();
}
