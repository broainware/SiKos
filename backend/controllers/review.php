<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../middleware/auth.php';

function getAll(): void {
    $idK=(int)($_GET['id_kamar']??0);
    $w=isAdmin()?'1=1':"r.status_tayang='Tayang'";
    if($idK) $w.=" AND r.id_kamar=$idK";
    $sql="SELECT r.*,p.nama_lengkap,k.nomor_kamar,k.tipe FROM review r JOIN penyewa p ON r.id_penyewa=p.id_penyewa JOIN kamar k ON r.id_kamar=k.id_kamar WHERE $w ORDER BY r.tanggal_review DESC";
    $r=DB::q($sql);

    $stats = DB::q("SELECT COUNT(*) c, ROUND(AVG(rating),1) avg FROM review WHERE status_tayang='Tayang'")->fetch_assoc();
    $distRows = DB::q("SELECT rating, COUNT(*) c FROM review WHERE status_tayang='Tayang' GROUP BY rating ORDER BY rating DESC")->fetch_all(MYSQLI_ASSOC);
    $dist = [];
    foreach($distRows as $row) { $dist[(int)$row['rating']] = (int)$row['c']; }

    json(['status'=>'success','data'=>$r->fetch_all(MYSQLI_ASSOC),'summary'=>[
      'total' => (int)$stats['c'],
      'avg' => (float)($stats['avg'] ?? 0),
      'dist' => $dist,
    ]]);
}

function create(): void {
    requirePenyewa();
    $pe=getPenyewa(); $idP=(int)$pe['id'];
    $idK=(int)($_POST['id_kamar']??0);
    $idB=(int)($_POST['id_booking']??0);
    $rating=(int)($_POST['rating']??0);
    $kat=clean($_POST['komentar']??'');
    if(!$idK||$rating<1||$rating>5||!$kat) json(['status'=>'error','message'=>'Data review tidak lengkap.']);
    $st=DB::prep("SELECT id_review FROM review WHERE id_penyewa=? AND id_booking=?");
    $st->bind_param('ii',$idP,$idB); $st->execute();
    if($st->get_result()->num_rows>0) json(['status'=>'error','message'=>'Sudah pernah memberikan review untuk booking ini.']);
    $st2=DB::prep("INSERT INTO review(id_penyewa,id_kamar,id_booking,rating,komentar) VALUES(?,?,?,?,?)");
    $st2->bind_param('iiiis',$idP,$idK,$idB,$rating,$kat);
    $st2->execute()?json(['status'=>'success','message'=>'Review berhasil dikirim!']):json(['status'=>'error','message'=>'Gagal.']);
}

function toggle(): void {
    requireAdmin();
    $id=(int)($_POST['id_review']??0);
    if(!$id) json(['status'=>'error','message'=>'ID tidak valid.']);
    $st=DB::prep("UPDATE review SET status_tayang=IF(status_tayang='Tayang','Disembunyikan','Tayang') WHERE id_review=?");
    $st->bind_param('i',$id); $st->execute()?json(['status'=>'success','message'=>'Status diperbarui.']):json(['status'=>'error','message'=>'Gagal.']);
}

function delete(): void {
    requireAdmin();
    $id=(int)($_POST['id_review']??0);
    if(!$id) json(['status'=>'error','message'=>'ID tidak valid.']);
    $st=DB::prep("DELETE FROM review WHERE id_review=?");
    $st->bind_param('i',$id); $st->execute()?json(['status'=>'success','message'=>'Review dihapus.']):json(['status'=>'error','message'=>'Gagal.']);
}
