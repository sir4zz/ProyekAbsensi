<?php
require_once '../config.php';
requireLogin('siswa');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_FILES['foto_profil'])) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada file yang diupload']);
    exit;
}

$id_siswa = $_SESSION['siswa_id'];
$file = $_FILES['foto_profil'];

// Validasi file
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$maxSize = 2 * 1024 * 1024; // 2MB

if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Tipe file tidak diizinkan. Gunakan JPG, PNG, atau GIF']);
    exit;
}

if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB']);
    exit;
}

// Create upload directory if not exists
$uploadDir = '../uploads/profile/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Get old photo
$siswa = $conn->query("SELECT foto_profil FROM siswa WHERE id_siswa = $id_siswa")->fetch_assoc();
$oldPhoto = $siswa['foto_profil'];

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFilename = 'profile_' . $id_siswa . '_' . time() . '.' . $extension;
$uploadPath = $uploadDir . $newFilename;

// Upload file
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    // Update database
    $stmt = $conn->prepare("UPDATE siswa SET foto_profil = ? WHERE id_siswa = ?");
    $stmt->bind_param("si", $newFilename, $id_siswa);
    
    if ($stmt->execute()) {
        // Delete old photo if exists
        if ($oldPhoto && file_exists($uploadDir . $oldPhoto)) {
            unlink($uploadDir . $oldPhoto);
        }
        
        echo json_encode(['success' => true, 'message' => 'Foto profil berhasil diupdate', 'filename' => $newFilename]);
    } else {
        // Delete uploaded file if database update fails
        unlink($uploadPath);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal upload file']);
}
?>