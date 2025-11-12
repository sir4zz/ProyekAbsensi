<?php
require_once '../config.php';
requireLogin('admin');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get guru data with kelas
    $result = $conn->query("
        SELECT g.*, 
               GROUP_CONCAT(DISTINCT gk.kelas ORDER BY gk.kelas SEPARATOR ', ') as kelas_ajar
        FROM guru g
        LEFT JOIN guru_kelas gk ON g.id_guru = gk.id_guru
        WHERE g.id_guru = $id
        GROUP BY g.id_guru
    ");
    
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