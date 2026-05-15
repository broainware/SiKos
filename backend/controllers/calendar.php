<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../middleware/auth.php';

function get(): void {
    $bln=clean($_GET['bulan']??date('Y-m'));
    $idK=(int)($_GET['id_kamar']??0);
    $p=explode('-',$bln); $y=(int)($p[0]??date('Y')); $m=(int)($p[1]??date('m'));
    $start=sprintf('%04d-%02d-01',$y,$m);
    $end=date('Y-m-t',strtotime("+2 months",strtotime($start)));
    $w="b.status IN('Pending','Aktif') AND b.tanggal_mulai<='$end' AND b.tanggal_selesai>='$start'";
    if($idK) $w.=" AND b.id_kamar=$idK";
    $sql="SELECT b.id_booking,b.kode_booking,b.id_kamar,b.tanggal_mulai,b.tanggal_selesai,b.status,k.nomor_kamar,k.tipe,b.nama_penyewa FROM booking b JOIN kamar k ON b.id_kamar=k.id_kamar WHERE $w ORDER BY b.tanggal_mulai";
    $r=DB::q($sql);
    $events=[];
    while($row=$r->fetch_assoc()){
        $c=$row['status']==='Aktif'?'#c0392b':'#e67e22';
        $events[]=['id'=>$row['id_booking'],'start'=>$row['tanggal_mulai'],'end'=>$row['tanggal_selesai'],'color'=>$c,'status'=>$row['status'],'kamar'=>$row['nomor_kamar'],'nama'=>isAdmin()?$row['nama_penyewa']:'','kode'=>$row['kode_booking']];
    }
    $kr=DB::q("SELECT id_kamar,nomor_kamar,tipe,status_ketersediaan FROM kamar ORDER BY lantai,nomor_kamar");
    $stats=DB::q("SELECT COUNT(*) as t, SUM(IF(status_ketersediaan='Terisi',1,0)) as terisi, SUM(IF(status_ketersediaan='Tersedia',1,0)) as tersedia, SUM(IF(status_ketersediaan='Perbaikan',1,0)) as perbaikan FROM kamar")->fetch_assoc();
    $pending=DB::q("SELECT COUNT(*) as c FROM booking WHERE status='Pending'")->fetch_assoc()['c'];
    json(['status'=>'success','events'=>$events,'kamar'=>$kr->fetch_all(MYSQLI_ASSOC),'stats'=>$stats,'pending'=>$pending]);
}
