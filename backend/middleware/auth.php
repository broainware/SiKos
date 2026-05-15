<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function isAdmin(): bool { return !empty($_SESSION['admin_id']); }
function isPenyewa(): bool { return !empty($_SESSION['penyewa_id']); }

function requireAdmin(): void {
    if (!isAdmin()) { header('Location: ' . APP_URL . '/pages/auth/login.php'); exit; }
}
function requirePenyewa(): void {
    if (!isPenyewa()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . APP_URL . '/pages/auth/login.php'); exit;
    }
}
function isAjax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}
function json(array $d, int $c = 200): void {
    http_response_code($c);
    header('Content-Type: application/json');
    echo json_encode($d); exit;
}
function clean(string $s): string {
    return htmlspecialchars(strip_tags(trim($s)), ENT_QUOTES, 'UTF-8');
}
function fmtRp(float $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}
function genKode(): string {
    return 'SKS-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(mt_rand(),true)),0,6));
}
function validateUpload(array $f): array {
    if ($f['error'] !== 0) return ['ok'=>false,'msg'=>'Error upload file.'];
    if ($f['size'] > 5*1024*1024) return ['ok'=>false,'msg'=>'Ukuran file maks 5MB.'];
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext,['jpg','jpeg','png','pdf'])) return ['ok'=>false,'msg'=>'Format: JPG, PNG, PDF.'];
    return ['ok'=>true,'ext'=>$ext];
}
function getAdmin(): array {
    return ['id'=>$_SESSION['admin_id']??0,'nama'=>$_SESSION['admin_nama']??'','username'=>$_SESSION['admin_username']??'','email'=>$_SESSION['admin_email']??'','no_hp'=>$_SESSION['admin_no_hp']??''];
}
function getPenyewa(): array {
    return ['id'=>$_SESSION['penyewa_id']??0,'nama'=>$_SESSION['penyewa_nama']??'','username'=>$_SESSION['penyewa_username']??'','email'=>$_SESSION['penyewa_email']??'','no_hp'=>$_SESSION['penyewa_no_hp']??''];
}
