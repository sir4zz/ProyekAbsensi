<?php
require_once '../config.php';
requireLogin('admin');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $result = $conn->query("SELECT * FROM siswa WHERE id_siswa = $id");
    
    if ($result->num_rows > 0) {
        $siswa = $result->fetch_assoc();
        echo json_encode($siswa);
    } else {
        echo json_encode(['error' => 'Data tidak ditemukan']);
    }
} else {
    echo json_encode(['error' => 'ID tidak valid']);
}
?>