<?php
require_once '../config.php';
requireLogin('admin');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $result = $conn->query("SELECT * FROM guru WHERE id_guru = $id");
    
    if ($result->num_rows > 0) {
        $guru = $result->fetch_assoc();
        echo json_encode($guru);
    } else {
        echo json_encode(['error' => 'Data tidak ditemukan']);
    }
} else {
    echo json_encode(['error' => 'ID tidak valid']);
}
?>