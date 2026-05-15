<?php
require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/middleware/auth.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
  // Auth
  'login'                  => ['POST','auth','doLogin'],
  'register'               => ['POST','auth','doRegister'],
  'logout'                 => ['POST','auth','doLogout'],
  // Kamar
  'get_kamar'              => ['GET','kamar','getAll'],
  'get_kamar_detail'       => ['GET','kamar','getDetail'],
  'create_kamar'           => ['POST','kamar','create'],
  'update_kamar'           => ['POST','kamar','update'],
  'delete_kamar'           => ['POST','kamar','delete'],
  'get_fasilitas'          => ['GET','kamar','getFasilitas'],
  // Booking
  'create_booking'         => ['POST','booking','create'],
  'get_bookings'           => ['GET','booking','getAll'],
  'get_my_bookings'        => ['GET','booking','getMine'],
  'get_booking_detail'     => ['GET','booking','getDetail'],
  'update_booking'         => ['POST','booking','update'],
  'delete_booking'         => ['POST','booking','delete'],
  'cek_booking'            => ['GET','booking','cek'],
  'get_penyewa_list'       => ['GET','booking','getPenyewaList'],
  // Pembayaran
  'upload_bukti'           => ['POST','pembayaran','upload'],
  'verifikasi'             => ['POST','pembayaran','verifikasi'],
  'get_pembayaran'         => ['GET','pembayaran','getAll'],
  // Perpanjangan
  'create_perpanjangan'    => ['POST','pembayaran','createPerpanjangan'],
  'get_perpanjangan'       => ['GET','pembayaran','getPerpanjangan'],
  'verifikasi_perpanjangan'=> ['POST','pembayaran','verifikasiPerpanjangan'],
  // Review
  'get_reviews'            => ['GET','review','getAll'],
  'create_review'          => ['POST','review','create'],
  'toggle_review'          => ['POST','review','toggle'],
  'delete_review'          => ['POST','review','delete'],
  // Calendar
  'get_calendar'           => ['GET','calendar','get'],
  // Stats + Profil
  'get_stats'              => ['GET','admin','getStats'],
  'update_admin_profil'    => ['POST','admin','updateProfil'],
  'update_penyewa_profil'  => ['POST','penyewa','updateProfil'],
];

if(!isset($routes[$action])) { json(['status'=>'error','message'=>'Endpoint tidak ditemukan: '.$action],404); }
[$em,$ctrl,$fn]=$routes[$action];
if($method!==$em) { json(['status'=>'error','message'=>'Method not allowed'],405); }

$file=__DIR__.'/../backend/controllers/'.$ctrl.'.php';
if(!file_exists($file)) { json(['status'=>'error','message'=>'Controller tidak ditemukan'],500); }
require_once $file;
if(!function_exists($fn)) { json(['status'=>'error','message'=>'Fungsi tidak ditemukan: '.$fn],500); }
$fn();
