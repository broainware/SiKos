<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../middleware/auth.php';

function getAll(): void {
    $db=DB::get();
    $w="1=1";
    if(!empty($_GET['status'])) $w.=" AND k.status_ketersediaan='".DB::esc($_GET['status'])."'";
    if(!empty($_GET['tipe']))   $w.=" AND k.tipe='".DB::esc($_GET['tipe'])."'";
    if(!empty($_GET['q'])) { $q='%'.DB::esc($_GET['q']).'%'; $w.=" AND (k.nomor_kamar LIKE '$q' OR k.tipe LIKE '$q')"; }
    $sql="SELECT k.*,GROUP_CONCAT(f.nama_fasilitas ORDER BY f.nama_fasilitas SEPARATOR '||') as fas_list
          FROM kamar k
          LEFT JOIN kamar_fasilitas kf ON k.id_kamar=kf.id_kamar
          LEFT JOIN fasilitas f ON kf.id_fasilitas=f.id_fasilitas
          WHERE $w GROUP BY k.id_kamar ORDER BY k.lantai,k.nomor_kamar";
    $r=DB::q($sql);
    $data=[];
    while($row=$r->fetch_assoc()){
        $row['fasilitas']=array_filter(explode('||',$row['fas_list']??''));
        $data[]=$row;
    }
    json(['status'=>'success','data'=>$data]);
}

function getDetail(): void {
    $id=(int)($_GET['id']??0);
    if(!$id) json(['status'=>'error','message'=>'ID tidak valid'],400);
    $st=DB::prep("SELECT k.*,GROUP_CONCAT(f.id_fasilitas SEPARATOR ',') as fas_ids,GROUP_CONCAT(f.nama_fasilitas ORDER BY f.nama_fasilitas SEPARATOR '||') as fas_names
                  FROM kamar k
                  LEFT JOIN kamar_fasilitas kf ON k.id_kamar=kf.id_kamar
                  LEFT JOIN fasilitas f ON kf.id_fasilitas=f.id_fasilitas
                  WHERE k.id_kamar=? GROUP BY k.id_kamar");
    $st->bind_param('i',$id); $st->execute();
    $row=$st->get_result()->fetch_assoc();
    if(!$row) json(['status'=>'error','message'=>'Kamar tidak ditemukan'],404);
    $row['fasilitas']=array_filter(explode('||',$row['fas_names']??''));
    $row['fas_ids_arr']=array_filter(explode(',',$row['fas_ids']??''));
    // reviews
    $st2=DB::prep("SELECT r.*,p.nama_lengkap FROM review r JOIN penyewa p ON r.id_penyewa=p.id_penyewa WHERE r.id_kamar=? AND r.status_tayang='Tayang' ORDER BY r.tanggal_review DESC LIMIT 5");
    $st2->bind_param('i',$id); $st2->execute();
    $row['reviews']=$st2->get_result()->fetch_all(MYSQLI_ASSOC);
    $avg=DB::q("SELECT AVG(rating) as a,COUNT(*) as c FROM review WHERE id_kamar=$id AND status_tayang='Tayang'")->fetch_assoc();
    $row['avg_rating']=round($avg['a']??0,1); $row['total_reviews']=$avg['c']??0;
    json(['status'=>'success','data'=>$row]);
}

function create(): void {
    requireAdmin();
    $adm=getAdmin();
    $nomor=clean($_POST['nomor_kamar']??'');
    $tipe=clean($_POST['tipe']??'');
    $lantai=(int)($_POST['lantai']??1);
    $harga=(float)($_POST['harga_per_bulan']??0);
    $status=clean($_POST['status_ketersediaan']??'Tersedia');
    $desc=clean($_POST['deskripsi']??'');
    $ket=clean($_POST['keterangan']??'');
    $fas=$_POST['fasilitas']??[];
    if(!$nomor||!$tipe||$harga<=0) json(['status'=>'error','message'=>'Data tidak lengkap.']);
    $foto=null;
    if(!empty($_FILES['foto']['name'])){
        $v=validateUpload($_FILES['foto']);
        if(!$v['ok']) json(['status'=>'error','message'=>$v['msg']]);
        if(!is_dir(UPLOAD_KAMAR)) mkdir(UPLOAD_KAMAR,0755,true);
        $fn='kamar_'.time().'_'.rand(100,999).'.'.$v['ext'];
        move_uploaded_file($_FILES['foto']['tmp_name'],UPLOAD_KAMAR.$fn); $foto=$fn;
    }
    $idA=$adm['id'];
    $st=DB::prep("INSERT INTO kamar(id_admin,nomor_kamar,tipe,lantai,harga_per_bulan,status_ketersediaan,deskripsi,foto,keterangan) VALUES(?,?,?,?,?,?,?,?,?)");
    $st->bind_param('issidssss',$idA,$nomor,$tipe,$lantai,$harga,$status,$desc,$foto,$ket);
    if(!$st->execute()) json(['status'=>'error','message'=>'Gagal tambah kamar.']);
    $kid=DB::lastId();
    if($fas){ $sf=DB::prep("INSERT IGNORE INTO kamar_fasilitas(id_kamar,id_fasilitas) VALUES(?,?)"); foreach($fas as $f){$fi=(int)$f;$sf->bind_param('ii',$kid,$fi);$sf->execute();} }
    json(['status'=>'success','message'=>'Kamar berhasil ditambahkan!','id'=>$kid]);
}

function update(): void {
    requireAdmin();
    $id=(int)($_POST['id_kamar']??0);
    $nomor=clean($_POST['nomor_kamar']??'');
    $tipe=clean($_POST['tipe']??'');
    $lantai=(int)($_POST['lantai']??1);
    $harga=(float)($_POST['harga_per_bulan']??0);
    $status=clean($_POST['status_ketersediaan']??'Tersedia');
    $desc=clean($_POST['deskripsi']??'');
    $ket=clean($_POST['keterangan']??'');
    $fas=$_POST['fasilitas']??[];
    if(!$id) json(['status'=>'error','message'=>'ID tidak valid.']);
    $st=DB::prep("UPDATE kamar SET nomor_kamar=?,tipe=?,lantai=?,harga_per_bulan=?,status_ketersediaan=?,deskripsi=?,keterangan=? WHERE id_kamar=?");
    $st->bind_param('ssidsssi',$nomor,$tipe,$lantai,$harga,$status,$desc,$ket,$id);
    if(!$st->execute()) json(['status'=>'error','message'=>'Gagal update kamar.']);
    DB::q("DELETE FROM kamar_fasilitas WHERE id_kamar=$id");
    if($fas){ $sf=DB::prep("INSERT IGNORE INTO kamar_fasilitas(id_kamar,id_fasilitas) VALUES(?,?)"); foreach($fas as $f){$fi=(int)$f;$sf->bind_param('ii',$id,$fi);$sf->execute();} }
    json(['status'=>'success','message'=>'Kamar berhasil diperbarui!']);
}

function delete(): void {
    requireAdmin();
    $id=(int)($_POST['id_kamar']??0);
    if(!$id) json(['status'=>'error','message'=>'ID tidak valid.']);
    $st=DB::prep("DELETE FROM kamar WHERE id_kamar=?");
    $st->bind_param('i',$id); $st->execute()?json(['status'=>'success','message'=>'Kamar dihapus.']):json(['status'=>'error','message'=>'Gagal.']);
}

function getFasilitas(): void {
    $r=DB::q("SELECT * FROM fasilitas ORDER BY nama_fasilitas");
    json(['status'=>'success','data'=>$r->fetch_all(MYSQLI_ASSOC)]);
}
