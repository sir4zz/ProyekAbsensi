<?php
require_once '../config.php';
requireLogin('admin');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get guru data
    $result = $conn->query("SELECT * FROM guru WHERE id_guru = $id");
    
    if ($result->num_rows > 0) {
        $guru = $result->fetch_assoc();
        
        // Get kelas yang diajar (format: TKJ-X, RPL-XI)
        $kelas_query = $conn->query("
            SELECT CONCAT(jurusan, '-', tingkat) as kelas_combo
            FROM guru_kelas 
            WHERE id_guru = $id
            ORDER BY jurusan, tingkat
        ");
        
        $kelas_ajar_array = [];
        while ($row = $kelas_query->fetch_assoc()) {
            $kelas_ajar_array[] = $row['kelas_combo'];
        }
        
        $guru['kelas_ajar_array'] = $kelas_ajar_array;
        
        echo json_encode($guru);
    } else {
        echo json_encode(['error' => 'Data tidak ditemukan']);
    }
} else {
    echo json_encode(['error' => 'ID tidak valid']);
}
?>