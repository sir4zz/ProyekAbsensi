<?php
require_once '../config.php';
requireLogin('siswa');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_siswa = $_SESSION['siswa_id'];
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $password_konfirmasi = $_POST['password_konfirmasi'];
    
    // Validasi password baru
    if (strlen($password_baru) < 6) {
        $_SESSION['error'] = 'Password minimal 6 karakter!';
        header('Location: dashboard.php');
        exit;
    }
    
    // Cek password konfirmasi
    if ($password_baru !== $password_konfirmasi) {
        $_SESSION['error'] = 'Password baru dan konfirmasi tidak cocok!';
        header('Location: dashboard.php');
        exit;
    }
    
    // Get data siswa
    $siswa = $conn->query("SELECT * FROM siswa WHERE id_siswa = $id_siswa")->fetch_assoc();
    
    // Cek password lama
    if (!password_verify($password_lama, $siswa['password'])) {
        $_SESSION['error'] = 'Password lama salah!';
        header('Location: dashboard.php');
        exit;
    }
    
    // Hash password baru
    $hashed = password_hash($password_baru, PASSWORD_DEFAULT);
    
    // Update password
    $sql = "UPDATE siswa SET password = '$hashed' WHERE id_siswa = $id_siswa";
    
    if ($conn->query($sql)) {
        $_SESSION['success'] = 'Password berhasil diubah!';
    } else {
        $_SESSION['error'] = 'Gagal mengubah password!';
    }
    
    header('Location: dashboard.php');
    exit;
}
?>