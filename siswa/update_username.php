<?php
require_once '../config.php';
requireLogin('siswa');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header('Location: dashboard.php');
    exit;
}

$id_siswa = $_SESSION['siswa_id'];
$username_baru = trim($_POST['username_baru']);
$password_konfirmasi = $_POST['password_konfirmasi'];

// Validasi input
if (empty($username_baru) || strlen($username_baru) < 4) {
    $_SESSION['error'] = 'Username harus minimal 4 karakter';
    header('Location: dashboard.php');
    exit;
}

// Get current siswa data
$siswa = $conn->query("SELECT username, password FROM siswa WHERE id_siswa = $id_siswa")->fetch_assoc();

// Verify password
if (!password_verify($password_konfirmasi, $siswa['password'])) {
    $_SESSION['error'] = 'Password salah!';
    header('Location: dashboard.php');
    exit;
}

// Check if username already exists
$check = $conn->prepare("SELECT id_siswa FROM siswa WHERE username = ? AND id_siswa != ?");
$check->bind_param("si", $username_baru, $id_siswa);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = 'Username sudah digunakan oleh siswa lain';
    header('Location: dashboard.php');
    exit;
}

// Update username
$stmt = $conn->prepare("UPDATE siswa SET username = ? WHERE id_siswa = ?");
$stmt->bind_param("si", $username_baru, $id_siswa);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Username berhasil diubah!';
} else {
    $_SESSION['error'] = 'Gagal mengubah username';
}

header('Location: dashboard.php');
exit;
?>