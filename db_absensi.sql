-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 09 Nov 2025 pada 15.11
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_absensi`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi_lengkap`
--

CREATE TABLE `absensi_lengkap` (
  `id_absensi` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_pulang` time DEFAULT NULL,
  `foto_masuk` varchar(255) DEFAULT NULL,
  `foto_pulang` varchar(255) DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `status` enum('Hadir','Izin','Sakit','Alfa') DEFAULT 'Hadir'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `absensi_lengkap`
--

INSERT INTO `absensi_lengkap` (`id_absensi`, `id_siswa`, `tanggal`, `jam_masuk`, `jam_pulang`, `foto_masuk`, `foto_pulang`, `lokasi`, `keterangan`, `status`) VALUES
(1, 1, '2024-11-01', '07:15:00', '14:30:00', NULL, NULL, '-6.200000,106.816666', 'Hadir tepat waktu', 'Hadir'),
(2, 2, '2024-11-01', '07:20:00', '14:35:00', NULL, NULL, '-6.914744,107.609810', 'Hadir', 'Hadir'),
(3, 3, '2024-11-01', NULL, NULL, NULL, NULL, NULL, 'Sakit demam', 'Sakit'),
(4, 1, '2024-11-02', '07:10:00', '14:25:00', NULL, NULL, '-6.200000,106.816666', 'Hadir', 'Hadir'),
(5, 4, '2024-11-02', '07:25:00', '14:40:00', NULL, NULL, '-6.966667,110.416664', 'Hadir', 'Hadir'),
(6, 1, '2025-11-07', '21:12:04', '21:19:17', 'absen_1_20251107211204.png', 'pulang_1_20251107211917.png', '-6.2455808,106.4173568', NULL, 'Hadir'),
(7, 2, '2025-11-08', '15:25:18', NULL, 'absen_2_20251108152518.png', NULL, '-6.2390272,106.4173568', '', ''),
(8, 2, '2025-11-08', '15:38:06', NULL, 'absen_2_20251108153806.png', NULL, '-6.2390272,106.4173568', '', ''),
(9, 2, '2025-11-08', '15:38:09', NULL, 'absen_2_20251108153809.png', NULL, '-6.2390272,106.4173568', '', ''),
(10, 3, '2025-11-08', '15:42:36', NULL, 'absen_3_20251108154236.png', NULL, '-6.2390272,106.4173568', 'main ps', 'Izin'),
(11, 3, '2025-11-09', '20:14:31', NULL, 'absen_3_20251109201431.png', NULL, '-6.3275008,106.6500096', 'puyeng', 'Sakit');

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_admin` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`, `nama_admin`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator'),
(2, 'admin2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Sekolah');

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru`
--

CREATE TABLE `guru` (
  `id_guru` int(11) NOT NULL,
  `nama_guru` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `mapel` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `guru`
--

INSERT INTO `guru` (`id_guru`, `nama_guru`, `username`, `password`, `mapel`) VALUES
(1, 'Budi Santoso, S.Pd', 'guru1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Matematika'),
(2, 'Siti Nurhaliza, S.Pd', 'guru2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bahasa Indonesia'),
(3, 'Ahmad Dahlan, S.Pd', 'guru3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bahasa Inggris'),
(4, 'Akbar S.D. S.M.P S.M.K.', 'king', '$2y$10$AzvBxRx72LaBMYWV5QkJKOIos..V6gdBpjOLkCFaay0X42H1DdzHe', 'Coding');

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int(11) NOT NULL,
  `nama_siswa` varchar(100) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `kelas` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tempat_lahir` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_telp` varchar(15) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `nama_wali` varchar(100) DEFAULT NULL,
  `no_telp_wali` varchar(15) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT 'default.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `nama_siswa`, `nis`, `kelas`, `username`, `password`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `alamat`, `no_telp`, `email`, `nama_wali`, `no_telp_wali`, `foto_profil`) VALUES
(1, 'Andi Pratama', '2024001', 'X-1', 'siswa1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L', 'Jakarta', '2008-05-15', 'Jl. Merdeka No. 123, Jakarta Pusat', '08123456789', 'andi@email.com', 'Bapak Pratama', '08198765432', 'default.jpg'),
(2, 'Sari Dewi', '2024002', 'X-1', 'siswa2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'P', 'Bandung', '2008-08-20', 'Jl. Sudirman No. 45, Bandung', '08234567890', 'sari@email.com', 'Ibu Dewi', '08187654321', 'default.jpg'),
(3, 'Budi Santoso', '2024003', 'X-2', 'siswa3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L', 'Surabaya', '2008-03-10', 'Jl. Pahlawan No. 78, Surabaya', '08345678901', 'budi@email.com', 'Bapak Santoso', '08176543210', 'default.jpg'),
(4, 'Rina Wulandari', '2024004', 'X-2', 'siswa4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'P', 'Semarang', '2008-11-25', 'Jl. Ahmad Yani No. 90, Semarang', '08456789012', 'rina@email.com', 'Ibu Wulandari', '08165432109', 'default.jpg'),
(5, 'Dimas Prasetyo', '2024005', 'XI-1', 'siswa5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L', 'Yogyakarta', '2007-07-07', 'Jl. Malioboro No. 12, Yogyakarta', '08567890123', 'dimas@email.com', 'Bapak Prasetyo', '08154321098', 'default.jpg'),
(6, 'Muhamad Siraj Supriyanto', '44332211', 'XI-TKJ 1', 'atmin', '$2y$10$hPpK11aMF.zRGyWFswO76uAAkfJBYpLWl4JPwCKK/c3rKCNpYYgMC', 'L', 'Tangerang', '1945-08-17', 'Indonesia', '087654321', 'siraj@gmail.com', '', '', 'default.jpg');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absensi_lengkap`
--
ALTER TABLE `absensi_lengkap`
  ADD PRIMARY KEY (`id_absensi`),
  ADD KEY `id_siswa` (`id_siswa`);

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id_guru`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi_lengkap`
--
ALTER TABLE `absensi_lengkap`
  MODIFY `id_absensi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `guru`
--
ALTER TABLE `guru`
  MODIFY `id_guru` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi_lengkap`
--
ALTER TABLE `absensi_lengkap`
  ADD CONSTRAINT `absensi_lengkap_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
