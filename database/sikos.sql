-- ============================================================
-- SIKOS - Sistem Informasi Manajemen Pemesanan Kamar Kos
-- Database Schema v2.0
-- Default passwords: admin123 / penyewa123
-- ============================================================

CREATE DATABASE IF NOT EXISTS sikos_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sikos_db;

CREATE TABLE IF NOT EXISTS admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nama_admin VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS penyewa (
    id_penyewa INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100),
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20),
    nik VARCHAR(20),
    pekerjaan VARCHAR(100),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fasilitas (
    id_fasilitas INT AUTO_INCREMENT PRIMARY KEY,
    nama_fasilitas VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kamar (
    id_kamar INT AUTO_INCREMENT PRIMARY KEY,
    id_admin INT NOT NULL,
    nomor_kamar VARCHAR(20) NOT NULL UNIQUE,
    tipe VARCHAR(50) NOT NULL DEFAULT 'Standar',
    lantai INT NOT NULL DEFAULT 1,
    harga_per_bulan DECIMAL(12,2) NOT NULL,
    status_ketersediaan ENUM('Tersedia','Terisi','Perbaikan') NOT NULL DEFAULT 'Tersedia',
    deskripsi TEXT,
    foto VARCHAR(255) DEFAULT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_admin) REFERENCES admin(id_admin) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kamar_fasilitas (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_kamar INT NOT NULL,
    id_fasilitas INT NOT NULL,
    UNIQUE KEY uq_kf (id_kamar, id_fasilitas),
    FOREIGN KEY (id_kamar) REFERENCES kamar(id_kamar) ON DELETE CASCADE,
    FOREIGN KEY (id_fasilitas) REFERENCES fasilitas(id_fasilitas) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS booking (
    id_booking INT AUTO_INCREMENT PRIMARY KEY,
    kode_booking VARCHAR(30) NOT NULL UNIQUE,
    id_penyewa INT NOT NULL,
    id_kamar INT NOT NULL,
    nama_penyewa VARCHAR(100),
    no_hp_penyewa VARCHAR(20),
    email_penyewa VARCHAR(100),
    pekerjaan VARCHAR(100),
    alamat_asal TEXT,
    tanggal_pemesanan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_mulai DATE NOT NULL,
    durasi_bulan INT NOT NULL DEFAULT 1,
    tanggal_selesai DATE NOT NULL,
    total_harga DECIMAL(12,2) NOT NULL,
    metode_pembayaran ENUM('Transfer BRI','Transfer BNI','GoPay') DEFAULT 'Transfer BRI',
    status ENUM('Pending','Aktif','Ditolak','Selesai','Dibatalkan') NOT NULL DEFAULT 'Pending',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_penyewa) REFERENCES penyewa(id_penyewa) ON DELETE CASCADE,
    FOREIGN KEY (id_kamar) REFERENCES kamar(id_kamar) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pembayaran (
    id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
    id_booking INT NOT NULL UNIQUE,
    nominal DECIMAL(12,2) NOT NULL,
    bukti_pembayaran VARCHAR(255) DEFAULT NULL,
    status_pembayaran ENUM('Menunggu','Proses Validasi','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
    waktu_upload TIMESTAMP NULL,
    waktu_verifikasi TIMESTAMP NULL,
    catatan_admin TEXT,
    FOREIGN KEY (id_booking) REFERENCES booking(id_booking) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS review (
    id_review INT AUTO_INCREMENT PRIMARY KEY,
    id_penyewa INT NOT NULL,
    id_kamar INT NOT NULL,
    id_booking INT,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    komentar TEXT NOT NULL,
    status_tayang ENUM('Tayang','Disembunyikan') DEFAULT 'Tayang',
    tanggal_review TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_penyewa) REFERENCES penyewa(id_penyewa) ON DELETE CASCADE,
    FOREIGN KEY (id_kamar) REFERENCES kamar(id_kamar) ON DELETE CASCADE,
    FOREIGN KEY (id_booking) REFERENCES booking(id_booking) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin: username=aininurfadhilah password=admin123
INSERT INTO admin (nama_admin, username, email, password, no_hp) VALUES
('Aini Nurfadhilah','aininurfadhilah','aininurfadhilah@gmail.com',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','08123456789');

INSERT INTO fasilitas (nama_fasilitas) VALUES
('Wifi'),('Kamar Mandi Dalam'),('Lemari'),('Kasur'),('Meja Belajar'),
('AC'),('CCTV'),('Parkir Motor'),('Parkir Mobil'),('Dapur Bersama'),
('Laundry'),('Air Panas'),('Kulkas'),('TV');

INSERT INTO kamar (id_admin,nomor_kamar,tipe,lantai,harga_per_bulan,status_ketersediaan,deskripsi) VALUES
(1,'A1','Standar',1,600000,'Terisi','Kamar standar nyaman dengan fasilitas dasar yang lengkap. Cocok untuk mahasiswa.'),
(1,'A2','Standar',1,700000,'Tersedia','Kamar standar dengan kamar mandi dalam. Ventilasi udara baik.'),
(1,'A3','Standar',1,600000,'Tersedia','Kamar standar menghadap taman. Tenang dan nyaman.'),
(1,'B1','Premier',2,2500000,'Terisi','Kamar premier dengan fasilitas premium lengkap termasuk AC dan lemari besar.'),
(1,'B2','Premier',2,2500000,'Tersedia','Kamar premier lantai 2 dengan pemandangan indah.'),
(1,'C1','Deluxe',3,3000000,'Tersedia','Kamar deluxe terluas dengan semua fasilitas premium. Best choice!'),
(1,'D2','Deluxe',3,3000000,'Tersedia','Kamar deluxe dengan balkon pribadi dan pemandangan kota.');

INSERT INTO kamar_fasilitas (id_kamar,id_fasilitas) VALUES
(1,1),(1,4),(1,3),(1,7),(1,8),
(2,1),(2,2),(2,3),(2,4),(2,7),(2,8),
(3,1),(3,4),(3,3),(3,7),
(4,1),(4,2),(4,3),(4,4),(4,5),(4,6),(4,7),(4,8),(4,10),
(5,1),(5,2),(5,3),(5,4),(5,5),(5,6),(5,7),(5,8),
(6,1),(6,2),(6,3),(6,4),(6,5),(6,6),(6,7),(6,8),(6,9),(6,10),(6,11),(6,12),
(7,1),(7,2),(7,3),(7,4),(7,5),(7,6),(7,7),(7,8),(7,9),(7,10),(7,11),(7,12);

-- Penyewa: username=rinanatalia_ password=penyewa123
INSERT INTO penyewa (nama_lengkap,username,email,password,no_hp,nik) VALUES
('Rina Natalia','rinanatalia_','nataliarina911@gmail.com',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','08123456789','3201010101010001'),
('Budi Santoso','budi123','budi@gmail.com',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','08198765432','3201010101010002');

INSERT INTO booking (kode_booking,id_penyewa,id_kamar,nama_penyewa,no_hp_penyewa,email_penyewa,tanggal_mulai,durasi_bulan,tanggal_selesai,total_harga,status) VALUES
('SKS-20250701-A59001',1,1,'Rina Natalia','08123456789','nataliarina911@gmail.com','2025-07-01',12,'2026-07-01',7200000,'Aktif');

INSERT INTO pembayaran (id_booking,nominal,status_pembayaran,waktu_verifikasi) VALUES
(1,7200000,'Disetujui','2025-06-30 10:00:00');

INSERT INTO review (id_penyewa,id_kamar,id_booking,rating,komentar) VALUES
(1,1,1,5,'Kamar bersih dan AC dingin. Lokasi sangat strategis!'),
(2,4,NULL,5,'Kamar bersih dan AC dingin. Lokasi sangat strategis!'),
(1,2,NULL,5,'Kamar bersih dan AC dingin. Lokasi sangat strategis!'),
(2,3,NULL,4,'Fasilitas lengkap, lingkungan aman dan nyaman.');

-- ============================================================
-- PATCH v2.1 - Auto-applied
-- ============================================================

-- Tabel perpanjangan sewa
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

-- Kolom alasan_penolakan di pembayaran
ALTER TABLE pembayaran ADD COLUMN IF NOT EXISTS alasan_penolakan TEXT DEFAULT NULL AFTER catatan_admin;
