-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 12 Nov 2025 pada 15.53
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
(13, 17, '2025-11-12', '21:32:01', NULL, 'absen_17_20251112213201.png', NULL, '-6.2152494,106.3982467', 'main ps', 'Izin');

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
(4, 'Akbar S.D. S.M.P S.M.K.', 'king', '$2y$10$AzvBxRx72LaBMYWV5QkJKOIos..V6gdBpjOLkCFaay0X42H1DdzHe', 'Coding'),
(6, 'Budi Santoso', 'budi.s', '$2y$10$Tr7s6OoaEKAdPAosC3cMwOCPLg3h7VgzTLqYL5SeiyMsJolpIJ/Di', 'Matematika'),
(7, 'Siti Aminah', 'siti.a', '$2y$10$NK8QTfk6X.2hinKSzxt4NOBpV.HoBw.eV8RefLl4Fhha.xNA3KpFy', 'Bahasa Inggris'),
(8, 'Ahmad Fauzi', 'ahmad.f', '$2y$10$KqDUVHHdhCrxWp9i23dL2uthgaA3UwsChLEACD2GrIhaoD/s2j7Ai', 'Fisika'),
(9, 'Rina Marlina', 'rina.m', '$2y$10$WTu7.tZ9leSha/1CkXZOSuXMWlmwscSH1gqZh5HOseyB6Q87rNjRu', 'Biologi'),
(10, 'Dewi Lestari', 'dewi.l', '$2y$10$ffOJTqH3L9PJs2/d4yW/NubiZT83KotRP2GkyqmMR8juTdjT30WPe', 'Sejarah');

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru_kelas`
--

CREATE TABLE `guru_kelas` (
  `id` int(11) NOT NULL,
  `id_guru` int(11) NOT NULL,
  `kelas` varchar(20) NOT NULL,
  `jurusan` varchar(50) DEFAULT NULL,
  `tahun_ajaran` varchar(20) DEFAULT '2024/2025'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `guru_kelas`
--

INSERT INTO `guru_kelas` (`id`, `id_guru`, `kelas`, `jurusan`, `tahun_ajaran`) VALUES
(9, 7, 'XI-1', NULL, '2024/2025'),
(12, 9, 'XI-2', NULL, '2024/2025'),
(13, 10, 'X-3', NULL, '2024/2025'),
(14, 10, 'XI-3', NULL, '2024/2025'),
(15, 10, 'XII-3', NULL, '2024/2025'),
(16, 6, 'X-1', NULL, '2024/2025'),
(17, 6, 'X-2', NULL, '2024/2025'),
(18, 6, 'X-5', NULL, '2024/2025'),
(19, 8, 'X-1', NULL, '2024/2025'),
(20, 8, 'X-2', NULL, '2024/2025'),
(21, 8, 'X-3', NULL, '2024/2025');

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int(11) NOT NULL,
  `nama_siswa` varchar(100) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `kelas` varchar(20) NOT NULL,
  `jurusan` varchar(50) DEFAULT NULL,
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

INSERT INTO `siswa` (`id_siswa`, `nama_siswa`, `nis`, `kelas`, `jurusan`, `username`, `password`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `alamat`, `no_telp`, `email`, `nama_wali`, `no_telp_wali`, `foto_profil`) VALUES
(9, 'Ahmad Rizki', '1001', 'X-1', 'TKJ', 'ahmadrizki', '$2y$10$onnGBsZWfARm/R4xnIC8J.vMAw5DP8619EUuUnzB89btKfjXYqpmu', 'L', 'Bandung', '2008-03-15', 'Jl. Merdeka 1', '081234567890', 'ahmad.rizki@example.com', 'Budi', '081298765432', 'default.jpg'),
(10, 'Siti Aisyah', '1002', 'X-1', 'TKJ', 'sitiaisyah', '$2y$10$ZDCVZqC79.L5hJRleAiEeOEB.ARlWLUXIs.26/N63B7bVF9.GXGTO', 'P', 'Jakarta', '2008-07-20', 'Jl. Mawar 2', '081234567891', 'siti.aisyah@example.com', 'Rahman', '081298765433', 'default.jpg'),
(11, 'Dewi Lestari', '1003', 'X-2', 'RPL', 'dewiles', '$2y$10$E6SDFPkkOH86AKBXYYkZCe/X842Ao7BdpU4eiLbgybPwhh2S8LQVe', 'P', 'Bekasi', '2008-02-10', 'Jl. Melati 3', '081234567892', 'dewi.lestari@example.com', 'Slamet', '081298765434', 'default.jpg'),
(12, 'Rian Pratama', '1004', 'X-2', 'RPL', 'rianp', '$2y$10$7XAQ7xD/5wG56RfXe9dV4ejRY9sZn5jAASU8OUVvHgkvuMW9Cnes2', 'L', 'Bogor', '2008-11-05', 'Jl. Anggrek 4', '081234567893', 'rian.pratama@example.com', 'Darto', '081298765435', 'default.jpg'),
(13, 'Nabila Putri', '1005', 'X-3', 'TKJ', 'nabilap', '$2y$10$8sqLMaSkors0Smas48cC4ugwGRQeUkChr0/s0cX3W2234WQACcsRS', 'P', 'Depok', '2008-09-25', 'Jl. Dahlia 5', '081234567894', 'nabila.putri@example.com', 'Herman', '081298765436', 'default.jpg'),
(14, 'Rizky Maulana', '1006', 'X-3', 'RPL', 'rizkym', '$2y$10$ylu7DekcazIiK8alhyHXpeypCQJ9S012yzjmx49AvtYLR.f/yyQGi', 'L', 'Cirebon', '2008-06-13', 'Jl. Kenanga 6', '081234567895', 'rizky.maulana@example.com', 'Saiful', '081298765437', 'default.jpg'),
(15, 'Fauzan Hakim', '1007', 'X-4', 'TKJ', 'fauzanh', '$2y$10$MXlsTOKB1Xl7.H1IFL1EL.l6S14n2QKoo.mBxnLf3HOt1r.Muv/0G', 'L', 'Tangerang', '2008-01-30', 'Jl. Melur 7', '081234567896', 'fauzan.hakim@example.com', 'Hidayat', '081298765438', 'default.jpg'),
(16, 'Aulia Rahma', '1008', 'X-4', 'RPL', 'auliar', '$2y$10$2vBTG7e8sxUOPxxkhIWScOLbKK60zIY.hGyMbZ6tsuBlqNpOsvqp.', 'P', 'Karawang', '2008-08-09', 'Jl. Flamboyan 8', '081234567897', 'aulia.rahma@example.com', 'Syamsul', '081298765439', 'default.jpg'),
(17, 'Bagas Setiawan', '1009', 'X-5', 'TKJ', 'bagass', '$2y$10$I4rGit.K0E/294NKtUwWhuI/SkJRdYTRehNPj7mm3SSfbRHtH5yw2', 'L', 'Cimahi', '2008-10-22', 'Jl. Sakura 9', '081234567898', 'bagas.setiawan@example.com', 'Kurniawan', '081298765440', 'default.jpg'),
(18, 'Citra Anggraini', '1010', 'X-5', 'RPL', 'citraa', '$2y$10$dOBLwoscOdoQl/8jWDMLCuhk5VeDVSb9ABFkxwGGdWRJzrY45ltyW', 'P', 'Garut', '2008-04-17', 'Jl. Teratai 10', '081234567899', 'citra.anggraini@example.com', 'Sutrisno', '081298765441', 'default.jpg');

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
-- Indeks untuk tabel `guru_kelas`
--
ALTER TABLE `guru_kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_guru` (`id_guru`),
  ADD KEY `idx_kelas` (`kelas`);

--
-- Indeks untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_kelas` (`kelas`),
  ADD KEY `idx_jurusan` (`jurusan`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi_lengkap`
--
ALTER TABLE `absensi_lengkap`
  MODIFY `id_absensi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `guru`
--
ALTER TABLE `guru`
  MODIFY `id_guru` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `guru_kelas`
--
ALTER TABLE `guru_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi_lengkap`
--
ALTER TABLE `absensi_lengkap`
  ADD CONSTRAINT `absensi_lengkap_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `guru_kelas`
--
ALTER TABLE `guru_kelas`
  ADD CONSTRAINT `guru_kelas_ibfk_1` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
