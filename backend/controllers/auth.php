<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../middleware/auth.php';

function doLogin(): void {
    $u = clean($_POST['username']??'');
    $p = $_POST['password']??'';
    if(!$u||!$p) { json(['status'=>'error','message'=>'Username dan password wajib diisi.']); }
    $db = DB::get();

    // Attempt admin login first, then penyewa
    $st = $db->prepare("SELECT * FROM admin WHERE username=? OR email=? LIMIT 1");
    $st->bind_param('ss',$u,$u);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    if($row && password_verify($p,$row['password'])) {
        $_SESSION['admin_id']=$row['id_admin'];
        $_SESSION['admin_nama']=$row['nama_admin'];
        $_SESSION['admin_username']=$row['username'];
        $_SESSION['admin_email']=$row['email'];
        $_SESSION['admin_no_hp']=$row['no_hp'];
        json(['status'=>'success','message'=>'Login berhasil.','redirect'=>APP_URL.'/pages/admin/dashboard.php']);
    }

    $st = $db->prepare("SELECT * FROM penyewa WHERE username=? OR email=? LIMIT 1");
    $st->bind_param('ss',$u,$u);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    if(!$row || !password_verify($p,$row['password'])) { json(['status'=>'error','message'=>'Username atau password salah.']); }

    $_SESSION['penyewa_id']=$row['id_penyewa'];
    $_SESSION['penyewa_nama']=$row['nama_lengkap'];
    $_SESSION['penyewa_username']=$row['username'];
    $_SESSION['penyewa_email']=$row['email'];
    $_SESSION['penyewa_no_hp']=$row['no_hp'];
    $red=$_SESSION['redirect_after_login']??APP_URL.'/pages/user/dashboard.php';
    unset($_SESSION['redirect_after_login']);
    json(['status'=>'success','message'=>'Login berhasil.','redirect'=>$red]);
}

function doRegister(): void {
    $email   = clean($_POST['email']??'');
    $u       = clean($_POST['username']??'');
    $p       = $_POST['password']??'';
    $confirm = $_POST['confirm_password']??'';

    if(!$email||!$u||!$p) { json(['status'=>'error','message'=>'Email, username, dan password wajib diisi.']); }
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) { json(['status'=>'error','message'=>'Format email tidak valid.']); }
    if(strlen($u)<4) { json(['status'=>'error','message'=>'Username minimal 4 karakter.']); }
    if(preg_match('/\s/',$u)) { json(['status'=>'error','message'=>'Username tidak boleh mengandung spasi.']); }
    if(strlen($p)<6) { json(['status'=>'error','message'=>'Password minimal 6 karakter.']); }
    if($confirm&&$p!==$confirm) { json(['status'=>'error','message'=>'Konfirmasi password tidak cocok.']); }

    $db = DB::get();
    $stE=$db->prepare("SELECT id_penyewa FROM penyewa WHERE email=? LIMIT 1");
    $stE->bind_param('s',$email); $stE->execute();
    if($stE->get_result()->num_rows>0) { json(['status'=>'error','message'=>'Email sudah terdaftar.']); }

    $stU=$db->prepare("SELECT id_penyewa FROM penyewa WHERE username=? LIMIT 1");
    $stU->bind_param('s',$u); $stU->execute();
    if($stU->get_result()->num_rows>0) { json(['status'=>'error','message'=>'Username sudah digunakan.']); }

    $hash=password_hash($p,PASSWORD_BCRYPT);
    $nama=ucwords(str_replace(['_','.','-'],' ',$u));
    $st2=$db->prepare("INSERT INTO penyewa(nama_lengkap,username,email,password) VALUES(?,?,?,?)");
    $st2->bind_param('ssss',$nama,$u,$email,$hash);
    if($st2->execute()) { json(['status'=>'success','message'=>'Akun berhasil dibuat! Silakan masuk.','redirect'=>APP_URL.'/pages/auth/login.php']); }
    else { json(['status'=>'error','message'=>'Gagal mendaftar, coba lagi.']); }
}

function doLogout(): void {
    $isAdmin=isAdmin();
    session_destroy();
    json(['status'=>'success','redirect'=>$isAdmin?APP_URL.'/pages/auth/login.php':APP_URL.'/index.php']);
}
