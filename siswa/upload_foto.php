<?php
require_once '../config.php';
requireLogin('siswa');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto_profil'])) {
    $id_siswa = $_SESSION['siswa_id'];
    $file = $_FILES['foto_profil'];
    
    // Validasi file
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_ext)) {
        echo json_encode(['success' => false, 'message' => 'Format file tidak valid! Gunakan JPG, PNG, atau GIF']);
        exit;
    }
    
    // Validasi ukuran (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar! Maksimal 2MB']);
        exit;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Gagal upload file']);
        exit;
    }
    
    // Create upload directory if not exists
    $upload_dir = '../uploads/profile/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Get old photo
    $siswa = $conn->query("SELECT foto_profil FROM siswa WHERE id_siswa = $id_siswa")->fetch_assoc();
    $old_photo = $siswa['foto_profil'];
    
    // Generate unique filename
    $filename = 'profile_' . $id_siswa . '_' . time() . '.' . $file_ext;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Update database
        $sql = "UPDATE siswa SET foto_profil = '$filename' WHERE id_siswa = $id_siswa";
        
        if ($conn->query($sql)) {
            // Delete old photo if exists
            if ($old_photo && $old_photo != 'default.jpg' && file_exists($upload_dir . $old_photo)) {
                unlink($upload_dir . $old_photo);
            }
            
            echo json_encode(['success' => true, 'message' => 'Foto profil berhasil diupdate']);
        } else {
            // Rollback: delete uploaded file
            unlink($filepath);
            echo json_encode(['success' => false, 'message' => 'Gagal update database']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'File tidak ditemukan']);
}
?>