<?php
require_once '../config.php';
requireLogin('siswa');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header('Location: dashboard.php');
    exit;
}

$id_siswa = $_SESSION['siswa_id'];
$password_lama = $_POST['password_lama'];
$password_baru = $_POST['password_baru'];
$password_konfirmasi = $_POST['password_konfirmasi'];

// Validasi input
if (empty($password_lama) || empty($password_baru) || empty($password_konfirmasi)) {
    $_SESSION['error'] = 'Semua field harus diisi';
    header('Location: dashboard.php');
    exit;
}

if (strlen($password_baru) < 6) {
    $_SESSION['error'] = 'Password baru harus minimal 6 karakter';
    header('Location: dashboard.php');
    exit;
}

if ($password_baru !== $password_konfirmasi) {
    $_SESSION['error'] = 'Password baru dan konfirmasi password tidak cocok';
    header('Location: dashboard.php');
    exit;
}

// Get current password
$siswa = $conn->query("SELECT password FROM siswa WHERE id_siswa = $id_siswa")->fetch_assoc();

// Verify old password
if (!password_verify($password_lama, $siswa['password'])) {
    $_SESSION['error'] = 'Password lama salah!';
    header('Location: dashboard.php');
    exit;
}

// Hash new password
$password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

// Update password
$stmt = $conn->prepare("UPDATE siswa SET password = ? WHERE id_siswa = ?");
$stmt->bind_param("si", $password_hash, $id_siswa);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Password berhasil diubah!';
} else {
    $_SESSION['error'] = 'Gagal mengubah password';
}

header('Location: dashboard.php');
exit;
?>