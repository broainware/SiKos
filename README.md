# SIKOS — Sistem Informasi Manajemen Pemesanan Kamar Kos
## Panduan Instalasi & Penggunaan

---

## 🖥️ Teknologi
- **Frontend:** HTML5, CSS3, JavaScript (AJAX/Fetch)
- **Backend:** PHP 8.x (Native/MVC sederhana)
- **Database:** MySQL 8.x
- **Server:** Apache (Laragon/XAMPP)
- **CDN:** Font Awesome 6, Google Fonts (Inter, Poppins)

---

## 📁 Struktur Folder
```
sikos/
├── .htaccess                  ← Konfigurasi Apache
├── index.php                  ← Landing Page
├── generate-placeholder.php   ← Helper (jalankan sekali)
├── database/
│   └── sikos.sql             ← Schema + Seed data
├── backend/
│   ├── config/
│   │   └── database.php      ← Koneksi DB + konstanta
│   ├── middleware/
│   │   └── auth.php          ← Session, validasi, helper
│   └── controllers/
│       ├── auth.php          ← Login/Logout/Register
│       ├── kamar.php         ← CRUD Kamar
│       ├── booking.php       ← Booking + anti-double
│       ├── pembayaran.php    ← Upload + verifikasi
│       ├── review.php        ← Review CRUD
│       ├── calendar.php      ← Smart Calendar data
│       ├── admin.php         ← Stats + profil admin
│       └── penyewa.php       ← Profil penyewa
├── api/
│   └── index.php             ← Router semua API endpoint
├── pages/
│   ├── partials/
│   │   ├── header.php        ← Navbar global
│   │   └── footer.php        ← Footer global
│   ├── auth/
│   │   ├── login.php         ← Halaman Masuk
│   │   ├── register.php      ← Halaman Daftar
│   │   └── lupa-sandi.php    ← Lupa Sandi
│   ├── admin/
│   │   ├── partials/
│   │   │   ├── sidebar.php   ← Sidebar admin
│   │   │   └── profil-modal.php
│   │   ├── dashboard.php     ← Dashboard Admin
│   │   ├── data-kamar.php    ← Manajemen Kamar
│   │   ├── data-reservasi.php← Manajemen Reservasi
│   │   ├── verifikasi.php    ← Verifikasi Pembayaran
│   │   ├── kalender.php      ← Kalender Master
│   │   └── review.php        ← Manajemen Review
│   ├── user/
│   │   ├── dashboard.php     ← Dashboard User
│   │   ├── booking.php       ← Form Booking 4-step
│   │   ├── verifikasi.php    ← Cek & Upload Pembayaran
│   │   └── kalender.php      ← Kalender Ketersediaan
│   ├── kamar.php             ← Katalog Kamar publik
│   ├── review.php            ← Halaman Review publik
│   ├── tentang.php           ← Halaman Tentang & Tim
│   └── kontak.php            ← Halaman Kontak + Map
└── public/
    ├── css/
    │   └── style.css         ← Design system lengkap
    ├── js/
    │   └── main.js           ← AJAX helper + SmartCalendar
    ├── images/
    │   └── hero-bg.jpg       ← Background hero
    └── uploads/
        ├── pembayaran/       ← Bukti pembayaran
        └── kamar/            ← Foto kamar
```

---

## 🚀 Cara Instalasi

### 1. Persyaratan
- Laragon 6.x / XAMPP 8.x
- PHP 8.0+
- MySQL 8.0+
- Apache 2.4+

### 2. Clone / Copy Project
```bash
# Copy folder sikos ke:
C:\laragon\www\sikos\         # Laragon
# atau
C:\xampp\htdocs\sikos\        # XAMPP
```

### 3. Import Database
1. Buka **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Klik **New** → buat database baru: `sikos_db`
3. Klik **Import** → pilih file `database/sikos.sql`
4. Klik **Go**

### 4. Konfigurasi (opsional)
Edit `backend/config/database.php` jika konfigurasi berbeda:
```php
define('DB_HOST', 'localhost');   // Host MySQL
define('DB_USER', 'root');        // Username
define('DB_PASS', '');            // Password (kosong untuk Laragon)
define('DB_NAME', 'sikos_db');    // Nama database
define('APP_URL', 'http://localhost/sikos'); // URL aplikasi
```

### 5. Buat Folder Upload
Pastikan folder berikut ada dan writable:
```
public/uploads/pembayaran/
public/uploads/kamar/
public/images/
```

### 6. Generate Placeholder Image
Akses sekali di browser:
```
http://localhost/sikos/generate-placeholder.php
```

### 7. Jalankan
Buka browser:
```
http://localhost/sikos/
```

---

## 🔑 Akun Default

| Role    | Username         | Password    |
|---------|-----------------|-------------|
| Admin   | `budisusanto` | `admin123`  |
| Penyewa | `rinanatalia`   | `rina123`|

---

## 🌐 Halaman & URL

### Publik
| Halaman | URL |
|---------|-----|
| Landing Page | `/sikos/` |
| Katalog Kamar | `/sikos/pages/kamar.php` |
| Review | `/sikos/pages/review.php` |
| Tentang | `/sikos/pages/tentang.php` |
| Kontak | `/sikos/pages/kontak.php` |
| Login | `/sikos/pages/auth/login.php` |
| Daftar | `/sikos/pages/auth/register.php` |

### Admin
| Halaman | URL |
|---------|-----|
| Dashboard | `/sikos/pages/admin/dashboard.php` |
| Data Kamar | `/sikos/pages/admin/data-kamar.php` |
| Reservasi | `/sikos/pages/admin/data-reservasi.php` |
| Verifikasi | `/sikos/pages/admin/verifikasi.php` |
| Kalender Master | `/sikos/pages/admin/kalender.php` |
| Review | `/sikos/pages/admin/review.php` |

### Penyewa
| Halaman | URL |
|---------|-----|
| Dashboard | `/sikos/pages/user/dashboard.php` |
| Form Booking | `/sikos/pages/user/booking.php` |
| Verifikasi Transaksi | `/sikos/pages/user/verifikasi.php` |
| Kalender | `/sikos/pages/user/kalender.php` |

---

## 🔌 API Endpoints

Semua request ke: `http://localhost/sikos/api/index.php?action={action}`

| action | Method | Keterangan |
|--------|--------|------------|
| `login` | POST | Login admin/user |
| `register` | POST | Daftar penyewa |
| `logout` | POST | Keluar |
| `get_kamar` | GET | Daftar kamar |
| `get_kamar_detail` | GET | Detail kamar |
| `create_kamar` | POST | Tambah kamar (admin) |
| `update_kamar` | POST | Edit kamar (admin) |
| `delete_kamar` | POST | Hapus kamar (admin) |
| `get_fasilitas` | GET | Master fasilitas |
| `create_booking` | POST | Buat booking |
| `get_bookings` | GET | Daftar booking (admin) |
| `get_my_bookings` | GET | Booking saya (user) |
| `get_booking_detail` | GET | Detail booking |
| `update_booking` | POST | Update status |
| `delete_booking` | POST | Hapus booking |
| `cek_booking` | GET | Cek status via kode/HP |
| `upload_bukti` | POST | Upload bukti bayar |
| `verifikasi` | POST | Approve/reject (admin) |
| `get_pembayaran` | GET | Daftar pembayaran |
| `get_reviews` | GET | Daftar review |
| `create_review` | POST | Buat review |
| `toggle_review` | POST | Tampil/sembunyi review |
| `delete_review` | POST | Hapus review |
| `get_calendar` | GET | Data smart calendar |
| `get_stats` | GET | Statistik dashboard |

---

## 📋 Fitur Utama

### Penyewa
- ✅ Registrasi & Login
- ✅ Dashboard dengan status kamar aktif, sisa masa sewa, status bayar
- ✅ Katalog kamar dengan foto, fasilitas, harga
- ✅ Form Booking 4 langkah (Data Diri → Pilih Kamar → Kalender → Pembayaran)
- ✅ Smart Calendar real-time (Tersedia/Pending/Terisi)
- ✅ Anti double booking otomatis
- ✅ Upload bukti pembayaran (JPG/PNG/PDF max 5MB)
- ✅ Cek status booking via ID atau nomor HP
- ✅ Review setelah masa sewa selesai

### Admin
- ✅ Dashboard ringkasan (kamar, booking, statistik)
- ✅ CRUD kamar lengkap dengan foto & fasilitas M:N
- ✅ Manajemen reservasi (lihat, update status, hapus)
- ✅ Verifikasi pembayaran (Approve/Reject)
- ✅ Kalender Master semua kamar
- ✅ Manajemen review (tampil/sembunyikan/hapus)
- ✅ Profil admin

---

## 🔐 Keamanan
- Password di-hash dengan `password_hash()` (bcrypt)
- Session-based authentication per role
- File upload validation (MIME type + ekstensi)
- SQL Injection prevention (prepared statements)
- XSS prevention (`htmlspecialchars`)
- Upload folder dilindungi `.htaccess`

---

*SIKOS v2.0 — Aini Nurfadhilah, Rina Natalia, Anggi Salsabila M Y*
*Universitas Siliwangi — 2026*
