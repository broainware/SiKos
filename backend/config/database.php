<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sikos_db');
define('APP_URL', 'http://localhost/sikos');
define('UPLOAD_BAYAR', __DIR__ . '/../../public/uploads/pembayaran/');
define('UPLOAD_KAMAR', __DIR__ . '/../../public/uploads/kamar/');
define('UPLOAD_URL_BAYAR', APP_URL . '/public/uploads/pembayaran/');
define('UPLOAD_URL_KAMAR', APP_URL . '/public/uploads/kamar/');

class DB {
    private static $conn = null;
    public static function get(): mysqli {
        if (!self::$conn) {
            self::$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if (self::$conn->connect_error) die('DB Error: ' . self::$conn->connect_error);
            self::$conn->set_charset('utf8mb4');
        }
        return self::$conn;
    }
    public static function q(string $sql) { return self::get()->query($sql); }
    public static function prep(string $sql) { return self::get()->prepare($sql); }
    public static function esc(string $s): string { return self::get()->real_escape_string($s); }
    public static function lastId(): int { return self::get()->insert_id; }
}
