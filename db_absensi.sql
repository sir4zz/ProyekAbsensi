-- Database: db_absensi
CREATE DATABASE IF NOT EXISTS db_absensi;
USE db_absensi;

-- Tabel Admin
CREATE TABLE admin (
    id_admin INT(11) PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_admin VARCHAR(100) NOT NULL
);

-- Tabel Guru
CREATE TABLE guru (
    id_guru INT(11) PRIMARY KEY AUTO_INCREMENT,
    nama_guru VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    mapel VARCHAR(50) NOT NULL
);

-- Tabel Siswa (Lengkap)
CREATE TABLE siswa (
    id_siswa INT(11) PRIMARY KEY AUTO_INCREMENT,
    nama_siswa VARCHAR(100) NOT NULL,
    nis VARCHAR(20) NOT NULL UNIQUE,
    kelas VARCHAR(20) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    tempat_lahir VARCHAR(50),
    tanggal_lahir DATE,
    alamat TEXT,
    no_telp VARCHAR(15),
    email VARCHAR(50),
    nama_wali VARCHAR(100),
    no_telp_wali VARCHAR(15),
    foto_profil VARCHAR(255) DEFAULT 'default.jpg'
);

-- Tabel Absensi Lengkap
CREATE TABLE absensi_lengkap (
    id_absensi INT(11) PRIMARY KEY AUTO_INCREMENT,
    id_siswa INT(11) NOT NULL,
    tanggal DATE NOT NULL,
    jam_masuk TIME DEFAULT NULL,
    jam_pulang TIME DEFAULT NULL,
    foto_masuk VARCHAR(255) DEFAULT NULL,
    foto_pulang VARCHAR(255) DEFAULT NULL,
    lokasi VARCHAR(100) DEFAULT NULL,
    keterangan TEXT,
    status ENUM('Hadir', 'Izin', 'Sakit', 'Alfa') DEFAULT 'Hadir',
    FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa) ON DELETE CASCADE
);

-- Insert Data Dummy Admin
INSERT INTO admin (username, password, nama_admin) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator'),
('admin2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Sekolah');

-- Insert Data Dummy Guru
INSERT INTO guru (nama_guru, username, password, mapel) VALUES
('Budi Santoso, S.Pd', 'guru1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Matematika'),
('Siti Nurhaliza, S.Pd', 'guru2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bahasa Indonesia'),
('Ahmad Dahlan, S.Pd', 'guru3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bahasa Inggris');

-- Insert Data Dummy Siswa
INSERT INTO siswa (nama_siswa, nis, kelas, username, password, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, no_telp, email, nama_wali, no_telp_wali) VALUES
('Andi Pratama', '2024001', 'X-1', 'siswa1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L', 'Jakarta', '2008-05-15', 'Jl. Merdeka No. 123, Jakarta Pusat', '08123456789', 'andi@email.com', 'Bapak Pratama', '08198765432'),
('Sari Dewi', '2024002', 'X-1', 'siswa2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'P', 'Bandung', '2008-08-20', 'Jl. Sudirman No. 45, Bandung', '08234567890', 'sari@email.com', 'Ibu Dewi', '08187654321'),
('Budi Santoso', '2024003', 'X-2', 'siswa3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L', 'Surabaya', '2008-03-10', 'Jl. Pahlawan No. 78, Surabaya', '08345678901', 'budi@email.com', 'Bapak Santoso', '08176543210'),
('Rina Wulandari', '2024004', 'X-2', 'siswa4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'P', 'Semarang', '2008-11-25', 'Jl. Ahmad Yani No. 90, Semarang', '08456789012', 'rina@email.com', 'Ibu Wulandari', '08165432109'),
('Dimas Prasetyo', '2024005', 'XI-1', 'siswa5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L', 'Yogyakarta', '2007-07-07', 'Jl. Malioboro No. 12, Yogyakarta', '08567890123', 'dimas@email.com', 'Bapak Prasetyo', '08154321098');

-- Insert Data Dummy Absensi
INSERT INTO absensi_lengkap (id_siswa, tanggal, jam_masuk, jam_pulang, lokasi, keterangan, status) VALUES
(1, '2024-11-01', '07:15:00', '14:30:00', '-6.200000,106.816666', 'Hadir tepat waktu', 'Hadir'),
(2, '2024-11-01', '07:20:00', '14:35:00', '-6.914744,107.609810', 'Hadir', 'Hadir'),
(3, '2024-11-01', NULL, NULL, NULL, 'Sakit demam', 'Sakit'),
(1, '2024-11-02', '07:10:00', '14:25:00', '-6.200000,106.816666', 'Hadir', 'Hadir'),
(4, '2024-11-02', '07:25:00', '14:40:00', '-6.966667,110.416664', 'Hadir', 'Hadir');

-- Note: Password default untuk semua user adalah "password"
-- Gunakan password_hash() di PHP untuk enkripsi yang lebih baik