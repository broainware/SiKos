-- ============================================================
-- SIKOS Database Patch v2.1
-- Jalankan setelah sikos.sql diimport jika perlu update
-- ============================================================
USE sikos_db;

-- Tabel perpanjangan (jika belum ada)
CREATE TABLE IF NOT EXISTS perpanjangan (
    id_perpanjangan INT AUTO_INCREMENT PRIMARY KEY,
    id_booking INT NOT NULL,
    id_penyewa INT NOT NULL,
    id_kamar INT NOT NULL,
    durasi_tambah INT NOT NULL DEFAULT 1,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    total_harga DECIMAL(12,2) NOT NULL,
    metode_pembayaran ENUM('Transfer BRI','Transfer BNI','GoPay') DEFAULT 'Transfer BRI',
    bukti_pembayaran VARCHAR(255) DEFAULT NULL,
    status ENUM('Menunggu','Proses Validasi','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
    catatan_admin TEXT,
    alasan_penolakan TEXT,
    waktu_upload TIMESTAMP NULL,
    waktu_verifikasi TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_booking) REFERENCES booking(id_booking) ON DELETE CASCADE,
    FOREIGN KEY (id_penyewa) REFERENCES penyewa(id_penyewa) ON DELETE CASCADE,
    FOREIGN KEY (id_kamar) REFERENCES kamar(id_kamar) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tambah kolom alasan_penolakan ke pembayaran (jalankan manual jika error):
-- ALTER TABLE pembayaran ADD COLUMN alasan_penolakan TEXT DEFAULT NULL AFTER catatan_admin;

-- Update email penyewa agar tidak NULL
ALTER TABLE penyewa MODIFY COLUMN email VARCHAR(100) DEFAULT NULL;
