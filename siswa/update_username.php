<?php
require_once '../config.php';
requireLogin('siswa');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_siswa = $_SESSION['siswa_id'];
    $username_baru = sanitize($_POST['username_baru']);
    $password_konfirmasi = $_POST['password_konfirmasi'];
    
    // Validasi username baru
    if (strlen($username_baru) < 4) {
        $_SESSION['error'] = 'Username minimal 4 karakter!';
        header('Location: dashboard.php');
        exit;
    }
    
    // Get data siswa
    $siswa = $conn->query("SELECT * FROM siswa WHERE id_siswa = $id_siswa")->fetch_assoc();
    
    // Cek password
    if (!password_verify($password_konfirmasi, $siswa['password'])) {
        $_SESSION['error'] = 'Password salah!';
        header('Location: dashboard.php');
        exit;
    }
    
    // Cek username sudah dipakai atau belum
    $check = $conn->query("SELECT id_siswa FROM siswa WHERE username = '$username_baru' AND id_siswa != $id_siswa");
    if ($check->num_rows > 0) {
        $_SESSION['error'] = 'Username sudah digunakan!';
        header('Location: dashboard.php');
        exit;
    }
    
    // Update username
    $sql = "UPDATE siswa SET username = '$username_baru' WHERE id_siswa = $id_siswa";
    
    if ($conn->query($sql)) {
        $_SESSION['success'] = 'Username berhasil diubah!';
    } else {
        $_SESSION['error'] = 'Gagal mengubah username!';
    }
    
    header('Location: dashboard.php');
    exit;
}
?>