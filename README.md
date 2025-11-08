# 📚 Sistem Absensi Sekolah XYZ

Sistem Absensi Digital Berbasis Web dengan Fitur Kamera & GPS

![Theme](https://img.shields.io/badge/Theme-Yellow%20%26%20Black-FFD700)
![PHP](https://img.shields.io/badge/PHP-7.4+-blue)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple)

---

## 🎯 Fitur Utama

### 👨‍💼 Admin
- ✅ Dashboard statistik lengkap
- ✅ CRUD Data Siswa (dengan profil lengkap)
- ✅ CRUD Data Guru
- ✅ Monitoring seluruh absensi
- ✅ Export data ke Excel
- ✅ Reset data absensi
- ✅ Backup database

### 👨‍🏫 Guru
- ✅ Dashboard monitoring real-time
- ✅ Lihat daftar absensi siswa
- ✅ Filter berdasarkan tanggal & kelas
- ✅ Export absensi ke Excel
- ✅ Lihat foto & lokasi GPS absensi

### 👨‍🎓 Siswa
- ✅ Dashboard profil lengkap (alamat, data wali, dll)
- ✅ Absensi masuk dengan kamera & GPS
- ✅ Absensi pulang dengan kamera & GPS
- ✅ Riwayat absensi pribadi
- ✅ Statistik kehadiran

---

## 🚀 Cara Instalasi

### 1. Persiapan
- Install **XAMPP** (PHP 7.4+ & MySQL)
- Download atau clone repository ini

### 2. Database
```sql
1. Buka phpMyAdmin (http://localhost/phpmyadmin)
2. Buat database baru bernama: db_absensi
3. Import file: db_absensi.sql
```

### 3. Konfigurasi
Buka file `includes/config.php` dan sesuaikan jika perlu:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_absensi');
```

### 4. Folder Upload
Buat folder `uploads` di root directory dengan permission write:
```
/absensi_sekolah/uploads/
```

### 5. Jalankan Aplikasi
```
http://localhost/absensi_sekolah/
```

---

## 👥 Akun Default

### Admin
- **Username:** admin
- **Password:** password

### Guru
- **Username:** guru1
- **Password:** password

### Siswa
- **Username:** siswa1
- **Password:** password

---

## 📁 Struktur Folder

```
absensi_sekolah/
│
├── admin/                  # Panel Admin
│   ├── includes/          # Header, Footer, Sidebar
│   ├── dashboard.php
│   ├── data_siswa.php
│   ├── data_guru.php
│   ├── data_absensi.php
│   ├── delete_all_absensi.php
│   ├── backup.php
│   └── logout.php
│
├── guru/                   # Panel Guru
│   ├── dashboard.php
│   ├── lihat_absensi.php
│   └── logout.php
│
├── siswa/                  # Panel Siswa
│   ├── dashboard.php
│   ├── absen.php
│   ├── riwayat.php
│   └── logout.php
│
├── includes/               # File Konfigurasi
│   └── config.php
│
├── uploads/                # Penyimpanan Foto Absensi
│
├── index.php              # Landing Page
├── login_admin.php
├── login_guru.php
├── login_siswa.php
├── db_absensi.sql         # Database Export
└── README.md
```

---

## 🎨 Fitur Teknologi

### Frontend
- **Bootstrap 5.3** - UI Framework
- **Font Awesome 6.4** - Icons
- **DataTables** - Tabel interaktif
- **SweetAlert2** - Alert modern

### Backend
- **PHP 7.4+** - Server-side scripting
- **MySQL** - Database
- **Prepared Statements** - Security SQL Injection

### Fitur Khusus
- **HTML5 Camera API** - Akses kamera untuk selfie
- **Geolocation API** - Deteksi lokasi GPS
- **Base64 Encoding** - Simpan foto
- **Session Management** - Keamanan login

---

## 📸 Fitur Absensi

### Absensi Masuk
1. Siswa login ke sistem
2. Klik menu "Absensi"
3. Izinkan akses kamera & lokasi
4. Sistem otomatis mendeteksi GPS
5. Ambil foto selfie
6. Kirim absensi

### Validasi
- ✅ Siswa hanya bisa absen 1x per hari
- ✅ Deteksi lokasi dalam radius sekolah
- ✅ Foto wajib diambil
- ✅ GPS wajib aktif

### Data Tersimpan
- Tanggal & waktu absensi
- Foto selfie (masuk & pulang)
- Koordinat GPS
- Status kehadiran

---

## 🗃️ Database Schema

### Tabel: `admin`
```sql
- id_admin (PK)
- username
- password
- nama_admin
```

### Tabel: `guru`
```sql
- id_guru (PK)
- nama_guru
- username
- password
- mapel
```

### Tabel: `siswa`
```sql
- id_siswa (PK)
- nama_siswa
- nis
- kelas
- username
- password
- jenis_kelamin
- tempat_lahir
- tanggal_lahir
- alamat
- no_telp
- email
- nama_wali
- no_telp_wali
- foto_profil
```

### Tabel: `absensi_lengkap`
```sql
- id_absensi (PK)
- id_siswa (FK)
- tanggal
- jam_masuk
- jam_pulang
- foto_masuk
- foto_pulang
- lokasi (latitude,longitude)
- keterangan
- status (Hadir/Izin/Sakit/Alfa)
```

---

## 🔒 Keamanan

- ✅ Password di-hash dengan `password_hash()`
- ✅ Prepared statements untuk SQL
- ✅ Session-based authentication
- ✅ Input validation & sanitization
- ✅ Role-based access control

---

## 📱 Responsive Design

Website ini fully responsive dan dapat diakses dari:
- 💻 Desktop
- 📱 Tablet
- 📱 Mobile Phone

---

## 🎯 Konfigurasi GPS

Koordinat sekolah dapat diatur di `includes/config.php`:
```php
define('SCHOOL_LAT', -6.200000);  // Latitude
define('SCHOOL_LNG', 106.816666); // Longitude
define('MAX_DISTANCE', 0.5);      // Radius dalam KM
```

---

## 📊 Export Data

### Format Excel
- Admin & Guru dapat export data absensi
- Format: `.xls`
- Include: NIS, Nama, Kelas, Tanggal, Jam, Status

### Backup Database
- Admin dapat backup full database
- Format: `.sql`
- Include: Semua tabel & data

---

## 🐛 Troubleshooting

### Kamera Tidak Berfungsi
- Pastikan browser mendukung `getUserMedia()`
- Izinkan akses kamera di browser
- Gunakan HTTPS (untuk production)

### GPS Tidak Terdeteksi
- Izinkan akses lokasi di browser
- Pastikan GPS device aktif
- Gunakan HTTPS (untuk production)

### Error Upload Foto
- Cek permission folder `uploads/`
- Pastikan folder ada dan writable
- Cek PHP `upload_max_filesize`

### Error Database
- Pastikan MySQL service running
- Cek koneksi di `config.php`
- Import ulang `db_absensi.sql`

---

## 📞 Support

Untuk bantuan atau pertanyaan:
- 📧 Email: support@sekolahxyz.com
- 🌐 Website: www.sekolahxyz.com

---

## 📝 Changelog

### Version 1.0.0 (2024)
- ✅ Initial release
- ✅ Login 3 role (Admin, Guru, Siswa)
- ✅ Absensi dengan Kamera & GPS
- ✅ CRUD lengkap
- ✅ Export Excel
- ✅ Backup Database

---

## 📄 License

Copyright © 2024 Sistem Absensi Sekolah XYZ
All rights reserved.

---

## 🙏 Credits

- **Bootstrap** - UI Framework
- **Font Awesome** - Icons
- **DataTables** - Interactive Tables
- **SweetAlert2** - Beautiful Alerts

---
